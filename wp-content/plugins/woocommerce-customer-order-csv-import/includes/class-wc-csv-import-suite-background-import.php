<?php
/**
 * WooCommerce Customer/Order/Coupon CSV Import Suite
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Customer/Order/Coupon CSV Import Suite to newer
 * versions in the future. If you wish to customize WooCommerce Customer/Order/Coupon CSV Import Suite for your
 * needs please refer to http://docs.woothemes.com/document/customer-order-csv-import-suite/ for more information.
 *
 * @package   WC-CSV-Import-Suite/Classes
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;


/**
 * WooCommerce CSV Import Suite Background Import handler class.
 *
 * Subclasses SV_WP_Background_Job_Handler, tailored for
 * processing files. As such, it's different in some key aspects:
 * - job progress (last processed line number) is stored in a dedicated option
 * - job results are stored in a dedicated option
 *
 * @since 3.0.0
 */
class WC_CSV_Import_Suite_Background_Import extends SV_WP_Background_Job_Handler {


	/** @var string async request prefix */
	protected $prefix = 'wc_csv_import_suite';

	/** @var string process action */
	protected $action = 'background_import';


	/**
	 * Initiate new background import handler
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		parent::__construct();

		add_action( "{$this->identifier}_job_created", array( $this, 'init_job' ) );
		add_action( "{$this->identifier}_job_complete", array( $this, 'cleanup_job' ) );

		add_filter( "{$this->identifier}_returned_job", array( $this, 'augment_job' ) );
	}


	/**
	 * Create dedicated progress & results options for each new import job
	 *
	 * Keeping progress and results in separate options can prevent potential
	 * bottlenecks when processing very large CSV files with thousands of lines
	 * of code
	 *
	 * @since 3.0.0
	 * @param object $job
	 */
	public function init_job( $job ) {

		// remove old completed jobs
		$this->fifo();

		// Start after line 1 (since first line is the header)
		update_option( "{$this->identifier}_progress_{$job->id}" , array( 'line' => 1, 'pos' => 0 ) );
		update_option( "{$this->identifier}_results_{$job->id}" , '' );
	}


	/**
	 * Clear old, completed & failed jobs from the database
	 *
	 * Makes sure that only up to 10 completed/failed jobs are kept in the database
	 *
	 * @since 3.0.0
	 * @return $this
	 */
	private function fifo() {
		global $wpdb;

		$key       = $this->identifier . '_job_%';
		$completed = '%"status":"completed"%';
		$failed    = '%"status":"failed"%';

		$jobs = $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT(*)
			FROM {$wpdb->options}
			WHERE option_name LIKE %s
			AND ( option_value LIKE %s OR option_value LIKE %s )
		", $key, $completed, $failed ) );

		$threshold = 10;

		if ( $jobs >= $threshold ) {
			$result = $wpdb->query( $wpdb->prepare( "
				DELETE
				FROM {$wpdb->options}
				WHERE option_name LIKE %s
				AND ( option_value LIKE %s OR option_value LIKE %s )
				ORDER BY option_id ASC
				LIMIT 1
			", $key, $completed, $failed ) );
		}

		return $this;
	}


	/**
	 * Clean up after a job has been completed
	 *
	 * @since 3.0.0
	 * @param object $job
	 */
	public function cleanup_job( $job ) {
		delete_option( "{$this->identifier}_progress_{$job->id}" );
		delete_option( "{$this->identifier}_results_{$job->id}" );
	}


	/**
	 * Augment job with additional data
	 *
	 * @since 3.0.0
	 * @param object $job
	 * @return object
	 */
	public function augment_job( $job ) {

		$job->progress = $this->get_job_progress( $job->id );
		$job->results  = $this->get_job_results( $job->id );

		return $job;
	}


	/**
	 * Get job progress
	 *
	 * @since 3.0.0
	 * @param string $job_id Unique job ID
	 * @return int
	 */
	protected function get_job_progress( $job_id ) {
		return get_option( "{$this->identifier}_progress_{$job_id}" );
	}


	/**
	 * Get job results
	 *
	 * @since 3.0.0
	 * @param string $job_id Unique job ID
	 * @return array
	 */
	protected function get_job_results( $job_id ) {
		return json_decode( get_option( "{$this->identifier}_results_{$job_id}" ), true );
	}


	/**
	 * Update job progress
	 *
	 * @since 3.0.0
	 * @param string $job_id Unique job ID
	 * @param int $progress Progress
	 */
	protected function update_job_progress( $job_id, $progress ) {
		update_option( "{$this->identifier}_progress_{$job_id}", $progress );
	}


	/**
	 * Update job results
	 *
	 * @since 3.0.0
	 * @param string $job_id Unique job ID
	 * @param array $results Batch results
	 */
	protected function update_job_results( $job_id, $results ) {
		update_option( "{$this->identifier}_results_{$job_id}", json_encode( $results ) );
	}


	/**
	 * Process job
	 *
	 * CSV Imports do not have a list of items to loop over. Instead, we
	 * start reading the file line-by-line until we run out of memory or
	 * exceed the time limit.
	 *
	 * @since 3.0.0
	 * @param object $job
	 */
	protected function process_job( $job ) {

		$line      = $job->progress['line'] + 1;
		$start_pos = $job->progress['pos'];
		$results   = (array) $job->results;

		// load the correct importer type
		$importer = wc_csv_import_suite()->get_importers_instance()->get_importer( $job->type );

		// no importer found, not much we can do here, halt further processing
		if ( ! $importer ) {

			$message = sprintf( esc_html__( 'Unknown importer "%s". Cancelling.', 'woocommerce-csv-import-suite' ), $job->type );

			wc_csv_import_suite()->log( $message );

			$this->fail_job( $job, $message );

			return;
		}

		// pass each line to importer until memory or time limit is exceeded
		while ( is_numeric( $start_pos ) && $start_pos <= $job->file_size ) {

			// adjust import options for the current line
			$options = (array) $job->options + array(
				'start_pos'  => $start_pos,
				'start_line' => $line,
				'max_lines'  => 1,
			);

			// import the current line
			$importer->import( $job->file_path, $options );

			// add new results and save
			$results += (array) $importer->get_import_results();
			$this->update_job_results( $job->id, $results );

			// update job progress
			$progress = $importer->get_import_progress();
			$this->update_job_progress( $job->id, $progress );

			// set import options for next round
			$start_pos = $progress['pos']; // if reached EOF, this will be empty/null
			$line      = $progress['line'] + 1;

			// memory or time limit reached
			if ( $this->time_exceeded() || $this->memory_exceeded() ) {
				break;
			}
		}

		// job complete! :)
		if ( ! is_numeric( $start_pos ) || $start_pos >= $job->file_size ) {
			$job->results = $results; // augment job with results before completing
			$this->complete_job( $job );
		}

	}


	/**
	 * No-op
	 *
	 * @since 3.0.3
	 */
	protected function process_item( $item, $job ) {
		// void
	}


}

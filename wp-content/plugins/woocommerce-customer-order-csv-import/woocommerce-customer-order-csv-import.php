<?php
/**
 * Plugin Name: WooCommerce Customer/Order/Coupon CSV Import Suite
 * Plugin URI: http://www.woothemes.com/extension/customerorder-csv-import-suite/
 * Description: Import customers, coupons and orders straight from the WordPress admin
 * Author: WooThemes / SkyVerge
 * Author URI: http://www.woothemes.com
 * Version: 3.0.3
 * Text Domain: woocommerce-csv-import-suite
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2012-2016 SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-CSV-Import-Suite
 * @author    SkyVerge
 * @category  Importer
 * @copyright Copyright (c) 2012-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

// Required functions
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'woo-includes/woo-functions.php' );
}

// Plugin updates
woothemes_queue_update( plugin_basename( __FILE__ ), 'eb00ca8317a0f64dbe185c995e5ea3df', '18709' );

// WC active check/is admin
if ( ! is_woocommerce_active() ) {
	return;
}

// Required library classss
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'lib/skyverge/woocommerce/class-sv-wc-framework-bootstrap.php' );
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.4.2', __( 'WooCommerce Customer/Order/Coupon CSV Import', 'woocommerce-csv-import-suite' ), __FILE__, 'init_woocommerce_csv_import_suite', array(
	'minimum_wc_version'   => '2.4.13',
	'minimum_wp_version'   => '4.1',
	'backwards_compatible' => '4.4.0',
) );

function init_woocommerce_csv_import_suite() {

/**
 * Customer/Order/Coupon CSV Import Suite Main Class.  This class is responsible
 * for registering the importers and setting up the admin start page/menu
 * items.  The actual import process is handed off to the various parse
 * and import classes.
 *
 * Adapted from the WordPress post importer by the WordPress team
 */
class WC_CSV_Import_Suite extends SV_WC_Plugin {


	/** version number */
	const VERSION = '3.0.3';

	/** @var WC_CSV_Import_Suite single instance of this plugin */
	protected static $instance;

	/** string the plugin id */
	const PLUGIN_ID = 'csv_import_suite';

	/** plugin text domain, DEPRECATED as of 2.9.0 */
	const TEXT_DOMAIN = 'woocommerce-csv-import-suite';

	/** @var \WC_CSV_Import_Suite_Admin instance */
	protected $admin;

	/** @var \WC_CSV_Import_Suite_Importers instance */
	protected $importers;

	/** @var \WC_CSV_Import_Suite_Background_Import instance */
	protected $background_import;

	/** @var \WC_CSV_Import_Suite_AJAX instance */
	protected $ajax;


	/**
	 * Construct and initialize the main plugin class
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array(
				'dependencies'          => array( 'mbstring' ),
				'function_dependencies' => array( 'str_getcsv' ),
			)
		);

		// Initialize
		add_action( 'init', array( $this, 'includes' ) );
	}


	/**
	 * Include required files
	 *
	 * @since 3.0.0
	 */
	public function includes() {

		// importers and background import must be loaded all the time, because
		// otherwise background jobs simply won't work
		require_once( $this->get_framework_path() . '/utilities/class-sv-wp-async-request.php' );
		require_once( $this->get_framework_path() . '/utilities/class-sv-wp-background-job-handler.php' );

		$this->background_import = $this->load_class( '/includes/class-wc-csv-import-suite-background-import.php', 'WC_CSV_Import_Suite_Background_Import' );
		$this->importers         = $this->load_class( '/includes/class-wc-csv-import-suite-importers.php', 'WC_CSV_Import_Suite_Importers' );

		if ( is_admin() ) {
			$this->admin_includes();
		}

		if ( is_ajax() ) {
			$this->ajax_includes();
		}
	}


	/**
	 * Include required admin files
	 *
	 * @since 3.0.0
	 */
	private function admin_includes() {
		$this->admin = $this->load_class( '/includes/admin/class-wc-csv-import-suite-admin.php', 'WC_CSV_Import_Suite_Admin' );
	}


	/**
	 * Include required AJAX files
	 *
	 * @since 3.0.0
	 */
	private function ajax_includes() {

		require_once( $this->get_plugin_path() . '/includes/class-wc-csv-import-suite-parser.php' );
		$this->ajax = $this->load_class( '/includes/class-wc-csv-import-suite-ajax.php', 'WC_CSV_Import_Suite_AJAX' );
	}


	/**
	 * Return admin class instance
	 *
	 * @since 3.0.0
	 * @return \WC_CSV_Import_Suite_Admin
	 */
	public function get_admin_instance() {
		return $this->admin;
	}


	/**
	 * Return importers class instance
	 *
	 * @since 3.0.0
	 * @return \WC_CSV_Import_Suite_Importers
	 */
	public function get_importers_instance() {
		return $this->importers;
	}


	/**
	 * Return background import class instance
	 *
	 * @since 3.0.0
	 * @return \WC_CSV_Import_Suite_Background_Import
	 */
	public function get_background_import_instance() {
		return $this->background_import;
	}


	/**
	 * Return the ajax class instance
	 *
	 * @since 3.0.0
	 * @return \WC_CSV_Import_Suite_AJAX
	 */
	public function get_ajax_instance() {
		return $this->ajax;
	}


	/**
	 * Backwards compat for changing the visibility of some class instances.
	 *
	 * @TODO Remove this as part of WC 2.7 compat {IT 2016-05-17}
	 *
	 * @since 3.0.0
	 */
	public function __get( $name ) {

		switch ( $name ) {

			case 'admin':
				_deprecated_function( 'wc_csv_import_suite()->admin', '3.0.0', 'wc_csv_import_suite()->get_admin_instance()' );
				return $this->get_admin_instance();

			case 'ajax':
				_deprecated_function( 'wc_csv_import_suite()->ajax', '3.0.0', 'wc_csv_import_suite()->get_ajax_instance()' );
				return $this->get_ajax_instance();
		}

		// you're probably doing it wrong
		trigger_error( 'Call to undefined property ' . __CLASS__ . '::' . $name, E_USER_ERROR );

		return null;
	}


	/**
	 * Load plugin text domain.
	 *
	 * @see SV_WC_Plugin::load_translation()
	 */
	public function load_translation() {

		load_plugin_textdomain( 'woocommerce-csv-import-suite', false, dirname( plugin_basename( $this->get_file() ) ) . '/i18n/languages' );
	}


	/**
	 * Returns the "Import" plugin action link to go directly to the plugin
	 * settings page (if any)
	 *
	 * @since 2.3
	 * @see SV_WC_Plugin::get_settings_link()
	 * @param string $plugin_id the plugin identifier.  Note that this can be a
	 *        sub-identifier for plugins with multiple parallel settings pages
	 *        (ie a gateway that supports both credit cards and echecks)
	 * @return string plugin configure link
	 */
	public function get_settings_link( $plugin_id = null ) {

		$settings_url = $this->get_settings_url( $plugin_id );

		if ( $settings_url ) {
			return sprintf( '<a href="%s">%s</a>', $settings_url, __( 'Import', 'woocommerce-csv-import-suite' ) );
		}

		// no settings
		return '';
	}


	/**
	 * Gets the plugin configuration URL
	 *
	 * @since 2.3
	 * @see SV_WC_Plugin::get_settings_url()
	 * @param string $plugin_id the plugin identifier.
	 * @return string plugin settings URL
	 */
	public function get_settings_url( $plugin_id = null ) {

		// link to the import page
		return admin_url( 'admin.php?page=' . self::PLUGIN_ID );
	}


	/**
	 * Gets the plugin documentation url, which is non-standard for this plugin
	 *
	 * @since 2.3.0
	 * @see SV_WC_Plugin::get_documentation_url()
	 * @return string documentation URL
	 */
	public function get_documentation_url() {
		return 'http://docs.woothemes.com/document/customer-order-csv-import-suite/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since VERSION
	 * @see SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {
		return 'http://support.woothemes.com/';
	}


	/**
	 * Returns true if on the Customer/Order/CouponImport page
	 *
	 * @since 2.3
	 * @see SV_WC_Plugin::is_plugin_settings()
	 * @return boolean true if on the plugin admin settings page
	 */
	public function is_plugin_settings() {
		return isset( $_GET['page'] ) && self::PLUGIN_ID == $_GET['page'];
	}


	/** Helper methods ******************************************************/


	/**
	* Main Customer/Order/Coupon CSV Import Suite Instance, ensures only one instance is/can be loaded
	*
	* @since 2.7.0
	* @see wc_csv_import_suite()
	* @return WC_CSV_Import_Suite
	*/
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 2.3
	 * @see SV_WC_Payment_Gateway::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {
		return __( 'WooCommerce Customer/Order/Coupon CSV Import', 'woocommerce-csv-import-suite' );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 2.3
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {
		return __FILE__;
	}


} // class WC_CSV_Import_Suite


/**
 * Returns the One True Instance of Customer/Order/Coupon CSV Import Suite
 *
 * @since 2.7.0
 * @return WC_CSV_Import_Suite
*/
function wc_csv_import_suite() {
	return WC_CSV_Import_Suite::instance();
}


// fire it up!
wc_csv_import_suite();

} // init_woocommerce_csv_import_suite()

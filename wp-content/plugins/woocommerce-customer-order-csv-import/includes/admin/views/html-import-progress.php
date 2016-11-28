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
 * @package     WC-CSV-Import-Suite/Views
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2016, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;
?>

<div class="woocommerce">

	<p><?php printf( $is_complete ? esc_html__( 'Imported file: %s', 'woocommerce-csv-import-suite' ) : esc_html__( 'Importing file: %s', 'woocommerce-csv-import-suite' ), $filename ); ?></p>

	<?php if ( $options['dry_run'] ) : ?>
		<div class="notice notice-warning notice-dry-run">
			<p><?php $is_complete ? esc_html_e( 'Performed a dry run with the selected file. No database records were inserted or updated.', 'woocommerce-csv-import-suite' ) : esc_html_e( 'Performing a dry run with the selected file. No database records will be inserted or updated.', 'woocommerce-csv-import-suite' ); ?></p>
		</div>
	<?php endif; ?>

	<div id="wc-csv-import-suite-progress">

		<div class="progress-bar">
			<div class="bar" style="width: 0%">
				<span class="percentage">0 %</span>
			</div>
		</div>

		<div class="progress-count">
			<?php esc_html_e( 'Processed:', 'woocommerce-csv-import-suite' ); ?> <?php printf( $csv_importer->i18n['count'], '<span class="processed-count">' . count( $results ) . '</span>' ); ?>
		</div>

	</div>

	<div id="wc-csv-import-suite-results">

		<h3><?php esc_html_e( 'Results', 'woocommerce-csv-import-suite' ); ?></h3>

		<div class="woocommerce-reports-wide">
			<div class="postbox">

			<div class="inside chart-with-sidebar">
				<div class="chart-sidebar">
					<ul class="chart-legend">
						<?php foreach ( $legends as $key => $legend ) : ?>
							<li style="border-color: <?php echo $legend['color']; ?>" <?php if ( isset( $legend['highlight_series'] ) ) echo 'class="highlight_series ' . ( isset( $legend['placeholder'] ) ? 'tips' : '' ) . ' ' . esc_attr( $key ) . '" data-series="' . esc_attr( $legend['highlight_series'] ) . '"'; ?> data-tip="<?php echo isset( $legend['placeholder'] ) ? $legend['placeholder'] : ''; ?>">
								<?php echo $legend['title']; ?>
							</li>
						<?php endforeach; ?>
					</ul>
					<ul class="chart-widgets">
						<li class="chart-widget details-widget js-details-link-widget">
							<a class="js-toggle-details details-toggle" href="#"><span class="dashicons dashicons-list-view"></span> <span class="js-toggle-details-text"><?php esc_html_e( 'View detailed results', 'woocommerce-csv-import-suite' ); ?></span></a>
						</li>
					</ul>
				</div>
				<div class="main">
					<div class="chart-container">
						<div class="chart-placeholder import-results pie-chart"></div>
					</div>
				</div>
			</div>

		</div>
		</div>

		<table class="widefat results-details">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Line', 'woocommerce-csv-import-suite' ); ?></th>
					<th><?php esc_html_e( 'Status', 'woocommerce-csv-import-suite' ); ?></th>
					<th><?php esc_html_e( 'Reason', 'woocommerce-csv-import-suite' ); ?></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>

	</div>

</div>

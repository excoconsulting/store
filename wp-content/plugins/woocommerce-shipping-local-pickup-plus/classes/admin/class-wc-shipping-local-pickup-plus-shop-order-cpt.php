<?php
/**
 * WooCommerce Local Pickup Plus
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Local Pickup Plus to newer
 * versions in the future. If you wish to customize WooCommerce Local Pickup Plus for your
 * needs please refer to http://docs.woothemes.com/document/local-pickup-plus/
 *
 * @package     WC-Shipping-Local-Pickup-Plus
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2016, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Order CPT class
 *
 * Handles modifications to the shop order CPT on both View Orders list table and Edit Order screen
 *
 * @since 1.8.0
 */
class WC_Shipping_Local_Pickup_Plus_CPT {


	/**
	 * Add actions/filters for View Orders/Edit Order screen
	 *
	 * @since 1.8.0
	 * @return \WC_Shipping_Local_Pickup_Plus_CPT
	 */
	public function __construct() {

		// Add 'Pickup Locations' orders page column header
		add_filter( 'manage_edit-shop_order_columns',        array( $this, 'render_pickup_locations_column_header' ), 20 );

		// Add 'Pickup Locations' orders page column content
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_pickup_locations_column_content' ) );

		// Add CSS to tweak the 'Pickup Locations' column
		add_action( 'admin_head',                           array( $this, 'render_pickup_locations_column_styles' ) );
	}


	/** Listable Columns ******************************************************/


	/**
	 * Adds 'Pickup Locations' column header to 'Orders' page immediately after 'Ship to' column
	 *
	 * @since 1.8.0
	 * @param array $columns
	 * @return array $new_columns
	 */
	public function render_pickup_locations_column_header( $columns ) {

		$new_columns = array();

		foreach ( $columns as $column_name => $column_info ) {

			$new_columns[ $column_name ] = $column_info;

			if ( 'shipping_address' == $column_name ) {

				$new_columns['pickup_locations'] = __( 'Pickup Locations', 'woocommerce-shipping-local-pickup-plus' );
			}
		}

		return $new_columns;
	}


	/**
	 * Adds 'Pickup Locations' column content to 'Orders' page immediately after 'Order Status' column
	 *
	 * @since 1.8.0
	 * @param array $column name of column being displayed
	 */
	public function render_pickup_locations_column_content( $column ) {
		global $post;

		if ( 'pickup_locations' === $column ) {

			$order = wc_get_order( $post->ID );

			$pickup_locations = $this->get_order_pickup_locations( $order );

			foreach ( $pickup_locations as $pickup_location ) {

				$formatted_pickup_location = WC()->countries->get_formatted_address( array_merge( array( 'first_name' => null, 'last_name' => null, 'state' => null ), $pickup_location ) );

				if ( isset( $pickup_location['phone'] ) && $pickup_location['phone'] ) {
					$formatted_pickup_location .= "<br/>\n" . $pickup_location['phone'];
				}

				echo esc_html( preg_replace( '#<br\s*/?>#i', ', ', $formatted_pickup_location ) );
			}
		}
	}


	/**
	 * Adds CSS to style the 'Pickup Locations' column
	 *
	 * @since 1.10.1
	 */
	public function render_pickup_locations_column_styles() {

		$screen = get_current_screen();

		if ( 'edit-shop_order' === $screen->id ) {
		?>
			<style type="text/css">
				.widefat .column-pickup_locations {
					width: 12%;
				}
			</style>
		<?php
		}
	}

	/** Helper Methods ********************************************************/


	/**
	 * Gets any order pickup locations from the given order
	 *
	 * @since 1.8.0
	 * @param WC_Order $order the order
	 * @return array of pickup locations, with country, postcode, state, city, address_2, adress_1, company, phone, cost and id properties
	 */
	private function get_order_pickup_locations( $order ) {

		$pickup_locations = array();

		foreach ( $order->get_shipping_methods() as $shipping_item ) {

			if ( WC_Local_Pickup_Plus::METHOD_ID == $shipping_item['method_id'] && isset( $shipping_item['pickup_location'] ) ) {

				$location = maybe_unserialize( $shipping_item['pickup_location'] );
				$pickup_locations[] = $location;
			}
		}

		return $pickup_locations;
	}


} // end \WC_Shipping_Local_Pickup_Plus_CPT class

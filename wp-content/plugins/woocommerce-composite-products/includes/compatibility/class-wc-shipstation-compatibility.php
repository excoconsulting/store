<?php
/**
 * Shipstation Integration.
 *
 * @version 3.3.0
 * @since   3.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_CP_Shipstation_Compatibility {

	public static function init() {

		// Shipstation compatibility
		add_filter( 'woocommerce_composite_filter_product_from_item', array( __CLASS__, 'filter_order_data' ), 11, 2 );
		add_filter( 'woocommerce_composite_filter_order_items_part_of_meta', array( __CLASS__, 'filter_order_data' ), 10, 2 );
	}

	/**
	 * Use the Order API Modifications in WC_CP_Order to return the correct items/weights/values for shipping.
	 *
	 * @param  boolean   $filter
	 * @param  WC_Order  $order
	 * @return boolean
	 */
	public static function filter_order_data( $filter, $order ) {

		global $wp;

		if ( isset( $wp->query_vars[ 'wc-api' ] ) && $wp->query_vars[ 'wc-api' ] === 'wc_shipstation' ) {
			$filter = true;
		}

		return $filter;
	}
}

WC_CP_Shipstation_Compatibility::init();

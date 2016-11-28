<?php
/**
 * Subscriptions Integration.
 *
 * @since  3.6.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_CP_Subscriptions_Compatibility {

	public static function init() {

		/*
		 * Remove orphaned bundled item when paying for an order that contains subscription items.
		 * Temporary workaround for https://github.com/Prospress/woocommerce-subscriptions/issues/1362
		 */
		add_action( 'woocommerce_add_to_cart', array( __CLASS__, 'remove_orhpaned_composited_cart_item' ), 10, 6 );
	}

	/**
	 * Remove orphaned bundled item when paying for an order that contains subscription items.
	 *
	 * @param  string   $cart_item_key
	 * @param  int      $product_id
	 * @param  int      $quantity
	 * @param  int      $variation_id
	 * @param  array    $variation
	 * @param  array    $cart_item_data
	 * @return void
	 */
	public static function remove_orhpaned_composited_cart_item( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {

		global $wp;

		if ( isset( $_GET[ 'pay_for_order' ] ) && isset( $_GET[ 'key' ] ) && isset( $wp->query_vars[ 'order-pay' ] ) && isset( $cart_item_data[ 'is_order_again_composited' ] ) ) {
			unset( WC()->cart->cart_contents[ $cart_item_key ] );
		}
	}
}

WC_CP_Subscriptions_Compatibility::init();

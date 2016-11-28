<?php
/**
 * Cost of Goods Compatibility.
 *
 * @version 3.3.0
 * @since   3.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_CP_COG_Compatibility {

	public static function init() {

		// Cost of Goods support
		add_filter( 'wc_cost_of_goods_save_checkout_order_item_meta_item_cost', array( __CLASS__, 'cost_of_goods_checkout_order_composited_item_cost' ), 10, 3 );
		add_filter( 'wc_cost_of_goods_save_checkout_order_meta_item_cost', array( __CLASS__, 'cost_of_goods_checkout_order_composited_item_cost' ), 10, 3 );
		add_filter( 'wc_cost_of_goods_set_order_item_cost_meta_item_cost', array( __CLASS__, 'cost_of_goods_set_order_item_cost_composited_item_cost' ), 10, 3 );
	}

	/**
	 * Cost of goods compatibility: Zero order item cost for composited products that belong to statically priced composites.
	 *
	 * @param  double $cost
	 * @param  array  $values
	 * @param  string $cart_item_key
	 * @return double
	 */
	public static function cost_of_goods_checkout_order_composited_item_cost( $cost, $values, $cart_item_key ) {

		if ( ! empty( $values[ 'composite_parent' ] ) ) {

			$cart_contents = WC()->cart->get_cart();
			$parent_key    = $values[ 'composite_parent' ];

			if ( isset( $cart_contents[ $parent_key ] ) ) {
				if ( ! $cart_contents[ $parent_key ][ 'data' ]->is_priced_per_product() ) {
					return 0;
				}
			}

		} elseif ( ! empty( $values[ 'composite_children' ] ) ) {
			if ( $values[ 'data' ]->is_priced_per_product() ) {
				return 0;
			}
		}

		return $cost;
	}

	/**
	 * Cost of goods compatibility: Zero order item cost for composited products that belong to statically priced composites.
	 *
	 * @param  double   $cost
	 * @param  array    $item
	 * @param  WC_Order $order
	 * @return double
	 */
	public static function cost_of_goods_set_order_item_cost_composited_item_cost( $cost, $item, $order ) {

		if ( ! empty( $item[ 'composite_parent' ] ) ) {

			// find bundle parent
			$parent_item = WC_CP_Order::get_composite_parent( $item, $order );

			$per_product_pricing = ! empty( $parent_item ) && isset( $parent_item[ 'per_product_pricing' ] ) ? $parent_item[ 'per_product_pricing' ] : get_post_meta( $parent_item[ 'product_id' ], '_per_product_pricing_bto', true );

			if ( $per_product_pricing === 'no' ) {
				return 0;
			}

		} elseif ( isset( $item[ 'composite_children' ] ) ) {

			$per_product_pricing = isset( $item[ 'per_product_pricing' ] ) ? $item[ 'per_product_pricing' ] : get_post_meta( $item[ 'product_id' ], '_per_product_pricing_bto', true );

			if ( $per_product_pricing === 'yes' ) {
				return 0;
			}
		}

		return $cost;
	}
}

WC_CP_COG_Compatibility::init();

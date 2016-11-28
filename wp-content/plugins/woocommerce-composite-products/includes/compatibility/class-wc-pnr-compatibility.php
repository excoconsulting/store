<?php
/**
 * Points and Rewards Compatibility.
 *
 * @version 3.3.0
 * @since   3.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_CP_PnR_Compatibility {

	public static function init() {

		// Points and Rewards support
		add_filter( 'woocommerce_points_earned_for_cart_item', array( __CLASS__, 'points_earned_for_composited_cart_item' ), 10, 3 );
		add_filter( 'woocommerce_points_earned_for_order_item', array( __CLASS__, 'points_earned_for_composited_order_item' ), 10, 5 );

		// Change earn points message for per-product-priced bundles
		add_filter( 'wc_points_rewards_single_product_message', array( __CLASS__, 'points_rewards_composite_message' ), 10, 2 );

		// Remove PnR message from variations
		add_action( 'woocommerce_composite_products_apply_product_filters', array( __CLASS__, 'points_rewards_remove_price_html_messages' ) );
		add_action( 'woocommerce_composite_products_remove_product_filters', array( __CLASS__, 'points_rewards_restore_price_html_messages' ) );
	}

	/**
	 * Return zero points for composited cart items if container item has product level points.
	 *
	 * @param  int     $points
	 * @param  string  $cart_item_key
	 * @param  array   $cart_item_values
	 * @return int
	 */
	public static function points_earned_for_composited_cart_item( $points, $cart_item_key, $cart_item_values ) {

		if ( isset( $cart_item_values[ 'composite_parent' ] ) ) {

			$cart_contents     = WC()->cart->get_cart();
			$composite_cart_id = $cart_item_values[ 'composite_parent' ];
			$composite         = $cart_contents[ $composite_cart_id ][ 'data' ];

			// check if earned points are set at product-level
			$composite_points             = WC_Points_Rewards_Product::get_product_points( $composite );
			$per_product_priced_composite = $composite->is_priced_per_product();
			$has_composite_points         = is_numeric( $composite_points ) ? true : false;

			if ( $has_composite_points || ! $per_product_priced_composite ) {
				$points = 0;
			} else {
				$points = WC_Points_Rewards_Manager::calculate_points( $cart_item_values[ 'data' ]->get_price() );
			}
		}

		return $points;
	}

	/**
	 * Return zero points for composited cart items if container item has product level points.
	 *
	 * @param  int        $points
	 * @param  string     $item_key
	 * @param  array      $item
	 * @param  WC_Order   $order
	 * @return int
	 */
	public static function points_earned_for_composited_order_item( $points, $product, $item_key, $item, $order ) {

		if ( isset( $item[ 'composite_parent' ] ) ) {

			// find container item
			foreach ( $order->get_items() as $order_item ) {

				$is_parent = isset( $order_item[ 'composite_cart_key' ] ) && $item[ 'composite_parent' ] === $order_item[ 'composite_cart_key' ];

				if ( $is_parent ) {

					$parent_item  = $order_item;
					$composite_id = $parent_item[ 'product_id' ];

					// check if earned points are set at product-level
					$composite_points = get_post_meta( $composite_id, '_wc_points_earned', true );

					$per_product_priced_composite = isset( $parent_item[ 'per_product_pricing' ] ) ? $parent_item[ 'per_product_pricing' ] : get_post_meta( $composite_id, '_per_product_pricing_bto', true );

					if ( ! empty( $composite_points ) || $per_product_priced_composite !== 'yes' ) {
						$points = 0;
					} else {
						$points = WC_Points_Rewards_Manager::calculate_points( $product->get_price() );
					}

					break;
				}
			}
		}

		return $points;
	}

	/**
	 * Points and Rewards single product message for per-product priced Bundles.
	 * @param  string                    $message
	 * @param  WC_Points_Rewards_Product $points_n_rewards
	 * @return string
	 */
	public static function points_rewards_composite_message( $message, $points_n_rewards ) {

		global $product;

		if ( $product->product_type == 'composite' ) {

			if ( ! $product->is_priced_per_product() ) {
				return $message;
			}

			// Will calculate points based on min_composite_price, which is saved as _price meta
			$composite_points = WC_Points_Rewards_Product::get_points_earned_for_product_purchase( $product );

			$message = $points_n_rewards->create_at_least_message_to_product_summary( $composite_points );
		}

		return $message;
	}

	/**
	 * Filter option_wc_points_rewards_single_product_message in order to force 'WC_Points_Rewards_Product::render_variation_message' to display nothing.
	 *
	 * @return void
	 */
	public static function points_rewards_remove_price_html_messages( $args ) {
		add_filter( 'option_wc_points_rewards_single_product_message', array( __CLASS__, 'return_empty_message' ) );
	}

	/**
	 * Restore option_wc_points_rewards_single_product_message. Forced in order to force 'WC_Points_Rewards_Product::render_variation_message' to display nothing.
	 *
	 * @return void
	 */
	public static function points_rewards_restore_price_html_messages( $args ) {
		remove_filter( 'option_wc_points_rewards_single_product_message', array( __CLASS__, 'return_empty_message' ) );
	}

	/**
	 * @see points_rewards_remove_price_html_messages
	 * @param  string  $message
	 * @return void
	 */
	public static function return_empty_message( $message ) {
		return false;
	}
}

WC_CP_PnR_Compatibility::init();

<?php
/**
 * WooCommerce Points and Rewards
 *
 * @package     WC-Points-Rewards/Classes
 * @author      WooThemes
 * @copyright   Copyright (c) 2013, WooThemes
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order class
 *
 * Handle adding points earned upon checkout & deducting points redeemed for discounts
 *
 * @since 1.0
 */
class WC_Points_Rewards_Order {


	/**
	 * Add hooks/filters
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// add points earned when payment is completed
		add_action( 'woocommerce_payment_complete', array( $this, 'add_points_earned' ) );

		// add points earned when payment is completed for gateways that don't call WC_Order::payment_complete()
		add_action( 'woocommerce_order_status_processing', array( $this, 'add_points_earned' ) );
		add_action( 'woocommerce_order_status_pending_to_completed', array( $this, 'add_points_earned' ) );
		add_action( 'woocommerce_order_status_on-hold_to_completed', array( $this, 'add_points_earned' ) );

		// add points earned when payment has previously failed
		add_action( 'woocommerce_order_status_failed_to_processing', array( $this, 'add_points_earned' ) );
		add_action( 'woocommerce_order_status_failed_to_completed',  array( $this, 'add_points_earned' ) );

		// deduct points redeemed for a discount upon checkout processing
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'maybe_deduct_redeemed_points' ) );

		// credit points back to the user if their order is cancelled or refunded
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'handle_cancelled_refunded_order' ) );
		add_action( 'woocommerce_order_status_refunded', array( $this, 'handle_cancelled_refunded_order' ) );
		add_action( 'woocommerce_order_status_failed', array( $this, 'handle_cancelled_refunded_order' ) );
	}

	/**
	 * Add the points earned for purchase to the customer's account upon successful payment
	 *
	 * @since 1.0
	 * @param object|int $order the WC_Order object or order ID
	 */
	public function add_points_earned( $order ) {
		global $wc_points_rewards;

		if ( ! is_object( $order ) ) {
			$order = new WC_Order( $order );
		}

		// bail for guest user
		if ( ! $order->user_id ) {
			return;
		}

		// check if points have already been added for this order
		$points = get_post_meta( $order->id, '_wc_points_earned', true );

		if ( '' !== $points ) {
			return;
		}

		// get points earned
		$points = $this->get_points_earned_for_purchase( $order );

		// set order meta, regardless of whether any points were earned, just so we know the process took place
		update_post_meta( $order->id, '_wc_points_earned', $points );

		// bail if no points earned
		if ( ! $points ) {
			return;
		}

		// add points
		WC_Points_Rewards_Manager::increase_points( $order->user_id, $points, 'order-placed', null, $order->id );

		// add order note
		$order->add_order_note( sprintf( __( 'Customer earned %d %s for purchase.', 'woocommerce-points-and-rewards' ), $points, $wc_points_rewards->get_points_label( $points ) ) );
	}

	/**
	 * Returns the amount of points earned for the purchase, calculated by getting the points earned for each individual
	 * product purchase multiplied by the quantity being ordered
	 *
	 * @since 1.0
	 */
	private function get_points_earned_for_purchase( $order ) {

		$points_earned = 0;

		foreach ( $order->get_items() as $item_key => $item ) {

			$product = $order->get_product_from_item( $item );

			if ( ! is_object( $product ) ) {
				continue;
			}

			// If prices include tax, we include the tax in the points calculation
			if ( get_option('woocommerce_prices_include_tax') == 'no' ) {
				// Get the un-discounted price paid and adjust our product price
				$item_price = $order->get_item_subtotal( $item, false, true );
			} else {
				// Get the un-discounted price paid and adjust our product price
				$item_price = $order->get_item_subtotal( $item, true, true );
			}

			$product->set_price( $item_price );

			// Calc points earned
			$points_earned += apply_filters( 'woocommerce_points_earned_for_order_item', WC_Points_Rewards_Product::get_points_earned_for_product_purchase( $product, $order ), $product, $item_key, $item, $order ) * $item['qty'];
		}

		// reduce by any discounts.  One minor drawback: if the discount includes a discount on tax and/or shipping
		//  it will cost the customer points, but this is a better solution than granting full points for discounted orders
		$discount = $order->order_discount + $order->cart_discount;
		$points_earned -= min( WC_Points_Rewards_Manager::calculate_points( $discount ), $points_earned );

		// check if applied coupons have a points modifier and use it to adjust the points earned
		$coupons = $order->get_used_coupons();

		if ( ! empty( $coupons ) ) {

			$points_modifier = 0;

			// get the maximum points modifier if there are multiple coupons applied, each with their own modifier
			foreach ( $coupons as $coupon_code ) {

				$coupon = new WC_Coupon( $coupon_code );

				if ( ! empty( $coupon->coupon_custom_fields['_wc_points_modifier'][0] ) && $coupon->coupon_custom_fields['_wc_points_modifier'][0] > $points_modifier )
					$points_modifier = $coupon->coupon_custom_fields['_wc_points_modifier'][0];
			}

			if ( $points_modifier > 0 ) {
				$points_earned = round( $points_earned * ( $points_modifier / 100 ) );
			}
		}

		return apply_filters( 'wc_points_rewards_points_earned_for_purchase', $points_earned, $order );
	}

	/**
	 * Deducts the points redeemed for a discount when the order is processed at checkout. Note that points are deducted
	 * immediately upon checkout processing to protect against abuse.
	 *
	 * @since 1.0
	 * @param int $order_id the WC_Order ID
	 */
	public function maybe_deduct_redeemed_points( $order_id ) {
		global $wc_points_rewards;

		$order = new WC_Order( $order_id );

		// bail for guest user
		if ( ! $order->user_id ) {
			return;
		}

		$discount_code = WC_Points_Rewards_Discount::get_discount_code();

		// only deduct points if they were redeemed for a discount
		if ( ! WC()->cart->has_discount( $discount_code ) ) {
			return;
		}

		// Get amount of discount
		$discount_amount = 0;

		if ( isset( WC()->cart->coupon_discount_amounts[ $discount_code ] ) ) {
			$discount_amount += WC()->cart->coupon_discount_amounts[ $discount_code ];
		}
		if ( WC()->cart->prices_include_tax && isset( WC()->cart->coupon_discount_tax_amounts[ $discount_code ] ) ) {
			$discount_amount += WC()->cart->coupon_discount_tax_amounts[ $discount_code ];
		}

		$points_redeemed = WC_Points_Rewards_Manager::calculate_points_for_discount( $discount_amount );

		// deduct points
		WC_Points_Rewards_Manager::decrease_points( $order->user_id, $points_redeemed , 'order-redeem', array( 'discount_code' => $discount_code, 'discount_amount' => $discount_amount ), $order->id );

		update_post_meta( $order->id, '_wc_points_redeemed', $points_redeemed );

		// add order note
		$order->add_order_note( sprintf( __( '%d %s redeemed for a %s discount.', 'woocommerce-points-and-rewards' ), $points_redeemed, $wc_points_rewards->get_points_label( $points_redeemed ), woocommerce_price( $discount_amount ) ) );
	}


	/**
	 * Handle an order that is cancelled or refunded by:
	 *
	 * 1) Removing any points earned for the order
	 *
	 * 2) Crediting points redeemed for a discount back to the customer's account if the order that they redeemed the points
	 * for a discount on is cancelled or refunded
	 *
	 * @since 1.0
	 * @param int $order_id the WC_Order ID
	 */
	public function handle_cancelled_refunded_order( $order_id ) {

		global $wc_points_rewards;

		$order = new WC_Order( $order_id );

		// bail for guest user
		if ( ! $order->user_id )
			return;

		// handle removing any points earned for the order
		$points_earned = get_post_meta( $order->id, '_wc_points_earned', true );

		if ( $points_earned > 0 ) {

			// remove points
			WC_Points_Rewards_Manager::decrease_points( $order->user_id, $points_earned, 'order-cancelled', null, $order->id );

			// remove points from order
			delete_post_meta( $order->id, '_wc_points_earned' );

			// add order note
			$order->add_order_note( sprintf( __( '%d %s removed.', 'woocommerce-points-and-rewards' ), $points_earned, $wc_points_rewards->get_points_label( $points_earned ) ) );
		}

		// handle crediting points redeemed for a discount
		$points_redeemed = get_post_meta( $order->id, '_wc_points_redeemed', true );

		if ( $points_redeemed > 0 ) {

			// credit points
			WC_Points_Rewards_Manager::increase_points( $order->user_id, $points_redeemed, 'order-cancelled', null, $order->id );

			// remove points from order
			delete_post_meta( $order->id, '_wc_points_redeemed' );

			// add order note
			$order->add_order_note( sprintf( __( '%d %s credited back to customer.', 'woocommerce-points-and-rewards' ), $points_redeemed, $wc_points_rewards->get_points_label( $points_redeemed ) ) );
		}
	}


} // end \WC_Points_Rewards_Order class

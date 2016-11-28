<?php
/*
 * Plugin Name: WooCommerce Distance Rate Shipping
 * Version: 1.0.4
 * Plugin URI: http://www.woothemes.com/products/woocommerce-distance-rate-shipping/
 * Description: Set up shipping rates based on the distance from your store to the customer, as well as charge based on number of items, order total or time to travel to customer.
 * Author: Gerhard Potgieter
 * Author URI: http://gerhardpotgieter.com
 * Requires at least: 3.8
 * Tested up to: 3.9.1
 *
 * Copyright: 2014 Gerhard Potgieter.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), 'bbb6fc986fe0f074dcd5141d451b4821', '461314' );

/**
 * Text domain
 */
load_plugin_textdomain( 'wc-distance-rate', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/**
 * Check if WooCommerce is active
 */
if ( is_woocommerce_active() ) {

	/**
	 * wc_distance_rate_init function.
	 *
	 * @access public
	 * @return void
	 */
	function wc_distance_rate_init() {
		include_once( 'includes/class-wc-shipping-distance-rate.php' );
	}
	add_action( 'woocommerce_shipping_init', 'wc_distance_rate_init' );

	/**
	 * wc_distance_rate_add_method function.
	 *
	 * @access public
	 * @param mixed $methods
	 * @return array
	 */
	function wc_distance_rate_add_method( $methods ) {
		$methods[] = 'WC_Shipping_Distance_Rate';
		return $methods;
	}
	add_filter( 'woocommerce_shipping_methods', 'wc_distance_rate_add_method' );
}

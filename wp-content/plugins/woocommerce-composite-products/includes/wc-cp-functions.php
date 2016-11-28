<?php
/**
 * Composite Products Functions.
 *
 * @version 3.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*--------------------------*/
/*  Conditional functions.  */
/*--------------------------*/

/**
 * True if the current product page is a composite product.
 *
 * @return boolean
 */
function is_composite_product() {

	global $product;

	return function_exists( 'is_product' ) && is_product() && ! empty( $product ) && isset( $product->product_type ) && $product->product_type === 'composite' ? true : false;
}

/*----------------------------*/
/*  Helper functions.         */
/*----------------------------*/

/**
 * get_option( 'woocommerce_calc_taxes' ) cache.
 *
 * @return string
 */
function wc_cp_calc_taxes() {
	$wc_calc_taxes = WC_CP()->api->cache_get( 'wc_calc_taxes' );
	if ( null === $wc_calc_taxes ) {
		$wc_calc_taxes = get_option( 'woocommerce_calc_taxes' );
		WC_CP()->api->cache_set( 'wc_calc_taxes', $wc_calc_taxes );
	}
	return $wc_calc_taxes;
}

/**
 * get_option( 'woocommerce_prices_include_tax' ) cache.
 *
 * @return string
 */
function wc_cp_prices_include_tax() {
	$wc_prices_include_tax = WC_CP()->api->cache_get( 'wc_prices_include_tax' );
	if ( null === $wc_prices_include_tax ) {
		$wc_prices_include_tax = get_option( 'woocommerce_prices_include_tax' );
		WC_CP()->api->cache_set( 'wc_prices_include_tax', $wc_prices_include_tax );
	}
	return $wc_prices_include_tax;
}

/**
 * get_option( 'woocommerce_tax_display_shop' ) cache.
 *
 * @return string
 */
function wc_cp_tax_display_shop() {
	$wc_tax_display_shop = WC_CP()->api->cache_get( 'wc_tax_display_shop' );
	if ( null === $wc_tax_display_shop ) {
		$wc_tax_display_shop = get_option( 'woocommerce_tax_display_shop' );
		WC_CP()->api->cache_set( 'wc_tax_display_shop', $wc_tax_display_shop );
	}
	return $wc_tax_display_shop;
}

/**
 * get_option( 'woocommerce_price_decimal_sep' ) cache.
 *
 * @return string
 */
function wc_cp_price_decimal_sep() {
	$wc_price_decimal_sep = WC_CP()->api->cache_get( 'wc_price_decimal_sep' );
	if ( null === $wc_price_decimal_sep ) {
		$wc_price_decimal_sep = wp_specialchars_decode( stripslashes( get_option( 'woocommerce_price_decimal_sep' ) ), ENT_QUOTES );
		WC_CP()->api->cache_set( 'wc_price_decimal_sep', $wc_price_decimal_sep );
	}
	return $wc_price_decimal_sep;
}

/**
 * get_option( 'woocommerce_price_thousand_sep' ) cache.
 *
 * @return string
 */
function wc_cp_price_thousand_sep() {
	$wc_price_thousand_sep = WC_CP()->api->cache_get( 'wc_price_thousand_sep' );
	if ( null === $wc_price_thousand_sep ) {
		$wc_price_thousand_sep = wp_specialchars_decode( stripslashes( get_option( 'woocommerce_price_thousand_sep' ) ), ENT_QUOTES );
		WC_CP()->api->cache_set( 'wc_price_thousand_sep', $wc_price_thousand_sep );
	}
	return $wc_price_thousand_sep;
}

/**
 * get_option( 'woocommerce_price_num_decimals' ) cache.
 *
 * @return string
 */
function wc_cp_price_num_decimals() {
	$wc_price_num_decimals = WC_CP()->api->cache_get( 'wc_price_num_decimals' );
	if ( null === $wc_price_num_decimals ) {
		$wc_price_num_decimals = absint( get_option( 'woocommerce_price_num_decimals', 2 ) );
		WC_CP()->api->cache_set( 'wc_price_num_decimals', $wc_price_num_decimals );
	}
	return $wc_price_num_decimals;
}

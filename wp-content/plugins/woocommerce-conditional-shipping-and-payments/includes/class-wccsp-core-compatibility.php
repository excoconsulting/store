<?php
/**
 * WC_CSP_Core_Compatibility class
 *
 * @author   SomewhereWarm <sw@somewherewarm.net>
 * @package  WooCommerce Conditional Shipping and Payments
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Functions related to core back-compatibility.
 *
 * @class  WC_CSP_Core_Compatibility
 * @since  1.0.0
 */
class WC_CSP_Core_Compatibility {

	// Modified shipping method instance IDs during the WC 2.6 upgrade.
	public static $updated_shipping_method_instance_ids;
	// Shipping methods that got the 'lagacy' treatment in WC 2.6.
	public static $legacy_methods = array( 'flat_rate', 'free_shipping', 'international_delivery', 'local_delivery', 'local_pickup' );
	// Shipping methods IDs whose rate IDs changed after the WC 2.6 upgrade, for which CSP is providing back-compat.
	public static $upgraded_methods = array( 'table_rate', 'flat_rate_boxes' );

	public static function init() {

		self::$updated_shipping_method_instance_ids = get_option( 'woocommerce_updated_instance_ids', array() );

		if ( is_admin() ) {
			add_filter( 'woocommerce_enable_deprecated_additional_flat_rates', __CLASS__ . '::enable_deprecated_addon_flat_rates' );
		}
	}

	/**
	 * Clears cached shipping rates.
	 *
	 * @return void
	 */
	public static function clear_cached_shipping_rates() {
		global $wpdb;

		// WC 2.2 - WC 2.4: Rates cached as transients.
		$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('\_transient\_wc\_ship\_%') OR `option_name` LIKE ('\_transient\_timeout\_wc\_ship\_%')" );

		// WC 2.5: Rates cached in session.
		if ( self::is_wc_version_gte_2_5() ) {
			// Increments the shipping transient version to invalidate session entries.
			WC_Cache_Helper::get_transient_version( 'shipping', true );
		}
	}

	/**
	 * Enable deprecated Add-on flat rate options panel.
	 *
	 * @param  boolean $enable
	 * @return boolean
	 */
	public static function enable_deprecated_addon_flat_rates( $enable ) {
		return true;
	}

	/**
	 * Display a WooCommerce help tip.
	 *
	 * @since  1.2.0
	 *
	 * @param  string $tip        Help tip text
	 * @return string
	 */
	public static function wc_help_tip( $tip ) {

		if ( self::is_wc_version_gte_2_5() ) {
			return wc_help_tip( $tip );
		} else {
			return '<img class="help_tip woocommerce-help-tip" data-tip="' . $tip . '" src="' . WC()->plugin_url() . '/assets/images/help.png" />';
		}
	}

	/**
	 * Get the WC Product instance for a given product ID or post.
	 *
	 * get_product() is soft-deprecated in WC 2.2
	 *
	 * @since  1.0.4
	 * @param  bool|int|string|\WP_Post $the_product
	 * @param  array $args
	 * @return WC_Product
	 */
	public static function wc_get_product( $the_product = false, $args = array() ) {

		if ( self::is_wc_version_gte_2_2() ) {

			return wc_get_product( $the_product, $args );

		} else {

			return get_product( $the_product, $args );
		}
	}

	/**
	 * Helper method to get the version of the currently installed WooCommerce.
	 *
	 * @since  1.0.4
	 * @return string woocommerce version number or null
	 */
	private static function get_wc_version() {

		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.6 or greater.
	 *
	 * @since  1.1.12
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_6() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '2.6', '>=' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.5 or greater.
	 *
	 * @since  1.1.11
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_5() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '2.5', '>=' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.3 or greater.
	 *
	 * @since  1.0.4
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_3() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '2.3', '>=' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.2 or greater.
	 *
	 * @since  1.0.4
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_2() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '2.2', '>=' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is less than 2.2.
	 *
	 * @since  1.0.4
	 * @return boolean true
	 */
	public static function is_wc_version_lt_2_2() {
		return self::get_wc_version() && version_compare( self::get_wc_version(), '2.2', '<' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than $version.
	 *
	 * @since  1.0.4
	 * @param  string $version
	 * @return boolean
	 */
	public static function is_wc_version_gt( $version ) {
		return self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>' );
	}
}

WC_CSP_Core_Compatibility::init();

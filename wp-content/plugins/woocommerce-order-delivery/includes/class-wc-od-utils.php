<?php
/**
 * Utils class
 *
 * @author     WooThemes
 * @package    WC_OD
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_OD_Utils' ) ) {

	class WC_OD_Utils {

		private static $active_plugins;

		public function __construct() {}

		/**
		 * Gets if the plugin is active.
		 *
		 * @since 1.0.0
		 *
		 * @param string $plugin Base plugin path from plugins directory.
		 * @return boolean True if the plugin is active. False otherwise.
		 */
		public static function is_plugin_active( $plugin ) {
			if ( ! self::$active_plugins ) {
				self::$active_plugins = (array) get_option( 'active_plugins', array() );
				if ( is_multisite() ) {
					self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
				}
			}

			return in_array( $plugin, self::$active_plugins ) || array_key_exists( $plugin, self::$active_plugins );
		}

		/**
		 * Gets the WooCommerce version.
		 *
		 * @since 1.0.0
		 *
		 * @global WooCommerce $woocommerce The WooCommerce instance.
		 *
		 * @return string The WooCommerce version.
		 */
		public static function get_woocommerce_version() {
			global $woocommerce;

			return $woocommerce->version;
		}

		/**
		 * Gets the menu slug for the WooCommerce settings page.
		 *
		 * @since 1.0.0
		 *
		 * @return string The menu slug for the WooCommerce settings page.
		 */
		public static function get_woocommerce_settings_page_slug() {
			return 'wc-settings';
		}

		/**
		 * Gets if we are in the WooCommerce settings page or not.
		 *
		 * @since 1.0.0
		 *
		 * @return boolean Are we in the WooCommerce settings page?
		 */
		public static function is_woocommerce_settings_page() {
			return ( is_admin() && isset( $_GET['page'] ) && $_GET['page'] === self::get_woocommerce_settings_page_slug() );
		}

		/**
		 * Gets the section slug for the shipping options.
		 *
		 * @since 1.0.2
		 *
		 * @return string The section slug for the shipping options.
		 */
		public static function get_shipping_options_section_slug() {
			return ( version_compare( self::get_woocommerce_version(), '2.6', '<' ) ? '' : 'options' );
		}
	}
}

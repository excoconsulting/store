<?php
/**
 * class-woocommerce-groupons.php
 *
 * Copyright (c) "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is provided subject to the license granted.
 * Unauthorized use and distribution is prohibited.
 * See COPYRIGHT.txt and LICENSE.txt
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This header and all notices must be kept intact.
 *
 * @author Karim Rahimpur
 * @package woocommerce-groupons
 * @since woocommerce-groupons 1.0.0
 */

/**
 * Boots.
 */
class WooCommerce_Groupons {

	private static $admin_messages = array();

	/**
	 * Put hooks in place and activate.
	 */
	public static function init() {
		//register_activation_hook( WOO_GROUPONS_FILE, array( __CLASS__, 'activate' ) );
		//register_deactivation_hook( WOO_GROUPONS_FILE, array( __CLASS__, 'deactivate' ) );
		//register_uninstall_hook( WOO_GROUPONS_FILE, array( __CLASS__, 'uninstall' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		add_action( 'init', array( __CLASS__, 'wp_init' ) );
		if ( self::check_dependencies() ) {
			require_once( WOO_GROUPONS_CORE_LIB . '/constants.php');
			$options = get_option( 'woocommerce-groupons', null );
			$enable_roles = isset( $options[WOO_GROUPONS_ENABLE_ROLES] ) ? $options[WOO_GROUPONS_ENABLE_ROLES] : WOO_GROUPONS_ENABLE_ROLES_DEFAULT;
			require_once( WOO_GROUPONS_CORE_LIB . '/class-woocommerce-groupon.php');
			if ( $enable_roles ) {
				require_once( WOO_GROUPONS_CORE_LIB . '/class-woocommerce-group-coupon-roles.php');
			}
			if ( is_admin() ) {
				require_once( WOO_GROUPONS_ADMIN_LIB . '/class-woocommerce-groupons-admin.php');
				require_once( WOO_GROUPONS_ADMIN_LIB . '/class-woocommerce-groupons-coupons-admin.php');
				if ( $enable_roles ) {
					require_once( WOO_GROUPONS_ADMIN_LIB . '/class-woocommerce-groupons-coupons-roles-admin.php');
				}
			}
			require_once( WOO_GROUPONS_VIEWS_LIB . '/class-woocommerce-groupons-shortcodes.php');
			$logout_remove_coupons = isset( $options[WOO_GROUPONS_LOGOUT_REMOVE_COUPONS] ) ? $options[WOO_GROUPONS_LOGOUT_REMOVE_COUPONS] : WOO_GROUPONS_LOGOUT_REMOVE_COUPONS_DEFAULT;
			if ( $logout_remove_coupons ) {
				add_action( 'wp_logout', array( __CLASS__, 'wp_logout' ) );
			}
		}
	}

	/**
	 * Loads translations.
	 */
	public static function wp_init() {
		load_plugin_textdomain( WOO_GROUPONS_PLUGIN_DOMAIN, null, 'woocommerce-group-coupons/languages' );
	}

	/**
	 * Avoid dangling coupons.
	 * These appear on orders with 0 discount - WooCommerce 2.0.10 when a coupon
	 * has been applied and the user logs out and then checks out.
	 */
	public static function wp_logout() {
		global $woocommerce;
		if ( isset( $woocommerce ) && isset( $woocommerce->cart ) ) {
			$woocommerce->cart->remove_coupons();
		}
	}

	/**
	 * Activate plugin.
	 * Reschedules pending tasks.
	 * @param boolean $network_wide
	 */
	public static function activate( $network_wide = false ) {
	}

	/**
	 * Deactivate plugin.
	 * @param boolean $network_wide
	 */
	public static function deactivate( $network_wide = false ) {
	}

	/**
	 * Uninstall plugin.
	 */
	public static function uninstall() {
	}

	/**
	 * Prints admin notices.
	 */
	public static function admin_notices() {
		if ( !empty( self::$admin_messages ) ) {
			foreach ( self::$admin_messages as $msg ) {
				echo $msg;
			}
		}
	}

	/**
	 * Check plugin dependencies and nag if they are not met.
	 * @param boolean $disable disable the plugin if true, defaults to false
	 */
	public static function check_dependencies( $disable = false ) {
		$result = true;
		$active_plugins = get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_sitewide_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$active_sitewide_plugins = array_keys( $active_sitewide_plugins );
			$active_plugins = array_merge( $active_plugins, $active_sitewide_plugins );
		}
		$groups_is_active = in_array( 'groups/groups.php', $active_plugins );
		define( 'WOO_GROUPONS_GROUPS_IS_ACTIVE', $groups_is_active );
		$woocommerce_is_active = in_array( 'woocommerce/woocommerce.php', $active_plugins );
		if ( !$woocommerce_is_active ) {
			self::$admin_messages[] = "<div class='error'>" . __( '<em>WooCommerce Groupons</em> needs the <a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a> plugin. Please install and activate it.', WOO_GROUPONS_PLUGIN_DOMAIN ) . "</div>";
		}
		if ( !$woocommerce_is_active ) {
			if ( $disable ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				deactivate_plugins( array( __FILE__ ) );
			}
			$result = false;
		}
		return $result;
	}
}
WooCommerce_Groupons::init();

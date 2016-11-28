<?php
/*
* Plugin Name: WooCommerce Product Bundles
* Plugin URI: http://www.woocommerce.com/products/product-bundles/
* Description: WooCommerce extension for creating simple product bundles, kits and assemblies.
* Version: 4.14.7
* Author: WooThemes
* Author URI: http://woocommerce.com/
* Developer: SomewhereWarm
* Developer URI: http://somewherewarm.net/
*
* Text Domain: woocommerce-product-bundles
* Domain Path: /languages/
*
* Requires at least: 4.1
* Tested up to: 4.5
*
* Copyright: © 2009-2015 Emmanouil Psychogyiopoulos.
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Required functions.
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/*
 * Plugin updates.
 */
woothemes_queue_update( plugin_basename( __FILE__ ), 'fbca839929aaddc78797a5b511c14da9', '18716' );

/*
 * WC active check.
 */
if ( ! is_woocommerce_active() ) {
	return;
}

/**
 * # Product Bundles
 *
 * This extension implements bundling functionalities by utilizing a container product (the "bundle" type) that triggers the addition of other products to the cart.
 * The extension does its own validation on the container product in order to ensure that all "bundled products" can be added to the cart.
 * Bundled products are added on the woocommerce_add_to_cart hook after adding the main container item.
 * Using a main container item makes it possible to define pricing properties and/or physical properties that replace the pricing and/or physical properties of the bundled products. This is useful when the bundle has a new static price and/or new shipping properties.
 * Depending on the chosen pricing / shipping mode, the container item OR the bundled products are marked as virtual, or are assigned a zero price in the cart.
 * To avoid confusion with zero prices in the front end, the extension filters the displayed price strings, cart item meta and markup classes in order to give the impression of a bundling relationship between the container item and the 'children' items.
 *
 * @class 	WC_Bundles
 * @version 4.14.7
 */
class WC_Bundles {

	public $version     = '4.14.7';
	public $required    = '2.3.0';

	public $admin;
	public $helpers;
	public $cart;
	public $order;
	public $display;
	public $compatibility;

	/**
	 * @var WC_Bundles - the single instance of the class.
	 *
	 * @since 4.11.4
	 */
	protected static $_instance = null;

	/**
	 * Main WC_Bundles instance.
	 *
	 * Ensures only one instance of WC_Bundles is loaded or can be loaded.
	 *
	 * @static
	 * @see    WC_PB()
	 * @return WC_Bundles - Main instance
	 * @since  4.11.4
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 4.11.4
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'woocommerce-product-bundles' ), '4.11.4' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 4.11.4
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'woocommerce-product-bundles' ), '4.11.4' );
	}

	/**
	 * Make stuff.
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	/**
	 * Plugin URL getter.
	 *
	 * @return string
	 */
	public function woo_bundles_plugin_url() {
		return plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
	}

	/**
	 * Plugin path getter.
	 *
	 * @return string
	 */
	public function woo_bundles_plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Fire in the hole!
	 *
	 * @return void
	 */
	public function plugins_loaded() {

		global $woocommerce, $woocommerce_composite_products;

		if ( is_admin() ) {
			// Admin notices handling.
			require_once( 'includes/admin/class-wc-pb-admin-notices.php' );
		}

		// WC version check.
		if ( version_compare( $woocommerce->version, $this->required ) < 0 ) {
			add_action( 'admin_notices', array( $this, 'wc_admin_notice' ) );
			return false;
		}

		// Class containing core compatibility functions and filters.
		require_once( 'includes/class-wc-pb-core-compatibility.php' );

		// Functions (incl deprecated).
		include( 'includes/wc-pb-functions.php' );
		include( 'includes/wc-pb-deprecated-functions.php' );

		// Class containing helper functions and filters.
		require_once( 'includes/class-wc-pb-helpers.php' );
		$this->helpers = new WC_PB_Helpers();

		// Class containing extenstions compatibility functions and filters.
		require_once( 'includes/class-wc-pb-compatibility.php' );
		$this->compatibility = new WC_PB_Compatibility();

		// WC_Bundled_Item and WC_Product_Bundle classes.
		require_once( 'includes/class-wc-bundled-item.php' );
		require_once( 'includes/class-wc-product-bundle.php' );

		require_once( 'includes/class-wc-pb-stock-manager.php' );

		// Admin functions and meta-boxes.
		if ( is_admin() ) {
			$this->admin_includes();
		}

		// Cart-related bundle functions and filters.
		require_once( 'includes/class-wc-pb-cart.php' );
		$this->cart = new WC_PB_Cart();

		// Order-related bundle functions and filters.
		require_once( 'includes/class-wc-pb-order.php' );
		$this->order = new WC_PB_Order();

		// Front-end filters and templates.
		require_once( 'includes/class-wc-pb-display.php' );
		$this->display = new WC_PB_Display();
	}

	/**
	 * Loads the Admin & AJAX filters / hooks.
	 *
	 * @return void
	 */
	public function admin_includes() {

		require_once( 'includes/admin/class-wc-pb-admin.php' );
		$this->admin = new WC_PB_Admin();
	}

	/**
	 * Display a warning message if WC version check fails.
	 *
	 * @return void
	 */
	public function wc_admin_notice() {

	    echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Product Bundles requires at least WooCommerce %s in order to function. Please upgrade WooCommerce.', 'woocommerce-product-bundles' ), $this->required ) . '</p></div>';
	}

	/**
	 * Load textdomain.
	 *
	 * @return void
	 */
	public function init() {

		load_plugin_textdomain( 'woocommerce-product-bundles', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Update or create 'bundle' product type on activation as required.
	 *
	 * @return void
	 */
	public function activate() {

		global $wpdb;

		$version = get_option( 'woocommerce_product_bundles_version', false );

		if ( $version == false ) {

			// Create 'bundle' product type on activation as required.

			if ( ! get_term_by( 'slug', 'bundle', 'product_type' ) ) {
				wp_insert_term( 'bundle', 'product_type' );
			}

			add_option( 'woocommerce_product_bundles_version', $this->version );

			// Update from previous versions.

			// Delete old option.
			delete_option( 'woocommerce_product_bundles_active' );

			// Delete old transients.
			$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_wc_bundled_item_%') OR `option_name` LIKE ('_transient_timeout_wc_bundled_item_%')" );

		} elseif ( version_compare( $version, $this->version, '<' ) ) {

			update_option( 'woocommerce_product_bundles_version', $this->version );
		}
	}

	/**
	 * Deactivate extension.
	 *
	 * @return void
	 */
	public function deactivate() {
		delete_option( 'woocommerce_product_bundles_version' );
	}
}

/**
 * Returns the main instance of WC_Bundles to prevent the need to use globals.
 *
 * @since  4.11.4
 * @return WooCommerce Product Bundles
 */
function WC_PB() {
  return WC_Bundles::instance();
}

$GLOBALS[ 'woocommerce_bundles' ] = WC_PB();

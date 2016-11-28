<?php
/*
* Plugin Name: WooCommerce Composite Products
* Plugin URI: http://www.woothemes.com/products/composite-products/
* Description: Create complex, configurable product kits and let your customers build their own, personalized versions.
* Version: 3.6.9
* Author: WooThemes
* Author URI: http://woothemes.com/
* Developer: SomewhereWarm
* Developer URI: http://somewherewarm.net/
*
* Text Domain: woocommerce-composite-products
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

// Required functions.
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

// Plugin updates.
woothemes_queue_update( plugin_basename( __FILE__ ), '0343e0115bbcb97ccd98442b8326a0af', '216836' );

// Check if WooCommerce is active.
if ( ! is_woocommerce_active() ) {
	return;
}

/**
 * # Composite Products
 *
 * This extension implements dynamic bundling functionalities by utilizing a container product (the "composite" type) that triggers the addition of other products to the cart.
 * Composite Products consist of Components. Components are created by defining a set of Component Options. Any existing catalog product (simple, variable or bundle) can be selected as a Component Option.
 *
 * A Composite Product can be added to the cart when all of its Components are configured.
 * The extension does its own validation to ensure that the selected "Composited Products" can be added to the cart.
 * Composited products are added on the 'woocommerce_add_to_cart' hook after adding the main container item.
 *
 * Using a main container item makes it possible to define pricing properties and/or physical properties that replace the pricing and/or physical properties of the bundled products. This is useful when the composite has a new static price and/or new shipping properties.
 * Depending on the chosen pricing / shipping mode, the container item OR the contained products are marked as virtual, or are assigned a zero price in the cart.
 * To avoid confusion with zero prices in the front end, the extension filters the displayed price strings, cart item meta and markup classes in order to give the impression of a grouping relationship between the container and its "contents".
 *
 * The font-end UX is entirely JS-driven. To strike a good balance between [ swift front-end UX ] / [ low server load ], all component content is pre-loaded on the first page load. However, product content such as extra fields and availability are loaded via ajax when a product selection is made.
 * Additionally, when viewing Component Options as thumbnails, the extension relies on WP_Query-based pagination to minimize the server load.
 *
 * By default, the product type will carry out a min-price calculation on instantiation, in order to display the cheapest configuration price.
 * For the lowest possible server load when compositing 100s or 1000nds of products, the extension provides a "Hide Price" option in the "General" Product Data tab, which prevents all composited products from being instantiated on product init.
 * In this case, if a min config price must be displayed, it is recommended to use a 'price_html' filter to manually define the cheapest config price string. For instance, @see https://gist.github.com/franticpsyx/a5cfc877e594f3642c3b .
 *
 * @class WC_Composite_Products
 * @author  SomewhereWarm
 * @version 3.6.9
 */

class WC_Composite_Products {

	public $version     = '3.6.9';
	public $required    = '2.3.0';

	public $admin;
	public $api;
	public $cart;
	public $order;
	public $display;
	public $compatibility;

	/**
	 * @var WC_Composite_Products - the single instance of the class.
	 *
	 * @since 3.2.3
	 */
	protected static $_instance = null;

	/**
	 * Main WC_Composite_Products instance.
	 *
	 * Ensures only one instance of WC_Composite_Products is loaded or can be loaded.
	 *
	 * @static
	 * @see WC_CP()
	 * @return WC_Composite_Products - Main instance
	 * @since 3.2.3
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
	 * @since 3.2.3
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'woocommerce-composite-products' ), '3.2.3' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 3.2.3
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'woocommerce-composite-products' ), '3.2.3' );
	}

	/**
	 * Contructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_meta_links' ), 10 ,2 );
	}

	/**
	 * Gets the plugin url.
	 *
	 * @since 1.0.0
	 */
	public function plugin_url() {
		return plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
	}

	/**
	 * Gets the plugin path.
	 *
	 * @since 1.0.0
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Fires things up.
	 *
	 * @since 1.0.0
	 */
	public function plugins_loaded() {

		global $woocommerce;

		if ( is_admin() ) {
			// Admin notices handling.
			require_once( 'includes/admin/class-wc-cp-admin-notices.php' );
		}

		// WC version check.
		if ( version_compare( $woocommerce->version, $this->required ) < 0 ) {
			$notice = sprintf( __( 'WooCommerce Composite Products requires at least WooCommerce %s in order to function. Please upgrade WooCommerce.', 'woocommerce-composite-products' ), $this->required_pb );
			WC_CP_Admin_Notices::add_notice( $notice, 'error' );
			return false;
		}

		// Class containing core compatibility functions and filters.
		require_once( 'includes/class-wc-cp-core-compatibility.php' );

		// CP functions.
		include_once( 'includes/wc-cp-functions.php' );
		include_once( 'includes/wc-cp-deprecated-functions.php' );

		// Composite widget.
		include_once( 'includes/wc-cp-widget-functions.php' );

		// Class containing extensions compatibility functions and filters.
		require_once( 'includes/class-wc-cp-compatibility.php' );
		$this->compatibility = new WC_CP_Compatibility();

		// WP_Query wrapper for component option queries.
		require_once( 'includes/class-wc-cp-query.php' );

		// Composited product wrapper.
		require_once( 'includes/class-wc-cp-product.php' );

		// Composite products API.
		require_once( 'includes/class-wc-cp-api.php' );
		$this->api = new WC_CP_API();

		// Composite products Scenarios API.
		require_once( 'includes/class-wc-cp-scenarios.php' );

		// Helper functions.
		require_once( 'includes/class-wc-cp-helpers.php' );

		// Composite products AJAX handlers.
		require_once( 'includes/class-wc-cp-ajax.php' );

		// Composite product class.
		require_once( 'includes/class-wc-product-composite.php' );

		// Stock manager
		require_once( 'includes/class-wc-cp-stock-manager.php' );

		// Admin functions and meta-boxes.
		if ( is_admin() ) {
			$this->admin_includes();
		}

		// Cart-related functions and filters.
		require_once( 'includes/class-wc-cp-cart.php' );
		$this->cart = new WC_CP_Cart();

		// Order-related functions and filters.
		require_once( 'includes/class-wc-cp-order.php' );
		$this->order = new WC_CP_Order();

		// Front-end functions and filters.
		require_once( 'includes/class-wc-cp-display.php' );
		$this->display = new WC_CP_Display();
	}

	/**
	 * Loads the Admin filters / hooks.
	 *
	 * @return void
	 */
	private function admin_includes() {

		require_once( 'includes/admin/class-wc-cp-admin.php' );
		$this->admin = new WC_CP_Admin();
	}

	/**
	 * Load textdomain.
	 *
	 * @return void
	 */
	public function init() {

		load_plugin_textdomain( 'woocommerce-composite-products', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Update or create 'Composite' product type on activation as required.
	 *
	 * @return void
	 */
	public function activate() {

		global $wpdb;

		$version = get_option( 'woocommerce_composite_products_version', false );

		if ( $version == false ) {

			// Create 'composite' product type on activation as required.

			if ( ! get_term_by( 'slug', 'composite', 'product_type' ) ) {
				wp_insert_term( 'composite', 'product_type' );
			}

			add_option( 'woocommerce_composite_products_version', $this->version );

			// Update from previous versions.

			// Delete old option.
			delete_option( 'woocommerce_composite_products_active' );

		} elseif ( version_compare( $version, $this->version, '<' ) ) {

			update_option( 'woocommerce_composite_products_version', $this->version );
		}

	}

	/**
	 * Deactivate extension.
	 *
	 * @return void
	 */
	public function deactivate() {

		delete_option( 'woocommerce_composite_products_version' );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param	mixed $links Plugin Row Meta
	 * @param	mixed $file  Plugin Base file
	 * @return	array
	 */
	public function plugin_meta_links( $links, $file ) {

		if ( $file == plugin_basename( __FILE__ ) ) {
			$links[] ='<a href="http://docs.woothemes.com/document/composite-products/">' . __( 'Docs', 'woocommerce-composite-products' ) . '</a>';
			$links[] = '<a href="http://support.woothemes.com/">' . __( 'Support', 'woocommerce-composite-products' ) . '</a>';
		}

		return $links;
	}
}

/**
 * Returns the main instance of WC_Composite_Products to prevent the need to use globals.
 *
 * @since  3.2.3
 * @return WooCommerce Composite Products
 */
function WC_CP() {

  return WC_Composite_Products::instance();
}

$GLOBALS[ 'woocommerce_composite_products' ] = WC_CP();

<?php
/**
 * Functions related to extension cross-compatibility.
 *
 * @class    WC_PB_Compatibility
 * @version  4.14.0
 * @since    4.6.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_PB_Compatibility {

	private $required = array();

	public static $addons_prefix          = '';
	public static $nyp_prefix             = '';
	public static $bundle_prefix          = '';

	public static $compat_product         = '';
	public static $compat_bundled_product = '';

	public static $stock_data;

	public function __construct() {

		$this->required = array(
			'cp'     => '3.6.0',
			'addons' => '2.7.16'
		);

		if ( is_admin() ) {
			// Check plugin min versions.
			add_action( 'admin_init', array( $this, 'check_required_versions' ) );
		}

		// Initialize.
		add_action( 'plugins_loaded', array( $this, 'init' ), 100 );
	}

	/**
	 * Init compatibility classes.
	 *
	 * @return void
	 */
	public function init() {

		// Addons and NYP support.
		require_once( 'compatibility/class-wc-addons-compatibility.php' );

		// Points and Rewards support.
		if ( class_exists( 'WC_Points_Rewards_Product' ) ) {
			require_once( 'compatibility/class-wc-pnr-compatibility.php' );
		}

		// Pre-orders support.
		if ( class_exists( 'WC_Pre_Orders' ) ) {
			require_once( 'compatibility/class-wc-po-compatibility.php' );
		}

		// Composite Products support.
		if ( class_exists( 'WC_Composite_Products' ) ) {
			require_once( 'compatibility/class-wc-cp-compatibility.php' );
		}

		// One Page Checkout support.
		if ( function_exists( 'is_wcopc_checkout' ) ) {
			require_once( 'compatibility/class-wc-opc-compatibility.php' );
		}

		// Cost of Goods support.
		if ( class_exists( 'WC_COG' ) ) {
			require_once( 'compatibility/class-wc-cog-compatibility.php' );
		}

		// QuickView support.
		if ( class_exists( 'WC_Quick_View' ) ) {
			require_once( 'compatibility/class-wc-qv-compatibility.php' );
		}

		// PIP support.
		if ( class_exists( 'WC_PIP' ) ) {
			require_once( 'compatibility/class-wc-pip-compatibility.php' );
		}

		// Subscriptions fixes.
		if ( class_exists( 'WC_Subscriptions' ) ) {
			require_once( 'compatibility/class-wc-subscriptions-compatibility.php' );
		}

		// Shipstation integration.
		require_once( 'compatibility/class-wc-shipstation-compatibility.php' );
	}

	/**
	 * Checks minimum required versions of compatible/integrated extensions.
	 *
	 * @return void
	 */
	public function check_required_versions() {

		global $woocommerce_composite_products;

		// PB version check.
		if ( ! empty( $woocommerce_composite_products ) && version_compare( $woocommerce_composite_products->version, $this->required[ 'cp' ] ) < 0 ) {
			$notice = sprintf( __( '<strong>WooCommerce Product Bundles</strong> is not compatible with the <strong>WooCommerce Composite Products</strong> version found on your system. Please update <strong>WooCommerce Composite Products</strong> to version <strong>%s</strong> or higher.', 'woocommerce-composite-products' ), $this->required[ 'cp' ] );
			WC_PB_Admin_Notices::add_notice( $notice, 'warning' );
		}

		// Addons version check.
		if ( class_exists( 'WC_Product_Addons' ) ) {
			$reflector   = new ReflectionClass( 'WC_Product_Addons' );
			$file        = $reflector->getFileName();
			$addons_data = get_plugin_data( $file, false, false );
			$version     = $addons_data[ 'Version' ];
			if ( version_compare( $version, $this->required[ 'addons' ] ) < 0 ) {
				$notice = sprintf( __( '<strong>WooCommerce Product Bundles</strong> is not compatible with the <strong>WooCommerce Product Addons</strong> version found on your system. Please update <strong>WooCommerce Product Addons</strong> to version <strong>%s</strong> or higher.', 'woocommerce-composite-products' ), $this->required[ 'addons' ] );
				WC_PB_Admin_Notices::add_notice( $notice, 'warning' );
			}
		}
	}

	/**
	 * Tells if a product is a Name Your Price product, provided that the extension is installed.
	 *
	 * @param  mixed    $product_id   product or id to check
	 * @return boolean                true if NYP exists and product is a NYP
	 */
	public function is_nyp( $product_id ) {

		if ( ! class_exists( 'WC_Name_Your_Price_Helpers' ) ) {
			return false;
		}

		if ( WC_Name_Your_Price_Helpers::is_nyp( $product_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Tells if a product is a subscription, provided that Subs is installed.
	 *
	 * @param  mixed    $product_id   product or id to check
	 * @return boolean                true if Subs exists and product is a Sub
	 */
	public function is_subscription( $product_id ) {

		if ( ! class_exists( 'WC_Subscriptions' ) ) {
			return false;
		}

		return WC_Subscriptions_Product::is_subscription( $product_id );
	}

	/**
	 * Tells if an order item is a subscription, provided that Subs is installed.
	 *
	 * @param  mixed      $order   order to check
	 * @param  WC_Prder   $order   item to check
	 * @return boolean             true if Subs exists and item is a Sub
	 */
	public function is_item_subscription( $order, $item ) {

		if ( ! class_exists( 'WC_Subscriptions_Order' ) ) {
			return false;
		}

		return WC_Subscriptions_Order::is_item_subscription( $order, $item );
	}

	/**
	 * Checks if a product has any required addons.
	 *
	 * @param  int       $product_id   id of product to check
	 * @return boolean                 result
	 */
	public function has_required_addons( $product_id ) {

		if ( ! function_exists( 'get_product_addons' ) ) {
			return false;
		}

		$addons = get_product_addons( $product_id, false, false, true );

		if ( $addons && ! empty( $addons ) ) {
			foreach ( $addons as $addon ) {
				if ( '1' == $addon[ 'required' ] ) {
					return true;
				}
			}
		}

		return false;
	}
}

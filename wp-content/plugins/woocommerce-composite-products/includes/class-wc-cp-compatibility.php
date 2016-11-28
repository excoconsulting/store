<?php
/**
 * 3rd-party Extensions Compatibility.
 *
 * @class    WC_CP_Compatibility
 * @version  3.6.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_CP_Compatibility {

	private $required = array();

	public function __construct() {

		$this->required = array(
			'pb'     => '4.14.0',
			'cc'     => '1.1.0',
			'cq'     => '1.1.0',
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

		// Product Bundles support.
		if ( class_exists( 'WC_Bundles' ) ) {
			require_once( 'compatibility/class-wc-pb-compatibility.php' );
		}

		// One Page Checkout support.
		if ( function_exists( 'is_wcopc_checkout' ) ) {
			require_once( 'compatibility/class-wc-opc-compatibility.php' );
		}

		// Cost of Goods support.
		if ( class_exists( 'WC_COG' ) ) {
			require_once( 'compatibility/class-wc-cog-compatibility.php' );
		}

		// Shipstation integration.
		require_once( 'compatibility/class-wc-shipstation-compatibility.php' );

		// QuickView support.
		if ( class_exists( 'WC_Quick_View' ) ) {
			require_once( 'compatibility/class-wc-qv-compatibility.php' );
		}

		// WC Quantity Increment support.
		if ( class_exists( 'WooCommerce_Quantity_Increment' ) ) {
			require_once( 'compatibility/class-wc-qi-compatibility.php' );
		}

		// PIP support.
		if ( class_exists( 'WC_PIP' ) ) {
			require_once( 'compatibility/class-wc-pip-compatibility.php' );
		}

		// Subscriptions fixes.
		if ( class_exists( 'WC_Subscriptions' ) ) {
			require_once( 'compatibility/class-wc-subscriptions-compatibility.php' );
		}
	}

	/**
	 * Checks minimum required versions of compatible/integrated extensions.
	 *
	 * @return void
	 */
	public function check_required_versions() {

		global $woocommerce_bundles;

		// PB version check.
		if ( ! empty( $woocommerce_bundles ) && version_compare( $woocommerce_bundles->version, $this->required[ 'pb' ] ) < 0 ) {
			$notice = sprintf( __( '<strong>WooCommerce Composite Products</strong> is not compatible with the <strong>WooCommerce Product Bundles</strong> version found on your system. Please update <strong>WooCommerce Product Bundles</strong> to version <strong>%s</strong> or higher.', 'woocommerce-composite-products' ), $this->required[ 'pb' ] );
			WC_CP_Admin_Notices::add_notice( $notice, 'warning' );
		}

		// CC version check.
		if ( class_exists( 'WC_CP_Scenario_Action_Conditional_Components' ) && version_compare( WC_CP_Scenario_Action_Conditional_Components::$version, $this->required[ 'cc' ] ) < 0 ) {
			$notice = sprintf( __( '<strong>WooCommerce Composite Products</strong> is not compatible with the <strong>WooCommerce Composite Products - Conditional Components</strong> version found on your system. Please update <strong>WooCommerce Composite Products - Conditional Components</strong> to version <strong>%s</strong> or higher.', 'woocommerce-composite-products' ), $this->required[ 'cc' ] );
			WC_CP_Admin_Notices::add_notice( $notice, 'warning' );
		}

		// CQ version check.
		if ( class_exists( 'WC_CP_Scenario_Action_Override_Qty' ) && version_compare( WC_CP_Scenario_Action_Override_Qty::$version, $this->required[ 'cq' ] ) < 0 ) {
			$notice = sprintf( __( '<strong>WooCommerce Composite Products</strong> is not compatible with the <strong>WooCommerce Composite Products - Conditional Quantities</strong> version found on your system. Please update <strong>WooCommerce Composite Products - Conditional Quantities</strong> to version <strong>%s</strong> or higher.', 'woocommerce-composite-products' ), $this->required[ 'cq' ] );
			WC_CP_Admin_Notices::add_notice( $notice, 'warning' );
		}

		// Addons version check.
		if ( class_exists( 'WC_Product_Addons' ) ) {
			$reflector   = new ReflectionClass( 'WC_Product_Addons' );
			$file        = $reflector->getFileName();
			$addons_data = get_plugin_data( $file, false, false );
			$version     = $addons_data[ 'Version' ];
			if ( version_compare( $version, $this->required[ 'addons' ] ) < 0 ) {
				$notice = sprintf( __( '<strong>WooCommerce Composite Products</strong> is not compatible with the <strong>WooCommerce Product Addons</strong> version found on your system. Please update <strong>WooCommerce Product Addons</strong> to version <strong>%s</strong> or higher.', 'woocommerce-composite-products' ), $this->required[ 'addons' ] );
				WC_CP_Admin_Notices::add_notice( $notice, 'warning' );
			}
		}
	}

	/**
	 * Tells if a product is a Name Your Price product, provided that the extension is installed.
	 *
	 * @param  mixed    $product      product or id to check
	 * @return boolean                true if NYP exists and product is a NYP
	 */
	public function is_nyp( $product ) {

		if ( ! class_exists( 'WC_Name_Your_Price_Helpers' ) ) {
			return false;
		}

		if ( WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {
			return true;
		}

		return false;
	}
}

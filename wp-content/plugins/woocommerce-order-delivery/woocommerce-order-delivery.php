<?php
/**
 * Plugin Name: WooCommerce Order Delivery
 * Plugin URI: http://woothemes.com/products/woocommerce-order-delivery/
 * Description: Choose a delivery date during checkout for the order.
 * Version: 1.0.2
 * Author: WooThemes
 * Author URI: http://woothemes.com/
 * Developer: Themesquad
 * Developer URI: http://themesquad.com/
 * Requires at least: 3.8
 * Tested up to: 4.5.2
 *
 * Text Domain: woocommerce-order-delivery
 * Domain Path: /languages/
 *
 * Copyright: © 2009-2016 WooThemes.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @author     WooThemes
 * @package    WC_OD
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), 'beaa91b8098712860ec7335d3dca61c0', '976514' );

if ( ! class_exists( 'WC_Order_Delivery' ) ) {

	final class WC_Order_Delivery {

		/**
		 * The plugin version.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var string
		 */
		public $version = '1.0.2';

		/**
		 * The plugin directory path.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var string
		 */
		public $dir_path;

		/**
		 * The plugin URL path.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var string
		 */
		public $dir_url;

		/**
		 * The plugin prefix.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var string
		 */
		public $prefix = 'wc_od_';


		/**
		 * Initializes the plugin.
		 *
		 * @since 1.0.0
		 * @staticvar WC_Order_Delivery $instance The *Singleton* instances of this class.
		 * @return WC_Order_Delivery The *Singleton* instance.
		 */
		public static function instance() {
			static $instance = null;
			if ( null === $instance ) {
				$instance = new self();
			}

			return $instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function __construct() {
			// Define paths.
			$this->dir_path = plugin_dir_path( __FILE__ );
			$this->dir_url = plugin_dir_url( __FILE__ );

			// Load text domain.
			load_plugin_textdomain( 'woocommerce-order-delivery', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

			// Autoload.
			spl_autoload_register( array( $this, 'autoload' ) );

			// Checks if WooCommerce is active.
			if ( is_woocommerce_active() ) {

				// Includes.
				$this->includes();

				// Plugin action links.
				$plugin = plugin_basename( __FILE__ );
				add_filter( "plugin_action_links_{$plugin}", array( $this, 'action_links' ) );

				// Hooks.
				add_action( 'plugins_loaded', array( $this, 'init' ) );

			} elseif ( is_admin() ) {
				add_action( 'admin_notices', array( $this, 'woocommerce_not_active' ) );
			}
		}

		/**
		 * Throw error on object clone.
		 *
		 * @since 1.0.0
		 * @access private
		 */
		private function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-order-delivery' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since 1.0.0
		 * @access private
		 */
		private function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-order-delivery' ), '1.0.0' );
		}

		/**
		 * Auto-load classes on demand to reduce memory consumption.
		 *
		 * @since 1.0.0
		 * @param mixed $class The class to load.
		 */
		public function autoload( $class ) {
			$classname = strtolower( $class );
			$file = 'class-' . str_replace( '_', '-', $classname ) . '.php';

			/**
			 * Filters the autoload classes.
			 *
			 * @since 1.0.0
			 * @param array $autoload An array with pairs ( pattern => $path ).
			 */
			$autoload = apply_filters( 'wc_od_autoload', array(
				'wc_od_' => $this->dir_path . 'includes/',
			) );

			foreach ( $autoload as $prefix => $path ) {
				if ( 0 === strpos( $classname, $prefix ) ) {
					$file_path = $path . $file;
					if ( is_readable( $file_path ) ) {
						include_once( $file_path );
						return;
					}
				}
			}
		}

		/**
		 * Includes the necessary files.
		 *
		 * @since 1.0.0
		 */
		public function includes() {
			include_once( 'includes/wc-od-functions.php' );
			if ( is_admin() ) {
				include_once( 'includes/admin/wc-od-admin-init.php' );
			}
		}

		/**
		 * Displays an admin notice when the WooCommerce plugin is not active.
		 *
		 * @since 1.0.0
		 */
		public function woocommerce_not_active() {
			if ( current_user_can( 'activate_plugins' ) ) :
			?>
			<div class="error">
				<p><strong><?php _e( 'WooCommerce Order Delivery', 'woocommerce-order-delivery' ); ?></strong>: <?php _e( 'The WooCommerce plugin is not active.', 'woocommerce-order-delivery' ); ?></p>
			</div>
			<?php
			endif;
		}

		/**
		 * Adds custom links to the plugins page.
		 *
		 * @since  1.0.0
		 * @param array $links The plugin links.
		 * @return array The filtered plugin links.
		 */
		public function action_links( $links ) {
			$settings_link = sprintf( '<a href="%1$s">%2$s</a>',
				wc_od_get_settings_url( WC_OD_Utils::get_shipping_options_section_slug() ),
				__( 'Settings', 'woocommerce-order-delivery' )
			);

			array_unshift( $links, $settings_link );

			return $links;
		}

		/**
		 * Init plugin.
		 *
		 * @since 1.0.0
		 */
		public function init() {
			// Use the ISO 8601 as the default date format.
			$this->date_format = _x( 'Y-m-d', 'date format for php', 'woocommerce-order-delivery' );
			$this->date_format_js = _x( 'yyyy-mm-dd', 'date format for js', 'woocommerce-order-delivery' );

			// Load settings.
			$this->settings();

			// Load checkout.
			$this->checkout();

			// Load order details.
			$this->order_details();
		}

		/**
		 * Get Settings Class.
		 *
		 * @since 1.0.0
		 *
		 * @return WC_OD_Settings
		 */
		public function settings() {
			return WC_OD_Settings::instance();
		}

		/**
		 * Get Checkout Class.
		 *
		 * @since 1.0.0
		 *
		 * @return WC_OD_Checkout
		 */
		public function checkout() {
			return WC_OD_Checkout::instance();
		}

		/**
		 * Get Order_Details Class.
		 *
		 * @since 1.0.0
		 *
		 * @return WC_OD_Order_Details
		 */
		public function order_details() {
			return WC_OD_Order_Details::instance();
		}
	}

	/**
	 * The main function for returning the plugin instance and avoiding
	 * the need to declare the global variable.
	 *
	 * @since 1.0.0
	 * @return WC_Order_Delivery The *Singleton* instance.
	 */
	function WC_OD() {
		return WC_Order_Delivery::instance();
	}

	WC_OD();
}

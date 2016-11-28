<?php
/*
 * Plugin Name: 	WooCommerce Advanced Product Labels
 * Plugin URI: 		http://woothemes.com/products/woocommerce-advanced-product-labels/
 * Description: 	Create product labels to increase visibility of your products, add information and increase conversion rate with just a few clicks!
 * Version: 		1.0.4
 * Author: 			Jeroen Sormani
 * Author URI: 		http://www.jeroensormani.com/
 * Text Domain: 	woocommerce-advanced-product-labels
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}
/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), 'd3f3fab18b6f605e093a15361e5dd486', '609121' );

/**
 *	Class Woocommerce_Advanced_Product_Labels
 *
 *	Main WAPL class, add filters and handling all other files
 *
 *	@class       Woocommerce_Advanced_Product_Labels
 *	@version     1.0.0
 *	@author      Jeroen Sormani
 */
class Woocommerce_Advanced_Product_Labels {


	/**
	 * Label styles.
	 *
	 * @since 1.0.0
	 * @var string $label_styles Label colors.
	 */
	public $label_styles = array();


	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 * @var string $version Plugin version number.
	 */
	public $version = '1.0.4';


	/**
	 * Plugin file.
	 *
	 * @since 1.0.0
	 * @var string $file Plugin file path.
	 */
	public $file = __FILE__;


	/**
	 * Instace of WooCommerce_Advanced_Product_Labels.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var object $instance The instance of WAPL.
	 */
	private static $instance;


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Check if WooCommerce is active
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) :
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		endif;

		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) :
			if ( ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) :
				return;
			endif;
		endif;

		// Load style script / admin style script
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Initialize plugin parts
		$this->init();

	}


	/**
	 * Instance.
	 *
	 * An global instance of the class. Used to retrieve the instance
	 * to use on other files/plugins/themes.
	 *
	 * @since 1.0.0
	 * @return object Instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) :
			self::$instance = new self();
		endif;

		return self::$instance;

	}


	/**
	 * init.
	 *
	 * Initialize plugin parts.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Load label styles
		$this->set_label_styles();

		/**
		 * Admin class
		 */
		require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-wapl-admin.php';
		$this->admin = new WAPL_Admin();

		/**
		 * Post Type class
		 */
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wapl-post-type.php';
		$this->post_type = new WAPL_Post_Type();

		/**
		 * Load single label class.
		 */
		require_once plugin_dir_path( __FILE__ ) . 'class-wapl-single-labels.php';
		$this->single_labels = new WAPL_Single_Labels();

		/**
		 * Load global label class.
		 */
		require_once plugin_dir_path( __FILE__ ) . 'class-wapl-global-labels.php';
		$this->global_labels = new WAPL_Global_Labels();

		/**
		 * Load conditions handler class.
		 */
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wapl-match-conditions.php';
		$this->matcher = new WAPL_Match_Conditions();

		// Load textdomain
		$this->load_textdomain();

	}


	/**
	 * Enqueue scripts.
	 *
	 * Enqueue javascript and stylesheets.
	 *
	 * @since 1.0.o
	 */
	public function enqueue_scripts() {

		wp_enqueue_style( 'woocommerce-advanced-product-labels', plugins_url( '/assets/css/style.css', __FILE__ ), array(), $this->version );

	}


	/**
	 * Admin scripts.
	 *
	 * Enqueue admin javascript and stylesheets.
	 *
	 * @since 1.0.0
	 */
	public function admin_enqueue_scripts() {

		wp_enqueue_style( 'wp-color-picker');
		wp_enqueue_style( 'woocommerce-advanced-product-labels-preview', plugins_url( '/assets/css/style.css', __FILE__ ), array(), $this->version );
		wp_enqueue_style( 'woocommerce-advanced-product-labels-admin', plugins_url( '/assets/css/admin-style.css', __FILE__ ), array(), $this->version );
		wp_enqueue_script( 'woocommerce-advanced-product-labels-admin-js', plugins_url( '/assets/js/wapl-admin.js', __FILE__ ), array( 'jquery', 'wp-color-picker' ), $this->version );

	}


	/**
	 * Textdomain.
	 *
	 * Load the textdomain based on WP language.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {

		load_plugin_textdomain( 'woocommerce-advanced-product-labels', false, plugin_basename( dirname( __FILE__ ) ) . "/languages" );

	}


	/**
	 * Label styles.
	 *
	 * Set the available label styles.
	 *
	 * @since 1.0.0
	 */
	public function set_label_styles() {

		$this->label_styles = apply_filters( 'wapl_label_styles', array(
			'red' 		=> __( 'Red', 		'woocommerce-advanced-product-labels' ),
			'blue' 		=> __( 'Blue', 		'woocommerce-advanced-product-labels' ),
			'green' 	=> __( 'Green', 	'woocommerce-advanced-product-labels' ),
			'yellow' 	=> __( 'Yellow', 	'woocommerce-advanced-product-labels' ),
			'orange' 	=> __( 'Orange', 	'woocommerce-advanced-product-labels' ),
			'gray' 		=> __( 'Gray', 		'woocommerce-advanced-product-labels' ),
			'black' 	=> __( 'Black', 	'woocommerce-advanced-product-labels' ),
			'white' 	=> __( 'White', 	'woocommerce-advanced-product-labels' ),
			'custom' 	=> __( 'Custom', 	'woocommerce-advanced-product-labels' ),
		) );

	}


	/**
	 * Get label types.
	 *
	 * Get the different label types. Extendable by users.
	 *
	 * @since 1.0.0
	 */
	public function get_label_types() {

		return apply_filters( 'wapl_label_types', array(
			'label'	=> __( 'Label', 'woocommerce-advanced-product-labels' ),
			'flash'	=> __( 'Flash', 'woocommerce-advanced-product-labels' ),
		) );


	}

}


/**
 * Load label object class.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wapl-label.php';


/**
 * The main function responsible for returning the Woocommerce_Advanced_Product_Labels object.
 *
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * Example: <?php WooCommerce_Advanced_Product_Labels()->method_name(); ?>
 *
 * @since 1.0.0
 *
 * @return object Woocommerce_Advanced_Product_Labels class object.
 */
if ( ! function_exists( 'WooCommerce_Advanced_Product_Labels' ) ) :

 	function WooCommerce_Advanced_Product_Labels() {
		return Woocommerce_Advanced_Product_Labels::instance();
	}

endif;

WooCommerce_Advanced_Product_Labels();

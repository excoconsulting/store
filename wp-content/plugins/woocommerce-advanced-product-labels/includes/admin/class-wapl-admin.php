<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class WAPL_Admin.
 *
 * WAPL_Admin class handles stuff for admin.
 *
 * @class       WAPL_Admin
 * @author     	Jeroen Sormani
 * @package		WooCommerce Advanced Product Labels
 * @version		1.0.0
 */
class WAPL_Admin {


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Add WC settings tab
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'settings_tab' ), 60 );

		// Settings page contents
		add_action( 'woocommerce_settings_tabs_labels', array( $this, 'settings_page' ) );

		// Save settings page
		add_action( 'woocommerce_update_options_labels', array( $this, 'update_options' ) );

		// Table field type
		add_action( 'woocommerce_admin_field_product_labels_table', array( $this, 'generate_table_field' ) );

		// Keep WC menu open while in WAS edit screen
		add_action( 'admin_head', array( $this, 'menu_highlight' ) );

	}


	/**
	 * Settings tab.
	 *
	 * Add a WooCommerce settings tab for the plugins settings page.
	 *
	 * @since 1.0.0
	 *
	 * @param 	array $tabs Array Default tabs used in WC.
	 * @return 	array 		All WC settings tabs including newly added.
	 */
	public function settings_tab( $tabs ) {

		$tabs['labels'] = __( 'Global Labels', 'woocommerce-advanced-product-labels' );

		return $tabs;

	}


	/**
	 * Settings page array.
	 *
	 * Get settings page fields array.
	 *
	 * @since 1.0.0
	 */
	public function get_settings() {

		$settings = apply_filters( 'woocommerce_wapl_settings', array(

			array(
				'title' 	=> __( 'WooCommerce Advanced Product Labels', 'woocommerce-advanced-product-labels' ),
				'type' 		=> 'title',
				'desc' 		=> '',
				'id'		=> 'wapl_general',
			),

			array(
				'title'   	=> __( 'Enable Global Labels', 'woocommerce-advanced-product-labels' ),
				'desc' 	  	=> __( 'When disabled you will still be able to add/modify labels, but no global labels will be displayed on the front.','woocommerce-advanced-product-labels' ),
				'id' 	  	=> 'enable_wapl',
				'default' 	=> 'yes',
				'type' 	  	=> 'checkbox',
				'autoload'	=> false
			),

			array(
				'title'   	=> __( 'Product Labels', 'woocommerce-advanced-product-labels' ),
				'type' 	  	=> 'product_labels_table',
			),

			array(
				'type' 		=> 'sectionend',
				'id' 		=> 'wapl_end'
			),

		) );

		return $settings;

	}


	/**
	 * Settings page content.
	 *
	 * Output settings page content via WooCommerce output_fields() method.
	 *
	 * @since 1.0.0
	 */
	public function settings_page() {

		WC_Admin_Settings::output_fields( $this->get_settings() );

	}


	/**
	 * Save settings.
	 *
	 * Save settings based on WooCommerce save_fields() method.
	 *
	 * @since 1.0.0
	 */
	public function update_options() {

		WC_Admin_Settings::save_fields( $this->get_settings() );

	}


	/**
	 * Table field type.
	 *
	 * Load and render table as a field type.
	 *
	 * @return string
	 */
	public function generate_table_field() {

		ob_start();

			/**
			 * Load Cart URLs table view.
			 */
			require_once plugin_dir_path( __FILE__ ) . 'views/global-labels-table.php';

		echo ob_get_clean();

	}


	/**
	 * validate_additional_product_labels_table_field function.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $key
	 * @return bool
	 */
	public function validate_additional_product_labels_table_field( $key ) {
		return false;
	}


	/**
	 * Keep menu open.
	 *
	 * Highlights the correct top level admin menu item for post type add screens.
	 *
	 * @since 1.0.4
	 */
	public function menu_highlight() {

		global $parent_file, $submenu_file, $post_type;

		if ( 'wapl' == $post_type ) :

			$parent_file	= 'woocommerce';
			$submenu_file	= 'wc-settings';

		endif;

	}


}

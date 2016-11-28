<?php
/**
 * WooCommerce POS General Settings
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/settings
 * @category	Class
 * @since     0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_POS_Admin_Settings_Checkout' ) ) :

/**
 * WC_POS_Admin_Settings_Layout
 */
class WC_POS_Admin_Settings_Checkout extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'checkout_pos';
		$this->label = __( 'Checkout', 'woocommerce' );

		add_filter( 'wc_pos_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'wc_pos_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'wc_pos_settings_save_' . $this->id, array( $this, 'save' ) );

	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		global $woocommerce;

		return apply_filters( 'woocommerce_point_of_sale_general_settings_fields', array(
			
			array( 'title' => __( 'Checkout Options', 'woocommerce' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

			array(
				'title'    => __( 'Default Country', 'woocommerce' ),
				'desc_tip' => __( 'Sets the default country for shipping and customer accounts.', 'wc_point_of_sale' ),
				'id'       => 'wc_pos_default_country',
				'css'      => 'min-width:350px;',
				'default'  => 'GB',
				'type'     => 'single_select_country',
			),

			array( 'type' => 'sectionend', 'id' => 'checkout_pos_options'),
			
			array( 'title' => __( 'Account Options', 'wc_point_of_sale' ), 'desc' => __( 'The following options affect the account creation process when creating customers.', 'wc_point_of_sale' ), 'type' => 'title', 'id' => 'checkout_page_options' ),
			

			array(
				'name'    => __( 'Username', 'wc_point_of_sale' ),
				'desc_tip'    => __( 'Choose what the username should be when customer is created.', 'wc_point_of_sale' ),
				'id'      => 'woocommerce_pos_end_of_sale_username_add_customer',
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'options' => array(
					1 => __('First & Last Name e.g. johnsmith', 'wc_point_of_sale'),
					2 => __('First & Last Name With Hyphen e.g. john-smith', 'wc_point_of_sale'),
					3 => __('Email address', 'wc_point_of_sale')
				),
				'autoload' => true
			),

			array(
				'name' => __( 'Customer Details', 'wc_point_of_sale' ),
				'id'   => 'wc_pos_load_customer_after_selecting',
				'type' => 'checkbox',
				'desc' => __( 'Load customer details after customer selection', 'wc_point_of_sale' ),
				'desc_tip' => __( 'Automatically displays the customer details screen when searching and selecting a customer.', 'wc_point_of_sale' ),
				'default'	=> 'no',
				'autoload'  => true					
			),
			
			array( 'type' => 'sectionend', 'id' => 'checkout_page_options'),			
			
			array( 'title' => __( 'Email Options', 'wc_point_of_sale' ), 'desc' => __( 'The following options affect the email notifications when orders are placed and accounts are created.', 'wc_point_of_sale' ), 'type' => 'title', 'id' => 'email_options' ),
			
			array(
				'name' => __( 'New Order', 'wc_point_of_sale' ),
				'id'   => 'wc_pos_email_notifications',
				'type' => 'checkbox',
				'desc' => __( 'Enable new order notification', 'wc_point_of_sale' ),
				'desc_tip' => sprintf(__( 'New order emails are sent to the recipient list when an order is received as shown %shere%s.', 'wc_point_of_sale' ), 
					'<a href="'.admin_url('admin.php?page=wc-settings&tab=email&section=wc_email_new_order').'">', '</a>'),
				'default'	=> 'no',
				'autoload'  => true					
			),
			
		  	array(
				'name' => __( 'Account Creation', 'wc_point_of_sale' ),
				'id'   => 'wc_pos_automatic_emails',
				'type' => 'checkbox',
				'desc' => __( 'Enable account creation notification', 'wc_point_of_sale' ),
				'desc_tip' => sprintf(__( 'Customer emails are sent to the customer when a customer signs up via checkout or account pages as shown %shere%s.', 'wc_point_of_sale' ), 
					'<a href="'.admin_url('admin.php?page=wc-settings&tab=email&section=wc_email_customer_new_account').'">', '</a>'),
				'default'	=> 'yes',
				'autoload'  => true					
			),

			array( 'type' => 'sectionend', 'id' => 'email_options'),
			
					
		)); // End general settings

	}

	/**
	 * Save settings
	 */
	public function save() {
		$settings = $this->get_settings();
		WC_POS_Admin_Settings::save_fields( $settings );
	}

}

endif;

return new WC_POS_Admin_Settings_Checkout();
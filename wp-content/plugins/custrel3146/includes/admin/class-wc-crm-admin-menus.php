<?php
/**
 * Setup menus in WP admin.
 *
 * @version		1.0
 * @category	Class
 * @author      Actuality Extensions
 * @package     WooCommerce_Customer_Relationship_Manager/Classes
 * @since       2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_CRM_Admin_Menus' ) ) :

/**
 * WC_CRM_Admin_Menus Class
 */
class WC_CRM_Admin_Menus {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		// Add menus
		add_filter( 'set-screen-option', array(&$this, 'set_screen'), 10, 3 );
		add_action( 'admin_menu', array($this, 'add_menu') );		
	    add_filter( 'admin_head', array( $this, 'submenu_order' ) );

	}

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
   * Add the menu item
   */
	public function add_menu() {
		$hook = add_menu_page(
		  __( 'Customers', 'wc_crm' ), // page title
		  __( 'Customers', 'wc_crm' ), // menu title
		  'manage_woocommerce', // capability
		  WC_CRM_TOKEN, // unique menu slug
		  array($this, 'customers_list'),
		  'dashicons-admin-wc-crm',
		  '56.3'
		);

		$new_customer_hook = add_submenu_page( 
			WC_CRM_TOKEN,
			__( "Add New Customer", 'wc_crm' ),
			__( "Add New", 'wc_crm'),
			'manage_woocommerce',
			WC_CRM_TOKEN.'-new-customer',
			array($this, 'new_customer')
		);

		$logs_hook = add_submenu_page(
			WC_CRM_TOKEN,
			__( "Activity", 'wc_crm' ),
			__( "Activity", 'wc_crm'),
			'manage_woocommerce',
			WC_CRM_TOKEN.'-logs',
			array($this, 'logs_page')
		);

		$accounts_hook = add_submenu_page(
		  WC_CRM_TOKEN,
		  __('Accounts', 'wc_crm'),
		  __('Accounts', 'wc_crm'),
		  'manage_woocommerce',
		  'edit.php?post_type=wc_crm_accounts'
		);

		$tasks_hook = add_submenu_page(
		  WC_CRM_TOKEN,
		  __('Tasks', 'wc_crm'),
		  __('Tasks', 'wc_crm'),
		  'manage_woocommerce',
		  'edit.php?post_type=wc_crm_tasks'
		);

		$calls_hook = add_submenu_page(
		  WC_CRM_TOKEN,
		  __('Calls', 'wc_crm'),
		  __('Calls', 'wc_crm'),
		  'manage_woocommerce',
		  'edit.php?post_type=wc_crm_calls'
		);

		$groups_hook = add_submenu_page( 
			WC_CRM_TOKEN,
			__( "Groups", 'wc_crm' ),
			__( "Groups", 'wc_crm'),
			'manage_woocommerce',
			WC_CRM_TOKEN.'-groups',
			array($this, 'groups_page')			
		 );


		add_submenu_page( WC_CRM_TOKEN,
			__( "Settings", 'wc_crm' ),
			__( "Settings", 'wc_crm'),
			'manage_woocommerce',
			WC_CRM_TOKEN.'-settings',
			array($this, 'settings')
			);


		add_action( "load-$hook", array($this, 'c_screen_option') );
		add_action( "load-$logs_hook", array($this, 'a_screen_option') );
		add_action( "load-$groups_hook", array($this, 'g_screen_option') );
	}

	public function c_screen_option()
	{
		if( !isset($_GET['c_id']) ){
			$option = 'per_page';
			$args = array(
				'label' => __( 'Customers', 'wc_crm' ),
				'default' => 20,
				'option' => 'customers_per_page'
			);
			add_screen_option( $option, $args );
			WC_CRM()->tables['customers'] = new WC_CRM_Table_Customers();
		}
	}
	public function a_screen_option()
	{
		if( !isset($_GET['c_id']) ){
			$option = 'per_page';
			$args = array(
				'label' => __( 'Log Records', 'wc_crm' ),
				'default' => 20,
				'option' => 'logs_per_page'
			);
			add_screen_option( $option, $args );
			WC_CRM()->tables['activity'] = new WC_CRM_Table_Activity();
		}
	}

	public function g_screen_option()
	{
		if( !isset($_GET['c_id']) ){
			$option = 'per_page';
			$args = array(
				'label' => __( 'Groups', 'wc_crm' ),
				'default' => 20,
				'option' => 'logs_per_page'
			);
			add_screen_option( $option, $args );
			WC_CRM()->tables['groups'] = new WC_CRM_Table_Groups();
		}
	}

  /**
   *
   * @param mixed $menu_order
   * @return array
   */
  public function submenu_order( ) {
    global $menu, $submenu, $parent_file, $submenu_file, $post_type;
    $pts = array('wc_crm_accounts', 'wc_crm_tasks', 'wc_crm_calls');
    if( in_array($post_type, $pts) ){
      $parent_file = WC_CRM_TOKEN;
      $submenu_file = 'edit.php?post_type='.$post_type;
    }
    
  }


  	
	public function customers_list() {
		$screen = isset($_GET['screen']) ? $_GET['screen'] : '';
		switch ($screen) {
			case 'customer_notes':
				WC_CRM_Screen_Customers_Edit::display_notes($_GET['c_id']);
				break;

			case 'email':
				WC_CRM_Screen_Activity::display_email_form();
				break;			
			
			default:
				if( isset($_GET['c_id']) && !empty($_GET['c_id'])){
					WC_CRM_Screen_Customers_Edit::output($_GET['c_id']);
				}else{
					WC_CRM_Screen_Customers_List::output();
				}
				break;
		}
	
  	}
  	public function new_customer() {
		WC_CRM_Screen_Customers_Edit::output();
  	}

  	public function logs_page() {
  		if(isset($_GET['log_id']) && !empty($_GET['log_id'])){
			WC_CRM_Screen_Activity::display_activity_data();
		}else{
			WC_CRM_Screen_Activity::output();
		}
  	}

  	public function groups_page() {
		WC_CRM_Screen_Groups::output();		
  	}

  	public function settings() {
		WC_CRM_Admin_Settings::output();		
  	}



}

endif;

return new WC_CRM_Admin_Menus();

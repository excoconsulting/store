<?php
/**
 * Installation related functions and actions.
 *
 * @category Admin
 * @package  WoocommercePointOfSale/Classes
 * @version  2.4.15
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * WC_POS_Install Class
 */
class WC_POS_Install {

  /** @var array DB updates that need to be run */
  private static $db_updates = array(
    '3.0.9' => 'updates/wc_pos-update-3.0.9.php',    
    '3.1.4' => 'updates/wc_pos-update-3.1.4.php',
  );

  /**
   * Hook in tabs.
   */
  public static function init() {    
    add_action( 'admin_init', array( __CLASS__, 'check_version' ), 5 );
    add_action( 'admin_init', array( __CLASS__, 'install_actions' ), 6 );
    if (function_exists('is_multisite') && is_multisite()) {
        add_action( 'wpmu_new_blog', array( __CLASS__, 'new_blog'), 10, 6); 
    }

    

  }
  public static function check_version(){

      if ( ! defined( 'IFRAME_REQUEST' ) && ( get_option( 'wc_pos_db_version' ) != WC_POS_VERSION ) ) {
        self::install();
        do_action( 'wc_pos_updated' );
      }
  }

  public static function new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
    global $wpdb;
    $pos_path = basename( dirname(  WC_POS_PLUGIN_FILE  ) );
    if (is_plugin_active_for_network($pos_path.'/woocommerce-point-of-sale.php')) {
        $old_blog = $wpdb->blogid;
        switch_to_blog($blog_id);
        self::install();
        switch_to_blog($old_blog);
    }
  }

  /**
   * Install actions such as installing pages when a button is clicked.
   */
  public static function install_actions() {
    if ( ! empty( $_GET['do_update_wc_pos'] ) ) {
      self::update();

      // Update complete
      WC_POS_Admin_Notices::remove_notice( 'pos_update' );

      // What's new redirect
      delete_transient( '_wc_pos_activation_redirect' );
      wp_redirect( admin_url( 'admin.php?page=wc_pos_settings' ) );
      exit;
    }
  }

  public static function install()
  {
    global $wpdb;
    if ( ! defined( 'WC_POS_INSTALLING' ) ) {
      define( 'WC_POS_INSTALLING', true );
    }   

    self::create_tables();
    self::create_product();
    self::create_roles();
    self::update_options();

    // Queue upgrades/setup wizard
    $current_version   = get_option(  'wc_pos_db_version', null );
    #$major_cur_version = substr( $current_version, 0, strrpos( $current_version, '.' ) );
    #$major_version     = substr( WC_POS_VERSION, 0, strrpos( WC_POS_VERSION, '.' ) );

    // No versions? This is a new install :)
    if ( is_null( $current_version ) && apply_filters( 'wc_pos_enable_setup_wizard', true ) ) {
      WC_POS_Admin_Notices::add_notice( 'pos_install' );
      set_transient( '_wc_pos_activation_redirect', 1, 30 );
    }

    if ( ! is_null( $current_version ) && version_compare( $current_version, max( array_keys( self::$db_updates ) ), '<' ) ) {
      WC_POS_Admin_Notices::add_notice( 'pos_update' );
    } else {
      self::update_pos_version();
    }

    /*
     * Deletes all expired transients. The multi-table delete syntax is used
     * to delete the transient record from table a, and the corresponding
     * transient_timeout record from table b.
     *
     * Based on code inside core's upgrade_network() function.
     */
    $sql = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
      WHERE a.option_name LIKE %s
      AND a.option_name NOT LIKE %s
      AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
      AND b.option_value < %d";
    $wpdb->query( $wpdb->prepare( $sql, $wpdb->esc_like( '_transient_' ) . '%', $wpdb->esc_like( '_transient_timeout_' ) . '%', time() ) );
    
    // Trigger action
    do_action( 'wc_pos_installed' );    

  }

  /**
   * Handle updates
   */
  private static function update() {
    $current_db_version = get_option( 'wc_pos_db_version');

    foreach ( self::$db_updates as $version => $updater ) {
      if ( version_compare( $current_db_version, $version, '<' ) ) {
        include( $updater );
        self::update_pos_version( $version );
      }
    }

    self::update_pos_version();
  }

  private static function create_tables() {
        global $wpdb;
        
        #$wpdb->hide_errors();
        $wpdb->show_errors();
        $installed_ver = get_option("wc_pos_db_version");

        $db_name = DB_NAME;
        $result = $wpdb->query("SELECT * 
                                FROM information_schema.COLUMNS 
                                WHERE 
                                    TABLE_SCHEMA = '{$db_name}' 
                                AND TABLE_NAME = '{$wpdb->users}' 
                                AND COLUMN_NAME = 'user_modified_gmt'");
        
        if( !$result ){
          $result = $wpdb->query("ALTER TABLE {$wpdb->users} ADD user_modified_gmt DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER user_registered");
          $result = $wpdb->query("UPDATE {$wpdb->users} SET user_modified_gmt=user_registered");
        }

        if ($installed_ver != WC_POS_VERSION) {

            $collate = '';
            if ($wpdb->has_cap('collation')) {
                if (!empty($wpdb->charset))
                    $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
                if (!empty($wpdb->collate))
                    $collate .= " COLLATE $wpdb->collate";
            }

            // initial install
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            $table_name = $wpdb->prefix . "wc_poin_of_sale_outlets";
            $sql = "CREATE TABLE $table_name (
            ID        bigint(20) NOT NULL AUTO_INCREMENT,
            name      text NOT NULL,
            contact   text DEFAULT '' NOT NULL,
            social    text DEFAULT '' NOT NULL,
            PRIMARY KEY  (ID)
    )" . $collate;
            dbDelta($sql);

            $table_name = $wpdb->prefix . "wc_poin_of_sale_registers";
            $sql = "CREATE TABLE $table_name (
            ID        bigint(20) NOT NULL AUTO_INCREMENT,
            name      varchar(255) NOT NULL,
            slug      varchar(255) NOT NULL,
            detail    text DEFAULT '' NOT NULL,
            outlet    int(20) DEFAULT 0 NOT NULL,
            default_customer int(20) DEFAULT 0 NOT NULL,
            order_id  int(20) DEFAULT 0 NOT NULL,
            settings   text DEFAULT '' NOT NULL,
            _edit_last    int(20) DEFAULT 0 NOT NULL,
            opened timestamp NOT NULL DEFAULT current_timestamp,
            closed timestamp NOT NULL,
            PRIMARY KEY  (ID)
    )" . $collate;
            dbDelta($sql);


            $table_name = $wpdb->prefix . "wc_poin_of_sale_tiles";
            $sql = "CREATE TABLE $table_name (
            ID          bigint(20) NOT NULL AUTO_INCREMENT,
            grid_id     bigint(20) NOT NULL,
            product_id  bigint(20) NOT NULL,
            style       varchar(100) DEFAULT 'image' NOT NULL,
            colour      varchar(6) DEFAULT '000000' NOT NULL,
            background  varchar(6) DEFAULT 'ffffff' NOT NULL,
            default_selection  bigint(20) NOT NULL,
            order_position     bigint(20) NOT NULL,
            PRIMARY KEY  (ID)
    )" . $collate;
            dbDelta($sql);

            $table_name = $wpdb->prefix . "wc_poin_of_sale_grids";
            $sql = "CREATE TABLE $table_name (
            ID        bigint(20) NOT NULL AUTO_INCREMENT,
            name      varchar(255) NOT NULL,
            label     varchar(255) NOT NULL,
            sort_order     varchar(255) DEFAULT 'name' NOT NULL,
            PRIMARY KEY  (ID)
    )" . $collate;
            dbDelta($sql);



            $table_name = $wpdb->prefix . "wc_poin_of_sale_receipts";
            $sql = "CREATE TABLE $table_name (
            ID          bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) DEFAULT '' NOT NULL,
            print_outlet_address varchar(255) DEFAULT '' NOT NULL,
            print_outlet_contact_details varchar(255) DEFAULT '' NOT NULL,
            telephone_label text DEFAULT '' NOT NULL,
            fax_label text DEFAULT '' NOT NULL,
            email_label text DEFAULT '' NOT NULL,
            website_label text DEFAULT '' NOT NULL,
            receipt_title text DEFAULT '' NOT NULL,
            order_number_label text DEFAULT '' NOT NULL,
            order_date_label text DEFAULT '' NOT NULL,
            order_date_format text DEFAULT '' NOT NULL,
            print_order_time varchar(255) DEFAULT '' NOT NULL,
            print_server varchar(255) DEFAULT '' NOT NULL,
            served_by_label text DEFAULT '' NOT NULL,
            served_by_type enum( 'username', 'nickname', 'display_name' ) DEFAULT 'username',
            tax_label text DEFAULT '' NOT NULL,
            total_label text DEFAULT '' NOT NULL,
            payment_label text DEFAULT '' NOT NULL,
            print_number_items text DEFAULT '' NOT NULL,
            items_label text DEFAULT '' NOT NULL,
            print_barcode varchar(255) DEFAULT '' NOT NULL,
            show_image_product varchar(255) DEFAULT '' NOT NULL,
            print_tax_number varchar(255) DEFAULT '' NOT NULL,
            tax_number_label text DEFAULT '' NOT NULL,
            print_order_notes varchar(255) DEFAULT '' NOT NULL,
            order_notes_label text DEFAULT '' NOT NULL,
            print_customer_name varchar(255) DEFAULT '' NOT NULL,
            customer_name_label text DEFAULT '' NOT NULL,
            print_customer_email varchar(255) DEFAULT '' NOT NULL,
            customer_email_label text DEFAULT '' NOT NULL,
            print_customer_ship_address varchar(255) DEFAULT '' NOT NULL,
            customer_ship_address_label text DEFAULT '' NOT NULL,
            header_text text DEFAULT '' NOT NULL,
            footer_text text DEFAULT '' NOT NULL,
            logo text DEFAULT '' NOT NULL,
            text_size enum( 'nomal', 'small', 'large' ) DEFAULT 'nomal',
            title_position enum( 'left', 'center', 'right' ) DEFAULT 'left',
            logo_size enum( 'nomal', 'small', 'large' ) DEFAULT 'nomal',
            logo_position enum( 'left', 'center', 'right' ) DEFAULT 'left',
            contact_position enum( 'left', 'center', 'right' ) DEFAULT 'left',
            tax_number_position enum( 'left', 'center', 'right' ) DEFAULT 'left',
            custom_css text DEFAULT '' NOT NULL,
            PRIMARY KEY  (ID)
    )" . $collate;
            dbDelta($sql);

    }
  }
  /**
   * Create roles and capabilities
   */
  public static function create_roles() {
    
    global $wp_roles;

    if ( ! class_exists( 'WP_Roles' ) ) {
      return;
    }

    if ( ! isset( $wp_roles ) ) {
      $wp_roles = new WP_Roles();
    }

    // Cashier role
    add_role( 'cashier', __( 'Cashier', 'wc_point_of_sale' ), array(
      'read'            => true,
      'edit_posts'      => false,
      'delete_posts'    => false,
      'list_users'      => true
    ) );

    // POS manager role
    add_role( 'pos_manager', __( 'POS Manager', 'wc_point_of_sale' ), array(
      'read'                   => true,
      'edit_users'             => true,
      'edit_posts'             => true,
      'delete_posts'           => true,
      'unfiltered_html'        => true,
      'upload_files'           => true,
      'list_users'             => true
    ) );

    $capabilities = self::get_core_capabilities();

    foreach ( $capabilities as $cap_group ) {
      foreach ( $cap_group as $cap ) {
        $wp_roles->add_cap( 'pos_manager', $cap );
        $wp_roles->add_cap( 'administrator', $cap );
      }
    }
    foreach ( $capabilities['cashier'] as $cap ) {
      $wp_roles->add_cap( 'cashier', $cap );
    }

    $shop_manager = $wp_roles->get_role('shop_manager');

      
    foreach ( $shop_manager->capabilities as $cap => $status ) {
      if($status == true )
        $wp_roles->add_cap( 'pos_manager', $cap );
    }
  }

  /**
   * Update options
   *
   */
  public static function update_options()
  {
      /*$pos_base_country = get_option('wc_pos_default_country');
      if( !$pos_base_country || empty($pos_base_country) ){
        $wc_base_country  = WC()->countries->get_base_country();
        update_option('wc_pos_default_country', $wc_base_country);
      }*/
  }

  /**
   * Update WC POS version to current
   */
  private static function update_pos_version( $version = null ) {
    delete_option( 'wc_pos_db_version' );
    add_option( 'wc_pos_db_version', is_null( $version ) ? WC_POS_VERSION : $version );
  }

  /**
   * Get capabilities for POS - these are assigned to admin/POS Manager/Cashier during installation or reset
   *
   * @return array
   */
   private static function get_core_capabilities() {
    $capabilities = array();

    $capabilities['cashier'] = array(
      'view_register',
      'read_private_shop_orders',
      'read_private_products',
      'read_private_shop_coupons'
    );
    $capabilities['manager'] = array(
      'manage_wc_point_of_sale',
      'view_woocommerce_reports',
    );
    return $capabilities;
  }

  public static function create_product() {
      global $wpdb;

      $old_product = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_title = 'POS custom product' ");
      if($old_product){
        $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'product' AND post_title = 'POS custom product' ");
      }

      $option_name = 'wc_pos_custom_product_id';
      $need_create = false;

      if ( $pr_id = (int)get_option($option_name) ) {
          $result = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type = 'pos_custom_product' AND post_status = 'publish' AND ID={$pr_id}");
          if(!$result)
              $need_create = true;
      } else {
          $need_create = true;
      }
      if($need_create){
          $new_product = array(
              'post_title'   => 'POS custom product',
              'post_status'  => 'publish',
              'post_type'    => 'pos_custom_product',
              'post_excerpt' => '',
              'post_content' => '',
              'post_author'  => get_current_user_id(),
          );

          // Attempts to create the new product
          $id = (int)wp_insert_post( $new_product, true );

          $regular_price = wc_format_decimal( 10);
          update_post_meta( $id, '_regular_price', $regular_price );
          update_post_meta( $id, '_price', $regular_price );
          update_post_meta( $id, '_visibility', 'hidden' );

          $product_type = wc_clean( 'simple' );
          wp_set_object_terms( $id, $product_type, 'product_type' );

          update_option($option_name, $id);

          
      }
      
  }

  public static function activate($networkwide) {
    $GLOBALS['wp_rewrite'] = new WP_Rewrite();
    global $wp_rewrite, $wpdb;
    self::flush_rewrite_rules();   
    if (function_exists('is_multisite') && is_multisite()) {
        // check if it is a network activation - if so, run the activation function for each blog id
        if ($networkwide) {
            $old_blog = $wpdb->blogid;
            // Get all blog ids
            $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            foreach ($blogids as $blog_id) {
                switch_to_blog($blog_id);
                self::install();
            }
            switch_to_blog($old_blog);
            return;
        }   
    }          
    self::install();
  }  

  /**
   * remove_roles function.
   */
  public static function remove_roles() {
    global $wp_roles;

    if ( ! class_exists( 'WP_Roles' ) ) {
      return;
    }

    if ( ! isset( $wp_roles ) ) {
      $wp_roles = new WP_Roles();
    }

    $capabilities = array(
      'view_register',
      'manage_wc_point_of_sale'
    );
    foreach ( $capabilities as $cap ) {
      $wp_roles->remove_cap( 'cashier', $cap );
      $wp_roles->remove_cap( 'pos_manager', $cap );
      $wp_roles->remove_cap( 'shop_manager', $cap );
      $wp_roles->remove_cap( 'administrator', $cap );
    }

    remove_role( 'pos_manager' );
    remove_role( 'cashier' );
  }

  

}
WC_POS_Install::init();
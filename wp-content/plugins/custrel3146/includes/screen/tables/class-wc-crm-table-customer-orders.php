<?php
/**
 * Table with list of customers order.
 *
 * @author   Actuality Extensions
 * @package  WooCommerce_Customer_Relationship_Manager
 * @since    1.0
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WC_CRM_Table_Customer_Orders extends WP_List_Table {

  protected $data;
  protected $popup;
  protected $found_data;

  function __construct($orders = array(), $popup = false) {
    include_once( dirname(WC_PLUGIN_FILE).'/includes/admin/class-wc-admin-post-types.php' );
    global $status, $page, $CPT_Shop_Order;
    $CPT_Shop_Order = new WC_Admin_Post_Types();

    $this->data  = $orders;
    $this->popup = $popup;

    parent::__construct( array(
      'singular' => __( 'order', 'wc_crm' ), //singular name of the listed records
      'plural' => __( 'orders', 'wc_crm' ), //plural name of the listed records
      'ajax' => false //does this table support ajax?

    ) );

  }


  function no_items() {
    _e( 'No orders data found.', 'wc_crm' );
  }

  function column_default( $item, $column_name ) {
    global $CPT_Shop_Order, $post, $woocommerce, $the_order;
    $post = $item;
    switch ( $column_name ) {
      case 'order_status':
      case 'order_title':
      case 'order_items':
      case 'shipping_address':
      case 'customer_message':
      case 'order_notes':
      case 'order_date':
      case 'order_total':
      case 'crm_actions':
        return $CPT_Shop_Order->render_shop_order_columns($column_name);
        //return $item[$column_name];
      default:
        return print_r( $item, true ); //Show the whole array for troubleshooting purposes
    }
  }

  function get_sortable_columns() {
    $sortable_columns = array(
      'order_title' => array('order_title', false),
      'order_date' => array('order_date', false),
      'order_total' => array('order_total', false),
    );
    return $sortable_columns;
  }

  function get_columns() {
    $columns = array(
      'order_status' => '<span class="status_head tips" data-tip="' . esc_attr__( 'Status', 'woocommerce' ) . '">' . esc_attr__( 'Status', 'woocommerce' ) . '</span>',
      'order_title' => __( 'Order', 'wc_crm' ),
      'order_items' => __( 'Purchased', 'wc_crm' ),
      'shipping_address' => __( 'Ship to', 'wc_crm' ),
      'customer_message' => '<span class="notes_head tips">'.__( 'Customer Message', 'wc_crm' ).'</span>',
      'order_date' => __( 'Date', 'wc_crm' ),
      'order_total' => __( 'Total', 'wc_crm' ),
      'crm_actions' => __( 'Actions', 'wc_crm' ),
    );
    return $columns;
  }

  function usort_reorder( $a, $b ) {
    // If no sort, default to last purchase
    $orderby = ( !empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'order_date';
    // If no order, default to desc
    $order = ( !empty( $_GET['order'] ) ) ? $_GET['order'] : 'desc';
    // Determine sort order
    if ( $orderby == 'order_value' ) {
      $result = $a[$orderby] - $b[$orderby];
    } else {
      $result = strcmp( $a[$orderby], $b[$orderby] );
    }
    // Send final sort direction to usort
    return ( $order === 'asc' ) ? $result : -$result;
  }


  function column_crm_actions( $item ) {
    global $woocommerce;
      $actions = array(
        'orders' => array(
          'classes' => 'view',
          'url' => sprintf( 'post.php?post=%s&action=edit', urlencode( $item->ID ) ),
          'action' => 'view',
          'name' => __( 'View Order', 'wc_crm' )
        ),
        'select_order' => array(
          'classes' => 'select_order',
          'url' => '#'.$item->ID,
          'name' => __( 'Select', 'wc_crm' ),
        )
      );
      if( $this->popup === false ){
        unset($actions['select_order']);
      }
      echo '<p>';
      foreach ( $actions as $action ) {
        printf( '<a class="button tips %s" href="%s" data-tip="%s">%s</a>', esc_attr($action['classes']), esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_attr( $action['name'] ) );
      }
      echo '</p>';
  }

  function prepare_items() {
    $columns  = $this->get_columns();
    $sortable = array();
    $this->_column_headers = array($columns, array(), $sortable);

    $this->items = $this->data;

  }

}
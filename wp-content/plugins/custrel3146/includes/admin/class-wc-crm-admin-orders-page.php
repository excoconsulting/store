<?php
/**
 *
 * @author   Actuality Extensions
 * @category Admin
 * @package  WC_CRM_Admin/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_CRM_Admin_Orders_Page' ) ) :

/**
 * WC_CRM_Admin_Orders_Page Class
 *
 * Handles the edit posts views and some functionality on the edit post screen for WC post types.
 */
class WC_CRM_Admin_Orders_Page {

	/**
   * Hook into ajax events
   */
  public function __construct() {
    add_action( 'admin_head', array($this, 'view_customer_button') );
    add_action( 'admin_head', array($this, 'view_customer_link') );
    add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_shop_order_columns' ), 50 );
  }
  public function view_customer_button(){
    $screen = get_current_screen();
    if( !$screen ) return;
    if($screen->id != 'shop_order' || !isset($_GET['post']) || empty($_GET['post']) ) return;
    $crm_customer_link = get_option( 'wc_crm_customer_link', 'customer' );

    $url = '';      

    $user_id = get_post_meta( $_GET['post'], '_customer_user', true ); 
    $email   = get_post_meta( $_GET['post'], '_billing_email', true ); 
    if($crm_customer_link == 'customer'){
      if($user_id){
        $user = wc_crm_get_customer($user_id, 'user_id');
        if($user){
          $url = get_admin_url().'admin.php?page='.WC_CRM_TOKEN.'&c_id='.$user->c_id;
        }
      }else if($email){
        $user = wc_crm_get_customer($email, 'email');
        if($user){
          $url = get_admin_url().'admin.php?page='.WC_CRM_TOKEN.'&c_id='.$user->c_id;
        }
      }
    }else if($user_id){
      $user = wc_crm_get_customer($user_id, 'user_id');
      if($user){
        $url = get_admin_url().'user-edit.php?user_id='.$user_id;
      }
    }
    if(empty($url)) return false;
    ?>
      <script>
      jQuery(document).ready(function($){
        $('h1 .page-title-action').after('<a class="add-new-h2 add-new-view-customer" href="<?php echo $url; ?>"><?php _e("View Customer", "wc_crm"); ?></a>');
      });
      </script>
      <style>
        .wrap .add-new-h2.add-new-view-customer, .wrap .add-new-h2.add-new-view-customer:active{
          background: #2ea2cc;
          color:#fff
        }
        .wrap .add-new-h2.add-new-view-customer:hover{
          background: #1e8cbe;
          border-color: #0074a2;
        }
        table td.order_title div.tips:not(.wc_crm_customer_link){
          display: none;
        }
      </style>
    <?php
  }

  public function view_customer_link()
  {
    $crm_customer_link = get_option( 'wc_crm_customer_link', 'customer' );
    if($crm_customer_link == 'customer'){
          
      ?>
      <style>
        table td.order_title div.wc_crm_customer_link{
          display: none;
        }
      </style>
      <script>
        jQuery('document').ready(function($){
          $('table td.order_title').each(function(index, el) {
              var html = $(this).find('div.wc_crm_customer_link').html();
              $(this).html(html);
          });
        });
      </script>
      <?php
    }
  }

  public function render_shop_order_columns( $column )
  {
    global $post, $woocommerce, $the_order;

    if ( empty( $the_order ) || $the_order->id != $post->ID ) {
      $the_order = wc_get_order( $post->ID );
    }
    switch ( $column ) {
      case 'order_title' :
        $crm_customer_link = get_option( 'wc_crm_customer_link', 'customer' );
        if($crm_customer_link == 'customer'){

          $url = '';

          echo '<div class="wc_crm_customer_link">';

          $user_id = $the_order->user_id; 
          $email   = $the_order->billing_email; 
          if($user_id){
            $user = wc_crm_get_customer($user_id, 'user_id');
            if($user){
              $url = get_admin_url().'admin.php?page='.WC_CRM_TOKEN.'&c_id='.$user->c_id;
            }
          }else if($email){
            $user = wc_crm_get_customer($email, 'email');
            if($user){
              $url = get_admin_url().'admin.php?page='.WC_CRM_TOKEN.'&c_id='.$user->c_id;
            }
          }

          if ( $the_order->user_id ) {
            $user_info = get_userdata( $the_order->user_id );
          }

          $username = '';
          if( !empty($url) ){
            $username = '<a href="' . $url . '">';
          }
          if ( ! empty( $user_info ) ) {


            if ( $user_info->first_name || $user_info->last_name ) {
              $username .= esc_html( sprintf( _x( '%1$s %2$s', 'full name', 'woocommerce' ), ucfirst( $user_info->first_name ), ucfirst( $user_info->last_name ) ) );
            } else {
              $username .= esc_html( ucfirst( $user_info->display_name ) );
            }


          } else {
            if ( $the_order->billing_first_name || $the_order->billing_last_name ) {
              $username .= trim( sprintf( _x( '%1$s %2$s', 'full name', 'woocommerce' ), $the_order->billing_first_name, $the_order->billing_last_name ) );
            } else {
              $username .= __( 'Guest', 'woocommerce' );
            }
          }
          if( !empty($url) ){
            $username .= '</a>';
          }

          printf( _x( '%s by %s', 'Order number by X', 'woocommerce' ), '<a href="' . admin_url( 'post.php?post=' . absint( $post->ID ) . '&action=edit' ) . '" class="row-title"><strong>#' . esc_attr( $the_order->get_order_number() ) . '</strong></a>', $username );

          if ( $the_order->billing_email ) {
            echo '<small class="meta email"><a href="' . esc_url( 'mailto:' . $the_order->billing_email ) . '">' . esc_html( $the_order->billing_email ) . '</a></small>';
          }

          echo '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __( 'Show more details', 'woocommerce' ) . '</span></button>';
          echo '</div>';
        }

      break;
    }
  }

}

new WC_CRM_Admin_Orders_Page();

endif;
<?php
/**
 * Admin Init
 *
 * @author      WooThemes
 * @package     WC_OD
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Admin init.
 *
 * @since 1.0.0
 */
function wc_od_admin_init() {
	if ( defined( 'DOING_AJAX' ) ) {
		include_once( 'wc-od-admin-ajax-functions.php' );
	}

	// The WooCommerce settings page.
	if ( WC_OD_Utils::is_woocommerce_settings_page() ) {
		include_once( 'class-wc-od-admin-settings.php' );
	}
}
add_action( 'admin_init', 'wc_od_admin_init' );


/** Shop Orders functions *****************************************************/


/**
 * Updates the Orders list columns.
 *
 * @since 1.0.0
 * @param array $columns The shop order columns.
 * @return array The modified shop order columns.
 */
function wc_od_admin_shop_order_columns( $columns ) {
	$modified_columns = array_slice( $columns, 0, 8 );
	$modified_columns['delivery_date'] = __( 'Delivery Date', 'woocommerce-order-delivery' );
	$modified_columns = array_merge( $modified_columns, array_slice( $columns, 8 ) );

	return $modified_columns;
}
add_filter( 'manage_edit-shop_order_columns', 'wc_od_admin_shop_order_columns', 20 );

/**
 * Adds the delivery_date column to the sortable list.
 *
 * @since 1.0.0
 * @param array $columns The sortable columns list.
 * @return array The filtered sortable columns list.
 */
function wc_od_admin_shop_order_sort_columns( $columns ) {
	$columns['delivery_date'] = 'delivery_date';

	return $columns;
}
add_filter( "manage_edit-shop_order_sortable_columns", 'wc_od_admin_shop_order_sort_columns' );

/**
 * Prints the content for the custom orders columns.
 *
 * @since 1.0.0
 * @global WP_Post $post The current post.
 * @param string $column_id The column ID.
 */
function wc_od_admin_shop_order_posts_column( $column_id ) {
	global $post;

	if ( 'delivery_date' === $column_id ) {
		$delivery_date = get_post_meta( $post->ID, '_delivery_date', true );
		if ( $delivery_date ) {
			echo wc_od_localize_date( $delivery_date, WC_OD()->date_format );
		} else {
			echo '<span class="na">–</span>';
		}
	}
}
add_action( 'manage_shop_order_posts_custom_column', 'wc_od_admin_shop_order_posts_column', 20 );

/**
 * Adds the query vars for order by delivery_date.
 *
 * @since 1.0.0
 * @global string $typenow The current post type.
 * @param array $vars The query vars.
 * @return array The filtered query vars.
 */
function wc_od_admin_shop_order_orderby( $vars ) {
	global $typenow;

	if ( 'shop_order' !== $typenow ) {
		return $vars;
	}

	// Sorting
	if ( isset( $vars['orderby'] ) ) {
		if ( 'delivery_date' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
				'meta_key' 	=> '_delivery_date',
				'orderby' 	=> 'meta_value_num',
			) );
		}
	}

	return $vars;
}
add_filter( 'request', 'wc_od_admin_shop_order_orderby' );

/**
 * Filters the order by query for cast the meta_value as date.
 *
 * @since 1.0.0
 * @global wpdb $wpdb
 * @param string $orderby The orderby query.
 * @param array $query    The query parameters.
 * @return string The filtered orderby query.
 */
function wc_od_admin_posts_orderby_date( $orderby, $query ) {
    global $wpdb;

	if ( 'shop_order' === $query->get( 'post_type' ) && '_delivery_date' === $query->get( 'meta_key' ) ) {
		$orderby = "CAST( $wpdb->postmeta.meta_value AS DATE ) " . $query->get( 'order' );
	}

	return $orderby;
}
add_filter( 'posts_orderby', 'wc_od_admin_posts_orderby_date', 10, 2 );


/** Edit Order functions ******************************************************/


/**
 * Adds the delivery date field to the 'Order Details' metabox.
 *
 * @since 1.0.0
 * @param WC_Order $order The order.
 */
function wc_od_admin_order_data_after_order_details( $order ) {
	$delivery_date = wc_od_localize_date( get_post_meta( $order->id, '_delivery_date', true ), 'Y-m-d' );
	?>
	<p class="form-field form-field-wide">
		<label for="delivery_date"><?php _e( 'Delivery date:', 'woocommerce-order-delivery' ) ?></label>
		<input id="delivery_date" class="date-picker-field" type="text" name="_delivery_date" maxlength="10" value="<?php echo esc_attr( $delivery_date ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
	</p>
	<?php
}
add_action( 'woocommerce_admin_order_data_after_order_details', 'wc_od_admin_order_data_after_order_details' );

/**
 * Saves the delivery date field on the edit-order page.
 *
 * @since 1.0.0
 * @param int     $post_id The post ID.
 * @param WP_Post $post    The post object.
 */
function wc_od_admin_process_shop_order_meta( $post_id, $post ) {
	if ( isset( $_POST['_delivery_date'] ) ) {
		if ( $_POST['_delivery_date'] ) {
			$delivery_date = wc_od_localize_date( esc_attr( $_POST['_delivery_date'] ), 'Y-m-d' );
			if ( $delivery_date ) {
				update_post_meta( $post_id, '_delivery_date', $delivery_date );
			} else {
				delete_post_meta( $post_id, '_delivery_date' );
			}
		} else {
			delete_post_meta( $post_id, '_delivery_date' );
		}
	}
}
add_action( 'woocommerce_process_shop_order_meta', 'wc_od_admin_process_shop_order_meta', 35, 2 );

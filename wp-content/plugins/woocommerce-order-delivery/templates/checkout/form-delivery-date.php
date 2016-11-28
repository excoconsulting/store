<?php
/**
 * Checkout delivery date form
 *
 * @author     WooThemes
 * @package    WC_OD
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div id="wc-od">
	<h3><?php _e( 'Shipping and delivery', 'woocommerce-order-delivery' ); ?></h3>
	<?php
		if ( 'calendar' === $delivery_option ) :
			echo '<p>' . __( 'We will try our best to deliver your order on the specified date.', 'woocommerce-order-delivery' ) . '</p>';
			echo $delivery_date_field;
		else :
			echo '<p>' . sprintf( __( 'We estimate that your order will be shipped on %s.', 'woocommerce-order-delivery' ), '<strong>' . $shipping_date . '</strong>' ) . '</p>';
			echo '<p>' . sprintf( __( 'The delivery will take approximately %s working days from the shipping date.', 'woocommerce-order-delivery' ), "<strong>{$delivery_range['min']}-{$delivery_range['max']}</strong>" ) . '</p>';
		endif;
	?>
</div>
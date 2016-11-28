<?php
/**
 * Order delivery date details
 *
 * @author     WooThemes
 * @package    WC_OD
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<header>
	<h3><?php _e( 'Chosen delivery date', 'woocommerce-order-delivery' ); ?></h3>
</header>

<p><?php printf(
	__( 'We will try our best to deliver your order on %s.', 'woocommerce-order-delivery' ),
	"<strong>{$delivery_date}</strong>" );
?></p>
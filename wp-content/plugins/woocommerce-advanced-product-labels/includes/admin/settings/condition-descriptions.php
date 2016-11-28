<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Descriptions.
 *
 * Display a description icon + tooltip on hover.
 *
 * @since 1.0.0
 *
 * @param string $condition Condition to show description for.
 */
function wapl_condition_description( $condition ) {

	$descriptions = array(
		'product' => __( 'Only display the label for this product', 'woocommerce-advanced-product-labels' ),
	);

	$descriptions = apply_filters( 'wapl_descriptions', $descriptions );

	// Display description
	if ( ! isset( $descriptions[ $condition ] ) ) :
		?><span class='wapl-description no-description'></span><?php
		return;
	endif;

	?><span class='wapl-description <?php echo $condition; ?>-description'>

		<div class='description'>

			<img class='wapl_tip' src='<?php echo WC()->plugin_url(); ?>/assets/images/help.png' height='24' width='24' />

			<div class='wapl_desc'>
				<?php echo $descriptions[ $condition ]; ?>
			</div>

		</div>

	</span><?php

}

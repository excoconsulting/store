<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Create operator dropdown.
 *
 * Set all operators and create dropdown for it.
 *
 * @since 1.0.0
 *
 * @param int 		$id 			Throw in the condition ID.
 * @param int 		$group 			Condition group ID.
 * @param string 	$current_value 	Current chosen slug.
 */
function wapl_condition_operator( $id, $group = 0, $current_value = '==' ) {

	$operators = array(
		'==' => __( 'Equal to', 'woocommerce-advanced-product-labels' ),
		'!=' => __( 'Not equal to', 'woocommerce-advanced-product-labels' ),
		'>=' => __( 'Greater or equal to', 'woocommerce-advanced-product-labels' ),
		'<=' => __( 'Less or equal to ', 'woocommerce-advanced-product-labels' )
	);

	$operators = apply_filters( 'wcam_operators', $operators );

	?><select name='_wapl_global_label[conditions][<?php echo $group; ?>][<?php echo $id; ?>][operator]' class='select wapl-select wapl-select-operators'><?php

		foreach ( $operators as $key => $value ) :

			?><option value='<?php echo $key; ?>' <?php selected( $key, $current_value ); ?>><?php echo $value; ?></option><?php

		endforeach;

	?></select><?php

}

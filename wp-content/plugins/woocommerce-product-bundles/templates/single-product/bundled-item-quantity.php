<?php
/**
 * Bundled Product Quantity Template.
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/bundled-item-quantity.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version 4.14.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$quantity_min = $bundled_item->get_quantity();
$quantity_max = $bundled_item->get_quantity( 'max', true );

if ( $quantity_min === $quantity_max || $bundled_item->is_out_of_stock() ) {

	?><div class="quantity quantity_hidden" style="display:none;"><input class="qty bundled_qty" type="hidden" name="<?php echo $bundle_fields_prefix; ?>bundle_quantity_<?php echo $bundled_item->item_id; ?>" value="<?php echo $quantity_min; ?>" /></div><?php

} else {

	$input_name = $bundle_fields_prefix . 'bundle_quantity_' . $bundled_item->item_id;

	ob_start();

 	woocommerce_quantity_input( array(
 		'input_name'  => $input_name,
 		'min_value'   => $quantity_min,
		'max_value'   => $quantity_max,
 		'input_value' => isset( $_REQUEST[ $input_name ] ) ? $_REQUEST[ $input_name ] : apply_filters( 'woocommerce_bundled_product_quantity', $quantity_min, $quantity_min, $quantity_max, $bundled_item )
 	), $bundled_item->product );

 	echo str_replace( 'qty text', 'qty text bundled_qty', ob_get_clean() );
}

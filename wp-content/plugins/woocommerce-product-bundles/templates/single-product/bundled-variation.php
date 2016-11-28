<?php
/**
 * Bundled Variation Product Template.
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/bundled-variation.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version  4.12.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="woocommerce-variation single_variation bundled_item_cart_details"></div>
<div class="woocommerce-variation-add-to-cart variations_button bundled_item_button">
	<input type="hidden" class="variation_id" name="<?php echo $bundle_fields_prefix . 'bundle_variation_id_' . $bundled_item->item_id; ?>" value=""/><?php

	wc_get_template( 'single-product/bundled-item-quantity.php', array(
		'bundled_item'         => $bundled_item,
		'bundle_fields_prefix' => $bundle_fields_prefix
	), false, WC_PB()->woo_bundles_plugin_path() . '/templates/' );

?></div><?php

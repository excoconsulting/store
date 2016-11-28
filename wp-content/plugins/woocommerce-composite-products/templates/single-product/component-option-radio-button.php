<?php
/**
 * Component Options - Single Radio Button Template.
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/component-option-radio-button.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version 3.6.0
 * @since   3.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id              = false !== $composited_product ? $composited_product->get_product()->id : '';
$product_title           = false !== $composited_product ? $composited_product->get_product()->get_title() : __( 'None', 'woocommerce-composite-products' );
$product_quantity_string = false !== $composited_product ? ( $quantity_min == $quantity_max && $quantity_min > 1 ? $quantity_min : '' ) : '';
$product_price_html      = false !== $composited_product ? $composited_product->get_price_html() : '';
$product_price_string    = false !== $composited_product ? $composited_product->get_price_string() : '';
$selected                = $selected_option == $product_id ? 'selected' : '';

?><li class="component_option_radio_button_container">
	<div id="component_option_radio_button_<?php echo $product_id === '' ? '0' : $product_id; ?>" class="cp_clearfix component_option_radio_button disabled <?php echo $selected; ?>" data-val="<?php echo $product_id; ?>">
		<a class="component_option_radio_button_tap" href="#" ></a>
		<div class="radio_button_input">
			<input type="radio" class="radio_button" name="wccp_component_radio[<?php echo $component_id; ?>]" value="<?php echo $product_id; ?>" <?php checked( $selected, 'selected' ); ?> />
		</div>
		<div class="radio_button_description">
			<h5 class="radio_button_title title"><?php

				echo apply_filters( 'woocommerce_composited_product_radio_button_title', WC_CP_Product::get_title_string( $product_title ), $product_quantity_string, $product_price_string, $product_id, $component_id, $product );

			?></h5>
			<span class="radio_button_price price"><?php

				echo $product_price_html;

			?></span>
		</div>
	</div>

</li><?php

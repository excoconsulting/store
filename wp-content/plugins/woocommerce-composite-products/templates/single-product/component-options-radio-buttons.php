<?php
/**
 * Component Options - Radio Buttons Template.
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/component-options-radio-buttons.php'.
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

?><div id="component_option_radio_buttons_<?php echo $component_id; ?>" class="component_option_radio_buttons" data-columns="1"><?php

	if ( ! empty( $component_options ) ) {

		?><ul class="component_option_radio_buttons_container cp_clearfix" style="list-style:none"><?php

			if ( $component_data[ 'optional' ] === 'yes' ) {

				// Single "None" radio option template.
				wc_get_template( 'single-product/component-option-radio-button.php', array(
					'product'            => $product,
					'composited_product' => false,
					'component_id'       => $component_id,
					'component_data'     => $component_data,
					'selected_option'    => $product->get_current_component_selection( $component_id )
				), '', WC_CP()->plugin_path() . '/templates/' );
			}

			foreach ( $component_options as $product_id ) {

				$composited_product = $product->get_composited_product( $component_id, $product_id );

				if ( ! $composited_product ) {
					continue;
				}

				// Single radio option template.
				wc_get_template( 'single-product/component-option-radio-button.php', array(
					'product'            => $product,
					'composited_product' => $composited_product,
					'component_id'       => $component_id,
					'quantity_min'       => $quantity_min,
					'quantity_max'       => $quantity_max,
					'component_data'     => $component_data,
					'selected_option'    => $product->get_current_component_selection( $component_id )
				), '', WC_CP()->plugin_path() . '/templates/' );
			}
		?></ul><?php
	} else {

		?><p class="no_query_results"><?php
			echo __( 'No results found.', 'woocommerce-composite-products' );
		?></p><?php
	}

	?><div class="cp_clearfix"></div>
</div>

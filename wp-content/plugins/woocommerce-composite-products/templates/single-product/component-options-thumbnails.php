<?php
/**
 * Component Options - Thumbnails Template.
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/component-options-thumbnails.php'.
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

$thumbnail_columns = $product->get_component_columns( $component_id );

?><div id="component_option_thumbnails_<?php echo $component_id; ?>" class="component_option_thumbnails columns-<?php echo esc_attr( $thumbnail_columns ); ?>" data-columns="<?php echo esc_attr( $thumbnail_columns ); ?>"><?php

	if ( ! empty( $component_options ) ) {

		?><ul class="component_option_thumbnails_container cp_clearfix" style="list-style:none"><?php

			$thumbnail_loop = 0;

			foreach ( $component_options as $product_id ) {

				$composited_product = $product->get_composited_product( $component_id, $product_id );

				if ( ! $composited_product ) {
					continue;
				}

				$thumbnail_loop++;

				// Single thumbnail template.
				wc_get_template( 'single-product/component-option-thumbnail.php', array(
					'product'            => $product,
					'composited_product' => $composited_product,
					'thumbnail_columns'  => $thumbnail_columns,
					'thumbnail_loop'     => $thumbnail_loop,
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

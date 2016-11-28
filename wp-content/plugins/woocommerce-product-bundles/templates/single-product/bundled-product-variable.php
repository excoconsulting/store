<?php
/**
 * Variable Bundled Product Template.
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/bundled-product-variable.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version 4.12.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $bundled_product_variations ) {
	echo '<p class="bundled_item_unavailable">' . __( 'This item is not available at the moment.', 'woocommerce-product-bundles' ) . '</p>';
} else {

	?><div class="cart bundled_item_cart_content" data-title="<?php echo esc_attr( $bundled_item->get_raw_title() ); ?>" data-optional_suffix="<?php echo $bundled_item->is_optional() ? apply_filters( 'woocommerce_bundles_optional_bundled_item_suffix', __( 'optional', 'woocommerce-product-bundles' ), $bundled_item, $bundle ) : ''; ?>" data-optional="<?php echo $bundled_item->is_optional() ? 'yes' : 'no'; ?>" data-type="<?php echo $bundled_product->product_type; ?>" data-product_variations="<?php echo esc_attr( json_encode( $bundled_product_variations ) ); ?>" data-bundled_item_id="<?php echo $bundled_item->item_id; ?>" data-product_id="<?php echo $bundled_item->product->id; ?>" data-bundle_id="<?php echo $bundle->id; ?>">
		<table class="variations" cellspacing="0">
			<tbody><?php

				$attribute_keys = array_keys( $bundled_product_attributes );

				foreach ( $bundled_product_attributes as $attribute_name => $options ) {

					?><tr class="attribute-options" data-attribute_label="<?php echo wc_attribute_label( $attribute_name ); ?>">
						<td class="label">
							<label for="<?php echo sanitize_title( $attribute_name ) . '_' . $bundled_item->item_id; ?>"><?php echo wc_attribute_label( $attribute_name ); ?> <abbr class="required" title="<?php _e( 'Required option', 'woocommerce-product-bundles' ); ?>">*</abbr></label>
						</td>
						<td class="value"><?php

							$selected = isset( $_REQUEST[ $bundle_fields_prefix . 'bundle_attribute_' . sanitize_title( $attribute_name ) . '_' . $bundled_item->item_id ] ) ? wc_clean( $_REQUEST[ $bundle_fields_prefix . 'bundle_attribute_' . sanitize_title( $attribute_name ) . '_' . $bundled_item->item_id ] ) : $bundled_item->get_selected_product_variation_attribute( $attribute_name );

							WC_PB_Core_Compatibility::wc_dropdown_variation_attribute_options( array(
								'options'   => $options,
								'attribute' => $attribute_name,
								'name'      => $bundle_fields_prefix . 'bundle_attribute_' . sanitize_title( $attribute_name ) . '_' . $bundled_item->item_id,
								'product'   => $bundled_product,
								'selected'  => $selected,
							) );

							echo end( $attribute_keys ) === $attribute_name ? '<a class="reset_variations" href="#">' . __( 'Clear', 'woocommerce-product-bundles' ) . '</a>' : '';

						?></td>
					</tr><?php
				}

			?></tbody>
		</table><?php

		/**
		 * woocommerce_bundled_product_add_to_cart hook.
		 *
		 * Used to output content normally hooked to 'woocommerce_before_add_to_cart_button'.
		 */
		do_action( 'woocommerce_bundled_product_add_to_cart', $bundled_product->id, $bundled_item );

		?><div class="single_variation_wrap bundled_item_wrap"><?php

			/**
			 * woocommerce_bundled_single_variation hook. Used to output variation data.
			 * @since 4.12.0
			 *
			 * @hooked wc_bundles_single_variation - 10
			 */
			do_action( 'woocommerce_bundled_single_variation', $bundled_product->id, $bundled_item );

		?></div>
	</div><?php
}

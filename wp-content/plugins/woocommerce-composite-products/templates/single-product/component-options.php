<?php
/**
 * Component Options Template.
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/component-options.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version 3.6.0
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_static     = $product->is_component_static( $component_id );
$is_optional   = $component_data[ 'optional' ] === 'yes';
$quantity_min  = $component_data[ 'quantity_min' ];
$quantity_max  = $component_data[ 'quantity_max' ];
$options_style = $product->get_component_options_style( $component_id );

?><div class="component_options" style="<?php echo $is_static ? 'display:none;' : ''; ?>">

	<div class="component_options_inner cp_clearfix">

		<p class="component_section_title">
			<label class="select_label">
				<?php echo __( 'Available options:', 'woocommerce-composite-products' ); ?>
			</label>
		</p><?php

		/**
		 * Action 'woocommerce_composite_component_options_{$options_style}'.
		 *
		 * @since  3.6.0
		 *
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 *
		 * @hooked wc_cp_component_options_thumbnails - 10
		 * @hooked wc_cp_component_options_radios     - 10
		 */
		do_action( 'woocommerce_composite_component_options_' . $options_style, $component_id, $product );

		?><div class="component_options_select_wrapper" style="<?php echo $options_style !== 'dropdowns' ? 'display:none;' : ''; ?>">
			<select id="component_options_<?php echo $component_id; ?>" class="component_options_select" name="wccp_component_selection[<?php echo $component_id; ?>]"><?php

				if ( ! $is_static ) {
					?><option class="empty none" data-title="<?php echo __( 'None', 'woocommerce-composite-products' ); ?>" value=""><?php echo $is_optional ? __( 'None', 'woocommerce-composite-products' ) : __( 'Choose an option&hellip;', 'woocommerce-composite-products' ); ?></option><?php
				}

				// If results are paginated, ensure that the current selection is added to the (hidden) dropdown.
				if ( WC_CP()->api->options_style_supports( $options_style, 'pagination' ) && $selected_option && ! in_array( $selected_option, $component_options ) ) {
					$component_options[] = $selected_option;
				}

				foreach ( $component_options as $product_id ) {

					$composited_product = $product->get_composited_product( $component_id, $product_id );

					if ( ! $composited_product ) {
						continue;
					}

					if ( has_post_thumbnail( $product_id ) ) {
						$attachment_id = get_post_thumbnail_id( $product_id );
						$attachment    = wp_get_attachment_image_src( $attachment_id, apply_filters( 'woocommerce_composite_component_option_image_size', 'shop_catalog' ) );
						$image_src     = $attachment ? current( $attachment ) : false;
					} else {
						$image_src = wc_placeholder_img_src();
					}

					$title           = $composited_product->get_product()->get_title();
					$quantity_string = $quantity_min == $quantity_max && $quantity_min > 1 ? $quantity_min : '';
					$price_string    = $composited_product->get_price_string();

					?><option data-title="<?php echo esc_attr( $title ); ?>" data-image_src="<?php echo esc_attr( $image_src ); ?>" value="<?php echo $product_id; ?>" <?php echo selected( $selected_option, $product_id, false ); ?>><?php

						echo apply_filters( 'woocommerce_composited_product_dropdown_title', WC_CP_Product::get_title_string( $title, '', $price_string ), $quantity_string, $price_string, $product_id, $component_id, $product );

					?></option><?php
				}
			?></select>
		</div>
		<div class="cp_clearfix"></div>
	</div>
</div><?php

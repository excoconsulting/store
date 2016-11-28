<?php
/**
 * Composited Simple Product Template.
 *
 * Override this template by copying it to 'yourtheme/woocommerce/composited-product/simple-product.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version  3.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><div class="details component_data" data-price="<?php echo esc_attr( $price ); ?>" data-regular_price="<?php echo esc_attr( $regular_price ); ?>" data-product_type="simple" data-custom="<?php echo esc_attr( json_encode( $custom_data ) ); ?>"><?php

	/**
	 * woocommerce_composited_product_details hook.
	 * @since 3.2.0
	 *
	 * @hooked wc_cp_composited_product_excerpt - 10
	 */
	do_action( 'woocommerce_composited_product_details', $product, $component_id, $composite_product );

	?><div class="component_wrap"><?php

		/**
		 * woocommerce_composited_product_add_to_cart hook.
		 *
		 * @hooked wc_cp_composited_product_price - 8
		 */
		do_action( 'woocommerce_composited_product_add_to_cart', $product, $component_id, $composite_product );

		$availability      = WC_CP()->api->get_composited_item_availability( $product, $quantity_min );
		$availability_html = empty( $availability[ 'availability' ] ) ? '' : '<p class="stock ' . esc_attr( $availability[ 'class' ] ) . '">' . esc_html( $availability[ 'availability' ] ) . '</p>';

		echo apply_filters( 'woocommerce_stock_html', $availability_html, $availability[ 'availability' ], $product );

		?><div class="quantity_button"><?php

	 		wc_get_template( 'composited-product/quantity.php', array(
				'quantity_min'      => $quantity_min,
				'quantity_max'      => $quantity_max,
				'component_id'      => $component_id,
				'product'           => $product,
				'composite_product' => $composite_product
			), '', WC_CP()->plugin_path() . '/templates/' );

		?></div>
	</div>
</div>


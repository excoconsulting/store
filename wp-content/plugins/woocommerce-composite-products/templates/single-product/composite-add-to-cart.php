<?php
/**
 * Composite add-to-cart panel template.
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/composite-add-to-cart.php'.
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

?><div id="composite_data_<?php echo $product->id; ?>" class="cart composite_data <?php echo isset( $_POST[ 'add-to-cart' ] ) ? 'composite_added_to_cart' : ''; ?>" data-item_id="review" data-composite_settings="<?php echo esc_attr( json_encode( $product->add_to_cart_form_settings() ) ); ?>" data-nav_title="<?php echo esc_attr( __( 'Review Configuration', 'woocommerce-composite-products' ) ); ?>" data-scenario_data="<?php echo esc_attr( json_encode( $product->get_current_scenario_data() ) ); ?>" data-price_data="<?php echo esc_attr( json_encode( $product->get_composite_price_data() ) ); ?>" data-container_id="<?php echo $product->id; ?>" style="display:none;"><?php

	do_action( 'woocommerce_before_add_to_cart_button' );

	?><div class="composite_wrap" style="<?php echo apply_filters( 'woocommerce_composite_button_behaviour', 'new', $product ) === 'new' ? '' : 'display:none'; ?>">
		<div class="composite_price"></div>
		<div class="composite_message" style="display:none;"><ul class="msg woocommerce-info"></ul></div>
		<div class="composite_availability"><?php
			$availability      = $product->get_availability();
			$availability_html = empty( $availability[ 'availability' ] ) ? '' : '<p class="stock ' . esc_attr( $availability[ 'class' ] ) . '">' . esc_html( $availability[ 'availability' ] ) . '</p>';

			echo apply_filters( 'woocommerce_stock_html', $availability_html, $availability[ 'availability' ], $product );
		?></div>
		<div class="composite_button"><?php

			/**
			 * Action 'woocommerce_composite_add_to_cart_button'.
			 *
			 * @hooked wc_cp_add_to_cart_button - 10
			 */
			do_action( 'woocommerce_composite_add_to_cart_button' );

			?><input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->id ); ?>" />
		</div>
	</div><?php

	do_action( 'woocommerce_after_add_to_cart_button' );

?></div><?php

do_action( 'woocommerce_after_add_to_cart_form' );

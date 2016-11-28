<?php
/**
 * Composited Product Image.
 *
 * Override this template by copying it to 'yourtheme/woocommerce/composited-product/image.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version  3.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><div class="composited_product_images"><?php

	if ( has_post_thumbnail( $product_id ) ) {

		$image_title = esc_attr( get_the_title( get_post_thumbnail_id( $product_id ) ) );
		$image_link  = wp_get_attachment_url( get_post_thumbnail_id( $product_id ) );
		$image       = get_the_post_thumbnail( $product_id, apply_filters( 'woocommerce_composited_product_thumbnail_size', 'shop_catalog' ), array(
			'title' => $image_title
		) );

		echo apply_filters( 'woocommerce_composited_product_image_html', sprintf( '<a href="%s" class="composited_product_image zoom" title="%s" data-rel="prettyPhoto">%s</a>', $image_link, $image_title, $image ), $product_id );
	} else {
		echo apply_filters( 'woocommerce_composited_product_image_html', sprintf( '<a href="%1$s" class="composited_product_image zoom" title="%2$s" data-rel="prettyPhoto"><img src="%1$s" alt="%2$s" /></a>', wc_placeholder_img_src(), __( 'Placeholder', 'woocommerce' ) ), $product_id );
	}

?></div>

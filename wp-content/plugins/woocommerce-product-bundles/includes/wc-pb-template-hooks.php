<?php
/**
 * Product Bundles Single Product Template Hooks.
 *
 * @version  4.12.0
 * @since    4.11.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Single product template for Product Bundles.
add_action( 'woocommerce_bundle_add_to_cart', 'wc_bundles_add_to_cart' );

// Single product add-to-cart button template for Product Bundles.
add_action( 'woocommerce_bundles_add_to_cart_button', 'wc_bundles_add_to_cart_button' );

// Bundled item wrapper open.
add_action( 'wc_bundles_bundled_item_details', 'wc_bundles_bundled_item_details_wrapper_open', 0, 2 );

// Bundled item image.
add_action( 'wc_bundles_bundled_item_details', 'wc_bundles_bundled_item_thumbnail', 5, 2 );

// Bundled item details container open.
add_action( 'wc_bundles_bundled_item_details', 'wc_bundles_bundled_item_details_open', 10, 2 );

// Bundled item title.
add_action( 'wc_bundles_bundled_item_details', 'wc_bundles_bundled_item_title', 15, 2 );

// Bundled item description.
add_action( 'wc_bundles_bundled_item_details', 'wc_bundles_bundled_item_description', 20, 2 );

// Bundled product details template.
add_action( 'wc_bundles_bundled_item_details', 'wc_bundles_bundled_item_product_details', 25, 2 );

// Bundled item details container close.
add_action( 'wc_bundles_bundled_item_details', 'wc_bundles_bundled_item_details_close', 30, 2 );

// Bundled item wrapper close.
add_action( 'wc_bundles_bundled_item_details', 'wc_bundles_bundled_item_details_wrapper_close', 100, 2 );

// Bundled variation template.
add_action( 'woocommerce_bundled_single_variation', 'wc_bundles_single_variation', 100, 2 );

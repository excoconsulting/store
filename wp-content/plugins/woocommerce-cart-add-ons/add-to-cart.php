<?php
/**
 * Loop Add to Cart
 *
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     1.6.4
 */

global $product, $woocommerce;

if ( ! $product->is_purchasable() && ! in_array( $product->product_type, array( 'external', 'grouped' ) ) ) return;
?>

<?php if ( ! $product->is_in_stock() ) : ?>

    <a href="<?php echo apply_filters( 'out_of_stock_add_to_cart_url', get_permalink( $product->id ) ); ?>" class="button"><?php echo apply_filters( 'out_of_stock_add_to_cart_text', __( 'Read More', 'woocommerce' ) ); ?></a>

<?php else : ?>

    <?php

        switch ( $product->product_type ) {
            case "variation" :
                $url    = add_query_arg( array('add-to-cart' => $product->id, 'variation_id' => $product->variation_id), get_permalink( $product->id ) );
                $variation = sfn_get_product( $product->variation_id );

                // load attributes
                foreach ( $variation->variation_data as $key => $value ) {
                    $url = add_query_arg( array($key => $value), $url );
                }

                $link   = apply_filters( 'variation_add_to_cart_url', $url );
                $label  = apply_filters( 'variation_add_to_cart_text', __('Add to Cart', 'woocommerce') );
            break;
        }

        printf('<a href="%s" rel="nofollow" data-product_id="%s" class="add_to_cart_button button product_type_%s">%s</a>', $link, $product->id, $product->product_type, $label);

    ?>

<?php endif; ?>

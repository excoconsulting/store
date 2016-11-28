<?php
/**
 * Component options pagination template.
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/component-options-pagination.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version  3.6.0
 * @since  2.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options_style = $product->get_component_options_style( $component_id );

if ( WC_CP()->api->options_style_supports( $options_style, 'pagination' ) && ! $product->is_component_static( $component_id ) ) {

	$per_page = $product->get_component_results_per_page( $component_id );

	if ( $product->get_current_component_options_query( $component_id )->has_pages() ) {

		$current_page = $product->get_current_component_options_query( $component_id )->get_current_page();
		$pages        = $product->get_current_component_options_query( $component_id )->get_pages_num();

		if ( $product->paginate_component_options( $component_id ) ) {

			$range     = apply_filters( 'woocommerce_component_options_pagination_range', 2, $component_id, $product );
			$showitems = ( $range * 2 ) + 1;

			?><div class="component_pagination">

				<span class="component_pagination_element index"><?php echo sprintf( __( '%1$s of %2$s', 'woocommerce-composite-products' ), $current_page, $pages ); ?></span>
				<?php

				// First
				if ( $current_page > 2 && $current_page > $range + 1 && $showitems < $pages ) {
					?><a class="component_pagination_element first" data-page_num="1" href='#'><?php echo __( '&laquo; First', 'woocommerce-composite-products' ); ?></a><?php
				}

				// Previous
				if ( $current_page > 1 && $showitems < $pages ) {
					?><a class="component_pagination_element previous" data-page_num="<?php echo $current_page - 1; ?>" href='#'><?php echo __( '&lsaquo; Previous', 'woocommerce-composite-products' ); ?></a><?php
				}

				// Page Numbers
				for ( $i = 1; $i <= $pages; $i++ ) {
					 if ( ! ( $i >= $current_page + $range + 1 || $i <= $current_page - $range - 1 ) || $pages <= $showitems ) {
					 	if ( $current_page == $i ) {
					 		?><span class="component_pagination_element number current" data-page_num="<?php echo $i; ?>"><?php echo $i; ?></span><?php
						} else {
							?><a class="component_pagination_element number" data-page_num="<?php echo $i; ?>" href="#"><?php echo $i; ?></a><?php
						}
					}
				}

				// Next
				if ( $current_page < $pages && $showitems < $pages ) {
					?><a class="component_pagination_element first" data-page_num="<?php echo $current_page + 1; ?>" href='#'><?php echo __( 'Next &rsaquo;', 'woocommerce-composite-products' ); ?></a><?php
				}

				// Last
				if ( $current_page < $pages - 1 &&  $current_page + $range - 1 < $pages && $showitems < $pages ) {
					?><a class="component_pagination_element previous" data-page_num="<?php echo $pages; ?>" href='#'><?php echo __( 'Last &raquo;', 'woocommerce-composite-products' ); ?></a><?php
				}

				?>
				<div class="pagination_data" data-results_per_page="<?php echo esc_attr( $per_page ); ?>" data-page_num="<?php echo esc_attr( $current_page ); ?>" data-pages="<?php echo esc_attr( $pages ); ?>" style="display:none;"></div>
			</div><?php

		} else {

			?><div class="component_pagination component_options_append">
				<a class="button component_options_load_more" href='#'><?php echo __( 'Load more&hellip;', 'woocommerce-composite-products' ); ?></a>
				<div class="pagination_data" data-results_per_page="<?php echo esc_attr( $per_page ); ?>" data-page_num="<?php echo esc_attr( $current_page ); ?>" data-pages="<?php echo esc_attr( $pages ); ?>" style="display:none;"></div>
			</div><?php
		}

	} else {

		?><div class="component_pagination" style="display:none">
			<div class="pagination_data" data-results_per_page="<?php echo esc_attr( $per_page ); ?>" data-page_num="1" data-pages="1" style="display:none;"></div>
		</div><?php
	}
}

<?php
/**
 * WooCommerce Nested Category Layout
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Nested Category Layout to newer
 * versions in the future. If you wish to customize WooCommerce Nested Category Layout for your
 * needs please refer to http://docs.woothemes.com/document/woocommerce-nested-category-layout/ for more information.
 *
 * @package   WC-Nested-Category-Layout/Templates
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

/**
 * Template Function Overrides
 */

defined( 'ABSPATH' ) or exit;


if ( ! function_exists( 'woocommerce_nested_category_products_content_section' ) ) {
	/**
	 * Our own template function, called for each product-containing nested category section
	 * on the modified shop pages
	 *
	 *
	 * @access public
	 * @since 1.0
	 * @param array $categories array of product category objects
	 * @param array $product_category_ids associative array of product id to array
	 *        of deepest categories the products belong to
	 */
	function woocommerce_nested_category_products_content_section( $categories, $product_category_ids ) {
		global $wp_query;

		$title = '';
		$term = '';

		// Build up the sub-category title, starting with the title of the current page category
		if ( is_product_category() ) {
			$term = get_term_by( 'slug', get_query_var( $wp_query->query_vars['taxonomy'] ), $wp_query->query_vars['taxonomy'] );
			$title = '<span>' . $term->name . '</span>';
		}

		// add any saved up category titles, along with the current
		foreach ( $categories as $title_cat ) {
			$url = esc_attr( get_term_link( $title_cat ) );
			$title .= ( $title ? ' - ' : '' ) . '<a href="' . $url . '">' . wptexturize( $title_cat->name ) . '</a>';
		}

		// subcategory header
		echo wp_kses_post( apply_filters( 'wc_nested_category_layout_category_title_html', sprintf( '<h2 class="wc-nested-category-layout-category-title">%s</h2>', $title ), $categories, $term ) );

		// optional thumbnail/description of the category
		$category = $categories[ count( $categories ) - 1 ];
		$thumbnail_id = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true );

		if ( $thumbnail_id ) {
			$image = wp_get_attachment_image_src( $thumbnail_id );
			$image = $image[0];
			echo apply_filters( 'wc_nested_category_layout_category_image', '<img style="float:left;" src="' . $image . '" alt="' . $category->name . '" />', $category );
		}

		// Optional category description
		if ( $category->description ) {
			$description = apply_filters( 'the_content', $category->description );
			if ( $description ) {
				echo '<div class="subcategory-term_description term_description">' . $description . '</div>';
			}
		}

		wc_get_template( 'loop/nested-category.php', array( 'woocommerce_product_category_ids' => $product_category_ids, 'category' => $title_cat ), '', wc_nested_category_layout()->get_plugin_path() . '/templates/' );
	}
}


if ( ! function_exists( 'woocommerce_category_products_content_section' ) ) {
	/**
	 * Our own template function, called for the current page category products,
	 * if they are contained by none of the deeper nested sub-categories
	 *
	 * @since 1.0
	 * @param object $category category object, or null if on the /shop/ page
	 * @param array $product_category_ids associative array of product id to array
	 *        of deepest categories the products belong to
	 */
	function woocommerce_category_products_content_section( $category, $product_category_ids ) {

		wc_get_template( 'loop/nested-category.php', array( 'woocommerce_product_category_ids' => $product_category_ids, 'category' => $category ), '', wc_nested_category_layout()->get_plugin_path() . '/templates/' );
	}
}


if ( ! function_exists( 'woocommerce_product_subcategories' ) ) {

	/**
	 * Display product sub categories as thumbnails.
	 *
	 * This is a largely unchanged copy of the core woocommerce function, which
	 * simply bails if the nested category layout is detected to be enabled
	 * on the current page.
	 *
	 * Code based on WooCommerce 2.0.3 woocommerce_product_subcategories()
	 * @see woocommerce/woocommerce-template.php
	 *
	 * @access public
	 * @subpackage	Loop
	 * @return void
	 */
	function woocommerce_product_subcategories( $args = array() ) {
		global $wp_query;

		// JES: don't show the subcategory thumbnails on the shop page or product category page with the nested layout option is enabled
		if ( ( is_shop() && 'yes' === get_option( 'woocommerce_nested_subcat_shop', 'no' ) ) ||
			( is_product_category() && 'yes' === get_option( 'woocommerce_nested_subcat_' . wc_nested_category_layout()->get_current_product_category_id(), 'no' ) ) ) {
			return true;
		}
		// End of modification

		$defaults = array(
			'before'        => '',
			'after'         => '',
			'force_display' => false
		);

		$args = wp_parse_args( $args, $defaults );

		extract( $args );

		// Main query only
		if ( ! is_main_query() && ! $force_display ) {
			return;
		}

		// Don't show when filtering, searching or when on page > 1 and ensure we're on a product archive
		if ( is_search() || is_filtered() || is_paged() || ( ! is_product_category() && ! is_shop() ) ) {
			return;
		}

		// Check categories are enabled
		if ( is_shop() && get_option( 'woocommerce_shop_page_display' ) === '' ) {
			return;
		}

		// Find the category + category parent, if applicable
		$term 			= get_queried_object();
		$parent_id 		= empty( $term->term_id ) ? 0 : $term->term_id;

		if ( is_product_category() ) {
			$display_type = get_woocommerce_term_meta( $term->term_id, 'display_type', true );

			switch ( $display_type ) {
				case 'products' :
					return;
				break;
				case '' :
					if ( get_option( 'woocommerce_category_archive_display' ) === '' ) {
						return;
					}
				break;
			}
		}

		// NOTE: using child_of instead of parent - this is not ideal but due to a WP bug ( http://core.trac.wordpress.org/ticket/15626 ) pad_counts won't work
		$args = apply_filters( 'woocommerce_product_subcategories_args', array(
			'parent'		=> $parent_id,
			'menu_order'	=> 'ASC',
			'hide_empty'	=> 1,
			'hierarchical'	=> 1,
			'taxonomy'		=> 'product_cat',
			'pad_counts'	=> 1
		) );

		$product_categories     = get_categories( $args );
		$product_category_found = false;

		if ( $product_categories ) {
			echo $before;

			foreach ( $product_categories as $category ) {
				wc_get_template( 'content-product_cat.php', array(
					'category' => $category
				) );
			}

			// If we are hiding products disable the loop and pagination
			if ( is_product_category() ) {
				$display_type = get_woocommerce_term_meta( $term->term_id, 'display_type', true );

				switch ( $display_type ) {
					case 'subcategories' :
						$wp_query->post_count    = 0;
						$wp_query->max_num_pages = 0;
					break;
					case '' :
						if ( get_option( 'woocommerce_category_archive_display' ) === 'subcategories' ) {
							$wp_query->post_count    = 0;
							$wp_query->max_num_pages = 0;
						}
					break;
				}
			}

			if ( is_shop() && get_option( 'woocommerce_shop_page_display' ) === 'subcategories' ) {
				$wp_query->post_count    = 0;
				$wp_query->max_num_pages = 0;
			}

			echo $after;
		}

		return true;
	}
}


if ( ! function_exists( 'woocommerce_reset_loop' ) ) {

	/**
	 * Reset the loop's index and columns when we're done outputting a product loop.
	 *
	 * JES: modify to record the fact that products have been displayed
	 *
	 * Code based on WooCommerce 2.0.3 woocommerce_reset_loop()
	 * @see woocommerce/woocommerce-template.php
	 *
	 * @access public
	 * @subpackage	Loop
	 * @return void
	 */
	function woocommerce_reset_loop() {
		global $woocommerce_loop;

		// JES: modification
		if ( isset( $woocommerce_loop['loop'] ) && $woocommerce_loop['loop'] ) $woocommerce_loop['has_products'] = true;

		// Reset loop/columns globals when starting a new loop
		$woocommerce_loop['loop'] = $woocommerce_loop['column'] = '';
	}
}

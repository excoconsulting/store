<?php
/**
 * Woocommerce configuration file
 */

// Adds theme support for woocommerce
add_theme_support('woocommerce');

//Disable the default WooCommerce stylesheet.
if ( version_compare( WOOCOMMERCE_VERSION, "2.1" ) >= 0 ) {
	add_filter( 'woocommerce_enqueue_styles', '__return_false' );
} else {
	define( 'WOOCOMMERCE_USE_CSS', false );
}

if (!function_exists('walker_edge_woocommerce_content')){
	/**
	 * Output WooCommerce content.
	 *
	 * This function is only used in the optional 'woocommerce.php' template
	 * which people can add to their themes to add basic woocommerce support
	 * without hooks or modifying core templates.
	 *
	 * @access public
	 * @return void
	 */
	function walker_edge_woocommerce_content() {

		if ( is_singular( 'product' ) ) {

			while ( have_posts() ) : the_post();

				wc_get_template_part( 'content', 'single-product' );

			endwhile;

		} else {

			if ( have_posts() ) :

				/**
				 * woocommerce_before_shop_loop hook.
				 *
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 */
				do_action( 'woocommerce_before_shop_loop' );

				woocommerce_product_loop_start();

					woocommerce_product_subcategories();

					while ( have_posts() ) : the_post();

						wc_get_template_part( 'content', 'product' );

					endwhile; // end of the loop.

				woocommerce_product_loop_end();

				/**
				 * woocommerce_after_shop_loop hook.
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				do_action( 'woocommerce_after_shop_loop' );

			elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) :

				wc_get_template( 'loop/no-products-found.php' );

			endif;
		}
	}
}

/*************** GENERAL FILTERS - begin ***************/

	//Define number of products per page
	add_filter('loop_shop_per_page', 'walker_edge_woocommerce_products_per_page', 20);

	//Set number of related products
	add_filter('woocommerce_output_related_products_args', 'walker_edge_woocommerce_related_products_args');

	//Sale flash template override
	add_filter('woocommerce_sale_flash', 'walker_edge_woocommerce_sale_flash');

	//Out of stock template
	add_filter('woocommerce_product_thumbnails', 'walker_edge_woocommerce_product_out_of_stock');
	add_action('woocommerce_before_shop_loop_item_title', 'walker_edge_woocommerce_product_out_of_stock', 10);

	//New product template
	add_filter('woocommerce_product_thumbnails', 'walker_edge_woocommerce_product_new_product');
	add_action('woocommerce_before_shop_loop_item_title', 'walker_edge_woocommerce_product_new_product', 10);

	//Add view all pagination for product lists
	add_action('woocommerce_after_shop_loop', 'walker_edge_woocommerce_view_all_pagination', 11);

	//Add additional html tags around woocommerce pagination
	add_action('woocommerce_after_shop_loop', 'walker_edge_woo_view_all_pagination_additional_tag_before', 9);
	add_action('woocommerce_after_shop_loop', 'walker_edge_woo_view_all_pagination_additional_tag_after', 12);

	//Override woocommerce add to cart html tag
	add_filter('woocommerce_loop_add_to_cart_link', 'walker_edge_woocommerce_loop_add_to_cart_link', 2);

/*************** GENERAL FILTERS - end ***************/	

/*************** PRODUCT LISTS FILTERS - begin ***************/

	//Override porduct list order by select
	add_filter('woocommerce_catalog_orderby', 'walker_edge_override_woocommerce_catalog_orderby');

	//Add additional html tags around product lists
	add_action('woocommerce_before_shop_loop', 'walker_edge_pl_holder_additional_tag_before', 35);
	add_action('woocommerce_after_shop_loop', 'walker_edge_pl_holder_additional_tag_after', 5);

	//Add open additional html tag around product elements
	add_action('woocommerce_before_shop_loop_item', 'walker_edge_pl_inner_additional_tag_before', 5);

	//Remove open and close link position
	remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
	remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
	
	//Add additional html tags around image and marks
	add_action('woocommerce_before_shop_loop_item_title', 'walker_edge_pl_image_additional_tag_before', 5);
	add_action('woocommerce_before_shop_loop_item_title', 'walker_edge_pl_image_additional_tag_after', 15);

	//Add yith quick view button
	add_action('woocommerce_before_shop_loop_item_title', 'walker_edge_woocommerce_quick_view_button', 17);

	//Add yith wishlist button
	add_action('woocommerce_before_shop_loop_item_title', 'walker_edge_woocommerce_wishlist_shortcode', 18);

	//Add additional html tags around yith elements
	add_action('woocommerce_before_shop_loop_item_title', 'walker_edge_pl_yith_additional_tag_before', 16);
	add_action('woocommerce_before_shop_loop_item_title', 'walker_edge_pl_yith_additional_tag_after', 19);


	/*************** Product Info Position Is On Image Hover ***************/

		//Add end additional html tag around product elements
		add_action('walker_edge_woo_pl_info_on_image_hover', 'walker_edge_pl_inner_additional_tag_after', 20);

		//Add open and close link position
		add_action('walker_edge_woo_pl_info_on_image_hover', 'woocommerce_template_loop_product_link_open', 19);
		add_action('walker_edge_woo_pl_info_on_image_hover', 'woocommerce_template_loop_product_link_close', 19);

		//Add additional html around product info elements
		add_action('walker_edge_woo_pl_info_on_image_hover', 'walker_edge_pl_inner_text_additional_tag_before', 5);
		add_action('walker_edge_woo_pl_info_on_image_hover', 'walker_edge_pl_inner_text_additional_tag_after', 15);

		//Override product title with our own html
		remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
		add_action('walker_edge_woo_pl_info_on_image_hover', 'walker_edge_woocommerce_template_loop_product_title', 7);

		//Change price position
		remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
		add_action('walker_edge_woo_pl_info_on_image_hover', 'woocommerce_template_loop_price', 10);

		//Change rating star position
		remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
		add_action('walker_edge_woo_pl_info_on_image_hover', 'woocommerce_template_loop_rating', 12);

		//Add additional html tags around rating star element
		add_action( 'walker_edge_woo_pl_info_on_image_hover', 'walker_edge_pl_rating_additional_tag_before', 11);
		add_action( 'walker_edge_woo_pl_info_on_image_hover', 'walker_edge_pl_rating_additional_tag_after', 13);

		//Change add to cart position
		remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
		add_action('walker_edge_woo_pl_info_on_image_hover', 'woocommerce_template_loop_add_to_cart', 14);

	/*************** Product Info Position Is Below Image ***************/

		//Add end additional html tag around product elements
		add_action('walker_edge_woo_pl_info_below_image', 'walker_edge_pl_inner_additional_tag_after', 17);

		//Add open and close link position
		add_action('walker_edge_woo_pl_info_below_image', 'woocommerce_template_loop_product_link_open', 16);
		add_action('walker_edge_woo_pl_info_below_image', 'woocommerce_template_loop_product_link_close', 16);

		//Add additional html around product info elements
		add_action('walker_edge_woo_pl_info_below_image', 'walker_edge_pl_inner_text_additional_tag_before', 5);
		add_action('walker_edge_woo_pl_info_below_image', 'walker_edge_pl_inner_text_additional_tag_after', 15);

		//Add additional html at the end of product info elements
		add_action('walker_edge_woo_pl_info_below_image', 'walker_edge_pl_text_wrapper_additional_tag_before', 20);
		add_action('walker_edge_woo_pl_info_below_image', 'walker_edge_pl_text_wrapper_additional_tag_after', 30);

		//Override product title with our own html
		remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
		add_action('walker_edge_woo_pl_info_below_image', 'walker_edge_woocommerce_template_loop_product_title', 22);

		//Change price position
		remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
		add_action('walker_edge_woo_pl_info_below_image', 'woocommerce_template_loop_price', 24);

		//Change rating star position
		remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
		add_action('walker_edge_woo_pl_info_below_image', 'woocommerce_template_loop_rating', 26);

		//Add additional html tags around rating star element
		add_action('walker_edge_woo_pl_info_below_image', 'walker_edge_pl_rating_additional_tag_before', 25);
		add_action('walker_edge_woo_pl_info_below_image', 'walker_edge_pl_rating_additional_tag_after', 27);

		//Change add to cart position
		remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
		add_action('walker_edge_woo_pl_info_below_image', 'woocommerce_template_loop_add_to_cart', 14);
		

/*************** PRODUCT LISTS FILTERS - end ***************/

/*************** PRODUCT SINGLE FILTERS - begin ***************/

	//Add additional html around single product summary and images
	add_action('woocommerce_before_single_product_summary', 'walker_edge_single_product_content_additional_tag_before', 5);
	add_action('woocommerce_after_single_product_summary', 'walker_edge_single_product_content_additional_tag_after', 1);

	//Add additional html around single product summary
	add_action('woocommerce_before_single_product_summary', 'walker_edge_single_product_summary_additional_tag_before', 30);
	add_action('woocommerce_after_single_product_summary', 'walker_edge_single_product_summary_additional_tag_after', 5);

	//Add additional html around single product info
	add_action('woocommerce_after_single_product_summary', 'walker_edge_single_product_additional_tag_before', 8);
	add_action('woocommerce_after_single_product_summary', 'walker_edge_single_product_additional_tag_after', 30);

	//Override product thumbnaiil columns size
	add_filter('woocommerce_product_thumbnails_columns', 'walker_edge_woocommerce_product_thumbnail_column_size', 10);

	//Override product thumbnaiil size
	add_filter('single_product_small_thumbnail_size', 'walker_edge_woocommerce_product_thumbnail_size', 10);

	//Change title position
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
	add_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 8);

	//Change price position
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
	add_action('woocommerce_single_product_summary', 'walker_edge_woocommerce_template_single_title', 5);

	//Change product meta position
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
	add_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 25);

	//Add social share (default woocommerce_share)
	add_action('woocommerce_single_product_summary', 'walker_edge_woocommerce_share', 28);

	//Change tabs position
	remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
	add_action('woocommerce_single_product_summary', 'woocommerce_output_product_data_tabs', 60);

	//Change yith wishlist button position
	add_filter('yith_wcwl_positions', 'walker_edge_woocommerce_wishlist_position', 10);


/*************** PRODUCT SINGLE FILTERS - end ***************/	

/*************** YITH QUICK VIEW CONTENT FILTERS - begin ***************/

	//Change rating star position
	remove_action('yith_wcqv_product_summary', 'woocommerce_template_single_rating', 10);
	add_action('yith_wcqv_product_summary', 'woocommerce_template_single_rating', 15);

	//Change price position
	remove_action('yith_wcqv_product_summary', 'woocommerce_template_single_price', 15);
	add_action('yith_wcqv_product_summary', 'woocommerce_template_single_price', 10);

	//Change add to cart button position
	remove_action('yith_wcqv_product_summary', 'woocommerce_template_single_add_to_cart', 25);
	add_action('yith_wcqv_product_summary', 'woocommerce_template_single_add_to_cart', 30);

	//Change product meta position
	remove_action('yith_wcqv_product_summary', 'woocommerce_template_single_meta', 30);
	add_action('yith_wcqv_product_summary', 'woocommerce_template_single_meta', 25);

	//Add social share (default woocommerce_share)
	add_action( 'yith_wcqv_product_summary', 'walker_edge_woocommerce_share', 26);

	//Add yith wishlist button
	add_action('yith_wcqv_product_summary', 'walker_edge_woocommerce_wishlist_shortcode', 27);

/*************** YITH QUICK VIEW CONTENT FILTERS - end ***************/	
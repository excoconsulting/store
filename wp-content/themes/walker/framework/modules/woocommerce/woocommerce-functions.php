<?php
/**
 * Woocommerce helper functions
 */

if(!function_exists('walker_edge_disable_woocommerce_pretty_photo')) {
    /**
     * Function that disable WooCommerce pretty photo script and style
     */
    function walker_edge_disable_woocommerce_pretty_photo() {
        //is woocommerce installed?
        if(walker_edge_is_woocommerce_installed()) {
            if(walker_edge_load_woo_assets()) {

                wp_deregister_style('woocommerce_prettyPhoto_css');
            }
        }
    }

    add_action('wp_enqueue_scripts', 'walker_edge_disable_woocommerce_pretty_photo');
}

if(!function_exists('walker_edge_disable_yith_pretty_photo')) {
    /**
     * Function that disable YITH Quick View pretty photo style
     */
    function walker_edge_disable_yith_pretty_photo() {
        //is woocommerce installed?
        if(walker_edge_is_woocommerce_installed() && walker_edge_is_yith_wcqv_install()) {

            wp_deregister_style('woocommerce_prettyPhoto_css');
        }
    }

	add_action('wp_footer', 'walker_edge_disable_yith_pretty_photo');
}

if(!function_exists('walker_edge_disable_yith_wl_pretty_photo')) {
    /**
     * Function that disable YITH Wishlist pretty photo style
     */
    function walker_edge_disable_yith_wl_pretty_photo() {
        //is woocommerce installed?
        if(walker_edge_is_woocommerce_installed() && walker_edge_is_yith_wishlist_install()) {

            wp_deregister_style('woocommerce_prettyPhoto_css');
        }
    }

	add_action('wp_enqueue_scripts', 'walker_edge_disable_yith_wl_pretty_photo');
}

if (!function_exists('walker_edge_woocommerce_body_class')) {
	/**
	 * Function that adds class on body for Woocommerce
	 *
	 * @param $classes
	 * @return array
	 */
	function walker_edge_woocommerce_body_class( $classes ) {
		if(walker_edge_is_woocommerce_page()) {
			$classes[] = 'edgtf-woocommerce-page';

			if(function_exists('is_shop') && is_shop()) {
				$classes[] = 'edgtf-woo-main-page';
			}

			if (is_singular('product')) {
				$classes[] = 'edgtf-woo-single-page';
			}
		}
		return $classes;
	}

	add_filter('body_class', 'walker_edge_woocommerce_body_class');
}

if(!function_exists('walker_edge_woocommerce_enabled_zoom_image_class')) {
	/**
	 * Function that featured image zoom class to header tag
	 *
	 * @param array array of classes from main filter
	 *
	 * @return array array of classes with added woocommerce class
	 */
	function walker_edge_woocommerce_enabled_zoom_image_class($classes) {

		if(walker_edge_is_woocommerce_installed()) {

			$enabled_zoom_image = walker_edge_get_meta_field_intersect('woo_enable_single_zoom_main_image');
			
			if ($enabled_zoom_image === 'yes') {
				$classes[] = 'edgtf-zoom-image-enabled';
			}
		}

		return $classes;
	}

	add_filter('body_class', 'walker_edge_woocommerce_enabled_zoom_image_class');
}

if(!function_exists('walker_edge_woocommerce_columns_class')) {
	/**
	 * Function that adds number of columns class to header tag
	 *
	 * @param array array of classes from main filter
	 *
	 * @return array array of classes with added woocommerce class
	 */
	function walker_edge_woocommerce_columns_class($classes) {

		if(walker_edge_is_woocommerce_installed()) {

			$products_list_number = walker_edge_options()->getOptionValue('edgtf_woo_product_list_columns');
			$classes[] = $products_list_number;

		}

		return $classes;
	}

	add_filter('body_class', 'walker_edge_woocommerce_columns_class');
}

if(!function_exists('walker_edge_woocommerce_columns_space_class')) {
	/**
	 * Function that adds space between columns class to header tag
	 *
	 * @param array array of classes from main filter
	 *
	 * @return array array of classes with added woocommerce class
	 */
	function walker_edge_woocommerce_columns_space_class($classes) {

		if(walker_edge_is_woocommerce_installed()) {

			$columns_space = walker_edge_options()->getOptionValue('edgtf_woo_product_list_columns_space');
			$classes[] = $columns_space;

		}

		return $classes;
	}

	add_filter('body_class', 'walker_edge_woocommerce_columns_space_class');
}

if(!function_exists('walker_edge_woocommerce_pl_info_position_class')) {
	/**
	 * Function that adds product list info position class to header tag
	 *
	 * @param array array of classes from main filter
	 *
	 * @return array array of classes with added woocommerce class
	 */
	function walker_edge_woocommerce_pl_info_position_class($classes) {

		if(walker_edge_is_woocommerce_installed()) {

			$info_position = walker_edge_options()->getOptionValue('edgtf_woo_product_list_info_position');
			$info_position_class = '';
			if($info_position === 'info_below_image') {
				$info_position_class = 'edgtf-woo-pl-info-below-image';
			} else if ($info_position === 'info_on_image_hover') {
				$info_position_class = 'edgtf-woo-pl-info-on-image-hover';
			}

			$classes[] = $info_position_class;

		}

		return $classes;
	}

	add_filter('body_class', 'walker_edge_woocommerce_pl_info_position_class');
}

if(!function_exists('walker_edge_is_woocommerce_page')) {
	/**
	 * Function that checks if current page is woocommerce shop, product or product taxonomy
	 * @return bool
	 *
	 * @see is_woocommerce()
	 */
	function walker_edge_is_woocommerce_page() {
		if (function_exists('is_woocommerce') && is_woocommerce()) {
			return is_woocommerce();
		} elseif (function_exists('is_cart') && is_cart()) {
			return is_cart();
		} elseif (function_exists('is_checkout') && is_checkout()) {
			return is_checkout();
		} elseif (function_exists('is_account_page') && is_account_page()) {
			return is_account_page();
		}
	}
}

if(!function_exists('walker_edge_is_woocommerce_shop')) {
	/**
	 * Function that checks if current page is shop or product page
	 * @return bool
	 *
	 * @see is_shop()
	 */
	function walker_edge_is_woocommerce_shop() {
		return function_exists('is_shop') && (is_shop() || is_product());
	}
}

if(!function_exists('walker_edge_get_woo_shop_page_id')) {
	/**
	 * Function that returns shop page id that is set in WooCommerce settings page
	 * @return int id of shop page
	 */
	function walker_edge_get_woo_shop_page_id() {
		if(walker_edge_is_woocommerce_installed()) {
			return get_option('woocommerce_shop_page_id');
		}
	}
}

if(!function_exists('walker_edge_is_product_category')) {
	function walker_edge_is_product_category() {
		return function_exists('is_product_category') && is_product_category();
	}
}

if(!function_exists('walker_edge_is_product_tag')) {
	function walker_edge_is_product_tag() {
		return function_exists('is_product_tag') && is_product_tag();
	}
}

if(!function_exists('walker_edge_is_yith_wishlist_install')) {
	function walker_edge_is_yith_wishlist_install() {
		return function_exists('yith_wishlist_install');
	}
}

if(!function_exists('walker_edge_is_yith_wcqv_install')) {
	function walker_edge_is_yith_wcqv_install() {
		return function_exists('yith_wcqv_install');
	}
}

if(!function_exists('walker_edge_load_woo_assets')) {
	/**
	 * Function that checks whether WooCommerce assets needs to be loaded.
	 *
	 * @see walker_edge_is_woocommerce_page()
	 * @see walker_edge_has_woocommerce_shortcode()
	 * @see walker_edge_has_woocommerce_widgets()
	 * @return bool
	 */

	function walker_edge_load_woo_assets() {
		return walker_edge_is_woocommerce_installed() && (walker_edge_is_woocommerce_page() || walker_edge_has_woocommerce_shortcode() || walker_edge_has_woocommerce_widgets());
	}
}

if(!function_exists('walker_edge_return_woocommerce_global_variable')) {
	function walker_edge_return_woocommerce_global_variable() {
		if(walker_edge_is_woocommerce_installed()) {
			global $product;

			return $product;
		}
	}
}

if(!function_exists('walker_edge_has_woocommerce_shortcode')) {
	/**
	 * Function that checks if current page has at least one of WooCommerce shortcodes added
	 * @return bool
	 */
	function walker_edge_has_woocommerce_shortcode() {
		$woocommerce_shortcodes = array(
			'edgtf_product_list',
			'edgtf_product_list_carousel',
			'edgtf_product_list_simple',
			'add_to_cart',
			'add_to_cart_url',
			'product_page',
			'product',
			'products',
			'product_categories',
			'product_category',
			'recent_products',
			'featured_products',
			'sale_products',
			'best_selling_products',
			'top_rated_products',
			'product_attribute',
			'related_products',
			'woocommerce_messages',
			'woocommerce_cart',
			'woocommerce_checkout',
			'woocommerce_order_tracking',
			'woocommerce_my_account',
			'woocommerce_edit_address',
			'woocommerce_change_password',
			'woocommerce_view_order',
			'woocommerce_pay',
			'woocommerce_thankyou',
			'yith_wcwl_add_to_wishlist',
			'yith_wcwl_wishlist'
		);

		foreach($woocommerce_shortcodes as $woocommerce_shortcode) {
			$has_shortcode = walker_edge_has_shortcode($woocommerce_shortcode);

			if($has_shortcode) {
				return true;
			}
		}

		return false;
	}
}

if(!function_exists('walker_edge_has_woocommerce_widgets')) {
	/**
	 * Function that checks if current page has at least one of WooCommerce shortcodes added
	 * @return bool
	 */
	function walker_edge_has_woocommerce_widgets() {
		$widgets_array = array(
			'edgtf_woocommerce_dropdown_cart',
			'woocommerce_widget_cart',
			'woocommerce_layered_nav',
			'woocommerce_layered_nav_filters',
			'woocommerce_price_filter',
			'woocommerce_product_categories',
			'woocommerce_product_search',
			'woocommerce_product_tag_cloud',
			'woocommerce_products',
			'woocommerce_recent_reviews',
			'woocommerce_recently_viewed_products',
			'woocommerce_top_rated_products'
		);

		foreach($widgets_array as $widget) {
			$active_widget = is_active_widget(false, false, $widget);

			if($active_widget) {
				return true;
			}
		}

		return false;
	}
}

if(!function_exists('walker_edge_add_woocommerce_shortcode_class')) {
	/**
	 * Function that checks if current page has at least one of WooCommerce shortcodes added
	 * @return string
	 */
	function walker_edge_add_woocommerce_shortcode_class($classes){
		$woocommerce_shortcodes = array(
			'woocommerce_order_tracking'
		);

		$body_class = '';

		foreach($woocommerce_shortcodes as $woocommerce_shortcode) {
			$has_shortcode = walker_edge_has_shortcode($woocommerce_shortcode);

			if($has_shortcode) {
				$classes[] = 'edgtf-woocommerce-page woocommerce-account edgtf-'.str_replace('_', '-', $woocommerce_shortcode);
			}
		}

		return $classes;
	}

	add_filter('body_class', 'walker_edge_add_woocommerce_shortcode_class');
}

if(!function_exists('walker_edge_woocommerce_share')) {
    /**
     * Function that social share for product page
     * Return array array of WooCommerce pages
     */
    function walker_edge_woocommerce_share() {
        if (walker_edge_is_woocommerce_installed()) {

            if (walker_edge_options()->getOptionValue('enable_social_share') == 'yes' && walker_edge_options()->getOptionValue('enable_social_share_on_product') == 'yes') :
                print '<div class="edgtf-woo-social-share-holder">';
                print '<span>'.esc_html__('Share:', 'walker').'</span>';
                echo walker_edge_get_social_share_html();
                print '</div>';
            endif;
        }
    }
}

if(!function_exists('walker_edge_woocommerce_product_single_class')) {
	function walker_edge_woocommerce_product_single_class($classes) {

		if(in_array('woocommerce', $classes)) {

			$product_single_layout = walker_edge_get_meta_field_intersect('single_product_layout');

			if($product_single_layout !== '') {
				$classes[] = 'edgtf-woo-single-page-layout-'.walker_edge_get_meta_field_intersect('single_product_layout');
			}

			if($product_single_layout === 'sticky-info' && walker_edge_options()->getOptionValue('woo_enable_single_sticky_content') === 'yes') {
				$classes[] = 'edgtf-woo-sticky-holder-enabled';
			}

			if($product_single_layout === 'standard' && walker_edge_get_meta_field_intersect('woo_enable_single_thumb_featured_switch') === 'yes') {
				$classes[] = 'edgtf-woo-single-switch-image';
			}
		}

		return $classes;
	}

	add_filter('body_class', 'walker_edge_woocommerce_product_single_class');
}

if(!function_exists('walker_edge_woocommerce_wishlist_shortcode')) {
	function walker_edge_woocommerce_wishlist_shortcode() {

		if(walker_edge_is_yith_wishlist_install()) {
			echo do_shortcode('[yith_wcwl_add_to_wishlist]');
		}
	}
}

if(!function_exists('walker_edge_woocommerce_quick_view_button')) {
	function walker_edge_woocommerce_quick_view_button() {

		if(walker_edge_is_yith_wcqv_install()) {
			global $product;
			$label = esc_html( get_option( 'yith-wcqv-button-label' ) );

			print '<div class="edgtf-yith-wcqv-holder"><a href="#" class="yith-wcqv-button" data-product_id="'.$product->id.'"><span class="edgtf-yith-wcqv-icon ion-ios-eye-outline"></span><span class="edgtf-yith-wcqv-label">'.$label.'</span></a></div>';
		}
	}
}
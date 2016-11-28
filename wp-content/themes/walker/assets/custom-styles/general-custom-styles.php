<?php
if(!function_exists('walker_edge_design_styles')) {
    /**
     * Generates general custom styles
     */
    function walker_edge_design_styles() {

        $preload_background_styles = array();

        if(walker_edge_options()->getOptionValue('preload_pattern_image') !== ""){
            $preload_background_styles['background-image'] = 'url('.walker_edge_options()->getOptionValue('preload_pattern_image').') !important';
        }else{
            $preload_background_styles['background-image'] = 'url('.esc_url(EDGE_ASSETS_ROOT."/img/preload_pattern.png").') !important';
        }

        echo walker_edge_dynamic_css('.edgtf-preload-background', $preload_background_styles);

		if (walker_edge_options()->getOptionValue('google_fonts')){
			$font_family = walker_edge_options()->getOptionValue('google_fonts');
			if(walker_edge_is_font_option_valid($font_family)) {
				echo walker_edge_dynamic_css('body', array('font-family' => walker_edge_get_font_option_val($font_family)));
			}
		}

        if(walker_edge_options()->getOptionValue('first_color') !== "") {
            $color_selector = array(
                'h1 a:hover',
                'h2 a:hover',
                'h3 a:hover',
                'h4 a:hover',
                'h5 a:hover',
                'h6 a:hover',
                'a',
                'p a',
                '.edgtf-comment-holder .edgtf-comment-text .replay:hover',
                '.edgtf-comment-holder .edgtf-comment-text .comment-reply-link:hover',
                '.edgtf-comment-holder .edgtf-comment-text .comment-edit-link:hover',
                '.edgtf-owl-slider .owl-nav .owl-prev:hover .edgtf-prev-icon',
                '.edgtf-owl-slider .owl-nav .owl-prev:hover .edgtf-next-icon',
                '.edgtf-owl-slider .owl-nav .owl-next:hover .edgtf-prev-icon',
                '.edgtf-owl-slider .owl-nav .owl-next:hover .edgtf-next-icon',
                '.edgtf-dark-header .edgtf-top-bar .widget a:hover',
                '.edgtf-header-vertical .edgtf-vertical-menu ul li ul li.current_page_item > a',
                '.edgtf-header-vertical .edgtf-vertical-menu ul li ul li.current-menu-item > a',
                '.edgtf-header-vertical .edgtf-vertical-menu ul li ul li.current-menu-ancestor > a',
                '.edgtf-mobile-header .edgtf-mobile-nav .edgtf-grid > ul > li > a:hover',
                '.edgtf-mobile-header .edgtf-mobile-nav .edgtf-grid > ul > li > h5:hover',
                '.edgtf-mobile-header .edgtf-mobile-nav .edgtf-grid > ul > li.edgtf-active-item > a',
                'nav.edgtf-fullscreen-menu ul li a:hover',
                'nav.edgtf-fullscreen-menu ul li ul li.current-menu-ancestor > a',
                'nav.edgtf-fullscreen-menu ul li ul li.current-menu-item > a',
                'nav.edgtf-fullscreen-menu > ul > li > a:hover',
                'nav.edgtf-fullscreen-menu > ul > li.edgtf-active-item > a',
                '.edgtf-search-page-holder article.sticky .edgtf-post-title-area h3 a',
                '.edgtf-popup-holder .edgtf-popup-inner .edgtf-popup-content .edgtf-popup-close:hover',
                '.edgtf-portfolio-single-navigation .edgtf-portfolio-single-prev a:hover',
                '.edgtf-portfolio-single-navigation .edgtf-portfolio-single-next a:hover',
                '.edgtf-author-description .edgtf-author-description-text-holder .edgtf-author-name a:hover',
                '.edgtf-related-posts-holder .edgtf-related-post .edgtf-post-info a:hover',
                '.edgtf-blog-single-navigation .edgtf-blog-single-prev a:hover',
                '.edgtf-blog-single-navigation .edgtf-blog-single-next a:hover',
                '.edgtf-single-links-pages .edgtf-single-links-pages-inner > a:hover',
                '.edgtf-single-links-pages .edgtf-single-links-pages-inner > span:hover',
                '.edgtf-blog-list-holder.edgtf-simple .edgtf-simple-text .edgtf-post-info-date a:hover',
                '.edgtf-counter-holder .edgtf-counter',
                '.edgtf-amp',
                '.edgtf-image-gallery .owl-controls .owl-prev:hover .edgtf-prev-icon',
                '.edgtf-image-gallery .owl-controls .owl-prev:hover .edgtf-next-icon',
                '.edgtf-image-gallery .owl-controls .owl-next:hover .edgtf-prev-icon',
                '.edgtf-image-gallery .owl-controls .owl-next:hover .edgtf-next-icon',
                'footer .edgtf-image-gallery .owl-controls .edgtf-prev-icon:hover',
                'footer .edgtf-image-gallery .owl-controls .edgtf-next-icon:hover',
                '.edgtf-side-menu .edgtf-image-gallery .owl-controls .edgtf-prev-icon:hover',
                '.edgtf-side-menu .edgtf-image-gallery .owl-controls .edgtf-next-icon:hover',
                'footer.edgtf-footer-skin-light .edgtf-image-gallery .owl-controls .edgtf-prev-icon:hover',
                'footer.edgtf-footer-skin-light .edgtf-image-gallery .owl-controls .edgtf-next-icon:hover',
                'aside.edgtf-sidebar .edgtf-image-gallery .owl-controls .edgtf-prev-icon:hover',
                'aside.edgtf-sidebar .edgtf-image-gallery .owl-controls .edgtf-next-icon:hover',
                '.edgtf-team-carousel-holder .owl-controls .owl-prev:hover .edgtf-prev-icon',
                '.edgtf-team-carousel-holder .owl-controls .owl-prev:hover .edgtf-next-icon',
                '.edgtf-team-carousel-holder .owl-controls .owl-next:hover .edgtf-prev-icon',
                '.edgtf-team-carousel-holder .owl-controls .owl-next:hover .edgtf-next-icon',
                '.edgtf-testimonials-holder .edgtf-testimonials .owl-controls .owl-prev:hover .edgtf-prev-icon',
                '.edgtf-testimonials-holder .edgtf-testimonials .owl-controls .owl-prev:hover .edgtf-next-icon',
                '.edgtf-testimonials-holder .edgtf-testimonials .owl-controls .owl-next:hover .edgtf-prev-icon',
                '.edgtf-testimonials-holder .edgtf-testimonials .owl-controls .owl-next:hover .edgtf-next-icon',
                '.widget .edgtf-btn.edgtf-btn-simple:hover',
                '.widget #wp-calendar caption',
                '.widget #wp-calendar td#today a',
                '.widget.widget_rss ul li .rss-date',
                '.widget.widget_search button:hover',
                '.widget.widget_recent_entries .post-date',
                '.widget.edgtf-blog-list-widget .edgtf-bli-title a:hover',
                'footer.edgtf-footer-skin-light .widget .edgtf-btn.edgtf-btn-simplehover',
                'footer.edgtf-footer-skin-light .widget.widget_text a:hover',
                'footer.edgtf-footer-skin-light .widget.widget_calendar a:hover',
                'footer.edgtf-footer-skin-light .widget.widget_rss a:hover',
                'footer.edgtf-footer-skin-light .widget.widget_search a:hover',
                'footer.edgtf-footer-skin-light .widget.widget_pages a:hover',
                'footer.edgtf-footer-skin-light .widget.widget_archive a:hover',
                'footer.edgtf-footer-skin-light .widget.widget_categories a:hover',
                'footer.edgtf-footer-skin-light .widget.widget_meta a:hover',
                'footer.edgtf-footer-skin-light .widget.widget_nav_menu a:hover',
                'footer.edgtf-footer-skin-light .widget.widget_recent_entries a:hover',
                'footer.edgtf-footer-skin-light .widget.widget_recent_comments a:hover',
                'footer.edgtf-footer-skin-light .widget.widget_tag_cloud a:hover',
                'footer.edgtf-footer-skin-light .widget.widget_edgtf_instagram_widget a:hover',
                'footer.edgtf-footer-skin-light .widget.widget_edgtf_twitter_widget a:hover',
                'footer.edgtf-footer-skin-light .widget.edgtf-blog-list-widget a:hover',
                'footer.edgtf-footer-skin-light .widget.widget_search button:hover',
                'footer.edgtf-footer-skin-light .widget.edgtf-blog-list-widget .edgtf-blog-list-holder .edgtf-bli-title a:hover',
                '.edgtf-twitter-widget li .edgtf-tweet-text a:hover',
                '.edgtf-footer-top-holder .edgtf-twitter-widget li .edgtf-tweet-text a:hover'
            );

            $woo_color_selector = array();
            if(walker_edge_is_woocommerce_installed()) {
                $woo_color_selector = array(
                    '.woocommerce-page .edgtf-content .edgtf-quantity-buttons .edgtf-quantity-minus:hover',
                    '.woocommerce-page .edgtf-content .edgtf-quantity-buttons .edgtf-quantity-plus:hover',
                    'div.woocommerce .edgtf-quantity-buttons .edgtf-quantity-minus:hover',
                    'div.woocommerce .edgtf-quantity-buttons .edgtf-quantity-plus:hover',
                    '.edgtf-woocommerce-page .woocommerce-ordering .select2-container .select2-choice:hover',
                    '.edgtf-single-product-summary .product_meta > span a:hover',
                    '.edgtf-woocommerce-page table.cart tr.cart_item td.product-remove a:hover',
                    '.edgtf-woocommerce-page .cart-collaterals .woocommerce-shipping-calculator .shipping-calculator-button:hover',
                    '.edgtf-woocommerce-page.woocommerce-checkout form.login .lost_password a:hover',
                    '.edgtf-woocommerce-page.woocommerce-account .woocommerce form.login .lost_password a:hover',
                    '.edgtf-woocommerce-page.woocommerce-account .woocommerce .myaccount_user a:hover',
                    '.edgtf-woocommerce-page.woocommerce-account .woocommerce table.shop_table td.order-number a:hover',
                    '.widget.woocommerce.widget_shopping_cart .widget_shopping_cart_content ul li a:not(.remove):hover',
                    '.widget.woocommerce.widget_shopping_cart .widget_shopping_cart_content ul li .remove:hover',
                    '.widget.woocommerce.widget_layered_nav_filters a:hover',
                    '.widget.woocommerce.widget_products ul li .product-title:hover',
                    '.widget.woocommerce.widget_recently_viewed_products ul li .product-title:hover',
                    '.widget.woocommerce.widget_top_rated_products ul li .product-title:hover',
                    '.widget.woocommerce.widget_recent_reviews a:hover',
                    '.edgtf-shopping-cart-holder .edgtf-header-cart:hover',
                    '.edgtf-vertical-menu-area .edgtf-shopping-cart-holder .edgtf-header-cart:hover',
                    '.edgtf-dark-header .edgtf-page-header > div:not(.edgtf-sticky-header):not(.fixed) .edgtf-shopping-cart-holder .edgtf-header-cart:hover',
                    '.edgtf-shopping-cart-dropdown .edgtf-item-info-holder .remove:hover',
                    '#yith-quick-view-modal #yith-quick-view-content .summary .product_meta > span a:hover',
                    '#yith-quick-view-modal #yith-quick-view-content .summary .edgtf-quantity-buttons .edgtf-quantity-minus:hover',
                    '#yith-quick-view-modal #yith-quick-view-content .summary .edgtf-quantity-buttons .edgtf-quantity-plus:hover',
                    '#yith-quick-view-modal #yith-quick-view-close:hover',
                    '.yith-wcwl-add-button a:hover',
                    '.yith-wcwl-wishlistaddedbrowse a:hover',
                    '.yith-wcwl-wishlistexistsbrowse a:hover',
                    '.woocommerce table.wishlist_table tbody tr td.product-remove a:hover'
                );
            }

            $color_selector = array_merge($color_selector, $woo_color_selector); 

            $color_important_selector = array(
                '.edgtf-dark-header.edgtf-header-vertical .edgtf-vertical-menu ul li a:hover',
                '.edgtf-dark-header.edgtf-header-vertical .edgtf-vertical-menu ul li ul li.current_page_item > a',
                '.edgtf-dark-header.edgtf-header-vertical .edgtf-vertical-menu ul li ul li.current-menu-item > a',
                '.edgtf-dark-header.edgtf-header-vertical .edgtf-vertical-menu ul li ul li.current-menu-ancestor > a',
                '.edgtf-dark-header.edgtf-header-vertical .edgtf-vertical-menu > ul > li.edgtf-active-item > a',
                '.edgtf-dark-header.edgtf-header-vertical .edgtf-vertical-menu > ul > li.current-menu-ancestor > a',
                '.edgtf-dark-header .edgtf-page-header > div:not(.fixed):not(.edgtf-sticky-header) .edgtf-menu-area .widget a:hover',
                '.edgtf-dark-header .edgtf-page-header > div:not(.fixed):not(.edgtf-sticky-header).edgtf-menu-area .widget a:hover',
                '.edgtf-dark-header .edgtf-page-header > div:not(.edgtf-sticky-header):not(.fixed) .edgtf-side-menu-button-opener.opened .edgtf-side-menu-title',
                '.edgtf-dark-header .edgtf-page-header > div:not(.edgtf-sticky-header):not(.fixed) .edgtf-side-menu-button-opener:hover .edgtf-side-menu-title',
                '.edgtf-dark-header .edgtf-top-bar .edgtf-side-menu-button-opener.opened .edgtf-side-menu-title',
                '.edgtf-dark-header .edgtf-top-bar .edgtf-side-menu-button-opener:hover .edgtf-side-menu-title',
                '.edgtf-side-menu-button-opener.opened .edgtf-side-menu-title',
                '.edgtf-side-menu-button-opener:hover .edgtf-side-menu-title',
                '.edgtf-btn.edgtf-btn-simple:not(.edgtf-btn-custom-hover-color):hover',
                '.edgtf-dark-header .edgtf-page-header > div:not(.edgtf-sticky-header):not(.fixed) .edgtf-social-icon-widget-holder:hover'
            );

            $background_color_selector = array(
                '.edgtf-st-loader .pulse',
                '.edgtf-st-loader .double_pulse .double-bounce1',
                '.edgtf-st-loader .double_pulse .double-bounce2',
                '.edgtf-st-loader .cube',
                '.edgtf-st-loader .rotating_cubes .cube1',
                '.edgtf-st-loader .rotating_cubes .cube2',
                '.edgtf-st-loader .stripes > div',
                '.edgtf-st-loader .wave > div',
                '.edgtf-st-loader .two_rotating_circles .dot1',
                '.edgtf-st-loader .two_rotating_circles .dot2',
                '.edgtf-st-loader .five_rotating_circles .container1 > div',
                '.edgtf-st-loader .five_rotating_circles .container2 > div',
                '.edgtf-st-loader .five_rotating_circles .container3 > div',
                '.edgtf-st-loader .atom .ball-1:before',
                '.edgtf-st-loader .atom .ball-2:before',
                '.edgtf-st-loader .atom .ball-3:before',
                '.edgtf-st-loader .atom .ball-4:before',
                '.edgtf-st-loader .clock .ball:before',
                '.edgtf-st-loader .mitosis .ball',
                '.edgtf-st-loader .lines .line1',
                '.edgtf-st-loader .lines .line2',
                '.edgtf-st-loader .lines .line3',
                '.edgtf-st-loader .lines .line4',
                '.edgtf-st-loader .fussion .ball',
                '.edgtf-st-loader .fussion .ball-1',
                '.edgtf-st-loader .fussion .ball-2',
                '.edgtf-st-loader .fussion .ball-3',
                '.edgtf-st-loader .fussion .ball-4',
                '.edgtf-st-loader .wave_circles .ball',
                '.edgtf-st-loader .pulse_circles .ball',
                '#submit_comment:hover',
                '.post-password-form input[type=\'submit\']:hover',
                '#edgtf-back-to-top > span',
                '.edgtf-search-page-holder .edgtf-search-page-form .edgtf-form-holder .edgtf-search-submit:hover',
                '.edgtf-btn.edgtf-btn-simple .edgtf-btn-text:after',
                '.edgtf-btn.edgtf-btn-simple .edgtf-btn-text:before',
                '.edgtf-icon-shortcode.circle',
                '.edgtf-icon-shortcode.square',
                '.widget #wp-calendar td#today'
            );

            $woo_background_color_selector = array();
            if(walker_edge_is_woocommerce_installed()) {
                $woo_background_color_selector = array(
                    '.woocommerce-page .edgtf-content a.button:hover',
                    '.woocommerce-page .edgtf-content a.added_to_cart:hover',
                    '.woocommerce-page .edgtf-content input[type="submit"]:hover',
                    '.woocommerce-page .edgtf-content button[type="submit"]:hover',
                    'div.woocommerce a.button:hover',
                    'div.woocommerce a.added_to_cart:hover',
                    'div.woocommerce input[type="submit"]:hover',
                    'div.woocommerce button[type="submit"]:hover',
                    '#yith-quick-view-modal #yith-quick-view-content .summary a.button:hover',
                    '#yith-quick-view-modal #yith-quick-view-content .summary input[type="submit"]:hover',
                    '#yith-quick-view-modal #yith-quick-view-content .summary button[type="submit"]:hover'
                );
            }

            $background_color_selector = array_merge($background_color_selector, $woo_background_color_selector); 

            $background_color_important_selector = array(
                '.edgtf-dark-header .edgtf-page-header > div:not(.edgtf-sticky-header):not(.fixed) .edgtf-side-menu-button-opener.opened .edgtf-side-menu-lines .edgtf-side-menu-line',
                '.edgtf-dark-header .edgtf-page-header > div:not(.edgtf-sticky-header):not(.fixed) .edgtf-side-menu-button-opener:hover .edgtf-side-menu-lines .edgtf-side-menu-line',
                '.edgtf-dark-header .edgtf-top-bar .edgtf-side-menu-button-opener.opened .edgtf-side-menu-lines .edgtf-side-menu-line',
                '.edgtf-dark-header .edgtf-top-bar .edgtf-side-menu-button-opener:hover .edgtf-side-menu-lines .edgtf-side-menu-line',
                '.edgtf-dark-header .edgtf-page-header > div:not(.edgtf-sticky-header):not(.fixed) .edgtf-fullscreen-menu-opener:not(.edgtf-fm-opened) .edgt-fullscreen-menu-lines:hover .edgtf-fullscreen-menu-line',
                '.edgtf-dark-header .edgtf-top-bar .edgtf-fullscreen-menu-opener:not(.edgtf-fm-opened) .edgt-fullscreen-menu-lines:hover .edgtf-fullscreen-menu-line',
                '.edgtf-fullscreen-menu-opener .edgt-fullscreen-menu-lines:hover .edgtf-fullscreen-menu-line',
                '.edgtf-btn.edgtf-btn-solid:not(.edgtf-btn-custom-hover-bg):hover'
            );

            $border_color_selector = array(
                '.edgtf-st-loader .pulse_circles .ball',
                '#edgtf-back-to-top > span'
            );

            $border_color_important_selector = array(
                '.edgtf-btn.edgtf-btn-solid:not(.edgtf-btn-custom-border-hover):hover'
            );

            echo walker_edge_dynamic_css($color_selector, array('color' => walker_edge_options()->getOptionValue('first_color')));
            echo walker_edge_dynamic_css($color_important_selector, array('color' => walker_edge_options()->getOptionValue('first_color').'!important'));
            echo walker_edge_dynamic_css('::selection', array('background' => walker_edge_options()->getOptionValue('first_color')));
            echo walker_edge_dynamic_css('::-moz-selection', array('background' => walker_edge_options()->getOptionValue('first_color')));
            echo walker_edge_dynamic_css($background_color_selector, array('background-color' => walker_edge_options()->getOptionValue('first_color')));
            echo walker_edge_dynamic_css($background_color_important_selector, array('background-color' => walker_edge_options()->getOptionValue('first_color').'!important'));
            echo walker_edge_dynamic_css($border_color_selector, array('border-color' => walker_edge_options()->getOptionValue('first_color')));
            echo walker_edge_dynamic_css($border_color_important_selector, array('border-color' => walker_edge_options()->getOptionValue('first_color').'!important'));
        }

		if (walker_edge_options()->getOptionValue('page_background_color')) {
			$background_color_selector = array(
				'.edgtf-wrapper-inner',
				'.edgtf-content'
			);
			echo walker_edge_dynamic_css($background_color_selector, array('background-color' => walker_edge_options()->getOptionValue('page_background_color')));
		}

		if (walker_edge_options()->getOptionValue('selection_color')) {
			echo walker_edge_dynamic_css('::selection', array('background' => walker_edge_options()->getOptionValue('selection_color')));
			echo walker_edge_dynamic_css('::-moz-selection', array('background' => walker_edge_options()->getOptionValue('selection_color')));
		}

		$boxed_background_style = array();
		if (walker_edge_options()->getOptionValue('page_background_color_in_box') !== '') {
			$boxed_background_style['background-color'] = walker_edge_options()->getOptionValue('page_background_color_in_box');
		}

		if (walker_edge_options()->getOptionValue('boxed_background_image')) {
			$boxed_background_style['background-image'] = 'url('.esc_url(walker_edge_options()->getOptionValue('boxed_background_image')).')';
			$boxed_background_style['background-position'] = 'center 0px';
			$boxed_background_style['background-repeat'] = 'no-repeat';
		}

		if (walker_edge_options()->getOptionValue('boxed_pattern_background_image')) {
			$boxed_background_style['background-image'] = 'url('.esc_url(walker_edge_options()->getOptionValue('boxed_pattern_background_image')).')';
			$boxed_background_style['background-position'] = '0px 0px';
			$boxed_background_style['background-repeat'] = 'repeat';
		}

		if (walker_edge_options()->getOptionValue('boxed_background_image_attachment')) {
			$boxed_background_style['background-attachment'] = (walker_edge_options()->getOptionValue('boxed_background_image_attachment'));
		}

		echo walker_edge_dynamic_css('.edgtf-boxed .edgtf-wrapper', $boxed_background_style);

        $paspartu_style = array();
        if (walker_edge_options()->getOptionValue('paspartu_color') !== '') {
            $paspartu_style['background-color'] = walker_edge_options()->getOptionValue('paspartu_color');
        }

        if (walker_edge_options()->getOptionValue('paspartu_width') !== '') {
            $paspartu_style['padding'] = walker_edge_options()->getOptionValue('paspartu_width').'%';
        }

        echo walker_edge_dynamic_css('.edgtf-paspartu-enabled .edgtf-wrapper', $paspartu_style);

        $archive_category_style = array();
        if (walker_edge_options()->getOptionValue('archive_background_color') !== '') {
            $archive_category_style['background-color'] = walker_edge_options()->getOptionValue('archive_background_color');
        }

        echo walker_edge_dynamic_css('body.archive:not(.woocommerce-page) .edgtf-content > .edgtf-content-inner > .edgtf-container', $archive_category_style);

        $archive_category_woo_style = array();
        if (walker_edge_options()->getOptionValue('archive_woo_background_color') !== '') {
            $archive_category_woo_style['background-color'] = walker_edge_options()->getOptionValue('archive_woo_background_color');
        }

        echo walker_edge_dynamic_css('body.archive.woocommerce-page .edgtf-content > .edgtf-content-inner > .edgtf-container', $archive_category_woo_style);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_design_styles');
}

if(!function_exists('walker_edge_content_styles')) {
    /**
     * Generates content custom styles
     */
    function walker_edge_content_styles() {

        $content_style = array();
        if (walker_edge_options()->getOptionValue('content_top_padding') !== '') {
            $padding_top = (walker_edge_options()->getOptionValue('content_top_padding'));
            $content_style['padding-top'] = walker_edge_filter_px($padding_top).'px';
        }

        $content_selector = array(
            '.edgtf-content .edgtf-content-inner > .edgtf-full-width > .edgtf-full-width-inner',
        );

        echo walker_edge_dynamic_css($content_selector, $content_style);

        $content_style_in_grid = array();
        if (walker_edge_options()->getOptionValue('content_top_padding_in_grid') !== '') {
            $padding_top_in_grid = (walker_edge_options()->getOptionValue('content_top_padding_in_grid'));
            $content_style_in_grid['padding-top'] = walker_edge_filter_px($padding_top_in_grid).'px';

        }

        $content_selector_in_grid = array(
            '.edgtf-content .edgtf-content-inner > .edgtf-container > .edgtf-container-inner',
        );

        echo walker_edge_dynamic_css($content_selector_in_grid, $content_style_in_grid);

    }

    add_action('walker_edge_style_dynamic', 'walker_edge_content_styles');
}

if (!function_exists('walker_edge_h1_styles')) {

    function walker_edge_h1_styles() {

        $h1_styles = array();

        if(walker_edge_options()->getOptionValue('h1_color') !== '') {
            $h1_styles['color'] = walker_edge_options()->getOptionValue('h1_color');
        }
        if(walker_edge_options()->getOptionValue('h1_google_fonts') !== '-1') {
            $h1_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('h1_google_fonts'));
        }
        if(walker_edge_options()->getOptionValue('h1_fontsize') !== '') {
            $h1_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h1_fontsize')).'px';
        }
        if(walker_edge_options()->getOptionValue('h1_lineheight') !== '') {
            $h1_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h1_lineheight')).'px';
        }
        if(walker_edge_options()->getOptionValue('h1_texttransform') !== '') {
            $h1_styles['text-transform'] = walker_edge_options()->getOptionValue('h1_texttransform');
        }
        if(walker_edge_options()->getOptionValue('h1_fontstyle') !== '') {
            $h1_styles['font-style'] = walker_edge_options()->getOptionValue('h1_fontstyle');
        }
        if(walker_edge_options()->getOptionValue('h1_fontweight') !== '') {
            $h1_styles['font-weight'] = walker_edge_options()->getOptionValue('h1_fontweight');
        }
        if(walker_edge_options()->getOptionValue('h1_letterspacing') !== '') {
            $h1_styles['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h1_letterspacing')).'px';
        }
        if(walker_edge_options()->getOptionValue('h1_margin_top') !== '') {
            $h1_styles['margin-top'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h1_margin_top')).'px';
        }
        if(walker_edge_options()->getOptionValue('h1_margin_bottom') !== '') {
            $h1_styles['margin-bottom'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h1_margin_bottom')).'px';
        }

        $h1_selector = array(
            'h1'
        );

        if (!empty($h1_styles)) {
            echo walker_edge_dynamic_css($h1_selector, $h1_styles);
        }
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_h1_styles');
}

if (!function_exists('walker_edge_h2_styles')) {

    function walker_edge_h2_styles() {

        $h2_styles = array();

        if(walker_edge_options()->getOptionValue('h2_color') !== '') {
            $h2_styles['color'] = walker_edge_options()->getOptionValue('h2_color');
        }
        if(walker_edge_options()->getOptionValue('h2_google_fonts') !== '-1') {
            $h2_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('h2_google_fonts'));
        }
        if(walker_edge_options()->getOptionValue('h2_fontsize') !== '') {
            $h2_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h2_fontsize')).'px';
        }
        if(walker_edge_options()->getOptionValue('h2_lineheight') !== '') {
            $h2_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h2_lineheight')).'px';
        }
        if(walker_edge_options()->getOptionValue('h2_texttransform') !== '') {
            $h2_styles['text-transform'] = walker_edge_options()->getOptionValue('h2_texttransform');
        }
        if(walker_edge_options()->getOptionValue('h2_fontstyle') !== '') {
            $h2_styles['font-style'] = walker_edge_options()->getOptionValue('h2_fontstyle');
        }
        if(walker_edge_options()->getOptionValue('h2_fontweight') !== '') {
            $h2_styles['font-weight'] = walker_edge_options()->getOptionValue('h2_fontweight');
        }
        if(walker_edge_options()->getOptionValue('h2_letterspacing') !== '') {
            $h2_styles['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h2_letterspacing')).'px';
        }
        if(walker_edge_options()->getOptionValue('h2_margin_top') !== '') {
            $h2_styles['margin-top'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h2_margin_top')).'px';
        }
        if(walker_edge_options()->getOptionValue('h2_margin_bottom') !== '') {
            $h2_styles['margin-bottom'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h2_margin_bottom')).'px';
        }

        $h2_selector = array(
            'h2'
        );

        if (!empty($h2_styles)) {
            echo walker_edge_dynamic_css($h2_selector, $h2_styles);
        }
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_h2_styles');
}

if (!function_exists('walker_edge_h3_styles')) {

    function walker_edge_h3_styles() {

        $h3_styles = array();

        if(walker_edge_options()->getOptionValue('h3_color') !== '') {
            $h3_styles['color'] = walker_edge_options()->getOptionValue('h3_color');
        }
        if(walker_edge_options()->getOptionValue('h3_google_fonts') !== '-1') {
            $h3_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('h3_google_fonts'));
        }
        if(walker_edge_options()->getOptionValue('h3_fontsize') !== '') {
            $h3_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h3_fontsize')).'px';
        }
        if(walker_edge_options()->getOptionValue('h3_lineheight') !== '') {
            $h3_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h3_lineheight')).'px';
        }
        if(walker_edge_options()->getOptionValue('h3_texttransform') !== '') {
            $h3_styles['text-transform'] = walker_edge_options()->getOptionValue('h3_texttransform');
        }
        if(walker_edge_options()->getOptionValue('h3_fontstyle') !== '') {
            $h3_styles['font-style'] = walker_edge_options()->getOptionValue('h3_fontstyle');
        }
        if(walker_edge_options()->getOptionValue('h3_fontweight') !== '') {
            $h3_styles['font-weight'] = walker_edge_options()->getOptionValue('h3_fontweight');
        }
        if(walker_edge_options()->getOptionValue('h3_letterspacing') !== '') {
            $h3_styles['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h3_letterspacing')).'px';
        }
        if(walker_edge_options()->getOptionValue('h3_margin_top') !== '') {
            $h3_styles['margin-top'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h3_margin_top')).'px';
        }
        if(walker_edge_options()->getOptionValue('h3_margin_bottom') !== '') {
            $h3_styles['margin-bottom'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h3_margin_bottom')).'px';
        }

        $h3_selector = array(
            'h3'
        );

        if (!empty($h3_styles)) {
            echo walker_edge_dynamic_css($h3_selector, $h3_styles);
        }
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_h3_styles');
}

if (!function_exists('walker_edge_h4_styles')) {

    function walker_edge_h4_styles() {

        $h4_styles = array();

        if(walker_edge_options()->getOptionValue('h4_color') !== '') {
            $h4_styles['color'] = walker_edge_options()->getOptionValue('h4_color');
        }
        if(walker_edge_options()->getOptionValue('h4_google_fonts') !== '-1') {
            $h4_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('h4_google_fonts'));
        }
        if(walker_edge_options()->getOptionValue('h4_fontsize') !== '') {
            $h4_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h4_fontsize')).'px';
        }
        if(walker_edge_options()->getOptionValue('h4_lineheight') !== '') {
            $h4_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h4_lineheight')).'px';
        }
        if(walker_edge_options()->getOptionValue('h4_texttransform') !== '') {
            $h4_styles['text-transform'] = walker_edge_options()->getOptionValue('h4_texttransform');
        }
        if(walker_edge_options()->getOptionValue('h4_fontstyle') !== '') {
            $h4_styles['font-style'] = walker_edge_options()->getOptionValue('h4_fontstyle');
        }
        if(walker_edge_options()->getOptionValue('h4_fontweight') !== '') {
            $h4_styles['font-weight'] = walker_edge_options()->getOptionValue('h4_fontweight');
        }
        if(walker_edge_options()->getOptionValue('h4_letterspacing') !== '') {
            $h4_styles['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h4_letterspacing')).'px';
        }
        if(walker_edge_options()->getOptionValue('h4_margin_top') !== '') {
            $h4_styles['margin-top'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h4_margin_top')).'px';
        }
        if(walker_edge_options()->getOptionValue('h4_margin_bottom') !== '') {
            $h4_styles['margin-bottom'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h4_margin_bottom')).'px';
        }

        $h4_selector = array(
            'h4'
        );

        if (!empty($h4_styles)) {
            echo walker_edge_dynamic_css($h4_selector, $h4_styles);
        }
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_h4_styles');
}

if (!function_exists('walker_edge_h5_styles')) {

    function walker_edge_h5_styles() {

        $h5_styles = array();

        if(walker_edge_options()->getOptionValue('h5_color') !== '') {
            $h5_styles['color'] = walker_edge_options()->getOptionValue('h5_color');
        }
        if(walker_edge_options()->getOptionValue('h5_google_fonts') !== '-1') {
            $h5_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('h5_google_fonts'));
        }
        if(walker_edge_options()->getOptionValue('h5_fontsize') !== '') {
            $h5_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h5_fontsize')).'px';
        }
        if(walker_edge_options()->getOptionValue('h5_lineheight') !== '') {
            $h5_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h5_lineheight')).'px';
        }
        if(walker_edge_options()->getOptionValue('h5_texttransform') !== '') {
            $h5_styles['text-transform'] = walker_edge_options()->getOptionValue('h5_texttransform');
        }
        if(walker_edge_options()->getOptionValue('h5_fontstyle') !== '') {
            $h5_styles['font-style'] = walker_edge_options()->getOptionValue('h5_fontstyle');
        }
        if(walker_edge_options()->getOptionValue('h5_fontweight') !== '') {
            $h5_styles['font-weight'] = walker_edge_options()->getOptionValue('h5_fontweight');
        }
        if(walker_edge_options()->getOptionValue('h5_letterspacing') !== '') {
            $h5_styles['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h5_letterspacing')).'px';
        }
        if(walker_edge_options()->getOptionValue('h5_margin_top') !== '') {
            $h5_styles['margin-top'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h5_margin_top')).'px';
        }
        if(walker_edge_options()->getOptionValue('h5_margin_bottom') !== '') {
            $h5_styles['margin-bottom'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h5_margin_bottom')).'px';
        }

        $h5_selector = array(
            'h5'
        );

        if (!empty($h5_styles)) {
            echo walker_edge_dynamic_css($h5_selector, $h5_styles);
        }
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_h5_styles');
}

if (!function_exists('walker_edge_h6_styles')) {

    function walker_edge_h6_styles() {

        $h6_styles = array();

        if(walker_edge_options()->getOptionValue('h6_color') !== '') {
            $h6_styles['color'] = walker_edge_options()->getOptionValue('h6_color');
        }
        if(walker_edge_options()->getOptionValue('h6_google_fonts') !== '-1') {
            $h6_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('h6_google_fonts'));
        }
        if(walker_edge_options()->getOptionValue('h6_fontsize') !== '') {
            $h6_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h6_fontsize')).'px';
        }
        if(walker_edge_options()->getOptionValue('h6_lineheight') !== '') {
            $h6_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h6_lineheight')).'px';
        }
        if(walker_edge_options()->getOptionValue('h6_texttransform') !== '') {
            $h6_styles['text-transform'] = walker_edge_options()->getOptionValue('h6_texttransform');
        }
        if(walker_edge_options()->getOptionValue('h6_fontstyle') !== '') {
            $h6_styles['font-style'] = walker_edge_options()->getOptionValue('h6_fontstyle');
        }
        if(walker_edge_options()->getOptionValue('h6_fontweight') !== '') {
            $h6_styles['font-weight'] = walker_edge_options()->getOptionValue('h6_fontweight');
        }
        if(walker_edge_options()->getOptionValue('h6_letterspacing') !== '') {
            $h6_styles['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h6_letterspacing')).'px';
        }
        if(walker_edge_options()->getOptionValue('h6_margin_top') !== '') {
            $h6_styles['margin-top'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h6_margin_top')).'px';
        }
        if(walker_edge_options()->getOptionValue('h6_margin_bottom') !== '') {
            $h6_styles['margin-bottom'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('h6_margin_bottom')).'px';
        }

        $h6_selector = array(
            'h6'
        );

        if (!empty($h6_styles)) {
            echo walker_edge_dynamic_css($h6_selector, $h6_styles);
        }
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_h6_styles');
}

if (!function_exists('walker_edge_text_styles')) {

    function walker_edge_text_styles() {

        $text_styles = array();

        if(walker_edge_options()->getOptionValue('text_color') !== '') {
            $text_styles['color'] = walker_edge_options()->getOptionValue('text_color');
        }
        if(walker_edge_options()->getOptionValue('text_google_fonts') !== '-1') {
            $text_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('text_google_fonts'));
        }
        if(walker_edge_options()->getOptionValue('text_fontsize') !== '') {
            $text_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('text_fontsize')).'px';
        }
        if(walker_edge_options()->getOptionValue('text_lineheight') !== '') {
            $text_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('text_lineheight')).'px';
        }
        if(walker_edge_options()->getOptionValue('text_texttransform') !== '') {
            $text_styles['text-transform'] = walker_edge_options()->getOptionValue('text_texttransform');
        }
        if(walker_edge_options()->getOptionValue('text_fontstyle') !== '') {
            $text_styles['font-style'] = walker_edge_options()->getOptionValue('text_fontstyle');
        }
        if(walker_edge_options()->getOptionValue('text_fontweight') !== '') {
            $text_styles['font-weight'] = walker_edge_options()->getOptionValue('text_fontweight');
        }
        if(walker_edge_options()->getOptionValue('text_letterspacing') !== '') {
            $text_styles['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('text_letterspacing')).'px';
        }

        $text_selector = array(
            'p'
        );

        if (!empty($text_styles)) {
            echo walker_edge_dynamic_css($text_selector, $text_styles);
        }
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_text_styles');
}

if (!function_exists('walker_edge_link_styles')) {

    function walker_edge_link_styles() {

        $link_styles = array();

        if(walker_edge_options()->getOptionValue('link_color') !== '') {
            $link_styles['color'] = walker_edge_options()->getOptionValue('link_color');
        }
        if(walker_edge_options()->getOptionValue('link_fontstyle') !== '') {
            $link_styles['font-style'] = walker_edge_options()->getOptionValue('link_fontstyle');
        }
        if(walker_edge_options()->getOptionValue('link_fontweight') !== '') {
            $link_styles['font-weight'] = walker_edge_options()->getOptionValue('link_fontweight');
        }
        if(walker_edge_options()->getOptionValue('link_fontdecoration') !== '') {
            $link_styles['text-decoration'] = walker_edge_options()->getOptionValue('link_fontdecoration');
        }

        $link_selector = array(
            'a',
            'p a'
        );

        if (!empty($link_styles)) {
            echo walker_edge_dynamic_css($link_selector, $link_styles);
        }
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_link_styles');
}

if (!function_exists('walker_edge_link_hover_styles')) {

    function walker_edge_link_hover_styles() {

        $link_hover_styles = array();

        if(walker_edge_options()->getOptionValue('link_hovercolor') !== '') {
            $link_hover_styles['color'] = walker_edge_options()->getOptionValue('link_hovercolor');
        }
        if(walker_edge_options()->getOptionValue('link_hover_fontdecoration') !== '') {
            $link_hover_styles['text-decoration'] = walker_edge_options()->getOptionValue('link_hover_fontdecoration');
        }

        $link_hover_selector = array(
            'a:hover',
            'p a:hover'
        );

        if (!empty($link_hover_styles)) {
            echo walker_edge_dynamic_css($link_hover_selector, $link_hover_styles);
        }

        $link_heading_hover_styles = array();

        if(walker_edge_options()->getOptionValue('link_hovercolor') !== '') {
            $link_heading_hover_styles['color'] = walker_edge_options()->getOptionValue('link_hovercolor');
        }

        $link_heading_hover_selector = array(
            'h1 a:hover',
            'h2 a:hover',
            'h3 a:hover',
            'h4 a:hover',
            'h5 a:hover',
            'h6 a:hover'
        );

        if (!empty($link_heading_hover_styles)) {
            echo walker_edge_dynamic_css($link_heading_hover_selector, $link_heading_hover_styles);
        }
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_link_hover_styles');
}

if (!function_exists('walker_edge_smooth_page_transition_styles')) {

    function walker_edge_smooth_page_transition_styles() {
        
        $loader_style = array();

        if(walker_edge_options()->getOptionValue('smooth_pt_bgnd_color') !== '') {
            $loader_style['background-color'] = walker_edge_options()->getOptionValue('smooth_pt_bgnd_color');
        }

        $loader_selector = array('.edgtf-smooth-transition-loader');

        if (!empty($loader_style)) {
            echo walker_edge_dynamic_css($loader_selector, $loader_style);
        }

        $spinner_style = array();

        if(walker_edge_options()->getOptionValue('smooth_pt_spinner_color') !== '') {
            $spinner_style['background-color'] = walker_edge_options()->getOptionValue('smooth_pt_spinner_color');
        }

        $spinner_selectors = array(
            '.edgtf-st-loader .edgtf-rotate-circles > div',
            '.edgtf-st-loader .pulse',
            '.edgtf-st-loader .double_pulse .double-bounce1',
            '.edgtf-st-loader .double_pulse .double-bounce2',
            '.edgtf-st-loader .cube',
            '.edgtf-st-loader .rotating_cubes .cube1',
            '.edgtf-st-loader .rotating_cubes .cube2',
            '.edgtf-st-loader .stripes > div',
            '.edgtf-st-loader .wave > div',
            '.edgtf-st-loader .two_rotating_circles .dot1',
            '.edgtf-st-loader .two_rotating_circles .dot2',
            '.edgtf-st-loader .five_rotating_circles .container1 > div',
            '.edgtf-st-loader .five_rotating_circles .container2 > div',
            '.edgtf-st-loader .five_rotating_circles .container3 > div',
            '.edgtf-st-loader .atom .ball-1:before',
            '.edgtf-st-loader .atom .ball-2:before',
            '.edgtf-st-loader .atom .ball-3:before',
            '.edgtf-st-loader .atom .ball-4:before',
            '.edgtf-st-loader .clock .ball:before',
            '.edgtf-st-loader .mitosis .ball',
            '.edgtf-st-loader .lines .line1',
            '.edgtf-st-loader .lines .line2',
            '.edgtf-st-loader .lines .line3',
            '.edgtf-st-loader .lines .line4',
            '.edgtf-st-loader .fussion .ball',
            '.edgtf-st-loader .fussion .ball-1',
            '.edgtf-st-loader .fussion .ball-2',
            '.edgtf-st-loader .fussion .ball-3',
            '.edgtf-st-loader .fussion .ball-4',
            '.edgtf-st-loader .wave_circles .ball',
            '.edgtf-st-loader .pulse_circles .ball'
        );

        if (!empty($spinner_style)) {
            echo walker_edge_dynamic_css($spinner_selectors, $spinner_style);
        }
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_smooth_page_transition_styles');
}
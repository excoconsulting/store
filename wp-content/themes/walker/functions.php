<?php
include_once get_template_directory().'/theme-includes.php';

if(!function_exists('walker_edge_styles')) {
    /**
     * Function that includes theme's core styles
     */
    function walker_edge_styles() {

        //include theme's core styles
        wp_enqueue_style('walker_edge_default_style', EDGE_ROOT.'/style.css');
        wp_enqueue_style('walker_edge_modules', EDGE_ASSETS_ROOT.'/css/modules.min.css');

        //is woocommerce installed?
        if(walker_edge_is_woocommerce_installed()) {
            if(walker_edge_load_woo_assets()) {

                //include theme's woocommerce styles
                wp_enqueue_style('walker_edge_woo', EDGE_ASSETS_ROOT.'/css/woocommerce.min.css');
            }
        }

        walker_edge_icon_collections()->enqueueStyles();

        if(walker_edge_load_blog_assets() || is_singular('portfolio-item')) {
            wp_enqueue_style('wp-mediaelement');
        }

        //define files afer which style dynamic needs to be included. It should be included last so it can override other files
        $style_dynamic_deps_array = array();
        if(walker_edge_load_woo_assets()) {
            $style_dynamic_deps_array = array('walker_edge_woo', 'walker_edge_woo_responsive');
        }

        if(file_exists(EDGE_ROOT_DIR.'/assets/css/style_dynamic.css') && walker_edge_is_css_folder_writable() && !is_multisite()) {
            wp_enqueue_style('walker_edge_style_dynamic', EDGE_ASSETS_ROOT.'/css/style_dynamic.css', $style_dynamic_deps_array, filemtime(EDGE_ROOT_DIR.'/assets/css/style_dynamic.css')); //it must be included after woocommerce styles so it can override it
        }

        //is responsive option turned on?
        if(walker_edge_is_responsive_on()) {
            wp_enqueue_style('walker_edge_modules_responsive', EDGE_ASSETS_ROOT.'/css/modules-responsive.min.css');

            //is woocommerce installed?
            if(walker_edge_is_woocommerce_installed()) {
                if(walker_edge_load_woo_assets()) {

                    //include theme's woocommerce responsive styles
                    wp_enqueue_style('walker_edge_woo_responsive', EDGE_ASSETS_ROOT.'/css/woocommerce-responsive.min.css');
                }
            }

            //include proper styles
            if(file_exists(EDGE_ROOT_DIR.'/assets/css/style_dynamic_responsive.css') && walker_edge_is_css_folder_writable() && !is_multisite()) {
                wp_enqueue_style('walker_edge_style_dynamic_responsive', EDGE_ASSETS_ROOT.'/css/style_dynamic_responsive.css', array(), filemtime(EDGE_ROOT_DIR.'/assets/css/style_dynamic_responsive.css'));
            }
        }

        //include Visual Composer styles
        if(class_exists('WPBakeryVisualComposerAbstract')) {
            wp_enqueue_style('js_composer_front');
        }
    }

    add_action('wp_enqueue_scripts', 'walker_edge_styles');
}

if(!function_exists('walker_edge_google_fonts_styles')) {
	/**
	 * Function that includes google fonts defined anywhere in the theme
	 */
    function walker_edge_google_fonts_styles() {
        $font_simple_field_array = walker_edge_options()->getOptionsByType('fontsimple');
        if(!(is_array($font_simple_field_array) && count($font_simple_field_array) > 0)) {
            $font_simple_field_array = array();
        }

        $font_field_array = walker_edge_options()->getOptionsByType('font');
        if(!(is_array($font_field_array) && count($font_field_array) > 0)) {
            $font_field_array = array();
        }

        $available_font_options = array_merge($font_simple_field_array, $font_field_array);

        $google_font_weight_array = walker_edge_options()->getOptionValue('google_font_weight');
        if(!empty($google_font_weight_array)) {
            $google_font_weight_array = array_slice(walker_edge_options()->getOptionValue('google_font_weight'), 1);
        }

        $font_weight_str = '400,500,700,800';
        if(!empty($google_font_weight_array) && $google_font_weight_array !== '') {
            $font_weight_str = implode(',',$google_font_weight_array);
        }

        $google_font_subset_array = walker_edge_options()->getOptionValue('google_font_subset');
        if(!empty($google_font_subset_array)) {
            $google_font_subset_array = array_slice(walker_edge_options()->getOptionValue('google_font_subset'), 1);
        }

        $font_subset_str = 'latin-ext';
        if(!empty($google_font_subset_array) && $google_font_subset_array !== '') {
            $font_subset_str = implode(',',$google_font_subset_array);
        }

        //define available font options array
        $fonts_array = array();
        foreach($available_font_options as $font_option) {
            //is font set and not set to default and not empty?
            $font_option_value = walker_edge_options()->getOptionValue($font_option);
            if(walker_edge_is_font_option_valid($font_option_value) && !walker_edge_is_native_font($font_option_value)) {
                $font_option_string = $font_option_value.':'.$font_weight_str;
                if(!in_array($font_option_string, $fonts_array)) {
                    $fonts_array[] = $font_option_string;
                }
            }
        }

        wp_reset_postdata();

        $fonts_array         = array_diff($fonts_array, array('-1:'.$font_weight_str));
        $google_fonts_string = implode('|', $fonts_array);

        //default fonts
        $default_font_string = 'Raleway:'.$font_weight_str;
        $protocol = is_ssl() ? 'https:' : 'http:';

        //is google font option checked anywhere in theme?
        if (count($fonts_array) > 0) {

            //include all checked fonts
            $fonts_full_list = $default_font_string . '|' . str_replace('+', ' ', $google_fonts_string);
            $fonts_full_list_args = array(
                'family' => urlencode($fonts_full_list),
                'subset' => urlencode($font_subset_str),
            );

            $walker_edge_fonts = add_query_arg( $fonts_full_list_args, $protocol.'//fonts.googleapis.com/css' );
            wp_enqueue_style( 'walker_edge_google_fonts', esc_url_raw($walker_edge_fonts), array(), '1.0.0' );

        } else {
            //include default google font that theme is using
            $default_fonts_args = array(
                'family' => urlencode($default_font_string),
                'subset' => urlencode($font_subset_str),
            );
            $walker_edge_fonts = add_query_arg( $default_fonts_args, $protocol.'//fonts.googleapis.com/css' );
            wp_enqueue_style( 'walker_edge_google_fonts', esc_url_raw($walker_edge_fonts), array(), '1.0.0' );
        }

    }

	add_action('wp_enqueue_scripts', 'walker_edge_google_fonts_styles');
}

if(!function_exists('walker_edge_scripts')) {
    /**
     * Function that includes all necessary scripts
     */
    function walker_edge_scripts() {
        global $wp_scripts;

        //init theme core scripts
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-ui-accordion');
		wp_enqueue_script('wp-mediaelement');

        // 3rd party JavaScripts that we used in our theme
        wp_enqueue_script('jquery-appear', EDGE_ASSETS_ROOT.'/js/modules/plugins/jquery.appear.js', array('jquery'), false, true);
        wp_enqueue_script('modernizr', EDGE_ASSETS_ROOT.'/js/modules/plugins/modernizr.custom.85257.js', array('jquery'), false, true);
        wp_enqueue_script('jquery-hoverIntent', EDGE_ASSETS_ROOT.'/js/modules/plugins/jquery.hoverIntent.min.js', array('jquery'), false, true);
        wp_enqueue_script('jquery-plugin', EDGE_ASSETS_ROOT.'/js/modules/plugins/jquery.plugin.js', array('jquery'), false, true);
        wp_enqueue_script('jquery-countdown', EDGE_ASSETS_ROOT.'/js/modules/plugins/jquery.countdown.min.js', array('jquery'), false, true);
        wp_enqueue_script('owl-carousel', EDGE_ASSETS_ROOT.'/js/modules/plugins/owl.carousel.min.js', array('jquery'), false, true);
        wp_enqueue_script('parallax', EDGE_ASSETS_ROOT.'/js/modules/plugins/parallax.min.js', array('jquery'), false, true);
        wp_enqueue_script('easypiechart', EDGE_ASSETS_ROOT.'/js/modules/plugins/easypiechart.js', array('jquery'), false, true);
        wp_enqueue_script('jquery-waypoints', EDGE_ASSETS_ROOT.'/js/modules/plugins/jquery.waypoints.min.js', array('jquery'), false, true);
        wp_enqueue_script('Chart', EDGE_ASSETS_ROOT.'/js/modules/plugins/Chart.min.js', array('jquery'), false, true);
        wp_enqueue_script('counter', EDGE_ASSETS_ROOT.'/js/modules/plugins/counter.js', array('jquery'), false, true);
        wp_enqueue_script('fluidvids', EDGE_ASSETS_ROOT.'/js/modules/plugins/fluidvids.min.js', array('jquery'), false, true);
        wp_enqueue_script('prettyPhoto', EDGE_ASSETS_ROOT.'/js/modules/plugins/jquery.prettyPhoto.js', array('jquery'), false, true);
        wp_enqueue_script('jquery-nicescroll', EDGE_ASSETS_ROOT.'/js/modules/plugins/jquery.nicescroll.min.js', array('jquery'), false, true);
        wp_enqueue_script('ScrollToPlugin', EDGE_ASSETS_ROOT.'/js/modules/plugins/ScrollToPlugin.min.js', array('jquery'), false, true);
        wp_enqueue_script('TweenLite', EDGE_ASSETS_ROOT.'/js/modules/plugins/TweenLite.min.js', array('jquery'), false, true);
        wp_enqueue_script('jquery-mixitup', EDGE_ASSETS_ROOT.'/js/modules/plugins/jquery.mixitup.min.js', array('jquery'), false, true);
        wp_enqueue_script('jquery-waitforimages', EDGE_ASSETS_ROOT.'/js/modules/plugins/jquery.waitforimages.js', array('jquery'), false, true);
        wp_enqueue_script('jquery-infinitescroll', EDGE_ASSETS_ROOT.'/js/modules/plugins/jquery.infinitescroll.min.js', array('jquery'), false, true);
        wp_enqueue_script('jquery-easing-1.3', EDGE_ASSETS_ROOT.'/js/modules/plugins/jquery.easing.1.3.js', array('jquery'), false, true);
        wp_enqueue_script('skrollr', EDGE_ASSETS_ROOT.'/js/modules/plugins/skrollr.js', array('jquery'), false, true);
        wp_enqueue_script('bootstrapCarousel', EDGE_ASSETS_ROOT.'/js/modules/plugins/bootstrapCarousel.js', array('jquery'), false, true);
        wp_enqueue_script('jquery-touchSwipe', EDGE_ASSETS_ROOT.'/js/modules/plugins/jquery.touchSwipe.min.js', array('jquery'), false, true);
        wp_enqueue_script('isotope', EDGE_ASSETS_ROOT.'/js/jquery.isotope.min.js', array('jquery'), false, true);

		if(walker_edge_is_smoth_scroll_enabled()) {
			wp_enqueue_script("walker_edge_smooth_page_scroll", EDGE_ASSETS_ROOT . "/js/smoothPageScroll.js", array(), false, true);
		}

        if(walker_edge_is_woocommerce_installed()) {
            wp_enqueue_script('select2');
        }

        //include google map api script
        if (walker_edge_options()->getOptionValue('google_maps_api_key') != '') {
            $google_maps_api_key = walker_edge_options()->getOptionValue('google_maps_api_key');
            wp_enqueue_script('google_map_api', '//maps.googleapis.com/maps/api/js?key=' . $google_maps_api_key, array(), false, true);
        } else {
            wp_enqueue_script('google_map_api', '//maps.googleapis.com/maps/api/js', array(), false, true);
        }

        wp_enqueue_script('walker_edge_modules', EDGE_ASSETS_ROOT.'/js/modules.min.js', array('jquery'), false, true);

        //include comment reply script
        $wp_scripts->add_data('comment-reply', 'group', 1);
        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
            wp_enqueue_script( 'comment-reply' );
        }

        //include Visual Composer script
        if(class_exists('WPBakeryVisualComposerAbstract')) {
            wp_enqueue_script('wpb_composer_front_js');
        }
    }

    add_action('wp_enqueue_scripts', 'walker_edge_scripts');
}

//defined content width variable
if (!isset( $content_width )) $content_width = 1060;

if(!function_exists('walker_edge_theme_setup')) {
    /**
     * Function that adds various features to theme. Also defines image sizes that are used in a theme
     */
    function walker_edge_theme_setup() {
        //add support for feed links
        add_theme_support('automatic-feed-links');

        //add support for post formats
        add_theme_support('post-formats', array('gallery', 'link', 'quote', 'video', 'audio'));

        //add theme support for post thumbnails
        add_theme_support('post-thumbnails');

        //add theme support for title tag
        add_theme_support('title-tag');

        //define thumbnail sizes
        add_image_size('walker_edge_feature_image', 1100);
        add_image_size('walker_edge_square', 650, 650, true);
        add_image_size('walker_edge_landscape', 800, 600, true);
        add_image_size('walker_edge_portrait', 600, 800, true);
        add_image_size('walker_edge_search_image', 76, 58, true);
        add_image_size('walker_edge_large_width', 1600, 600, true);
        add_image_size('walker_edge_large_height', 800, 1200, true);
        add_image_size('walker_edge_large_width_height', 1600, 1200, true);

        load_theme_textdomain( 'walker', get_template_directory().'/languages' );
    }

    add_action('after_setup_theme', 'walker_edge_theme_setup');
}

if(!function_exists('walker_edge_rgba_color')) {
    /**
     * Function that generates rgba part of css color property
     *
     * @param $color string hex color
     * @param $transparency float transparency value between 0 and 1
     *
     * @return string generated rgba string
     */
    function walker_edge_rgba_color($color, $transparency) {
        if($color !== '' && $transparency !== '') {
            $rgba_color = '';

            $rgb_color_array = walker_edge_hex2rgb($color);
            $rgba_color .= 'rgba('.implode(', ', $rgb_color_array).', '.$transparency.')';

            return $rgba_color;
        }
    }
}

if(!function_exists('walker_edge_header_meta')) {
    /**
     * Function that echoes meta data if our seo is enabled
     */
    function walker_edge_header_meta() { ?>

        <meta charset="<?php bloginfo('charset'); ?>"/>
        <link rel="profile" href="http://gmpg.org/xfn/11"/>
	    <?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
		    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	    <?php endif; ?>

    <?php }

    add_action('walker_edge_header_meta', 'walker_edge_header_meta');
}

if(!function_exists('walker_edge_user_scalable_meta')) {
    /**
     * Function that outputs user scalable meta if responsiveness is turned on
     * Hooked to walker_edge_header_meta action
     */
    function walker_edge_user_scalable_meta() {
        //is responsiveness option is chosen?
        if(walker_edge_is_responsive_on()) { ?>
            <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
        <?php } else { ?>
            <meta name="viewport" content="width=1200,user-scalable=yes">
        <?php }
    }

    add_action('walker_edge_header_meta', 'walker_edge_user_scalable_meta');
}

if(!function_exists('walker_edge_get_page_id')) {
	/**
	 * Function that returns current page / post id.
	 * Checks if current page is woocommerce page and returns that id if it is.
	 * Checks if current page is any archive page (category, tag, date, author etc.) and returns -1 because that isn't
	 * page that is created in WP admin.
	 *
	 * @return int
	 *
	 * @version 0.1
	 *
	 * @see walker_edge_is_woocommerce_installed()
	 * @see walker_edge_is_woocommerce_shop()
	 */
	function walker_edge_get_page_id() {
		if(walker_edge_is_woocommerce_installed() && walker_edge_is_woocommerce_shop()) {
			return walker_edge_get_woo_shop_page_id();
		}

		if(is_archive() || is_search() || is_404() || (is_home() && is_front_page())) {
			return -1;
		}

		return get_queried_object_id();
	}
}

if(!function_exists('walker_edge_is_default_wp_template')) {
    /**
     * Function that checks if current page archive page, search, 404 or default home blog page
     * @return bool
     *
     * @see is_archive()
     * @see is_search()
     * @see is_404()
     * @see is_front_page()
     * @see is_home()
     */
    function walker_edge_is_default_wp_template() {
        return is_archive() || is_search() || is_404() || (is_front_page() && is_home());
    }
}

if(!function_exists('walker_edge_get_page_template_name')) {
    /**
     * Returns current template file name without extension
     * @return string name of current template file
     */
    function walker_edge_get_page_template_name() {
        $file_name = '';

        if(!walker_edge_is_default_wp_template()) {
            $file_name_without_ext = preg_replace('/\\.[^.\\s]{3,4}$/', '', basename(get_page_template()));

            if($file_name_without_ext !== '') {
                $file_name = $file_name_without_ext;
            }
        }

        return $file_name;
    }
}

if(!function_exists('walker_edge_has_shortcode')) {
    /**
     * Function that checks whether shortcode exists on current page / post
     *
     * @param string shortcode to find
     * @param string content to check. If isn't passed current post content will be used
     *
     * @return bool whether content has shortcode or not
     */
    function walker_edge_has_shortcode($shortcode, $content = '') {
        $has_shortcode = false;

        if($shortcode) {
            //if content variable isn't past
            if($content == '') {
                //take content from current post
                $page_id = walker_edge_get_page_id();
                if(!empty($page_id)) {
                    $current_post = get_post($page_id);

                    if(is_object($current_post) && property_exists($current_post, 'post_content')) {
                        $content = $current_post->post_content;
                    }
                }
            }

            //does content has shortcode added?
            if(stripos($content, '['.$shortcode) !== false) {
                $has_shortcode = true;
            }
        }

        return $has_shortcode;
    }
}

if(!function_exists('walker_edge_get_dynamic_sidebar')) {
    /**
     * Return Custom Widget Area content
     *
     * @return string
     */
    function walker_edge_get_dynamic_sidebar($index = 1) {
        ob_start();
        dynamic_sidebar($index);
        $sidebar_contents = ob_get_clean();

        return $sidebar_contents;
    }
}

if(!function_exists('walker_edge_get_sidebar')) {
    /**
     * Return Sidebar
     *
     * @return string
     */
    function walker_edge_get_sidebar() {

        $id = walker_edge_get_page_id();

        $sidebar = "sidebar";

        if (get_post_meta($id, 'edgtf_custom_sidebar_meta', true) != '') {
            $sidebar = get_post_meta($id, 'edgtf_custom_sidebar_meta', true);
        } else {
            if (is_single() && walker_edge_options()->getOptionValue('blog_single_custom_sidebar') != '') {
                $sidebar = esc_attr(walker_edge_options()->getOptionValue('blog_single_custom_sidebar'));
            } elseif ((walker_edge_is_product_category() || walker_edge_is_product_tag()) && walker_edge_get_woo_shop_page_id()) {
                $shop_id = walker_edge_get_woo_shop_page_id();
                if(get_post_meta($shop_id, 'edgtf_custom_sidebar_meta', true) != '') {
                    $sidebar = esc_attr(get_post_meta($shop_id, 'edgtf_custom_sidebar_meta', true));
                }
            } elseif ((is_archive() || (is_home() && is_front_page())) && walker_edge_options()->getOptionValue('blog_custom_sidebar') != '') {
                $sidebar = esc_attr(walker_edge_options()->getOptionValue('blog_custom_sidebar'));
            } elseif (is_search() && walker_edge_options()->getOptionValue('search_page_custom_sidebar') != '') {
                $sidebar = esc_attr(walker_edge_options()->getOptionValue('search_page_custom_sidebar'));
            } elseif (is_page() && walker_edge_options()->getOptionValue('page_custom_sidebar') != '') {
                $sidebar = esc_attr(walker_edge_options()->getOptionValue('page_custom_sidebar'));
            }
        }

        return $sidebar;
    }
}

if( !function_exists('walker_edge_sidebar_columns_class') ) {

    /**
     * Return classes for columns holder when sidebar is active
     *
     * @return array
     */

    function walker_edge_sidebar_columns_class() {

        $sidebar_class = array();
        $sidebar_layout = walker_edge_sidebar_layout();

        switch($sidebar_layout):
            case 'sidebar-33-right':
                $sidebar_class[] = 'edgtf-two-columns-66-33';
                break;
            case 'sidebar-25-right':
                $sidebar_class[] = 'edgtf-two-columns-75-25';
                break;
            case 'sidebar-33-left':
                $sidebar_class[] = 'edgtf-two-columns-33-66';
                break;
            case 'sidebar-25-left':
                $sidebar_class[] = 'edgtf-two-columns-25-75';
                break;

        endswitch;

        $sidebar_class[] = ' edgtf-content-has-sidebar clearfix';

        return walker_edge_class_attribute($sidebar_class);
    }
}

if( !function_exists('walker_edge_sidebar_layout') ) {

    /**
     * Function that check is sidebar is enabled and return type of sidebar layout
     */
    function walker_edge_sidebar_layout() {

        $sidebar_layout = '';
        $page_id        = walker_edge_get_page_id();

        $page_sidebar_meta = get_post_meta($page_id, 'edgtf_sidebar_meta', true);

        if(($page_sidebar_meta !== '') && $page_id !== -1) {
            if($page_sidebar_meta == 'no-sidebar') {
                $sidebar_layout = '';
            } else {
                $sidebar_layout = $page_sidebar_meta;
            }
        } else {
            if(is_single() && walker_edge_options()->getOptionValue('blog_single_sidebar_layout')) {
                $sidebar_layout = esc_attr(walker_edge_options()->getOptionValue('blog_single_sidebar_layout'));
            } elseif((is_archive() || (is_home() && is_front_page())) && walker_edge_options()->getOptionValue('archive_sidebar_layout')) {
                $sidebar_layout = esc_attr(walker_edge_options()->getOptionValue('archive_sidebar_layout'));
            } elseif(is_page() && walker_edge_options()->getOptionValue('page_sidebar_layout')) {
                $sidebar_layout = esc_attr(walker_edge_options()->getOptionValue('page_sidebar_layout'));
            }
        }

        return $sidebar_layout;
    }
}

if( !function_exists('walker_edge_page_custom_style') ) {
    /**
     * Function that print custom page style
     */
    function walker_edge_page_custom_style() {
        $style = '';
        $style = apply_filters('walker_edge_add_page_custom_style', $style);
        
        if($style !== '') {
            wp_add_inline_style( 'walker_edge_modules', $style);
        }
    }
    
    add_action('wp_enqueue_scripts', 'walker_edge_page_custom_style');
}

if( !function_exists('walker_edge_container_style') ) {
    /**
     * Function that return container style
     */
    function walker_edge_container_style($style) {
        $id = walker_edge_get_page_id();
        $class_prefix = walker_edge_get_unique_page_class();

        $container_selector = array(
            $class_prefix.' .edgtf-content .edgtf-content-inner > .edgtf-container',
            $class_prefix.' .edgtf-content .edgtf-content-inner > .edgtf-full-width',
        );

        $container_class = array();
        $page_backgorund_color = get_post_meta($id, "edgtf_page_background_color_meta", true);

        if($page_backgorund_color){
            $container_class['background-color'] = $page_backgorund_color;
        }

        $current_style = walker_edge_dynamic_css($container_selector, $container_class);
        $current_style = $current_style . $style;

        return $current_style;

    }
    add_filter('walker_edge_add_page_custom_style', 'walker_edge_container_style');
}

if(!function_exists('walker_edge_get_unique_page_class')) {
    /**
     * Returns unique page class based on post type and page id
     *
     * @return string
     */
    function walker_edge_get_unique_page_class() {
        $id = walker_edge_get_page_id();
        $page_class = '';

        if(is_single()) {
            $page_class = '.postid-'.$id;
        } elseif($id === walker_edge_get_woo_shop_page_id()) {
            $page_class = '.archive';
        } else {
            $page_class .= '.page-id-'.$id;
        }

        return $page_class;
    }
}

if( !function_exists('walker_edge_content_padding_top') ) {

    /**
     * Function that return padding for content
     */
    function walker_edge_content_padding_top($style) {

        $id = walker_edge_get_page_id();
        $current_style = '';

        if(is_single()) {
            $post_type = '.postid-';
        } else {
            $post_type = '.page-id-';
        }

        $content_selector = array(
            $post_type . $id . ' .edgtf-content .edgtf-content-inner > .edgtf-container > .edgtf-container-inner',
            $post_type . $id . ' .edgtf-content .edgtf-content-inner > .edgtf-full-width > .edgtf-full-width-inner',
        );

        $content_class = array();

        $page_padding_top = get_post_meta($id, "edgtf_page_content_top_padding", true);

        if($page_padding_top !== ''){
            if(get_post_meta($id, "edgtf_page_content_top_padding_mobile", true) == 'yes') {
                $content_class['padding-top'] = walker_edge_filter_px($page_padding_top).'px!important';
            }
            else {
                $content_class['padding-top'] = walker_edge_filter_px($page_padding_top).'px';
            }
            $current_style .= walker_edge_dynamic_css($content_selector, $content_class);
        }

        $current_style = $current_style . $style;

        return $current_style;

    }
    add_filter('walker_edge_add_page_custom_style', 'walker_edge_content_padding_top');
}

if(!function_exists('walker_edge_print_custom_css')) {
    /**
     * Prints out custom css from theme options
     */
    function walker_edge_print_custom_css() {
        $custom_css = walker_edge_options()->getOptionValue('custom_css');

        if($custom_css !== '') {
            wp_add_inline_style('walker_edge_modules', $custom_css);
        }
    }

    add_action('wp_enqueue_scripts', 'walker_edge_print_custom_css');
}

if(!function_exists('walker_edge_print_custom_js')) {
    /**
     * Prints out custom css from theme options
     */
    function walker_edge_print_custom_js() {
        $custom_js = walker_edge_options()->getOptionValue('custom_js');

        if($custom_js !== '') {
            wp_add_inline_script('walker_edge_modules', $custom_js);
        }
    }

    add_action('wp_enqueue_scripts', 'walker_edge_print_custom_js');
}

if(!function_exists('walker_edge_get_global_variables')) {
    /**
     * Function that generates global variables and put them in array so they could be used in the theme
     */
    function walker_edge_get_global_variables() {

        $global_variables = array();
        $element_appear_amount = -150;

        $global_variables['edgtfAddForAdminBar'] = is_admin_bar_showing() ? 32 : 0;
        $global_variables['edgtfElementAppearAmount'] = walker_edge_options()->getOptionValue('element_appear_amount') !== '' ? walker_edge_options()->getOptionValue('element_appear_amount') : $element_appear_amount;
        $global_variables['edgtfFinishedMessage'] = esc_html__('No more posts', 'walker');
        $global_variables['edgtfMessage'] = esc_html__('Loading new posts...', 'walker');

        $global_variables = apply_filters('walker_edge_js_global_variables', $global_variables);

        wp_localize_script('walker_edge_modules', 'edgtfGlobalVars', array(
            'vars' => $global_variables
        ));

    }

    add_action('wp_enqueue_scripts', 'walker_edge_get_global_variables');
}

if(!function_exists('walker_edge_per_page_js_variables')) {
	/**
	 * Outputs global JS variable that holds page settings
	 */
	function walker_edge_per_page_js_variables() {
        $per_page_js_vars = apply_filters('walker_edge_per_page_js_vars', array());

        wp_localize_script('walker_edge_modules', 'edgtfPerPageVars', array(
            'vars' => $per_page_js_vars
        ));
    }

    add_action('wp_enqueue_scripts', 'walker_edge_per_page_js_variables');
}

if(!function_exists('walker_edge_content_elem_style_attr')) {
    /**
     * Defines filter for adding custom styles to content HTML element
     */
    function walker_edge_content_elem_style_attr() {
        $styles = apply_filters('walker_edge_content_elem_style_attr', array());

        walker_edge_inline_style($styles);
    }
}

if(!function_exists('walker_edge_is_woocommerce_installed')) {
    /**
     * Function that checks if woocommerce is installed
     * @return bool
     */
    function walker_edge_is_woocommerce_installed() {
        return function_exists('is_woocommerce');
    }
}

if(!function_exists('walker_edge_visual_composer_installed')) {
    /**
     * Function that checks if visual composer installed
     * @return bool
     */
    function walker_edge_visual_composer_installed() {
        //is Visual Composer installed?
        if(class_exists('WPBakeryVisualComposerAbstract')) {
            return true;
        }

        return false;
    }
}

if(!function_exists('walker_edge_contact_form_7_installed')) {
    /**
     * Function that checks if contact form 7 installed
     * @return bool
     */
    function walker_edge_contact_form_7_installed() {
        //is Contact Form 7 installed?
        if(defined('WPCF7_VERSION')) {
            return true;
        }

        return false;
    }
}

if(!function_exists('walker_edge_is_wpml_installed')) {
    /**
     * Function that checks if WPML plugin is installed
     * @return bool
     *
     * @version 0.1
     */
    function walker_edge_is_wpml_installed() {
        return defined('ICL_SITEPRESS_VERSION');
    }
}

if(!function_exists('walker_edge_max_image_width_srcset')) {
	/**
	 * Set max width for srcset to 1920
	 *
	 * @return int
	 */
	function walker_edge_max_image_width_srcset() {
        return 1920;
    }

	add_filter('max_srcset_image_width', 'walker_edge_max_image_width_srcset');
}
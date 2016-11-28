<?php

use WalkerEdgeNamespace\Modules\Header\Lib\HeaderFactory;

if(!function_exists('walker_edge_get_header')) {
    /**
     * Loads header HTML based on header type option. Sets all necessary parameters for header
     * and defines walker_edge_header_type_parameters filter
     */
    function walker_edge_get_header() {

        //will be read from options
        $header_type     = walker_edge_get_meta_field_intersect('header_type');

        if($header_type !== 'header-bottom') {

            $simple_header_in_grid     = walker_edge_get_meta_field_intersect('enable_grid_layout_header_simple');

            $full_screen_header_in_grid = walker_edge_get_meta_field_intersect('enable_grid_layout_header_full_screen');

            $header_behavior = walker_edge_options()->getOptionValue('header_behaviour');

            extract(walker_edge_get_page_options());

            if(HeaderFactory::getInstance()->validHeaderObject()) {
                $parameters = array(
                    'hide_logo'          => walker_edge_options()->getOptionValue('hide_logo') == 'yes' ? true : false,
                    'simple_header_in_grid' => $simple_header_in_grid == 'yes' ? true : false,
                    'full_screen_header_in_grid' => $full_screen_header_in_grid == 'yes' ? true : false,
                    'show_sticky'        => in_array($header_behavior, array(
                        'sticky-header-on-scroll-up',
                        'sticky-header-on-scroll-down-up'
                    )) ? true : false,
                    'show_fixed_wrapper' => in_array($header_behavior, array('fixed-on-scroll')) ? true : false,
                    'menu_area_background_color' => $menu_area_background_color,
                    'menu_area_border_bottom_color' => $menu_area_border_bottom_color,
                    'vertical_header_background_color' => $vertical_header_background_color,
                    'vertical_header_opacity' => $vertical_header_opacity,
                    'vertical_background_image' => $vertical_background_image,
                    'vertical_text_align_class' => $vertical_text_align_class
                );

                $parameters = apply_filters('walker_edge_header_type_parameters', $parameters, $header_type);

                HeaderFactory::getInstance()->getHeaderObject()->loadTemplate($parameters);
            }
        }
    }
}

if(!function_exists('walker_edge_get_header_bottom')) {
    /**
     * Loads header HTML based on header type option. Sets all necessary parameters for header
     * and defines walker_edge_header_type_parameters filter
     */
    function walker_edge_get_header_bottom() {

        //will be read from options
        $header_type     = walker_edge_get_meta_field_intersect('header_type');

        if($header_type === 'header-bottom') {
            extract(walker_edge_get_page_options());

            if(HeaderFactory::getInstance()->validHeaderObject()) {
                $parameters = array(
                    'hide_logo'          => walker_edge_options()->getOptionValue('hide_logo') == 'yes' ? true : false,
                    'menu_area_background_color' => $menu_area_background_color,
                    'menu_area_border_bottom_color' => $menu_area_border_bottom_color
                );

                $parameters = apply_filters('walker_edge_header_type_parameters', $parameters, $header_type);

                HeaderFactory::getInstance()->getHeaderObject()->loadTemplate($parameters);
            }
        }
    }
}

if(!function_exists('walker_edge_get_header_top')) {
    /**
     * Loads header top HTML and sets parameters for it
     */
    function walker_edge_get_header_top() {

        //generate column width class
        switch(walker_edge_options()->getOptionValue('top_bar_layout')) {
            case ('two-columns'):
                $column_widht_class = '50-50';
                break;
            case ('three-columns'):
                $column_widht_class = walker_edge_options()->getOptionValue('top_bar_column_widths');
                break;
        }

        $params = array(
            'column_widths'      => $column_widht_class,
            'show_widget_center' => walker_edge_options()->getOptionValue('top_bar_layout') === 'three-columns' ? true : false,
            'show_header_top'    => walker_edge_get_meta_field_intersect('top_bar') === 'yes' ? true : false,
            'top_bar_in_grid'    => walker_edge_get_meta_field_intersect('top_bar_in_grid') === 'yes' ? true : false
        );

        $params = apply_filters('walker_edge_header_top_params', $params);

        walker_edge_get_module_template_part('templates/parts/header-top', 'header', '', $params);
    }
}

if(!function_exists('walker_edge_get_logo')) {
    /**
     * Loads logo HTML
     *
     * @param $slug
     */
    function walker_edge_get_logo($slug = '') {

        $slug = $slug !== '' ? $slug : walker_edge_options()->getOptionValue('header_type');

        if ($slug == 'sticky'){
            $logo_image = walker_edge_options()->getOptionValue('logo_image_sticky');
        } else if ($slug == 'classic'){
            $logo_image = walker_edge_options()->getOptionValue('logo_image_classic_header');
        } else if ($slug == 'vertical'){
            $logo_image = walker_edge_options()->getOptionValue('logo_image_vertical_header');
        } else {
            $logo_image = walker_edge_options()->getOptionValue('logo_image');
        }

        $logo_image_dark = walker_edge_options()->getOptionValue('logo_image_dark');
        $logo_image_light = walker_edge_options()->getOptionValue('logo_image_light');


        //get logo image dimensions and set style attribute for image link.
        $logo_dimensions = walker_edge_get_image_dimensions($logo_image);

        $logo_height = '';
        $logo_styles = '';
        if(is_array($logo_dimensions) && array_key_exists('height', $logo_dimensions)) {
            $logo_height = $logo_dimensions['height'];
            $logo_styles = 'height: '.intval($logo_height / 2).'px;'; //divided with 2 because of retina screens
        }

        $params = array(
            'logo_image'  => $logo_image,
            'logo_image_dark' => $logo_image_dark,
            'logo_image_light' => $logo_image_light,
            'logo_styles' => $logo_styles
        );

        walker_edge_get_module_template_part('templates/parts/logo', 'header', $slug, $params);
    }
}

if(!function_exists('walker_edge_get_main_menu')) {
    /**
     * Loads main menu HTML
     *
     * @param string $additional_class addition class to pass to template
     */
    function walker_edge_get_main_menu($additional_class = 'edgtf-default-nav') {
        walker_edge_get_module_template_part('templates/parts/navigation', 'header', '', array('additional_class' => $additional_class));
    }
}

if(!function_exists('walker_edge_get_sticky_menu')) {
	/**
	 * Loads sticky menu HTML
	 *
	 * @param string $additional_class addition class to pass to template
	 */
	function walker_edge_get_sticky_menu($additional_class = 'edgtf-default-nav') {
		walker_edge_get_module_template_part('templates/parts/sticky-navigation', 'header', '', array('additional_class' => $additional_class));
	}
}

if(!function_exists('walker_edge_get_vertical_main_menu')) {
    /**
     * Loads vertical menu HTML
     */
    function walker_edge_get_vertical_main_menu() {
        walker_edge_get_module_template_part('templates/parts/vertical-navigation', 'header', '');
    }
}

if(!function_exists('walker_edge_get_sticky_header')) {
    /**
     * Loads sticky header behavior HTML
     */
    function walker_edge_get_sticky_header() {

        $parameters = array(
            'hide_logo'             => walker_edge_options()->getOptionValue('hide_logo') == 'yes' ? true : false,
            'sticky_header_in_grid' => walker_edge_options()->getOptionValue('sticky_header_in_grid') == 'yes' ? true : false
        );

        walker_edge_get_module_template_part('templates/behaviors/sticky-header', 'header', '', $parameters);
    }
}

if(!function_exists('walker_edge_get_mobile_header')) {
    /**
     * Loads mobile header HTML only if responsiveness is enabled
     */
    function walker_edge_get_mobile_header() {
        if(walker_edge_is_responsive_on()) {

            $header_type  = walker_edge_get_meta_field_intersect('header_type');

            if($header_type !== 'header-bottom') {

                $mobile_menu_title = walker_edge_options()->getOptionValue('mobile_menu_title');

                $has_navigation = false;
                if(has_nav_menu('main-navigation') || has_nav_menu('mobile-navigation')) {
                    $has_navigation = true;
                }

                $parameters = array(
                    'show_logo'              => walker_edge_options()->getOptionValue('hide_logo') == 'yes' ? false : true,
                    'menu_opener_icon'       => walker_edge_icon_collections()->getMobileMenuIcon(walker_edge_options()->getOptionValue('mobile_icon_pack'), true),
                    'show_navigation_opener' => $has_navigation,
                    'mobile_menu_title'      => $mobile_menu_title
                );

                walker_edge_get_module_template_part('templates/types/mobile-header', 'header', $header_type, $parameters);
            }
        }
    }
}

if(!function_exists('walker_edge_get_mobile_header_bottom')) {
    /**
     * Loads mobile header HTML only if responsiveness is enabled
     */
    function walker_edge_get_mobile_header_bottom() {
        if(walker_edge_is_responsive_on()) {

            $header_type  = walker_edge_get_meta_field_intersect('header_type');

            if($header_type === 'header-bottom') {

                $mobile_menu_title = walker_edge_options()->getOptionValue('mobile_menu_title');

                $has_navigation = false;
                if(has_nav_menu('main-navigation') || has_nav_menu('mobile-navigation')) {
                    $has_navigation = true;
                }

                $parameters = array(
                    'show_logo'              => walker_edge_options()->getOptionValue('hide_logo') == 'yes' ? false : true,
                    'menu_opener_icon'       => walker_edge_icon_collections()->getMobileMenuIcon(walker_edge_options()->getOptionValue('mobile_icon_pack'), true),
                    'show_navigation_opener' => $has_navigation,
                    'mobile_menu_title'      => $mobile_menu_title
                );

                walker_edge_get_module_template_part('templates/types/mobile-header', 'header', $header_type, $parameters);
            }
        }
    }
}

if(!function_exists('walker_edge_get_mobile_logo')) {
    /**
     * Loads mobile logo HTML. It checks if mobile logo image is set and uses that, else takes normal logo image
     *
     * @param string $slug
     */
    function walker_edge_get_mobile_logo($slug = '') {

        $slug = $slug !== '' ? $slug : walker_edge_options()->getOptionValue('header_type');

        //check if mobile logo has been set and use that, else use normal logo
        if(walker_edge_options()->getOptionValue('logo_image_mobile') !== '') {
            $logo_image = walker_edge_options()->getOptionValue('logo_image_mobile');
        } else {
            $logo_image = walker_edge_options()->getOptionValue('logo_image');
        }

        //get logo image dimensions and set style attribute for image link.
        $logo_dimensions = walker_edge_get_image_dimensions($logo_image);

        $logo_height = '';
        $logo_styles = '';
        if(is_array($logo_dimensions) && array_key_exists('height', $logo_dimensions)) {
            $logo_height = $logo_dimensions['height'];
            $logo_styles = 'height: '.intval($logo_height / 2).'px'; //divided with 2 because of retina screens
        }

        //set parameters for logo
        $parameters = array(
            'logo_image'      => $logo_image,
            'logo_dimensions' => $logo_dimensions,
            'logo_height'     => $logo_height,
            'logo_styles'     => $logo_styles
        );

        walker_edge_get_module_template_part('templates/parts/mobile-logo', 'header', $slug, $parameters);
    }
}

if(!function_exists('walker_edge_get_mobile_nav')) {
    /**
     * Loads mobile navigation HTML
     */
    function walker_edge_get_mobile_nav() {

        walker_edge_get_module_template_part('templates/parts/mobile-navigation', 'header', '');
    }
}

if(!function_exists('walker_edge_get_page_options')) {
    /**
     * Gets options from page
     */
    function walker_edge_get_page_options() {
        $id = walker_edge_get_page_id();
        $page_options = array();
        $menu_area_background_color_rgba = '';
        $menu_area_background_color = '';
        $menu_area_background_transparency = '1';
        $menu_area_border_bottom_color = '';
        $vertical_header_background_color = '';
        $vertical_header_opacity = '';
        $vertical_background_image = '';
        $vertical_text_align_class = '';

        $header_type = walker_edge_get_meta_field_intersect('header_type');
        
        switch ($header_type) {
            case 'header-standard':

                if(get_post_meta($id, 'edgtf_menu_area_background_color_header_standard_meta', true) !== '') {
                    $menu_area_background_color = get_post_meta($id, 'edgtf_menu_area_background_color_header_standard_meta', true);
                }

                if(get_post_meta($id, 'edgtf_menu_area_background_transparency_header_standard_meta', true) !== '') {
                    $menu_area_background_transparency = get_post_meta($id, 'edgtf_menu_area_background_transparency_header_standard_meta', true);
                }

                if(get_post_meta($id, 'edgtf_menu_area_background_color_header_standard_meta', true) === '' && get_post_meta($id, 'edgtf_menu_area_background_transparency_header_standard_meta', true) !== '') {
                    $menu_area_background_color = '#fff';
                    $menu_area_background_transparency = get_post_meta($id, 'edgtf_menu_area_background_transparency_header_standard_meta', true);
                }

                if(walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency) !== null) {
                    $menu_area_background_color_rgba = 'background-color:'.walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency);
                }

                if(get_post_meta($id, 'edgtf_menu_area_border_bottom_color_header_standard_meta', true) !== '') {
                    $menu_area_border_bottom_color = 'border-color:'.get_post_meta($id, 'edgtf_menu_area_border_bottom_color_header_standard_meta', true);
                }

                break;


            case 'header-simple':

                if(get_post_meta($id, 'edgtf_menu_area_background_color_header_simple_meta', true) !== '') {
                    $menu_area_background_color = get_post_meta($id, 'edgtf_menu_area_background_color_header_simple_meta', true);
                }

                if(get_post_meta($id, 'edgtf_menu_area_background_transparency_header_simple_meta', true) !== '') {
                    $menu_area_background_transparency = get_post_meta($id, 'edgtf_menu_area_background_transparency_header_simple_meta', true);
                }

                if(get_post_meta($id, 'edgtf_menu_area_background_color_header_simple_meta', true) === '' && get_post_meta($id, 'edgtf_menu_area_background_transparency_header_simple_meta', true) !== '') {
                    $menu_area_background_color = '#fff';
                    $menu_area_background_transparency = get_post_meta($id, 'edgtf_menu_area_background_transparency_header_simple_meta', true);
                }

                if(walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency) !== null) {
                    $menu_area_background_color_rgba = 'background-color:'.walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency);
                }

                if(get_post_meta($id, 'edgtf_menu_area_border_bottom_color_header_simple_meta', true) !== '') {
                    $menu_area_border_bottom_color = 'border-color:'.get_post_meta($id, 'edgtf_menu_area_border_bottom_color_header_simple_meta', true);
                }

                break;


            case 'header-classic':

                if(get_post_meta($id, 'edgtf_menu_area_background_color_header_classic_meta', true) !== '') {
                    $menu_area_background_color = get_post_meta($id, 'edgtf_menu_area_background_color_header_classic_meta', true);
                }

                if(get_post_meta($id, 'edgtf_menu_area_background_transparency_header_classic_meta', true) !== '') {
                    $menu_area_background_transparency = get_post_meta($id, 'edgtf_menu_area_background_transparency_header_classic_meta', true);
                }

                if(get_post_meta($id, 'edgtf_menu_area_background_color_header_classic_meta', true) === '' && get_post_meta($id, 'edgtf_menu_area_background_transparency_header_classic_meta', true) !== '') {
                    $menu_area_background_color = '#fff';
                    $menu_area_background_transparency = get_post_meta($id, 'edgtf_menu_area_background_transparency_header_classic_meta', true);
                }

                if(walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency) !== null) {
                    $menu_area_background_color_rgba = 'background-color:'.walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency);
                }

                if(get_post_meta($id, 'edgtf_menu_area_border_bottom_color_header_classic_meta', true) !== '') {
                    $menu_area_border_bottom_color = 'border-color:'.get_post_meta($id, 'edgtf_menu_area_border_bottom_color_header_classic_meta', true);
                }

                break;


            case 'header-vertical':
            
                if(get_post_meta($id, 'edgtf_vertical_header_background_color_meta', true) !== '') {
                    $vertical_header_background_color = 'background-color:'.get_post_meta($id, 'edgtf_vertical_header_background_color_meta', true);
                }

                if(get_post_meta($id, 'edgtf_vertical_header_transparency_meta', true) !== '') {
                    $vertical_header_opacity = 'opacity:'.get_post_meta($id, 'edgtf_vertical_header_transparency_meta', true);
                }

                if(get_post_meta($id, 'edgtf_vertical_header_background_color_meta', true) === '' && get_post_meta($id, 'edgtf_vertical_header_transparency_meta', true) !== '') {
                    $vertical_header_background_color = '#fff';
                    $vertical_header_opacity = get_post_meta($id, 'edgtf_vertical_header_transparency_meta', true);
                }

                if(get_post_meta($id, 'edgtf_disable_vertical_header_background_image_meta', true) === 'yes'){
                    $vertical_background_image = 'background-image: none';
                } elseif (($meta_temp = get_post_meta($id, 'edgtf_vertical_header_background_image_meta', true)) !== ''){
                    $vertical_background_image = 'background-image: url('.$meta_temp.')';
                }

                if(walker_edge_get_meta_field_intersect('vertical_header_text_align') !== '') {
                    $vertical_text_align_class = 'edgtf-vertical-align-'.walker_edge_get_meta_field_intersect('vertical_header_text_align');
                }

                break;
    

            case 'header-full-screen':

                if(get_post_meta($id, 'edgtf_menu_area_background_color_header_full_screen_meta', true) !== '') {
                    $menu_area_background_color = get_post_meta($id, 'edgtf_menu_area_background_color_header_full_screen_meta', true);
                }

                if(get_post_meta($id, 'edgtf_menu_area_background_transparency_header_full_screen_meta', true) !== '') {
                    $menu_area_background_transparency = get_post_meta($id, 'edgtf_menu_area_background_transparency_header_full_screen_meta', true);
                }

                if(get_post_meta($id, 'edgtf_menu_area_background_color_header_full_screen_meta', true) === '' && get_post_meta($id, 'edgtf_menu_area_background_transparency_header_full_screen_meta', true) !== '') {
                    $menu_area_background_color = '#fff';
                    $menu_area_background_transparency = get_post_meta($id, 'edgtf_menu_area_background_transparency_header_full_screen_meta', true);
                }

                if(walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency) !== null) {
                    $menu_area_background_color_rgba = 'background-color:'.walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency);
                }

                if(get_post_meta($id, 'edgtf_menu_area_border_bottom_color_header_full_screen_meta', true) !== '') {
                    $menu_area_border_bottom_color = 'border-color:'.get_post_meta($id, 'edgtf_menu_area_border_bottom_color_header_full_screen_meta', true);
                }

                break;


            case 'header-bottom':

                if(get_post_meta($id, 'edgtf_menu_area_background_color_header_bottom_meta', true) !== '') {
                    $menu_area_background_color = get_post_meta($id, 'edgtf_menu_area_background_color_header_bottom_meta', true);
                }

                if(get_post_meta($id, 'edgtf_menu_area_background_transparency_header_bottom_meta', true) !== '') {
                    $menu_area_background_transparency = get_post_meta($id, 'edgtf_menu_area_background_transparency_header_bottom_meta', true);
                }

                if(get_post_meta($id, 'edgtf_menu_area_background_color_header_bottom_meta', true) === '' && get_post_meta($id, 'edgtf_menu_area_background_transparency_header_bottom_meta', true) !== '') {
                    $menu_area_background_color = '#fff';
                    $menu_area_background_transparency = get_post_meta($id, 'edgtf_menu_area_background_transparency_header_bottom_meta', true);
                }

                if(walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency) !== null) {
                    $menu_area_background_color_rgba = 'background-color:'.walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency);
                }

                if(get_post_meta($id, 'edgtf_menu_area_border_bottom_color_header_bottom_meta', true) !== '') {
                    $menu_area_border_bottom_color = 'border-color:'.get_post_meta($id, 'edgtf_menu_area_border_bottom_color_header_bottom_meta', true);
                }

                break;      
        }

        $page_options['menu_area_background_color'] = $menu_area_background_color_rgba;
        $page_options['menu_area_border_bottom_color'] = $menu_area_border_bottom_color;
        $page_options['vertical_header_background_color'] = $vertical_header_background_color;
        $page_options['vertical_header_opacity'] = $vertical_header_opacity;
        $page_options['vertical_background_image'] = $vertical_background_image;
        $page_options['vertical_text_align_class'] = $vertical_text_align_class;

        return $page_options;
    }
}
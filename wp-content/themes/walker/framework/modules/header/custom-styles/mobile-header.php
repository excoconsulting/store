<?php

if(!function_exists('walker_edge_mobile_header_general_styles')) {
    /**
     * Generates general custom styles for mobile header
     */
    function walker_edge_mobile_header_general_styles() {
        $mobile_header_styles = array();
        if(walker_edge_options()->getOptionValue('mobile_header_height') !== '') {
            $mobile_header_styles['height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('mobile_header_height')).'px';
        }

        if(walker_edge_options()->getOptionValue('mobile_header_background_color')) {
            $mobile_header_styles['background-color'] = walker_edge_options()->getOptionValue('mobile_header_background_color');
        }

        if(walker_edge_options()->getOptionValue('mobile_header_border_bottom_color')) {
            $mobile_header_styles['border-color'] = walker_edge_options()->getOptionValue('mobile_header_border_bottom_color');
        }

        echo walker_edge_dynamic_css('.edgtf-mobile-header .edgtf-mobile-header-inner', $mobile_header_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_mobile_header_general_styles');
}

if(!function_exists('walker_edge_mobile_navigation_styles')) {
    /**
     * Generates styles for mobile navigation
     */
    function walker_edge_mobile_navigation_styles() {
        $mobile_nav_styles = array();
        if(walker_edge_options()->getOptionValue('mobile_menu_background_color')) {
            $mobile_nav_styles['background-color'] = walker_edge_options()->getOptionValue('mobile_menu_background_color');
        }

        if(walker_edge_options()->getOptionValue('mobile_menu_border_bottom_color')) {
            $mobile_nav_styles['border-color'] = walker_edge_options()->getOptionValue('mobile_menu_border_bottom_color');
        }

        echo walker_edge_dynamic_css('.edgtf-mobile-header .edgtf-mobile-nav', $mobile_nav_styles);

        $mobile_nav_item_styles = array();
        if(walker_edge_options()->getOptionValue('mobile_menu_separator_color') !== '') {
            $mobile_nav_item_styles['border-bottom-color'] = walker_edge_options()->getOptionValue('mobile_menu_separator_color');
        }

        if(walker_edge_options()->getOptionValue('mobile_text_color') !== '') {
            $mobile_nav_item_styles['color'] = walker_edge_options()->getOptionValue('mobile_text_color');
        }

        if(walker_edge_is_font_option_valid(walker_edge_options()->getOptionValue('mobile_font_family'))) {
            $mobile_nav_item_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('mobile_font_family'));
        }

        if(walker_edge_options()->getOptionValue('mobile_font_size') !== '') {
            $mobile_nav_item_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('mobile_font_size')).'px';
        }

        if(walker_edge_options()->getOptionValue('mobile_line_height') !== '') {
            $mobile_nav_item_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('mobile_line_height')).'px';
        }

        if(walker_edge_options()->getOptionValue('mobile_text_transform') !== '') {
            $mobile_nav_item_styles['text-transform'] = walker_edge_options()->getOptionValue('mobile_text_transform');
        }

        if(walker_edge_options()->getOptionValue('mobile_font_style') !== '') {
            $mobile_nav_item_styles['font-style'] = walker_edge_options()->getOptionValue('mobile_font_style');
        }

        if(walker_edge_options()->getOptionValue('mobile_font_weight') !== '') {
            $mobile_nav_item_styles['font-weight'] = walker_edge_options()->getOptionValue('mobile_font_weight');
        }

        $mobile_nav_item_selector = array(
            '.edgtf-mobile-header .edgtf-mobile-nav .edgtf-grid > ul > li > a',
            '.edgtf-mobile-header .edgtf-mobile-nav .edgtf-grid > ul > li > h5'
        );

        echo walker_edge_dynamic_css($mobile_nav_item_selector, $mobile_nav_item_styles);

        $mobile_nav_item_hover_styles = array();
        if(walker_edge_options()->getOptionValue('mobile_text_hover_color') !== '') {
            $mobile_nav_item_hover_styles['color'] = walker_edge_options()->getOptionValue('mobile_text_hover_color');
        }

        $mobile_nav_item_selector_hover = array(
            '.edgtf-mobile-header .edgtf-mobile-nav .edgtf-grid > ul > li.edgtf-active-item > a',
            '.edgtf-mobile-header .edgtf-mobile-nav .edgtf-grid > ul > li > a:hover',
            '.edgtf-mobile-header .edgtf-mobile-nav .edgtf-grid > ul > li > h5:hover'
        );

        echo walker_edge_dynamic_css($mobile_nav_item_selector_hover, $mobile_nav_item_hover_styles);

        $mobile_nav_dropdown_item_styles = array();
        if(walker_edge_options()->getOptionValue('mobile_menu_separator_color') !== '') {
            $mobile_nav_dropdown_item_styles['border-bottom-color'] = walker_edge_options()->getOptionValue('mobile_menu_separator_color');
        }

        if(walker_edge_options()->getOptionValue('mobile_dropdown_text_color') !== '') {
            $mobile_nav_dropdown_item_styles['color'] = walker_edge_options()->getOptionValue('mobile_dropdown_text_color');
        }

        if(walker_edge_is_font_option_valid(walker_edge_options()->getOptionValue('mobile_dropdown_font_family'))) {
            $mobile_nav_dropdown_item_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('mobile_dropdown_font_family'));
        }

        if(walker_edge_options()->getOptionValue('mobile_dropdown_font_size') !== '') {
            $mobile_nav_dropdown_item_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('mobile_dropdown_font_size')).'px';
        }

        if(walker_edge_options()->getOptionValue('mobile_dropdown_line_height') !== '') {
            $mobile_nav_dropdown_item_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('mobile_dropdown_line_height')).'px';
        }

        if(walker_edge_options()->getOptionValue('mobile_dropdown_text_transform') !== '') {
            $mobile_nav_dropdown_item_styles['text-transform'] = walker_edge_options()->getOptionValue('mobile_dropdown_text_transform');
        }

        if(walker_edge_options()->getOptionValue('mobile_dropdown_font_style') !== '') {
            $mobile_nav_dropdown_item_styles['font-style'] = walker_edge_options()->getOptionValue('mobile_dropdown_font_style');
        }

        if(walker_edge_options()->getOptionValue('mobile_dropdown_font_weight') !== '') {
            $mobile_nav_dropdown_item_styles['font-weight'] = walker_edge_options()->getOptionValue('mobile_dropdown_font_weight');
        }

        $mobile_nav_dropdown_item_selector = array(
            '.edgtf-mobile-header .edgtf-mobile-nav ul ul li a',
            '.edgtf-mobile-header .edgtf-mobile-nav ul ul li h5'
        );

        echo walker_edge_dynamic_css($mobile_nav_dropdown_item_selector, $mobile_nav_dropdown_item_styles);

        $mobile_nav_dropdown_item_hover_styles = array();
        if(walker_edge_options()->getOptionValue('mobile_dropdown_text_hover_color') !== '') {
            $mobile_nav_dropdown_item_hover_styles['color'] = walker_edge_options()->getOptionValue('mobile_dropdown_text_hover_color');
        }

        $mobile_nav_dropdown_item_selector_hover = array(
            '.edgtf-mobile-header .edgtf-mobile-nav ul ul li.current-menu-ancestor > a',
            '.edgtf-mobile-header .edgtf-mobile-nav ul ul li.current-menu-item > a',
            '.edgtf-mobile-header .edgtf-mobile-nav ul ul li a:hover',
            '.edgtf-mobile-header .edgtf-mobile-nav ul ul li h5:hover'
        );

        echo walker_edge_dynamic_css($mobile_nav_dropdown_item_selector_hover, $mobile_nav_dropdown_item_hover_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_mobile_navigation_styles');
}

if(!function_exists('walker_edge_mobile_logo_styles')) {
    /**
     * Generates styles for mobile logo
     */
    function walker_edge_mobile_logo_styles() {
        if(walker_edge_options()->getOptionValue('mobile_logo_height') !== '') { ?>
            @media only screen and (max-width: 1024px) {
            <?php echo walker_edge_dynamic_css(
                '.edgtf-mobile-header .edgtf-mobile-logo-wrapper a',
                array('height' => walker_edge_filter_px(walker_edge_options()->getOptionValue('mobile_logo_height')).'px !important')
            ); ?>
            }
        <?php }

        if(walker_edge_options()->getOptionValue('mobile_logo_height_phones') !== '') { ?>
            @media only screen and (max-width: 480px) {
            <?php echo walker_edge_dynamic_css(
                '.edgtf-mobile-header .edgtf-mobile-logo-wrapper a',
                array('height' => walker_edge_filter_px(walker_edge_options()->getOptionValue('mobile_logo_height_phones')).'px !important')
            ); ?>
            }
        <?php }

        if(walker_edge_options()->getOptionValue('mobile_header_height') !== '') {
            $max_height = intval(walker_edge_filter_px(walker_edge_options()->getOptionValue('mobile_header_height'))).'px';
            echo walker_edge_dynamic_css('.edgtf-mobile-header .edgtf-mobile-logo-wrapper a', array('max-height' => $max_height));
        }
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_mobile_logo_styles');
}

if(!function_exists('walker_edge_mobile_icon_styles')) {
    /**
     * Generates styles for mobile icon opener
     */
    function walker_edge_mobile_icon_styles() {
        $mobile_icon_styles = array();
        if(walker_edge_options()->getOptionValue('mobile_icon_color') !== '') {
            $mobile_icon_styles['color'] = walker_edge_options()->getOptionValue('mobile_icon_color');
        }

        if(walker_edge_options()->getOptionValue('mobile_icon_size') !== '') {
            $mobile_icon_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('mobile_icon_size')).'px';
        }

        echo walker_edge_dynamic_css('.edgtf-mobile-header .edgtf-mobile-menu-opener a', $mobile_icon_styles);

        if(walker_edge_options()->getOptionValue('mobile_icon_hover_color') !== '') {
            echo walker_edge_dynamic_css(
                '.edgtf-mobile-header .edgtf-mobile-menu-opener a:hover',
                array('color' => walker_edge_options()->getOptionValue('mobile_icon_hover_color')));
        }
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_mobile_icon_styles');
}
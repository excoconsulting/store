<?php

if(!function_exists('walker_edge_header_top_bar_styles')) {
    /**
     * Generates styles for header top bar
     */
    function walker_edge_header_top_bar_styles() {
        global $walker_edge_options;

        if($walker_edge_options['top_bar_height'] !== '') {
            echo walker_edge_dynamic_css('.edgtf-top-bar', array('height' => walker_edge_filter_px($walker_edge_options['top_bar_height']).'px'));
            echo walker_edge_dynamic_css('.edgtf-top-bar .edgtf-logo-wrapper a', array('max-height' => walker_edge_filter_px($walker_edge_options['top_bar_height']).'px'));
        }

        $background_color = walker_edge_options()->getOptionValue('top_bar_background_color');
        $top_bar_styles = array();
        if($background_color !== '') {
            $background_transparency = 1;
            if(walker_edge_options()->getOptionValue('top_bar_background_transparency') !== '') {
               $background_transparency = walker_edge_options()->getOptionValue('top_bar_background_transparency');
            }

            $background_color = walker_edge_rgba_color($background_color, $background_transparency);
            $top_bar_styles['background-color'] = $background_color;
        }

        echo walker_edge_dynamic_css('.edgtf-top-bar', $top_bar_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_header_top_bar_styles');
}

if(!function_exists('walker_edge_header_standard_menu_area_styles')) {
    /**
     * Generates styles for header standard menu
     */
    function walker_edge_header_standard_menu_area_styles() {
        global $walker_edge_options;

        $holder_area_header_standard_styles = array();

        if($walker_edge_options['menu_area_background_color_header_standard'] !== '') {
            $menu_area_background_color        = $walker_edge_options['menu_area_background_color_header_standard'];
            $menu_area_background_transparency = 1;

            if($walker_edge_options['menu_area_background_transparency_header_standard'] !== '') {
                $menu_area_background_transparency = $walker_edge_options['menu_area_background_transparency_header_standard'];
            }

            $holder_area_header_standard_styles['background-color'] = walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency);
        }

        if($walker_edge_options['menu_area_background_color_header_standard'] === '' && $walker_edge_options['menu_area_background_transparency_header_standard'] !== '') {
            $menu_area_background_color        = '#fff';
            $menu_area_background_transparency = $walker_edge_options['menu_area_background_transparency_header_standard'];

            $holder_area_header_standard_styles['background-color'] = walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency);
        }

        if($walker_edge_options['menu_area_border_color_header_standard'] !== '') {
            $holder_area_header_standard_styles['border-bottom-color'] = walker_edge_options()->getOptionValue('menu_area_border_color_header_standard');
        }

        $holder_area_header_standard_selector = array(
            '.edgtf-header-standard .edgtf-page-header'
        );

        echo walker_edge_dynamic_css($holder_area_header_standard_selector, $holder_area_header_standard_styles);

        $menu_area_header_standard_styles = array();

        if($walker_edge_options['menu_area_height_header_standard'] !== '') {
            $max_height = intval(walker_edge_filter_px($walker_edge_options['menu_area_height_header_standard'])).'px';
            echo walker_edge_dynamic_css('.edgtf-header-standard .edgtf-page-header .edgtf-logo-wrapper a', array('max-height' => $max_height));

            $menu_area_header_standard_styles['height'] = walker_edge_filter_px($walker_edge_options['menu_area_height_header_standard']).'px';

        }

        echo walker_edge_dynamic_css('.edgtf-header-standard .edgtf-page-header .edgtf-menu-area', $menu_area_header_standard_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_header_standard_menu_area_styles');
}

if(!function_exists('walker_edge_header_simple_menu_area_styles')) {
    /**
     * Generates styles for header simple menu
     */
    function walker_edge_header_simple_menu_area_styles() {
        global $walker_edge_options;

        $holder_area_header_simple_styles = array();

        if($walker_edge_options['menu_area_background_color_header_simple'] !== '') {
            $menu_area_background_color        = $walker_edge_options['menu_area_background_color_header_simple'];
            $menu_area_background_transparency = 1;

            if($walker_edge_options['menu_area_background_transparency_header_simple'] !== '') {
                $menu_area_background_transparency = $walker_edge_options['menu_area_background_transparency_header_simple'];
            }

            $holder_area_header_simple_styles['background-color'] = walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency);
        }

        if($walker_edge_options['menu_area_background_color_header_simple'] === '' && $walker_edge_options['menu_area_background_transparency_header_simple'] !== '') {
            $menu_area_background_color        = '#fff';
            $menu_area_background_transparency = $walker_edge_options['menu_area_background_transparency_header_simple'];

            $holder_area_header_simple_styles['background-color'] = walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency);
        }

        if($walker_edge_options['menu_area_border_bottom_color_header_simple'] !== '') {
            $holder_area_header_simple_styles['border-bottom-color'] = $walker_edge_options['menu_area_border_bottom_color_header_simple'];
        }

        $holder_area_header_simple_selector = array(
            '.edgtf-header-simple .edgtf-page-header'
        );

        echo walker_edge_dynamic_css($holder_area_header_simple_selector, $holder_area_header_simple_styles);

        $menu_area_header_simple_styles = array();

        if($walker_edge_options['menu_area_height_header_simple'] !== '') {
            $max_height = intval(walker_edge_filter_px($walker_edge_options['menu_area_height_header_simple'])).'px';
            echo walker_edge_dynamic_css('.edgtf-header-simple .edgtf-page-header .edgtf-logo-wrapper a', array('max-height' => $max_height));

            $menu_area_header_simple_styles['height'] = walker_edge_filter_px($walker_edge_options['menu_area_height_header_simple']).'px';

        }

        echo walker_edge_dynamic_css('.edgtf-header-simple .edgtf-page-header .edgtf-menu-area', $menu_area_header_simple_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_header_simple_menu_area_styles');
}

if(!function_exists('walker_edge_header_classic_menu_area_styles')) {
    /**
     * Generates styles for header classic menu
     */
    function walker_edge_header_classic_menu_area_styles() {
        global $walker_edge_options;
        
        $holder_area_header_classic_styles = array();
        
        if($walker_edge_options['menu_area_background_color_header_classic'] !== '') {
            $menu_area_background_color        = $walker_edge_options['menu_area_background_color_header_classic'];
            $menu_area_background_transparency = 1;
            
            if($walker_edge_options['menu_area_background_transparency_header_classic'] !== '') {
                $menu_area_background_transparency = $walker_edge_options['menu_area_background_transparency_header_classic'];
            }
            
            $holder_area_header_classic_styles['background-color'] = walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency);
        }
        
        if($walker_edge_options['menu_area_background_color_header_classic'] === '' && $walker_edge_options['menu_area_background_transparency_header_classic'] !== '') {
            $menu_area_background_color        = '#fff';
            $menu_area_background_transparency = $walker_edge_options['menu_area_background_transparency_header_classic'];
            
            $holder_area_header_classic_styles['background-color'] = walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency);
        }
        
        if($walker_edge_options['menu_area_border_color_header_classic'] !== '') {
            $holder_area_header_classic_styles['border-bottom-color'] = walker_edge_options()->getOptionValue('menu_area_border_color_header_classic');
        }
        
        $holder_area_header_classic_selector = array(
            '.edgtf-header-classic .edgtf-page-header'
        );
        
        echo walker_edge_dynamic_css($holder_area_header_classic_selector, $holder_area_header_classic_styles);
        
        $logo_area_header_classic_styles = array();
        
        if($walker_edge_options['logo_area_height_header_classic'] !== '') {
            $max_height = intval(walker_edge_filter_px($walker_edge_options['logo_area_height_header_classic'])).'px';
            echo walker_edge_dynamic_css('.edgtf-header-classic .edgtf-page-header .edgtf-logo-wrapper a', array('max-height' => $max_height));
            
            $logo_area_header_classic_styles['height'] = walker_edge_filter_px($walker_edge_options['logo_area_height_header_classic']).'px';
        }
        
        echo walker_edge_dynamic_css('.edgtf-header-classic .edgtf-page-header .edgtf-logo-area', $logo_area_header_classic_styles);
        
        $menu_area_header_classic_styles = array();
        
        if($walker_edge_options['menu_area_height_header_classic'] !== '') {
            $menu_area_header_classic_styles['height'] = walker_edge_filter_px($walker_edge_options['menu_area_height_header_classic']).'px';
        }
        
        echo walker_edge_dynamic_css('.edgtf-header-classic .edgtf-page-header .edgtf-menu-area', $menu_area_header_classic_styles);
    }
    
    add_action('walker_edge_style_dynamic', 'walker_edge_header_classic_menu_area_styles');
}

if(!function_exists('walker_edge_header_classic_logo_area_styles')) {
    /**
     * Generates styles for classic header type logo element
     */
    function walker_edge_header_classic_logo_area_styles() {
        global $walker_edge_options;
        
        $logo_styles = array();
        
        if($walker_edge_options['logo_area_top_padding_header_classic'] !== '') {
            $logo_styles['padding-top'] = walker_edge_filter_px($walker_edge_options['logo_area_top_padding_header_classic']).'px';
        }
        
        $logo_styles_selector = array(
            '.edgtf-header-classic .edgtf-logo-area .edgtf-logo-wrapper'
        );
        
        echo walker_edge_dynamic_css($logo_styles_selector, $logo_styles);
    }
    
    add_action('walker_edge_style_dynamic', 'walker_edge_header_classic_logo_area_styles');
}

if(!function_exists('walker_edge_header_full_screen_menu_area_styles')) {
    /**
     * Generates styles for header full_screen menu
     */
    function walker_edge_header_full_screen_menu_area_styles() {
        global $walker_edge_options;

        $holder_area_header_full_screen_styles = array();

        if($walker_edge_options['menu_area_background_color_header_full_screen'] !== '') {
            $menu_area_background_color        = $walker_edge_options['menu_area_background_color_header_full_screen'];
            $menu_area_background_transparency = 1;

            if($walker_edge_options['menu_area_background_transparency_header_full_screen'] !== '') {
                $menu_area_background_transparency = $walker_edge_options['menu_area_background_transparency_header_full_screen'];
            }

            $holder_area_header_full_screen_styles['background-color'] = walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency);
        }

        if($walker_edge_options['menu_area_background_color_header_full_screen'] === '' && $walker_edge_options['menu_area_background_transparency_header_full_screen'] !== '') {
            $menu_area_background_color        = '#fff';
            $menu_area_background_transparency = $walker_edge_options['menu_area_background_transparency_header_full_screen'];

            $holder_area_header_full_screen_styles['background-color'] = walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency);
        }

        if($walker_edge_options['menu_area_border_bottom_color_header_full_screen'] !== '') {
            $holder_area_header_full_screen_styles['border-bottom-color'] = $walker_edge_options['menu_area_border_bottom_color_header_full_screen'];
        }

        $holder_area_header_full_screen_selector = array(
            '.edgtf-header-full-screen .edgtf-page-header'
        );

        echo walker_edge_dynamic_css($holder_area_header_full_screen_selector, $holder_area_header_full_screen_styles);

        $menu_area_header_full_screen_styles = array();

        if($walker_edge_options['menu_area_height_header_full_screen'] !== '') {
            $max_height = intval(walker_edge_filter_px($walker_edge_options['menu_area_height_header_full_screen'])).'px';
            echo walker_edge_dynamic_css('.edgtf-header-full-screen .edgtf-page-header .edgtf-logo-wrapper a', array('max-height' => $max_height));

            $menu_area_header_full_screen_styles['height'] = walker_edge_filter_px($walker_edge_options['menu_area_height_header_full_screen']).'px';

        }

        echo walker_edge_dynamic_css('.edgtf-header-full-screen .edgtf-page-header .edgtf-menu-area', $menu_area_header_full_screen_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_header_full_screen_menu_area_styles');
}

if(!function_exists('walker_edge_vertical_menu_styles')) {
    function walker_edge_vertical_menu_styles() {

        $vertical_header_styles = array();

        $vertical_header_selectors = array(
            '.edgtf-header-vertical .edgtf-vertical-area-background'
        );

        if(walker_edge_options()->getOptionValue('vertical_header_background_color') !== '') {
            $vertical_header_styles['background-color'] = walker_edge_options()->getOptionValue('vertical_header_background_color');
        }

        if(walker_edge_options()->getOptionValue('vertical_header_transparency') !== '') {
            $vertical_header_styles['opacity'] = walker_edge_options()->getOptionValue('vertical_header_transparency');
        }

        if(walker_edge_options()->getOptionValue('vertical_header_background_image') !== '') {
            $vertical_header_styles['background-image'] = 'url('.walker_edge_options()->getOptionValue('vertical_header_background_image').')';
        }

        echo walker_edge_dynamic_css($vertical_header_selectors, $vertical_header_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_vertical_menu_styles');
}

if(!function_exists('walker_edge_vertical_holder_styles')) {
    function walker_edge_vertical_holder_styles() {

        $vertical_header_styles = array();

        $vertical_header_selectors = array(
            '.edgtf-header-vertical .edgtf-vertical-menu-area-inner'
        );

        if(walker_edge_options()->getOptionValue('vertical_header_text_align') !== '') {
            $vertical_header_styles['text-align'] = walker_edge_options()->getOptionValue('vertical_header_text_align');
        }

        echo walker_edge_dynamic_css($vertical_header_selectors, $vertical_header_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_vertical_holder_styles');
}

if(!function_exists('walker_edge_header_bottom_menu_area_styles')) {
    /**
     * Generates styles for header bottom menu
     */
    function walker_edge_header_bottom_menu_area_styles() {
        global $walker_edge_options;

        $holder_area_header_bottom_styles = array();

        if($walker_edge_options['menu_area_background_color_header_bottom'] !== '') {
            $menu_area_background_color        = $walker_edge_options['menu_area_background_color_header_bottom'];
            $menu_area_background_transparency = 1;

            if($walker_edge_options['menu_area_background_transparency_header_bottom'] !== '') {
                $menu_area_background_transparency = $walker_edge_options['menu_area_background_transparency_header_bottom'];
            }

            $holder_area_header_bottom_styles['background-color'] = walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency);
        }

        if($walker_edge_options['menu_area_background_color_header_bottom'] === '' && $walker_edge_options['menu_area_background_transparency_header_bottom'] !== '') {
            $menu_area_background_color        = '#fff';
            $menu_area_background_transparency = $walker_edge_options['menu_area_background_transparency_header_bottom'];

            $holder_area_header_bottom_styles['background-color'] = walker_edge_rgba_color($menu_area_background_color, $menu_area_background_transparency);
        }

        if($walker_edge_options['menu_area_border_color_header_bottom'] !== '') {
            $holder_area_header_bottom_styles['border-bottom-color'] = walker_edge_options()->getOptionValue('menu_area_border_color_header_bottom');
        }

        $holder_area_header_bottom_selector = array(
            '.edgtf-header-bottom .edgtf-page-header'
        );

        echo walker_edge_dynamic_css($holder_area_header_bottom_selector, $holder_area_header_bottom_styles);

        $menu_area_header_bottom_styles = array();

        if($walker_edge_options['menu_area_height_header_bottom'] !== '') {
            $max_height = intval(walker_edge_filter_px($walker_edge_options['menu_area_height_header_bottom'])).'px';
            echo walker_edge_dynamic_css('.edgtf-header-bottom .edgtf-page-header .edgtf-logo-wrapper a', array('max-height' => $max_height));

            $menu_area_header_bottom_styles['height'] = walker_edge_filter_px($walker_edge_options['menu_area_height_header_bottom']).'px';

        }

        echo walker_edge_dynamic_css('.edgtf-header-bottom .edgtf-page-header .edgtf-menu-area', $menu_area_header_bottom_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_header_bottom_menu_area_styles');
}

if(!function_exists('walker_edge_sticky_header_styles')) {
    /**
     * Generates styles for sticky haeder
     */
    function walker_edge_sticky_header_styles() {
        global $walker_edge_options;

        if($walker_edge_options['sticky_header_background_color'] !== '') {

            $sticky_header_background_color              = $walker_edge_options['sticky_header_background_color'];
            $sticky_header_background_color_transparency = 1;

            if($walker_edge_options['sticky_header_transparency'] !== '') {
                $sticky_header_background_color_transparency = $walker_edge_options['sticky_header_transparency'];
            }

            echo walker_edge_dynamic_css('.edgtf-page-header .edgtf-sticky-header .edgtf-sticky-holder', array('background-color' => walker_edge_rgba_color($sticky_header_background_color, $sticky_header_background_color_transparency)));
        }

        if($walker_edge_options['sticky_header_border_color'] !== '') {

            $sticky_header_border_color = $walker_edge_options['sticky_header_border_color'];

            echo walker_edge_dynamic_css('.edgtf-page-header .edgtf-sticky-header .edgtf-sticky-holder', array('border-color' => $sticky_header_border_color));
        }

        if($walker_edge_options['sticky_header_height'] !== '') {
            $max_height = intval(walker_edge_filter_px($walker_edge_options['sticky_header_height'])).'px';

            echo walker_edge_dynamic_css('.edgtf-page-header .edgtf-sticky-header', array('height' => $walker_edge_options['sticky_header_height'].'px'));
            echo walker_edge_dynamic_css('.edgtf-page-header .edgtf-sticky-header .edgtf-logo-wrapper a', array('max-height' => $max_height));
        }

        $sticky_menu_item_styles = array();
        if($walker_edge_options['sticky_color'] !== '') {
            $sticky_menu_item_styles['color'] = $walker_edge_options['sticky_color'];
        }
        if($walker_edge_options['sticky_google_fonts'] !== '-1') {
            $sticky_menu_item_styles['font-family'] = walker_edge_get_formatted_font_family($walker_edge_options['sticky_google_fonts']);
        }
        if($walker_edge_options['sticky_fontsize'] !== '') {
            $sticky_menu_item_styles['font-size'] = walker_edge_filter_px($walker_edge_options['sticky_fontsize']).'px';
        }
        if($walker_edge_options['sticky_lineheight'] !== '') {
            $sticky_menu_item_styles['line-height'] = walker_edge_filter_px($walker_edge_options['sticky_lineheight']).'px';
        }
        if($walker_edge_options['sticky_texttransform'] !== '') {
            $sticky_menu_item_styles['text-transform'] = $walker_edge_options['sticky_texttransform'];
        }
        if($walker_edge_options['sticky_fontstyle'] !== '') {
            $sticky_menu_item_styles['font-style'] = $walker_edge_options['sticky_fontstyle'];
        }
        if($walker_edge_options['sticky_fontweight'] !== '') {
            $sticky_menu_item_styles['font-weight'] = $walker_edge_options['sticky_fontweight'];
        }
        if($walker_edge_options['sticky_letterspacing'] !== '') {
            $sticky_menu_item_styles['letter-spacing'] = walker_edge_filter_px($walker_edge_options['sticky_letterspacing']).'px';
        }

        $sticky_menu_item_selector = array(
            '.edgtf-main-menu.edgtf-sticky-nav > ul > li > a'
        );

        echo walker_edge_dynamic_css($sticky_menu_item_selector, $sticky_menu_item_styles);

        $sticky_menu_item_hover_styles = array();
        if($walker_edge_options['sticky_hovercolor'] !== '') {
            $sticky_menu_item_hover_styles['color'] = $walker_edge_options['sticky_hovercolor'];
        }

        $sticky_menu_item_hover_selector = array(
            '.edgtf-main-menu.edgtf-sticky-nav > ul > li:hover > a',
            '.edgtf-main-menu.edgtf-sticky-nav > ul > li.edgtf-active-item > a'
        );

        echo walker_edge_dynamic_css($sticky_menu_item_hover_selector, $sticky_menu_item_hover_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_sticky_header_styles');
}

if(!function_exists('walker_edge_fixed_header_styles')) {
    /**
     * Generates styles for fixed haeder
     */
    function walker_edge_fixed_header_styles() {
        global $walker_edge_options;

        $fixed_area_styles = array();

        if($walker_edge_options['fixed_header_background_color'] !== '') {
            $fixed_header_background_color        = $walker_edge_options['fixed_header_background_color'];
            $fixed_header_background_color_transparency = 1;

            if($walker_edge_options['fixed_header_transparency'] !== '') {
                $fixed_header_background_color_transparency = $walker_edge_options['fixed_header_transparency'];
            }

            $fixed_area_styles['background-color'] = walker_edge_rgba_color($fixed_header_background_color, $fixed_header_background_color_transparency) . '!important';
        }

        if($walker_edge_options['fixed_header_background_color'] === '' && $walker_edge_options['fixed_header_transparency'] !== '') {
            $fixed_header_background_color        = '#fff';
            $fixed_header_background_color_transparency = $walker_edge_options['fixed_header_transparency'];

            $fixed_area_styles['background-color'] = walker_edge_rgba_color($fixed_header_background_color, $fixed_header_background_color_transparency) . '!important';
        }

        $selector = array(
            '.edgtf-page-header .edgtf-fixed-wrapper.fixed .edgtf-menu-area'
        );

        echo walker_edge_dynamic_css($selector, $fixed_area_styles);

        $fixed_area_holder_styles = array();

        if($walker_edge_options['fixed_header_border_bottom_color'] !== '') {
            $fixed_area_holder_styles['border-bottom-color'] = $walker_edge_options['fixed_header_border_bottom_color'];
        }

        $selector_holder = array(
            '.edgtf-page-header .edgtf-fixed-wrapper.fixed'
        );

        echo walker_edge_dynamic_css($selector_holder, $fixed_area_holder_styles);

        $fixed_menu_item_styles = array();
        if($walker_edge_options['fixed_color'] !== '') {
            $fixed_menu_item_styles['color'] = $walker_edge_options['fixed_color'];
        }
        if($walker_edge_options['fixed_google_fonts'] !== '-1') {
            $fixed_menu_item_styles['font-family'] = walker_edge_get_formatted_font_family($walker_edge_options['fixed_google_fonts']);
        }
        if($walker_edge_options['fixed_fontsize'] !== '') {
            $fixed_menu_item_styles['font-size'] = walker_edge_filter_px($walker_edge_options['fixed_fontsize']).'px';
        }
        if($walker_edge_options['fixed_lineheight'] !== '') {
            $fixed_menu_item_styles['line-height'] = walker_edge_filter_px($walker_edge_options['fixed_lineheight']).'px';
        }
        if($walker_edge_options['fixed_texttransform'] !== '') {
            $fixed_menu_item_styles['text-transform'] = $walker_edge_options['fixed_texttransform'];
        }
        if($walker_edge_options['fixed_fontstyle'] !== '') {
            $fixed_menu_item_styles['font-style'] = $walker_edge_options['fixed_fontstyle'];
        }
        if($walker_edge_options['fixed_fontweight'] !== '') {
            $fixed_menu_item_styles['font-weight'] = $walker_edge_options['fixed_fontweight'];
        }
        if($walker_edge_options['fixed_letterspacing'] !== '') {
            $fixed_menu_item_styles['letter-spacing'] = walker_edge_filter_px($walker_edge_options['fixed_letterspacing']).'px';
        }

        $fixed_menu_item_selector = array(
            '.edgtf-fixed-wrapper.fixed .edgtf-main-menu > ul > li > a'
        );

        echo walker_edge_dynamic_css($fixed_menu_item_selector, $fixed_menu_item_styles);

        $fixed_menu_item_hover_styles = array();
        if($walker_edge_options['fixed_hovercolor'] !== '') {
            $fixed_menu_item_hover_styles['color'] = $walker_edge_options['fixed_hovercolor'];
        }

        $fixed_menu_item_hover_selector = array(
            '.edgtf-fixed-wrapper.fixed .edgtf-main-menu > ul > li:hover > a',
            '.edgtf-fixed-wrapper.fixed .edgtf-main-menu > ul > li.edgtf-active-item > a'
        );

        echo walker_edge_dynamic_css($fixed_menu_item_hover_selector, $fixed_menu_item_hover_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_fixed_header_styles');
}

if(!function_exists('walker_edge_main_menu_styles')) {
    /**
     * Generates styles for main menu
     */
    function walker_edge_main_menu_styles() {
        global $walker_edge_options;

        if($walker_edge_options['menu_color'] !== '' || $walker_edge_options['menu_fontsize'] !== '' || $walker_edge_options['menu_lineheight'] !== "" ||$walker_edge_options['menu_fontstyle'] !== '' || $walker_edge_options['menu_fontweight'] !== '' || $walker_edge_options['menu_texttransform'] !== '' || $walker_edge_options['menu_letterspacing'] !== '' || $walker_edge_options['menu_google_fonts'] != "-1") { ?>
            .edgtf-main-menu > ul > li > a {
            <?php if($walker_edge_options['menu_color']) { ?> color: <?php echo esc_attr($walker_edge_options['menu_color']); ?>; <?php } ?>
            <?php if($walker_edge_options['menu_google_fonts'] != "-1") { ?>
                font-family: '<?php echo esc_attr(str_replace('+', ' ', $walker_edge_options['menu_google_fonts'])); ?>', sans-serif;
            <?php } ?>
            <?php if($walker_edge_options['menu_fontsize'] !== '') { ?> font-size: <?php echo esc_attr($walker_edge_options['menu_fontsize']); ?>px; <?php } ?>
            <?php if($walker_edge_options['menu_lineheight'] !== '') { ?> line-height: <?php echo esc_attr($walker_edge_options['menu_lineheight']); ?>px; <?php } ?>
            <?php if($walker_edge_options['menu_fontstyle'] !== '') { ?> font-style: <?php echo esc_attr($walker_edge_options['menu_fontstyle']); ?>; <?php } ?>
            <?php if($walker_edge_options['menu_fontweight'] !== '') { ?> font-weight: <?php echo esc_attr($walker_edge_options['menu_fontweight']); ?>; <?php } ?>
            <?php if($walker_edge_options['menu_texttransform'] !== '') { ?> text-transform: <?php echo esc_attr($walker_edge_options['menu_texttransform']); ?>;  <?php } ?>
            <?php if($walker_edge_options['menu_letterspacing'] !== '') { ?> letter-spacing: <?php echo esc_attr($walker_edge_options['menu_letterspacing']); ?>px; <?php } ?>
            }
        <?php } ?>

        <?php if($walker_edge_options['menu_hovercolor'] !== '') { ?>
            .edgtf-main-menu > ul > li > a:hover,
            .edgtf-main-menu > ul > li.edgtf-active-item:hover > a {
                color: <?php echo esc_attr($walker_edge_options['menu_hovercolor']); ?> !important;
            }
        <?php } ?>

        <?php if($walker_edge_options['menu_activecolor'] !== '') { ?>
            .edgtf-main-menu > ul > li.edgtf-active-item > a {
                color: <?php echo esc_attr($walker_edge_options['menu_activecolor']); ?>;
            }
        <?php } ?>

        <?php if($walker_edge_options['menu_light_hovercolor'] !== '') { ?>
            .edgtf-light-header .edgtf-page-header > div:not(.edgtf-sticky-header):not(.edgtf-fixed-wrapper) .edgtf-main-menu > ul > li > a:hover,
            .edgtf-light-header .edgtf-page-header > div:not(.edgtf-sticky-header):not(.edgtf-fixed-wrapper) .edgtf-main-menu > ul > li.edgtf-active-item:hover > a {
                color: <?php echo esc_attr($walker_edge_options['menu_light_hovercolor']); ?> !important;
            }
        <?php } ?>

        <?php if($walker_edge_options['menu_light_activecolor'] !== '') { ?>
            .edgtf-light-header .edgtf-page-header > div:not(.edgtf-sticky-header):not(.edgtf-fixed-wrapper) .edgtf-main-menu > ul > li.edgtf-active-item > a {
                color: <?php echo esc_attr($walker_edge_options['menu_light_activecolor']); ?> !important;
            }
        <?php } ?>

        <?php if($walker_edge_options['menu_dark_hovercolor'] !== '') { ?>
            .edgtf-dark-header .edgtf-page-header > div:not(.edgtf-sticky-header):not(.edgtf-fixed-wrapper) .edgtf-main-menu > ul > li > a:hover,
            .edgtf-dark-header .edgtf-page-header > div:not(.edgtf-sticky-header):not(.edgtf-fixed-wrapper) .edgtf-main-menu > ul > li.edgtf-active-item:hover > a {
                color: <?php echo esc_attr($walker_edge_options['menu_dark_hovercolor']); ?> !important;
            }
        <?php } ?>

        <?php if($walker_edge_options['menu_dark_activecolor'] !== '') { ?>
            .edgtf-dark-header .edgtf-page-header > div:not(.edgtf-sticky-header):not(.edgtf-fixed-wrapper) .edgtf-main-menu > ul > li.edgtf-active-item > a {
                color: <?php echo esc_attr($walker_edge_options['menu_dark_activecolor']); ?>;
            }
        <?php } ?>

        <?php if( $walker_edge_options['menu_padding_left_right'] !== '') { ?>
            .edgtf-main-menu > ul > li > a > span.item_outer {
                padding: 0  <?php echo esc_attr($walker_edge_options['menu_padding_left_right']); ?>px;
            }
        <?php } ?>

        <?php if($walker_edge_options['menu_margin_left_right'] !== '') { ?>
            .edgtf-main-menu > ul > li > a {
                margin: 0  <?php echo esc_attr($walker_edge_options['menu_margin_left_right']); ?>px;
            }
        <?php } ?>

        <?php if($walker_edge_options['dropdown_top_position'] !== '') { ?>
            header .edgtf-drop-down .second {
                top: <?php echo esc_attr($walker_edge_options['dropdown_top_position']).'%;'; ?>
            }
        <?php } ?>

        <?php if($walker_edge_options['dropdown_color'] !== '' || $walker_edge_options['dropdown_fontsize'] !== '' || $walker_edge_options['dropdown_lineheight'] !== '' || $walker_edge_options['dropdown_fontstyle'] !== '' || $walker_edge_options['dropdown_fontweight'] !== '' || $walker_edge_options['dropdown_google_fonts'] != "-1" || $walker_edge_options['dropdown_texttransform'] !== '' || $walker_edge_options['dropdown_letterspacing'] !== '') { ?>
                .edgtf-drop-down .second .inner > ul > li > a{
                    <?php if(!empty($walker_edge_options['dropdown_color'])) { ?> color: <?php echo esc_attr($walker_edge_options['dropdown_color']); ?>; <?php } ?>
                    <?php if($walker_edge_options['dropdown_google_fonts'] != "-1") { ?>
                        font-family: '<?php echo esc_attr(str_replace('+', ' ', $walker_edge_options['dropdown_google_fonts'])); ?>', sans-serif !important;
                    <?php } ?>
                    <?php if($walker_edge_options['dropdown_fontsize'] !== '') { ?> font-size: <?php echo esc_attr($walker_edge_options['dropdown_fontsize']); ?>px; <?php } ?>
                    <?php if($walker_edge_options['dropdown_lineheight'] !== '') { ?> line-height: <?php echo esc_attr($walker_edge_options['dropdown_lineheight']); ?>px; <?php } ?>
                    <?php if($walker_edge_options['dropdown_fontstyle'] !== '') { ?> font-style: <?php echo esc_attr($walker_edge_options['dropdown_fontstyle']); ?>;  <?php } ?>
                    <?php if($walker_edge_options['dropdown_fontweight'] !== '') { ?>font-weight: <?php echo esc_attr($walker_edge_options['dropdown_fontweight']); ?>; <?php } ?>
                    <?php if($walker_edge_options['dropdown_texttransform'] !== '') { ?> text-transform: <?php echo esc_attr($walker_edge_options['dropdown_texttransform']); ?>;  <?php } ?>
                    <?php if($walker_edge_options['dropdown_letterspacing'] !== '') { ?> letter-spacing: <?php echo esc_attr($walker_edge_options['dropdown_letterspacing']); ?>px;  <?php } ?>
                }
        <?php } ?>

        <?php if(!empty($walker_edge_options['dropdown_hovercolor'])) { ?>
            .edgtf-drop-down .second .inner > ul > li > a:hover,
            .edgtf-drop-down .second .inner > ul > li.current-menu-ancestor > a,
            .edgtf-drop-down .second .inner > ul > li.current-menu-item > a {
                color: <?php echo esc_attr($walker_edge_options['dropdown_hovercolor']); ?> !important;
            }
        <?php } ?>

        <?php if($walker_edge_options['dropdown_wide_color'] !== '' || $walker_edge_options['dropdown_wide_fontsize'] !== '' || $walker_edge_options['dropdown_wide_lineheight'] !== '' || $walker_edge_options['dropdown_wide_fontstyle'] !== '' || $walker_edge_options['dropdown_wide_fontweight'] !== '' || $walker_edge_options['dropdown_wide_google_fonts'] !== "-1" || $walker_edge_options['dropdown_wide_texttransform'] !== '' || $walker_edge_options['dropdown_wide_letterspacing'] !== '') { ?>
            .edgtf-drop-down .wide .second .inner > ul > li > a {
            <?php if($walker_edge_options['dropdown_wide_color'] !== '') { ?> color: <?php echo esc_attr($walker_edge_options['dropdown_wide_color']); ?>; <?php } ?>
            <?php if($walker_edge_options['dropdown_wide_google_fonts'] != "-1") { ?>
                font-family: '<?php echo esc_attr(str_replace('+', ' ', $walker_edge_options['dropdown_wide_google_fonts'])); ?>', sans-serif !important;
            <?php } ?>
            <?php if($walker_edge_options['dropdown_wide_fontsize'] !== '') { ?> font-size: <?php echo esc_attr($walker_edge_options['dropdown_wide_fontsize']); ?>px; <?php } ?>
            <?php if($walker_edge_options['dropdown_wide_lineheight'] !== '') { ?> line-height: <?php echo esc_attr($walker_edge_options['dropdown_wide_lineheight']); ?>px; <?php } ?>
            <?php if($walker_edge_options['dropdown_wide_fontstyle'] !== '') { ?> font-style: <?php echo esc_attr($walker_edge_options['dropdown_wide_fontstyle']); ?>;  <?php } ?>
            <?php if($walker_edge_options['dropdown_wide_fontweight'] !== '') { ?>font-weight: <?php echo esc_attr($walker_edge_options['dropdown_wide_fontweight']); ?>; <?php } ?>
            <?php if($walker_edge_options['dropdown_wide_texttransform'] !== '') { ?> text-transform: <?php echo esc_attr($walker_edge_options['dropdown_wide_texttransform']); ?>;  <?php } ?>
            <?php if($walker_edge_options['dropdown_wide_letterspacing'] !== '') { ?> letter-spacing: <?php echo esc_attr($walker_edge_options['dropdown_wide_letterspacing']); ?>px;  <?php } ?>
            }
        <?php } ?>

        <?php if($walker_edge_options['dropdown_wide_hovercolor'] !== '') { ?>
            .edgtf-drop-down .wide .second .inner > ul > li > a:hover,
            .edgtf-drop-down .wide .second .inner > ul > li.current-menu-ancestor > a,
            .edgtf-drop-down .wide .second .inner > ul > li.current-menu-item > a {
                color: <?php echo esc_attr($walker_edge_options['dropdown_wide_hovercolor']); ?> !important;
            }
        <?php } ?>

        <?php if($walker_edge_options['dropdown_color_thirdlvl'] !== '' || $walker_edge_options['dropdown_fontsize_thirdlvl'] !== '' || $walker_edge_options['dropdown_lineheight_thirdlvl'] !== '' || $walker_edge_options['dropdown_fontstyle_thirdlvl'] !== '' || $walker_edge_options['dropdown_fontweight_thirdlvl'] !== '' || $walker_edge_options['dropdown_google_fonts_thirdlvl'] != "-1" || $walker_edge_options['dropdown_texttransform_thirdlvl'] !== '' || $walker_edge_options['dropdown_letterspacing_thirdlvl'] !== '') { ?>
            .edgtf-drop-down .second .inner ul li ul li a {
            <?php if($walker_edge_options['dropdown_color_thirdlvl'] !== '') { ?> color: <?php echo esc_attr($walker_edge_options['dropdown_color_thirdlvl']); ?>;  <?php } ?>
            <?php if($walker_edge_options['dropdown_google_fonts_thirdlvl'] != "-1") { ?>
                font-family: '<?php echo esc_attr(str_replace('+', ' ', $walker_edge_options['dropdown_google_fonts_thirdlvl'])); ?>', sans-serif;
            <?php } ?>
            <?php if($walker_edge_options['dropdown_fontsize_thirdlvl'] !== '') { ?> font-size: <?php echo esc_attr($walker_edge_options['dropdown_fontsize_thirdlvl']); ?>px;  <?php } ?>
            <?php if($walker_edge_options['dropdown_lineheight_thirdlvl'] !== '') { ?> line-height: <?php echo esc_attr($walker_edge_options['dropdown_lineheight_thirdlvl']); ?>px;  <?php } ?>
            <?php if($walker_edge_options['dropdown_fontstyle_thirdlvl'] !== '') { ?> font-style: <?php echo esc_attr($walker_edge_options['dropdown_fontstyle_thirdlvl']); ?>;   <?php } ?>
            <?php if($walker_edge_options['dropdown_fontweight_thirdlvl'] !== '') { ?> font-weight: <?php echo esc_attr($walker_edge_options['dropdown_fontweight_thirdlvl']); ?>;  <?php } ?>
            <?php if($walker_edge_options['dropdown_texttransform_thirdlvl'] !== '') { ?> text-transform: <?php echo esc_attr($walker_edge_options['dropdown_texttransform_thirdlvl']); ?>;  <?php } ?>
            <?php if($walker_edge_options['dropdown_letterspacing_thirdlvl'] !== '') { ?> letter-spacing: <?php echo esc_attr($walker_edge_options['dropdown_letterspacing_thirdlvl']); ?>px;  <?php } ?>
            }
        <?php } ?>
        
        <?php if($walker_edge_options['dropdown_hovercolor_thirdlvl'] !== '') { ?>
            .edgtf-drop-down .second .inner ul li ul li a:hover,
            .edgtf-drop-down .second .inner ul li ul li.current-menu-ancestor > a,
            .edgtf-drop-down .second .inner ul li ul li.current-menu-item > a {
                color: <?php echo esc_attr($walker_edge_options['dropdown_hovercolor_thirdlvl']); ?> !important;
            }
        <?php } ?>

        <?php if($walker_edge_options['dropdown_wide_color_thirdlvl'] !== '' || $walker_edge_options['dropdown_wide_fontsize_thirdlvl'] !== '' || $walker_edge_options['dropdown_wide_lineheight_thirdlvl'] !== '' || $walker_edge_options['dropdown_wide_fontstyle_thirdlvl'] !== '' || $walker_edge_options['dropdown_wide_fontweight_thirdlvl'] !== '' || $walker_edge_options['dropdown_wide_google_fonts_thirdlvl'] != "-1" || $walker_edge_options['dropdown_wide_texttransform_thirdlvl'] !== '' || $walker_edge_options['dropdown_wide_letterspacing_thirdlvl'] !== '') { ?>
            .edgtf-drop-down .wide .second .inner ul li ul li a {
            <?php if($walker_edge_options['dropdown_wide_color_thirdlvl'] !== '') { ?> color: <?php echo esc_attr($walker_edge_options['dropdown_wide_color_thirdlvl']); ?>;  <?php } ?>
            <?php if($walker_edge_options['dropdown_wide_google_fonts_thirdlvl'] != "-1") { ?>
                font-family: '<?php echo esc_attr(str_replace('+', ' ', $walker_edge_options['dropdown_wide_google_fonts_thirdlvl'])); ?>', sans-serif;
            <?php } ?>
            <?php if($walker_edge_options['dropdown_wide_fontsize_thirdlvl'] !== '') { ?> font-size: <?php echo esc_attr($walker_edge_options['dropdown_wide_fontsize_thirdlvl']); ?>px;  <?php } ?>
            <?php if($walker_edge_options['dropdown_wide_lineheight_thirdlvl'] !== '') { ?> line-height: <?php echo esc_attr($walker_edge_options['dropdown_wide_lineheight_thirdlvl']); ?>px;  <?php } ?>
            <?php if($walker_edge_options['dropdown_wide_fontstyle_thirdlvl'] !== '') { ?> font-style: <?php echo esc_attr($walker_edge_options['dropdown_wide_fontstyle_thirdlvl']); ?>;   <?php } ?>
            <?php if($walker_edge_options['dropdown_wide_fontweight_thirdlvl'] !== '') { ?> font-weight: <?php echo esc_attr($walker_edge_options['dropdown_wide_fontweight_thirdlvl']); ?>;  <?php } ?>
            <?php if($walker_edge_options['dropdown_wide_texttransform_thirdlvl'] !== '') { ?> text-transform: <?php echo esc_attr($walker_edge_options['dropdown_wide_texttransform_thirdlvl']); ?>;  <?php } ?>
            <?php if($walker_edge_options['dropdown_wide_letterspacing_thirdlvl'] !== '') { ?> letter-spacing: <?php echo esc_attr($walker_edge_options['dropdown_wide_letterspacing_thirdlvl']); ?>px;  <?php } ?>
            }
        <?php } ?>

        <?php if($walker_edge_options['dropdown_wide_hovercolor_thirdlvl'] !== '') { ?>
            .edgtf-drop-down .wide .second .inner ul li ul li a:hover,
            .edgtf-drop-down .wide .second .inner ul li ul li.current-menu-ancestor > a,
            .edgtf-drop-down .wide .second .inner ul li ul li.current-menu-item > a {
                color: <?php echo esc_attr($walker_edge_options['dropdown_wide_hovercolor_thirdlvl']); ?> !important;
            }
        <?php }
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_main_menu_styles');
}

if(!function_exists('walker_edge_vertical_main_menu_styles')) {
    /**
     * Generates styles for vertical main main menu
     */
    function walker_edge_vertical_main_menu_styles() {

        $menu_holder_styles = array();

        if(walker_edge_options()->getOptionValue('vertical_menu_top_margin') !== '') {
            $menu_holder_styles['margin-top'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('vertical_menu_top_margin')).'px';
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_bottom_margin') !== '') {
            $menu_holder_styles['margin-bottom'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('vertical_menu_bottom_margin')).'px';
        }
        
        $menu_holder_selector = array(
            '.edgtf-header-vertical .edgtf-vertical-menu'
        );

        echo walker_edge_dynamic_css($menu_holder_selector, $menu_holder_styles);

        $first_level_styles = array();
        $first_level_hover_styles = array();

        if(walker_edge_options()->getOptionValue('vertical_menu_1st_color') !== '') {
            $first_level_styles['color'] = walker_edge_options()->getOptionValue('vertical_menu_1st_color');
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_1st_google_fonts') !== '-1') {
            $first_level_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('vertical_menu_1st_google_fonts'));
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_1st_fontsize') !== '') {
            $first_level_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('vertical_menu_1st_fontsize')).'px';
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_1st_lineheight') !== '') {
            $first_level_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('vertical_menu_1st_lineheight')).'px';
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_1st_texttransform') !== '') {
            $first_level_styles['text-transform'] = walker_edge_options()->getOptionValue('vertical_menu_1st_texttransform');
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_1st_fontstyle') !== '') {
            $first_level_styles['font-style'] = walker_edge_options()->getOptionValue('vertical_menu_1st_fontstyle');
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_1st_fontweight') !== '') {
            $first_level_styles['font-weight'] = walker_edge_options()->getOptionValue('vertical_menu_1st_fontweight');
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_1st_letter_spacing') !== '') {
            $first_level_styles['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('vertical_menu_1st_letter_spacing')).'px';
        }

        if(walker_edge_options()->getOptionValue('vertical_menu_1st_hover_color') !== '') {
            $first_level_hover_styles['color'] = walker_edge_options()->getOptionValue('vertical_menu_1st_hover_color');
        }

        $first_level_selector = array(
            '.edgtf-header-vertical .edgtf-vertical-menu > ul > li > a'
        );
        $first_level_hover_selector = array(
            '.edgtf-header-vertical .edgtf-vertical-menu > ul > li > a:hover',
            '.edgtf-header-vertical .edgtf-vertical-menu > ul > li > a.edgtf-active-item',
            '.edgtf-header-vertical .edgtf-vertical-menu > ul > li > a.current-menu-ancestor'
        );

        echo walker_edge_dynamic_css($first_level_selector, $first_level_styles);
        echo walker_edge_dynamic_css($first_level_hover_selector, $first_level_hover_styles);

        $second_level_styles = array();
        $second_level_hover_styles = array();

        if(walker_edge_options()->getOptionValue('vertical_menu_2nd_color') !== '') {
            $second_level_styles['color'] = walker_edge_options()->getOptionValue('vertical_menu_2nd_color');
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_2nd_google_fonts') !== '-1') {
            $second_level_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('vertical_menu_2nd_google_fonts'));
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_2nd_fontsize') !== '') {
            $second_level_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('vertical_menu_2nd_fontsize')).'px';
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_2nd_lineheight') !== '') {
            $second_level_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('vertical_menu_2nd_lineheight')).'px';
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_2nd_texttransform') !== '') {
            $second_level_styles['text-transform'] = walker_edge_options()->getOptionValue('vertical_menu_2nd_texttransform');
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_2nd_fontstyle') !== '') {
            $second_level_styles['font-style'] = walker_edge_options()->getOptionValue('vertical_menu_2nd_fontstyle');
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_2nd_fontweight') !== '') {
            $second_level_styles['font-weight'] = walker_edge_options()->getOptionValue('vertical_menu_2nd_fontweight');
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_2nd_letter_spacing') !== '') {
            $second_level_styles['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('vertical_menu_2nd_letter_spacing')).'px';
        }

        if(walker_edge_options()->getOptionValue('vertical_menu_2nd_hover_color') !== '') {
            $second_level_hover_styles['color'] = walker_edge_options()->getOptionValue('vertical_menu_2nd_hover_color');
        }

        $second_level_selector = array(
            '.edgtf-header-vertical .edgtf-vertical-menu .second .inner > ul > li > a'
        );

        $second_level_hover_selector = array(
            '.edgtf-header-vertical .edgtf-vertical-menu .second .inner > ul > li > a:hover',
            '.edgtf-header-vertical .edgtf-vertical-menu .second .inner > ul > li.current_page_item > a',
            '.edgtf-header-vertical .edgtf-vertical-menu .second .inner > ul > li.current-menu-item > a',
            '.edgtf-header-vertical .edgtf-vertical-menu .second .inner > ul > li.current-menu-ancestor > a'
        );

        echo walker_edge_dynamic_css($second_level_selector, $second_level_styles);
        echo walker_edge_dynamic_css($second_level_hover_selector, $second_level_hover_styles);

        $third_level_styles = array();
        $third_level_hover_styles = array();

        if(walker_edge_options()->getOptionValue('vertical_menu_3rd_color') !== '') {
            $third_level_styles['color'] = walker_edge_options()->getOptionValue('vertical_menu_3rd_color');
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_3rd_google_fonts') !== '-1') {
            $third_level_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('vertical_menu_3rd_google_fonts'));
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_3rd_fontsize') !== '') {
            $third_level_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('vertical_menu_3rd_fontsize')).'px';
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_3rd_lineheight') !== '') {
            $third_level_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('vertical_menu_3rd_lineheight')).'px';
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_3rd_texttransform') !== '') {
            $third_level_styles['text-transform'] = walker_edge_options()->getOptionValue('vertical_menu_3rd_texttransform');
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_3rd_fontstyle') !== '') {
            $third_level_styles['font-style'] = walker_edge_options()->getOptionValue('vertical_menu_3rd_fontstyle');
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_3rd_fontweight') !== '') {
            $third_level_styles['font-weight'] = walker_edge_options()->getOptionValue('vertical_menu_3rd_fontweight');
        }
        if(walker_edge_options()->getOptionValue('vertical_menu_3rd_letter_spacing') !== '') {
            $third_level_styles['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('vertical_menu_3rd_letter_spacing')).'px';
        }

        if(walker_edge_options()->getOptionValue('vertical_menu_3rd_hover_color') !== '') {
            $third_level_hover_styles['color'] = walker_edge_options()->getOptionValue('vertical_menu_3rd_hover_color');
        }

        $third_level_selector = array(
            '.edgtf-header-vertical .edgtf-vertical-menu .second .inner ul li ul li a'
        );

        $third_level_hover_selector = array(
            '.edgtf-header-vertical .edgtf-vertical-menu .second .inner ul li ul li a:hover',
            '.edgtf-header-vertical .edgtf-vertical-menu .second .inner ul li ul li a.edgtf-active-item',
            '.edgtf-header-vertical .edgtf-vertical-menu .second .inner ul li ul li.current_page_item a',
            '.edgtf-header-vertical .edgtf-vertical-menu .second .inner ul li ul li.current-menu-item a'
        );

        echo walker_edge_dynamic_css($third_level_selector, $third_level_styles);
        echo walker_edge_dynamic_css($third_level_hover_selector, $third_level_hover_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_vertical_main_menu_styles');
}
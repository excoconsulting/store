<?php

if(!function_exists('walker_edge_404_footer_top_general_styles')) {
    /**
     * Generates general custom styles for footer top area
     */
    function walker_edge_404_footer_top_general_styles() {
        $item_styles = array();

        if(walker_edge_options()->getOptionValue('404_page_background_color')) {
            $item_styles['background-color'] = walker_edge_options()->getOptionValue('404_page_background_color');
        }

        if (walker_edge_options()->getOptionValue('404_page_background_image') !== '') {
            $item_styles['background-image'] = 'url('.walker_edge_options()->getOptionValue('404_page_background_image').')';
            $item_styles['background-position'] = 'center 0';
            $item_styles['background-size'] = 'cover';
            $item_styles['background-repeat'] = 'no-repeat';
        }

        if (walker_edge_options()->getOptionValue('404_page_background_pattern_image') !== '') {
            $item_styles['background-image'] = 'url('.walker_edge_options()->getOptionValue('404_page_background_pattern_image').')';
            $item_styles['background-position'] = '0 0';
            $item_styles['background-repeat'] = 'repeat';
        }

        echo walker_edge_dynamic_css('.edgtf-404-page .edgtf-content', $item_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_404_footer_top_general_styles');
}

if(!function_exists('walker_edge_404_title_styles')) {
    /**
     * Generates styles for 404 page title
     */
    function walker_edge_404_title_styles() {
        $item_styles = array();

        if(walker_edge_options()->getOptionValue('404_title_color') !== '') {
            $item_styles['color'] = walker_edge_options()->getOptionValue('404_title_color');
        }

        if(walker_edge_is_font_option_valid(walker_edge_options()->getOptionValue('404_title_google_fonts'))) {
            $item_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('404_title_google_fonts'));
        }

        if(walker_edge_options()->getOptionValue('404_title_fontsize') !== '') {
            $item_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('404_title_fontsize')).'px';
        }

        if(walker_edge_options()->getOptionValue('404_title_lineheight') !== '') {
            $item_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('404_title_lineheight')).'px';
        }

        if(walker_edge_options()->getOptionValue('404_title_fontstyle') !== '') {
            $item_styles['font-style'] = walker_edge_options()->getOptionValue('404_title_fontstyle');
        }

        if(walker_edge_options()->getOptionValue('404_title_fontweight') !== '') {
            $item_styles['font-weight'] = walker_edge_options()->getOptionValue('404_title_fontweight');
        }

        if(walker_edge_options()->getOptionValue('404_title_letterspacing') !== '') {
            $item_styles['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('404_title_letterspacing')).'px';
        }

        if(walker_edge_options()->getOptionValue('404_title_texttransform') !== '') {
            $item_styles['text-transform'] = walker_edge_options()->getOptionValue('404_title_texttransform');
        }

        $item_selector = array(
            '.edgtf-404-page .edgtf-page-not-found h1'
        );

        echo walker_edge_dynamic_css($item_selector, $item_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_404_title_styles');
}

if(!function_exists('walker_edge_404_subtitle_styles')) {
    /**
     * Generates styles for 404 page subtitle
     */
    function walker_edge_404_subtitle_styles() {
        $item_styles = array();

        if(walker_edge_options()->getOptionValue('404_subtitle_color') !== '') {
            $item_styles['color'] = walker_edge_options()->getOptionValue('404_subtitle_color');
        }

        if(walker_edge_is_font_option_valid(walker_edge_options()->getOptionValue('404_subtitle_google_fonts'))) {
            $item_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('404_subtitle_google_fonts'));
        }

        if(walker_edge_options()->getOptionValue('404_subtitle_fontsize') !== '') {
            $item_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('404_subtitle_fontsize')).'px';
        }

        if(walker_edge_options()->getOptionValue('404_subtitle_lineheight') !== '') {
            $item_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('404_subtitle_lineheight')).'px';
        }

        if(walker_edge_options()->getOptionValue('404_subtitle_fontstyle') !== '') {
            $item_styles['font-style'] = walker_edge_options()->getOptionValue('404_subtitle_fontstyle');
        }

        if(walker_edge_options()->getOptionValue('404_subtitle_fontweight') !== '') {
            $item_styles['font-weight'] = walker_edge_options()->getOptionValue('404_subtitle_fontweight');
        }

        if(walker_edge_options()->getOptionValue('404_subtitle_letterspacing') !== '') {
            $item_styles['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('404_subtitle_letterspacing')).'px';
        }

        if(walker_edge_options()->getOptionValue('404_subtitle_texttransform') !== '') {
            $item_styles['text-transform'] = walker_edge_options()->getOptionValue('404_subtitle_texttransform');
        }

        $item_selector = array(
            '.edgtf-404-page .edgtf-page-not-found h3'
        );

        echo walker_edge_dynamic_css($item_selector, $item_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_404_subtitle_styles');
}
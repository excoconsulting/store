<?php

if(!function_exists('walker_edge_footer_top_general_styles')) {
    /**
     * Generates general custom styles for footer top area
     */
    function walker_edge_footer_top_general_styles() {
        $item_styles = array();

        if(walker_edge_options()->getOptionValue('footer_top_background_color')) {
            $item_styles['background-color'] = walker_edge_options()->getOptionValue('footer_top_background_color');
        }

        echo walker_edge_dynamic_css('footer .edgtf-footer-top-holder', $item_styles);

        $item_inner_styles = array();

        if(walker_edge_options()->getOptionValue('footer_top_padding_top') !== '') {
            $item_inner_styles['padding-top'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('footer_top_padding_top')).'px';
        }

        if(walker_edge_options()->getOptionValue('footer_top_padding_bottom') !== '') {
            $item_inner_styles['padding-bottom'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('footer_top_padding_bottom')).'px';
        }

        $item_inner_selector = array(
            'footer .edgtf-footer-top:not(.edgtf-footer-top-full) .edgtf-container-inner',
            'footer .edgtf-footer-top.edgtf-footer-top-full'
        );

        echo walker_edge_dynamic_css($item_inner_selector, $item_inner_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_footer_top_general_styles');
}

if(!function_exists('walker_edge_footer_top_title_styles')) {
    /**
     * Generates styles for footer top widgets title
     */
    function walker_edge_footer_top_title_styles() {
        $item_styles = array();

        if(walker_edge_options()->getOptionValue('footer_title_color') !== '') {
            $item_styles['color'] = walker_edge_options()->getOptionValue('footer_title_color');
        }

        if(walker_edge_is_font_option_valid(walker_edge_options()->getOptionValue('footer_title_google_fonts'))) {
            $item_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('footer_title_google_fonts'));
        }

        if(walker_edge_options()->getOptionValue('footer_title_fontsize') !== '') {
            $item_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('footer_title_fontsize')).'px';
        }

        if(walker_edge_options()->getOptionValue('footer_title_lineheight') !== '') {
            $item_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('footer_title_lineheight')).'px';
        }

        if(walker_edge_options()->getOptionValue('footer_title_fontstyle') !== '') {
            $item_styles['font-style'] = walker_edge_options()->getOptionValue('footer_title_fontstyle');
        }

        if(walker_edge_options()->getOptionValue('footer_title_fontweight') !== '') {
            $item_styles['font-weight'] = walker_edge_options()->getOptionValue('footer_title_fontweight');
        }

        if(walker_edge_options()->getOptionValue('footer_title_letterspacing') !== '') {
            $item_styles['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('footer_title_letterspacing')).'px';
        }

        if(walker_edge_options()->getOptionValue('footer_title_texttransform') !== '') {
            $item_styles['text-transform'] = walker_edge_options()->getOptionValue('footer_title_texttransform');
        }

        $item_selector = array(
            '.edgtf-footer-top-holder .widget > .edgtf-footer-widget-title'
        );

        echo walker_edge_dynamic_css($item_selector, $item_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_footer_top_title_styles');
}

if(!function_exists('walker_edge_footer_bottom_general_styles')) {
    /**
     * Generates general custom styles for footer bottom area
     */
    function walker_edge_footer_bottom_general_styles() {
        $item_styles = array();
        if(walker_edge_options()->getOptionValue('footer_bottom_height') !== '') {
            $item_styles['height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('footer_bottom_height')).'px';
        }

        if(walker_edge_options()->getOptionValue('footer_bottom_background_color')) {
            $item_styles['background-color'] = walker_edge_options()->getOptionValue('footer_bottom_background_color');
        }

        if(walker_edge_options()->getOptionValue('footer_bottom_border_top_color')) {
            $item_styles['border'] = '1px solid '.walker_edge_options()->getOptionValue('footer_bottom_border_top_color');
        }

        echo walker_edge_dynamic_css('footer .edgtf-footer-bottom-holder', $item_styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_footer_bottom_general_styles');
}
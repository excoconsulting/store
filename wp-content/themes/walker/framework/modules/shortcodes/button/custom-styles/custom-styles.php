<?php

if(!function_exists('walker_edge_button_typography_styles')) {
    /**
     * Typography styles for all button types
     */
    function walker_edge_button_typography_styles() {
        $selector = '.edgtf-btn';
        $styles = array();

        $font_family = walker_edge_options()->getOptionValue('button_font_family');
        if(walker_edge_is_font_option_valid($font_family)) {
            $styles['font-family'] = walker_edge_get_font_option_val($font_family);
        }

        $text_transform = walker_edge_options()->getOptionValue('button_text_transform');
        if(!empty($text_transform)) {
            $styles['text-transform'] = $text_transform;
        }

        $font_style = walker_edge_options()->getOptionValue('button_font_style');
        if(!empty($font_style)) {
            $styles['font-style'] = $font_style;
        }

        $letter_spacing = walker_edge_options()->getOptionValue('button_letter_spacing');
        if($letter_spacing !== '') {
            $styles['letter-spacing'] = walker_edge_filter_px($letter_spacing).'px';
        }

        $font_weight = walker_edge_options()->getOptionValue('button_font_weight');
        if(!empty($font_weight)) {
            $styles['font-weight'] = $font_weight;
        }

        echo walker_edge_dynamic_css($selector, $styles);
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_button_typography_styles');
}

if(!function_exists('walker_edge_button_outline_styles')) {
    /**
     * Generate styles for outline button
     */
    function walker_edge_button_outline_styles() {
        //outline styles
        $outline_styles   = array();
        $outline_selector = '.edgtf-btn.edgtf-btn-outline';

        if(walker_edge_options()->getOptionValue('btn_outline_text_color')) {
            $outline_styles['color'] = walker_edge_options()->getOptionValue('btn_outline_text_color');
        }

        if(walker_edge_options()->getOptionValue('btn_outline_border_color')) {
            $outline_styles['border-color'] = walker_edge_options()->getOptionValue('btn_outline_border_color');
        }

        echo walker_edge_dynamic_css($outline_selector, $outline_styles);

        //outline hover styles
        if(walker_edge_options()->getOptionValue('btn_outline_hover_text_color')) {
            echo walker_edge_dynamic_css(
                '.edgtf-btn.edgtf-btn-outline:not(.edgtf-btn-custom-hover-color):hover',
                array('color' => walker_edge_options()->getOptionValue('btn_outline_hover_text_color').'!important')
            );
        }

        if(walker_edge_options()->getOptionValue('btn_outline_hover_bg_color')) {
            echo walker_edge_dynamic_css(
                '.edgtf-btn.edgtf-btn-outline:not(.edgtf-btn-custom-hover-bg):hover',
                array('background-color' => walker_edge_options()->getOptionValue('btn_outline_hover_bg_color').'!important')
            );
        }

        if(walker_edge_options()->getOptionValue('btn_outline_hover_border_color')) {
            echo walker_edge_dynamic_css(
                '.edgtf-btn.edgtf-btn-outline:not(.edgtf-btn-custom-border-hover):hover',
                array('border-color' => walker_edge_options()->getOptionValue('btn_outline_hover_border_color').'!important')
            );
        }
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_button_outline_styles');
}

if(!function_exists('walker_edge_button_solid_styles')) {
    /**
     * Generate styles for solid type buttons
     */
    function walker_edge_button_solid_styles() {
        //solid styles
        $solid_selector = '.edgtf-btn.edgtf-btn-solid';
        $solid_styles = array();

        if(walker_edge_options()->getOptionValue('btn_solid_text_color')) {
            $solid_styles['color'] = walker_edge_options()->getOptionValue('btn_solid_text_color');
        }

        if(walker_edge_options()->getOptionValue('btn_solid_bg_color')) {
            $solid_styles['background-color'] = walker_edge_options()->getOptionValue('btn_solid_bg_color');
        }

        if(walker_edge_options()->getOptionValue('btn_solid_border_color')) {
            $solid_styles['border-color'] = walker_edge_options()->getOptionValue('btn_solid_border_color');
        }

        echo walker_edge_dynamic_css($solid_selector, $solid_styles);

        //solid hover styles
        if(walker_edge_options()->getOptionValue('btn_solid_hover_text_color')) {
            echo walker_edge_dynamic_css(
                '.edgtf-btn.edgtf-btn-solid:not(.edgtf-btn-custom-hover-color):hover',
                array('color' => walker_edge_options()->getOptionValue('btn_solid_hover_text_color').'!important')
            );
        }

        if(walker_edge_options()->getOptionValue('btn_solid_hover_bg_color')) {
            echo walker_edge_dynamic_css(
                '.edgtf-btn.edgtf-btn-solid:not(.edgtf-btn-custom-hover-bg):hover',
                array('background-color' => walker_edge_options()->getOptionValue('btn_solid_hover_bg_color').'!important')
            );
        }

        if(walker_edge_options()->getOptionValue('btn_solid_hover_border_color')) {
            echo walker_edge_dynamic_css(
                '.edgtf-btn.edgtf-btn-solid:not(.edgtf-btn-custom-hover-bg):hover',
                array('border-color' => walker_edge_options()->getOptionValue('btn_solid_hover_border_color').'!important')
            );
        }
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_button_solid_styles');
}

if(!function_exists('walker_edge_button_simple_styles')) {
    /**
     * Generate styles for simple type buttons
     */
    function walker_edge_button_simple_styles() {
        //simple styles
        $simple_selector = '.edgtf-btn.edgtf-btn-simple';
        $simple_styles = array();

        if(walker_edge_options()->getOptionValue('btn_simple_text_color')) {
            $simple_styles['color'] = walker_edge_options()->getOptionValue('btn_simple_text_color');
        }

        echo walker_edge_dynamic_css($simple_selector, $simple_styles);

        //simple hover styles
        if(walker_edge_options()->getOptionValue('btn_simple_hover_text_color')) {
            echo walker_edge_dynamic_css(
                '.edgtf-btn.edgtf-btn-simple:not(.edgtf-btn-custom-hover-color):hover',
                array('color' => walker_edge_options()->getOptionValue('btn_simple_hover_text_color').'!important')
            );
        }
    }

    add_action('walker_edge_style_dynamic', 'walker_edge_button_simple_styles');
}
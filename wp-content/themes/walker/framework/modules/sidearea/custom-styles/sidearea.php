<?php

if (!function_exists('walker_edge_side_area_slide_from_right_type_style')) {

	function walker_edge_side_area_slide_from_right_type_style()	{

		if (walker_edge_options()->getOptionValue('side_area_width') !== '') {
			echo walker_edge_dynamic_css('.edgtf-side-menu-slide-from-right .edgtf-side-menu', array(
				'right' => '-'.walker_edge_filter_px(walker_edge_options()->getOptionValue('side_area_width')) . 'px',
				'width' => walker_edge_filter_px(walker_edge_options()->getOptionValue('side_area_width')) . 'px'
			));
		}
	}

	add_action('walker_edge_style_dynamic', 'walker_edge_side_area_slide_from_right_type_style');
}

if (!function_exists('walker_edge_side_area_icon_color_styles')) {

	function walker_edge_side_area_icon_color_styles() {

		if (walker_edge_options()->getOptionValue('side_area_icon_color') !== '') {

			echo walker_edge_dynamic_css('a.edgtf-side-menu-button-opener .edgtf-side-menu-lines .edgtf-side-menu-line', array(
				'background-color' => walker_edge_options()->getOptionValue('side_area_icon_color')
			));
		}

		if (walker_edge_options()->getOptionValue('side_area_icon_hover_color') !== '') {

			echo walker_edge_dynamic_css('a.edgtf-side-menu-button-opener:hover .edgtf-side-menu-lines .edgtf-side-menu-line', array(
				'background-color' => walker_edge_options()->getOptionValue('side_area_icon_hover_color') . '!important'
			));
		}

		if (walker_edge_options()->getOptionValue('side_area_light_icon_color') !== '') {

			echo walker_edge_dynamic_css('.edgtf-light-header .edgtf-page-header > div:not(.edgtf-sticky-header):not(.fixed) .edgtf-side-menu-button-opener .edgtf-side-menu-lines .edgtf-side-menu-line,
			.edgtf-light-header.edgtf-header-style-on-scroll .edgtf-page-header .edgtf-side-menu-button-opener .edgtf-side-menu-lines .edgtf-side-menu-line,
			.edgtf-light-header .edgtf-top-bar .edgtf-side-menu-button-opener .edgtf-side-menu-lines .edgtf-side-menu-line', array(
				'background-color' => walker_edge_options()->getOptionValue('side_area_light_icon_color') . ' !important'
			));
		}

		if (walker_edge_options()->getOptionValue('side_area_light_icon_hover_color') !== '') {

			echo walker_edge_dynamic_css('.edgtf-light-header .edgtf-page-header > div:not(.edgtf-sticky-header):not(.fixed) .edgtf-side-menu-button-opener:hover .edgt-side-menu-lines .edgtf-side-menu-line,
			.edgtf-light-header.edgtf-header-style-on-scroll .edgtf-page-header .edgtf-side-menu-button-opener:hover .edgt-side-menu-lines .edgtf-side-menu-line,
			.edgtf-light-header .edgtf-top-bar .edgtf-side-menu-button-opener:hover .edgt-side-menu-lines .edgtf-side-menu-line', array(
				'background-color' => walker_edge_options()->getOptionValue('side_area_light_icon_hover_color') . ' !important'
			));
		}

		if (walker_edge_options()->getOptionValue('side_area_dark_icon_color') !== '') {

			echo walker_edge_dynamic_css('.edgtf-dark-header .edgtf-page-header > div:not(.edgtf-sticky-header):not(.fixed) .edgtf-side-menu-button-opener .edgtf-side-menu-lines .edgtf-side-menu-line,
			.edgtf-dark-header.edgtf-header-style-on-scroll .edgtf-page-header .edgtf-side-menu-button-opener .edgtf-side-menu-lines .edgtf-side-menu-line,
			.edgtf-dark-header .edgtf-top-bar .edgtf-side-menu-button-opener .edgtf-side-menu-lines .edgtf-side-menu-line', array(
				'background-color' => walker_edge_options()->getOptionValue('side_area_dark_icon_color') . ' !important'
			));
		}

		if (walker_edge_options()->getOptionValue('side_area_dark_icon_hover_color') !== '') {

			echo walker_edge_dynamic_css('.edgtf-dark-header .edgtf-page-header > div:not(.edgtf-sticky-header):not(.fixed) .edgtf-side-menu-button-opener:hover .edgtf-side-menu-lines .edgtf-side-menu-line,
			.edgtf-dark-header.edgtf-header-style-on-scroll .edgtf-page-header .edgtf-side-menu-button-opener:hover .edgtf-side-menu-lines .edgtf-side-menu-line,
			.edgtf-dark-header .edgtf-top-bar .edgtf-side-menu-button-opener:hover .edgtf-side-menu-lines .edgtf-side-menu-line', array(
				'background-color' => walker_edge_options()->getOptionValue('side_area_dark_icon_hover_color') . ' !important'
			));
		}

		if (walker_edge_options()->getOptionValue('side_area_close_icon_color') !== '') {

			echo walker_edge_dynamic_css('.edgtf-side-menu a.edgtf-close-side-menu .edgtf-side-menu-lines .edgtf-side-menu-line', array(
				'background-color' => walker_edge_options()->getOptionValue('side_area_close_icon_color')
			));
		}

		if (walker_edge_options()->getOptionValue('side_area_close_icon_hover_color') !== '') {

			echo walker_edge_dynamic_css('.edgtf-side-menu a.edgtf-close-side-menu:hover .edgtf-side-menu-lines .edgtf-side-menu-line', array(
				'background-color' => walker_edge_options()->getOptionValue('side_area_close_icon_hover_color')
			));
		}
	}

	add_action('walker_edge_style_dynamic', 'walker_edge_side_area_icon_color_styles');
}

if (!function_exists('walker_edge_side_area_icon_spacing_styles')) {

	function walker_edge_side_area_icon_spacing_styles()	{
		$icon_spacing = array();

		if (walker_edge_options()->getOptionValue('side_area_icon_padding_left') !== '') {
			$icon_spacing['padding-left'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('side_area_icon_padding_left')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('side_area_icon_padding_right') !== '') {
			$icon_spacing['padding-right'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('side_area_icon_padding_right')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('side_area_icon_margin_left') !== '') {
			$icon_spacing['margin-left'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('side_area_icon_margin_left')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('side_area_icon_margin_right') !== '') {
			$icon_spacing['margin-right'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('side_area_icon_margin_right')) . 'px';
		}

		if (!empty($icon_spacing)) {

			echo walker_edge_dynamic_css('a.edgtf-side-menu-button-opener', $icon_spacing);
		}
	}

	add_action('walker_edge_style_dynamic', 'walker_edge_side_area_icon_spacing_styles');
}

if (!function_exists('walker_edge_side_area_alignment')) {

	function walker_edge_side_area_alignment() {

		if (walker_edge_options()->getOptionValue('side_area_aligment')) {

			echo walker_edge_dynamic_css('.edgtf-side-menu-slide-from-right .edgtf-side-menu', array(
				'text-align' => walker_edge_options()->getOptionValue('side_area_aligment')
			));

			if(walker_edge_options()->getOptionValue('side_area_aligment') == 'center') {
				echo walker_edge_dynamic_css('.edgtf-side-menu .widget img', array(
					'margin' => '0 auto'
				));
			}
		}
	}

	add_action('walker_edge_style_dynamic', 'walker_edge_side_area_alignment');
}

if (!function_exists('walker_edge_side_area_styles')) {

	function walker_edge_side_area_styles() {

		$side_area_styles = array();

		if (walker_edge_options()->getOptionValue('side_area_background_color') !== '') {
			$side_area_styles['background-color'] = walker_edge_options()->getOptionValue('side_area_background_color');
		}

		if (walker_edge_options()->getOptionValue('side_area_padding_top') !== '') {
			$side_area_styles['padding-top'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('side_area_padding_top')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('side_area_padding_right') !== '') {
			$side_area_styles['padding-right'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('side_area_padding_right')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('side_area_padding_bottom') !== '') {
			$side_area_styles['padding-bottom'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('side_area_padding_bottom')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('side_area_padding_left') !== '') {
			$side_area_styles['padding-left'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('side_area_padding_left')) . 'px';
		}

		if (!empty($side_area_styles)) {
			echo walker_edge_dynamic_css('.edgtf-side-menu', $side_area_styles);
		}
	}

	add_action('walker_edge_style_dynamic', 'walker_edge_side_area_styles');
}

if (!function_exists('walker_edge_side_area_title_styles')) {

	function walker_edge_side_area_title_styles() {

		$title_styles = array();

		if (walker_edge_options()->getOptionValue('side_area_title_color') !== '') {
			$title_styles['color'] = walker_edge_options()->getOptionValue('side_area_title_color');
		}

		if (walker_edge_options()->getOptionValue('side_area_title_fontsize') !== '') {
			$title_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('side_area_title_fontsize')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('side_area_title_lineheight') !== '') {
			$title_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('side_area_title_lineheight')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('side_area_title_texttransform') !== '') {
			$title_styles['text-transform'] = walker_edge_options()->getOptionValue('side_area_title_texttransform');
		}

		if (walker_edge_options()->getOptionValue('side_area_title_google_fonts') !== '-1') {
			$title_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('side_area_title_google_fonts')) . ', sans-serif';
		}

		if (walker_edge_options()->getOptionValue('side_area_title_fontstyle') !== '') {
			$title_styles['font-style'] = walker_edge_options()->getOptionValue('side_area_title_fontstyle');
		}

		if (walker_edge_options()->getOptionValue('side_area_title_fontweight') !== '') {
			$title_styles['font-weight'] = walker_edge_options()->getOptionValue('side_area_title_fontweight');
		}

		if (walker_edge_options()->getOptionValue('side_area_title_letterspacing') !== '') {
			$title_styles['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('side_area_title_letterspacing')) . 'px';
		}

		if (!empty($title_styles)) {

			echo walker_edge_dynamic_css('.edgtf-side-menu .widget .edgtf-sidearea-widget-title', $title_styles);
		}
	}

	add_action('walker_edge_style_dynamic', 'walker_edge_side_area_title_styles');
}

if (!function_exists('walker_edge_side_area_text_styles')) {

	function walker_edge_side_area_text_styles() {
		$text_styles = array();

		if (walker_edge_options()->getOptionValue('side_area_text_google_fonts') !== '-1') {
			$text_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('side_area_text_google_fonts')) . ', sans-serif';
		}

		if (walker_edge_options()->getOptionValue('side_area_text_fontsize') !== '') {
			$text_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('side_area_text_fontsize')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('side_area_text_lineheight') !== '') {
			$text_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('side_area_text_lineheight')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('side_area_text_letterspacing') !== '') {
			$text_styles['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('side_area_text_letterspacing')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('side_area_text_fontweight') !== '') {
			$text_styles['font-weight'] = walker_edge_options()->getOptionValue('side_area_text_fontweight');
		}

		if (walker_edge_options()->getOptionValue('side_area_text_fontstyle') !== '') {
			$text_styles['font-style'] = walker_edge_options()->getOptionValue('side_area_text_fontstyle');
		}

		if (walker_edge_options()->getOptionValue('side_area_text_texttransform') !== '') {
			$text_styles['text-transform'] = walker_edge_options()->getOptionValue('side_area_text_texttransform');
		}

		if (walker_edge_options()->getOptionValue('side_area_text_color') !== '') {
			$text_styles['color'] = walker_edge_options()->getOptionValue('side_area_text_color');
		}

		if (!empty($text_styles)) {

			echo walker_edge_dynamic_css('.edgtf-side-menu .widget, .edgtf-side-menu .widget.widget_search form, .edgtf-side-menu .widget.widget_search form input[type="text"], .edgtf-side-menu .widget.widget_search form input[type="submit"], .edgtf-side-menu .widget h6, .edgtf-side-menu .widget h6 a, .edgtf-side-menu .widget p, .edgtf-side-menu .widget li a, .edgtf-side-menu .widget.widget_rss li a.rsswidget, .edgtf-side-menu #wp-calendar caption,.edgtf-side-menu .widget li, .edgtf-side-menu h3, .edgtf-side-menu .widget.widget_archive select, .edgtf-side-menu .widget.widget_categories select, .edgtf-side-menu .widget.widget_text select, .edgtf-side-menu .widget.widget_search form input[type="submit"], .edgtf-side-menu #wp-calendar th, .edgtf-side-menu #wp-calendar td, .edgtf-side-menu .q_social_icon_holder i.simple_social', $text_styles);

		}
	}

	add_action('walker_edge_style_dynamic', 'walker_edge_side_area_text_styles');
}

if (!function_exists('walker_edge_side_area_link_styles')) {

	function walker_edge_side_area_link_styles()	{
		$link_styles = array();

		if (walker_edge_options()->getOptionValue('sidearea_link_font_family') !== '-1') {
			$link_styles['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('sidearea_link_font_family')) . ',sans-serif';
		}

		if (walker_edge_options()->getOptionValue('sidearea_link_font_size') !== '') {
			$link_styles['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('sidearea_link_font_size')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('sidearea_link_line_height') !== '') {
			$link_styles['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('sidearea_link_line_height')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('sidearea_link_letter_spacing') !== '') {
			$link_styles['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('sidearea_link_letter_spacing')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('sidearea_link_font_weight') !== '') {
			$link_styles['font-weight'] = walker_edge_options()->getOptionValue('sidearea_link_font_weight');
		}

		if (walker_edge_options()->getOptionValue('sidearea_link_font_style') !== '') {
			$link_styles['font-style'] = walker_edge_options()->getOptionValue('sidearea_link_font_style');
		}

		if (walker_edge_options()->getOptionValue('sidearea_link_text_transform') !== '') {
			$link_styles['text-transform'] = walker_edge_options()->getOptionValue('sidearea_link_text_transform');
		}

		if (walker_edge_options()->getOptionValue('sidearea_link_color') !== '') {
			$link_styles['color'] = walker_edge_options()->getOptionValue('sidearea_link_color');
		}

		if (!empty($link_styles)) {

			echo walker_edge_dynamic_css('.edgtf-side-menu .widget li a, .edgtf-side-menu .widget a:not(.qbutton)', $link_styles);
		}

		if (walker_edge_options()->getOptionValue('sidearea_link_hover_color') !== '') {
			echo walker_edge_dynamic_css('.edgtf-side-menu .widget a:not(.qbutton):hover', array(
				'color' => walker_edge_options()->getOptionValue('sidearea_link_hover_color')
			));
		}
	}

	add_action('walker_edge_style_dynamic', 'walker_edge_side_area_link_styles');
}
<?php

if (!function_exists('walker_edge_fullscreen_menu_general_styles')) {

	function walker_edge_fullscreen_menu_general_styles()
	{
		$fullscreen_menu_background_color = '';
		if (walker_edge_options()->getOptionValue('fullscreen_alignment') !== '') {
			echo walker_edge_dynamic_css('nav.edgtf-fullscreen-menu ul li, .edgtf-fullscreen-above-menu-widget-holder, .edgtf-fullscreen-below-menu-widget-holder', array(
				'text-align' => walker_edge_options()->getOptionValue('fullscreen_alignment')
			));
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_background_color') !== '') {
			$fullscreen_menu_background_color = walker_edge_hex2rgb(walker_edge_options()->getOptionValue('fullscreen_menu_background_color'));
			if (walker_edge_options()->getOptionValue('fullscreen_menu_background_transparency') !== '') {
				$fullscreen_menu_background_transparency = walker_edge_options()->getOptionValue('fullscreen_menu_background_transparency');
			} else {
				$fullscreen_menu_background_transparency = 0.9;
			}
		}

		if ($fullscreen_menu_background_color !== '') {
			echo walker_edge_dynamic_css('.edgtf-fullscreen-menu-holder', array(
				'background-color' => 'rgba(' . $fullscreen_menu_background_color[0] . ',' . $fullscreen_menu_background_color[1] . ',' . $fullscreen_menu_background_color[2] . ',' . $fullscreen_menu_background_transparency . ')'
			));
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_background_image') !== '') {
			echo walker_edge_dynamic_css('.edgtf-fullscreen-menu-holder', array(
				'background-image' => 'url(' . walker_edge_options()->getOptionValue('fullscreen_menu_background_image') . ')',
				'background-position' => 'center 0',
				'background-repeat' => 'no-repeat'
			));
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_pattern_image') !== '') {
			echo walker_edge_dynamic_css('.edgtf-fullscreen-menu-holder', array(
				'background-image' => 'url(' . walker_edge_options()->getOptionValue('fullscreen_menu_pattern_image') . ')',
				'background-repeat' => 'repeat',
				'background-position' => '0 0'
			));
		}
	}

	add_action('walker_edge_style_dynamic', 'walker_edge_fullscreen_menu_general_styles');
}

if (!function_exists('walker_edge_fullscreen_menu_first_level_style')) {

	function walker_edge_fullscreen_menu_first_level_style()	{

		$first_menu_style = array();

		if (walker_edge_options()->getOptionValue('fullscreen_menu_color') !== '') {
			$first_menu_style['color'] = walker_edge_options()->getOptionValue('fullscreen_menu_color');
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_google_fonts') !== '-1') {
			$first_menu_style['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('fullscreen_menu_google_fonts')) . ',sans-serif';
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_fontsize') !== '') {
			$first_menu_style['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('fullscreen_menu_fontsize')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_lineheight') !== '') {
			$first_menu_style['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('fullscreen_menu_lineheight')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_fontstyle') !== '') {
			$first_menu_style['font-style'] = walker_edge_options()->getOptionValue('fullscreen_menu_fontstyle');
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_fontweight') !== '') {
			$first_menu_style['font-weight'] = walker_edge_options()->getOptionValue('fullscreen_menu_fontweight');
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_letterspacing') !== '') {
			$first_menu_style['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('fullscreen_menu_letterspacing')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_texttransform') !== '') {
			$first_menu_style['text-transform'] = walker_edge_options()->getOptionValue('fullscreen_menu_texttransform');
		}

		if (!empty($first_menu_style)) {
			echo walker_edge_dynamic_css('nav.edgtf-fullscreen-menu > ul > li > a', $first_menu_style);
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_color') !== '') {
			echo walker_edge_dynamic_css('.edgtf-fullscreen-menu-opener.edgtf-fm-opened .edgt-fullscreen-menu-lines .edgtf-fullscreen-menu-line, .edgtf-fullscreen-menu-opener.edgtf-fm-opened .edgt-fullscreen-menu-lines .edgtf-fullscreen-menu-line', array(
				'background-color' => walker_edge_options()->getOptionValue('fullscreen_menu_color')
			));
		}

		$first_menu_hover_style = array();

		if (walker_edge_options()->getOptionValue('fullscreen_menu_hover_color') !== '') {
			$first_menu_hover_style['color'] = walker_edge_options()->getOptionValue('fullscreen_menu_hover_color');
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_hover_background_color') !== '') {
			$first_menu_hover_style['background-color'] = walker_edge_options()->getOptionValue('fullscreen_menu_hover_background_color');
		}

		if (!empty($first_menu_hover_style)) {
			echo walker_edge_dynamic_css('nav.edgtf-fullscreen-menu > ul > li > a:hover', $first_menu_hover_style);
		}

		$first_menu_active_style = array();

		if (walker_edge_options()->getOptionValue('fullscreen_menu_active_color') !== '') {
			$first_menu_active_style['color'] = walker_edge_options()->getOptionValue('fullscreen_menu_active_color');
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_active_background_color') !== '') {
			$first_menu_active_style['background-color'] = walker_edge_options()->getOptionValue('fullscreen_menu_active_background_color');
		}

		if (!empty($first_menu_active_style)) {
			echo walker_edge_dynamic_css('nav.edgtf-fullscreen-menu > ul > li.edgtf-active-item > a', $first_menu_active_style);
		}
	}

	add_action('walker_edge_style_dynamic', 'walker_edge_fullscreen_menu_first_level_style');
}

if (!function_exists('walker_edge_fullscreen_menu_second_level_style')) {

	function walker_edge_fullscreen_menu_second_level_style() {
		$second_menu_style = array();
		if (walker_edge_options()->getOptionValue('fullscreen_menu_color_2nd') !== '') {
			$second_menu_style['color'] = walker_edge_options()->getOptionValue('fullscreen_menu_color_2nd');
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_google_fonts_2nd') !== '-1') {
			$second_menu_style['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('fullscreen_menu_google_fonts_2nd')) . ',sans-serif';
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_fontsize_2nd') !== '') {
			$second_menu_style['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('fullscreen_menu_fontsize_2nd')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_lineheight_2nd') !== '') {
			$second_menu_style['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('fullscreen_menu_lineheight_2nd')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_fontstyle_2nd') !== '') {
			$second_menu_style['font-style'] = walker_edge_options()->getOptionValue('fullscreen_menu_fontstyle_2nd');
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_fontweight_2nd') !== '') {
			$second_menu_style['font-weight'] = walker_edge_options()->getOptionValue('fullscreen_menu_fontweight_2nd');
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_letterspacing_2nd') !== '') {
			$second_menu_style['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('fullscreen_menu_letterspacing_2nd')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_texttransform_2nd') !== '') {
			$second_menu_style['text-transform'] = walker_edge_options()->getOptionValue('fullscreen_menu_texttransform_2nd');
		}

		if (!empty($second_menu_style)) {
			echo walker_edge_dynamic_css('nav.edgtf-fullscreen-menu ul li ul li a', $second_menu_style);
		}

		$second_menu_hover_style = array();

		if (walker_edge_options()->getOptionValue('fullscreen_menu_hover_color_2nd') !== '') {
			$second_menu_hover_style['color'] = walker_edge_options()->getOptionValue('fullscreen_menu_hover_color_2nd');
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_hover_background_color_2nd') !== '') {
			$second_menu_hover_style['background-color'] = walker_edge_options()->getOptionValue('fullscreen_menu_hover_background_color_2nd');
		}

		if (!empty($second_menu_hover_style)) {
			echo walker_edge_dynamic_css('nav.edgtf-fullscreen-menu ul li ul li a:hover, nav.edgtf-fullscreen-menu ul li ul li.current-menu-ancestor > a, nav.edgtf-fullscreen-menu ul li ul li.current-menu-item > a', $second_menu_hover_style);
		}
	}

	add_action('walker_edge_style_dynamic', 'walker_edge_fullscreen_menu_second_level_style');

}

if (!function_exists('walker_edge_fullscreen_menu_third_level_style')) {

	function walker_edge_fullscreen_menu_third_level_style()	{
		$third_menu_style = array();
		if (walker_edge_options()->getOptionValue('fullscreen_menu_color_3rd') !== '') {
			$third_menu_style['color'] = walker_edge_options()->getOptionValue('fullscreen_menu_color_3rd');
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_google_fonts_3rd') !== '-1') {
			$third_menu_style['font-family'] = walker_edge_get_formatted_font_family(walker_edge_options()->getOptionValue('fullscreen_menu_google_fonts_3rd')) . ',sans-serif';
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_fontsize_3rd') !== '') {
			$third_menu_style['font-size'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('fullscreen_menu_fontsize_3rd')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_lineheight_3rd') !== '') {
			$third_menu_style['line-height'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('fullscreen_menu_lineheight_3rd')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_fontstyle_3rd') !== '') {
			$third_menu_style['font-style'] = walker_edge_options()->getOptionValue('fullscreen_menu_fontstyle_3rd');
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_fontweight_3rd') !== '') {
			$third_menu_style['font-weight'] = walker_edge_options()->getOptionValue('fullscreen_menu_fontweight_3rd');
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_letterspacing_3rd') !== '') {
			$third_menu_style['letter-spacing'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('fullscreen_menu_letterspacing_3rd')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_texttransform_3rd') !== '') {
			$third_menu_style['text-transform'] = walker_edge_options()->getOptionValue('fullscreen_menu_texttransform_3rd');
		}

		if (!empty($third_menu_style)) {
			echo walker_edge_dynamic_css('nav.edgtf-fullscreen-menu ul li ul li ul li a', $third_menu_style);
		}

		$third_menu_hover_style = array();

		if (walker_edge_options()->getOptionValue('fullscreen_menu_hover_color_3rd') !== '') {
			$third_menu_hover_style['color'] = walker_edge_options()->getOptionValue('fullscreen_menu_hover_color_3rd');
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_hover_background_color_3rd') !== '') {
			$third_menu_hover_style['background-color'] = walker_edge_options()->getOptionValue('fullscreen_menu_hover_background_color_3rd');
		}

		if (!empty($third_menu_hover_style)) {
			echo walker_edge_dynamic_css('nav.edgtf-fullscreen-menu ul li ul li ul li a:hover, nav.edgtf-fullscreen-menu ul li ul li ul li.current-menu-ancestor > a, nav.edgtf-fullscreen-menu ul li ul li ul li.current-menu-item > a', $third_menu_hover_style);
		}
	}

	add_action('walker_edge_style_dynamic', 'walker_edge_fullscreen_menu_third_level_style');

}

if (!function_exists('walker_edge_fullscreen_menu_icon_styles')) {

	function walker_edge_fullscreen_menu_icon_styles() {

		if (walker_edge_options()->getOptionValue('fullscreen_menu_icon_color') !== '') {

			echo walker_edge_dynamic_css('.edgtf-fullscreen-menu-opener .edgt-fullscreen-menu-lines .edgtf-fullscreen-menu-line', array(
				'background-color' => walker_edge_options()->getOptionValue('fullscreen_menu_icon_color')
			));
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_icon_hover_color') !== '') {

			echo walker_edge_dynamic_css('.edgtf-fullscreen-menu-opener:hover .edgt-fullscreen-menu-lines .edgtf-fullscreen-menu-line', array(
				'background-color' => walker_edge_options()->getOptionValue('fullscreen_menu_icon_hover_color')
			));
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_light_icon_color') !== '') {
			echo walker_edge_dynamic_css('.edgtf-light-header .edgtf-page-header > div:not(.edgtf-sticky-header) .edgtf-fullscreen-menu-opener:not(.edgtf-fm-opened) .edgt-fullscreen-menu-lines .edgtf-fullscreen-menu-line,
			.edgtf-light-header.edgtf-header-style-on-scroll .edgtf-page-header .edgtf-fullscreen-menu-opener:not(.edgtf-fm-opened) .edgt-fullscreen-menu-lines .edgtf-fullscreen-menu-line,
			.edgtf-light-header .edgtf-top-bar .edgtf-fullscreen-menu-opener:not(.edgtf-fm-opened) .edgt-fullscreen-menu-lines .edgtf-fullscreen-menu-line', array(
				'background-color' => walker_edge_options()->getOptionValue('fullscreen_menu_light_icon_color') . ' !important'
			));
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_light_icon_hover_color') !== '') {

			echo walker_edge_dynamic_css('.edgtf-light-header .edgtf-page-header > div:not(.edgtf-sticky-header) .edgtf-fullscreen-menu-opener:not(.edgtf-fm-opened):hover .edgt-fullscreen-menu-lines .edgtf-fullscreen-menu-line,
			.edgtf-light-header.edgtf-header-style-on-scroll .edgtf-page-header .edgtf-fullscreen-menu-opener:not(.edgtf-fm-opened):hover .edgt-fullscreen-menu-lines .edgtf-fullscreen-menu-line,
			.edgtf-light-header .edgtf-top-bar .edgtf-fullscreen-menu-opener:not(.edgtf-fm-opened):hover .edgt-fullscreen-menu-lines .edgtf-fullscreen-menu-line', array(
				'background-color' => walker_edge_options()->getOptionValue('fullscreen_menu_light_icon_hover_color') . ' !important'
			));
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_dark_icon_color') !== '') {

			echo walker_edge_dynamic_css('.edgtf-dark-header .edgtf-page-header > div:not(.edgtf-sticky-header) .edgtf-fullscreen-menu-opener:not(.edgtf-fm-opened) .edgt-fullscreen-menu-lines .edgtf-fullscreen-menu-line,
			.edgtf-dark-header.edgtf-header-style-on-scroll .edgtf-page-header .edgtf-fullscreen-menu-opener:not(.edgtf-fm-opened) .edgt-fullscreen-menu-lines .edgtf-fullscreen-menu-line,
			.edgtf-dark-header .edgtf-top-bar .edgtf-fullscreen-menu-opener:not(.edgtf-fm-opened) .edgt-fullscreen-menu-lines .edgtf-fullscreen-menu-line', array(
				'background-color' => walker_edge_options()->getOptionValue('fullscreen_menu_dark_icon_color') . ' !important'
			));
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_dark_icon_hover_color') !== '') {

			echo walker_edge_dynamic_css('.edgtf-dark-header .edgtf-page-header > div:not(.edgtf-sticky-header) .edgtf-fullscreen-menu-opener:not(.edgtf-fm-opened):hover .edgt-fullscreen-menu-lines .edgtf-fullscreen-menu-line,
			.edgtf-dark-header.edgtf-header-style-on-scroll .edgtf-page-header .edgtf-fullscreen-menu-opener:not(.edgtf-fm-opened):hover .edgt-fullscreen-menu-lines .edgtf-fullscreen-menu-line,
			.edgtf-dark-header .edgtf-top-bar .edgtf-fullscreen-menu-opener:not(.edgtf-fm-opened):hover .edgt-fullscreen-menu-lines .edgtf-fullscreen-menu-line', array(
				'background-color' => walker_edge_options()->getOptionValue('fullscreen_menu_dark_icon_hover_color') . ' !important'
			));
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_icon_background_color') !== '') {

			echo walker_edge_dynamic_css('.edgtf-fullscreen-menu-opener', array(
				'display' => 'inline-block'
			));
			echo walker_edge_dynamic_css('.edgtf-fullscreen-menu-opener', array(
				'padding' => '10px 13px',
			));
			echo walker_edge_dynamic_css('.edgtf-fullscreen-menu-opener:not(.edgtf-fm-opened)', array(
				'background-color' => walker_edge_options()->getOptionValue('fullscreen_menu_icon_background_color')
			));
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_icon_background_hover_color') !== '') {

			walker_edge_dynamic_css('.edgtf-fullscreen-menu-opener:not(.edgtf-fm-opened):hover', array(
				'background-color' => walker_edge_options()->getOptionValue('fullscreen_menu_icon_background_hover_color')
			));
		}
	}

	add_action('walker_edge_style_dynamic', 'walker_edge_fullscreen_menu_icon_styles');
}

if (!function_exists('walker_edge_fullscreen_menu_icon_spacing')) {

	function walker_edge_fullscreen_menu_icon_spacing() {

		$fullscreen_menu_icon_spacing = array();

		if (walker_edge_options()->getOptionValue('fullscreen_menu_icon_padding_left') !== '') {
			$fullscreen_menu_icon_spacing['padding-left'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('fullscreen_menu_icon_padding_left')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_icon_padding_right') !== '') {
			$fullscreen_menu_icon_spacing['padding-right'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('fullscreen_menu_icon_padding_right')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_icon_margin_left') !== '') {
			$fullscreen_menu_icon_spacing['margin-left'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('fullscreen_menu_icon_margin_left')) . 'px';
		}

		if (walker_edge_options()->getOptionValue('fullscreen_menu_icon_margin_right') !== '') {
			$fullscreen_menu_icon_spacing['margin-right'] = walker_edge_filter_px(walker_edge_options()->getOptionValue('fullscreen_menu_icon_margin_right')) . 'px';
		}

		if (!empty($fullscreen_menu_icon_spacing)) {
			echo walker_edge_dynamic_css('a.edgtf-fullscreen-menu-opener', $fullscreen_menu_icon_spacing);
		}
	}

	add_action('walker_edge_style_dynamic', 'walker_edge_fullscreen_menu_icon_spacing');
}
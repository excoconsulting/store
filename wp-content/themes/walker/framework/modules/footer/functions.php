<?php

if (!function_exists('walker_edge_get_footer_classes')) {
	/**
	 * Return all footer classes
	 *
	 * @param $page_id
	 * @return string|void
	 */
	function walker_edge_get_footer_classes($page_id) {

		$footer_classes                     = '';
		$footer_classes_array               = array();

		if(get_post_meta($page_id, 'edgtf_disable_footer_meta', true) == 'yes'){
			$footer_classes_array[] = 'edgtf-disable-footer';
		}

		if(get_post_meta($page_id, 'edgtf_set_footer_skin_meta', true) !== ''){
			$footer_classes_array[] = 'edgtf-footer-skin-'.get_post_meta($page_id, 'edgtf_set_footer_skin_meta', true);
		}

		//is some class added to footer classes array?
		if(is_array($footer_classes_array) && count($footer_classes_array)) {
			//concat all classes and prefix it with class attribute
			$footer_classes = esc_attr(implode(' ', $footer_classes_array));
		}

		return $footer_classes;
	}
}

if (!function_exists('walker_edge_footer_top_classes')) {
	/**
	 * Return classes for footer top
	 *
	 * @return string
	 */
	function walker_edge_footer_top_classes() {

		$footer_top_classes = array();

		if(walker_edge_options()->getOptionValue('footer_in_grid') != 'yes') {
			$footer_top_classes[] = 'edgtf-footer-top-full';
		}

		//footer aligment
		if(walker_edge_options()->getOptionValue('footer_top_columns_alignment') != '') {
			$footer_top_classes[] = 'edgtf-footer-top-alignment-'.walker_edge_options()->getOptionValue('footer_top_columns_alignment');
		}

		return implode(' ', $footer_top_classes);
	}
}
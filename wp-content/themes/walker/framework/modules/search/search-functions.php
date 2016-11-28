<?php

if( !function_exists('walker_edge_search_body_class') ) {
	/**
	 * Function that adds body classes for different search types
	 *
	 * @param $classes array original array of body classes
	 *
	 * @return array modified array of classes
	 */
	function walker_edge_search_body_class($classes) {

		if ( is_active_widget( false, false, 'edgtf_search_opener' ) ) {

			$classes[] = 'edgtf-' . walker_edge_options()->getOptionValue('search_type');

			if ( walker_edge_options()->getOptionValue('search_type') == 'fullscreen-search' ) {

				$classes[] = 'edgtf-search-fade';

			}

		}
		return $classes;

	}

	add_filter('body_class', 'walker_edge_search_body_class');
}

if ( ! function_exists('walker_edge_get_search') ) {
	/**
	 * Loads search HTML based on search type option.
	 */
	function walker_edge_get_search() {

		if ( walker_edge_active_widget( false, false, 'edgtf_search_opener' ) ) {

			$search_type = walker_edge_options()->getOptionValue('search_type');

			if ($search_type === 'slide-from-header-bottom') {
				walker_edge_slide_from_header_bottom_search();
				return;
			}

			walker_edge_load_search_template();
		}
	}
}

if ( ! function_exists('walker_edge_slide_from_header_bottom_search') ) {
	/**
	 * Finds part of header where search template will be loaded
	 */
	function walker_edge_slide_from_header_bottom_search() {

		add_action( 'walker_edge_end_of_page_header_html', 'walker_edge_load_search_template');
	}
}

if ( ! function_exists('walker_edge_load_search_template') ) {
	/**
	 * Loads HTML template with parameters
	 */
	function walker_edge_load_search_template() {
		global $walker_edge_IconCollections;

		$search_type = walker_edge_options()->getOptionValue('search_type');

		$search_icon = '';
		$search_icon_close = '';
		if ( walker_edge_options()->getOptionValue('search_icon_pack') !== '' ) {
			$search_icon = $walker_edge_IconCollections->getSearchIcon( walker_edge_options()->getOptionValue('search_icon_pack'), true );
			$search_icon_close = $walker_edge_IconCollections->getSearchClose( walker_edge_options()->getOptionValue('search_icon_pack'), true );
		}

		$parameters = array(
			'search_in_grid'		=> walker_edge_options()->getOptionValue('search_in_grid') == 'yes' ? true : false,
			'search_icon'			=> $search_icon,
			'search_icon_close'		=> $search_icon_close
		);

		walker_edge_get_module_template_part( 'templates/types/'.$search_type, 'search', '', $parameters );
	}
}
<?php
if (!function_exists('walker_edge_register_side_area_sidebar')) {
	/**
	 * Register side area sidebar
	 */
	function walker_edge_register_side_area_sidebar() {

		register_sidebar(array(
			'name' => 'Side Area',
			'id' => 'sidearea', //TODO Change name of sidebar
			'description' => 'Side Area',
			'before_widget' => '<div id="%1$s" class="widget edgtf-sidearea %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h4 class="edgtf-sidearea-widget-title">',
			'after_title' => '</h4>'
		));

	}

	add_action('widgets_init', 'walker_edge_register_side_area_sidebar');

}

if(!function_exists('walker_edge_side_menu_body_class')) {
    /**
     * Function that adds body classes for different side menu styles
     *
     * @param $classes array original array of body classes
     *
     * @return array modified array of classes
     */
    function walker_edge_side_menu_body_class($classes) {

		if (is_active_widget( false, false, 'edgtf_side_area_opener' )) {

			$classes[] = 'edgtf-side-menu-slide-from-right';

		}

		return $classes;
    }

    add_filter('body_class', 'walker_edge_side_menu_body_class');
}

if(!function_exists('walker_edge_get_side_area')) {
	/**
	 * Loads side area HTML
	 */
	function walker_edge_get_side_area() {

		if (is_active_widget( false, false, 'edgtf_side_area_opener' )) {

			$parameters = array(
				'show_side_area_title' => walker_edge_options()->getOptionValue('side_area_title') !== '' ? true : false, //Dont show title if empty
			);

			walker_edge_get_module_template_part('templates/sidearea', 'sidearea', '', $parameters);
		}
	}
}

if (!function_exists('walker_edge_get_side_area_title')) {
	/**
	 * Loads side area title HTML
	 */
	function walker_edge_get_side_area_title() {

		$parameters = array(
			'side_area_title' => walker_edge_options()->getOptionValue('side_area_title')
		);

		walker_edge_get_module_template_part('templates/parts/title', 'sidearea', '', $parameters);
	}
}


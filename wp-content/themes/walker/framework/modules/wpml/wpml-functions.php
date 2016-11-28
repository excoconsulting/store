<?php

if(!function_exists('walker_edge_disable_wpml_css')) {
    function walker_edge_disable_wpml_css() {
	    define('ICL_DONT_LOAD_LANGUAGE_SELECTOR_CSS', true);
    }

	add_action('after_setup_theme', 'walker_edge_disable_wpml_css');
}
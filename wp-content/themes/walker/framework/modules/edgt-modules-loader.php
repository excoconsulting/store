<?php

if(!function_exists('walker_edge_load_modules')) {
    /**
     * Loades all modules by going through all folders that are placed directly in modules folder
     * and loads load.php file in each. Hooks to walker_edge_after_options_map action
     *
     * @see http://php.net/manual/en/function.glob.php
     */
    function walker_edge_load_modules() {
        foreach(glob(EDGE_FRAMEWORK_ROOT_DIR.'/modules/*/load.php') as $module_load) {
            include_once $module_load;
        }
    }

    add_action('walker_edge_before_options_map', 'walker_edge_load_modules');
}

if(!function_exists('walker_edge_load_shortcode_interface')) {

    function walker_edge_load_shortcode_interface() {

        include_once EDGE_FRAMEWORK_MODULES_ROOT_DIR.'/shortcodes/lib/shortcode-interface.php';

    }

    add_action('walker_edge_before_options_map', 'walker_edge_load_shortcode_interface');

}

if(!function_exists('walker_edge_load_shortcodes')) {
    /**
     * Loades all shortcodes by going through all folders that are placed directly in shortcodes folder
     * and loads load.php file in each. Hooks to walker_edge_after_options_map action
     *
     * @see http://php.net/manual/en/function.glob.php
     */
    function walker_edge_load_shortcodes() {
        foreach(glob(EDGE_FRAMEWORK_ROOT_DIR.'/modules/shortcodes/*/load.php') as $shortcode_load) {
            include_once $shortcode_load;
        }

        include_once EDGE_FRAMEWORK_MODULES_ROOT_DIR.'/shortcodes/lib/shortcode-loader.inc';
    }

    add_action('walker_edge_before_options_map', 'walker_edge_load_shortcodes');
}

if(!function_exists('walker_edge_load_widget_class')) {
	 /**
     * Loades widget class file. 
     *
     */
	function walker_edge_load_widget_class(){
		include_once EDGE_FRAMEWORK_MODULES_ROOT_DIR.'/widgets/lib/widget-class.php';
	} 
	
	add_action('walker_edge_before_options_map', 'walker_edge_load_widget_class');
}

if(!function_exists('walker_edge_load_widgets')) {
    /**
     * Loades all widgets by going through all folders that are placed directly in widgets folder
     * and loads load.php file in each. Hooks to walker_edge_after_options_map action
     */
    function walker_edge_load_widgets() {
		
        foreach(glob(EDGE_FRAMEWORK_ROOT_DIR.'/modules/widgets/*/load.php') as $widget_load) {
            include_once $widget_load;
        }

        include_once EDGE_FRAMEWORK_MODULES_ROOT_DIR.'/widgets/lib/widget-loader.php';
    }

    add_action('walker_edge_before_options_map', 'walker_edge_load_widgets');
}
<?php

add_action('after_setup_theme', 'walker_edge_meta_boxes_map_init', 1);
function walker_edge_meta_boxes_map_init() {
    /**
    * Loades all meta-boxes by going through all folders that are placed directly in meta-boxes folder
    * and loads map.php file in each.
    *
    * @see http://php.net/manual/en/function.glob.php
    */

    do_action('walker_edge_before_meta_boxes_map');

	global $walker_edge_options;
	global $walker_edge_Framework;
	global $walker_edge_options_fontstyle;
	global $walker_edge_options_fontweight;
	global $walker_edge_options_texttransform;
	global $walker_edge_options_fontdecoration;
	global $walker_edge_options_arrows_type;

    foreach(glob(EDGE_FRAMEWORK_ROOT_DIR.'/admin/meta-boxes/*/map.php') as $meta_box_load) {
        include_once $meta_box_load;
    }

	do_action('walker_edge_meta_boxes_map');

	do_action('walker_edge_after_meta_boxes_map');
}
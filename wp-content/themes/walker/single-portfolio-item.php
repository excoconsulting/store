<?php

get_header();
walker_edge_get_title();
do_action('walker_edge_before_slider_action');
get_template_part('slider');
do_action('walker_edge_after_slider_action');
walker_edge_single_portfolio();
get_footer();

?>
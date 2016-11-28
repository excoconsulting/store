<?php

//top header bar
add_action('walker_edge_before_page_header', 'walker_edge_get_header_top');

//mobile header
add_action('walker_edge_after_page_header', 'walker_edge_get_mobile_header');

//header bottom
add_action('walker_edge_after_slider_action', 'walker_edge_get_header_bottom');
add_action('walker_edge_before_slider_action', 'walker_edge_get_mobile_header_bottom');
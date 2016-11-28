<?php

if(!function_exists('walker_edge_register_top_header_areas')) {
    /**
     * Registers widget areas for top header bar when it is enabled
     */
    function walker_edge_register_top_header_areas() {

        register_sidebar(array(
            'name'          => esc_html__('Top Bar Left', 'walker'),
            'id'            => 'edgtf-top-bar-left',
            'before_widget' => '<div id="%1$s" class="widget %2$s edgtf-top-bar-widget">',
            'after_widget'  => '</div>',
            'description'   => esc_html__('Widgets added here will appear on the left side in Top Bar Header.', 'walker')
        ));

        register_sidebar(array(
            'name'          => esc_html__('Top Bar Center', 'walker'),
            'id'            => 'edgtf-top-bar-center',
            'before_widget' => '<div id="%1$s" class="widget %2$s edgtf-top-bar-widget">',
            'after_widget'  => '</div>',
            'description'   => esc_html__('Widgets added here will appear on the middle side in Top Bar Header.', 'walker')
        ));

        register_sidebar(array(
            'name'          => esc_html__('Top Bar Right', 'walker'),
            'id'            => 'edgtf-top-bar-right',
            'before_widget' => '<div id="%1$s" class="widget %2$s edgtf-top-bar-widget">',
            'after_widget'  => '</div>',
            'description'   => esc_html__('Widgets added here will appear on the right side in Top Bar Header.', 'walker')
        ));
    }

    add_action('widgets_init', 'walker_edge_register_top_header_areas');
}

if(!function_exists('walker_edge_header_widget_areas')) {
    /**
     * Registers widget areas for header types
     */
    function walker_edge_header_standard_widget_areas() {
        register_sidebar(array(
            'name'          => esc_html__('Header Standard Widget Area', 'walker'),
            'id'            => 'edgtf-header-standard-widget-area',
            'before_widget' => '<div id="%1$s" class="widget %2$s edgtf-header-standard-widget-area">',
            'after_widget'  => '</div>',
            'description'   => esc_html__('Widgets added here will appear on the right hand side from the main menu. Only for Standard Header Type.', 'walker')
        ));
        register_sidebar(array(
            'name'          => esc_html__('Header Simple Widget Area', 'walker'),
            'id'            => 'edgtf-header-simple-widget-area',
            'before_widget' => '<div id="%1$s" class="widget %2$s edgtf-header-simple-widget-area">',
            'after_widget'  => '</div>',
            'description'   => esc_html__('Widgets added here will appear on the right hand side from the main menu. Only for Simple Header Type.', 'walker')
        ));
        register_sidebar(array(
            'name'          => esc_html__('Header Classic Widget Area', 'walker'),
            'id'            => 'edgtf-header-classic-widget-area',
            'before_widget' => '<div id="%1$s" class="widget %2$s edgtf-header-classic-widget-area">',
            'after_widget'  => '</div>',
            'description'   => esc_html__('Widgets added here will appear on the right hand side from the logo and logo will goes left. Only for Classic Header Type.', 'walker')
        ));
        register_sidebar(array(
            'name'          => esc_html__('Header Full Screen Widget Area', 'walker'),
            'id'            => 'edgtf-header-full-screen-widget-area',
            'before_widget' => '<div id="%1$s" class="widget %2$s edgtf-header-full-screen-widget-area">',
            'after_widget'  => '</div>',
            'description'   => esc_html__('Widgets added here will appear on the right side of your header area. Only for Full Screen Header Type.', 'walker')
        ));
        register_sidebar(array(
            'name'          => esc_html__('Header Vertical Widget Area', 'walker'),
            'id'            => 'edgtf-header-vertical-widget-area',
            'before_widget' => '<div id="%1$s" class="widget %2$s edgtf-header-vertical-widget-area">',
            'after_widget'  => '</div>',
            'description'   => esc_html__('Widgets added here will appear on the bottom of vertical menu. Only for Vertical Header Type.', 'walker')
        ));
        register_sidebar(array(
            'name'          => esc_html__('Header Vertical Bottom Widget Area', 'walker'),
            'id'            => 'edgtf-header-vertical-bottom-widget-area',
            'before_widget' => '<div id="%1$s" class="widget %2$s edgtf-header-vertical-bottom-widget-area">',
            'after_widget'  => '</div>',
            'description'   => esc_html__('Widgets added here will appear on the end of vertical area. Only for Vertical Header Type.', 'walker')
        ));
        register_sidebar(array(
            'name'          => esc_html__('Header Bottom Widget Area', 'walker'),
            'id'            => 'edgtf-header-bottom-widget-area',
            'before_widget' => '<div id="%1$s" class="widget %2$s edgtf-header-bottom-widget-area">',
            'after_widget'  => '</div>',
            'description'   => esc_html__('Widgets added here will appear on the right hand side from the main menu. Only for Bottom Header Type.', 'walker')
        ));
    }

    add_action('widgets_init', 'walker_edge_header_standard_widget_areas');
}

if(!function_exists('walker_edge_register_mobile_header_areas')) {
    /**
     * Registers widget areas for mobile header
     */
    function walker_edge_register_mobile_header_areas() {
        if(walker_edge_is_responsive_on()) {
            register_sidebar(array(
                'name'          => esc_html__('Right From Mobile Logo', 'walker'),
                'id'            => 'edgtf-right-from-mobile-logo',
                'before_widget' => '<div id="%1$s" class="widget %2$s edgtf-right-from-mobile-logo">',
                'after_widget'  => '</div>',
                'description'   => esc_html__('Widgets added here will appear on the right hand side from the mobile logo', 'walker')
            ));
        }
    }

    add_action('widgets_init', 'walker_edge_register_mobile_header_areas');
}

if(!function_exists('walker_edge_register_sticky_header_areas')) {
    /**
     * Registers widget area for sticky header
     */
    function walker_edge_register_sticky_header_areas() {
        if(in_array(walker_edge_options()->getOptionValue('header_behaviour'), array('sticky-header-on-scroll-up','sticky-header-on-scroll-down-up'))) {
            register_sidebar(array(
                'name'          => esc_html__('Sticky Right', 'walker'),
                'id'            => 'edgtf-sticky-right',
                'before_widget' => '<div id="%1$s" class="widget %2$s edgtf-sticky-right">',
                'after_widget'  => '</div>',
                'description'   => esc_html__('Widgets added here will appear on the right hand side in sticky menu', 'walker')
            ));
        }
    }

    add_action('widgets_init', 'walker_edge_register_sticky_header_areas');
}
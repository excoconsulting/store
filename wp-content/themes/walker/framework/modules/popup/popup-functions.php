<?php

if ( ! function_exists('walker_edge_get_popup') ) {
    /**
     * Loads search HTML based on search type option.
     */
    function walker_edge_get_popup() {

        if ( walker_edge_active_widget( false, false, 'edgtf_popup_opener' ) ) {
            if(walker_edge_options()->getOptionValue('enable_popup') === 'yes') {
                walker_edge_load_popup_template();
            }
        }
    }
}

if ( ! function_exists('walker_edge_load_popup_template') ) {
    /**
     * Loads HTML template with parameters
     */
    function walker_edge_load_popup_template() {
        $parameters = array();
        $parameters['image'] = walker_edge_options()->getOptionValue('popup_image');
        $parameters['title'] = walker_edge_options()->getOptionValue('popup_title');
        $parameters['subtitle'] = walker_edge_options()->getOptionValue('popup_subtitle');
        $parameters['contact_form'] = walker_edge_options()->getOptionValue('popup_contact_form');
        $parameters['contact_form_style'] = walker_edge_options()->getOptionValue('popup_contact_form_style');
        walker_edge_get_module_template_part( 'templates/popup', 'popup', '', $parameters );
    }
}
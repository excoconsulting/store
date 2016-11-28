<?php

if ( ! function_exists('walker_edge_general_options_map') ) {
    /**
     * General options page
     */
    function walker_edge_general_options_map() {

        walker_edge_add_admin_page(
            array(
                'slug'  => '',
                'title' => 'General',
                'icon'  => 'fa fa-institution'
            )
        );

        $panel_design_style = walker_edge_add_admin_panel(
            array(
                'page'  => '',
                'name'  => 'panel_design_style',
                'title' => 'Design Style'
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'google_fonts',
                'type'          => 'font',
                'default_value' => '-1',
                'label'         => 'Google Font Family',
                'description'   => 'Choose a default Google font for your site',
                'parent' => $panel_design_style
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'additional_google_fonts',
                'type'          => 'yesno',
                'default_value' => 'no',
                'label'         => 'Additional Google Fonts',
                'description'   => '',
                'parent'        => $panel_design_style,
                'args'          => array(
                    "dependence" => true,
                    "dependence_hide_on_yes" => "",
                    "dependence_show_on_yes" => "#edgtf_additional_google_fonts_container"
                )
            )
        );

        $additional_google_fonts_container = walker_edge_add_admin_container(
            array(
                'parent'            => $panel_design_style,
                'name'              => 'additional_google_fonts_container',
                'hidden_property'   => 'additional_google_fonts',
                'hidden_value'      => 'no'
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'additional_google_font1',
                'type'          => 'font',
                'default_value' => '-1',
                'label'         => 'Font Family',
                'description'   => 'Choose additional Google font for your site',
                'parent'        => $additional_google_fonts_container
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'additional_google_font2',
                'type'          => 'font',
                'default_value' => '-1',
                'label'         => 'Font Family',
                'description'   => 'Choose additional Google font for your site',
                'parent'        => $additional_google_fonts_container
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'additional_google_font3',
                'type'          => 'font',
                'default_value' => '-1',
                'label'         => 'Font Family',
                'description'   => 'Choose additional Google font for your site',
                'parent'        => $additional_google_fonts_container
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'additional_google_font4',
                'type'          => 'font',
                'default_value' => '-1',
                'label'         => 'Font Family',
                'description'   => 'Choose additional Google font for your site',
                'parent'        => $additional_google_fonts_container
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'additional_google_font5',
                'type'          => 'font',
                'default_value' => '-1',
                'label'         => 'Font Family',
                'description'   => 'Choose additional Google font for your site',
                'parent'        => $additional_google_fonts_container
            )
        );

        walker_edge_add_admin_field(
            array(
                'name' => 'google_font_weight',
                'type' => 'checkboxgroup',
                'default_value' => '',
                'label' => 'Google Fonts Style & Weight',
                'description' => 'Choose a default Google font weights for your site. Impact on page load time',
                'parent' => $panel_design_style,
                'options' => array(
                    '100'       => '100 Thin',
                    '100italic' => '100 Thin Italic',
                    '200'       => '200 Extra-Light',
                    '200italic' => '200 Extra-Light Italic',
                    '300'       => '300 Light',
                    '300italic' => '300 Light Italic',
                    '400'       => '400 Regular',
                    '400italic' => '400 Regular Italic',
                    '500'       => '500 Medium',
                    '500italic' => '500 Medium Italic',
                    '600'       => '600 Semi-Bold',
                    '600italic' => '600 Semi-Bold Italic',
                    '700'       => '700 Bold',
                    '700italic' => '700 Bold Italic',
                    '800'       => '800 Extra-Bold',
                    '800italic' => '800 Extra-Bold Italic',
                    '900'       => '900 Ultra-Bold',
                    '900italic' => '900 Ultra-Bold Italic'
                )
            )
        );

        walker_edge_add_admin_field(
            array(
                'name' => 'google_font_subset',
                'type' => 'checkboxgroup',
                'default_value' => '',
                'label' => 'Google Fonts Subset',
                'description' => 'Choose a default Google font subsets for your site',
                'parent' => $panel_design_style,
                'options' => array(
                    'latin' => 'Latin',
                    'latin-ext' => 'Latin Extended',
                    'cyrillic' => 'Cyrillic',
                    'cyrillic-ext' => 'Cyrillic Extended',
                    'greek' => 'Greek',
                    'greek-ext' => 'Greek Extended',
                    'vietnamese' => 'Vietnamese'
                )
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'first_color',
                'type'          => 'color',
                'label'         => 'First Main Color',
                'description'   => 'Choose the most dominant theme color. Default color is #5d5d5d',
                'parent'        => $panel_design_style
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'page_background_color',
                'type'          => 'color',
                'label'         => 'Page Background Color',
                'description'   => 'Choose the background color for page content. Default color is #ffffff',
                'parent'        => $panel_design_style
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'selection_color',
                'type'          => 'color',
                'label'         => 'Text Selection Color',
                'description'   => 'Choose the color users see when selecting text',
                'parent'        => $panel_design_style
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'boxed',
                'type'          => 'yesno',
                'default_value' => 'no',
                'label'         => 'Boxed Layout',
                'description'   => '',
                'parent'        => $panel_design_style,
                'args'          => array(
                    "dependence" => true,
                    "dependence_hide_on_yes" => "",
                    "dependence_show_on_yes" => "#edgtf_boxed_container"
                )
            )
        );

        $boxed_container = walker_edge_add_admin_container(
            array(
                'parent'            => $panel_design_style,
                'name'              => 'boxed_container',
                'hidden_property'   => 'boxed',
                'hidden_value'      => 'no'
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'page_background_color_in_box',
                'type'          => 'color',
                'label'         => 'Page Background Color',
                'description'   => 'Choose the page background color outside box.',
                'parent'        => $boxed_container
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'boxed_background_image',
                'type'          => 'image',
                'label'         => 'Background Image',
                'description'   => 'Choose an image to be displayed in background',
                'parent'        => $boxed_container
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'boxed_pattern_background_image',
                'type'          => 'image',
                'label'         => 'Background Pattern',
                'description'   => 'Choose an image to be used as background pattern',
                'parent'        => $boxed_container
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'boxed_background_image_attachment',
                'type'          => 'select',
                'default_value' => 'fixed',
                'label'         => 'Background Image Attachment',
                'description'   => 'Choose background image attachment',
                'parent'        => $boxed_container,
                'options'       => array(
                    'fixed'     => 'Fixed',
                    'scroll'    => 'Scroll'
                )
            )
        );
        
        walker_edge_add_admin_field(
            array(
                'name'          => 'paspartu',
                'type'          => 'yesno',
                'default_value' => 'no',
                'label'         => 'Passepartout',
                'description'   => 'Enabling this option will display passepartout around site content',
                'parent'        => $panel_design_style,
                'args'          => array(
                    "dependence" => true,
                    "dependence_hide_on_yes" => "",
                    "dependence_show_on_yes" => "#edgtf_paspartu_container"
                )
            )
        );

        $paspartu_container = walker_edge_add_admin_container(
            array(
                'parent'            => $panel_design_style,
                'name'              => 'paspartu_container',
                'hidden_property'   => 'paspartu',
                'hidden_value'      => 'no'
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'paspartu_color',
                'type'          => 'color',
                'label'         => 'Passepartout Color',
                'description'   => 'Choose passepartout color, default value is #ffffff',
                'parent'        => $paspartu_container
            )
        );

        walker_edge_add_admin_field(
            array(
                'name' => 'paspartu_width',
                'type' => 'text',
                'label' => 'Passepartout Size',
                'description' => 'Enter size amount for passepartout',
                'parent' => $paspartu_container,
                'args' => array(
                    'col_width' => 2,
                    'suffix' => '%'
                )
            )
        );

        walker_edge_add_admin_field(
            array(
                'parent' => $paspartu_container,
                'type' => 'yesno',
                'default_value' => 'no',
                'name' => 'disable_top_paspartu',
                'label' => 'Disable Top Passepartout'
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'initial_content_width',
                'type'          => 'select',
                'default_value' => '',
                'label'         => 'Initial Width of Content',
                'description'   => 'Choose the initial width of content which is in grid (Applies to pages set to "Default Template" and rows set to "In Grid"',
                'parent'        => $panel_design_style,
                'options'       => array(
                    ""          => "1100px - default",
                    "grid-1300" => "1300px",
                    "grid-1200" => "1200px",
                    "grid-1000" => "1000px",
                    "grid-800"  => "800px"
                )
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'preload_pattern_image',
                'type'          => 'image',
                'label'         => 'Preload Pattern Image',
                'description'   => 'Choose preload pattern image to be displayed until images are loaded ',
                'parent'        => $panel_design_style
            )
        );

        walker_edge_add_admin_field(
            array(
                'name' => 'element_appear_amount',
                'type' => 'text',
                'label' => 'Element Appearance',
                'description' => 'For animated elements, set distance (related to browser bottom) to start the animation',
                'parent' => $panel_design_style,
                'args' => array(
                    'col_width' => 2,
                    'suffix' => 'px'
                )
            )
        );

        $panel_settings = walker_edge_add_admin_panel(
            array(
                'page'  => '',
                'name'  => 'panel_settings',
                'title' => 'Settings'
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'smooth_scroll',
                'type'          => 'yesno',
                'default_value' => 'no',
                'label'         => 'Smooth Scroll',
                'description'   => 'Enabling this option will perform a smooth scrolling effect on every page (except on Mac and touch devices)',
                'parent'        => $panel_settings
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'smooth_page_transitions',
                'type'          => 'yesno',
                'default_value' => 'no',
                'label'         => 'Smooth Page Transitions',
                'description'   => 'Enabling this option will perform a smooth transition between pages when clicking on links.',
                'parent'        => $panel_settings,
                'args'          => array(
                    "dependence" => true,
                    "dependence_hide_on_yes" => "",
                    "dependence_show_on_yes" => "#edgtf_page_transitions_container"
                )
            )
        );

        $page_transitions_container = walker_edge_add_admin_container(
            array(
                'parent'            => $panel_settings,
                'name'              => 'page_transitions_container',
                'hidden_property'   => 'smooth_page_transitions',
                'hidden_value'      => 'no'
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'smooth_pt_bgnd_color',
                'type'          => 'color',
                'label'         => 'Page Loader Background Color',
                //'description'   => 'Enabling this option will perform a smooth transition between pages when clicking on links.',
                'parent'        => $page_transitions_container
            )
        );

        $group_pt_spinner_animation = walker_edge_add_admin_group(array(
            'name'          => 'group_pt_spinner_animation',
            'title'         => 'Loader Style',
            'description'   => 'Define styles for loader spinner animation',
            'parent'        => $page_transitions_container
        ));

        $row_pt_spinner_animation = walker_edge_add_admin_row(array(
            'name'      => 'row_pt_spinner_animation',
            'parent'    => $group_pt_spinner_animation
        ));

        walker_edge_add_admin_field(array(
            'type'          => 'selectsimple',
            'name'          => 'smooth_pt_spinner_type',
            'default_value' => '',
            'label'         => 'Spinner Type',
            'parent'        => $row_pt_spinner_animation,
            'options'       => array(
                "rotate_circles" => "Rotate Circles",
                "pulse" => "Pulse",
                "double_pulse" => "Double Pulse",
                "cube" => "Cube",
                "rotating_cubes" => "Rotating Cubes",
                "stripes" => "Stripes",
                "wave" => "Wave",
                "two_rotating_circles" => "2 Rotating Circles",
                "five_rotating_circles" => "5 Rotating Circles",
                "atom" => "Atom",
                "clock" => "Clock",
                "mitosis" => "Mitosis",
                "lines" => "Lines",
                "fussion" => "Fussion",
                "wave_circles" => "Wave Circles",
                "pulse_circles" => "Pulse Circles"
            )
        ));

        walker_edge_add_admin_field(array(
            'type'          => 'colorsimple',
            'name'          => 'smooth_pt_spinner_color',
            'default_value' => '',
            'label'         => 'Spinner Color',
            'parent'        => $row_pt_spinner_animation
        ));

        walker_edge_add_admin_field(
            array(
                'name'          => 'show_back_button',
                'type'          => 'yesno',
                'default_value' => 'yes',
                'label'         => 'Show "Back To Top Button"',
                'description'   => 'Enabling this option will display a Back to Top button on every page',
                'parent'        => $panel_settings
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'responsiveness',
                'type'          => 'yesno',
                'default_value' => 'yes',
                'label'         => 'Responsiveness',
                'description'   => 'Enabling this option will make all pages responsive',
                'parent'        => $panel_settings
            )
        );

        $panel_custom_code = walker_edge_add_admin_panel(
            array(
                'page'  => '',
                'name'  => 'panel_custom_code',
                'title' => 'Custom Code'
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'custom_css',
                'type'          => 'textarea',
                'label'         => 'Custom CSS',
                'description'   => 'Enter your custom CSS here',
                'parent'        => $panel_custom_code
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'custom_js',
                'type'          => 'textarea',
                'label'         => 'Custom JS',
                'description'   => 'Enter your custom Javascript here',
                'parent'        => $panel_custom_code
            )
        );

        $panel_google_maps_api_key = walker_edge_add_admin_panel(
            array(
                'page'  => '',
                'name'  => 'panel_google_maps_api_key',
                'title' => 'Google Maps API key'
            )
        );

        walker_edge_add_admin_field(
            array(
                'name'          => 'google_maps_api_key',
                'type'          => 'text',
                'label'         => 'Google Maps API key',
                'description'   => 'Enter your Google Maps API key here',
                'parent'        => $panel_google_maps_api_key
            )
        );

    }

    add_action( 'walker_edge_options_map', 'walker_edge_general_options_map', 1);

}
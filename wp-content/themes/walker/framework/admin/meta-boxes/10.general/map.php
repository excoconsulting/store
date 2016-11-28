<?php

    $general_meta_box = walker_edge_add_meta_box(
        array(
            'scope' => array('page', 'portfolio-item', 'post'),
            'title' => 'General',
            'name' => 'general_meta'
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name' => 'edgtf_page_background_color_meta',
            'type' => 'color',
            'default_value' => '',
            'label' => 'Page Background Color',
            'description' => 'Choose background color for page content',
            'parent' => $general_meta_box
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name' => 'edgtf_page_slider_meta',
            'type' => 'text',
            'default_value' => '',
            'label' => 'Slider Shortcode',
            'description' => 'Paste your slider shortcode here',
            'parent' => $general_meta_box
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name'        => 'edgtf_page_slider_meta_position',
            'type'        => 'select',
            'label'       => 'Set Slider Shortcode to Start Behind Header',
            'parent'      => $general_meta_box,
            'options'     => array(
                'no' => 'No',
                'yes' => 'Yes',
            )
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name'        => 'edgtf_paspartu_meta',
            'type'        => 'select',
            'label'       => 'Passepartout',
            'description' => 'Enabling this option will display passepartout around site content',
            'parent'      => $general_meta_box,
            'options'     => array(
                '' => 'Default',
                'no' => 'No',
                'yes' => 'Yes'
            )
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'parent' => $general_meta_box,
            'type' => 'select',
            'name' => 'edgtf_disable_top_paspartu_meta',
            'label' => 'Disable Top Passepartout',
            'options' => array(
                '' => 'Default',
                'no' => 'No',
                'yes' => 'Yes'
            )
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name'        => 'edgtf_page_transition_type',
            'type'        => 'selectblank',
            'label'       => 'Page Transition',
            'description' => 'Choose the type of transition to this page',
            'parent'      => $general_meta_box,
            'default_value' => '',
            'options'     => array(
                'no-animation' => 'No animation',
                'fade' => 'Fade'
            )
        )
    );

    $edgtf_content_padding_group = walker_edge_add_admin_group(array(
        'name' => 'content_padding_group',
        'title' => 'Content Style',
        'description' => 'Define styles for Content area',
        'parent' => $general_meta_box
    ));

    $edgtf_content_padding_row = walker_edge_add_admin_row(array(
        'name' => 'edgtf_content_padding_row',
        'next' => true,
        'parent' => $edgtf_content_padding_group
    ));

    walker_edge_add_meta_box_field(
        array(
            'name'          => 'edgtf_page_content_top_padding',
            'type'          => 'textsimple',
            'default_value' => '',
            'label'         => 'Content Top Padding',
            'parent'        => $edgtf_content_padding_row,
            'args'          => array(
                'suffix' => 'px'
            )
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name'        => 'edgtf_page_content_top_padding_mobile',
            'type'        => 'selectblanksimple',
            'label'       => 'Set this top padding for mobile header',
            'parent'      => $edgtf_content_padding_row,
            'options'     => array(
                'yes' => 'Yes',
                'no' => 'No',
            )
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name'        => 'edgtf_page_comments_meta',
            'type'        => 'selectblank',
            'label'       => 'Show Comments',
            'description' => 'Enabling this option will show comments on your page',
            'parent'      => $general_meta_box,
            'options'     => array(
                'yes' => 'Yes',
                'no' => 'No',
            )
        )
    );

    walker_edge_add_meta_box_field(array(
        'name'        => 'edgtf_enable_full_screen_content',
        'type'        => 'yesno',
        'label'       => 'Full Screen Content',
        'description' => 'Enabling this option will set full screen content for Coming Soon Template',
        'default_value' => 'no',
        'parent'      => $general_meta_box,
        'args' => array(
            "dependence" => true,
            "hide" => array(
                "no" => "#edgtf_edgtf_full_screen_content_container",
                "yes" => ""
            ),
            "show" => array(
                "no" => "",
                "yes" => "#edgtf_edgtf_full_screen_content_container"
            )
        )
    ));

        $full_screen_content_container = walker_edge_add_admin_container(
            array(
                'parent' => $general_meta_box,
                'name' => 'edgtf_full_screen_content_container',
                'hidden_property' => 'edgtf_enable_full_screen_content',
                'hidden_value' => 'no',
            )
        );

            walker_edge_add_meta_box_field(
                array(
                    'parent' => $full_screen_content_container,
                    'type' => 'image',
                    'name' => 'edgtf_full_screen_content_background_image',
                    'default_value' => '',
                    'label' => 'Background Image',
                    'description' => 'Choose a background image for coming soon page content'
                )
            );

            walker_edge_add_meta_box_field(
                array(
                    'parent' => $full_screen_content_container,
                    'type' => 'select',
                    'name' => 'edgtf_full_screen_content_vertical_alignment',
                    'default_value' => '',
                    'label' => 'Vertical Alignment',
                    'description' => 'Specify content elements vertical alignment',
                    'options'     => array(
                        '' => 'Default',
                        'middle' => 'Middle',
                    )
                )
            );
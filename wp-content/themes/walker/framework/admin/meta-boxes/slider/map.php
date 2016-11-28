<?php

//Slider

$slider_meta_box = walker_edge_add_meta_box(
    array(
        'scope' => array('slides'),
        'title' => 'Slide Background',
        'name' => 'slides_type'
    )
);

    walker_edge_add_meta_box_field(
        array(
            'name'          => 'edgtf_slide_background_type',
            'type'          => 'select',
            'default_value' => 'image',
            'label'         => 'Slide Background Type',
            'description'   => 'Do you want to upload an image or video?',
            'parent'        => $slider_meta_box,
            'options'       => array(
                "image" => "Image",
                "video" => "Video"
            ),
            'args' => array(
                "dependence" => true,
                "hide" => array(
                    "image" => "#edgtf_edgtf_slides_video_settings",
                    "video" => "#edgtf_edgtf_slides_image_settings"
                ),
                "show" => array(
                    "image" => "#edgtf_edgtf_slides_image_settings",
                    "video" => "#edgtf_edgtf_slides_video_settings"
                )
            )
        )
    );


//Slide Image

$image_meta_container = walker_edge_add_admin_container(
    array(
        'name' => 'edgtf_slides_image_settings',
        'parent' => $slider_meta_box,
        'hidden_property' => 'edgtf_slide_background_type',
        'hidden_values' => array('video')
    )
);

    walker_edge_add_meta_box_field(
        array(
            'name'        => 'edgtf_slide_image',
            'type'        => 'image',
            'label'       => 'Slide Image',
            'description' => 'Choose background image',
            'parent'      => $image_meta_container
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name'        => 'edgtf_slide_image_tablet',
            'type'        => 'image',
            'label'       => 'Slide Image for Tablet - Portrait devices',
            'description' => 'Choose background image',
            'parent'      => $image_meta_container
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name'        => 'edgtf_slide_image_phone',
            'type'        => 'image',
            'label'       => 'Slide Image For Mobile devices',
            'description' => 'Choose background image',
            'parent'      => $image_meta_container
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name'        => 'edgtf_slide_overlay_image',
            'type'        => 'image',
            'label'       => 'Overlay Image',
            'description' => 'Choose overlay image (pattern) for background image',
            'parent'      => $image_meta_container
        )
    );


//Slide Video

$video_meta_container = walker_edge_add_admin_container(
    array(
        'name' => 'edgtf_slides_video_settings',
        'parent' => $slider_meta_box,
        'hidden_property' => 'edgtf_slide_background_type',
        'hidden_values' => array('image')
    )
);

    walker_edge_add_meta_box_field(
        array(
            'name'        => 'edgtf_slide_video_webm',
            'type'        => 'text',
            'label'       => 'Video - webm',
            'description' => 'Path to the webm file that you have previously uploaded in Media Section',
            'parent'      => $video_meta_container
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name'        => 'edgtf_slide_video_mp4',
            'type'        => 'text',
            'label'       => 'Video - mp4',
            'description' => 'Path to the mp4 file that you have previously uploaded in Media Section',
            'parent'      => $video_meta_container
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name'        => 'edgtf_slide_video_ogv',
            'type'        => 'text',
            'label'       => 'Video - ogv',
            'description' => 'Path to the ogv file that you have previously uploaded in Media Section',
            'parent'      => $video_meta_container
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name'        => 'edgtf_slide_video_image',
            'type'        => 'image',
            'label'       => 'Video Preview Image',
            'description' => 'Choose background image that will be visible until video is loaded. This image will be shown on touch devices too.',
            'parent'      => $video_meta_container
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name' => 'edgtf_slide_video_overlay',
            'type' => 'yesempty',
            'default_value' => '',
            'label' => 'Video Overlay Image',
            'description' => 'Do you want to have a overlay image on video?',
            'parent' => $video_meta_container,
            'args' => array(
                "dependence" => true,
                "dependence_hide_on_yes" => "",
                "dependence_show_on_yes" => "#edgtf_edgtf_slide_video_overlay_container"
            )
        )
    );

    $slide_video_overlay_container = walker_edge_add_admin_container(array(
        'name' => 'edgtf_slide_video_overlay_container',
        'parent' => $video_meta_container,
        'hidden_property' => 'edgtf_slide_video_overlay',
        'hidden_values' => array('','no')
    ));

        walker_edge_add_meta_box_field(
            array(
                'name'        => 'edgtf_slide_video_overlay_image',
                'type'        => 'image',
                'label'       => 'Overlay Image',
                'description' => 'Choose overlay image (pattern) for background video.',
                'parent'      => $slide_video_overlay_container
            )
        );


//Slide Elements

$elements_meta_box = walker_edge_add_meta_box(
    array(
        'scope' => array('slides'),
        'title' => 'Slide Elements',
        'name' => 'edgtf_slides_elements'
    )
);

    walker_edge_add_admin_section_title(
        array(
            'parent' => $elements_meta_box,
            'name' => 'edgtf_slides_elements_frame',
            'title' => 'Elements Holder Frame'
        )
    );

    walker_edge_add_slide_holder_frame_scheme(
        array(
            'parent' => $elements_meta_box
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name'        => 'edgtf_slide_holder_elements_alignment',
            'type'        => 'select',
            'label'       => 'Elements Alignment',
            'description' => 'How elements are aligned with respect to the Holder Frame',
            'parent'      => $elements_meta_box,
            'default_value' => 'center',
            'options' => array(
                "center" => "Center",
                "left" => "Left",
                "right" => "Right",
                "custom" => "Custom"
            ),
            'args'        => array(
                "dependence" => true,
                "hide" => array(
                    "center" => "#edgtf_edgtf_slide_holder_frame_height",
                    "left" => "#edgtf_edgtf_slide_holder_frame_height",
                    "right" => "#edgtf_edgtf_slide_holder_frame_height",
                    "custom" => ""
                ),
                "show" => array(
                    "center" => "",
                    "left" => "",
                    "right" => "",
                    "custom" => "#edgtf_edgtf_slide_holder_frame_height"
                )
            )
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name'        => 'edgtf_slide_holder_frame_in_grid',
            'type'        => 'select',
            'label'       => 'Holder Frame in Grid?',
            'description' => 'Whether to keep the holder frame width the same as that of the grid.',
            'parent'      => $elements_meta_box,
            'default_value' => 'no',
            'options' => array(
                "yes" => "Yes",
                "no" => "No"
            ),
            'args'        => array(
                "dependence" => true,
                "hide" => array(
                    "yes" => "#edgtf_edgtf_slide_holder_frame_width, #edgtf_edgtf_holder_frame_responsive_container",
                    "no" => ""
                ),
                "show" => array(
                    "yes" => "",
                    "no" => "#edgtf_edgtf_slide_holder_frame_width, #edgtf_edgtf_holder_frame_responsive_container"
                )
            )
        )
    );

    $holder_frame = walker_edge_add_admin_group(array(
        'title' => 'Holder Frame Properties',
        'description' => 'The frame is always positioned centrally on the slide. All elements are positioned and sized relatively to the holder frame. Refer to the scheme above.',
        'name' => 'edgtf_holder_frame',
        'parent' => $elements_meta_box
    ));

        $row1 = walker_edge_add_admin_row(array(
            'name' => 'row1',
            'parent' => $holder_frame
        ));

            $holder_frame_width = walker_edge_add_meta_box_field(
                array(
                    'name'        => 'edgtf_slide_holder_frame_width',
                    'type'        => 'textsimple',
                    'label'       => 'Relative width (C/A*100)',
                    'parent'      => $row1,
                    'hidden_property' => 'edgtf_slide_holder_frame_in_grid',
                    'hidden_values' => array('yes')
                )
            );

            $holder_frame_height = walker_edge_add_meta_box_field(
                array(
                    'name'        => 'edgtf_slide_holder_frame_height',
                    'type'        => 'textsimple',
                    'label'       => 'Height to width ratio (D/C*100)',
                    'parent'      => $row1,
                    'hidden_property' => 'edgtf_slide_holder_elements_alignment',
                    'hidden_values' => array('center', 'left', 'right')
                )
            );

    $holder_frame_responsive_container = walker_edge_add_admin_container(array(
        'name' => 'edgtf_holder_frame_responsive_container',
        'parent' => $elements_meta_box,
        'hidden_property' => 'edgtf_slide_holder_frame_in_grid',
        'hidden_values' => array('yes')
    ));

    $holder_frame_responsive = walker_edge_add_admin_group(array(
        'title' => 'Responsive Relative Width',
        'description' => 'Enter different relative widths of the holder frame for each responsive stage. Leave blank to have the frame width scale proportionally to the screen size.',
        'name' => 'edgtf_holder_frame_responsive',
        'parent' => $holder_frame_responsive_container
    ));
    
    $screen_widths_holder_frame = array(
        // These values must match those in edgt.layout.inc, slider.php and shortcodes.js
        "mobile" => 600,
        "tabletp" => 800,
        "tabletl" => 1024,
        "laptop" => 1440
    );

        $row2 = walker_edge_add_admin_row(array(
            'name' => 'row2',
            'parent' => $holder_frame_responsive
        ));

            $holder_frame_width = walker_edge_add_meta_box_field(
                array(
                    'name'        => 'edgtf_slide_holder_frame_width_mobile',
                    'type'        => 'textsimple',
                    'label'       => 'Mobile (up to '.$screen_widths_holder_frame["mobile"].'px)',
                    'parent'      => $row2
                )
            );

            $holder_frame_height = walker_edge_add_meta_box_field(
                array(
                    'name'        => 'edgtf_slide_holder_frame_width_tablet_p',
                    'type'        => 'textsimple',
                    'label'       => 'Tablet - Portrait ('.($screen_widths_holder_frame["mobile"]+1).'px - '.$screen_widths_holder_frame["tabletp"].'px)',
                    'parent'      => $row2
                )
            );

            $holder_frame_height = walker_edge_add_meta_box_field(
                array(
                    'name'        => 'edgtf_slide_holder_frame_width_tablet_l',
                    'type'        => 'textsimple',
                    'label'       => 'Tablet - Landscape ('.($screen_widths_holder_frame["tabletp"]+1).'px - '.$screen_widths_holder_frame["tabletl"].'px)',
                    'parent'      => $row2
                )
            );

        $row3 = walker_edge_add_admin_row(array(
            'name' => 'row3',
            'parent' => $holder_frame_responsive
        ));

            $holder_frame_width = walker_edge_add_meta_box_field(
                array(
                    'name'        => 'edgtf_slide_holder_frame_width_laptop',
                    'type'        => 'textsimple',
                    'label'       => 'Laptop ('.($screen_widths_holder_frame["tabletl"]+1).'px - '.$screen_widths_holder_frame["laptop"].'px)',
                    'parent'      => $row3
                )
            );

            $holder_frame_height = walker_edge_add_meta_box_field(
                array(
                    'name'        => 'edgtf_slide_holder_frame_width_desktop',
                    'type'        => 'textsimple',
                    'label'       => 'Desktop (above '.$screen_widths_holder_frame["laptop"].'px)',
                    'parent'      => $row3
                )
            );

    walker_edge_add_meta_box_field(
        array(
            'parent' => $elements_meta_box,
            'type' => 'text',
            'name' => 'edgtf_slide_elements_default_width',
            'label' => 'Default Screen Width in px (A)',
            'description' => 'All elements marked as responsive scale at the ratio of the actual screen width to this screen width. Default is 1920px.'
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'parent' => $elements_meta_box,
            'type' => 'select',
            'name' => 'edgtf_slide_elements_default_animation',
            'default_value' => 'none',
            'label' => 'Default Elements Animation',
            'description' => 'This animation will be applied to all elements except those with their own animation settings.',
            'options' => array(
                "none" => "No Animation",
                "flip" => "Flip",
                "spin" => "Spin",
                "fade" => "Fade In",
                "from_bottom" => "Fly In From Bottom",
                "from_top" => "Fly In From Top",
                "from_left" => "Fly In From Left",
                "from_right" => "Fly In From Right"
            )
        )
    );

    walker_edge_add_admin_section_title(
        array(
            'parent' => $elements_meta_box,
            'name' => 'edgtf_slides_elements_list',
            'title' => 'Elements'
        )
    );

    $slide_elements = walker_edge_add_slide_elements_framework(
        array(
            'parent' => $elements_meta_box,
            'name' => 'edgtf_slides_elements_holder'
        )
    );

//Slide Behaviour

$behaviours_meta_box = walker_edge_add_meta_box(
    array(
        'scope' => array('slides'),
        'title' => 'Slide Behaviours',
        'name' => 'edgtf_slides_behaviour_settings'
    )
);  

    walker_edge_add_admin_section_title(
        array(
            'parent' => $behaviours_meta_box,
            'name' => 'edgtf_header_styling_title',
            'title' => 'Header'
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'parent' => $behaviours_meta_box,
            'type' => 'selectblank',
            'name' => 'edgtf_slide_header_style',
            'default_value' => '',
            'label' => 'Header Style',
            'description' => 'Header style will be applied when this slide is in focus',
            'options' => array(
                "light" => "Light",
                "dark" => "Dark"
            )
        )
    );

    walker_edge_add_admin_section_title(
        array(
            'parent' => $behaviours_meta_box,
            'name' => 'edgtf_image_animation_title',
            'title' => 'Slide Image Animation'
        )
    );

    walker_edge_add_meta_box_field(
        array(
            'name' => 'edgtf_enable_image_animation',
            'type' => 'yesno',
            'default_value' => 'no',
            'label' => 'Enable Image Animation',
            'description' => 'Enabling this option will turn on a motion animation on the slide image',
            'parent' => $behaviours_meta_box,
            'args' => array(
                "dependence" => true,
                "dependence_hide_on_yes" => "",
                "dependence_show_on_yes" => "#edgtf_edgtf_enable_image_animation_container"
            )
        )
    );

    $enable_image_animation_container = walker_edge_add_admin_container(array(
        'name' => 'edgtf_enable_image_animation_container',
        'parent' => $behaviours_meta_box,
        'hidden_property' => 'edgtf_enable_image_animation',
        'hidden_value' => 'no'
    ));

        walker_edge_add_meta_box_field(
            array(
                'parent' => $enable_image_animation_container,
                'type' => 'select',
                'name' => 'edgtf_enable_image_animation_type',
                'default_value' => 'zoom_center',
                'label' => 'Animation Type',
                'options' => array(
                    "zoom_center" => "Zoom In Center",
                    "zoom_top_left" => "Zoom In to Top Left",
                    "zoom_top_right" => "Zoom In to Top Right",
                    "zoom_bottom_left" => "Zoom In to Bottom Left",
                    "zoom_bottom_right" => "Zoom In to Bottom Right"
                )
            )
        );
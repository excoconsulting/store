<?php

$header_meta_box = walker_edge_add_meta_box(
    array(
        'scope' => array('page', 'portfolio-item', 'post'),
        'title' => 'Header',
        'name' => 'header_meta'
    )
);

    walker_edge_add_meta_box_field(
        array(
            'name' => 'edgtf_header_type_meta',
            'type' => 'select',
            'default_value' => '',
            'label' => 'Choose Header Type',
            'description' => 'Select header type layout',
            'parent' => $header_meta_box,
            'options' => array(
                '' => 'Default',
                'header-standard' => 'Standard Header Layout',
                'header-simple' => 'Simple Header Layout',
                'header-classic' => 'Classic Header Layout',
                'header-full-screen' => 'Full Screen Header Layout',
                'header-vertical' => 'Vertical Header Layout',
                'header-bottom' => 'Bottom Header Layout'
            ),
            'args' => array(
                "dependence" => true,
                "hide" => array(
                    "" => '#edgtf_edgtf_header_standard_type_meta_container, #edgtf_edgtf_header_simple_type_meta_container, #edgtf_edgtf_header_full_screen_type_meta_container, #edgtf_edgtf_header_vertical_type_meta_container, #edgtf_edgtf_header_bottom_type_meta_container',
                    "header-standard" => '#edgtf_edgtf_header_simple_type_meta_container, #edgtf_edgtf_header_classic_type_meta_container, #edgtf_edgtf_header_full_screen_type_meta_container, #edgtf_edgtf_header_vertical_type_meta_container, #edgtf_edgtf_header_bottom_type_meta_container',
                    "header-simple" => '#edgtf_edgtf_header_standard_type_meta_container, #edgtf_edgtf_header_classic_type_meta_container, #edgtf_edgtf_header_full_screen_type_meta_container, #edgtf_edgtf_header_vertical_type_meta_container, #edgtf_edgtf_header_bottom_type_meta_container',
                    "header-classic" => '#edgtf_edgtf_header_standard_type_meta_container, #edgtf_edgtf_header_simple_type_meta_container, #edgtf_edgtf_header_full_screen_type_meta_container, #edgtf_edgtf_header_vertical_type_meta_container, #edgtf_edgtf_header_bottom_type_meta_container',
                    "header-full-screen" => '#edgtf_edgtf_header_standard_type_meta_container, #edgtf_edgtf_header_simple_type_meta_container, #edgtf_edgtf_header_classic_type_meta_container, #edgtf_edgtf_header_vertical_type_meta_container, #edgtf_edgtf_header_bottom_type_meta_container',
                    "header-vertical" => '#edgtf_edgtf_header_standard_type_meta_container, #edgtf_edgtf_header_simple_type_meta_container, #edgtf_edgtf_header_classic_type_meta_container, #edgtf_edgtf_header_full_screen_type_meta_container, #edgtf_edgtf_header_bottom_type_meta_container',
                    "header-bottom" => '#edgtf_edgtf_header_standard_type_meta_container, #edgtf_edgtf_header_simple_type_meta_container, #edgtf_edgtf_header_classic_type_meta_container, #edgtf_edgtf_header_full_screen_type_meta_container, #edgtf_edgtf_header_vertical_type_meta_container',
                ),
                "show" => array(
                    "" => '',
                    "header-standard" => '#edgtf_edgtf_header_standard_type_meta_container',
                    "header-simple" => '#edgtf_edgtf_header_simple_type_meta_container',
                    "header-classic" => '#edgtf_edgtf_header_classic_type_meta_container',
                    "header-full-screen" => '#edgtf_edgtf_header_full_screen_type_meta_container',
                    "header-vertical" => '#edgtf_edgtf_header_vertical_type_meta_container',
                    "header-bottom" => '#edgtf_edgtf_header_bottom_type_meta_container'
                )
            )
        )
    );

    $header_standard_type_meta_container = walker_edge_add_admin_container(
        array(
            'parent' => $header_meta_box,
            'name' => 'edgtf_header_standard_type_meta_container',
            'hidden_property' => 'edgtf_header_type_meta',
            'hidden_values' => array(
                'header-simple',
	            'header-classic',
                'header-full-screen',
                'header-vertical',
                'header-bottom'
            ),
        )
    );

        walker_edge_add_meta_box_field(
            array(
                'name' => 'edgtf_menu_area_background_color_header_standard_meta',
                'type' => 'color',
                'label' => 'Background Color',
                'description' => 'Choose a background color for header area',
                'parent' => $header_standard_type_meta_container
            )
        );

        walker_edge_add_meta_box_field(
            array(
                'name' => 'edgtf_menu_area_background_transparency_header_standard_meta',
                'type' => 'text',
                'label' => 'Background Transparency',
                'description' => 'Choose a transparency for the header background color (0 = fully transparent, 1 = opaque)',
                'parent' => $header_standard_type_meta_container,
                'args' => array(
                    'col_width' => 2
                )
            )
        );

        walker_edge_add_meta_box_field(
            array(
                'name' => 'edgtf_menu_area_border_bottom_color_header_standard_meta',
                'type' => 'color',
                'label' => 'Border Bottom Color',
                'description' => 'Choose a border bottom color for header area',
                'parent' => $header_standard_type_meta_container
            )
        );

    $header_simple_type_meta_container = walker_edge_add_admin_container(
        array(
            'parent' => $header_meta_box,
            'name' => 'edgtf_header_simple_type_meta_container',
            'hidden_property' => 'edgtf_header_type_meta',
            'hidden_values' => array(
                'header-standard',
	            'header-classic',
                'header-full-screen',
                'header-vertical',
                'header-bottom'
            ),
        )
    );

        walker_edge_add_meta_box_field(
            array(
                'name' => 'edgtf_enable_grid_layout_header_simple_meta',
                'type' => 'select',
                'default_value' => '',
                'label' => 'Enable Grid Layout',
                'description' => 'Enabling this option you will set simple header area to be in grid',
                'parent' => $header_simple_type_meta_container,
                'options' => array(
                    '' => '',
                    'no' => 'No',
                    'yes' => 'Yes',
                )
            )
        );

        walker_edge_add_meta_box_field(
            array(
                'name' => 'edgtf_menu_area_background_color_header_simple_meta',
                'type' => 'color',
                'label' => 'Background Color',
                'description' => 'Choose a background color for header area',
                'parent' => $header_simple_type_meta_container
            )
        );

        walker_edge_add_meta_box_field(
            array(
                'name' => 'edgtf_menu_area_background_transparency_header_simple_meta',
                'type' => 'text',
                'label' => 'Background Transparency',
                'description' => 'Choose a transparency for the header background color (0 = fully transparent, 1 = opaque)',
                'parent' => $header_simple_type_meta_container,
                'args' => array(
                    'col_width' => 2
                )
            )
        );

        walker_edge_add_meta_box_field(
            array(
                'name' => 'edgtf_menu_area_border_bottom_color_header_simple_meta',
                'type' => 'color',
                'label' => 'Border Bottom Color',
                'description' => 'Choose a border bottom color for header area',
                'parent' => $header_simple_type_meta_container
            )
        );

	$header_classic_type_meta_container = walker_edge_add_admin_container(
		array(
			'parent' => $header_meta_box,
			'name' => 'edgtf_header_classic_type_meta_container',
			'hidden_property' => 'edgtf_header_type_meta',
			'hidden_values' => array(
				'header-standard',
				'header-simple',
				'header-full-screen',
				'header-vertical',
				'header-bottom'
			),
		)
	);
	
		walker_edge_add_meta_box_field(
			array(
				'name' => 'edgtf_menu_area_background_color_header_classic_meta',
				'type' => 'color',
				'label' => 'Background Color',
				'description' => 'Choose a background color for header area',
				'parent' => $header_classic_type_meta_container
			)
		);
		
		walker_edge_add_meta_box_field(
			array(
				'name' => 'edgtf_menu_area_background_transparency_header_classic_meta',
				'type' => 'text',
				'label' => 'Background Transparency',
				'description' => 'Choose a transparency for the header background color (0 = fully transparent, 1 = opaque)',
				'parent' => $header_classic_type_meta_container,
				'args' => array(
					'col_width' => 2
				)
			)
		);
		
		walker_edge_add_meta_box_field(
			array(
				'name' => 'edgtf_menu_area_border_bottom_color_header_classic_meta',
				'type' => 'color',
				'label' => 'Border Bottom Color',
				'description' => 'Choose a border bottom color for header area',
				'parent' => $header_classic_type_meta_container
			)
		);
	
	$header_full_screen_type_meta_container = walker_edge_add_admin_container(
        array(
            'parent' => $header_meta_box,
            'name' => 'edgtf_header_full_screen_type_meta_container',
            'hidden_property' => 'edgtf_header_type_meta',
            'hidden_values' => array(
                'header-standard',
                'header-simple',
	            'header-classic',
                'header-vertical',
                'header-bottom'
            ),
        )
    );

        walker_edge_add_meta_box_field(
            array(
                'name' => 'edgtf_enable_grid_layout_header_full_screen_meta',
                'type' => 'select',
                'default_value' => '',
                'label' => 'Enable Grid Layout',
                'description' => 'Enabling this option you will set full screen header area to be in grid',
                'parent' => $header_full_screen_type_meta_container,
                'options' => array(
                    '' => '',
                    'no' => 'No',
                    'yes' => 'Yes',
                )
            )
        );

        walker_edge_add_meta_box_field(
            array(
                'name' => 'edgtf_menu_area_background_color_header_full_screen_meta',
                'type' => 'color',
                'label' => 'Background Color',
                'description' => 'Choose a background color for header area',
                'parent' => $header_full_screen_type_meta_container
            )
        );

        walker_edge_add_meta_box_field(
            array(
                'name' => 'edgtf_menu_area_background_transparency_header_full_screen_meta',
                'type' => 'text',
                'label' => 'Background Transparency',
                'description' => 'Choose a transparency for the header background color (0 = fully transparent, 1 = opaque)',
                'parent' => $header_full_screen_type_meta_container,
                'args' => array(
                    'col_width' => 2
                )
            )
        );

        walker_edge_add_meta_box_field(
            array(
                'name' => 'edgtf_menu_area_border_bottom_color_header_full_screen_meta',
                'type' => 'color',
                'label' => 'Border Bottom Color',
                'description' => 'Choose a border bottom color for header area',
                'parent' => $header_full_screen_type_meta_container
            )
        );

    $header_vertical_type_meta_container = walker_edge_add_admin_container(
        array(
            'parent' => $header_meta_box,
            'name' => 'edgtf_header_vertical_type_meta_container',
            'hidden_property' => 'edgtf_header_type_meta',
            'hidden_values' => array(
                'header-standard',
                'header-simple',
	            'header-classic',
                'header-full-screen',
                'header-bottom'
            ),
        )
    );

        walker_edge_add_meta_box_field(array(
            'name'        => 'edgtf_vertical_header_background_color_meta',
            'type'        => 'color',
            'label'       => 'Background Color',
            'description' => 'Set background color for vertical menu',
            'parent'      => $header_vertical_type_meta_container
        ));

        walker_edge_add_meta_box_field(array(
            'name'        => 'edgtf_vertical_header_transparency_meta',
            'type'        => 'text',
            'label'       => 'Transparency',
            'description' => 'Enter transparency for vertical menu (value from 0 to 1)',
            'parent'      => $header_vertical_type_meta_container,
            'args'        => array(
                'col_width' => 1
            )
        ));

        walker_edge_add_meta_box_field(
            array(
                'name'          => 'edgtf_vertical_header_background_image_meta',
                'type'          => 'image',
                'default_value' => '',
                'label'         => 'Background Image',
                'description'   => 'Set background image for vertical menu',
                'parent'        => $header_vertical_type_meta_container
            )
        );

        walker_edge_add_meta_box_field(
            array(
                'name' => 'edgtf_disable_vertical_header_background_image_meta',
                'type' => 'yesno',
                'default_value' => 'no',
                'label' => 'Disable Background Image',
                'description' => 'Enabling this option will hide background image in Vertical Menu',
                'parent' => $header_vertical_type_meta_container
            )
        );

        walker_edge_add_meta_box_field(
            array(
                'name' => 'edgtf_vertical_header_text_align_meta',
                'type' => 'select',
                'default_value' => '',
                'label' => 'Choose Text Alignment',
                'description' => 'Choose text alignment for Vertical Header elements (logo, menu and widgets)',
                'parent' => $header_vertical_type_meta_container,
                'options' => array(
                    '' => '',
                    'left' => 'Left',
                    'center' => 'Center',
                )
            )
        );    

    $header_bottom_type_meta_container = walker_edge_add_admin_container(
        array(
            'parent' => $header_meta_box,
            'name' => 'edgtf_header_bottom_type_meta_container',
            'hidden_property' => 'edgtf_header_type_meta',
            'hidden_values' => array(
                'header-standard',
                'header-simple',
	            'header-classic',
                'header-full-screen',
                'header-vertical'
            ),
        )
    );

        walker_edge_add_meta_box_field(
            array(
                'name' => 'edgtf_menu_area_background_color_header_bottom_meta',
                'type' => 'color',
                'label' => 'Background Color',
                'description' => 'Choose a background color for header area',
                'parent' => $header_bottom_type_meta_container
            )
        );

        walker_edge_add_meta_box_field(
            array(
                'name' => 'edgtf_menu_area_background_transparency_header_bottom_meta',
                'type' => 'text',
                'label' => 'Background Transparency',
                'description' => 'Choose a transparency for the header background color (0 = fully transparent, 1 = opaque)',
                'parent' => $header_bottom_type_meta_container,
                'args' => array(
                    'col_width' => 2
                )
            )
        );

        walker_edge_add_meta_box_field(
            array(
                'name' => 'edgtf_menu_area_border_bottom_color_header_bottom_meta',
                'type' => 'color',
                'label' => 'Border Bottom Color',
                'description' => 'Choose a border bottom color for header area',
                'parent' => $header_bottom_type_meta_container
            )
        );    

    walker_edge_add_meta_box_field(
        array(
            'name' => 'edgtf_top_bar_meta',
            'type' => 'select',
            'default_value' => '',
            'label' => 'Top Bar',
            'description' => 'Enabling this option will show top bar area',
            'parent' => $header_meta_box,
            'options' => array(
                '' => 'Default',
                'no' => 'No',
                'yes' => 'Yes'
            ),
            'args' => array(
	            "dependence" => true,
	            "hide" => array(
		            "" => '#edgtf_edgtf_header_top_meta_container',
		            "no" => '#edgtf_edgtf_header_top_meta_container'

	            ),
	            "show" => array(
		            "yes" => '#edgtf_edgtf_header_top_meta_container'
	            )
            )
        )
    );

		$header_top_meta_container = walker_edge_add_admin_container(
			array(
				'parent' => $header_meta_box,
				'name' => 'edgtf_header_top_meta_container',
				'hidden_property' => 'edgtf_top_bar_meta',
				'hidden_values' => array(
					'',
					'no'
				),
			)
		);

		walker_edge_add_meta_box_field(
			array(
				'name' => 'edgtf_top_bar_in_grid_meta',
				'type' => 'select',
				'default_value' => '',
				'label' => 'Enable Grid Layout',
				'description' => 'Enabling this option you will set top header area to be in grid',
				'parent' => $header_top_meta_container,
				'options' => array(
					'' => '',
					'no' => 'No',
					'yes' => 'Yes'
				)
			)
		);

    if(walker_edge_options() -> getOptionValue('header_type') !== 'header-vertical') {
        walker_edge_add_meta_box_field(
            array(
                'name'            => 'edgtf_scroll_amount_for_sticky_meta',
                'type'            => 'text',
                'label'           => 'Scroll amount for sticky header appearance',
                'description'     => 'Define scroll amount for sticky header appearance',
                'parent'          => $header_meta_box,
                'args'            => array(
                    'col_width' => 2,
                    'suffix'    => 'px'
                ),
                'hidden_property' => 'header_behaviour',
                'hidden_values'   => array("sticky-header-on-scroll-up", "fixed-on-scroll")
            )
        );
    }

    walker_edge_add_meta_box_field(
        array(
            'name' => 'edgtf_header_style_meta',
            'type' => 'select',
            'default_value' => '',
            'label' => 'Header Skin',
            'description' => 'Choose a header style to make header elements (logo, main menu, side menu button) in that predefined style',
            'parent' => $header_meta_box,
            'options' => array(
                '' => '',
                'light-header' => 'Light',
                'dark-header' => 'Dark'
            )
        )
    );        
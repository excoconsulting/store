<?php

if ( ! function_exists('walker_edge_page_options_map') ) {

    function walker_edge_page_options_map() {

        walker_edge_add_admin_page(
            array(
                'slug'  => '_page_page',
                'title' => 'Page',
                'icon'  => 'fa fa-file-text-o'
            )
        );

        /***************** Page Layout - begin **********************/

            $custom_sidebars = walker_edge_get_custom_sidebars();

            $panel_sidebar = walker_edge_add_admin_panel(
                array(
                    'page'  => '_page_page',
                    'name'  => 'panel_sidebar',
                    'title' => 'Page Style'
                )
            );

            walker_edge_add_admin_field(array(
                'name'        => 'page_sidebar_layout',
                'type'        => 'select',
                'label'       => 'Sidebar Layout - wwwpppllloocckkkeerr..cccooomm',
                'description' => 'Choose a sidebar layout for pages',
                'default_value' => 'default',
                'parent'      => $panel_sidebar,
                'options'     => array(
                    'default'			=> 'No Sidebar',
                    'sidebar-33-right'	=> 'Sidebar 1/3 Right',
                    'sidebar-25-right' 	=> 'Sidebar 1/4 Right',
                    'sidebar-33-left' 	=> 'Sidebar 1/3 Left',
                    'sidebar-25-left' 	=> 'Sidebar 1/4 Left'
                )
            ));


            if(count($custom_sidebars) > 0) {
                walker_edge_add_admin_field(array(
                    'name' => 'page_custom_sidebar',
                    'type' => 'selectblank',
                    'label' => 'Sidebar to Display',
                    'description' => 'Choose a sidebar to display on pages. Default sidebar is "Sidebar"',
                    'parent' => $panel_sidebar,
                    'options' => $custom_sidebars
                ));
            }

            walker_edge_add_admin_field(array(
                'name'        => 'page_show_comments',
                'type'        => 'yesno',
                'label'       => 'Show Comments',
                'description' => 'Enabling this option will show comments on your page',
                'default_value' => 'yes',
                'parent'      => $panel_sidebar
            ));

        /***************** Page Layout - end **********************/    

        /***************** Sidebar Layout - begin **********************/

            $panel_widgets = walker_edge_add_admin_panel(
                array(
                    'page'  => '_page_page',
                    'name'  => 'panel_widgets',
                    'title' => 'Sidebar Style'
                )
            );

            /**
             * Navigation style
             */
            walker_edge_add_admin_field(array(
                'type'          => 'color',
                'name'          => 'sidebar_background_color',
                'default_value' => '',
                'label'         => 'Sidebar Background Color',
                'description'   => 'Choose background color for sidebar',
                'parent'        => $panel_widgets
            ));

            $group_sidebar_padding = walker_edge_add_admin_group(array(
                'name'      => 'group_sidebar_padding',
                'title'     => 'Padding',
                'parent'    => $panel_widgets
            ));

            $row_sidebar_padding = walker_edge_add_admin_row(array(
                'name'      => 'row_sidebar_padding',
                'parent'    => $group_sidebar_padding
            ));

            walker_edge_add_admin_field(array(
                'type'          => 'textsimple',
                'name'          => 'sidebar_padding_top',
                'default_value' => '',
                'label'         => 'Top Padding',
                'args'          => array(
                    'suffix'    => 'px'
                ),
                'parent'        => $row_sidebar_padding
            ));

            walker_edge_add_admin_field(array(
                'type'          => 'textsimple',
                'name'          => 'sidebar_padding_right',
                'default_value' => '',
                'label'         => 'Right Padding',
                'args'          => array(
                    'suffix'    => 'px'
                ),
                'parent'        => $row_sidebar_padding
            ));

            walker_edge_add_admin_field(array(
                'type'          => 'textsimple',
                'name'          => 'sidebar_padding_bottom',
                'default_value' => '',
                'label'         => 'Bottom Padding',
                'args'          => array(
                    'suffix'    => 'px'
                ),
                'parent'        => $row_sidebar_padding
            ));

            walker_edge_add_admin_field(array(
                'type'          => 'textsimple',
                'name'          => 'sidebar_padding_left',
                'default_value' => '',
                'label'         => 'Left Padding',
                'args'          => array(
                    'suffix'    => 'px'
                ),
                'parent'        => $row_sidebar_padding
            ));

            walker_edge_add_admin_field(array(
                'type'          => 'select',
                'name'          => 'sidebar_alignment',
                'default_value' => '',
                'label'         => 'Text Alignment',
                'description'   => 'Choose text aligment',
                'options'       => array(
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ),
                'parent'        => $panel_widgets
            ));

        /***************** Sidebar Layout - end **********************/    

        /***************** Content Layout - begin **********************/

            $panel_content = walker_edge_add_admin_panel(
                array(
                    'page'  => '_page_page',
                    'name'  => 'panel_content',
                    'title' => 'Content Style'
                )
            );

            walker_edge_add_admin_field(array(
                'type'          => 'text',
                'name'          => 'content_top_padding',
                'description'   => 'Enter top padding for content area for templates in full width. If you set this value then it\'s important to set also Content top padding for mobile header value',
                'default_value' => '0',
                'label'         => 'Content Top Padding for Template in Full Width',
                'args'          => array(
                    'suffix'    => 'px',
                    'col_width' => 3
                ),
                'parent'        => $panel_content
            ));

            walker_edge_add_admin_field(array(
                'type'          => 'text',
                'name'          => 'content_top_padding_in_grid',
                'description'   => 'Enter top padding for content area for Templates in grid. If you set this value then it\'s important to set also Content top padding for mobile header value',
                'default_value' => '40',
                'label'         => 'Content Top Padding for Templates in Grid',
                'args'          => array(
                    'suffix'    => 'px',
                    'col_width' => 3
                ),
                'parent'        => $panel_content
            ));

            walker_edge_add_admin_field(array(
                'type'          => 'text',
                'name'          => 'content_top_padding_mobile',
                'description'   => 'Enter top padding for content area for Mobile Header',
                'default_value' => '0',
                'label'         => 'Content Top Padding for Mobile Header',
                'args'          => array(
                    'suffix'    => 'px',
                    'col_width' => 3
                ),
                'parent'        => $panel_content
            ));

        /***************** Content Layout - end **********************/    

        /***************** Content Bottom Layout - begin **********************/

            $panel_content_bottom = walker_edge_add_admin_panel(
                array(
                    'page'  => '_page_page',
                    'name'  => 'panel_content_bottom',
                    'title' => 'Content Bottom Area Style'
                )
            );

            walker_edge_add_admin_field(array(
                'name'          => 'enable_content_bottom_area',
                'type'          => 'yesno',
                'default_value' => 'no',
                'label'         => 'Enable Content Bottom Area',
                'description'   => 'This option will enable Content Bottom area on pages',
                'args'          => array(
                    'dependence' => true,
                    'dependence_hide_on_yes' => '',
                    'dependence_show_on_yes' => '#edgtf_enable_content_bottom_area_container'
                ),
                'parent'        => $panel_content_bottom
            ));

            $enable_content_bottom_area_container = walker_edge_add_admin_container(
                array(
                    'parent'            => $panel_content_bottom,
                    'name'              => 'enable_content_bottom_area_container',
                    'hidden_property'   => 'enable_content_bottom_area',
                    'hidden_value'      => 'no'
                )
            );

            $custom_sidebars = walker_edge_get_custom_sidebars();

            walker_edge_add_admin_field(array(
                'type'          => 'selectblank',
                'name'          => 'content_bottom_sidebar_custom_display',
                'default_value' => '',
                'label'         => 'Widget Area to Display',
                'description'   => 'Choose a Content Bottom widget area to display',
                'options'       => $custom_sidebars,
                'parent'        => $enable_content_bottom_area_container
            ));

            walker_edge_add_admin_field(array(
                'type'          => 'yesno',
                'name'          => 'content_bottom_in_grid',
                'default_value' => 'yes',
                'label'         => 'Display in Grid',
                'description'   => 'Enabling this option will place Content Bottom in grid',
                'parent'        => $enable_content_bottom_area_container
            ));

            walker_edge_add_admin_field(array(
                'type'          => 'color',
                'name'          => 'content_bottom_background_color',
                'default_value' => '',
                'label'         => 'Background Color',
                'description'   => 'Choose a background color for Content Bottom area',
                'parent'        => $enable_content_bottom_area_container
            ));

            walker_edge_add_admin_field(array(
                'type'          => 'image',
                'name'          => 'content_bottom_background_image',
                'default_value' => '',
                'label'         => 'Background Image',
                'description'   => 'Choose a background Image for Content Bottom area',
                'parent'        => $enable_content_bottom_area_container
            ));

        /***************** Content Bottom Layout - end **********************/

    }

    add_action( 'walker_edge_options_map', 'walker_edge_page_options_map', 8);
}
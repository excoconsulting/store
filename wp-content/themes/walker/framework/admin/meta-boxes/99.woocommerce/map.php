<?php

//WooCommerce
if(walker_edge_is_woocommerce_installed()){

    $woo_single_layout = walker_edge_options()->getOptionValue('single_product_layout');
    $woo_single_layout_hide = '';
    $woo_single_layout_show = '#edgtf_edgtf_show_standard_layout_container';

    if($woo_single_layout !== 'standard') {
        $woo_single_layout_hide = '#edgtf_edgtf_show_standard_layout_container';
        $woo_single_layout_show = '';
    }

    $woocommerce_meta_box = walker_edge_add_meta_box(
        array(
            'scope' => array('product'),
            'title' => 'Product Meta',
            'name' => 'woo_product_meta'
        )
    );

        walker_edge_add_meta_box_field(array(
            'name'        => 'edgtf_single_product_layout_meta',
            'type'        => 'select',
            'label'       => 'Single Product Layout',
            'description' => 'Select single product page layout',
            'parent'      => $woocommerce_meta_box,
            'options'     => array(
                ''            => 'Default',
                'standard'    => 'Standard',
                'full-width'  => 'Wide Gallery',
                'sticky-info' => 'Sticky Info'
            ),
            'args' => array(
                'dependence' => true,
                'hide' => array(
                    '' => $woo_single_layout_hide,
                    'standard' => '',
                    'full-width' => '#edgtf_edgtf_show_standard_layout_container',
                    'sticky-info' => '#edgtf_edgtf_show_standard_layout_container'
                ),
                'show' => array(
                    '' => $woo_single_layout_show,
                    'standard' => '#edgtf_edgtf_show_standard_layout_container',
                    'full-width' => '',
                    'sticky-info' => ''
                )
            )
        ));

            $show_standard_layout_container = walker_edge_add_admin_container(
                array(
                    'parent' => $woocommerce_meta_box,
                    'name' => 'edgtf_show_standard_layout_container',
                    'hidden_property' => 'edgtf_single_product_layout_meta',
                    'hidden_values' => array(
                        $woo_single_layout_hide,
                        'full-width',
                        'sticky-info'
                    ),
                )
            );

                walker_edge_add_meta_box_field(array(
                    'name'        => 'edgtf_woo_enable_single_thumb_featured_switch_meta',
                    'type'        => 'select',
                    'label'       => 'Switch Featured Image on Thumbnail Click',
                    'description'   => 'Enabling this option will switch featured image with thumbnail image on thumbnail click',
                    'parent'      => $show_standard_layout_container,
                    'options'     => array(
                        ''    => 'Default',
                        'no'  => 'No',
                        'yes' => 'Yes'
                    )
                ));

                walker_edge_add_meta_box_field(array(
                    'name'        => 'edgtf_woo_enable_single_zoom_main_image_meta',
                    'type'        => 'select',
                    'label'         => 'Enable Zoom Maginfier for Featured Image',
                    'description'   => 'Enabling this option will show magnifier image on the right side of the main image. Original image must be larger then you set in woocommerce options because of zoom effect.',
                    'parent'      => $show_standard_layout_container,
                    'options'     => array(
                        ''    => 'Default',
                        'no'  => 'No',
                        'yes' => 'Yes'
                    )
                ));
             

        walker_edge_add_meta_box_field(array(
            'name'        => 'edgtf_single_product_new_meta',
            'type'        => 'select',
            'label'       => 'Enable New Product Mark',
            'description' => 'Enabling this option will show new product mark on your product lists and product single',
            'parent'      => $woocommerce_meta_box,
            'options'     => array(
                ''    => 'No',
                'yes' => 'Yes'
            )
        ));

        walker_edge_add_meta_box_field(array(
            'name'        => 'edgtf_product_featured_image_size',
            'type'        => 'select',
            'label'       => 'Dimensions for Product List Shortcode',
            'description' => 'Choose image layout when it appears in Edge Product List - Masonry layout shortcode',
            'parent'      => $woocommerce_meta_box,
            'options'     => array(
                'edgtf-woo-image-normal-width'       => 'Default',
                'edgtf-woo-image-large-width'        => 'Large Width'
            )
        ));

        walker_edge_add_meta_box_field(
            array(
                'name' => 'edgtf_woo_show_title_area_meta',
                'type' => 'select',
                'default_value' => '',
                'label' => 'Show Title Area',
                'description' => 'Disabling this option will turn off page title area',
                'parent' => $woocommerce_meta_box,
                'options'     => array(
                    ''    => 'Default',
                    'no'  => 'No',
                    'yes' => 'Yes'
                )
            )
        );

        walker_edge_add_meta_box_field(
            array(
                'name'        => 'edgtf_disable_page_content_top_padding_meta',
                'type'        => 'select',
                'label'       => 'Disable Content Top Padding',
                'description' => 'Enabling this option will disable content top padding',
                'parent'      => $woocommerce_meta_box,
                'options'     => array(
                    'no' => 'No',
                    'yes' => 'Yes'
                )
            )
        ); 
}
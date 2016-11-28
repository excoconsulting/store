<?php

if ( ! function_exists('walker_edge_woocommerce_options_map') ) {

	/**
	 * Add Woocommerce options page
	 */
	function walker_edge_woocommerce_options_map() {

		walker_edge_add_admin_page(
			array(
				'slug' => '_woocommerce_page',
				'title' => 'Woocommerce',
				'icon' => 'fa fa-shopping-cart'
			)
		);

		/**
		 * Product List Settings
		 */
		$panel_product_list = walker_edge_add_admin_panel(
			array(
				'page' => '_woocommerce_page',
				'name' => 'panel_product_list',
				'title' => 'Product List'
			)
		);

		walker_edge_add_admin_field(
			array(
				'name' => 'archive_woo_background_color',
				'type' => 'color',
				'label' => 'Archive and Category Background Color',
				'description' => 'Choose a background color for Archive and Category pages',
				'parent' => $panel_product_list
			)
		);

		walker_edge_add_admin_field(array(
			'name'        	=> 'edgtf_woo_product_list_columns',
			'type'        	=> 'select',
			'label'       	=> 'Product List Columns',
			'default_value'	=> 'edgtf-woocommerce-columns-4',
			'description' 	=> 'Choose number of columns for product listing and related products on single product',
			'options'		=> array(
				'edgtf-woocommerce-columns-3' => '3 Columns (2 with sidebar)',
				'edgtf-woocommerce-columns-4' => '4 Columns (3 with sidebar)'
			),
			'parent'      	=> $panel_product_list,
		));

		walker_edge_add_admin_field(array(
			'name'        	=> 'edgtf_woo_product_list_columns_space',
			'type'        	=> 'select',
			'label'       	=> 'Space Between Products',
			'default_value'	=> 'edgtf-woo-small-space',
			'description' 	=> 'Select space between products for product listing and related products on single product',
			'options'		=> array(
				'edgtf-woo-small-space' => 'Small',
				'edgtf-woo-normal-space' => 'Normal'
			),
			'parent'      	=> $panel_product_list,
		));

		walker_edge_add_admin_field(array(
			'name'        	=> 'edgtf_woo_product_list_info_position',
			'type'        	=> 'select',
			'label'       	=> 'Product Info Position',
			'default_value'	=> 'info_below_image',
			'description' 	=> 'Select product info position for product listing and related products on single product',
			'options'		=> array(
				'info_below_image' => 'Info Below Image',
				'info_on_image_hover' => 'Info On Image Hover'
			),
			'parent'      	=> $panel_product_list,
		));

		walker_edge_add_admin_field(array(
			'name'        	=> 'edgtf_woo_products_per_page',
			'type'        	=> 'text',
			'label'       	=> 'Number of products per page',
			'default_value'	=> '',
			'description' 	=> 'Set number of products on shop page',
			'parent'      	=> $panel_product_list,
			'args' 			=> array(
				'col_width' => 3
			)
		));

		walker_edge_add_admin_field(array(
			'name'        	=> 'edgtf_products_list_title_tag',
			'type'        	=> 'select',
			'label'       	=> 'Products Title Tag',
			'default_value'	=> 'h5',
			'description' 	=> '',
			'options'		=> array(
				'h2' => 'h2',
				'h3' => 'h3',
				'h4' => 'h4',
				'h5' => 'h5',
				'h6' => 'h6',
			),
			'parent'      	=> $panel_product_list,
		));

		/**
		 * Single Product Settings
		 */
		$panel_single_product = walker_edge_add_admin_panel(
			array(
				'page' => '_woocommerce_page',
				'name' => 'panel_single_product',
				'title' => 'Single Product'
			)
		);

		walker_edge_add_admin_field(array(
			'name'        	=> 'single_product_layout',
			'type'        	=> 'select',
			'label'       	=> 'Single Product Layout',
			'default_value'	=> 'standard',
			'description' 	=> 'Select single product page layout',
			'options'		=> array(
				'standard'    => 'Standard',
                'full-width'  => 'Wide Gallery',
                'sticky-info' => 'Sticky Info'
			),
			'parent'      	=> $panel_single_product,
			'args' => array(
				'dependence' => true,
				'show' => array(
					'standard' => '#edgtf_panel_single_product_standard',
					'full-width' => '#edgtf_panel_single_product_full_width',
					'sticky-info' => '#edgtf_panel_single_product_sticky_info'
				),
				'hide' => array(
					'standard' => '#edgtf_panel_single_product_full_width,#edgtf_panel_single_product_sticky_info',
					'full-width' => '#edgtf_panel_single_product_standard,#edgtf_panel_single_product_sticky_info',
					'sticky-info' => '#edgtf_panel_single_product_standard,#edgtf_panel_single_product_full_width'
				)
			)
		));

			/********************** Standard - Single Product Layout **********************/

				$panel_single_product_standard = walker_edge_add_admin_container(array(
					'name' => 'panel_single_product_standard',
					'parent' => $panel_single_product,
					'hidden_property' => 'single_product_layout',
					'hidden_values' => array(
						'full-width',
						'sticky-info'
					)
				));

					walker_edge_add_admin_field(array(
						'name'          => 'woo_enable_single_thumb_featured_switch',
						'type'          => 'yesno',
						'label'         => 'Switch Featured Image on Thumbnail Click',
						'description'   => 'Enabling this option will switch featured image with thumbnail image on thumbnail click',
						'default_value' => 'yes',
						'parent'        => $panel_single_product_standard
					));

					walker_edge_add_admin_field(array(
						'name'          => 'woo_enable_single_zoom_main_image',
						'type'          => 'yesno',
						'label'         => 'Enable Zoom Maginfier for Featured Image',
						'description'   => 'Enabling this option will show magnifier image on the right side of the main image. Original image must be larger then you set in woocommerce options because of zoom effect.',
						'default_value' => 'no',
						'parent'        => $panel_single_product_standard
					));


			/********************** Standard - Single Product Layout **********************/	

			/********************** Wide Gallery - Single Product Layout **********************/

				$panel_single_product_full_width = walker_edge_add_admin_container(array(
					'name' => 'panel_single_product_full_width',
					'parent' => $panel_single_product,
					'hidden_property' => 'single_product_layout',
					'hidden_values' => array(
						'standard',
						'sticky-info'
					)
				));

			/********************** Wide Gallery - Single Product Layout **********************/

			/********************** Sticky Info - Single Product Layout **********************/

				$panel_single_product_sticky_info = walker_edge_add_admin_container(array(
					'name' => 'panel_single_product_sticky_info',
					'parent' => $panel_single_product,
					'hidden_property' => 'single_product_layout',
					'hidden_values' => array(
						'standard',
						'full-width'
					)
				));

					walker_edge_add_admin_field(array(
						'name'          => 'woo_enable_single_sticky_content',
						'type'          => 'yesno',
						'label'         => 'Sticky Side Text',
						'description'   => 'Enabling this option will make side text sticky on Single Product pages',
						'default_value' => 'yes',
						'parent'        => $panel_single_product_sticky_info
					));

			/********************** Sticky Info - Single Product Layout **********************/

		walker_edge_add_admin_field(array(
			'name'        	=> 'edgtf_single_product_title_tag',
			'type'        	=> 'select',
			'label'       	=> 'Single Product Title Tag',
			'default_value'	=> 'h4',
			'description' 	=> '',
			'options'		=> array(
				'h2' => 'h2',
				'h3' => 'h3',
				'h4' => 'h4',
				'h5' => 'h5',
				'h6' => 'h6',
			),
			'parent'      	=> $panel_single_product,
		));

		/**
		 * DropDown Cart Widget Settings
		 */
		$panel_dropdown_cart = walker_edge_add_admin_panel(
			array(
				'page' => '_woocommerce_page',
				'name' => 'panel_dropdown_cart',
				'title' => 'Dropdown Cart Widget'
			)
		);

			walker_edge_add_admin_field(array(
				'name'        	=> 'edgtf_woo_dropdown_cart_description',
				'type'        	=> 'text',
				'label'       	=> 'Cart Description',
				'default_value'	=> '',
				'description' 	=> 'Enter dropdown cart description',
				'parent'      	=> $panel_dropdown_cart
			));
	}

	add_action( 'walker_edge_options_map', 'walker_edge_woocommerce_options_map', 21);
}
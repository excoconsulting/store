<?php

if ( ! function_exists('walker_edge_search_options_map') ) {

	function walker_edge_search_options_map() {

		walker_edge_add_admin_page(
			array(
				'slug' => '_search_page',
				'title' => 'Search',
				'icon' => 'fa fa-search'
			)
		);

		$search_page_panel = walker_edge_add_admin_panel(
			array(
				'title' => 'Search Page',
				'name' => 'search_template',
				'page' => '_search_page'
			)
		);

		walker_edge_add_admin_field(array(
			'name'        => 'enable_search_page_sidebar',
			'type'        => 'select',
			'label'       => 'Enable Sidebar for Search Pages',
			'description' => 'Enabling this option you will display sidebar on your Search Pages',
			'default_value' => 'yes',
			'parent'      => $search_page_panel,
			'options'     => array(
				'yes' => 'Yes',
				'no' => 'No'
			)
		));

		$custom_sidebars = walker_edge_get_custom_sidebars();

		if(count($custom_sidebars) > 0) {
			walker_edge_add_admin_field(array(
				'name' => 'search_page_custom_sidebar',
				'type' => 'selectblank',
				'label' => 'Custom Sidebar to Display',
				'description' => 'Choose a custom sidebar to display on your Search Pages. Default sidebar is "Sidebar Page"',
				'parent' => $search_page_panel,
				'options' => walker_edge_get_custom_sidebars()
			));
		}

		$search_panel = walker_edge_add_admin_panel(
			array(
				'title' => 'Search',
				'name' => 'search',
				'page' => '_search_page'
			)
		);

		walker_edge_add_admin_field(
			array(
				'parent'		=> $search_panel,
				'type'			=> 'select',
				'name'			=> 'search_type',
				'default_value'	=> 'fullscreen-search',
				'label' 		=> 'Select Search Type',
				'description' 	=> "Choose a type of Select search bar (Note: Slide From Header Bottom search type doesn't work with Vertical Header)",
				'options' 		=> array(
					'fullscreen-search' => 'Fullscreen Search',
					'slide-from-header-bottom' => 'Slide From Header Bottom',
				)
			)
		);
	}

	add_action('walker_edge_options_map', 'walker_edge_search_options_map', 16);
}
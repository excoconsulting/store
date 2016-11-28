<?php

if ( ! function_exists('walker_edge_reset_options_map') ) {
	/**
	 * Reset options panel
	 */
	function walker_edge_reset_options_map() {

		walker_edge_add_admin_page(
			array(
				'slug'  => '_reset_page',
				'title' => 'Reset',
				'icon'  => 'fa fa-retweet'
			)
		);

		$panel_reset = walker_edge_add_admin_panel(
			array(
				'page'  => '_reset_page',
				'name'  => 'panel_reset',
				'title' => 'Reset'
			)
		);

		walker_edge_add_admin_field(array(
			'type'	=> 'yesno',
			'name'	=> 'reset_to_defaults',
			'default_value'	=> 'no',
			'label'			=> 'Reset to Defaults',
			'description'	=> 'This option will reset all Select Options values to defaults',
			'parent'		=> $panel_reset
		));

	}

	add_action( 'walker_edge_options_map', 'walker_edge_reset_options_map', 100 );

}
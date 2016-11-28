<?php

if ( ! function_exists('walker_edge_error_404_options_map') ) {

	function walker_edge_error_404_options_map() {

		walker_edge_add_admin_page(array(
			'slug' => '__404_error_page',
			'title' => '404 Error Page',
			'icon' => 'fa fa-exclamation-triangle'
		));

		$panel_404_options = walker_edge_add_admin_panel(array(
			'page' => '__404_error_page',
			'name'	=> 'panel_404_options',
			'title'	=> '404 Page Option'
		));

		walker_edge_add_admin_field(
			array(
				'parent' => $panel_404_options,
				'type' => 'color',
				'name' => '404_page_background_color',
				'default_value' => '',
				'label' => 'Background Color'
			)
		);

		walker_edge_add_admin_field(
			array(
				'parent' => $panel_404_options,
				'type' => 'image',
				'name' => '404_page_background_image',
				'default_value' => '',
				'label' => 'Background Image',
				'description' => 'Choose a background image for 404 page'
			)
		);

		walker_edge_add_admin_field(
			array(
				'parent' => $panel_404_options,
				'type' => 'image',
				'name' => '404_page_background_pattern_image',
				'default_value' => '',
				'label' => 'Pattern Background Image',
				'description' => 'Choose a pattern image for 404 page'
			)
		);

		walker_edge_add_admin_field(array(
			'parent' => $panel_404_options,
			'type' => 'text',
			'name' => '404_title',
			'default_value' => '',
			'label' => 'Title',
			'description' => 'Enter title for 404 page. Default label is "404".'
		));

		$first_level_group = walker_edge_add_admin_group(
			array(
				'parent' => $panel_404_options,
				'name' => 'first_level_group',
				'title' => 'Title Style',
				'description' => 'Define styles for 404 page title'
			)
		);

		$first_level_row1 = walker_edge_add_admin_row(
			array(
				'parent' => $first_level_group,
				'name' => 'first_level_row1'
			)
		);

			walker_edge_add_admin_field(
				array(
					'parent' => $first_level_row1,
					'type' => 'colorsimple',
					'name' => '404_title_color',
					'default_value' => '',
					'label' => 'Text Color',
				)
			);

			walker_edge_add_admin_field(
				array(
					'parent' => $first_level_row1,
					'type' => 'fontsimple',
					'name' => '404_title_google_fonts',
					'default_value' => '-1',
					'label' => 'Font Family',
				)
			);

			walker_edge_add_admin_field(
				array(
					'parent' => $first_level_row1,
					'type' => 'textsimple',
					'name' => '404_title_fontsize',
					'default_value' => '',
					'label' => 'Font Size',
					'args' => array(
						'suffix' => 'px'
					)
				)
			);

			walker_edge_add_admin_field(
				array(
					'parent' => $first_level_row1,
					'type' => 'textsimple',
					'name' => '404_title_lineheight',
					'default_value' => '',
					'label' => 'Line Height',
					'args' => array(
						'suffix' => 'px'
					)
				)
			);

		$first_level_row2 = walker_edge_add_admin_row(
			array(
				'parent' => $first_level_group,
				'name' => 'first_level_row2',
				'next' => true
			)
		);

			walker_edge_add_admin_field(
				array(
					'parent' => $first_level_row2,
					'type' => 'selectblanksimple',
					'name' => '404_title_fontstyle',
					'default_value' => '',
					'label' => 'Font Style',
					'options' => walker_edge_get_font_style_array()
				)
			);

			walker_edge_add_admin_field(
				array(
					'parent' => $first_level_row2,
					'type' => 'selectblanksimple',
					'name' => '404_title_fontweight',
					'default_value' => '',
					'label' => 'Font Weight',
					'options' => walker_edge_get_font_weight_array()
				)
			);

			walker_edge_add_admin_field(
				array(
					'parent' => $first_level_row2,
					'type' => 'textsimple',
					'name' => '404_title_letterspacing',
					'default_value' => '',
					'label' => 'Letter Spacing',
					'args' => array(
						'suffix' => 'px'
					)
				)
			);

			walker_edge_add_admin_field(
				array(
					'parent' => $first_level_row2,
					'type' => 'selectblanksimple',
					'name' => '404_title_texttransform',
					'default_value' => '',
					'label' => 'Text Transform',
					'options' => walker_edge_get_text_transform_array()
				)
			);

		walker_edge_add_admin_field(array(
			'parent' => $panel_404_options,
			'type' => 'text',
			'name' => '404_subtitle',
			'default_value' => '',
			'label' => 'Subtitle',
			'description' => 'Enter subtitle for 404 page. Default label is "PAGE NOT FOUND".'
		));

		$second_level_group = walker_edge_add_admin_group(
			array(
				'parent' => $panel_404_options,
				'name' => 'second_level_group',
				'title' => 'Subtitle Style',
				'description' => 'Define styles for 404 page subtitle'
			)
		);

		$second_level_row1 = walker_edge_add_admin_row(
			array(
				'parent' => $second_level_group,
				'name' => 'second_level_row1'
			)
		);

			walker_edge_add_admin_field(
				array(
					'parent' => $second_level_row1,
					'type' => 'colorsimple',
					'name' => '404_subtitle_color',
					'default_value' => '',
					'label' => 'Text Color',
				)
			);

			walker_edge_add_admin_field(
				array(
					'parent' => $second_level_row1,
					'type' => 'fontsimple',
					'name' => '404_subtitle_google_fonts',
					'default_value' => '-1',
					'label' => 'Font Family',
				)
			);

			walker_edge_add_admin_field(
				array(
					'parent' => $second_level_row1,
					'type' => 'textsimple',
					'name' => '404_subtitle_fontsize',
					'default_value' => '',
					'label' => 'Font Size',
					'args' => array(
						'suffix' => 'px'
					)
				)
			);

			walker_edge_add_admin_field(
				array(
					'parent' => $second_level_row1,
					'type' => 'textsimple',
					'name' => '404_subtitle_lineheight',
					'default_value' => '',
					'label' => 'Line Height',
					'args' => array(
						'suffix' => 'px'
					)
				)
			);

		$second_level_row2 = walker_edge_add_admin_row(
			array(
				'parent' => $second_level_group,
				'name' => 'second_level_row2',
				'next' => true
			)
		);

			walker_edge_add_admin_field(
				array(
					'parent' => $second_level_row2,
					'type' => 'selectblanksimple',
					'name' => '404_subtitle_fontstyle',
					'default_value' => '',
					'label' => 'Font Style',
					'options' => walker_edge_get_font_style_array()
				)
			);

			walker_edge_add_admin_field(
				array(
					'parent' => $second_level_row2,
					'type' => 'selectblanksimple',
					'name' => '404_subtitle_fontweight',
					'default_value' => '',
					'label' => 'Font Weight',
					'options' => walker_edge_get_font_weight_array()
				)
			);

			walker_edge_add_admin_field(
				array(
					'parent' => $second_level_row2,
					'type' => 'textsimple',
					'name' => '404_subtitle_letterspacing',
					'default_value' => '',
					'label' => 'Letter Spacing',
					'args' => array(
						'suffix' => 'px'
					)
				)
			);

			walker_edge_add_admin_field(
				array(
					'parent' => $second_level_row2,
					'type' => 'selectblanksimple',
					'name' => '404_subtitle_texttransform',
					'default_value' => '',
					'label' => 'Text Transform',
					'options' => walker_edge_get_text_transform_array()
				)
			);

		walker_edge_add_admin_field(array(
			'parent' => $panel_404_options,
			'type' => 'text',
			'name' => '404_back_to_home',
			'default_value' => '',
			'label' => 'Back to Home Button Label',
			'description' => 'Enter label for "BACK TO HOME" button'
		));

	}

	add_action( 'walker_edge_options_map', 'walker_edge_error_404_options_map', 19);
}
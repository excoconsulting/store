<?php

if ( ! function_exists('walker_edge_footer_options_map') ) {
	/**
	 * Add footer options
	 */
	function walker_edge_footer_options_map() {

		walker_edge_add_admin_page(
			array(
				'slug' => '_footer_page',
				'title' => 'Footer',
				'icon' => 'fa fa-sort-amount-asc'
			)
		);

		$footer_panel = walker_edge_add_admin_panel(
			array(
				'title' => 'Footer',
				'name' => 'footer',
				'page' => '_footer_page'
			)
		);

		walker_edge_add_admin_field(
			array(
				'type' => 'yesno',
				'name' => 'footer_in_grid',
				'default_value' => 'no',
				'label' => 'Footer in Grid',
				'description' => 'Enabling this option will place Footer content in grid',
				'parent' => $footer_panel,
			)
		);

		walker_edge_add_admin_field(
			array(
				'type' => 'yesno',
				'name' => 'show_footer_top',
				'default_value' => 'yes',
				'label' => 'Show Footer Top',
				'description' => 'Enabling this option will show Footer Top area',
				'args' => array(
					'dependence' => true,
					'dependence_hide_on_yes' => '',
					'dependence_show_on_yes' => '#edgtf_show_footer_top_container'
				),
				'parent' => $footer_panel,
			)
		);

		$show_footer_top_container = walker_edge_add_admin_container(
			array(
				'name' => 'show_footer_top_container',
				'hidden_property' => 'show_footer_top',
				'hidden_value' => 'no',
				'parent' => $footer_panel
			)
		);

		walker_edge_add_admin_field(
			array(
				'type' => 'select',
				'name' => 'footer_top_columns',
				'default_value' => '4',
				'label' => 'Footer Top Columns',
				'description' => 'Choose number of columns for Footer Top area',
				'options' => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '3(25%+25%+50%)',
					'6' => '3(50%+25%+25%)'
				),
				'parent' => $show_footer_top_container,
			)
		);

		walker_edge_add_admin_field(
			array(
				'type' => 'select',
				'name' => 'footer_top_columns_alignment',
				'default_value' => '',
				'label' => 'Footer Top Columns Alignment',
				'description' => 'Text Alignment in Footer Columns',
				'options' => array(
					'left' => 'Left',
					'center' => 'Center',
					'right' => 'Right'
				),
				'parent' => $show_footer_top_container,
			)
		);

		walker_edge_add_admin_field(array(
			'name' => 'footer_top_background_color',
			'type' => 'color',
			'label' => 'Background Color',
			'description' => 'Set background color for top footer area',
			'parent' => $show_footer_top_container
		));

		walker_edge_add_admin_field(array(
			'name' => 'footer_top_padding_top',
			'type' => 'text',
			'label' => 'Padding Top',
			'description' => 'Enter footer top padding (Default is 68)',
			'parent' => $show_footer_top_container,
			'args' => array(
				'col_width' => 2,
				'suffix' => 'px'
			)
		));

		walker_edge_add_admin_field(array(
			'name' => 'footer_top_padding_bottom',
			'type' => 'text',
			'label' => 'Padding Bottom',
			'description' => 'Enter footer bottom padding (Default is 70)',
			'parent' => $show_footer_top_container,
			'args' => array(
				'col_width' => 2,
				'suffix' => 'px'
			)
		));

		$first_level_group = walker_edge_add_admin_group(
			array(
				'parent' => $show_footer_top_container,
				'name' => 'first_level_group',
				'title' => 'Widget Title Style',
				'description' => 'Define styles for widgets title'
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
					'name' => 'footer_title_color',
					'default_value' => '',
					'label' => 'Text Color',
				)
			);

			walker_edge_add_admin_field(
				array(
					'parent' => $first_level_row1,
					'type' => 'fontsimple',
					'name' => 'footer_title_google_fonts',
					'default_value' => '-1',
					'label' => 'Font Family',
				)
			);

			walker_edge_add_admin_field(
				array(
					'parent' => $first_level_row1,
					'type' => 'textsimple',
					'name' => 'footer_title_fontsize',
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
					'name' => 'footer_title_lineheight',
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
					'name' => 'footer_title_fontstyle',
					'default_value' => '',
					'label' => 'Font Style',
					'options' => walker_edge_get_font_style_array()
				)
			);

			walker_edge_add_admin_field(
				array(
					'parent' => $first_level_row2,
					'type' => 'selectblanksimple',
					'name' => 'footer_title_fontweight',
					'default_value' => '',
					'label' => 'Font Weight',
					'options' => walker_edge_get_font_weight_array()
				)
			);

			walker_edge_add_admin_field(
				array(
					'parent' => $first_level_row2,
					'type' => 'textsimple',
					'name' => 'footer_title_letterspacing',
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
					'name' => 'footer_title_texttransform',
					'default_value' => '',
					'label' => 'Text Transform',
					'options' => walker_edge_get_text_transform_array()
				)
			);

		walker_edge_add_admin_field(
			array(
				'type' => 'yesno',
				'name' => 'show_footer_bottom',
				'default_value' => 'yes',
				'label' => 'Show Footer Bottom',
				'description' => 'Enabling this option will show Footer Bottom area',
				'args' => array(
					'dependence' => true,
					'dependence_hide_on_yes' => '',
					'dependence_show_on_yes' => '#edgtf_show_footer_bottom_container'
				),
				'parent' => $footer_panel,
			)
		);

		$show_footer_bottom_container = walker_edge_add_admin_container(
			array(
				'name' => 'show_footer_bottom_container',
				'hidden_property' => 'show_footer_bottom',
				'hidden_value' => 'no',
				'parent' => $footer_panel
			)
		);

		walker_edge_add_admin_field(
			array(
				'type' => 'select',
				'name' => 'footer_bottom_columns',
				'default_value' => '2',
				'label' => 'Footer Bottom Columns',
				'description' => 'Choose number of columns for Footer Bottom area',
				'options' => array(
					'1' => '1',
					'2' => '2',
					'3' => '3'
				),
				'parent' => $show_footer_bottom_container,
			)
		);

		walker_edge_add_admin_field(array(
			'name' => 'footer_bottom_height',
			'type' => 'text',
			'label' => 'Height',
			'description' => 'Enter footer bottom bar height (Default is 60)',
			'parent' => $show_footer_bottom_container,
			'args' => array(
				'col_width' => 2,
				'suffix' => 'px'
			)
		));

		walker_edge_add_admin_field(array(
			'name' => 'footer_bottom_background_color',
			'type' => 'color',
			'label' => 'Background Color',
			'description' => 'Set background color for bottom footer area',
			'parent' => $show_footer_bottom_container
		));

		walker_edge_add_admin_field(array(
			'name' => 'footer_bottom_border_top_color',
			'type' => 'color',
			'label' => 'Border Top Color',
			'description' => 'Set border top color for bottom footer area',
			'parent' => $show_footer_bottom_container
		));
	}

	add_action( 'walker_edge_options_map', 'walker_edge_footer_options_map', 11);
}
<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\ParallaxSection;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;

class ParallaxSection implements ShortcodeInterface{
	private $base;

	function __construct() {
		$this->base = 'edgtf_parallax_section';
		add_action('vc_before_init', array($this, 'vcMap'));
	}
	public function getBase() {
		return $this->base;
	}
	
	public function vcMap() {
		if(function_exists('vc_map')){
			vc_map( 
				array(
					'name' => esc_html__('Edge Parallax Section', 'walker'),
					'base' => $this->base,
					'as_child' => array('only' => 'edgtf_parallax_sections'),
					'content_element' => true,
					'category' => 'by EDGE',
					'icon' => 'icon-wpb-parallax-section extended-custom-icon',
					'show_settings_on_create' => true,
					'params' => array(
						array(
							'type' => 'dropdown',
							'class' => '',
							'heading' => 'Parallax Section Type',
							'param_name' => 'parallax_section_type',
							'value' => array(
								'Basic'    	=> 'parallax-section-basic',
								'Advanced'    	=> 'parallax-section-advanced',
							),
							'save_always' => true,
							'description' => 'Choose between the two predefined types.'
						),
						array(
							'type' => 'dropdown',
							'class' => '',
							'heading' => 'Parallax Layout',
							'param_name' => 'parallax_layout',
							'value' => array(
								'Main Image on the Left'    	=> 'main_image_left',
								'Main Image on the Right'     => 'main_image_right',
							),
							'save_always' => true,
							'description' => 'Choose between the two predefined layouts.',
						    'dependency' => array('element' => 'parallax_section_type', 'value' => 'parallax-section-basic')
						),
						array(
							'type' => 'attach_image',
							'class' => '',
							'heading' => 'Main Image',
							'param_name' => 'main_image',
							'value' => '',
							'description' => 'Set the main parallax image.',
						    'dependency' => array('element' => 'parallax_section_type', 'value' => 'parallax-section-basic')
						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Main Image Link',
							'param_name' => 'main_image_link',
							'value' => '',
							'description' => 'Set an external URL to link to.',
						    'dependency' => array('element' => 'main_image', 'not_empty' => true)

						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Main Image Y start offset',
							'param_name' => 'main_image_y_start_offset',
							'value' => '0px',
							'save_always' => true,
							'description' => 'Enter the Y-axis pixel start offset for Main Image',
						    'dependency' => array('element' => 'main_image', 'not_empty' => true),
							'group' => 'Advanced Options'
						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Main Image Y end offset',
							'param_name' => 'main_image_y_end_offset',
							'value' => '-350px',
							'save_always' => true,
							'description' => 'Enter the Y-axis pixel end offset for Main Image',
						    'dependency' => array('element' => 'main_image', 'not_empty' => true),
							'group' => 'Advanced Options'
						),
						array(
							'type' => 'attach_image',
							'class' => '',
							'heading' => 'Side Image',
							'param_name' => 'side_image',
							'value' => '',
							'description' => 'Set the side parallax image.',
						    'dependency' => array('element' => 'parallax_section_type', 'value' => 'parallax-section-basic')
						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Side Image Link',
							'param_name' => 'side_image_link',
							'value' => '',
							'description' => 'Set an external URL to link to.',
						    'dependency' => array('element' => 'side_image', 'not_empty' => true)
						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Side Image Y start offset',
							'param_name' => 'side_image_y_start_offset',
							'value' => '0px',
							'save_always' => true,
							'description' => 'Enter the Y-axis pixel start offset for Side Image',
						    'dependency' => array('element' => 'side_image', 'not_empty' => true),
							'group' => 'Advanced Options'
						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Side Image Y end offset',
							'param_name' => 'side_image_y_end_offset',
							'value' => '-350px',
							'save_always' => true,
							'description' => 'Enter the Y-axis pixel end offset for Side Image',
						    'dependency' => array('element' => 'side_image', 'not_empty' => true),
							'group' => 'Advanced Options'
						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Heading',
							'param_name' => 'heading',
							'value' => '',
							'description' => '',
						),
						array(
							'type' => 'textarea',
							'class' => '',
							'heading' => 'Excerpt',
							'param_name' => 'excerpt',
							'value' => '',
							'description' => '',
						),
						array(
							'type' => 'dropdown',
							'heading' => 'Show Buttons',
							'param_name' => 'show_buttons',
							'value' => array(
								'Yes'   => 'yes',
								'No' => 'no'
							),
							'save_always' => true,
						    'dependency' => array('element' => 'parallax_section_type', 'value' => 'parallax-section-advanced'),
							'description' => ''
						),
						array(
							'type' => 'textfield',
							'heading' => 'First Button Label',
							'param_name' => 'first_button_label',
							'save_always' => true,
							'dependency' => array('element' => 'show_buttons', 'value' => array('yes')),
							'description' => ''
						),
						array(
							'type' => 'textfield',
							'heading' => 'First Button URL',
							'param_name' => 'first_button_url',
							'save_always' => true,
							'dependency' => array('element' => 'first_button_label', 'not_empty' => true),
							'description' => ''
						),
						array(
							'type' => 'colorpicker',
							'heading' => 'First Button Background Color',
							'param_name' => 'first_button_background_color',
							'save_always' => true,
							'dependency' => array('element' => 'first_button_label', 'not_empty' => true),
							'description' => ''
						),
						array(
							'type' => 'colorpicker',
							'heading' => 'First Button Hover Background Color',
							'param_name' => 'first_button_hover_background_color',
							'save_always' => true,
							'dependency' => array('element' => 'first_button_background_color', 'not_empty' => true),
							'description' => ''
						),
						array(
							'type' => 'textfield',
							'heading' => 'Second Button Label',
							'param_name' => 'second_button_label',
							'save_always' => true,
							'dependency' => array('element' => 'first_button_label', 'not_empty' => true),
							'description' => ''
						),
						array(
							'type' => 'textfield',
							'heading' => 'Second Button URL',
							'param_name' => 'second_button_url',
							'save_always' => true,
							'dependency' => array('element' => 'second_button_label', 'not_empty' => true),
							'description' => ''
						),
						array(
							'type' => 'colorpicker',
							'heading' => 'Second Button Background Color',
							'param_name' => 'second_button_background_color',
							'save_always' => true,
							'dependency' => array('element' => 'second_button_label', 'not_empty' => true),
							'description' => ''
						),
						array(
							'type' => 'colorpicker',
							'heading' => 'Second Button Hover Background Color',
							'param_name' => 'second_button_hover_background_color',
							'save_always' => true,
							'dependency' => array('element' => 'second_button_background_color', 'not_empty' => true),
							'description' => ''
						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Text Area Y start offset',
							'param_name' => 'text_area_y_start_offset',
							'value' => '0px',
							'save_always' => true,
							'description' => 'Enter the Y-axis pixel start offset for Text Area',
						    'dependency' => array('element' => 'parallax_section_type', 'value' => 'parallax-section-basic'),
							'group' => 'Advanced Options'
						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Text Area Y end offset',
							'param_name' => 'text_area_y_end_offset',
							'value' => '-120px',
							'save_always' => true,
							'description' => 'Enter the Y-axis pixel end offset for Text Area',
						    'dependency' => array('element' => 'parallax_section_type', 'value' => 'parallax-section-basic'),
							'group' => 'Advanced Options'
						),
						array(
							'type' => 'attach_image',
							'class' => '',
							'heading' => 'Hero Image',
							'param_name' => 'hero_image',
							'value' => '',
							'description' => 'Set the hero parallax image.',
						    'dependency' => array('element' => 'parallax_section_type', 'value' => 'parallax-section-advanced')
						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Hero Image Link',
							'param_name' => 'hero_image_link',
							'value' => '',
							'description' => 'Set an external URL to link to.',
						    'dependency' => array('element' => 'hero_image', 'not_empty' => true)
						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Hero Image Y start offset',
							'param_name' => 'hero_image_y_start_offset',
							'value' => '0px',
							'save_always' => true,
							'description' => 'Enter the Y-axis pixel start offset for Hero Image',
						    'dependency' => array('element' => 'hero_image', 'not_empty' => true),
							'group' => 'Advanced Options'
						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Hero Image Y end offset',
							'param_name' => 'hero_image_y_end_offset',
							'value' => '-150px',
							'save_always' => true,
							'description' => 'Enter the Y-axis pixel end offset for Hero Image',
						    'dependency' => array('element' => 'hero_image', 'not_empty' => true),
							'group' => 'Advanced Options'
						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Info Section Y start offset',
							'param_name' => 'info_section_y_start_offset',
							'value' => '0px',
							'save_always' => true,
							'description' => 'Enter the Y-axis pixel start offset for Info Section',
						    'dependency' => array('element' => 'parallax_section_type', 'value' => 'parallax-section-advanced'),
							'group' => 'Advanced Options'
						),
						array(
							'type' => 'attach_image',
							'class' => '',
							'heading' => 'First Additional Image',
							'param_name' => 'additional_image_1',
							'value' => '',
							'description' => 'Set the first additional image.',
						    'dependency' => array('element' => 'parallax_section_type', 'value' => 'parallax-section-advanced'),
						),
						array(
							'type' => 'attach_image',
							'class' => '',
							'heading' => 'Second Additional Image',
							'param_name' => 'additional_image_2',
							'value' => '',
							'description' => 'Set the second additional image.',
						    'dependency' => array('element' => 'additional_image_1', 'not_empty' => true)
						),
						array(
							'type' => 'attach_image',
							'class' => '',
							'heading' => 'Third Additional Image',
							'param_name' => 'additional_image_3',
							'value' => '',
							'description' => 'Set the third additional image.',
						    'dependency' => array('element' => 'additional_image_2', 'not_empty' => true)
						),
						array(
							'type' => 'attach_image',
							'class' => '',
							'heading' => 'Fourth Additional Image',
							'param_name' => 'additional_image_4',
							'value' => '',
							'description' => 'Set the fourth additional image.',
						    'dependency' => array('element' => 'additional_image_3', 'not_empty' => true)
						),
						array(
							'type' => 'attach_image',
							'class' => '',
							'heading' => 'Fifth Additional Image',
							'param_name' => 'additional_image_5',
							'value' => '',
							'description' => 'Set the fifth additional image.',
						    'dependency' => array('element' => 'additional_image_4', 'not_empty' => true)
						),
						array(
							'type' => 'dropdown',
							'heading' => 'Additional Images Scroll Animation',
							'param_name' => 'additional_images_scroll_animation',
							'value' => array(
								'No'   => 'no',
								'Yes' => 'yes'
							),
							'save_always' => true,
						    'dependency' => array('element' => 'additional_image_5', 'not_empty' => true),
							'description' => 'Set the additional images to appear and disappear on scroll. Useful for the top section of your page.',
							'group' => 'Advanced Options',
						),
						array(
							'type' => 'dropdown',
							'heading' => 'Overflow',
							'param_name' => 'overflow',
							'value' => array(
								'Hidden'   => 'hidden',
								'Visible' => 'visible'
							),
							'save_always' => true,
						    'dependency' => array('element' => 'parallax_section_type', 'value' => 'parallax-section-advanced'),
							'description' => 'Set the excessive content to be cut off or visible. ',
							'group' => 'Advanced Options',
						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Height',
							'param_name' => 'height',
							'value' => '',
							'description' => '',
							'group' => 'Layout Options'
						),
						array(
							'type' => 'dropdown',
							'class' => '',
							'heading' => 'Responsive Height',
							'param_name' => 'responsive_height',
							'value' => array(
								'No'   => 'no',
								'Yes' => 'yes'
							),
							'description' => 'Toggle additional options for responsive height',
						    'dependency' => array('element' => 'parallax_section_type', 'value' => 'parallax-section-advanced'),
							'group' => 'Layout Options'
						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Height for laptops',
							'param_name' => 'laptops_height',
							'value' => '',
							'description' => 'Under 1280px wide devices.',
						    'dependency' => array('element' => 'responsive_height', 'value' => 'yes'),
							'group' => 'Layout Options'
						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Height for tablets',
							'param_name' => 'tablets_height',
							'value' => '',
							'description' => 'Under 1024px wide devices.',
						    'dependency' => array('element' => 'responsive_height', 'value' => 'yes'),
							'group' => 'Layout Options'
						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Top Margin',
							'param_name' => 'top_margin',
							'value' => '',
							'description' => '',
							'group' => 'Layout Options'
						),
						array(
							'type' => 'textfield',
							'class' => '',
							'heading' => 'Bottom Margin',
							'param_name' => 'bottom_margin',
							'value' => '',
							'description' => '',
							'group' => 'Layout Options'
						),
						array(
							'type' => 'colorpicker',
							'class' => '',
							'heading' => 'Background Color',
							'param_name' => 'background_color',
							'value' => '',
							'description' => '',
							'group' => 'Layout Options'
						)
					)
				)
			);			
		}
	}

	public function render($atts, $content = null) {
		$args = array(
			'parallax_section_type' => '',
			'parallax_layout' => '',
			'main_image' => '',
			'main_image_link' => '',
			'main_image_y_start_offset' => '',
			'main_image_y_end_offset' => '',
			'hero_image_y_start_offset' => '',
			'hero_image_y_end_offset' => '',
			'side_image' => '',
			'side_image_link' => '',
			'side_image_y_start_offset' => '',
			'side_image_y_end_offset' => '',
			'heading' => '',
			'excerpt' => '',
			'show_buttons' => '',
			'first_button_label' => '',
			'first_button_url' => '',
			'first_button_background_color' => '',
			'first_button_hover_background_color' => '',
			'second_button_label' => '',
			'second_button_url' => '',
			'second_button_background_color' => '',
			'second_button_hover_background_color' => '',
			'text_area_y_start_offset' => '',
			'text_area_y_end_offset' => '',
			'info_section_y_start_offset' => '',
			'info_section_y_end_offset' => '',
			'hero_image' => '',
			'hero_image_link' => '',
			'additional_image_1' => '',
			'additional_image_2' => '',
			'additional_image_3' => '',
			'additional_image_4' => '',
			'additional_image_5' => '',
			'additional_images_scroll_animation' => '',
			'overflow' => '',
			'height' => '',
			'responsive_height' => '',
			'laptops_height' => '',
			'tablets_height' => '',
			'top_margin' => '',
			'bottom_margin' => '',
			'background_color' => '',
		);
		
		$params = shortcode_atts($args, $atts);
		extract($params);
		$params['content']= $content;

		//images
		$params['main_image_src'] = '';
		if (is_numeric($main_image)) {
		    $params['main_image_src'] = wp_get_attachment_url($main_image);
		} else {
		    $params['main_image_src'] = $main_image;
		}

		$params['side_image_src'] = '';
		if (is_numeric($side_image)) {
		    $params['side_image_src'] = wp_get_attachment_url($side_image);
		} else {
		    $params['side_image_src'] = $side_image;
		}

		$params['hero_image_src'] = '';
		if (is_numeric($hero_image)) {
		    $params['hero_image_src'] = wp_get_attachment_url($hero_image);
		} else {
		    $params['hero_image_src'] = $hero_image;
		}

		$params['add_image1_src'] = '';
		if (is_numeric($additional_image_1)) {
		    $params['add_image1_src'] = wp_get_attachment_url($additional_image_1);
		} else {
		    $params['add_image1_src'] = $additional_image_1;
		}

		$params['add_image2_src'] = '';
		if (is_numeric($additional_image_2)) {
		    $params['add_image2_src'] = wp_get_attachment_url($additional_image_2);
		} else {
		    $params['add_image2_src'] = $additional_image_2;
		}

		$params['add_image3_src'] = '';
		if (is_numeric($additional_image_3)) {
		    $params['add_image3_src'] = wp_get_attachment_url($additional_image_3);
		} else {
		    $params['add_image3_src'] = $additional_image_3;
		}

		$params['add_image4_src'] = '';
		if (is_numeric($additional_image_4)) {
		    $params['add_image4_src'] = wp_get_attachment_url($additional_image_4);
		} else {
		    $params['add_image4_src'] = $additional_image_4;
		}

		$params['add_image5_src'] = '';
		if (is_numeric($additional_image_5)) {
		    $params['add_image5_src'] = wp_get_attachment_url($additional_image_5);
		} else {
		    $params['add_image5_src'] = $additional_image_5;
		}

		$params['parallax_section_style'] = $this->getParallaxSectionStyle($params);
		$params['parallax_section_class'] = $this->getParallaxSectionClass($params);
		$params['offsets'] = $this->getBasicParallaxSectionTypeOffsets($params);
		$params['first_button_params'] = $this->getFirstButtonParams($params);
		$params['second_button_params'] = $this->getSecondButtonParams($params);
        $params['resp_height_data'] = $this->getResponsiveHeightData($params);

		$html = walker_edge_get_shortcode_module_template_part('templates/' . $params['parallax_section_type'], 'parallax-sections', '', $params);

		return $html;
	}


	/**
	 * Return Parallax Section style
	 *
	 * @param $params
	 * @return array
	 */
	private function getParallaxSectionStyle($params) {

		$parallax_section_style = array();

		if ($params['height'] !== '') {
			$parallax_section_style[] = 'height: ' . walker_edge_filter_px($params['height']) . 'px';
		}

		if ($params['background_color'] !== '') {
			$parallax_section_style[] = 'background-color: ' . $params['background_color'];
		}

		if ($params['top_margin'] !== '') {
			$parallax_section_style[] = 'margin-top: ' . walker_edge_filter_px($params['top_margin']) . 'px';
		}

		if ($params['bottom_margin'] !== '') {
			$parallax_section_style[] = 'margin-bottom: ' . walker_edge_filter_px($params['bottom_margin']) . 'px';
		}

		return implode(';', $parallax_section_style);

	}

	/**
	 * Return Parallax Section classes
	 *
	 * @param $params
	 * @return array
	 */
	private function getParallaxSectionClass($params) {

		$parallax_section_class = array();

		if (($params['parallax_layout'] !== '') && ($params['parallax_section_type'] == 'parallax-section-basic')) {
			if ($params['parallax_layout'] == 'main_image_left') {
				$parallax_section_class[] = 'edgtf-parallax-layout-1';
			} elseif ($params['parallax_layout'] == 'main_image_right') {
				$parallax_section_class[] = 'edgtf-parallax-layout-2';
			}
		}

		if ($params['height'] !== '') {
			$parallax_section_class[] = 'edgtf-section-height-set';
		}

		if ($params['responsive_height'] == 'yes') {
			$parallax_section_class[] = 'edgtf-section-responsive-height-set';
		}

		if (($params['additional_images_scroll_animation'] == 'yes') && ($params['parallax_section_type'] == 'parallax-section-advanced')) {
			$parallax_section_class[] = 'edgtf-additional-images-scroll-animation';
		}

		if (($params['overflow'] !== '') && ($params['parallax_section_type'] == 'parallax-section-advanced')) {
			$parallax_section_class[] = 'edgtf-overflow-visible';
		}

		return implode(' ', $parallax_section_class);

	}


	/**
	 * Return Basic Parallax Section Type offsets
	 *
	 * @param $params
	 * @return array
	 */
	private function getBasicParallaxSectionTypeOffsets($params) {

		$parallax_section_offsets = array();

		if ($params['main_image_y_start_offset'] !== '') {
			$parallax_section_offsets['main_image_y_start_offset'] = walker_edge_filter_px($params['main_image_y_start_offset']) . 'px';
		} else {
			$parallax_section_offsets['main_image_y_start_offset'] = '0px';
		}

		if ($params['main_image_y_end_offset'] !== '') {
			$parallax_section_offsets['main_image_y_end_offset'] = walker_edge_filter_px($params['main_image_y_end_offset']) . 'px';
		} else {
			$parallax_section_offsets['main_image_y_start_offset'] = '-350px';
		}

		if ($params['side_image_y_start_offset'] !== '') {
			$parallax_section_offsets['side_image_y_start_offset'] = walker_edge_filter_px($params['side_image_y_start_offset']) . 'px';
		} else {
			$parallax_section_offsets['side_image_y_start_offset'] = '0px';
		}

		if ($params['side_image_y_end_offset'] !== '') {
			$parallax_section_offsets['side_image_y_end_offset'] = walker_edge_filter_px($params['side_image_y_end_offset']) . 'px';
		} else {
			$parallax_section_offsets['side_image_y_start_offset'] = '-350px';
		}

		if ($params['text_area_y_start_offset'] !== '') {
			$parallax_section_offsets['text_area_y_start_offset'] = walker_edge_filter_px($params['text_area_y_start_offset']) . 'px';
		} else {
			$parallax_section_offsets['text_area_y_start_offset'] = '0px';
		}

		if ($params['text_area_y_end_offset'] !== '') {
			$parallax_section_offsets['text_area_y_end_offset'] = walker_edge_filter_px($params['text_area_y_end_offset']) . 'px';
		} else {
			$parallax_section_offsets['text_area_y_end_offset'] = '-120px';
		}

		if ($params['hero_image_y_start_offset'] !== '') {
			$parallax_section_offsets['hero_image_y_start_offset'] = walker_edge_filter_px($params['hero_image_y_start_offset']) . 'px';
		} else {
			$parallax_section_offsets['hero_image_y_start_offset'] = '0px';
		}

		if ($params['hero_image_y_end_offset'] !== '') {
			$parallax_section_offsets['hero_image_y_end_offset'] = walker_edge_filter_px($params['hero_image_y_end_offset']) . 'px';
		} else {
			$parallax_section_offsets['hero_image_y_end_offset'] = '-150px';
		}

		if ($params['info_section_y_start_offset'] !== '') {
			$parallax_section_offsets['info_section_y_start_offset'] = walker_edge_filter_px($params['info_section_y_start_offset']) . 'px';
		} else {
			$parallax_section_offsets['info_section_y_start_offset'] = '0px';
		}

		if ($params['info_section_y_end_offset'] !== '') {
			$parallax_section_offsets['info_section_y_end_offset'] = walker_edge_filter_px($params['info_section_y_end_offset']) . 'px';
		} else {
			$parallax_section_offsets['info_section_y_end_offset'] = '-60px';
		}

		return $parallax_section_offsets;

	}


	/**
	 * Return First Button Params
	 *
	 * @param $params
	 * @return array
	 */
	private function getFirstButtonParams($params) {

		$first_button_params_array = array();

		if (($params['show_buttons'] == 'yes') && ($params['first_button_label'] !== '')) {

			$first_button_params_array['type'] = 'solid';
			$first_button_params_array['target'] = 'blank';

			if(!empty($params['first_button_url'])) {
				$first_button_params_array['link'] = $params['first_button_url'];
			}

			if(!empty($params['first_button_label'])) {
				$first_button_params_array['text'] = $params['first_button_label'];
			}

			if(!empty($params['first_button_background_color'])) {
				$first_button_params_array['background_color'] = $params['first_button_background_color'];
			}

			if(!empty($params['first_button_hover_background_color'])) {
				$first_button_params_array['hover_background_color'] = $params['first_button_hover_background_color'];
			}
		
		}
		return $first_button_params_array;

	}

	/**
	 * Return Second Button Params
	 *
	 * @param $params
	 * @return array
	 */
	private function getSecondButtonParams($params) {

		$second_button_params_array = array();

		if (($params['show_buttons'] == 'yes') && ($params['second_button_label'] !== '')) {

			$second_button_params_array['type'] = 'solid';
			$second_button_params_array['target'] = 'blank';

			if(!empty($params['second_button_url'])) {
				$second_button_params_array['link'] = $params['second_button_url'];
			}

			if(!empty($params['second_button_label'])) {
				$second_button_params_array['text'] = $params['second_button_label'];
			}

			if(!empty($params['second_button_background_color'])) {
				$second_button_params_array['background_color'] = $params['second_button_background_color'];
			}

			if(!empty($params['second_button_hover_background_color'])) {
				$second_button_params_array['hover_background_color'] = $params['second_button_hover_background_color'];
			}
		
		}
		return $second_button_params_array;

	}

	/**
	 * Return Responsive Height Data params
	 *
	 * @param $params
	 * @return array
	 */
	private function getResponsiveHeightData($params) {

		$data = array();

		if ($params['responsive_height'] == 'yes') {


			if(!empty($params['laptops_height'])) {
			    $data['data-laptop-height'] = $params['laptops_height'];
			}

			if(!empty($params['tablets_height'])) {
			    $data['data-tablet-height'] = $params['tablets_height'];
			}

		}

		return $data;

	}

}

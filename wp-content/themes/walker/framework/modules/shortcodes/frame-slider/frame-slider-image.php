<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\FrameSliderImage;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;

class FrameSliderImage implements ShortcodeInterface {
	private $base;
	function __construct() {
		$this->base = 'edgtf_frame_slider_image';
		add_action('vc_before_init', array($this, 'vcMap'));
	}
	public function getBase() {
		return $this->base;
	}
	
	public function vcMap() {
		vc_map( array(
			'name' => esc_html__('Edge Slide Image', 'walker'),
			'base' => $this->base,
			'icon' => 'icon-wpb-frame-slider-image extended-custom-icon',
			'category' => 'by EDGE',
			'as_child' => array('only' => 'edgtf_frame_slider_left_panel'),
			'params' => array(
				array(
					'type'			=> 'attach_image',
					'heading'		=> 'Image',
					'param_name'	=> 'image',
					'description'	=> 'Select image from media library'
				),
				array(
					'type'			=> 'textfield',
					'heading'		=> 'Link',
					'param_name'	=> 'link',
					'description'	=> 'Enter an external URL to link to.'
				),
				array(
				    'type'       => 'dropdown',
				    'heading'    => 'Target',
				    'param_name' => 'target',
				    'value'      => array(
				        ''      => '',
				        'Self'  => '_self',
				        'Blank' => '_blank'
				    ),
				    'dependency' => array('element' => 'link', 'not_empty' => true),
				),
			)
		));
	}

	public function render($atts, $content = null) {
	
		$args = array(
			'image'			    => '',
			'link'			    => '',
			'target'		    => '_self',
		);

		$params = shortcode_atts($args, $atts);

		$html = walker_edge_get_shortcode_module_template_part('templates/frame-slider-image', 'frame-slider', '', $params);

		return $html;

	}

}

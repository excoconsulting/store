<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\FrameSlider;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;

class FrameSlider implements ShortcodeInterface{
	private $base;
	function __construct() {
		$this->base = 'edgtf_frame_slider';
		add_action('vc_before_init', array($this, 'vcMap'));
	}
	public function getBase() {
		return $this->base;
	}
	
	public function vcMap() {
		vc_map( array(
			'name'						=> esc_html__('Edge Frame Slider', 'walker'),
			'base'						=> $this->base,
			'icon'						=> 'icon-wpb-frame-slider extended-custom-icon',
			'category'					=> 'by EDGE',
			'as_parent'					=> array('only' => 'edgtf_frame_slider_left_panel,edgtf_frame_slider_right_panel'),
			'js_view'					=> 'VcColumnView',
			'show_settings_on_create'	=> false,
			'params'					=> array()
		));
	}

	public function render($atts, $content = null) {
	
		$args = array();
		$params = shortcode_atts($args, $atts);
		extract($params);

		$html						= '';

		$html .= '<div class="edgtf-frame-slider">';
			$html .= do_shortcode($content);
		$html .= '</div>';

		return $html;

	}

}

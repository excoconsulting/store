<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\FrameSliderRightPanel;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;

class FrameSliderRightPanel implements ShortcodeInterface{
	private $base;

	function __construct() {
		$this->base = 'edgtf_frame_slider_right_panel';
		add_action('vc_before_init', array($this, 'vcMap'));
	}
	public function getBase() {
		return $this->base;
	}

	public function vcMap() {
		if(function_exists('vc_map')){
			vc_map(
				array(
					'name' => esc_html__('Edge Right Sliding Panel', 'walker'),
					'base' => $this->base,
					'as_parent'	=> array('only' => 'edgtf_frame_slider_content_item'),
					'as_child'	=> array('only' => 'edgtf_frame_slider'),
					'content_element' => true,
					'category' => 'by EDGE',
					'icon' => 'icon-wpb-frame-slider-right-panel extended-custom-icon',
					'show_settings_on_create' => false,
					'js_view' => 'VcColumnView',
					'params' => array()
				)
			);
		}
	}

	public function render($atts, $content = null) {
		$args = array();

		$params = shortcode_atts($args, $atts);
		extract($params);

		$html = '<div class="edgtf-frame-slider-right-panel">';
		$html .= '<div class="edgtf-frame-slider-content-holder">';
		$html .= do_shortcode($content);
		$html .= '</div>';
		$html .= '</div>';


		return $html;
	}  

}

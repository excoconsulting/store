<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\FrameSliderLeftPanel;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;

class FrameSliderLeftPanel implements ShortcodeInterface{
	private $base;

	function __construct() {
		$this->base = 'edgtf_frame_slider_left_panel';
		add_action('vc_before_init', array($this, 'vcMap'));
	}
	public function getBase() {
		return $this->base;
	}
	
	public function vcMap() {
		if(function_exists('vc_map')){
			vc_map( 
				array(
					'name' => esc_html__('Edge Left Sliding Panel', 'walker'),
					'base' => $this->base,
					'as_parent'	=> array('only' => 'edgtf_frame_slider_image'),
					'as_child'	=> array('only' => 'edgtf_frame_slider'),
					'content_element' => true,
					'category' => 'by EDGE',
					'icon' => 'icon-wpb-frame-slider-left-panel extended-custom-icon',
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
		$html = '<div class="edgtf-frame-slider-left-panel">';
		$html .= '<div class="edgtf-left-panel-frame">';
		$html .= '<img class="edgtf-frame-image" src="'. EDGE_ROOT.'/assets/css/img/frame.png" alt="" />';
		$html .= '<div class="edgtf-left-panel-absolute">';
		$html .= '<div class="edgtf-frame-images-holder">';
		$html .= do_shortcode($content);
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

}

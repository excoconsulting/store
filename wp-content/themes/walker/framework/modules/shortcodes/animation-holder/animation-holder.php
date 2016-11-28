<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\AnimationHolder;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;
/**
	* class Animation Holder
*/
class AnimationHolder implements ShortcodeInterface{
	/**
	 * @var string
	 */
	private $base;

	function __construct() {
		$this->base = 'edgtf_animation_holder';
		add_action('vc_before_init', array($this, 'vcMap'));
	}

	public function getBase() {
		return	$this->base;
	}

	public function vcMap() {

		vc_map( array(
			'name' =>  esc_html__('Edge Animation Holder', 'walker'),
			'base' => $this->base,
			"as_parent" => array('except' => 'vc_row'),
			'content_element' => true,
			'category' => 'by EDGE',
			'icon' => 'icon-wpb-animation-holder extended-custom-icon',
			'show_settings_on_create' => true,
			'js_view' => 'VcColumnView',
			'params' => array(
				array(
					'type' => 'dropdown',
					'heading' => 'Animation Type',
					'param_name' => 'animation',
					'value' => array(
						'Element Grow In'	=> 'edgtf-grow-in',
						'Element Fade In Down'	=> 'edgtf-fade-in-down',
						'Element from Fade'	=> 'edgtf-element-from-fade',
						'Element from Left'  => 'edgtf-element-from-left',
						'Element from Right' => 'edgtf-element-from-right',
						'Element from Top'	 => 'edgtf-element-from-top',
						'Element from Bottom'	 => 'edgtf-element-from-bottom',
						'Element Flip In'	 => 'edgtf-flip-in',
						'Element X Rotate'	 => 'edgtf-x-rotate',
						'Element Z Rotate'	 => 'edgtf-z-rotate',
						'Element Y Translate'	 => 'edgtf-y-translate',
						'Element Fade In X Rotate'	 => 'edgtf-fade-in-left-x-rotate',
					),
					'save_always' => true,
					'description' => ''
				),
                array(
                    'type' => 'textfield',
                    'class' => '',
                    'heading' => 'Animation Delay',
                    'param_name' => 'animation_delay',
                    'value' => '',
                    'description' => 'Animation delay in miliseconds.'
                )
			)
		) );
	}

	public function render($atts, $content = null) {
		$args = array(
			'animation' => '',
			'animation_delay' => '',
        );

        extract(shortcode_atts($args, $atts));

        $html = '<div class="edgtf-animation-holder '. esc_attr($animation) .'" data-animation="'.esc_attr($animation).'" data-animation-delay="'.esc_attr($animation_delay).'">'.do_shortcode($content).'</div>';

        return $html;
	}
}
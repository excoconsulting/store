<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\PricingTables;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;

class PricingTables implements ShortcodeInterface{
	private $base;
	function __construct() {
		$this->base = 'edgtf_pricing_tables';
		add_action('vc_before_init', array($this, 'vcMap'));
	}
	public function getBase() {
		return $this->base;
	}
	
	public function vcMap() {

		vc_map( array(
				'name' => esc_html__('Edge Pricing Tables', 'walker'),
				'base' => $this->base,
				'as_parent' => array('only' => 'edgtf_pricing_table'),
				'content_element' => true,
				'category' => 'by EDGE',
				'icon' => 'icon-wpb-pricing-tables extended-custom-icon',
				'show_settings_on_create' => true,
				'js_view' => 'VcColumnView',
				'params' => array(
					array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Columns',
						'param_name' => 'columns',
						'value' => array(
							'Two'       => 'edgtf-two-columns',
							'Three'     => 'edgtf-three-columns',
							'Four'      => 'edgtf-four-columns',
						),
						'save_always' => true,
						'description' => ''
					),
					array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Space Between Items',
						'param_name' => 'space_between_items',
						'value' => array(
							'Normal'  => 'edgtf-normal-space',
							'Small'   => 'edgtf-small-space',
							'Big'     => 'edgtf-big-space'
						),
						'save_always' => true,
						'description' => ''
					)
				)
		) );
	}

	public function render($atts, $content = null) {
		$args = array(
			'columns'         	  => 'edgtf-two-columns',
			'space_between_items' => 'edgtf-normal-space'
		);
		
		$params = shortcode_atts($args, $atts);
		extract($params);

		$holder_class = '';

		if ($columns !== '') {
			$holder_class .= ' '.$columns;
		}

		if ($space_between_items !== '') {
			$holder_class .= ' '.$space_between_items;
		}
		
		$html = '<div class="edgtf-pricing-tables clearfix '.esc_attr($holder_class).'">';
		$html .= '<div class="edgtf-pricing-tables-inner">';
		$html .= do_shortcode($content);
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}
}
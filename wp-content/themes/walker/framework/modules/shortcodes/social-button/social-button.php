<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\SocialButton;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;

class SocialButton implements ShortcodeInterface {

	private $base;

	/**
	 * Social Button constructor.
	 */
	public function __construct() {
		$this->base = 'edgtf_social_button';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	/**
	 * Returns base for shortcode
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}

	/**
	 * Maps shortcode to Visual Composer. Hooked on vc_before_init
	 *
	 * @see edgt_core_get_social_button_array_vc()
	 */
	public function vcMap() {

		vc_map(array(
			'name' => esc_html__('Edge Social Button', 'walker'),
			'base' => $this->getBase(),
			'icon' => 'icon-wpb-social-button extended-custom-icon',
			'category' => 'by EDGE',
			'allowed_container_element' => 'vc_row',
			'params' => array(
				array(	// General
                    'type'        	=> 'textfield',
                    'heading'     	=> 'Title',
                    'param_name'  	=> 'title',
                    'value'       	=> '',
                    'admin_label' 	=> true
                ),
                array(
                    'type'        	=> 'textfield',
                    'heading'     	=> 'Link',
                    'param_name'  	=> 'link',
                    'value'       	=> ''
                ),
                array(
                    'type'       => 'dropdown',
                    'heading'    => 'Target',
                    'param_name' => 'target',
                    'value'      => array(
                        'Same Window'  => '_self',
                        'New Window' => '_blank'
                    ),
					'save_always' => true
                ),
				array(
                    'type'        	=> 'textfield',
                    'heading'     	=> 'Width',
                    'param_name'  	=> 'width',
                    'value'       	=> ''
                ),
                array(
					'type'			=> 'textfield',
					'class' 		=> '',
					'heading' 		=> 'Padding',
					'param_name'	=> 'padding',
					'value' 		=> '',
					'description' 	=> 'Please insert padding in format (top right bottom left). Example 0px 10px 0px 10px. Also you use percentage mark.'
				),
				array(
					'type'			=> 'textfield',
					'class' 		=> '',
					'heading' 		=> 'Margin',
					'param_name'	=> 'margin',
					'value' 		=> '',
					'description' 	=> 'Please insert margin in format (top right bottom left). Example 0px 10px 0px 10px. Also you use percentage mark.'
				),
				array(	// Typography
                    "type" 			=> "textfield",
					"heading" 		=> "Font Size (px)",
					"param_name" 	=> "font_size",
					"value" 		=> "",
                    'dependency'	=> array('element' => 'title', 'not_empty' => true),
                    'group'       	=> 'Typography'
                ),
				array(
                    "type" 			=> "dropdown",
					"heading" 		=> "Font Weight",
					"param_name" 	=> "font_weight",
					"value" 		=> array_flip(walker_edge_get_font_weight_array(true)),
					"save_always" 	=> true,
                    'dependency'	=> array('element' => 'title', 'not_empty' => true),
                    'group'       	=> 'Typography'
                ),
                array(
                    "type" 			=> "dropdown",
					"heading" 		=> "Font Style",
					"param_name" 	=> "font_style",
					"value" 		=> walker_edge_get_font_style_array(),
					"save_always" 	=> true,
                    'dependency'	=> array('element' => 'title', 'not_empty' => true),
                    'group'       	=> 'Typography'
                ),
                array(
					"type" 			=> "textfield",
					"heading" 		=> "Line Height (px)",
					"param_name" 	=> "line_height",
					"value" 		=> "",
					'dependency'	=> array('element' => 'title', 'not_empty' => true),
                    'group'       	=> 'Typography'
				),
				array(
					"type" 			=> "textfield",
					"heading" 		=> "Letter Spacing (px)",
					"param_name" 	=> "letter_spacing",
					"value" 		=> "",
					'dependency'	=> array('element' => 'title', 'not_empty' => true),
                    'group'       	=> 'Typography'
				),
                array(	// Design
                    'type'     		=> 'colorpicker',
                    'heading'    	=> 'Color',
                    'param_name' 	=> 'color',
                    'dependency'	=> array('element' => 'title', 'not_empty' => true),
                    'group'       	=> 'Design Options'
                ),
                array(
                    'type'       	=> 'colorpicker',
                    'heading'   	=> 'Hover Color',
                    'param_name' 	=> 'hover_color',
                    'dependency'	=> array('element' => 'title', 'not_empty' => true),
                    'group'       	=> 'Design Options'
                ),
                array(
                    'type'       	=> 'colorpicker',
                    'heading'    	=> 'Background Color',
                    'param_name' 	=> 'background_color',
                    'dependency'	=> array('element' => 'title', 'not_empty' => true),
                    'group'       	=> 'Design Options'
                ),
                array(
                    'type'       	=> 'colorpicker',
                    'heading'    	=> 'Hover Background Color',
                    'param_name' 	=> 'hover_background_color',
                    'dependency'	=> array('element' => 'title', 'not_empty' => true),
                    'group'       	=> 'Design Options'
                ),
                array(
                    'type'        	=> 'textfield',
                    'heading'     	=> 'Border Width (px)',
                    'param_name'  	=> 'border_width',
                    'value'       	=> '',
                    'dependency'	=> array('element' => 'title', 'not_empty' => true),
                    'group'       	=> 'Design Options'
                ),
                array(
                    'type'       	=> 'colorpicker',
                    'heading'    	=> 'Border Color',
                    'param_name' 	=> 'border_color',
                    'dependency'	=> array('element' => 'title', 'not_empty' => true),
                    'group'       	=> 'Design Options'
                ),
                array(
                    'type'       	=> 'colorpicker',
                    'heading'    	=> 'Hover Border Color',
                    'param_name' 	=> 'hover_border_color',
                    'dependency'	=> array('element' => 'title', 'not_empty' => true),
                    'group'       	=> 'Design Options'
                ),
			)
		));

	}

	/**
	 * Renders shortcodes HTML
	 *
	 * @param $atts array of shortcode params
	 * @param $content string shortcode content
	 * @return string
	 */
	public function render($atts, $content = null) {
		 $args = array(
            'title'                		=> '',
            'link'                		=> '',
            'target'                	=> '_self',
            'width'                 	=> '100%',
            'padding'               	=> '',
            'margin'               		=> '',
            'font_size'                 => '',
            'font_weight'               => '',
            'font_style'               	=> '',
            'line_height'            	=> '',
            'letter_spacing'            => '',
            'color'        				=> '#333',
            'hover_color'               => '#333',
            'background_color'          => '',
            'hover_background_color'	=> '',
            'border_width'            	=> '',
            'border_color'       		=> '',
            'hover_border_color' 		=> ''
        );

		$params = shortcode_atts($args, $atts);
		$params['button_styles'] = $this->getButtonStyles($params);
		$params['button_data'] = $this->getButtonData($params);
        $params['button_span_data'] = $this->getButtonSpanData($params);
		
		$html = walker_edge_get_shortcode_module_template_part('templates/social-button-template', 'social-button', '', $params);

		return $html;
	}

	/**
     * Returns inline button styles
     *
     * @param $params
     *
     * @return string
     */
    private function getButtonStyles($params) {
        $styles = array();

        if(!empty($params['width'])) {
            $styles[] = 'width: ' . $params['width'];
        }
        if(!empty($params['padding'])) {
            $styles[] = 'padding: ' . $params['padding'];
        }
        if(!empty($params['margin'])) {
            $styles[] = 'margin: ' . $params['margin'];
        }
        if(!empty($params['font_size'])) {
            $styles[] = 'font-size: ' . walker_edge_filter_px($params['font_size']) . 'px';
        }
        if(!empty($params['font_weight']) && $params['font_weight'] !== '') {
         	$styles[] = 'font-weight: '.$params['font_weight'];
        }
        if(!empty($params['font_style'])) {
         	$styles[] = 'font-style: '.$params['font_style'];
        }
        if(!empty($params['line_height'])) {
            $styles[] = 'line-height: '.walker_edge_filter_px($params['line_height']) . 'px';
        }
        if(!empty($params['letter_spacing'])) {
            $styles[] = 'letter-spacing: '.walker_edge_filter_px($params['letter_spacing']) . 'px';
        }
        if(!empty($params['color'])) {
            $styles[] = 'color: ' . $params['color'];
        }
        if(!empty($params['background_color'])) {
         	$styles[] = 'background-color: ' . $params['background_color'];
        }
        if(!empty($params['border_width'])) {
            $styles[] = 'border-width: '.walker_edge_filter_px($params['border_width']) . 'px';
        }
        if(!empty($params['border_color'])) {
            $styles[] = 'border-color: ' . $params['border_color'];
            
            if(empty($params['border_width'])) {
            	$styles[] = 'border-width: 1px';
            }
        }

        return implode(';', $styles);
    }

    /**
     * Returns button data array for hover style
     *
     * @param $params
     *
     * @return array
     */
    private function getButtonData($params) {
        $data = array();

        if(!empty($params['hover_color'])) {
            $data['data-hover-color'] = $params['hover_color'];
        }
        if(!empty($params['color'])) {
            $data['data-color'] = $params['color'];
        }
        if(!empty($params['hover_background_color'])) {
         	$data['data-hover-background-color'] = $params['hover_background_color'];
        }
        if(!empty($params['hover_border_color'])) {
            $data['data-hover-border-color'] = $params['hover_border_color'];
        }

        return $data;
    }

    /**
     * Returns button data array for hover style
     *
     * @param $params
     *
     * @return array
     */
    private function getButtonSpanData($params) {
        $data = array();

        if(!empty($params['title'])) {
            $data['data-title'] = $params['title'];
        }

        return $data;
    }
}
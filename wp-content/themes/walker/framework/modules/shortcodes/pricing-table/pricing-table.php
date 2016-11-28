<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\PricingTable;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;

class PricingTable implements ShortcodeInterface{
	private $base;
	function __construct() {
		$this->base = 'edgtf_pricing_table';
		add_action('vc_before_init', array($this, 'vcMap'));
	}
	public function getBase() {
		return $this->base;
	}
	
	public function vcMap() {
		vc_map( array(
			'name' => esc_html__('Edge Pricing Table', 'walker'),
			'base' => $this->base,
			'icon' => 'icon-wpb-pricing-table extended-custom-icon',
			'category' => 'by EDGE',
			'allowed_container_element' => 'vc_row',
			'as_child' => array('only' => 'edgtf_pricing_tables'),
			'params' => array(
				array( // General Options
					'type' => 'dropdown',
					'admin_label' => true,
					'heading' => 'Type',
					'param_name' => 'type',
					'value' => array(
						'Title Above Price' => 'title_above_price',
						'Title Next To Price' => 'title_in_price'
					),
					'save_always' => true,
					'description' => ''
				),
				array(
					'type' => 'textfield',
					'admin_label' => true,
					'heading' => 'Title',
					'param_name' => 'title',
					'value' => 'Basic Plan',
					'description' => ''
				),
				array(
					'type' => 'textfield',
					'admin_label' => true,
					'heading' => 'Price',
					'param_name' => 'price',
					'description' => 'Default value is 100'
				),
				array(
					'type' => 'textfield',
					'admin_label' => true,
					'heading' => 'Currency',
					'param_name' => 'currency',
					'description' => 'Default mark is $'
				),
				array(
					'type' => 'textfield',
					'admin_label' => true,
					'heading' => 'Price Period',
					'param_name' => 'price_period',
					'description' => 'Default label is monthly'
				),
                array(
                    'type' => 'textfield',
                    'admin_label' => true,
                    'heading' => 'Price Description',
                    'param_name' => 'price_description',
                    'description' => '',
                    'dependency' => array('element' => 'type',  'value' => 'title_above_price')
                ),
				array(
					'type' => 'dropdown',
					'admin_label' => true,
					'heading' => 'Show Button',
					'param_name' => 'show_button',
					'value' => array(
						'Yes' => 'yes',
						'No' => 'no'
					),
					'save_always' => true,
					'description' => ''
				),
                array(
                    'type' => 'textfield',
                    'admin_label' => true,
                    'heading' => 'Button Text',
                    'param_name' => 'button_text',
                    'dependency' => array('element' => 'show_button',  'value' => 'yes')
                ),
                array(
                    'type' => 'textfield',
                    'admin_label' => true,
                    'heading' => 'Button Link',
                    'param_name' => 'link',
                    'dependency' => array('element' => 'show_button',  'value' => 'yes')
                ),
                array(
                    'type' => 'textarea_html',
                    'holder' => 'div',
                    'class' => '',
                    'heading' => 'Content',
                    'param_name' => 'content',
                    'value' => '<li>content content content</li><li>content content content</li><li>content content content</li>',
                    'description' => ''
                ),
                array( // Design Options
                    'type' => 'dropdown',
                    'admin_label' => true,
                    'heading' => 'Enable Content Border',
                    'param_name' => 'content_border',
                    'value' => array(
                        'Yes' => 'yes',
                        'No' => 'no'
                    ),
                    'save_always' => true,
                    'description' => '',
					'group' => 'Design Options'
                ),
                array(
                    'type' => 'colorpicker',
                    'admin_label' => true,
                    'heading' => 'Title Color',
                    'param_name' => 'title_color',
                    'group' => 'Design Options'
                ),
                array(
                    'type' => 'colorpicker',
                    'admin_label' => true,
                    'heading' => 'Title Background Color',
                    'param_name' => 'title_background_color',
                    'group' => 'Design Options',
                    'dependency' => array('element' => 'type',  'value' => 'title_above_price')
                ),
                array(
                    'type'			=> 'attach_image',
                    'heading'		=> 'Price Holder Background Image',
                    'param_name'	=> 'header_image',
                    'description'	=> 'Select image from media library',
                    'group' => 'Design Options',
                    'dependency' => array('element' => 'type',  'value' => 'title_in_price')
                ),
                array(
                    'type' => 'colorpicker',
                    'heading' => 'Price Holder Text Color',
                    'param_name' => 'header_text_color',
                    'description'	=> '',
                    'group' => 'Design Options'
                ),
                array(
                    'type' => 'colorpicker',
                    'admin_label' => true,
                    'heading' => 'Content Background Color',
                    'param_name' => 'content_background_color',
                    'group' => 'Design Options'
                ),
				array(
					'type'        => 'dropdown',
					'heading'     => 'Button Type',
					'param_name'  => 'button_type',
					'value'       => array(
						'Outline' => 'outline',
						'Solid'   => 'solid',
						'Simple'  => 'simple',
					),
					'save_always' => true,
                    'group' => 'Design Options',
					'dependency' => array('element' => 'show_button',  'value' => 'yes')
				),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Button Size',
                    'param_name'  => 'button_size',
                    'value'       => array(
                        'Small'     => 'small',
                        'Medium'    => 'medium',
                        'Large'     => 'large',
                        'Huge'      => 'huge'
                    ),
                    'save_always' => true,
                    'group' => 'Design Options',
                    'dependency' => array('element' => 'show_button',  'value' => 'yes')
                ),
                array(
                    'type'        => 'colorpicker',
                    'heading'     => 'Button Color',
                    'param_name'  => 'button_color',
                    'group' => 'Design Options',
                    'dependency' => array('element' => 'show_button',  'value' => 'yes')
                ),
                array(
                    'type'        => 'colorpicker',
                    'heading'     => 'Hover Button Color',
                    'param_name'  => 'hover_button_color',
                    'group' => 'Design Options',
                    'dependency' => array('element' => 'show_button',  'value' => 'yes')
                ),
                array(
                    'type'        => 'colorpicker',
                    'heading'     => 'Button Background Color',
                    'param_name'  => 'button_background_color',
                    'group' => 'Design Options',
                    'dependency' => array('element' => 'show_button',  'value' => 'yes')
                ),
                array(
                    'type'        => 'colorpicker',
                    'heading'     => 'Hover Button Background Color',
                    'param_name'  => 'hover_button_background_color',
                    'group' => 'Design Options',
                    'dependency' => array('element' => 'show_button',  'value' => 'yes')
                ),
                array(
                    'type'        => 'colorpicker',
                    'heading'     => 'Button Border Color',
                    'param_name'  => 'button_border_color',
                    'group' => 'Design Options',
                    'dependency' => array('element' => 'show_button',  'value' => 'yes')
                ),
                array(
                    'type'        => 'colorpicker',
                    'heading'     => 'Hover Button Border Color',
                    'param_name'  => 'hover_button_border_color',
                    'group' => 'Design Options',
                    'dependency' => array('element' => 'show_button',  'value' => 'yes')
                )
			)
		));
	}

	public function render($atts, $content = null) {
	
		$args = array(
			'type'						    => 'title_above_price',
			'title'         			    => 'Basic Plan',
			'price'         			    => '100',
			'currency'      			    => '$',
			'price_period'  			    => 'Monthly',
            'price_description'  			=> '',
            'show_button'				    => 'yes',
            'button_text'   			    => 'button',
            'link'          			    => '',
            'content_border'                => '',
			'title_color'				    => '',
            'title_background_color'	    => '',
            'header_image'			        => '',
            'header_text_color'			    => '',
            'content_background_color'	    => '',
            'button_type'				    => '',
            'button_size'				    => '',
            'button_color'				    => '',
            'hover_button_color'			=> '',
            'button_background_color'		=> '',
            'hover_button_background_color' => '',
            'button_border_color'           => '',
            'hover_button_border_color'     => ''
        );
		$params = shortcode_atts($args, $atts);
//		extract($params);

		$params['button_size'] = !empty($params['button_size']) ? $params['button_size'] : 'medium';
		$params['button_type'] = !empty($params['button_type']) ? $params['button_type'] : 'solid';
		$params['link']   = !empty($params['link']) ? $params['link'] : '#';

        // prepare pricing table params
        $pricing_table_classes		= 'edgtf-price-table';

		if($params['type'] === 'title_above_price') {
			$pricing_table_classes .= ' edgtf-title-above-price';
		}
        else if($params['type'] === 'title_in_price') {
			$pricing_table_classes .= ' edgtf-title-in-price';
		}

        if($params['content_border'] === 'no') {
            $pricing_table_classes .= ' edgtf-price-table-no-border';
        }

		$params['pricing_table_classes'] = $pricing_table_classes;
		$params['content']= preg_replace('#^<\/p>|<p>$#', '', $content); // delete p tag before and after content
		$params['pricing_table_styles'] = $this->getPricingTableStyles($params);
		$params['pricing_title_styles'] = $this->getPricingTitleStyles($params);

        // prepare header styles
        $params['header_styles'] = $this->getHeaderStyles($params);

        //prepare button params
		$params['button_classes']      = $this->getButtonClasses($params);
		$params['button_styles']       = $this->getButtonStyles($params);
		$params['button_data']         = $this->getButtonDataAttr($params);

		// prepare template label
		$available_types = array('title_above_price', 'title_in_price');
		$template_label = (in_array($params['type'], $available_types)) ? str_replace('_', '-', $params['type']) : 'title-above-price';

		return walker_edge_get_shortcode_module_template_part("templates/pricing-table-{$template_label}-template",'pricing-table', '', $params);
	}

	/**
	 * Return pricing table styles
	 *
	 * @param $params
	 * @return array
	 */
	private function getPricingTableStyles($params) {

		$itemStyle = array();

		if ($params['content_background_color'] !== '') {
            $itemStyle[] = 'background-color: ' . $params['content_background_color'];
        }

		return implode(';', $itemStyle);
	}

	/**
	 * Return pricing table title styles
	 *
	 * @param $params
	 * @return array
	 */
	private function getPricingTitleStyles($params) {

		$itemStyle = array();

		if ($params['title_color'] !== '') {
            $itemStyle[] = 'color: ' . $params['title_color'];
        }

        if ($params['type'] === 'title_above_price' && $params['title_background_color'] !== '') {
            $itemStyle[] = 'background-color: ' . $params['title_background_color'];
        }

		return implode(';', $itemStyle);
	}

    /**
     * Return header styles
     *
     * @param $params
     * @return array
     */
    private function getHeaderStyles($params) {

        $headerStyles = array();

        if ($params['type'] === 'title_in_price' && $params['header_image'] !== '') {
            $headerStyles[] = 'border-bottom-color: transparent; background-image: url(' . wp_get_attachment_url($params['header_image']) . ')';
        }
        if ($params['header_text_color'] !== '') {
            $headerStyles[] = 'color: ' . $params['header_text_color'];
        }

        return implode(';', $headerStyles);
    }

	/**
	 * Returns array of button styles
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getButtonStyles($params) {
		$styles = array();

		if(!empty($params['color'])) {
			$styles[] = 'color: '.$params['color'];
		}

		if(!empty($params['background_color']) && $params['type'] !== 'outline') {
			$styles[] = 'background-color: '.$params['background_color'];
		}

		if(!empty($params['border_color'])) {
			$styles[] = 'border-color: '.$params['border_color'];
		}

		if(!empty($params['font_size'])) {
			$styles[] = 'font-size: '.walker_edge_filter_px($params['font_size']).'px';
		}

		return $styles;
	}

	/**
	 *
	 * Returns array of button data attr
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getButtonDataAttr($params) {
		$data = array();

		if(!empty($params['hover_color'])) {
			$data['data-hover-color'] = $params['hover_color'];
		}

		if(!empty($params['hover_background_color'])) {
			$data['data-hover-bg-color'] = $params['hover_background_color'];
		}

		if(!empty($params['hover_border_color'])) {
			$data['data-hover-border-color'] = $params['hover_border_color'];
		}

		return $data;
	}

	/**
	 * Returns array of HTML classes for button
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getButtonClasses($params) {
		$buttonClasses = array(
			'edgtf-btn',
			'edgtf-btn-'.$params['button_size'],
			'edgtf-btn-'.$params['button_type']
		);

		if(!empty($params['hover_color'])) {
			$buttonClasses[] = 'edgtf-btn-custom-hover-color';
		}

		if(!empty($params['hover_background_color'])) {
			$buttonClasses[] = 'edgtf-btn-custom-hover-bg';
		}

		if(!empty($params['hover_border_color'])) {
			$buttonClasses[] = 'edgtf-btn-custom-border-hover';
		}

		return $buttonClasses;
	}
}
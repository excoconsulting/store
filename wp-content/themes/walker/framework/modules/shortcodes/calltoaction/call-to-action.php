<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\CallToAction;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;
/**
 * Class CallToAction
 */
class CallToAction implements ShortcodeInterface {

	/**
	 * @var string
	 */
	private $base;

	public function __construct() {
		$this->base = 'edgtf_call_to_action';

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
	 * @see edgt_core_get_carousel_slider_array_vc()
	 */
	public function vcMap() {

        $call_to_action_button_1_icons_array = array();
        $call_to_action_button_IconCollections = walker_edge_icon_collections()->iconCollections;
        foreach($call_to_action_button_IconCollections as $collection_key => $collection) {

            $call_to_action_button_1_icons_array[] = array(
                'type' => 'dropdown',
                'heading' => 'Button 1 Icon',
                'param_name' => 'button_1_'.$collection->param,
                'value' => $collection->getIconsArray(),
                'save_always' => true,
                'dependency' => Array('element' => 'button_1_icon_pack', 'value' => array($collection_key)),
                'group'			=> 'Button 1 Options',
            );

        }

        $call_to_action_button_2_icons_array = array();
        $call_to_action_button_IconCollections = walker_edge_icon_collections()->iconCollections;
        foreach($call_to_action_button_IconCollections as $collection_key => $collection) {

            $call_to_action_button_2_icons_array[] = array(
                'type' => 'dropdown',
                'heading' => 'Button 2 Icon',
                'param_name' => 'button_2_'.$collection->param,
                'value' => $collection->getIconsArray(),
                'save_always' => true,
                'dependency' => Array('element' => 'button_2_icon_pack', 'value' => array($collection_key)),
                'group'			=> 'Button 2 Options',
            );

        }

		vc_map( array(
				'name' => esc_html__('Edge Call To Action', 'walker'),
				'base' => $this->getBase(),
				'category' => 'by EDGE',
				'icon' => 'icon-wpb-call-to-action extended-custom-icon',
				'allowed_container_element' => 'vc_row',
				'params' => array_merge(
                    array(
                        array(
                            'type' => 'textarea_html',
                            'admin_label' => true,
                            'heading' => 'Content',
                            'param_name' => 'content',
                            'value' => '<p>'.'I am test text for Call to action.'.'</p>',
                            'description' => ''
                        )
                    ),
					array(
						array(
							'type' 			=> 'textfield',
							'heading' 		=> 'Default Text Font Size (px)',
							'param_name' 	=> 'text_size',
							'description' 	=> 'Font size for p tag',
							'group'			=> 'Design Options',
						),
                        array(
							'type' 			=> 'textfield',
							'heading' 		=> 'Space Between Text and Buttons (px)',
							'param_name' 	=> 'space_below_text',
							'description' 	=> '',
							'group'			=> 'Design Options',
						)
					),
                    array(
                        array(
                            'type' => 'dropdown',
                            'heading' => 'Button 1 Size',
                            'param_name' => 'button_1_size',
                            'value' => array(
                                'Default' => '',
                                'Small' => 'small',
                                'Medium' => 'medium',
                                'Large' => 'large'
                            ),
                            'description' => '',
                            'group'			=> 'Button 1 Options',
                        ),
                        array(
                            'type' => 'textfield',
                            'heading' => 'Button 1 Text',
                            'param_name' => 'button_1_text',
                            'admin_label' 	=> true,
                            'description' => '',
                            'group'			=> 'Button 1 Options',
                        ),
                        array(
                            'type'        => 'colorpicker',
                            'heading'     => 'Button 1 Color',
                            'param_name'  => 'button_1_color',
                            'group'       => 'Button 1 Options',
                            'admin_label' => true
                        ),
                        array(
                            'type'        => 'colorpicker',
                            'heading'     => 'Button 1 Hover Color',
                            'param_name'  => 'button_1_hover_color',
                            'group'       => 'Button 1 Options',
                            'admin_label' => true
                        ),
                        array(
                            'type'        => 'colorpicker',
                            'heading'     => 'Button 1 Background Color',
                            'param_name'  => 'button_1_background_color',
                            'admin_label' => true,
                            'group'       => 'Button 1 Options'
                        ),
                        array(
                            'type'        => 'colorpicker',
                            'heading'     => 'Button 1 Hover Background Color',
                            'param_name'  => 'button_1_hover_background_color',
                            'admin_label' => true,
                            'group'       => 'Button 1 Options'
                        ),
                        array(
                            'type'        => 'colorpicker',
                            'heading'     => 'Button 1 Border Color',
                            'param_name'  => 'button_1_border_color',
                            'admin_label' => true,
                            'group'		  => 'Button 1 Options',
                        ),
                        array(
                            'type'        => 'colorpicker',
                            'heading'     => 'Button 1 Hover Border Color',
                            'param_name'  => 'button_1_hover_border_color',
                            'admin_label' => true,
                            'group'		  => 'Button 1 Options',
                        ),
                        array(
                            'type' => 'textfield',
                            'heading' => 'Button 1 Link',
                            'param_name' => 'button_1_link',
                            'description' => '',
                            'admin_label' 	=> true,
                            'group'			=> 'Button 1 Options',
                        ),
                        array(
                            'type' => 'dropdown',
                            'heading' => 'Button 1 Target',
                            'param_name' => 'button_1_target',
                            'value' => array(
                                '' => '',
                                'Self' => '_self',
                                'Blank' => '_blank'
                            ),
                            'description' => '',
                            'group'			=> 'Button 1 Options',
                        ),
                        array(
                            'type' => 'dropdown',
                            'heading' => 'Button 1 Icon Pack',
                            'param_name' => 'button_1_icon_pack',
                            'value' => array_merge(array('No Icon' => ''),walker_edge_icon_collections()->getIconCollectionsVC()),
                            'save_always' => true,
                            'group'			=> 'Button 1 Options',
                        )
                    ),
                    $call_to_action_button_1_icons_array,
                    array(
                        array(
                            'type' => 'dropdown',
                            'heading' => 'Button 2 Size',
                            'param_name' => 'button_2_size',
                            'value' => array(
                                'Default' => '',
                                'Small' => 'small',
                                'Medium' => 'medium',
                                'Large' => 'large'
                            ),
                            'description' => '',
                            'group'			=> 'Button 2 Options',
                        ),
                        array(
                            'type' => 'textfield',
                            'heading' => 'Button 2 Text',
                            'param_name' => 'button_2_text',
                            'admin_label' 	=> true,
                            'description' => '',
                            'group'			=> 'Button 2 Options',
                        ),
                        array(
                            'type'        => 'colorpicker',
                            'heading'     => 'Button 2 Color',
                            'param_name'  => 'button_2_color',
                            'group'       => 'Button 2 Options',
                            'admin_label' => true
                        ),
                        array(
                            'type'        => 'colorpicker',
                            'heading'     => 'Button 2 Hover Color',
                            'param_name'  => 'button_2_hover_color',
                            'group'       => 'Button 2 Options',
                            'admin_label' => true
                        ),
                        array(
                            'type'        => 'colorpicker',
                            'heading'     => 'Button 2 Background Color',
                            'param_name'  => 'button_2_background_color',
                            'admin_label' => true,
                            'group'       => 'Button 2 Options'
                        ),
                        array(
                            'type'        => 'colorpicker',
                            'heading'     => 'Button 2 Hover Background Color',
                            'param_name'  => 'button_2_hover_background_color',
                            'admin_label' => true,
                            'group'       => 'Button 2 Options'
                        ),
                        array(
                            'type'        => 'colorpicker',
                            'heading'     => 'Button 2 Border Color',
                            'param_name'  => 'button_2_border_color',
                            'admin_label' => true,
                            'group'		  => 'Button 2 Options',
                        ),
                        array(
                            'type'        => 'colorpicker',
                            'heading'     => 'Button 2 Hover Border Color',
                            'param_name'  => 'button_2_hover_border_color',
                            'admin_label' => true,
                            'group'		  => 'Button 2 Options',
                        ),
                        array(
                            'type' => 'textfield',
                            'heading' => 'Button 2 Link',
                            'param_name' => 'button_2_link',
                            'description' => '',
                            'admin_label' 	=> true,
                            'group'			=> 'Button 2 Options',
                        ),
                        array(
                            'type' => 'dropdown',
                            'heading' => 'Button 2 Target',
                            'param_name' => 'button_2_target',
                            'value' => array(
                                '' => '',
                                'Self' => '_self',
                                'Blank' => '_blank'
                            ),
                            'description' => '',
                            'group'			=> 'Button 2 Options',
                        ),
                        array(
                            'type' => 'dropdown',
                            'heading' => 'Button 2 Icon Pack',
                            'param_name' => 'button_2_icon_pack',
                            'value' => array_merge(array('No Icon' => ''),walker_edge_icon_collections()->getIconCollectionsVC()),
                            'save_always' => true,
                            'group'			=> 'Button 2 Options',
                        )
                    ),
                    $call_to_action_button_2_icons_array
				)
		) );

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
			'text_size' => '',
			'space_below_text' => '',
            'button_1_size' 				  => '',
            'button_1_link' 				  => '',
            'button_1_text' 				  => '',
            'button_1_target' 				  => '',
            'button_1_icon_pack' 			  => '',
            'button_1_color'	              => '',
            'button_1_hover_color'            => '',
            'button_1_background_color'       => '',
            'button_1_hover_background_color' => '',
            'button_1_border_color'           => '',
            'button_1_hover_border_color'     => '',
            'button_2_size' 				  => '',
            'button_2_link' 				  => '',
            'button_2_text' 				  => '',
            'button_2_target' 				  => '',
            'button_2_icon_pack' 			  => '',
            'button_2_color' 				  => '',
            'button_2_hover_color'            => '',
            'button_2_background_color'       => '',
            'button_2_hover_background_color' => '',
            'button_2_border_color'           => '',
            'button_2_hover_border_color'     => '',
		);

		$call_to_action_button_1_form_fields = array();
		$call_to_action_button_2_form_fields = array();

		foreach (walker_edge_icon_collections()->iconCollections as $collection_key => $collection) {

			$call_to_action_button_1_form_fields['button_1_' . $collection->param ] = '';
			$call_to_action_button_2_form_fields['button_2_' . $collection->param ] = '';

		}

        $args = array_merge($args, $call_to_action_button_1_form_fields, $call_to_action_button_2_form_fields);

		$params = shortcode_atts($args, $atts);

		$params['content'] = $content = preg_replace('#^<\/p>|<p>$#', '', $content);
		$params['content_styles'] = $this->getContentStyles($params);
		$params['button_1_parameters'] = $this->getButtonParameters($params, '1');
		$params['button_2_parameters'] = $this->getButtonParameters($params, '2');

		//Get HTML from template
		$html = walker_edge_get_shortcode_module_template_part('templates/call-to-action-with-buttons', 'calltoaction', '', $params);

		return $html;

	}

	/**
	 * Return CSS styles for Call To Action Content
	 *
	 * @param $params
	 * @return string
	 */
	private function getContentStyles($params) {
		$content_styles = array();

		if ($params['text_size'] !== '') {
			$content_styles[] = 'font-size: ' . walker_edge_filter_px($params['text_size']) . 'px';
		}
        if ($params['space_below_text'] !== '') {
            $content_styles[] = 'margin-bottom: ' . walker_edge_filter_px($params['space_below_text']) . 'px';
        }

		return implode(';', $content_styles);
	}
	
	private function getButtonParameters($params, $index) {
		$button_params_array = array();

        if(!empty($params['button_'.$index.'_text'])) {
            $button_params_array['text'] = $params['button_'.$index.'_text'];
        } else {
            $button_params_array['text'] = false;
        }

		if(!empty($params['button_'.$index.'_link'])) {
			$button_params_array['link'] = $params['button_'.$index.'_link'];
		} else {
            $button_params_array['link'] = false;
        }
		
		if(!empty($params['button_'.$index.'_size'])) {
			$button_params_array['size'] = $params['button_'.$index.'_size'];
		}
		
		if(!empty($params['button_'.$index.'_icon_pack'])) {
			$button_params_array['icon_pack'] = $params['button_'.$index.'_icon_pack'];
			$iconPackName = walker_edge_icon_collections()->getIconCollectionParamNameByKey($params['button_'.$index.'_icon_pack']);
			$button_params_array[$iconPackName] = $params['button_'.$index.'_'.$iconPackName];
		}
				
		if(!empty($params['button_'.$index.'_target'])) {
			$button_params_array['target'] = $params['button_'.$index.'_target'];
		}

        if(!empty($params['button_'.$index.'_target'])) {
            $button_params_array['target'] = $params['button_'.$index.'_target'];
        }

        if(!empty($params['button_'.$index.'_color'])) {
            $button_params_array['color'] = $params['button_'.$index.'_color'];
        }

        if(!empty($params['button_'.$index.'_hover_color'])) {
            $button_params_array['hover_color'] = $params['button_'.$index.'_hover_color'];
        }

        if(!empty($params['button_'.$index.'_background_color'])) {
            $button_params_array['background_color'] = $params['button_'.$index.'_background_color'];
        }

        if(!empty($params['button_'.$index.'_hover_background_color'])) {
            $button_params_array['hover_background_color'] = $params['button_'.$index.'_hover_background_color'];
        }

        if(!empty($params['button_'.$index.'_border_color'])) {
            $button_params_array['border_color'] = $params['button_'.$index.'_border_color'];
        }

        if(!empty($params['button_'.$index.'_hover_border_color'])) {
            $button_params_array['hover_border_color'] = $params['button_'.$index.'_hover_border_color'];
        }
		
		return $button_params_array;
	}
}
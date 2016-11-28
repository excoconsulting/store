<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\Message;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class Message
 */
class Message implements ShortcodeInterface	{
	private $base; 
	
	function __construct() {
		$this->base = 'edgtf_message';

		add_action('vc_before_init', array($this, 'vcMap'));
	}
	
	/**
		* Returns base for shortcode
		* @return string
	 */
	public function getBase() {
		return $this->base;
	}	
	public function vcMap() {
						
		vc_map( array(
			'name' => esc_html__('Edge Message', 'walker'),
			'base' => $this->base,
			'category' => 'by EDGE',
			'icon' => 'icon-wpb-message extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params' => array_merge(
				array(
					array(
						'type' => 'dropdown',
						'admin_label' => true,
						'heading' => 'Type',
						'param_name' => 'type',
						'value' => array(
							'Normal' => 'normal',
							'With Icon' => 'with_icon'
						),
						'save_always' => true
					)
				),
				\WalkerEdgeClassIconCollections::get_instance()->getVCParamsArray(Array('element' => 'type', 'value' => array('with_icon'))),
				array(
					array(
						'type' => 'colorpicker',
						'heading' => 'Icon Color',
						'param_name' => 'icon_color',
						'description' => '',
						'dependency' => Array('element' => 'type', 'value' => array('with_icon')),
						'group' => 'Design Options'
					),
					array(
						'type' => 'colorpicker',
						'heading' => 'Icon Background Color',
						'param_name' => 'icon_background_color',
						'description' => '',
						'dependency' => Array('element' => 'type', 'value' => array('with_icon')),
						'group' => 'Design Options'
					),
					array(
						'type' => 'colorpicker',
						'heading' => 'Background Color',
						'param_name' => 'background_color',
						'description' => '',
						'group' => 'Design Options'
					),
					array(
						'type' => 'colorpicker',
						'heading' => 'Border Color',
						'param_name' => 'border_color',
						'description' => '',
						'group' => 'Design Options'
					),
					array(
						'type' => 'textfield',
						'heading' => 'Border Width (px)',
						'param_name' => 'border_width',
						'description' => 'Default value is 0',
						'group' => 'Design Options'
					),
					array(
						'type' => 'colorpicker',
						'heading' => 'Close Mark Color',
						'param_name' => 'close_mark_color',
						'description' => '',
						'group' => 'Design Options'
					),
					array(
						'type' => 'textarea_html',
						'heading' => 'Content',
						'param_name' => 'content',
						'value' => '<p>'.'I am test text for Message shortcode.'.'</p>',
						'description' => ''
					)
				)
			)
		) );

	}

	public function render($atts, $content = null) {
		
		$args = array(
            'type' => '',
            'background_color' => '',
            'border_color' => '',
            'border_width' => '',
            'icon_size' => '',
            'icon_custom_size' => '',
            'icon_color' => '',
            'icon_background_color' => '',
            'close_mark_color' => ''
        );
		
		$args = array_merge($args, walker_edge_icon_collections()->getShortcodeParams());
		$params = shortcode_atts($args, $atts);
		$params['content']= preg_replace('#^<\/p>|<p>$#', '', $content);

		//Extract params for use in method
		extract($params);
		//Get HTML from template based on type of team
		$iconPackName = walker_edge_icon_collections()->getIconCollectionParamNameByKey($params['icon_pack']);
		$message_classes = '';
		
		if ($type == 'with_icon') {
			$message_classes .= ' edgtf-with-icon';
		}
		$params['message_classes'] = $message_classes;
		$params['message_styles'] = $this->getHolderStyle($params);
		$params['icon'] = $params[$iconPackName];
		$params['icon_attributes']['style'] = $this->getIconStyle($params);
		$params['message_close_styles'] = $this->getCloseMarkStyle($params);
		
		$html = walker_edge_get_shortcode_module_template_part('templates/message-holder-template', 'message', '', $params);
		
		return $html;
	}
	/**
     * Generates message icon styles
     *
     * @param $params
     *
     * @return array
     */
	private function getIconStyle($params){
		$iconStyles = array();

        if(!empty($params['icon_color'])) {
            $iconStyles[] = 'color: '.$params['icon_color'];
        }

        if(!empty($params['icon_background_color'])) {
            $iconStyles[] = 'background-color:'.$params['icon_background_color'].'';
        }

        return implode(';', $iconStyles);
	
	}
	 /**
     * Generates message holder styles
     *
     * @param $params
     *
     * @return array
     */
	private function getHolderStyle($params){
		$holderStyles = array();

        if(!empty($params['background_color'])) {
            $holderStyles[] = 'background-color: '.$params['background_color'];
        }

        if(!empty($params['border_width']) || !empty($params['border_color'])) {

            $holderStyles[] = 'border-style: solid';

            if(!empty($params['border_color'])) {
                $holderStyles[] = 'border-color:'.$params['border_color'];
            }

            if(!empty($params['border_width'])) {
                $holderStyles[] = 'border-width:'.walker_edge_filter_px($params['border_width']).'px';
            }
        }

        return implode(';', $holderStyles);
	}

	/**
     * Generates message close mark styles
     *
     * @param $params
     *
     * @return array
     */
	private function getCloseMarkStyle($params){
		$closeMarkStyles = array();
		
		if(!empty($params['close_mark_color'])) {
            $closeMarkStyles[] = 'color: '.$params['close_mark_color'];
        }

        return implode(';', $closeMarkStyles);
	}
}
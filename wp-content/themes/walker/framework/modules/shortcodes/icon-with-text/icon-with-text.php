<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\IconWithText;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class IconWithText
 * @package WalkerEdgeNamespace\Modules\Shortcodes\IconWithText
 */
class IconWithText implements ShortcodeInterface {
    /**
     * @var string
     */
    private $base;

    /**
     *
     */
    public function __construct() {
        $this->base = 'edgtf_icon_with_text';

        add_action('vc_before_init', array($this, 'vcMap'));
    }

    /**
     * @return string
     */
    public function getBase() {
        return $this->base;
    }

    /**
     *
     */
    public function vcMap() {
        vc_map(array(
            'name'                      => esc_html__('Edge Icon With Text', 'walker'),
            'base'                      => $this->base,
            'icon'                      => 'icon-wpb-icon-with-text extended-custom-icon',
            'category'                  => 'by EDGE',
            'allowed_container_element' => 'vc_row',
            'params'                    => array_merge(
                array(
                    array(
                        'type'        => 'textfield',
                        'heading'     => 'Custom CSS Class',
                        'param_name'  => 'custom_class',
                        'admin_label' => true
                    )
                ),
                walker_edge_icon_collections()->getVCParamsArray(),
                array(
                    array(
                        'type'       => 'attach_image',
                        'heading'    => 'Custom Icon',
                        'param_name' => 'custom_icon'
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => 'Icon Position',
                        'param_name'  => 'icon_position',
                        'value'       => array(
                            'Top'             => 'top',
                            'Left'            => 'left',
                            'Left From Title' => 'left-from-title'
                        ),
                        'description' => 'Icon Position',
                        'save_always' => true,
                        'admin_label' => true
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => 'Icon Flip on Hover',
                        'param_name'  => 'icon_flip',
                        'value'       => array(
                            'Yes'             => 'yes',
                            'No'            => 'no',
                        ),
                        'description' => 'Available for Top and Left from Title Icon Position layouts.',
                        'dependency' => array('element' => 'custom_icon', 'not_empty' => true),
                        'save_always' => true,
                        'admin_label' => true
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => 'Icon Type',
                        'param_name'  => 'icon_type',
                        'value'       => array(
                            'Normal' => 'normal',
                            'Circle' => 'circle',
                            'Square' => 'square'
                        ),
                        'save_always' => true,
                        'admin_label' => true,
                        'group'       => 'Icon Settings',
                        'description' => 'This attribute doesn\'t work when Icon Position is Top. In This case Icon Type is Normal',
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => 'Icon Size',
                        'param_name'  => 'icon_size',
                        'value'       => array(
                            'Tiny'       => 'edgtf-icon-tiny',
                            'Small'      => 'edgtf-icon-small',
                            'Medium'     => 'edgtf-icon-medium',
                            'Large'      => 'edgtf-icon-large',
                            'Very Large' => 'edgtf-icon-huge'
                        ),
                        'admin_label' => true,
                        'save_always' => true,
                        'group'       => 'Icon Settings',
                        'description' => 'This attribute doesn\'t work when Icon Position is Top'
                    ),
                    array(
                        'type'       => 'textfield',
                        'heading'    => 'Custom Icon Size (px)',
                        'param_name' => 'custom_icon_size',
                        'group'      => 'Icon Settings'
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => 'Icon Animation',
                        'param_name'  => 'icon_animation',
                        'value'       => array(
                            'No'  => '',
                            'Yes' => 'yes'
                        ),
                        'group'       => 'Icon Settings',
                        'save_always' => true
                    ),
                    array(
                        'type'       => 'textfield',
                        'heading'    => 'Icon Animation Delay (ms)',
                        'param_name' => 'icon_animation_delay',
                        'group'      => 'Icon Settings',
                        'dependency' => array('element' => 'icon_animation', 'value' => array('yes'))
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => 'Icon Margin',
                        'param_name'  => 'icon_margin',
                        'value'       => '',
                        'description' => 'Margin should be set in a top right bottom left format',
                        'group'       => 'Icon Settings',
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => 'Shape Size (px)',
                        'param_name'  => 'shape_size',
                        'description' => '',
                        'group'       => 'Icon Settings'
                    ),
                    array(
                        'type'       => 'colorpicker',
                        'heading'    => 'Icon Color',
                        'param_name' => 'icon_color',
                        'group'      => 'Icon Settings'
                    ),
                    array(
                        'type'       => 'colorpicker',
                        'heading'    => 'Icon Hover Color',
                        'param_name' => 'icon_hover_color',
                        'group'      => 'Icon Settings'
                    ),
                    array(
                        'type'        => 'colorpicker',
                        'heading'     => 'Icon Background Color',
                        'param_name'  => 'icon_background_color',
                        'description' => 'Icon Background Color (only for square and circle icon type)',
                        'dependency'  => array('element' => 'icon_type', 'value' => array('square', 'circle')),
                        'group'       => 'Icon Settings'
                    ),
                    array(
                        'type'        => 'colorpicker',
                        'heading'     => 'Icon Hover Background Color',
                        'param_name'  => 'icon_hover_background_color',
                        'description' => 'Icon Hover Background Color (only for square and circle icon type)',
                        'dependency'  => array('element' => 'icon_type', 'value' => array('square', 'circle')),
                        'group'       => 'Icon Settings'
                    ),
                    array(
                        'type'        => 'colorpicker',
                        'heading'     => 'Icon Border Color',
                        'param_name'  => 'icon_border_color',
                        'description' => 'Only for Square and Circle Icon type',
                        'dependency'  => array('element' => 'icon_type', 'value' => array('square', 'circle')),
                        'group'       => 'Icon Settings'
                    ),
                    array(
                        'type'        => 'colorpicker',
                        'heading'     => 'Icon Border Hover Color',
                        'param_name'  => 'icon_border_hover_color',
                        'description' => 'Only for Square and Circle Icon type',
                        'dependency'  => array('element' => 'icon_type', 'value' => array('square', 'circle')),
                        'group'       => 'Icon Settings'
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => 'Border Width',
                        'param_name'  => 'icon_border_width',
                        'description' => 'Only for Square and Circle Icon type',
                        'dependency'  => array('element' => 'icon_type', 'value' => array('square', 'circle')),
                        'group'       => 'Icon Settings'
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => 'Title',
                        'param_name'  => 'title',
                        'value'       => '',
                        'admin_label' => true
                    ),
                    array(
                        'type'       => 'dropdown',
                        'heading'    => 'Title Tag',
                        'param_name' => 'title_tag',
                        'value'      => array(
                            ''   => '',
                            'h2' => 'h2',
                            'h3' => 'h3',
                            'h4' => 'h4',
                            'h5' => 'h5',
                            'h6' => 'h6',
                        ),
						'save_always' => true,
                        'dependency' => array('element' => 'title', 'not_empty' => true),
                        'group'      => 'Text Settings'
                    ),
                    array(
                        'type'       => 'colorpicker',
                        'heading'    => 'Title Color',
                        'param_name' => 'title_color',
                        'dependency' => array('element' => 'title', 'not_empty' => true),
                        'group'      => 'Text Settings'
                    ),
                    array(
                        'type'       => 'textarea',
                        'heading'    => 'Text',
                        'param_name' => 'text'
                    ),
                    array(
                        'type'       => 'colorpicker',
                        'heading'    => 'Text Color',
                        'param_name' => 'text_color',
                        'dependency' => array('element' => 'text', 'not_empty' => true),
                        'group'      => 'Text Settings'
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => 'Link',
                        'param_name'  => 'link',
                        'value'       => ''
                    ),
                    array(
                        'type'       => 'textfield',
                        'heading'    => 'Link Text',
                        'param_name' => 'link_text',
                        'dependency' => array('element' => 'link', 'not_empty' => true)
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
						'save_always' => true,
                        'dependency' => array('element' => 'link', 'not_empty' => true),
                    ),
                    array(
                        'type'       => 'textfield',
                        'heading'    => 'Text Left Padding (px)',
                        'param_name' => 'text_left_padding',
                        'dependency' => array('element' => 'icon_position', 'value' => array('left')),
                        'group'      => 'Text Settings'
                    )
                )
            )
        ));
    }

    /**
     * @param array $atts
     * @param null $content
     *
     * @return string
     */
    public function render($atts, $content = null) {
        $default_atts = array(
            'custom_class'                => '',
            'custom_icon'                 => '',
            'icon_flip'                   => 'yes',
            'icon_position'               => '',
            'icon_type'                   => '',
            'icon_size'                   => '',
            'custom_icon_size'            => '',
            'icon_animation'              => '',
            'icon_animation_delay'        => '',
            'icon_margin'                 => '',
            'shape_size'                  => '',
            'icon_color'                  => '',
            'icon_hover_color'            => '',
            'icon_background_color'       => '',
            'icon_hover_background_color' => '',
            'icon_border_color'           => '',
            'icon_border_hover_color'     => '',
            'icon_border_width'           => '',
            'title'                       => '',
            'title_tag'                   => 'h4',
            'title_color'                 => '',
            'text'                        => '',
            'text_color'                  => '',
            'link'                        => '',
            'link_text'                   => '',
            'target'                      => '_self',
            'text_left_padding'           => ''
        );

        $default_atts = array_merge($default_atts, walker_edge_icon_collections()->getShortcodeParams());
        $params       = shortcode_atts($default_atts, $atts);
		
		$tag_array = array('h2', 'h3', 'h4', 'h5', 'h6');
        $params['title_tag'] = (in_array($params['title_tag'], $tag_array)) ? $params['title_tag'] : $default_atts['title_tag'];

        $params['icon_parameters'] = $this->getIconParameters($params);
        $params['holder_classes']  = $this->getHolderClasses($params);
        $params['title_styles']    = $this->getTitleStyles($params);
        $params['content_styles']  = $this->getContentStyles($params);
        $params['text_styles']     = $this->getTextStyles($params);
        $params['button_params']   = $this->getButtonStyles($params);
		
		$params['template'] = $this->getTemplateName($params);
        
		// return walker_edge_get_shortcode_module_template_part('templates/'.$params['template'], 'icon-with-text', $params);
		return walker_edge_get_shortcode_module_template_part('templates/iwt', 'icon-with-text', $params['icon_position'], $params);
    }

    /**
     * Returns parameters for icon shortcode as a string
     *
     * @param $params
     *
     * @return array
     */
    private function getIconParameters($params) {
        $params_array = array();

        if(empty($params['custom_icon'])) {
            $iconPackName = walker_edge_icon_collections()->getIconCollectionParamNameByKey($params['icon_pack']);

            $params_array['icon_pack']   = $params['icon_pack'];
            $params_array[$iconPackName] = $params[$iconPackName];

            if(!empty($params['icon_size'])) {
                $params_array['size'] = $params['icon_size'];
            }

            if(!empty($params['custom_icon_size'])) {
                $params_array['custom_size'] = $params['custom_icon_size'];
            }

            if(!empty($params['icon_type'])) {
                $params_array['type'] = $params['icon_type'];
            }

            $params_array['shape_size'] = $params['shape_size'];

            if(!empty($params['icon_border_color'])) {
                $params_array['border_color'] = $params['icon_border_color'];
            }

            if(!empty($params['icon_border_hover_color'])) {
                $params_array['hover_border_color'] = $params['icon_border_hover_color'];
            }

            if(!empty($params['icon_border_width'])) {
                $params_array['border_width'] = $params['icon_border_width'];
            }

            if(!empty($params['icon_background_color'])) {
                $params_array['background_color'] = $params['icon_background_color'];
            }

            if(!empty($params['icon_hover_background_color'])) {
                $params_array['hover_background_color'] = $params['icon_hover_background_color'];
            }

            $params_array['icon_color'] = $params['icon_color'];

            if(!empty($params['icon_hover_color'])) {
                $params_array['hover_icon_color'] = $params['icon_hover_color'];
            }

            $params_array['icon_animation']       = $params['icon_animation'];
            $params_array['icon_animation_delay'] = $params['icon_animation_delay'];
            $params_array['margin']               = $params['icon_margin'];
        }

        return $params_array;
    }

    /**
     * Returns array of holder classes
     *
     * @param $params
     *
     * @return array
     */
    private function getHolderClasses($params) {
        $classes = array('edgtf-iwt', 'clearfix');

        if(!empty($params['custom_class'])) {
            $classes[] = $params['custom_class'];
        }

        if(!empty($params['icon_position'])) {
            switch($params['icon_position']) {
                case 'top':
                    $classes[] = 'edgtf-iwt-icon-top';
                    break;
                case 'left':
                    $classes[] = 'edgtf-iwt-icon-left';
                    break;
                case 'left-from-title':
                    $classes[] = 'edgtf-iwt-left-from-title';
                    break;
                default:
                    break;
            }
        }

        if(!empty($params['icon_size'])) {
            $classes[] = 'edgtf-iwt-'.str_replace('edgtf-', '', $params['icon_size']);
        }

        if (!empty($params['icon_flip'])) {
            $classes [] = 'edgtf-iwt-flip';
        }

        return $classes;
    }

	/**
     * Returns inline title styles
     *
     * @param $params
     *
     * @return string
     */
    private function getTitleStyles($params) {
        $styles = array();

        if(!empty($params['title_color'])) {
            $styles[] = 'color: '.$params['title_color'];
        }

        return implode(';', $styles);
    }

	/**
     * Returns inline text styles
     *
     * @param $params
     *
     * @return string
     */
    private function getTextStyles($params) {
        $styles = array();

        if(!empty($params['text_color'])) {
            $styles[] = 'color: '.$params['text_color'];
        }

        return implode(';', $styles);
    }

	/**
     * Returns inline content styles
     *
     * @param $params
     *
     * @return string
     */
    private function getContentStyles($params) {
        $styles = array();

        if($params['icon_position'] == 'left' && !empty($params['text_left_padding'])) {
            $styles[] = 'padding-left: ' . walker_edge_filter_px($params['text_left_padding']) . 'px';
        }

        return implode(';', $styles);
    }

	/**
     * Returns array of button styles
     *
     * @param $params
     *
     * @return array
     */
    private function getButtonStyles($params) {
        $button_params = array();

        if ($params['link'] != '') {
            $button_params['custom_class'] = 'edgtf-iwt-link';
            $button_params['type'] = 'simple';
            $button_params['link'] = $params['link'];

            if ($params['link_text'] != '') {
                $button_params['text'] = $params['link_text'];
            }
            if ($params['target'] != '') {
                $button_params['target'] = $params['target'];
            }
        }

        return $button_params;
    }
	
	/**
     * Returns Template Name
     *
     * @param $params
     *
     * @return string
     */
	private function getTemplateName($params) {
			
		$template_name = 'iwt';
			
		if(!empty($params['icon_position'])) {
            switch($params['icon_position']) {
                case 'top':
                    $template_name = 'iwt';
                    break;
                case 'left':
                    $template_name = 'iwt';
                    break;
                case 'left-from-title':
                    $template_name = 'iwt-left-from-title';
                    break;
                default:
                    break;
            }
        }
		
		return $template_name;
	}
}
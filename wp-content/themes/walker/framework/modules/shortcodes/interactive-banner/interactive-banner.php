<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\InteractiveBanner;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;

class InteractiveBanner implements ShortcodeInterface{

    private $base;

    /**
     * Interactive Banner constructor.
     */
    public function __construct() {
        $this->base = 'edgtf_interactive_banner';

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
     * @see edgt_core_get_image_with_text_array_vc()
     */
    public function vcMap() {

        vc_map(array(
            'name'                      => esc_html__('Edge Interactive Banner', 'walker'),
            'base'                      => $this->getBase(),
            'category'                  => 'by EDGE',
            'icon' 						=> 'icon-wpb-interactive-banner extended-custom-icon',
            'allowed_container_element' => 'vc_row',
            'params'                    => array(
                // General
                array(
                    'type'			=> 'attach_image',
                    'heading'		=> 'Image',
                    'param_name'	=> 'image',
                    'description'	=> 'Select image from media library'
                ),
                array(
                    'type'        => 'textfield',
                    'heading'     => 'Title',
                    'param_name'  => 'title',
                    'value'       => '',
                    'admin_label' => true
                ),
                array(
                    'type'       => 'textarea',
                    'heading'    => 'Description',
                    'param_name' => 'description'
                ),
                array(
                    'type'        => 'textfield',
                    'heading'     => 'Link',
                    'param_name'  => 'link',
                    'value'       => '',
                    'admin_label' => true
                ),
                array(
                    'type'        => 'textfield',
                    'heading'     => 'Link Text',
                    'param_name'  => 'link_text',
                    'description' => '',
                    'dependency'  => array('element' => 'link', 'not_empty' => true)
                ),
                array(
                    'type'       => 'dropdown',
                    'heading'    => 'Target',
                    'param_name' => 'target',
                    'value'      => array(
                        'Same Window'  => '_self',
                        'New Window' => '_blank'
                    ),
                    'dependency' => array('element' => 'link', 'not_empty' => true),
                ),

                // Design Options
                array(
                    'type'       => 'dropdown',
                    'heading'    => 'Text Align',
                    'param_name' => 'text_align',
                    'value'      => array(
                        'Left'   => 'left',
                        'Center' => 'center',
                        'Right'  => 'right'
                    ),
                    'group'       => 'Design Options'
                ),
                array(
                    'type'        => 'textfield',
                    'heading'     => 'Content Padding (%)',
                    'param_name'  => 'content_padding',
                    'description' => 'Please insert padding in format (top right bottom left). Default value is 5% 5% 5% 5%',
                    'group'       => 'Design Options'
                ),
                array(
                    'type'        => 'colorpicker',
                    'heading'     => 'Title Color',
                    'param_name'  => 'title_color',
                    'group'       => 'Design Options',
                    'dependency' => array('element' => 'title', 'not_empty' => true)
                ),
                array(
                    'type'       => 'dropdown',
                    'heading'    => 'Title Tag',
                    'param_name' => 'title_tag',
                    'value'      => array(
                        'h2' => 'h2',
                        'h3' => 'h3',
                        'h4' => 'h4',
                        'h5' => 'h5',
                        'h6' => 'h6',
                    ),
                    'group'       => 'Design Options',
                    'dependency' => array('element' => 'title', 'not_empty' => true)
                ),
                array(
                    'type'        => 'colorpicker',
                    'heading'     => 'Description Color',
                    'param_name'  => 'description_color',
                    'group'       => 'Design Options',
                    'dependency' => array('element' => 'description', 'not_empty' => true)
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Button Type',
                    'param_name'  => 'button_type',
                    'value'       => array(
                        'Simple'  => 'simple',
                        'Outline' => 'outline',
                        'Solid'   => 'solid',
                    ),
                    'save_always' => true,
                    'group'       => 'Design Options'
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Button Size',
                    'param_name'  => 'button_size',
                    'value'       => array(
                        'Default'   => '',
                        'Small'     => 'small',
                        'Medium'    => 'medium',
                        'Large'     => 'large',
                        'Huge'      => 'huge'
                    ),
                    'save_always' => true,
                    'dependency'  => array('element' => 'button_type', 'value' => array('solid', 'outline')),
                    'group'       => 'Design Options'
                ),
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Enable Button Animate Text',
                    'param_name'  => 'enable_button_animate_text',
                    'value'       => array(
                        'No'     => 'no',
                        'Yes'   => 'yes'
                    ),
                    'save_always' => true,
                    'dependency'  => array('element' => 'button_type', 'value' => array('solid')),
                    'group'       => 'Design Options'
                ),
                array(
                    'type'        => 'colorpicker',
                    'heading'     => 'Button Color',
                    'param_name'  => 'button_color',
                    'group'       => 'Design Options'
                ),
                array(
                    'type'        => 'colorpicker',
                    'heading'     => 'Button Hover Color',
                    'param_name'  => 'button_hover_color',
                    'group'       => 'Design Options'
                ),
                array(
                    'type'        => 'colorpicker',
                    'heading'     => 'Button Background Color',
                    'param_name'  => 'button_background_color',
                    'dependency'  => array('element' => 'button_type', 'value' => array('solid')),
                    'group'       => 'Design Options'
                ),
                array(
                    'type'        => 'colorpicker',
                    'heading'     => 'Button Hover Background Color',
                    'param_name'  => 'button_hover_background_color',
                    'dependency'  => array('element' => 'button_type', 'value' => array('solid', 'outline')),
                    'group'       => 'Design Options'
                ),
                array(
                    'type'        => 'colorpicker',
                    'heading'     => 'Button Border Color',
                    'param_name'  => 'button_border_color',
                    'dependency'  => array('element' => 'button_type', 'value' => array('solid', 'outline')),
                    'group'       => 'Design Options'
                ),
                array(
                    'type'        => 'colorpicker',
                    'heading'     => 'Button Hover Border Color',
                    'param_name'  => 'button_hover_border_color',
                    'dependency'  => array('element' => 'button_type', 'value' => array('solid', 'outline')),
                    'group'       => 'Design Options'
                ),
                array(
                    'type'        => 'textfield',
                    'heading'     => 'Button Margin',
                    'param_name'  => 'button_margin',
                    'description' => 'Insert margin in format: 0px 0px 1px 0px',
                    'group'       => 'Design Options'
                ),

                // Padding & Responsiveness
                array(
                    'type'        => 'dropdown',
                    'heading'     => 'Place Text Below Image',
                    'param_name'  => 'place_text_below_image',
                    'value'       => array(
                        'On Tablet Landscape' => 'tablet-landscape',
                        'On Tablet Portrait'  => 'tablet-portrait',
                        'On Mobile Landscape' => 'mobile-landscape',
                        'On Mobile Portrait'  => 'mobile-portrait'
                    ),
                    'save_always' => true,
                    'group' => 'Padding & Responsiveness'
                ),
                array(
                    'type' => 'textfield',
                    'class' => '',
                    'heading' => 'Content Padding (%) on screen size between 1280px-1440px',
                    'param_name' => 'content_padding_1280_1440',
                    'value' => '',
                    'description' => 'Please insert padding in format (top right bottom left). Default value is 5% 5% 5% 5%',
                    'group' => 'Padding & Responsiveness'
                ),
                array(
                    'type' => 'textfield',
                    'class' => '',
                    'heading' => 'Content Padding (%) on screen size between 1024px-1280px',
                    'param_name' => 'content_padding_1024_1280',
                    'value' => '',
                    'description' => 'Please insert padding in format (top right bottom left). Default value is 5% 5% 5% 5%',
                    'group' => 'Padding & Responsiveness'
                ),
                array(
                    'type' => 'textfield',
                    'class' => '',
                    'heading' => 'Content Padding (%) on screen size between 768px-1024px',
                    'param_name' => 'content_padding_768_1024',
                    'value' => '',
                    'description' => 'Please insert padding in format (top right bottom left). Default value is 5% 5% 5% 5%',
                    'group' => 'Padding & Responsiveness'
                ),
                array(
                    'type' => 'textfield',
                    'class' => '',
                    'heading' => 'Content Padding (%) on screen size between 600px-768px',
                    'param_name' => 'content_padding_600_768',
                    'value' => '',
                    'description' => 'Please insert padding in format (top right bottom left). Default value is 5% 5% 5% 5%',
                    'group' => 'Padding & Responsiveness'
                )
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

            // General
            'image'			                => '',
            'title'			                => '',
            'description'                   => '',
            'link'			                => '',
            'link_text'		                => '',
            'target'		                => '_self',

            // Design Options
            'text_align'                    => 'left',
            'content_padding'               => '',
            'title_tag'	 	                => 'h2',
            'title_color'                   =>  '',
            'description_color'             => '',
            'button_type'                   => 'simple',
            'button_size'                   => '',
            'enable_button_animate_text'    => 'no',
            'button_color'                  => '',
            'button_hover_color'            => '',
            'button_background_color'       => '',
            'button_hover_background_color' => '',
            'button_border_color'           => '',
            'button_hover_border_color'     => '',
            'button_margin'                 => '',

            // Padding & Responsiveness
            'place_text_below_image'        => 'tablet-landscape',
            'content_padding_1280_1440'     => '',
            'content_padding_1024_1280'     => '',
            'content_padding_768_1024'      => '',
            'content_padding_600_768'       => ''
        );

        $params = shortcode_atts($args, $atts);

        $rand_class = 'edgtf-interactive-banner-custom-' . mt_rand(100000,1000000);

        $params['main_content_responsive_class'] = $rand_class;
        $params['main_content_responsive']       = $this->getMainContentResponsiveStyles($params);
        $params['main_content_classes']          = $this->getMainContentClasses($params);
        $params['main_content_styles']           = $this->getMainContentStyles($params);
        $params['title_styles']                  = $this->getTitleStyles($params);
        $params['description_styles']            = $this->getDescriptionStyles($params);
        $params['button_parameters']             = $this->getButtonParameters($params);

        $html = walker_edge_get_shortcode_module_template_part('templates/interactive-banner-template', 'interactive-banner', '', $params);

        return $html;

    }

    /**
     * Returns array of HTML classes for main content
     *
     * @param $params
     *
     * @return array
     */
    private function getMainContentClasses($params) {
        $classes = array(
            'edgtf-interactive-banner-info'
        );

        if(!empty($params['main_content_responsive_class'])) {
            $classes[] = $params['main_content_responsive_class'];
        }

        if(!empty($params['place_text_below_image'])) {
            $classes[] = 'edgtf-responsive-on-'.$params['place_text_below_image'];
        }

        return $classes;
    }

    /**
     * Returns array of main content styles
     *
     * @param $params
     *
     * @return array
     */
    private function getMainContentStyles($params) {
        $styles = array();

        if(!empty($params['text_align'])) {
            $styles[] = 'text-align: '.$params['text_align'];
        }

        if(!empty($params['content_padding'])) {
            $styles[] = 'padding: '.$params['content_padding'];
        }

        return $styles;
    }

    /**
     * Return Main Content Responsive styles
     *
     * @param $params
     * @return array
     */
    private function getMainContentResponsiveStyles($params) {

        $responsive_styles = array();

        if ($params['content_padding_1280_1440'] !== '') {
            $responsive_styles['content_padding_1280_1440'] = $params['content_padding_1280_1440'];
        }

        if ($params['content_padding_1024_1280'] !== '') {
            $responsive_styles['content_padding_1024_1280'] = $params['content_padding_1024_1280'];
        }

        if ($params['content_padding_768_1024'] !== '') {
            $responsive_styles['content_padding_768_1024'] = $params['content_padding_768_1024'];
        }

        if ($params['content_padding_600_768'] !== '') {
            $responsive_styles['content_padding_600_768'] = $params['content_padding_600_768'];
        }

        return $responsive_styles;
    }

    /**
     * Returns array of title styles
     *
     * @param $params
     *
     * @return array
     */
    private function getTitleStyles($params) {
        $styles = array();

        if(!empty($params['title_color'])) {
            $styles[] = 'color: '.$params['title_color'];
        }

        return $styles;
    }

    /**
     * Returns array of description styles
     *
     * @param $params
     *
     * @return array
     */
    private function getDescriptionStyles($params) {
        $styles = array();

        if(!empty($params['description_color'])) {
            $styles[] = 'color: '.$params['description_color'];
        }

        return $styles;
    }

    /**
     * Returns button params array
     *
     * @param $params
     * @return array
     */
    private function getButtonParameters($params) {
        $button_params_array = array();

        if(!empty($params['button_type'])) {
            $button_params_array['type'] = $params['button_type'];
        }

        if(!empty($params['button_size'])) {
            $button_params_array['size'] = $params['button_size'];
        }

        if(!empty($params['enable_button_animate_text'])) {
            $button_params_array['enable_animate_text'] = $params['enable_button_animate_text'];
        }

        if(!empty($params['link'])) {
            $button_params_array['link'] = $params['link'];
        }

        if(!empty($params['link_text'])) {
            $button_params_array['text'] = $params['link_text'];
        }

        if(!empty($params['target'])) {
            $button_params_array['target'] = $params['target'];
        }

        if(!empty($params['button_color'])) {
            $button_params_array['color'] = $params['button_color'];
        }

        if(!empty($params['button_background_color'])) {
            $button_params_array['background_color'] = $params['button_background_color'];
        }

        if(!empty($params['button_border_color'])) {
            $button_params_array['border_color'] = $params['button_border_color'];
        }

        if(!empty($params['button_hover_color'])) {
            $button_params_array['hover_color'] = $params['button_hover_color'];
        }

        if(!empty($params['button_hover_background_color'])) {
            $button_params_array['hover_background_color'] = $params['button_hover_background_color'];
        }

        if(!empty($params['button_hover_border_color'])) {
            $button_params_array['hover_border_color'] = $params['button_hover_border_color'];
        }

        if(!empty($params['button_margin'])) {
            $button_params_array['margin'] = $params['button_margin'];
        }

        return $button_params_array;
    }
}
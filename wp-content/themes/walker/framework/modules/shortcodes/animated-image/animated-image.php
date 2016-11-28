<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\AnimatedImage;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;

class AnimatedImage implements ShortcodeInterface{

	private $base;

	/**
	 * Animated Image constructor.
	 */
	public function __construct() {
		$this->base = 'edgtf_animated_image';

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
	 * @see edgt_core_get_animated_image_array_vc()
	 */
	public function vcMap() {

		vc_map(array(
			'name'                      => esc_html__('Edge Animated Image', 'walker'),
			'base'                      => $this->getBase(),
			'category'                  => 'by EDGE',
			'icon' 						=> 'icon-wpb-animated-image extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params'                    => array(
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
                    'dependency' => array('element' => 'title', 'not_empty' => true)
                ),
                array(
                    'type'        => 'textfield',
                    'heading'     => 'Link',
                    'param_name'  => 'link',
                    'value'       => '',
                    'admin_label' => true
                ),
                array(
                    'type'       => 'dropdown',
                    'heading'    => 'Target',
                    'param_name' => 'target',
                    'value'      => array(
                        ''      => '',
                        'Same Window'  => '_self',
                        'New Window' => '_blank'
                    ),
                    'dependency' => array('element' => 'link', 'not_empty' => true),
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
			'image'			    => '',
			'title'			    => '',
			'title_tag'	 	    => 'h3',
			'link'			    => '',
			'target'		    => '_self'
		);

		$params = shortcode_atts($args, $atts);

		$tag_array = array('h2', 'h3', 'h4', 'h5', 'h6');
		$params['title_tag'] = (in_array($params['title_tag'], $tag_array)) ? $params['title_tag'] : $args['title_tag'];

        $params['target'] = !empty($params['target']) ? $params['target'] : '_self';

		$html = walker_edge_get_shortcode_module_template_part('templates/animated-image-template', 'animated-image', '', $params);

		return $html;
	}

}
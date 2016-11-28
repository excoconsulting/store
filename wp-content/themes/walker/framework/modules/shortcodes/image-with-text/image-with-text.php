<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\ImageWithText;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;

class ImageWithText implements ShortcodeInterface{

	private $base;

	/**
	 * Image With Text constructor.
	 */
	public function __construct() {
		$this->base = 'edgtf_image_with_text';

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
			'name'                      => esc_html__('Edge Image With Text', 'walker'),
			'base'                      => $this->getBase(),
			'category'                  => 'by EDGE',
			'icon' 						=> 'icon-wpb-image-with-text extended-custom-icon',
			'allowed_container_element' => 'vc_row',
			'params'                    => array(
				array(
					'type'			=> 'attach_image',
					'heading'		=> 'Image',
					'param_name'	=> 'image',
					'description'	=> 'Select image from media library'
				),
				array(
					'type'			=> 'textfield',
					'heading'		=> 'Image Size',
					'param_name'	=> 'image_size',
					'description'	=> 'Enter image size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size'
				),
				array(
					'type'			=> 'dropdown',
					'heading'		=> 'Enable Lightbox Functionality',
					'param_name'	=> 'enable_lightbox',
					'value'			=> array(
						'No'		=> 'no',
						'Yes'		=> 'yes'
					),
					'save_always'	=> true,
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
                    'type'       => 'textarea',
                    'heading'    => 'Text',
                    'param_name' => 'text'
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
                    'description' => 'Default label is READ MORE',
                    'dependency'  => array('element' => 'link', 'not_empty' => true)
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
                    'dependency' => array('element' => 'link', 'not_empty' => true),
                ),
                array(
                	'type'	=> 'colorpicker',
                	'heading' => 'Background Color',
                	'param_name' => 'background_color',
                	'group' => 'Design Options',
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
			'image_size'	    => 'full',
			'enable_lightbox'   => '',
			'title'			    => '',
			'title_tag'	 	    => 'h4',
			'text'			    => '',
			'link'			    => '',
			'link_text'		    => 'READ MORE',
			'target'		    => '_self',
            'background_color' =>  ''
		);

		$params = shortcode_atts($args, $atts);
		$params['image'] = $this->getImage($params);
		$params['image_size'] = $this->getImageSize($params['image_size']);
		$params['enable_lightbox'] = ($params['enable_lightbox'] == 'yes') ? true : false;
		$params['button_parameters'] = $this->getButtonParameters($params);
        $params['holder_classes']  = $this->getHolderClasses($params);

		$html = walker_edge_get_shortcode_module_template_part('templates/image-with-text', 'image-with-text', '', $params);

		return $html;
	}

	/**
	 * Return image for shortcode
	 *
	 * @param $params
	 * @return array
	 */
	private function getImage($params) {
        $image = array();

        if ($params['image'] !== '') {
            $id = $params['image'];

            $image['image_id'] = $id;
            $image_original = wp_get_attachment_image_src($id, 'full');
            $image['url'] = $image_original[0];
            $image['title'] = get_the_title($id);
        }

		return $image;
	}

	/**
	 * Return image size or custom image size array
	 *
	 * @param $image_size
	 * @return array
	 */
	private function getImageSize($image_size) {

		$image_size = trim($image_size);
		//Find digits
		preg_match_all( '/\d+/', $image_size, $matches );
		if(in_array( $image_size, array('thumbnail', 'thumb', 'medium', 'large', 'full'))) {
			return $image_size;
		} elseif(!empty($matches[0])) {
			return array(
					$matches[0][0],
					$matches[0][1]
			);
		} else {
			return 'thumbnail';
		}
	}

	private function getButtonParameters($params) {
		$button_params_array = array();

		$button_params_array['type'] = 'simple';

		if(!empty($params['link'])) {
			$button_params_array['link'] = $params['link'];
		}

		if(!empty($params['target'])) {
			$button_params_array['target'] = $params['target'];
		} else {
			$button_params_array['target'] = '_self';
		}

		if(!empty($params['link_text'])) {
			$button_params_array['text'] = $params['link_text'];
		}

		return $button_params_array;
	}


	/**
	 * Returns array of holder classes
	 *
	 * @param $params
	 *
	 * @return array
	 */
	private function getHolderClasses($params) {
	    $classes = array('edgtf-imwt-holder');

	    if(($params['enable_lightbox'] == 'yes')) {
	        $classes[] = 'edgtf-imwt-lightbox';
	    }

	    return $classes;
	}

}
<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\ShowcaseCarousel;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class Showcase Carousel
 */
class ShowcaseCarousel implements ShortcodeInterface{

	private $base;

	function __construct() {
		$this->base='edgtf_showcase_carousel';
		
		add_action('vc_before_init', array($this, 'vcMap'));
	}

	/**\
	 * Returns base for shortcode
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}

	public function vcMap() {

		vc_map( array(
			'name' => esc_html__('Edge Showcase Carousel', 'walker'),
			'base' => $this->base,
			'icon' => 'icon-wpb-showcase-carousel extended-custom-icon',
			'category' => 'by EDGE',
			'allowed_container_element' => 'vc_row',
			'params' => array(
				array(
					'type'			=> 'attach_images',
					'heading'		=> 'Images',
					'param_name'	=> 'images',
					'description'	=> 'Select images from media library'
				),
				array(
					'type'			=> 'textfield',
					'heading'		=> 'Image Size',
					'param_name'	=> 'image_size',
					'description'	=> 'Enter image size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size'
				),
				array(
                    "type" => "textarea",
                    "heading" => "Custom Links",
                    "param_name" => "custom_links",
                    "value" => "",
                    "description" => "Delimit Links by comma"
                ),
                array(
                    'type'       => 'dropdown',
                    'heading'    => 'Custom Link Target',
                    'param_name' => 'custom_link_target',
                    'value'      => array(
                        'Same Window'  => '_self',
                        'New Window' => '_blank'
                    ),
                    'save_always' => true
                ),
				array(
					'type' => 'textfield',
					'admin_label' => true,
					'heading' => 'Carousel Auto Play Interval (ms)',
					'param_name' => 'autoplay',
					'description' => 'The speed in milliseconds to wait before auto-rotating. Default value is 3000.',
                    'admin_label' => true
				),
				array(
					'type' => 'textfield',
					'admin_label' => true,
					'heading' => 'Carousel Speed (ms)',
					'param_name' => 'speed',
					'description' => 'Time in milliseconds it takes to rotate the slides. Default value is 650.',
                    'admin_label' => true
				),
				array(
                    'type'        => 'dropdown',
                    'heading'     => 'Enable Navigation',
                    'param_name'  => 'navigation',
                    'value'       => array(
                        'Yes' => 'yes',
                        'No' => 'no'
                    ),
                    'save_always' => true,
                    'admin_label' => true
                )
			)
		) );
	}

	public function render($atts, $content = null) {
		$args = array(
			'images' 			 => '',
			'image_size' 		 => 'full',
			'custom_links'		 => '',
            'custom_link_target' => '_blank',
			'autoplay' 			 => '3000',
			'speed' 			 => '650',
			'navigation' 		 => 'yes'
        );
		$params = shortcode_atts($args, $atts);

		$params['carousel_data'] = $this->getCarouselData($params);
		$params['images'] = $this->getCarouselImages($params);
		$params['image_size'] = $this->getImageSizes($params['image_size']);
		$params['links'] = $this->getImageLinks($params);

		$html = walker_edge_get_shortcode_module_template_part('templates/showcase-carousel', 'showcase-carousel', '', $params);

		return $html;
	}

	/**
	 * Return all configuration data for carousel
	 *
	 * @param $params
	 * @return array
	 */
	private function getCarouselData($params) {

		$carousel_data = array();

		$carousel_data['data-autoplay'] = ($params['autoplay'] !== '') ? $params['autoplay'] : '';
		$carousel_data['data-speed'] = ($params['speed'] !== '') ? $params['speed'] : '';
		$carousel_data['data-navigation'] = ($params['navigation'] !== '') ? $params['navigation'] : '';

		return $carousel_data;
	}

	/**
	 * Return images
	 *
	 * @param $params
	 * @return array
	 */
	private function getCarouselImages($params) {

		$images = array();

		if ($params['images'] !== '') {

			$size = $params['image_size'];
			$image_ids = explode(',', $params['images']);

			foreach ($image_ids as $id) {

				$img = wp_get_attachment_image_src($id, $size);

				$image['url'] = $img[0];
				$image['width'] = $img[1];
				$image['height'] = $img[2];
				$image['title'] = get_the_title($id);

				$images[] = $image;
			}
		}

		return $images;
	}

	/**
	 * Return image sizes
	 *
	 * @param $image_size
	 * @return array
	 */
	private function getImageSizes($image_size) {

		//Remove whitespaces
		$image_size = trim($image_size);
		//Find digits
		preg_match_all( '/\d+/', $image_size, $matches );
		if ( !empty($matches[0]) ) {
			return array(
				$matches[0][0],
				$matches[0][1]
			);
		} elseif ( in_array( $image_size, array('thumbnail', 'thumb', 'medium', 'large', 'full') )) {
			return $image_size;
		} else {
			return 'thumbnail';
		}
	}

	/**
     * Return links for images
     *
     * @param $params
     * @return array
     */
    private function getImageLinks($params) {

        $custom_links = array();

        if (!empty($params['custom_links'])) {
            $custom_links = array_map('trim', explode(',', $params['custom_links']));
	    }

        return $custom_links;
    }
}
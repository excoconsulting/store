<?php

namespace WalkerEdgeNamespace\Modules\Shortcodes\ProductListCarousel;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;
/**
 * Class ProductListCarousel
 */
class ProductListCarousel implements ShortcodeInterface {
	/**
	* @var string
	*/
	private $base;
	
	function __construct() {
		$this->base = 'edgtf_product_list_carousel';
		
		add_action('vc_before_init', array($this,'vcMap'));
	}
	
	public function getBase() {
		return $this->base;
	}
	public function vcMap() {

		vc_map( array(
			'name' => esc_html__('Edge Product List - Carousel', 'walker'),
			'base' => $this->base,
			'icon' => 'icon-wpb-product-list-carousel extended-custom-icon',
			'category' => 'by EDGE',
			'allowed_container_element' => 'vc_row',
			'params' => array(
					array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Type',
						'param_name' => 'type',
						'value' => array(
							'Standard' => 'standard',
							'Simple' => 'simple'
						),
						'save_always' => true,
						'description' => ''
					),
					array(
						'type' => 'textfield',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Number of Products',
						'param_name' => 'number_of_posts',
						'description' => ''
					),
                    array(
                        'type' => 'dropdown',
                        'holder' => 'div',
                        'class' => '',
                        'heading' => 'Number of Visible Items',
                        'param_name' => 'number_of_visible_items',
                        'value' => array(
                            'One' => '1',
                            'Two' => '2',
                            'Three' => '3',
                            'Four' => '4',
                            'Five' => '5'
                        ),
                        'description' => '',
                        'save_always' => true,
						'dependency' => array('element' => 'type', 'value' => array('standard'))
                    ),
                    array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Enable Carousel Autoplay',
						'param_name' => 'carousel_autoplay',
						'value' => array(
							'Yes' => 'yes',
							'No' => 'no'
						),
						'save_always' => true,
						'description' => ''
					),
                    array(
						'type' => 'textfield',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Slide Duration (ms)',
						'param_name' => 'carousel_autoplay_timeout',
						'description' => 'Autoplay interval timeout. Default value is 5000',
						'dependency' => array('element' => 'carousel_autoplay', 'value' => array('yes'))
					),
                    array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Enable Carousel Loop',
						'param_name' => 'carousel_loop',
						'value' => array(
							'Yes' => 'yes',
							'No' => 'no'
						),
						'save_always' => true,
						'description' => ''
					),
					array(
						'type' => 'textfield',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Animation Speed (ms)',
						'param_name' => 'carousel_speed',
						'description' => 'Carousel Speed interval. Default value is 650',
					),
                    array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Space Between Items',
						'param_name' => 'space_between_items',
						'value' => array(
							'Small' => 'small',
							'Normal' => 'normal'
						),
						'save_always' => true,
						'description' => '',
						'dependency' => array('element' => 'type', 'value' => array('standard'))
					),
					array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Order By',
						'param_name' => 'order_by',
						'value' => array(
							'Title' => 'title',
							'Date' => 'date',
							'Random' => 'rand',
							'Post Name' => 'name',
							'ID' => 'id',
                            'Menu Order' => 'menu_order'
						),
						'save_always' => true,
						'description' => ''
					),
					array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Order',
						'param_name' => 'order',
						'value' => array(
							'ASC' => 'ASC',
							'DESC' => 'DESC'
						),
						'save_always' => true,
						'description' => ''
					),
					array(
	                    'type' => 'dropdown',
	                    'heading' => 'Choose Sorting Taxonomy',
	                    'param_name' => 'taxonomy_to_display',
	                    'value' => array(
	                        'Category' => 'category',
	                        'Tag' => 'tag',
	                        'Id' => 'id'
	                    ),
	                    'save_always' => true,
	                    'admin_label' => true,
	                    'description' => 'If you would like to display only certain products, this is where you can select the criteria by which you would like to choose which products to display.'
	                ),
	                array(
	                    'type' => 'textfield',
	                    'heading' => 'Enter Taxonomy Values',
	                    'param_name' => 'taxonomy_values',
	                    'value' => '',
	                    'admin_label' => true,
	                    'description' => 'Separate values (category slugs, tags, or post IDs) with a comma'
	                ),
	                array(
						'type' => 'dropdown',
						'heading' => 'Image Proportions',
						'param_name' => 'image_size',
						'value' => array(
							'Default' => '',
							'Original' => 'original',
							'Square' => 'square'
						),
						'save_always' => true
					),
	                array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Enable Carousel Navigation',
						'param_name' => 'carousel_navigation',
						'value' => array(
							'Yes' => 'yes',
							'No' => 'no'
						),
						'save_always' => true,
						'description' => ''
					),
					array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Choose Navigation Type',
						'param_name' => 'carousel_navigation_type',
						'value' => array(
							'Image' => 'image',
							'Icon Font' => 'icon-font'
						),
						'save_always' => true,
						'description' => '',
						'dependency' => array('element' => 'carousel_navigation', 'value' => array('yes'))
					),
					array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Enable Carousel Pagination',
						'param_name' => 'carousel_pagination',
						'value' => array(
							'No' => 'no',
							'Yes' => 'yes'
						),
						'save_always' => true,
						'description' => ''
					),
					array(
						'type' => 'colorpicker',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Shader Background Color',
						'param_name' => 'shader_background_color',
						'description' => '',
						'group'	=> 'Product Info'
					),
					array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Display Title',
						'param_name' => 'display_title',
						'value' => array(
							'Yes' => 'yes',
							'No' => 'no'
						),
						'save_always' => true,
						'description' => '',
						'group'	=> 'Product Info'
					),
					array(
						'type' => 'dropdown',
						'admin_label' => true,
						'heading' => 'Title Tag',
						'param_name' => 'title_tag',
						'value' => array(
							''   => '',
							'h2' => 'h2',
							'h3' => 'h3',
							'h4' => 'h4',	
							'h5' => 'h5',	
							'h6' => 'h6',	
						),
						'save_always' => true,
						'description' => '',
						'dependency' => array('element' => 'display_title', 'value' => array('yes')),
						'group'	=> 'Product Info'
					),
					array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Title Text Transform',
						'param_name' => 'title_transform',
						'value' => array(
							'Default' 	 => '',
							'None' 		 => 'none',
							'Capitalize' => 'capitalize',
							'Lowercase'  => 'lowercase',
							'Uppercase'  => 'uppercase'
						),
						'save_always' => true,
						'description' => '',
						'dependency' => array('element' => 'display_title', 'value' => array('yes')),
						'group'	=> 'Product Info'
					),
					array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Display Price',
						'param_name' => 'display_price',
						'value' => array(
							'Yes' => 'yes',
							'No' => 'no'
						),
						'save_always' => true,
						'description' => '',
						'group'	=> 'Product Info'
					),
					array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Display Rating',
						'param_name' => 'display_rating',
						'value' => array(
							'Yes' => 'yes',
							'No' => 'no'
						),
						'save_always' => true,
						'description' => '',
						'group'	=> 'Product Info'
					),
					array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Display Excerpt',
						'param_name' => 'display_excerpt',
						'value' => array(
							'Yes' => 'yes',
							'No' => 'no'
						),
						'save_always' => true,
						'description' => '',
						'group'	=> 'Product Info',
						'dependency' => array('element' => 'type', 'value' => array('simple'))
					),
					array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Display Button',
						'param_name' => 'display_button',
						'value' => array(
							'Yes' => 'yes',
							'No' => 'no'
						),
						'save_always' => true,
						'description' => '',
						'group'	=> 'Product Info'
					),
					array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Display Quick View',
						'param_name' => 'display_quick_view',
						'value' => array(
							'No' => 'no',
							'Yes' => 'yes'
						),
						'save_always' => true,
						'description' => 'This option works only if YITH WooCommerce Quick View plugin is installed',
						'group'	=> 'YITH Product Info'
					),
					array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Display Wishlist',
						'param_name' => 'display_wishlist',
						'value' => array(
							'No' => 'no',
							'Yes' => 'yes'
						),
						'save_always' => true,
						'description' => 'This option works only if YITH WooCommerce Wishlist plugin is installed',
						'group'	=> 'YITH Product Info'
					)
				)
		) );

	}
	public function render($atts, $content = null) {
		
		$default_atts = array(
			'type'						=> 'standard',
            'number_of_posts' 		 	=> '8',
            'number_of_visible_items' 	=> '4',
            'carousel_autoplay'	 	  	=> 'yes',
            'carousel_autoplay_timeout' => '5000',
            'carousel_loop'	 		 	=> 'yes',
            'carousel_speed' 		 	=> '650',
            'space_between_items'	  	=> 'normal',
            'carousel_navigation'	 	=> 'yes',
            'carousel_navigation_type'	=> 'image',
            'carousel_pagination'	 	=> 'no',
            'order_by' 				  	=> '',
            'order' 				  	=> '',
            'taxonomy_to_display' 	  	=> 'category',
            'taxonomy_values' 		 	=> '',
            'image_size'			  	=> '',
            'shader_background_color' 	=> '',
            'display_title' 		  	=> 'yes',
            'title_tag'				  	=> 'h4',
            'title_transform'		  	=> 'uppercase',
            'display_price' 		  	=> 'yes',
            'display_rating' 		  	=> 'yes',
            'display_excerpt'			=> 'yes',
            'display_button' 		  	=> 'yes',
            'display_quick_view'	  	=> 'no',
            'display_wishlist'		  	=> 'no'
        );
		
		$params = shortcode_atts($default_atts, $atts);
		extract($params);
		$params['holder_classes'] = $this->getHolderClasses($params);
		$params['holder_data'] = $this->getProductListCarouselDataAttributes($params);

		$params['yith_holder_classes'] = $this->getYITHHolderClasses($params);

		$tag_array = array('h2', 'h3', 'h4', 'h5', 'h6');
        $params['title_tag'] = (in_array($params['title_tag'], $tag_array)) ? $params['title_tag'] : $default_atts['title_tag'];
		$params['title_styles'] = $this->getTitleStyles($params);

		$params['shader_styles'] = $this->getShaderStyles($params);

		$queryArray = $this->generateProductQueryArray($params);
		$query_result = new \WP_Query($queryArray);
		$params['query_result'] = $query_result;	

		$html ='';
        $html .= walker_edge_get_shortcode_module_template_part('templates/product-list-template', 'product-list-carousel', '', $params);
		return $html;	
	}

	/**
	   * Generates holder classes
	   *
	   * @param $params
	   *
	   * @return string
	*/
	private function getHolderClasses($params){
		$holderClasses = '';

		$carouselType = $this->getCarouselTypeClass($params);

        $columnsSpace = $this->getColumnsSpaceClass($params);

        if($params['carousel_navigation'] === 'yes') {
        	$holderClasses .= 'edgtf-navigation-'.$params['carousel_navigation_type'];
        }

        if($params['carousel_pagination'] === 'yes') {
        	$holderClasses .= ' edgtf-plc-pag-enabled';
        }

        $holderClasses .= ' '. $carouselType . ' '. $columnsSpace;
		
		return $holderClasses;
	}

	/**
     * Generates carousel type classes for product list holder
     *
     * @param $params
     *
     * @return string
     */
    private function getCarouselTypeClass($params){

        $carouselType = '';
        $type = $params['type'];

        switch ($type) {
            case 'standard':
                $carouselType = 'edgtf-standard-type';
                break;
            case 'simple':
                $carouselType = 'edgtf-simple-type';
                break;
            default:
                $carouselType = 'edgtf-standard-type';
                break;
        }

        return $carouselType;
    }

	/**
     * Generates space between columns classes for product list holder
     *
     * @param $params
     *
     * @return string
     */
    private function getColumnsSpaceClass($params){

        $columnsSpace = '';
        $spaceBetweenItems = $params['space_between_items'];

        switch ($spaceBetweenItems) {
            case 'normal':
                $columnsSpace = 'edgtf-normal-space';
                break;
            case 'small':
                $columnsSpace = 'edgtf-small-space';
                break;
            default:
                $columnsSpace = 'edgtf-normal-space';
                break;
        }

        return $columnsSpace;
    }

    /**
	   * Generates holder classes for YITH plugins
	   *
	   * @param $params
	   *
	   * @return string
	*/
	private function getYITHHolderClasses($params){
		$holderClasses = '';

		if($params['display_quick_view'] === 'yes' && $params['display_wishlist'] === 'yes' && walker_edge_is_yith_wishlist_install() && walker_edge_is_yith_wcqv_install()) {
			$holderClasses = 'edgtf-pl-yith-items';
		}
		
		return $holderClasses;
	}

    /**
     * Return all data that product list carousel needs
     *
     * @param $params
     * @return array
     */
    private function getProductListCarouselDataAttributes($params) {

        $data = array();

        if(!empty($params['number_of_visible_items']) && $params['type'] !== 'simple'){
            $data['data-number-of-visible-items'] = $params['number_of_visible_items'];
        } else if($params['type'] === 'simple'){
            $data['data-number-of-visible-items'] = 1;
        }
        if(!empty($params['carousel_autoplay'])) {
            $data['data-autoplay'] = $params['carousel_autoplay'];
        }
        if(!empty($params['carousel_autoplay_timeout'])) {
            $data['data-autoplay-timeout'] = $params['carousel_autoplay_timeout'];
        }
        if(!empty($params['carousel_loop'])) {
            $data['data-loop'] = $params['carousel_loop'];
        }
        if(!empty($params['carousel_speed'])) {
            $data['data-speed'] = $params['carousel_speed'];
        }
        if(!empty($params['carousel_navigation'])) {
            $data['data-navigation'] = $params['carousel_navigation'];
        }
        if(!empty($params['carousel_pagination'])) {
            $data['data-pagination'] = $params['carousel_pagination'];
        }

        return $data;
    }

	/**
	   * Generates query array
	   *
	   * @param $params
	   *
	   * @return array
	*/
	public function generateProductQueryArray($params){
		
		$queryArray = array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page' => $params['number_of_posts'],
			'orderby' => $params['order_by'],
			'order' => $params['order'],
			'meta_query' => WC()->query->get_meta_query()
		);

        if ($params['taxonomy_to_display'] !== '' && $params['taxonomy_to_display'] === 'category') {
            $queryArray['product_cat'] = $params['taxonomy_values'];
        }

        if ($params['taxonomy_to_display'] !== '' && $params['taxonomy_to_display'] === 'tag') {
            $queryArray['product_tag'] = $params['taxonomy_values'];
        }

        if ($params['taxonomy_to_display'] !== '' && $params['taxonomy_to_display'] === 'id') {
            $idArray = $params['taxonomy_values'];
            $ids = explode(',', $idArray);
            $queryArray['post__in'] = $ids;
        }

        return $queryArray;
	}

	/**
     * Return Style for Title
     *
     * @param $params
     * @return string
     */
    private function getTitleStyles($params) {
        $item_styles = array();
		
        if ($params['title_transform'] !== '') {
            $item_styles[] = 'text-transform: '.$params['title_transform'];
        }

		return implode(';', $item_styles);
    }

    /**
     * Return Style for Shader
     *
     * @param $params
     * @return string
     */
    private function getShaderStyles($params) {
        $item_styles = array();
		
        if ($params['shader_background_color'] !== '') {
            $item_styles[] = 'background-color: '.$params['shader_background_color'];
        }

		return implode(';', $item_styles);
    }
}
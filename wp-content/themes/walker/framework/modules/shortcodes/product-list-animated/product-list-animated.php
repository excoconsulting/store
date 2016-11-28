<?php

namespace WalkerEdgeNamespace\Modules\Shortcodes\ProductListAnimated;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;
/**
 * Class ProductListAnimated
 */
class ProductListAnimated implements ShortcodeInterface {
	/**
	* @var string
	*/
	private $base;
	
	function __construct() {
		$this->base = 'edgtf_product_list_animated';
		
		add_action('vc_before_init', array($this,'vcMap'));
	}
	
	public function getBase() {
		return $this->base;
	}

	public function vcMap() {

		vc_map( array(
			'name' => esc_html__('Edge Product List - Animated', 'walker'),
			'base' => $this->base,
			'icon' => 'icon-wpb-product-list-animated extended-custom-icon',
			'category' => 'by EDGE',
			'allowed_container_element' => 'vc_row',
			'params' => array(
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
                        'heading' => 'Number of Columns',
                        'param_name' => 'number_of_columns',
                        'value' => array(
                            'One' => '1',
                            'Two' => '2',
                            'Three' => '3',
                            'Four' => '4',
                            'Five' => '5',
                            'Six' => '6'
                        ),
                        'description' => '',
                        'save_always' => true
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
						'heading' => 'Display Button',
						'param_name' => 'display_button',
						'value' => array(
							'Yes' => 'yes',
							'No' => 'no'
						),
						'save_always' => true,
						'description' => '',
						'group'	=> 'Product Info'
					)
				)
		) );
	}

	public function render($atts, $content = null) {
		
		$default_atts = array(
            'number_of_posts' 		  => '8',
            'number_of_columns' 	  => '4',
            'order_by' 				  => '',
            'order' 				  => '',
            'taxonomy_to_display' 	  => 'category',
            'taxonomy_values' 		  => '',
            'image_size'			  => '',
            'shader_background_color' => '',
            'display_title' 		  => 'yes',
            'title_tag'				  => 'h4',
            'title_transform'		  => 'uppercase',
            'display_price' 		  => 'yes',
            'display_rating' 		  => 'no',
            'display_button' 		  => 'yes'
        );
		
		$params = shortcode_atts($default_atts, $atts);
		extract($params);
		$params['holder_classes'] = $this->getHolderClasses($params);

		$tag_array = array('h2', 'h3', 'h4', 'h5', 'h6');
        $params['title_tag'] = (in_array($params['title_tag'], $tag_array)) ? $params['title_tag'] : $default_atts['title_tag'];
		$params['title_styles'] = $this->getTitleStyles($params);

		$params['shader_styles'] = $this->getShaderStyles($params);

		$queryArray = $this->generateProductQueryArray($params);
		$query_result = new \WP_Query($queryArray);
		$params['query_result'] = $query_result;	

		$html = walker_edge_get_shortcode_module_template_part('templates/product-list-template', 'product-list-animated', '', $params);
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

        $columnNumber = $this->getColumnNumberClass($params);

        $holderClasses .= $columnNumber;
		
		return $holderClasses;
	}

    /**
     * Generates columns number classes for product list holder
     *
     * @param $params
     *
     * @return string
     */
    private function getColumnNumberClass($params){

        $columnsNumber = '';
        $columns = $params['number_of_columns'];

        switch ($columns) {
            case 1:
                $columnsNumber = 'edgtf-one-column';
                break;
            case 2:
                $columnsNumber = 'edgtf-two-columns';
                break;
            case 3:
                $columnsNumber = 'edgtf-three-columns';
                break;
            case 4:
                $columnsNumber = 'edgtf-four-columns';
                break;
            case 5:
                $columnsNumber = 'edgtf-five-columns';
                break;
            case 6:
                $columnsNumber = 'edgtf-six-columns';
                break;        
            default:
                $columnsNumber = 'edgtf-four-columns';
                break;
        }

        return $columnsNumber;
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
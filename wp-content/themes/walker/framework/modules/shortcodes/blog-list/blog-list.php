<?php

namespace WalkerEdgeNamespace\Modules\Shortcodes\BlogList;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;
/**
 * Class BlogList
 */
class BlogList implements ShortcodeInterface {
	/**
	* @var string
	*/
	private $base;
	
	function __construct() {
		$this->base = 'edgtf_blog_list';
		
		add_action('vc_before_init', array($this,'vcMap'));
	}
	
	public function getBase() {
		return $this->base;
	}
	public function vcMap() {

		vc_map( array(
			'name' => esc_html__('Edge Blog List', 'walker'),
			'base' => $this->base,
			'icon' => 'icon-wpb-blog-list extended-custom-icon',
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
							'Masonry' => 'masonry',
							'Simple' => 'simple',
						),
						'description' => '',
                        'save_always' => true
					),
					array(
						'type' => 'textfield',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Number of Posts',
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
                            'Four' => '4'
                        ),
                        'description' => '',
                        'save_always' => true,
                        'dependency' => Array('element' => 'type', 'value' => array('standard', 'simple'))
                    ),
                    array(
                        'type' => 'dropdown',
                        'holder' => 'div',
                        'class' => '',
                        'heading' => 'Space Between Columns',
                        'param_name' => 'space_between_columns',
                        'value' => array(
                            'Small' => 'small',
                            'Normal' => 'normal'
                        ),
                        'description' => '',
                        'save_always' => true,
                        'dependency' => Array('element' => 'type', 'value' => array('standard', 'masonry'))
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
							'Post Name' => 'name'
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
						'type' => 'textfield',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Category Slug',
						'param_name' => 'category',
						'description' => 'Leave empty for all or use comma for list'
					),
					array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Image Size',
						'param_name' => 'image_size',
						'value' => array(
							'Original' => 'original',
							'Landscape' => 'landscape',
							'Square' => 'square'
						),
						'description' => '',
						'dependency' => Array('element' => 'type', 'value' => array('masonry', 'standard')),
                        'save_always' => true
					),
					array(
						'type' => 'textfield',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Text Length',
						'param_name' => 'text_length',
						'description' => 'Number of characters',
						'dependency' => Array('element' => 'type', 'value' => array('masonry', 'standard')),
					),
					array(
						'type' => 'dropdown',
						'class' => '',
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
						'description' => ''
					),
                    array(
                        'type' => 'dropdown',
                        'holder' => 'div',
                        'class' => '',
                        'heading' => 'Enable Post Info Section',
                        'param_name' => 'post_info_section',
                        'value' => array(
                            'Yes' => 'yes',
                            'No' => 'no'
                        ),
                        'save_always' => true,
                        'description' => '',
                        'dependency' => Array('element' => 'type', 'value' => array('standard', 'masonry')),
                    ),
                    array(
                        'type' => 'dropdown',
                        'holder' => 'div',
                        'class' => '',
                        'heading' => 'Enable Post Info Author',
                        'param_name' => 'post_info_author',
                        'value' => array(
                            'Yes' => 'yes',
                            'No' => 'no'
                        ),
                        'save_always' => true,
                        'dependency' => Array('element' => 'post_info_section', 'value' => array('yes'))
                    ),
                    array(
                        'type' => 'dropdown',
                        'holder' => 'div',
                        'class' => '',
                        'heading' => 'Enable Post Info Date',
                        'param_name' => 'post_info_date',
                        'value' => array(
                            'Yes' => 'yes',
                            'No' => 'no'
                        ),
                        'save_always' => true,
                        'dependency' => Array('element' => 'post_info_section', 'value' => array('yes'))
                    ),
                    array(
                        'type' => 'dropdown',
                        'holder' => 'div',
                        'class' => '',
                        'heading' => 'Enable Post Info Category',
                        'param_name' => 'post_info_category',
                        'value' => array(
                            'No' => 'no',
                            'Yes' => 'yes'
                        ),
                        'save_always' => true,
                        'dependency' => Array('element' => 'post_info_section', 'value' => array('yes'))
                    ),
                    array(
                        'type' => 'dropdown',
                        'holder' => 'div',
                        'class' => '',
                        'heading' => 'Enable Post Info Comments',
                        'param_name' => 'post_info_comments',
                        'value' => array(
                            'Yes' => 'yes',
                            'No' => 'no'
                        ),
                        'save_always' => true,
                        'dependency' => Array('element' => 'post_info_section', 'value' => array('yes'))
                    ),
                    array(
                        'type' => 'dropdown',
                        'holder' => 'div',
                        'class' => '',
                        'heading' => 'Enable Post Info Like',
                        'param_name' => 'post_info_like',
                        'value' => array(
                            'No' => 'no',
                            'Yes' => 'yes'
                        ),
                        'save_always' => true,
                        'dependency' => Array('element' => 'post_info_section', 'value' => array('yes'))
                    ),
                    array(
                        'type' => 'dropdown',
                        'holder' => 'div',
                        'class' => '',
                        'heading' => 'Enable Post Info Share',
                        'param_name' => 'post_info_share',
                        'value' => array(
                            'No' => 'no',
                            'Yes' => 'yes'
                        ),
                        'save_always' => true,
                        'dependency' => Array('element' => 'post_info_section', 'value' => array('yes'))
                    ),
					array(
						'type' => 'dropdown',
						'holder' => 'div',
						'class' => '',
						'heading' => 'Enable Read More Button',
						'param_name' => 'read_more_button',
						'value' => array(
							'Yes' => 'yes',
							'No' => 'no'
						),
						'save_always' => true,
						'description' => '',
						'dependency' => Array('element' => 'type', 'value' => array('standard', 'masonry')),
					)
				)
		) );

	}
	public function render($atts, $content = null) {
		
		$default_atts = array(
			'type' => 'masonry',
            'number_of_posts' => '',
            'number_of_columns' => '',
            'space_between_columns' => 'normal',
            'image_size' => 'original',
            'order_by' => '',
            'order' => '',
            'category' => '',
            'title_tag' => 'h4',
			'text_length' => '90',
            'post_info_section' => 'yes',
			'post_info_author' => 'yes',
			'post_info_date' => 'yes',
			'post_info_category' => 'no',
			'post_info_comments' => 'yes',
			'post_info_like' => 'no',
			'post_info_share' => 'no',
			'read_more_button' => 'yes'
        );
		
		$params = shortcode_atts($default_atts, $atts);
		extract($params);
		$params['holder_classes'] = $this->getBlogHolderClasses($params);
	
		$queryArray = $this->generateBlogQueryArray($params);
		$query_result = new \WP_Query($queryArray);
		$params['query_result'] = $query_result;	
     
		
        $thumbImageSize = $this->generateImageSize($params);
		$params['thumb_image_size'] = $thumbImageSize;

		$html ='';
        $html .= walker_edge_get_shortcode_module_template_part('templates/blog-list-holder', 'blog-list', '', $params);
		return $html;	
	}

	/**
	   * Generates holder classes
	   *
	   * @param $params
	   *
	   * @return string
	*/
	private function getBlogHolderClasses($params){
		$holderClasses = '';

        $columnNumber = $this->getColumnNumberClass($params);
        $spaceClass = $this->getSpaceClass($params);
		
		if(!empty($params['type'])){
			switch($params['type']){
				case 'masonry':
					$holderClasses = 'edgtf-masonry';
				break;
                case 'standard' :
                    $holderClasses = 'edgtf-standard';
                break;
                case 'simple':
                    $holderClasses = 'edgtf-simple';
                    break;
				default: 
					$holderClasses = 'edgtf-masonry';
			}
		}

        $holderClasses .= ' '.$columnNumber;
        $holderClasses .= ' '.$spaceClass;
		
		return $holderClasses;
	}

    /**
     * Generates column classes
     *
     * @param $params
     *
     * @return string
     */
    private function getColumnNumberClass($params){

        $columnsNumber = '';
        $type = $params['type'];
        $columns = $params['number_of_columns'];

        if ($type == 'standard' || $type == 'simple') {
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
                default:
                    $columnsNumber = 'edgtf-one-column';
                    break;
            }
        }
        return $columnsNumber;
    }

    /**
     * Generates space classes
     *
     * @param $params
     *
     * @return string
     */
    private function getSpaceClass($params){

        $spaceClass = '';
        $type = $params['type'];
        $columns = $params['space_between_columns'];

        if ($type === 'standard' || $type === 'masonry') {
            switch ($columns) {
                case 'small':
                    $spaceClass = 'edgtf-small-space';
                    break;
                case 'normal':
                    $spaceClass = 'edgtf-normal-space';
                    break;
                case 'large':
                    $spaceClass = 'edgtf-large-space';
                    break;
                default:
                    $spaceClass = 'edgtf-normal-space';
                    break;
            }
        }
        return $spaceClass;
    }

	/**
	   * Generates query array
	   *
	   * @param $params
	   *
	   * @return array
	*/
	public function generateBlogQueryArray($params){
		
		$queryArray = array(
			'orderby' => $params['order_by'],
			'order' => $params['order'],
			'posts_per_page' => $params['number_of_posts'],
			'category_name' => $params['category']
		);
		return $queryArray;
	}

	/**
	   * Generates image size option
	   *
	   * @param $params
	   *
	   * @return string
	*/
	private function generateImageSize($params){
		$thumbImageSize = '';
		$imageSize = $params['image_size'];
		
		if ($imageSize !== '' && $imageSize == 'landscape') {
            $thumbImageSize .= 'walker_edge_landscape';
        } else if($imageSize === 'square'){
			$thumbImageSize .= 'walker_edge_square';
		} else if ($imageSize !== '' && $imageSize == 'original') {
            $thumbImageSize .= 'full';
        }
		return $thumbImageSize;
	}
}
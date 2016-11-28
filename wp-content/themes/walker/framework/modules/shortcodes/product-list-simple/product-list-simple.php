<?php
namespace WalkerEdgeNamespace\Modules\Shortcodes\ProductListSimple;

use WalkerEdgeNamespace\Modules\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class ProductListSimple that represents product list shortcode
 */
class ProductListSimple implements ShortcodeInterface {
    /**
    * @var string
    */
    private $base;
    
    function __construct() {
        $this->base = 'edgtf_product_list_simple';
        
        add_action('vc_before_init', array($this,'vcMap'));
    }
    
    public function getBase() {
        return $this->base;
    }

    public function vcMap() {

        vc_map( array(
            'name' => esc_html__('Edge Product List - Simple', 'walker'),
            'base' => $this->base,
            'icon' => 'icon-wpb-product-list-simple extended-custom-icon',
            'category' => 'by EDGE',
            'allowed_container_element' => 'vc_row',
            'params' => array(
                    array(
                        'type' => 'dropdown',
                        'heading' => 'Type',
                        'param_name' => 'type',
                        'value' => array(
                            'Sale' => 'sale',
                            'Best Sellers' => 'best-sellers',
                            'Featured' => 'featured'
                        ),
                        'save_always' => true,
                        'admin_label' => true
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => 'Number of Products',
                        'param_name'  => 'number',
                        'admin_label' => true,
                        'description' => 'Number of products to show (default value is 4)'
                    ),
                    array(
                        'type' => 'dropdown',
                        'heading' => 'Order By',
                        'param_name' => 'order_by',
                        'value' => array(
                            'Title' => 'title',
                            'Date' => 'date',
                            'ID' => 'id',
                            'Menu Order' => 'menu_order',
                            'Random' => 'rand',
                            'Post Name' => 'name'
                        ),
                        'save_always' => true,
                        'dependency'  => array('element' => 'type', 'value' =>  array('sale', 'featured'))
                    ),
                    array(
                        'type' => 'dropdown',
                        'heading' => 'Order',
                        'param_name' => 'sort_order',
                        'value' => array(
                            'Ascending' => 'ASC',
                            'Descending' => 'DESC'
                        ),
                        'save_always' => true,
                        'dependency'  => array('element' => 'type', 'value' =>  array('sale', 'featured'))
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
                        'description' => ''
                    ),
                    array(
                        'type' => 'dropdown',
                        'admin_label' => true,
                        'heading' => 'Title Tag',
                        'param_name' => 'title_tag',
                        'value' => array(
                            ''   => '',
                            'h4' => 'h4',   
                            'h5' => 'h5',   
                            'h6' => 'h6',   
                        ),
                        'save_always' => true,
                        'description' => '',
                        'dependency' => array('element' => 'display_title', 'value' => array('yes'))
                    ),
                    array(
                        'type' => 'dropdown',
                        'holder' => 'div',
                        'class' => '',
                        'heading' => 'Title Text Transform',
                        'param_name' => 'title_transform',
                        'value' => array(
                            'Default'    => '',
                            'None'       => 'none',
                            'Capitalize' => 'capitalize',
                            'Lowercase'  => 'lowercase',
                            'Uppercase'  => 'uppercase'
                        ),
                        'save_always' => true,
                        'description' => '',
                        'dependency' => array('element' => 'display_title', 'value' => array('yes'))
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
                        'description' => ''
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
                        'description' => ''
                    )
                )
            ) 
        );
    }

    /**
     * Renders HTML for product list shortcode
     *
     * @param array $atts
     * @param null $content
     *
     * @return string
     */
    public function render($atts, $content = null){
        $default_atts = array(
            'type'            => 'sale',
            'number'          => '4',
            'order_by'        => 'title',
            'sort_order'      => 'ASC',
            'display_title'   => 'yes',
            'title_tag'       => 'h5',
            'title_transform' => 'uppercase',
            'display_price'   => 'yes',
            'display_rating'  => 'yes'
        );
        $params = shortcode_atts($default_atts, $atts);
        extract($params);

        $params['holder_classes'] = $this->getHolderClasses($params);

        $tag_array = array('h2', 'h3', 'h4', 'h5', 'h6');
        $params['title_tag'] = (in_array($params['title_tag'], $tag_array)) ? $params['title_tag'] : $default_atts['title_tag'];
        $params['title_styles'] = $this->getTitleStyles($params);

        $queryArray = $this->generateProductQueryArray($params);
        $query_result = new \WP_Query($queryArray);
        $params['query_result'] = $query_result;

        $html = walker_edge_get_shortcode_module_template_part('templates/product-list-template', 'product-list-simple', '', $params);
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

        $productListType = $params['type'];

        switch ($productListType) {
            case 'sale':
                $holderClasses = 'edgtf-pls-sale';
                break;
            case 'best-sellers':
                $holderClasses = 'edgtf-pls-best-sellers';
                break;
            case 'featured':
                $holderClasses = 'edgtf-pls-featured';
                break;
            default:
                $holderClasses = 'edgtf-pls-sale';
                break;
        }
        
        return $holderClasses;
    }

    /**
     * Creates an array of args for loop
     *
     * @param $params
     * @return array
     */
    private function generateProductQueryArray($params){

        global $woocommerce;

        switch($params['type']){
            case 'sale':
                $args = array(
                    'posts_per_page' => $params['number'],
                    'orderby'        => $params['order_by'],
                    'order'          => $params['sort_order'],
                    'post_status'    => 'publish',
                    'post_type'      => 'product',
                    'no_found_rows'  => 1,
                    'meta_query'     => WC()->query->get_meta_query(),
                    'post__in'       => array_merge( array( 0 ), wc_get_product_ids_on_sale() )
                );
                break;
            case 'best-sellers':
                $args = array(
                    'post_type'           => 'product',
                    'post_status'         => 'publish',
                    'ignore_sticky_posts' => 1,
                    'posts_per_page'      => $params['number'],
                    'meta_key'            => 'total_sales',
                    'orderby'             => 'meta_value_num'
                );
                break;
            case 'featured':
                $args = array(
                    'post_type'           => 'product',
                    'post_status'         => 'publish',
                    'posts_per_page' => $params['number'],
                    'orderby'        => $params['order_by'],
                    'order'          => $params['sort_order'],
                    'meta_key' => '_featured',
                    'meta_value' => 'yes',
                );
                break;
        }

        return $args;
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
}
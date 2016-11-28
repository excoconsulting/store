<?php
namespace EdgeCore\CPT\Carousels;

use EdgeCore\Lib;

/**
 * Class CarouselRegister
 * @package EdgeCore\CPT\Carousels
 */
class CarouselRegister implements Lib\PostTypeInterface {
    /**
     * @var string
     */
    private $base;
    /**
     * @var string
     */
    private $taxBase;

    public function __construct() {
        $this->base = 'carousels';
        $this->taxBase = 'carousels_category';
    }

    /**
     * @return string
     */
    public function getBase() {
        return $this->base;
    }

    /**
     * Registers custom post type with WordPress
     */
    public function register() {
        $this->registerPostType();
        $this->registerTax();
    }

    /**
     * Registers custom post type with WordPress
     */
    private function registerPostType() {
        global $walker_edge_Framework;

        $menuPosition = 5;
        $menuIcon = 'dashicons-admin-post';
        if(edgt_core_theme_installed()) {
            $menuPosition = $walker_edge_Framework->getSkin()->getMenuItemPosition('carousel');
            $menuIcon = $walker_edge_Framework->getSkin()->getMenuIcon('carousel');
        }

        register_post_type($this->base,
            array(
                'labels'    => array(
                    'name'        => esc_html__('Edge Carousel','edgt_core' ),
                    'menu_name' => esc_html__('Edge Carousel','edgt_core' ),
                    'all_items' => esc_html__('Carousel Items','edgt_core' ),
                    'add_new' =>  esc_html__('Add New Carousel Item','edgt_core'),
                    'singular_name'   => esc_html__('Carousel Item','edgt_core' ),
                    'add_item'      => esc_html__('New Carousel Item','edgt_core'),
                    'add_new_item'    => esc_html__('Add New Carousel Item','edgt_core'),
                    'edit_item'     => esc_html__('Edit Carousel Item','edgt_core')
                ),
                'public'    =>  false,
                'show_in_menu'  =>  true,
                'rewrite'     =>  array('slug' => 'carousels'),
                'menu_position' =>  $menuPosition,
                'show_ui'   =>  true,
                'has_archive' =>  false,
                'hierarchical'  =>  false,
                'supports'    =>  array('title'),
                'menu_icon'  =>  $menuIcon
            )
        );
    }

    /**
     * Registers custom taxonomy with WordPress
     */
    private function registerTax() {
        $labels = array(
            'name' => esc_html__( 'Carousels', 'edgt_core' ),
            'singular_name' => esc_html__( 'Carousel', 'edgt_core' ),
            'search_items' =>  esc_html__( 'Search Carousels','edgt_core' ),
            'all_items' => esc_html__( 'All Carousels','edgt_core' ),
            'parent_item' => esc_html__( 'Parent Carousel','edgt_core' ),
            'parent_item_colon' => esc_html__( 'Parent Carousel:','edgt_core' ),
            'edit_item' => esc_html__( 'Edit Carousel','edgt_core' ),
            'update_item' => esc_html__( 'Update Carousel','edgt_core' ),
            'add_new_item' => esc_html__( 'Add New Carousel','edgt_core' ),
            'new_item_name' => esc_html__( 'New Carousel Name','edgt_core' ),
            'menu_name' => esc_html__( 'Carousels','edgt_core' ),
        );

        register_taxonomy($this->taxBase, array($this->base), array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'query_var' => true,
            'show_admin_column' => true,
            'rewrite' => array( 'slug' => 'carousels-category' ),
        ));
    }

}
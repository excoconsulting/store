<?php
namespace EdgeCore\CPT\Testimonials;

use EdgeCore\Lib;


/**
 * Class TestimonialsRegister
 * @package EdgeCore\CPT\Testimonials
 */
class TestimonialsRegister implements Lib\PostTypeInterface {
    /**
     * @var string
     */
    private $base;

    public function __construct() {
        $this->base = 'testimonials';
        $this->taxBase = 'testimonials_category';
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
     * Regsiters custom post type with WordPress
     */
    private function registerPostType() {
        global $walker_edge_Framework;

        $menuPosition = 5;
        $menuIcon = 'dashicons-admin-post';

        if(edgt_core_theme_installed()) {
            $menuPosition = $walker_edge_Framework->getSkin()->getMenuItemPosition('testimonial');
            $menuIcon = $walker_edge_Framework->getSkin()->getMenuIcon('testimonial');
        }

        register_post_type('testimonials',
            array(
                'labels' 		=> array(
                    'name' 				=> esc_html__('Testimonials','edgt_core' ),
                    'singular_name' 	=> esc_html__('Testimonial','edgt_core' ),
                    'add_item'			=> esc_html__('New Testimonial','edgt_core'),
                    'add_new_item' 		=> esc_html__('Add New Testimonial','edgt_core'),
                    'edit_item' 		=> esc_html__('Edit Testimonial','edgt_core')
                ),
                'public'		=>	false,
                'show_in_menu'	=>	true,
                'rewrite' 		=> 	array('slug' => 'testimonials'),
                'menu_position' => 	$menuPosition,
                'show_ui'		=>	true,
                'has_archive'	=>	false,
                'hierarchical'	=>	false,
                'supports'		=>	array('title', 'thumbnail'),
                'menu_icon'  =>  $menuIcon
            )
        );
    }

    /**
     * Registers custom taxonomy with WordPress
     */
    private function registerTax() {
        $labels = array(
            'name' => esc_html__( 'Testimonials Categories', 'edgt_core' ),
            'singular_name' => esc_html__( 'Testimonial Category', 'edgt_core' ),
            'search_items' =>  esc_html__( 'Search Testimonials Categories','edgt_core' ),
            'all_items' => esc_html__( 'All Testimonials Categories','edgt_core' ),
            'parent_item' => esc_html__( 'Parent Testimonial Category','edgt_core' ),
            'parent_item_colon' => esc_html__( 'Parent Testimonial Category:','edgt_core' ),
            'edit_item' => esc_html__( 'Edit Testimonials Category','edgt_core' ),
            'update_item' => esc_html__( 'Update Testimonials Category','edgt_core' ),
            'add_new_item' => esc_html__( 'Add New Testimonials Category','edgt_core' ),
            'new_item_name' => esc_html__( 'New Testimonials Category Name','edgt_core' ),
            'menu_name' => esc_html__( 'Testimonials Categories','edgt_core' ),
        );

        register_taxonomy($this->taxBase, array($this->base), array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'query_var' => true,
            'show_admin_column' => true,
            'rewrite' => array( 'slug' => 'testimonials-category' ),
        ));
    }
}
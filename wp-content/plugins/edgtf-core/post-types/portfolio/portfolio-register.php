<?php
namespace EdgeCore\CPT\Portfolio;

use EdgeCore\Lib\PostTypeInterface;

/**
 * Class PortfolioRegister
 * @package EdgeCore\CPT\Portfolio
 */
class PortfolioRegister implements PostTypeInterface {
    /**
     * @var string
     */
    private $base;

    public function __construct() {
        $this->base = 'portfolio-item';
        $this->taxBase = 'portfolio-category';

        add_filter('single_template', array($this, 'registerSingleTemplate'));
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
        $this->registerTagTax();
    }

    /**
     * Registers portfolio single template if one does'nt exists in theme.
     * Hooked to single_template filter
     * @param $single string current template
     * @return string string changed template
     */
    public function registerSingleTemplate($single) {
        global $post;

        if($post->post_type == $this->base) {
            if(!file_exists(get_template_directory().'/single-portfolio-item.php')) {
                return EDGE_CORE_CPT_PATH.'/portfolio/templates/single-'.$this->base.'.php';
            }
        }

        return $single;
    }

    /**
     * Registers custom post type with WordPress
     */
    private function registerPostType() {
        global $walker_edge_Framework, $walker_edge_options;

        $menuPosition = 5;
        $menuIcon = 'dashicons-admin-post';
        $slug = $this->base;

        if(edgt_core_theme_installed()) {
            $menuPosition = $walker_edge_Framework->getSkin()->getMenuItemPosition('portfolio');
            $menuIcon = $walker_edge_Framework->getSkin()->getMenuIcon('portfolio');

            if(isset($walker_edge_options['portfolio_single_slug'])) {
                if($walker_edge_options['portfolio_single_slug'] != ""){
                    $slug = $walker_edge_options['portfolio_single_slug'];
                }
            }
        }

        register_post_type( $this->base,
            array(
                'labels' => array(
                    'name' => esc_html__( 'Portfolio','edgt_core' ),
                    'singular_name' => esc_html__( 'Portfolio Item','edgt_core' ),
                    'add_item' => esc_html__('New Portfolio Item','edgt_core'),
                    'add_new_item' => esc_html__('Add New Portfolio Item','edgt_core'),
                    'edit_item' => esc_html__('Edit Portfolio Item','edgt_core')
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => $slug),
                'menu_position' => $menuPosition,
                'show_ui' => true,
                'supports' => array('author', 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'comments'),
                'menu_icon'  =>  $menuIcon
            )
        );
    }

    /**
     * Registers custom taxonomy with WordPress
     */
    private function registerTax() {
        $labels = array(
            'name' => esc_html__( 'Portfolio Categories', 'edgt_core' ),
            'singular_name' => esc_html__( 'Portfolio Category', 'edgt_core' ),
            'search_items' =>  esc_html__( 'Search Portfolio Categories','edgt_core' ),
            'all_items' => esc_html__( 'All Portfolio Categories','edgt_core' ),
            'parent_item' => esc_html__( 'Parent Portfolio Category','edgt_core' ),
            'parent_item_colon' => esc_html__( 'Parent Portfolio Category:','edgt_core' ),
            'edit_item' => esc_html__( 'Edit Portfolio Category','edgt_core' ),
            'update_item' => esc_html__( 'Update Portfolio Category','edgt_core' ),
            'add_new_item' => esc_html__( 'Add New Portfolio Category','edgt_core' ),
            'new_item_name' => esc_html__( 'New Portfolio Category Name','edgt_core' ),
            'menu_name' => esc_html__( 'Portfolio Categories','edgt_core' ),
        );

        register_taxonomy($this->taxBase, array($this->base), array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' => 'portfolio-category' ),
        ));
    }

    /**
     * Registers custom tag taxonomy with WordPress
     */
    private function registerTagTax() {
        $labels = array(
            'name' => esc_html__( 'Portfolio Tags', 'edgt_core' ),
            'singular_name' => esc_html__( 'Portfolio Tag', 'edgt_core' ),
            'search_items' =>  esc_html__( 'Search Portfolio Tags','edgt_core' ),
            'all_items' => esc_html__( 'All Portfolio Tags','edgt_core' ),
            'parent_item' => esc_html__( 'Parent Portfolio Tag','edgt_core' ),
            'parent_item_colon' => esc_html__( 'Parent Portfolio Tags:','edgt_core' ),
            'edit_item' => esc_html__( 'Edit Portfolio Tag','edgt_core' ),
            'update_item' => esc_html__( 'Update Portfolio Tag','edgt_core' ),
            'add_new_item' => esc_html__( 'Add New Portfolio Tag','edgt_core' ),
            'new_item_name' => esc_html__( 'New Portfolio Tag Name','edgt_core' ),
            'menu_name' => esc_html__( 'Portfolio Tags','edgt_core' ),
        );

        register_taxonomy('portfolio-tag',array($this->base), array(
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' => 'portfolio-tag' ),
        ));
    }
}
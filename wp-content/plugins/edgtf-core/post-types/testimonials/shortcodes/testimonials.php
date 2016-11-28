<?php

namespace EdgeCore\CPT\Testimonials\Shortcodes;


use EdgeCore\Lib;

/**
 * Class Testimonials
 * @package EdgeCore\CPT\Testimonials\Shortcodes
 */
class Testimonials implements Lib\ShortcodeInterface
{
    /**
     * @var string
     */
    private $base;

    public function __construct() {
        $this->base = 'edgtf_testimonials';

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
     * Maps shortcode to Visual Composer
     *
     * @see vc_map()
     */
    public function vcMap() {
        if(function_exists('vc_map')) {
            vc_map( array(
                'name' => 'Edge Testimonials',
                'base' => $this->base,
                'category' => 'by EDGE',
                'icon' => 'icon-wpb-testimonials extended-custom-icon',
                'allowed_container_element' => 'vc_row',
                'params' => array(
                    array(
                        'type'        => 'textfield',
                        'heading'     => 'Custom CSS class',
                        'param_name'  => 'custom_class',
                        'admin_label' => true
                    ),
                    array(
                        'type' => 'textfield',
                        'admin_label' => true,
                        'heading' => 'Category',
                        'param_name' => 'category',
                        'value' => '',
                        'description' => 'Category Slug (leave empty for all)'
                    ),
                    array(
                        'type' => 'textfield',
                        'admin_label' => true,
                        'heading' => 'Number',
                        'param_name' => 'number',
                        'value' => '',
                        'description' => 'Number of Testimonials'
                    ),
                    array(
                        'type' => 'dropdown',
                        'admin_label' => true,
                        'heading' => 'Show Author',
                        'param_name' => 'show_author',
                        'value' => array(
                            'Yes' => 'yes',
                            'No' => 'no'
                        ),
                        'save_always' => true,
                        'group' => 'Content Styles',
                        'description' => ''
                    ),
                    array(
                        'type' => 'dropdown',
                        'admin_label' => true,
                        'heading' => 'Show Author Job Position',
                        'param_name' => 'show_position',
                        'value' => array(
                            'Yes' => 'yes',
                            'No' => 'no',
                        ),
                        'save_always' => true,
                        'group' => 'Content Styles',
                        'dependency' => array('element' => 'show_author', 'value' => array('yes')),
                        'description' => ''
                    ),
                    array(
                        'type' => 'dropdown',
                        'admin_label' => true,
                        'heading' => 'Show Navigation',
                        'param_name' => 'show_navigation',
                        'value' => array(
                            'Yes' => 'yes',
                            'No' => 'no',
                        ),
                        'save_always' => true,
                        'group' => 'Content Styles',
                        'description' => ''
                    ),
                    array(
                        'type' => 'textfield',
                        'admin_label' => true,
                        'heading' => 'Animation speed',
                        'param_name' => 'animation_speed',
                        'value' => '',
                        'description' => 'Speed of slide animation in milliseconds. Default value is 600.'
                    )
                )
            ) );
        }
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
            'custom_class' => '',
            'number' => '-1',
            'category' => '',
            'show_author' => 'yes',
            'show_position' => 'yes',
            'show_navigation' => '',
            'animation_speed' => ''
        );
        $params = shortcode_atts($args, $atts);


        $params['number'] = esc_attr($params['number']);
        $params['category'] = esc_attr($params['category']);
        $params['animation_speed'] = esc_attr($params['animation_speed']);


        //Extract params for use in method
        extract($params);

        $data_attr = $this->getDataParams($params);
        $query_args = $this->getQueryParams($params);
        $query_results = new \WP_Query($query_args);

        $holder_classes = $this->getHolderClasses($params);
        $paramClasses = $this->getParamClasses($params);

        $html = '';
        $html .= '<div class="edgtf-testimonials-holder clearfix '.$holder_classes.'">';
        $html .= '<div class="edgtf-testimonials-inner">';
        $html .= '<div class="edgtf-grid">';

        $html .= '<div class="edgtf-testimonials ' . $paramClasses.'"' . $data_attr . '>';

        if ($query_results->have_posts()):
            while ($query_results->have_posts()) : $query_results->the_post();
                $author = get_post_meta(get_the_ID(), 'edgtf_testimonial_author', true);
                $text = get_post_meta(get_the_ID(), 'edgtf_testimonial_text', true);
                $title = get_post_meta(get_the_ID(), 'edgtf_testimonial_title', true);
                $job = get_post_meta(get_the_ID(), 'edgtf_testimonial_author_position', true);

                $params['author'] = $author;
                $params['text'] = $text;
                $params['title'] = $title;
                $params['job'] = $job;
                $params['current_id'] = get_the_ID();

                $html .= edgt_core_get_shortcode_module_template_part('testimonials', 'testimonials-classic', '', $params);

            endwhile;
        else:
            $html .= esc_html__('Sorry, no posts matched your criteria.', 'edgt_core');
        endif;

        wp_reset_postdata();

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Returns array of holder classes
     *
     * @param $params
     *
     * @return array
     */
    private function getHolderClasses($params) {
        $classes = array();

        if(!empty($params['custom_class'])) {
            $classes[] = $params['custom_class'];
        }

        return implode(' ', $classes);
    }

    /**
     * Generates testimonial data attribute array
     *
     * @param $params
     *
     * @return string
     */
    private function getDataParams($params) {
        $data_attr = '';

        if (!empty($params['animation_speed'])) {
            $data_attr .= ' data-animation-speed ="' . $params['animation_speed'] . '"';
        }

        return $data_attr;
    }

    /**
     * Generates testimonials query attribute array
     *
     * @param $params
     *
     * @return array
     */
    private function getQueryParams($params) {

        $args = array(
            'post_type' => 'testimonials',
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => $params['number']
        );

        if ($params['category'] != '') {
            $args['testimonials_category'] = $params['category'];
        }
        return $args;
    }

    /**
     * Generates testimonials param classes
     *
     * @param $params
     *
     * @return array
     */
    private function getParamClasses($params) {

        $classes = array();

        if ($params['number'] == '1') {
            $classes[] = 'edgtf-single-testimonial';
        }
        if ($params['show_navigation'] == 'yes') {
            $classes[] = 'edgtf-testimonials-navigation';
        }

        return implode(' ', $classes);
    }
}
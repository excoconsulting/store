<?php

/**
 * Widget that adds blog list
 *
 * Class Blog_List_Widget
 */
class WalkerEdgeClassBlogListWidget extends WalkerEdgeClassWidget {
    /**
     * Set basic widget options and call parent class construct
     */
    public function __construct() {
        parent::__construct(
            'edgtf_blog_list_widget', // Base ID
            'Edge Blog List Widget' // Name
        );

        $this->setParams();
    }

    /**
     * Sets widget options
     */
    protected function setParams() {
        $this->params = array(
            array(
                'type' => 'textfield',
                'name' => 'widget_title',
                'title' => 'Widget Title'
            ),
            array(
                'type' => 'textfield',
                'title' => 'Number of Posts',
                'name' => 'number_of_posts',
                'description' => ''
            ),
            array(
                'type' => 'dropdown',
                'title' => 'Order By',
                'name' => 'order_by',
                'options' => array(
                    'title' => 'Title',
                    'date' => 'Date',
                    'rand' => 'Random',
                    'name' => 'Post Name'
                ),
                'description' => ''
            ),
            array(
                'type' => 'dropdown',
                'title' => 'Order',
                'name' => 'order',
                'options' => array(
                    'ASC' => 'ASC',
                    'DESC' => 'DESC'
                ),
                'description' => ''
            ),
            array(
                'type' => 'textfield',
                'title' => 'Category Slug',
                'name' => 'category',
                'description' => 'Leave empty for all or use comma for list'
            ),
            array(
                'type' => 'dropdown',
                'title' => 'Image Size',
                'name' => 'image_size',
                'options' => array(
                    'original' => 'Original',
                    'landscape' => 'Landscape',
                    'square' => 'Square'
                ),
                'description' => ''
            ),
            array(
                'type' => 'textfield',
                'title' => 'Text Length',
                'name' => 'text_length',
                'description' => 'Number of characters'
            ),
            array(
                'type' => 'dropdown',
                'title' => 'Title Tag',
                'name' => 'title_tag',
                'options' => array(
                    'h5' => 'h5',
                    'h2' => 'h2',
                    'h3' => 'h3',
                    'h4' => 'h4',
                    'h6' => 'h6',
                ),
                'description' => ''
            ),
            array(
                'type' => 'dropdown',
                'title' => 'Enable Post Info Section',
                'name' => 'post_info_section',
                'options' => array(
                    'yes' => 'Yes',
                    'no' => 'No'
                ),
                'description' => ''
            ),
            array(
                'type' => 'dropdown',
                'title' => 'Enable Post Info Author',
                'name' => 'post_info_author',
                'options' => array(
                    'yes' => 'Yes',
                    'no' => 'No'
                )
            ),
            array(
                'type' => 'dropdown',
                'title' => 'Enable Post Info Date',
                'name' => 'post_info_date',
                'options' => array(
                    'yes' => 'Yes',
                    'no' => 'No'
                )
            ),
            array(
                'type' => 'dropdown',
                'title' => 'Enable Post Info Category',
                'name' => 'post_info_category',
                'options' => array(
                    'no' => 'No',
                    'yes' => 'Yes'
                )
            ),
            array(
                'type' => 'dropdown',
                'title' => 'Enable Post Info Comments',
                'name' => 'post_info_comments',
                'options' => array(
                    'yes' => 'Yes',
                    'no' => 'No'
                )
            ),
            array(
                'type' => 'dropdown',
                'title' => 'Enable Post Info Like',
                'name' => 'post_info_like',
                'options' => array(
                    'no' => 'No',
                    'yes' => 'Yes'
                )
            ),
            array(
                'type' => 'dropdown',
                'title' => 'Enable Post Info Share',
                'name' => 'post_info_share',
                'options' => array(
                    'no' => 'No',
                    'yes' => 'Yes'
                )
            ),
            array(
                'type' => 'dropdown',
                'title' => 'Enable Read More Button',
                'name' => 'read_more_button',
                'options' => array(
                    'no' => 'No',
                    'yes' => 'Yes'
                ),
                'description' => ''
            )
        );
    }

    /**
     * Generates widget's HTML
     *
     * @param array $args args from widget area
     * @param array $instance widget's options
     */
    public function widget($args, $instance) {

        //prepare variables
        $params = '';

        $instance['type'] = 'standard';
        $instance['number_of_columns'] = 1;

        //is instance empty?
        if(is_array($instance) && count($instance)) {
            //generate shortcode params
            foreach($instance as $key => $value) {
                $params .= " $key='$value' ";
            }
        }

        echo '<div class="widget edgtf-blog-list-widget">';

            if($instance['widget_title'] !== '') {
                print $args['before_title'].$instance['widget_title'].$args['after_title'];
            }

            //finally call the shortcode
            echo do_shortcode("[edgtf_blog_list $params]"); // XSS OK

            echo '</div>'; //close div.mkdf-plw-seven
    }
}
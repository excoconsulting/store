<?php

namespace EdgeCore\CPT\Slider\Shortcodes;

use EdgeCore\Lib;

/**
 * Class Slider
 * @package EdgeCore\CPT\Slider\Shortcodes
 */
class Slider implements Lib\ShortcodeInterface {
    /**
     * @var string
     */
    private $base;

    public function __construct() {
        $this->base = 'edgtf_slider';
    }

    /**
     * Returns base for shortcode
     * @return string
     */
    public function getBase() {
        return $this->base;
    }

    /**
     * Renders shortcodes HTML
     *
     * @param $atts array of shortcode params
     * @param $content string shortcode content
     * @return string
     */
    public function render($atts, $content = null) {

        global $walker_edge_options;
        extract(shortcode_atts(array("slider" => ""), $atts));
        $html = "";

        if ($slider != "") {
            $args = array(
                'post_type'       => 'slides',
                'slides_category' => $slider,
                'orderby'         => "menu_order",
                'order'           => "ASC",
                'posts_per_page'  => - 1
            );

            $slider_id = get_term_by('slug', $slider, 'slides_category')->term_id;
            $slider_meta = get_option("taxonomy_term_" . $slider_id);

            /* check if slider should have an anchor - start */
            $slider_anchor = '';
            if (isset($slider_meta['anchor']) && $slider_meta['anchor'] != '') {
                $slider_anchor = 'data-edgtf-anchor="'.$slider_meta['anchor'].'"';
            }
            /* check if slider should have an anchor - end */

            /* check if slider effects on header style - start */
            $slider_header_effect = $slider_meta['header_effect'];
            if ($slider_header_effect == 'yes') {
                $header_effect_class = 'edgtf-header-effect';
            } else {
                $header_effect_class = '';
            }
            /* check if slider effects on header style - end */

            /* check if slider has parallax effect - start */
            $slider_css_position_class = '';
            $slider_parallax = 'yes';
            if (isset($slider_meta['slider_parallax_effect'])) {
                $slider_parallax = $slider_meta['slider_parallax_effect'];
            }
            if ($slider_parallax == 'no') {
                $data_parallax_effect = 'data-parallax="no"';
                $data_parallax_transform = '';
                $slider_css_position_class = 'edgtf-relative-position';
            } else {
                $data_parallax_effect = 'data-parallax="yes"';
                $data_parallax_transform = 'data-start="transform: translateY(0px);" data-1440="transform: translateY(500px);"';
                $slider_css_position_class = 'edgtf-parallax-enabled';
            }

            /* check if slider has parallax effect - end */

            /* set heights for slider - start */
            $height = "";
            if (isset($slider_meta['height']) && $slider_meta['height'] != '') {
                $height = esc_attr($slider_meta['height']);
            } 
            $responsive_height = "";
            if (isset($slider_meta['responsive_height']) && $slider_meta['responsive_height'] != '') {
                $responsive_height = esc_attr($slider_meta['responsive_height']);
            } 
            if ($height == "" || $height == "0") {
                $full_screen_class = "edgtf-full-screen";
                $responsive_height_class = "";
                $height_class = "";
                $slide_holder_height = "";
                $slide_height = "";
                $data_height = "";
                $carouselinner_height = 'height: 100%';
            } else {
                $full_screen_class = "";
                $height_class = "edgtf-has-height";
                if ($responsive_height == "yes") {
                    $responsive_height_class = "edgtf-responsive-height";
                } else {
                    $responsive_height_class = "";
                }
                $slide_holder_height = "height: " . $height . "px;";
                $slide_height = "height: " . ($height) . "px;";
                $data_height = "data-height='" . $height . "'";
                $carouselinner_height = "height: 100%;"; 
            }
            /* set heights for slider - start */

            /* set paspartu for slider - start */
            $slide_holder_paspartu = "";
            $slider_arrows_margin_left = "";
            $slider_arrows_margin_right = "";
            if (isset($slider_meta['paspartu']) && $slider_meta['paspartu'] != '') {
                $slide_holder_paspartu .= 'padding-left: ' . $slider_meta['paspartu'] . '%;';
                $slide_holder_paspartu .= 'padding-right: ' . $slider_meta['paspartu'] . '%;';

                /* add margin on arrows */
                $slider_arrows_margin_left .= 'margin-left: ' . $slider_meta['paspartu'] . '%;';
                $slider_arrows_margin_right .= 'margin-right: ' . $slider_meta['paspartu'] . '%;';
            }
            /* set paspartu for slider - end */

            /* check if slider has auto start - start */
            $auto = "yes";
            if (isset($slider_meta['auto_start']) && $slider_meta['auto_start'] != '') {
                $auto = $slider_meta['auto_start'];
            }

            if ($auto == "yes") {
                $auto_start_class = "edgtf-auto-start";
            } else {
                $auto_start_class = "";
            }
            /* check if slider has auto start - end */

            /* check for alide animation time - start */
            $slide_animation_timeout = "";
            if (isset($slider_meta['animation_timeout']) && $slider_meta['animation_timeout'] != '') {
                $slide_animation_timeout = 'data-slide_animation_timeout="' . $slider_meta['animation_timeout'] . '"';
            } else {
                $slide_animation_timeout = 'data-slide_animation_timeout="6000"';
            }
            /* check for alide animation time - end */

            /* check for slide animation type - start */
            $animation_type = 'slide';
            if (isset($slider_meta['animation_type']) && $slider_meta['animation_type'] != '') {
                $animation_type = $slider_meta['animation_type'];
            }
            switch ($animation_type) {
                case 'fade':
                    $animation_type_class = 'edgtf-fade';
                    break;
                case 'slide-vertical-up':
                    $animation_type_class = 'edgtf-vertical-up';
                    break;
                case 'slide-vertical-down':
                    $animation_type_class = 'edgtf-vertical-down';
                    break;
                case 'slide-cover':
                    $animation_type_class = 'edgtf-slide-cover';
                    break;
                case 'slide-peek':
                    $animation_type_class = 'edgtf-slide-peek';
                    break;    
                default:
                    $animation_type_class = 'edgtf-slide';
            }
            /* check for slide animation type - end */

            /* set slider preloader - start */
            if($walker_edge_options['smooth_page_transitions'] == "yes" && $walker_edge_options['smooth_pt_spinner_type'] != "") {
                $ajax_loader = '<div class="edgtf-st-loader"><div class="edgtf-st-loader1">' . walker_edge_loading_spinners(true) . '</div></div>';
            }else{
                $ajax_loader = '<div class="edgtf-st-loader"><div class="edgtf-st-loader1">'. walker_edge_loading_spinner_pulse() .'</div></div>';
            }

            /* set slider preloader - end */

            /* set padding for slider arrows - start */
            $slider_arrows_padding = "padding-top: " . esc_attr($this->walker_edge_get_slider_navigation_padding()) . "px;";
            /* set padding for slider arrows - end */

            $html .= '<div id="edgtf-' . esc_attr($slider) . '" ' . $slider_anchor . ' class="carousel slide ' . esc_attr($animation_type_class . ' ' . $full_screen_class . ' ' . $responsive_height_class . ' ' . $height_class . ' ' . $auto_start_class . ' ' . $header_effect_class) . ' " ' . $slide_animation_timeout . ' ' . $data_height . ' ' . $data_parallax_effect . ' '.walker_edge_get_inline_style($slide_holder_height . ' ' .$slide_holder_paspartu).'><div class="edgtf-slider-preloader">'.$ajax_loader.'</div>';
            $html .= '<div class="carousel-inner ' . esc_attr($slider_css_position_class) . '" '.walker_edge_get_inline_style($carouselinner_height).' '.$data_parallax_transform.'>';

            global $wp_query;
            query_posts($args);
            $found_slides = $wp_query->post_count;

            if (have_posts()) : $postCount = 0;
                while (have_posts()) : the_post();
                    //get slider type
                    $slide_type = get_post_meta(get_the_ID(), "edgtf_slide_background_type", true);
                    
                    //get slider image
                    $image = esc_url(get_post_meta(get_the_ID(), "edgtf_slide_image", true));
                    $slide_image_tablet_responsive = esc_url(get_post_meta(get_the_ID(), "edgtf_slide_image_tablet", true));
                    $slide_image_phone_responsive = esc_url(get_post_meta(get_the_ID(), "edgtf_slide_image_phone", true));
                    $image_holder_data = array();
                    if($image != '') {
                        $image_holder_data[] = 'data-original-image = '.$image;
                    }
                    if($slide_image_tablet_responsive != '') {
                        $image_holder_data[] = 'data-responsive-tablet-image = '.$slide_image_tablet_responsive;
                    }
                    if($slide_image_phone_responsive != '') {
                        $image_holder_data[] = 'data-responsive-phone-image = '.$slide_image_phone_responsive;
                    }                        

                    //get slider overlay pattern
                    $image_overlay_pattern = esc_url(get_post_meta(get_the_ID(), "edgtf_slide_overlay_image", true));

                    /* get slider video files - start */
                    $video_webm = esc_url(get_post_meta(get_the_ID(), "edgtf_slide_video_webm", true));
                    $video_mp4 = esc_url(get_post_meta(get_the_ID(), "edgtf_slide_video_mp4", true));
                    $video_ogv = esc_url(get_post_meta(get_the_ID(), "edgtf_slide_video_ogv", true));
                    $video_image = esc_url(get_post_meta(get_the_ID(), "edgtf_slide_video_image", true));
                    $video_overlay = get_post_meta(get_the_ID(), "edgtf_slide_video_overlay", true);
                    $video_overlay_image = esc_url(get_post_meta(get_the_ID(), "edgtf_slide_video_overlay_image", true));
                    /* get slider video files - end */

                    /* slider image animation settings - start */
                    $animate_image_class = "";
                    $animate_image_data = "";
                    if (get_post_meta(get_the_ID(), "edgtf_enable_image_animation", true) == "yes") {
                        $animate_image_class .= "edgtf-animate-image ";
                        $animate_image_class .= ($meta_temp = get_post_meta(get_the_ID(), "edgtf_enable_image_animation_type", true));
                        $animate_image_data .= "data-edgtf_animate_image='".$meta_temp."'";
                    }
                    /* slider image animation settings - end */


                    /* slide on scroll animation - start */

                    //fefault values for data start and data end animation
                    $data_start_amount = '0';
                    $data_end_amount = '300';
                    $data_start_custom_style = ' opacity: 1;';
                    $data_end_custom_style = ' opacity: 0;';

                    $slide_elements_data_start = ' data-' . $data_start_amount . '="' . $data_start_custom_style . ' ' . 'top: 50%;' . '"';
                    $slide_elements_data_end = ' data-' . $data_end_amount . '="' . $data_end_custom_style . ' ' . 'top: 25%;' . '"';

                    /* slide on scroll animation - end */

                    /* check if slide has header style settings - start */
                    $header_style = "";
                    if (($meta_temp = get_post_meta(get_the_ID(), "edgtf_slide_header_style", true)) != "") {
                        $header_style = $meta_temp;
                    }
                    /* check if slide has header style settings - end */


                    $html .= '<div class="item ' . $header_style . ' '.$animate_image_class.'" style="' . $slide_height . '" '.$animate_image_data.'>';

                    $title = get_the_title();

                    /* render video or image for slide item - start */
                    if ($slide_type == 'video') {

                        $html .= '<div class="edgtf-video"><div class="edgtf-mobile-video-image" '.walker_edge_get_inline_style('background-image: url(' . esc_url($video_image) . ')').'></div><div class="edgtf-video-overlay';
                        if ($video_overlay == "yes") {
                            $html .= ' active';
                        }
                        $html .= '"';
                        if ($video_overlay_image != "") {
                            $html .= ' style="background-image:url(' . esc_url($video_overlay_image) . ');"';
                        }
                        $html .= '>';
                        if ($video_overlay_image != "") {
                            $html .= '<img src="' . esc_url($video_overlay_image) . '" alt="" />';
                        } else {
                            $html .= '<img src="' . esc_url(get_template_directory_uri()) . '/assets/css/img/pixel-video.png" alt="" />';
                        }
                        $html .= '</div><div class="edgtf-video-wrap">';
                        $html .= '<video class="edgtf-video-element" width="1920" height="800" poster="' . esc_url($video_image) . '" controls="controls" preload="auto" loop autoplay>';
                        if (!empty($video_webm)) {
                            $html .= '<source type="video/webm" src="' . esc_url($video_webm) . '">';
                        }
                        if (!empty($video_mp4)) {
                            $html .= '<source type="video/mp4" src="' . esc_url($video_mp4) . '">';
                        }
                        if (!empty($video_ogv)) {
                            $html .= '<source type="video/ogg" src="' . esc_url($video_ogv) . '">';
                        }
                        $html .='<object width="320" height="240" type="application/x-shockwave-flash" data="' . esc_url(get_template_directory_uri()) . '/assets/js/flashmediaelement.swf">
                                                    <param name="movie" value="' . esc_url(get_template_directory_uri()) . '/assets/js/flashmediaelement.swf" />
                                                    <param name="flashvars" value="controls=true&amp;file=' . esc_url($video_mp4) . '" />
                                                    <img src="' . esc_url($video_image) . '" width="1920" height="800" title="No video playback capabilities" alt="Video thumb" />
                                            </object>
                                    </video>
                            </div></div>';
                    } else {
                        $html .= '<div class="edgtf-image" '.walker_edge_get_inline_style('background-image:url(' . esc_url($image) . ')').' '.implode(' ',$image_holder_data).'>';
                        $html .= '<img src="' . esc_url($image) . '" alt="' . esc_attr($title) . '">';

                        if ($image_overlay_pattern !== "") {
                            $html .= '<div class="edgtf-image-pattern" '.walker_edge_get_inline_style('background: url(' . esc_url($image_overlay_pattern) . ') repeat 0 0').'></div>';
                        }
                        $html .= '</div>';
                    }
                    /* render video or image for slide item - end */


                    /* Slide elements insertion - start */

                    $element_positions = get_post_meta( get_the_ID(), 'edgtf_slide_holder_elements_alignment', true );
                    if (!strlen($element_positions)) {
                        $element_positions = 'center';
                    }

                    $frame_in_grid = get_post_meta( get_the_ID(), 'edgtf_slide_holder_frame_in_grid', true );
                    if (!strlen($frame_in_grid)) {
                        $frame_in_grid = 'no';
                    }

                    $def_width = get_post_meta( get_the_ID(), 'edgtf_slide_elements_default_width', true );
                    if (!strlen($def_width)) {
                        $def_width = '1920';
                    }

                    if ($element_positions == 'custom') {
                        $html .= 
                            '<div class="edgtf-slider-elements-container edgtf-custom-elements '.( $frame_in_grid == 'yes' ? 'edgtf-grid' : '' ).'" '.walker_edge_get_inline_style( ($frame_in_grid == 'no' && (($meta_temp = get_post_meta(get_the_ID(), "edgtf_slide_holder_frame_width", true)) != '') ? 'width: '.$meta_temp.'%; ' : '') . 'padding-top: ' . esc_attr($this->walker_edge_get_slider_navigation_padding()) . 'px;').' '.$slide_elements_data_start.' '.$slide_elements_data_end.($frame_in_grid == 'yes' ? '' : ' data-width-mobile="'.esc_attr(get_post_meta(get_the_ID(), 'edgtf_slide_holder_frame_width_mobile', true)).'" data-width-tablet-p="'.esc_attr(get_post_meta(get_the_ID(), 'edgtf_slide_holder_frame_width_tablet_p', true)).'" data-width-tablet-l="'.esc_attr(get_post_meta(get_the_ID(), 'edgtf_slide_holder_frame_width_tablet_l', true)).'" data-width-laptop="'.esc_attr(get_post_meta(get_the_ID(), 'edgtf_slide_holder_frame_width_laptop', true)).'" data-width-desktop="'.esc_attr(get_post_meta(get_the_ID(), 'edgtf_slide_holder_frame_width_desktop', true)).'"').'>'.
                                '<div class="edgtf-slider-elements-container-inner" ' . walker_edge_get_inline_style((($meta_temp = get_post_meta(get_the_ID(), "edgtf_slide_holder_frame_height", true)) != '') ? 'padding-bottom: '.$meta_temp.'%;' : '') . '>' .
                                    '<div class="edgtf-slider-elements-holder-frame" ' . walker_edge_get_inline_style('width: 100%; height: 100%').' data-default-width="'.esc_attr($def_width).'">'
                        ;
                    }
                    else {
                        $html .= 
                            '<div class="edgtf-slider-elements-container" '.walker_edge_get_inline_style('width: 100%; box-sizing: border-box; height: 100%; ' . 'padding-top: ' . esc_attr($this->walker_edge_get_slider_navigation_padding()) . 'px;').' '.$slide_elements_data_start.' '.$slide_elements_data_end.($frame_in_grid == 'yes' ? '' : ' data-width-mobile="'.esc_attr(get_post_meta(get_the_ID(), 'edgtf_slide_holder_frame_width_mobile', true)).'" data-width-tablet-p="'.esc_attr(get_post_meta(get_the_ID(), 'edgtf_slide_holder_frame_width_tablet_p', true)).'" data-width-tablet-l="'.esc_attr(get_post_meta(get_the_ID(), 'edgtf_slide_holder_frame_width_tablet_l', true)).'" data-width-laptop="'.esc_attr(get_post_meta(get_the_ID(), 'edgtf_slide_holder_frame_width_laptop', true)).'" data-width-desktop="'.esc_attr(get_post_meta(get_the_ID(), 'edgtf_slide_holder_frame_width_desktop', true)).'"').'>'.
                                '<div class="edgtf-slider-elements-container-inner" '.walker_edge_get_inline_style('height: 100%;').'>' .
                                    '<div class="edgtf-slider-elements-holder-frame '.( $frame_in_grid == 'yes' ? 'edgtf-grid' : '' ).'" '.walker_edge_get_inline_style( ($frame_in_grid == 'no' && (($meta_temp = get_post_meta(get_the_ID(), "edgtf_slide_holder_frame_width", true)) != '') ? 'width: '.$meta_temp.'%; ' : '').'top: 50%; left: 50%; -webkit-transform: translate3d(-50%,-50%,0); transform: translate3d(-50%,-50%,0);').' data-default-width="'.esc_attr($def_width).'">'
                        ;
                    }

                    $default_animation = get_post_meta( get_the_ID(), 'edgtf_slide_elements_default_animation', true );
                    if (!strlen($default_animation)) {
                        $default_animation = 'none';
                    }

                    // These keys and values have to match those in edgt.layout.inc, map.php (for slider) and shortcodes.js
                    $default_factors = array( 
                        "mobile" => 0.5,
                        "tabletp" => 0.6,
                        "tabletl" => 0.7,
                        "laptop" => 0.8,
                        "desktop" => 1
                    );
                    $default_left = array( 
                        "mobile" => "",
                        "tabletp" => "",
                        "tabletl" => "",
                        "laptop" => "",
                        "desktop" => ""
                    );
                    $default_top = array( 
                        "mobile" => "",
                        "tabletp" => "",
                        "tabletl" => "",
                        "laptop" => "",
                        "desktop" => ""
                    );

                    $slide_bottom_html = '';
                    $no = 1;
                    $slide_elements = get_post_meta(get_the_ID(), 'edgt_slide_elements', true );
                    while (isset($slide_elements[$no-1])) {
                        $slide_element = $slide_elements[$no-1];

                        if ($slide_element['slideelementvisible'] == 'yes') {
                            $slide_element_html = '';
                            $class_string = 'edgtf-el edgtf-slide-element';
                            $data_string = '';
                            $style_string = '';
                            $inner_style_string = ''; 

                            if ($element_positions != 'custom') {
                                $style_string .= 'position: static; text-align: '.$element_positions.';';
                                if ($slide_element['slideelementmargintop'] != '') {
                                    $style_string .= 'margin-top: '.esc_attr($slide_element['slideelementmargintop']).'px;';
                                    $data_string .= 'data-default-margin-top="'.esc_attr($slide_element['slideelementmargintop']).'" ';
                                }
                                if ($slide_element['slideelementmarginbottom'] != '') {
                                    $style_string .= 'margin-bottom: '.esc_attr($slide_element['slideelementmarginbottom']).'px;';
                                    $data_string .= 'data-default-margin-bottom="'.esc_attr($slide_element['slideelementmarginbottom']).'" ';
                                }
                                if ($slide_element['slideelementmarginleft'] != '') {
                                    $style_string .= 'margin-left: '.esc_attr($slide_element['slideelementmarginleft']).'px;';
                                    $data_string .= 'data-default-margin-left="'.esc_attr($slide_element['slideelementmarginleft']).'" ';
                                }
                                if ($slide_element['slideelementmarginright'] != '') {
                                    $style_string .= 'margin-right: '.esc_attr($slide_element['slideelementmarginright']).'px;';
                                    $data_string .= 'data-default-margin-right="'.esc_attr($slide_element['slideelementmarginright']).'" ';
                                }
                            }
                            else {
                                if ($slide_element['slideelementzindex'] != '') {
                                    $style_string .= 'z-index: '.esc_attr($slide_element['slideelementzindex']).';';
                                }
                                switch ($slide_element['slideelementpivot']) {
                                    case 'top-center':
                                        $style_string .= '-webkit-transform: translate(-50%,0); transform: translate(-50%,0); text-align: center;';
                                    break;
                                    case 'top-right':
                                        $style_string .= '-webkit-transform: translate(-100%,0); transform: translate(-100%,0); text-align: right;';
                                    break;
                                    case 'middle-left':
                                        $style_string .= '-webkit-transform: translate(0,-50%); transform: translate(0,-50%);';
                                    break;
                                    case 'middle-center':
                                        $style_string .= '-webkit-transform: translate(-50%,-50%); transform: translate(-50%,-50%); text-align: center;';
                                    break;
                                    case 'middle-right':
                                        $style_string .= '-webkit-transform: translate(-100%,-50%); transform: translate(-100%,-50%); text-align: right;';
                                    break;
                                    case 'bottom-left':
                                        $style_string .= '-webkit-transform: translate(0,-100%); transform: translate(0,-100%);';
                                    break;
                                    case 'bottom-center':
                                        $style_string .= '-webkit-transform: translate(-50%,-100%); transform: translate(-50%,-100%); text-align: center;';
                                    break;
                                    case 'bottom-right':
                                        $style_string .= '-webkit-transform: translate(-100%,-100%); transform: translate(-100%,-100%); text-align: right;';
                                    break;
                                }
                            }

                            if ($slide_element['slideelementanimation'] == 'default') {
                                if ($default_animation != 'none') {
                                    $class_string .= ' edgtf-slide-element-animation-'.$default_animation;
                                    if ($slide_element['slideelementanimationdelay'] != '') {
                                        $inner_style_string .= '-webkit-animation-delay: '.esc_attr($slide_element['slideelementanimationdelay']).'; animation-delay: '.esc_attr($slide_element['slideelementanimationdelay']).';';
                                    }
                                    if ($slide_element['slideelementanimationduration'] != '') {
                                        $inner_style_string .= '-webkit-animation-duration: '.esc_attr($slide_element['slideelementanimationduration']).'; animation-duration: '.esc_attr($slide_element['slideelementanimationduration']).';';
                                    }
                                }
                            }
                            else {
                                $class_string .= ' edgtf-slide-element-animation-'.$slide_element['slideelementanimation'];
                                if ($slide_element['slideelementanimationdelay'] != '') {
                                    $inner_style_string .= '-webkit-animation-delay: '.esc_attr($slide_element['slideelementanimationdelay']).'; animation-delay: '.esc_attr($slide_element['slideelementanimationdelay']).';';
                                }
                                if ($slide_element['slideelementanimationduration'] != '') {
                                    $inner_style_string .= '-webkit-animation-duration: '.esc_attr($slide_element['slideelementanimationduration']).'; animation-duration: '.esc_attr($slide_element['slideelementanimationduration']).';';
                                }
                            }

                            $responsiveness_data = '';
                            if ($slide_element['slideelementresponsive'] == 'stages') {
                                $temp_scale = $default_factors;
                                foreach($temp_scale as $key => &$value) {
                                    if ($slide_element['slideelementscale'.$key] != '') {
                                        $value = $slide_element['slideelementscale'.$key];
                                    }
                                }
                                $responsiveness_data .= 'data-resp-scale="'.esc_attr(json_encode($temp_scale)).'" ';

                                if ($element_positions == 'custom') {
                                    $temp_left = $default_left;
                                    foreach($temp_left as $key => &$value) {
                                        if ($slide_element['slideelementleft'.$key] != '') {
                                            $value = $slide_element['slideelementleft'.$key];
                                        }
                                    }
                                    $responsiveness_data .= 'data-resp-left="'.esc_attr(json_encode($temp_left)).'" ';

                                    $temp_top = $default_top;
                                    foreach($temp_top as $key => &$value) {
                                        if ($slide_element['slideelementtop'.$key] != '') {
                                            $value = $slide_element['slideelementtop'.$key];
                                        }
                                    }
                                    $responsiveness_data .= 'data-resp-top="'.esc_attr(json_encode($temp_top)).'" ';
                                }
                            }

                            switch ($slide_element['slideelementtype']) {
                                case 'text':
                                    $class_string .= ' edgtf-slide-element-text';
                                    $text_options = $slide_element['slideelementtextoptions'];
                                    $text_style_tag = '';

                                    if ($slide_element['slideelementtextwidth'] != '') {
                                        $data_string .= 'data-width="'.esc_attr($slide_element['slideelementtextwidth']).'" ';
                                        $style_string .= 'width: '.esc_attr($slide_element['slideelementtextwidth']).'%;';
                                    }
                                    else {
                                        $data_string .= 'data-width="100" ';
                                        $style_string .= 'width: 100%;';
                                    }
                                    if ($slide_element['slideelementtextheight'] != '') {
                                        $data_string .= 'data-height="'.esc_attr($slide_element['slideelementtextheight']).'" ';
                                        $style_string .= 'height: '.esc_attr($slide_element['slideelementtextheight']).'%;';
                                    }
                                    if ($slide_element['slideelementtexttop'] != '') {
                                        $data_string .= 'data-top="'.esc_attr($slide_element['slideelementtexttop']).'" ';
                                        $style_string .= 'top: '.esc_attr($slide_element['slideelementtexttop']).'%;';
                                    }
                                    if ($slide_element['slideelementtextleft'] != '') {
                                        $data_string .= 'data-left="'.esc_attr($slide_element['slideelementtextleft']).'" ';
                                        $style_string .= 'left: '.esc_attr($slide_element['slideelementtextleft']).'%;';
                                    }

                                    $class_string .= ' '.'edgtf-slide-element-text-'.$slide_element['slideelementtextstyle'];
                                    
                                    if ($text_options == 'advanced') {
                                        if ($slide_element['slideelementfontcolor'] != '') {
                                            $style_string .= 'color: '.esc_attr($slide_element['slideelementfontcolor']).';';
                                        }
                                        if ($slide_element['slideelementfontsize'] != '') {
                                            $style_string .= 'font-size: '.esc_attr($slide_element['slideelementfontsize']).'px;';
                                            $data_string .= 'data-default-font-size="'.esc_attr($slide_element['slideelementfontsize']).'" ';
                                        }
                                        if ($slide_element['slideelementlineheight'] != '') {
                                            $style_string .= 'line-height: '.esc_attr($slide_element['slideelementlineheight']).'px;';
                                            $data_string .= 'data-default-line-height="'.esc_attr($slide_element['slideelementlineheight']).'" ';
                                        }
                                        if ($slide_element['slideelementletterspacing'] != '') {
                                            $style_string .= 'letter-spacing: '.esc_attr($slide_element['slideelementletterspacing']).'px;';
                                            $data_string .= 'data-default-letter-spacing="'.esc_attr($slide_element['slideelementletterspacing']).'" ';
                                        }
                                        if ($slide_element['slideelementfontfamily'] != '') {
                                            $style_string .= 'font-family: '.str_replace('+', ' ', $slide_element['slideelementfontfamily']).';';
                                        }
                                        if ($slide_element['slideelementfontstyle'] != '') {
                                            $style_string .= 'font-style: '.esc_attr($slide_element['slideelementfontstyle']).';';
                                        }
                                        if ($slide_element['slideelementfontweight'] != '') {
                                            $style_string .= 'font-weight: '.esc_attr($slide_element['slideelementfontweight']).';';
                                        }
                                        if ($slide_element['slideelementtexttransform'] != '') {
                                            $style_string .= 'text-transform: '.esc_attr($slide_element['slideelementtexttransform']).';';
                                        }

                                        if ($slide_element['slideelementtextbgndcolor'] != '') {
                                            $slide_element_bg_color = esc_attr($slide_element['slideelementtextbgndcolor']);
                                            if ($slide_element['slideelementtextbgndtransparency'] != '') {
                                                $slide_element_bg_transparency = esc_attr($slide_element['slideelementtextbgndtransparency']);
                                            } else {
                                                $slide_element_bg_transparency = 1;
                                            }
                                            $style_string .= 'background-color: '.walker_edge_rgba_color($slide_element_bg_color, $slide_element_bg_transparency).';';
                                        }

                                        if ($slide_element['slideelementtextborderthickness'] != '') {
                                            $style_string .= 'border-width: '.esc_attr($slide_element['slideelementtextborderthickness']).'px;';
                                        }
                                        if ($slide_element['slideelementtextborderstyle'] != '') {
                                            $style_string .= 'border-style: '.esc_attr($slide_element['slideelementtextborderstyle']).';';
                                        }
                                        if ($slide_element['slideelementtextbordercolor'] != '') {
                                            $style_string .= 'border-color: '.esc_attr($slide_element['slideelementtextbordercolor']).';';
                                        }
                                    }

                                    $class_string .= ' '.'edgtf-slide-element-responsive-text';
                                    $data_string .= $responsiveness_data;

                                    // Overriding styles in case of non-custom positioning
                                    if ($element_positions != 'custom') {
                                        $style_string .= 
                                            'width: 100%;' .
                                            'height: auto;'
                                        ;
                                    }

                                    $slide_element_html .= 
                                        '<div class="'.$class_string.'" '.$data_string.' style="'.$style_string.'"><div class="edgtf-slide-element-inner" style="'.$inner_style_string.'">' .
                                            ($slide_element['slideelementtextlink'] != '' ? '<a class="edgtf-slide-element-wrapper-link inheriting" style="'.($slide_element['slideelementtextlinkhovercolor'] != '' ? 'color: '.esc_attr($slide_element['slideelementtextlinkhovercolor']) : '').'" href="'.esc_url($slide_element['slideelementtextlink']).'" target="'.esc_attr($slide_element['slideelementtexttarget']).'">' : '') .
                                                '<span>'.esc_html($slide_element['slideelementtext']).'</span>'.
                                            ($slide_element['slideelementtextlink'] != '' ? '</a>' : '').
                                        '</div></div>'
                                    ;
                                break;

                                case 'image':
                                    $class_string .= ' edgtf-slide-element-image';

                                    if ($slide_element['slideelementimagewidth'] != '') {
                                        $data_string .= 'data-width="'.esc_attr($slide_element['slideelementimagewidth']).'" ';
                                        $style_string .= 'width: '.esc_attr($slide_element['slideelementimagewidth']).'%;';
                                    }
                                    if ($slide_element['slideelementimageheight'] != '') {
                                        $data_string .= 'data-height="'.esc_attr($slide_element['slideelementimageheight']).'" ';
                                        $style_string .= 'height: '.esc_attr($slide_element['slideelementimageheight']).'%;';
                                    }
                                    if ($slide_element['slideelementimagetop'] != '') {
                                        $data_string .= 'data-top="'.esc_attr($slide_element['slideelementimagetop']).'" ';
                                        $style_string .= 'top: '.esc_attr($slide_element['slideelementimagetop']).'%;';
                                    }
                                    if ($slide_element['slideelementimageleft'] != '') {
                                        $data_string .= 'data-left="'.esc_attr($slide_element['slideelementimageleft']).'" ';
                                        $style_string .= 'left: '.esc_attr($slide_element['slideelementimageleft']).'%;';
                                    }

                                    if ($slide_element['slideelementimageborderthickness'] != '') {
                                        $style_string .= 'border-width: '.esc_attr($slide_element['slideelementimageborderthickness']).'px;';
                                    }
                                    if ($slide_element['slideelementimageborderstyle'] != '') {
                                        $style_string .= 'border-style: '.esc_attr($slide_element['slideelementimageborderstyle']).';';
                                    }
                                    if ($slide_element['slideelementimagebordercolor'] != '') {
                                        $style_string .= 'border-color: '.esc_attr($slide_element['slideelementimagebordercolor']).';';
                                    }

                                    $class_string .= ' '.'edgtf-slide-element-responsive-image';
                                    $data_string .= $responsiveness_data;

                                    // Overriding attributes in case of non-custom positioning
                                    if ($element_positions != 'custom') {
                                        $style_string .= 
                                            'width: 100%;' .
                                            'height: auto;'
                                        ;
                                    }


                                    if ($slide_element['slideelementimageuploadwidth'] != '') {
                                        $data_string .= 'data-upload-width="'.esc_attr($slide_element['slideelementimageuploadwidth']).'" ';
                                    }
                                    if ($slide_element['slideelementimageuploadheight'] != '') {
                                        $data_string .= 'data-upload-height="'.esc_attr($slide_element['slideelementimageuploadheight']).'" ';
                                    }

                                    $slide_element_html .= 
                                        '<div class="'.$class_string.' '.($element_positions.'-image').'" '.$data_string.' style="'.$style_string.'"><div class="edgtf-slide-element-inner" style="'.$inner_style_string.'">'.
                                            ($slide_element['slideelementimagelink'] != '' ? '<a class="edgtf-slide-element-wrapper-link inheriting" href="'.esc_url($slide_element['slideelementimagelink']).'" target="'.esc_attr($slide_element['slideelementimagetarget']).'">' : '') .
                                                '<img alt="" '. (($element_positions != 'custom' && $slide_element['slideelementimagewidth'] != '') ? 'style="width: '.esc_attr($slide_element['slideelementimagewidth']).'%" ' : '') .'src="'.(isset($slide_element['slideelementimageurl']) ? $slide_element['slideelementimageurl'] : '').'">'.
                                            ($slide_element['slideelementimagelink'] != '' ? '</a>' : '').
                                        '</div></div>'
                                    ;
                                break;

                                case 'button':
                                    $class_string .= ' edgtf-slide-element-button';
                                    $button_params = array();

                                    if ($slide_element['slideelementbuttonsize'] != '') {
                                        $button_params['size'] = $slide_element['slideelementbuttonsize'];
                                    }
                                    if ($slide_element['slideelementbuttontype'] != '') {
                                        $button_params['type'] = $slide_element['slideelementbuttontype'];
                                    }
                                    if ($slide_element['slideelementbuttontext'] != '') {
                                        $button_params['text'] = $slide_element['slideelementbuttontext'];
                                    }
                                    if ($slide_element['slideelementbuttonlink'] != '') {
                                        $button_params['link'] = $slide_element['slideelementbuttonlink'];
                                    }
                                    if ($slide_element['slideelementbuttontarget'] != '') {
                                        $button_params['target'] = $slide_element['slideelementbuttontarget'];
                                    }
                                    if ($slide_element['slideelementbuttonfontcolor'] != '') {
                                        $button_params['color'] = $slide_element['slideelementbuttonfontcolor'];
                                    }
                                    if ($slide_element['slideelementbuttonfonthovercolor'] != '') {
                                        $button_params['hover_color'] = $slide_element['slideelementbuttonfonthovercolor'];
                                    }
                                    if ($slide_element['slideelementbuttonbgndcolor'] != '') {
                                        $button_params['background_color'] = $slide_element['slideelementbuttonbgndcolor'];
                                    }
                                    if ($slide_element['slideelementbuttonbgndhovercolor'] != '') {
                                        $button_params['hover_background_color'] = $slide_element['slideelementbuttonbgndhovercolor'];
                                    }
                                    if ($slide_element['slideelementbuttonbordercolor'] != '') {
                                        $button_params['border_color'] = $slide_element['slideelementbuttonbordercolor'];
                                    }
                                    if ($slide_element['slideelementbuttonborderhovercolor'] != '') {
                                        $button_params['hover_border_color'] = $slide_element['slideelementbuttonborderhovercolor'];
                                    }
                                    if ($slide_element['slideelementbuttonfontsize'] != '') {
                                        $button_params['font_size'] = $slide_element['slideelementbuttonfontsize'];
                                        $data_string .= 'data-default-font-size="'.esc_attr($slide_element['slideelementbuttonfontsize']).'" ';
                                    }
                                    if ($slide_element['slideelementbuttonfontweight'] != '') {
                                        $button_params['font_weight'] = $slide_element['slideelementbuttonfontweight'];
                                    }
                                    if ($slide_element['slideelementbuttoniconpack'] != '') {
                                        $button_params['icon_pack'] = $slide_element['slideelementbuttoniconpack'];
                                        if ($button_params['icon_pack'] != 'no_icon') {
                                            $iconPackName = walker_edge_icon_collections()->getIconCollectionParamNameByKey($button_params['icon_pack']);
                                            if ($slide_element['slideelementbuttonicon_'.$button_params['icon_pack']] != '') {
                                                $button_params[$iconPackName] = $slide_element['slideelementbuttonicon_'.$button_params['icon_pack']];
                                            }
                                        }
                                    }

                                    if ($element_positions != 'custom' && $slide_element['slideelementbuttoninline'] == 'yes') {
                                        $class_string .= ' '.'edgtf-slide-element-button-inline';
                                    }

                                    if ($slide_element['slideelementbuttontop'] != '') {
                                        $data_string .= 'data-top="'.esc_attr($slide_element['slideelementbuttontop']).'" ';
                                        $style_string .= 'top: '.esc_attr($slide_element['slideelementbuttontop']).'%;';
                                    }
                                    if ($slide_element['slideelementbuttonleft'] != '') {
                                        $data_string .= 'data-left="'.esc_attr($slide_element['slideelementbuttonleft']).'" ';
                                        $style_string .= 'left: '.esc_attr($slide_element['slideelementbuttonleft']).'%;';
                                    }

                                    $class_string .= ' '.'edgtf-slide-element-responsive-button';
                                    $data_string .= $responsiveness_data;

                                    $slide_element_html .= '<div class="'.$class_string.'" '.$data_string.' style="'.$style_string.'"><div class="edgtf-slide-element-inner" style="'.$inner_style_string.'">'.walker_edge_get_button_html($button_params).'</div></div>';
                                break;

                                case 'section-link':
                                    $class_string .= ' edgtf-slide-element-section-link';
                                    $responsiveness_class = '';
                                    $data_string .= 'data-' . $data_start_amount . '="' . $data_start_custom_style . '" ';
                                    $data_string .= 'data-' . $data_end_amount . '="' . $data_end_custom_style . '" ';

                                    $responsiveness_class .= 'edgtf-slide-element-responsive-text';

                                    $slide_bottom_html .= 
                                        '<div class="'.$class_string.'" '.$data_string.' style="'.$style_string.'"><div class="edgtf-slide-element-inner" style="'.$inner_style_string.'">'.
                                            '<div class="edgtf-slide-anchor-holder">'.
                                                '<a class="edgtf-slide-anchor-button edgtf-anchor '.$responsiveness_class.'" '.$responsiveness_data.'href="' . esc_url($slide_element['slideelementsectionlinkanchor']) . '">'.
                                                    '<span class="edgtf-scroll-text '.$responsiveness_class.'" '.$responsiveness_data.'>'.esc_html($slide_element['slideelementsectionlinktext']).'</span>' .
                                                '</a>'.
                                            '</div>' .
                                        '</div></div>'
                                    ;
                                break;
                            }
                            $html .= $slide_element_html;
                        }

                        $no++;
                    }

                    $html .=
                                '</div>' . // closing .edgtf-slider-elements-holder-frame
                            '</div>' . // closing .edgtf-slider-elements-container-inner
                        '</div>' // closing .edgtf-slider-elements-container
                    ;

                    $html .= $slide_bottom_html;

                    /* Slide elements insertion - end */

                    $html .= '</div>'; //close div.item

                    $postCount++;
                endwhile;
            else:
                $html .= esc_html__('Sorry, no slides matched your criteria.', 'edgt_core');
            endif;
            wp_reset_query();

            $html .= '</div>'; //close div.carousel-inner
            if ($found_slides > 1) {
                $show_navigation_circles = 'yes';
                if (isset($slider_meta['show_navigation_circles']) && $slider_meta['show_navigation_circles'] != '') {
                    $show_navigation_circles = $slider_meta['show_navigation_circles'];
                }
                $navigation_circles_position = 'edgtf-position-right';
                if (isset($slider_meta['navigation_circles_position']) && $slider_meta['navigation_circles_position'] !== '') {
                    $navigation_circles_position = 'edgtf-position-'.$slider_meta['navigation_circles_position'];
                }
	            $navigation_circles_skin = '';
                if (isset($slider_meta['navigation_circles_skin']) && $slider_meta['navigation_circles_skin'] !== '') {
	                $navigation_circles_skin = 'edgtf-'.$slider_meta['navigation_circles_skin'].'-skin';
                }
                if ($show_navigation_circles == "yes") {

                    $html .= '<ol class="carousel-indicators '.esc_attr($navigation_circles_position).' '.esc_attr($navigation_circles_skin).'" data-start="opacity: 1;" data-300="opacity:0;">';

                    query_posts($args);
                    if (have_posts()) : $postCount = 0;
                        while (have_posts()) : the_post();

                            $html .= '<li data-target="#edgtf-' . esc_attr($slider) . '" data-slide-to="' . esc_attr($postCount) . '"';
                            if ($postCount == 0) {
                                $html .= ' class="active"';
                            }
                            $html .= '></li>';

                            $postCount++;
                        endwhile;
                    else:
                        $html .= esc_html__('Sorry, no posts matched your criteria.', 'edgt_core');
                    endif;

                    wp_reset_query();
                    $html .= '</ol>';
                }

                $show_navigation_arrows = 'no';
                if (isset($slider_meta['show_navigation_arrows']) && $slider_meta['show_navigation_arrows'] !== '') {
                    $show_navigation_arrows = $slider_meta['show_navigation_arrows'];
                }
                $show_custom_navigation_arrows = 'yes';
                if (isset($slider_meta['show_custom_navigation_arrows']) && $slider_meta['show_custom_navigation_arrows'] !== '') {
                    $show_custom_navigation_arrows = $slider_meta['show_custom_navigation_arrows'];
                }
	            $navigation_arrows_skin = '';
	            if (isset($slider_meta['navigation_arrows_skin']) && $slider_meta['navigation_arrows_skin'] !== '') {
		            $navigation_arrows_skin = 'edgtf-'.$slider_meta['navigation_arrows_skin'].'-skin';
	            }
                if ($show_navigation_arrows === 'yes' && $show_custom_navigation_arrows !== 'yes') {

                    $html .= '<div class="edgtf-controls-holder">';

                    $html .= '<a class="left carousel-control edgtf-default-nav '.esc_attr($navigation_arrows_skin).'" style="'.$slider_arrows_padding.' '. $slider_arrows_margin_left .'" href="#edgtf-' . esc_attr($slider) . '" data-slide="prev" data-start="opacity: 1;" data-300="opacity:0;">';
                        $html .= '<span class="edgtf-prev-nav">';
                            $html .= '<span class="edgtf-slider-icon icon-arrows-left"></span>';
                            $html .= '<span class="edgtf-slider-nav-label">'.esc_html__('PREV', 'edgt_core').'</span>';
                        $html .= '</span>';
                    $html .= '</a>';

                    $html .= '<a class="right carousel-control edgtf-default-nav '.esc_attr($navigation_arrows_skin).'" style="'.$slider_arrows_padding.' '. $slider_arrows_margin_right .'" href="#edgtf-' . esc_attr($slider) . '" data-slide="next" data-start="opacity: 1;" data-300="opacity:0;">';
                        $html .= '<span class="edgtf-next-nav">';
                            $html .= '<span class="edgtf-slider-nav-label">'.esc_html__('NEXT', 'edgt_core').'</span>';
                            $html .= '<span class="edgtf-slider-icon icon-arrows-right"></span>';
                        $html .= '</span>';
                    $html .= '</a>';

                    $html .= '</div>'; //close of div.edgtf-controls-holder
                }
                if ($show_custom_navigation_arrows === 'yes') {

                    $html .= '<div class="edgtf-controls-holder">';

                    $html .= '<a class="left carousel-control edgtf-custom-nav '.esc_attr($navigation_arrows_skin).'" style="'.$slider_arrows_padding.' '. $slider_arrows_margin_left .'" href="#edgtf-' . esc_attr($slider) . '" data-slide="prev" data-start="opacity: 1;" data-300="opacity:0;">';
                        $html .= '<span class="edgtf-prev-nav">';
                            $html .= '<span class="edgtf-slider-icon"></span>';
                            $html .= '<span class="edgtf-slider-nav-label">'.esc_html__('PREV', 'edgt_core').'</span>';
                        $html .= '</span>';
                    $html .= '</a>';

                    $html .= '<a class="right carousel-control edgtf-custom-nav '.esc_attr($navigation_arrows_skin).'" style="'.$slider_arrows_padding.' '. $slider_arrows_margin_right .'" href="#edgtf-' . esc_attr($slider) . '" data-slide="next" data-start="opacity: 1;" data-300="opacity:0;">';
                        $html .= '<span class="edgtf-next-nav">';
                            $html .= '<span class="edgtf-slider-nav-label">'.esc_html__('NEXT', 'edgt_core').'</span>';
                            $html .= '<span class="edgtf-slider-icon"></span>';
                        $html .= '</span>';
                    $html .= '</a>';

                    $html .= '</div>'; //close of div.edgtf-controls-holder
                }
            }
            $html .= '</div>'; //close of div.carousel
        }

        return $html;
    }

    /**
     * Function that returns slider item pading
     **/
    function walker_edge_get_slider_item_padding() {
        return apply_filters('walker_edge_slider_item_padding', 0);
    }

    /**
     * Function that returns slider navigation pading
     **/
    function walker_edge_get_slider_navigation_padding() {
        return apply_filters('walker_edge_slider_navigation_padding', 0);
    }
}
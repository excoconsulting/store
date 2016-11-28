<?php
/**
  * Plugin Name: WooCommerce Cart Add-Ons
  * Plugin URI: http://woothemes.com/woocommerce
  * Description: Adds the ability to define and display any product or variation based upon the products added to the cart by the user. Use widgets to display these recommendations in the sidebar, use <?php if (function_exists('sfn_display_cart_addons')) sfn_display_cart_addons(); ?> to show these recommendation anywhere in your theme, or select [display-addons] shortcode.
  * Version: 1.5.12
  * Author: 75nineteen Media LLC
  * Author URI: http://www.75nineteen.com
  * Text domain: sfn_cart_addons

  * Copyright 2016 75nineteen Media LLC.  (email : scott@75nineteen.com)
  * 
  * This program is free software: you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation, either version 3 of the License, or
  * (at your option) any later version.
  * 
  * This program is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU General Public License for more details.
  * 
  * You should have received a copy of the GNU General Public License
  * along with this program.  If not, see <http://www.gnu.org/licenses/>.
  */

/**
 * Localisation
 **/
load_plugin_textdomain( 'sfn_cart_addons', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) )
	require_once( 'woo-includes/woo-functions.php' );

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '3a8ef25334396206f5da4cf208adeda3', '18717' );

class SFN_Cart_Addons {

    public function __construct() {
        // activation
        register_activation_hook(__FILE__, array($this, 'activate'));

        require 'widget.php';
        add_action( 'widgets_init', create_function( '', 'register_widget( "cart_addons_widget" );' ) );

        // menu
        add_action('admin_menu', array($this, 'menu'), 20);

        add_filter('woocommerce_screen_ids', array($this, 'register_screen_id') );

        // help format variations
        add_filter('post_class', array($this, 'post_class'));
        add_filter('the_title', array($this, 'the_title'), 10, 2);
        add_filter('the_permalink', array($this, 'the_permalink'));

        // settings styles and scripts
        add_action( 'admin_enqueue_scripts', array($this, 'settings_scripts'));
        add_action( 'admin_post_sfn_cart_addons_update_settings', array($this, 'update_settings'));

        add_action( 'wp_ajax_sfn_product_is_variable', array($this, 'ajax_product_is_variable') );

        // cart page
        add_action('woocommerce_after_cart_table', array($this, 'cart_display_addons'), 20);

        // shortcode
        add_shortcode('display-addons', array($this, 'sc_display_addons'));
    }

    public function activate() {
        $settings = get_option( 'sfn_cart_addons', false );

        if ( !$settings ) {
            $settings = array(
                'header_title'      => __('Product Add-ons', 'sfn_cart_addons'),
                'default_addons'    => array()
            );

            update_option( 'sfn_cart_addons', $settings );
        }

        if ( ! get_option('sfn_cart_addons_products', false) ) {
            update_option( 'sfn_cart_addons_products', array() );
        }

        if ( ! get_option('sfn_cart_addons_categories', false) ) {
            update_option( 'sfn_cart_addons_categories', array() );
        }
    }

    public function menu() {
        add_submenu_page('woocommerce', __('Cart Add-Ons', 'sfn_cart_addons'),  __('Cart Add-Ons', 'sfn_cart_addons') , 'manage_woocommerce', 'sfn-cart-addons', array($this, 'settings'));
    }

    public function register_screen_id( $screens ) {
        $screens[] = 'woocommerce_page_sfn-cart-addons';

        return $screens;
    }

    public function settings_scripts() {
        global $woocommerce;

        if ( isset($_GET['page']) && $_GET['page'] == 'sfn-cart-addons' ) {
            wp_enqueue_style('sfn-cart-addons', plugins_url('assets/css/settings.css', __FILE__));


            // WC < 2.3
            if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '<=' ) ) {
                woocommerce_admin_scripts();
            }

            if ( version_compare(WOOCOMMERCE_VERSION, '2.3', '<') ) {
                wp_enqueue_script( 'woocommerce_admin' );
                wp_enqueue_script('farbtastic');
                wp_enqueue_script( 'ajax-chosen' );
                wp_enqueue_script( 'chosen' );

                wp_enqueue_style('chosen', plugins_url() .'/woocommerce/assets/css/chosen.css');
            }

            if ( !wp_script_is( 'sfn-product-search', 'registered' ) ) {
                wp_register_script( 'sfn-product-search', plugins_url( 'assets/js/product-search.js', __FILE__ ), array('jquery') );
            }

            wp_enqueue_script( 'sfn-product-search' );
            wp_localize_script( 'sfn-product-search', 'sfn_product_search', array(
                'security' => wp_create_nonce("search-products")
            ) );

            $cart_addons_settings = array(
                'all_categories_used'       => __('All categories have been used', 'sfn_cart_addons'),
                'search_products'           => __('Search for a product &hellip;', 'sfn_cart_addons')
            );

            wp_enqueue_script( 'cart_addons_settings', plugins_url('assets/js/settings.js', __FILE__), array('jquery') );
            wp_localize_script( 'cart_addons_settings', 'cart_addons_settings', $cart_addons_settings );

            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_script( 'jquery-ui-core', null, array('jquery') );

            ?>
            <style type="text/css">
            .chzn-choices li.search-field .default {
                width: auto !important;
            }

            .chzn-container-multi .chzn-choices .search-field input { height: auto !important; }
            </style>
            <?php

            wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );
            wp_enqueue_style( 'jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/themes/base/jquery-ui.css' );
        }
    }

    public function settings() {
        include dirname(__FILE__) .'/settings.php';
    }

    public function update_settings() {
        global $wpdb, $woocommerce;

        $_POST = array_map('stripslashes_deep', $_POST);

        $settings   = get_option('sfn_cart_addons', array());
        $default    = array();

        if ( isset($_POST['header_title']) ) {
            $settings['header_title'] = $_POST['header_title'];
        }

        if ( isset($_POST['upsell_number']) ) {
            $settings['upsell_number'] = (int)$_POST['upsell_number'];
        }

        if ( isset($_POST['default_products']) && is_array($_POST['default_products']) ) {
            if ( !empty($_POST['default_products']) ) {
                $default_products = $this->csv_to_array( $_POST['default_products'] );

                foreach ( $default_products as $product_id ) {
                    $default[] = $product_id;
                }
            }
        }
        $settings['default_addons'] = array_filter( $default );
        update_option( 'sfn_cart_addons', $settings );

        // delete all entries
        $product_addons     = array();
        $category_addons    = array();

        if ( isset($_POST['category']) && is_array($_POST['category']) ) {
            $categories = $_POST['category'];

            foreach ( $_POST['category_priorities'] as $idx => $key ) {
                $category_id                = $categories[ $key ];
                $category_products[ $key ]  = $this->csv_to_array( $_POST['category_products'][ $key ] );

                // make sure there are products selected
                if ( !empty($category_products[ $key ]) ) {
                    // insert
                    $addon = array(
                        'category_id'   => $category_id,
                        'priority'      => $idx+1,
                        'products'      => array()
                    );

                    foreach ( $category_products[ $key ] as $product_id ) {
                        $addon['products'][] = $product_id;
                    }

                    $category_addons[] = $addon;
                }
            }
        }

        $products = isset($_POST['product']) ? $_POST['product'] : array();

        if ( !empty( $_POST['product_priorities'] ) ) {
            foreach ( $_POST['product_priorities'] as $idx => $key ) {
                $product_id                 = $products[ $key ];
                $include_variations         = (isset($_POST['product_include_variations'][ $key ])) ? $_POST['product_include_variations'][ $key ] : 0;
                $product_products[ $key ]   = $this->csv_to_array( $_POST['product_products'][ $key ] );

                // make sure there are products selected
                if ( !empty($product_products[ $key ]) ) {
                    // insert
                    $addon = array(
                        'product_id'            => $product_id,
                        'include_variations'    => (bool)$include_variations,
                        'priority'              => $idx+1,
                        'products'              => array()
                    );

                    foreach ( $product_products[ $key ] as $product_id ) {
                        $addon['products'][] = $product_id;
                    }

                    $product_addons[] = $addon;
                }
            }
        }

        $tmp = array();
        foreach ($category_addons as $key => $row) {
            $tmp[$key]  = $row['priority'];
        }
        array_multisort($tmp, SORT_ASC, $category_addons);

        $tmp = array();
        foreach ($product_addons as $key => $row) {
            $tmp[$key]  = $row['priority'];
        }
        array_multisort($tmp, SORT_ASC, $product_addons);

        update_option( 'sfn_cart_addons_categories', $category_addons );
        update_option( 'sfn_cart_addons_products', $product_addons );

        wp_redirect( 'admin.php?page=sfn-cart-addons&updated=1' );
        exit;
    }

    /**
     * AJAX method to check if the given $_GET['product_id'] is a variable product
     */
    public function ajax_product_is_variable() {
        ob_start();

        $is_variable    = false;
        $type           = '';
        $product_id     = absint( $_GET['product_id'] );
        $product        = (function_exists('wc_get_product')) ? wc_get_product( $product_id ) : new WC_Product( $product_id );

        if ( $product ) {
            $type = $product->product_type;
        }

        if ( $type == 'variable' ) {
            $is_variable = true;
        }

        ob_end_clean();

        die(json_encode(array('is_variable' => $is_variable)));

    }

    /**
     * Used for backwards compatibility with the switch from Chosen to Select2.
     * Converts array{0: "1,2,3"} to array{0: "1", 1: "2", 2: "3"}
     *
     * @param array $array
     * @return array
     */
    public function csv_to_array( $array ) {
        $new_array = $array;

        if ( empty( $array ) ) {
            return $array;
        }

        if ( count( $array ) == 1 && strpos( $array[0], ',' ) !== false ) {
            $new_array = explode( ',', $array[0] );
        }

        return $new_array;
    }

    public function cart_display_addons() {
        $this->display_addons(null, 'loop', 0, false);
    }

    public function display_addons( $length = null, $display_mode = 'loop', $add_to_cart = 0, $return = true ) {
        global $wpdb, $woocommerce;

        $add_to_cart    = (bool)$add_to_cart;
        $settings       = get_option( 'sfn_cart_addons' );
        $addon          = false;
        $args           = false;
        $addon_ids      = array();
        $contents       = $woocommerce->cart->cart_contents;

        if (! is_null($length) && !empty($length) ) {
            $max = $length;
        } else {
            $max = (isset($settings['upsell_number'])) ? (int)$settings['upsell_number'] : false;
        }

        if ( empty($contents) ) {
            return;
        }

        // extract all the product ids from the cart
        $products_in_cart = array();

        foreach ( $contents as $product ) {
            $products_in_cart[] = (isset($product['variation_id']) && $product['variation_id'] > 0) ? $product['variation_id'] : $product['product_id'];
        }

        // search for product matches
        $product_addons = get_option( 'sfn_cart_addons_products', array() );

        foreach ( $product_addons as $addons ) {

            if ( isset( $addons['include_variations'] ) && $addons['include_variations'] ) {
                $product  = function_exists('wc_get_product') ? wc_get_product( $addons['product_id'] ) : new WC_Product( $addons['product_id'] );

                if ( !$product ) {
                    continue;
                }

                $children = $product->get_children();

                foreach ( $children as $child_id ) {
                    if ( in_array( $child_id, $products_in_cart ) ) {
                        foreach ( $addons['products'] as $pid ) {
                            $addon_ids[] = $pid;
                        }
                    }
                }
            } else {
                if (in_array($addons['product_id'], $products_in_cart)) {
                    foreach ( $addons['products'] as $pid ) {
                        $addon_ids[] = $pid;
                    }
                }
            }


        }

        // search for category matches
        $all_categories = array();

        foreach ( $products_in_cart as $product_id ) {
            $product_cats = $this->get_product_categories( $product_id );

            if (! empty( $product_cats ) )
                $all_categories = array_merge( $all_categories, $product_cats );
        }

        $category_addons = get_option( 'sfn_cart_addons_categories', array() );
        foreach ( $category_addons as $addons ) {
            if (in_array($addons['category_id'], $all_categories)) {
                foreach ( $addons['products'] as $pid ) {
                    $addon_ids[] = $pid;
                }
            }
        }

        // default addons
        if ( $max !== false && empty( $addon_ids ) ) {
            $default_addons = $settings['default_addons'];

            $addon_ids = $default_addons;
        }

        $args = false;
        if ( !empty($addon_ids) ) {
            // remove the products that are already in the cart

            foreach ( $addon_ids as $idx => $prod_id ) {
                if ( in_array($prod_id, $products_in_cart) ) {
                    unset($addon_ids[$idx]);
                }
            }

            if (! empty($addon_ids) ) {
                $args = array(
                    'post_type'         => array('product', 'product_variation'),
                    'post__in'          => $addon_ids,
                    'posts_per_page'    => $max
                );
            } else {
                $args = false;
            }
        }

        // no addons to display!
        if ($args === false) return;

        $loop = new WP_Query( $args );

        // output buffering, we need to return the output
        ob_start();

        if ($display_mode == 'loop') {
            do_action('woocommerce_before_shop_loop');

            ?>
            <div class="sfn-cart-addons">
                <h2><?php echo esc_html($settings['header_title']); ?></h2>
                <ul class="products sfn-cart-addons">
                    <?php
                    do_action('woocommerce_before_shop_loop_products');
                    $x = 0;

                    if ($loop->have_posts()) : while ($loop->have_posts()) : $loop->the_post(); global $product;
                        $product = sfn_get_product(get_the_ID());
                        if ( !$product || !$product->is_visible() ) {
                            continue;
                        }
                        wc_get_template('content-product.php', array('product' => $product));
                    endwhile; endif;
                    ?>

                </ul>

                <div style="clear:both; height:1px;"></div>
            </div>
            <?php
            do_action('woocommerce_after_shop_loop');
        } elseif ($display_mode == 'images') {
            ?>
            <div class="sfn-cart-addons-images">
                <ul class="products sfn-cart-addons">
                    <?php
                    $x = 0;
                    if ($loop->have_posts()) : while ($loop->have_posts()) : $loop->the_post(); global $product;
                        $product = sfn_get_product(get_the_ID());
                        if ( !$product || !$product->is_visible() ) {
                            continue;
                        }
                        echo '<li><a href="'. get_permalink($product->id) .'">'. woocommerce_get_product_thumbnail() .'</a>';
                        if ( $add_to_cart ) {
                            woocommerce_template_loop_add_to_cart();
                        }
                        echo '</li>';
                    endwhile; endif;
                    ?>
                </ul>
            </div>
            <?php
        } elseif ($display_mode == 'images_name') {
            ?>
            <div class="sfn-cart-addons-images">
                <ul class="products sfn-cart-addons">
                    <?php
                    $x = 0;
                    if ($loop->have_posts()) : while ($loop->have_posts()) : $loop->the_post(); global $product;
                        $product = sfn_get_product(get_the_ID());
                        if ( !$product || !$product->is_visible() ) {
                            continue;
                        }
                        echo '<li style="text-align:center;"><a href="'. get_permalink($product->id) .'">'. woocommerce_get_product_thumbnail() .'</a><br/><a href="'. get_permalink($product->id) .'">'. get_the_title() .'</a>';

                        if ( $add_to_cart ) {
                            woocommerce_template_loop_add_to_cart();
                        }

                        echo '</li>';
                    endwhile; endif;
                    ?>
                </ul>
            </div>
            <?php
        } elseif ($display_mode == 'images_name_price') {
            ?>
            <div class="sfn-cart-addons-images">
                <ul class="products sfn-cart-addons">
                    <?php
                    $x = 0;
                    if ($loop->have_posts()) : while ($loop->have_posts()) : $loop->the_post(); global $product;
                        $product = sfn_get_product(get_the_ID());
                        if ( !$product || !$product->is_visible() ) {
                            continue;
                        }

                        echo '<li style="text-align:center;"><a href="'. get_permalink($product->id) .'">'. woocommerce_get_product_thumbnail() .'</a><br/><a href="'. get_permalink($product->id) .'">'. get_the_title() .'</a> '. woocommerce_price( $product->get_price() );

                        if ( $add_to_cart ) {
                            woocommerce_template_loop_add_to_cart();
                        }

                        echo '</li>';
                    endwhile; endif;
                    ?>
                </ul>
            </div>
            <?php
        } elseif ($display_mode == 'names') {
            ?>
            <div class="sfn-cart-addons-names">
                <ul class="products sfn-cart-addons">
                    <?php
                    $x = 0;
                    if ($loop->have_posts()) : while ($loop->have_posts()) : $loop->the_post(); global $product;

                        $product = sfn_get_product(get_the_ID());

                        if ( !$product || !$product->is_visible() ) {
                            continue;
                        }

                        echo '<li><a href="'. get_permalink($product->id) .'">'. get_the_title() .'</a>';

                        if ( $add_to_cart ) {
                            woocommerce_template_loop_add_to_cart();
                        }

                        echo '</li>';
                    endwhile; endif;
                    ?>
                </ul>
            </div>
            <?php
        } elseif ($display_mode == 'names_price') {
            ?>
            <div class="sfn-cart-addons-names">
                <ul class="products sfn-cart-addons">
                    <?php
                    $x = 0;
                    if ($loop->have_posts()) : while ($loop->have_posts()) : $loop->the_post(); global $product;
                        $product = sfn_get_product(get_the_ID());

                        if ( !$product || !$product->is_visible() ) {
                            continue;
                        }
                        echo '<li><a href="'. get_permalink($product->id) .'">'. get_the_title() .' '. woocommerce_price($product->get_price()) .'</a>';

                        if ( $add_to_cart ) {
                            woocommerce_template_loop_add_to_cart();
                        }

                        echo '</li>';
                    endwhile; endif;
                    ?>
                </ul>
            </div>
            <?php
        }

        $content = ob_get_clean();

        // reset data
        wp_reset_postdata();

        if ( $return ) {
            return $content;
        } else {
            echo $content;
        }

    }

    public function sc_display_addons($atts) {
        extract(shortcode_atts(array(
            'length'        => 4,
            'mode'          => 'loop',
            'add_to_cart'   => 0
        ), $atts));

        return $this->display_addons($length, $mode, $add_to_cart);
    }

    public function post_class( $classes ) {
        if ( in_array('product_variation', $classes) )
            $classes[] = 'product';

        return $classes;
    }

    public function the_title( $title, $id = null ) {
        global $post;

        if ( is_null($id) ) $id = $post->ID;

        $product = sfn_get_product( $id );

        if ( isset($product->variation_id) && $product->variation_id > 0 ) {
            $attributes = $product->get_variation_attributes();
            $extra_data = ' &ndash; ' . implode( ', ', $attributes );

            $title = sprintf( __( '%s%s', 'woocommerce' ), $product->get_title(), $extra_data );
        }

        return $title;
    }

    public function the_permalink($permalink) {
        global $product;

        if ( $product && $product->product_type == 'variation' ) {
            return get_permalink( $product->id );
        }

        return $permalink;
    }

    public function get_product_categories( $product_id ) {

        $product    = (function_exists('get_product')) ? get_product( $product_id ) : new WC_Product( $product_id );
        $parent_id  = (isset($product->parent)) ? $product->parent->id : 0;
        $categories = array();

        // if product is a variation, use the parent product
        if ( $parent_id > 0 )
            $product_id = $parent_id;

        $terms = wp_get_post_terms( $product_id, 'product_cat' );

        if ( is_array($terms) && count($terms) > 0 ) {
            foreach ( $terms as $term ) {
                $categories[] = $term->term_id;
            }
        }

        return $categories;
    }
}
if ( is_woocommerce_active() ) {
    $sfn_cart_addons = new SFN_Cart_Addons();
}

function sfn_display_cart_addons( $length = 4, $display_mode = 'loop', $add_to_cart = 0 ) {
    global $sfn_cart_addons;

    if ( isset($sfn_cart_addons) )
        echo $sfn_cart_addons->display_addons($length, $display_mode, $add_to_cart);
}

if (! function_exists('sfn_get_product') ) {
    function sfn_get_product( $id ) {
        if ( function_exists('get_product') ) {
            return get_product( $id );
        } else {
            $product_post = get_post( $id );

            if ( ! $product_post ) return new WC_Product( $id );

            if ( $product_post->post_type == 'product_variation' ) :
                return new WC_Product_Variation( $id );
            else :
                return new WC_Product( $id );
            endif;
        }
    }
}

if (! function_exists('woocommerce_template_loop_add_to_cart') ) {
    function woocommerce_template_loop_add_to_cart() {
        global $product;

        if ( $product ) {
            $defaults = array(
                'quantity' => 1,
                'class'    => implode( ' ', array_filter( array(
                    'button',
                    'product_type_' . $product->product_type,
                    $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                    $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : ''
                ) ) )
            );

            $args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( array(), $defaults ), $product );
        }

        if ( $product->product_type == 'variation' ) {
            include 'add-to-cart.php';
        } else {
            wc_get_template( 'loop/add-to-cart.php', $args );
        }

        //include 'add-to-cart.php';
    }
}

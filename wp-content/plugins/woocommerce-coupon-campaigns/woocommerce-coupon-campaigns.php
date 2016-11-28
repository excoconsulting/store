<?php
/*
 * Plugin Name: WooCommerce Coupon Campaigns & Tracking
 * Version: 1.0.2
 * Plugin URI: http://woothemes.com/products/woocommerce-coupon-campaigns/.
 * Description: Provides the ability to group coupons into campaigns - also offers better tracking and reporting of coupons as well as a bulk coupon generation tool.
 * Author: WooThemes
 * Author URI: http://www.woothemes.com/
 * Requires at least: 3.0
 * Tested up to: 3.8.1
 *
 * @package WordPress
 * @author WooThemes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
    require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '0d1018512ffcfcca48a43da05de22647', '506329' );

if ( ! function_exists( 'wc_coupon_campaigns_tracking' ) && ! function_exists( 'wc_coupon_campaigns_tracking_reports' ) ) :

function wc_coupon_campaigns_tracking() {

    if ( is_admin() ) {
        require_once( 'classes/class-wc-coupon-campaigns.php' );

        global $wc_coupon_campaigns;
        $wc_coupon_campaigns = new WC_Coupon_Campaigns( __FILE__ );
    }
}
add_action( 'plugins_loaded', 'wc_coupon_campaigns_tracking', 0 );


function wc_coupon_campaigns_tracking_reports( $reports ) {

    $reports['coupons'] = array(
        'title'   => __( 'Coupon Campaigns', 'wc_coupon_campaigns' ),
        'reports' => array(
            'campaigns' => array(
                'title'       => __( 'Coupon Campaigns', 'wc_coupon_campaigns' ),
                'description' => '',
                'hide_title'  => true,
                'callback'    => array( 'WC_Admin_Reports', 'get_report' )
            )
        )
    );

    return $reports;
}
add_filter( 'woocommerce_admin_reports', 'wc_coupon_campaigns_tracking_reports');


function wc_coupon_campaigns_tracking_reports_path( $path, $name, $class ) {

    if ( 'WC_Report_campaigns' == $class ) {
        $dir = plugin_dir_path( __FILE__ );
        $path = $dir . "classes/class-wc-report-coupon-campaign.php";
    }

    return $path;
}
add_filter( 'wc_admin_reports_path', 'wc_coupon_campaigns_tracking_reports_path', 10, 3 );



endif;

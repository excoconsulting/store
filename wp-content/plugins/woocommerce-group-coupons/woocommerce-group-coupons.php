<?php
/**
 * woocommerce-group-coupons.php
 *
 * Copyright (c) 2013-2015 "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is provided subject to the license granted.
 * Unauthorized use and distribution is prohibited.
 * See COPYRIGHT.txt and LICENSE.txt
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Karim Rahimpur
 * @package woocommerce-groupons
 * @since woocommerce-groupons 1.0.0
 *
 * Plugin Name: WooCommerce Group Coupons
 * Plugin URI: http://www.itthinx.com/plugins/woocommerce-groupons
 * Description: Coupons for groups. Provides the option to have coupons that are restricted to group members or roles. Works with the free <a href="http://www.itthinx.com/plugins/groups/">Groups</a> plugin. <a href="http://www.itthinx.com/documentation/woocommerce-group-coupons/">Documentation</a> | <a href="http://www.itthinx.com/plugins/woocommerce-group-coupons/">Plugin page</a>
 * Version: 1.5.0 
 * Author: itthinx
 * Author URI: http://www.itthinx.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Required functions
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

// Plugin updates
woothemes_queue_update( plugin_basename( __FILE__ ), '6a8e3f1b65027f729645a0e48952cfbf', '216795' );

// Check if WooCommerce is active
if ( ! is_woocommerce_active() ) {
	return;
}

define( 'WOO_GROUPONS_PLUGIN_VERSION', '1.5.0' );
define( 'WOO_GROUPONS_FILE', __FILE__ );
define( 'WOO_GROUPONS_PLUGIN_DOMAIN', 'groups' );
define( 'WOO_GROUPONS_LOG', false );
define( 'WOO_GROUPONS_CORE_DIR', WP_PLUGIN_DIR . '/woocommerce-group-coupons' );
define( 'WOO_GROUPONS_CORE_LIB', WOO_GROUPONS_CORE_DIR . '/lib/core' );
define( 'WOO_GROUPONS_ADMIN_LIB', WOO_GROUPONS_CORE_DIR . '/lib/admin' );
define( 'WOO_GROUPONS_VIEWS_LIB', WOO_GROUPONS_CORE_DIR . '/lib/views' );
define( 'WOO_GROUPONS_PLUGIN_URL', WP_PLUGIN_URL . '/woocommerce-group-coupons' );
require_once( WOO_GROUPONS_CORE_LIB . '/class-woocommerce-groupons.php');

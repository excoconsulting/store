<?php
/**
 * woocommerce-product-search.php
 *
 * Copyright (c) 2014-2015 "kento" Karim Rahimpur www.itthinx.com
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
 * @author itthinx
 * @package woocommerce-product-search
 * @since 1.0.0
 *
 * Plugin Name: WooCommerce Product Search
 * Plugin URI: http://www.itthinx.com/plugins/woocommerce-product-search
 * Description: Enhanced product search for WooCommerce.
 * Version: 1.6.0
 * Author: itthinx
 * Author URI: http://www.itthinx.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Required functions
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

// Plugin updates
woothemes_queue_update( plugin_basename( __FILE__ ), 'c84cc8ca16ddac3408e6b6c5871133a8', '512174' );

// Check if WooCommerce is active
if ( ! is_woocommerce_active() ) {
	return;
}

define( 'WOO_PS_PLUGIN_VERSION', '1.6.0' );
define( 'WOO_PS_PLUGIN_DOMAIN', 'woocommerce-product-search' );
define( 'WOO_PS_FILE', __FILE__ );
if ( !defined( 'WOO_PS_LOG' ) ) {
	define( 'WOO_PS_LOG', false );
}
define( 'WOO_PS_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'WOO_PS_CORE_LIB', WOO_PS_CORE_DIR . 'core' );
define( 'WOO_PS_ADMIN_LIB', WOO_PS_CORE_DIR . 'admin' );
define( 'WOO_PS_VIEWS_LIB', WOO_PS_CORE_DIR . 'views' );
define( 'WOO_PS_PLUGIN_URL', plugins_url( 'woocommerce-product-search' ) );

require_once( WOO_PS_CORE_LIB . '/class-woocommerce-product-search.php');

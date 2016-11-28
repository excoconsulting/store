<?php
/**
 * product-search.php
 *
 * Copyright (c) "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is provided subject to the license granted.
 * Unauthorized use and distribution is prohibited.
 * See COPYRIGHT.txt and LICENSE.txt
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This header and all notices must be kept intact.
 *
 * @author itthinx
 * @package woocommerce-product-search
 * @since 1.0.1
 */

ob_start();

// We can't use SHORTINIT as we need at least our plugin loaded and all
// related resources. Loading only the essentials could be possible
// but would be rather hackish as what is requested to be present
// would depend on how WordPress does it internally and that can change
// between versions and would make an unstable solution.
// define( 'SHORTINIT', true ); <-- not

define( 'DOING_AJAX', true );

// bootstrap WordPress
if ( !defined( 'ABSPATH' ) ) {
	$wp_load = 'wp-load.php';
	$max_depth = 100; // prevent death by depth
	while ( !file_exists( $wp_load ) && ( $max_depth > 0 ) ) {
		$wp_load = '../' . $wp_load;
		$max_depth--;
	}
	if ( file_exists( $wp_load ) ) {
		require_once $wp_load;
	}
}

if ( defined( 'ABSPATH' ) ) {
	$results = WooCommerce_Product_Search_Service::request_results();
	$ob = ob_get_clean();
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG && $ob ) {
		error_log( $ob );
	}
	echo json_encode( $results );
	exit;
} else {
	$ob = ob_get_clean();
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG && $ob ) {
		error_log( $ob );
	}
	echo json_encode( array( '' ) );
	exit;
}

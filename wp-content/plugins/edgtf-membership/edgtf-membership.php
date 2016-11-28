<?php
/**
 * Plugin Name: Edge Membership
 * Description: Plugin that adds social login and user dashboard page
 * Author: Edge Themes
 * Version: 1.0
 */

require_once 'load.php';

\EdgefMembership\Lib\ShortcodeLoader::getInstance()->load();

if ( ! function_exists( 'edgtf_membership_text_domain' ) ) {
	/**
	 * Loads plugin text domain so it can be used in translation
	 */
	function edgtf_membership_text_domain() {
		load_plugin_textdomain( 'edgtf_membership', false, EDGE_MEMBERSHIP_REL_PATH . '/languages' );
	}

	add_action( 'plugins_loaded', 'edgtf_membership_text_domain' );
}

if ( ! function_exists( 'edgtf_membership_scripts' ) ) {
	/**
	 * Loads plugin scripts
	 */
	function edgtf_membership_scripts() {

		wp_enqueue_style( 'edgtf_membership_style', plugins_url( EDGE_MEMBERSHIP_REL_PATH . '/assets/css/membership-style.min.css' ) );

		$array_deps = array(
			'underscore',
			'jquery-ui-tabs'
		);
		if ( edgtf_membership_theme_installed() ) {
			$array_deps[] = 'walker_edge_modules';
		}
		wp_enqueue_script( 'edgtf_membership_script', plugins_url( EDGE_MEMBERSHIP_REL_PATH . '/assets/js/script.min.js' ), $array_deps, false, true );
	}

	add_action( 'wp_enqueue_scripts', 'edgtf_membership_scripts' );
}
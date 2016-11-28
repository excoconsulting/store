<?php
/**
 * Admin notices handling.
 *
 * @class    WC_PB_Admin_Notices
 * @version  4.14.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_PB_Admin_Notices {

	public static $meta_box_notices = array();
	public static $admin_notices    = array();

	/**
	 * Constructor.
	 */
	public static function init() {

		add_action( 'admin_notices', array( __CLASS__, 'output_notices' ) );
		add_action( 'shutdown', array( __CLASS__, 'save_notices' ) );
	}

	/**
	 * Add an error message.
	 *
	 * @param string $text
	 */
	public static function add_notice( $text, $type, $save_notice = false ) {

		$notice = array(
			'type'    => $type,
			'content' => $text
		);

		if ( $save_notice ) {
			self::$meta_box_notices[] = $notice;
		} else {
			self::$admin_notices[] = $notice;
		}
	}

	/**
	 * Save errors to an option.
	 */
	public static function save_notices() {
		update_option( 'wc_pb_meta_box_notices', self::$meta_box_notices );
	}

	/**
	 * Show any stored error messages.
	 */
	public static function output_notices() {

		$saved_notices = maybe_unserialize( get_option( 'wc_pb_meta_box_notices', array() ) );
		$notices       = $saved_notices + self::$admin_notices;

		if ( ! empty( $notices ) ) {

			foreach ( $notices as $notice ) {
				echo '<div class="wc_pb_notice notice-' . $notice[ 'type' ] . ' notice is-dismissible">';
				echo '<p>' . wp_kses_post( $notice[ 'content' ] ) . '</p>';
				echo '</div>';
			}

			// Clear
			delete_option( 'wc_pb_meta_box_notices' );
		}
	}
}

WC_PB_Admin_Notices::init();

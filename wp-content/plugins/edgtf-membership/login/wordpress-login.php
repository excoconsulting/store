<?php
/**
 * Wordpress Users Login
 */

if ( ! function_exists( 'edgtf_membership_login_user' ) ) {
	/**
	 * Login user
	 */
	function edgtf_membership_login_user() {

		if ( empty( $_POST ) || ! isset( $_POST ) ) {
			edgtf_membership_ajax_response( 'error', esc_html__( 'All fields are empty', 'edgtf_membership' ) );
		} else {
			check_ajax_referer( 'edgtf-ajax-login-nonce', 'security' );
			$data = $_POST;

			$login = $data['login_data'];
			parse_str( $login, $login_data );

			$credentials['user_login']    = $login_data['user_login_name'];
			$credentials['user_password'] = $login_data['user_login_password'];
			$redirect_uri                 = $login_data['redirect'];

			if ( isset( $credentials['remember'] ) && $credentials['remember'] == 'forever' ) {
				$credentials['remember'] = true;
			} else {
				$credentials['remember'] = false;
			}
			$user_signon = wp_signon( $credentials, false );

			if ( is_wp_error( $user_signon ) ) {
				edgtf_membership_ajax_response( 'error', esc_html__( 'Wrong username or password.', 'edgtf_membership' ) );
			} else {
				if ( $redirect_uri == '' ) {
					$redirect_uri = edgtf_membership_get_dashboard_page_url();
					if ( $redirect_uri == '' ) {
						$redirect_uri = esc_url( home_url( '/' ) );
					}
				}
				edgtf_membership_ajax_response( 'success', esc_html__( 'Login successful, redirecting...', 'edgtf_membership' ), $redirect_uri );
			}

		}
		wp_die();
	}

	add_action( 'wp_ajax_nopriv_edgtf_membership_login_user', 'edgtf_membership_login_user' );
}

if ( ! function_exists( 'edgtf_membership_register_user' ) ) {
	/**
	 * Register new user
	 */
	function edgtf_membership_register_user() {

		if ( empty( $_POST ) || ! isset( $_POST ) ) {
			edgtf_membership_ajax_response( 'error', esc_html__( 'All fields are empty', 'edgtf_membership' ) );
		} else {
			check_ajax_referer( 'edgtf-ajax-register-nonce', 'security' );
			$data = $_POST;

			$register = $data['register_data'];
			parse_str( $register, $register_data );
			$credentials['user_login'] = $register_data['user_register_name'];
			$credentials['user_email'] = $register_data['user_register_email'];

			$user_id = username_exists( $credentials['user_login'] );

			if ( ! $user_id and email_exists( $credentials['user_email'] ) == false ) {
				$user_id = register_new_user( $credentials['user_login'], $credentials['user_email'] );
				wp_update_user( array( 'ID' => $user_id, 'role' => 'subscriber' ) );
				edgtf_membership_ajax_response( 'success', esc_html__( 'You are successfully registered. Please check your email', 'edgtf_membership' ) );
			} else {
				edgtf_membership_ajax_response( 'error', esc_html__( 'User already exists', 'edgtf_membership' ) );
			}
		}
		wp_die();
	}

	add_action( 'wp_ajax_nopriv_edgtf_membership_register_user', 'edgtf_membership_register_user' );
}

if ( ! function_exists( 'edgtf_membership_user_lost_password' ) ) {
	/**
	 * Reset user password
	 */
	function edgtf_membership_user_lost_password() {

		if ( ! function_exists( 'retrieve_password' ) ) {
			ob_start();
			include_once( ABSPATH . 'wp-login.php' );
			ob_clean();
		}
		$result = retrieve_password();
		if ( $result === true ) {
			edgtf_membership_ajax_response( 'success', esc_html__( 'We have sent you an email', 'edgtf_membership' ) );
		} else {
			edgtf_membership_ajax_response( 'error', $result->get_error_message() );
		}

		wp_die();

	}

	add_action( 'wp_ajax_nopriv_edgtf_membership_user_lost_password', 'edgtf_membership_user_lost_password' );

}
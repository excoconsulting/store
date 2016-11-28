<?php
/**
 * Plugin functions
 */

if ( ! function_exists( 'edgtf_membership_version_class' ) ) {
	/**
	 * Adds plugin version class to body
	 *
	 * @param $classes
	 *
	 * @return array
	 */
	function edgtf_membership_version_class( $classes ) {
		$classes[] = 'edgtf-social-login-' . EDGE_MEMBERSHIP_VERSION;

		return $classes;
	}

	add_filter( 'body_class', 'edgtf_membership_version_class' );
}

if ( ! function_exists( 'edgtf_membership_theme_installed' ) ) {
	/**
	 * Checks whether theme is installed or not
	 * @return bool
	 */
	function edgtf_membership_theme_installed() {
		return defined( 'EDGE_ROOT' );
	}
}

if ( ! function_exists( 'edgtf_membership_get_shortcode_template_part' ) ) {
	/**
	 * Loads Shortcode template part.
	 *
	 * @param $shortcode
	 * @param $template
	 * @param string $slug
	 * @param array $params
	 *
	 * @see walker_edge_get_template_part()
	 * @return string
	 */
	function edgtf_membership_get_shortcode_template_part( $shortcode, $template, $slug = '', $params = array() ) {

		//HTML Content from template
		$html          = '';
		$template_path = EDGE_MEMBERSHIP_ABS_PATH . '/shortcodes/' . $shortcode . '/templates';

		$temp = $template_path . '/' . $template;
		if ( is_array( $params ) && count( $params ) ) {
			extract( $params );
		}

		$template = '';

		if ( $temp !== '' ) {
			if ( $slug !== '' ) {
				$template = "{$temp}-{$slug}.php";
			}
			$template = $temp . '.php';
		}
		if ( $template ) {
			ob_start();
			include( $template );
			$html = ob_get_clean();
		}

		return $html;
	}
}

if ( ! function_exists( 'edgtf_membership_get_widget_template_part' ) ) {
	/**
	 * Loads Widget template part.
	 *
	 * @param $widget
	 * @param $template
	 * @param string $slug
	 * @param array $params
	 *
	 * @see walker_edge_get_template_part()
	 * @return string
	 */
	function edgtf_membership_get_widget_template_part( $widget, $template, $slug = '', $params = array() ) {

		//HTML Content from template
		$html          = '';
		$template_path = EDGE_MEMBERSHIP_ABS_PATH . '/widgets/' . $widget . '/templates';

		$temp = $template_path . '/' . $template;
		if ( is_array( $params ) && count( $params ) ) {
			extract( $params );
		}

		$template = '';

		if ( $temp !== '' ) {
			if ( $slug !== '' ) {
				$template = "{$temp}-{$slug}.php";
			}
			$template = $temp . '.php';
		}
		if ( $template ) {
			ob_start();
			include( $template );
			$html = ob_get_clean();
		}

		return $html;
	}
}

if ( ! function_exists( 'edgtf_membership_ajax_response' ) ) {
	/**
	 * Ajax response for login and register forms
	 *
	 * @param $status
	 * @param string $message
	 * @param string $redirect
	 * @param null $data
	 */
	function edgtf_membership_ajax_response( $status, $message = '', $redirect = '', $data = null ) {

		$response = array(
			'status'   => $status,
			'message'  => $message,
			'redirect' => $redirect,
			'data'     => $data
		);

		$response = json_encode( $response );

		exit( $response );

	}

}

if ( ! function_exists( 'edgtf_membership_ajax_response_message_holder' ) ) {
	/**
	 * Template for ajax response
	 */
	function edgtf_membership_ajax_response_message_holder() {

		$html = '<div class="edgtf-membership-response-holder clearfix"></div>';
		$html .= '<script type="text/template" class="edgtf-membership-response-template">
					<div class="edgtf-membership-response <%= messageClass %> ">
						<div class="edgtf-membership-response-message">
							<p><%= message %></p>
						</div>
					</div>
				</script>';

		echo $html;
	}

	add_action( 'edgtf_membership_action_login_ajax_response', 'edgtf_membership_ajax_response_message_holder' );

}

if(!function_exists('edgtf_membership_execute_shortcode')) {
	/**
	 * @param $shortcode_tag - shortcode base
	 * @param $atts - shortcode attributes
	 * @param null $content - shortcode content
	 *
	 * @return mixed|string
	 */
	function edgtf_membership_execute_shortcode($shortcode_tag, $atts, $content = null) {
		global $shortcode_tags;

		if(!isset($shortcode_tags[$shortcode_tag])) {
			return 'Shortcode doesn\'t exist';
		}

		if(is_array($shortcode_tags[$shortcode_tag])) {
			$shortcode_array = $shortcode_tags[$shortcode_tag];

			return call_user_func(array(
				$shortcode_array[0],
				$shortcode_array[1]
			), $atts, $content, $shortcode_tag);
		}

		return call_user_func($shortcode_tags[$shortcode_tag], $atts, $content, $shortcode_tag);
	}
}

if(!function_exists('edgtf_membership_kses_img')) {
	/**
	 * Function that does escaping of img html.
	 * It uses wp_kses function with predefined attributes array.
	 * Should be used for escaping img tags in html.
	 * Defines walker_edge_kses_img_atts filter that can be used for changing allowed html attributes
	 *
	 * @see wp_kses()
	 *
	 * @param $content string string to escape
	 * @return string escaped output
	 */
	function edgtf_membership_kses_img($content) {
		$img_atts = apply_filters('edgtf_membership_filter_kses_img_atts', array(
			'src' => true,
			'alt' => true,
			'height' => true,
			'width' => true,
			'class' => true,
			'id' => true,
			'title' => true
		));

		return wp_kses($content, array(
			'img' => $img_atts
		));
	}
}
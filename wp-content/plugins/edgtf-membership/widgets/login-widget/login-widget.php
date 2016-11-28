<?php

class EdgefMembershipLoginRegister extends WP_Widget {
	protected $params;

	public function __construct() {
		parent::__construct(
			'edgtf_login_register_widget', // Base ID
			'Edge Login',
			array( 'description' => esc_html__( 'Login and register, connect with social networks', 'edgtf_membership' ), )
		);

		$this->setParams();
	}

	protected function setParams() {
		$this->params = array(
			array(
				'name'			=> 'woocommerce_dropdown_cart_margin',
				'type'			=> 'textfield',
				'title'			=> 'Margin (top right bottom left)',
				'description'	=> 'Define margin for login widget'
			)
		);
	}

	public function getParams() {
		return $this->params;
	}

	public function widget( $args, $instance ) {

		$item_styles = '';

		if (!empty($instance['woocommerce_dropdown_cart_margin'])) {
			$item_styles = 'style="margin: ' . $instance['woocommerce_dropdown_cart_margin'].'"';
		}

		echo '<div class="widget edgtf-login-register-widget" '.$item_styles.'>';
		if ( ! is_user_logged_in() ) {
			echo '<a href="#" class="edgtf-login-opener">' . esc_html__( 'Login', 'edgtf_membership' ) . '</a>';

			add_action( 'wp_footer', array( $this, 'edgtf_membership_render_login_form' ) );

		} else {
			echo edgtf_membership_get_widget_template_part( 'login-widget', 'login-widget-template' );
		}
		echo '</div>';

	}

	public function edgtf_membership_render_login_form() {

		//Render modal with login and register forms
		echo edgtf_membership_get_widget_template_part( 'login-widget', 'login-modal-template' );
	}
}

function edgtf_membership_login_widget_load() {
	register_widget( 'EdgefMembershipLoginRegister' );
}

add_action( 'widgets_init', 'edgtf_membership_login_widget_load' );
<div class="edgtf-social-register-holder">
	<form method="post" class="edgtf-register-form">
		<fieldset>
			<div>
				<input type="text" name="user_register_name" id="user_register_name"
				       placeholder="<?php esc_html_e( 'User Name', 'edgtf_membership' ) ?>" value="" required
				       pattern=".{3,}"
				       title="<?php esc_html_e( 'Three or more characters', 'edgtf_membership' ); ?>"/>
			</div>
			<div>
				<input type="email" name="user_register_email" id="user_register_email"
				       placeholder="<?php esc_html_e( 'Email', 'edgtf_membership' ) ?>" value="" required/>
			</div>
			<div class="edgtf-register-button-holder">
				<?php
				if ( edgtf_membership_theme_installed() ) {
					echo walker_edge_get_button_html( array(
						'html_type' => 'button',
						'text'      => esc_html__( 'REGISTER', 'edgtf_membership' ),
						'type'      => 'solid'
					) );
				} else {
					echo '<button type="submit">' . esc_html__( 'REGISTER', 'edgtf_membership' ) . '</button>';
				}
				wp_nonce_field( 'edgtf-ajax-register-nonce', 'edgtf-register-security' ); ?>
			</div>
		</fieldset>
	</form>
	<?php do_action( 'edgtf_membership_action_login_ajax_response' ); ?>
</div>
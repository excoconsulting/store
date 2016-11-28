<div class="edgtf-social-login-holder">
	<form method="post" class="edgtf-login-form">
		<?php
		$redirect = '';
		if ( isset( $_GET['redirect_uri'] ) ) {
			$redirect = $_GET['redirect_uri'];
		} ?>
		<fieldset>
			<div>
				<input type="text" name="user_login_name" id="user_login_name" placeholder="<?php esc_html_e( 'User Name', 'edgtf_membership' ) ?>" value="" required pattern=".{3,}" title="<?php esc_html_e( 'Three or more characters', 'edgtf_membership' ); ?>"/>
			</div>
			<div>
				<input type="password" name="user_login_password" id="user_login_password" placeholder="<?php esc_html_e( 'Password', 'edgtf_membership' ) ?>" value="" required/>
			</div>
			<div class="edgtf-lost-pass-remember-holder clearfix">
				<span class="edgtf-login-remember">
					<input name="rememberme" value="forever" id="rememberme" type="checkbox"/>
					<label for="rememberme" class="edgtf-checbox-label"><?php esc_html_e( 'Remember me', 'edgtf_membership' ) ?></label>
				</span>	
			</div>
			<input type="hidden" name="redirect" id="redirect" value="<?php echo esc_url( $redirect ); ?>">
			<div class="edgtf-login-button-holder">
				<?php
				if ( edgtf_membership_theme_installed() ) {
					echo walker_edge_get_button_html( array(
						'html_type' => 'button',
						'text'      => esc_html__( 'LOGIN', 'edgtf_membership' ),
						'type'      => 'solid'
					) );
				} else {
					echo '<button type="submit">' . esc_html__( 'LOGIN', 'edgtf_membership' ) . '</button>';
				}
				?>
				<a href="<?php echo wp_lostpassword_url(); ?>" class="edgtf-login-action-btn" data-el="#edgtf-reset-pass-content" data-title="<?php esc_html_e( 'Lost Password?', 'edgtf_membership' ); ?>"><?php esc_html_e( 'Lost Your Password?', 'edgtf_membership' ); ?></a>
				<?php wp_nonce_field( 'edgtf-ajax-login-nonce', 'edgtf-login-security' ); ?>
			</div>
		</fieldset>
	</form>
	<?php do_action( 'edgtf_membership_action_login_ajax_response' ); ?>
</div>
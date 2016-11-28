<div class="edgtf-social-reset-password-holder">
	<form action="<?php echo site_url( 'wp-login.php?action=lostpassword' ); ?>" method="post" id="edgtf-lost-password-form" class="edgtf-reset-pass-form">
		<div>
			<input type="text" name="user_reset_password_login" class="edgtf-input-field" id="user_reset_password_login" placeholder="<?php esc_html_e( 'Enter username or email', 'edgtf_membership' ) ?>" value="" size="20" required>
		</div>
		<?php do_action( 'lostpassword_form' ); ?>
		<div class="edgtf-reset-password-button-holder">
			<?php
			if ( edgtf_membership_theme_installed() ) {
				echo walker_edge_get_button_html( array(
					'html_type' => 'button',
					'text'      => esc_html__( 'NEW PASSWORD', 'edgtf_membership' ),
					'type'      => 'solid'
				) );
			} else {
				echo '<button type="submit">' . esc_html__( 'NEW PASSWORD', 'edgtf_membership' ) . '</button>';
			}
			?>
		</div>
	</form>
	<?php do_action( 'edgtf_membership_action_login_ajax_response' ); ?>
</div>
<div class="edgtf-membership-dashboard-page">
	<h3 class="edgtf-membership-dashboard-page-title">
		<?php esc_html_e( 'Edit Profile', 'edgtf_membership' ); ?>
	</h3>
	<div>
		<form method="post" id="edgtf-membership-update-profile-form">
			<div class="edgtf-membership-input-holder">
				<label for="first_name"><?php esc_html_e( 'First Name', 'edgtf_membership' ); ?></label>
				<input class="edgtf-membership-input" type="text" name="first_name" id="first_name"
				       value="<?php echo $first_name; ?>">
			</div>
			<div class="edgtf-membership-input-holder">
				<label for="last_name"><?php esc_html_e( 'Last Name', 'edgtf_membership' ); ?></label>
				<input class="edgtf-membership-input" type="text" name="last_name" id="last_name"
				       value="<?php echo $last_name; ?>">
			</div>
			<div class="edgtf-membership-input-holder">
				<label for="email"><?php esc_html_e( 'Email', 'edgtf_membership' ); ?></label>
				<input class="edgtf-membership-input" type="email" name="email" id="email"
				       value="<?php echo $email; ?>">
			</div>
			<div class="edgtf-membership-input-holder">
				<label for="url"><?php esc_html_e( 'Website', 'edgtf_membership' ); ?></label>
				<input class="edgtf-membership-input" type="text" name="url" id="url" value="<?php echo $website; ?>">
			</div>
			<div class="edgtf-membership-input-holder">
				<label for="description"><?php esc_html_e( 'Description', 'edgtf_membership' ); ?></label>
				<input class="edgtf-membership-input" type="text" name="description" id="description"
				       value="<?php echo $description; ?>">
			</div>
			<div class="edgtf-membership-input-holder">
				<label for="password"><?php esc_html_e( 'Password', 'edgtf_membership' ); ?></label>
				<input class="edgtf-membership-input" type="password" name="password" id="password" value="">
			</div>
			<div class="edgtf-membership-input-holder">
				<label for="password2"><?php esc_html_e( 'Repeat Password', 'edgtf_membership' ); ?></label>
				<input class="edgtf-membership-input" type="password" name="password2" id="password2" value="">
			</div>
			<?php
			if ( edgtf_membership_theme_installed() ) {
				echo walker_edge_get_button_html( array(
					'text'      => esc_html__( 'UPDATE PROFILE', 'edgtf_membership' ),
					'html_type' => 'button',
					'custom_attrs' => array(
						'data-updating-text' => esc_html__('UPDATING PROFILE', 'edgtf_membership'),
						'data-updated-text' => esc_html__('PROFILE UPDATED', 'edgtf_membership'),
					)
				) );
			} else {
				echo '<button type="submit">' . esc_html__( 'UPDATE PROFILE', 'edgtf_membership' ) . '</button>';
			}
			wp_nonce_field( 'edgtf_validate_edit_profile', 'edgtf_nonce_edit_profile' )
			?>
		</form>
		<?php
		do_action( 'edgtf_membership_action_login_ajax_response' );
		?>
	</div>
</div>
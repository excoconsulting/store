<div class="edgtf-membership-dashboard-page">
	<h3 class="edgtf-membership-dashboard-page-title">
		<?php esc_html_e( 'Profile', 'edgtf_membership' ); ?>
	</h3>
	<div class="edgtf-membership-dashboard-page-content">
		<?php echo edgtf_membership_kses_img( $profile_image ); ?>
		<p>
			<span><?php esc_html_e( 'First Name', 'edgtf_membership' ); ?>:</span>
			<?php echo $first_name; ?>
		</p>
		<p>
			<span><?php esc_html_e( 'Last Name', 'edgtf_membership' ); ?>:</span>
			<?php echo $last_name; ?>
		</p>
		<p>
			<span><?php esc_html_e( 'Email', 'edgtf_membership' ); ?>:</span>
			<?php echo $email; ?>
		</p>
		<p>
			<span><?php esc_html_e( 'Desription', 'edgtf_membership' ); ?>:</span>
			<?php echo $description; ?>
		</p>
		<p>
			<span><?php esc_html_e( 'Website', 'edgtf_membership' ); ?>:</span>
			<a href="<?php echo esc_url( $website ); ?>" target="_blank"><?php echo $website; ?></a>
		</p>
	</div>
</div>

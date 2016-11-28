<?php
get_header();
if ( edgtf_membership_theme_installed() ) {
	walker_edge_get_title();
} else { ?>
	<div class="edgtf-membership-title">
		<?php the_title( '<h1>', '</h1>' ); ?>
	</div>
<?php }
?>
	<div class="edgtf-container">
		<?php do_action( 'walker_edge_after_container_open' ); ?>
		<div class="edgtf-container-inner clearfix">
			<?php if ( is_user_logged_in() ) { ?>
				<div class="edgtf-membership-dashboard-nav-holder clearfix">
					<?php
					//Include dashboard navigation
					echo edgtf_membership_get_dashboard_template_part( 'navigation' );
					?>
				</div>
				<div class="edgtf-membership-dashboard-content-holder">
					<?php echo edgtf_membership_get_dashboard_pages(); ?>
				</div>
			<?php } else { ?>
				<div class="edgtf-login-register-content">
					<ul>
						<li>
							<a href="#edgtf-login-content"><?php esc_html_e( 'Login', 'edgtf_membership' ); ?></a>
						</li>
						<li>
							<a href="#edgtf-register-content"><?php esc_html_e( 'Register', 'edgtf_membership' ); ?></a>
						</li>
						<li>
							<a href="#edgtf-reset-pass-content"><?php esc_html_e( 'Reset Password', 'edgtf_membership' ); ?></a>
						</li>
					</ul>
					<div class="edgtf-login-content-inner" id="edgtf-login-content">
						<div
							class="edgtf-wp-login-holder"><?php echo edgtf_membership_execute_shortcode( 'edgtf_user_login', array() ); ?>
						</div>
					</div>
					<div class="edgtf-register-content-inner" id="edgtf-register-content">
						<div
							class="edgtf-wp-register-holder"><?php echo edgtf_membership_execute_shortcode( 'edgtf_user_register', array() ) ?>
						</div>
					</div>
					<div class="edgtf-reset-pass-content-inner" id="edgtf-reset-pass-content">
						<div
							class="edgtf-wp-reset-pass-holder"><?php echo edgtf_membership_execute_shortcode( 'edgtf_user_reset_password', array() ) ?>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php do_action( 'walker_edge_before_container_close' ); ?>
	</div>
<?php get_footer(); ?>
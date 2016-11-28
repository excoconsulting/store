<div class="edgtf-login-register-holder">
	<div class="edgtf-login-register-content">
		<ul>
			<li><a href="#edgtf-login-content"><?php esc_html_e( 'LOGIN', 'edgtf_membership' ); ?></a></li>
			<li><a href="#edgtf-register-content"><?php esc_html_e( 'REGISTER', 'edgtf_membership' ); ?></a></li>
		</ul>
		<div class="edgtf-login-content-inner" id="edgtf-login-content">
			<div class="edgtf-wp-login-holder"><?php echo edgtf_membership_execute_shortcode( 'edgtf_user_login', array() ); ?></div>
		</div>
		<div class="edgtf-register-content-inner" id="edgtf-register-content">
			<div class="edgtf-wp-register-holder"><?php echo edgtf_membership_execute_shortcode( 'edgtf_user_register', array() ) ?></div>
		</div>
	</div>
</div>
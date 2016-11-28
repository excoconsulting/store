<?php
$current_user    = wp_get_current_user();
$name            = $current_user->display_name;
$current_user_id = $current_user->ID;
?>
<div class="edgtf-logged-in-user">
	<span>
		<?php if ( edgtf_membership_theme_installed() ) {
			$profile_image = get_user_meta( $current_user_id, 'social_profile_image', true );
			if ( $profile_image == '' ) {
				$profile_image = get_avatar( $current_user_id, 28 );
			} else {
				$profile_image = '<img src="' . esc_url( $profile_image ) . '" />';
			}
			echo edgtf_membership_kses_img( $profile_image );
		} ?>
		<span class="edgtf-logged-in-user-name"><?php echo esc_html( $name ); ?></span>
		<?php if ( edgtf_membership_theme_installed() ) {
			echo walker_edge_icon_collections()->renderIcon( 'fa-angle-down', 'font_awesome' );
		} ?>
	</span>
</div>
<ul class="edgtf-login-dropdown">
	<?php
	$nav_items = edgtf_membership_get_dashboard_navigation_items();
	foreach ( $nav_items as $nav_item ) { ?>
		<li>
			<a href="<?php echo $nav_item['url']; ?>">
				<?php echo $nav_item['text']; ?>
			</a>
		</li>
	<?php } ?>
	<li>
		<a href="<?php echo wp_logout_url( home_url( '/' ) ); ?>">
			<?php esc_html_e( 'Log Out', 'edgtf_membership' ); ?>
		</a>
	</li>
</ul>
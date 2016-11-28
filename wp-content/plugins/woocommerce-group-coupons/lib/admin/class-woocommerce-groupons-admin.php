<?php
/**
 * class-woocommerce-groupons-admin.php
 *
 * Copyright (c) "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is provided subject to the license granted.
 * Unauthorized use and distribution is prohibited.
 * See COPYRIGHT.txt and LICENSE.txt
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This header and all notices must be kept intact.
 *
 * @author Karim Rahimpur
 * @package woocommerce-groupons
 * @since woocommerce-groupons 1.0.0
 */

/**
 * WooCommerce Groupons settings.
 */
class WooCommerce_Groupons_Admin {

	const NONCE = 'woocommerce-groupons-admin-nonce';

	/**
	 * Admin setup.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ), 40 );
	}

	/**
	 * Adds the admin section.
	 */
	public static function admin_menu() {
		$admin_page = add_submenu_page(
			'woocommerce',
			__( 'Group Coupons' ),
			__( 'Group Coupons' ),
			'manage_woocommerce',
			'woocommerce_group_coupons',
			array( __CLASS__, 'woocommerce_groupons' )
		);
// 		add_action( 'admin_print_scripts-' . $admin_page, array( __CLASS__, 'admin_print_scripts' ) );
// 		add_action( 'admin_print_styles-' . $admin_page, array( __CLASS__, 'admin_print_styles' ) );
	}

	/**
	 * Renders the admin section.
	 */
	public static function woocommerce_groupons() {

		if ( !current_user_can( 'manage_woocommerce' ) ) {
			wp_die( __( 'Access denied.', WOO_GROUPONS_PLUGIN_DOMAIN ) );
		}

		$options = get_option( 'woocommerce-groupons', null );
		if ( $options === null ) {
			if ( add_option( 'woocommerce-groupons', array(), null, 'no' ) ) {
				$options = get_option( 'woocommerce-groupons' );
			}
		}

		if ( isset( $_POST['submit'] ) ) {
			if ( wp_verify_nonce( $_POST[self::NONCE], 'set' ) ) {
				if ( WOO_GROUPONS_GROUPS_IS_ACTIVE ) {
					$options[WOO_GROUPONS_SHOW_YOU_MUST_BE_A_MEMBER] = isset( $_POST[WOO_GROUPONS_SHOW_YOU_MUST_BE_A_MEMBER] );
					$options[WOO_GROUPONS_YOU_MUST_BE_A_MEMBER] = !empty( $_POST[WOO_GROUPONS_YOU_MUST_BE_A_MEMBER] ) ? wp_filter_kses( $_POST[WOO_GROUPONS_YOU_MUST_BE_A_MEMBER] ) : '';
					$options[WOO_GROUPONS_AUTO_COUPONS] = isset( $_POST[WOO_GROUPONS_AUTO_COUPONS] );
				}
				$options[WOO_GROUPONS_ENABLE_ROLES] = isset( $_POST[WOO_GROUPONS_ENABLE_ROLES] );
				$options[WOO_GROUPONS_LOGOUT_REMOVE_COUPONS] = isset( $_POST[WOO_GROUPONS_LOGOUT_REMOVE_COUPONS] );
				update_option( 'woocommerce-groupons', $options );
			}
		}

		$show_you_must_be_a_member = isset( $options[WOO_GROUPONS_SHOW_YOU_MUST_BE_A_MEMBER] ) ? $options[WOO_GROUPONS_SHOW_YOU_MUST_BE_A_MEMBER] : WOO_GROUPONS_SHOW_YOU_MUST_BE_A_MEMBER_DEFAULT;
		$you_must_be_a_member = isset( $options[WOO_GROUPONS_YOU_MUST_BE_A_MEMBER] ) ? $options[WOO_GROUPONS_YOU_MUST_BE_A_MEMBER] : WOO_GROUPONS_YOU_MUST_BE_A_MEMBER_DEFAULT;
		$auto_coupons = isset( $options[WOO_GROUPONS_AUTO_COUPONS] ) ? $options[WOO_GROUPONS_AUTO_COUPONS] : WOO_GROUPONS_AUTO_COUPONS_DEFAULT;
		$enable_roles = isset( $options[WOO_GROUPONS_ENABLE_ROLES] ) ? $options[WOO_GROUPONS_ENABLE_ROLES] : WOO_GROUPONS_ENABLE_ROLES_DEFAULT;
		$logout_remove_coupons = isset( $options[WOO_GROUPONS_LOGOUT_REMOVE_COUPONS] ) ? $options[WOO_GROUPONS_LOGOUT_REMOVE_COUPONS] : WOO_GROUPONS_LOGOUT_REMOVE_COUPONS_DEFAULT;

		echo '<div class="woocommerce-groupons">';

		echo '<h2>' . __( 'Group Coupons', WOO_GROUPONS_PLUGIN_DOMAIN ) . '</h2>';

		echo '<form action="" name="options" method="post">';
		echo '<div>';

		if ( WOO_GROUPONS_GROUPS_IS_ACTIVE ) {

			echo '<p>';
			echo __( '<h3>Groups</h3>', WOO_GROUPONS_PLUGIN_DOMAIN );
			echo '</p>';

			echo '<h4>' . __( 'Automatic group coupons', WOO_GROUPONS_PLUGIN_DOMAIN ) . '</h4>';

			echo '<p>';
			echo '<label>';
			printf( '<input name="%s" type="checkbox" %s />', WOO_GROUPONS_AUTO_COUPONS, $auto_coupons ? ' checked="checked" ' : '' );
			echo ' ';
			_e( 'Enable automatic coupons', WOO_GROUPONS_PLUGIN_DOMAIN );
			echo '</label>';
			echo '</p>';
			echo '<p class="description">';
			_e( 'Automatic group coupons are coupons that are applied to group members automatically.', WOO_GROUPONS_PLUGIN_DOMAIN );
			echo '</p>';

			echo '<h4>' . __( 'Coupon error message', WOO_GROUPONS_PLUGIN_DOMAIN ) . '</h4>';

			echo '<p>';
			echo '<label>';
			printf( '<input name="%s" type="checkbox" %s />', WOO_GROUPONS_SHOW_YOU_MUST_BE_A_MEMBER, $show_you_must_be_a_member ? ' checked="checked" ' : '' );
			echo ' ';
			_e( 'Show the customized message', WOO_GROUPONS_PLUGIN_DOMAIN );
			echo '</label>';
			echo '</p>';
			echo '<p class="description">';
			_e( 'Show a customized error message when applying a coupon fails due to the user not being a member of a required group.', WOO_GROUPONS_PLUGIN_DOMAIN );
			echo '</p>';

			echo '<p>';
			echo '<label>';
			printf( '<input style="%s" name="%s" type="text" value="%s" />', 'width:62%;', WOO_GROUPONS_YOU_MUST_BE_A_MEMBER, esc_attr( $you_must_be_a_member ) );
			echo '</p>';
			echo '<p class="description">';
			_e( 'Use the <code>%s</code> placeholder where the names of the groups should be shown.', WOO_GROUPONS_PLUGIN_DOMAIN );
			echo ' ';
			_e( sprintf( 'The default error message is <blockquote>%s</blockquote>', htmlentities( WOO_GROUPONS_YOU_MUST_BE_A_MEMBER_DEFAULT ) ), WOO_GROUPONS_PLUGIN_DOMAIN );
			echo '</label>';
			echo '</p>';
		} else {
			echo '<div class="options_group">';
			echo '<p>';
			echo __( '<h3>Groups</h3>', WOO_GROUPONS_PLUGIN_DOMAIN );
			echo '</p>';
			echo '<p class="description">';
			echo __( 'The <a href="http://www.itthinx.com/plugins/groups/">Groups</a> plugin must be installed and activated to limit coupons to group members.', WOO_GROUPONS_PLUGIN_DOMAIN );
			echo '</p>';
			echo '<p class="description">';
			echo __( 'Related settings will be shown if <em>Groups</em> is activated.', WOO_GROUPONS_PLUGIN_DOMAIN );
			echo '</p>';
			echo '</div>';
		}

		echo '<h3>' . __( 'Roles', WOO_GROUPONS_PLUGIN_DOMAIN ) . '</h3>';

		echo '<p>';
		echo '<label>';
		printf( '<input name="%s" type="checkbox" %s />', WOO_GROUPONS_ENABLE_ROLES, $enable_roles ? ' checked="checked" ' : '' );
		echo ' ';
		_e( 'Enable coupon restrictions based on roles.', WOO_GROUPONS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';
		echo '<p class="description">';
		_e( 'Coupons can be limited by user role when this option is enabled.', WOO_GROUPONS_PLUGIN_DOMAIN );
		echo '</p>';

		echo '<h3>' . __( 'Logout', WOO_GROUPONS_PLUGIN_DOMAIN ) . '</h3>';

		echo '<p>';
		echo '<label>';
		printf( '<input name="%s" type="checkbox" %s />', WOO_GROUPONS_LOGOUT_REMOVE_COUPONS, $logout_remove_coupons ? ' checked="checked" ' : '' );
		echo ' ';
		_e( 'Remove coupons after logout.', WOO_GROUPONS_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';
		echo '<p class="description">';
		_e( 'When this option is enabled, all coupons are removed from the cart after a user logs out.', WOO_GROUPONS_PLUGIN_DOMAIN );
		echo '</p>';

		echo '<p>';
		echo wp_nonce_field( 'set', self::NONCE, true, false );
		echo '<input class="button" type="submit" name="submit" value="' . __( 'Save', WOO_GROUPONS_PLUGIN_DOMAIN ) . '"/>';
		echo '</p>';
		echo '</div>';

		echo '</form>';

		echo '</div>'; // .woocommerce-groupons

	}
}
WooCommerce_Groupons_Admin::init();

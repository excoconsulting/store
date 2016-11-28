<?php
/**
 * class-woocommerce-groupons-coupons-roles-admin.php
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
 * @since woocommerce-groupons 1.1.0
 */

/**
 * Adds roles info on the Coupons admin screen.
 */
class WooCommerce_Groupons_Coupons_Roles_Admin {

	/**
	 * Adds column hooks.
	 */
	public static function init() {
		add_filter( 'manage_edit-shop_coupon_columns', array( __CLASS__, 'shop_coupon_columns' ), 100 );
		add_action( 'manage_shop_coupon_posts_custom_column', array( __CLASS__, 'shop_coupon_posts_custom_column' ), 10, 2 );
	}

	/**
	 * Adds our columns to the coupon list.
	 * @param array $posts_columns
	 * @return array
	 */
	public static function shop_coupon_columns( $posts_columns ) {
		$posts_columns['roles'] = sprintf( __( '<span title="%s">Roles</span>', WOO_GROUPONS_PLUGIN_DOMAIN ), __( 'Coupons are limited to the roles shown.', WOO_GROUPONS_PLUGIN_DOMAIN ) );
		return $posts_columns;
	}

	/**
	 * Renders the roles column.
	 * @param string $column_name
	 * @param int $post_id
	 */
	public static function shop_coupon_posts_custom_column( $column_name, $post_id ) {
		switch( $column_name ) {
			case 'roles' :
				$coupon_roles = get_post_meta( $post_id, '_groupon_roles', false );
				if ( count( $coupon_roles ) > 0 ) {
					global $wp_roles;
					$role_names = $wp_roles->get_names();
					$roles = array();
					foreach( $coupon_roles as $role ) {
						if ( $wp_roles->is_role( $role ) ) {
							$roles[] = $role;
						}
					}
					usort( $roles, 'strcmp' );
					echo '<ul>';
					foreach( $roles as $role ) {
						echo '<li>';
						echo wp_filter_nohtml_kses( isset( $role_names[$role] ) ? $role_names[$role] : $role );
						echo '</li>';
					}
					echo '</ul>';
				} else {
					echo __( '-', WOO_GROUPONS_PLUGIN_DOMAIN );
				}
				break;
		}
	}
}
WooCommerce_Groupons_Coupons_Roles_Admin::init();

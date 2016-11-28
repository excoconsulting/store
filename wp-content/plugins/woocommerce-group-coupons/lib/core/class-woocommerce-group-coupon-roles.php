<?php
/**
 * class-woocommerce-group-coupon-roles.php
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
 * Coupon handler.
 */
class WooCommerce_Group_Coupon_Roles {

	/**
	 * Initialize hooks and filters.
	 */
	public static function init() {
		add_filter( 'woocommerce_coupon_is_valid', array( __CLASS__, 'woocommerce_coupon_is_valid' ), 10, 2 );
		add_filter( 'woocommerce_coupon_data_tabs', array( __CLASS__, 'woocommerce_coupon_data_tabs' ) );
		add_action( 'woocommerce_process_shop_coupon_meta', array( __CLASS__, 'woocommerce_process_shop_coupon_meta' ), 10, 2 );
		add_action( 'init', array( __CLASS__, 'wp_init' ) );
	}

	/**
	 * Registers panel actions.
	 */
	public static function wp_init() {
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1.9' ) >= 0 ) {
			add_action( 'woocommerce_coupon_data_panels', array( __CLASS__, 'woocommerce_coupon_data_panels' ) );
		} else {
			add_action( 'woocommerce_coupon_options', array( __CLASS__, 'woocommerce_coupon_data_panels' ) );
		}
	}

	/**
	 * Filter the validity of a coupon based on roles.
	 * @param boolean $valid
	 * @param WC_Coupon $coupon
	 */
	public static function woocommerce_coupon_is_valid( $valid, $coupon ) {
		if ( $valid ) {
			$coupon_roles = !empty( $coupon ) && !empty( $coupon->id ) ? get_post_meta( $coupon->id, '_groupon_roles', false ) : array();
			if ( count( $coupon_roles ) > 0 ) {
				$has_role = false;
				foreach ( $coupon_roles as $role ) {
					global $wp_roles;
					if ( $wp_roles->is_role( $role ) && self::has_role( $role ) ) {
						$has_role = true;
						break;
					}
				}
				$valid = $has_role;
			}
			// Allow others to plug in here:
			$valid = apply_filters( 'woocommerce_group_coupon_is_valid', $valid, $coupon );
		}
		return $valid;
	}

	/**
	 * Adds the Groups tab.
	 * @param array $tabs
	 * @return array
	 */
	public static function woocommerce_coupon_data_tabs( $tabs ) {
		$tabs['roles'] = array(
			'label'  => __( 'Roles', WOO_GROUPONS_PLUGIN_DOMAIN ),
			'target' => 'custom_coupon_roles',
			'class'  => 'coupon-roles'
		); 
		return $tabs;
	}

	/**
	 * Renders role options.
	 */
	public static function woocommerce_coupon_data_panels() {

		global $post;

		// guard against woocommerce_coupon_options action invoked during save
		if ( isset( $_POST['action'] ) ) {
			return;
		}

		echo '<div id="custom_coupon_roles" class="panel woocommerce_options_panel">';

		echo '<div class="options_group">';
		echo '<p>';
		echo __( '<strong>Roles</strong> - limit the coupon to roles', WOO_GROUPONS_PLUGIN_DOMAIN );
		echo '</p>';
		$coupon_roles = !empty( $post ) ? get_post_meta( $post->ID, '_groupon_roles', false ) : array();
		global $wp_roles;
		$roles = $wp_roles->get_names();
		if ( count( $roles ) > 0 ) {
			echo '<p class="form-field">';
			if ( !defined( 'WC_VERSION' ) || ( version_compare( WC_VERSION , '2.3.0' ) < 0 ) ) {
				printf( '<select class="_groupon_roles multiselect" name="_groupon_roles[]" multiple="multiple" title="%s">', esc_attr( __( 'No restriction', WOO_GROUPONS_PLUGIN_DOMAIN ) ) );
			} else {
				echo '<label for="_groupon_roles">';
				_e( 'Roles', WOO_GROUPONS_PLUGIN_DOMAIN );
				echo '</label>';
				echo '<select id="_groupon_roles" name="_groupon_roles[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="' . __( 'No roles', WOO_GROUPONS_PLUGIN_DOMAIN ) . '">';
			}

			foreach( $roles as $role => $name ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $role ), in_array( $role, $coupon_roles ) ? ' selected="selected" ' : '', wp_filter_nohtml_kses( $name ) );
			}
			echo '</select>';
			echo '</p>';
			if ( !defined( 'WC_VERSION' ) || ( version_compare( WC_VERSION , '2.3.0' ) < 0 ) ) {
				echo '<script type="text/javascript">';
				echo 'if (typeof jQuery !== "undefined") {';
				echo 'jQuery("select._groupon_roles").chosen();';
				echo '}';
				echo '</script>';
			}
		} else {
			echo '<p>';
			__( 'There are no roles available.', WOO_GROUPONS_PLUGIN_DOMAIN );
			echo '</p>';
		}

		echo '<p class="description">';
		echo __( 'Only users who have one of the selected roles will be allowed to use the coupon.', WOO_GROUPONS_PLUGIN_DOMAIN );
		echo ' ';
		echo __( 'If no role is selected, the coupon is not restricted to any roles.', WOO_GROUPONS_PLUGIN_DOMAIN );
		echo '</p>';
		echo '</div>';

		echo '</div>'; // #custom_coupon_roles

		if ( !( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1.9' ) >= 0 ) ) {
			echo '<script type="text/javascript">';
			echo 'if (typeof jQuery !== "undefined"){';
			echo 'jQuery(document).ready(function(){';
			echo 'jQuery("#custom_coupon_roles").insertAfter(jQuery(".woocommerce_options_panel").last());';
			echo '});';
			echo '}';
			echo '</script>';
		}
	}

	/**
	 * Saves role data for the coupon.
	 * @param int $post_id coupon ID
	 * @param object $post coupon
	 */
	public static function woocommerce_process_shop_coupon_meta( $post_id, $post ) {
		global $wp_roles;
		delete_post_meta( $post_id, '_groupon_roles' );
		$roles = $wp_roles->get_names();
		if ( isset( $_POST['_groupon_roles'] ) ) {
			foreach( $roles as $role => $name ) {
				if ( in_array( $role, $_POST['_groupon_roles'] ) ) {
					add_post_meta( $post_id, '_groupon_roles', $role );
				}
			}
		}
	}

	/**
	 * Check if the user has the role.
	 * @param string $role
	 * @param int $user_id current user by default
	 */
	public static function has_role( $role, $user_id = null ) {
		$result = false;
		if ( $user_id === null ) {
			$user = wp_get_current_user();
		} else {
			$user = get_userdata( intval( $user_id ) );
		}
		if ( $user ) {
			$result = in_array( $role, (array) $user->roles );
		}
		return $result;
	}
}
WooCommerce_Group_Coupon_Roles::init();

<?php
/**
 * class-woocommerce-groupon.php
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
 * Coupon handler.
 */
class WooCommerce_Groupon {

	/**
	 * Initialize hooks and filters.
	 */
	public static function init() {
		if ( WOO_GROUPONS_GROUPS_IS_ACTIVE ) {
			add_action( 'init', array( __CLASS__, 'wp_init' ) );
			add_filter( 'woocommerce_coupon_is_valid', array( __CLASS__, 'woocommerce_coupon_is_valid' ), 10, 2 );
			add_filter( 'woocommerce_coupon_data_tabs', array( __CLASS__, 'woocommerce_coupon_data_tabs' ) );
			add_action( 'woocommerce_process_shop_coupon_meta', array( __CLASS__, 'woocommerce_process_shop_coupon_meta' ), 10, 2 );
			add_filter( 'woocommerce_coupon_error', array( __CLASS__, 'woocommerce_coupon_error' ), 10, 3 );
			add_action( 'woocommerce_calculate_totals', array( __CLASS__, 'woocommerce_calculate_totals' ) );
		} else {
			add_action( 'woocommerce_coupon_options', array( __CLASS__, 'woocommerce_coupon_options_groups_missing' ) );
		}
	}

	/**
	 * Data panel actions.
	 */
	public static function wp_init() {
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1.9' ) >= 0 ) {
			// from WC 2.1.9
			add_action( 'woocommerce_coupon_data_panels', array( __CLASS__, 'woocommerce_coupon_data_panels' ) );
		} else {
			add_action( 'woocommerce_coupon_options', array( __CLASS__, 'woocommerce_coupon_data_panels' ) );
		}
	}

	/**
	 * Applies auto coupons for group members.
	 * Registers panel actions.
	 */
	public static function woocommerce_calculate_totals( $cart ) {
		global $wpdb, $woocommerce;

		if ( isset( $woocommerce ) && isset( $woocommerce->cart ) && $woocommerce->cart->coupons_enabled() ) {
			$options = get_option( 'woocommerce-groupons', null );
			$auto_coupons = isset( $options[WOO_GROUPONS_AUTO_COUPONS] ) ? $options[WOO_GROUPONS_AUTO_COUPONS] : WOO_GROUPONS_AUTO_COUPONS_DEFAULT;
			if ( $auto_coupons ) {
				$coupons = $wpdb->get_results( "SELECT DISTINCT ID, post_title FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id WHERE {$wpdb->posts}.post_status = 'publish' AND {$wpdb->postmeta}.meta_key = '_groupon_auto_groups'" );
				if ( $coupons && ( count( $coupons ) > 0 ) ) {
					// Note that this may include guests at some point ...
					$user_id = get_current_user_id();
					foreach ( $coupons as $coupon ) {
						$coupon_code = $coupon->post_title;
						$coupon = new WC_Coupon( $coupon_code );
						if ( $coupon->id ) {
							// Check it can be used with cart
							if ( $coupon->is_valid() ) {
								if ( !$woocommerce->cart->has_discount( $coupon_code ) ) {
									$apply_coupon = false;
									$coupon_auto_groups = !empty( $coupon ) && !empty( $coupon->id ) ? get_post_meta( $coupon->id, '_groupon_auto_groups', false ) : array();
									if ( count( $coupon_auto_groups ) > 0 ) {
										$is_member = false;
										foreach ( $coupon_auto_groups as $group_id ) {
											if ( Groups_User_Group::read( $user_id, $group_id ) ) {
												$apply_coupon = true;
												break;
											}
										}
									}
									if ( $apply_coupon ) {
										$woocommerce->cart->add_discount( $coupon_code );
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Filter the validity of a coupon based on group membership.
	 * @param boolean $valid
	 * @param WC_Coupon $coupon
	 */
	public static function woocommerce_coupon_is_valid( $valid, $coupon ) {
		// Only act if the coupon is still considered valid at this point.
		if ( $valid ) {
			$user_id = get_current_user_id(); // Don't restrict, this applies to guests, too.
			$coupon_groups = !empty( $coupon ) && !empty( $coupon->id ) ? get_post_meta( $coupon->id, '_groupon_groups', false ) : array();
			if ( count( $coupon_groups ) > 0 ) {
				$is_member = false;
				foreach ( $coupon_groups as $group_id ) {
					if ( Groups_User_Group::read( $user_id, $group_id ) ) {
						$is_member = true;
						break;
					}
				}
				$valid = $is_member;
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
		$tabs['groups'] = array(
			'label'  => __( 'Groups', WOO_GROUPONS_PLUGIN_DOMAIN ),
			'target' => 'custom_coupon_groups',
			'class'  => 'coupon-groups'
		); 
		return $tabs;
	}

	/**
	 * Renders group options.
	 */
	public static function woocommerce_coupon_data_panels() {

		global $wpdb, $post;

		// guard against woocommerce_coupon_options action invoked during save
		if ( isset( $_POST['action'] ) ) {
			return;
		}

		echo '<div id="custom_coupon_groups" class="panel woocommerce_options_panel">';

		echo '<div class="options_group">';

		echo '<p>';
		echo __( '<strong>Groups</strong> - limit the coupon to group members', WOO_GROUPONS_PLUGIN_DOMAIN );
		echo '</p>';

		$coupon_groups = !empty( $post ) ? get_post_meta( $post->ID, '_groupon_groups', false ) : array();

		$group_table = _groups_get_tablename( "group" );
		$groups = $wpdb->get_results( "SELECT * FROM $group_table ORDER BY name" );

		if ( count( $groups ) > 0 ) {
			echo '<ul>';
			foreach( $groups as $group ) {
				echo '<li>';
				woocommerce_wp_checkbox(
					array(
						'id'    => '_groupon_groups-' . esc_attr( $group->group_id ), // field name is derived from this, can't indicate name="_groupon_groups[]"
						'label' => wp_filter_nohtml_kses( $group->name ),
						'value' => in_array( $group->group_id, $coupon_groups ) ? 'yes' : ''
					)
				);
				echo '</li>';
			}
			echo '</ul>';
		} else {
			echo __( 'There are no groups available to select. At least one group must exist.', WOO_GROUPONS_PLUGIN_DOMAIN );
		}

		echo '<p class="description">';
		echo __( 'Only members of the selected groups will be allowed to use the coupon.', WOO_GROUPONS_PLUGIN_DOMAIN );
		echo ' ';
		echo __( 'If no group is selected, the coupon is not restricted to any group members.', WOO_GROUPONS_PLUGIN_DOMAIN );
		echo '</p>';

		echo '</div>'; // .options_group

		$auto_coupons = isset( $options[WOO_GROUPONS_AUTO_COUPONS] ) ? $options[WOO_GROUPONS_AUTO_COUPONS] : WOO_GROUPONS_AUTO_COUPONS_DEFAULT;
		if ( $auto_coupons ) {

			echo '<div class="options_group">';

			echo '<p>';
			echo __( '<strong>Automatic application</strong> - apply the coupon to group members automatically', WOO_GROUPONS_PLUGIN_DOMAIN );
			echo '</p>';

			$coupon_auto_groups = !empty( $post ) ? get_post_meta( $post->ID, '_groupon_auto_groups', false ) : array();

			if ( count( $groups ) > 0 ) {
				echo '<ul>';
				foreach( $groups as $group ) {
					echo '<li>';
					woocommerce_wp_checkbox(
						array(
							'id'    => '_groupon_auto_groups-' . esc_attr( $group->group_id ), // field name is derived from this, can't indicate name="_groupon_auto_groups[]"
							'label' => wp_filter_nohtml_kses( $group->name ),
							'value' => in_array( $group->group_id, $coupon_auto_groups ) ? 'yes' : ''
						)
					);
					echo '</li>';
				}
				echo '</ul>';
			} else {
				echo __( 'There are no groups available to select. At least one group must exist.', WOO_GROUPONS_PLUGIN_DOMAIN );
			}

			echo '<p class="description">';
			echo __( 'The coupon will be applied automatically to members of any of the selected groups.', WOO_GROUPONS_PLUGIN_DOMAIN );
			echo '</p>';

			echo '<p class="description">';
			echo '<strong>';
			echo __( 'Important: ', WOO_GROUPONS_PLUGIN_DOMAIN );
			echo '</strong>';
			echo __( 'If the coupon should only be available to members of the selected groups, you must also limit the coupon to these groups.', WOO_GROUPONS_PLUGIN_DOMAIN );
			echo '</p>';

			echo '</div>'; // .options_group
		}

		echo '</div>'; // #custom_coupon_groups

		if ( !( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1.9' ) >= 0 ) ) {
			echo '<script type="text/javascript">';
			echo 'if (typeof jQuery !== "undefined"){';
			echo 'jQuery(document).ready(function(){';
			echo 'jQuery("#custom_coupon_groups").insertAfter(jQuery(".woocommerce_options_panel").last());';
			echo '});';
			echo '}';
			echo '</script>';
		}
	}

	/**
	 * Options reminder when Groups is missing.
	 */
	public static function woocommerce_coupon_options_groups_missing() {
		// guard against woocommerce_coupon_options action invoked during save
		if ( isset( $_POST['action'] ) ) {
			return;
		}
		echo '<div class="options_group">';
		echo '<p>';
		echo __( '<strong>Groups</strong> - limit the coupon to group members', WOO_GROUPONS_PLUGIN_DOMAIN );
		echo '</p>';
		echo '<p>';
		echo __( 'The <a href="http://www.itthinx.com/plugins/groups/">Groups</a> plugin must be installed and activated to limit coupons to group members.', WOO_GROUPONS_PLUGIN_DOMAIN );
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Saves group data for the coupon.
	 * @param int $post_id coupon ID
	 * @param object $post coupon
	 */
	public static function woocommerce_process_shop_coupon_meta( $post_id, $post ) {
		global $wpdb;
		delete_post_meta( $post_id, '_groupon_groups' );
		delete_post_meta( $post_id, '_groupon_auto_groups' );
		$group_table = _groups_get_tablename( "group" );
		$groups = $wpdb->get_results( "SELECT group_id FROM $group_table" );
		if ( count( $groups ) > 0 ) {
			foreach( $groups as $group ) {
				if ( !empty( $_POST['_groupon_groups-' . $group->group_id] ) ) {
					add_post_meta( $post_id, '_groupon_groups', $group->group_id );
				}
				if ( !empty( $_POST['_groupon_auto_groups-' . $group->group_id] ) ) {
					add_post_meta( $post_id, '_groupon_auto_groups', $group->group_id );
				}
			}
		}
	}

	/**
	 * Modifies the coupon error message if enabled.
	 * @param string $err error message
	 * @param int $err_code error code
	 * @param WC_oOupon $coupon the coupon
	 */
	public static function woocommerce_coupon_error( $err, $err_code, $coupon ) {
		global $woocommerce;
		if ( $err_code == WC_Coupon::E_WC_COUPON_INVALID_FILTERED ) {
			$options = get_option( 'woocommerce-groupons', null );
			$show_msg = isset( $options[WOO_GROUPONS_SHOW_YOU_MUST_BE_A_MEMBER] ) ? $options[WOO_GROUPONS_SHOW_YOU_MUST_BE_A_MEMBER] : WOO_GROUPONS_SHOW_YOU_MUST_BE_A_MEMBER_DEFAULT;
			if ( $show_msg ) {
				$coupon_groups = !empty( $coupon ) ? get_post_meta( $coupon->id, '_groupon_groups', false ) : array();
				if ( count( $coupon_groups ) > 0 ) {
					$group_names = array();
					foreach( $coupon_groups as $group_id ) {
						if ( $group = Groups_Group::read( $group_id ) ) {
							$group_names[] = wp_filter_nohtml_kses( $group->name );
						}
					}
					$group_names = implode( __( ' or ', WOO_GROUPONS_PLUGIN_DOMAIN ), $group_names );
					$msg = isset( $options[WOO_GROUPONS_YOU_MUST_BE_A_MEMBER] ) ? $options[WOO_GROUPONS_YOU_MUST_BE_A_MEMBER] : WOO_GROUPONS_YOU_MUST_BE_A_MEMBER_DEFAULT;
					$err = sprintf( $msg, $group_names );
				}
			}
		}
		return $err;
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
WooCommerce_Groupon::init();

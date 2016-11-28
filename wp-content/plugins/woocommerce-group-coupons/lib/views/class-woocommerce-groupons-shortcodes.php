<?php
/**
 * class-woocommerce-groupons-shortcodes.php
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
 * Shortcodes.
 */
class WooCommerce_Groupons_Shortcodes {

	/**
	 * Adds shortcodes.
	 */
	public static function init() {
		add_shortcode( 'group_coupons', array( __CLASS__, 'groupons' ) );
	}

	/**
	 * Renders the group coupons the current user can use.
	 * Attributes: 
	 * <ul>
	 * <li>color             : defaults to <code>blue</code>, also supports <code>red</code>, <code>green</code>, <code>yellow</code> for built-in styles and other values for custom CSS rules</li>
	 * <li>order_by          : defaults to <code>code</code>, also accepts <code>id</code></li>
	 * <li>order             : default to <code>ASC</code>, also accepts <code>DESC</code></li>
	 * <li>auto              : defaults to <code>no</code>, whether coupons that are automatically applied should be included, use <code>yes</code> to include those</li>
	 * <li>groups            : defaults to <code>yes</code>, whether coupons that are limited to groups should be included, use <code>no</code> to disable</li>
	 * <li>roles             : defaults to <code>yes</code>, whether coupons that are limited to roles should be included, use <code>no</code> to disable</li>
	 * <li>show_cart_invalid : defaults to <code>true</code>, coupons that require cart conditions to be valid should be included although the current cart does not meet those conditions, use <code>false</code> to disable</li>
	 * <li>stylesheet        : indicate a URL to load a custom stylesheet, when empty will not load a stylesheet, defaults to <code>null</code> and loads the stylesheet with built-in styles</li>
	 * </ul>
	 *
	 * @param array $atts attributes
	 * @param string $content not used
	 * @return rendered groups for current user
	 */
	public static function groupons( $atts, $content = null ) {
		global $wpdb, $woocommerce_group_coupons;

		$options = get_option( 'woocommerce-groupons', null );
		$enable_roles = isset( $options[WOO_GROUPONS_ENABLE_ROLES] ) ? $options[WOO_GROUPONS_ENABLE_ROLES] : WOO_GROUPONS_ENABLE_ROLES_DEFAULT;

		$options = shortcode_atts(
			array(
				'color'             => 'blue',
				'order_by'          => 'code',
				'order'             => 'ASC',
				'auto'              => 'no',
				'groups'            => 'yes',
				'roles'             => 'yes',
				'show_cart_invalid' => true,
				'stylesheet'        => null,
				'show_discount'     => 'yes'
			),
			$atts
		);

		$color = wp_strip_all_tags( $options['color'] );
		$show_cart_invalid = $options['show_cart_invalid'] === true || $options['show_cart_invalid'] === 'true' || $options['show_cart_invalid'] === 'yes';

		$user_id = get_current_user_id();

		$output = "";
		if ( !isset( $woocommerce_group_coupons ) ) {
			$woocommerce_group_coupons = 0;
			if ( $options['stylesheet'] === null ) {
				wp_enqueue_style( 'woocommerce-group-coupons', trailingslashit( WOO_GROUPONS_PLUGIN_URL ) . 'css/woocommerce-group-coupons.css', array(), WOO_GROUPONS_PLUGIN_VERSION );
			} else {
				if ( !empty( $options['stylesheet'] ) ) {
					wp_enqueue_style( 'woocommerce-group-coupons', $options['stylesheet'], array(), WOO_GROUPONS_PLUGIN_VERSION );
				}
			}
		}

		$coupons      = array();
		$auto_coupons = array();
		$role_coupons = array();

		if ( WOO_GROUPONS_GROUPS_IS_ACTIVE ) {
			if ( $options['groups'] === 'yes' ) {
				$_coupons = $wpdb->get_results( "SELECT DISTINCT ID, post_title FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id WHERE {$wpdb->posts}.post_status = 'publish' AND {$wpdb->postmeta}.meta_key = '_groupon_groups'" );
				if ( $_coupons && ( count( $_coupons ) > 0 ) ) {
					foreach ( $_coupons as $coupon ) {
						$coupon_code = $coupon->post_title;
						$coupon = new WC_Coupon( $coupon_code );
						if ( $coupon->id ) {
							// Check if it can be used
							if ( $coupon->is_valid() || $show_cart_invalid && ( self::is_cart_error( $coupon ) || self::is_subscription_coupon( $coupon ) ) ) {
								$coupon_groups = !empty( $coupon ) && !empty( $coupon->id ) ? get_post_meta( $coupon->id, '_groupon_groups', false ) : array();
								if ( count( $coupon_groups ) > 0 ) {
									foreach ( $coupon_groups as $group_id ) {
										if ( Groups_User_Group::read( $user_id, $group_id ) ) {
											$coupons[] = $coupon;
										}
									}
								}
							}
						}
					}
				}
			}

			if ( $options['auto'] === 'yes' ) {
				$_auto_coupons = $wpdb->get_results( "SELECT DISTINCT ID, post_title FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id WHERE {$wpdb->posts}.post_status = 'publish' AND {$wpdb->postmeta}.meta_key = '_groupon_auto_groups'" );
				if ( $_auto_coupons && ( count( $_auto_coupons ) > 0 ) ) {
					foreach ( $_auto_coupons as $coupon ) {
						$coupon_code = $coupon->post_title;
						$coupon = new WC_Coupon( $coupon_code );
						if ( $coupon->id ) {
							// Check if it can be used
							if ( $coupon->is_valid() || $show_cart_invalid && ( self::is_cart_error( $coupon ) || self::is_subscription_coupon( $coupon ) ) ) {
								$coupon_auto_groups = !empty( $coupon ) && !empty( $coupon->id ) ? get_post_meta( $coupon->id, '_groupon_auto_groups', false ) : array();
								if ( count( $coupon_auto_groups ) > 0 ) {
									foreach ( $coupon_auto_groups as $group_id ) {
										if ( Groups_User_Group::read( $user_id, $group_id ) ) {
											$auto_coupons[] = $coupon;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		if ( $enable_roles ) {
			if ( $options['roles'] === 'yes') {
				$_coupons = $wpdb->get_results( "SELECT DISTINCT ID, post_title FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id WHERE {$wpdb->posts}.post_status = 'publish' AND {$wpdb->postmeta}.meta_key = '_groupon_roles'" );
				if ( $_coupons && ( count( $_coupons ) > 0 ) ) {
					foreach ( $_coupons as $coupon ) {
						$coupon_code = $coupon->post_title;
						$coupon = new WC_Coupon( $coupon_code );
						if ( $coupon->id ) {
							// Check if it can be used
							if ( $coupon->is_valid() || $show_cart_invalid && ( self::is_cart_error( $coupon ) || self::is_subscription_coupon( $coupon ) ) ) {
								$coupon_roles = !empty( $coupon ) && !empty( $coupon->id ) ? get_post_meta( $coupon->id, '_groupon_roles', false ) : array();
								if ( count( $coupon_roles ) > 0 ) {
									foreach ( $coupon_roles as $role ) {
										if ( WooCommerce_Group_Coupon_Roles::has_role( $role ) ) {
											$role_coupons[] = $coupon;
										}
									}
								}
							}
						}
					}
				}
			}
		}

		if ( $options['auto'] === 'yes' ) {
			$_coupons = array_merge( $coupons, $auto_coupons );
		} else {
			$_coupons = $coupons;
		}
		$_coupons = array_merge( $_coupons, $role_coupons );

		$coupons = array();
		foreach( $_coupons as $coupon ) {
			if ( !key_exists( $coupon->id, $coupons ) ) {
				$coupons[$coupon->id] = $coupon;
			}
		}

		switch( $options['order_by'] ) {
			case 'id' :
				usort( $coupons, array( __CLASS__, 'by_id' ) );
				break;
			case 'code' :
				usort( $coupons, array( __CLASS__, 'by_code' ) );
				break;
		}
		switch( $options['order'] ) {
			case 'desc' :
			case 'DESC' :
				$coupons = array_reverse( $coupons );
				break;
		}

		foreach( $coupons as $coupon ) {
			$output .= sprintf( '<div class="groups-coupon-container %s">', $color );
			$output .= sprintf( '<div class="groups-coupon %s">',  $color );
			$output .= '<div class="code">';
			$output .= wp_strip_all_tags( $coupon->code );
			$output .= '</div>';
			if ( $options['show_discount'] === true || $options['show_discount'] === 'true' || $options['show_discount'] === 'yes' ) {
				$discount_info = self::get_discount_info( $coupon );
				if ( !empty( $discount_info ) ) {
					$output .= '<div class="discount-info">';
					$output .= $discount_info;
					$output .= '</div>';
				}
			}
			$output .= '</div>'; // .groups-coupon
			$output .= '</div>'; // .groups-coupon-container
		}
		return $output;
	}

	/**
	 * Check for cart dependencies.
	 * @param array $error_codes
	 * @return true when the error codes indicate only cart dependencies
	 */
	private static function is_cart_error( &$coupon ) {
		self::check_validity( $coupon, $valid, $error_codes );
		$is_cart_error = false;
		if ( count( $error_codes ) > 0 ) {
			$is_cart_error = true;
			foreach( $error_codes as $error_code ) {
				switch( $error_code ) {
					case WC_Coupon::E_WC_COUPON_MIN_SPEND_LIMIT_NOT_MET :
					case WC_Coupon::E_WC_COUPON_NOT_APPLICABLE :
					case WC_Coupon::E_WC_COUPON_NOT_VALID_SALE_ITEMS :
						$is_cart_error = $is_cart_error && true;
						break;
					default :
						$is_cart_error = false;
				}
			}
		}
		return $is_cart_error;
	}

	/**
	 * Check if the coupon is applicable to subscriptions.
	 * @param WC_Coupon $coupon
	 * @return true if it's a subscription coupon, false otherwise
	 */
	private static function is_subscription_coupon( &$coupon ) {
		return
			!empty( $coupon ) &&
			isset( $coupon->type ) &&
			in_array(
				$coupon->type,
				array(
					'recurring_fee',
					'sign_up_fee',
					'recurring_percent',
					'sign_up_fee_percent'
				)
			);
	}

	/**
	 * Perform validity checks on the coupon.
	 *
	 * Derived from WC_Coupon::is_valid().
	 *
	 * @param WC_Coupon $coupon the coupon to check
	 * @param boolean $valid contains validity flag after checks
	 * @param array $error_codes contains all error codes obtained during checks
	 */
	public static function check_validity( &$coupon, &$valid, &$error_codes ) {
		global $woocommerce;
	
		$valid = true;
		$error_codes = array();
	
		if ( $coupon->id ) {
	
			// Usage Limit
			if ( $coupon->usage_limit > 0 ) {
				if ( $coupon->usage_count >= $coupon->usage_limit ) {
					$valid = false;
					$error_codes[] = WC_Coupon::E_WC_COUPON_USAGE_LIMIT_REACHED;
				}
			}
	
			// Expired
			if ( $coupon->expiry_date ) {
				if ( current_time( 'timestamp' ) > $coupon->expiry_date ) {
					$valid = false;
					$error_codes[] = WC_Coupon::E_WC_COUPON_EXPIRED;
				}
			}
	
			// Minimum spend
			if ( $coupon->minimum_amount > 0 ) {
				if ( $coupon->minimum_amount > $woocommerce->cart->subtotal ) {
					$valid = false;
					$error_codes[] = WC_Coupon::E_WC_COUPON_MIN_SPEND_LIMIT_NOT_MET;
				}
			}
	
			// Product ids - If a product included is found in the cart then its valid
			if ( sizeof( $coupon->product_ids ) > 0 ) {
				$valid_for_cart = false;
				if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
					foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
						if ( in_array( $cart_item['product_id'], $coupon->product_ids ) || in_array( $cart_item['variation_id'], $coupon->product_ids ) || in_array( $cart_item['data']->get_parent(), $coupon->product_ids ) ) {
							$valid_for_cart = true;
						}
					}
				}
				if ( ! $valid_for_cart ) {
					$valid = false;
					$error_codes[] = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
				}
			}
	
			// Category ids - If a product included is found in the cart then its valid
			if ( sizeof( $coupon->product_categories ) > 0 ) {
				$valid_for_cart = false;
				if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
					foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
						$product_cats = wp_get_post_terms( $cart_item['product_id'], 'product_cat', array( "fields" => "ids" ) );
						if ( sizeof( array_intersect( $product_cats, $coupon->product_categories ) ) > 0 ) {
							$valid_for_cart = true;
						}
					}
				}
				if ( ! $valid_for_cart ) {
					$valid = false;
					$error_codes[] = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
				}
			}
	
			// Cart discounts cannot be added if non-eligble product is found in cart
			if ( $coupon->type != 'fixed_product' && $coupon->type != 'percent_product' ) {
	
				// Exclude Products
				if ( sizeof( $coupon->exclude_product_ids ) > 0 ) {
					$valid_for_cart = true;
					if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
						foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
							if ( in_array( $cart_item['product_id'], $coupon->exclude_product_ids ) || in_array( $cart_item['variation_id'], $coupon->exclude_product_ids ) || in_array( $cart_item['data']->get_parent(), $coupon->exclude_product_ids ) ) {
								$valid_for_cart = false;
							}
						}
					}
					if ( ! $valid_for_cart ) {
						$valid = false;
						$error_codes[] = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
					}
				}
	
				// Exclude Sale Items
				if ( $coupon->exclude_sale_items == 'yes' ) {
					$valid_for_cart = true;
					$product_ids_on_sale = woocommerce_get_product_ids_on_sale();
					if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
						foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
							if ( in_array( $cart_item['product_id'], $product_ids_on_sale, true ) || in_array( $cart_item['variation_id'], $product_ids_on_sale, true ) || in_array( $cart_item['data']->get_parent(), $product_ids_on_sale, true ) ) {
								$valid_for_cart = false;
							}
						}
					}
					if ( ! $valid_for_cart ) {
						$valid = false;
						$error_codes[] = WC_Coupon::E_WC_COUPON_NOT_VALID_SALE_ITEMS;
					}
				}
	
				// Exclude Categories
				if ( sizeof( $coupon->exclude_product_categories ) > 0 ) {
					$valid_for_cart = true;
					if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
						foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
							$product_cats = wp_get_post_terms( $cart_item['product_id'], 'product_cat', array( "fields" => "ids" ) );
							if ( sizeof( array_intersect( $product_cats, $coupon->exclude_product_categories ) ) > 0 ) {
								$valid_for_cart = false;
							}
						}
					}
					if ( ! $valid_for_cart ) {
						$valid = false;
						$error_codes[] = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
					}
				}
			}
	
			$filtered_valid = apply_filters( 'woocommerce_coupon_is_valid', $valid, $coupon );
			if ( $valid && !$filtered_valid ) {
				$error_codes[] = WC_Coupon::E_WC_COUPON_INVALID_FILTERED;
			}
			$valid = $filtered_valid;
	
		} else {
			$valid = false;
			$error_codes[] = WC_Coupon::E_WC_COUPON_NOT_EXIST;
		}
	
		return $error_codes;
	}

	/**
	 * Coupon comparison by id.
	 *
	 * @param WC_Coupon $a
	 * @param WC_Coupon $b
	 * @return int
	 */
	public static function by_id( $a, $b ) {
		return $a->id - $b->id;
	}

	/**
	 * Coupon comparison by code.
	 *
	 * @param WC_Coupon $a
	 * @param WC_Coupon $b
	 * @return int
	 */
	public static function by_code( $a, $b ) {
		return strcmp( $a->code, $b->code );
	}

	/**
	 * Returns a description of the discount.
	 * 
	 * @param WC_Coupon $coupon
	 * @return string HTML describing the discount
	 */
	public static function get_discount_info( $coupon, $atts = array() ) {
		$product_delimiter = isset( $atts['product_delimiter'] ) ? $atts['product_delimiter'] : ', ';
		$category_delimiter = isset( $atts['category_delimiter'] ) ? $atts['category_delimiter'] : ', ';
		$result = '';

		$amount_suffix = get_woocommerce_currency_symbol();
		switch( $coupon->type ) {
			case 'percent' :
			case 'percent_product' :
			case 'sign_up_fee_percent' :
			case 'recurring_percent' :
				$amount_suffix = '%';
				break;
		}

		$products = array();
		$categories = array();
		switch ( $coupon->type ) {
			case 'fixed_product' :
			case 'percent_product' :
			case 'sign_up_fee' :
			case 'sign_up_fee_percent' :
			case 'recurring_fee' :
			case 'recurring_percent' :
				if ( sizeof( $coupon->product_ids ) > 0 ) {
					foreach( $coupon->product_ids as $product_id ) {
						if ( function_exists( 'wc_get_product' ) ) {
							$product = wc_get_product( $product_id );
						} else {
							$product = get_product( $product_id );
						}
						if ( $product ) {
							$products[] = sprintf(
								'<span class="product-link"><a href="%s">%s</a></span>',
								esc_url( get_permalink( $product_id ) ),
								$product->get_title()
							);
						}
					}
				}
				if ( sizeof( $coupon->product_categories ) > 0 ) {
					foreach( $coupon->product_categories as $term_id ) {
						if ( $term = get_term_by( 'id', $term_id, 'product_cat' ) ) {
							$categories[] = sprintf(
								'<span class="product-link"><a href="%s">%s</a></span>',
								get_term_link( $term->slug, 'product_cat' ),
								esc_html( $term->name )
							);
						}
					}
				}
				break;
		}

		switch ( $coupon->type ) {
			case 'fixed_product' :
			case 'percent_product' :
				if ( sizeof( $coupon->product_ids ) > 0 ) {
					if ( count( $products ) > 0 ) {
						$result = sprintf( __( '%s%s Discount on %s', WOO_GROUPONS_PLUGIN_DOMAIN ), $coupon->amount, $amount_suffix, implode( $product_delimiter, $products ) );
					} else {
						$result = sprintf( __( '%s%s Discount on selected products', WOO_GROUPONS_PLUGIN_DOMAIN ), $coupon->amount, $amount_suffix );
					}
				} else if ( sizeof( $coupon->product_categories ) > 0 ) {
					$result = sprintf( __( '%s%s Discount in %s', WOO_GROUPONS_PLUGIN_DOMAIN ), $coupon->amount, $amount_suffix, implode( $category_delimiter, $categories ) );
				} else if ( sizeof( $coupon->exclude_product_ids ) > 0 || sizeof( $coupon->exclude_product_categories ) > 0 ) {
					$result = sprintf( __( '%s%s Discount on selected products', WOO_GROUPONS_PLUGIN_DOMAIN ), $coupon->amount, $amount_suffix );
				} else {
					$result = sprintf( __( '%s%s Discount', WOO_GROUPONS_PLUGIN_DOMAIN ), $coupon->amount, $amount_suffix );
				}
				break;

			case 'fixed_cart' :
			case 'percent' :
				$result = sprintf( __( '%s%s Discount', WOO_GROUPONS_PLUGIN_DOMAIN ), $coupon->amount, $amount_suffix );
				break;

			case 'sign_up_fee' :
			case 'sign_up_fee_percent' :
			case 'recurring_fee' :
			case 'recurring_percent' :
				$discount_name = __( 'Subscription Discount', WOO_GROUPONS_PLUGIN_DOMAIN );
				if ( $coupon->type == 'sign_up_fee' || $coupon->type == 'sign_up_fee_percent' ) {
					$discount_name = __( 'Sign Up Discount', WOO_GROUPONS_PLUGIN_DOMAIN );
				}
				if ( sizeof( $coupon->product_ids ) > 0 ) {
					if ( count( $products ) > 0 ) {
						$result = sprintf( __( '%s%s %s on %s', WOO_GROUPONS_PLUGIN_DOMAIN ), $coupon->amount, $amount_suffix, $discount_name, implode( $product_delimiter, $products ) );
					} else {
						$result = sprintf( __( '%s%s %s on selected products', WOO_GROUPONS_PLUGIN_DOMAIN ), $coupon->amount, $amount_suffix, $discount_name );
					}
				} else if ( sizeof( $coupon->product_categories ) > 0 ) {
					$result = sprintf( __( '%s%s %s in %s', WOO_GROUPONS_PLUGIN_DOMAIN ), $coupon->amount, $amount_suffix, $discount_name, implode( $category_delimiter, $categories ) );
				} else if ( sizeof( $coupon->exclude_product_ids ) > 0 || sizeof( $coupon->exclude_product_categories ) > 0 ) {
					$result = sprintf( __( '%s%s %s on selected products', WOO_GROUPONS_PLUGIN_DOMAIN ), $coupon->amount, $amount_suffix, $discount_name );
				} else {
					$result = sprintf( __( '%s%s %s', WOO_GROUPONS_PLUGIN_DOMAIN ), $coupon->amount, $amount_suffix, $discount_name );
				}
				break;
		}

		return apply_filters( 'woocommerce_group_coupon_discount_info', $result, $coupon );
	}

}
WooCommerce_Groupons_Shortcodes::init();

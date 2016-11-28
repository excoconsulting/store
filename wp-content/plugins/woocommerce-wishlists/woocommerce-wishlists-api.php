<?php

/*
 * Request Handlers - These functions will be moved into class-wc-wishlists-request-handler
 */

add_action( 'wp_loaded', 'woocommerce_wishlist_handle_add_to_wishlist_action', 9 );

function woocommerce_wishlist_handle_add_to_wishlist_action() {
	global $woocommerce, $woocommerce_wishlist;



	if ( !isset( $_REQUEST['add-to-wishlist-itemid'] ) || empty( $_REQUEST['add-to-wishlist-itemid'] ) ) {
		return;
	} else {
		remove_action( 'init', 'woocommerce_add_to_cart_action' );
		remove_action( 'wp_loaded', 'WC_Form_Handler::add_to_cart_action', 20 );
	}

	WC_Wishlists_User::set_cookie();

	//We need to force WooCommerce to set the session cookie.
	//We do this here since we need to make sure that the product pages are no longer cached. 
	if ( !is_admin() && !WC_Wishlist_Compatibility::WC()->session->has_session() ) {
		WC_Wishlist_Compatibility::WC()->session->set_customer_session_cookie( true );
	}

	if ( !is_user_logged_in() && (WC_Wishlists_Settings::get_setting( 'wc_wishlist_guest_enabled', 'enabled' ) == 'disabled') ) {
		return;
	}

	$wishlist_id = isset( $_REQUEST['wlid'] ) ? $_REQUEST['wlid'] : 0;
	if ( !$wishlist_id && (WC_Wishlists_Settings::get_setting( 'wc_wishlist_autocreate', 'yes' ) == 'yes') ) {
		$wishlist_id = WC_Wishlists_Wishlist::create_list( __( 'Wishlist', 'wc_wishlist' ) );

		//Wishlist created successfully.  Show messages
		if ( $wishlist_id ) {
			if ( is_user_logged_in() ) {
				$redirect_url = WC_Wishlists_Wishlist::get_the_url_edit( $wishlist_id );
				if ( WC_Wishlists_Settings::get_setting( 'woocommerce_wishlist_redirect_after_add', 'yes' ) == 'yes' && WC_Wishlist_Compatibility::wc_error_count() == 0 ) {
					$message = sprintf( __( 'This list has been automatically created for you.', 'wc_wishlist' ) );
					WC_Wishlist_Compatibility::wc_add_notice( apply_filters( 'woocommerce_wishlist_wishlist_created_message', $message ) );
				} else {
					$message = sprintf( __( 'A list has been created for you. <a href="%s">Manage list</a> ', 'wc_wishlist' ), $redirect_url );
					WC_Wishlist_Compatibility::wc_add_notice( apply_filters( 'woocommerce_wishlist_wishlist_created_message', $message ) );
				}
			} else {
				$myaccounturl = get_permalink( woocommerce_get_page_id( 'myaccount' ) );
				$redirect_url = WC_Wishlists_Wishlist::get_the_url_edit( $wishlist_id );
				$auth_url = apply_filters( 'woocommerce_wishlist_authentication_url', esc_url( add_query_arg( array('redirect' => urlencode( $redirect_url )), $myaccounturl ) ) );
				$register_url = apply_filters( 'woocommerce_wishlist_registration_url', esc_url( add_query_arg( array('redirect' => urlencode( $redirect_url )), $myaccounturl ) ) );
				$message = sprintf( __( 'A temporary list has been created for you. <a href="%s">Login</a> or <a href="%s">register for an account</a> to save this list for future use.  You may access this temporary list for up to 30 days or until you clear your browser history.', 'wc_wishlist' ), $auth_url, $register_url );
				WC_Wishlist_Compatibility::wc_add_notice( apply_filters( 'woocommerce_wishlist_wishlist_created_message', $message ) );
			}
		}
	} else {
		//Auto Create is disabled.  Require user to create a list manually.
	}

	if ( !$wishlist_id ) {
		WC_Wishlist_Compatibility::wc_add_notice( __( 'Unable to locate or create a list for you.  Please try again later', 'wc_wishlist' ), 'error' );
		wp_redirect( apply_filters( 'woocommerce_add_to_cart_product_id', get_permalink( $_REQUEST['product_id'] ) ) );
		exit;
	} else {
		
	}

	$added_to_wishlist = false;

	switch ( $_REQUEST['add-to-wishlist-type'] ) {

		// Variable Products
		case 'variable' :

			// Only allow integer variation ID - if its not set, redirect to the product page
			if ( empty( $_REQUEST['variation_id'] ) || !is_numeric( $_REQUEST['variation_id'] ) || $_REQUEST['variation_id'] < 1 ) {
				WC_Wishlist_Compatibility::wc_add_notice( __( 'Please choose product options&hellip;', 'woocommerce' ), 'error' );
				wp_redirect( apply_filters( 'woocommerce_add_to_cart_product_id', get_permalink( $_REQUEST['product_id'] ) ) );
				exit;
			}

			if ( WC_Wishlist_Compatibility::is_wc_version_gte_2_1() ) {

				$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['product_id'] ) );
				$adding_to_cart = get_product( $product_id );

				$variation_id = empty( $_REQUEST['variation_id'] ) ? '' : absint( $_REQUEST['variation_id'] );
				$quantity = empty( $_REQUEST['quantity'] ) ? 1 : apply_filters( 'woocommerce_stock_amount', $_REQUEST['quantity'] );
				$all_variations_set = true;
				$variations = array();

				// Only allow integer variation ID - if its not set, redirect to the product page
				if ( empty( $variation_id ) ) {
					wc_add_notice( __( 'Please choose product options&hellip;', 'woocommerce' ), 'error' );
					return;
				}

				$attributes = $adding_to_cart->get_attributes();
				$variation = get_product( $variation_id );

				// Verify all attributes
				foreach ( $attributes as $attribute ) {
					if ( !$attribute['is_variation'] )
						continue;

					$taxonomy = 'attribute_' . sanitize_title( $attribute['name'] );

					if ( isset( $_REQUEST[$taxonomy] ) ) {

						// Get value from post data
						// Don't use wc_clean as it destroys sanitized characters
						if ( $attribute['is_taxonomy'] ) {
							// Don't use wc_clean as it destroys sanitized characters
							$value = sanitize_title( stripslashes( $_REQUEST[$taxonomy] ) );
						} else {
							$value = wc_clean( stripslashes( $_REQUEST[$taxonomy] ) );
						}

						// Get valid value from variation
						$valid_value = $variation->variation_data[$taxonomy];

						// Allow if valid
						if ( '' === $valid_value || $valid_value === $value ) {

							// Pre 2.4 handling where 'slugs' were saved instead of the full text attribute
							if ( !$attribute['is_taxonomy'] ) {
								if ( $value === sanitize_title( $value ) && version_compare( get_post_meta( $product_id, '_product_version', true ), '2.4.0', '<' ) ) {
									$text_attributes = wc_get_text_attributes( $attribute['value'] );
									foreach ( $text_attributes as $text_attribute ) {
										if ( sanitize_title( $text_attribute ) === $value ) {
											$value = $text_attribute;
											break;
										}
									}
								}
							}

							$variations[$taxonomy] = $value;
							continue;
						}
					}

					$all_variations_set = false;
				}
			} else {
				// Get product ID to add and quantity
				$product_id = (int) apply_filters( 'woocommerce_add_to_cart_product_id', $_REQUEST['product_id'] );
				$variation_id = (int) $_REQUEST['variation_id'];
				$quantity = (isset( $_REQUEST['quantity'] )) ? (int) $_REQUEST['quantity'] : 1;
				$attributes = (array) maybe_unserialize( get_post_meta( $product_id, '_product_attributes', true ) );
				$variations = array();
				$all_variations_set = true;

				if ( function_exists( 'get_product' ) ) {
					$variation = get_product( $variation_id );
				} else {
					$variation = new WC_Product( $variation_id );
				}

				// Verify all attributes for the variable product were set
				// Verify all attributes
				foreach ( $attributes as $attribute ) {
					if ( !$attribute['is_variation'] )
						continue;

					$taxonomy = 'attribute_' . sanitize_title( $attribute['name'] );

					if ( !empty( $_REQUEST[$taxonomy] ) ) {

						// Get value from post data
						// Don't use woocommerce_clean as it destroys sanitized characters
						$value = sanitize_title( trim( stripslashes( $_REQUEST[$taxonomy] ) ) );

						// Get valid value from variation
						$valid_value = $variation->variation_data[$taxonomy];

						// Allow if valid
						if ( $valid_value == '' || $valid_value == $value ) {
							if ( $attribute['is_taxonomy'] )
								$variations[esc_html( $attribute['name'] )] = $value;
							else {
								// For custom attributes, get the name from the slug
								$options = array_map( 'trim', explode( '|', $attribute['value'] ) );
								foreach ( $options as $option ) {
									if ( sanitize_title( $option ) == $value ) {
										$value = $option;
										break;
									}
								}
								$variations[esc_html( $attribute['name'] )] = $value;
							}
							continue;
						}
					}

					$all_variations_set = false;
				}
			}


			if ( $all_variations_set ) {
				//Add to cart validation
				$passed_validation = apply_filters( 'woocommerce_add_to_wishlist_validation', apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity ), $product_id, $quantity );
				
				if ( $passed_validation ) {
					if ( WC_Wishlists_Wishlist_Item_Collection::add_item( $wishlist_id, $product_id, $quantity, $variation_id, $variations ) ) {
						$added_to_wishlist = true;
					}
				}
			} else {
				WC_Wishlist_Compatibility::wc_add_notice( __( 'Please choose product options&hellip;', 'woocommerce' ), 'error' );
				wp_redirect( apply_filters( 'woocommerce_add_to_cart_product_id', get_permalink( $_REQUEST['product_id'] ) ) );
				exit;
			}

			break;

		case 'group':
		case 'grouped' :

			if ( isset( $_REQUEST['quantity'] ) && is_array( $_REQUEST['quantity'] ) ) {

				$quantity_set = false;

				foreach ( $_REQUEST['quantity'] as $item => $quantity ) {
					if ( $quantity < 1 )
						continue;

					$quantity_set = true;

					//Add to cart validation
					$passed_validation = apply_filters( 'woocommerce_add_to_wishlist_validation', apply_filters( 'woocommerce_add_to_cart_validation', true, $item, $quantity ), $item, $quantity );

					if ( $passed_validation ) {
						if ( WC_Wishlists_Wishlist_Item_Collection::add_item( $wishlist_id, $item, $quantity ) ) {

							$added_to_wishlist = true;
						}
					}
				}

				if ( !$added_to_wishlist && !$quantity_set ) {
					WC_Wishlist_Compatibility::wc_add_notice( __( 'Please choose a quantity&hellip;', 'woocommerce' ), 'error' );

					$product_id = isset( $_REQUEST['product_id'] ) ? $_REQUEST['product_id'] : $_REQUEST['add-to-wishlist-itemid'];

					wp_redirect( apply_filters( 'woocommerce_add_to_cart_product_id', get_permalink( $product_id ) ) );
					exit;
				}
			} elseif ( $_REQUEST['add-to-wishlist-itemid'] ) {

				/* Link on product archives */
				WC_Wishlist_Compatibility::wc_add_notice( __( 'Please choose a product&hellip;', 'woocommerce' ), 'error' );
				wp_redirect( get_permalink( $_REQUEST['add-to-wishlist-itemid'] ) );
				exit;
			}

			break;

		// Simple Products - add-to-cart contains product ID
		default :

			//Only allow integers
			if ( !is_numeric( $_REQUEST['add-to-wishlist-itemid'] ) )
				break;

			//Get product ID to add and quantity
			$product_id = (int) $_REQUEST['add-to-wishlist-itemid'];
			$quantity = (isset( $_REQUEST['quantity'] )) ? (int) $_REQUEST['quantity'] : 1;

			//Add to cart validation
			$passed_validation = apply_filters( 'woocommerce_add_to_wishlist_validation', apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity ), $product_id, $quantity );

			if ( $passed_validation ) {
				//Add the product to the wishlist
				if ( WC_Wishlists_Wishlist_Item_Collection::add_item( $wishlist_id, (int) $_REQUEST['add-to-wishlist-itemid'], $quantity ) ) {

					$added_to_wishlist = true;
				}
			}

			break;
	}

	//If we added the product to the cart we can now do a redirect, otherwise just continue loading the page to show errors
	if ( $added_to_wishlist && $wishlist_id != 'session' ) {
		woocommerce_wishlist_add_to_wishlist_message( $wishlist_id );

		if ( !isset( $_REQUEST['wl_from_single_product'] ) || empty( $_REQUEST['wl_from_single_product'] ) ) {
			$url = add_query_arg( array('add-to-wishlist-itemid' => false) );
			wp_redirect( esc_url_raw( $url ) );
			die();
		}

		$url = apply_filters( 'add_to_wishlist_redirect_url', false, $wishlist_id );
		//If has custom URL redirect there
		if ( $url ) {
			wp_safe_redirect( $url );
			exit;
		} elseif ( WC_Wishlists_Settings::get_setting( 'woocommerce_wishlist_redirect_after_add_to_cart', 'yes' ) == 'yes' && WC_Wishlist_Compatibility::wc_error_count() == 0 ) {
			//Redirect to the wishlist
			wp_safe_redirect( WC_Wishlists_Wishlist::get_the_url_edit( $wishlist_id ) );
			die();
		} else {
			//Check for is product so we can add items from the shop page / and quick view.
			if ( is_numeric( $_REQUEST['add-to-wishlist-itemid'] ) ) {
				wp_redirect( get_permalink( $_REQUEST['add-to-wishlist-itemid'] ) );
				die();
			} elseif ( isset( $_GET['product_id'] ) ) {
				wp_redirect( get_permalink( $_GET['product_id'] ) );
				die();
			}
		}
	} elseif ( $wishlist_id == 'session' ) {

		if ( $added_to_wishlist ) {
			WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( '%s Items ready to move to a new list', 'wc_wishlist' ), 1 ) );
		}

		$wl_return_to = false;
		if ( isset( $_REQUEST['wl_from_single_product'] ) || !empty( $_REQUEST['wl_from_single_product'] ) ) {
			if ( is_numeric( $_REQUEST['add-to-wishlist-itemid'] ) ) {
				$wl_return_to = $_REQUEST['add-to-wishlist-itemid'];
			} elseif ( isset( $_GET['product_id'] ) ) {
				$wl_return_to = $_GET['product_id'];
			}
		}

		wp_safe_redirect( add_query_arg( array('wl_return_to' => $wl_return_to), WC_Wishlists_Pages::get_url_for( 'create-a-list' ) ) );
		die();
	}
}

function woocommerce_wishlist_add_to_wishlist_message( $wishlist_id = false ) {
	global $woocommerce, $woocommerce_wishlist;

	$wishlist = new WC_Wishlists_Wishlist( $wishlist_id );

// Output success messages
	if ( WC_Wishlists_Settings::get_setting( 'woocommerce_wishlist_redirect_after_add_to_cart', 'yes' ) == 'yes' ) :
		$return_to = (wp_get_referer()) ? wp_get_referer() : home_url();
		$message = sprintf( '<a href="%s" class="button">%s</a> %s', $return_to, __( 'Continue Shopping &rarr;', 'woocommerce' ), __( 'Product successfully added to your wishlist.', 'wc_wishlist' ) );
	else :
		$title = (get_the_title( $wishlist_id ));
		$view_list_url = WC_Wishlists_Wishlist::get_the_url_view( $wishlist_id );
		$edit_list_url = WC_Wishlists_Wishlist::get_the_url_edit( $wishlist_id );
		$list_user_settings_url = $edit_list_url . '#tab-wl-settings';
		$success_message = sprintf( __( 'Product successfully added to %s.', 'wc_wishlist' ), esc_html( $title ) );
		$message = sprintf( '<a href="%s" class="button">%s</a> %s', $edit_list_url, __( 'Manage Wishlist &rarr;', 'wc_wishlist' ), $success_message );
	endif;
	WC_Wishlist_Compatibility::wc_add_notice( apply_filters( 'woocommerce_wishlist_add_to_wishlist_message', $message ) );
}

add_action( 'init', 'woocommerce_wishlist_handle_share_via_email_action', 9 );

function woocommerce_wishlist_handle_share_via_email_action() {
	global $woocommerce, $woocommerce_wishlist, $phpmailer;

	if ( !isset( $_POST['wishlist-action'] ) || !($_POST['wishlist-action'] == 'share-via-email') ) {
		return;
	}

	if ( !WC_Wishlists_Plugin::verify_nonce( 'share-via-email' ) ) {
		return;
	}

	$wishlist_id = filter_input( INPUT_POST, 'wishlist_id', FILTER_SANITIZE_NUMBER_INT );

	if ( !$wishlist_id ) {
		WC_Wishlist_Compatibility::wc_add_notice( __( 'Action failed. Please refresh the page and retry.', 'woocommerce' ), 'error' );
		return;
	}

	$wishlist = new WC_Wishlists_Wishlist( $wishlist_id );
	if ( !$wishlist ) {
		WC_Wishlist_Compatibility::wc_add_notice( __( 'Action failed. Please refresh the page and retry.', 'woocommerce' ), 'error' );
		return;
	}

	if ( $wishlist->get_wishlist_sharing() == 'Private' ) {
		WC_Wishlist_Compatibility::wc_add_notice( __( 'Unable to share a private list.', 'woocommerce' ), 'error' );
		return;
	}

	$name = filter_input( INPUT_POST, 'wishlist_email_from', FILTER_SANITIZE_STRING );

	$to = filter_input( INPUT_POST, 'wishlist_email_to', FILTER_SANITIZE_STRIPPED );
	$content = filter_input( INPUT_POST, 'wishlist_content', FILTER_SANITIZE_STRIPPED );
	$name = $name ? $name : get_post_meta( $wishlist->id, '_wishlist_first_name', true ) . ' ' . get_post_meta( $wishlist->id, '_wishlist_last_name', true );
	$name = $name ? $name : __( 'Someone', 'wc_wishlist' );

	$body = '';

	$sent = 0;
	if ( $to ) {
		$addresses = explode( ',', $to );
		array_map( 'trim', $addresses );
		$clean_addresses = array();
		foreach ( $addresses as $address ) {
			$clean_addresses[] = filter_var( $address, FILTER_SANITIZE_EMAIL );
		}

		if ( count( $clean_addresses ) ) {
			$body .= '<br />' . sprintf( __( '%s has a list to share', 'wc_wishlist' ), $name ) .
				sprintf( __( ' on <a href="%s">%s</a>. %s You can view this list clicking on the link or copy and pasting it into your browser. <br />View List: <a href="%s">%s</a>', 'wc_wishlist' ), get_site_url(), get_bloginfo( 'name' ), $content, $wishlist->get_the_url_view( $wishlist_id, true ), $wishlist->get_the_url_view( $wishlist_id, true )
			);

			$body = apply_filters( 'woocommerce_wishlist_share_via_email_body', $body, $wishlist_id, $name, $to );

			add_filter( 'wp_mail_content_type', create_function( '', 'return "text/html"; ' ) );
			add_filter( 'wp_mail_from', 'woocommerce_wishlist_get_from_address' );

			$headers = '"Content-Type: "' . "text/html" . '"\r\n"';
			$sent = wp_mail( $clean_addresses, sprintf( __( '%s has a list to share', 'wc_wishlist' ), $name ), $body, $headers );

			remove_filter( 'wp_mail_from', 'woocommerce_wishlist_get_from_address' );
		}
	}

	if ( $sent ) {
		WC_Wishlist_Compatibility::wc_add_notice( __( 'Your email has been sent', 'wc_wishlist' ) );
	} elseif ( $sent === false ) {
		WC_Wishlist_Compatibility::wc_add_notice( __( 'Unable to send mail.  Please check your values and try again.', 'wc_wishlist' ) . ' ' . $phpmailer->ErrorInfo, 'error' );
	} else {
		WC_Wishlist_Compatibility::wc_add_notice( __( 'Unable to send mail.  Please check your values and try again.', 'wc_wishlist' ), 'error' );
	}
}

function woocommerce_wishlist_get_from_address() {
	return sanitize_email( get_option( 'woocommerce_email_from_address' ) );
}

/* == Ajax Actions === */
add_action( 'wp_ajax_woocommerce_remove_wishlist_item', 'woocommerce_wishlist_ajax_remove_item' );

function woocommerce_wishlist_ajax_remove_item() {
	global $woocommerce, $wpdb;

	check_ajax_referer( 'wishlist-item', 'security' );

	$wishlist_id = $_POST['wlid'];
	$wishlist_item_ids = $_POST['wishlist_item_ids'];

	if ( sizeof( $wishlist_item_ids ) > 0 ) {
		foreach ( $wishlist_item_ids as $id ) {
			WC_Wishlists_Wishlist_Item_Collection::remove_item( $wishlist_id, $id );
		}
	}

	die();
}

add_action( 'authenticate', 'woocommerce_wishlists_authenticate' );

function woocommerce_wishlists_authenticate( $user ) {
	global $wishlist_session_key;
	if ( !is_user_logged_in() ) {
		$wishlist_session_key = WC_Wishlists_User::get_wishlist_key();
	}
	return $user;
}

add_action( 'wp_login', 'woocommerce_wishlists_logon', 10, 2 );

function woocommerce_wishlists_logon( $user_login, $user ) {
	global $wishlist_session_key;
	if ( $wishlist_session_key ) {
		$lists = WC_Wishlists_User::get_wishlists( false, $wishlist_session_key );
		if ( $lists && count( $lists ) ) {
			foreach ( $lists as $list ) {
				WC_Wishlists_Wishlist::update_owner( $list->id, $user->ID, $wishlist_session_key );
			}
		}
	}
}

add_action( 'user_register', 'woocommerce_wishlists_register', 10, 1 );

function woocommerce_wishlists_register( $user_id ) {
	$wishlist_session_key = WC_Wishlists_User::get_wishlist_key();
	if ( $wishlist_session_key ) {
		$lists = WC_Wishlists_User::get_wishlists( false, $wishlist_session_key );
		if ( $lists && count( $lists ) ) {
			foreach ( $lists as $list ) {
				WC_Wishlists_Wishlist::update_owner( $list->id, $user_id, $wishlist_session_key );
			}
		}
	}
}

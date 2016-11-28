<?php
/**
 * Product Bundles Compatibility.
 *
 * @version 3.3.0
 * @since   3.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_CP_PB_Compatibility {

	public static function init() {

		// Bundles support
		add_action( 'woocommerce_add_cart_item', array( __CLASS__, 'bundled_cart_item_price_modification' ), 9, 2 );
		add_action( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'bundled_cart_item_session_price_modification' ), 9, 3 );

		add_action( 'woocommerce_add_cart_item', array( __CLASS__, 'bundled_cart_item_after_price_modification' ), 11 );
		add_action( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'bundled_cart_item_after_price_modification' ), 11 );
	}

	/**
	 * Add filters to modify bundled product prices when parent product is composited and has a discount.
	 *
	 * @param  array  $cart_item_data
	 * @param  string $cart_item_key
	 * @return void
	 */
	public static function bundled_cart_item_price_modification( $cart_item_data, $cart_item_key ) {

		if ( isset( $cart_item_data[ 'bundled_by' ] ) ) {

			$bundle_key = $cart_item_data[ 'bundled_by' ];

			if ( isset( WC()->cart->cart_contents[ $bundle_key ] ) ) {

				$bundle_cart_data = WC()->cart->cart_contents[ $bundle_key ];

				if ( isset( $bundle_cart_data[ 'composite_parent' ] ) ) {

					$composite_key = $bundle_cart_data[ 'composite_parent' ];

					if ( isset( WC()->cart->cart_contents[ $composite_key ] ) ) {

						$composite    = WC()->cart->cart_contents[ $composite_key ][ 'data' ];
						$component_id = $bundle_cart_data[ 'composite_item' ];

						WC_CP()->api->apply_composited_product_filters( $bundle_cart_data[ 'data' ], $component_id, $composite );
					}
				}
			}
		}

		return $cart_item_data;
	}

	/**
	 * Add filters to modify bundled product prices when parent product is composited and has a discount.
	 *
	 * @param  string $cart_item_data
	 * @param  array  $session_item_data
	 * @param  string $cart_item_key
	 * @return void
	 */
	public static function bundled_cart_item_session_price_modification( $cart_item_data, $session_item_data, $cart_item_key ) {
		return self::bundled_cart_item_price_modification( $cart_item_data, $cart_item_key );
	}

	/**
	 * Remove filters that modify bundled product prices when parent product is composited and has a discount.
	 *
	 * @param  string $cart_item_data
	 * @return void
	 */
	public static function bundled_cart_item_after_price_modification( $cart_item_data ) {

		if ( isset( $cart_item_data[ 'bundled_by' ] ) ) {

			$bundle_key = $cart_item_data[ 'bundled_by' ];

			if ( isset( WC()->cart->cart_contents[ $bundle_key ] ) ) {

				$bundle_cart_data = WC()->cart->cart_contents[ $bundle_key ];

				if ( isset( $bundle_cart_data[ 'composite_parent' ] ) ) {

					$composite_key = $bundle_cart_data[ 'composite_parent' ];

					if ( isset( WC()->cart->cart_contents[ $composite_key ] ) ) {

						WC_CP()->api->remove_composited_product_filters();
					}
				}
			}
		}

		return $cart_item_data;
	}
}

WC_CP_PB_Compatibility::init();

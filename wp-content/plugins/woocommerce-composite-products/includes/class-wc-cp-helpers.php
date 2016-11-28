<?php
/**
 * Helper functions.
 *
 * @class   WC_CP_Helpers
 * @version 3.6.9
 * @since   3.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_CP_Helpers {

	/**
	 * Filters the 'woocommerce_price_num_decimals' option to use the internal WC rounding precision.
	 *
	 * @return void
	 */
	public static function extend_price_display_precision() {
		add_filter( 'option_woocommerce_price_num_decimals', array( 'WC_CP_Core_Compatibility', 'wc_get_rounding_precision' ) );
	}

	/**
	 * Reset applied filters to the 'woocommerce_price_num_decimals' option.
	 *
	 * @return void
	 */
	public static function reset_price_display_precision() {
		remove_filter( 'option_woocommerce_price_num_decimals', array( 'WC_CP_Core_Compatibility', 'wc_get_rounding_precision' ) );
	}

	/**
	 * Loads variation ids for a given variable product.
	 *
	 * @param  int    $item_id
	 * @return array
	 */
	public static function get_product_variations( $item_id ) {

		if ( WC_CP_Core_Compatibility::is_wc_version_gte_2_4() ) {
			$transient_name = 'wc_product_children_' . $item_id;
			$transient      = get_transient( $transient_name );
			$variations     = isset( $transient[ 'all' ] ) && is_array( $transient[ 'all' ] ) ? $transient[ 'all' ] : false;
		} else {
			$transient_name = 'wc_product_children_ids_' . $this->id . WC_Cache_Helper::get_transient_version( 'product' );
			$variations     = get_transient( $transient_name );
		}

        if ( false === $variations ) {

			$args = array(
				'post_type'   => 'product_variation',
				'post_status' => array( 'publish' ),
				'numberposts' => -1,
				'orderby'     => 'menu_order',
				'order'       => 'asc',
				'post_parent' => $item_id,
				'fields'      => 'ids'
			);

			$variations = get_posts( $args );
		}

		return $variations;
	}

	/**
	 * Loads variation descriptions and ids for a given variable product.
	 *
	 * @param  int $item_id    product id
	 * @return array           array that contains variation ids => descriptions
	 */
	public static function get_product_variation_descriptions( $item_id ) {

		$variation_descriptions = array();

		$variations = self::get_product_variations( $item_id );

		if ( empty( $variations ) ) {
			return $variation_descriptions;
		}

		foreach ( $variations as $variation_id ) {

			$variation_description = self::get_product_variation_title( $variation_id );

			if ( ! $variation_description ) {
				continue;
			}

			$variation_descriptions[ $variation_id ] = $variation_description;
		}

		return $variation_descriptions;
	}

	/**
	 * Return a formatted product title based on variation id.
	 *
	 * @param  int    $item_id
	 * @return string
	 */
	public static function get_product_variation_title( $variation_id ) {

		if ( is_object( $variation_id ) ) {
			$variation = $variation_id;
		} else {
			$variation = wc_get_product( $variation_id );
		}

		if ( ! $variation ) {
			return false;
		}

		if ( WC_CP_Core_Compatibility::is_wc_version_gte_2_5() ) {
			$description = $variation->get_formatted_variation_attributes( true );
		} else {
			$description = wc_get_formatted_variation( $variation->get_variation_attributes(), true );
		}

		$title = $variation->get_title();
		$sku   = $variation->get_sku();

		if ( $sku ) {
			$identifier = $sku;
		} else {
			$identifier = '#' . $variation->variation_id;
		}

		return self::format_product_title( $title, $identifier, $description );
	}

	/**
	 * Return a formatted product title based on id.
	 *
	 * @param  int    $product_id
	 * @return string
	 */
	public static function get_product_title( $product_id ) {

		if ( is_object( $product_id ) ) {
			$title = $product_id->get_title();
			$sku   = $product_id->get_sku();
			$id    = $product_id->id;
		} else {
			$title = get_the_title( $product_id );
			$sku   = get_post_meta( $product_id, '_sku', true );
			$id    = $product_id;
		}

		if ( ! $title ) {
			return false;
		}

		if ( $sku ) {
			$identifier = $sku;
		} else {
			$identifier = '#' . $id;
		}

		return self::format_product_title( $title, $identifier );
	}

	/**
	 * Format a product title.
	 *
	 * @param  string $title
	 * @param  string $identifier
	 * @param  string $meta
	 * @param  string $paren
	 * @return string
	 */
	public static function format_product_title( $title, $identifier = '', $meta = '', $paren = false ) {

		if ( $identifier && $meta ) {
			if ( $paren ) {
				$title = sprintf( _x( '%1$s &mdash; %2$s (%3$s)', 'product title followed by meta and sku in parenthesis', 'woocommerce-composite-products' ), $title, $meta, $identifier );
			} else {
				$title = sprintf( _x( '%1$s &ndash; %2$s &mdash; %3$s', 'sku followed by product title and meta', 'woocommerce-composite-products' ), $identifier, $title, $meta );
			}
		} elseif ( $identifier ) {
			if ( $paren ) {
				$title = sprintf( _x( '%1$s (%2$s)', 'product title followed by sku in parenthesis', 'woocommerce-composite-products' ), $title, $identifier );
			} else {
				$title = sprintf( _x( '%1$s &ndash; %2$s', 'sku followed by product title', 'woocommerce-composite-products' ), $identifier, $title );
			}
		} elseif ( $meta ) {
			$title = sprintf( _x( '%1$s &mdash; %2$s', 'product title followed by meta', 'woocommerce-composite-products' ), $title, $meta );
		}

		return $title;
	}
}

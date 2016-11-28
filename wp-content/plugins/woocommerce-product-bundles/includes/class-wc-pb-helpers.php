<?php
/**
 * Product Bundle Helper Functions.
 *
 * @class   WC_PB_Helpers
 * @version 4.14.1
 * @since   4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_PB_Helpers {

	public static $bundled_item;

	public static $cache = array();

	/**
	 * Simple cache getter.
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public static function cache_get( $key ) {
		$value = null;
		if ( isset( self::$cache[ $key ] ) ) {
			$value = self::$cache[ $key ];
		}
		return $value;
	}

	/**
	 * Simple cache setter.
	 *
	 * @param  string $key
	 * @param  mixed  $value
	 * @return void
	 */
	public static function cache_set( $key, $value ) {
		self::$cache[ $key ] = $value;
	}

	/**
	 * True when processing a FE request.
	 *
	 * @return boolean
	 */
	public static function is_front_end() {
		$is_fe = ( ! is_admin() ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		return $is_fe;
	}

	/**
	 * Filters the 'woocommerce_price_num_decimals' option to use the internal WC rounding precision.
	 *
	 * @return void
	 */
	public static function extend_price_display_precision() {
		add_filter( 'option_woocommerce_price_num_decimals', array( 'WC_PB_Core_Compatibility', 'wc_get_rounding_precision' ) );
	}

	/**
	 * Reset applied filters to the 'woocommerce_price_num_decimals' option.
	 *
	 * @return void
	 */
	public static function reset_price_display_precision() {
		remove_filter( 'option_woocommerce_price_num_decimals', array( 'WC_PB_Core_Compatibility', 'wc_get_rounding_precision' ) );
	}

	/**
	 * Calculates bundled product prices incl. or excl. tax depending on the 'woocommerce_tax_display_shop' setting.
	 *
	 * @param  WC_Product   $product    the product
	 * @param  double       $price      the product price
	 * @return double                   modified product price incl. or excl. tax
	 */
	public static function get_product_display_price( $product, $price ) {

		if ( ! $price ) {
			return $price;
		}

		if ( get_option( 'woocommerce_tax_display_shop' ) === 'excl' ) {
			$product_price = $product->get_price_excluding_tax( 1, $price );
		} else {
			$product_price = $product->get_price_including_tax( 1, $price );
		}

		return $product_price;
	}

	/**
	 * Returns the recurring price component of a subscription product.
	 *
	 * @param  WC_Product $product
	 * @return string
	 */
	public static function get_recurring_price_html_component( $product ) {

		$sync_date = $product->subscription_payment_sync_date;
		$product->subscription_payment_sync_date = 0;

		$sub_price_html = WC_Subscriptions_Product::get_price_string( $product, array( 'price' => '%s', 'sign_up_fee' => false ) );

		$product->subscription_payment_sync_date = $sync_date;

		return $sub_price_html;
	}

	/**
	 * Loads variation ids for a given variable product.
	 *
	 * @param  int    $item_id
	 * @return array
	 */
	public function get_product_variations( $item_id ) {

		if ( WC_PB_Core_Compatibility::is_wc_version_gte_2_4() ) {
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
	 * Return a formatted product title based on id.
	 *
	 * @param  int    $product_id
	 * @return string
	 */
	public function get_product_title( $product_id, $suffix = '' ) {

		$title = get_the_title( $product_id );

		if ( $suffix ) {
			$title = sprintf( _x( '%1$s %2$s', 'product title followed by suffix', 'woocommerce-product-bundles' ), $title, $suffix );
		}

		$sku = get_post_meta( $product_id, '_sku', true );

		if ( ! $title ) {
			return false;
		}

		if ( $sku ) {
			$sku = sprintf( __( 'SKU: %s', 'woocommerce-product-bundles' ), $sku );
		} else {
			$sku = '';
		}

		return self::format_product_title( $title, $sku, '', true );
	}

	/**
	 * Return a formatted product title based on variation id.
	 *
	 * @param  int    $item_id
	 * @return string
	 */
	public function get_product_variation_title( $variation_id ) {

		if ( is_object( $variation_id ) ) {
			$variation = $variation_id;
		} else {
			$variation = wc_get_product( $variation_id );
		}

		if ( ! $variation ) {
			return false;
		}

		if ( WC_PB_Core_Compatibility::is_wc_version_gte_2_5() ) {
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
	 * Format a product title.
	 *
	 * @param  string  $title
	 * @param  string  $sku
	 * @param  string  $meta
	 * @param  boolean $paren
	 * @return string
	 */
	public static function format_product_title( $title, $sku = '', $meta = '', $paren = false ) {

		if ( $sku && $meta ) {
			if ( $paren ) {
				$title = sprintf( _x( '%1$s &mdash; %2$s (%3$s)', 'product title followed by meta and sku in parenthesis', 'woocommerce-product-bundles' ), $title, $meta, $sku );
			} else {
				$title = sprintf( _x( '%1$s &ndash; %2$s &ndash; %3$s', 'sku followed by product title and meta', 'woocommerce-product-bundles' ), $sku, $title, $meta );
			}
		} elseif ( $sku ) {
			if ( $paren ) {
				$title = sprintf( _x( '%1$s (%2$s)', 'product title followed by sku in parenthesis', 'woocommerce-product-bundles' ), $title, $sku );
			} else {
				$title = sprintf( _x( '%1$s &ndash; %2$s', 'sku followed by product title', 'woocommerce-product-bundles' ), $sku, $title );
			}
		} elseif ( $meta ) {
			$title = sprintf( _x( '%1$s &mdash; %2$s', 'product title followed by meta', 'woocommerce-product-bundles' ), $title, $meta );
		}

		return $title;
	}

	/**
	 * Format a product title incl qty, price and suffix.
	 *
	 * @param  string $title
	 * @param  string $qty
	 * @param  string $price
	 * @param  string $suffix
	 * @return string
	 */
	public static function format_product_shop_title( $title, $qty = '', $price = '', $suffix = '' ) {

		$quantity_string = '';
		$price_string    = '';
		$suffix_string   = '';

		if ( $qty ) {
			$quantity_string = sprintf( _x( ' &times; %s', 'qty string', 'woocommerce-product-bundles' ), $qty );
		}

		if ( $price ) {
			$price_string = sprintf( _x( ' &ndash; %s', 'price suffix', 'woocommerce-product-bundles' ), $price );
		}

		if ( $suffix ) {
			$suffix_string = sprintf( _x( ' &ndash; %s', 'suffix', 'woocommerce-product-bundles' ), $suffix );
		}

		$title_string = sprintf( _x( '%1$s%2$s%3$s%4$s', 'title, quantity, price, suffix', 'woocommerce-product-bundles' ), $title, $quantity_string, $price_string, $suffix_string );

		return $title_string;
	}

	/**
	 * Add price filters to modify child product prices depending on the per-product pricing option state, including any discounts defined at bundled item level.
	 *
	 * @param   WC_Bundled_Item $bundled_item
	 * @return  void
	 */
	public static function add_price_filters( $bundled_item ) {

		self::$bundled_item = $bundled_item;

		add_filter( 'woocommerce_get_price', array( __CLASS__, 'filter_get_price' ), 15, 2 );
		add_filter( 'woocommerce_get_sale_price', array( __CLASS__, 'filter_get_sale_price' ), 15, 2 );
		add_filter( 'woocommerce_get_regular_price', array( __CLASS__, 'filter_get_regular_price' ), 15, 2 );
		add_filter( 'woocommerce_get_price_html', array( __CLASS__, 'filter_get_price_html' ), 10, 2 );
		add_filter( 'woocommerce_show_variation_price', array( __CLASS__, 'filter_show_variation_price' ), 10, 3 );
		add_filter( 'woocommerce_get_variation_price_html', array( __CLASS__, 'filter_get_price_html' ), 10, 2 );
		add_filter( 'woocommerce_variation_prices', array( __CLASS__, 'filter_get_variation_prices' ), 10, 3 );
	}

	/**
	 * Remove price filters after modifying child product prices depending on the per-product pricing option state, including any discounts defined at bundled item level.
	 *
	 * @return  void
	 */
	public static function remove_price_filters() {

		self::$bundled_item = false;

		remove_filter( 'woocommerce_get_price', array( __CLASS__, 'filter_get_price' ), 15, 2 );
		remove_filter( 'woocommerce_get_sale_price', array( __CLASS__, 'filter_get_sale_price' ), 15, 2 );
		remove_filter( 'woocommerce_get_regular_price', array( __CLASS__, 'filter_get_regular_price' ), 15, 2 );
		remove_filter( 'woocommerce_get_price_html', array( __CLASS__, 'filter_get_price_html' ), 10, 2 );
		remove_filter( 'woocommerce_show_variation_price', array( __CLASS__, 'filter_show_variation_price' ), 10, 3 );
		remove_filter( 'woocommerce_get_variation_price_html', array( __CLASS__, 'filter_get_price_html' ), 10, 2 );
		remove_filter( 'woocommerce_variation_prices', array( __CLASS__, 'filter_get_variation_prices' ), 10, 3 );
	}

	/**
	 * Filter get_variation_prices() calls for bundled products to include discounts.
	 *
	 * @param  array                $prices_array
	 * @param  WC_Product_Variable  $product
	 * @param  boolean              $display
	 * @return array
	 */
	public static function filter_get_variation_prices( $prices_array, $product, $display ) {

		$bundled_item = self::$bundled_item;

		if ( $bundled_item ) {

			$prices         = array();
			$regular_prices = array();
			$sale_prices    = array();

			$discount_from_regular = apply_filters( 'woocommerce_bundled_item_discount_from_regular', true, $bundled_item );
			$discount              = $bundled_item->get_discount();
			$priced_per_product    = $bundled_item->is_priced_per_product();

			// Filter regular prices.
			foreach ( $prices_array[ 'regular_price' ] as $variation_id => $regular_price ) {
				if ( $priced_per_product ) {
					$regular_prices[ $variation_id ] = $regular_price === '' ? $prices_array[ 'price' ][ $variation_id ] : $regular_price;
				} else {
					$regular_prices[ $variation_id ] = 0;
				}
			}

			// Filter prices.
			foreach ( $prices_array[ 'price' ] as $variation_id => $price ) {
				if ( $priced_per_product ) {
					if ( $discount_from_regular ) {
						$regular_price = $regular_prices[ $variation_id ];
					} else {
						$regular_price = $price;
					}
					$price                   = empty( $discount ) ? $price : round( ( double ) $regular_price * ( 100 - $discount ) / 100, WC_PB_Core_Compatibility::wc_get_price_decimals() );
					$prices[ $variation_id ] = apply_filters( 'woocommerce_bundled_variation_price', $price, $variation_id, $discount, $bundled_item );
				} else {
					$prices[ $variation_id ] = 0;
				}
			}

			// Filter sale prices.
			foreach ( $prices_array[ 'sale_price' ] as $variation_id => $sale_price ) {
				if ( $priced_per_product ) {
					$sale_prices[ $variation_id ] = empty( $discount ) ? $sale_price : $prices[ $variation_id ];
				} else {
					$sale_prices[ $variation_id ] = 0;
				}
			}

			$prices_array = array(
				'price'         => $prices,
				'regular_price' => $regular_prices,
				'sale_price'    => $sale_prices
			);
		}

		return $prices_array;
	}

	/**
	 * Filter condition for allowing WC to calculate variation price_html.
	 *
	 * @param  boolean              $show
	 * @param  WC_Product_Variable  $product
	 * @param  WC_Product_Variation $variation
	 * @return boolean
	 */
	public static function filter_show_variation_price( $show, $product, $variation ) {

		$bundled_item = self::$bundled_item;

		if ( $bundled_item ) {

			if ( $bundled_item->is_priced_per_product() && $bundled_item->max_price > 0 && $bundled_item->max_price > $bundled_item->min_price ) {
				$show = true;
			}
		}

		return $show;
	}

	/**
	 * Filter get_price() calls for bundled products to include discounts.
	 *
	 * @param  double       $price      unmodified price
	 * @param  WC_Product   $product    the bundled product
	 * @return double                   modified price
	 */
	public static function filter_get_price( $price, $product ) {

		$bundled_item = self::$bundled_item;

		if ( $bundled_item ) {

			if ( $price === '' ) {
				return $price;
			}

			if ( ! $bundled_item->is_priced_per_product() ) {
				return 0;
			}

			if ( apply_filters( 'woocommerce_bundled_item_discount_from_regular', true, $bundled_item ) ) {
				$regular_price = $product->get_regular_price();
			} else {
				$regular_price = $price;
			}

			$discount                    = $bundled_item->get_discount();
			$bundled_item_price          = empty( $discount ) ? $price : ( empty( $regular_price ) ? $regular_price : round( ( double ) $regular_price * ( 100 - $discount ) / 100, WC_PB_Core_Compatibility::wc_get_price_decimals() ) );

			$product->bundled_item_price = $bundled_item_price;

			$price = apply_filters( 'woocommerce_bundled_item_price', $bundled_item_price, $product, $discount, $bundled_item );
		}

		return $price;
	}

	/**
	 * Filter get_regular_price() calls for bundled products to include discounts.
	 *
	 * @param  double       $price      unmodified reg price
	 * @param  WC_Product   $product    the bundled product
	 * @return double                   modified reg price
	 */
	public static function filter_get_regular_price( $regular_price, $product ) {

		$bundled_item = self::$bundled_item;

		if ( $bundled_item ) {

			if ( ! $bundled_item->is_priced_per_product() ) {
				return 0;
			}

			$regular_price = empty( $regular_price ) ? $product->price : $regular_price;
		}

		return $regular_price;
	}

	/**
	 * Filter get_sale_price() calls for bundled products to include discounts.
	 *
	 * @param  double       $price      unmodified reg price
	 * @param  WC_Product   $product    the bundled product
	 * @return double                   modified reg price
	 */
	public static function filter_get_sale_price( $sale_price, $product ) {

		$bundled_item = self::$bundled_item;

		if ( $bundled_item ) {

			if ( ! $bundled_item->is_priced_per_product() ) {
				return 0;
			}

			$discount   = $bundled_item->get_discount();
			$sale_price = empty( $discount ) ? $sale_price : self::filter_get_price( $product->price, $product );
		}

		return $sale_price;
	}

	/**
	 * Filter the html price string of bundled items to show the correct price with discount and tax - needs to be hidden in per-product pricing mode.
	 *
	 * @param  string      $price_html    unmodified price string
	 * @param  WC_Product  $product       the bundled product
	 * @return string                     modified price string
	 */
	public static function filter_get_price_html( $price_html, $product ) {

		$bundled_item = self::$bundled_item;

		if ( $bundled_item ) {

			if ( ! $bundled_item->is_priced_per_product() ) {
				return '';
			}

			$quantity   = $bundled_item->get_quantity();
			/* translators: for quantity use %2$s */
			$price_html = apply_filters( 'woocommerce_bundled_item_price_html', $quantity > 1 ? sprintf( __( '%1$s <span class="bundled_item_price_quantity">/ pc.</span>', 'woocommerce-product-bundles' ), $price_html, $quantity ) : $price_html, $price_html, $bundled_item );
		}

		return $price_html;
	}

	/**
	 * Updates post_meta v1 storage scheme (scattered post_meta) to v2 (serialized post_meta).
	 *
	 * @param  int    $bundle_id     bundle product_id
	 * @return void
	 */
	public static function serialize_bundle_meta( $bundle_id ) {

		global $wpdb;

		$bundled_item_ids   = maybe_unserialize( get_post_meta( $bundle_id, '_bundled_ids', true ) );
		$default_attributes = maybe_unserialize( get_post_meta( $bundle_id, '_bundle_defaults', true ) );
		$allowed_variations = maybe_unserialize( get_post_meta( $bundle_id, '_allowed_variations', true ) );

		$bundle_data = array();

		foreach ( $bundled_item_ids as $bundled_item_id ) {

			$bundle_data[ $bundled_item_id ] = array();

			$filtered       = get_post_meta( $bundle_id, 'filter_variations_' . $bundled_item_id, true );
			$o_defaults     = get_post_meta( $bundle_id, 'override_defaults_' . $bundled_item_id, true );
			$hide_thumbnail = get_post_meta( $bundle_id, 'hide_thumbnail_' . $bundled_item_id, true );
			$item_o_title   = get_post_meta( $bundle_id, 'override_title_' . $bundled_item_id, true );
			$item_title     = get_post_meta( $bundle_id, 'product_title_' . $bundled_item_id, true );
			$item_o_desc    = get_post_meta( $bundle_id, 'override_description_' . $bundled_item_id, true );
			$item_desc      = get_post_meta( $bundle_id, 'product_description_' . $bundled_item_id, true );
			$item_qty       = get_post_meta( $bundle_id, 'bundle_quantity_' . $bundled_item_id, true );
			$discount       = get_post_meta( $bundle_id, 'bundle_discount_' . $bundled_item_id, true );
			$visibility     = get_post_meta( $bundle_id, 'visibility_' . $bundled_item_id, true );

			$sep = explode( '_', $bundled_item_id );

			$bundle_data[ $bundled_item_id ][ 'product_id' ]        = $sep[0];
			$bundle_data[ $bundled_item_id ][ 'filter_variations' ] = ( $filtered === 'yes' ) ? 'yes' : 'no';

			if ( isset( $allowed_variations[ $bundled_item_id ] ) ) {
				$bundle_data[ $bundled_item_id ][ 'allowed_variations' ] = $allowed_variations[ $bundled_item_id ];
			}

			$bundle_data[ $bundled_item_id ][ 'override_defaults' ] = ( $o_defaults === 'yes' ) ? 'yes' : 'no';

			if ( isset( $default_attributes[ $bundled_item_id ] ) ) {
				$bundle_data[ $bundled_item_id ][ 'bundle_defaults' ] = $default_attributes[ $bundled_item_id ];
			}

			$bundle_data[ $bundled_item_id ][ 'hide_thumbnail' ] = ( $hide_thumbnail === 'yes' ) ? 'yes' : 'no';
			$bundle_data[ $bundled_item_id ][ 'override_title' ] = ( $item_o_title === 'yes' ) ? 'yes' : 'no';

			if ( $item_o_title === 'yes' ) {
				$bundle_data[ $bundled_item_id ][ 'product_title' ] = $item_title;
			}

			$bundle_data[ $bundled_item_id ][ 'override_description' ] = ( $item_o_desc === 'yes' ) ? 'yes' : 'no';

			if ( $item_o_desc === 'yes' ) {
				$bundle_data[ $bundled_item_id ][ 'product_description' ] = $item_desc;
			}

			$bundle_data[ $bundled_item_id ][ 'bundle_quantity' ]          = $item_qty;
			$bundle_data[ $bundled_item_id ][ 'bundle_discount' ]          = $discount;
			$bundle_data[ $bundled_item_id ][ 'visibility' ]               = ( $visibility === 'hidden' ) ? 'hidden' : 'visible';
			$bundle_data[ $bundled_item_id ][ 'hide_filtered_variations' ] = 'no';
		}

		update_post_meta( $bundle_id, '_bundle_data', $bundle_data );

		$wpdb->query( $wpdb->prepare( "DELETE FROM `$wpdb->postmeta` WHERE `post_id` LIKE %s AND (
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE %s OR
			`meta_key` LIKE ('_bundled_ids') OR
			`meta_key` LIKE ('_bundle_defaults') OR
			`meta_key` LIKE ('_allowed_variations')
		)", $bundle_id, 'filter_variations_%', 'override_defaults_%', 'bundle_quantity_%', 'bundle_discount_%', 'hide_thumbnail_%', 'override_title_%', 'product_title_%', 'override_description_%', 'product_description_%', 'hide_filtered_variations_%', 'visibility_%' ) );

		return $bundle_data;
	}

	/**
	 * Filter variable subscription product 'from' text.
	 *
	 * @param  string     $text
	 * @param  WC_Product $product
	 * @return string
	 */
	public static function filter_variable_sub_html_from_text( $text, $product ) {
		return _x( 'from ', 'variable sub price html from text', 'woocommerce-product-bundles' );
	}

	/**
	 * Calculates bundled product prices incl. or excl. tax depending on the 'woocommerce_tax_display_shop' setting.
	 *
	 * @param  WC_Product   $product    the product
	 * @param  double       $price      the product price
	 * @return double                   modified product price incl. or excl. tax
	 */
	public function get_product_price_incl_or_excl_tax( $product, $price ) {
		_deprecated_function( 'get_product_price_incl_or_excl_tax', '4.11.4', 'get_product_display_price' );
		return self::get_product_display_price( $product, $price );
	}
}

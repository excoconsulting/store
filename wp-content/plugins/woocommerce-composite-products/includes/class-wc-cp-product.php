<?php
/**
 * Composited product wrapper class.
 *
 * @class   WC_CP_Product
 * @version 3.6.4
 * @since   2.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

class WC_CP_Product {

	private $product;

	/**
	 * Raw meta prices used in the min/max composite price calculation.
	 * @var string
	 */
	public  $min_price;
	public  $max_price;
	public  $min_regular_price;
	public  $max_regular_price;

	public $min_price_product;
	public $max_price_product;
	public $min_regular_price_product;
	public $max_regular_price_product;

	private $is_nyp;
	private $is_purchasable;

	private $component_data;
	private $component_id;
	private $composite;

	private $per_product_pricing;

	public function __construct( $product_id, $component_id, $parent ) {

		$this->product = wc_get_product( $product_id );

		if ( $this->product ) {

			$this->component_data      = $parent->get_component_data( $component_id );
			$this->component_id        = $component_id;
			$this->per_product_pricing = $parent->is_priced_per_product();
			$this->composite           = $parent;

			$this->init();
		}
	}

	/**
	 * Initialize composited product price data, if needed.
	 *
	 * @return void
	 */
	public function init() {

		// Init prices
		$this->min_price          = 0;
		$this->max_price          = 0;
		$this->min_regular_price  = 0;
		$this->max_regular_price  = 0;

		$this->min_price_incl_tax = 0;
		$this->min_price_excl_tax = 0;

		$id = $this->get_product()->id;

		// Purchasable status.
		if ( ! $this->per_product_pricing && $this->product->price === '' ) {
			$this->product->price = 0;
		}

		$this->is_purchasable = $this->product->is_purchasable();

		// Calculate product prices.
		if ( $this->per_product_pricing && $this->is_purchasable ) {

			$composited_product = $this->product;
			$product_type       = $composited_product->product_type;

			$this->is_nyp       = false;

			/*-----------------------------------------------------------------------------------*/
			/*  Simple Products and Static Bundles.                                              */
			/*-----------------------------------------------------------------------------------*/

			if ( $product_type === 'simple' ) {

				$product_price         = $this->get_raw_price();
				$product_regular_price = $this->get_raw_regular_price();

				// Name your price support.
				if ( WC_CP()->compatibility->is_nyp( $composited_product ) ) {
					$product_price        = $product_regular_price = WC_Name_Your_Price_Helpers::get_minimum_price( $id ) ? WC_Name_Your_Price_Helpers::get_minimum_price( $id ) : 0;
					$this->product->price = $this->product->regular_price = $this->product->sale_price = $product_price;
					$this->is_nyp         = true;
				}

				$this->min_price              = $this->max_price         = $product_price;
				$this->min_regular_price      = $this->max_regular_price = $product_regular_price;

			/*-----------------------------------------------------------------------------------*/
			/*  Variable Products.                                                               */
			/*-----------------------------------------------------------------------------------*/

			} elseif ( $product_type === 'variable' ) {

				/**
				 * Filter to control whether component-level discounts are applied on top of the regular or sale price.
				 *
				 * @param  boolean  $discount_from_regular
				 * @param  string   $component_id
				 * @param  string   $composite_id
				 */
				$discount_from_regular = apply_filters( 'woocommerce_composited_product_discount_from_regular', true, $this->component_id, $this->composite->id );

				if ( WC_CP_Core_Compatibility::is_wc_version_gte_2_4() ) {

					$variation_prices = $composited_product->get_variation_prices( false );

					if ( ! empty( $this->component_data[ 'discount' ] ) && $discount_from_regular ) {
						$variation_price_ids = array_keys( $variation_prices[ 'regular_price' ] );
					} else {
						$variation_price_ids = array_keys( $variation_prices[ 'price' ] );
					}

					$min_variation_price_id = current( $variation_price_ids );
					$max_variation_price_id = end( $variation_price_ids );

				} else {

					if ( ! empty( $this->component_data[ 'discount' ] ) && $discount_from_regular ) {

						// Product may need to be synced.
						if ( $composited_product->get_variation_regular_price( 'min', false ) === false ) {
							$composited_product->variable_product_sync();
						}

						$min_variation_price_id = get_post_meta( $this->product->id, '_min_regular_price_variation_id', true );
						$max_variation_price_id = get_post_meta( $this->product->id, '_max_regular_price_variation_id', true );

					} else {

						// Product may need to be synced.
						if ( $composited_product->get_variation_price( 'min', false ) === false ) {
							$composited_product->variable_product_sync();
						}

						$min_variation_price_id = get_post_meta( $this->product->id, '_min_price_variation_id', true );
						$max_variation_price_id = get_post_meta( $this->product->id, '_max_price_variation_id', true );
					}
				}

				$min_variation = $composited_product->get_child( $min_variation_price_id );
				$max_variation = $composited_product->get_child( $max_variation_price_id );

				if ( $min_variation && $max_variation ) {

					$this->min_price_product = $this->min_regular_price_product = $min_variation;
					$this->max_price_product = $this->min_regular_price_product = $max_variation;

					$this->min_price             = $this->get_raw_price( $min_variation );
					$this->max_price             = $this->get_raw_price( $max_variation );
					$min_variation_regular_price = $this->get_raw_regular_price( $min_variation );
					$max_variation_regular_price = $this->get_raw_regular_price( $max_variation );

					// The variation with the lowest price may have a higher regular price then the variation with the highest price.
					if ( $max_variation_regular_price < $min_variation_regular_price ) {
						$this->min_regular_price_product = $max_variation;
						$this->max_regular_price_product = $min_variation;
					}

					$this->min_regular_price = min( $min_variation_regular_price, $max_variation_regular_price );
					$this->max_regular_price = max( $min_variation_regular_price, $max_variation_regular_price );
				}

			/*-----------------------------------------------------------------------------------*/
			/*  Other Product Types.                                                             */
			/*-----------------------------------------------------------------------------------*/

			} else {

				$price = $this->get_raw_price();

				/**
				 * Filter the raw min price.
				 *
				 * @param  string         $price
				 * @param  WC_CP_Product  $cp_product
				 */
				$this->min_price         = apply_filters( 'woocommerce_composited_product_min_price', $price, $this );

				/**
				 * Filter the raw max price.
				 *
				 * @param  string         $price
				 * @param  WC_CP_Product  $cp_product
				 */
				$this->max_price         = apply_filters( 'woocommerce_composited_product_max_price', $price, $this );

				$regular_price = $this->get_raw_regular_price();

				/**
				 * Filter the raw min regular price.
				 *
				 * @param  string         $price
				 * @param  WC_CP_Product  $cp_product
				 */
				$this->min_regular_price = apply_filters( 'woocommerce_composited_product_min_regular_price', $regular_price, $this );

				/**
				 * Filter the raw max regular price.
				 *
				 * @param  string         $price
				 * @param  WC_CP_Product  $cp_product
				 */
				$this->max_regular_price = apply_filters( 'woocommerce_composited_product_max_regular_price', $regular_price, $this );

				/**
				 * Filter the NYP status of the product.
				 *
				 * @param  string         $price
				 * @param  WC_CP_Product  $cp_product
				 */
				$this->is_nyp            = apply_filters( 'woocommerce_composited_product_is_nyp', $this->is_nyp, $this );

				if ( $this->is_nyp ) {
					$this->product->price = $this->product->regular_price = $this->product->sale_price = $this->min_price;
				}
			}
		}
	}

	/**
	 * Wrapper for 'get_price_html()' that applies price filters.
	 *
	 * @return string
	 */
	public function get_price_html() {

		$price_html = '';

		if ( $this->is_purchasable ) {
			$this->add_filters();
			$price_html = $this->get_product()->get_price_html();
			$this->remove_filters();
		}

		return $price_html;
	}

	/**
	 * Generated dropdown price string for composited products in per product pricing mode.
	 *
	 * @return string
	 */
	public function get_price_string() {

		if ( ! $this->exists() ) {
			return false;
		}

		$price_string = '';
		$component_id = $this->component_id;
		$product_id   = $this->get_product()->id;

		if ( $this->per_product_pricing && $this->is_purchasable ) {

			$discount = $sale = '';

			$has_multiple = ! $this->is_sold_individually() && $this->component_data[ 'quantity_min' ] > 1;



			$ref_price = $this->get_regular_price( 'min', true, true );
			$price     = $this->get_price( 'min', true );
			$is_nyp    = $this->is_nyp;
			$is_range  = $price < $this->get_price( 'max', true );

			if ( ! empty( $this->component_data[ 'discount' ] ) && $ref_price > 0 && ! $is_nyp && $this->get_product() && $this->get_product()->product_type !== 'bundle' ) {
				$discount = sprintf( __( '(%s%% off)', 'woocommerce-composite-products' ), round( $this->component_data[ 'discount' ], 1 ) );
			}

			if ( ! $discount && $ref_price > $price && $ref_price > 0 && ! $is_nyp ) {
				$sale = sprintf( __( '(%s%% off)', 'woocommerce-composite-products' ), round( 100 * ( $ref_price - $price ) / $ref_price, 1 ) );
			}

			$pct_off = $discount . $sale;

			/**
			 * Filter the composited product price string suffix.
			 *
			 * @param  string         $percent_off
			 * @param  string         $component_id
			 * @param  string         $product_id
			 * @param  string         $price
			 * @param  string         $ref_price
			 * @param  boolean        $is_nyp
			 * @param  boolean        $is_range
			 * @param  WC_CP_Product  $composited_product
			 */
			$suffix       = apply_filters( 'woocommerce_composited_product_price_suffix', $pct_off, $component_id, $product_id, $price, $ref_price, $is_nyp, $is_range, $this ) ;
			$show_free    = $price == 0 && ! $is_range;
			$price_string = $show_free ? __( 'Free!', 'woocommerce' ) : WC_CP()->api->get_composited_item_price_string_price( $price );
			$qty_suffix   = $has_multiple && ! $show_free ? __( '/ pc.', 'woocommerce-composite-products' ) : '';

			/**
			 * Filter the composited product price string (before applying prefix).
			 *
			 * @param  string         $price_string
			 * @param  string         $formatted_price
			 * @param  string         $formatted_qty
			 * @param  string         $percent_off_suffix
			 * @param  string         $price
			 * @param  boolean        $is_range
			 * @param  boolean        $has_multiple
			 * @param  string         $product_id
			 * @param  string         $component_id
			 * @param  WC_CP_Product  $composited_product
			 */
			$price_string = apply_filters( 'woocommerce_composited_product_price_string_inner', sprintf( _x( '%1$s %2$s %3$s', 'dropdown price followed by per unit suffix and discount suffix', 'woocommerce-composite-products' ), $price_string, $qty_suffix, $suffix ), $price_string, $qty_suffix, $suffix, $price, $is_range, $has_multiple, $product_id, $component_id, $this );

			$price_string = $is_range || $is_nyp ? sprintf( _x( '%1$s%2$s', 'Price range: from', 'woocommerce-composite-products' ), $this->get_product()->get_price_html_from_text(), $price_string ) : $price_string;
		}

		/**
		 * Last chance to filter the entire price string.
		 *
		 * @param  string         $price_string
		 * @param  string         $product_id
		 * @param  string         $component_id
		 * @param  WC_CP_Product  $composited_product
		 */
		return apply_filters( 'woocommerce_composited_product_price_string', $price_string, $product_id, $component_id, $this );
	}

	/**
	 * Generated title string for composited products.
	 *
	 * @param  string $title
	 * @param  string $qty
	 * @param  string $price
	 * @return string
	 */
	public static function get_title_string( $title, $qty = '', $price = '' ) {

		$quantity_string = '';
		$price_string    = '';

		if ( $qty ) {
			$quantity_string = sprintf( _x( ' &times; %s', 'qty string', 'woocommerce-composite-products' ), $qty );
		}

		if ( $price ) {
			$price_string = sprintf( _x( ' &ndash; %s', 'price suffix', 'woocommerce-composite-products' ), $price );
		}

		$title_string = sprintf( _x( '%1$s%2$s%3$s', 'title quantity price', 'woocommerce-composite-products' ), $title, $quantity_string, $price_string );

		return $title_string;
	}

	/**
	 * Adds price filters to account for component discounts.
	 *
	 * @return void
	 */
	public function add_filters() {

		$product = $this->get_product();

		if ( ! $product ) {
			return false;
		}

		WC_CP()->api->apply_composited_product_filters( $product, $this->component_id, $this->composite );
	}

	/**
	 * Removes attached price filters.
	 *
	 * @return void
	 */
	public function remove_filters() {

		WC_CP()->api->remove_composited_product_filters();
	}

	/**
	 * Get composited product.
	 *
	 * @return WC_Product|false
	 */
	public function get_product() {

		if ( ! $this->exists() ) {
			return false;
		}

		return $this->product;
	}

	/**
	 * Get composite product.
	 *
	 * @return WC_Product_Composite|false
	 */
	public function get_composite() {

		if ( empty( $this->composite ) ) {
			return false;
		}

		return $this->composite;
	}

	/**
	 * Get component id.
	 *
	 * @return string|false
	 */
	public function get_component_id() {

		if ( empty( $this->component_id ) ) {
			return false;
		}

		return $this->component_id;
	}

	/**
	 * True if the composited product is marked as individually-sold item.
	 *
	 * @return boolean
	 */
	public function is_sold_individually() {

		$is_sold_individually = false;

		if ( $this->get_product()->is_sold_individually() ) {
			$is_sold_individually = true;
		}

		return $is_sold_individually;
	}

	/**
	 * True if the composited product is a NYP product.
	 *
	 * @return boolean
	 */
	public function is_nyp() {

		return $this->is_nyp;
	}

	/**
	 * True if the composited product is a valid product.
	 *
	 * @return boolean
	 */
	public function exists() {

		$exists = false;

		if ( ! empty( $this->product ) ) {
			$exists = true;
		}

		return $exists;
	}

	/**
	 * Get bundled product price after discount, price filters excluded.
	 *
	 * @return mixed
	 */
	public function get_raw_price( $product = false ) {

		if ( ! $product ) {
			$product = $this->product;
		}

		$price = $product->price;

		if ( $price === '' ) {
			return $price;
		}

		if ( ! $this->per_product_pricing ) {
			return ( double ) 0;
		}

		/** Documented in {@see init} */
		if ( apply_filters( 'woocommerce_composited_product_discount_from_regular', true, $this->component_id, $this->composite->id ) ) {
			$regular_price = $product->regular_price;
		} else {
			$regular_price = $price;
		}

		if ( ! empty( $this->component_data[ 'discount' ] ) ) {
			$discount = $this->component_data[ 'discount' ];
			$price    = empty( $regular_price ) ? $regular_price : round( ( double ) $regular_price * ( 100 - $discount ) / 100, wc_cp_price_num_decimals() );
		}

		return $price;
	}

	/**
	 * Get bundled product regular price before discounts, price filters excluded.
	 *
	 * @return mixed
	 */
	public function get_raw_regular_price( $product = false ) {

		if ( ! $product ) {
			$product = $this->product;
		}

		$regular_price = $product->regular_price;

		if ( ! $this->per_product_pricing ) {
			return ( double ) 0;
		}

		if ( empty( $product->regular_price ) ) {
			$regular_price = $product->price;
		}

		return $regular_price;
	}

	/**
	 * Get bundled item price after discount.
	 *
	 * @param  string  $min_or_max
	 * @param  boolean $display
	 * @return double
	 */
	public function get_price( $min_or_max = 'min', $display = false ) {

		if ( ! $this->exists() ) {
			return false;
		}

		$prop    = $min_or_max . '_price_product';
		$product = ! empty( $this->$prop ) ? $this->$prop : $this->product;

		$this->add_filters();
		$price = $product->get_price();
		$this->remove_filters();

		/**
		 * Last chance to filter the composite product price.
		 *
		 * @param  string         $price
		 * @param  string         $min_or_max
		 * @param  boolean        $display
		 * @param  WC_CP_Product  $composited_product
		 */
		return apply_filters( 'woocommerce_composited_product_get_price', $display ? WC_CP()->api->get_composited_product_price( $product, $price ) : $price, $min_or_max, $display, $this );
	}

	/**
	 * Get bundled item regular price after discount.
	 *
	 * @param  string  $min_or_max
	 * @param  boolean $display
	 * @param  boolean $strict
	 * @return double
	 */
	public function get_regular_price( $min_or_max = 'min', $display = false, $strict = false ) {

		if ( ! $this->exists() ) {
			return false;
		}

		$prop    = $strict ? $min_or_max . '_price_product' : $min_or_max . '_regular_price_product';
		$product = ! empty( $this->$prop ) ? $this->$prop : $this->product;

		$this->add_filters();
		$price = $product->get_regular_price();
		$this->remove_filters();

		/**
		 * Last chance to filter the composite product regular price.
		 *
		 * @param  string         $price
		 * @param  string         $min_or_max
		 * @param  boolean        $display
		 * @param  WC_CP_Product  $composited_product
		 */
		return apply_filters( 'woocommerce_composited_product_get_regular_price', $display ? WC_CP()->api->get_composited_product_price( $product, $price ) : $price, $min_or_max, $display, $this );
	}

	/**
	 * Min bundled item price incl tax.
	 *
	 * @return double
	 */
	public function get_price_including_tax( $min_or_max = 'min', $qty = 1 ) {

		if ( ! $this->exists() ) {
			return false;
		}

		$prop    = $min_or_max . '_price_product';
		$product = ! empty( $this->$prop ) ? $this->$prop : $this->product;

		$this->add_filters();
		$price = $product->get_price();
		$this->remove_filters();

		if ( $price && wc_cp_calc_taxes() === 'yes' && wc_cp_prices_include_tax() !== 'yes' ) {
			$price = $product->get_price_including_tax( $qty, $price );
		} else {
			$price = $price * $qty;
		}

		/**
		 * Last chance to filter the composite product price incl tax.
		 *
		 * @param  string         $price
		 * @param  string         $min_or_max
		 * @param  WC_CP_Product  $composited_product
		 * @param  string         $qty
		 */
		return apply_filters( 'woocommerce_composited_product_get_price_including_tax', $price, $min_or_max, $this, $qty );
	}

	/**
	 * Min bundled item price excl tax.
	 *
	 * @return double
	 */
	public function get_price_excluding_tax( $min_or_max = 'min', $qty = 1 ) {

		if ( ! $this->exists() ) {
			return false;
		}

		$prop    = $min_or_max . '_price_product';
		$product = ! empty( $this->$prop ) ? $this->$prop : $this->product;

		$this->add_filters();
		$price = $product->get_price();
		$this->remove_filters();

		if ( $price && wc_cp_calc_taxes() === 'yes' && wc_cp_prices_include_tax() === 'yes' ) {
			$price = $product->get_price_excluding_tax( $qty, $price );
		} else {
			$price = $price * $qty;
		}

		/**
		 * Last chance to filter the composite product price excl tax.
		 *
		 * @param  string         $price
		 * @param  string         $min_or_max
		 * @param  WC_CP_Product  $composited_product
		 * @param  string         $qty
		 */
		return apply_filters( 'woocommerce_composited_product_get_price_excluding_tax', $price, $min_or_max, $this, $qty );
	}

	/**
	 * Returns false if the product cannot be bought.
	 *
	 * @return bool
	 */
	public function is_purchasable() {

		return $this->is_purchasable;
	}

	/**
	 * Deprecated price methods.
	 *
	 * @deprecated
	 */
	public function get_min_price() {
		_deprecated_function( 'get_min_price()', '3.2.3', 'get_price()' );
		return $this->min_price;
	}

	public function get_min_regular_price() {
		_deprecated_function( 'get_min_regular_price()', '3.2.3', 'get_regular_price()' );
		return $this->min_regular_price;
	}

	public function get_max_price() {
		_deprecated_function( 'get_max_price()', '3.2.3', 'get_price()' );
		return $this->max_price;
	}

	public function get_max_regular_price() {
		_deprecated_function( 'get_max_regular_price()', '3.2.3', 'get_regular_price()' );
		return $this->max_regular_price;
	}

	public function get_min_price_incl_tax() {
		_deprecated_function( 'get_min_price_incl_tax()', '3.2.3', 'get_price_including_tax()' );
		return $this->get_price_including_tax( 'min' );
	}

	public function get_min_price_excl_tax() {
		_deprecated_function( 'get_min_price_excl_tax()', '3.2.3', 'get_price_excluding_tax()' );
		return $this->get_price_excluding_tax( 'min' );
	}
}

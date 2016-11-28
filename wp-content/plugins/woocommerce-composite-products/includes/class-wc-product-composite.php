<?php
/**
 * Composite Product Class.
 *
 * @class   WC_Product_Composite
 * @version 3.6.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Product_Composite extends WC_Product {

	private $composite_data = array();

	public $per_product_pricing;
	public $per_product_shipping;

	private $composite_layout;
	private $base_layout;
	private $base_layout_variation;

	private $sold_individually_context;

	/**
	 * Global Options Style moved at component level - @see WC_Product_Composite::get_component_options_style().
	 *
	 * @deprecated 3.6.0
	 */
	private $selections_style;

	private $composited_products             = array();

	private $component_options               = array();
	private $current_component_options_query = array();

	/**
	 * @deprecated 3.5.0
	 */
	public $min_price;

	public $base_price;
	public $base_regular_price;
	public $base_sale_price;

	private $hide_price_html;
	private $is_editable_in_cart;

	public $min_composite_price;
	public $max_composite_price;
	public $min_composite_regular_price;
	public $max_composite_regular_price;

	/**
	 * Index of composited products with lowest/highest component-level prices.
	 * Used in 'get_composite_price', 'get_composite_regular_price', 'get_composite_price_including_tax' and 'get_composite_price_excluding_tax methods'.
	 *
	 * @var array
	 */
	public $price_index = array(
		'price' => array(
			'min' => array(),
			'max' => array()
		),
		'regular_price' => array(
			'min' => array(),
			'max' => array()
		)
	);

	/**
	 * Array of composite price data for consumption by the front-end script.
	 * @var array
	 */
	private $composite_price_data = array();

	/**
	 * Array of cached composite prices.
	 * @var array
	 */
	private $composite_price_cache;

	private $contains_nyp;
	private $has_discounts;

	private $suppress_range_format = false;

	private $is_synced = false;

	public function __construct( $bundle_id ) {

		$this->product_type = 'composite';

		parent::__construct( $bundle_id );

		$this->composite_data            = get_post_meta( $this->id, '_bto_data', true );
		$this->per_product_pricing       = get_post_meta( $this->id, '_per_product_pricing_bto', true );
		$this->per_product_shipping      = get_post_meta( $this->id, '_per_product_shipping_bto', true );

		$this->composite_layout          = get_post_meta( $this->id, '_bto_style', true );
		$this->selections_style          = get_post_meta( $this->id, '_bto_selection_mode', true );

		$this->contains_nyp              = false;
		$this->has_discounts             = false;

		$this->base_price                = ( $base_price         = get_post_meta( $this->id, '_base_price', true ) ) ? (double) $base_price : 0.0;
		$this->base_regular_price        = ( $base_regular_price = get_post_meta( $this->id, '_base_regular_price', true ) ) ? (double) $base_regular_price : 0.0;
		$this->base_sale_price           = ( $base_sale_price    = get_post_meta( $this->id, '_base_sale_price', true ) ) ? (double) $base_sale_price : '';

		$this->hide_price_html           = get_post_meta( $this->id, '_bto_hide_shop_price', true ) === 'yes' ? true : false;

		$this->sold_individually_context = get_post_meta( $this->id, '_bto_sold_individually', true );
		$this->is_editable_in_cart       = get_post_meta( $this->id, '_bto_edit_in_cart', true );
	}

	/**
	 * True if the composite is in sync with its contents.
	 *
	 * @return boolean
	 */
	public function is_synced() {

		return $this->is_synced;
	}

	/**
	 * Calculates min and max prices based on the composited product data.
	 *
	 * @return void
	 */
	public function sync_composite() {

		if ( $this->is_synced() ) {
			return true;
		}

		if ( empty( $this->composite_data ) ) {
			return false;
		}

		// Initialize min/max price information.
		$this->min_composite_price = $this->max_composite_price = $this->min_composite_regular_price = $this->max_composite_regular_price = '';

		// Initialize component options.
		foreach ( $this->get_composite_data() as $component_id => $component_data ) {

			$this->composited_products[ $component_id ] = array();

			// Do not pass any ordering args to speed up the query - ordering and filtering is done when calling get_current_component_options().
			$this->component_options[ $component_id ]   = WC_CP()->api->get_component_options( $component_data );
		}

		if ( $this->is_priced_per_product() ) {

			// SLOW calculation of cheapest price. Not recommended if your Composite includes more than ~100 products in total.
			if ( ! $this->hide_price_html() ) {

				$has_finite_max_price = true;

				foreach ( $this->get_composite_data() as $component_id => $component_data ) {

					$item_min_price         = '';
					$item_max_price         = '';
					$item_min_regular_price = '';
					$item_max_regular_price = '';

					if ( ! empty( $component_data[ 'discount' ] ) ) {
						$this->has_discounts = true;
					}

					// No options available
					if ( empty( $this->component_options[ $component_id ] ) ) {
						continue;
					}

					foreach ( $this->component_options[ $component_id ] as $id ) {

						$composited_product = $this->get_composited_product( $component_id, $id );

						if ( ! $composited_product || ! $composited_product->is_purchasable() ) {
							continue;
						}

						if ( $composited_product->is_nyp() ) {
							$this->contains_nyp          = true;
							$this->suppress_range_format = true;
							$has_finite_max_price        = false;
						}

						// Update component min/max raw prices and create indexes to min/max products.
						$min_price         = $composited_product->min_price;
						$min_regular_price = $composited_product->min_regular_price;

						if ( $item_min_price === '' || $min_price < $item_min_price ) {
							$item_min_price                                         = $min_price;
							$this->price_index[ 'price' ][ 'min' ][ $component_id ] = $id;
						}

						if ( $item_min_regular_price === '' || $min_regular_price < $item_min_regular_price ) {
							$item_min_regular_price                                         = $min_regular_price;
							$this->price_index[ 'regular_price' ][ 'min' ][ $component_id ] = $id;
						}

						$max_price         = $composited_product->max_price;
						$max_regular_price = $composited_product->max_regular_price;

						if ( $item_max_price === '' || $max_price > $item_max_price ) {
							$item_max_price                                         = $max_price;
							$this->price_index[ 'price' ][ 'max' ][ $component_id ] = $id;
						}

						if ( $item_max_regular_price === '' || $max_regular_price > $item_max_regular_price ) {
							$item_max_regular_price                                         = $max_regular_price;
							$this->price_index[ 'regular_price' ][ 'max' ][ $component_id ] = $id;
						}
					}

					// Sync composite.
					if ( $component_data[ 'optional' ] === 'yes' ) {
						$this->suppress_range_format      = true;
						$component_data[ 'quantity_min' ] = 0;
					}

					if ( $component_data[ 'quantity_min' ] !== $component_data[ 'quantity_max' ] ) {
						$this->suppress_range_format = true;
					}

					$this->min_composite_price         = $this->min_composite_price + $component_data[ 'quantity_min' ] * $item_min_price;
					$this->min_composite_regular_price = $this->min_composite_regular_price + $component_data[ 'quantity_min' ] * $item_min_regular_price;

					if ( $has_finite_max_price && $component_data[ 'quantity_max' ] ) {
						$this->max_composite_price         = $this->max_composite_price + $component_data[ 'quantity_max' ] * $item_max_price;
						$this->max_composite_regular_price = $this->max_composite_regular_price + $component_data[ 'quantity_max' ] * $item_max_regular_price;
					} else {
						$has_finite_max_price = false;
					}
				}

				$composite_base_price              = $this->base_price;
				$composite_base_reg_price          = $this->base_regular_price;

				$this->min_composite_price         = $composite_base_price + $this->min_composite_price;
				$this->min_composite_regular_price = $composite_base_reg_price + $this->min_composite_regular_price;

				if ( $has_finite_max_price ) {
					$this->max_composite_price         = $composite_base_price + $this->max_composite_price;
					$this->max_composite_regular_price = $composite_base_reg_price + $this->max_composite_regular_price;
				} else {
					$this->max_composite_price = $this->max_composite_regular_price = '';
				}

			// FAST calculation. When the "Hide Price" option is not checked, the expensive loop does not run and all required variables are set via filters. Recommended if your Composite includes more than ~100 products in total.
			} else {

				// Use these filters if you want to prevent price meta calculations but still show a html price string and return price filter widget min/max results.
				$this->min_composite_price                     = apply_filters( 'woocommerce_min_composite_price', $this->min_composite_price, $this );
				$this->max_composite_price                     = apply_filters( 'woocommerce_max_composite_price', $this->max_composite_price, $this );
				$this->min_composite_regular_price             = apply_filters( 'woocommerce_min_composite_regular_price', $this->min_composite_regular_price, $this );
				$this->max_composite_regular_price             = apply_filters( 'woocommerce_max_composite_regular_price', $this->max_composite_regular_price, $this );

				// Use these filters to control which configurations correspond to the above composite min/max prices. Used in {@see get_composite_price} and {@see get_composite_regular_price}.
				$this->price_index[ 'regular_price' ][ 'min' ] = apply_filters( 'woocommerce_min_composite_regular_price_index', $this->price_index[ 'regular_price' ][ 'min' ], $this );
				$this->price_index[ 'regular_price' ][ 'max' ] = apply_filters( 'woocommerce_max_composite_regular_price_index', $this->price_index[ 'regular_price' ][ 'max' ], $this );
				$this->price_index[ 'price' ][ 'min' ]         = apply_filters( 'woocommerce_min_composite_price_index', $this->price_index[ 'price' ][ 'min' ], $this );
				$this->price_index[ 'price' ][ 'max' ]         = apply_filters( 'woocommerce_max_composite_price_index', $this->price_index[ 'price' ][ 'max' ], $this );
			}

		} else {

			if ( WC_CP()->compatibility->is_nyp( $this ) ) {
				$this->min_composite_price = $this->min_composite_regular_price = get_post_meta( $this->id, '_min_price', true );
				$this->max_composite_price = $this->max_composite_regular_price = '';
			} else {
				$this->min_composite_price         = $this->max_composite_price         = $this->price;
				$this->min_composite_regular_price = $this->max_composite_regular_price = $this->regular_price;
			}
		}

		do_action( 'woocommerce_composite_synced', $this );

		$this->is_synced = true;

		$this->update_price_meta();

		return true;
	}

	/**
	 * Update price meta for access in queries.
	 *
	 * @return void
	 */
	private function update_price_meta() {

		if ( apply_filters( 'woocommerce_composite_update_price_meta', true, $this ) ) {

			if ( ! is_admin() && $this->is_priced_per_product() ) {

				if ( $this->price != $this->min_composite_price ) {
					update_post_meta( $this->id, '_price', $this->min_composite_price );
				}
				if ( $this->min_composite_price < $this->min_composite_regular_price ) {
					if ( $this->sale_price != $this->min_composite_price ) {
						update_post_meta( $this->id, '_sale_price', $this->min_composite_price );
						delete_transient( 'wc_products_onsale' );
					}
				} else {
					if ( $this->sale_price !== '' ) {
						update_post_meta( $this->id, '_sale_price', '' );
						delete_transient( 'wc_products_onsale' );
					}
				}
				if ( $this->regular_price != $this->min_composite_regular_price ) {
					update_post_meta( $this->id, '_regular_price', $this->min_composite_regular_price );
				}
			}
		}
	}

	/**
	 * Stores bundle pricing strategy data that is passed to JS.
	 *
	 * @return void
	 */
	public function load_price_data() {

		$this->composite_price_data[ 'is_purchasable' ]      = $this->is_purchasable() ? 'yes' : 'no';
		$this->composite_price_data[ 'per_product_pricing' ] = $this->is_priced_per_product() ? 'yes' : 'no';
		$this->composite_price_data[ 'show_free_string' ]    = ( $this->is_priced_per_product() ? apply_filters( 'woocommerce_composite_show_free_string', false, $this ) : true ) ? 'yes' : 'no';

		$this->composite_price_data[ 'prices' ]              = new stdClass;
		$this->composite_price_data[ 'regular_prices' ]      = new stdClass;

		$this->composite_price_data[ 'prices_tax' ]          = new stdClass;

		$this->composite_price_data[ 'addons_prices' ]       = new stdClass;

		$this->composite_price_data[ 'quantities' ]          = new stdClass;

		if ( $this->is_priced_per_product() ) {
			$base_price          = $this->get_base_price();
			$base_regular_price  = $this->get_base_regular_price();
		} else {
			$base_price         = $this->get_price();
			$base_regular_price = $this->get_regular_price();
		}

		WC_CP_Helpers::extend_price_display_precision();

		$base_price_incl_tax = $this->get_price_including_tax( 1, 1000 );
		$base_price_excl_tax = $this->get_price_excluding_tax( 1, 1000 );

		WC_CP_Helpers::reset_price_display_precision();

		$this->composite_price_data[ 'base_price' ]         = $base_price;
		$this->composite_price_data[ 'base_regular_price' ] = $base_regular_price;
		$this->composite_price_data[ 'base_price_tax' ]     = $base_price_incl_tax / $base_price_excl_tax;

		$this->composite_price_data[ 'total' ]              = 0.0;
		$this->composite_price_data[ 'regular_total' ]      = 0.0;
		$this->composite_price_data[ 'total_incl_tax' ]     = 0.0;
		$this->composite_price_data[ 'total_excl_tax' ]     = 0.0;
	}

	/**
	 * In per-product pricing mode, get_price() returns the minimum price.
	 *
	 * @return string
	 */
	public function get_price() {
		/**
		 * Filter composite product price.
		 *
		 * @param  string                $price
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_composite_get_price', parent::get_price(), $this );
	}

	/**
	 * In per-product pricing mode, get_regular_price() returns the minimum regular price.
	 *
	 * @return string
	 */
	public function get_regular_price() {
		/**
		 * Filter composite product regular price.
		 *
		 * @param  string                $price
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_composite_get_regular_price', parent::get_regular_price(), $this );
	}

	/**
	 * In per-product pricing mode, get_sale_price() returns the minimum sale price.
	 *
	 * @return string
	 */
	public function get_sale_price() {
		/**
		 * Filter composite product sale price.
		 *
		 * @param  string                $price
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_composite_get_sale_price', parent::get_sale_price(), $this );
	}

	/**
	 * Get composite base price.
	 *
	 * @return string
	 */
	public function get_base_price() {

		if ( $this->is_priced_per_product() ) {
			/**
			 * Filter composite product base price.
			 *
			 * @param  string                $price
			 * @param  WC_Product_Composite  $product
			 */
			return apply_filters( 'woocommerce_composite_get_base_price', $this->base_price, $this );
		} else {
			return false;
		}
	}

	/**
	 * Get composite base regular price.
	 *
	 * @return string
	 */
	public function get_base_regular_price() {

		if ( $this->is_priced_per_product() ) {
			/**
			 * Filter composite product base regular price.
			 *
			 * @param  string                $price
			 * @param  WC_Product_Composite  $product
			 */
			return apply_filters( 'woocommerce_composite_get_base_regular_price', $this->base_regular_price, $this );
		} else {
			return false;
		}
	}

	/**
	 * Get composite base sale price.
	 *
	 * @return string
	 */
	public function get_base_sale_price() {

		if ( $this->is_priced_per_product() ) {
			/**
			 * Filter composite product base sale price.
			 *
			 * @param  string                $price
			 * @param  WC_Product_Composite  $product
			 */
			return apply_filters( 'woocommerce_composite_get_base_sale_price', $this->base_sale_price, $this );
		} else {
			return false;
		}
	}

	/**
	 * Get min/max composite price.
	 *
	 * @param  string  $min_or_max
	 * @param  boolean $display
	 * @return mixed
	 */
	public function get_composite_price( $min_or_max = 'min', $display = false ) {

		if ( $this->is_priced_per_product() ) {

			if ( ! $this->is_synced() ) {
				$this->sync_composite();
			}

			$cache_key = ( $display ? 'display' : 'raw' ) . '_price_' . $min_or_max;

			if ( isset( $this->composite_price_cache[ $cache_key ] ) ) {
				$price = $this->composite_price_cache[ $cache_key ];
			} else {
				$prop = $min_or_max . '_composite_price';
				if ( $this->$prop === '' ) {
					$price = '';
				} else {
					$price = $display ? WC_CP()->api->get_composited_product_price( $this, $this->get_base_price() ) : $this->get_base_price();
					foreach ( $this->price_index[ 'price' ][ $min_or_max ] as $component_id => $product_id ) {
						$component_data = $this->get_component_data( $component_id );
						$item_qty       = $component_data[ 'optional' ] === 'yes' && $min_or_max === 'min' ? 0 : $component_data[ 'quantity_' . $min_or_max ];
						if ( $item_qty ) {
							$composited_product  = $this->get_composited_product( $component_id, $product_id );
							$price              += $item_qty * $composited_product->get_price( $min_or_max, $display );
						}
					}
				}
			}

		} else {

			$price = parent::get_price();

			if ( $display ) {
				$price = WC_CP_Core_Compatibility::is_wc_version_gte_2_4() ? parent::get_display_price( $price ) : WC_CP()->api->get_composited_product_price( $this, $price );
			}
		}

		return $price;
	}

	/**
	 * Get min/max composite regular price.
	 *
	 * @param  string  $min_or_max
	 * @param  boolean $display
	 * @return mixed
	 */
	public function get_composite_regular_price( $min_or_max = 'min', $display = false ) {

		if ( $this->is_priced_per_product() ) {

			if ( ! $this->is_synced() ) {
				$this->sync_composite();
			}

			$cache_key = ( $display ? 'display' : 'raw' ) . '_regular_price_' . $min_or_max;

			if ( isset( $this->composite_price_cache[ $cache_key ] ) ) {
				$price = $this->composite_price_cache[ $cache_key ];
			} else {
				$prop = $min_or_max . '_composite_regular_price';
				if ( $this->$prop === '' ) {
					$price = '';
				} else {
					$price = $display ? WC_CP()->api->get_composited_product_price( $this, $this->get_base_regular_price() ) : $this->get_base_regular_price();
					foreach ( $this->price_index[ 'regular_price' ][ $min_or_max ] as $component_id => $product_id ) {
						$component_data = $this->get_component_data( $component_id );
						$item_qty       = $component_data[ 'optional' ] === 'yes' && $min_or_max === 'min' ? 0 : $component_data[ 'quantity_' . $min_or_max ];
						if ( $item_qty ) {
							$composited_product  = $this->get_composited_product( $component_id, $product_id );
							$price              += $item_qty * $composited_product->get_regular_price( $min_or_max, $display, true );
						}
					}
				}
			}

		} else {

			$price = parent::get_regular_price();

			if ( $display ) {
				$price = WC_CP_Core_Compatibility::is_wc_version_gte_2_4() ? parent::get_display_price( $price ) : WC_CP()->api->get_composited_product_price( $this, $price );
			}
		}

		return $price;
	}

	/**
	 * Get min/max composite price including tax.
	 *
	 * @return mixed
	 */
	public function get_composite_price_including_tax( $min_or_max = 'min', $qty = 1 ) {

		if ( $this->is_priced_per_product() ) {

			if ( ! $this->is_synced() ) {
				$this->sync_composite();
			}

			$cache_key = 'price_incl_tax_' . $min_or_max . '_' . $qty;

			if ( isset( $this->composite_price_cache[ $cache_key ] ) ) {
				$price = $this->composite_price_cache[ $cache_key ];
			} else {
				$price = $this->get_price_including_tax( $qty, $this->get_base_price() );
				foreach ( $this->price_index[ 'price' ][ $min_or_max ] as $component_id => $product_id ) {
					$component_data = $this->get_component_data( $component_id );
					$item_qty       = $component_data[ 'optional' ] === 'yes' && $min_or_max === 'min' ? 0 : $component_data[ 'quantity_' . $min_or_max ];
					if ( $item_qty ) {
						$composited_product  = $this->get_composited_product( $component_id, $product_id );
						$price              += $composited_product->get_price_including_tax( $min_or_max, $item_qty * $qty );
					}
				}
			}

		} else {

			$price = parent::get_price_including_tax( $qty, parent::get_price() );
		}

		return $price;
	}

	/**
	 * Get min/max composite price excluding tax.
	 *
	 * @return double
	 */
	public function get_composite_price_excluding_tax( $min_or_max = 'min', $qty = 1 ) {

		if ( $this->is_priced_per_product() ) {

			if ( ! $this->is_synced() ) {
				$this->sync_composite();
			}

			$cache_key = 'price_excl_tax_' . $min_or_max . '_' . $qty;

			if ( isset( $this->composite_price_cache[ $cache_key ] ) ) {
				$price = $this->composite_price_cache[ $cache_key ];
			} else {
				$price = $this->get_price_excluding_tax( $qty, $this->get_base_price() );
				foreach ( $this->price_index[ 'price' ][ $min_or_max ] as $component_id => $product_id ) {
					$component_data = $this->get_component_data( $component_id );
					$item_qty       = $component_data[ 'optional' ] === 'yes' && $min_or_max === 'min' ? 0 : $component_data[ 'quantity_' . $min_or_max ];
					if ( $item_qty ) {
						$composited_product  = $this->get_composited_product( $component_id, $product_id );
						$price              += $composited_product->get_price_excluding_tax( $min_or_max, $item_qty * $qty );
					}
				}
			}

		} else {

			$price = parent::get_price_excluding_tax( $qty, parent::get_price() );
		}

		return $price;
	}

	/**
	 * Overrides adjust_price to use base price in per-item pricing mode.
	 *
	 * @return double
	 */
	public function adjust_price( $price ) {

		if ( $this->is_priced_per_product() ) {
			$this->price      = $this->price + $price;
			$this->base_price = $this->base_price + $price;
		} else {
			return parent::adjust_price( $price );
		}
	}

	/**
	 * Bypass pricing calculations in per-item pricing mode.
	 *
	 * @return boolean
	 */
	public function hide_price_html() {

		return apply_filters( 'woocommerce_composite_hide_price_html', $this->hide_price_html, $this );
	}

	/**
	 * Returns range style html price string without min and max.
	 *
	 * @param  mixed    $price    default price
	 * @return string             overridden html price string (old style)
	 */
	public function get_price_html( $price = '' ) {

		if ( ! $this->is_synced() ) {
			$this->sync_composite();
		}

		if ( $this->is_priced_per_product() && ! empty( $this->composite_data ) ) {

			// Get the price.
			if ( $this->hide_price_html() || $this->get_composite_price( 'min' ) === '' ) {

				$price = apply_filters( 'woocommerce_composite_empty_price_html', '', $this );

			} else {

				$suppress_range_format = $this->suppress_range_format || apply_filters( 'woocommerce_composite_force_old_style_price_html', false, $this );

				if ( $suppress_range_format ) {

					$price = wc_price( $this->get_composite_price( 'min', true ) );

					if ( $this->get_composite_regular_price( 'min', true ) !== $this->get_composite_price( 'min', true ) ) {

						$regular_price = wc_price( $this->get_composite_regular_price( 'min', true ) );

						if ( $this->get_composite_price( 'min', true ) !== $this->get_composite_price( 'max', true ) ) {
							$price = sprintf( _x( '%1$s%2$s', 'Price range: from', 'woocommerce-composite-products' ), $this->get_price_html_from_text(), $this->get_price_html_from_to( $regular_price, $price ) . $this->get_price_suffix() );
						} else {
							$price = $this->get_price_html_from_to( $regular_price, $price ) . $this->get_price_suffix();
						}

						$price = apply_filters( 'woocommerce_composite_sale_price_html', $price, $this );

					} elseif ( $this->get_composite_price( 'min', true ) === 0 && $this->get_composite_price( 'max', true ) === 0 ) {

						$free_string = apply_filters( 'woocommerce_composite_show_free_string', false, $this ) ? __( 'Free!', 'woocommerce' ) : $price;
						$price       = apply_filters( 'woocommerce_composite_free_price_html', $free_string, $this );

					} else {

						if ( $this->get_composite_price( 'min', true ) !== $this->get_composite_price( 'max', true ) ) {
							$price = sprintf( _x( '%1$s%2$s', 'Price range: from', 'woocommerce-composite-products' ), $this->get_price_html_from_text(), $price . $this->get_price_suffix() );
						} else {
							$price = $price . $this->get_price_suffix();
						}

						$price = apply_filters( 'woocommerce_composite_price_html', $price, $this );
					}

				} else {

					if ( $this->get_composite_price( 'min', true ) !== $this->get_composite_price( 'max', true ) ) {
						$price = sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $this->get_composite_price( 'min', true ) ), wc_price( $this->get_composite_price( 'max', true ) ) );
					} else {
						$price = wc_price( $this->get_composite_price( 'min', true ) );
					}

					if ( $this->get_composite_regular_price( 'min', true ) !== $this->get_composite_price( 'min', true ) || $this->get_composite_regular_price( 'max', true ) > $this->get_composite_price( 'max', true ) ) {

						if ( $this->get_composite_regular_price( 'min', true ) !== $this->get_composite_regular_price( 'max', true ) ) {
							$regular_price = sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $this->get_composite_regular_price( 'min', true ) ), wc_price( $this->get_composite_regular_price( 'max', true ) ) );
						} else {
							$regular_price = wc_price( $this->get_composite_regular_price( 'min', true ) );
						}

						$price = apply_filters( 'woocommerce_composite_sale_price_html', $this->get_price_html_from_to( $regular_price, $price ) . $this->get_price_suffix(), $this );

					} elseif ( $this->get_composite_price( 'min', true ) === 0 && $this->get_composite_price( 'max', true ) === 0 ) {

						$free_string = apply_filters( 'woocommerce_composite_show_free_string', false, $this ) ? __( 'Free!', 'woocommerce' ) : $price;
						$price       = apply_filters( 'woocommerce_composite_free_price_html', $free_string, $this );

					} else {
						$price = apply_filters( 'woocommerce_composite_price_html', $price . $this->get_price_suffix(), $this );
					}
				}
			}

			return apply_filters( 'woocommerce_get_price_html', $price, $this );

		} else {

			return parent::get_price_html();
		}
	}

	/**
	 * Prices incl. or excl. tax are calculated based on the bundled products prices, so get_price_suffix() must be overridden to return the correct field in per-product pricing mode.
	 *
	 * @return 	string    modified price html suffix
	 */
	public function get_price_suffix( $price = '', $qty = 1 ) {

		if ( $this->is_priced_per_product() ) {

			$price_display_suffix  = get_option( 'woocommerce_price_display_suffix' );

			if ( $price_display_suffix ) {
				$price_display_suffix = ' <small class="woocommerce-price-suffix">' . $price_display_suffix . '</small>';

				if ( false !== strpos( $price_display_suffix, '{price_including_tax}' ) ) {
					$price_display_suffix = str_replace( '{price_including_tax}', wc_price( $this->get_composite_price_including_tax() * $qty ), $price_display_suffix );
				}

				if ( false !== strpos( $price_display_suffix, '{price_excluding_tax}' ) ) {
					$price_display_suffix = str_replace( '{price_excluding_tax}', wc_price( $this->get_composite_price_excluding_tax() * $qty ), $price_display_suffix );
				}
			}

			return apply_filters( 'woocommerce_get_price_suffix', $price_display_suffix, $this );

		} else {

			return parent::get_price_suffix();
		}
	}

	/**
	 * Component configuration array passed through 'woocommerce_composite_component_data' filter.
	 *
	 * @return array
	 */
	public function get_composite_data() {

		if ( empty( $this->composite_data ) ) {
			return false;
		}

		$composite_data = array();

		foreach ( $this->composite_data as $component_id => $component_data ) {
			/**
			 * Filter component data.
			 *
			 * @param  array                 $component_data
			 * @param  string                $component_id
			 * @param  WC_Product_Composite  $product
			 */
			$composite_data[ $component_id ] = apply_filters( 'woocommerce_composite_component_data', $component_data, $component_id, $this );
		}

		return $composite_data;
	}

	/**
	 * Composite base layout.
	 *
	 * @return string
	 */
	public function get_composite_layout_style() {

		if ( ! empty( $this->base_layout ) ) {
			return $this->base_layout;
		}

		$composite_layout = WC_CP()->api->get_selected_layout_option( $this->composite_layout );

		$layout = explode( '-', $composite_layout, 2 );

		$this->base_layout = $layout[0];

		return $this->base_layout;
	}

	/**
	 * Composite base layout variation.
	 *
	 * @return string
	 */
	public function get_composite_layout_style_variation() {

		if ( ! empty( $this->base_layout_variation ) ) {
			return $this->base_layout_variation;
		}

		$composite_layout = WC_CP()->api->get_selected_layout_option( $this->composite_layout );

		$layout = explode( '-', $composite_layout, 2 );

		if ( ! empty( $layout[1] ) ) {
			$this->base_layout_variation = $layout[1];
		} else {
			$this->base_layout_variation = 'standard';
		}

		return $this->base_layout_variation;
	}

	/**
	 * Build scenario data arrays for specific components, adapted to the data present in the current component options queries.
	 * Make sure this is always called after component options queries have run, otherwise component options queries will be populated with results for the initial composite state.
	 *
	 * @param  array    $component_ids
	 * @param  boolean  $use_current_query
	 * @return array
	 */
	public function get_current_scenario_data( $component_ids = array() ) {

		$composite_scenario_meta = get_post_meta( $this->id, '_bto_scenario_data', true );

		/**
		 * Filter raw scenario metadata.
		 *
		 * @param  array                $composite_scenario_meta
		 * @param  WC_Product_Composite $product
		 */
		$composite_scenario_meta = apply_filters( 'woocommerce_composite_scenario_meta', $composite_scenario_meta, $this );

		$composite_data               = $this->get_composite_data();
		$composite_data_for_scenarios = array();

		foreach ( $composite_data as $component_id => $component_data ) {

			if ( empty( $component_ids ) || in_array( $component_id, $component_ids ) ) {

				$composite_data_for_scenarios[ $component_id ] = $component_data;

				$current_component_options = $this->get_current_component_options( $component_id );
				$default_option            = $this->get_current_component_selection( $component_id );

				if ( $default_option && ! in_array( $default_option, $current_component_options ) ) {
					$current_component_options[] = $default_option;
				}

				$composite_data_for_scenarios[ $component_id ][ 'current_component_options' ] = $current_component_options;
			}
		}

		$composite_scenario_data = WC_CP_Scenarios::build_scenarios( $composite_scenario_meta, $composite_data_for_scenarios );

		/**
		 * Filter generated scenario data.
		 *
		 * @param  array                $current_component_scenario_data
		 * @param  array                $composite_scenario_meta
		 * @param  array                $composite_data_for_scenarios
		 * @param  WC_Product_Composite $product
		 */
		$this->composite_scenario_data = apply_filters( 'woocommerce_composite_scenario_data', $composite_scenario_data, $composite_scenario_meta, $composite_data_for_scenarios, $this );

		return $this->composite_scenario_data;
	}

	/**
	 * Gets price data array. Contains localized strings and price data passed to JS.
	 *
	 * @return array
	 */
	public function get_composite_price_data() {

		if ( ! $this->is_synced() ) {
			$this->sync_composite();
		}

		$this->load_price_data();

		return $this->composite_price_data;
	}

	/**
	 * True if a component contains a NYP item.
	 *
	 * @return boolean
	 */
	public function contains_nyp() {

		if ( ! $this->is_synced() ) {
			$this->sync_composite();
		}

		if ( $this->contains_nyp ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * True if a one of the composited products has a component discount, or if there is a base sale price defined.
	 *
	 * @return boolean
	 */
	public function is_on_sale() {

		if ( $this->is_priced_per_product() ) {
			if ( ! $this->is_synced() ) {
				$this->sync_composite();
			}
			$composite_on_sale = ( ( $this->get_base_sale_price() != $this->get_base_regular_price() && $this->get_base_sale_price() == $this->get_base_price() ) || ( $this->has_discounts && $this->get_composite_regular_price( 'min' ) > 0 ) );
		} else {
			$composite_on_sale = parent::is_on_sale();
		}

		/**
		 * Filter composite on sale status.
		 *
		 * @param   boolean               $composite_on_sale
		 * @param   WC_Product_Composite  $this
		 */
		return apply_filters( 'woocommerce_product_is_on_sale', $composite_on_sale, $this );
	}

	/**
	 * Override purchasable method to account for empty price meta being allowed when the "Per-Item Pricing" option is checked.
	 *
	 * @return boolean
	 */
	public function is_purchasable() {

		$purchasable = true;

		// Products must exist of course.
		if ( ! $this->exists() ) {
			$purchasable = false;

		// When priced statically a price needs to be set.
		} elseif ( ! $this->is_priced_per_product() && $this->get_price() === '' ) {
			$purchasable = false;

		// Check the product is published.
		} elseif ( $this->post->post_status !== 'publish' && ! current_user_can( 'edit_post', $this->id ) ) {
			$purchasable = false;
		}

		/**
		 * Filter composite purchasable status.
		 *
		 * @param   boolean               $is_purchasable
		 * @param   WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_is_purchasable', $purchasable, $this );
	}

	/**
	 * Get composited product.
	 *
	 * @param  string            $component_id
	 * @param  int               $product_id
	 * @return WC_Product|false
	 */
	public function get_composited_product( $component_id, $product_id ) {

		if ( isset( $this->composited_products[ $component_id ][ $product_id ] ) ) {

			$composited_product = $this->composited_products[ $component_id ][ $product_id ];

		} else {

			$composited_product = new WC_CP_Product( $product_id, $component_id, $this );

			if ( ! $composited_product->exists() ) {
				return false;
			}

			$this->composited_products[ $component_id ][ $product_id ] = $composited_product;
		}

		return $composited_product;
	}

	/**
	 * Get component options to display. Fetched using a WP Query wrapper to allow advanced component options filtering / ordering / pagination.
	 *
	 * @param  string $component_id
	 * @param  array  $args
	 * @return array
	 */
	public function get_current_component_options( $component_id, $args = array() ) {

		$current_options = array();

		if ( isset( $this->current_component_options_query[ $component_id ] ) ) {

			$current_options = $this->current_component_options_query[ $component_id ]->get_component_options();

		} else {

			if ( ! $this->is_synced() ) {
				$this->sync_composite();
			}

			$options_style = $this->get_component_options_style( $component_id );

			// Only do paged component options when supported.
			if ( false === WC_CP()->api->options_style_supports( $options_style, 'pagination' ) ) {
				$per_page = false;
			} else {
				$per_page = $this->get_component_results_per_page( $component_id );
			}

			$defaults = array(
				'load_page'       => $this->paginate_component_options( $component_id ) ? 'selected' : 1,
				'per_page'        => $per_page,
				'selected_option' => $this->get_current_component_selection( $component_id ),
				'orderby'         => $this->get_component_default_ordering_option( $component_id ),
				'query_type'      => 'product_ids',
			);

			// Component option ids have already been queried without any pages / filters / sorting when initializing the product in 'sync_composite'.
			// This time, we can speed up our paged / filtered / sorted query by using the stored ids of the first "raw" query.

			$component_data                   = $this->get_component_data( $component_id );
			$component_data[ 'assigned_ids' ] = $this->get_component_options( $component_id );

			/**
			 * Filter args passed to WC_CP_Query.
			 *
			 * @param  array                 $query_args
			 * @param  array                 $passed_args
			 * @param  string                $component_id
			 * @param  WC_Product_Composite  $product
			 */
			$current_args = apply_filters( 'woocommerce_composite_component_options_query_args_current', wp_parse_args( $args, $defaults ), $args, $component_id, $this );

			// Pass through query to apply filters / ordering.
			$query = new WC_CP_Query( $component_data, $current_args );

			$this->current_component_options_query[ $component_id ] = $query;

			$current_options = $query->get_component_options();
		}

		return $current_options;
	}

	/**
	 * Grab component discount by component id.
	 *
	 * @param  string $component_id
	 * @return string
	 */
	public function get_component_discount( $component_id ) {

		if ( ! isset( $this->composite_data[ $component_id ][ 'discount' ] ) ) {
			return false;
		}

		$component_data = $this->get_component_data( $component_id );

		return $component_data[ 'discount' ];
	}

	/**
	 * Thumbnail loop columns count.
	 *
	 * @param  string $component_id
	 * @return int
	 */
	public function get_component_columns( $component_id ) {

		/**
		 * Filter count of thumbnail loop columns.
		 * By default, the component options loop has 1 column less than the main shop loop.
		 *
		 * @param  int                   $columns_count
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_composite_component_loop_columns', max( apply_filters( 'loop_shop_columns', 4 ) - 1, 1 ), $component_id, $this );
	}

	/**
	 * Thumbnail loop results per page.
	 *
	 * @param  string $component_id
	 * @return int
	 */
	public function get_component_results_per_page( $component_id ) {

		$thumbnail_columns = $this->get_component_columns( $component_id );

		/**
		 * Filter count of thumbnails loop items per page.
		 * By default displays 2 rows of options.
		 *
		 * @param  int                   $per_page_count
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_component_options_per_page', $thumbnail_columns * 2, $component_id, $this );
	}

	/**
	 * Get the default method to order the options of a component.
	 *
	 * @param  int    $component_id
	 * @return string
	 */
	public function get_component_default_ordering_option( $component_id ) {

		/**
		 * Filter the default order-by method.
		 *
		 * @param  string                $order_by_id
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 */
		$default_orderby = apply_filters( 'woocommerce_composite_component_default_orderby', 'default', $component_id, $this );

		return $default_orderby;
	}

	/**
	 * Get component sorting options, if enabled.
	 *
	 * @param  int    $component_id
	 * @return array
	 */
	public function get_component_ordering_options( $component_id ) {

		$component_data = $this->get_component_data( $component_id );

		if ( isset( $component_data[ 'show_orderby' ] ) && $component_data[ 'show_orderby' ] == 'yes' ) {

			$default_orderby      = $this->get_component_default_ordering_option( $component_id );
			$show_default_orderby = 'default' === $default_orderby;

			/**
			 * Filter the available sorting drowdown options.
			 *
			 * @param  array                 $order_by_data
			 * @param  string                $component_id
			 * @param  WC_Product_Composite  $product
			 */
			$component_orderby_options = apply_filters( 'woocommerce_composite_component_orderby', array(
				'default'    => __( 'Default sorting', 'woocommerce' ),
				'popularity' => __( 'Sort by popularity', 'woocommerce' ),
				'rating'     => __( 'Sort by average rating', 'woocommerce' ),
				'date'       => __( 'Sort by newness', 'woocommerce' ),
				'price'      => __( 'Sort by price: low to high', 'woocommerce' ),
				'price-desc' => __( 'Sort by price: high to low', 'woocommerce' )
			), $component_id, $this );

			if ( ! $show_default_orderby ) {
				unset( $component_orderby_options[ 'default' ] );
			}

			if ( get_option( 'woocommerce_enable_review_rating' ) === 'no' ) {
				unset( $component_orderby_options[ 'rating' ] );
			}

			if ( ! $this->is_priced_per_product() ) {
				unset( $component_orderby_options[ 'price' ] );
				unset( $component_orderby_options[ 'price-desc' ] );
			}

			return $component_orderby_options;
		}

		return false;
	}

	/**
	 * Get component filtering options, if enabled.
	 *
	 * @param  int    $component_id
	 * @return array
	 */
	public function get_component_filtering_options( $component_id ) {

		global $wc_product_attributes;

		$component_data = $this->get_component_data( $component_id );

		if ( isset( $component_data[ 'show_filters' ] ) && $component_data[ 'show_filters' ] == 'yes' ) {

			$active_filters = array();

			if ( ! empty( $component_data[ 'attribute_filters' ] ) ) {

				foreach ( $wc_product_attributes as $attribute_taxonomy_name => $attribute_data ) {

					if ( in_array( $attribute_data->attribute_id, $component_data[ 'attribute_filters' ] ) && taxonomy_exists( $attribute_taxonomy_name ) ) {

						$orderby = $attribute_data->attribute_orderby;

						switch ( $orderby ) {
							case 'name' :
								$args = array( 'orderby' => 'name', 'hide_empty' => false, 'menu_order' => false );
							break;
							case 'id' :
								$args = array( 'orderby' => 'id', 'order' => 'ASC', 'menu_order' => false, 'hide_empty' => false );
							break;
							case 'menu_order' :
								$args = array( 'menu_order' => 'ASC', 'hide_empty' => false );
							break;
						}

						$taxonomy_terms = get_terms( $attribute_taxonomy_name, $args );

						if ( $taxonomy_terms ) {

							switch ( $orderby ) {
								case 'name_num' :
									usort( $taxonomy_terms, '_wc_get_product_terms_name_num_usort_callback' );
								break;
								case 'parent' :
									usort( $taxonomy_terms, '_wc_get_product_terms_parent_usort_callback' );
								break;
							}

							// Add to array
							$filter_options = array();

							foreach ( $taxonomy_terms as $term ) {
								$filter_options[ $term->term_id ] = $term->name;
							}

							// Default filter format
							$filter_data = array(
								'filter_type'    => 'attribute_filter',
								'filter_id'      => $attribute_taxonomy_name,
								'filter_name'    => $attribute_data->attribute_label,
								'filter_options' => $filter_options,
							);

							$active_filters[] = $filter_data;
						}
					}
				}
			}

			/**
			 * Filter the active filters data.
			 *
			 * @param  array                 $active_filters
			 * @param  string                $component_id
			 * @param  WC_Product_Composite  $product
			 */
			$component_filtering_options = apply_filters( 'woocommerce_composite_component_filters', $active_filters, $component_id, $this );

			if ( ! empty( $component_filtering_options ) ) {

				return $component_filtering_options;
			}
		}

		return false;
	}

	/**
	 * Get the query object used to retrieve the component options of a component.
	 * Should be called after {@see get_current_component_options} has been used to retrieve / sort / filter a set of component options.
	 *
	 * @param  int          $component_id
	 * @return WC_CP_Query
	 */
	public function get_current_component_options_query( $component_id ) {

		if ( ! isset( $this->current_component_options_query[ $component_id ] ) ) {
			return false;
		}

		return $this->current_component_options_query[ $component_id ];
	}

	/**
	 * Get all component options (product ids) available in a component.
	 *
	 * @param  string $component_id
	 * @return array
	 */
	public function get_component_options( $component_id ) {

		if ( ! $this->is_synced() ) {
			$this->sync_composite();
		}

		return $this->component_options[ $component_id ];
	}

	/**
	 * Component options selection style.
	 *
	 * @param  string  $component_id
	 * @return string
	 */
	public function get_component_options_style( $component_id ) {

		$component_data = $this->get_component_data( $component_id );

		if ( isset( $component_data[ 'selection_mode' ] ) ) {
			$options_style = $component_data[ 'selection_mode' ];
		} elseif ( ! empty( $this->selections_style ) ) {
			$options_style = $this->selections_style;
		} else {
			$options_style = 'dropdowns';
		}

		if ( false === WC_CP()->api->get_options_style( $options_style ) ) {
			$options_style = 'dropdowns';
		}

		return $options_style;
	}

	/**
	 * True if a component has only one option and is not optional.
	 *
	 * @param  string  $component_id
	 * @return boolean
	 */
	public function is_component_static( $component_id ) {

		$component_data = $this->get_component_data( $component_id );

		$is_optional = $component_data[ 'optional' ] === 'yes';
		$is_static   = count( $this->get_component_options( $component_id ) ) == 1 && ! $is_optional;

		return $is_static;
	}

	/**
	 * True if a component is optional.
	 *
	 * @param  string  $component_id
	 * @return boolean
	 */
	public function is_component_optional( $component_id ) {

		$component_data = $this->get_component_data( $component_id );

		$is_optional = $component_data[ 'optional' ] === 'yes';

		return $is_optional;
	}

	/**
	 * Controls whether component options loaded via ajax will be appended or paginated.
	 * When incompatible component options are set to be hidden, pagination cannot be used since results are filtered via js on the client side.
	 *
	 * @param  string  $component_id
	 * @return boolean
	 */
	public function paginate_component_options( $component_id ) {

		$options_style = $this->get_component_options_style( $component_id );

		if ( WC_CP()->api->options_style_supports( $options_style, 'pagination' ) ) {
			// Pagination cannot be enabled when hiding disabled component options (work is done on the client side).
			if ( $this->hide_disabled_component_options( $component_id ) ) {
				$paginate = false;
			} else {
				/**
				 * Last chance to disable pagination and show a "Load More" button.
				 *
				 * @param  boolean               $paginate
				 * @param  string                $component_id
				 * @param  WC_Product_Composite  $product
				 */
				$paginate = apply_filters( 'woocommerce_component_options_paginate_results', true, $component_id, $this );
			}
		} else {
			$paginate = false;
		}

		return $paginate;
	}

	/**
	 * Controls whether disabled component options will be hidden instead of greyed-out.
	 *
	 * @param  string  $component_id
	 * @return boolean
	 */
	public function hide_disabled_component_options( $component_id ) {

		/**
		 * Filter to decide whether incompatible component options will be hidden.
		 *
		 * @param  boolean               $paginate
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_component_options_hide_incompatible', false, $component_id, $this );
	}

	/**
	 * Get the currently selected option (product id) for a component.
	 *
	 * @since  3.6.0
	 * @param  string $component_id
	 * @return int
	 */
	public function get_current_component_selection( $component_id ) {

		$component_data = $this->get_component_data( $component_id );

		if ( ! $component_data ) {
			return '';
		}

		$selected_value    = false;
		$current_query     = $this->get_current_component_options_query( $component_id );

		if ( false !== $current_query ) {
			$current_query_args = $current_query->get_query_args();
			if ( ! empty( $current_query_args ) ) {
				$selected_value = $current_query_args[ 'selected_option' ];
			}
		}

		if ( false === $selected_value ) {

			$component_options = $this->get_component_options( $component_id );

			if ( $component_data[ 'optional' ] !== 'yes' && count( $component_options ) === 1 ) {
				$selected_value = $component_options[0];
			} elseif ( isset( $_REQUEST[ 'wccp_component_selection' ][ $component_id ] ) ) {
				$selected_value = $_REQUEST[ 'wccp_component_selection' ][ $component_id ];
			} else {
				$selected_value = isset( $component_data[ 'default_id' ] ) && in_array( $component_data[ 'default_id' ], $this->get_component_options( $component_id ) ) ? $component_data[ 'default_id' ] : '';
			}

			/**
			 * Filter the default selection.
			 *
			 * @param  string                $selected_product_id
			 * @param  string                $component_id
			 * @param  WC_Product_Composite  $product
			 */
			$selected_value = apply_filters( 'woocommerce_composite_component_default_option', $selected_value, $component_id, $this );
		}

		return $selected_value;
	}

	/**
	 * Get component data array by component id.
	 *
	 * @param  string $component_id
	 * @return array
	 */
	public function get_component_data( $component_id ) {

		if ( ! isset( $this->composite_data[ $component_id ] ) ) {
			return false;
		}

		/**
		 * Filter the metadata of a single component.
		 *
		 * @param  array                 $component_data
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_composite_component_data', $this->composite_data[ $component_id ], $component_id, $this );
	}

	/**
	 * Get component thumbnail.
	 *
	 * @param  string $component_id
	 * @return array
	 */
	public function get_component_image( $component_id ) {

		$component_data = $this->get_component_data( $component_id );

		if ( ! $component_data ) {
			return '';
		}

		$image_src = '';

		if ( ! empty( $component_data[ 'thumbnail_id' ] ) ) {

			$image_src_data = wp_get_attachment_image_src( $component_data[ 'thumbnail_id' ], apply_filters( 'woocommerce_composite_component_image_size', 'shop_catalog' )  );
			$image_src      = $image_src_data ? current( $image_src_data ) : false;
		}

		if ( ! $image_src ) {
			$image_src = WC_CP()->plugin_url() . '/assets/images/placeholder.png';
		}

		$image = sprintf( '<img class="summary_element_content" alt="%s" src="%s"/>', __( 'Component image', 'woocommerce-composite-products' ), $image_src );


		/**
		 * Filter the component image.
		 *
		 * Add class="norefresh" to prevent summary image updates and keep the original image static.
		 * Return '' to hide all images from the summary section.
		 *
		 * @param  string                $image_src
		 * @param  string                $image
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_composite_component_image', $image, $image_src, $component_id, $this );
	}

	/**
	 * Create an array of classes to use in the component layout templates.
	 *
	 * @param  string $component_id
	 * @return array
	 */
	public function get_component_classes( $component_id ) {

		$classes    = array();
		$layout     = $this->get_composite_layout_style();
		$components = $this->get_composite_data();
		$style      = $this->get_component_options_style( $component_id );

		/**
		 * Filter component "toggle box" view, by default enabled when using the "Progressive" layout.
		 *
		 * @param  boolean               $is_toggled
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 */
		$toggled    = $layout === 'paged' ? false : apply_filters( 'woocommerce_composite_component_toggled', $layout === 'progressive' ? true : false, $component_id, $this );

		$classes[]  = 'component';
		$classes[]  = $layout;
		$classes[]  = 'options-style-' . $style;

		if ( WC_CP()->api->options_style_supports( $style, 'pagination' ) ) {
			if ( $this->paginate_component_options( $component_id ) ) {
				$classes[] = 'paginate-results';
			} else {
				$classes[] = 'append-results';
			}
		}

		if ( $this->hide_disabled_component_options( $component_id ) ) {
			$classes[] = 'hide-incompatible-products';
			$classes[] = 'hide-incompatible-variations';
		}

		if ( $layout === 'paged' ) {
			$classes[] = 'multistep';
		} elseif ( $layout === 'progressive' ) {

			$classes[] = 'multistep';
			$classes[] = 'progressive';
			$classes[] = 'autoscrolled';

			/*
			 * To leave open in blocked state, for instance when displaying options as thumbnails, use:
			 *
			 * if ( $toggled && $style === 'thumbnails' ) {
			 *     $classes[] = 'block-open';
			 * }
			 */
		}

		if ( $toggled ) {
			$classes[] = 'toggled';
		}

		if ( array_search( $component_id, array_keys( $components ) ) === 0 ) {
			$classes[] = 'active';
			$classes[] = 'first';

			if ( $toggled ) {
				$classes[] = 'open';
			}
		} else {

			if ( $layout === 'progressive' ) {
				$classes[] = 'blocked';
			}

			if ( $toggled ) {
				$classes[] = 'closed';
			}
		}

		if ( array_search( $component_id, array_keys( $components ) ) === count( $components ) - 1 ) {
			$classes[] = 'last';
		}

		if ( $this->is_component_static( $component_id ) ) {
			$classes[] = 'static';
		}

		$hide_product_thumbnail = isset( $components[ $component_id ][ 'hide_product_thumbnail' ] ) ? $components[ $component_id ][ 'hide_product_thumbnail' ] : 'no';

		if ( $hide_product_thumbnail === 'yes' ) {
			$classes[] = 'selection_thumbnail_hidden';
		}

		/**
		 * Filter component classes. Used for JS app initialization.
		 *
		 * @param  array                 $classes
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_composite_component_classes', $classes, $component_id, $this );
	}

	/**
	 * True if the composite is priced per product.
	 *
	 * @return boolean
	 */
	public function is_priced_per_product() {

		$is_priced_per_product = $this->per_product_pricing === 'yes' ? true : false;

		return $is_priced_per_product;
	}

	/**
	 * True if the composite is priced per product.
	 *
	 * @return boolean
	 */
	public function is_shipped_per_product() {

		$is_shipped_per_product = $this->per_product_shipping === 'yes' ? true : false;

		return $is_shipped_per_product;
	}

	/**
	 * True if the composite is editable in cart.
	 *
	 * @return boolean
	 */
	public function is_editable_in_cart() {

		return $this->is_editable_in_cart === 'yes';
	}

	/**
	 * Wrapper for get_permalink that adds composite configuration data to the URL.
	 *
	 * @return string
	 */
	public function get_permalink() {

		$permalink             = get_permalink( $this->id );
		$composite_config_data = false;
		$fn_args_count         = func_num_args();

		if ( $fn_args_count === 1 ) {

			$cart_item = func_get_arg( 0 );

			if ( isset( $cart_item[ 'composite_data' ] ) && is_array( $cart_item[ 'composite_data' ] ) ) {

				$composite_config_data = $cart_item[ 'composite_data' ];
				$args                  = array();

				foreach ( $composite_config_data as $component_id => $component_config_data ) {

					if ( isset( $component_config_data[ 'product_id' ] ) ) {
						$args[ 'wccp_component_selection' ][ $component_id ] = $component_config_data[ 'product_id' ];
					}

					if ( isset( $component_config_data[ 'quantity' ] ) ) {
						$args[ 'wccp_component_quantity' ][ $component_id ] = $component_config_data[ 'quantity' ];
					}

					if ( isset( $component_config_data[ 'variation_id' ] ) ) {
						$args[ 'wccp_variation_id' ][ $component_id ] = $component_config_data[ 'variation_id' ];
					}

					if ( isset( $component_config_data[ 'attributes' ] ) && is_array( $component_config_data[ 'attributes' ] ) ) {
						foreach ( $component_config_data[ 'attributes' ] as $tax => $val ) {
							$args[ 'wccp_' . $tax ][ $component_id ] = $val;
						}
					}
				}

				if ( ! empty( $args ) && $this->is_editable_in_cart() ) {

					// Find the cart id we are updating.

					$cart_id = '';

					foreach ( WC()->cart->cart_contents as $item_key => $item_values ) {
						if ( isset( $item_values[ 'composite_children' ] ) && $item_values[ 'composite_data' ] === $cart_item[ 'composite_data' ] ) {
							$cart_id = $item_key;
						}
					}

					if ( $cart_id ) {
						$args[ 'update-composite' ] = $cart_id;
					}
				}

				$args = apply_filters( 'woocommerce_composite_cart_permalink_args', $args, $cart_item, $this );

				if ( ! empty( $args ) ) {
					$permalink = esc_url( add_query_arg( $args, $permalink ) );
				}

			}
		}

		return $permalink;
	}

	/**
	 * Get the add to cart button text.
	 *
	 * @return  string
	 */
	public function add_to_cart_text() {

		$text = $this->is_purchasable() && $this->is_in_stock() ? __( 'Select options', 'woocommerce' ) : __( 'Read More', 'woocommerce' );

		return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
	}

	/**
	 * Get the add to cart button text for the single page.
	 *
	 * @return string
	 */
	public function single_add_to_cart_text() {

		$text = __( 'Add to cart', 'woocommerce' );

		if ( isset( $_GET[ 'update-composite' ] ) ) {
			$updating_cart_key = wc_clean( $_GET[ 'update-composite' ] );

			if ( isset( WC()->cart->cart_contents[ $updating_cart_key ] ) ) {
				$text = __( 'Update Cart', 'woocommerce-composite-products' );
			}
		}

		return apply_filters( 'woocommerce_product_single_add_to_cart_text', $text, $this );
	}

	/**
	 * Get composite-specific add to cart form settings.
	 *
	 * @return  string
	 */
	public function add_to_cart_form_settings() {

		$non_blocking_states = 'no';

		if ( $this->get_composite_layout_style() === 'single' ) {
			$has_masked_components = false;
			$scenario_data         = $this->get_current_scenario_data();

			if ( isset( $scenario_data[ 'scenario_settings' ][ 'masked_components' ] ) ) {
				foreach ( $scenario_data[ 'scenario_settings' ][ 'masked_components' ] as $scenario_id => $masked_components ) {
					if ( ! empty( $masked_components ) ) {
						$has_masked_components = true;
						break;
					}
				}
			}

			$non_blocking_states = $has_masked_components ? 'yes' : 'no';
		}

		$settings = array(
			// Apply a sequential configuration process when using the 'componentized' layout.
			// When set to 'yes', a component can be configured only if all previous components have been configured.
			'sequential_componentized_progress' => apply_filters( 'woocommerce_composite_sequential_comp_progress', 'no', $this ), /* yes | no */
			// Hide or disable the add-to-cart button if the composite has any components pending user input.
			'button_behaviour'                  => apply_filters( 'woocommerce_composite_button_behaviour', 'new', $this ), /* new | old */
			'layout'                            => $this->get_composite_layout_style(),
			'layout_variation'                  => $this->get_composite_layout_style_variation(),
			'update_browser_history'            => $this->get_composite_layout_style() !== 'single' ? 'yes' : 'no',
			'non_blocking_states'               => apply_filters( 'woocommerce_composite_non_blocking_states', $non_blocking_states, $this ),
			'slugs'                             => $this->get_component_slugs(),
		);

		/**
		 * Filter composite-level JS app settings.
		 *
		 * @param  array                 $settings
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_composite_add_to_cart_form_settings', $settings, $this );
	}

	/**
	 * Sold individually extended options for Composite Products, used to add context to the 'sold_individually' option.
	 * Returns 'no' | 'product' | 'configuration', depending on the 'sold_individually' context.
	 *
	 * @return  string
	 */
	public function get_sold_individually_context() {

		return $this->sold_individually_context;
	}

	/**
	 * Generate component slugs based on component titles. Used to generate  routes.
	 *
	 * @return array
	 */
	private function get_component_slugs() {

		$composite_data = $this->get_composite_data();
		$slugs          = array();

		if ( ! empty( $composite_data ) ) {
			foreach ( $composite_data as $component_id => $component_data ) {
				$component_title = apply_filters( 'woocommerce_composite_component_title', $component_data[ 'title' ], $component_id, $this->id );
				$sanitized_title = sanitize_title( $component_title );
				$component_slug  = $sanitized_title;
				$loop            = 0;

				while ( in_array( $component_slug, $slugs ) ) {
					$loop++;
					$component_slug = $sanitized_title . '-' . $loop;
				}

				$slugs[ $component_id ] = $component_slug;
			}

			$review_slug       = $this->get_composite_layout_style_variation() === 'componentized' ? __( 'configuration', 'woocommerce-composite-products' ) : __( 'review', 'woocommerce-composite-products' );
			$slugs[ 'review' ] = sanitize_title( $review_slug );
		}

		return $slugs;
	}

	/*
	 * Deprecated functions.
	 */

	/**
	 * @deprecated
	 */
	public function get_bto_scenario_data() {
		_deprecated_function( 'WC_Product_Composite::get_bto_scenario_data()', '2.5.0', 'WC_Product_Composite::get_composite_scenario_data()' );
		return $this->get_composite_scenario_data();
	}

	/**
	 * @deprecated
	 */
	public function get_bto_data() {
		_deprecated_function( 'WC_Product_Composite::get_bto_data()', '2.5.0', 'WC_Product_Composite::get_composite_data()' );
		return $this->get_composite_data();
	}

	/**
	 * @deprecated
	 */
	public function get_bto_price_data() {
		_deprecated_function( 'WC_Product_Composite::get_bto_price_data()', '2.5.0', 'WC_Product_Composite::get_composite_price_data()' );
		return $this->get_composite_price_data();
	}

	/**
	 * @deprecated 3.6.0
	 */
	public function get_composite_selections_style() {

		_deprecated_function( 'WC_Product_Composite::get_composite_selections_style()', '3.6.0', 'WC_Product_Composite::get_component_options_style()' );

		$selections_style = $this->selections_style;

		if ( empty( $selections_style ) ) {
			$selections_style = 'dropdowns';
		}

		return $selections_style;
	}

	/**
	 * @deprecated 3.6.0
	 */
	public function get_component_default_option( $component_id ) {
		_deprecated_function( 'WC_Product_Composite::get_component_default_option()', '3.6.0', 'WC_Product_Composite::get_current_component_selection()' );
		return $this->get_current_component_selection( $component_id );
	}

	/**
	 * @deprecated 3.6.0
	 */
	public function get_current_component_scenarios( $component_id, $current_component_options ) {
		_deprecated_function( 'WC_Product_Composite::get_current_component_scenarios()', '3.6.0', 'WC_Product_Composite::get_current_scenario_data()' );
		return $this->get_current_scenario_data( array( $component_id ) );
	}

	/**
	 * @deprecated 3.6.0
	 */
	public function get_composite_scenario_data() {
		_deprecated_function( 'WC_Product_Composite::get_composite_scenario_data()', '3.6.0', 'WC_Product_Composite::get_current_scenario_data()' );
		return $this->get_current_scenario_data();
	}
}

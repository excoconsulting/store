<?php
/**
 * Product Bundle Class.
 *
 * @class   WC_Product_Bundle
 * @version 4.14.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Product_Bundle extends WC_Product {

	public $bundle_data;

	public $bundled_items;
	private $is_synced;

	/**
	 * @deprecated 4.13.0
	 * @var double
	 */
	public $min_price;

	public $base_price;
	public $base_regular_price;
	public $base_sale_price;

	/**
	 * Bundle prices calculated from raw price meta.
	 * @var mixed
	 */
	public $min_bundle_price;
	public $max_bundle_price;
	public $min_bundle_regular_price;
	public $max_bundle_regular_price;

	/**
	 * Array of bundle price data for consumption by the front-end script.
	 * @var array
	 */
	private $bundle_price_data;

	/**
	 * Array of cached bundle prices.
	 * @var array
	 */
	private $bundle_price_cache;

	public $per_product_pricing_active;
	public $per_product_shipping_active;

	private $all_items_purchasable;
	private $all_items_sold_individually;
	private $all_items_in_stock;
	private $all_items_visible;
	private $all_items_optional;

	private $has_items_on_backorder;

	/**
	 * Index of bundled item quantities for min/max price calculations.
	 * Used in 'get_bundle_price', 'get_bundle_regular_price', 'get_bundle_price_including_tax' and 'get_bundle_price_excluding_tax' methods.
	 * @var array
	 */
	public $bundled_quantities_index;

	private $contains_nyp;
	private $is_nyp;

	private $has_discounts;

	private $contains_sub;

	private $contains_optional;
	private $suppress_range_format;

	private $items_require_input;

	public function __construct( $bundle ) {

		$this->product_type = 'bundle';

		parent::__construct( $bundle );

		$this->bundle_data = get_post_meta( $this->id, '_bundle_data', true );

		$bundled_item_ids = get_post_meta( $this->id, '_bundled_ids', true );

		// Update from 3.X.
		if ( empty( $this->bundle_data ) && ! empty( $bundled_item_ids ) ) {
			$this->bundle_data = WC_PB_Helpers::serialize_bundle_meta( $this->id );
		}

		$this->contains_nyp                = false;
		$this->is_nyp                      = false;

		$this->contains_sub                = false;

		$this->contains_optional           = false;
		$this->suppress_range_format       = false;

		$this->items_require_input         = false;
		$this->all_items_optional          = true;

		$this->all_items_visible           = true;

		$this->all_items_sold_individually = true;
		$this->all_items_in_stock          = true;
		$this->all_items_purchasable       = true;
		$this->has_items_on_backorder      = false;

		$this->per_product_pricing_active  = ( get_post_meta( $this->id, '_per_product_pricing_active', true ) === 'yes' ) ? true : false;
		$this->per_product_shipping_active = ( get_post_meta( $this->id, '_per_product_shipping_active', true ) === 'yes' ) ? true : false;

		$this->base_price                  = ( $base_price         = get_post_meta( $this->id, '_base_price', true ) ) ? (double) $base_price : 0.0;
		$this->base_regular_price          = ( $base_regular_price = get_post_meta( $this->id, '_base_regular_price', true ) ) ? (double) $base_regular_price : 0.0;
		$this->base_sale_price             = ( $base_sale_price    = get_post_meta( $this->id, '_base_sale_price', true ) ) ? (double) $base_sale_price : '';

		$this->bundled_quantities_index = array(
			'min' => array(),
			'max' => array()
		);

		if ( WC_PB()->compatibility->is_nyp( $this ) ) {
			$this->is_nyp = true;
		}

		$this->is_synced = false;
	}

	/**
	 * Load bundled items.
	 *
	 * @return void
	 * @since  4.7.0
	 */
	private function load_bundled_items() {

		foreach ( $this->bundle_data as $bundled_item_id => $bundled_item_data ) {

			$bundled_item = new WC_Bundled_Item( $bundled_item_id, $this );

			if ( $bundled_item->exists() ) {
				$this->bundled_items[ $bundled_item_id ] = $bundled_item;
			}
		}
	}

	/**
	 * Calculates min and max prices and availability status based on the bundled product data.
	 * Takes into account any defined variation filters.
	 *
	 * @return void
	 * @since  4.2.0
	 */
	public function sync_bundle() {

		if ( ! empty( $this->bundle_data ) ) {
			$this->load_bundled_items();
		}

		$this->min_bundle_price          = '';
		$this->max_bundle_price          = '';
		$this->min_bundle_regular_price  = '';
		$this->max_bundle_regular_price  = '';

		$has_infinite_max_price          = false;

		$is_front_end                    = WC_PB_Helpers::is_front_end();

		if ( empty( $this->bundled_items ) ) {
			return;
		}

		foreach ( $this->bundled_items as $bundled_item ) {

			$min_quantity = $bundled_item->get_quantity( 'min' );
			$max_quantity = $bundled_item->get_quantity( 'max' );

			if ( ! $bundled_item->is_sold_individually() ) {
				$this->all_items_sold_individually = false;
			}

			if ( $bundled_item->is_optional() ) {
				$this->contains_optional     = true;
				$this->suppress_range_format = true;
			} else {
				$this->all_items_optional = false;
			}

			if ( $bundled_item->is_out_of_stock() && ! $bundled_item->is_optional() && $min_quantity !== 0 ) {
				$this->all_items_in_stock = false;
			}

			if ( $bundled_item->is_on_backorder() && ! $bundled_item->is_optional() && $min_quantity !== 0 ) {
				$this->has_items_on_backorder = true;
			}

			if ( ! $bundled_item->is_purchasable() && ! $bundled_item->is_optional() && $min_quantity !== 0 ) {
				$this->all_items_purchasable = false;
			}

			if ( $bundled_item->get_discount() > 0 ) {
				$this->has_discounts = true;
			}

			if ( $bundled_item->is_nyp() ) {
				$this->contains_nyp          = true;
				$this->suppress_range_format = true;
				$has_infinite_max_price      = true;
			}

			if ( $bundled_item->is_sub() ) {
				$this->contains_sub = true;

				// If it's a variable sub with a variable price, show 'From:' string before Bundle price.
				if ( $bundled_item->is_variable_sub() ) {
					if ( $bundled_item->product->min_variation_price !== $bundled_item->product->max_variation_price || $bundled_item->product->subscription_period !== $bundled_item->product->max_variation_period || $bundled_item->product->subscription_period_interval !== $bundled_item->product->max_variation_period_interval ) {
						$this->suppress_range_format = true;
						$has_infinite_max_price      = true;
					}
				}
			}

			// Significant cost due to get_product_addons - skip this in the admin area since items_require_input is only used to modify add to cart button behaviour.
			if ( $is_front_end && ! $bundled_item->is_optional() && $bundled_item->requires_input() ) {
				$this->items_require_input = true;
			}

			if ( ! $bundled_item->is_visible() ) {
				$this->all_items_visible = false;
			}

			// Init quantities for min/max price calculations.
			$this->bundled_quantities_index[ 'min' ][ $bundled_item->item_id ] = $bundled_item->is_optional() ? 0 : $min_quantity;
			$this->bundled_quantities_index[ 'max' ][ $bundled_item->item_id ] = $max_quantity;
		}

		// Filter initialized quantities for min/max price calculations.
		$this->bundled_quantities_index = apply_filters( 'woocommerce_bundled_item_required_quantities', $this->bundled_quantities_index, $this );

		// Sync prices.
		foreach ( $this->bundled_items as $bundled_item ) {

			if ( $this->is_priced_per_product() ) {

				$bundled_item_qty_min           = $this->bundled_quantities_index[ 'min' ][ $bundled_item->item_id ];
				$bundled_item_qty_max           = $this->bundled_quantities_index[ 'max' ][ $bundled_item->item_id ];

				if ( $bundled_item_qty_min !== $bundled_item_qty_max ) {
					$this->suppress_range_format = true;
				}

				$this->min_bundle_price         = $this->min_bundle_price + $bundled_item_qty_min * (double) $bundled_item->min_price;
				$this->min_bundle_regular_price = $this->min_bundle_regular_price + $bundled_item_qty_min * (double) $bundled_item->min_regular_price;

				if ( ! $has_infinite_max_price && $bundled_item_qty_max ) {
					$this->max_bundle_price         = $this->max_bundle_price + $bundled_item_qty_max * (double) $bundled_item->max_price;
					$this->max_bundle_regular_price = $this->max_bundle_regular_price + $bundled_item_qty_max * (double) $bundled_item->max_regular_price;
				} else {
					$has_infinite_max_price = true;
				}
			}
		}

		if ( $this->is_priced_per_product() ) {

			$bundle_base_price     = $this->base_price;
			$bundle_base_reg_price = $this->base_regular_price;

			$this->min_bundle_price         += $bundle_base_price;
			$this->min_bundle_regular_price += $bundle_base_reg_price;

			if ( ! $has_infinite_max_price ) {
				$this->max_bundle_price         += $bundle_base_price;
				$this->max_bundle_regular_price += $bundle_base_reg_price;
			} else {
				$this->max_bundle_price = $this->max_bundle_regular_price = '';
			}

		} else {

			if ( $this->is_nyp() ) {

				$this->min_bundle_price = $this->min_bundle_regular_price = get_post_meta( $this->id, '_min_price', true );
				$this->max_bundle_price = $this->max_bundle_regular_price = '';

			} else {

				$this->min_bundle_price         = $this->max_bundle_price         = $this->price;
				$this->min_bundle_regular_price = $this->max_bundle_regular_price = $this->regular_price;
			}
		}

		$this->is_synced = true;

		// Allow adding to cart via ajax if no user input is required.
		if ( isset( $this->supports ) && $is_front_end && ! $this->requires_input() ) {
			$this->supports[] = 'ajax_add_to_cart';
		}

		do_action( 'woocommerce_bundles_synced_bundle', $this );

		$this->update_price_meta();
	}

	/**
	 * Update price meta for access in queries. Prices are unfiltered to remain static.
	 *
	 * @return void
	 */
	private function update_price_meta() {

		if ( apply_filters( 'woocommerce_bundles_update_price_meta', true, $this ) ) {

			if ( WC_PB_Helpers::is_front_end() && $this->is_priced_per_product() ) {
				if ( $this->price != $this->min_bundle_price ) {
					update_post_meta( $this->id, '_price', $this->min_bundle_price );
				}
				if ( $this->min_bundle_price < $this->min_bundle_regular_price ) {
					if ( $this->sale_price != $this->min_bundle_price ) {
						update_post_meta( $this->id, '_sale_price', $this->min_bundle_price );
						delete_transient( 'wc_products_onsale' );
					}
				} else {
					if ( $this->sale_price !== '' ) {
						update_post_meta( $this->id, '_sale_price', '' );
						delete_transient( 'wc_products_onsale' );
					}
				}
				if ( $this->regular_price != $this->min_bundle_regular_price ) {
					update_post_meta( $this->id, '_regular_price', $this->min_bundle_regular_price );
				}
			}
		}
	}

	/**
	 * Indicates if the bundle has been synced and all bundled contents loaded.
	 *
	 * @return boolean
	 */
	public function is_synced() {

		return $this->is_synced;
	}

	/**
	 * Stores bundle pricing data used by the front-end script.
	 *
	 * @return void
	 * @since  4.7.0
	 */
	private function load_price_data() {

		if ( empty( $this->bundle_price_data ) ) {

			$bundle_price_data = array();

			$bundle_price_data[ 'is_purchasable' ]               = $this->is_purchasable() ? 'yes' : 'no';
			$bundle_price_data[ 'per_product_pricing' ]          = $this->is_priced_per_product() ? 'yes' : 'no';
			$bundle_price_data[ 'show_free_string' ]             = ( $this->is_priced_per_product() ? apply_filters( 'woocommerce_bundle_show_free_string', false, $this ) : true ) ? 'yes' : 'no';

			$bundle_price_data[ 'prices' ]                       = array();
			$bundle_price_data[ 'regular_prices' ]               = array();

			$bundle_price_data[ 'prices_tax' ]                   = array();

			$bundle_price_data[ 'addons_prices' ]                = array();

			$bundle_price_data[ 'quantities' ]                   = array();

			$bundle_price_data[ 'product_ids' ]                  = array();

			$bundle_price_data[ 'is_sold_individually' ]         = array();

			$bundle_price_data[ 'recurring_prices' ]             = array();
			$bundle_price_data[ 'regular_recurring_prices' ]     = array();

			$bundle_price_data[ 'recurring_html' ]               = array();
			$bundle_price_data[ 'recurring_keys' ]               = array();

			if ( $this->is_priced_per_product() ) {
				$base_price          = $this->get_base_price();
				$base_regular_price  = $this->get_base_regular_price();
			} else {
				$base_price          = $this->get_price();
				$base_regular_price  = $this->get_regular_price();
			}

			WC_PB_Helpers::extend_price_display_precision();

			$base_price_incl_tax = $this->get_price_including_tax( 1, 1000 );
			$base_price_excl_tax = $this->get_price_excluding_tax( 1, 1000 );

			WC_PB_Helpers::reset_price_display_precision();

			$bundle_price_data[ 'base_price' ]         = $base_price;
			$bundle_price_data[ 'base_regular_price' ] = $base_regular_price;

			$bundle_price_data[ 'base_price_tax' ]     = $base_price_incl_tax / $base_price_excl_tax;

			$totals = new stdClass;

			$totals->price          = 0.0;
			$totals->regular_price  = 0.0;
			$totals->price_incl_tax = 0.0;
			$totals->price_excl_tax = 0.0;

			$bundle_price_data[ 'total' ]             = 0.0;
			$bundle_price_data[ 'regular_total' ]     = 0.0;
			$bundle_price_data[ 'total_incl_tax' ]    = 0.0;
			$bundle_price_data[ 'total_excl_tax' ]    = 0.0;

			$bundle_price_data[ 'base_price_totals' ] = $totals;
			$bundle_price_data[ 'totals' ]            = $totals;
			$bundle_price_data[ 'recurring_totals' ]  = $totals;

			$bundled_items = $this->get_bundled_items();

			if ( empty( $bundled_items ) ) {
				return;
			}

			foreach ( $bundled_items as $bundled_item ) {

				if ( ! $bundled_item->is_purchasable() ) {
					continue;
				}

				WC_PB_Helpers::extend_price_display_precision();

				$price_incl_tax = $bundled_item->product->get_price_including_tax( 1, 1000 );
				$price_excl_tax = $bundled_item->product->get_price_excluding_tax( 1, 1000 );

				WC_PB_Helpers::reset_price_display_precision();

				$bundle_price_data[ 'is_nyp' ][ $bundled_item->item_id ]                             = $bundled_item->is_nyp() ? 'yes' : 'no';

				$bundle_price_data[ 'product_ids' ][ $bundled_item->item_id ]                        = $bundled_item->product->id;

				$bundle_price_data[ 'is_sold_individually' ][ $bundled_item->item_id ]               = $bundled_item->is_sold_individually() ? 'yes' : 'no';

				$bundle_price_data[ 'prices' ][ $bundled_item->item_id ]                             = $bundled_item->get_bundled_item_price( 'min' );
				$bundle_price_data[ 'regular_prices' ][ $bundled_item->item_id ]                     = $bundled_item->get_bundled_item_regular_price( 'min' );

				$bundle_price_data[ 'prices_tax' ][ $bundled_item->item_id ]                         = $price_incl_tax / $price_excl_tax;

				$bundle_price_data[ 'addons_prices' ][ $bundled_item->item_id ]                      = '';

				$bundle_price_data[ 'bundled_item_' . $bundled_item->item_id . '_totals' ]           = $totals;
				$bundle_price_data[ 'bundled_item_' . $bundled_item->item_id . '_recurring_totals' ] = $totals;

				$bundle_price_data[ 'quantities' ][ $bundled_item->item_id ]                         = '';

				$bundle_price_data[ 'recurring_prices' ][ $bundled_item->item_id ]                   = '';
				$bundle_price_data[ 'regular_recurring_prices' ][ $bundled_item->item_id ]           = '';

				// Store sub recurring key for summation (variable sub keys are stored in variations data).
				$bundle_price_data[ 'recurring_html' ][ $bundled_item->item_id ]                     = '';
				$bundle_price_data[ 'recurring_keys' ][ $bundled_item->item_id ]                     = '';

				if ( $bundled_item->is_sub() && ! $bundled_item->is_variable_sub() ) {

					$bundle_price_data[ 'recurring_prices' ][ $bundled_item->item_id ]             = $bundled_item->get_bundled_item_recurring_price( 'min' );
					$bundle_price_data[ 'regular_recurring_prices' ][ $bundled_item->item_id ]     = $bundled_item->get_bundled_item_regular_recurring_price( 'min' );

					$bundle_price_data[ 'recurring_keys' ][ $bundled_item->item_id ]               = str_replace( '_synced', '', WC_Subscriptions_Cart::get_recurring_cart_key( array( 'data' => $bundled_item->product ), ' ' ) );
					$bundle_price_data[ 'recurring_html' ][ $bundled_item->item_id ]               = WC_PB_Helpers::get_recurring_price_html_component( $bundled_item->product );
				}
			}

			if ( $this->is_priced_per_product() && $this->contains_sub ) {
				if ( $this->get_bundle_regular_price( 'min' ) != 0 ) {
					$bundle_price_data[ 'price_string' ] = sprintf( _x( '%1$s<span class="bundled_subscriptions_price_html" style="display:none"> now,</br>then %2$s</span>', 'subscription price html suffix', 'woocommerce-product-bundles' ), '%s', '%r' );
				} else {
					$bundle_price_data[ 'price_string' ] = '<span class="bundled_subscriptions_price_html">%r</span>';
				}
			} else {
				$bundle_price_data[ 'price_string' ] = '%s';
			}

			$this->bundle_price_data = apply_filters( 'woocommerce_bundle_price_data', $bundle_price_data, $this );
		}
	}

	/**
	 * Gets price data array. Contains localized strings and price data passed to JS.
	 *
	 * @return array localized strings and price data passed to JS
	 */
	public function get_bundle_price_data() {

		if ( ! $this->is_synced() ) {
			$this->sync_bundle();
		}

		$this->load_price_data();

		return $this->bundle_price_data;
	}

	/**
	 * Bundle is a NYP product.
	 *
	 * @return boolean
	 */
	public function is_nyp() {

		return $this->is_nyp;
	}

	/**
	 * Bundle contains NYP products.
	 *
	 * @return boolean
	 */
	public function contains_nyp() {

		if ( ! $this->is_synced() ) {
			$this->sync_bundle();
		}

		return $this->contains_nyp;
	}

	/**
	 * Bundle contains optional items.
	 *
	 * @return boolean
	 */
	public function contains_optional( $exclusively = false ) {

		if ( ! $this->is_synced() ) {
			$this->sync_bundle();
		}

		if ( $exclusively ) {
			return $this->all_items_optional;
		}

		return $this->contains_optional;
	}

	/**
	 * Bundle is priced per product.
	 * @return boolean
	 */
	public function is_priced_per_product() {

		$is_ppp = false;

		if ( $this->per_product_pricing_active ) {
			$is_ppp = true;
		}

		return apply_filters( 'woocommerce_bundle_is_priced_per_product', $is_ppp, $this );
	}

	/**
	 * Bundle is shipped per product.
	 * @return boolean
	 */
	public function is_shipped_per_product() {

		$is_spp = false;

		if ( $this->per_product_shipping_active ) {
			$is_spp = true;
		}

		return apply_filters( 'woocommerce_bundle_is_shipped_per_product', $is_spp, $this );
	}

	/**
	 * Gets the attributes of all variable bundled items.
	 *
	 * @return array attributes array
	 */
	public function get_bundle_variation_attributes() {

		if ( ! $this->is_synced() ) {
			$this->sync_bundle();
		}

		if ( empty( $this->bundled_items ) ) {
			return array();
		}

		$bundle_attributes = array();
		$bundled_items     = $this->get_bundled_items();

		foreach ( $bundled_items as $bundled_item ) {
			$bundle_attributes[ $bundled_item->item_id ] = $bundled_item->get_product_variation_attributes();
		}

		return $bundle_attributes;
	}

	/**
	 * Gets default (overriden) selections for variable product attributes.
	 *
	 * @return array default attribute selections.
	 */
	public function get_selected_bundle_variation_attributes() {

		if ( ! $this->is_synced() ) {
			$this->sync_bundle();
		}

		if ( empty( $this->bundled_items ) ) {
			return array();
		}

		$seleted_bundle_attributes = array();
		$bundled_items             = $this->get_bundled_items();

		foreach ( $bundled_items as $bundled_item ) {
			$seleted_bundle_attributes[ $bundled_item->item_id ] = $bundled_item->get_selected_product_variation_attributes();
		}

		return $seleted_bundle_attributes;
	}

	/**
	 * Gets product variation data which is passed to JS.
	 *
	 * @return array variation data array
	 */
	public function get_available_bundle_variations() {

		if ( ! $this->is_synced() ) {
			$this->sync_bundle();
		}

		if ( empty( $this->bundled_items ) ) {
			return array();
		}

		$bundle_variations = array();
		$bundled_items     = $this->get_bundled_items();

		foreach ( $bundled_items as $bundled_item ) {
			$bundle_variations[ $bundled_item->item_id ] = $bundled_item->get_product_variations();
		}

		return $bundle_variations;
	}

	/**
	 * Gets all bundled items.
	 *
	 * @return array  of WC_Bundled_Item objects
	 */
	public function get_bundled_items() {

		if ( ! $this->is_synced() ) {
			$this->sync_bundle();
		}

		if ( ! empty( $this->bundled_items ) ) {
			return apply_filters( 'woocommerce_bundled_items', $this->bundled_items, $this );
		}

		return false;
	}

	/**
	 * Checks if a specific bundled item exists.
	 *
	 * @param  $bundled_item_id
	 * @return boolean
	 */
	public function has_bundled_item( $bundled_item_id ) {

		if ( ! empty( $this->bundle_data ) && isset( $this->bundle_data[ $bundled_item_id ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Gets a specific bundled item.
	 *
	 * @param  $bundled_item_id
	 * @return WC_Bundled_Item
	 */
	public function get_bundled_item( $bundled_item_id ) {

		if ( ! empty( $this->bundle_data ) && isset( $this->bundle_data[ $bundled_item_id ] ) ) {
			if ( isset( $this->bundled_items[ $bundled_item_id ] ) ) {
				return $this->bundled_items[ $bundled_item_id ];
			} else {
				return new WC_Bundled_Item( $bundled_item_id, $this );
			}
		}

		return false;
	}

	/**
	 * In per-product pricing mode, get_price() returns the minimum price.
	 *
	 * @return 	double
	 */
	public function get_price() {
		return apply_filters( 'woocommerce_bundle_get_price', parent::get_price(), $this );
	}

	/**
	 * In per-product pricing mode, get_regular_price() returns the minimum regular price.
	 *
	 * @return 	double
	 */
	public function get_regular_price() {
		return apply_filters( 'woocommerce_bundle_get_regular_price', parent::get_regular_price(), $this );
	}

	/**
	 * In per-product pricing mode, get_sale_price() returns the minimum sale price.
	 *
	 * @return 	double
	 */
	public function get_sale_price() {
		return apply_filters( 'woocommerce_bundle_get_sale_price', parent::get_sale_price(), $this );
	}

	/**
	 * Get bundle base price.
	 *
	 * @return double
	 */
	public function get_base_price() {

		if ( $this->is_priced_per_product() ) {
			return apply_filters( 'woocommerce_bundle_get_base_price', $this->base_price, $this );
		} else {
			return false;
		}
	}

	/**
	 * Get bundle base regular price.
	 *
	 * @return double
	 */
	public function get_base_regular_price() {

		if ( $this->is_priced_per_product() ) {
			return apply_filters( 'woocommerce_bundle_get_base_regular_price', $this->base_regular_price, $this );
		} else {
			return false;
		}
	}

	/**
	 * Get bundle base regular price.
	 *
	 * @return double
	 */
	public function get_base_sale_price() {

		if ( $this->is_priced_per_product() ) {
			return apply_filters( 'woocommerce_bundle_get_base_sale_price', $this->base_sale_price, $this );
		} else {
			return false;
		}
	}

	/**
	 * Get min/max bundle price.
	 *
	 * @param  string $min_or_max
	 * @return double
	 */
	public function get_bundle_price( $min_or_max = 'min', $display = false ) {

		if ( $this->is_priced_per_product() ) {

			if ( ! $this->is_synced() ) {
				$this->sync_bundle();
			}

			$cache_key = ( $display ? 'display' : 'raw' ) . '_price_' . $min_or_max;

			if ( isset( $this->bundle_price_cache[ $cache_key ] ) ) {
				$price = $this->bundle_price_cache[ $cache_key ];
			} else {
				$prop = $min_or_max . '_bundle_price';
				if ( $this->$prop === '' ) {
					$price = '';
				} else {
					$price         = $display ? WC_PB_Helpers::get_product_display_price( $this, $this->get_base_price() ) : $this->get_base_price();
					$bundled_items = $this->get_bundled_items();
					if ( ! empty( $bundled_items ) ) {
						foreach ( $bundled_items as $bundled_item ) {
							$bundled_item_qty = $this->bundled_quantities_index[ $min_or_max ][ $bundled_item->item_id ];
							if ( $bundled_item_qty ) {
								$price += $bundled_item_qty * $bundled_item->get_bundled_item_price( $min_or_max, $display );
							}
						}
					}
				}
			}

		} else {

			$price = parent::get_price();

			if ( $display ) {
				$price = WC_PB_Core_Compatibility::is_wc_version_gte_2_4() ? parent::get_display_price( $price ) : WC_PB_Helpers::get_product_display_price( $this, $price );
			}
		}

		return $price;
	}

	/**
	 * Get min/max bundle regular price.
	 *
	 * @param  string $min_or_max
	 * @return double
	 */
	public function get_bundle_regular_price( $min_or_max = 'min', $display = false ) {

		if ( $this->is_priced_per_product() ) {

			if ( ! $this->is_synced() ) {
				$this->sync_bundle();
			}

			$cache_key = ( $display ? 'display' : 'raw' ) . '_regular_price_' . $min_or_max;

			if ( isset( $this->bundle_price_cache[ $cache_key ] ) ) {
				$price = $this->bundle_price_cache[ $cache_key ];
			} else {
				$prop = $min_or_max . '_bundle_regular_price';
				if ( $this->$prop === '' ) {
					$price = '';
				} else {
					$price         = $display ? WC_PB_Helpers::get_product_display_price( $this, $this->get_base_regular_price() ) : $this->get_base_regular_price();
					$bundled_items = $this->get_bundled_items();
					if ( ! empty( $bundled_items ) ) {
						foreach ( $bundled_items as $bundled_item ) {
							$bundled_item_qty = $this->bundled_quantities_index[ $min_or_max ][ $bundled_item->item_id ];
							if ( $bundled_item_qty ) {
								$price += $bundled_item_qty * $bundled_item->get_bundled_item_regular_price( $min_or_max, $display, true );
							}
						}
					}
				}
			}

		} else {

			$price = parent::get_regular_price();

			if ( $display ) {
				$price = WC_PB_Core_Compatibility::is_wc_version_gte_2_4() ? parent::get_display_price( $price ) : WC_PB_Helpers::get_product_display_price( $this, $price );
			}
		}

		return $price;
	}

	/**
	 * Bundle price including tax.
	 *
	 * @return double
	 */
	public function get_bundle_price_including_tax( $min_or_max = 'min', $qty = 1 ) {

		if ( $this->is_priced_per_product() ) {

			if ( ! $this->is_synced() ) {
				$this->sync_bundle();
			}

			$cache_key = 'price_incl_tax_' . $min_or_max . '_' . $qty;

			if ( isset( $this->bundle_price_cache[ $cache_key ] ) ) {
				$price = $this->bundle_price_cache[ $cache_key ];
			} else {
				$price         = $this->get_price_including_tax( $qty, $this->get_base_price() );
				$bundled_items = $this->get_bundled_items();

				if ( ! empty( $bundled_items ) ) {
					foreach ( $bundled_items as $bundled_item ) {
						$bundled_item_qty = $qty * $this->bundled_quantities_index[ $min_or_max ][ $bundled_item->item_id ];
						if ( $bundled_item_qty ) {
							$price += $bundled_item->get_bundled_item_price_including_tax( $min_or_max, $bundled_item_qty );
						}
					}
				}
			}

		} else {

			$price = parent::get_price_including_tax( $qty, parent::get_price() );
		}

		return $price;
	}

	/**
	 * Min/max bundle price excl tax.
	 *
	 * @return double
	 */
	public function get_bundle_price_excluding_tax( $min_or_max = 'min', $qty = 1 ) {

		if ( $this->is_priced_per_product() ) {

			if ( ! $this->is_synced() ) {
				$this->sync_bundle();
			}

			$cache_key = 'price_excl_tax_' . $min_or_max . '_' . $qty;

			if ( isset( $this->bundle_price_cache[ $cache_key ] ) ) {
				$price = $this->bundle_price_cache[ $cache_key ];
			} else {

				$price         = $this->get_price_excluding_tax( $qty, $this->get_base_price() );
				$bundled_items = $this->get_bundled_items();

				if ( ! empty( $bundled_items ) ) {
					foreach ( $bundled_items as $bundled_item ) {
						$bundled_item_qty = $qty * $this->bundled_quantities_index[ $min_or_max ][ $bundled_item->item_id ];
						if ( $bundled_item_qty ) {
							$price += $bundled_item->get_bundled_item_price_excluding_tax( $min_or_max, $bundled_item_qty );
						}
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
	 * Prices incl. or excl. tax are calculated based on the bundled products prices, so get_price_suffix() must be overridden to return the correct field in per-product pricing mode.
	 *
	 * @return 	string    modified price html suffix
	 */
	public function get_price_suffix( $price = '', $qty = 1 ) {

		if ( $this->is_priced_per_product() ) {

			$price_display_suffix = get_option( 'woocommerce_price_display_suffix' );

			if ( $price_display_suffix ) {

				$price_display_suffix = ' <small class="woocommerce-price-suffix">' . $price_display_suffix . '</small>';

				if ( false !== strpos( $price_display_suffix, '{price_including_tax}' ) ) {
					$price_display_suffix = str_replace( '{price_including_tax}', wc_price( $this->get_bundle_price_including_tax() * $qty ), $price_display_suffix );
				}

				if ( false !== strpos( $price_display_suffix, '{price_excluding_tax}' ) ) {
					$price_display_suffix = str_replace( '{price_excluding_tax}', wc_price( $this->get_bundle_price_excluding_tax() * $qty ), $price_display_suffix );
				}
			}

			return apply_filters( 'woocommerce_get_price_suffix', $price_display_suffix, $this );

		} else {

			return parent::get_price_suffix();
		}

	}

	/**
	 * Calculate subscriptions price html component by breaking up bundled subs into recurring scheme groups and adding up all prices in each group.
	 *
	 * @return string
	 */
	public function apply_subs_price_html( $price ) {

		if ( ! empty( $this->bundled_items ) ) {

			$bundled_items           = $this->get_bundled_items();
			$subs_details            = array();
			$subs_details_html       = array();
			$non_optional_subs_exist = false;

			foreach ( $bundled_items as $bundled_item_id => $bundled_item ) {

				if ( $bundled_item->is_sub() ) {

					$bundled_product = $bundled_item->product;

					if ( $bundled_item->is_variable_sub() ) {
						$product_id = get_post_meta( $bundled_product->id, '_min_price_variation_id', true );
						$product    = $bundled_product->get_child( $product_id );
					} else {
						$product = $bundled_product;
					}

					$sub_string = str_replace( '_synced', '', WC_Subscriptions_Cart::get_recurring_cart_key( array( 'data' => $product ), ' ' ) );

					if ( ! isset( $subs_details[ $sub_string ][ 'bundled_items' ] ) ) {
						$subs_details[ $sub_string ][ 'bundled_items' ] = array();
					}

					if ( ! isset( $subs_details[ $sub_string ][ 'price' ] ) ) {
						$subs_details[ $sub_string ][ 'price' ]         = 0;
						$subs_details[ $sub_string ][ 'regular_price' ] = 0;
						$subs_details[ $sub_string ][ 'is_range' ]      = false;
					}

					$subs_details[ $sub_string ][ 'bundled_items' ][] = $bundled_item_id;

					$subs_details[ $sub_string ][ 'price' ]         += $this->bundled_quantities_index[ 'min' ][ $bundled_item_id ] * WC_PB_Helpers::get_product_display_price( $product, $bundled_item->min_recurring_price );
					$subs_details[ $sub_string ][ 'regular_price' ] += $this->bundled_quantities_index[ 'min' ][ $bundled_item_id ] * WC_PB_Helpers::get_product_display_price( $product, $bundled_item->min_regular_recurring_price );

					if ( $bundled_item->is_variable_sub() ) {
						if ( $bundled_product->min_variation_price !== $bundled_product->max_variation_price || $bundled_product->subscription_period !== $bundled_product->max_variation_period || $bundled_product->subscription_period_interval !== $bundled_product->max_variation_period_interval ) {
							$subs_details[ $sub_string ][ 'is_range' ] = true;
						}
					}

					if ( ! isset( $subs_details[ $sub_string ][ 'price_html' ] ) ) {
						$subs_details[ $sub_string ][ 'price_html' ] = WC_PB_Helpers::get_recurring_price_html_component( $product );
					}
				}
			}

			if ( ! empty( $subs_details ) ) {

				foreach ( $subs_details as $sub_details ) {
					if ( $sub_details[ 'price' ] > 0 ) {

						$sub_price_html = wc_price( $sub_details[ 'price' ] );

						if ( $sub_details[ 'price' ] !== $sub_details[ 'regular_price' ] ) {

							$sub_regular_price_html = wc_price( $sub_details[ 'regular_price' ] );

							if ( $sub_details[ 'is_range' ] ) {
								$sub_price_html = sprintf( _x( '%1$s%2$s', 'Price range: from', 'woocommerce-product-bundles' ), _x( '<span class="from">from </span>', 'min-price', 'woocommerce-product-bundles' ), $this->get_price_html_from_to( $sub_regular_price_html, $sub_price_html ) );
							} else {
								$sub_price_html = $this->get_price_html_from_to( $sub_regular_price_html, $sub_price_html );
							}

						} elseif ( $sub_details[ 'price' ] == 0 && ! $sub_details[ 'is_range' ] ) {
							$sub_price_html = __( 'Free!', 'woocommerce' );
						} else {
							if ( $sub_details[ 'is_range' ] ) {
								$sub_price_html = sprintf( _x( '%1$s%2$s', 'Price range: from', 'woocommerce-product-bundles' ), _x( '<span class="from">from </span>', 'min-price', 'woocommerce-product-bundles' ), $sub_price_html );
							}
						}

						$sub_price_html      = sprintf( $sub_details[ 'price_html' ], $sub_price_html );
						$subs_details_html[] = '<span class="bundled_sub_price_html">' . $sub_price_html . '</span>';
					}
				}

				$price_html = implode( '<span class="plus"> + </span>', $subs_details_html );

				if ( $this->get_bundle_regular_price( 'min' ) != 0 ) {
					$price = sprintf( _x( '%1$s<span class="bundled_subscriptions_price_html" %2$s> now,</br>then %3$s</span>', 'subscription price html suffix', 'woocommerce-product-bundles' ), $price, ! empty( $subs_details_html ) ? '' : 'style="display:none"', $price_html );
				} else {
					$price = '<span class="bundled_subscriptions_price_html">' . $price_html . '</span>';
				}
			}
		}

		return $price;
	}

	/**
	 * Returns range style html price string without min and max.
	 *
	 * @param  mixed    $price    default price
	 * @return string             overridden html price string (old style)
	 */
	public function get_price_html( $price = '' ) {

		if ( ! $this->is_purchasable() ) {
			return apply_filters( 'woocommerce_bundle_empty_price_html', '', $this );
		}

		if ( $this->is_priced_per_product() ) {

			if ( ! $this->is_synced() ) {
				$this->sync_bundle();
			}

			// Get the price.
			if ( $this->get_bundle_price( 'min' ) === '' ) {
				$price = apply_filters( 'woocommerce_bundle_empty_price_html', '', $this );
			} else {

				$suppress_range_format = $this->suppress_range_format || apply_filters( 'woocommerce_bundle_force_old_style_price_html', false, $this );

				if ( $suppress_range_format ) {

					$price = wc_price( $this->get_bundle_price( 'min', true ) );

					if ( $this->get_bundle_regular_price( 'min', true ) !== $this->get_bundle_price( 'min', true ) ) {

						$regular_price = wc_price( $this->get_bundle_regular_price( 'min', true ) );

						if ( $this->get_bundle_price( 'min', true ) !== $this->get_bundle_price( 'max', true ) ) {
							$price = sprintf( _x( '%1$s%2$s', 'Price range: from', 'woocommerce-product-bundles' ), $this->get_price_html_from_text(), $this->get_price_html_from_to( $regular_price, $price ) . $this->get_price_suffix() );
						} else {
							$price = $this->get_price_html_from_to( $regular_price, $price ) . $this->get_price_suffix();
						}

						$price = apply_filters( 'woocommerce_bundle_sale_price_html', $price, $this );

					} elseif ( $this->get_bundle_price( 'min', true ) === 0 && $this->get_bundle_price( 'max', true ) === 0 ) {

						$free_string = apply_filters( 'woocommerce_bundle_show_free_string', false, $this ) ? __( 'Free!', 'woocommerce' ) : $price;
						$price       = apply_filters( 'woocommerce_bundle_free_price_html', $free_string, $this );

					} else {

						if ( $this->get_bundle_price( 'min', true ) !== $this->get_bundle_price( 'max', true ) ) {
							$price = sprintf( _x( '%1$s%2$s', 'Price range: from', 'woocommerce-product-bundles' ), $this->get_price_html_from_text(), $price . $this->get_price_suffix() );
						} else {
							$price = $price . $this->get_price_suffix();
						}

						$price = apply_filters( 'woocommerce_bundle_price_html', $price, $this );
					}

				} else {

					if ( $this->get_bundle_price( 'min', true ) !== $this->get_bundle_price( 'max', true ) ) {
						$price = sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $this->get_bundle_price( 'min', true ) ), wc_price( $this->get_bundle_price( 'max', true ) ) );
					} else {
						$price = wc_price( $this->get_bundle_price( 'min', true ) );
					}

					if ( $this->get_bundle_regular_price( 'max', true ) !== $this->get_bundle_price( 'max', true ) || $this->get_bundle_regular_price( 'min', true ) !== $this->get_bundle_price( 'min', true ) ) {

						if ( $this->get_bundle_regular_price( 'min', true ) !== $this->get_bundle_regular_price( 'max', true ) ) {
							$regular_price = sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $this->get_bundle_regular_price( 'min', true ) ), wc_price( $this->get_bundle_regular_price( 'max', true ) ) );
						} else {
							$regular_price = wc_price( $this->get_bundle_regular_price( 'min', true ) );
						}

						$price = apply_filters( 'woocommerce_bundle_sale_price_html', $this->get_price_html_from_to( $regular_price, $price ) . $this->get_price_suffix(), $this );

					} elseif ( $this->get_bundle_price( 'min', true ) === 0 && $this->get_bundle_price( 'max', true ) === 0 ) {

						$free_string = apply_filters( 'woocommerce_bundle_show_free_string', false, $this ) ? __( 'Free!', 'woocommerce' ) : $price;
						$price       = apply_filters( 'woocommerce_bundle_free_price_html', $free_string, $this );

					} else {
						$price = apply_filters( 'woocommerce_bundle_price_html', $price . $this->get_price_suffix(), $this );
					}
				}
			}

			$price = apply_filters( 'woocommerce_get_bundle_price_html', $price, $this );

			if ( $this->contains_sub ) {
				$price = $this->apply_subs_price_html( $price );
			}

			return apply_filters( 'woocommerce_get_price_html', $price, $this );

		} else {

			return parent::get_price_html();
		}
	}

	/**
	 * True if the bundle contains a sub.
	 *
	 * @return boolean
	 */
	public function contains_sub() {

		if ( ! $this->is_synced() ) {
			$this->sync_bundle();
		}

		return $this->contains_sub;
	}

	/**
	 * True if all bundled items are in stock in the desired quantities.
	 *
	 * @return boolean  true if all in stock
	 */
	public function all_items_in_stock() {

		if ( ! $this->is_synced() ) {
			$this->sync_bundle();
		}

		return $this->all_items_in_stock;
	}

	/**
	 * Override on_sale status of product bundles. If a bundled item is on sale or has a discount applied, then the bundle appears as on sale.
	 *
	 * @return 	boolean    sale status of bundle
	 */
	public function is_on_sale() {

		$is_on_sale = false;

		if ( $this->is_priced_per_product() ) {
			if ( ! $this->is_synced() ) {
				$this->sync_bundle();
			}
			$is_on_sale = ( ( $this->get_base_sale_price() !== $this->get_base_regular_price() && $this->get_base_sale_price() === $this->get_base_price() ) || ( $this->has_discounts && $this->get_bundle_regular_price( 'min' ) > 0 ) );
		} else {
			$is_on_sale = parent::is_on_sale();
		}

		return apply_filters( 'woocommerce_product_is_on_sale', $is_on_sale, $this );
	}

	/**
	 * A bundle is sold individually if it is marked as an "individually-sold" product, or if all bundled items are sold individually.
	 *
	 * @return 	boolean    sold individually status
	 */
	public function is_sold_individually() {

		if ( ! $this->is_synced() ) {
			$this->sync_bundle();
		}

		return parent::is_sold_individually() || $this->all_items_sold_individually;
	}

	/**
	 * A bundle is purchasable if it contains (purchasable) bundled items.
	 *
	 * @return boolean
	 */
	public function is_purchasable() {

		if ( ! $this->is_synced() ) {
			$this->sync_bundle();
		}

		$purchasable = true;

		// Products must exist of course.
		if ( ! $this->exists() ) {
			$purchasable = false;

		// When priced statically a price needs to be set.
		} elseif ( $this->is_priced_per_product() == false && $this->get_price() === '' ) {
			$purchasable = false;

		// Check the product is published.
		} elseif ( $this->post->post_status !== 'publish' && ! current_user_can( 'edit_post', $this->id ) ) {
			$purchasable = false;

		// check if the product contains anything.
		} elseif ( false === $this->get_bundled_items() ) {
			$purchasable = false;

		// check if all non-optional contents are purchasable.
		} elseif ( false === $this->all_items_purchasable ) {
			$purchasable = false;
		}

		return apply_filters( 'woocommerce_is_purchasable', $purchasable, $this );
	}

	/**
	 * A bundle appears "on backorder" if the container is on backorder, or if a bundled item is on backorder (and requires notification).
	 *
	 * @return 	boolean    true if on backorder
	 */
	public function is_on_backorder( $qty_in_cart = 0 ) {

		if ( ! $this->is_synced() ) {
			$this->sync_bundle();
		}

		return parent::is_on_backorder() || $this->has_items_on_backorder;
	}

	/**
	 * A bundle on backorder requires notification if the container is defined like this, or a bundled item is on backorder and requires notification.
	 *
	 * @return 	boolean    true if backorders require notification or if has items on backorder
	 */
	public function backorders_require_notification() {

		if ( ! $this->is_synced() ) {
			$this->sync_bundle();
		}

		return parent::backorders_require_notification() || $this->has_items_on_backorder;
	}

	/**
	 * Availability of bundle based on bundle-level stock and bundled-items-level stock.
	 *
	 * @return 	array    availability data array
	 */
	public function get_availability() {

		$availability = parent::get_availability();

		if ( is_woocommerce() ) {

			if ( ! $this->is_synced() ) {
				$this->sync_bundle();
			}

			if ( parent::is_in_stock() && ! $this->all_items_in_stock() ) {

				$availability[ 'availability' ] = __( 'Insufficient stock', 'woocommerce-product-bundles' );

			} elseif ( parent::is_in_stock() && $this->has_items_on_backorder ) {

				$availability[ 'availability' ] = __( 'Available on backorder', 'woocommerce' );
				$availability[ 'class' ]        = 'available-on-backorder';
			}
		}

		return $availability;
	}

	/**
	 * True if the product is in stock and all bundled items are in stock.
	 *
	 * @return bool
	 */
	public function is_in_stock() {

		$is_in_stock = parent::is_in_stock();

		if ( $is_in_stock ) {

			if ( is_woocommerce() ) {

				if ( ! $this->is_synced() ) {
					$this->sync_bundle();
				}

				if ( ! $this->all_items_in_stock() ) {
					$is_in_stock = false;
				}
			}
		}

		return $is_in_stock;
	}

	/**
	 * Returns whether or not the bundle has any attributes set. Takes into account the attributes of all bundled products.
	 *
	 * @return 	boolean		true if the bundle has any attributes of its own, or if any of the bundled items has attributes
	 */
	public function has_attributes() {

		// check bundle for attributes.
		if ( sizeof( $this->get_attributes() ) > 0 ) {

			foreach ( $this->get_attributes() as $attribute ) {

				if ( isset( $attribute[ 'is_visible' ] ) && $attribute[ 'is_visible' ] ) {
					return true;
				}
			}
		}

		// Check all bundled items for attributes.
		$bundled_items = $this->get_bundled_items();

		if ( ! empty( $bundled_items ) && apply_filters( 'woocommerce_bundle_show_bundled_product_attributes', true, $this ) ) {

			foreach ( $bundled_items as $bundled_item ) {

				if ( ! $bundled_item->is_visible() ) {
					continue;
				}

				$bundled_product = $bundled_item->product;

				if ( sizeof( $bundled_product->get_attributes() ) > 0 ) {

					foreach ( $bundled_product->get_attributes() as $attribute ) {

						if ( isset( $attribute[ 'is_visible' ] ) && $attribute[ 'is_visible' ] ) {
							return true;
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Lists a table of attributes for the bundle page.
	 *
	 * @return 	void
	 */
	public function list_attributes() {

		// show attributes attached to the bundle only
		wc_get_template( 'single-product/product-attributes.php', array(
			'product' => $this
		), '', '' );

		$bundled_items = $this->get_bundled_items();

		if ( ! empty( $bundled_items ) && apply_filters( 'woocommerce_bundle_show_bundled_product_attributes', true, $this ) ) {

			foreach ( $bundled_items as $bundled_item ) {

				if ( ! $bundled_item->is_visible() ) {
					continue;
				}

				$bundled_product = $bundled_item->product;

				if ( ! $this->is_shipped_per_product() ) {
					$bundled_product->length = $bundled_product->width = $bundled_product->height = $bundled_product->weight = '';
				}

				if ( $bundled_product->has_attributes() ) {

					echo '<h3>' . $bundled_item->get_title() . '</h3>';

					// Filter bundled item attributes based on active variation filters.
					add_filter( 'woocommerce_attribute',  array( $this, 'bundled_item_attribute' ), 10, 3 );

					$this->listing_attributes_of = $bundled_item->item_id;

					wc_get_template( 'single-product/product-attributes.php', array(
						'product' => $bundled_product
					), '', '' );

					$this->listing_attributes_of = '';

					remove_filter( 'woocommerce_attribute',  array( $this, 'bundled_item_attribute' ), 10, 3 );
				}
			}
		}
	}

	/**
	 * Hide attributes if they correspond to filtered-out variations.
	 *
	 * @param  string   $output     original output
	 * @param  array    $attribute  attribute data
	 * @param  array    $values     attribute values
	 * @return string               modified output
	 */
	public function bundled_item_attribute( $output, $attribute, $values ) {

		if ( $attribute[ 'is_variation' ] ) {

			$variation_attribute_values = array();

			$bundled_item            = $this->get_bundled_item( $this->listing_attributes_of );
			$bundled_item_variations = $bundled_item->get_product_variations();

			if ( empty( $bundled_item_variations ) ) {
				return $output;
			}

			$attribute_key = 'attribute_' . sanitize_title( $attribute[ 'name' ] );

			// Find active attribute values from the bundled item variation data.
			foreach ( $bundled_item_variations as $variation_data ) {
				if ( isset( $variation_data[ 'attributes' ][ $attribute_key ] ) ) {
					$variation_attribute_values[] = $variation_data[ 'attributes' ][ $attribute_key ];
					$variation_attribute_values   = array_unique( $variation_attribute_values );
				}
			}

			if ( ! empty( $variation_attribute_values ) && in_array( '', $variation_attribute_values ) ) {
				return $output;
			}

			$attribute_name = $attribute[ 'name' ];

			$filtered_values = array();

			if ( $attribute[ 'is_taxonomy' ] ) {

				$product_terms = WC_PB_Core_Compatibility::wc_get_product_terms( $bundled_item->product_id, $attribute_name, array( 'fields' => 'all' ) );

				foreach ( $product_terms as $product_term ) {
					if ( in_array( $product_term->slug, $variation_attribute_values ) ) {
						$filtered_values[] = $product_term->name;
					}
				}

				return wpautop( wptexturize( implode( ', ', $filtered_values ) ) );

			} else {

				foreach ( $values as $value ) {

					$check_value = WC_PB_Core_Compatibility::is_wc_version_gte_2_4() ? $value : sanitize_title( $value );

					if ( in_array( $check_value, $variation_attribute_values ) ) {
						$filtered_values[] = $value;
					}
				}

				return wpautop( wptexturize( implode( ', ', $filtered_values ) ) );
			}
		}

		return $output;
	}

	/**
	 * Get the add to url used mainly in loops.
	 *
	 * @return 	string
	 */
	public function add_to_cart_url() {

		$url = esc_url( $this->is_purchasable() && $this->is_in_stock() && ! $this->requires_input() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id ) ) : get_permalink( $this->id ) );

		return apply_filters( 'bundle_add_to_cart_url', $url, $this );
	}

	/**
	 * Get the add to cart button text.
	 *
	 * @return 	string
	 */
	public function add_to_cart_text() {

		$text = __( 'Read more', 'woocommerce' );

		if ( $this->is_purchasable() && $this->is_in_stock() ) {

			if ( $this->requires_input() ) {

				if ( $this->all_items_visible ) {
					$text =  __( 'Select options', 'woocommerce' );
				} else {
					$text =  __( 'View contents', 'woocommerce-product-bundles' );
				}

			} else {
				$text =  __( 'Add to cart', 'woocommerce' );
			}
		}

		return apply_filters( 'bundle_add_to_cart_text', $text, $this );
	}

	/**
	 * A bundle requires user input if: ( is nyp ) or ( has required addons ) or ( has items with variables ).
	 *
	 * @return boolean  true if it needs configuration before adding to cart
	 */
	public function requires_input() {

		if ( ! $this->is_synced() ) {
			$this->sync_bundle();
		}

		$requires_input = false;

		if ( $this->is_nyp || WC_PB()->compatibility->has_required_addons( $this->id ) || $this->items_require_input ) {
			$requires_input = true;
		}

		return apply_filters( 'woocommerce_bundle_requires_input', $requires_input, $this );
	}

	/**
	 * Deprecated methods.
	 *
	 * @deprecated
	 */
	public function has_variables() {
		_deprecated_function( 'has_variables()', '4.11.7', 'requires_input()' );
		return $this->requires_input();
	}

	public function get_max_bundle_regular_price() {
		_deprecated_function( 'get_max_bundle_regular_price()', '4.11.4', 'get_bundle_regular_price()' );
		return $this->get_bundle_regular_price( 'max', true );
	}

	public function get_min_bundle_regular_price() {
		_deprecated_function( 'get_min_bundle_regular_price()', '4.11.4', 'get_bundle_regular_price()' );
		return $this->get_bundle_regular_price( 'min', true );
	}

	public function get_max_bundle_price() {
		_deprecated_function( 'get_max_bundle_price()', '4.11.4', 'get_bundle_price()' );
		return $this->get_bundle_price( 'max', true );
	}

	public function get_min_bundle_price() {
		_deprecated_function( 'get_min_bundle_price()', '4.11.4', 'get_bundle_price()' );
		return $this->get_bundle_price( 'min', true );
	}

	public function get_min_bundle_price_incl_tax() {
		_deprecated_function( 'get_min_bundle_price_incl_tax()', '4.11.4', 'get_bundle_price_including_tax()' );
		return $this->get_bundle_price_including_tax( 'min' );
	}

	public function get_min_bundle_price_excl_tax() {
		_deprecated_function( 'get_min_bundle_price_excl_tax()', '4.11.4', 'get_bundle_price_excluding_tax()' );
		return $this->get_bundle_price_excluding_tax( 'min' );
	}
}

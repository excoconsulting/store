<?php
/**
 * Bundled Item Container.
 *
 * The bunded item class is a container that initializes and holds all pricing, availability and variation/attribute-related data of a bundled item.
 *
 * @class   WC_Bundled_Item
 * @version 4.14.7
 * @since   4.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Bundled_Item {

	public $item_id;
	private $item_data;

	public $product_id;
	public $product;

	public $bundle_id;

	private $optional;
	private $quantity;
	private $discount;
	private $sign_up_discount;

	private $per_product_pricing;

	public $title;
	public $description;
	public $visibility;

	private $selection_overrides;
	private $allowed_variations;

	private $purchasable;
	private $sold_individually;
	private $on_sale;
	private $nyp;

	private $stock_status;
	private $total_stock;

	/**
	 * Raw meta prices used in the min/max bundle price calculation.
	 * @var string
	 */
	public $min_price;
	public $max_price;
	public $min_regular_price;
	public $max_regular_price;
	public $min_recurring_price;
	public $max_recurring_price;
	public $min_regular_recurring_price;
	public $max_regular_recurring_price;

	public $min_price_product;
	public $max_price_product;
	public $min_regular_price_product;
	public $max_regular_price_product;

	private $product_attributes;
	private $selected_product_attributes;

	private $product_variations;

	private $is_front_end;

	public function __construct( $bundled_item_id, $parent ) {

		$this->item_id    = $bundled_item_id;
		$this->product_id = $parent->bundle_data[ $bundled_item_id ][ 'product_id' ];
		$this->bundle_id  = $parent->id;
		$this->item_data  = $parent->bundle_data[ $bundled_item_id ];

		$this->visibility = array(
			'product' => 'visible',
			'cart'    => 'visible',
			'order'   => 'visible',
		);

		// Do not process bundled item stock data in the back end, in order to speed things up just a bit.
		$this->is_front_end = WC_PB_Helpers::is_front_end();

		do_action( 'woocommerce_before_init_bundled_item', $this );

		$bundled_product = wc_get_product( $this->product_id );

		// if not present, item cannot be purchased.
		if ( $bundled_product ) {

			$this->product               = $bundled_product;

			$this->title                 = ! empty( $this->item_data[ 'override_title' ] ) && $this->item_data[ 'override_title' ] === 'yes' ? $this->item_data[ 'product_title' ] : $bundled_product->get_title();
			$this->description           = ! empty( $this->item_data[ 'override_description' ] ) && $this->item_data[ 'override_description' ] === 'yes' ? $this->item_data[ 'product_description' ] : $bundled_product->post->post_excerpt;
			$this->optional              = ! empty( $this->item_data[ 'optional' ] ) && $this->item_data[ 'optional' ] === 'yes' ? 'yes' : 'no';
			$this->hide_thumbnail        = ! empty( $this->item_data[ 'hide_thumbnail' ] ) && $this->item_data[ 'hide_thumbnail' ] === 'yes' ? 'yes' : 'no';
			$this->quantity              = isset( $this->item_data[ 'bundle_quantity' ] ) ? absint( $this->item_data[ 'bundle_quantity' ] ) : 1;
			$this->discount              = ! empty( $this->item_data[ 'bundle_discount' ] ) ? ( double ) $this->item_data[ 'bundle_discount' ] : 0.0;
			$this->sign_up_discount      = ! empty( $this->item_data[ 'bundle_sign_up_discount' ] ) ? ( double ) $this->item_data[ 'bundle_sign_up_discount' ] : 0.0;
			$this->selection_overrides   = ! empty( $this->item_data[ 'override_defaults' ] ) && $this->item_data[ 'override_defaults' ] === 'yes' ? $this->item_data[ 'bundle_defaults' ] : '';
			$this->allowed_variations    = ! empty( $this->item_data[ 'filter_variations' ] ) && $this->item_data[ 'filter_variations' ] === 'yes' ? $this->item_data[ 'allowed_variations' ] : '';
			$this->per_product_pricing   = $parent->is_priced_per_product();
			$this->sold_individually     = false;
			$this->on_sale               = false;
			$this->nyp                   = false;
			$this->purchasable           = false;

			if ( ! empty( $this->item_data[ 'visibility' ] ) ) {
				if ( is_array( $this->item_data[ 'visibility' ] ) ) {
					$this->visibility[ 'product' ] = ! empty( $this->item_data[ 'visibility' ][ 'product' ] ) && $this->item_data[ 'visibility' ][ 'product' ] === 'hidden' ? 'hidden' : 'visible';
					$this->visibility[ 'cart' ]    = ! empty( $this->item_data[ 'visibility' ][ 'cart' ] ) && $this->item_data[ 'visibility' ][ 'cart' ] === 'hidden' ? 'hidden' : 'visible';
					$this->visibility[ 'order' ]   = ! empty( $this->item_data[ 'visibility' ][ 'order' ] ) && $this->item_data[ 'visibility' ][ 'order' ] === 'hidden' ? 'hidden' : 'visible';
				} else {
					if ( $this->item_data[ 'visibility' ] === 'hidden' ) {
						$this->visibility[ 'product' ] = 'hidden';
					} elseif ( $this->item_data[ 'visibility' ] === 'secret' ) {
						$this->visibility[ 'product' ] = $this->visibility[ 'cart' ] = $this->visibility[ 'order' ] = 'hidden';
					}
				}
			}

			if ( $bundled_product->is_purchasable() ) {
				$this->purchasable = true;
				$this->init();
			}
		}

		do_action( 'woocommerce_after_init_bundled_item', $this );
	}

	/**
	 * Initializes a bundled item for access by the container: Calculates min and max prices, checks availability info, etc.
	 *
	 * @return void
	 */
	public function init() {

		$product_id      = $this->product_id;
		$bundled_product = $this->product;

		$quantity        = $this->get_quantity();
		$discount        = $this->get_discount();

		/*------------------------------*/
		/*  Simple Subs                 */
		/*------------------------------*/

		if ( $bundled_product->product_type === 'subscription' ) {

			if ( $this->is_front_end ) {

				if ( $bundled_product->is_sold_individually() ) {
					$this->sold_individually = true;
				}

				if ( ! $bundled_product->is_in_stock() || ! $bundled_product->has_enough_stock( $quantity ) ) {
					$this->stock_status = 'out-of-stock';
				}

				if ( $bundled_product->is_on_backorder() && $bundled_product->backorders_require_notification() ) {
					$this->stock_status = 'available-on-backorder';
				}

				$this->total_stock = $bundled_product->get_total_stock();
			}

			if ( $this->is_priced_per_product() ) {

				// Recurring price.

				$regular_recurring_fee             = $this->get_raw_regular_price();
				$recurring_fee                     = $this->get_raw_price();

				$this->min_regular_recurring_price = $this->max_regular_recurring_price = $regular_recurring_fee;
				$this->min_recurring_price         = $this->max_recurring_price         = $recurring_fee;

				if ( $regular_recurring_fee > $recurring_fee ) {
					$this->on_sale = true;
				}

				// Sign up price.

				$signup_fee              = isset( $bundled_product->subscription_sign_up_fee ) ? $bundled_product->subscription_sign_up_fee : 0;

				$regular_up_front_fee    = $this->get_up_front_subscription_price( $regular_recurring_fee, $signup_fee, $bundled_product );
				$up_front_fee            = $this->get_up_front_subscription_price( $recurring_fee, $signup_fee, $bundled_product );

				$this->min_regular_price = $this->max_regular_price = $regular_up_front_fee;
				$this->min_price         = $this->max_price         = $up_front_fee;

				if ( $regular_up_front_fee > $up_front_fee ) {
					$this->on_sale = true;
				}
			}

		/*-----------------------------------*/
		/*  Simple Products                  */
		/*-----------------------------------*/

		} elseif ( $bundled_product->product_type === 'simple' ) {

			if ( $this->is_front_end ) {

				if ( $bundled_product->is_sold_individually() ) {
					$this->sold_individually = true;
				}

				if ( ! $bundled_product->is_in_stock() || ! $bundled_product->has_enough_stock( $quantity ) ) {
					$this->stock_status = 'out-of-stock';
				}

				if ( $bundled_product->is_on_backorder() && $bundled_product->backorders_require_notification() ) {
					$this->stock_status = 'available-on-backorder';
				}

				$this->total_stock = $bundled_product->get_total_stock();
			}

			if ( $this->is_priced_per_product() ) {

				$regular_price = $this->get_raw_regular_price();
				$price         = $this->get_raw_price();

				// Name your price support.

				if ( WC_PB()->compatibility->is_nyp( $bundled_product ) ) {

					$this->product->regular_price = $this->product->price = $regular_price = $price = WC_Name_Your_Price_Helpers::get_minimum_price( $product_id ) ? WC_Name_Your_Price_Helpers::get_minimum_price( $product_id ) : 0;
					$this->nyp     = true;
				}

				$this->min_regular_price = $this->max_regular_price = $regular_price;
				$this->min_price         = $this->max_price         = $price;

				if ( $regular_price > $price ) {
					$this->on_sale = true;
				}
			}

		/*-------------------------------------------*/
		/*	Variable Products                        */
		/*-------------------------------------------*/

		} elseif ( $bundled_product->product_type === 'variable' || $bundled_product->product_type === 'variable-subscription' ) {

			$calc_prices   = $this->is_priced_per_product();
			$min_variation = $max_variation = false;

			if ( $bundled_product->is_sold_individually() ) {
				$this->sold_individually = true;
			}

			// Without any variation filters present, we can just rely on parent methods.
			if ( empty( $this->allowed_variations ) ) {

				if ( $this->is_front_end ) {

					$this->total_stock = $bundled_product->get_total_stock();

					if ( ! $bundled_product->is_in_stock() || ( ! $bundled_product->backorders_allowed() && $bundled_product->managing_stock() && $this->total_stock < $quantity ) ) {
						$this->stock_status = 'out-of-stock';
					} else {
						$variation_in_stock_exists = false;
						foreach ( $bundled_product->get_children( true ) as $child_id ) {
							if ( 'yes' === get_post_meta( $child_id, '_manage_stock', true ) ) {
								$stock = get_post_meta( $child_id, '_stock', true );
								if ( $stock >= $quantity ) {
									$variation_in_stock_exists = true;
									break;
								}
							} else {
								$variation_in_stock_exists = true;
								break;
							}
						}
						if ( ! $variation_in_stock_exists ) {
							$this->stock_status = 'out-of-stock';
						}
					}

					if ( $bundled_product->is_on_backorder() && $bundled_product->backorders_require_notification() ) {
						$this->stock_status = 'available-on-backorder';
					}
				}

				if ( $calc_prices ) {

					if ( $bundled_product->product_type === 'variable-subscription' ) {

						if ( ! isset( $bundled_product->subscription_period ) || ! isset( $bundled_product->subscription_period_interval ) || ! isset( $bundled_product->max_variation_period ) || ! isset( $bundled_product->max_variation_period_interval ) ) {
							$bundled_product->variable_product_sync();
						}

						$min_variation_price_id = get_post_meta( $bundled_product->id, '_min_price_variation_id', true );
						$max_variation_price_id = get_post_meta( $bundled_product->id, '_max_price_variation_id', true );

					} else {

						if ( WC_PB_Core_Compatibility::is_wc_version_gte_2_4() ) {

							$variation_prices = $bundled_product->get_variation_prices( false );

							if ( ! empty( $discount ) && apply_filters( 'woocommerce_bundled_item_discount_from_regular', true, $this ) ) {
								$variation_price_ids = array_keys( $variation_prices[ 'regular_price' ] );
							} else {
								$variation_price_ids = array_keys( $variation_prices[ 'price' ] );
							}

							$min_variation_price_id = current( $variation_price_ids );
							$max_variation_price_id = end( $variation_price_ids );

						} else {

							if ( ! empty( $discount ) && apply_filters( 'woocommerce_bundled_item_discount_from_regular', true, $this ) ) {

								// Product may need to be synced.
								if ( $bundled_product->get_variation_regular_price( 'min', false ) === false ) {
									$bundled_product->variable_product_sync();
								}

								$min_variation_price_id = get_post_meta( $this->product_id, '_min_regular_price_variation_id', true );
								$max_variation_price_id = get_post_meta( $this->product_id, '_max_regular_price_variation_id', true );

							} else {

								// Product may need to be synced.
								if ( $bundled_product->get_variation_price( 'min', false ) === false ) {
									$bundled_product->variable_product_sync();
								}

								$min_variation_price_id = get_post_meta( $this->product_id, '_min_price_variation_id', true );
								$max_variation_price_id = get_post_meta( $this->product_id, '_max_price_variation_id', true );
							}
						}
					}

					$min_variation = $bundled_product->get_child( $min_variation_price_id );
					$max_variation = $bundled_product->get_child( $max_variation_price_id );
				}

			// When variation filters are present, we need to iterate over the variations.
			} else {

				$variation_in_stock_exists   = $this->is_front_end ? false : true;
				$all_variations_on_backorder = $this->is_front_end ? true : false;

				$min_variation_price         = '';
				$max_variation_price         = '';

				$this->total_stock = max( 0, wc_stock_amount( $bundled_product->stock ) );

				foreach ( $bundled_product->get_children( true ) as $child_id ) {

					// Do not continue if variation is filtered.
					if ( is_array( $this->allowed_variations ) && ! in_array( $child_id, $this->allowed_variations ) ) {
						continue;
					}

					$variation = $bundled_product->get_child( $child_id );

					if ( ! $variation ) {
						continue;
					}

					// Stock status.
					if ( ! $variation_in_stock_exists ) {
						if ( $variation->is_in_stock() && $variation->has_enough_stock( $quantity ) ) {
							$variation_in_stock_exists = true;
						}
					}

					// Total stock.
					if ( $variation->managing_stock() ) {
						$this->total_stock += max( 0, wc_stock_amount( $variation->stock ) );
					}

					// Backorder.
					if ( $all_variations_on_backorder ) {
						if ( $bundled_product->backorders_allowed() && $bundled_product->backorders_require_notification() ) {
							if ( ! $variation->is_on_backorder() ) {
								$all_variations_on_backorder = false;
							}
						} else {
							$all_variations_on_backorder = false;
						}
					}

					// Prices.
					if ( $calc_prices ) {

						if ( ! empty( $discount ) && apply_filters( 'woocommerce_bundled_item_discount_from_regular', true, $this ) ) {

							// Lowest price.
							if ( '' === $min_variation_price || $variation->regular_price < $min_variation_price ) {
								$min_variation_price = $variation->regular_price;
								$min_variation       = $variation;
							}

							// Highest price.
							if ( '' === $max_variation_price || $variation->regular_price > $max_variation_price ) {
								$max_variation_price = $variation->regular_price;
								$max_variation       = $variation;
							}

						} else {

							// Lowest price.
							if ( '' === $min_variation_price || $variation->price < $min_variation_price ) {
								$min_variation_price = $variation->price;
								$min_variation       = $variation;
							}

							// Highest price.
							if ( '' === $max_variation_price || $variation->price > $max_variation_price ) {
								$max_variation_price = $variation->price;
								$max_variation       = $variation;
							}
						}
					}
				}

				if ( ! $variation_in_stock_exists ) {
					$this->stock_status = 'out-of-stock';
				}

				if ( $all_variations_on_backorder ) {
					$this->stock_status = 'available-on-backorder';
				}
			}

			if ( $min_variation && $max_variation ) {

				$this->min_price_product = $this->min_regular_price_product = $min_variation;
				$this->max_price_product = $this->min_regular_price_product = $max_variation;

				if ( $bundled_product->product_type === 'variable-subscription' ) {

					$this->min_recurring_price         = $this->max_recurring_price         = $this->get_raw_price( $min_variation );
					$this->min_regular_recurring_price = $this->max_regular_recurring_price = $this->get_raw_regular_price( $min_variation );

					$min_signup_fee                    = isset( $min_variation->subscription_sign_up_fee ) ? $min_variation->subscription_sign_up_fee : 0;

					$min_regular_up_front_fee          = $this->get_up_front_subscription_price( $this->min_regular_recurring_price, $min_signup_fee, $min_variation );
					$min_up_front_fee                  = $this->get_up_front_subscription_price( $this->min_recurring_price, $min_signup_fee, $min_variation );

					$this->min_regular_price           = $this->max_regular_price = $min_regular_up_front_fee;
					$this->min_price                   = $this->max_price         = $min_up_front_fee;

				} else {

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

				if ( $this->min_regular_price > $this->min_price || $this->max_regular_price > $this->max_price ) {
					$this->on_sale = true;
				}
			}
		}
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

		if ( ! $this->is_priced_per_product() ) {
			return 0;
		}

		if ( apply_filters( 'woocommerce_bundled_item_discount_from_regular', true, $this ) ) {
			$regular_price = $product->regular_price;
		} else {
			$regular_price = $price;
		}

		$discount           = $this->get_discount();
		$bundled_item_price = empty( $discount ) ? $price : ( empty( $regular_price ) ? $regular_price : round( ( double ) $regular_price * ( 100 - $discount ) / 100, WC_PB_Core_Compatibility::wc_get_price_decimals() ) );

		$price = apply_filters( 'woocommerce_bundled_item_price', $bundled_item_price, $product, $discount, $this );

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

		if ( ! $this->is_priced_per_product() ) {
			return 0;
		}

		$regular_price = empty( $regular_price ) ? $product->price : $regular_price;

		return $regular_price;
	}

	/**
	 * Get bundled item price, after discount, filters included.
	 *
	 * @param  string  $min_or_max
	 * @param  boolean $display
	 * @return mixed
	 */
	public function get_bundled_item_price( $min_or_max = 'min', $display = false ) {

		if ( ! $this->exists() ) {
			return false;
		}

		$prop    = $min_or_max . '_price_product';
		$product = ! empty( $this->$prop ) ? $this->$prop : $this->product;

		$this->add_price_filters();
		$price = $product->get_price();

		if ( $this->is_sub() ) {
			$signup_fee = $product->get_sign_up_fee();
			$price      = $this->get_up_front_subscription_price( $price, $signup_fee, $product );
		}

		if ( $this->is_nyp() && 'max' === $min_or_max ) {
			$price = '';
		}

		$this->remove_price_filters();

		return $display ? WC_PB_Helpers::get_product_display_price( $product, $price ) : $price;
	}

	/**
	 * Get bundled item recurring price after discount, filters included.
	 *
	 * @param  string  $min_or_max
	 * @param  boolean $display
	 * @return mixed
	 */
	public function get_bundled_item_recurring_price( $min_or_max = 'min', $display = false ) {

		if ( ! $this->exists() ) {
			return false;
		}

		$prop    = $min_or_max . '_price_product';
		$product = ! empty( $this->$prop ) ? $this->$prop : $this->product;

		$this->add_price_filters();
		$price = $product->get_price();
		$this->remove_price_filters();

		return $display ? WC_PB_Helpers::get_product_display_price( $product, $price ) : $price;
	}

	/**
	 * Get bundled item regular price after discount, filters included.
	 *
	 * @param  string  $min_or_max
	 * @param  boolean $display
	 * @return mixed
	 */
	public function get_bundled_item_regular_price( $min_or_max = 'min', $display = false, $strict = false ) {

		if ( ! $this->exists() ) {
			return false;
		}

		$prop    = $strict ? $min_or_max . '_price_product' : $min_or_max . '_regular_price_product';
		$product = ! empty( $this->$prop ) ? $this->$prop : $this->product;

		$this->add_price_filters();
		$price = $product->get_regular_price();

		if ( $this->is_sub() ) {
			$signup_fee = $product->get_sign_up_fee();
			$price      = $this->get_up_front_subscription_price( $price, $signup_fee, $product );
		}

		if ( $this->is_nyp() && 'max' === $min_or_max ) {
			$price = '';
		}

		$this->remove_price_filters();

		return $display ? WC_PB_Helpers::get_product_display_price( $product, $price ) : $price;
	}

	/**
	 * Get bundled item recurring price after discount, filters included.
	 *
	 * @param  string  $min_or_max
	 * @param  boolean $display
	 * @return mixed
	 */
	public function get_bundled_item_regular_recurring_price( $min_or_max = 'min', $display = false ) {

		if ( ! $this->exists() ) {
			return false;
		}

		$prop    = $min_or_max . '_regular_price_product';
		$product = ! empty( $this->$prop ) ? $this->$prop : $this->product;

		$this->add_price_filters();
		$price = $product->get_regular_price();
		$this->remove_price_filters();

		return $display ? WC_PB_Helpers::get_product_display_price( $product, $price ) : $price;
	}

	/**
	 * Min bundled item price incl tax.
	 *
	 * @return double
	 */
	public function get_bundled_item_price_including_tax( $min_or_max = 'min', $qty = 1 ) {

		if ( ! $this->exists() ) {
			return false;
		}

		$prop    = $min_or_max . '_price_product';
		$product = ! empty( $this->$prop ) ? $this->$prop : $this->product;

		$this->add_price_filters();
		$price = $product->get_price();

		if ( $this->is_sub() ) {
			$signup_fee = $product->get_sign_up_fee();
			$price      = $this->get_up_front_subscription_price( $price, $signup_fee, $product );
		}

		$this->remove_price_filters();

		if ( $price && get_option( 'woocommerce_calc_taxes' ) === 'yes' && get_option( 'woocommerce_prices_include_tax' ) !== 'yes' ) {
			$price = $product->get_price_including_tax( $qty, $price );
		} else {
			$price = $price * $qty;
		}

		if ( $this->is_nyp() && 'max' === $min_or_max ) {
			$price = '';
		}

		return $price;
	}

	/**
	 * Min bundled item price excl tax.
	 *
	 * @return double
	 */
	public function get_bundled_item_price_excluding_tax( $min_or_max = 'min', $qty = 1 ) {

		if ( ! $this->exists() ) {
			return false;
		}

		$prop    = $min_or_max . '_price_product';
		$product = ! empty( $this->$prop ) ? $this->$prop : $this->product;

		$this->add_price_filters();
		$price = $product->get_price();

		if ( $this->is_sub() ) {
			$signup_fee = $product->get_sign_up_fee();
			$price      = $this->get_up_front_subscription_price( $price, $signup_fee, $product );
		}

		$this->remove_price_filters();

		if ( $price && get_option( 'woocommerce_calc_taxes' ) === 'yes' && get_option( 'woocommerce_prices_include_tax' ) === 'yes' ) {
			$price = $product->get_price_excluding_tax( $qty, $price );
		} else {
			$price = $price * $qty;
		}

		if ( $this->is_nyp() && 'max' === $min_or_max ) {
			$price = '';
		}

		return $price;
	}

	/**
	 * True if the bundled item is priced per product.
	 *
	 * @return boolean
	 */
	public function is_priced_per_product() {

		$is_ppp = false;

		if ( $this->per_product_pricing ) {
			$is_ppp = true;
		}

		return apply_filters( 'woocommerce_bundle_is_priced_per_product', $is_ppp, $this );
	}

	/**
	 * Bundled item sale status.
	 *
	 * @return  boolean  true if on sale
	 */
	public function is_on_sale() {

		$on_sale = $this->on_sale;

		if ( $this->is_out_of_stock() ) {
			return false;
		}

		return $on_sale;
	}

	/**
	 * Bundled item purchasable status.
	 *
	 * @return  boolean  true if purchasable
	 */
	public function is_purchasable() {

		return $this->purchasable;
	}

	/**
	 * Bundled item exists status.
	 *
	 * @return  boolean  true if bundled item exists
	 */
	public function exists() {

		$exists = true;

		if ( empty( $this->product ) ) {
			$exists = false;
		}

		if ( $exists && isset( $this->product->post->post_status ) && $this->product->post->post_status === 'trash' ) {
			$exists = false;
		}

		return $exists;
	}

	/**
	 * Bundled item out of stock status.
	 * Takes min quantity into account.
	 *
	 * @return  boolean  true if out of stock
	 */
	public function is_out_of_stock() {

		if ( $this->stock_status === 'out-of-stock' ) {
			return true;
		}

		return false;
	}

	/**
	 * Bundled item in stock status.
	 * Takes min quantity into account.
	 *
	 * @return  boolean  true if in stock
	 */
	public function is_in_stock() {

		if ( $this->stock_status === 'out-of-stock' ) {
			return false;
		}

		return true;
	}

	/**
	 * Bundled item backorder status.
	 *
	 * @return  boolean  true if on backorder
	 */
	public function is_on_backorder() {

		if ( $this->stock_status === 'available-on-backorder' ) {
			return true;
		}

		return false;
	}

	/**
	 * Bundled item sold individually status.
	 *
	 * @return boolean  true if sold individually
	 */
	public function is_sold_individually() {

		if ( $this->sold_individually ) {
			return true;
		}

		return false;
	}

	/**
	 * Bundled item name-your-price status.
	 *
	 * @return boolean  true if item is NYP
	 */
	public function is_nyp() {

		return $this->nyp;
	}

	/**
	 * Check if the product has variables to adjust before adding to cart.
	 * Conditions: ( is NYP ) or ( has required addons ) or ( has options )
	 *
	 * @return boolean  true if the item has variables to adjust before adding to cart
	 */
	public function requires_input() {

		if ( $this->is_nyp() || WC_PB()->compatibility->has_required_addons( $this->product_id ) || $this->product->product_type === 'variable' || $this->product->product_type === 'variable-subscription' ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the item is a subscription.
	 *
	 * @return boolean  true if the item is a sub
	 */
	public function is_sub() {

		if ( $this->product->product_type === 'subscription' || $this->product->product_type === 'variable-subscription' ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the item is a variable subscription.
	 *
	 * @return boolean  true if the item is a sub
	 */
	public function is_variable_sub() {

		if ( $this->product->product_type === 'variable-subscription' ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the variation attributes array if this product is variable.
	 *
	 * @return array
	 */
	public function get_product_variation_attributes() {

		if ( ! empty( $this->product_attributes ) ) {
			return $this->product_attributes;
		}

		if ( $this->product->product_type === 'variable' || $this->product->product_type === 'variable-subscription' ) {
			$this->product_attributes = $this->product->get_variation_attributes();
			return $this->product_attributes;
		}

		return false;
	}

	/**
	 * Returns the selected variation attribute if this product is variable.
	 *
	 * @return string
	 */
	public function get_selected_product_variation_attribute( $attribute_name ) {

		$defaults       = $this->get_selected_product_variation_attributes();
		$attribute_name = sanitize_title( $attribute_name );

		return isset( $defaults[ $attribute_name ] ) ? $defaults[ $attribute_name ] : '';
	}

	/**
	 * Returns the selected variation attributes if this product is variable.
	 *
	 * @return array
	 */
	public function get_selected_product_variation_attributes() {

		if ( ! empty( $this->selected_product_attributes ) ) {
			return $this->selected_product_attributes;
		}

		if ( $this->product->product_type === 'variable' || $this->product->product_type === 'variable-subscription' ) {

			if ( ! empty( $this->selection_overrides ) ) {
				$selected_product_attributes = $this->selection_overrides;
			} else {

				$selected_product_attributes = ( array ) maybe_unserialize( get_post_meta( $this->product_id, '_default_attributes', true ) );

				// Ensure default attribute selections correspond to attribute values that have not been filtered out.
				if ( ! empty( $selected_product_attributes ) && ! empty( $this->allowed_variations ) && ! empty( $this->product_variations ) ) {

					$variation_attribute_values = array();

					foreach ( $this->product_variations as $variation_data ) {
						if ( isset( $variation_data[ 'attributes' ] ) ) {
							foreach ( $variation_data[ 'attributes' ] as $attribute_key => $attribute_value ) {
								$variation_attribute_values[ $attribute_key ][] = sanitize_title( $attribute_value );
								if ( in_array( '', $variation_attribute_values[ $attribute_key ] ) ) {
									break;
								}
							}
						}
					}

					foreach ( $selected_product_attributes as $selected_attribute_key => $selected_attribute_value ) {
						if ( $selected_attribute_value !== '' && isset( $variation_attribute_values [ 'attribute_' . $selected_attribute_key ] ) && ! in_array( '', $variation_attribute_values[ 'attribute_' . $selected_attribute_key ] ) && ! in_array( $selected_attribute_value, $variation_attribute_values[ 'attribute_' . $selected_attribute_key ] ) ) {
							$selected_product_attributes[ $selected_attribute_key ] = '';
						}
					}
				}
			}

			$this->selected_product_attributes = apply_filters( 'woocommerce_product_default_attributes', $selected_product_attributes, $this->product );

			return $this->selected_product_attributes;
		}

		return false;
	}

	/**
	 * Returns this product's available variations array.
	 *
	 * @return array
	 */
	public function get_product_variations() {

		if ( ! empty( $this->product_variations ) ) {
			return $this->product_variations;
		}

		if ( $this->product->product_type === 'variable' || $this->product->product_type === 'variable-subscription' ) {

			do_action( 'woocommerce_before_init_bundled_item', $this );

			// Filter children to exclude filtered out variations.
			add_filter( 'woocommerce_get_children', array( $this, 'bundled_item_children' ), 10, 2 );

			// Filter variations data.
			add_filter( 'woocommerce_available_variation', array( $this, 'bundled_item_available_variation' ), 10, 3 );

			$this->add_price_filters();

			if ( $this->product->product_type === 'variable-subscription' ) {
				WC_PB_Helpers::$bundled_item = $this;
			}

			$bundled_item_variations = $this->product->get_available_variations();

			if ( $this->product->product_type === 'variable-subscription' ) {
				WC_PB_Helpers::$bundled_item = false;
			}

			$this->remove_price_filters();

			remove_filter( 'woocommerce_available_variation', array( $this, 'bundled_item_available_variation' ), 10, 3 );

			remove_filter( 'woocommerce_get_children', array( $this, 'bundled_item_children' ), 10, 2 );

			do_action( 'woocommerce_after_init_bundled_item', $this );

			// Add only active variations.
			foreach ( $bundled_item_variations as $variation_data ) {
				if ( ! empty( $variation_data ) ) {
					$this->product_variations[] = $variation_data;
				}
			}

			return $this->product_variations;
		}

		return false;
	}

	/**
	 * Filter variable product children to exclude filtered out variations and improve performance of 'WC_Product_Variable::get_available_variations'
	 *
	 * @param  array                $children         ids of variations to load
	 * @param  WC_Product_Variable  $bundled_product  variable bundled product
	 * @return array                                  modified ids of variations to load
	 */
	public function bundled_item_children( $children, $bundled_product ) {

		if ( empty( $this->allowed_variations ) || ! is_array( $this->allowed_variations ) ) {
			return $children;
		} else {
			$filtered_children = array();

			foreach ( $children as $variation_id ) {
				// Remove if filtered.
				if ( in_array( $variation_id, $this->allowed_variations ) ) {
					$filtered_children[] = $variation_id;
				}
			}

			return $filtered_children;
		}
	}

	/**
	 * Modifies the results of get_available_variations() to implement variation filtering and bundle discounts for variable products.
	 * Also calculates variation prices incl. or excl. tax.
	 *
	 * @param  array                  $variation_data     unmodified variation data
	 * @param  WC_Product             $bundled_product    the bundled product
	 * @param  WC_Product_Variation   $bundled_variation  the variation in question
	 * @return array                                      modified variation data
	 */
	public function bundled_item_available_variation( $variation_data, $bundled_product, $bundled_variation ) {

		$bundled_item_id = $this->item_id;

		// Disable if certain conditions are met...
		if ( ! empty( $this->allowed_variations ) ) {

			if ( ! is_array( $this->allowed_variations ) ) {
				return array();
			}

			if ( ! in_array( $bundled_variation->variation_id, $this->allowed_variations ) ) {
				return array();
			}
		}

		if ( $bundled_variation->price === '' ) {
			return array();
		}

		// Add price data.
		WC_PB_Helpers::extend_price_display_precision();
		$price_incl_tax                                  = $bundled_variation->get_price_including_tax( 1, 1000 );
		$price_excl_tax                                  = $bundled_variation->get_price_excluding_tax( 1, 1000 );
		WC_PB_Helpers::reset_price_display_precision();

		$variation_data[ 'price' ]                       = $bundled_variation->get_price();
		$variation_data[ 'regular_price' ]               = $bundled_variation->get_regular_price();

		$variation_data[ 'price_tax' ]                   = $price_incl_tax / $price_excl_tax;

		$variation_data[ 'regular_recurring_price' ]     = '';
		$variation_data[ 'recurring_price' ]             = '';

		$variation_data[ 'recurring_html' ]              = '';
		$variation_data[ 'recurring_key' ]               = '';

		if ( $bundled_product->product_type === 'variable-subscription' ) {

			$variation_data[ 'regular_recurring_price' ]     = $variation_data[ 'regular_price' ];
			$variation_data[ 'recurring_price' ]             = $variation_data[ 'price' ];

			$signup_fee                                      = $bundled_variation->get_sign_up_fee();

			$variation_data[ 'regular_price' ]               = $this->get_up_front_subscription_price( $variation_data[ 'regular_price' ], $signup_fee, $bundled_variation );
			$variation_data[ 'price' ]                       = $this->get_up_front_subscription_price( $variation_data[ 'price' ], $signup_fee, $bundled_variation );

			$variation_data[ 'recurring_html' ]              = WC_PB_Helpers::get_recurring_price_html_component( $bundled_variation );
			$variation_data[ 'recurring_key' ]               = str_replace( '_synced', '', WC_Subscriptions_Cart::get_recurring_cart_key( array( 'data' => $bundled_variation ), ' ' ) );
		}

		$variation_price_html = '';

		if ( $this->is_priced_per_product() ) {
			if ( $variation_data[ 'price_html' ] === '' ) {

				if ( $bundled_product->get_variation_price( 'min' ) === $bundled_product->get_variation_price( 'max' ) ) {
					$cache_key                   = 'variation_price_html_' . $this->bundle_id . '_' . $this->item_id;
					$cached_variation_price_html = WC_PB_Helpers::cache_get( $cache_key );
					if ( null === $cached_variation_price_html ) {
						$cached_variation_price_html = '<p class="price">' . $bundled_variation->get_price_html() . '</p>';
						WC_PB_Helpers::cache_set( $cache_key, $cached_variation_price_html );
					}
					$variation_price_html = $cached_variation_price_html;
				} else {
					$variation_price_html = '<p class="price">' . $bundled_variation->get_price_html() . '</p>';
				}
			} else {
				$variation_price_html = $variation_data[ 'price_html' ];
			}
		}

		$variation_data[ 'price_html' ] = $variation_price_html;

		// Modify availability data.
		$quantity     = $this->get_quantity();
		$quantity_max = $this->get_quantity( 'max', true, $bundled_variation );
		$availability = $this->get_availability( $bundled_variation );

		if ( ! $this->is_in_stock() || ! $bundled_variation->is_in_stock() || ! $bundled_variation->has_enough_stock( $quantity ) ) {
			$variation_data[ 'is_in_stock' ] = false;
		}

		if ( $bundled_variation->is_on_backorder() && $bundled_product->backorders_require_notification() ) {
			$variation_data[ 'is_on_backorder' ] = 'available-on-backorder';
		}

		$availability_html = empty( $availability[ 'availability' ] ) ? '' : '<p class="stock ' . esc_attr( $availability[ 'class' ] ) . '">' . wp_kses_post( $availability[ 'availability' ] ) . '</p>';

		$variation_data[ 'availability_html' ] = apply_filters( 'woocommerce_stock_html', $availability_html, $availability[ 'availability' ], $bundled_variation );
		$variation_data[ 'min_qty' ]           = $quantity;
		$variation_data[ 'max_qty' ]           = $quantity_max;

		if ( $variation_data[ 'min_qty' ] !== $variation_data[ 'max_qty' ] ) {
			$variation_data[ 'is_sold_individually' ] = false;
		}

		return $variation_data;
	}

	/**
	 * Add price filters to modify child product prices depending on the per-product pricing option state, including any discounts defined at bundled item level.
	 * Applied i) when displaying single-product form content, ii) when initializing Product Bundles and iii) when calculating cart prices.
	 *
	 * @return  void
	 */
	public function add_price_filters() {
		WC_PB_Helpers::add_price_filters( $this );
	}

	/**
	 * Remove price filters after modifying child product prices depending on the per-product pricing option state, including any discounts defined at bundled item level.
	 *
	 * @return  void
	 */
	public function remove_price_filters() {
		WC_PB_Helpers::remove_price_filters();
	}

	/**
	 * True if there is a title override.
	 *
	 * @return boolean
	 */
	public function has_title_override() {
		if ( ! empty( $this->item_data[ 'override_title' ] ) && $this->item_data[ 'override_title' ] === 'yes' ) {
			return true;
		}

		return false;
	}

	/**
	 * Item title.
	 *
	 * @return string item title
	 */
	public function get_title() {
		return apply_filters( 'woocommerce_bundled_item_title', $this->title, $this );
	}

	/**
	 * Item raw item title.
	 *
	 * @return string item title
	 */
	public function get_raw_title() {

		$title = $this->get_title();

		if ( $title === '' ) {
			$title = $this->product->get_title();
		}

		return apply_filters( 'woocommerce_bundled_item_raw_title', $title, $this );
	}

	/**
	 * Item title.
	 *
	 * @return string item title
	 */
	public function get_description() {
		return apply_filters( 'woocommerce_bundled_item_description', wpautop( do_shortcode( wp_kses_post( $this->description ) ) ), $this );
	}

	/**
	 * Visible or hidden in the product/cart/order templates.
	 *
	 * @return boolean true if visible
	 */
	public function is_visible( $where = 'product' ) {
		return isset( $this->visibility[ $where ] ) && $this->visibility[ $where ] === 'visible';
	}

	/**
	 * Item hidden from all templates.
	 *
	 * @return boolean true if secret
	 */
	public function is_secret() {
		return $this->visibility[ 'product' ] === 'hidden' && $this->visibility[ 'cart' ] === 'hidden' && $this->visibility[ 'order' ] === 'hidden';
	}

	/**
	 * Optional item.
	 *
	 * @return boolean true if optional
	 */
	public function is_optional() {
		return $this->optional === 'yes' ? true : false;
	}

	/**
	 * Item min/max quantity.
	 *
	 * @return int
	 */
	public function get_quantity( $min_or_max = 'min', $bound_by_stock = false, $product = false ) {

		$qty_min = $this->quantity;
		$qty_min = ( $qty_min > 1 && $this->is_sold_individually() ) ? 1 : $qty_min;
		$qty_min = apply_filters( 'woocommerce_bundled_item_quantity', $qty_min, $this );
		$qty     = $qty_min;

		if ( $min_or_max === 'max' ) {

			$qty_max = $qty_min;

			if ( ! $product ) {
				$product = $this->product;
			}

			if ( isset( $this->item_data[ 'bundle_quantity_max' ] ) ) {
				if ( $this->item_data[ 'bundle_quantity_max' ] !== '' ) {
					$qty_max = max( $this->item_data[ 'bundle_quantity_max' ], $qty_min );
				} else {
					$qty_max = '';
				}
			}

			$qty_max = $this->is_sold_individually() ? 1 : $qty_max;

			// Variations min/max quantity attributes handled via JS.
			if ( $bound_by_stock && ! in_array( $product->product_type, array( 'variable', 'variable-subscription' ) ) ) {

				$qty_max_bound = '';

				if ( $product->managing_stock() && ! $product->backorders_allowed() ) {
					$qty_max_bound = ! empty( $product->variation_id ) ? $product->get_total_stock() : $this->total_stock;
				}

				// Max product quantity can't be greater than the bundled Max Quantity setting.
				if ( $qty_max > 0 ) {
					$qty_max_bound = ( $qty_max_bound !== '' ) ? min( $qty_max, $qty_max_bound ) : $qty_max;
				}

				// Max product quantity can't be lower than the min product quantity - if it is, then the product is not in stock.
				if ( $qty_max_bound !== '' ) {
					if ( $qty_min > $qty_max_bound ) {
						$qty_max_bound = $qty_min;
					}
				}

				$qty_max = $qty_max_bound;
			}

			$qty = apply_filters( 'woocommerce_bundled_item_quantity_max', $qty_max, $this );
		}

		return $qty;
	}

	/**
	 * Item discount.
	 *
	 * @return int
	 */
	public function get_discount() {
		return apply_filters( 'woocommerce_bundled_item_discount', $this->discount, $this );
	}

	/**
	 * Item sign-up discount.
	 *
	 * @return int
	 */
	public function get_sign_up_discount() {
		return apply_filters( 'woocommerce_bundled_item_sign_up_discount', $this->sign_up_discount, $this );
	}

	/**
	 * Checkbox state for optional bundled items.
	 *
	 * @return boolean
	 */
	public function is_optional_checked() {

		if ( ! $this->is_optional() ) {
			return false;
		}

		if ( isset( $_REQUEST[ apply_filters( 'woocommerce_product_bundle_field_prefix', '', $this->product_id ) . 'bundle_selected_optional_' . $this->item_id ] ) ) {
			$checked = true;
		} else {
			$checked = false;
		}

		return apply_filters( 'woocommerce_bundled_item_is_optional_checked', $checked, $this );
	}

	/**
	 * Visible or hidden item thumbnail.
	 *
	 * @return boolean true if visible
	 */
	public function is_thumbnail_visible() {
		return $this->hide_thumbnail === 'yes' ? false : true;
	}

	/**
	 * Get classes for template use.
	 *
	 * @return string
	 */
	public function get_classes() {

		$classes = array();

		if ( $this->get_quantity( 'min' ) !== $this->get_quantity( 'max' ) && ! $this->is_out_of_stock() ) {
			$classes[] = 'has_qty_input';
		}

		if ( ! $this->is_thumbnail_visible() ) {
			$classes[] = 'thumbnail_hidden';
		}

		if ( ! $this->is_visible() ) {
			$classes[] = 'bundled_item_hidden';
		}

		return implode( ' ', apply_filters( 'woocommerce_bundled_item_classes', $classes, $this ) );
	}

	/**
	 * Bundled product availability that takes min_quantity > 1 into account.
	 *
	 * @return array
	 */
	public function get_availability( $product = false ) {

		if ( ! $product ) {
			$product = $this->product;
		}

		$quantity     = $this->get_quantity();
		$total_stock  = ! empty( $product->variation_id ) ? $product->get_total_stock() : $this->total_stock;
		$availability = $class = '';

		if ( $product->managing_stock() ) {

			if ( $product->is_in_stock() && $total_stock > get_option( 'woocommerce_notify_no_stock_amount' ) && $total_stock >= $quantity ) {

				switch ( get_option( 'woocommerce_stock_format' ) ) {

					case 'no_amount' :
						$availability = __( 'In stock', 'woocommerce' );
					break;

					case 'low_amount' :
						if ( $total_stock <= get_option( 'woocommerce_notify_low_stock_amount' ) ) {
							$availability = sprintf( __( 'Only %s left in stock', 'woocommerce' ), $total_stock );

							if ( $product->backorders_allowed() && $product->backorders_require_notification() ) {
								$availability .= ' ' . __( '(can be backordered)', 'woocommerce' );
							}
						} else {
							$availability = __( 'In stock', 'woocommerce' );
						}
					break;

					default :
						$availability = sprintf( __( '%s in stock', 'woocommerce' ), $total_stock );

						if ( $product->backorders_allowed() && $product->backorders_require_notification() ) {
							$availability .= ' ' . __( '(can be backordered)', 'woocommerce' );
						}
					break;
				}

				$class = 'in-stock';

			} elseif ( $product->backorders_allowed() && $product->backorders_require_notification() ) {

				if ( $total_stock >= $quantity || get_option( 'woocommerce_stock_format' ) === 'no_amount' || $total_stock <= 0 ) {
					$availability = __( 'Available on backorder', 'woocommerce' );
				} else {
					$availability = __( 'Available on backorder', 'woocommerce' ) . ' ' . sprintf( __( '(only %s left in stock)', 'woocommerce-product-bundles' ), $total_stock );
				}

				$class = 'available-on-backorder';

			} elseif ( $product->backorders_allowed() ) {

				$availability = __( 'In stock', 'woocommerce' );
				$class        = 'in-stock';

			} else {

				if ( $product->is_in_stock() && $total_stock > get_option( 'woocommerce_notify_no_stock_amount' ) ) {

					if ( get_option( 'woocommerce_stock_format' ) === 'no_amount' ) {
						$availability = __( 'Insufficient stock', 'woocommerce-product-bundles' );
					} else {
						$availability = __( 'Insufficient stock', 'woocommerce-product-bundles' ) . ' ' . sprintf( __( '(only %s left in stock)', 'woocommerce-product-bundles' ), $total_stock );
					}

					$class = 'out-of-stock';

				} else {

					$availability = __( 'Out of stock', 'woocommerce' );
					$class        = 'out-of-stock';
				}
			}

		} elseif ( ! $product->is_in_stock() ) {

			$availability = __( 'Out of stock', 'woocommerce' );
			$class        = 'out-of-stock';
		}

		return apply_filters( 'woocommerce_bundled_item_availability', array( 'availability' => $availability, 'class' => $class ), $this );
	}

	/**
	 * Get (synced) subscription up-front price.
	 *
	 * @since  4.14.6
	 *
	 * @param  double     $sign_up_fee
	 * @param  double     $recurring_price
	 * @param  WC_Product $product
	 * @return double
	 */
	public function get_up_front_subscription_price( $recurring_price, $sign_up_fee, $product = false ) {

		if ( ! $product ) {
			$product = $this->product;
		}

		$price = $sign_up_fee;

		if ( WC_PB()->compatibility->is_subscription( $product ) ) {

			if ( 0 == WC_Subscriptions_Product::get_trial_length( $product ) ) {

				if ( WC_Subscriptions_Synchroniser::is_product_synced( $product ) ) {

					$next_payment_date = WC_Subscriptions_Synchroniser::calculate_first_payment_date( $product, 'timestamp' );

					if ( WC_Subscriptions_Synchroniser::is_today( $next_payment_date ) ) {

						$price += $recurring_price;

					} elseif ( WC_Subscriptions_Synchroniser::is_product_prorated( $product ) ) {

						switch( $product->subscription_period ) {
							case 'week' :
								$days_in_cycle = 7 * $product->subscription_period_interval;
								break;
							case 'month' :
								$days_in_cycle = date( 't' ) * $product->subscription_period_interval;
								break;
							case 'year' :
								$days_in_cycle = ( 365 + date( 'L' ) ) * $product->subscription_period_interval;
								break;
						}

						$days_until_next_payment = ceil( ( $next_payment_date - gmdate( 'U' ) ) / ( 60 * 60 * 24 ) );
						$price                   = $sign_up_fee + $days_until_next_payment * ( $recurring_price / $days_in_cycle );
					}

				} else {
					$price += $recurring_price;
				}
			}
		}

		return round( $price, WC_PB_Core_Compatibility::wc_get_rounding_precision() );
	}

	/**
	 * @deprecated
	 */
	public function get_prorated_price_for_subscription( $recurring_price, $sign_up_fee, $product = false ) {
		_deprecated_function( __FUNCTION__ . '()', '4.14.6', __CLASS__ . '::get_up_front_subscription_price()' );
		return $this->get_up_front_subscription_price( $recurring_price, $sign_up_fee, $product );
	}
	public function has_variables() {
		_deprecated_function( 'has_variables()', '4.11.7', 'requires_input()' );
		return $this->requires_input();
	}
	public function get_sign_up_fee( $sign_up_fee, $product ) {
		_deprecated_function( 'get_sign_up_fee()', '4.14.1' );
		return $sign_up_fee;
	}
}

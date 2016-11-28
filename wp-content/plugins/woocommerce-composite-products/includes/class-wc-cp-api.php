<?php
/**
 * Globally accessible functions and filters associated with the Composite type.
 *
 * @class   WC_CP_API
 * @version 3.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_CP_API {

	/**
	 * Composited product filter parameters set by 'add_composited_product_filters'.
	 *
	 * @var array
	 */
	public $filter_params;

	/**
	 * General-purpose key/value cache.
	 *
	 * @var array
	 */
	public $cache;

	public function __construct() {
		$this->cache = $this->filter_params = array();
	}

	/**
	 * Simple cache getter.
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function cache_get( $key ) {
		$value = null;
		if ( isset( $this->cache[ $key ] ) ) {
			$value = $this->cache[ $key ];
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
	public function cache_set( $key, $value ) {
		$this->cache[ $key ] = $value;
	}

	/**
	 * Sets up a WP_Query wrapper object to fetch component options. The query is configured based on the data stored in the 'component_data' array.
	 * Note that the query parameters are filterable - @see WC_CP_Query for details.
	 *
	 * @param  array  $component_data
	 * @param  array  $query_args
	 * @return array
	 */
	public function get_component_options( $component_data, $query_args = array() ) {

		$query = new WC_CP_Query( $component_data, $query_args );

		return $query->get_component_options();
	}

	/**
	 * Get composite layout options.
	 *
	 * @return array
	 */
	public function get_layout_options() {

		$sanitized_custom_layouts = array();

		$base_layouts = array(
			'single'              => __( 'Stacked', 'woocommerce-composite-products' ),
			'progressive'         => __( 'Progressive', 'woocommerce-composite-products' ),
			'paged'               => __( 'Stepped', 'woocommerce-composite-products' ),
		);

		$custom_layouts = array(
			'paged-componentized' => __( 'Componentized', 'woocommerce-composite-products' ),
		);

		/**
		 * Filter layout variations array to add custom layout variations.
		 *
		 * @param array $custom_layouts
		 */
		$custom_layouts = apply_filters( 'woocommerce_composite_product_layout_variations', $custom_layouts );

		foreach ( $custom_layouts as $layout_id => $layout_description ) {

			$sanitized_layout_id = esc_attr( sanitize_title( $layout_id ) );

			if ( array_key_exists( $sanitized_layout_id, $base_layouts ) ) {
				continue;
			}

			$sanitized_layout_id_parts = explode( '-', $sanitized_layout_id, 2 );

			if ( ! empty( $sanitized_layout_id_parts[0] ) && array_key_exists( $sanitized_layout_id_parts[0], $base_layouts ) ) {
				$sanitized_custom_layouts[ $sanitized_layout_id ] = $layout_description;
			}
		}

		return array_merge( $base_layouts, $sanitized_custom_layouts );
	}

	/**
	 * Get composite selection styles.
	 *
	 * @return array
	 */
	public function get_options_styles() {

		$styles = array(
			array(
				'id'          => 'dropdowns',
				'description' => __( 'Dropdown', 'woocommerce-composite-products' ),
				'supports'    => array()
			),
			array(
				'id'          => 'thumbnails',
				'description' => __( 'Thumbnails', 'woocommerce-composite-products' ),
				'supports'    => array( 'pagination' )
			),
			array(
				'id'          => 'radios',
				'description' => __( 'Radio Buttons', 'woocommerce-composite-products' ),
				'supports'    => array()
			)
		);

		/**
		 * Filter the selection styles array to add custom styles.
		 *
		 * @param  array  $styles
		 */
		return apply_filters( 'woocommerce_composite_product_options_styles', $styles );
	}

	/**
	 * Get composite selection style data.
	 *
	 * @param  string  $style_id
	 * @return array|false
	 */
	public function get_options_style( $style_id ) {

		$styles = $this->get_options_styles();
		$found  = false;

		foreach ( $styles as $style ) {
			if ( $style[ 'id' ] ===  $style_id ) {
				$found = $style;
				break;
			}
		}

		return $found;
	}

	/**
	 * True if a selection style supports a given functionality.
	 *
	 * @param  string $style_id
	 * @param  string $what
	 * @return bool
	 */
	public function options_style_supports( $style_id, $what ) {

		$options_style_data = $this->get_options_style( $style_id );
		$supports           = false;

		if ( $options_style_data && isset( $options_style_data[ 'supports' ] ) && is_array( $options_style_data[ 'supports' ] ) && in_array( $what, $options_style_data[ 'supports' ] ) ) {
			$supports = true;
		}

		return $supports;
	}

	/**
	 * Get composite layout tooltips.
	 *
	 * @param  string  $layout_id
	 * @return string
	 */
	public function get_layout_tooltip( $layout_id ) {

		$tooltips = array(
			'single'              => __( 'Components are presented in a stacked layout, with the add-to-cart button located at the bottom. Component Options can be selected in any sequence.', 'woocommerce-composite-products' ),
			'progressive'         => __( 'Similar to the Stacked layout, however, Components can be toggled open/closed and must be configured in sequence. Only a single Component is visible at any time.', 'woocommerce-composite-products' ),
			'paged'               => __( 'Components are presented as individual steps of a paginated configuration process. Selections are summarized in a final Review step, at which point the Composite can be added to the cart.', 'woocommerce-composite-products' ),
			'paged-componentized' => __( 'A variation of the Stepped layout that begins with a Summary of all Components and allows them to be configured in any sequence.', 'woocommerce-composite-products' ),
		);

		if ( ! isset( $tooltips[ $layout_id ] ) ) {
			return '';
		}

		$tooltip = '<br/>' . WC_CP_Core_Compatibility::wc_help_tip( $tooltips[ $layout_id ] );

		return $tooltip;
	}

	/**
	 * Get selected layout option.
	 *
	 * @param  string $layout
	 * @return string
	 */
	public function get_selected_layout_option( $layout ) {

		if ( ! $layout ) {
			return 'single';
		}

		$layouts         = $this->get_layout_options();
		$layout_id_parts = explode( '-', $layout, 2 );

		if ( array_key_exists( $layout, $layouts ) ) {
			return $layout;
		} elseif ( array_key_exists( $layout_id_parts[0], $layouts ) ) {
			return $layout_id_parts[0];
		}

		return 'single';
	}

	/**
	 * Price-related filters. Modify composited product prices to take into account component discounts.
	 *
	 * @param  WC_Product           $product
	 * @param  string               $component_id
	 * @param  WC_Product_Composite $composite
	 * @return void
	 */
	public function apply_composited_product_filters( $product, $component_id, $composite ) {

		$component_data = $composite->get_component_data( $component_id );

		$quantity_min   = $component_data[ 'quantity_min' ];
		$quantity_max   = $component_data[ 'quantity_max' ];

		if ( $product->is_sold_individually() ) {
 			$quantity_max = 1;
 			$quantity_min = min( $quantity_min, 1 );
 		}

		$this->filter_params[ 'product' ]             = $product;
 		$this->filter_params[ 'composite' ]           = $composite;
		$this->filter_params[ 'composite_id' ]        = $composite->id;
		$this->filter_params[ 'component_id' ]        = $component_id;
		$this->filter_params[ 'discount' ]            = isset( $component_data[ 'discount' ] ) ? $component_data[ 'discount' ] : 0;
		$this->filter_params[ 'per_product_pricing' ] = $composite->is_priced_per_product();
		$this->filter_params[ 'quantity_min' ]        = $quantity_min;
		$this->filter_params[ 'quantity_max' ]        = $quantity_max;

		add_filter( 'woocommerce_available_variation', array( $this, 'filter_available_variation' ), 10, 3 );
		add_filter( 'woocommerce_get_price', array( $this, 'filter_show_product_get_price' ), 16, 2 );
		add_filter( 'woocommerce_get_regular_price', array( $this, 'filter_show_product_get_regular_price' ), 16, 2 );
		add_filter( 'woocommerce_get_sale_price', array( $this, 'filter_show_product_get_sale_price' ), 16, 2 );
		add_filter( 'woocommerce_get_price_html', array( $this, 'filter_show_product_get_price_html' ), 5, 2 );
		add_filter( 'woocommerce_get_variation_price_html', array( $this, 'filter_show_product_get_price_html' ), 5, 2 );

		add_filter( 'woocommerce_bundles_update_price_meta', array( $this, 'filter_show_product_bundles_update_price_meta' ), 10, 2 );
		add_filter( 'woocommerce_bundle_is_composited', array( $this, 'filter_bundle_is_composited' ), 10, 2 );
		add_filter( 'woocommerce_bundle_is_priced_per_product', array( $this, 'filter_bundle_is_priced_per_product' ), 10, 2 );

		add_filter( 'woocommerce_bundle_get_base_price', array( $this, 'filter_show_product_get_base_price' ), 16, 2 );
		add_filter( 'woocommerce_bundle_get_base_regular_price', array( $this, 'filter_show_product_get_base_regular_price' ), 16, 2 );

		add_filter( 'woocommerce_nyp_html', array( $this, 'filter_show_product_get_nyp_price_html' ), 15, 2 );

		/**
		 * Action 'woocommerce_composite_products_apply_product_filters'.
		 *
		 * @param  WC_Product            $product
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $composite
		 */
		do_action( 'woocommerce_composite_products_apply_product_filters', $product, $component_id, $composite );
	}

	/**
	 * Filters variation data in the show_product function.
	 *
	 * @param  mixed                    $variation_data
	 * @param  WC_Product               $bundled_product
	 * @param  WC_Product_Variation     $bundled_variation
	 * @return mixed
	 */
	public function filter_available_variation( $variation_data, $product, $variation ) {

		if ( ! empty ( $this->filter_params ) ) {

			// Add price data.
			WC_CP_Helpers::extend_price_display_precision();
			$price_incl_tax                           = $variation->get_price_including_tax( 1, 1000 );
			$price_excl_tax                           = $variation->get_price_excluding_tax( 1, 1000 );
			WC_CP_Helpers::reset_price_display_precision();

			$variation_data[ 'price' ]                = $variation->get_price();
			$variation_data[ 'regular_price' ]        = $variation->get_regular_price();

			$variation_data[ 'price_tax' ]            = $price_incl_tax / $price_excl_tax;

			$variation_data[ 'price_html' ]           = $this->filter_params[ 'per_product_pricing' ] ? ( $variation_data[ 'price_html' ] === '' ? '<span class="price">' . $variation->get_price_html() . '</span>' : $variation_data[ 'price_html' ] ) : '';

			$availability                             = $this->get_composited_item_availability( $variation, $this->filter_params[ 'quantity_min' ] );
			$availability_html                        = empty( $availability[ 'availability' ] ) ? '' : '<p class="stock ' . esc_attr( $availability[ 'class' ] ) . '">' . wp_kses_post( $availability[ 'availability' ] ) . '</p>';

			$variation_data[ 'availability_html' ]    = apply_filters( 'woocommerce_stock_html', $availability_html, $availability[ 'availability' ], $variation );
			$variation_data[ 'is_sold_individually' ] = $variation_data[ 'is_sold_individually' ] && $this->filter_params[ 'quantity_min' ] == 1 ? true : false;

			$variation_data[ 'min_qty' ]              = $this->filter_params[ 'quantity_min' ];
			$variation_data[ 'max_qty' ]              = $variation_data[ 'max_qty' ] === null ? '' : $variation_data[ 'max_qty' ];

			// Max variation quantity can't be greater than the component Max Quantity.
			if ( $this->filter_params[ 'quantity_max' ] > 0 ) {
				$variation_data[ 'max_qty' ] = ( $variation_data[ 'max_qty' ] !== '' ) ? min( $this->filter_params[ 'quantity_max' ], $variation_data[ 'max_qty' ] ) : $this->filter_params[ 'quantity_max' ];
			}

			// Max variation quantity can't be lower than the min variation quantity - if it is, then the variation is not in stock.
			if ( $variation_data[ 'max_qty' ] !== '' ) {
				if ( $this->filter_params[ 'quantity_min' ] > $variation_data[ 'max_qty' ] ) {
					$variation_data[ 'is_in_stock' ] = false;
					$variation_data[ 'max_qty' ]     = $this->filter_params[ 'quantity_min' ];
				}
			}
		}

		return $variation_data;
	}

	/**
	 * Components discounts should not trigger bundle price updates.
	 *
	 * @param  boolean            $is
	 * @param  WC_Product_Bundle  $bundle
	 * @return boolean
	 */
	public function filter_show_product_bundles_update_price_meta( $update, $bundle ) {
		return false;
	}

	/**
	 * Filter 'woocommerce_bundle_is_composited'.
	 *
	 * @param  boolean            $is
	 * @param  WC_Product_Bundle  $bundle
	 * @return boolean
	 */
	public function filter_bundle_is_composited( $is, $bundle ) {
		return true;
	}

	/**
	 * Filter 'woocommerce_bundle_is_priced_per_product'. If a composite is not priced per product, this should force composited bundles to revert to static pricing, too, to force bundled items to return a zero price.
	 *
	 * @param  boolean            $is
	 * @param  WC_Product_Bundle  $bundle
	 * @return boolean
	 */
	public function filter_bundle_is_priced_per_product( $is_ppp, $bundle ) {

		if ( ! empty ( $this->filter_params ) ) {

			if ( ! $this->filter_params[ 'per_product_pricing' ] ) {
				return false;
			}
		}

		return $is_ppp;
	}

	/**
	 * Filters get_price_html to include component discounts.
	 *
	 * @param  string     $price_html
	 * @param  WC_Product $product
	 * @return string
	 */
	public function filter_show_product_get_price_html( $price_html, $product ) {

		if ( ! empty ( $this->filter_params ) ) {

			// Tells NYP to back off.
			$product->is_filtered_price_html = 'yes';

			if ( ! $this->filter_params[ 'per_product_pricing' ] ) {

				$price_html = '';

			} else {

				$add_suffix = true;

				// Don't add /pc suffix to products in composited bundles (possibly duplicate).
				if ( isset( $this->filter_params[ 'product' ] ) ) {
					$filtered_product = $this->filter_params[ 'product' ];
					if ( $filtered_product->id != $product->id ) {
						$add_suffix = false;
					}
				}

				if ( $add_suffix ) {
					$suffix     = $this->filter_params[ 'quantity_min' ] > 1 && ! $product->is_sold_individually() ? ' ' . __( '/ pc.', 'woocommerce-composite-products' ) : '';
					$price_html = $price_html . $suffix;
				}
			}

			$price_html = apply_filters( 'woocommerce_composited_item_price_html', $price_html, $product, $this->filter_params[ 'component_id' ], $this->filter_params[ 'composite_id' ] );
		}

		return $price_html;
	}

	/**
	 * Filters get_price_html to hide nyp prices in static pricing mode.
	 *
	 * @param  string     $price_html
	 * @param  WC_Product $product
	 * @return string
	 */
	public function filter_show_product_get_nyp_price_html( $price_html, $product ) {

		if ( ! empty ( $this->filter_params ) ) {

			if ( ! $this->filter_params[ 'per_product_pricing' ] ) {
				$price_html = '';
			}
		}

		return $price_html;
	}

	/**
	 * Filters get_price to include component discounts.
	 *
	 * @param  double     $price
	 * @param  WC_Product $product
	 * @return string
	 */
	public function filter_show_product_get_price( $price, $product ) {

		if ( ! empty ( $this->filter_params ) ) {

			if ( $price === '' ) {
				return $price;
			}

			if ( ! $this->filter_params[ 'per_product_pricing' ] ) {
				return ( double ) 0;
			}

			if ( apply_filters( 'woocommerce_composited_product_discount_from_regular', true, $this->filter_params[ 'component_id' ], $this->filter_params[ 'composite_id' ] ) ) {
				$regular_price = $product->get_regular_price();
			} else {
				$regular_price = $price;
			}

			if ( ! empty( $this->filter_params[ 'discount' ] ) ) {
				$discount = $this->filter_params[ 'discount' ];
				$price    = empty( $regular_price ) ? $regular_price : round( ( double ) $regular_price * ( 100 - $discount ) / 100, wc_cp_price_num_decimals() );
			}
		}

		return $price;
	}

	/**
	 * Filters get_base_price to include component discounts.
	 *
	 * @param  double     $price
	 * @param  WC_Product $product
	 * @return string
	 */
	public function filter_show_product_get_base_price( $price, $product ) {

		if ( ! empty ( $this->filter_params ) ) {

			if ( $price === '' ) {
				return $price;
			}

			if ( ! $this->filter_params[ 'per_product_pricing' ] ) {
				return ( double ) 0;
			}

			if ( apply_filters( 'woocommerce_composited_product_discount_from_regular', true, $this->filter_params[ 'component_id' ], $this->filter_params[ 'composite_id' ] ) ) {
				$regular_price = $product->get_base_regular_price();
			} else {
				$regular_price = $price;
			}

			if ( ! empty( $this->filter_params[ 'discount' ] ) ) {
				$discount = $this->filter_params[ 'discount' ];
				$price    = empty( $regular_price ) ? $regular_price : round( ( double ) $regular_price * ( 100 - $discount ) / 100, wc_cp_price_num_decimals() );
			}
		}

		return $price;
	}

	/**
	 * Filters get_regular_price to include component discounts.
	 *
	 * @param  double     $price
	 * @param  WC_Product $product
	 * @return string
	 */
	public function filter_show_product_get_regular_price( $price, $product ) {

		if ( ! empty ( $this->filter_params ) ) {

			if ( ! $this->filter_params[ 'per_product_pricing' ] ) {
				return ( double ) 0;
			}

			if ( empty( $product->regular_price ) ) {
				$price = $product->price;
			}
		}

		return $price;
	}

	/**
	 * Filters get_base_price to include component discounts.
	 *
	 * @param  double     $price
	 * @param  WC_Product $product
	 * @return string
	 */
	public function filter_show_product_get_base_regular_price( $price, $product ) {

		if ( ! empty ( $this->filter_params ) ) {

			if ( ! $this->filter_params[ 'per_product_pricing' ] ) {
				return ( double ) 0;
			}

			if ( empty( $product->base_regular_price ) ) {
				$price = $product->base_price;
			}
		}

		return $price;
	}

	/**
	 * Filters get_sale_price to include component discounts.
	 *
	 * @param  double     $price
	 * @param  WC_Product $product
	 * @return string
	 */
	public function filter_show_product_get_sale_price( $price, $product ) {

		if ( ! empty ( $this->filter_params ) ) {

			if ( ! $this->filter_params[ 'per_product_pricing' ] ) {
				return ( double ) 0;
			}

			if ( ! empty( $this->filter_params[ 'discount' ] ) ) {
				$price = $this->filter_show_product_get_price( $product->price, $product );
			}
		}

		return $price;
	}

	/**
	 * Remove price filters. @see add_composited_product_filters.
	 *
	 * @return void
	 */
	public function remove_composited_product_filters() {

		/**
		 * Action 'woocommerce_composite_products_remove_product_filters'.
		 *
		 * @param  array  $filter_params
		 */
		do_action( 'woocommerce_composite_products_remove_product_filters', $this->filter_params );

		$this->filter_params = array();

		remove_filter( 'woocommerce_available_variation', array( $this, 'filter_available_variation' ), 10, 3 );
		remove_filter( 'woocommerce_get_price', array( $this, 'filter_show_product_get_price' ), 16, 2 );
		remove_filter( 'woocommerce_get_regular_price', array( $this, 'filter_show_product_get_regular_price' ), 16, 2 );
		remove_filter( 'woocommerce_get_sale_price', array( $this, 'filter_show_product_get_sale_price' ), 16, 2 );
		remove_filter( 'woocommerce_get_price_html', array( $this, 'filter_show_product_get_price_html' ), 5, 2 );
		remove_filter( 'woocommerce_get_variation_price_html', array( $this, 'filter_show_product_get_price_html' ), 5, 2 );

		remove_filter( 'woocommerce_nyp_html', array( $this, 'filter_show_product_get_nyp_price_html' ), 15, 2 );

		remove_filter( 'woocommerce_bundle_is_priced_per_product', array( $this, 'filter_bundle_is_priced_per_product' ), 10, 2 );
		remove_filter( 'woocommerce_bundle_is_composited', array( $this, 'filter_bundle_is_composited' ), 10, 2 );
		remove_filter( 'woocommerce_bundles_update_price_meta', array( $this, 'filter_show_product_bundles_update_price_meta' ), 10, 2 );

		remove_filter( 'woocommerce_bundle_get_base_price', array( $this, 'filter_show_product_get_base_price' ), 16, 2 );
		remove_filter( 'woocommerce_bundle_get_base_regular_price', array( $this, 'filter_show_product_get_base_regular_price' ), 16, 2 );
	}

	/**
	 * Get the shop price of a product incl or excl tax, depending on the 'woocommerce_tax_display_shop' setting.
	 *
	 * @param  WC_Product $product
	 * @param  double $price
	 * @return double
	 */
	public function get_composited_product_price( $product, $price = '' ) {

		if ( ! $price ) {
			return $price;
		}

		if ( wc_cp_tax_display_shop() === 'excl' ) {
			$product_price = $product->get_price_excluding_tax( 1, $price );
		} else {
			$product_price = $product->get_price_including_tax( 1, $price );
		}

		return $product_price;
	}

	/**
	 * Used throughout the extension instead of 'wc_price'.
	 *
	 * @param  double $price
	 * @return string
	 */
	public function get_composited_item_price_string_price( $price, $args = array() ) {

		$return          = '';
		$num_decimals    = wc_cp_price_num_decimals();
		$currency        = isset( $args['currency'] ) ? $args['currency'] : '';
		$currency_symbol = get_woocommerce_currency_symbol( $currency );
		$decimal_sep     = wc_cp_price_decimal_sep();
		$thousands_sep   = wc_cp_price_thousand_sep();

		$price = apply_filters( 'raw_woocommerce_price', floatval( $price ) );
		$price = apply_filters( 'formatted_woocommerce_price', number_format( $price, $num_decimals, $decimal_sep, $thousands_sep ), $price, $num_decimals, $decimal_sep, $thousands_sep );

		if ( apply_filters( 'woocommerce_price_trim_zeros', false ) && $num_decimals > 0 ) {
			$price = wc_trim_zeros( $price );
		}

		$return = sprintf( get_woocommerce_price_format(), $currency_symbol, $price );

		return $return;
	}

	/**
	 * Composited product availability function that takes into account min quantity.
	 *
	 * @param  WC_Product $product
	 * @param  int $quantity
	 * @return array
	 */
	public function get_composited_item_availability( $product, $quantity ) {

		$availability = $class = '';

		if ( $product->managing_stock() ) {

			if ( $product->is_in_stock() && $product->get_total_stock() > get_option( 'woocommerce_notify_no_stock_amount' ) && $product->get_total_stock() >= $quantity ) {

				switch ( get_option( 'woocommerce_stock_format' ) ) {

					case 'no_amount' :
						$availability = __( 'In stock', 'woocommerce' );
					break;

					case 'low_amount' :
						if ( $product->get_total_stock() <= get_option( 'woocommerce_notify_low_stock_amount' ) ) {
							$availability = sprintf( __( 'Only %s left in stock', 'woocommerce' ), $product->get_total_stock() );

							if ( $product->backorders_allowed() && $product->backorders_require_notification() ) {
								$availability .= ' ' . __( '(can be backordered)', 'woocommerce' );
							}
						} else {
							$availability = __( 'In stock', 'woocommerce' );
						}
					break;

					default :
						$availability = sprintf( __( '%s in stock', 'woocommerce' ), $product->get_total_stock() );

						if ( $product->backorders_allowed() && $product->backorders_require_notification() ) {
							$availability .= ' ' . __( '(can be backordered)', 'woocommerce' );
						}
					break;
				}

				$class        = 'in-stock';

			} elseif ( $product->backorders_allowed() && $product->backorders_require_notification() ) {

				if ( $product->get_total_stock() >= $quantity || get_option( 'woocommerce_stock_format' ) == 'no_amount' || $product->get_total_stock() <= 0 ) {
					$availability = __( 'Available on backorder', 'woocommerce' );
				} else {
					$availability = __( 'Available on backorder', 'woocommerce' ) . ' ' . sprintf( __( '(only %s left in stock)', 'woocommerce-composite-products' ), $product->get_total_stock() );
				}

				$class        = 'available-on-backorder';

			} elseif ( $product->backorders_allowed() ) {

				$availability = __( 'In stock', 'woocommerce' );
				$class        = 'in-stock';

			} else {

				if ( $product->is_in_stock() && $product->get_total_stock() > get_option( 'woocommerce_notify_no_stock_amount' ) ) {

					if ( get_option( 'woocommerce_stock_format' ) == 'no_amount' ) {
						$availability = __( 'Insufficient stock', 'woocommerce-composite-products' );
					} else {
						$availability = __( 'Insufficient stock', 'woocommerce-composite-products' ) . ' ' . sprintf( __( '(only %s left in stock)', 'woocommerce-composite-products' ), $product->get_total_stock() );
					}

					$class        = 'out-of-stock';

				} else {

					$availability = __( 'Out of stock', 'woocommerce' );
					$class        = 'out-of-stock';
				}
			}

		} elseif ( ! $product->is_in_stock() ) {

			$availability = __( 'Out of stock', 'woocommerce' );
			$class        = 'out-of-stock';
		}

		return apply_filters( 'woocommerce_composited_product_availability', array( 'availability' => $availability, 'class' => $class ), $product );
	}

	/**
	 * @deprecated 3.5.0
	 */
	public function get_product_variations( $item_id ) {
		_deprecated_function( 'WC_CP_API::get_product_variations()', '3.5.0', 'WC_CP_Helpers::get_product_variations()' );
		return WC_CP_Helpers::get_product_variations( $item_id );
	}

	/**
	 * @deprecated 3.5.0
	 */
	public function get_product_variation_descriptions( $item_id ) {
		_deprecated_function( 'WC_CP_API::get_product_variation_descriptions()', '3.5.0', 'WC_CP_Helpers::get_product_variation_descriptions()' );
		return WC_CP_Helpers::get_product_variation_descriptions( $item_id );
	}

	/**
	 * @deprecated 3.5.0
	 */
	public function get_product_variation_title( $variation_id ) {
		_deprecated_function( 'WC_CP_API::get_product_variation_title()', '3.5.0', 'WC_CP_Helpers::get_product_variation_title()' );
		return WC_CP_Helpers::get_product_variation_title( $variation_id );
	}

	/**
	 * @deprecated 3.5.0
	 */
	public function get_product_title( $product_id ) {
		_deprecated_function( 'WC_CP_API::get_product_title()', '3.5.0', 'WC_CP_Helpers::get_product_title()' );
		return WC_CP_Helpers::get_product_title( $product_id );
	}

	/**
	 * @deprecated 3.5.0
	 */
	public function format_product_title( $title, $identifier = '', $meta = '', $paren = false ) {
		_deprecated_function( 'WC_CP_API::format_product_title()', '3.5.0', 'WC_CP_Helpers::format_product_title()' );
		return WC_CP_Helpers::format_product_title( $title, $identifier, $meta, $paren );
	}

	/**
	 * @deprecated 3.2.0
	 */
	public function add_composited_product_filters( $args, $product = false ) {
		_deprecated_function( 'WC_CP_API::add_composited_product_filters()', '3.2.0', 'WC_CP_API::apply_composited_product_filters()' );

		$composite    = wc_get_product( $args[ 'composite_id' ] );
		$component_id = $args[ 'component_id' ];

		return $this->apply_composited_product_filters( $product, $component_id, $composite );
	}

	/**
	 * @deprecated 3.5.0
	 */
	public function filter_scenarios_by_type( $scenarios, $type, $scenario_data ) {
		_deprecated_function( 'WC_CP_API::filter_scenarios_by_type()', '3.5.0', 'WC_CP_Scenarios::filter_scenarios_by_type()' );
		return WC_CP_Scenarios::filter_scenarios_by_type( $scenarios, $type, $scenario_data );
	}

	/**
	 * @deprecated 3.5.0
	 */
	public function build_scenarios( $bto_scenario_meta, $bto_data ) {
		_deprecated_function( 'WC_CP_API::build_scenarios()', '3.5.0', 'WC_CP_Scenarios::build_scenarios()' );
		return WC_CP_Scenarios::build_scenarios( $bto_scenario_meta, $bto_data );
	}

	/**
	 * @deprecated 3.5.0
	 */
	public function get_scenarios_for_product( $scenario_meta, $group_id, $product_id, $variation_id, $product_type ) {
		_deprecated_function( 'WC_CP_API::get_scenarios_for_product()', '3.5.0', 'WC_CP_Scenarios::get_scenarios_for_product()' );
		return WC_CP_Scenarios::get_scenarios_for_product( $scenario_meta, $group_id, $product_id, $variation_id, $product_type );
	}

	/**
	 * @deprecated 3.5.0
	 */
	public function scenario_contains_product( $scenario_data, $group_id, $product_id ) {
		_deprecated_function( 'WC_CP_API::scenario_contains_product()', '3.5.0', 'WC_CP_Scenarios::scenario_contains_product()' );
		return WC_CP_Scenarios::scenario_contains_product( $scenario_data, $group_id, $product_id );
	}

	/**
	 * @deprecated 3.5.0
	 */
	public function product_active_in_scenario( $scenario_data, $group_id, $product_id, $variation_id, $product_type ) {
		_deprecated_function( 'WC_CP_API::product_active_in_scenario()', '3.5.0', 'WC_CP_Scenarios::product_active_in_scenario()' );
		return WC_CP_Scenarios::product_active_in_scenario( $scenario_data, $group_id, $product_id, $variation_id, $product_type );
	}
}

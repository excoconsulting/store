<?php
/**
 * Composite front-end filters and functions.
 *
 * @class   WC_CP_Display
 * @version 3.6.0
 * @since   2.2.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_CP_Display {

	private $enqueued_composited_table_item_js = false;

	public function __construct() {

		// Single product template functions and hooks.
		require_once( 'wc-cp-template-functions.php' );
		require_once( 'wc-cp-template-hooks.php' );

		// Front end scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );

		// Change the tr class attributes when displaying bundled items in templates.
		add_filter( 'woocommerce_cart_item_class', array( $this, 'table_item_class' ), 10, 2 );
		add_filter( 'woocommerce_order_item_class', array( $this, 'table_item_class' ), 10, 2 );

		// Add preamble info to composited products.
		add_filter( 'woocommerce_cart_item_name', array( $this, 'in_cart_component_title' ), 10, 3 );
		add_filter( 'woocommerce_checkout_cart_item_quantity', array( $this, 'cart_item_component_quantity' ), 10, 3 );

		add_filter( 'woocommerce_order_item_name', array( $this, 'order_table_component_title' ), 10, 2 );
		add_filter( 'woocommerce_order_item_quantity_html', array( $this, 'order_table_component_quantity' ), 10, 2 );

		// Filter cart item count.
		add_filter( 'woocommerce_cart_contents_count',  array( $this, 'cart_contents_count' ) );

		// Filter cart widget items.
		add_filter( 'woocommerce_before_mini_cart', array( $this, 'add_cart_widget_filters' ) );
		add_filter( 'woocommerce_after_mini_cart', array( $this, 'remove_cart_widget_filters' ) );

		// Wishlists.
		add_filter( 'woocommerce_wishlist_list_item_price', array( $this, 'wishlist_list_item_price' ), 10, 3 );
		add_action( 'woocommerce_wishlist_after_list_item_name', array( $this, 'wishlist_after_list_item_name' ), 10, 2 );

		// Indent composited items in emails.
		add_action( 'woocommerce_email_styles', array( $this, 'email_styles' ) );
	}

	/**
	 * Front end styles and scripts.
	 *
	 * @return void
	 */
	public function frontend_scripts() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$dependencies = array( 'jquery', 'jquery-blockui', 'underscore', 'backbone', 'wc-add-to-cart-variation' );

		if ( class_exists( 'WC_Bundles' ) ) {
			$dependencies[] = 'wc-add-to-cart-bundle';
		}

		if ( class_exists( 'Product_Addon_Display' ) ) {
			$dependencies[] = 'woocommerce-addons';
		}

		/**
		 * Filter to allow adding custom script dependencies here.
		 *
		 * @param  array  $dependencies
		 */
		$dependencies = apply_filters( 'woocommerce_composite_script_dependencies', $dependencies );

		wp_register_script( 'wc-add-to-cart-composite', WC_CP()->plugin_url() . '/assets/js/add-to-cart-composite' . $suffix . '.js', $dependencies, WC_CP()->version );

		wp_register_style( 'wc-composite-single-css', WC_CP()->plugin_url() . '/assets/css/wc-composite-single.css', false, WC_CP()->version, 'all' );

		wp_register_style( 'wc-composite-css', WC_CP()->plugin_url() . '/assets/css/wc-composite-styles.css', false, WC_CP()->version, 'all' );

		wp_enqueue_style( 'wc-composite-css' );

		/**
		 * Filter front-end params.
		 *
		 * @param  array  $params
		 */
		$params = apply_filters( 'woocommerce_composite_front_end_params', array(
			'small_width_threshold'                 => 450,
			'full_width_threshold'                  => 450,
			'legacy_width_threshold'                => 450,
			'i18n_qty_string'                       => _x( ' &times; %s', 'qty string', 'woocommerce-composite-products' ),
			'i18n_price_string'                     => _x( ' &ndash; %s', 'price suffix', 'woocommerce-composite-products' ),
			'i18n_title_string'                     => sprintf( _x( '%1$s%2$s%3$s', 'title quantity price', 'woocommerce-composite-products' ), '%t', '%q', '%p' ),
			'i18n_selected_product_string'          => sprintf( _x( '%1$s%2$s', 'product title followed by details', 'woocommerce-composite-products' ), '%t', '%m' ),
			'i18n_free'                             => __( 'Free!', 'woocommerce' ),
			'i18n_total'                            => __( 'Total', 'woocommerce-composite-products' ) . ': ',
			'i18n_none'                             => __( 'None', 'woocommerce-composite-products' ),
			'i18n_select_an_option'                 => __( 'Choose an option&hellip;', 'woocommerce-composite-products' ),
			'i18n_previous_step'                    => __( 'Previous &ndash; %s', 'woocommerce-composite-products' ),
			'i18n_next_step'                        => __( 'Next &ndash; %s', 'woocommerce-composite-products' ),
			'i18n_final_step'                       => __( 'Review Configuration', 'woocommerce-composite-products' ),
			'i18n_reset_selection'                  => __( 'Reset selection', 'woocommerce-composite-products' ),
			'i18n_clear_selection'                  => __( 'Clear selection', 'woocommerce-composite-products' ),
			'i18n_validation_issues'                => __( 'To continue, please review the following issues:', 'woocommerce-composite-products' ),
			'i18n_validation_issues_for'            => sprintf( __( '<span class="msg-source">%1$s</span> &rarr; <span class="msg-content">%2$s</span>', 'woocommerce-composite-products' ), '%c', '%e' ),
			'i18n_item_unavailable_text'            => __( 'The selected item cannot be purchased at the moment.', 'woocommerce-composite-products' ),
			'i18n_unavailable_text'                 => __( 'This product cannot be purchased at the moment.', 'woocommerce-composite-products' ),
			'i18n_select_component_option'          => __( 'Please choose an option to continue&hellip;', 'woocommerce-composite-products' ),
			'i18n_select_component_option_for'      => __( 'Please choose an option.', 'woocommerce-composite-products' ),
			'i18n_selected_product_invalid'         => __( 'The chosen option is incompatible with your current configuration.', 'woocommerce-composite-products' ),
			'i18n_selected_product_options_invalid' => __( 'The chosen product options are incompatible with your current configuration.', 'woocommerce-composite-products' ),
			'i18n_select_product_options'           => __( 'Please choose product options to continue&hellip;', 'woocommerce-composite-products' ),
			'i18n_select_product_options_for'       => __( 'Please choose product options.', 'woocommerce-composite-products' ),
			'i18n_summary_empty_component'          => __( 'Configure', 'woocommerce-composite-products' ),
			'i18n_summary_configured_component'     => __( 'Change', 'woocommerce-composite-products' ),
			'i18n_summary_static_component'         => __( 'View', 'woocommerce-composite-products' ),
			'i18n_insufficient_stock'               => __( 'Insufficient stock &rarr; %s.', 'woocommerce-composite-products' ),
			'i18n_comma_sep'                        => sprintf( _x( '%1$s, %2$s', 'comma-separated items', 'woocommerce-composite-products' ), '%s', '%v' ),
			'i18n_reload_threshold_exceeded'        => __( 'Loading &quot;%s&quot; options is taking a bit longer than usual. Would you like to keep trying?', 'woocommerce-composite-products' ),
			'i18n_no_compat_options'                => __( 'No compatible options to display.', 'woocommerce-composite-products' ),
			'i18n_step_not_accessible'              => __( '&quot;%s&quot; is not accessible. Please ensure that &quot;%s&quot; is compatible with the current configuration and that all preceeding steps are properly configured.', 'woocommerce-composite-products' ),
			'compat_options_autoload'               => 'yes',
			'currency_symbol'                       => get_woocommerce_currency_symbol(),
			'currency_position'                     => esc_attr( stripslashes( get_option( 'woocommerce_currency_pos' ) ) ),
			'currency_format_num_decimals'          => absint( get_option( 'woocommerce_price_num_decimals' ) ),
			'currency_format_decimal_sep'           => esc_attr( stripslashes( get_option( 'woocommerce_price_decimal_sep' ) ) ),
			'currency_format_thousand_sep'          => esc_attr( stripslashes( get_option( 'woocommerce_price_thousand_sep' ) ) ),
			'currency_format_trim_zeros'            => false === apply_filters( 'woocommerce_price_trim_zeros', false ) ? 'no' : 'yes',
			'script_debug_level'                    => array(), /* 'debug', 'debug:views', 'debug:events', 'debug:models', 'debug:scenarios' */
			'show_quantity_buttons'                 => 'no',
			'transition_type'                       => 'fade',
			'relocated_content_reset_on_return'     => 'yes',
			'is_wc_version_gte_2_3'                 => WC_CP_Core_Compatibility::is_wc_version_gte_2_3() ? 'yes' : 'no',
			'is_wc_version_gte_2_4'                 => WC_CP_Core_Compatibility::is_wc_version_gte_2_4() ? 'yes' : 'no',
			'use_wc_ajax'                           => WC_CP_Core_Compatibility::use_wc_ajax() ? 'yes' : 'no',
			'price_display_suffix'                  => esc_attr( get_option( 'woocommerce_price_display_suffix' ) ),
			'prices_include_tax'                    => esc_attr( wc_cp_prices_include_tax() ),
			'tax_display_shop'                      => esc_attr( wc_cp_tax_display_shop() ),
			'calc_taxes'                            => esc_attr( wc_cp_calc_taxes() ),
		) );

		wp_localize_script( 'wc-add-to-cart-composite', 'wc_composite_params', $params );
	}

	/**
	 * Show composited product data in the front-end.
	 * Used on first product page load to display content for component defaults.
	 *
	 * @param  mixed                   $product_id
	 * @param  mixed                   $component_id
	 * @param  WC_Product_Composite    $container_id
	 * @return string
	 */
	public function show_composited_product( $product_id, $component_id, $composite ) {

		if ( $product_id === '0' || $product_id === '' ) {

			return '<div class="component_data" data-price="0" data-regular_price="0" data-product_type="none" style="display:none;"></div>';

		} else {

			$composited_product = $composite->get_composited_product( $component_id, $product_id );
			$product            = $composited_product->get_product();

			if ( ! $product || ! $composited_product->is_purchasable() ) {

				if ( $composite->is_component_static( $component_id ) ) {
					$error = __( 'This item cannot be purchased at the moment.', 'woocommerce-composite-products' );
				} else {
					$link  = '<a class="clear_component_options" href="#clear_component">' . __( 'Clear selection?', 'woocommerce-composite-products' ) . '</a>';
					$error = sprintf( __( 'The selected item cannot be purchased at the moment. %s', 'woocommerce-composite-products' ), $link );
				}

				return sprintf( '<div class="component_data woocommerce-error" data-price="0" data-regular_price="0" data-product_type="invalid-product">%s</div>', $error );
			}
		}

		ob_start();

		WC_CP()->api->apply_composited_product_filters( $product, $component_id, $composite );

		/**
 		 * Action 'woocommerce_composite_show_composited_product'.
 		 *
 		 * @param  WC_Product            $product
 		 * @param  string                $component_id
 		 * @param  WC_Product_Composite  $composite
 		 */
		do_action( 'woocommerce_composite_show_composited_product', $product, $component_id, $composite );

		WC_CP()->api->remove_composited_product_filters();

		$output = ob_get_clean();

		return $output;
	}

	/**
	 * Changes the tr class of composited items in all templates to allow their styling.
	 *
	 * @param  string   $classname
	 * @param  array    $values
	 * @return string
	 */
	public function table_item_class( $classname, $values ) {

		if ( isset( $values[ 'composite_data' ] ) && ! empty( $values[ 'composite_parent' ] ) ) {
			return $classname . ' component_table_item';
		} elseif ( isset( $values[ 'composite_data' ] ) && ! empty( $values[ 'composite_children' ] ) ) {
			return $classname . ' component_container_table_item';
		}

		return $classname;
	}

	/**
	 * Adds order item title preambles to cart items ( Composite Attribute Descriptions ).
	 *
	 * @param  string   $content
	 * @param  array    $cart_item_values
	 * @param  string   $cart_item_key
	 * @return string
	 */
	public function in_cart_component_title( $content, $cart_item_values, $cart_item_key, $append_qty = false ) {

		if ( isset( $cart_item_values[ 'composite_children' ] ) ) {

			$product = $cart_item_values[ 'data' ];

			if ( function_exists( 'is_cart' ) && is_cart() && ! did_action( 'woocommerce_before_mini_cart' ) && $product->product_type === 'composite' ) {

				if ( $product->is_editable_in_cart() ) {
					$content = sprintf( __( '%s <small>(click to edit)</small>', 'woocommerce-composite-products' ), $content );
				}
			}

		} elseif ( ! empty( $cart_item_values[ 'composite_item' ] ) && ! empty( $cart_item_values[ 'composite_parent' ] ) ) {

			$item_id      = $cart_item_values[ 'composite_item' ];
			$composite_id = ! empty( $cart_item_values[ 'composite_data' ][ $item_id ][ 'composite_id' ] ) ? $cart_item_values[ 'composite_data' ][ $item_id ][ 'composite_id' ] : '';

			/** Documented in admin/wc-cp-admin.php */
			$item_title   = apply_filters( 'woocommerce_composite_component_title', $cart_item_values[ 'composite_data' ][ $item_id ][ 'title' ], $item_id, $composite_id );

			if ( is_checkout() || ( isset( $_REQUEST[ 'action' ] ) && $_REQUEST[ 'action' ] === 'woocommerce_update_order_review' ) ) {
				$append_qty = true;
			}

			if ( $append_qty ) {
				/**
				 * Filter qty html.
				 *
				 * @param  array   $cart_item_values
				 * @param  string  $cart_item_key
				 */
				$item_quantity = apply_filters( 'woocommerce_composited_cart_item_quantity_html', '<strong class="composited-product-quantity">' . sprintf( _x( ' &times; %s', 'qty string', 'woocommerce-composite-products' ), $cart_item_values[ 'quantity' ] ) . '</strong>', $cart_item_values, $cart_item_key );
			} else {
				$item_quantity = '';
			}

			$product_title = $content . $item_quantity;
			$item_data     = array( 'key' => $item_title, 'value' => $product_title );

			$this->enqueue_composited_table_item_js();

			ob_start();

			wc_get_template( 'component-item.php', array( 'component_data' => $item_data ), '', WC_CP()->plugin_path() . '/templates/' );

			return ob_get_clean();
		}

		return $content;
	}

	/**
	 * Delete composited item quantity from the review-order.php template. Quantity is inserted into the product name by 'in_cart_component_title'.
	 *
	 * @param  string 	$quantity
	 * @param  array 	$cart_item
	 * @param  string 	$cart_key
	 * @return string
	 */
	public function cart_item_component_quantity( $quantity, $cart_item, $cart_key ) {

		if ( ! empty( $cart_item[ 'composite_item' ] ) ) {
			return '';
		}

		return $quantity;
	}

	/**
	 * Adds component title preambles to order-details template.
	 *
	 * @param  string 	$content
	 * @param  array 	$order_item
	 * @return string
	 */
	public function order_table_component_title( $content, $order_item ) {

		if ( ! empty( $order_item[ 'composite_item' ] ) ) {

			$item_id        = $order_item[ 'composite_item' ];
			$composite_data = maybe_unserialize( $order_item[ 'composite_data' ] );
			$composite_id   = ! empty( $composite_data[ $item_id ][ 'composite_id' ] ) ? $composite_data[ $item_id ][ 'composite_id' ] : '';

			/** Documented in admin/wc-cp-admin.php */
			$item_title     = apply_filters( 'woocommerce_composite_component_title', $composite_data[ $item_id ][ 'title' ], $item_id, $composite_id );

			/**
			 * Filter qty html.
			 *
			 * @param  array  $order_item
			 */
			$item_quantity  = apply_filters( 'woocommerce_composited_order_item_quantity_html', '<strong class="composited-product-quantity">' . sprintf( _x( ' &times; %s', 'qty string', 'woocommerce-composite-products' ), $order_item[ 'qty' ] ) . '</strong>', $order_item );

			if ( did_action( 'woocommerce_view_order' ) || did_action( 'woocommerce_thankyou' ) || did_action( 'before_woocommerce_pay' ) ) {

				$item_data  = array( 'key' => $item_title, 'value' => $content . $item_quantity );

				$this->enqueue_composited_table_item_js();

				ob_start();

				wc_get_template( 'component-item.php', array( 'component_data' => $item_data ), '', WC_CP()->plugin_path() . '/templates/' );

				$content = ob_get_clean();

			} elseif ( did_action( 'wc_pip_header' ) || ( did_action( 'woocommerce_email_before_order_table' ) > did_action( 'woocommerce_email_after_order_table' ) ) ) {

				$class = did_action( 'wc_pip_header' ) ? 'composited-product' : '';

				$content = '<small><span class="' . $class . '" style="display:block">' . wp_kses_post( $item_title ) . ':</span> ' . wp_kses_post( $content ) . '</small>';
			}
		}

		return $content;
	}

	/**
	 * Delete composited item quantity from order-details template. Quantity is inserted into the product name by 'order_table_component_title'.
	 *
	 * @param  string 	$content
	 * @param  array 	$order_item
	 * @return string
	 */
	public function order_table_component_quantity( $content, $order_item ) {

		if ( isset( $order_item[ 'composite_item' ] ) && ! empty( $order_item[ 'composite_item' ] ) ) {
			return '';
		}

		return $content;
	}

	/**
	 * Enqeue js that wraps bundled table items in a div in order to apply indentation reliably.
	 *
	 * @return void
	 */
	private function enqueue_composited_table_item_js() {

		if ( ! $this->enqueued_composited_table_item_js ) {
			wc_enqueue_js( "
				var wc_cp_wrap_composited_table_item = function() {
					jQuery( '.component_table_item td.product-name' ).wrapInner( '<div class=\"component_table_item_indent\"></div>' );
				}

				jQuery( 'body' ).on( 'updated_checkout', function() {
					wc_cp_wrap_composited_table_item();
				} );

				wc_cp_wrap_composited_table_item();
			" );

			$this->enqueued_composited_table_item_js = true;
		}
	}

	/**
	 * Filters the reported number of cart items - counts only composite containers.
	 *
	 * @param  int 			$count
	 * @param  WC_Order 	$order
	 * @return int
	 */
	function cart_contents_count( $count ) {

		$cart     = WC()->cart->get_cart();
		$subtract = 0;

		foreach ( $cart as $key => $value ) {

			if ( isset( $value[ 'composite_item' ] ) ) {
				$subtract += $value[ 'quantity' ];
			}
		}

		return $count - $subtract;
	}

	/**
	 * Add cart widget filters.
	 *
	 * @return void
	 */
	function add_cart_widget_filters() {

		add_filter( 'woocommerce_widget_cart_item_visible', array( $this, 'cart_widget_item_visible' ), 10, 3 );
		add_filter( 'woocommerce_widget_cart_item_quantity', array( $this, 'cart_widget_item_qty' ), 10, 3 );
		add_filter( 'woocommerce_cart_item_name', array( $this, 'cart_widget_container_item_name' ), 10, 3 );
	}

	/**
	 * Remove cart widget filters.
	 *
	 * @return void
	 */
	function remove_cart_widget_filters() {

		remove_filter( 'woocommerce_widget_cart_item_visible', array( $this, 'cart_widget_item_visible' ), 10, 3 );
		remove_filter( 'woocommerce_widget_cart_item_quantity', array( $this, 'cart_widget_item_qty' ), 10, 3 );
		remove_filter( 'woocommerce_cart_item_name', array( $this, 'cart_widget_container_item_name' ), 10, 3 );
	}

	/**
	 * Tweak composite container qty.
	 *
	 * @param  bool 	$qty
	 * @param  array 	$cart_item
	 * @param  string 	$cart_item_key
	 * @return bool
	 */
	function cart_widget_item_qty( $qty, $cart_item, $cart_item_key ) {

		if ( isset( $cart_item[ 'composite_children' ] ) ) {
			$qty = '<span class="quantity">' . apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $cart_item[ 'data' ], $cart_item[ 'quantity' ] ), $cart_item, $cart_item_key ) . '</span>';
		}

		return $qty;
	}

	/**
	 * Do not show composited items.
	 *
	 * @param  bool 	$show
	 * @param  array 	$cart_item
	 * @param  string 	$cart_item_key
	 * @return bool
	 */
	function cart_widget_item_visible( $show, $cart_item, $cart_item_key ) {

		if ( isset( $cart_item[ 'composite_item' ] ) ) {
			return false;
		}

		return $show;
	}

	/**
	 * Tweak composite container name.
	 *
	 * @param  bool 	$show
	 * @param  array 	$cart_item
	 * @param  string 	$cart_item_key
	 * @return bool
	 */
	function cart_widget_container_item_name( $name, $cart_item, $cart_item_key ) {

		if ( isset( $cart_item[ 'composite_children' ] ) ) {
			$name = WC_CP_Product::get_title_string( $name, $cart_item[ 'quantity' ] );
		}

		return $name;
	}

	/**
	 * Inserts bundle contents after main wishlist bundle item is displayed.
	 *
	 * @param  array    $item       Wishlist item
	 * @param  array    $wishlist   Wishlist
	 * @return void
	 */
	public function wishlist_after_list_item_name( $item, $wishlist ) {

		if ( ! empty( $item[ 'composite_data' ] ) ) {
			echo '<dl>';
			foreach ( $item[ 'composite_data' ] as $composited_item => $composited_item_data ) {

				echo '<dt class="component_title_meta wishlist_component_title_meta">' . $composited_item_data[ 'title' ] . ':</dt>';
				echo '<dd class="component_option_meta wishlist_component_option_meta">' . get_the_title( $composited_item_data[ 'product_id' ] ) . ' <strong class="component_quantity_meta wishlist_component_quantity_meta product-quantity">&times; ' . $composited_item_data[ 'quantity' ] . '</strong></dd>';

				if ( ! empty ( $composited_item_data[ 'attributes' ] ) ) {

					$attributes = '';

					foreach ( $composited_item_data[ 'attributes' ] as $attribute_name => $attribute_value ) {

						$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $attribute_name ) ) );

						// If this is a term slug, get the term's nice name.
			            if ( taxonomy_exists( $taxonomy ) ) {

			            	$term = get_term_by( 'slug', $attribute_value, $taxonomy );

			            	if ( ! is_wp_error( $term ) && $term && $term->name ) {
			            		$attribute_value = $term->name;
			            	}

			            	$label = wc_attribute_label( $taxonomy );

			            // If this is a custom option slug, get the options name.
			            } else {

							$attribute_value    = apply_filters( 'woocommerce_variation_option_name', $attribute_value );
							$composited_product = wc_get_product( $composited_item_data[ 'product_id' ] );
							$product_attributes = $composited_product->get_attributes();

							if ( isset( $product_attributes[ str_replace( 'attribute_', '', $attribute_name ) ] ) ) {
								$label = wc_attribute_label( $product_attributes[ str_replace( 'attribute_', '', $attribute_name ) ][ 'name' ] );
							} else {
								$label = $attribute_name;
							}
						}

						$attributes = $attributes . $label . ': ' . $attribute_value . ', ';
					}
					echo '<dd class="component_attribute_meta wishlist_component_attribute_meta">' . rtrim( $attributes, ', ' ) . '</dd>';
				}
			}
			echo '</dl>';
			echo '<p class="component_notice wishlist_component_notice">' . __( '*', 'woocommerce-composite-products' ) . '&nbsp;&nbsp;<em>' . __( 'Accurate pricing info available in cart.', 'woocommerce-composite-products' ) . '</em></p>';
		}
	}

	/**
	 * Modifies wishlist bundle item price - the precise sum cannot be displayed reliably unless the item is added to the cart.
	 *
	 * @param  double   $price      Item price
	 * @param  array    $item       Wishlist item
	 * @param  array    $wishlist   Wishlist
	 * @return string   $price
	 */
	public function wishlist_list_item_price( $price, $item, $wishlist ) {

		if ( ! empty( $item[ 'composite_data' ] ) )
			return __( '*', 'woocommerce-composite-products' );

		return $price;

	}

	/**
	 * Indent composited items in emails.
	 *
	 * @param  string 	$css
	 * @return string
	 */
	function email_styles( $css ) {
		$css = $css . ".component_table_item td:nth-child(1) { padding-left: 35px !important; } .component_table_item td { border-top: none; }";
		return $css;
	}
}

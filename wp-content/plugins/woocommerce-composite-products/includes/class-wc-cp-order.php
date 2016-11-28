<?php
/**
 * Composite order-related filters and functions.
 *
 * @class 	WC_CP_Order
 * @version 3.6.0
 * @since   2.2.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_CP_Order {

	/**
	 * Flag to prevent woocommerce_order_get_items filters from modifying original order line items when calling WC_Order::get_items().
	 *
	 * @var boolean
	 */
	public static $override_order_items_filters = false;

	public function __construct() {

		// Filter price output shown in cart, review-order & order-details templates.
		add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'order_item_subtotal' ), 10, 3 );

		// Composite containers should not affect order status.
		add_filter( 'woocommerce_order_item_needs_processing', array( $this, 'container_item_needs_processing' ), 10, 3 );

		// Modify order items to include composite meta.
		add_action( 'woocommerce_add_order_item_meta', array( $this, 'add_order_item_meta' ), 10, 3 );

		// Hide composite configuration metadata in order line items.
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hide_order_item_meta' ) );

		// Filter order item count in the front-end.
		add_filter( 'woocommerce_get_item_count',  array( $this, 'order_item_count' ), 10, 3 );

		// Filter admin dashboard item count and classes.
		if ( is_admin() ) {
			add_filter( 'woocommerce_admin_order_item_count',  array( $this, 'order_item_count_string' ), 10, 2 );
			add_filter( 'woocommerce_admin_html_order_item_class',  array( $this, 'html_order_item_class' ), 10, 2 );
			add_filter( 'woocommerce_admin_order_item_class',  array( $this, 'html_order_item_class' ), 10, 2 );
		}

		// Apply 'woocommerce_composite_filter_product_from_item' filter while completing payment - @see 'get_product_from_item()' and 'container_item_needs_processing()'.
		add_action( 'woocommerce_pre_payment_complete', array( $this, 'apply_get_product_from_item_filter' ) );
		add_action( 'woocommerce_payment_complete', array( $this, 'remove_get_product_from_item_filter' ) );

		/*
		 * Order API Modifications.
		 */

		// Filter WC API response content to add composite container/children references.
		add_filter( 'woocommerce_api_order_response', array( $this, 'api_order_response' ), 10, 4 );

		// Filter WC Order item/product contents to modify shipping/pricing properties and meta depending on the "Per-Item Shipping" and "Per-Item Pricing" options state.
		add_filter( 'woocommerce_get_product_from_item', array( $this, 'get_product_from_item' ), 10, 3 );
		add_filter( 'woocommerce_order_get_items', array( $this, 'order_items' ), 10, 2 );
		add_filter( 'woocommerce_order_get_items', array( $this, 'order_items_part_of_meta' ), 10, 2 );
	}

	/**
	 * Find the parent of a composited item in an order.
	 *
	 * @param  array    $item
	 * @param  WC_Order $order
	 * @param  string   $return_type  'id'|'item'
	 * @return array
	 */
	public static function get_composite_parent( $item, $order, $return_type = 'item' ) {

		if ( isset( $item[ 'composite_parent' ] ) && isset( $item[ 'composite_data' ] ) ) {

			$composite_data = $item[ 'composite_data' ];

			self::$override_order_items_filters = true;

			foreach ( $order->get_items( 'line_item' ) as $order_item_id => $order_item ) {

				$is_parent = false;

				if ( isset( $order_item[ 'composite_cart_key' ] ) ) {
					$is_parent = $item[ 'composite_parent' ] === $order_item[ 'composite_cart_key' ];
				} else {
					$is_parent = isset( $order_item[ 'composite_data' ] ) && $order_item[ 'composite_data' ] === $composite_data && isset( $order_item[ 'composite_children' ] );
				}

				if ( $is_parent ) {
					return $return_type === 'id' ? $order_item_id : $order_item;
				}
			}

			self::$override_order_items_filters = false;

		}

		return false;
	}

	/**
	 * Find the children of a composite order item.
	 *
	 * @param  array     $item
	 * @param  WC_Order  $order
	 * @param  string    $return_type   'id'|'item'
	 * @param  boolean   $strict_mode   Set 'true' to get non-filtered descendents.
	 * @return array
	 */
	public static function get_composite_children( $item, $order, $return_type = 'item', $strict_mode = false ) {

		$children = array();

		if ( isset( $item[ 'composite_children' ] ) && isset( $item[ 'composite_data' ] ) ) {

			self::$override_order_items_filters = true;

			$children_keys = unserialize( $item[ 'composite_children' ] );

			if ( ! empty( $children_keys ) ) {

				foreach ( $order->get_items( 'line_item' ) as $order_item_id => $order_item ) {

					$is_child = false;

					if ( isset( $order_item[ 'composite_cart_key' ] ) ) {
						$is_child = in_array( $order_item[ 'composite_cart_key' ], $children_keys ) ? true : false;
					} else {
						$is_child = isset( $order_item[ 'composite_data' ] ) && $order_item[ 'composite_data' ] == $item[ 'composite_data' ] && isset( $order_item[ 'composite_parent' ] ) ? true : false;
					}

					if ( false === $strict_mode ) {
						/**
						 * Filter to allow sub-grouped order items to be recognized as composite container order item children.
						 *
						 * @param   boolean   $is_child
						 * @param   array     $checked_order_item
						 * @param   string    $container_order_item
						 * @param   WC_Order  $order
						 */
						$is_child = apply_filters( 'woocommerce_order_item_is_child_of_composite', $is_child, $order_item, $item, $order );
					}

					if ( $is_child ) {
						if ( $return_type === 'id' ) {
							$children[] = $order_item_id;
						} else {
							$children[ $order_item_id ] = $order_item;
						}
					}
				}
			}

			self::$override_order_items_filters = false;
		}

		return $children;
	}

	/**
	 * Modifies the subtotal of order-items (order-details.php) depending on the composite pricing strategy.
	 *
	 * @param  string 	$subtotal
	 * @param  array 	$item
	 * @param  WC_Order $order
	 * @return string
	 */
	public function order_item_subtotal( $subtotal, $item, $order ) {

		// If it's a composited item...
		if ( isset( $item[ 'composite_parent' ] ) ) {

			$composite_data = $item[ 'composite_data' ];

			// Find composite parent.
			$parent_item = self::get_composite_parent( $item, $order );

			if ( function_exists( 'is_account_page' ) && is_account_page() || function_exists( 'is_checkout' ) && is_checkout() ) {
				$wrap_start = '';
				$wrap_end   = '';
			} else {
				$wrap_start = '<small>';
				$wrap_end   = '</small>';
			}

			if ( $parent_item && $parent_item[ 'per_product_pricing' ] === 'no' ) {
				return '';
			} else {
				return  $wrap_start . __( 'Option subtotal', 'woocommerce-composite-products' ) . ': ' . $subtotal . $wrap_end;
			}
		}

		// If it's a parent item...
		if ( isset( $item[ 'composite_children' ] ) ) {

			if ( isset( $item[ 'subtotal_updated' ] ) ) {
				return $subtotal;
			}

			$children = self::get_composite_children( $item, $order );

			if ( ! empty( $children ) ) {

				foreach ( $children as $child ) {
					$item[ 'line_subtotal' ]     += $child[ 'line_subtotal' ];
					$item[ 'line_subtotal_tax' ] += $child[ 'line_subtotal_tax' ];
				}

				$item[ 'subtotal_updated' ] = 'yes';

				return $order->get_formatted_line_subtotal( $item );
			}
		}

		return $subtotal;
	}

	/**
	 * Composite Containers should not affect order status - let it be decided by composited items only.
	 *
	 * @param  bool 		$is_needed
	 * @param  WC_Product 	$product
	 * @param  int 			$order_id
	 * @return bool
	 */
	public function container_item_needs_processing( $is_needed, $product, $order_id ) {

		if ( $product->is_type( 'composite' ) && isset( $product->composite_needs_processing ) && 'no' === $product->composite_needs_processing ) {
			$is_needed = false;
		}

		return $is_needed;
	}

	/**
	 * Adds composite info to order items.
	 *
	 * @param  int 		$order_item_id
	 * @param  array 	$cart_item_values
	 * @param  string 	$cart_item_key
	 * @return void
	 */
	public function add_order_item_meta( $order_item_id, $cart_item_values, $cart_item_key ) {

		if ( isset( $cart_item_values[ 'composite_children' ] ) ) {

			wc_add_order_item_meta( $order_item_id, '_composite_children', $cart_item_values[ 'composite_children' ] );

			if ( $cart_item_values[ 'data' ]->is_priced_per_product() ) {
				wc_add_order_item_meta( $order_item_id, '_per_product_pricing', 'yes' );
			} else {
				wc_add_order_item_meta( $order_item_id, '_per_product_pricing', 'no' );
			}

			if ( $cart_item_values[ 'data' ]->is_shipped_per_product() ) {
				wc_add_order_item_meta( $order_item_id, '_per_product_shipping', 'yes' );
			} else {
				wc_add_order_item_meta( $order_item_id, '_per_product_shipping', 'no' );
			}
		}

		if ( ! empty( $cart_item_values[ 'composite_parent' ] ) ) {
			wc_add_order_item_meta( $order_item_id, '_composite_parent', $cart_item_values[ 'composite_parent' ] );
		}

		if ( ! empty( $cart_item_values[ 'composite_item' ] ) ) {
			wc_add_order_item_meta( $order_item_id, '_composite_item', $cart_item_values[ 'composite_item' ] );
		}

		if ( isset( $cart_item_values[ 'composite_data' ] ) ) {

			wc_add_order_item_meta( $order_item_id, '_composite_cart_key', $cart_item_key );

			wc_add_order_item_meta( $order_item_id, '_composite_data', $cart_item_values[ 'composite_data' ] );

			// Store shipping data - useful when exporting order content
			foreach ( WC()->cart->get_shipping_packages() as $package ) {

				foreach ( $package[ 'contents' ] as $pkg_item_id => $pkg_item_values ) {

					if ( $pkg_item_id === $cart_item_key ) {

						$bundled_shipping = $pkg_item_values[ 'data' ]->needs_shipping() ? 'yes' : 'no';
						$bundled_weight   = $pkg_item_values[ 'data' ]->get_weight();

						wc_add_order_item_meta( $order_item_id, '_bundled_shipping', $bundled_shipping );

						if ( $bundled_shipping === 'yes' ) {
							wc_add_order_item_meta( $order_item_id, '_bundled_weight', $bundled_weight );
						}
					}
				}
			}
		}
	}

	/**
	 * Hides composite metadata.
	 *
	 * @param  array $hidden
	 * @return array
	 */
	public function hide_order_item_meta( $hidden ) {
		return array_merge( $hidden, array( '_composite_parent', '_composite_item', '_composite_total', '_composite_cart_key', '_per_product_pricing', '_per_product_shipping', '_bundled_shipping', '_bundled_weight' ) );
	}

	/**
	 * Filters the reported number of order items - counts only composite containers.
	 *
	 * @param  int 			$count
	 * @param  string 		$type
	 * @param  WC_Order 	$order
	 * @return int
	 */
	public function order_item_count( $count, $type, $order ) {

		$subtract = 0;

		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			foreach ( $order->get_items() as $order_item ) {

				if ( isset( $order_item[ 'composite_item' ] ) ) {
					$subtract += $order_item[ 'qty' ];
				}
			}
		}

		return $count - $subtract;
	}

	/**
	 * Filters the string of order item count.
	 * Include bundled items as a suffix.
	 *
	 * @param  int          $count      initial reported count
	 * @param  WC_Order     $order      the order
	 * @return int                      modified count
	 */
	public function order_item_count_string( $count, $order ) {

		$add = 0;

		foreach ( $order->get_items() as $item ) {
			if ( isset( $item[ 'composite_item' ] ) ) {
				$add += $item[ 'qty' ];
			}
		}

		if ( $add > 0 ) {
			return sprintf( __( '%1$s, %2$s composited', 'woocommerce-composite-products' ), $count, $add );
		}

		return $count;
	}

	/**
	 * Filters the order item admin class.
	 *
	 * @param  string       $class     class
	 * @param  array        $item      the order item
	 * @return string                  modified class
	 */
	public function html_order_item_class( $class, $item ) {

		if ( isset( $item[ 'composite_item' ] ) ) {
			return $class . ' composited_item';
		}

		return $class;
	}

	/**
	 * Activates the 'get_product_from_item' filter below.
	 *
	 * @param  string $order_id
	 * @return void
	 */
	public function apply_get_product_from_item_filter( $order_id ) {

		add_filter( 'woocommerce_composite_filter_product_from_item', '__return_true' );
	}

	/**
	 * Deactivates the 'get_product_from_item' filter below.
	 *
	 * @param  string $order_id
	 * @return void
	 */
	public function remove_get_product_from_item_filter( $order_id ) {

		remove_filter( 'woocommerce_composite_filter_product_from_item', '__return_true' );
	}


	/*--------------------------*/
	/* Order API Modifications  */
	/*--------------------------*/

	/**
	 * Filters WC API order responses to add references between bundle container/children items. Also modifies expanded product data based on the "per-item pricing" and "per-item shipping" settings.
	 *
	 * @since  3.6.0
	 *
	 * @param  array         $order_data
	 * @param  WC_Order      $order
	 * @param  array         $fields
	 * @param  WC_API_Server $server
	 * @return array
	 */
	public function api_order_response( $order_data, $order, $fields, $server ) {

		if ( empty( $order_data[ 'line_items' ] ) ) {
			return $order_data;
		}

		$order_items = $order->get_items();

		foreach ( $order_data[ 'line_items' ] as $order_data_item_index => $order_data_item ) {

			$order_data_item_id = $order_data_item[ 'id' ];

			// Add relationship references.
			if ( ! isset( $order_items[ $order_data_item_id ] ) ) {
				continue;
			}

			$parent_id    = self::get_composite_parent( $order_items[ $order_data_item_id ], $order, 'id' );
			$children_ids = self::get_composite_children( $order_items[ $order_data_item_id ], $order, 'id', true );

			if ( false !== $parent_id ) {
				$order_data[ 'line_items' ][ $order_data_item_index ][ 'composite_parent' ] = $parent_id;
			} elseif ( ! empty ( $children_ids ) ) {
				$order_data[ 'line_items' ][ $order_data_item_index ][ 'composite_children' ] = $children_ids;
			} else {
				continue;
			}

			// Modify product data.
			if ( ! isset( $order_data_item[ 'product_data' ] ) ) {
				continue;
			}

			add_filter( 'woocommerce_composite_filter_product_from_item', '__return_true' );
			$product = $order->get_product_from_item( $order_items[ $order_data_item_id ] );
			remove_filter( 'woocommerce_composite_filter_product_from_item', '__return_true' );

			$order_data[ 'line_items' ][ $order_data_item_index ][ 'product_data' ][ 'price' ]                  = $product->get_price();
			$order_data[ 'line_items' ][ $order_data_item_index ][ 'product_data' ][ 'sale_price' ]             = $product->get_sale_price() ? $product->get_sale_price() : null;
			$order_data[ 'line_items' ][ $order_data_item_index ][ 'product_data' ][ 'regular_price' ]          = $product->get_regular_price();

			$order_data[ 'line_items' ][ $order_data_item_index ][ 'product_data' ][ 'shipping_required' ]      = $product->needs_shipping();

			$order_data[ 'line_items' ][ $order_data_item_index ][ 'product_data' ][ 'weight' ]                 = $product->get_weight() ? $product->get_weight() : null;
			$order_data[ 'line_items' ][ $order_data_item_index ][ 'product_data' ][ 'dimensions' ][ 'length' ] = $product->length;
			$order_data[ 'line_items' ][ $order_data_item_index ][ 'product_data' ][ 'dimensions' ][ 'width' ]  = $product->width;
			$order_data[ 'line_items' ][ $order_data_item_index ][ 'product_data' ][ 'dimensions' ][ 'height' ] = $product->height;
		}

		return $order_data;
	}

	/**
	 * Restores virtual status and weights/dimensions of bundle containers/children depending on the "per-item pricing" and "per-item shipping" settings.
	 * Virtual containers/children are assigned a zero weight and tiny dimensions in order to maintain the value of the associated item in shipments (for instance, when a bundle has a static price but is shipped per item).
	 *
	 * Restore composite container price - equal to base price in "per-item pricing" mode.
	 *
	 * @param  WC_Product $product
	 * @param  array      $item
	 * @param  WC_Order   $order
	 * @return WC_Product
	 */
	public function get_product_from_item( $product, $item, $order ) {

		/**
		 * Filter to control code execution based on context.
		 *
		 * @param  boolean   $enable_filter
		 * @param  WC_Order  $order
		 */
		if ( apply_filters( 'woocommerce_composite_filter_product_from_item', false, $order ) ) {

			// Restore base price.
			if ( ! empty( $product ) && $product->product_type === 'composite' && isset( $item[ 'composite_children' ] ) && isset( $item[ 'per_product_pricing' ] ) && $item[ 'per_product_pricing' ] === 'yes' ) {
				$product->price         = $product->get_base_price();
				$product->regular_price = $product->get_base_regular_price();
				$product->sale_price    = $product->get_base_sale_price();
			}

			// Modify shipping properties.
			if ( ! empty( $product ) && isset( $item[ 'composite_data' ] ) && isset( $item[ 'bundled_shipping' ] ) ) {
				if ( $item[ 'bundled_shipping' ] === 'yes' ) {
					if ( isset( $item[ 'bundled_weight' ] ) ) {
						$product->weight = $item[ 'bundled_weight' ];
					}
				} else {

					// Process container.
					if ( isset( $item[ 'composite_children' ] ) && isset( $item[ 'composite_cart_key' ] ) ) {

						$bundle_key                  = $item[ 'composite_cart_key' ];
						$child_items                 = self::get_composite_children( $item, $order );
						$non_virtual_child_exists    = false;

						// Virtual container converted to non-virtual with zero weight and tiny dimensions if it has non-virtual bundled children.
						foreach ( $child_items as $child_item_id => $child_item ) {
							if ( isset( $child_item[ 'bundled_shipping' ] ) && $child_item[ 'bundled_shipping' ] === 'yes' ) {
								$non_virtual_child_exists = true;
								break;
							}
						}

						if ( $non_virtual_child_exists ) {
							$product->virtual = 'no';
						}

						// If no child requires processing and the container is virtual, it should not require processing - @see 'container_item_needs_processing()'.
						if ( did_action( 'woocommerce_pre_payment_complete' ) && ! did_action( 'woocommerce_payment_complete' ) && $product->is_virtual() && sizeof( $child_items ) > 0 ) {

							$child_items                = self::get_composite_children( $item, $order, 'item', true );
							$composite_needs_processing = false;

							foreach ( $child_items as $child_item_id => $child_item ) {

								if ( $child_product = $order->get_product_from_item( $child_item ) ) {

									$virtual_downloadable_child = $child_product->is_downloadable() && $child_product->is_virtual();
									/** WC core filter. */
									if ( apply_filters( 'woocommerce_order_item_needs_processing', ! $virtual_downloadable_child, $child_product, $order->id ) ) {
										$composite_needs_processing = true;
										break;
									}
								}
							}

							if ( ! $composite_needs_processing ) {
								$product->composite_needs_processing = 'no';
							}
						}
					}

					$product->weight = 0;
					$product->length = $product->height = $product->width = 0.001;
				}
			}
		}

		return $product;
	}

	/**
	 * Adds "Part of" meta to bundled order items.
	 *
	 * @param  WC_Product $product
	 * @param  array      $item
	 * @param  WC_Order   $order
	 * @return WC_Product
	 */
	public function order_items_part_of_meta( $items, $order ) {

		/**
		 * Filter to control code execution based on context.
		 *
		 * @param  boolean   $enable_filter
		 * @param  WC_Order  $order
		 */
		$execute = apply_filters( 'woocommerce_composite_filter_order_items_part_of_meta', false, $order );

		if ( false === self::$override_order_items_filters && $execute ) {

			foreach ( $items as $item_id => $item ) {

				if ( isset( $item[ 'composite_data' ] ) && ! empty( $item[ 'composite_parent' ] ) ) {

					$parent = self::get_composite_parent( $item, $order );

					if ( $parent ) {

						if ( WC_CP_Core_Compatibility::is_wc_version_gte_2_4() ) {

							// Terrible hack: add an element in the 'item_meta_array' array (a puppy somewhere just died).
							if ( ! empty( $items[ $item_id ][ 'item_meta_array' ] ) ) {

								$keys         = array_keys( $items[ $item_id ][ 'item_meta_array' ] );
								$last_key     = end( $keys );

								$entry        = new stdClass();
								$entry->key   = __( 'Part of', 'woocommerce-composite-products' );
								$entry->value = $parent[ 'name' ];

								$items[ $item_id ][ 'item_meta_array' ][ $last_key + 1 ] = $entry;
							}
						}

						$items[ $item_id ][ 'item_meta' ][ __( 'Part of', 'woocommerce-composite-products' ) ] = $parent[ 'name' ];
					}
				}
			}
		}

		return $items;
	}

	/**
	 * Excludes/modifies order items depending on the "per-item pricing" and "per-item shipping" settings.
	 *
	 * @param  array    $items
	 * @param  WC_Order $order
	 * @return array
	 */
	public function order_items( $items, $order ) {

		$return_items = $items;

		/**
		 * Filter to control code execution based on context.
		 *
		 * @param  boolean   $enable_filter
		 * @param  WC_Order  $order
		 */
		$execute = apply_filters( 'woocommerce_composite_filter_order_items', false, $order );

		if ( false === self::$override_order_items_filters && $execute ) {

			$return_items = array();

			foreach ( $items as $item_id => $item ) {

				if ( isset( $item[ 'composite_children' ] ) && isset( $item[ 'composite_cart_key' ] ) ) {

					/*
					 * Do not export bundled items that are shipped packaged in the container ("bundled" shipping).
					 * Instead, add their totals into the container and create a container "Contents" meta field to provide a description of the included products.
					 */

					if ( isset( $item[ 'per_product_shipping' ] ) && $item[ 'per_product_shipping' ] === 'no' ) {

						$bundle_key  = $item[ 'composite_cart_key' ];

						// Aggregate contents
						$meta_key    = __( 'Contents', 'woocommerce-composite-products' );
						$meta_values = array();

						// Aggregate prices
						$bundle_totals = array(
							'line_subtotal'     => $item[ 'line_subtotal' ],
							'line_total'        => $item[ 'line_total' ],
							'line_subtotal_tax' => $item[ 'line_subtotal_tax' ],
							'line_tax'          => $item[ 'line_tax' ],
							'line_tax_data'     => maybe_unserialize( $item[ 'line_tax_data' ] )
						);

						foreach ( $items as $child_item_id => $child_item ) {

							if ( isset( $child_item[ 'composite_parent' ] ) && $child_item[ 'composite_parent' ] === $bundle_key && isset( $child_item[ 'bundled_shipping' ] ) && $child_item[ 'bundled_shipping' ] === 'no' ) {

								/*
								 * Aggregate bundled items shipped within the container as "Contents" meta of container.
								 */

								$child = $order->get_product_from_item( $child_item );

								if ( ! $child ) {
									continue;
								}

								$sku = $child->get_sku();

								if ( ! $sku ) {
									$sku = '#' . ( isset( $child->variation_id ) ? $child->variation_id : $child->id );
								}

								$title = WC_CP_Product::get_title_string( $child_item[ 'name' ], $child_item[ 'qty' ] );
								$meta  = '';

								if ( ! empty( $child_item[ 'item_meta' ] ) ) {

									if ( ! empty( $child_item[ 'item_meta' ][ __( 'Part of', 'woocommerce-composite-products' ) ] ) ) {
										unset( $child_item[ 'item_meta' ][ __( 'Part of', 'woocommerce-composite-products' ) ] );
									}

									if ( WC_CP_Core_Compatibility::is_wc_version_gte_2_4() ) {
										$item_meta = new WC_Order_Item_Meta( $child_item );
									} else {
										$item_meta = new WC_Order_Item_Meta( $child_item[ 'item_meta' ] );
									}

									$formatted_meta = $item_meta->display( true, true, '_', ', ' );

									if ( $formatted_meta ) {
										$meta = $formatted_meta;
									}
								}

								$meta_values[] = WC_CP_Helpers::format_product_title( $title, $sku, $meta, true );

								/*
								 * Aggregate the totals of bundled items shipped within the container into the container price.
								 */

								$bundle_totals[ 'line_subtotal' ]     += $child_item[ 'line_subtotal' ];
								$bundle_totals[ 'line_total' ]        += $child_item[ 'line_total' ];
								$bundle_totals[ 'line_subtotal_tax' ] += $child_item[ 'line_subtotal_tax' ];
								$bundle_totals[ 'line_tax' ]          += $child_item[ 'line_tax' ];

								$child_item_line_tax_data = maybe_unserialize( $child_item[ 'line_tax_data' ] );

								$bundle_totals[ 'line_tax_data' ][ 'total' ] = array_merge( $bundle_totals[ 'line_tax_data' ][ 'total' ], $child_item_line_tax_data[ 'total' ] );
							}
						}

						$items[ $item_id ][ 'line_tax_data' ] = serialize( $bundle_totals[ 'line_tax_data' ] );

						$items[ $item_id ]                    = array_merge( $item, $bundle_totals );


						if ( WC_CP_Core_Compatibility::is_wc_version_gte_2_4() ) {

							// Terrible hack: add an element in the 'item_meta_array' array (a puppy somewhere just died).
							if ( ! empty( $items[ $item_id ][ 'item_meta_array' ] ) ) {

								$keys         = array_keys( $items[ $item_id ][ 'item_meta_array' ] );
								$last_key     = end( $keys );

								$entry        = new stdClass();
								$entry->key   = $meta_key;
								$entry->value = implode( ', ', $meta_values );

								$items[ $item_id ][ 'item_meta_array' ][ $last_key + 1 ] = $entry;
							}
						}

						$items[ $item_id ][ 'item_meta' ][ $meta_key ] = implode( ', ', $meta_values );

						$return_items[ $item_id ] = $items[ $item_id ];

					/*
					 * If the bundled items are shipped individually ("per-item" shipping), do not export the container unless it has a non-zero price.
					 * In this case, instead of marking it as virtual, modify its weight and dimensions (tiny values) to avoid any extra shipping costs and ensure that its value is included in the shipment - @see 'get_product_from_item'.
					 */

					} elseif ( $item[ 'line_total' ] > 0 ) {
						$return_items[ $item_id ] = $items[ $item_id ];
					}

				} elseif ( isset( $item[ 'composite_parent' ] ) && isset( $item[ 'composite_cart_key' ] ) ) {

					if ( ! isset( $item[ 'bundled_shipping' ] ) || $item[ 'bundled_shipping' ] === 'yes' ) {
						$return_items[ $item_id ] = $items[ $item_id ];
					}

				} else {
					$return_items[ $item_id ] = $items[ $item_id ];
				}
			}
		}

		return $return_items;
	}

	/**
	 * @deprecated
	 */
	public function get_composited_order_item_container( $item, $order ) {
		_deprecated_function( 'WC_CP_Order::get_composited_order_item_container()', '3.5.0', 'WC_CP_Order::get_composite_parent()' );
		return self::get_composite_parent( $item, $order );
	}
}

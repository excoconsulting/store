<?php
/**
 * Print Invoices & Packing Lists Integration.
 *
 * @since  4.14.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_PB_PIP_Compatibility {

	public static function init() {

		add_filter( 'wc_pip_document_table_row_item_data', array( __CLASS__, 'filter_pip_row_item_data' ), 10, 5 );
		add_filter( 'wc_pip_document_table_rows', array( __CLASS__, 'filter_pip_table_rows' ), 52, 4 );
		add_filter( 'wc_pip_document_table_product_class', array( __CLASS__, 'filter_pip_document_table_bundled_item_class' ), 10, 4 );
		add_filter( 'wc_pip_order_item_name', array( WC_PB()->display, 'woo_bundles_order_table_item_title' ), 10, 2 );

		add_filter( 'wc_pip_packing_list_hide_virtual_item', array( __CLASS__, 'filter_pip_hide_virtual_item' ), 10, 4 );

		if ( class_exists( 'WC_PB_CP_Compatibility' ) ) {
			add_filter( 'wc_pip_order_item_name', array( 'WC_PB_CP_Compatibility', 'composited_bundle_order_table_item_title' ), 9, 2 );
		}

		add_action( 'wc_pip_styles', array( __CLASS__, 'add_pip_bundled_item_styles' ) );
	}

	/**
	 * Ensure bundle container line items are always dislpayed.
	 *
	 * @param  boolean    $hide
	 * @param  WC_Product $product
	 * @param  array      $item
	 * @param  WC_Order   $order
	 * @return boolean
	 */
	public static function filter_pip_hide_virtual_item( $hide, $product, $item, $order ) {

		if ( ! empty( $item[ 'bundled_items' ] ) ) {
			$hide = false;
		}

		return $hide;
	}

	/**
	 * Add bundled item class CSS rule.
	 * @return  void
	 */
	public static function add_pip_bundled_item_styles() {
		?>
		.bundled-product {
			padding-left: 35px;
		}
		<?php
	}

	/**
	 * Add 'bundled-product' class to pip row classes.
	 *
	 * @param  array      $classes
	 * @param  WC_Product $product
	 * @param  array      $item
	 * @param  string     $type
	 * @return array
	 */
	public static function filter_pip_document_table_bundled_item_class( $classes, $product, $item, $type ) {

		if ( ! empty( $item[ 'bundled_by' ] ) ) {
			$classes[] = 'bundled-product';
		}

		return $classes;
	}

	/**
	 * Temporarily add order item data to array.
	 *
	 * @param  array      $item_data
	 * @param  array      $item
	 * @param  WC_Product $product
	 * @param  string     $order_id
	 * @param  string     $type
	 * @return array
	 */
	public static function filter_pip_row_item_data( $item_data, $item, $product, $order_id, $type ) {

		$item_data[ 'wc_pb_item_data' ] = $item;

		return $item_data;
	}

	/**
	 * Re-sort PIP table rows so that bundled items are always below their container.
	 *
	 * @param  array  $table_rows
	 * @param  array  $items
	 * @param  string $order_id
	 * @param  string $type
	 * @return array
	 */
	public static function filter_pip_table_rows( $table_rows, $items, $order_id, $type ) {

		$filtered_table_rows = array();

		if ( ! empty( $table_rows ) ) {

			foreach ( $table_rows as $table_row_key => $table_row_data ) {

				if ( empty( $table_row_data[ 'items' ] ) ) {
					continue;
				}

				$sorted_rows = array();

				foreach ( $table_row_data[ 'items' ] as $row_item ) {

					if ( isset( $row_item[ 'wc_pb_item_data' ] ) && isset( $row_item[ 'wc_pb_item_data' ][ 'bundled_items' ] ) ) {

						$sorted_rows[] = $row_item;

						$children_keys = unserialize( $row_item[ 'wc_pb_item_data' ][ 'bundled_items' ] );

						// Look for the child in all table rows and bring it over.
						foreach ( $table_rows as $table_row_key_inner => $table_row_data_inner ) {
							foreach ( $table_row_data_inner[ 'items' ] as $row_item_inner ) {

								$is_child = false;

								if ( isset( $row_item_inner[ 'wc_pb_item_data' ] ) && isset( $row_item_inner[ 'wc_pb_item_data' ][ 'bundle_cart_key' ] ) ) {
									$is_child = in_array( $row_item_inner[ 'wc_pb_item_data' ][ 'bundle_cart_key' ], $children_keys );
								}

								if ( $is_child ) {
									$sorted_rows[] = $row_item_inner;
								}
							}
						}

					} else {

						// Do not copy bundled items (will be looked up by their parents).
						if ( ! isset( $row_item[ 'wc_pb_item_data' ] ) || ! isset( $row_item[ 'wc_pb_item_data' ][ 'bundled_by' ] ) ) {
							$sorted_rows[] = $row_item;
						}
					}
				}

				// Unset our (now redundant) data.
				foreach ( $sorted_rows as $sorted_row_item => $sorted_row_item_data ) {
					if ( isset( $sorted_row_item_data[ 'wc_pb_item_data' ] ) ) {
						unset( $sorted_rows[ $sorted_row_item ][ 'wc_pb_item_data' ]  );
					}
				}

				$filtered_table_rows[ $table_row_key ]            = $table_row_data;
				$filtered_table_rows[ $table_row_key ][ 'items' ] = $sorted_rows;
			}

			// Ensure empty categories are not displayed at all.
			foreach ( $filtered_table_rows as $table_row_key => $table_row_data ) {
				if ( empty( $table_row_data[ 'items' ] ) ) {
					unset( $filtered_table_rows[ $table_row_key ] );
				}
			}
		}

		return $filtered_table_rows;
	}
}

WC_PB_PIP_Compatibility::init();

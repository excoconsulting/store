<?php
/**
 * WooCommerce Customer/Order/Coupon CSV Import Suite
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Customer/Order/Coupon CSV Import Suite to newer
 * versions in the future. If you wish to customize WooCommerce Customer/Order/Coupon CSV Import Suite for your
 * needs please refer to http://docs.woothemes.com/document/customer-order-csv-import-suite/ for more information.
 *
 * @package   WC-CSV-Import-Suite/Classes
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! class_exists( 'WP_Importer' ) ) return;

defined( 'ABSPATH' ) or exit;

/**
 * WooCommerce Order Importer class for managing the import process of a CSV file.
 *
 * The main difficulty in importing orders is that by default WooCommerce relies
 * on the internal post_id to act as the order number, which we can not set when
 * importing orders. So we have to make some concessions based on the users
 * particular environment:
 *
 * 1. If they happen to have a custom order number plugin installed that makes
 *    use of the filter/action hooks provided by this plugin, then this import
 *    plugin will integrate seemlessly with that plugin and things will be happy.
 *    Granted, one assumption has to be made on the import format: that a custom
 *    order number will consist of a numeric (incrementing) piece, and a string
 *    formatted piece, but after that custom order number plugins can go nuts
 * 2. If the user does not have a custom order number plugin installed, then
 *    this plugin will compensate by at least setting the provided order number
 *    to the _order_number_formatted/_order_number metas used by the Sequential
 *    Order Number Pro plugin, and add an order note providing the original
 *    order number.
 *
 * The second tricky part is handling the order items. This is dealt with by
 * allowing an arbitrary number of columns of the form order_item_1, order_item_2,
 * etc. The value for each order item is a pipe-delimited string containing:
 * sku|quantity|price
 *
 * @since 1.0.0
 *
 * Class renamed from WC_CSV_Order_Import to WC_CSV_Import_Suite_Order_Import in 3.0.0
 */
class WC_CSV_Import_Suite_Order_Import extends WC_CSV_Import_Suite_Importer {


	/** @var array Known order meta fields */
	private $order_meta_fields;

	/** @var array Known order address fields */
	private $order_address_fields;

	/** @var array order line item types */
	private $line_types;

	/** @var array field mappings for the `line_item`-type line items **/
	private $line_item_mapping;

	/** @var array field mappings for the `tax`-type line items **/
	private $tax_item_mapping;

	/** @var array field mappings for the `shipping`-type line items **/
	private $shipping_item_mapping;

	/** @var array field mappings for the `fee`-type line items **/
	private $fee_item_mapping;

	/** @var array order statuses holder */
	private $order_statuses_clean;

	/** @var array order shipping methods holder */
	private $available_shipping_methods;

	/** @var array order shipping methods holder */
	private $available_payment_gateways;

	/** @var array refunded order item ids */
	private $refunded_item_order_ids = array();


	/**
	 * Construct and initialize the importer
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		parent::__construct();

		$this->title                   = __( 'Import Orders', 'woocommerce-csv-import-suite' );
		$this->file_url_import_enabled = apply_filters( 'woocommerce_csv_order_file_url_import_enabled', true );

		$this->i18n = array(
			'count'          => esc_html__( '%s orders' ),
			'count_inserted' => esc_html__( '%s orders inserted' ),
			'count_merged'   => esc_html__( '%s orders merged' ),
			'count_skipped'  => esc_html__( '%s orders skipped' ),
			'count_failed'   => esc_html__( '%s orders failed' ),
		);

		$this->order_meta_fields = array(
			"tax_total",
			"shipping_total",
			"shipping_tax_total",
			"cart_discount",
			"order_total",
			"payment_method",
			"customer_user",
			"download_permissions_granted",
		);

		$this->order_address_fields = array(
			'first_name',
			'last_name',
			'company',
			'email',
			'phone',
			'address_1',
			'address_2',
			'city',
			'state',
			'postcode',
			'country',
		);

		$this->line_types = array(
			'line_item' => 'line_items',
			'shipping'  => 'shipping_lines',
			'fee'       => 'fee_lines',
			'tax'       => 'tax_lines',
			'coupon'    => 'coupon_lines',
		);

		$this->line_item_mapping = array(
			'id' => 'order_item_id',
		);

		$this->tax_item_mapping = array(
			'id'                => 'order_item_id',
			'name'              => 'code',
			'title'             => 'label',
			'total'             => 'tax_amount',
			'shipping_total'    => 'shipping_tax_amount',
			'tax_rate_compound' => 'compound',
		);

		$this->shipping_item_mapping = array(
			'id'                => 'order_item_id',
			'method'            => 'method_title', // translation from CSV Export default
			'total'             => 'cost',
		);

		$this->fee_item_mapping = array(
			'id'    => 'order_item_id',
			'title' => 'name',
			'tax'   => 'total_tax',
		);

		// provide some base custom order number functionality, while allowing 3rd party plugins with custom
		// order number functionality to unhook this and provide their own logic
		add_action( 'woocommerce_set_order_number', array( $this, 'woocommerce_set_order_number' ), 10, 3 );

		add_filter( 'wc_csv_import_suite_woocommerce_order_csv_column_default_mapping', array( $this, 'column_default_mapping' ), 10, 2 );

		add_action( 'wc_csv_import_suite_column_mapping_options', array( $this, 'advanced_column_mapping_options' ), 10, 5 );

		add_action( 'wc_csv_import_suite_before_import_options_fields', array( $this, 'advanced_import_options' ) );
	}


	/**
	 * Get CSV column mapping options
	 *
	 * @since 3.0.0
	 * @return array Associative array of column mapping options
	 */
	public function get_column_mapping_options() {

		$billing_prefix  = __( 'Billing: %s',  'woocommerce-csv-import-suite' );
		$shipping_prefix = __( 'Shipping: %s', 'woocommerce-csv-import-suite' );

		// note that there are no mapping options for line item fields - they are
		// passed through as-is
		return array(

			__( 'Order data', 'woocommerce-csv-import-suite' ) => array(
				'order_id'                     => __( 'Order ID', 'woocommerce-csv-import-suite' ),
				'order_number_formatted'       => __( 'Formatted order number', 'woocommerce-csv-import-suite' ),
				'order_number'                 => __( 'Order number', 'woocommerce-csv-import-suite' ),
				'date'                         => __( 'Date', 'woocommerce-csv-import-suite' ),
				'status'                       => __( 'Status', 'woocommerce-csv-import-suite' ),
				'currency'                     => __( 'Currency', 'woocommerce-csv-import-suite' ),
				'shipping_total'               => __( 'Shipping total', 'woocommerce-csv-import-suite' ),
				'shipping_tax_total'           => __( 'Shipping tax total', 'woocommerce-csv-import-suite' ),
				'fee_total'                    => __( 'Fees total', 'woocommerce-csv-import-suite' ),
				'fee_tax_total'                => __( 'Fees tax total', 'woocommerce-csv-import-suite' ),
				'tax_total'                    => __( 'Tax total', 'woocommerce-csv-import-suite' ),
				'cart_discount'                => __( 'Discount', 'woocommerce-csv-import-suite' ),
				'order_total'                  => __( 'Order total', 'woocommerce-csv-import-suite' ),
				'refunded_total'               => __( 'Total refunded amount', 'woocommerce-csv-import-suite' ),
				'payment_method'               => __( 'Payment method', 'woocommerce-csv-import-suite' ),
				'shipping_method'              => __( 'Shipping method', 'woocommerce-csv-import-suite' ),
				'customer_note'                => __( 'Customer note', 'woocommerce-csv-import-suite' ),
				'order_notes'                  => __( 'Order notes', 'woocommerce-csv-import-suite' ),
				'download_permissions_granted' => __( 'Download Permissions Granted', 'woocommerce-csv-import-suite' ),
			),

			__( 'Order items', 'woocommerce-csv-import-suite' ) => array(
				'line_items'                   => __( 'Line items', 'woocommerce-csv-import-suite' ),
				'shipping_items'               => __( 'Shipping items', 'woocommerce-csv-import-suite' ),
				'tax_items'                    => __( 'Taxes', 'woocommerce-csv-import-suite' ),
				'fee_items'                    => __( 'Fees', 'woocommerce-csv-import-suite' ),
				'coupons'                      => __( 'Coupons', 'woocommerce-csv-import-suite' ),
			),

			__( 'Customer data', 'woocommerce-csv-import-suite' ) => array(
				'customer_user'       => __( 'Customer user (ID, username or email)', 'woocommerce-csv-import-suite' ),
				'billing_first_name'  => sprintf( $billing_prefix,  __( 'First name', 'woocommerce-csv-import-suite' ) ),
				'billing_last_name'   => sprintf( $billing_prefix,  __( 'Last name', 'woocommerce-csv-import-suite' ) ),
				'billing_company'     => sprintf( $billing_prefix,  __( 'Company', 'woocommerce-csv-import-suite' ) ),
				'billing_address_1'   => sprintf( $billing_prefix,  __( 'Address 1', 'woocommerce-csv-import-suite' ) ),
				'billing_address_2'   => sprintf( $billing_prefix,  __( 'Address 2', 'woocommerce-csv-import-suite' ) ),
				'billing_city'        => sprintf( $billing_prefix,  __( 'City', 'woocommerce-csv-import-suite' ) ),
				'billing_state'       => sprintf( $billing_prefix,  __( 'State', 'woocommerce-csv-import-suite' ) ),
				'billing_postcode'    => sprintf( $billing_prefix,  __( 'Postcode', 'woocommerce-csv-import-suite' ) ),
				'billing_country'     => sprintf( $billing_prefix,  __( 'Country', 'woocommerce-csv-import-suite' ) ),
				'billing_email'       => sprintf( $billing_prefix,  __( 'Email', 'woocommerce-csv-import-suite' ) ),
				'billing_phone'       => sprintf( $billing_prefix,  __( 'Phone', 'woocommerce-csv-import-suite' ) ),
				'shipping_first_name' => sprintf( $shipping_prefix, __( 'First name', 'woocommerce-csv-import-suite' ) ),
				'shipping_last_name'  => sprintf( $shipping_prefix, __( 'Last name', 'woocommerce-csv-import-suite' ) ),
				'shipping_company'    => sprintf( $shipping_prefix, __( 'Company', 'woocommerce-csv-import-suite' ) ),
				'shipping_address_1'  => sprintf( $shipping_prefix, __( 'Address 1', 'woocommerce-csv-import-suite' ) ),
				'shipping_address_2'  => sprintf( $shipping_prefix, __( 'Address 2', 'woocommerce-csv-import-suite' ) ),
				'shipping_city'       => sprintf( $shipping_prefix, __( 'City', 'woocommerce-csv-import-suite' ) ),
				'shipping_state'      => sprintf( $shipping_prefix, __( 'State', 'woocommerce-csv-import-suite' ) ),
				'shipping_postcode'   => sprintf( $shipping_prefix, __( 'Postcode', 'woocommerce-csv-import-suite' ) ),
				'shipping_country'    => sprintf( $shipping_prefix, __( 'Country', 'woocommerce-csv-import-suite' ) ),
			),

			'refunds' => __( 'Refunds', 'woocommerce-csv-import-suite' ),
		);
	}


	/**
	 * Adjust default mapping for CSV columns
	 *
	 * @since 3.0.0
	 * @param string $map_to
	 * @param string $column column
	 * @return string
	 */
	public function column_default_mapping( $map_to, $column ) {

		switch ( $column ) {

			// translations from the new JSON format (following WC API naming conventions)
			case 'id':                 return 'order_id';
			case 'created_at':         return 'date';
			case 'total':              return 'order_total';
			case 'cart_tax':           return 'tax_total';
			case 'total_shipping':     return 'shipping_total';
			case 'total_discount':     return 'cart_discount';
			case 'shipping_tax':       return 'shipping_tax_total';
			case 'total_refunded':     return 'refunded_total';
			case 'note':               return 'customer_note';
			case 'shipping_lines':     return 'shipping_items';
			case 'fee_lines':          return 'fee_items';
			case 'coupon_lines':       return 'coupons';
			case 'tax_lines':          return 'tax_items';

			// translations for our own legacy format
			case 'order_shipping':     return 'shipping_total';
			case 'order_shipping_tax': return 'shipping_tax_total';
			case 'order_fees':         return 'fee_total';
			case 'order_fee_tax':      return 'fee_tax_total';
			case 'order_tax':          return 'tax_total';
			case 'order_currency':     return 'currency';
			case 'discount_total':     return 'cart_discount';

			// translations for the Customer/Order Export plugin legacy format
			case 'order_status':       return 'status';
			case 'shipping':           return 'shipping_total';
			case 'fees':               return 'fee_total';
			case 'fee_tax':            return 'fee_tax_total';
			case 'tax':                return 'tax_total';
			case 'billing_post_code':  return 'billing_postcode';
			case 'shipping_post_code': return 'shipping_postcode';
			case 'order_items':        return 'line_items';
			case 'customer_id':        return 'customer_user';

			// translations for the Customer/Order Export plugin one item per line legacy format
			case 'row_amount':         return 'item_quantity';
			case 'row_price':          return 'item_total';
			case 'item_variation':     return 'item_meta';
			case 'item_amount':        return 'item_quantity';
		}

		return $map_to;
	}


	/**
	 * Provide additional column mapping options for certain CSV input formats
	 *
	 * @since 3.0.0
	 * @param array $options Associative array of column mapping options
	 * @param string $importer Importer type
	 * @param array $headers Normalized headers
	 * @param array $raw_headers Raw headers from CSV file
	 * @param array $columns Associative array as 'column' => 'default mapping'
	 * @return array
	 */
	public function advanced_column_mapping_options( $options, $importer, $headers, $raw_headers, $columns ) {

		if ( 'woocommerce_order_csv' == $importer ) {

			$format = $this->detect_csv_file_format( $raw_headers );
			$group  = __( 'Order items', 'woocommerce-csv-import-suite' );

			switch ( $format ) {

				case 'csv_import_legacy':

					$order_item_options = $tax_item_options = $shipping_method_options = $shipping_cost_options = array();

					// add an option for each order item column
					foreach ( $columns as $column => $value ) {

						if ( SV_WC_Helper::str_starts_with( $column, 'order_item_' ) ) {

							$parts                         = explode( '_', $column );
							$number                        = array_pop( $parts );
							$order_item_options[ $column ] = sprintf( __( 'Order item %d', 'woocommerce-csv-import-suite' ), $number );
						}

						elseif ( SV_WC_Helper::str_starts_with( $column, 'tax_item_' ) ) {

							$parts                       = explode( '_', $column );
							$number                      = array_pop( $parts );
							$tax_item_options[ $column ] = sprintf( __( 'Tax item %d', 'woocommerce-csv-import-suite' ), $number );
						}

						elseif ( SV_WC_Helper::str_starts_with( $column, 'shipping_method_' ) ) {

							$parts                       = explode( '_', $column );
							$number                      = array_pop( $parts );
							$shipping_method_options[ $column ] = sprintf( __( 'Shipping method %d', 'woocommerce-csv-import-suite' ), $number );
						}

						elseif ( SV_WC_Helper::str_starts_with( $column, 'shipping_cost_' ) ) {

							$parts                       = explode( '_', $column );
							$number                      = array_pop( $parts );
							$shipping_cost_options[ $column ] = sprintf( __( 'Shipping cost %d', 'woocommerce-csv-import-suite' ), $number );
						}

					}

					$options[ $group ] = $order_item_options + $tax_item_options + $shipping_method_options + $shipping_cost_options;
				break;

				case 'csv_export_default_one_row_per_item':

					unset( $options[ $group ]['line_items'] );

					$new_options = array(
						'item_name'     => __( 'Item name', 'woocommerce-csv-import-suite' ),
						'item_sku'      => __( 'Item SKU', 'woocommerce-csv-import-suite' ),
						'item_quantity' => __( 'Item quantity', 'woocommerce-csv-import-suite' ),
						'item_tax'      => __( 'Item tax', 'woocommerce-csv-import-suite' ),
						'item_total'    => __( 'Item total', 'woocommerce-csv-import-suite' ),
						'item_meta'     => __( 'Item meta', 'woocommerce-csv-import-suite' ),
					);

					$options[ $group ] = $new_options + $options[ $group ];
				break;

				case 'csv_export_legacy_one_row_per_item':

					unset( $options[ $group ]['line_items'] );

					$new_options = array(
						'item_sku'       => __( 'Item SKU', 'woocommerce-csv-import-suite' ),
						'item_name'      => __( 'Item name', 'woocommerce-csv-import-suite' ),
						'item_meta'      => __( 'Item meta', 'woocommerce-csv-import-suite' ),
						'item_quantity'  => __( 'Item quantity', 'woocommerce-csv-import-suite' ), // Item Amount
						'item_total'     => __( 'Item total', 'woocommerce-csv-import-suite' ), // Row Price
					);

					$options[ $group ] = $new_options + $options[ $group ];
				break;
			}
		}

		return $options;
	}


	/**
	 * Render advanced options for order CSV import
	 *
	 * @since 3.0.0
	 */
	public function advanced_import_options() {

		if ( ! isset( $_GET['import'] ) || 'woocommerce_order_csv' != $_GET['import'] ) {
			return;
		}

		?>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Allow unknown products', 'woocommerce-csv-import-suite' ); ?>
			</th>
			<td>
				<label>
					<input type="checkbox" value="1" name="options[allow_unknown_products]" id="wc-csv-import-suite-order-allow-unknown-products" />
					<?php esc_html_e( 'Allow line items with unknown product sku/id. The line item will not be linked to any product, so this is not necessarily recommended.', 'woocommerce-csv-import-suite' ); ?>
				</label>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Re-calculate taxes & totals', 'woocommerce-csv-import-suite' ); ?>
			</th>
			<td>
				<label>
					<input type="checkbox" value="1" name="options[recalculate_totals]" id="wc-csv-import-suite-order-recalculate-totals" />
					<?php esc_html_e( 'Re-calculate taxes and totals after importing the order. This may result in different tax and order totals than in the CSV file.', 'woocommerce-csv-import-suite' ); ?>
				</label>
			</td>
		</tr>

		<?php
	}


	/**
	 * Checks whether the CSV uses a multi-line format
	 *
	 * Checks whether data for a single item spans across multiple physical lines
	 * in the CSV file.
	 *
	 * @since 3.0.0
	 * @see WC_CSV_Import_Suite_Importer::is_multiline_format();
	 * @param array $raw_headers Raw CSV headers
	 * @return bool
	 */
	protected function is_multiline_format( $raw_headers ) {

		$format = $this->detect_csv_file_format( $raw_headers );

		return in_array( $format, array(
			'csv_export_default_one_row_per_item',
			'csv_export_legacy_one_row_per_item'
		) );
	}


	/**
	 * Get identifier for a single item
	 *
	 * Utility method to get a unique identifier for a single item in a CSV file.
	 * Useful for detecting physical lines in a CSV file to form a single item.
	 *
	 * @since 3.0.0
	 * @see WC_CSV_Import_Suite_Importer::get_item_identifier();
	 * @param array $data Item data, either raw data from CSV parser, mapped to
	 *                    columns, or parsed item data
	 * @return int|string|null
	 */
	protected function get_item_identifier( $data ) {

		if ( ! empty( $data['order_number_formatted'] ) ) {
			return $data['order_number_formatted'];
		}

		if ( ! empty( $data['order_id'] ) ) {
			return $data['order_id'];
		}

		if ( ! empty( $data['id'] ) ) {
			return $data['id'];
		}

		return null;
	}


	/**
	 * Merge data from multiple parsed lines into one item
	 *
	 * @since 3.0.0
	 * @see WC_CSV_Import_Suite_Importer::merge_parsed_items();
	 * @param array $items Array of parsed items
	 * @return array
	 */
	protected function merge_parsed_items( $items ) {

		$combined_item = array();

		foreach ( $items as $line_num => $item ) {

			// get full data set from first item
			if ( empty( $combined_item ) ) {
				$combined_item = $item;
			}

			// merge the line items from all other items
			else {
				$combined_item['line_items'][] = array_shift( $item['line_items'] );
			}

		}

		return $combined_item;
	}

	/**
	 * Parse raw order data, building and returning an array of order data
	 * to import into the database.
	 *
	 * The order data is broken into two portions:	the couple of defined fields
	 * that make up the wp_posts table, and then the name-value meta data that is
	 * inserted into the wp_postmeta table.	Within the meta data category, there
	 * are known meta fields, such as 'billing_first_name' for instance, and then
	 * arbitrary meta fields are allowed and identified by a CSV column title with
	 * the prefix 'meta:'.
	 *
	 * @since 3.0.0
	 * @param array $item Raw order data from CSV
	 * @param array $options Optional. Options
	 * @param array $raw_headers Optional. Raw headers
	 * @throws \WC_CSV_Import_Suite_Import_Exception validation, parsing errors
	 * @return array Parsed order data
	 */
	protected function parse_item( $item, $options = array(), $raw_headers = array() ) {

		$csv_file_format        = $this->detect_csv_file_format( $raw_headers );
		$csv_export_file        = SV_WC_Helper::str_starts_with( $csv_file_format, 'csv_export' );
		$merging                = $options['merge'];
		$allow_unknown_products = isset( $options['allow_unknown_products'] ) && $options['allow_unknown_products'];
		$insert_non_matching    = isset( $options['insert_non_matching'] ) && $options['insert_non_matching'];

		$order_data = $postmeta = $terms = array();

		/* translators: Placeholders: %s - row number */
		$preparing = $merging ? __( '> Row %s - preparing for merge.', 'woocommerce-csv-import-suite' ) : __( '> Row %s - preparing for import.', 'woocommerce-csv-import-suite' );
		wc_csv_import_suite()->log( sprintf( $preparing, $this->line_num ) );

		// prepare order identifiers (number)
		$order_id = ! empty( $item['order_id'] ) ? $item['order_id'] : null;

		// standard format: optional integer order number and formatted order number
		$order_number           = ! empty( $item['order_number'] )           ? $item['order_number']           : null;
		$order_number_formatted = ! empty( $item['order_number_formatted'] ) ? $item['order_number_formatted'] : $order_number;

		// Customer/Order CSV Export plugin format. If the Sequential
		// Order Numbers Pro plugin is installed, order_number will be
		// available, if the Order ID is numeric use that, but otherwise
		// we have no idea what the underlying sequential order number might be
		if ( $csv_export_file ) {
			$order_number_formatted = ! empty( $item['order_id'] ) ? $item['order_id'] : null;
		}

		// use formatted for underlying order number if order number not supplied
		if ( is_numeric( $order_number_formatted ) && ! $order_number ) {
			$order_number = $order_number_formatted;
		}

		// validate the supplied formatted order number/order number
		if ( ! $csv_export_file && $order_number && ! is_numeric( $order_number ) ) {
			throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_order_number_invalid', sprintf( __( 'Order number field must be an integer: %s.', 'woocommerce-csv-import-suite' ), $order_number ) );
		}

		if ( ! $csv_export_file && $order_number_formatted && ! $order_number ) {
			throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_missing_numerical_order_number', __( 'Formatted order number provided but no numerical order number, see the documentation for further details.', 'woocommerce-csv-import-suite' ) );
		}

		// prepare for merging
		if ( $merging ) {

			$found_order = false;

			// check that at least one required field for merging is provided
			if ( ! $order_id && ! $order_number_formatted ) {
				wc_csv_import_suite()->log( __( '> > Cannot merge without id or order number. Importing instead.', 'woocommerce-csv-import-suite' ) );
				$merging = false;
			}

			// check if order exists

			// 1. try matching order number
			if ( $order_number_formatted ) {

				$found_order = $this->get_order_id_by_formatted_number( $order_number_formatted );

				// and secondly allowing other plugins to return an entirely different order number if the simple search above doesn't do it for them
				$found_order = apply_filters( 'woocommerce_find_order_by_order_number', $found_order, $order_number_formatted );

				if ( ! $found_order ) {

					if ( ! $order_id ) {

						if ( $insert_non_matching ) {
							wc_csv_import_suite()->log( sprintf( __( '> > Skipped. Cannot find order with formatted number %s. Importing instead.', 'woocommerce-csv-import-suite' ), $order_number_formatted ) );
							$merging = false;
						} else {
							throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_cannot_find_order', sprintf( __( 'Cannot find order with formatted number %s.', 'woocommerce-csv-import-suite' ), $order_number_formatted ) );
						}

					} else {
						// we can keep trying with order ID
						wc_csv_import_suite()->log( sprintf( __( '> > Cannot find order with formatted number %s.', 'woocommerce-csv-import-suite' ), $order_number_formatted ) );
					}

				} else {
					wc_csv_import_suite()->log( sprintf( __( '> > Found order with formatted number %s.', 'woocommerce-csv-import-suite' ), $order_number_formatted ) );
					$order_data['id'] = $found_order;
				}

			}

			if ( ! $found_order && $order_id ) {

				// check if an order with the same ID exists
				$found_order = 'shop_order' == get_post_type( $order_id );

				if ( ! $found_order ) {

					if ( $insert_non_matching ) {
						wc_csv_import_suite()->log( sprintf( __( '> > Skipped. Cannot find order with id %s. Importing instead.', 'woocommerce-csv-import-suite' ), $order_id ) );
						$merging = false;
					} else {
						throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_cannot_find_order', sprintf( __( 'Cannot find order with id %s.', 'woocommerce-csv-import-suite' ), $order_id ) );
					}

				} else {
					wc_csv_import_suite()->log( sprintf( __( '> > Found order with ID %s.', 'woocommerce-csv-import-suite' ), $order_id ) );
					$order_data['id'] = $order_id;
				}
			}
		}

		// prepare for importing
		if ( ! $merging && $order_number_formatted ) {

			// ensure the order does not exist
			$order_id = $this->get_order_id_by_formatted_number( $order_number_formatted );

			if ( $order_id ) {
				throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_order_already_exists', sprintf( __( 'Order %s already exists.', 'woocommerce-csv-import-suite' ), $order_number_formatted ) );
			}
		}

		// handle the special (optional) customer_user field
		if ( isset( $item['customer_user'] ) && $item['customer_user'] ) {

			// attempt to find the customer user
			$found_customer = false;

			if ( is_numeric( $item['customer_user'] ) ) {

				$user = get_user_by( 'id', $item['customer_user'] );

				if ( ! $user ) {
					throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_cannot_find_customer', sprintf( __( 'Cannot find customer with id %s.', 'woocommerce-csv-import-suite' ), $item['customer_user'] ) );
				} else {
					$found_customer = $user->ID;
				}
			}

			// check by email
			elseif ( is_email( $item['customer_user'] ) ) {
				$found_customer = email_exists( $item['customer_user'] );
			}

			// "But I still haven't found what I'm looking for..." ♫
			if ( ! $found_customer ) {
				$found_customer = username_exists( $item['customer_user'] );
			}

			if ( $found_customer ) {
				$order_data['customer_id'] = $found_customer; // user id
			}

			else {
				// guest checkout
				$order_data['customer_id'] = 0;
			}
		}

		// see if we can link the user by billing email
		elseif ( isset( $item['billing_email'] ) && $item['billing_email'] ) {

			$found_customer = email_exists( $item['billing_email'] );

			if ( $found_customer ) {
				$order_data['customer_id'] = $found_customer;
			}
			else  {
				$order_data['customer_id'] = 0; // guest checkout
			}

		} else {
			// guest checkout
			$order_data['customer_id'] = 0;
		}

		// validate order status
		if ( ! empty( $item['status'] ) ) {

			$item['status'] = str_replace( 'wc-', '', strtolower( $item['status'] ) );
			$order_statuses = $this->get_order_statuses_clean();

			// unknown order status
			if ( ! array_key_exists( $item['status'], $order_statuses ) ) {
				/* translators: Placeholders: %1$s - order status, %2$s - available order statuses */
				throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_invalid_order_status', sprintf( __( 'Unknown order status %1$s (%2$s).', 'woocommerce-csv-import-suite' ),	$item['status'], implode( array_keys( $order_statuses ) ), ', ' ) );
			} else {
				$order_data['status'] = $item['status'];
			}

		} else {
			// TODO: WC core sets order status as pending by default, should we follow that instead?
			$order_data['status'] = 'processing'; // default
		}

		// validate order date
		if ( ! empty( $item['date'] ) ) {
			if ( false === ( $item['date'] = strtotime( $item['date'] ) ) ) {

				// invalid date format
				throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_invalid_date_format', sprintf( __( 'Invalid date format %s.', 'woocommerce-csv-import-suite' ), $item['date'] ) );
			} else {
				$order_data['date'] = $item['date'];
			}
		} else {
			$order_data['date'] = time();
		}

		// prepare order notes
		if ( ! empty( $item['order_notes'] ) ) {
			$order_data['order_notes'] = explode( '|', $item['order_notes'] );
		}

		// prepare customer notes
		if ( ! empty( $item['customer_note'] ) ) {
			$order_data['note'] = $item['customer_note'];
		}

		// validate currency
		if ( ! empty( $item['currency'] ) ) {

			$currency_codes = array_keys( get_woocommerce_currencies() );

			if ( ! in_array( strtoupper( $item['currency'] ), $currency_codes ) ) {
				// invalid currency code
				throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_invalid_currency_code', sprintf( __( 'Skipped. Invalid or unsupported currency %s.', 'woocommerce-csv-import-suite' ),	$item['currency'] ) );
			} else {
				$order_data['currency'] = $item['currency'];
			}
		}

		// optional order number, for convenience
		if ( ! is_null( $order_number ) ) {
			$order_data['order_number'] = $order_number;
		}

		if ( $order_number_formatted ) {
			$order_data['order_number_formatted'] = $order_number_formatted;
		}

		// set totals to null
		$tax_total = $fee_tax_total = $shipping_tax_total = $shipping_total = $fee_total = null;

		// construct order addresses
		$address_types = array( 'billing_', 'shipping_' );
		$order_data['billing_address']  = array();
		$order_data['shipping_address'] = array();

		foreach ( $address_types as $address_type ) {

			foreach ( $this->order_address_fields as $field ) {

				$key = $address_type . $field;

				if ( isset( $item[ $key ] ) ) {
					$order_data[ $address_type . 'address' ][ $field ] = wc_clean( $item[ $key ] );
					unset( $item[ $key ] );
				}
			}
		}

		// if shipping address was not provided, copy it from the billing address
		if ( empty( $order_data['shipping_address'] ) ) {
			$order_data['shipping_address'] = $order_data['billing_address'];
		}

		// get any known order meta fields, and default any missing ones to 0/null
		// the provided shipping/payment method will be used as-is, and if found in the list of available ones, the respective titles will also be set
		foreach ( $this->order_meta_fields as $column ) {

			switch ( $column ) {

				case 'customer_user':
					// customer_user is handled outside of meta scope
				break;

				case 'payment_method':

					$value              = isset( $item[ $column ] ) ? $item[ $column ] : '';
					$available_gateways = $this->get_available_payment_gateways();

					// look up shipping method by id or title
					$payment_method = isset( $available_gateways[ $value ] ) ? $value : null;

					if ( ! $payment_method ) {
						// try by title
						foreach ( $available_gateways as $method ) {

							if ( 0 === strcasecmp( $method->title, $value ) ) {
								$payment_method = $method->id;
								break; // go with the first one we find
							}
						}
					}

					if ( $payment_method ) {
						// known payment method found
						$postmeta['_payment_method']       = $payment_method;
						$postmeta['_payment_method_title'] = $available_gateways[ $payment_method ]->title;
					} elseif ( $value ) {
						// standard format, payment method but no title
						$postmeta['_payment_method']       = $value;
						$postmeta['_payment_method_title'] = '';
					} else {
						// none
						$postmeta['_payment_method']       = '';
						$postmeta['_payment_method_title'] = '';
					}
				break;

				// handle numerics
				case 'shipping_total':
					// ignore blanks but allow zeroes
					$shipping_total = isset( $item[ $column ] ) && is_numeric( $item[ $column ] ) ? $item[ $column ] : null;	// save the order shipping total for later use
					$postmeta['_order_shipping'] = number_format( (float) $shipping_total, 2, '.', '' );
				break;

				case 'shipping_tax_total':
				case 'tax_total':
					// ignore blanks but allow zeroes
					if ( isset( $item[ $column ] ) && is_numeric( $item[ $column ] ) ) {
						$$column = $item[ $column ];
					}
				break;

				case 'cart_discount':
				case 'order_total':
					// ignore blanks but allow zeroes
					$value = isset( $item[ $column ] ) && is_numeric( $item[ $column ] ) ? $item[ $column ] : null;
					$postmeta[ '_' . $column ] = number_format( (float) $value, 2, '.', '' );
				break;

				case 'billing_country':
				case 'shipping_country':
					$value = isset( $item[ $column ] ) ? $item[ $column ] : '';

					// support country name or code by converting to code
					$country_code = array_search( $value, WC()->countries->countries );
					if ( $country_code ) {
						$value = $country_code;
					}

					$item[ $column ] = $value;
				break;

				case 'download_permissions_granted':

					if ( isset( $item['download_permissions_granted'] ) ) {
						$order_data['download_permissions_granted'] = $item['download_permissions_granted'];
					}
				break;

				default: $postmeta[ '_' . $column ] = isset( $item[ $column ] ) ? $item[ $column ] : "";
			}
		}

		// get order fee totals
		foreach ( array( 'fee_total', 'fee_tax_total' ) as $column ) {
			// ignore blanks but allow zeroes
			if ( isset( $item[ $column ] ) && is_numeric( $item[ $column ] ) ) {
				$$column = $item[ $column ];
			}
		}

		// get any custom meta fields
		foreach ( $item as $key => $value ) {

			if ( ! $value ) {
				continue;
			}

			// handle meta: columns - import as custom fields
			if ( SV_WC_Helper::str_starts_with( $key, 'meta:' ) ) {

				// get meta key name
				$meta_key = trim( str_replace( 'meta:', '', $key ) );

				// skip known meta fields
				if ( in_array( $meta_key, $this->order_meta_fields ) ) {
					continue;
				}

				// add to postmeta array
				$postmeta[ $meta_key ] = $value;
			}

			// handle tax: columns - import as taxonomy terms
			elseif ( SV_WC_Helper::str_starts_with( $key, 'tax:' ) ) {

				$results = $this->parse_taxonomy_terms( $key, $value );

				if ( ! $results ) {
					continue;
				}

				// add to array
				$terms[] = array(
					'taxonomy' => $results[0],
					'terms'    => $results[1],
				);
			}
		}

		// taxes & tax total
		$tax_items = $this->parse_tax_items( $item, $tax_total, $shipping_tax_total );

		// shipping items and shipping total
		$shipping_items = $this->parse_shipping_items( $item, $shipping_total, $csv_file_format, $tax_items );

		$postmeta[ '_shipping_total' ] = number_format( (float) $shipping_total, 2, '.', '' );

		// line items
		$order_items = $this->parse_line_items( $item, $csv_file_format, $merging, $allow_unknown_products, $tax_items );

		// unless merging, require at least 1 line item
		if ( ! $merging && empty( $order_items ) ) {
			throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_missing_line_items', esc_html__( 'Cannot import order without line items', 'woocommerce-csv-import-suite' ) );
		}

		// order fees
		$fee_items = $this->parse_fee_items( $item, $fee_total, $fee_tax_total, $tax_items );

		// coupons
		$coupons = $this->parse_coupons( $item );

		// add the order tax totals to the order meta
		$postmeta['_order_tax']          = number_format( (float) $tax_total, 2, '.', '' );
		$postmeta['_order_shipping_tax'] = number_format( (float) $shipping_tax_total, 2, '.', '' );


		// put most of it together
		$order_data['terms']          = $terms;
		$order_data['order_meta']     = $postmeta;

		$order_data['line_items']     = $order_items;
		$order_data['shipping_lines'] = $shipping_items;
		$order_data['tax_lines']      = $tax_items;
		$order_data['fee_lines']      = $fee_items;
		$order_data['coupon_lines']   = $coupons;


		// refunds
		$order_data['refunds'] = $this->parse_refunds( $item, $order_data );


		// when merging, try to match items to existing order items
		if ( $merging ) {

			foreach ( $this->line_types as $line_type => $key ) {

				$items = $this->match_order_items( $order_data['id'], $order_data[ $key ], $line_type, $csv_file_format );

				// if there were errors, skip merging any order items of the same type,
				// as we can't reliably merge on errors.
				if ( is_wp_error( $items ) ) {
					wc_csv_import_suite()->log( $items->get_error_message() );

					// unsetting the order item type array will ensure that items of this
					// type will not be touched during processing, see: process_items()
					unset( $order_data[ $key ] );
					break;
				}

				$order_data[ $key ] = $items;
			}
		}

		/**
		 * Filter parsed order data
		 *
		 * Gives a chance for 3rd parties to parse data from custom columns
		 *
		 * @since 3.0.0
		 * @param array $order Parsed order data
		 * @param array $data Raw order data from CSV
		 * @param array $options Import options
		 * @param array $raw_headers Raw CSV headers
		 */
		return apply_filters( 'wc_csv_import_suite_parsed_customer_data', $order_data, $item, $options, $raw_headers );
	}


	/**
	 * Parse line items from raw CSV data
	 *
	 * @since 3.0.0
	 * @param array $item Raw order data from CSV file
	 * @param string $format CSV file format
	 * @param bool $merging
	 * @param bool $allow_unknown_products. Optional. Defaults to false.
	 * @param array $tax_items Parsed tax items
	 * @throws \WC_CSV_Import_Suite_Import_Exception validation, parsing errors
	 * @return array Parsed line item data
	 */
	private function parse_line_items( $item = array(), $format, $merging, $allow_unknown_products = false, $tax_items ) {
		global $wpdb;

		$line_items = $raw_line_items = array();

		switch ( $format ) {

			case 'default':

				if ( empty( $item['line_items'] ) ) {
					return array();
				}

				// default format supports line items both in JSON and in a "simple" format
				if ( $this->is_possibly_json_array( $item['line_items'] ) ) {

					try {
						$raw_line_items = $this->parse_json( $item['line_items'] );
					} catch( WC_CSV_Import_Suite_Import_Exception $e ) {
						throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_line_items_parse_json_error', sprintf( esc_html__( 'Error while parsing line items for line %d: %s', 'woocommerce-csv-import-suite' ), $this->line_num, $e->getMessage() ) );
					}

				} else {
					$raw_line_items = $this->parse_delimited_string( $item['line_items'] );
				}

				// validate / parse line items
				if ( ! empty( $raw_line_items ) ) {
					foreach ( $raw_line_items as $raw_line_item ) {

						// normalize line item fields
						foreach ( $this->line_item_mapping as $from => $to ) {

							if ( isset( $raw_line_item[ $from ] ) ) {

								$raw_line_item[ $to ] = $raw_line_item[ $from ];
								unset( $raw_line_item[ $from ] );
							}
						}

						$line_items[] = $this->parse_line_item( $raw_line_item, $allow_unknown_products, $tax_items );
					}
				}
			break;

			case 'csv_import_legacy':

				if ( ! empty( $item['order_item_1'] ) ) {

					// one or more order items
					$i = 1;
					while ( ! empty( $item[ 'order_item_' . $i ] ) ) {

						// split on non-escaped pipes
						$_item_data = $this->split_on_delimiter( $item[ 'order_item_' . $i ], '|' );

						// pop off the special sku, qty and total values
						$product_identifier = array_shift( $_item_data );	// sku or product_id:id
						$qty                = array_shift( $_item_data );
						$total              = array_shift( $_item_data );

						if ( ! $product_identifier || ! $qty || ! is_numeric( $total ) ) {
							// invalid item
							throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_missing_sku_quantity_or_email', sprintf( esc_html__( 'Missing SKU, quantity or total for %s on line %s.', 'woocommerce-csv-import-suite' ), 'order_item_' . $i, $this->line_num ) );
						}

						// product_id or sku
						if ( SV_WC_Helper::str_starts_with( $product_identifier, 'product_id:' ) ) {

							// product by product_id
							$product_id = substr( $product_identifier, 11 );

							// not a product
							if ( ! $this->is_valid_product( $product_id ) ) {
								$product_id = '';
							}

						} else {
							// find by sku
							$product_id = $this->get_product_id_by_sky( $product_identifier );
						}

						if ( ! $allow_unknown_products && ! $product_id ) {
							throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_unknown_order_item', sprintf( esc_html__( 'Unknown order item: %s.', 'woocommerce-csv-import-suite' ), $product_identifier ) );
						}

						// get any additional item meta
						$item_meta = ! empty( $_item_data ) ? $this->split_key_value_pairs( $_item_data, ':' ) : array();

						$line_items[] = array( 'product_id' => $product_id, 'quantity' => $qty, 'total' => $total, 'meta' => $item_meta );

						$i++;
					}
				}
			break;

			case 'csv_export_legacy_one_row_per_item':
				$line_items[] = $this->parse_csv_export_line_item( $item, $allow_unknown_products, ':' );
			break;

			case 'csv_export_default_one_row_per_item':
				$line_items[] = $this->parse_csv_export_line_item( $item, $allow_unknown_products );
			break;

			case 'csv_export_legacy':

				// split line items on non-escaped semicolons
				$_line_items = $this->split_on_delimiter( $item['line_items'], ';' );

				if ( ! empty( $_line_items ) ) {
					foreach ( $_line_items as $_line_item ) {

						// replace any escaped semicolons
						$_line_item = str_replace( '\;', ';', $_line_item );

						$name     = $_line_item;
						$quantity = 1;

						$meta = $sku = null;

						// try to detect item quantity. quantity is either the last thing
						// at the end of string, or just before a dash that's separating
						// basic item data from item meta
						if ( preg_match( '/(x[0-9]+)$/', $_line_item, $matches ) ) {

							$name     = trim( str_replace( $matches[0], '', $_line_item ) );
							$quantity = $matches[0];

						} else if ( preg_match( '/(x[0-9]+ - )/', $_line_item, $matches ) ) {

							$parts = array_map( 'trim', explode( $matches[0], $_line_item ) );
							$name  = $parts[0];
							$meta  = isset( $parts[1] ) ? $parts[1] : null;

						}

						// try to get the item sku, which should be the last part of the name
						// such as: item name (sku)
						if ( preg_match( '/\(([^)]+)\)/', $name, $matches ) ) {
							$sku  = $matches[1];
							$name = trim( str_replace( $matches[0], '', $name ) );
						}

						$_line_item = array(
							'item_name'     => $name,
							'item_sku'      => $sku,
							'item_quantity' => $quantity,
							'item_meta'     => $meta,
							'item_total'    => null,
						);

						// the format does not provide a total for any items... so we can't
						// really support that format at all, left here for phun :)

						// parse line item data
						$line_items[] = $this->parse_csv_export_line_item( $_line_item, $allow_unknown_products, ':' );
					}
				}
			break;
		}

		// attach variation data to line items, in a similar format as it's done
		// in WC_CLI/WC_API classes
		foreach ( $line_items as $line_item_key => $line_item ) {

			// Get variation data from product
			if ( $line_item['product_id'] ) {
				$product = wc_get_product( $line_item['product_id'] );

				if ( $product && ( $product->is_type( 'variable' ) || $product->is_type( 'variation' ) || $product->is_type( 'subscription_variation' ) ) && method_exists( $product, 'get_variation_id' ) ) {

					foreach ( $product->get_variation_attributes() as $variation_key => $value ) {
						$variation_key = str_replace( 'attribute_', '', str_replace( 'pa_', '', $variation_key ) ); // from get_attributes in class-wc-api-products.php
						$line_items[ $line_item_key ]['variations'][ $variation_key ] = $value;
					}
				}
			}

			// get variation data from item meta. This will override any variation
			// data from the product
			if ( ! empty( $line_item['meta'] ) ) {
				foreach ( $line_item['meta'] as $meta_key => $value ) {

					if ( SV_WC_Helper::str_starts_with( 'pa_', $meta_key ) ) {
						$variation_key = str_replace( 'pa_', '', $meta_key ); // from get_attributes in class-wc-api-products.php
						$line_items[ $line_item_key ]['variations'][ $variation_key ] = $value;

						// remove variation data from meta
						unset( $line_items[ $line_item_key ]['meta'][ $meta_key ] );
					}
				}
			}
		}

		return $line_items;
	}


	/**
	 * Match order items to existing order items
	 *
	 * @since 3.0.0
	 * @param int $order_id
	 * @param array $items
	 * @param string $type
	 * @param string $csv_file_format
	 * @return array Items
	 */
	private function match_order_items( $order_id, $items, $type, $csv_file_format ) {

		$order    = wc_get_order( $order_id );
		$existing = $order->get_items( $type );

		foreach ( $items as $key => $item ) {

			$order_item_id = $this->get_matching_order_item_id( $existing, $item, $type, $csv_file_format );

			if ( is_wp_error( $order_item_id ) ) {
				return $order_item_id;
			}

			if ( $order_item_id ) {

				$items[ $key ]['order_item_id'] = $order_item_id;

			} else {

				switch ( $type ) {

					case 'line_item':
						$message = sprintf( __( '> > Cannot update product "%s" without order item ID. Inserting instead.', 'woocommerce-csv-import-suite' ), $item['product_id'] );
					break;

					case 'coupon':
						$message = sprintf( __( '> > Cannot update coupon "%s" without order item ID. Inserting instead.', 'woocommerce-csv-import-suite' ), $item['code'] );
					break;

					case 'fee':
						$message = sprintf( __( '> > Cannot update fee "%s" without order item ID. Inserting instead.', 'woocommerce-csv-import-suite' ), $item['title'] );
					break;

					case 'shipping':
						$message = sprintf( __( '> > Cannot update shipping method "%s" without order item ID. Inserting instead.', 'woocommerce-csv-import-suite' ), $item['title'] );
					break;

					case 'tax':
						$message = sprintf( __( '> > Cannot update tax rate "%s" without order item ID. Inserting instead.', 'woocommerce-csv-import-suite' ), $item['code'] );
					break;
				}

				if ( $message ) {
					wc_csv_import_suite()->log( $message );
				}
			}
		}

		return $items;
	}


	/**
	 * Match a single order item to an existing item
	 *
	 * @since 3.0.0
	 * @param array $existing
	 * @param array $item
	 * @param string $type
	 * @param string $csv_file_format
	 * @return int|null Order item ID or null if no match was found
	 */
	private function get_matching_order_item_id( $existing, $item, $type, $csv_file_format ) {

		$order_item_id = null;

		// try matching based on provided order_item_id
		if ( isset( $item['order_item_id'] ) && $item['order_item_id'] ) {

			$new_order_item_id = $item['order_item_id'];

			// first, try matching based on _original_order_item_id
			foreach ( $existing as $existing_order_item_id => $existing_item ) {

				$_meta                   = $existing_item['item_meta'];
				$_original_order_item_id = isset( $_meta['_original_order_item_id'] ) && isset( $_meta['_original_order_item_id'][0] )
																 ? $_meta['_original_order_item_id'][0]
																 : null;

				if ( $_original_order_item_id && $new_order_item_id == $_original_order_item_id ) {
					$order_item_id = $existing_order_item_id;
					break; // we're extremely happy with the first match we've found :)
				}
			}

			// if no match was found, try matching directly on order_item_id
			if ( ! $order_item_id ) {

				if ( isset( $existing[ $new_order_item_id ] ) ) {
					$order_item_id = $new_order_item_id;
				}
			}
		}

		// No direct match was found, try matching based on other properties.
		//
		// This will give a chance for older CSV formats to be able to merge orders.
		// The basic idea is that if there is a property that could be used to
		// more or less uniquely identify an order item, we try it. If we find only
		// a single match (probably 95% of cases), it should be fairly safe to
		// update an item based on that. If there are multiple matches (which can
		// happen with a more complex store setup or order ), an error should be
		// returned instead, which will result in the current order being skipped.
		if ( ! $order_item_id ) {

			$properties = array();

			switch ( $type ) {

				case 'line_item':
					$properties = array( 'variation_id' => 'product_id', 'product_id' => 'product_id', 'name' => 'title' );
				break;

				case 'coupon':
				case 'fee':
					$properties = array( 'name' => 'title' );
				break;

				case 'shipping':
					$properties = array( 'method_id' => 'method_id', 'name' => 'title' );
				break;

				case 'tax':
					$properties = array( 'rate_id' => 'rate_id', 'name' => 'code' );
				break;
			}

			$order_item_id = $this->get_matching_order_item_id_by_properties( $existing, $item, $type, $properties );
		}


		/**
		 * Filter the matching order_item_id when updating order
		 *
		 * @since 3.0.0
		 * @param mixed $order_item_id
		 * @param array $item
		 * @param array $existing_items
		 * @param string $type
		 * @param string $csv_file_format
		 */
		return apply_filters( 'wc_csv_import_suite_found_order_item_id', $order_item_id, $item, $existing, $type, $csv_file_format );
	}


	/**
	 * Try to match an order item by comparing a list of properties
	 *
	 * Property order is significant - higher priorty properties should be listed
	 * first.
	 *
	 * @since 3.0.0
	 * @param array $existing Existing items
	 * @param array $item Item to be inserted/merged
	 * @param string $type Order item type
	 * @param array $properties Prioritized list of properties to match against
	 * @return int|null|WP_Error
	 */
	private function get_matching_order_item_id_by_properties( $existing, $item, $type, $properties = array() ) {

		if ( empty( $existing ) ) {
			return null;
		}

		$order_item_id = null;
		$matches       = array();

		// loop over prioritized properties and try to find a match
		foreach ( $properties as $existing_item_property => $item_property ) {

			// compare each existing item with the one at hand, one by one
			foreach ( $existing as $existing_order_item_id => $existing_item ) {

				// get all matches
				if ( isset( $existing_item[ $existing_item_property ] ) &&
						 isset( $item[ $item_property ] ) &&
						 $existing_item[ $existing_item_property ] == $item[ $item_property ] ) {
					$matches[] = $existing_order_item_id;
				}
			}

			//sSome matches were found on this property
			if ( ! empty( $matches ) ) {

				// more than 1 item matches - this means that we cannot reliably
				// determine which existing order item to update, so we must fail
				// TODO: Idea: we could also check if the new items also include multiple
				// items with the same "identifier". If not, then it _should_ be fairly
				// safe to match against the first one, as the others will be deleted
				// anyway.
				if ( count( $matches ) > 1 ) {

					// we're passing WP_Error here to give 3rd parties a chance to adjust
					// the found order_item_id before failure

					switch ( $type ) {

						case 'line_item':
							$message = sprintf( __( '> > Cannot update product "%s" without order item ID, as multiple similar products exist for the order and no direct match could be determined. Skipping merging line items.', 'woocommerce-csv-import-suite' ), $item['title'] );
						break;

						case 'coupon':
							$message = sprintf( __( '> > Cannot update coupon "%s" without order item ID, as multiple similar coupons exist for the order and no direct match could be determined. Skipping merging coupons.', 'woocommerce-csv-import-suite' ), $item['code'] );
						break;

						case 'fee':
							$message = sprintf( __( '> > Cannot update fee "%s" without order item ID, as multiple similar fees exist for the order and no direct match could be determined. Skipping merging fees.', 'woocommerce-csv-import-suite' ), $item['title'] );
						break;

						case 'shipping':
							$message = sprintf( __( '> > Cannot update shipping method "%s" without order item ID, as multiple similar shipping methods exist for the order and no direct match could be determined. Skipping merging shipping methods.', 'woocommerce-csv-import-suite' ), $item['title'] );
						break;

						case 'tax':
							$message = sprintf( __( '> > Cannot update tax rate "%s" without order item ID, as multiple similar tax rates exist for the order and no direct match could be determined. Skipping merging tax rates.', 'woocommerce-csv-import-suite' ), $item['code'] );
						break;

						default:
							$message = __( 'Cannot determine a unique match for order item', 'woocommerce-csv-import-suite' );
						break;
					}

					return new WP_Error( 'wc_csv_import_suite_ambiguous_order_item_match', $message );
				}
				// we've found the "perfect" match
				else {
					return $matches[0];
				}
			}

		}

		return $order_item_id;
	}


	/**
	 * Parse a line item - expects it to be in the default CSV format
	 *
	 * @since 3.0.0
	 * @param array $item Raw item data
	 * @param bool $allow_unknown_products Optional. Defaults to false
	 * @param array $tax_items Parsed tax items
	 * @throws \WC_CSV_Import_Suite_Import_Exception validation, parsing errors
	 * @return array Parsed item data
	 */
	private function parse_line_item( $item, $allow_unknown_products = false, $tax_items ) {

		$product_id = $this->get_array_key_value( $item, 'product_id' );
		$sku        = $this->get_array_key_value( $item, 'sku' );
		$qty        = $this->get_array_key_value( $item, 'quantity' );
		$total      = $this->get_array_key_value( $item, 'total' );

		$product_identifier = $product_id ? $product_id : $sku;

		if ( ! $product_identifier || ! $qty || ! is_numeric( $total ) ) {
			// invalid item
			throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_missing_product_id_sku_quantity_or_total', sprintf( __( 'Missing product ID/SKU, quantity or total for order item on line %s.', 'woocommerce-csv-import-suite' ), $this->line_num ) );
		}

		// match product by product_id
		if ( $product_id && ! $this->is_valid_product( $product_id ) ) {
			$product_id = null; // not a product
		}

		// match product by SKU
		if ( ! $product_id && $sku ) {
			$product_identifier = $sku;
			$product_id         = $this->get_product_id_by_sky( $sku );
		}

		if ( ! $allow_unknown_products && ! $product_id ) {
			throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_unknown_order_item', sprintf( __( 'Unknown order item: %s.', 'woocommerce-csv-import-suite' ), $product_identifier ) );
		}

		// map line item taxes to the correct tax rates
		if ( ! empty( $item['tax_data'] ) ) {
			$item['tax_data'] = $this->map_tax_data_rates( $item['tax_data'], $tax_items );
		}

		$item['product_id'] = $product_id;

		return $item;
	}


	/**
	 * Parse a CSV Export (legacy and default one row per item) formatted line item
	 *
	 * @since 3.0.0
	 * @param array $item
	 * @param bool $allow_unknown_products Optional. Defaults to false
	 * @param string $meta_key_value_separator Optional. Defaults to `=`
	 * @throws \WC_CSV_Import_Suite_Import_Exception validation, parsing errors
	 * @return array
	 */
	private function parse_csv_export_line_item( $item, $allow_unknown_products = false, $meta_key_value_separator = '=' ) {

		$sku   = $this->get_array_key_value( $item, 'item_sku' );
		$qty   = $this->get_array_key_value( $item, 'item_quantity' );
		$total = $this->get_array_key_value( $item, 'item_total' );

		if ( ! $sku || ! $qty || ! is_numeric( $total ) ) {

			// invalid item
			throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_missing_sku_quantity_or_total', sprintf( __( 'Missing SKU, quantity or total for order item on line %s', 'woocommerce-csv-import-suite' ), $this->line_num ) );
		}

		// find by sku
		$product_id = $this->get_product_id_by_sky( $sku );

		// unknown product
		if ( ! $allow_unknown_products && ! $product_id ) {
			/* translators: Placeholders: %s - order item SKU */
			throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_unknown_order_item', sprintf( __( 'Unknown order item: %s.', 'woocommerce-csv-import-suite' ), $sku ) );
		}

		// get any additional item meta
		$item_meta = array();

		if ( isset( $item['item_meta'] ) && ! empty( $item['item_meta'] ) ) {
			$item_meta = $this->split_on_delimiter( $item['item_meta'], ',' );
			$item_meta = $this->split_key_value_pairs( $item_meta, $meta_key_value_separator );
		}

		$item_data = array(
			'product_id' => $product_id,
			'quantity'   => $qty,
			'total'      => $total,
			'meta'       => $item_meta,
		);

		if ( isset( $item['item_name'] ) && ! empty( $item['item_name'] ) ) {
			$item_data['name'] = trim( $item['item_name'] );
		}

		if ( isset( $item['item_tax'] ) ) {
			$item_data['tax_total'] = $item['item_tax'];
		}

		return $item_data;
	}


	/**
	 * Parse shipping items from raw CSV data
	 *
	 * @since 3.0.0
	 * @param array $item Raw order data from CSV file
	 * @param int $shipping_total Optional. Shipping total. Passed by reference.
	 * @param string $format Optional. CSV file format.
	 * @param array $tax_items Parsed tax items form CSV file
	 * @throws \WC_CSV_Import_Suite_Import_Exception validation, parsing errors
	 * @return array Array with 2 values: order shipping methods and shipping_total
	 */
	private function parse_shipping_items( $item = array(), &$shipping_total = null, $format = null, $tax_items = array() ) {

		// shipping methods & costs
		$shipping_items    = array();
		$available_methods = $this->get_available_shipping_methods();

		// shipping items - applies to modern formats
		if ( ! empty( $item['shipping_items'] ) ) {

			if ( $this->is_possibly_json_array( $item['shipping_items'] ) ) {

				try {
					$raw_shipping_items = $this->parse_json( $item['shipping_items'] );
				} catch ( WC_CSV_Import_Suite_Import_Exception $e ) {
					throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_shipping_items_parse_json_error', sprintf( esc_html__( 'Error while parsing shipping items for line %d: %s', 'woocommerce-csv-import-suite' ), $this->line_num, $e->getMessage() ) );
				}

			} else {
				$raw_shipping_items = $this->parse_delimited_string( $item['shipping_items'] );
			}

			foreach( $raw_shipping_items as $raw_shipping_item ) {

				// normalize shipping fields
				foreach ( $this->shipping_item_mapping as $from => $to ) {
					if ( isset( $raw_shipping_item[ $from ] ) ) {

						$raw_shipping_item[ $to ] = $raw_shipping_item[ $from ];
						unset( $raw_shipping_item[ $from ] );
					}
				}

				if ( ! isset( $raw_shipping_item['cost'] ) ) {
					$raw_shipping_item['cost'] = 0;
				}

				$shipping_items[] = $raw_shipping_item;
			}
		}


		// pre WC 2.1 format of a single shipping method, left for backwards
		// compatibility of import files, applies to csv_import && csv_export
		// legacy formats
		else if ( ! empty( $item['shipping_method'] ) ) {

			// CSV export supports multiple comma-separated shipping methods
			$methods = array_map( 'trim', explode( ',', $item['shipping_method'] ) );

			// cost however, is always the same
			$cost = isset( $item['shipping_cost'] ) ? $item['shipping_cost'] : $shipping_total;

			// collect the shipping method id/cost
			// unfortunately, in case of multiple shipping methods, there's no way to
			// get the cost for each shipping method, so we simply use the full cost
			// for the first shipping method, and leave the rest with empty hands
			foreach( $methods as $key => $method ) {

				$shipping_items[] = array(
					'method_id'    => 'csv_import_legacy' == $format ? $method : null, // legacy CSV import format provides method ID
					'method_title' => 'csv_import_legacy' != $format ? $method : null, // Other formats provide method title
					'cost'         => $key < 1 ? $cost : null, // use cost for first method only
				);
			}

		}

		// collect any additional shipping methods, or update details for already
		// collected methods.
		// applies to csv_import_legacy format
		$i = null;
		if ( isset( $item['shipping_method_1'] ) ) {
			$i = 1;
		} elseif( isset( $item['shipping_method_2'] ) ) {
			$i = 2;
		}

		if ( ! is_null( $i ) ) {

			while ( ! empty( $item[ 'shipping_method_' . $i ] ) ) {

				$shipping_items[ $i - 1 ] = array(
					'method_id'    => $item[ 'shipping_method_' . $i ],
					'method_title' => null,
					'cost'         => isset( $item[ 'shipping_cost_' . $i ] ) ? $item[ 'shipping_cost_' . $i ] : null,
				);

				$i++;
			}
		}

		// if the order shipping total wasn't set, calculate it
		if ( null === $shipping_total ) {

			$shipping_total = 0;

			foreach ( $shipping_items as $shipping_item ) {
				$shipping_total += abs( $shipping_item['cost'] );
			}

		} elseif ( null !== $shipping_total && 1 == count( $shipping_items ) && is_null( $shipping_items[0]['cost'] ) ) {
			// special case: if there was a total order shipping but no cost for the single shipping method, use the total shipping for the order shipping line item
			$shipping_items[0]['cost'] = $shipping_total;
		}


		// match shipping items to known, available shipping methods
		foreach ( $shipping_items as $key => $shipping_item ) {

			// look up shipping method by id or title
			$shipping_method = isset( $shipping_item['method_id'] ) ? $this->get_array_key_value( $available_methods, $shipping_item['method_id'] ) : null;

			if ( ! $shipping_method && ! empty( $shipping_item['method_title'] ) ) {

				// try by title
				foreach ( $available_methods as $method ) {

					if ( 0 === strcasecmp( $method->title, $shipping_item['method_title'] ) ) {
						$shipping_method = $method;
						break; // go with the first one we find
					}
				}
			}

			// known shipping method found
			if ( $shipping_method ) {
				$shipping_items[ $key ]['method_id']    = $shipping_method->id;
				$shipping_items[ $key ]['method_title'] = $shipping_method->title;
			}

			// map shipping taxes to the correct tax rates
			if ( ! empty( $shipping_item['taxes'] ) ) {
				$shipping_items[ $key ]['taxes'] = $this->map_tax_data_rates( $shipping_item['taxes'], $tax_items );
			}
		}

		return $shipping_items;
	}


	/**
	 * Parse shipping items from raw CSV data
	 *
	 * @since 3.0.0
	 * @param array $item Raw order data from CSV file
	 * @param int $tax_total Optional. Tax total
	 * @param int $shipping_tax_total Optional. Shipping tax total
	 * @throws \WC_CSV_Import_Suite_Import_Exception validation, parsing errors
	 * @return array Array with 3 items: tax items, tax total and shipping tax total
	 */
	private function parse_tax_items( $item, &$tax_total = null, &$shipping_tax_total = null ) {

		$tax_items     = array();
		$raw_tax_items = array();
		$tax_rates     = $this->get_tax_rates();

		// CSV import legacy tax item format which supports multiple tax items in
		// numbered columns containing a pipe-delimited, colon-labeled format
		if ( ! empty( $item['tax_item_1'] ) || ! empty( $item['tax_item'] ) ) {

			// get the first tax item
			$_tax_item = ! empty( $item['tax_item_1'] ) ? $item['tax_item_1'] : $item['tax_item'];

			$i = 1;

			while ( $_tax_item ) {

				// turn "label: Tax | tax_amount: 10" into an associative array
				$tax_item_data = $this->split_on_delimiter( $_tax_item, '|' );
				$tax_item_data = $this->split_key_value_pairs( $tax_item_data, ':' );

				$raw_tax_items[] = $tax_item_data;

				// get the next tax item (if any)
				$i++;
				$_tax_item = isset( $item[ 'tax_item_' . $i ] ) ? $item[ 'tax_item_' . $i ] : null;
			}
		}

		// default format
		else if ( ! empty( $item['tax_items'] ) ) {

			if ( $this->is_possibly_json_array( $item['tax_items'] ) ) {

				try {
					$raw_tax_items = $this->parse_json( $item['tax_items'] );
				} catch ( WC_CSV_Import_Suite_Import_Exception $e ) {
					throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_tax_items_parse_json_error', sprintf( esc_html__( 'Error while parsing taxes for line %d: %s', 'woocommerce-csv-import-suite' ), $this->line_num, $e->getMessage() ) );
				}

			} else {
				$raw_tax_items = $this->parse_delimited_string( $item['tax_items'] );
			}
		}

		// parse extracted (but still raw & uncooked) tax items
		if ( ! empty( $raw_tax_items ) ) {

			$tax_amount_sum = $shipping_tax_amount_sum = 0;

			foreach( $raw_tax_items as $raw_tax_item ) {

				if ( $tax_item = $this->parse_tax_item( $raw_tax_item ) ) {

					$tax_items[] = $tax_item;

					// sum up the order totals, in case it wasn't part of the import
					$tax_amount_sum          += $tax_item['tax_amount'];
					$shipping_tax_amount_sum += $tax_item['shipping_tax_amount'];
				}
			}

			if ( ! is_numeric( $tax_total ) ) {
				$tax_total = $tax_amount_sum;
			}

			if ( ! is_numeric( $shipping_tax_total ) ) {
				$shipping_tax_total = $shipping_tax_amount_sum;
			}
		}

		// default tax and shipping totals to zero if not set
		if ( ! is_numeric( $tax_total ) ) {
			$tax_total = 0;
		}
		if ( ! is_numeric( $shipping_tax_total ) ) {
			$shipping_tax_total = 0;
		}


		// no tax items specified, so create a default one using the tax totals
		if ( 0 == count( $tax_items ) ) {

			$tax_items[] = array(
				'code'                => '',
				'rate_id'             => 0,
				'label'               => esc_html__( 'Tax', 'woocommerce-csv-import-suite' ),
				'compound'            => '',
				'tax_amount'          => $tax_total,
				'shipping_tax_amount' => $shipping_tax_total,
			);
		}

		return $tax_items;
	}


	/**
	 * Parse a single tax item
	 *
	 * @since 3.0.0
	 * @param array $tax_item_data Raw tax item data
	 * @return array|null Parsed tax item data or null
	 */
	private function parse_tax_item( $tax_item_data ) {

		$tax_rates = $this->get_tax_rates();

		// normalize tax fields
		foreach ( $this->tax_item_mapping as $from => $to ) {
			if ( isset( $tax_item_data[ $from ] ) ) {

				$tax_item_data[ $to ] = $tax_item_data[ $from ];
				unset( $tax_item_data[ $from ] );
			}
		}

		// if neither tax or shipping amount provided, bail out
		// TODO: should we throw here instead? {IT 2016-05-30}
		if ( ! isset( $tax_item_data['tax_amount'] ) && ! isset( $tax_item_data['shipping_tax_amount'] ) ) {
			return;
		}

		// default rate id to 0 if not set
		if ( ! isset( $tax_item_data['rate_id'] ) ) {
			$tax_item_data['rate_id'] = 0;
		}

		// keep a reference to the rate_id provided in the CSV file
		$original_rate_id = $tax_item_data['rate_id'];

		// try and look up rate id by code
		// Code is made up of COUNTRY-STATE-NAME-Priority. E.g GB-VAT-1, US-AL-TAX-1.
		// We do this instead of relying blindly on rate_id because tax code is more
		// portable than rate_id across stores
		if ( isset( $tax_item_data['code'] ) ) {

			foreach ( $tax_rates as $tax_rate ) {

				if ( WC_Tax::get_rate_code( $tax_rate->tax_rate_id ) == $tax_item_data['code'] ) {

					// found the tax by code
					$tax_item_data['rate_id'] = $tax_rate->tax_rate_id;
					$tax_item_data['label']   = $tax_rate->tax_rate_name;
					break;
				}
			}
		}

		// try and look up rate id by label if needed
		if ( ! $tax_item_data['rate_id'] && isset( $tax_item_data['label'] ) && $tax_item_data['label'] ) {
			foreach ( $tax_rates as $tax_rate ) {

				if ( 0 === strcasecmp( $tax_rate->tax_rate_name, $tax_item_data['label'] ) ) {

					// found the tax by label
					$tax_item_data['rate_id'] = $tax_rate->tax_rate_id;
					break;
				}
			}
		}

		// check for a rate being specified which does not exist, and clear it out (technically an error?)
		if ( $tax_item_data['rate_id'] && ! isset( $tax_rates[ $tax_item_data['rate_id'] ] ) ) {
			$tax_item_data['rate_id'] = 0;
		}

		// fetch tax rate code
		if ( $tax_item_data['rate_id'] && ( ! isset( $tax_item_data['code'] ) || $tax_item_data['code'] ) ) {
			$tax_item_data['code'] = WC_Tax::get_rate_code( $tax_item_data['rate_id'] );
		} else {
			$tax_item_data['code'] = '';
		}

		// default label of 'Tax' if not provided
		if ( ! isset( $tax_item_data['label'] ) || ! $tax_item_data['label'] ) {
			$tax_item_data['label'] = esc_html__( 'Tax', 'woocommerce-csv-import-suite' );
		}

		// default tax amounts to 0 if not set
		if ( ! isset( $tax_item_data['tax_amount'] ) ) {
			$tax_item_data['tax_amount'] = 0;
		}

		if ( ! isset( $tax_item_data['shipping_tax_amount'] ) ) {
			$tax_item_data['shipping_tax_amount'] = 0;
		}

		// handle compound flag by using the defined tax rate value (if any)
		if ( ! isset( $tax_item_data['compound'] ) ) {
			$tax_item_data['compound'] = '';

			if ( $tax_item_data['rate_id'] ) {
				$tax_item_data['compound'] = $tax_rates[ $tax_item_data['rate_id'] ]->tax_rate_compound;
			}
		}

		// store a reference of the original rate_id, so that we can match refund
		// taxes to the correct tax rate
		if ( $tax_item_data['rate_id'] && $tax_item_data['rate_id'] != $original_rate_id ) {
			$tax_item_data['_original_rate_id'] = $original_rate_id;
		}

		return $tax_item_data;
	}


	/**
	 * Parse fee items from raw CSV data
	 *
	 * @since 3.0.0
	 * @param array $item Raw order data from CSV file
	 * @param int $fee_total Optional. Fee total
	 * @param int $fee_tax_total Optional. Fee tax total
	 * @param array $tax_items Parsed tax items
	 * @throws \WC_CSV_Import_Suite_Import_Exception validation, parsing errors
	 * @return array Array with 3 items: fee items, fee total and fee tax total
	 */
	private function parse_fee_items( $item, &$fee_total = null, &$fee_tax_total = null, $tax_items ) {

		$fee_items = array();

		// `fee_items` is supported by the default format(s)
		if ( ! empty( $item['fee_items'] ) ) {

			$fee_amount_sum = $fee_tax_amount_sum = 0;

			if ( $this->is_possibly_json_array( $item['fee_items'] ) ) {

				try {
					$raw_fee_items = $this->parse_json( $item['fee_items'] );
				} catch ( WC_CSV_Import_Suite_Import_Exception $e ) {
					throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_fee_items_parse_json_error', sprintf( esc_html__( 'Error while parsing fees for line %d: %s', 'woocommerce-csv-import-suite' ), $this->line_num, $e->getMessage() ) );
				}

			} else {
				$raw_fee_items = $this->parse_delimited_string( $item['fee_items'] );
			}

			// parse raw fee items into something edible
			foreach ( $raw_fee_items as $raw_fee_item ) {

				// normalize fee fields
				foreach ( $this->fee_item_mapping as $from => $to ) {
					if ( isset( $raw_fee_item[ $from ] ) ) {

						$raw_fee_item[ $to ] = $raw_fee_item[ $from ];
						unset( $raw_fee_item[ $from ] );
					}
				}

				// make sure total & tax have proper values
				$fee_item_total = $this->get_array_key_value( $raw_fee_item, 'total', 0 );
				$fee_item_tax   = $this->get_array_key_value( $raw_fee_item, 'total_tax', 0 );

				if ( ! is_numeric( $fee_item_total ) ) {
					$fee_item_total = 0;
				}

				if ( ! is_numeric( $fee_item_tax ) ) {
					$fee_item_tax = 0;
				}

				// TODO: should we require the fee name/title? WC-API seems to do so.
				$fee_item = array(
					'order_item_id' => $this->get_array_key_value( $raw_fee_item, 'order_item_id' ),
					'title'         => $this->get_array_key_value( $raw_fee_item, 'name', esc_html__( 'Fee', 'woocommerce-csv-import-suite' ) ),
					'total'         => $fee_item_total,
					'total_tax'     => $fee_item_tax,
					'taxable'       => $this->get_array_key_value( $raw_fee_item, 'taxable', !!$fee_item_tax ),
					'tax_class'     => $this->get_array_key_value( $raw_fee_item, 'tax_class', '' ),
					'tax_data'      => $this->get_array_key_value( $raw_fee_item, 'tax_data' ),
				);

				if ( ! empty( $fee_item['tax_data'] ) ) {
					$fee_item['tax_data'] = $this->map_tax_data_rates( $fee_item['tax_data'], $tax_items );
				}

				$fee_items[] = $fee_item;

				// sum up the fee totals, in case it wasn't part of the import
				$fee_amount_sum     += $fee_item_total;
				$fee_tax_amount_sum += $fee_item_tax;
			}


			// set fee and tax totals if they were not provided
			if ( ! is_numeric( $fee_total ) ) {
				$fee_total = $fee_amount_sum;
			}

			if ( ! is_numeric( $fee_tax_total ) ) {
				$fee_tax_total = $fee_tax_amount_sum;
			}
		}

		// default fee and tax totals to zero if not set
		if ( ! is_numeric( $fee_total ) ) {
			$fee_total = 0;
		}

		if ( ! is_numeric( $fee_tax_total ) ) {
			$fee_tax_total = 0;
		}

		// no fee items specified, but fee total is available, so create a default
		// one using the fee totals
		if ( empty( $fee_items ) && $fee_total ) {

			$fee_items[] = array(
				'title'          => esc_html__( 'Fee', 'woocommerce-csv-import-suite' ),
				'total'          => $fee_total,
				'total_tax'      => $fee_tax_total,
				'taxable'        => $fee_tax_total != 0,
				'tax_class'      => '',
			);
		}

		return $fee_items;
	}


	/**
	 * Parse coupons from raw CSV data
	 *
	 * @since 3.0.0
	 * @param array $item Raw order data from CSV file
	 * @throws \WC_CSV_Import_Suite_Import_Exception validation, parsing errors
	 * @return array Array of coupons, if any
	 */
	private function parse_coupons( $item ) {

		$coupons = array();

		if ( isset( $item['coupons'] ) && ! empty( $item['coupons'] ) ) {

			if ( $this->is_possibly_json_array( $item['coupons'] ) ) {

				try {
					$_coupons = $this->parse_json( $item['coupons'] );
				} catch ( WC_CSV_Import_Suite_Import_Exception $e ) {
					throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_coupons_parse_json_error', sprintf( esc_html__( 'Error while parsing coupons for line %d: %s', 'woocommerce-csv-import-suite' ), $this->line_num, $e->getMessage() ) );
				}

			} else {
				$_coupons = $this->parse_delimited_string( $item['coupons'] );
			}

			foreach ( $_coupons as $_coupon_data ) {

				if ( ! $this->get_array_key_value( $_coupon_data, 'code' ) ) {
					throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_missing_coupon_code', sprintf( esc_html__( 'Missing coupon code on line %d', 'woocommerce-csv-import-suite' ), $this->line_num ) );
				}

				$coupons[] = $_coupon_data;
			}
		}

		return $coupons;
	}


	/**
	 * Parse refunds
	 *
	 * @since 3.0.0
	 * @param int $item Raw order data
	 * @param array $order_data Parsed order data, passed by reference
	 * @throws \WC_CSV_Import_Suite_Import_Exception validation, parsing errors
	 * @return array|null
	 */
	private function parse_refunds( $item, &$order_data ) {

		$refunds = null;

		// if refunds data is readily available, use that
		if ( isset( $item['refunds'] ) ) {

			if ( $this->is_possibly_json_array( $item['refunds'] ) ) {

				try {
					$refunds = $this->parse_json( $item['refunds'] );
				} catch( WC_CSV_Import_Suite_Import_Exception $e ) {
					throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_refunds_parse_json_error', sprintf( esc_html__( 'Error while parsing refunds for line %d: %s', 'woocommerce-csv-import-suite' ), $this->line_num, $e->getMessage() ) );
				}

				if ( ! empty( $refunds ) ) {

					foreach ( $refunds as $key => $refund ) {

						if ( ! empty( $refund['line_items'] ) ) {

							foreach ( $refund['line_items'] as $item_key => $refunded_item ) {

								// generate unique id for the refunded item, so that we can later
								// map the refunded items to the order items
								$refunded_item_temp_id = uniqid( 'refunded_item_' );

								// store temp id on refunded line item
								$refunds[ $key ]['line_items'][ $item_key ]['refunded_item_temp_id'] = $refunded_item_temp_id;

								// find the refunded line item, and store the temp id there as well
								foreach ( $this->line_types as $line_type ) {
									foreach ( $order_data[ $line_type ] as $line_key => $order_item ) {

										if ( ! isset( $order_item['order_item_id'] ) ) {
											continue;
										}

										if ( $order_item['order_item_id'] == $refunded_item['refunded_item_id'] ) {
											$order_data[ $line_type ][ $line_key ]['refunded_item_temp_id'] = $refunded_item_temp_id;
										}
									}
								}

								// map taxes to the correct tax rates
								if ( ! empty( $refunded_item['refund_tax'] ) ) {
									$refunds[ $key ]['line_items'][ $item_key ]['refund_tax'] = $this->map_tax_data_rates( $refunded_item['refund_tax'], $order_data['tax_lines'] );
								}
							}
						}
					}
				}
			}

			else {

				$refunds = $this->parse_delimited_string( $item['refunds'] );

				if ( ! empty( $refunds ) ) {

					// map refunded line items to teh first refund, since we have no way
					// of knowing any better
					$refunds[0]['line_items'] = $this->get_refunded_items( $order_data );
				}
			}

		}


		// If no refunds were provided, extract data about them from other fields
		if ( ! $refunds ) {

			$refunded_total       = isset( $item['refunded_total'] ) && is_numeric( $item['refunded_total'] ) ? $item['refunded_total'] : null;
			$refunded_items       = array();
			$refunded_items_total = 0;

			$refunded_items = $this->get_refunded_items( $order_data );

			// if total wasn't provided, use the calculated value
			if ( is_null( $refunded_total ) && ! empty( $refunded_items ) ) {

				// add up item refund amounts
				foreach ( $refunded_items as $refunded_item ) {
					$refunded_items_total += $refunded_item['refund_total'];
				}

				if ( $refunded_items_total ) {
					$refunded_total = $refunded_items_total;
				}
			}

			// no total and no refunded items... no refunds!
			if ( ! $refunded_total && empty( $refunded_items ) ) {
				return null;
			}

			$refunds = array(
				array(
					'amount'     => $refunded_total,
					'reason'     => null,
					'line_items' => $refunded_items,
					'date'       => null,
				),
			);
		}

		return $refunds;
	}


	/**
	 * Get refunded items from order data
	 *
	 * @since 3.0.3
	 * @param array $order_data Parsed order data, passed by reference
	 * @return array
	 */
	private function get_refunded_items( &$order_data ) {

		$refunded_items = array();

		foreach ( $this->line_types as $line_type ) {

			if ( empty( $order_data[ $line_type ] ) ) {
				continue;
			}

			foreach ( $order_data[ $line_type ] as $line_key => $order_item ) {

				// this item is not refunded
				if ( empty( $order_item['refunded'] ) || $order_item['refunded'] < 0 ) {
					continue;
				}

				// generate unique id for the refunded item, so that we can later
				// map the refunded items to the order items
				$refunded_item_temp_id = uniqid( 'refunded_item_' );

				$order_data[ $line_type ][ $line_key ]['refunded_item_temp_id'] = $refunded_item_temp_id;

				$refunded_item = array(
					'refunded_item_temp_id' => $refunded_item_temp_id,
					'refund_total'          => $order_item['refunded'],
				);

				if ( isset( $order_item['refunded_qty'] ) ) {
					$refunded_item['qty'] = $order_item['refunded_qty'];
				}

				if ( isset( $order_item['refunded_tax'] ) ) {
					$refunded_item['refund_tax'] = $order_item['refunded_tax'];
				}

				$refunded_items[] = $refunded_item;
			}
		}

		return $refunded_items;
	}


	/**
	 * Map tax data rate IDs to known tax rate IDs
	 *
	 * @since 3.0.0
	 * @param array $tax_data
	 * @param array $tax_items Parsed tax items
	 * @return array
	 */
	private function map_tax_data_rates( $tax_data, $tax_items ) {

		// sanity check
		if ( ! is_array( $tax_data ) || empty( $tax_data ) ) {
			return array();
		}

		foreach ( $tax_data as $rate_id => $total ) {

			// handle detailed total/subtotal tax_data. in this case $rate_id will be
			// a string key, either total or subtotal, and actual tax data will be it's value
			if ( ! is_numeric( $rate_id ) && is_array( $total ) ) {
				$tax_data[ $rate_id ] = $this->map_tax_data_rates( $total, $tax_items );
			}

			// map each rate_id=>total pair ot a known, previously mapped tax rate
			else {
				foreach ( $tax_items as $tax_item ) {

					// found a match on _original_rate_id
					if ( isset( $tax_item['_original_rate_id'] ) && $tax_item['_original_rate_id'] == $rate_id ) {

						// remove taxes for original rate_id
						unset( $tax_data[ $rate_id ] );

						// and assign them to the found rate
						$tax_data[ $tax_item['rate_id'] ] = $total;
					}
				}
			}
		}

		return $tax_data;
	}


	/**
	 * Process an order
	 *
	 * @since 3.0.0
	 * @param mixed $data Parsed order data, ready for processing, compatible with
	 *                    wc_create_order/wc_update_order
	 * @param array $options Optional. Options
	 * @param array $raw_headers Optional. Raw headers
	 * @return int|null
	 */
	protected function process_item( $data, $options = array(), $raw_headers = array() ) {

		// if recalculate_totals is not provided, default to false
		$options = wp_parse_args( $options, array( 'recalculate_totals' => false ) );
		$merging = $options['merge'] && isset( $data['id'] ) && $data['id'];
		$dry_run = isset( $options['dry_run'] ) && $options['dry_run'];

		wc_csv_import_suite()->log( __( '> Processing order', 'woocommerce-csv-import-suite' ) );

		$order_identifier = $this->get_item_identifier( $data );

		if ( ! $dry_run ) {
			wc_transaction_query( 'start' );
		}

		try {

			if ( $merging ) {

				wc_csv_import_suite()->log( sprintf( __( '> Merging order %s.', 'woocommerce-csv-import-suite' ), $order_identifier ) );

				if ( ! $dry_run ) {
					$order_id = $this->update_order( $data['id'], $data, $options );
				}

			} else {

				// insert customer
				wc_csv_import_suite()->log( sprintf( __( '> Inserting order %s', 'woocommerce-csv-import-suite' ), esc_html( $order_identifier ) ) );

				if ( ! $dry_run ) {
					$order_id = $this->create_order( $data, $options );
				}
			}


			// import failed
			if ( ! $dry_run && is_wp_error( $order_id ) ) {
				$this->add_import_result( 'failed', $order_id->get_error_message() );
				return null;
			}

			// TODO: is that OK to log and return as order_id in case of dry run?
			if ( $dry_run ) {
				$order_id = $merging ? $data['id'] : 9999;
			}

			if ( ! $dry_run ) {
				wc_transaction_query( 'commit' );
			}

		} catch ( WC_CSV_Import_Suite_Import_Exception $e ) {

			if ( ! $dry_run ) {
				wc_transaction_query( 'rollback' );
			}

			$this->add_import_result( 'failed', $e->getMessage() );
			return null;
		}

		// no order identifier provided in CSV, use the order ID
		if ( ! $order_identifier ) {
			$order_identifier = $order_id;
		}

		if ( $merging ) {
			wc_csv_import_suite()->log( sprintf( __( '> Finished merging order %s.', 'woocommerce-csv-import-suite' ), $order_identifier ) );
			$this->add_import_result( 'merged' );
		} else {
			wc_csv_import_suite()->log( sprintf( __( '> Finished importing order %s.', 'woocommerce-csv-import-suite' ), $order_identifier ) );
			$this->add_import_result( 'inserted' );
		}

		return $order_id;
	}


	/**
	 * Create an order
	 *
	 * Based on WC_API_Orders::create_order
	 *
	 * @since 3.0.0
	 * @param array $data
	 * @param array $options
	 * @return int|WP_Error
	 */
	private function create_order( $data, $options ) {

		try {

			/**
			 * Filter new order data from CSV
			 *
			 * @since 3.0.0
			 * @param array $data
			 * @param array $options
			 * @param object $this
			 */
			$data = apply_filters( 'wc_csv_import_suite_import_order_data', $data, $options, $this );

			// default order args, note that status is checked for validity in wc_create_order()
			$default_order_args = array(
				'status'        => $data['status'],
				'customer_note' => isset( $data['note'] ) ? $data['note'] : '',
				'customer_id'   => $data['customer_id'],
			);

			// create the pending order
			$order = $this->create_base_order( $default_order_args, $data );

			if ( is_wp_error( $order ) ) {

				$messages = $this->implode_wp_error_messages( $order );

				/* translators: Placeholders: %1$s - order identifier, %2$s - error message */
				throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_cannot_create_order', sprintf( __( 'Failed to insert order %1$s: %2$s;', 'woocommerce-csv-import-suite' ), esc_html( $post['order_number_formatted'] ), esc_html( $messages ) ) );
			}

			// add order notes - only when not merging, as order notes
			// are not keyed
			if ( ! empty( $data['order_notes'] ) ) {
				foreach ( $data['order_notes'] as $order_note ) {
					$order->add_order_note( $order_note );
				}
			}

			// do our best to provide some custom order number functionality while
			// also allowing 3rd party plugins to provide their own custom order
			// number facilities
			if ( ! empty( $data['order_number_formatted'] ) ) {

				do_action( 'woocommerce_set_order_number', $order, $data['order_number'], $data['order_number_formatted'] );
				$order->add_order_note( sprintf( __( 'Original order #%s', 'woocommerce-csv-import-suite' ), $data['order_number_formatted'] ) );

				// get the order so we can display the correct order number
				$order = wc_get_order( $order->id );
			}

			// update order data, such as meta and items
			$this->update_order_data( $order, $data, $options );

			// calculate totals and set them
			if ( $options['recalculate_totals'] ) {
				$order->calculate_taxes();
				$order->calculate_totals();
			}

			// record the product sales - only recorded when creating an order,
			// following WC core.
			$order->record_product_sales();

			/**
			 * Triggered after an order has been created via CSV import
			 *
			 * @since 3.0.0
			 * @param int $id Order ID
			 * @param array $data Data from CSV
			 * @param array $options Import options
			 */
			do_action( 'wc_csv_import_suite_create_order', $order->id, $data, $options );

		} catch ( WC_CSV_Import_Suite_Import_Exception $e ) {
			return new WP_Error( $e->getErrorCode(), $e->getMessage() );
		}
	}


	/**
	 * Update an order
	 *
	 * Based on WC_API_Orders::update_order
	 *
	 * @since 3.0.0
	 * @param int $id
	 * @param array $data
	 * @param array $options
	 * @return int|WP_Error
	 */
	private function update_order( $id, $data, $options ) {

		try {

			$order      = wc_get_order( $id );
			$order_args = array( 'order_id' => $order->id );

			// customer note
			if ( isset( $data['note'] ) ) {
				$order_args['customer_note'] = $data['note'];
			}

			// update the order post to set customer note/modified date
			$order = wc_update_order( $order_args );

			if ( is_wp_error( $order ) ) {

				$messages         = $this->implode_wp_error_messages( $order );
				$order_identifier = $this->get_item_identifier( $data );

				/* translators: Placeholders: %1$s - order identifier, %2$s - error message(s) */
				throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_cannot_update_order', sprintf( __( 'Failed to update order %1$s: %2$s;', 'woocommerce-csv-import-suite' ), esc_html( $order_identifier ), esc_html( $messages ) ) );
			}

			// order status
			if ( ! empty( $data['status'] ) ) {
				$order->update_status( $data['status'], isset( $data['status_note'] ) ? $data['status_note'] : '' );
			}

			// customer ID
			if ( isset( $data['customer_id'] ) && $data['customer_id'] != $order->get_user_id() ) {
				update_post_meta( $order->id, '_customer_user', $data['customer_id'] );
			}

			// update order data, such as meta and items
			$this->update_order_data( $order, $data, $options );

			// calculate totals and set them
			if ( $options['recalculate_totals'] ) {
				$order->calculate_taxes();
				$order->calculate_totals();
			}

			/**
			 * Triggered after an order has been updated via CSV import
			 *
			 * @since 3.0.0
			 * @param int $id Order ID
			 * @param array $data Data from CSV
			 * @param array $options Import options
			 */
			do_action( 'wc_csv_import_suite_update_order', $order->id, $data, $options );

		} catch ( WC_CSV_Import_Suite_Import_Exception $e ) {
			return new WP_Error( $e->getErrorCode(), $e->getMessage() );
		}
	}


	/**
	 * Update order data
	 *
	 * Based on WC_API_Customers::update_customer_data()
	 *
	 * @since 3.0.0
	 * @param \WC_Order $order
	 * @param array $data
	 * @param array $options
	 */
	private function update_order_data( WC_Order $order, $data, $options ) {

		if ( isset( $data['date'] ) && $data['date'] ) {
			wp_update_post( array(
				'ID'        => $order->id,
				'post_date' => date( 'Y-m-d H:i:s', $data['date'] )
			));
		}


		$merging = $options['merge'] && isset( $data['id'] ) && $data['id'];

		$this->process_terms( $order->id, $data['terms'] );

		// set order addresses
		$order->set_address( $data['billing_address'],  'billing' );
		$order->set_address( $data['shipping_address'], 'shipping' );

		// clear any previously set refunded order item ids
		$this->refunded_item_order_ids = array();

		// set order lines
		foreach ( $this->line_types as $line_type => $line ) {
			// don't set lines if they're empty. This ensures partial updates/merges
			// are supported and won't wipe out order lines
			if ( ! empty( $data[ $line ] ) && is_array( $data[ $line ] ) ) {
				$this->process_items( $order, $data[ $line ], $line_type, $merging );
			}
		}

		// set order currency
		if ( isset( $data['currency'] ) ) {
			update_post_meta( $order->id, '_order_currency', $data['currency'] );
		}

		// grant downloadable product permissions
		if ( isset( $data['download_permissions_granted'] ) && $data['download_permissions_granted'] ) {
			wc_downloadable_product_permissions( $order->id );
		}

		// set order meta
		if ( isset( $data['order_meta'] ) && is_array( $data['order_meta'] ) ) {
			$this->set_order_meta( $order->id, $data['order_meta'] );
		}

		// set the paying customer flag on the user meta if applicable
		$paid_statuses = array( 'processing', 'completed', 'refunded' );

		if ( $data['customer_id'] && in_array( $data['status'], $paid_statuses ) ) {
			update_user_meta( $data['customer_id'], "paying_customer", 1 );
		}

		// process refunds
		if ( ! empty( $data['refunds'] ) ) {

			// remove previous refunds
			foreach ( $order->get_refunds() as $refund ) {
				wc_delete_shop_order_transients( $refund->id );
				wp_delete_post( $refund->id, true );
			}

			foreach ( $data['refunds'] as $refund_data ) {

				// try mapping temp refunded item ids to real order item ids
				if ( ! empty( $refund_data['line_items'] ) ) {
					foreach ( $refund_data['line_items'] as $key => $refunded_item ) {

						if ( isset( $refunded_item['refunded_item_temp_id'] ) ) {

							$temp_id = $refunded_item['refunded_item_temp_id'];

							// get the real order item id for this refunded itme
							$order_item_id = $this->get_array_key_value( $this->refunded_item_order_ids, $temp_id );

							if ( $order_item_id ) {
								$refund_data['line_items'][ $order_item_id ] = $refunded_item;
								unset( $refund_data['line_items'][ $key ] );
							}
						}
					}
				}

				wc_create_refund( array(
					'order_id'   => $order->id,
					'amount'     => $refund_data['amount'],
					'reason'     => $refund_data['reason'],
					'line_items' => $refund_data['line_items'],
					'date'       => $refund_data['date'],
				) );
			}
		}

		wc_delete_shop_order_transients( $order->id );

		/**
		 * Triggered after order data has been updated via CSV
		 *
		 * This will be triggered for both new and updated orders
		 *
		 * @since 3.0.0
		 * @param int $id Order ID
		 * @param array $data Order data
		 * @param array $options Import options
		 */
		do_action( 'wc_csv_import_suite_update_order_data', $order->id, $data, $options );
	}


	/**
	 * Creates new WC_Order.
	 *
	 * @since 3.0.0
	 * @param $args array
	 * @return WC_Order
	 */
	private function create_base_order( $args, $data ) {
		return wc_create_order( $args );
	}


	/**
	 * Helper method to add/update order meta
	 *
	 * @since 3.0.0
	 * @param int $order_id valid order ID
	 * @param array $order_meta order meta in array( 'meta_key' => 'meta_value' ) format
	 */
	private function set_order_meta( $order_id, $order_meta ) {

		foreach ( $order_meta as $meta_key => $meta_value ) {

			if ( is_string( $meta_key ) ) {
				update_post_meta( $order_id, $meta_key, $meta_value );
			}
		}
	}


	/**
	 * Process items for an order
	 *
	 * @since 3.0.0
	 * @param \WC_Order $order The order object the items should be attached to
	 * @param array items Parsed items from CSV.
	 * @param string $type Optional. Line items type. Defaults to 'line_item'.
	 * @param bool $merging Optional. Defaults to false
	 */
	private function process_items( WC_Order $order, $items = array(), $type = 'line_item', $merging = false ) {

		if ( empty( $items ) ) {
			return;
		}

		if ( $merging ) {
			$existing = $order->get_items( $type );
			$updated  = array();
		}

		foreach ( $items as $item ) {

			$order_item_id = null;

			if ( $merging ) {
				$order_item_id = isset( $item['order_item_id'] ) && $item['order_item_id'] ? $item['order_item_id'] : null;
			}

			// update existing item
			if ( $merging && $order_item_id ) {

				$this->update_order_item( $order, $order_item_id, $item, $type );

				// Mark item as updated - even if it failed, as otherwise it would be
				// deleted from the order below
				$updated[] = $order_item_id;
			}

			// insert as new
			else {
				$order_item_id = $this->add_order_item( $order, $item, $type );
			}

			// if create/update was successful, upodate order item meta as well
			if ( $order_item_id ) {
				$this->update_order_item_meta( $order_item_id, $item, $type );

				// store a mapping/reference to the real order item id
				if ( isset( $item['refunded_item_temp_id'] ) ) {
					$this->refunded_item_order_ids[ $item['refunded_item_temp_id'] ] = $order_item_id;
				}
			}
		}

		// delete existing items that were not present in the CSV when merging
		if ( $merging ) {
			$this->delete_removed_items( $existing, $updated );
		}
	}


	/**
	 * Add an item to the provided order
	 *
	 * @since 3.0.0
	 * @param \WC_Order $order
	 * @param array $item Parsed item data from CSV
	 * @param string $type Line item type
	 * @return int|false ID of the inserted order item, false on failure
	 */
	private function add_order_item( WC_Order $order, $item, $type ) {

		$result = false;

		switch ( $type ) {

			case 'line_item':
				$product = $this->get_product_for_item( $item );
				$args    = $this->prepare_product_args( $item );

				$result = $order->add_product( $product, $args['qty'], $args );

				if ( ! $result ) {
					wc_csv_import_suite()->log( sprintf( __( '> > Warning: cannot add order item "%s".', 'woocommerce-csv-import-suite' ), esc_html( $identifier ) ) );
				}
			break;

			case 'shipping':
				$args = array(
					'order_item_name' => $item['method_title'],
					'order_item_type' => 'shipping',
				);

				// we're using wc_add_order_item instead of $order->add_shipping because
				// we do not want the order total to be recalculated
				$result = wc_add_order_item( $order->id, $args );

				if ( ! $result ) {
					wc_csv_import_suite()->log( sprintf( __( '> > Warning: cannot add shipping method "%s".', 'woocommerce-csv-import-suite' ), esc_html( $item['title'] ) ) );
				}
			break;

			case 'tax':

				$args = array(
					'order_item_name' => $item['code'],
					'order_item_type' => 'tax',
				);

				$result = wc_add_order_item( $order->id, $args );

				if ( ! $result ) {
					wc_csv_import_suite()->log( sprintf( __( '> > Warning: cannot add tax "%s".', 'woocommerce-csv-import-suite' ), esc_html( $item['label'] ) ) );
				}
			break;

			case 'coupon':
				$result = $order->add_coupon( $item['code'], $item['amount'] );

				if ( ! $result ) {
					wc_csv_import_suite()->log( sprintf( __( '> > Warning: cannot add coupon "%s".', 'woocommerce-csv-import-suite' ), esc_html( $item['code'] ) ) );
				}
			break;

			case 'fee':
				$order_fee            = new stdClass();
				$order_fee->id        = sanitize_title( $item['title'] );
				$order_fee->name      = $item['title'];
				$order_fee->amount    = isset( $item['total'] ) ? floatval( $item['total'] ) : 0;
				$order_fee->taxable   = false;
				$order_fee->tax       = 0;
				$order_fee->tax_data  = array();
				$order_fee->tax_class = '';

				// if taxable, tax class and total are required
				if ( isset( $item['taxable'] ) && $item['taxable'] ) {

					$order_fee->taxable   = true;
					$order_fee->tax_class = $item['tax_class'];

					if ( isset( $item['total_tax'] ) ) {
						$order_fee->tax = isset( $item['total_tax'] ) ? wc_format_refund_total( $item['total_tax'] ) : 0;
					}

					if ( isset( $item['tax_data'] ) ) {
						$tax_data            = isset( $item['tax_data']['total'] ) ? $item['tax_data']['total'] : $item['tax_data'];
						$order_fee->tax      = wc_format_refund_total( array_sum( $tax_data ) );
						$order_fee->tax_data = array_map( 'wc_format_refund_total', $tax_data );
					}
				}

				$result = $order->add_fee( $order_fee );

				if ( ! $result ) {
					wc_csv_import_suite()->log( sprintf( __( '> > Warning: cannot add fee "%s".', 'woocommerce-csv-import-suite' ), esc_html( $item['title'] ) ) );
				}

			break;
		}

		// store original order item ID
		if ( $result && isset( $item['order_item_id'] ) && $item['order_item_id'] > 0 ) {
			wc_update_order_item_meta( $result, '_original_order_item_id', $item['order_item_id'] );
		}

		return $result;
	}


	/**
	 * Update an order item
	 *
	 * @since 3.0.0
	 * @param \WC_Order $order WC_Order instance
	 * @param int $order_item_id Order item ID to update
	 * @param array $item Parsed item data from CSV
	 * @param string $type Line item type
	 * @return int|false ID of the updated order item, false on failure
	 */
	private function update_order_item( WC_Order $order, $order_item_id, $item, $type ) {

		$result = false;

		switch ( $type ) {

			case 'line_item':
				$product = $this->get_product_for_item( $item );
				$args    = $this->prepare_product_args( $item );

				$result = $order->update_product( $order_item_id, $product, $args );

				if ( ! $result ) {
					wc_csv_import_suite()->log( sprintf( __( '> > Warning: cannot update order item %d.', 'woocommerce-csv-import-suite' ), $order_item_id ) );
				}
			break;

			case 'shipping':
				$args   = array( 'order_item_name' => $item['method_title'] );
				$result = wc_update_order_item( $order_item_id, $args );

				if ( ! $result ) {
					wc_csv_import_suite()->log( sprintf( __( '> > Warning: cannot update shipping method "%s".', 'woocommerce-csv-import-suite' ), esc_html( $item['title'] ) ) );
				}
			break;

			case 'tax':
				$args   = array( 'order_item_name' => $item['code'] );
				$result = wc_update_order_item( $order_item_id, $args );

				if ( ! $result ) {
					wc_csv_import_suite()->log( sprintf( __( '> > Warning: cannot update tax "%s".', 'woocommerce-csv-import-suite' ), esc_html( $item['label'] ) ) );
				}
			break;

			case 'coupon':
				$args = array(
					'code'            => $item['code'],
					'discount_amount' => $item['amount'],
				);

				$result = $order->update_coupon( $order_item_id, $args );

				if ( ! $result ) {
					wc_csv_import_suite()->log( sprintf( __( '> > Warning: cannot merge coupon "%s".', 'woocommerce-csv-import-suite' ), esc_html( $item['code'] ) ) );
				}
			break;

			case 'fee':
				$args = array(
					'name'       => $item['title'],
					'line_total' => $item['total'],
					'line_tax'   => $item['total_tax'],
					'tax_class'  => isset( $item['tax_class'] ) ? $item['tax_class'] : '',
				);

				$result = $order->update_fee( $order_item_id, $args );

				if ( ! $result ) {
					wc_csv_import_suite()->log( sprintf( __( '> > Warning: cannot merge fee "%s".', 'woocommerce-csv-import-suite' ), esc_html( $item['title'] ) ) );
				}
			break;
		}

		return $result;
	}


	/**
	 * Update order item meta after adding or inserting an item
	 *
	 * @since 3.0.0
	 * @param int $order_item_id
	 * @param array $item Parsed item data from CSV
	 * @param string $type Line item type
	 */
	private function update_order_item_meta( $order_item_id, $item, $type ) {

		if ( empty( $item ) || ! $type ) {
			return;
		}

		switch ( $type ) {

			case 'line_item':

				if ( ! empty( $item['meta'] ) ) {
					foreach ( $item['meta'] as $meta_key => $meta_value ) {
						wc_update_order_item_meta( $order_item_id, $meta_key, $meta_value );
					}
				}

				if ( isset( $item['tax_data'] ) ) {
					wc_update_order_item_meta( $order_item_id, '_line_tax_data', $item['tax_data'] );
				}
			break;

			case 'tax':
				wc_update_order_item_meta( $order_item_id, 'rate_id',             $item['rate_id'] );
				wc_update_order_item_meta( $order_item_id, 'label',               $item['label'] );
				wc_update_order_item_meta( $order_item_id, 'compound',            $item['compound'] );
				wc_update_order_item_meta( $order_item_id, 'tax_amount',          $item['tax_amount'] );
				wc_update_order_item_meta( $order_item_id, 'shipping_tax_amount', $item['shipping_tax_amount'] );
			break;

			case 'shipping':
				wc_update_order_item_meta( $order_item_id, 'method_id', $item['method_id'] );
				wc_update_order_item_meta( $order_item_id, 'cost',      $item['cost'] );

				if ( isset( $item['taxes'] ) ) {
					wc_update_order_item_meta( $order_item_id, 'taxes', $item['taxes'] );
				}
			break;

			case 'fee':
				if ( isset( $item['tax_data'] ) ) {
					wc_update_order_item_meta( $order_item_id, '_line_tax_data', $item['tax_data'] );
				}
			break;
		}
	}


	/**
	 * Delete items that were not present in updated CSV when merging
	 *
	 * @since 3.0.0
	 * @param array $existing
	 * @param array $updated
	 */
	private function delete_removed_items( $existing, $updated ) {

		if ( count( $existing ) != count( $updated ) ) {

			// if this order item was not updated, it must be removed from the order
			foreach ( $existing as $order_item_id => $item ) {

				if ( ! in_array( $order_item_id, $updated ) ) {
					wc_delete_order_item( $order_item_id );
				}
			}
		}
	}


	/**
	 * Get product for parsed line item from CSV
	 *
	 * This methods returns a 'bogus' product with id 0 for unknown products
	 *
	 * @since 3.0.0
	 * @param array $item Parsed line item data from CSV
	 */
	private function get_product_for_item( $item ) {

		if ( isset( $item['product_id'] ) && $item['product_id'] ) {
			$product = wc_get_product( $item['product_id'] );
		} else {
			$product = new WC_Product( null );
			$product->id = 0;
			$product->tax_class = isset( $item['tax_class'] ) ? $item['tax_class'] : '';
			$product->post = new StdClass();
			$product->post->post_title = isset( $item['name'] ) ? $item['name'] : __( 'Unknown product', 'woocommerce-csv-import-suite' );
		}

		return $product;
	}


	/**
	 * Prepare product arguments to be consumed by $order->add/update_product
	 * Based on WC_API_Orders::set_line_item()
	 *
	 * @since 3.0.0
	 * @param array $item
	 * @return array Product / line item arguments, ready to be used by
	 *               $order->add/update_product
	 */
	private function prepare_product_args( $item ) {

		$args = array();

		if ( isset( $item['quantity'] ) ) {
			$args['qty'] = $item['quantity'];
		}

		// total
		if ( isset( $item['total'] ) ) {
			$args['totals']['total'] = floatval( $item['total'] );
		}

		// total tax
		if ( isset( $item['total_tax'] ) ) {
			$args['totals']['tax'] = floatval( $item['total_tax'] );
		}

		// subtotal
		if ( isset( $item['subtotal'] ) ) {
			$args['totals']['subtotal'] = floatval( $item['subtotal'] );
		}

		// subtotal tax
		if ( isset( $item['subtotal_tax'] ) ) {
			$args['totals']['subtotal_tax'] = floatval( $item['subtotal_tax'] );
		}

		// variations
		if ( isset( $item['variations'] ) ) {
			$args['variation'] = $item['variations'];
		}

		return $args;
	}


	/**
	 * Action to set the custom order number.	This can be modified by 3rd party
	 * plugins via the applied filters, or replaced wholesale if entirely different
	 * logic is required
	 *
	 * @since 1.0.0
	 * @param WC_Order $order order object
	 * @param int $order_number incrementing order number piece
	 * @param string $order_number_formatted formatted order number piece
	 */
	public function woocommerce_set_order_number( $order, $order_number, $order_number_formatted ) {
		// the best we can do to tie the newly imported order to the old, is to
		// at least record the order number internally (allowing 3rd party plugins
		// to specify the order number meta field name), and set a visible order
		// note indicating the original order number.	If the user has a custom order
		// number plugin like the Sequential Order Number Pro installed, then things
		// will be even cleaner on the backend
		update_post_meta( $order->id, apply_filters( 'woocommerce_order_number_meta_name',           '_order_number' ),           $order_number );
		update_post_meta( $order->id, apply_filters( 'woocommerce_order_number_formatted_meta_name', '_order_number_formatted' ), $order_number_formatted );
	}


	/**
	 * Get known, available shipping methods
	 *
	 * Caches the results for subsequent calls
	 *
	 * @since 3.0.0
	 * @return array
	 */
	private function get_available_shipping_methods() {

		if ( ! isset( $this->available_shipping_methods ) ) {
			$this->available_shipping_methods = WC()->shipping()->load_shipping_methods();
		}

		return $this->available_shipping_methods;
	}


	/**
	 * Get known, available payment gateways
	 *
	 * Caches the results for subsequent calls
	 *
	 * @since 3.0.0
	 * @return array
	 */
	private function get_available_payment_gateways() {

		if ( ! isset( $this->available_payment_gateways ) ) {
			$this->available_payment_gateways = WC()->payment_gateways->payment_gateways();
		}

		return $this->available_payment_gateways;
	}


	/**
	 * Get WC order statuses without the prefix
	 *
	 * Caches the results for subsequent calls
	 *
	 * @since 3.0.0
	 * @return array
	 */
	private function get_order_statuses_clean() {

		if ( ! isset( $this->order_statuses_clean ) ) {

			$this->order_statuses_clean = array();

			foreach ( wc_get_order_statuses() as $slug => $name ) {
				$this->order_statuses_clean[ preg_replace( '/^wc-/', '', $slug ) ] = $name;
			}
		}

		return $this->order_statuses_clean;
	}


	/**
	 * Get all defined tax rates, keyed off of ID
	 *
	 * Caches the results for subsequent calls
	 *
	 * @since 3.0.0
	 * @return array
	 */
	private function get_tax_rates() {

		if ( ! isset( $this->tax_rates ) ) {

			$this->tax_rates = array();

			global $wpdb;

			foreach ( $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates" ) as $row ) {
				$this->tax_rates[ $row->tax_rate_id ] = $row;
			}
		}

		return $this->tax_rates;
	}


	/**
	 * Detect CSV file format
	 *
	 * @since 3.0.0
	 * @param array $headers
	 * @return bool
	 */
	private function detect_csv_file_format( $headers ) {

		// legacy CSV Import format
		if ( in_array( 'order_item_1', $headers ) ) {
			return 'csv_import_legacy';
		}

		// CSV Export legacy format
		if (
			in_array( 'Order ID',           $headers ) &&
			in_array( 'Order Status',       $headers ) &&
			in_array( 'Billing Post code',  $headers ) &&
			in_array( 'Shipping Post code', $headers ) &&
			in_array( 'Order Items',        $headers )
		) {
			return 'csv_export_legacy';
		}

		// CSV Export one row per item legacy format
		if (
			in_array( 'Item SKU',       $headers ) &&
			in_array( 'Item Name',      $headers ) &&
			in_array( 'Item Variation', $headers ) &&
			in_array( 'Item Amount',    $headers ) &&
			in_array( 'Row Price',      $headers ) &&
			in_array( 'Order ID',       $headers )
		) {
			return 'csv_export_legacy_one_row_per_item';
		}

		// CSV Export one row per item format
		if (
			in_array( 'item_name',     $headers ) &&
			in_array( 'item_sku',      $headers ) &&
			in_array( 'item_quantity', $headers ) &&
			in_array( 'item_tax',      $headers ) &&
			in_array( 'item_total',    $headers ) &&
			in_array( 'item_meta',     $headers ) &&
			in_array( 'order_id',      $headers )
		) {
			return 'csv_export_default_one_row_per_item';
		}

		// CSV Export Default format is also considered default, since it's very
		// similar to the new default format
		return 'default';
	}


	/**
	 * Get order ID by formatted order number
	 *
	 * @since 3.0.0
	 * @param int|string $formatted_number
	 * @return int Found order ID or 0 if no match found
	 */
	private function get_order_id_by_formatted_number( $formatted_number ) {

		// we'll give 3rd party plugins two chances to hook in their custom order number facilities:
		// first by performing a simple search using the order meta field name used by both this and the
		// Sequential Order Number Pro plugin, allowing other plugins to filter over it if needed,
		// while still providing this plugin with some base functionality
		$query_args = array(
			'numberposts' => 1,
			'meta_key'    => apply_filters( 'woocommerce_order_number_formatted_meta_name', '_order_number_formatted' ),
			'meta_value'  => $formatted_number,
			'post_type'   => 'shop_order',
			'post_status' => array_keys( wc_get_order_statuses() ),
			'fields'      => 'ids',
		);

		$order_id = 0;
		$orders = get_posts( $query_args );

		if ( ! empty( $orders ) ) {
			list( $order_id ) = get_posts( $query_args );
		}

		return $order_id;
	}


	/**
	 * Split string on delimiter
	 *
	 * Will try to split on non-escaped delimiter first. Uses simple explode
	 * as a fallback
	 *
	 * @since 3.0.0
	 * @param string $string Input text
	 * @param string $delimiter
	 * @param bool $unescape_results Optional. Defaults to true. If true, will try
	 *                               to unescape any escaped delimiters in the
	 *                               resulting pieces.
	 * @return array Pieces
	 */
	private function split_on_delimiter( $string, $delimiter, $unescape_results = true ) {

		// split on non-escaped delimiters
		// http://stackoverflow.com/questions/6243778/split-string-by-delimiter-but-not-if-it-is-escaped
		$pieces = preg_split( '~\\\\.(*SKIP)(*FAIL)|\\' . $delimiter . '~s', $string );

		// fallback: try a simple explode, since the above apparently doesn't always work
		if ( $string && empty( $pieces ) ) {
			$pieces = explode( $delimiter, $string );
		}

		// unescape delimiter in results
		if ( $unescape_results && ! empty( $pieces ) ) {
			foreach ( $pieces as $key => $piece ) {

				$pieces[ $key ] = str_replace( '\\' . $delimiter, $delimiter, $piece );
			}
		}

		return array_map( 'trim', $pieces );
	}


	/**
	 * Split an array of string based key-value pairs into an associative array
	 *
	 * @since 3.0.0
	 * @param array $pairs Array of strings of key-value pairs, joined together by
	 *                     $delimiter
	 * @param string $delimiter Delimiter separating keys and values
	 * @param bool $unescape_results Optional. Defaults to true. If true, will try
	 *                               to unescape any escaped delimiters in the
	 *                               results.
	 * @return array
	 */
	private function split_key_value_pairs( $pairs, $delimiter, $unescape_results = true ) {

		$data = array();

		foreach ( $pairs as $pair ) {

			// split to key-value pieces
			$pieces = $this->split_on_delimiter( $pair, $delimiter, $unescape_results );
			$name   = $pieces[0];
			$value  = isset( $pieces[1] ) ? $pieces[1] : null;

			if ( $name ) {
				$data[ $name ] = $value;
			}
		}

		return $data;
	}


	/**
	 * Parse items from a delimited string
	 *
	 * Extracts items from string where items are separated by a delimiter
	 * (default semicolon ;), properties are separated with another delimiter
	 * (default pipe :), and property key-value pairs are separated with yet
	 * another delimiter (default colon :).
	 *
	 * Supports parsing a special 'meta' property, where it expects the meta
	 * items be separated with comma (,) by default and meta key-value pairs be
	 * separated with an equals sign (=) by default.
	 *
	 * All delimiters can be customized.
	 *
	 * @since 3.0.0
	 * @param string $input Input string
	 * @param array $delimiters {
	 *        Optional. Associative array of delimiters.
	 *
	 *        @type string $item Item delimiter. Default ';'.
	 *        @type string $property Property delimiter. Default '|'.
	 *        @type string $property_key_value Property key-value delimiter. Default ':'.
	 *        @type string $meta Meta delimiter. Default ','.
	 *        @type string $meta_key_value Meta key-value delimiter. Default '='.
	 * }
	 * @return array
	 */
	private function parse_delimited_string( $input, $delimiters = array() ) {

		$d = wp_parse_args( $delimiters, array(
			'item'               => ';', // item separator
			'property'           => '|', // property separator
			'property_key_value' => ':', // property key-value separator
			'meta'               => ',', // meta separator
			'meta_key_value'     => '=', // meta key-value separator
		) );

		// split string into items, based on item delimiter
		$items = $this->split_on_delimiter( $input, $d['item'] );

		// parse each item
		foreach( $items as $key => $item ) {

			// split item into properties
			$item = $this->split_on_delimiter( $item, $d['property'] );
			// split properties into key-value pairs
			$item = $this->split_key_value_pairs( $item, $d['property_key_value'] );

			// split item meta into key-value pairs
			if ( isset( $item['meta'] ) && ! empty( $item['meta'] ) ) {
				$item['meta'] = $this->split_on_delimiter( $item['meta'], $d['meta'] );
				$item['meta'] = $this->split_key_value_pairs( $item['meta'], $d['meta_key_value'] );
			}

			$items[ $key ] = $item;
		}

		return $items;
	}


	/**
	 * Check if input string is possibly a JSON array
	 *
	 * @since 3.0.0
	 * @param string $string
	 * @return bool True if string is possible JSON, false otherwise
	 */
	private function is_possibly_json_array( $string ) {
		return '[]' == $string || SV_WC_Helper::str_starts_with( $string, '[{' ) && SV_WC_Helper::str_ends_with( $string, '}]' );
	}


	/**
	 * Parse/decode a JSON string while throwing exceptions on errors
	 *
	 * @since 3.0.0
	 * @param string $string Input string
	 * @throws \WC_CSV_Import_Suite_Import_Exception json decoding errors
	 * @return array
	 */
	private function parse_json( $string ) {

		// decode the JSON data
		$result = json_decode( $string, true );
		$error  = null;

		// switch and check possible JSON errors
		switch ( json_last_error() ) {
			case JSON_ERROR_NONE:
				// JSON is valid // No error has occurred
			break;

			case JSON_ERROR_DEPTH:
				$error = esc_html__( 'The maximum stack depth has been exceeded.', 'woocommerce-csv-import-suite' );
			break;

			case JSON_ERROR_STATE_MISMATCH:
				$error = esc_html__( 'Invalid or malformed JSON.', 'woocommerce-csv-import-suite' );
			break;

			case JSON_ERROR_CTRL_CHAR:
				$error = esc_html__( 'Control character error, possibly incorrectly encoded.', 'woocommerce-csv-import-suite' );
			break;

			case JSON_ERROR_SYNTAX:
				$error = esc_html__( 'Syntax error, malformed JSON.', 'woocommerce-csv-import-suite' );
			break;

			// PHP >= 5.3.3
			case JSON_ERROR_UTF8:
				$error = esc_html__( 'Malformed UTF-8 characters, possibly incorrectly encoded.', 'woocommerce-csv-import-suite' );
			break;

			// PHP >= 5.5.0
			case JSON_ERROR_RECURSION:
				$error = esc_html__( 'One or more recursive references in the value to be encoded.', 'woocommerce-csv-import-suite' );
			break;

			// PHP >= 5.5.0
			case JSON_ERROR_INF_OR_NAN:
				$error = esc_html__( 'One or more NAN or INF values in the value to be encoded.', 'woocommerce-csv-import-suite' );
			break;

			case JSON_ERROR_UNSUPPORTED_TYPE:
				$error = esc_html__( 'A value of a type that cannot be encoded was given.', 'woocommerce-csv-import-suite' );
			break;

			default:
				$error = esc_html__( 'Unknown JSON error occured.', 'woocommerce-csv-import-suite' );
			break;
		}

		if ( $error ) {
			throw new WC_CSV_Import_Suite_Import_Exception( 'wc_csv_import_suite_json_parse_error', $error );
		}

		// everything is OK
		return $result;
	}


	/**
	 * Check if a given post ID is a valid product post type
	 *
	 * @since 3.0.0
	 * @param int $product_id
	 * @return bool
	 */
	private function is_valid_product( $product_id ) {

		/**
		 * Filter valid product post types
		 *
		 * @since 3.0.0
		 * @param array $valid_types Array fo valid post type names
		 */
		$valid_product_post_types = apply_filters( 'wc_csv_import_suite_valid_product_post_types', array( 'product', 'product_variation' ) );

		return in_array( get_post_type( $product_id ), $valid_product_post_types );
	}


	/**
	 * Get product ID by item SKU
	 *
	 * @since 3.0.0
	 * @param string $sku
	 * @return int|null
	 */
	private function get_product_id_by_sky( $sku ) {
		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT post_id FROM $wpdb->postmeta
			WHERE meta_key='_sku' AND meta_value=%s LIMIT 1
		", $sku ) );
	}


	/**
	 * Safely get a value for a key from an array
	 *
	 * @since 3.0.0
	 * @param array $array
	 * @param int|string $key
	 * @param mixed $default
	 * @return mixed
	 */
	private function get_array_key_value( $array, $key, $default = null ) {
		return isset( $array[ $key ] ) ? $array[ $key ] : $default;
	}


}

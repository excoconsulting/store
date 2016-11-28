<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Checkout Field Editor Export Handler
 *
 * Adds support for:
 *
 * + Customer / Order CSV Export
 *
 * @since 1.2.5
 */
class WC_Checkout_Field_Editor_Export_Handler {

	/** @var checkout fields */
	private $fields;


	/**
	 * Setup class
	 *
	 * @since 1.2.5
	 */
	public function __construct() {

		$this->fields = $this->set_fields();

		// Customer / Order CSV Export column headers/data
		add_filter( 'wc_customer_order_csv_export_order_headers', array( $this, 'add_fields_to_csv_export_column_headers' ), 10, 2 );
		add_filter( 'wc_customer_order_csv_export_order_row',     array( $this, 'add_fields_to_csv_export_column_data' ), 10, 4 );

		add_filter( 'wc_customer_order_xml_export_suite_order_export_order_list_format', array( $this, 'add_fields_to_xml_export_order_list_format' ), 10, 2 );
	}


	/**
	 * Adds support for Customer/Order CSV Export by adding a vendor column
	 * header
	 *
	 * @since 1.2.5
	 * @param array $headers existing array of header key/names for the CSV export
	 * @return array
	 */
	public function add_fields_to_csv_export_column_headers( $headers, $csv_generator ) {

		$field_headers = array();

		foreach ( $this->fields as $name => $options ) {
			$field_headers[ $name ] = $name;
		}

		return array_merge( $headers, $field_headers );
	}


	/**
	 * Adds support for Customer/Order CSV Export by adding checkout editor field data
	 *
	 * @since 1.2.5
	 * @param array $order_data generated order data matching the column keys in the header
	 * @param WC_Order $order order being exported
	 * @param \WC_Customer_Order_CSV_Export_Generator $csv_generator instance
	 * @return array
	 */
	public function add_fields_to_csv_export_column_data( $order_data, $order, $csv_generator ) {

		$field_data       = array();
		$new_order_data   = array();
		$one_row_per_item = false;

		foreach ( $this->fields as $name => $options ) {
			$field_data[ $name ] = get_post_meta( $order->id, $name, true );
		}

		// determine if the selected format is "one row per item"
		if ( version_compare( wc_customer_order_csv_export()->get_version(), '4.0.0', '<' ) ) {

			$one_row_per_item = ( 'default_one_row_per_item' === $csv_generator->order_format || 'legacy_one_row_per_item' === $csv_generator->order_format );

		// v4.0.0 - 4.0.2
		} elseif ( ! isset( $csv_generator->format_definition ) ) {

			// get the CSV Export format definition
			$format_definition = wc_customer_order_csv_export()->get_formats_instance()->get_format( $csv_generator->export_type, $csv_generator->export_format );

			$one_row_per_item = isset( $format_definition['row_type'] ) && 'item' === $format_definition['row_type'];

		// v4.0.3+
		} else {

			$one_row_per_item = 'item' === $csv_generator->format_definition['row_type'];
		}

		if ( $one_row_per_item ) {

			foreach ( $order_data as $data ) {
				$new_order_data[] = array_merge( $field_data, (array) $data );
			}

		} else {

			$new_order_data = array_merge( $field_data, $order_data );
		}

		return $new_order_data;
	}


	/**
	 * Adds support for Customer/Order XML Export Suite by adding checkout editor field data
	 *
	 * @since 1.4.6
	 * @param array $order_format array of order data to be exported
	 * @param WC_Order $order order being exported
	 * @return array modified array of order data to be exported
	 */
	public function add_fields_to_xml_export_order_list_format( $order_format, $order ) {

		$field_data = array();

		foreach ( $this->fields as $name => $options ) {
			$order_format[ $name ] = get_post_meta( $order->id, $name, true );
		}

		return $order_format;
	}


	/**
	 * Set all registered fields
	 *
	 * @since 1.2.5
	 * @return array
	 */
	private function set_fields() {

		$fields = array();

		$temp_fields = get_option( 'wc_fields_billing' );

		if ( $temp_fields !== false ) {
			$fields = array_merge( $fields, $temp_fields );
		}

		$temp_fields = get_option( 'wc_fields_shipping' );

		if ( $temp_fields !== false ) {
			$fields = array_merge( $fields, $temp_fields );
		}

		$temp_fields = get_option( 'wc_fields_additional' );

		if ( $temp_fields !== false ) {
			$fields = array_merge( $fields, $temp_fields );
		}

		return $fields;
	}

	/**
	 * Get all registered fields
	 *
	 * @since 1.4.9
	 * @return array
	 */
	public function get_fields() {
		return $this->fields;
	}

} // end WC_Checkout_Field_Editor_Export_Handler

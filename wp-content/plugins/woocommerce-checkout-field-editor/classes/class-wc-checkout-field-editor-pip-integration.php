<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Checkout Field Editor PIP Integration
 *
 * Adds support for:
 *
 * + Print Invoices & Packing lists
 *
 * @since 1.6.0
 */
class WC_Checkout_Field_Editor_PIP_Integration {

	/**
	 * Add actions and filters
	 *
	 * @since 1.6.0
	 */
	public function __construct() {
		// Add settings options
		add_filter( 'wc_pip_general_settings', array( $this, 'add_settings' ), 20, 1 );

		// Display save button
		add_filter( 'wc_pip_settings_hide_save_button', array( $this, 'show_save_button' ), 20, 2 );

		// Output fields in template
		add_action( 'wc_pip_after_customer_addresses', array( $this, 'custom_fields' ), 20, 4 );

		// Add some css styles
		add_action( 'wc_pip_styles', array( $this, 'add_styles' ), 20 );
	}

	/**
	 * Extend PIP template settings
	 *
	 * @since 1.6.0
	 * @param array $settings
	 * @return array
	 */
	public function add_settings( $settings ) {
		return array_merge( $settings, array(
			// Section start
			array(
				'name' => __( 'Checkout Fields Editor', 'woocommerce-checkout-field-editor' ),
				'type' => 'title',
			),

			// Document types
			array(
				'id'       => 'woocommerce_pip_checkout_field_editor_custom_fields',
				'name'     => __( 'Custom Checkout Fields', 'woocommerce-checkout-field-editor' ),
				'desc_tip' => __( 'Select which document types should display custom checkout fields.', 'woocommerce-checkout-field-editor' ),
				'type'     => 'multiselect',
				'class'    => 'wc-enhanced-select',
				'options'  => wc_pip()->get_document_types(),
			),

			// Section end
			array(
				'type' => 'sectionend',
			),
		) );
	}

	/**
	 * Show save button in general tab
	 *
	 * @since 1.6.0
	 * @param bool $hide_save_button
	 * @param string $current_section
	 * @return array
	 */
	public function show_save_button( $hide_save_button, $current_section ) {
		if ( 'general' == $current_section ) {
			$hide_save_button = false;
		}

		return $hide_save_button;
	}

	/**
	 * Output custom fields in PIP templates
	 *
	 * @since 1.6.0
	 * @param string $document_type PIP Document type
	 * @param string $action Current action running on Document
	 * @param WC_PIP_Document $document PIP Document object
	 * @param WC_Order $order Order object
	 */
	public function custom_fields( $document_type, $action, $document, $order ) {
		if ( function_exists( 'wc_display_custom_fields_view_order' ) && $document_types = get_option( 'woocommerce_pip_checkout_field_editor_custom_fields' ) ) {
			if ( is_array( $document_types ) && in_array( $document_type, $document_types ) ) {
				wc_display_custom_fields_view_order( $order );
			}
		}
	}

	/**
	 * Output some styles for PIP documents
	 *
	 * @since 1.6.0
	 */
	public function add_styles() { 
	?>
		dl dt {
			display: inline-block;
			font-weight: bold;
		}

		dl dd {
			display: inline;
			margin-left: 5px;
		}

		dl dd:after {
			content: "";
			display: block;
		}
	<?php
	}

} // end WC_Checkout_Field_Editor_PIP_Integration

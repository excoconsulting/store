<?php
/**
 * WooCommerce Checkout Add-Ons
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Checkout Add-Ons to newer
 * versions in the future. If you wish to customize WooCommerce Checkout Add-Ons for your
 * needs please refer to http://docs.woothemes.com/document/woocommerce-checkout-add-ons/ for more information.
 *
 * @package     WC-Checkout-Add-Ons/Classes
 * @author      SkyVerge
 * @copyright   Copyright (c) 2014-2016, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Frontend class
 *
 * @since 1.0
 */
class WC_Checkout_Add_Ons_Frontend {


	/**
	 * Separator used between add-on name and selected/entered value
	 * in order review area
	 */
	private $label_separator = ' - ';

	/** Are we currently in checkout order review? **/
	private $is_checkout_order_review = false;


	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// Load frontend styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'load_styles_scripts' ) );

		// Add add-on fields to checkout fields
		add_filter( 'woocommerce_checkout_fields', array( $this, 'add_checkout_fields' ) );

		/**
		 * Filter the add-on fields position.
		 *
		 * @since 1.6.0
		 * @param string $position
		 */
		$position = apply_filters( 'wc_checkout_add_ons_position', get_option( 'wc_checkout_add_ons_position', 'woocommerce_checkout_after_customer_details' ) );

		// Render add-on fields in position based on the settings.
		add_action( $position, array( $this, 'render_add_ons' ), 20 );

		// Add any selected add-ons as fees to cart
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_cart_fees' ) );

		// Flatten the fee display of add-ons with no associated cost
		add_filter( 'woocommerce_cart_totals_fee_html', array( $this, 'adjust_fee_html' ), 10, 2 );

		// Handle file uploads for the `file` add-on
		add_action( 'wp_ajax_wc_checkout_add_on_upload_file',        array( $this, 'upload_file' ) );
		add_action( 'wp_ajax_nopriv_wc_checkout_add_on_upload_file', array( $this, 'upload_file' ) );
		add_action( 'wp_ajax_wc_checkout_add_on_remove_file',        array( $this, 'remove_file' ) );
		add_action( 'wp_ajax_nopriv_wc_checkout_add_on_remove_file', array( $this, 'remove_file' ) );

		// Add support for radio, file and multichecbox form field types
		add_filter( 'woocommerce_form_field_wc_checkout_add_ons_multicheckbox',  array( $this, 'form_field' ), 11, 4 );
		add_filter( 'woocommerce_form_field_wc_checkout_add_ons_multiselect',    array( $this, 'form_field' ), 11, 4 );
		add_filter( 'woocommerce_form_field_wc_checkout_add_ons_radio',          array( $this, 'form_field' ), 11, 4 );
		add_filter( 'woocommerce_form_field_wc_checkout_add_ons_file',           array( $this, 'form_field' ), 11, 4 );

		// save add-on meta on checkout
		// this also saves the add-on meta for subscriptions
		add_action( 'woocommerce_add_order_fee_meta', array( $this, 'save_add_on_meta' ), 10, 3 );

		// Initialize session
		add_action( 'woocommerce_init', array( $this, 'init_session' ) );

		// Get correct checkout-add-on value
		add_filter( 'woocommerce_checkout_get_value', array( $this, 'checkout_get_add_on_value' ), 10, 2 );

		// clear session after checkout has been processed
		// this is done intentionally late so that Subscriptions can use the session data as well
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'clear_session' ), 150 );

		// Handle displaying selected/entered add-on values in checkout order review area
		add_action( 'woocommerce_before_template_part', array( $this, 'indicate_checkout_order_review' ), 1 );
		add_action( 'woocommerce_after_template_part',   array( $this, 'clear_checkout_order_review_indicator' ) );
		add_filter( 'esc_html', array( $this, 'display_add_on_value_in_checkout_order_review' ), 10, 2 );

		// Handle displaying selected/entered add-on values in my-account/view-order screen
		add_filter( 'woocommerce_get_order_item_totals',                array( $this, 'append_order_add_on_fee_meta' ), 10, 2 );
		add_filter( 'woocommerce_get_order_item_totals_excl_free_fees', array( $this, 'include_free_order_add_on_fee_meta' ), 10, 2 );

		// Override default select/multiselect/radio value sanitization in special cases
		add_filter( 'sanitize_title', array( $this, 'sanitize_select_field_values' ), 10, 3 );
	}


	/**
	 * Handle add-on file uploads
	 *
	 * @since 1.0
	 */
	public function upload_file() {

		check_ajax_referer( 'wc-checkout-add-ons-upload-file', 'security' );

		if ( empty( $_FILES ) || $_FILES['file']['error'] ) {
			return false;
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
			require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
			require_once( ABSPATH . "wp-admin" . '/includes/media.php' );
		}

		$file_info = pathinfo( $_FILES['file']['name'] );

		$_FILES['file']['name'] = $file_info['filename'] . '-' . uniqid() . '.' . $file_info['extension'];

		$attachment_id = media_handle_upload( 'file', 0 );

		if ( is_wp_error( $attachment_id ) ) {

			echo json_encode( $attachment_id );

		} else {

			$this->store_uploaded_file_in_session( $attachment_id );

			echo json_encode( array(
				'id'    => $attachment_id,
				'title' => get_the_title( $attachment_id ) . '.' . strtolower( $file_info['extension'] ),
				'url'   => wp_get_attachment_url( $attachment_id ),
			) );
		}

		exit;
	}


	/**
	 * Handle add-on file removals
	 *
	 * @since 1.0
	 */
	public function remove_file() {

		check_ajax_referer( 'wc-checkout-add-ons-remove-file', 'security' );

		$file = $_POST['file'];

		// Bail out if no file
		if ( ! $file ) {
			return;
		}

		// Security: bail out if not uploaded in the same session
		if ( ! in_array( $file, WC()->session->checkout_add_ons['files'] ) ) {
			return;
		}

		// Delete the attachment
		wp_delete_attachment( $file, true );
		$this->remove_uploaded_file_from_session( $file );

		exit;
	}


	/**
	 * Loads frontend styles and scripts on checkout page
	 *
	 * @since 1.0
	 */
	public function load_styles_scripts() {

		$load_styles_scripts = is_checkout();

		if ( ! $load_styles_scripts && function_exists( 'is_wcopc_checkout' ) ) {
			$load_styles_scripts =  is_wcopc_checkout();
		}

		/**
		 * Filter if Checkout Add-ons scripts and styles should be loaded
		 *
		 * @since 1.3.0
		 * @param bool $load_styles_scripts true if scripts and styles should be loaded; false otherwise
		 */
		if ( ! apply_filters( 'wc_checkout_add_ons_load_styles_scripts', $load_styles_scripts ) ) {
			return;
		}

		$add_on_fields = wc_checkout_add_ons()->get_add_ons();

		$script_dependencies = array( 'jquery' );

		// Determine if we need to load Plupload
		if ( ! empty( $add_on_fields ) ) {
			foreach ( $add_on_fields as $add_on_field ) {
				if ( 'file' === $add_on_field->type ) {
					wp_enqueue_script( 'plupload-all' );
					$script_dependencies[] = 'plupload-all';
					break;
				}
			}
		}

		// Register and load our styles and scripts
		wp_register_script( 'wc-checkout-add-ons-frontend', wc_checkout_add_ons()->get_plugin_url() . '/assets/js/frontend/wc-checkout-add-ons.min.js', $script_dependencies, WC_Checkout_Add_Ons::VERSION, true );

		wp_localize_script( 'wc-checkout-add-ons-frontend', 'wc_checkout_add_ons', array(
			'ajax_url'                  => admin_url( 'admin-ajax.php' ),
			'max_file_size'             => wp_max_upload_size(),
			'max_files'                 => 1,
			'mime_types'                => $this->get_mime_types(),
			'upload_nonce'              => wp_create_nonce('wc-checkout-add-ons-upload-file'),
			'remove_nonce'              => wp_create_nonce('wc-checkout-add-ons-remove-file'),
			'select_placeholder_single' => __( 'Select an Option', 'woocommerce-checkout-add-ons' ),
			'select_placeholder_multi'  => __( 'Select Some Options', 'woocommerce-checkout-add-ons' ),
			'select_no_results_text'    => __( 'No results match', 'woocommerce-checkout-add-ons' ),
		) );

		wp_enqueue_script( 'wc-checkout-add-ons-frontend' );

		wp_enqueue_style( 'wc-checkout-add-ons-frontend', wc_checkout_add_ons()->get_plugin_url() . '/assets/css/frontend/wc-checkout-add-ons.min.css', array(), WC_Checkout_Add_Ons::VERSION );
	}


	/**
	 * Add add-on fields to checkout fields
	 *
	 * Adding add-on fields to checkout fields provides
	 * automatic validation and helps to keep our code
	 * more maintainable.
	 *
	 * @since 1.0
	 * @param array $checkout_fields associative array of field id to definition
	 * @return array associative array of field id to definition
	 */
	public function add_checkout_fields( $checkout_fields ) {

		$add_ons = wc_checkout_add_ons()->get_add_ons();
		$is_processing = defined( 'WOOCOMMERCE_CHECKOUT' );

		if ( ! empty( $add_ons ) ) {

			$checkout_fields['add_ons'] = array();

			// store names in an array so we can skip duplicates - the WC Fee API only accepts fees with unique names
			$add_on_names = array();

			foreach ( $add_ons as $add_on ) {

				// continue to next add-on if an add-on of the same name was already added
				if ( in_array( $add_on->name, $add_on_names ) ) {
					continue;
				}

				// record the add-on name
				$add_on_names[] = $add_on->name;

				switch ( $add_on->type ) {
					case 'file':

						$checkout_fields['add_ons'][ 'wc_checkout_add_ons_' . $add_on->id ] = array(
							'type'     => 'wc_checkout_add_ons_file',
							'label'    => $is_processing ? $add_on->name : $this->get_formatted_label( $add_on->name, $add_on->label, $add_on->get_cost_html() ),
							'required' => $add_on->is_required(),
						);
					break;

					case 'text':
					case 'textarea':
					case 'checkbox':

						$checkout_fields['add_ons'][ 'wc_checkout_add_ons_' . $add_on->id ] = array(
							'type'     => $add_on->type,
							'label'    => $is_processing ? $add_on->name : $this->get_formatted_label( $add_on->name, $add_on->label, $add_on->get_cost_html() ),
							'required' => $add_on->is_required(),
						);
					break;

					case 'select':
					case 'radio':

						$options = array();
						$default = null;

						foreach ( $add_on->get_options() as $option ) {

							$cost_type = isset( $option['cost_type'] ) ? $option['cost_type'] : 'fixed';

							$value = wp_strip_all_tags( $this->get_formatted_label( null, $option['label'], $add_on->get_cost_html( $option['cost'], $cost_type ) ) );
							$key   = sanitize_title( $option['label'], '', 'wc_checkout_add_ons_sanitize' );

							$options[ $key ] = $value;

							if ( $option['selected'] ) {
								$default = $key;
							}
						}

						$checkout_fields['add_ons'][ 'wc_checkout_add_ons_' . $add_on->id ] = array(
							'type'        => 'radio' === $add_on->type ? 'wc_checkout_add_ons_radio' : $add_on->type,
							'label'       => $is_processing ? $add_on->name : $this->get_formatted_label( $add_on->name, $add_on->label ),
							'required'    => $add_on->is_required(),
							'options'     => $options,
							'default'     => $default,
							'placeholder' => $default,
						);
					break;

					case 'multiselect':
					case 'multicheckbox':

						// Create special `wc_checkout_add_ons_multicheckbox` type for checkboxes with multiple options
						$options = array();
						$defaults = array();

						foreach ( $add_on->get_options() as $option ) {

							$cost_type = isset( $option['cost_type'] ) ? $option['cost_type'] : 'fixed';

							$value = wp_strip_all_tags( $this->get_formatted_label( null, $option['label'], $add_on->get_cost_html( $option['cost'], $cost_type ) ) );
							$key   = sanitize_title( $option['label'], '', 'wc_checkout_add_ons_sanitize' );

							$options[ $key ] = $value;

							if ( $option['selected'] ) {
								$defaults[] = $key;
							}
						}

						$checkout_fields['add_ons'][ 'wc_checkout_add_ons_' . $add_on->id ] = array(
							'type'     => 'wc_checkout_add_ons_' . $add_on->type,
							'label'    => $is_processing ? $add_on->name : $this->get_formatted_label( $add_on->name, $add_on->label ),
							'required' => $add_on->is_required(),
							'options'  => $options,
							'default'  => $defaults,
						);
					break;
				}
			}

		}

		return $checkout_fields;
	}


	/**
	 * Add add-ons as fees to cart
	 *
	 * For file and text inputs, simply the cost is added.
	 * For selects and radio buttons, the cost of the selected option is added.
	 * For checkboxes, the cost of each checked option is added.
	 *
	 * @since 1.0
	 * @param object $cart
	 */
	public function add_cart_fees( $cart ) {

		if ( ! $_POST || ( is_admin() && ! is_ajax() ) ) {
			return;
		}

		if ( isset( $_POST['post_data'] ) ) {
			parse_str( $_POST['post_data'], $post_data );
		}

		$add_ons = wc_checkout_add_ons()->get_add_ons();

		foreach ( $add_ons as $add_on ) {

			// check if this cart is a recurring cart from Subscriptions
			// if so, check that the add-on is set to be renewable
			if ( wc_checkout_add_ons()->is_subscriptions_active() && $cart->start_date && ! $add_on->is_renewable() ) {
				continue;
			}

			$id    = esc_attr( $add_on->id );
			$name  = __( $add_on->name, 'woocommerce-checkout-add-ons' );
			$field = WC_Checkout_Add_Ons::PLUGIN_PREFIX . $id;
			$value = isset( $post_data[ $field ] ) ? $post_data[ $field ] : ( isset( $_POST[ $field ] ) ? $_POST[ $field ] : null );

			switch ( $add_on->type ) {

				case 'text':
				case 'textarea':
				case 'checkbox':
				case 'file':

					if ( $value ) {

						$cost      = $add_on->get_cost();
						$taxable   = $add_on->is_taxable();
						$tax_class = $add_on->get_tax_class();

						// Calculate the percentage if needed.
						if ( 'percent' === $add_on->get_cost_type() ) {
							$cost = $cart->cart_contents_total * $cost;
						}

						$cart->add_fee( $name, $cost, $taxable, $tax_class );

						if ( in_array( $add_on->type, array( 'text', 'textarea' ) ) ) {
							$value = stripslashes( $value );
						}

						$this->store_add_on_in_session( $field, $id, $name, $value );
					}

				break;

				case 'select':
				case 'radio':

					if ( $value ) {

						foreach ( $add_on->get_options() as $option ) {

							$key = sanitize_title( $option['label'], '', 'wc_checkout_add_ons_sanitize' );

							if ( $value === $key ) {

								$cost      = $option['cost'];
								$taxable   = $add_on->is_taxable();
								$tax_class = $add_on->get_tax_class();

								// Calculate the percentage if needed.
								if ( isset( $option['cost_type'] ) && 'percent' === $option['cost_type'] ) {
									$cost = $cart->cart_contents_total * $cost;
								}

								$cart->add_fee( $name, $cost, $taxable, $tax_class );

								$this->store_add_on_in_session( $field, $id, $name, $value );
							}
						}
					}

				break;

				case 'multiselect':
				case 'multicheckbox':

					$has_value = false;
					$cost = 0;
					$value = is_array( $value ) ? $value : array( $value );

					foreach ( $add_on->get_options() as $option ) {

						$key = sanitize_title( $option['label'], '', 'wc_checkout_add_ons_sanitize' );

						if ( in_array( $key, $value ) ) {

							$has_value = true;

							// Calculate the percentage if needed.
							if ( isset( $option['cost_type'] ) && 'percent' === $option['cost_type'] ) {
								$option['cost'] = $cart->cart_contents_total * $option['cost'];
							}

							$cost += $option['cost'];
						}
					}

					if ( $has_value ) {

						$taxable   = $add_on->is_taxable();
						$tax_class = $add_on->get_tax_class();

						$cart->add_fee( $name, $cost, $taxable, $tax_class );

						$this->store_add_on_in_session( $field, $id, $name, $value );

					} else {

						// Set value to null to make sure that if there is no value, the add-on is removed from session
						$value = null;
					}

				break;

			}

			// Remove add-on from session if it has no value
			if ( ! $value ) {
				$this->remove_add_on_from_session( $field, $name );
			}
		}
	}


	/**
	 * Flatten (--) the fee display of add-ons with no associated cost.
	 *
	 * @since 1.6.1
	 * @param string $html The cost HTML.
	 * @param object $fee The fee.
	 * @return string $html The modified cost HTML.
	 */
	public function adjust_fee_html( $html, $fee ) {

		$add_on = null;

		// Try and find an add-on with a matching name
		foreach ( wc_checkout_add_ons()->get_add_ons() as $_add_on ) {

			if ( $fee->name === $_add_on->name ) {

				$add_on = $_add_on;
				break;
			}
		}

		// No add-on was found, so bail
		if ( ! $add_on ) {
			return $html;
		}

		$cost = '';

		switch ( $add_on->type ) {

			case 'multiselect':
			case 'multicheckbox':

				$session_data = WC()->session->checkout_add_ons['fees'][ sanitize_title( $add_on->name ) ];

				$options = $add_on->get_options();

				foreach ( (array) $session_data['value'] as $selected_option ) {

					foreach ( $options as $option ) {

						if ( '' === $option['cost'] ) {
							continue;
						}

						if ( $selected_option === sanitize_title( $option['label'], '', 'wc_checkout_add_ons_sanitize' ) ) {

							$cost += $option['cost'];
						}
					}
				}

			break;

			case 'radio':
			case 'select':

				$session_data = WC()->session->checkout_add_ons['fees'][ sanitize_title( $add_on->name ) ];

				$options = $add_on->get_options();

				foreach ( $options as $option ) {

					if ( $session_data['value'] === sanitize_title( $option['label'], '', 'wc_checkout_add_ons_sanitize' ) ) {
						$cost = $option['cost'];
					}
				}

			break;

			default:
				$cost = $add_on->get_cost();
			break;
		}

		if ( '' === $cost ) {
			$html = '--';
		}

		/**
		 * Filter the add-on fee display at checkout.
		 *
		 * @since 1.6.1
		 * @param string $html The add-on fee HTML.
		 * @param string $cost The calculated add-on cost
		 * @param WC_Checkout_Add_On $add_on The add-on object.
		 */
		$html = apply_filters( 'wc_checkout_add_ons_checkout_add_on_fee_html', $html, $cost, $add_on );

		return $html;
	}


	/**
	 * Get formatted label, using $label if set, otherwise $name. Includes
	 * $cost if provided
	 *
	 * @since 1.0
	 * @param string $name field name
	 * @param string $label optional descriptive field label (default: empty string)
	 * @param string $cost optional field cost (default: empty string)
	 * @return string formatted label field
	 */
	public function get_formatted_label( $name, $label = '', $cost = '' ) {
		return ( $label ? esc_html( $label ) : esc_html( $name ) ) . ( $cost ? ' (' . $cost . ')' : '' );
	}


	/**
	 * Render add-ons after customer details
	 *
	 * @since 1.0
	 */
	public function render_add_ons() {

		$checkout_add_on_fields = isset( WC()->checkout()->checkout_fields['add_ons'] ) ? WC()->checkout()->checkout_fields['add_ons'] : null;

		// load the template
		wc_get_template(
			'checkout/add-ons.php',
			array(
				'add_on_fields' => $checkout_add_on_fields,
			),
			'',
			wc_checkout_add_ons()->get_plugin_path() . '/templates/'
		);
	}


	/**
	 * Support radio, file and multicheckbox field types in woocommerce_form_field
	 *
	 * @since 1.0
	 * @param string $field
	 * @param string $key
	 * @param array $args
	 * @param mixed $value
	 * @return string form field markup
	 */
	public function form_field( $field, $key, $args, $value ) {

		$after    = '';
		$required = '';

		if ( ! empty( $args['clear'] ) ) {
			$after = '<div class="clear"></div>';
		}

		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required = ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce-checkout-add-ons'  ) . '">*</abbr>';
		}

		// Custom attribute handling
		$custom_attributes = array();

		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {

			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		switch ( $args['type'] ) {

			case 'wc_checkout_add_ons_radio' :

				if ( ! empty( $args['options'] ) ) {

					$field .= '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $key ) . '_field">';

					if ( $args['label'] ) {
						$field .= '<label for="' . esc_attr( current( array_keys( $args['options'] ) ) ) . '" class="' . implode( ' ', $args['label_class'] ) .'">' . $args['label']. $required  . '</label>';
					}

					foreach ( $args['options'] as $option_key => $option_text ) {

						$field .= '<input type="radio" class="input-checkbox" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . ' />';

						$field .= '<label for="' . esc_attr( $key ) . '_' . esc_attr( $option_key ) . '" class="checkbox ' . implode( ' ', $args['label_class'] ) .'">' . $option_text . '</label><br>';

					}

					$field .= '</p>' . $after;
				}

			break;

			case 'wc_checkout_add_ons_multiselect' :

				$value = is_array( $value ) ? $value : array( $value );

				if ( ! empty( $args['options'] ) ) {

					$options = '';
					foreach ( $args['options'] as $option_key => $option_text ) {

						$options .= '<option value="' . esc_attr( $option_key ) . '" '. selected( in_array( $option_key, $value ), 1, false ) . '>' . esc_attr( $option_text ) .'</option>';
					}

					$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $key ) . '_field">';

					if ( $args['label'] ) {
						$field .= '<label for="' . esc_attr( $key ) . '" class="' . implode( ' ', $args['label_class'] ) .'">' . $args['label']. $required . '</label>';
					}

					$field .= '<select name="' . esc_attr( $key ) . '[]" id="' . esc_attr( $key ) . '" class="select" multiple="multiple" ' . implode( ' ', $custom_attributes ) . '>'
							. $options
							. ' </select></p>'
							. $after;
				}

			break;

			case 'wc_checkout_add_ons_multicheckbox' :

				$value = is_array( $value ) ? $value : array( $value );

				if ( ! empty( $args['options'] ) ) {

					$field .= '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $key ) . '_field">';

					if ( $args['label'] ) {
						$field .= '<label for="' . esc_attr( current( array_keys( $args['options'] ) ) ) . '" class="' . implode( ' ', $args['label_class'] ) .'">' . $args['label']. $required  . '</label>';
					}

					foreach ( $args['options'] as $option_key => $option_text ) {

						$field .= '<input type="checkbox" class="input-checkbox" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '[]" id="' . esc_attr( $key ) . '_' . esc_attr( $option_key ) . '"' . checked( in_array( $option_key, $value ), 1, false ) . ' />';
						$field .= '<label for="' . esc_attr( $key ) . '_' . esc_attr( $option_key ) . '" class="checkbox ' . implode( ' ', $args['label_class'] ) .'">' . $option_text . '</label><br>';
					}

					$field .= '</p>' . $after;
				}

			break;

			case 'wc_checkout_add_ons_file' :

				$field .= '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $key ) . '_field">';

				if ( $args['label'] ) {
					$field .= '<label for="' . esc_attr( current( array_keys( $args['options'] ) ) ) . '" class="' . implode( ' ', $args['label_class'] ) .'">' . $args['label']. $required  . '</label>';
				}

				$url   = '';
				$title = '';

				// Value === attachment ID
				if ( $value ) {

					if ( ! in_array( $value, WC()->session->checkout_add_ons['files'] ) ) {

						// The file was not uploaded in the current session. Clear value
						$value = '';

					} else {

						// Get the url and title for the provided attachment
						$url   = wp_get_attachment_url( $value );
						$title = get_the_title( $value );

					}
				}

				$field .= '<div class="input-file-plupload ' . implode( ' ', $args['input_class'] ) .'">';

				$field .= '<a class="dropzone ' . ( $url ? 'hide' : '' ) . '">';
				$field .= __( 'Drag file here or click to upload', 'woocommerce-checkout-add-ons' );
				$field .= '<div class="progress hide"><div class="bar"></div></div>';
				$field .='</a>';

				$field .= '<div class="preview ' . ( ! $url ? 'hide' : '' ) . '">';
				$field .= '<a href="' . $url . '" class="file">' . $title  . '</a>';
				$field .= '<a href="#" class="remove-file">' . __( 'Remove', 'woocommerce-checkout-add-ons' ) . '</a>';
				$field .= '</div>';

				$field .= '<div class="feedback hide"></div>';
				$field .= '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . $value . '" />';
				$field .= '<noscript>' . __( 'You need to enable Javascript to upload files', 'woocommerce-checkout-add-ons' ) . '</noscript>';
				$field .= '</div>';

				$field .= '</p>' . $after;

			break;

		}

		return $field;
	}


	/**
	 * Initialize checkout add-ons session
	 *
	 * Gives us an array in WooCommerce session where
	 * we can store state about the currently selected
	 * add-ons as well as cart fees and add-on relations.
	 *
	 * @since 1.0
	 */
	public function init_session() {

		if ( isset( WC()->session ) && ! WC()->session->checkout_add_ons ) {

			WC()->session->checkout_add_ons = array(
				'fields' => array(),
				'fees'   => array(),
				'files'  => array(),
			);
		}
	}


	/**
	 * Get add-on checkout value
	 *
	 * @since 1.0
	 * @param mixed $value
	 * @param string $input
	 * @return mixed
	 */
	public function checkout_get_add_on_value( $value, $input ) {

		// Get add-on value from session
		if ( isset( WC()->session->checkout_add_ons['fields'][ $input ] ) ) {
			$value = WC()->session->checkout_add_ons['fields'][ $input ]['value'];
		}

		return $value;
	}


	/**
	 * Store add-on value and related fee in session
	 *
	 * @since 1.0
	 * @param string $field name used in checkout
	 * @param int $id of the add-on
	 * @param string $name localized name of the add-on
	 * @param mixed $value add-on value
	 */
	public function store_add_on_in_session( $field, $id, $name, $value ) {

		$session_data = WC()->session->checkout_add_ons;

		// Store add-on data with field as key for easy lookup on checkout_get_add_on_value
		$data = array( 'id' => $id, 'value' => $value );
		$session_data['fields'][ $field ] = $session_data['fees'][ sanitize_title( $name ) ] = $data;

		WC()->session->checkout_add_ons = $session_data;
	}


	/**
	 * Remove add-on data from session
	 *
	 * @since 1.0
	 * @param string $field name used in checkout
	 */
	public function remove_add_on_from_session( $field, $name ) {

		$session_data = WC()->session->checkout_add_ons;

		if ( isset( $session_data['fields'][ $field ] ) ) {
			unset( $session_data['fields'][ $field ] );
		}

		$name = sanitize_title( $name );

		if ( isset( $session_data['fees'][ $name ] ) ) {
			unset( $session_data['fees'][ $name ] );
		}

		WC()->session->checkout_add_ons = $session_data;
	}


	/**
	 * Store a reference to the uploaded file in session
	 *
	 * @since 1.0
	 * @param string $file attachment title
	 */
	public function store_uploaded_file_in_session( $file ) {

		$session_data = WC()->session->checkout_add_ons;

		$session_data['files'][] = $file;

		WC()->session->checkout_add_ons = $session_data;
	}


	/**
	 * Remove reference to the uploaded file from session
	 *
	 * @since 1.0
	 * @param string $file attachment title
	 */
	public function remove_uploaded_file_from_session( $file ) {

		$session_data = WC()->session->checkout_add_ons;

		$key = array_search( $file, $session_data['files'] );
		if ( $key !== false ) {
			unset( $session_data['files'][ $key ] );
		}

		WC()->session->checkout_add_ons = $session_data;
	}


	/**
	 * Clear WC Checkout Add-Ons data from session
	 *
	 * @since 1.0
	 */
	public function clear_session() {

		unset( WC()->session->checkout_add_ons );
	}


	/**
	 * Store the fact that checkout order review is being displayed
	 *
	 * This helps us to limit the use of esc_html filter for appending
	 * add-on values to names in checkout order review area
	 *
	 * @since 1.0
	 */
	public function indicate_checkout_order_review( $template_name ) {

		if ( 'checkout/review-order.php' === $template_name ) {
			$this->is_checkout_order_review = true;
		}
	}


	/**
	 * Indicate that we are not in order review area anymore
	 *
	 * @since 1.0
	 */
	public function clear_checkout_order_review_indicator( $template_name ) {

		if ( 'checkout/review-order.php' === $template_name ) {
			$this->is_checkout_order_review = false;
		}
	}


	/**
	 * Display add-on values in order review area
	 *
	 * Works by filtering the esc_html'ed name of the add-on/fee
	 * and appending the add-on value to the name for display
	 * purposes only
	 *
	 * @since 1.0
	 * @param string $safe_text
	 * @param string $text
	 * @return string $safe_text
	 */
	public function display_add_on_value_in_checkout_order_review( $safe_text, $text ) {

		// Bail out if not in checkout order review area
		if ( ! $this->is_checkout_order_review ) {
			return $safe_text;
		}

		$text = sanitize_title( $text );

		if ( isset( WC()->session->checkout_add_ons['fees'][ $text ] ) ) {

			$session_data = WC()->session->checkout_add_ons['fees'][ $text ];

			// Get add-on value from session and set it for add-on
			$add_on = wc_checkout_add_ons()->get_add_on( $session_data['id'] );

			// Format add-on value
			$value = $add_on->normalize_value( $session_data['value'], true );

			// Append value to add-on name
			if ( $value ) {

				if ( 'text' === $add_on->type || 'textarea' === $add_on->type ) {
					$value = $add_on->truncate_label( $value );
				}

				$safe_text .= $this->label_separator . $value;
			}
		}

		return $safe_text;
	}


	/**
	 * Save meta data for add-on fees
	 *
	 * @since 1.0
	 * @param string $order_id the order identifier
	 * @param string $item_id the order item identifier
	 * @param object $fee fee object
	 */
	public function save_add_on_meta( $order_id, $item_id, $fee ) {

		if ( isset( WC()->session->checkout_add_ons['fees'][ $fee->id ] ) ) {

			$session_data = WC()->session->checkout_add_ons['fees'][ $fee->id ];

			// Get add-on value(s) from session
			$add_on = wc_checkout_add_ons()->get_add_on( $session_data['id'] );
			$value = $session_data['value'];

			// Special formatting and sanitization for textareas.
			if ( 'textarea' === $add_on->type ) {

				// Textareas get no label - it will be generated when displayed
				$label = '';

				$value = trim( $value );

				// Standardize OSX & Windows line breaks.
				$value = str_replace( array( "\r\n", "\r" ), "\n", $value );

				// Strip more than two contiguous line breaks.
				$value = preg_replace( "/\n\n+/", "\n\n", $value );

				// Sanitize each line.
				$value = array_map( 'wc_clean', explode( "\n", $value ) );

				$value = implode( "\n", $value );

			} else if ( 'file' === $add_on->type ) {

				// Files get no label - it will be generated when displayed
				$label = '';

				$files = explode( ',', $value );

				foreach ( $files as $file_id )	{

					// Attach the file to the order
					wp_update_post( array(
						'ID'          => $file_id,
						'post_parent' => $order_id,
					) );

				}

			} else {

				// Sanitize add-on value(s) for saving
				// `urldecode()` is used because `woocommerce_form_field()` escapes values with `esc_attr()`
				// and could mangle strings with special or accented characters
				$value = is_array( $value ) ? array_map( 'wc_clean', array_map( 'urldecode', $value ) ) : wc_clean( urldecode( $value ) );

				// Label(s) - for select, radio, etc
				$label = $add_on->normalize_value( $value, false );
			}

			// Save add-on meta: id, label(s), value(s)
			wc_add_order_item_meta( $item_id, '_wc_checkout_add_on_id', $add_on->id );
			wc_add_order_item_meta( $item_id, '_wc_checkout_add_on_value', $value );
			wc_add_order_item_meta( $item_id, '_wc_checkout_add_on_label', $label );
		}
	}


	/**
	 * Add checkout add-on meta to order row label for display purposes in
	 * my-account/view-order and order emails.
	 *
	 * @since 1.0
	 * @param array $total_rows
	 * @param object $order
	 * @return array $total_rows
	 */
	public function append_order_add_on_fee_meta( $total_rows, $order ) {

		foreach ( $total_rows as $row_key => $row ) {

			$parts = explode( '_', $row_key );
			$item_type = $parts[0];
			$item_id = isset( $parts[1] ) ? $parts[1] : null;

			if ( 'fee' === $item_type ) {

				$add_on_id = wc_get_order_item_meta( $item_id, '_wc_checkout_add_on_id' );

				if ( $add_on_id ) {

					$value = wc_get_order_item_meta( $item_id, '_wc_checkout_add_on_value' );
					$label = wc_get_order_item_meta( $item_id, '_wc_checkout_add_on_label' );

					$add_on = wc_checkout_add_ons()->get_add_on( $add_on_id );

					// make sure this isn't a subscription before adding the value HTML, as the Subscriptions template runs this through esc_html()
					if ( ! wc_checkout_add_ons()->is_subscriptions_active() || ! wcs_is_subscription( $order ) ) {

						if ( 'textarea' === $add_on->type ) {
							$label = '<div class="textarea-value">' . $add_on->normalize_value( $value, false ) . '</div>';
						} elseif ( ! $label && 'file' === $add_on->type ) {
							$label = $add_on->normalize_value( $value, false );
						}
					}

					if ( $label ) {
						$label = $this->label_separator . ( is_array( $label ) ? implode( ', ', $label ) : $label );
					}

					$total_rows[ $row_key ]['label'] .= $label;
				}
			}
		}

		return $total_rows;
	}


	/**
	 * Include free order add-on fee meta
	 *
	 * @since 1.0
	 * @param bool $excl_free true is free item meta should be excluded
	 * @param int $item_id the item meta id
	 * @return bool $excl_free
	 */
	public function include_free_order_add_on_fee_meta( $excl_free, $item_id ) {

		$excl_free = wc_get_order_item_meta( $item_id, '_wc_checkout_add_on_id' ) ? false : $excl_free;

		return $excl_free;
	}


	/**
	 * Replace some characters lost from select/multiselect/radio value sanitization.
	 *
	 * @since 1.6.1
	 * @param string $title The sanitized value.
	 * @param string $raw_title The raw value.
	 * @param string $context The context for which the title is being sanitized.
	 * @return string $title The sanitized value with special handling.
	 */
	public function sanitize_select_field_values( $title, $raw_title, $context ) {

		if ( 'wc_checkout_add_ons_sanitize' !== $context ) {
			return $title;
		}

		$title = remove_accents( $title );

		// If the value is a negative, add the leading dash back
		if ( is_numeric( $raw_title ) && SV_WC_Helper::str_starts_with( $raw_title, '-' ) ) {
			$title = '-' . $title;
		}

		return $title;
	}


	/**
	 * Get the supported MIME types for file upload.
	 *
	 * @since 1.7.1
	 * @return array
	 */
	protected function get_mime_types() {

		$extensions = $types = array();

		$allowed_types = get_allowed_mime_types();

		// break the allowed extensions into their respective types
		foreach ( $allowed_types as $allowed_extensions => $type ) {

			$type = substr( $type, 0, strpos( $type, '/' ) );

			$extensions[ $type ][] = str_replace( '|', ',', $allowed_extensions );
		}

		// format the extensions for plupload
		foreach ( $extensions as $type => $exts ) {

			$types[] = array(
				'title'       => $type,
				'extensions' => implode( ',', $exts ),
			);
		}

		/**
		 * Filter the allowed upload mime types.
		 *
		 * @since 1.7.1
		 * @param array $types the allowed types and their extensions, each an array of {
		 *
		 *     @type string $name       the mime type name
		 *     @type string $extensions the supported extensions, comma separated
		 * }
		 */
		return apply_filters( 'wc_checkout_add_ons_mime_types', $types );
	}


} // end \WC_Checkout_Add_Ons_Frontend class

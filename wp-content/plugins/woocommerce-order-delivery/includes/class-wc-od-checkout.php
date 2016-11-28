<?php
/**
 * Class to handle the plugin behaviour in the checkout page
 *
 * @author     WooThemes
 * @package    WC_OD
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_OD_Checkout' ) ) {

	class WC_OD_Checkout {

		/**
		 * The first allowed date for ship an order.
		 *
		 * Calculate this data is a heavy process, so we defined this property
		 * to store the value and execute the process just one time per request.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var int A timestamp representing the first allowed date to ship an order.
		 */
		private $first_shipping_date;

		/**
		 * The first allowed date for deliver an order.
		 *
		 * Calculate this data is a heavy process, so we defined this property
		 * to store the value and execute the process just one time per request.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var int A timestamp representing the first allowed date to deliver an order.
		 */
		private $first_delivery_date;


		/**
		 * Initializes the class.
		 *
		 * @since 1.0.0
		 * @staticvar WC_OD_Checkout $instance The *Singleton* instances of this class.
		 * @return WC_OD_Checkout The *Singleton* instance.
		 */
		public static function instance() {
			static $instance = null;
			if ( null === $instance ) {
				$instance = new self();
			}

			return $instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function __construct() {
			// WP Hooks.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// WooCommerce hooks.
			add_action( 'woocommerce_checkout_shipping', array( $this, 'checkout_content' ), 99 );
			add_action( 'woocommerce_checkout_process', array( $this, 'validate_delivery_date' ) );
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'update_order_meta' ) );

			// Delivery date validation hooks.
			add_filter( 'wc_od_validate_delivery_date', array( $this, 'validate_delivery_day' ), 10, 2 );
			add_filter( 'wc_od_validate_delivery_date', array( $this, 'validate_minimum_days' ), 10, 2 );
			add_filter( 'wc_od_validate_delivery_date', array( $this, 'validate_maximum_days' ), 10, 2 );
			add_filter( 'wc_od_validate_delivery_date', array( $this, 'validate_no_events' ), 10, 2 );
		}

		/**
		 * Throw error on object clone.
		 *
		 * @since 1.0.0
		 * @access private
		 */
		private function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-order-delivery' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since 1.0.0
		 * @access private
		 */
		private function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-order-delivery' ), '1.0.0' );
		}

		/**
		 * Enqueue scripts.
		 *
		 * @since 1.0.0
		 * @global WooCommerce $woocommerce The WooCommerce instance.
		 */
		public function enqueue_scripts() {
			global $woocommerce;

			// It's the checkout page.
			if ( is_checkout() && $woocommerce->cart->needs_shipping() && 'calendar' === WC_OD()->settings()->get_setting( 'checkout_delivery_option' ) ) {
				$disable_week_days = array_keys( wc_od_get_days_by( WC_OD()->settings()->get_setting( 'delivery_days' ), 'enabled', '0' ) );
				$min_delivery_days = $this->min_delivery_days();
				$max_delivery_days = $this->max_delivery_days() - $min_delivery_days;
				$events = wc_od_get_events( array(
					'type'  => 'delivery',
					'start' => date( 'Y-m-d', strtotime( "today +{$min_delivery_days} days" ) ),
					'end'   => date( 'Y-m-d', strtotime( "tomorrow +{$max_delivery_days} days" ) ),
				) );

				wp_enqueue_style( 'bootstrap-datepicker', WC_OD()->dir_url . 'assets/css/lib/bootstrap-datepicker.css' );
				wp_enqueue_script( 'bootstrap-datepicker', WC_OD()->dir_url . 'assets/js/lib/bootstrap-datepicker.min.js', array( 'jquery' ), '1.3.0', true );
				wp_enqueue_script( 'wc-od-checkout', WC_OD()->dir_url . 'assets/js/wc-od-checkout.js', array( 'jquery', 'bootstrap-datepicker' ), false, true );
				wp_localize_script( 'wc-od-checkout', 'wc_od_checkout_l10n', array(
					'language'        => get_bloginfo( 'language' ),
					'format'          => WC_OD()->date_format_js,
					'weekStart'       => get_option( 'start_of_week', 0 ),
					'disableWeekDays' => $disable_week_days,
					'minDeliveryDays' => $min_delivery_days,
					'maxDeliveryDays' => $max_delivery_days,
					'events'          => $events,
				) );
			}
		}

		/**
		 * Gets the delivery date field arguments.
		 *
		 * @since 1.0.1
		 *
		 * @return array An array with the delivery date field arguments.
		 */
		public function get_delivery_date_field_args() {
			/**
			 * Filters the arguments for the delivery date field.
			 *
			 * @since 1.0.0
			 *
			 * @param array $args The arguments for the delivery date field.
			 */
			return apply_filters( 'wc_od_delivery_date_field_args', array(
				'type'        => 'text',
				'label'       => __( 'Pick a delivery Date', 'woocommerce-order-delivery' ),
				'placeholder' => WC_OD()->date_format_js,
				'class'       => array( 'form-row-wide' ),
				'required'    => false,
				'return'      => true,
			) );
		}

		/**
		 * Adds the custom content to the checkout form.
		 *
		 * @since 1.0.0
		 * @global WooCommerce $woocommerce The WooCommerce instance.
		 */
		public function checkout_content() {
			global $woocommerce;

			if ( is_checkout() && $woocommerce->cart->needs_shipping() ) {

				$checkout = $woocommerce->checkout();
				$args = array(
					'checkout'            => $checkout,
					'delivery_date_field' => woocommerce_form_field( 'delivery_date', $this->get_delivery_date_field_args(), $checkout->get_value( 'delivery_date' ) ),
					'delivery_option'     => WC_OD()->settings()->get_setting( 'checkout_delivery_option' ),
					'shipping_date'       => wc_od_localize_date( $this->get_first_shipping_date() ),
					'delivery_range'      => WC_OD()->settings()->get_setting( 'delivery_range' ),
				);

				wc_od_get_template( 'checkout/form-delivery-date.php', $args );
			}
		}

		/**
		 * Validates the delivery date field on the checkout process.
		 *
		 * @since 1.0.0
		 */
		public function validate_delivery_date() {
			$field_args = $this->get_delivery_date_field_args();

			// Validation: Required field.
			if ( isset( $field_args['required'] ) && $field_args['required'] && ( ! isset( $_POST['delivery_date'] ) || '' === $_POST['delivery_date'] ) ) {

				wc_add_notice( __( '<strong>Delivery Date</strong> is a required field.', 'woocommerce-order-delivery' ), 'error' );

			} elseif ( isset( $_POST['delivery_date'] ) && '' !== $_POST['delivery_date'] ) {

				$valid = true;
				$delivery_timestamp = strtotime( $_POST['delivery_date'] );

				if ( false !== $delivery_timestamp ) {
					/**
					 * Filters the delivery date validation.
					 *
					 * @since 1.0.0
					 *
					 * @param boolean $valid              Is the delivery date valid?.
					 * @param int     $delivery_timestamp The delivery date timestamp.
					 */
					$valid = apply_filters( 'wc_od_validate_delivery_date', $valid, $delivery_timestamp );
				}

				if ( ! $valid ) {
					wc_add_notice( __( '<strong>Delivery Date</strong> is not a valid date.', 'woocommerce-order-delivery' ), 'error' );
				}

			}
		}

		/**
		 * Validates if the day of the week is enabled for the delivery.
		 *
		 * @since 1.0.0
		 *
		 * @param boolean $valid              Is valid the delivery date?
		 * @param int     $delivery_timestamp The delivery date timestamp.
		 * @return boolean True if the delivery date is valid. False otherwise.
		 */
		public function validate_delivery_day( $valid, $delivery_timestamp ) {
			if ( $valid ) {
				$delivery_date = getdate( $delivery_timestamp );
				$delivery_days = WC_OD()->settings()->get_setting( 'delivery_days' );

				$valid = $delivery_days[ $delivery_date['wday'] ]['enabled'];
			}

			return $valid;
		}

		/**
		 * Validates if the minimum days for the delivery is satisfied.
		 *
		 * @since 1.0.0
		 *
		 * @param boolean $valid              Is valid the delivery date?
		 * @param int     $delivery_timestamp The delivery date timestamp.
		 * @return boolean True if the delivery date is valid. False otherwise.
		 */
		public function validate_minimum_days( $valid, $delivery_timestamp ) {
			if ( $valid ) {
				$min_delivery_days = $this->min_delivery_days();
				$min_delivery_timestamp = strtotime( "today + {$min_delivery_days} days" );

				$valid = ( $delivery_timestamp >= $min_delivery_timestamp );
			}

			return $valid;
		}

		/**
		 * Validates if the maximum days for the delivery is satisfied.
		 *
		 * @since 1.0.0
		 *
		 * @param boolean $valid              Is valid the delivery date?
		 * @param int     $delivery_timestamp The delivery date timestamp.
		 * @return boolean True if the delivery date is valid. False otherwise.
		 */
		public function validate_maximum_days( $valid, $delivery_timestamp ) {
			if ( $valid ) {
				$max_delivery_days = $this->max_delivery_days();
				$max_delivery_timestamp = strtotime( "today + {$max_delivery_days} days" );

				$valid = ( $delivery_timestamp < $max_delivery_timestamp );
			}

			return $valid;
		}

		/**
		 * Validates that not exists events for the delivery date.
		 *
		 * @since 1.0.0
		 *
		 * @param boolean $valid              Is valid the delivery date?
		 * @param int     $delivery_timestamp The delivery date timestamp.
		 * @return boolean True if the delivery date is valid. False otherwise.
		 */
		public function validate_no_events( $valid, $delivery_timestamp ) {
			if ( $valid ) {
				$address_type = ( isset( $_POST['ship_to_different_address'] ) ? 'shipping' : 'billing' );

				$country = '';
				if ( isset( $_POST["{$address_type}_country"] ) ) {
					$country = strtoupper( sanitize_title( $_POST["{$address_type}_country"] ) );
				}

				$state = '';
				if ( isset( $_POST["{$address_type}_state"] ) ) {
					$state = strtoupper( sanitize_title( $_POST["{$address_type}_state"] ) );
				}

				$delivery_events = wc_od_get_events( array(
					'type'    => 'delivery',
					'start'   => date( 'Y-m-d', $delivery_timestamp ),
					'end'     => date( 'Y-m-d', strtotime( '+1 day', $delivery_timestamp ) ),
					'country' => $country,
					'state'   => $state,
				) );

				$valid = empty( $delivery_events );
			}

			return $valid;
		}

		/**
		 * Updates the order post meta.
		 *
		 * @since 1.0.0
		 * @param int $order_id The order ID.
		 */
		public function update_order_meta( $order_id ) {
			if ( isset( $_POST['delivery_date'] ) && $_POST['delivery_date'] ) {
				// Stores the date in the ISO 8601 format.
				$delivery_date = wc_od_localize_date( esc_attr( $_POST['delivery_date'] ), 'Y-m-d' );
				if ( $delivery_date ) {
					update_post_meta( $order_id, '_delivery_date', $delivery_date );
				}
			}
		}

		/**
		 * Gets the first day to ship the orders.
		 *
		 * @since 1.0.0
		 * @return int A timestamp representing the first allowed date to ship the orders.
		 */
		public function get_first_shipping_date() {
			if ( $this->first_shipping_date ) {
				return $this->first_shipping_date;
			}

			$first_shipping_date = null;

			$min_working_days = WC_OD()->settings()->get_setting( 'min_working_days' );
			$shipping_days    = WC_OD()->settings()->get_setting( 'shipping_days' );

			$days_for_shipping = 0;
			$today             = strtotime( 'today' );
			$wday              = date( 'w' );

			do {
				// Allowed shipping day.
				if ( $shipping_days[ $wday ]['enabled'] ) {
					$timestamp = strtotime( "today +{$days_for_shipping} days" );
					$shipping_events = wc_od_get_events( array(
						'type'  => 'shipping',
						'start' => date( 'Y-m-d', $timestamp ),
						'end'   => date( 'Y-m-d', strtotime( '+1 day', $timestamp ) ),
					) );

					// No events for this day.
					if ( ! $shipping_events ) {
						// Checks the time parameter only for the current day.
						if ( 0 === $days_for_shipping ) {
							if ( $shipping_days[ $wday ]['time'] ) {
								$limit = preg_split( '/:/', $shipping_days[ $wday ]['time'] );
								// We can start to process the order today.
								if ( current_time( 'timestamp' ) < strtotime( "today + {$limit[0]} hours {$limit[1]} minutes" ) ) {
									$first_shipping_date = $today;
								}
							} else {
								// We can start to process the order today.
								$first_shipping_date = $today;
							}
						} else {
							$first_shipping_date = $timestamp;
						}

						// Not found yet.
						if ( 0 < $min_working_days ) {
							$first_shipping_date = null;
						}

						// Weekday.
						$min_working_days--;
					}
				}

				$days_for_shipping++;
				$wday = ( ( $wday + 1 ) % 7 );
			} while ( ! $first_shipping_date );

			$this->first_shipping_date = $first_shipping_date;

			return $first_shipping_date;
		}

		/**
		 * Gets the first day to deliver the orders.
		 *
		 * @since 1.0.0
		 * @return int A timestamp representing the first allowed date to deliver the orders.
		 */
		public function get_first_delivery_date() {
			if ( $this->first_delivery_date ) {
				return $this->first_delivery_date;
			}

			$first_delivery_date = null;
			$first_shipping_date = $this->get_first_shipping_date();

			$delivery_days       = WC_OD()->settings()->get_setting( 'delivery_days' );
			$delivery_range      = WC_OD()->settings()->get_setting( 'delivery_range' );
			$min_delivery_days   = $delivery_range['min'];

			$seconds_in_a_day  = 86400;
			$days_for_delivery = ( ( $first_shipping_date - strtotime( 'today' ) ) / $seconds_in_a_day );
			$wday              = date( 'w', $first_shipping_date );

			do {
				$timestamp = strtotime( "today +{$days_for_delivery} days" );

				/*
				 * Special Case: The current date is the shipping date and the minimum delivery days is higher than zero.
				 * We do not deliver this day because it is disabled. But it is a working day for the shipping company.
				 */
				if ( $delivery_days[ $wday ]['enabled'] || ( $first_shipping_date === $timestamp && 0 < $min_delivery_days ) ) {
					// Events for all countries.
					$delivery_events = wc_od_get_events( array(
						'type'    => 'delivery',
						'start'   => date( 'Y-m-d', $timestamp ),
						'end'     => date( 'Y-m-d', strtotime( '+1 day', $timestamp ) ),
						'country' => '',
					) );

					// No events for this day.
					if ( ! $delivery_events ) {
						if ( 0 >= $min_delivery_days ) {
							$first_delivery_date = $timestamp;
						}

						// Weekday.
						$min_delivery_days--;
					}
				}

				$days_for_delivery++;
				$wday = ( ( $wday + 1 ) % 7 );
			} while ( ! $first_delivery_date );

			$this->first_delivery_date = $first_delivery_date;

			return $first_delivery_date;
		}

		/**
		 * Gets the minimum days for delivery.
		 *
		 * @since 1.0.0
		 * @return int The minimum days for delivery.
		 */
		public function min_delivery_days() {
			$seconds_in_a_day  = 86400;
			$min_delivery_days = ( ( $this->get_first_delivery_date() - strtotime( 'today' ) ) / $seconds_in_a_day );

			/**
			 * Filters the minimun days for delivery.
			 *
			 * @since 1.0.0
			 * @param int $min_delivery_days The minimum days for delivery.
			 */
			$min_delivery_days = apply_filters( 'wc_od_min_delivery_days', $min_delivery_days );

			return intval( $min_delivery_days );
		}

		/**
		 * Gets the maximum days for delivery.
		 *
		 * @since 1.0.0
		 * @return int The maximum days for delivery.
		 */
		public function max_delivery_days() {
			/**
			 * Filters the maximum days for delivery.
			 *
			 * @since 1.0.0
			 * @param int $max_delivery_days The maximum days for delivery.
			 */
			$max_delivery_days = apply_filters( 'wc_od_max_delivery_days', WC_OD()->settings()->get_setting( 'max_delivery_days' ) );

			return intval( $max_delivery_days );
		}
	}
}

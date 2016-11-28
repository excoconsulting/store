<?php
/**
 * Admin Settings
 *
 * @author     WooThemes
 * @package    WC_OD
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_OD_Admin_Settings' ) ) {
	/**
	 * WC_OD_Admin_Settings Class
	 */
	class WC_OD_Admin_Settings {

		/**
		 * The setting errors registered during validation.
		 *
		 * @since 1.0.0
		 * @access protected
		 * @var array The setting errors.
		 */
		protected $errors = array();


		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @global string $current_tab The current tab.
		 * @global string $current_section The current section.
		 */
		public function __construct() {
			global $current_tab, $current_section;

			// The $current_tab and $current_section global variables are initialized after.
			if ( null === $current_tab ) {
				$current_tab = wc_od_get_query_arg( 'tab' );
				if ( ! $current_tab ) {
					$current_tab = 'general';
				}
			}

			if ( null === $current_section ) {
				$current_section = wc_od_get_query_arg( 'section' );
			}

			$this->includes();

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );

			// WooCommerce settings menu.
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'rename_shipping_tab' ), 25 );
			add_filter( 'woocommerce_get_sections_shipping', array( $this, 'add_shipping_sections' ) );

			// WooCommerce settings hooks.
			add_filter( 'woocommerce_shipping_settings', array( $this, 'add_shipping_settings' ) );
			add_action( 'woocommerce_settings_shipping', array( $this, 'output' ) );

			// Custom fields display.
			add_action( 'woocommerce_admin_field_wc_od_shipping_days', 'wc_od_field_wrapper' );
			add_action( 'woocommerce_admin_field_wc_od_delivery_days', 'wc_od_field_wrapper' );
			add_action( 'woocommerce_admin_field_wc_od_day_range', 'wc_od_field_wrapper' );
			add_action( 'woocommerce_admin_field_wc_od_calendar', 'wc_od_calendar_field' );

			// Custom fields save.
			add_action( 'woocommerce_update_option_wc_od_shipping_days', array( $this, 'save_field' ) );
			add_action( 'woocommerce_update_option_wc_od_delivery_days', array( $this, 'save_field' ) );
			add_action( 'woocommerce_update_option_wc_od_day_range', array( $this, 'save_field' ) );
		}

		/**
		 * Gets if we are in the shipping tab of the settings page.
		 *
		 * @since 1.0.0
		 *
		 * @global string $current_tab The current tab.
		 *
		 * @return boolean True if the current setting tab is 'shipping'. False otherwise.
		 */
		public function is_shipping_tab() {
			global $current_tab;

			return ( 'shipping' === $current_tab );
		}

		/**
		 * Gets if the current settings section has a calendar.
		 *
		 * @since 1.0.0
		 *
		 * @global string $current_section The current section.
		 *
		 * @return boolean True if the current section has a calendar. False otherwise.
		 */
		public function is_calendar_section() {
			global $current_section;

			return ( 'shipping_calendar' === $current_section || 'delivery_calendar' === $current_section );
		}

		/**
		 * Includes the necessary files.
		 *
		 * @since 1.0.0
		 */
		public function includes() {
			if ( $this->is_shipping_tab() ) {
				if ( $this->is_calendar_section() ) {
					include_once( 'wc-od-admin-calendars-settings.php' );
				} else {
					include_once( 'wc-od-admin-general-settings.php' );
				}

				/**
				 * Includes the necessary files.
				 *
				 * @since 1.0.0
				 */
				do_action( 'wc_od_settings_includes' );
			}
		}

		/**
		 * Enqueues the settings scripts.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_scripts() {
			if ( $this->is_shipping_tab() ) {
				if ( $this->is_calendar_section() ) {

					if ( version_compare( WC_OD_Utils::get_woocommerce_version(), '2.3', '<' ) ) {
						wp_enqueue_style( 'select2', WC_OD()->dir_url . 'assets/css/lib/select2.css' );
						wp_enqueue_script( 'select2', WC_OD()->dir_url . 'assets/js/lib/select2.min.js', array( 'jquery' ), '3.5.2', true );

						// Use a higher version of the jquery-blockui script.
						wp_deregister_script( 'jquery-blockui' );
						wp_enqueue_script( 'jquery-blockui', WC_OD()->dir_url . 'assets/js/lib/jquery.blockUI.min.js', array( 'jquery' ), '2.70', true );
					}

					wp_enqueue_style( 'bootstrap-datepicker', WC_OD()->dir_url . 'assets/css/lib/bootstrap-datepicker.css' );
					wp_enqueue_style( 'fullcalendar', WC_OD()->dir_url . 'assets/css/lib/fullcalendar.css' );
					wp_enqueue_style( 'tooltipster', WC_OD()->dir_url . 'assets/css/lib/tooltipster.css' );

					wp_enqueue_script( 'jquery-ui-dialog' );
					wp_enqueue_script( 'bootstrap-datepicker', WC_OD()->dir_url . 'assets/js/lib/bootstrap-datepicker.min.js', array( 'jquery' ), '1.3.0', true );
					wp_enqueue_script( 'tooltipster', WC_OD()->dir_url . 'assets/js/lib/jquery.tooltipster.min.js', array( 'jquery' ), '3.3.0', true );

					wp_enqueue_script( 'moment', WC_OD()->dir_url . 'assets/js/lib/moment.min.js', array(), '2.9.0', true );
					wp_enqueue_script( 'fullcalendar', WC_OD()->dir_url . 'assets/js/lib/fullcalendar.min.js', array( 'jquery', 'moment' ), '2.3.0', true );
					wp_enqueue_script( 'wc-od-calendar', WC_OD()->dir_url . 'assets/js/wc-od-calendar.js', array( 'jquery' ), '1.0.0', true );
				}

				wp_enqueue_style( 'jquery-timepicker', WC_OD()->dir_url . 'assets/css/lib/jquery.timepicker.css' );
				wp_enqueue_script( 'jquery-timepicker', WC_OD()->dir_url . 'assets/js/lib/jquery.timepicker.min.js', array( 'jquery' ), '1.6.0', true );

				wp_enqueue_style( 'wc-od-settings', WC_OD()->dir_url . 'assets/css/wc-od-settings.css' );
				wp_enqueue_script( 'wc-od-settings', WC_OD()->dir_url . 'assets/js/wc-od-settings.js', array( 'jquery' ), false, true );
				wp_localize_script( 'wc-od-settings', 'wc_od_settings_l10n', $this->localize_settings_script() );

			}
		}

		/**
		 * Adds localization to the wc-od-settings.js script.
		 *
		 * @since 1.0.0
		 *
		 * @global string $current_section The current section.
		 *
		 * @return array The localized parameters.
		 */
		public function localize_settings_script() {
			global $current_section;

			$wc_od_settings_l10n = array();
			if ( $this->is_calendar_section() ) {
				// Gets the events type.
				$event_type = str_replace( '_calendar', '', $current_section );
				if ( ! $event_type ) {
					$event_type = 'shipping';
				}

				// Shipping events use the defaults callables.
				$callable_type = ( 'shipping' === $event_type ? 'event' : $event_type );

				// BlockUI.js texts. Use spin for WooCommerce >= 2.3.
				$block_ui_texts = array( 'loading' => '', 'saving' => '', 'deleting' => '' );
				if ( version_compare( WC_OD_Utils::get_woocommerce_version(), '2.3', '<' ) ) {
					$block_ui_texts = array(
						'loading'  => __( 'Loading events &hellip;', 'woocommerce-order-delivery' ),
						'saving'   => __( 'Saving &hellip;', 'woocommerce-order-delivery' ),
						'deleting' => __( 'Deleting &hellip;', 'woocommerce-order-delivery' ),
					);
				}

				// Defines the localization parameters.
				$wc_od_settings_l10n = array(
					'language'            => get_bloginfo( 'language' ),
					'weekStart'           => get_option( 'start_of_week', 0 ),
					'eventsType'          => $event_type,
					'loadingText'         => $block_ui_texts['loading'],
					'modalContent'        => call_user_func( "wc_od_{$callable_type}_modal_content" ),
					'eventTooltipContent' => call_user_func( "wc_od_{$callable_type}_tooltip_content" ),
					'modalTexts'          => array(
						'add'      => __( 'Add event', 'woocommerce-order-delivery' ),
						'edit'     => __( 'Edit event', 'woocommerce-order-delivery' ),
						'delete'   => __( 'Are you sure you want to delete this event?', 'woocommerce-order-delivery' ),
						'saving'   => $block_ui_texts['saving'],
						'deleting' => $block_ui_texts['deleting'],
					),
				);

				if ( 'delivery' === $event_type ) {
					$wc_od_settings_l10n['countryStates'] = json_encode( wc_od_get_country_states_for_select2() );
				}
			}

			/**
			 * Filters the localized parameters for the wc-od-settings.js script.
			 *
			 * @since 1.0.0
			 *
			 * @param array $wc_od_settings_l10n The default parameters to filter.
			 */
			return apply_filters( 'wc_od_settings_l10n', $wc_od_settings_l10n );
		}

		/**
		 * Renames the shipping tab.
		 *
		 * @since 1.0.0
		 *
		 * @param array $tabs The WooCommerce settings tabs.
		 * @return array The filtered WooCommerce settings tabs.
		 */
		public function rename_shipping_tab( $tabs ) {
			$tabs['shipping'] = __( 'Shipping & Delivery', 'woocommerce-order-delivery' );

			return $tabs;
		}

		/**
		 * Adds sections to the shipping tab.
		 *
		 * @since 1.0.0
		 *
		 * @param array $sections The shipping sections.
		 * @return array The filtered shipping sections.
		 */
		public function add_shipping_sections( $sections ) {
			// Move the calendars after the "Shipping Classes" section for WC 4.6+.
			$index = ( version_compare( WC_OD_Utils::get_woocommerce_version(), '2.6', '<' ) ? 1 : 3 );

			return array_merge(
				array_slice( $sections, 0, $index ),
				array(
					'shipping_calendar' => __( 'Shipping Calendar', 'woocommerce-order-delivery' ),
					'delivery_calendar' => __( 'Delivery Calendar', 'woocommerce-order-delivery' ),
				),
				array_slice( $sections, $index )
			);
		}

		/**
		 * Adds the shipping settings.
		 *
		 * @since 1.0.0
		 *
		 * @global $current_section The current section.
		 *
		 * @param array $settings The shipping settings.
		 * @return boolean The shipping settings.
		 */
		public function add_shipping_settings( $settings ) {
			global $current_section;

			return array_merge( $settings, $this->get_settings( $current_section ) );
		}

		/**
		 * Gets settings array.
		 *
		 * @since 1.0.0
		 *
		 * @param string $current_section The current section.
		 * @return array An array with the settings.
		 */
		public function get_settings( $current_section = '' ) {
			$settings = array();
			$section = ( WC_OD_Utils::get_shipping_options_section_slug() === $current_section ? 'shipping' : $current_section );

			if ( 'shipping' === $section ) {

				$settings = array(

					array(
						'id'    => 'shipping_options_extended',
						'type'  => 'title',
					),

					array(
						'id'       => wc_od_maybe_prefix( 'min_working_days' ),
						'title'    => __( 'Minimum working days', 'woocommerce-order-delivery' ),
						'desc'     => __( 'The minimum number of days it takes you to process an order.', 'woocommerce-order-delivery' ),
						'type'     => 'number',
						'default'  => WC_OD()->settings()->get_default( 'min_working_days' ),
						'css'      => 'width:50px;',
						'desc_tip' => true,
						'custom_attributes' => array(
							'min'  => 0,
							'step' => 1,
						)
					),

					array(
						'id'       => wc_od_maybe_prefix( 'shipping_days' ),
						'title'    => __( 'Shipping days', 'woocommerce-order-delivery' ),
						'desc'     => __( 'Choose the shipping days and their time limit to ship orders. You can set the time limit to process an order on the same day.', 'woocommerce-order-delivery' ),
						'type'     => 'wc_od_shipping_days',
						'default'  => WC_OD()->settings()->get_default( 'shipping_days' ),
						'desc_tip' => true,
					),

					array(
						'id'   => 'shipping_options_extended',
						'type' => 'sectionend',
					),

					array(
						'id'    => 'delivery_options',
						'title' => __( 'Delivery Options', 'woocommerce-order-delivery' ),
						'type'  => 'title',
					),

					array(
						'id'       => wc_od_maybe_prefix( 'delivery_range' ),
						'title'    => __( 'Delivery range', 'woocommerce-order-delivery' ),
						'desc'     => __( 'Interval of days it takes your shipping company to deliver an order after submitting the order to them.', 'woocommerce-order-delivery' ),
						'type'     => 'wc_od_day_range',
						'default'  => WC_OD()->settings()->get_default( 'delivery_range' ),
						'css'      => 'width:50px;',
						'desc_tip' => true,
						'custom_attributes' => array(
							'min'  => 0,
							'step' => 1,
						)
					),

					array(
						'id'       => wc_od_maybe_prefix( 'checkout_delivery_option' ),
						'title'    => __( 'Checkout options', 'woocommerce-order-delivery' ),
						'desc'     => __( 'Choose the delivery date option to be displayed on the checkout page.', 'woocommerce-order-delivery' ),
						'type'     => 'radio',
						'desc_tip' => true,
						'default'  => WC_OD()->settings()->get_default( 'checkout_delivery_option' ),
						'options'  => array(
							'text'     => __( 'A text block with information about shipping and delivery', 'woocommerce-order-delivery' ),
							'calendar' => __( 'A calendar to let the customer to choose a delivery date', 'woocommerce-order-delivery' ),
						),
					),

					array(
						'id'       => wc_od_maybe_prefix( 'delivery_days' ),
						'title'    => __( 'Delivery days', 'woocommerce-order-delivery' ),
						'desc'     => __( 'Choose the available days to deliver orders.', 'woocommerce-order-delivery' ),
						'type'     => 'wc_od_delivery_days',
						'default'  => WC_OD()->settings()->get_default( 'delivery_days' ),
						'desc_tip' => true,
					),

					array(
						'id'       => wc_od_maybe_prefix( 'max_delivery_days' ),
						'title'    => __( 'Maximum delivery range', 'woocommerce-order-delivery' ),
						'desc'     => __( 'Maximum days that the customer can choose from, starting on the current date, to receive the order.', 'woocommerce-order-delivery' ),
						'type'     => 'number',
						'default'  => WC_OD()->settings()->get_default( 'max_delivery_days' ),
						'css'      => 'width:50px;',
						'desc_tip' => true,
						'custom_attributes' => array(
							'min'  => 0,
							'step' => 1,
						)
					),

					array(
						'id'   => 'delivery_options',
						'type' => 'sectionend',
					),

				);

			} elseif ( 'shipping_calendar' === $current_section ) {

				$settings = array(

					array(
						'id'    => 'shipping_calendar',
						'title' => __( 'Shipping Calendar', 'woocommerce-order-delivery' ),
						'type'  => 'title',
						'desc'  => __( 'This calendar is used to overwrite the default <em>Shipping days</em> setting. Use it for example to define your non working days or holidays periods.', 'woocommerce-order-delivery' ),
					),

					array(
						'id'      => wc_od_maybe_prefix( 'shipping_events' ),
						'type'    => 'wc_od_calendar',
						'default' => WC_OD()->settings()->get_default( 'shipping_events' ),
					),

					array(
						'id'   => 'shipping_calendar',
						'type' => 'sectionend',
					),

				);

			} elseif ( 'delivery_calendar' === $current_section ) {

				$settings = array(

					array(
						'id'    => 'delivery_calendar',
						'title' => __( 'Delivery Calendar', 'woocommerce-order-delivery' ),
						'type'  => 'title',
						'desc'  => __( 'This calendar is used to overwrite the default <em>Delivery days</em> setting. Use it to disable specific delivery days.', 'woocommerce-order-delivery' ),
					),

					array(
						'id'      => wc_od_maybe_prefix( 'delivery_events' ),
						'type'    => 'wc_od_calendar',
						'default' => WC_OD()->settings()->get_default( 'delivery_events' ),
					),

					array(
						'id'   => 'delivery_calendar',
						'type' => 'sectionend',
					),

				);

			}

			/**
			 * Filters the settings.
			 *
			 * The dynamic portion of the hook name, $section, refers to the $section name.
			 *
			 * @since 1.0.0
			 *
			 * @param array $settings The settings.
			 */
			return apply_filters( "wc_od_{$section}_settings", $settings );
		}

		/**
		 * Outputs the settings.
		 *
		 * @since 1.0.0
		 *
		 * @global string $current_section The current section.
		 */
		public function output() {
			global $current_section, $hide_save_button;

			if ( $this->is_calendar_section() ) {
				// Hide the save button for the calendar sections.
				$hide_save_button = true;

				woocommerce_admin_fields( $this->get_settings( $current_section ) );
			}
		}

		/**
		 * Validate and save the setting.
		 *
		 * @since 1.0.0
		 *
		 * @param array $setting The setting data.
		 */
		public function save_field( $setting ) {
			$setting_id = wc_od_no_prefix( $setting['id'] );
			$setting_value = isset( $_POST[ $setting_id ] ) ? stripslashes_deep( $_POST[ $setting_id ] ) : null;

			switch ( $setting_id ) {
				case 'shipping_days':
				case 'delivery_days':
					$previous_value = WC_OD()->settings()->get_setting( $setting_id );
					$days_data = is_array( $setting_value ) ? $setting_value : array();
					$clean_days_data = array();
					foreach ( $previous_value as $key => $data ) {
						$day_data = ( isset( $days_data[ $key ] ) ? $days_data[ $key ] : array() );
						$clean_day_data = array(
							'enabled' => ( ( isset( $day_data['enabled'] ) && $day_data['enabled'] ) ? '1' : '0' ),
						);

						if ( 'shipping_days' === $setting_id ) {
							$time = ( ( isset( $day_data['time'] ) && $day_data['time'] ) ? $day_data['time'] : '' );
							$clean_day_data['time'] = wc_od_sanitize_time( $time );
						}

						$clean_days_data[ $key ] = $clean_day_data;
					}

					$enabled_days = wc_od_get_days_by( $clean_days_data, 'enabled', '1' );
					if ( empty( $enabled_days ) ) {
						$error_key = ( 'shipping_days' === $setting_id ? 'shipping_days_empty' : 'delivery_days_empty' );
						$this->add_setting_error( $error_key );
					} else {
						$setting_value = $clean_days_data;
					}
					break;
				case 'delivery_range':
					if ( is_null( $setting_value ) ) {
						$setting_value = array( 'min' => 0, 'max' => 0 );
					} else {
						$setting_value = array(
							'min' => ( isset( $setting_value['min'] ) ? absint( $setting_value['min'] ) : 0 ),
							'max' => ( isset( $setting_value['max'] ) ? absint( $setting_value['max'] ) : 0 ),
						);
					}
					break;
			}

			// Update the setting if no errors.
			if ( empty( $this->errors ) ) {
				WC_OD()->settings()->update_setting( $setting_id, $setting_value );
			}
		}

		/**
		 * Gets a setting error message by key.
		 *
		 * @since 1.0.0
		 *
		 * @param string $error_key Optional. The error key.
		 * @return string The error message, or an empty string if the key doesn't exists.
		 */
		public function get_setting_error_message( $error_key = '' ) {
			/**
			 * Filters the error messages.
			 *
			 * @since 1.0.0
			 *
			 * @param array $error_messages The error messages.
			 */
			$error_messages = apply_filters( 'wc_od_settings_error_messages', array(
				'shipping_days_empty' => __( 'You must check at least one shipping day.', 'woocommerce-order-delivery' ),
				'delivery_days_empty' => __( 'You must check at least one delivery day.', 'woocommerce-order-delivery' ),
			) );

			if ( $error_key ) {
				return ( isset( $error_messages[ $error_key ] ) ? $error_messages[ $error_key ] : '' );
			}

			return $error_messages;
		}

		/**
		 * Adds a setting error.
		 *
		 * @since 1.0.0
		 *
		 * @param string $error_key The error key.
		 */
		public function add_setting_error( $error_key ) {
			$error_message = $this->get_setting_error_message( $error_key );
			if ( $error_message ) {
				// Store the error key.
				$this->errors[] = $error_key;

				WC_Admin_Settings::add_error( $error_message );
			}
		}

	}
}

new WC_OD_Admin_Settings();

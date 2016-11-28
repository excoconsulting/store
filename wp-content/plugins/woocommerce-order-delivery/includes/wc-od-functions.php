<?php
/**
 * Useful functions for the plugin
 *
 * @author      WooThemes
 * @package     WC_OD
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Gets the value of the query string argument.
 *
 * @since 1.0.0
 * @param string $arg The query string argument.
 * @return mixed      The argument value.
 */
function wc_od_get_query_arg( $arg ) {
	$value = '';
	$arg = sanitize_key( $arg );
	if ( ! empty( $_POST ) && isset( $_POST['_wp_http_referer'] ) ) {
		$query_string = parse_url( $_POST['_wp_http_referer'], PHP_URL_QUERY );
		if ( $query_string ) {
			$query_args = array();
			parse_str( $query_string, $query_args );
			if ( isset( $query_args[ $arg ] ) ) {
				$value = $query_args[ $arg ];
			}
		}
	} elseif ( isset( $_GET[ $arg ] ) ) {
		$value = $_GET[ $arg ];
	}

	return urldecode( $value );
}

/**
 * Gets the specified admin url.
 *
 * @since 1.0.0
 * @param string $section      Optional. The section name parameter.
 * @param array  $extra_params Optional. Additional parameters in pairs key => value.
 * @return string The admin page url.
 */
function wc_od_get_settings_url( $section = '', $extra_params = array() ) {
	$url = 'admin.php?page=' . urlencode( WC_OD_Utils::get_woocommerce_settings_page_slug() );
	$url .= '&amp;tab=shipping';

	if ( $section ) {
		$url .= '&amp;section=' . urlencode( $section );
	}

	if ( ! empty( $extra_params ) ) {
		foreach( $extra_params as $param => $value ) {
			$url .= '&amp;' . esc_attr( $param ) . '=' . urlencode( $value );
		}
	}

	return admin_url( $url );
}

/**
 * Gets the days by the specified property and value.
 *
 * @since 1.0.0
 * @param array $days      The days data.
 * @param string $property The day property to filter.
 * @param mixed $value     The property value to search.
 * @return array The filtered days.
 */
function wc_od_get_days_by( $days, $property, $value ) {
	$filtered_days = array();
	foreach ( $days as $index => $day ) {
		if ( isset( $day[ $property ] ) && $value === $day[ $property ] ) {
			$filtered_days[ $index ] = $day;
		}
	}

	return $filtered_days;
}

/**
 * Gets the events.
 *
 * @since 1.0.0
 * @param array $filters The filters for retrieve the events.
 * @return array The filtered events.
 */
function wc_od_get_events( $filters = array() ) {
	$event_type = ( isset( $filters['type'] ) ? $filters['type'] : 'event' ) ;
	$event_class = 'WC_OD_Event';
	if ( 'delivery' === $event_type ) {
		$event_class = 'WC_OD_Delivery_Event';
	}

	$event_filters = array_diff_key( $filters, array_flip( array( 'timezone', 'start', 'end', 'type' ) ) );
	$event_filters['range_start'] = wc_od_parse_datetime( $filters['start'] );
	$event_filters['range_end'] = wc_od_parse_datetime( $filters['end'] );

	// Parse the timezone parameter if it is present.
	$timezone = null;
	if ( isset( $filters['timezone'] ) && $filters['timezone'] ) {
		$timezone = new DateTimeZone( $filters['timezone'] );
	}

	$setting_name = $event_type . '_events';
	$events = WC_OD()->settings()->get_setting( $setting_name, array() );
	$filtered_events = array();
	foreach ( $events as $eventData ) {
		$event = new $event_class( $eventData, $timezone );
		if ( $event->is_valid( $event_filters ) ) {
			$filtered_events[] = $event->to_array();
		}
	}

	return $filtered_events;
}

/**
 * Removes the plugin prefix from the beginning of the string.
 *
 * @since 1.0.0
 * @param string $string The string to parse.
 * @return string The parsed string.
 */
function wc_od_no_prefix( $string ) {
	$prefix = WC_OD()->prefix;
	if ( $prefix === substr( $string, 0, strlen( $prefix ) ) ) {
		$string = substr( $string, strlen( $prefix ) );
	}

	return $string;
}

/**
 * Maybe adds the plugin prefix to the beginning of the string.
 *
 * @since 1.0.0
 * @param string $string The string to parse.
 * @return string The parsed string.
 */
function wc_od_maybe_prefix( $string ) {
	$string = wc_od_no_prefix( $string );

	return WC_OD()->prefix . $string;
}

/**
 * Gets templates passing attributes and including the file.
 *
 * @since 1.0.0
 * @global WooCommerce $woocommerce The WooCommerce instance.
 * @param string $template_name The template name.
 * @param array  $args          Optional. The template arguments.
 */
function wc_od_get_template( $template_name, $args = array() ) {
	wc_get_template( $template_name, $args, WC_TEMPLATE_PATH, WC_OD()->dir_path . 'templates/' );
}


/** Datetime functions ********************************************************/


/**
 * Parses a string into a DateTime object, optionally forced into the given timezone.
 *
 * @since 1.0.0
 * @param string       $string    A string representing a datetime
 * @param DateTimeZone $timezone  Optional. The timezone.
 * @return DateTime  The DataTime object.
 */
function wc_od_parse_datetime( $string, $timezone = null ) {
	if ( ! $timezone ) {
		$timezone = new DateTimeZone( 'UTC' );
	}

	$date = new DateTime( $string, $timezone );
	$date->setTimezone( $timezone );

	return $date;
}

/**
 * Takes the year-month-day values of the given DateTime and converts them to a new UTC DateTime.
 *
 * @since 1.0.0
 * @param DateTime $datetime The datetime.
 * @return DateTime The DataTime object.
 */
function wc_od_strip_time( $datetime ) {
	return new DateTime( $datetime->format( 'Y-m-d' ) );
}

/**
 * Parses a string into a DateTime object.
 *
 * @since 1.0.0
 * @param string $string      A string representing a time.
 * @param string $time_format The time format.
 * @return string The sanitized time.
 */
function wc_od_sanitize_time( $string, $time_format = 'H:i' ) {
	if ( ! $string ) {
		return '';
	}

	$timestamp = strtotime( $string );
	if ( false === $timestamp ) {
		return '';
	}

	return date( $time_format, $timestamp );
}

/**
 * Gets the localized date with the date format.
 *
 * @since 1.0.0
 * @param string|int $date   The date to localize.
 * @param string     $format Optional. The date format. If null use the general WordPress date format.
 * @return string|null The localized date string. Null if the date is not valid.
 */
function wc_od_localize_date( $date, $format = null ) {
	$timestamp = ( is_numeric( $date ) && (int) $date == $date ? (int) $date : strtotime( $date ) );
	$date_i18n = null;
	if ( false !== $timestamp ) {
		if ( ! $format ) {
			$format = get_option( 'date_format', 'F j, Y' );
		}

		$date_i18n = date_i18n( $format , $timestamp );
	}

	return $date_i18n;
}


/** Countries & states functions **********************************************/


/**
 * Gets the countries you ship to.
 *
 * @since 1.0.0
 * @global WooCommerce $woocommerce The WooCommerce instance.
 * @return array
 */
function wc_od_get_countries() {
	global $woocommerce;

	$countries = $woocommerce->countries->get_allowed_countries();
	// The get_shipping_countries() method was added on WooCommerce 2.1.
	if ( method_exists( $woocommerce->countries, 'get_shipping_countries' ) ) {
		$countries = array_merge( $countries, $woocommerce->countries->get_shipping_countries() );
	}

	return $countries;
}

/**
 * Gets the country states you ship to.
 *
 * @since 1.0.0
 * @global WooCommerce $woocommerce The WooCommerce instance.
 * @return array
 */
function wc_od_get_country_states() {
	global $woocommerce;

	$country_states = $woocommerce->countries->get_allowed_country_states();
	// The get_shipping_country_states() method was added on WooCommerce 2.1.
	if ( method_exists( $woocommerce->countries, 'get_shipping_country_states' ) ) {
		$country_states = array_merge( $country_states, $woocommerce->countries->get_shipping_country_states() );
	}

	return $country_states;
}

/**
 * Gets the country states you ship to.
 *
 * The state's information is formatted for the select2 library.
 *
 * @since 1.0.0
 * @return array
 */
function wc_od_get_country_states_for_select2() {
	$formatted_country_states = array();
	$country_states = wc_od_get_country_states();
	foreach ( $country_states as $country => $states ) {
		$formatted_country_states[ $country ] = array();
		foreach ( $states as $key => $state ) {
			$formatted_country_states[ $country ][] = array( 'id' => $key, 'text' => $state );
		}
	}

	return $formatted_country_states;
}
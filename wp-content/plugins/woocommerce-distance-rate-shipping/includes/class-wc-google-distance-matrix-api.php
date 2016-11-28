<?php
/**
 * Google Distance Matrix API class, handles all API calls to Google Distance Matrix API
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Google_Distance_Matrix_API' ) ) {

	class WC_Google_Distance_Matrix_API {

		/**
		 * API URL
		 */
		const API_URL = 'https://maps.googleapis.com/maps/api/distancematrix/json';

		/**
		 * API Key
		 * @var String
		 */
		public $api_key;

		/**
		 * Debug mode
		 * @var string
		 */
		public $debug;

		/**
		 * Constructor
		 * @return void
		 */
		public function __construct( $api_key, $debug ) {
			$this->api_key = $api_key;
			$this->debug = $debug;
		} // End __construct()

		/**
		 * Make a call to the Google Distance Matrix API
		 * @param  string $params
		 * @return json
		 */
		private function perform_request( $params ) {
			$args = array(
				'timeout'     => apply_filters( 'google_distance_matrix_api_timeout', 3 ), // default to 3 seconds
				'redirection' => 0,
				'httpversion' => '1.0',
				'sslverify'   => false,
				'blocking'    => true,
				'user-agent'  => "PHP " . PHP_VERSION . '/WooCommerce ' . get_option( 'woocommerce_db_version' )
			);

			$response = wp_remote_get( self::API_URL . '?' . ( ! empty( $this->api_key ) ? 'key=' . $this->api_key . '&' : '' ) . $params, $args );

			if ( 'yes' == $this->debug ) {
				wc_add_notice( 'Request: <br/>' . '<code>' . print_r( $params, true ) . '</code>' );
				wc_add_notice( 'Response: <br/>' . '<code>' . print_r( $response, true ) . '</code>' );
			}

			if ( is_wp_error( $response ) ) {
				throw new Exception( $response );
			}

			return $response;
		} // End perform_request()

		/**
		 * Get the distance based on origin and destination address
		 * @param  string $origin
		 * @param  string $destination
		 * @param  string $sensor
		 * @param  string $mode
		 * @param  string $avoid
		 * @param  string $units
		 * @return array
		 */
		public function get_distance( $origin, $destination, $sensor = 'false', $mode = 'driving', $avoid = '', $units = 'metric' ) {
			if ( false === ( $distance = get_transient( md5( $origin . '_' . $destination ) ) ) ) {
				$params = array();
				$params['origins'] = $origin;
				$params['destinations'] = $destination;
				$params['mode'] = $mode;
				if ( ! empty( $avoid ) ) {
					$params['avoid'] = $avoid;
				}
				$params['units'] = $units;
				$params['sensor'] = $sensor;

				$params = http_build_query( $params );
				$response = $this->perform_request( $params );

				$distance = json_decode( $response['body'] );

				// Only put valid results in transient
				if ( isset( $distance->rows[0]->elements[0]->status ) && ( 'OK' ==  $distance->rows[0]->elements[0]->status ) ) {
					set_transient( md5( $origin . '_' . $destination ), $distance, DAY_IN_SECONDS );
				}
			}
			return $distance;
		} // End get_distance()
	}
}
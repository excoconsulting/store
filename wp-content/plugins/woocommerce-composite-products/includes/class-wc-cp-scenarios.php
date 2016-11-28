<?php
/**
 * Globally accessible functions and filters associated with Composite products' Scenarios.
 *
 * @class   WC_CP_Scenarios
 * @version 3.5.0
 * @since   3.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_CP_Scenarios {

	/**
	 * Filter scenarios by action type.
	 *
	 * @param  array  $scenarios
	 * @param  string $type
	 * @param  array  $scenario_data
	 * @return array
	 */
	public static function filter_scenarios_by_type( $scenarios, $type, $scenario_data ) {

		$filtered = array();

		if ( ! empty( $scenarios ) ) {
			foreach ( $scenarios as $scenario_id ) {

				if ( ! empty( $scenario_data [ 'scenario_settings' ][ 'scenario_actions' ][ $scenario_id ] ) ) {
					$actions = $scenario_data [ 'scenario_settings' ][ 'scenario_actions' ][ $scenario_id ];

					if ( is_array( $actions ) && in_array( $type, $actions ) ) {
						$filtered[] = $scenario_id;
					}
				}
			}
		}

		return $filtered;
	}

	/**
	 * Returns the following arrays:
	 *
	 * 1. $scenarios             - contains all scenario ids.
	 * 2. $scenario_settings     - includes scenario actions and masked components in scenarios.
	 * 3. $scenario_data         - maps every product/variation in a group to the scenarios where it is active.
	 * 4. $defaults_in_scenarios - the scenarios where all default component selections coexist.
	 *
	 * @param  array $bto_scenario_meta     scenarios meta
	 * @param  array $bto_data              component data - values may contain a 'current_component_options' key to generate scenarios for a subset of all component options
	 * @return array
	 */
	public static function build_scenarios( $bto_scenario_meta, $bto_data ) {

		$input = array(
			'scenario_meta'  => $bto_scenario_meta,
			'component_data' => $bto_data
		);

		$request_id = md5( json_encode( $input ) );

		$result = WC_CP()->api->cache_get( 'build_scenarios_' . $request_id );

		if ( null !== $result ) {
			return $result;
		}

		$scenarios          = empty( $bto_scenario_meta ) ? array() : array_map( 'strval', array_keys( $bto_scenario_meta ) );
		$common_scenarios   = $scenarios;
		$scenario_data      = array();
		$scenario_settings  = array();

		$compat_group_count = 0;

		// Store the 'actions' associated with every scenario.
		foreach ( $scenarios as $scenario_id ) {

			$scenario_settings[ 'scenario_actions' ][ $scenario_id ] = array();

			if ( isset( $bto_scenario_meta[ $scenario_id ][ 'scenario_actions' ] ) ) {

				$actions = array();

				foreach ( $bto_scenario_meta[ $scenario_id ][ 'scenario_actions' ] as $action_name => $action_data ) {
					if ( isset( $action_data[ 'is_active' ] ) && $action_data[ 'is_active' ] === 'yes' ) {
						$actions[] = $action_name;

						if ( $action_name === 'compat_group' ) {
							$compat_group_count++;
						}
					}
				}

				$scenario_settings[ 'scenario_actions' ][ $scenario_id ] = $actions;

			} else {
				$scenario_settings[ 'scenario_actions' ][ $scenario_id ] = array( 'compat_group' );
				$compat_group_count++;
			}
		}

		$scenario_settings[ 'scenario_actions' ][ '0' ] = array( 'compat_group' );

		// Find which components in every scenario are 'non shaping components' (marked as unrelated).
		if ( ! empty( $bto_scenario_meta ) ) {
			foreach ( $bto_scenario_meta as $scenario_id => $scenario_single_meta ) {

				$scenario_settings[ 'masked_components' ][ $scenario_id ] = array();

				foreach ( $bto_data as $group_id => $group_data ) {

					if ( isset( $scenario_single_meta[ 'modifier' ][ $group_id ] ) && $scenario_single_meta[ 'modifier' ][ $group_id ] === 'masked' ) {
						$scenario_settings[ 'masked_components' ][ $scenario_id ][] = ( string ) $group_id;
					}
				}
			}
		}

		$scenario_settings[ 'masked_components' ][ '0' ] = array();

		// Include the '0' scenario for use when no 'compat_group' scenarios exist.
		if ( $compat_group_count === 0 ) {
			$scenarios[] = '0';
		}

		// Map each product and variation to the scenarios that contain it.
		foreach ( $bto_data as $group_id => $group_data ) {

			$scenario_data[ $group_id ] = array();

			// 'None' option.
			if ( $group_data[ 'optional' ] === 'yes' ) {

				$scenarios_for_product = self::get_scenarios_for_product( $bto_scenario_meta, $group_id, -1, '', 'none' );

				$scenario_data[ $group_id ][ 0 ] = $scenarios_for_product;
			}

			// Component options.

			// When indicated, build scenarios only based on a limited set of component options.
			if ( isset( $bto_data[ $group_id ][ 'current_component_options' ] ) ) {

				$component_options = $bto_data[ $group_id ][ 'current_component_options' ];

			// Otherwise run a query to get all component options.
			} else {

				$component_options = WC_CP()->api->get_component_options( $group_data );
			}

			foreach ( $component_options as $product_id ) {

				if ( ! is_numeric( $product_id ) ) {
					continue;
				}

				// Get product type.
				$terms        = get_the_terms( $product_id, 'product_type' );
				$product_type = ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';

				if ( $product_type === 'variable' ) {

					$variations = WC_CP_Helpers::get_product_variations( $product_id );

					if ( ! empty( $variations ) ) {

						$scenarios_for_product = array();

						foreach ( $variations as $variation_id ) {

							$scenarios_for_variation = self::get_scenarios_for_product( $bto_scenario_meta, $group_id, $product_id, $variation_id, 'variation' );

							$scenarios_for_product   = array_merge( $scenarios_for_product, $scenarios_for_variation );

							$scenario_data[ $group_id ][ $variation_id ] = $scenarios_for_variation;
						}

						$scenario_data[ $group_id ][ $product_id ] = array_values( array_unique( $scenarios_for_product ) );
					}

				} else {

					$scenarios_for_product = self::get_scenarios_for_product( $bto_scenario_meta, $group_id, $product_id, '', $product_type );

					$scenario_data[ $group_id ][ $product_id ] = $scenarios_for_product;
				}
			}

			if ( isset( $group_data[ 'default_id' ] ) && $group_data[ 'default_id' ] !== '' ) {

				if ( ! empty ( $scenario_data[ $group_id ][ $group_data[ 'default_id' ] ] ) ) {
					$common_scenarios = array_intersect( $common_scenarios, $scenario_data[ $group_id ][ $group_data[ 'default_id' ] ] );
				} else {
					$common_scenarios = array();
				}
			}
		}

		$result = array(
			'scenarios'             => $scenarios,
			'scenario_settings'     => $scenario_settings,
			'scenario_data'         => $scenario_data,
			'defaults_in_scenarios' => $common_scenarios
		);

		WC_CP()->api->cache_set( 'build_scenarios_' . $request_id, $result );

		return $result;
	}

	/**
	 * Returns an array of all scenarios where a particular component option (product/variation) is active.
	 *
	 * @param  array   $scenario_meta
	 * @param  string  $group_id
	 * @param  int     $product_id
	 * @param  int     $variation_id
	 * @param  string  $product_type
	 * @return array
	 */
	public static function get_scenarios_for_product( $scenario_meta, $group_id, $product_id, $variation_id, $product_type ) {

		if ( empty( $scenario_meta ) ) {
			return array( '0' );
		}

		$scenarios = array();

		foreach ( $scenario_meta as $scenario_id => $scenario_data ) {

			if ( self::product_active_in_scenario( $scenario_data, $group_id, $product_id, $variation_id, $product_type ) ) {
				$scenarios[] = ( string ) $scenario_id;
			}
		}

		// All products belong in the '0' scenario.
		$scenarios[] = '0';

		return $scenarios;
	}

	/**
	 * Returns true if a product/variation id of a particular component is present in the scenario meta array. Also @see product_active_in_scenario function.
	 *
	 * @param  array   $scenario_data
	 * @param  string  $group_id
	 * @param  int     $product_id
	 * @return boolean
	 */
	public static function scenario_contains_product( $scenario_data, $group_id, $product_id ) {

		if ( isset( $scenario_data[ 'component_data' ] ) && ! empty( $scenario_data[ 'component_data' ][ $group_id ] ) && is_array( $scenario_data[ 'component_data' ][ $group_id ] ) && in_array( $product_id, $scenario_data[ 'component_data' ][ $group_id ] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns true if a product/variation id of a particular component is present in the scenario meta array. Uses 'scenario_contains_product' but also takes exclusion rules into account.
	 * When checking a variation, also makes sure that the parent product is also tested against the scenario meta array.
	 *
	 * @param  array   $scenario_data
	 * @param  string  $group_id
	 * @param  int     $product_id
	 * @param  int     $variation_id
	 * @param  string  $product_type
	 * @return boolean
	 */
	public static function product_active_in_scenario( $scenario_data, $group_id, $product_id, $variation_id, $product_type ) {

		if ( empty( $scenario_data[ 'component_data' ] ) || empty( $scenario_data[ 'component_data' ][ $group_id ] ) ) {
			return true;
		}

		$id = ( $product_type === 'variation' ) ? $variation_id : $product_id;

		if ( self::scenario_contains_product( $scenario_data, $group_id, 0 ) ) {
			return true;
		}

		$exclude = false;

		if ( isset( $scenario_data[ 'modifier' ][ $group_id ] ) && $scenario_data[ 'modifier' ][ $group_id ] === 'not-in' ) {
			$exclude = true;
		} elseif ( isset( $scenario_data[ 'exclude' ][ $group_id ] ) && $scenario_data[ 'exclude' ][ $group_id ] === 'yes' ) {
			$exclude = true;
		}

		$product_active_in_scenario = false;

		if ( self::scenario_contains_product( $scenario_data, $group_id, $id ) ) {
			if ( ! $exclude ) {
				$product_active_in_scenario = true;
			} else {
				$product_active_in_scenario = false;
			}
		} else {
			if ( ! $exclude ) {

				if ( $product_type === 'variation' ) {

					if ( self::scenario_contains_product( $scenario_data, $group_id, $product_id ) ) {
						$product_active_in_scenario = true;
					} else {
						$product_active_in_scenario = false;
					}

				} else {
					$product_active_in_scenario = false;
				}

			} else {

				if ( $product_type === 'variation' ) {

					if ( self::scenario_contains_product( $scenario_data, $group_id, $product_id ) ) {
						$product_active_in_scenario = false;
					} else {
						$product_active_in_scenario = true;
					}

				} else {
					$product_active_in_scenario = true;
				}
			}
		}

		return $product_active_in_scenario;
	}
}

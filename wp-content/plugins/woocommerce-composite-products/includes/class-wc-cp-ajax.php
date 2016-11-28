<?php
/**
 * Composited Products AJAX Handlers.
 *
 * @class 	WC_CP_AJAX
 * @version 3.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_CP_AJAX {

	public static function init() {

		// Use WC ajax if available, otherwise fall back to WP ajax.
		if ( WC_CP_Core_Compatibility::use_wc_ajax() ) {

			add_action( 'wc_ajax_woocommerce_show_composited_product', __CLASS__ . '::show_composited_product_ajax' );
			add_action( 'wc_ajax_woocommerce_show_component_options', __CLASS__ . '::show_component_options_ajax' );

		} else {

			add_action( 'wp_ajax_woocommerce_show_composited_product', __CLASS__ . '::show_composited_product_ajax' );
			add_action( 'wp_ajax_woocommerce_show_component_options', __CLASS__ . '::show_component_options_ajax' );

			add_action( 'wp_ajax_nopriv_woocommerce_show_composited_product', __CLASS__ . '::show_composited_product_ajax' );
			add_action( 'wp_ajax_nopriv_woocommerce_show_component_options', __CLASS__ . '::show_component_options_ajax' );
		}
	}

	/**
	 * Display paged component options via ajax. Effective in 'thumbnails' mode only.
	 *
	 * @return void
	 */
	public static function show_component_options_ajax() {

		$data = array();

		if ( isset( $_POST[ 'load_page' ] ) && intval( $_POST[ 'load_page' ] ) > 0 && isset( $_POST[ 'composite_id' ] ) && intval( $_POST[ 'composite_id' ] ) > 0 && ! empty( $_POST[ 'component_id' ] ) ) {

			$component_id    = intval( $_POST[ 'component_id' ] );
			$composite_id    = intval( $_POST[ 'composite_id' ] );
			$selected_option = ! empty( $_POST[ 'selected_option' ] ) ? intval( $_POST[ 'selected_option' ] ) : '';
			$load_page       = intval( $_POST[ 'load_page' ] );

		} else {

			wp_send_json( array(
				'result'                  => 'failure',
				'component_scenario_data' => array(),
				'options_markup'          => sprintf( '<div class="woocommerce-error">%s</div>', __( 'Looks like something went wrong. Please refresh the page and try again.', 'woocommerce-composite-products' ) )
			) );
		}

		$product = wc_get_product( $composite_id );

		$query_args = array(
			'selected_option' => $selected_option,
			'load_page'       => $load_page,
		);

		// Include orderby argument if posted -- if not, the default ordering method will be used.
		if ( ! empty( $_POST[ 'orderby' ] ) ) {
			$query_args[ 'orderby' ] = $_POST[ 'orderby' ];
		}

		// Include filters argument if posted -- if not, no filters will be applied to the query.
		if ( ! empty( $_POST[ 'filters' ] ) ) {
			$query_args[ 'filters' ] = $_POST[ 'filters' ];
		}

		// Load Component Options.
		$current_options = $product->get_current_component_options( $component_id, $query_args );

		ob_start();

		wc_get_template( 'single-product/component-options.php', array(
			'product'           => $product,
			'component_id'      => $component_id,
			'component_options' => $current_options,
			'component_data'    => $product->get_component_data( $component_id ),
			'selected_option'   => $selected_option,
		), '', WC_CP()->plugin_path() . '/templates/' );

		$component_options_markup = ob_get_clean();

		ob_start();

		wc_get_template( 'single-product/component-options-pagination.php', array(
			'product'             => $product,
			'component_id'        => $component_id,
		), '', WC_CP()->plugin_path() . '/templates/' );

		$component_pagination_markup = ob_get_clean();

		// Load Scenario data for the current Component and current Component Options.
		$scenario_data = $product->get_current_scenario_data( array( $component_id ) );

		wp_send_json( array(
			'result'                  => 'success',
			'component_scenario_data' => $scenario_data[ 'scenario_data' ][ $component_id ],
			'options_markup'          => $component_options_markup,
			'pagination_markup'       => $component_pagination_markup,
		) );
	}

	/**
	 * Ajax listener that fetches product markup when a new selection is made.
	 *
	 * @param  mixed    $product_id
	 * @param  mixed    $item_id
	 * @param  mixed    $container_id
	 * @return string
	 */
	public static function show_composited_product_ajax( $product_id = '', $component_id = '', $composite_id = '' ) {

		global $product;

		if ( isset( $_POST[ 'product_id' ] ) && intval( $_POST[ 'product_id' ] ) > 0 && isset( $_POST[ 'component_id' ] ) && ! empty( $_POST[ 'component_id' ] ) && isset( $_POST[ 'composite_id' ] ) && ! empty( $_POST[ 'composite_id' ] ) ) {

			$product_id   = intval( $_POST[ 'product_id' ] );
			$component_id = intval( $_POST[ 'component_id' ] );
			$composite_id = intval( $_POST[ 'composite_id' ] );

		} else {

			wp_send_json( array(
				'result' => 'failure',
				'reason' => 'required params missing',
				'markup' => sprintf( '<div class="component_data woocommerce-error" data-price="0" data-regular_price="0" data-product_type="invalid-data">%s</div>', __( 'There was an error while updating your selection. Please refresh the page and try again.', 'woocommerce-composite-products' ) )
			) );
		}

		$composite          = wc_get_product( $composite_id );
		$composited_product = $composite->get_composited_product( $component_id, $product_id );

		if ( ! $composited_product || ! $composited_product->is_purchasable() ) {

			$link  = '<a class="clear_component_options" href="#clear_component">' . __( 'Clear selection?', 'woocommerce-composite-products' ) . '</a>';
			$error = sprintf( __( 'The selected item cannot be purchased at the moment. %s', 'woocommerce-composite-products' ), $link );

			wp_send_json( array(
				'result' => 'failure',
				'reason' => 'product does not exist or is not purchasable',
				'markup' => sprintf( '<div class="component_data woocommerce-error" data-price="0" data-regular_price="0" data-product_type="invalid-product">%s</div>', $error )
			) );
		}

		$product = $composited_product->get_product();

		$composite->sync_composite();

		ob_start();

 		WC_CP()->api->apply_composited_product_filters( $product, $component_id, $composite );

 		/**
 		 * Action 'woocommerce_composite_show_composited_product'.
 		 *
 		 * @param  WC_Product            $product
 		 * @param  string                $component_id
 		 * @param  WC_Product_Composite  $composite
 		 */
		do_action( 'woocommerce_composite_show_composited_product', $product, $component_id, $composite );

		WC_CP()->api->remove_composited_product_filters();

		$output = ob_get_clean();

		wp_send_json( array(
			'result' => 'success',
			'markup' => $output,
		) );
	}

}

WC_CP_AJAX::init();

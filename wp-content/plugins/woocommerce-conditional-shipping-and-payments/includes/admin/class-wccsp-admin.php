<?php
/**
 * WC_CSP_Admin class
 *
 * @author   SomewhereWarm <sw@somewherewarm.net>
 * @package  WooCommerce Conditional Shipping and Payments
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product CSP Restrictions Admin Class.
 *
 * Loads admin tabs and adds related hooks / filters.
 *
 * @version 1.2.0
 * @since   1.0.0
 */
class WC_CSP_Admin {

	private $save_errors = array();

	/*
	 * Setup admin class.
	 */
	public function __construct() {

		// Ajax save config.
		add_action( 'wp_ajax_woocommerce_add_checkout_restriction', array( $this, 'ajax_add_checkout_restriction' ) );

		// Admin jquery.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 11 );

		/*
		 * Product Settings.
		 */

		// Creates the admin panel tab.
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'product_write_panel_tab' ) );

		// Creates the panel for configuring product options.
		add_action( 'woocommerce_product_write_panels', array( $this, 'product_write_panel' ) );

		// Processes and saves the necessary post metas from the selections made above.
		add_action( 'woocommerce_process_product_meta', array( $this, 'process_product_meta' ) );

		/*
		 * Global Settings.
		 */

		// Add global 'Restrictions' tab to WooCommerce settings.
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_restrictions_settings_page' ) );
	}

	/**
	 * Add 'Restrictions' tab to WooCommerce Settings tabs.
	 *
	 * @since  1.0
	 * @param  array $settings
	 * @return array $settings
	 */
	public function add_restrictions_settings_page( $settings ) {

		$settings[] = include( 'settings/class-wccsp-settings-restrictions.php' );

		return $settings;
	}

	/**
	 * Admin product writepanel scripts.
	 *
	 * @return void
	 */
	function admin_scripts() {

		global $post;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$wc_screen_id = sanitize_title( __( 'WooCommerce', 'woocommerce' ) );

		// Get admin screen id.
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		// Product metaboxes.

		if ( WC_CSP_Core_Compatibility::is_wc_version_gte_2_2() ) {
			$writepanel_dependency = 'wc-admin-meta-boxes';
		} else {
			$writepanel_dependency = 'woocommerce_admin_meta_boxes';
		}

		if ( in_array( $screen_id, array( 'edit-product', 'product' ) ) ) {

			wp_register_script( 'wc-restrictions-writepanel', WC_CSP()->plugin_url() . '/assets/js/wc-restrictions-write-panels' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'wp-util', $writepanel_dependency ), WC_Conditional_Shipping_Payments::VERSION );
			wp_register_style( 'wc-restrictions-css', WC_CSP()->plugin_url() . '/assets/css/wc-restrictions-write-panels.css', array( 'woocommerce_admin_styles' ), WC_Conditional_Shipping_Payments::VERSION );

		} elseif ( $screen_id === $wc_screen_id . '_page_wc-settings' && isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] === 'restrictions' ) {

			wp_register_script( 'wc-global-restrictions-writepanel', WC_CSP()->plugin_url() . '/assets/js/wc-restrictions-write-panels' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'wp-util' ), WC_Conditional_Shipping_Payments::VERSION );
			wp_register_style( 'wc-restrictions-css', WC_CSP()->plugin_url() . '/assets/css/wc-restrictions-write-panels.css', array( 'woocommerce_admin_styles' ), WC_Conditional_Shipping_Payments::VERSION );

		}

		// WooCommerce admin pages.
		if ( in_array( $screen_id, array( 'product' ) ) ) {

			wp_enqueue_script( 'wc-restrictions-writepanel' );

			$params = array(
				'add_restriction_nonce'     => wp_create_nonce( 'wc_restrictions_add_restriction' ),
				'wc_ajax_url'               => admin_url( 'admin-ajax.php' ),
				'post_id'                   => isset( $post->ID ) ? $post->ID : '',
				'wc_plugin_url'             => WC()->plugin_url(),
				'is_wc_version_gte_2_3'     => WC_CSP_Core_Compatibility::is_wc_version_gte_2_3() ? 'yes' : 'no',
				'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'woocommerce' ),
				'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'woocommerce' ),
				'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
				'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
				'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
				'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
				'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
				'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
			);

			wp_localize_script( 'wc-restrictions-writepanel', 'wc_restrictions_admin_params', $params );
		}

		if ( in_array( $screen_id, array( 'edit-product', 'product' ) ) ) {
			wp_enqueue_style( 'wc-restrictions-css' );
		}

		if ( $screen_id === $wc_screen_id . '_page_wc-settings' && isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] === 'restrictions' ) {

			wp_enqueue_script( 'wc-global-restrictions-writepanel' );

			$params = array(
				'add_restriction_nonce'     => wp_create_nonce( 'wc_restrictions_add_restriction' ),
				'wc_ajax_url'               => admin_url( 'admin-ajax.php' ),
				'post_id'                   => '',
				'wc_plugin_url'             => WC()->plugin_url(),
				'is_wc_version_gte_2_3'     => WC_CSP_Core_Compatibility::is_wc_version_gte_2_3() ? 'yes' : 'no',
				'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'woocommerce' ),
				'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'woocommerce' ),
				'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
				'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
				'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
				'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
				'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
				'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
			);

			wp_localize_script( 'wc-global-restrictions-writepanel', 'wc_restrictions_admin_params', $params );
			wp_enqueue_style( 'wc-restrictions-css' );
		}
	}

	/**
	 * Restrictions writepanel tab.
	 *
	 * @return void
	 */
	function product_write_panel_tab() {

		echo '<li class="restrictions_options restrictions_tab"><a href="#restrictions_data">' . __( 'Restrictions', 'woocommerce-conditional-shipping-and-payments' ) . '</a></li>';
	}

	/**
	 * Product writepanel for Restrictions.
	 *
	 * @return void
	 */
	function product_write_panel() {

		global $woocommerce, $post;

		$restrictions = WC_CSP()->restrictions->get_admin_product_field_restrictions();

		?>
		<div id="restrictions_data" class="panel woocommerce_options_panel wc-metaboxes-wrapper">

			<div class="options_group">

				<p class="toolbar">

					<select name="_restriction_type" class="restriction_type">
						<option value=""><?php _e( 'Restriction type&hellip;', 'woocommerce-conditional-shipping-and-payments' ); ?></option>
						<?php

						foreach ( $restrictions as $restriction_id => $restriction )
							echo '<option value="' . $restriction_id . '">' . $restriction->get_title() . '</option>';

						?>
					</select>
					<button type="button" class="button button-primary add_restriction"><?php _e( 'Add Restriction', 'woocommerce-conditional-shipping-and-payments' ); ?></button>

					<a href="#" class="close_all"><?php _e( 'Close all', 'woocommerce' ); ?></a>
					<a href="#" class="expand_all"><?php _e( 'Expand all', 'woocommerce' ); ?></a>
				</p>

				<div class="woocommerce_restrictions wc-metaboxes ui-sortable">

					<?php

					$applied_restrictions = WC_CSP()->restrictions->maybe_update_restriction_data( get_post_meta( $post->ID, '_wccsp_restrictions', true ), 'product' );

					if ( $applied_restrictions ) {
						foreach ( $applied_restrictions as $index => $restriction_data ) {

							$restriction_id = $restriction_data[ 'restriction_id' ];
							$restriction    = WC_CSP()->restrictions->get_restriction( $restriction_id );

							if ( $restriction ) {
								$restriction->get_admin_product_metaboxes_content( $index, $restriction_data );
							}
						}
					}
					?>
				</div>

			</div>

		</div>
		<?php
	}

	/**
	 * Process, verify and save restriction product data.
	 *
	 * @param  int    $post_id
	 * @return void
	 */
	function process_product_meta( $post_id ) {

		if ( isset( $_POST[ 'restriction' ] ) ) {
			$posted_restrictions_data = $_POST[ 'restriction' ];
		}

		$restriction_data = array();
		$count            = 0;
		$loop             = 0;

		if ( isset( $posted_restrictions_data ) ) {

			uasort( $posted_restrictions_data, array( $this, 'cmp' ) );

			foreach ( $posted_restrictions_data as &$posted_restriction_data ) {

				$posted_restriction_data[ 'index' ] = $loop + 1;

				if ( isset( $posted_restriction_data[ 'restriction_id' ] ) ) {

					$restriction_id = stripslashes( $posted_restriction_data[ 'restriction_id' ] );
					$restriction    = WC_CSP()->restrictions->get_restriction( $restriction_id );

					if ( $restriction && $restriction->has_admin_product_fields() ) {
						$processed_data = $restriction->process_admin_product_fields( $posted_restriction_data );
					}

					if ( $processed_data ) {

						$processed_data                     = apply_filters( 'woocommerce_csp_process_admin_product_fields', $processed_data, $posted_restriction_data, $restriction_id );

						$processed_data[ 'restriction_id' ] = $restriction_id;
						$processed_data[ 'index' ]          = $count;

						if ( WC_CSP_Core_Compatibility::is_wc_version_gte_2_6() ) {
							$processed_data[ 'wc_26_shipping' ] = 'yes';
						}

						$restriction_data[ $count ]         = $processed_data;
						$count++;
					}

					$loop++;
				}
			}

			update_post_meta( $post_id, '_wccsp_restrictions', $restriction_data );

		} else {

			delete_post_meta( $post_id, '_wccsp_restrictions' );
		}

		// Clear cached shipping rates.
		WC_CSP_Core_Compatibility::clear_cached_shipping_rates();
	}

	/**
	 * Sort posted restriction data.
	 */
    private function cmp( $a, $b ) {

	    if ( $a[ 'position' ] == $b[ 'position' ] ) {
	        return 0;
	    }

	    return ( $a[ 'position' ] < $b[ 'position' ] ) ? -1 : 1;
	}

	/**
	 * Handles adding restrictions via Ajax.
	 *
	 * @return void
	 */
	function ajax_add_checkout_restriction() {

		check_ajax_referer( 'wc_restrictions_add_restriction', 'security' );

		$restriction_id = stripslashes( $_POST[ 'restriction_id' ] );
		$applied_count  = intval( $_POST[ 'applied_count' ] );
		$count          = intval( $_POST[ 'count' ] );
		$index          = intval( $_POST[ 'index' ] );
		$post_id        = intval( $_POST[ 'post_id' ] );

		$errors = array();

		ob_start();

		// Add if no rules exist, or if the restriction supports multiple definitions.
		if ( $applied_count === 0 || ( $applied_count > 0 && WC_CSP()->restrictions->get_restriction( $restriction_id )->supports_multiple() ) ) {

			if ( empty( $post_id ) ) {
				WC_CSP()->restrictions->get_restriction( $restriction_id )->get_admin_global_metaboxes_content( $index, array( 'index' => $count ), true );
			} else {
				WC_CSP()->restrictions->get_restriction( $restriction_id )->get_admin_product_metaboxes_content( $index, array( 'index' => $count ), true );
			}

		} else {
			$errors[] = __( 'This restriction is already defined and cannot be added again. Only restrictions that support multiple rule definitions can be added more than once.', 'woocommerce-conditional-shipping-and-payments' );
		}

		$output = ob_get_clean();

		header( 'Content-Type: application/json; charset=utf-8' );

		echo json_encode( array(
			'markup' => $output,
			'errors' => $errors
		) );

		die();
	}

}

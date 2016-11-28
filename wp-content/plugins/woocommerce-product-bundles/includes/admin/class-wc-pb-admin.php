<?php
/**
 * Product Bundles Admin Class.
 *
 * Loads admin tabs and adds related hooks/filters.
 *
 * @class   WC_PB_Admin
 * @version 4.14.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_PB_Admin {

	/**
	 * Setup admin class.
	 */
	public function __construct() {

		// Admin jquery.
		add_action( 'admin_enqueue_scripts', array( $this, 'woo_bundles_admin_scripts' ), 11 );

		// Creates the admin panel tab 'Bundled Products'.
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'woo_bundles_product_write_panel_tab' ) );

		// Adds the base price options.
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'base_price_options' ) );

		// Creates the panel for selecting bundled product options.
		add_action( 'woocommerce_product_write_panels', array( $this, 'woo_bundles_product_write_panel' ) );
		add_action( 'woocommerce_product_options_stock', array( $this, 'woo_bundles_stock_group' ) );

		// Adds the shipping/pricing bundle type options.
		add_filter( 'product_type_options', array( $this, 'woo_bundles_type_options' ) );

		// Processes and saves the necessary post metas from the selections made above.
		add_action( 'woocommerce_process_product_meta_bundle', array( $this, 'woo_bundles_process_bundle_meta' ) );

		// Allows the selection of the 'bundled product' type.
		add_filter( 'product_type_selector', array( $this, 'woo_bundles_product_selector_filter' ) );

		// Template override scan path.
		add_filter( 'woocommerce_template_overrides_scan_paths', array( $this, 'woo_bundles_template_scan_path' ) );

		// Ajax add bundled product.
		add_action( 'wp_ajax_woocommerce_add_bundled_product', array( $this, 'ajax_add_bundled_product' ) );

		// Ajax search bundled item variations.
		add_action( 'wp_ajax_woocommerce_search_bundled_variations', array( $this, 'ajax_search_bundled_variations' ) );

		// Basic bundled product admin config options.
		add_action( 'woocommerce_bundled_product_admin_config_html', array( $this, 'bundled_product_admin_config_html' ), 10, 4 );

		// Advanced bundled product admin config options.
		add_action( 'woocommerce_bundled_product_admin_advanced_html', array( $this, 'bundled_product_admin_advanced_html' ), 10, 4 );

		// Bundle tab settings.
		add_action( 'woocommerce_bundled_products_admin_config', array( $this, 'bundled_products_admin_config' ) );

		// Scheduled base sale price.
		add_action( 'woocommerce_scheduled_sales', array( $this, 'scheduled_sales' ) );
	}

	/**
	 * Adds the base and sale price option writepanel options.
	 *
	 * @return void
	 */
	public function base_price_options() {

		global $thepostid;

		echo '<div class="options_group bundle_base_pricing show_if_bundle">';

		// Base Prices.
		$base_regular_price = get_post_meta( $thepostid, '_base_regular_price', true );
		$base_sale_price    = get_post_meta( $thepostid, '_base_sale_price', true );

		woocommerce_wp_text_input( array( 'id' => '_wc_pb_base_regular_price', 'value' => $base_regular_price, 'class' => 'short', 'label' => __( 'Base Regular Price', 'woocommerce-product-bundles' ) . ' (' . get_woocommerce_currency_symbol() . ')', 'data_type' => 'price' ) );
		woocommerce_wp_text_input( array( 'id' => '_wc_pb_base_sale_price', 'value' => $base_sale_price, 'class' => 'short', 'label' => __( 'Base Sale Price', 'woocommerce-product-bundles' ) . ' (' . get_woocommerce_currency_symbol() . ')', 'data_type' => 'price', 'description' => '<a href="#" class="sale_schedule">' . __( 'Schedule', 'woocommerce' ) . '</a>' ) );

		// Special Price date range.
		$sale_price_dates_from = ( $date = get_post_meta( $thepostid, '_base_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
		$sale_price_dates_to   = ( $date = get_post_meta( $thepostid, '_base_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';

		echo '<p class="form-field sale_price_dates_fields wc_pb_base_sale_price_dates_fields">
			<label for="_wc_pb_base_sale_price_dates_from">' . __( 'Base Sale Price Dates', 'woocommerce-product-bundles' ) . '</label>
			<input type="text" class="short sale_price_dates_from" name="_wc_pb_base_sale_price_dates_from" id="_wc_pb_base_sale_price_dates_from" value="' . esc_attr( $sale_price_dates_from ) . '" placeholder="' . _x( 'From&hellip;', 'placeholder', 'woocommerce' ) . ' YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
			<input type="text" class="short sale_price_dates_to" name="_wc_pb_base_sale_price_dates_to" id="_wc_pb_base_sale_price_dates_to" value="' . esc_attr( $sale_price_dates_to ) . '" placeholder="' . _x( 'To&hellip;', 'placeholder', 'woocommerce' ) . '  YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
			<a href="#" class="cancel_sale_schedule" style="display: inline-block; margin: 21px 0 0 10px;">' . __( 'Cancel', 'woocommerce' ) . '</a>' . WC_PB_Core_Compatibility::wc_help_tip( __( 'The sale will end at the beginning of the set date.', 'woocommerce' ) ) . '
		</p>';

		echo '</div>';
	}

	/**
	 * Function which handles the start and end of scheduled base price sales via cron.
	 *
	 * @access public
	 * @return void
	 */
	public function scheduled_sales() {
		global $wpdb;

		if ( function_exists( 'WC_CP' ) ) {
			return;
		}

		// Sales which are due to start
		$product_ids = $wpdb->get_col( $wpdb->prepare( "
			SELECT postmeta.post_id FROM {$wpdb->postmeta} as postmeta
			LEFT JOIN {$wpdb->postmeta} as postmeta_2 ON postmeta.post_id = postmeta_2.post_id
			LEFT JOIN {$wpdb->postmeta} as postmeta_3 ON postmeta.post_id = postmeta_3.post_id
			WHERE postmeta.meta_key = '_base_sale_price_dates_from'
			AND postmeta_2.meta_key = '_base_price'
			AND postmeta_3.meta_key = '_base_sale_price'
			AND postmeta.meta_value > 0
			AND postmeta.meta_value < %s
			AND postmeta_2.meta_value != postmeta_3.meta_value
		", current_time( 'timestamp' ) ) );

		if ( $product_ids ) {
			foreach ( $product_ids as $product_id ) {
				$sale_price = get_post_meta( $product_id, '_base_sale_price', true );

				if ( $sale_price ) {
					update_post_meta( $product_id, '_base_price', $sale_price );
				} else {
					// No sale price!
					update_post_meta( $product_id, '_base_sale_price_dates_from', '' );
					update_post_meta( $product_id, '_base_sale_price_dates_to', '' );
				}
			}

			delete_transient( 'wc_products_onsale' );
		}

		// Sales which are due to end.
		$product_ids = $wpdb->get_col( $wpdb->prepare( "
			SELECT postmeta.post_id FROM {$wpdb->postmeta} as postmeta
			LEFT JOIN {$wpdb->postmeta} as postmeta_2 ON postmeta.post_id = postmeta_2.post_id
			LEFT JOIN {$wpdb->postmeta} as postmeta_3 ON postmeta.post_id = postmeta_3.post_id
			WHERE postmeta.meta_key = '_base_sale_price_dates_to'
			AND postmeta_2.meta_key = '_base_price'
			AND postmeta_3.meta_key = '_base_regular_price'
			AND postmeta.meta_value > 0
			AND postmeta.meta_value < %s
			AND postmeta_2.meta_value != postmeta_3.meta_value
		", current_time( 'timestamp' ) ) );

		if ( $product_ids ) {
			foreach ( $product_ids as $product_id ) {
				$regular_price = get_post_meta( $product_id, '_base_regular_price', true );

				update_post_meta( $product_id, '_base_price', $regular_price );
				update_post_meta( $product_id, '_base_sale_price', '' );
				update_post_meta( $product_id, '_base_sale_price_dates_from', '' );
				update_post_meta( $product_id, '_base_sale_price_dates_to', '' );
			}

			delete_transient( 'wc_products_onsale' );
		}
	}

	/**
	 * Render main settings in 'woocommerce_bundled_products_admin_config' action.
	 *
	 * @return void
	 */
	public function bundled_products_admin_config() {

		global $post;

		$bundle_data          = maybe_unserialize( get_post_meta( $post->ID, '_bundle_data', true ) );
		$bundled_variable_num = 0;
		$post_id              = $post->ID;
		$toggle               = 'closed';
		$tabs                 = $this->get_bundled_product_tabs();

		?><div class="options_group wc-metaboxes-wrapper wc-bundle-metaboxes-wrapper">

			<div id="wc-bundle-metaboxes-wrapper-inner">

				<p class="toolbar">
					<a href="#" class="close_all"><?php _e('Close all', 'woocommerce'); ?></a>
					<a href="#" class="expand_all"><?php _e('Expand all', 'woocommerce'); ?></a>
				</p>

				<div class="wc-bundled-items wc-metaboxes"><?php

					if ( ! empty( $bundle_data ) ) {

						$loop = 0;

						foreach ( $bundle_data as $item_id => $item_data ) {

							$sep        = explode( '_', $item_id );
							$product_id = $item_data[ 'product_id' ];

							$suffix = (string) $product_id != (string) $item_id ? '#' . $sep[1] : '';
							$title  = WC_PB()->helpers->get_product_title( $product_id, $suffix );

							if ( ! $title ) {
								continue;
							}

							include( 'html-bundled-product-admin.php' );

							$loop++;
						}
					}
				?></div>
			</div>

		</div>

		<p class="bundled_products_toolbar toolbar">
			<span class="bundled_products_toolbar_wrapper">
				<span class="bundled_product_selector"><?php

					if ( WC_PB_Core_Compatibility::is_wc_version_gte_2_3() ) {

						?><input type="hidden" class="wc-product-search" style="width: 250px;" id="bundled_product" name="bundled_product" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products" data-multiple="false" data-selected="" value="" /><?php

					} else {

						?><select id="bundled_product" name="bundled_product" class="ajax_chosen_select_products" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce' ); ?>">
							<option></option>
						</select><?php
					}

				?></span>
				<button type="button" class="button button-primary add_bundled_product"><?php _e( 'Add Product', 'woocommerce-product-bundles' ); ?></button>
			</span>
		</p><?php
	}

	/**
	 * Handles getting bundled product meta box tabs - @see bundled_product_admin_html.
	 *
	 * @return array
	 */
	public function get_bundled_product_tabs() {

		return apply_filters( 'woocommerce_bundled_product_admin_html_tabs', array(
			array(
				'id'    => 'config',
				'title' => __( 'Basic Settings', 'woocommerce-product-bundles' ),
			),
			array(
				'id'    => 'advanced',
				'title' => __( 'Advanced Settings', 'woocommerce-product-bundles' ),
			)
		) );
	}

	/**
	 * Add bundled product "Basic" tab content.
	 *
	 * @param  int   $loop
	 * @param  int   $product_id
	 * @param  array $item_data
	 * @param  int   $post_id
	 * @return void
	 */
	public function bundled_product_admin_config_html( $loop, $product_id, $item_data, $post_id ) {

		$bundled_product = wc_get_product( $product_id );

		if ( $bundled_product->product_type === 'variable' ) {

			$allowed_variations = isset( $item_data[ 'allowed_variations' ] ) ? $item_data[ 'allowed_variations' ] : '';
			$default_attributes = isset( $item_data[ 'bundle_defaults' ] ) ? $item_data[ 'bundle_defaults' ] : '';

			$filter_variations = isset( $item_data[ 'filter_variations' ] ) ? $item_data[ 'filter_variations' ] : '';
			$override_defaults = isset( $item_data[ 'override_defaults' ] ) ? $item_data[ 'override_defaults' ] : '';

			?><div class="filtering">
				<div class="form-field filter_variations">
					<label for="filter_variations">
						<?php echo __( 'Filter Variations', 'woocommerce-product-bundles' ); ?>
					</label>
					<input type="checkbox" class="checkbox"<?php echo ( $filter_variations == 'yes' ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][filter_variations]" <?php echo ( $filter_variations == 'yes' ? 'value="1"' : '' ); ?>/>
					<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Check to enable only a subset of the available variations.', 'woocommerce-product-bundles' ) ); ?>
				</div>
			</div>


			<div class="bundle_variation_filters">
				<div class="form-field"><?php

					$variations = WC_PB()->helpers->get_product_variations( $product_id );
					$attributes = maybe_unserialize( get_post_meta( $product_id, '_product_attributes', true ) );

					if ( sizeof( $variations ) < 100 || ! WC_PB_Core_Compatibility::is_wc_version_gte_2_5() ) {

						?><select multiple="multiple" name="bundle_data[<?php echo $loop; ?>][allowed_variations][]" style="width: 95%;" data-placeholder="<?php _e( 'Choose variations&hellip;', 'woocommerce-product-bundles' ); ?>" class="<?php echo WC_PB_Core_Compatibility::is_wc_version_gte_2_3() ? 'wc-enhanced-select' : 'chosen_select'; ?>" > <?php

							foreach ( $variations as $variation_id ) {

								if ( is_array( $allowed_variations ) && in_array( $variation_id, $allowed_variations ) ) {
									$selected = 'selected="selected"';
								} else {
									$selected = '';
								}

								$variation_description = WC_PB()->helpers->get_product_variation_title( $variation_id );

								if ( ! $variation_description ) {
									continue;
								}

								echo '<option value="' . $variation_id . '" ' . $selected . '>' . $variation_description . '</option>';
							}

						?></select><?php

					} else {

						$allowed_variations_descriptions = array();

						if ( ! empty( $allowed_variations ) ) {

							foreach ( $allowed_variations as $allowed_variation_id ) {

								$variation_description = WC_PB()->helpers->get_product_variation_title( $allowed_variation_id );

								if ( ! $variation_description ) {
									continue;
								}

								$allowed_variations_descriptions[ $allowed_variation_id ] = $variation_description;
							}
						}

						?><input type="hidden" name="bundle_data[<?php echo $loop; ?>][allowed_variations]" class="wc-product-search" style="width: 95%;" data-placeholder="<?php _e( 'Search for variations&hellip;', 'woocommerce-product-bundles' ); ?>" data-limit="100" data-include="<?php echo esc_attr( implode( ', ', $variations ) ); ?>" data-action="woocommerce_search_bundled_variations" data-multiple="true" data-selected="<?php

							echo esc_attr( json_encode( $allowed_variations_descriptions ) );

						?>" value="<?php echo implode( ',', array_keys( $allowed_variations_descriptions ) ); ?>" /><?php
					}

				?></div>
			</div>

			<div class="defaults">
				<div class="form-field override_defaults">
					<label for="override_defaults"><?php echo __( 'Override Default Selections', 'woocommerce-product-bundles' ) ?></label>
					<input type="checkbox" class="checkbox"<?php echo ( $override_defaults == 'yes' ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][override_defaults]" <?php echo ( $override_defaults == 'yes' ? 'value="1"' : '' ); ?>/>
					<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'In effect for this bundle only. The available options are in sync with the filtering settings above. Always save any changes made above before configuring this section.', 'woocommerce-product-bundles' ) ); ?>
				</div>
			</div>

			<div class="bundle_selection_defaults">
				<div class="form-field"><?php

					foreach ( $attributes as $attribute ) {

						// Only deal with attributes that are variations.
						if ( ! $attribute[ 'is_variation' ] ) {
							continue;
						}

						// Get current value for variation (if set).
						$variation_selected_value = ( isset( $default_attributes[ sanitize_title( $attribute[ 'name' ] ) ] ) ) ? $default_attributes[ sanitize_title( $attribute[ 'name' ] ) ] : '';

						// Name will be something like attribute_pa_color.
						echo '<select name="bundle_data[' . $loop . '][default_attributes][' . sanitize_title( $attribute[ 'name' ] ) .']"><option value="">' . __( 'No default', 'woocommerce' ) . ' ' . wc_attribute_label( $attribute[ 'name' ] ) . '&hellip;</option>';

						// Get terms for attribute taxonomy or value if its a custom attribute.
						if ( $attribute[ 'is_taxonomy' ] ) {

							$post_terms = wp_get_post_terms( $product_id, $attribute[ 'name' ] );

							sort( $post_terms );

							foreach ( $post_terms as $term ) {
								echo '<option ' . selected( $variation_selected_value, $term->slug, false ) . ' value="' . esc_attr( $term->slug ) . '">' . apply_filters( 'woocommerce_variation_option_name', esc_html( $term->name ) ) . '</option>';
							}

						} else {

							$options = array_map( 'trim', explode( WC_DELIMITER, $attribute[ 'value' ] ) );

							sort( $options );

							foreach ( $options as $option ) {
								echo '<option ' . selected( sanitize_title( $variation_selected_value ), sanitize_title( $option ), false ) . ' value="' . esc_attr( sanitize_title( $option ) ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
							}
						}

						echo '</select>';
					}
				?></div>
			</div><?php
		}

		$item_quantity     = isset( $item_data[ 'bundle_quantity' ] ) ? absint( $item_data[ 'bundle_quantity' ] ) : 1;
		$item_quantity_max = $item_quantity;

		if ( isset( $item_data[ 'bundle_quantity_max' ] ) ) {
			if ( $item_data[ 'bundle_quantity_max' ] !== '' ) {
				$item_quantity_max = absint( $item_data[ 'bundle_quantity_max' ] );
			} else {
				$item_quantity_max = '';
			}
		}

		$per_product_pricing = get_post_meta( $post_id, '_per_product_pricing_active', true ) === 'yes' ? true : false;
		$item_discount       = isset( $item_data[ 'bundle_discount' ] ) ? $item_data[ 'bundle_discount' ] : '';
		$is_optional         = isset( $item_data[ 'optional' ] ) ? $item_data[ 'optional' ] : '';

		?><div class="optional">
			<div class="form-field optional">
				<label for="optional"><?php echo __( 'Optional', 'woocommerce-product-bundles' ) ?></label>
				<input type="checkbox" class="checkbox"<?php echo ( $is_optional === 'yes' ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][optional]" <?php echo ( $is_optional === 'yes' ? 'value="1"' : '' ); ?>/>
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Check this option to mark the bundled product as optional.', 'woocommerce-product-bundles' ) ); ?>
			</div>
		</div>

		<div class="quantity">
			<div class="form-field">
				<label><?php echo __( 'Quantity Min', 'woocommerce' ); ?></label>
				<input type="number" class="bundle_quantity" size="6" name="bundle_data[<?php echo $loop; ?>][bundle_quantity]" value="<?php echo $item_quantity; ?>" step="any" min="0" />
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'The minumum/default quantity of this bundled product.', 'woocommerce-product-bundles' ) ); ?>
			</div>
		</div>

		<div class="max_quantity">
			<div class="form-field">
				<label><?php echo __( 'Quantity Max', 'woocommerce-product-bundles' ); ?></label>
				<input type="number" class="bundle_quantity" size="6" name="bundle_data[<?php echo $loop; ?>][bundle_quantity_max]" value="<?php echo $item_quantity_max; ?>" step="any" min="0" />
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'The maximum quantity of this bundled product. Leave the field empty for an unlimited maximum quantity.', 'woocommerce-product-bundles' ) ); ?>
			</div>
		</div>

		<div class="discount">
			<div class="form-field">
				<label><?php echo __( 'Discount %', 'woocommerce' ); ?></label>
				<input type="text" <?php echo $per_product_pricing ? '' : 'disabled="disabled"'; ?> class="input-text bundle_discount wc_input_decimal" size="5" name="bundle_data[<?php echo $loop; ?>][bundle_discount]" value="<?php echo $item_discount; ?>" />
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Discount applied to the regular price of this bundled product when Per-Item Pricing is active. If a Discount is applied to a bundled product which has a sale price defined, the sale price will be overridden.', 'woocommerce-product-bundles' ) ); ?>
			</div>
		</div><?php
	}

	/**
	 * Add bundled product "Advanced" tab content.
	 *
	 * @param  int   $loop
	 * @param  int   $product_id
	 * @param  array $item_data
	 * @param  int   $post_id
	 * @return void
	 */
	public function bundled_product_admin_advanced_html( $loop, $product_id, $item_data, $post_id ) {

		$hide_thumbnail       = isset( $item_data[ 'hide_thumbnail' ] ) ? $item_data[ 'hide_thumbnail' ] : '';
		$override_title       = isset( $item_data[ 'override_title' ] ) ? $item_data[ 'override_title' ] : '';
		$override_description = isset( $item_data[ 'override_description' ] ) ? $item_data[ 'override_description' ] : '';

		$visibility = array(
			'product' => 'visible',
			'cart'    => 'visible',
			'order'   => 'visible',
		);

		// Visibility settings (refactored from string to array format).
		if ( ! empty( $item_data[ 'visibility' ] ) ) {
			if ( is_array( $item_data[ 'visibility' ] ) ) {
				$visibility[ 'product' ] = ! empty( $item_data[ 'visibility' ][ 'product' ] ) && $item_data[ 'visibility' ][ 'product' ] === 'hidden' ? 'hidden' : 'visible';
				$visibility[ 'cart' ]    = ! empty( $item_data[ 'visibility' ][ 'cart' ] ) && $item_data[ 'visibility' ][ 'cart' ] === 'hidden' ? 'hidden' : 'visible';
				$visibility[ 'order' ]   = ! empty( $item_data[ 'visibility' ][ 'order' ] ) && $item_data[ 'visibility' ][ 'order' ] === 'hidden' ? 'hidden' : 'visible';
			} else {
				if ( $item_data[ 'visibility' ] === 'hidden' ) {
					$visibility[ 'product' ] = 'hidden';
				} elseif ( $item_data[ 'visibility' ] === 'secret' ) {
					$visibility[ 'product' ] = $visibility[ 'cart' ] = $visibility[ 'order' ] = 'hidden';
				}
			}
		}

		?><div class="item_visibility">
			<div class="form-field">
				<label for="item_visibility"><?php _e( 'Visibility', 'woocommerce-product-bundles' ); ?></label>
				<div>
					<input type="checkbox" class="checkbox visibility_product"<?php echo ( $visibility[ 'product' ] === 'visible' ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][visibility][product]" <?php echo ( $visibility[ 'product' ] === 'visible' ? 'value="1"' : '' ); ?>/>
					<span><?php _e( 'Single-product template', 'woocommerce-product-bundles' ); ?></span>
					<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Controls the visibility of this bundled product in the single-product template. Not recommended for variable products, unless default attribute selections (or default selection overrides) have been set.', 'woocommerce-product-bundles' ) ); ?>
				</div>
				<div>
					<input type="checkbox" class="checkbox visibility_cart"<?php echo ( $visibility[ 'cart' ] === 'visible' ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][visibility][cart]" <?php echo ( $visibility[ 'cart' ] === 'visible' ? 'value="1"' : '' ); ?>/>
					<span><?php _e( 'Cart template', 'woocommerce-product-bundles' ); ?></span>
					<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Controls the visibility of this bundled product in the cart template.', 'woocommerce-product-bundles' ) ); ?>
				</div>
				<div>
					<input type="checkbox" class="checkbox visibility_order"<?php echo ( $visibility[ 'order' ] === 'visible' ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][visibility][order]" <?php echo ( $visibility[ 'order' ] === 'visible' ? 'value="1"' : '' ); ?>/>
					<span><?php _e( 'Order/e-mail templates', 'woocommerce-product-bundles' ); ?></span>
					<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Controls the visibility of this bundled product in the order &amp; e-mail templates.', 'woocommerce-product-bundles' ) ); ?>
				</div>
			</div>
		</div>

		<div class="images">
			<div class="form-field hide_thumbnail">
				<label for="hide_thumbnail"><?php echo __( 'Hide Thumbnail', 'woocommerce-product-bundles' ) ?></label>
				<input type="checkbox" class="checkbox"<?php echo ( $hide_thumbnail === 'yes' ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][hide_thumbnail]" <?php echo ( $hide_thumbnail === 'yes' ? 'value="1"' : '' ); ?>/>
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Check this option to hide the thumbnail image of this bundled product.', 'woocommerce-product-bundles' ) ); ?>
			</div>
		</div>

		<div class="override_title">
			<div class="form-field override_title">
				<label for="override_title"><?php echo __( 'Override Title', 'woocommerce-product-bundles' ) ?></label>
				<input type="checkbox" class="checkbox"<?php echo ( $override_title === 'yes' ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][override_title]" <?php echo ( $override_title === 'yes' ? 'value="1"' : '' ); ?>/>
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Check this option to override the default product title.', 'woocommerce-product-bundles' ) ); ?>
			</div>
		</div>

		<div class="custom_title">
			<div class="form-field product_title"><?php

				$title = isset( $item_data[ 'product_title' ] ) ? $item_data[ 'product_title' ] : '';

				?><textarea name="bundle_data[<?php echo $loop; ?>][product_title]" placeholder="" rows="2" cols="20"><?php echo esc_textarea( $title ); ?></textarea>
			</div>
		</div>

		<div class="override_description">
			<div class="form-field override_description">
				<label for="override_description"><?php echo __( 'Override Short Description', 'woocommerce-product-bundles' ) ?></label>
				<input type="checkbox" class="checkbox"<?php echo ( $override_description === 'yes' ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][override_description]" <?php echo ( $override_description === 'yes' ? 'value="1"' : '' ); ?>/>
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Check this option to override the default short product description.', 'woocommerce-product-bundles' ) ); ?>
			</div>
		</div>

		<div class="custom_description">
			<div class="form-field product_description"><?php

				$description = isset( $item_data[ 'product_description' ] ) ? $item_data[ 'product_description' ] : '';

				?><textarea name="bundle_data[<?php echo $loop; ?>][product_description]" placeholder="" rows="2" cols="20"><?php echo esc_textarea( $description ); ?></textarea>
			</div>
		</div><?php
	}

	/**
	 * Admin writepanel scripts.
	 *
	 * @return void
	 */
	public function woo_bundles_admin_scripts() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( WC_PB_Core_Compatibility::is_wc_version_gte_2_2() ) {
			$writepanel_dependency = 'wc-admin-meta-boxes';
		} else {
			$writepanel_dependency = 'woocommerce_admin_meta_boxes';
		}

		wp_register_script( 'woo_bundles_writepanel', WC_PB()->woo_bundles_plugin_url() . '/assets/js/bundled-product-write-panels' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', $writepanel_dependency ), WC_PB()->version );
		wp_register_style( 'woo_bundles_css', WC_PB()->woo_bundles_plugin_url() . '/assets/css/bundles-write-panels.css', array( 'woocommerce_admin_styles' ), WC_PB()->version );
		wp_register_style( 'woo_bundles_edit_order_css', WC_PB()->woo_bundles_plugin_url() . '/assets/css/bundles-edit-order.css', array( 'woocommerce_admin_styles' ), WC_PB()->version );

		// Get admin screen id.
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		// WooCommerce admin pages.
		if ( in_array( $screen_id, array( 'product' ) ) ) {
			wp_enqueue_script( 'woo_bundles_writepanel' );

			$params = array(
				'add_bundled_product_nonce' => wp_create_nonce( 'wc_bundles_add_bundled_product' ),
				'is_wc_version_gte_2_3'     => WC_PB_Core_Compatibility::is_wc_version_gte_2_3() ? 'yes' : 'no',
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

			wp_localize_script( 'woo_bundles_writepanel', 'wc_bundles_admin_params', $params );
		}

		if ( in_array( $screen_id, array( 'edit-product', 'product' ) ) ) {
			wp_enqueue_style( 'woo_bundles_css' );
		}

		if ( in_array( $screen_id, array( 'shop_order', 'edit-shop_order' ) ) ) {
			wp_enqueue_style( 'woo_bundles_edit_order_css' );
		}
	}

	/**
	 * Add Bundled Products write panel tab.
	 *
	 * @return void
	 */
	public function woo_bundles_product_write_panel_tab() {

		echo '<li class="bundled_product_tab show_if_bundle bundled_product_options linked_product_options"><a href="#bundled_product_data">'.__( 'Bundled Products', 'woocommerce-product-bundles' ).'</a></li>';
	}

	/**
	 * Write panel for Product Bundles.
	 *
	 * @return void
	 */
	public function woo_bundles_product_write_panel() {

		?><div id="bundled_product_data" class="panel woocommerce_options_panel">
			<?php do_action( 'woocommerce_bundled_products_admin_config' ); ?>
		</div><?php
	}

	/**
	 * Add Bundled Products stock note.
	 *
	 * @return void
	 */
	public function woo_bundles_stock_group() {

		global $post;

		?><span class="bundle_stock_msg show_if_bundle">
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'By default, the sale of a product within a bundle has the same effect on its stock as an individual sale. There are no separate inventory settings for bundled items. However, managing stock at bundle level can be very useful for allocating bundle stock quota, or for keeping track of bundled item sales.', 'woocommerce-product-bundles' ) ); ?>
		</span><?php
	}

	/**
	 * Product bundle options for post-1.6.2 product data section.
	 *
	 * @param  array    $options    product options
	 * @return array                modified product options
	 */
	public function woo_bundles_type_options( $options ) {

		$options[ 'per_product_shipping_active' ] = array(
			'id' 			=> '_per_product_shipping_active',
			'wrapper_class' => 'show_if_bundle',
			'label' 		=> __( 'Per-Item Shipping', 'woocommerce-product-bundles' ),
			'description' 	=> __( 'If your bundle consists of items that are assembled or packaged together, leave this box un-checked and define the shipping properties of the entire bundle below. If, however, the bundled items are shipped individually, check this option to retain their original shipping weight and dimensions. &quot;Per-Item Shipping&quot; should also be selected when the bundle consists of virtual items, which are not shipped.', 'woocommerce-product-bundles' ),
			'default'		=> 'no'
		);

		$options[ 'per_product_pricing_active' ] = array(
			'id' 			=> '_per_product_pricing_active',
			'wrapper_class' => 'show_if_bundle bundle_pricing',
			'label' 		=> __( 'Per-Item Pricing', 'woocommerce-product-bundles' ),
			'description' 	=> __( 'When enabled, the bundle will be priced per-item, based on standalone item prices and tax rates.', 'woocommerce-product-bundles' ),
			'default'		=> 'no'
		);

		return $options;
	}

	/**
	 * Process, verify and save bundle type product data.
	 *
	 * @param  int    $post_id    the product post id
	 * @return void
	 */
	public function woo_bundles_process_bundle_meta( $post_id ) {

		// Per-Item Pricing.

		if ( isset( $_POST[ '_per_product_pricing_active' ] ) ) {

			update_post_meta( $post_id, '_per_product_pricing_active', 'yes' );
			update_post_meta( $post_id, '_regular_price', '' );
			update_post_meta( $post_id, '_sale_price', '' );
			update_post_meta( $post_id, '_price', '' );

			// Update base price meta.
			if ( isset( $_POST[ '_wc_pb_base_regular_price'] ) ) {
				update_post_meta( $post_id, '_base_regular_price', ( $_POST[ '_wc_pb_base_regular_price' ] === '' ) ? '' : wc_format_decimal( $_POST[ '_wc_pb_base_regular_price' ] ) );
			}

			if ( isset( $_POST[ '_wc_pb_base_sale_price' ] ) ) {
				update_post_meta( $post_id, '_base_sale_price', ( $_POST[ '_wc_pb_base_sale_price' ] === '' ? '' : wc_format_decimal( $_POST[ '_wc_pb_base_sale_price' ] ) ) );
			}

			$date_from = isset( $_POST[ '_wc_pb_base_sale_price_dates_from' ] ) ? wc_clean( $_POST[ '_wc_pb_base_sale_price_dates_from' ] ) : '';
			$date_to   = isset( $_POST[ '_wc_pb_base_sale_price_dates_to' ] ) ? wc_clean( $_POST[ '_wc_pb_base_sale_price_dates_to' ] ) : '';

			// Dates.
			if ( $date_from ) {
				update_post_meta( $post_id, '_base_sale_price_dates_from', strtotime( $date_from ) );
			} else {
				update_post_meta( $post_id, '_base_sale_price_dates_from', '' );
			}

			if ( $date_to ) {
				update_post_meta( $post_id, '_base_sale_price_dates_to', strtotime( $date_to ) );
			} else {
				update_post_meta( $post_id, '_base_sale_price_dates_to', '' );
			}

			if ( $date_to && ! $date_from ) {
				update_post_meta( $post_id, '_base_sale_price_dates_from', strtotime( 'NOW', current_time( 'timestamp' ) ) );
			}

			// Update price if on sale
			if ( '' !== $_POST[ '_wc_pb_base_sale_price' ] && '' == $date_to && '' == $date_from ) {
				update_post_meta( $post_id, '_base_price', wc_format_decimal( $_POST[ '_wc_pb_base_sale_price' ] ) );
			} else {
				update_post_meta( $post_id, '_base_price', ( $_POST[ '_wc_pb_base_regular_price' ] === '' ) ? '' : wc_format_decimal( $_POST[ '_wc_pb_base_regular_price' ] ) );
			}

			if ( '' !== $_POST[ '_wc_pb_base_sale_price' ] && $date_from && strtotime( $date_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
				update_post_meta( $post_id, '_base_price', wc_format_decimal( $_POST[ '_wc_pb_base_sale_price' ] ) );
			}

			if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
				update_post_meta( $post_id, '_base_price', ( $_POST[ '_wc_pb_base_regular_price' ] === '' ) ? '' : wc_format_decimal( $_POST[ '_wc_pb_base_regular_price' ] ) );
				update_post_meta( $post_id, '_base_sale_price_dates_from', '' );
				update_post_meta( $post_id, '_base_sale_price_dates_to', '' );
			}

		} else {
			update_post_meta( $post_id, '_per_product_pricing_active', 'no' );
		}

		// Per-Item Shipping.

		if ( isset( $_POST[ '_per_product_shipping_active' ] ) ) {
			update_post_meta( $post_id, '_per_product_shipping_active', 'yes' );
			update_post_meta( $post_id, '_virtual', 'yes' );
			update_post_meta( $post_id, '_weight', '' );
			update_post_meta( $post_id, '_length', '' );
			update_post_meta( $post_id, '_width', '' );
			update_post_meta( $post_id, '_height', '' );
		} else {
			update_post_meta( $post_id, '_per_product_shipping_active', 'no' );
			update_post_meta( $post_id, '_virtual', 'no' );
			update_post_meta( $post_id, '_weight', stripslashes( $_POST[ '_weight' ] ) );
			update_post_meta( $post_id, '_length', stripslashes( $_POST[ '_length' ] ) );
			update_post_meta( $post_id, '_width', stripslashes( $_POST[ '_width' ] ) );
			update_post_meta( $post_id, '_height', stripslashes( $_POST[ '_height' ] ) );
		}

		$posted_bundle_data = isset( $_POST[ 'bundle_data' ] ) ? $_POST[ 'bundle_data' ] : false;

		if ( ! $posted_bundle_data || false === $processed_bundle_data = $this->build_bundle_config( $post_id, $posted_bundle_data ) ) {

			delete_post_meta( $post_id, '_bundle_data' );

			$this->add_admin_error( __( 'Please add at least one product to the bundle before publishing. To add products, click on the <strong>Bundled Products</strong> tab.', 'woocommerce-product-bundles' ) );

			global $wpdb;
			$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $post_id ) );

			return;

		} else {

			update_post_meta( $post_id, '_bundle_data', $processed_bundle_data );
		}

		// Delete no longer used meta.
		delete_post_meta( $post_id, '_min_bundle_price' );
		delete_post_meta( $post_id, '_max_bundle_price' );
	}

	/**
	 * Update bundle post_meta on save.
	 *
	 * @return 	mixed     bundle data array configuration or false if unsuccessful
	 */
	public function build_bundle_config( $post_id, $posted_bundle_data ) {

		// Process Bundled Product Configuration.
		$bundle_data         = array();
		$ordered_bundle_data = array();

		$bundle_data_old = get_post_meta( $post_id, '_bundle_data', true );

		// Now start saving new data.
		$times         = array();
		$save_defaults = array();
		$ordering      = array();

		if ( ! empty( $posted_bundle_data ) ) {

			foreach ( $posted_bundle_data as $val => $data ) {

				$id = isset( $data[ 'product_id' ] ) ? $data[ 'product_id' ] : false;

				if ( ! $id ) {
					continue;
				}

				$terms        = get_the_terms( $id, 'product_type' );
				$product_type = ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';

				$is_sub = $product_type === 'subscription' || $product_type === 'variable-subscription';

				if ( ( $id && $id > 0 ) && ( $product_type === 'simple' || $product_type === 'variable' || $is_sub ) && ( $post_id != $id ) ) {

					if ( $is_sub ) {
						if ( ! class_exists( 'WC_Subscriptions' ) || version_compare( WC_Subscriptions::$version, '2.0.0', '<' ) ) {
							$this->add_admin_error( sprintf( __( '&quot;%1$s&quot; (#%2$s) was not saved. WooCommerce Subscriptions version 2.0 or higher is required in order to bundle Subscription products.', 'woocommerce-product-bundles' ), get_the_title( $id ), $id ) );
							continue;
						}
					}

					// Allow bundling the same item id multiple times by adding a suffix.
					if ( ! isset( $times[ $id ] ) ) {

						$times[ $id ] 	= 1;
						$val 			= $id;

					} else {

						// Only allow multiple instances of non-sold-individually items.
						if ( get_post_meta( $id, '_sold_individually', true ) == 'yes' ) {

							$this->add_admin_error( sprintf( __( '&quot;%1$s&quot; (#%2$s) is sold individually and cannot be bundled more than once.', 'woocommerce-product-bundles' ), get_the_title( $id ), $id ) );
							continue;

						}

						$times[ $id ] += 1;
						$val = isset( $data[ 'item_id' ] ) ? $data[ 'item_id' ] : $id . '_' . $times[ $id ];
					}

					$bundle_data[ $val ] = array();

					$bundle_data[ $val ][ 'product_id' ] = $id;

					// Save thumbnail preferences first.
					if ( isset( $data[ 'hide_thumbnail' ] ) ) {
						$bundle_data[ $val ][ 'hide_thumbnail' ] = 'yes';
					} else {
						$bundle_data[ $val ][ 'hide_thumbnail' ] = 'no';
					}

					// Save title preferences.
					if ( isset( $data[ 'override_title' ] ) ) {
						$bundle_data[ $val ][ 'override_title' ] = 'yes';
						$bundle_data[ $val ][ 'product_title' ] = isset( $data[ 'product_title' ] ) ? $data[ 'product_title' ] : '';
					} else {
						$bundle_data[ $val ][ 'override_title' ] = 'no';
					}

					// Save description preferences.
					if ( isset( $data[ 'override_description' ] ) ) {
						$bundle_data[ $val ][ 'override_description' ] = 'yes';
						$bundle_data[ $val ][ 'product_description' ] = isset( $data[ 'product_description' ] ) ? wp_kses_post( stripslashes( $data[ 'product_description' ] ) ) : '';
					} else {
						$bundle_data[ $val ][ 'override_description' ] = 'no';
					}

					// Save optional.
					if ( isset( $data[ 'optional' ] ) ) {
						$bundle_data[ $val ][ 'optional' ] = 'yes';
					} else {
						$bundle_data[ $val ][ 'optional' ] = 'no';
					}

					// Save quantity data.
					if ( isset( $data[ 'bundle_quantity' ] ) ) {

						if ( is_numeric( $data[ 'bundle_quantity' ] ) ) {

							$quantity = absint( $data[ 'bundle_quantity' ] );

							if ( $quantity >= 0 && $data[ 'bundle_quantity' ] - $quantity == 0 ) {

								if ( $quantity !== 1 && get_post_meta( $id, '_sold_individually', true ) === 'yes' ) {

									$this->add_admin_error( sprintf( __( '&quot;%1$s&quot; (#%2$s) is sold individually. Its minimum quantity cannot be higher than 1.', 'woocommerce-product-bundles' ), get_the_title( $id ), $id ) );
									$bundle_data[ $val ][ 'bundle_quantity' ] = 1;

								} else {
									$bundle_data[ $val ][ 'bundle_quantity' ] = $quantity;
								}

							} else {

								$this->add_admin_error( sprintf( __( 'The quantity you entered for &quot;%1$s%2$s&quot; (#%3$s) was not valid and has been reset. Please enter a non-negative integer value.', 'woocommerce-product-bundles' ), get_the_title( $id ), ( $id != $val ? ' #' . $times[ $id ] : '' ), $id ) );
							}
						}

					} else {

						$bundle_data[ $val ][ 'bundle_quantity' ] = 1;
					}

					$quantity_min = $bundle_data[ $val ][ 'bundle_quantity' ];

					// Save max quantity data.
					if ( isset( $data[ 'bundle_quantity_max' ] ) && ( is_numeric( $data[ 'bundle_quantity_max' ] ) || $data[ 'bundle_quantity_max' ] === '' ) ) {

						$quantity = $data[ 'bundle_quantity_max' ] !== '' ? absint( $data[ 'bundle_quantity_max' ] ) : '';

						if ( $quantity === '' || ( $quantity > 0 && $quantity >= $quantity_min && $data[ 'bundle_quantity_max' ] - $quantity == 0 ) ) {

							if ( $quantity !== 1 && get_post_meta( $id, '_sold_individually', true ) === 'yes' ) {

								$this->add_admin_error( sprintf( __( '&quot;%1$s&quot; (#%2$s) is sold individually. Its maximum quantity cannot be higher than 1.', 'woocommerce-product-bundles' ), get_the_title( $id ), $id ) );
								$bundle_data[ $val ][ 'bundle_quantity_max' ] = 1;

							} else {
								$bundle_data[ $val ][ 'bundle_quantity_max' ] = $quantity;
							}

						} else {

							$this->add_admin_error( sprintf( __( 'The maximum product quantity that you entered for &quot;%1$s%2$s&quot; (#%3$s) was not valid and has been reset. Please enter a positive integer value, at least as high as the minimum quantity. Otherwise, leave the field empty for an unlimited maximum quantity.', 'woocommerce-product-bundles' ), get_the_title( $id ), ( $id != $val ? ' #' . $times[ $id ] : '' ), $id ) );
						}

					} else {
						$bundle_data[ $val ][ 'bundle_quantity_max' ] = max( $quantity_min, 1 );
					}

					// Save sale price data.
					if ( isset( $data[ 'bundle_discount' ] ) ) {

						if ( is_numeric( $data[ 'bundle_discount' ] ) ) {

							$discount = ( float ) wc_format_decimal( $data[ 'bundle_discount' ] );

							if ( $discount < 0 || $discount > 100 ) {

								$this->add_admin_error( sprintf( __( 'The discount value you entered for &quot;%1$s%2$s&quot; (#%3$s) was not valid and has been reset. Please enter a positive number between 0-100.', 'woocommerce-product-bundles' ), get_the_title( $id ), ( $id != $val ? ' #' . $times[$id] : '' ), $id ) );
								$bundle_data[ $val ][ 'bundle_discount' ] = '';

							} else {
								$bundle_data[ $val ][ 'bundle_discount' ] = $discount;
							}
						} else {
							$bundle_data[ $val ][ 'bundle_discount' ] = '';
						}
					} else {
						$bundle_data[ $val ][ 'bundle_discount' ] = '';
					}

					// Save data related to variable items.
					if ( $product_type === 'variable' ) {

						$allowed_variations = array();

						// Save variation filtering options.
						if ( isset( $data[ 'filter_variations' ] ) ) {

							if ( isset( $data[ 'allowed_variations' ] ) ) {

								if ( is_array( $data[ 'allowed_variations' ] ) ) {
									$allowed_variations = array_map( 'intval', $data[ 'allowed_variations' ] );
								} else {
									$allowed_variations = array_filter( array_map( 'intval', explode( ',', $data[ 'allowed_variations' ] ) ) );
								}

								if ( count( $allowed_variations ) > 0 ) {

									$bundle_data[ $val ][ 'filter_variations' ] = 'yes';

									$bundle_data[ $val ][ 'allowed_variations' ] = $allowed_variations;

									if ( isset( $data[ 'hide_filtered_variations' ] ) ) {
										$bundle_data[ $val ][ 'hide_filtered_variations' ] = 'yes';
									} else {
										$bundle_data[ $val ][ 'hide_filtered_variations' ] = 'no';
									}
								}
							}
							else {
								$bundle_data[ $val ][ 'filter_variations' ] = 'no';
								$this->add_admin_error( __( 'Please select at least one variation for each bundled product you want to filter.', 'woocommerce-product-bundles' ) );
							}
						} else {
							$bundle_data[ $val ][ 'filter_variations' ] = 'no';
						}

						// Save defaults options.
						if ( isset( $data[ 'override_defaults' ] ) ) {

							if ( isset( $data[ 'default_attributes' ] ) ) {

								// If filters are set, check that the selections are valid.

								if ( isset( $data[ 'filter_variations' ] ) && ! empty( $allowed_variations ) ) {

									// The array to store all valid attribute options of the iterated product.
									$filtered_attributes = array();

									// Populate array with valid attributes.
									foreach ( $allowed_variations as $variation ) {

										$variation_data = array();

										// Sweep the post meta for attributes.
										if ( WC_PB_Core_Compatibility::is_wc_version_gte_2_4() ) {
											$variation_data = wc_get_product_variation_attributes( $variation );
										} else {
											$post_meta = get_post_meta( $variation );

											foreach ( $post_meta as $field => $value ) {

												if ( ! strstr( $field, 'attribute_' ) ) {
													continue;
												}

												$variation_data[ $field ] = $value[0];
											}
										}

										foreach ( $variation_data as $name => $value ) {

											$attribute_name  = substr( $name, strlen( 'attribute_' ) );
											$attribute_value = sanitize_title( $value );

											// Populate array.
											if ( ! isset( $filtered_attributes[ sanitize_title( $attribute_name ) ] ) ) {
												$filtered_attributes[ sanitize_title( $attribute_name ) ][] = $attribute_value;
											} elseif ( ! in_array( $attribute_value, $filtered_attributes[ sanitize_title( $attribute_name ) ] ) ) {
												$filtered_attributes[ sanitize_title( $attribute_name ) ][] = $attribute_value;
											}
										}

									}

									// Check validity.
									foreach ( $data[ 'default_attributes' ] as $sanitized_name => $value ) {

										if ( $value === '' ) {
											continue;
										}

										if ( ! in_array( sanitize_title( $value ), $filtered_attributes[ $sanitized_name ] ) && ! in_array( '', $filtered_attributes[ $sanitized_name ] ) ) {

											// Set option to "Any".
											$data[ 'default_attributes' ][ $sanitized_name ] = '';

											// Throw an error.
											$this->add_admin_error( sprintf( __( 'The defaults that you selected for &quot;%1$s%2$s&quot; (#%3$s) are inconsistent with the set of active variations. Always double-check your preferences before saving, and always save any changes made to the variation filters before choosing new defaults.', 'woocommerce-product-bundles' ), get_the_title( $id ), ( $id != $val ? ' #' . $times[$id] : '' ), $id ) );

											continue;
										}
									}
								}

								// Save.
								foreach ( $data[ 'default_attributes' ] as $sanitized_name => $value ) {
									$bundle_data[ $val ][ 'bundle_defaults' ][ $sanitized_name ] = $value;
								}

								$bundle_data[ $val ][ 'override_defaults' ] = 'yes';
							}

						} else {

							$bundle_data[ $val ][ 'override_defaults' ] = 'no';
						}
					}

					// Save visibility preferences.
					$visibility = array(
						'product' => 'visible',
						'cart'    => 'visible',
						'order'   => 'visible',
					);

					$visibility[ 'product' ] = isset( $data[ 'visibility' ][ 'product' ] ) ? 'visible' : 'hidden';
					$visibility[ 'cart' ]    = isset( $data[ 'visibility' ][ 'cart' ] ) ? 'visible' : 'hidden';
					$visibility[ 'order' ]   = isset( $data[ 'visibility' ][ 'order' ] ) ? 'visible' : 'hidden';

					if ( $visibility[ 'product' ] === 'hidden' ) {

						if ( $product_type === 'variable' ) {

							if ( $bundle_data[ $val ][ 'override_defaults' ] === 'yes' ) {

								if ( isset( $data[ 'default_attributes' ] ) ) {

									foreach ( $data[ 'default_attributes' ] as $default_name => $default_value ) {

										if ( ! $default_value ) {

											$visibility[ 'product' ] = 'visible';
											$this->add_admin_error( sprintf( __( '&quot;%1$s%2$s&quot; (#%s) cannot be hidden from the single-product template unless all default options of the bundled product are defined.', 'woocommerce-product-bundles' ), get_the_title( $id ), ( $id != $val ? ' #' . $times[$id] : '' ), $id ) );
											break;
										}
									}

								} else {

									$visibility[ 'product' ] = 'visible';
								}

							} else {

								$this->add_admin_error( sprintf( __( '&quot;%1$s%2$s&quot; (#%3$s) cannot be hidden from the single-product template unless all default options of the bundled product are defined.', 'woocommerce-product-bundles' ), get_the_title( $id ), ( $id != $val ? ' #' . $times[$id] : '' ), $id ) );
								$visibility[ 'product' ] = 'visible';
							}
						}
					}

					$bundle_data[ $val ][ 'visibility' ] = $visibility;


					// Save position data.
					if ( isset( $data[ 'bundle_order' ] ) ) {
						$ordering[ (int) $data[ 'bundle_order' ] ] = $val;
					} else {
						$ordering[ count( $ordering ) ] = $val;
					}

					$bundle_data[ $val ] = apply_filters( 'woocommerce_bundles_process_bundled_item_admin_data', $bundle_data[ $val ], $data, $val, $post_id );
				}
			}

			// Check empty.
			if ( empty( $bundle_data ) ) {
				return false;
			}

			// Sorting
			ksort( $ordering );
			$ordered_bundle_data = array();

			foreach ( $ordering as $item_id ) {
			    $ordered_bundle_data[ $item_id ] = $bundle_data[ $item_id ];
			}

			return $ordered_bundle_data;

		} else {

			return false;
		}
	}

	/**
	 * Add the 'bundle' product type to the product type dropdown.
	 *
	 * @param  array    $options    product types array
	 * @return array                modified product types array
	 */
	public function woo_bundles_product_selector_filter( $options ) {

		$options[ 'bundle' ] = __( 'Product bundle', 'woocommerce-product-bundles' );

		return $options;
	}

	/**
	 * Ajax search for bundled variations.
	 *
	 * @return void
	 */
	public function ajax_search_bundled_variations() {

		WC_AJAX::json_search_products( '', array( 'product_variation' ) );
	}

	/**
	 * Handles adding bundled products via ajax.
	 *
	 * @return void
	 */
	public function ajax_add_bundled_product() {

		check_ajax_referer( 'wc_bundles_add_bundled_product', 'security' );

		$loop       = intval( $_POST[ 'id' ] );
		$post_id    = intval( $_POST[ 'post_id' ] );
		$product_id = intval( $_POST[ 'product_id' ] );
		$item_id    = false;
		$toggle     = 'open';
		$tabs       = $this->get_bundled_product_tabs();

		$title      = WC_PB()->helpers->get_product_title( $product_id );
		$product    = wc_get_product( $product_id );
		$item_data  = array();

		$response   = array();

		$response[ 'markup' ]  = '';
		$response[ 'message' ] = '';

		if ( $title && $product ) {

			if ( in_array( $product->product_type, array( 'simple', 'variable', 'subscription', 'variable-subscription' ) ) ) {

				ob_start();
				include( 'html-bundled-product-admin.php' );
				$response[ 'markup' ] = ob_get_clean();

			} else {

				$response[ 'message' ] = __( 'The selected product cannot be bundled. Please select a simple product, a variable product, or a simple/variable subscription.', 'woocommerce-product-bundles' );
			}

		} else {
			$response[ 'message' ] = __( 'The selected product is invalid.', 'woocommerce-product-bundles' );
		}

		header( 'Content-Type: application/json; charset=utf-8' );
		echo json_encode( $response );

		die();
	}

	/**
	 * Support scanning for template overrides in extension.
	 *
	 * @param  array   $paths paths to check
	 * @return array          modified paths to check
	 */
	public function woo_bundles_template_scan_path( $paths ) {

		$paths[ 'WooCommerce Product Bundles' ] = WC_PB()->woo_bundles_plugin_path() . '/templates/';

		return $paths;
	}

	/**
	 * Add admin notices.
	 *
	 * @param string $content
	 * @param string $type
	 */
	public function add_admin_notice( $content, $type ) {

		WC_PB_Admin_Notices::add_notice( $content, $type, true );
	}

	/**
	 * Add admin errors.
	 *
	 * @param  string $error
	 * @return string
	 */
	public function add_admin_error( $error ) {

		$this->add_admin_notice( $error, 'error' );
	}
}

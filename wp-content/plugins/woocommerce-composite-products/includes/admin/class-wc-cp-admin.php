<?php
/**
 * Admin filters and functions.
 *
 * @class   WC_CP_Admin
 * @version 3.6.6
 * @since   2.2.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_CP_Admin {

	private $saved_notices = array();

	public function __construct() {

		// Admin jquery.
		add_action( 'admin_enqueue_scripts', array( $this, 'composite_admin_scripts' ) );

		// Creates the admin Components and Scenarios panel tabs.
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'composite_write_panel_tabs' ) );

		// Adds the base price options.
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'composite_pricing_options' ) );

		// Creates the admin Components and Scenarios panels.
		add_action( 'woocommerce_product_write_panels', array( $this, 'composite_write_panel' ) );
		add_action( 'woocommerce_product_options_stock', array( $this, 'composite_stock_info' ) );

		// Allows the selection of the 'composite product' type.
		add_filter( 'product_type_options', array( $this, 'add_composite_type_options' ) );

		// Processes and saves the necessary post metas from the selections made above.
		add_action( 'woocommerce_process_product_meta_composite', array( $this, 'process_composite_meta' ) );

		// Allows the selection of the 'composite product' type.
		add_filter( 'product_type_selector', array( $this, 'add_composite_type' ) );

		// Ajax save composite config.
		add_action( 'wp_ajax_woocommerce_bto_composite_save', array( $this, 'ajax_composite_save' ) );

		// Ajax add component.
		add_action( 'wp_ajax_woocommerce_add_composite_component', array( $this, 'ajax_add_component' ) );

		// Ajax add scenario.
		add_action( 'wp_ajax_woocommerce_add_composite_scenario', array( $this, 'ajax_add_scenario' ) );

		// Ajax search default component id.
		add_action( 'wp_ajax_woocommerce_json_search_default_component_option', array( $this, 'json_search_default_component_option' ) );

		// Ajax search products and variations in scenarios.
		add_action( 'wp_ajax_woocommerce_json_search_component_options_in_scenario', array( $this, 'json_search_component_options_in_scenario' ) );

		// Template override scan path.
		add_filter( 'woocommerce_template_overrides_scan_paths', array( $this, 'composite_template_scan_path' ) );

		/*----------------------------------*/
		/*  Composite writepanel options.   */
		/*----------------------------------*/

		add_action( 'woocommerce_composite_admin_html', array( $this, 'composite_layout_options' ), 10, 2 );
		add_action( 'woocommerce_composite_admin_html', array( $this, 'composite_component_options' ), 15, 2 );

		/*---------------------------------*/
		/*  Component meta boxes.          */
		/*---------------------------------*/

		add_action( 'woocommerce_composite_component_admin_html', array( $this, 'component_admin_html' ), 10, 4 );

		// Basic component config options
		add_action( 'woocommerce_composite_component_admin_config_html', array( $this, 'component_config_title' ), 10, 3 );
		add_action( 'woocommerce_composite_component_admin_config_html', array( $this, 'component_config_description' ), 15, 3 );
		add_action( 'woocommerce_composite_component_admin_config_html', array( $this, 'component_config_options' ), 20, 3 );
		add_action( 'woocommerce_composite_component_admin_config_html', array( $this, 'component_config_options_style' ), 20, 3 );
		add_action( 'woocommerce_composite_component_admin_config_html', array( $this, 'component_config_quantity_min' ), 25, 3 );
		add_action( 'woocommerce_composite_component_admin_config_html', array( $this, 'component_config_quantity_max' ), 33, 3 );
		add_action( 'woocommerce_composite_component_admin_config_html', array( $this, 'component_config_discount' ), 35, 3 );
		add_action( 'woocommerce_composite_component_admin_config_html', array( $this, 'component_config_optional' ), 40, 3 );

		// Advanced component configuration
		add_action( 'woocommerce_composite_component_admin_advanced_html', array( $this, 'component_config_default_option' ), 5, 3 );
		add_action( 'woocommerce_composite_component_admin_advanced_html', array( $this, 'component_sort_filter_show_orderby' ), 10, 3 );
		add_action( 'woocommerce_composite_component_admin_advanced_html', array( $this, 'component_sort_filter_show_filters' ), 15, 3 );
		add_action( 'woocommerce_composite_component_admin_advanced_html', array( $this, 'component_layout_hide_product_title' ), 20, 3 );
		add_action( 'woocommerce_composite_component_admin_advanced_html', array( $this, 'component_layout_hide_product_description' ), 25, 3 );
		add_action( 'woocommerce_composite_component_admin_advanced_html', array( $this, 'component_layout_hide_product_thumbnail' ), 30, 3 );
		add_action( 'woocommerce_composite_component_admin_advanced_html', array( $this, 'component_id_marker' ), 100, 3 );

		/*----------------------------*/
		/* Scenario meta boxes html   */
		/*----------------------------*/

		add_action( 'woocommerce_composite_scenario_admin_html', array( $this, 'scenario_admin_html' ), 10, 5 );

		// Scenario options.
		add_action( 'woocommerce_composite_scenario_admin_info_html', array( $this, 'scenario_info' ), 10, 4 );
		add_action( 'woocommerce_composite_scenario_admin_config_html', array( $this, 'scenario_config' ), 10, 4 );
		add_action( 'woocommerce_composite_scenario_admin_actions_html', array( $this, 'scenario_actions' ), 10, 4 );

		/*-----------------------------*/
		/* Reset query cache on save.  */
		/*-----------------------------*/

		add_action( 'woocommerce_delete_product_transients', array( $this, 'delete_cp_query_transients' ) );

		/*-----------------------------*/
		/* Scheduled base sale price.  */
		/*-----------------------------*/

		add_action( 'woocommerce_scheduled_sales', array( $this, 'scheduled_sales' ) );

		/*-----------------------------*/
		/* Sold Individually Options.  */
		/*-----------------------------*/

		add_action( 'woocommerce_product_options_sold_individually', array( $this, 'sold_individually_options' ) );

		/*-----------------------------------*/
		/* Editing in Cart Option.           */
		/*-----------------------------------*/

		add_action( 'woocommerce_product_options_advanced', array( $this, 'edit_in_cart_option' ) );
	}

	/**
	 * Enables the "Edit in Cart".
	 *
	 * @return void
	 */
	public function edit_in_cart_option() {

		global $thepostid;

		echo '<div class="options_group show_if_composite">';

		woocommerce_wp_checkbox( array( 'id' => '_bto_edit_in_cart', 'label' => __( 'Allow editing in cart', 'woocommerce' ), 'desc_tip' => true, 'description' => __( 'Enable this option to allow editing this Composite product after it has been added to the cart.', 'woocommerce-composite-products' ) ) );

		echo '</div>';
	}

	/**
	 * Renders additional "Sold Individually" options.
	 *
	 * @return void
	 */
	public function sold_individually_options() {

		global $thepostid;

		$sold_individually       = get_post_meta( $thepostid, '_sold_individually', true );
		$sold_individually_level = get_post_meta( $thepostid, '_bto_sold_individually', true );

		$value = 'no';

		if ( $sold_individually === 'yes' ) {
			if ( ! $sold_individually_level ) {
				$value = 'configuration';
			} else {
				$value = $sold_individually_level;
			}
		}

		// Extend "Sold Individually" options to account for different configurations.
		woocommerce_wp_select( array(
			'id'            => '_bto_sold_individually',
			'wrapper_class' => 'show_if_composite',
			'label'         => __( 'Sold Individually', 'woocommerce' ),
			'options'       => array(
				'no'            => __( 'No', 'woocommerce-composite-products' ),
				'product'       => __( 'Yes', 'woocommerce-composite-products' ),
				'configuration' => __( 'Matching configurations only', 'woocommerce-composite-products' )
			),
			'value'         => $value,
			'desc_tip'      => 'true',
			'description'   => __( 'Allow only one of this item (or only one of each unique configuration of this item) to be bought in a single order.', 'woocommerce-composite-products' )
		) );
	}

	/**
	 * Renders the composite writepanel Layout Options section before the Components section.
	 *
	 * @param  array $composite_data
	 * @param  int   $post_id
	 * @return void
	 */
	public function composite_layout_options( $composite_data, $post_id ) {

		?><div class="options_group bundle_group bto_clearfix">

			<div class="bto_layouts bto_clearfix form-field">
				<label class="bundle_group_label">
					<?php _e( 'Composite Layout', 'woocommerce-composite-products' ); ?>
					<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( 'Choose a layout for this Composite product.', 'woocommerce-composite-products' ) ); ?>
				</label>
				<ul class="bto_clearfix bto_layouts_list">
					<?php
					$layouts         = WC_CP()->api->get_layout_options();
					$selected_layout = WC_CP()->api->get_selected_layout_option( get_post_meta( $post_id, '_bto_style', true ) );

					foreach ( $layouts as $layout_id => $layout_description ) {

						/**
						 * Filter the image associated with a layout.
						 *
						 * @param  string $image_src
						 * @param  string $layout_id
						 */
						$layout_src     = apply_filters( 'woocommerce_composite_product_layout_image_src', WC_CP()->plugin_url() . '/assets/images/' . $layout_id . '.png', $layout_id );
						$layout_tooltip = WC_CP()->api->get_layout_tooltip( $layout_id );

						?><li><label class="bto_layout_label <?php echo $selected_layout == $layout_id ? 'selected' : ''; ?>">
							<img class="layout_img" src="<?php echo $layout_src; ?>" />
							<input <?php echo $selected_layout == $layout_id ? 'checked="checked"' : ''; ?> name="bto_style" type="radio" value="<?php echo $layout_id; ?>" />
							<span><?php echo $layout_description . ' ' . $layout_tooltip; ?></span>
						</label></li><?php
					}

				?></ul>
			</div>

			<?php

			/**
			 * Action 'woocommerce_composite_admin_after_layout_options':
			 *
			 * @param  array   $composite_data
			 * @param  string  $post_id
			 */
			do_action( 'woocommerce_composite_admin_after_layout_options', $composite_data, $post_id );

		?></div><?php
	}

	/**
	 * Renders the composite writepanel Layout Options section before the Components section.
	 *
	 * @param  array $composite_data
	 * @param  int   $post_id
	 * @return void
	 */
	public function composite_component_options( $composite_data, $post_id ) {

		?><div class="options_group config_group bto_clearfix">
			<p class="toolbar">
				<span class="options_group_h3">
					<?php _e( 'Components', 'woocommerce-composite-products' ); ?>
					<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( '<strong>Components</strong> are the building blocks of a Composite product. Every Component includes an assortment of products to choose from - the <strong>Component Options</strong>.', 'woocommerce-composite-products' ) ); ?>
				</span>
				<a href="#" class="close_all"><?php _e( 'Close all', 'woocommerce' ); ?></a>
				<a href="#" class="expand_all"><?php _e( 'Expand all', 'woocommerce' ); ?></a>
			</p>

			<div id="bto_config_group_inner">

				<div class="bto_groups wc-metaboxes ui-sortable" data-count="">

					<?php

					if ( $composite_data ) {

						$i = 0;

						foreach ( $composite_data as $group_id => $data ) {

							// Compat with old CP versions that didn't store the component_id here.
							if ( ! isset( $data[ 'component_id' ] ) ) {
								$data[ 'component_id' ] = $group_id;
							}

							/**
							 * Action 'woocommerce_composite_component_admin_html'.
							 *
							 * @param  int     $i
							 * @param  array   $data
							 * @param  string  $post_id
							 * @param  string  $state
							 *
							 * @hooked {@see component_admin_html} - 10
							 */
							do_action( 'woocommerce_composite_component_admin_html', $i, $data, $post_id, 'closed' );

							$i++;
						}
					}

				?></div>
			</div>

			<p class="toolbar borderless">
				<button type="button" class="button save_composition"><?php _e( 'Save Configuration', 'woocommerce-composite-products' ); ?></button>
				<button type="button" class="button button-primary add_bto_group"><?php _e( 'Add Component', 'woocommerce-composite-products' ); ?></button>
			</p>
		</div><?php
	}

	/**
	 * Renders the composite writepanel Layout Options section before the Components section.
	 *
	 * @param  array $composite_data
	 * @param  int   $post_id
	 * @return void
	 */
	public function layout_options_admin_html( $composite_data, $post_id ) {
		?>

		<?php
	}

	/**
	 * Function which handles the start and end of scheduled sales via cron.
	 *
	 * @access public
	 * @return void
	 */
	public function scheduled_sales() {
		global $wpdb;

		// Sales which are due to start.
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
	 * Add a component id watermark in the 'Advanced Configuration' tab.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public function component_id_marker( $id, $data, $product_id ) {

		if ( ! empty( $data[ 'component_id' ] ) ) {

			?><span class="group_id">
				<?php echo sprintf( __( '#id: %s', 'woocommerce-composite-products' ), $data[ 'component_id' ] ); ?>
			</span><?php
		}
	}

	/**
	 * Handles getting component meta box tabs - @see 'component_admin_html'.
	 *
	 * @return array
	 */
	public function get_component_tabs() {

		/**
		 * Filter the tab sections that appear in every Component metabox.
		 *
		 * @param  array  $tabs
		 */
		return apply_filters( 'woocommerce_composite_component_admin_html_tabs', array(
			'config' => array(
				'title'   => __( 'Basic Configuration', 'woocommerce-composite-products' )
			),
			'advanced' => array(
				'title'   => __( 'Advanced Configuration', 'woocommerce-composite-products' )
			)
		) );
	}

	/**
	 * Load component meta box in 'woocommerce_composite_component_admin_html'.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $post_id
	 * @param  string $toggle
	 * @return void
	 */
	public function component_admin_html( $id, $data, $post_id, $toggle = 'closed' ) {

		$tabs = $this->get_component_tabs();

		include( 'html-component-admin.php' );
	}

	/**
	 * Load component meta box in 'woocommerce_composite_component_admin_html'.
	 *
	 * @param  int    $id
	 * @param  array  $scenario_data
	 * @param  array  $composite_data
	 * @param  int    $post_id
	 * @param  string $toggle
	 * @return void
	 */
	public function scenario_admin_html( $id, $scenario_data, $composite_data, $post_id, $toggle = 'closed' ) {

		include( 'html-scenario-admin.php' );
	}

	/**
	 * Delete component options query cache on product save.
	 *
	 * @param  int   $post_id
	 * @return void
	 */
	public function delete_cp_query_transients( $post_id ) {

		// Invalidate query cache
		if ( class_exists( 'WC_Cache_Helper' ) ) {
			WC_Cache_Helper::get_transient_version( 'wccp_q', true );
		}

		if ( ! wp_using_ext_object_cache() ) {

			global $wpdb;

			// Delete all query transients
			$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_wccp_q_%') OR `option_name` LIKE ('_transient_timeout_wccp_q_%')" );
		}
	}

	/**
	 * Add scenario title and description options.
	 *
	 * @param  int    $id
	 * @param  array  $scenario_data
	 * @param  array  $composite_data
	 * @param  int    $product_id
	 * @return void
	 */
	public function scenario_actions( $id, $scenario_data, $composite_data, $product_id ) {

		$defines_compat_group = isset( $scenario_data[ 'scenario_actions' ][ 'compat_group' ][ 'is_active' ] ) ? $scenario_data[ 'scenario_actions' ][ 'compat_group' ][ 'is_active' ] : 'yes';

		?>
		<div class="scenario_action_compat_group" >
			<div class="form-field">
				<label for="scenario_action_compat_group_<?php echo $id; ?>">
					<?php echo __( 'Add dependency', 'woocommerce-composite-products' ); ?>
				</label>
				<input type="checkbox" class="checkbox"<?php echo ( $defines_compat_group === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_scenario_data[<?php echo $id; ?>][scenario_actions][compat_group][is_active]" <?php echo ( $defines_compat_group === 'yes' ? ' value="1"' : '' ); ?> />
				<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( 'Creates a group of dependent selections from the products/variations included in this Scenario. Any selections that do not belong in this group will be disabled unless they are valid in another Scenario.', 'woocommerce-composite-products' ) ); ?>
			</div>
		</div>
		<?php

	}

	/**
	 * Add scenario title and description options.
	 *
	 * @param  int    $id
	 * @param  array  $scenario_data
	 * @param  array  $composite_data
	 * @param  int    $product_id
	 * @return void
	 */
	public function scenario_info( $id, $scenario_data, $composite_data, $product_id ) {

		$title       = isset( $scenario_data[ 'title' ] ) ? $scenario_data[ 'title' ] : '';
		$position    = isset( $scenario_data[ 'position' ] ) ? $scenario_data[ 'position' ] : $id;
		$description = isset( $scenario_data[ 'description' ] ) ? $scenario_data[ 'description' ] : '';

		?>
		<div class="scenario_title">
			<div class="form-field">
				<label>
					<?php echo __( 'Scenario Name', 'woocommerce-composite-products' ); ?>
				</label>
				<input type="text" class="scenario_title component_text_input" name="bto_scenario_data[<?php echo $id; ?>][title]" value="<?php echo $title; ?>"/>
				<input type="hidden" name="bto_scenario_data[<?php echo $id; ?>][position]" class="scenario_position" value="<?php echo $position; ?>"/>
			</div>
		</div>
		<div class="scenario_description">
			<div class="form-field">
				<label>
					<?php echo __( 'Scenario Description', 'woocommerce-composite-products' ); ?>
				</label>
				<textarea class="scenario_description" name="bto_scenario_data[<?php echo $id; ?>][description]" id="scenario_description_<?php echo $id; ?>" placeholder="" rows="2" cols="20"><?php echo esc_textarea( $description ); ?></textarea>
			</div>
		</div>
		<?php
	}

	/**
	 * Add scenario config options.
	 *
	 * @param  int    $id
	 * @param  array  $scenario_data
	 * @param  array  $composite_data
	 * @param  int    $product_id
	 * @return void
	 */
	public function scenario_config( $id, $scenario_data, $composite_data, $product_id ) {

		?><div class="scenario_config_group"><?php

			foreach ( $composite_data as $component_id => $component_data ) {

				$modifier = '';

				if ( isset( $scenario_data[ 'modifier' ][ $component_id ] ) ) {

					$modifier = $scenario_data[ 'modifier' ][ $component_id ];

				} else {

					$exclude = isset( $scenario_data[ 'exclude' ][ $component_id ] ) ? $scenario_data[ 'exclude' ][ $component_id ] : 'no';

					if ( $exclude === 'no' ) {
						$modifier = 'in';
					} elseif ( $exclude === 'masked' ) {
						$modifier = 'masked';
					} else {
						$modifier = 'not-in';
					}
				}

				/**
				 * Filter the component title.
				 *
				 * @param  string  $title
				 * @param  string  $component_id
				 * @param  string  $product_id
				 */
				$component_title = apply_filters( 'woocommerce_composite_component_title', $component_data[ 'title' ], $component_id, $product_id );

				?><div class="bto_scenario_selector">
					<div class="form-field">
						<label><?php
							echo  $component_title;
						?></label>
						<div class="bto_scenario_modifier_wrapper bto_scenario_exclude_wrapper">
							<select class="bto_scenario_modifier bto_scenario_exclude" name="bto_scenario_data[<?php echo $id; ?>][modifier][<?php echo $component_id; ?>]">
								<option <?php selected( $modifier, 'in', true ); ?> value="in"><?php echo __( 'selection is', 'woocommerce-composite-products' ); ?></option>
								<option <?php selected( $modifier, 'not-in', true ); ?> value="not-in"><?php echo __( 'selection is not', 'woocommerce-composite-products' ); ?></option>
								<option <?php selected( $modifier, 'masked', true ); ?> value="masked"><?php echo __( 'selection is masked', 'woocommerce-composite-products' ); ?></option>
							</select>
						</div>
						<div class="bto_scenario_selector_inner" <?php echo $modifier === 'masked' ? 'style="display:none"' : ''; ?>><?php

							$component_options          = WC_CP()->api->get_component_options( $component_data );
							$component_options_data     = array();
							$component_options_count    = count( $component_options );
							$component_variations_count = 0;

							if ( count( $component_options ) < 30 ) {

								foreach ( $component_options as $component_option_id ) {

									$title = WC_CP_Helpers::get_product_title( $component_option_id );

									if ( ! $title ) {
										continue;
									}

									$component_options_data[ $component_option_id ] = array( 'title' => $title );

									// Get product type.
									$terms        = get_the_terms( $component_option_id, 'product_type' );
									$product_type = ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';

									$component_options_data[ $component_option_id ][ 'product_type' ] = $product_type;

									$variations_count            = sizeof( WC_CP_Helpers::get_product_variations( $component_option_id ) );
									$component_variations_count += $variations_count;

									if ( $component_variations_count >= 500 ) {
										break;
									}
								}
							}

							if ( $component_options_count < 30 && $component_variations_count < 500 ) {

								$scenario_options    = array();
								$scenario_selections = array();

								if ( $component_data[ 'optional' ] === 'yes' ) {

									if ( WC_CP_Scenarios::scenario_contains_product( $scenario_data, $component_id, -1 ) ) {
										$scenario_selections[] = -1;
									}

									$scenario_options[ -1 ] = __( 'None', 'woocommerce-composite-products' );
								}

								if ( WC_CP_Scenarios::scenario_contains_product( $scenario_data, $component_id, 0 ) ) {
									$scenario_selections[] = 0;
								}

								$scenario_options[ 0 ] = __( 'All Products and Variations', 'woocommerce-composite-products' );

								foreach ( $component_options_data as $option_id => $option_data ) {

									$title        = $option_data[ 'title' ];
									$product_type = $option_data[ 'product_type' ];

									if ( $product_type === 'variable' ) {
										$product_title          = $title . ' ' . __( '&mdash; All Variations', 'woocommerce-composite-products' );
										$variation_descriptions = WC_CP_Helpers::get_product_variation_descriptions( $option_id );
									} else {
										$product_title = $title;
									}

									if ( WC_CP_Scenarios::scenario_contains_product( $scenario_data, $component_id, $option_id ) ) {

										$scenario_selections[] = $option_id;
									}

									$scenario_options[ $option_id ] = $product_title;

									if ( $product_type === 'variable' ) {

										if ( ! empty( $variation_descriptions ) ) {

											foreach ( $variation_descriptions as $variation_id => $description ) {

												if ( WC_CP_Scenarios::scenario_contains_product( $scenario_data, $component_id, $variation_id ) ) {
													$scenario_selections[] = $variation_id;
												}

												$scenario_options[ $variation_id ] = $description;
											}
										}
									}

								}

								$optional_tip = $component_data[ 'optional' ] === 'yes' ? sprintf( __( '<br/><strong>Pro Tip</strong>: Use the <strong>None</strong> option to control the <strong>Optional</strong> property of <strong>%s</strong> in this Scenario.', 'woocommerce-composite-products' ), $component_title ) : '';
								$select_tip   = sprintf( __( 'Select products and variations from <strong>%1$s</strong>.<br/><strong>Tip</strong>: Choose the <strong>All Products and Variations</strong> option to add all products and variations available under <strong>%1$s</strong> in this Scenario.%2$s', 'woocommerce-composite-products' ), $component_title, $optional_tip );

								?><select id="bto_scenario_ids_<?php echo $id; ?>_<?php echo $component_id; ?>" name="bto_scenario_data[<?php echo $id; ?>][component_data][<?php echo $component_id; ?>][]" style="width: 75%;" class="<?php echo WC_CP_Core_Compatibility::is_wc_version_gte_2_3() ? 'wc-enhanced-select' : 'chosen_select'; ?> bto_scenario_ids" multiple="multiple" data-placeholder="<?php echo __( 'Select products &amp; variations&hellip;', 'woocommerce-composite-products' ); ?>"><?php

									foreach ( $scenario_options as $scenario_option_id => $scenario_option_description ) {
										$option_selected = in_array( $scenario_option_id, $scenario_selections ) ? 'selected="selected"' : '';
										echo '<option ' . $option_selected . 'value="' . $scenario_option_id . '">' . $scenario_option_description . '</option>';
									}

								?></select>
								<span class="bto_scenario_select tips" data-tip="<?php echo $select_tip; ?>"></span><?php

							} else {

								$selections_in_scenario = array();

								if ( ! empty( $scenario_data[ 'component_data' ] ) ) {

									foreach ( $scenario_data[ 'component_data' ][ $component_id ] as $product_id_in_scenario ) {

										if ( $product_id_in_scenario == -1 ) {
											if ( $component_data[ 'optional' ] === 'yes' ) {
												$selections_in_scenario[ $product_id_in_scenario ] = __( 'None', 'woocommerce-composite-products' );
											}
										} elseif ( $product_id_in_scenario == 0 ) {
											$selections_in_scenario[ $product_id_in_scenario ] = __( 'All Products and Variations', 'woocommerce-composite-products' );
										} else {

											$product_in_scenario = wc_get_product( $product_id_in_scenario );

											if ( ! $product_in_scenario ) {
												continue;
											}

											if ( ! in_array( $product_in_scenario->id, $component_options ) ) {
												continue;
											}

											if ( $product_in_scenario->product_type === 'variation' ) {
												$selections_in_scenario[ $product_id_in_scenario ] = WC_CP_Helpers::get_product_variation_title( $product_in_scenario );
											} elseif ( $product_in_scenario->product_type === 'variable' ) {
												$selections_in_scenario[ $product_id_in_scenario ] = WC_CP_Helpers::get_product_title( $product_in_scenario ) . ' ' . __( '&mdash; All Variations', 'woocommerce-composite-products' );
											} else {
												$selections_in_scenario[ $product_id_in_scenario ] = WC_CP_Helpers::get_product_title( $product_in_scenario );
											}
										}
									}
								}

								$optional_tip = $component_data[ 'optional' ] === 'yes' ? sprintf( __( '<br/><strong>Pro Tip</strong>: The <strong>None</strong> option controls the <strong>Optional</strong> property of <strong>%s</strong> in this Scenario.', 'woocommerce-composite-products' ), $component_title ) : '';
								$search_tip   = sprintf( __( 'Search for products and variations from <strong>%1$s</strong>.<br/><strong>Tip</strong>: Choose the <strong>All Products and Variations</strong> option to add all products and variations available under <strong>%1$s</strong> in this Scenario.%2$s', 'woocommerce-composite-products' ), $component_title, $optional_tip );

								if ( WC_CP_Core_Compatibility::is_wc_version_gte_2_3() ) {

									?><input type="hidden" id="bto_scenario_ids_<?php echo $id; ?>_<?php echo $component_id; ?>" name="bto_scenario_data[<?php echo $id; ?>][component_data][<?php echo $component_id; ?>]" class="wc-component-options-search" style="width: 75%;" data-component_optional="<?php echo $component_data[ 'optional' ]; ?>" data-component_id="<?php echo $component_id; ?>" data-placeholder="<?php _e( 'Search for products &amp; variations&hellip;', 'woocommerce-composite-products' ); ?>" data-action="woocommerce_json_search_component_options_in_scenario" data-multiple="true" data-selected="<?php

										echo esc_attr( json_encode( $selections_in_scenario ) );

									?>" value="<?php echo implode( ',', array_keys( $selections_in_scenario ) ); ?>" />
									<span class="bto_scenario_search tips" data-tip="<?php echo $search_tip; ?>"></span><?php

								} else {

									?><select id="bto_scenario_ids_<?php echo $id; ?>_<?php echo $component_id; ?>" name="bto_scenario_data[<?php echo $id; ?>][component_data][<?php echo $component_id; ?>][]" class="ajax_chosen_select_component_options" multiple="multiple" data-component_optional="<?php echo $component_data[ 'optional' ]; ?>" data-action="woocommerce_json_search_component_options_in_scenario" data-component_id="<?php echo $component_id; ?>" data-placeholder="<?php echo  __( 'Search for products &amp; variations&hellip;', 'woocommerce-composite-products' ); ?>"><?php

										if ( ! empty( $selections_in_scenario ) ) {

											foreach ( $selections_in_scenario as $selection_id_in_scenario => $selection_in_scenario ) {
												echo '<option value="' . $selection_id_in_scenario . '" selected="selected">' . $selection_in_scenario . '</option>';
											}
										}

									?></select>
									<span class="bto_scenario_search tips" data-tip="<?php echo $search_tip; ?>"></span><?php
								}
							}

						?></div>
					</div>
				</div><?php
			}

		?></div><?php
	}

	/**
	 * Add component layout hide title option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public function component_layout_hide_product_title( $id, $data, $product_id ) {

		$hide_product_title = isset( $data[ 'hide_product_title' ] ) ? $data[ 'hide_product_title' ] : '';

		?>
		<div class="component_hide_selection_title group_hide_product_title">
			<div class="form-field">
				<label for="group_hide_product_title_<?php echo $id; ?>">
					<?php echo __( 'Hide Selected Component Option Title', 'woocommerce-composite-products' ); ?>
				</label>
				<input type="checkbox" class="checkbox"<?php echo ( $hide_product_title === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][hide_product_title]" <?php echo ( $hide_product_title === 'yes' ? 'value="1"' : '' ); ?>/>
				<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( 'Check this option to hide the selected Component Option title.', 'woocommerce-composite-products' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component layout hide description option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public function component_layout_hide_product_description( $id, $data, $product_id ) {

		$hide_product_description = isset( $data[ 'hide_product_description' ] ) ? $data[ 'hide_product_description' ] : '';

		?>
		<div class="component_hide_selection_description group_hide_product_description" >
			<div class="form-field">
				<label for="group_hide_product_description_<?php echo $id; ?>">
					<?php echo __( 'Hide Selected Component Option Description', 'woocommerce-composite-products' ); ?>
				</label>
				<input type="checkbox" class="checkbox"<?php echo ( $hide_product_description === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][hide_product_description]" <?php echo ( $hide_product_description === 'yes' ? 'value="1"' : '' ); ?>/>
				<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( 'Check this option to hide the selected Component Option description.', 'woocommerce-composite-products' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component layout hide thumbnail option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public function component_layout_hide_product_thumbnail( $id, $data, $product_id ) {

		$hide_product_thumbnail = isset( $data[ 'hide_product_thumbnail' ] ) ? $data[ 'hide_product_thumbnail' ] : '';

		?>
		<div class="component_hide_selection_thumbnail group_hide_product_thumbnail" >
			<div class="form-field">
				<label for="group_hide_product_thumbnail_<?php echo $id; ?>">
					<?php echo __( 'Hide Selected Component Option Thumbnail', 'woocommerce-composite-products' ); ?>
				</label>
				<input type="checkbox" class="checkbox"<?php echo ( $hide_product_thumbnail === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][hide_product_thumbnail]" <?php echo ( $hide_product_thumbnail === 'yes' ? 'value="1"' : '' ); ?>/>
				<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( 'Check this option to hide the selected Component Option thumbnail.', 'woocommerce-composite-products' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component 'show orderby' option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public function component_sort_filter_show_orderby( $id, $data, $product_id ) {

		$show_orderby = isset( $data[ 'show_orderby' ] ) ? $data[ 'show_orderby' ] : 'no';

		?>
		<div class="component_show_orderby group_show_orderby" >
			<div class="form-field">
				<label for="group_show_orderby_<?php echo $id; ?>">
					<?php echo __( 'Show Component Options Sorting Dropdown', 'woocommerce-composite-products' ); ?>
				</label>
				<input type="checkbox" class="checkbox"<?php echo ( $show_orderby === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][show_orderby]" <?php echo ( $show_orderby === 'yes' ? 'value="1"' : '' ); ?>/>
				<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( 'Check this option to show a <strong>Sort options by</strong> dropdown. Use this setting if you have added a large number of Component Options. Recommended in combination with the <strong>Thumbnails</strong> Options Style.', 'woocommerce-composite-products' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component 'show filters' option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public function component_sort_filter_show_filters( $id, $data, $product_id ) {

		$show_filters         = isset( $data[ 'show_filters' ] ) ? $data[ 'show_filters' ] : 'no';
		$selected_taxonomies  = isset( $data[ 'attribute_filters' ] ) ? $data[ 'attribute_filters' ] : array();
		$attribute_taxonomies = wc_get_attribute_taxonomies();

		?>
		<div class="component_show_filters group_show_filters" >
			<div class="form-field">
				<label for="group_show_filters_<?php echo $id; ?>">
					<?php echo __( 'Show Layered Component Option Filters', 'woocommerce-composite-products' ); ?>
				</label>
				<input type="checkbox" class="checkbox"<?php echo ( $show_filters === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][show_filters]" <?php echo ( $show_filters === 'yes' ? 'value="1"' : '' ); ?>/>
				<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( 'Check this option to configure and display layered attribute filters to narrow down Component Options. Use this setting if you have added a large number of Component Options. Recommended in combination with the <strong>Thumbnails</strong> Options Style.', 'woocommerce-composite-products' ) ); ?>
			</div>
		</div><?php

		if ( $attribute_taxonomies ) {

			$attribute_array = array();

			foreach ( $attribute_taxonomies as $tax ) {

				if ( taxonomy_exists( wc_attribute_taxonomy_name( $tax->attribute_name ) ) )
					$attribute_array[ $tax->attribute_id ] = $tax->attribute_label;
			}

			?><div class="component_filters group_filters" >
				<div class="bto_attributes_selector bto_multiselect">
					<div class="form-field">
						<label><?php echo __( 'Active Attribute Filters', 'woocommerce-composite-products' ); ?>:</label>
						<select id="bto_attribute_ids_<?php echo $id; ?>" name="bto_data[<?php echo $id; ?>][attribute_filters][]" style="width: 75%" class="multiselect <?php echo WC_CP_Core_Compatibility::is_wc_version_gte_2_3() ? 'wc-enhanced-select' : 'chosen_select'; ?>" multiple="multiple" data-placeholder="<?php echo  __( 'Select product attributes&hellip;', 'woocommerce-composite-products' ); ?>"><?php

							foreach ( $attribute_array as $attribute_taxonomy_id => $attribute_taxonomy_label )
								echo '<option value="' . $attribute_taxonomy_id . '" ' . selected( in_array( $attribute_taxonomy_id, $selected_taxonomies ), true, false ).'>' . $attribute_taxonomy_label . '</option>';

						?></select>
					</div>
				</div><?php

				/**
				 * Action 'woocommerce_composite_component_admin_config_filter_options':
				 * Add your own custom filter config options here.
				 *
				 * @param  string  $component_id
				 * @param  array   $component_data
				 * @param  string  $composite_id
				 */
				do_action( 'woocommerce_composite_component_admin_config_filter_options', $id, $data, $product_id );

			?></div><?php
		}
	}

	/**
	 * Add component config title option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public function component_config_title( $id, $data, $product_id ) {

		$title    = isset( $data[ 'title' ] ) ? $data[ 'title' ] : '';
		$position = isset( $data[ 'position' ] ) ? $data[ 'position' ] : $id;

		?>
		<div class="component_title group_title">
			<div class="form-field">
				<label>
					<?php echo __( 'Component Name', 'woocommerce-composite-products' ); ?>
				</label>
				<input type="text" class="group_title component_text_input" name="bto_data[<?php echo $id; ?>][title]" value="<?php echo $title; ?>"/>
				<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( 'Name or title of this Component.', 'woocommerce-composite-products' ) ); ?>
				<input type="hidden" name="bto_data[<?php echo $id; ?>][position]" class="group_position" value="<?php echo $position; ?>" />
			</div>
		</div>
		<?php
	}

	/**
	 * Add component config description option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public function component_config_description( $id, $data, $product_id ) {

		$description = isset( $data[ 'description' ] ) ? $data[ 'description' ] : '';

		?>
		<div class="component_description group_description">
			<div class="form-field">
				<label>
					<?php echo __( 'Component Description', 'woocommerce-composite-products' ); ?>
				</label>
				<textarea class="group_description" name="bto_data[<?php echo $id; ?>][description]" id="group_description_<?php echo $id; ?>" placeholder="" rows="2" cols="20"><?php echo esc_textarea( $description ); ?></textarea>
				<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( 'Optional short description of this Component.', 'woocommerce-composite-products' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component config multi select products option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public function component_config_options( $id, $data, $product_id ) {

		$query_type          = isset( $data[ 'query_type' ] ) ? $data[ 'query_type' ] : 'product_ids';
		$product_categories  = ( array ) get_terms( 'product_cat', array( 'get' => 'all' ) );
		$selected_categories = isset( $data[ 'assigned_category_ids' ] ) ? $data[ 'assigned_category_ids' ] : array();

		$select_by = array(
			'product_ids'  => __( 'Select products', 'woocommerce-composite-products' ),
			'category_ids' => __( 'Select categories', 'woocommerce-composite-products' )
		);

		/**
		 * Filter the default query types.
		 *
		 * @param  array  $select_by
		 */
		$select_by = apply_filters( 'woocommerce_composite_component_query_types', $select_by, $data, $product_id );

		?>
		<div class="component_query_type bto_query_type">
			<div class="form-field">
				<label>
					<?php echo __( 'Component Options', 'woocommerce-composite-products' ); ?>
				</label>
				<select class="bto_query_type" name="bto_data[<?php echo $id; ?>][query_type]"><?php

					foreach ( $select_by as $key => $description ) {
						?><option value="<?php echo $key; ?>" <?php selected( $query_type, $key, true ); ?>><?php echo $description; ?></option><?php
					}

				?></select>
				<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( 'Select the products you want to use as Component Options. You can add products individually, or select a category to add all associated products.', 'woocommerce-composite-products' ) ); ?>
			</div>
		</div>

		<div class="component_selector bto_selector bto_query_type_selector bto_multiselect bto_query_type_product_ids">
			<div class="form-field"><?php

				$product_id_options = array();

				if ( ! empty( $data[ 'assigned_ids' ] ) ) {

					$item_ids = $data[ 'assigned_ids' ];

					foreach ( $item_ids as $item_id ) {

						$product_title = WC_CP_Helpers::get_product_title( $item_id );

						if ( $product_title ) {

							$product_id_options[ $item_id ] = $product_title;
						}
					}

				}

				if ( WC_CP_Core_Compatibility::is_wc_version_gte_2_3() ) {


					?><input type="hidden" id="bto_ids_<?php echo $id; ?>" name="bto_data[<?php echo $id; ?>][assigned_ids]" class="wc-product-search" style="width: 75%;" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products" data-multiple="true" data-selected="<?php

						echo esc_attr( json_encode( $product_id_options ) );

					?>" value="<?php echo implode( ',', array_keys( $product_id_options ) ); ?>" /><?php

				} else {

					?><select id="bto_ids_<?php echo $id; ?>" name="bto_data[<?php echo $id; ?>][assigned_ids][]" class="ajax_chosen_select_products" multiple="multiple" data-placeholder="<?php echo  __( 'Search for a product&hellip;', 'woocommerce' ); ?>"><?php

						if ( ! empty( $product_id_options ) ) {

							foreach( $product_id_options as $product_id => $product_name ) {
								echo '<option value="' . $product_id . '" selected="selected">' . $product_name . '</option>';
							}
						}

					?></select><?php

				}
			?></div>
		</div>

		<div class="component_category_selector bto_category_selector bto_query_type_selector bto_multiselect bto_query_type_category_ids">
			<div class="form-field">

				<select id="bto_category_ids_<?php echo $id; ?>" name="bto_data[<?php echo $id; ?>][assigned_category_ids][]" style="width: 75%" class="multiselect <?php echo WC_CP_Core_Compatibility::is_wc_version_gte_2_3() ? 'wc-enhanced-select' : 'chosen_select'; ?>" multiple="multiple" data-placeholder="<?php echo  __( 'Select product categories&hellip;', 'woocommerce-composite-products' ); ?>"><?php

					foreach ( $product_categories as $product_category )
						echo '<option value="' . $product_category->term_id . '" ' . selected( in_array( $product_category->term_id, $selected_categories ), true, false ).'>' . $product_category->name . '</option>';

				?></select>
			</div>
		</div><?php

		/**
		 * Action 'woocommerce_composite_component_admin_config_query_options'.
		 * Use this hook to display additional query type options associated with a custom query type added via {@see woocommerce_composite_component_query_types}.
		 *
		 * @param  $id          int
		 * @param  $data        array
		 * @param  $product_id  string
		 */
		do_action( 'woocommerce_composite_component_admin_config_query_options', $id, $data, $product_id );
	}

	/**
	 * Add component options style option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public function component_config_options_style( $id, $data, $product_id ) {

		?><div class="component_options_style group_options_style">
			<div class="form-field">
				<label>
					<?php _e( 'Options Style', 'woocommerce-composite-products' ); ?>
				</label>
				<select name="bto_data[<?php echo $id; ?>][selection_mode]"><?php

					if ( ! empty( $data[ 'selection_mode' ] ) ) {
						$mode = $data[ 'selection_mode' ];
					} else {

						$mode = get_post_meta( $product_id, '_bto_selection_mode', true );

						if ( empty( $mode ) ) {
							$mode = 'dropdowns';
						}
					}

					foreach ( WC_CP()->api->get_options_styles() as $style ) {
						echo '<option ' . selected( $mode, $style[ 'id' ], false ) . ' value="' . $style[ 'id' ] . '">' . $style[ 'description' ] . '</option>';
					}

				?></select>
				<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( '<strong>Thumbnails</strong> &ndash; Component Options are presented as thumbnails, paginated and arranged in columns similar to the main shop loop.</br></br><strong>Dropdown</strong> &ndash; Component Options are listed in a dropdown menu without any pagination. Ideal for presenting a small number of Component Options, while keeping the layout as compact as possible.</br></br><strong>Radio Buttons</strong> &ndash; Component Options are listed as radio buttons, without any pagination. Suitable for providing an overview of all Component Options. Not recommended for Components with many options.', 'woocommerce-composite-products' ) ); ?>
			</div>
		</div><?php
	}

	/**
	 * Add component config default selection option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public function component_config_default_option( $id, $data, $product_id ) {

		?><div class="component_default_selector default_selector">
			<div class="form-field">
				<label>
					<?php echo __( 'Default Component Option', 'woocommerce-composite-products' ); ?>
				</label><?php

				// Run query to get component option ids.
				$item_ids = WC_CP()->api->get_component_options( $data );

				if ( ! empty( $item_ids ) ) {

					// If < 30 show a dropdown, otherwise show an ajax chosen field.
					if ( count( $item_ids ) < 30 ) {

						?><select id="group_default_<?php echo $id; ?>" name="bto_data[<?php echo $id; ?>][default_id]">
							<option value=""><?php echo __( 'No default option&hellip;', 'woocommerce-composite-products' ); ?></option><?php

							$selected_default = $data[ 'default_id' ];

							foreach ( $item_ids as $item_id ) {

								$product_title = WC_CP_Helpers::get_product_title( $item_id );

								if ( $product_title ) {
									echo '<option value="' . $item_id . '" ' . selected( $selected_default, $item_id, false ) . '>'. $product_title . '</option>';
								}
							}

						?></select><?php

					} else {

						$selected_default = $data[ 'default_id' ];
						$product_title    = '';

						if ( $selected_default ) {

							$product_title = WC_CP_Helpers::get_product_title( $selected_default );
						}

						if ( WC_CP_Core_Compatibility::is_wc_version_gte_2_3() ) {

							?><input type="hidden" id="group_default_<?php echo $id; ?>" name="bto_data[<?php echo $id; ?>][default_id]" class="wc-component-options-search" style="width: 75%;" data-component_id="<?php echo isset( $data[ 'component_id' ] ) ? $data[ 'component_id' ] : ''; ?>" data-placeholder="<?php _e( 'No default selected. Search for a product&hellip;', 'woocommerce-composite-products' ); ?>" data-allow_clear="true" data-action="woocommerce_json_search_default_component_option" data-multiple="false" data-selected="<?php

								echo esc_attr( $product_title ? $product_title : __( 'No default selected. Search for a product&hellip;', 'woocommerce-composite-products' ) );

							?>" value="<?php echo $product_title ? $selected_default : ''; ?>" /><?php

						} else {

							?><select id="group_default_<?php echo $id; ?>" name="bto_data[<?php echo $id; ?>][default_id]" class="ajax_chosen_select_component_options" data-action="woocommerce_json_search_default_component_option" data-component_id="<?php echo isset( $data[ 'component_id' ] ) ? $data[ 'component_id' ] : ''; ?>" data-placeholder="<?php echo  __( 'No default selected. Search for a product&hellip;', 'woocommerce-composite-products' ); ?>">
								<option value=""><?php echo __( 'No default option&hellip;', 'woocommerce-composite-products' ); ?></option><?php

								$selected_default = $data[ 'default_id' ];

								if ( $selected_default ) {

									$product_title = WC_CP_Helpers::get_product_title( $selected_default );

									if ( $product_title ) {
										echo '<option value="' . $selected_default . '" selected="selected">' . $product_title . '</option>';
									}
								}

							?></select><?php
						}
					}

					echo WC_CP_Core_Compatibility::wc_help_tip( __( 'Select a product that you want to use as the default (pre-selected) Component Option. To use this option, you must first add some products in the <strong>Component Options</strong> field and then save your configuration.', 'woocommerce-composite-products' ) );

				} else {
					?><div class="prompt"><em><?php _e( 'To choose a default product, you must first add some products in the Component Options field and then save your configuration&hellip;', 'woocommerce-composite-products' ); ?></em></div><?php
				}

			?></div>
		</div>
		<?php
	}

	/**
	 * Add component config min quantity option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public function component_config_quantity_min( $id, $data, $product_id ) {

		$quantity_min = isset( $data[ 'quantity_min' ] ) ? $data[ 'quantity_min' ] : 1;

		?>
		<div class="group_quantity_min">
			<div class="form-field">
				<label for="group_quantity_min_<?php echo $id; ?>">
					<?php echo __( 'Min Quantity', 'woocommerce-composite-products' ); ?>
				</label>
				<input type="number" class="group_quantity_min" name="bto_data[<?php echo $id; ?>][quantity_min]" id="group_quantity_min_<?php echo $id; ?>" value="<?php echo $quantity_min; ?>" placeholder="" step="1" min="0" />
				<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( 'Set a minimum quantity for the selected Component Option.', 'woocommerce-composite-products' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component config max quantity option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public function component_config_quantity_max( $id, $data, $product_id ) {

		$quantity_max = isset( $data[ 'quantity_max' ] ) ? $data[ 'quantity_max' ] : 1;

		?>
		<div class="group_quantity_max">
			<div class="form-field">
				<label for="group_quantity_max_<?php echo $id; ?>">
					<?php echo __( 'Max Quantity', 'woocommerce-composite-products' ); ?>
				</label>
				<input type="number" class="group_quantity_max" name="bto_data[<?php echo $id; ?>][quantity_max]" id="group_quantity_max_<?php echo $id; ?>" value="<?php echo $quantity_max; ?>" placeholder="" step="1" min="0" />
				<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( 'Set a maximum quantity for the selected Component Option. Leave the field empty to allow an unlimited maximum quantity.', 'woocommerce-composite-products' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component config discount option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public function component_config_discount( $id, $data, $product_id ) {

		$discount = isset( $data[ 'discount' ] ) ? $data[ 'discount' ] : '';

		?>
		<div class="group_discount">
			<div class="form-field">
				<label for="group_discount_<?php echo $id; ?>">
					<?php echo __( 'Discount %', 'woocommerce-composite-products' ); ?>
				</label>
				<input type="text" class="group_discount input-text wc_input_decimal" name="bto_data[<?php echo $id; ?>][discount]" id="group_discount_<?php echo $id; ?>" value="<?php echo $discount; ?>" placeholder="" />
				<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( 'Component-level discount applied to any selected Component Option when the <strong>Per-Item Pricing</strong> field is checked. Note that component-level discounts are calculated on top of regular product prices.', 'woocommerce-composite-products' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component config optional option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public function component_config_optional( $id, $data, $product_id ) {

		$optional = isset( $data[ 'optional' ] ) ? $data[ 'optional' ] : '';

		?>
		<div class="group_optional" >
			<div class="form-field">
				<label for="group_optional_<?php echo $id; ?>">
					<?php echo __( 'Optional', 'woocommerce-composite-products' ); ?>
				</label>
				<input type="checkbox" class="checkbox"<?php echo ( $optional === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][optional]" <?php echo ( $optional === 'yes' ? ' value="1"' : '' ); ?> />
				<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( 'Checking this option will allow customers to proceed without making any selection for this Component at all.', 'woocommerce-composite-products' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Admin writepanel scripts.
	 *
	 * @return void
	 */
	public function composite_admin_scripts() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( WC_CP_Core_Compatibility::is_wc_version_gte_2_2() ) {
			$writepanel_dependency = 'wc-admin-meta-boxes';
		} else {
			$writepanel_dependency = 'woocommerce_admin_meta_boxes';
		}

		wp_register_script( 'wc_composite_writepanel', WC_CP()->plugin_url() . '/assets/js/wc-composite-write-panels' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', $writepanel_dependency ), WC_CP()->version );
		wp_register_style( 'wc_composite_writepanel_css', WC_CP()->plugin_url() . '/assets/css/wc-composite-write-panels.css', array( 'woocommerce_admin_styles' ), WC_CP()->version );
		wp_register_style( 'wc_composite_edit_order_css', WC_CP()->plugin_url() . '/assets/css/wc-composite-edit-order.css', array( 'woocommerce_admin_styles' ), WC_CP()->version );

		// Get admin screen id.
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		// WooCommerce admin pages.
		if ( in_array( $screen_id, array( 'product' ) ) ) {
			wp_enqueue_script( 'wc_composite_writepanel' );

			$params = array(
				'save_composite_nonce'      => wp_create_nonce( 'wc_bto_save_composite' ),
				'add_component_nonce'       => wp_create_nonce( 'wc_bto_add_component' ),
				'add_scenario_nonce'        => wp_create_nonce( 'wc_bto_add_scenario' ),
				'i18n_no_default'           => __( 'No default option&hellip;', 'woocommerce-composite-products' ),
				'i18n_all'                  => __( 'All Products and Variations', 'woocommerce-composite-products' ),
				'i18n_none'                 => __( 'None', 'woocommerce-composite-products' ),
				'is_wc_version_gte_2_3'     => WC_CP_Core_Compatibility::is_wc_version_gte_2_3() ? 'yes' : 'no',
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

			wp_localize_script( 'wc_composite_writepanel', 'wc_composite_admin_params', $params );
		}

		if ( in_array( $screen_id, array( 'edit-product', 'product' ) ) )
			wp_enqueue_style( 'wc_composite_writepanel_css' );

		if ( in_array( $screen_id, array( 'shop_order', 'edit-shop_order' ) ) )
			wp_enqueue_style( 'wc_composite_edit_order_css' );
	}

	/**
	 * Adds the Composite Product write panel tabs.
	 *
	 * @return string
	 */
	public function composite_write_panel_tabs() {

		echo '<li class="bto_product_tab show_if_composite composite_product_options"><a href="#bto_product_data">'.__( 'Components', 'woocommerce-composite-products' ).'</a></li>';
		echo '<li class="bto_product_tab show_if_composite composite_scenarios"><a href="#bto_scenario_data">'.__( 'Scenarios', 'woocommerce-composite-products' ).'</a></li>';
	}

	/**
	 * Adds the base and sale price option writepanel options.
	 *
	 * @return void
	 */
	public function composite_pricing_options() {

		global $thepostid;

		echo '<div class="options_group bto_base_pricing show_if_composite">';

		// Price.
		woocommerce_wp_text_input( array( 'id' => '_base_regular_price', 'class' => 'short', 'label' => __( 'Base Regular Price', 'woocommerce-composite-products' ) . ' (' . get_woocommerce_currency_symbol() . ')', 'data_type' => 'price', 'desc_tip' => true, 'description' => __( 'Base regular/sale price of the Composite, added on top of the regular/sale price of all selected Components.', 'woocommerce-composite-products' ) ) );

		// Sale Price.
		woocommerce_wp_text_input( array( 'id' => '_base_sale_price', 'class' => 'short', 'label' => __( 'Base Sale Price', 'woocommerce-composite-products' ) . ' (' . get_woocommerce_currency_symbol() . ')', 'data_type' => 'price', 'description' => '<a href="#" class="sale_schedule">' . __( 'Schedule', 'woocommerce' ) . '</a>' ) );

		// Special Price date range.
		$sale_price_dates_from = ( $date = get_post_meta( $thepostid, '_base_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
		$sale_price_dates_to   = ( $date = get_post_meta( $thepostid, '_base_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';

		echo '<p class="form-field sale_price_dates_fields base_sale_price_dates_fields">
			<label for="_base_sale_price_dates_from">' . __( 'Base Sale Price Dates', 'woocommerce-composite-products' ) . '</label>
			<input type="text" class="short sale_price_dates_from" name="_base_sale_price_dates_from" id="_base_sale_price_dates_from" value="' . esc_attr( $sale_price_dates_from ) . '" placeholder="' . _x( 'From&hellip;', 'placeholder', 'woocommerce' ) . ' YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
			<input type="text" class="short sale_price_dates_to" name="_base_sale_price_dates_to" id="_base_sale_price_dates_to" value="' . esc_attr( $sale_price_dates_to ) . '" placeholder="' . _x( 'To&hellip;', 'placeholder', 'woocommerce' ) . '  YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
			<a href="#" class="cancel_sale_schedule" style="display: inline-block; margin: 21px 0 0 10px;">' . __( 'Cancel', 'woocommerce' ) . '</a>' . WC_CP_Core_Compatibility::wc_help_tip( __( 'The sale will end at the beginning of the set date.', 'woocommerce' ) ) . '
		</p>';

		// Hide Shop Price.
		woocommerce_wp_checkbox( array( 'id' => '_bto_hide_shop_price', 'label' => __( 'Hide Price', 'woocommerce-composite-products' ), 'desc_tip' => true, 'description' => __( 'Disable all internal price calculations and hide the Composite price displayed in the shop catalog and product summary.', 'woocommerce-composite-products' ) ) );

		echo '</div>';
	}

	/**
	 * Add Composited Products stock note.
	 *
	 * @return void
	 */
	public function composite_stock_info() {

		global $post;

		?><span class="composite_stock_msg show_if_composite">
				<?php echo WC_CP_Core_Compatibility::wc_help_tip( __( 'By default, the sale of a product within a composite has the same effect on its stock as an individual sale. There are no separate inventory settings for composited items. However, managing stock at composite level can be very useful for allocating composite stock quota, or for keeping track of composited item sales.', 'woocommerce-composite-products' ) ); ?>
		</span><?php
	}

	/**
	 * Components and Scenarios write panels.
	 *
	 * @return void
	 */
	public function composite_write_panel() {

		global $post, $wpdb;

		$bto_data      = get_post_meta( $post->ID, '_bto_data', true );
		$bto_scenarios = get_post_meta( $post->ID, '_bto_scenario_data', true );

		?>
		<div id="bto_product_data" class="bto_panel panel woocommerce_options_panel wc-metaboxes-wrapper"><?php

			/**
			 * Action 'woocommerce_composite_admin_html'.
			 *
			 * @param   array   $bto_data
			 * @param   string  $post_id
			 *
			 * @hooked {@see composite_layout_options}    - 10
			 * @hooked {@see composite_component_options} - 15
			 */
			do_action( 'woocommerce_composite_admin_html', $bto_data, $post->ID );

		?></div>
		<div id="bto_scenario_data" class="bto_panel panel woocommerce_options_panel wc-metaboxes-wrapper">
			<div class="options_group">

				<div id="bto_scenarios_inner"><?php

					if ( $bto_data ) {

						?><div id="bto-scenarios-message" class="<?php echo WC_CP_Core_Compatibility::is_wc_version_gte_2_4() ? 'inline notice woocommerce-message' : 'squeezer'; ?>">
							<span><?php
								$tip = '<a href="#" class="tips" data-tip="' . __( 'Use Scenarios to create dependencies between Component Options. Developers may use Scenarios and the Scenario Actions API to define conditions for triggering custom actions at every step of the configuration, depending on the selected products/variations.', 'woocommerce-composite-products' ) . '">' . __( 'help', 'woocommerce-composite-products' ) . '</a>';
								echo sprintf( __( 'Need %s to set up <strong>Scenarios</strong> ?', 'woocommerce-composite-products' ), $tip );
							?></span>
							<span><a class="button-primary" href="<?php echo 'http://docs.woothemes.com/document/composite-products'; ?>" target="_blank"><?php _e( 'Learn more', 'woocommerce' ); ?></a></span>
						</div>
						<p class="toolbar">
							<a href="#" class="close_all"><?php _e( 'Close all', 'woocommerce' ); ?></a>
							<a href="#" class="expand_all"><?php _e( 'Expand all', 'woocommerce' ); ?></a>
						</p>

						<div class="bto_scenarios wc-metaboxes"><?php

							if ( $bto_scenarios ) {

								$i = 0;

								foreach ( $bto_scenarios as $scenario_id => $scenario_data ) {

									$scenario_data[ 'scenario_id' ] = $scenario_id;

									/**
									 * Action 'woocommerce_composite_scenario_admin_html'.
									 *
									 * @param   int     $i
									 * @param   array   $scenario_data
									 * @param   array   $composite_data
									 * @param   string  $post_id
									 * @param   string  $state
									 *
									 * @hooked  {@see scenario_admin_html} - 10
									 */
									do_action( 'woocommerce_composite_scenario_admin_html', $i, $scenario_data, $bto_data, $post->ID, 'closed' );

									$i++;
								}
							}

						?></div>

						<p class="toolbar borderless">
							<button type="button" class="button button-primary add_bto_scenario"><?php _e( 'Add Scenario', 'woocommerce-composite-products' ); ?></button>
						</p><?php

					} else {

						?><div id="bto-scenarios-message" class="<?php echo WC_CP_Core_Compatibility::is_wc_version_gte_2_4() ? 'inline notice woocommerce-message' : 'squeezer'; ?>">
							<span><?php _e( 'Scenarios can be defined only after creating and saving some Components on the <strong>Components</strong> tab.', 'woocommerce-composite-products' ); ?></span>
							<span><a class="button-primary" href="<?php echo 'http://docs.woothemes.com/document/composite-products'; ?>" target="_blank"><?php _e( 'Learn more', 'woocommerce' ); ?></a></span>
						</div><?php
					}

				?></div>
			</div>
		</div><?php
	}

	/**
	 * Product options for post-1.6.2 product data section.
	 *
	 * @param  array $options
	 * @return array
	 */
	public function add_composite_type_options( $options ) {

		$options[ 'per_product_shipping_bto' ] = array(
			'id'            => '_per_product_shipping_bto',
			'wrapper_class' => 'show_if_composite',
			'label'         => __( 'Per-Item Shipping', 'woocommerce-composite-products' ),
			'description'   => __( 'If your Composite product consists of items that are assembled or packaged together, leave this box un-checked and define the shipping properties of the entire Composite below. If, however, the contents of the Composite are shipped individually, check this option to retain their original shipping weight and dimensions. <strong>Per-Item Shipping</strong> should also be checked if all composited items are virtual.', 'woocommerce-composite-products' ),
			'default'       => 'no'
		);

		$options[ 'per_product_pricing_bto' ] = array(
			'id'            => '_per_product_pricing_bto',
			'wrapper_class' => 'show_if_composite bto_per_item_pricing',
			'label'         => __( 'Per-Item Pricing', 'woocommerce-composite-products' ),
			'description'   => __( 'When <strong>Per-Item Pricing</strong> is checked, the Composite product will be priced according to the cost of its contents. To add a fixed amount to the Composite price when thr <strong>Per-Item Pricing</strong> option is checked, use the Base Price fields below.', 'woocommerce-composite-products' ),
			'default'       => 'no'
		);

		return $options;
	}

	/**
	 * Adds the 'composite product' type to the menu.
	 *
	 * @param  array 	$options
	 * @return array
	 */
	public function add_composite_type( $options ) {

		$options[ 'composite' ] = __( 'Composite product', 'woocommerce-composite-products' );

		return $options;
	}

	/**
	 * Process, verify and save composite product data.
	 *
	 * @param  int 	$post_id
	 * @return void
	 */
	public function process_composite_meta( $post_id ) {

		// Sold Individually options.

		if ( ! empty( $_POST[ '_bto_sold_individually' ] ) ) {

			$sold_individually = wc_clean( $_POST[ '_bto_sold_individually' ] );

			if ( $sold_individually === 'no' ) {
				update_post_meta( $post_id, '_sold_individually', 'no' );
				update_post_meta( $post_id, '_bto_sold_individually', 'no' );
			} elseif ( $sold_individually === 'product' || $sold_individually === 'configuration' ) {
				update_post_meta( $post_id, '_sold_individually', 'yes' );
				update_post_meta( $post_id, '_bto_sold_individually', $sold_individually );
			}

		} else {
			delete_post_meta( $post_id, '_bto_sold_individually' );
		}

		// Edit in cart option.

		if ( ! empty( $_POST[ '_bto_edit_in_cart' ] ) ) {
			update_post_meta( $post_id, '_bto_edit_in_cart', 'yes' );
		} else {
			update_post_meta( $post_id, '_bto_edit_in_cart', 'no' );
		}

		// Per-Item Pricing.

		if ( isset( $_POST[ '_per_product_pricing_bto' ] ) ) {

			update_post_meta( $post_id, '_per_product_pricing_bto', 'yes' );

			// Update price meta.
			if ( isset( $_POST[ '_base_regular_price'] ) ) {
				update_post_meta( $post_id, '_base_regular_price', ( $_POST[ '_base_regular_price' ] === '' ) ? '' : wc_format_decimal( $_POST[ '_base_regular_price' ] ) );
			}

			if ( isset( $_POST[ '_base_sale_price' ] ) ) {
				update_post_meta( $post_id, '_base_sale_price', ( $_POST[ '_base_sale_price' ] === '' ? '' : wc_format_decimal( $_POST[ '_base_sale_price' ] ) ) );
			}

			$date_from = isset( $_POST[ '_base_sale_price_dates_from' ] ) ? wc_clean( $_POST[ '_base_sale_price_dates_from' ] ) : '';
			$date_to   = isset( $_POST[ '_base_sale_price_dates_to' ] ) ? wc_clean( $_POST[ '_base_sale_price_dates_to' ] ) : '';

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

			// Update price if on sale.
			if ( '' !== $_POST[ '_base_sale_price' ] && '' == $date_to && '' == $date_from ) {
				update_post_meta( $post_id, '_base_price', wc_format_decimal( $_POST[ '_base_sale_price' ] ) );
			} else {
				update_post_meta( $post_id, '_base_price', ( $_POST[ '_base_regular_price' ] === '' ) ? '' : wc_format_decimal( $_POST[ '_base_regular_price' ] ) );
			}

			if ( '' !== $_POST[ '_base_sale_price' ] && $date_from && strtotime( $date_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
				update_post_meta( $post_id, '_base_price', wc_format_decimal( $_POST[ '_base_sale_price' ] ) );
			}

			if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
				update_post_meta( $post_id, '_base_price', ( $_POST[ '_base_regular_price' ] === '' ) ? '' : wc_format_decimal( $_POST[ '_base_regular_price' ] ) );
				update_post_meta( $post_id, '_base_sale_price_dates_from', '' );
				update_post_meta( $post_id, '_base_sale_price_dates_to', '' );
			}

			// Update override price meta.
			if ( ! empty( $_POST[ '_bto_hide_shop_price' ] ) ) {
				update_post_meta( $post_id, '_bto_hide_shop_price', 'yes' );
			} else {
				update_post_meta( $post_id, '_bto_hide_shop_price', 'no' );
			}

		} else {
			update_post_meta( $post_id, '_per_product_pricing_bto', 'no' );
		}

		// Per-Item Shipping.

		if ( isset( $_POST[ '_per_product_shipping_bto' ] ) ) {
			update_post_meta( $post_id, '_per_product_shipping_bto', 'yes' );
			update_post_meta( $post_id, '_virtual', 'yes' );
			update_post_meta( $post_id, '_weight', '' );
			update_post_meta( $post_id, '_length', '' );
			update_post_meta( $post_id, '_width', '' );
			update_post_meta( $post_id, '_height', '' );
		} else {
			update_post_meta( $post_id, '_per_product_shipping_bto', 'no' );
			update_post_meta( $post_id, '_virtual', 'no' );
			update_post_meta( $post_id, '_weight', stripslashes( $_POST[ '_weight' ] ) );
			update_post_meta( $post_id, '_length', stripslashes( $_POST[ '_length' ] ) );
			update_post_meta( $post_id, '_width', stripslashes( $_POST[ '_width' ] ) );
			update_post_meta( $post_id, '_height', stripslashes( $_POST[ '_height' ] ) );
		}

		$this->save_composite_config( $post_id, $_POST );

		// Delete no longer used meta.
		delete_post_meta( $post_id, '_min_composite_price' );
		delete_post_meta( $post_id, '_max_composite_price' );
	}

	/**
	 * Save components and scenarios.
	 *
	 * @param  int   $post_id
	 * @param  array $posted_composite_data
	 * @return boolean
	 */
	public function save_composite_config( $post_id, $posted_composite_data ) {

		global $wpdb;

		// Composite style.

		$composite_layout = 'single';

		if ( isset( $posted_composite_data[ 'bto_style' ] ) ) {
			$composite_layout = stripslashes( $posted_composite_data[ 'bto_style' ] );
		}

		update_post_meta( $post_id, '_bto_style', $composite_layout );

		// Process Composite Product Configuration.

		$zero_product_item_exists = false;
		$component_options_count  = 0;
		$bto_data                 = get_post_meta( $post_id, '_bto_data', true );

		if ( ! $bto_data ) {
			$bto_data = array();
		}

		if ( isset( $posted_composite_data[ 'bto_data' ] ) ) {

			/*--------------------------*/
			/*  Components.             */
			/*--------------------------*/

			$counter  = 0;
			$ordering = array();

			foreach ( $posted_composite_data[ 'bto_data' ] as $row_id => $post_data ) {

				$bto_ids     = isset( $post_data[ 'assigned_ids' ] ) ? $post_data[ 'assigned_ids' ] : '';
				$bto_cat_ids = isset( $post_data[ 'assigned_category_ids' ] ) ? $post_data[ 'assigned_category_ids' ] : '';

				$group_id    = isset ( $post_data[ 'group_id' ] ) ? stripslashes( $post_data[ 'group_id' ] ) : ( current_time( 'timestamp' ) + $counter );
				$counter++;

				$bto_data[ $group_id ] = array();

				// Save component id.
				$bto_data[ $group_id ][ 'component_id' ] = $group_id;

				// Save query type.
				if ( isset( $post_data[ 'query_type' ] ) && ! empty( $post_data[ 'query_type' ] ) ) {
					$bto_data[ $group_id ][ 'query_type' ] = stripslashes( $post_data[ 'query_type' ] );
				} else {
					$bto_data[ $group_id ][ 'query_type' ] = 'product_ids';
				}

				if ( ! empty( $bto_ids ) ) {

					if ( is_array( $bto_ids ) ) {
						$bto_ids = array_map( 'intval', $post_data[ 'assigned_ids' ] );
					} else {
						$bto_ids = array_filter( array_map( 'intval', explode( ',', $post_data[ 'assigned_ids' ] ) ) );
					}

					foreach ( $bto_ids as $key => $id ) {

						// Get product type.
						$terms        = get_the_terms( $id, 'product_type' );
						$product_type = ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';

						if ( $id && $id > 0 && in_array( $product_type, apply_filters( 'woocommerce_composite_products_supported_types', array( 'simple', 'variable', 'bundle' ) ) ) && $post_id != $id ) {

							// Check that product exists
							if ( ! get_post( $id ) ) {
								continue;
							}

							$error = apply_filters( 'woocommerce_composite_products_custom_type_save_error', false, $id );

							if ( $error ) {
								$this->add_notice( $error, 'error' );
								continue;
							}

							// Save assigned ids.
							$bto_data[ $group_id ][ 'assigned_ids' ][] = $id;
						}
					}

					if ( ! empty( $bto_data[ $group_id ][ 'assigned_ids' ] ) ) {
						$bto_data[ $group_id ][ 'assigned_ids' ] = array_unique( $bto_data[ $group_id ][ 'assigned_ids' ] );
					}

				}

				if ( ! empty( $bto_cat_ids ) ) {

					$bto_cat_ids = array_map( 'absint', $post_data[ 'assigned_category_ids' ] );

					$bto_data[ $group_id ][ 'assigned_category_ids' ] = array_values( $bto_cat_ids );
				}

				// True if no products were added.
				if ( ( $bto_data[ $group_id ][ 'query_type' ] === 'product_ids' && empty( $bto_data[ $group_id ][ 'assigned_ids' ] ) ) || ( $bto_data[ $group_id ][ 'query_type' ] === 'category_ids' && empty( $bto_data[ $group_id ][ 'assigned_category_ids' ] ) ) ) {

					unset( $bto_data[ $group_id ] );
					$zero_product_item_exists = true;
					continue;
				}

				// Run query to get component option ids.
				$component_options = WC_CP()->api->get_component_options( $bto_data[ $group_id ] );

				// Add up options.
				$component_options_count += count( $component_options );


				// Save selection style.
				$component_options_style = 'dropdowns';

				if ( isset( $post_data[ 'selection_mode' ] ) ) {
					$component_options_style = stripslashes( $post_data[ 'selection_mode' ] );
				}

				$bto_data[ $group_id ][ 'selection_mode' ] = $component_options_style;


				// Save default preferences.
				if ( isset( $post_data[ 'default_id' ] ) && ! empty( $post_data[ 'default_id' ] ) && count( $component_options ) > 0 ) {

					if ( in_array( $post_data[ 'default_id' ], $component_options ) )
						$bto_data[ $group_id ][ 'default_id' ] = stripslashes( $post_data[ 'default_id' ] );
					else {
						$bto_data[ $group_id ][ 'default_id' ] = '';
					}

				} else {

					// If the component option is only one, set it as default.
					if ( count( $component_options ) == 1 && ! isset( $post_data[ 'optional' ] ) ) {
						$bto_data[ $group_id ][ 'default_id' ] = $component_options[0];
					} else {
						$bto_data[ $group_id ][ 'default_id' ] = '';
					}
				}


				// Save title preferences.
				if ( isset( $post_data[ 'title' ] ) && ! empty( $post_data[ 'title' ] ) ) {
					$bto_data[ $group_id ][ 'title' ] = strip_tags( stripslashes( $post_data[ 'title' ] ) );
				} else {

					$bto_data[ $group_id ][ 'title' ] = 'Untitled Component';
					$this->add_notice( __( 'Please give a valid Name to all Components before publishing.', 'woocommerce-composite-products' ), 'error' );

					if ( isset( $posted_composite_data[ 'post_status' ] ) && $posted_composite_data[ 'post_status' ] === 'publish' ) {
						global $wpdb;
						$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $post_id ) );
					}
				}


				// Unpaginated selections style notice.
				if ( ! WC_CP()->api->options_style_supports( $component_options_style, 'pagination' ) ) {
					$unpaginated_options_count = count( $component_options );

					if ( $unpaginated_options_count > 30 ) {
						$dropdowns_prompt = sprintf( __( 'You have added %1$s product options to "%2$s". To reduce the load on your server, please consider switching the <strong>Options Style</strong> of the component to the <strong>Product Thumbnails</strong> setting, which enables a paginated display of Component Options.', 'woocommerce-composite-products' ), $unpaginated_options_count, strip_tags( stripslashes( $post_data[ 'title' ] ) ) );
						$this->add_notice( $dropdowns_prompt, 'warning' );
					}
				}


				// Save description preferences.
				if ( isset( $post_data[ 'description' ] ) && ! empty( $post_data[ 'description' ] ) ) {
					$bto_data[ $group_id ][ 'description' ] = wp_kses_post( stripslashes( $post_data[ 'description' ] ) );
				} else {
					$bto_data[ $group_id ][ 'description' ] = '';
				}


				// Save min quantity data.
				if ( isset( $post_data[ 'quantity_min' ] ) && is_numeric( $post_data[ 'quantity_min' ] ) ) {

					$quantity_min = absint( $post_data[ 'quantity_min' ] );

					if ( $quantity_min >= 0 ) {
						$bto_data[ $group_id ][ 'quantity_min' ] = $quantity_min;
					} else {
						$bto_data[ $group_id ][ 'quantity_min' ] = 1;

						$error = sprintf( __( 'The <strong>Min Quantity</strong> entered for "%s" was not valid and has been reset. Please enter a non-negative integer value.', 'woocommerce-composite-products' ), strip_tags( stripslashes( $post_data[ 'title' ] ) ) );
						$this->add_notice( $error, 'error' );
					}

				} else {
					// If its not there, it means the product was just added.
					$bto_data[ $group_id ][ 'quantity_min' ] = 1;

					$error = sprintf( __( 'The <strong>Min Quantity</strong> entered for "%s" was not valid and has been reset. Please enter a non-negative integer value.', 'woocommerce-composite-products' ), strip_tags( stripslashes( $post_data[ 'title' ] ) ) );
					$this->add_notice( $error, 'error' );
				}

				$quantity_min = $bto_data[ $group_id ][ 'quantity_min' ];


				// Save max quantity data.
				if ( isset( $post_data[ 'quantity_max' ] ) && ( is_numeric( $post_data[ 'quantity_max' ] ) || $post_data[ 'quantity_max' ] === '' ) ) {

					$quantity_max = $post_data[ 'quantity_max' ] !== '' ? absint( $post_data[ 'quantity_max' ] ) : '';

					if ( $quantity_max === '' || ( $quantity_max > 0 && $quantity_max >= $quantity_min ) ) {
						$bto_data[ $group_id ][ 'quantity_max' ] = $quantity_max;
					} else {
						$bto_data[ $group_id ][ 'quantity_max' ] = 1;

						$error = sprintf( __( 'The <strong>Max Quantity</strong> you entered for "%s" was not valid and has been reset. Please enter a positive integer value greater than (or equal to) <strong>Min Quantity</strong>, or leave the field empty.', 'woocommerce-composite-products' ), strip_tags( stripslashes( $post_data[ 'title' ] ) ) );
						$this->add_notice( $error, 'error' );
					}

				} else {
					// If its not there, it means the product was just added.
					$bto_data[ $group_id ][ 'quantity_max' ] = 1;

					$error = sprintf( __( 'The <strong>Max Quantity</strong> you entered for "%s" was not valid and has been reset. Please enter a positive integer value greater than (or equal to) <strong>Min Quantity</strong>, or leave the field empty.', 'woocommerce-composite-products' ), strip_tags( stripslashes( $post_data[ 'title' ] ) ) );
					$this->add_notice( $error, 'error' );
				}


				// Save discount data.
				if ( isset( $post_data[ 'discount' ] ) ) {

					if ( is_numeric( $post_data[ 'discount' ] ) ) {

						$discount = ( float ) wc_format_decimal( $post_data[ 'discount' ] );

						if ( $discount < 0 || $discount > 100 ) {

							$error = sprintf( __( 'The <strong>Discount</strong> value you entered for "%s" was not valid and has been reset. Please enter a positive number between 0-100.', 'woocommerce-composite-products' ), strip_tags( stripslashes( $post_data[ 'title' ] ) ) );
							$this->add_notice( $error, 'error' );

							$bto_data[ $group_id ][ 'discount' ] = '';

						} else {
							$bto_data[ $group_id ][ 'discount' ] = $discount;
						}
					} else {
						$bto_data[ $group_id ][ 'discount' ] = '';
					}
				} else {
					$bto_data[ $group_id ][ 'discount' ] = '';
				}


				// Save optional data.
				if ( isset( $post_data[ 'optional' ] ) ) {
					$bto_data[ $group_id ][ 'optional' ] = 'yes';
				} else {
					$bto_data[ $group_id ][ 'optional' ] = 'no';
				}

				// Save hide product title data.
				if ( isset( $post_data[ 'hide_product_title' ] ) ) {
					$bto_data[ $group_id ][ 'hide_product_title' ] = 'yes';
				} else {
					$bto_data[ $group_id ][ 'hide_product_title' ] = 'no';
				}


				// Save hide product description data.
				if ( isset( $post_data[ 'hide_product_description' ] ) ) {
					$bto_data[ $group_id ][ 'hide_product_description' ] = 'yes';
				} else {
					$bto_data[ $group_id ][ 'hide_product_description' ] = 'no';
				}


				// Save hide product thumbnail data.
				if ( isset( $post_data[ 'hide_product_thumbnail' ] ) ) {
					$bto_data[ $group_id ][ 'hide_product_thumbnail' ] = 'yes';
				} else {
					$bto_data[ $group_id ][ 'hide_product_thumbnail' ] = 'no';
				}


				// Save show orderby data.
				if ( isset( $post_data[ 'show_orderby' ] ) ) {
					$bto_data[ $group_id ][ 'show_orderby' ] = 'yes';
				} else {
					$bto_data[ $group_id ][ 'show_orderby' ] = 'no';
				}


				// Save show filters data.
				if ( isset( $post_data[ 'show_filters' ] ) ) {
					$bto_data[ $group_id ][ 'show_filters' ] = 'yes';
				} else {
					$bto_data[ $group_id ][ 'show_filters' ] = 'no';
				}


				// Save filters.
				if ( ! empty( $post_data[ 'attribute_filters' ] ) ) {
					$attribute_filter_ids = array_map( 'absint', $post_data[ 'attribute_filters' ] );
					$bto_data[ $group_id ][ 'attribute_filters' ] = array_values( $attribute_filter_ids );
				}


				// Prepare position data.
				if ( isset( $post_data[ 'position' ] ) ) {
					$ordering[ (int) $post_data[ 'position' ] ] = $group_id;
				} else {
					$ordering[ count( $ordering ) ] = $group_id;
				}

				/**
				 * Filter the component data before saving. Add custom errors via 'WC_CP()->admin->add_error()'.
				 *
				 * @param  array   $component_data
				 * @param  array   $post_data
				 * @param  string  $component_id
				 * @param  string  $post_id
				 */
				$bto_data[ $group_id ] = apply_filters( 'woocommerce_composite_process_component_data', $bto_data[ $group_id ], $post_data, $group_id, $post_id );

				// Invalidate query cache.
				if ( class_exists( 'WC_Cache_Helper' ) ) {
					WC_Cache_Helper::get_transient_version( 'wccp_q', true );
				}

				if ( ! wp_using_ext_object_cache() ) {
					// Delete query transients
					$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_wccp_q_" . $group_id . "_%') OR `option_name` LIKE ('_transient_timeout_wccp_q_" . $group_id . "_%')" );
				}
			}

			ksort( $ordering );
			$ordered_bto_data = array();
			$ordering_loop    = 0;

			foreach ( $ordering as $group_id ) {
				$ordered_bto_data[ $group_id ]               = $bto_data[ $group_id ];
				$ordered_bto_data[ $group_id ][ 'position' ] = $ordering_loop;
				$ordering_loop++;
			}

			// Prompt user to activate the right options when a Composite includes a large number of Component Options.
			if ( $component_options_count > 100 ) {
				if ( isset( $_POST[ '_per_product_pricing_bto' ] ) && empty( $_POST[ '_bto_hide_shop_price' ] ) ) {
					$large_composite_prompt = __( 'You have added a significant number of Component Options to this Composite. To avoid placing a big load on your server, consider checking the <strong>Hide Price</strong> option, which is located in the <strong>General</strong> tab. This setting will bypass all min/max pricing calculations which typically happen during product load when the Per-Item Pricing option is checked.', 'woocommerce-composite-products' );
					$this->add_notice( $large_composite_prompt, 'warning' );
				}
			}


			/*--------------------------*/
			/*  Scenarios.              */
			/*--------------------------*/

			// Convert posted data coming from select2 ajax inputs.
			$compat_scenario_data = array();

			if ( isset( $posted_composite_data[ 'bto_scenario_data' ] ) ) {
				foreach ( $posted_composite_data[ 'bto_scenario_data' ] as $scenario_id => $scenario_post_data ) {

					$compat_scenario_data[ $scenario_id ] = $scenario_post_data;

					if ( isset( $scenario_post_data[ 'component_data' ] ) ) {
						foreach ( $scenario_post_data[ 'component_data' ] as $component_id => $products_in_scenario ) {

							if ( ! empty( $products_in_scenario ) ) {
								if ( is_array( $products_in_scenario ) ) {
									$compat_scenario_data[ $scenario_id ][ 'component_data' ][ $component_id ] = array_unique( array_map( 'intval', $products_in_scenario ) );
								} else {
									$compat_scenario_data[ $scenario_id ][ 'component_data' ][ $component_id ] = array_unique( array_map( 'intval', explode( ',', $products_in_scenario ) ) );
								}
							} else {
								$compat_scenario_data[ $scenario_id ][ 'component_data' ][ $component_id ] = array();
							}
						}
					}
				}

				$posted_composite_data[ 'bto_scenario_data' ] = $compat_scenario_data;
			}
			// End conversion.

			// Start processing.
			$bto_scenario_data          = array();
			$ordered_bto_scenario_data  = array();
			$compat_group_actions_exist = false;
			$masked_rules_exist         = false;

			if ( isset( $posted_composite_data[ 'bto_scenario_data' ] ) ) {

				$counter = 0;
				$scenario_ordering = array();

				foreach ( $posted_composite_data[ 'bto_scenario_data' ] as $scenario_id => $scenario_post_data ) {

					$scenario_id = isset ( $scenario_post_data[ 'scenario_id' ] ) ? stripslashes( $scenario_post_data[ 'scenario_id' ] ) : ( current_time( 'timestamp' ) + $counter );
					$counter++;

					$bto_scenario_data[ $scenario_id ] = array();

					// Save scenario title.
					if ( isset( $scenario_post_data[ 'title' ] ) && ! empty( $scenario_post_data[ 'title' ] ) ) {
						$bto_scenario_data[ $scenario_id ][ 'title' ] = strip_tags ( stripslashes( $scenario_post_data[ 'title' ] ) );
					} else {
						unset( $bto_scenario_data[ $scenario_id ] );
						$this->add_notice( __( 'Please give a valid Name to all Scenarios before saving.', 'woocommerce-composite-products' ), 'error' );
						continue;
					}

					// Save scenario description.
					if ( isset( $scenario_post_data[ 'description' ] ) && ! empty( $scenario_post_data[ 'description' ] ) ) {
						$bto_scenario_data[ $scenario_id ][ 'description' ] = wp_kses_post( stripslashes( $scenario_post_data[ 'description' ] ) );
					} else {
						$bto_scenario_data[ $scenario_id ][ 'description' ] = '';
					}

					// Prepare position data.
					if ( isset( $scenario_post_data[ 'position' ] ) ) {
						$scenario_ordering[ ( int ) $scenario_post_data[ 'position' ] ] = $scenario_id;
					} else {
						$scenario_ordering[ count( $scenario_ordering ) ] = $scenario_id;
					}

					$bto_scenario_data[ $scenario_id ][ 'scenario_actions' ] = array();

					// Save scenario action(s).
					if ( isset( $scenario_post_data[ 'scenario_actions' ][ 'compat_group' ] ) ) {

						if ( ! empty( $scenario_post_data[ 'scenario_actions' ][ 'compat_group' ][ 'is_active' ] ) ) {
							$bto_scenario_data[ $scenario_id ][ 'scenario_actions' ][ 'compat_group' ][ 'is_active' ] = 'yes';
							$compat_group_actions_exist = true;
						}
					} else {
						$bto_scenario_data[ $scenario_id ][ 'scenario_actions' ][ 'compat_group' ][ 'is_active' ] = 'no';
					}

					// Save component options in scenario.
					$bto_scenario_data[ $scenario_id ][ 'component_data' ] = array();

					foreach ( $ordered_bto_data as $group_id => $group_data ) {

						// Save modifier flag.
						if ( isset( $scenario_post_data[ 'modifier' ][ $group_id ] ) && $scenario_post_data[ 'modifier' ][ $group_id ] === 'not-in' ) {

							if ( ! empty( $scenario_post_data[ 'component_data' ][ $group_id ] ) ) {

								if ( WC_CP_Scenarios::scenario_contains_product( $scenario_post_data, $group_id, 0 ) ) {
									$bto_scenario_data[ $scenario_id ][ 'modifier' ][ $group_id ] = 'in';
								} else {
									$bto_scenario_data[ $scenario_id ][ 'modifier' ][ $group_id ] = 'not-in';
								}
							} else {
								$bto_scenario_data[ $scenario_id ][ 'modifier' ][ $group_id ] = 'in';
							}

						} elseif ( isset( $scenario_post_data[ 'modifier' ][ $group_id ] ) && $scenario_post_data[ 'modifier' ][ $group_id ] === 'masked' ) {
							$bto_scenario_data[ $scenario_id ][ 'modifier' ][ $group_id ] = 'masked';

							$masked_rules_exist = true;

							if ( ! WC_CP_Scenarios::scenario_contains_product( $scenario_post_data, $group_id, 0 ) ) {
								$scenario_post_data[ 'component_data' ][ $group_id ][] = 0;
							}
						} else {
							$bto_scenario_data[ $scenario_id ][ 'modifier' ][ $group_id ] = 'in';
						}


						$all_active = false;

						if ( ! empty( $scenario_post_data[ 'component_data' ][ $group_id ] ) ) {

							$bto_scenario_data[ $scenario_id ][ 'component_data' ][ $group_id ] = array();

							if ( WC_CP_Scenarios::scenario_contains_product( $scenario_post_data, $group_id, 0 ) ) {

								$bto_scenario_data[ $scenario_id ][ 'component_data' ][ $group_id ][] = 0;
								$all_active = true;
							}

							if ( $all_active ) {
								continue;
							}

							if ( WC_CP_Scenarios::scenario_contains_product( $scenario_post_data, $group_id, -1 ) ) {
								$bto_scenario_data[ $scenario_id ][ 'component_data' ][ $group_id ][] = -1;
							}

							// Run query to get component option ids.
							$component_options = WC_CP()->api->get_component_options( $group_data );


							foreach ( $scenario_post_data[ 'component_data' ][ $group_id ] as $item_in_scenario ) {

								if ( (int) $item_in_scenario === -1 || (int) $item_in_scenario === 0 ) {
									continue;
								}

								// Get product.
								$product_in_scenario = get_product( $item_in_scenario );

								if ( $product_in_scenario->product_type === 'variation' ) {

									$parent_id = $product_in_scenario->id;

									if ( $parent_id && in_array( $parent_id, $component_options ) && ! in_array( $parent_id, $scenario_post_data[ 'component_data' ][ $group_id ] ) ) {
										$bto_scenario_data[ $scenario_id ][ 'component_data' ][ $group_id ][] = $item_in_scenario;
									}

								} else {

									if ( in_array( $item_in_scenario, $component_options ) ) {
										$bto_scenario_data[ $scenario_id ][ 'component_data' ][ $group_id ][] = $item_in_scenario;
									}
								}
							}

						} else {

							$bto_scenario_data[ $scenario_id ][ 'component_data' ][ $group_id ]   = array();
							$bto_scenario_data[ $scenario_id ][ 'component_data' ][ $group_id ][] = 0;
						}

					}

					/**
					 * Filter the scenario data before saving. Add custom errors via 'WC_CP()->admin->add_error()'.
					 *
					 * @param  array   $scenario_data
					 * @param  array   $post_data
					 * @param  string  $scenario_id
					 * @param  array   $composite_data
					 * @param  string  $post_id
					 */
					$bto_scenario_data[ $scenario_id ] = apply_filters( 'woocommerce_composite_process_scenario_data', $bto_scenario_data[ $scenario_id ], $scenario_post_data, $scenario_id, $ordered_bto_data, $post_id );
				}

				// Re-order and save position data.
				ksort( $scenario_ordering );
				$ordering_loop = 0;
				foreach ( $scenario_ordering as $scenario_id ) {
					$ordered_bto_scenario_data[ $scenario_id ]               = $bto_scenario_data[ $scenario_id ];
					$ordered_bto_scenario_data[ $scenario_id ][ 'position' ] = $ordering_loop;
				    $ordering_loop++;
				}

			}

			// Verify defaults.
			if ( ! empty( $ordered_bto_scenario_data ) ) {

				// Stacked layout notices.
				if ( $composite_layout === 'single' && $compat_group_actions_exist ) {
					$info = __( 'For a more streamlined user experience in applications that involve Scenarios and dependent Component Options, please consider selecting the <strong>Progressive</strong>, <strong>Stepped</strong> or <strong>Componentized</strong> layout.', 'woocommerce-composite-products' );
					$this->add_notice( $info, 'info' );
				}

				// Only build scenarios for the defaults.
				foreach ( $ordered_bto_data as $group_id => $group_data ) {
					$bto_data[ $group_id ][ 'current_component_options' ] = array( $group_data[ 'default_id' ] );
				}

				$scenarios_for_products = WC_CP_Scenarios::build_scenarios( $ordered_bto_scenario_data, $bto_data );

				$common_scenarios = array_values( $scenarios_for_products[ 'scenarios' ] );

				foreach ( $ordered_bto_data as $group_id => $group_data ) {

					$default_option_id = $group_data[ 'default_id' ];

					if ( $default_option_id !== '' ) {

						if ( empty( $scenarios_for_products[ 'scenario_data' ][ $group_id ][ $default_option_id ] ) ) {
							$error = sprintf( __( 'The <strong>Default Component Option</strong> that you selected for "%s" is not active in any Scenario. The default Component Options must be compatible in order to work. Always double-check your preferences before saving, and always save any changes made to the Component Options before choosing new defaults.', 'woocommerce-composite-products' ), $group_data[ 'title' ] );
							$this->add_notice( $error, 'error' );
						} else {
							$common_scenarios = array_intersect( $common_scenarios, $scenarios_for_products[ 'scenario_data' ][ $group_id ][ $default_option_id ] );
						}
					}
				}

				if ( empty( $common_scenarios ) ) {
					$error = __( 'The set of default Component Options that you selected was not found in any of the defined Scenarios. The default Component Options must be compatible in order to work. Always double-check the default Component Options before creating or modifying Scenarios.', 'woocommerce-composite-products' );
					$this->add_notice( $error, 'error' );
				}
			}

			// Save config.
			update_post_meta( $post_id, '_bto_data', $ordered_bto_data );
			update_post_meta( $post_id, '_bto_scenario_data', $ordered_bto_scenario_data );
		}

		if ( ! isset( $posted_composite_data[ 'bto_data' ] ) || count( $bto_data ) == 0 ) {

			delete_post_meta( $post_id, '_bto_data' );

			$this->add_notice( __( 'Please create at least one Component before publishing. To add a Component, go to the Components tab and click on the Add Component button.', 'woocommerce-composite-products' ), 'error' );

			if ( isset( $posted_composite_data[ 'post_status' ] ) && $posted_composite_data[ 'post_status' ] === 'publish' ) {
				global $wpdb;
				$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $post_id ) );
			}

			return false;
		}

		if ( $zero_product_item_exists ) {
			$this->add_notice( __( 'Please assign at least one valid Component Option to every Component. Once you have added a Component, you can add Component Options to it by selecting products individually, or by choosing product categories.', 'woocommerce-composite-products' ), 'error' );
			return false;
		}

		return true;
	}

	/**
	 * Handles saving composite config via ajax.
	 *
	 * @return void
	 */
	public function ajax_composite_save() {

		check_ajax_referer( 'wc_bto_save_composite', 'security' );

		parse_str( $_POST[ 'data' ], $posted_composite_data );

		$post_id = absint( $_POST[ 'post_id' ] );

		$this->save_composite_config( $post_id, $posted_composite_data );

		wp_send_json( $this->saved_notices );
	}

	/**
	 * Handles adding components via ajax.
	 *
	 * @return void
	 */
	public function ajax_add_component() {

		check_ajax_referer( 'wc_bto_add_component', 'security' );

		$id             = intval( $_POST[ 'id' ] );
		$post_id        = intval( $_POST[ 'post_id' ] );

		$component_data = array();

		/**
		 * Action 'woocommerce_composite_component_admin_html'.
		 *
		 * @param  int     $id
		 * @param  array   $component_data
		 * @param  int     $post_id
		 * @param  string  $state
		 *
		 * @hooked {@see component_admin_html} - 10
		 */
		do_action( 'woocommerce_composite_component_admin_html', $id, $component_data, $post_id, 'open' );

		die();
	}

	/**
	 * Handles adding scenarios via ajax.
	 *
	 * @return void
	 */
	public function ajax_add_scenario() {

		check_ajax_referer( 'wc_bto_add_scenario', 'security' );

		$id             = intval( $_POST[ 'id' ] );
		$post_id        = intval( $_POST[ 'post_id' ] );

		$composite_data = get_post_meta( $post_id, '_bto_data', true );
		$scenario_data  = array();

		/**
		 * Action 'woocommerce_composite_scenario_admin_html'.
		 *
		 * @param  int     $id
		 * @param  array   $scenario_data
		 * @param  array   $composite_data
		 * @param  int     $post_id
		 * @param  string  $state
		 *
		 * @hooked {@see scenario_admin_html} - 10
		 */
		do_action( 'woocommerce_composite_scenario_admin_html', $id, $scenario_data, $composite_data, $post_id, 'open' );

		die();
	}

	/**
	 * Search for default component option and echo json.
	 *
	 * @return void
	 */
	public function json_search_default_component_option() {
		$this->json_search_component_options();
	}

	/**
	 * Search for default component option and echo json.
	 *
	 * @return void
	 */
	public function json_search_component_options_in_scenario() {
		$this->json_search_component_options( 'search_component_options_in_scenario', $post_types = array( 'product', 'product_variation' ) );
	}

	/**
	 * Search for component options and echo json.
	 *
	 * @param   string $x (default: '')
	 * @param   string $post_types (default: array('product'))
	 * @return  void
	 */
	public function json_search_component_options( $x = 'default', $post_types = array( 'product' ) ) {

		global $wpdb;

		ob_start();

		check_ajax_referer( 'search-products', 'security' );

		$term         = (string) wc_clean( stripslashes( $_GET[ 'term' ] ) );
		$like_term    = '%' . $wpdb->esc_like( $term ) . '%';

		$composite_id = $_GET[ 'composite_id' ];
		$component_id = $_GET[ 'component_id' ];

		if ( empty( $term ) || empty( $composite_id ) || empty( $component_id ) ) {
			die();
		}

		$composite_data = get_post_meta( $composite_id, '_bto_data', true );
		$component_data = isset( $composite_data[ $component_id ] ) ? $composite_data[ $component_id ] : false;

		if ( false == $composite_data || false == $component_data ) {
			die();
		}

		// Run query to get component option ids.
		$component_options = WC_CP()->api->get_component_options( $component_data );

		// Add variation ids to component option ids.
		if ( $x === 'search_component_options_in_scenario' ) {
			$variations_args = array(
				'post_type'      => array( 'product_variation' ),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'post_parent'    => array_merge( array( '0' ), $component_options ),
				'fields'         => 'ids'
			);

			$component_options_variations = get_posts( $variations_args );
			$component_options            = array_merge( $component_options, $component_options_variations );
		}

		if ( is_numeric( $term ) ) {

			$query = $wpdb->prepare( "
				SELECT ID FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
				WHERE posts.post_status = 'publish'
				AND (
					posts.post_parent = %s
					OR posts.ID = %s
					OR posts.post_title LIKE %s
					OR (
						postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
					)
				)
			", $term, $term, $term, $like_term );

		} else {

			$query = $wpdb->prepare( "
				SELECT ID FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
				WHERE posts.post_status = 'publish'
				AND (
					posts.post_title LIKE %s
					or posts.post_content LIKE %s
					OR (
						postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
					)
				)
			", $like_term, $like_term, $like_term );
		}

		$query .= " AND posts.post_type IN ('" . implode( "','", array_map( 'esc_sql', $post_types ) ) . "')";

		// Include results among component options only.
		$query .= " AND posts.ID IN (" . implode( ',', array_map( 'intval', $component_options ) ) . ")";

		// Include first 1000 results only.
		$query .= " LIMIT 1000";

		$posts          = array_unique( $wpdb->get_col( $query ) );
		$found_products = array();

		if ( $posts ) {
			foreach ( $posts as $post ) {

				$product = wc_get_product( $post );

				if ( $product->product_type === 'variation' ) {
					$found_products[ $post ] = WC_CP_Helpers::get_product_variation_title( $product );
				} else {
					if ( $x === 'search_component_options_in_scenario' && $product->product_type === 'variable' ) {
						$found_products[ $post ] = WC_CP_Helpers::get_product_title( $product ) . ' ' . __( '&mdash; All Variations', 'woocommerce-composite-products' );
					} else {
						$found_products[ $post ] = WC_CP_Helpers::get_product_title( $product );
					}
				}
			}
		}

		wp_send_json( $found_products );
	}

	/**
	 * Support scanning for template overrides in extension.
	 *
	 * @param  array $paths
	 * @return array
	 */
	public function composite_template_scan_path( $paths ) {

		$paths[ 'WooCommerce Composite Products' ] = WC_CP()->plugin_path() . '/templates/';

		return $paths;
	}

	/**
	 * Add and return admin meta box save notices.
	 *
	 * @param  string $content
	 * @param  string $type
	 * @return string
	 */
	private function add_admin_notice( $content, $type ) {

		WC_CP_Admin_Notices::add_notice( $content, $type, true );

		return strip_tags( $content );
	}

	/**
	 * Add custom save notices via filters.
	 *
	 * @param string $content
	 * @param string $type
	 */
	public function add_notice( $content, $type ) {

		$this->saved_notices[] = $this->add_admin_notice( $content, $type );
	}

	/**
	 * Add custom save errors via filters.
	 *
	 * @param string $error
	 */
	public function add_error( $error ) {

		$this->add_notice( $error, 'error' );
	}
}

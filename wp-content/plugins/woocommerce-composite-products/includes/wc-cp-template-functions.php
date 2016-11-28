<?php
/**
 * Composite Product Template Functions.
 *
 * @version  3.6.0
 * @since    3.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/*---------------------------------------------------------*/
/*                                                         */
/*  Composite products single product template functions.  */
/*                                                         */
/*---------------------------------------------------------*/

/**
 * Add-to-cart template for composite products.
 *
 * @return void
 */
function wc_cp_add_to_cart() {

	global $product;

	// Enqueue scripts.
	wp_enqueue_script( 'wc-add-to-cart-composite' );

	// Enqueue styles.
	wp_enqueue_style( 'wc-composite-single-css' );

	// Load NYP scripts.
	if ( function_exists( 'WC_Name_Your_Price' ) ) {
		WC_Name_Your_Price()->display->nyp_scripts();
	}

	// Enqueue Bundle styles.
	if ( class_exists( 'WC_Bundles' ) ) {
		wp_enqueue_style( 'wc-bundle-css' );
	}

	$navigation_style = $product->get_composite_layout_style();
	$components       = $product->get_composite_data();

	if ( ! empty( $components ) ) {
		wc_get_template( 'single-product/add-to-cart/composite.php', array(
			'navigation_style' => $navigation_style,
			'components'       => $components,
			'product'          => $product
		), '', WC_CP()->plugin_path() . '/templates/' );
	}
}

/**
 * Add-to-cart button and quantity template for composite products.
 *
 * @return void
 */
function wc_cp_add_to_cart_button() {

	if ( isset( $_GET[ 'update-composite' ] ) ) {
		$cart_id = wc_clean( $_GET[ 'update-composite' ] );
		echo '<input type="hidden" name="update-composite" value="' . $cart_id . '" />';
	}

	wc_get_template( 'single-product/add-to-cart/composite-quantity-input.php', array(), false, WC_CP()->plugin_path() . '/templates/' );
	wc_get_template( 'single-product/add-to-cart/composite-button.php', array(), false, WC_CP()->plugin_path() . '/templates/' );
}


/*-----------------------------------------------------------------------------*/
/*                                                                             */
/*  Composite products single product summary widget functions.                */
/*                                                                             */
/*-----------------------------------------------------------------------------*/

/**
 * Summary widget content.
 *
 * @since  3.6.0
 * @param  WC_Product_Composite $composite
 * @return void
 */
function wc_cp_summary_widget_content( $components, $composite ) {

	?><div class="widget_composite_summary_elements cp_clearfix"><?php
		wc_get_template( 'single-product/composite-summary-content.php', array(
			'summary_columns'  => 1,
			'summary_elements' => count( $components ),
			'components'       => $components,
			'product'          => $composite
		), '', WC_CP()->plugin_path() . '/templates/' );
	?></div><?php
}

/**
 * Summary widget price. Empty element to be populated by the script.
 *
 * @since  3.6.0
 * @param  WC_Product_Composite $composite
 * @return void
 */
function wc_cp_summary_widget_price( $components, $composite ) {

	?><div class="widget_composite_summary_price cp_clearfix">
		<div class="composite_price"></div>
	</div><?php
}

/**
 * Summary widget validation message. Empty element to be populated by the script.
 *
 * @since  3.6.0
 * @param  WC_Product_Composite $composite
 * @return void
 */
function wc_cp_summary_widget_message( $components, $composite ) {

	?><div class="widget_composite_summary_error cp_clearfix">
		<div class="composite_message" style="display:none;"><ul class="msg woocommerce-info"></ul></div>
	</div><?php
}

/**
 * Summary widget product availability.
 *
 * @since  3.6.0
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_summary_widget_availability( $components, $product ) {

	?><div class="widget_composite_summary_availability cp_clearfix">
		<div class="composite_availability"><?php
			$availability      = $product->get_availability();
			$availability_html = empty( $availability[ 'availability' ] ) ? '' : '<p class="stock ' . esc_attr( $availability[ 'class' ] ) . '">' . esc_html( $availability[ 'availability' ] ) . '</p>';

			echo apply_filters( 'woocommerce_stock_html', $availability_html, $availability[ 'availability' ], $product );
		?></div>
	</div><?php
}

/**
 * Summary widget add-to-cart button.
 *
 * @since  3.6.0
 * @param  WC_Product_Composite $composite
 * @return void
 */
function wc_cp_summary_widget_button( $components, $composite ) {

	?><div class="widget_composite_summary_button cp_clearfix">
		<div class="composite_button">
			<?php do_action( 'woocommerce_composite_add_to_cart_button' ); ?>
		</div>
	</div><?php
}


/*-----------------------------------------------------------------------------*/
/*                                                                             */
/*  Composite products single product template functions - component options.  */
/*                                                                             */
/*-----------------------------------------------------------------------------*/

/**
 * In progressive mode, wrap component options & sorting/filtering controls in a blockable div.
 *
 * @param  string               $component_id
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_add_progressive_mode_block_wrapper_end( $component_id, $product ) {

	?></div><?php
}

/**
 * In progressive mode, wrap component options & sorting/filtering controls in a blockable div.
 *
 * @param  string               $component_id
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_add_progressive_mode_block_wrapper_start( $component_id, $product ) {

	?><div class="component_selections_inner">
		<div class="block_component_selections_inner"></div><?php
}

/**
 * Show current selection details in non-paged modes.
 *
 * @param  string               $component_id
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_add_current_selection_details( $component_id, $product ) {

	// Default Component Option.
	$selected_option  = $product->get_current_component_selection( $component_id );
	$navigation_style = $product->get_composite_layout_style();

	?><div class="component_content" data-product_id="<?php echo $component_id; ?>">
		<div class="component_summary cp_clearfix"><?php

			/**
			 * Action 'woocommerce_composite_component_before_summary_content_paged'.
			 * @since  3.4.0
			 *
			 * @param  string                $component_id
			 * @param  WC_Product_Composite  $product
			 *
			 * @hooked wc_cp_component_summary_message - 10
			 */
			do_action( 'woocommerce_composite_component_before_summary_content_' . $navigation_style, $component_id, $product );

			?><div class="product content summary_content"><?php
				echo WC_CP()->display->show_composited_product( $selected_option, $component_id, $product );
			?></div>
		</div>
	</div><?php
}

/**
 * Show current selection details in paged modes -- added before component options when viewed as thumbnails.
 *
 * @param  string               $component_id
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_add_current_selection_details_paged_top( $component_id, $product ) {

	$options_style = $product->get_component_options_style( $component_id );

	if ( $options_style === 'thumbnails' ) {
		wc_cp_add_current_selection_details( $component_id, $product );
	}
}

/**
 * Show current selection details in paged modes -- added after component options when viewed as thumbnails.
 *
 * @param  string               $component_id
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_add_current_selection_details_paged_bottom( $component_id, $product ) {

	$options_style = $product->get_component_options_style( $component_id );

	if ( $options_style === 'dropdowns' || $options_style === 'radios' ) {
		wc_cp_add_current_selection_details( $component_id, $product );
	}
}

/**
 * Show component options pagination.
 *
 * @param  string               $component_id
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_add_component_options_pagination( $component_id, $product ) {

	// Component Options Pagination template.
	wc_get_template( 'single-product/component-options-pagination.php', array(
		'product'      => $product,
		'component_id' => $component_id,
	), '', WC_CP()->plugin_path() . '/templates/' );
}

/**
 * Show component options.
 *
 * @param  string               $component_id
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_add_component_options( $component_id, $product ) {

	// Component Options template.
	wc_get_template( 'single-product/component-options.php', array(
		'product'           => $product,
		'component_id'      => $component_id,
		'component_options' => $product->get_current_component_options( $component_id ),
		'component_data'    => $product->get_component_data( $component_id ),
		'selected_option'   => $product->get_current_component_selection( $component_id )
	), '', WC_CP()->plugin_path() . '/templates/' );
}

/**
 * Show component options as thumbnails.
 *
 * @param  string               $component_id
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_component_options_thumbnails( $component_id, $product ) {

	$component_data = $product->get_component_data( $component_id );
	$quantity_min   = $component_data[ 'quantity_min' ];
	$quantity_max   = $component_data[ 'quantity_max' ];

	// Thumbnails template.
	wc_get_template( 'single-product/component-options-thumbnails.php', array(
		'product'           => $product,
		'component_id'      => $component_id,
		'quantity_min'      => $quantity_min,
		'quantity_max'      => $quantity_max,
		'component_options' => $product->get_current_component_options( $component_id ),
		'component_data'    => $component_data,
		'selected_option'   => $product->get_current_component_selection( $component_id )
	), '', WC_CP()->plugin_path() . '/templates/' );
}

/**
 * Show component option as radio buttons.
 *
 * @param  string               $component_id
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_component_options_radios( $component_id, $product ) {

	$component_data = $product->get_component_data( $component_id );
	$quantity_min   = $component_data[ 'quantity_min' ];
	$quantity_max   = $component_data[ 'quantity_max' ];

	// Radio buttons template.
	wc_get_template( 'single-product/component-options-radio-buttons.php', array(
		'product'           => $product,
		'component_id'      => $component_id,
		'quantity_min'      => $quantity_min,
		'quantity_max'      => $quantity_max,
		'component_options' => $product->get_current_component_options( $component_id ),
		'component_data'    => $component_data,
		'selected_option'   => $product->get_current_component_selection( $component_id )
	), '', WC_CP()->plugin_path() . '/templates/' );
}

/**
 * Add sorting input.
 *
 * @param  string               $component_id
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_add_sorting( $component_id, $product ) {

	// Component Options sorting template.
	wc_get_template( 'single-product/component-options-orderby.php', array(
		'product'      => $product,
		'component_id' => $component_id,
	), '', WC_CP()->plugin_path() . '/templates/' );
}

/**
 * Add attribute filters.
 *
 * @param  string               $component_id
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_add_filtering( $component_id, $product ) {

	// Component Options filtering template.
	wc_get_template( 'single-product/component-options-filters.php', array(
		'product'      => $product,
		'component_id' => $component_id,
	), '', WC_CP()->plugin_path() . '/templates/' );
}


/*----------------------------------------------------------------------------------*/
/*                                                                                  */
/*  Composite products single product template functions - layout.                  */
/*                                                                                  */
/*----------------------------------------------------------------------------------*/

/**
 * Add Composite Summary on the 'woocommerce_before_add_to_cart_button' hook.
 *
 * @return void
 */
function wc_cp_before_add_to_cart_button() {

	global $product;

	if ( $product->product_type === 'composite' ) {
		wc_cp_add_summary( $product->get_composite_data(), $product );
	}
}

/**
 * Add Review/Summary with current configuration details.
 * The Summary template must be loaded if the summary widget is active.
 *
 * @param  array                $components
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_add_summary( $components, $product ) {

	$navigation_style           = $product->get_composite_layout_style();
	$navigation_style_variation = $product->get_composite_layout_style_variation();

	/**
	 * Filter to determine whether the summary will be displayed.
	 *
	 * @param  boolean               $show_summary
	 * @param  string                $layout_id
	 * @param  string                $layout_variation_id
	 * @param  WC_Product_Composite  $product
	 */
	$show_summary = apply_filters( 'woocommerce_composite_summary_display', $navigation_style === 'paged', $navigation_style, $navigation_style_variation, $product );

	if ( $show_summary ) {
		wc_get_template( 'single-product/composite-summary.php', array(
			'product'    => $product,
			'components' => $components,
			'hidden'     => false,
		), '', WC_CP()->plugin_path() . '/templates/' );
	}
}

/**
 * Hook layout/style-specific content on the 'woocommerce_composite_before_components' action.
 *
 * @return void
 */
function wc_cp_before_components( $components, $product ) {

	$layout = $product->get_composite_layout_style();

	/**
	 * Action 'woocommerce_composite_before_components_paged':
	 * @since  3.4.0
	 *
	 * @param  array                 $components
	 * @param  WC_Product_Composite  $product
	 *
	 * @hooked wc_cp_component_transition_scroll_target    - 10
	 * @hooked wc_cp_pagination                            - 15
	 * @hooked wc_cp_navigation_top                        - 20
	 * @hooked wc_cp_navigation_movable                    - 20
	 */
	do_action( 'woocommerce_composite_before_components_' . $layout, $components, $product );
}

/**
 * Hook layout/style-specific content on the 'woocommerce_composite_after_components' action.
 *
 * @return void
 */
function wc_cp_after_components( $components, $product ) {

	$layout = $product->get_composite_layout_style();

	/**
	 * Action 'woocommerce_composite_after_components_{$layout}':
	 * @since  3.4.0
	 *
	 * @param  array                 $components
	 * @param  WC_Product_Composite  $product
	 *
	 * Action 'woocommerce_composite_after_components_single':
	 *
	 * @hooked wc_cp_add_to_cart_section - 10
	 *
	 *
	 * Action 'woocommerce_composite_after_components_progressive':
	 *
	 * @hooked wc_cp_add_to_cart_section - 10
	 * @hooked wc_cp_navigation_bottom   - 15
	 *
	 *
	 * Action 'woocommerce_composite_after_components_paged':
	 *
	 * @hooked wc_cp_add_to_cart_section                  - 10
	 * @hooked wc_cp_navigation_bottom                    - 20
	 */
	do_action( 'woocommerce_composite_after_components_' . $layout, $components, $product );
}

/**
 * Add-to-cart section.
 *
 * @param  array                $components
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_add_to_cart_section( $components, $product ) {

	wc_get_template( 'single-product/composite-add-to-cart.php', array(
		'product'                    => $product,
		'components'                 => $components,
		'navigation_style'           => $product->get_composite_layout_style(),
		'navigation_style_variation' => $product->get_composite_layout_style_variation(),
	), '', WC_CP()->plugin_path() . '/templates/' );

}

/**
 * Add previous/next navigation buttons in paged mode -- added on bottom of page under the component options section.
 *
 * @param  array                $components
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_navigation_bottom( $components, $product ) {

	$position                   = 'bottom';
	$navigation_style           = $product->get_composite_layout_style();
	$navigation_style_variation = $product->get_composite_layout_style_variation();

	$classes = array( $position, $navigation_style, $navigation_style_variation );

	wc_cp_navigation( $classes, $product );
}

/**
 * Add previous/next navigation buttons in paged mode -- added on top of page under the composite pagination section when component options are viewed as thumbnails.
 *
 * @param  array                $components
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_navigation_top( $components, $product ) {

	$position                   = 'top';
	$navigation_style           = 'paged';
	$navigation_style_variation = $product->get_composite_layout_style_variation();

	$classes = array( $position, $navigation_style, $navigation_style_variation );
	wc_cp_navigation( $classes, $product );
}

/**
 * Add previous/next navigation buttons in multi-page mode -- added on top of page under the composite pagination section.
 *
 * @param  array                $components
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_navigation_movable( $components, $product ) {

	$position                   = 'movable hidden';
	$navigation_style           = 'paged';
	$navigation_style_variation = $product->get_composite_layout_style_variation();

	$classes = array( $position, $navigation_style, $navigation_style_variation );
	wc_cp_navigation( $classes, $product );
}

/**
 * Add previous/next navigation buttons in multi-page mode.
 *
 * @param  array                $classes
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_navigation( $classes, $composite ) {

	wc_get_template( 'single-product/composite-navigation.php', array(
		'product'                    => $composite,
		'navigation_style'           => $composite->get_composite_layout_style(),
		'navigation_style_variation' => $composite->get_composite_layout_style_variation(),
		'classes'                    => implode( ' ', $classes )
	), '', WC_CP()->plugin_path() . '/templates/' );
}

/**
 * Component selection notices container displayed in the component_summary container.
 *
 * @param  string               $component_id
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_component_summary_message( $component_id, $product ) {

	$classes = array( 'top' );
	wc_cp_component_message( $classes );
}

/**
 * Component selection notices container displayed in the component_selections container.
 *
 * @param  array                $components
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_component_selections_message( $components, $product ) {

	$classes = array( 'bottom' );
	wc_cp_component_message( $classes );
}

/**
 * Component selection notices container displayed in progressive/paged layouts.
 *
 * @param  array                $classes
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_component_message( $classes ) {

	echo '<div class="component_message ' . implode( ' ', $classes ) . '" style="display:none"><ul class="msg woocommerce-info"></ul></div>';
}

/**
 * When changing between components in paged mode, the viewport will scroll to this div if it's not visible.
 * Adding the 'scroll_bottom' class to the element will scroll the bottom of the viewport to the target.
 *
 * @param  string               $component_id
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_component_transition_scroll_target( $components, $product ) {

	?><div class="scroll_show_component"></div><?php
}

/**
 * Adds composite pagination in paged mode.
 *
 * @param  string               $component_id
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_pagination( $components, $product ) {

	$layout_variation = $product->get_composite_layout_style_variation();

	if ( $layout_variation !== 'componentized' ) {

		wc_get_template( 'single-product/composite-pagination.php', array(
			'product'          => $product,
			'components'       => $components,
		), '', WC_CP()->plugin_path() . '/templates/' );

	}
}

/**
 * When selecting the final step in paged mode, the viewport will scroll to this div.
 * Adding the 'scroll_bottom' class to the element will scroll the bottom of the viewport to the target.
 *
 * @param  string               $component_id
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_final_step_scroll_target( $components, $product ) {

	$navigation_style = $product->get_composite_layout_style();

	if ( $navigation_style === 'paged' ) {

		?><div class="scroll_final_step"></div><?php
	}
}

/**
 * No js notice.
 *
 * @param  array                $components
 * @param  WC_Product_Composite $product
 * @return void
 */
function wc_cp_no_js_msg( $components, $product ) {

	?><p  class="cp-no-js-msg">
		<span id="cp-no-js-msg">
			<script type="text/javascript">
				var el = document.getElementById( 'cp-no-js-msg' );
				el.innerHTML = "<?php _e( 'Loading...', 'woocommerce-composite-products' ); ?>";
			</script>
		</span>
		<noscript>
			<?php _e( 'JavaScript must be supported by your browser and needs to be enabled in order to view this page.', 'woocommerce-composite-products' ); ?>
		</noscript>
	</p><?php
}


/*--------------------------------------------------------*/
/*                                                        */
/*  Composited products template functions.               */
/*                                                        */
/*--------------------------------------------------------*/

/**
 * Composited product title template.
 *
 * @param  WC_Product           $product
 * @param  string               $component_id
 * @param  WC_Product_Composite $composite
 * @return void
 */
function wc_cp_composited_product_title( $product, $component_id, $composite ) {

	$component_data     = $composite->get_component_data( $component_id );
	$hide_product_title = isset( $component_data[ 'hide_product_title' ] ) ? $component_data[ 'hide_product_title' ] : 'no';
	$show_selection_ui  = true;

	if ( $composite->is_component_static( $component_id ) ) {
		$show_selection_ui = false;
	}

	// Current selection title.
	if ( $hide_product_title !== 'yes' ) {

		if ( $show_selection_ui ) {
			wc_get_template( 'composited-product/selection.php', array(
				'component_id' => $component_id,
				'composite'    => $composite
			), '', WC_CP()->plugin_path() . '/templates/' );
		}

		wc_get_template( 'composited-product/title.php', array(
			'title'        => $product->get_title(),
			'product_id'   => $product->id,
			'component_id' => $component_id,
			'composite'    => $composite,
			'quantity'     => ''
		), '', WC_CP()->plugin_path() . '/templates/' );
	}

	// Clear current selection.
	if ( $show_selection_ui ) {
		?><p class="component_section_title clear_component_options_wrapper">
			<a class="clear_component_options" href="#clear_component"><?php
				echo __( 'Clear selection', 'woocommerce-composite-products' );
			?></a>
		</p><?php
	}
}

/**
 * Composited product details wrapper open.
 *
 * @param  WC_Product           $product
 * @param  string               $component_id
 * @param  WC_Product_Composite $composite
 * @return void
 */
function wc_cp_composited_product_details_wrapper_open( $product, $component_id, $composite ) {

	echo '<div class="composited_product_details_wrapper">';
}

/**
 * Composited product thimbnail template.
 *
 * @param  WC_Product           $product
 * @param  string               $component_id
 * @param  WC_Product_Composite $composite
 * @return void
 */
function wc_cp_composited_product_thumbnail( $product, $component_id, $composite ) {

	$component_data         = $composite->get_component_data( $component_id );
	$hide_product_thumbnail = isset( $component_data[ 'hide_product_thumbnail' ] ) ? $component_data[ 'hide_product_thumbnail' ] : 'no';

	if ( $hide_product_thumbnail !== 'yes' ) {
		wc_get_template( 'composited-product/image.php', array(
			'product_id' => $product->id
		), '', WC_CP()->plugin_path() . '/templates/' );
	}
}

/**
 * Composited product details template.
 *
 * @param  WC_Product           $product
 * @param  string               $component_id
 * @param  WC_Product_Composite $composite
 * @return void
 */
function wc_cp_composited_product_details( $product, $component_id, $composite ) {

	/**
	 * Action 'woocommerce_composite_show_composited_product_{$product_type}'.
	 * Composited product details template (type-specific).
	 *
	 * @param  WC_Product            $product
	 * @param  string                $component_id
	 * @param  WC_Product_Composite  $composite
	 *
	 * @hooked wc_cp_composited_product_details_variable - 10
	 * @hooked wc_cp_composited_product_details_simple   - 10
	 */
	do_action( 'woocommerce_composite_show_composited_product_' . $product->product_type, $product, $component_id, $composite );
}

/**
 * Composited product details wrapper close.
 *
 * @param  WC_Product           $product
 * @param  string               $component_id
 * @param  WC_Product_Composite $composite
 * @return void
 */
function wc_cp_composited_product_details_wrapper_close( $product, $component_id, $composite ) {

	echo '</div>';
}

/**
 * Composited Simple product details template.
 *
 * @param  WC_Product           $product
 * @param  string               $component_id
 * @param  WC_Product_Composite $composite
 * @return void
 */
function wc_cp_composited_product_details_simple( $product, $component_id, $composite ) {

	$component_data      = $composite->get_component_data( $component_id );
	$per_product_pricing = $composite->is_priced_per_product();
	$quantity_min        = $component_data[ 'quantity_min' ];
	$quantity_max        = $product->managing_stock() && ! $product->backorders_allowed() ? $product->get_stock_quantity() : '';

	// Max product quantity can't be greater than the component Max Quantity.
	if ( $component_data[ 'quantity_max' ] > 0 ) {
		$quantity_max = ( $quantity_max !== '' ) ? min( $component_data[ 'quantity_max' ], $quantity_max ) : $component_data[ 'quantity_max' ];
	}

	// Max product quantity can't be lower than the min product quantity - if it is, then the product is not in stock.
	if ( $quantity_max !== '' ) {
		if ( $quantity_min > $quantity_max ) {
			$quantity_max = $quantity_min;
		}
	}

	if ( $product->is_sold_individually() ) {
		$quantity_max = 1;
		$quantity_min = min( $quantity_min, 1 );
	}

	WC_CP_Helpers::extend_price_display_precision();

	$price_incl_tax = $product->get_price_including_tax( 1, 1000 );
	$price_excl_tax = $product->get_price_excluding_tax( 1, 1000 );
	$tax_ratio      = $price_incl_tax / $price_excl_tax;

	WC_CP_Helpers::reset_price_display_precision();

	/**
	 * Filter custom data passed to the template. Data added here can be accessed from the JS app.
	 *
	 * @param  array                  $custom_data
	 * @param  WC_Product             $displayed_product
	 * @param  string                 $component_id
	 * @param  array                  $component_data
	 * @param  WC_Product_Composite   $composite_product
	 */
	$custom_data = apply_filters( 'woocommerce_composited_product_custom_data', array( 'price_tax' => $tax_ratio ), $product, $component_id, $component_data, $composite );

	wc_get_template( 'composited-product/simple-product.php', array(
		'product'           => $product,
		'price'             => $product->get_price(),
		'regular_price'     => $product->get_regular_price(),
		'custom_data'       => $custom_data,
		'composite_id'      => $composite->id,
		'component_id'      => $component_id,
		'quantity_min'      => $quantity_min,
		'quantity_max'      => $quantity_max,
		'composite_product' => $composite
	), '', WC_CP()->plugin_path() . '/templates/' );
}

/**
 * Composited Variable product details template.
 *
 * @param  WC_Product           $product
 * @param  string               $component_id
 * @param  WC_Product_Composite $composite
 * @return void
 */
function wc_cp_composited_product_details_variable( $product, $component_id, $composite ) {

	$product_variations = $product->get_available_variations();

	if ( ! $product_variations ) {

		?><div class="component_data" data-component_set="false" data-price="0" data-regular_price="0" data-product_type="unavailable-product">
			<?php _e( 'This item is currently unavailable.', 'woocommerce-composite-products' ); ?>
		</div><?php

	} else {

		$component_data = $composite->get_component_data( $component_id );

		/** Documented in {@see wc_cp_composited_product_details_simple} */
		$custom_data = apply_filters( 'woocommerce_composited_product_custom_data', array( 'price_tax' => 1.0 ), $product, $component_id, $component_data, $composite );

		$attributes     = $product->get_variation_attributes();
		$attribute_keys = array_keys( $attributes );

		$quantity_min   = $component_data[ 'quantity_min' ];
		$quantity_max   = $component_data[ 'quantity_max' ];

		if ( $product->is_sold_individually() ) {
			$quantity_max = 1;
			$quantity_min = min( $quantity_min, 1 );
		}

		wc_get_template( 'composited-product/variable-product.php', array(
			'product'                  => $product,
			'product_variations'       => $product_variations,
			'attributes'               => $attributes,
			'attribute_keys'           => $attribute_keys,
			'custom_data'              => $custom_data,
			'component_id'             => $component_id,
			'quantity_min'             => $quantity_min,
			'quantity_max'             => $quantity_max,
			'composite_product'        => $composite
		), '', WC_CP()->plugin_path() . '/templates/' );
	}
}

/**
 * Composited product excerpt.
 *
 * @param  WC_Product           $product
 * @param  string               $component_id
 * @param  WC_Product_Composite $composite
 * @return void
 */
function wc_cp_composited_product_excerpt( $product, $component_id, $composite ) {

	$component_data           = $composite->get_component_data( $component_id );
	$hide_product_description = isset( $component_data[ 'hide_product_description' ] ) ? $component_data[ 'hide_product_description' ] : 'no';

	if ( $hide_product_description !== 'yes' ) {
		wc_get_template( 'composited-product/excerpt.php', array(
			'product_description' => $product->post->post_excerpt,
			'product_id'          => $product->id,
			'component_id'        => $component_id,
			'composite'           => $composite,
		), '', WC_CP()->plugin_path() . '/templates/' );
	}
}

/**
 * Composited Simple product price.
 *
 * @param  WC_Product           $product
 * @param  string               $component_id
 * @param  WC_Product_Composite $composite
 * @return void
 */
function wc_cp_composited_product_price( $product, $component_id, $composite ) {

	if ( $product->product_type === 'simple' ) {

		if ( $composite->is_priced_per_product() && $product->get_price() !== '' ) {
			wc_get_template( 'composited-product/price.php', array(
				'product' => $product
			), '', WC_CP()->plugin_path() . '/templates/' );
		}
	}
}

/**
 * Composited single variation template.
 *
 * @param  WC_Product_Variable  $product
 * @param  string               $component_id
 * @param  WC_Product_Composite $composite
 * @return void
 */
function wc_cp_composited_single_variation( $product, $component_id, $composite ) {

	$component_data = $composite->get_component_data( $component_id );
	$quantity_min   = $component_data[ 'quantity_min' ];
	$quantity_max   = $component_data[ 'quantity_max' ];

	if ( $product->is_sold_individually() ) {
		$quantity_max = 1;
		$quantity_min = min( $quantity_min, 1 );
	}

	wc_get_template( 'composited-product/variation.php', array(
		'quantity_min'      => $quantity_min,
		'quantity_max'      => $quantity_max,
		'component_id'      => $component_id,
		'product'           => $product,
		'composite_product' => $composite
	), '', WC_CP()->plugin_path() . '/templates/' );
}

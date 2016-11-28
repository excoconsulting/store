<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Woocommerce_Advanced_Product_Labels_Globals
 *
 * Handle the global product labels
 *
 * @class 		Woocommerce_Advanced_Product_Labels_Globals
 * @author		Jeroen Sormani
 * @package 	WooCommerce Advanced Product Labels
 * @version		1.0.0
 */
class WAPL_Global_Labels extends Woocommerce_Advanced_Product_Labels {


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Load some ajax in admin footer
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Ajax action to load without refresh
		add_action( 'wp_ajax_wapl_meta_boxes_conditions_ajax_values', array( $this, 'meta_boxes_conditions_ajax_values' ) );
		add_action( 'wp_ajax_wapl_meta_box_ajax_add_condition', array( $this, 'meta_box_ajax_add_condition' ) );
		add_action( 'wp_ajax_wapl_meta_box_ajax_add_group', array( $this, 'meta_box_ajax_add_group' ) );

		// Add labels
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'global_label_hook' ), 15 ); // executing global label function

	}


	/**
	 * Load javascript
	 *
	 * Load some javascript/ajax in admin footer
	 *
	 * @since 1.0.0
	 */
	public function admin_enqueue_scripts() {

		wp_enqueue_script( 'woocommerce-advanced-product-labels-conditions', plugins_url( '/assets/js/wapl-admin-conditions.js', __FILE__ ), array( 'jquery' ), $this->version, true );

	}


	/**
	 * Ajax update condition values.
	 *
	 * Update the condition values by Ajax.
	 *
	 * @since 1.0.0
	 */
	public function meta_boxes_conditions_ajax_values() {

		wapl_condition_values( $_POST['id'], $_POST['group'], $_POST['condition_key'] );

		// die() required at end of Ajax call
		die();

	}


	/**
	 * Add condition row.
	 *
	 * Add condition row by Ajax
	 *
	 * @since 1.0.0
	 */
	public function meta_box_ajax_add_condition() {

		new WAPL_Condition( null, $_POST['group'] );

		// die() required at end of Ajax call
		die();

	}


	/**
	 * Add condition group.
	 *
	 * Add condition group by Ajax.
	 *
	 * @since 1.0.0
	 */
	public function meta_box_ajax_add_group() {

		?><div class='conditions-group-wrap'>

			<div class='conditions-group' data-group='<?php echo $_POST['group']; ?>'>
				<p>
					<strong><?php _e( 'Or a product must match all these condititions to show the label:', 'woocommerce-advanced-product-labels' ); ?></strong>
				</p><?php

				new WAPL_Condition( null, $_POST['group'], 'product', null, null, null );

			?></div>

			<p>
				<strong><?php _e( 'Or', 'woocommerce-advanced-product-labels' ); ?></strong>
			</p>

		</div><?php

		// die() required at end Ajax call
		die();

	}


	/**
	 * Display labels.
	 *
	 * Hook into product loop to add the global product labels.
	 *
	 * @since 1.0.0
	 */
	public function global_label_hook() {

		// Stop if global labels are disabled
		if ( 'no' == get_option( 'enable_wapl', 'yes' ) ) :
			return;
		endif;

		global $product;
		$exclude = get_post_meta( $product->id, '_wapl_label_exclude', true );

		// Check if product is excluded from Global Labels
		if ( 'yes' == $exclude ) :
			return;
		endif;

		// Get all global labels
		$global_labels = get_posts( array( 'posts_per_page' => '-1', 'post_type' => 'wapl', 'order_by' => 'date', 'order' => 'asc' ) );

		// Loop through each global label
		foreach ( $global_labels as $global_label ) :

			// Retreive label data and conditions
			$conditions = get_post_meta( $global_label->ID, '_wapl_global_label', true );

			$one_condition_group_match = false; // None of the condition groups match yet
			foreach ( $conditions['conditions'] as $group_key => $group ) :

				$all_conditions_match = true;

				// Loop through all the conditions of that Global label
				foreach ( $group as $condition ) :

					// Apply the filter for this condition
					$match = apply_filters( 'wapl_match_conditions_' . $condition['condition'], false, $condition['operator'], $condition['value'] );

					// set $all_conditions_match to false when one match fails of this group
					if ( ! $match ) :
						$all_conditions_match = false;
					endif;

				endforeach;

				if ( $all_conditions_match ) :
					$one_condition_group_match = true;
				endif;

			endforeach;

			// if one of the condition groups match, echo the label
			if ( $one_condition_group_match ) :

				$label_custom_bg_color 		= isset( $conditions['label_custom_background_color'] ) ? $conditions['label_custom_background_color'] : '#D9534F';
				$label_custom_text_color 	= isset( $conditions['label_custom_text_color'] ) ? $conditions['label_custom_text_color'] : '#fff';
				$style_attr = isset( $conditions['style'] ) && 'custom' == $conditions['style'] ? "style='background-color: $label_custom_bg_color; color: $label_custom_text_color;'" : '';
				new Wapl_Label( $conditions['type'], $conditions['text'], $conditions['style'], $conditions['align'], $style_attr );
			endif;

		endforeach;

	}


}


/**
 * Load condition row object class.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/settings/class-wapl-condition.php';

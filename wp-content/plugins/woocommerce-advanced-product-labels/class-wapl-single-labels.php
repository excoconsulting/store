<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 *	Class Wapl_Single_label
 *
 *	WAPL signle label class, load single label config.
 *
 *	@class       Wapl_Single_label
 *	@version     1.0.0
 *	@author      Jeroen Sormani
 */
class WAPL_Single_Labels extends WooCommerce_Advanced_Product_Labels {


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Add the product tabs
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_product_label_tab' ) );
		add_action( 'woocommerce_product_write_panels', array( $this, 'product_label_tab_settings' ) );

		// Update meta from the above settings
		add_action( 'woocommerce_process_product_meta', array( $this, 'update_product_tab_settings' ) );

		// Hook in on te product title
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'product_label_template_hook' ), 15 );

	}


	/**
	 * Label products tab.
	 *
	 * Display 'Product labels' tab on edit product page.
	 *
	 * @since 1.0.0
	 *
	 * @param 	array $tabs Existing tabs.
	 * @return 	array 		Modified settings tabs, containing 'Product label'.
	 */
	public function add_product_label_tab( $tabs ) {

		$tabs['labels'] = array(
			'label'		=> __( 'Product label', 'woocommerce-advanced-product-labels' ),
			'target'	=> 'woocommerce_advanced_product_labels',
			'class'		=> array( 'woocommerce_advanced_product_labels' ),
		);

		return $tabs;

	}


	/**
	 * Settings in 'Product label' tab.
	 *
	 * Configure and display the settings in the 'Product data' meta box
	 *
	 * @since 1.0.0
	 */
	public function product_label_tab_settings() {

		$label = $this->get_label_data();

		?><div id='woocommerce_advanced_product_labels' class='panel woocommerce_options_panel'>

			<div class='options_group'><?php

				woocommerce_wp_checkbox( array(
					'id' 			=> '_wapl_label_exclude',
					'label' 		=> __( 'Exclude Global Labels', 'woocommerce-advanced-product-labels' ),
					'description'	=> __( 'When checked, global labels will be excluded', 'woocommerce-advanced-product-labels' ),
				) );

			?></div>

			<div class='options_group'>

				<div class='wapl-column' style='width: 50%;'><?php

					woocommerce_wp_select( array(
						'id' 			=> '_wapl_label_type',
						'label' 		=> __( 'Label type', 'woocommerce-advanced-product-labels' ),
						'desc_tip' 		=> true,
						'description'	=> __( '<strong>\'Flash\'</strong> is positioned on top of the product image<br/>
													<strong>\'Label\'</strong> is positioned above the product title', 'woocommerce-advanced-product-labels' ),
						'options' 		=> WooCommerce_Advanced_Product_Labels()->get_label_types(),
					) );

					woocommerce_wp_text_input( array(
						'id' 			=> '_wapl_label_text',
						'label' 		=> __( 'Label text', 'woocommerce-advanced-product-labels' ),
						'desc_tip' 		=> true,
						'description' 	=> __( 'What text do you want the label to show?', 'woocommerce-advanced-product-labels' ),
					) );

					woocommerce_wp_select( array(
						'id' 			=> '_wapl_label_style',
						'label' 		=> __( 'Label style', 'woocommerce-advanced-product-labels' ),
						'options' 		=> WooCommerce_Advanced_Product_Labels()->label_styles
					) );

					$label_custom_bg_color 		= isset( $label['custom_bg_color'] ) ? $label['custom_bg_color'] : '#D9534F';
					$label_custom_text_color 	= isset( $label['custom_text_color'] ) ? $label['custom_text_color'] : '#fff';

					?><p class='form-field _wapl_label_custom_bg_color_field wapl-custom-colors custom-colors <?php echo isset( $label['style'] ) && $label['style'] == 'custom' ? '' : 'hidden'; ?>'>
						<label for='wapl-custom-background'><?php _e( 'Background color', 'woocommerce-advanced-product-labels' ); ?></label>
						<input type='text' name='_wapl_custom_bg_color' value='<?php echo $label_custom_bg_color; ?>' id='wapl-custom-background' class='color-picker' />

						<label for='wapl-custom-text'><?php _e( 'Text color', 'woocommerce-advanced-product-labels' ); ?></label>
						<input type='text' name='_wapl_custom_text_color' value='<?php echo $label_custom_text_color; ?>' id='wapl-custom-text' class='color-picker' />
					</p><?php

					woocommerce_wp_select( array(
						'id' 		=> '_wapl_label_align',
						'label' 	=> __( 'Label align', 'woocommerce-advanced-product-labels' ),
						'options'	=> array(
							'none' 		=> __( 'None', 		'woocommerce-advanced-product-labels' ),
							'left' 		=> __( 'Left', 		'woocommerce-advanced-product-labels' ),
							'right' 	=> __( 'Right', 	'woocommerce-advanced-product-labels' ),
							'center' 	=> __( 'Center',	'woocommerce-advanced-product-labels' ),
							),
					) );

				?></div>

				<div class='wapl-column' style='width: 20%; margin-top: 20px; padding-left: 40px; border-left: 1px solid #ddd;'>

					<div id='wapl-label-preview'>
						<img width='150' height='150' title='' alt='' src='<?php echo apply_filters( 'wapl_preview_image_src', 'data:image/gif;base64,R0lGODdhlgCWAOMAAMzMzJaWlr6+vpycnLGxsaOjo8XFxbe3t6qqqgAAAAAAAAAAAAAAAAAAAAAAAAAAACwAAAAAlgCWAAAE/hDISau9OOvNu/9gKI5kaZ5oqq5s675wLM90bd94ru987//AoHBILBqPyKRyyWw6n9CodEqtWq/YrHbL7Xq/4LB4TC6bz+i0es1uu9/wuHxOr9vv+Lx+z+/7/4CBgoOEhYaHiImKi4yNjo+QkZKTlJWWl5iZmpucnZ6foHcCAwMTAaenBxMCBQEFBiajpRKoqautr2cEp7MApwjAAhIGA64BvSK7x6YBwAjCAMTGyGK7rb3LFbsEAAgBqsnTptQA293fZQaq2b7krbACzSPq7eMW7wDxCGjsxwTPE4oNc2XhlIB4ATT0G/APGgCB0Qie6VcL2kIL3oDJy0ARlUVsz+TEsEPw6sDGi/dIFdgwsuRJkPxCZkNZAaFDDOwozIQ5MSREiAYkVggaAJZCnwkfJg26sucEcEol4NN3QRm3o08DJp260Uw2k9yYSjDnDarOAgVC6pwFNmJTsujKoD3VtFjauNKuXWh1wGSBffdaSbRbDFzenGNqLb12VcIoV0YrnKI1uWCtYYwpPM4VqrPnz6BDix5NurTp06hTq17NurXr17Bjy55Nu7bt27hz697Nu7fv38CDCx9OvLjx48iTK1/OvLnz59CjS59OvfqLCAA7' ); ?>' /><?php

						new WAPL_Label( $label['type'], $label['text'], $label['style'], $label['align'], $label['style_attr'] );

						?><p><strong>Product name</strong></p>
					</div>
				</div>
			</div>

		</div><?php

	}


	/**
	 * Update single product label
	 *
	 * @since 1.0.0
	 */
	public function update_product_tab_settings() {

		global $post;

		// Save each field in seperate post meta, needed for WC
		$meta_keys = array(
			'_wapl_label_type',
			'_wapl_label_text',
			'_wapl_label_style',
			'_wapl_label_align',
			'_wapl_custom_bg_color',
			'_wapl_custom_text_color',
		);

		foreach ( $meta_keys as $meta ) :

			if ( isset( $_POST[ $meta ] ) ) :
				update_post_meta( $post->ID, $meta, sanitize_text_field( $_POST[ $meta ] ) );
			endif;

		endforeach;

		if ( isset( $_POST['_wapl_label_exclude'] ) ) :
			update_post_meta( $post->ID, '_wapl_label_exclude', 'yes' );
		else :
			update_post_meta( $post->ID, '_wapl_label_exclude', 'no' );
		endif;

	}


	/**
	 * Hook label in product loop.
	 *
	 * Echo's the product label @hook 'woocommerce_before_shop_loop_item_title'.
	 *
	 * @since 1.0.0
	 */
	public function product_label_template_hook() {

		$label = $this->get_label_data();

		if ( $label ) :
			// Echo product label
			new WAPL_Label( $label['type'], $label['text'], $label['style'], $label['align'], $label['style_attr'] );
		endif;

	}


	/**
	 * Return label data.
	 *
	 * @since 1.0.0
	 *
	 * @param int $product_id
	 * @return array
	 */
	public function get_label_data( $product_id = null ) {

		global $post;

		$data_defaults = array(
			'exclude'	 	=> 'no',
			'type' 			=> 'label',
			'text' 			=> '',
			'style' 		=> '',
			'align' 		=> '',
			'style_attr' 	=> '',
		);

		if ( ! $product_id ) :
			$product_id = $post->ID;
		endif;

		$data = wp_parse_args( array(
			'exclude' 			=> get_post_meta( $post->ID, '_wapl_label_exclude', true ),
			'type' 				=> get_post_meta( $post->ID, '_wapl_label_type', true ),
			'text' 				=> get_post_meta( $post->ID, '_wapl_label_text', true ),
			'style' 			=> get_post_meta( $post->ID, '_wapl_label_style', true ),
			'align' 			=> get_post_meta( $post->ID, '_wapl_label_align', true ),
			'custom_bg_color' 	=> get_post_meta( $post->ID, '_wapl_custom_bg_color', true ),
			'custom_text_color' => get_post_meta( $post->ID, '_wapl_custom_text_color', true ),
		), $data_defaults );

		$custom_bg_color 	= ! empty( $data['custom_bg_color'] ) ? $data['custom_bg_color'] : '#D9534F';
		$custom_text_color 	= ! empty( $data['custom_text_color'] ) ? $data['custom_text_color'] : '#fff';
		$data['style_attr'] = ! empty( $data['style'] ) && 'custom' == $data['style'] ? "style='background-color: {$custom_bg_color}; color: {$custom_text_color};'" : '';

		return $data;

	}


}

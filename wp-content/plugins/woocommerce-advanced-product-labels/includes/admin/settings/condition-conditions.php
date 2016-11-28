<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Condition key dropdown
 *
 * Display condition key dropdown of a conditions row
 *
 * @author     	Jeroen Sormani
 * @package 	WooCommerce Advanced Product Labels
 * @version    	1.0.0
 */
function wapl_condition_conditions( $id, $group = 0, $condition = 'product' ) {

	$conditions = array(
		__( 'Conditions', 'woocommerce-advanced-product-labels' ) => array(
			'product' 			=> __( 'Product', 'woocommerce-advanced-product-labels' ),
			'category' 			=> __( 'Product category', 'woocommerce-advanced-product-labels' ),
			'product_type' 		=> __( 'Product type', 'woocommerce-advanced-product-labels' ),
			'in_sale' 			=> __( 'In sale', 'woocommerce-advanced-product-labels' ),
			'bestseller'		=> __( 'Bestsellers', 'woocommerce-advanced-product-labels' ),
			'age'				=> __( 'Product age', 'woocommerce-advanced-product-labels' ),
		),
		__( 'Attributes', 'woocommerce-advanced-product-labels' ) => array(
			'price' 			=> __( 'Price', 'woocommerce-advanced-product-labels' ),
			'sale_price' 		=> __( 'Sale price', 'woocommerce-advanced-product-labels' ),
			'stock_status' 		=> __( 'Stock status', 'woocommerce-advanced-product-labels' ),
			'stock_quantity'	=> __( 'Stock quantity', 'woocommerce-advanced-product-labels' ),
			'shipping_class' 	=> __( 'Shipping class', 'woocommerce-advanced-product-labels' ),
			'tag' 				=> __( 'Tag', 'woocommerce-advanced-product-labels' ),
			'sales' 			=> __( 'Total sales', 'woocommerce-advanced-product-labels' ),
			'featured' 			=> __( 'Featured product', 'woocommerce-advanced-product-labels' ),
		),
	);

	$conditions = apply_filters( 'wapl_conditions', $conditions );


	?><span id='wapl_conditions'>

		<select class='wapl-condition select wapl-select wapl-select-condition' data-group='<?php echo $group; ?>' data-id='<?php echo $id; ?>'
			name='_wapl_global_label[conditions][<?php echo $group; ?>][<?php echo $id; ?>][condition]'><?php

			foreach ( $conditions as $key => $value ) :

				?><optgroup label='<?php echo $key; ?>'><?php

					foreach ( $value as $key => $value ) :
						?><option value='<?php echo $key; ?>' <?php selected( $condition, $key ); ?>><?php echo $value; ?></option><?php
					endforeach;

				?></optgroup><?php

			endforeach;

		?></select>

	</span><?php

}

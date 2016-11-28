<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Condition value dropdown
 *
 * Display condition value dropdown of a conditions row
 *
 * @author     	Jeroen Sormani
 * @package 	WooCommerce Advanced Product Labels
 * @version    	1.0.0
 */
function wapl_condition_values( $id, $group = 0, $condition = 'product', $current_value = '' ) {

	// Defaults
	$values = array( 'placeholder' => '', 'min' => '', 'max' => '', 'field' => 'text', 'options' => array() );

	switch( $condition ) :

		default:
		case 'product':

			$values['field'] = 'select';

			$products = get_posts( array( 'posts_per_page' => '-1', 'post_type' => 'product' ) );
			foreach ( $products as $product ) :
				$values['options'][$product->ID ] = $product->post_title;
			endforeach;

		break;

		case 'category':

			$values['field'] = 'select';

			$categories = get_terms( 'product_cat', array( 'hide_empty' => false ) );
			foreach ( $categories as $category ) :
				$values['options'][ $category->slug ] = $category->name;
			endforeach;

		break;

		case 'product_type':

			$values['field'] = 'select';

			$productTypes = get_terms( 'product_type', array( 'hide_empty' => false ) );
			foreach ( $productTypes as $type ) :
				$values['options'][ $type->slug ] = $type->name;
			endforeach;

		break;

		case 'in_sale':

			$values['field']	= 'select';
			$values['options'] 	= array(
				'1'	=> __( 'Yes', 'woocommerce-advanced-product-labels' ),
				'0'	=> __( 'No', 'woocommerce-advanced-product-labels' ),
			);

		break;

		case 'bestseller':
			$values['field']		= 'text';
			$values['placeholder']	= __( 'Top * of bestsellers', 'woocommerce-advanced-product-labels' );
		break;

		case 'age':
			$values['field']		= 'text';
			$values['placeholder']	= __( 'Product age in days or date', 'woocommerce-advanced-product-labels' );
		break;

		case 'price':

			$values['field'] 		= 'text';
			$values['placeholder']	= __( 'Price', 'woocommerce-advanced-product-labels' );

		break;

		case 'sale_price' :

			$values['field'] 		= 'text';
			$values['placeholder']	= __( 'Sale price', 'woocommerce-advanced-product-labels' );

		break;

		case 'stock_status':

			$values['field']	= 'select';
			$values['options']	= array(
				'1' => __( 'In stock', 'woocommerce-advanced-product-labels' ),
				'0' => __( 'Out of stock', 'woocommerce-advanced-product-labels' ),
			);

		break;

		case 'stock_quantity':

			$values['field']		= 'text';
			$values['placeholder'] 	= __( 'Stock quantity', 'woocommerce-advanced-product-labels' );

		break;

		case 'shipping_class':

			$values['field'] = 'select';

			$shipping_classes = get_terms( 'product_shipping_class', array( 'hide_empty' => false ) );

			foreach ( $shipping_classes as $shipping_class ) :
				$values['options'][ $shipping_class->slug ] = $shipping_class->name;
			endforeach;

		break;

		case 'tag':

			$values['field'] = 'select';

			$tags = get_terms( 'product_tag', array( 'hide_empty' => false ) );
			foreach ( $tags as $tag ) :
				$values['options'][ $tag->slug ] = $tag->name;
			endforeach;

		break;

		case 'sales':

			$values['field']		= 'text';
			$values['placeholder'] 	= __( 'Total sales', 'woocommerce-advanced-product-labels' );

		break;

		case 'featured':

			$values['field']		= 'select';
			$values['options'] 	= array(
				'1' => __( 'Yes', 'woocommerce-advanced-product-labels' ),
				'0' => __( 'No', 'woocommerce-advanced-product-labels' ),
			);

		break;

	endswitch;

	$values = apply_filters( 'wapl_condition_values', $values );

	?><span id='value_<?php echo $id; ?>_wrap' data-value='<?php echo $id; ?>'><?php

		switch( $values['field'] ) :

			case 'text' :

				?><input
					type='text'
					name='_wapl_global_label[conditions][<?php echo $group; ?>][<?php echo $id; ?>][value]'
					value='<?php echo $current_value; ?>'
					placeholder='<?php echo $values['placeholder']; ?>'
					class='input input-wapl input-wapl-value wapl-value'
					data-value='<?php echo $id; ?>' data-type='value'><?php

			break;

			case 'select' :

				?><select name='_wapl_global_label[conditions][<?php echo $group; ?>][<?php echo $id; ?>][value]' id='value_<?php echo $id; ?>' class='wapl-select wapl-select-value wapl-value'><?php

				foreach ( $values['options'] as $key => $value ) :

					if ( ! is_array( $value ) ) :
						?><option value='<?php echo $key; ?>' <?php selected( $key, $current_value ); ?>><?php echo $value; ?></option><?php
					else :
						?><optgroup label='<?php echo $key ?>'><?php
							foreach ( $value as $k => $v ) :
								?><option value='<?php echo $k; ?>' <?php selected( $k, $current_value ); ?>><?php echo $v; ?></option><?php
							endforeach;
						?></optgroup><?php

					endif;

				endforeach;

				if ( empty( $values['options'] ) ) :
					?><option readonly value=''><?php
						_e( 'There are no options available', 'woocommerce-advanced-product-labels' );
					?></option><?php
				endif;

				?></select><?php

			break;

			default :
				do_action( 'wapl_condition_value_field_type_' . $values['field'], $values );
			break;

		endswitch;

	?></span><?php

}

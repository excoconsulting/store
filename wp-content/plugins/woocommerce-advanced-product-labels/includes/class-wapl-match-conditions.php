<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 *	Class Wapl_Conditions
 *
 *	The Labels Conditions class handles the matching rules for labels
 *
 *	@class      Wapl_Conditions
 *	@author     Jeroen Sormani
 *	@package 	WooCommerce Advanced Product Labels
 *	@version    1.0.0
 */
class Wapl_Match_Conditions extends Woocommerce_Advanced_Product_Labels {


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_filter( 'wapl_match_conditions_product', 		array( $this, 'condition_match_product' ), 10, 3 );
		add_filter( 'wapl_match_conditions_category', 		array( $this, 'condition_match_product_category' ), 10, 3 );
		add_filter( 'wapl_match_conditions_product_type', 	array( $this, 'condition_match_product_type' ), 10, 3 );
		add_filter( 'wapl_match_conditions_in_sale', 		array( $this, 'condition_match_in_sale' ), 10, 3 );
		add_filter( 'wapl_match_conditions_bestseller', 	array( $this, 'condition_match_bestseller' ), 10, 3 );
		add_filter( 'wapl_match_conditions_age', 			array( $this, 'condition_match_age' ), 10, 3 );

		add_filter( 'wapl_match_conditions_price',			array( $this, 'condition_match_price' ), 10, 3 );
		add_filter( 'wapl_match_conditions_sale_price', 	array( $this, 'condition_match_sale_price' ), 10, 3 );
		add_filter( 'wapl_match_conditions_stock_status', 	array( $this, 'condition_match_stock_status' ), 10, 3 );
		add_filter( 'wapl_match_conditions_stock_quantity', array( $this, 'condition_match_stock_quantity' ), 10, 3 );
		add_filter( 'wapl_match_conditions_shipping_class', array( $this, 'condition_match_shipping_class' ), 10, 3 );
		add_filter( 'wapl_match_conditions_tag', 			array( $this, 'condition_match_tag' ), 10, 3 );
		add_filter( 'wapl_match_conditions_sales', 			array( $this, 'condition_match_sales' ), 10, 3 );
		add_filter( 'wapl_match_conditions_featured', 		array( $this, 'condition_match_featured' ), 10, 3 );


	}


	/**
	 * Match product.
	 *
	 * Match the condition value against the product.
	 *
	 * @since 1.0.0
	 *
	 * @param 	bool 	$match		Current match value.
	 * @param 	string 	$operator	Operator selected by the user in the condition row.
	 * @param 	mixed 	$value		Value given by the user in the condition row.
	 * @return 	BOOL 				Matching result, TRUE if results match, otherwise FALSE.
	 */
	public function condition_match_product( $match, $operator, $value ) {

		global $product, $post;

		if ( $operator == '==' ) :
			$match = ( $post->ID == $value );
		elseif ( $operator == '!=' ) :
			$match = ( $post->ID != $value );
		else :
			$match = false;
		endif;

		return $match;

	}


	/**
	 * Match product category.
	 *
	 * Match the condition value against the product category.
	 *
	 * @since 1.0.0
	 *
	 * @param 	bool 	$match		Current match value.
	 * @param 	string 	$operator	Operator selected by the user in the condition row.
	 * @param 	mixed 	$value		Value given by the user in the condition row.
	 * @return 	BOOL 				Matching result, TRUE if results match, otherwise FALSE.
	 */
	public function condition_match_product_category( $match, $operator, $value ) {

		global $product, $post;

		if ( $operator == '==' ) :
			$match = ( has_term( $value, 'product_cat', $post->ID ) );
		elseif ( $operator == '!=' ) :
			$match = ( !has_term( $value, 'product_cat', $post->ID ) );
		else :
			$match = false;
		endif;

		return $match;

	}


	/**
	 * Match product type.
	 *
	 * Match the condition value against the product type.
	 *
	 * @since 1.0.0
	 *
	 * @param 	bool 	$match		Current match value.
	 * @param 	string 	$operator	Operator selected by the user in the condition row.
	 * @param 	mixed 	$value		Value given by the user in the condition row.
	 * @return 	BOOL 				Matching result, TRUE if results match, otherwise FALSE.
	 */
	public function condition_match_product_type( $match, $operator, $value ) {

		global $product;

		if ( $operator == '==' ) :
			$match = ( $product->product_type == $value );
		elseif ( $operator == '!=' ) :
			$match = ( $product->product_type != $value );
		else :
			$match = false;
		endif;

		return $match;

	}


	/**
	 * Match in sale.
	 *
	 * Match the condition value against the sale status of the product.
	 *
	 * @since 1.0.0
	 *
	 * @param 	bool 	$match		Current match value.
	 * @param 	string 	$operator	Operator selected by the user in the condition row.
	 * @param 	mixed 	$value		Value given by the user in the condition row.
	 * @return 	BOOL 				Matching result, TRUE if results match, otherwise FALSE.
	 */
	public function condition_match_in_sale( $match, $operator, $value ) {

		global $product;

		if ( $operator == '==' ) :
			$match = ( $product->is_on_sale() == $value );
		elseif ( $operator == '!=' ) :
			$match = ( $product->is_on_sale() != $value );
		else :
			$match = false;
		endif;

		return $match;

	}


	/**
	 * Match bestseller.
	 *
	 * Match the condition value against the top x bestsellers.
	 *
	 * @since 1.0.0
	 *
	 * @param 	bool 	$match		Current match value.
	 * @param 	string 	$operator	Operator selected by the user in the condition row.
	 * @param 	mixed 	$value		Value given by the user in the condition row.
	 * @return 	BOOL 				Matching result, TRUE if results match, otherwise FALSE.
	 */
	public function condition_match_bestseller( $match, $operator, $value ) {

		global $product;

		$args = array(
			'post_type' 			=> 'product',
			'post_status' 			=> 'publish',
			'ignore_sticky_posts'   => 1,
			'posts_per_page'		=> $value,
			'meta_key' 		 		=> 'total_sales',
			'orderby' 		 		=> 'meta_value_num',
			'meta_query' 			=> array(
				array(
					'key' 		=> '_visibility',
					'value' 	=> array( 'catalog', 'visible' ),
					'compare' 	=> 'IN'
				)
			)
		);

		$bestsellers = get_posts( $args ); // Get bestsellers

		foreach ( $bestsellers as $bestseller ) : // Get array of bestseller IDs
			$bestseller_ids[] = $bestseller->ID;
		endforeach;

		if ( in_array( $product->id, (array) $bestseller_ids ) ) : // Match bestseller IDs to product ID
			$match = true;
		else :
			$match = false;
		endif;

		return $match;

	}


	/**
	 * Match product age.
	 *
	 * Match the condition value against product age.
	 *
	 * @since 1.0.0
	 *
	 * @param 	bool 	$match		Current match value.
	 * @param 	string 	$operator	Operator selected by the user in the condition row.
	 * @param 	mixed 	$value		Value given by the user in the condition row.
	 * @return 	BOOL 				Matching result, TRUE if results match, otherwise FALSE.
	 */
	public function condition_match_age( $match, $operator, $value ) {

		global $post;

		// Check if its a date, when false substract the number of days
		if ( date( 'Y-m-d', strtotime( $value ) ) > 1970 ) :

			if ( $operator == '==' ) :
				$match = ( date( 'Y-m-d', strtotime( $post->post_date ) ) ==  date( 'Y-m-d', strtotime( $value ) ) );
			elseif ( $operator == '!=' ) :
				$match = ( date( 'Y-m-d', strtotime( $post->post_date ) ) !=  date( 'Y-m-d', strtotime( $value ) ) );
			elseif ( $operator == '>=' ) :
				$match = ( date( 'Y-m-d', strtotime( $post->post_date ) ) >=  date( 'Y-m-d', strtotime( $value ) ) );
			elseif ( $operator == '<=' ) :
				$match = ( date( 'Y-m-d', strtotime( $post->post_date ) ) <=  date( 'Y-m-d', strtotime( $value ) ) );
			else :
				$match = false;
			endif;

		else :

			if ( $operator == '==' ) :
				$match = ( date( 'Y-m-d', strtotime( $post->post_date ) ) ==  date( 'Y-m-d', strtotime( "-$value days", time() ) ) );
			elseif ( $operator == '!=' ) :
				$match = ( date( 'Y-m-d', strtotime( $post->post_date ) ) !=  date( 'Y-m-d', strtotime( "-$value days", time() ) ));
			elseif ( $operator == '>=' ) :
				$match = ( date( 'Y-m-d', strtotime( $post->post_date ) ) <=  date( 'Y-m-d', strtotime( "-$value days", time() ) ) );
			elseif ( $operator == '<=' ) :
				$match = ( date( 'Y-m-d', strtotime( $post->post_date ) ) >=  date( 'Y-m-d', strtotime( "-$value days", time() ) ) );
			else :
				$match = false;
			endif;

		endif;

		return $match;

	}


	/**
	 * Match price.
	 *
	 * Match the condition value against product price.
	 *
	 * @since 1.0.0
	 *
	 * @param 	bool 	$match		Current match value.
	 * @param 	string 	$operator	Operator selected by the user in the condition row.
	 * @param 	mixed 	$value		Value given by the user in the condition row.
	 * @return 	BOOL 				Matching result, TRUE if results match, otherwise FALSE.
	 */
	public function condition_match_price( $match, $operator, $value ) {

		global $product;

		if ( $operator == '==' ) :
			$match = ( $product->get_price() == $value );
		elseif ( $operator == '!=' ) :
			$match = ( $product->get_price() != $value );
		elseif ( $operator == '>=' ) :
			$match = ( $product->get_price() >= $value );
		elseif ( $operator == '<=' ) :
			$match = ( $product->get_price() <= $value );
		else :
			$match = false;
		endif;

		return $match;

	}


	/**
	 * Match sale price.
	 *
	 * Match the condition value against product sale price.
	 *
	 * @since 1.0.0
	 *
	 * @param 	bool 	$match		Current match value.
	 * @param 	string 	$operator	Operator selected by the user in the condition row.
	 * @param 	mixed 	$value		Value given by the user in the condition row.
	 * @return 	BOOL 				Matching result, TRUE if results match, otherwise FALSE.
	 */
	public function condition_match_sale_price( $match, $operator, $value ) {

		global $product;

		if ( $operator == '==' ) :
			$match = ( $product->get_sale_price() == $value );
		elseif ( $operator == '!=' ) :
			$match = ( $product->get_sale_price() != $value );
		elseif ( $operator == '>=' ) :
			$match = ( $product->get_sale_price() >= $value );
		elseif ( $operator == '<=' ) :
			$match = ( $product->get_sale_price() <= $value );
		else :
			$match = false;
		endif;

		return $match;

	}


	/**
	 * Match stock status.
	 *
	 * Match the condition value against product stock status.
	 *
	 * @since 1.0.0
	 *
	 * @param 	bool 	$match		Current match value.
	 * @param 	string 	$operator	Operator selected by the user in the condition row.
	 * @param 	mixed 	$value		Value given by the user in the condition row.
	 * @return 	BOOL 				Matching result, TRUE if results match, otherwise FALSE.
	 */
	public function condition_match_stock_status( $match, $operator, $value ) {

		global $product;

		if ( $operator == '==' ) :
			$match = ( $product->is_in_stock() == $value );
		elseif ( $operator == '!=' ) :
			$match = ( $product->is_in_stock() != $value );
		else :
			$match = false;
		endif;

		return $match;

	}


	/**
	 * Match stock quantity.
	 *
	 * Match the condition value against product stock quantity.
	 *
	 * @since 1.0.0
	 *
	 * @param 	bool 	$match		Current match value.
	 * @param 	string 	$operator	Operator selected by the user in the condition row.
	 * @param 	mixed 	$value		Value given by the user in the condition row.
	 * @return 	BOOL 				Matching result, TRUE if results match, otherwise FALSE.
	 */
	public function condition_match_stock_quantity( $match, $operator, $value ) {

		global $product;

		if ( $operator == '==' ) :
			$match = ( $product->get_stock_quantity() == $value );
		elseif ( $operator == '!=' ) :
			$match = ( $product->get_stock_quantity() != $value );
		elseif ( $operator == '>=' ) :
			$match = ( $product->get_stock_quantity() >= $value );
		elseif ( $operator == '<=' ) :
			$match = ( $product->get_stock_quantity() <= $value );
		else :
			$match = false;
		endif;

		return $match;

	}


	/**
	 * Match shipping class.
	 *
	 * Match the condition value against product shipping class.
	 *
	 * @since 1.0.0
	 *
	 * @param 	bool 	$match		Current match value.
	 * @param 	string 	$operator	Operator selected by the user in the condition row.
	 * @param 	mixed 	$value		Value given by the user in the condition row.
	 * @return 	BOOL 				Matching result, TRUE if results match, otherwise FALSE.
	 */
	public function condition_match_shipping_class( $match, $operator, $value ) {

		global $product;

		if ( $operator == '==' ) :
			$match = ( $product->get_shipping_class() == $value );
		elseif ( $operator == '!=' ) :
			$match = ( $product->get_shipping_class() != $value );
		else :
			$match = false;
		endif;

		return $match;

	}


	/**
	 * Match tag.
	 *
	 * Match the condition value against product tags.
	 *
	 * @since 1.0.0
	 *
	 * @param 	bool 	$match		Current match value.
	 * @param 	string 	$operator	Operator selected by the user in the condition row.
	 * @param 	mixed 	$value		Value given by the user in the condition row.
	 * @return 	BOOL 				Matching result, TRUE if results match, otherwise FALSE.
	 */
	public function condition_match_tag( $match, $operator, $value ) {

		global $product, $post;

		if ( $operator == '==' ) :
			$match = ( has_term( $value, 'product_tag', $post->ID ) );
		elseif ( $operator == '!=' ) :
			$match = ( ! has_term( $value, 'product_tag', $post->ID ) );
		else :
			$match = false;
		endif;

		return $match;

	}


	/**
	 * Match total sales.
	 *
	 * Match the condition value against product total sales.
	 *
	 * @since 1.0.0
	 *
	 * @param 	bool 	$match		Current match value.
	 * @param 	string 	$operator	Operator selected by the user in the condition row.
	 * @param 	mixed 	$value		Value given by the user in the condition row.
	 * @return 	BOOL 				Matching result, TRUE if results match, otherwise FALSE.
	 */
	public function condition_match_sales( $match, $operator, $value ) {

		global $product;

		if ( $operator == '==' ) :
			$match = ( get_post_meta( $product->id, 'total_sales', true ) == $value );
		elseif ( $operator == '!=' ) :
			$match = ( get_post_meta( $product->id, 'total_sales', true ) != $value );
		elseif ( $operator == '>=' ) :
			$match = ( get_post_meta( $product->id, 'total_sales', true ) >= $value );
		elseif ( $operator == '<=' ) :
			$match = ( get_post_meta( $product->id, 'total_sales', true ) <= $value );
		else :
			$match = false;
		endif;

		return $match;

	}


	/**
	 * Match featured.
	 *
	 * Match the condition value against featured product.
	 *
	 * @since 1.0.0
	 *
	 * @param 	bool 	$match		Current match value.
	 * @param 	string 	$operator	Operator selected by the user in the condition row.
	 * @param 	mixed 	$value		Value given by the user in the condition row.
	 * @return 	BOOL 				Matching result, TRUE if results match, otherwise FALSE.
	 */
	public function condition_match_featured( $match, $operator, $value ) {

		global $post;

		$args = array(
			'fields'				=> 'ids',
			'post_type'				=> 'product',
			'post_status' 			=> 'publish',
			'posts_per_page' 		=> -1,
			'orderby' 				=> 'date',
			'order' 				=> 'DESC',
			'meta_query'			=> array(
				array(
					'key' 		=> '_featured',
					'value' 	=> 'yes'
				)
			)
		);
		$featured_products = get_posts( $args ); // Get featured products

		if ( $operator == '==' ) :
			$match = ( in_array( $post->ID, $featured_products ) );
		elseif ( $operator == '!=') :
			$match = ( ! in_array( $post->ID, $featured_products ) );
		else :
			$match = false;
		endif;

		return $match;

	}

}

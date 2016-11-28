<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class WAPL_Label
 *
 * Create product label object
 *
 * @class		WAPL_Label
 * @author		Jeroen Sormani
 * @package		WooCommerce Advanced Product Labels
 * @version		1.0.0
 */
class WAPL_Label {


	public $label_type;
	public $label_text;
	public $label_style;
	public $label_align;
	public $style_attr;


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $label_type (label/flash)
	 * @param string $label_text
	 * @param string $label_style
	 * @param string $label_align (none/left/center/right)
	 */
	public function __construct( $label_type = 'label', $label_text = '', $label_style = '', $label_align = '', $style_attr = '' ) {

		$this->label_type 	= $label_type;
		$this->label_text 	= $label_text;
		$this->label_style 	= $label_style;
		$this->label_align	= $label_align;
		$this->style_attr	= $style_attr;

		$this->hooks();

		$this->get_formatted_product_label();

	}


	/**
	 * Label hooks.
	 *
	 * Add filter hooks to the label.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {
		add_filter( 'wapl_product_label', array( $this, 'smart_product_label_filter' ) );
	}


	/**
	 * HTML label format.
	 *
	 * Echo the HTML format of the label
	 *
	 * @since 1.0.0
	 *
	 * @hooked by smart_product_label_filter()
	 * @used product_label_filter(), global_label_hook()
	 */
	public function get_formatted_product_label() {

		ob_start();
			?><div class="label-wrap wapl-<?php echo $this->label_type; ?> label-<?php echo $this->label_style; ?> wapl-align<?php echo $this->label_align; ?>">
				<span class="woocommerce-advanced-product-label product-label label-<?php echo $this->label_style; ?>" <?php echo $this->style_attr; ?>><?php echo $this->label_text; ?></span>
			</div><?php

			$label = ob_get_contents();
		ob_end_clean();

		// echo the product label
		echo apply_filters( 'wapl_product_label', $label );

	}


	/**
	 * SMART labels.
	 *
	 * Add filter to convert SMART labels.
	 *
	 * @since 1.0.0
	 *
	 * @param 	string $label 	Label text value.
	 * @return 	string 			Modified label text value.
	 */
	public function smart_product_label_filter( $label ) {

		global $product;

		if ( ! $product ) :
			$product_posts = get_posts( array( 'post_type' => 'product', 'posts_per_page' => 1 ) );
			$product = reset( $product_posts );
		endif;

		$product = wc_get_product( $product );

		if ( ! $product->is_type( 'variable' ) ) :

			$regular_price 	= $product->get_regular_price();
			$sale_price 	= $product->get_sale_price();

		else : // Get the right variable percentage

			if ( ( $product->get_variation_sale_price( 'min' ) / $product->get_variation_regular_price( 'min' ) < ( $product->get_variation_sale_price( 'max' ) / $product->get_variation_regular_price( 'max' ) ) ) ) :
				$regular_price = $product->get_variation_regular_price( 'min' );
				$sale_price = $product->get_variation_sale_price( 'min' );
			else :
				$regular_price = $product->get_variation_regular_price( 'max' );
				$sale_price = $product->get_variation_sale_price( 'max' );
			endif;

		endif;

		$percentage = null;
		if ( ! empty( $regular_price ) && $regular_price != 0 ) :
			$percentage = ( $regular_price - $sale_price ) / $regular_price * 100;
		endif;

		$label = str_replace( '{percentage}', round( $percentage, apply_filters( 'wapl_filter_discount_round', 1 ) ) . '%', $label );
		$label = str_replace( '{discount}', sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), ($regular_price-$sale_price) ), $label );
		$label = str_replace( '{price}', sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), $regular_price ), $label );
		$label = str_replace( '{saleprice}', sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), $sale_price ), $label );
		$label = str_replace( '{delprice}', '<del>'.sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), $regular_price ) . '</del>', $label );

		return $label;

	}


}

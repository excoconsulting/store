<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Min_Max_Quantities_Addons {

	/**
	 * Checks if product is of type "composite"
	 *
	 * @access public
	 * @since 2.3.9
	 * @version 2.3.9
	 * @return
	 */
	public function is_composite_product( $product_id ) {
		if ( empty( $product_id ) ) {
			return false;
		}

		$product = wc_get_product( $product_id );

		if ( 'composite' === $product->product_type ) {
			return true;
		}

		return false;
	}
}

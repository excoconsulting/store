<?php
/**
 * Composited Product Title.
 *
 * Override this template by copying it to 'yourtheme/woocommerce/composited-product/title.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version  3.1.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<h4 class="composited_product_title product_title"><?php
	echo WC_CP_Product::get_title_string( apply_filters( 'woocommerce_composited_product_title', $title, $product_id, $component_id, $composite ), $quantity );
?></h4>

<?php
/**
 * Component Title Template.
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/component-title.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version  3.0.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<h2 class="component_title product_title"><?php

	echo $title;

	if ( isset( $toggled ) && $toggled ) {
		?><span class="toggle_component_wrapper">
			<a class="toggle_component" href="#">
				<span class="toggle_component_text"><?php
					echo __( 'Toggle', 'woocommerce-composite-products' );
				?></span>
			</a>
		</span><?php
	}

?></h2>

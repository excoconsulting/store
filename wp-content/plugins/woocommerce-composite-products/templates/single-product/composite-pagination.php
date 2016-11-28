<?php
/**
 * Composite pagination template.
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/composite-pagination.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version 3.0.0
 * @since   2.5.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><div id="composite_pagination_<?php echo $product->id; ?>" class="composite_pagination">
	<ul class="pagination_elements" style="list-style:none"><?php

		$loop = 1;

		foreach ( $components as $component_id => $component_data ) {

			$title = apply_filters( 'woocommerce_composite_component_title', $component_data[ 'title' ], $component_id, $product->id );

			?><li class="pagination_element pagination_element_<?php echo $component_id; ?>" data-item_id="<?php echo $component_id; ?>">
				<span class="element_index"><?php
					echo apply_filters( 'woocommerce_composite_component_step_index', $loop, count( $components ), $product );
				?></span>
				<span class="element_title">
					<a class="element_link inactive" href="#"><?php
						echo $title;
					?></a>
				</span>
			</li><?php

			$loop++;
		}

		?><li class="pagination_element pagination_element_review" data-item_id="review">
			<span class="element_index"><?php
				echo apply_filters( 'woocommerce_composite_component_step_index', $loop, count( $components ), $product );
			?></span>
			<span class="element_title">
				<a class="element_link inactive" href="#"><?php
					echo __( 'Review Configuration', 'woocommerce-composite-products' );
				?></a>
			</span>
		</li>
	</ul>
</div>

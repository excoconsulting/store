<?php
/**
 * Composite paged mode Summary template content.
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/composite-summary-content.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version 3.6.0
 * @since   3.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><ul class="summary_elements cp_clearfix" style="list-style:none"><?php

	$summary_element_loop = 1;

	foreach ( $components as $component_id => $component_data ) {

		$summary_element_class = '';

		// Summary loop first/last class
		if ( ( ( $summary_element_loop - 1 ) % $summary_columns ) == 0 || $summary_columns == 1 ) {
			$summary_element_class = 'first';
		}

		if ( $summary_element_loop % $summary_columns == 0 ) {
			$summary_element_class = 'last';
		}

		$title = apply_filters( 'woocommerce_composite_component_title', esc_html( $component_data[ 'title' ] ), $component_id, $product->id );

		?><li class="summary_element summary_element_<?php echo $component_id; ?> <?php echo $summary_element_class; ?>" data-item_id="<?php echo $component_id; ?>">
			<div class="summary_element_wrapper_outer">
				<div class="summary_element_wrapper summary_element_link cp_clearfix disabled">
					<div class="summary_element_wrapper_inner cp_clearfix">
						<a class="summary_element_tap" href="#" ></a>
						<div class="summary_element_title summary_element_data">
							<h3 class="title summary_element_content"><?php
								echo apply_filters( 'woocommerce_composite_component_step_title', sprintf( __( '<span class="step_index">%d</span> <span class="step_title">%s</span>', 'woocommerce-composite-products' ), $summary_element_loop, $title ), $title, $summary_element_loop, $summary_elements, $product );
							?></h3>
						</div>
						<div class="summary_element_image summary_element_data"><?php

							echo $product->get_component_image( $component_id );

						?></div>
						<div class="summary_element_selection summary_element_data">
						</div>
						<div class="summary_element_price summary_element_data">
						</div>
					</div>
				</div>
			</div>
		</li><?php

		$summary_element_loop++;
	}
?></ul>

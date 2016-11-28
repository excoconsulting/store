<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WAPL Global table
 *
 * Display table with all the user configured global product labels
 *
 * @author     	Jeroen Sormani
 * @package 	WooCommerce Advanced Product Labels
 * @version    	1.0.0
 */
$labels = get_posts( array( 'posts_per_page' => '-1', 'post_type' => 'wapl', 'post_status' => array( 'draft', 'publish' ) ) );

?><tr valign='top'>
	<th scope='row' class='titledesc'><?php
		_e( 'Labels', 'woocommerce-advanced-product-labels' ); ?>:<br />
	</th>
	<td class='forminp' id='woocommerce-advanced-product-labels-overview'>

		<table class='wp-list-table wapl-table widefat'>
			<thead>
				<tr>
					<th style='padding-left: 10px;'><?php _e( 'Title', 'woocommerce-advanced-product-labels' ); ?></th>
					<th style='padding-left: 10px;'><?php _e( 'Label text', 'woocommerce-advanced-product-labels' ); ?></th>
					<th style='padding-left: 10px;'><?php _e( 'Author', 'woocommerce-advanced-product-labels' ); ?></th>
				</tr>
			</thead>
			<tbody><?php

				$i = 0;
				foreach ( $labels as $label ) :

					$settings 	= get_post_meta( $label->ID, '_wapl_global_label', true );
					$alt 		= ( $i++ ) % 2 == 0 ? 'alternate' : '';
					?><tr class='<?php echo $alt; ?>'>

						<td>
							<strong>
								<a href='<?php echo get_edit_post_link( $label->ID ); ?>' class='row-title' title='<?php _e( 'Edit Label', 'woocommerce-advanced-product-labels' ); ?>'><?php
									 echo _draft_or_post_title( $label->ID );
								?></a><?php
									 echo _post_states( $label );
							?></strong>
							<div class='row-actions'>
								<span class='edit'>
									<a href='<?php echo get_edit_post_link( $label->ID ); ?>' title='<?php _e( 'Edit Label', 'woocommerce-advanced-product-labels' ); ?>'><?php
										_e( 'Edit', 'woocommerce-advanced-product-labels' ); ?>
									</a>
									 |
								</span>
								<span class='trash'>
									<a href='<?php echo get_delete_post_link( $label->ID ); ?>' title='<?php _e( 'Delete Label', 'woocommerce-advanced-product-labels' ); ?>'><?php
										_e( 'Delete', 'woocommerce-advanced-product-labels' );
									?></a>
								</span>
							</div>
						</td>

						<td><?php
							echo $settings['text'];
						?></td>

						<td><?php
							echo get_the_author_meta( 'display_name', $label->post_author );
						?></td>

					</tr><?php

				endforeach;

				if ( empty( $labels ) ) :

					?><tr>
						<td colspan='2'><?php _e( 'There are no Labels. Yet...', 'woocommerce-advanced-product-labels' ); ?></td>
					</tr><?php

				endif;

			?></tbody>
			<tfoot>
				<tr>
					<th colspan='4' style='padding-left: 10px;'>
						<a href='<?php echo admin_url( 'post-new.php?post_type=wapl' ); ?>' class='add button'><?php
							_e( 'Add Product Label', 'woocommerce-advanced-product-labels' );
						?></a>
					</th>
				</tr>
			</tfoot>
		</table>
	</td>
</tr>

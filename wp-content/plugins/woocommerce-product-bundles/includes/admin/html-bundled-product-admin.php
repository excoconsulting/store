<?php
/**
 * Admin Add Bundled Product markup.
 *
 * @version 4.12.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><div class="wc-bundled-item wc-metabox <?php echo $toggle; ?>" rel="<?php echo $loop; ?>">
	<h3>
		<button type="button" class="remove_row button"><?php echo __( 'Remove', 'woocommerce' ); ?></button>
		<div class="handlediv" title="<?php echo __( 'Click to toggle', 'woocommerce' ); ?>"></div>
		<strong class="item-title"><?php echo $title . ' &ndash; #'. $product_id; ?></strong>
	</h3>
	<div class="item-data wc-metabox-content">
		<input type="hidden" name="bundle_data[<?php echo $loop; ?>][bundle_order]" class="bundled_item_position" value="<?php echo $loop; ?>" /><?php

		if ( false !== $item_id ) {
			?><input type="hidden" name="bundle_data[<?php echo $loop; ?>][item_id]" class="item_id" value="<?php echo $item_id; ?>" /><?php
		}

		?><input type="hidden" name="bundle_data[<?php echo $loop; ?>][product_id]" class="product_id" value="<?php echo $product_id; ?>" />

		<ul class="subsubsub"><?php

			/*--------------------------------*/
			/*  Tab menu items.               */
			/*--------------------------------*/

			$tab_loop = 0;

			foreach ( $tabs as $tab_values ) {

				$tab_id = $tab_values[ 'id' ];

				?><li><a href="#" data-tab="<?php echo $tab_id; ?>" class="<?php echo $tab_loop === 0 ? 'current' : ''; ?>"><?php
					echo $tab_values[ 'title' ];
				?></a></li><?php

				$tab_loop++;
			}

		?></ul><?php

		/*--------------------------------*/
		/*  Tab contents.                 */
		/*--------------------------------*/

		$tab_loop = 0;

		foreach ( $tabs as $tab_values ) {

			$tab_id = $tab_values[ 'id' ];

			?><div class="options_group options_group_<?php echo $tab_id; ?> <?php echo $tab_loop > 0 ? 'options_group_hidden' : ''; ?>"><?php
				do_action( 'woocommerce_bundled_product_admin_' . $tab_id . '_html', $loop, $product_id, $item_data, $post_id );
			?></div><?php

			$tab_loop++;
		}

	?></div>
</div>

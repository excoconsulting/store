<?php
global $wpdb, $woocommerce_recommender;

$wp_table = new WC_Recommender_Table_Recommendations( );
$wp_table->prepare_items();
?>
<div class="wc_recommendations_table">
	<form id="form-group-list" action="" method="post">
		<?php $wp_table->search_box( 'search', 'search_id' ); ?>
		<input type="hidden" name="wc-recommender-admin-action" value="bulk-recommendation-action" />
		<?php $wp_table->display(); ?>
	</form>
</div>


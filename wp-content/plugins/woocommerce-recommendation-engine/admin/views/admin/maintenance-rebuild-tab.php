<h3><?php _e( 'Rebuild Recommendations', 'wc_recommender' ); ?></h3>
<form method="POST">

	<div id="wc-recommender-complete" style="display:none;">
		<p><?php _e( 'Rebuild of recommendations complete', 'wc_recommender' ); ?></p>
	</div>

	<div id="wc-recommender-start">
		<?php _e('Rebuild all recommendations now', 'wc_recommender'); ?>
		<br />
		<input class='button primary' id="rebuild-recommendations" type="button" value="<?php _e( 'Rebuild' ); ?>" />
	</div>

	<div id="wc-recommender-status" style="display:none;">
		<p><?php _e('Building Recommendations:', 'wc_recommender'); ?> <span id="next_start">0</span> through <span id="through"></span> of <span id="total">...</span></p>
		<p><?php _e('Estimated Time Remaining:', 'wc_recommender'); ?> <span id="remaining">...</p>
	</div>
	
</form>

<script type="text/javascript">





</script>

<form method="get" id="searchform" action="<?php echo esc_url(home_url('/')); ?>">
	<div><label class="screen-reader-text" for="s">Search for:</label>
		<input type="text" value="" placeholder="<?php esc_html_e('Search', 'walker'); ?>" name="s" id="s" />
		<button type="submit" id="searchsubmit"><span class="ion-ios-search"></span></button>
	</div>
</form>
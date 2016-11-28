<div class="edgtf-google-map-holder">
	<div class="edgtf-google-map" id="<?php echo esc_attr($map_id); ?>" <?php print $map_data; ?>></div>
	<?php if ($scroll_wheel == "false") { ?>
		<div class="edgtf-google-map-overlay"></div>
	<?php } ?>
</div>

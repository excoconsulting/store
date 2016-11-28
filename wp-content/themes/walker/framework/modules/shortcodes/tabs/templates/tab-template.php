<div class="edgtf-tabs <?php echo esc_attr($tab_class); ?> clearfix">
	<ul class="edgtf-tabs-nav clearfix">
		<?php foreach ($tabs_titles as $tab_title) { ?>
			<li>
				<a href="#tab-<?php echo sanitize_title($tab_title)?>">
					<?php if($tab_title !== '') { ?>
						<span class="edgtf-tab-text-title">
							<?php echo esc_attr($tab_title)?>
						</span>
					<?php } ?>
				</a>
			 </li>
		<?php } ?>
	</ul> 
	<?php echo do_shortcode($content); ?>
</div>
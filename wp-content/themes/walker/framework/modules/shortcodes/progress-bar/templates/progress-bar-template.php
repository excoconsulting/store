<div <?php walker_edge_class_attribute($holder_classes); ?>>
	<<?php echo esc_attr($title_tag);?> class="edgtf-progress-title-holder clearfix">
		<span class="edgtf-progress-title"><?php echo esc_attr($title)?></span>
		<span class="edgtf-progress-number-wrapper">
			<span class="edgtf-progress-number">
				<span class="edgtf-percent">0</span>
			</span>
		</span>
	</<?php echo esc_attr($title_tag)?>>
	<div class="edgtf-progress-content-outer " <?php echo walker_edge_inline_style($inactive_bar_style) ?>>
		<div data-percentage=<?php echo esc_attr($percent)?> class="edgtf-progress-content" <?php echo walker_edge_inline_style($active_bar_style) ?>></div>
	</div>
</div>	
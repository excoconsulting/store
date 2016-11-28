<div class="edgtf-st-holder <?php echo esc_attr($title_position_class); ?>" <?php echo walker_edge_get_inline_style($holder_styles); ?>>
	<?php if($title !== '') { ?>
		<div class="edgtf-st-left-separator">
			<span <?php echo walker_edge_get_inline_style($separator_styles); ?>></span>
		</div>
		<<?php echo esc_attr($title_tag); ?> class="edgtf-st-title" <?php echo walker_edge_get_inline_style($title_styles); ?>>
			<span><?php echo esc_html($title); ?></span>
		</<?php echo esc_attr($title_tag);?>>
		<div class="edgtf-st-right-separator">
			<span <?php echo walker_edge_get_inline_style($separator_styles); ?>></span>
		</div>
	<?php } ?>
</div>
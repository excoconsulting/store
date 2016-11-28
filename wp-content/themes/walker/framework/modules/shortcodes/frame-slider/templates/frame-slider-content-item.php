<div class="edgtf-frame-slider-content-item">
	<<?php echo esc_attr($title_tag); ?> class="edgtf-frame-slider-title" <?php walker_edge_inline_style($title_styles); ?>><?php echo esc_html($title); ?></<?php echo esc_attr($title_tag); ?>>
	<?php if ($description !== '') { ?>
	    <p class="edgtf-frame-slider-description" <?php walker_edge_inline_style($description_styles); ?>><?php echo esc_html($description); ?></p>
	<?php } ?>
	<?php if(!empty($link) && !empty($link_text)) : ?>
	    <?php echo walker_edge_execute_shortcode('edgtf_button', $button_parameters); ?>
	<?php endif; ?>
</div>
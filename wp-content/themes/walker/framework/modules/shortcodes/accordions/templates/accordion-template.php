<<?php echo esc_attr($title_tag)?> class="clearfix edgtf-title-holder">
    <span class="edgtf-accordion-mark">
		<span class="edgtf-accordion-mark-icon">
			<span class="edgtf_icon_plus icon-arrows-plus"></span>
			<span class="edgtf_icon_minus icon-arrows-minus"></span>
		</span>
	</span>
	<span class="edgtf-tab-title"><?php echo esc_attr($title); ?></span>
	</span>
</<?php echo esc_attr($title_tag)?>>
<div class="edgtf-accordion-content">
	<div class="edgtf-accordion-content-inner">
		<?php echo do_shortcode($content); ?>
	</div>
</div>
<?php if ($title !== '') { ?>
<a class="edgtf-social-btn-holder" href="<?php echo esc_url($link); ?>" target="<?php echo esc_attr($target); ?>" <?php walker_edge_inline_style($button_styles); ?> <?php echo walker_edge_get_inline_attrs($button_data); ?>>
	<span class="edgtf-social-btn-title" <?php echo walker_edge_get_inline_attrs($button_span_data); ?>><?php echo esc_html($title); ?></span>
</a>
<?php } ?>
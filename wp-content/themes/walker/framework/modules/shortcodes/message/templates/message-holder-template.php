<div class="edgtf-message  <?php echo esc_attr($message_classes)?>" <?php echo walker_edge_get_inline_style($message_styles); ?>>
	<div class="edgtf-message-inner">
		<?php if($type == 'with_icon'){
			$icon_html = walker_edge_get_shortcode_module_template_part('templates/' . $type, 'message', '', $params);
			print $icon_html;
		} ?>
		<a href="#" class="edgtf-close" <?php echo walker_edge_get_inline_style($message_close_styles); ?>><i class="ion-ios-close-empty"></i></a>
		<div class="edgtf-message-text-holder">
			<div class="edgtf-message-text">
				<div class="edgtf-message-text-inner"><?php echo do_shortcode($content); ?></div>
			</div>
		</div>
	</div>
</div>
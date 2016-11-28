<?php $i = 0; ?>
<div class="edgtf-showcase-carousel" <?php echo walker_edge_get_inline_attrs($carousel_data); ?>>
	<div class="edgtf-showcase-carousel-images">
		<?php foreach ($images as $image) { ?>
			<div class="edgtf-showcase-carousel-item">
				<?php if(!empty($links[$i])) { ?>
	                <a class="edgtf-showcase-carousel-link" href="<?php echo esc_url($links[$i]) ?>" title="<?php echo esc_attr($image['title']); ?>" target="<?php echo esc_attr($custom_link_target); ?>">
	            <?php } ?>
				<img src="<?php echo esc_url($image['url'])?>" alt="<?php echo esc_attr($image['title']); ?>" width="<?php echo esc_attr($image['width']); ?>" height="<?php echo esc_attr($image['height']); ?>">
				<?php if(!empty($links[$i])) { ?>
					</a>
				<?php } ?>
				<?php $i++; ?>
			</div>
		<?php } ?>
	</div>
</div>
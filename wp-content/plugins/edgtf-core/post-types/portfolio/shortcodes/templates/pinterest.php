<article class="edgtf-portfolio-item <?php echo esc_attr($categories); ?>">
	<div class="edgtf-item-image-holder">
		<div class="edgtf-icons-holder">
			<?php echo $icon_html; ?>	
		</div>
		<a itemprop="url" href="<?php echo esc_url($item_link); ?>">
			<?php echo get_the_post_thumbnail(get_the_ID(),$thumb_size); ?>				
		</a>
	</div>
	<div class="edgtf-item-text-overlay">
		<div class="edgtf-item-text-overlay-inner">
			<div class="edgtf-item-text-holder">
				<<?php echo esc_attr($title_tag); ?> itemprop="name" class="edgtf-item-title entry-title">
					<?php echo esc_attr(get_the_title()); ?>
				</<?php echo esc_attr($title_tag); ?>>
				<?php echo $category_html; ?>
			</div>
		</div>	
	</div>
</article>

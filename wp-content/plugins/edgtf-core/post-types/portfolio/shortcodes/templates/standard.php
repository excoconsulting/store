<?php // This line is needed for mixItUp gutter ?>

<article class="edgtf-portfolio-item mix <?php echo esc_attr($categories)?>">
	<div class="edgtf-item-image-holder">
		<div class="edgtf-icons-holder">
			<?php echo $icon_html ?>	
		</div>
		<a itemprop="url" href="<?php echo esc_url($item_link); ?>">
			<?php echo get_the_post_thumbnail(get_the_ID(),$thumb_size); ?>				
		</a>
	</div>
	<div class="edgtf-item-text-holder">
		<<?php echo esc_attr($title_tag); ?> itemprop="name" class="edgtf-item-title entry-title">
			<a itemprop="url" href="<?php echo esc_url($item_link); ?>"><?php echo esc_attr(get_the_title()); ?></a>	
		</<?php echo esc_attr($title_tag); ?>>	
		<?php echo $category_html; ?>
	</div>
</article>

<?php // This line is needed for mixItUp gutter ?>
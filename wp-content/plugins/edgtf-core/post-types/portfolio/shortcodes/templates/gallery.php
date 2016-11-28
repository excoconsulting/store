<?php // This line is needed for mixItUp gutter ?>

<article class="edgtf-portfolio-item mix <?php echo esc_attr($categories)?>">
	<div class="edgtf-portfolio-item-inner">
		<a itemprop="url" class="edgtf-portfolio-link" href="<?php echo esc_url($item_link); ?>"></a>
		<div class="edgtf-item-image-holder">
			<?php echo get_the_post_thumbnail(get_the_ID(),$thumb_size); ?>				
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
	</div>
</article>
<?php // This line is needed for mixItUp gutter ?>
<div class="edgtf-related-posts-holder clearfix">
	<?php if ( $related_posts && $related_posts->have_posts() ) : ?>
		<div class="edgtf-related-posts-title">
			<h4><?php esc_html_e('RELATED POSTS', 'walker' ); ?></h4>
		</div>
		<div class="edgtf-related-posts-inner clearfix">
			<?php while ( $related_posts->have_posts() ) : $related_posts->the_post(); ?>
				<div class="edgtf-related-post">
					<div class="edgtf-related-post-inner">
						<div class="edgtf-related-post-image">
							<?php if (has_post_thumbnail()) { ?>
								<a itemprop="url" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
									<?php if($related_posts_image_size !== '') {
										the_post_thumbnail(array($related_posts_image_size, 0));
									} else {
										the_post_thumbnail('walker_edge_feature_image');
									} ?>
								</a>	
							<?php }	?>
						</div>
						<h5><a itemprop="name" class="entry-title edgtf-post-title" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h5>
						<div class="edgtf-post-info">
							<?php walker_edge_post_info(array(
								'date' => 'yes'
							)) ?>
						</div>
					</div>	
				</div>
			<?php endwhile; ?>
		</div>
	<?php endif; 
	wp_reset_postdata();
	?>
</div>
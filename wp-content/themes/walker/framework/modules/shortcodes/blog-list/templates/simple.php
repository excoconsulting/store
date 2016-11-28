<li class="edgtf-bli clearfix">
	<div class="edgtf-simple-inner">
		<?php if ( has_post_thumbnail() ) { ?>
		<div class="edgtf-simple-image">
			<a itemprop="url" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
				<?php echo get_the_post_thumbnail(get_the_ID(), 'walker_edge_search_image'); ?>
			</a>
		</div>
		<?php } ?>
		<div class="edgtf-simple-text">
			<<?php echo esc_attr($title_tag);?> itemprop="name" class="entry-title edgtf-simple-title">
				<a itemprop="url" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
			</<?php echo esc_attr($title_tag);?>>
            <div class="edgtf-simple-post-info">
                <?php walker_edge_post_info(array('date' => 'yes')); ?>
            </div>
		</div>
	</div>
</li>
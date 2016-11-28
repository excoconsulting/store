<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="edgtf-post-content">
		<div class="edgtf-post-video-holder">
			<?php walker_edge_get_module_template_part('templates/parts/video', 'blog'); ?>
		</div>
		<div class="edgtf-post-text">
			<div class="edgtf-post-info">
				<?php walker_edge_post_info(array(
					'author' => $display_author,
					'date' => $display_date,
					'category' => $display_category,
					'comments' => $display_comments,
					'like' => $display_like,
					'share' => $display_share
				)) ?>
			</div>
			<?php
				$title_param = array();
				$title_param['blog_template_type'] = $blog_template_type;
				$title_param['title_post_format'] = $post_format;
				walker_edge_get_module_template_part('templates/lists/parts/title', 'blog', '', $title_param);
			?>
			<?php walker_edge_excerpt($excerpt_length); ?>
			<div class="edgtf-post-read-more-holder">
				<?php walker_edge_read_more_button(); ?>
			</div>
            <?php do_action('walker_edge_blog_list_tags'); ?>
		</div>
	</div>
</article>
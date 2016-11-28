<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="edgtf-post-content">
		<?php
			$gallery_param = array();
			$gallery_param['blog_template_type'] = $blog_template_type;
			walker_edge_get_module_template_part('templates/lists/parts/gallery', 'blog', '', $gallery_param); ?>
		<div class="edgtf-post-text">
			<div class="edgtf-post-info">
				<?php walker_edge_post_info(array(
					'category' => $display_category,
					'date' => $display_date,
					'author' => $display_author, 
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
			<?php the_content(); ?>
			<?php do_action('walker_edge_blog_list_tags'); ?>
		</div>
	</div>
</article>
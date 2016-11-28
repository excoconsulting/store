<div class="edgtf-blog-list-holder <?php echo esc_attr($holder_classes); ?>">
	<?php if ($type === 'standard') { ?>
	<div class="edgtf-blh-inner">
	<?php } ?>
	<ul class="edgtf-blog-list">
		<?php if ($type === 'masonry') { ?>
			<div class="edgtf-blog-masonry-grid-sizer"></div>
			<div class="edgtf-blog-masonry-grid-gutter"></div>
		<?php } ?>
		<?php 
		$html = '';
			if($query_result->have_posts()):
			while ($query_result->have_posts()) : $query_result->the_post();
				$html .= walker_edge_get_shortcode_module_template_part('templates/'.$type, 'blog-list', '', $params);
			endwhile;
			print $html;
			else: ?>
			<div class="edgtf-blog-list-messsage">
				<p><?php esc_html_e('No posts were found.', 'walker'); ?></p>
			</div>
			<?php endif;
			wp_reset_postdata();
		?>
	</ul>
	<?php if ($type === 'standard') { ?>
	</div>
	<?php } ?>
</div>
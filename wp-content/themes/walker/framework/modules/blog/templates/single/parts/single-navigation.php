<?php if(walker_edge_options()->getOptionValue('blog_single_navigation') == 'yes'){ ?>
	<?php $navigation_blog_through_category = walker_edge_options()->getOptionValue('blog_navigation_through_same_category') ?>
	<div class="edgtf-blog-single-navigation">
		<?php if(get_previous_post() !== ""){ ?>
			<div class="edgtf-blog-single-prev">
				<?php
				if($navigation_blog_through_category == 'yes'){
					previous_post_link('%link','<span class="edgtf-blog-single-nav-mark icon-arrows-left"></span><span class="edgtf-blog-single-nav-text">'.esc_html__('PREV', 'walker').'</span>', true,'','category');
					if(get_previous_post(true) !== ""){
						$prev_post = get_previous_post(true);
						echo '<div class="edgtf-blog-single-nav-image">';
							echo get_the_post_thumbnail($prev_post->ID, 'thumbnail');
						echo '</div>';
					}
				} else {
					previous_post_link('%link','<span class="edgtf-blog-single-nav-mark icon-arrows-left"></span><span class="edgtf-blog-single-nav-text">'.esc_html__('PREV', 'walker').'</span>');
					if(get_previous_post() != ""){
						$prev_post = get_previous_post();
						echo '<div class="edgtf-blog-single-nav-image">';
							echo get_the_post_thumbnail($prev_post->ID, 'thumbnail');
						echo '</div>';
					}
				}
				?>
			</div>
		<?php } ?>
		<?php if(get_next_post() != ""){ ?>
			<div class="edgtf-blog-single-next">
				<?php
				if($navigation_blog_through_category == 'yes'){
					if(get_next_post(true) !== ""){
						$next_post = get_next_post(true);
						echo '<div class="edgtf-blog-single-nav-image">';
							echo get_the_post_thumbnail($next_post->ID, 'thumbnail');
						echo '</div>';
					}
					next_post_link('%link','<span class="edgtf-blog-single-nav-text">'.esc_html__('NEXT', 'walker').'</span><span class="edgtf-blog-single-nav-mark icon-arrows-right"></span>', true,'','category');
				} else {
					if(get_next_post() !== ""){
						$next_post = get_next_post();
						echo '<div class="edgtf-blog-single-nav-image">';
							echo get_the_post_thumbnail($next_post->ID, 'thumbnail');
						echo '</div>';
					}
					next_post_link('%link','<span class="edgtf-blog-single-nav-text">'.esc_html__('NEXT', 'walker').'</span><span class="edgtf-blog-single-nav-mark icon-arrows-right"></span>');
				}
				?>
			</div>
		<?php } ?>
	</div>
<?php } ?>
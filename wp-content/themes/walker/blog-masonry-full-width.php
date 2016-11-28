<?php
	/*
		Template Name: Blog: Masonry Full Width
	*/
?>
<?php get_header(); ?>
<?php walker_edge_get_title(); ?>
<?php do_action('walker_edge_before_slider_action'); ?>
<?php get_template_part('slider'); ?>
<?php do_action('walker_edge_after_slider_action'); ?>
	<div class="edgtf-full-width">
		<div class="edgtf-full-width-inner clearfix">
			<?php walker_edge_get_blog('masonry-full-width'); ?>
		</div>
	</div>
	<?php do_action('walker_edge_blog_list_additional_tags'); ?>
<?php get_footer(); ?>
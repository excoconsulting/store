<?php
$blog_archive_pages_classes = walker_edge_blog_archive_pages_classes(walker_edge_get_default_blog_list());
?>
<?php get_header(); ?>
<?php walker_edge_get_title(); ?>
<div class="<?php echo esc_attr($blog_archive_pages_classes['holder']); ?>">
<?php do_action('walker_edge_after_container_open'); ?>
	<div class="<?php echo esc_attr($blog_archive_pages_classes['inner']); ?>">
		<?php walker_edge_get_blog(walker_edge_get_default_blog_list()); ?>
	</div>
<?php do_action('walker_edge_before_container_close'); ?>
</div>
<?php do_action('walker_edge_blog_list_additional_tags'); ?>
<?php get_footer(); ?>
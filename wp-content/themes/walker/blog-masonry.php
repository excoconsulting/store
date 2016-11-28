<?php
    /*
    	Template Name: Blog: Masonry
    */
?>
<?php get_header(); ?>
<?php walker_edge_get_title(); ?>
<?php do_action('walker_edge_before_slider_action'); ?>
<?php get_template_part('slider'); ?>
<?php do_action('walker_edge_after_slider_action'); ?>
	<div class="edgtf-container">
	    <?php do_action('walker_edge_after_container_open'); ?>
	    <div class="edgtf-container-inner" >
	        <?php walker_edge_get_blog('masonry'); ?>
	    </div>
	    <?php do_action('walker_edge_before_container_close'); ?>
	</div>
	<?php do_action('walker_edge_blog_list_additional_tags'); ?>
<?php get_footer(); ?>
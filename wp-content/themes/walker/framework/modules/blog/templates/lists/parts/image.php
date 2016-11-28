<?php 
$post_link = get_the_permalink();

if($post_format === 'link') {

	if($image_post_link_link !== '') {
		$post_link = $image_post_link_link;
	} else {
		$post_link = get_the_permalink();
	}
}
?>
<?php if ( has_post_thumbnail() ) { ?>
	<div class="edgtf-post-image">
		<a itemprop="url" href="<?php echo esc_html($post_link); ?>" title="<?php the_title_attribute(); ?>">
			<?php the_post_thumbnail('walker_edge_feature_image'); ?>
		</a>
		<?php if ($post_format === 'audio') {
			walker_edge_get_module_template_part('templates/parts/audio', 'blog');
		} ?>
	</div>
<?php } else {
	if ($post_format === 'audio') {
		walker_edge_get_module_template_part('templates/parts/audio', 'blog');
	}
} ?>
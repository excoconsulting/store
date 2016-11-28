<?php
	if(isset($title_tag)){
		$title_tag = $title_tag;
	} else if ($blog_template_type === 'masonry' || $blog_template_type === 'masonry-full-width') {
		$title_tag = 'h4';
	} else {
		$title_tag = 'h3';
	}

	$post_link = get_the_permalink();
	if($title_post_format === 'link') {
		if($title_post_link_link !== '') {
			$post_link = $title_post_link_link;
		} else {
			$post_link = get_the_permalink();
		}
	}
?>
<<?php echo esc_attr($title_tag);?> itemprop="name" class="entry-title edgtf-post-title">
	<a itemprop="url" href="<?php echo esc_html($post_link); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
</<?php echo esc_attr($title_tag);?>>
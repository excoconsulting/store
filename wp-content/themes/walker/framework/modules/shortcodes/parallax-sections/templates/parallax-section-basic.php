<div class="edgtf-parallax-section-basic <?php echo esc_attr($parallax_section_class) ?>" <?php echo walker_edge_get_inline_attr($parallax_section_style, 'style') ?>>
	<div class="edgtf-parallax-text-holder" data-bottom-top="transform[customEase]:translateY(<?php echo esc_attr($offsets['text_area_y_start_offset']) ?>)"  data-top-bottom="transform[customEase]:translateY(<?php echo esc_attr($offsets['text_area_y_end_offset']) ?>)">
		<?php if ($heading !== '') { ?> 
			<h3 class="edgtf-parallax-title"><?php echo esc_html($heading)?></h3>
		<?php } ?>	
		<?php if ($excerpt !=='') { ?> 
			<p class="edgtf-parallax-excerpt"><?php echo esc_html($excerpt)?></p>
		<?php } ?>	
	</div>
	<div class="edgtf-main-image-holder" data-bottom-top="transform[customEase]:translateY(<?php echo esc_attr($offsets['main_image_y_start_offset']) ?>)"  data-top-bottom="transform[customEase]:translateY(<?php echo esc_attr($offsets['main_image_y_end_offset']) ?>)">
		<?php if ($main_image_link !== '') { ?>
			<a itemprop="url" class="edgtf-parallax-link" href="<?php echo esc_url($main_image_link) ?>" target="_self"></a>
		<?php } ?>
		<?php if ($main_image_src !== '') { ?>
			<img itemprop="image" class="edgtf-main-image" src="<?php echo esc_url($main_image_src) ?>" alt="<?php esc_html_e('parallax main image','walker')  ?>"/>
		<?php } ?>
	</div>
	<div class="edgtf-side-image-holder" data-bottom-top="transform[customEase]:translateY(<?php echo esc_attr($offsets['side_image_y_start_offset']) ?>)"  data-top-bottom="transform[customEase]:translateY(<?php echo esc_attr($offsets['side_image_y_end_offset']) ?>)">
		<?php if ($side_image_link !== '') { ?>
			<a itemprop="url" class="edgtf-parallax-link" href="<?php echo esc_url($main_image_link) ?>" target="_self"></a>
		<?php } ?>
		<?php if ($side_image_src !== '') { ?>
			<img itemprop="image" class="edgtf-side-image" src="<?php echo esc_url($side_image_src) ?>" alt="<?php esc_html_e('parallax side image','walker')  ?>"/>
		<?php } ?>
	</div>
</div>

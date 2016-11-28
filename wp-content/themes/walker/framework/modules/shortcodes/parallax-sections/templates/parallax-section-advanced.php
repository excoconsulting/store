<div class="edgtf-parallax-section-advanced <?php echo esc_attr($parallax_section_class) ?>" <?php echo walker_edge_get_inline_attr($parallax_section_style, 'style') ?>  <?php echo walker_edge_get_inline_attrs($resp_height_data); ?>>
	<div class="edgtf-parallax-inner">
		<div class="edgtf-parallax-hero-image-holder" data-center="transform[customEase]:translateY(<?php echo esc_attr($offsets['hero_image_y_start_offset']) ?>)" data-top-center="transform[customEase]:translateY(<?php echo esc_attr($offsets['hero_image_y_end_offset']) ?>)">
			<?php if ($hero_image_link !== '') { ?>
				<a class="edgtf-parallax-link" href="#>" target="_self"></a>
			<?php } ?>
			<?php if ($hero_image_src !== '') { ?>
				<img itemprop="image" class="edgtf-hero-image" src="<?php echo esc_url($hero_image_src) ?>" alt="<?php esc_html_e('parallax hero image','walker')  ?>"/>
			<?php } ?>
		</div>
		<div class="edgtf-parallax-info-section" data-center="transform[customEase]:translateY(<?php echo esc_attr($offsets['info_section_y_start_offset']) ?>)" data-top-center="transform[customEase]:translateY(<?php echo esc_attr($offsets['info_section_y_end_offset']) ?>)">
			<?php if ($heading !== '') { ?> 
				<h1 class="edgtf-parallax-hero-title"><?php echo esc_html($heading)?></h1>
			<?php } ?>
			<?php if ($excerpt !=='') { ?> 
				<p class="edgtf-parallax-excerpt"><?php echo esc_html($excerpt)?></p>
			<?php } ?>
			<?php if ($show_buttons == 'yes') { 
					if (($first_button_label !=='') && ($first_button_url !== '')) {
						echo walker_edge_execute_shortcode('edgtf_button', $first_button_params);
					}
					if (($second_button_label !=='') && ($second_button_url !== '')) {
						echo walker_edge_execute_shortcode('edgtf_button', $second_button_params);
					}
			} ?>
		</div>
		<div class="edgtf-additional-image-holder edgtf-add-1">
			<?php if ($add_image1_src !== '') { ?>
				<img itemprop="image" class="edgtf-add-image" src="<?php echo esc_url($add_image1_src) ?>" alt="<?php esc_html_e('parallax additional image','walker')  ?>"/>
			<?php } ?>
		</div>
		<div class="edgtf-additional-image-holder edgtf-add-2">
			<?php if ($add_image2_src !== '') { ?>
				<img itemprop="image" class="edgtf-add-image" src="<?php echo esc_url($add_image2_src) ?>" alt="<?php esc_html_e('parallax additional image','walker')  ?>"/>
			<?php } ?>
		</div>
		<div class="edgtf-additional-image-holder edgtf-add-3">
			<?php if ($add_image3_src !== '') { ?>
				<img itemprop="image" class="edgtf-add-image" src="<?php echo esc_url($add_image3_src) ?>" alt="<?php esc_html_e('parallax additional image','walker')  ?>"/>
			<?php } ?>
		</div>
		<div class="edgtf-additional-image-holder edgtf-add-4">
			<?php if ($add_image4_src !== '') { ?>
				<img itemprop="image" class="edgtf-add-image" src="<?php echo esc_url($add_image4_src) ?>" alt="<?php esc_html_e('parallax additional image','walker')  ?>"/>
			<?php } ?>
		</div>
		<div class="edgtf-additional-image-holder edgtf-add-5">
			<?php if ($add_image5_src !== '') { ?>
				<img itemprop="image" class="edgtf-add-image" src="<?php echo esc_url($add_image5_src) ?>" alt="<?php esc_html_e('parallax additional image','walker')  ?>"/>
			<?php } ?>
		</div>
	</div>
</div>
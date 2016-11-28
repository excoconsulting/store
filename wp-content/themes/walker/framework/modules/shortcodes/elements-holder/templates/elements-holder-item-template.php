<div class="edgtf-elements-holder-item <?php echo esc_attr($elements_holder_item_class); ?>" <?php echo walker_edge_get_inline_attrs($elements_holder_item_data); ?> <?php echo walker_edge_get_inline_style($elements_holder_item_style); ?>>
	<div class="edgtf-elements-holder-item-inner">
		<?php if($holder_link !== '') { ?>
			<a itemprop="url" class="edgtf-elements-holder-link" href="<?php echo esc_url($holder_link); ?>" target="<?php echo esc_attr($holder_link_target); ?>"></a>
		<?php } ?>
		<div class="edgtf-elements-holder-item-content <?php echo esc_attr($elements_holder_item_content_class); ?>" <?php echo walker_edge_get_inline_style($elements_holder_item_content_style); ?>>
			<?php if(count($elements_holder_item_content_responsive) > 0){ ?>
			<style type="text/css" data-type="edgtf-elements-custom-padding" scoped>
				<?php if(!empty($elements_holder_item_content_responsive['item_padding_1280_1440'])){ ?>
					@media only screen and (min-width: 1280px) and (max-width: 1440px) {
						.edgtf-elements-holder .edgtf-elements-holder-item-content.<?php echo esc_attr($elements_holder_item_content_class); ?> {
							padding: <?php echo esc_attr($elements_holder_item_content_responsive['item_padding_1280_1440']); ?> !important;
						}
					}
				<?php } ?>
				<?php if(!empty($elements_holder_item_content_responsive['item_padding_1024_1280'])){ ?>
					@media only screen and (min-width: 1024px) and (max-width: 1280px) {
						.edgtf-elements-holder .edgtf-elements-holder-item-content.<?php echo esc_attr($elements_holder_item_content_class); ?> {
							padding: <?php echo esc_attr($elements_holder_item_content_responsive['item_padding_1024_1280']); ?> !important;
						}
					}
				<?php } ?>
				<?php if(!empty($elements_holder_item_content_responsive['item_padding_768_1024'])){ ?>
				@media only screen and (min-width: 768px) and (max-width: 1024px) {
					.edgtf-elements-holder .edgtf-elements-holder-item-content.<?php echo esc_attr($elements_holder_item_content_class); ?> {
						padding: <?php echo esc_attr($elements_holder_item_content_responsive['item_padding_768_1024']); ?> !important;
					}
				}
				<?php } ?>
				<?php if(!empty($elements_holder_item_content_responsive['item_padding_600_768'])){ ?>
				@media only screen and (min-width: 600px) and (max-width: 768px) {
					.edgtf-elements-holder .edgtf-elements-holder-item-content.<?php echo esc_attr($elements_holder_item_content_class); ?> {
						padding: <?php echo esc_attr($elements_holder_item_content_responsive['item_padding_600_768']); ?> !important;
					}
				}
				<?php } ?>
				<?php if(!empty($elements_holder_item_content_responsive['item_padding_480_600'])){ ?>
				@media only screen and (min-width: 480px) and (max-width: 600px) {
					.edgtf-elements-holder .edgtf-elements-holder-item-content.<?php echo esc_attr($elements_holder_item_content_class); ?> {
						padding: <?php echo esc_attr($elements_holder_item_content_responsive['item_padding_480_600']); ?> !important;
					}
				}
				<?php } ?>
				<?php if(!empty($elements_holder_item_content_responsive['item_padding_480'])){ ?>
				@media only screen and (max-width: 480px) {
					.edgtf-elements-holder .edgtf-elements-holder-item-content.<?php echo esc_attr($elements_holder_item_content_class); ?> {
						padding: <?php echo esc_attr($elements_holder_item_content_responsive['item_padding_480']); ?> !important;
					}
				}
				<?php } ?>
				</style>
			<?php } ?>
			<?php echo do_shortcode($content); ?>
		</div>
	</div>
</div>
<div <?php walker_edge_class_attribute($holder_classes); ?> style="background-color: <?php echo esc_attr($background_color) ?>">
    <div class="edgtf-imwt-inner">
        <div class="edgtf-imwt-table">
            <div class="edgtf-imwt-cell">
                <div class="edgtf-imwt-image">
                    <?php if ($enable_lightbox) { ?>
                        <a href="<?php echo esc_url($image['url'])?>" data-rel="prettyPhoto[single_pretty_photo]" title="<?php echo esc_attr($image['title']); ?>"></a>
                    <?php } ?>
                        <?php if(is_array($image_size) && count($image_size)) : ?>
                            <?php echo walker_edge_generate_thumbnail($image['image_id'], null, $image_size[0], $image_size[1]); ?>
                        <?php else: ?>
                            <?php echo wp_get_attachment_image($image['image_id'], $image_size); ?>
                        <?php endif; ?>
                </div>
                <?php if ($enable_lightbox) { ?>
                    <div class="edgtf-imwt-image-bgrnd" style="background-image: url(<?php echo esc_url($image['url']); ?>)">
                    </div>
                <?php } ?>
                <div class="edgtf-imwt-text">
                    <div class="edgtf-imwt-title-holder">
                    <<?php echo esc_attr($title_tag); ?> class="edgtf-imwt-title"><?php echo esc_attr($title); ?></<?php echo esc_attr($title_tag); ?>>
                    </div>

                    <?php if ($text !== '') { ?>
                        <p class="edgtf-imwt-text"><?php echo esc_html($text); ?></p>
                    <?php } ?>

                    <?php if(!empty($link) && !empty($link_text)) : ?>
                        <?php echo walker_edge_execute_shortcode('edgtf_button', $button_parameters); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
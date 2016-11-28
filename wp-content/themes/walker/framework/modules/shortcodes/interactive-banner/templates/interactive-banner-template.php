<?php
/**
 * Interactive Banner shortcode template
 */
?>
<div class="edgtf-interactive-banner-holder">
    <?php if ( $image !== '' ) { ?>
        <div class="edgtf-interactive-banner-image">
            <?php echo wp_get_attachment_image($image,'full'); ?>
        </div>
        <div <?php walker_edge_class_attribute($main_content_classes); ?> <?php walker_edge_inline_style($main_content_styles); ?>>
            <?php if(count($main_content_responsive) > 0){ ?>
                <style type="text/css" data-type="edgtf-interactive-banner-custom-padding" scoped>
                    <?php if(!empty($main_content_responsive['content_padding_1280_1440'])){ ?>
                    @media only screen and (min-width: 1281px) and (max-width: 1440px) {
                        .edgtf-interactive-banner-holder .edgtf-interactive-banner-info.<?php echo esc_attr($main_content_responsive_class); ?> {
                            padding: <?php echo esc_attr($main_content_responsive['content_padding_1280_1440']); ?> !important;
                        }
                    }
                    <?php } ?>
                    <?php if(!empty($main_content_responsive['content_padding_1024_1280'])){ ?>
                    @media only screen and (min-width: 1025px) and (max-width: 1280px) {
                        .edgtf-interactive-banner-holder .edgtf-interactive-banner-info.<?php echo esc_attr($main_content_responsive_class); ?> {
                            padding: <?php echo esc_attr($main_content_responsive['content_padding_1024_1280']); ?> !important;
                        }
                    }
                    <?php } ?>
                    <?php if(!empty($main_content_responsive['content_padding_768_1024'])){ ?>
                    @media only screen and (min-width: 769px) and (max-width: 1024px) {
                        .edgtf-interactive-banner-holder .edgtf-interactive-banner-info.<?php echo esc_attr($main_content_responsive_class); ?> {
                            padding: <?php echo esc_attr($main_content_responsive['content_padding_768_1024']); ?> !important;
                        }
                    }
                    <?php } ?>
                    <?php if(!empty($main_content_responsive['content_padding_600_768'])){ ?>
                    @media only screen and (min-width: 601px) and (max-width: 768px) {
                        .edgtf-interactive-banner-holder .edgtf-interactive-banner-info.<?php echo esc_attr($main_content_responsive_class); ?> {
                            padding: <?php echo esc_attr($main_content_responsive['content_padding_600_768']); ?> !important;
                        }
                    }
                    <?php } ?>
                </style>
            <?php } ?>
            <<?php echo esc_attr($title_tag); ?> class="edgtf-interactive-banner-title" <?php walker_edge_inline_style($title_styles); ?>><?php echo esc_html($title); ?></<?php echo esc_attr($title_tag); ?>>
            <?php if ($description !== '') { ?>
                <p class="edgtf-interactive-banner-description" <?php walker_edge_inline_style($description_styles); ?>><?php echo esc_html($description); ?></p>
            <?php } ?>
            <?php if(!empty($link) && !empty($link_text)) : ?>
                <?php echo walker_edge_execute_shortcode('edgtf_button', $button_parameters); ?>
            <?php endif; ?>
        </div>
    <?php } ?>
</div>
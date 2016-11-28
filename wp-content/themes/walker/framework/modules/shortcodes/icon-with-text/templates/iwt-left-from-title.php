<div <?php walker_edge_class_attribute($holder_classes); ?>>
    <div class="edgtf-iwt-content-holder">
        <div class="edgtf-iwt-icon-title-holder">
            <div class="edgtf-iwt-icon-holder">
                <?php if(!empty($custom_icon)) : ?>
                    <?php echo wp_get_attachment_image($custom_icon, 'full'); ?>
                    <?php if(($icon_flip == 'yes')): ?>
                        <?php echo wp_get_attachment_image($custom_icon, 'full'); ?>
                    <?php endif; ?>
                <?php else: ?>
                    <?php echo walker_edge_get_shortcode_module_template_part('templates/icon', 'icon-with-text', '', array('icon_parameters' => $icon_parameters)); ?>
                <?php endif; ?>
            </div>
            <div class="edgtf-iwt-title-holder">
                <<?php echo esc_attr($title_tag); ?> <?php walker_edge_inline_style($title_styles); ?>><?php echo esc_html($title); ?></<?php echo esc_attr($title_tag); ?>>
            </div>
        </div>
        <div class="edgtf-iwt-text-holder">
            <p <?php walker_edge_inline_style($text_styles); ?>><?php echo esc_html($text); ?></p>

            <?php if(!empty($link) && !empty($link_text)) : ?>
                <?php echo walker_edge_execute_shortcode('edgtf_button', $button_params); ?>
            <?php endif; ?>
        </div>
    </div>
</div>
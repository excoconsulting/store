<?php
/**
 * Call to action shortcode template
 */
?>
<div class="edgtf-container-inner">
    <div class="edgtf-call-to-action">
        <div class="edgtf-text-wrapper">
            <div class="edgtf-call-to-action-text" <?php echo walker_edge_get_inline_style($content_styles) ?>><?php echo do_shortcode($content); ?></div>
        </div>
        <div class="edgtf-call-to-action-buttons">
            <?php
            if ($button_1_parameters['link'] && $button_1_parameters['text']) {
                echo walker_edge_get_button_html($button_1_parameters);
            }
            if ($button_2_parameters['link'] && $button_2_parameters['text']) {
                echo walker_edge_get_button_html($button_2_parameters);
            }
            ?>
        </div>
    </div>
</div>
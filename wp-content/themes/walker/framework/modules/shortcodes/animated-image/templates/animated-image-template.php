<?php
/**
 * Animated Image shortcode template
 */
?>
<div class="edgtf-animated-image-holder">
    <?php if ( $image !== '' ) { ?>
        <div class="edgtf-animated-image">
            <?php if(!empty($link)) { ?><a href="<?php echo esc_url($link) ?>" target="<?php echo esc_attr($target); ?>"><?php } ?>
                <?php echo wp_get_attachment_image($image,'full'); ?>
            <?php if(!empty($link)) { ?></a><?php } ?>
        </div>
        <<?php echo esc_attr($title_tag); ?> class="edgtf-animated-image-title">
            <?php if(!empty($link)) { ?><a href="<?php echo esc_url($link) ?>" target="<?php echo esc_attr($target); ?>"><?php } ?>
                <?php echo esc_html($title); ?>
            <?php if(!empty($link)) { ?></a><?php } ?>
        </<?php echo esc_attr($title_tag); ?>>
    <?php } ?>
</div>
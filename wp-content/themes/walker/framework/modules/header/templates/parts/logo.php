<?php do_action('walker_edge_before_site_logo'); ?>

<div class="edgtf-logo-wrapper">
    <a itemprop="url" href="<?php echo esc_url(home_url('/')); ?>" <?php walker_edge_inline_style($logo_styles); ?>>
        <img itemprop="image" class="edgtf-normal-logo" src="<?php echo esc_url($logo_image); ?>" alt="<?php esc_html_e('logo','walker'); ?>"/>
        <?php if(!empty($logo_image_dark)){ ?><img itemprop="image" class="edgtf-dark-logo" src="<?php echo esc_url($logo_image_dark); ?>" alt="<?php esc_html_e('dark logo','walker'); ?>o"/><?php } ?>
        <?php if(!empty($logo_image_light)){ ?><img itemprop="image" class="edgtf-light-logo" src="<?php echo esc_url($logo_image_light); ?>" alt="<?php esc_html_e('light logo','walker'); ?>"/><?php } ?>
    </a>
</div>

<?php do_action('walker_edge_after_site_logo'); ?>
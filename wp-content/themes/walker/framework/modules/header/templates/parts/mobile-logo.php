<?php do_action('walker_edge_before_mobile_logo'); ?>

<div class="edgtf-mobile-logo-wrapper">
    <a itemprop="url" href="<?php echo esc_url(home_url('/')); ?>" <?php walker_edge_inline_style($logo_styles); ?>>
        <img itemprop="image" src="<?php echo esc_url($logo_image); ?>" alt="<?php esc_html_e('mobile logo','walker'); ?>"/>
    </a>
</div>

<?php do_action('walker_edge_after_mobile_logo'); ?>
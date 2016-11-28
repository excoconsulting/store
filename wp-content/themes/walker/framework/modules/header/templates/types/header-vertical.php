<?php do_action('walker_edge_before_page_header'); ?>

<aside class="edgtf-vertical-menu-area">
    <div class="edgtf-vertical-menu-area-inner <?php echo esc_attr($vertical_text_align_class); ?>">
        <div class="edgtf-vertical-area-background" <?php walker_edge_inline_style(array($vertical_header_background_color,$vertical_header_opacity,$vertical_background_image)); ?>></div>
        <?php if(!$hide_logo) {
            walker_edge_get_logo('vertical');
        } ?>
        
        <?php walker_edge_get_vertical_main_menu(); ?>

        <div class="edgtf-vertical-area-widget-holder">
            <?php if(is_active_sidebar('edgtf-header-vertical-widget-area')) : ?>
                <?php dynamic_sidebar('edgtf-header-vertical-widget-area'); ?>
            <?php endif; ?>
        </div>

        <div class="edgtf-vertical-area-bottom-widget-holder">
            <?php if(is_active_sidebar('edgtf-header-vertical-bottom-widget-area')) : ?>
                <?php dynamic_sidebar('edgtf-header-vertical-bottom-widget-area'); ?>
            <?php endif; ?>
        </div>
    </div>
</aside>

<?php do_action('walker_edge_after_page_header'); ?>
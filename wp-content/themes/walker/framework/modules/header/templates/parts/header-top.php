<?php if($show_header_top) : ?>

<?php do_action('walker_edge_before_header_top'); ?>

<div class="edgtf-top-bar">
    <?php if($top_bar_in_grid) : ?>
    <div class="edgtf-grid">
    <?php endif; ?>
		<?php do_action( 'walker_edge_after_header_top_html_open' ); ?>
        <div class="edgtf-vertical-align-containers edgtf-<?php echo esc_attr($column_widths); ?>">
            <div class="edgtf-position-left">
                <div class="edgtf-position-left-inner">
                    <?php if(is_active_sidebar('edgtf-top-bar-left')) : ?>
                        <?php dynamic_sidebar('edgtf-top-bar-left'); ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php if($show_widget_center){ ?>
                <div class="edgtf-position-center">
                    <div class="edgtf-position-center-inner">
                        <?php if(is_active_sidebar('edgtf-top-bar-center')) : ?>
                            <?php dynamic_sidebar('edgtf-top-bar-center'); ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php } ?>
            <div class="edgtf-position-right">
                <div class="edgtf-position-right-inner">
                    <?php if(is_active_sidebar('edgtf-top-bar-right')) : ?>
                        <?php dynamic_sidebar('edgtf-top-bar-right'); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php if($top_bar_in_grid) : ?>
    </div>
    <?php endif; ?>
</div>

<?php do_action('walker_edge_after_header_top'); ?>

<?php endif; ?>
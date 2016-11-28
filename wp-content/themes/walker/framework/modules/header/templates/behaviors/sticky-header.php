<?php do_action('walker_edge_before_sticky_header'); ?>

<div class="edgtf-sticky-header">
    <?php do_action( 'walker_edge_after_sticky_menu_html_open' ); ?>
    <div class="edgtf-sticky-holder">
    <?php if($sticky_header_in_grid) : ?>
        <div class="edgtf-grid">
            <?php endif; ?>
            <div class=" edgtf-vertical-align-containers">
                <div class="edgtf-position-left">
                    <div class="edgtf-position-left-inner">
                        <?php if(!$hide_logo) {
                            walker_edge_get_logo('sticky');
                        } ?>
                    </div>
                </div>
                <div class="edgtf-position-center">
                    <div class="edgtf-position-center-inner">
                        <?php walker_edge_get_sticky_menu('edgtf-sticky-nav'); ?>
                    </div>
                </div>
                <div class="edgtf-position-right">
                    <div class="edgtf-position-right-inner">
                        <?php if(is_active_sidebar('edgtf-sticky-right')) : ?>
                            <?php dynamic_sidebar('edgtf-sticky-right'); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if($sticky_header_in_grid) : ?>
        </div>
        <?php endif; ?>
    </div>
    <?php do_action('walker_edge_end_of_page_header_html'); ?>
</div>

<?php do_action('walker_edge_after_sticky_header'); ?>
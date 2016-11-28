<?php if(walker_edge_options()->getOptionValue('portfolio_single_hide_pagination') !== 'yes') : ?>

    <?php
    $back_to_link = get_post_meta(get_the_ID(), 'portfolio_single_back_to_link', true);
    $nav_same_category = walker_edge_options()->getOptionValue('portfolio_single_nav_same_category') == 'yes';
    ?>

    <div class="edgtf-portfolio-single-navigation">
        <?php if(get_previous_post() !== '') : ?>
            <div class="edgtf-portfolio-single-prev">
                <?php if($nav_same_category) {
                    previous_post_link('%link','<span class="edgtf-portfolio-single-nav-mark icon-arrows-left"></span><span class="edgtf-portfolio-single-nav-text">'.esc_html__('PREV', 'walker').'</span>', true,'','portfolio-category');
                    if(get_previous_post(true) !== ""){
                        $prev_post = get_previous_post(true);
                        echo '<div class="edgtf-portfolio-single-nav-image">';
                            echo get_the_post_thumbnail($prev_post->ID, 'thumbnail');
                        echo '</div>';
                    }
                } else {
                    previous_post_link('%link','<span class="edgtf-portfolio-single-nav-mark icon-arrows-left"></span><span class="edgtf-portfolio-single-nav-text">'.esc_html__('PREV', 'walker').'</span>');
                    if(get_previous_post() != ""){
                        $prev_post = get_previous_post();
                        echo '<div class="edgtf-portfolio-single-nav-image">';
                            echo get_the_post_thumbnail($prev_post->ID, 'thumbnail');
                        echo '</div>';
                    }
                } ?>
            </div>
        <?php endif; ?>

        <?php if($back_to_link !== '') : ?>
            <div class="edgtf-portfolio-back-btn">
                <a href="<?php echo esc_url(get_permalink($back_to_link)); ?>">
                    <span class="ion-grid"></span>
                </a>
            </div>
        <?php endif; ?>

        <?php if(get_next_post() !== '') : ?>
            <div class="edgtf-portfolio-single-next">
                <?php if($nav_same_category) {
                    if(get_next_post(true) !== ""){
                        $next_post = get_next_post(true);
                        echo '<div class="edgtf-portfolio-single-nav-image">';
                            echo get_the_post_thumbnail($next_post->ID, 'thumbnail');
                        echo '</div>';
                    }
                    next_post_link('%link','<span class="edgtf-portfolio-single-nav-text">'.esc_html__('NEXT', 'walker').'</span><span class="edgtf-portfolio-single-nav-mark icon-arrows-right"></span>', true,'','portfolio-category');
                } else {
                    if(get_next_post() !== ""){
                        $next_post = get_next_post();
                        echo '<div class="edgtf-portfolio-single-nav-image">';
                            echo get_the_post_thumbnail($next_post->ID, 'thumbnail');
                        echo '</div>';
                    }
                    next_post_link('%link','<span class="edgtf-portfolio-single-nav-text">'.esc_html__('NEXT', 'walker').'</span><span class="edgtf-portfolio-single-nav-mark icon-arrows-right"></span>');
                } ?>
            </div>
        <?php endif; ?>
    </div>

<?php endif; ?>
<?php if(walker_edge_options()->getOptionValue('portfolio_single_hide_date') !== 'yes') : ?>
    <div class="edgtf-portfolio-info-item edgtf-portfolio-date">
        <p class="edgtf-portfolio-info-title"><?php esc_html_e('Date:', 'walker'); ?></p>
        <p itemprop="dateCreated" class="edgtf-portfolio-info-date entry-date updated"><?php the_time(get_option('date_format')); ?></p>
        <meta itemprop="interactionCount" content="UserComments: <?php echo get_comments_number(walker_edge_get_page_id()); ?>"/>
    </div>
<?php endif; ?>
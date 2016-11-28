<?php if(walker_edge_options()->getOptionValue('enable_social_share') == 'yes' && walker_edge_options()->getOptionValue('enable_social_share_on_portfolio-item') == 'yes') : ?>
    <div class="edgtf-portfolio-social edgtf-portfolio-info-item">
    	<span class="edgtf-portfolio-social-label"><?php esc_html_e('Share:', 'walker'); ?></span>
        <?php echo walker_edge_get_social_share_html() ?>
    </div>
<?php endif; ?>
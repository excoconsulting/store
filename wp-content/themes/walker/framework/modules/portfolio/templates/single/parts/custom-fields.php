<?php
$custom_fields = get_post_meta(get_the_ID(), 'edgt_portfolios', true);

if(is_array($custom_fields) && count($custom_fields)) :
    usort($custom_fields, 'walker_edge_compare_portfolio_options');

    foreach($custom_fields as $custom_field) : ?>
        <div class="edgtf-portfolio-info-item edgtf-portfolio-custom-field">
            <?php if(!empty($custom_field['optionLabel'])) : ?>
                <p class="edgtf-portfolio-info-title"><?php echo esc_html($custom_field['optionLabel'].':'); ?></p>
            <?php endif; ?>
            <p><?php if(!empty($custom_field['optionUrl'])) : ?><a itemprop="url" href="<?php echo esc_url($custom_field['optionUrl']); ?>"><?php endif; ?>
                    <?php echo esc_html($custom_field['optionValue']); ?>
                <?php if(!empty($custom_field['optionUrl'])) : ?></a><?php endif; ?>
            </p>
        </div>
    <?php endforeach; ?>

<?php endif; ?>
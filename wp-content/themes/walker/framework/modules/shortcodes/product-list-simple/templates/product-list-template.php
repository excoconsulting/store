<div class="edgtf-pls-holder <?php echo esc_attr($holder_classes) ?>">
    <ul class="edgtf-pls-inner">
        <?php if($query_result->have_posts()): while ($query_result->have_posts()) : $query_result->the_post(); ?>
            <?php 
                $product = walker_edge_return_woocommerce_global_variable();

                $rating_enabled = false;
                if ( get_option( 'woocommerce_enable_review_rating' ) !== 'no' ) {
                    $rating_enabled = true;
                    $average      = $product->get_average_rating();
                }
            ?>
            <li class="edgtf-pls-item">
                <div class="edgtf-pls-image">
                    <a itemprop="url" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                        <?php echo get_the_post_thumbnail( get_the_ID(), apply_filters( 'walker_edge_product_list_simple_image_size', 'shop_thumbnail' )) ?>
                    </a>    
                </div>
                <div class="edgtf-pls-text">
                    <?php if($display_title === 'yes') { ?>
                        <<?php echo esc_attr($title_tag); ?> itemprop="name" class="entry-title edgtf-pls-title" <?php echo walker_edge_get_inline_style($title_styles); ?>><?php the_title(); ?></<?php echo esc_attr($title_tag); ?>>
                    <?php } ?>
                    <?php if($display_price === 'yes') { ?>
                        <div class="edgtf-pls-price"><?php print $product->get_price_html(); ?></div>
                    <?php } ?>  
                    <?php if ($rating_enabled && $display_rating === 'yes') { ?>
                        <div class="edgtf-pls-rating-holder">
                            <div class="edgtf-pls-rating" title="<?php printf( esc_html__( 'Rated %s out of 5', 'walker' ), $average ); ?>">
                                <span style="width:<?php echo ( ( $average / 5 ) * 100 ); ?>%"></span>
                            </div>
                        </div>  
                    <?php } ?>
                </div>
            </li>
        <?php endwhile; else: ?>
            <li class="edgtf-pls-messsage">
                <p><?php esc_html_e('No posts were found.', 'walker'); ?></p>
            </li>
        <?php endif;
            wp_reset_postdata();
        ?>
    </ul>
</div>
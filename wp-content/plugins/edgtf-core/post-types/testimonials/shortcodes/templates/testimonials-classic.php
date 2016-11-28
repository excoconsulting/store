<div id="edgtf-testimonials<?php echo esc_attr($current_id) ?>" class="edgtf-testimonial-content">
    <div class="edgtf-testimonial-content-inner">
        <?php if(get_the_post_thumbnail($current_id) !== "") { ?>
            <div class="edgtf-testimonial-image-holder">
                <div class="edgtf-testimonial-image-left-separator">
                    <span></span>
                </div>
                <span class="edgtf-testimonial-image">
                    <?php echo get_the_post_thumbnail($current_id, '110'); ?>
                </span>
                <div class="edgtf-testimonial-image-right-separator">
                    <span></span>
                </div>
            </div>
        <?php } ?>
        <div class="edgtf-testimonial-text-holder">
            <div class="edgtf-testimonial-text-inner">
                <p class="edgtf-testimonial-text"><?php echo trim($text); ?></p>
                <?php if ($show_author == "yes") { ?>
                    <div class = "edgtf-testimonial-author">
                        <p class="edgtf-testimonial-author-text"><?php echo esc_attr($author)?>
                            <?php if($show_position == "yes" && $job !== ''){ ?>
                                <span> / </span><span class="edgtf-testimonials-job"><?php echo esc_attr($job)?></span>
                            <?php }?>
                        </p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

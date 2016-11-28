<div class="edgtf-popup-holder">
    <div class="edgtf-popup-shader"></div>
    <div class="edgtf-popup-table">
        <div class="edgtf-popup-table-cell">
            <div class="edgtf-popup-inner">
                <div class="edgtf-popup-content">
                    <a class="edgtf-popup-close" href="javascript:void(0)"><span class="ion-ios-close-empty"></span></a>
                    <?php if($image !== '') { ?>
                        <div class="edgtf-popup-image">
                            <img src="<?php echo esc_url($image); ?>" alt="<?php esc_html_e('Pop-up Image', 'walker'); ?>" />
                        </div>
                    <?php } ?>    
                    <?php if($title !== '') { ?>
                        <h4 class="edgtf-popup-title"><?php echo esc_html($title); ?></h4>
                    <?php } ?>
                    <?php if($subtitle !== '') { ?>
                        <p class="edgtf-popup-subtitle"><?php echo esc_html($subtitle); ?></p>
                    <?php } ?>
                    <?php if($contact_form !== '') { ?>
                        <div class="edgtf-popup-form">
                            <?php echo do_shortcode('[contact-form-7 id="' . $contact_form .'" html_class="'. $contact_form_style .'"]'); ?>
                        </div>
                    <?php } ?>    
                </div>
            </div>
        </div>
    </div>
</div>
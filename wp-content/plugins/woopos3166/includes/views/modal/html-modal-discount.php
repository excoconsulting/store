<div class="md-modal md-dynamicmodal md-menu md-close-by-overlay" id="modal-order_discount">
    <div class="media-frame-menu">
        <div class="media-menu">
            <a href="#discount_tab" class="active discount_modal"><?php _e('Discount', 'woocommerce'); ?></a>
            <a href="#coupon_tab" class="coupon_modal"><?php _e('Coupon', 'woocommerce'); ?></a>
            <?php if( isset( $GLOBALS['wc_points_rewards'] ) ) { ?>
            <a href="#wc_points_rewards_tab" class="wc_points_rewards_modal"><?php echo apply_filters('woocommerce_cart_totals_coupon_label', 'WC_POINTS_REDEMPTION'); ?></a>
            <?php } ?>
        </div>                
    </div>
    <div class="md-content">        
        <div id="discount_tab" class="discount_section popup_section" style="display: block;">
            <h1><?php _e('Discount', 'wc_point_of_sale'); ?><span class="md-close"></span></h1>
            <div class="media-frame-wrap">

                    <input type="hidden" id="order_discount_prev" value="<?php echo ($order->get_total_discount() > 0 ) ? $order->get_total_discount() : ''; ?>">

                    <div id="inline_order_discount"></div>

                    <input type="hidden" id="order_discount_symbol" value="currency_symbol">

            </div>
            <div class="wrap-button">
                <button class="button wp-button-large md-close" type="button"><?php _e('Back', 'wc_point_of_sale'); ?></button>
                <button class="button button-primary wp-button-large alignright" type="button" id="save_order_discount"><?php _e('Add Discount', 'wc_point_of_sale'); ?></button>
            </div>
        </div>
        <div id="coupon_tab" class="discount_section popup_section">
            <h1><?php _e('Coupon', 'wc_point_of_sale'); ?><span class="md-close"></span></h1>
            <div class="media-frame-wrap">
                <input id="coupon_code" class="input-text" type="text" placeholder="<?php _e('Coupon code', 'wc_point_of_sale'); ?>" value="" name="coupon_code">
                <button class="button" type="button" name="apply_coupon" id="apply_coupon_btn"><?php _e('Apply Coupon', 'wc_point_of_sale'); ?></button>

                <div class="messages"></div>
            </div>
            <div class="wrap-button">
                <button class="button wp-button-large md-close" type="button"><?php _e('Back', 'wc_point_of_sale'); ?></button>
            </div>

        </div>
        <?php if( isset( $GLOBALS['wc_points_rewards'] ) ) { ?>
            <div id="wc_points_rewards_tab" class="discount_section popup_section">
                <h1><?php echo apply_filters('woocommerce_cart_totals_coupon_label', 'WC_POINTS_REDEMPTION'); ?><span class="md-close"></span></h1>
                <div class="media-frame-wrap">
                    <p>
                    <?php
                    global $wc_points_rewards;
                    $message = get_option( 'wc_points_rewards_redeem_points_message' );
                    $message = str_replace( '{points}', '<span id="wc_points_rewards_number_of_points">' . number_format_i18n( 0 ) . '</span>', $message );

                    // the maximum discount available given how many points the customer has
                    $message = str_replace( '{points_value}', '<span id="wc_points_rewards_points_value">' . woocommerce_price( 0 ) . '</span>', $message );

                    // points label
                    $message = str_replace( '{points_label}', $wc_points_rewards->get_points_label( 0 ), $message );

                    echo $message;
                    ?>
                    </p>                    
                    <button type="button" class="button wc_points_rewards_apply_discount" >
                        <?php _e( 'Apply Discount', 'wc_point_of_sale' ); ?>
                    </button>
                </div>
                <div class="wrap-button">
                    <button class="button wp-button-large md-close" type="button"><?php _e('Back', 'wc_point_of_sale'); ?></button>
                </div>

            </div>
        <?php } ?>
    </div>
</div>
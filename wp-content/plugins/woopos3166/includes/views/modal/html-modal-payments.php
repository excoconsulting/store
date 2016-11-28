<!-- Payments Popup Box -->
<?php
$available_gateways = wc_pos_get_available_payment_gateways();
//$enabled_gateways   = get_option( 'pos_enabled_gateways', array() ); 
?>
<div class="md-modal md-dynamicmodal md-menu full-width md-close-by-overlay" id="modal-order_payment">
    <div class="media-frame-menu">
        <div class="media-menu">
            <?php
                WC()->customer = new WC_Customer();
                WC()->cart = new WC_Cart();
                $title = '';
                if (!empty($available_gateways)) {
                    
                    foreach ($available_gateways as $i => $gateway) {
                        $class = '';
                        if($i == 0){
                            $title = $gateway->title;                            
                            $class = 'active';
                        }
                        ?>
                        <a href="#<?php echo $gateway->id; ?>" class="<?php echo $class; ?> payment_methods payment_method_<?php echo $gateway->id; ?>" data-bind="<?php echo $gateway->id; ?>">
                            <input id="payment_method_<?php echo $gateway->id; ?>" type="radio" class="select_payment_method" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>"  style="display: none;"/>
                            <?php echo $gateway->title; ?>
                        </a>
                        <?php
                    }
                }else{
                    $title = __('No available gateways', 'wc_point_of_sale');
                    ?>
                        <a href="#no_available_gateways" class="active payment_methods no_available_gateways" data-bind="no_available_gateways">
                            <?php _e('No available gateways', 'wc_point_of_sale'); ?>
                        </a>
                    <?php
                }
            ?>
        </div>               
    </div>
    <div class="md-content">
        <h1><span class="txt"><?php echo $title; ?></span><span class="md-close"></span></h1>
        <div>
            <div class="topaytop">
                <div class="topaytopin">
                    <span class="to-pay-total"><span id="show_total_amt"><?php echo wc_price(0); ?></span></span>
                </div>
                <input type="hidden" id="show_total_amt_inp">
            </div>
            <?php
                if (!empty($available_gateways)) {
                    $i = 0;
                    foreach ($available_gateways as $gateway) {                    
                    ?>
                    <div id="<?php echo $gateway->id; ?>" class="popup_section full-height" style="<?php echo $i == 0 ? 'display: block;' : ''; ?>">
                        
                        <div class="media-frame-wrap">
                            <div class="payment_box payment_method_<?php echo $gateway->id; ?>">
                            <?php
                            $i++;
                            if(  $gateway->id == 'pos_chip_pin' ){
                                ?>
                                <p><?php _e('Please process the payment using your chip & PIN device. The reference/order number for this order is below.', 'wc_point_of_sale'); ?></p>
                                <table class="chip_and_pin_order_number">
                                    <tr>
                                        <th><?php _e('Order Number', 'wc_point_of_sale'); ?></th>
                                        <td id="pos_chip_pin_order_id"></td>
                                    </tr>
                                </table>
                                <?php
                            } else if ( $gateway->has_fields() || $gateway->get_description() ) {
                                $gateway->payment_fields();                            
                            }
                            if($gateway->id == 'cod'){
                            ?>
                                <table class="tendered_change_cod">
                                    <tr>
                                        <th class="amount_tendered"><label><?php _e('Tendered', 'wc_point_of_sale'); ?></label></th>
                                        <td class="amount_tendered"><input name="amount_pay"  id="amount_pay_cod" type="text" class="txtpopamtfild"/></td>
                                    </tr>
                                    <tr>
                                        <th class="amount_change"><label><?php _e('Change', 'wc_point_of_sale'); ?></label></th>
                                        <td class="amount_change"><input name="amount_change" id="amount_change_cod" type="text" class="txtpopamtfild" readonly="readonly"/></td>
                                    </tr>
                                </table>
                                <div id="inline_amount_tendered"></div>
                                <span class="error_amount" style="color: #CC0000;"></span>
                            <?php
                            }
                            ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    } 
                }
            ?>
            <div class="clearfix"></div>
        </div>
        <?php if (!empty($available_gateways)) { ?>
        <div class="wrap-button">
            <input name="" type="button" class="button back_to_sale md-close" value="<?php _e('Back', 'wc_point_of_sale'); ?>"/>
            <input name="" type="button" class="button-primary go_payment " value="<?php _e('Pay', 'wc_point_of_sale'); ?>"/>
            <div id="payment_switch_wrap">
              <input type="checkbox" class="payment_switch" value="yes" name="payment_email_receipt" id="payment_email_receipt" data-animate="true" data-label-text="<span class='payment_email_receipt_switch_label'></span>" data-on-text="Yes" data-off-text="No" <?php echo $data['settings']['print_receipt'] ? 'checked="true"' : '';  ?> >

              <input type="checkbox" class="payment_switch" value="yes" name="payment_print_receipt" id="payment_switch" data-animate="true" data-label-text="<span class='payment_switch_label'></span>" data-on-text="Yes" data-off-text="No" <?php echo $data['settings']['email_receipt'] ? 'checked="true"' : '';  ?> >
            </div>
        </div>
        <?php } ?>
    </div>
</div>
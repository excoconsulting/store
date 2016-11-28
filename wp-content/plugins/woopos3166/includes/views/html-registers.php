<?php
/**
 * HTML for a view registers page in admin.
 *
 * @author   Actuality Extensions
 * @package  WoocommercePointOfSale/views
 * @since    0.1
 */

$admin_url = get_admin_url(get_current_blog_id(), '/');
if(isset($_SERVER['HTTP_REFERER'])){
    $ref = $_SERVER['HTTP_REFERER'];
    if( !empty($_SERVER['HTTPS']) && !empty($ref) && strpos($ref, 'https://') === false ){
        $admin_url = str_replace('https://', 'http://', $admin_url);
    }
}
?>

<div class="wrap" id="wc-pos-registers-edit">
    <h2>
        <?php echo $data['name']; ?>
        <a class="button tips" href="<?php echo $admin_url; ?>admin.php?page=wc_pos_registers" id="go_back_register" data-tip="<?php _e('Return To Registers', 'wc_point_of_sale'); ?>"><?php _e('Back', 'wc_point_of_sale'); ?></a>
        <a class="button tips" href="#" id="retrieve_sales" data-tip="<?php _e('Load Order', 'wc_point_of_sale'); ?>"><?php _e('Load', 'wc_point_of_sale'); ?></a>
        <?php if ( current_user_can( 'edit_private_shop_orders' ) ) { ?>
        <a class="button tips" href="<?php echo $admin_url; ?>edit.php?post_type=shop_order" id="orders_page" data-tip="<?php _e('Orders', 'wc_point_of_sale'); ?>"><?php _e('Orders', 'wc_point_of_sale'); ?></a>
        <?php } ?>
        <?php if ( current_user_can( 'manage_wc_point_of_sale' ) ) { ?>
        <a class="button tips" href="<?php echo $admin_url; ?>admin.php?page=wc_pos_settings" id="settings_page" data-tip="<?php _e('Settings', 'wc_point_of_sale'); ?>"><?php _e('Settings', 'wc_point_of_sale'); ?></a>
        <?php } ?>

        <button class="button ladda-button tips" data-spinner-color="#6d6d6d" id="sync_data" data-tip='<span id="last_sync_time"></span>'><span class="ladda-label"></span><?php _e('Sync', 'wc_point_of_sale'); ?></button>
        
        <?php
        if( get_option('wc_pos_disable_connection_status', 'no') != 'yes' ){
            ?>
            <div class="button offline-ui-up" id="offline_indication">
                <div class="offline-ui-content"></div>
                <a class="offline-ui-retry" href=""></a>
            </div>
            <?php
        }
        ?>
        
        <a class="button button-primary tips" style="float:right;" href="<?php echo $admin_url; ?>admin.php?page=wc_pos_registers&amp;close=<?php echo $data['ID']; ?>" id="close_register" data-tip="<?php _e('Close Register', 'wc_point_of_sale'); ?>">
        </a>
        <?php if( get_option('wc_pos_lock_screen') == 'yes' ) { ?>
        <a class="button tips" style="float:right;" href="#" id="lock_register" data-tip="<?php _e('Lock Register', 'wc_point_of_sale'); ?>">
        </a>
        <?php } ?>
        <?php $current_user = wp_get_current_user();      ?>
        <a class="pos_register_user_panel" href="<?php echo $admin_url;?>profile.php">
        	<span class="pos_register_user_image"><?php echo get_avatar( $current_user->ID, 64); ?></span>
        	<span class="pos_register_user_name"><?php echo $current_user->display_name; ?></span>
        </a>

    </h2>
    <?php
    ?>
    <div id="edit_wc_pos_registers">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">                
                <div id="postbox-container-2" class="postbox-container">
                    <div id="wc-pos-register-data" class="postbox ">
                        <div class="tbc">
                            <div class="tb">
                                <div class="hndle tbr">
                                    <div class="add_items">
                                        <input id="add_product_id" class="ajax_chosen_select_products_and_variations" data-placeholder="<?php _e('Search Products', 'wc_point_of_sale'); ?>" />
                                        
                                        <a class="tips" id="add_product_to_register" data-modal="modal-add_custom_product" data-tip="<?php _e('Add Custom Product', 'wc_point_of_sale'); ?>"><span></span></a>
                                        <?php if(get_option('woocommerce_calc_shipping') == 'yes') { ?>
                                        <a class="tips" id="add_shipping_to_register" data-modal="modal-add_custom_shipping" data-tip="<?php _e('Add Shipping', 'wc_point_of_sale'); ?>">
                                            <span></span>
                                        </a>
                                        <?php } ?>
                                    </div>
                                    <span class="clearfix"></span>
                                </div>
                                <div class="inside tbr">
                                    <div class="tb">
                                        <div class="woocommerce_order_items_wrapper tbr">
                                            <div class="tbc" id="woocommerce_order_items-container">
                                                <table class="woocommerce_order_items" cellspacing="0" cellpadding="0">
                                                    <thead>
                                                        <tr>
	                                                        <th class="quantity"><?php _e('Qty', 'wc_point_of_sale'); ?></th>
                                                            <th colspan="3" class="item"><?php _e('Product', 'wc_point_of_sale'); ?></th>
                                                            <th class="line_cost"><?php _e('Cost', 'wc_point_of_sale'); ?></th>                                                            
                                                            <th class="line_cost_total"><?php _e('Total', 'wc_point_of_sale'); ?></th>
                                                            <th class="line_remove">&nbsp;</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="order_items_list">
                                                        <?php
                                                        $order = new WC_Order($data['order_id']);
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="wc_pos_register_subtotals tbr">
                                            <table class="woocommerce_order_items" cellspacing="0" cellpadding="0">
                                                <tr id="tr_order_subtotal_label">
                                                    <th class="subtotal_label"><?php _e('Subtotal', 'wc_point_of_sale'); ?></th>
                                                    <td class="subtotal_amount">
                                                        <span id="subtotal_amount"><?php echo wc_price(0); ?></span>
                                                    </td>
                                                </tr>
                                                <?php /********************************/ ?>
                                                <?php
                                                if(isset($detail_data['default_shipping_method']) && $detail_data['default_shipping_method'] != ''){
                                                ?>
                                                <tr class="shipping_methods_register" style="display: table-row;">
                                                <?php
                                                }else{
                                                ?>
                                                <tr class="shipping_methods_register">
                                                <?php } ?>
                                                    <th>
                                                    <?php
                                                    if(isset($detail_data['default_shipping_method']) && $detail_data['default_shipping_method'] != ''){
                                                        _e( 'Shipping and Handling', 'woocommerce' );                                            
                                                    }
                                                    ?>
                                                    </th>
                                                    <td>
                                                    <?php
                                                    if(isset($detail_data['default_shipping_method']) && $detail_data['default_shipping_method'] != ''){
                                                        $chosen_method = $detail_data['default_shipping_method'];
                                                        $shipping_methods = WC()->shipping->load_shipping_methods();
                                                    ?>
                                                        <select name="shipping_method[0]" data-index="0" id="shipping_method_0" class="shipping_method">
                                                        <option value="no_shipping" <?php selected( 'no_shipping', $chosen_method ); ?> data-cost="0"><?php _e('No Shipping','wc_point_of_sale' ); ?></option>
                                                        <?php
                                                        foreach ($shipping_methods as $key => $method) {
                                                            ?>
                                                            <option value="<?php echo esc_attr( $method->id ); ?>" <?php selected( $method->id, $chosen_method ); ?> data-cost="<?php echo isset($method->cost) ? $method->cost : 0; ?>"><?php echo $method->get_title(); ?> <?php echo isset($method->cost) ? wc_price($method->cost) : ''; ?></option>
                                                            <?php
                                                        }
                                                        ?>
                                                        </select>
                                                    <?php
                                                    }
                                                    ?>
                                                    </td>
                                                </tr>
                                                <?php
                                                /********************************/ 
                                                if( wc_pos_tax_enabled() ){
                                                ?>
                                                <tr class="tax_row">
                                                    <td colspan="2" class="tax_col"><table></table></td>
                                                    <!-- <th class="tax_label"><?php _e('Tax', 'wc_point_of_sale'); ?></th>
                                                    <td class="tax_amount"><strong id="tax_amount"></strong></td> -->
                                                </tr>
                                                <?php
                                                }

                                                 if ($d = $order->get_total_discount()) { ?>
                                                    <tr id="tr_order_discount">
                                                        <th class="total_label"><?php _e('Order Discount', 'wc_point_of_sale'); ?>
            	                                            <span id="span_clear_order_discount"></span>
                                                        </th>
                                                        <td class="total_amount">
                                                            <input type="hidden" value="<?php echo $d; ?>" id="order_discount" name="order_discount">
                                                            <span id="formatted_order_discount"><?php echo wc_price($d, array('currency' => $order->get_order_currency())); ?></span>
                                                            
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                                <tr id="tr_order_total_label">
                                                    <th class="total_label"><?php _e('Total', 'wc_point_of_sale'); ?></th>
                                                    <td class="total_amount"><strong id="total_amount"><?php echo wc_price(0); ?></strong></td>
                                                </tr>
                                                
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="wc-pos-customer-data" class="postbox ">
                        <div class="hndle">
                            <div class="add_items">
                                <input id="customer_user" class="ajax_chosen_select_customer" data-placeholder="<?php _e('Search Customers', 'wc_point_of_sale'); ?>" autocompleate="off"/>
                                <a class="tips" id="add_customer_to_register" type="button" data-tip="<?php _e('Add Customer', 'wc_point_of_sale'); ?>"><span><?php _e('', 'wc_point_of_sale'); ?></span></a>
                            </div>
                            <span class="clearfix"></span>
                        </div>
                        <div class="inside">
                            <div class="woocommerce_order_items_wrapper">
                                <table class="woocommerce_order_items" cellspacing="0" cellpadding="0">
                                    <thead>
                                        <tr>
                                            <th class="customer" colspan="2"><?php _e('Customer Name', 'wc_point_of_sale'); ?></th>
                                            <?php if( isset( $GLOBALS['wc_points_rewards'] ) ){
                                                global $wc_points_rewards;
                                                $points_label   = $wc_points_rewards->get_points_label( 2 );?>
                                                <th width="5%" class="customer_points"><?php echo $points_label; ?></th>
                                            <?php } ?>
                                            <th width="1%">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody id="customer_items_list">
                                        <?php
                                        $user_to_add = absint($data['default_customer']);
                                        pos_get_user_html($user_to_add);
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="postbox-container-1" class="postbox-container">
                    <div id="wc-pos-register-grids" class="postbox ">
                        <div class="tbc">
                        <?php
                        $pos_layout = get_option('woocommerce_pos_second_column_layout', 'product_grids');
                        
                        if($pos_layout == 'product_grids') :
                            $grid_id = $data['grid_template'];
                            if($grid_id == 'all'){
                                ?>
                                <h3 class="hndle">
                                    <span id="wc-pos-register-grids-title"><?php _e('All Products', 'wc_point_of_sale' ); ?></span>
                                    <i class="close_product_grids"></i>
                                </h3>
                                <?php
                            }else if($grid_id == 'categories'){
                                ?>
                                <h3 class="hndle">
                                    <span id="wc-pos-register-grids-title" class="cat_title" data-parent="0"><?php _e('Categories', 'wc_point_of_sale' ); ?></span>
                                    <i class="close_product_grids"></i>
                                </h3>
                                <?php
                            }else{
                            $grids_single_record = wc_point_of_sale_tile_record($grid_id);
                            $grids_all_record    = wc_point_of_sale_get_all_grids($grid_id);
                                ?>
                                <h3 class="hndle">
                                    <span id="wc-pos-register-grids-title"><?php if(!empty($grids_single_record)) _e( ucfirst($grids_single_record[0]->name).' Layout', 'wc_point_of_sale' ) ?></span>
                                    <i class="close_product_grids"></i>
                                </h3>
                                <?php 
                            } ?>
                            <div class="inside" id="grid_layout_cycle"></div>
                            <div class="previous-next-toggles">
                                <span class="previous-grid-layout tips" data-tip="<?php _e('Previous', 'wc_point_of_sale'); ?>"></span>
                                <div id="nav_layout_cycle_wrap">
                                    <div id="nav_layout_cycle"></div>
                                </div>
                                <span class="next-grid-layout tips" data-tip="<?php _e('Next', 'wc_point_of_sale'); ?>"></span>
                            </div>
                            <?php
                         else: ?>
                            <div class="inside" id="grid_layout_cycle">
                            <?php if($pos_layout == 'company_image'){ 
                                $woocommerce_pos_company_logo = get_option('woocommerce_pos_company_logo', '');
                                $src = '';
                                if(!empty($woocommerce_pos_company_logo) ){
                                    $src = wp_get_attachment_image_src( $woocommerce_pos_company_logo, 'full' );
                                    $src = $src[0];
                                }
                                ?>
                                <div class="grid_logo" >
                                    <img src="<?php echo $src; ?>" alt="">
                                </div>
                            <?php } elseif($pos_layout == 'text'){ ?>
                                <div class="grid_text" >
                                    <?php echo get_option('woocommerce_pos_register_layout_text', ''); ?>                                
                                </div>
                            <?php } elseif ($pos_layout == 'company_image_text'){
                                $woocommerce_pos_company_logo = get_option('woocommerce_pos_company_logo', '');
                                $src = '';
                                if(!empty($woocommerce_pos_company_logo) ){
                                    $src = wp_get_attachment_image_src( $woocommerce_pos_company_logo, 'full' );
                                    $src = $src[0];
                                }
                                ?>
                                <div class="grid_logo" style="height: 33%; ">
                                    <img src="<?php echo $src; ?>" alt="">
                                </div>
                                <div class="grid_text" style="height: 67%; ">
                                    <?php echo get_option('woocommerce_pos_register_layout_text', ''); ?>                                
                                </div>
                            <?php } ?>
                            </div>                            
                        <?php
                        endif; ?>
                        </div>
                    </div>
                    <div id="wc-pos-register-buttons" class="postbox ">
                        <div class="tbc">
                            <div class="tb">
                                <div class="tbr">
                                    <div class="button tips wc_pos_show_tiles tbc" type="button" data-tip="<?php _e('Show Tiles', 'wc_point_of_sale'); ?>">
                                    </div>
                                    <div class="button tips wc_pos_register_void tbc" type="button" data-tip="<?php _e('Void Order', 'wc_point_of_sale'); ?>">
                                    </div>
                                    <div class="button tips wc_pos_register_save" type="submit" data-tip="<?php _e('Save Order', 'wc_point_of_sale'); ?>">
                                    </div>
                                    <div class="button tips wc_pos_register_notes tbc" type="button" data-tip="<?php _e('Add A Note', 'wc_point_of_sale'); ?>">
                                    </div>
                                    <?php 
                                    $discount = esc_attr( get_user_meta( get_current_user_id(), 'discount', true ) );
                                    if($discount != 'disable'): ?>
                                        <div class="button tips wc_pos_register_discount tbc" type="button" data-tip="<?php _e('Apply Discount', 'wc_point_of_sale'); ?>">
                                        </div>
                                    <?php endif; ?>
                                    <div class="button tips wc_pos_register_pay tbc" type="button" data-tip="<?php _e('Accept Payment', 'wc_point_of_sale'); ?>"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>        
    </div>       

</div>
<?php
#var_dump($data);
?>
<script>
    var change_user   = <?php echo json_encode( isChangeUserAfterSale( $data['ID'] ) ); ?>;
    var note_request  = <?php echo json_encode( isNoteRequest( $data['ID'] ) ); ?>;
    var print_receipt = <?php echo json_encode( isPrintReceipt( $data['ID'] ) ); ?>;
    var email_receipt = <?php echo absint( $data['settings']['email_receipt'] ); ?>;
    
</script>
<style>
    .keypad-popup{
        z-index: 999;
        position: fixed !important;
        bottom: 0 !important;
        top: inherit !important;
    }
</style>
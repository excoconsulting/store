<div id="sale_report_popup_inner">
    <table>
        <tr>
            <td class="first-col"><?php _e('Register name:', 'wc_point_of_sale'); ?></td>
            <td><strong><?php echo $data['name']; ?></strong></td>
        </tr>
        <tr>
            <td class="first-col"><?php _e('Outlet name:', 'wc_point_of_sale'); ?></td>
            <td><strong><?php echo $outlet; ?></strong></td>
        </tr>
        <tr>
            <td class="first-col"><?php _e('Opened:', 'wc_point_of_sale'); ?></td>
            <td><strong><?php 
                echo date_i18n( __( 'jS F Y', 'woocommerce' ), strtotime( $data['opened'] ) ) . "\n";
                _e(' at ', 'wc_point_of_sale');
                echo date_i18n( __( 'g:i:s A', 'woocommerce' ),strtotime( $data['opened'] ) )  . "\n";
                ?></strong></td>
        </tr>
        <tr>
            <td class="first-col"><?php _e('Closed:', 'wc_point_of_sale'); ?></td>
            <td><strong><?php 
                echo date_i18n( __( 'jS F Y', 'woocommerce' ), strtotime( $data['closed'] ) ) . "\n";
                _e(' at ', 'wc_point_of_sale');
                echo date_i18n( __( 'g:i:s A', 'woocommerce' ),strtotime( $data['closed'] ) )  . "\n";
                ?></strong></td>
        </tr>
    </table>
    <h3><?php _e('Sales', 'wc_point_of_sale'); ?></h3>
    <table class="wp-list-table widefat fixed posts">
        <thead>
            <tr>
                <th class="manage-column column-order_customer" scope="col">
                    <?php _e('Order', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order-date" scope="col">
                    <?php _e('Date', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order-time" scope="col">
                    <?php _e('Time', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order_total" style="width: 25%;" scope="col">
                    <?php _e('Total', 'wc_point_of_sale'); ?>
                </th>
            </tr>
        </thead>
        <?php
            global $wpdb;
            $canceled_orders = array();
            $saved_orders    = array();
            $report_opened = $data['opened'];
            $report_closed = $data['closed'];

            $save_order_status = get_option( 'wc_pos_save_order_status', 'pending' );
            $save_order_status = 'wc-' === substr( $save_order_status, 0, 3 ) ? substr( $save_order_status, 3 ) : $save_order_status;

            $sql = "SELECT ID, post_status FROM {$wpdb->posts}
                INNER JOIN {$wpdb->postmeta} reg_id
    ON ( reg_id.post_id = {$wpdb->posts}.ID AND reg_id.meta_key = 'wc_pos_id_register' AND reg_id.meta_value = $rg_id )

    WHERE {$wpdb->posts}.post_type='shop_order' AND ({$wpdb->posts}.post_date BETWEEN '$report_opened' AND '$report_closed') 
            ";
            $results = $wpdb->get_results($sql);
            $payment_methods = array();
        ?>
        <tbody>
            <?php if($results){
                foreach ($results as $value) {
                    if( $value->post_status == 'wc-cancelled' ){
                        $canceled_orders[] = $value->ID;
                        continue;
                    } 
                    if( $value->post_status == 'wc-'.$save_order_status ){
                        $saved_orders[] = $value->ID;
                        continue;
                    } 
                    $the_order = new WC_Order( $value->ID );
                    ?>
                    <tr>
                        <td>
                        <?php

                        echo '<div class="tips" >';

                        if ( $the_order->user_id ) {
                            $user_info = get_userdata( $the_order->user_id );
                        }

                        if ( ! empty( $user_info ) ) {

                            $username = '<a href="user-edit.php?user_id=' . absint( $user_info->ID ) . '">';

                            if ( $user_info->first_name || $user_info->last_name ) {
                                $username .= esc_html( ucfirst( $user_info->first_name ) . ' ' . ucfirst( $user_info->last_name ) );
                            } else {
                                $username .= esc_html( ucfirst( $user_info->display_name ) );
                            }

                            $username .= '</a>';

                        } else {
                            if ( $the_order->billing_first_name || $the_order->billing_last_name ) {
                                $username = trim( $the_order->billing_first_name . ' ' . $the_order->billing_last_name );
                            } else {
                                $username = __( 'Guest', 'woocommerce' );
                            }
                        }

                        printf( __( '%s by %s', 'woocommerce' ), '<a href="' . admin_url( 'post.php?post=' . absint( $value->ID ) . '&action=edit' ) . '"><strong>' . esc_attr( $the_order->get_order_number() ) . '</strong></a>', $username );

                        if ( $the_order->billing_email ) {
                            echo '<small class="meta email"><a href="' . esc_url( 'mailto:' . $the_order->billing_email ) . '">' . esc_html( $the_order->billing_email ) . '</a></small>';
                        }

                        echo '</div>';
                        ?>
                        </td>
                        <td>
                            <?php
                                echo date_i18n( __( 'jS F Y', 'woocommerce' ), strtotime( $the_order->order_date ) ) . "\n";
							?>
                        </td>	                             
                        <td>
                            <?php
                              echo date_i18n( __( 'g:i:s A', 'woocommerce' ), strtotime( $the_order->order_date ) )  . "\n";
                              ?>  
                        </td>
                        <td><?php 
                            echo esc_html( strip_tags( $the_order->get_formatted_order_total() ) );

                            if ( $the_order->payment_method_title ) {
                                if( !isset($payment_methods[$the_order->payment_method_title]) )
                                    $payment_methods[$the_order->payment_method_title] = $the_order->get_total();
                                else
                                    $payment_methods[$the_order->payment_method_title] += $the_order->get_total();
                            }
                         ?>
                         </td>
                    </tr>
                    <?php
                }                                   
            }else{
                echo '<tr><td colspan="2"> No sales </td></tr>';    
            } ?>
        </tbody>
    </table>

    <?php if( !empty($canceled_orders) ){ ?>
    <h3><?php _e('Cancelled', 'wc_point_of_sale'); ?></h3>
    <table class="wp-list-table widefat fixed posts">
        <thead>
            <tr>
                <th class="manage-column column-order_customer" scope="col">
                    <?php _e('Order', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order-date" scope="col">
                    <?php _e('Date', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order-time" scope="col">
                    <?php _e('Time', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order_total" style="width: 25%;" scope="col">
                    <?php _e('Total', 'wc_point_of_sale'); ?>
                </th>
            </tr>
        </thead>
        <tbody>
               <?php  foreach ($canceled_orders as $ID) {
                    $the_order = new WC_Order( $ID );
                    ?>
                    <tr>
                        <td>
                        <?php

                        echo '<div class="tips" >';

                        if ( $the_order->user_id ) {
                            $user_info = get_userdata( $the_order->user_id );
                        }

                        if ( ! empty( $user_info ) ) {

                            $username = '<a href="user-edit.php?user_id=' . absint( $user_info->ID ) . '">';

                            if ( $user_info->first_name || $user_info->last_name ) {
                                $username .= esc_html( ucfirst( $user_info->first_name ) . ' ' . ucfirst( $user_info->last_name ) );
                            } else {
                                $username .= esc_html( ucfirst( $user_info->display_name ) );
                            }

                            $username .= '</a>';

                        } else {
                            if ( $the_order->billing_first_name || $the_order->billing_last_name ) {
                                $username = trim( $the_order->billing_first_name . ' ' . $the_order->billing_last_name );
                            } else {
                                $username = __( 'Guest', 'woocommerce' );
                            }
                        }

                        printf( __( '%s by %s', 'woocommerce' ), '<a href="' . admin_url( 'post.php?post=' . absint( $value->ID ) . '&action=edit' ) . '"><strong>' . esc_attr( $the_order->get_order_number() ) . '</strong></a>', $username );

                        if ( $the_order->billing_email ) {
                            echo '<small class="meta email"><a href="' . esc_url( 'mailto:' . $the_order->billing_email ) . '">' . esc_html( $the_order->billing_email ) . '</a></small>';
                        }

                        echo '</div>';
                        ?>
                        </td>
                        <td>
                            <?php
                                echo date_i18n( __( 'jS F Y', 'woocommerce' ), strtotime( $the_order->order_date ) ) . "\n";
                            ?>
                        </td>                                
                        <td>
                            <?php
                              echo date_i18n( __( 'g:i:s A', 'woocommerce' ), strtotime( $the_order->order_date ) )  . "\n";
                              ?>  
                        </td>
                        <td><?php 
                            echo esc_html( strip_tags( $the_order->get_formatted_order_total() ) );

                            if ( $the_order->payment_method_title ) {
                                if( !isset($payment_methods[$the_order->payment_method_title]) )
                                    $payment_methods[$the_order->payment_method_title] = $the_order->get_total();
                                else
                                    $payment_methods[$the_order->payment_method_title] += $the_order->get_total();
                            }
                         ?>
                         </td>
                    </tr>
                    <?php
                }  ?>                                  
        </tbody>
    </table>
    <?php } ?>
    <?php if( !empty($saved_orders) ){ ?>
    <h3><?php _e('Saved', 'wc_point_of_sale'); ?></h3>
    <table class="wp-list-table widefat fixed posts">
        <thead>
            <tr>
                <th class="manage-column column-order_customer" scope="col">
                    <?php _e('Order', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order-date" scope="col">
                    <?php _e('Date', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order-time" scope="col">
                    <?php _e('Time', 'wc_point_of_sale'); ?>
                </th>
                <th class="manage-column column-order_total" style="width: 25%;" scope="col">
                    <?php _e('Total', 'wc_point_of_sale'); ?>
                </th>
            </tr>
        </thead>
        <tbody>
               <?php  foreach ($saved_orders as $ID) {
                    $the_order = new WC_Order( $ID );
                    ?>
                    <tr>
                        <td>
                        <?php

                        echo '<div class="tips" >';

                        if ( $the_order->user_id ) {
                            $user_info = get_userdata( $the_order->user_id );
                        }

                        if ( ! empty( $user_info ) ) {

                            $username = '<a href="user-edit.php?user_id=' . absint( $user_info->ID ) . '">';

                            if ( $user_info->first_name || $user_info->last_name ) {
                                $username .= esc_html( ucfirst( $user_info->first_name ) . ' ' . ucfirst( $user_info->last_name ) );
                            } else {
                                $username .= esc_html( ucfirst( $user_info->display_name ) );
                            }

                            $username .= '</a>';

                        } else {
                            if ( $the_order->billing_first_name || $the_order->billing_last_name ) {
                                $username = trim( $the_order->billing_first_name . ' ' . $the_order->billing_last_name );
                            } else {
                                $username = __( 'Guest', 'woocommerce' );
                            }
                        }

                        printf( __( '%s by %s', 'woocommerce' ), '<a href="' . admin_url( 'post.php?post=' . absint( $value->ID ) . '&action=edit' ) . '"><strong>' . esc_attr( $the_order->get_order_number() ) . '</strong></a>', $username );

                        if ( $the_order->billing_email ) {
                            echo '<small class="meta email"><a href="' . esc_url( 'mailto:' . $the_order->billing_email ) . '">' . esc_html( $the_order->billing_email ) . '</a></small>';
                        }

                        echo '</div>';
                        ?>
                        </td>
                        <td>
                            <?php
                                echo date_i18n( __( 'jS F Y', 'woocommerce' ), strtotime( $the_order->order_date ) ) . "\n";
                            ?>
                        </td>                                
                        <td>
                            <?php
                              echo date_i18n( __( 'g:i:s A', 'woocommerce' ), strtotime( $the_order->order_date ) )  . "\n";
                              ?>  
                        </td>
                        <td><?php 
                            echo esc_html( strip_tags( $the_order->get_formatted_order_total() ) );

                            if ( $the_order->payment_method_title ) {
                                if( !isset($payment_methods[$the_order->payment_method_title]) )
                                    $payment_methods[$the_order->payment_method_title] = $the_order->get_total();
                                else
                                    $payment_methods[$the_order->payment_method_title] += $the_order->get_total();
                            }
                         ?>
                         </td>
                    </tr>
                    <?php
                }  ?>                                  
        </tbody>
    </table>
    <?php } ?>

    <?php if(!empty($payment_methods)): ?>
        <h3><?php _e('Payments', 'wc_point_of_sale'); ?></h3>
        <table class="wp-list-table widefat fixed posts">
            <thead>
                <tr>
                    <th class="manage-column column-payment_type" scope="col">
                        <?php _e('Type', 'wc_point_of_sale'); ?>
                    </th>
                    <th class="manage-column column-amount" scope="col">
                        <?php _e('Amount', 'wc_point_of_sale'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payment_methods as $name => $amount) { ?>
                    <tr>
                        <td><?php echo $name; ?></td>
                        <td><?php echo wc_price($amount); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>
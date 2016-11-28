<html>
<head>
  <meta charset="utf-8">
  <title><?php _e( 'Receipt', 'woocommerce-point-of-sale' ); ?></title>
  <style>
	  	
	  	@media print {
		  	body.pos_receipt, html {
			  	min-width: 100%;
			    width: 100%;
			    margin: 0;
			    padding: 0;
			}
			@page {
			  	margin: 0;
	  		}
	  	}
		body.pos_receipt, table.order-info, table.receipt_items, table.customer-info, #pos_receipt_title, #pos_receipt_address, #pos_receipt_contact, #pos_receipt_header, #pos_receipt_footer, #pos_receipt_tax, #pos_receipt_info, #pos_receipt_items {
			font-family: 'Arial', sans-serif;
			line-height: 1.4;
			font-size: 14px;
			background: transparent;
			color: #000;
			box-shadow: none;
			text-shadow: none;
		}
		#pos_receipt_logo {
			text-align: center;
		}
		#print_receipt_logo {
		  	height: 70px;
		  	width: auto;
		}
		body.pos_receipt h1,
		body.pos_receipt h2,
		body.pos_receipt h3,
		body.pos_receipt h4,
		body.pos_receipt h5,
		body.pos_receipt h6 {
			margin: 0;
		}
		table.customer-info, table.order-info, table.receipt_items {
			width: 100%;
			border-collapse: collapse;
			border-spacing: 0;
		}
		table.customer-info tr, table.order-info tr, table.receipt_items tr {
			border-bottom: 1px solid #eee;
		}
		table.customer-info th, table.order-info th, table.receipt_items th,
		table.customer-info td, table.order-info td, table.receipt_items td {
			padding: 8px 10px;
			vertical-align: top;
		}
		table.order-info {
			border-top: 2px solid #000;
		}
		table.customer-info th,	
		table.order-info th {
			text-align: left;
			width: 40%;
		}
		table.receipt_items tr .column-product-image {
			text-align: center;
		    white-space: nowrap;
		    width: 52px;
		}
		table.receipt_items .column-product-image img{
			height: auto;
		    margin: 0;
		    max-height: 40px;
		    max-width: 40px;
		    vertical-align: middle;
		    width: auto;
		}
		table.receipt_items thead tr {
			border-bottom: 2px solid #000;
		}
		table.receipt_items {
			border-bottom: 2px solid #000;
		}
		table.receipt_items thead th {
			text-align: left;
		}
		table.receipt_items tfoot th {
			text-align: right;
		}
		#pos_customer_info, #pos_receipt_title, #pos_receipt_logo, #pos_receipt_contact, #pos_receipt_tax, #pos_receipt_header, #pos_receipt_info, #pos_receipt_items {
			margin-bottom: 1em;
		}
		#pos_receipt_header, #pos_receipt_title, #pos_receipt_footer {
			text-align: center;
		}
		#pos_receipt_title {
			font-weight: bold;
			font-size: 20px;
		}
		#pos_receipt_barcode {
			border-bottom: 2px solid #000;
		}
		.attribute_receipt_value {
			line-height: 1.5;
		}
  </style>
  
  <?php if($receipt_style){
  		?>
  		<style id="receipt_style">
  			<?php
  				foreach ($receipt_style as $style_key => $style) {
  					if ( isset($receipt_options[$style_key]) ){
  						$k = $receipt_options[$style_key];
  						if( isset( $style[$k] ) ){
  							echo $style[$k];
  						}
  					}
  				}
  			?>
  			
				<?php echo $receipt_options['custom_css']; ?>
  			@media print {
  			}
  		</style>
  		<?php
  	}
	?>
</head>
<body id="pos_receipt">
	<div id="pos_receipt_title">
		<?php if( $receipt_options['receipt_title']) { ?>
			<?php echo $receipt_options['receipt_title']; ?>
        <?php } ?>
	</div>
	<div id="pos_receipt_logo">
		<img src="<?php echo $attachment_image_logo[0]; ?>" id="print_receipt_logo" <?php echo (!$receipt_options['logo']) ? 'style="display: none;"' : ''; ?>>
	</div>
	<div id="pos_receipt_address">
		<strong> <?php bloginfo( 'name' ); ?>, <?php echo $outlet['name']; ?></strong>
		<?php 
		
		if( $receipt_options['print_outlet_address'] == 'yes') { ?>
			<br>
			<?php echo $outlet_address; ?>
		<?php } ?>
	</div>
	<div id="pos_receipt_contact">

		<?php if( $receipt_options['print_outlet_contact_details'] == 'yes') { ?>
		<?php if($outlet['social']['email']){
	        if($receipt_options['email_label']) echo $receipt_options['email_label']. ': ';
	        echo  $outlet['social']['email'].'<br>';}
	    ?>
	    <?php if($outlet['social']['phone']){
            if($receipt_options['telephone_label']) echo $receipt_options['telephone_label']. ': ';
            echo  $outlet['social']['phone'].'<br>';}
		?>
		<?php if($outlet['social']['fax']){
            if($receipt_options['fax_label']) echo $receipt_options['fax_label']. ': ';
            echo  $outlet['social']['fax'].'<br>';}
        ?>
        <?php if($outlet['social']['website']){
            if($receipt_options['website_label']) echo $receipt_options['website_label']. ': ';
            echo  $outlet['social']['website'];}
        ?>
        <?php  } ?>
	</div>
	<div id="pos_receipt_tax">
		<?php if( $receipt_options['print_tax_number'] == 'yes') { ?>
                <span id="print-tax_number_label"><?php echo $receipt_options['tax_number_label']. ': '; ?></span>
                <?php
                    $tax_number   = get_post_meta($order->id, 'wc_pos_order_tax_number', true);
                    if($tax_number == '')
                        echo isset($register['detail']['tax_number']) ? $register['detail']['tax_number']:'[tax-number]';
                    else
                        echo $tax_number;
                ?>
        <?php  } ?>
	</div>
	<div id="pos_receipt_header">
		<?php echo $receipt_options['header_text']; ?>
	</div>
	<div id="pos_receipt_info">
		<table class="order-info">
			<tbody>
				<?php if($receipt_options['order_number_label']) { ?>
				<tr>
					<th><?php echo $receipt_options['order_number_label']; ?></th>
					<td><?php echo $order->get_order_number(); ?></td>
				</tr>
				<?php } else { echo $order->get_order_number(); } ?>
				<?php if( $receipt_options['print_order_time'] == 'yes') { ?>
				<tr>
					<th><?php echo $receipt_options['order_date_label']; ?></th>
					<td><?php if( $receipt_options['order_date_label']){?>
					<?php } $order_date = explode(' ', $order->order_date); echo date_i18n( $receipt_options['order_date_format'], strtotime( $order_date[0] )); ?> at <?php echo $order_date[1]; ?>
					</td>
				</tr>
				<?php } ?>
				<?php if( $receipt_options['print_customer_name'] == 'yes'  && ( $order->billing_first_name || $order->billing_first_name ) ) { ?>
					<tr>
						<th><?php echo $receipt_options['customer_name_label']; ?></th>
						<td>
							<?php echo esc_html( $order->billing_first_name ); ?> <?php echo esc_html( $order->billing_last_name ); ?>
						</td>
					</tr>
				<?php } ?>
				<?php if( $receipt_options['print_customer_email'] == 'yes' && $order->billing_email) { ?>

				<tr>
					<th><?php echo $receipt_options['customer_email_label']; ?></th>
					<td><?php echo esc_html( $order->billing_email ); ?></td>
				</tr>
				<?php } ?>
				<?php if( $receipt_options['print_customer_ship_address'] == 'yes' && ( $order->shipping_address_1 )) { ?>
					<tr>
						<th><?php echo $receipt_options['customer_ship_address_label']; ?></th>
						<td>
							<?php echo ( $address = $order->get_formatted_shipping_address() ) ? $address : __( 'N/A', 'woocommerce' ); ?>
						</td>
					</tr>
				<?php } ?>
				<?php if( $receipt_options['print_server'] == 'yes') {
					$post_author = $order->post->post_author; 
					$served_by   = get_userdata($post_author);
					if( $served_by ){
						switch ($receipt_options['served_by_type']) {
							case 'nickname':
								$served_by_name = $served_by->nickname;
								break;
							case 'display_name':
								$served_by_name = $served_by->display_name;
								break;
							default:
								$served_by_name = $served_by->user_nicename;
								break;
						}
					}else{
						$served_by_name = get_post_meta($order->id, 'wc_pos_served_by_name', true);						
					}
				?>
				<tr>
					<th><?php echo $receipt_options['served_by_label']; ?></th>
					<td><?php echo $served_by_name; ?> on <?php echo $register_name; ?></td>
				</tr>
				<?php  } ?>
			</tbody>
		</table>
	</div>
	<div id="pos_receipt_items">
	    <table class="receipt_items">
	        <thead>
	            <tr>
	                <th><?php _e( 'Qty', 'wc_point_of_sale' ); ?></th>
	                <?php if ($receipt_options['show_image_product'] == 'yes'){ ?>
	                	<th class="column-product-image"></th>
                	<?php } ?>
	                <th><?php _e( 'Product', 'wc_point_of_sale' ); ?></th>
	                <th><?php _e( 'Cost', 'wc_point_of_sale' ); ?></th>
	                <th><?php _e( 'Total', 'wc_point_of_sale' ); ?></th>
	            </tr>
	        </thead>
	        <tbody>
	            <?php 
                    $items = $order->get_items( 'line_item' );
                    $_items       = array();
                    $_items_nosku = array();
                    $_items_sku   = array();
                    $_cart_subtotal = 0;
                    foreach ($items as $item_id => $item) {
	                        
	                        $_product  = $order->get_product_from_item( $item );
							$item_meta = $order->get_item_meta( $item_id );
	                        if($_product){
	                            $sku       =  $_product->get_sku();
	                        }else{
	                            $sku       =  '';
	                        }
	                        ob_start();
	                        ?>
	                        <tr>
	                            <td><?php echo $item['qty'] ;?></td>
	                            <?php if ($receipt_options['show_image_product'] == 'yes'){ ?>
				                	<td class="column-product-image">
				                	<?php
				                		$thumbnail = $_product ? apply_filters( 'woocommerce_admin_order_item_thumbnail', $_product->get_image( 'thumbnail', array( 'title' => '' ), false ), $item_id, $item ) : '';
										echo '<div class="wc-order-item-thumbnail">' . wp_kses_post( $thumbnail ) . '</div>';
									?>
				                	</td>
			                	<?php } ?>
	                            <td class="product-name" ><strong>
		                            <?php echo ( $_product && $_product->get_sku() ) ? esc_html( $_product->get_sku() ) . ' &ndash; ' : ''; ?>
		                            <?php echo $name = esc_html( $item['name'] ); ?></strong>
		                            <?php
									if ( $metadata = $order->has_meta( $item_id ) ) {
										$meta_list = array();
										foreach ( $metadata as $meta ) {

											// Skip hidden core fields
											if ( in_array( $meta['meta_key'], apply_filters( 'woocommerce_hidden_order_itemmeta', array(
												'_qty',
												'_tax_class',
												'_product_id',
												'_variation_id',
												'_line_subtotal',
												'_line_subtotal_tax',
												'_line_total',
												'_line_tax',
											) ) ) ) {
												continue;
											}

											// Skip serialised meta
											if ( is_serialized( $meta['meta_value'] ) ) {
												continue;
											}

											// Get attribute data
											if ( taxonomy_exists( wc_sanitize_taxonomy_name( $meta['meta_key'] ) ) ) {
												$term               = get_term_by( 'slug', $meta['meta_value'], wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
												$meta['meta_key']   = wc_attribute_label( wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
												$meta['meta_value'] = isset( $term->name ) ? $term->name : $meta['meta_value'];
											} else {
												$meta['meta_key']   = apply_filters( 'woocommerce_attribute_label', wc_attribute_label( $meta['meta_key'], $_product ), $meta['meta_key'] );
											}

											$meta_list[] = wp_kses_post( rawurldecode( $meta['meta_key'] ) ) . ': ' . wp_kses_post( make_clickable( rawurldecode( $meta['meta_value'] ) ) );
										}
										if(!empty($meta_list)){
											echo '<br> <span class="attribute_receipt_value">' . implode( "<br> ", $meta_list );
										}
									}
								?>
	                            </td>
	                            <td class="product-price" >
	                                <?php
	                                	$tax_display = 'incl' == $order->tax_display_cart ? true : false;
	                                    if ( isset( $item['line_total'] ) ) {
	                                        echo wc_price( $order->get_item_subtotal( $item, $tax_display, true ), array( 'currency' => $order->get_order_currency() ) );
	                                    }
	                                ?>
	                            </td>
	                            <td class="product-amount" >
	                            <?php  ?>
	                            <?php
									if ( isset( $item['line_total'] ) ) {
										echo $order->get_formatted_line_subtotal( $item );
									}

									if ( $refunded = $order->get_total_refunded_for_item( $item_id ) ) {
										echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_order_currency() ) ) . '</small>';
									}
								?>

	                            </td>
	                        </tr>
	                    <?php
	                    if(empty($sku)){
	                        $_items_nosku[$item_id] = $name;
	                    }else{
	                    	$_items_sku[$item_id] = $sku.$name;
	                    }

                        $_items[$item_id] = ob_get_contents();
	
	                    ob_end_clean();
                    }
                    asort($_items_sku);
                    foreach ($_items_sku as $key => $_item) {
                        echo $_items[$key];
                    }
                    asort($_items_nosku);
                    foreach ($_items_nosku as $key => $_item) {
                        echo $_items[$key];
                    }
	            ?>
	        </tbody>
	        <tfoot>
	            <?php
	                if ( $totals = $order->get_order_item_totals() ) {
	                    $i = 0;
	                    $total_order = 0;
	                    foreach ( $totals as $total_key => $total ) {
	                        if( $total_key == 'cart_subtotal' ){
	                            $total_label = __( 'Subtotal', 'wc_point_of_sale' );
	                        }
	                        elseif( $total_key == 'order_total' ){
	                            $total_label = '<span id="print-total_label">'.__( 'Total', 'wc_point_of_sale' ).'</span>';
	                            $total_order = $total['value'];
	                        }
	                        elseif( $total_key == 'discount' ){
	                            $total_label = __( 'Discount', 'wc_point_of_sale' );
	                        }
	                        elseif( $total_key == 'shipping' ){
	                            $total_label = __( 'Shipping', 'wc_point_of_sale' );
	                        }
	                        else{
	                            continue;
	                        }
	                        $i++;
	                        if( $total_key == 'order_total' ){
	                               // Tax for tax exclusive prices
	                            $tax_display = $order->tax_display_cart;
	                            if ( 'excl' == $tax_display ) {
	                                if ( get_option( 'woocommerce_tax_total_display' ) == 'itemized' ) {
	                                    foreach ( $order->get_tax_totals() as $code => $tax ) {
	                                        $total_rows[] = array(
	                                            'label' => $tax->label,
	                                            'value' => $tax->formatted_amount
	                                        );
	                                    }
	                                } else {
	                                    $total_rows[] = array(
	                                        'label' => WC()->countries->tax_or_vat(),
	                                        'value' => wc_price( $order->get_total_tax(), array('currency' => $order->get_order_currency()) )
	                                    );
	                                }
	                            }
	                            if(!empty($total_rows)){
	                                foreach ($total_rows as $row) {
	                                ?>
	                                <tr>
	                                	<?php if ($receipt_options['show_image_product'] == 'yes'){ ?>
						                	<th class="column-product-image"></th>
					                	<?php } ?>
	                                    <th scope="row" colspan="3">
	                                        <?php echo $row['label']; ?>  <span id="print-tax_label">
	                                        <?php if($preview) {
	                                                echo '('.$receipt_options['tax_label'].')';
	                                        } elseif($receipt_options['tax_label']){
	                                             echo '('.$receipt_options['tax_label'].')';
	                                         }?>
	                                        </span></th>
	                                    <td><?php echo $row['value']; ?></td>
	                                </tr>
	                                <?php
	                                }
	                            }
	                        }
	                        ?>
	                        <tr>
	                        	<?php if ($receipt_options['show_image_product'] == 'yes'){ ?>
				                	<th class="column-product-image"></th>
			                	<?php } ?>
	                            <th scope="row" colspan="3">
	                                <?php echo $total_label; ?>
	                            </th>
	                            <td>
	                                <?php echo $total['value']; ?>
	                            </td>
	                        </tr>
	                        <?php                        
	                    }
	                    ?>
	                    <tr>
	                    	<?php if ($receipt_options['show_image_product'] == 'yes'){ ?>
			                	<th class="column-product-image"></th>
		                	<?php } ?>
	                        <th scope="row" colspan="3">
	                            <?php echo $order->payment_method_title ;?> <span id="print-payment_label"><?php echo $receipt_options['payment_label']; ?></span>
	                        </th>
	                        <td>
	                            <?php
	                                $amount_pay = get_post_meta( $order->id, 'wc_pos_amount_pay', true );
	                                if($amount_pay){
	                                    echo wc_price( $amount_pay, array('currency' => $order->get_order_currency()) );
	                                }
	                                else{
	                                    echo $total_order;
	                                }
	                            ?>
	                        </td>
	                    </tr>
	                    <?php if( $order->payment_method == 'cod' ) { ?>
	                    <tr>
	                    	<?php if ($receipt_options['show_image_product'] == 'yes'){ ?>
			                	<th class="column-product-image"></th>
		                	<?php } ?>
	                        <th scope="row" colspan="3">
	                            <?php _e( 'Change', 'wc_point_of_sale' ); ?>
	                        </th>
	                        <td>
	                            <?php
	                                $amount_change = get_post_meta( $order->id, 'wc_pos_amount_change', true );
	                                if($amount_change){
	                                     echo wc_price( $amount_change, array('currency' => $order->get_order_currency()) );
	                                }
	                                else{
	                                    echo wc_price( 0, array('currency' => $order->get_order_currency()) );
	                                }
	                            ?>
	                        </td>
	                    </tr>
	                    <?php } ?>
	                     <?php if( $preview || $receipt_options['print_number_items'] == 'yes') { ?>
	                     <tr id="print_number_items">
	                     	<?php if ($receipt_options['show_image_product'] == 'yes'){ ?>
			                	<th class="column-product-image"></th>
		                	<?php } ?>
	                        <th scope="row" colspan="3">
	                            <span id="print-items_label"><?php echo $receipt_options['items_label']; ?></span>
	                        </th>
	                        <td>
	                            <?php  echo $order->get_item_count(); ?>
	                        </td>
	                    </tr>
	                    <?php  } ?>
	                    <?php
	                }
	            ?>
	        </tfoot>
	    </table>
	</div>

	<div id="pos_customer_info">
		<table class="customer-info">
			<tbody>
				<?php if( $receipt_options['print_order_notes'] == 'yes' && $order->customer_note) { ?>
				<tr>
					<th><?php echo $receipt_options['order_notes_label']; ?></th>
					<td><?php echo wptexturize( $order->customer_note ); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div id="pos_receipt_barcode">
	    <center>
	         <?php if($receipt_options['print_barcode'] == 'yes'){ ?><p id="print_barcode"><img src="<?php echo  WC_POS()->plugin_url(). '/includes/lib/barcode/image.php?filetype=PNG&dpi=72&scale=2&rotation=0&font_family=Arial.ttf&font_size=12&thickness=30&start=NULL&code=BCGcode128&text='.str_replace("#", "", $order->get_order_number()); ?>" alt=""></p>
	        <?php } ?>
	    </center>
	</div>
	<div id="pos_receipt_footer">
	    <?php echo $receipt_options['footer_text']; ?>
	</div>
</body>
</html>
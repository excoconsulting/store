<style>
  	@media print {
	  	body.pos_receipt, html {
		  	min-width: 100% !important;
		    width: 100% !important;
		    margin: 0 !important;
		    padding: 0 !important;
		}
		@page {
		  	margin: 0 !important;
  		}
  	}
	body.pos_receipt, table.order-info, table.customer-info, table.receipt_items, #pos_receipt_title, #pos_receipt_address, #pos_receipt_contact, #pos_receipt_header, #pos_receipt_footer, #pos_receipt_tax, #pos_receipt_info, #pos_receipt_items {
		font-family: 'Arial', sans-serif !important;
		line-height: 1.4;
		font-size: 14px;
		background: transparent !important;
		color: #000 !important;
		box-shadow: none !important;
		text-shadow: none !important;
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
		font-size: 20px !important;
	}
	#pos_receipt_barcode {
		border-bottom: 2px solid #000;
	}
	.attribute_receipt_value {
		line-height: 1.5;
	}
</style>
<style id="receipt_style_tag">
</style>
<div id="pos_receipt_title">
	<?php echo $receipt_options['receipt_title']; ?>
</div>
<div id="pos_receipt_logo">
	<img src="<?php echo $attachment_image_logo[0]; ?>" id="print_receipt_logo" <?php echo (!$receipt_options['logo']) ? 'style="display: none;"' : ''; ?>>
</div>
<div id="pos_receipt_address">
	<strong> <?php bloginfo( 'name' ); ?>, <?php _e('Outlet Name', 'wc_point_of_sale'); ?></strong>
	<br>
	<span class="show_receipt_print_outlet_address"><?php _e('123 Woodlands Avenue', 'wc_point_of_sale'); ?><br><?php _e('Woodlands Peak', 'wc_point_of_sale'); ?><br><?php _e('W12 3OD', 'wc_point_of_sale'); ?></span>
</div>
<div id="pos_receipt_contact" class="show_receipt_print_outlet_contact_details">
	<span id="print-email_label"><?php echo $receipt_options['email_label']; ?></span><span class="colon">:</span> email@domain.com
    <br>
    <span id="print-telephone_label"><?php echo $receipt_options['telephone_label']; ?></span><span class="colon">:</span> 1234567890
	<br>
	<span id="print-fax_label"><?php echo $receipt_options['fax_label']; ?></span><span class="colon">:</span> 1234567890
    <br>
    <span id="print-website_label"><?php echo $receipt_options['website_label']; ?></span><span class="colon">:</span> www.shop.com
</div>
<div id="pos_receipt_tax">
	<span id="print-tax_number_label"><?php echo $receipt_options['tax_number_label']; ?></span><span class="colon">:</span> GB 123 4567 89
</div>
<div id="pos_receipt_header">
	<?php echo $receipt_options['header_text']; ?>
</div>
<div id="pos_receipt_info">
	<table class="order-info">
		<tbody>
			<tr>
				<th><span id="print-order_number_label"><?php echo $receipt_options['order_number_label']; ?></span></th>
				<td>WC-123</td>
			</tr>
			<tr id="print_order_time">
				<th><span id="print-order_date_label"><?php echo $receipt_options['order_date_label']; ?></span></th>
				<td><?php $order_date[] = '[date]'; $order_date[] = '[time]'; echo $order_date[0]; ?> at <?php echo $order_date[1]; ?></td>
			</tr>
			<tr id="print_customer_name">
				<th><span id="print-customer_name_label"><?php echo $receipt_options['customer_name_label']; ?></span></th>
				<td>John Doe</td>
			</tr>
			<tr id="print_customer_email">
				<th><span id="print-customer_email_label"><?php echo $receipt_options['customer_email_label']; ?></span></th>
				<td>john@doe.com</td>
			</tr>
			<tr id="print_customer_ship_address">
				<th><span id="print-customer_ship_address_label"><?php echo $receipt_options['customer_ship_address_label']; ?></span></th>
				<td>Sandra Doe<br>
					10 Street Road<br>
					City Name<br>
					Postcode<br>
					Country<br>
				</td>
			</tr>
			    <?php $current_user = wp_get_current_user(); ?>
			<tr id="print_server">
				<th> <span id="print-served_by_label"><?php echo $receipt_options['served_by_label']; ?></span></th>
				<td> <span id="print-served_by_type" data-nickname="<?php echo $current_user->user_nicename; ?>" data-display_name="<?php echo $current_user->display_name; ?>" data-username="<?php echo $current_user->user_login; ?>">
					<?php
					switch ($receipt_options['served_by_type']) {

						case 'nickname':
							echo $current_user->user_nicename;
							break;
						case 'display_name':
							echo $current_user->display_name;
							break;
						default:
							echo $current_user->user_login;
							break;
					}
					?>
					</span>
					 on [register-name]
				 </td>
			</tr>
		</tbody>
	</table>
</div>
<div id="pos_receipt_items">
    <table class="receipt_items">
        <thead>
            <tr>
                <th><?php _e( 'Qty', 'wc_point_of_sale' ); ?></th>
                <th class="column-product-image"></th>
                <th><?php _e( 'Product', 'wc_point_of_sale' ); ?></th>
                <th><?php _e( 'Cost', 'wc_point_of_sale' ); ?></th>
                <th><?php _e( 'Total', 'wc_point_of_sale' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>2</td>
                <td class="column-product-image">
                	<?php echo wc_placeholder_img(); ?>
                </td>
                <td><strong>PHONE – Mobile Phone</strong><br><span class="attribute_receipt_value">Size: 32GB</span><br><span class="attribute_receipt_value">Colour: Silver</span></td>
                <td>£59.00</td>
                <td>£118.00</td>
            </tr>
        </tbody>
         <tfoot>
            <tr>
            	<th class="column-product-image"></th>
                <th scope="row" colspan="3">
                    Subtotal
                </th>
                <td>£118.00</td>
            </tr>
            <tr>
            	<th class="column-product-image"></th>
                <th scope="row" colspan="3">
                    VAT <span id="print-tax_label">(Tax)</span>
                </th>
                <td>£23.60</td>
            </tr>
            <tr>
            	<th class="column-product-image"></th>
                <th scope="row" colspan="3">
                    Payment Type <span id="print-payment_label">Sales</span>
                </th>
                <td>£141.60</td>
            </tr>
            <tr>
            	<th class="column-product-image"></th>
                <th scope="row" colspan="3">
                    <span id="print-total_label">Total</span>
                </th>
                <td>£141.60</td>
            </tr>
             <tr>
             	<th class="column-product-image"></th>
                <th scope="row" colspan="3">
                    Change
                </th>
                <td>£0.00</td>
            </tr>
             <tr id="print_number_items">
             	<th class="column-product-image"></th>
                <th scope="row" colspan="3">
                   <span id="print-items_label">Number of Items</span>
                </th>
                <td>2</td>
            </tr>
         </tfoot>
    </table>
</div>
<div id="pos_customer_info">
	<table class="customer-info">
		<tbody>
			<tr id="print_order_notes">
				<th><span id="print-order_notes_label"><?php echo $receipt_options['order_notes_label']; ?></span></th>
				<td>Please deliver this tomorrow at 12pm. Thanks!</td>
			</tr>
		</tbody>
	</table>
</div>
<div id="pos_receipt_barcode">
    <center>
         <img src="<?php echo  WC_POS()->plugin_url(). '/includes/lib/barcode/image.php?filetype=PNG&dpi=72&scale=2&rotation=0&font_family=Arial.ttf&font_size=12&thickness=30&start=NULL&code=BCGcode128&text=WC-123'; ?>" alt="">
    </center>
</div>
<div id="pos_receipt_footer">
    <?php echo $receipt_options['footer_text']; ?>
</div>
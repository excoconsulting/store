<?php
/**
* WoocommercePointOfSale Functions
*
* @author   Actuality Extensions
* @package  WoocommercePointOfSale/Admin/Functions
* @since    0.1
*/

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function pos_admin_page()
{
	global $post_type;
	if($post_type == 'product')
		return true;
	$pos_pages = array(
		'wc_pos_settings',
		'wc_pos_barcodes',
		'wc_pos_receipts',
		'wc_pos_users',
		'wc_pos_tiles',
		'wc_pos_grids',
		'wc_pos_outlets',
		'wc_pos_registers',
		'wc_pos_stock_controller',
	);
	return isset($_GET['page']) && !empty($_GET['page']) && in_array($_GET['page'], $pos_pages) ;
}

function pos_tiles_admin_page()
{
	return isset($_GET['page']) && $_GET['page'] == 'wc_pos_tiles' ;
}

function pos_receipts_admin_page()
{
	return isset($_GET['page']) && $_GET['page'] == 'wc_pos_receipts' ;
}
function pos_barcodes_admin_page()
{
	return isset($_GET['page']) && $_GET['page'] == 'wc_pos_barcodes' ;
}
function pos_settings_admin_page()
{
	return isset($_GET['page']) && $_GET['page'] == 'wc_pos_settings' ;
}

function pos_shop_order_page()
{
	return isset($_GET['post_type']) && $_GET['post_type'] == 'shop_order' ;
}
/**
 * Output a text input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function wc_pos_text_input( $field ) {
	global $thepostid, $post, $woocommerce;

	$thepostid              = empty( $thepostid ) ? '' : $thepostid;
	$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = isset( $field['value'] ) ? $field['value'] : (!empty( $thepostid ) ? get_post_meta( $thepostid, $field['id'], true ) : '' );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';
	$data_type              = empty( $field['data_type'] ) ? '' : $field['data_type'];

	$field['wrapper_tag'] 		= isset( $field['wrapper_tag'] ) ? $field['wrapper_tag'] : 'div';
	$field['wrapper_label_tag'] 		= isset( $field['wrapper_label_tag'] ) ? $field['wrapper_label_tag'] : '%s';
	$field['wrapper_field_tag'] 		= isset( $field['wrapper_field_tag'] ) ? $field['wrapper_field_tag'] : '%s';

	switch ( $data_type ) {
		case 'price' :
			$field['class'] .= ' wc_input_price';
			$field['value']  = wc_format_localized_price( $field['value'] );
		break;
		case 'decimal' :
			$field['class'] .= ' wc_input_decimal';
			$field['value']  = wc_format_localized_decimal( $field['value'] );
		break;
	}

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) )
		foreach ( $field['custom_attributes'] as $attribute => $value )
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';

	$input = '<input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . implode( ' ', $custom_attributes ) . ' /> ';

	if ( ! empty( $field['description'] ) ) {

		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			$input .= '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
		} else {
			$input .= '<p class="description">' . $field['description'] . '</p>';
		}

	}

	$label = '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
	echo '<' . $field['wrapper_tag'] . ' class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">'. sprintf($field['wrapper_label_tag'], $label) . sprintf($field['wrapper_field_tag'], $input);


	echo '</' . $field['wrapper_tag'] . '>';
}


/**
 * Output a select input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function wc_pos_select( $field ) {
	global $thepostid, $post, $woocommerce;

	$thepostid 				= empty( $thepostid ) ? '' : $thepostid;
	$field['class'] 		= isset( $field['class'] ) ? $field['class'] : 'select short';
	$field['type'] 		   = isset( $field['type'] ) && $field['type'] == 'multiselect' ? 'multiple="multiple"' : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']           = isset( $field['value'] ) ? $field['value'] : (!empty( $thepostid ) ? get_post_meta( $thepostid, $field['id'], true ) : '' );
	$field['wrapper_tag'] 		= isset( $field['wrapper_tag'] ) ? $field['wrapper_tag'] : 'div';
	$field['wrapper_label_tag'] 		= isset( $field['wrapper_label_tag'] ) ? $field['wrapper_label_tag'] : '%s';
	$field['wrapper_field_tag'] 		= isset( $field['wrapper_field_tag'] ) ? $field['wrapper_field_tag'] : '%s';

	$name = esc_attr( $field['id'] );
	if( !empty($field['type'])){
		$name .= '[]';
	}

	$select = '<select id="' . esc_attr( $field['id'] ) . '" name="' . $name . '" class="' . esc_attr( $field['class'] ) . '" '.$field['type'].'>';
	foreach ( $field['options'] as $key => $value ) {
		if( is_array($field['value'])){
			$select .= '<option value="' . esc_attr( $key ) . '" ' . selected( true, in_array($key, $field['value']), false ) . '>' . esc_html( $value ) . '</option>';
		}else{
			$select .= '<option value="' . esc_attr( $key ) . '" ' . selected( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';
		}

	}
	$select .= '</select> ';

	if ( ! empty( $field['description'] ) ) {

		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			$select .= '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
		} else {
			$select .= '<p class="description">' .  $field['description']  . '</p>';
		}

	}

	$label = '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';

	echo '<' . $field['wrapper_tag'] . ' class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">' . sprintf($field['wrapper_label_tag'], $label) . sprintf($field['wrapper_field_tag'], $select);



	echo '</' . $field['wrapper_tag'] . '>';
}

/**
 * Output a radio input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function wc_pos_radio( $field ) {
	global $thepostid, $post, $woocommerce;

	$thepostid 				= empty( $thepostid ) ? '' : $thepostid;
	$field['class'] 		= isset( $field['class'] ) ? $field['class'] : 'select short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']           = isset( $field['value'] ) ? $field['value'] : (!empty( $thepostid ) ? get_post_meta( $thepostid, $field['id'], true ) : '' );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$field['wrapper_tag'] 		= isset( $field['wrapper_tag'] ) ? $field['wrapper_tag'] : 'div';
	$field['wrapper_label_tag'] 		= isset( $field['wrapper_label_tag'] ) ? $field['wrapper_label_tag'] : '%s';
	$field['wrapper_field_tag'] 		= isset( $field['wrapper_field_tag'] ) ? $field['wrapper_field_tag'] : '%s';

	$label = '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
	$inputs = '<ul class="wc-radios">';
	  foreach ( $field['options'] as $key => $value ) {

		$inputs .= '<li><label><input
			        		name="' . esc_attr( $field['name'] ) . '"
			        		value="' . esc_attr( $key ) . '"
			        		type="radio"
			        		class="' . esc_attr( $field['class'] ) . '"
			        		' . checked( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '
			        		/> ' . esc_html( $value ) . '</label>
    						</li>';
		}
		$inputs .= '</ul>';
		if ( ! empty( $field['description'] ) ) {

			if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
				$inputs .= '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
			} else {
				$inputs .= '<p class="description">' . $field['description'] . '</p>';
			}

		}

	echo '<' . $field['wrapper_tag'] . ' class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">' . sprintf($field['wrapper_label_tag'], $label) .  sprintf($field['wrapper_field_tag'], $inputs);


    echo '</' . $field['wrapper_tag'] . '>';
}

function pos_set_register_lock( $register_id ) {
	global $wpdb;

	$table_name = $wpdb->prefix . "wc_poin_of_sale_registers";

	$db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $register_id");
	
	if ( !$db_data)
		return false;

	if ( 0 == ($user_id = get_current_user_id()) )
		return false;

	$now = current_time( 'mysql' );
	
	$data['opened']     = $now;
	$data['_edit_last'] = $user_id;
	$rows_affected = $wpdb->update( $table_name, $data, array( 'ID' => $register_id ) );
	return array( $now, $user_id );
}

function pos_check_register_lock( $register_id ) {
	global $wpdb;

	$table_name = $wpdb->prefix . "wc_poin_of_sale_registers";

	$db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $register_id");

	if ( !$db_data)
		return false;

	$row = $db_data[0];

	$user = $row->_edit_last;

	if ( strtotime($row->opened) >= strtotime($row->closed) && $user != get_current_user_id() ){
		return $user;
	}
	return false;
}
function pos_check_register_is_open( $register_id ) {
	global $wpdb;

	$table_name = $wpdb->prefix . "wc_poin_of_sale_registers";

	$db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $register_id");

	if ( !$db_data)
		return false;

	$row = $db_data[0];

	if ($row->_edit_last > 0 && strtotime($row->opened) > strtotime($row->closed))
		return true;
	else
		return false;
}
function pos_check_user_can_open_register( $register_id ) {
	global $wpdb;

	$table_name = $wpdb->prefix . "wc_poin_of_sale_registers";

	$db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $register_id");

	if ( !$db_data)
		return false;

	$row = $db_data[0];

	if ( !$outlet = $row->outlet )
		return false;

	$value_user_meta = esc_attr( get_user_meta( get_current_user_id(), 'outlet', true ) );
	if($value_user_meta == $outlet) return true;
	
	return false;
}



function set_outlet_taxable_address($address){
  $register_id = 0;
  if(isset($_POST['register_id']) && !empty($_POST['register_id']) ) {
  	$register_id = absint($_POST['register_id']);
  }elseif(isset($_GET['page']) && $_GET['page'] == 'wc_pos_registers' && isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id']) && !empty($_GET['action']) ){
  	$register_id = absint($_GET['id']);
  }
  if($register_id) {
      $id_outlet = getOutletID($register_id);

      $outlet = WC_POS()->outlet()->get_data($id_outlet);
      $address_data = $outlet[0]['contact'];
      return array( $address_data['country'], $address_data['state'], $address_data['postcode'], $address_data['city'] );
  }
  else{
      return $address;
  }
}
function isChangeUserAfterSale($register_id = 0)
{	
	if($register_id){
		$register_data = WC_POS()->register()->get_data($register_id);
	    return $register_data[0]['settings']['change_user'] ? true : false;
	}
	return false;
}
function isPrintReceipt($register_id = 0)
{	
	if($register_id){
		$register_data = WC_POS()->register()->get_data($register_id);
	    return $register_data[0]['settings']['print_receipt'] ? true : false;
	}
	return false;
}
function isNoteRequest($register_id = 0)
{	
	if($register_id){
		$register_data = WC_POS()->register()->get_data($register_id);
	    return $register_data[0]['settings']['note_request'] ? true : false;
	}
	return false;
}
function isEmailReceipt($register_id = 0)
{	
	if($register_id){
		$register_data = WC_POS()->register()->get_data($register_id);
		if( $register_data[0]['settings']['email_receipt'] ){
		    return array(
		    	'receipt_template' => $register_data[0]['detail']['receipt_template'],
		    	'outlet' => $register_data[0]['outlet']
		    	);
    	}
    	return false;
	}
	return false;
}

function sentEmailReceipt($register_id, $order_id)
{
	$register_email_receipt = isEmailReceipt( $register_id );
	$order_email_receipt    = get_post_meta( $order_id, 'pos_payment_email_receipt', true );
	$email_notifications    = get_option('wc_pos_email_notifications');

	if($email_notifications == 'yes'){
		$mail = WC()->mailer();
	    $mail->emails['WC_Email_New_Order']->trigger( $order_id );
    }

 	if(!empty($order_email_receipt) ){
    	update_post_meta( $order_id, '_billing_email', $order_email_receipt );
    	$register_email_receipt = true;
    }
    if($register_email_receipt){
		$mail = WC()->mailer();
	    $mail->emails['WC_Email_Customer_Processing_Order']->trigger( $order_id );
    }

}
function pos_set_html_content_type() {
    return 'text/html';
  }

function isChangeUser($register_id = 0)
{	
	if($register_id){
		$register_data = WC_POS()->register()->get_data($register_id);
	    return $register_data[0]['settings']['email_receipt'];
	}
	return false;
}

function wc_pos_get_outlet_location( $id_register = 0 )
{
	$location = array();
	if( !$id_register && !isset($_GET['reg']) ) return $location;

	if( $id_register > 0){
		$data = WC_POS()->register()->get_data($id_register);
		$data = $data[0];
		$outlet_id = $data['outlet'];
		$outlet    = WC_POS()->outlet()->get_data($outlet_id);
	}else{
	
		$slug = $_GET['reg'];
		$data = WC_POS()->register()->get_data_by_slug($slug);
		$data = $data[0];
		$slug      = $data['slug'];
		$register  = $slug;
		$outlet_id = $data['outlet'];
		$outlet    = WC_POS()->outlet()->get_data($outlet_id);
	}
	if( $outlet ){
		$location = $outlet[0];
	}
	return $location;
}

function wc_pos_get_shop_location()
{
	return array(
				'country' 	=> WC()->countries->get_base_country(),
				'state' 	=> WC()->countries->get_base_state(),
				'postcode' 	=> WC()->countries->get_base_postcode(),
				'city' 		=> WC()->countries->get_base_city()
			);
}

function wc_pos_find_all_rates()
{
	global $wpdb;
	// Run the query
	
	$tax_class = '';
	$rates     = array();
	$sql = "SELECT tax_rates.*
			FROM {$wpdb->prefix}woocommerce_tax_rates as tax_rates
			LEFT OUTER JOIN {$wpdb->prefix}woocommerce_tax_rate_locations as locations ON tax_rates.tax_rate_id = locations.tax_rate_id
			LEFT OUTER JOIN {$wpdb->prefix}woocommerce_tax_rate_locations as locations2 ON tax_rates.tax_rate_id = locations2.tax_rate_id
			GROUP BY tax_rate_id
			ORDER BY tax_rate_priority, tax_rate_order
		";
	$found_rates = $wpdb->get_results( $sql );


	foreach ( $found_rates as $key_rate => $found_rate ) {
		
		$sql = "SELECT location_code FROM {$wpdb->prefix}woocommerce_tax_rate_locations WHERE tax_rate_id = {$found_rate->tax_rate_id} AND location_type = 'postcode' ";
		$found_postcodes = $wpdb->get_results( $sql );
		$postcode = array();
		if($found_postcodes)
			foreach ($found_postcodes as $code) {
				$postcode[] = $code->location_code;
			}

		$sql = "SELECT location_code FROM {$wpdb->prefix}woocommerce_tax_rate_locations WHERE tax_rate_id = {$found_rate->tax_rate_id} AND location_type = 'city' ";
		$found_postcodes = $wpdb->get_results( $sql );
		$city = array();
		if($found_postcodes)
			foreach ($found_postcodes as $code) {
				$city[] = $code->location_code;
			}

		$rates[ $found_rate->tax_rate_id ] = array(
			'rate'     => (float)$found_rate->tax_rate,
			'label'    => $found_rate->tax_rate_name,
			'shipping' => $found_rate->tax_rate_shipping ? 'yes' : 'no',
			'compound' => $found_rate->tax_rate_compound ? 'yes' : 'no',
			'country'  => $found_rate->tax_rate_country,
			'state'    => $found_rate->tax_rate_state,
			'city' 	   => implode(';', $city),
			'postcode' => implode(';', $postcode),
			'taxclass' => $found_rate->tax_rate_class,
			'priority' => $found_rate->tax_rate_priority
		);
	}

	return $rates;
}


function wc_pos_get_register($id)
{
	global $wpdb;
	$table_name = $wpdb->prefix . "wc_poin_of_sale_registers";
	$reg = $wpdb->get_row("SELECT * FROM $table_name WHERE ID = $id LIMIT 1");
	if(isset($reg))
		return $reg;
	else
		return false;
}
function getOutletID($reg_id = 0)
{
		global $wpdb;
		if(!$reg_id) return 0;
		
	  $db_data = wc_pos_get_register($reg_id);
		return $db_data->outlet;
}
function wc_pos_check_can_delete($type, $ids)
{
	global $wpdb;
	$table_reg = $wpdb->prefix . "wc_poin_of_sale_registers";
	switch ($type) {
		case 'outlet':
			if(is_array($ids)){
				foreach ($ids as $key => $id) {
					$result = $wpdb->get_results("SELECT ID FROM $table_reg WHERE outlet = $id");
					if($result)
						unset($ids[$key]);
				}
				if (!empty($ids)) {
	        $ids = implode(',', array_map('intval', $ids));
	        return "WHERE ID IN ($ids)";
				}
      }else{
      	$result = $wpdb->get_results("SELECT ID FROM $table_reg WHERE outlet = $ids");
				if(!$result)
        	return "WHERE ID = $ids";
      }
      return false;
			break;
		case 'grid':
			if(is_array($ids)){
					foreach ($ids as $key =>  $id) {
						$result = $wpdb->get_results("SELECT ID FROM $table_reg WHERE detail LIKE '%\"grid_template\":\"$id\"%' ");
						if($result)
							unset($ids[$key]);
					}

					if (!empty($ids))
						return $ids;
        }else{
        	$result = $wpdb->get_results("SELECT ID FROM $table_reg WHERE detail LIKE '%\"grid_template\":\"$ids\"%' ");
					if(!$result)
          	return $ids;
        }
      return false;
			break;
		case 'receipt':
			$default_receipt = (int)get_option('_pos_default_receipt');
			if(is_array($ids)){
					foreach ($ids as $key =>  $id) {
						if($default_receipt && $default_receipt == (int)$id ){
							unset($ids[$key]);
						}else{
							$result = $wpdb->get_results("SELECT ID FROM $table_reg WHERE detail LIKE '%\"receipt_template\":\"$id\"%' ");
							if($result)
								unset($ids[$key]);
						}
					}

					if (!empty($ids))
						return $ids;
        }else{
        	if($default_receipt && $default_receipt == (int)$ids ){
        		return false;
        	}
        	$result = $wpdb->get_results("SELECT ID FROM $table_reg WHERE detail LIKE '%\"receipt_template\":\"$ids\"%' ");
			if(!$result)
          	return $ids;
        }
      return false;
			break;
		
		default:
			return false;
			break;
	}
}

function pos_term_relationships()
{
	$relationships = array();	
	$hierarchy     = array();	
	$parents       = array();	
	
	$args  = array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all');			
	$terms = get_terms( 'product_cat', $args);

	$relationships[0] = pos_get_non_cat_products(); 
	$out_of_stock  = get_option('wc_pos_show_out_of_stock_products');
	$default_order = get_option('wc_pos_default_tile_orderby');

	if($terms){
		foreach ($terms as $term) {
			$term_id = (int)$term->term_id;
			$parent  = (int)$term->parent;
			if($parent == 0 ){

				if( !isset( $hierarchy[$term_id] ) )
					$hierarchy[$term_id] = array();

				$parents[$term_id] = $term_id;
			}

			if( $parent > 0 )
				$hierarchy[$parent][] = $term_id;

			// Ordering
			switch ($default_order) {
				case 'date':
				case 'price':
				case 'popularity':
				case 'rating':
					$ordering = WC()->query->get_catalog_ordering_args($default_order);
					break;
				case 'price-desc':
					$ordering = WC()->query->get_catalog_ordering_args('price', 'DESC');
					break;
				
				default:
					$ordering = WC()->query->get_catalog_ordering_args();
					break;
			}

			$args       = array(
				'posts_per_page' => -1,
				'post_type' => 'product',
				'fields'    => 'ids',
				'order'     => $ordering['order'],
				'orderby'   => $ordering['orderby'],
				'tax_query' => array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $term->term_id,
					),
				),
			);

			if ( isset( $ordering['meta_key'] ) ) {
				$args['meta_key'] = $ordering['meta_key'];
			}
			$products = new WP_Query($args);
			$relationships[$term->term_id] = array();
			if( $products->have_posts() ){
				if( $out_of_stock != 'yes' ){
					foreach ($products->posts as $key => $_id) { 
						$product = wc_get_product($_id);
					
						if( $product && $product->is_in_stock() ){
							$relationships[$term->term_id][] = $_id;
						}
					}
				}else {
	    			$relationships[$term->term_id] = $products->posts;
				}
			}
			
		}
	}

	return array(
		'relationships' => $relationships,
		'hierarchy'     => $hierarchy,
		'parents'       => $parents
	);
}

function pos_get_non_cat_products()
{
  global $wpdb;
  $products = array();

  $tax      = "SELECT tax.term_taxonomy_id tax_id FROM {$wpdb->term_taxonomy} tax WHERE tax.taxonomy = 'product_cat' ";
  $taxonomy = $wpdb->get_results($tax);
  $t = array();
  if($taxonomy){
    foreach ($taxonomy as $tx) {
      $t[] = $tx->tax_id;
    }
  }
  if(!empty($t)){
	  $t = implode(',', $t);  
	}else{
		$t = 0;
	}
  $query    = "SELECT post.ID FROM {$wpdb->posts} post 
    LEFT JOIN {$wpdb->term_relationships} rel ON(rel.object_id = post.ID AND rel.term_taxonomy_id IN({$t}) )
    WHERE post.post_type = 'product' AND post.post_status = 'publish' AND rel.object_id IS NULL
    ";
  $result   = $wpdb->get_results($query);
  if($result){
  	foreach ($result as $value) {
  		$products[] = (int)$value->ID;
  	}
  }

  return $products;
}


function pos_get_registers_by_outlet($outlet_id=0)
{
	global $wpdb;
	$registers = array();

	if($outlet_id){
		$table  =  $wpdb->prefix . 'wc_poin_of_sale_registers';
		$query  = "SELECT ID FROM {$table} WHERE outlet = $outlet_id";
		$result = $wpdb->get_results( $query);
		if($result)
			foreach ($result as $reg)
			$registers[] = $reg->ID;
		
	}
	return $registers;
}
function pos_get_registers_by_cashier($cashier_id=0)
{
	global $wpdb;
	$registers = array();

	if($cashier_id){
		$order_types     = wc_get_order_types( 'order-count' );
		$order_types     = implode( "','", $order_types );
		$query = "SELECT CAST( meta_register_id.meta_value as SIGNED) as register_id
									FROM {$wpdb->posts} AS posts
									LEFT JOIN {$wpdb->postmeta} AS meta_register_id ON ( posts.ID = meta_register_id.post_id AND meta_register_id.meta_key = 'wc_pos_id_register' )
									LEFT JOIN {$wpdb->postmeta} AS meta_order_type ON ( posts.ID = meta_order_type.post_id AND meta_order_type.meta_key = 'wc_pos_order_type' )
      						WHERE posts.post_type IN ( '{$order_types}' )
      						AND posts.post_author = {$cashier_id}
        					AND meta_order_type.meta_value = 'POS' GROUP BY register_id";

		$result = $wpdb->get_results($query);

		if($result)
			foreach ($result as $reg)
			$registers[] = (int)$reg->register_id;
		
	}

	return $registers;
}
function pos_enable_generate_password($value)
{
	return 'yes';
}
/**
 * Get an array of product attribute taxonomies.
 *
 * @access public
 * @return array
 */
function pos_get_attribute_taxonomy_names() {
	$taxonomy_names = array();
	$attribute_taxonomies = wc_get_attribute_taxonomies();
	if ( $attribute_taxonomies ) {
		foreach ( $attribute_taxonomies as $tax ) {
			$taxonomy_names[wc_attribute_taxonomy_name( $tax->attribute_name )] = $tax->attribute_name;
		}
	}
	return $taxonomy_names;
}

function pos_get_user_html($user_to_add)
{
	if( $user_to_add > 0 ){
		$customer = new WP_User( $user_to_add);
	        
	    $b_addr = array(
	            'first_name' => $customer->billing_first_name,
	            'last_name'  => $customer->billing_last_name,
	            'company'    => $customer->billing_company,
	            'address_1'  => $customer->billing_address_1,
	            'address_2'  => $customer->billing_address_2,
	            'city'       => $customer->billing_city,
	            'state'      => $customer->billing_state,
	            'postcode'   => $customer->billing_postcode,
	            'country'    => $customer->billing_country,
	            'email'      => $customer->billing_email,
	            'phone'      => $customer->billing_phone,
	        );
	    $s_addr = array(
	            'first_name' => $customer->shipping_first_name,
	            'last_name'  => $customer->shipping_last_name,
	            'company'    => $customer->shipping_company,
	            'address_1'  => $customer->shipping_address_1,
	            'address_2'  => $customer->shipping_address_2,
	            'city'       => $customer->shipping_city,
	            'state'      => $customer->shipping_state,
	            'postcode'   => $customer->shipping_postcode,
	            'country'    => $customer->shipping_country,
	        );

	    $user_data = array(
	        'first_name' => $customer->first_name,
	        'last_name'  => $customer->last_name,
	        'email'      => $customer->user_email
	        );

	    if(empty($b_addr['first_name'])){
	        $b_addr['first_name'] = $user_data['first_name'];
	    }
	    if(empty($b_addr['last_name'])){
	        $b_addr['last_name'] = $user_data['last_name'];
	    }
	    if(empty($b_addr['email'])){
	        $b_addr['email'] = $user_data['email'];      
	    }
    } 


    $class = 'new_row';    
    ob_start();
    require( WC_POS()->plugin_path() . '/includes/views/html-admin-registers-customer.php' );

    $out = ob_get_contents();

    return $out;
}

if ( !function_exists('is_pos') ){
	function is_pos()
	{
		global $wp_query;
        $q = $wp_query->query;
        if( isset( $q['page'] ) && $q['page'] == 'wc_pos_registers' && isset( $q['action'] ) && $q['action'] == 'view' ) {
            return true;
        }
        return false;
	}
}

function wc_pos_get_available_payment_gateways()
{
	/*$available = array();
	$gateways  = WC()->payment_gateways->payment_gateways();
    $enabled   = get_option( 'pos_enabled_gateways', array() ); 
	foreach ($gateways as $key => $gateway) {
		if( in_array($key, $enabled)){
			$available[$key] = $gateway;
		}
	}
	return $available;*/

	$enabled_gateways = get_option( 'pos_enabled_gateways', array() );
	$payment_gateways = array();
	$load_gateways    = array();

	foreach ( WC()->payment_gateways->payment_gateways() as $gateway ) {
		$g = $gateway;
		$g->title = $gateway->get_title();
		$load_gateways[esc_attr( $gateway->id )] = $g;
	}
	$load_gateways['pos_chip_pin'] = (object) array('id' => 'pos_chip_pin', 'title' => __('Chip & PIN', 'wc_point_of_sale') );
	
	// Get sort order option
	$ordering  = (array) get_option( 'pos_exist_gateways' );
	
	$order_end = 999;

	// Load gateways in order
	foreach ( $load_gateways as $id => $load_gateway ) {
		if( !in_array( $id, $enabled_gateways) ){
			continue;
		}
		if ( in_array( $id, $ordering ) ) {
			$key = array_search( $id, $ordering );
			$payment_gateways[ $key ] = $load_gateway;
		} else {
			// Add to end of the array
			$payment_gateways[ $order_end ] = $load_gateway;
			$order_end++;
		}
	}

	ksort( $payment_gateways );
	return $payment_gateways;
}

function wc_pos_tax_enabled()
{
	$pos_calc_taxes = get_option( 'woocommerce_pos_tax_calculation', 'enabled');
	if( $pos_calc_taxes == 'enabled' && wc_tax_enabled() ){
		return true;
	}else{
		return false;
	}
}


function pos_localize_script($script_name = '')
{
	$pos_tax_based_on = get_option( 'woocommerce_pos_calculate_tax_based_on', 'outlet' );
	if($pos_tax_based_on == 'default'){
		$pos_tax_based_on = get_option( 'woocommerce_tax_based_on' );
	}

  	wp_localize_script($script_name, 'wc_pos_params', apply_filters('wc_pos_params', array(
      'wp_debug'        => defined('WP_DEBUG') ? WP_DEBUG : false,
      'avatar'          => get_avatar( 0, 30),
      'sound_path'      => WC_POS()->plugin_sound_url(),
      'ajax_url'        => WC()->ajax_url(),
      'admin_url'       => admin_url(),
      'ajax_loader_url' => apply_filters('woocommerce_ajax_loader_url', WC()->plugin_url() . '/assets/images/ajax-loader@2x.gif'),
      'reprint_receipt_url' => wp_nonce_url( admin_url( 'admin.php?print_pos_receipt=true&order_id=_order_id_' ), 'print_pos_receipt' ),
      'post_id'         => isset($post->ID) ? $post->ID : '',
      'def_img'         => wc_placeholder_img_src(),
      'custom_pr_id'    => (int)get_option('wc_pos_custom_product_id'),
      'attr_tax_names'  => pos_get_attribute_taxonomy_names(),
      'hidden_order_itemmeta'    => array_flip(apply_filters( 'woocommerce_hidden_order_itemmeta', array(
              '_qty',
              '_tax_class',
              '_product_id',
              '_variation_id',
              '_line_subtotal',
              '_line_subtotal_tax',
              '_line_tax_data',
              '_line_total',
              '_line_tax',
            ))),

      'new_update_pos_outlets_address_nonce'  => wp_create_nonce("new-update-pos-outlets-address"),
      'edit_update_pos_outlets_address_nonce' => wp_create_nonce("edit-update-pos-outlets-address"),
      'search_variations_for_product'         => wp_create_nonce("search_variations_for_product"),
      'printing_receipt_nonce'                => wp_create_nonce("printing_receipt"),
      'add_product_to_register'               => wp_create_nonce("add_product_to_register"),
      'remove_product_from_register'          => wp_create_nonce("remove_product_from_register"),
      'add_customers_to_register'             => wp_create_nonce("add_customers_to_register"),
      'check_shipping'                        => wp_create_nonce("check_shipping"),
      'load_order_data'                       => wp_create_nonce("load_order_data"),
      'load_pending_orders'                   => wp_create_nonce("load_pending_orders"),
      'search_products_and_variations'        => wp_create_nonce("search-products"),
      'add_product_grid'                      => wp_create_nonce("add-product_grid"),
      'search_customers'                      => wp_create_nonce("search-customers"),
      'void_register_nonce'                   => wp_create_nonce("void_register"),

      'remove_item_notice'      => __("Are you sure you want to remove the selected items?", 'wc_point_of_sale'),



      'barcode_url'        => plugins_url( 'includes/lib/barcode/image.php?filetype=PNG&dpi=72&scale=2&rotation=0&font_family=Arial.ttf&&thickness=30&start=NULL&code=BCGcode128' , WC_POS_FILE )
  )));	
}
function wc_pos_woocommerce_version_check( $version = '2.5' ) {
    global $woocommerce;
    
    if( $woocommerce && version_compare( $woocommerce->version, $version, ">=" ) ) {
      return true;
    }
  return false;
}
function wc_pos_clear_transient()
{
	global $wpdb;

	$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_wc_pos%';");
}
function is_pos_referer()
{
    $referer = wp_get_referer();
    $pos_url = get_home_url()."/point-of-sale/";

    if( strpos( $referer, $pos_url) !== false){
        return true;
    }
    return false;
}


function pos_get_acf_fields()
{
	add_filter('acf/location/rule_match/ef_crm_customers', '__return_true');

	$acf_fields = array();
	$acfs = apply_filters('acf/get_field_groups', false);
	if( $acfs )
	{
		$filter = array(
			'ef_user' => true,
			'ef_crm_customers' => true,
			'post_type' => 'shop_order'
		);
		$field_groups = apply_filters( 'acf/location/match_field_groups', array(), $filter );
		
		foreach( $acfs as $acf )
		{
			if( in_array($acf['id'], $field_groups)){
				$fields    = apply_filters('acf/field_group/get_fields', array(), $acf['id']);
				foreach ($fields as $field) {
					$acf_fields[] = $field['name'];
				}
			}
		}
	}
	remove_filter('acf/location/rule_match/ef_crm_customers', '__return_true');
	return $acf_fields;
}

function pos_get_acf_order_fields()
{
	$acf_fields = array();
	$acfs = apply_filters('acf/get_field_groups', false);
	if( $acfs )
	{
		$filter = array(
			'post_type' => 'shop_order'
		);
		$field_groups = apply_filters( 'acf/location/match_field_groups', array(), $filter );
		
		foreach( $acfs as $acf )
		{
			if( in_array($acf['id'], $field_groups)){
				$fields    = apply_filters('acf/field_group/get_fields', array(), $acf['id']);
				foreach ($fields as $field) {
					$acf_fields[] = $field['name'];
				}
			}
		}
	}
	return $acf_fields;
}
function pos_get_custom_order_fields()
{
	$custom_fields = array();
	if( function_exists('wc_admin_custom_order_fields')){
		foreach ( wc_admin_custom_order_fields()->get_order_fields() as $field_id => $field ) {

			$custom_fields[] = '_wc_acof_'.$field_id;
		}
	}
	return $custom_fields;
}


function pos_close_register($register_id = 0)
{
	global $wpdb;

	if( $register_id ){
		$table_name = $wpdb->prefix . "wc_poin_of_sale_registers";
        $db_data = $wpdb->get_results("SELECT * FROM $table_name WHERE ID = $register_id");

        if ( $db_data && 0 != ($user_id = get_current_user_id()) ){
            $row = $db_data[0];
            
            $lock_user = $row->_edit_last;
            if($lock_user == $user_id){
                $now = current_time( 'mysql' );    
                $data['closed']     = $now;
                $data['_edit_last'] = $user_id;
                $wpdb->update( $table_name, $data, array( 'ID' => $register_id ) );                
                return true;
            }
        }
	}
	return false;
}

function pos_logout($register_id = 0)
{
	global $wpdb;
	if( $register_id ){
		$data = WC_POS()->register()->get_data($register_id);
		if( $data ){
			$data = $data[0];
			if( $data['settings']['change_user'] ){
				pos_close_register($register_id);
				wp_logout();
				return true;
			}
		}
	}
	return false;
}
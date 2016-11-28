<?php
/**
 * Chained Products - Main class
 * 
 * @author Store Apps
 */
class SA_WC_Chained_Products {

	static $text_domain;

	private $supported_types = array();

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->supported_types =  array( 'simple', 'variable', 'variation', 'mix-and-match', 'composite', 'bundle' );

		self::$text_domain = 'woocommerce-chained-products';

		add_action( 'init', array( $this, 'localize' ) );

		// For adding / saving chained products
		add_action( 'woocommerce_product_options_related', array(&$this, 'on_product_write_panels'), 20 );
		add_action( 'save_post', array(&$this, 'on_process_product_meta'), 1, 2 );

		// Actions on order status change
		add_action( 'woocommerce_payment_complete', array(&$this, 'on_do_chained_products'), 20);
		add_action( 'woocommerce_order_status_processing', array(&$this, 'on_do_chained_products'), 20);
		add_action( 'woocommerce_order_status_completed', array(&$this, 'on_do_chained_products'), 20);
		add_action( 'woocommerce_order_status_refunded', array(&$this, 'on_undo_chained_products'), 20);
		add_action( 'woocommerce_order_status_cancelled', array(&$this, 'on_undo_chained_products'), 20);

		// Actions for adding / removing products from order
		add_action( 'wp_ajax_woocommerce_add_order_item', array(&$this, 'on_add_order_item_manually'), 10 );
		add_action( 'wp_ajax_remove_chained_order_items_manually', array(&$this, 'remove_chained_order_items_manually') );
		
		add_filter( 'wp_insert_post_data', array( &$this, 'remove_shortcode_from_post_content' )  );

		add_action( 'wp_ajax_woocommerce_json_search_products_and_only_variations', array ( &$this, 'woocommerce_json_search_products_and_only_variations' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', array ( &$this, 'enqueue_chained_products_js_css' ), 20);
		add_action( 'admin_footer', array ( &$this, 'chained_products_footer_js' ), 20);
		
		add_action( 'woocommerce_add_order_item_meta', array( &$this, 'add_chained_products_order_item_meta' ), 10, 2 );
		add_filter( 'woocommerce_hidden_order_itemmeta', array( &$this, 'woocommerce_hide_chained_products_order_itemmeta' ) );

		add_action( 'save_post_shop_order', array( $this, 'add_chained_products_in_given_order' ), 10, 3 );

        add_filter( 'plugin_action_links_' . plugin_basename( WC_CP_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );

		// Actions to be added on woocommerce_loaded event
		add_action( 'woocommerce_loaded', array( &$this, 'on_woocommerce_loaded' ) );

		add_action( 'woocommerce_ajax_save_product_variations', array( $this, 'cp_save_product_variations' ) );

	}

	/**
	 * to handle WC compatibility related function call from appropriate class
	 *
	 * @param $function_name string
	 * @param $arguments array of arguments passed while calling $function_name
	 * @return result of function call
	 *
	 */
	public function __call( $function_name, $arguments = array() ) {

		if ( ! is_callable( 'Chained_Products_WC_Compatibility', $function_name ) ) return;

		if ( ! empty( $arguments ) ) {
			return call_user_func_array( 'Chained_Products_WC_Compatibility::'.$function_name, $arguments );
		} else {
			return call_user_func( 'Chained_Products_WC_Compatibility::'.$function_name );
		}

	}

	/**
	 * Function to load language & translation for chained products
	 */
	public function localize() {

		$text_domains = array( self::$text_domain, 'wc-chained-products' );

		$plugin_dirname = WC_CP_PLUGIN_DIRNAME;

		foreach ( $text_domains as $text_domain ) {

			self::$text_domain = $text_domain;

			$locale = apply_filters( 'plugin_locale', get_locale(), self::$text_domain );

			$loaded = load_textdomain( self::$text_domain, WP_LANG_DIR . '/' . $plugin_dirname . '/' . self::$text_domain . '-' . $locale . '.mo' );

			if ( ! $loaded ) {
				$loaded = load_plugin_textdomain( self::$text_domain, false, $plugin_dirname . '/languages' );
			}

			if ( $loaded ) {
				break;
			}

		}
		
	}

	/**
	 * Adding actions on woocommerce_loaded event
	 */
	public function on_woocommerce_loaded() {

		if ( Chained_Products_WC_Compatibility::is_wc_gte_23() ) {
			add_action( 'woocommerce_product_after_variable_attributes', array(&$this, 'on_product_write_panels'), 20, 3 );
		} else {
			add_action( 'woocommerce_variation_options', array(&$this, 'on_product_write_panels'), 20, 3 );	
		}
		
	}

	/**
	 * When adding order item manually from order edit admin page
	 * 
	 * @global wpdb $wpdb WordPress Database object
	 */
	public function on_add_order_item_manually() {
			
		if( ! Chained_Products_WC_Compatibility::is_wc_22() && ! Chained_Products_WC_Compatibility::is_wc_21() ) {
					
			if ( check_ajax_referer( 'add-order-item', 'security' ) == 1 ) {

				global $wpdb;
				$index                  = trim( stripslashes( $_POST['index'] ) );
				$product_id             = $_POST['item_to_add'];                                
				$chained_product_detail = $this->get_all_chained_product_details( $product_id );
				$chained_product_ids    = is_array( $chained_product_detail ) ? array_keys( $chained_product_detail ) : null;

				if ( $chained_product_ids != null ) {
					
					foreach ( $chained_product_ids as $chained_product_id ) {
		
						$chained_product_id = (int) trim( $chained_product_id );
						
						if ($chained_product_id > 0) {
							$index++;
							
							$item_to_add = trim(stripslashes( $chained_product_id ));
						
							$post = '';
							
							// Find the item
							if (is_numeric($item_to_add)) :
								$post = get_post( $item_to_add );
							endif;
							
							if (!$post || ($post->post_type!=='product' && $post->post_type!=='product_variation')) :
								$post_id = $wpdb->get_var($wpdb->prepare("
									SELECT post_id
									FROM $wpdb->posts
									LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
									WHERE $wpdb->postmeta.meta_key = '_sku'
									AND $wpdb->posts.post_status = 'publish'
									AND $wpdb->posts.post_type = 'shop_product'
									AND $wpdb->postmeta.meta_value = %s
									LIMIT 1
								"), $item_to_add );
								$post = get_post( $post_id );
							endif;
							
							if (!$post || ($post->post_type!=='product' && $post->post_type!=='product_variation')) :
								die();
							endif;
							
				            $_product = Chained_Products_WC_Compatibility::get_product( $post->ID );
							
							?>
							<tr class="item" rel="<?php echo $index; ?>">
								<td class="thumb">
									<a href="<?php echo esc_url( admin_url('post.php?post='. $_product->id .'&action=edit') ); ?>" class="tips" data-tip="<?php
																				echo '<strong>'.__('Product ID:', 'woocommerce').'</strong> '. $_product->id;
										echo '<br/><strong>'.__('Variation ID:', 'woocommerce').'</strong> '; if (isset($_product->variation_id) && $_product->variation_id) echo $_product->variation_id; else echo '-';
																				echo '<br/><strong>'.__('Product SKU:', 'woocommerce').'</strong> '; if ($_product->sku) echo $_product->sku; else echo '-';
																		?>"><?php echo $_product->get_image(); ?></a>
								</td>
								<td class="sku">
									<?php if ($_product->sku) echo $_product->sku; else echo '-'; ?>
									<input type="hidden" class="item_id" name="item_id[<?php echo $index; ?>]" value="<?php echo esc_attr( $_product->id ); ?>" />
									<input type="hidden" name="item_name[<?php echo $index; ?>]" value="<?php echo esc_attr( $_product->get_title() ); ?>" />
									<input type="hidden" name="item_variation[<?php echo $index; ?>]" value="<?php if (isset($_product->variation_id)) echo $_product->variation_id; ?>" />
								</td>
								<td class="name">
								
									<div class="row-actions">
										<span class="trash"><a class="remove_row" href="#"><?php _e('Delete item', 'woocommerce'); ?></a> | </span>
										<span class="view"><a href="<?php echo esc_url( admin_url('post.php?post='. $_product->id .'&action=edit') ); ?>"><?php _e('View product', 'woocommerce'); ?></a>
									</div>
									
									<?php echo $_product->get_title(); ?>
									<?php if (isset($_product->variation_data)) echo '<br/>' . woocommerce_get_formatted_variation( $_product->variation_data, true ); ?>
									<table class="meta" cellspacing="0">
										<tfoot>
											<tr>
												<td colspan="3"><button class="add_meta button"><?php _e('Add&nbsp;meta', 'woocommerce'); ?></button></td>
											</tr>
										</tfoot>
										<tbody class="meta_items"></tbody>
									</table>
								</td>
								
								<?php do_action('woocommerce_admin_order_item_values', $_product); ?>
								
								<td class="tax_class" width="1%">
									<select class="tax_class" name="item_tax_class[<?php echo $loop; ?>]">
										<?php 
										$tax_classes = array_filter(array_map('trim', explode("\n", get_option('woocommerce_tax_classes'))));
										$classes_options = array();
										$classes_options[''] = __('Standard', 'woocommerce');
										if ($tax_classes) foreach ($tax_classes as $class) :
											$classes_options[sanitize_title($class)] = $class;
										endforeach;
										foreach ($classes_options as $value => $name) echo '<option value="'. $value .'" '.selected( $value, $_product->get_tax_status(), false ).'>'. $name .'</option>';
										?>
									</select>
								</td>
								
								<td class="quantity" width="1%">
																	<input type="text" name="item_quantity[<?php echo $index; ?>]" placeholder="0" value="<?php echo $chained_product_detail[$chained_product_id]['unit'];?>" size="2" class="quantity" />
								</td>
								
								<td class="line_subtotal" width="1%">
									<label><?php _e('Cost', 'woocommerce'); ?>: <input type="text" name="line_subtotal[<?php echo $index; ?>]" placeholder="0.00" value="<?php echo esc_attr( number_format(0) ); ?>" class="line_subtotal" /></label>
									
									<label><?php _e('Tax', 'woocommerce'); ?>: <input type="text" name="line_subtotal_tax[<?php echo $index; ?>]" placeholder="0.00" class="line_subtotal_tax" /></label>
								</td>
								
								<td class="line_total" width="1%">
									<label><?php _e('Cost', 'woocommerce'); ?>: <input type="text" name="line_total[<?php echo $index; ?>]" placeholder="0.00" value="<?php echo esc_attr( number_format(0) ); ?>" class="line_total" /></label>
									
									<label><?php _e('Tax', 'woocommerce'); ?>: <input type="text" name="line_tax[<?php echo $index; ?>]" placeholder="0.00" class="line_tax" /></label>
								</td>
								
							</tr>
							<?php
						}
					}
				}
			}
		} else {                   

			check_ajax_referer( 'order-item', 'security' );

			$item_to_add            = sanitize_text_field( $_POST['item_to_add'] );
			$order_id               = absint( $_POST['order_id'] );
			$order       			= Chained_Products_WC_Compatibility::get_order( $order_id );
			$chained_product_detail = $this->get_all_chained_product_details( $item_to_add );
			$chained_product_ids    = is_array( $chained_product_detail ) ? array_keys($chained_product_detail ) : null;
							
			if ( $chained_product_ids != null ) {
				
				foreach ( $chained_product_ids as $chained_product_id ) {
					// Find the item
					if ( ! is_numeric( $chained_product_id ) ) {
						continue;
					}

					$post = get_post( $chained_product_id );

					if ( ! $post || ( 'product' !== $post->post_type && 'product_variation' !== $post->post_type ) ) {
						continue;
					}

					$_product    = Chained_Products_WC_Compatibility::get_product( $post->ID );
					$order_taxes = $order->get_taxes();
					$class       = 'new_row';

					// Set values
					$item = array();

					$item['product_id']        = $_product->id;
					$item['variation_id']      = isset( $_product->variation_id ) ? $_product->variation_id : '';
					$item['variation_data']    = $item['variation_id'] ? $_product->get_variation_attributes() : '';
					$item['name']              = $_product->get_title();
					$item['tax_class']         = $_product->get_tax_class();
					$item['qty']               = ( ! empty( $chained_product_detail[ $chained_product_id ]['unit'] ) ) ? $chained_product_detail[ $chained_product_id ]['unit'] : 1;
					$item['line_subtotal']     = wc_format_decimal( 0 );
					$item['line_subtotal_tax'] = '';
					$item['line_total']        = wc_format_decimal( 0 );
					$item['line_tax']          = '';

					$item['chained_product_of'] = $item_to_add;

					// Add line item
					$item_id = wc_add_order_item( $order->id, array(
						'order_item_name' 		=> $item['name'],
						'order_item_type' 		=> 'line_item'
					) );

					// Add line item meta
					if ( $item_id ) {
						wc_add_order_item_meta( $item_id, '_qty', $item['qty'] );
						wc_add_order_item_meta( $item_id, '_tax_class', $item['tax_class'] );
						wc_add_order_item_meta( $item_id, '_product_id', $item['product_id'] );
						wc_add_order_item_meta( $item_id, '_variation_id', $item['variation_id'] );
						wc_add_order_item_meta( $item_id, '_line_subtotal', $item['line_subtotal'] );
						wc_add_order_item_meta( $item_id, '_line_subtotal_tax', $item['line_subtotal_tax'] );
						wc_add_order_item_meta( $item_id, '_line_total', $item['line_total'] );
						wc_add_order_item_meta( $item_id, '_line_tax', $item['line_tax'] );

						// Since 2.2
						wc_add_order_item_meta( $item_id, '_line_tax_data', array( 'total' => array(), 'subtotal' => array() ) );

						// Store variation data in meta
						if ( $item['variation_data'] && is_array( $item['variation_data'] ) ) {
							foreach ( $item['variation_data'] as $key => $value ) {
								wc_add_order_item_meta( $item_id, str_replace( 'attribute_', '', $key ), $value );
							}
						}

						wc_add_order_item_meta( $item_id, '_chained_product_of', $item['chained_product_of'] );

						do_action( 'woocommerce_ajax_add_order_item_meta', $item_id, $item );
					}

					$item          = apply_filters( 'woocommerce_ajax_order_item', $item, $item_id );

					include( WC()->plugin_path() . '/includes/admin/meta-boxes/views/html-order-item.php' );

				}//end foreach
			}// end if
		}
	}

	/**
	 * Function to remove chained items manually
	 */
	public function remove_chained_order_items_manually() {

		check_ajax_referer( 'remove-chained-order-items-manually', 'security' );

		$order_item_id = ( ! empty( $_POST['order_item_id'] ) ) ? $_POST['order_item_id'] : 0;
		$order_id = ( ! empty( $_POST['order_id'] ) ) ? $_POST['order_id'] : 0;

		if ( empty( $order_item_id ) || empty( $order_id ) ) die( json_encode(array()) );

		global $wpdb;

		$item_product_id   = wc_get_order_item_meta( $order_item_id, '_product_id' );
		$item_variation_id = wc_get_order_item_meta( $order_item_id, '_variation_id' );

		$item_to_remove = ( ! empty( $item_variation_id ) ) ? $item_variation_id : $item_product_id;

		$chained_product_detail = $this->get_all_chained_product_details( $item_to_remove );
		$chained_product_ids    = is_array( $chained_product_detail ) ? array_keys($chained_product_detail ) : null;

		if ( empty( $chained_product_ids ) ) die( json_encode(array()) );

		$order_item_ids_query = "SELECT DISTINCT oi.order_item_id 
									FROM wp_woocommerce_order_items AS oi
									JOIN wp_woocommerce_order_itemmeta AS oim
										ON ( oi.order_item_id = oim.order_item_id AND oim.meta_key = '_chained_product_of' )
									WHERE oim.meta_value = {$item_to_remove}
										AND oi.order_id = {$order_id}";

		$order_item_ids = $wpdb->get_col( $order_item_ids_query );

		if ( empty( $order_item_ids ) ) die( json_encode(array()) );

		foreach ( $order_item_ids as $order_item_id ) {
			wc_delete_order_item( $order_item_id );
		}
		
		echo json_encode( array( 'order_item_ids' => $order_item_ids ) );
		die();
	}

	/**
	 * When adding chained products
	 * 
	 * @param int $order_id
	 */
	public function on_do_chained_products($order_id) {
		$this->_on_process_chained_products($order_id, 'do');
	}

	/**
	 * When removing chained products
	 * 
	 * @param int $order_id
	 */
	public function on_undo_chained_products($order_id) {
		$this->_on_process_chained_products($order_id, 'undo');
	}

	/**
	 * Process addition or removal of chained products from order
	 * 
	 * @access private
	 * @param int $order_id
	 * @param string $operation
	 */
	private function _on_process_chained_products($order_id, $operation = 'do') {

		$order = Chained_Products_WC_Compatibility::get_order( $order_id );
		$order_items = (array) $order->get_items();
		$order_changed = false;

		foreach($order_items as $item) {
			$product = $order->get_product_from_item( $item );
			if ( $product instanceof WC_Product_Variation ) {
				$product = Chained_Products_WC_Compatibility::get_product( $product->id );
			}
			
			if ( empty( $product->product_custom_fields['_chained_product_ids'] ) ) continue;

			if ( isset( $product->product_custom_fields['_chained_product_ids'][0] ) && $product->product_custom_fields['_chained_product_ids'][0] !== '' ) {
				$chained_products = ( array ) maybe_unserialize( $product->product_custom_fields['_chained_product_ids'][0] );

				switch ($operation)
					{
						case 'do':
							// TODO: Add item to the order as well?
							$order_items = $this->_on_add_order_item($order, $order_items, $chained_products, $item['qty'], $product->id);
							$order_changed = true;
							break;
						case 'undo':
							// TODO: Remove item from the order as well?
							$order_items = $this->_on_remove_order_item($order, $order_items, $chained_products, $item['qty'], $product->id);
							$order_changed = true;
							break;
					}
			}

		}
		if ($order_changed === true)
		{
			update_post_meta($order_id, '_order_items', $order_items);
		}
	}

	/**
	 * When revoking access to download, after cancellation or refund of order containing chained products
	 * 
	 * @access private
	 * @global wpdb $wpdb WordPress Database Object
	 * @param WC_Order $order WooCommerce Order
	 * @param int $product_id
	 * @return mixed
	 */
	private function _on_revoke_downloadable_product($order, $product_id) {
		global $wpdb;

		$result = $wpdb->query("
			DELETE FROM {$wpdb->prefix}woocommerce_downloadable_product_permissions
			WHERE order_id = {$order->id}
			AND product_id = $product_id
		");
		// If above query executed successfully i.e. revoked download permission then add order note
		if ($result > 0) {
			return array(
				'success'		=> 1,
				'download_id'  	=> $product_id,
			);
		}
		return null;
	}

	/**
	 * When adding items to order
	 * 
	 * @access private
	 * @param WC_Order $order WooCommerce Order
	 * @param array $order_items
	 * @param array $chained_products
	 * @param int $quantity
	 * @param int $chained_parent_id
	 * @return array $order_items
	 */
	private function _on_add_order_item($order, $order_items, $chained_products, $quantity, $chained_parent_id) {

		$names = array();
		foreach ((array) $chained_products as $chained_product_id) {
			$chained_product_id = (int) trim($chained_product_id);
			$names[] = '"' . $this->get_product_title($chained_product_id) . '"';
		}

		if (count($names) > 0) {
			$note = sprintf(_n('Chained order item %s was added.', 'Chained order items %s were added.', count($names), self::$text_domain), implode(", ", $names));
			$order->add_order_note ( $note, 0 );
		}
		return $order_items;
	}

	/**
	 * When removing order item
	 * 
	 * @access private
	 * @param WC_Order $order WooCommerce Order
	 * @param array $order_items
	 * @param array $chained_products
	 * @param int $quantity
	 * @param int $chained_parent_id
	 * @return array $order_items
	 */
	private function _on_remove_order_item($order, $order_items, $chained_products, $quantity, $chained_parent_id) {

		$names = array();
		// Loop to locate & unset order item, if exists
		foreach ( $order_items as $key => $value ) {
			if ( isset( $value['variation_id'] ) && ( $value['variation_id'] > 0 ) ) {
				$id_or_variation_id = $value['variation_id'];
			} else {
				$id_or_variation_id = $value['id'];
			}
			$_product = Chained_Products_WC_Compatibility::get_product( $id_or_variation_id );

			if ( in_array( $id_or_variation_id, $chained_products ) ) {
				unset( $order_items[$key] );
				if ( ( get_option( 'woocommerce_manage_stock' ) == 'yes' ) && ( get_post_meta( $chained_parent_id, '_chained_product_manage_stock', true ) == 'yes' ) && $_product->backorders_allowed() ) $_product->increase_stock( $quantity );
				$names[] = '"' . $this->get_product_title( $id_or_variation_id ) . '"';
				$this->_on_revoke_downloadable_product($order, $id_or_variation_id);
			}
		}
		if (count($names) > 0) {
			$note = sprintf(_n('Chained order item %s was removed.', 'Chained order items %s were removed.', count($names), self::$text_domain), implode(", ", $names));
			$order->add_order_note ( $note, 0 );
		}
		return $order_items;
	}

	/**
	 * Hide Chained Products order meta from order dashboard
	 * 
	 * @param array $itemmeta
	 * @return array $itemmeta
	 */
	public function woocommerce_hide_chained_products_order_itemmeta( $itemmeta ) {
	
		$itemmeta[] = "_chained_product_of";
		return $itemmeta;

	}

	/**
	 * Function to add single chained item in order
	 * 
	 * @param int $item_to_add
	 * @param int $qty
	 * @param WC_Order $order
	 * @param int $chained_product_of
	 * @param array $chained_product_detail
	 */
	public function add_chained_item_in_order( $item_to_add = 0, $order = null, $chained_product_of = 0, $chained_product_detail = null, $qty = 1 ) {

		if ( empty( $item_to_add ) || empty( $order ) || empty( $chained_product_of ) || empty( $chained_product_detail ) ) return;

		if ( ! is_numeric( $item_to_add ) ) {
			return false;
		}

		$post = get_post( $item_to_add );

		if ( ! $post || ( 'product' !== $post->post_type && 'product_variation' !== $post->post_type ) ) {
			return false;
		}

		$_product    = Chained_Products_WC_Compatibility::get_product( $post->ID );
		$order_taxes = $order->get_taxes();
		$class       = 'new_row';

		$unit 		 = ( ! empty( $chained_product_detail[ $item_to_add ]['unit'] ) ) ? $chained_product_detail[ $item_to_add ]['unit'] : 1;

		// Set values
		$item = array();

		$item['product_id']        = $_product->id;
		$item['variation_id']      = isset( $_product->variation_id ) ? $_product->variation_id : '';
		$item['variation_data']    = $item['variation_id'] ? $_product->get_variation_attributes() : '';
		$item['name']              = $_product->get_title();
		$item['tax_class']         = $_product->get_tax_class();
		$item['qty']               = $qty * $unit;
		$item['line_subtotal']     = wc_format_decimal( 0 );
		$item['line_subtotal_tax'] = '';
		$item['line_total']        = wc_format_decimal( 0 );
		$item['line_tax']          = '';

		// Add line item
		$item_id = wc_add_order_item( $order->id, array(
			'order_item_name' 		=> $item['name'],
			'order_item_type' 		=> 'line_item'
		) );

		// Add line item meta
		if ( $item_id ) {
			wc_add_order_item_meta( $item_id, '_qty', $item['qty'] );
			wc_add_order_item_meta( $item_id, '_tax_class', $item['tax_class'] );
			wc_add_order_item_meta( $item_id, '_product_id', $item['product_id'] );
			wc_add_order_item_meta( $item_id, '_variation_id', $item['variation_id'] );
			wc_add_order_item_meta( $item_id, '_line_subtotal', $item['line_subtotal'] );
			wc_add_order_item_meta( $item_id, '_line_subtotal_tax', $item['line_subtotal_tax'] );
			wc_add_order_item_meta( $item_id, '_line_total', $item['line_total'] );
			wc_add_order_item_meta( $item_id, '_line_tax', $item['line_tax'] );

			// Since 2.2
			wc_add_order_item_meta( $item_id, '_line_tax_data', array( 'total' => array(), 'subtotal' => array() ) );

			// Store variation data in meta
			if ( $item['variation_data'] && is_array( $item['variation_data'] ) ) {
				foreach ( $item['variation_data'] as $key => $value ) {
					wc_add_order_item_meta( $item_id, str_replace( 'attribute_', '', $key ), $value );
				}
			}

			wc_add_order_item_meta( $item_id, '_chained_product_of', $chained_product_of );

		}

		return $item_id;

	}

	/**
	 * Function to grant download permission for a chained item
	 * 
	 * @param int $chained_item_id Chained item
	 * @param WC_Order $order
	 */
	public function grant_download_permission_for_chained_item( $chained_item_id = 0, $order = null ) {

		if ( empty( $chained_item_id ) || empty( $order ) ) return;

		$downloadable 	= get_post_meta( $chained_item_id, '_downloadable', true );
		$virtual 		= get_post_meta( $chained_item_id, '_virtual', true );
		$_product 		= Chained_Products_WC_Compatibility::get_product( $chained_item_id );

		if ( 'completed' == $order->status || 'processing' == $order->status ) {

			if ( Chained_Products_WC_Compatibility::is_wc_22() || Chained_Products_WC_Compatibility::is_wc_21() ) {

				$files   = $_product->get_files();
				if ( $files ) {
					foreach ( $files as $download_id => $file ) {
						wc_downloadable_file_permission( $download_id, $chained_item_id, $order );
					}
				}

			} else {

				$file_download_paths = get_post_meta( $chained_item_id, '_file_paths', true );
				if ( empty( $file_download_paths ) ) return;
				
				foreach ( $file_download_paths as $download_id => $file_path ) {
					woocommerce_downloadable_file_permission( $download_id, $chained_item_id, $order );
				}
			
			}
					
		}

	}

	/**
	 * Add chained products in given order
	 * 
	 * @param int $item_to_add Chained parent
	 * @param WC_Order $order
	 */
	public function add_chained_items_of_product_in_order( $item_to_add = 0, $order = null ) {
	
		if ( empty( $item_to_add ) || empty( $order ) ) return;

		$chained_product_detail = $this->get_all_chained_product_details( $item_to_add );
		$chained_product_ids    = is_array( $chained_product_detail ) ? array_keys($chained_product_detail ) : null;
						
		if ( $chained_product_ids != null ) {
			
			foreach ( $chained_product_ids as $chained_product_id ) {
				
				$item_id = $this->add_chained_item_in_order( $chained_product_id, $order, $item_to_add, $chained_product_detail );

			}//end foreach

		}

	}

	/**
	 * Add chained products in given order
	 * 
	 * @param int $order_id
	 * @param array $post
	 * @param bool $update
	 */
	public function add_chained_products_in_given_order( $order_id = 0, $post = null, $update = false ) {

		$active_plugins = (array) get_option('active_plugins', array());

		if (is_multisite()) {
			$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
		}

		if ( in_array('woocommerce-give-products/woocommerce-give-products.php', $active_plugins) || array_key_exists('woocommerce-give-products/woocommerce-give-products.php', $active_plugins) ) {

			if ( empty( $_GET['page'] ) || $_GET['page'] != 'give_products' ) return;
			if ( empty( $_GET['give_products_nonce'] ) || ! wp_verify_nonce( $_GET['give_products_nonce'], 'give_products' ) ) return;
			if ( empty( $order_id ) ) return;
			if ( empty( $post ) ) return;
			if ( ! $update ) return;

			$order = Chained_Products_WC_Compatibility::get_order( $order_id );
			$order_items = $order->get_items();

			if ( empty( $order_items ) ) return;

			foreach ( $order_items as $order_item_id => $order_item ) {

				$product_id = ( ! empty( $order_item['variation_id'] ) ) ? $order_item['variation_id'] : $order_item['product_id'];

				$this->add_chained_items_of_product_in_order( $product_id, $order );

			}

		}

	}

	/**
	 * Add Chained Products order meta in new order
	 * 
	 * @param int $item_id
	 * @param array $product_values
	 */
	public function add_chained_products_order_item_meta( $item_id, $product_values ) {
		
		$cart = Chained_Products_WC_Compatibility::global_wc()->cart->get_cart();

		foreach ( $cart as $values ) {

			if( $product_values == $values && isset( $values['chained_item_of'] ) ) {

				if( empty( $cart[$values['chained_item_of']]['variation_id'] ) ) {
					$product_id = $cart[$values['chained_item_of']]['product_id'];
				} else {
					$product_id = $cart[$values['chained_item_of']]['variation_id'];
				}

				woocommerce_add_order_item_meta( $item_id, '_chained_product_of', $product_id );
				break;

			}

		}

	}
	
	/**
	 * Function to return all simple, variation products called via ajax
	 * 
	 * @param string $x search term
	 * @param array $post_types
	 */
	public function woocommerce_json_search_products_and_only_variations( $x = '', $post_types = array( 'product', 'product_variation' ) ) {

		check_ajax_referer( 'search-products-and-only-variations', 'security' );
		$term = ( string )urldecode( stripslashes( strip_tags( $_GET['term'] ) ) );

		if ( empty( $term ) )
			die();

		if ( is_numeric( $term ) ) {

			$args = array(
					'post_type'			=> $post_types,
					'post_status'	 	=> array( "publish", "private" ),
					'posts_per_page' 	=> -1,
					'post__in' 			=> array( 0, $term ),
					'fields'			=> 'ids'
			);

			$args2 = array(
					'post_type'			=> $post_types,
					'post_status'	 	=> array( "publish", "private" ),
					'posts_per_page' 	=> -1,
					'post_parent' 		=> $term,
					'fields'			=> 'ids'
			);

			$args3 = array(
					'post_type'			=> $post_types,
					'post_status' 		=> array( "publish", "private" ),
					'posts_per_page' 	=> -1,
					'meta_query' 		=> array(
							array(
								'key' 		=> '_sku',
								'value' 	=> $term,
								'compare' 	=> 'LIKE'
							)
					),
					'fields'			=> 'ids'
			);
			
			$posts = array_unique( array_merge( get_posts( $args ), get_posts( $args2 ), get_posts( $args3 ) ) );

		} else {

			$args = array(
					'post_type'			=> $post_types,
					'post_status' 		=> array( "publish", "private" ),
					'posts_per_page' 	=> -1,
					's' 				=> $term,
					'fields'			=> 'ids' 
			);

			$args2 = array(
					'post_type'			=> $post_types,
					'post_status' 		=> array( "publish", "private" ),
					'posts_per_page' 	=> -1,
					'meta_query' 		=> array(
							array(
								'key' 		=> '_sku',
								'value' 	=> $term,
								'compare' 	=> 'LIKE'
							)
					),
					'fields'			=> 'ids'
			);
			
			$posts = array_unique( array_merge( get_posts( $args, ARRAY_A ), get_posts( $args2, ARRAY_A ) ) );

		}

		$found_products = array();

		if ( $posts ) foreach ( $posts as $post ) {
				
			$post_type 		= get_post_type( $post );
			$product_type 	= wp_get_object_terms( $post, 'product_type', array( 'fields' => 'slugs' ) );
			
			if(  $post_type == "product" && $product_type[0] == "variable" )
				continue;          
				
			if ( Chained_Products_WC_Compatibility::is_wc_22() || Chained_Products_WC_Compatibility::is_wc_21() ) {

				$product = Chained_Products_WC_Compatibility::get_product( $post );
				$found_products[ $post ] = Chained_Products_WC_Compatibility::get_formatted_product_name( $product );
				
			} else {
				
				$SKU = get_post_meta( $post, '_sku', true );
				if ( isset( $SKU ) && $SKU )
					$SKU = ' (SKU: ' . $SKU . ')';
				$found_products[$post] = get_the_title( $post ) . ' &ndash; #' . $post . $SKU;

			}             

		}
		echo json_encode( $found_products );
		die();

	}

	/**
	 * Enqueue CSS style in admin page
	 */
	public function enqueue_chained_products_js_css() {
	   
		wp_register_style( 'woocommerce_chained_products_css', plugins_url( 'woocommerce-chained-products/assets/css/chained-products-admin.css' ) );
		wp_enqueue_style( 'woocommerce_chained_products_css' );

		if ( Chained_Products_WC_Compatibility::is_wc_gte_23() && wp_script_is( 'select2' ) ) {
			wp_localize_script( 'select2', 'cp_select_params', array(
				'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'woocommerce' ),
				'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'woocommerce' ),
				'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
				'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
				'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
				'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
				'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
				'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
				'ajax_url'                  => admin_url( 'admin-ajax.php' ),
				'search_products_nonce'     => wp_create_nonce( 'search-products' ),
				'search_customers_nonce'    => wp_create_nonce( 'search-customers' )
			) );
		}

	}

	/**
	 * Enqueue JS in admin footer
	 */
	public function chained_products_footer_js() {
		global $post, $pagenow, $typenow;
		
		if ( empty( $pagenow ) || ( $pagenow != 'post.php' && $pagenow != 'post-new.php' ) ) return;
		if ( empty( $typenow ) || $typenow != 'shop_order' ) return;
		?>
		<script type="text/javascript">
			jQuery(function(){
				jQuery('#order_line_items').on( 'click', 'a.delete-order-item', function(){
					var order_item_id = jQuery(this).parents('tr.item').attr( 'data-order_item_id' );
					jQuery.ajax({
						url: '<?php echo admin_url( "admin-ajax.php" ); ?>',
						dataType: 'json',
						type: 'post',
						data: {
							action: 'remove_chained_order_items_manually',
							order_item_id: order_item_id,
							order_id: '<?php echo $post->ID; ?>',
							security: '<?php echo wp_create_nonce( "remove-chained-order-items-manually" ) ?>'
						},
						success: function( response ) {
							if ( response.order_item_ids != undefined && response.order_item_ids != '' ) {
								jQuery.each( response.order_item_ids, function( index, value ){
									jQuery('#order_line_items').find('tr[data-order_item_id=' + value + ']').remove();
								});
							}
						}
					});
				});
			});
		</script>
		<?php
	}

	/**
	 * Add fields for chained products on product edit admin page
	 * 
	 * @global woocommerce $woocommerce - Main instance of WooCommerce
	 * @global object $post
	 * @param int $loop
	 * @param array $variation_data
	 */
	public function on_product_write_panels( $loop = 0, $variation_data = '', $variation = '' ) {

		global $woocommerce, $post, $wpdb;

		$product 	= !empty($post) ? Chained_Products_WC_Compatibility::get_product( $post->ID ) : Chained_Products_WC_Compatibility::get_product( $variation->ID );
		$row_loop 	= 0;

		if ( Chained_Products_WC_Compatibility::is_wc_gte_23() ) {
			$chained_parent_id = empty( $variation ) ? $post->ID : $variation->ID;
		} else {
			$chained_parent_id = empty( $variation_data ) ? $post->ID : $variation_data['variation_post_id'];
		}

		$classes = array();
		foreach( $this->get_supported_product_types() as $type ){
			$classes[] = "show_if_" . $type;
		}

		if ( !empty( $variation ) ) {

			$class = 'woocommerce_options_panel';
			$style = 'style = "float: none; width: auto; padding: 1px;"';
			echo "</td></tr></tbody></table>";			
		}

		if( $product->product_type == 'variable' ) {

			$class = 'woocommerce_options_panel';
			$style = 'style = "background: #f5f5f5; display: none; width: auto; padding: 1px;"';
			echo "</td></tr></tbody></table>";

	 	}
     
	    $exclude_query = "SELECT ID FROM {$wpdb->posts} WHERE post_parent = {$post->ID} AND post_type = 'product_variation'";
		$exclude_ids = $wpdb->get_col( $exclude_query );
        	
		if ( ! empty( $exclude_ids ) && is_array( $exclude_ids ) ) {
 	        $total_ids_to_exclude = implode( ',', array_merge( array( $post->ID ), $exclude_ids ) );
        } else {
            $total_ids_to_exclude = $post->ID;
        }

        ?>	
		<div id="chained_products_setting_fields_<?php echo $chained_parent_id; ?>" class="options_group grouping <?php if( isset( $class ) ) echo $class; ?> chained_products_admin_settings" <?php if( isset( $style ) ) echo $style; ?>>
			<div id="chained_products_list_<?php echo $chained_parent_id; ?>">
				<?php

				$product_detail = get_post_meta( $chained_parent_id, '_chained_product_detail', true );

				if ( ! empty( $product_detail ) ) {

					$total_chained_details = $this->get_all_chained_product_details( $chained_parent_id );
					
					foreach ( $total_chained_details as $product_id => $product_data ) {
					?>
						<p class="form-field <?php if( ! isset( $product_detail[$product_id] ) ) echo 'nested_chained_products_'.$chained_parent_id; ?>" id="chained_products_row_<?php echo $chained_parent_id . '_' . $row_loop; ?>">
							<label for="chained_products_ids_<?php echo $chained_parent_id . '_' . $row_loop; ?>"><?php if( $row_loop == 0 ) _e( 'Chained Products', self::$text_domain ); ?>
								<span style="display: inline;" class="description chained_product_description"> </span>
							</label>

							<?php

								if ( Chained_Products_WC_Compatibility::is_wc_22() || Chained_Products_WC_Compatibility::is_wc_21() ) {

									$product = Chained_Products_WC_Compatibility::get_product( $product_id );
									$product_name = (!empty($product)) ? Chained_Products_WC_Compatibility::get_formatted_product_name( $product ) : $product_id;

								} else {
									
									$SKU = get_post_meta( $product_id, '_sku', true );
									if ( isset( $SKU ) && $SKU )
										$SKU = ' (SKU: ' . $SKU . ')';
									$product_name = get_the_title( $product_id ) . ' &ndash; #' . $product_id . $SKU;

								}

							 	if ( Chained_Products_WC_Compatibility::is_wc_gte_23() ){ ?>
									<input type="hidden" class="wc-product-search ajax_chosen_select_products_and_only_variations_<?php echo $chained_parent_id; ?>" style="width: 50%;" id="chained_products_ids_<?php echo $chained_parent_id . '_' . $row_loop; ?>" name="<?php if( ! isset( $product_detail[$product_id] ) ) echo 'nested_'; ?>chained_products_ids[<?php echo $chained_parent_id; ?>][<?php echo $row_loop; ?>]" data-placeholder="<?php _e( 'Search for a product...', 'woocommerce' ); ?>" 
									data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo $total_ids_to_exclude; ?>" data-multiple="true" 
									data-selected="<?php 
										$json_ids    = array();
										$json_ids[ $product_id ] = wp_kses_post( $product->get_formatted_name() );

										echo esc_attr( json_encode( $json_ids ) );
									?>"
									value="<?php echo $product_id; ?>" <?php if( ! isset( $product_detail[$product_id] ) ) echo 'readonly';?> />
							<?php } else { ?>
									<select id="chained_products_ids_<?php echo $chained_parent_id . '_' . $row_loop; ?>" name="<?php if( ! isset( $product_detail[$product_id] ) ) echo 'nested_'; ?>chained_products_ids[<?php echo $chained_parent_id; ?>][<?php echo $row_loop; ?>]" class="ajax_chosen_select_products_and_only_variations_<?php echo $chained_parent_id; ?>" multiple="multiple" data-placeholder="<?php _e( 'Search for a product...', 'woocommerce' ); ?>">
										<?php echo '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . $product_name . '</option>'; ?>
									</select>
							<?php } ?>
							<input type="number" class="chained_products_quantity short" name="chained_products_quantity[<?php echo $chained_parent_id; ?>][<?php echo $row_loop; ?>]" value="<?php echo ( ! empty( $product_data['unit'] ) ) ? $product_data['unit'] : '1'; ?>" placeholder="<?php _e( 'Qty', self::$text_domain ); ?>" min="1" />
							<?php
							if( isset( $product_detail[$product_id] ) ) {

								if( $row_loop == 0 ) { 
							?>
									<span class="add_remove_chained_products_row dashicons-plus" id="add_chained_products_row_<?php echo $chained_parent_id; ?>" title="<?php _e( 'Add Product', self::$text_domain ); ?>"></span>
							<?php } else { ?>
									<span class="add_remove_chained_products_row dashicons-no remove_chained_products_row_<?php echo $chained_parent_id; ?>" id="<?php echo $row_loop; ?>" title="<?php _e( 'Remove Product', self::$text_domain ); ?>"></span>
							<?php }
							}
							?>
						</p>
						<?php
						$row_loop++;
					}

				} else {

					?>
					<p class="form-field" id="chained_products_row_<?php echo $chained_parent_id . '_' . $row_loop; ?>">
						<label for="chained_products_ids_<?php echo $chained_parent_id . '_' . $row_loop; ?>"><?php if( $row_loop == 0 ) _e( 'Chained Products', self::$text_domain ); ?>
							<span style="display: inline;" class="description chained_product_description"> </span>
						</label>

						<?php if ( Chained_Products_WC_Compatibility::is_wc_gte_23() ){ ?>
							<input type="hidden" class="wc-product-search ajax_chosen_select_products_and_only_variations_<?php echo $chained_parent_id; ?>" style="width: 50%;" id="chained_products_ids_<?php echo $chained_parent_id . '_' . $row_loop; ?>" name="chained_products_ids[<?php echo $chained_parent_id; ?>][<?php echo $row_loop; ?>]" data-placeholder="<?php _e( 'Search for a product...', 'woocommerce' ); ?>" 
							data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo $total_ids_to_exclude; ?>" data-multiple="true"/>
						<?php } else { ?> 
							<select id="chained_products_ids_<?php echo $chained_parent_id . '_' . $row_loop; ?>" name="chained_products_ids[<?php echo $chained_parent_id; ?>][<?php echo $row_loop; ?>]" class="ajax_chosen_select_products_and_only_variations_<?php echo $chained_parent_id; ?>" multiple="multiple" data-placeholder="<?php _e( 'Search for a product...', 'woocommerce' ); ?>">
							</select>
						<?php } ?>	

						<input type="number" class="chained_products_quantity short" name="chained_products_quantity[<?php echo $chained_parent_id; ?>][<?php echo $row_loop; ?>]" value="1" placeholder="<?php _e( 'Qty', self::$text_domain ); ?>" min="1">
						<span class="add_remove_chained_products_row  dashicons-plus" id="add_chained_products_row_<?php echo $chained_parent_id; ?>" title="<?php _e( 'Add Product', self::$text_domain ); ?>"></span>
					</p>
					<?php			 			
					$row_loop++;

				}
				?>
			</div>
			<?php

			if ( get_option( 'woocommerce_manage_stock' ) == 'yes' ) {
				?>
				<p class="form-field chained_products_manage_stock_field">
					<label for="chained_products_manage_stock_<?php echo $chained_parent_id; ?>"><?php _e( 'Manage stock?', self::$text_domain ); ?></label>
					<input type="checkbox" class="checkbox" name="chained_products_manage_stock[<?php echo $chained_parent_id; ?>]" id="chained_products_manage_stock_<?php echo $chained_parent_id; ?>" <?php if ( get_post_meta( $chained_parent_id, '_chained_product_manage_stock', true ) == 'yes' ) echo 'checked="checked"'; ?>>
					<span style="display: inline;" class="description"><?php _e( 'Enable stock management for chained products', self::$text_domain ); ?></span>
					<?php 
					if ( Chained_Products_WC_Compatibility::is_wc_gte_24() ) {
                        echo wc_help_tip( __( 'Check to manage stock for products listed in chained products, uncheck otherwise.', self::$text_domain ) ); 
					} else { 
						?>
					    <img class="help_tip" data-tip="<?php _e( 'Check to manage stock for products listed in chained products, uncheck otherwise.', self::$text_domain ); ?>" src="<?php echo Chained_Products_WC_Compatibility::global_wc()->plugin_url(); ?>/assets/images/help.png" width="16" height="16" />
					    <?php 
					} ?>	
				</p> <?php
	        } ?>

			<p class="form-field chained_product_update_order">
				<label for="chained_product_update_order_<?php echo $chained_parent_id; ?>"><?php _e( 'Update existing orders?', self::$text_domain ); ?></label>
				<input type="checkbox" class="checkbox" name="chained_product_update_order[<?php echo $chained_parent_id;?>]" id="chained_product_update_order_<?php echo $chained_parent_id; ?>">
				<span style="display: inline;" class="description"><?php _e( 'Update existing orders with above chained products', self::$text_domain ); ?></span>
				<?php 
				if ( Chained_Products_WC_Compatibility::is_wc_gte_24() ) {
                    echo wc_help_tip( __( 'Check to update existing orders containing this main product. Existing orders will be affected.', self::$text_domain ) ); 
				} else { 
					?>
			        <img class="help_tip" data-tip="<?php _e( 'Check to update existing orders containing this main product. Existing orders will be affected.', self::$text_domain ); ?>" src="<?php echo Chained_Products_WC_Compatibility::global_wc()->plugin_url(); ?>/assets/images/help.png" width="16" height="16" />
				    <?php 
				} ?>
		    </p>
			<div id="message" class="updated below-h2 chained_products_shortcode">
				<p><?php _e( 'To show Chained Products on product page click', self::$text_domain ); ?>
					<a class ="insert_shortcode"><?php _e( 'Insert shortcode in description', self::$text_domain ); ?></a>
				</p>
			</div>
		</div>	
		<?php

		// Javascript
		ob_start();

		?>
		jQuery( function() {

			jQuery(document).on( 'ready', function() {

					<?php if ( Chained_Products_WC_Compatibility::is_wc_gte_23() ){?>
						init_select2();
					<?php } ?>

					jQuery('select#product-type').on( 'change', function() {
						var productType = jQuery(this).find('option:selected').val();

						if ( productType == 'simple' ) {

							var chained_post_id = jQuery('#post_ID').val();

							jQuery('div#chained_products_setting_fields_'+chained_post_id).show();
							jQuery('span.chained_product_description').text('');
						} else {
							jQuery('div#chained_products_setting_fields_<?php echo $chained_parent_id; ?>').hide();
						}

						<?php if ( Chained_Products_WC_Compatibility::is_wc_gte_23() ){?>
							init_select2();
						<?php } ?>

					});

					jQuery( '#woocommerce-product-data' ).on( 'woocommerce_variations_added woocommerce_variations_loaded', function(){

						setTimeout( function() { 
							
							<?php if ( Chained_Products_WC_Compatibility::is_wc_gte_23() ){?>
								init_select2();
								
							<?php } else { ?>

								jQuery( '[class^= "ajax_chosen_select_products_and_only_variations"]' ).each(function() {

									var id_prefix = 'ajax_chosen_select_products_and_only_variations_',
								 	chained_id = jQuery(this).attr('class').substr(id_prefix.length);

									init_chosen(chained_id);
								});
								

								var wc_table_background = jQuery('#variable_product_options .woocommerce_variation table').css('background');
								jQuery(' [id^="chained_products_setting_fields_"] ').css( 'background', wc_table_background );

							<?php } ?>

							jQuery('[id^="add_chained_products_row"]').each(function() {
								var id_prefix = 'add_chained_products_row_',
								 	chained_id = jQuery(this).attr('id').substr(id_prefix.length);
								 	chained_products_add_row(chained_id);
							});

							// Tooltips
							var tiptip_args = {
								'attribute' : 'data-tip',
								'fadeIn' : 50,
								'fadeOut' : 50,
								'delay' : 200
							};
							jQuery(".tips, .help_tip").tipTip( tiptip_args );

						}, 100);
					});
			});
			
			jQuery('.wc-metaboxes-wrapper').on('click', '.wc-metabox h3', function(event){
				if (jQuery(event.target).filter(':input, option').length)
					return;		
				if( jQuery(this).next('.wc-metabox-content').css('display') == 'none' ) {
					jQuery(this).parent().find('.chained_products_admin_settings').hide();
				} else {
					jQuery(this).parent().find('.chained_products_admin_settings').show();
				}
				<?php if ( Chained_Products_WC_Compatibility::is_wc_gte_23() ){ ?>
					init_select2();
				<?php } else { ?>
					jQuery('.wc-metaboxes-wrapper').find("select[id^=chained_products_ids_]").chosen();
				<?php } ?>
			})
			.on('click', '.expand_all', function(event){
				jQuery(this).closest('.wc-metaboxes-wrapper').find('.wc-metabox').find('.chained_products_admin_settings').show();
				<?php if ( Chained_Products_WC_Compatibility::is_wc_gte_23() ){ ?>
					init_select2();
				<?php } else { ?>
					jQuery('.wc-metaboxes-wrapper').find("select[id^=chained_products_ids_]").chosen();
				<?php } ?>
			})
			.on('click', '.close_all', function(event){
				jQuery(this).closest('.wc-metaboxes-wrapper').find('.wc-metabox').find('.chained_products_admin_settings').hide();
				<?php if ( Chained_Products_WC_Compatibility::is_wc_gte_23() ){ ?>
					init_select2();
				<?php } else { ?>
					jQuery('.wc-metaboxes-wrapper').find("select[id^=chained_products_ids_]").chosen();
				<?php } ?>
			});

			<?php if ( !Chained_Products_WC_Compatibility::is_wc_gte_23() ){?>
				setTimeout( function() {
					init_chosen(<?php echo $chained_parent_id; ?>);
				}, 100 );
			<?php } ?>

			var row_id = '<?php echo $row_loop; ?>';

			var wc_table_background = jQuery('#variable_product_options .woocommerce_variation table').css('background');
			jQuery('#chained_products_setting_fields_<?php echo $chained_parent_id; ?>').css( 'background', wc_table_background );

			for (var i = 0; i < row_id; i++) {
				set_unique_product_field( 'chained_products_ids_<?php echo $chained_parent_id; ?>_'+i );
			}        
			jQuery('ajax_chosen_select_products_and_only_variations_<?php echo $chained_parent_id; ?>').live( 'change' , function(){
				
				set_unique_product_field( jQuery(this).attr('id') );
				display_insert_shortcode_message();

			});
									
			function set_unique_product_field( changed_id ) {

				<?php if( Chained_Products_WC_Compatibility::is_wc_22() || Chained_Products_WC_Compatibility::is_wc_21() ) { ?>
					jQuery('div#'+changed_id+'_chosen ul.chosen-choices li.search-field').css( 'display' , 'list-item' );
					jQuery('div#'+changed_id+'_chosen div.chosen-drop').css( 'display' , 'initial' );
					setTimeout(function() {
						
						if( jQuery('div#'+changed_id+'_chosen ul.chosen-choices li').length >= 2 ) {

							jQuery('div#'+changed_id+'_chosen ul.chosen-choices li.search-field').css( 'display' , 'none' );
							jQuery('div#'+changed_id+'_chosen div.chosen-drop').css( 'display' , 'none' );

						}
						
					}, 200 );
				<?php } else { ?>
					jQuery('div#'+changed_id+'_chzn ul.chzn-choices li.search-field').css( 'display' , 'initial' );
					jQuery('div#'+changed_id+'_chzn div.chzn-drop').css( 'display' , 'initial' );
					setTimeout(function() {
					 
						if( jQuery('div#'+changed_id+'_chzn ul.chzn-choices li').length >= 2 ) {

							jQuery('div#'+changed_id+'_chzn ul.chzn-choices li.search-field').css( 'display' , 'none' );
							jQuery('div#'+changed_id+'_chzn div.chzn-drop').css( 'display' , 'none' );

						}
					 
					}, 200 );
				<?php } ?>


			}

			function init_chosen(chained_id) {
				jQuery('select.ajax_chosen_select_products_and_only_variations_'+chained_id).ajaxChosen({
					method: 'GET',
					url:    '<?php echo admin_url( 'admin-ajax.php' ) ?>',
					dataType: 	'json',
					afterTypeDelay: 100,
					data:   {
						action: 'woocommerce_json_search_products_and_only_variations',
						security: '<?php echo wp_create_nonce( 'search-products-and-only-variations' ) ?>'
					}
				}, function (data) {

					var terms = {};

					jQuery.each(data, function (i, val) {
						terms[i] = val;
					});

					return terms;
				});

				if (jQuery('div[id^=chained_products_list_] li.chosen-choices').length <= 0){
					jQuery('div[id^=chained_products_list_] li.search-field input').css('width','100%');
				}
			}


			function getEnhancedSelectFormatString() {

					var formatString = {};

					formatString = {
						formatMatches: function( matches ) {
							if ( 1 === matches ) {
								return cp_select_params.i18n_matches_1;
							}

							return cp_select_params.i18n_matches_n.replace( '%qty%', matches );
						},
						formatNoMatches: function() {
							return cp_select_params.i18n_no_matches;
						},
						formatAjaxError: function( jqXHR, textStatus, errorThrown ) {
							return cp_select_params.i18n_ajax_error;
						},
						formatInputTooShort: function( input, min ) {
							var number = min - input.length;

							if ( 1 === number ) {
								return cp_select_params.i18n_input_too_short_1
							}

							return cp_select_params.i18n_input_too_short_n.replace( '%qty%', number );
						},
						formatInputTooLong: function( input, max ) {
							var number = input.length - max;

							if ( 1 === number ) {
								return cp_select_params.i18n_input_too_long_1
							}

							return cp_select_params.i18n_input_too_long_n.replace( '%qty%', number );
						},
						formatSelectionTooBig: function( limit ) {
							if ( 1 === limit ) {
								return cp_select_params.i18n_selection_too_long_1;
							}

							return cp_select_params.i18n_selection_too_long_n.replace( '%qty%', number );
						},
						formatLoadMore: function( pageNumber ) {
							return cp_select_params.i18n_load_more;
						},
						formatSearching: function() {
							return cp_select_params.i18n_searching;
						}
					};

					return formatString;
				}

				function init_select2() {

					// Ajax product search box
					jQuery( '[id^= "chained_products_ids"]' ).filter( ':not(.chained_enhanced)' ).each( function() {

						var select2_args = {
							allowClear:  jQuery( this ).data( 'allow_clear' ) ? true : false,
							placeholder: jQuery( this ).data( 'placeholder' ),
							minimumInputLength: jQuery( this ).data( 'minimum_input_length' ) ? jQuery( this ).data( 'minimum_input_length' ) : '3',
							escapeMarkup: function( m ) {
								return m;
							},
							maximumSelectionSize : 1,
							ajax: {
						        url:         wc_enhanced_select_params.ajax_url,
						        dataType:    'json',
						        quietMillis: 250,
						        data: function( term, page ) {
						            return {
										term:     term,
										action:   jQuery( this ).data( 'action' ) || 'woocommerce_json_search_products_and_variations',
										security: wc_enhanced_select_params.search_products_nonce,
										exclude:  jQuery( this ).data( 'exclude' )
						            };
						        },
						        results: function( data, page ) {
						        	var terms = [];
							        if ( data ) {
										jQuery.each( data, function( id, text ) {
											terms.push( { id: id, text: text } );
										});
									}
						            return { results: terms };
						        },
						        cache: true
						    }
						};

						if ( jQuery( this ).data( 'multiple' ) === true ) {
							select2_args.multiple = true;
							select2_args.initSelection = function( element, callback ) {
								var data     = jQuery.parseJSON( element.attr( 'data-selected' ) );
								var selected = [];

								jQuery( element.val().split( "," ) ).each( function( i, val ) {
									selected.push( { id: val, text: data[ val ] } );
								});
								return callback( selected );
							};
							select2_args.formatSelection = function( data ) {
								return '<div class=\"selected-option\" data-id=\"' + data.id + '\">' + data.text + '</div>';
							};
						} else {
							select2_args.multiple = false;
							select2_args.initSelection = function( element, callback ) {
								var data = {id: element.val(), text: element.attr( 'data-selected' )};
								return callback( data );
							};
						}

						select2_args = jQuery.extend( select2_args, getEnhancedSelectFormatString() );

						jQuery( this ).select2( select2_args ).addClass( 'enhanced' ).addClass( 'chained_enhanced' );
					});

					if (jQuery('div[id^=chained_products_list_] ul.select2-choices').length <= 1){
						jQuery('div[id^=chained_products_list_] li.select2-search-field input').css('width','100%');
					}				

				}

			function chained_products_add_row(chained_id) {
				jQuery('#add_chained_products_row_'+chained_id).off('click').on( 'click', function() { 
					jQuery('.nested_chained_products_'+chained_id).remove();
					var row_id = jQuery(' [id^= "chained_products_ids_'+chained_id+'"] ').length;
					var current_row_element = jQuery(this).parent();

					var new_row = " <p class='form-field' id='chained_products_row_"+chained_id+"_"+row_id+"'>\
										<label for='chained_products_ids"+chained_id+"_"+row_id+"'>\
											<?php echo __( "Chained Products", self::$text_domain ); ?>\
											<span class='description chained_product_description'></span>\
										</label>\
										<?php if ( Chained_Products_WC_Compatibility::is_wc_gte_23() ){?>\
											<input type='hidden' class='wc-product-search ajax_chosen_select_products_and_only_variations_"+chained_id+"' style='width: 50%;' id='chained_products_ids_"+chained_id+"_"+row_id+"' name='chained_products_ids["+chained_id+"]["+row_id+"]' data-placeholder='<?php _e( 'Search for a product...', 'woocommerce' ); ?>' \
											data-action='woocommerce_json_search_products_and_variations' data-exclude='<?php echo $total_ids_to_exclude; ?>' data-multiple='true'/>\
										<?php } else { ?>\
											<select id='chained_products_ids_"+chained_id+"_"+row_id+"' name='chained_products_ids["+chained_id+"]["+row_id+"]' class='ajax_chosen_select_products_and_only_variations_"+chained_id+"' multiple='multiple' data-placeholder='<?php _e( 'Search for a product...', 'woocommerce' ); ?>'>\
											</select>\
										<?php } ?>\
										<input type='number' class='chained_products_quantity short' name='chained_products_quantity["+chained_id+"]["+row_id+"]' value='1' placeholder='<?php _e( 'Qty', self::$text_domain ); ?>' min='1'>\
										<span class='add_remove_chained_products_row dashicons-plus' id='add_chained_products_row_"+chained_id+"' title='<?php _e( "Add Product", self::$text_domain ); ?>'></span>\
									</p>\
									";

					jQuery('div#chained_products_list_'+chained_id).prepend(new_row);
					current_row_element.find('label').text('');
					current_row_element.find('span.add_remove_chained_products_row')
										.removeClass('dashicons-plus')
										.addClass('dashicons-no')
										.addClass('remove_chained_products_row_'+chained_id)
										.attr('title', '<?php echo __( 'Remove Product', self::$text_domain ); ?>')
										.removeAttr('id')
										.off('click')
										.on('click', function(){
											var id_prefix = 'chained_products_row_',
												ids = current_row_element.attr('id').substr(id_prefix.length).split("_"),
												prev_row_id = ids[1];
											<?php if ( Chained_Products_WC_Compatibility::is_wc_gte_24() ) { ?>
												jQuery( this ).closest( 'div' ).parent().parent().parent().parent().addClass( 'variation-needs-update' );
												jQuery( 'button.cancel-variation-changes, button.save-variation-changes' ).removeAttr( 'disabled' );
												jQuery( '#variable_product_options' ).trigger( 'woocommerce_variations_input_changed' );
											<?php } ?>
											jQuery('p#chained_products_row_'+chained_id+'_'+prev_row_id).remove();
											jQuery('.nested_chained_products_'+chained_id).remove();
											display_insert_shortcode_message();
										});
					chained_products_add_row(chained_id);
					display_insert_shortcode_message();			

					<?php if ( Chained_Products_WC_Compatibility::is_wc_gte_23() ){?>
						init_select2();
					<?php } else { ?>
						init_chosen(chained_id);
					<?php } ?>

				});
			}

			chained_products_add_row(<?php echo $chained_parent_id; ?>);

			jQuery('.wc-metaboxes-wrapper, .woocommerce_options_panel').on('click', '[class^="add_remove_chained_products_row dashicons-no remove_chained_products_row_"]', function() {
				var id_prefix = 'chained_products_row_',
					ids = jQuery(this).parent().attr('id').substr(id_prefix.length).split("_"),
					chained_id = ids[0],
					remove_row = jQuery(this).attr('id');

				<?php if ( Chained_Products_WC_Compatibility::is_wc_gte_24() ) { ?>
					jQuery( this ).closest( 'div' ).parent().parent().parent().parent().addClass( 'variation-needs-update' );
					jQuery( 'button.cancel-variation-changes, button.save-variation-changes' ).removeAttr( 'disabled' );
					jQuery( '#variable_product_options' ).trigger( 'woocommerce_variations_input_changed' );
				<?php } ?>

				jQuery('p#chained_products_row_'+chained_id+'_'+remove_row).remove();
				jQuery('.nested_chained_products_'+chained_id).remove();
				display_insert_shortcode_message();

			});

			display_insert_shortcode_message();

			function display_insert_shortcode_message() {

				setTimeout(function() {

					des_content = jQuery( 'textarea#content' ).val();

					if( des_content.indexOf( "[chained_products" ) == -1 ) {

						if( jQuery('div[id^=chained_products_list_] li.search-choice').length > 0 || jQuery('div[id^=chained_products_list_] li.select2-search-choice').length > 0 )
							jQuery('div.chained_products_shortcode').css( 'display', 'block' );                    		
						else
							jQuery('div.chained_products_shortcode').css( 'display', 'none' );

					} else {       	
						jQuery('div.chained_products_shortcode').css( 'display', 'none' );
					}
				}, 700 );
			}

			jQuery('.wc-metaboxes-wrapper, .woocommerce_options_panel').on('click', 'a.insert_shortcode', function() {
				des_content = jQuery( 'textarea#content' ).val();

				if( des_content.indexOf( "[chained_products" ) == -1 ) {

					if((jQuery( 'textarea#content' ).css( 'display') == 'none' ) ) {
						jQuery( '#content-html' ).trigger( 'click' );
						jQuery( 'textarea#content' ).val( jQuery( 'textarea#content' ).val() + "[chained_products]" );
						jQuery( '#content-tmce' ).trigger( 'click' );
					} else {            			
						jQuery( 'textarea#content' ).val( jQuery( 'textarea#content' ).val() + "[chained_products]" );
					}
				}
				jQuery('div.chained_products_shortcode').css( 'display', 'none' );            	
			
			});

			setTimeout( function(){
				<?php if( Chained_Products_WC_Compatibility::is_wc_22() || Chained_Products_WC_Compatibility::is_wc_21() ) { ?>
					jQuery('[class*=nested_chained_products] .chosen-container-multi .chosen-choices .search-choice .search-choice-close').remove();
				<?php } else { ?>
					jQuery('[class*=nested_chained_products] .chzn-container-multi .chzn-choices .search-choice .search-choice-close').remove();
				<?php } ?>
				jQuery('[class*=nested_chained_products] .chained_products_quantity').attr('readonly', 'readonly')
			}, 500 );

		});
		<?php

		Chained_Products_WC_Compatibility::enqueue_js( ob_get_clean() );
	}

	/**
	 * Function to save chained products detail via both ajax & form submit
	 * 
	 * @param int $product_id
	 */
	public function cp_save_product_variations( $product_id ) {
		
		$variable_product_ids = $_POST['variable_post_id'];

		$update_order_for_products = array();

		foreach ( $variable_product_ids as $variation_id ) {
			if ( ! empty( $_POST['chained_product_update_order'][ $variation_id ] ) && $_POST['chained_product_update_order'][ $variation_id ] == 'on' ) {
				$update_order_for_products[] = $variation_id;
			}
			$this->update_chained_product_data( $variation_id );
		}

		if ( ! empty( $update_order_for_products ) ) {
			$this->update_chained_products_order( $update_order_for_products );
		}

	}

	/**
	 * Save chained products details in product's meta
	 * 
	 * @param int $post_id
	 * @param object $post
	 */
	public function on_process_product_meta( $post_id, $post ) {

		if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( is_int( wp_is_post_revision( $post ) ) ) return;
		if ( is_int( wp_is_post_autosave( $post ) ) ) return;
		if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( $_POST['woocommerce_meta_nonce'], 'woocommerce_save_data' ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;
		if ( $post->post_type != 'product' ) return;
		
		// save variations
		if ( $_POST['product-type'] == 'variable' && !empty( $_POST['variable_post_id'] ) && ! Chained_Products_WC_Compatibility::is_wc_gte_24() ) {

			$this->cp_save_product_variations( $post_id );

		} elseif ( in_array( $_POST['product-type'], $this->get_supported_product_types() ) ) {		// save supported product types

			$this->update_chained_product_data( $post_id );
			if ( ! empty( $_POST['chained_product_update_order'][ $post_id ] ) && $_POST['chained_product_update_order'][ $post_id ] == 'on' ) {
				$this->update_chained_products_order( $post_id );
			}        	

		}

	}

	/**
	 * Supported types 
	 * 
	 * @return array
	 */
	public function get_supported_product_types() {
		return $this->supported_types;
	}

	/**
	 * Update previous orders with new chained products 
	 * 
	 * @global wpdb $wpdb
	 * @param int|array $chained_parent_id
	 */
	public function update_chained_products_order( $chained_parent_id ) {

		global $wpdb;

		$query = "SELECT order_items.order_id, order_itemmeta.meta_key, order_itemmeta.meta_value, order_items.order_item_id
					FROM {$wpdb->prefix}woocommerce_order_items AS order_items
						LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_itemmeta
							ON ( order_items.order_item_id = order_itemmeta.order_item_id )
					WHERE order_itemmeta.meta_key IN ( '_product_id', '_variation_id', '_qty', '_chained_product_of' )
						AND order_items.order_id IN ( SELECT oi.order_id
														FROM {$wpdb->prefix}woocommerce_order_items AS oi
															LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim
																ON ( oi.order_item_id = oim.order_item_id )
														WHERE oim.meta_key IN ( '_product_id', '_variation_id' )
															AND oim.meta_value ";

		if ( is_array( $chained_parent_id ) && count( $chained_parent_id ) > 1 ) {
			$query .= "IN ( " . implode( ',', $chained_parent_id ) . " )";
		} else {
			if ( is_array( $chained_parent_id) ) {
				$chained_parent_id = current( $chained_parent_id );
			}
			$query .= "= {$chained_parent_id}";
		}
													
		$query .= ")";

		/*-----------------------------------------------------------------------------------*/
		/* Fetch all orders having this chained product/s
		/*-----------------------------------------------------------------------------------*/
		$order_items = $wpdb->get_results( $query, "ARRAY_A" );

		$order_with_product = array();
		$order_with_product_details = array();
		$order_with_chained_parent_qty = array();
		$revoke_download = array();

		/*-----------------------------------------------------------------------------------*/
		/* Loop through query result to get order details in following format:
		/*
		/* array(
		/* 			order_id => array(
		/* 								item_id => array(
		/* 													meta_key => meta_value,
		/*													...
		/*												),
		/*								...
		/*							),
		/*			...
		/*		)
		/*
		/*-----------------------------------------------------------------------------------*/
		if ( ! empty( $order_items ) ) {
			foreach ( $order_items as $item ) {
				if ( empty( $order_with_product_details[ $item['order_id'] ] ) ) {
					$order_with_product_details[ $item['order_id'] ] = array();
				}
				if ( empty( $order_with_product_details[ $item['order_id'] ][ $item['order_item_id'] ] ) ) {
					$order_with_product_details[ $item['order_id'] ][ $item['order_item_id'] ] = array();
				}
				$order_with_product_details[ $item['order_id'] ][ $item['order_item_id'] ][ $item['meta_key'] ] = $item['meta_value'];
			}
		}
		if ( ! empty( $order_with_product_details ) ) {

			/*---------------------------------------------------------------------------------*/
			/*
			/* Loop through $order_with_product_details
			/*
			/* Perform following 2 things:
			/*		1. Create array containing all orders with chained parent & its quantity
			/*		2. Create array containing all orders with products in respective order
			/*
			/*---------------------------------------------------------------------------------*/
			foreach ( $order_with_product_details as $order_id => $items ) {
				if ( ! empty( $items ) ) {
					foreach ( $items as $item ) {
						$product_id = ( ! empty( $item['_variation_id'] ) ) ? $item['_variation_id'] : $item['_product_id'];
						/*---------------------------------------------------------------*/
						/* Collect chained parent with its qty in following format
						/*
						/* array(
						/*			order_id => array(
						/*								chained_parent => qty,
						/*								...
						/*							),
						/*			...
						/*		)
						/*
						/*---------------------------------------------------------------*/
						if ( empty( $item['_chained_product_of'] ) ) {
							if ( empty( $order_with_chained_parent_qty[ $order_id ] ) ) {
								$order_with_chained_parent_qty[ $order_id ] = array();
							}
							$order_with_chained_parent_qty[ $order_id ][ $product_id ] = $item['_qty'];
						/*--------------------------------------------------------------------------------*/
						/* Collect order with product in following format
						/*
						/* array(
						/*			order_id => array(
						/*								chained_parent => array(
						/*															chained_item,
						/*															...
						/*														),
						/*								...
						/*							),
						/*			...
						/*		)
						/*
						/*--------------------------------------------------------------------------------*/
						} else {
							if ( empty( $order_with_product[ $order_id ] ) ) {
								$order_with_product[ $order_id ] = array();
							}
							if ( empty( $order_with_product[ $order_id ][ $item['_chained_product_of'] ] ) ) {
								$order_with_product[ $order_id ][ $item['_chained_product_of'] ] = array();
							}
							$order_with_product[ $order_id ][ $item['_chained_product_of'] ][] = $product_id;
						}
					}
				}
			}

			/*-------------------------------------------------------------------------*/
			/* Collect all nested chained products & merge with chained products
			/*
			/* array(
			/*			chained_parent => array(
			/*										chained_item,
			/*										...
			/*									),
			/*			...
			/*		)
			/*-------------------------------------------------------------------------*/
			$all_chained_products_ids = ( ! empty( $_POST['chained_products_ids'] ) ) ? $_POST['chained_products_ids'] : array();
			if ( ! empty( $_POST['nested_chained_products_ids'] ) ) {
				foreach ( $_POST['nested_chained_products_ids'] as $parent_id => $chained_ids ) {
					if ( empty( $all_chained_products_ids[ $parent_id ] ) ) {
						$all_chained_products_ids[ $parent_id ] = array();
					}
					$all_chained_products_ids[ $parent_id ] += $_POST['nested_chained_products_ids'][ $parent_id ];
				}
			}

			/*---------------------------------------------------------------------------*/
			/*
			/* Loop through existing orders
			/*
			/* Perform following 3 things:
			/*		1. Add new chained items in order, if it is added in main product
			/*		2. Update quantity of chained items, if chained item's qty is changed
			/*		3. Remove order item, if chained item is removed from main product
			/*
			/*---------------------------------------------------------------------------*/
			foreach ( $order_with_product_details as $order_id => $items ) {

				if ( ! empty( $items ) ) {
					$order = Chained_Products_WC_Compatibility::get_order( $order_id );
					$added = $updated = $deleted = array();
					foreach ( $items as $item_id => $item ) {
						/*--------------------------*/
						/* Add new chained item
						/*--------------------------*/
						if ( empty( $item['_chained_product_of'] ) ) {
							$chained_parent_id = ( ! empty( $item['_variation_id'] ) ) ? $item['_variation_id'] : $item['_product_id'];
							$chained_product_detail = $this->get_all_chained_product_details( $chained_parent_id );
							if ( ! empty( $all_chained_products_ids[ $chained_parent_id ] ) && ( ! empty( $order_with_product[ $order_id ][ $chained_parent_id ] ) || ! empty( $order_with_chained_parent_qty[ $order_id ][ $chained_parent_id ] ) ) ) {
								$new_chained_items = array();
								if ( ! empty( $order_with_product[ $order_id ][ $chained_parent_id ] ) ) {
									$new_chained_items = array_diff( $all_chained_products_ids[ $chained_parent_id ], $order_with_product[ $order_id ][ $chained_parent_id ] );
								} elseif ( ! empty( $order_with_chained_parent_qty[ $order_id ][ $chained_parent_id ] ) ) {
									/*----------------------------------------------------------------*/
									/* Following line will handle those cases where
									/* a chained product is added to that product
									/* which didn't had any chained products earlier
									/* therefore considering all chained product ids as new chained items
									/*----------------------------------------------------------------*/
									$new_chained_items = $all_chained_products_ids[ $chained_parent_id ];
								}
								if ( ! empty( $new_chained_items ) ) {
									foreach ( $new_chained_items as $item_to_add ) {
										$parent_qty = ( ! empty( $item['_qty'] ) ) ? $item['_qty'] : 1;
										$new_item_id = $this->add_chained_item_in_order( $item_to_add, $order, $chained_parent_id, $chained_product_detail, $parent_qty );
										if ( ! empty( $new_item_id ) ) {
											$this->grant_download_permission_for_chained_item( $item_to_add, $order );
											$added[] = $this->get_product_title( $item_to_add );
										}
									}
								}
							}
							continue;
						}
						$product_id = ( ! empty( $item['_variation_id'] ) ) ? $item['_variation_id'] : $item['_product_id'];
						$chained_parent_id = $item['_chained_product_of'];
						/*-------------*/
						/* Update qty
						/*-------------*/
						if ( ! empty( $all_chained_products_ids[ $chained_parent_id ] ) && in_array( $product_id, $all_chained_products_ids[ $chained_parent_id ] ) ) {
							$index = array_search( $product_id, $all_chained_products_ids[ $chained_parent_id ] );
							$unit = ( ! empty( $_POST['chained_products_quantity'][ $chained_parent_id ][ $index ] ) ) ? $_POST['chained_products_quantity'][ $chained_parent_id ][ $index ] : 1;
							$chained_parent_qty_in_order = ( ! empty( $order_with_chained_parent_qty[ $order_id ][ $chained_parent_id ] ) ) ? $order_with_chained_parent_qty[ $order_id ][ $chained_parent_id ] : 1;
							$new_qty = $chained_parent_qty_in_order * $unit;
							$old_qty = ( ! empty( $item['_qty'] ) ) ? $item['_qty'] : 1;
							if ( $new_qty != $old_qty ) {
								wc_update_order_item_meta( $item_id, '_qty', $new_qty );
								$updated[] = $this->get_product_title( $product_id );
							}
						/*-----------------------*/
						/* Remove chained item
						/*-----------------------*/
						} elseif ( ! empty( $all_chained_products_ids[ $chained_parent_id ] ) && ! in_array( $product_id, $all_chained_products_ids[ $chained_parent_id ] ) ) {
							wc_delete_order_item( $item_id );
							$revoke_download[] = array( 'order_id' => $order_id, 'product_id' => $product_id );
							$deleted[] = $this->get_product_title( $product_id );
						}
					}
					$note  = '';
					if ( ! empty( $added ) ) {
						$note .= sprintf( _n( 'Chained order item %s was added.', 'Chained order items %s were added.', count( $added ), self::$text_domain ), implode( ", ", $added ) );
					}
					if ( ! empty( $updated ) ) {
						$note .= sprintf( _n( 'Quantity of chained order item %s was updated.', 'Quantity of chained order items %s were added.', count( $updated ), self::$text_domain ), implode( ", ", $updated ) );
					}
					if ( ! empty( $deleted ) ) {
						$note .= sprintf( _n( 'Chained order item %s was removed.', 'Chained order items %s were removed.', count( $deleted ), self::$text_domain ), implode( ", ", $deleted ) );
					}
					if ( ! empty( $note ) ) {
						$order->add_order_note( $note, 0 );
					}
				}
			}

			if ( ! empty( $revoke_download ) ) {
				$revoke_download_query = "DELETE FROM {$wpdb->prefix}woocommerce_downloadable_product_permissions WHERE ";
				$revoke_download_segment = array();
				foreach ( $revoke_download as $row ) {
					if ( empty( $row['order_id'] ) || empty( $row['product_id'] ) ) continue;
					$revoke_download_segment[] = "( order_id = {$row['order_id']} AND product_id = {$row['product_id']} )";
				}
				$revoke_download_query .= implode( " OR ", $revoke_download_segment );
				$is_revoked = $wpdb->query( $revoke_download_query );
				if ( $is_revoked === false ) {
					update_option( '_chained_products_revoke_failed_' . time(), $revoke_download );
				}
			}

		}

	}

	/**
	 * Remove shortcode if present in post content
	 * 
	 * @param array $data
	 * @return array $data
	 */
	public function remove_shortcode_from_post_content( $data ) {
	  
		if( isset( $_POST['chained_products_ids'] ) && ! empty( $_POST['chained_products_ids'] ) )
			return $data;
			
		$post_data['post_content'] = $data['post_content'];

		$shortcode_start = strpos( $post_data['post_content'], '[chained_products' );

		if( $shortcode_start !== false ) {

			$shortcode_end = strpos( $post_data['post_content'], "]", $shortcode_start );

			if( $shortcode_end !== false ) {

				$shortcode_length 		= $shortcode_end - $shortcode_start + 1;
				$shortcode 				= substr( $post_data['post_content'], $shortcode_start, $shortcode_length );
				$data['post_content']	= str_replace( $shortcode, "", $post_data['post_content'] );

			}

		}
		
		return $data;
	}

	/**
	 * Update chained product and quantity bundle detail in database
	 * 
	 * @param int $chained_parent_id
	 */
	public function update_chained_product_data( $chained_parent_id ) {

		if( isset( $_POST['chained_products_ids'][$chained_parent_id] ) && ! empty( $_POST['chained_products_ids'][$chained_parent_id] ) ) {
			
			$chained_products_ids 			= $_POST['chained_products_ids'][$chained_parent_id];
			$chained_products_quantity		= $_POST['chained_products_quantity'][$chained_parent_id];

			foreach ( $chained_products_ids as $index => $product_id ) {
				
				if ( ! isset( $chained_products[$chained_parent_id][$product_id] ) ) {
					$chained_products[$chained_parent_id][$product_id] = 0;
				}

				if ( isset( $chained_products_quantity[$index]) && ! empty( $chained_products_quantity[$index] ) ) {
					$chained_products[$chained_parent_id][$product_id] += $chained_products_quantity[$index];
				} else {
					$chained_products[$chained_parent_id][$product_id] += 1;
				}

			}

			$chained_products_detail = array();

			foreach ( $chained_products[$chained_parent_id] as $product_id => $quantity ) {
				
				$product = Chained_Products_WC_Compatibility::get_product( $product_id );

				if ( !empty($product) && ($product->product_type == 'simple' || $product->product_type == 'variation' ) ) {

					$chained_products_detail[$product_id] = array ( 'unit'        	=> $quantity,
																	'product_name'	=> $this->get_product_title( $product_id )
																	);
					$chained_products_ids[] = $product_id;

				}

			}

			update_post_meta( $chained_parent_id, '_chained_product_detail', $chained_products_detail );

			if ( ! empty( $_POST['product-type'] ) && $_POST['product-type'] == 'simple' ) {
				update_post_meta( $chained_parent_id, '_chained_product_ids', $chained_products_ids ); 
			}
		

			if ( get_option( 'woocommerce_manage_stock' ) == 'yes' ) {

				if ( isset( $_POST['chained_products_manage_stock'][$chained_parent_id] ) && $_POST['chained_products_manage_stock'][$chained_parent_id] == 'on' ) {
					update_post_meta( $chained_parent_id, '_chained_product_manage_stock', 'yes' );
				} else {
					update_post_meta( $chained_parent_id, '_chained_product_manage_stock', 'no' );
				}

			}

		} else {

			delete_post_meta( $chained_parent_id, '_chained_product_detail' );
			delete_post_meta( $chained_parent_id, '_chained_product_manage_stock' );

			if ( ! empty( $_POST['product-type'] ) && $_POST['product-type'] == 'simple' ) {
				delete_post_meta( $chained_parent_id, '_chained_product_ids' );
			}

		}
 
	}

	/**
	 * Function to get formatted Product's Name
	 * 
	 * @param int $product_id
	 * @return string $product_title
	 */
	public function get_product_title ( $product_id ) {
		$parent_id = wp_get_post_parent_id ( $product_id );
		$the_title = get_the_title( $product_id );

		if ( $parent_id > 0 ) {
			$_product = Chained_Products_WC_Compatibility::get_product( $product_id );
			$pos = strpos( $the_title, 'of ');
			if ( $pos !== false ) {
				$product_title = substr( $the_title, $pos + 3 );
			} else {
				$product_title = $the_title;
			}
		} else {
			$_product = Chained_Products_WC_Compatibility::get_product( $product_id );
			$product_title = $the_title;
		}

		if ( !empty( $_product->variation_data ) && woocommerce_get_formatted_variation( $_product->variation_data, true ) != '' ) $product_title .= ' ( ' . woocommerce_get_formatted_variation( $_product->variation_data, true ) . ' )';

		return $product_title;
	}

	/**
	 * Function to find whether product is chained to any product
	 * 
	 * @param int $product_id
	 * @return boolean
	 */
	public function is_chained_product( $product_id ) {
		global $wpdb;
		$chained_product_ids = array();
		$results = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = '_chained_product_detail'" );
		foreach ( $results as $result ) {
			$result_unserialized = maybe_unserialize( $result );
			$results_ids = ( !empty( $result ) && is_array($result_unserialized) ) ? array_keys( $result_unserialized ) : array();
			$chained_product_ids = array_merge( $chained_product_ids, $results_ids );
		}
		if ( in_array( $product_id, $chained_product_ids ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Function to find whether product has chained items associated with it
	 * 
	 * @param int $product_id
	 * @return boolean
	 */
	public function has_chained_products( $product_id ) {
		
		$chained_product_detail = get_post_meta( $product_id, '_chained_product_detail', true );
		$chained_product_ids = ( ! empty( $chained_product_detail ) ) ? array_keys( $chained_product_detail ) : array();

		if ( ! empty( $chained_product_ids ) ) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Function to return parent_id if parent_id is greater than 0 or product_id if parent_id is 0
	 * 
	 * @param int $product_id
	 * @return int
	 */
	public function get_parent( $product_id ) {
		$parent_id = wp_get_post_parent_id ( $product_id );

		if ( $parent_id > 0 ) {
			return $parent_id;
		} else {
			return $product_id;
		}
	}


	 /**
	  * Function for creating array of chained products of chained products
	  * 
	  * @global array $total_chained_ids
	  * @global array $chained_series
	  * @global array $remaining_chained_products
	  * @param array $chained_product_ids
	  * @param int $chained_parent_id
	  * @return array $total_chained_ids
	  */
	public function get_all_chained_product_ids( $chained_product_ids, $chained_parent_id ) {
			
	    global $total_chained_ids, $chained_series, $remaining_chained_products;

		$chained_series[] = $chained_parent_id;
		$remaining_chained_products = array_unique( array_merge( $remaining_chained_products, $chained_product_ids ) );

		foreach ( $chained_product_ids as $product_id ) {

			$remaining_chained_products = array_diff( $remaining_chained_products, array( $product_id ) );

			if( in_array( $product_id, $chained_series ) ) {
					continue;
			}

			$total_chained_ids[]    = $product_id;
			$chained_product_detail = get_post_meta( $product_id, '_chained_product_detail', true );

			if ( ! empty( $chained_product_detail[ $chained_parent_id ] ) ) {
				unset( $chained_product_detail[ $chained_parent_id ] );
			}

			$chained_product_ids    = is_array( $chained_product_detail ) ? array_keys( $chained_product_detail ) : array();

			$remaining_chained_products = array_unique( array_merge( $remaining_chained_products, $chained_product_ids ) );

			if( !empty( $chained_product_ids ) ) {
					$this->get_all_chained_product_ids( $remaining_chained_products, $product_id );
					return $total_chained_ids;
			} else {
					continue;
			}
		}

		if( ! empty( $remaining_chained_products ) ) {
			$this->get_all_chained_product_ids( $remaining_chained_products, $chained_parent_id );
		}
		return $total_chained_ids;
	}

	/**
	 * Function for creating array of chained product details of all chained products
	 * 
	 * @global array $total_chained_ids
	 * @global array $total_chained_details
	 * @global array $chained_series
	 * @global array $remaining_chained_products
	 * @param int $chained_parant_id
	 * @return array
	 */
	public function get_all_chained_product_details( $chained_parent_id ) {          
		global $total_chained_ids, $total_chained_details, $chained_series, $remaining_chained_products;
		
		$total_chained_ids = $total_chained_details = $chained_series = $remaining_chained_products = array();
		
		$total_chained_details  = get_post_meta( $chained_parent_id, '_chained_product_detail', true );
		$chained_product_ids    = ( is_array( $total_chained_details ) ) ? array_keys( $total_chained_details ) : null;
	   
		if ( $chained_product_ids == null ) {
			return $total_chained_details;
		}
	   
		$total_chained_ids = array_unique( $this->get_all_chained_product_ids( $chained_product_ids, $chained_parent_id ) );

		if ( is_array( $chained_product_ids ) && in_array( $chained_parent_id, $chained_product_ids ) ) {
			$total_chained_ids[] = $chained_parent_id;
		}

		/*if( sizeof( $total_chained_ids ) == sizeof( $total_chained_details ) )
			return $total_chained_details;*/               
		
		return $this->calculate_all_chained_products_detail( $chained_parent_id, $total_chained_ids, $total_chained_details );                                               
	}
	
	/**
	 * Function for calculating details (quantities) of all nested chained products
	 * 
	 * @param int $chained_parent_id
	 * @param array$total_chained_ids
	 * @param array $total_chained_details
	 * @return array $total_chained_details
	 */
	public function calculate_all_chained_products_detail( $chained_parent_id, $total_chained_ids = array(), $total_chained_details ) {

		if ( ! empty( $total_chained_ids ) && is_array( $total_chained_ids ) ) {

			foreach( $total_chained_ids as $id ) {

	            $product_details = get_post_meta( $id, '_chained_product_detail', true );

	            if ( ! empty( $product_details ) ) {

		            foreach ( $product_details as $product_id => $details ) {

		                if ( ! empty( $total_chained_details ) && is_array( $total_chained_details ) && array_key_exists( $product_id, $total_chained_details ) ) {

		             	 	$product_details[ $product_id ]['unit'] = ( $details['unit'] * $total_chained_details[ $id ]['unit'] ) + $total_chained_details[ $product_id ]['unit'];
		             	    $total_chained_details[ $product_id ]['unit'] =  $product_details[ $product_id ]['unit'];

		             	} else {

		                    $product_details[ $product_id ]['unit'] = $details['unit'] * $total_chained_details[ $id ]['unit'];
		                    $total_chained_details[ $product_id ] = $product_details[ $product_id ];

		                }

		            }

	            }

	        }

        }

        return $total_chained_details;
	}

	/**
	 * Function to get Product's Instance
	 * 
	 * @param int $product_id
	 * @return WC_Product $_product
	 */
	public function get_product_instance( $product_id ) {
		$_product = Chained_Products_WC_Compatibility::get_product( $product_id );
		
		return $_product;
	}

	/**
	 * Function to check whether to show chained items to customer
	 * 
	 * @return boolean
	 */
	public function is_show_chained_items() {
        
        $is_show = get_option( 'sa_show_chained_items_to_customer', 'yes' );

		if ( $is_show == 'no' ) {
			 
			add_filter( 'woocommerce_cart_contents_count', 'sa_cp_get_cart_count' );
		    $bool = false;
	
		} else {			
			$bool = true;
		}

		return apply_filters( 'sa_cp_show_chained_items', $bool, $is_show );
	}

	/**
	 * Function to check whether to show chained item's price
	 * 
	 * @return boolean
	 */
	public function is_show_chained_item_price() {
		
		$is_show = get_option( 'sa_show_chained_item_price', 'no' );

		if ( $is_show == 'yes' ) {
			$bool = true;
		} else {
			$bool = false;
		}

		return apply_filters( 'sa_cp_show_chained_item_price', $bool, $is_show );
		
	}

	/**
	 * function to add more action on plugins page
	 *
	 * @param array $links
	 * @return array $links
	 */
	public function plugin_action_links( $links ) {
        $action_links = array(
            'about' => '<a href="' . admin_url( 'admin.php?page=cp-about' ) . '" title="' . esc_attr( __( 'Know Chained Products', self::$text_domain ) ) . '">' . __( 'About', self::$text_domain ) . '</a>',
        );

        return array_merge( $action_links, $links );
    }

	/**
	 * function to fetch plugin's data
	 */
	public static function get_chained_products_plugin_data() {
		return get_plugin_data( WC_CP_PLUGIN_FILE );
	}
}
?>
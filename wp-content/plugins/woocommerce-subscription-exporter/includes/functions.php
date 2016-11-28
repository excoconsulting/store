<?php
if ( is_admin() ) {

	/* Start of: WordPress Administration */

	function wc_subs_exporter_admin_menu() {
		add_submenu_page( 'woocommerce', __( 'Subscription Export', 'wc-subs-exporter' ), __( 'Subscriptions Export', 'wc-subs-exporter' ), 'manage_options', 'wc-subs-exporter', 'wc_subs_exporter_html_page' );
	}
	add_action( 'admin_menu', 'wc_subs_exporter_admin_menu' );

	function wc_subs_exporter_template_header( $title, $icon ) {
?>
<div class="wrap">
	<div id="icon-<?php echo $icon; ?>" class="icon32"><br /></div>
	<h2>
		<?php echo $title; ?>
		<a href="<?php echo add_query_arg( 'tab', 'export' ); ?>" class="add-new-h2"><?php _e( 'Add New', 'wc-subs-exporter' ); ?></a>
	</h2>
<?php
	}

	function wc_subs_exporter_template_footer() { 
?>
</div>
<?php
	}

	function wc_subs_exporter_generate_csv_header( $type ) {
		header( 'Content-Encoding: UTF-8' );
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename=' . wc_subs_exporter_generate_csv_filename( $type ) );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
	}

	function wc_subs_exporter_generate_csv_filename( $type = '' ) {
		$date = date( 'Ymd:His' );
		if ( $type ) {
			$filename = 'wc_export_subs_' . $type . '-' . $date . '.csv';
		} else {
			$filename = 'wc_export_subs_default-' . $date . '.csv';
		}
		return $filename;
	}

	function wc_subs_exporter_add_post_mime_type( $post_mime_types = array() ) {
		$post_mime_types['text/csv'] = array( __( 'Subscription Exports', 'wc-subs-exporter' ), __( 'Manage Subscription Exports', 'wc-subs-exporter' ), _n_noop( 'Subscription Export <span class="count">(%s)</span>', 'Subscription Exports <span class="count">(%s)</span>' ) );
		return $post_mime_types;
	}
	add_filter( 'post_mime_types', 'wc_subs_exporter_add_post_mime_type' );

	function wc_subs_exporter_read_csv_file( $post = null ) {
		if ( $post->post_type != 'attachment' )
			return false;
		if ( $post->post_mime_type != 'text/csv' )
			return false;

		$filename = $post->post_name;
		$filepath = get_attached_file( $post->ID );
		$contents = __( 'No export entries were found, please try again with different export filters.', 'wc-subs-exporter' );
		if ( file_exists( $filepath ) ) {
			$handle = fopen( $filepath, "r" );
			$contents = stream_get_contents( $handle );
			fclose( $handle );
		}
		if ( $contents ) { 
?>
	<div class="postbox-container">
		<div class="postbox">
			<h3 class="hndle"><?php _e( 'CSV File', 'wc-subs-exporter' ); ?></h3>
			<div class="inside">
				<textarea style="font:12px Consolas, Monaco, Courier, monospace; width:100%; height:200px;"><?php echo $contents; ?></textarea>
			</div>
			<!-- .inside -->
		</div>
		<!-- .postbox -->
	</div>
	<!-- .postbox-container -->
<?php
		}
	}
	add_action( 'edit_form_after_editor', 'wc_subs_exporter_read_csv_file' );

	function wc_subs_exporter_return_export_types() {
		$export_types = array();
		$export_types['subscriptions'] = __( 'Subscriptions', 'wc-subs-exporter' );
		return $export_types;
	}

	function wc_subs_exporter_export_type_label( $export_type = '', $echo = false ) {
		$output = '';
		if ( ! empty( $export_type ) ) {
			$export_types = wc_subs_exporter_return_export_types();
			if ( array_key_exists( $export_type, $export_types ) )
				$output = $export_types[$export_type];
		}
		if ( $echo )
			echo $output;
		else
			return $output;
	}
	
	function wc_subs_exporter_get_transient_name() {
		$post = $_POST;
		unset( $post['action'] );
		return 'subs_exp_' . md5( serialize( $post ) );
	}
	
	function wc_subs_exporter_get_filtered_subscriptions( $export ) {
		global $wpdb;

		$subscriptions = $filtered_subscriptions = array();
		
		$sql = "SELECT DISTINCT i.order_id, m.product_id, p.meta_value
				FROM
				(
				SELECT order_item_id,
				MAX(CASE WHEN meta_key = '_product_id' THEN meta_value END) product_id
				FROM {$wpdb->prefix}woocommerce_order_itemmeta
				WHERE meta_key LIKE '_subscription%' 
					OR meta_key LIKE '_recurring%'
					OR meta_key = '_product_id'
				GROUP BY order_item_id
				HAVING MAX(meta_key LIKE '_subscription%')
					+ MAX(meta_key LIKE '_recurring%') > 0
				) m JOIN {$wpdb->prefix}woocommerce_order_items i 
				ON m.order_item_id = i.order_item_id 
				LEFT JOIN {$wpdb->prefix}postmeta p 
				ON i.order_id = p.post_id 
				AND p.meta_key = '_customer_user'
				LEFT JOIN {$wpdb->prefix}posts po 
				ON p.post_id = po.ID
				WHERE po.post_type = 'shop_order' AND po.post_parent = 0";

		$order_ids_and_product_ids = $wpdb->get_results( $sql );

		foreach ( $order_ids_and_product_ids as $order_id_and_product_id ) {
			if ( empty ( $order_id_and_product_id->product_id ) ) {
				continue;
			}

			$subscription_key = $order_id_and_product_id->order_id . '_' . $order_id_and_product_id->product_id;
			$subscription     = WC_Subscriptions_Manager::get_subscription( $subscription_key );

			if ( empty( $subscription ) ) {
				continue;
			}
			
		// filter status 	
			if ( ! array_key_exists( $subscription['status'], $export->status )  ) {
				continue;
			}

		// filter dates
			if ( $export->dates_from && strtotime( $subscription['start_date'] ) < strtotime( $export->dates_from ) ) {
				continue;
			} elseif ( $export->dates_to && strtotime( $subscription['start_date'] ) > strtotime( $export->dates_to ) ) {
				continue;
			}

			$subscriptions[$order_id_and_product_id->meta_value][$subscription_key] = $subscription;
		}
		
		$processed_rows = 0;
		foreach( $subscriptions as $user => $user_subscriptions ) {
			if ( ++$processed_rows <= $export->offset ) 
				continue;
			foreach( $user_subscriptions as $key => $subscription ) {
				$filtered_subscriptions[ $user ][ $key ] = $subscription;
			}
			if ( $processed_rows == $export->limit_volume )
				break;
		}
		return $filtered_subscriptions;
	}
	
	function wc_subs_exporter_create_csv( $export ) {
		global $wc_subs_exporter;

		if ( ! $export->status ) 
			return false;
				
		if ( empty( $export->subscriptions ) ) {
			return false;
		}
		
		$subscription_statuses = wc_subs_exporter_get_subscription_statuses();
		
		
		$fields = array(
 __('Order Id', 'wc-subs-exporter'),
 __('Order Status', 'wc-subs-exporter'), 
 
 __('Subscription Status', 'wc-subs-exporter'), 
 __('Subscription Description', 'wc-subs-exporter'), 
 __('Subscription Start Date', 'wc-subs-exporter'), 
 __('Subscription Expiration Date', 'wc-subs-exporter'), 
 __('Subscription Last Payment', 'wc-subs-exporter'), 
 
 __('Email', 'wc-subs-exporter'), 
 __('Billing First Name', 'wc-subs-exporter'), 
 __('Billing Last Name', 'wc-subs-exporter'), 
 __('Billing Address 1', 'wc-subs-exporter'), 
 __('Billing Address 2', 'wc-subs-exporter'), 
 __('Billing City', 'wc-subs-exporter'), 
 __('Billing State', 'wc-subs-exporter'), 
 __('Billing Zip', 'wc-subs-exporter'), 
 __('Billing Country', 'wc-subs-exporter'), 
 
 __('Shipping First Name', 'wc-subs-exporter'), 
 __('Shipping Last Name', 'wc-subs-exporter'), 
 __('Shipping Address 1', 'wc-subs-exporter'),
 __('Shipping Address 2', 'wc-subs-exporter'), 
 __('Shipping City', 'wc-subs-exporter'), 
 __('Shipping State', 'wc-subs-exporter'), 
 __('Shipping Zip', 'wc-subs-exporter'), 
 __('Shipping Country', 'wc-subs-exporter'), 
 
 __('Product SKU', 'wc-subs-exporter'), 
 __('Product Description', 'wc-subs-exporter'), 
 __('Quantity', 'wc-subs-exporter'), 
 __('Date Ordered', 'wc-subs-exporter'), 
 
 __('Coupon Code Used', 'wc-subs-exporter'), 
 
 );

		$csv    = '';
		if ( $export->bom )
			$csv .= chr(239) . chr(187) . chr(191) . '';
		foreach ( $fields as $field ) {			
			$csv .= wc_subs_exporter_escape_csv_value( $field, $export->delimiter, $export->escape_formatting ) . $export->delimiter;
		}
		$csv = substr( $csv, 0, -( strlen( $export->delimiter ) ) ) . "\n";
		
		$processed_rows = 0;
		
		foreach( $export->subscriptions as $user => $user_subscriptions ) {
			foreach( $user_subscriptions as $key => $subscription ) {
				$order          = new WC_Order( $subscription['order_id'] );
				$order_item     = WC_Subscriptions_Order::get_item_by_product_id( $order, $subscription['product_id'] );
				$product        = $order->get_product_from_item( $order_item );
				
				if ( empty( $product ) )
					continue;
				
				$product_sku    = $product->get_sku();
				
				$product_title  = WC_Subscriptions_Order::get_item_name( $subscription['order_id'], $subscription['product_id'] );

				if ( isset( $product->variation_data ) ) {
					$product_description = woocommerce_get_formatted_variation( $product->variation_data, true );
				} else {
					$product_description = $product_title; 
				}
				
				$coupon_code_used = '';
				$coupons = $order->get_items( array( 'coupon' ) );
				foreach ( $coupons as $item_id => $item ) {
					$coupon_code_used .= $item['name'] . ' ';
				}
				
				$start_date = substr( $subscription['start_date'], 0, 10 );
				
				if ( $subscription['expiry_date'] ) {
					$end_date = substr( $subscription['expiry_date'], 0, 10 );
				} else {
					$end_date = '0000-00-00';
				}
                
                if ( $order->user_id > 0 ) {
                    $user_info = get_userdata( $order->user_id );
                }
				
				if ( array_key_exists( 'completed_payments', $subscription ) && is_array( $subscription['completed_payments'] ) ) 
					$recent_payment = substr( end( $subscription['completed_payments'] ), 0, 10 );
				else
					$recent_payment = '';
				
				$order_date = substr( $order->completed_date, 0 , 10 );
				
				$csv .= wc_subs_exporter_escape_csv_value( $order->id, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $order->status, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		

				$csv .= wc_subs_exporter_escape_csv_value( $subscription_statuses[ $subscription['status'] ], $export->delimiter, $export->escape_formatting ) . $export->delimiter;
				$csv .= wc_subs_exporter_escape_csv_value( $product_title, $export->delimiter, $export->escape_formatting ) . $export->delimiter;			
				$csv .= wc_subs_exporter_escape_csv_value( $start_date, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $end_date, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $recent_payment, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
	
				$csv .= wc_subs_exporter_escape_csv_value( $user_info->user_email, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $order->billing_first_name, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $order->billing_last_name, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $order->billing_address_1, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $order->billing_address_2, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $order->billing_city, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $order->billing_state, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $order->billing_postcode, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $order->billing_country, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
	
				$csv .= wc_subs_exporter_escape_csv_value( $order->shipping_first_name, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $order->shipping_last_name, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $order->shipping_address_1, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $order->shipping_address_2, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $order->shipping_city, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $order->shipping_state, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $order->shipping_postcode, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $order->shipping_country, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
	
				$csv .= wc_subs_exporter_escape_csv_value( $product_sku, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $product_description, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( 1, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				$csv .= wc_subs_exporter_escape_csv_value( $order_date, $export->delimiter, $export->escape_formatting ) . $export->delimiter;		
				
				$csv .= wc_subs_exporter_escape_csv_value( trim( $coupon_code_used ), $export->delimiter, $export->escape_formatting ) . $export->delimiter;		

				
				$csv .= "\n";
			}
			
		}
		
		if ( ! $csv ) {
			return false;
		} else {
			if ( isset( $wc_subs_exporter['debug'] ) && $wc_subs_exporter['debug'] )
				$wc_subs_exporter['debug_log'] = $csv;
			else
				return $csv;
		}
	}


	function wc_subs_exporter_format_date( $date ) {
		return str_replace( '/', '-', $date );
	}

	/* Export */

	function wc_subs_exporter_admin_active_tab( $tab_name = null, $tab = null ) {
		if ( isset( $_GET['tab'] ) && ! $tab )
			$tab = $_GET['tab'];
		else
			$tab = 'export';

		$output = '';
		if ( isset( $tab_name ) && $tab_name ) {
			if ( $tab_name == $tab )
				$output = ' nav-tab-active';
		}
		echo $output;
	}

	function wc_subs_exporter_tab_template( $tab ) {
		global $wc_subs_exporter;
		switch( $tab ) {
			case 'options':
				$delimiter          = wc_subs_exporter_get_option( 'delimiter', ',' );
				$category_separator = wc_subs_exporter_get_option( 'category_separator', '|' );
				$bom                = wc_subs_exporter_get_option( 'bom', 1 );
				$escape_formatting  = wc_subs_exporter_get_option( 'escape_formatting', 'all' );
				$limit_volume       = wc_subs_exporter_get_option( 'limit_volume', -1 );
				$offset             = wc_subs_exporter_get_option( 'offset', 0 );
				$timeout            = wc_subs_exporter_get_option( 'timeout', 0 );
				$save_csv_archive   = wc_subs_exporter_get_option( 'save_csv_archive', 1 );
				$encoding           = wc_subs_exporter_get_option( 'encoding', 'UTF-8');
				$file_encodings     = mb_list_encodings();
				break;
				
			case 'export':
				if ( 'calculate-export-size' == wc_subs_exporter_get_action() ) {
					$export = new stdClass();
					$export->status       = ( isset( $_POST['status']    ) ) ? $_POST['status'] : false;
					$export->dates_from   = ( isset( $_POST['from_date'] ) ) ? wc_subs_exporter_format_date( $_POST['from_date'] ) : false;
					$export->dates_to     = ( isset( $_POST['to_date']   ) ) ? wc_subs_exporter_format_date( $_POST['to_date'] ) : false;
					$export->limit_volume = wc_subs_exporter_get_option( 'limit_volume', -1 );
					$export->offset       = wc_subs_exporter_get_option( 'offset', 0 );

					// get subscriptions 	
					$subscriptions = wc_subs_exporter_get_filtered_subscriptions( $export );
					// save a transient
					set_transient( wc_subs_exporter_get_transient_name(), $subscriptions, 5 * MINUTE_IN_SECONDS );
				}
				break;

			case 'archive':
				$files = wc_subs_exporter_get_archive_files();
				if ( $files ) {
					foreach( $files as $key => $file )
						$files[$key] = wc_subs_exporter_get_archive_file( $file );
				}
				break;

		}
		include_once( $wc_subs_exporter['abspath'] . '/templates/admin/wc-subs-exporter-admin_' . $tab . '.php' );
	}

	function wc_subs_exporter_save_csv_file_attachment( $filename = '' ) {
		$output = 0;
		if ( ! empty( $filename ) ) {
			$object = array(
				'post_title' => $filename,
				'post_type' => 'woo-export',
				'post_mime_type' => 'text/csv'
			);
			$post_ID = wp_insert_attachment( $object, $filename );
			if ( $post_ID )
				$output = $post_ID;
		}
		return $output;
	}

	function wc_subs_exporter_save_csv_file_guid( $post_ID, $export_type, $upload_url ) {
		add_post_meta( $post_ID, '_wc_subs_export_type', $export_type );
		$object = array(
			'ID' => $post_ID,
			'guid' => $upload_url
		);
		wp_update_post( $object );
	}

	function wc_subs_exporter_memory_prompt() {
		if ( ! wc_subs_exporter_get_option( 'dismiss_memory_prompt', 0 ) ) {
			$memory_limit = (int)( ini_get( 'memory_limit' ) );
			$minimum_memory_limit = 64;
			if ( $memory_limit < $minimum_memory_limit ) {
				ob_start();
				$memory_url = add_query_arg( 'action', 'dismiss_memory_prompt' );
				$message = sprintf( __( 'We recommend setting memory to at least 64MB, your site has %dMB currently allocated. See: <a href="%s" target="_blank">Increasing memory allocated to PHP</a>', 'wc-subs-exporter' ), $memory_limit, 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP' ); ?>
<div class="error settings-error">
	<p>
		<strong><?php echo $message; ?></strong>
		<span style="float:right;"><a href="<?php echo $memory_url; ?>"><?php _e( 'Dismiss', 'wc-subs-exporter' ); ?></a></span>
	</p>
</div>
<?php
				ob_end_flush();
			}
		}
	}

	function wc_subs_exporter_fail_notices() {
		$message = false;
		if ( isset( $_GET['failed'] ) )
			$message = __( 'A WordPress error caused the exporter to fail, please get in touch.', 'wc-subs-exporter' );
		if ( isset( $_GET['empty'] ) )
			$message = __( 'No entries were found, please try again with different filters.', 'wc-subs-exporter' );

		if ( $message ) {
			echo '
				<div class="updated settings-error">
					<p><strong>' . $message . '</strong></p>
				</div>';
		}
	}

	function wc_subs_exporter_get_archive_files() {
		$args = array(
			'post_type' => 'attachment',
			'post_mime_type' => 'text/csv',
			'meta_key' => '_wc_subs_export_type',
			'meta_value' => null,
			'posts_per_page' => -1
		);
		if ( isset( $_GET['filter'] ) ) {
			$filter = $_GET['filter'];
			if ( ! empty( $filter ) )
				$args['meta_value'] = $filter;
		}
		$files = get_posts( $args );
		return $files;
	}

	function wc_subs_exporter_get_archive_file( $file = '' ) {
		$wp_upload_dir           = wp_upload_dir();
		$file->export_type       = get_post_meta( $file->ID, '_wc_subs_export_type', true );
		$file->export_type_label = wc_subs_exporter_export_type_label( $file->export_type );

		if ( empty( $file->export_type ) )
			$file->export_type = __( 'Unassigned', 'wc-subs-exporter' );

		if ( empty( $file->guid ) )
			$file->guid = $wp_upload_dir['url'] . '/' . basename( $file->post_title );

		$file->post_mime_type = get_post_mime_type( $file->ID );
		if ( ! $file->post_mime_type )
			$file->post_mime_type = __( 'N/A', 'wc-subs-exporter' );

		$file->media_icon = wp_get_attachment_image( $file->ID, array( 80, 60 ), true );

		$author_name = get_user_by( 'id', $file->post_author );
		$file->post_author_name = $author_name->display_name;

		$t_time = strtotime( $file->post_date, current_time( 'timestamp' ) );
		$time = get_post_time( 'G', true, $file->ID, false );
		if ( ( abs( $t_diff = time() - $time ) ) < 86400 )
			$file->post_date = sprintf( __( '%s ago' ), human_time_diff( $time ) );
		else
			$file->post_date = mysql2date( __( 'Y/m/d' ), $file->post_date );

		unset( $author_name, $t_time, $time );
		return $file;
	}

	function wc_subs_exporter_archives_quicklink_current( $current = '' ) {
		$output = '';
		if ( isset( $_GET['filter'] ) ) {
			$filter = $_GET['filter'];
			if ( $filter == $current )
				$output = ' class="current"';
		} else if ( $current == 'all' ) {
			$output = ' class="current"';
		}
		echo $output;
	}

	function wc_subs_exporter_archives_quicklink_count( $type = '' ) {
		$output = '0';
		$args = array(
			'post_type' => 'attachment',
			'meta_key' => '_wc_subs_export_type',
			'meta_value' => null,
			'numberposts' => -1
		);
		if ( $type )
			$args['meta_value'] = $type;
		$posts = get_posts( $args );
		if ( $posts )
			$output = count( $posts );
		echo $output;
	}

	/* End of: WordPress Administration */

}

function wc_subs_exporter_escape_csv_value( $value = '', $delimiter = ',', $format = 'all' ) {
	$output = $value;
	if ( ! empty( $output ) ) {
		$output = str_replace( '"', '""', $output );
		$output = str_replace( PHP_EOL, "\r\n", $output );
		switch( $format ) {
			case 'all':
				$output = '"' . $output . '"';
				break;
			case 'excel':
				if ( strstr( $output, $delimiter ) !== false || strstr( $output, "\r\n" ) !== false )
					$output = '"' . $output . '"';
				break;
		}
	}
	return $output;
}

function wc_subs_exporter_get_option( $option = null, $default = false ) {
	global $wc_subs_exporter;
	$output = '';
	if ( isset( $option ) ) {
		$separator = '_';
		$output = get_option( $wc_subs_exporter['prefix'] . $separator . $option, $default );
	}
	return $output;
}

function wc_subs_exporter_update_option( $option = null, $value = null ) {
	global $wc_subs_exporter;
	$output = false;
	if ( isset( $option ) && isset( $value ) ) {
		$separator = '_';
		$output = update_option( $wc_subs_exporter['prefix'] . $separator . $option, $value );
	}
	return $output;
}

function wc_subs_exporter_get_action() {
	if ( isset( $_POST['action'] ) )
		$action = $_POST['action'];
	elseif ( isset( $_GET['action'] ) )
		$action = $_GET['action'];
	else
		$action = false;
	return $action;
}
?>
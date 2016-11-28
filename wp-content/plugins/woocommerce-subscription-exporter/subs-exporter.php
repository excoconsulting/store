<?php
/*
Plugin Name: WooCommerce Subscriptions Exporter
Plugin URI: 
Description: Allows exporting of Subscriptions data.
Version: 1.1.11
Author: PPGR
Author URI: http://ppgr.com.br
Requires at least: 3.5 & WooCommerce 2.0 & WooCommerce Subscriptions 1.4.9
Tested up to: 3.9.1 & WooCommerce 2.1.11 & WooCommerce Subscriptions 1.5.4

	Copyright: © 2013, 2014 PPGR.
	License: GNU General Public License v2.0
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

load_plugin_textdomain( 'wc-subs-exporter', null, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

$wc_subs_exporter = 
	array(
		'debug'    => false,
		'filename' => basename( __FILE__ ),
		'dirname'  => basename( dirname( __FILE__ ) ),
		'abspath'  => dirname( __FILE__ ),
		'relpath'  => basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ),
		'prefix'   => 'wc-subs-exporter',
		'name'     => __( 'WooCommerce Subscription Exporter', 'wc-subs-exporter' ),
		'menu'     => __( 'Subscriptions Exporter', 'wc-subs-exporter' ),
		'title'    => __( 'Subscriptions Exporter', 'wc-subs-exporter' ),
	);
		
include_once( $wc_subs_exporter['abspath'] . '/includes/functions.php' );

if ( is_admin() ) {

	function wc_subs_exporter_get_subscription_statuses() {
			// initialize subscription statuses
		$subscription_statuses = array(
			'active'    => __( 'Active', WC_Subscriptions::$text_domain ),
			'pending'   => __( 'Pending', WC_Subscriptions::$text_domain ),
			'on-hold'   => __( 'Suspended', WC_Subscriptions::$text_domain ),
			'expired'   => __( 'Expired', WC_Subscriptions::$text_domain ),
			'cancelled' => __( 'Cancelled', WC_Subscriptions::$text_domain ),
			'trash'     => __( 'Deleted', WC_Subscriptions::$text_domain ),
		);
		return $subscription_statuses;

	}
	
	function wc_subs_exporter_add_settings_link( $links, $file ) {
		static $this_plugin;
		if ( ! $this_plugin ) $this_plugin = plugin_basename( __FILE__ );
		if ( $file == $this_plugin ) {
			$settings_link = sprintf( '<a href="%s">' . __( 'Export Subscriptions', 'wc-subs-exporter' ) . '</a>', add_query_arg( 'page', 'wc-subs-exporter', 'admin.php' ) );
			array_unshift( $links, $settings_link );
		}
		return $links;
	}
	add_filter( 'plugin_action_links', 'wc_subs_exporter_add_settings_link', 10, 2 );

	function wc_subs_exporter_enqueue_scripts( $hook ) {
		if ( 'woocommerce_page_wc-subs-exporter' == $hook ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style(  'jquery-ui-style', (is_ssl()) ? 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' : 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
			wp_enqueue_script( 'wc_subs_exporter_scripts', plugins_url( '/templates/admin/wc-subs-exporter-admin.js', __FILE__ ), array( 'jquery' ) );
			wp_enqueue_style(  'wc_subs_exporter_styles',   plugins_url( '/templates/admin/wc-subs-exporter-admin.css', __FILE__ ) );
		}
	}
	add_action( 'admin_enqueue_scripts', 'wc_subs_exporter_enqueue_scripts' );
	
	
	function wc_subs_exporter_admin_init() {
		global $wc_subs_exporter;

		$action = wc_subs_exporter_get_action();
		switch( $action ) {

			case 'dismiss_memory_prompt':
				wc_subs_exporter_update_option( 'dismiss_memory_prompt', 1 );
				$url = add_query_arg( 'action', null );
				wp_redirect( $url );
				break;
				
			case 'save-options':
				$export = new stdClass();

				$export->delimiter = ( empty( $_POST['delimiter'] ) ? ',' : $_POST['delimiter'] ) ;
				if ( $export->delimiter <> wc_subs_exporter_get_option( 'delimiter' ) )
					wc_subs_exporter_update_option( 'delimiter', $export->delimiter );
					
				$export->category_separator = $_POST['category_separator'];
				if ( $export->category_separator <> wc_subs_exporter_get_option( 'category_separator' ) )
					wc_subs_exporter_update_option( 'category_separator', $export->category_separator );
				
				$export->bom = $_POST['bom'];
				if ( $export->bom <> wc_subs_exporter_get_option( 'bom' ) )
					wc_subs_exporter_update_option( 'bom', $export->bom );
				
				$export->escape_formatting = $_POST['escape_formatting'];
				if ( $export->escape_formatting <> wc_subs_exporter_get_option( 'escape_formatting' ) )
					wc_subs_exporter_update_option( 'escape_formatting', $export->escape_formatting );
				
				$export->encoding = $_POST['encoding'];
				if ( $export->encoding <> wc_subs_exporter_get_option( 'encoding' ) )
					wc_subs_exporter_update_option( 'encoding', $export->encoding );

				$export->limit_volume = -1;
				if ( ! empty( $_POST['limit_volume'] ) ) {
					$export->limit_volume = $_POST['limit_volume'];
					if ( $export->limit_volume <> wc_subs_exporter_get_option( 'limit_volume' ) )
						wc_subs_exporter_update_option( 'limit_volume', $export->limit_volume );
				}
				
				$export->offset = 0;
				if ( ! empty( $_POST['offset'] ) ) {
					$export->offset = (int)$_POST['offset'];
					if ( $export->offset <> wc_subs_exporter_get_option( 'offset' ) )
						wc_subs_exporter_update_option( 'offset', $export->offset );
				}
				
				$export->save_csv_archive = 0;
				if ( ! empty( $_POST['save_csv_archive'] ) ) {
					$export->save_csv_archive = (int) $_POST['save_csv_archive'];
					if ( $export->limit_volume <> wc_subs_exporter_get_option( 'save_csv_archive' ) )
						wc_subs_exporter_update_option( 'save_csv_archive', $export->save_csv_archive );
				}
				
				$timeout = 600;								
				if ( isset( $_POST['timeout'] ) ) {
					$timeout = $_POST['timeout'];
					if ( $timeout <> wc_subs_exporter_get_option( 'timeout' ) )
						wc_subs_exporter_update_option( 'timeout', $timeout );
				}
				break;


			case 'export':
				$export = new stdClass();
				
				$export->type     = 'subscriptions';
				$export->filename = wc_subs_exporter_generate_csv_filename( $export->type );

				$export->delimiter            = wc_subs_exporter_get_option( 'delimiter', ',' );
				$export->category_separator   = wc_subs_exporter_get_option( 'category_separator', '|' );
				$export->bom                  = wc_subs_exporter_get_option( 'bom', 1 );
				$export->escape_formatting    = wc_subs_exporter_get_option( 'escape_formatting', 'all' );
				$export->limit_volume         = wc_subs_exporter_get_option( 'limit_volume', -1 );
				$export->offset               = wc_subs_exporter_get_option( 'offset', 0 );
				$export->save_csv_archive     = wc_subs_exporter_get_option( 'save_csv_archive', 1 );
				$export->encoding             = wc_subs_exporter_get_option( 'encoding', 'UTF-8');
				
				$file_encodings     = mb_list_encodings();
				
				$export->status     = ( isset( $_POST['status']    ) ) ? $_POST['status'] : false;
				$export->dates_from = ( isset( $_POST['from_date'] ) ) ? wc_subs_exporter_format_date( $_POST['from_date'] ) : false;
				$export->dates_to   = ( isset( $_POST['to_date']   ) ) ? wc_subs_exporter_format_date( $_POST['to_date'] ) : false;
				
				if ( ! ini_get( 'safe_mode' ) ) {
					$timeout = wc_subs_exporter_get_option( 'timeout' );
					set_time_limit( $timeout );
				}

				@ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );
				
				// get subscriptions

				$transient_subscriptions = get_transient( wc_subs_exporter_get_transient_name() );
				
				if ( $transient_subscriptions ) 
					$export->subscriptions = $transient_subscriptions;
				else 
					$export->subscriptions = wc_subs_exporter_get_filtered_subscriptions( $export );
				
		
				if ( isset( $wc_subs_exporter['debug'] ) && $wc_subs_exporter['debug'] ) {
				
					wc_subs_exporter_create_csv( $export );
					
				} else {
				
					/* Generate CSV contents */

					$bits = wc_subs_exporter_create_csv( $export );
					if ( ! $bits ) {
						wp_redirect( add_query_arg( 'empty', 1 ) );
						exit();
					}
					if ( ! $export->save_csv_archive ) {

						/* Print to browser */

						wc_subs_exporter_generate_csv_header( $export->type );
						echo $bits;
						exit();

					} else {

						/* Save to file and insert to WordPress Media */

						if ( $export->filename && $bits ) {
							$post_ID     = wc_subs_exporter_save_csv_file_attachment( $export->filename );
							$upload      = wp_upload_bits( $export->filename, null, $bits );
							$attach_data = wp_generate_attachment_metadata( $post_ID, $upload['file'] );
							wp_update_attachment_metadata( $post_ID, $attach_data );
							
							if ( $post_ID )
								wc_subs_exporter_save_csv_file_guid( $post_ID, $export->type, $upload['url'] );
								
							wc_subs_exporter_generate_csv_header( $export->type );
							readfile( $upload['file'] );
						} else {
							wp_redirect( add_query_arg( 'failed', true ) );
						}
						exit();
					}
				}
				break;
			default:
				break;
		}
	}
	add_action( 'admin_init', 'wc_subs_exporter_admin_init' );

	function wc_subs_exporter_html_page() {
		global $wc_subs_exporter, $export;
		wc_subs_exporter_template_header( $wc_subs_exporter['title'], 'tools' );
		$action = wc_subs_exporter_get_action();
		switch( $action ) {
			case 'save-options': 
				$message = __( 'Subscriptions Exporter options saved.', 'wc-subs-exporter' );
				$output   = '<div class="updated"><p><strong>' . $message . '</strong></p></div>';
				echo $output;
				wc_subs_exporter_manage_form();
				break;
				
			case 'export':
				if ( isset( $wc_subs_exporter['debug'] ) && $wc_subs_exporter['debug'] ) {
					if ( ! isset( $wc_subs_exporter['debug_log'] ) )
						$wc_subs_exporter['debug_log'] = __( 'No export entries were found, please try again with different filters.', 'wc-subs-exporter' );
					$output = '<h3>' . sprintf( __( 'Export Log: %s', 'wc-subs-exporter' ), $export->filename ) . '</h3>';
					$output .= '<textarea id="export_log">' . $wc_subs_exporter['debug_log'] . '</textarea>';
				} else {
					$message = __( 'Selected subscriptions data has been exported to an archive file.', 'wc-subs-exporter' );
					$output   = '<div class="updated settings-error"><p><strong>' . $message . '</strong></p></div>';
				}
				echo $output;
				wc_subs_exporter_manage_form();
				break;
				
			default:
				wc_subs_exporter_manage_form();
				break;
		}
		wc_subs_exporter_template_footer();
	}

	function wc_subs_exporter_manage_form() {
		global $wc_subs_exporter;

		if ( isset( $_GET['tab'] ) )
			$tab = $_GET['tab'];
		else 
			$tab = 'export';
		
		$url = add_query_arg( 'page', 'wc-subs-exporter' );
		wc_subs_exporter_memory_prompt();
		wc_subs_exporter_fail_notices();
		include_once( 'templates/admin/wc-subs-exporter-admin.php' );
	}
}
?>
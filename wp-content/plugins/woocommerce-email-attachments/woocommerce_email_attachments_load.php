<?php
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

global $wc_email_att_skip_files, $wc_email_att_htaccess, $wc_email_att_plugin_base_name, $wc_email_att_are_activation_hooks;

$wc_email_att_plugin_path = str_replace( basename( __FILE__ ), '', __FILE__ );

/**
 * load woocommerce_email_attachments main class, which loads needed classes with autoload when needed
 **/
require_once $wc_email_att_plugin_path . 'classes/class-wc-email-att.php';

	// create object
WC_Email_Att::instance();

WC_Email_Att::$show_activation = true;			//	true to show deactivation and uninstall checkbox
WC_Email_Att::$show_uninstall = true;
WC_Email_Att::$plugin_path = $wc_email_att_plugin_path;
WC_Email_Att::$plugin_url = trailingslashit( plugins_url( '', plugin_basename( __FILE__ ) ) );		//	also set in init hook to allow other plugins to change it in a filter hook
WC_Email_Att::$plugin_base_name = $wc_email_att_plugin_base_name;
WC_Email_Att::$skip_files = $wc_email_att_skip_files;

WC_Email_Att_Func::$htaccess = $wc_email_att_htaccess;

WC_Email_Att::instance()->init();
	

	//	allow plugins to load, which depend on this plugin being already loaded
do_action( 'wc_email_att_plugin_loaded' );


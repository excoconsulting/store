<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function rml_install($errorlevel = false) {
	global $wpdb;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$charset_collate = $wpdb->get_charset_collate();
	$table_name = RML_Core::getInstance()->getTableName();
	$blog_id = get_current_blog_id();
	
	// Avoid errors
	if ($errorlevel === false) {
		$show_errors = $wpdb->show_errors(false);
		$suppress_errors = $wpdb->suppress_errors(false);
		$errorLevel = error_reporting();
		error_reporting(0);
	}

	// Table realmedialibrary
	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		parent mediumint(9) DEFAULT '-1' NOT NULL,
		name tinytext NOT NULL,
		slug text DEFAULT '' NOT NULL,
		absolute text DEFAULT '' NOT NULL,
		bid mediumint(10) DEFAULT $blog_id NOT NULL,
		ord mediumint(10) DEFAULT 999 NOT NULL,
		type mediumint(2) DEFAULT 0 NOT NULL,
		restrictions varchar(255) DEFAULT '' NOT NULL,
		cnt mediumint(10) DEFAULT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";
	dbDelta( $sql );
	
	if ($errorlevel) {
		$wpdb->print_error();
	}
	
	// Table realmedialibrary_order
	$table_name = RML_Core::getInstance()->getTableName("order");
	$sql = "CREATE TABLE $table_name (
		attachment bigint(20) NOT NULL,
		fid mediumint(9) DEFAULT '-1',
		nr bigint(20),
		oldCustomNr bigint(20) DEFAULT null,
		UNIQUE KEY rmlorder (attachment,fid)
	) $charset_collate;";
	dbDelta( $sql );
	
	if ($errorlevel) {
		$wpdb->print_error();
	}
	
	// Table realmedialibrary_meta
	$table_name = RML_Core::getInstance()->getTableName("meta");
	$sql = "CREATE TABLE $table_name (
	  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	  `realmedialibrary_id` bigint(20) unsigned NOT NULL DEFAULT '0',
	  `meta_key` varchar(255) DEFAULT NULL,
	  `meta_value` longtext,
	  PRIMARY KEY  (meta_id),
	  KEY realmedialibrary_id (realmedialibrary_id),
	  KEY meta_key (meta_key)
	) $charset_collate;";
	dbDelta( $sql );
	
	if ($errorlevel) {
		$wpdb->print_error();
	}
	
	if ($errorlevel === false) {
		$wpdb->show_errors($show_errors);
		$wpdb->suppress_errors($suppress_errors);
		error_reporting($errorLevel);
	}
	
	do_action("RML/Version/Update");
	update_option( 'rml_db_version', RML_VERSION );
}

/*
function jal_install_data() {
	global $wpdb;
	
	$welcome_name = 'Mr. WordPress';
	$welcome_text = 'Congratulations, you just completed the installation!';
	
	$table_name = $wpdb->prefix . 'liveshoutbox';
	
	$wpdb->insert( 
		$table_name, 
		array( 
			'time' => current_time( 'mysql' ), 
			'name' => $welcome_name, 
			'text' => $welcome_text, 
		) 
	);
}
*/
<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

define( 'WP_ROCKET_ADVANCED_CACHE', true );
$rocket_cache_path = '/Applications/MAMP/htdocs/store/wp-content/cache/wp-rocket/';
$rocket_config_path = '/Applications/MAMP/htdocs/store/wp-content/wp-rocket-config/';

if ( file_exists( '/Applications/MAMP/htdocs/store/wp-content/plugins/wprocket/inc/front/process.php' ) ) {
	include( '/Applications/MAMP/htdocs/store/wp-content/plugins/wprocket/inc/front/process.php' );
} else {
	define( 'WP_ROCKET_ADVANCED_CACHE_PROBLEM', true );
}
<?php
/**
Plugin Name: WP Real Media Library
Plugin URI: http://matthias-web.de
Description: Organize your wordpress media library in a nice way.
Author: Matthias Günter
Version: 2.6.3
Author URI: http://matthias-web.de
Licence: GPLv2
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if (defined('RML_PATH')) {
	return;
}

define('RML_PATH', dirname ( __FILE__ ));
define('RML_FILE', __FILE__);
define('RML_TD', 'real-media-library');
define('RML_VERSION', '2.6.3');
define('RML_PRE_GET_POSTS_PRIORITY', 9999999);

/**
 * CONSTANT FOLDER TYPES!
 */
define('RML_TYPE_FOLDER', 0);
define('RML_TYPE_COLLECTION', 1);
define('RML_TYPE_GALLERY', 2);
define('RML_TYPE_ALL', 3);
define('RML_TYPE_ROOT', 4);

// Localize the plugin
add_action( 'plugins_loaded', "rml_plugins_laoded" );
function rml_plugins_laoded() {
    load_plugin_textdomain( RML_TD, FALSE, dirname(plugin_basename(__FILE__)).'/languages/' );
}

// Load core
require_once(dirname ( __FILE__ ) . '/inc/general/Core.class.php');

/*
USE Query with custom attribute rml_folder
$query = new WP_Query(array(
	'post_status' => 'inherit',
	'post_type' => 'attachment',
	'rml_folder' => 4
	//'meta_query' => array(array('key' => '_rml_folder',        'value' => 4,         'compare' => '='        ))
));
RML_Core::print_r($query);*/

// Start core
RML_Core::start();

// Matthias advert
require_once(dirname ( __FILE__ ) . '/inc/others/advert.php');
register_activation_hook( __FILE__, 'matthiasweb_advert_activation' );
?>
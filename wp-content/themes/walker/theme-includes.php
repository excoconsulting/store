<?php

//define constants
define('EDGE_ROOT', get_template_directory_uri());
define('EDGE_ROOT_DIR', get_template_directory());
define('EDGE_ASSETS_ROOT', get_template_directory_uri().'/assets');
define('EDGE_ASSETS_ROOT_DIR', get_template_directory().'/assets');
define('EDGE_FRAMEWORK_ROOT', get_template_directory_uri().'/framework');
define('EDGE_FRAMEWORK_ROOT_DIR', get_template_directory().'/framework');
define('EDGE_FRAMEWORK_ADMIN_ASSETS_ROOT', get_template_directory_uri().'/framework/admin/assets');
define('EDGE_FRAMEWORK_MODULES_ROOT', get_template_directory_uri().'/framework/modules');
define('EDGE_FRAMEWORK_MODULES_ROOT_DIR', get_template_directory().'/framework/modules');
define('EDGE_THEME_ENV', 'dev');

//include necessary files
include_once EDGE_ROOT_DIR.'/framework/edgt-framework.php';
include_once EDGE_ROOT_DIR.'/includes/nav-menu/edgt-menu.php';
include_once EDGE_ROOT_DIR.'/includes/sidebar/edgt-custom-sidebar.php';
include_once EDGE_ROOT_DIR.'/includes/edgtf-related-posts.php';
include_once EDGE_ROOT_DIR.'/includes/edgt-options-helper-functions.php';
include_once EDGE_ROOT_DIR.'/includes/sidebar/sidebar.php';
require_once EDGE_ROOT_DIR.'/includes/plugins/class-tgm-plugin-activation.php';
include_once EDGE_ROOT_DIR.'/includes/plugins/plugins-activation.php';
include_once EDGE_ROOT_DIR.'/assets/custom-styles/general-custom-styles.php';
include_once EDGE_ROOT_DIR.'/assets/custom-styles/general-custom-styles-responsive.php';

if(!is_admin()) {
    include_once EDGE_ROOT_DIR.'/includes/edgt-body-class-functions.php';
    include_once EDGE_ROOT_DIR.'/includes/edgt-loading-spinners.php';
}
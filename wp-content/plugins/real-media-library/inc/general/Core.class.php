<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Singleton Core Class
 * 
 * Handles all actions and filter. It includes all available
 * Classes that handle the callbacks.
 */
class RML_Core {
    
    private static $me = null;

    private function __construct() {
        
    }

    public function paging() {
        // Permissions
        add_filter('RML/Folder/TreeNodeLi/Class',               array(RML_Permissions::getInstance(), 'liClass'), 10, 2);
        add_filter('RML/Validate/Insert',                       array(RML_Permissions::getInstance(), 'insert'), 10, 3);
        add_filter('RML/Validate/Create',                       array(RML_Permissions::getInstance(), 'create'), 10, 4);
        add_filter('RML/Validate/Rename',                       array(RML_Permissions::getInstance(), 'setName'), 10, 3);
        add_filter('RML/Validate/Delete',                       array(RML_Permissions::getInstance(), 'deleteFolder'), 10, 3);
        add_filter('wp_die_ajax_handler',                       array($this, 'update_count'));
        add_filter('wp_die_handler',                            array($this, 'update_count'));
        
        // Register actions
        add_action('init',                                      array($this, 'init'));
        add_action('admin_init',                                array(RML_Options::getInstance(), 'register_fields'));
        add_action('plugins_loaded',                            array($this, 'update_db_check') );
        
        // Others
        register_activation_hook( RML_FILE, 'rml_install' );
    }
    
    public function init() {
        global $shortcode_tags;
        add_shortcode("folder-gallery", $shortcode_tags['gallery']);
        
        // Add our folder shortcode
        RML_Folder_Shortcode::getInstance();
        
        /**
         * ================================= ACTIONS
         * General actions
         */
        if (RML_Options::load_frontend()) {
            add_action('wp_enqueue_scripts',                    array(RML_Backend::getInstance(), 'admin_enqueue_scripts') );
            add_action('wp_footer',                             array(RML_Backend::getInstance(), 'admin_footer'));
        }
        
        add_action('admin_enqueue_scripts',                     array(RML_Backend::getInstance(), 'admin_enqueue_scripts') );
        add_action('admin_footer',                              array(RML_Backend::getInstance(), 'admin_footer'));
        
        add_action('pre_get_posts',                             array(RML_Filter::getInstance(), 'pre_get_posts'), RML_PRE_GET_POSTS_PRIORITY); //999999
        add_action('add_post_meta',                             array(RML_Filter::getInstance(), 'add_post_meta'), 10, 3);
        add_action('update_postmeta',                           array(RML_Filter::getInstance(), 'update_post_meta'), 10, 4);
                
        add_action('wp_ajax_rml_bulk_move',                     array(RML_Ajax::getInstance(), 'wp_ajax_bulk_move'));
        add_action('wp_ajax_rml_bulk_sort',                     array(RML_Ajax::getInstance(), 'wp_ajax_bulk_sort'));
        add_action('wp_ajax_rml_folder_count',                  array(RML_Ajax::getInstance(), 'wp_ajax_folder_count'));
        add_action('wp_ajax_rml_folder_rename',                 array(RML_Ajax::getInstance(), 'wp_ajax_folder_rename'));
        add_action('wp_ajax_rml_folder_delete',                 array(RML_Ajax::getInstance(), 'wp_ajax_folder_delete'));
        add_action('wp_ajax_rml_folder_create',                 array(RML_Ajax::getInstance(), 'wp_ajax_folder_create'));
        add_action('wp_ajax_rml_wipe',                          array(RML_Ajax::getInstance(), 'wp_ajax_wipe'));
        add_action('wp_ajax_rml_sidebar_resize',                array(RML_Ajax::getInstance(), 'wp_ajax_sidebar_resize'));
        add_action('wp_ajax_rml_tree_content',                  array(RML_Ajax::getInstance(), 'wp_ajax_tree_content'));
                
        add_action('wp_prepare_attachment_for_js',              array(RML_Filter::getInstance(), 'wp_prepare_attachment_for_js'), 10, 3);
        add_action('add_attachment',                            array(RML_Filter::getInstance(), 'add_attachment'));
        add_action('delete_attachment',                         array(RML_Filter::getInstance(), 'delete_attachment'));
                
        add_action('RML/Structure/Rebuild',                     array(RML_Util::getInstance(), 'structure_rebuild'));
        add_action('RML/Backend/JS_Localize',                   array(RML_Util::getInstance(), 'nonces'));
        
        // Order
        add_action('RML/Backend/JS_Localize',                   array(RML_Order::getInstance(), 'js_localize'));
        add_action('RML/Item/Move',                             array(RML_Order::getInstance(), 'item_move'), 10, 3);
        add_action('RML/Item/Moved',                            array(RML_Order::getInstance(), 'item_moved_single'), 10, 3);
        add_action('RML/Item/MoveFinished',                     array(RML_Order::getInstance(), 'item_move_finished'), 10, 3);
        add_action('delete_attachment',                         array(RML_Order::getInstance(), 'delete_attachment'));
                
        add_action('RML/Options/Register',                      array(RML_Order_Options::getInstance(), 'register'));
        
        add_action('wp_ajax_rml_attachment_order',              array(RML_Order_Ajax::getInstance(), 'wp_ajax_attachment_order'));
        add_action('wp_ajax_rml_attachment_order_reset_all',    array(RML_Order_Ajax::getInstance(), 'wp_ajax_attachment_order_reset_all'));
        add_action('wp_ajax_rml_attachment_order_reset',        array(RML_Order_Ajax::getInstance(), 'wp_ajax_attachment_order_reset'));
        add_action('wp_ajax_rml_attachment_order_reindex',      array(RML_Order_Ajax::getInstance(), 'wp_ajax_attachment_order_reindex'));
        add_action('wp_ajax_rml_attachment_order_by',           array(RML_Order_Ajax::getInstance(), 'wp_ajax_attachment_order_by'));
        add_action('wp_ajax_rml_attachment_order_by_last_custom', array(RML_Order_Ajax::getInstance(), 'wp_ajax_attachment_order_by_last_custom'));
        
        // Meta data
        add_action('RML/Folder/Deleted',                        array(RML_Meta::getInstance(), 'folder_deleted'), 10, 2);

        add_action('wp_ajax_rml_meta_content',                  array(RML_Meta_Ajax::getInstance(), 'wp_ajax_meta_content'));
        add_action('wp_ajax_rml_meta_save',                     array(RML_Meta_Ajax::getInstance(), 'wp_ajax_meta_save'));
        
        /**
         * ================================= FILTERS
         * General filters:
         */
        add_filter('attachment_fields_to_edit',                 array(RML_CustomField::getInstance(), 'attachment_fields_to_edit'), 10, 2);
        add_filter('attachment_fields_to_save',                 array(RML_CustomField::getInstance(), 'attachment_fields_to_save'), 10 , 2);
                
        add_filter('restrict_manage_posts',                     array(RML_Filter::getInstance(), 'restrict_manage_posts'));
        add_filter('ajax_query_attachments_args',               array(RML_Filter::getInstance(), 'ajax_query_attachments_args'));
        add_filter('mla_media_modal_query_final_terms',         array(RML_Filter::getInstance(), 'ajax_query_attachments_args'));

        add_filter('shortcode_atts_gallery',                    array(RML_Folder_Shortcode::getInstance(), 'shortcode_atts_gallery'), 10, 3 );
                
        // Order
        add_filter('posts_clauses',                             array(RML_Order::getInstance(), 'posts_clauses'), 10, 2);
        add_filter('RML/Folder/TreeNode/Href',                  array(RML_Order::getInstance(), 'treeHref'), 10, 3);
        add_filter('mla_media_modal_query_final_terms',         array(RML_Order::getInstance(), 'mla_media_modal_query_final_terms'), 10, 2);
        
        add_filter('RML/Folder/Meta/ActionButtons',             array(RML_Order_Options::getInstance(), 'meta_actionbuttons'), 10, 2);
        add_filter("RML/Folder/TreeNode/Content",               array(RML_Order_Options::getInstance(), 'treeNode_content'), 10, 2);
                
        add_filter('RML/Backend/Nonces',                        array(RML_Order_Ajax::getInstance(), 'nonces'));
        
        // Meta data
        add_filter('RML/Backend/Nonces',                        array(RML_Meta_Ajax::getInstance(), 'nonces'));
        
        /**
         * ================================= OTHERS
         */
        if (current_user_can("upload_files"))
            add_thickbox();
        add_rml_meta_box( "general", RML_Meta::getInstance(), false, 0 );
        add_rml_meta_box( "galleryOrder", new RML_MetaGalleryOrder(), false, 999 );
        add_rml_meta_box( "actions", new RML_MetaActions(), false, 999 );
        //add_rml_meta_box( "coverImage", new RML_CoverImage(), true );
        //add_rml_meta_box( "description", new RML_MetaDescription, false );
    }
    
    /**
     * Include all necessery files and classes
     */
    public function include_all() {
        $pathes = array(
            "inc/attachment/Folder.class.php",
            "inc/attachment/Structure.class.php",
            "inc/attachment/CustomField.class.php",
            "inc/attachment/Filter.class.php",
            "inc/attachment/Permissions.class.php",
            
            "inc/metadata/Meta.interface.php",
            
            "inc/order/Order.class.php",
            "inc/order/Options.class.php",
            "inc/order/Ajax.class.php",
            "inc/order/GalleryOrder.class.php",
            
            "inc/metadata/Meta.class.php",
            "inc/metadata/Ajax.class.php",
            "inc/metadata/CoverImage.class.php",
            "inc/metadata/Actions.class.php",
            "inc/metadata/Description.class.php",
            
            "inc/general/Options.class.php",
            "inc/general/Util.class.php",
            "inc/general/QueryCount.class.php",
            "inc/general/Backend.class.php",
            "inc/general/View.class.php",
            "inc/general/Ajax.class.php",
            "inc/general/FolderShortcode.class.php",
            
            "inc/others/install.php",
            
            "inc/api/api.php",
            "inc/api/meta.php"
            );
        
        for ($i = 0; $i < count($pathes); $i++) {
            require_once(RML_PATH . '/' . $pathes[$i]);
        }
    }
    
    public function update_db_check() {
        $installed = get_option( 'rml_db_version' );
        if ($installed != RML_VERSION) {
            rml_install();
        }
    }
    
    /**
     * Hack the wp die filter to make the last update count.
     * 
     * @filter wp_die_ajax_handler
     * @filter wp_die_handler
     * @see RML_Structure::wp_die
     */
    public function update_count($str) {
        RML_Structure::getInstance()->wp_die();
        return $str;
    }

    public function getTableName($name = "") {
        return self::tableName($name);
    }
    
    public static function tableName($name = "") {
        global $wpdb;
        return $wpdb->prefix . "realmedialibrary" . (($name == "") ? "" : "_" . $name);
    }
    
    /**
     * Starts the plugin settings
     */
    public static function start() {
        
        $instance = self::getInstance();
        $instance->include_all();
        $instance->paging();
        
    }
    
    public static function print_r($row) {
        echo '<pre>';
        print_r($row);
        echo '</pre>';
    }
    
    public static function getInstance() {
        if (self::$me == null) {
            self::$me = new RML_Core();
        }
        return self::$me;
    }
    
    public static function get_object_vars_from_public($obj) {
        return get_object_vars($obj);
    }
    
}
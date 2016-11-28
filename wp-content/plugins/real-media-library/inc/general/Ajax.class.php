<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class RML_Ajax {
	private static $me = null;
        
    private function __construct() {
            
    }
    
    /**
     * Wipes the RML settings. That means: Attachment relations to
     * the folders and the folders.
     * 
     * @REQUEST method 'all' or 'rel'
     */
    public function wp_ajax_wipe() {
        // Security checks
        RML_Util::getInstance()->checkNonce('rmlAjaxWipe', 'manage_options');
        
        // Process
        global $wpdb;
        $table_name = RML_Core::getInstance()->getTableName();
        $table_order = RML_Core::getInstance()->getTableName("order");
        
        $sqlMeta = "UPDATE $wpdb->postmeta SET meta_value = -1 WHERE meta_key = '_rml_folder'";
        $sqlFolders = "DELETE FROM $table_name";
        $sqlOrder = "DELETE FROM $table_order";
        
        $method = $_REQUEST["method"];
        if ($method == "all") {
            $wpdb->query($sqlMeta);
            $wpdb->query($sqlFolders);
            $wpdb->query($sqlOrder);
        }else if ($method == "rel") {
            $wpdb->query($sqlMeta);
            $wpdb->query($sqlOrder);
        }
        
        do_action("RML/Wipe/" + $method);
        wp_send_json_success();
    }
    
    /**
     * Creates a folder.
     * 
     * @POST name The name of the folder
     * @POST parent The ID of the parent folder, use -1 for Root level
     * @POST type The type of the folder (see /real-media-library.php contants)
     */
    public function wp_ajax_folder_create() {
        // Security checks
        RML_Util::getInstance()->checkNonce('rmlAjaxFolderCreate');
        
        // Process
        $name = isset($_POST["name"]) ? $_POST["name"] : "";
        $parent = isset($_POST["parent"]) ? $_POST["parent"] : -1;
        $type = isset($_POST["type"]) ? $_POST["type"] : -1;
        
        $result = wp_rml_create($name, $parent, $type);
        
        if (is_array($result)) {
            wp_send_json_error($result);
        }else{
            wp_send_json_success(array("id" => $result));
        }
    }
    
    /**
     * Renames a folder.
     * 
     * @POST name The new name of the folder
     * @POST id The folder id which should be renamed
     */
    public function wp_ajax_folder_rename() {
        // Security checks
        RML_Util::getInstance()->checkNonce('rmlAjaxFolderRename');
        
        // Process
        $name = isset($_POST["name"]) ? $_POST["name"] : "";
        $id = isset($_POST["id"]) ? $_POST["id"] : -1;
        
        $result = wp_rml_rename($name, $id);
        
        if ($result === true) {
            $folder = wp_rml_get_by_id($id, null, true);
            wp_send_json_success(array(
                "slug" => $folder->absolutePath()
            ));
        }else{
            wp_send_json_error($result);
        }
    }
    
    /**
     * Deletes a folder.
     * 
     * @POST id The folder id
     */
    public function wp_ajax_folder_delete() {
        // Security checks
        RML_Util::getInstance()->checkNonce('rmlAjaxFolderDelete');
        
        // Process
        $id = isset($_POST["id"]) ? $_POST["id"] : -1;
        
        $result = wp_rml_delete($id);
        
        if ($result === true) {
            wp_send_json_success();
        }else{
            wp_send_json_error($result);
        }
    }
    
    /**
     * Moves one or more attachments to a given folder.
     * 
     * @POST ids (array) One or more attachment ids
     * @POST to The folder id
     */
    public function wp_ajax_bulk_move() {
        // Security checks
        RML_Util::getInstance()->checkNonce('rmlAjaxBulkMove');
        
        // Process
        $ids = isset($_POST["ids"]) ? $_POST["ids"] : null;
        $to = isset($_POST["to"]) ? $_POST["to"] : null;
        
        $result = wp_rml_move($to, $ids);
        
        if (is_array($result)) {
            wp_send_json_error($result);
        }else{
            wp_send_json_success();
        }
    }
    
    /**
     * Sorts the folder tree.
     * 
     * @POST ids (array) Serialized array of the new tree structure
     */
    public function wp_ajax_bulk_sort() {
        // Security checks
        RML_Util::getInstance()->checkNonce('rmlAjaxBulkSort');
        
        // Process
        $ids = isset($_POST["ids"]) ? $_POST["ids"] : null;
        if (!is_array($ids) || count($ids) == 0) {
            wp_send_json_error(__("Something went wrong."));
        }
        
        // fid (folderid): pid: (parentid)
        $struct = RML_Structure::getInstance();
        
        $changer = array(); // This folders can be changed
        
        // Check, if types are right
        $i = 0;
        $foundError = false;
        foreach ($ids as $value) {
            $fid = $value["fid"]; // Folder ID
            $pid = $value["pid"]; // Parent ID
            
            if ($pid === "-1") {
                // You can modify the root folder ID, for example for other users media library
	            $pid = apply_filters("RML/BulkSort/ParentRoot", $pid);
            }
            
            // Check
            if (!is_numeric($fid) || !is_numeric($pid)) {
                continue;
            }
            
            // Execute
            $fid = $struct->getFolderById($fid);
            if ($fid !== null && $struct->isAllowedTo($pid, $fid->type)) {
                // Check, if parent may have this name as folder.
                
                $changer[] = array($fid, $pid, $i);
            }else{
                $foundError = true;
                break;
            }
            
            $i++;
        }
        
        // Change it!
        if ($foundError) {
            wp_send_json_error(__("Something went wrong. Please be sure folders can not be in collections and galleries, collections can only be in folders and other collections and galleries can only be in collections.", RML_TD));
        }else{
            foreach ($changer as $value) {
                $value[0]->setParent($value[1], $value[2]);
            }
            do_action("RML/Structure/Rebuild");
            wp_send_json_success();
        }
    }
    
    /**
     * Get the current folder count of one or more folders.
     * 
     * @REQUEST ids (array|string) Array or imploded (,) string of folder ids
     *                             Use ALL for the all files count
     * @NOTICE this should be optimized with a single query
     */
    public function wp_ajax_folder_count() {
        // Security checks
        RML_Util::getInstance()->checkNonce('rmlAjaxFolderCount');
        
        $result = array();
        $struct = RML_Structure::getInstance();
        
        // Default folder counts
        $result[""] = $struct->getCntAttachments();
        $result["-1"] = $struct->getCntRoot();
        
        // Iterate through our folders
        $folders = $struct->getRows();
        if (is_array($folders)) {
            foreach ($folders as $value) {
                $query = new RML_WP_Query_Count(
                    apply_filters('RML/Folder/QueryCountArgs', array(
                    	'post_status' => 'inherit',
                    	'post_type' => 'attachment',
                    	'rml_folder' => $value->id
                    ))
                );

                $result[$value->id] = isset($query->posts[0]) ? $query->posts[0] : 0;
            }
        }
        
        $result = apply_filters('RML/Folder/QueryCount', $result);
        wp_send_json_success($result);
    }
    
    /**
     * Save the size of the resized sidebar so the sidebar.dummy.php
     * can modify the CSS.
     * 
     * @POST width The new width of the sidebar
     */
    public function wp_ajax_sidebar_resize() {
        // Security checks
        RML_Util::getInstance()->checkNonce('rmlAjaxSidebarResize');
        
        // Process
        $width = isset($_POST["width"]) ? $_POST["width"] : 0;
        
        if ($width > 0) {
            setcookie( "rml_" . get_current_blog_id() . "_resize", $width, strtotime( '+365 days' ), '/' );
            wp_send_json_success();
        }else{
            wp_send_json_error();
        }
    }
    
    /**
     * Print out the content for the meta options (custom fields)
     * for a given folder id.
     * 
     * @POST folderId the folder id
     */
    public function wp_ajax_meta_content() {
        // Security checks
        RML_Util::getInstance()->checkNonce('rmlAjaxMetaContent');
        
        // Process
        echo RML_Meta::getInstance()->content();
    }
    
    /**
     * Get the HTML for the real media library nodes.
     * 
     * @void
     */
    public function wp_ajax_tree_content() {
        // Security checks
        RML_Util::getInstance()->checkNonce('rmlAjaxTreeContent');
        
        // Tree HTML
        $result = array();
        $folders = RML_Structure::getInstance();
        $view = $folders->getView();
        $result["nodes"] = $view->treeHTML("");
        
        // Slug array
        $result["namesSlug"] = $view->namesSlugArray();
        
        // Names slug array
        wp_send_json_success($result);
    }
    
    public static function getInstance() {
        if (self::$me == null) {
                self::$me = new RML_Ajax();
        }
        return self::$me;
    }
}

?>
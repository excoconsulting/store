<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * DEFINED POST TYPES
 * 
 *      define('RML_TYPE_FOLDER', 0);
 *      define('RML_TYPE_COLLECTION', 1);
 *      define('RML_TYPE_GALLERY', 2);
 *
 * ==========================================
 * 
 * Example Szenario #1:
 *   1. User navigates to http://example.com/rml/collection1
 *   2. Use wp_rml_get_by_absolute_path("/collection1") to get the RML_Folder Object
 *   3. (Additional check) $folder->is(RML_TYPE_COLLECTION) to check, if it is a collection.
 *   4. Iterate the childrens with foreach ($folder->children as $value) { }
 *   5. In collection can only be other collections or galleries.
 * 
 *   6. (Additional check) $value->is(RML_TYPE_GALLERY) to check, if it is a gallery.
 *   7. Fetch the IDs with $value->fetchFileIds();
 * 
 * ==========================================
 * 
 * If you want to use more attractive functions look into the RML_Structure Class.
 * You easily get it with RML_Structure::getInstance() (Singleton).
 * 
 * Meaning: Root = Unorganized Pictures
 * 
 * ==========================================
 * 
 * ORDER QUERY
 * 
 * Using the custom order of galleries: In your get_posts()
 * query args use the option "orderby" => "rml" to get the
 * images ordered by custom user order.
 * 
 * ==========================================
 * 
 * CUSTOM FIELDS FOR FOLDERS, COLLECTIONS, GALLERIES, ....
 * 
 * You want create your own custom fields for a rml object?
 * Have a look at the RML_Meta class.
 * 
 * @see inc/metadata/Meta.class.php RML_Meta
 * 
 * ==========================================
 * 
 * EXAMPLES
 * 
 * Filters and actions:
 *      RML/Folder/QueryArgs
 *      RML/Folder/QueryResult
 *      RML/Folder/QueryCountArgs
 *      RML/Folder/Deleted
 *      RML/Folder/Created
 *      RML/Folder/Renamed
 *      RML/Folder/Moved
 *      RML/Item/Moved
 *      RML/Item/MoveFinished
 *      RML/Folder/TreeNode/Icon
 *      RML/Folder/TreeNode/Class
 *      RML/Folder/TreeNode/Content
 *      RML/Folder/TreeNode/Href
 * 
 * Here are a few options for the actions and filters (this are not all)
 * 
 * EXAMPLES IMPLEMENTATION
 * 
 * apply_filters('RML/Folder/QueryArgs', $args)
 *      $args           Arguments for read query WP_Query
 * 
 * apply_filters('RML/Folder/QueryResult', $posts)
 *      $posts          Result of WP_Query
 * 
 * apply_filters('RML/Folder/QueryCountArgs', $args)
 *      $args           Arguments for count query RML_WP_Query_Count class
 * 
 * do_action("RML/Folder/Deleted", $id, $posts)
 *      $id             ID of the deleted folder
 *      $posts          WP_Query result of folders content
 * 
 * do_action("RML/Folder/Created", $parent, $name, $type, $id)
 *      $parent         Parent id
 *      $name           New name of the folder
 *      $type           RML_TYPE_... constant
 *      $id             Folder id
 * 
 * do_action('RML/Folder/Renamed', $name, $obj)
 *      $name           New name of the folder
 *      $obj            Folder object RML_Folder
 * 
 * do_action('RML/Folder/Moved', $obj, $id, $ord, $force)
 *      $obj            Folder object RML_Folder
 *      $id             ID of the moved folder
 *      $ord            Order number of the moved folder
 *      $force          Boolean
 * 
 * do_action("RML/Item/Moved", $value, $fromFid, $toFid)
 *      $value          Post ID of attachment
 *      $fromFid        From folder id
 *      $toFid          To folder id
 * 
 * do_action("RML/Item/MoveFinished", $folder, $ids);
 *      $folder         Folder object RML_Folder or null for root
 *      $ids            Array of moved post ids
 */

if (!function_exists('wp_attachment_folder')) {
    /**
     * Returns the folder id of an given attachment
     * 
     * @param $attachmentId The attachment ID, if you pass an array you get an array of folder IDs
     * @return Folder ID or "" or Array
     */
    function wp_attachment_folder($attachmentId) {
        if (is_array($attachmentId)) {
            if (count($attachmentId) > 0) {
                global $wpdb;
                $attachments_in = implode(",", $attachmentId);
                $folders = $wpdb->get_col("SELECT DISTINCT(wpp2.meta_value) FROM $wpdb->postmeta AS wpp2 WHERE wpp2.meta_key='_rml_folder' AND wpp2.post_id IN ($attachments_in)");
                return $folders;
            }
            return array();
        }
        return RML_Filter::getInstance()->getAttachmentFolder($attachmentId);
    }
}

if (!function_exists('wp_attachment_order_update')) {
    /**
     * Moves an attachment before or after another attachment in the
     * order table.
     * 
     * @param currentId The attachment which should be moved
     * @param nextId The attachment next to the currentId, if it is
     *               false the currentId should be moved to the end of table.
     * @return boolean
     */
    function wp_attachment_order_update($currentId, $nextId) {
        return RML_Order::getInstance()->update($currentId, $nextId);
    }
}

if (!function_exists('wp_attachment_order_number')) {
    /**
     * Get the order number for a specific attachment.
     * 
     * @param $attachmentId The attachment id
     * @return Int or false
     */
    function wp_attachment_order_number($attachmentId) {
        return RML_Order::getInstance()->getNr($attachmentId);
    }
}

if (!function_exists('wp_rml_root_childs')) {
    /**
     * Gets the first level childs of the media library.
     * 
     * @return Array of RML_Folder objects
     */
    function wp_rml_root_childs() {
        return RML_Structure::getInstance()->getTree();
    }
}

if (!function_exists('wp_rml_select_tree')) {
    /**
     * Returns a .rml-root-list with an given tree. The selected folder id is
     * saved automatically in a hidden input type.
     * 
     * @param inputName the name for the hidden input type and the name for the list
     * @param selected the selected folder id (saved also in hidden input type)
     * @param tree Array of RML_Folder objects
     * @param extraClasses classes for the rml root list container
     * @return Formatted HTML string
     * 
     * Experimental:
     * <strong>Note #1</strong> The select tree has a javascript callback when it
     * is initalized. You can bind it with this snippet:
     * 
     * window.rml.hooks.register("customList", function(obj, $) {
     *       //if (obj.hasClass("my-extra-class")) {
     *            alert(obj.html());
     *       //}
     * });
     * 
     * <strong>Note #2</strong> If you want to use the select tree after a DOM change (ajax,
     * for example: Modal dialog in visual editor) please call the javascript function
     * window.rml.library.customLists() to affect the initalization referred to Note #1.
     * 
     * <strong>Note #3</strong> You can use a sub class of RML_Folder to customize your tree.
     * 
     * @see To see an demo how to use it, have a look at ../inc/admin_footer/sidebar.dummy.php:96
     * @see ../assets/js/library.js:customLists()
     * @see Filters are available for the TreeNode: RML_View
     */
    function wp_rml_select_tree($inputName, $selected, $tree, $extraClasses = "") {
        $output = '<div class="aio-tree rml-root-list rml-custom-list ' . $extraClasses . '" id="rml-list-' . $inputName . '" data-id="' . $inputName . '">
                <input type="hidden" name="' . $inputName . '" value="' . $selected . '" />
                
                <div class="aio-list-standard">
                    <div class="aio-nodes">
                        ' . RML_Structure::getInstance()->getView()->treeHTML($selected, $tree, $inputName) . '
                    </div>
                </div>
            </div>';
        return $output;
    }
}

if (!function_exists('wp_rml_create')) {
    /**
     * Creates a folder. At first it checks if a folder in parent already exists.
     * Then it checks if the given type is allowed in the parent.
     * 
     * @param $name String Name of the folder
     * @param $parent int ID of the parent (-1 for root)
     * @param $type integer 0|1|2 @see Folder.class.inc
     * @param $restrictions Restrictions for this folder, see RML_Permissions
     * @param $supress_validation Supress the permission validation
     * @return  int (ID) when successfully => Check with is_numeric($result)
     *          array with error strings
     * @attention Use RML_Structure::getInstance()->resetData() to register the
     * new created folder/folders to the structure.
     */
    function wp_rml_create($name, $parent, $type, $restrictions = array(), $supress_validation = false) {
        return RML_Structure::getInstance()->createFolder($name, $parent, $type, $restrictions, $supress_validation);
    }
}

if (!function_exists('wp_rml_rename')) {
    /**
     * Renames a folder and then checks, if there is no duplicate folder in the
     * parent folder.
     * 
     * @param $name String New name of the folder
     * @param $id The ID of the folder
     * @param $supress_validation Supress the permission validation
     * @return true or Array with errors
     */
    function wp_rml_rename($name, $id, $supress_validation = false) {
        $folder = wp_rml_get_by_id($id, null, true);
        if ($folder !== null) {
            return $folder->setName($name, $supress_validation);
        }else{
            return array(__("The given folder does not exist or you can not rename this folder.", RML_TD));
        }
    }
}

if (!function_exists('wp_rml_delete')) {
    /**
     * Deletes a folder by ID.
     * 
     * @param $id The ID of the folder
     * @param $supress_validation Supress the permission validation
     * @return true or Array with errors
     */
    function wp_rml_delete($id, $supress_validation = false) {
        return RML_Structure::getInstance()->deleteFolder($id, $supress_validation);
    }
}

if (!function_exists('wp_rml_move')) {
    /**
     * Moves a set of attachments to a specific folder.
     * 
     * @param $to Folder ID, if folder not exists then root will be
     * @param $ids Array of attachment ids
     * @param $supress_validation Supress the permission validation
     * @return true or Array with errors
     * 
     * <strong>Note: </strong> Do not use update_post_meta _rml_folder without
     * the given actions. This is needed for the order.
     * @see RML_Order
     */
    function wp_rml_move($to, $ids, $supress_validation = false) {
        $folder = RML_Structure::getInstance()->getFolderById($to);
        $folderId = $folder !== null ? $folder->id : -1;
        
        do_action("RML/Item/Move", $folderId, $ids, $folder);
        
        if ($folder !== null) {
            $errors = $folder->insert($ids, $supress_validation);
        }else{
            // There can not be errors, expect the "mov" permission (@see RML_Permission)
            if (is_array($ids)) {
                // You can modify the root folder ID, for example for other users media library
    	        $parent = apply_filters("RML/BulkSort/ParentRoot", -1);
                
                $validIds = array();
                foreach ($ids as $value) {
                    if ($supress_validation === false) {
                        $errors = RML_Permissions::insert(array(), $value, null);
                        if (count($errors) > 0) {
                            break;
                        }
                    }
                    
                    $validIds[] = $value;
                }
                
                // Get the folder IDs of the attachments
                $foldersToUpdate = wp_attachment_folder($validIds);
                
                // Update the folder
                foreach ($validIds as $value) {
                    update_post_meta($value, "_rml_folder", $parent);
                }
                wp_rml_update_count($foldersToUpdate);
            }
        }
        
        do_action("RML/Item/MoveFinished", $folderId, $ids, $folder);
        return isset($errors) && count($errors) > 0 ? $errors : true;
    }
}

if (!function_exists('wp_rml_update_count')) {
    /**
     * Handle the count cache for the folders. This should avoid
     * a lack SQL subquery which loads data from the postmeta table.
     * 
     * @param $folders Array of folders ID, if null then all folders with cnt = NULL are updated
     * @param $attachments Array of attachments ID, is merged with $folders if given
     */
    function wp_rml_update_count($folders = null, $attachments = null) {
        RML_Structure::getInstance()->updateCountCache($folders, $attachments);
    }
}

if (!function_exists('wp_rml_dropdown')) {
    /**
     * This functions returns a HTML formatted string which contains
     * <options> elements with all folders, collections and galleries.
     * 
     * @param $selected The selected item
     *              "": "All Files"
     *              -1: "Root"
     *              int: Folder ID
     * @param $disabled array Defines, which folder types are disabled (@see ./real-media-library.php for Constant-Types)
     *                        Default disabled is RML_TYPE_COLLECTION
     * @param $useAll boolean Defines, if "All Files" should be showed
     * @return String
     */
    function wp_rml_dropdown($selected, $disabled, $useAll = true) {
        return RML_Structure::getInstance()->optionsFasade($selected, $disabled, $useAll);
    }
}

if (!function_exists('wp_rml_dropdown_collection')) {
    /**
     * This functions returns a HTML formatted string which contains
     * <options> elements with all folders, collections and galleries.
     * Note: Only COLLECTIONS are SELECTABLE!
     * 
     * @param $selected The selected item
     *              "": "All Files"
     *              -1: "Root"
     *              int: Folder ID
     * @return String
     */
    function wp_rml_dropdown_collection($selected) {
        return wp_rml_dropdown($selected, array(0,2,3,4));
    }
}

if (!function_exists('wp_rml_dropdown_gallery')) {
    /**
     * This functions returns a HTML formatted string which contains
     * <options> elements with all folders, collections and galleries.
     * Note: Only GALLERIES are SELECTABLE!
     * 
     * @param $selected The selected item
     *              "": "All Files"
     *              -1: "Root"
     *              int: Folder ID
     * @return String
     */
    function wp_rml_dropdown_gallery($selected) {
        return wp_rml_dropdown($selected, array(0,1,3,4));
    }
}

if (!function_exists('wp_rml_dropdown_gallery_or_collection')) {
    /**
     * This functions returns a HTML formatted string which contains
     * <options> elements with all folders, collections and galleries.
     * Note: Only GALLERIES AND COLLECTIONS are SELECTABLE!
     * 
     * @param $selected The selected item
     *              "": "All Files"
     *              -1: "Root"
     *              int: Folder ID
     * @return String
     */
    function wp_rml_dropdown_gallery_or_collection($selected) {
        return wp_rml_dropdown($selected, array(0,3,4));
    }
}

if (!function_exists('wp_rml_is_type')) {
    /**
     * Determines, if a Folder is a special folder type.
     * 
     * @param $folder RML_Folder or int
     * @param $allowed array Defines, which folder types are allowed (@see ./real-media-library.php for Constant-Types) 
     * @return boolean
     */
    function wp_rml_is_type($folder, $allowed) {
        if (!$folder instanceof RML_Folder) {
            $folder = wp_rml_get_by_id($folder, null, true);
            
            if (!$folder instanceof RML_Folder) {
                return false;
            }
        }
        
        return in_array($folder->type, $allowed);
    }
}

if (!function_exists('wp_rml_get_by_id')) {
    /**
     * This functions checks if a specific folder exists by ID and is
     * a given allowed RML Folder Type. If the given folder is -1 you will
     * get the first level folders.
     * 
     * @param $id int Folder ID
     * @param $allowed array Defines, which folder types are allowed (@see ./real-media-library.php for Constant-Types)
     *                       If it is null, all folder types are allowed.
     * @param $mustBeFolderObject Defines if the function may return the wp_rml_root_childs result
     * @return RML_Folder object or NULL
     * 
     * Note: The Folder ID must be a valid Folder ID, not Root and "All Files" => FolderID > -1
     */
    function wp_rml_get_by_id($id, $allowed = null, $mustBeFolderObject = false) {
        if (!is_numeric($id)) {
            return null;
        }
        
        if ($mustBeFolderObject == false && ($id == -1 || $id == "-1")) {
            return wp_rml_root_childs();
        }
        
        $folder = RML_Structure::getInstance()->getFolderByID($id);
        
        if (is_array($allowed)) {
            if (!wp_rml_is_type($folder, $allowed)) {
                return null;
            }
        }
        
        return $folder;
    }
}

if (!function_exists('wp_rml_get_by_absolute_path')) {
    /**
     * This functions checks if a specific folder exists by absolute path and is
     * a given allowed RML Folder Type.
     * 
     * @param $path string Folder Absolute Path
     * @param $allowed array Defines, which folder types are allowed (@see ./real-media-library.php for Constant-Types)
     *                       If it is null, all folder types are allowed.
     * @return RML_Folder object or NULL
     * 
     * Note: The absolute path may not be "/" (Root).
     */
    function wp_rml_get_by_absolute_path($path, $allowed = null) {
        $folder = RML_Structure::getInstance()->getFolderByAbsolutePath($path);
        
        if (is_array($allowed)) {
            if (!wp_rml_is_type($folder, $allowed)) {
                return null;
            }
        }
        
        return $folder;
    }
}

if (!function_exists('wp_rml_test_showcase')) {
    /**
     * Outputs a few options for the api usage
     */
    function wp_rml_test_showcase() {
        echo '<br /><br />
                Selected: Root; All folder types allowed; "All Files" disabled
                <select>
                        ' . wp_rml_dropdown(-1, array(), false) . '
                </select>';
                
        echo '<br /><br />
                Selected: All Files; Only folders allowed; "All Files" allowed
                <select>
                        ' . wp_rml_dropdown(-1, array(RML_TYPE_COLLECTION, RML_TYPE_GALLERY), true) . '
                </select>';
                
        echo '<br /><br />
                Select a collection
                <select>
                        ' . wp_rml_dropdown_collection("") . '
                </select>';
                
        echo '<br /><br />
                Select a gallery
                <select>
                        ' . wp_rml_dropdown_gallery("") . '
                </select>';
                
        echo '<br /><br />
                Select a gallery or collection
                <select>
                        ' . wp_rml_dropdown_gallery_or_collection("") . '
                </select>';
                
        echo '<br /><br />
                Get Folder with childrens by Absolute Path
        ';
        RML_Core::print_r(wp_rml_get_by_absolute_path("/others/from-you/2016"));
        
        RML_Core::print_r(wp_rml_root_childs());
        
        echo '<br /><br />
                Check if root has child folder
        ';
        var_dump(RML_Structure::getInstance()->hasChildSlug(-1, "others", false));
    }
}

?>
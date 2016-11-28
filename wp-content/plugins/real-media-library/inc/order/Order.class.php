<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * @see order.js
 */
class RML_Order {
	private static $me = null;
	private $addedFilter = false;
	private $needsIndex = array();      // Multidemnsional array [folderID] with result of this::needsIndex @deprecated
	
	private $pushTo = array();          // The push to process @see this::items_push
	private $removeSQLs = array();      // The removeSQLs => iterate down from order for specific items
	private $deleteIds = array();       // Ids of items, which needs to be removed DELETE
	private $avoidFids = array();       // List of folder ids, which have no order yet => no push process will be proceeded
	
	private $table_name;
	private $debug_sqls = false;
        
    private function __construct() {
        $this->table_name = RML_Core::getInstance()->getTableName("order");
    }
    
    /**
     * Create a toolbar icon to move.
     * This should be only visible if we are in orderby
     * post_date DESC and in the gallery are more images > 1
     * Otherwise redirect to to order by date with <a>-class "_external".
     * 
     * @filter RML/Backend/JS_Localize
     */
    public function js_localize($arr) {
        $mode = get_user_option( 'media_library_mode', get_current_user_id() ) ? get_user_option( 'media_library_mode', get_current_user_id() ) : 'grid';
        
        if ((!isset($_GET["orderby"]) 
                || $_GET["orderby"] !== "rml"
                || isset($_GET["attachment-filter"]))
                && $mode != "grid") {
            $query = array();
            if (isset($_GET["rml_folder"])) {
                $query["rml_folder"] = $_GET["rml_folder"];
            }
            $query["orderby"] = "rml";
            $query["order"] = "asc";
            $href = "?" . http_build_query($query);
            $arr["wpListModeOrder"] = $href;
        }else{
            $arr["wpListModeOrder"] = "1";
        }
        return $arr;
    }
    
    /**
     * Add GET query paramter for galleries.
     */
    public function treeHref($query, $id, $type) {
        if ($type == RML_TYPE_GALLERY) {
            $query['orderby'] = "rml";
            $query['order'] = "asc";
        }else{
            unset($query['orderby']);
            unset($query['order']);
        }
        
        return $query;
    }
    
    /**
     * Index the order table for specific folder. Note: All order
     * of the given folder will be deleted.
     * 
     * @param id ID of the folder
     * @param delete Delete the order
     */
    public function index($id, $delete = true) {
        global $wpdb;
        
        // First, delete the old entries from this folder
        if ($delete) {
            $isValid = $this->delete_order($id);
            if (!$isValid) {
                return false;
            }
        }
        
        // Create INSERT-SELECT statement for this folder
        $sql = $wpdb->prepare("INSERT INTO " . $this->table_name . " (`attachment`, `fid`, `nr`, `oldCustomNr`) 
                SELECT
                	wpp2.ID AS attachment,
                	wpp2.meta_value AS fid,
                	@rownum := @rownum + 1 AS nr,
                	@rownum AS oldCustomNr
                FROM (SELECT @rownum := 0) AS r,
                	(SELECT wpp.ID, wppm.meta_value
                		FROM " . $wpdb->posts . " AS wpp
                		INNER JOIN " . $wpdb->postmeta . " AS wppm ON ( wpp.ID = wppm.post_id )
                		WHERE 1=1
                		AND (( wppm.meta_key = '_rml_folder' AND CAST(wppm.meta_value AS CHAR) = %s ))
                		AND wpp.post_type = 'attachment'
                		AND ((wpp.post_status = 'inherit'))
                		GROUP BY wpp.ID ORDER BY wpp.post_date DESC, wpp.ID DESC) 
                	AS wpp2", $id);
        $this->debug_sqls($sql);
        RML_Util::getInstance()->query($sql);
    }
    
    /**
     * Check if a specifc folder needs a index run.
     * Check count of attachments in folders and count
     * in order table.
     * 
     * For example: read out the attachments and push
     * it to the gallery.
     * 
     * @param $fid The folder id
     * @return object   [0] the count in wp_posts
     *                  [1] count in order table
     *                  [2] boolean needsIndex
     */
    public function needsIndex($fid, $execute = false) {
        global $wpdb;
        
        $sql = $wpdb->prepare("SELECT COUNT(*) AS cnt
                        		FROM " . $wpdb->posts . " AS wpp
                        		INNER JOIN " . $wpdb->postmeta . " AS wppm ON ( wpp.ID = wppm.post_id )
                        		WHERE 1=1
                        		AND (( wppm.meta_key = '_rml_folder' AND CAST(wppm.meta_value AS CHAR) = %s ))
                        		AND wpp.post_type = 'attachment'
                        		AND ((wpp.post_status = 'inherit'))
                        	UNION ALL
                        	    SELECT COUNT(*) AS cnt
                        	    FROM " . $this->table_name . " AS rmlorder
                        	    WHERE rmlorder.fid = %s", $fid, $fid);
	    
	    //$this->debug_sqls($sql);
	    $result = $wpdb->get_results($sql, ARRAY_A);
	    $result["needsIndex"] = $result[0]["cnt"] < $result[1]["cnt"];
	    
	    if ($execute) {
	        if (wp_rml_is_type($fid, array(RML_TYPE_GALLERY))) {
                if ($result[1]["cnt"] <= 0 && $result[0]["cnt"] > 0) {
                    $this->index($fid, false);
                }
            }
	    }
	    
	    return $result;
    }
    
    /**
     * This function retrieves the order of the order
     * table and removes empty spaces, for example:
     * 0 1 5 7 8 9 10 =>
     * 0 1 2 3 4 5 6
     * 
     * Note: This function should be called, if the SUM
     * of order nr is bigger than !COUNT (f)
     * 
     * @param $fid The folder id
     * @return boolean
     */
    public function reindex($fid) {
        if ($fid > 0 && ($max = $this->getMaximal($fid)) !== false) {
            $folder = RML_Structure::getInstance()->getFolderByID($fid);
            if ($folder != null) {
                $ids = $folder->fetchFileIds("ASC", "rml");
                if (count($ids) > 0) {
                    // There is an order, now process
                    global $wpdb;
                    
                    $sqls = array();
                    
                    for ($i = 0; $i < count($ids); $i++) {
                        $sqls[] = "UPDATE " . $this->table_name . " SET nr=" . ($i+1) . " WHERE fid=" . $folder->id . " AND attachment=". $ids[$i];
                    }
                    
                    RML_Util::getInstance()->query($sqls);
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Push a folder order by a given count.
     * 
     * @param $fid The folder id
     * @param $count
     */
    public function push($fid, $count = 1) {
        global $wpdb;
        
        $min = $this->getMinimal($fid);
        if (!$min || !is_numeric($count) || $count < 1) {
            return;
        }
        
        $sql_iterate = $wpdb->prepare("UPDATE " . $this->table_name . " AS o
                    SET o.nr = o.nr + " . $count . ",
                    o.oldCustomNr = o.oldCustomNr + " . $count . "
                    WHERE o.nr >= %d
                    AND o.fid = %s", $min, $fid);
        $this->debug_sqls($sql_iterate);
        RML_Util::getInstance()->query($sql_iterate);
    }
    
    /**
     * Moves an attachment before or after another attachment in the
     * order table.
     * 
     * @param currentId The attachment which should be moved
     * @param nextId The attachment next to the currentId, if it is
     *               false the currentId should be moved to the end of table.
     * @return boolean
     * 
     * <strong>Note:</strong> When nextID is false, check first, if there
     * is really no next ID. This is not done in this function.
     */
    public function update($currentId, $nextId) {
        // Not the same id
        if (!$currentId || trim($currentId) == trim($nextId)) {
            return false;
        }
        
        // Get the order number for current attachment
        $currentNr = $this->getNr($currentId);
        
        // The attachment folder may not be empty (root)
        $currentFid = wp_attachment_folder($currentId);
        if (!$currentFid || empty($currentFid)) {
            return;
        }
        
        // Executes a index if needed
        if ($currentNr === false) {
            $this->needsIndex($currentFid, true);
            $currentNr = $this->getNr($currentId);
            $this->debug_sqls("Update of folder with many images, index it... found nr " . $currentNr);
        }
        
        // Process an order update method
        if ($currentNr !== false && $nextId === false) {
            // Move to end
            $max = $this->getMaximal($currentFid);    
            
            // Check if needed
            if ($max !== $currentNr) {
                $sql = $this->prepareSqlForMove($currentId, $max, $currentNr, $max, $currentFid);
                
                // Check, if generated sql is given
                if ($sql !== false) {
                    $this->debug_sqls($sql);
                    RML_Util::getInstance()->query($sql);
                    $this->saveOldCustomNr($currentFid);
                    return true;
                }
            }
        
        }else if (($nextNr = $this->getNr($nextId)) !== false
            && $currentNr !== false) {
                
            // Move between
            $iterate_left = null; // BETWEEN left AND right
            $iterate_right = null;
            $iterate = null; // The iteration level
            
            if ($nextNr > $currentNr) {
                // Move upwards
                $iterate_left = $currentNr;
                $iterate_right = $nextNr - 1;
                $iterate = "- 1";
                $nextNr -= 1;
            }else{
                // Move downwards
                $iterate_left = $nextNr;
                $iterate_right = $currentNr;
                $iterate = "+ 1";
            }
            
            // Do it when iteration is setted
            if ($iterate !== null) {
                $sql = $this->prepareSqlForMove($currentId, $nextNr, $iterate_left, $iterate_right, $currentFid, $iterate);
                
                if ($sql !== false) {
                    $this->debug_sqls($sql);
                    RML_Util::getInstance()->query($sql);
                    $this->saveOldCustomNr($currentFid);
                    return true;
                }
            }
        }else{
            $this->debug_sqls("No method for order update found");
        }
        $this->debug_sqls("Currentid: " . $currentId);
        $this->debug_sqls("Currentnumber: " . $this->getNr($currentId));
        $this->debug_sqls("Nextid: " . $nextId);
        $this->debug_sqls("Nextnr: " . $this->getNr($nextId));
        return false;
    }
    
    /**
     * Update the old custom nr so it can easily be restored.
     * 
     * @param $fid The folder id
     * @used this::update
     */
    private function saveOldCustomNr($fid) {
        global $wpdb;
        
        $wpdb->query($wpdb->prepare("UPDATE " . $this->table_name . " SET oldCustomNr = nr WHERE fid = %d;", $fid));
    }
    
    /**
     * Update the current nr to the old custom nr so it is restored.
     * 
     * @param $fid The folder id
     * @used RML_Order_Ajax::wp_ajax_attachment_order_by_last_custom
     */
    public function restoreOldCustomNr($fid) {
        global $wpdb;
        
        $tableName = RML_Core::getInstance()->getTableName("order");
        $wpdb->query($wpdb->prepare("UPDATE " . $this->table_name . " SET nr = oldCustomNr WHERE fid = %d;", $fid));
    }
    
    /**
     * Create two SQL-Statements to update one attachment ID.
     * 
     * @return array or false if no update is needed
     */
    private function prepareSqlForMove($currentId, $nextNr, $iterate_left, $iterate_right, $fid, $iterate = "- 1") {
        global $wpdb;
        
        if ($iterate_left == $iterate_right) {
            return false;
        }
        
        $sql_iterate = $wpdb->prepare("UPDATE " . $this->table_name . " AS o
                                SET o.nr = o.nr " . $iterate . "
                                WHERE o.attachment != %d
                                AND o.nr BETWEEN %d AND %d
                                AND o.fid = %s", $currentId, $iterate_left, $iterate_right, $fid);
        
        $sql_move = $wpdb->prepare("UPDATE " . $this->table_name . " AS o
                                SET o.nr = %d
                                WHERE o.attachment = %d", $nextNr, $currentId);
        return array($sql_iterate, $sql_move);
    }
    
    /**
     * JOIN the order table and orderby the nr.
     * It is only affected when
     * $query = new WP_Query(array(
     *      'post_status' => 'inherit',
     *      'post_type' => 'attachment',
     *      'rml_folder' => 4,
     *      'orderby' => 'rml'
     * ));
     * 
     * @param $pieces array clauses
     * @param &$query WP_Query object
     * @return $pieces
     */
    public function posts_clauses($pieces, $query) {
        if (!empty($query->query_vars['parsed_rml_folder']) &&
            (empty($query->query['orderby']) ||
                (isset($query->query['orderby']) && $query->query['orderby'] == "rml")
            )
        ){
        /*
        if (!empty($query->query_vars['parsed_rml_folder']) &&
            isset($query->query['orderby']) &&
            $query->query['orderby'] == "rml") { */
            
            $folder = $query->query_vars['parsed_rml_folder']; // Folder ID
            
            if (wp_rml_is_type($folder, array(RML_TYPE_GALLERY))) {
                global $wpdb;
                
                // left join
                $pieces["join"] .= " LEFT JOIN " . $this->table_name . " AS rmlorder ON rmlorder.attachment = " . $wpdb->posts . ".ID ";
                
                // order by
                $pieces["orderby"] = "rmlorder.nr, " . $wpdb->posts.  ".post_date DESC, " . $wpdb->posts.  ".ID DESC";
            }
        }
        
        return $pieces;
    }
    
    /**
     * =====================================================================================================
     *                              PROCESS FOR IMAGE MOVING BETWEEN TWO FOLDERS
     * 
     * item_move            Before the move starts
     * item_moved_single    Single item moved
     * item_moved_finished  After the move processed
     * 
     * Then this::removeSQLs will be used for the remove process.
     * Then this::pushTo will be used for the push process.
     * =====================================================================================================
     */
    
    /**
     * Check, if a given folder needs an index. Only in galleries.
     * 
     * @hooked RML/Item/Move
     */
    public function item_move($folder, $ids, $folderObj) {
        /**
         * Check if the push process is needed.
         */
        $this->debug_sqls("Before starting the move process...");
        if ($folderObj instanceof RML_Folder) {
            $needsIndex = $this->needsIndex($folderObj->id);
            if ($needsIndex[1]["cnt"] <= 0) {
                $this->debug_sqls("Avoid push process for " . $folderObj->id);
                $this->avoidFids[] = $folderObj->id;
            }
        }
        
        /**
         * Arrays and infos for the process.
         */
        $this->pushTo = array();            // PUSH SQLS
        $this->removeSQLs = array();        // REMOVE SQLS
        $this->deleteIds = array();         // DELETE IDS
    }
    
    /**
     * Checks, if a given folder id is restricted for the push process.
     * 
     * @param $fid The folder id
     * @uses this::avoidFids
     * return boolean
     */
    public function isAvoidedForPush($fid) {
        foreach ($this->avoidFids as $key => $value) {
            if ($value === $fid) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * If we move an attachment to a gallery we will push it to
     * the order. Before we must check if we need a reindex for this folder.
     * 
     * @see this::item_move
     * @uses this::pushTo
     * @uses this::removeSQLs
     * @hooked RML/Item/Moved
     */
    public function item_moved_single($attachmentId, $fromFid, $toFid) {
        global $wpdb;
        
        /**
         * from !gallery to gallery => push to gallery
         * from gallery to !gallery => remove from gallery
         * from gallery to gallery => remove FROM gallery, push TO gallery
         */
         
        $fromAction = "";
        $toAction = "";
        
        if ($fromFid > 0 && ($from = wp_rml_get_by_id($fromFid, array(RML_TYPE_GALLERY), false)) !== null) {
            // from gallery
            $fromAction = "remove";
        }else{
            // from !gallery
            // Silence is golden.
        }
        
        if ($toFid > 0 && ($to = wp_rml_get_by_id($toFid, array(RML_TYPE_GALLERY), false)) !== null) {
            // to gallery (only if order is available, yet)
            if (!$this->isAvoidedForPush($toFid)) {
                $toAction = "push";
            }else{
                $this->debug_sqls("Folder is avoided " . $toFid);
            }
        }else{
            // to !gallery
            // Silence is golden.
        }
        
        /**
         * ACTION remove
         *  - iterate remaining attachments down
         *  - delete attachment from order table
         */
        if ($fromAction == "remove") {
            if (($fromNr = $this->getNr($attachmentId)) !== false) {
                // update
                $this->removeSQLs[] = $wpdb->prepare("UPDATE " . $this->table_name . " AS o
                                                SET o.nr = o.nr - 1,
                                                o.oldCustomNr = o.oldCustomNr - 1
                                                WHERE o.attachment != %d
                                                AND o.nr > (SELECT o2.nr FROM (SELECT * FROM " . $this->table_name . " WHERE attachment = %d LIMIT 1) AS o2)
                                                AND o.fid = %s", $attachmentId, $attachmentId, $fromFid);
            }else{
                $this->debug_sqls("No nr found for " . $attachmentId);
            }
            if ($toAction != "push") {
                //$this->removeSQLs[] = $wpdb->prepare("DELETE FROM " . $this->table_name . " AS o WHERE o.attachment = %d", $attachmentId);
                $this->deleteIds[] = $attachmentId;
            }
        }
        
        /**
         * ACTION push
         *  - Push the order
         *  - Use items_push in item_moved_finished method
         */
        if ($toAction == "push") {
            $toNr = isset($fromNr) ? $fromNr : $this->getNr($attachmentId);
            $this->pushTo[] = array("insert" => $toNr === false, "attachmentId" => $attachmentId, "toFid" => $toFid);
        }
    }
    
    /**
     * Run an index process if needed.
     * Run an DELETE process if needed.
     * Run an iterate process if needed.
     * Run an push process if needed.
     * 
     * @hooked RML/Item/MoveFinished
     */
    public function item_move_finished($folder, $ids, $folderObj) {
        $this->debug_sqls("Item move finished, item delete and iterate");
        
        // The iterate process
        if (is_array($this->removeSQLs) && count($this->removeSQLs) > 0) {
            $this->debug_sqls($this->removeSQLs);
            RML_Util::getInstance()->query($this->removeSQLs);
        }
        
        // The delete process
        if (is_array($this->deleteIds) && count($this->deleteIds) > 0) {
            $sql_delete = "DELETE FROM " . $this->table_name . " WHERE attachment IN (" . join(",", $this->deleteIds) . ")";
            $this->debug_sqls($sql_delete);
            RML_Util::getInstance()->query($sql_delete);
        }
        
        // The push process
        $this->items_push($this->pushTo);
        
        // Reset
        $this->pushTo = array();
        $this->removeSQLs = array();
        $this->deleteIds = array();
    }
    
    /**
     * =====================================================================================================
     * 
     *                              END OF PROCESS, but see also items_push
     * 
     * =====================================================================================================
     */
    
    /**
     * Wrapper for pushing multiple items to a given folder.
     * 
     * @used this::add_attachment
     * @used this::item_move_finished
     * @uses this::pushTo
     * @param $pushTo Array for the items
     *                array("insert" => $toNr === false, "attachmentId" => $attachmentId, "toFid" => $toFid);
     * 
     * <strong>Note: </strong> Please do not use different toFid's. This is because the move item action
     * is called in single actions.
     */
    private function items_push($pushTo) {
        $this->debug_sqls("Items push");
        global $wpdb;
        
        if (!is_array($pushTo)) {
            return;
        }
        
        $inserts = array(); // Insert SQLs
        $updates = array(); // Update SQLs
        $folderId = null;
        $i = 0;
        
        foreach ($pushTo as $val) {
            $i++;
            
            // array("insert" => $toNr === false, "attachmentId" => $attachmentId, "toFid" => $toFid);
            
            if ($val["insert"] == true) {
                // Is insert
                $inserts[] = "(" . $val["attachmentId"] . ", " . $val["toFid"] . ", " . $i . ", " . $i . ")";
            }else{
                // Is Update
                $updates[] = $wpdb->prepare("UPDATE " . $this->table_name . " AS o
                                SET o.nr = %d, o.fid = %d
                                WHERE o.attachment = %d", $i, $val["toFid"], $val["attachmentId"]);
            }
            
            if ($folderId === null) {
                $folderId = $val["toFid"];
            }
        }
        
        // Is there any sql needed, then push and query
        $cntToPush = count($inserts) + count($updates);
        if ($folderId !== null & $cntToPush > 0) {
            // The Real PUSH!
            $this->push($folderId, $cntToPush);
            
            // Are inserts needed
            if (count($inserts) > 0) {
                $sql_insert = "INSERT INTO " . $this->table_name . " (`attachment`, `fid`, `nr`, `oldCustomNr`) VALUES " . implode(",", $inserts);
                $this->debug_sqls($sql_insert);
                RML_Util::getInstance()->query($sql_insert);
            }
            
            // Updates
            foreach ($updates as $update) {
                $this->debug_sqls($update);
                RML_Util::getInstance()->query($update);
            }
        }
    }
    
    /**
     * Deletes a complete order for a given folder ID.
     * 
     * @param $fid The folder ID (may not be root)
     */
    public function delete_order($fid) {
        global $wpdb;
        
        if (!empty($fid) && ($f = wp_rml_get_by_id($fid)) !== null) {
            $sql = $wpdb->prepare("DELETE FROM " . $this->table_name . " WHERE fid = %d", $fid);
            $wpdb->query($sql);
            return true;
        }
        return false;
    }
    
    /**
     * Deletes the complete order. Use it with CAUTION!
     */
    public function delete_all_order() {
        global $wpdb;
        
        $sql = "DELETE FROM " . $this->table_name;
        $wpdb->query($sql);
    }
    
    /**
     * When a attachment is deleted, remove it complete from the
     * order table. Simulate a move process.
     * 
     * @param $postid The post id of the attachment
     * @hooked delete_attachment
     * @see this::reindex()
     */
    public function delete_attachment($postid) {
        if (wp_rml_is_type(wp_attachment_folder($postid), array(RML_TYPE_GALLERY))) {
            wp_rml_move("-1", array($postid));
        }
    }
    
    /**
     * When a attachment is added, add it complete to the
     * order table.
     * 
     * @param $postid The post id of the attachment
     * @hooked add_attachment
     * @see this::reindex()
     *
    public function add_attachment($postid) {
        // A add_attachment is not required, because the update_post_meta is
        // called when uploading a new image.
    }
    */
    
    /**
     * Gets the biggest sort order number of 
     * a given folder.
     * 
     * @param $fid Folder id
     * @return int
     */
    public function getMaximal($fid, $function = "MAX") {
        global $wpdb;
        
        $max = $wpdb->get_var($wpdb->prepare("SELECT " . $function . "(o.nr) FROM " . $this->table_name . " AS o WHERE o.fid = %s", $fid));
        
        if (!($max > 0)) {
            return false;
        }else{
            return $max;
        }
    }
    
    public function getMinimal($fid) {
        return $this->getMaximal($fid, "MIN");
    }
    
    /**
     * Get the order number for a specific attachment.
     * 
     * @param $attachmentId The attachment id
     * @return Int or false
     * 
     * @api wp_attachment_order_number()
     */
    public function getNr($attachmentId) {
        global $wpdb;
        
        $nextNr = $wpdb->get_var($wpdb->prepare("SELECT o.nr FROM " . $this->table_name . " AS o WHERE o.attachment = %d", $attachmentId));

        if (!($nextNr > 0)) {
            return false;
        }else{
            return $nextNr;
        }
    }
    
    /**
     * Get the next attachment id for a specific attachment.
     * 
     * @param $attachmentId The attachment id
     * @param $folderId The attachments' folder
     * @return Int or false
     */
    public function getAttachmentNextTo($attachmentId, $folderId = null) {
        global $wpdb;
        
        // Load the attachments' folder id
        if ($folderId === null) {
            $folderId = wp_attachment_folder($attachmentId);
            if (!($folderId > 0)) {
                return false;
            }
        }
        
        $sql = $wpdb->prepare("SELECT o.attachment
                        FROM (SELECT *
                            FROM " . $this->table_name . "
                            WHERE fid=%d ORDER BY nr) AS o
                        WHERE o.nr > (SELECT o2.nr FROM (SELECT nr FROM " . $this->table_name . " WHERE attachment=%d) AS o2)
                        LIMIT 1;", $folderId, $attachmentId);
        $nextNr = $wpdb->get_var($sql);
        if (!($nextNr > 0)) {
            return false;
        }else{
            return $nextNr;
        }
    }
    
    /**
     * Add a attribute to the ajax output. The attribute represents
     * the folder order number if it is a gallery.
     * 
     * @used order.js
     * @hooked wp_prepare_attachment_for_js
     * @movedpermanently RML_Filter::wp_prepare_attachment_for_js()
     */
    /* public function wp_prepare_attachment_for_js($response, $attachment, $meta) {
        
    }*/
    
    /**
     * Get the whole order table for a given foler id. It uses a cache
     * to not query always the same database sql.
     * 
     * @param fid the folder id
     * @param fromCache load the data from the cache
     * @param indexMode the return is an indexed array with attachement id key
     * @return array or false
     * @used for example in order.js
     */
    public $orderNumbers = array(); // Can be cleared!
    public function getOrderNumbers($fid, $fromCache = true, $indexMode = true) {
        global $wpdb;
        
        if (is_numeric($fid) && $fid > 0) {
            if ($fromCache && isset($this->orderNumbers[$fid])) {
                $results = $this->orderNumbers[$fid];
            }else{
                $results = $wpdb->get_results($wpdb->prepare("SELECT o.attachment, o.nr  FROM " . $this->table_name . " AS o WHERE o.fid = %d", $fid), ARRAY_A );
                $this->orderNumbers[$fid] = $results;
                
                if (count($results) == 0) {
                    return false;
                }
            }
        }else{
            return false;
        }
        
        if ($indexMode && count($results) > 0) {
            $_result = array();
            foreach ($results as $key => $value) {
                $_result[((int)$value["attachment"])] = (int) $value["nr"];
            }
            $results = $_result;
        }
        
        return $results;
    }
    
    private function debug_sqls($sqls) {
        if ($this->debug_sqls === true) {
            if (is_array($sqls)) {
                foreach ($sqls as $key => $value) {
                    error_log("[$key] $value");
                }
            }else{
                error_log($sqls);
            }
        }
    }
    
    /**
     * Get the old custom nr count so we can decide if already available.
     * 
     * @param $fid The folder id
     * @return int count
     * @used this::update
     */
    public function getOldCustomNrCount($fid) {
        global $wpdb;
        
        $result = $wpdb->get_col($wpdb->prepare("SELECT COUNT(oldCustomNr) FROM " . $this->table_name . " WHERE fid = %d", $fid));
        return $result[0];
    }
    
    /**
     * Media Library Assistent extension.
     */
    public function mla_media_modal_query_final_terms($query) {
        $folderId = RML_Filter::getInstance()->getFolder(null, true);
        if ($folderId !== null && wp_rml_is_type($folderId, array(RML_TYPE_GALLERY))) {
            $query['orderby'] = 'rml';
            $query['order'] = 'ASC';
        }
        return $query;
    }
    
    public static function getInstance() {
        if (self::$me == null) {
                self::$me = new RML_Order();
        }
        return self::$me;
    }
}

?>
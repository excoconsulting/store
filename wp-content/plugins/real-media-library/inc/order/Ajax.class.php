<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class RML_Order_Ajax {
    
    private static $me = null;

    private function __construct() {
        
    }
    
    /**
     * Create nonces for the order ajax requests.
     * 
     * @hooked RML/Backend/Nonces
     */
    public function nonces($nonces) {
        $nonces["attachmentOrder"] = wp_create_nonce("rmlAjaxAttachmentOrder");
        $nonces["attachmentOrderResetAll"] = wp_create_nonce("rmlAjaxAttachmentOrderResetAll");
        $nonces["attachmentOrderReset"] = wp_create_nonce("rmlAjaxAttachmentOrderReset");
        $nonces["attachmentOrderReindex"] = wp_create_nonce("rmlAjaxAttachmentOrderReindex");
        $nonces["attachmentOrderBy"] = wp_create_nonce("rmlAjaxAttachmentOrderBy");
        $nonces["attachmentOrderByLastCustom"] = wp_create_nonce("rmlAjaxAttachmentOrderByLastCustom");
        return $nonces;
    }
    
    /**
     * Set an order by a given string
     * 
     * @REQUEST method the id of the folder (must be gallery)
     * @see RML_Order
     */
    public function wp_ajax_attachment_order_by() {
        // Security checks
        RML_Util::getInstance()->checkNonce('rmlAjaxAttachmentOrderBy');
        
        // Process
        $fid = isset($_REQUEST["method"]) ? $_REQUEST["method"] : 0;
        $orderby = isset($_REQUEST["orderby"]) ? $_REQUEST["orderby"] : null;
        
        if ($fid > 0 && !empty($orderby)) {
            if (RML_MetaGalleryOrder::order($fid, $orderby)) {
                wp_send_json_success();
            }else{
                wp_send_json_error();
            }
        }else{
            wp_send_json_error();
        }
    }
    
    /**
     * Reset a given gallery to the last custom order.
     * 
     * @REQUEST method the id of the folder (must be gallery)
     * @see RML_Order
     */
    public function wp_ajax_attachment_order_by_last_custom() {
        // Security checks
        RML_Util::getInstance()->checkNonce('rmlAjaxAttachmentOrderByLastCustom');
        
        // Process
        $fid = isset($_REQUEST["method"]) ? $_REQUEST["method"] : 0;
        if ($fid > 0 && RML_Order::getInstance()->getOldCustomNrCount($fid) > 0) {
            RML_Order::getInstance()->restoreOldCustomNr($fid);
            wp_send_json_success();
        }else{
            wp_send_json_error();
        }
    }
    
    /**
     * Reset an order for a given folder id.
     * 
     * @REQUEST method the id of the folder (must be gallery)
     * @see RML_Order
     */
    public function wp_ajax_attachment_order_reset() {
        // Security checks
        RML_Util::getInstance()->checkNonce('rmlAjaxAttachmentOrderReset');
        
        // Process
        $fid = isset($_REQUEST["method"]) ? $_REQUEST["method"] : 0;
        
        if ($fid > 0 && RML_Order::getInstance()->delete_order($fid)) {
            wp_send_json_success();
        }else{
            wp_send_json_error();
        }
    }
    
    /**
     * Reindex an order for a given folder id.
     * 
     * @REQUEST method the id of the folder (must be gallery)
     * @see RML_Order
     */
    public function wp_ajax_attachment_order_reindex() {
        // Security checks
        RML_Util::getInstance()->checkNonce('rmlAjaxAttachmentOrderReindex');
        
        // Process
        $fid = isset($_REQUEST["method"]) ? $_REQUEST["method"] : 0;
        
        if ($fid > 0 && RML_Order::getInstance()->reindex($fid)) {
            wp_send_json_success();
        }else{
            wp_send_json_error();
        }
    }
    
    /**
     * Order a gallery.
     * 
     * @POST attachmentId The attachment id which should be moved
     * @POST nextId The next attachment id to attachmentId or false for the end
     * @POST lastId The last attachment id of the view in frontend
     * @see RML_Order
     */
    public function wp_ajax_attachment_order() {
        // Security checks
        RML_Util::getInstance()->checkNonce('rmlAjaxAttachmentOrder');
        
        // Process
        $attachmentId = isset($_POST["attachmentId"]) ? $_POST["attachmentId"] : 0;
        $nextId = isset($_POST["nextId"]) ? $_POST["nextId"] : false;
        $lastId = isset($_POST["lastId"]) ? $_POST["lastId"] : false;
        
        if ($attachmentId > 0) {
            if ($nextId === "false") {
                $nextId = false;
            }
            
            // Is it the real end?
            //getAttachmentNextTo($attachmentId, $folderId);
            if ($nextId === false && $lastId !== false) {
                $nextIdTo = RML_Order::getInstance()->getAttachmentNextTo($lastId);
                if ($nextIdTo > 0) {
                    $nextId = $nextIdTo;
                }
            }
            
            wp_attachment_order_update($attachmentId, $nextId);
        }
    }
    
    /**
     * Reset all orders of all galleries.
     */
    public function wp_ajax_attachment_order_reset_all() {
        // Security checks
        RML_Util::getInstance()->checkNonce('rmlAjaxAttachmentOrderResetAll');
        
        // Process
        RML_Order::getInstance()->delete_all_order();
        
        wp_send_json_success();
    }
    
    public static function getInstance() {
        if (self::$me == null) {
            self::$me = new RML_Order_Ajax();
        }
        return self::$me;
    }
}
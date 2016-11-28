<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Implements a order by field.
 * 
 * @see interface iRML_Meta for more details
 */
class RML_MetaGalleryOrder implements iRML_Meta {
    
    static $cachedOrders = null;
    
    /**
     * Start to order the given folder by a given order type.
     * 
     * @param $fid The folder id
     * @param $orderby The ordertype key
     * @return boolean
     * @see this::getAvailableOrders
     */
    public static function order($fid, $orderby) {
        $orders = self::getAvailableOrders();
        if (in_array($orderby, array_keys($orders))) {
            global $wpdb;
            
            // Get order
            $split = explode("_", $orderby);
            $order = $orders[$orderby];
            $direction = $split[1];
            $orderTableName = RML_Core::getInstance()->getTableName("order");
            
            // Check if the folder needs an index
            RML_Order::getInstance()->needsIndex($fid, true);
            
            // Run SQL
            $sql = $wpdb->prepare("UPDATE $orderTableName AS rmlo2
                LEFT JOIN (
                	SELECT @rownum := @rownum + 1 AS nr, t.ID
                	FROM ( SELECT wp.ID
                		FROM $orderTableName AS rmlo
                		INNER JOIN $wpdb->posts AS wp ON rmlo.attachment = wp.id AND wp.post_type = \"attachment\"
                		WHERE rmlo.fid = %d
                		ORDER BY " . $order["sqlOrder"] . " $direction ) AS t, (SELECT @rownum := 0) AS r
                ) AS rmlonew ON rmlo2.attachment = rmlonew.ID
                SET rmlo2.nr = rmlonew.nr
                WHERE rmlo2.fid = %d", $fid, $fid);
            $wpdb->query($sql);
            
            // Save in the metadata
            update_media_folder_meta($fid, "orderby", $orderby);
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * Get all available order by methods.
     * 
     * @return Localized array
     */
    public static function getAvailableOrders() {
        if (self::$cachedOrders === null) {
            $orders = array(
                "title_asc" => array(
                    "label" => __("Order by title ascending", RML_TD),
                    "sqlOrder" => "wp.post_title"
                ),
                "title_desc" => array(
                    "label" => __("Order by title descending", RML_TD),
                    "sqlOrder" => "wp.post_title"
                ),
                "filename_asc" => array(
                    "label" => __("Order by filename ascending", RML_TD),
                    "sqlOrder" => "SUBSTRING_INDEX(wp.guid, '/', -1)"
                ),
                "filename_desc" => array(
                    "label" => __("Order by filename descending", RML_TD),
                    "sqlOrder" => "SUBSTRING_INDEX(wp.guid, '/', -1)"
                ),
                "id_asc" => array(
                    "label" => __("Order by ID ascending", RML_TD),
                    "sqlOrder" => "wp.ID"
                ),
                "id_desc" => array(
                    "label" => __("Order by ID descending", RML_TD),
                    "sqlOrder" => "wp.ID"
                )
            );
            self::$cachedOrders = apply_filters("RML/Order/Orderby", $orders);
        }
        return self::$cachedOrders;
    }
    
    /**
     * The input field.
     *
     * @see interface iRML_Meta
     */
    public function content($content, $folder) {
        if ($folder instanceof RML_Folder && $folder->is(RML_TYPE_GALLERY)) {
            $content .= '<tr>
                <th scope="row">' . __('Order') . '</th>
                <td>
                    <select>';
            
            foreach (self::getAvailableOrders() as $key => $value) {
                $content .= '<option value="' . $key . '">' . $value["label"] . '</option>';
            }
            
            $content .= '
                    </select>
                    <a class="button actionbutton" id="rml-meta-action-order-by" data-nonce-key="attachmentOrderBy" 
                        data-action="rml_attachment_order_by" 
                        data-method="' . $folder->id . '" href="#">' . __('Apply', RML_TD) . '</a>
                    <br />
                    <p>' . __('After you applied a new order please reload the view of attachments to see the result. Note: The order is applied once you click "Apply".', RML_TD) . '</p>
                </td>
            </tr>';
        }
        
        return $content;
    }
    
    /**
     * Save the general infos: Name
     * 
     * @see interface iRML_Meta
     */
    public function save($response, $folder) {
        // Silence is golden.
        return $response;
    }
    
    /**
     * The general scripts and styles.
     *
     * @see interface iRML_Meta
     */
    public function scripts() {
        // Silence is golden.
    }
}
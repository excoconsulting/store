<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Implements a description field.
 * 
 * @see inc/api/meta.php
 * @see interface iRML_Meta for more details
 */
class RML_MetaDescription implements iRML_Meta {
    
    public function getDescription($folder_id) {
        return get_media_folder_meta($folder_id, "description", true);
    }
    
    /**
     * The input field.
     *
     * @see interface iRML_Meta
     */
    public function content($content, $folder) {
        $description = $this->getDescription($folder === null ? -1 : $folder->id);
        $content .= '<tr>
            <th scope="row">' . __('Description') . '</th>
            <td>
                <textarea name="description" type="text" class="regular-text" style="width: 100%;box-sizing: border-box;">' . $description . '</textarea>
            </td>
        </tr>
        <tr class="rml-meta-margin"></tr>';
        
        return $content;
    }
    
    /**
     * Save the general infos: Name
     * 
     * @see interface iRML_Meta
     */
    public function save($response, $folder) {
        $toSaveFID = $folder === null ? -1 : $folder->id;
        $description = $this->getDescription($toSaveFID);
        
        if (isset($_POST["description"])) {
            $newDesc = $_POST["description"];
            if ($newDesc != $description) {
                if (strlen($newDesc) > 0) {
                    update_media_folder_meta($toSaveFID, "description", $newDesc);
                }else{
                    // Delete it
                    delete_media_folder_meta($toSaveFID, "description");
                }
            }
        }
        
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
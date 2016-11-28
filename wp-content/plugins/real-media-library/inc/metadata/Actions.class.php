<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Implements action buttons to the meta box. The <a>-button
 * must have an id so it can be registered to the hook
 * folderMeta/action/{ID}. The ID should start with prefix
 * rml-meta-action-{ID}.
 * 
 * @see interface iRML_Meta
 * @see meta.js for more details and javascript hooks
 * @filter RML/Folder/Meta/ActionButtons (Parameter: array of buttons)
 */
class RML_MetaActions implements iRML_Meta {
    public function scripts() {
        // @see meta.js for the hook folderMeta/action/{ID}
    }
    
    /**
     * The general custom fields. This creates the field for
     * a cover image.
     */
    public function content($content, $folder) {
        $buttons = apply_filters("RML/Folder/Meta/ActionButtons", array(), $folder);
        
        if (count($buttons) > 0) {
            $content .= '<tr>
                <th scope="row">' . __('Actions', RML_TD) . '</th>
                <td><fieldset>' . implode(" ", $buttons) . '</fieldset></td>
            </tr>';
        }
        
        return $content;
    }
    
    /**
     * Nothing to save.
     */
    public function save($response, $folder) {
        return $response;
    }
}
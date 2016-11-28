<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Singleton Class.
 * 
 * Add tab to the media options. The custom list needs further
 * changes with buttons "Reset" and "Reindex".
 */
class RML_Order_Options {
    
    private static $me = null;

    private function __construct() {
        
    }
    
    /**
     * Hook into the options panel of Real Media Library.
     * 
     * @hooked RML/Order/Register
     */
    public function register() {
        add_settings_section(
        	'rml_options_order',
        	__('RealMediaLibrary:Gallery order'),
        	array(RML_Options::getInstance(), 'empty_callback'),
        	'media'
        );
        
        add_settings_field(
            'rml_button_wipe',
            '<label for="rml_button_wipe">'.__('Reset the order of all galleries' , RML_TD ).'</label>' ,
            array($this, 'html_rml_button_wipe'),
            'media',
            'rml_options_order'
        );
    }
    
    public function html_rml_button_wipe() {
        echo '<button class="rml-button-wipe button button-primary"
                    data-nonce-key="attachmentOrderResetAll"
                    data-action="rml_attachment_order_reset_all"
                    data-method="">' . __('Reset') . '</button>';
        
        $library = wp_rml_root_childs();
        echo wp_rml_select_tree("options-order", null, $library);
    }
    
    /**
     * Add buttons to the custom list for this option.
     * 
     * @hooked RML/Folder/TreeNode/Content
     */
    public function treeNode_content($html, $args) {
        if (isset($args[7]) && $args[7] == "options-order"
                && ($fid = $args[0]) > 0
                && ($max = RML_Order::getInstance()->getMaximal($fid)) !== false) {
            $html .= '
            <button class="button button-primary rml-button-wipe rml-order-reset"
                data-nonce-key="attachmentOrderReset" 
                data-action="rml_attachment_order_reset" 
                data-method="' . $fid . '">' . __('Reset order', RML_TD) . '</button>
                
            <button class="button rml-button-wipe rml-order-reset rml-order-reindex"
                data-nonce-key="attachmentOrderReindex" 
                data-action="rml_attachment_order_reindex" 
                data-method="' . $fid . '">' . __('Reindex order', RML_TD) . '</button>';
        }
        
        return $html;
    }

    /**
     * Create the buttons for the order in folder meta box.
     * 
     * @hoooked RML/Folder/Meta/ActionButtons
     */
    public function meta_actionbuttons($buttons, $folder) {
        if ($folder !== null && ($max = RML_Order::getInstance()->getMaximal($folder->id)) !== false) {
            $buttons[] = '<a class="button actionbutton" id="rml-meta-action-order-reset" data-nonce-key="attachmentOrderReset" 
                                data-action="rml_attachment_order_reset" 
                                data-method="' . $folder->id . '" href="#">' . __('Reset order', RML_TD) . '</a>';
            
            if (RML_Order::getInstance()->getOldCustomNrCount($folder->id) > 0) {
                $buttons[] = '<a class="button actionbutton" id="rml-meta-action-order-by-last-custom" data-nonce-key="attachmentOrderByLastCustom" 
                                    data-action="rml_attachment_order_by_last_custom" 
                                    data-method="' . $folder->id . '" href="#">' . __('Reset to last custom order', RML_TD) . '</a>';
            }
            //$buttons[] = '<a class="button actionbutton" id="rml-meta-action-order-reindex" data-nonce-key="attachmentOrderReindex" 
            //                    data-action="rml_attachment_order_reindex" 
            //                    data-method="' . $folder->id . '" href="#">' . __('Reindex order', RML_TD) . '</a>';
        }
        return $buttons;
    }
    
    public static function getInstance() {
        if (self::$me == null) {
            self::$me = new RML_Order_Options();
        }
        return self::$me;
    }
    
}
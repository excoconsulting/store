<?php
/**
 * Allowed restrictions for folders:
 * 
 * - rea          Restrict to rearrange the hierarchical levels of the subfolders (it is downwards
 *                all subfolders!) and can not be inherited
 * 
 * - cre          Restrict to create new subfolders
 * - ins          Restrict to insert/upload new attachments, automatically moved to root if upload
 * - ren          Restrict to rename the folder
 * - del          Restrict to delete the folder
 * - mov          Restrict to move files outside the folder
 * 
 * You can append a ">" after each permission so it is inherited in each created subfolder: "cre>", "ins>", ...
 * 
 * @see RML_Folder::$restrictions
 * @see RML_Folder::isRestrictFor
 * @see RML_Structure::createFolder
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class RML_Permissions {
    
    private static $me = null;
    
    /**
     * Restrict to insert/upload new attachments, automatically moved to root if upload
     * Restrict to move files outside of a folder
     * 
     * @filter RML/Validate/Insert
     * @see RML_Folder::insert
     */
    public static function insert($errors, $id, $folder) {
        if ($folder instanceof RML_Folder && $folder->isRestrictFor("ins")) {
            $errors[] = __("You are not allowed to insert files here.", RML_TD);
            return $errors;
        }
        
        // Check if "mov" of current folder is allowed
        $otherFolder = wp_attachment_folder($id);
        if ($otherFolder !== "") {
            $otherFolder = wp_rml_get_by_id($otherFolder, null, true);
            if ($otherFolder instanceof RML_Folder && $otherFolder->isRestrictFor("mov")) {
                $errors[] = __("You are not allowed to move the file.", RML_TD);
            }
        }
        
        return $errors;
    }
    
    /**
     * Restrict to create new subfolders
     * 
     * @filter RML/Validate/Create
     * @see RML_Structure::createFolder
     */
    public static function create($errors, $name, $parent, $type) {
        $folder = wp_rml_get_by_id($parent, null, true);
        if ($folder instanceof RML_Folder && $folder->isRestrictFor("cre")) {
            $errors[] = __("You are not allowed to create a subfolder here.", RML_TD);
        }
        return $errors;
    }
    
    /**
     * Restrict to create new subfolders
     * 
     * @filter RML/Validate/Delete
     * @see RML_Structure::deleteFolder
     */
    public static function deleteFolder($errors, $id, $folder) {
        if ($folder instanceof RML_Folder && $folder->isRestrictFor("del")) {
            $errors[] = __("You are not allowed to delete this folder.", RML_TD);
        }
        return $errors;
    }
    
    /**
     * Restrict to rename a folder
     * 
     * @filter RML/Validate/Rename
     * @see RML_Folder::setName
     */
    public static function setName($errors, $name, $folder) {
        if ($folder instanceof RML_Folder && $folder->isRestrictFor("ren")) {
            $errors[] = __("You are not allowed to rename this folder.", RML_TD);
        }
        return $errors;
    }
    
    /**
     * Add mandatory classes to the <li> object to apply child permissions.
     * 
     * @filter RML/Folder/TreeNodeLi/Class
     */
    public function liClass($classes, $folder) {
        /**
         * Restrict hierarchical change.
         * 
         * @see $liClasses
         */
        if ($folder->isRestrictFor("rea")) {
            $classes[] = "aio-restrict-hierarchical-change";
        }
        
        if ($folder->restrictionsCount > 0 && !($folder->restrictionsCount === 1 && $folder->isRestrictFor("rea"))) {
            $classes[] = "aio-restrict";
        }
        return $classes;
    }

    public static function getInstance() {
        if (self::$me == null) {
            self::$me = new RML_Permissions();
        }
        return self::$me;
    }
}

?>
<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Meta tags for folders.
 * 
 * @table wp_realmedialibrary_meta
 * $wpdb->realmedialibrarymeta
 * 
 * (C) add_media_folder_meta(...)
 * (R) get_media_folder_meta(...)
 * (U) update_media_folder_meta(...)
 * (D) delete_folder_meta(...)
 * 
 * delete_media_folder_meta_by_key(...): Delete everything from folder meta matching meta key.
 * 
 * Here you can use the default meta data hooks like:
 *  add_{$meta_type}_meta
 *  => add_realmedialibrary_meta
 * 
 * @see RML_Meta
 *          RML_Meta::content_general
 *          RML_Meta::save_general
 * @see assets/js/meta.js
 * 
 * PREDEFINED META KEYS:
 *  description
 *  coverImage
 */

if (!function_exists('get_media_folder_meta')) {
    /**
     * Retrieve folder meta field for a folder.
     *
     * @param int    $folder_id Folder ID.
     * @param string $key     Optional. The meta key to retrieve. By default, returns
     *                        data for all keys. Default empty.
     * @param bool   $single  Optional. Whether to return a single value. Default false.
     * @return mixed Will be an array if $single is false. Will be value of meta data
     *               field if $single is true.
     */
    function get_media_folder_meta( $folder_id, $key = '', $single = false ) {
        RML_Meta::getInstance(); // Necessery checks to prepare metas
        $folder_id = $folder_id == "-1" ? 100000000000 : $folder_id; // FIX absint() in wordpress
        return get_metadata('realmedialibrary', $folder_id, $key, $single);
    }
    
    //var_dump(get_media_folder_meta(108, "test", true));
}

if (!function_exists('add_media_folder_meta')) {
    /**
     * Add meta data field to a folder.
     *
     * Folder meta data is called "Custom Fields" on the Administration Screen.
     *
     * @param int    $folder_id  Folder ID.
     * @param string $meta_key   Metadata name.
     * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
     * @param bool   $unique     Optional. Whether the same key should not be added.
     *                           Default false.
     * @return int|false Meta ID on success, false on failure.
     */
    function add_media_folder_meta( $folder_id, $meta_key, $meta_value, $unique = false ) {
        RML_Meta::getInstance(); // Necessery checks to prepare metas
        $folder_id = $folder_id == "-1" ? 100000000000 : $folder_id; // FIX absint() in wordpress
        return add_metadata('realmedialibrary', $folder_id, $meta_key, $meta_value, $unique);
    }
}

if (!function_exists('update_media_folder_meta')) {
    /**
     * Update folder meta field based on folder ID.
     *
     * Use the $prev_value parameter to differentiate between meta fields with the
     * same key and folder ID.
     *
     * If the meta field for the folder does not exist, it will be added.
     *
     * @param int    $folder_id  Folder ID.
     * @param string $meta_key   Metadata key.
     * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
     * @param mixed  $prev_value Optional. Previous value to check before removing.
     *                           Default empty.
     * @return int|bool Meta ID if the key didn't exist, true on successful update,
     *                  false on failure.
     */
    function update_media_folder_meta( $folder_id, $meta_key, $meta_value, $prev_value = '' ) {
        RML_Meta::getInstance(); // Necessery checks to prepare metas
        $folder_id = $folder_id == "-1" ? 100000000000 : $folder_id; // FIX absint() in wordpress
        return update_metadata('realmedialibrary', $folder_id, $meta_key, $meta_value, $prev_value);
    }
    
    //var_dump(update_media_folder_meta(108, "test", "1234"));
}

if (!function_exists('delete_media_folder_meta')) {
    /**
     * Remove metadata matching criteria from a folder.
     *
     * You can match based on the key, or key and value. Removing based on key and
     * value, will keep from removing duplicate metadata with the same key. It also
     * allows removing all metadata matching key, if needed.
     *
     * @param int    $folder_id  Folder ID.
     * @param string $meta_key   Metadata name.
     * @param mixed  $meta_value Optional. Metadata value. Must be serializable if
     *                           non-scalar. Default empty.
     * @return bool True on success, false on failure.
     */
    function delete_media_folder_meta( $folder_id, $meta_key, $meta_value = '' ) {
        RML_Meta::getInstance(); // Necessery checks to prepare metas
        $folder_id = $folder_id == "-1" ? 100000000000 : $folder_id; // FIX absint() in wordpress
        return delete_metadata('realmedialibrary', $folder_id, $meta_key, $meta_value);
    }
    //var_dump(delete_media_folder_meta(108, "test"));
}

if (!function_exists('delete_media_folder_meta_by_key')) {
    /**
     * Delete everything from folder meta matching meta key.
     * 
     * @param string $folder_meta_key Key to search for when deleting.
     * @return bool Whether the post meta key was deleted from the database.
     */
    function delete_media_folder_meta_by_key( $folder_meta_key ) {
        RML_Meta::getInstance(); // Necessery checks to prepare metas
        $folder_id = $folder_id == "-1" ? 100000000000 : $folder_id; // FIX absint() in wordpress
        return delete_metadata( 'realmedialibrary', null, $folder_meta_key, '', true );
    }
}

if (!function_exists('truncate_media_folder_meta')) {
    /**
     * Remove all metas of a folder. Use this with caution!!
     * 
     * @param $folder_id Folder ID
     * @return result of $wpdb->query
     */
    function truncate_media_folder_meta($folder_id) {
        RML_Meta::getInstance(); // Necessery checks to prepare metas
        global $wpdb;
        
        $folder_id = $folder_id == "-1" ? 100000000000 : $folder_id; // FIX absint() in wordpress
        $sql = $wpdb->prepare("DELETE FROM " . $wpdb->realmedialibrarymeta . " WHERE realmedialibrary_id=%d", $folder_id);
        return $wpdb->query($sql);
    }
    
    //var_dump(truncate_media_folder_meta(108));
}

if (!function_exists('add_rml_meta_box')) {
    /**
     * Remove all metas of a folder. Use this with caution!!
     * 
     * @param $name unique name for this meta box
     * @param $obj The object which implements iRML_Meta
     * @param $hasScripts boolean Load the resources if exists
     * @param $priority Priority for actions and filters
     * @see interface iRML_Meta
     * 
     * @called Call this function in the "init" action
     * @see action "init" of wordpress
     * @return boolean
     */
    function add_rml_meta_box($name, $obj, $hasScripts = false, $priority = 10) {
        if (!RML_Meta::getInstance()->add($name, $obj)) {
            return false;
        }
        
        add_filter('RML/Folder/Meta/Content', array($obj, 'content'), $priority, 2);
        add_filter('RML/Folder/Meta/Save', array($obj, 'save'), $priority, 2);
        
        if ($hasScripts) {
            add_action("RML/Backend/Scripts/MediaLibrary", array($obj, "scripts"), $priority);
        }
        return true;
    }
}
<?php
/**
 * This class handles all hooks for the general filters.
 * 
 * @author MatthiasWeb
 * @since 1.0
 * @singleton
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class RML_Filter {
    private static $me = null;
    
    private function __construct() {
    	add_filter('RML/Backend/JS_Localize', array($this, 'localize'));
    }
    
    /**
     * Returns the folder id of an given attachment
     * 
     * @param $attachmentId The attachment ID
     * @return Folder ID
     */
    public function getAttachmentFolder($attachmentId) {
        $fid = get_post_meta($attachmentId, "_rml_folder", true);
        if ($fid === false) {
        	return "";
        }else{
        	return $fid;
        }
    }
    
    /**
     * Localize my filter variables for javascripts
     */
    public function localize($arr) {
    	$namesSlugArray = RML_Structure::getInstance()->getView()->namesSlugArray();
        $mode = get_user_option( 'media_library_mode', get_current_user_id() ) ? get_user_option( 'media_library_mode', get_current_user_id() ) : 'grid';
    	
    	// General for filters
    	$arr["ajaxUrl"] = admin_url('admin-ajax.php');
    	$arr["namesSlug"] = $namesSlugArray;
    	$arr["blogId"] = get_current_blog_id();
    	$arr["listMode"] = $mode;
    	
    	return $arr;
    }
    
    /**
     * Show a "Folder gallery" to the TinyMCE
     * 
     * @hooked add_media_button
     * @author MatthiasWeb
     * @since 1.0
     * @deprecated
     */
    public function add_media_button( $button ) {
		global $wp_version;
		$output = '';
		
    	$img 	= '<i class="fa fa-folder-open-o"></i>&nbsp;';
    	$output = '<a href="#" onclick="alert(\'Since version 2.2.3: Folder gallery button is now available in the visual editor!\');return false;" class="button"
                        title="' . __( 'Folder gallery', RML_TD) . '"
                        style="padding-left: .4em;">' . $img . ' ' . __( 'Folder gallery', RML_TD) . '</a>';

		return $button . $output;

	}

    /**
     * Define a new query option for WP_Query.
     * "rml_folder" integer
     * 
     * @hooked pre_get_posts
     * @author MatthiasWeb
     * @since 1.0
     */
    public function pre_get_posts($query) {
        $folder = $this->getFolder($query, RML_Backend::getInstance()->isScreenBase("upload"));
    	
    	if($folder !== null){
    		$mq = $query->get('meta_query');
    		$appendix = null;
			if (!is_array($mq)) {
				$mq = array();
			}
			
    		if ($folder > 0) {
    			$appendix = array(
		            'key' => '_rml_folder',
    	            'value' => $folder,
    	            'compare' => '='
    	        );
    	        
    		}else if ($folder == "-1"){
    			$appendix = array(
    				'relation' => 'OR',
    				array(
						'key' => '_rml_folder',
    		            'value' => '-1',
    		            'compare' => '='
    	        	),
    	        	array(
    					'key' => '_rml_folder',
    		            'value' => '',
    		            'compare' => 'NOT EXISTS'
    	        	)
	        	);
    		}
    		
    		if ($appendix !== null) {
    			$mq[] = apply_filters("RML/Folder/PreGetPostsMeta", $appendix, $query, $folder);
    			$query->set('meta_query', $mq);
    			$query->set('parsed_rml_folder', $folder);
    			do_action("RML/Folder/PreGetPosts", $query, $folder);
    		}
    	}
    }
    
    /**
     * Get folder from different sources.
     * 
     * @return folder id or null
     */
    public function getFolder($query, $fromRequest = false) {
    	$folder = null;
    	
    	if ($query !== null && 
    		($queryFolder = $query->get('rml_folder')) &&
    		isset($queryFolder)) {
    			
	        // Query rml folder from query itself
    		$folder = $queryFolder;
    	}else if(current_user_can("upload_files")) {
    		if ($fromRequest) {
	    		if (isset($_REQUEST["rml_folder"])) {
	    	        // Query rml folder from list mode
	        		$folder = $_REQUEST["rml_folder"];
	        	}else if (isset($_POST["query"]["rml_folder"])) {
	    	        // Query rml folder from grid mode
	    	        $folder = $_POST["query"]["rml_folder"];
	        	}else{
	        		return;
	        	}
    		}
        }else{
    		return null;
    	}
    	return is_numeric($folder) ? $folder : null;
    }
    
    public function ajax_query_attachments_args($query) {
    	$fid = $this->getFolder(null, true);
    	if ($fid !== null) {
    		$query["rml_folder"] = $fid;
    	}
    	return $query;
    }
    
    /**
     * Handles the upload to move an attachment directly to a given folder
     */
    public function add_attachment($postID) {
    	$rmlFolder = isset($_REQUEST["rmlFolder"]) ? $_REQUEST["rmlFolder"] : null;
    	if ($rmlFolder !== null) {
    		$r = wp_rml_move($rmlFolder, array($postID));
    	}
    }
    
    /**
     * Add the attachment ID to the count update
     * 
     * @see RML_Structure::wp_die
     * @see RML_Structure::$newAttachments
     */
    public function delete_attachment($postID) {
        RML_Structure::getInstance()->addNewAttachment($postID);
    }
    
    /**
     * Add a attribute to the ajax output. The attribute represents
     * the folder order number if it is a gallery.
     * 
     * @used order.js
     * @hooked wp_prepare_attachment_for_js
     * @movedpermanently RML_Filter::wp_prepare_attachment_for_js()
     */
    public function wp_prepare_attachment_for_js($response, $attachment, $meta) {
    	$f = get_post_meta($attachment->ID, "_rml_folder", true);
    
		// append attribute
		$response['rmlFolderId'] = isset($f) ? $f : -1;
		
		if (isset($_POST["query"]) &&
				is_array($_POST["query"]) &&
				isset($_POST["query"]["orderby"]) &&
				$_POST["query"]["orderby"] == "rml") {
			$orders = RML_Order::getInstance()->getOrderNumbers($f);
			if (is_array($orders) && isset($orders[$attachment->ID])) {
				$response['rmlGalleryOrder'] = $orders[$attachment->ID];
			}
		}
		
		// return
		return $response;
	}
	
    /**
     * Create a select option in list table of attachments
     * 
     * @hooked restrict_manage_posts
     * @author MatthiasWeb
     * @since 1.0
     */
    public function restrict_manage_posts() {
        $screen = get_current_screen();
    	if ($screen->id == "upload") {
    		echo '<select name="rml_folder" id="filter-by-rml-folder" class="attachment-filters attachment-filters-rml">
    			' . RML_Structure::getInstance()->optionsFasade(
    						isset($_REQUEST['rml_folder']) ? $_REQUEST['rml_folder'] : "",
    						array()
						) . '
    		</select>&nbsp;';
    	}
    }
    
    /**
     * When we move a file this action will reaction RML/Item/Moved
     * 
     * @hooked add_{$meta_type}_meta $meta_type = post
     * @hooked update_postmeta
     */
    public function add_post_meta($object_id, $meta_key, $_meta_value) {
    	if ($meta_key == "_rml_folder") {
        	do_action("RML/Item/Moved", $object_id, "-1", $_meta_value);
    	}
    }
    public function update_post_meta($meta_id, $object_id, $meta_key, $_meta_value) {
    	if ($meta_key == "_rml_folder") {
        	do_action("RML/Item/Moved", $object_id, get_post_meta($object_id, "_rml_folder", true), $_meta_value);
    	}
    }
    
    public static function getInstance() {
        if (self::$me == null) {
                self::$me = new RML_Filter();
        }
        return self::$me;
    }
}
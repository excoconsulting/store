<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class RML_Backend {
	private static $me = null;
        
    private function __construct() {
        
    }
    
    /**
     * Data for the wp localize script.
     * 
     * @return array
     */
    private function localize_js() {
        $additional = array();

        return array_merge($additional, array(
            'lang' => array(
                "save"                              => __('Save'),
                "close"                             => __('Close'),
                "cancel"                            => __('Cancel'),
                "done"                              => __('Done', RML_TD),
                "failed"                            => __('Failed', RML_TD),
                "noMedia"                           => __('No media files found.'),
                "uploadingCollection"               => __('A collection can not contain files. Upload moved to Uncategorized...', RML_TD),
                "uploadingGallery"                  => __('A gallery can contain only images. Upload moved to Uncategorized...', RML_TD),
                "deleteFailed"                      => __('In this folder are sub directories, please delete them first!', RML_TD),
                "deleteRoot"                        => __('Do not delete root. :(', RML_TD),
                "deleteConfirm"                     => __('Would you like to delete this folder? Note: All files in this folder will be deleted.', RML_TD),
                "deleteConfirmTitle"                => __('Are you sure?', RML_TD),
                "deleteConfirmSubmit"               => __("Yes, delete it!", RML_TD),
                "deleteConfirmCancel"               => __("Cancel"),
                "moveFromAllConfirmSubmit"          => __("Move it!", RML_TD),
                "moveFromAllConfirmText"            => __("You want to move one or more files from different folder sources because you are in the \"All Files\" view.", RML_TD),
                "moveSingleFile"                    => __('Move {0} file', RML_TD),
                "moveMultipleFiles"                 => __('Move {0} files', RML_TD),
                "renameRoot"                        => __('Do not rename root. :(', RML_TD),
                "renamePrompt"                      => __('Tell me the new name', RML_TD),
                "renamePromptTitle"                 => __('Rename folder', RML_TD),
                "orderFailedFilterOn"               => __("In the current view of uploads are filters active. Please reset these and refresh the view."),
                "detailsFailedOn"                   => __('Please got to your media library "Media > Library" to open the details window for the selected folder.', RML_TD),
                "detailsFailedAlreadyOpen"          => __('A folder details window is already opened.', RML_TD),
                "wipe"                              => __('Are you sure?', RML_TD),
                "metadata" => array(
                    "coverImage" => array(
                        "label_add" => __('Set cover image', RML_TD),
                        "label_replace" => __('Select another cover image', RML_TD),
                        "label_remove" => __('No cover image', RML_TD),
                        "label_modal" => __('Select cover image', RML_TD),
                        "label_button" => __('Set as cover image', RML_TD)
                    )
                ),
                
                "createTypes" => array(
                    "folder" => array(
                        "toolTipTitle" => __("Click this to create a <strong>new folder</strong>", RML_TD),
                        "toolTipText" => __("A folder can contain every type of file and collections, but no galleries. If you want to create a subfolder simply select a folder from the list and click this button.", RML_TD)
                    ),
                    "collection" => array(
                        "toolTipTitle" => __("Click this to create a <strong>new collection</strong>", RML_TD),
                        "toolTipText" => __("A collection can contain no files. But you can create there other collections and <strong>galleries</strong>. The mentioned above gallery is only a <i>gallery data folder</i>, that means they are not automatically in your frontend (your website).<br/><br/>You can create a <strong>visual gallery</strong> from this <i>gallery data folder</i> via the Visual Editor in your page/post.", RML_TD)
                    ),
                    "gallery" => array(
                        "toolTipTitle" => __("Click this to create a <strong>new gallery data folder</strong>", RML_TD),
                        "toolTipText" => __("A <i>gallery data folder</i> can only contain images. It is simplier for you to distinguish where your visual galleries are.<br/><br/>You can also order the images into <strong>a custom image order</strong> per drag&drop.", RML_TD)
                    )
                ),
                "toolbarItems" => array(
                    "order" => array(
                        "toolTipTitle" => __("Reorder images", RML_TD),
                        "toolTipText" => __('Start to reorder the images in the current gallery. The order of a folder can be reset in the media options.<br />Go to the folder details to order a gallery by <strong>title, filename, ID, ...</strong>', RML_TD),
                        "toolTipTextDisabledLink" => __("Please select a <i class='mwf-gallery'></i> <i>gallery data folder</i> to reorder the images into a <strong>custom image order</strong>.<br/><br/><u>How you can create a gallery:</u><br />1. Create a new <i class='mwf-collection'></i> collection.<br/>2. Select the created collection and create a <i class='mwf-gallery'></i> gallery data folder.", RML_TD)
                    ),
                    "refresh" => array(
                        "toolTipTitle" => __("Refresh", RML_TD),
                        "toolTipText" => __("Refreshes the current folder view.", RML_TD)
                    ),
                    "rename" => array(
                        "toolTipTitle" => __("Rename", RML_TD),
                        "toolTipText" => __("Rename the current selected folder.", RML_TD),
                        "toolTipTextDisabledLink" => __("The selected folder can not be renamed. Please select a user-created folder.", RML_TD)
                    ),
                    "delete" => array(
                        "toolTipTitle" => __("Delete", RML_TD),
                        "toolTipText" => __("Delete the current selected folder.", RML_TD),
                        "toolTipTextDisabledLink" => __("The selected folder can not be deleted. Please select a user-created folder.", RML_TD)
                    ),
                    "rearrange" => array(
                        "toolTipTitle" => __("Rearrange", RML_TD),
                        "toolTipText" => __("Change the hierarchical order of the folders.", RML_TD)
                    ),
                    "details" => array(
                        "toolTipTitle" => __("Folder details", RML_TD),
                        "toolTipText" => __("Select a folder and view more details about it.", RML_TD),
                        "toolTipTextDisabledLink" => __("Please select a folder to make this button enabled.", RML_TD)
                    ),
                    "restrictionsInherits" => __("New folders inherit this restriction", RML_TD),
                    "restrictionsSuffix" => __("The current selected folder has some restrictions:", RML_TD),
                    "restrictions" => array(
                        "rea" => __("You can not <b>rearrange</b> subfolders", RML_TD),
                        "cre" => __("You can not <b>create</b> subfolders", RML_TD),
                        "ins" => __("You can not <b>insert</b> new files. New files will be moved to Uncategorized...", RML_TD),
                        "ren" => __("You can not <b>rename</b> the folder", RML_TD),
                        "del" => __("You can not <b>delete</b> the folder", RML_TD),
                        "mov" => __("You can not <b>move</b> files outside the folder", RML_TD)
                    )
                ),
                'mceButtonTooltip' => __('Gallery from Media Folder', RML_TD),
                'mceListBoxDirsTooltip' => __('Note: You can only select galleries. Folders and collections are grayed.', RML_TD),
                'mceBodyGallery' => __('Folder', RML_TD),
                'mceBodyLinkTo' => __('Link to'),
                'mceBodyColumns' => __('Columns'),
                'mceBodyRandomOrder' => __('Random Order'),
                'mceBodySize' => __('Size'),
                'mceBodyLinkToValues' => array(
                    array("value" => "post", "text" => __('Attachment File')),
                    array("value" => "file", "text" => __('Media File')),
                    array("value" => "none", "text" => __('None'))
                ),
                'mceBodySizeValues' => array(
                    array("value" => "thumbnail", "text" => __('Thumbnail')),
                    array("value" => "medium", "text" => __('Medium')),
                    array("value" => "large", "text" => __('Large')),
                    array("value" => "full", "text" => __('Full Size'))
                )
            )
        ));
    }
    
    public function admin_enqueue_scripts($hook) {
        if (!current_user_can("upload_files")) {
            return;
        }
        
        $debug = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG === true);
        
        /***==============================
    	 * Scripts
    	 * 
    	 * The javascript should be build like it is
    	 * not conflicting with any other plugin. So, RML
    	 * includes a own hook system window.rmlHooks
    	 */
        
    	/**
    	 * jQuery scripts (Helper)
    	 * core.js, widget.js, mouse.js, draggable.js, droppable.js, sortable.js
    	 */
    	$requires = array("jquery", "jquery-ui-core", "jquery-ui-widget", "jquery-ui-mouse", "jquery-ui-draggable", "jquery-ui-droppable", "jquery-ui-sortable", "jquery-touch-punch");
        foreach ($requires as $value) {
            wp_enqueue_script($value);
        }
    	
    	/**
    	 * Only minified scripts
    	 */
    	wp_enqueue_script('jquery-tooltipster',         plugins_url( 'assets/js/jquery.tooltipster.min.js', RML_FILE ), $requires, RML_VERSION);
    	wp_enqueue_script('jquery-nested-sortable',     plugins_url( 'assets/js/jquery.ui.nested-sortable.min.js', RML_FILE ), $requires, RML_VERSION);

    	/**
    	 * RML relevant scripts (hooks)
    	 */
    	if (!$debug) {
    	    wp_enqueue_script('jquery-aio-tree',            plugins_url( 'assets/js/jquery.ui.aio-tree.min.js', RML_FILE ), $requires, RML_VERSION);
    	    wp_enqueue_script('wp-media-picker',            plugins_url( 'assets/js/jquery.wp-media-picker.min.js', RML_FILE ), $requires, RML_VERSION);
    	    wp_enqueue_script('rml-general',                plugins_url( 'assets/js/realmedialibrary.min.js', RML_FILE ), $requires, RML_VERSION);
    	}else{
    	    wp_enqueue_script('jquery-aio-tree',            plugins_url( 'assets/js/jquery.ui.aio-tree.js', RML_FILE ), $requires, RML_VERSION);
    	    wp_enqueue_script('wp-media-picker',            plugins_url( 'assets/js/jquery.wp-media-picker.js', RML_FILE ), $requires, RML_VERSION);
        	wp_enqueue_script('rml-general',                plugins_url( 'assets/js/rml.0-general.js', RML_FILE ), $requires, RML_VERSION);
        	wp_enqueue_script('rml-library',                plugins_url( 'assets/js/rml.1-library.js', RML_FILE ), $requires, RML_VERSION);
        	wp_enqueue_script('rml-grid',                   plugins_url( 'assets/js/rml.2-grid.js', RML_FILE ), $requires, RML_VERSION);
        	wp_enqueue_script('rml-list',                   plugins_url( 'assets/js/rml.3-list.js', RML_FILE ), $requires, RML_VERSION);
        	wp_enqueue_script('rml-modal',                  plugins_url( 'assets/js/rml.4-modal.js', RML_FILE ), $requires, RML_VERSION);
        	wp_enqueue_script('rml-order',                  plugins_url( 'assets/js/rml.5-order.js', RML_FILE ), $requires, RML_VERSION);
        	wp_enqueue_script('rml-meta',                   plugins_url( 'assets/js/rml.6-meta.js', RML_FILE ), $requires, RML_VERSION);
        	wp_enqueue_script('rml-uploader',               plugins_url( 'assets/js/rml.8-uploader.js', RML_FILE ), $requires, RML_VERSION);
        	wp_enqueue_script('rml-options',                plugins_url( 'assets/js/rml.7-options.js', RML_FILE ), $requires, RML_VERSION);
        	wp_enqueue_script('rml-main',                   plugins_url( 'assets/js/rml.99-main.js', RML_FILE ), $requires, RML_VERSION);
    	}
    	wp_localize_script('rml-general', 'rmlOpts',        apply_filters('RML/Backend/JS_Localize', $this->localize_js()));
    	
    	/***==============================
    	 * Styles
    	 */
    	
    	wp_enqueue_style('font-awesome-fa',             'https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css');
    	wp_enqueue_style('rml-font',                    plugins_url( 'assets/minifyfont/css/minifyfont.css', RML_FILE ), array(), RML_VERSION);
    	
    	if (!$debug) {
        	wp_enqueue_style('tooltipster',                 plugins_url( 'assets/css/jquery.tooltipster.min.css', RML_FILE ), array(), RML_VERSION);
        	wp_enqueue_style('aio-tree',                    plugins_url( 'assets/css/jquery.ui.aio-tree.min.css', RML_FILE ), array(), RML_VERSION);
        	wp_enqueue_style('aio-tree-theme-wordpress',    plugins_url( 'assets/css/jquery.ui.aio-tree-theme-wordpress.min.css', RML_FILE ), array(), RML_VERSION);
        	wp_enqueue_style('wp-media-picker',             plugins_url( 'assets/css/jquery.wp-media-picker.min.css', RML_FILE ), array(), RML_VERSION);
        	wp_enqueue_style('rml-main-style',              plugins_url( 'assets/css/style.min.css', RML_FILE ), array(), RML_VERSION);
        	wp_enqueue_style('rml-sweetalert',              plugins_url( 'assets/css/sweetalert.min.css', RML_FILE ), array(), RML_VERSION);
    	}else{
    	    wp_enqueue_style('tooltipster',                 plugins_url( 'assets/css/jquery.tooltipster.css', RML_FILE ), array(), RML_VERSION);
        	wp_enqueue_style('aio-tree',                    plugins_url( 'assets/css/jquery.ui.aio-tree.css', RML_FILE ), array(), RML_VERSION);
        	wp_enqueue_style('aio-tree-theme-wordpress',    plugins_url( 'assets/css/jquery.ui.aio-tree-theme-wordpress.css', RML_FILE ), array(), RML_VERSION);
        	wp_enqueue_style('wp-media-picker',             plugins_url( 'assets/css/jquery.wp-media-picker.css', RML_FILE ), array(), RML_VERSION);
        	wp_enqueue_style('rml-main-style',              plugins_url( 'assets/css/style.css', RML_FILE ), array(), RML_VERSION);
        	wp_enqueue_style('rml-sweetalert',              plugins_url( 'assets/css/sweetalert.css', RML_FILE ), array(), RML_VERSION);
    	}
    	
    	/**
    	 * Options media relevant styles
    	 */
    	if ($this->isScreenBase("options-media")) {
    	    wp_enqueue_style('rml-options-style',       plugins_url( 'assets/css/options.css', RML_FILE ), array(), RML_VERSION);
    	}
    	
    	// Enqueue scripts and styles for the media library screen
    	if ($this->isScreenBase("upload")) {
    	    do_action("RML/Backend/Scripts/MediaLibrary");
    	}
    }
    
    public function isScreenBase($base, $log = false) {
        if (function_exists("get_current_screen")) {
            $screen = get_current_screen();
        }else{
            return false;
        }
        
        if ($log) {
            error_log($screen->base);
        }
        
        if (isset($screen->base)) {
            return $screen->base == $base;
        }else{
            return false;
        }
    }
    
    public function admin_footer() {
        if ($this->isScreenBase("theme-install") || !current_user_can("upload_files")) {
            return;
        }
        
        $pathes = array(
            "inc/admin_footer/sidebar.dummy.php",
            );
            
        if ($this->isScreenBase("options-media")) {
            $pathes[] = "inc/admin_footer/options.dummy.php";
        }
        
        for ($i = 0; $i < count($pathes); $i++) {
            require_once(RML_PATH . '/' . $pathes[$i]);
        }
    }
    
    public static function getInstance() {
        if (self::$me == null) {
            self::$me = new RML_Backend();
        }
        return self::$me;
    }
}

?>
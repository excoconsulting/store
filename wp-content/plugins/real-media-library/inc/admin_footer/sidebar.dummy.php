<?php
/**
 * This file creates a dummy for the sidebar
 * shown in the media library. Javascript handles
 * it, to append it to the components.
 * 
 * @author MatthiasWeb
 * @package real-media-library
 * @since 1.0
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

$folders = RML_Structure::getInstance();
$view = $folders->getView();
$folderActiveId = isset($_REQUEST['rml_folder']) ? $_REQUEST['rml_folder'] : "";
$folderActive = wp_rml_get_by_id($folderActiveId, null, true);
$folderTree = $view->treeHTML($folderActiveId);

// Output extra CSS for the resized sidebar
$cookieWidth = isset($_COOKIE["rml_" . get_current_blog_id() . "_resize"]) ? $_COOKIE["rml_" . get_current_blog_id() . "_resize"] : 0;
if ($cookieWidth < 250) {
    $cookieWidth = 250;
}
echo '<style type="text/css">
body.wp-admin.upload-php #wpbody-content {
    width: calc(100% - ' . $cookieWidth . 'px);
}.rml-container.rml-no-dummy {
    width: ' . $cookieWidth . 'px;
}
</style>';

// Get the folder type of current folder for frontend
$folderActiveType = $folderActive == null ? "none" : $folderActive->type;

// Output the sidebar
?>
<!-- RML: Do not worry about this code, it is only loaded if you have capability to upload images. -->
<div class="aio-tree aio-tree-fade rml-container rml-dummy"
    id="rml<?php echo get_current_blog_id(); ?>"
    style="display:none;">
    <div class="wrap aio-wrap rml-hide-upload-preview-<?php echo get_option('rml_hide_upload_preview', 0); ?>">
        <div class="aio-fixed-header">
            <h3 class="aio-tree-headline">
                <?php _e('Folders', RML_TD); ?>
            </h3>
            
            <?php // Sidebar header
            ?>
            <div class="aio-toolbar">
                <div class="aio-toolbar-placeholder" style="color: #ef5757;">
                    <i class="fa fa-warning aio-tooltip" style="display:none;"
                        data-aio-tooltip-title="<?php _e("Restrictions", RML_TD); ?>"
                        data-aio-tooltip-text=""></i>&nbsp;
                </div>
            	<div class="aio-toolbar-items"></div>
            	<div class="clear"></div>
            </div>
        </div>
        <div class="rml-uploading" style="display:none;">
            <div class="rml-uploading-details">
                <div class="rml-uploading-details-remain-time"><?php _e('Remaining time', RML_TD); ?> <strong>-</strong></div>
                <div class="rml-uploading-details-remain-bytes"><strong>0 KB</strong> / s</div>
                <div class="rml-uploading-details-remain-loaded"><strong>0 KB</strong> / <span>0 KB</span></div>
            </div>
            <div class="rml-uploading-list"></div>
        </div>
        <div class="aio-fixed-header-helper"></div>
        
        <?php // Sidebar content: Folder list
        do_action("RML/Sidebar/Content");
        
        ?>
        <div class="aio-list-standard">
            <?php
            echo $folders->getView()->createNode(null, "", RML_TYPE_ALL, "", __('All Files', RML_TD),
                    $folders->getCntAttachments(), $folderActiveId);
                    
            echo $folders->getView()->createNode(null, -1, RML_TYPE_ROOT, "/", "/ " . __('Unorganized', RML_TD),
                    $folders->getCntRoot(), $folderActiveId)
            ?>
            
            <hr />
            
            <div class="aio-nodes">
                <?php echo $folderTree; ?>
            </div>
            <div class="aio-no-content">
                <div class="aio-no-content-cover"></div>
                <h3><?php _e("You have no folders.", RML_TD); ?></h3>
                <h5><?php _e("Simply create a folder by clicking the above button. You can also create a collection-gallery relation.", RML_TD); ?></h5>
            </div>
        </div>
        
        <?php
        if (get_option('rml_hide_info_links', 0) != 1) {
        ?>
        <div class="aio-info-links">
            <a href="http://codecanyon.net/item/wp-real-media-library-organize-your-uploads/13155134" target="_blank">RML Version <?php echo RML_VERSION; ?></a>
            <br />
            <a href="http://justifiedgrid.com/" target="_blank"><?php _e('Tip: Make your photos shine with Justified&nbsp;Image&nbsp;Grid', RML_TD); ?></a>
            <?php do_action('RML/Sidebar/InfoLinks'); ?>
        </div>
        <?php
        }
        ?>
    </div>
</div>
<!-- END RML -->
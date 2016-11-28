/**
 * List of hooks:
 * 
 * - folderMeta/loaded: Meta data loaded
 * - folderMeta/parsed: Meta dialog parsed
 * - folderMeta/save/failed: The changes failed to save
 * - folderMeta/save/success: The changes were successfully saved
 */

/* global jQuery rmlOpts RMLisDefined */

/**
 * Create handler for failed changes. Show the error messages
 * at the top of the meta box.
 */
window.rml.hooks.register("folderMeta/save/failed", function(response, fields, ajaxContext, sweet, name, $) {
    window.rml.sweetAlert.enableButtons();
    
    var liHTML = "<li>" + response.data.errors.join("</li><li>") + "</li>";
    jQuery(".rml-meta-errors").html(liHTML).show();
    window.rml.library.sweetAlertPosition();
});

/**
 * Create handler for successful changes. Close the
 * dialog.
 * 
 * It also handles the rename process.
 */
window.rml.hooks.register("folderMeta/save/success", function(response, fields, ajaxContext, sweet, name, $) {
    window.rml.sweetAlert.close();
    
    var folderId = fields.folderId;
        
    // Rename the folder object
    if (RMLisDefined(response.data.newSlug)) {
        var slug = response.data.newSlug,
            newName = fields.name;
        window.rml.hooks.call("renamed", [ folderId, newName, slug ], $(this)); // @see library.js registered hooks
    }
});

/**
 * Create sweet alert with this folder meta.
 */
window.rml.hooks.register("folderMeta/loaded", function(response, fid, name, $) {
    var that = $(this);
    
    // Show the custom fields dialog!
    window.rml.sweetAlert({
        title: name,
        text: response,
        html: true,
        confirmButtonText: rmlOpts.lang.save,
        cancelButtonText: rmlOpts.lang.close,
        closeOnConfirm: false,
        showLoaderOnConfirm: true,
        showCancelButton: true
    }, function() {
        var sweet = this;
        jQuery(".rml-meta-errors").hide();
        window.rml.library.sweetAlertPosition();
        
        // Serialize the meta data form
        var data = jQuery("form.rml-meta").serializeArray();
        var fields = { };
        jQuery.each( data, function( key, value ) {
            fields[value.name] = value.value;
        });
        
        fields.action = "rml_meta_save";
        fields.nonce = rmlOpts.nonces.metaSave;
        
        // Post it!
        jQuery.ajax({
            url: rmlOpts.ajaxUrl,
            type: 'POST',
            data: fields,
            success: function(response) {
                var hookName;
                if (response.success) {
                    hookName = "folderMeta/save/success";
                }else{
                    hookName = "folderMeta/save/failed";
                }
                
                /**
                 * Register to this two hooks above!
                 * 
                 * @param response The response from the server after saved
                 * @param fields The POST query
                 * @param this The ajax request
                 * @param sweet The dialog
                 * @param name The name of the folder
                 */
                window.rml.hooks.call(hookName, [ response, fields, this, sweet, name ], that);
            }
        });
    });
    
    // Hook after dialog parsed
    setTimeout(function() {
        window.rml.hooks.call("folderMeta/parsed", [ response, fid, name ], that);
    }.bind(this), 500);
});

/**
 * ========================================================
 * 
 *          Use the media picker in the cover image.
 * 
 * ========================================================
 */
window.rml.hooks.register("folderMeta/parsed", function(response, fid, name, $) {
    // Check the filter on in the media gallery
    var hasFilter = $("body").hasClass("rml-view-gallery-filter-on");
    
    var picker = $(".rml-meta-media-picker");
    if (picker.size() <= 0) {
        return;
    }
    
    picker.wpMediaPicker({
        label_add: rmlOpts.lang.metadata.coverImage.label_add,
        label_replace: rmlOpts.lang.metadata.coverImage.label_replace,
        label_remove: rmlOpts.lang.metadata.coverImage.label_remove,
        label_modal: rmlOpts.lang.metadata.coverImage.label_modal,
        label_button: rmlOpts.lang.metadata.coverImage.label_button,
        query: {
            post_mime_type: 'image'
        },
        onShow: function() {
            $(".sweet-overlay,.sweet-alert").fadeOut();
        },
        onClose: function() {
            $(".sweet-overlay,.sweet-alert").fadeIn();
            // Fix filter
            if (!hasFilter) {
                $("body").removeClass("rml-view-gallery-filter-on");
            }
        },
        htmlChange: function() {
            setTimeout(function() {
                picker.parents("td").find("i.fa").remove();
                $(".rml-meta-media-picker").parents("fieldset").show();
                window.rml.library.sweetAlertPosition();
            }.bind(this), 500);
        }
    });
});

/**
 * Add action button handler.
 */
window.rml.hooks.register("ready", function($) {
    // Wipe data and action buttons
    $(document).on("click", ".rml-button-wipe, .sweet-alert a.actionbutton", function(e) {
        if (window.confirm(rmlOpts.lang.wipe)) {
            var id = $(this).attr("id"), useCallback = false;
            if (RMLisDefined(id)) {
                window.rml.hooks.call("folderMeta/action/" + id);
                useCallback = true; // Call hook for this function when data is finished
            }
            
            var button = $(this), method = button.attr("data-method"), action = $(this).attr("data-action");
            button.html("<i class=\"fa fa-circle-o-notch fa-spin\"></i>").prop("disabled", true);
            button.attr("disabled", "disabled"); // for <a>-tags
            
            var post = {
                action: action,
                nonce: rmlOpts.nonces[$(this).attr("data-nonce-key")],
                method: method
            };
            
            window.rml.hooks.call("actionButton/" + action, [ post ], this);
            
            jQuery.ajax({
                url: rmlOpts.ajaxUrl,
                data: post,
                invokeData: button,
                success: function(response) {
                    var _btn = this.invokeData;
                    if (useCallback) {
                        window.rml.hooks.call("folderMeta/actionFinished/" + id, [ response ]);
                    }
                    window.rml.hooks.call("actionButtonDone/" + action, [ post ], this);
                    
                    if (response.success) {
                        _btn.html("<i class=\"fa fa-check\"></i> " + rmlOpts.lang.done);
                    }else{
                        _btn.html("<i class=\"fa fa-error\"></i> " + rmlOpts.lang.failed);
                    }
                    button.attr("disabled", false);
                }
            });
        }
        e.preventDefault();
        return false;
    });
});
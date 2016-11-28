/* global jQuery wp RMLWpIs rmlOpts RMLisAIO */

/**
 * Change the AllInOneTree for the ajax-supported media grid view.
 * The switchFolder action hook is only called in grid mode!
 */
window.rml.hooks.register("aioSettings/grid", function(settings, $) {
    /**
     * Folder switch handler => AJAX call
     */
    settings.others.onSwitchFolder = function(obj, oldID) {
        if (!RMLisAIO($(this))) { // avoid no AIO container
            return false;
        }
        
        var id = obj.attr("data-aio-id"),
            type = obj.attr("data-aio-type"),
            selectToChange; // The <select> tag for which we are changing
            
        // No action while creating a new folder
        if (id == "AIO_NEW_TO_CREATE" || oldID == "AIO_NEW_TO_CREATE") {
            return false;
        }
        
        // Set the active
        $(this).allInOneTree("active", id, true);

        // Get the <select> tag
        selectToChange = $($(this).allInOneTree("option").container.customSelectToChange);
        selectToChange.val(id.length > 0 ? id : "all").change();
        
        // Refresh if needed
        if (obj.hasClass("needs-refresh")) {
            $(this).allInOneTree("toolbarButton", "refresh");
            obj.removeClass("needs-refresh");
        }
        
        // Do a movement reinit (for the case, wordpress loads the files from the cache)
        setTimeout(function() {
            $(this).allInOneTree("reinit", "movement");
        }.bind(this), 500);
        
        // Call hook
        window.rml.hooks.call("switchFolder", [ obj, obj.attr("data-aio-id"), obj.attr("data-aio-type") ], $(this));
        return false;
    };
});

/**
 * Save another important hooks for the grid view in "Media Library" page
 */
window.rml.hooks.register("afterInit/grid", function($) {
    var container = $(this); // Take the container from context
    
    /**
     * We are in grid mode, so if some attachments come new to
     * the grid view, make them draggable, too.
     */
    window.rml.hooks.register("attachmentsChanged", function() {
        if (RMLisAIO(container)) {
            container.allInOneTree("reinit", "movement");
        }
    });
    
    /**
     * "Edit selection" button is clicked and the back to library
     * button is visible, then disable the movement.
     */
    var backbone = window.rml.library.getBackboneOfAIO(container);
    if (typeof backbone.view !== "undefined") {
        var returnToLibrary = backbone.view.$( '.media-button-backToLibrary' );
        if (returnToLibrary.size() > 0) {
            container.allInOneTree("movement", false);
        }
    }
    
    /**
     * Simulate a click on the given first folder (can be active
     * from the switch list => grid view)
     */
    container.allInOneTree("active", container.allInOneTree("active"));
});
/**
 * Initialize a new tree for the media modal.
 */
window.rml.hooks.register("newModal", function(filter, $) {
    var menu = $(this).find(".media-menu"),
        container,
        containerID = $(this).parent().attr("id");
        
    if (!menu.data("rml")) {
        menu.append('<div class="separator"></div>');
        menu.data("rml", true);
    }else{
        menu.find(".aio-tree").remove();
    }
    $(this).addClass("rml-media-modal");
    $(this).find(".media-frame.hide-menu").removeClass("hide-menu"); // Never hide the medie menu frame
    
    // If it is the "Edit gallery" modal then create no tree
    var mediaButtonReverse = $(this).find(".media-button-reverse");
    if (mediaButtonReverse.is(":visible")) {
        return;
    }
    
    // Add tree container to left menu
    container = $(".rml-container.rml-dummy").clone().appendTo(menu);
    container.removeClass("rml-dummy").addClass("rml-no-dummy");
    
    // Add modal library relevant options
    var aioSettings = $.extend(true, {}, window.rml.defaultAioSettings, {
        container: {
            isListMode: false,
            customSelectToChange: "#" + containerID + " .attachment-filters-rml",
            isResizable: false,
            isSticky: false,
            isResizable: false,
            isWordpressModal: true,
            theme: "wordpress wordpress-fixed"
        },
        movement: {
            selector: "#" + containerID + " ul.attachments > li"
        }
    });
    
    // Apply filters to the allInOneTree
    window.rml.hooks.call("aioSettings", aioSettings);
    window.rml.hooks.call("aioSettings/grid", aioSettings);
    
    // Apply filters to the allInOneTree modal mode
    window.rml.hooks.call("aioSettings/modal", aioSettings);
    window.rml.hooks.call("aioSettings/modal/grid", aioSettings);
    
    // Create the tree
    container.allInOneTree(aioSettings);
    
    window.rml.hooks.call("afterInit", false, container);
    window.rml.hooks.call("afterInit/grid", false, container);
    // modal after init
    window.rml.hooks.call("afterInit/modal", [ menu, $(this) ], container);
});

/**
 * Set an interval, which searches for new modal selects.
 * 
 * @hook newModal
 */
window.rml.hooks.register("general", function($) {
    setInterval(function() {
        $(".media-modal .attachment-filters-rml").each(function() {
            if ($(this).data("initialized") != true) {
                $(this).data("initialized", true);
                window.rml.hooks.call("newModal", $(this), $(this).parents(".media-modal"));
            }
        });
    }, 500);
});
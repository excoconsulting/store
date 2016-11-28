/**
 * This file is only called in the media options.
 */
 
/* global wp RMLStartsWith jQuery */

window.rml.hooks.register("ready/noMediaLibrary", function(e, args) {
    var $ = jQuery;
    
    // append to known option
    var container = $(".rml-options.rml-dummy")
                        .insertBefore($('[for="rml_hide_upload_preview"]').parents("table").prev())
                        .removeClass("rml-dummy");
    var headline = $('<h2>Real Media Library</h2>').insertBefore(container);
    var nav = container.find("nav ul");
    var navLiCnt = 0;
                        
    // Search the option panels
    $("table.form-table").each(function() {
        var oHeadline = $(this).prev(),
            sHeadline = oHeadline.html();
        if (typeof sHeadline !== "undefined" && RMLStartsWith(sHeadline, "RealMediaLibrary")) {
            sHeadline = sHeadline.split(":", 2)[1];
            
            // Append headline to options panel
            var li = $("<li class=\"" + ((navLiCnt === 0) ? "active" : "") + "\">" + sHeadline + "</li>").appendTo(nav);
            var section = $(this).appendTo(container);
            
            if (navLiCnt === 0) {
                section.show();
            }
            
            li.click(function() {
                container.children("table").hide();
                nav.find(".active").removeClass("active");
                $(this).addClass("active");
                section.show(); 
            });
            
            oHeadline.remove();
            navLiCnt++;
        }
    });
});
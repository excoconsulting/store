"use strict";

/*global mOxie RMLisDefined wp rmlOpts jQuery Image */

window.rml.uploader = {
    uploading: {},
    sucessTimeout: null,
    
    /**
     * Bind actions to the uploading process.
     */
    init: function() {
        var $ = jQuery;
        if (typeof window.wp !== 'undefined' && typeof wp.Uploader === 'function') {
            $.extend( wp.Uploader.prototype, {
                init : function() { // plupload 'PostInit'
                    window.rml.hooks.call("uploader/init", [ ], this);
                
                    this.uploader.bind('FilesAdded', function( up, files ) {
            			for ( var i = 0 ; i < files.length ; i++ ) {
            			    // Get current folder options
            			    var active = window.rml.library.getObjectOfMediaPickerOrActive(),
            		            opts = window.rml.uploader.settings(active, files[i], opts);
            		        window.rml.uploader.uploading[files[i].attachment.cid] = opts;
            		        
            		        files[i].rmlFolder = opts.id;

            			    // Call the frontend view (left sidebar)
            			    window.rml.uploader.prepareUpload(files[i], active, opts);
            			    window.rml.hooks.call("uploader/FilesAddedSingle", [ up, files[i], active, opts ], this);
            			}
            		});
            		
            		this.uploader.bind('BeforeUpload', function(uploader, file) {
            		    window.rml.hooks.call("uploader/BeforeUpload", [ uploader, file ], this);
            		    
            		    // Set server-side-readable rmlfolder id
            		    var params = uploader.settings.multipart_params;
                        params.rmlFolder = file.rmlFolder;
                        
                        if (!RMLisDefined(params.rmlFolder)) {
                            var active = window.rml.library.getObjectOfMediaPickerOrActive(),
            		            opts = window.rml.uploader.settings(active, file, opts);
            		        params.rmlFolder = opts.id;
                        }
            		});
            		
            		// The upload progress on left sidebar
            		this.uploader.bind('UploadProgress', function(up, file) {
            		    /**
        			     * HOOK: Upload progress of the current file.
        			     * 
        			     * @args up uploader object of pluploader
        			     * @args file the current file object
        			     */
            		    window.rml.hooks.call("uploader/UploadProgress", [ up, file ], this);
            		    window.rml.uploader.progress(up, file);
            		});
            		
            		// Reset the upload queue
            		this.uploader.bind('UploadComplete', function(up, files) {
            		    up.splice();
            		    up.total.reset();
            		    
            		    // Refresh the count of the container
            		    try {
                		    window.rml.library.refreshAllCounts();
            		    }catch(e) {}
            		});
                },
                success : function( file_attachment ) { // plupload 'FileUploaded'
                    window.rml.hooks.call("upload/success", [ file_attachment ], this);
                    window.rml.uploader.success(file_attachment);
                }
            });
        }
    },
    
    /**
     * Success handler for uploaded files. Move the file to
     * the specific folder.
     */
    success: function(fileObj) {
        var $ = jQuery;
        var item = $('.rml-uploading-' + fileObj.cid);
        if (item.size() > 0) {
            var data = {
                'action': 'bulk_move',
                'ids' : [fileObj.id],
                'to' : parseInt(item.attr("data-id"))
            };
            
            item.find(".percent").css("width", "100%");
            item.find(".read_percent").html("100%");
            item.remove();
            window.rml.hooks.call("upload/success/moved", [ fileObj, data ], this);
            
            // check if last
            if (jQuery(".rml-uploading").find(".rml-uploading-item").size() <= 0) {
                $(".rml-uploading").stop().slideUp();
                window.rml.hooks.call("upload/success/movedAll", [ ], this);
            }
            
            // Refresh if neccessery
            if (this.sucessTimeout != null) {
                clearTimeout(this.sucessTimeout);
            }
            
            this.sucessTimeout = setTimeout(function() {
                try {
                    $(".aio-tree-instance").each(function() {
                        window.rml.hooks.call("refreshView", [], $(this));
                    });
                }catch (e) {
                    console.log(e);
                }
                clearTimeout(window.rml.uploader.sucessTimeout);
            }, 1000);
        }
    },
    
    /**
     * Handles a progress for a uploading item
     */
    progress: function(up, file) {
        var $ = jQuery;
        
        file.percent -= 1;
        if (file.percent < 0) {
            file.percent = 0;
        }
        
        var item = $('.rml-uploading-' + file.id),
            percent =  file.percent + "%";
        
        if (item.size() > 0) {
            item.find(".percent").css("width", percent);
            item.find(".read_percent").html(percent);
            
            if (file.percent > 98) {
                item.find(".percent").addClass("finish");
            }
        }
    },
    
    /**
     * Get the settings (slug, id, deny) when uploading to a current folder.
     * 
     * @param active Active folder object
     * @param file plupload file object
     * @return object
     */
    settings: function(active, file) {
        var $ = jQuery, opts = {
                slug: null,
                id: null,
                deny: ''
            };
        
        // Get translation texts for warnings
        var uploadingTexts = {
            denyCollection: rmlOpts.lang.uploadingCollection,
            denyGallery: rmlOpts.lang.uploadingGallery,
            denyPermission: rmlOpts.lang.toolbarItems.restrictions.ins
        };
        
        // Get active folder
        if (active.size() > 0) {
            opts.slug = active.attr("data-slug");
            if (typeof opts.slug === "undefined") {
                opts.slug = "/";
            }
            opts.id = parseInt(active.attr("data-aio-id"));
    
            // Check folder type and automatically redirect it to root folder.
            if (active.attr("data-restrictions").indexOf("ins") > -1) {
                // Wrong permission
                opts.slug = "/";
                opts.id = -1;
                opts.deny = '<div class="warnings">\
                    <span class="deny-gallery">' + uploadingTexts.denyPermission + '</span>\
                </div>';
            }else if (active.attr("data-aio-type") == "1") {
                opts.slug = "/";
                opts.id = -1;
                opts.deny = '<div class="warnings">\
                    <span class="deny-collection">' + uploadingTexts.denyCollection + '</span>\
                </div>';
            }else if (active.attr("data-aio-type") == "2") {
                // May only contain image files
                var allowedExts = [ 'jpg', 'jpeg', 'jpe', 'gif', 'png' ],
                    ext = file.name.substr(file.name.lastIndexOf('.') + 1).toLowerCase();
                if ($.inArray(ext, allowedExts) <= -1) {
                    opts.slug = "/";
                    opts.id = -1;
                    opts.deny = '<div class="warnings">\
                        <span class="deny-gallery">' + uploadingTexts.denyGallery + '</span>\
                    </div>';
                }
            }
        }else{
            opts.slug = "/";
            
            // Get the id
            var select = $(".attachment-filters.attachment-filters-rml");
            if (select.size() > 0 && select.val() != "all") {
                opts.id = parseInt(select.val());
            }else{
                opts.id = -1;
            }
        }
        
        return opts;
    },
    
    /**
     * Create a uploading file row with preview image
     */
    prepareUpload: function(file, active, opts) {
        var $ = jQuery;
        
        // Create row item
        var item = $( '<div class="rml-uploading-item rml-uploading-' + file.attachment.cid + ' rml-uploading-' + file.id + '""\
                    data-slug="' + opts.slug + '" \
                    data-id="' + opts.id + '"\>\
                <div class="left"></div>\
                <div class="right">\
                    <div class="filename">' + file.name + '</div>\
                    <div class="folder">\
                        <i class="fa fa-folder-o"></i> ' + opts.slug + '<br />\
                        ' + window.rml.uploader.humanFileSize(file.size, true) + ' - <span class="read_percent">0%</span>\
                    </div>\
                    <div class="bar">\
                        <div class="percent"></div>\
                    </div>\
                    ' + opts.deny + '\
                </div>\
                <div class="rml-fix"></div>\
            </div>' ).appendTo( '.rml-uploading-list' );
        
        $(".rml-uploading").show();
    	var image = $( new Image() ).appendTo( item.find(".left") );
    	
    	if ($(".rml-hide-upload-preview-1").size() == 0) {
        	var preloader = new mOxie.Image();
        	preloader.onload = function() {
        	    preloader.downsize( 48, 48 );
        	    image.prop( "src", preloader.getAsDataURL() );
        	};
        	preloader.load( file.getSource() );
    	}
    },
    
    // @link http://stackoverflow.com/questions/10420352/converting-file-size-in-bytes-to-human-readable
    humanFileSize: function(bytes, si) {
        var thresh = si ? 1000 : 1024;
        if(Math.abs(bytes) < thresh) {
            return bytes + ' B';
        }
        var units = si
            ? ['kB','MB','GB','TB','PB','EB','ZB','YB']
            : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
        var u = -1;
        do {
            bytes /= thresh;
            ++u;
        } while(Math.abs(bytes) >= thresh && u < units.length - 1);
        return bytes.toFixed(1)+' '+units[u];
    },
    
    secondsFormat: function(totalSec) {
        var hours   = Math.floor(totalSec / 3600);
        var minutes = Math.floor((totalSec - (hours * 3600)) / 60);
        var seconds = totalSec - (hours * 3600) - (minutes * 60);
        
        var result = (hours < 10 ? "0" + hours : hours) + ":" + (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds  < 10 ? "0" + seconds : seconds);
        return result;
    }
};

/**
 * Update the reamin info for the total upload progress:
 * - bytes per seconds
 * - loaded / total size
 * - remain time
 * 
 * @hooks uploader/UploadProgress
 */
window.rml.hooks.register("uploader/UploadProgress", function(uploader, file) {
    jQuery(".rml-uploading-details-remain-bytes strong")
        .html(window.rml.uploader.humanFileSize(uploader.total.bytesPerSec, true));
        
    jQuery(".rml-uploading-details-remain-loaded strong")
        .html(window.rml.uploader.humanFileSize(uploader.total.loaded, true));
    jQuery(".rml-uploading-details-remain-loaded span")
        .html(window.rml.uploader.humanFileSize(uploader.total.size, true));
    
    var remainTime = Math.floor((uploader.total.size - uploader.total.loaded) / uploader.total.bytesPerSec);
    jQuery(".rml-uploading-details-remain-time strong").html(window.rml.uploader.secondsFormat(remainTime));
});
/* global RMLisDefined RMLWpIs rmlOpts jQuery localStorage wp navigator RMLisAIO */

/**
 * Library handler
 */
window.rml.library = {
    attachments: {},
    lastIdInView: false,
    gridViewMediaFrame: false,
    
    /**
     * Update the restrictions in the toolbar.
     */
    updateRestrictions: function(obj, container) {
    	var restrictions = obj.attr("data-restrictions"),
    		warningFa = container.find(".aio-toolbar-placeholder > .fa-warning"),
    		langs = rmlOpts.lang.toolbarItems.restrictions;
    	restrictions = restrictions.length > 0 ? restrictions.split(",") : [];
    	if (restrictions.length > 0) {
    		// There are restrictions
    		var ul = "<ul>", i, langStringFound, inherits, inheritsCnt = 0;
    		jQuery.each(langs, function(key, value) {
    			langStringFound = false;
    			inherits = false;
    			
    			// Search if the restriction is available
    			for (var i = 0; i < restrictions.length; i++) {
    				if (restrictions[i].indexOf(key) === 0) {
    					langStringFound = true;
    					
    					if (restrictions[i].slice(-1) === ">") {
    						inherits = true;
    						inheritsCnt++;
    					}
    					
    					break;
    				}
    			}
    			
    			// Build the string!
    			if (langStringFound) {
    				ul += "<li>" + value;
    				if (inherits) {
    					ul += " <b>*</b>";
    				}
    				ul += "</li>";
    			}
    		});
    		ul += "</ul>";
    		
    		if (inheritsCnt > 0) {
    			ul += "<b>*</b> " + rmlOpts.lang.toolbarItems.restrictionsInherits;
    		}
    		
    		var args = {}; // Make object so we can change the reference!
    		args.text = rmlOpts.lang.toolbarItems.restrictionsSuffix + ul;
    		window.rml.hooks.call("restrictionText", [ args, obj ], container);
    		
    		// Update the warning
    		warningFa.show();
    		warningFa.attr("data-aio-tooltip-text", args.text);
    		container.allInOneTree("reinit", "tooltips");
    	}else{
    		warningFa.hide();
    	}
    },
    
    /**
     * Initialize the media library. Add a filter to the
     * "Insert media" dialog and the media library grid view.
     * 
     * It should only inialized once for the whole page session!
     * 
     * @hook propsForAllAJAX
     * @hook propsForMediaFolderEntryAJAX
     */
    initailizeToolbar: function() {
        if (!RMLWpIs("media") || !rmlOpts.namesSlug) {
    		return;
    	}
    	
    	var wp = window.wp;
    	  
    	// Create Filter
    	wp.media.view.AttachmentFilters.RML = wp.media.view.AttachmentFilters.extend({
    	    className: "attachment-filters attachment-filters-rml",
    	    createFilters: function() {
    	        var filters = { },
    	            names = rmlOpts.namesSlug.names,
            	    types = rmlOpts.namesSlug.types,
            	    slugs = rmlOpts.namesSlug.slugs;
            	
    	        // default "all" filter, shows all tags
    			filters.all = {
    				text:  "All",
    				props: {
    					rml_folder: "",
						rml_type: ""
    				},
    				priority: 10
    			};
    			
    			window.rml.hooks.call("propsForAllAJAX", [ filters.all, rmlOpts.namesSlug ], this);
    			// create a filter for each tag
    			var i, props;
    			for (i = 0;i<names.length;i++) {
    			    props = {
						rml_folder: slugs[i],
						rml_type: types[i]
					};
					
					// Another can add a property?!
					window.rml.hooks.call("propsForMediaFolderEntryAJAX", [ props, rmlOpts.namesSlug ], this);
					
    				filters[slugs[i]] = {
    					// tag name
    					text:  names[i],
    					props: props,
    					priority: 20+i
    				};
    			}
    			this.filters = filters;
    	    }
    	});
    	
    	// Create toolbar with our filter
    	// @source https://blog.handbuilt.co/2016/01/25/add-a-custom-taxonomy-dropdown-filter-to-the-wordpress-media-library/
    	var AttachmentsBrowser = wp.media.view.AttachmentsBrowser;
    	wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
    	    createToolbar: function() {
    	        this.$el.data("backboneView", this);
    	        AttachmentsBrowser.prototype.createToolbar.call(this);
    			this.toolbar.set('rml_folder', new wp.media.view.AttachmentFilters.RML({
    				controller: this.controller,
    				model:      this.collection.props,
    				priority:   -75
    			}).render() );

    			window.rml.hooks.call("createToolbar", false, this);
    			
    			// Event for attachmentsChanged event
    			var timeout; // Create timeout, because the event is called more than once
    			this.collection.on("change reset add remove", function() {
    			    clearTimeout(timeout);
    			    timeout = setTimeout(function() {
    			        window.rml.hooks.call("attachmentsChanged", arguments, this);
    			    }.bind(this), 500);
    			}, this);
    	    }
    	});
    	
    	// Listen to the ajax complete to refresh the folder counts
    	jQuery(document).ajaxComplete(function (e, xhs, req) {
    	    try {
    	        if (req.data.indexOf("action=delete-post") > -1) {
    	            this.refreshAllCounts();
    	        }
    	    }catch(e) {}
    	}.bind(this));
    },

    /**
     * Refresh the position of the current
     * opened sweetAlert dialog.
     */
    sweetAlertPosition: function() {
        var $ = jQuery;
        var modal = $(".sweet-alert.showSweetAlert");
        if (modal.size() > 0) {
            var height = modal.outerHeight();
            modal.stop().animate({ "margin-top": (height / 2 + 8.5) * -1 }, 500);
        }
    },
    
    /**
     * Get the selected folder in the wp media picker
     * dialog. The dialog must be visible and focused.
     * 
     * @return <option>-object or null
     */
    getMediaPickerSelected: function() {
        var $ = jQuery, modal = null, active = null;
        // Get each media modal
        $(".media-modal.wp-core-ui").each(function() {
            if ($(this).parent().is(":visible")) {
                modal = $(this);
            }
        });
        
        if (modal !== null) {
            // Get <select>
            active = modal.find(".attachment-filters.attachment-filters-rml").find(":selected");
        }
        
        return active;
    },
    
    /**
     * Get the node tree in media library depending
     * on the active media picker. If there is no media picker
     * active then get the current.
     * 
     * @return jQuery object
     */
    getObjectOfMediaPickerOrActive: function() {
        var modalActive = this.getMediaPickerSelected();
        if (modalActive !== null) {
            var modalID = modalActive.attr("value");
            return jQuery(".aio-tree-instance").allInOneTree("byId", modalID);
        }else{
            return jQuery(".aio-tree-instance").allInOneTree("active");
        }
    },
    
    /**
     * @see http://stackoverflow.com/questions/19491336/get-url-parameter-jquery-or-how-to-get-query-string-values-in-js
     */
    getUrlParameter: function(sParam, URL) {
        var sPageURL = decodeURIComponent(URL ? URL : window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;
            
        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');
    
            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    },
    
    /**
     * Get information about a folder.
     * 
     * @param fid folder id
     * @param infoType if there is a result, this key will be returned
     * @return null or object
     */
    getFolderInfo: function(fid, infoType) {
        var result = null;
        try {
            jQuery.each(rmlOpts.mce.dirs, function(key, value) {
                if (value.value == fid) {
                    result = value;
                }
            });
        }catch(e) {}
        
        if (typeof infoType !== "undefined" && result !== null) {
            return result[infoType];
        }
        
        return result;
    },
    
    /**
     * Get the browser and backboneView
     * of a given AIO Tree.
     */
    getBackboneOfAIO: function(obj) {
        // Get the attachments browser
        var parentModal = obj.parents(".media-modal"),
            browser, backboneView;
        if (parentModal.size() > 0) {
            browser = parentModal.find(".attachments-browser");
        }else{
            browser = jQuery("#wpbody-content .attachments-browser");
        }
        backboneView = browser.data("backboneView");
        return { browser: browser, view: backboneView };
    },
    
    refreshAllCounts: function() {
        this.refreshCount(jQuery(".aio-tree-instance"));
    },
    
    /**
     * Refresh the counts of folders in tree.
     * 
     * @param container the AIO container
     * @param _cb callback
     */
    refreshCount: function(container, _cb) {
        jQuery.ajax({
            url: rmlOpts.ajaxUrl,
            data: {
                action: "rml_folder_count",
                nonce: rmlOpts.nonces.folderCount
            },
            invokeData: _cb,
            success: function(response) {
                if (response.success) {
                    container.allInOneTree("counts", response.data);
                }
                if (typeof _cb === "function") {
                    _cb(response);
                }
            }
        });
    },
    
    /**
     * Initialize custom lists
     */
    liveCustomLists: false,
    customLists: function() {
        var $ = jQuery;
        $(".rml-root-list.rml-custom-list:not(.rml-custom-init)").each(function() {
            $(this).addClass("rml-custom-init");
            window.rml.hooks.call("customList", $(this));
            if (typeof $(this).attr("id") !== "undefined") {
                window.rml.hooks.call("customList-" + $(this).attr("data-id"), $(this));
            }
        });
        
        // Create live updater for hidden input type
        if (!this.liveCustomLists) {
            $(document).on("click", ".rml-root-list.rml-custom-list a", function(e) {
                var id = $(this).attr("data-id"),
                    list = $(this).parents(".rml-root-list.rml-custom-list");
                list.children("input").val(id);
                list.find("a.active").removeClass("active");
                $(this).addClass("active");
                e.preventDefault();
                return false;
            });
            
            this.liveCustomLists = true;
        }
    },
    
    /**
     * Iterates through a 
     * 
     * @context draggable
     * @param ui the draggable ui object
     * @param container the aio container
     * @param listMode function to iterate over list mode items (<tr>)
     * @param gridMode function to iterate over grid mode items @bind backbone variable
     */
    iterateDraggedItems: function(ui, container, listMode, gridMode) {
        var $ = jQuery;
        if (container.data("isListMode")) {
            // List-mode
            var trs = $('input[name="media[]"]:checked');
            
            // Multiselect
            if (trs.size() > 0) {
                trs.each(function() {
                    listMode($(this).parents("tr"));
                });
            }else{
                // One selected
                listMode(ui);
            }
            
        }else{
            // Grid mode, get the backbone
            var backbone = window.rml.library.getBackboneOfAIO(container),
                selected;
            if (backbone.view) {
                gridMode = gridMode.bind(backbone);
                try {
                    selected = backbone.view.options.selection.models;
                }catch(e){}
                
                // Multiselect
                if (selected && selected.length > 0) {
                    for (var i = 0; i < selected.length; i++) {
                        gridMode(selected[i].attributes);
                    }
                }else{ // Only one attachment is selected, catch the backbone model
                    var id = ui.data("id"),
                        models = backbone.view.collection.models;
                    for (var i = 0; i < models.length; i++) {
                        if (models[i].id == id) {
                            gridMode(models[i].attributes);
                            break;
                        }
                    }
                }
            }
        }
    }
}

/**
 * When a new container is created, please do something on Ajax
 * complete.
 */
window.rml.hooks.register("afterInit/ML", function() {
    window.rml.hooks.register("attachmentsChanged", function() {
        if (RMLisAIO(jQuery(this))) {
            jQuery(this).allInOneTree("reinit", "sticky");
        }
    }.bind(this));
});

/**
 * Refresh the page. Detect if it is in
 * grid mode and refresh via AJAX. Otherwise, refresh
 * full page.
 */
window.rml.hooks.register("refreshView", function($) {
    var backbone = window.rml.library.getBackboneOfAIO($(this));
    if (backbone.browser.size() > 0 && typeof backbone.view == "object") {
        // Refresh the backbone view
        try {
            backbone.view.collection.props.set({ignore: (+ new Date())});
        }catch(e) {console.log(e);};
    }else{
        window.location.reload();
    }
});

/**
 * Initialize the toolbar for grid view's (modal and media library page)
 */
window.rml.hooks.register("ready", function() {
    window.rml.library.initailizeToolbar();
});

/**
 * Rename process
 */
window.rml.hooks.register("renamed", function(folderId, newName, slug, $) {
    $(this).allInOneTree("rename", newName);
    $(this).allInOneTree("active").attr("data-slug", slug);
});

/**
 * There is a new created folder, reload the tree and render the tree.
 * 
 * @hook created
 */
window.rml.hooks.register("createdBeforeRendering", function(args, createResponse, $) {
    $(this).allInOneTree("loader", true);
    $.post(
        rmlOpts.ajaxUrl, 
        {
            'action': 'rml_tree_content',
            'nonce' : rmlOpts.nonces.treeContent
        },
        function(response){
            if (response.success) {
                var ul = $(response.data.nodes);
                // HTML View
                if (ul.size() > 0) {
                    $(this).allInOneTree("toolbarButton", "rename");
                    $(this).allInOneTree("nodesHTML", ul.html());
                    $(this).allInOneTree("loader", false);
                }
                
                // Names slug array
                rmlOpts.namesSlug = response.data.namesSlug;
                
                // add it to the collection as available
                var backbone = window.rml.library.getBackboneOfAIO($(this));
                if (typeof backbone.view === "object") {
                    var RMLFilter = backbone.view.toolbar.get("rml_folder");
                    RMLFilter.createFilters();
                }
                
                // "just" add it to the options as available
                $(".attachment-filters.attachment-filters-rml").append('<option value="' + createResponse.data.id + '">' + args[0] + '</option>');
                
                window.rml.hooks.call("created", [ args, createResponse, response ], $(this));
            }
        }.bind(this)
    );
});

/**
 * Call an action filter to modify the query
 * for the attachments in media grid mode.
 * 
 * @hook modifyMediaGridRequestForFolder
 * @args [0] options
 * @args [1] originalOptions
 * @args [2] jqXHR
 * @args [3] request folder id
 * 
 * @example window.rml.hooks.register("modifyMediaGridRequestForFolder", function(e, args) {});
 */
window.rml.hooks.register("general", function(e, args) {
    jQuery.ajaxPrefilter(function( options, originalOptions, jqXHR ) {
        try {
            if (RMLisDefined(originalOptions.data)
                && RMLisDefined(originalOptions.data.action)
                && RMLisDefined(originalOptions.data.query.rml_folder)) {
                // call hook modifyMediaGridRequestForFolder
                if (originalOptions.data.action == "query-attachments") {
                    window.rml.hooks.call("modifyMediaGridRequestForFolder", [ options, originalOptions, jqXHR, originalOptions.data.query.rml_folder] );
                }
            }
        }catch(e) {}
    });
});

/**
 * Check for an given folder type to modify the ajax request
 * and hook into the method
 * 
 * @hook modifyMediaGridRequestForFolder/Type/{Type}
 */
window.rml.hooks.register("modifyMediaGridRequestForFolder", function(options, originalOptions, jqXHR, fid, $) {
    // Only real folders
    if (fid < 0) {
        return;
    }
    var type = originalOptions.data.query.rml_type;
    if (type) {
        var action = "modifyMediaGridRequestForFolder/Type/" + type;
        window.rml.hooks.call(action, arguments);
    }
});

/**
 * Register handler for resizing. The shadow should be shown if it holds
 * over.
 */
window.rml.hooks.register("afterInit/ML", function($) {
    var container = $(this),
        fixedHeader = container.find(".aio-fixed-header");
    if (fixedHeader.size() > 0) {
        $(document).scroll(function() {
            var reachedOverflow = $(this).scrollTop() + fixedHeader.outerHeight() + fixedHeader.position().top;
            if (reachedOverflow > container.find(".aio-list-standard").offset().top) {
                container.addClass("aio-theme-wordpress-fixed");
                fixedHeader.addClass("aio-reached-overflow").css("min-width", (container.children(".aio-wrap").width() - 10) + "px"); // 10 = padding
            }else{
                container.removeClass("aio-theme-wordpress-fixed");
                fixedHeader.removeClass("aio-reached-overflow").css("min-width", "");
            }
        });
    }
});
window.rml.hooks.register("afterInit/modal", function(menu, modal, $) {
    var container = $(this),
        fixedHeader = container.find(".aio-fixed-header"),
        fixedHeaderHelper = container.find(".aio-fixed-header-helper"),
        headerHeight = fixedHeader.outerHeight();
    if (container.hasClass("aio-theme-wordpress-fixed") && fixedHeader.size() > 0 && fixedHeaderHelper.size() > 0) {
        menu.scroll(function() {
            var modalTop = modal.position().top,
                reachedOverflow = $(this).scrollTop(),
                //toReach = container.find(".aio-list-standard").offset().top;
                toReach = container.find(".aio-list-standard").offset().top + reachedOverflow - modalTop;
            if (reachedOverflow > toReach) {
                fixedHeaderHelper.height(headerHeight);
                container.addClass("aio-theme-wordpress-fixed");
                fixedHeader.addClass("aio-reached-overflow")
                    .css({
                        "min-width": (container.children(".aio-wrap").width() - 10) + "px",
                        "top": modalTop + "px"
                    }); // 10 = padding
            }else{
                container.removeClass("aio-theme-wordpress-fixed");
                fixedHeader.removeClass("aio-reached-overflow").css("min-width", "");
                fixedHeaderHelper.height(0);
            }
        });
    }
});

/**
 * Update restrictions when loading a new folder.
 * 
 * @see window.rml.library.updateRestrictions
 */
window.rml.hooks.register("switchFolder", function(obj, id, type, $) {
	window.rml.library.updateRestrictions(obj, $(this));
});

/**
 * Acceptance for different folder types.
 * 
 * @see default AIO settings options.movement.droppableSettings.accept
 */
window.rml.returnTrue = function() {
    return true;
}
// All, Unorganized and folders acceppt all files
window.rml.typeAccept["3"] = window.rml.returnTrue;
window.rml.typeAccept["4"] = window.rml.returnTrue;
window.rml.typeAccept["0"] = window.rml.returnTrue;
window.rml.typeAccept["1"] = function() { return false; } // A collection never accepts any file
window.rml.typeAccept["2"] = function(ui, container, $) {
    // A gallery accepts only images
    var foundInvalid = false;
    
    window.rml.library.iterateDraggedItems(ui, container, function(tr) {
        if (!tr.find(".media-icon").hasClass("image-icon")) {
            foundInvalid = true;
        }
    }, function(attributes) {
        if (attributes.type != "image") {
            foundInvalid = true;
        }
    });
    return !foundInvalid;
}
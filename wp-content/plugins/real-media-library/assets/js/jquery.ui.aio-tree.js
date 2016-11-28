"use strict";
/* global jQuery */

(function($) {
    /** Function.prototype.bind polyfill */
    Function.prototype.bind=(function(){}).bind||function(b){if(typeof this!=="function"){throw new TypeError("Function.prototype.bind - what is trying to be bound is not callable");}function c(){}var a=[].slice,f=a.call(arguments,1),e=this,d=function(){return e.apply(this instanceof c?this:b||window,f.concat(a.call(arguments)));};c.prototype=this.prototype;d.prototype=new c();return d;};
    
    /** Array.prototype.indexOf polyfill */
    Array.prototype.indexOf||(Array.prototype.indexOf=function(a,b){for(var c=b||0,d=this.length;c<d;c++)if(this[c]===a)return c;return-1});
    
    /**
     * Handler for the collapsable / expandable folder
     * structure. It adds the [+] / [-] buttons to the
     * folders - and also the handlers.
     * 
     * It also handles the switchFolder event.
     * @see TypeHandler::_doSwitchFolder
     */
    var ContainerHandler = {
        /**
         * Parsed Opposite object (jQuery)
         */
        _usedOpposite: null,
        
        /**
         * @see this::_updateCollapsableNodes
         */
        _usedCollapsable: false,
        
        /**
         * Determines, if the localStorage can be used if
         * it should be used. This depends on the id (generated?)
         * and the availability of the localStorage.
         */
        _useCollapsableLocalStorage: false,
        
        /**
         * Initialize with given widget options.
         * 
         * @param e the widget
         */
        init: function(e) {
            // Initialize collapsable
            if (!e._generatedId &&
                    e.options.container.isCollapsable) {
                this._doCollapsable(e);
            }else{
                e._nodes.find("li").addClass("aio-open");
            }
            
            // Initialize resizable container
            this._doResizable(e);
            
            // Initialize the switchFolder event
            $(document).on("click", "#" + e._id + " .aio-list-standard a", function(event) {
                if (typeof e.options.others.onSwitchFolder === "function" && !$(this).hasClass("aio-disable-link")) {
                    return (e.options.others.onSwitchFolder.bind(e.element))($(this), event);
                }
            });
        },
        
        /**
         * Reinit the position of the sticky
         * container or create a new sticky container, if needed.
         * 
         * @see this::_usedSticky
         */
        sticky: function(e) {
            // Initialize sticky container
            if (e.options.container.isSticky) {
                if (typeof $.fn.hcSticky !== "undefined") {
                    if (e.element.data("sticky")) {
                        e.element.hcSticky('reinit');
                        return;
                    }
                    
                    e.element.hcSticky(e.options.container.hcStickySettings);
                    e.element.data("sticky", true);
                }else{
                    throw "If want to use the sticky container please include the HC Sticky jQuery plugin, see http://someweblog.com/hcsticky-jquery-floating-sticky-plugin/ !";
                }
            }
            //e.element.hcSticky('reinit');
        },
        
        /**
         * Initialize the tooltips. This should be done
         * in the end of tree creation. It can also be
         * reinitialized through the reinit method.
         */
        _doTooltips: function(e) {
            var callback = e.options.container.onDoTooltips;
            if (typeof callback === "function") {
                (callback.bind(e))();
            }else{
                // Use tooltipster
                if (typeof $.fn.tooltipster === "function") {
                    var title, text, disabledLinkText, html;
                    $(".aio-tooltip").each(function() {
                        title = $(this).attr("data-aio-tooltip-title");
                        text = $(this).attr("data-aio-tooltip-text");
                        disabledLinkText = $(this).attr("data-aio-tooltip-text-disabled");
                        if ($(this).hasClass("aio-disable-link")) { // Use the disabled text in tooltip
                            text = disabledLinkText;
                        }
                        
                        // Do the tooltip
                        if (title.length > 0 || text.length > 0) {
                            html = (title.length > 0 ? '<div class="aio-tooltip-title">' + title + '</div>' : "") + '<div class="aio-tooltip-text">' + text + "</p>";
                            if ($(this).hasClass("tooltipstered")) {
                                try {
                                    $(this).tooltipster("destroy");
                                } catch (e) { }
                            }
                            $(this).tooltipster($.extend({
                                content: html,
                                contentAsHTML: true,
                                theme: "tooltipster-aio",
                                animation: "grow",
                                maxWidth: 225
                            }, e.options.container.tooltipsterSettings));
                        }
                    });
                }else{
                    throw "Please include tooltipster jQuery plugin (http://iamceege.github.io/tooltipster/)";
                }
            }
        },
        
        /**
         * Initialize the collapsable tree nodes.
         */
        _doCollapsable: function(e) {
            this._usedCollapsable = true;
            this._useCollapsableLocalStorage = e._localStorage && e.options.container.collapsableLocalstorage;
            this._updateCollapsableNodes(e);
            var _useCollapsableLocalStorage = this._useCollapsableLocalStorage;
            
            // Event handler for the expander
            $(document).on("click", "#" + e._id + " .aio-expander", function() {
                var isOpen = $(this).hasClass("aio-open"),
                    removeClass = isOpen ? "aio-open" : "aio-close",
                    addClass = isOpen ? "aio-close" : "aio-open",
                    status = addClass == "aio-open" ? "1" : "0";
                $(this).removeClass(removeClass).addClass(addClass);
                $(this).parent().removeClass(removeClass).addClass(addClass);
                
                // Save status in localStorage
                var aLink = $(this).parent().children("a");
                if (typeof e.options.others.onExpand === "function") {
                    (e.options.others.onExpand.bind(e.element))(status, $(this).parent().children("a"));
                }
                
                if (_useCollapsableLocalStorage && e._hasAttr("data-aio-id", aLink)) {
                    var id = aLink.attr("data-aio-id");
                    window.localStorage.setItem('AIO' + e._id + '-expand-' + id, status);
                }
            });
        },
        
        /**
         * Put a [+] or [-] icon to the folders.
         * This function is only called, if _doCollapsable
         * is called once. Call this function also when
         * you add a new node or delete one.
         * 
         * @param e the widget
         * @return boolean if update was performed
         */
        _updateCollapsableNodes: function(e) {
            if (this._usedCollapsable !== true) {
                return false;
            }
            
            var expander, treeClass, treeStatus, aLink, id,
                _useCollapsableLocalStorage = this._useCollapsableLocalStorage;
            e._nodes.find(".aio-expander").remove();
            e._nodes.find("li").each(function() {
                if ($(this).children("ul").children("li").size() > 0) {
                    // Reset all
                    treeStatus = undefined;
                    treeClass = "aio-close";
                    
                    aLink = $(this).children("a");
                    if (_useCollapsableLocalStorage && e._hasAttr("data-aio-id", aLink)) {
                        id = aLink.attr("data-aio-id");
                        treeStatus = window.localStorage.getItem("AIO" + e._id + "-expand-" + id);
                        if (treeStatus == "1") {
                            treeClass = "aio-open";
                        }else if (treeStatus == "0"){
                            treeStatus = "aio-close";
                        }
                    }
                    
                    if (!e._defined(treeStatus)) { // Set default if not set
                        treeClass = 'aio-' + e.options.container.collapsableDefaultStatus;
                    }
                    $(this).addClass(treeClass);
                    expander = $("<div class=\"aio-expander " + treeClass + "\"><i class=\"fa fa-minus-square-o\"></i><i class=\"fa fa-plus-square-o\"></i></div>").appendTo($(this));
                }
            });
            
            return true;
        },
        
        /**
         * Initialize the resize handler button and add
         * document listeners to the resizeable button.
         * 
         * @param e jQuery widget
         */
        _doResizable: function(e) {
            var touchable = e._isTouch(), cOptions = e.options.container,
                /** 
                 * Function to get the current width of the container
                 * depending on the max size
                 */
                saveWidth = function() {
                    var saveWidth = e.element.width();
                    if (saveWidth < e.options.container.resizeMin) {
                        saveWidth = e.options.container.resizeMin;
                    }
                    return saveWidth > e.options.container.resizeMax ? e.options.container.resizeMax : saveWidth;
                };

            // On Touch devices?
            var resizeButton = $('<div class="aio-split-resize"></div>').prependTo(e.element.children(".aio-wrap"));
            if (!(!e._generatedId &&
                    e.options.container.isResizable &&
                    (e._localStorage || e.options.container.resizeLocalStorage == false))
                || !cOptions.resizeTouch && touchable.touch) {
                resizeButton.addClass("aio-hide");
                return;
            }
            
            // Get width
            var lWidth = window.localStorage.getItem("AIO" + e._id + "-width");
            if (!e._defined(lWidth)) {
                lWidth = cOptions.defaultWidth;
            }
            if (lWidth < cOptions.resizeMin) {
                lWidth = cOptions.resizeMin;
            }
            
            // Get opposite to element (or handler)
            var oppositeHandler = null;
            if (typeof cOptions.onResizeOpposite === "function") {
                oppositeHandler = cOptions.onResizeOpposite;
            }else{
                // Resize element
                var opposite = e._parseElements(cOptions.resizeOpposite);
                if (!e._defined(opposite) || opposite.size() == 0) {
                    throw "If you want to resize the container, you need an opposite site! (options.container.resizeOpposite)";
                }
                opposite = opposite.first();
                this._usedOpposite = opposite;
                
                // Create unique ID for the opposite
                var guid = e._idGenerate(opposite);
                
                // Create default opposite handler
                oppositeHandler = function(width) {
                    if (cOptions.resizeOppositeMin > 0 && opposite.width() < cOptions.resizeOppositeMin + 1)
                        return false;
                    
                    // Handle resize
                    try {
                        e._injectStyle("opposite", "#" + guid + "{width: -webkit-calc(100% - " + width + "px) !important;width: -moz-calc(100% - " + width + "px) !important;width: calc(100% - " + width + "px) !important;}");
                    } catch (e) {
                        return false;
                    }
                };
            }

            // We need an oppositeHandler
            if (oppositeHandler === null) {
                throw "There is no opposite handler!";
            }
            
            // Create handler for resizing
            var resizeX = 0, container = e.element;
            var resizeHandler = typeof cOptions.customResizeHandler === "function" ? cOptions.customResizeHandler : function(ev) {
                ev.preventDefault();
                var min = cOptions.resizeMin,
                    max = cOptions.resizeMax,
                    x = ev.pageX - container.offset().left;
                
                if (x >= min && x <= max) {
                    resizeX = x;
                    if (oppositeHandler(resizeX) !== false) {
                        container.css("width", resizeX + "px");
                        if (container.data("sticky")) {
                            container.parent().css("width", resizeX + "px");
                        }
                    }
                }
            };
            resizeHandler = resizeHandler.bind(e.element);
            oppositeHandler = oppositeHandler.bind(e.element);
            
            // Add handler to the resize button
            var currentlyResizing = false;
            resizeButton.on(touchable.down, function(ev) {
                ev.preventDefault();
                $(document).on(touchable.move, resizeHandler);
                currentlyResizing = true;
            });
            
            $(document).on(touchable.up, function(ev) {
                ev.preventDefault();
                $(document).unbind(touchable.move, resizeHandler);
                
                // Save in localStorage
                if (currentlyResizing) {
                    if (cOptions.resizeLocalStorage && e._localStorage) {
                        window.localStorage.setItem("AIO" + e._id + "-width", saveWidth());
                    }
                    
                    // Call finished callback
                    if (typeof cOptions.onResizeFinished === "function") {
                        (cOptions.onResizeFinished.bind(e.element))(e.element.width());
                    }
                }
                currentlyResizing = false;
            });
            
            // Call the resize once
            var initHandler = typeof cOptions.onResizeInit === "function" ? cOptions.onResizeInit : function(width) {
                container.css("width", width + "px");
            };
            initHandler = initHandler.bind(e.element);
            
            if (oppositeHandler(lWidth) !== false) {
                initHandler(lWidth);
            }
            
            resizeButton.show();
        }
    };
    
    /**
     * Handler for the drag and drop movement.
     * This defines a selector for the draggable items
     * and the droppable folders.
     * 
     * If you use your own MovementHandler implement the following functions:
     * reinit, state
     */
    var MovementHandler = {
        currentState: true,
        currentStateText: "enable",
        
        /**
         * Initialize with given widget options.
         * 
         * @param e the widget
         */
        init: function(e) {
            this._doDraggable(e.options.movement, e);
            this._doDroppable(e.options.movement, e);
        },
        
        /**
         * Reinitialize the drag and drop. 
         * 
         * @param e the widget
         */
        reinit: function(e) {
            // Reinitialize draggable with the current movement state
            this._doDraggable(e.options.movement, e);
            this._doDroppable(e.options.movement, e);
        },
        
        /**
         * Disable or enable the drag & drop.
         * 
         * @param e the widget
         * @param state boolean the state of the movement
         */
        state: function(e, state) {
            this.currentState = state;
            this.currentStateText = state ? "enable" : "disable";
            this.reinit(e);
        },
        
        /**
         * Initialize the draggable.
         * 
         * @param movement the movement options
         * @param e the widget
         */
        _doDraggable: function(movement, e) {
            var onGetLabel = typeof movement.onGetLabel === "function" ? movement.onGetLabel : this.draggableLabel,
                onGetHelper = typeof movement.onGetHelper === "function" ? movement.onGetHelper : this.draggableHelper;
            
            onGetLabel = onGetLabel.bind(e.element);
            onGetHelper = onGetHelper.bind(e.element);
            var draggableSettings = {
                revert: 'invalid',
                appendTo: 'body',
                cursorAt: { top: 0, left: 0 },
                distance: 10,
                scrollSensitivity: 50,
                scrollSpeed: 50,
                refreshPositions: true,
                helper: function() {
                    // Draggable label
                    var label = onGetLabel(this, arguments);
                    return onGetHelper(label, this, arguments);
                },
                start: function() {
                    e._standardList.addClass("aio-currently-dragging");
                },
                stop: function() {
                    e._standardList.removeClass("aio-currently-dragging");
                }
            };
            $.extend(draggableSettings, movement.draggableSettings);
            $(movement.selector).draggable(draggableSettings).draggable(this.currentStateText);
        },
        
        /**
         * Initialize the droppable.
         * 
         * @param movement the movement options
         * @param e the widget
         */
        _doDroppable: function(movement, e) {
            e._standardList.find("a").droppable($.extend({
                activeClass: "ui-state-default",
                hoverClass: "ui-state-hover",
                tolerance: "pointer"
            }, movement.droppableSettings)).droppable(this.currentStateText);
        },
        
        /**
         * Return a label for the movement, by default only print out "Move".
         * There is also a font-awesome icon.
         */
        draggableLabel: function() {
            return '<i class="fa fa-arrow-right" style="margin-right:5px;"></i> Move';
        },
        
        /**
         * Create a DIV container for the draggable helper.
         * 
         * @param draggableLabel @see this::draggableLabel
         */
        draggableHelper: function(draggableLabel) {
            return $('<div class="aio-movement-helper">' + draggableLabel + '</div>');
        }
    };
    
    /**
     * Loader handler for the toolbar.
     */
    var LoaderHandler = {
        container: null,
        
        /**
         * Add a handler container to the toolbar.
         * 
         * @param e the widget
         */
        init: function(e) {
            this.container = $('<div class="aio-tree-loader"></div>').appendTo(e._toolbar);
        },
        
        /**
         * Allow multiple arguments to allow
         * $(...).allInOneTree("loader", "show");
         * $(...).allInOneTree("loader", "hide");
         * $(...).allInOneTree("loader", true);
         * $(...).allInOneTree("loader", false);
         * $(...).allInOneTree("loader", "89"); => 89%
         * $(...).allInOneTree("loader", 50, 500); => 50 / 500 * 100 = 10%
         */
        fromArguments: function(args) {
            var percentage;
            if (args.length === 1) {
                if (args[0] == "show" || args[0] == true) {
                    percentage = 100;
                }else if (args[0] == "hide" || args[1] == false) {
                    percentage = 0;
                }else{
                    percentage = args[0];
                }
            }else if (args.length === 2) {
                percentage = args[0] / args[1] * 100;
            }
            this.change(percentage);
        },
        
        /**
         * Change the visibility of the loader.
         * If percentage is <= 0, then the loader
         * is not visible. Otherwise it is shown.
         */
        change: function(percentage) {
            if (percentage > 0) {
                this.container.parents(".aio-tree").addClass("loading");
                this.container.css("width", percentage + "%");
            }else{
                this.container.parents(".aio-tree").removeClass("loading");
                this.container.css("width", "0px");
            }
        }
    };
    
    /**
     * Toolbar handler
     */
    var ToolbarHandler = {
        itemsContainer: null,
        widget: null, /** Here we save the widget, because it is needed in the predefined functions */
        
        /**
         * Initialize the toolbar items
         */
        init: function(e) {
            this.widget = e;
            this.itemsContainer = e._toolbar.find(".aio-toolbar-items");
            
            this._iterateButtons(e, function(button) {
                this._doCreateLink(button, e);
            }.bind(this));
        },
        
        /**
         * Create a link for each create type
         */
        _doCreateLink: function(button, e) {
            // Check if link is already available
            if (this.itemsContainer.find('a[data-aio-name="' + button.name + '"]').size() > 0) {
                return;
            }
            
            var classes = typeof button.cssClasses === "string" ? button.cssClasses : "";
                classes = classes + ((typeof button.usePredefinedFunction == "string") ? " aio-predefined-" + button.usePredefinedFunction : "");
            var labelBack = button.labelBack || e.options.toolbarButtons.labelBack;
            var visibleInActiveFolderType = typeof button.visibleInActiveFolderType === "object" && button.visibleInActiveFolderType.length > 0 ? button.visibleInActiveFolderType.join(";") : ":",
                html = '<a href="#" class="' + classes + ' aio-tooltip" style="display:none;" data-aio-name="' + button.name + '" data-aio-allowed="' + visibleInActiveFolderType + '" data-aio-name="' + button.name + '" data-aio-tooltip-title="' + (typeof button.toolTipTitle !== "undefined" ? button.toolTipTitle : "") + '" data-aio-tooltip-text-disabled="' + (typeof button.toolTipTextDisabledLink !== "undefined" ? button.toolTipTextDisabledLink : "") + '" data-aio-tooltip-text="' + (typeof button.toolTipText !== "undefined" ? button.toolTipText : "") + '">' + button.content + '<span class="aio-toolbar-back">' + labelBack + '</span></a>',
                link = $(html).appendTo(this.itemsContainer);
            
            // Save the data
            button.element = link;
            link.data("aio-link", button);
            
            // Click handler
            var that = this;
            link.click(function(event) {
                var active = e.active(),
                    isCancelActive = $(this).hasClass("isCancelActive"),
                    isCreateNew = active.size() > 0 && active.attr("data-aio-id") == "AIO_NEW_TO_CREATE",
                    setToActive = false;
                    
                // Custom allowed function
                if (typeof button.onAllowed === "function" && !isCancelActive) {
                    var result = (button.onAllowed.bind(e.element))(e, $(this));
                    if (result === false) {
                        event.preventDefault();
                        return false;
                    }
                }
                 
                // Disable link
                if ($(this).hasClass("aio-disable-link") && !isCreateNew) {
                    event.preventDefault();
                    return false;
                }
                
                // Perhaps it is a new creating folder?
                if (isCreateNew) {
                    if (isCancelActive) {
                        e.element.removeClass("aio-prepare-create-new");
                        
                        // Is there any folder, that was active before?
                        var _activeBeforeCreateForm = e._typesHandler._activeBeforeCreateForm;
                        if (_activeBeforeCreateForm != null && typeof _activeBeforeCreateForm === "object") {
                            setToActive = _activeBeforeCreateForm;
                        }
                        e._typesHandler._activeBeforeCreateForm = null;
                    }else{
                        e.element.addClass("aio-prepare-create-new");
                    }
                }
                
                // Predefined function?
                if (typeof button.usePredefinedFunction == "string") {
                    if (typeof that["_doPredefined" + button.usePredefinedFunction] === "function") {
                        that["_doPredefined" + button.usePredefinedFunction](e, $(this), isCancelActive, button);
                        // Disable types handler
                        e._typesHandler.state(isCancelActive);
                    }
                }
                
                // Custom onCancel function
                if (typeof button.onCancel === "function") {
                    if (isCancelActive) {
                        that.itemsContainer.add($(this)).removeClass("isCancelActive");
                        (button.onCancel.bind(e.element))($(this));
                    }else{
                        that.itemsContainer.add($(this)).addClass("isCancelActive");
                    }
                    
                    // Disable types handler
                    e._typesHandler.state(isCancelActive);
                }
                
                // Custom onClick function
                if (typeof button.onClick === "function" && !isCancelActive) {
                    (button.onClick.bind(e.element))(e, $(this));
                }
                
                // Reset to before active (when creating new folder)
                if (e._defined(setToActive)) {
                    e.active(setToActive);
                }

                event.preventDefault();
                return false;
            });
        },
    
        /**
         * PREDEFINED FUNCTION: Rename
         * It also includes the creating callback
         */
        _doPredefinedrename: function(e, obj, isCancelActive, button) {
            var a = e.active(),
                name = a.find(".aio-node-name"), input, submitButton,
                isCreateNew = a.size() > 0 && a.attr("data-aio-id") == "AIO_NEW_TO_CREATE",
                onSave = function() {
                    var newName = input.val(),
                        callbackOnSave = isCreateNew ? e.options.others.onCreateFolder : button.onSave;
                    if (typeof callbackOnSave === "function") {
                        callbackOnSave = (callbackOnSave.bind(e.element));
                        if (isCreateNew) {
                            callbackOnSave(newName, a.data("createType"), a.data("createToParent"), a);
                        }else{
                            callbackOnSave(a.attr("data-aio-id"), newName);
                        }
                    }else{
                        throw "If you want to use the rename/create function please define an onSave/onCreateFolder action!";
                    }
                }.bind(e);
                
            if (name.css("visibility") !== "hidden") {
                // Create input and button
                name.css("visibility", "hidden");
                e.nodes(false); // Deactive node links
                this.itemsContainer.add(obj).addClass("isCancelActive");
                input = $('<input type="text"/>').insertAfter(a);
                submitButton = $('<button class="aio-rename-submit">' + e.options.others.labelRenameSave + '</button>').insertAfter(input);
                input.val(name.html()).focus();
                
                // Add event handlers to input and button
                input.keypress(function(e) {
                    if(e.which == 13) {
                        onSave();
                    }
                });
                submitButton.on("click", function(e) {
                    onSave();
                    e.preventDefault();
                    return false;
                });
                
            }else{
                // Disable renaming
                name.css("visibility", "visible");
                e.nodes(true);
                this.itemsContainer.add(obj).removeClass("isCancelActive");
                a.parent().children("input,button.aio-rename-submit").remove();
            }
        },
        
        /**
         * PREDEFINED FUNCTION: Rearrange
         */
        _doPredefinedrearrange: function(e, obj, isCancelActive) {
            if (typeof $.fn.nestedSortable === "function") {
                var nodesUL = e._nodes.children("ul"),
                    saveButton = e.element.find(".aio-rearrange-save"),
                    onRearrangeSave = e.options.container.onRearrangeSave,
                    onRearrangeCancel = e.options.container.onRearrangeCancel;
                if (isCancelActive) {
                    // Go back to normal mode
                    this.itemsContainer.add(obj).removeClass("isCancelActive");
                    e._standardList.children("a").show();
                    e.element.removeClass("aio-rearrange");
                    nodesUL.sortable("destroy");
                    nodesUL.data("nestedSortable", false);
                    saveButton.stop().hide();
                    
                    // Repaste the HTML
                    var nodesHTML = nodesUL.data("nodesHTML");
                    nodesUL.html(nodesHTML);
                    
                    // The <a>'s should a href!
                    e.nodes(true);
                    
                    // Change state of draggable and droppable to enabled
                    e.movement(true);
                    
                    if (typeof onRearrangeCancel === "function") {
                        (onRearrangeCancel.bind(e.element))(e, event);
                    }
                    
                }else{
                    // Go to the rearrange mode
                    e.element.addClass("aio-rearrange");
                    this.itemsContainer.add(obj).addClass("isCancelActive");
                    e._standardList.children("a").hide();
                    nodesUL.data("nodesHTML", nodesUL.html());
                    
                    // Add a rearrange save button (if not exists)
                    if (saveButton.size() <= 0) {
                        saveButton = $('<button class="aio-rearrange-save button button-primary" style="display:none;">' + e.options.others.labelRearrangeSave + '</button>').insertAfter(e._toolbar);
                        var that = this;
                        /**
                         * Click handler for the save rearranged order button.
                         * 
                         * @see options.container.onRearrangeSave
                         */
                        saveButton.click(function(event) {
                            if (typeof onRearrangeSave === "function") {
                                (onRearrangeSave.bind(e.element))
                                    (that._doPredefinedrearrangeParse(nodesUL), nodesUL.html(), $(this), event);
                            }
                            
                            event.preventDefault();
                            return false;
                        });
                    }
                    saveButton.show();
                    
                    // Change state of draggable and droppable to disabled
                    e.movement(false);
                    
                    // The <a>'s should not contain a href!
                    e.nodes(false);
                    
                    // Make the list nested sortable
                    this._doPredefinedrearrangeActivate(nodesUL, e);
                    nodesUL.data("nestedSortable", true);
                }
            }else{
                throw "Please include nestedSortable jQuery plugin (https://github.com/ilikenwf/nestedSortable)";
            }
        },
        
        /**
         * Use this function to make the nestedSortable really
         * sortable!
         * 
         * @see this:_doPredefinedrearrange
         */
        _doPredefinedrearrangeActivate: function(nodesUL, e) {
            nodesUL.nestedSortable($.extend({
                handle: 'a[data-aio-href]',
                items: 'li',
                listType: 'ul',
                tolerance: "pointer",
                toleranceElement: '> a',
                helper:	'clone',
                forceHelperSize: true,
                forcePlaceholderSize: true,
                doNotClear: true,
                isAllowed: function(placeholder, placeholderParent, currentItem) {
                    var classNameRestrict = "aio-restrict-hierarchical-change", result = true;
                    // Check if current restricted
                    if (currentItem.parents("." + classNameRestrict).size() > 0) {
                        // Same level is allowed
                        var id = currentItem.attr("id");
                        if (placeholder.parent().children("#" + id).size() === 0) {
                            result = false;
                        }
                    }
                    
                    // Check if new destination is restricted
                    if (placeholder.parents("." + classNameRestrict).size() > 0) {
                        result = false;
                    }
                    return result;
                }
            }, e.options.container.nestedSortableSettings));
        },
        
        /**
         * Parses the nestedSortable to an object array.
         * 
         * @param obj the nestedSortable object
         * @return { raw: original from nestedSortable, parsed: arraid for FID and PID }
         * @see this:_doPredefinedrearrange
         */
        _doPredefinedrearrangeParse: function(obj) {
            var arraied = obj.nestedSortable('toArray', {
                startDepthCount: 0,
                onGetId: function(obj, depth, _left, o) {
                    var id = obj.children("a").attr("data-aio-id");
                    return id;
                }
            });
            
            arraied.shift();
            var raw = [];
            if (typeof arraied === "object" && arraied.length > 1) {
                $.each(arraied, function(k, v) {
                    if (v.parent_id == null) {
                        v.parent_id = -1;
                    }
                    raw.push({fid: v.id, pid: v.parent_id});
                });
            }
            
            return { raw: arraied, parsed: raw };
        },
        
        /**
         * Iterate through the toolbar buttons and execute a given callback.
         */
        _iterateButtons: function(e, callback) {
            var buttons = e.options.toolbarButtons.items;
            if (typeof buttons === "object" || buttons.length > 0) {
                // Iterate the create types
                var button, name;
                for (var i = 0; i < buttons.length; i++) {
                    button = buttons[i];
                    
                    // Validate
                    name = button.name;
                    if (!e._defined(name)) {
                        console.error("Toolbar button must have a name.");
                        continue;
                    }

                    if (callback(button, e) === false) {
                        break;
                    }
                }
            }
        },
    };
    
    /**
     * Handler for the create types. It creates
     * the buttons based on the options.
     * 
     * @see options.createTypes
     */
    var TypesHandler = {
        /**
         * Here are the links for the createTypes saved
         */
        _linkContainer: false,
        
        /**
         * Timeout for the animation of createTypes
         */
        _animationTimeout: undefined,
        
        /**
         * That is the active node before a new one is created
         */
        _activeBeforeCreateForm: null,
        
        /**
         * Initialize the create types and create
         * <a> links.
         * 
         * @param e the widget
         */
        init: function(e) {
            this._iterateCreateTypes(e, function(target) {
                this._doCreateLink(target, e);
            }.bind(this));
            
            // Create a "Cancel" button
            if (this._linkContainer != false) {
                var html = '<a href="javascript:void();" class="' + e.options.createCancel.cssClasses + ' aio-cancel-create-new">' + e.options.createCancel.label + '</a>';
                var link = $(html).appendTo(this._linkContainer);
                link.click(function(event) {
                    e.toolbarButton("rename");
                    e._nodes.find(".aio-create-new").parent().remove(); // Remove the input field
                    
                    event.preventDefault();
                    return false;
                });
            }
        },
        
        /**
         * Enable or disbale the link container buttons
         * 
         * @param boolean state
         */
        state: function(state) {
            if (typeof this._linkContainer === "object") {
                if (state) {
                    this._linkContainer.removeClass("aio-disable-all");
                }else{
                    this._linkContainer.addClass("aio-disable-all");
                }
            }
        },
        
        /**
         * Create a link for each create type
         */
        _doCreateLink: function(target, e) {
            // Check if link is already available
            var linkContainer = e.element.find(".aio-tree-headline");
            this._linkContainer = linkContainer;
            if (linkContainer.find('a[data-aio-name="' + target.name + '"]').size() > 0) {
                return;
            }
            
            var classes = typeof target.cssClasses === "string" ? target.cssClasses : "",
                visibleInActiveFolderType = typeof target.visibleInActiveFolderType === "object" && target.visibleInActiveFolderType.length > 0 ? target.visibleInActiveFolderType.join(";") : ":",
                html = '<a href="#" class="' + classes + ' aio-tooltip" style="display:none;" data-aio-name="' + target.name + '" data-aio-allowed="' + visibleInActiveFolderType + '" data-aio-tooltip-title="' + (typeof target.toolTipTitle !== "undefined" ? target.toolTipTitle : "") + '" data-aio-tooltip-text="' + (typeof target.toolTipText !== "undefined" ? target.toolTipText : "") + '">' + target.label + '</a>',
                link = $(html).appendTo(linkContainer);
            
            // Click handler for this link
            link.data("createType", target);
            link.click(function(event) {
                if ($(this).hasClass("aio-disable-link")) {
                    event.preventDefault();
                    return false;
                }
                
                this._doCreateForm($(this), e, target);
                
                event.preventDefault();
                return false;
            }.bind(this));
            
            // Save the data
            link.data("aio-link", target);
        },
        
        /**
         * Click handler for the create form
         */
        _doCreateForm: function(obj, e, target) {
            // Check, where it should be appended
            var active = e.active(), parentUL, parentID = -1;
            if (active.size() <= 0 || active.parents(".aio-nodes").size() <= 0) { 
                parentUL = e._nodes.children("ul");
            }else{
                parentUL = active.parent().children("ul");
                parentID = active.attr("data-aio-id");
                // Expand it
                var expander = active.parent().children(".aio-expander");
                if (expander.size() > 0) {
                    if (expander.hasClass("aio-close")) {
                        expander.click();
                    }
                }else{
                    active.parent().addClass("aio-open");
                }
            }
            if (active.size() > 0) {
                this._activeBeforeCreateForm = active;
            }
            
            // Create the form
            var li = $('<li>\
	            <a href="javascript:void(0)" class="aio-create-new" data-aio-type="" data-aio-id="AIO_NEW_TO_CREATE" data-aio-href="">\
            		' + target.icon + '\
            		<div class="aio-node-name"></div>\
            		<span class="aio-cnt aio-cnt-0">0</span>\
            	</a>\
            	<ul></ul>\
            </li>').appendTo(parentUL);
            li.children("a").data("createType", target);
            li.children("a").data("createToParent", parentID);
            e.active("AIO_NEW_TO_CREATE");
            e.toolbarButton("rename");
        },
        
        /**
         * Iterate through the create types and execute a given callback.
         */
        _iterateCreateTypes: function(e, callback) {
            var createTypes = e.options.createTypes;
            if (typeof createTypes === "object" || createTypes.length > 0) {
                // Iterate the create types
                var type, name;
                for (var i = 0; i < createTypes.length; i++) {
                    type = createTypes[i];
                    
                    // Validate
                    name = type.name;
                    if (!e._defined(name)) {
                        console.error("Create type must have a name.");
                        continue;
                    }

                    callback(type, e);
                }
            }
        },
        
        /**
         * This functions shows or hides the createTypes
         * for the given folder type.
         */
        _doSwitchFolder: function(e, obj, animationDuration) {
            var allowed, objType = obj.attr("data-aio-type"), show,
                animationArr = { show: [], hide: [] };
            if (!animationDuration) {
                animationDuration = 300;
            }
            
            // Get searchable items which
            var items = typeof this._linkContainer === "object" ? this._linkContainer.find("a") : false,
                toolbarItems = e._toolbar.find(".aio-toolbar-items").find("a");
            items = toolbarItems.add(items);
                
            items.each(function() {
                if ($(this).hasClass("aio-cancel-create-new")) {
                    return true;
                }
                
                allowed = $(this).attr("data-aio-allowed");
                allowed = allowed === ":" ? [] : allowed.split(";");
                show = false;
                
                if (allowed.length > 0) {
                    if (allowed.indexOf(objType) > -1) {
                        show = true;
                    }
                }else{
                    show = true;
                }
                animationArr[show ? "show" : "hide"].push($(this));
            });
            
            var currentItem;
            var showFn = function() {
                if (animationArr.show.length > 0) {
                    for (var i = 0; i < animationArr.show.length; i++) {
                        currentItem = animationArr.show[i].stop();
                        if (currentItem.parent().hasClass("aio-toolbar-items")) {
                            // "Enable" toolbar items only
                            currentItem.removeClass("aio-disable-link");
                        }else{
                            animationArr.show[i].stop().fadeIn(animationDuration).removeClass("aio-hidden");
                        }
                    }
                }
                e.reinit("tooltips");
            }.bind(this);
            
            // Do the animation
            if (animationArr.hide.length > 0) {
                for (var i = 0; i < animationArr.hide.length; i++) {
                    currentItem = animationArr.hide[i].stop();
                    if (currentItem.parent().hasClass("aio-toolbar-items")) {
                        // "Disable" toolbar items only
                        currentItem.addClass("aio-disable-link");
                    }else{
                        currentItem.fadeOut(animationDuration).addClass("aio-hidden");
                    }
                }
                if (e._defined(this._animationTimeout)) {
                    clearTimeout(this._animationTimeout);
                }
                this._animationTimeout = setTimeout(function() {
                    showFn();
                }.bind(this), animationDuration > 0 ? animationDuration + 50 : 0);
            }else{
                showFn();
            }
            
        }
    };
    
    /**
     * The widget
     */
    var AIOTree = {
        /**
         * ID of the DOM element.
         * Build your document listeners to this ID.
         */
        _id: "",
        
        /**
         * Is the id generated or had the DOM
         * element already an ID?
         */
        _generatedId: false,
        
        /**
         * Local storage available?
         */
        _localStorage: false,
        
        /**
         * The used movement handler
         */
        _movementHandler: null,
        
        /**
         * The used type handler
         */
        _typesHandler: null,
        
        /**
         * The used container handler
         */
        _containerHandler: null,
        
        /**
         * The used loader handler
         */
        _loaderHandler: null,
        
        /**
         * The used toolbar handler
         */
        _toolbarHandler: null,
        
        /**
         * .aio-list-standard container
         */
        _standardList: null,
        
        /**
         * .aio-nodes container
         */
        _nodes: null,
        
        /**
         * .aio-toolbar container
         */
        _toolbar: null,
        
        /**
         * Default options. Options with on[...] and custom[...]Handler
         * are annonymous function to replace a function. An option onGet[...]
         * must return something.
         * 
         * @mixed = can be function, jQuery object or string
         * @mixed-string = can be string or function that returns a string
         */
        options: {
            /**
             * First step: Define the container options.
             * 
             * @see ContainerHandler
             */
            container: {
                isCollapsable: true,                                            // Only when the ID is not generated (@see _generateId) and localStorage is available
                isResizable: true,                                              // Only when the ID is not generated (@see _generateId) and localStorage is available
                isSticky: true,                                                 // HC-Sticky plugin must be loaded
                theme: "",                                                      // Adds a class to the aio-tree with .aio-theme-{THEME}, if you want to apply multiple themes use a " " delimtter (space)
                
                // Collapsable options
                collapsableLocalstorage: true,                                  // Saves the state of an expanded/collapsed tree node in the localStorage. The nodes <a> must have a data-aio-id attribute so the state can be saved.
                collapsableDefaultStatus: "open",                               // or 'open', the default state of a node when the given node item is not saved in the localStorage
                
                // Resizable options ("required" when isResizable is true)
                defaultWidth: 250,                                              // Default width of container
                resizeOpposite: null,                                           // @mixed (required) The opposite site of the container if the sidebar grows (single item)
                customResizeHandler: false,                                     // FUNCTION this function will be called instead of doing anything while resizing (the true resizing)
                onResizeOpposite: false,                                        // FUNCTION this function will be called instead of changing the resizeOpposite element
                onResizeInit: false,                                            // FUNCTION this function will be called at first when initalizing the resize value from localStorage, it is recommenend to use this even customResizeHandler is defined
                onResizeFinished: false,                                        // FUNCTION this function will be called when the resizing is finished
                resizeTouch: false,                                             // Determines if the resizable works on touch devices
                resizeLocalStorage: true,                                       // Determines if the resize should be saved in the localStorage
                resizeMin: 250,                                                 // Minimal container width (px)
                resizeMax: 800,                                                 // Maximal container width (px)
                resizeOppositeMin: 200,                                         // Minimal opposite width (px)
                
                onRearrangeCancel: false,                                       // FUNCTION this function will be called when the rearrange mode is cancelled or closed. Use this function to reinit new generated HTML, for example
                onRearrangeSave: false,                                         // FUNCTION this function will be called when the "Save" button is pressed in rearrange mode. Use the AIO loader and the toolbarButton method to hide the rearrange mode
                onDoTooltips: false,                                            // FUNCTION this function will be called instead of the default tooltip handler. Controls, which have a tooltip, have the class .aio-tooltip
                tooltipsterSettings: { },                                       // Settings for tooltipster jQuery plugin
                nestedSortableSettings: { },                                    // Settings for nestedSortable jQuery plugin
                hcStickySettings: { },                                          // Settings for HC Sticky jQuery plugin
                customLoaderHandler: false                                      // Custom loader handler @see LoaderHandler
            },
            
            /**
             * Defines different create types, for example collections
             * and galleries. There can be multiple create types.
             * 
             * If you want no create types, define it as false.
             *
             * @see TypesHandler
             */
            createTypes: [{
                name: "folder",                                                 // (required) Default folder name
                icon: '<i class="fa fa-folder-open"></i><i class="fa fa-folder"></i>', // The icon when creating a new node
                visibleInActiveFolderType: false,                               // If it is NO array (otherwise this ["folderType"]) then the create type is visible in all folder types
                label: "New",                                                   // Label for the button (can be HTML)
                cssClasses: false,                                              // String with addClass({cssClasses}) to the link
                toolTipTitle: "",
                toolTipText: ""
            }],
            
            /**
             * Defines the cancel button for the create mode
             */
            createCancel: {
                label: "Cancel",
                cssClasses: ""
            },
            
            /**
             * Defines different toolbar buttons. For example the
             * refresh, delete, reorder or whatever. This is only 
             * an example how it can be used.
             * 
             * @see TypesHandler
             */
            toolbarButtons: {
                items: [{
                    name: "rename",
                    content: '<i class="fa fa-pencil"></i>',
                    visibleInActiveFolderType: false,
                    usePredefinedFunction: "rename",
                    toolTipTitle: "Rename",
                    toolTipText: "Rename the current selected folder.",
                    toolTipTextDisabledLink: "This link is disabled! Please select a folder."
                }, {
                    name: "rearrange",                                          // (required) The name of the toolbar button
                    content: '<i class="fa fa-sort"></i>',                      // Icon
                    visibleInActiveFolderType: false,                           // If it is NO array (otherwise this ["folderType"]) then the button is visible in all folder types
                    cssClasses: false,                                          // String with addClass({cssClasses}) to the link
                    onClick: false,                                             // FUNCTION this function will be called on click
                    onCancel: false,                                            // FUNCTION if there is function the button is cancelable
                    onAllowed: false,                                           // FUNCTION this function will be called before the onClick event and determines if the action can be accessed. @return boolean
                    usePredefinedFunction: "rearrange",                         // Use a predefined function, available is "rearrange"
                    toolTipTitle: "Rearrange",
                    toolTipText: "Change the hierarchical order of the folders."
                    //labelBack: ""                                             // Define your own labelBack for this item
                }],
                labelBack: "Cancel"
            },
            
            /**
             * Second step: Define the movement of items.
             * 
             * @see MovementHandler
             */
            movement: {
                customMovementHandler: false,                                   // Custom movement handler @see MovementHandler
                selector: ".aio-draggable-item",                                // (required) The selector for the jQuery.draggable function
                
                // On drop
                onDrop: false,                                                  // FUNCTION to handle the onDrop functionality
                
                // Will be applied to all targets (if a option is set in a target, the option in the target has a more higher priority)
                onGetLabel: false,                                              // FUNCTION this function will be called instead of the "Move" label for the helper
                onGetHelper: false,                                             // FUNCTION this function will be called instead of the <div class="aio-movement-helper"> output
                draggableSettings: { },                                         // Draggable settings for jQuery draggable
                droppableSettings: { }                                          // Droppable settings for jQuery droppable
            },
            
            others: {
                labelRenameSave: "OK",
                labelRearrangeSave: "Save new order",
                onExpand: false,                                                // FUNCTION this function will be called after a folder is expanded/collapsed
                onAfterFinish: false,                                           // FUNCTION this function will be called after the tree is initialized
                onSwitchFolder: false,                                          // FUNCTION this function will be called when a <a> folder is clicked
                onCreateFolder: false                                          // FUNCTION this function will be called when a new folder is created
            }
        },
        
        /**
         * C'tor
         */
        _create: function() {
            // Validate
            if (!this.element.hasClass("aio-tree")) {
                throw "Add class .aio-tree to the item.";
            }
            
            // Get standard list
            this._standardList = this.element.find(".aio-list-standard");
            if (this._standardList.size() !== 1) {
                throw "Please define one .aio-standard-list container";
            }
            
            // Get nodes list
            this._nodes = this.element.find(".aio-list-standard .aio-nodes");
            if (this._standardList.size() !== 1) {
                throw "Please define one .aio-list-standard > .aio-nodes container";
            }
            
            // Get toolbar
            this._toolbar = this.element.find(".aio-toolbar");
            if (this._toolbar.size() !== 1) {
                throw "Please define one .aio-toolbar container. If you want to hide the toolbar, create one and hide it with display:none;.";
            }
            
            // We need draggable, droppable and sortable
            if (typeof $.fn.draggable !== "function" || typeof $.fn.sortable !== "function") {
                throw "Please include jQuery UI with draggable and sortable support (https://jqueryui.com/).";
            }
            
            this.element.addClass("aio-tree-instance");
            
            // Themefiy the tree
            var theme = this.options.container.theme, themeClass = [];
            if (this._defined(theme) && theme.length > 0) {
                var themeSplit = theme.split(" ");
                for (var i = 0; i < themeSplit.length; i++) {
                    themeClass.push("aio-theme-" + themeSplit[i]);
                }
                this.element.addClass(themeClass.join(" "));
            }else{
                this.element.addClass("aio-theme-default");
            }
            
            // Check (perhaps add) an ID to the container
            this._idGenerate();
            this._testLocalStorage();

            // Handle collapsable, sticky and resizable container
            ContainerHandler.init(this);
            this._containerHandler = ContainerHandler;
            
            // Handle create types
            TypesHandler.init(this);
            this._typesHandler = TypesHandler;
            
            // Handle drag and drop
            if (typeof this.options.movement.customMovementHandler === "object") {
                this.options.customMovementHandler.init(this, MovementHandler); // Pass the default MovementHandler, too
                this._movementHandler = this.options.customMovementHandler;
            }else{
                MovementHandler.init(this); 
                this._movementHandler = MovementHandler;
            }
            
            // Handle toolbar
            ToolbarHandler.init(this);
            this._toolbarHandler = ToolbarHandler;
            
            // Handle loader
            if (typeof this.options.container.customLoaderHandler === "object") {
                this._loaderHandler = this.options.container.customLoaderHandler;
            }else{
                this._loaderHandler = LoaderHandler;
            }
            this._loaderHandler.init(this);
            
            /**
             * End initalizations
             */
            // Init active folder
            var activeFolder = this.active();
            if (activeFolder.size() > 0) {
                this._typesHandler._doSwitchFolder(this, activeFolder, 0);
            }
             
            // Init tooltips (for createTypes)
            this._containerHandler._doTooltips(this);
            
            // Remove fade effect if wanted
            setTimeout(function() {
                this.element.removeClass("aio-tree-fade");
            }.bind(this), 50);
            
            // After init callback
            if (typeof this.options.others.onAfterFinish === "function") {
                (this.options.others.onAfterFinish.bind(this.element))();
            }
            
            // Sticky it
            this._containerHandler.sticky(this);
            
            this.element.data("allInOneTree", true);
            this._checkFolderContent();
        },
        
        /**
         * =====================================================================
         *                      PUBLIC METHODS
         * =====================================================================
         */
        
        /**
         * Reinit all methods of the tree.
         * 
         * container.allInOneTree("reinit"); => reinit all
         * container.allInOneTree("reinit", "movement"); => reinit only movement
         * 
         * Possible types: movement tooltips tree
         * 
         * @param type  (optional) only reinit a set of types
         *              This can be the following:
         *              if you want to reinit multiple, seperate by space "movement tooltips"
         */
        reinit: function(type) {
            var allowAll = arguments.length == 0,
                types = allowAll ? [ "movement", "tooltips", "tree" ] : type.split(" ");

            if (types.indexOf("movement") > -1) {
                this._movementHandler.reinit(this);
            }
            if (types.indexOf("tooltips") > -1) {
                this._containerHandler._doTooltips(this);
            }
            if (types.indexOf("tree") > -1) {
                this._containerHandler._updateCollapsableNodes(this);
            }
            
            // Always reinit sticky container
            this._containerHandler.sticky(this);
            this._checkFolderContent();
        },

        /**
         * container.allInOneTree("movement", false); => disable movement handler
         */
        movement: function(state) {
            this._movementHandler.state(this, state);
        },
        
        /**
         * Simulate a click for a toolbar button by name.
         */
        toolbarButton: function(name) {
            this._toolbarHandler._iterateButtons(this, function(button) {
                if (button.name === name) {
                    if (typeof button.element === "object") {
                        button.element.click();
                    }
                    return false;
                }
            }.bind(this));
        },
        
        /**
         * Get the active <a> folder or
         * if you pass the first parameter, this must be the ID
         * of the folder and it will be set as active.
         * 
         * container.allInOneTree("active");
         * 
         * @param id (optional) the id of the <a> node (setter) or a jQuery object (<a>)
         * @param avoidOnSwitchEvent (optional) set this to true, if you call this function from the onSwitchFolder event
         * @return jQuery object if getter or boolean if setter
         * @see options.others.onSwitchFolder
         */
        active: function(id, avoidOnSwitchEvent) {
            var active = this._standardList.find("a.active");
            if (typeof id !== "undefined") {
                var newActive;
                
                if (typeof id === "object") {
                    newActive = id;
                }else{
                    newActive = this.byId(id);
                }
                
                if (typeof newActive === "object" && newActive.size() > 0) {
                    active.removeClass("active");
                    newActive.addClass("active");
                    this._typesHandler._doSwitchFolder(this, newActive);
                    if (typeof this.options.others.onSwitchFolder === "function" && avoidOnSwitchEvent !== true) {
                        return (this.options.others.onSwitchFolder.bind(this.element))(newActive, typeof active === "object" ? active.attr("data-aio-id") : undefined);
                    }
                    return true;
                }else{
                    return false;
                }
            }else{
                return active;
            }
        },
        
        /**
         * Change the toolbar loader to a specific state.
         * 
         * @see LoaderHandler
         */
        loader: function() {
            this._loaderHandler.fromArguments(arguments);
        },
        
        /**
         * Simply enable or disable the links in the tree.
         */
        nodes: function(enabled) {
            this._standardList.find("a").each(function() {
                if (enabled) {
            	    $(this).attr("href", $(this).attr("data-aio-href")).removeClass("aio-disable-link");
                }else{
                	$(this).attr("data-aio-href", $(this).attr("href")).addClass("aio-disable-link");
                	$(this).attr("href", "javascript:void(0)");
                }
            });
        },
        
        /**
         * Paste new HTML to the nodes list or get the nodes HTML. For example
         * if you change the hierarchical order (rearrange). If you change
         * it, be sure that you reinit() AFTER your toolbarButton
         * click.
         * 
         * Please do not paste HTML per jQuery yourself!
         * 
         * @see this::toolbarButton
         * @see this::reinit
         * @param newHTML html string putted into .aio-nodes>ul
         * @return if you pass no paramter, the HTML of the nodes will be returned
         */
        nodesHTML: function(newHTML) {
            var nodesUL = this._nodes.children("ul");
            if (newHTML) {
                nodesUL.data("nodesHTML", newHTML);
                nodesUL.html(newHTML);
                this.reinit();
            }else{
                return nodesUL.html();
            }
        },
        
        /**
         * Updates the counts of the given folder ID array.
         * 
         * @param arr object key = folder id, value = count
         */
        counts: function(arr) {
            if (typeof arr === "object") {
                var folder;
                $.each(arr, function(key, value) {
                    folder = this.byId(key);
                    if (folder !== false) {
                        folder.find(".aio-cnt").attr("class", "aio-cnt aio-cnt-" + value).html(value);
                    }
                }.bind(this));
            } 
        },
        
        /**
         * Get an folder by id. This includes standard list nodes, too.
         * 
         * @param id the id
         * @return jquery object
         */
        byId: function(id) {
            if (typeof id !== "undefined") {
                return this._standardList.find('a[data-aio-id="' + id + '"]');
            }else{
                return false;
            }
        },
        
        /**
         * Rename an element.
         * 
         * @param element the id of the element or a jQuery object of the <a> (@see this::byId)
         * @param newName the new name
         * @return boolean
         * @uses this::byId
         * @uses this::active
         * 
         * <strong>Note:</strong> If you are using only one parameter (newName) the
         * active folder will be renamed!
         */
        rename: function(element, newName) {
            var obj;
            // Get the element
            if (arguments.length == 1) {
                obj = this.active();
                newName = element;
            }else if (arguments.length == 2) {
                obj = element instanceof jQuery ? element : this.byId(element);
            }else{
                return false;
            }
            
            // Change the name if element is found (<a>)
            if (obj instanceof jQuery) {
                obj.children(".aio-node-name").html(newName);
                var input = obj.children("input"); // Check if rename input exists!
                if (input.size() > 0) {
                    input.val(newName);
                }
                return true;
            }else{
                return false;
            }
        },
        
        /**
         * Delete an element.
         * 
         * @param element the id of the element or a jQuery object of the <a> (@see this::byId)
         * @return boolean
         * 
         * <strong>Note:</strong> If you are using no parameter the
         * active folder will be deleted!
         */
        remove: function(element) {
            var obj;
            // Get the element
            if (arguments.length == 1 && typeof element === "object") {
                obj = element instanceof jQuery ? element : this.byId(element);
            }else{
                obj = this.active();
            }
            
            // Delete it
            if (obj instanceof jQuery) {
                // Remove the list object
                var li = obj.parent(),
                    parentOfLi = li.parent().parent(),
                    nextActive = false,
                    active = this.active(),
                    activeID = active.attr("data-aio-id");
                
                if (active.size() > 0 && activeID) { // We have active object!
                    if (active.is(obj)) {
                        // Select first or parent folder if we delete the active folder
                        if (parentOfLi.is("li")) {
                            // Select parent
                            nextActive = parentOfLi.children("a");
                        }else{
                            nextActive = "firstNode";
                        }
                    }else if (parentOfLi.children("ul").find('a[data-aio-id="' + activeID + '"]').size() > 0) { // Check if active is in deleted parent
                        nextActive = "firstNode";
                    }
                }
                
                // Remove it!
                li.remove();
                
                // check if the first node should be taken
                if (nextActive === "firstNode") {
                    if (this._nodes.find("a").size() > 0) {
                        nextActive = this._nodes.find('a:first');
                    }else if (this._standardList.find("a").size() > 0) {
                        nextActive = this._standardList.find("a:first");
                    }
                }
                
                // Set next active
                if (nextActive instanceof jQuery) {
                    this.active(nextActive);
                }
                
                // Reinit the tree
                this.reinit("tree");
                return true;
            }else{
                return false;
            }
        },
        
        /**
         * Parses the nestedSortable to an object array.
         * 
         * @param obj the nestedSortable object
         * @return { raw: original from nestedSortable, parsed: arraid for FID and PID } or false
         * @see this:_doPredefinedrearrange
         */
        serialize: function() {
            var nodesUL = this._nodes.children("ul"), th = this._toolbarHandler,
                alreadyNestedSortableActive;
            if (nodesUL.size() > 0) {
                alreadyNestedSortableActive = nodesUL.data("nestedSortable");
                if (!alreadyNestedSortableActive) { // activate
                    th._doPredefinedrearrangeActivate(nodesUL, this);
                }
                var parsed = th._doPredefinedrearrangeParse(nodesUL); // get result
                if (!alreadyNestedSortableActive) { // deactivate
                    nodesUL.sortable("destroy");
                }
                
                return parsed;
            }else{
                return false;
            }
        },
        
        /**
         * Parses the breadcrumb to the current folder.
         * You can define a return type which defines the
         * type of breadcrumb.
         * 
         * array: returns the breadcrumbs with its names in an array
         * string: return the breadcrumbs joined with a "/" into a string
         * html: return the breadcrumb as html string and <a>-handler to
         *       switch to the given folder
         * objects: return the breadcrumbs with all folder details in an object array
         * 
         * @param returnType "array"|"string"|"html"|"objects"
         * @param appendTo if you define returnType as html then this parameters is required.
         *                 it defines where the links should be added to (append)
         * @param htmlSeperator define the seperator for the directories you want to use (optional)
         * @return your defined returnType
         */
        breadcrumb: function(returnType, appendTo, htmlSeperator) {
            var active = this.active();
            if (active) {
                // Get the array for the current active folder
                var toTop = [], isStandard = !active.parents(".aio-nodes").size() > 0;
                
                // iterate the hierarchy
                var iterateHierarchy = function(cb) {
                    while (active !== false) {
                        active = active.parent() // => LI
                                        .parent() // => UL
                                        .parent() // => LI OF PARENT FOLDER
                                        .children('a[data-aio-id]');
                        if (active.size() > 0) {
                            cb();
                        }else{
                            active = false;
                        }
                    }
                };
                
                // define return types
                if (returnType === "html" || returnType === "objects") {
                    // We need a more complex array (object array)
                    var getFolderInfo = function(aTag) {
                        return {
                            id: aTag.attr("data-aio-id"),
                            type: aTag.attr("data-aio-type"),
                            name: aTag.children(".aio-node-name").html(),
                            obj: aTag,
                            link: aTag.attr("href")
                        };
                    };
                    toTop.push(getFolderInfo(active)); // The current active
                    if (!isStandard) {
                        iterateHierarchy(function() {
                            toTop.push(getFolderInfo(active));
                        });
                    }
                    toTop.reverse();
                    
                    // Return the objects
                    if (returnType === "objects") {
                        return toTop;
                    }
                    
                    // Append the html
                    if (appendTo) {
                        var row, link, seperator = htmlSeperator ? htmlSeperator : '<i class="fa fa-chevron-right" style="margin-left:5px;margin-right:5px;"></i>';
                        appendTo.html("");
                        for (var i = 0; i < toTop.length; i++) {
                            row = toTop[i];
                            link = $('<a href="' + row.link + '">' + row.name + '</a>').appendTo(appendTo);
                            link.data("aio-link", row.obj);
                            if (i < toTop.length - 1) {
                                appendTo.append(seperator);
                            }
                            link.click(function(event) {
                                $(this).data("aio-link").click();
                                event.preventDefault();
                                return false;
                            });
                        }
                    }else{
                        throw "Please define an appendTo parameter if you want to output as html!";
                    }
                }else{
                    // We need only a single array
                    toTop.push(active.children(".aio-node-name").html()); // The current active
                    if (!isStandard) {
                        iterateHierarchy(function() {
                            toTop.push(active.children(".aio-node-name").html());
                        });
                    }
                    toTop.reverse();
                    
                    if (!returnType || returnType === "array") {
                        if (!isStandard) {
                            toTop.unshift("/");
                        }
                        return toTop;
                    }
                    if (returnType === "string") {
                        var result = toTop.join("/");
                        if (!isStandard) {
                            result = "/" + result;
                        }
                        return result;
                    }
                }
            }
            return "";
        },
        
        /**
         * =====================================================================
         *                      PRIVATE METHODS
         * =====================================================================
         */
        
        /**
         * If there are no folders add the no-conotent.
         */
        _checkFolderContent: function() {
            if (this._nodes.find("li").size() > 0) {
                this.element.removeClass("aio-no-folders");
            }else{
                this.element.addClass("aio-no-folders");
            }
        },
        
        /**
         * Detects, if the localStorage is available.
         * 
         * @see _localStorage
         */
        _testLocalStorage: function() {
            var test = 'test';
            try {
                window.localStorage.setItem(test, test);
                window.localStorage.removeItem(test);
                this._localStorage = true;
            } catch(e) { }
        },
        
        /**
         * Assign the ID to the tree
         * If the object has a ID use this one, otherwise
         * use a generated one.
         * 
         * @param item the jQuery item it should append (optional)
         * @return if param item is given then return a generated id
         */
        _idGenerate: function(item) {
            var id;
            if (item instanceof jQuery) {
                if (this._hasAttr("id", item)) {
                    return item.attr("id");
                }else{
                    id = this._guid();
                    item.attr("id", id);
                    return id;
                }
            }else{
                var e = this.element;
                if (this._hasAttr("id")) {
                    id = e.attr("id");
                }else{
                    id = this._guid();
                    e.attr("id", id);
                    this._generatedId = true;
                }
                this._id = id;
            }
        },
        
        /**
         * Checks, if a given jQuery object has an
         * attribute. If you pass no obj, the element is used.
         * 
         * @param name the name of the attribute
         * @param obj (optional) jQuery object
         * @return boolean
         */
        _hasAttr: function(name, obj) {
            var e = obj instanceof jQuery ? obj : this.element,
                attr = e.attr(name);
            return typeof attr !== typeof undefined && attr !== false;
        },
        
        /**
         * This function returns a jQuery object
         * with one/multiple elements. The selector
         * can be a jQuery object, a string or a function.
         * 
         * @param obj string|jQuery object|function
         * @param noJquery set to true => only string and function is allowed and output is no jQuery object
         * @return jQuery object or undefined
         */
        _parseElements: function(obj, noJquery) {
            if (typeof obj === "string") {
                return noJquery !== true ? $(obj) : obj;
            }else if (typeof obj === "function") {
                return (obj.bind(this.element))(jQuery);
            }else if (obj instanceof jQuery && noJquery !== true) {
                return obj;
            }else{
                return undefined;
            }
        },
        
        _defined: function(obj) {
            return typeof obj !== typeof undefined && obj !== false && obj !== null;
        },
        
        /**
         * Checks if the current device is touch and returns
         * an array for mousedown mouseup and mousemove event names.
         */
        _isTouch: function () {
          var isTouch = 'ontouchstart' in window || window.navigator.maxTouchPoints;
          if (isTouch) {
              return { touch: isTouch, up: "touchend", down: "touchstart", move: "touchmove" };
          }else{
              return { touch: isTouch, up: "mouseup", down: "mousedown", move: "mousemove" };
          }
        },
        
        /**
         * Inject a style for a given type.
         */
        _injectStyle: function(type, rule) {
            var id = this._id + "-style-" + type;
            $("#" + id).remove();
            var div = $("<div />", {
                html: '&shy;<style>' + rule + '</style>'
            }).attr("id", id).hide().appendTo("body");
            return div;
        },
        
        /**
         * GUID generator
         */
        _guid: function() {
          function s4() {
            return Math.floor((1 + Math.random()) * 0x10000)
              .toString(16)
              .substring(1);
          }
          return "aio-" + s4() + s4() + '-' + s4() + '-' + s4() + '-' +
            s4() + '-' + s4() + s4() + s4();
        }
    };
    
    $.widget('mguenter.allInOneTree', AIOTree);
})(jQuery);
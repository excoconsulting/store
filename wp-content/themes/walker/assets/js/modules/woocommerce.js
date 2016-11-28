(function($) {
    'use strict';

    var woocommerce = {};
    edgtf.modules.woocommerce = woocommerce;

    woocommerce.edgtfInitQuantityButtons = edgtfInitQuantityButtons;
    woocommerce.edgtfInitSelect2 = edgtfInitSelect2;
    woocommerce.edgtfWooCommerceStickySidebar = edgtfWooCommerceStickySidebar;
    woocommerce.edgtfInitProductListCarousel = edgtfInitProductListCarousel;
    woocommerce.edgtfInitSingleProductImageSwitchLogic = edgtfInitSingleProductImageSwitchLogic;
    woocommerce.edgtfSetDataTitleForSingleProductButtons = edgtfSetDataTitleForSingleProductButtons;

    woocommerce.edgtfOnDocumentReady = edgtfOnDocumentReady;
    woocommerce.edgtfOnWindowLoad = edgtfOnWindowLoad;
    woocommerce.edgtfOnWindowResize = edgtfOnWindowResize;
    woocommerce.edgtfOnWindowScroll = edgtfOnWindowScroll;

    $(document).ready(edgtfOnDocumentReady);
    $(window).load(edgtfOnWindowLoad);
    $(window).resize(edgtfOnWindowResize);
    $(window).scroll(edgtfOnWindowScroll);
    
    /* 
        All functions to be called on $(document).ready() should be in this function
    */
    function edgtfOnDocumentReady() {
        edgtfInitQuantityButtons();
        edgtfInitSelect2();
	    edgtfReInitSelect2CartAjax();
        edgtfInitProductListMasonryShortcode();
        edgtfReinitWooStickySidebarOnTabClick();
        edgtfInitProductListCarousel();
        edgtfInitSingleProductImageSwitchLogic();
        edgtfSetDataTitleForSingleProductButtons();
    }

    /* 
        All functions to be called on $(window).load() should be in this function
    */
    function edgtfOnWindowLoad() {
	    edgtfInitSingleProductZoomImage();
        edgtfWooCommerceStickySidebar().init();
        edgtfInitProductListAnimatedShortcode();
    }

    /* 
        All functions to be called on $(window).resize() should be in this function
    */
    function edgtfOnWindowResize() {
        edgtfInitProductListMasonryShortcode();
    }

    /* 
        All functions to be called on $(window).scroll() should be in this function
    */
    function edgtfOnWindowScroll() {
        edgtfInitProductListAnimatedShortcode();
        edgtfInitSingleProductZoomImageLogic();
    }
    
    /*
    ** Init quantity buttons to increase/decrease products for cart
    */
    function edgtfInitQuantityButtons() {

        $(document).on( 'click', '.edgtf-quantity-minus, .edgtf-quantity-plus', function(e) {
            e.stopPropagation();

            var button = $(this),
                inputField = button.siblings('.edgtf-quantity-input'),
                step = parseFloat(inputField.attr('step')),
                max = parseFloat(inputField.attr('max')),
                minus = false,
                inputValue = parseFloat(inputField.val()),
                newInputValue;

            if (button.hasClass('edgtf-quantity-minus')) {
                minus = true;
            }

            if (minus) {
                newInputValue = inputValue - step;
                if (newInputValue >= 1) {
                    inputField.val(newInputValue);
                } else {
                    inputField.val(0);
                }
            } else {
                newInputValue = inputValue + step;
                if ( max === undefined ) {
                    inputField.val(newInputValue);
                } else {
                    if ( newInputValue >= max ) {
                        inputField.val(max);
                    } else {
                        inputField.val(newInputValue);
                    }
                }
            }

	        inputField.trigger( 'change' );
        });
    }

    /*
    ** Init select2 script for select html dropdowns
    */
    function edgtfInitSelect2() {

        if ($('.woocommerce-ordering .orderby').length) {
            $('.woocommerce-ordering .orderby').select2({
                minimumResultsForSearch: Infinity
            });
        }

	    if($('#calc_shipping_country').length) {
		    $('#calc_shipping_country').select2();
	    }

	    if($('.cart-collaterals .shipping select#calc_shipping_state').length) {
		    $('.cart-collaterals .shipping select#calc_shipping_state').select2();
	    }
    }

	/*
	 ** Re Init select2 script for select html dropdowns
	 */
	function edgtfReInitSelect2CartAjax() {

		$(document).ajaxComplete(function() {
			if ($('#calc_shipping_country').length) {
				$('#calc_shipping_country').select2();
			}
			
			if($('.cart-collaterals .shipping select#calc_shipping_state').length) {
				$('.cart-collaterals .shipping select#calc_shipping_state').select2();
			}
		});
	}

    /*
    ** Init sticky sidebar for single product page when single layout is sticky info
    */
    function edgtfWooCommerceStickySidebar(){

        var sswHolder = $('.edgtf-single-product-summary');
        var headerHeightOffset = 0;
        var widgetTopOffset = 0;
        var widgetTopPosition = 0;
        var sidebarHeight = 0;
        var sidebarWidth = 0;
        var objectsCollection = [];

        function addObjectItems() {
            if (sswHolder.length){
                sswHolder.each(function(){
                    var thisSswHolder = $(this);
                    widgetTopOffset = thisSswHolder.offset().top;
                    widgetTopPosition = thisSswHolder.position().top;
                    sidebarHeight = thisSswHolder.outerHeight();
                    sidebarWidth = thisSswHolder.width();

                    objectsCollection.push({'object': thisSswHolder, 'offset': widgetTopOffset, 'position': widgetTopPosition, 'height': sidebarHeight, 'width': sidebarWidth});
                });
            }
        }

        function initStickySidebarWidget() {

            if (objectsCollection.length && edgtf.body.hasClass('edgtf-woo-sticky-holder-enabled')){
                $.each(objectsCollection, function(i){

                    var thisSswHolder = objectsCollection[i]['object'];
                    var thisWidgetTopOffset = objectsCollection[i]['offset'];
                    var thisWidgetTopPosition = objectsCollection[i]['position'];
                    var thisSidebarHeight = objectsCollection[i]['height'];
                    var thisSidebarWidth = objectsCollection[i]['width'];

                    if (edgtf.body.hasClass('edgtf-fixed-on-scroll')) {
                        headerHeightOffset = 88;
                        if ($('.edgtf-fixed-wrapper').hasClass('edgtf-fixed')) {
                            headerHeightOffset = $('.edgtf-fixed-wrapper.edgtf-fixed').height();
                        }
                    } else {
                        headerHeightOffset = $('.edgtf-page-header').height();
                    }

                    if (edgtf.windowWidth > 1024) {

                        var sidebarPosition = -(thisWidgetTopPosition - headerHeightOffset - edgtfGlobalVars.vars.edgtfAddForAdminBar - 10); // 10 is arbitrarily value for smooth sticky animation for first scroll
                        var stickySidebarHeight = thisSidebarHeight - thisWidgetTopPosition;
                        var summaryContentTopMargin = parseInt($('.edgtf-single-product-summary').css('margin-top'));
                        var stickySidebarRowHolderHeight = thisSswHolder.parent().outerHeight() - 10 - summaryContentTopMargin - 10; // 10 is arbitrarily value for smooth sticky animation for first scroll and second 10 value is margin top of single product title

                        //move sidebar up when hits the end of section row
                        var rowSectionEndInViewport = thisWidgetTopOffset - headerHeightOffset - thisWidgetTopPosition - edgtfGlobalVars.vars.edgtfTopBarHeight + stickySidebarRowHolderHeight;

                        if ((edgtf.scroll >= thisWidgetTopOffset - headerHeightOffset) && thisSidebarHeight < stickySidebarRowHolderHeight) {
                            if(thisSswHolder.children('.summary').hasClass('edgtf-sticky-sidebar-appeared')) {
                                thisSswHolder.children('.summary.edgtf-sticky-sidebar-appeared').css({'top': sidebarPosition+'px'});
                            } else {
                                thisSswHolder.children('.summary').addClass('edgtf-sticky-sidebar-appeared').css({'position': 'fixed', 'top': sidebarPosition+'px', 'width': thisSidebarWidth, 'margin-top': '-10px'}).animate({'margin-top': '0'}, 200);
                            }

                            if (edgtf.scroll + stickySidebarHeight >= rowSectionEndInViewport) {
                                thisSswHolder.children('.summary.edgtf-sticky-sidebar-appeared').css({'position': 'absolute', 'top': stickySidebarRowHolderHeight-stickySidebarHeight+sidebarPosition-headerHeightOffset+'px'});
                            } else {
                                thisSswHolder.children('.summary.edgtf-sticky-sidebar-appeared').css({'position': 'fixed', 'top': sidebarPosition+'px'});
                            }
                        } else {
                            thisSswHolder.children('.summary').removeClass('edgtf-sticky-sidebar-appeared').css({'position': 'relative', 'top': '0',  'width': 'auto'});
                        }
                    } else {
                        thisSswHolder.children('.summary').removeClass('edgtf-sticky-sidebar-appeared').css({'position': 'relative', 'top': '0',  'width': 'auto'});
                    }
                });
            }
        }

        return {
            init: function() {
                addObjectItems();

                initStickySidebarWidget();

                $(window).scroll(function(){
                    initStickySidebarWidget();
                });
            },
            reInit: initStickySidebarWidget
        };
    }

    /*
    ** ReInit sticky sidebar logic when tabs are clicked on single product
    */
    function edgtfReinitWooStickySidebarOnTabClick() {
        var item = $('.woocommerce-tabs ul.tabs>li a');

        if(item.length) {
            item.on('click', function(){
                if($(this).parents('.summary').hasClass('edgtf-sticky-sidebar-appeared')){
                    $(this).parents('.summary').removeClass('edgtf-sticky-sidebar-appeared').css({'position': 'relative', 'top': '0',  'width': 'auto'});
                    setTimeout(function(){
                        edgtfWooCommerceStickySidebar().init();
                    }, 100);
                } else {
                    setTimeout(function(){
                        edgtfWooCommerceStickySidebar().init();
                    }, 100);
                }
            });
        }
    }

    /*
    ** Init Product List Masonry Shortcode Layout
    */
    function edgtfInitProductListMasonryShortcode() {

        var container = $('.edgtf-pl-holder.edgtf-masonry-layout .edgtf-pl-outer');
        
        if(container.length) {

            container.waitForImages(function() {
                container.isotope({
                    itemSelector: '.edgtf-pli',
                    resizable: false,
                    masonry: {
                        columnWidth: '.edgtf-pl-sizer',
                        gutter: '.edgtf-pl-gutter'
                    }
                });
                container.css('opacity', 1);
            });
        }
    }

    /*
    ** Init Product List Slider Shortcode
    */
    function edgtfInitProductListCarousel() {

        var carouselHolder = $('.edgtf-plc-holder');

        if (carouselHolder.length) {
            carouselHolder.each(function () {
                var thisCarousels = $(this),
                    carousel = thisCarousels.children('.edgtf-plc-outer'),
                    numberOfItems = (thisCarousels.data('number-of-visible-items') !== '') ? parseInt(thisCarousels.data('number-of-visible-items')) : 3,
                    autoplay = (thisCarousels.data('autoplay') === 'yes') ? true : false,
                    autoplayTimeout = (thisCarousels.data('autoplay-timeout') !== '') ? parseInt(thisCarousels.data('autoplay-timeout')) : 5000,
                    loop = (thisCarousels.data('loop') === 'yes') ? true : false,
                    speed = (thisCarousels.data('speed') !== '') ? parseInt(thisCarousels.data('speed')) : 650,
                    margin = (thisCarousels.hasClass('edgtf-normal-space')) ? 30 : 10,
                    navigation = (thisCarousels.data('navigation') === 'yes') ? true : false,
                    pagination = (thisCarousels.data('pagination') === 'yes') ? true : false;

                var responsiveItems1 = numberOfItems;
                var responsiveItems2 = 3;
                var responsiveItems3 = 2;

                if (numberOfItems > 4) {
                    responsiveItems1 = 4;
                }

                if (numberOfItems < 3) {
                    responsiveItems2 = numberOfItems;
                    responsiveItems3 = numberOfItems;
                }

                if (numberOfItems === 1) {
                    margin = 0;
                }

                var owl = carousel.owlCarousel({
                    items: numberOfItems,
                    autoplay: autoplay,
                    autoplayTimeout: autoplayTimeout,
                    autoplayHoverPause: true,
                    loop: loop,
                    smartSpeed: speed,
                    margin: margin,
                    nav: navigation,
                    navText: [
                        '<span class="edgtf-prev-icon"><span class="edgtf-icon-arrow icon-arrows-left"></span><span class="edgtf-nav-label edgtf-prev-label">PREV</span></span>',
                        '<span class="edgtf-next-icon"><span class="edgtf-nav-label edgtf-next-label">NEXT</span><span class="edgtf-icon-arrow icon-arrows-right"></span></span>'
                    ],
                    dots: pagination,
                    mouseDrag:true,
                    touchDrag: true,
                    responsive:{
                        1200:{
                            items: numberOfItems
                        },
                        1024:{
                            items: responsiveItems1
                        },
                        769:{
                            items: responsiveItems2
                        },
                        601:{
                            items: responsiveItems3
                        },
                        0:{
                            items: 1
                        }
                    }
                });

                carousel.css({'visibility': 'visible'});
            });
        }
    }

    /*
    ** Init Product List Animated Shortcode Layout
    */
    function edgtfInitProductListAnimatedShortcode() {

        var productListAnimatedHolder = $('.edgtf-pla-holder');
        
        if(productListAnimatedHolder.length) {
            productListAnimatedHolder.each(function(){
                var thisProductList = $(this).children('.edgtf-pla-item');

                thisProductList.each(function(){
                    var thisItem = $(this),
                        thisItemHeight = $(this).outerHeight(),
                        thisItemOffset = $(this).offset().top;

                    if(thisItemOffset + thisItemHeight - edgtf.windowHeight - edgtf.scroll < thisItemHeight * 0.9 && edgtf.windowWidth > 1024) {
                        thisItem.addClass('edgtf-pla-animated');
                    } else {
                        thisItem.removeClass('edgtf-pla-animated');
                    }
                });
            });
        }
    }

    /*
    ** Init switch image logic for thumbnail and featured images on product single page
    */
    function edgtfInitSingleProductImageSwitchLogic() {

        if(edgtf.body.hasClass('edgtf-woo-single-switch-image')){
            
            var thumbnailImage = $('.edgtf-woo-single-page .product .images .thumbnails > a'),
                featuredImage = $('.edgtf-woo-single-page .product .images .woocommerce-main-image');

            if(featuredImage.length) {
                featuredImage.on('click', function() {
                    if($('div.pp_overlay').length) {
                        $.prettyPhoto.close();
                    }              
                    if(edgtf.body.hasClass('edgtf-disable-thumbnail-prettyphoto')){
                        edgtf.body.removeClass('edgtf-disable-thumbnail-prettyphoto');
                    }
                    if(featuredImage.children('.edgtf-fake-featured-image').length){
                        $('.edgtf-fake-featured-image').stop().animate({'opacity': '0'}, 300, function() {
                            $(this).remove();
                        });
                    }
                    
                    setTimeout(function() {
                        edgtfInitSingleProductZoomImage();
                    }, 1000);                
                });
            }

            if(thumbnailImage.length) {
                thumbnailImage.each(function(){
                    var thisThumbnailImage = $(this),
                        thisThumbnailImageSrc = thisThumbnailImage.attr('href');                    

                    thisThumbnailImage.on('click', function() {
                        if(!edgtf.body.hasClass('edgtf-disable-thumbnail-prettyphoto')){
                            edgtf.body.addClass('edgtf-disable-thumbnail-prettyphoto');
                        }

                        if($('div.pp_overlay').length) {
                            $.prettyPhoto.close();
                        }
                        if(thisThumbnailImageSrc !== '' && featuredImage !== '') {
                            if(featuredImage.children('.edgtf-fake-featured-image').length){
                                $('.edgtf-fake-featured-image').remove();
                            }
                            featuredImage.append('<img itemprop="image" class="edgtf-fake-featured-image" src="'+thisThumbnailImageSrc+'" />');
                        }

                        edgtfInitSingleProductZoomImage();
                    });
                });
            }            
        }
    }

    /*
    ** Set data attribute for single product buttons for hover animation
    */
    function edgtfSetDataTitleForSingleProductButtons() {

        var singleProductButton = $('.edgtf-single-product-summary form.cart .button');

        if(singleProductButton.length) {
            singleProductButton.each(function() {
                var thisItem = $(this),
                    thisItemText = thisItem.text();

                if(thisItemText.length) {
                    thisItem.attr('data-title', thisItemText);
                }    
            });
        }
    }

    /*
    ** Set data attribute for single product buttons for hover animation
    */
    function edgtfInitSingleProductZoomImage() {

        var item = $('.no-touch .edgtf-woo-single-page-layout-standard.edgtf-zoom-image-enabled .product .images a.woocommerce-main-image');

        if(item.length) {

            if(item.children('.edgtf-woocommerce-main-image-zoom').length) {
                item.children('.edgtf-woocommerce-main-image-zoom').remove();
            }

            item.each(function() {
                var thisItem = $(this),
                    thisItemImage = thisItem.attr('href');

                    thisItem.attr('id', 'edgtf-woo-zoom-cursor');

                    if(thisItem.children('.edgtf-fake-featured-image').length) {
                        thisItemImage = thisItem.children('.edgtf-fake-featured-image').attr('src');
                    } 

                if(thisItemImage.length) {

                    if(thisItem.children('.edgtf-woocommerce-zoom-cursor').length) {
                        thisItem.children('.edgtf-woocommerce-zoom-cursor').remove();
                    }

                    thisItem.append('<div class="edgtf-woocommerce-zoom-cursor"></div>');
                    thisItem.append('<div class="edgtf-woocommerce-main-image-zoom" data-src="'+thisItemImage+'" style="background-image: url('+thisItemImage+');"></div>');
                    edgtfInitSingleProductZoomImageLogic();
                }
            });
        }
    }

    /*
    ** Set data attribute for single product buttons for hover animation
    */
    function edgtfInitSingleProductZoomImageLogic() {

        var item = $('#edgtf-woo-zoom-cursor');
    
        if(item.length) {
        
            var tmpImg = new Image(),
                zoomImageSrc = item.children('.edgtf-woocommerce-main-image-zoom').data('src'),
                itemWidth = item.outerWidth(),
                itemHeight = item.outerHeight(),
                itemOffsetTop = item.offset().top - edgtf.scroll,
                itemOffsetLeft = item.offset().left,
                cursor = $('.edgtf-woocommerce-zoom-cursor'),
                cursorWidth = cursor.outerWidth(),
                cursorHeight = cursor.outerHeight(),
                x = 0,
                y = 0,
                currentXCPosition = 0,
                currentYCPosition = 0,
                imagePosition = 0,
                imagecurrXPosition = 0,
                imagecurrYPosition = 0,
                imageXPosition = 0,
                imageYPosition = 0,
                zoomImage = item.children('.edgtf-woocommerce-main-image-zoom');
        
            tmpImg.src = zoomImageSrc;
        
            document.getElementById('edgtf-woo-zoom-cursor').addEventListener("mousemove", function(event) {
	            var orginalImageWidth = tmpImg.width,
		            orginalImageHeight = tmpImg.height;
	            
                x = (event.clientX - itemOffsetLeft - cursorWidth / 2) >> 0;
                y = (event.clientY - itemOffsetTop - cursorWidth / 2) >> 0;
            
                if(x > itemWidth - cursorWidth) {
                    currentXCPosition = itemWidth - cursorWidth;
                } else if (x < 0) {
                    currentXCPosition = 0;
                } else {
                    currentXCPosition = x;
                }
            
                if(y > itemHeight - cursorHeight) {
                    currentYCPosition = itemHeight - cursorHeight;
                } else if (y < 0) {
                    currentYCPosition = 0;
                } else {
                    currentYCPosition = y;
                }
            
                imageXPosition = (currentXCPosition / itemWidth * orginalImageWidth) >> 0;
                imageYPosition = (currentYCPosition / itemHeight * orginalImageHeight) >> 0;
            
                imagecurrXPosition += (imageXPosition - imagecurrXPosition) / 3;
                imagecurrYPosition += (imageYPosition - imagecurrYPosition) / 3;
            
                imagePosition = -imagecurrXPosition + 'px' + ' ' + -imagecurrYPosition + 'px';
            
                item.css({'overflow': 'inherit'});
                cursor.css({'opacity': '1', 'top': currentYCPosition, 'left': currentXCPosition});
                zoomImage.css({'opacity': '1', 'background-position': imagePosition});
            });
        
            document.getElementById('edgtf-woo-zoom-cursor').addEventListener("mouseleave", function() {
                item.css({'overflow': 'hidden'});
                cursor.css({'opacity': '0'});
                zoomImage.css({'opacity': '0'});
            });
        }
    }

})(jQuery);
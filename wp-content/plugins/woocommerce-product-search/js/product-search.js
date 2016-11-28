/**
 * product-search.js
 *
 * Copyright (c) "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is provided subject to the license granted.
 * Unauthorized use and distribution is prohibited.
 * See COPYRIGHT.txt and LICENSE.txt
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This header and all notices must be kept intact.
 *
 * @author itthinx
 * @package woocommerce-product-search
 * @since 1.0.0
 */

var ixwps = {
	// query posting enabled
	doPost : true,
	blinkerTimeouts : [],
	blinkerTimeout : 5000
};

/**
 * Inhibit form submission when enter is pressed on the input field.
 */
ixwps.inhibitEnter = function( fieldId ) {
	jQuery("#"+fieldId).keydown(function(e){
		if ( e.keyCode == 13 ) { // enter
			e.preventDefault();
			return false;
		}
	});
};

/**
 * Show/hide search results when input field gains/loses focus.
 */
ixwps.dynamicFocus = function ( fieldId, resultsId ) {
	var $field = jQuery('#'+fieldId),
		$results = jQuery('#'+resultsId);
	$field.focusout(function(e){
		// Check if any child element has the focus and leave a moment to
		// make sure it already gained focus before deciding whether we
		// lost focus or not.
		var $elem = jQuery(this);
		setTimeout(
			function() {
				var hasFocus = ($elem.find(':focus').length > 0);
				if ( !hasFocus ) {
					$results.hide();
				}
			},
			100
		);
	});
	$field.focusin(function(e){
		// lateral funcionality: take care of hiding results if search field is empty 
		var $elem = jQuery(this),
			$searchField = $elem.find('input.product-search-field');
		if ( $searchField.length > 0 ) {
			if ( jQuery($searchField[0]).val().length == 0 ) {
				// empty results for an empty query [1]
				$results.html('');
			}
		}
		// standard behavior here is to show whether it's empty or not, keep that
		$results.show();
	});
};

/**
 * Result navigation.
 */
ixwps.navigate = function( fieldId, resultsId ) {
	jQuery("#"+fieldId).keydown(function(e){
		var i = 0, navigate = false, escape = false;
		switch ( e.keyCode ) {
			case 37 : // left
				break;
			case 39 : // right
				break;
			case 38 : // up
				i = -1;
				break;
			case 40 : // down
				i = 1;
				break;
			case 13 : // enter
				navigate = true;
				break;
			case 27 : // esc
				escape = true;
				break;
		}
		if ( i != 0 ) {
			var entries = jQuery("#"+resultsId).find('.entry'),
				active = jQuery("#"+resultsId+" .entry.active").index();
			if ( entries.length > 0 ) {
				if ( active >= 0 ) {
					jQuery(entries[active]).removeClass("active");
				}
				active += i;
				if ( active < 0 ) {
					active = entries.length - 1;
				} else if ( active >= entries.length ) {
					active = 0;
				}
				jQuery(entries[active]).addClass("active");
			}
			e.preventDefault();
			return false;
		}
		if ( navigate ) {
			var entries = jQuery("#"+resultsId).find('.entry'),
				active = jQuery("#"+resultsId+" .entry.active").index();
			if ( ( active >= 0 ) && ( active < entries.length ) ) {
				var link = jQuery(entries[active]).find('a').get(0);
				if ( typeof link !== 'undefined' ) {
					var url = jQuery(link).attr('href');
					if ( typeof url !== 'undefined' ) {
						e.preventDefault();
						ixwps.doPost = false; // disable posting the query
						document.location = url;
						return false;
					}
				}
			}
		}
		if ( escape ) {
			var entries = jQuery("#"+resultsId).find('.entry'),
			active = jQuery("#"+resultsId+" .entry.active").index();
			if ( entries.length > 0 ) {
				if ( active >= 0 ) {
					jQuery(entries[active]).removeClass("active");
				}
			}
			e.preventDefault();
			return false;
		}
	});
};

/**
 * Creates an event handler that adjusts the results width to the width of
 * the search field.
 */
ixwps.autoAdjust = function( fieldId, resultsId ) {
	var $field = jQuery('#'+fieldId),
		$results = jQuery('#'+resultsId);
	$results.on('adjustWidth',function(e){
		e.stopPropagation();
		// field width minus own border
		var w = $field.outerWidth() - ( $results.outerWidth() - $results.innerWidth() );
		$results.width(w);
	});
};

/**
 * POST query and display results.
 * 
 * The args parameter object can be used to indicate:
 * - no_results : alternative text to show when no results are obtained
 * - blinkerTimeout : to modify the default blinker timeout in milliseconds or 0 to disable it
 * 
 * @param fieldId
 * @param containerId
 * @param resultsId
 * @param url
 * @param query
 * @param object args
 */
ixwps.productSearch = function( fieldId, containerId, resultsId, url, query, args ) {

	// This is true when we are going somewhere else using document.location,
	// don't post the query in that case.
	if (!ixwps.doPost) {
		return;
	}

	if ( typeof args === "undefined" ) {
		args = {};
	}

	var $results = jQuery( "#"+resultsId ),
		$blinker = jQuery( "#"+fieldId ),
		blinkerTimeout = ixwps.blinkerTimeout;

	if ( typeof args.blinkerTimeout !== "undefined" ) {
		blinkerTimeout = args.blinkerTimeout;
	}
	query = jQuery.trim(query);
	if ( query != "" ) {
		$blinker.addClass('blinker');
		if ( blinkerTimeout > 0 ) {
			ixwps.blinkerTimeouts["#"+fieldId] = setTimeout(function(){$blinker.removeClass('blinker');}, blinkerTimeout);
		}
		var params = {
			"action" : "product_search",
			"product-search": 1,
			"product-query": query
		};
		if ( typeof args.lang !== "undefined" ) {
			params.lang = args.lang;
		}
		jQuery.post(
			url,
			params,
			function ( data ) {
				var results = '';
				if ( ( data !== null ) && ( data.length > 0 ) ) {
					var current_type = null,
						product_thumbnails = true,
						show_description = false,
						show_price = false;
					if ( typeof args.product_thumbnails !== "undefined" ) {
						product_thumbnails = args.product_thumbnails;
					}
					if ( typeof args.show_description !== "undefined" ) {
						show_description = args.show_description;
					}
					if ( typeof args.show_price !== "undefined" ) {
						show_price = args.show_price;
					}
					// table start
					results += '<table class="search-results">';
					for( var key in data ) {
						var first = '';
						if ( current_type != data[key].type ) {
							current_type = data[key].type;
							first = 'first';
						}

						results += '<tr class="entry ' + data[key].type + ' ' + first + '">';

						if ( product_thumbnails && ( current_type != 's_product_cat' ) ) {
							results += '<td class="product-image">';
							results += '<a href="' + data[key].url + '" title="' + data[key].title + '">';
							if ( typeof data[key].thumbnail !== "undefined" ) {
								var width = '', height = '', alt='';
								if ( typeof data[key].thumbnail_alt !== "undefined" ) {
									alt = ' alt="' + data[key].thumbnail_alt + '" ';
								}
								if ( typeof data[key].thumbnail_width !== "undefined" ) {
									width = ' width="' + data[key].thumbnail_width + '" ';
								}
								if ( typeof data[key].thumbnail_height !== "undefined" ) {
									height = ' height="' + data[key].thumbnail_height + '" ';
								}
								results += '<img class="thumbnail" src="' + data[key].thumbnail + '" ' + alt + width + height + '/>';
							}
							results += '</a>';
							results += '</td>';
						}

						switch( current_type ) {
							case 's_product_cat' :
								results += '<td class="category-info" colspan="2">';
								results += '<a href="' + data[key].url + '" title="' + data[key].title + '">';
								results += '<span class="title">' + data[key].title + '</span>';
								results += '</a>';
								results += '</td>';
								break;
							default:
								results += '<td class="product-info">';
								results += '<a href="' + data[key].url + '" title="' + data[key].title + '">';
								results += '<span class="title">' + data[key].title + '</span>';
								if ( show_description ) {
									if ( typeof data[key].description !== "undefined" ) {
										results += '<span class="description">' + data[key].description + '</span>';
									}
								}
								if ( show_price ) {
									if ( typeof data[key].price !== "undefined" ) {
										results += '<span class="price">' + data[key].price + '</span>';
									}
								}
								results += '</a>';
								results += '</td>';
						}

						
						results += '</tr>';
					}
					results += '</table>';
					// end of table
				} else {
					if ( typeof args.no_results !== "undefined" ) {
						if ( args.no_results.length > 0 ) {
							results += '<div class="no-results">';
							results += args.no_results;
							results += '</div>';
						}
					}
				}
				$results.show().html( results );
				ixwps.clickable( resultsId );
				$results.trigger('adjustWidth');
				$blinker.removeClass('blinker');
				if ( blinkerTimeout > 0 ) {
					clearTimeout(ixwps.blinkerTimeouts["#"+fieldId]);
				}
			},
			"json"
		);
	} else {
		// hide and empty results for an empty query
		// if we don't get here (minimum characters not input), [1] will empty
		$results.hide().html('');
	}
};

/**
 * Clickable table rows.
 */
ixwps.clickable = function( resultsId ) {
	jQuery('#' + resultsId + ' table.search-results tr').click(function(){
		var url = jQuery(this).find('a').attr('href');
		if ( url ) {
			window.location = url;
		}
	});
	jQuery('#' + resultsId + ' table.search-results tr').css('cursor','pointer');
};

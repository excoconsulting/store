
/*-----------------------------------------------------------------*/
/*  Global variable for composite apps.                            */
/*-----------------------------------------------------------------*/

var wc_cp_composite_scripts = {};

/*-----------------------------------------------------------------*/
/*  Global utility variables + functions.                          */
/*-----------------------------------------------------------------*/

/**
 * Cache for speed.
 */
var $wc_cp_body     = jQuery( document.body ),
	$wc_cp_window   = jQuery( window ),
	$wc_cp_document = jQuery( document );

/**
 * BlockUI background params.
 */
var wc_cp_block_params = {
	message:    null,
	fadeIn:     0,
	fadeOut:    0,
	overlayCSS: {
		background: 'rgba( 255, 255, 255, 0 )',
		opacity:    1,
	}
};

var wc_cp_block_wait_params = {
	message:    null,
	fadeIn:     200,
	fadeOut:    200,
	overlayCSS: {
		background: 'rgba( 255, 255, 255, 0 )',
		opacity:    0.6,
	}
};

/**
 * Toggle-box handling.
 */
function wc_cp_toggle_element( $container, $content, complete ) {

	if ( $container.data( 'animating' ) === true ) {
		return false;
	}

	if ( $container.hasClass( 'closed' ) ) {
		setTimeout( function() {
			$content.slideDown( { duration: 300, queue: false, always: function() {
				$container.data( 'animating', false );
				if ( typeof( complete ) === 'function' ) {
					complete();
				}
			} } );
		}, 40 );
		$container.removeClass( 'closed' ).addClass( 'open' );
		$container.data( 'animating', true );
	} else {
		setTimeout( function() {
			$content.slideUp( { duration: 300, queue: false, always: function() {
				$container.data( 'animating', false );
				if ( typeof( complete ) === 'function' ) {
					complete();
				}
			} } );
		}, 40 );
		$container.removeClass( 'open' ).addClass( 'closed' );
		$container.data( 'animating', true );
	}

	return true;
}

/**
 * Viewport scroller.
 */
function wc_cp_scroll_viewport( target, params ) {

	var anim_complete;
	var scroll_to;

	var partial         = typeof( params.partial ) === 'undefined' ? true : params.partial;
	var offset          = typeof( params.offset ) === 'undefined' ? 50 : params.offset;
	var timeout         = typeof( params.timeout ) === 'undefined' ? 5 : params.timeout;
	var anim_duration   = typeof( params.duration ) === 'undefined' ? 250 : params.duration;
	var anim_queue      = typeof( params.queue ) === 'undefined' ? false : params.queue;
	var always_complete = typeof( params.always_on_complete ) === 'undefined' ? false : params.always_on_complete;
	var scroll_method   = typeof( params.scroll_method ) === 'undefined' ? false : params.scroll_method;

	var do_scroll       = false;
	var $w              = $wc_cp_window;
	var $d              = $wc_cp_document;

	if ( typeof( params.on_complete ) === 'undefined' || params.on_complete === false ) {
		anim_complete = function() {
			return false;
		};
	} else {
		anim_complete = params.on_complete;
	}

	var scroll_viewport = function() {

		// Scroll viewport by an offset.
		if ( target === 'relative' ) {

			scroll_to = $w.scrollTop() - offset;
			do_scroll = true;

		// Scroll viewport to absolute document position.
		} else if ( target === 'absolute' ) {

			scroll_to = offset;
			do_scroll = true;

		// Scroll to target element.
		} else if ( target.length > 0 && target.is( ':visible' ) && ! target.wc_cp_is_in_viewport( partial ) ) {

			var window_offset = offset;

			if ( scroll_method === 'bottom' || target.hasClass( 'scroll_bottom' ) ) {
				window_offset = $w.height() - target.outerHeight( true ) - offset;
			} else if ( scroll_method === 'middle' ) {
				window_offset = $w.height() / 3 * 2 - target.outerHeight( true ) - offset;
			}

			scroll_to = target.offset().top - window_offset;

			// Ensure element top is in viewport.
			if ( target.offset().top < scroll_to ) {
				scroll_to = target.offset().top;
			}

			do_scroll = true;
		}

		if ( do_scroll ) {

			// Prevent out-of-bounds scrolling.
			if ( scroll_to > $d.height() - $w.height() ) {
				scroll_to = $d.height() - $w.height() - 100;
			}

			// Avoid scrolling both html and body.
			var pos            = jQuery( 'html' ).scrollTop();
			var animate_target = 'body';

			jQuery( 'html' ).scrollTop( jQuery( 'html' ).scrollTop() - 1 );
			if ( pos != jQuery( 'html' ).scrollTop() ) {
				animate_target = 'html';
			}

			jQuery( animate_target ).animate( { scrollTop: scroll_to }, { duration: anim_duration, queue: anim_queue, always: anim_complete } );

		} else {
			if ( always_complete ) {
				anim_complete();
			}
		}
	};

	if ( timeout > 0 ) {
		setTimeout( function() {
			scroll_viewport();
		}, timeout );
	} else {
		scroll_viewport();
	}
}

/**
 * Formats price strings according to WC settings.
 */
function wc_cp_woocommerce_number_format( price ) {

	var remove     = wc_composite_params.currency_format_decimal_sep;
	var position   = wc_composite_params.currency_position;
	var symbol     = wc_composite_params.currency_symbol;
	var trim_zeros = wc_composite_params.currency_format_trim_zeros;
	var decimals   = wc_composite_params.currency_format_num_decimals;

	if ( trim_zeros == 'yes' && decimals > 0 ) {
		for ( var i = 0; i < decimals; i++ ) { remove = remove + '0'; }
		price = price.replace( remove, '' );
	}

	var price_format = '';

	if ( position == 'left' ) {
		price_format = '<span class="amount">' + symbol + price + '</span>';
	} else if ( position == 'right' ) {
		price_format = '<span class="amount">' + price + symbol +  '</span>';
	} else if ( position == 'left_space' ) {
		price_format = '<span class="amount">' + symbol + ' ' + price + '</span>';
	} else if ( position == 'right_space' ) {
		price_format = '<span class="amount">' + price + ' ' + symbol +  '</span>';
	}

	return price_format;
}

/**
 * Formats price values according to WC settings.
 */
function wc_cp_number_format( number ) {

	var decimals      = wc_composite_params.currency_format_num_decimals;
	var decimal_sep   = wc_composite_params.currency_format_decimal_sep;
	var thousands_sep = wc_composite_params.currency_format_thousand_sep;

	var n = number, c = isNaN( decimals = Math.abs( decimals ) ) ? 2 : decimals;
	var d = typeof( decimal_sep ) === 'undefined' ? ',' : decimal_sep;
	var t = typeof( thousands_sep ) === 'undefined' ? '.' : thousands_sep, s = n < 0 ? '-' : '';
	var i = parseInt( n = Math.abs( +n || 0 ).toFixed(c) ) + '', j = ( j = i.length ) > 3 ? j % 3 : 0;

	return s + ( j ? i.substr( 0, j ) + t : '' ) + i.substr(j).replace( /(\d{3})(?=\d)/g, '$1' + t ) + ( c ? d + Math.abs( n - i ).toFixed(c).slice(2) : '' );
}

/**
 * i18n-friendly joining of values in an array of strings.
 */
function wc_cp_join( arr ) {

	var joined_arr = '';
	var count      = arr.length;

	if ( count > 0 ) {

		var loop = 0;

		for ( var i = 0; i < count; i++ ) {

			loop++;

			if ( count == 1 || loop == 1 ) {
				joined_arr = arr[ i ];
			} else {
				joined_arr = wc_composite_params.i18n_comma_sep.replace( '%s', joined_arr ).replace( '%v', arr[ i ] );
			}
		}
	}

	return joined_arr;
}

/**
 * Construct a (formatted) map of selected variation attributes.
 */
function wc_cp_get_variation_data( $variations, formatted ) {

	formatted = typeof( formatted ) === 'undefined' ? false : formatted;

	var $attribute_options       = $variations.find( '.attribute-options' ),
		attribute_options_length = $attribute_options.length,
		meta                     = [],
		formatted_meta           = '';

	if ( attribute_options_length === 0 ) {
		return '';
	}

	$attribute_options.each( function( index ) {

		var $attribute_option = jQuery( this );

		var selected = $attribute_option.find( 'select' ).val();

		if ( selected === '' ) {
			meta           = [];
			formatted_meta = '';
			return false;
		}

		var key   = $attribute_option.data( 'attribute_label' ),
			value = $attribute_option.find( 'select option:selected' ).text();

		meta.push( { meta_key: key, meta_value: value } );

		formatted_meta = formatted_meta + '<span class="meta_element"><span class="meta_key">' + $attribute_option.data( 'attribute_label' ) + ':</span> <span class="meta_value">' + $attribute_option.find( 'select option:selected' ).text();

		if ( index !== attribute_options_length - 1 ) {
			formatted_meta = formatted_meta + ', ';
		}

		formatted_meta = formatted_meta + '</span></span>';

	} );

	return formatted ? formatted_meta : meta;
}

/**
 * Element-in-viewport check with partial element detection & direction support.
 * Credit: Sam Sehnert - https://github.com/customd/jquery-visible
 */
jQuery.fn.wc_cp_is_in_viewport = function( partial, hidden, direction ) {

	var $w = $wc_cp_window;

	if ( this.length < 1 ) {
		return;
	}

	var $t         = this.length > 1 ? this.eq(0) : this,
		t          = $t.get(0),
		vpWidth    = $w.width(),
		vpHeight   = $w.height(),
		clientSize = hidden === true ? t.offsetWidth * t.offsetHeight : true;

	direction = (direction) ? direction : 'vertical';

	if ( typeof t.getBoundingClientRect === 'function'){

		// Use this native browser method, if available.
		var rec      = t.getBoundingClientRect(),
			tViz     = rec.top    >= 0 && rec.top    <  vpHeight,
			bViz     = rec.bottom >  0 && rec.bottom <= vpHeight,
			lViz     = rec.left   >= 0 && rec.left   <  vpWidth,
			rViz     = rec.right  >  0 && rec.right  <= vpWidth,
			vVisible = partial ? tViz || bViz : tViz && bViz,
			hVisible = partial ? lViz || rViz : lViz && rViz;

		if ( direction === 'both' ) {
			return clientSize && vVisible && hVisible;
		} else if ( direction === 'vertical' ) {
			return clientSize && vVisible;
		} else if ( direction === 'horizontal' ) {
			return clientSize && hVisible;
		}

	} else {

		var viewTop         = $w.scrollTop(),
			viewBottom      = viewTop + vpHeight,
			viewLeft        = $w.scrollLeft(),
			viewRight       = viewLeft + vpWidth,
			offset          = $t.offset(),
			_top            = offset.top,
			_bottom         = _top + $t.height(),
			_left           = offset.left,
			_right          = _left + $t.width(),
			compareTop      = partial === true ? _bottom : _top,
			compareBottom   = partial === true ? _top : _bottom,
			compareLeft     = partial === true ? _right : _left,
			compareRight    = partial === true ? _left : _right;

		if ( direction === 'both' ) {
			return !!clientSize && ( ( compareBottom <= viewBottom ) && ( compareTop >= viewTop ) ) && ( ( compareRight <= viewRight ) && ( compareLeft >= viewLeft ) );
		} else if ( direction === 'vertical' ) {
			return !!clientSize && ( ( compareBottom <= viewBottom ) && ( compareTop >= viewTop ) );
		} else if ( direction === 'horizontal' ) {
			return !!clientSize && ( ( compareRight <= viewRight ) && ( compareLeft >= viewLeft ) );
		}
	}
};

/**
 * Composite app object getter.
 */
jQuery.fn.wc_get_composite_script = function() {

	var $composite_form = jQuery( this );

	if ( ! $composite_form.hasClass( 'composite_form' ) ) {
		return false;
	}

	var script_id = $composite_form.data( 'script_id' );

	if ( typeof( wc_cp_composite_scripts[ script_id ] ) !== 'undefined' ) {
		return wc_cp_composite_scripts[ script_id ];
	}

	return false;
};

/*-----------------------------------------------------------------*/
/*  Encapsulation.                                                 */
/*-----------------------------------------------------------------*/

( function( $, Backbone ) {

	/*-----------------------------------------------------------------*/
	/*  Class Definitions.                                             */
	/*-----------------------------------------------------------------*/

	var wc_cp_classes = {};

	/**
	 * Composite product object. The core of the app.
	 */
	function WC_CP_Composite( data ) {

		var composite                           = this;

		this.composite_id                       = data.$composite_data.data( 'container_id' );

		/*
		 * Common jQuery DOM elements for quick, global access.
		 */
		this.$composite_data                    = data.$composite_data;
		this.$composite_form                    = data.$composite_form;
		this.$composite_add_to_cart_button      = data.$composite_form.find( '.composite_add_to_cart_button' );
		this.$composite_navigation              = data.$composite_form.find( '.composite_navigation' );
		this.$composite_navigation_top          = data.$composite_form.find( '.composite_navigation.top' );
		this.$composite_navigation_bottom       = data.$composite_form.find( '.composite_navigation.bottom' );
		this.$composite_navigation_movable      = data.$composite_form.find( '.composite_navigation.movable' );
		this.$composite_pagination              = data.$composite_form.find( '.composite_pagination' );
		this.$composite_summary                 = data.$composite_form.find( '.composite_summary' );
		this.$composite_summary_widget          = $( '.widget_composite_summary' ).filter( function() { return $( this ).find( '.widget_composite_summary_content_' + composite.composite_id ).length > 0; } );

		this.$components                        = data.$composite_form.find( '.component' );
		this.$steps                             = {};

		this.$composite_availability            = data.$composite_data.find( '.composite_availability' );
		this.$composite_price                   = data.$composite_data.find( '.composite_price' );
		this.$composite_message                 = data.$composite_data.find( '.composite_message' );
		this.$composite_message_content         = data.$composite_data.find( '.composite_message ul.msg' );
		this.$composite_button                  = data.$composite_data.find( '.composite_button' );

		/*
		 * Object properties used for some real work.
		 */
		this.timers                             = { on_resize_timer: false };

		this.ajax_url                           = wc_composite_params.use_wc_ajax === 'yes' ? woocommerce_params.wc_ajax_url : woocommerce_params.ajax_url;
		this.debug_tab_count                    = 0;

		this.settings                           = data.$composite_data.data( 'composite_settings' );

		this.is_initialized                     = false;
		this.has_transition_lock                = false;

		this.steps                              = [];
		this.step_factory                       = new wc_cp_classes.WC_CP_Step_Factory();

		// Stores and updates the active scenarios. Used by component models to calculate active scenarios excl and/or up to specific steps.
		this.scenarios                          = new wc_cp_classes.WC_CP_Scenarios_Manager( this );

		// WP-style actions dispatcher. Dispatches actions in response to key model events.
		this.actions                            = new wc_cp_classes.WC_CP_Actions_Dispatcher( this );

		// WP-style filters manager.
		this.filters                            = new wc_cp_classes.WC_CP_Filters_Manager();

		// Backbone Router.
		this.router                             = false;

		// Composite Data Model.
		this.data_model                         = false;

		// View classes. If necessary, override/extend these before any associated views are instantiated - @see 'init_views'.
		this.view_classes                       = new wc_cp_classes.WC_CP_Views( this );

		// Model classes. If necessary, override/extend these before any associated models are instantiated - @see 'init_models'.
		this.model_classes                      = new wc_cp_classes.WC_CP_Models( this );

		// Composite Views - @see 'init_views'.
		this.composite_summary_view             = false;
		this.composite_pagination_view          = false;
		this.composite_navigation_view          = false;
		this.composite_validation_view          = false;
		this.composite_availability_view        = false;
		this.composite_price_view               = false;
		this.composite_add_to_cart_button_view  = false;
		this.composite_summary_widget_views     = [];

		// API.
		this.api                                = {

			/**
			 * Navigate to a step by id.
			 *
			 * @param  string step_id
			 * @return false | void
			 */
			navigate_to_step: function( step_id ) {

				var step = composite.get_step_by( 'id', step_id );

				if ( false === step ) {
					return false;
				}

				composite.navigate_to_step( step );
			},

			/**
			 * Navigate to the previous step, if one exists.
			 *
			 * @return void
			 */
			show_previous_step: function() {

				composite.show_previous_step();
			},

			/**
			 * Navigate to the next step, if one exists.
			 *
			 * @return void
			 */
			show_next_step: function() {

				composite.show_next_step();
			},

			/**
			 * Get all created instances of WC_CP_Step.
			 *
			 * @return array
			 */
			get_steps: function() {

				return composite.get_steps();
			},

			/**
			 * Get all created instances of WC_CP_Component (inherits from WC_CP_Step).
			 *
			 * @return array
			 */
			get_components: function() {

				return composite.get_components();
			},

			/**
			 * Get the instance of WC_CP_Step based on its step_id. For components, step_id === component_id.
			 *
			 * @param  string  step_id
			 * @return WC_CP_Step | false
			 */
			get_step: function( step_id ) {

				return composite.get_step( step_id );
			},

			/**
			 * Get the instance of WC_CP_Step based on its step_id, step_index, or step_slug.
			 *
			 * - step_id: for components, step_id === component_id
			 * - step_index: zero-based index of a step
			 * - step_slug: sanitized slug obtained from the step title, used mainly by the Backbone Router to keep track of browser history when navigating between steps.
			 *
			 * @param  string  by
			 * @param  string  id
			 * @return WC_CP_Step | false
			 */
			get_step_by: function( by, id ) {

				return composite.get_step_by( by, id );
			},

			/**
			 * Get the step title of a WC_CP_Step instance based on its step_id.
			 *
			 * @param  string  step_id
			 * @return string | false
			 */
			get_step_title: function( step_id ) {

				var step = composite.get_step_by( 'id', step_id );

				if ( false === step ) {
					return false;
				}

				return step.get_title();
			},

			/**
			 * Get the step slug of a WC_CP_Step instance based on its step_id.
			 *
			 * @param  string  step_id
			 * @return string | false
			 */
			get_step_slug: function( step_id ) {

				var step = composite.get_step_by( 'id', step_id );

				if ( false === step ) {
					return false;
				}

				return step.step_slug;
			},

			/**
			 * Get the current step.
			 *
			 * @return WC_CP_Step | false
			 */
			get_current_step: function() {

				return composite.get_current_step();
			},

			/**
			 * Get the previous step.
			 *
			 * @return WC_CP_Step | false
			 */
			get_previous_step: function() {

				return composite.get_previous_step();
			},

			/**
			 * Get the next step.
			 *
			 * @return WC_CP_Step | false
			 */
			get_next_step: function() {

				return composite.get_next_step();
			},

			/**
			 * Get the current composite totals.
			 *
			 * @return object
			 */
			get_composite_totals: function() {

				return composite.data_model.get( 'totals' );
			},

			/**
			 * Get the current stock status of the composite.
			 *
			 * @return string ('in-stock' | 'out-of-stock')
			 */
			get_composite_stock_status: function() {

				return composite.data_model.get( 'is_in_stock' ) ? 'in-stock' : 'out-of-stock';
			},

			/**
			 * Get the current availability string of the composite.
			 *
			 * @return string
			 */
			get_composite_availability: function() {

				var availability = composite.composite_availability_view.get_components_availability_string();

				if ( availability === '' && false !== composite.composite_availability_view.$composite_stock_status ) {
					availability = composite.composite_availability_view.$composite_stock_status.clone().wrap( '<div></div>' ).parent().html();
				}

				return availability;
			},

			/**
			 * Get the current validation status of the composite.
			 *
			 * @return string ('pass' | 'fail')
			 */
			get_composite_validation_status: function() {

				return composite.data_model.get( 'passes_validation' ) ? 'pass' : 'fail';
			},

			/**
			 * Get the current validation messages for the composite.
			 *
			 * @return array
			 */
			get_composite_validation_messages: function() {

				return composite.data_model.get( 'validation_messages' );
			},

			/**
			 * Gets composite configuration details.
			 *
			 * @return object | false
			 */
			get_composite_configuration: function() {

				var composite_config = {};

				if ( composite.get_components().length === 0 ) {
					return false;
				}

				$.each( composite.get_components(), function( index, component ) {

					var component_config = composite.api.get_component_configuration( component.component_id );

					composite_config[ component.component_id ] = component_config;
				} );

				return composite_config;
			},

			/**
			 * Get the component price.
			 *
			 * @param  string  component_id
			 * @return object | false
			 */
			get_component_totals: function( component_id ) {

				if ( false === composite.get_step_by( 'id', component_id ) ) {
					return false;
				}

				return composite.data_model.get( 'component_' + component_id + '_totals' );
			},

			/**
			 * Get the current stock status of a component.
			 *
			 * @param  string  component_id
			 * @return string ('in-stock' | 'out-of-stock')
			 */
			get_component_stock_status: function( component_id ) {

				var component = composite.get_step_by( 'id', component_id );

				if ( false === component ) {
					return false;
				}

				return component.step_validation_model.get( 'is_in_stock' ) ? 'in-stock' : 'out-of-stock';
			},

			/**
			 * Get the current availability status of a component.
			 *
			 * @param  string  component_id
			 * @return string ('in-stock' | 'out-of-stock')
			 */
			get_component_availability: function( component_id ) {

				var component = composite.get_step_by( 'id', component_id );

				if ( false === component ) {
					return false;
				}

				var $availability = component.$component_summary_content.find( '.component_wrap .stock' );

				return $availability.length > 0 ? $availability.clone().wrap( '<div></div>' ).parent().html() : '';
			},

			/**
			 * Get the current validation status of a component.
			 *
			 * @param  string  component_id
			 * @return string ('pass' | 'fail')
			 */
			get_component_validation_status: function( component_id ) {

				var component = composite.get_step_by( 'id', component_id );

				if ( false === component ) {
					return false;
				}

				return component.step_validation_model.get( 'passes_validation' ) ? 'pass' : 'fail';
			},

			/**
			 * Get the current validation messages of a component. Context: 'component' or 'composite'.
			 *
			 * @param  string  component_id
			 * @param  string  context
			 * @return array
			 */
			get_component_validation_messages: function( component_id, context ) {

				var component = composite.get_step_by( 'id', component_id );

				if ( false === component ) {
					return false;
				}

				var messages = context === 'composite' ? component.step_validation_model.get( 'composite_messages' ) : component.step_validation_model.get( 'component_messages' );

				return messages;
			},

			/**
			 * Gets configuration details for a single component.
			 *
			 * @param  string  component_id
			 * @return object | false
			 */
			get_component_configuration: function( component_id ) {

				var component        = composite.get_step_by( 'id', component_id ),
					component_config = false;

				if ( false === component ) {
					return component_config;
				}

				component_config = {
					title:           component.get_title(),
					selection_title: component.get_selected_product_title( false ),
					selection_meta:  component.get_selected_product_meta( false ),
					product_id:      component.get_selected_product( false ),
					variation_id:    component.get_selected_variation( false ),
					product_valid:   component.is_selected_product_valid(),
					variation_valid: component.is_selected_variation_valid(),
					quantity:        component.get_selected_quantity(),
					product_type:    component.get_selected_product_type()
				};

				// Pass through 'component_configuration' filter - @see WC_CP_Filters_Manager class.
				component_config = composite.filters.apply_filters( 'component_configuration', [ component_config, component ] );

				return component_config;
			},

			/**
			 * True if the composite is priced per product.
			 *
			 * @return boolean
			 */
			is_priced_per_product: function() {

				return composite.data_model.price_data[ 'per_product_pricing' ] === 'yes';
			}
		};

		/**
		 * Script initialization.
		 */
		this.init = function() {

			/*
			 * Trigger pre-init jQuery event that 3rd party code may use for initialization.
			 */
			composite.$composite_data.trigger( 'wc-composite-initializing', [ composite ] );

			/*
			 * Init composite on the 'initialize_composite' hook - callbacks declared inline since they are not meant to be unhooked.
			 * To extend/override model/view classes, modify them from action callbacks hooked in at an earlier priority than the 'init_models' and 'init_views' calls.
			 */
			this.actions

				/*
				 * Init steps.
				 */
				.add_action( 'initialize_composite', function() {
					composite.init_steps();
				}, 10, this )

				/*
				 * Init models.
				 */
				.add_action( 'initialize_composite', function() {
					composite.init_models();
				}, 20, this )

				/*
				 * Init actions dispatcher. Dispatches actions in response to key model events.
				 */
				.add_action( 'initialize_composite', function() {
					composite.actions.init();
				}, 30, this )

				/*
				 * Trigger resize to add responsive CSS classes to form.
				 */
				.add_action( 'initialize_composite', function() {
					composite.on_resize_handler();
				}, 40, this )

				/*
				 * Init views.
				 */
				.add_action( 'initialize_composite', function() {
					composite.init_views();
				}, 50, this )

				/*
				 * Init scenarios manager. Models are initialized, so we can now start listening to component model events.
				 */
				.add_action( 'initialize_composite', function() {
					composite.scenarios.init();
				}, 60, this )

				/*
				 * Validate steps.
				 */
				.add_action( 'initialize_composite', function() {
					composite.console_log( 'debug:events', '\nValidating Steps:' );
					composite.debug_tab_count = composite.debug_tab_count + 2;
					$.each( composite.get_steps(), function( index, step ) {
						step.validate();
					} );
					composite.debug_tab_count = composite.debug_tab_count - 2;
					composite.console_log( 'debug:events', '\nValidation complete.' );
				}, 70, this )

				/*
				 * Activate initial step.
				 */
				.add_action( 'initialize_composite', function() {
					composite.get_current_step().show_step();
				}, 80, this )

				/*
				 * Init Backbone router.
				 *
				 * Works with Paged & Progressive layout composites displayed in single-product pages.
				 * Browser history will not work with composites displayed in other places, for instance composites placed in WP pages via WC shortcodes.
				 */
				.add_action( 'initialize_composite', function() {
					composite.init_router();
				}, 90, this );


			/*
			 * Run init action.
			 */
			this.actions.do_action( 'initialize_composite' );

			/*
			 * Mark as initialized.
			 */
			composite.is_initialized = true;

			/*
			 * Add post-init action hooks.
			 */
			this.actions

				/**
				 * Init data model state.
				 */
				.add_action( 'composite_initialized', function() {
					composite.data_model.init();
				}, 10, this )

				/*
				 * Finally, render all views.
				 */
				.add_action( 'composite_initialized', function() {
					composite.render_views();
				}, 20, this );

			/*
			 * Run post-init action.
			 */
			this.actions.do_action( 'composite_initialized' );
		};

		/**
		 * Init backbone router to support browser history when transitioning between steps.
		 */
		this.init_router = function() {

			var	current_step = composite.get_current_step(),
				WC_CP_Router = Backbone.Router.extend( {

					has_initial_route: false,
					is_initialized:    false,

					routes:    {
						':step_slug': 'show_step'
					},

					show_step: function( step_slug ) {

						var encoded_slug = encodeURIComponent( step_slug );
						var step         = composite.get_step_by( 'slug', encoded_slug );

						if ( step ) {

							if ( ! this.is_initialized ) {
								this.has_initial_route = true;
							}

							// If the requested step cannot be viewed, do not proceed: Show a notice and create a new history entry based on the current step.
							if ( step.is_locked() ) {
								window.alert( wc_composite_params.i18n_step_not_accessible.replace( /%s/g, step.get_title() ) );
								composite.router.navigate( composite.get_current_step().step_slug );
							// Otherwise, scroll the viewport to the top and show the requested step.
							} else {
								if ( this.is_initialized ) {
									wc_cp_scroll_viewport( composite.$composite_form, { timeout: 0, partial: false, duration: 0, queue: false } );
								}
								step.show_step();
							}
						}
					}

				} );


			if ( $wc_cp_body.hasClass( 'single-product' ) && $wc_cp_body.hasClass( 'postid-' + composite.composite_id ) ) {

				composite.router = new WC_CP_Router();

				// Start recording history and trigger the initial route.
				Backbone.history.start();

				// Set router as initialized.
				composite.router.is_initialized = true;

				// If no initial route exists, find the initial route as defined by the served markup and write it to the history without triggering it.
				if ( composite.settings.layout !== 'single' && false === composite.router.has_initial_route && ! window.location.hash ) {
					composite.router.navigate( current_step.step_slug, { trigger: false } );
				}
			}
		};

		/**
		 * Initialize composite step objects.
		 */
		this.init_steps = function() {

			composite.console_log( 'debug:events', '\nInitializing Steps...' );

			/*
			 * Prepare markup for "Review" step, if needed.
			 */
			if ( composite.settings.layout === 'paged' ) {

				// Componentized layout: replace the step-based process with a summary-based process.
				if ( composite.settings.layout_variation === 'componentized' ) {

					composite.$composite_form.find( '.multistep.active' ).removeClass( 'active' );
					composite.$composite_data.addClass( 'multistep active' );

					// No review step in the pagination template.
					composite.$composite_pagination.find( '.pagination_element_review' ).remove();

					// No summary widget.
					composite.$composite_summary_widget.hide();

				// If the composite-add-to-cart.php template is added right after the component divs, it will be used as the final step of the step-based configuration process.
				} else if ( composite.$composite_data.prev().hasClass( 'multistep' ) ) {

					composite.$composite_data.addClass( 'multistep' );
					composite.$composite_data.hide();

					// If the composite was just added to the cart, make the review/summary step active.
					if ( composite.$composite_data.hasClass( 'composite_added_to_cart' ) ) {
						composite.$composite_form.find( '.multistep.active' ).removeClass( 'active' );
						composite.$composite_data.addClass( 'active' );
					}

				} else {
					composite.$composite_data.show();
					composite.$composite_data.find( '.component_title' ).hide();
					composite.$composite_pagination.find( '.pagination_element_review' ).remove();
				}

			} else if ( composite.settings.layout === 'progressive' ) {

				composite.$components.show();
				composite.$composite_data.show();

			} else if ( composite.settings.layout === 'single' ) {

				composite.$components.show();
				composite.$composite_data.show();
			}

			/*
			 * Initialize step objects.
			 */

			composite.$steps = composite.$composite_form.find( '.multistep' );

			composite.$composite_form.children( '.component, .multistep' ).each( function( index ) {

				var step = composite.step_factory.create_step( composite, $( this ), index );
				composite.steps[ index ] = step;

			} );

			composite.$composite_navigation.removeAttr( 'style' );
		};

		/**
		 * Ajax URL.
		 */
		this.get_ajax_url = function( action ) {

			return wc_composite_params.use_wc_ajax === 'yes' ? this.ajax_url.toString().replace( '%%endpoint%%', action ) : this.ajax_url;
		};

		/**
		 * Shows a step and updates the history as required.
		 */
		this.navigate_to_step = function( step ) {

			if ( typeof( step ) === 'object' && typeof( step.show_step ) === 'function' ) {
				step.show_step();

				if ( this.allow_history_updates() ) {
					this.router.navigate( step.step_slug );
				}
			}
		};

		/**
		 * True when updating browser history.
		 */
		this.allow_history_updates = function() {

			return ( false !== composite.router && 'yes' === this.settings.update_browser_history && composite.is_initialized );
		};

		/**
		 * Shows the step marked as previous from the current one.
		 */
		this.show_previous_step = function() {

			$.each( composite.get_steps(), function( step_index, step ) {

				if ( step.is_previous() ) {
					composite.navigate_to_step( step );
					return false;
				}
			} );
		};

		/**
		 * Shows the step marked as next from the current one.
		 */
		this.show_next_step = function() {

			$.each( composite.get_steps(), function( step_index, step ) {

				if ( step.is_next() ) {
					composite.navigate_to_step( step );
					return false;
				}
			} );
		};

		/**
		 * Returns step objects.
		 */
		this.get_steps = function() {

			return this.steps;
		};

		/**
		 * Returns step objects that are components.
		 */
		this.get_components = function() {

			var components = [];

			$.each( this.steps, function( step_index, step ) {

				if ( step.is_component() ) {
					components.push( step );
				}

			} );

			return components;
		};

		/**
		 * Returns a step object by id.
		 */
		this.get_step = function( step_id ) {

			var found = false;

			$.each( composite.get_steps(), function( step_index, step ) {

				if ( step.step_id == step_id ) {
					found = step;
					return false;
				}

			} );

			return found;
		};

		/**
		 * Returns a step object by id/index.
		 */
		this.get_step_by = function( by, id ) {

			var found = false;

			if ( by !== 'id' && by !== 'index' && by !== 'slug' ) {
				return false;
			}

			$.each( composite.get_steps(), function( step_index, step ) {

				if ( ( by === 'id' && String( step.step_id ) === String( id ) ) || ( by === 'index' && String( step_index ) === String( id ) ) || ( by === 'slug' && String( step.step_slug ).toUpperCase() === String( id ).toUpperCase() ) ) {
					found = step;
					return false;
				}

			} );

			return found;

		};

		/**
		 * Returns the current step object.
		 */
		this.get_current_step = function() {

			var current = false;

			$.each( composite.get_steps(), function( step_index, step ) {

				if ( step.is_current() ) {
					current = step;
					return false;
				}

			} );

			return current;
		};

		/**
		 * Returns the previous step object.
		 */
		this.get_previous_step = function() {

			var previous = false;

			$.each( composite.get_steps(), function( step_index, step ) {

				if ( step.is_previous() ) {
					previous = step;
					return false;
				}

			} );

			return previous;
		};

		/**
		 * Returns the next step object.
		 */
		this.get_next_step = function() {

			var next = false;

			$.each( composite.get_steps(), function( step_index, step ) {

				if ( step.is_next() ) {
					next = step;
					return false;
				}

			} );

			return next;
		};

		/**
		 * Handler for viewport resizing.
		 */
		this.on_resize_handler = function() {

			// Add responsive classes to composite form.

			var form_width = composite.$composite_form.width();

			if ( form_width <= wc_composite_params.small_width_threshold ) {
				composite.$composite_form.addClass( 'small_width' );
			} else {
				composite.$composite_form.removeClass( 'small_width' );
			}

			if ( form_width > wc_composite_params.full_width_threshold ) {
				composite.$composite_form.addClass( 'full_width' );
			} else {
				composite.$composite_form.removeClass( 'full_width' );
			}

			if ( wc_composite_params.legacy_width_threshold ) {
				if ( form_width <= wc_composite_params.legacy_width_threshold ) {
					composite.$composite_form.addClass( 'legacy_width' );
				} else {
					composite.$composite_form.removeClass( 'legacy_width' );
				}
			}

			// Reset relocated container if in wrong position.

			if ( composite.is_initialized ) {
				$.each( composite.get_components(), function( index, component ) {

					if ( component.component_selection_view.is_relocated() ) {

						var relocation_params = component.component_selection_view.get_content_relocation_params();

						if ( relocation_params.relocate ) {

							var $relocation_target    = component.$component_options.find( '.component_option_content_container' ),
								$relocation_reference = relocation_params.reference;

							$relocation_reference.after( $relocation_target );
						}
					}
				} );
			}
		};

		/**
		 * Log stuff in the console.
		 */
		this.console_log = function( context, message ) {

			if ( window.console && typeof( message ) !== 'undefined' ) {
				var log = false;

				if ( context === 'error' ) {
					log = true;
				} else if ( _.contains( wc_composite_params.script_debug_level, context ) ) {
					log = true;
				} else {
					$.each( wc_composite_params.script_debug_level, function( index, debug_level_context ) {
						if ( context.indexOf( debug_level_context ) > -1 ) {
							log = true;
							return false;
						}
					} );
				}

				if ( log ) {
					var tabs = '';
					for ( var i = composite.debug_tab_count; i > 0; i-- ) {
						tabs = tabs + '	';
					}
					if ( typeof( message.substring ) === 'function' && message.substring( 0, 1 ) === '\n' ) {
						message = message.replace( '\n', '\n' + tabs );
					} else {
						message = tabs + message;
					}

					window.console.log( message );
				}
			}
		};

		/**
		 * Creates all necessary composite- and step/component-level models.
		 */
		this.init_models = function() {

			/*
		 	 * Step models associated with the validation status and access permission status of a step.
		 	 */
			$.each( composite.get_steps(), function( step_index, step ) {
				step.step_validation_model = new composite.model_classes.Step_Validation_Model( step );
				step.step_access_model     = new composite.model_classes.Step_Access_Model( step );
			} );

			/*
		 	 * Component models associated with component options and component selections.
		 	 */
			$.each( composite.get_components(), function( index, component ) {
				component.component_options_model   = new composite.model_classes.Component_Options_Model( component );
				component.component_selection_model = new composite.model_classes.Component_Selection_Model( component );
			} );

			/*
		 	 * Composite product data model for storing validation, pricing, availability and quantity data.
		 	 */
			composite.data_model = new composite.model_classes.Composite_Data_Model();
		};

		/**
		 * Creates:
		 *
		 *  - Composite product views responsible for updating the composite availability, pricing and add-to-cart button located in: i) the composite form and ii) summary widgets.
		 *  - Composite product views responsible for updateing the navigation, pagination and summary elements.
		 *  - All necessary step & component views associated with the display of validation messages, component selection details and component options.
		 */
		this.init_views = function() {

			composite.console_log( 'debug:events', '\nInitializing Views...' );

			/*
			 * Instantiate composite views.
			 */
			this.composite_validation_view = new composite.view_classes.Composite_Validation_View( {
				is_in_widget: false,
				el:           composite.$composite_message,
				$el_content:  composite.$composite_message_content,
				model:        composite.data_model,
			} );

			this.composite_price_view = new composite.view_classes.Composite_Price_View( {
				is_in_widget: false,
				el:           composite.$composite_price,
				model:        composite.data_model,
			} );

			this.composite_availability_view = new composite.view_classes.Composite_Availability_View( {
				is_in_widget: false,
				el:           composite.$composite_availability,
				model:        composite.data_model,
			} );

			this.composite_add_to_cart_button_view = new composite.view_classes.Composite_Add_To_Cart_Button_View( {
				is_in_widget: false,
				el:           composite.$composite_button,
				$el_button:   composite.$composite_add_to_cart_button,
				model:        composite.data_model,
			} );

			if ( composite.$composite_pagination.length > 0 ) {
				composite.composite_pagination_view = new composite.view_classes.Composite_Pagination_View( { el: composite.$composite_pagination } );
			}

			if ( composite.$composite_summary.length > 0 ) {
				composite.composite_summary_view = new composite.view_classes.Composite_Summary_View( { is_in_widget: false, el: composite.$composite_summary } );
			}

			if ( composite.$composite_navigation.length > 0 ) {
				composite.composite_navigation_view = new composite.view_classes.Composite_Navigation_View( { el: composite.$composite_navigation } );
			}

			if ( composite.$composite_summary_widget.length > 0 ) {
				composite.$composite_summary_widget.each( function( index, $widget ) {
					composite.composite_summary_widget_views.push( new composite.view_classes.Composite_Widget_View( { widget_count: index + 1, el: $widget } ) );
				} );
			}

			/*
			 * Initialize step/component views.
			 */
			$.each( composite.get_steps(), function( step_index, step ) {
				step.validation_view = new composite.view_classes.Step_Validation_View( step, { el: step.$component_message, model: step.step_validation_model } );
			} );

			$.each( composite.get_components(), function( index, component ) {
				component.component_options_view   = new composite.view_classes.Component_Options_View( component, { el: this.$component_options, model: this.component_options_model } );
				component.component_selection_view = new composite.view_classes.Component_Selection_View( component, { el: this.$component_content, model: this.component_selection_model } );
				component.component_title_view     = new composite.view_classes.Component_Title_View( component, { el: component.$el.find( '.component_title' ) } );
			} );

			/*
			 * Initialize component selection view scripts.
			 */
			$.each( composite.get_components(), function( index, component ) {
				component.component_selection_view.init_dependencies();
			} );
		};

		/**
		 * Renders component options views and the composite pagination, navigation and summary template views.
		 */
		this.render_views = function() {

			composite.console_log( 'debug:views', '\nRendering Views...' );
			composite.debug_tab_count = composite.debug_tab_count + 2;

			$.each( composite.get_components(), function( index, component ) {
				component.component_selection_view.update_selection_title();
				component.component_options_view.render_options_state();
				component.component_title_view.render_navigation_state();

				// Run 'component_options_loaded' action - @see WC_CP_Actions_Dispatcher class reference.
				composite.actions.do_action( 'component_options_loaded', [ component ] );
			} );

			if ( false !== composite.composite_pagination_view ) {
				composite.composite_pagination_view.render();
			}
			if ( false !== composite.composite_summary_view ) {
				composite.composite_summary_view.render();
			}
			if ( false !== composite.composite_navigation_view ) {
				composite.composite_navigation_view.render( 'transition' );
			}

			$.each( composite.composite_summary_widget_views, function( index, view ) {
				view.composite_summary_view.render();
			} );

			composite.debug_tab_count = composite.debug_tab_count - 2;
			composite.console_log( 'debug:views', '\nRendering complete.' );

			/*
			 * Get rid of no-js notice and classes.
			 */
			composite.$composite_form.removeClass( 'cp-no-js' );
			composite.$composite_form.find( '.cp-no-js-msg' ).remove();
		};

		/**
		 * Blocks the composite form and adds a waiting ui cue in the passed elements.
		 */
		this.block = function( $waiting_for ) {

			this.$composite_form.block( wc_cp_block_params );
			$waiting_for.block( wc_cp_block_wait_params );
			composite.has_transition_lock = true;
		};

		/**
		 * Unblocks the composite form and removes the waiting ui cue from the passed elements.
		 */
		this.unblock = function( $waiting_for ) {

			this.$composite_form.unblock();
			$waiting_for.unblock();
			composite.has_transition_lock = false;
		};
	}

	/*
	 * Load classes from external files to keep things tidy.
	 */


	/**
	 * Model classes instantiated in a CP app lifecycle.
	 */
	wc_cp_classes.WC_CP_Models = function( composite ) {

		/**
		 * Composite product data model for storing validation, pricing, availability and quantity data.
		 */
		this.Composite_Data_Model = function( opts ) {

			var Model = Backbone.Model.extend( {

				price_data:  composite.$composite_data.data( 'price_data' ),
				$nyp:        false,

				initialize: function() {

					var params = {
						passes_validation:     true,
						validation_messages:   [],
						is_in_stock:           true,
						stock_statuses:        [],
						totals:                { price: '', regular_price: '', price_incl_tax: '', price_excl_tax: '' }
					};

					$.each( composite.get_components(), function( index, component ) {
						params[ 'component_' + component.component_id + '_totals' ] = { price: '', regular_price: '', price_incl_tax: '', price_excl_tax: '' };
					} );

					this.set( params );

					// Price suffix data.
					this.price_data.suffix_exists              = wc_composite_params.price_display_suffix !== '';
					this.price_data.suffix_contains_price_incl = wc_composite_params.price_display_suffix.indexOf( '{price_including_tax}' ) > -1;
					this.price_data.suffix_contains_price_excl = wc_composite_params.price_display_suffix.indexOf( '{price_excluding_tax}' ) > -1;

					/**
					 * Update model totals when the selected addons change.
					 */
					composite.actions.add_action( 'component_addons_changed', this.addons_changed_handler, 10, this );

					/**
					 * Update model totals when the nyp price changes.
					 */
					composite.actions.add_action( 'component_nyp_changed', this.nyp_changed_handler, 10, this );

					/**
					 * Update model totals and availability state when a new component quantity is selected.
					 */
					composite.actions.add_action( 'component_quantity_changed', this.quantity_changed_handler, 10, this );

					/**
					 * Update model totals and availability state when a new selection is made.
					 */
					composite.actions.add_action( 'component_selection_changed', this.selection_changed_handler, 30, this );

					/**
					 * Update availability state when the contents of an existing selection change.
					 */
					composite.actions.add_action( 'component_selection_content_changed', this.selection_content_changed_handler, 30, this );

					/**
					 * Update model validation state when a step validation model state changes.
					 */
					composite.actions.add_action( 'component_validation_message_changed', this.validation_message_changed_handler, 10, this );

					/**
					 * Update a single summary view element price when its totals change.
					 */
					composite.actions.add_action( 'component_totals_changed', this.component_totals_changed_handler, 10, this );

					/**
					 * Update composite totals when a new NYP price is entered at composite level.
					 */
					var $nyp = composite.$composite_data.find( '.nyp' );

					if ( $nyp.length > 0 ) {

						this.$nyp                       = $nyp;
						this.price_data[ 'base_price' ] = $nyp.data( 'price' );

						composite.$composite_data.on( 'woocommerce-nyp-updated-item', { model: this }, function( event ) {

							var model = event.data.model;

							model.price_data[ 'base_price' ]         = model.$nyp.data( 'price' );
							model.price_data[ 'base_regular_price' ] = model.$nyp.data( 'price' );

							model.update_totals();
						} );
					}
				},

				/**
				 * Initializes the model and prepares data for consumption by views.
				 */
				init: function() {

					composite.console_log( 'debug:models', '\nInitializing composite data model...' );
					composite.debug_tab_count = composite.debug_tab_count + 2;

					this.update_validation();
					this.update_totals();
					this.update_availability();

					composite.debug_tab_count = composite.debug_tab_count - 2;
				},

				/**
				 * Updates component totals when an addons change event is triggered.
				 */
				addons_changed_handler: function( component ) {

					if ( ! composite.is_initialized ) {
						return false;
					}

					this.update_totals( component );
				},

				/**
				 * Updates component totals when a nyp price change event is triggered.
				 */
				nyp_changed_handler: function( component ) {

					if ( ! composite.is_initialized ) {
						return false;
					}

					this.update_totals( component );
				},

				/**
				 * Updates model totals and availability state.
				 */
				selection_changed_handler: function() {

					if ( ! composite.is_initialized ) {
						return false;
					}

					this.update_totals();
					this.update_availability();
				},

				/**
				 * Updates model availability state.
				 */
				selection_content_changed_handler: function() {

					if ( ! composite.is_initialized ) {
						return false;
					}

					this.update_availability();
				},

				/**
				 * Updates model totals and availability state.
				 */
				quantity_changed_handler: function( component ) {

					if ( ! composite.is_initialized ) {
						return false;
					}

					this.update_totals( component );
					this.update_availability();
				},

				/**
				 * Updates model validation state when the state of a step validation model changes.
				 */
				validation_message_changed_handler: function() {
					this.update_validation();
				},

				// Updates totals when component subtotals change.
				component_totals_changed_handler: function() {
					this.calculate_totals();
				},

				/**
				 * Updates the validation state of the model.
				 */
				update_validation: function() {

					var messages = [];

					if ( this.is_purchasable() ) {
						messages = this.get_validation_messages();
					} else {
						messages.push( wc_composite_params.i18n_unavailable_text );
					}

					composite.console_log( 'debug:models', '\nUpdating \'Composite_Data_Model\' validation state... Attribute count: "validation_messages": ' + messages.length + ', Attribute: "passes_validation": ' + ( messages.length === 0 ).toString() );

					composite.debug_tab_count = composite.debug_tab_count + 2;
					this.set( { validation_messages: messages, passes_validation: messages.length === 0 } );
					composite.debug_tab_count = composite.debug_tab_count - 2;
				},

				/**
				 * Get all validation messages grouped by source. Messages added from the 'Review' step are displayed individually.
				 */
				get_validation_messages: function() {

					var validation_messages = [];

					$.each( composite.get_steps(), function( step_index, step ) {

						var source = step.get_title();

						$.each( step.get_validation_messages( 'composite' ), function( message_index, message ) {

							if ( step.is_review() ) {
								validation_messages.push( { sources: false, content: message.toString() } );
							} else {
								var appended = false;

								if ( validation_messages.length > 0 ) {
									$.each( validation_messages, function( id, msg ) {
										if ( msg.content === message ) {
											var sources_new = msg.sources;
											var content_new = msg.content;
											sources_new.push( source );
											validation_messages[ id ] = { sources: sources_new, content: content_new };
											appended = true;
											return false;
										}
									} );
								}

								if ( ! appended ) {
									validation_messages.push( { sources: [ source ], content: message.toString() } );
								}
							}

						} );

					} );

					var messages = [];

					if ( validation_messages.length > 0 ) {
						$.each( validation_messages, function( id, msg ) {
							if ( msg.sources === false ) {
								messages.push( msg.content );
							} else {
								var sources = wc_cp_join( msg.sources );
								messages.push( wc_composite_params.i18n_validation_issues_for.replace( '%c', sources ).replace( '%e', msg.content ) );
							}
						} );
					}

					// Pass through 'composite_validation_messages' filter - @see WC_CP_Filters_Manager class.
					messages = composite.filters.apply_filters( 'composite_validation_messages', [ messages ] );

					return messages;
				},

				/**
				 * True if the product is purchasable.
				 */
				is_purchasable: function() {

					if ( this.price_data[ 'is_purchasable' ] === 'no' ) {
						return false;
					}

					return true;
				},

				/**
				 * Updates model availability state.
				 */
				update_availability: function() {

					var stock_statuses = [],
						is_in_stock    = true;

					$.each( composite.get_components(), function( index, component ) {
						stock_statuses.push( component.step_validation_model.get( 'is_in_stock' ) );
					} );

					is_in_stock = _.contains( stock_statuses, false ) ? false : true;

					composite.console_log( 'debug:models', '\nUpdating \'Composite_Data_Model\' availability... Attribute: "stock_statuses": ' + stock_statuses.toString() + ', Attribute: "is_in_stock": ' + is_in_stock.toString() );

					composite.debug_tab_count = composite.debug_tab_count + 2;
					this.set( {
						stock_statuses: stock_statuses,
						is_in_stock:    is_in_stock,
					} );
					composite.debug_tab_count = composite.debug_tab_count - 2;
				},

				/**
				 * Calculates and updates model subtotals.
				 */
				update_totals: function( component ) {

					var model = this;

					composite.console_log( 'debug:models', '\nUpdating \'Composite_Data_Model\' totals...' );

					composite.debug_tab_count = composite.debug_tab_count + 2;

					if ( typeof( component ) === 'undefined' ) {

						$.each( composite.get_components(), function( index, component ) {
							model.update_component_prices( component );
						} );

						this.calculate_subtotals();

					} else {
						this.update_component_prices( component );
						this.calculate_subtotals( component );
					}

					composite.debug_tab_count = composite.debug_tab_count - 2;
				},

				/**
				 * Adds model subtotals and calculates model totals.
				 */
				calculate_totals: function( price_data_array ) {

					var model                = this,
						price_data           = typeof( price_data_array ) === 'undefined' ? model.price_data : price_data_array,
						base_price           = Number( price_data[ 'base_price' ] ),
						base_regular_price   = Number( price_data[ 'base_regular_price' ] ),
						base_price_tax_ratio = Number( price_data[ 'base_price_tax' ] ),
						base_price_totals;

					composite.console_log( 'debug:models', '\nAdding totals...' );

					base_price_totals = this.get_taxed_totals( base_price, base_regular_price, base_price_tax_ratio );

					price_data[ 'base_display_price' ] = base_price_totals.price;

					price_data[ 'total' ]              = base_price_totals.price;
					price_data[ 'regular_total' ]      = base_price_totals.regular_price;

					price_data[ 'total_incl_tax' ]     = base_price_totals.price_incl_tax;
					price_data[ 'total_excl_tax' ]     = base_price_totals.price_excl_tax;

					$.each( composite.get_components(), function( index, component ) {

						var component_totals = typeof( price_data_array ) === 'undefined' ? model.get( 'component_' + component.component_id + '_totals' ) : price_data_array[ 'component_' + component.component_id + '_totals' ];

						// Pass through 'component_totals' filter - @see WC_CP_Filters_Manager class.
						component_totals = composite.filters.apply_filters( 'component_totals', [ component_totals, component ] );

						price_data[ 'total' ]          += component_totals.price;
						price_data[ 'regular_total' ]  += component_totals.regular_price;

						price_data[ 'total_incl_tax' ] += component_totals.price_incl_tax;
						price_data[ 'total_excl_tax' ] += component_totals.price_excl_tax;
					} );

					var totals = {
						price:          price_data[ 'total' ],
						regular_price:  price_data[ 'regular_total' ],
						price_incl_tax: price_data[ 'total_incl_tax' ],
						price_excl_tax: price_data[ 'total_excl_tax' ]
					};

					// Pass through 'composite_totals' filter - @see WC_CP_Filters_Manager class.
					totals = composite.filters.apply_filters( 'composite_totals', [ totals ] );

					if ( typeof( price_data_array ) === 'undefined' ) {
						composite.debug_tab_count = composite.debug_tab_count + 2;
						this.set( { totals: totals } );
						composite.debug_tab_count = composite.debug_tab_count - 2;
					} else {
						return totals;
					}
				},

				/**
				 * Calculates totals by applying tax ratios to raw prices.
				 */
				get_taxed_totals: function( price, regular_price, tax_ratio ) {

					var totals = {
						price:          price,
						regular_price:  regular_price,
						price_incl_tax: price,
						price_excl_tax: price
					};

					if ( tax_ratio > 0 ) {

						if ( wc_composite_params.prices_include_tax === 'yes' ) {
							totals.price_incl_tax = price;
							totals.price_excl_tax = price / tax_ratio;
						} else {
							totals.price_incl_tax = price * tax_ratio;
							totals.price_excl_tax = price;
						}

						if ( wc_composite_params.tax_display_shop === 'incl' ) {
							totals.price = totals.price_incl_tax;
							if ( wc_composite_params.prices_include_tax === 'yes' ) {
								totals.regular_price = regular_price;
							} else {
								totals.regular_price = regular_price * tax_ratio;
							}
						} else {
							totals.price = totals.price_excl_tax;
							if ( wc_composite_params.prices_include_tax === 'yes' ) {
								totals.regular_price = regular_price / tax_ratio;
							} else {
								totals.regular_price = regular_price;
							}
						}
					}

					return totals;
				},

				/**
				 * Calculates composite subtotals (component totals) and updates the component totals attributes on the model when the calculation is done on the client side.
				 * For components that require a server-side calculation of incl/excl tax totals, a request is prepared and submitted in order to get accurate values.
				 */
				calculate_subtotals: function( triggered_by, price_data_array ) {

					var model      = this,
						price_data = typeof( price_data_array ) === 'undefined' ? model.price_data : price_data_array;

					triggered_by = typeof( triggered_by ) === 'undefined' ? false : triggered_by;

					$.each( composite.get_components(), function( index, component ) {

						if ( false !== triggered_by && triggered_by.component_id !== component.component_id ) {
							return true;
						}

						var totals        = {
								price:          0.0,
								regular_price:  0.0,
								price_incl_tax: 0.0,
								price_excl_tax: 0.0
							},

							qty           = price_data[ 'quantities' ][ component.component_id ],
							product_id    = component.get_selected_product_type() === 'variable' ? component.get_selected_variation( false ) : component.get_selected_product( false ),

							tax_ratio     = price_data[ 'prices_tax' ][ component.component_id ],

							regular_price = qty * ( price_data[ 'regular_prices' ][ component.component_id ] + price_data[ 'addons_prices' ][ component.component_id ] ),
							price         = qty * ( price_data[ 'prices' ][ component.component_id ] + price_data[ 'addons_prices' ][ component.component_id ] );


						composite.console_log( 'debug:models', 'Calculating "' + component.get_title() + '" totals...' );

						if ( wc_composite_params.calc_taxes === 'yes' ) {

							if ( product_id > 0 && qty > 0 && ( price > 0 || regular_price > 0 ) ) {

								totals = model.get_taxed_totals( price, regular_price, tax_ratio );
							}

						} else {

							totals.price          = price;
							totals.regular_price  = regular_price;
							totals.price_incl_tax = price;
							totals.price_excl_tax = price;
						}

						if ( typeof( price_data_array ) === 'undefined' ) {

							composite.console_log( 'debug:models', 'Updating \'Composite_Data_Model\' component totals... Attribute: "component_' + component.component_id + '_totals".' );

							composite.debug_tab_count = composite.debug_tab_count + 2;
							model.set( 'component_' + component.component_id + '_totals', totals );
							composite.debug_tab_count = composite.debug_tab_count - 2;

						} else {
							price_data[ 'component_' + component.component_id + '_totals' ] = totals;
						}

					} );

					if ( typeof( price_data_array ) !== 'undefined' ) {
						return price_data;
					}
				},

				/**
				 * Updates the 'price_data' model property with the latest component prices.
				 */
				update_component_prices: function( component ) {

					composite.console_log( 'debug:models', 'Fetching "' + component.get_title() + '" price data...' );

					var quantity    = component.get_selected_quantity(),
						custom_data = component.$component_data.data( 'custom' );

					// Copy prices.
					this.price_data[ 'prices' ][ component.component_id ]         = Number( component.$component_data.data( 'price' ) );
					this.price_data[ 'regular_prices' ][ component.component_id ] = Number( component.$component_data.data( 'regular_price' ) );

					this.price_data[ 'prices_tax' ][ component.component_id ]     = 1.0;

					if ( typeof custom_data !== 'undefined' && custom_data[ 'price_tax' ] !== 'undefined' ) {
						this.price_data[ 'prices_tax' ][ component.component_id ] = Number( custom_data[ 'price_tax' ] );
					}

					// Calculate addons price.
					this.price_data[ 'addons_prices' ][ component.component_id ]  = Number( component.component_selection_model.get( 'selected_addons' ) );

					if ( quantity > 0 ) {
						this.price_data[ 'quantities' ][ component.component_id ] = parseInt( quantity );
					} else {
						this.price_data[ 'quantities' ][ component.component_id ] = 0;
					}
				}

			} );

			var obj = new Model( opts );
			return obj;
		};

		/**
		 * Validates the configuration state of a step.
		 */
		this.Step_Validation_Model = function( step, opts ) {

			var self  = step;
			var Model = Backbone.Model.extend( {

				initialize: function() {

					var params = {
						passes_validation:  true,
						is_in_stock:        true,
						component_messages: [],
						composite_messages: [],
					};

					this.set( params );

					/**
					 * Re-validate step when a new selection is made.
					 */
					composite.actions.add_action( 'component_selection_changed', this.selection_changed_handler, 20, this );

					/**
					 * Re-validate step when the contents of an existing selection change.
					 */
					composite.actions.add_action( 'component_selection_content_changed', this.selection_content_changed_handler, 20, this );
				},

				selection_changed_handler: function() {
					if ( composite.is_initialized ) {
						self.validate();
					}
				},

				selection_content_changed_handler: function( step ) {
					if ( composite.is_initialized ) {
						if ( step.step_id === self.step_id ) {
							self.validate();
						}
					}
				},

				update: function( is_valid, is_in_stock ) {

					var params = {
						passes_validation:  is_valid,
						is_in_stock:        is_in_stock,
						component_messages: self.get_validation_messages( 'component' ),
						composite_messages: self.get_validation_messages( 'composite' )
					};

					composite.console_log( 'debug:models', '\nUpdating \'Step_Validation_Model\': "' + self.get_title() + '", Attribute: "passes_validation": ' + params.passes_validation.toString() + ', Attribute: "is_in_stock": ' + params.is_in_stock.toString() );

					if ( this.get( 'passes_validation' ) !== params.passes_validation ) {
						composite.console_log( 'debug:models', 'Validation state changed.\n' );
					} else {
						composite.console_log( 'debug:models', 'Validation state unchanged.\n' );
					}

					if ( this.get( 'component_messages' ).length !== params.component_messages.length || this.get( 'component_messages' ).length !== _.intersection( this.get( 'component_messages' ), params.component_messages ).length ) {
						composite.console_log( 'debug:models', 'Validation message changed.\n' );
					} else {
						composite.console_log( 'debug:models', 'Validation message unchanged.\n' );
					}

					if ( this.get( 'is_in_stock' ) !== params.is_in_stock ) {
						composite.console_log( 'debug:models', 'Stock state changed.\n' );
					} else {
						composite.console_log( 'debug:models', 'Stock state unchanged.\n' );
					}

					composite.debug_tab_count = composite.debug_tab_count + 2;
					this.set( params );
					composite.debug_tab_count = composite.debug_tab_count - 2;
				}

			} );

			var obj = new Model( opts );
			return obj;
		};

		/**
		 * Controls permission for access to a step.
		 */
		this.Step_Access_Model = function( step, opts ) {

			var self  = step;
			var Model = Backbone.Model.extend( {

				initialize: function() {

					var params = {
						is_locked: false,
					};

					var prev_step = self.step_index > 0 ? composite.steps[ self.step_index - 1 ] : false;

					this.set( params );

					/*
					 * Lock state changes only if:
					 *
					 * - Previous step exists.
					 * - Previous step is not a review step.
					 * - Layout is not the 'stacked' one
					 * - Layout is not the 'componentized' one, unless the 'composite_settings.sequential_componentized_progress' option is on.
					 * - Layout is the 'componentized' one, the 'composite_settings.sequential_componentized_progress' option is on and the step is not the Review.
					 */
					if ( prev_step && ! prev_step.is_review() && composite.settings.layout !== 'single' && ( composite.settings.layout_variation !== 'componentized' || composite.settings.sequential_componentized_progress === 'yes' && ! self.is_review() ) ) {
						// Update lock state when validation state of previous step changes.
						this.listenTo( prev_step.step_validation_model, 'change:passes_validation', this.update_lock_state );
						// Update lock state when lock state of previous step changes.
						this.listenTo( prev_step.step_access_model, 'change:is_locked', this.update_lock_state );
					}
				},

				update_lock_state: function() {

					var prev_step = composite.steps[ self.step_index - 1 ],
						lock      = false;

					if ( prev_step.step_access_model.get( 'is_locked' ) ) {
						lock = true;
					} else if ( ! prev_step.step_validation_model.get( 'passes_validation' ) ) {
						lock = true;
					}

					composite.console_log( 'debug:models', '\nUpdating \'Step_Access_Model\': "' + self.get_title() + '", Attribute: "is_locked": ' + lock.toString() );

					if ( this.get( 'is_locked' ) !== lock ) {
						composite.console_log( 'debug:models', 'Lock state changed.\n' );
					} else {
						composite.console_log( 'debug:models', 'Lock state unchanged.\n' );
					}

					composite.debug_tab_count = composite.debug_tab_count + 2;
					this.set( { is_locked: lock } );
					composite.debug_tab_count = composite.debug_tab_count - 2;
				}

			} );

			var obj = new Model( opts );
			return obj;
		};

		/**
		 * Sorting, filtering and pagination data and methods associated with the available component options.
		 */
		this.Component_Options_Model = function( component, opts ) {

			var self  = component;
			var Model = Backbone.Model.extend( {

				initialize: function() {

					var available_options = self.find_available_options();

					var params = {
						filters:           self.find_active_filters(),
						orderby:           self.find_order_by(),
						current_page:      self.find_pagination_param( 'current_page' ),

						available_options: available_options,
						active_options:    available_options.slice(),
					};

					this.set( params );

					if ( composite.settings.layout === 'single' ) {

						/**
						 * Update the 'active_options' attribute when the active scenarios are updated.
						 */
						composite.actions.add_action( 'active_scenarios_updated', this.refresh_options_state, 10, this );

					} else {

						/**
						 * Update the 'active_options' attribute when the active scenarios change.
						 */
						composite.actions.add_action( 'active_scenarios_changed', this.refresh_options_state, 10, this );

						/**
						 * Ensure options state is refreshed when the available options of this component change.
						 */
						composite.actions.add_action( 'available_options_changed_' + self.step_id, this.available_options_changed_handler, 10, this );

						/*
						 * Reset invalid product/variation selections when transitioning to this step.
						 */
						composite.actions.add_action( 'active_step_changed_' + self.step_id, this.active_step_changed_handler, 10, this );
					}

				},

				active_step_changed_handler: function() {

					this.refresh_options_state( self );
				},

				available_options_changed_handler: function() {

					this.refresh_options_state( self );
				},

				request_options: function( params, request_type ) {

					var model = this;

					this.set( params );

					var data = {
						action:           'woocommerce_show_component_options',
						load_page:        this.get( 'current_page' ),
						component_id:     self.component_id,
						composite_id:     composite.composite_id,
						selected_option:  self.get_selected_product( false ),
						filters:          this.get( 'filters' ),
						orderby:          this.get( 'orderby' ),
					};

					// Get component options via ajax.
					$.post( composite.get_ajax_url( data.action ), data, function( response ) {
						model.trigger( 'component_options_data_loaded', response, request_type );
					}, 'json' );
				},

				refresh_options: function( options ) {

					composite.console_log( 'debug:models', '\nUpdating \'Component_Options_Model\': "' + self.get_title() + '", Attribute: "available_options": ' + _.map( options, function( num ) { return num === '' ? '0' : num; } ) );

					composite.debug_tab_count = composite.debug_tab_count + 2;
					this.set( { available_options: options } );
					composite.debug_tab_count = composite.debug_tab_count - 2;
				},

				refresh_options_state: function( triggered_by ) {

					/*
					 * 1. Update active options.
					 */

					composite.console_log( 'debug:models', '\nUpdating \'Component_Options_Model\': "' + self.get_title() + '", Attribute: "active_options"...' );

					composite.debug_tab_count = composite.debug_tab_count + 2;

					var active_options          = [],
						active_scenarios        = [],
						triggered_by_index      = triggered_by.step_index,
						component_id            = self.component_id,
						scenario_data           = composite.scenarios.get_scenario_data().scenario_data,
						item_scenario_data      = scenario_data[ component_id ],
						excl_current            = false,
						up_to_current           = false,
						is_optional             = false,
						invalid_product_found   = false,
						invalid_variation_found = false;

					if ( triggered_by.is_review() ) {
						triggered_by_index = 1000;
					}

					// "Blocking" behaviour === A selection in the Nth component constrains the active options in components that precede and follow it.
					if ( composite.settings.layout === 'single' && composite.settings.non_blocking_states !== 'yes' && composite.is_initialized ) {

						// The constraints of the working item must not be taken into account when refreshing thumbnails using the 'single' layout, in order to be able to switch the selection.
						// The constraints of the firing item must not be taken into account when the update action is triggered by a dropdown, in order to be able to switch the selection.
						excl_current = true;

					// "Non-Blocking" behaviour === A selection in the Nth component constrains only the active options of the components that follow it.
					} else {
						// The constraints of the firing item must not be taken into account in paged modes.
						excl_current  = true;
						// No need to look further than the current item in paged modes.
						up_to_current = true;
					}

					// Now go get them, boy: Get active scenarios filtered by action = 'compat_group'.
					active_scenarios = composite.scenarios.filter_scenarios_by_type( composite.scenarios.calculate_active_scenarios( self, up_to_current, excl_current ), 'compat_group' );

					composite.console_log( 'debug:models', '\nReference scenarios: [' + active_scenarios + ']' );
					composite.console_log( 'debug:models', 'Removing any scenarios where the current component is masked...' );

					active_scenarios = composite.scenarios.get_binding_scenarios_for( active_scenarios, component_id );

					// Enable all if all active scenarios ignore this component.
					if ( active_scenarios.length === 0 ) {
						active_scenarios.push( '0' );
					}

					composite.console_log( 'debug:models', 'Active scenarios: [' + active_scenarios + ']' );

					/*
					 * Set component 'optional' status by adding the '0' id to the 'active_options' array.
					 */

					if ( 0 in item_scenario_data ) {

						var optional_in_scenarios = item_scenario_data[ 0 ];

						for ( var s = 0; s < optional_in_scenarios.length; s++ ) {

							var optional_in_scenario_id = optional_in_scenarios[ s ];

							if ( $.inArray( optional_in_scenario_id, active_scenarios ) > -1 ) {
								is_optional = true;
								break;
							}
						}

					} else {
						is_optional = false;
					}

					if ( is_optional ) {
						composite.console_log( 'debug:models', 'Component set as optional.' );
						active_options.push( '' );
					}

					/*
					 * Add compatible products to the 'active_options' array.
					 */
					$.each( this.get( 'available_options' ), function( index, product_id ) {

						if ( product_id === '' ) {
							return true;
						}

						var product_in_scenarios = item_scenario_data[ product_id ];
						var is_compatible        = false;

						composite.console_log( 'debug:models', 'Updating selection #' + product_id + ':' );
						composite.console_log( 'debug:models', '	Selection in scenarios: [' + product_in_scenarios + ']' );

						for ( var i = 0; i < product_in_scenarios.length; i++ ) {

							var scenario_id = product_in_scenarios[ i ];

							if ( $.inArray( scenario_id, active_scenarios ) > -1 ) {
								is_compatible = true;
								break;
							}
						}

						if ( is_compatible ) {
							composite.console_log( 'debug:models', '	Selection enabled.' );
							active_options.push( product_id );
						} else {
							composite.console_log( 'debug:models', '	Selection disabled.' );

							if ( self.get_selected_product( false ) === product_id ) {

								invalid_product_found = true;

								if ( composite.settings.layout === 'single' && composite.settings.non_blocking_states !== 'yes' ) {
									if ( false === composite.scenarios.has_invalid_product( self.step_index ) ) {
										invalid_product_found = false;
									}
								}

								if ( invalid_product_found ) {
									composite.console_log( 'debug:models', '	--- Selection invalid.' );
								}
							}
						}
					} );

					/*
					 * Disable incompatible variations.
					 */

					if ( self.get_selected_product_type() === 'variable' ) {

						var variation_input_id = self.get_selected_variation(),
							product_variations = self.$component_data.data( 'product_variations' );

						composite.console_log( 'debug:models', '	Checking variations...' );

						if ( variation_input_id > 0 ) {
							composite.console_log( 'debug:models', '		--- Stored variation is #' + variation_input_id );
						}

						for ( var i = 0; i < product_variations.length; i++ ) {

							var variation_id           = product_variations[ i ].variation_id;
							var variation_in_scenarios = item_scenario_data[ variation_id ];
							var is_compatible          = false;

							composite.console_log( 'debug:models', '		Checking variation #' + variation_id + ':' );
							composite.console_log( 'debug:models', '		Selection in scenarios: [' + variation_in_scenarios + ']' );

							for ( var k = 0; k < variation_in_scenarios.length; k++ ) {

								var scenario_id = variation_in_scenarios[ k ];

								if ( $.inArray( scenario_id, active_scenarios ) > -1 ) {
									is_compatible = true;
									break;
								}
							}

							if ( is_compatible ) {
								composite.console_log( 'debug:models', '		Variation enabled.' );
								active_options.push( variation_id.toString() );
							} else {
								composite.console_log( 'debug:models', '		Variation disabled.' );

								if ( self.get_selected_variation( false ).toString() === variation_id.toString() ) {

									invalid_variation_found = true;

									if ( composite.settings.layout === 'single' && composite.settings.non_blocking_states !== 'yes' ) {
										if ( false === composite.scenarios.has_invalid_variation( self.step_index ) ) {
											invalid_variation_found = false;
										}
									}

									if ( invalid_variation_found ) {
										composite.console_log( 'debug:models', '		--- Selection invalid.' );
									}
								}
							}
						}
					}

					composite.console_log( 'debug:models', 'Done.\n' );

					composite.debug_tab_count = composite.debug_tab_count - 2;

					if ( this.get( 'active_options' ).length !== active_options.length || this.get( 'active_options' ).length !== _.intersection( this.get( 'active_options' ), active_options ).length ) {
						composite.console_log( 'debug:models', '\nActive options changed - [' + _.map( this.get( 'active_options' ), function( num ){ return num === '' ? '0' : num; } ) + '] => [' + _.map( active_options, function( num ){ return num === '' ? '0' : num; } ) + '].\n' );
					} else {
						composite.console_log( 'debug:models', '\nActive options unchanged - [' + _.map( this.get( 'active_options' ), function( num ){ return num === '' ? '0' : num; } ) + '] => [' + _.map( active_options, function( num ){ return num === '' ? '0' : num; } ) + '].\n' );
					}

					composite.debug_tab_count = composite.debug_tab_count + 2;
					this.set( { active_options: active_options } );
					composite.debug_tab_count = composite.debug_tab_count - 2;


					/*
					 * 2. Reset invalid selections.
					 */

					if ( composite.settings.layout === 'single' || self.is_current() ) {

						composite.console_log( 'debug:models', '\nChecking current "' + self.get_title() + '" selections:' );

						if ( invalid_product_found ) {

							composite.console_log( 'debug:models', '\nProduct selection invalid - resetting...\n\n' );

							composite.debug_tab_count = composite.debug_tab_count + 2;

							self.component_selection_view.resetting_product = true;
							self.$component_options_select.val( '' ).change();
							self.component_selection_view.resetting_product = false;

							composite.debug_tab_count = composite.debug_tab_count - 2;

						} else if ( invalid_variation_found ) {

							composite.console_log( 'debug:models', '\nVariation selection invalid - resetting...\n\n' );

							composite.debug_tab_count = composite.debug_tab_count + 2;

							self.component_selection_view.resetting_variation = true;
							self.$component_summary_content.find( '.reset_variations' ).trigger( 'click' );
							self.component_selection_view.resetting_variation = false;

							composite.debug_tab_count = composite.debug_tab_count - 2;

						} else {
							composite.console_log( 'debug:models', '...looking good!' );
						}

						if ( invalid_product_found || invalid_variation_found ) {
							// Force a re-draw if an invalid selection was found.
							self.component_options_view.render_options_state();
						}
					}
				}

			} );

			var obj = new Model( opts );
			return obj;
		};

		/**
		 * Data and methods associated with the current selection.
		 */
		this.Component_Selection_Model = function( component, opts ) {

			var self  = component;
			var Model = Backbone.Model.extend( {

				selected_product: '',

				selected_product_title:       '',
				selected_variation_data:      '',
				selected_product_image_src:   '',
				selected_variation_image_src: '',

				initialize: function() {

					var params = {
						selected_product:      self.find_selected_product_param( 'id' ),
						selected_variation:    self.find_selected_product_param( 'variation_id' ),
						selected_quantity:     self.find_selected_product_param( 'quantity' ),
						// Addons only identified by price.
						selected_addons:       0.0,
						// NYP identified by price.
						selected_nyp:          0.0,
					};

					this.selected_product             = params.selected_product;
					this.selected_product_title       = self.find_selected_product_param( 'title' );
					this.selected_product_image_src   = self.find_selected_product_param( 'product_image_src' );
					this.selected_variation_image_src = self.find_selected_product_param( 'variation_image_src' );

					this.set( params );
				},

				request_details: function( product_id ) {

					var model = this;

					var data  = {
						action:        'woocommerce_show_composited_product',
						product_id:    product_id,
						component_id:  self.component_id,
						composite_id:  composite.composite_id
					};

					// Get component selection details via ajax.
					$.post( composite.get_ajax_url( data.action ), data, function( response ) {

						model.selected_product = product_id;

						model.trigger( 'component_selection_details_loaded', response );

					}, 'json' );
				},

				update_selected_product: function() {

					var attr_msg                  = '',
						selected_variation_id     = self.find_selected_product_param( 'variation_id' ),
						selected_qty              = self.find_selected_product_param( 'quantity' ),
						updating_product          = this.get( 'selected_product' ) !== this.selected_product,
						updating_variation        = this.get( 'selected_variation' ) !== selected_variation_id,
						updating_qty              = this.get( 'selected_quantity' ) !== selected_qty;

					if ( updating_product ) {

						this.selected_product_image_src = self.find_selected_product_param( 'product_image_src' );

						attr_msg = 'Attribute: "selected_product": #' + ( this.selected_product === '' ? '0' : this.selected_product );
					}

					if ( updating_variation ) {

						if ( selected_variation_id > 0 ) {

							this.selected_variation_data      = self.find_selected_product_param( 'variation_data' );
							this.selected_variation_image_src = self.find_selected_product_param( 'variation_image_src' );

						} else {
							this.selected_variation_data      = '';
							this.selected_variation_image_src = '';
						}

						attr_msg = attr_msg + ( attr_msg !== '' ? ', ' : '' ) + 'Attribute: "selected_variation": #' + ( selected_variation_id === '' ? '0' : selected_variation_id );
					}

					if ( updating_qty ) {
						attr_msg = attr_msg + ( attr_msg !== '' ? ', ' : '' ) + 'Attribute: "selected_quantity": ' + selected_qty;
					}

					if ( updating_product || updating_variation || updating_qty ) {
						composite.console_log( 'debug:models', '\nUpdating \'Component_Selection_Model\': "' + self.get_title() + '": ' + attr_msg );
					}

					this.selected_product_title = self.find_selected_product_param( 'title' );

					composite.debug_tab_count = composite.debug_tab_count + 2;
					this.set( {
						selected_product:   this.selected_product,
						selected_variation: selected_variation_id,
						selected_quantity:  selected_qty,
						selected_addons:    0.0,
						selected_nyp:       0.0
					} );
					composite.debug_tab_count = composite.debug_tab_count - 2;

					this.trigger( 'selected_product_updated', this );
				},

				update_selected_variation: function() {

					var selected_variation_id = self.find_selected_product_param( 'variation_id' );

					if ( this.get( 'selected_variation' ) !== selected_variation_id ) {

						if ( selected_variation_id > 0 ) {

							this.selected_variation_data      = self.find_selected_product_param( 'variation_data' );
							this.selected_variation_image_src = self.find_selected_product_param( 'variation_image_src' );

						} else {
							this.selected_variation_data      = '';
							this.selected_variation_image_src = '';
						}

						composite.console_log( 'debug:models', '\nUpdating \'Component_Selection_Model\': "' + self.get_title() + '", Attribute: "selected_variation": #' + ( selected_variation_id === '' ? '0' : selected_variation_id ) );
					}

					composite.debug_tab_count = composite.debug_tab_count + 2;
					this.set( { selected_variation: selected_variation_id } );
					composite.debug_tab_count = composite.debug_tab_count - 2;

					this.trigger( 'selected_variation_updated', this );
				},

				update_selected_quantity: function() {

					var selected_qty = self.find_selected_product_param( 'quantity' );

					if ( this.get( 'selected_quantity' ) !== selected_qty ) {
						composite.console_log( 'debug:models', '\nUpdating \'Component_Selection_Model\': "' + self.get_title() + '", Attribute: "selected_quantity": ' + selected_qty );
					}

					composite.debug_tab_count = composite.debug_tab_count + 2;
					this.set( { selected_quantity: selected_qty } );
					composite.debug_tab_count = composite.debug_tab_count - 2;
				},

				update_selected_addons: function() {

					var selected_addons_price = self.find_selected_product_param( 'addons_price' );

					if ( this.get( 'selected_addons' ) !== selected_addons_price ) {
						composite.console_log( 'debug:models', '\nUpdating \'Component_Selection_Model\': "' + self.get_title() + '", Attribute: "selected_addons_price": ' + selected_addons_price );
					}

					composite.debug_tab_count = composite.debug_tab_count + 2;
					this.set( { selected_addons: selected_addons_price } );
					composite.debug_tab_count = composite.debug_tab_count - 2;
				},

				update_nyp: function() {

					var nyp_price = self.find_selected_product_param( 'nyp_price' );

					if ( this.get( 'selected_nyp' ) !== nyp_price ) {
						composite.console_log( 'debug:models', '\nUpdating \'Component_Selection_Model\': "' + self.get_title() + '", Attribute: "nyp_price": ' + nyp_price );
					}

					self.$component_data.data( 'price', nyp_price );
					self.$component_data.data( 'regular_price', nyp_price );

					composite.debug_tab_count = composite.debug_tab_count + 2;
					this.set( { selected_nyp: nyp_price } );
					composite.debug_tab_count = composite.debug_tab_count - 2;
				}

			} );

			var obj = new Model( opts );
			return obj;
		};

	};



	/**
	 * View classes instantiated in a CP app lifecycle.
	 */
	wc_cp_classes.WC_CP_Views = function( composite ) {

		/**
		 * Handles the display of composite validation messages.
		 */
		this.Composite_Validation_View = function( opts ) {

			var View = Backbone.View.extend( {

				render_timer: false,
				$el_content: false,
				is_in_widget: false,

				initialize: function( options ) {

					this.$el_content  = options.$el_content;
					this.is_in_widget = options.is_in_widget;

					/**
					 * Update the view when the validation messages change.
					 */
					composite.actions.add_action( 'composite_validation_message_changed', this.render, 100, this );
				},

				render: function() {

					var view  = this,
						model = this.model;

					composite.console_log( 'debug:views', '\nScheduled update of composite validation view' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '.' );

					clearTimeout( view.render_timer );
					view.render_timer = setTimeout( function() {
						view.render_task( model );
					}, 10 );
				},

				render_task: function( model ) {

					composite.console_log( 'debug:views', '\nUpdating composite validation view' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );

					var is_purchasable = model.is_purchasable(),
						messages       = model.get( 'validation_messages' ),
						messages_html  = $( '<ul/>' ),
						message_class  = 'validation_msg' + ( ( this.is_in_widget || false === is_purchasable ) ? '' : ' indented_validation_msg' );

					if ( ! model.get( 'passes_validation' ) && messages.length > 0 ) {

						if ( is_purchasable ) {
							messages_html.append( $( '<li/>' ).addClass( 'validation_msg' ).html( wc_composite_params.i18n_validation_issues ) );
						} else {
							message_class += ' non_purchasable_msg';
						}

						$.each( messages, function( index, validation_message ) {
							messages_html.append( $( '<li/>' ).addClass( message_class ).html( validation_message ) );
						} );

						this.$el_content.html( messages_html.html() );
						this.$el.removeClass( 'inactive' ).slideDown( 200 );

					} else {
						this.$el.addClass( 'inactive' ).slideUp( 200 );
					}
				}

			} );

			var obj = new View( opts );
			return obj;
		};

		/**
		 * View associated with the price template.
		 */
		this.Composite_Price_View = function( opts ) {

			var View = Backbone.View.extend( {

				render_timer:   false,
				is_in_widget:   false,
				$addons_totals: false,
				suffix:         '',

				suffix_contains_price_incl: false,
				suffix_contains_price_excl: false,

				refreshing_addons_totals: false,

				initialize: function( options ) {

					this.is_in_widget = options.is_in_widget;

					// Add-ons support.
					if ( ! this.is_in_widget ) {

						var $addons_totals = composite.$composite_data.find( '#product-addons-total' );

						if ( $addons_totals.length > 0 ) {

							this.$addons_totals = $addons_totals;

							/**
							 * Update the addons totals when the composite totals change.
							 */
							composite.actions.add_action( 'composite_totals_changed', this.update_addons_totals, 100, this );

							/**
							 * Update addons grand totals with correct prices without triggering an ajax call.
							 */
							composite.$composite_data.on( 'updated_addons', { view: this }, this.updated_addons_handler );

							this.$el.after( $addons_totals );
						}
					}

					// Suffix.
					if ( wc_composite_params.price_display_suffix !== '' ) {
						this.suffix = ' <small class="woocommerce-price-suffix">' + wc_composite_params.price_display_suffix + '</small>';

						this.suffix_contains_price_incl = wc_composite_params.price_display_suffix.indexOf( '{price_including_tax}' ) > -1;
						this.suffix_contains_price_excl = wc_composite_params.price_display_suffix.indexOf( '{price_excluding_tax}' ) > -1;
					}

					/**
					 * Update the view when the composite totals change.
					 */
					composite.actions.add_action( 'composite_totals_changed', this.render, 100, this );

					/**
					 * Update the view when the validation messages change.
					 */
					composite.actions.add_action( 'composite_validation_message_changed', this.render, 100, this );
				},

				/**
				 * Populate prices used by the addons script and re-trigger a 'woocommerce-product-addons-update' event.
				 */
				updated_addons_handler: function( event ) {

					var view = event.data.view;

					if ( false === view.refreshing_addons_totals && view.model.get( 'passes_validation' ) ) {

						var composite_totals = view.model.get( 'totals' ),
							addons_tax_diff  = 0;

						view.refreshing_addons_totals = true;

						view.$addons_totals.data( 'price', composite_totals.price );
						view.$addons_totals.data( 'raw-price', composite_totals.price );

						if ( wc_composite_params.calc_taxes === 'yes' ) {
							if ( wc_composite_params.tax_display_shop === 'incl' ) {

								if ( wc_composite_params.prices_include_tax === 'yes' ) {
									addons_tax_diff = view.$addons_totals.data( 'addons-price' ) * ( 1 - 1 / view.model.price_data[ 'base_price_tax' ] );
								}

								view.$addons_totals.data( 'raw-price', composite_totals.price_excl_tax - addons_tax_diff );
								view.$addons_totals.data( 'tax-mode', 'excl' );

							} else {

								if ( wc_composite_params.prices_include_tax === 'no' ) {
									addons_tax_diff = view.$addons_totals.data( 'addons-price' ) * ( 1 - view.model.price_data[ 'base_price_tax' ] );
								}

								view.$addons_totals.data( 'raw-price', composite_totals.price_incl_tax - addons_tax_diff );
								view.$addons_totals.data( 'tax-mode', 'incl' );
							}
						}

						composite.$composite_data.trigger( 'woocommerce-product-addons-update' );

						view.refreshing_addons_totals = false;
					}
				},

				/**
				 * Prevent addons ajax call, since composite container-level tax does not apply to entire contents.
				 */
				update_addons_totals: function() {

					if ( false !== this.$addons_totals ) {

						// Ensure addons ajax is not triggered at this point.
						this.$addons_totals.data( 'price', 0 );
						this.$addons_totals.data( 'raw-price', 0 );

						composite.$composite_data.trigger( 'woocommerce-product-addons-update' );
					}
				},

				get_price_html: function( price_data_array ) {

					var model            = this.model,
						price_data       = typeof( price_data_array ) === 'undefined' ? model.price_data : price_data_array,
						composite_totals = typeof( price_data_array ) === 'undefined' ? model.get( 'totals' ) : price_data_array[ 'totals' ],
						price_html       = '';

					if ( composite_totals.price === 0.0 && price_data[ 'show_free_string' ] === 'yes' ) {
						price_html = '<p class="price"><span class="total">' + wc_composite_params.i18n_total + '</span>' + wc_composite_params.i18n_free + '</p>';
					} else {

						var formatted_price         = wc_cp_woocommerce_number_format( wc_cp_number_format( composite_totals.price ) ),
							formatted_regular_price = wc_cp_woocommerce_number_format( wc_cp_number_format( composite_totals.regular_price ) ),
							formatted_suffix        = '',
							formatted_price_incl    = '',
							formatted_price_excl    = '';

						if ( this.suffix !== '' ) {

							formatted_suffix = this.suffix;

							if ( this.suffix_contains_price_incl ) {
								formatted_price_incl = '<span class="amount">' + wc_cp_woocommerce_number_format( wc_cp_number_format( price_data[ 'total_incl_tax' ] ) ) + '</span>';
								formatted_suffix     =  formatted_suffix.replace( '{price_including_tax}', formatted_price_incl );
							}

							if ( this.suffix_contains_price_excl ) {
								formatted_price_excl = '<span class="amount">' + wc_cp_woocommerce_number_format( wc_cp_number_format( price_data[ 'total_excl_tax' ] ) ) + '</span>';
								formatted_suffix     =  formatted_suffix.replace( '{price_excluding_tax}', formatted_price_excl );
							}
						}

						if ( composite_totals.regular_price > composite_totals.price ) {
							price_html = '<p class="price"><span class="total">' + wc_composite_params.i18n_total + '</span><del>' + formatted_regular_price + '</del> <ins>' + formatted_price + '</ins>' + formatted_suffix + '</p>';
						} else {
							price_html = '<p class="price"><span class="total">' + wc_composite_params.i18n_total + '</span>' + formatted_price + formatted_suffix + '</p>';
						}
					}

					return composite.filters.apply_filters( 'composite_price_html', [ price_html, this, price_data_array ] );
				},

				render: function() {

					var view  = this,
						model = this.model;

					composite.console_log( 'debug:views', '\nScheduled update of composite price view' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '.' );

					clearTimeout( view.render_timer );
					view.render_timer = setTimeout( function() {
						view.render_task( model );
					}, 10 );
				},

				render_task: function( model ) {

					var price_html;

					composite.console_log( 'debug:views', '\nUpdating composite price view' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );

					if ( model.get( 'passes_validation' ) && ( model.price_data[ 'per_product_pricing' ] === 'yes' || model.price_data[ 'total' ] !== model.price_data[ 'base_display_price' ] ) ) {

						price_html = this.get_price_html();

						this.$el.html( price_html );
						this.$el.removeClass( 'inactive' ).slideDown( 200 );

					} else {
						this.$el.addClass( 'inactive' ).slideUp( 200 );
					}
				}

			} );

			var obj = new View( opts );
			return obj;
		};

		/**
		 * View associated with the availability status.
		 */
		this.Composite_Availability_View = function( opts ) {

			var View = Backbone.View.extend( {

				$composite_stock_status: false,
				is_in_widget:            false,
				render_timer:            false,

				initialize: function( options ) {

					this.is_in_widget = options.is_in_widget;

					// Save composite stock status.
					if ( composite.$composite_data.find( '.composite_wrap p.stock' ).length > 0 ) {
						this.$composite_stock_status = composite.$composite_data.find( '.composite_wrap p.stock' ).clone();
					}

					/**
					 * Update the view when the stock statuses change.
					 */
					composite.actions.add_action( 'composite_availability_message_changed', this.render, 100, this );
				},

				render: function() {

					var view  = this,
						model = this.model;

					composite.console_log( 'debug:views', '\nScheduled update of composite availability view' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '.' );

					clearTimeout( view.render_timer );
					view.render_timer = setTimeout( function() {
						view.render_task( model );
					}, 10 );
				},

				render_task: function( model ) {

					composite.console_log( 'debug:views', '\nUpdating composite availability view' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );

					/*
					 * Update composite availability string.
					 */
					var overridden_stock_status = this.get_components_availability_string();

					if ( '' !== overridden_stock_status ) {
						this.$el.html( overridden_stock_status ).slideDown( 200 );
					} else {
						if ( false !== this.$composite_stock_status ) {
							this.$el.html( this.$composite_stock_status ).slideDown( 200 );
						} else {
							this.$el.slideUp( 200 );
						}
					}
				},

				get_components_availability_string: function() {

					var out_of_stock = [];

					$.each( composite.get_components(), function( index, component ) {
						if ( ! component.step_validation_model.get( 'is_in_stock' ) ) {
							out_of_stock.push( component.get_title() );
						}
					} );

					var overridden_stock_status = '';

					if ( out_of_stock.length > 0 ) {
						var composite_out_of_stock_string = '<p class="stock out-of-stock">' + wc_composite_params.i18n_insufficient_stock + '</p>';
						overridden_stock_status = composite_out_of_stock_string.replace( '%s', wc_cp_join( out_of_stock ) );
					}

					return overridden_stock_status;
				}

			} );

			var obj = new View( opts );
			return obj;
		};

		/**
		 * View associated with the composite add-to-cart button.
		 */
		this.Composite_Add_To_Cart_Button_View = function( opts ) {

			var View = Backbone.View.extend( {

				render_timer: false,
				is_in_widget: false,
				$el_button:   false,
				$qty:         false,

				widget_qty_synced: false,

				initialize: function( options ) {

					this.is_in_widget = options.is_in_widget;
					this.$el_button   = options.$el_button;
					this.$el_qty      = this.$el.find( '.quantity input.qty' );

					/**
					 * Update the view when the validation messages change, or when the stock status of the composite changes.
					 */
					composite.actions.add_action( 'composite_availability_status_changed', this.render, 100, this );
					composite.actions.add_action( 'composite_validation_status_changed', this.render, 100, this );

					/*
					 * Events for non-widgetized view.
					 */
					if ( ! this.is_in_widget ) {
						/**
						 * Button click event handler: Activate all fields for posting.
						 */
						this.$el_button.on( 'click', function() {
							$.each( composite.get_steps(), function( index, step ) {
								step.$el.find( 'select, input' ).each( function() {
									$( this ).prop( 'disabled', false );
								} );
							} );
						} );
					}

					/*
					 * Events for widgetized view.
					 */
					if ( this.is_in_widget ) {
						/**
						 * Button click event handler: Trigger click in non-widgetized view, located within form.
						 */
						this.$el_button.on( 'click', function() {
							composite.composite_add_to_cart_button_view.$el_button.trigger( 'click' );
						} );

						/**
						 * Copy changed quantity quantity into non-widgetized view.
						 */
						this.$el_qty.on( 'change', { view: this }, function( event ) {

							var view = event.data.view;

							if ( ! view.widget_qty_synced ) {
								composite.console_log( 'debug:views', '\nCopying widget #' + view.is_in_widget + ' quantity value into composite add-to-cart quantity field...' );
								view.widget_qty_synced = true;
								composite.composite_add_to_cart_button_view.$el_qty.val( view.$el_qty.val() ).change();
								view.widget_qty_synced = false;
							}
						} );

						/**
						 * Copy changed composite quantity into view.
						 */
						composite.composite_add_to_cart_button_view.$el_qty.on( 'change', { view: this }, function( event ) {

							var view = event.data.view;

							composite.console_log( 'debug:views', '\nCopying composite add-to-cart quantity value into widget #' + view.is_in_widget + ' quantity field...' );
							view.$el_qty.val( composite.composite_add_to_cart_button_view.$el_qty.val() ).change();
						} );
					}
				},

				render: function() {

					var view  = this,
						model = this.model;

					composite.console_log( 'debug:views', '\nScheduled update of composite add-to-cart button view' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '.' );

					clearTimeout( view.render_timer );
					view.render_timer = setTimeout( function() {
						view.render_task( model );
					}, 10 );
				},

				render_task: function( model ) {

					composite.console_log( 'debug:views', '\nUpdating composite add-to-cart button view' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );

					if ( model.get( 'passes_validation' ) && model.get( 'is_in_stock' ) ) {

						if ( composite.settings.button_behaviour === 'new' ) {
							this.$el_button.prop( 'disabled', false ).removeClass( 'disabled' );
						} else {
							this.$el.slideDown( 200 );
						}

					} else {
						if ( composite.settings.button_behaviour === 'new' ) {
							this.$el_button.prop( 'disabled', true ).addClass( 'disabled' );
						} else {
							this.$el.slideUp( 200 );
						}
					}
				}

			} );

			var obj = new View( opts );
			return obj;
		};

		/**
		 * View associated with the composite pagination template.
		 */
		this.Composite_Pagination_View = function( opts ) {

			var View = Backbone.View.extend( {

				view_elements: {},

				initialize: function() {

					var view = this;

					$.each( composite.get_steps(), function( index, step ) {

						view.view_elements[ step.step_id ] = {
							$pagination_element:      composite.$composite_pagination.find( '.pagination_element_' + step.step_id ),
							$pagination_element_link: composite.$composite_pagination.find( '.pagination_element_' + step.step_id + ' .element_link' ),
						};

						/**
					 	 * Update a single pagination view element when its 'step_access_model' lock state changes.
						 */
						step.step_access_model.on( 'change:is_locked', function() { composite.composite_pagination_view.render_element_state( step ); } );
					} );

					/**
				 	 * Update all pagination view elements on transitioning to a new step.
					 */
					composite.actions.add_action( 'active_step_changed', this.active_step_changed_handler, 100, this );

					/**
					 * On clicking a composite pagination link.
					 */
					this.$el.on( 'click', '.pagination_element a', this.clicked_pagination_element );
				},

				active_step_changed_handler: function() {

					this.render();
				},

				/**
				 * Pagination element clicked.
				 */
				clicked_pagination_element: function() {

					$( this ).blur();

					if ( composite.has_transition_lock ) {
						return false;
					}

					if ( $( this ).hasClass( 'inactive' ) ) {
						return false;
					}

					var step_id = $( this ).closest( '.pagination_element' ).data( 'item_id' ),
						step    = composite.get_step( step_id );

					if ( step ) {
						composite.navigate_to_step( step );
					}

					return false;
				},

				/**
				 * Renders all elements state (active/inactive).
				 */
				render: function() {

					if ( ! composite.is_initialized ) {
						return false;
					}

					composite.console_log( 'debug:views', '\nRendering pagination view elements state...' );

					composite.debug_tab_count = composite.debug_tab_count + 2;

					$.each( composite.get_steps(), function( index, step ) {
						composite.composite_pagination_view.render_element_state( step );
					} );

					composite.debug_tab_count = composite.debug_tab_count - 2;					},

				/**
				 * Renders a single element's state (active/inactive).
				 */
				render_element_state: function( step ) {

					composite.console_log( 'debug:views', '\nUpdating "' + step.get_title() + '" pagination view element state...' );

					if ( step.is_current() ) {
						this.view_elements[ step.step_id ].$pagination_element_link.addClass( 'inactive' );
						this.view_elements[ step.step_id ].$pagination_element.addClass( 'pagination_element_current' );
					} else {

						if ( step.is_locked() ) {
							this.view_elements[ step.step_id ].$pagination_element_link.addClass( 'inactive' );
							this.view_elements[ step.step_id ].$pagination_element.removeClass( 'pagination_element_current' );
						} else {
							this.view_elements[ step.step_id ].$pagination_element_link.removeClass( 'inactive' );
							this.view_elements[ step.step_id ].$pagination_element.removeClass( 'pagination_element_current' );
						}
					}
				}

			} );

			var obj = new View( opts );
			return obj;
		};

		/**
		 * View associated with the composite summary template.
		 */
		this.Composite_Summary_View = function( opts ) {

			var View = Backbone.View.extend( {

				update_price_timers:   {},
				update_content_timers: {},
				view_elements:         {},
				is_in_widget:          false,

				initialize: function( options ) {

					var view = this;

					this.is_in_widget = options.is_in_widget;

					$.each( composite.get_steps(), function( index, step ) {

						/**
					 	 * Update a single summary view element state when its 'step_access_model' lock state changes.
						 */
						step.step_access_model.on( 'change:is_locked', function() { view.render_element_state( step ); } );

						/**
					 	 * Update a single summary view element content when its 'step_validation_model' state changes.
						 */
						step.step_validation_model.on( 'change:passes_validation', function() { view.render_element_content( step ); } );
					} );

					$.each( composite.get_components(), function( index, component ) {

						view.view_elements[ component.component_id ] = {

							$summary_element:         view.$el.find( '.summary_element_' + component.component_id ),
							$summary_element_link:    view.$el.find( '.summary_element_' + component.component_id + ' .summary_element_link' ),

							$summary_element_wrapper: view.$el.find( '.summary_element_' + component.component_id + ' .summary_element_wrapper' ),
							$summary_element_inner:   view.$el.find( '.summary_element_' + component.component_id + ' .summary_element_wrapper_inner' ),

							$summary_element_title:   view.$el.find( '.summary_element_' + component.component_id + ' .summary_element_selection' ),
							$summary_element_image:   view.$el.find( '.summary_element_' + component.component_id + ' .summary_element_image' ),
							$summary_element_price:   view.$el.find( '.summary_element_' + component.component_id + ' .summary_element_price' ),
						};
					} );

					/**
					 * Update a single summary view element content when its quantity changes.
					 */
					composite.actions.add_action( 'component_quantity_changed', this.quantity_changed_handler, 100, this );

					/**
					 * Update a single summary view element content when a new selection is made.
					 */
					composite.actions.add_action( 'component_selection_changed', this.selection_changed_handler, 100, this );

					/**
					 * Update a single summary view element content when the contents of an existing selection change.
					 */
					composite.actions.add_action( 'component_selection_content_changed', this.selection_changed_handler, 100, this );

					/**
					 * Update a single summary view element price when its totals change.
					 */
					composite.actions.add_action( 'component_totals_changed', this.component_totals_changed_handler, 100, this );

					/**
				 	 * Update all summary view elements on transitioning to a new step.
					 */
					if ( composite.settings.layout !== 'single' ) {
						composite.actions.add_action( 'active_step_changed', this.active_step_changed_handler, 100, this );
					}

					/**
					 * On clicking a summary link.
					 */
					this.$el.on( 'click', '.summary_element_link', this.clicked_summary_element );

					/**
					 * On tapping a summary link.
					 */
					this.$el.on( 'click', 'a.summary_element_tap', function() {
						$( this ).closest( '.summary_element_link' ).trigger( 'click' );
						return false;
					} );
				},

				active_step_changed_handler: function() {

					this.render_state();
				},

				selection_changed_handler: function( step ) {

					if ( ! composite.is_initialized ) {
						return false;
					}

					this.render_element_content( step );
				},

				quantity_changed_handler: function( step ) {

					this.render_element_content( step );
				},

				component_totals_changed_handler: function( step ) {

					this.render_element_price( step );
				},

				/**
				 * Summary element clicked.
				 */
				clicked_summary_element: function() {

					if ( composite.has_transition_lock ) {
						return false;
					}

					var form = composite.$composite_form;

					if ( $( this ).hasClass( 'disabled' ) ) {
						return false;
					}

					var step_id = $( this ).closest( '.summary_element' ).data( 'item_id' );

					if ( typeof( step_id ) === 'undefined' ) {
						var composite_summary = composite.$composite_summary;
						var element_index     = composite_summary.find( '.summary_element' ).index( $( this ).closest( '.summary_element' ) );
						step_id               = form.find( '.multistep.component:eq(' + element_index + ')' ).data( 'item_id' );
					}

					var step = composite.get_step( step_id );

					if ( step === false ) {
						return false;
					}

					if ( ! step.is_current() || composite.settings.layout === 'single' ) {
						composite.navigate_to_step( step );
					}

					return false;
				},

				/**
				 * Renders all elements state (active/inactive).
				 */
				render_state: function() {

					if ( ! composite.is_initialized ) {
						return false;
					}

					var view = this;

					composite.console_log( 'debug:views', '\nRendering summary view element states' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );
					composite.debug_tab_count = composite.debug_tab_count + 2;
					$.each( composite.get_steps(), function( index, step ) {
						view.render_element_state( step );
					} );
					composite.debug_tab_count = composite.debug_tab_count - 2;
				},

				/**
				 * Render content.
				 */
				render_content: function() {

					var view = this;

					composite.console_log( 'debug:views', '\nRendering summary view element contents' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );
					composite.debug_tab_count = composite.debug_tab_count + 2;
					$.each( composite.get_steps(), function( index, step ) {
						view.render_element_content( step );
					} );
					composite.debug_tab_count = composite.debug_tab_count - 2;
				},

				/**
				 * Render prices.
				 */
				render_price: function() {

					var view = this;

					composite.console_log( 'debug:views', '\nRendering summary view element prices' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );
					composite.debug_tab_count = composite.debug_tab_count + 2;
					$.each( composite.get_steps(), function( index, step ) {
						view.render_element_price( step );
					} );
					composite.debug_tab_count = composite.debug_tab_count - 2;
				},

				/**
				 * Render state.
				 */
				render: function() {

					composite.console_log( 'debug:views', '\nRendering summary view elements' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );
					composite.debug_tab_count = composite.debug_tab_count + 2;
					this.render_price();
					this.render_content();
					this.render_state();
					composite.debug_tab_count = composite.debug_tab_count - 2;
				},


				/**
				 * Renders a single element's price (scheduler).
				 */
				render_element_price: function( step ) {

					if ( ! composite.is_initialized ) {
						return false;
					}

					var view = this;

					if ( typeof this.view_elements[ step.step_id ] === 'undefined' ) {
						return false;
					}

					composite.console_log( 'debug:views', '\nScheduled update of "' + step.get_title() + '" summary view element price' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '.' );

					if ( typeof( this.update_price_timers[ step.step_index ] ) !== 'undefined' ) {
						clearTimeout( view.update_price_timers[ step.step_index ] );
					}

					this.update_price_timers[ step.step_index ] = setTimeout( function() {
						view.render_element_price_task( step );
					}, 10 );
				},


				/**
				 * Renders a single element's content.
				 */
				render_element_price_task: function( step ) {

					composite.console_log( 'debug:views', '\nRendering "' + step.get_title() + '" summary view element price' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );

					var price_data = composite.data_model.price_data;

					if ( step.is_component() ) {

						var component    = step,
							component_id = component.component_id,
							$item_price  = this.view_elements[ component_id ].$summary_element_price,
							product_id   = component.get_selected_product_type() === 'variable' ? component.get_selected_variation( false ) : component.get_selected_product( false ),
							qty          = component.get_selected_quantity();

						// Update price.
						if ( product_id > 0 && qty > 0 ) {

							var component_totals = composite.data_model.get( 'component_' + component_id + '_totals' );

							if ( price_data[ 'per_product_pricing' ] === 'no' && component_totals.price === 0.0 && component_totals.regular_price === 0.0 ) {
								$item_price.html( '' );
							} else {
								var price_format         = wc_cp_woocommerce_number_format( wc_cp_number_format( component_totals.price ) ),
									regular_price_format = wc_cp_woocommerce_number_format( wc_cp_number_format( component_totals.regular_price ) );

								if ( component_totals.regular_price > component_totals.price ) {
									$item_price.html( '<span class="price summary_element_content"><del>' + regular_price_format + '</del> <ins>' + price_format + '</ins></span>' );
								} else {
									$item_price.html( '<span class="price summary_element_content">' + price_format + '</span>' );
								}
							}
						} else {
							$item_price.html(' <span class="price summary_element_content" style="visibility:hidden;">&nbsp;</span>' );
						}
					}
				},

				/**
				 * Renders a single element's content (scheduler).
				 */
				render_element_content: function( step ) {

					if ( ! composite.is_initialized ) {
						return false;
					}

					var view = this;

					if ( typeof this.view_elements[ step.step_id ] === 'undefined' ) {
						return false;
					}

					composite.console_log( 'debug:views', '\nScheduled update of "' + step.get_title() + '" summary view element content' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '.' );

					if ( typeof( this.update_content_timers[ step.step_index ] ) !== 'undefined' ) {
						clearTimeout( view.update_content_timers[ step.step_index ] );
					}

					this.update_content_timers[ step.step_index ] = setTimeout( function() {
						view.render_element_content_task( step );
					}, 50 );
				},

				/**
				 * Renders a single element's content.
				 */
				render_element_content_task: function( step ) {

					composite.console_log( 'debug:views', '\nRendering "' + step.get_title() + '" summary view element content' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );

					if ( step.is_component() ) {

						var component           = step,
							component_id        = component.component_id,

							$item_summary_outer = this.view_elements[ component_id ].$summary_element_wrapper,
							$item_summary_inner = this.view_elements[ component_id ].$summary_element_inner,
							$item_title         = this.view_elements[ component_id ].$summary_element_title,
							$item_image         = this.view_elements[ component_id ].$summary_element_image,

							title               = '',
							select              = '',
							image               = '',

							load_height         = 0,

							is_visible          = this.$el.is( ':visible' );

						// Lock height if animating.
						if ( is_visible ) {
							load_height = $item_summary_inner.outerHeight( true );
							$item_summary_outer.css( 'height', load_height );
						}

						title = component.get_selected_product_title( true, false );
						image = component.get_selected_product_image_src( false );

						// Selection text.
						if ( false === this.is_in_widget ) {
							if ( title && component.passes_validation() ) {
								if ( component.is_static() ) {
									select = '<a href="">' + wc_composite_params.i18n_summary_static_component + '</a>';
								} else {
									select = '<a href="">' + wc_composite_params.i18n_summary_configured_component + '</a>';
								}
							} else {
								select = '<a href="">' + wc_composite_params.i18n_summary_empty_component + '</a>';
							}
						}

						// Update title.
						if ( title ) {
							$item_title.html( '<span class="summary_element_content">' + title + '</span>' + ( this.is_in_widget ? '' : '<span class="summary_element_content summary_element_selection_prompt">' + select + '</span>' ) );
						} else {
							$item_title.html( this.is_in_widget ? '' : '<span class="summary_element_content summary_element_selection_prompt">' + select + '</span>' );
						}

						// Update element class.
						if ( component.passes_validation() ) {
							$item_summary_outer.addClass( 'configured' );
						} else {
							$item_summary_outer.removeClass( 'configured' );
						}

						// Hide selection text.
						if ( ( step.is_current() && is_visible ) || this.is_in_widget ) {
							$item_title.find( '.summary_element_selection_prompt' ).hide();
						}

						// Update image.
						this.render_summary_element_image( $item_image, image );

						// Run 'component_summary_content_updated' action to allow 3rd party code to add data to the summary - @see WC_CP_Actions_Dispatcher class.
						composite.actions.do_action( 'component_summary_content_updated', [ component, this ] );

						// Animate.
						if ( is_visible ) {

							// Measure height.
							var new_height     = $item_summary_inner.outerHeight( true );
							var animate_height = false;

							if ( Math.abs( new_height - load_height ) > 1 ) {
								animate_height = true;
							} else {
								$item_summary_outer.css( 'height', 'auto' );
							}

							if ( animate_height ) {

								composite.console_log( 'debug:events', 'Starting updated element content animation...' );

								$item_summary_outer.animate( { 'height': new_height }, { duration: 200, queue: false, always: function() {

									composite.console_log( 'debug:events', 'Ended updated element content animation.' );

									$item_summary_outer.css( { 'height': 'auto' } );
								} } );
							}
						}
					}
				},

				/**
				 * Updates images in the Review/Summary template.
		 		 */
				render_summary_element_image: function( $item_image, img_src ) {

					var element_image = $item_image.find( 'img' );

					if ( element_image.length == 0 || element_image.hasClass( 'norefresh' ) ) {
						return false;
					}

					var o_src = element_image.attr( 'data-o_src' );

					if ( ! img_src ) {

						if ( typeof( o_src ) !== 'undefined' ) {
							element_image.attr( 'src', o_src );
						}

					} else {

						if ( typeof( o_src ) === 'undefined' ) {
							o_src = ( ! element_image.attr( 'src' ) ) ? '' : element_image.attr( 'src' );
							element_image.attr( 'data-o_src', o_src );
						}

						element_image.attr( 'src', img_src );
					}
				},

				/**
				 * Renders a single element's state (active/inactive).
				 */
				render_element_state: function( step ) {

					if ( typeof this.view_elements[ step.step_id ] === 'undefined' ) {
						return false;
					}

					if ( ! composite.is_initialized ) {
						return false;
					}

					var $element      = this.view_elements[ step.step_id ].$summary_element,
						$element_link = this.view_elements[ step.step_id ].$summary_element_link;

					composite.console_log( 'debug:views', '\nUpdating "' + step.get_title() + '" summary view element state' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );

					if ( step.is_current() ) {

						$element_link.removeClass( 'disabled' );

						if ( composite.settings.layout !== 'single' ) {
							$element_link.addClass( 'selected' );
						}

						if ( false === composite.get_step( 'review' ) ) {
							$element.find( '.summary_element_selection_prompt' ).slideUp( 200 );
						}

					} else {

						$element.find( '.summary_element_selection_prompt' ).slideDown( 200 );

						if ( step.is_locked() ) {

							$element_link.removeClass( 'selected' );
							$element_link.addClass( 'disabled' );

						} else {

							$element_link.removeClass( 'disabled' );
							$element_link.removeClass( 'selected' );
						}
					}
				}

			} );

			var obj = new View( opts );
			return obj;
		};

		/**
		 * View associated with navigation view elements.
		 */
		this.Composite_Navigation_View = function( opts ) {

			var View = Backbone.View.extend( {

				render_timer:    false,
				show_hide_timer: false,

				updated_buttons_data: {},

				navi_in_step: false,

				$el_progressive:   composite.$composite_form.find( '.composite_navigation.progressive' ),
				$el_paged_top:     composite.$composite_navigation_top,
				$el_paged_bottom:  composite.$composite_navigation_bottom,
				$el_paged_movable: composite.$composite_navigation_movable,

				initialize: function() {

					/**
				 	 * Update navigation view elements when a new selection is made.
					 */
					composite.actions.add_action( 'component_selection_changed', this.selection_changed_handler, 100, this );

					/**
				 	 * Update navigation view elements when the contents of an existing selection are changed.
					 */
					composite.actions.add_action( 'component_selection_content_changed', this.selection_changed_handler, 100, this );

					/**
				 	 * Update navigation view elements on transitioning to a new step.
					 */
					composite.actions.add_action( 'active_step_transition_start', this.active_step_transition_start_handler, 100, this );

					/**
					 * On clicking the Previous/Next navigation buttons.
					 */
					this.$el.on( 'click', '.page_button', this.clicked_navigation_button );
				},

				/**
			 	 * Updates navigation view elements when a new selection is made.
			 	 * Handled by the composite actions dispatcher.
				 */
				selection_changed_handler: function( step ) {

					if ( ! composite.is_initialized ) {
						return false;
					}

					// Autotransition to next.
					if ( step.is_current() && step.autotransition() && step.passes_validation() && ! step.component_selection_view.resetting_product ) {
						composite.show_next_step();
						return false;
					}

					var view = this;

					composite.console_log( 'debug:views', '\nScheduling navigation UI update...' );

					clearTimeout( view.render_timer );
					view.render_timer = setTimeout( function() {
						view.render( 'change' );
					}, 40 );
				},

				/**
			 	 * Update navigation view elements on transitioning to a new step.
				 */
				active_step_transition_start_handler: function() {

					var view = this;

					clearTimeout( view.render_timer );
					view.render( 'transition' );
				},

				/**
				 * Previous/Next navigation button clicked.
				 */
				clicked_navigation_button: function() {

					$( this ).blur();

					if ( $( this ).hasClass( 'inactive' ) ) {
						return false;
					}

					if ( composite.has_transition_lock ) {
						return false;
					}

					if ( $( this ).hasClass( 'next' ) ) {

						if ( composite.get_next_step() ) {
							composite.show_next_step();
						} else {
							wc_cp_scroll_viewport( composite.$composite_form.find( '.scroll_final_step' ), { partial: false, duration: 250, queue: false } );
						}

					} else {
						composite.show_previous_step();
					}

					return false;
				},

				update_buttons: function() {

					var button_next  = this.$el.find( '.next' ),
						button_prev  = this.$el.find( '.prev' );

					if ( false !== this.updated_buttons_data.button_next_html ) {
						button_next.html( this.updated_buttons_data.button_next_html );
					}

					if ( false !== this.updated_buttons_data.button_prev_html ) {
						button_prev.html( this.updated_buttons_data.button_prev_html );
					}

					if ( this.updated_buttons_data.button_next_visible ) {
						button_next.removeClass( 'invisible' );
					} else {
						button_next.addClass( 'invisible' );
					}

					if ( this.updated_buttons_data.button_prev_visible ) {
						button_prev.removeClass( 'invisible' );
					} else {
						button_prev.addClass( 'invisible' );
					}

					if ( this.updated_buttons_data.button_next_active ) {
						button_next.removeClass( 'inactive' );
					} else {
						button_next.addClass( 'inactive' );
					}
				},

				render: function( event_type ) {

					composite.console_log( 'debug:views', '\nRendering navigation UI...' );

					var current_step        = composite.get_current_step(),
						next_step           = composite.get_next_step(),
						prev_step           = composite.get_previous_step(),
						view                = this;

					this.updated_buttons_data = {
						button_next_html:    false,
						button_prev_html:    false,
						button_next_visible: false,
						button_prev_visible: false,
						button_next_active:  false,
					};

					if ( event_type === 'transition' && composite.settings.layout === 'paged' && composite.settings.layout_variation === 'componentized' ) {
						if ( current_step.is_review() ) {
							this.$el_paged_bottom.hide();
						} else {
							this.$el_paged_bottom.show();
						}
					}

					if ( current_step.is_component() ) {

						// Selectively show next/previous navigation buttons.
						if ( next_step && composite.settings.layout_variation !== 'componentized' ) {

							this.updated_buttons_data.button_next_html    = wc_composite_params.i18n_next_step.replace( '%s', next_step.get_title() );
							this.updated_buttons_data.button_next_visible = true;

						} else if ( composite.settings.layout === 'paged' ) {
							this.updated_buttons_data.button_next_html    = wc_composite_params.i18n_final_step;
							this.updated_buttons_data.button_next_visible = true;
						}
					}

					// Paged previous/next.
					if ( current_step.passes_validation() || ( composite.settings.layout_variation === 'componentized' && current_step.is_component() ) ) {

						if ( next_step ) {
							this.updated_buttons_data.button_next_active = true;
						}

						if ( prev_step && composite.settings.layout === 'paged' && prev_step.is_component() ) {
							this.updated_buttons_data.button_prev_html    = wc_composite_params.i18n_previous_step.replace( '%s', prev_step.get_title() );
							this.updated_buttons_data.button_prev_visible = true;
						} else {
							this.updated_buttons_data.button_prev_html = '';
						}

					} else {

						if ( prev_step && prev_step.is_component() ) {

							var product_id = prev_step.get_selected_product();

							if ( product_id > 0 || product_id === '0' || product_id === '' && prev_step.is_optional() ) {

								if ( composite.settings.layout === 'paged' ) {
									this.updated_buttons_data.button_prev_html    = wc_composite_params.i18n_previous_step.replace( '%s', prev_step.get_title() );
									this.updated_buttons_data.button_prev_visible = true;
								}
							}
						}
					}

					/*
					 * Move navigation into the next component when using the progressive layout without toggles.
					 */
					if ( composite.settings.layout === 'progressive' ) {

						var navi = view.$el_progressive;

						if ( view.navi_in_step !== current_step.step_id ) {

							navi.slideUp( { duration: 200, always: function() {

								view.update_buttons();
								navi.appendTo( current_step.$inner_el ).hide();

								view.navi_in_step = current_step.step_id;

								setTimeout( function() {

									var show_navi = false;

									if ( ! current_step.$el.hasClass( 'last' ) ) {
										if ( current_step.passes_validation() && ! next_step.has_toggle() ) {
											show_navi = true;
										}
									}

									if ( show_navi ) {
										navi.slideDown( { duration: 200, queue: false } );
									}

								}, 200 );

							} } );

						} else {

							view.update_buttons();

							var show_navi = false;

							if ( ! current_step.$el.hasClass( 'last' ) ) {
								if ( current_step.passes_validation() && ! next_step.has_toggle() ) {
									show_navi = true;
								}
							}

							if ( show_navi ) {
								navi.slideDown( 200 );
							} else {
								navi.slideUp( 200 );
							}
						}

					/*
					 * Move navigation when using a paged layout with thumbnails.
					 */
					} else if ( composite.settings.layout === 'paged' ) {

						if ( view.navi_in_step !== current_step.step_id ) {
							current_step.$el.prepend( view.$el_paged_top );
							current_step.$el.append( view.$el_paged_bottom );
							view.navi_in_step = current_step.step_id;
						}

						view.update_buttons();

						if ( current_step.is_component() && current_step.has_options_style( 'thumbnails' ) ) {

							if ( current_step.get_selected_product() > 0 ) {

								// Measure distance from bottom navi and only append navi in content if far enough.
								var navi_in_content    = current_step.$component_content.find( '.composite_navigation' ).length > 0,
									bottom_navi_nearby = false,
									show_hide_el       = ! current_step.$el.is( ':visible' ) && event_type === 'transition';

								if ( current_step.append_results() ) {

									if ( current_step.component_selection_view.is_relocated() && false === current_step.$component_pagination.find( '.component_options_load_more' ).is( ':visible' ) ) {

										if ( show_hide_el ) {
											current_step.$el.addClass( 'invisible' ).show();
										}

										var distance_from_navi = view.$el_paged_bottom.offset().top - current_step.$component_content.offset().top - current_step.$component_content.outerHeight( true );

										if ( distance_from_navi < 150 ) {
											bottom_navi_nearby = true;
										}

										if ( show_hide_el ) {
											current_step.$el.hide().removeClass( 'invisible' );
										}
									}
								}

								if ( ! navi_in_content && ! bottom_navi_nearby ) {
									view.$el_paged_movable.appendTo( current_step.$component_summary );
									navi_in_content = true;
								}

								if ( navi_in_content ) {
									if ( bottom_navi_nearby || current_step.is_static() || false === current_step.has_options_style( 'thumbnails' ) ) {
										view.$el_paged_movable.addClass( 'hidden' );
									} else {
										view.$el_paged_movable.removeClass( 'hidden' );
									}
								}
							}
						}
					}
				}

			} );

			var obj = new View( opts );
			return obj;
		};

		/**
		 * View associated with the Composite Summary Widget and its elements.
		 */
		this.Composite_Widget_View = function( opts ) {

			var View = Backbone.View.extend( {

				price_view:      false,
				show_hide_timer: false,

				initialize: function( options ) {

					this.$el.removeClass( 'cp-no-js' );

					this.validation_view = new composite.view_classes.Composite_Validation_View( {
						is_in_widget: options.widget_count,
						el:           this.$el.find( '.widget_composite_summary_error .composite_message' ),
						$el_content:  this.$el.find( '.widget_composite_summary_error .composite_message ul.msg' ),
						model:        composite.data_model,
					} );

					this.price_view = new composite.view_classes.Composite_Price_View( {
						is_in_widget: options.widget_count,
						el:           this.$el.find( '.widget_composite_summary_price .composite_price' ),
						model:        composite.data_model,
					} );

					this.availability_view = new composite.view_classes.Composite_Availability_View( {
						is_in_widget: options.widget_count,
						el:           this.$el.find( '.widget_composite_summary_availability .composite_availability' ),
						model:        composite.data_model,
					} );

					this.add_to_cart_button_view = new composite.view_classes.Composite_Add_To_Cart_Button_View( {
						is_in_widget: options.widget_count,
						el:           this.$el.find( '.widget_composite_summary_button .composite_button' ),
						$el_button:   this.$el.find( '.widget_composite_summary_button .composite_button .composite_add_to_cart_button' ),
						model:        composite.data_model,
					} );

					this.composite_summary_view = new composite.view_classes.Composite_Summary_View( {
						is_in_widget: options.widget_count,
						el:           this.$el.find( '.widget_composite_summary_elements' ),
					} );

					/**
					 * Show/hide the widget when transitioning to a new step.
					 */
					if ( composite.settings.layout === 'paged' ) {
						composite.actions.add_action( 'active_step_changed', this.active_step_changed_handler, 100, this );
					}
				},

				active_step_changed_handler: function() {

					this.show_hide();
				},

				show_hide: function() {

					var view = this;

					clearTimeout( view.show_hide_timer );
					this.show_hide_timer = setTimeout( function() {
						view.show_hide_task();
					}, 20 );
				},

				show_hide_task: function() {

					var is_review = composite.get_current_step().is_review();

					if ( is_review ) {
						this.$el.slideUp( 250 );
						this.$el.animate( { opacity: 0 }, { duration: 250, queue: false } );
						this.$el.addClass( 'inactive' );
					} else {
						if ( this.$el.hasClass( 'inactive' ) ) {
							this.$el.removeClass( 'inactive' );
							this.$el.slideDown( 250 );
							this.$el.animate( { opacity: 1 }, { duration: 250, queue: false } );
						}
					}
				}

			} );

			var obj = new View( opts );
			return obj;
		};

		/**
		 * Handles the display of step validation messages.
		 */
		this.Step_Validation_View = function( step, opts ) {

			var self = step;
			var View = Backbone.View.extend( {

				render_timer: false,
				render_html: false,

				initialize: function() {

					var view = this;

					this.listenTo( this.model, 'change:component_messages', function() {

						if ( ! self.is_current() || typeof( self.$component_message ) === 'undefined' || typeof( self.$component_message_content ) === 'undefined' ) {
							return false;
						}

						if ( self.component_selection_view.resetting_product ) {
							return false;
						}

						composite.console_log( 'debug:views', '\nScheduling "' + self.get_title() + '" validation message update...' );
						clearTimeout( view.render_timer );
						view.render_timer = setTimeout( function() {
							view.prepare_render( 'change' );
							view.render( 'change' );
						}, 10 );
					} );

					/**
					 * Prepare display of component messages when transitioning to this step.
					 */
					if ( composite.settings.layout !== 'single' ) {
						composite.actions.add_action( 'active_step_changed_' + self.step_id, this.active_step_changed_handler, 100, this );
					}

					/**
					 * Display component messages after transitioning to this step.
					 */
					if ( composite.settings.layout !== 'single' ) {
						composite.actions.add_action( 'active_step_transition_end_' + self.step_id, this.active_step_transition_end_handler, 100, this );
					}
				},

				/**
				 * Shows component messages when transitioning this step.
				 */
				active_step_changed_handler: function() {

					if ( ! self.is_current() || typeof( self.$component_message ) === 'undefined' || typeof( self.$component_message_content ) === 'undefined' ) {
						return false;
					}

					this.prepare_render( 'transition' );
				},

				/**
				 * Shows component messages when transitioning this step.
				 */
				active_step_transition_end_handler: function() {

					if ( ! self.is_current() || typeof( self.$component_message ) === 'undefined' || typeof( self.$component_message_content ) === 'undefined' ) {
						return false;
					}

					clearTimeout( this.render_timer );
					this.render( 'transition' );
				},

				/**
				 * Prepares validation messages for rendering.
				 */
				prepare_render: function( event_type ) {

					var display_message;

					composite.console_log( 'debug:views', '\nPreparing "' + self.get_title() + '" validation message update...' );

					this.render_html = false;

					if ( self.passes_validation() || ( composite.settings.layout_variation === 'componentized' && self.is_component() ) ) {
						display_message = false;
					} else {
						display_message = true;
					}

					if ( display_message ) {

						// Don't show the prompt if it's the last component of the progressive layout.
						if ( ! self.$el.hasClass( 'last' ) || ! self.$el.hasClass( 'progressive' ) ) {
							// We actually have something to display here.
							var messages            = $( '<ul/>' ),
								validation_messages = self.get_validation_messages();

							if ( validation_messages.length > 0 ) {
								$.each( validation_messages, function( i, message ) {
									messages.append( $( '<li/>' ).html( message ) );
								} );

								this.render_html = messages.html();
							}
						}
					}

					if ( event_type === 'transition' && false === this.render_html ) {
						if ( composite.settings.layout === 'progressive' ) {
							if ( self.has_toggle() ) {
								self.$component_message.hide();
							}
						} else if ( composite.settings.layout === 'paged' ) {
							self.$component_message.hide();
						}
					}
				},

				/**
				 * Renders validation messages.
				 */
				render: function( event_type ) {

					var view = this;

					composite.console_log( 'debug:views', '\nUpdating "' + self.get_title() + '" validation message...' );

					if ( false !== this.render_html ) {
						self.$component_message_content.html( this.render_html );
					}

					if ( composite.settings.layout === 'progressive' ) {

						if ( event_type === 'transition' ) {

							setTimeout( function() {

								if ( false === view.render_html ) {
									self.$component_message.slideUp( 200 );
								} else {
									self.$component_message.slideDown( 200 );
								}

							}, 200 );

						} else {

							if ( false === this.render_html ) {
								self.$component_message.slideUp( 200 );
							} else {
								self.$component_message.slideDown( 200 );
							}
						}

					} else if ( composite.settings.layout === 'paged' ) {

						var component_message_delay = 0;

						// Add a delay when loading a new component option with notices, in order to display the message after the animation has finished.
						if ( self.is_component() && self.$component_content.hasClass( 'updating' ) && false !== this.render_html ) {
							component_message_delay = 600;
						}

						// Hide the message container when moving into a relocating summary and add a delay.
						if ( self.is_component() && self.$component_content.hasClass( 'relocating' ) ) {
							self.$component_message.hide();
							component_message_delay = 600;
						}

						setTimeout( function() {
							if ( false === view.render_html ) {
								self.$component_message.slideUp( 200 );
							} else {
								self.$component_message.slideDown( 200 );
							}
						}, component_message_delay );
					}
				}

			} );

			var obj = new View( opts );
			return obj;
		};

		/**
		 * Updates the model data from UI interactions and listens to the component options model for updated content.
		 */
		this.Component_Options_View = function( component, opts ) {

			var self = component;
			var View = Backbone.View.extend( {

				append_results_nesting: 0,
				append_results_nesting_count: 0,

				render_count: 0,

				$blocked_element: false,

				initialize: function() {

					/**
				 	 * Reload component options upon activating a filter.
					 */
					self.$el.on( 'click', '.component_filter_option a', { view: this }, this.activate_filter );

					/**
					 * Reload component options upon resetting a filter.
					 */
					self.$el.on( 'click', '.component_filters a.reset_component_filter', { view: this }, this.reset_filter );

					/**
					 * Reload component options upon resetting all filters.
					 */
					self.$el.on( 'click', '.component_filters a.reset_component_filters', { view: this }, this.reset_filters );

					/**
					 * Reload component options upon requesting a new page.
					 */
					self.$el.on( 'click', '.component_pagination a.component_pagination_element', { view: this }, this.load_page );

					/**
					 * Append component options upon clicking the 'Load More' button.
					 */
					self.$el.on( 'click', '.component_pagination a.component_options_load_more', { view: this }, this.load_more );

					/**
					 * Reload component options upon reordering.
					 */
					self.$el.on( 'change', '.component_ordering select', { view: this }, this.order_by );

					/**
					 * Toggle filters.
					 */
					self.$el.on( 'click', '.component_filter_title label', { view: this }, this.toggle_filter );


					/**
					 * Navigate to step on clicking the blocked area in progressive mode.
					 */
					if ( composite.settings.layout === 'progressive' ) {
						self.$el.on( 'click', '.block_component_selections_inner', { view: this }, this.clicked_blocked_area );
					}

					/**
					 * Change selection when clicking a thumbnail or thumbnail tap area.
					 */
					if ( self.has_options_style( 'thumbnails' ) ) {
						self.$el.on( 'click', '.component_option_thumbnail', { view: this }, this.clicked_thumbnail );
						self.$el.on( 'click', 'a.component_option_thumbnail_tap', { view: this }, this.clicked_thumbnail_tap );
					}

					/**
					 * Change selection when clicking a radio button.
					 */
					if ( self.has_options_style( 'radios' ) ) {
						self.$el.on( 'change', '.component_option_radio_buttons input', { view: this }, this.clicked_radio );
						self.$el.on( 'click', 'a.component_option_radio_button_tap', { view: this }, this.clicked_radio_tap );
					}

					/**
					 * Render reload/append responses into view.
					 */
					this.listenTo( this.model, 'component_options_data_loaded', this.render_response );

					/**
					 * Render component options active/inactive state into view.
					 */
					composite.actions.add_action( 'active_options_changed_' + self.step_id, this.render_options_state, 10, this );

				},

				clicked_blocked_area: function() {

					composite.navigate_to_step( self );
					return false;
				},

				clicked_thumbnail_tap: function() {

					$( this ).closest( '.component_option_thumbnail' ).trigger( 'click' );
					return false;
				},

				clicked_thumbnail: function() {

					$( this ).blur();

					if ( self.$el.hasClass( 'disabled' ) || $( this ).hasClass( 'disabled' ) ) {
						return true;
					}

					if ( ! $( this ).hasClass( 'selected' ) ) {
						var value = $( this ).data( 'val' );
						self.$component_options_select.val( value ).change();
					}
				},

				clicked_radio_tap: function() {

					$( this ).closest( '.component_option_radio_button' ).find( 'input' ).trigger( 'click' );
					return false;
				},

				clicked_radio: function() {

					var $container = $( this ).closest( '.component_option_radio_button' );

					if ( self.$el.hasClass( 'disabled' ) || $container.hasClass( 'disabled' ) ) {
						return true;
					}

					if ( ! $container.hasClass( 'selected' ) ) {
						var value = $( this ).val();
						self.$component_options_select.val( value ).change();
					}
				},

				toggle_filter: function() {

					$( this ).blur();

					var component_filter         = $( this ).closest( '.component_filter' ),
						component_filter_content = component_filter.find( '.component_filter_content' );

					wc_cp_toggle_element( component_filter, component_filter_content );

					return false;
				},

				activate_filter: function( event ) {

					$( this ).blur();

					// Do nothing if the component is disabled.
					if ( self.$el.hasClass( 'disabled' ) ) {
						return false;
					}

					var view                    = event.data.view,
						component_filter_option = $( this ).closest( '.component_filter_option' );

					if ( ! component_filter_option.hasClass( 'selected' ) ) {
						component_filter_option.addClass( 'selected' );
					} else {
						component_filter_option.removeClass( 'selected' );
					}

					// Add/remove 'active' classes.
					view.update_filters_ui();

					// Block container.
					composite.block( self.$component_filters );
					view.$blocked_element = self.$component_filters;

					setTimeout( function() {
						self.$component_selections.addClass( 'refresh_component_options' );
						// Update model and reload options.
						self.component_options_model.request_options( { current_page: 1, filters: self.find_active_filters() }, 'reload' );

					}, 120 );

					return false;
				},

				reset_filter: function( event ) {

					$( this ).blur();

					// Get active filters.
					var view                     = event.data.view,
						component_filter_options = $( this ).closest( '.component_filter' ).find( '.component_filter_option.selected' );

					if ( component_filter_options.length == 0 ) {
						return false;
					}

					component_filter_options.removeClass( 'selected' );

					// Add/remove 'active' classes.
					view.update_filters_ui();

					// Block container.
					composite.block( self.$component_filters );
					view.$blocked_element = self.$component_filters;

					setTimeout( function() {
						self.$component_selections.addClass( 'refresh_component_options' );
						// Update model and reload options.
						self.component_options_model.request_options( { current_page: 1, filters: self.find_active_filters() }, 'reload' );

					}, 120 );

					return false;
				},

				reset_filters: function( event ) {

					$( this ).blur();

					// Get active filters.
					var view                     = event.data.view,
						component_filter_options = self.$component_filters.find( '.component_filter_option.selected' );

					if ( component_filter_options.length == 0 ) {
						return false;
					}

					component_filter_options.removeClass( 'selected' );

					// Add/remove 'active' classes.
					view.update_filters_ui();

					// Block container.
					composite.block( self.$component_filters );
					view.$blocked_element = self.$component_filters;

					setTimeout( function() {
						self.$component_selections.addClass( 'refresh_component_options' );
						// Update model and reload options.
						self.component_options_model.request_options( { current_page: 1, filters: self.find_active_filters() }, 'reload' );

					}, 120 );

					return false;
				},

				/**
				 * Add active/filtered classes to the component filters markup, can be used for styling purposes.
				 */
				update_filters_ui: function() {

					var filters   = self.$component_filters.find( '.component_filter' ),
						all_empty = true;

					if ( filters.length == 0 ) {
						return false;
					}

					filters.each( function() {

						if ( $( this ).find( '.component_filter_option.selected' ).length == 0 ) {
							$( this ).removeClass( 'active' );
						} else {
							$( this ).addClass( 'active' );
							all_empty = false;
						}

					} );

					if ( all_empty ) {
						self.$component_filters.removeClass( 'filtered' );
					} else {
						self.$component_filters.addClass( 'filtered' );
					}
				},

				load_page: function( event ) {

					$( this ).blur();

					var view = event.data.view,
						page = parseInt( $( this ).data( 'page_num' ) );

					if ( page > 0 ) {

						// Block container.
						composite.block( self.$component_selections );
						view.$blocked_element = self.$component_selections;
						self.$component_selections.find( '.blockUI' ).addClass( 'bottom' );

						setTimeout( function() {
							// Update model and reload options.
							self.component_options_model.request_options( { current_page: page }, 'reload' );
						}, 120 );
					}

					return false;
				},

				load_more: function( event ) {

					$( this ).blur();

					var view  = event.data.view,
						page  = parseInt( self.find_pagination_param( 'current_page' ) ),
						pages = parseInt( self.find_pagination_param( 'pages' ) );

					if ( page > 0 && page < pages ) {

						// Block container.
						composite.block( self.$component_options );
						view.$blocked_element = self.$component_options;
						self.$component_options.find( '.blockUI' ).addClass( 'bottom' );

						setTimeout( function() {
							// Update model and reload options.
							self.component_options_model.request_options( { current_page: page + 1 }, 'append' );
						}, 120 );
					}

					return false;
				},

				order_by: function() {

					var orderby = $( this ).val();

					$( this ).blur();

					// Block container.
					composite.block( self.$component_options );

					setTimeout( function() {
						// Update model and reload options.
						self.component_options_model.request_options( { current_page: 1, orderby: orderby }, 'reload' );
					}, 120 );

					return false;
				},

				/**
				 * Renders active/inactive options in the DOM based on 'active_options' model attribute changes.
				 */
				render_options_state: function() {

					if ( ! composite.is_initialized ) {
						return false;
					}

					this.render_count++;

					composite.console_log( 'debug:views', '\nRendering "' + self.get_title() + '" options state...' );

					var model                    = self.component_options_model,
						active_options           = model.get( 'active_options' ),
						selected_product         = self.get_selected_product( false ),
						component_options_select = self.$component_options_select,
						thumbnail,
						thumbnail_container,
						radio_button,
						radio_button_container;

					/*
					 * Hide or grey-out inactive products.
					 */

					var thumbnails,
						radio_buttons,
						thumbnail_loop    = 0,
						thumbnail_columns = 1;

					if ( self.has_options_style( 'thumbnails' ) ) {
						thumbnails        = self.$component_options.find( '.component_option_thumbnails' );
						thumbnail_columns = parseInt( thumbnails.data( 'columns' ) );
					} else if ( self.has_options_style( 'radios' ) ) {
						radio_buttons = self.$component_options.find( '.component_option_radio_buttons' );
					}

					// Reset options.
					if ( ! component_options_select.data( 'select_options' ) ) {
						component_options_select.data( 'select_options', component_options_select.find( 'option:gt(0)' ).get() );
					}

					component_options_select.find( 'option:gt(0)' ).remove();
					component_options_select.append( component_options_select.data( 'select_options' ) );
					component_options_select.find( 'option:gt(0)' ).removeClass( 'disabled' );
					component_options_select.find( 'option:gt(0)' ).removeAttr( 'disabled' );

					if ( self.has_options_style( 'thumbnails' ) ) {
						thumbnails.find( '.no_compat_results' ).remove();
					} else if ( self.has_options_style( 'radios' ) ) {
						radio_buttons.find( '.no_compat_results' ).remove();
					}

					// Enable or disable options.
					component_options_select.find( 'option:gt(0)' ).each( function() {

						var product_id    = $( this ).val(),
							is_compatible = _.contains( active_options, product_id );

						if ( self.has_options_style( 'thumbnails' ) ) {
							thumbnail           = self.$component_options.find( '#component_option_thumbnail_' + $( this ).val() );
							thumbnail_container = thumbnail.closest( '.component_option_thumbnail_container' );
						} else if ( self.has_options_style( 'radios' ) ) {
							radio_button           = self.$component_options.find( '#component_option_radio_button_' + $( this ).val() );
							radio_button_container = radio_button.closest( '.component_option_radio_button_container' );
						}

						// Incompatible product.
						if ( ! is_compatible ) {

							if ( selected_product !== product_id ) {
								$( this ).addClass( 'disabled' );
							} else {
								$( this ).prop( 'disabled', 'disabled' );
							}

							if ( self.has_options_style( 'thumbnails' ) ) {
								thumbnail.addClass( 'disabled' );
							} else if ( self.has_options_style( 'radios' ) ) {
								radio_button.addClass( 'disabled' );
							}

						} else {

							if ( self.has_options_style( 'thumbnails' ) ) {
								thumbnail.removeClass( 'disabled' );
							} else if ( self.has_options_style( 'radios' ) ) {
								radio_button.removeClass( 'disabled' );
							}
						}

						// Update first/last/hidden thumbnail classes when appending results.
						if ( self.has_options_style( 'thumbnails' ) && self.append_results() ) {

							var thumbnail_container_class = '';

							if ( self.hide_disabled_products() && ! is_compatible ) {

								thumbnail_container_class = 'hidden';

							} else {

								thumbnail_loop++;

								// Add first/last class to compatible li elements.
								if ( ( ( thumbnail_loop - 1 ) % thumbnail_columns ) == 0 || thumbnail_columns == 1 ) {
									thumbnail_container_class = 'first';
								}

								if ( thumbnail_loop % thumbnail_columns == 0 ) {
									thumbnail_container_class += ' last';
								}
							}

							thumbnail_container.removeClass( 'first last hidden' );

							if ( thumbnail_container_class ) {
								thumbnail_container.addClass( thumbnail_container_class );
							}
						}

						// Update radio buttons.
						if ( self.has_options_style( 'radios' ) ) {
							if ( ! is_compatible ) {
								if ( self.hide_disabled_products() ) {
									radio_button_container.addClass( 'hidden' );
								}
								radio_button.find( 'input' ).prop( 'disabled', 'disabled' );
							} else {
								radio_button_container.removeClass( 'hidden' );
								radio_button.find( 'input' ).removeAttr( 'disabled' );
							}
						}

					} );

					// 'None' option handling.
					if ( self.is_optional() ) {
						// Update 'None' option radio.
						if ( self.has_options_style( 'radios' ) ) {
							radio_button           = self.$component_options.find( '#component_option_radio_button_0' );
							radio_button_container = radio_button.closest( '.component_option_radio_button_container' );

							radio_button.removeClass( 'disabled' );
							radio_button.find( 'input' ).removeAttr( 'disabled' );

							radio_button_container.removeClass( 'hidden' );
						}
						// Update 'None' option dropdown text.
						self.$component_options_select.find( 'option.none' ).html( wc_composite_params.i18n_none );
					} else {
						// Update 'None' option radio.
						if ( self.has_options_style( 'radios' ) ) {
							radio_button           = self.$component_options.find( '#component_option_radio_button_0' );
							radio_button_container = radio_button.closest( '.component_option_radio_button_container' );

							if ( self.hide_disabled_products() ) {
								radio_button_container.addClass( 'hidden' );
							}

							radio_button.addClass( 'disabled' );
							radio_button.find( 'input' ).prop( 'disabled', 'disabled' );
						}
						// Update 'None' option dropdown text.
						self.$component_options_select.find( 'option.none' ).html( wc_composite_params.i18n_select_an_option );
					}

					// The hiding bit.
					if ( self.hide_disabled_products() ) {
						component_options_select.find( 'option.disabled' ).remove();

						if ( self.has_options_style( 'thumbnails' ) ) {

							var thumbnail_elements         = thumbnails.find( '.component_option_thumbnail_container' ),
								visible_thumbnail_elements = thumbnail_elements.not( '.hidden' );

							if ( thumbnail_elements.length > 0 && visible_thumbnail_elements.length == 0 ) {
								thumbnails.find( '.component_option_thumbnails_container' ).after( '<p class="no_compat_results">' + wc_composite_params.i18n_no_compat_options + '</p>' );
								self.has_compat_results   = false;
								self.compat_results_count = 0;
							} else {
								self.has_compat_results   = true;
								self.compat_results_count = visible_thumbnail_elements.length;
							}

						} else if ( self.has_options_style( 'radios' ) ) {

							var radio_elements         = radio_buttons.find( '.component_option_radio_button_container' ),
								visible_radio_elements = radio_elements.not( '.hidden' );

							if ( radio_elements.length > 0 && visible_radio_elements.length == 0 ) {
								radio_buttons.find( '.component_option_radio_buttons_container' ).after( '<p class="no_compat_results">' + wc_composite_params.i18n_no_compat_options + '</p>' );
							}
						}

					} else {
						component_options_select.find( 'option.disabled' ).prop( 'disabled', 'disabled' );
					}

					/*
					 * Hide or grey-out inactive variations.
					 */

					if ( self.get_selected_product_type() === 'variable' ) {

						var selected_variation    = self.get_selected_variation( false ),
							product_variations    = self.$component_data.data( 'product_variations' ),
							compatible_variations = [],
							variation;

						for ( var i = 0; i < product_variations.length; i++ ) {

							var variation_id  = product_variations[ i ].variation_id.toString(),
								is_compatible = _.contains( active_options, variation_id );

							// Copy all variation objects but set the variation_is_active property to false in order to disable the attributes of incompatible variations.
							// Only if WC v2.3 and disabled variations are set to be visible.
							if ( wc_composite_params.is_wc_version_gte_2_3 === 'yes' && ! self.hide_disabled_variations() ) {

								var variation_has_empty_attributes = false;

								variation = $.extend( true, {}, product_variations[ i ] );

								if ( ! is_compatible ) {

									variation.variation_is_active = false;

									// Do not include incompatible variations with empty attributes - they can break stuff when prioritized.
									for ( var attr_name in variation.attributes ) {
										if ( variation.attributes[ attr_name ] === '' ) {
											variation_has_empty_attributes = true;
											break;
										}
									}

								}

								if ( ! variation_has_empty_attributes ) {
									compatible_variations.push( variation );
								}

							// Copy only compatible variations.
							// Only if disabled variations are set to be hidden.
							} else {
								if ( is_compatible ) {
									compatible_variations.push( product_variations[ i ] );
								} else {
									if ( parseInt( selected_variation ) === parseInt( variation_id ) ) {
										variation                     = $.extend( true, {}, product_variations[ i ] );
										variation.variation_is_active = false;
										compatible_variations.push( variation );
									}
								}
							}
						}

						// Put filtered variations in place.
						self.$component_summary_content.data( 'product_variations', compatible_variations );

						// Update the variations script.
						self.$component_summary_content.triggerHandler( 'reload_product_variations' );
					}

					// Run 'options_state_rendered' action - @see WC_CP_Composite_Dispatcher class.
					composite.actions.do_action( 'options_state_rendered', [ self ] );
				},

				/**
				 * Reload/append component options into view.
				 */
				render_response: function( response, render_type ) {

					var view        = this,
						load_height = 0,
						retry       = false;

					if ( typeof self.$component_options.get( 0 ).getBoundingClientRect().height !== 'undefined' ) {
						load_height = self.$component_options.get( 0 ).getBoundingClientRect().height;
					} else {
						load_height = self.$component_options.outerHeight();
					}

					// Lock height.
					self.$component_options.css( 'height', load_height );

					if ( response.result === 'success' ) {

						var component_options_select = self.$component_options_select,
							current_selection_id     = self.get_selected_product(),
							current_selection_id_raw = self.get_selected_product( false ),
							response_markup          = $( response.options_markup ),
							thumbnails_container     = self.$component_options_inner.find( '.component_option_thumbnails_container' ),
							new_thumbnail_options    = response_markup.find( '.component_option_thumbnail_container' );

						if ( render_type === 'append' && self.append_results() ) {
							new_thumbnail_options.find( '.component_option_thumbnail' ).addClass( 'appended' );
						}

						// Put new content in place.
						if ( render_type === 'append' ) {

							// Reset select options.
							if ( typeof( component_options_select.data( 'select_options' ) ) !== 'undefined' && component_options_select.data( 'select_options' ) !== false ) {
								component_options_select.find( 'option:gt(0)' ).remove();
								component_options_select.append( component_options_select.data( 'select_options' ) );
								component_options_select.find( 'option:gt(0)' ).removeClass( 'disabled' );
								component_options_select.find( 'option:gt(0)' ).removeAttr( 'disabled' );
							}

							// Appending product thumbnails...
							var new_select_options      = response_markup.find( 'select.component_options_select option' ),
								default_selected_option = component_options_select.find( 'option[value="' + current_selection_id_raw + '"]' );

							// Clean up and merge the existing + newly loaded select options.
							new_select_options = new_select_options.filter( ':gt(0)' );

							if ( current_selection_id_raw > 0 && thumbnails_container.find( '#component_option_thumbnail_' + current_selection_id_raw ).length == 0 ) {

								default_selected_option.remove();

								if ( current_selection_id > 0 && thumbnails_container.find( '#component_option_thumbnail_' + current_selection_id ).length > 0 ) {
									new_select_options = new_select_options.not( ':selected' );
								}

							} else {
								new_select_options = new_select_options.not( ':selected' );
							}

							new_select_options.appendTo( component_options_select );

							// Append thumbnails.
							new_thumbnail_options.appendTo( thumbnails_container );

						} else {

							self.component_options_model.trigger( 'component_options_flush', response, 'reload' );

							self.$component_options_inner.html( $( response_markup ).find( '.component_options_inner' ).html() );

							self.$component_options_select = component_options_select = self.$component_options_inner.find( 'select.component_options_select' );
							thumbnails_container           = self.$component_options_inner.find( '.component_option_thumbnails_container' );
						}

						var pages_left             = 0,
							results_per_page       = 0,
							pages_loaded           = 0,
							pages                  = 0,
							show_pagination        = false,
							pagination_markup_html = '';

						// Update pagination.
						if ( response.pagination_markup ) {

							var $pagination_markup = $( response.pagination_markup );
							pagination_markup_html = $pagination_markup.html();

							results_per_page = self.find_pagination_param( 'results_per_page', $pagination_markup );
							pages_loaded     = self.find_pagination_param( 'current_page', $pagination_markup );
							pages            = self.find_pagination_param( 'pages', $pagination_markup );
							pages_left       = pages - pages_loaded;

							if ( self.append_results() ) {

								if ( pages_loaded < pages ) {
									show_pagination = true;
								}

							} else {
								show_pagination = true;
							}
						}

						// Reset options.
						component_options_select.data( 'select_options', false );

						// Update component scenarios with new data.
						var scenario_data = composite.$composite_data.data( 'scenario_data' );

						if ( render_type === 'append' ) {

							// Append product scenario data.
							$.each( response.component_scenario_data, function( product_id, product_in_scenarios ) {
								scenario_data.scenario_data[ self.component_id ][ product_id ] = product_in_scenarios;
							} );

						} else {

							// Replace product scenario data.
							scenario_data.scenario_data[ self.component_id ] = response.component_scenario_data;
						}

						var initial_selection_id = current_selection_id_raw;
						current_selection_id     = component_options_select.val();

						if ( initial_selection_id !== current_selection_id ) {
							composite.console_log( 'error', '\nInitial selection not found in DOM.' );
						} else {

							// Count how many of the newly loaded results are actually visible.
							if ( render_type === 'append' && self.append_results() && self.hide_disabled_products() ) {

								var active_options_pre,
									active_options_post,
									options_added;

								// Number of active products before appending new ones.
								active_options_pre = _.intersection( view.model.get( 'available_options' ), view.model.get( 'active_options' ) ).length;

								// Update model with new available options.
								view.model.refresh_options( self.find_available_options() );

								// Number of active products after appending new ones.
								active_options_post = _.intersection( view.model.get( 'available_options' ), view.model.get( 'active_options' ) ).length;

								// Number of active products added.
								options_added = active_options_post - active_options_pre;

								view.append_results_nesting_count += options_added;

								if ( view.append_results_nesting_count < results_per_page && pages_left > 0 ) {

									view.append_results_nesting++;
									retry = true;

									if ( view.append_results_nesting > 10 ) {
										if ( window.confirm( wc_composite_params.i18n_reload_threshold_exceeded.replace( '%s', self.get_title() ) ) ) {
											view.append_results_nesting = 0;
										} else {
											retry = false;
										}
									}
								}

							} else {

								// Update model with new available options.
								view.model.refresh_options( self.find_available_options() );
							}

							// Update ui.
							if ( ! retry ) {

								view.append_results_nesting_count = 0;
								view.append_results_nesting       = 0;
							}
						}

						if ( ! retry ) {

							var render_count_pre = view.render_count;

							// Ensure active options have rendered at least once, because the model state may not change.
							if ( render_count_pre === view.render_count ) {
								view.render_options_state();
							}

							// Run 'component_options_loaded' action - @see WC_CP_Actions_Dispatcher class reference.
							composite.actions.do_action( 'component_options_loaded', [ self ] );

							// Update pagination.
							if ( show_pagination ) {
								self.$component_pagination.html( pagination_markup_html );
								self.$component_pagination.slideDown( 200 );
							} else {
								self.$component_pagination.slideUp( { duration: 200, always: function() {
									self.$component_pagination.html( pagination_markup_html );
								} } );
							}

							// Preload images before proceeding.
							var thumbnail_images = self.$component_options_inner.find( '.component_option_thumbnail_container:not(.hidden) img' );

							var finalize = function() {

								if ( thumbnail_images.length > 0 && thumbnails_container.is( ':visible' ) ) {

									var wait = false;

									thumbnail_images.each( function() {

										var image = $( this );

										if ( image.height() === 0 ) {
											wait = true;
											return false;
										}

									} );

									if ( wait ) {
										setTimeout( function() {
											finalize();
										}, 100 );
									} else {
										view.animate_component_options( render_type, load_height );
									}
								} else {
									view.animate_component_options( render_type, load_height );
								}
							};

							setTimeout( function() {
								finalize();
							}, 10 );

						} else {
							// Update model and request more options.
							view.model.request_options( { current_page: view.model.get( 'current_page' ) + 1 }, 'append' );
						}

					} else {

						// Show failure message.
						self.$component_options_inner.html( response.options_markup );

						view.animate_component_options( render_type, load_height );
					}
				},

				animate_component_options: function( render_type, load_height ) {

					var view = this;

					// Measure height.
					var new_height     = self.$component_options_inner.outerHeight( true ),
						animate_height = false;

					if ( Math.abs( new_height - load_height ) > 1 ) {
						animate_height = true;
					} else {
						self.$component_options.css( 'height', 'auto' );
					}

					var appended = {};

					if ( self.append_results() ) {
						appended = self.$component_selections.find( '.appended' );
						appended.removeClass( 'appended' );
					}

					// Animate component options container.
					if ( animate_height ) {

						if ( ! render_type === 'append' ) {
							self.$component_selections.removeClass( 'refresh_component_options' );
						}

						self.$component_options.animate( { 'height' : new_height }, { duration: 250, queue: false, always: function() {
							self.$component_options.css( { 'height' : 'auto' } );
							setTimeout( function() {
								self.$component_selections.removeClass( 'refresh_component_options' );
								composite.unblock( view.$blocked_element );
							}, 100 );
						} } );

					} else {
						setTimeout( function() {
							self.$component_selections.removeClass( 'refresh_component_options' );
							composite.unblock( view.$blocked_element );
						}, 250 );
					}

				}

			} );

			var obj = new View( opts );
			return obj;
		};

		/**
		 * Updates the model data from UI interactions and listens to the component selection model for updated content.
		 */
		this.Component_Selection_View = function( component, opts ) {

			var self = component;
			var	View = Backbone.View.extend( {

				$relocation_origin:           false,
				relocated:                    false,

				relocate_component_content:   false,
				relocate_to_origin:           false,
				$relocation_target:           false,
				$relocation_reference:        false,
				load_height:                  0,

				resetting_product:            false,
				resetting_variation:          false,

				flushing_component_options:   false,

				update_selection_title_timer: false,

				initialize: function() {

					/**
					 * Update model on changing a component option.
					 */
					self.$el.on( 'change', '.component_options select.component_options_select', { view: this }, this.option_changed );

					/**
					 * Update model data when a new variation is selected.
					 */
					self.$el.on( 'woocommerce_variation_has_changed', { view: this }, function( event ) {
						// Update model.
						event.data.view.model.update_selected_variation();
						// Ensure min/max constraints are always honored.
						self.$component_quantity.trigger( 'change' );
						// Remove images class from composited_product_images div in order to avoid styling issues.
						self.$component_summary_content.find( '.composited_product_images' ).removeClass( 'images' );
					} );

					/**
					 * Add 'images' class to composited_product_images div when initiating a variation selection change.
					 */
					self.$el.on( 'woocommerce_variation_select_change', function() {
						// Required by the variations script to flip images.
						self.$component_summary.find( '.composited_product_images' ).addClass( 'images' );
						// Reset component prices.
						self.$component_data.data( 'price', 0.0 );
						self.$component_data.data( 'regular_price', 0.0 );

						var custom_data = self.$component_data.data( 'custom' );

						custom_data[ 'price_tax' ] = 1.0;
					} );

					/**
					 * Update composite totals and form inputs when a new variation is selected.
					 */
					self.$el.on( 'found_variation', function( event, variation ) {
						// Update component prices.
						self.$component_data.data( 'price', variation.price );
						self.$component_data.data( 'regular_price', variation.regular_price );

						var custom_data = self.$component_data.data( 'custom' );

						custom_data[ 'price_tax' ] = variation.price_tax;
					} );

					/**
					 * Update model upon changing quantities.
					 */
					self.$el.on( 'change', '.component_wrap input.qty', function() {

						var min = parseFloat( $( this ).attr( 'min' ) ),
							max = parseFloat( $( this ).attr( 'max' ) );

						if ( min >= 0 && ( parseFloat( $( this ).val() ) < min || isNaN( parseFloat( $( this ).val() ) ) ) ) {
							$( this ).val( min );
						}

						if ( max > 0 && parseFloat( $( this ).val() ) > max ) {
							$( this ).val( max );
						}

						if ( ! self.initializing_scripts ) {
							self.component_selection_model.update_selected_quantity();
						}
					} );

					/**
					 * Initialize prettyPhoto script when component selection scripts are initialized.
					 */
					self.$el.on( 'wc-composite-component-loaded', function() {

						if ( $.isFunction( $.fn.prettyPhoto ) ) {

							self.$component_summary_content.find( 'a[data-rel^="prettyPhoto"]' ).prettyPhoto( {
								hook: 'data-rel',
								social_tools: false,
								theme: 'pp_woocommerce',
								horizontal_padding: 20,
								opacity: 0.8,
								deeplinking: false
							} );
						}
					} );

					/**
					 * On clicking the clear options button.
					 */
					self.$el.on( 'click', '.clear_component_options', function() {

						if ( $( this ).hasClass( 'reset_component_options' ) ) {
							return false;
						}

						var selection = self.$component_options_select;

						selection.val( '' ).change();

						return false;
					} );

					/**
					 * On clicking the reset options button.
					 */
					self.$el.on( 'click', '.reset_component_options', function() {

						var selection = self.$component_options_select;

						self.unblock_step_inputs();

						self.set_active();

						selection.val( '' ).change();

						self.block_next_steps();

						return false;
					} );

					/**
					 * Update model upon changing addons selections.
					 */
					self.$el.on( 'updated_addons', this.updated_addons_handler );

					/**
					 * Update composite totals when a new NYP price is entered.
					 */
					self.$el.on( 'woocommerce-nyp-updated-item', this.updated_nyp_handler );

					/*
					 * When returning to a visited component with relocated selection details,
					 * reset the position of the relocated container if the 'relocated_content_reset_on_return' flag is set to 'yes'.
					 */
					if ( wc_composite_params.relocated_content_reset_on_return === 'yes' ) {
						composite.actions.add_action( 'active_step_changed', this.active_step_changed_handler, 110, this );
					}

					/**
					 * Render selection details responses into view.
					 */
					this.listenTo( this.model, 'component_selection_details_loaded', this.render_response );

					/**
					 * Reset relocated content before flushing outdated component options.
					 */
					this.listenTo( self.component_options_model, 'component_options_flush', this.component_options_flush_handler );

					/**
					 * Update the selection title when the quantity is changed.
					 */
					composite.actions.add_action( 'component_quantity_changed', this.quantity_changed_handler, 100, this );
				},

				/**
				 * Resets the position of the relocated container when the active step changes.
				 */
				active_step_changed_handler: function( step ) {

					if ( self.step_id === step.step_id ) {
						if ( this.is_relocated() ) {
							this.reset_relocated_content();
						}
					}
				},

				/**
				 * Updates the selection title when the quantity is changed.
				 */
				quantity_changed_handler: function( step ) {

					if ( step.step_id === self.step_id ) {
						this.update_selection_title( this.model );
					}
				},

				/**
				 * Updates the model upon changing addons selections.
				 */
				updated_addons_handler: function() {

					self.component_selection_model.update_selected_addons();
				},

				/**
				 * Updates the composite data model upon changing addons selections.
				 */
				updated_nyp_handler: function() {

					self.component_selection_model.update_nyp();
				},

				/**
				 * Appends a quantity suffix to the selection title.
				 */
				update_selection_title: function( model ) {

					var view  = this;

					model = typeof ( model ) === 'undefined' ? view.model : model;

					if ( self.get_selected_product_id() > 0 ) {

						composite.console_log( 'debug:views', '\nScheduled update of "' + self.get_title() + '" selection title.' );

						clearTimeout( view.update_selection_title_timer );
						view.update_selection_title_timer = setTimeout( function() {
							view.update_selection_title_task( model );
						}, 10 );
					}
				},

				/**
				 * Appends quantity data to the selected product title.
				 */
				update_selection_title_task: function( model ) {

					composite.console_log( 'debug:views', '\nUpdating "' + self.get_title() + '" selection title...' );

					var selection_qty            = parseInt( model.get( 'selected_quantity' ) ),
						selection_title          = self.$component_options_select.find( 'option:selected' ).data( 'title' ),
						selection_qty_string     = selection_qty > 1 ? wc_composite_params.i18n_qty_string.replace( '%s', selection_qty ) : '',
						selection_title_incl_qty = wc_composite_params.i18n_title_string.replace( '%t', selection_title ).replace( '%q', selection_qty_string ).replace( '%p', '' ),
						$title_html              = self.$component_summary_content.find( '.composited_product_title' );

					$title_html.html( selection_title_incl_qty );
				},

				/**
				 * Initializes the view by triggering selection-related scripts.
				 */
				init_dependencies: function() {

					self.init_scripts();
				},

				/**
				 * Blocks the composite form and adds a waiting ui cue in the working element.
				 */
				block: function() {

					if ( self.has_options_style( 'thumbnails' ) ) {
						composite.block( self.$component_options.find( '.component_option_thumbnails .selected' ) );
					} else {
						composite.block( self.$component_options );
					}
				},

				/**
				 * Unblocks the composite form and removes the waiting ui cue from the working element.
				 */
				unblock: function() {

					if ( self.has_options_style( 'thumbnails' ) ) {
						composite.unblock( self.$component_options.find( '.component_option_thumbnails .selected' ) );
					} else {
						composite.unblock( self.$component_options );
					}
				},

				/**
				 * Update model on changing a component option.
				 */
				option_changed: function( event ) {

					var view                = event.data.view,
						selected_product_id = $( this ).val();

					$( this ).blur();

					// Exit if triggering 'change' for the existing selection.
					if ( self.get_selected_product( false ) === selected_product_id ) {
						return false;
					}

					// Toggle thumbnail/radio selection state.
					if ( self.has_options_style( 'thumbnails' ) ) {
						self.$component_options.find( '.component_option_thumbnails .selected' ).removeClass( 'selected disabled' );
						self.$component_options.find( '#component_option_thumbnail_' + selected_product_id ).addClass( 'selected' );
					} else if ( self.has_options_style( 'radios' ) ) {
						var $selected = self.$component_options.find( '.component_option_radio_buttons .selected' );
						$selected.removeClass( 'selected disabled' );
						$selected.find( 'input' ).prop( 'checked', false );
						self.$component_options.find( '#component_option_radio_button_' + ( selected_product_id === '' ? '0' : selected_product_id ) ).addClass( 'selected' ).find( 'input' ).prop( 'checked', true );
					}

					// Remove all event listeners.
					self.$component_summary_content.removeClass( 'variations_form bundle_form cart' );
					self.$component_summary_content.off().find( '*' ).off();

					if ( selected_product_id !== '' ) {

						// Block composite form + add waiting cues.
						view.block();

						// Add updating class to content.
						self.$component_content.addClass( 'updating' );

						setTimeout( function() {
							// Request product details from model and let the model update itself.
							view.model.request_details( selected_product_id );
						}, 120 );

					} else {

						// Handle selection resets within the view, but update the model data.
						view.model.selected_product = '';
						view.render_response( false );
					}

					return false;
				},

				/**
				 * Update view with new selection details passed by model.
				 */
				render_response: function( response ) {

					var view                        = this,
						selected_product            = this.model.selected_product,
						relocations_allowed         = this.relocations_allowed();

					if ( typeof self.$component_content.get( 0 ).getBoundingClientRect().height !== 'undefined' ) {
						view.load_height = self.$component_content.get( 0 ).getBoundingClientRect().height;
					} else {
						view.load_height = self.$component_content.outerHeight();
					}

					view.relocate_component_content = false;
					view.relocate_to_origin         = false;
					view.$relocation_target         = false;
					view.$relocation_reference      = false;

					// Save initial location of component_content div.
					if ( relocations_allowed ) {
						if ( false === view.$relocation_origin ) {
							view.$relocation_origin = $( '<div class="component_content_origin">' );
							self.$component_content.before( view.$relocation_origin );
						}
					}

					// Check if fetched component content will be relocated under current product thumbnail.
					if ( relocations_allowed ) {

						var relocation_params           = view.get_content_relocation_params();

						view.$relocation_reference      = relocation_params.reference;
						view.relocate_component_content = relocation_params.relocate;

					} else if ( view.is_relocated() ) {

						view.relocate_component_content = true;
						view.relocate_to_origin         = true;
					}

					// Get the selected product data.
					if ( selected_product !== '' ) {

						// Check if component_content div must be relocated.
						if ( view.relocate_component_content ) {

							if ( view.relocate_to_origin ) {

								// Animate component content height to 0 while scrolling as much as its height.
								// Then, reset relocation and update content.
								self.$component_content.animate( { 'height': 0 }, { duration: 200, queue: false, always: function() {
									view.reset_relocated_content();
									view.update_content( response.markup );
								} } );

								view.load_height = 0;

							} else {

								var was_in_viewport = self.$component_content.wc_cp_is_in_viewport();

								view.relocated = true;

								self.$component_content.addClass( 'relocated' );
								self.$component_content.addClass( 'relocating' );

								view.$relocation_target = $( '<li class="component_option_content_container">' );
								view.$relocation_reference.after( view.$relocation_target );

								// Animate component content height to 0 while scrolling as much as its height.
								// Then, update content.
								self.$component_content.animate( { 'height': 0 }, { duration: 200, queue: false, always: function() {
									view.update_content( response.markup );
								} } );

								if ( self.$component_content.offset().top < view.$relocation_target.offset().top && ! was_in_viewport ) {
									wc_cp_scroll_viewport( 'relative', { offset: view.load_height, timeout: 0, duration: 200, queue: false } );
								}

								view.load_height = 0;
							}

						} else {

							// Lock height.
							self.$component_content.css( 'height', view.load_height );

							// Process response content.
							view.update_content( response.markup );
						}

					} else {

						var animate = true;

						if ( view.resetting_product ) {
							animate = false;
						}

						if ( animate ) {

							// Set to none just in case a script attempts to read this.
							self.$component_data.data( 'product_type', 'none' );

							// Allow the appended message container to remain visible.
							var navigation_movable_height = composite.$composite_navigation_movable.is( ':visible' ) ? composite.$composite_navigation_movable.outerHeight( true ) : 0;
							var reset_height              = view.is_relocated() ? 0 : ( self.$component_summary.outerHeight( true ) - self.$component_summary_content.innerHeight() - navigation_movable_height );

							// Animate component content height.
							self.$component_content.animate( { 'height': reset_height }, { duration: 200, queue: false, always: function() {

								// Reset content.
								view.reset_content();

								self.$component_content.css( { 'height': 'auto' } );

							} } );

						} else {
							// Reset content.
							view.reset_content();
						}
					}
				},

				/**
				 * Updates view with new selection details markup.
				 */
				update_content: function( content ) {

					var view = this;

					// Put content in place.
					self.$component_summary_content.html( content );

					// Relocate content.
					if ( view.relocate_component_content ) {
						self.$component_content.appendTo( view.$relocation_target );
						self.$component_options.find( '.component_option_content_container' ).not( view.$relocation_target ).remove();
					}

					view.updated_content();

					var animation_delay = 300;

					setTimeout( function() {
						view.animate_updated_content();
					}, animation_delay );
				},

				/**
				 * Update model and trigger scripts after updating view with selection content.
				 */
				updated_content: function() {

					if ( this.model.selected_product > 0 ) {
						self.init_scripts();
					} else {
						self.init_scripts( false );
					}

					// Update the model.
					this.model.update_selected_product();

					// Refresh options state.
					self.component_options_model.refresh_options_state( self );
				},

				animate_updated_content: function() {

					// Measure height.
					var new_height     = self.$component_summary.outerHeight( true ),
						animate_height = false,
						view           = this;

					if ( this.relocate_component_content || Math.abs( new_height - this.load_height ) > 1 ) {
						animate_height = true;
					} else {
						self.$component_content.css( 'height', 'auto' );
					}

					if ( this.is_relocated() ) {
						self.$component_content.removeClass( 'relocating' );
					}

					// Animate component content height and scroll to selected product details.
					if ( animate_height ) {

						composite.console_log( 'debug:events', 'Starting updated content animation...' );

						// Animate component content height.
						self.$component_content.animate( { 'height': new_height }, { duration: 200, queue: false, always: function() {

							composite.console_log( 'debug:events', 'Ended updated content animation.' );

							// Scroll...
							wc_cp_scroll_viewport( self.$component_content, { offset: 50, partial: composite.settings.layout !== 'paged', scroll_method: 'middle', duration: 200, queue: false, always_on_complete: true, on_complete: function() {

								// Reset height.
								self.$component_content.css( { 'height' : 'auto' } );

								// Unblock.
								view.unblock();
								self.$component_content.removeClass( 'updating' );

							} } );

						} } );

					} else {

						// Scroll.
						wc_cp_scroll_viewport( self.$component_content, { offset: 50, partial: composite.settings.layout !== 'paged', scroll_method: 'middle', duration: 200, queue: false, always_on_complete: true, on_complete: function() {

							// Unblock.
							view.unblock();
							self.$component_content.removeClass( 'updating' );

						} } );
					}

				},

				reset_content: function() {

					// Reset content.
					self.$component_summary_content.html( '<div class="component_data" data-price="0" data-regular_price="0" data-product_type="none" style="display:none;"></div>' );

					// Remove appended navi.
					if ( self.$el.find( '.composite_navigation.movable' ).length > 0 ) {
						composite.$composite_navigation_movable.addClass( 'hidden' );
					}

					this.reset_relocated_content();
					this.updated_content();
				},

				/**
				 * Move relocated view back to its original position before reloading component options into our Component_Options_View.
				 */
				component_options_flush_handler: function( response, render_type ) {

					if ( this.is_relocated() && render_type === 'reload' && response.result === 'success' ) {
						this.flushing_component_options = true;
						this.reset_relocated_content();
						this.flushing_component_options = false;
					}
				},

				/**
				 * Move relocated view back to its original position.
				 */
				reset_relocated_content: function() {

					if ( this.is_relocated() ) {

						// Hide message if visible.
						self.$component_message.hide();

						if ( this.flushing_component_options ) {
							self.$component_content.hide();
						}

						// Move content to origin.
						self.component_selection_view.$relocation_origin.after( self.$component_content );

						if ( this.flushing_component_options ) {
							setTimeout( function() {
								self.$component_content.slideDown( 250 );
								// Scroll to component options.
								wc_cp_scroll_viewport( 'relative', { offset: -self.$component_summary.outerHeight( true ), timeout: 0, duration: 250, queue: false } );
							}, 200 );
						}

						// Remove origin and relocation container.
						self.component_selection_view.$relocation_origin.remove();
						self.component_selection_view.$relocation_origin = false;
						self.$component_options.find( '.component_option_content_container' ).remove();

						if ( false === this.flushing_component_options ) {
							if ( this.model.selected_product === '' ) {
								// Scroll to selections.
								wc_cp_scroll_viewport( self.$component_selections, { partial: false, duration: 250, queue: false } );
							}
						}

						this.relocated = false;
						self.$component_content.removeClass( 'relocated' );
					}
				},

				/**
				 * True if the view is allowed to relocate below the thumbnail.
				 */
				relocations_allowed: function() {

					if ( composite.settings.layout === 'paged' && self.append_results() && self.has_options_style( 'thumbnails' ) && ! self.$el.hasClass( 'disable-relocations' ) ) {
						if ( self.$component_options.height() > $wc_cp_window.height() ) {
							return true;
						}
					}

					return false;
				},

				/**
				 * True if the component_content container is relocated below the thumbnail.
				 */
				is_relocated: function() {

					return this.relocated;
				},

				/**
				 * Get relocation parameters for this view, when allowed. Returns:
				 *
				 * - A thumbnail (list item) to be used as the relocation reference (the relocated content should be right after this element).
				 * - A boolean indicating whether the view should be moved under the reference element.
				 */
				get_content_relocation_params: function() {

					var relocate_component_content = false,
						$relocation_reference      = false,
						selected_thumbnail         = self.$component_options.find( '.component_option_thumbnail.selected' ).closest( '.component_option_thumbnail_container' ),
						thumbnail_to_column_ratio  = selected_thumbnail.outerWidth( true ) / self.$component_options.outerWidth(),
						last_thumbnail_in_row      = ( selected_thumbnail.hasClass( 'last' ) || thumbnail_to_column_ratio > 0.6 ) ? selected_thumbnail : selected_thumbnail.nextAll( '.last' ).first();

					if ( last_thumbnail_in_row.length > 0 ) {
						$relocation_reference = last_thumbnail_in_row;
					} else {
						$relocation_reference = self.$component_options.find( '.component_option_thumbnail_container' ).last();
					}

					if ( $relocation_reference.next( '.component_option_content_container' ).length === 0 ) {
						relocate_component_content = true;
					}

					return { reference: $relocation_reference,  relocate: relocate_component_content };
				}

			} );

			var obj = new View( opts );
			return obj;
		};

		/**
		 * Updates component title elements by listening to step model changes.
		 */
		this.Component_Title_View = function( component, opts ) {

			var self = component;
			var View = Backbone.View.extend( {

				initialize: function() {

					/**
					 * On clicking toggled component titles.
					 */
					this.$el.on( 'click', this.clicked_title_handler );

					if ( composite.settings.layout === 'progressive' && self.has_toggle() ) {

						/**
					 	 * Update view element when its 'step_access_model' lock state changes.
						 */
						this.listenTo( self.step_access_model, 'change:is_locked', this.render_navigation_state );
						/**
					 	 * Update view element on transitioning to a new step.
						 */
						composite.actions.add_action( 'active_step_changed', this.active_step_changed_handler, 100, this );
					}
				},

				clicked_title_handler: function() {

					$( this ).blur();

					if ( ! self.has_toggle() ) {
						return false;
					}

					if ( self.is_current() ) {
						return false;
					}

					if ( $( this ).hasClass( 'inactive' ) ) {
						return false;
					}

					if ( composite.settings.layout === 'single' ) {
						wc_cp_toggle_element( self.$el, self.$component_inner );
					} else {
						composite.navigate_to_step( self );
					}

					return false;
				},

				active_step_changed_handler: function() {

					this.render_navigation_state();
				},

				/*
				 * Update progressive component title based on lock state.
				 */
				render_navigation_state: function() {

					if ( composite.settings.layout === 'progressive' && self.has_toggle() ) {

						composite.console_log( 'debug:views', '\nUpdating "' + self.get_title() + '" component title state...' );

						if ( self.is_current() ) {
							this.$el.removeClass( 'inactive' );
						} else {
							if ( self.is_locked() ) {
								this.$el.addClass( 'inactive' );
							} else {
								this.$el.removeClass( 'inactive' );
							}
						}
					}
				}

			} );

			var obj = new View( opts );
			return obj;
		};

	};



	/**
	 * Actions dispatcher that triggers actions in response to specific events.
	 * When multiple models (or both models & views) need to respond to a specific event, model handlers must be run before view handlers (and in the right sequence) to ensure that views have access to correctly updated model data.
	 *
	 * Without a dispatcher:
	 *
	 *  - declaring those handlers in the right sequence can make our code hard to read, and
	 *  - it is very hard for 3rd party code to add handlers at a specific point in the callback execution queue.
	 *
	 * The dispatcher:
	 *
	 *  - translates key events into actions and provides an API for declaring callbacks for specific actions in the desired priority, and
	 *  - makes code a lot easier to read since internal functionality is abstracted (models/views listen to key, internal events directly).
	 *
	 *
	 * A complete reference of all application actions & callbacks is provided in the "Actions Reference" below.
	 *
	 */
	wc_cp_classes.WC_CP_Actions_Dispatcher = function( composite ) {

		/*
		 *--------------------------*
		 *                          *
		 *   Actions Reference      *
		 *                          *
		 *--------------------------*
		 *
		 *--------------------------*
		 *   1. Components          *
		 *--------------------------*
		 *
		 *
		 * Action 'show_step':
		 *
		 * Triggered when navigating to a step.
		 *
		 * @param  WC_CP_Step  step
		 *
		 * @hooked Action 'show_step_{step.step_id}' - 0
		 *
		 *
		 *
		 * Action 'show_step_{step.step_id}':
		 *
		 * Triggered when navigating to the step with id === step_id.
		 *
		 * @hooked WC_CP_Step::autoscroll_single - 10
		 *
		 *
		 *
		 * Action 'active_step_changed':
		 *
		 * Triggered when the active step changes.
		 *
		 * @param  WC_CP_Step  step
		 *
		 * @hooked Action 'active_step_changed_{step.step_id}'                    - 0
		 * @hooked Component_Title_View::active_step_changed_handler              - 100
		 * @hooked Composite_Pagination_View::active_step_changed_handler         - 100
		 * @hooked Composite_Summary_View::active_step_changed_handler            - 100
		 * @hooked Composite_Widget_View::active_step_changed_handler             - 100
		 * @hooked Step_Validation_View::active_step_changed_handler              - 100
		 * @hooked Component_Selection_View::active_step_transition_start_handler - 110
		 * @hooked WC_CP_Step::autoscroll_paged                                   - 120
		 *
		 *
		 *
		 * Action 'active_step_changed_{step.step_id}':
		 *
		 * Triggered when the step with id === step_id becomes active.
		 *
		 * @hooked Component_Options_Model::active_step_changed_handler - 10
		 *
		 *
		 *
		 * Action 'active_step_transition_start':
		 *
		 * Triggered when the transition animation to an activated step starts.
		 *
		 * @param  WC_CP_Step  step
		 *
		 * @hooked Action 'active_step_transition_start_{step.step_id}'            - 0
		 * @hooked Composite_Navigation_View::active_step_transition_start_handler - 100
		 *
		 *
		 *
		 * Action 'active_step_transition_start_{step.step_id}':
		 *
		 * Triggered when the transition animation to the activated step with id === step_id starts.
		 *
		 * @hooked WC_CP_Step::autoload_compat_options - 10
		 *
		 *
		 *
		 * Action 'active_step_transition_end':
		 *
		 * Triggered when the transition animation to an activated step ends.
		 *
		 * @param  WC_CP_Step  step
		 *
		 * @hooked Action 'active_step_transition_end_{step.step_id}' - 0
		 *
		 *
		 *
		 * Action 'active_step_transition_end_{step.step_id}':
		 *
		 * Triggered when the transition animation to the activated step with id === step_id ends.
		 *
		 * @hooked WC_CP_Step::autoscroll_progressive                       - 10
		 * @hooked WC_CP_Step::autoscroll_paged_relocated                   - 10
		 * @hooked Step_Validation_View::active_step_transition_end_handler - 100
		 *
		 *
		 *
		 * Action 'component_selection_changed':
		 *
		 * Triggered when the product/variation selection of a Component changes.
		 *
		 * @param  WC_CP_Component  component
		 *
		 * @hooked WC_CP_Scenarios_Manager::selection_changed_handler   - 10
		 * @hooked Step_Validation_Model::selection_changed_handler     - 20
		 * @hooked Composite_Data_Model::selection_changed_handler      - 30
		 * @hooked Composite_Summary_View::selection_changed_handler    - 100
		 * @hooked Composite_Navigation_View::selection_changed_handler - 100
		 *
		 *
		 *
		 * Action 'component_selection_content_changed':
		 *
		 * Triggered when options/content associated with a selected product change, requiring re-validation, re-calculation of totals and re-freshes of all associated views.
		 *
		 * @param  WC_CP_Component  component
		 *
		 * @hooked Step_Validation_Model::selection_changed_handler        - 20
		 * @hooked Composite_Data_Model::selection_content_changed_handler - 30
		 * @hooked Composite_Summary_View::selection_changed_handler       - 100
		 * @hooked Composite_Navigation_View::selection_changed_handler    - 100
		 *
		 *
		 *
		 * Action 'component_quantity_changed':
		 *
		 * Triggered when the quantity of a selected product/variation changes.
		 *
		 * @param  WC_CP_Component  component
		 *
		 * @hooked Composite_Data_Model::quantity_changed_handler     - 10
		 * @hooked Composite_Summary_View::quantity_changed_handler   - 100
		 * @hooked Component_Selection_View::quantity_changed_handler - 100
		 *
		 *
		 *
		 * Action 'component_addons_changed':
		 *
		 * Triggered when the Product Add-ons associated with a selected product/variation change.
		 *
		 * @param  WC_CP_Component  component
		 *
		 * @hooked Composite_Data_Model::addons_changed_handler   - 10
		 *
		 *
		 *
		 * Action 'component_nyp_changed':
		 *
		 * Triggered when the price of a selected Name-Your-Price product/variation changes.
		 *
		 * @param  WC_CP_Component  component
		 *
		 * @hooked Composite_Data_Model::nyp_changed_handler   - 10
		 *
		 *
		 *
		 * Action 'component_validation_message_changed':
		 *
		 * Triggered when the validation notices associated with a Component change.
		 *
		 * @param  WC_CP_Component  component
		 *
		 * @hooked Composite_Data_Model::validation_message_changed_handler - 10
		 *
		 *
		 *
		 * Action 'active_options_changed':
		 *
		 * Triggered when the active/enabled Component Options of a Component change.
		 *
		 * @param  WC_CP_Component  component
		 *
		 * @hooked Action 'active_options_changed_{step.step_id}' - 0
		 *
		 *
		 *
		 * Action 'active_options_changed_{step.step_id}':
		 *
		 * Triggered when the active/enabled Component Options of the Component with id === step_id change.
		 *
		 * @hooked Component_Options_Model::render_options_state - 10
		 *
		 *
		 *
		 * Action 'available_options_changed':
		 *
		 * Triggered when the Component Options available in a Component change.
		 *
		 * @param  WC_CP_Component  component
		 *
		 * @hooked WC_CP_Scenarios_Manager::available_options_changed_handler - -10
		 * @hooked Action 'available_options_changed_{step.step_id}'          - 0
		 *
		 *
		 *
		 * Action 'available_options_changed_{step.step_id}':
		 *
		 * Triggered when the Component Options available in the Component with id === step_id change.
		 *
		 * @hooked Component_Options_Model::available_options_changed_handler - 10
		 *
		 *
		 *
		 * Action 'options_state_rendered':
		 *
		 * Triggered after the active Component Options have been rendered by the Component_Options_View Backbone view.
		 *
		 * @param  WC_CP_Component  component
		 *
		 * @hooked void
		 *
		 *
		 *
		 * Action 'component_options_loaded':
		 *
		 * Triggered after a new set of Component Options has been loaded and rendered by the Component_Options_View Backbone view.
		 *
		 * @param  WC_CP_Component  component
		 *
		 * @hooked Action 'component_options_loaded_{step.step_id}' - 0
		 *
		 *
		 *
		 * Action 'component_options_loaded_{step.step_id}':
		 *
		 * @hooked void
		 *
		 *
		 *
		 * Action 'component_scripts_initialized':
		 *
		 * Triggered when the details associated with a new product selection are rendered by the Component_Selection_View, once the associated product type scripts have been initialized.
		 *
		 * @param  WC_CP_Component  component
		 *
		 * @hooked Action 'component_scripts_initialized_{step.step_id}' - 0
		 *
		 *
		 *
		 * Action 'component_scripts_initialized_{step.step_id}':
		 *
		 * @hooked void
		 *
		 *
		 *
		 * Action 'component_totals_changed':
		 *
		 * Triggered when the price of a Component changes.
		 *
		 * @param WC_CP_Component  component
		 *
		 * @hooked Composite_Data_Model::calculate_totals                   - 10
		 * @hooked Composite_Summary_View::component_totals_changed_handler - 100
		 *
		 *
		 *
		 * Action 'validate_step':
		 *
		 * Triggered during step validation, before the Step_Validation_Model has been updated with the validation results.
		 *
		 * @param  WC_CP_Step  step
		 * @param  boolean     is_valid
		 *
		 * @hooked void
		 *
		 *
		 *
		 * Action 'component_summary_content_updated':
		 *
		 * Triggered when the content associated with a specific Component in a Composite_Summary_View view changes.
		 *
		 * @param  WC_CP_Component         component
		 * @param  Composite_Summary_View  view
		 *
		 * @hooked void
		 *
		 *
		 *
		 *--------------------------*
		 *   2. Scenarios           *
		 *--------------------------*
		 *
		 *
		 * Action 'active_scenarios_changed':
		 *
		 * Triggered when the active scenarios change in response to a product/variation selection change in a Component.
		 *
		 * @param  WC_CP_Component  triggered_by
		 *
		 * @hooked Component_Options_Model::refresh_options_state - 10
		 *
		 *
		 *
		 * Action 'active_scenarios_updated':
		 *
		 * Triggered when the active scenarios are updated (but not necessarily changed) in response to a product/variation selection change in a Component.
		 *
		 * @param  WC_CP_Component  triggered_by
		 *
		 * @hooked Component_Options_Model::refresh_options_state - 10
		 *
		 *
		 *
		 *--------------------------*
		 *   3. Composite           *
		 *--------------------------*
		 *
		 *
		 * Action 'initialize_composite':
		 *
		 * Action that handles app initialization by prioritizing the execution of the required functions.
		 *
		 * @hooked @see WC_CP_Composite::init
		 *
		 *
		 *
		 * Action 'composite_initialized':
		 *
		 * Action that handles app post-initialization by prioritizing the execution of the required functions.
		 *
		 * @hooked @see WC_CP_Composite::init
		 *
		 *
		 *
		 * Action 'composite_totals_changed':
		 *
		 * Triggered when the composite price/totals change.
		 *
		 * @hooked Composite_Price_View::render               - 100
		 * @hooked Composite_Price_View::update_addons_totals - 100
		 *
		 *
		 *
		 * Action 'composite_validation_status_changed':
		 *
		 * Triggered when the validation status of the Composite changes.
		 *
		 * @hooked Composite_Add_To_Cart_Button_View::render - 100
		 *
		 *
		 *
		 * Action 'composite_validation_message_changed':
		 *
		 * Triggered when the validation notice of the Composite changes.
		 *
		 * @hooked Composite_Validation_View::render - 100
		 *
		 *
		 *
		 * Action 'composite_availability_status_changed':
		 *
		 * Triggered when the availability status of the Composite changes.
		 *
		 * @hooked Composite_Add_To_Cart_Button_View::render - 100
		 *
		 *
		 *
		 * Action 'composite_availability_message_changed':
		 *
		 * Triggered when the availability html message of the Composite changes.
		 *
		 * @hooked Composite_Availability_View::render - 100
		 *
		 */

		var dispatcher = this,
			actions    = {},
			functions  = {

				add_action: function( hook, callback, priority, context ) {

					var hookObject = {
						callback : callback,
						priority : priority,
						context : context
					};

					var hooks = actions[ hook ];
					if ( hooks ) {
						hooks.push( hookObject );
						hooks = this.sort_actions( hooks );
					} else {
						hooks = [ hookObject ];
					}

					actions[ hook ] = hooks;
				},

				remove_action: function( hook, callback, context ) {

					var handlers, handler, i;

					if ( ! actions[ hook ] ) {
						return;
					}
					if ( ! callback ) {
						actions[ hook ] = [];
					} else {
						handlers = actions[ hook ];
						if ( ! context ) {
							for ( i = handlers.length; i--; ) {
								if ( handlers[ i ].callback === callback ) {
									handlers.splice( i, 1 );
								}
							}
						} else {
							for ( i = handlers.length; i--; ) {
								handler = handlers[ i ];
								if ( handler.callback === callback && handler.context === context ) {
									handlers.splice( i, 1 );
								}
							}
						}
					}
				},

				sort_actions: function( hooks ) {

					var tmpHook, j, prevHook;
					for ( var i = 1, len = hooks.length; i < len; i++ ) {
						tmpHook = hooks[ i ];
						j = i;
						while( ( prevHook = hooks[ j - 1 ] ) &&  prevHook.priority > tmpHook.priority ) {
							hooks[ j ] = hooks[ j - 1 ];
							--j;
						}
						hooks[ j ] = tmpHook;
					}

					return hooks;
				},

				do_action: function( hook, args ) {

					var handlers = actions[ hook ], i, len;

					if ( ! handlers ) {
						return false;
					}

					len = handlers.length;

					for ( i = 0; i < len; i++ ) {
						handlers[ i ].callback.apply( handlers[ i ].context, args );
					}

					return true;
				}

			};

		this.init = function() {

			composite.console_log( 'debug:events', '\nInitializing Actions Dispatcher...' );

			/*
			 *--------------------------*
			 *   1. Components          *
			 *--------------------------*
			 */

			/*
			 * Dispatch actions for key events triggered by step objects and their models.
			 */
			$.each( composite.get_steps(), function( index, step ) {

				if ( step.is_component() ) {

					/*
					 * Dispatch action when a selection change event is triggered.
					 */
					step.component_selection_model.on( 'change:selected_product change:selected_variation', function() {

						if ( ! step.initializing_scripts ) {
							// Run 'component_selection_changed' action - @see WC_CP_Actions_Dispatcher class description.
							dispatcher.do_action( 'component_selection_changed', [ step ] );
						}
					} );

					/*
					 * Dispatch action when a quantity change event is triggered.
					 */
					step.component_selection_model.on( 'change:selected_quantity', function() {
						// Run 'component_quantity_changed' action - @see WC_CP_Actions_Dispatcher class description.
						dispatcher.do_action( 'component_quantity_changed', [ step ] );
					} );

					/*
					 * Dispatch action when a selected addons change event is triggered.
					 */
					step.component_selection_model.on( 'change:selected_addons', function() {

						if ( ! step.initializing_scripts ) {
							// Run 'component_addons_changed' action - @see WC_CP_Actions_Dispatcher class description.
							dispatcher.do_action( 'component_addons_changed', [ step ] );
						}
					} );

					/*
					 * Dispatch action when a nyp change event is triggered.
					 */
					step.component_selection_model.on( 'change:selected_nyp', function() {

						if ( ! step.initializing_scripts ) {
							// Run 'component_nyp_changed' action - @see WC_CP_Actions_Dispatcher class description.
							dispatcher.do_action( 'component_nyp_changed', [ step ] );
						}
					} );

					/*
					 * Dispatch action when the active options change in response to scenario changes.
					 */
					step.component_options_model.on( 'change:active_options', function() {
						// Run 'active_options_changed' action - @see WC_CP_Actions_Dispatcher class description.
						dispatcher.do_action( 'active_options_changed', [ step ] );
					} );

					/*
					 * Dispatch action when the available options change.
					 */
					step.component_options_model.on( 'change:available_options', function() {
						// Run 'available_options_changed' action - @see WC_CP_Actions_Dispatcher class description.
						dispatcher.do_action( 'available_options_changed', [ step ] );
					} );

					/*
					 * Dispatch action when the component totals change.
					 */
					composite.data_model.on( 'change:component_' + step.step_id + '_totals', function() {
						// Run 'component_totals_changed' action - @see WC_CP_Actions_Dispatcher class description.
						dispatcher.do_action( 'component_totals_changed', [ step ] );
					} );

					/**
					 * Event triggered by custom product types to indicate that the state of the component selection has changed.
					 */
					step.$el.on ( 'woocommerce-composited-product-update', function() {

						if ( ! step.initializing_scripts ) {
							// Run 'component_selection_changed' action - @see WC_CP_Actions_Dispatcher class description.
							dispatcher.do_action( 'component_selection_changed', [ step ] );
						}
					} );
				}

				/*
				 * Dispatch action when the validation state of a step changes.
				 */
				step.step_validation_model.on( 'change:composite_messages', function() {

					if ( ! step.initializing_scripts ) {
						// Run 'component_validation_message_changed' action - @see WC_CP_Actions_Dispatcher class description.
						dispatcher.do_action( 'component_validation_message_changed', [ step ] );
					}
				} );

			} );

			/*
			 * Dispatch step action associated with the 'show_step' action.
			 */
			dispatcher.add_action( 'show_step', function( step ) {
				// Run 'show_step_{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
				dispatcher.do_action( 'show_step_' + step.step_id );
			}, 0, this );

			/*
			 * Dispatch step action associated with the 'active_step_changed' action.
			 */
			dispatcher.add_action( 'active_step_changed', function( step ) {
				// Run 'active_step_changed_{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
				dispatcher.do_action( 'active_step_changed_' + step.step_id );
			}, 0, this );

			/*
			 * Dispatch step action associated with the 'active_step_transition_start' action.
			 */
			dispatcher.add_action( 'active_step_transition_start', function( step ) {
				// Run 'active_step_transition_start_{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
				dispatcher.do_action( 'active_step_transition_start_' + step.step_id );
			}, 0, this );

			/*
			 * Dispatch step action associated with the 'active_step_transition_end' action.
			 */
			dispatcher.add_action( 'active_step_transition_end', function( step ) {
				// Run 'active_step_transition_end_{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
				dispatcher.do_action( 'active_step_transition_end_' + step.step_id );
			}, 0, this );

			/*
			 * Dispatch step action associated with the 'active_options_changed' action.
			 */
			dispatcher.add_action( 'active_options_changed', function( step ) {
				// Run 'active_options_changed_{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
				dispatcher.do_action( 'active_options_changed_' + step.step_id );
			}, 0, this );

			/*
			 * Dispatch step action associated with the 'available_options_changed' action.
			 */
			dispatcher.add_action( 'available_options_changed', function( step ) {
				// Run 'available_options_changed_{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
				dispatcher.do_action( 'available_options_changed_' + step.step_id );
			}, 0, this );

			/*
			 * Dispatch step action associated with the 'component_options_loaded' action.
			 */
			dispatcher.add_action( 'component_options_loaded', function( step ) {
				// Run 'component_options_loaded_{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
				dispatcher.do_action( 'component_options_loaded_' + step.step_id );
				// Trigger event for back-compat.
				step.$el.trigger( 'wc-composite-component-options-loaded', [ step, composite ] );
			}, 0, this );

			/*
			 * Dispatch step action associated with the 'component_scripts_initialized' action.
			 */
			dispatcher.add_action( 'component_scripts_initialized', function( step ) {
				// Run 'component_scripts_initialized_{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
				dispatcher.do_action( 'component_scripts_initialized_' + step.step_id );
				// Trigger event for back-compat.
				step.$el.trigger( 'wc-composite-component-loaded', [ step, composite ] );
			}, 0, this );

			/*
			 *--------------------------*
			 *   2. Scenarios           *
			 *--------------------------*
			 */

			/*
			 * Dispatch action when the active scenarios change.
			 */
			composite.scenarios.on( 'active_scenarios_changed', function( triggered_by ) {
				// Run 'active_scenarios_changed' action - @see WC_CP_Actions_Dispatcher class description.
				dispatcher.do_action( 'active_scenarios_changed', [ triggered_by ] );
			} );

			/*
			 * Dispatch action when the active scenarios are updated.
			 */
			composite.scenarios.on( 'active_scenarios_updated', function( triggered_by ) {
				// Run 'active_scenarios_updated' action - @see WC_CP_Actions_Dispatcher class description.
				dispatcher.do_action( 'active_scenarios_updated', [ triggered_by ] );
			} );

			/*
			 *--------------------------*
			 *   3. Composite           *
			 *--------------------------*
			 */

			/*
			 * Dispatch action when the composite totals change.
			 */
			composite.data_model.on( 'change:totals', function() {
				// Run 'composite_totals_changed' action - @see WC_CP_Actions_Dispatcher class description.
				dispatcher.do_action( 'composite_totals_changed' );
			} );

			/*
			 * Dispatch action when the composite validation status changes.
			 */
			composite.data_model.on( 'change:passes_validation', function() {
				// Run 'composite_validation_status_changed' action - @see WC_CP_Actions_Dispatcher class description.
				dispatcher.do_action( 'composite_validation_status_changed' );
			} );

			/*
			 * Dispatch action when the composite validation message changes.
			 */
			composite.data_model.on( 'change:validation_messages', function() {
				// Run 'composite_validation_message_changed' action - @see WC_CP_Actions_Dispatcher class description.
				dispatcher.do_action( 'composite_validation_message_changed' );
			} );

			/*
			 * Dispatch action when the composite availability status changes.
			 */
			composite.data_model.on( 'change:is_in_stock', function() {
				// Run 'composite_availability_status_changed' action - @see WC_CP_Actions_Dispatcher class description.
				dispatcher.do_action( 'composite_availability_status_changed' );
			} );

			/*
			 * Dispatch action when the composite availability message changes.
			 */
			composite.data_model.on( 'change:stock_statuses', function() {
				// Run 'composite_availability_message_changed' action - @see WC_CP_Actions_Dispatcher class description.
				dispatcher.do_action( 'composite_availability_message_changed' );
			} );

		};

		/**
		 * Adds an action handler to the dispatcher.
		 */
		this.add_action = function( action, callback, priority, context ) {

			if ( typeof action === 'string' && typeof callback === 'function' ) {
				priority = parseInt( ( priority || 10 ), 10 );
				functions.add_action( action, callback, priority, context );
			}

			return dispatcher;
		};

		/**
		 * Performs an action if it exists.
		 */
		this.do_action = function( action, args ) {

			if ( typeof action === 'string' ) {
				functions.do_action( action, args );
			}

			return dispatcher;
		};

		/**
		 * Removes the specified action.
		 */
		this.remove_action = function( action, callback ) {

			if ( typeof action === 'string' ) {
				functions.remove_action( action, callback );
			}

			return dispatcher;
		};

	};



	/**
	 * Filters manager that handles filtering of various function outputs.
	 *
	 * A complete reference of all application filters & callbacks is provided in the "Filters Reference" below.
	 */
	wc_cp_classes.WC_CP_Filters_Manager = function() {

		/*
		 *--------------------------*
		 *                          *
		 *   Filters Reference      *
		 *                          *
		 *--------------------------*
		 *
		 *--------------------------*
		 *   1. Composite           *
		 *--------------------------*
		 *
		 *
		 * Filter 'composite_validation_messages':
		 *
		 * Filters the individual Composite validation notice messages before updating model state.
		 *
		 * @param  array  messages   Validation messages.
		 * @return array
		 *
		 * @hooked void
		 *
		 *
		 *
		 * Filter 'composite_totals':
		 *
		 * Filters the Composite totals before updating model state.
		 *
		 * @param  object  totals   Composite prices.
		 * @return object
		 *
		 * @hooked void
		 *
		 *
		 *
		 *--------------------------*
		 *   2. Components          *
		 *--------------------------*
		 *
		 *
		 * Filter 'component_totals':
		 *
		 * Filters the totals of a Component before updating the data model state.
		 *
		 * @param  object            totals      Component prices.
		 * @param  WC_CP_Component   component   Component object.
		 * @return object
		 *
		 * @hooked void
		 *
		 *
		 *
		 * Filter 'step_validation_messages':
		 *
		 * Filters the validation notices associated with a step.
		 *
		 * @param  array        messages   Validation messages.
		 * @param  string       scope      Scope for validation messages ('composite', 'component').
		 * @param  WC_CP_Step   step       Step object.
		 * @return array
		 *
		 * @hooked void
		 *
		 *
		 *
		 * Filter 'step_is_valid':
		 *
		 * Filters the validation status of a step before updating the Step_Validation_Model state.
		 *
		 * @param  boolean      is_valid   Validation state.
		 * @param  WC_CP_Step   step       Step object.
		 * @return boolean
		 *
		 * @hooked void
		 *
		 *
		 *
		 * Filter 'step_is_locked':
		 *
		 * @param  boolean      is_locked   Access state.
		 * @param  WC_CP_Step   step        Step object.
		 * @return boolean
		 *
		 * @hooked void
		 *
		 *
		 *
		 * Filter 'component_is_optional':
		 *
		 * Filters the optional status of a Component.
		 *
		 * @param  boolean          is_optional   True if optional.
		 * @param  WC_CP_Component  step          Component object.
		 * @return boolean
		 *
		 * @hooked void
		 *
		 *
		 *
		 * Filter 'component_selection_title':
		 *
		 * Filters the raw product title of the current Component selection.
		 *
		 * @param  string            title        The title.
		 * @param  WC_CP_Component   component    Component object.
		 * @return string
		 *
		 * @hooked void
		 *
		 *
		 *
		 * Filter 'component_selection_formatted_title':
		 *
		 * Filters the formatted title of the current Component selection.
		 *
		 * @param  string            title        The returned title.
		 * @param  string            raw_title    The raw, unformatted title.
		 * @param  string            qty          The quantity of the selected product.
		 * @param  string            meta         The formatted meta.
		 * @param  WC_CP_Component   component    Component object.
		 * @return string
		 *
		 * @hooked void
		 *
		 *
		 *
		 * Filter 'component_selection_meta':
		 *
		 * Filters the meta array associated with the current Component selection.
		 *
		 * @param  array             meta         The returned meta array.
		 * @param  WC_CP_Component   component    WC_CP_Component  Component object.
		 * @return array
		 *
		 * @hooked void
		 *
		 *
		 *
		 * Filter 'component_selection_formatted_meta':
		 *
		 * Filters the formatted meta associated with the current Component selection.
		 *
		 * @param  string           formatted_meta   The returned formatted meta.
		 * @param  array            meta             The meta array.
		 * @param  WC_CP_Component  component        Component object.
		 * @return string
		 *
		 * @hooked void
		 *
		 *
		 *
		 * Filter 'component_configuration':
		 *
		 * Filters the configuration data object associated with a Component.
		 *
		 * @param  object           config           The returned component configuration data object.
		 * @param  WC_CP_Component  component        Component object.
		 * @return object
		 *
		 * @hooked void
		 *
		 */

		var manager   = this,
			filters   = {},
			functions = {

				add_filter: function( hook, callback, priority, context ) {

					var hookObject = {
						callback : callback,
						priority : priority,
						context : context
					};

					var hooks = filters[ hook ];
					if ( hooks ) {
						hooks.push( hookObject );
						hooks = this.sort_filters( hooks );
					} else {
						hooks = [ hookObject ];
					}

					filters[ hook ] = hooks;
				},

				remove_filter: function( hook, callback, context ) {

					var handlers, handler, i;

					if ( ! filters[ hook ] ) {
						return;
					}
					if ( ! callback ) {
						filters[ hook ] = [];
					} else {
						handlers = filters[ hook ];
						if ( ! context ) {
							for ( i = handlers.length; i--; ) {
								if ( handlers[ i ].callback === callback ) {
									handlers.splice( i, 1 );
								}
							}
						} else {
							for ( i = handlers.length; i--; ) {
								handler = handlers[ i ];
								if ( handler.callback === callback && handler.context === context) {
									handlers.splice( i, 1 );
								}
							}
						}
					}
				},

				sort_filters: function( hooks ) {

					var tmpHook, j, prevHook;
					for ( var i = 1, len = hooks.length; i < len; i++ ) {
						tmpHook = hooks[ i ];
						j = i;
						while( ( prevHook = hooks[ j - 1 ] ) &&  prevHook.priority > tmpHook.priority ) {
							hooks[ j ] = hooks[ j - 1 ];
							--j;
						}
						hooks[ j ] = tmpHook;
					}

					return hooks;
				},

				apply_filters: function( hook, args ) {

					var handlers = filters[ hook ], i, len;

					if ( ! handlers ) {
						return args[ 0 ];
					}

					len = handlers.length;

					for ( i = 0; i < len; i++ ) {
						args[ 0 ] = handlers[ i ].callback.apply( handlers[ i ].context, args );
					}

					return args[ 0 ];
				}

			};

		/**
		 * Adds a filter.
		 */
		this.add_filter = function( filter, callback, priority, context ) {

			if ( typeof filter === 'string' && typeof callback === 'function' ) {
				priority = parseInt( ( priority || 10 ), 10 );
				functions.add_filter( filter, callback, priority, context );
			}

			return manager;
		};

		/**
		 * Applies all filter callbacks.
		 */
		this.apply_filters = function( filter, args ) {

			if ( typeof filter === 'string' ) {
				return functions.apply_filters( filter, args );
			}
		};

		/**
		 * Removes the specified filter callback.
		 */
		this.remove_filter = function( filter, callback ) {

			if ( typeof filter === 'string' ) {
				functions.remove_filter( filter, callback );
			}

			return manager;
		};

	};



	/**
	 * Updates the active scenarios when:
	 *
	 *  - Refreshing/appending new component options: Adds an 'available_options_changed' action handler.
	 *  - Selecting a new product/variation: Adds a 'component_selection_changed' action handler ('component_selection_changed' action dispatched when a 'change:selected_product' and 'change:selected_variation' event is triggered by a Component_Selection_Model).
	 *
	 * Triggers the 'active_scenarios_updated' and 'active_scenarios_changed' events which are picked up by Component_Options_Model models to update their options state (handlers added to the corresponding dispatcher actions).
	 */
	wc_cp_classes.WC_CP_Scenarios_Manager = function( composite ) {

		var manager      = this,
			manager_data = {
			updating_scenarios:             false,
			scenario_data:                  composite.$composite_data.data( 'scenario_data' ),
			active_scenarios:               [],
			invalid_product_step_indexes:   [],
			invalid_variation_step_indexes: [],
		};

		_.extend( manager, Backbone.Events );

		/**
		 * Initialize after components have been created.
		 */
		this.init = function() {

			/**
			 * Update the active scenarios when refreshing/appending new component options.
			 */
			composite.actions.add_action( 'available_options_changed', this.available_options_changed_handler, -10, this );

			/**
			 * Update the active scenarios when selecting a new product/variation.
			 */
			composite.actions.add_action( 'component_selection_changed', this.selection_changed_handler, 10, this );

			// Initialize scenarios.
			composite.console_log( 'debug:events', '\nInitializing Scenarios Manager...' );
			composite.debug_tab_count = composite.debug_tab_count + 2;

			manager.update_active_scenarios( _.first( composite.steps ), false, false );

			composite.debug_tab_count = composite.debug_tab_count - 2;
			composite.console_log( 'debug:events', '\nScenarios Manager initialized.\n' );
		};

		/**
		 * True if the component was found to contain an invalid product selection.
		 */
		this.has_invalid_product = function( step_index ) {

			return _.contains( manager_data.invalid_product_step_indexes, step_index );
		};

		/**
		 * True if the component was found to contain an invalid variation selection.
		 */
		this.has_invalid_variation = function( step_index ) {

			return _.contains( manager_data.invalid_variation_step_indexes, step_index );
		};

		/**
		 * Return active scenarios.
		 */
		this.get_active_scenarios = function() {

			return manager_data.active_scenarios;
		};

		/**
		 * Return all step indexes where an invalid selection was found during the last update.
		 */
		this.get_invalid_step_indexes = function() {

			return _.sortBy( _.uniq( _.union( manager_data.invalid_product_step_indexes, manager_data.invalid_variation_step_indexes ) ), function( num ) { return num; } );
		};

		/**
		 * Return current scenario data.
		 */
		this.get_scenario_data = function() {

			return manager_data.scenario_data;
		};

		/**
		 * Replace stored scenario data for a given component, for instance when refreshing the component options view.
		 */
		this.set_component_scenario_data = function( component_id, component_scenario_data ) {

			manager_data.scenario_data.scenario_data[ component_id ] = component_scenario_data;
		};

		/**
		 * Append scenario data for a given component, in order to include data for more products.
		 */
		this.set_component_scenario_data = function( component_id, component_scenario_data ) {

			manager_data.scenario_data.scenario_data[ component_id ] = component_scenario_data;
		};

		this.selection_changed_handler = function( triggered_by ) {

			this.update_active_scenarios( triggered_by );
		};

		this.available_options_changed_handler = function( triggered_by ) {

			this.update_active_scenarios( triggered_by );
		};

		/**
		 * Updates active scenarios and triggers an event if changed.
		 */
		this.update_active_scenarios = function( triggered_by ) {

			composite.console_log( 'debug:scenarios', '\nScenarios update triggered by event originating from "' + triggered_by.get_title() + '"...' );

			// Don't do anything if event originates from script initialization.
			if ( triggered_by.initializing_scripts ) {
				composite.console_log( 'debug:scenarios', 'Breaking out - event triggered during script init...\n\n' );
				return false;
			}

			composite.console_log( 'debug:scenarios', '\nClearing incompatible products/variations data...' );

			manager_data.invalid_product_step_indexes   = [];
			manager_data.invalid_variation_step_indexes = [];

			// Backup current state to compare with new one.
			var active_scenarios_pre                    = manager_data.active_scenarios;

			// Get new scenarios based on selections.
			manager_data.updating_scenarios             = true;
			var updated_scenarios                       = this.calculate_active_scenarios( triggered_by, false, false );
			manager_data.updating_scenarios             = false;

			// Only trigger event if the active scenarios changed :)
			if ( active_scenarios_pre.length !== updated_scenarios.length || active_scenarios_pre.length !== _.intersection( active_scenarios_pre, updated_scenarios ).length ) {
				composite.console_log( 'debug:scenarios', '\nActive scenarios changed: - [' + active_scenarios_pre + '] => [' + updated_scenarios + ']' );
				manager_data.active_scenarios = updated_scenarios;

				manager.trigger( 'active_scenarios_changed', triggered_by );

			} else {
				composite.console_log( 'debug:scenarios', '\nActive scenarios unchanged.' );
			}

			manager.trigger( 'active_scenarios_updated', triggered_by );
		};

		/**
		 * Extract active scenarios from current selections.
		 * Scenarios can be calculated up to, or excluding the step passed as reference.
		 */
		this.calculate_active_scenarios = function( ref_step, up_to_ref, excl_ref ) {

			var ref_step_index         = ref_step.step_index,
				scenarios              = manager.get_scenario_data().scenarios,
				compat_group_scenarios = manager.filter_scenarios_by_type( scenarios, 'compat_group' );

			if ( ref_step.is_review() ) {
				ref_step_index = 1000;
			}

			if ( compat_group_scenarios.length === 0 ) {
				scenarios.push( '0' );
			}

			var active_scenarios            = scenarios;
			var scenario_shaping_components = [];

			if ( ! manager_data.updating_scenarios ) {
				composite.console_log( 'debug:scenarios', '\n' + 'Scenarios requested by "' + ref_step.get_title() + '"...' );
			}

			composite.console_log( 'debug:scenarios', '\n' + 'Calculating active scenarios...' );

			$.each( composite.get_components(), function( index, component ) {

				// Omit reference component when excluded.
				if ( excl_ref && parseInt( component.step_index ) === parseInt( ref_step_index ) ) {
					return true;
				}

				// Exit when reaching beyond reference component.
				if ( up_to_ref && component.step_index > ref_step_index ) {
					return false;
				}

				var product_id   = component.get_selected_product( false ),
					product_type = component.get_selected_product_type();

				if ( product_id !== null && product_id >= 0 ) {

					var scenario_data      = manager.get_scenario_data().scenario_data,
						item_scenario_data = scenario_data[ component.component_id ];

					// Treat '' optional component selections as 'None' if the component is optional.
					if ( product_id === '' ) {
						if ( 0 in item_scenario_data ) {
							product_id = '0';
						} else {
							return true;
						}
					}

					var product_in_scenarios = item_scenario_data[ product_id ];

					composite.console_log( 'debug:scenarios', 'Selection #' + product_id + ' of "' + component.get_title() + '" in scenarios: [' + product_in_scenarios + ']' );

					var product_intersection    = _.intersection( active_scenarios, product_in_scenarios ),
						product_is_compatible   = product_intersection.length > 0,
						variation_id            = '',
						variation_is_compatible = true;

					if ( product_is_compatible ) {

						if ( product_type === 'variable' ) {

							variation_id = component.get_selected_variation( false );

							if ( variation_id > 0 ) {

								var variation_in_scenarios = item_scenario_data[ variation_id ];

								composite.console_log( 'debug:scenarios', 'Variation selection #' + variation_id + ' of "' + component.get_title() + '" in scenarios: [' + variation_in_scenarios +']' );

								product_intersection    = _.intersection( product_intersection, variation_in_scenarios );
								variation_is_compatible = product_intersection.length > 0;
							}
						}
					}

					var is_compatible = product_is_compatible && variation_is_compatible;

					if ( is_compatible ) {

						scenario_shaping_components.push( component.component_id );
						active_scenarios = product_intersection;

						composite.console_log( 'debug:scenarios', 'Active scenarios: [' + active_scenarios + ']' );

					} else if ( ! product_is_compatible ) {

						var invalid_product_msg = 'Incompatible product selection found...';
						if ( manager_data.updating_scenarios && product_id > 0 ) {
							invalid_product_msg += ' saved.';
							manager_data.invalid_product_step_indexes.push( component.step_index );
						}
						composite.console_log( 'debug:scenarios', invalid_product_msg );

					} else if ( ! variation_is_compatible ) {

						var invalid_variation_msg = 'Incompatible variation selection found...';
						if ( manager_data.updating_scenarios && variation_id > 0 ) {
							invalid_variation_msg += ' saved.';
							manager_data.invalid_variation_step_indexes.push( component.step_index );
						}
						composite.console_log( 'debug:scenarios', invalid_variation_msg );
					}
				}

			} );

			composite.console_log( 'debug:scenarios', 'Removing active scenarios where all scenario shaping components (' + scenario_shaping_components + ') are masked...' );

			var result = manager.get_binding_scenarios( active_scenarios, scenario_shaping_components );

			composite.console_log( 'debug:scenarios', 'Calculated active scenarios: [' + result + ']\n' );

			return result;
		};

		/**
		 * Filters out unbinding scenarios.
		 */
		this.get_binding_scenarios = function( scenarios, scenario_shaping_components ) {

			var masked = this.get_scenario_data().scenario_settings.masked_components,
				clean  = [];

			if ( scenario_shaping_components.length > 0 ) {

				if ( scenarios.length > 0 ) {
					for ( var i = 0; i < scenarios.length; i++ ) {

						var scenario_id = scenarios[ i ];

						// If all scenario shaping components are masked, filter out the scenario.
						var all_components_masked_in_scenario = true;

						for ( var k = 0; k < scenario_shaping_components.length; k++ ) {

							var component_id = scenario_shaping_components[ k ];

							if ( $.inArray( component_id.toString(), masked[ scenario_id ] ) == -1 ) {
								all_components_masked_in_scenario = false;
								break;
							}
						}

						if ( ! all_components_masked_in_scenario ) {
							clean.push( scenario_id );
						}
					}
				}

			} else {
				clean = scenarios;
			}

			if ( clean.length === 0 && scenarios.length > 0 ) {
				clean = scenarios;
			}

			return clean;
		};

		/**
		 * Gets active scenarios by type.
		 */
		this.get_active_scenarios_by_type = function( type ) {

			return this.filter_scenarios_by_type( manager_data.active_scenarios, type );
		};

		/**
		 * Filters active scenarios by type.
		 */
		this.get_scenarios_by_type = function( type ) {

			return this.filter_scenarios_by_type( this.get_scenario_data().scenarios, type );
		};

		/**
		 * Filters scenarios by type.
		 */
		this.filter_scenarios_by_type = function( scenarios, type ) {

			var filtered    = [],
				scenario_id = '';

			if ( scenarios.length > 0 ) {
				for ( var i = 0; i < scenarios.length; i++ ) {

					scenario_id = scenarios[ i ];

					if ( $.inArray( type, this.get_scenario_data().scenario_settings.scenario_actions[ scenario_id ] ) > -1 ) {
						filtered.push( scenario_id );
					}
				}
			}

			return filtered;
		};

		/**
		 * Filters out masked scenarios in 'update_selections'.
		 */
		this.get_binding_scenarios_for = function( scenarios, component_id ) {

			var masked      = this.get_scenario_data().scenario_settings.masked_components,
				clean       = [],
				scenario_id = '';

			if ( scenarios.length > 0 ) {
				for ( var i = 0; i < scenarios.length; i++ ) {

					scenario_id = scenarios[ i ];

					if ( $.inArray( component_id.toString(), masked[ scenario_id ] ) == -1 ) {
						clean.push( scenario_id );
					}

				}
			}

			return clean;
		};

	};



	/**
	 * Factory class for creating new step objects.
	 */
	wc_cp_classes.WC_CP_Step_Factory = function() {

		/**
	     * Step class.
	     */
		function WC_CP_Step( composite, $step, index ) {

			var self                 = this;

			this.step_id             = $step.data( 'item_id' );
			this.step_index          = index;
			this.step_title          = $step.data( 'nav_title' );
			this.step_slug           = composite.$composite_data.data( 'composite_settings' ).slugs[ this.step_id ];

			this._component_messages = [];
			this._composite_messages = [];

			this._is_component       = $step.hasClass( 'component' );
			this._is_review          = $step.hasClass( 'cart' );

			this._is_current         = $step.hasClass( 'active' );
			this._is_previous        = $step.hasClass( 'prev' );
			this._is_next            = $step.hasClass( 'next' );

			this._toggled            = $step.hasClass( 'toggled' );

			this._autotransition     = $step.hasClass( 'autotransition' );

			this.$el                 = $step;
			this.$inner_el           = $step.find( '.component_inner' );

			this.$step_title         = $step.find( '.component_title' );

			/**
			* @deprecated
			*/
			this.$self               = $step;

			/**
			 * Step actions - @see WC_CP_Actions_Dispatcher class.
			 */
			this.add_actions = function() {

				/*
				 * Viewport auto-scrolling.
				 */
				if ( composite.settings.layout === 'single' ) {
					// Viewport auto-scrolling on the 'show_step' action.
					composite.actions.add_action( 'show_step_' + self.step_id, this.autoscroll_single, 10, this );
				} else if ( composite.settings.layout === 'paged' ) {
					// Viewport auto-scrolling on the 'active_step_changed' action.
					composite.actions.add_action( 'active_step_changed', this.autoscroll_paged, 120, this );
					// Viewport auto-scrolling on the 'active_step_transition_end' action.
					composite.actions.add_action( 'active_step_transition_end_' + self.step_id, this.autoscroll_paged_relocated, 10, this );
				} else if ( composite.settings.layout === 'progressive' ) {
					// Viewport auto-scrolling on the 'active_step_transition_end' hook.
					composite.actions.add_action( 'active_step_transition_end_' + self.step_id, this.autoscroll_progressive, 10, this );
				}
			};

			/**
			 * Single layout auto-scrolling behaviour on the 'show_step' hook - single layout.
			 */
			this.autoscroll_single = function() {

				var do_scroll = ( composite.is_initialized === false ) ? false : true;

				// Scroll to the desired section.
				if ( do_scroll ) {
					wc_cp_scroll_viewport( self.$el, { partial: false, duration: 250, queue: false } );
				}
			};

			/**
			 * Paged layout auto-scrolling behaviour on the 'show_step' hook.
			 */
			this.autoscroll_paged = function( step ) {

				if ( self.step_id === step.step_id ) {

					var do_scroll    = ( composite.is_initialized === false ) ? false : true,
						is_component = self.is_component(),
						component    = is_component ? self : false;

					if ( ! is_component || ! component.component_selection_view.is_relocated() ) {
						if ( do_scroll ) {
							wc_cp_scroll_viewport( composite.$composite_form.find( '.scroll_show_component' ), { timeout: 20, partial: true, duration: 250, queue: false } );
						}
					}
				}
			};

			/**
			 * Paged layout auto-scrolling behaviour on the 'active_step_transition_end' hook - relocated content.
			 */
			this.autoscroll_paged_relocated = function() {

				var do_scroll    = ( composite.is_initialized === false ) ? false : true,
					is_component = self.is_component(),
					component    = is_component ? self : false;

				if ( is_component && component.component_selection_view.is_relocated() ) {
					if ( do_scroll ) {
						wc_cp_scroll_viewport( component.$component_content, { timeout: 0, partial: false, duration: 250, queue: false, scroll_method: 'middle' } );
					}
				}
			};

			/**
			 * Prog layout auto-scrolling behaviour on the 'active_step_transition_end' hook.
			 */
			this.autoscroll_progressive = function() {

				var do_scroll = ( composite.is_initialized === false ) ? false : true;

				// Scroll.
				if ( do_scroll && self.$el.hasClass( 'autoscrolled' ) ) {
					if ( ! self.$step_title.wc_cp_is_in_viewport( false ) ) {
						wc_cp_scroll_viewport( self.$el, { timeout: 0, partial: false, duration: 250, queue: false } );
					}
				}
			};

			/**
			 * True if the step is configured to transition automatically to the next when a valid selection is made.
			 */
			this.autotransition = function() {

				return this._autotransition;
			};

			/**
			 * Reads the navigation permission of this step.
			 */
			this.is_animating = function() {

				return this.$el.hasClass( 'animating' );
			};

			/**
			 * True if the step UI is toggled.
			 */
			this.has_toggle = function() {

				return this._toggled;
			};

			/**
			 * Reads the navigation permission of this step.
			 */
			this.is_locked = function() {

				var is_locked = this.step_access_model.get( 'is_locked' );

				// Pass through 'step_is_locked' filter - @see WC_CP_Filters_Manager class.
				return composite.filters.apply_filters( 'step_is_locked', [ is_locked, this ] );
			};

			/**
			 * Forbids navigation to this step.
			 */
			this.lock = function() {

				this.step_access_model.set( { locked: true } );
			};

			/**
			 * Permits navigation to this step.
			 */
			this.unlock = function() {

				this.step_access_model.set( { locked: false } );
			};

			this.get_title = function() {

				return this.step_title;
			};

			this.get_element = function() {

				return this.$el;
			};

			this.is_review = function() {

				return this._is_review;
			};

			this.is_component = function() {

				return this._is_component;
			};

			this.get_component = function() {

				if ( this._is_component ) {
					return this;
				} else {
					return false;
				}
			};

			this.is_current = function() {

				return this._is_current;
			};

			this.is_next = function() {

				return this._is_next;
			};

			this.is_previous = function() {

				return this._is_previous;
			};

			/**
			 * Brings a new step into view - called when clicking on a navigation element.
			 */
			this.show_step = function() {

				if ( this.is_locked() || this.is_animating() ) {
					return false;
				}

				var	is_current = this.is_current();

				if ( composite.settings.layout === 'single' ) {
					// Toggle open if possible.
					this.toggle_step( 'open', true );
				}

				if ( ! is_current || ! composite.is_initialized ) {
					// Move active component.
					this.set_active();
				}

				// Run 'show_step' action - @see WC_CP_Actions_Dispatcher class description.
				composite.actions.do_action( 'show_step', [ this ] );
			};

			/**
			 * Sets a step as active by hiding the previous one and updating the steps' markup.
			 */
			this.set_active = function() {

				var step            = this,
					style           = composite.settings.layout,
					style_variation = composite.settings.layout_variation,
					curr_step_pre   = composite.get_current_step(),
					next_step_pre   = composite.get_next_step(),
					prev_step_pre   = composite.get_previous_step(),
					next_step       = composite.get_step_by( 'index', step.step_index + 1 ),
					prev_step       = composite.get_step_by( 'index', step.step_index - 1 );

				if ( style === 'paged' && style_variation === 'componentized' ) {
					next_step = prev_step = composite.get_step_by( 'id', 'review' );
				}

				curr_step_pre._is_current = false;
				step._is_current          = true;

				curr_step_pre.$el.removeClass( 'active' );
				this.$el.addClass( 'active' );

				if ( false !== next_step_pre ) {
					next_step_pre._is_next = false;
					next_step_pre.$el.removeClass( 'next' );
				}

				if ( false !== next_step ) {
					next_step._is_next = true;
					next_step.$el.addClass( 'next' );
				}

				if ( false !== prev_step_pre ) {
					prev_step_pre._is_previous = false;
					prev_step_pre.$el.removeClass( 'prev' );
				}

				if ( false !== prev_step ) {
					prev_step._is_previous = true;
					prev_step.$el.addClass( 'prev' );
				}

				if ( curr_step_pre.step_id !== step.step_id ) {

					if ( style === 'paged' ) {

						composite.has_transition_lock = true;

						var $cloned       = curr_step_pre.$el.clone(),
							outer_timeout = wc_composite_params.transition_type === 'slide' ? 10 : 250;

						// Clone selection state of visible select fields.
						curr_step_pre.$el.find( 'select' ).each( function() {
							if ( ! $( this ).is( ':visible' ) ) {
								return true;
							}
							var name = $( this ).attr( 'name' );
							$cloned.find( 'select[name="' + name + '"]' ).val( $( this ).val() );
						} );

						// Clone state of visible text fields.
						curr_step_pre.$el.find( 'textarea' ).each( function() {
							if ( ! $( this ).is( ':visible' ) ) {
								return true;
							}
							var name = $( this ).attr( 'name' );
							$cloned.find( 'textarea[name="' + name + '"]' ).val( $( this ).val() );
						} );

						// Change names of all input fields to avoid issues.
						$cloned.find( 'select, input, textarea' ).each( function() {
							$( this ).attr( 'name', $( this ).attr( 'name' ) + '_cloned' );
						} );

						$cloned.insertBefore( curr_step_pre.$el.hide() );

						setTimeout( function() {

							$cloned.addClass( 'faded' );

							if ( wc_composite_params.transition_type === 'fade' ) {
								step.$el.addClass( 'invisible faded' );
							}

							setTimeout( function() {

								var current_transition_delay = 150;

								if ( wc_composite_params.transition_type === 'slide' ) {
									var active_step_height = $cloned.height(),
										step_height        = step.$el.height(),
										transition_height  = active_step_height * 0.3 + step_height * 0.7;

									current_transition_delay = 150 + Math.min( 450, parseInt( transition_height / 5 ) );
								}

								// Run 'active_step_transition_start' action - @see WC_CP_Actions_Dispatcher class description.
								composite.actions.do_action( 'active_step_transition_start', [ step ] );

								composite.console_log( 'debug:events', 'Starting transition...' );

								// Hide with a sliding effect.
								$cloned.slideUp( { duration: current_transition_delay, always: function() {
									$cloned.remove();
								} } );

								// Show with a sliding effect.
								if ( curr_step_pre.step_index < step.step_index ) {
									step.$el.after( '<div style="display:none" class="active_placeholder"></div>' );
									step.$el.insertBefore( $cloned );
								}

								step.$el.slideDown( { duration: current_transition_delay, always: function() {

									if ( curr_step_pre.step_index < step.step_index ) {
										composite.$composite_form.find( '.active_placeholder' ).replaceWith( step.$el );
									}

									setTimeout( function() {
										// Run 'active_step_transition_end' action - @see WC_CP_Actions_Dispatcher class description.
										composite.actions.do_action( 'active_step_transition_end', [ step ] );
									}, outer_timeout );

									composite.console_log( 'debug:events', 'Transition ended.' );

									if ( wc_composite_params.transition_type === 'fade' ) {
										setTimeout( function() {
											composite.$steps.removeClass( 'faded invisible' );
										}, 10 );
									}

									composite.has_transition_lock = false;

								} } );

							}, outer_timeout );

						}, 10 );

					} else {

						if ( style === 'progressive' ) {

							// Update blocks.
							step.update_block_state();
						}

						composite.has_transition_lock = true;

						setTimeout( function() {
							// Run 'active_step_transition_start' action - @see WC_CP_Actions_Dispatcher class description.
							composite.actions.do_action( 'active_step_transition_start', [ step ] );
						}, 5 );

						setTimeout( function() {
							// Run 'active_step_transition_end' action - @see WC_CP_Actions_Dispatcher class description.
							composite.actions.do_action( 'active_step_transition_end', [ step ] );

							composite.has_transition_lock = false;

						}, 350 );

					}

				} else {
					step.$el.show();
				}

				// Run 'active_step_changed' action - @see WC_CP_Actions_Dispatcher class description.
				composite.actions.do_action( 'active_step_changed', [ this ] );
			};

			/**
			 * Updates the block state of a progressive step that's brought into view.
			 */
			this.update_block_state = function() {

				var style = composite.settings.layout;

				if ( style !== 'progressive' ) {
					return false;
				}

				$.each( composite.get_steps(), function( index, step ) {

					if ( step.step_index < self.step_index ) {

						step.block_step_inputs();

						// Do not close when the component is set to remain open when blocked.
						if ( ! step.$el.hasClass( 'block-open' ) ) {
							step.toggle_step( 'closed', true );
						}
					}
				} );

				this.unblock_step_inputs();
				this.unblock_step();

				this.block_next_steps();
			};

			/**
			 * Unblocks access to step in progressive mode.
			 */
			this.unblock_step = function() {

				this.toggle_step( 'open', true );

				this.$el.removeClass( 'blocked' );
			};

			/**
			 * Blocks access to all later steps in progressive mode.
			 */
			this.block_next_steps = function() {

				var min_block_index = this.step_index;

				$.each( composite.get_steps(), function( index, step ) {

					if ( index > min_block_index ) {

						if ( step.$el.hasClass( 'disabled' ) ) {
							step.unblock_step_inputs();
						}

						step.block_step();
					}
				} );
			};

			/**
			 * Blocks access to step in progressive mode.
			 */
			this.block_step = function() {

				this.$el.addClass( 'blocked' );

				this.toggle_step( 'closed', false );
			};

			/**
			 * Toggle step in progressive mode.
			 */
			this.toggle_step = function( state, active, complete ) {

				if ( this.has_toggle() ) {

					if ( state === 'open' ) {
						if ( this.$el.hasClass( 'closed' ) ) {
							wc_cp_toggle_element( this.$el, this.$inner_el, complete );
						}

					} else if ( state === 'closed' ) {
						if ( this.$el.hasClass( 'open' ) ) {
							wc_cp_toggle_element( this.$el, this.$inner_el, complete );
						}
					}

					if ( active ) {
						this.$step_title.removeClass( 'inactive' );
					} else {
						this.$step_title.addClass( 'inactive' );
					}
				}
			};

			/**
			 * Unblocks step inputs.
			 */
			this.unblock_step_inputs = function() {

				this.$el.removeClass( 'disabled' );

				var reset_options = this.$el.find( '.clear_component_options' );
				reset_options.html( wc_composite_params.i18n_clear_selection ).removeClass( 'reset_component_options' );
			};

			/**
			 * Blocks step inputs.
			 */
			this.block_step_inputs = function() {

				this.$el.addClass( 'disabled' );

				if ( ! self.has_toggle() || self.$el.hasClass( 'block-open' ) ) {
					var reset_options = this.$el.find( '.clear_component_options' );
					reset_options.html( wc_composite_params.i18n_reset_selection ).addClass( 'reset_component_options' );
				}
			};

			/**
			 * True if access to the step is blocked (progressive mode).
			 */
			this.is_blocked = function() {

				return this.$el.hasClass( 'blocked' );
			};

			/**
			 * True if access to the step inputs is blocked (progressive mode).
			 */
			this.has_blocked_inputs = function() {

				return this.$el.hasClass( 'disabled' );
			};

			/**
			 * Adds a validation message.
			 */
			this.add_validation_message = function( message, scope ) {

				scope = typeof( scope ) === 'undefined' ? 'component' : scope;

				if ( scope === 'composite' ) {
					this._composite_messages.push( message.toString() );
				} else {
					this._component_messages.push( message.toString() );
				}
			};

			/**
			 * Get all validation messages.
			 */
			this.get_validation_messages = function( scope ) {

				var messages;

				scope = typeof( scope ) === 'undefined' ? 'component' : scope;

				if ( scope === 'composite' ) {
					messages = this._composite_messages;
				} else {
					messages = this._component_messages;
				}

				// Pass through 'step_validation_messages' filter - @see WC_CP_Filters_Manager class.
				return composite.filters.apply_filters( 'step_validation_messages', [ messages, scope, this ] );
			};

			/**
			 * Validate component selection and stock status and add validation messages.
			 */
			this.validate = function() {

				if ( self.initializing_scripts ) {
					return false;
				}

				var step     = this,
					valid    = true,
					in_stock = true;

				this._component_messages = [];
				this._composite_messages = [];

				if ( this.is_component() ) {

					var product_id   = this.get_selected_product(),
						variation_id = this.get_selected_variation(),
						product_type = this.get_selected_product_type();

					valid = false;

					// Check if valid selection present.

					if ( product_id > 0 ) {

						if ( product_type === 'variable' && variation_id > 0 ) {
							valid = true;
						} else if ( product_type === 'simple' || product_type === 'none' ) {
							valid = true;
						} else if ( product_type !== 'variable' && product_type !== 'simple' && this.$component_data.data( 'component_set' ) === true ) {
							valid = true;
						}

					} else if ( product_id === '' && this.is_optional() ) {
						valid = true;
					}

					// Pass through 'step_is_valid' filter - @see WC_CP_Filters_Manager class.
					valid = composite.filters.apply_filters( 'step_is_valid', [ valid, this ] );

					if ( ! valid ) {
						if ( product_id > 0 ) {
							if ( product_type === 'invalid-product' ) {
								this.add_validation_message( wc_composite_params.i18n_item_unavailable_text, 'composite' );
							} else if ( product_type === 'variable' ) {
								if ( ! this.is_selected_variation_valid() ) {
									this.add_validation_message( wc_composite_params.i18n_selected_product_options_invalid, 'composite' );
								} else {
									this.add_validation_message( wc_composite_params.i18n_select_product_options );
									this.add_validation_message( wc_composite_params.i18n_select_product_options_for, 'composite' );
								}
							}
						} else {
							if ( ! this.is_selected_product_valid() ) {
								this.add_validation_message( wc_composite_params.i18n_selected_product_invalid, 'composite' );
							} else {
								this.add_validation_message( wc_composite_params.i18n_select_component_option );
								this.add_validation_message( wc_composite_params.i18n_select_component_option_for, 'composite' );
							}
						}
					}

					if ( ! this.is_in_stock() ) {
						in_stock = false;
					}

					// Run 'validate_step' action - @see WC_CP_Actions_Dispatcher class description.
					composite.actions.do_action( 'validate_step', [ step, valid ] );
				}

				this.step_validation_model.update( valid, in_stock );
			};

			/**
			 * Check if any validation messages exist.
			 */
			this.passes_validation = function() {

				return this.step_validation_model.get( 'passes_validation' );
			};

			this.add_actions();
		}

		/**
	     * Component class - inherits from WC_CP_Step.
	     */
		function WC_CP_Component( composite, $component, index ) {

			WC_CP_Step.call( this, composite, $component, index );

			var self                        = this;

			this.initializing_scripts       = false;

			this.component_index            = index;
			this.component_id               = $component.attr( 'data-item_id' );
			this.component_title            = $component.data( 'nav_title' );

			this.has_compat_results         = true;
			this.compat_results_count       = 0;

			this._hide_disabled_products    = $component.hasClass( 'hide-incompatible-products' );
			this._hide_disabled_variations  = $component.hasClass( 'hide-incompatible-variations' );
			this._append_results            = $component.hasClass( 'append-results' );
			this._is_static                 = $component.hasClass( 'static' );

			this.$component_summary         = $component.find( '.component_summary' );
			this.$component_summary_content = $component.find( '.component_summary > .content' );
			this.$component_selections      = $component.find( '.component_selections' );
			this.$component_content         = $component.find( '.component_content' );
			this.$component_options         = $component.find( '.component_options' );
			this.$component_filters         = $component.find( '.component_filters' );
			this.$component_ordering        = $component.find( '.component_ordering select' );
			this.$component_options_inner   = $component.find( '.component_options_inner' );
			this.$component_inner           = $component.find( '.component_inner' );
			this.$component_pagination      = $component.find( '.component_pagination' );
			this.$component_message         = $component.find( '.component_message' );
			this.$component_message_content = $component.find( '.component_message ul.msg' );

			this.$component_data            = this.$component_summary_content.find( '.component_data' );
			this.$component_quantity        = this.$component_summary_content.find( '.component_wrap input.qty' );
			this.$component_options_select  = this.$component_options.find( 'select.component_options_select' );

			/**
			 * Component actions - @see WC_CP_Actions_Dispatcher class.
			 */
			this.add_component_actions = function() {

				if ( composite.settings.layout !== 'single' ) {
					/*
					 * Autoload results.
					 */
					composite.actions.add_action( 'active_step_transition_start_' + self.step_id, this.autoload_compat_options, 10, this );
				}
			};

			/**
			 * Attempt to load more results if all loaded thumbnail options are hidden.
			 */
			this.autoload_compat_options = function() {

				if ( wc_composite_params.compat_options_autoload === 'yes' ) {
					if ( composite.settings.layout !== 'single' && self.has_options_style( 'thumbnails' ) ) {

						if ( self.append_results() && self.hide_disabled_products() ) {

							var load_more        = self.$component_pagination.find( '.component_options_load_more' ),
								results_per_page = self.find_pagination_param( 'results_per_page' );

							if ( false === self.has_compat_results || self.compat_results_count < results_per_page ) {
								load_more.trigger( 'click' );
							}
						}
					}
				}
			};

			/**
			 * True when component options are appended using a 'Load More' button, instead of paginated.
			 */
			this.append_results = function() {

				return this._append_results;
			};

			/**
			 * Gets the selected option id from the component selection model.
			 */
			this.get_selected_product = function( check_invalid ) {

				if ( typeof( check_invalid ) === 'undefined' ) {
					check_invalid = true;
				}

				if ( check_invalid && ! this.is_selected_product_valid() ) {
					return null;
				}

				return this.component_selection_model.get( 'selected_product' );
			};

			/**
			 * Gets the selected option id from the component selection model.
			 */
			this.get_selected_variation = function( check_invalid ) {

				if ( typeof( check_invalid ) === 'undefined' ) {
					check_invalid = true;
				}

				if ( check_invalid && ! this.is_selected_variation_valid() ) {
					return null;
				}

				return this.component_selection_model.get( 'selected_variation' );
			};

			/**
			 * Gets the selected product/variation quantity from the component selection model.
			 */
			this.get_selected_quantity = function() {

				return this.component_selection_model.get( 'selected_quantity' );
			};

			/**
			 * Get the product type of the selected product.
			 */
			this.get_selected_product_type = function() {

				return this.$component_data.data( 'product_type' );
			};

			/**
			 * Gets the (formatted) product title from the component selection model.
			 */
			this.get_selected_product_title = function( formatted, check_invalid ) {

				check_invalid = typeof( check_invalid ) === 'undefined' ? false : check_invalid;
				formatted     = typeof( formatted ) === 'undefined' ? false : formatted;

				if ( check_invalid && ! this.is_selected_product_valid() ) {
					return '';
				}

				var title           = this.component_selection_model.selected_product_title,
					qty             = this.get_selected_quantity(),
					product_type    = this.get_selected_product_type(),
					formatted_title = '',
					formatted_meta  = '',
					formatted_qty   = '';

				// Pass through 'component_selection_title' filter - @see WC_CP_Filters_Manager class.
				title = composite.filters.apply_filters( 'component_selection_title', [ title, this ] );

				if ( title && formatted ) {

					if ( product_type === 'none' ) {
						if ( this.is_optional() ) {
							formatted_title = '<span class="content_product_title none">' + title + '</span>';
						}
					} else {

						formatted_qty   = qty > 1 ? '<strong>' + wc_composite_params.i18n_qty_string.replace( '%s', qty ) + '</strong>' : '';
						formatted_title = wc_composite_params.i18n_title_string.replace( '%t', title ).replace( '%q', formatted_qty ).replace( '%p', '' );
						formatted_meta  = this.get_selected_product_meta( true );

						if ( formatted_meta ) {
							formatted_title = wc_composite_params.i18n_selected_product_string.replace( '%t', formatted_title ).replace( '%m', '<span class="content_product_meta">' + formatted_meta + '</span>' );
						}

						formatted_title = '<span class="content_product_title">' + formatted_title + '</span>';
					}

					// Pass through 'component_selection_formatted_title' filter - @see WC_CP_Filters_Manager class.
					formatted_title = composite.filters.apply_filters( 'component_selection_formatted_title', [ formatted_title, title, qty, formatted_meta, this ] );
				}

				return formatted ? formatted_title : title;
			};

			/**
			 * Gets (formatted) meta for the selected product.
			 */
			this.get_selected_product_meta = function( formatted ) {

				formatted = typeof( formatted ) === 'undefined' ? false : formatted;

				var formatted_meta = '',
					meta           = this.get_selected_variation( false ) > 0 ? this.component_selection_model.selected_variation_data : [];

				// Pass through 'component_selection_meta' filter - @see WC_CP_Filters_Manager class.
				meta = composite.filters.apply_filters( 'component_selection_meta', [ meta, this ] );

				if ( meta.length > 0 && formatted ) {
					$.each( meta, function( index, data ) {
						formatted_meta = formatted_meta + '<span class="meta_element"><span class="meta_key">' + data.meta_key + ':</span> <span class="meta_value">' + data.meta_value;
						if ( index !== meta.length - 1 ) {
							formatted_meta = formatted_meta + ', ';
						}
						formatted_meta = formatted_meta + '</span></span>';
					} );

					// Pass through 'component_selection_formatted_meta' filter - @see WC_CP_Filters_Manager class.
					formatted_meta = composite.filters.apply_filters( 'component_selection_formatted_meta', [ formatted_meta, meta, this ] );
				}

				return formatted ? formatted_meta : meta;
			};

			/**
			 * Gets image src for the selected product/variation.
			 */
			this.get_selected_product_image_src = function( check_invalid ) {

				check_invalid = typeof( check_invalid ) === 'undefined' ? true : check_invalid;

				if ( check_invalid && ! this.is_selected_product_valid() ) {
					return '';
				}

				return this.get_selected_variation( check_invalid ) > 0 && this.component_selection_model.selected_variation_image_src ? this.component_selection_model.selected_variation_image_src : this.component_selection_model.selected_product_image_src;
			};

			/**
			 * True if the currently selected product is incompatible based on the active scenarios.
			 */
			this.is_selected_product_valid = function( active_options ) {

				if ( typeof( active_options ) === 'undefined' ) {
					active_options = this.component_options_model.get( 'active_options' );
				}

				return this.component_selection_model.get( 'selected_product' ) === '' || _.contains( active_options, this.component_selection_model.get( 'selected_product' ) );
			};

			/**
			 * True if the currently selected variation is incompatible based on the active scenarios.
			 */
			this.is_selected_variation_valid = function( active_options ) {

				if ( typeof( active_options ) === 'undefined' ) {
					active_options = this.component_options_model.get( 'active_options' );
				}

				return this.component_selection_model.get( 'selected_variation' ) === '' || _.contains( active_options, this.component_selection_model.get( 'selected_variation' ) );
			};

			/**
			 * When true, hide incompatible/disabled products.
			 */
			this.hide_disabled_products = function() {

				return this._hide_disabled_products;
			};

			/**
			 * When true, hide incompatible/disabled variations.
			 */
			this.hide_disabled_variations = function() {

				return this._hide_disabled_variations;
			};

			/**
			 * Find a param for the selected product in the DOM.
			 */
			this.find_selected_product_param = function( param ) {

				if ( param === 'id' ) {
					return this.$component_options_select.val();
				} else if ( param === 'variation_id' ) {
					if ( this.get_selected_product_type() === 'variable' ) {
						return this.$component_summary_content.find( '.single_variation_wrap .variations_button input.variation_id' ).val();
					}
					return '';
				} else if ( param === 'title' ) {
					return this.$component_options_select.find( 'option:selected' ).data( 'title' );
				} else if ( param === 'variation_data' ) {
					var $variations = this.$component_summary_content.find( '.variations' );
					if ( $variations.length > 0 ) {
						return wc_cp_get_variation_data( $variations, false );
					}
					return [];
				} else if ( param === 'product_image_src' ) {
					var pi_src = this.$component_options_select.find( 'option:selected' ).data( 'image_src' );
					return pi_src ? pi_src : '';
				} else if ( param === 'variation_image_src' ) {

					var variation_id = this.find_selected_product_param( 'variation_id' ),
						variations   = this.$component_data.data( 'product_variations' ),
						vi_src       = '';

						if ( variation_id > 0 && variations ) {
							$.each( variations, function( index, variation ) {
								if ( parseInt( variation.variation_id ) === parseInt( variation_id ) ) {
									vi_src = variation.image_src;
									return false;
								}
							} );
						}

					return vi_src ? vi_src : '';

				} else if ( param === 'quantity' ) {
					var qty = this.$component_quantity.length > 0 ? this.$component_quantity.val() : 0;
					return parseInt( qty );
				} else if ( param === 'addons_price' ) {
					var $addons_totals = this.$component_summary_content.find( '.component_wrap #product-addons-total' ),
						addons_price   = $addons_totals.length > 0 ? $addons_totals.data( 'addons-raw-price' ) : 0;
					return Number( addons_price );
				} else if ( param === 'nyp_price' ) {
					var $nyp      = this.$component_summary_content.find( '.nyp' ),
						nyp_price = $nyp.length > 0 ? $nyp.data( 'price' ) : 0;
					return Number( nyp_price );
				}
			};

			/**
			 * Find a pagination param in the DOM.
			 */
			this.find_pagination_param = function( param, $el ) {

				if ( typeof( $el ) === 'undefined' ) {
					$el = self.$component_pagination;
				}

				var value = 1;

				if ( $el.length > 0 ) {
					if ( param === 'current_page' ) {
						value = $el.find( '.pagination_data' ).data( 'page_num' );
					} else if ( param === 'pages' ) {
						value = $el.find( '.pagination_data' ).data( 'pages' );
					} else if ( param === 'results_per_page' ) {
						value = $el.find( '.pagination_data' ).data( 'results_per_page' );
					}
				} else {
					if ( param === 'results_per_page' ) {
						value = '';
					}
				}

				return value;
			};

			/**
			 * Find active order by value in the DOM.
			 */
			this.find_order_by = function() {

				var orderby = '';

				if ( this.$component_ordering.length > 0 ) {
					orderby = this.$component_ordering.val();
				}

				return orderby;
			};

			/**
			 * Find active component filters in the DOM.
			 */
			this.find_active_filters = function() {

				var component_filters = this.$component_filters;
				var filters           = {};

				if ( component_filters.length == 0 ) {
					return filters;
				}

				component_filters.find( '.component_filter_option.selected' ).each( function() {

					var filter_type = $( this ).closest( '.component_filter' ).data( 'filter_type' );
					var filter_id   = $( this ).closest( '.component_filter' ).data( 'filter_id' );
					var option_id   = $( this ).data( 'option_id' );

					if ( filter_type in filters ) {

						if ( filter_id in filters[ filter_type ] ) {

							filters[ filter_type ][ filter_id ].push( option_id );

						} else {

							filters[ filter_type ][ filter_id ] = [];
							filters[ filter_type ][ filter_id ].push( option_id );
						}

					} else {

						filters[ filter_type ]              = {};
						filters[ filter_type ][ filter_id ] = [];
						filters[ filter_type ][ filter_id ].push( option_id );
					}

				} );

				return filters;
			};

			/**
			 * Find available component option ids in the DOM.
			 */
			this.find_available_options = function() {

				var $options      = self.$component_options_select.find( 'option' );
				var options_array = [];

				if ( $options.length > 0 ) {
					$options.each( function() {
						options_array.push( $( this ).val() );
					} );
				}

				return options_array;
			};

			/**
			 * False if the component has an out-of-stock availability class.
			 */
			this.is_in_stock = function() {

				var is_in_stock = true;

				if ( this.$component_summary_content.find( '.component_wrap .out-of-stock' ).not( '.inactive' ).length > 0 && this.get_selected_quantity() > 0 ) {
					if ( this.get_selected_product_type() !== 'variable' || this.get_selected_variation( false ) > 0 ) {
						is_in_stock = false;
					}
				}

				return is_in_stock;
			};

			this.is_nyp = function() {

				return this.component_selection_model.get( 'selected_nyp' ) > 0;
			};

			/**
			 * Gets the options style for this component.
			 */
			this.has_options_style = function( style ) {

				return this.$el.hasClass( 'options-style-' + style );
			};

			/**
			 * Get the bundle script object.
			 *
			 */
			this.get_bundle_script = function() {

				var bundle = false;

				if ( typeof( wc_pb_bundle_scripts[ self.component_id ] ) !== 'undefined' ) {
					bundle = wc_pb_bundle_scripts[ self.component_id ];
				}

				return bundle;
			};

			/**
			 * Initialize component scripts dependent on product type - called when selecting a new Component Option.
			 * When called with init = false, no type-dependent scripts will be initialized.
			 */
			this.init_scripts = function( init ) {

				if ( typeof( init ) === 'undefined' ) {
					init = true;
				}

				this.$component_data     = this.$component_summary_content.find( '.component_data' );
				this.$component_quantity = this.$component_summary_content.find( '.component_wrap input.qty' );

				if ( init ) {

					self.initializing_scripts = true;

					self.init_qty_input();

					var product_type    = this.get_selected_product_type(),
						summary_content = this.$component_summary_content;

					if ( product_type === 'variable' ) {

						if ( ! summary_content.hasClass( 'cart' ) ) {
							summary_content.addClass( 'cart' );
						}

						if ( ! summary_content.hasClass( 'variations_form' ) ) {
							summary_content.addClass( 'variations_form' );
						}

						// Put filtered variations in place.
						summary_content.data( 'product_variations', this.$component_data.data( 'product_variations' ) );

						// Copy variation ids into the 'active_options' attribute of the options model to ensure that get_selected_variation doesn't return null until the attribute is refreshed.
						$.each( this.$component_data.data( 'product_variations' ), function( index, variation ) {
							self.component_options_model.attributes.active_options.push( variation.variation_id.toString() );
						} );

						// Initialize variations script.
						summary_content.wc_variation_form();

						// Fire change in order to save 'variation_id' input.
						summary_content.find( '.variations select' ).change();

						// Complete all pending animations.
						summary_content.find( 'div' ).stop( true, true );

					} else if ( product_type === 'bundle' ) {

						if ( ! summary_content.hasClass( 'bundle_form' ) ) {
							summary_content.addClass( 'bundle_form' );
						}

						// Initialize bundles script now.
						summary_content.find( '.bundle_data' ).wc_pb_bundle_form();

						// Complete all pending animations.
						summary_content.find( 'div' ).stop( true, true );

					} else {

						if ( ! summary_content.hasClass( 'cart' ) ) {
							summary_content.addClass( 'cart' );
						}
					}

					self.initializing_scripts = false;
				}

				// Run 'component_scripts_initialized' action - @see WC_CP_Actions_Dispatcher class description.
				composite.actions.do_action( 'component_scripts_initialized', [ self ] );
			};

			/**
			 * Get the step that corresponds to this component.
			 */
			this.get_step = function() {

				return composite.get_step( this.component_id );
			};

			/**
			 * True if a Component is static (single option).
			 */
			this.is_static = function() {

				return this._is_static;
			};

			/**
			 * True if a Component is set as optional.
			 */
			this.is_optional = function() {

				var is_optional = _.contains( this.component_options_model.get( 'active_options' ), '' );

				// Pass through 'component_is_optional' filter - @see WC_CP_Filters_Manager class.
				return composite.filters.apply_filters( 'component_is_optional', [ is_optional, this ] );
			};

			/**
			 * Initialize quantity input.
			 */
			this.init_qty_input = function() {

				// Quantity buttons.
				if ( wc_composite_params.is_wc_version_gte_2_3 === 'no' || wc_composite_params.show_quantity_buttons === 'yes' ) {
					this.$component_summary_content.find( 'div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)' ).addClass( 'buttons_added' ).append( '<input type="button" value="+" class="plus" />' ).prepend( '<input type="button" value="-" class="minus" />' );
				}

				this.$component_quantity.trigger( 'change' );
			};

			/**
			 * @deprecated
			 */
			this.get_selected_product_id = function() {
				return this.find_selected_product_param( 'id' );
			};

			this.add_component_actions();
		}

		this.create_component = function( composite, $component, index ) {

			WC_CP_Component.prototype             = this.create( WC_CP_Step.prototype );
			WC_CP_Component.prototype.constructor = WC_CP_Component;

			return new WC_CP_Component( composite, $component, index );
		};

		this.create_step = function( composite, $step, index ) {

			if ( $step.hasClass( 'component' ) ) {
				return this.create_component( composite, $step, index );
			}

			return new WC_CP_Step( composite, $step, index );
		};

		this.create = function( o ) {
			function F() {}
			F.prototype = o;
			return new F();
		};

	};


	/*-----------------------------------------------------------------*/
	/*  Initialization.                                                */
	/*-----------------------------------------------------------------*/

	$wc_cp_document.ready( function() {

		/**
		 * QuickView compatibility.
		 */
		$wc_cp_body.on( 'quick-view-displayed', function() {

			$( '.composite_form .composite_data' ).each( function() {
				$( this ).wc_composite_form();
			} );
		} );

		/**
		 * Responsive form CSS (we can't rely on media queries since we must work with the .composite_form width, not screen width).
		 */
		$wc_cp_window.resize( function() {

			$.each( wc_cp_composite_scripts, function( container_id, composite ) {

				clearTimeout( composite.timers.on_resize_timer );

				composite.timers.on_resize_timer = setTimeout( function() {
					composite.on_resize_handler();
				}, 50 );
			} );
		} );

		/**
	 	 * Composite app initialization on '.composite_data' jQuery objects.
	 	 */
		$.fn.wc_composite_form = function() {

			if ( ! $( this ).hasClass( 'composite_data' ) ) {
				return true;
			}

			var composite_id    = $( this ).data( 'container_id' ),
				$composite_form = $( this ).closest( '.composite_form' );

			if ( typeof( wc_cp_composite_scripts[ composite_id ] ) !== 'undefined' ) {
				$composite_form.find( '*' ).off();
			}

			wc_cp_composite_scripts[ composite_id ] = new WC_CP_Composite( { $composite_form: $composite_form, $composite_data: $( this ) } );

			$composite_form.data( 'script_id', composite_id );

			wc_cp_composite_scripts[ composite_id ].init();
		};

		/*
		 * Initialize form script.
		 */
		$( '.composite_form .composite_data' ).each( function() {
			$( this ).wc_composite_form();
		} );

	} );

} ) ( jQuery, Backbone );

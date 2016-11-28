/* @exclude */
/* jshint -W069 */
/* jshint -W041 */
/* jshint -W018 */
/* @endexclude */

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

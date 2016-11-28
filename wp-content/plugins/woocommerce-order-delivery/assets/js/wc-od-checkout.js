/**
 * WC_OD Checkout scripts
 *
 * @autor   WooThemes
 * @package WC_OD
 * @since   1.0.0
 */

/**
 * Checkout function.
 *
 * @param {jQuery} $       The jQuery instance.
 * @param {Object} options The WC_OD checkout options.
 */
;(function( $, options ) {

	'use strict';

	var WC_OD = function( options ) {
		var defaults = {
			language: 'en',
			format: 'yyyy-mm-dd',
			weekStart: 0,
			daysOfWeekDisabled: [],
			startDate: null,
			endDate: null,
			events: {}
		};

		this.options = $.extend( {}, defaults, options );
	};

	WC_OD.prototype = {

		init: function() {
			this.$deliveryDate = null;
			this.disabledDays = {};
			this.$checoutForm = $( 'form[name="checkout"]' );
			this.$shippingCheckbox = this.$checoutForm.find( '#ship-to-different-address-checkbox' );
			this.country = '';
			this.state = '';

			// Initialize the country and state values.
			this.changeCountry();
			this.changeState();

			// Bind events.
			this._bindEvents();
		},

		_bindEvents: function() {
			var that = this;

			this.$shippingCheckbox.on( 'change', function() {
				that.changeCountry();
				that.changeState();
			});

			this.$checoutForm.on( 'change', '#billing_country, #shipping_country', function() {
				that.changeCountry();
				that.changeState();
			});

			this.$checoutForm.on( 'change', '#billing_state, #shipping_state', function() {
				that.changeState();
				that.updateDeliveryDateCalendar();
			});

			$( 'body' ).on( 'updated_checkout', function() {
				that.updateDeliveryDateCalendar();
			});
		},

		updateDeliveryDateCalendar: function() {
			var that = this;

			this.disabledDays = this.getDisabledDays();

			if ( this.$deliveryDate ) {
				this.$deliveryDate.bootstrapDP( 'update' );
			} else {
				this.$deliveryDate = $( '#delivery_date' ).bootstrapDP({
					language: this.options.language,
					format: this.options.format,
					weekStart: this.options.weekStart,
					daysOfWeekDisabled: this.options.disableWeekDays,
					startDate: '+' + this.options.minDeliveryDays + 'd',
					endDate: '+' + this.options.maxDeliveryDays + 'd',
					clearBtn: true,
					autoclose: true,
					beforeShowDay: function( date ) {
						var date_info;

						if ( that.disabledDays[ date.format() ] ) {
							date_info = false;
						}

						return date_info;
					}
				}).on( 'changeDate', function() {
					$( 'body' ).trigger( 'update_checkout' );
				});
			}
		},

		getAddresType: function() {
			return ( this.$shippingCheckbox.is( ':checked' ) ? 'shipping' : 'billing' );
		},

		changeCountry: function() {
			var addressType = this.getAddresType(),
			    value = $( '#' + addressType + '_country' ).val();

			this.country = ( value ? value : '' );
		},

		changeState: function() {
			var addressType = this.getAddresType(),
			    value = $( '#' + addressType + '_state' ).val();

			this.state = ( value ? value : '' );
		},

		getDisabledDays: function() {
			var that = this,
			    events = {};

			$.each( that.options.events, function( index ) {
				var event = that.options.events[ index ],
				    startDate,
				    endDate;

				if ( event.country && that.country !== event.country ) {
					return ''; // Continue
				}

				if ( event.states && -1 === event.states.indexOf( that.state ) ) {
					return ''; // Continue
				}

				startDate = new Date( event.start );
				events[ startDate.format() ] = event.title;

				if ( event.end ) {
					endDate = new Date( event.end );
					while ( startDate < endDate ) {
						startDate = startDate.addDays( 1 );
						events[ startDate.format() ] = event.title;
					}
				}
			});

			return events;
		}
	};

	// Extends the Date prototype with the addDays function.
	Date.prototype.addDays = function( days ) {
		var dat = new Date( this.valueOf() );
		dat.setDate( dat.getDate() + days );

		return dat;
	};

	// Extends the Date prototype with the format function.
	Date.prototype.format = function() {
		return this.getFullYear() + '-' + parseInt( this.getMonth() + 1, 10 ) + '-' + parseInt( this.getDate(), 10 );
	};

	$(function() {
		// Bootstrap Datepicker no conflict.
		$.fn.bootstrapDP = $.fn.datepicker.noConflict();

		new WC_OD( options ).init();
	});
})( jQuery, wc_od_checkout_l10n );
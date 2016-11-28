/**
 * Settings scripts
 *
 * @autor   WooThemes
 * @package WC_OD
 * @since   1.0.0
 */

/**
 * Settings function.
 *
 * @param {jQuery} $       The jQuery instance.
 * @param {Object} options The WC_OD settings options.
 */
;(function( $, options ) {

	'use strict';

	var WC_OD_Settings = {
		init: function() {
			var $timepickers = $( '.timepicker' ),
			    $eventCalendar = $( '.wc-od-calendar-field' );

			// Init timepickers
			if ( $timepickers.length ) {
				$( '.timepicker' ).timepicker( { timeFormat: 'H:i', maxTime: '23:59' } );
			}

			this.deliveryCheckoutOptionsToggle();

			if ( $eventCalendar.length && $.isFunction( $.fn.WC_OD_Calendar ) ) {
				// Init calendar.
				if ( 'delivery' === options.eventsType ) {
					$eventCalendar.WC_OD_Delivery_Calendar( options );
				} else {
					$eventCalendar.WC_OD_Calendar( options );
				}
			}
		},
		deliveryCheckoutOptionsToggle: function() {
			var $field = $( 'input[name="wc_od_checkout_delivery_option"]' ),
				toggleFields = [
					$( '#wc_od_delivery_days_0' ).closest( 'tr' )[0],
					$( '#wc_od_max_delivery_days' ).closest( 'tr' )[0]
				];

			if ( $field.length ) {
				if ( 'text' === $field.filter( ':checked' ).val() ) {
					$( toggleFields ).hide();
				}

				$field.on( 'change', function() {
					$( toggleFields ).toggle();
				});
			}
		}
	};

	$(function() {
		WC_OD_Settings.init();
	});
})( jQuery, wc_od_settings_l10n );
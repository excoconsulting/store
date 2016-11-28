jQuery( function( $ ) {

	var loading_icon = '<span class="loading-icon"><img src="images/wpspin_light.gif"/></span>';

	// Update condition values
	$( '#wapl_conditions' ).on( 'change', '.wapl-select-condition', ( function() {

		var data = {
			action: 		'wapl_meta_boxes_conditions_ajax_values',
			condition_key: 	$( this ).val(),
			id: 			$( this ).attr( 'data-id' ),
			group: 			$( this ).attr( 'data-group' ),
		};
		var replace_id = '#value_' + data.id + '_wrap';

		// Display loading icon
		$( replace_id ).html( loading_icon );

		$.post( ajaxurl, data, function( response ) {
			$( replace_id ).replaceWith( response );
		});

	}));


	// Add condition
	$( '#conditions-wrap' ).on( 'click', '.condition-add', function() {

		var data = { action: 'wapl_meta_box_ajax_add_condition', group: $( this ).attr( 'data-group' ) };

		// Display loading icon
		$( '.conditions-group[data-group=' + data.group + ']' ).append( loading_icon ).children( ':last' );

		$.post( ajaxurl, data, function( response ) {
			$( '.conditions-group[data-group=' + data.group + ']' ).append( response ).children( ':last' ).hide().fadeIn( 'normal' );
			$( '.conditions-group[data-group=' + data.group + '] .loading-icon' ).remove();
		});

	});


	// Remove condition field
	$( '#conditions-wrap' ).on( 'click', '.condition-delete', function() {

		if ( $( this ).closest( '.conditions-group' ).children( '.wapl-condition-wrap' ).length == 1 ) {
			$( this ).closest( '.conditions-group' ).fadeOut( 'normal', function() { $( this ).remove();	});
		} else {
			$( this ).closest( '.wapl-condition-wrap' ).fadeOut( 'normal', function() { $( this ).remove(); });
		}

	});


	// Add conditions OR group
	$( '#conditions-wrap' ).on('click', '.add-group', function() {

		// Display loading icon
		$( '#conditions-container' ).append( loading_icon ).children( ':last' );

		var data = {
			action: 'wapl_meta_box_ajax_add_group',
			group: 	parseInt( $( '.conditions-group' ).last().attr( 'data-group').replace( 'group_','' ) ) + 1,
		};

		$.post( ajaxurl, data, function( response ) {
			$( '.conditions-group-wrap ~ .loading-icon' ).last().remove();
			$( '#conditions-container' ).append( response ).children( ':last' ).hide().fadeIn( 'normal' );
		});

	});

});

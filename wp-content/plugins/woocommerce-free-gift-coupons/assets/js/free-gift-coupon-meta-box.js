jQuery( function($){

	// Coupon type options
	$('select#discount_type').change(function(){

		// Get value
		var select_val = $(this).val();

		$toggle_fields = $('.coupon_amount_field, .free_shipping_field, .apply_before_tax_field' );

		if ( select_val == 'free_gift' ) {
			$('.show_if_free_gift').show();
			$toggle_fields.hide();
		} else {
			$('.show_if_free_gift').hide();
			$toggle_fields.show();
		}

	}).change();



});
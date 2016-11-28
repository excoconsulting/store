jQuery( function($){

	// WC 2.6 adds coupons via ajax but FGC needs refresh to update the cart items
	$( document.body ).on( 'applied_coupon removed_coupon', function(){
		window.location.reload();
	});

});
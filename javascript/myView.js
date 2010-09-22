$(document).ready( function() {
	$(window).resize( function() {
		addWidthClass();
	});
	addWidthClass();
});

function addWidthClass() {
	/* Remove any existing width classes */
	$('body').removeClass( 'width-800' );
	$('body').removeClass( 'width-1024' );
	$('body').removeClass( 'width-1280' );
	$('body').removeClass( 'width-wide' );
	
	var docWidth = $(document).width();
	if( docWidth <= 800 ) {
		$('body').addClass( 'width-800' );
	} else if ( docWidth <= 1024 ) {
		$('body').addClass( 'width-1024' );
	} else if ( docWidth <= 1280 ) {
		$('body').addClass( 'width-1280' );
	} else {
		$('body').addClass( 'width-wide' );
	}

}

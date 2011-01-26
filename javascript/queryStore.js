$(document).ready( function() {
	$('#filter-is-public').live('click', function() {
		setAccessLevelVisibility();
	});

	$('[name=stored_query_myview], [name=stored_query_mylist]').live( 'submit', function(event) {
		event.preventDefault();
		var newForm = '';
		var currentDiv = $(this).parent();
		$.ajax({
			type: "POST",
			url: $(this).attr('action'),
			data: $(this).serialize() + '&ajax=1',
			success: function(msg){
				newForm=msg;
				// insert ok img and fade it out, fade new form in
				currentDiv.prepend('<img class="ok-img hidden" src="images/ok.gif" alt="Successful" />');
				currentDiv.children('form').replaceWith(newForm);
				currentDiv.children('.ok-img').fadeIn(2000, function() {
					currentDiv.children('.ok-img').fadeOut(1000, function() {
						currentDiv.children('.ok-img').remove();
						currentDiv.children('form').fadeIn( 1000 );
					});
				});
			},
			error: function( xhr, ajaxOptions, thrownError ) {
				alert( xhr.status);
				alert(thrownError);
			}
		});
	});
	setAccessLevelVisibility();	
});

function setAccessLevelVisibility() {
	if( $('#filter-is-public').is(':checked') ) {
		$('#filter-access-level').show();
		$('#filter-access-level-label').show();
	} else {
		$('#filter-access-level').hide();
		$('#filter-access-level-label').hide();
	}
}

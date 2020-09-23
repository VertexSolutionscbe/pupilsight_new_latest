/*
Pupilsight, Flexible & Open School System
*/

jQuery(function($){

	// Show the reason/comment when the attendance dropdown is changed
	$('select[name$="-type"]').change( function() {

		// Get the data-id from the preceding hidden input
		var id = $(this).prev('input').data('id');

		// Show/hide the div container
		$('#'+id+'-hideReasons').show();
	});

});
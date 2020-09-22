/*
Pupilsight, Flexible & Open School System
*/

function getDate() {
	// GET CURRENT DATE
	var date = new Date();
 
	// GET YYYY, MM AND DD FROM THE DATE OBJECT
	var yyyy = date.getFullYear().toString();
	var mm = (date.getMonth()+1).toString();
	var dd  = date.getDate().toString();
 
	// CONVERT mm AND dd INTO chars
	var mmChars = mm.split('');
	var ddChars = dd.split('');
 
	// CONCAT THE STRINGS IN YYYY-MM-DD FORMAT
	var datestring = yyyy + '-' + (mmChars[1]?mm:"0"+mmChars[0]) + '-' + (ddChars[1]?dd:"0"+ddChars[0]);
	
	return datestring ;
}

jQuery(function($){

	// Select all tool for Attendance by Class/Roll Group
	$('#set-all').click( function() {
		$('select[name$="-type"]').val(  $('select[name="set-all-type"]').val() );
		$('select[name$="-reason"]').val(  $('select[name="set-all-reason"]').val() );
		$('input[name$="-comment"]').val(  $('input[name="set-all-comment"]').val() );
		$('#set-all-note').show();
	});
});
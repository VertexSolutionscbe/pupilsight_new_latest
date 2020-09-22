/*
Pupilsight, Flexible & Open School System
*/

/**
 * Compares two version number strings.
 * @param    string  a
 * @param    string  b
 * @return   Return values:
            - a number < 0 if a < b
            - a number > 0 if a > b
            - 0 if a = b
 */
function versionCompare(a, b) {
    var i, diff;
    var regExStrip0 = /(\.0+)+$/;
    var segmentsA = a.replace(regExStrip0, '').split('.');
    var segmentsB = b.replace(regExStrip0, '').split('.');
    var l = Math.min(segmentsA.length, segmentsB.length);

    for (i = 0; i < l; i++) {
        diff = parseInt(segmentsA[i], 10) - parseInt(segmentsB[i], 10);
        if (diff) {
            return diff;
        }
    }
    return segmentsA.length - segmentsB.length;
}


$(function(){

    $("select.columnOrder").on('change', function(){

        var currentSelection = $(this).val();
        var textBox = $(this).parent().parent().parent().find('input.columnText');

        textBox.prop("readonly", currentSelection != columnDataCustom );
        textBox.prop("disabled", currentSelection != columnDataCustom );

        if ( currentSelection == columnDataFunction ) {
            textBox.val("*generated*");
        } else if ( currentSelection == columnDataCustom ) {
            textBox.val("");
        } else if ( currentSelection == columnDataSkip ) {
            textBox.val("*skipped*");
        } else if ( currentSelection >= 0 ) {
            if ( currentSelection in csvFirstLine ) {
                textBox.val(csvFirstLine[ currentSelection ] );
            } else {
                textBox.val("");
            }
        }
    });
    $("select.columnOrder").change();

	$("#ignoreErrors").click(function() {
		if ($(this).is(':checked')) {
			$(this).val( 1 );
			$("#submitStep3").prop("disabled", false).prop("type", "submit").prop("value", "Submit");
		} else {
			$(this).val( 0 );
			$("#submitStep3").prop("disabled", true).prop("value", "Cannot Continue");
		}
	});
}); 

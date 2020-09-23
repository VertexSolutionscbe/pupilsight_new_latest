/*
Pupilsight, Flexible & Open School System
*/

$(document).ready(function(){

    // Initialize datepicker
    $.datepicker.setDefaults($.datepicker.regional[Pupilsight.config.datepicker.locale]);


    // Initialize tooltip
    if ($(window).width() > 768) {
        $(document).tooltip({
            show: 800,
            hide: false,
            content: function () {
                return $(this).prop('title');
            },
            position: {
                my: "center bottom-20",
                at: "center top",
                using: function (position, feedback) {
                    $(this).css(position);
                    $("<div>").
                        addClass("arrow").
                        addClass(feedback.vertical).
                        addClass(feedback.horizontal).
                        appendTo(this);
                }
            }
        });
    }

    // Initialize latex
    $(".latex").latex();

    // Initialize tinymce
    tinymce.init({
        selector: "div#editorcontainer textarea",
        width: '100%',
        menubar : false,
        toolbar: 'bold, italic, underline,forecolor,backcolor,|,alignleft, aligncenter, alignright, alignjustify, |, formatselect, |, fontselect, fontsizeselect, |, table, |, bullist, numlist,outdent, indent, |, link, unlink, image, media, hr, charmap, subscript, superscript, |, cut, copy, paste, undo, redo, fullscreen',
        plugins: 'table, template, paste, visualchars, link, template, textcolor, hr, charmap, fullscreen',
        statusbar: false,
        valid_elements: Pupilsight.config.tinymce.valid_elements,
        invalid_elements: '',
        apply_source_formatting : true,
        browser_spellcheck: true,
        convert_urls: false,
        relative_urls: false,
        default_link_target: "_blank"
    });

    // Initialize sessionTimeout
    var sessionDuration = Pupilsight.config.sessionTimeout.sessionDuration;
    if (sessionDuration > 0) {
        $.sessionTimeout({
            message: Pupilsight.config.sessionTimeout.message,
            keepAliveUrl: 'keepAlive.php' ,
            redirUrl: 'logout.php?timeout=true',
            logoutUrl: 'logout.php' ,
            warnAfter: sessionDuration * 1000,
            redirAfter: (sessionDuration * 1000) + 600000
        });
    }
});

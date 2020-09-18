// Container for the report config
var wdtReportConfig = {}, custom_uploader = null, wdtDownloadToken = '';

(function ($) {

    $(function () {

        var nextStepButton = $('#wdt-rb-next-step');
        var previousStepButton = $('#wdt-rb-previous-step');

        /**
         * Steps switcher (Next)
         */
        nextStepButton.click(function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var $curStepBlock = $('div.wdt-rb-step:visible:eq(0)');
            var curStep = $curStepBlock.data('step');
            $('#wdt-rb-report-name').change();

            if (!$('#wdt-rb-report-name').val()) {
                wdtNotify(wdt_rb_strings.error, wdt_rb_strings.reportNameEmpty, 'danger');
                return;
            }
            $curStepBlock.hide();
            $('li.wdt-rb-breadcrumbs-block').removeClass('active');

            switch (curStep) {
                case 1:
                    $('.wdt-preload-layer').animateFadeIn();
                    // Defining data source
                    if ($('#wdt-rb-report-source').val()) {
                        wdtReportConfig.dataSource = $('#wdt-rb-report-source').val();
                        $('.wdt-rb-generation-logic-block').show();
                        $('.wdt-rb-follow-filtering-block').show();
                        $('.wdt-rb-report-preview-rows-block').show();
                    } else {
                        wdtReportConfig.dataSource = null;
                        $('.wdt-rb-generation-logic-block').hide();
                        $('.wdt-rb-follow-filtering-block').hide();
                        $('.wdt-rb-report-download-file-mask-block').hide();
                        $('.wdt-rb-report-preview-rows-block').hide();
                    }
                    $('div.wdt-rb-step[data-step="2"]').animateFadeIn();
                    $('li.wdt-rb-breadcrumbs-block[data-step="2"]').addClass('active');
                    previousStepButton.prop('disabled', false);
                    previousStepButton.animateFadeIn();
                    $('.wdt-preload-layer').animateFadeOut();
                    break;
                case 2:
                    $('.wdt-preload-layer').animateFadeIn();
                    // Report name
                    wdtReportConfig.name = $('#wdt-rb-report-name').val();
                    // Generation logic
                    if (wdtReportConfig.dataSource !== null) {
                        wdtReportConfig.generationLogic = $('#wdt-rb-generation-logic').val();
                    } else {
                        wdtReportConfig.generationLogic = 'single';
                    }
                    // File mask
                    if (wdtReportConfig.generationLogic == 'multiple') {
                        wdtReportConfig.filenameMask = $('#wdt-rb-report-download-file-mask').val();
                    }
                    // Follow filtering
                    wdtReportConfig.followFiltering = $('#wdt-rb-follow-filtering').is(':checked') ? 1 : 0;
                    // Collecting additional variables
                    wdtReportConfig.additionalVars = [];
                    if ($('div.wdt-rb-custom-var-block').length > 0) {
                        wdtReportConfig.additionalVars = {};
                        $('div.wdt-rb-custom-var-block').each(function () {
                            if ($(this).find('#wdt-rb-var-name').val() != '') {
                                wdtReportConfig.additionalVars[$(this).find('#wdt-rb-var-name').val()] =
                                    $(this).find('#wdt-rb-var-default-value').val();
                            }
                        });
                        if ($.isEmptyObject(wdtReportConfig.additionalVars)) {
                            wdtReportConfig.additionalVars = [];
                        }
                    }

                    $('div.wdt-rb-step[data-step="3"]').animateFadeIn();
                    $('li.wdt-rb-breadcrumbs-block[data-step="3"]').addClass('active');
                    $('.wdt-preload-layer').animateFadeOut();
                    break;
                case 3:
                    if ($('#wdt-rb-template-file').val() !== '') {
                        $('.wdt-preload-layer').animateFadeIn();
                        wdtReportConfig.templateFile = $('#wdt-rb-template-file').val();
                        nextStepButton.prop('disabled', true);
                        nextStepButton.hide()
                        $('#wdt-rb-save-report').show();
                        $('div.wdt-rb-step[data-step="4"]').animateFadeIn();
                        $('li.wdt-rb-breadcrumbs-block[data-step="4"]').addClass('active');
                        $('.wdt-preload-layer').animateFadeOut();
                    } else {
                        wdtNotify(wdt_rb_strings.error, wdt_rb_strings.fileUploadEmptyFile, 'danger');
                        $('li.wdt-rb-breadcrumbs-block[data-step="3"]').addClass('active');
                        $curStepBlock.show();
                    }
                    break;
            }
        });

        /**
         * Steps switcher (Prev)
         */
        previousStepButton.click(function (e) {
            e.preventDefault();

            var $curStepBlock = $('div.wdt-rb-step:visible:eq(0)');
            var curStep = $curStepBlock.data('step');

            $('.wdt-preload-layer').animateFadeIn();
            $curStepBlock.hide();
            $('div.wdt-rb-step[data-step="' + (curStep - 1) + '"]').animateFadeIn();
            $('li.wdt-rb-breadcrumbs-block').removeClass('active');
            $('li.wdt-rb-breadcrumbs-block[data-step="' + (curStep - 1) + '"]').addClass('active');

            switch (curStep) {
                case 2:
                    previousStepButton.hide();
                    previousStepButton.prop('disabled', true);
                    break;
                case 3:
                    nextStepButton.show();
                    break;
                case 4:
                    nextStepButton.prop('disabled', false);
                    nextStepButton.show();
                    $('#wdt-rb-save-report').hide();
                    break;
                case 5:
                    $('div.wdt-rb-step.step4').show(200);
                    $('div.wdt-rb-step.step5').hide(200);
                    $('#wdt-rb-save-report').show();
                    $('#wdt-rb-finish-report').hide();
                    $('div.report_builder_breadcrumbs_block.step5').removeClass('active');
                    $('div.report_builder_breadcrumbs_block.step4').addClass('active');
                    break;
            }

            $('.wdt-preload-layer').animateFadeOut();

        });

        // Helper function to add custom variables
        var rbAddCustomVar = function (customVar) {
            if (typeof customVar == 'undefined') {
                customVar = {name: '', value: ''};
            }

            var customVarTmpl = $.templates('#wdt-rb-custom-var-tmpl');
            var $customVarHtml = $(customVarTmpl.render(customVar));

            $($customVarHtml)
                .appendTo('#wdt-rb-custom-vars-container')
                .find('#wdt-rb-var-name')
                .focus();
            $('[data-toggle="tooltip"]').tooltip();

        };

        /**
         * Show the file mask only for multi-file reports
         */
        $('#wdt-rb-generation-logic').change(function (e) {
            if ($(this).val() == 'single') {
                $('.wdt-rb-report-download-file-mask-block').hide();
            }
            if ($(this).val() == 'multiple') {
                $('.wdt-rb-report-download-file-mask-block').animateFadeIn();
            }
        });

        /**
         * Initialize the report config if an edit action is called
         */
        if (typeof wdt_report_config_db !== 'undefined') {
            // Copy everything from back-end
            wdtReportConfig = wdt_report_config_db;
            // Set the data source
            $('#wdt-rb-report-source')
                .val(wdtReportConfig.dataSource)
                .change();
            // Set the report name
            $('#wdt-rb-report-name').val(wdtReportConfig.name);
            // Set the default variables
            if (typeof wdtReportConfig.additionalVars !== 'undefined') {
                for (var varName in wdtReportConfig.additionalVars) {
                    rbAddCustomVar({name: varName, value: wdtReportConfig.additionalVars[varName]});
                }
            }
            // Set the generation logic
            if (wdtReportConfig.dataSource != null) {
                $('#wdt-rb-generation-logic')
                    .val(wdtReportConfig.generationLogic)
                    .change();
                if (wdtReportConfig.generationLogic == 'multiple') {
                    $('#wdt-rb-report-download-file-mask').val(wdtReportConfig.filenameMask);
                }
                $('#wdt-rb-follow-filtering').prop('checked', wdtReportConfig.followFiltering === '1');
            }
            // Set the template URL
            $('#wdt-rb-template-file').val(wdtReportConfig.templateFile);

        }

        /**
         * Add custom variable
         */
        $('#wdt-rb-add-custom-var').click(function (e) {
            e.preventDefault;
            e.stopImmediatePropagation;
            rbAddCustomVar();
        });

        /**
         * Delete custom variable
         */
        $(document).on('click', '#wdt-rb-custom-vars-container li#wdt-rb-delete-custom-var', function (e) {
            e.preventDefault();
            $(this).closest('div.wdt-rb-custom-var-block').remove();
        });

        /**
         * Slugify custom variable name
         */
        $(document).on('keyup', '#wdt-rb-custom-vars-container #wdt-rb-var-name', function (e) {
            e.preventDefault();
            $(this).closest('div.wdt-rb-custom-var-block').find('#wdt-tb-var-definition').html($(this).val());
        });

        /**
         * Uploader
         */
        $('#wdt-rb-template-file-button').click(function (e) {

            e.preventDefault();

            // Extend the wp.media object
            custom_uploader = wp.media.frames.file_frame = wp.media({
                title: wdt_rb_strings.choose_file,
                button: {
                    text: wdt_rb_strings.choose_file
                },
                multiple: false,
                library: {
                    type: 'application/vnd.ms-powerpointtd,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                }
            });

            // When a file is selected, grab the URL and set it as the text field's value
            custom_uploader.on('select', function () {
                attachment = custom_uploader.state().get('selection').first().toJSON();
                $('#wdt-rb-template-file').val(attachment.url);
            });

            //Open the uploader dialog
            custom_uploader.open();

        });

    });

    /**
     * Save report
     */
    $('#wdt-rb-save-report').click(function (e) {
        e.preventDefault();
        $('.wdt-preload-layer').animateFadeIn();
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'JSON',
            data: {
                action: 'report_builder_save_report',
                wdtReportConfig: wdtReportConfig
            },
            success: function (data) {
                if (data.result === 'success') {
                    wdtReportConfig.id = data.id;

                    // Generate the shortcodes
                    var rbShortcodesTmpl = $.templates('#wdt-rb-shortcodes-template');
                    var rbShortcodesHtml = $(rbShortcodesTmpl.render(wdtReportConfig));

                    $('div.wdt-rb-step[data-step="5"] .wdt-rb-shortcodes-block').html(rbShortcodesHtml);
                    $('[data-toggle="tooltip"]').tooltip();
                    $('#wdt-rb-save-report').hide();
                    $('#wdt-rb-finish-report').show();
                    $('div.wdt-rb-step[data-step="4"]').hide();
                    $('div.wdt-rb-step[data-step="5"]').animateFadeIn();
                    $('li.wdt-rb-breadcrumbs-block[data-step="4"]').removeClass('active');
                    $('li.wdt-rb-breadcrumbs-block[data-step="5"]').addClass('active');
                } else {
                    alert('There was a problem when trying to save your report!');
                }
                $('.wdt-preload-layer').animateFadeOut();
            }
        })

    });

    /**
     * Go to Browse on finish button
     */
    $('#wdt-rb-finish-report').click(function (e) {
        e.preventDefault();
        window.location = wdt_rb_vars.browse_url;
    });

    /**
     * Download preview result
     */
    $('#wdt-rb-download-preview').click(function (e) {
        e.preventDefault();
        $('#rpbwdtPreloadLayer').show();

        wdtReportConfig.rowsLimit = $('#wdt-rb-report-preview-rows').val();

        wdtDownloadToken = new Date().getTime();
        wdtExpireCookie('wdtDownloadToken');
        var wdtDownloadTimer = window.setInterval(function () {
            var token = wdtGetCookie("wdtDownloadToken");
            if (token == wdtDownloadToken) {
                $('#rpbwdtPreloadLayer').hide();
                wdtExpireCookie("wdtDownloadToken");
                window.clearInterval(wdtDownloadTimer);
                $('.wdt-preload-layer').animateFadeOut();
            }

        }, 1000);

        $.redirect(
            ajaxurl,
            {
                wdtReportConfig: wdtReportConfig,
                action: 'report_builder_download_preview',
                wdtDownloadToken: wdtDownloadToken
            }
        );

    });

    $('button.wdt-rb-backend-close').click(function(){
        $('#wdt-rb-backend-close-modal').modal('show');

        $('#wdt-rb-backend-close-button').click(function() {
            $(location).attr('href', wdt_rb_vars.siteUrl);
        });
    });

    /**
     * Helper function to slugify text
     * @param string Text
     * @returns String Text with all special symbols cut
     */
    function wdtSlugify(Text) {
        return Text
            .toLowerCase()
            .replace(/ /g, '')
            .replace(/[^\w-]+/g, '')
            ;
    }

})(jQuery);
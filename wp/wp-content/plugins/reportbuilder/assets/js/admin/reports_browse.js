/**
 * JS controller for browsing reports
 */

(function($){

    $(function(){

        /**
         * Search reports in backend
         */
        $("input#search_id-search-input").on("keyup", _.debounce(function() {
                $("button#search-submit").click();
            }, 800)
        )

        /**
         * Delete a report
         */
        $('.delete-report').click(function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            $('#wdt-delete-modal').modal('show');

            var href = $(this).attr('href');

            $('#wdt-browse-delete-button').click(function () {
                window.location = href;
            });
        });

        /**
         * Download a generated report
         */
        $('a.download-rb-report').click(function(e){
            e.preventDefault();

            var wdtReportConfig = {
                id: $(this).data('report_id'),
                additionalVars: []
            };

            wdtDownloadToken = new Date().getTime();
            wdtExpireCookie( 'wdtDownloadToken' );
            var wdtDownloadTimer = window.setInterval( function() {
                var token = wdtGetCookie( "wdtDownloadToken" );
                if( token == wdtDownloadToken ) {
                    wdtExpireCookie( "wdtDownloadToken" );
                    window.clearInterval( wdtDownloadTimer );
                    $('.wdt-preload-layer').animateFadeOut();
                }
            }, 1000 );

            $.redirect(
                ajaxurl,
                {
                    wdtReportConfig: wdtReportConfig,
                    nonce: '%%ADMINAREA%%',
                    action: 'report_builder_download_report',
                    wdtDownloadToken: wdtDownloadToken
                }
            );

        });

        /**
         * Open shortcodes dialog and fill it with report shortcodes
         */
        $('a.get-rb-shortcodes').click(function(e){
            e.preventDefault();
            $('.wdt-preload-layer').animateFadeIn();
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'report_builder_get_report_data',
                    id: $(this).data('report_id')
                },
                success: function( wdtReportConfig ){
                    $('.wdt-preload-layer').animateFadeOut();
                    var rbShortcodesTmpl = $.templates('#wdt-rb-shortcodes-template');
                    var rbShortcodesHtml = $( rbShortcodesTmpl.render(wdtReportConfig ));
                    $('#wdt-rb-shortcodes-modal .modal-body .col-sm-12').html(rbShortcodesHtml);
                    $('[data-toggle="tooltip"]').tooltip();
                    $('#wdt-rb-shortcodes-modal').modal('show');
                }
            })
        });

        /**
         * Bulk action alert
         */
        $('#doaction, #doaction2').click( function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var select_box = $(this).siblings('div.wpdt-bulk-select').find('select.wpdt-bulk-select').val();

            if (select_box == -1) {
                return;
            }

            if ($('#reportbuilder_browse_form table.widefat input[type="checkbox"]:checked').length == 0) {
                return;
            }

            $('#wdt-delete-modal').modal('show');

            $('#wdt-browse-delete-button').click(function () {
                $('#reportbuilder_browse_form').submit();
            });
        });
        /**
         * Highlight a row when checkbox is active
         */
        $(document).on('click','.wdt-datatables-admin-wrap .card.wdt-browse-table table tbody :checkbox',function() {
            var parent_tr = $(this).closest('tr');
            var btnDelete = $('#doaction');
            if ($(this).is(':checked')) {
                parent_tr.addClass('checked-row');
            } else {
                parent_tr.removeClass('checked-row');
            }
            var numOfCheckedTR = $('#reportbuilder_browse_form table tr.checked-row');
            if (numOfCheckedTR.length){
                btnDelete.removeClass('disabled').html('<i class="wpdt-icon-trash-reg"></i>' + wpdatatables_edit_strings.deleteSelected);
            } else {
                btnDelete.addClass('disabled').html('<i class="wpdt-icon-trash-reg"></i>' + wpdatatables_edit_strings.delete);
            }
        });

    });

})(jQuery);

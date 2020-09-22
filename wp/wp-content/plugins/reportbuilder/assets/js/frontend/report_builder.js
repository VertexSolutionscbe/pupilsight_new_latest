(function($){

    /**
     * Handler for download
     */
    $(document).on('click','button.reportbuilder_button',function(e){
        e.preventDefault();
        e.stopImmediatePropagation;

        // Disable the button
        var $button = $(this);
        $button.prop( 'disabled','disabled' );
        // Start preloader
        var $preload = $('<span class="dashicons dashicons-update wdtrotate"> </span>').prependTo($(this));

        var wdtReportConfig = {
            id: $(this).data('report_id'),
            additionalVars: []
        };

        $('input.reportbuilder_input').each(function(){
            wdtReportConfig.additionalVars.push({
                name: $(this).data('var_name'),
                value: encodeURIComponent($(this).val())
            });
        });

        // Check if wpDataTable is present on page - if it is, override the data
        if( $(this).data('source_id') != '' && window['reportbuilder_'+wdtReportConfig.id].follow_filtering == '1' ){
            var $wdtable = $('table.wpDataTable[data-wpdatatable_id='+$(this).data('source_id')+']');
            if( $wdtable.length > 0 ){
                var wdtObj = wpDataTables[$wdtable.get(0).id];
                // Get only the filtered data and pass it to report config
                wdtReportConfig.filteredData = JSON.stringify( wdtObj._('tr', {"filter": "applied"}).toArray() );
            }
        }

        var fileHandling = $(this).data('type') || 'download';

        if( fileHandling == 'download' ){
            wdtDownloadToken = new Date().getTime();
            wdtExpireCookie( 'wdtDownloadToken' );
            var wdtDownloadTimer = window.setInterval( function() {
                var token = wdtGetCookie( "wdtDownloadToken" );
                if( token == wdtDownloadToken ) {
                    wdtExpireCookie( "wdtDownloadToken" );
                    window.clearInterval( wdtDownloadTimer );
                    // Enable the button
                    $button.prop( 'disabled','' );
                    // Remove preloader
                    $preload.remove();
                }

            }, 1000 );

            $.redirect(
                window['reportbuilder_'+wdtReportConfig.id].ajaxurl,
                {
                    wdtReportConfig: wdtReportConfig,
                    nonce: window['reportbuilder_'+wdtReportConfig.id].nonce,
                    action: 'report_builder_download_report',
                    wdtDownloadToken: wdtDownloadToken,
                    downloadType: $(this).data('type')
                }
            );
        }else if( fileHandling == 'save' ) {
            $.ajax({
                url: window['reportbuilder_'+wdtReportConfig.id].ajaxurl,
                type: 'POST',
                data: {
                    action: 'report_builder_download_report',
                    wdtReportConfig: wdtReportConfig,
                    nonce: window['reportbuilder_'+wdtReportConfig.id].nonce,
                    wdtDownloadToken: '',
                    downloadType: $(this).data('type')
                },
                success: function(){
                    // Enable the button
                    $button.prop( 'disabled','' );
                    // Remove preloader
                    $preload.remove();
                }
            })
        }

    })

})(jQuery)
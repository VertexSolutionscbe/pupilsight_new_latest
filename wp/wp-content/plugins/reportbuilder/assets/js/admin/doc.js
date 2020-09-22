var docsHomeUrl = 'http://wpreportbuilder.com';

jQuery('.wdt-documentation').click(function (e) {
    e.preventDefault();
    switch (jQuery(this).data('doc-page')) {
        case 'browse_page':
            window.open(docsHomeUrl + '/documentation/');
            break;
        case 'wizard':
            window.open(docsHomeUrl + '/documentation/report-builder-wizard/');
            break;
    }
});
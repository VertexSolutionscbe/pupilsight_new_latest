(function ($) {
    $(function () {

        // Handle Activation Settings
        handleActivationSettings();

        // Handle callback from Envato when activating the plugin
        authenticateEnvatoOAuthCallback();

        $('#wdt-envato-activation-report').on('click', function () {
            authenticateEnvatoOAuth()
        });

        $('#wdt-envato-deactivation-report').on('click', function () {
            deactivatePlugin()
        });

        // Add event on "Activate"/"Deactivate" button
        $('#wdt-activate-plugin-report').on('click', function () {
            if (typeof wdt_current_config.wdtActivatedReport === 'undefined' || wdt_current_config.wdtActivatedReport == 0 || wdt_current_config.wdtActivatedReport == '') {
                activatePlugin()
            } else {
                deactivatePlugin()
            }
        });

        // Activate plugin
        function activatePlugin() {
            $('#wdt-activate-plugin-report').html('<i class="wpdt-icon-spinner9"></i>Loading...');

            let domain    = location.hostname;
            let subdomain = location.hostname;

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpdatatables_activate_plugin',
                    purchaseCodeStore: $('#wdt-purchase-code-store-report').val(),
                    wdtNonce: $('#wdtNonce').val(),
                    slug: 'reportbuilder',
                    domain: domain,
                    subdomain: subdomain
                },
                success: function (response) {
                    let valid = JSON.parse(response).valid;
                    let domainRegistered = JSON.parse(response).domainRegistered;

                    if (valid === true && domainRegistered === true) {
                        wdt_current_config.wdtActivatedReport = 1;
                        wdt_current_config.wdtPurchaseCodeStoreReport = $('#wdt-purchase-code-store-report').val();
                        wdtNotify('Success!', 'Plugin has been activated', 'success');
                        $('#wdt-purchase-code-store-report').prop('disabled', 'disabled');
                        $('#wdt-activate-plugin-report').removeClass('btn-primary').addClass('btn-danger').html(' <i class="wpdt-icon-times-circle-full"></i>Deactivate');
                        $('.wdt-envato-activation-report').hide()
                    } else if (valid === false) {
                        wdtNotify(wpdatatablesSettingsStrings.error, wpdatatablesSettingsStrings.purchaseCodeInvalid, 'danger');
                        $('#wdt-activate-plugin-report').html(' <i class="wpdt-icon-check-circle-full"></i>Activate');
                    } else {
                        wdtNotify(wpdatatablesSettingsStrings.error, wpdatatablesSettingsStrings.activation_domains_limit, 'danger');
                        jQuery('#wdt-activate-plugin-report').html(' <i class="wpdt-icon-check-circle-full"></i>Activate');
                    }
                },
                error: function () {
                    wdt_current_config.wdtActivatedReport = 0;
                    wdtNotify('Error!', 'Unable to activate the plugin. Please try again.', 'danger');
                    $('#wdt-activate-plugin-report').html(' <i class="wpdt-icon-check-circle-full"></i>Activate');
                }
            });
        }

        // Deactivate plugin
        function deactivatePlugin() {
            $('#wdt-activate-plugin-report').html(' <i class="wpdt-icon-spinner9"></i>Loading...');
            $('#wdt-envato-deactivation-report').html(' <i class="wpdt-icon-spinner9"></i>Loading...');
            let domain    = location.hostname;
            let subdomain = location.hostname;
            let params = {
                action: 'wpdatatables_deactivate_plugin',
                wdtNonce: $('#wdtNonce').val(),
                domain: domain,
                subdomain: subdomain,
                slug: 'reportbuilder',
            };

            if (wdt_current_config.wdtPurchaseCodeStoreReport) {
                params.type = 'code';
                params.purchaseCodeStore = wdt_current_config.wdtPurchaseCodeStoreReport;
            } else if (wdt_current_config.wdtEnvatoTokenEmailReport) {
                params.type = 'envato';
                params.envatoTokenEmail = wdt_current_config.wdtEnvatoTokenEmailReport;
            }

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: params,
                success: function (response) {
                    var parsedResponse = JSON.parse(response);
                    if (parsedResponse.deactivated === true) {
                        wdt_current_config.wdtPurchaseCodeStoreReport = '';
                        wdt_current_config.wdtEnvatoTokenEmail = '';
                        wdt_current_config.wdtActivatedReport = 0;
                        $('#wdt-purchase-code-store-report').prop('disabled', '').val('');
                        $('#wdt-activate-plugin-report').removeClass('btn-danger').addClass('btn-primary').html('<i class="wpdt-icon-check-circle-full"></i>Activate');
                        $('.wdt-envato-activation-report').show()
                        $('.wdt-preload-layer').animateFadeOut();
                        $('#wdt-envato-activation-report span').text(wpdatatablesSettingsStrings.activateWithEnvato);
                        $('#wdt-envato-activation-report').prop('disabled', '');
                        $('#wdt-envato-deactivation-report').html(' <i class="wpdt-icon-times-circle-full"></i>Deactivate').hide()
                        $('.wdt-purchase-code-report').show();
                    } else {
                        wdtNotify(wpdatatablesSettingsStrings.error, wpdatatablesSettingsStrings.unable_to_deactivate_plugin, 'danger');
                        $('#wdt-activate-plugin-report').html('<i class="wpdt-icon-times-circle-full"></i>Deactivate');
                        $('#wdt-envato-deactivation-report').html('<i class="wpdt-icon-times-circle-full"></i>Deactivate');
                    }
                }
            });
        }

        function authenticateEnvatoOAuth() {
            let domain    = location.hostname;
            let subdomain = location.hostname;
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpdatatables_parse_server_name',
                    wdtNonce: $('#wdtNonce').val(),
                    domain: domain,
                    subdomain: subdomain
                },
                success: function (response) {
                    let serverName = JSON.parse(response);
                    let domain = serverName.domain;
                    let subdomain = serverName.subdomain;
                    window.location.replace(
                        wdtStore.url + 'activation/envato?slug=reportbuilder&domain=' + domain + '&subdomain=' + subdomain + '&redirectUrl=' + wdtStore.redirectUrl + '/wp-admin/admin.php?page=wpdatatables-settings'
                    )
                }
            });
        }

        function authenticateEnvatoOAuthCallback() {
            // Get value of valid query parameter
            var valid = searchQueryString('valid');
            var domainRegistered = searchQueryString('domainRegistered');
            var slug = searchQueryString('slug');

            if (valid !== null && slug === 'reportbuilder') {

                // Remove query parameters sent back from TMS Store
                let redirectURL = this.removeURLParameter(window.location.href, 'valid');
                redirectURL = this.removeURLParameter(redirectURL, 'slug');
                redirectURL = this.removeURLParameter(redirectURL, 'domainRegistered');

                $('.tab-nav a[href="#wdt-activation"]').tab('show');

                if (valid === 'true' && domainRegistered === 'true' && searchQueryString('envatoTokenEmail')) {
                    // Set refresh token
                    wdt_current_config.wdtEnvatoTokenEmailReport = searchQueryString('envatoTokenEmail');
                    // Set activated
                    wdt_current_config.wdtActivatedReport = 1;

                    // Change button text and disable it
                    $('#wdt-envato-activation-report span').text(wpdatatablesSettingsStrings.envato_api_activated);
                    $('#wdt-envato-activation-report').prop('disabled', 'disabled');
                    $('.wdt-purchase-code-report').hide();
                    $('#wdt-envato-deactivation-report').show();

                    // Save plugin settings
                    $.ajax({
                        url: ajaxurl,
                        dataType: 'json',
                        method: 'POST',
                        data: {
                            action: 'wpdatatables_save_plugin_settings',
                            settings: wdt_current_config
                        },
                        success: function () {
                            $('.wdt-preload-layer').animateFadeOut();
                            wdtNotify(wpdatatables_edit_strings.success, wpdatatables_edit_strings.pluginActivated, 'success');
                        }
                    });

                    redirectURL = this.removeURLParameter(redirectURL, 'envatoTokenEmail')
                } else if (valid === 'false') {
                    wdtNotify(wpdatatablesSettingsStrings.error, wpdatatablesSettingsStrings.envato_failed_report, 'danger');
                } else if (domainRegistered === 'false') {
                    wdtNotify(wpdatatablesSettingsStrings.error, wpdatatablesSettingsStrings.activation_domains_limit, 'danger');
                }

                window.history.pushState(null, null, redirectURL)
            }
        }

        function handleActivationSettings() {
            if (wdt_current_config.wdtActivatedReport == 1) {
                if (wdt_current_config.wdtEnvatoTokenEmailReport) {
                    // Change button text and disable it
                    $('#wdt-envato-activation-report span').text(wpdatatablesSettingsStrings.envato_api_activated);
                    $('#wdt-envato-activation-report').prop('disabled', 'disabled');
                    $('#wdt-envato-deactivation-report').show()
                    $('.wdt-purchase-code-report').hide()
                } else {
                    $('.wdt-envato-activation-report').hide()

                    // Fill the purchase code input on settings load
                    $('#wdt-purchase-code-store-report').val(wdt_current_config.wdtPurchaseCodeStoreReport);

                    // Change the "Activate"/"Deactivate" button if plugin is activated/deactivated
                    $('#wdt-purchase-code-store-report').prop('disabled', 'disabled');
                    $('#wdt-activate-plugin-report').removeClass('btn-primary').addClass('btn-danger').html('<i class="wpdt-icon-times-circle-full"></i>Deactivate');
                }
            } else {
                $('#wdt-purchase-code-store-report').prop('disabled', '');
                $('#wdt-activate-plugin-report').removeClass('btn-danger').addClass('btn-primary').html('<i class="wpdt-icon-check-circle-full"></i>Activate');
            }
        }
    });
})(jQuery);

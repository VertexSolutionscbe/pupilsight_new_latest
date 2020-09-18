<!-- Panel Group -->
<div class="col-sm-6 m-b-30">

    <div class="wdt-activation-section">

        <div class="wpdt-plugins-desc">
            <img class="img-responsive" src="<?php echo WDT_ASSETS_PATH; ?>img/addons/report-builder-logo.png" alt="">
            <h4> <?php _e('Report Builder', 'wpdatatables'); ?></h4>
        </div>

        <!-- Panel Body -->
        <div class="panel-body">

            <!-- TMS Store Purchase Code -->
            <div class="col-sm-10 wdt-purchase-code-report p-l-0">

                <!-- TMS Store Purchase Code Heading-->
                <h4 class="c-title-color m-b-4 m-t-0">
                    <?php _e('TMS Store Purchase Code', 'wpdatatables'); ?>
                    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                       title="<?php _e('If your brought the plugin directly on our website, enter TMS Store purchase code to enable auto updates.', 'wpdatatables'); ?>"></i>
                </h4>
                <!-- /TMS Store Purchase Code Heading -->

                <!-- TMS Store Purchase Code Form -->
                <div class="form-group m-b-0">
                    <div class="row">

                        <!-- TMS Store Purchase Code Input -->
                        <div class="col-sm-11 p-r-0">
                            <div class="fg-line">
                                <input type="text" name="wdt-purchase-code-store-report"
                                       id="wdt-purchase-code-store-report"
                                       class="form-control input-sm"
                                       placeholder="<?php _e('Please enter your Report Builder TMS Store Purchase Code', 'wpdatatables'); ?>"
                                       value=""
                                />
                            </div>
                        </div>
                        <!-- TMS Store Purchase Code Input -->

                        <!-- TMS Store Purchase Code Activate Button -->
                        <div class="col-sm-1">
                            <button class="btn btn-primary wdt-store-activate-plugin" id="wdt-activate-plugin-report">
                                <i class="wpdt-icon-check-circle-full"></i><?php _e('Activate ', 'wpdatatables'); ?>
                            </button>
                        </div>
                        <!-- /TMS Store Purchase Code Activate Button -->

                    </div>
                </div>
                <!-- /TMS Store Purchase Code Form -->

            </div>
            <!-- /TMS Store Purchase Code -->

            <!-- Envato API -->
            <div class="col-sm-10 wdt-envato-activation wdt-envato-activation-report p-l-0">

                <!-- Envato API Heading-->
                <h4 class="c-title-color m-b-4 m-t-0">
                    <?php _e('Envato API', 'wpdatatables'); ?>
                    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                       title="<?php _e('If you brought the plugin on the Envato (CodeCanyon) activate the plugin using Envato API to enable auto updates.', 'wpdatatables'); ?>"></i>
                </h4>
                <!-- /Envato API Heading -->

                <!-- Envato API Form -->
                <div class="form-group m-b-0">
                    <div class="row m-l-0">

                        <!-- Envato API Button -->
                        <button class="btn wdt-envato-activation-button"
                                id="wdt-envato-activation-report">
                            <div id="wdt-envato-div">
                                <img src="<?php echo WDT_ASSETS_PATH ?>img/envato.svg"
                                     class="wdt-envato-activation-logo"
                                >
                            </div>
                            <span>
                                    <?php _e('Activate with Envato', 'wpdatatables'); ?>
                                </span>
                        </button>
                        <!-- /Envato API Button -->

                        <button class="btn btn-danger  wdt-envato-deactivation-button"
                                style="display: none;" id="wdt-envato-deactivation-report">
                            <i class="wpdt-icon-times-circle-full"></i><?php _e('Deactivate ', 'wpdatatables'); ?>
                        </button>

                    </div>
                </div>
                <!-- /Envato API Form -->

            </div>
            <!-- /Envato API -->

        </div>
        <!-- /Panel Body -->
    </div>

</div>
<!-- /Panel Group -->

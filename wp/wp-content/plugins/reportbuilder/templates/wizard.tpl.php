<div class="wrap wdt-datatables-admin-wrap">

    <input type="hidden" id="report_id" value="<?php if (!empty($report_id)) {
        echo $report_id;
    } ?>"/>

    <div class="container">

        <div class="row">

            <div class="card wdt-table-constructor">

                <!-- Preloader -->
                <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>
                <!-- /Preloader -->

                <div class="card-header wdt-admin-card-header ch-alt">
                    <img id="wpdt-inline-logo"
                         src="<?php echo WDT_RB_ROOT_URL; ?>assets/img/Report-builder.svg"/>
                    <h2>
                    <span style="display: none">Report Builder Wizard</span>
                        <?php _e('Create Report', 'wpdatatables'); ?>
                    </h2>
                    <ul class="actions p-t-5">
                        <li>
                            <button class="btn wdt-rb-backend-close"><?php _e('Cancel', 'wpdatatables'); ?>
                            </button>
                        </li>
                    </ul>
                </div>
                <!-- /.card-header -->

                <div class="card-body card-padding">

                    <ol class="breadcrumb wdt-rb-breadcrumb">
                        <li class="wdt-rb-breadcrumbs-block active"
                            data-step="1"><?php _e('Data Source', 'wpdatatables'); ?></li>
                        <li class="wdt-rb-breadcrumbs-block"
                            data-step="2"><?php _e('Settings and variables', 'wpdatatables'); ?></li>
                        <li class="wdt-rb-breadcrumbs-block" data-step="3"><?php _e('Template', 'wpdatatables'); ?></li>
                        <li class="wdt-rb-breadcrumbs-block"
                            data-step="4"><?php _e('Download preview', 'wpdatatables'); ?></li>
                        <li class="wdt-rb-breadcrumbs-block"
                            data-step="5"><?php _e('Get shortcodes', 'wpdatatables'); ?></li>
                    </ol>

                    <div class="steps m-t-20">

                        <?php include WDT_RB_ROOT_PATH . 'templates/steps/step_1.inc.php'; ?>

                        <?php include WDT_RB_ROOT_PATH . 'templates/steps/step_2.inc.php'; ?>

                        <?php include WDT_RB_ROOT_PATH . 'templates/steps/step_3.inc.php'; ?>

                        <?php include WDT_RB_ROOT_PATH . 'templates/steps/step_4.inc.php'; ?>

                        <?php include WDT_RB_ROOT_PATH . 'templates/steps/step_5.inc.php'; ?>

                        <?php include WDT_RB_ROOT_PATH . 'templates/custom_vars.tpl.php'; ?>

                        <?php include WDT_RB_ROOT_PATH . 'templates/shortcodes.tpl.php'; ?>

                        <?php include WDT_RB_ROOT_PATH . 'templates/close_modal.inc.php'; ?>

                    </div>



                </div>
                <!-- /.card-body -->
                <div class="row m-t-15 m-b-5 p-l-15 p-r-15">
                    <button class="btn wdt-button wdt-primary-button pull-right m-l-5" style="display:none;"
                            id="wdt-rb-finish-report">
                        <?php _e('Browse reports ', 'wpdatatables'); ?></button>
                    <button class="btn wdt-button wdt-primary-button pull-right m-l-5" style="display:none;"
                            id="wdt-rb-save-report">
                        <i class="wpdt-icon-save"></i><?php _e('Save report ', 'wpdatatables'); ?></button>
                    <button class="btn btn-primary wdt-button wdt-primary-button pull-right m-l-5" id="wdt-rb-next-step">
                        <?php _e('Next ', 'wpdatatables'); ?></button>
                    <button class="btn wdt-button wdt-secondary-button hidden pull-right" id="wdt-rb-previous-step"
                            disabled="disabled"><?php _e(' Previous', 'wpdatatables'); ?></button>
                    <a class="btn btn-default wdt-button wdt-third-button btn-icon-text wdt-documentation"
                       data-doc-page="wizard">
                        <i class="wpdt-icon-file-thin"></i> <?php _e('View Documentation', 'wpdatatables'); ?>
                    </a>
                </div>
            </div>
            <!-- /.card /.wdt-table-constructor -->

        </div>
        <!-- /.row -->

    </div>
    <!-- /.container -->

</div>
<!-- .wdt-datatables-admin-wrap -->
<div class="wrap wdt-datatables-admin-wrap">

    <!-- .container -->
    <div class="container">

        <!-- .row -->
        <div class="row">

            <!-- .card .wdt-browse-table -->
            <div class="card wdt-browse-table">

                <!-- Preloader -->
                <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>
                <!-- /Preloader -->

                <!-- .card-header -->
                <div class="card-header wdt-admin-card-header ch-alt">
                    <img id="wpdt-inline-logo"
                         src="<?php echo WDT_RB_ROOT_URL; ?>assets/img/Report-builder.svg"/>
                    <h2>
                        <span style="display: none">Report Builder</span>
                        <?php _e('Browse Reports', 'wpdatatables'); ?>
                    </h2>
                    <ul class="actions">
                        <li>
                            <button onclick="location.href='admin.php?page=wpdatareports-wizard'"
                                    class="btn bgm-blue  wdt-add-new">
                                <i class="wpdt-icon-plus"></i>
                                <?php _e('Add New', 'wpdatatables'); ?>
                            </button>
                        </li>
                    </ul>
                </div>
                <!--/ .card-header -->

                <form method="post" action="<?php echo admin_url('admin.php?page=wpdatareports'); ?>"
                      id="reportbuilder_browse_form">
                    <?php echo $tableHTML; ?>
                </form>
            </div>
            <!--/ .card .wdt-browse-table -->

        </div>
        <!--/ .row -->

    </div>
    <!-- .container -->

    <!-- Delete modal -->
    <?php include WDT_TEMPLATE_PATH . 'common/delete_modal.inc.php'; ?>
    <!-- /Delete modal -->

    <!-- Shortcodes modal -->
    <?php include WDT_RB_ROOT_PATH . 'templates/shortcodes_modal.inc.php'; ?>
    <!-- /Shortcodes modal -->

    <!-- Template for the shortcodes block -->
    <?php include WDT_RB_ROOT_PATH . 'templates/shortcodes.tpl.php'; ?>
    <!-- /Template for the shortcodes block -->

</div>
<!--/ .wdt-datatables-admin-wrap -->
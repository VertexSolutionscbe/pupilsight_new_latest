<div class="row wdt-rb-step" data-step="1">

    <div class="col-sm-6 col-md-6">

        <h4 class="c-title-color m-b-4">
            <?php _e('Data source','wpdatatables');?>
            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right" title="<?php _e('Choose a wpDataTable which will be used as a data source. You can also skip this if you want just a custom report.','wpdatatables'); ?>"></i>
        </h4>

        <div class="form-group">
            <div class="fg-line">
                <div class="select">
                    <select class="selectpicker" id="wdt-rb-report-source">
                        <option value=""><?php _e('None (skip)', 'wpdatatables'); ?></option>
                        <?php foreach (WPDataTable::getAllTables() as $table) { ?>
                            <option value="<?php echo $table['id'] ?>"><?php echo $table['title'] ?>
                                (id: <?php echo $table['id']; ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>

    </div>

</div>
<div class="row wdt-rb-step hidden" data-step="4">

    <div class="col-sm-6 col-md-6 wdt-rb-report-preview-rows-block">
        <h4 class="c-title-color m-b-4">
            <?php _e('Preview the report for first X table rows', 'wpdatatables'); ?>
            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php _e('Leave 0 to process the whole table. The more rows you define in the preview, the longer it might take.', 'wpdatatables'); ?>"></i>
        </h4>
        <div class="form-group">
            <div class="fg-line">
                <input type="text" class="form-control input-sm" value="5"
                       id="wdt-rb-report-preview-rows">
            </div>
        </div>
    </div>

    <div class="col-sm-12 col-md-12 p-b-10 wdt-rb-download-preview-block">
        <h4 class="c-title-color m-b-4">
            <?php _e('Preview your report', 'wpdatatables'); ?>
            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php _e('Click to download a generated preview of your report to see how it will look for the users. If something is wrong you can go back and change the report settings. If everything is fine you can save your report.', 'wpdatatables'); ?>"></i>
        </h4>
        <div class="col-sm-3 p-0">
            <button class="btn wdt-button wdt-primary-button" id="wdt-rb-download-preview">
                <i class="wpdt-icon-file-download"></i><?php _e('Download Report Preview', 'wpdatatables'); ?>
            </button>
        </div>
    </div>

</div>
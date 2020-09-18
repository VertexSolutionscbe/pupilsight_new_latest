<div class="row wdt-rb-step hidden" data-step="2">

    <div class="col-sm-6 col-md-6 wdt-rb-report-name-block">
        <h4 class="c-title-color m-b-4">
            <?php _e('Report name', 'wpdatatables'); ?>
            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php _e('Provide a name for this report which will help you to find it later. To use this in the report template insert ${reportname}.', 'wpdatatables'); ?>"></i>
        </h4>
        <div class="form-group">
            <div class="fg-line">
                <input type="text" class="form-control input-sm" value="New Report"
                       id="wdt-rb-report-name">
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-6 wdt-rb-generation-logic-block">
        <h4 class="c-title-color m-b-4">
            <?php _e('Generation logic', 'wpdatatables'); ?>
            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php _e('Choose how to treat multiple rows in the table. Would you like to create a single document with compiled data, or a separate document for each table row (downloaded in a zip file)?', 'wpdatatables'); ?>"></i>
        </h4>
        <div class="form-group">
            <div class="fg-line">
                <div class="select">
                    <select class="selectpicker" id="wdt-rb-generation-logic">
                        <option value="single"> <?php _e('Single file', 'wpdatatables'); ?></option>
                        <option value="multiple"> <?php _e('ZIP with multiple files for each row', 'wpdatatables'); ?></option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12 wdt-rb-custom-vars-block">
        <div class="col-sm-12 p-0">
            <div class="col-sm-12 p-0">
                <h4 class="c-title-color m-b-4">
                    <?php _e('Additional variables', 'wpdatatables'); ?>
                    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                       title="<?php _e('Here you can define variables that do not exist in data source, but can be used in the report, and changed by front-end users. For example, name, signature text, etc. You can see a list of predefined variables in our documentation page.', 'wpdatatables'); ?>"></i>
                </h4>
            </div>
        </div>
        <div class="form-group m-b-0" id="wdt-rb-custom-vars-container">

        </div>
        <div class="col-sm-12 p-0">
            <button class="btn wdt-button wdt-link-button pull-left" id="wdt-rb-add-custom-var">
                <i class="wpdt-icon-plus"></i>
                <?php _e('Add variable', 'wpdatatables'); ?>
            </button>
        </div>
    </div>

    <div class="col-sm-6 col-md-6 p-b-10 wdt-rb-follow-filtering-block">
        <h4 class="c-title-color m-b-4">
            <?php _e('Report data filtering', 'wpdatatables'); ?>
            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php _e('Tick to build report only for the table rows that are currently visible on the page (wpDataTable must be on the same page with the report). Might slow down report generation for large datasets.', 'wpdatatables'); ?>"></i>
        </h4>
        <div class="toggle-switch p-b-20" data-ts-color="blue">
            <input id="wdt-rb-follow-filtering" type="checkbox" hidden="hidden">
            <label for="wdt-rb-follow-filtering"><?php _e('Follow table filtering', 'wpdatatables'); ?></label>
        </div>
    </div>

    <div class="col-sm-6 col-md-6 wdt-rb-report-download-file-mask-block hidden">
        <h4 class="c-title-color m-b-4">
            <?php _e('Generated filenames mask', 'wpdatatables'); ?>
            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php _e('Choose how to name the files inside of the generated ZIP. Please keep the names unique not to overwrite each other. Extension will be XLSX for spreadsheets, DOCX for documents.', 'wpdatatables'); ?>"></i>
        </h4>
        <div class="form-group">
            <div class="fg-line">
                <input type="text" class="form-control input-sm" value="${reportname}-${count}"
                       id="wdt-rb-report-download-file-mask">
            </div>
        </div>
    </div>

</div>
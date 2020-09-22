<?php
/**
 * Template for the shortcodes block
 * @author Alexander Gilmanov
 * @since 06.04.2016
 */
?>
<script type="text/jsrender" id="wdt-rb-shortcodes-template">
<div>
   <div class="col-sm-12 wpdt-report-message text-center">
        <img src="<?php echo WDT_RB_ROOT_URL; ?>assets/img/icon_report.svg" alt="">
        <h4 class="m-t-0">
        <?php _e('Your report is ready! Checkout below available shortcodes.', 'wpdatatables'); ?>
        </h4>
    </div>
    <div class="col-sm-12 p-b-20 text-center">
        <h5>
            <?php _e('Download report button shortcode', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="top" data-html="true" title="
            <?php _e('Use this shortcode to insert a download button in your post or page. Change the value in <strong>text</strong> attribute to change what will be displayed on the button, using the <strong>class</strong> attribute you can assign CSS classes to your button to make it look nice in your theme.', 'wpdatatables'); ?>
             "></i>
        </h5>
        <div class="col-sm-12 p-0">
            <div class="wpdt-shortcode-container">
                <a class="wdt-copy-shortcode" data-toggle="tooltip" data-shortcode-type="download-report" data-placement="top" title="" data-original-title="Click to copy shortcode">
                    <i class="wpdt-icon-copy"></i>
                </a>
                <span class="wdt-shortcode-class" id="wdt-download-report-shortcode-id">[reportbuilder id={{>id}} text="Download report" class="btn"]</span>
            </div>
        </div>
    </div>
    <div class="col-sm-12 p-b-20 text-center">
        <h5>
            <?php _e('Save report to Media Library button shortcode', 'wpdatatables'); ?>
             <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="top" data-html="true" title="
            <?php _e('Use this shortcode to insert a button in your post or page that will trigger a <strong>save to WP Media Library</strong> action for this report. Change the value in <strong>text</strong> attribute to change what will be displayed on the button, using the <strong>class</strong> attribute you can assign CSS classes to your button to make it look nice in your theme.', 'wpdatatables'); ?>
             "></i>
        </h5>
        <div class="col-sm-12 p-0">
            <div class="wpdt-shortcode-container">
                <a class="wdt-copy-shortcode" data-toggle="tooltip" data-shortcode-type="save-report" data-placement="top" title="" data-original-title="Click to copy shortcode">
                    <i class="wpdt-icon-copy"></i>
                </a>
                <span class="wdt-shortcode-class" id="wdt-save-report-shortcode-id">[reportbuilder id={{>id}} type="save" text="Save report"]</span>
            </div>
        </div>
    </div>
    {{if additionalVars.length != 0}}
    <div class="col-sm-12 text-center">
        <h5>
            <?php _e('Custom variables input fields', 'wpdatatables'); ?>
             <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="top" data-html="true" title="
             <?php _e('Use the shortcodes below to generate front-end inputs for the additional variables. <strong>Text</strong> attribute allows to change the label, <strong>default</strong> can override the predefined value, <strong>class</strong> allows to set CSS classes.', 'wpdatatables'); ?>
             "></i>
        </h5>
    </div>
    {{props additionalVars ~id=id}}
    <div class="col-sm-12 p-b-10 text-center">
        <label>
           "{{>key}}":
        </label>
        <div class="com-sm-12">
            <div class="wpdt-shortcode-container">
                <a class="wdt-copy-shortcode" data-toggle="tooltip" data-shortcode-type="var-{{:#index}}" data-placement="top" title="" data-original-title="Click to copy shortcode">
                    <i class="wpdt-icon-copy"></i>
                </a>
                <span class="wdt-shortcode-class wdt-var" id="wdt-var-{{:#index}}-shortcode-id">[reportbuilder id={{:~id}} element="varInput" name="{{>key}}" text="Please enter the {{>key}}" default="{{>prop}}" class=""]</span>
            </div>
        </div>
    </div>
    {{/props}}
    {{/if}}
</div>

</script>


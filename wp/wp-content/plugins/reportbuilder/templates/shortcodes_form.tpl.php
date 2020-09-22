<?php
/**
 * Template for the shortcodes form in Visual Composer
 * @author Alexander Gilmanov
 * @since 07.04.2016
 */
?>
        <input type="hidden" id="reportbuilder_report_id" value="<?php echo $reportBuilder->getId();?>" />

        <div class="rb_form_group">
            <label class="rb_label">
                <?php _e('Download report button','wpdatatables'); ?>
                <button class="button button-primary button-large insertButtonShortcode" data-type=""><?php _e('Insert','wpdatatables'); ?></button>
            </label>
            <div class="rb_form_col">
                <div class="input_block">
                    <div class="input_label"><?php _e('Button label','wpdatatables');?>:</div>
                    <input type="text" class="buttonLabel" value="<?php echo __('Download report', 'wpdatatables');?>">
                </div>
                <div class="input_block">
                    <div class="input_label"><?php _e('Button CSS class','wpdatatables');?>:</div>
                    <input type="text" class="buttonCSSClass" value="<?php echo __('btn', 'wpdatatables');?>" />
                </div>
            </div>
        </div>
        <div class="rb_form_group">
            <label class="rb_label">
                <?php _e('Save report to Media Library button','wpdatatables'); ?>
                <button class="button button-primary button-large insertButtonShortcode" data-type="save"><?php _e('Insert','wpdatatables'); ?></button>
            </label>
            <div class="rb_form_col">
                <div class="input_block">
                    <div class="input_label"><?php _e('Button label','wpdatatables');?>:</div>
                    <input class="buttonLabel" type="text" value="<?php echo __('Save report to Media Library', 'wpdatatables');?>">
                </div>
                <div class="input_block">
                    <div class="input_label"><?php _e('Button CSS class','wpdatatables');?>:</div>
                    <input class="buttonCSSClass" type="text" value="<?php echo __('btn', 'wpdatatables');?>" />
                </div>
            </div>
        </div>
        <?php if( count( $reportBuilder->getAdditionalVars() ) ) { ?>
            <?php foreach( $reportBuilder->getAdditionalVars() as $name => $value ){ ?>
            <div class="rb_form_group">
                <label class="rb_label">
                    <?php echo __('Input for ','wpdatatables'); ?> "<span class="wdt-rb-var-name"><?php echo $name; ?></span>":
                    <button class="button button-primary button-large insertInputShortcode"><?php _e('Insert','wpdatatables'); ?></button>
                </label>
                <div class="rb_form_col">
                    <div class="input_block">
                        <div class="input_label"><?php _e('Input label','wpdatatables');?>:</div>
                        <input class="inputLabel" type="text" value="<?php echo __('Please enter the','wpdatatables').' '.$name;?>">
                    </div>
                    <div class="input_block">
                        <div class="input_label"><?php _e('Input default value','wpdatatables');?>:</div>
                        <input class="inputValue" type="text" value="<?php echo $value; ?>" />
                    </div>
                    <div class="input_block">
                        <div class="input_label"><?php _e('Input CSS class','wpdatatables');?>:</div>
                        <input class="inputCSSClass" type="text" value="" />
                    </div>
                </div>
            </div>
            <?php } ?>
        <?php } ?>


<?php
/**
 * Template for a front-end text input field
 */
?>

<label class="reportbuilder_input_label <?php echo $class; ?>" for="report_<?php echo $this->getId();?>_<?php echo $name; ?>" ><?php echo $text; ?></label><input type="text" class="reportbuilder_input <?php echo $class; ?>" data-report_id="<?php echo $this->getId();?>" data-var_name="<?php echo $name; ?>" value="<?php echo $default; ?>" />

<?php
/**
 * Template for a front-end button field
 */
?>

<button class="reportbuilder_button <?php echo $type; ?> <?php echo $class; ?>" data-type="<?php echo $type; ?>" data-source_id="<?php echo $this->getDataSourceId(); ?>" data-report_id="<?php echo $this->getId();?>" ><?php echo $text; ?></button>

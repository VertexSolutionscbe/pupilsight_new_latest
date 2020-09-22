<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;



if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_attribute_plugin.php') == false) {
    //Acess denied
    echo "<div class='error'>"; 
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs->add(__('Manage Sketch Attribute'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $sketchId = $_GET['id'];

    $sqls = "SELECT sketch_name FROM examinationReportTemplateSketch WHERE id = ".$sketchId." ";
    $results = $connection2->query($sqls);
    $sketchdata = $results->fetch();
    $sketchName = $sketchdata['sketch_name'];


    echo '<h3>';
    echo __('Sketch Attribute ('.$sketchName.')');
    echo '<a id="saveAttrFormula" class="btn btn-primary" style="float: right;margin: -6px 0 0 0px;">Save</a>';
    echo '</h3>';

    
    $sqla = "SELECT a.id as attrid, b.id, b.report_column_word, b.report_column_label FROM examinationReportTemplateAttributes AS a LEFT JOIN  examinationReportTemplateConfiguration AS b ON a.ertc_id = b.id WHERE sketch_id = ".$sketchId." ORDER BY a.pos ASC";
    $resulta = $connection2->query($sqla);
    $attrdata = $resulta->fetchAll();

    $sql = "SELECT table_label FROM examinationReportTemplateConfiguration GROUP BY table_label";
    $result = $connection2->query($sql);
    $labeldata = $result->fetchAll();

    $sqlf = "SELECT * FROM examinationReportTemplateFormula ORDER BY pos ASC";
    $resultf = $connection2->query($sqlf);
    $formuladata = $resultf->fetchAll();
    //print_r($labeldata);

?>
            
    <form id="attrFormula" method="post" action="modules/Academics/sketch_manage_attribute_formulaProcess.php">
    <input type="hidden" name="sketch_id" value="<?php echo $sketchId;?>">
    <div id="cloning" class="row">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th style="width:15%">Attributes</th>
                    <th style="width:15%">Template Id</th>
                    <th style="width:40%">Formula</th>
                    <th style="width:30%">Plugins</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($attrdata)) { 
                    foreach($attrdata as $ad){
                        $sqla = "SELECT GROUP_CONCAT(b.name) AS pluginname FROM examinationReportTemplatePluginAttributeMapping AS a LEFT JOIN  examinationReportTemplatePlugin AS b ON a.plugin_id = b.id WHERE a.erta_id = ".$ad['attrid']." ";
                        $resulta = $connection2->query($sqla);
                        $attrdata = $resulta->fetch();

                        $sqlf = "SELECT * FROM examinationReportTemplateFormulaAttributeMapping WHERE erta_id = ".$ad['attrid']." ";
                        $resultf = $connection2->query($sqlf);
                        $formulamapdata = $resultf->fetch();
                ?>
                    <input type="hidden" name="erta_id[]" value="<?php echo $ad['attrid'];?>">
                    <tr>
                        <td><?php echo $ad['report_column_label'];?></td>
                        <td><?php echo '${'.$ad['report_column_word'].'}';?></td>
                        <td style="">
                            <select id="formulaName" data-id="<?php echo $ad['attrid'];?>" name="formula_id[<?php echo $ad['attrid'];?>]" class="form-control" style="width:40% !important; float:left; margin: 0 10px 0 0px;">
                                <option value="">AS IS</option>
                                <?php if(!empty($formuladata)) { 
                                    foreach($formuladata as $fd) {   

                                ?>
                                    <option value="<?php echo $fd['id'];?>"  <?php if($formulamapdata['formula_id'] == $fd['id']) { ?> selected <?php } ?> ><?php echo $fd['name'];?></option>
                                <?php } } ?>
                            </select>
                                    <input id="formulaValue-<?php echo $ad['attrid'];?>" type="textbox" class="form-control forVal" name="formula_val[<?php echo $ad['attrid'];?>]" value="<?php echo $formulamapdata['formula_val'];?>" style="border: 1px solid #ced4da;border-radius: 4px;height: 34px;width: 40%;font-size: 14px;" <?php if(empty($formulamapdata['formula_val'])) { ?>readonly <?php } ?>>
                        </td>
                        <td><a class="thickbox" href="fullscreen.php?q=/modules/Academics/sketch_manage_plugin.php&id=<?php echo $ad['attrid'];?>"><i title="Add Plugin" class="fas fa-plus-square"></i></a>  <?php echo $attrdata['pluginname'];?></td>
                    </tr>
                    
                <?php       
                    } }   
                ?>
            </tbody>
        </table>
    </div>    
    </form>
			
<?php  
}

?>

<script>
    
    
    $(document).on('change', '#formulaName', function() {
        $(".forVal").prop('readonly', true);
        var id = $(this).attr('data-id');
        var val = $(this).val();
        if(id != '' && val != ''){
            $("#formulaValue-"+id).prop('readonly', false);
        }
    });

    $(document).on('click', '#saveAttrFormula', function() {
       $("#attrFormula").submit();
    });
</script>
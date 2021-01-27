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

    $page->breadcrumbs
        ->add(__('Manage Sketch'), 'sketch_manage.php')
        ->add(__('Manage Sketch Attribute'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $id = $_GET['id'];
    $sqlchk = "SELECT a.*, b.pupilsightSchoolYearID, b.pupilsightProgramID, b.class_ids FROM examinationReportTemplateAttributes AS a LEFT JOIN examinationReportTemplateSketch AS b ON a.sketch_id = b.id  WHERE a.id = ".$id." ";
    $resultchk = $connection2->query($sqlchk);
    $chkdata = $resultchk->fetch();

    if($chkdata['attribute_category'] == 'Entity'){
    
    echo '<h3>';
    echo __('Select Attribute');
    echo '<a id="saveAttrFormula" class="btn btn-primary" style="float: right;margin: -6px 0 0 0px;">Save</a>';
    echo '</h3>';

    
    $sqla = "SELECT * FROM examinationReportTemplateConfiguration  WHERE table_label = '".$chkdata['attribute_type']."' ";
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
    <div style="width:30%; margin-bottom:10px;" >
        <input type="text" class="w-full" id="searchTable" placeholder="Search">
    </div>
    <form id="attrFormula" method="post" action="modules/Academics/sketch_manage_attribute_formulaProcess.php">
    <input type="hidden" name="sketch_id" value="<?php echo $chkdata['sketch_id'];?>">
    <input type="hidden" name="erta_id" value="<?php echo $id;?>">
    <div id="cloning" class="row">
        <table class="table table-hover"  id="myTable">
            <thead>
                <tr>
                    <th style="width:15%">Select</th>
                    <th style="width:15%">Attribute</th>
                    <th style="width:40%">Formula</th>
                    <th style="width:30%">Plugins</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($attrdata)) { 
                    foreach($attrdata as $ad){
                        
                ?>
                    
                    <tr>
                        <td><input type="checkbox" name="ertc_id[]" value="<?php echo $ad['id'];?>"></td>
                        <td><?php echo $ad['report_column_label'];?></td>
                        <td style="">
                            <select id="formulaName" data-id="<?php echo $ad['id'];?>" name="formula_id[<?php echo $ad['id'];?>]" class="form-control" style="width:40% !important; float:left; margin: 0 10px 0 0px;">
                                <option value="">AS IS</option>
                                <?php if(!empty($formuladata)) { 
                                    foreach($formuladata as $fd) {   

                                ?>
                                    <option value="<?php echo $fd['id'];?>"  ><?php echo $fd['name'];?></option>
                                <?php } } ?>
                            </select>
                                    <input id="formulaValue-<?php echo $ad['id'];?>" type="textbox" class="form-control forVal" name="formula_val[<?php echo $ad['id'];?>]" value="" style="border: 1px solid #ced4da;border-radius: 4px;height: 34px;width: 40%;font-size: 14px;" readonly>
                        </td>
                        <td><a class="thickbox" href="fullscreen.php?q=/modules/Academics/sketch_manage_plugin.php&id=<?php echo $ad['id'];?>"><i title="Add Plugin" class="fas fa-plus-square"></i></a>  </td>
                    </tr>
                    
                <?php       
                    } }   
                ?>
            </tbody>
        </table>
    </div>    
    </form>
			
<?php  
    } elseif($chkdata['attribute_category'] == 'Test') {
        echo '<h3>';
        echo __('Select Test');
        echo '<a id="saveAttrFormula" class="btn btn-primary" style="float: right;margin: -6px 0 0 0px;">Save</a>';
        echo '</h3>';

        $sqla = "SELECT b.* FROM examinationTestAssignClass AS a LEFT JOIN examinationTestMaster AS b ON a.test_master_id = b.id WHERE a.pupilsightSchoolYearID = ".$chkdata['pupilsightSchoolYearID']." AND a.pupilsightProgramID = ".$chkdata['pupilsightProgramID']." AND pupilsightYearGroupID IN (".$chkdata['class_ids'].") GROUP BY b.id ";
        $resulta = $connection2->query($sqla);
        $attrdata = $resulta->fetchAll();

        // echo '<pre>';
        // print_r($attrdata);
        // echo '</pre>';
        // die();
        
        $sqlf = "SELECT * FROM examinationReportTemplateFormula ORDER BY pos ASC";
        $resultf = $connection2->query($sqlf);
        $formuladata = $resultf->fetchAll();

        $sqlg = "SELECT * FROM examinationGradeSystem ORDER BY id ASC";
        $resultg = $connection2->query($sqlg);
        $gradeData = $resultg->fetchAll();

        $sqls = "SELECT * FROM examinationReportTemplateAttributes WHERE sketch_id = ".$chkdata['sketch_id']." AND test_master_id != '' ";
        //die();
        $results = $connection2->query($sqls);
        $suppattrdata = $results->fetchAll();

        

    ?>    
        <div style="width:30%; margin-bottom:10px;" >
            <input type="text" class="w-full" id="searchTable" placeholder="Search">
        </div>
        <form id="attrFormula" method="post" action="modules/Academics/sketch_manage_attribute_test_formulaProcess.php">
        <input type="hidden" name="sketch_id" value="<?php echo $chkdata['sketch_id'];?>">
        <input type="hidden" name="erta_id" value="<?php echo $id;?>">
        <div id="cloning" class="row">
            <table class="table table-hover" id="myTable">
                <thead>
                    <tr>
                        <th style="width:15%">Select</th>
                        <th style="width:15%">Test Master Name</th>
                        <th style="width:15%">Test Code</th>
                        <th style="width:40%">Formula</th>
                        <th style="width:30%">Plugins</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($attrdata)) { 
                        foreach($attrdata as $ad){
                            
                    ?>
                        
                        <tr>
                            <td><input type="checkbox" name="test_master_id[]" value="<?php echo $ad['id'];?>"></td>
                            <td><?php echo $ad['name'];?></td>
                            <td><?php echo $ad['code'];?></td>
                            <td style="">
                                <select id="formulaName" data-id="<?php echo $ad['id'];?>" name="formula_id[<?php echo $ad['id'];?>]" class="form-control" style="width:40% !important; float:left; margin: 0 10px 0 0px;">
                                    <option value="">AS IS</option>
                                    <?php if(!empty($formuladata)) { 
                                        foreach($formuladata as $fd) {   

                                    ?>
                                        <option value="<?php echo $fd['id'];?>"  ><?php echo $fd['name'];?></option>
                                    <?php } } ?>
                                </select>
                                        <input id="formulaValue-<?php echo $ad['id'];?>" type="textbox" class="form-control forVal" name="formula_val[<?php echo $ad['id'];?>]" value="" style="border: 1px solid #ced4da;border-radius: 4px;height: 34px;width: 40%;font-size: 14px;" readonly>
                            </td>
                            <td><a class="thickbox" href="fullscreen.php?q=/modules/Academics/sketch_manage_plugin.php&id=<?php echo $ad['id'];?>"><i title="Add Plugin" class="mdi mdi-plus-circle mdi-24px"></i></a>  </td>
                        </tr>
                        
                    <?php       
                        } }   
                    ?>
                </tbody>
            </table>
            <?php if($chkdata['attribute_type'] == 'Grade') { ?>
            <div style="display:flex;width:100%; margin-bottom:10px;">

                <span style="font-size:14px;">Grading System : </span>
                <select name="grade_id" class="form-control" style=" width:25%;margin: 0px 10px 0 10px;">
                    <option value="">Select Grade</option>
                    <?php if(!empty($gradeData)) {
                        foreach($gradeData as $gd) { ?>
                            <option value="<?php echo $gd['id']; ?>"><?php echo $gd['name']; ?></option>
                    <?php } }?>
                </select>

                <span style="font-size:14px;">Supported Attribute : </span>
                <select name="supported_attribute" class="form-control" style=" width:25%;margin: 0px 10px 0 10px;">
                    <option value="">Select</option>
                    <?php if(!empty($suppattrdata)) {
                        foreach($suppattrdata as $adt) { ?>
                            <option value="<?php echo $adt['id']; ?>"><?php echo $adt['attribute_name']; ?></option>
                    <?php } }?>
                </select>
            </div>
         <?php } else { ?>
            <input type="hidden" name="grade_id" value="">
            <input type="hidden" name="supported_attribute" value="">
         <?php } ?>
            
        </div>    
        </form>
  
<?php
    }  elseif($chkdata['attribute_category'] == 'Computed') {
        echo '<h3>';
        echo __('Select Test');
        echo '<a id="saveAttrFormula" class="btn btn-primary" style="float: right;margin: -6px 0 0 0px;">Save</a>';
        echo '</h3>';

        $sqla = "SELECT * FROM examinationReportTemplateAttributes WHERE sketch_id = ".$chkdata['sketch_id']." AND id != ".$id." ";
       //die();
        $resulta = $connection2->query($sqla);
        $attrdata = $resulta->fetchAll();

        // echo '<pre>';
        // print_r($attrdata);
        // echo '</pre>';
        // die();
        
        $sqlf = "SELECT * FROM examinationReportTemplateFormula ORDER BY pos ASC";
        $resultf = $connection2->query($sqlf);
        $formuladata = $resultf->fetchAll();

        $sqlg = "SELECT * FROM examinationGradeSystem ORDER BY id ASC";
        $resultg = $connection2->query($sqlg);
        $gradeData = $resultg->fetchAll();

        $sqlsup = "SELECT * FROM examinationReportTemplateAttributes WHERE sketch_id = ".$chkdata['sketch_id']." AND attr_ids != '' ";
       //die();
        $resultsup = $connection2->query($sqlsup);
        $suppattrdata = $resultsup->fetchAll();


?>  

<div style="width:30%; margin-bottom:10px;" >
            <input type="text" class="w-full" id="searchTable" placeholder="Search">
        </div>
        <form id="attrFormula" method="post" action="modules/Academics/sketch_manage_attribute_multiple_attributeProcess.php">
        <input type="hidden" name="sketch_id" value="<?php echo $chkdata['sketch_id'];?>">
        <input type="hidden" name="erta_id" value="<?php echo $id;?>">
        <div id="cloning" class="row">
            <table class="table table-hover" id="myTable">
                <thead>
                    <tr>
                        <th style="width:15%">Select</th>
                        <th style="width:15%">Test Attribute</th>
                        <th style="width:40%">Formula</th>
                        <th style="width:30%">Plugins</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($attrdata)) { 
                        foreach($attrdata as $ad){

                            $sqla = "SELECT GROUP_CONCAT(b.name) AS pluginname FROM examinationReportTemplatePluginAttributeMapping AS a LEFT JOIN  examinationReportTemplatePlugin AS b ON a.plugin_id = b.id WHERE a.erta_id = ".$ad['id']." ";
                            $resulta = $connection2->query($sqla);
                            $attrdata = $resulta->fetch();
                        
                            $sqlf = "SELECT * FROM examinationReportTemplateFormulaAttributeMapping WHERE erta_id = ".$ad['id']." ";
                            $resultf = $connection2->query($sqlf);
                            $formulamapdata = $resultf->fetch();
                    ?>
                        
                        <tr>
                            <td><input type="checkbox" name="attr_id[]" value="<?php echo $ad['id'];?>"></td>
                            <td><?php echo $ad['attribute_name'];?></td>
                            <td style="">
                                <select id="formulaName" data-id="<?php echo $ad['id'];?>" name="formula_id[<?php echo $ad['id'];?>]" class="form-control" style="width:40% !important; float:left; margin: 0 10px 0 0px;">
                                    <option value="">AS IS</option>
                                    <?php if(!empty($formuladata)) { 
                                        foreach($formuladata as $fd) {   

                                    ?>
                                        <option value="<?php echo $fd['id'];?>" <?php if($formulamapdata['formula_id'] == $fd['id']) { ?> selected <?php } ?> ><?php echo $fd['name'];?></option>
                                    <?php } } ?>
                                </select>
                                        <input id="formulaValue-<?php echo $ad['id'];?>" type="textbox" class="form-control forVal" name="formula_val[<?php echo $ad['id'];?>]" value="<?php echo $formulamapdata['formula_val'];?>" style="border: 1px solid #ced4da;border-radius: 4px;height: 34px;width: 40%;font-size: 14px;" <?php if(empty($formulamapdata['formula_val'])) { ?>readonly <?php } ?>>
                            </td>
                            <td><a class="thickbox" href="fullscreen.php?q=/modules/Academics/sketch_manage_plugin.php&id=<?php echo $ad['id'];?>"><i title="Add Plugin" class="fas fa-plus-square"></i></a>   <?php echo $attrdata['pluginname'];?></td>
                        </tr>
                        
                    <?php       
                        } }   
                    ?>
                </tbody>
            </table>
            <div style="display:flex;width:100%; margin-bottom:10px;">

                <span style="font-size:14px;">Grading System : </span>
                <select name="grade_id" class="form-control" style=" width:25%;margin: 0px 10px 0 10px;">
                    <option value="">Select Grade</option>
                    <?php if(!empty($gradeData)) {
                        foreach($gradeData as $gd) { ?>
                            <option value="<?php echo $gd['id']; ?>"><?php echo $gd['name']; ?></option>
                    <?php } }?>
                </select>

                <span style="font-size:14px;">Supported Attribute : </span>
                <select name="supported_attribute" class="form-control" style=" width:25%;margin: 0px 10px 0 10px;">
                    <option value="">Select</option>
                    <?php if(!empty($suppattrdata)) {
                        foreach($suppattrdata as $adt) { ?>
                            <option value="<?php echo $adt['id']; ?>"><?php echo $adt['attribute_name']; ?></option>
                    <?php } }?>
                </select>
            </div>

            <div style="display:flex;width:100%;">

                <span style="font-size:14px;">Final Formula : </span>
                <select name="final_formula" class="form-control" style=" width:25%;margin: 0px 10px 0 25px;">
                    <option value="">AS IS</option>
                    <option value="Sum">Sum</option>
                    <option value="Average">Average</option>
                    <option value="Best_of_Sum">Best of Sum</option>
                    <option value="Best_of_Average">Best of Average</option>
                    <option value="Best_of_All">Best of All</option>
                    <!-- <option value="Sum">Sum</option>
                    <option value="Sum">Sum</option>
                    <option value="Sum">Sum</option>
                    <option value="Sum">Sum</option>
                    <option value="Sum">Sum</option>
                    <option value="Sum">Sum</option>
                    <option value="Sum">Sum</option>
                    <option value="Sum">Sum</option>
                    <option value="Sum">Sum</option> -->
                </select>

                <span style="font-size:14px;">Final Plugin : </span>
                <a style="margin: 0px 10px 0 10px;" class="thickbox" href="fullscreen.php?q=/modules/Academics/sketch_manage_plugin.php&id=<?php echo $chkdata['id'];?>"><i style="font-size: 20px;" title="Add Plugin" class="fas fa-plus-square"></i></a>
            </div>
        </div>    
        </form>
<?php        
    }

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

    $("#searchTable").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#myTable tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
</script>
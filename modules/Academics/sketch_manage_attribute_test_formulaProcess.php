<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Academics/sketch_manage_attribute.php&id='.$_POST['sketch_id'];

if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_attribute.php') == false) {
    //Acess denied
    echo "<div class='error'>"; 
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    
    $erta_id = $_POST['erta_id'];
    $test_master_id = $_POST['test_master_id'];
    $formula_id = $_POST['formula_id'];
    $formula_val = $_POST['formula_val'];
    $grade_id = $_POST['grade_id'];
    $supported_attribute = $_POST['supported_attribute'];
    $subject_type = $_POST['subject_type'];
    $subject_val_id = $_POST['subject_val_id'];
    $subject_display_type = $_POST['subject_display_type'];
    $final_formula = $_POST['final_formula'];
    $final_formula_best_cal = $_POST['final_formula_best_cal'];

    if(!empty($formula_id) && !empty($erta_id)){
        $data = array('erta_id' => $erta_id);
        $sqldel = 'DELETE FROM examinationReportTemplateFormulaAttributeMapping WHERE erta_id=:erta_id';
        $resultdel = $connection2->prepare($sqldel);
        $resultdel->execute($data);

        //foreach($test_master_id as $testmasterid){
            $testmasterid = implode(',',$test_master_id);
            $datau = array('test_master_id' => $testmasterid, 'final_formula' => $final_formula, 'final_formula_best_cal' => $final_formula_best_cal, 'grade_id' => $grade_id, 'supported_attribute' => $supported_attribute, 'subject_type' => $subject_type, 'subject_val_id' => $subject_val_id, 'subject_display_type' => $subject_display_type,  'id' => $erta_id);
            $sqlupd = 'UPDATE examinationReportTemplateAttributes SET test_master_id=:test_master_id, final_formula=:final_formula, final_formula_best_cal=:final_formula_best_cal, grade_id=:grade_id, supported_attribute=:supported_attribute, subject_type=:subject_type, subject_val_id=:subject_val_id, subject_display_type=:subject_display_type  WHERE id=:id';
            $resultupd = $connection2->prepare($sqlupd);
            $resultupd->execute($datau);


            foreach($test_master_id as $tmasterid){
                $fid = $formula_id[$tmasterid];
                $fval = $formula_val[$tmasterid];
                
                if(!empty($fid)){
                    $data1 = array('formula_id' => $fid, 'erta_id' => $erta_id, 'test_master_id' => $tmasterid, 'formula_val' => $fval);
                    $sql1 = "INSERT INTO examinationReportTemplateFormulaAttributeMapping SET formula_id=:formula_id, erta_id=:erta_id, test_master_id=:test_master_id, formula_val=:formula_val";
                    $result = $connection2->prepare($sql1);
                    $result->execute($data1);
                }
            }
        //}
        header("Location: {$URL}");  
    }

}
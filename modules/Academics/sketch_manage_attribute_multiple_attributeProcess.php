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
    $attr_id = $_POST['attr_id'];
    $formula_id = $_POST['formula_id'];
    $formula_val = $_POST['formula_val'];
    $final_formula = $_POST['final_formula'];
    $attrId =  implode(',',$attr_id);
    $grade_id = $_POST['grade_id'];
    $supported_attribute = $_POST['supported_attribute'];

    if(!empty($formula_id) && !empty($erta_id)){
        $datau = array('attr_ids' => $attrId, 'final_formula' => $final_formula, 'grade_id' => $grade_id, 'supported_attribute' => $supported_attribute, 'id' => $erta_id);
        $sqlupd = 'UPDATE examinationReportTemplateAttributes SET attr_ids=:attr_ids, final_formula=:final_formula, grade_id=:grade_id, supported_attribute=:supported_attribute  WHERE id=:id';
        $resultupd = $connection2->prepare($sqlupd);
        $resultupd->execute($datau);
        
        foreach($attr_id as $attrid){
            $data = array('erta_id' => $attrid);
            $sqldel = 'DELETE FROM examinationReportTemplateFormulaAttributeMapping WHERE erta_id=:erta_id';
            $resultdel = $connection2->prepare($sqldel);
            $resultdel->execute($data);

            $fid = $formula_id[$attrid];
            $fval = $formula_val[$attrid];
            
            if(!empty($fid)){
                $data1 = array('formula_id' => $fid, 'erta_id' => $attrid, 'formula_val' => $fval);
                $sql1 = "INSERT INTO examinationReportTemplateFormulaAttributeMapping SET formula_id=:formula_id, erta_id=:erta_id, formula_val=:formula_val";
                $result = $connection2->prepare($sql1);
                $result->execute($data1);
            }
        }
        header("Location: {$URL}");  
    }

}
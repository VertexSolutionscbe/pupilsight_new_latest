<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Academics/sketch_manage_attribute_plugin.php&id='.$_POST['sketch_id'];

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
    $formula_id = $_POST['formula_id'];
    $formula_val = $_POST['formula_val'];

    if(!empty($formula_id) && !empty($erta_id)){

        foreach($erta_id as $ertaid){

            $data = array('erta_id' => $ertaid);
            $sqldel = 'DELETE FROM examinationReportTemplateFormulaAttributeMapping WHERE erta_id=:erta_id';
            $resultdel = $connection2->prepare($sqldel);
            $resultdel->execute($data);
                
                $fid = $formula_id[$ertaid];
                $fval = $formula_val[$ertaid];
                
                if(!empty($fid)){
                    $data1 = array('formula_id' => $fid, 'erta_id' => $ertaid, 'formula_val' => $fval);
                    $sql1 = "INSERT INTO examinationReportTemplateFormulaAttributeMapping SET formula_id=:formula_id, erta_id=:erta_id, formula_val=:formula_val";
                    $result = $connection2->prepare($sql1);
                    $result->execute($data1);
                }
           
        }
        header("Location: {$URL}");  
    }

}
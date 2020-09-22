<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/ajax_transitions.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {  
    //Proceed!
    $type = $_POST['type'];
    $val = $_POST['val'];
    if($type == 'getterm'){
        $sql = 'SELECT * FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearID = '.$val.' ';
        $result = $connection2->query($sql);
        $rowdata = $result->fetchAll();
        $returndata = '<option>Select Term</option>';
        foreach($rowdata as $row){
            $returndata .= '<option value='.$row['pupilsightSchoolYearTermID'].'>'.$row['name'].'</option>';
        }
        echo $returndata;
    }

    if($type == 'gettermdaterange'){
        $sql = 'SELECT firstDay, lastDay FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearTermID = '.$val.' ';
        $result = $connection2->query($sql);
        $rowdata = $result->fetch();
        $return['firstDay'] = $rowdata['firstDay'];
        $return['lastDay'] = $rowdata['lastDay'];
        echo json_encode($return);
    }

    
    
    
}


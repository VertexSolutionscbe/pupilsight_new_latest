<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/department_manage_delete.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightDepartmentID = $_GET['pupilsightDepartmentID'];
    if ($pupilsightDepartmentID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            
            $datas = array('pupilsightDepartmentID' => $pupilsightDepartmentID);
            $sqls = 'SELECT * FROM assign_core_subjects_toclass WHERE pupilsightDepartmentID=:pupilsightDepartmentID';           
            $results = $connection2->prepare($sqls);
            $results->execute($datas);
            
            $data = array('pupilsightDepartmentID' => $pupilsightDepartmentID);
            $sql = 'SELECT * FROM pupilsightDepartment WHERE pupilsightDepartmentID=:pupilsightDepartmentID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        }  else if($results->rowCount() > 0){
            echo "<div class='error'>";
            echo __('This subject has been mapped to a class .');
            echo '</div>';

        }else {
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/department_manage_deleteProcess.php?pupilsightDepartmentID=$pupilsightDepartmentID", true);
            echo $form->getOutput();
        }
    }
}

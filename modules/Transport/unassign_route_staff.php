<?php
/*
Pupilsight, Flexible & Open School System
*/
$session = $container->get('session');
$staffids = $session->get('student_ids');
use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Transport/unassign_route_staff.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    // $form->addHiddenValue('stu_id', $staffids);
    $stu_id = explode(',', $staffids);
    
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    // $id = 28;
    if ($stu_id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        foreach($stu_id as $stu){
        try {

             $data = array('pupilsightPersonID' => $stu);
            $sql = 'SELECT * FROM trans_route_assign WHERE  pupilsightPersonID=:pupilsightPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
          
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }
    }
   // print_r($result->rowCount());die();
        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/staff_route_unassignProcess.php?pupilsightPersonID=$stu_id", true);
            echo $form->getOutput();
        }

    }
}

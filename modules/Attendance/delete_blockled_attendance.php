<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';


if (isActionAccessible($guid, $connection2, '/modules/Attendance/blocked_attendance.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
   
       
            //Proceed!
            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

           
            $pupilsightAttendanceBlockID = $_GET['pupilsightAttendanceBlockID'];
            if ($pupilsightAttendanceBlockID == '') {
                echo "<div class='alert alert-danger'>";
                echo __('You have not specified one or more required parameters.');
                echo '</div>';
            } else {
                try {
                   
                        $data = array('pupilsightAttendanceBlockID' => $pupilsightAttendanceBlockID);
                        $sql = 'SELECT * FROM pupilsightAttendanceBlocked WHERE pupilsightAttendanceBlockID=:pupilsightAttendanceBlockID';
                   
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($result->rowCount() != 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('The selected record does not exist, or you do not have access to it.');
                    echo '</div>';
                } else {
                    $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/blocked_attendancedeleteProcess.php?pupilsightAttendanceBlockID=$pupilsightAttendanceBlockID");
                    echo $form->getOutput();
                }
            }
        
   
}

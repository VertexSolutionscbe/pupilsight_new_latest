<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$pupilsightActivityID = $_GET['pupilsightActivityID'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/activities_manage_enrolment_add.php&pupilsightActivityID=$pupilsightActivityID&search=".$_GET['search']."&pupilsightSchoolYearTermID=".$_GET['pupilsightSchoolYearTermID'];

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_manage_enrolment_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $status = $_POST['status'];

    if ($pupilsightActivityID == '' or $status == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Run through each of the selected participants.
        $update = true;
        $choices = null;
        if (isset($_POST['Members'])) {
            $choices = $_POST['Members'];
        }

        if (count($choices) < 1) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            foreach ($choices as $t) {
                //Check to see if student is already registered in this activity
                try {
                    $data = array('pupilsightPersonID' => $t, 'pupilsightActivityID' => $pupilsightActivityID);
                    $sql = 'SELECT * FROM pupilsightActivityStudent WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightActivityID=:pupilsightActivityID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                //If student not in activity, add them
                if ($result->rowCount() == 0) {
                    try {
                        $data = array('pupilsightPersonID' => $t, 'pupilsightActivityID' => $pupilsightActivityID, 'status' => $status, 'timestamp' => date('Y-m-d H:i:s', time()));
                        $sql = 'INSERT INTO pupilsightActivityStudent SET pupilsightPersonID=:pupilsightPersonID, pupilsightActivityID=:pupilsightActivityID, status=:status, timestamp=:timestamp';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $update = false;
                    }

                    //Set log
                    $pupilsightModuleID = getModuleIDFromName($connection2, 'Activities') ;
                    setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], $pupilsightModuleID, $_SESSION[$guid]['pupilsightPersonID'], 'Activities - Student Added', array('pupilsightPersonIDStudent' => $t));
                }
            }
            //Write to database
            if ($update == false) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}

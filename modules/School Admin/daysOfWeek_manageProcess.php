<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/daysOfWeek_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/daysOfWeek_manage.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    //Proceed!
    try {
        $data = array();
        $sql = "SELECT * FROM pupilsightDaysOfWeek WHERE name='Monday' OR name='Tuesday' OR name='Wednesday' OR name='Thursday' OR name='Friday' OR name='Saturday' OR name='Sunday' ORDER BY sequenceNumber";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    if ($result->rowCount() != 7) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    } else {
        $valid = true;
        $unqiue = true;
        $update = true;

        while ($row = $result->fetch()) {
            $name = $row['name'];
            $sequenceNumber = $_POST[$name.'sequenceNumber'];
            $schoolDay = $_POST[$name.'schoolDay'];

            if (isset($_POST[$name.'schoolOpenH']) && isset($_POST[$name.'schoolOpenM']) && is_numeric($_POST[$name.'schoolOpenH'])) {
                $schoolOpen = $_POST[$name.'schoolOpenH'].':'.intval($_POST[$name.'schoolOpenM']).':00';
            } else {
                $schoolOpen = null;
            }

            if (isset($_POST[$name.'schoolStartH']) && isset($_POST[$name.'schoolStartM']) && is_numeric($_POST[$name.'schoolStartH'])) {
                $schoolStart = $_POST[$name.'schoolStartH'].':'.intval($_POST[$name.'schoolStartM']).':00';
            } else {
                $schoolStart = null;
            }

            if (isset($_POST[$name.'schoolEndH']) && isset($_POST[$name.'schoolEndM']) && is_numeric($_POST[$name.'schoolEndH'])) {
                $schoolEnd = $_POST[$name.'schoolEndH'].':'.intval($_POST[$name.'schoolEndM']).':00';
            } else {
                $schoolEnd = null;
            }

            if (isset($_POST[$name.'schoolCloseH']) && isset($_POST[$name.'schoolCloseM']) && is_numeric($_POST[$name.'schoolCloseH'])) {
                $schoolClose = $_POST[$name.'schoolCloseH'].':'.intval($_POST[$name.'schoolCloseM']).':00';
            } else {
                $schoolClose = null;
            }

            //Validate Inputs
            if ($sequenceNumber == '' or is_numeric($sequenceNumber) == false or ($schoolDay != 'Y' and $schoolDay != 'N')) {
                $valid = false;
            }
            // Check for invlid times for active school days
            else if ($schoolDay == 'Y' AND ($schoolOpen == null || $schoolStart == null || $schoolEnd == null || $schoolClose == null)) {
                $valid = false;
            } else {
                //Run SQL
                try {
                    $dataUpdate = array('sequenceNumber' => $sequenceNumber, 'schoolDay' => $schoolDay, 'schoolOpen' => $schoolOpen, 'schoolStart' => $schoolStart, 'schoolEnd' => $schoolEnd, 'schoolClose' => $schoolClose, 'name' => $name);
                    $sqlUpdate = 'UPDATE pupilsightDaysOfWeek SET sequenceNumber=:sequenceNumber, schoolDay=:schoolDay, schoolOpen=:schoolOpen, schoolStart=:schoolStart, schoolEnd=:schoolEnd, schoolClose=:schoolClose WHERE name=:name';
                    $resultUpdate = $connection2->prepare($sqlUpdate);
                    $resultUpdate->execute($dataUpdate);
                } catch (PDOException $e) {
                    $update = false;
                }
            }
        }

        //Deal with invalid or not unique
        if ($valid != true) {
            $URL .= '&return=warning1';
            header("Location: {$URL}");
            exit;
        } else {
            //Deal with failed update
            if ($update != true) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit;
            } else {
                $URL .= '&return=success0';
                header("Location: {$URL}");
                exit;
            }
        }
    }
}

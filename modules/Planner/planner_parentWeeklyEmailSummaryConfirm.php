<?php
/*
Pupilsight, Flexible & Open School System
*/

//Get variables
$pupilsightSchoolYearID = '';
if (isset($_GET['pupilsightSchoolYearID'])) {
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
}
$key = '';
if (isset($_GET['key'])) {
    $key = $_GET['key'];
}
$pupilsightPersonIDStudent = '';
if (isset($_GET['pupilsightPersonIDStudent'])) {
    $pupilsightPersonIDStudent = $_GET['pupilsightPersonIDStudent'];
}
$pupilsightPersonIDParent = '';
if (isset($_GET['pupilsightPersonIDParent'])) {
    $pupilsightPersonIDParent = $_GET['pupilsightPersonIDParent'];
}

//Check variables
if ($pupilsightSchoolYearID == '' or $key == '' or $pupilsightPersonIDStudent == '' or $pupilsightPersonIDParent == '') { echo "<div class='alert alert-danger'>";
    echo __('You have not specified one or more required parameters.');
    echo '</div>';
} else {
    //Check for record
    $keyReadFail = false;
    try {
        $dataKeyRead = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightPersonIDStudent' => $pupilsightPersonIDStudent, 'pupilsightPersonIDParent' => $pupilsightPersonIDParent, 'key' => $key);
        $sqlKeyRead = 'SELECT * FROM pupilsightPlannerParentWeeklyEmailSummary WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonIDStudent=:pupilsightPersonIDStudent AND pupilsightPersonIDParent=:pupilsightPersonIDParent AND `key`=:key';
        $resultKeyRead = $connection2->prepare($sqlKeyRead);
        $resultKeyRead->execute($dataKeyRead);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>";
        echo __('Your request failed due to a database error.');
        echo '</div>';
    }

    if ($resultKeyRead->rowCount() != 1) { //If not exists, report error
        echo "<div class='alert alert-danger'>";
        echo __('The selected record does not exist, or you do not have access to it.');
        echo '</div>';
    } else {    //If exists check confirmed
        $rowKeyRead = $resultKeyRead->fetch();

        if ($rowKeyRead['confirmed'] == 'Y') { //If already confirmed, report success
            echo "<div class='alert alert-sucess'>";
            echo __('Thank you for confirming receipt and reading of this email.');
            echo '</div>';
        } else { //If not confirmed, confirm
            $keyWriteFail = false;
            try {
                $dataKeyWrite = array('pupilsightPersonIDStudent' => $pupilsightPersonIDStudent, 'pupilsightPersonIDParent' => $pupilsightPersonIDParent, 'key' => $key);
                $sqlKeyWrite = "UPDATE pupilsightPlannerParentWeeklyEmailSummary SET confirmed='Y' WHERE pupilsightPersonIDStudent=:pupilsightPersonIDStudent AND pupilsightPersonIDParent=:pupilsightPersonIDParent AND `key`=:key";
                $resultKeyWrite = $connection2->prepare($sqlKeyWrite);
                $resultKeyWrite->execute($dataKeyWrite);
            } catch (PDOException $e) {
                $keyWriteFail = true;
            }

            if ($keyWriteFail == true) { //Report error
                echo "<div class='alert alert-danger'>";
                echo __('Your request failed due to a database error.');
                echo '</div>';
            } else { //Report success
                echo "<div class='alert alert-sucess'>";
                echo __('Thank you for confirming receipt and reading of this email.');
                echo '</div>';
            }
        }
    }
}

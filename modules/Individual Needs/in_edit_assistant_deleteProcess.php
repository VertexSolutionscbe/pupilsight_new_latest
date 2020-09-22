<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightPersonIDAssistant = $_GET['pupilsightPersonIDAssistant'];
$pupilsightPersonIDStudent = $_GET['pupilsightPersonIDStudent'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/in_edit.php&pupilsightPersonID=$pupilsightPersonIDStudent";

if (isActionAccessible($guid, $connection2, '/modules/Individual Needs/in_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, '/modules/Individual Needs/in_edit.php', $connection2);
    if ($highestAction == false) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
    } else {
        if ($highestAction != 'Individual Needs Records_viewEdit') {
            $URL .= '&return=error0';
            header("Location: {$URL}");
        } else {
            if ($pupilsightPersonIDAssistant == '' or $pupilsightPersonIDStudent == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                try {
                    $data = array('pupilsightPersonIDStudent' => $pupilsightPersonIDStudent, 'pupilsightPersonIDAssistant' => $pupilsightPersonIDAssistant);
                    $sql = 'SELECT * FROM pupilsightINAssistant WHERE pupilsightPersonIDStudent=:pupilsightPersonIDStudent AND pupilsightPersonIDAssistant=:pupilsightPersonIDAssistant';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() < 1) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightPersonIDStudent' => $pupilsightPersonIDStudent, 'pupilsightPersonIDAssistant' => $pupilsightPersonIDAssistant);
                        $sql = 'DELETE FROM pupilsightINAssistant WHERE pupilsightPersonIDStudent=:pupilsightPersonIDStudent AND pupilsightPersonIDAssistant=:pupilsightPersonIDAssistant';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}

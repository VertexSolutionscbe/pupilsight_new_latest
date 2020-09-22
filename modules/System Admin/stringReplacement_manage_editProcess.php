<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightStringID = $_GET['pupilsightStringID'];
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/stringReplacement_manage_edit.php&pupilsightStringID=$pupilsightStringID&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/System Admin/stringReplacement_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    if ($pupilsightStringID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightStringID' => $pupilsightStringID);
            $sql = 'SELECT * FROM pupilsightString WHERE pupilsightStringID=:pupilsightStringID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            //Validate Inputs
            $original = $_POST['original'];
            $replacement = $_POST['replacement'];
            $mode = $_POST['mode'];
            $caseSensitive = $_POST['caseSensitive'];
            $priority = $_POST['priority'];

            if ($original == '' or $replacement == '' or $mode == '' or $caseSensitive == '' or $priority == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('original' => $original, 'replacement' => $replacement, 'mode' => $mode, 'caseSensitive' => $caseSensitive, 'priority' => $priority, 'pupilsightStringID' => $pupilsightStringID);
                    $sql = 'UPDATE pupilsightString SET original=:original, replacement=:replacement, mode=:mode, caseSensitive=:caseSensitive, priority=:priority WHERE pupilsightStringID=:pupilsightStringID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Update string list in session & clear cache to force reload
                $pupilsight->locale->setStringReplacementList($pdo, true);
                $_SESSION[$guid]['pageLoads'] = null;

                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}

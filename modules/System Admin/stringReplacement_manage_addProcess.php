<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/stringReplacement_manage_add.php&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/System Admin/stringReplacement_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $original = $_POST['original'];
    $replacement = $_POST['replacement'];
    $mode = $_POST['mode'];
    $caseSensitive = $_POST['caseSensitive'];
    $priority = $_POST['priority'];

    //Validate Inputs
    if ($original == '' or $replacement == '' or $mode == '' or $caseSensitive == '' or $priority == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Write to database
        try {
            $data = array('original' => $original, 'replacement' => $replacement, 'mode' => $mode, 'caseSensitive' => $caseSensitive, 'priority' => $priority);
            $sql = 'INSERT INTO pupilsightString SET original=:original, replacement=:replacement, mode=:mode, caseSensitive=:caseSensitive, priority=:priority';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Last insert ID
        $AI = str_pad($connection2->lastInsertID(), 8, '0', STR_PAD_LEFT);

        //Update string list in session & clear cache to force reload
        $pupilsight->locale->setStringReplacementList($pdo, true);
        $_SESSION[$guid]['pageLoads'] = null;

        //Success 0
        $URL .= "&return=success0&editID=$AI";
        header("Location: {$URL}");
    }
}

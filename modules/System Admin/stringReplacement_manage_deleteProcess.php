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
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/stringReplacement_manage_delete.php&pupilsightStringID=$pupilsightStringID&search=$search";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/stringReplacement_manage.php&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/System Admin/stringReplacement_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
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
            //Write to database
            try {
                $data = array('pupilsightStringID' => $pupilsightStringID);
                $sql = 'DELETE FROM pupilsightString WHERE pupilsightStringID=:pupilsightStringID';
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

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}

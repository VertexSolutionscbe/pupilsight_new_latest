<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$orphaned = '';
if (isset($_GET['orphaned'])) {
    if ($_GET['orphaned'] == 'true') {
        $orphaned = 'true';
    }
}

$pupilsightThemeID = $_GET['pupilsightThemeID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/theme_manage_uninstall.php&pupilsightThemeID='.$pupilsightThemeID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/theme_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/theme_manage_uninstall.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if role specified
    if ($pupilsightThemeID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightThemeID' => $pupilsightThemeID);
            $sql = "SELECT * FROM pupilsightTheme WHERE pupilsightThemeID=:pupilsightThemeID AND active='N'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }
        if ($result->rowCount() != 1) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //Remove theme
            try {
                $dataDelete = array('pupilsightThemeID' => $pupilsightThemeID);
                $sqlDelete = 'DELETE FROM pupilsightTheme WHERE pupilsightThemeID=:pupilsightThemeID';
                $resultDelete = $connection2->prepare($sqlDelete);
                $resultDelete->execute($dataDelete);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            if ($orphaned != 'true') {
                $URLDelete = $URLDelete.'&return=success0';
            } else {
                $URLDelete = $URLDelete.'&return=warning0';
            }
            header("Location: {$URLDelete}");
        }
    }
}

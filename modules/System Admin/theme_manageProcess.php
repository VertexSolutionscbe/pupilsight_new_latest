<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightThemeID = $_POST['pupilsightThemeID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/theme_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/theme_manage.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if theme specified
    if ($pupilsightThemeID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightThemeID' => $pupilsightThemeID);
            $sql = 'SELECT * FROM pupilsightTheme WHERE pupilsightThemeID=:pupilsightThemeID';
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
            //Deactivate all themes
            try {
                $data = array();
                $sql = "UPDATE pupilsightTheme SET active='N'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
                exit();
            }

            //Write to database
            try {
                $data = array('pupilsightThemeID' => $pupilsightThemeID);
                $sql = "UPDATE pupilsightTheme SET active='Y' WHERE pupilsightThemeID=:pupilsightThemeID ";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $_SESSION[$guid]['pageLoads'] = null;
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}

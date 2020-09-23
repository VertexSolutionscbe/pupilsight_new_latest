<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsighti18nID = $_POST['pupilsighti18nID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/i18n_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/i18n_manage.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if theme specified
    if ($pupilsighti18nID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsighti18nID' => $pupilsighti18nID);
            $sql = 'SELECT * FROM pupilsighti18n WHERE pupilsighti18nID=:pupilsighti18nID';
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
            //Update session variables
            $row = $result->fetch();
            setLanguageSession($guid, $row);

            //Deactivate all languages
            try {
                $data = array();
                $sql = "UPDATE pupilsighti18n SET systemDefault='N'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error1';
                header("Location: {$URL}");
                exit();
            }

            //Write to database
            try {
                $data = array('pupilsighti18nID' => $pupilsighti18nID);
                $sql = "UPDATE pupilsighti18n SET systemDefault='Y' WHERE pupilsighti18nID=:pupilsighti18nID ";
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

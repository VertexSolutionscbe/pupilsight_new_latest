<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightModuleID = $_GET['pupilsightModuleID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/module_manage_edit.php&pupilsightModuleID='.$pupilsightModuleID;

if (isActionAccessible($guid, $connection2, '/modules/System Admin/module_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if role specified
    if ($pupilsightModuleID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightModuleID' => $pupilsightModuleID);
            $sql = 'SELECT * FROM pupilsightModule WHERE pupilsightModuleID=:pupilsightModuleID';
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
            $category = $_POST['category'];
            $active = $_POST['active'];

            if ($category == '' or $active == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('category' => $category, 'active' => $active, 'pupilsightModuleID' => $pupilsightModuleID);
                    $sql = 'UPDATE pupilsightModule SET category=:category, active=:active WHERE pupilsightModuleID=:pupilsightModuleID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Reset cache to force top-menu reload
                $_SESSION[$guid]['pageLoads'] = null;

                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}

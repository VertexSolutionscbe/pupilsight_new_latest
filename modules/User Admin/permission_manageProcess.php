<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightModuleID = isset($_POST['pupilsightModuleID'])? $_POST['pupilsightModuleID'] : '';
$pupilsightRoleID = isset($_POST['pupilsightRoleID'])? $_POST['pupilsightRoleID'] : '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/permission_manage.php&pupilsightModuleID='.$pupilsightModuleID.'&pupilsightRoleID='.$pupilsightRoleID;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/permission_manage.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    $permissions = isset($_POST['permission'])? $_POST['permission'] : array();
    $totalCount = isset($_POST['totalCount'])? $_POST['totalCount'] : array();
    $maxInputVars = ini_get('max_input_vars');

    if (empty($totalCount)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    } else if (is_null($maxInputVars) != false && $maxInputVars <= count($_POST, COUNT_RECURSIVE)) {
        $URL .= '&return=error3';
        header("Location: {$URL}");
        exit;
    } else {
        $data = array();

        if (empty($pupilsightModuleID) && empty($pupilsightRoleID)) {
            $sql = "TRUNCATE TABLE pupilsightPermission";
        } else {
            $where = array();

            if (!empty($pupilsightModuleID)) {
                $data['pupilsightModuleID'] = $pupilsightModuleID;
                $where[] = "pupilsightAction.pupilsightModuleID=:pupilsightModuleID";
            }

            if (!empty($pupilsightRoleID)) {
                $data['pupilsightRoleID'] = $pupilsightRoleID;
                $where[] = "pupilsightPermission.pupilsightRoleID=:pupilsightRoleID";
            }

            $sql = "DELETE pupilsightPermission 
                    FROM pupilsightPermission 
                    JOIN pupilsightAction ON (pupilsightPermission.pupilsightActionID=pupilsightAction.pupilsightActionID) 
                    WHERE ".implode(' AND ', $where);
        }

        try {
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        $insertFail = false;
        foreach ($permissions as $pupilsightActionID => $roles) {
            if (empty($roles)) continue;

            foreach ($roles as $pupilsightRoleID => $checked) {
                if ($checked != 'on') continue;

                try {
                    $data = array('pupilsightActionID' => $pupilsightActionID, 'pupilsightRoleID' => $pupilsightRoleID);
                    $sql = 'INSERT INTO pupilsightPermission SET pupilsightActionID=:pupilsightActionID, pupilsightRoleID=:pupilsightRoleID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $insertFail = true;
                }
            }
        }

        if ($insertFail == true) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit;
        } else {
            $_SESSION[$guid]['pageLoads'] = null;

            //Success0
            $URL .= '&return=success0';
            header("Location: {$URL}");
            exit;
        }
    }
}

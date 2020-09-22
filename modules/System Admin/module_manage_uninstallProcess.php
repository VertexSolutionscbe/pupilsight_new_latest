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

$pupilsightModuleID = $_GET['pupilsightModuleID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/module_manage_uninstall.php&pupilsightModuleID='.$pupilsightModuleID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/module_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/module_manage_uninstall.php') == false) {
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
            $row = $result->fetch();
            $module = $row['name'];
            $partialFail = false;

            //Check for tables and views to remove, and remove them
            $tables = null;
            if (isset($_POST['remove'])) {
                $tables = $_POST['remove'];
            }
            if (is_array($tables)) {
                if (count($tables) > 0) {
                    foreach ($tables as $table) {
                        $type = null;
                        $name = null;
                        if (substr($table, 0, 5) == 'Table') {
                            $type = 'TABLE';
                            $name = substr($table, 6);
                        } elseif (substr($table, 0, 4) == 'View') {
                            $type = 'VIEW';
                            $name = substr($table, 5);
                        }
                        if ($type != null and $name != null) {
                            try {
                                $dataDelete = array();
                                $sqlDelete = "DROP $type $name";
                                $resultDelete = $connection2->prepare($sqlDelete);
                                $resultDelete->execute($dataDelete);
                            } catch (PDOException $e) {
                                echo $e->getMessage().'<br/><br/>';
                                $partialFail = true;
                            }
                        }
                    }
                }
            }

            //Get actions to remove permissions
            try {
                $data = array('pupilsightModuleID' => $pupilsightModuleID);
                $sql = 'SELECT * FROM pupilsightAction WHERE pupilsightModuleID=:pupilsightModuleID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            while ($row = $result->fetch()) {
                //Remove permissions
                try {
                    $dataDelete = array('pupilsightActionID' => $row['pupilsightActionID']);
                    $sqlDelete = 'DELETE FROM pupilsightPermission WHERE pupilsightActionID=:pupilsightActionID';
                    $resultDelete = $connection2->prepare($sqlDelete);
                    $resultDelete->execute($dataDelete);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
            }

            //Remove actions
            try {
                $dataDelete = array('pupilsightModuleID' => $pupilsightModuleID);
                $sqlDelete = 'DELETE FROM pupilsightAction WHERE pupilsightModuleID=:pupilsightModuleID';
                $resultDelete = $connection2->prepare($sqlDelete);
                $resultDelete->execute($dataDelete);
            } catch (PDOException $e) {
                $partialFail = true;
            }

            //Remove module
            try {
                $dataDelete = array('pupilsightModuleID' => $pupilsightModuleID);
                $sqlDelete = 'DELETE FROM pupilsightModule WHERE pupilsightModuleID=:pupilsightModuleID';
                $resultDelete = $connection2->prepare($sqlDelete);
                $resultDelete->execute($dataDelete);
            } catch (PDOException $e) {
                $partialFail = true;
            }

            //Remove hooks
            try {
                $dataDelete = array('pupilsightModuleID' => $pupilsightModuleID);
                $sqlDelete = 'DELETE FROM pupilsightHook WHERE pupilsightModuleID=:pupilsightModuleID';
                $resultDelete = $connection2->prepare($sqlDelete);
                $resultDelete->execute($dataDelete);
            } catch (PDOException $e) {
                $partialFail = true;
            }

            //Remove settings
            try {
                $dataDelete = array('scope' => $module);
                $sqlDelete = 'DELETE FROM pupilsightSetting WHERE scope=:scope';
                $resultDelete = $connection2->prepare($sqlDelete);
                $resultDelete->execute($dataDelete);
            } catch (PDOException $e) {
                $partialFail = true;
            }

            if ($partialFail == true) {
                $URL .= '&return=warning2';
                header("Location: {$URL}");
            } else {
                // Clear the main menu from session cache
                $pupilsight->session->forget('menuMainItems');

                if ($orphaned != 'true') {
                    $URLDelete = $URLDelete.'&return=warning0';
                } else {
                    $URLDelete = $URLDelete.'&return=success0';
                }
                header("Location: {$URLDelete}");
            }
        }
    }
}

<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightModuleID = $_GET['pupilsightModuleID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/module_manage_update.php&pupilsightModuleID='.$pupilsightModuleID;
$_SESSION[$guid]['moduleUpdateError'] = '';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/module_manage_update.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if role specified
    if ($pupilsightModuleID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //NAMED
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

            $versionDB = $_POST['versionDB'];
            $versionCode = $_POST['versionCode'];

            //Validate Inputs
            if ($versionDB == '' or $versionCode == '' or version_compare($versionDB, $versionCode) != -1) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                include $_SESSION[$guid]['absolutePath'].'/modules/'.$row['name'].'/CHANGEDB.php';

                $partialFail = false;
                foreach ($sql as $version) {
                    if (version_compare($version[0], $versionDB, '>') and version_compare($version[0], $versionCode, '<=')) {
                        $sqlTokens = explode(';end', $version[1]);
                        foreach ($sqlTokens as $sqlToken) {
                            if (trim($sqlToken) != '') {
                                try {
                                    $result = $connection2->query($sqlToken);
                                } catch (PDOException $e) {
                                    $_SESSION[$guid]['moduleUpdateError'] .= htmlPrep($sqlToken).'<br/><b>'.$e->getMessage().'</b><br/><br/>';
                                    $partialFail = true;
                                }
                            }
                        }
                    }
                }

                if ($partialFail == true) {
                    $URL .= '&return=warning1';
                    header("Location: {$URL}");
                } else {
                    //Update DB version
                    try {
                        $data = array('versionCode' => $versionCode, 'name' => $row['name']);
                        $sql = 'UPDATE pupilsightModule SET version=:versionCode WHERE name=:name';
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

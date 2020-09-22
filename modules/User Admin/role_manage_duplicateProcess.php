<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightRoleID = $_GET['pupilsightRoleID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/role_manage.php&pupilsightRoleID=$pupilsightRoleID";

if (isActionAccessible($guid, $connection2, '/modules/User Admin/role_manage_duplicate.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $name = $_POST['name'];
    $nameShort = $_POST['nameShort'];

    if ($pupilsightRoleID == '' or $name == '' or $nameShort == '') {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Lock table
        try {
            $sql = 'LOCK TABLE pupilsightRole WRITE, pupilsightPermission WRITE';
            $result = $connection2->query($sql);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Get next autoincrement for unit
        try {
            $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightRole'";
            $resultAI = $connection2->query($sqlAI);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        $rowAI = $resultAI->fetch();
        $AI = str_pad($rowAI['Auto_increment'], 8, '0', STR_PAD_LEFT);
        $partialFail = false;

        if ($AI == '') {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('pupilsightRoleID' => $pupilsightRoleID);
                $sql = 'SELECT * FROM pupilsightRole WHERE pupilsightRoleID=:pupilsightRoleID';
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
                try {
                    $data = array('pupilsightRoleID' => $AI, 'category' => $row['category'], 'name' => $name, 'nameShort' => $nameShort, 'description' => $row['description'], 'canLoginRole' => $row['canLoginRole'], 'futureYearsLogin' => $row['futureYearsLogin'], 'pastYearsLogin' => $row['pastYearsLogin'], 'restriction' => $row['restriction']);
                    $sql = "INSERT INTO pupilsightRole SET pupilsightRoleID=:pupilsightRoleID, category=:category, name=:name, nameShort=:nameShort, description=:description, type='Additional', canLoginRole=:canLoginRole, futureYearsLogin=:futureYearsLogin, pastYearsLogin=:pastYearsLogin, restriction=:restriction";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Duplicate permissions
                try {
                    $dataPermissions = array('pupilsightRoleID' => $pupilsightRoleID);
                    $sqlPermissions = 'SELECT * FROM pupilsightPermission WHERE pupilsightRoleID=:pupilsightRoleID';
                    $resultPermissions = $connection2->prepare($sqlPermissions);
                    $resultPermissions->execute($dataPermissions);
                } catch (PDOException $e) {
                    $partialFail = true;
                    echo $e->getMessage();
                }

                while ($rowPermissions = $resultPermissions->fetch()) {
                    $copyOK = true;
                    try {
                        $dataCopy = array('pupilsightRoleID' => $AI, 'pupilsightActionID' => $rowPermissions['pupilsightActionID']);
                        $sqlCopy = 'INSERT INTO pupilsightPermission SET pupilsightRoleID=:pupilsightRoleID, pupilsightActionID=:pupilsightActionID';
                        $resultCopy = $connection2->prepare($sqlCopy);
                        $resultCopy->execute($dataCopy);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                }

                //Unlock locked database tables
                try {
                    $sql = 'UNLOCK TABLES';
                    $result = $connection2->query($sql);
                } catch (PDOException $e) {
                }

                if ($partialFail == true) {
                    $URL .= '&return=error6';
                    header("Location: {$URL}");
                } else {
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}

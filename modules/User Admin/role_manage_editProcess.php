<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightRoleID = $_GET['pupilsightRoleID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/role_manage_edit.php&pupilsightRoleID='.$pupilsightRoleID;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/role_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if role specified
    if ($pupilsightRoleID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
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
            $values = $result->fetch();

            //Validate Inputs
            $category = $_POST['category'];
            $name = $_POST['name'];
            $nameShort = $_POST['nameShort'];
            $description = $_POST['description'];
            $canLoginRole = isset($_POST['canLoginRole'])? $_POST['canLoginRole'] : 'Y';
            $futureYearsLogin = isset($_POST['futureYearsLogin'])? $_POST['futureYearsLogin'] : $values['futureYearsLogin'];
            $pastYearsLogin = isset($_POST['pastYearsLogin'])? $_POST['pastYearsLogin'] : $values['pastYearsLogin'];
            $restriction = $_POST['restriction'];

            if (empty($category) or empty($name) or empty($nameShort) or empty($description) or empty($futureYearsLogin) or empty($pastYearsLogin) or empty($restriction) ) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'nameShort' => $nameShort, 'pupilsightRoleID' => $pupilsightRoleID);
                    $sql = 'SELECT * FROM pupilsightRole WHERE (name=:name OR nameShort=:nameShort) AND NOT pupilsightRoleID=:pupilsightRoleID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('category' => $category, 'name' => $name, 'nameShort' => $nameShort, 'description' => $description, 'canLoginRole' => $canLoginRole, 'futureYearsLogin' => $futureYearsLogin, 'pastYearsLogin' => $pastYearsLogin, 'restriction' => $restriction, 'pupilsightRoleID' => $pupilsightRoleID);
                        $sql = 'UPDATE pupilsightRole SET category=:category, name=:name, nameShort=:nameShort, description=:description, canLoginRole=:canLoginRole, futureYearsLogin=:futureYearsLogin, pastYearsLogin=:pastYearsLogin, restriction=:restriction WHERE pupilsightRoleID=:pupilsightRoleID';
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

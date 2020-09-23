<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/role_manage_add.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/role_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $category = $_POST['category'];
    $name = $_POST['name'];
    $nameShort = $_POST['nameShort'];
    $description = $_POST['description'];
    $canLoginRole = isset($_POST['canLoginRole'])? $_POST['canLoginRole'] : 'Y';
    $futureYearsLogin = isset($_POST['futureYearsLogin'])? $_POST['futureYearsLogin'] : 'N';
    $pastYearsLogin = isset($_POST['pastYearsLogin'])? $_POST['pastYearsLogin'] : 'N';
    $restriction = $_POST['restriction'];

    if (empty($category) or empty($name) or empty($nameShort) or empty($description) or empty($futureYearsLogin) or empty($pastYearsLogin) or empty($restriction) ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniqueness
        try {
            $data = array('name' => $name, 'nameShort' => $nameShort);
            $sql = 'SELECT * FROM pupilsightRole WHERE name=:name OR nameShort=:nameShort';
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
                $data = array('category' => $category, 'name' => $name, 'nameShort' => $nameShort, 'description' => $description, 'canLoginRole' => $canLoginRole, 'futureYearsLogin' => $futureYearsLogin, 'pastYearsLogin' => $pastYearsLogin, 'restriction' => $restriction);
                $sql = "INSERT INTO pupilsightRole SET category=:category, name=:name, nameShort=:nameShort, description=:description, type='Additional', canLoginRole=:canLoginRole, futureYearsLogin=:futureYearsLogin, pastYearsLogin=:pastYearsLogin, restriction=:restriction";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}

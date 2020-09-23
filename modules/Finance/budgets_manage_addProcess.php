<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/budgets_manage_add.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/budgets_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $name = $_POST['name'];
    $nameShort = $_POST['nameShort'];
    $active = $_POST['active'];
    $category = $_POST['category'];

    //Lock table
    try {
        $sql = 'LOCK TABLES pupilsightFinanceBudget WRITE, pupilsightFinanceBudgetPerson WRITE';
        $result = $connection2->query($sql);
    } catch (PDOException $e) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit();
    }

    //Get next autoincrement
    try {
        $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightFinanceBudget'";
        $resultAI = $connection2->query($sqlAI);
    } catch (PDOException $e) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit();
    }

    $rowAI = $resultAI->fetch();
    $AI = str_pad($rowAI['Auto_increment'], 4, '0', STR_PAD_LEFT);

    if ($name == '' or $nameShort == '' or $active == '' or $category == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check for uniqueness
        try {
            $data = array('name' => $name, 'nameShort' => $nameShort);
            $sql = 'SELECT * FROM pupilsightFinanceBudget WHERE name=:name OR nameShort=:nameShort';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() > 0) {
            $URL .= '&return=error7';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('name' => $name, 'nameShort' => $nameShort, 'active' => $active, 'category' => $category, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "INSERT INTO pupilsightFinanceBudget SET name=:name, nameShort=:nameShort, active=:active, category=:category, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, timestampCreator='".date('Y-m-d H:i:s')."'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Scan through staff
            $partialFail = false;
            $staff = array();
            if (isset($_POST['staff'])) {
                $staff = $_POST['staff'];
            }
            $access = $_POST['access'];
            if ($access != 'Full' and $access != 'Write' and $access != 'Read') {
                $role = 'Read';
            }
            if (count($staff) > 0) {
                foreach ($staff as $t) {
                    //Check to see if person is already registered in this budget
                    try {
                        $dataGuest = array('pupilsightPersonID' => $t, 'pupilsightFinanceBudgetID' => $AI);
                        $sqlGuest = 'SELECT * FROM pupilsightFinanceBudgetPerson WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID';
                        $resultGuest = $connection2->prepare($sqlGuest);
                        $resultGuest->execute($dataGuest);
                    } catch (PDOException $e) {
                        echo "<div class='error'>".$e->getMessage().'</div>';
                    }

                    if ($resultGuest->rowCount() == 0) {
                        try {
                            $data = array('pupilsightPersonID' => $t, 'pupilsightFinanceBudgetID' => $AI, 'access' => $access);
                            $sql = 'INSERT INTO pupilsightFinanceBudgetPerson SET pupilsightPersonID=:pupilsightPersonID, pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID, access=:access';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }
                }
            }

            try {
                $sql = 'UNLOCK TABLES';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
            }

            if ($partialFail == true) {
                $URL .= '&return=warning1';
                header("Location: {$URL}");
            } else {
                $URL .= "&return=success0&editID=$AI";
                header("Location: {$URL}");
            }
        }
    }
}

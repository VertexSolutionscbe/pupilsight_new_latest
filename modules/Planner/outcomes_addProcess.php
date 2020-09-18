<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

$filter2 = '';
if (isset($_GET['filter2'])) {
    $filter2 = $_GET['filter2'];
}

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/outcomes_add.php&filter2=$filter2";

if (isActionAccessible($guid, $connection2, '/modules/Planner/outcomes_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
    } else {
        if ($highestAction != 'Manage Outcomes_viewEditAll' and $highestAction != 'Manage Outcomes_viewAllEditLearningArea') {
            $URL .= '&return=error0';
            header("Location: {$URL}");
        } else {
            //Proceed!
            $scope = $_POST['scope'];
            if ($scope == 'Learning Area') {
                $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
            } else {
                $pupilsightDepartmentID = null;
            }
            $name = $_POST['name'];
            $nameShort = $_POST['nameShort'];
            $active = $_POST['active'];
            $category = $_POST['category'];
            $description = $_POST['description'];
            $pupilsightYearGroupIDList = isset($_POST['pupilsightYearGroupIDList'])? $_POST['pupilsightYearGroupIDList'] : array();
            $pupilsightYearGroupIDList = implode(',', $pupilsightYearGroupIDList);

            if ($scope == '' or ($scope == 'Learning Area' and $pupilsightDepartmentID == '') or $name == '' or $nameShort == '' or $active == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('scope' => $scope, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'name' => $name, 'nameShort' => $nameShort, 'active' => $active, 'category' => $category, 'description' => $description, 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = 'INSERT INTO pupilsightOutcome SET scope=:scope, pupilsightDepartmentID=:pupilsightDepartmentID, name=:name, nameShort=:nameShort, active=:active, category=:category, description=:description, pupilsightYearGroupIDList=:pupilsightYearGroupIDList, pupilsightPersonIDCreator=:pupilsightPersonIDCreator';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Last insert ID
                $AI = str_pad($connection2->lastInsertID(), 8, '0', STR_PAD_LEFT);

                $URL .= '&return=success0&editID='.$AI;
                header("Location: {$URL}");
            }
        }
    }
}

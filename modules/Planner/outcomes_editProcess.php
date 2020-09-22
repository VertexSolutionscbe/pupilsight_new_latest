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

$pupilsightOutcomeID = $_GET['pupilsightOutcomeID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/outcomes_edit.php&pupilsightOutcomeID=$pupilsightOutcomeID&filter2=$filter2";

if (isActionAccessible($guid, $connection2, '/modules/Planner/outcomes_edit.php') == false) {
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
            //Check if school year specified
            if ($pupilsightOutcomeID == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                try {
                    if ($highestAction == 'Manage Outcomes_viewEditAll') {
                        $data = array('pupilsightOutcomeID' => $pupilsightOutcomeID);
                        $sql = 'SELECT * FROM pupilsightOutcome WHERE pupilsightOutcomeID=:pupilsightOutcomeID';
                    } elseif ($highestAction == 'Manage Outcomes_viewAllEditLearningArea') {
                        $data = array('pupilsightOutcomeID' => $pupilsightOutcomeID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = "SELECT * FROM pupilsightOutcome JOIN pupilsightDepartment ON (pupilsightOutcome.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) AND NOT pupilsightOutcome.pupilsightDepartmentID IS NULL WHERE pupilsightOutcomeID=:pupilsightOutcomeID AND (role='Coordinator' OR role='Teacher (Curriculum)') AND pupilsightPersonID=:pupilsightPersonID AND scope='Learning Area'";
                    }
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
                        $URL .= '&return=error3';
                        header("Location: {$URL}");
                    } else {
                        //Write to database
                        try {
                            $data = array('scope' => $scope, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'name' => $name, 'nameShort' => $nameShort, 'active' => $active, 'category' => $category, 'description' => $description, 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList, 'pupilsightOutcomeID' => $pupilsightOutcomeID);
                            $sql = 'UPDATE pupilsightOutcome SET scope=:scope, pupilsightDepartmentID=:pupilsightDepartmentID, name=:name, nameShort=:nameShort, active=:active, category=:category, description=:description, pupilsightYearGroupIDList=:pupilsightYearGroupIDList WHERE pupilsightOutcomeID=:pupilsightOutcomeID';
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
}

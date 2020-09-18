<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

//Search & Filters
$search = null;
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}
$filter2 = null;
if (isset($_GET['filter2'])) {
    $filter2 = $_GET['filter2'];
}

$pupilsightRubricID = $_GET['pupilsightRubricID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/rubrics_edit.php&pupilsightRubricID=$pupilsightRubricID&sidebar=false&search=$search&filter2=$filter2";

if (isActionAccessible($guid, $connection2, '/modules/Rubrics/rubrics_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
    } else {
        if ($highestAction != 'Manage Rubrics_viewEditAll' and $highestAction != 'Manage Rubrics_viewAllEditLearningArea') {
            $URL .= '&return=error0';
            header("Location: {$URL}");
        } else {
            //Proceed!
            //Check if school year specified
            if ($pupilsightRubricID == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                try {
                    if ($highestAction == 'Manage Rubrics_viewEditAll') {
                        $data = array('pupilsightRubricID' => $pupilsightRubricID);
                        $sql = 'SELECT * FROM pupilsightRubric WHERE pupilsightRubricID=:pupilsightRubricID';
                    } elseif ($highestAction == 'Manage Rubrics_viewAllEditLearningArea') {
                        $data = array('pupilsightRubricID' => $pupilsightRubricID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = "SELECT * FROM pupilsightRubric JOIN pupilsightDepartment ON (pupilsightRubric.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) AND NOT pupilsightRubric.pupilsightDepartmentID IS NULL WHERE pupilsightRubricID=:pupilsightRubricID AND (role='Coordinator' OR role='Teacher (Curriculum)') AND pupilsightPersonID=:pupilsightPersonID AND scope='Learning Area'";
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
                    $pupilsightDepartmentID = null;
                    if ($scope == 'Learning Area') {
                        $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
                    }
                    $name = $_POST['name'];
                    $active = $_POST['active'];
                    $category = $_POST['category'];
                    $description = $_POST['description'];
                    $pupilsightYearGroupIDList = isset($_POST['pupilsightYearGroupIDList']) ? implode(',', $_POST['pupilsightYearGroupIDList']) : '';
                    $pupilsightScaleID = null;
                    if (isset($_POST['pupilsightScaleID'])) {
                        if ($_POST['pupilsightScaleID'] != '') {
                            $pupilsightScaleID = $_POST['pupilsightScaleID'];
                        }
                    }

                    if ($scope == '' or ($scope == 'Learning Area' and $pupilsightDepartmentID == '') or $name == '' or $active == '') {
                        $URL .= '&return=error3';
                        header("Location: {$URL}");
                    } else {
                        //Write to database
                        try {
                            $data = array('scope' => $scope, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'name' => $name, 'active' => $active, 'category' => $category, 'description' => $description, 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList, 'pupilsightScaleID' => $pupilsightScaleID, 'pupilsightRubricID' => $pupilsightRubricID);
                            $sql = 'UPDATE pupilsightRubric SET scope=:scope, pupilsightDepartmentID=:pupilsightDepartmentID, name=:name, active=:active, category=:category, description=:description, pupilsightYearGroupIDList=:pupilsightYearGroupIDList, pupilsightScaleID=:pupilsightScaleID WHERE pupilsightRubricID=:pupilsightRubricID';
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

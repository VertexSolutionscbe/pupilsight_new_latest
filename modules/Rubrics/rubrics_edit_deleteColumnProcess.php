<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

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
$pupilsightRubricColumnID = $_GET['pupilsightRubricColumnID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/rubrics_edit.php&pupilsightRubricID=$pupilsightRubricID&sidebar=false&search=$search&filter2=$filter2";

if (isActionAccessible($guid, $connection2, '/modules/Rubrics/rubrics_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['address'], $connection2);
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
            if ($pupilsightRubricID == '' or $pupilsightRubricColumnID == '') {
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
                    //Check for existence and association of column
                    try {
                        $dataColumn = array('pupilsightRubricID' => $pupilsightRubricID, 'pupilsightRubricColumnID' => $pupilsightRubricColumnID);
                        $sqlColumn = 'SELECT * FROM pupilsightRubric JOIN pupilsightRubricColumn ON (pupilsightRubricColumn.pupilsightRubricID=pupilsightRubric.pupilsightRubricID) WHERE pupilsightRubricColumn.pupilsightRubricID=:pupilsightRubricID AND pupilsightRubricColumnID=:pupilsightRubricColumnID';
                        $resultColumn = $connection2->prepare($sqlColumn);
                        $resultColumn->execute($dataColumn);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    if ($resultColumn->rowCount() != 1) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                    } else {
                        //Combined delete of column and cells
                        try {
                            $data = array('pupilsightRubricID' => $pupilsightRubricID, 'pupilsightRubricColumnID' => $pupilsightRubricColumnID);
                            $sql = 'DELETE FROM pupilsightRubricColumn WHERE pupilsightRubricColumn.pupilsightRubricID=:pupilsightRubricID AND pupilsightRubricColumn.pupilsightRubricColumnID=:pupilsightRubricColumnID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        try {
                            $data = array('pupilsightRubricID' => $pupilsightRubricID, 'pupilsightRubricColumnID' => $pupilsightRubricColumnID);
                            $sql = 'DELETE FROM pupilsightRubricCell WHERE pupilsightRubricCell.pupilsightRubricID=:pupilsightRubricID AND pupilsightRubricCell.pupilsightRubricColumnID=:pupilsightRubricColumnID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                        }

                        $URL .= '&return=success0';
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}

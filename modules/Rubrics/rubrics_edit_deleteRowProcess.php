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
$pupilsightRubricRowID = $_GET['pupilsightRubricRowID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/rubrics_edit.php&pupilsightRubricID=$pupilsightRubricID&sidebar=false&search=$search&filter2=$filter2";

if (isActionAccessible($guid, $connection2, '/modules/Rubrics/rubrics_edit.php') == false) {
    $URL .= '&&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['address'], $connection2);
    if ($highestAction == false) {
        $URL .= '&&return=error2';
        header("Location: {$URL}");
    } else {
        if ($highestAction != 'Manage Rubrics_viewEditAll' and $highestAction != 'Manage Rubrics_viewAllEditLearningArea') {
            $URL .= '&&return=error0';
            header("Location: {$URL}");
        } else {
            //Proceed!
            //Check if school year specified
            if ($pupilsightRubricID == '' or $pupilsightRubricRowID == '') {
                $URL .= '&&return=error1';
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
                    $URL .= '&columnDeleteReturn=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() != 1) {
                    $URL .= '&&return=error2';
                    header("Location: {$URL}");
                } else {
                    //Check for existence and association of row
                    try {
                        $dataRow = array('pupilsightRubricID' => $pupilsightRubricID, 'pupilsightRubricRowID' => $pupilsightRubricRowID);
                        $sqlRow = 'SELECT * FROM pupilsightRubric JOIN pupilsightRubricRow ON (pupilsightRubricRow.pupilsightRubricID=pupilsightRubric.pupilsightRubricID) WHERE pupilsightRubricRow.pupilsightRubricID=:pupilsightRubricID AND pupilsightRubricRowID=:pupilsightRubricRowID';
                        $resultRow = $connection2->prepare($sqlRow);
                        $resultRow->execute($dataRow);
                    } catch (PDOException $e) {
                        $URL .= '&&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    if ($resultRow->rowCount() != 1) {
                        $URL .= '&&return=error2';
                        header("Location: {$URL}");
                    } else {
                        //Combined delete of row and cells
                        try {
                            $data = array('pupilsightRubricID' => $pupilsightRubricID, 'pupilsightRubricRowID' => $pupilsightRubricRowID);
                            $sql = 'DELETE FROM pupilsightRubricRow WHERE pupilsightRubricRow.pupilsightRubricID=:pupilsightRubricID AND pupilsightRubricRow.pupilsightRubricRowID=:pupilsightRubricRowID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= '&&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        try {
                            $data = array('pupilsightRubricID' => $pupilsightRubricID, 'pupilsightRubricRowID' => $pupilsightRubricRowID);
                            $sql = 'DELETE FROM pupilsightRubricCell WHERE pupilsightRubricCell.pupilsightRubricID=:pupilsightRubricID AND pupilsightRubricCell.pupilsightRubricRowID=:pupilsightRubricRowID';
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

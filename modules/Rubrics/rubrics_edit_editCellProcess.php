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
                    $URL .= '&columnDeleteReturn=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() != 1) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                } else {
                    $partialFail = false;

                    //Add in all cells
                    $cells = $_POST['cell'];
                    for ($i = 0; $i < count($cells); ++$i) {
                        if ($_POST['pupilsightRubricColumnID'][$i] == '' or $_POST['pupilsightRubricRowID'][$i] == '') {
                            $partialFail = true;
                        } else {
                            //CELL DOES NOT EXIST YET
                            if ($_POST['pupilsightRubricCellID'][$i] == '') {
                                try {
                                    $data = array('pupilsightRubricID' => $pupilsightRubricID, 'pupilsightRubricColumnID' => $_POST['pupilsightRubricColumnID'][$i], 'pupilsightRubricRowID' => $_POST['pupilsightRubricRowID'][$i], 'contents' => $_POST['cell'][$i]);
                                    $sql = 'INSERT INTO pupilsightRubricCell SET pupilsightRubricID=:pupilsightRubricID, pupilsightRubricColumnID=:pupilsightRubricColumnID, pupilsightRubricRowID=:pupilsightRubricRowID, contents=:contents';
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }
                            }
                            //CELL EXISTS
                            else {
                                try {
                                    $data = array('pupilsightRubricID' => $pupilsightRubricID, 'pupilsightRubricColumnID' => $_POST['pupilsightRubricColumnID'][$i], 'pupilsightRubricRowID' => $_POST['pupilsightRubricRowID'][$i], 'contents' => $_POST['cell'][$i], 'pupilsightRubricCellID' => $_POST['pupilsightRubricCellID'][$i]);
                                    $sql = 'UPDATE pupilsightRubricCell SET pupilsightRubricID=:pupilsightRubricID, pupilsightRubricColumnID=:pupilsightRubricColumnID, pupilsightRubricRowID=:pupilsightRubricRowID, contents=:contents WHERE pupilsightRubricCellID=:pupilsightRubricCellID';
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }
                            }
                        }
                    }

                    //Deal with partial fail and success
                    if ($partialFail == true) {
                        $URL .= '&return=error5';
                        header("Location: {$URL}");
                    } else {
                        $URL .= '&return=success0';
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}

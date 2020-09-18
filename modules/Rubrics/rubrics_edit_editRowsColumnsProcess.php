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
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/rubrics_edit_editRowsColumns.php&pupilsightRubricID=$pupilsightRubricID&sidebar=false&search=$search&filter2=$filter2";
$URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/rubrics_edit.php&pupilsightRubricID=$pupilsightRubricID&sidebar=false&search=$search&filter2=$filter2";

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
                    $row = $result->fetch();
                    $pupilsightScaleID = $row['pupilsightScaleID'];
                    $partialFail = false;

                    //DEAL WITH ROWS
                    $rowTitles = isset($_POST['rowTitle'])? $_POST['rowTitle'] : array();
                    $rowOutcomes = isset($_POST['pupilsightOutcomeID'])? $_POST['pupilsightOutcomeID'] : array();
                    $rowIDs = isset($_POST['pupilsightRubricRowID'])? $_POST['pupilsightRubricRowID'] : array();
                    $count = 0;
                    foreach ($rowIDs as $pupilsightRubricRowID) {
                        $type = isset($_POST["type$count"])? $_POST["type$count"] : 'Standalone';
                        if ($type == 'Standalone' or $rowOutcomes[$count] == '') {
                            try {
                                $data = array('title' => $rowTitles[$count], 'pupilsightRubricRowID' => $pupilsightRubricRowID);
                                $sql = 'UPDATE pupilsightRubricRow SET title=:title, pupilsightOutcomeID=NULL WHERE pupilsightRubricRowID=:pupilsightRubricRowID';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        } elseif ($type == 'Outcome Based') {
                            try {
                                $data = array('pupilsightOutcomeID' => $rowOutcomes[$count], 'pupilsightRubricRowID' => $pupilsightRubricRowID);
                                $sql = "UPDATE pupilsightRubricRow SET title='', pupilsightOutcomeID=:pupilsightOutcomeID WHERE pupilsightRubricRowID=:pupilsightRubricRowID";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        } else {
                            $partialFail = true;
                        }

                        ++$count;
                    }

                    //DEAL WITH COLUMNS
                    //If no grade scale specified
                    if ($row['pupilsightScaleID'] == '') {
                        $columnTitles = isset($_POST['columnTitle'])? $_POST['columnTitle'] : array();
                        $columnIDs = isset($_POST['pupilsightRubricColumnID'])? $_POST['pupilsightRubricColumnID'] : array();
                        $columnVisualises = isset($_POST['columnVisualise'])? $_POST['columnVisualise'] : array();
                        $count = 0;
                        foreach ($columnIDs as $pupilsightRubricColumnID) {
                            $visualise = $columnVisualises[$count] ?? 'N';
                            try {
                                $data = array('title' => $columnTitles[$count], 'visualise' => $visualise, 'pupilsightRubricColumnID' => $pupilsightRubricColumnID);
                                $sql = 'UPDATE pupilsightRubricColumn SET title=:title, pupilsightScaleGradeID=NULL, visualise=:visualise WHERE pupilsightRubricColumnID=:pupilsightRubricColumnID';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                            ++$count;
                        }
                    }
                    //If scale specified	
                    else {
                        $columnGrades = $_POST['pupilsightScaleGradeID'];
                        $columnIDs = $_POST['pupilsightRubricColumnID'];
                        $columnVisualises = isset($_POST['columnVisualise'])? $_POST['columnVisualise'] : array();
                        $count = 0;
                        foreach ($columnIDs as $pupilsightRubricColumnID) {
                            $visualise = $columnVisualises[$count] ?? 'N';
                            try {
                                $data = array('pupilsightScaleGradeID' => $columnGrades[$count], 'visualise' => $visualise, 'pupilsightRubricColumnID' => $pupilsightRubricColumnID);
                                $sql = "UPDATE pupilsightRubricColumn SET title='', pupilsightScaleGradeID=:pupilsightScaleGradeID, visualise=:visualise WHERE pupilsightRubricColumnID=:pupilsightRubricColumnID";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                            ++$count;
                        }
                    }

                    if ($partialFail) {
                        $URL .= '&return=warning1';
                        header("Location: {$URL}");
                    } else {
                        $URL = $URLSuccess.'&return=success0#rubricDesign';
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}

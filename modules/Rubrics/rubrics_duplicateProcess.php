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
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/rubrics_duplicate.php&pupilsightRubricID=$pupilsightRubricID&search=$search&filter2=$filter2";

if (isActionAccessible($guid, $connection2, '/modules/Rubrics/rubrics_duplicate.php') == false) {
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
                    //Proceed!
                    $scope = $_POST['scope'];
                    $pupilsightDepartmentID = null;
                    if ($scope == 'Learning Area') {
                        $pupilsightDepartmentID = !empty($_POST['pupilsightDepartmentID'])? $_POST['pupilsightDepartmentID'] : $row['pupilsightDepartmentID'];
                    }
                    $name = $_POST['name'];

                    if ($scope == '' or ($scope == 'Learning Area' and $pupilsightDepartmentID == null) or $name == '') {
                        $URL .= '&return=error3';
                        header("Location: {$URL}");
                    } else {
                        //Write to database
                        try {
                            $data = array('scope' => $scope, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'name' => $name, 'active' => $row['active'], 'category' => $row['category'], 'description' => $row['description'], 'pupilsightYearGroupIDList' => $row['pupilsightYearGroupIDList'], 'pupilsightScaleID' => $row['pupilsightScaleID'], 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
                            $sql = 'INSERT INTO pupilsightRubric SET scope=:scope, pupilsightDepartmentID=:pupilsightDepartmentID, name=:name, active=:active, category=:category, description=:description, pupilsightYearGroupIDList=:pupilsightYearGroupIDList, pupilsightScaleID=:pupilsightScaleID, pupilsightPersonIDCreator=:pupilsightPersonIDCreator';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        //Get last insert ID
                        $AI = str_pad($connection2->lastInsertID(), 8, '0', STR_PAD_LEFT);

                        $partialFail = false;

                        //INSERT ROWS
                        $rows = array();
                        try {
                            $dataFetch = array('pupilsightRubricID' => $pupilsightRubricID);
                            $sqlFetch = 'SELECT * FROM pupilsightRubricRow WHERE pupilsightRubricID=:pupilsightRubricID ORDER BY sequenceNumber';
                            $resultFetch = $connection2->prepare($sqlFetch);
                            $resultFetch->execute($dataFetch);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                        while ($rowFetch = $resultFetch->fetch()) {
                            try {
                                $dataInsert = array('pupilsightRubricID' => $AI, 'title' => $rowFetch['title'], 'sequenceNumber' => $rowFetch['sequenceNumber'], 'pupilsightOutcomeID' => $rowFetch['pupilsightOutcomeID']);
                                $sqlInsert = 'INSERT INTO pupilsightRubricRow SET pupilsightRubricID=:pupilsightRubricID, title=:title, sequenceNumber=:sequenceNumber, pupilsightOutcomeID=:pupilsightOutcomeID';
                                $resultInsert = $connection2->prepare($sqlInsert);
                                $resultInsert->execute($dataInsert);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                            $rows[$rowFetch['pupilsightRubricRowID']] = str_pad($connection2->lastInsertID(), 9, '0', STR_PAD_LEFT);
                        }

                        //INSERT COLUMNS
                        $columns = array();
                        try {
                            $dataFetch = array('pupilsightRubricID' => $pupilsightRubricID);
                            $sqlFetch = 'SELECT * FROM pupilsightRubricColumn WHERE pupilsightRubricID=:pupilsightRubricID ORDER BY sequenceNumber';
                            $resultFetch = $connection2->prepare($sqlFetch);
                            $resultFetch->execute($dataFetch);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                        while ($rowFetch = $resultFetch->fetch()) {
                            try {
                                $dataInsert = array('pupilsightRubricID' => $AI, 'title' => $rowFetch['title'], 'sequenceNumber' => $rowFetch['sequenceNumber'], 'pupilsightScaleGradeID' => $rowFetch['pupilsightScaleGradeID']);
                                $sqlInsert = 'INSERT INTO pupilsightRubricColumn SET pupilsightRubricID=:pupilsightRubricID, title=:title, sequenceNumber=:sequenceNumber, pupilsightScaleGradeID=:pupilsightScaleGradeID';
                                $resultInsert = $connection2->prepare($sqlInsert);
                                $resultInsert->execute($dataInsert);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                            $columns[$rowFetch['pupilsightRubricColumnID']] = str_pad($connection2->lastInsertID(), 9, '0', STR_PAD_LEFT);
                        }

                        //INSERT CELLS
                        try {
                            $dataFetch = array('pupilsightRubricID' => $pupilsightRubricID);
                            $sqlFetch = 'SELECT * FROM pupilsightRubricCell WHERE pupilsightRubricID=:pupilsightRubricID';
                            $resultFetch = $connection2->prepare($sqlFetch);
                            $resultFetch->execute($dataFetch);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                        while ($rowFetch = $resultFetch->fetch()) {
                            try {
                                $dataInsert = array('pupilsightRubricID' => $AI, 'pupilsightRubricColumnID' => $columns[$rowFetch['pupilsightRubricColumnID']], 'pupilsightRubricRowID' => $rows[$rowFetch['pupilsightRubricRowID']], 'contents' => $rowFetch['contents']);
                                $sqlInsert = 'INSERT INTO pupilsightRubricCell SET pupilsightRubricID=:pupilsightRubricID, pupilsightRubricColumnID=:pupilsightRubricColumnID, pupilsightRubricRowID=:pupilsightRubricRowID, contents=:contents';
                                $resultInsert = $connection2->prepare($sqlInsert);
                                $resultInsert->execute($dataInsert);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }

                        if ($partialFail == true) {
                            $URL .= '&return=warning1';
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
}

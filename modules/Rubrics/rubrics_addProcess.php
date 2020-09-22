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

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/rubrics_add.php&search=$search&filter2=$filter2";
$URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/rubrics_edit.php&sidebar=false&search=$search&filter2=$filter2";

if (isActionAccessible($guid, $connection2, '/modules/Rubrics/rubrics_add.php') == false) {
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
            $scope = $_POST['scope'];
            if ($scope == 'Learning Area') {
                $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
            } else {
                $pupilsightDepartmentID = null;
            }
            $name = $_POST['name'];
            $active = $_POST['active'];
            $category = $_POST['category'];
            $description = $_POST['description'];
            $pupilsightYearGroupIDList = isset($_POST['pupilsightYearGroupIDList']) ? implode(',', $_POST['pupilsightYearGroupIDList']) : '';
            $pupilsightScaleID = null;
            if ($_POST['pupilsightScaleID'] != '') {
                $pupilsightScaleID = $_POST['pupilsightScaleID'];
            }

            if ($scope == '' or ($scope == 'Learning Area' and $pupilsightDepartmentID == '') or $name == '' or $active == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                //Lock table
                try {
                    $sql = 'LOCK TABLES pupilsightRubric WRITE';
                    $result = $connection2->query($sql);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Get next autoincrement
                try {
                    $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightRubric'";
                    $resultAI = $connection2->query($sqlAI);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                $rowAI = $resultAI->fetch();
                $AI = str_pad($rowAI['Auto_increment'], 8, '0', STR_PAD_LEFT);

                if ($AI == '') {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('scope' => $scope, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'name' => $name, 'active' => $active, 'category' => $category, 'description' => $description, 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList, 'pupilsightScaleID' => $pupilsightScaleID, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = 'INSERT INTO pupilsightRubric SET scope=:scope, pupilsightDepartmentID=:pupilsightDepartmentID, name=:name, active=:active, category=:category, description=:description, pupilsightYearGroupIDList=:pupilsightYearGroupIDList, pupilsightScaleID=:pupilsightScaleID, pupilsightPersonIDCreator=:pupilsightPersonIDCreator';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Unlock module table
                    try {
                        $sql = 'UNLOCK TABLES';
                        $result = $connection2->query($sql);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Create rows & columns
                    for ($i = 1; $i <= $_POST['rows']; ++$i) {
                        try {
                            $data = array('pupilsightRubricID' => $AI, 'title' => "Row $i", 'sequenceNumber' => $i);
                            $sql = 'INSERT INTO pupilsightRubricRow SET pupilsightRubricID=:pupilsightRubricID, title=:title, sequenceNumber=:sequenceNumber';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                        }
                    }
                    for ($i = 1; $i <= $_POST['columns']; ++$i) {
                        try {
                            $data = array('pupilsightRubricID' => $AI, 'title' => "Column $i", 'sequenceNumber' => $i);
                            $sql = 'INSERT INTO pupilsightRubricColumn SET pupilsightRubricID=:pupilsightRubricID, title=:title, sequenceNumber=:sequenceNumber';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                        }
                    }

                    $URL = $URLSuccess."&return=success0&pupilsightRubricID=$AI";
                    header("Location: {$URL}");
                }
            }
        }
    }
}

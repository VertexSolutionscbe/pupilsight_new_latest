<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';


$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$atlColumnID = $_GET['atlColumnID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/atl_manage_edit.php&atlColumnID=$atlColumnID&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/ATL/atl_manage_edit.php') == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['address'], $connection2);
    if ($highestAction == false) {
        //Fail 0
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        if (empty($_POST)) {
            $URL .= '&return=error5';
            header("Location: {$URL}");
        } else {
            //Proceed!
            //Check if school year specified
            if ($atlColumnID == '' or $pupilsightCourseClassID == '') {
                //Fail1
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                try {
                    $data = array('atlColumnID' => $atlColumnID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = 'SELECT * FROM atlColumn WHERE atlColumnID=:atlColumnID AND pupilsightCourseClassID=:pupilsightCourseClassID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    //Fail2
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() != 1) {
                    //Fail 2
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                } else {
                    $row = $result->fetch();

                    //Validate Inputs
                    $name = $_POST['name'];
                    $description = $_POST['description'];
                    $pupilsightRubricID = $_POST['pupilsightRubricID'];
                    $completeDate = $_POST['completeDate'];
                    if ($completeDate == '') {
                        $completeDate = null;
                        $complete = 'N';
                    } else {
                        $completeDate = dateConvert($guid, $completeDate);
                        $complete = 'Y';
                    }
                    $pupilsightPersonIDLastEdit = $_SESSION[$guid]['pupilsightPersonID'];
                    $groupingID = $row['groupingID'];

                    if ($name == '' or $description == '') {
                        //Fail 3
                        $URL .= '&return=error3';
                        header("Location: {$URL}");
                    } else {
                        //ATTEMPT TO UPDATE LINKED COLUMNS
                        $partialFail = false;
                        if (isset($_POST['pupilsightCourseClassID'])) {
                            if (is_array($_POST['pupilsightCourseClassID'])) {
                                $pupilsightCourseClassIDs = $_POST['pupilsightCourseClassID'];
                                foreach ($pupilsightCourseClassIDs as $pupilsightCourseClassID2) {
                                    //Write to database
                                    try {
                                        $data = array('name' => $name, 'description' => $description, 'pupilsightRubricID' => $pupilsightRubricID, 'completeDate' => $completeDate, 'complete' => $complete, 'pupilsightPersonIDLastEdit' => $_SESSION[$guid]['pupilsightPersonID'], 'groupingID' => $groupingID, 'pupilsightCourseClassID' => $pupilsightCourseClassID2);
                                        $sql = 'UPDATE atlColumn SET name=:name, description=:description, pupilsightRubricID=:pupilsightRubricID, completeDate=:completeDate, complete=:complete, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit WHERE groupingID=:groupingID AND pupilsightCourseClassID=:pupilsightCourseClassID';
                                        $result = $connection2->prepare($sql);
                                        $result->execute($data);
                                    } catch (PDOException $e) {
                                        $partialFail = true;
                                        echo $e->getMessage();
                                    }

                                }
                            }
                        }

                        //Write to database
                        try {
                            $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'name' => $name, 'description' => $description, 'pupilsightRubricID' => $pupilsightRubricID, 'completeDate' => $completeDate, 'complete' => $complete, 'pupilsightPersonIDLastEdit' => $pupilsightPersonIDLastEdit, 'atlColumnID' => $atlColumnID);
                            $sql = 'UPDATE atlColumn SET pupilsightCourseClassID=:pupilsightCourseClassID, name=:name, description=:description, pupilsightRubricID=:pupilsightRubricID, completeDate=:completeDate, complete=:complete, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit WHERE atlColumnID=:atlColumnID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            //Fail 2
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        if ($partialFail == true) {
                            //Fail 6
                            $URL .= '&return=warning1';
                            header("Location: {$URL}");
                        } else {
                            //Success 0
                            $URL .= '&return=success0';
                            header("Location: {$URL}");
                        }
                    }
                }
            }
        }
    }
}

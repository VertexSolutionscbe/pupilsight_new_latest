<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';


$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$atlColumnID = $_GET['atlColumnID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/atl_write_data.php&atlColumnID=$atlColumnID&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/ATL/atl_write_data.php') == false) {
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
                $name = $row['name' ];
                $count = $_POST['count'];
                $partialFail = false;

                for ($i = 1;$i <= $count;++$i) {
                    $pupilsightPersonIDStudent = $_POST["$i-pupilsightPersonID"];
                    //Complete
                    $completeValue = isset($_POST["complete$i"])? $_POST["complete$i"] : 'N';
                    $pupilsightPersonIDLastEdit = $_SESSION[$guid]['pupilsightPersonID'];

                    $selectFail = false;
                    try {
                        $data = array('atlColumnID' => $atlColumnID, 'pupilsightPersonIDStudent' => $pupilsightPersonIDStudent);
                        $sql = 'SELECT * FROM atlEntry WHERE atlColumnID=:atlColumnID AND pupilsightPersonIDStudent=:pupilsightPersonIDStudent';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                        $selectFail = true;
                    }
                    if (!($selectFail)) {
                        if ($result->rowCount() < 1) {
                            try {
                                $data = array('atlColumnID' => $atlColumnID, 'pupilsightPersonIDStudent' => $pupilsightPersonIDStudent, 'complete' => $completeValue, 'pupilsightPersonIDLastEdit' => $pupilsightPersonIDLastEdit);
                                $sql = 'INSERT INTO atlEntry SET atlColumnID=:atlColumnID, pupilsightPersonIDStudent=:pupilsightPersonIDStudent, complete=:complete, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        } else {
                            $row = $result->fetch();
                            //Update
                            try {
                                $data = array('atlColumnID' => $atlColumnID, 'pupilsightPersonIDStudent' => $pupilsightPersonIDStudent, 'complete' => $completeValue, 'pupilsightPersonIDLastEdit' => $pupilsightPersonIDLastEdit, 'atlEntryID' => $row['atlEntryID']);
                                $sql = 'UPDATE atlEntry SET atlColumnID=:atlColumnID, pupilsightPersonIDStudent=:pupilsightPersonIDStudent, complete=:complete, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit WHERE atlEntryID=:atlEntryID';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }
                }

                //Update column
                $completeDate = $_POST['completeDate'];
                if ($completeDate == '') {
                    $completeDate = null;
                    $complete = 'N';
                } else {
                    $completeDate = dateConvert($guid, $completeDate);
                    $complete = 'Y';
                }
                try {
                    $data = array('completeDate' => $completeDate, 'complete' => $complete, 'atlColumnID' => $atlColumnID);
                    $sql = 'UPDATE atlColumn SET completeDate=:completeDate, complete=:complete WHERE atlColumnID=:atlColumnID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }

                //Return!
                if ($partialFail == true) {
                    //Fail 3
                    $URL .= '&return=error3';
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

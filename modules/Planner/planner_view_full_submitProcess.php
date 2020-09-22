<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightPlannerEntryID = $_GET['pupilsightPlannerEntryID'];
$currentDate = $_POST['currentDate'];
$today = date('Y-m-d');
$params = '';
if (isset($_GET['date'])) {
    $params = $params.'&date='.$_GET['date'];
}
if (isset($_GET['viewBy'])) {
    $params = $params.'&viewBy='.$_GET['viewBy'];
}
if (isset($_GET['pupilsightCourseClassID'])) {
    $params = $params.'&pupilsightCourseClassID='.$_GET['pupilsightCourseClassID'];
}
$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_view_full.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID$params";

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_view_full.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    if (empty($_POST)) {
        $URL .= '&return=error6';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if planner specified
        if ($pupilsightPlannerEntryID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                $sql = 'SELECT * FROM pupilsightPlannerEntry WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
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
                //Check that date is not in the future
                if ($currentDate > $today) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Check that date is a school day
                    if (isSchoolOpen($guid, $currentDate, $connection2) == false) {
                        $URL .= '&return=warning1';
                        header("Location: {$URL}");
                    } else {
                        //Get variables
                        $type = $_POST['type'];
                        $version = $_POST['version'];
                        $link = $_POST['link'];
                        $status = $_POST['status'];
                        $timestamp = date('Y-m-d H:i:s');
                        //Recheck status in case page held open during the deadline
                        if ($timestamp > $row['homeworkDueDateTime']) {
                            $status = 'Late';
                        }
                        $pupilsightPlannerEntryID = $_POST['pupilsightPlannerEntryID'];
                        $count = $_POST['count'];
                        $lesson = $_POST['lesson'];

                        //Validation
                        if ($type == '' or $version == '' or ($_FILES['file']['name'] == '' and $link == '') or $status == '' or $count == '' or $lesson == '') {
                            $URL .= '&return=error3';
                            header("Location: {$URL}");
                        } else {
                            $partialFail = false;
                            if ($type == 'Link') {
                                if (substr($link, 0, 7) != 'http://' and substr($link, 0, 8) != 'https://') {
                                    $partialFail = true;
                                } else {
                                    $attachment = $link;
                                }
                            }
                            if ($type == 'File') {
                                $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);

                                $file = (isset($_FILES['file']))? $_FILES['file'] : null;

                                // Upload the file, return the /uploads relative path
                                $attachment = $fileUploader->uploadFromPost($file, $_SESSION[$guid]['username'].'_'.$lesson);

                                if (empty($attachment)) {
                                    $partialFail = true;
                                }
                            }

                            //Deal with partial fail
                            if ($partialFail == true) {
                                $URL .= '&return=error6';
                                header("Location: {$URL}");
                            } else {
                                //Write to database
                                try {
                                    $data = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'type' => $type, 'version' => $version, 'status' => $status, 'location' => $attachment, 'count' => ($count + 1), 'timestamp' => $timestamp);
                                    $sql = 'INSERT INTO pupilsightPlannerEntryHomework SET pupilsightPlannerEntryID=:pupilsightPlannerEntryID, pupilsightPersonID=:pupilsightPersonID, type=:type, version=:version, status=:status, location=:location, count=:count, timestamp=:timestamp';
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
    }
}

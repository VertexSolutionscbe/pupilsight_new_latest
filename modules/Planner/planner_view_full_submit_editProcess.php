<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightPlannerEntryID = $_POST['pupilsightPlannerEntryID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/planner_view_full.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID&search=".$_POST['search'].$_POST['params'];

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_view_full_submit_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if planner specified
        if ($pupilsightPlannerEntryID == '') {
            $URL .= '&return=error1a';
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
                if ($_POST['submission'] != 'true' and $_POST['submission'] != 'false') {
                    $URL .= '&return=error1b';
                    header("Location: {$URL}");
                } else {
                    if ($_POST['submission'] == 'true') {
                        $submission = true;
                        $pupilsightPlannerEntryHomeworkID = $_POST['pupilsightPlannerEntryHomeworkID'];
                    } else {
                        $submission = false;
                        $pupilsightPersonID = $_POST['pupilsightPersonID'];
                    }

                    $type = null;
                    if (isset($_POST['type'])) {
                        $type = $_POST['type'];
                    }
                    $version = null;
                    if (isset($_POST['version'])) {
                        $version = $_POST['version'];
                    }
                    $link = null;
                    if (isset($_POST['link'])) {
                        $link = $_POST['link'];
                    }
                    $status = null;
                    if (isset($_POST['status'])) {
                        $status = $_POST['status'];
                    }
                    $pupilsightPlannerEntryID = null;
                    if (isset($_POST['pupilsightPlannerEntryID'])) {
                        $pupilsightPlannerEntryID = $_POST['pupilsightPlannerEntryID'];
                    }
                    $count = null;
                    if (isset($_POST['count'])) {
                        $count = $_POST['count'];
                    }
                    $lesson = null;
                    if (isset($_POST['lesson'])) {
                        $lesson = $_POST['lesson'];
                    }

                    if (($submission == true and $pupilsightPlannerEntryHomeworkID == '') or ($submission == false and ($pupilsightPersonID == '' or $type == '' or $version == '' or ($type == 'File' and $_FILES['file']['name'] == '') or ($type == 'Link' and $link == '') or $status == '' or $lesson == '' or $count == ''))) {
                        $URL .= '&return=error1';
                        header("Location: {$URL}");
                    } else {
                        if ($submission == true) {
                            try {
                                $data = array('status' => $status, 'pupilsightPlannerEntryHomeworkID' => $pupilsightPlannerEntryHomeworkID);
                                $sql = 'UPDATE pupilsightPlannerEntryHomework SET status=:status WHERE pupilsightPlannerEntryHomeworkID=:pupilsightPlannerEntryHomeworkID';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $URL .= '&return=error2';
                                header("Location: {$URL}");
                                exit();
                            }
                            $URL .= '&return=success0';
                            header("Location: {$URL}");
                        } else {
                            $partialFail = false;
                            $attachment = null;
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
                                    $data = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $pupilsightPersonID, 'type' => $type, 'version' => $version, 'status' => $status, 'location' => $attachment, 'count' => ($count + 1), 'timestamp' => date('Y-m-d H:i:s'));
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

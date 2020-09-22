<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightCourseID = $_GET['pupilsightCourseID'];
$pupilsightUnitID = $_GET['pupilsightUnitID'];
$classCount = $_POST['classCount'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/units_edit.php&pupilsightUnitID=$pupilsightUnitID&pupilsightCourseID=$pupilsightCourseID&pupilsightSchoolYearID=$pupilsightSchoolYearID";

if (isActionAccessible($guid, $connection2, '/modules/Planner/units_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        if (empty($_POST)) {
            $URL .= '&return=warning1';
            header("Location: {$URL}");
        } else {
            //Proceed!

            //Validate Inputs
            $name = $_POST['name'];
            $description = $_POST['description'];
            $tags = $_POST['tags'];
            $active = $_POST['active'];
            $map = $_POST['map'];
            $ordering = $_POST['ordering'];
            $details = $_POST['details'];
            $license = $_POST['license'];
            $sharedPublic = null;
            if (isset($_POST['sharedPublic'])) {
                $sharedPublic = $_POST['sharedPublic'];
            }

            if ($pupilsightSchoolYearID == '' or $pupilsightCourseID == '' or $pupilsightUnitID == '' or $name == '' or $description == '' or $active == '' or $map == '' or $ordering == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check access to specified course
                try {
                    if ($highestAction == 'Unit Planner_all') {
                        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID);
                        $sql = 'SELECT * FROM pupilsightCourse WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseID=:pupilsightCourseID';
                    } elseif ($highestAction == 'Unit Planner_learningAreas') {
                        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = "SELECT pupilsightCourseID, pupilsightCourse.name, pupilsightCourse.nameShort FROM pupilsightCourse JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID AND (role='Coordinator' OR role='Assistant Coordinator' OR role='Teacher (Curriculum)') AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseID=:pupilsightCourseID ORDER BY pupilsightCourse.nameShort";
                    }
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() != 1) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Check existence of specified unit
                    try {
                        $data = array('pupilsightUnitID' => $pupilsightUnitID, 'pupilsightCourseID' => $pupilsightCourseID);
                        $sql = 'SELECT * FROM pupilsightUnit WHERE pupilsightUnitID=:pupilsightUnitID AND pupilsightCourseID=:pupilsightCourseID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    if ($result->rowCount() != 1) {
                        $URL .= '&return=error3';
                        header("Location: {$URL}");
                    } else {
                        $row = $result->fetch();
                        $partialFail = false;
                        //Move attached file, if there is one
                        if (!empty($_FILES['file']['tmp_name'])) {
                            $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);

                            $file = (isset($_FILES['file']))? $_FILES['file'] : null;

                            // Upload the file, return the /uploads relative path
                            $attachment = $fileUploader->uploadFromPost($file, $name);

                            if (empty($attachment)) {
                                $partialFail = true;
                            } else {
                                $content = $attachment;
                            }
                        } else {
                            $attachment = $_POST['attachment'];
                        }

                        //Update classes
                        if ($classCount > 0) {
                            for ($i = 0;$i < $classCount;++$i) {
                                $running = $_POST['running'.$i];
                                if ($running != 'Y' and $running != 'N') {
                                    $running = 'N';
                                }

                                //Check to see if entry exists
                                try {
                                    $dataUnitClass = array('pupilsightUnitID' => $pupilsightUnitID, 'pupilsightCourseClassID' => $_POST['pupilsightCourseClassID'.$i]);
                                    $sqlUnitClass = 'SELECT * FROM pupilsightUnitClass WHERE pupilsightUnitID=:pupilsightUnitID AND pupilsightCourseClassID=:pupilsightCourseClassID';
                                    $resultUnitClass = $connection2->prepare($sqlUnitClass);
                                    $resultUnitClass->execute($dataUnitClass);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }

                                if ($resultUnitClass->rowCount() > 0) {
                                    try {
                                        $dataClass = array('running' => $running, 'pupilsightUnitID' => $pupilsightUnitID, 'pupilsightCourseClassID' => $_POST['pupilsightCourseClassID'.$i]);
                                        $sqlClass = 'UPDATE pupilsightUnitClass SET running=:running WHERE pupilsightUnitID=:pupilsightUnitID AND pupilsightCourseClassID=:pupilsightCourseClassID';
                                        $resultClass = $connection2->prepare($sqlClass);
                                        $resultClass->execute($dataClass);
                                    } catch (PDOException $e) {
                                        $partialFail = true;
                                    }
                                } else {
                                    try {
                                        $dataClass = array('running' => $running, 'pupilsightUnitID' => $pupilsightUnitID, 'pupilsightCourseClassID' => $_POST['pupilsightCourseClassID'.$i]);
                                        $sqlClass = 'INSERT INTO pupilsightUnitClass SET pupilsightUnitID=:pupilsightUnitID, pupilsightCourseClassID=:pupilsightCourseClassID, running=:running';
                                        $resultClass = $connection2->prepare($sqlClass);
                                        $resultClass->execute($dataClass);
                                    } catch (PDOException $e) {
                                        $partialFail = true;
                                    }
                                }
                            }
                        }

                        //Update blocks
                        $order = '';
                        if (isset($_POST['order'])) {
                            $order = $_POST['order'];
                        }
                        $sequenceNumber = 0;
                        $dataRemove = array();
                        $whereRemove = '';
                        if (count($order) < 0) {
                            $URL .= '&return=error1';
                            header("Location: {$URL}");
                        } else {
                            if (is_array($order)) {
                                foreach ($order as $i) {
                                    $title = '';
                                    if ($_POST["title$i"] != "Block $i") {
                                        $title = $_POST["title$i"];
                                    }
                                    $type2 = '';
                                    if ($_POST["type$i"] != 'type (e.g. discussion, outcome)') {
                                        $type2 = $_POST["type$i"];
                                    }
                                    $length = '';
                                    if ($_POST["length$i"] != 'length (min)') {
                                        $length = $_POST["length$i"];
                                    }
                                    $contents = $_POST["contents$i"];
                                    $teachersNotes = $_POST["teachersNotes$i"];
                                    $pupilsightUnitBlockID = $_POST["pupilsightUnitBlockID$i"];

                                    if ($pupilsightUnitBlockID != '') {
                                        try {
                                            $dataBlock = array('pupilsightUnitID' => $pupilsightUnitID, 'title' => $title, 'type' => $type2, 'length' => $length, 'contents' => $contents, 'teachersNotes' => $teachersNotes, 'sequenceNumber' => $sequenceNumber, 'pupilsightUnitBlockID' => $pupilsightUnitBlockID);
                                            $sqlBlock = 'UPDATE pupilsightUnitBlock SET pupilsightUnitID=:pupilsightUnitID, title=:title, type=:type, length=:length, contents=:contents, teachersNotes=:teachersNotes, sequenceNumber=:sequenceNumber WHERE pupilsightUnitBlockID=:pupilsightUnitBlockID';
                                            $resultBlock = $connection2->prepare($sqlBlock);
                                            $resultBlock->execute($dataBlock);
                                        } catch (PDOException $e) {
                                            $partialFail = true;
                                        }
                                        $dataRemove["pupilsightUnitBlockID$sequenceNumber"] = $pupilsightUnitBlockID;
                                        $whereRemove .= "AND NOT pupilsightUnitBlockID=:pupilsightUnitBlockID$sequenceNumber ";
                                    } else {
                                        try {
                                            $dataBlock = array('pupilsightUnitID' => $pupilsightUnitID, 'title' => $title, 'type' => $type2, 'length' => $length, 'contents' => $contents, 'teachersNotes' => $teachersNotes, 'sequenceNumber' => $sequenceNumber);
                                            $sqlBlock = 'INSERT INTO pupilsightUnitBlock SET pupilsightUnitID=:pupilsightUnitID, title=:title, type=:type, length=:length, contents=:contents, teachersNotes=:teachersNotes, sequenceNumber=:sequenceNumber';
                                            $resultBlock = $connection2->prepare($sqlBlock);
                                            $resultBlock->execute($dataBlock);
                                        } catch (PDOException $e) {
                                            echo $e->getMessage();
                                            $partialFail = true;
                                        }
                                        $dataRemove["pupilsightUnitBlockID$sequenceNumber"] = $connection2->lastInsertId();
                                        $whereRemove .= "AND NOT pupilsightUnitBlockID=:pupilsightUnitBlockID$sequenceNumber ";
                                    }

                                    ++$sequenceNumber;
                                }
                            }
                        }

                        //Remove orphaned blocks
                        if ($whereRemove != '(') {
                            try {
                                $dataRemove['pupilsightUnitID'] = $pupilsightUnitID;
                                $sqlRemove = "DELETE FROM pupilsightUnitBlock WHERE pupilsightUnitID=:pupilsightUnitID $whereRemove";
                                $resultRemove = $connection2->prepare($sqlRemove);
                                $resultRemove->execute($dataRemove);
                            } catch (PDOException $e) {
                                echo $e->getMessage();
                                $partialFail = true;
                            }
                        }

                        //Delete all outcomes
                        try {
                            $dataDelete = array('pupilsightUnitID' => $pupilsightUnitID);
                            $sqlDelete = 'DELETE FROM pupilsightUnitOutcome WHERE pupilsightUnitID=:pupilsightUnitID';
                            $resultDelete = $connection2->prepare($sqlDelete);
                            $resultDelete->execute($dataDelete);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }
                        //Insert outcomes
                        $count = 0;
                        if (isset($_POST['outcomeorder'])) {
                            if (count($_POST['outcomeorder']) > 0) {
                                foreach ($_POST['outcomeorder'] as $outcome) {
                                    if ($_POST["outcomepupilsightOutcomeID$outcome"] != '') {
                                        try {
                                            $dataInsert = array('pupilsightUnitID' => $pupilsightUnitID, 'pupilsightOutcomeID' => $_POST["outcomepupilsightOutcomeID$outcome"], 'content' => $_POST["outcomecontents$outcome"], 'count' => $count);
                                            $sqlInsert = 'INSERT INTO pupilsightUnitOutcome SET pupilsightUnitID=:pupilsightUnitID, pupilsightOutcomeID=:pupilsightOutcomeID, content=:content, sequenceNumber=:count';
                                            $resultInsert = $connection2->prepare($sqlInsert);
                                            $resultInsert->execute($dataInsert);
                                        } catch (PDOException $e) {
                                            echo $e;
                                            $partialFail = true;
                                        }
                                    }
                                    ++$count;
                                }
                            }
                        }

                        //Write to database
                        try {
                            $data = array('name' => $name, 'attachment' => $attachment, 'description' => $description, 'tags' => $tags, 'active' => $active, 'map' => $map, 'ordering' => $ordering, 'details' => $details, 'license' => $license, 'sharedPublic' => $sharedPublic, 'pupilsightPersonIDLastEdit' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightUnitID' => $pupilsightUnitID);
                            $sql = 'UPDATE pupilsightUnit SET name=:name, attachment=:attachment, description=:description, tags=:tags, active=:active, map=:map, ordering=:ordering, details=:details, license=:license, sharedPublic=:sharedPublic, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit WHERE pupilsightUnitID=:pupilsightUnitID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        if ($partialFail) {
                            $URL .= '&updateReturn=error6';
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

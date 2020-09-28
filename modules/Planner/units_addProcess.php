<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightProgramID = $_GET['pupilsightProgramID'];
$pupilsightCourseID = $_GET['pupilsightCourseID'];
$classCount = $_POST['classCount'] ?? null;
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/units_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightProgramID=$pupilsightProgramID&pupilsightCourseID=$pupilsightCourseID";
$URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/units_edit.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightProgramID=$pupilsightProgramID&pupilsightCourseID=$pupilsightCourseID";

if (isActionAccessible($guid, $connection2, '/modules/Planner/units_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        if (empty($_POST)) {
            $URL .= '&return=error6';
            header("Location: {$URL}");
        } else {
            //Proceed!
            //Validate Inputs
            $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $tags = $_POST['tags'];
            $active = $_POST['active'];
            $map = $_POST['map'];
            $ordering = $_POST['ordering'];
            $details = $_POST['details'];
            $license = $_POST['license'] ?? null;
            $sharedPublic = null;
            if (isset($_POST['sharedPublic'])) {
                $sharedPublic = $_POST['sharedPublic'];
            }

            if ($pupilsightSchoolYearID == '' or $pupilsightCourseID == '' or $name == '' or $description == '' or $active == '' or $map == '' or $ordering == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                //Check access to specified course
                try {
                    // if ($highestAction == 'Unit Planner_all') {
                    //     $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID);
                    //     $sql = 'SELECT * FROM pupilsightCourse WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseID=:pupilsightCourseID';
                    // } elseif ($highestAction == 'Unit Planner_learningAreas') {
                    //     $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    //     $sql = "SELECT pupilsightCourseID, pupilsightCourse.name, pupilsightCourse.nameShort FROM pupilsightCourse JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID AND (role='Coordinator' OR role='Assistant Coordinator' OR role='Teacher (Curriculum)') AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseID=:pupilsightCourseID ORDER BY pupilsightCourse.nameShort";
                    // }
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightCourseID);
                    $sql = 'SELECT * FROM pupilsightProgramClassSectionMapping WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2'.$e->getMessage();
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() != 1) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Lock markbook column table
                    try {
                        $sql = 'LOCK TABLES pupilsightUnit WRITE, pupilsightUnitClass WRITE, pupilsightUnitBlock WRITE,  pupilsightUnitOutcome WRITE, pupilsightFileExtension READ';
                        $result = $connection2->query($sql);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Get next autoincrement
                    try {
                        $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightUnit'";
                        $resultAI = $connection2->query($sqlAI);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $rowAI = $resultAI->fetch();
                    $AI = str_pad($rowAI['Auto_increment'], 10, '0', STR_PAD_LEFT);

                    $partialFail = false;

                    //Move attached file, if there is one
                    if (!empty($_FILES['file']['tmp_name'])) {
                        $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);

                        $file = (isset($_FILES['file']))? $_FILES['file'] : null;

                        // Upload the file, return the /uploads relative path
                        $attachment = $fileUploader->uploadFromPost($file, $name);

                        if (empty($attachment)) {
                            $partialFail = true;
                        }
                    } else {
                        $attachment = '';
                    }

                    //ADD CLASS RECORDS
                    if ($classCount > 0) {
                        for ($i = 0;$i < $classCount;++$i) {
                            $running = $_POST['running'.$i];
                            if ($running != 'Y' and $running != 'N') {
                                $running = 'N';
                            }

                            try {
                                $dataClass = array('pupilsightUnitID' => $AI, 'pupilsightCourseClassID' => $_POST['pupilsightCourseClassID'.$i], 'running' => $running);
                                $sqlClass = 'INSERT INTO pupilsightUnitClass SET pupilsightUnitID=:pupilsightUnitID, pupilsightCourseClassID=:pupilsightCourseClassID, running=:running';
                                $resultClass = $connection2->prepare($sqlClass);
                                $resultClass->execute($dataClass);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }

                    //ADD BLOCKS
                    $blockCount = ($_POST['blockCount'] - 1);
                    $sequenceNumber = 0;
                    if ($blockCount > 0) {
                        $order = array();
                        if (isset($_POST['order'])) {
                            $order = $_POST['order'];
                        }
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

                            if ($title != '' or $contents != '') {
                                try {
                                    $dataBlock = array('pupilsightUnitID' => $AI, 'title' => $title, 'type' => $type2, 'length' => $length, 'contents' => $contents, 'teachersNotes' => $teachersNotes, 'sequenceNumber' => $sequenceNumber);
                                    $sqlBlock = 'INSERT INTO pupilsightUnitBlock SET pupilsightUnitID=:pupilsightUnitID, title=:title, type=:type, length=:length, contents=:contents, teachersNotes=:teachersNotes, sequenceNumber=:sequenceNumber';
                                    $resultBlock = $connection2->prepare($sqlBlock);
                                    $resultBlock->execute($dataBlock);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }
                                ++$sequenceNumber;
                            }
                        }
                    }

                    //Insert outcomes
                    $count = 0;
                    $outcomeorder = null;
                    if (isset($_POST['outcomeorder'])) {
                        $outcomeorder = $_POST['outcomeorder'];
                    }
                    if (count($outcomeorder) > 0) {
                        foreach ($outcomeorder as $outcome) {
                            if ($_POST["outcomepupilsightOutcomeID$outcome"] != '') {
                                try {
                                    $dataInsert = array('AI' => $AI, 'pupilsightOutcomeID' => $_POST["outcomepupilsightOutcomeID$outcome"], 'content' => $_POST["outcomecontents$outcome"], 'count' => $count);
                                    $sqlInsert = 'INSERT INTO pupilsightUnitOutcome SET pupilsightUnitID=:AI, pupilsightOutcomeID=:pupilsightOutcomeID, content=:content, sequenceNumber=:count';
                                    $resultInsert = $connection2->prepare($sqlInsert);
                                    $resultInsert->execute($dataInsert);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }
                            }
                            ++$count;
                        }
                    }

                    //Write to database
                    try {
                        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID,'pupilsightProgramID' => $pupilsightProgramID,'pupilsightYearGroupID' => $pupilsightCourseID,'pupilsightCourseID' => $pupilsightCourseID,'pupilsightDepartmentID' => $pupilsightDepartmentID, 'name' => $name, 'description' => $description, 'tags' => $tags, 'active' => $active, 'map' => $map, 'ordering' => $ordering, 'license' => $license, 'sharedPublic' => $sharedPublic, 'attachment' => $attachment, 'details' => $details, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDLastEdit' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = 'INSERT INTO pupilsightUnit SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID,pupilsightYearGroupID=:pupilsightYearGroupID,pupilsightCourseID=:pupilsightCourseID,pupilsightDepartmentID=:pupilsightDepartmentID, name=:name, description=:description, tags=:tags, active=:active, map=:map, ordering=:ordering, license=:license, sharedPublic=:sharedPublic, attachment=:attachment, details=:details, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit';
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
                    }

                    if ($partialFail == true) {
                        $URL .= '&return=warning1';
                        header("Location: {$URL}");
                    } else {
                        $URLSuccess = $URLSuccess."&return=success3&pupilsightUnitID=$AI";
                        header("Location: {$URLSuccess}");
                    }
                }
            }
        }
    }
}

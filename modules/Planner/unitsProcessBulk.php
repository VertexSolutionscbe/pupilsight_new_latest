<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseID = $_POST['pupilsightCourseID'];
$pupilsightCourseIDCopyTo = $_POST['pupilsightCourseIDCopyTo'];
$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
$action = $_POST['action'];

if ($pupilsightCourseID == '' or $pupilsightCourseIDCopyTo == '' or $pupilsightSchoolYearID == '' or $action == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/units.php&pupilsightCourseID=$pupilsightCourseID&pupilsightSchoolYearID=$pupilsightSchoolYearID";

    if (isActionAccessible($guid, $connection2, '/modules/Planner/units.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        $units = array();
        for ($i = 0; $i < $_POST['count']; ++$i) {
            if (isset($_POST["check-$i"])) {
                if ($_POST["check-$i"] == 'on') {
                    $units[$i] = $_POST["pupilsightUnitID-$i"];
                }
            }
        }

        //Proceed!
        //Check if person specified
        if (count($units) < 1) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            $partialFail = false;
            if ($action == 'Duplicate') {
                foreach ($units AS $pupilsightUnitID) { //For every unit to be copied
                    //Check existence of unit and fetch details
                    try {
                        $data = array('pupilsightUnitID' => $pupilsightUnitID);
                        $sql = 'SELECT * FROM pupilsightUnit WHERE pupilsightUnitID=:pupilsightUnitID';
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
                        $name = $row['name'];
                        if ($pupilsightCourseIDCopyTo == $pupilsightCourseID) {
                            $name .= ' (Copy)';
                        }

                        //Write the duplicate to the database
                        try {
                            $data = array('pupilsightCourseID' => $pupilsightCourseIDCopyTo, 'name' => $name, 'description' => $row['description'], 'map' => $row['map'], 'tags' => $row['tags'], 'ordering' => $row['ordering'], 'attachment' => $row['attachment'], 'details' => $row['details'], 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDLastEdit' => $_SESSION[$guid]['pupilsightPersonID']);
                            $sql = 'INSERT INTO pupilsightUnit SET pupilsightCourseID=:pupilsightCourseID, name=:name, description=:description, map=:map, tags=:tags, ordering=:ordering, attachment=:attachment, details=:details ,pupilsightPersonIDCreator=:pupilsightPersonIDCreator, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        //Last insert ID
                        $AI = str_pad($connection2->lastInsertID(), 10, '0', STR_PAD_LEFT);

                        //Copy Outcomes
                        try {
                            $dataOutcomes = array('pupilsightUnitID' => $pupilsightUnitID);
                            $sqlOutcomes = 'SELECT * FROM pupilsightUnitOutcome WHERE pupilsightUnitID=:pupilsightUnitID';
                            $resultOutcomes = $connection2->prepare($sqlOutcomes);
                            $resultOutcomes->execute($dataOutcomes);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                        if ($resultOutcomes->rowCount() > 0) {
                            while ($rowOutcomes = $resultOutcomes->fetch()) {
                                //Write to database
                                try {
                                    $dataCopy = array('pupilsightUnitID' => $AI, 'pupilsightOutcomeID' => $rowOutcomes['pupilsightOutcomeID'], 'sequenceNumber' => $rowOutcomes['sequenceNumber'], 'content' => $rowOutcomes['content']);
                                    $sqlCopy = 'INSERT INTO pupilsightUnitOutcome SET pupilsightUnitID=:pupilsightUnitID, pupilsightOutcomeID=:pupilsightOutcomeID, sequenceNumber=:sequenceNumber, content=:content';
                                    $resultCopy = $connection2->prepare($sqlCopy);
                                    $resultCopy->execute($dataCopy);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }
                            }
                        }

                        //Copy smart blocks
                        try {
                            $dataBlocks = array('pupilsightUnitID' => $pupilsightUnitID);
                            $sqlBlocks = 'SELECT * FROM pupilsightUnitBlock WHERE pupilsightUnitID=:pupilsightUnitID ORDER BY sequenceNumber';
                            $resultBlocks = $connection2->prepare($sqlBlocks);
                            $resultBlocks->execute($dataBlocks);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                        while ($rowBlocks = $resultBlocks->fetch()) {
                            try {
                                $dataBlock = array('pupilsightUnitID' => $AI, 'title' => $rowBlocks['title'], 'type' => $rowBlocks['type'], 'length' => $rowBlocks['length'], 'contents' => $rowBlocks['contents'], 'teachersNotes' => $rowBlocks['teachersNotes'], 'sequenceNumber' => $rowBlocks['sequenceNumber']);
                                $sqlBlock = 'INSERT INTO pupilsightUnitBlock SET pupilsightUnitID=:pupilsightUnitID, title=:title, type=:type, length=:length, contents=:contents, teachersNotes=:teachersNotes, sequenceNumber=:sequenceNumber';
                                $resultBlock = $connection2->prepare($sqlBlock);
                                $resultBlock->execute($dataBlock);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }
                }
            }
            else {
                $URL .= '&return=error3';
                header("Location: {$URL}");
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

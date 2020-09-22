<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightPlannerEntryID = $_POST['pupilsightPlannerEntryID'];
$mode = $_POST['mode'];
$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_view_full.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID".$_POST['params'];

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_view_full.php') == false) {
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
        if ($pupilsightPlannerEntryID == '' or $mode == '' or ($mode != 'view' and $mode != 'edit')) {
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

                $partialFail = false;
                if ($mode == 'view') {
                    $ids = $_POST['pupilsightUnitClassBlockID'];
                    for ($i = 0; $i < count($ids); ++$i) {
                        if ($ids[$i] == '') {
                            $partialFail = true;
                        } else {
                            $complete = 'N';
                            if (isset($_POST["complete$i"])) {
                                if ($_POST["complete$i"] == 'on') {
                                    $complete = 'Y';
                                }
                            }
                            //Write to database
                            try {
                                $data = array('complete' => $complete, 'pupilsightUnitClassBlockID' => $ids[$i]);
                                $sql = 'UPDATE pupilsightUnitClassBlock SET complete=:complete WHERE pupilsightUnitClassBlockID=:pupilsightUnitClassBlockID';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                echo 'Here';
                                echo $e->getMessage();
                                $partialFail = true;
                            }
                        }
                    }
                } else {
                    $order = $_POST['order'];
                    $seq = $_POST['minSeq'];

                    $summaryBlocks = '';
                    foreach ($order as $i) {
                        $id = $_POST["pupilsightUnitClassBlockID$i"];
                        $title = $_POST["title$i"];
                        $summaryBlocks .= $title.', ';
                        $type = $_POST["type$i"];
                        $length = $_POST["length$i"];
                        $contents = $_POST["contents$i"];
                        $teachersNotes = $_POST["teachersNotes$i"];
                        $complete = 'N';
                        if (isset($_POST["complete$i"])) {
                            if ($_POST["complete$i"] == 'on') {
                                $complete = 'Y';
                            }
                        }

                        //Write to database
                        try {
                            $data = array('title' => $title, 'type' => $type, 'length' => $length, 'contents' => $contents, 'teachersNotes' => $teachersNotes, 'complete' => $complete, 'sequenceNumber' => $seq, 'pupilsightUnitClassBlockID' => $id);
                            $sql = 'UPDATE pupilsightUnitClassBlock SET title=:title, type=:type, length=:length, contents=:contents, teachersNotes=:teachersNotes, complete=:complete, sequenceNumber=:sequenceNumber WHERE pupilsightUnitClassBlockID=:pupilsightUnitClassBlockID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                        ++$seq;
                    }

                    $summaryBlocks = substr($summaryBlocks, 0, -2);
                    if (strlen($summaryBlocks) > 75) {
                        $summaryBlocks = substr($summaryBlocks, 0, 72).'...';
                    }
                    if ($summaryBlocks) {
                        $summary = $summaryBlocks;
                    }

                    //Write to database
                    try {
                        $data = array('summary' => $summary, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                        $sql = 'UPDATE pupilsightPlannerEntry SET summary=:summary WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                }

                //Return final verdict
                if ($partialFail == true) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}

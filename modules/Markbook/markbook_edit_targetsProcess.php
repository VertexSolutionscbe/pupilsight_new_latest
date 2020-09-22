<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/markbook_edit_targets.php&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_edit_targets.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightCourseClassID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        $count = $_POST['count'];
        $pupilsightScaleIDTarget = $_POST['pupilsightScaleIDTarget'];
        if ($pupilsightScaleIDTarget == '')
            $pupilsightScaleIDTarget = null;
        $partialFail = false;

        //Update target scale
        try {
            $data = array('pupilsightScaleIDTarget' => $pupilsightScaleIDTarget, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sql = 'UPDATE pupilsightCourseClass SET pupilsightScaleIDTarget=:pupilsightScaleIDTarget WHERE pupilsightCourseClassID=:pupilsightCourseClassID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $partialFail = true;
        }

        for ($i = 1;$i <= $count;++$i) {
            $pupilsightPersonIDStudent = $_POST["$i-pupilsightPersonID"];
            $pupilsightScaleGradeID = null;
            if (!empty($_POST["$i-pupilsightScaleGradeID"])) {
                $pupilsightScaleGradeID = $_POST["$i-pupilsightScaleGradeID"];
            }

            $selectFail = false;
            try {
                $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonIDStudent' => $pupilsightPersonIDStudent);
                $sql = 'SELECT * FROM pupilsightMarkbookTarget WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonIDStudent=:pupilsightPersonIDStudent';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $partialFail = true;
                $selectFail = true;
            }
            if (!($selectFail)) {
                if ($result->rowCount() < 1) {
                    try {
                        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonIDStudent' => $pupilsightPersonIDStudent, 'pupilsightScaleGradeID' => $pupilsightScaleGradeID);
                        $sql = 'INSERT INTO pupilsightMarkbookTarget SET pupilsightCourseClassID=:pupilsightCourseClassID, pupilsightPersonIDStudent=:pupilsightPersonIDStudent, pupilsightScaleGradeID=:pupilsightScaleGradeID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo $e->getMessage();
                        $partialFail = true;
                    }
                } else {
                    $row = $result->fetch();
                    //Update
                    try {
                        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonIDStudent' => $pupilsightPersonIDStudent, 'pupilsightScaleGradeID' => $pupilsightScaleGradeID);
                        $sql = 'UPDATE pupilsightMarkbookTarget SET pupilsightScaleGradeID=:pupilsightScaleGradeID WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonIDStudent=:pupilsightPersonIDStudent';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                }
            }
        }

        //Return!
        if ($partialFail == true) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}

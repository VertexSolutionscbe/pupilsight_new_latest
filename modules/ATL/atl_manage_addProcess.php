<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';


$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/atl_manage_add.php&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/ATL/atl_manage_add.php') == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    if (empty($_POST)) {
        $URL .= '&return=error5';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Validate Inputs
        $pupilsightCourseClassIDMulti = null;
        if (isset($_POST['pupilsightCourseClassIDMulti'])) {
            $pupilsightCourseClassIDMulti = $_POST['pupilsightCourseClassIDMulti'];
            $pupilsightCourseClassIDMulti = array_unique($pupilsightCourseClassIDMulti);
        }
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
        $pupilsightPersonIDCreator = $_SESSION[$guid]['pupilsightPersonID'];
        $pupilsightPersonIDLastEdit = $_SESSION[$guid]['pupilsightPersonID'];

        //Lock markbook column table
        try {
            $sqlLock = 'LOCK TABLES atlColumn WRITE';
            $resultLock = $connection2->query($sqlLock);
        } catch (PDOException $e) {
            //Fail 2
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Get next groupingID
        try {
            $sqlGrouping = 'SELECT DISTINCT groupingID FROM atlColumn WHERE NOT groupingID IS NULL ORDER BY groupingID DESC';
            $resultGrouping = $connection2->query($sqlGrouping);
        } catch (PDOException $e) {
            //Fail 2
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        $rowGrouping = $resultGrouping->fetch();
        if (is_null($rowGrouping['groupingID'])) {
            $groupingID = 1;
        } else {
            $groupingID = ($rowGrouping['groupingID'] + 1);
        }

        if (is_array($pupilsightCourseClassIDMulti) == false or is_numeric($groupingID) == false or $groupingID < 1 or $name == '' or $description == '') {
            //Fail 3
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            $partialFail = false;

            foreach ($pupilsightCourseClassIDMulti as $pupilsightCourseClassIDSingle) {
                //Write to database
                try {
                    $data = array('groupingID' => $groupingID, 'pupilsightCourseClassID' => $pupilsightCourseClassIDSingle, 'name' => $name, 'description' => $description, 'pupilsightRubricID' => $pupilsightRubricID, 'completeDate' => $completeDate, 'complete' => $complete, 'pupilsightPersonIDCreator' => $pupilsightPersonIDCreator, 'pupilsightPersonIDLastEdit' => $pupilsightPersonIDLastEdit);
                    $sql = 'INSERT INTO atlColumn SET groupingID=:groupingID, pupilsightCourseClassID=:pupilsightCourseClassID, name=:name, description=:description, pupilsightRubricID=:pupilsightRubricID, completeDate=:completeDate, complete=:complete, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    //Fail 2
                    $partialFail = true;
                }
            }

            //Unlock module table
            try {
                $sql = 'UNLOCK TABLES';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
            }

            if ($partialFail != false) {
                //Success 0
                $URL .= '&return=error6';
                header("Location: {$URL}");
            } else {
                //Success 0
                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}

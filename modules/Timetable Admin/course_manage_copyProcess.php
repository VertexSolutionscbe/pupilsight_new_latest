<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightSchoolYearIDNext = $_GET['pupilsightSchoolYearIDNext'];
$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Timetable Admin/course_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearIDNext";

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/course_manage.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school years specified (current and next)
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearIDNext == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //GET CURRENT COURSES
        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT * FROM pupilsightCourse WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() < 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            $partialFail = false;
            while ($row = $result->fetch()) {
                //Write to database
                try {
                    $dataInsert = array('pupilsightSchoolYearID' => $pupilsightSchoolYearIDNext, 'pupilsightDepartmentID' => $row['pupilsightDepartmentID'], 'name' => $row['name'], 'nameShort' => $row['nameShort'], 'description' => $row['description'], 'pupilsightYearGroupIDList' => $row['pupilsightYearGroupIDList'], 'orderBy' => $row['orderBy']);
                    $sqlInsert = 'INSERT INTO pupilsightCourse SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightDepartmentID=:pupilsightDepartmentID, name=:name, nameShort=:nameShort, description=:description, pupilsightYearGroupIDList=:pupilsightYearGroupIDList, orderBy=:orderBy';
                    $resultInsert = $connection2->prepare($sqlInsert);
                    $resultInsert->execute($dataInsert);
                } catch (PDOException $e) {
                    $partialFail = true;
                }

                $AI = $connection2->lastInsertId();

                if ($AI != null) {
                    //NOW DEAL WITH CLASSES
                    try {
                        $dataClass = array('pupilsightCourseID' => $row['pupilsightCourseID']);
                        $sqlClass = 'SELECT * FROM pupilsightCourseClass WHERE pupilsightCourseID=:pupilsightCourseID';
                        $resultClass = $connection2->prepare($sqlClass);
                        $resultClass->execute($dataClass);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }

                    while ($rowClass = $resultClass->fetch()) {
                        //Write to database
                        try {
                            $dataInsert = array('pupilsightCourseID' => $AI, 'name' => $rowClass['name'], 'nameShort' => $rowClass['nameShort'], 'reportable' => $rowClass['reportable']);
                            $sqlInsert = 'INSERT INTO pupilsightCourseClass SET pupilsightCourseID=:pupilsightCourseID, name=:name, nameShort=:nameShort, reportable=:reportable';
                            $resultInsert = $connection2->prepare($sqlInsert);
                            $resultInsert->execute($dataInsert);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }
                }
            }

            if ($partialFail == true) {
                $URL .= '&return=error5';
                header("Location: {$URL}");
            } else {
                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}

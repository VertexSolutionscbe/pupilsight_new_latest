<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseID = $_GET['pupilsightCourseID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/course_manage_edit.php&pupilsightCourseID='.$pupilsightCourseID.'&pupilsightSchoolYearID='.$_POST['pupilsightSchoolYearID'];

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/course_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if special day specified
    if ($pupilsightCourseID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightCourseID' => $pupilsightCourseID);
            $sql = 'SELECT * FROM pupilsightCourse WHERE pupilsightCourseID=:pupilsightCourseID';
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
            //Validate Inputs
            if ($_POST['pupilsightDepartmentID'] != '') {
                $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
            } else {
                $pupilsightDepartmentID = null;
            }
            $name = $_POST['name'];
            $nameShort = $_POST['nameShort'];
            $orderBy = $_POST['orderBy'];
            $description = $_POST['description'];
            $map = $_POST['map'];
            $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
            $pupilsightYearGroupIDList = (isset($_POST['pupilsightYearGroupIDList']))? implode(',', $_POST['pupilsightYearGroupIDList']) : '';

            if ($name == '' or $nameShort == '' or $pupilsightSchoolYearID == '' or $map == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID);
                    $sql = 'SELECT * FROM pupilsightCourse WHERE (name=:name AND pupilsightSchoolYearID=:pupilsightSchoolYearID) AND NOT (pupilsightCourseID=:pupilsightCourseID)';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightDepartmentID' => $pupilsightDepartmentID, 'name' => $name, 'nameShort' => $nameShort, 'orderBy' => $orderBy, 'description' => $description, 'map' => $map, 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList, 'pupilsightCourseID' => $pupilsightCourseID);
                        $sql = 'UPDATE pupilsightCourse SET pupilsightDepartmentID=:pupilsightDepartmentID, name=:name, nameShort=:nameShort, orderBy=:orderBy, description=:description, map=:map, pupilsightYearGroupIDList=:pupilsightYearGroupIDList WHERE pupilsightCourseID=:pupilsightCourseID';
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

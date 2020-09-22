<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseClassID = $_POST['pupilsightCourseClassID'];
$pupilsightCourseID = $_POST['pupilsightCourseID'];
$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];

if ($pupilsightCourseID == '' or $pupilsightSchoolYearID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/course_manage_class_edit.php&pupilsightCourseID=$pupilsightCourseID&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightSchoolYearID=$pupilsightSchoolYearID";

    if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/course_manage_class_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if course specified
        if ($pupilsightCourseClassID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                $sql = 'SELECT * FROM pupilsightCourseClass WHERE pupilsightCourseClassID=:pupilsightCourseClassID';
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
                $name = $_POST['name'];
                $nameShort = $_POST['nameShort'];
                $reportable = $_POST['reportable'];
                $attendance = (isset($_POST['attendance']))? $_POST['attendance'] : 'N';

                if ($name == '' or $nameShort == '') {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Check unique inputs for uniquness
                    try {
                        $data = array('name' => $name, 'nameShort' => $nameShort, 'pupilsightCourseID' => $pupilsightCourseID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                        $sql = 'SELECT * FROM pupilsightCourseClass WHERE ((name=:name) OR (nameShort=:nameShort)) AND pupilsightCourseID=:pupilsightCourseID AND NOT pupilsightCourseClassID=:pupilsightCourseClassID';
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
                            $data = array('name' => $name, 'nameShort' => $nameShort, 'reportable' => $reportable, 'attendance' => $attendance, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                            $sql = 'UPDATE pupilsightCourseClass SET name=:name, nameShort=:nameShort, reportable=:reportable, attendance=:attendance WHERE pupilsightCourseClassID=:pupilsightCourseClassID';
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

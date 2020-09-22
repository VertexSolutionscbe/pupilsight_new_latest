<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightTTDayID = $_GET['pupilsightTTDayID'];
$pupilsightTTID = $_GET['pupilsightTTID'];
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightTTColumnRowID = $_GET['pupilsightTTColumnRowID'];
$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightTTDayRowClassID = $_GET['pupilsightTTDayRowClassID'];

if ($pupilsightTTDayID == '' or $pupilsightTTID == '' or $pupilsightSchoolYearID == '' or $pupilsightTTColumnRowID == '' or $pupilsightCourseClassID == '' or $pupilsightTTDayRowClassID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/tt_edit_day_edit_class_exception_add.php&pupilsightTTDayID=$pupilsightTTDayID&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightTTColumnRowID=$pupilsightTTColumnRowID&pupilsightTTDayRowClass=$pupilsightTTDayRowClassID&pupilsightCourseClassID=$pupilsightCourseClassID";

    if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit_day_edit_class_exception_add.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightTTDayID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightTTColumnRowID' => $pupilsightTTColumnRowID, 'pupilsightTTDayID' => $pupilsightTTDayID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                $sql = 'SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightTTDayRowClassID, pupilsightSpaceID FROM pupilsightTTDayRowClass JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightTTColumnRowID=:pupilsightTTColumnRowID AND pupilsightTTDayID=:pupilsightTTDayID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID';
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
                //Run through each of the selected participants.
                $update = true;
                $choices = $_POST['Members'];

                if (count($choices) < 1) {
                    $URL .= '&return=error1';
                    header("Location: {$URL}");
                } else {
                    foreach ($choices as $t) {
                        //Check to see if person is already exempted from this class
                        try {
                            $data = array('pupilsightPersonID' => $t, 'pupilsightTTDayRowClassID' => $pupilsightTTDayRowClassID);
                            $sql = 'SELECT * FROM pupilsightTTDayRowClassException WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightTTDayRowClassID=:pupilsightTTDayRowClassID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $update = false;
                        }

                        //If student not in course, add them
                        if ($result->rowCount() == 0) {
                            try {
                                $data = array('pupilsightPersonID' => $t, 'pupilsightTTDayRowClassID' => $pupilsightTTDayRowClassID);
                                $sql = 'INSERT INTO pupilsightTTDayRowClassException SET pupilsightPersonID=:pupilsightPersonID, pupilsightTTDayRowClassID=:pupilsightTTDayRowClassID';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $update = false;
                            }
                        }
                    }
                    //Write to database
                    if ($update == false) {
                        $URL .= '&return=error2';
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

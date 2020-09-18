<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightPlannerEntryID = $_GET['pupilsightPlannerEntryID'];
$pupilsightPlannerEntryGuestID = $_GET['pupilsightPlannerEntryGuestID'];
$viewBy = $_GET['viewBy'];
$subView = $_GET['subView'];
if ($viewBy != 'date' and $viewBy != 'class') {
    $viewBy = 'date';
}
$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$date = $_GET['date'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/planner_edit.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID";

//Params to pass back (viewBy + date or classID)
if ($viewBy == 'date') {
    $params = "&viewBy=$viewBy&date=$date";
} else {
    $params = "&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&subView=$subView";
}

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_edit.php') == false) {
    $URL .= "&return=error0$params";
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        //Proceed!

        //Check if school year specified
        if ($pupilsightPlannerEntryID == '' or $pupilsightPlannerEntryGuestID == '' or ($viewBy == 'class' and $pupilsightCourseClassID == 'Y')) {
            $URL .= "&return=error1$params";
            header("Location: {$URL}");
        } else {
            try {
                if ($viewBy == 'date') {
                    if ($highestAction == 'Lesson Planner_viewEditAllClasses') {
                        $data = array('date' => $date, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                        $sql = 'SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE date=:date AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                    } else {
                        $data = array('date' => $date, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = "SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, role FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND date=:date AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID";
                    }
                } else {
                    if ($highestAction == 'Lesson Planner_viewEditAllClasses') {
                        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                        $sql = 'SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                    } else {
                        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = "SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, role FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID";
                    }
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= "&return=error2$params";
                header("Location: {$URL}");
                exit();
            }

            if ($result->rowCount() != 1) {
                $URL .= "&return=error2$params";
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('pupilsightPlannerEntryGuestID' => $pupilsightPlannerEntryGuestID);
                    $sql = 'DELETE FROM pupilsightPlannerEntryGuest WHERE pupilsightPlannerEntryGuestID=:pupilsightPlannerEntryGuestID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= "&return=error2$params";
                    header("Location: {$URL}");
                    exit();
                }

                $URL .= "&return=success0$params";
                header("Location: {$URL}");
            }
        }
    }
}

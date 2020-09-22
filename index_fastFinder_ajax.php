<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include './pupilsight.php';

$themeName = 'Default';
if (isset($_SESSION[$guid]['pupilsightThemeName'])) {
    $themeName = $_SESSION[$guid]['pupilsightThemeName'];
}

if (isset($_SESSION[$guid]) == false or isset($_SESSION[$guid]['pupilsightPersonID']) == false) {
    die( __('Your request failed because you do not have access to this action.') );
} else {

    $searchTerm = (isset($_REQUEST['q']))? $_REQUEST['q'] : '';

    // Allow for * as wildcard (as well as %)
    $searchTerm = str_replace('*', '%', $searchTerm);

    // Cancel out early for empty searches
    if (empty($searchTerm)) die('[]');

    // Check access levels
    $studentIsAccessible = isActionAccessible($guid, $connection2, '/modules/students/student_view.php');
    $highestActionStudent = getHighestGroupedAction($guid, '/modules/students/student_view.php', $connection2);

    $staffIsAccessible = isActionAccessible($guid, $connection2, '/modules/Staff/staff_view.php');
    $classIsAccessible = false;
    $alarmIsAccessible = isActionAccessible($guid, $connection2, '/modules/System Admin/alarm.php');
    $highestActionClass = getHighestGroupedAction($guid, '/modules/Planner/planner.php', $connection2);
    if (isActionAccessible($guid, $connection2, '/modules/Planner/planner.php') and $highestActionClass != 'Lesson Planner_viewMyChildrensClasses') {
        $classIsAccessible = true;
    }

    $resultSet = array();
    $resultError = '[{"id":"","name":"Database Error"}]';

    // ACTIONS
    // Grab the cached set of translated actions from the session
    $actions = $pupilsight->session->get('fastFinderActions');

    if (empty($actions)) {
        $actions = $pupilsight->session->cacheFastFinderActions($pupilsight->session->get('pupilsightRoleIDCurrent'));
        $actions[] = array('');
    }
    
    if (!empty($actions) && is_array($actions)) {
        foreach ($actions as $action) {
            // Add actions that match the search query to the result set
            if (stristr($action['name'], $searchTerm) !== false) {
                $resultSet['Action'][] = $action;
            }

            // Handle the special Lockdown case
            if ($alarmIsAccessible) {
                if (stristr('Lockdown', $searchTerm) !== false && $action['name'] == 'Sound Alarm') {
                    $action['name'] = 'Lockdown';
                    $resultSet['Action'][] = $action;
                }
            }
        }
    }

    // CLASSES
    if ($classIsAccessible) {
        try {
            if ($highestActionClass == 'Lesson Planner_viewEditAllClasses' or $highestActionClass == 'Lesson Planner_viewAllEditMyClasses') {
                $data = array( 'search' => '%'.$searchTerm.'%', 'pupilsightSchoolYearID2' => $_SESSION[$guid]['pupilsightSchoolYearID'] );
                $sql = "SELECT pupilsightCourseClass.pupilsightCourseClassID AS id, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS name, NULL as type
                        FROM pupilsightCourseClass
                        JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID)
                        WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID2
                        AND (pupilsightCourse.name LIKE :search OR CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) LIKE :search)
                        ORDER BY name";
            } else {
                $data = array('search' => '%'.$searchTerm.'%', 'pupilsightSchoolYearID3' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'] );
                $sql = "SELECT pupilsightCourseClass.pupilsightCourseClassID AS id, CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS name, NULL as type
                        FROM pupilsightCourseClassPerson
                        JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                        JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID)
                        WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID3
                        AND pupilsightPersonID=:pupilsightPersonID
                        AND (pupilsightCourse.name LIKE :search OR CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) LIKE :search)
                        ORDER BY name";
            }
            $resultList = $connection2->prepare($sql);
            $resultList->execute($data);
        } catch (PDOException $e) { die($resultError); }

        if ($resultList->rowCount() > 0) $resultSet['Class'] = $resultList->fetchAll();
    }

    // STAFF
    if ($staffIsAccessible == true) {
        try {
            $data = array('search' => '%'.$searchTerm.'%', 'today' => date('Y-m-d') );
            $sql = "SELECT pupilsightPerson.pupilsightPersonID AS id,
                    (CASE WHEN pupilsightPerson.username LIKE :search
                        THEN concat(surname, ', ', preferredName, ' (', pupilsightPerson.username, ')')
                        ELSE concat(surname, ', ', preferredName) END) AS name,
                    NULL as type
                    FROM pupilsightPerson
                    JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                    WHERE status='Full'
                    AND (dateStart IS NULL OR dateStart<=:today)
                    AND (dateEnd IS NULL  OR dateEnd>=:today)
                    AND (pupilsightPerson.surname LIKE :search
                        OR pupilsightPerson.preferredName LIKE :search
                        OR pupilsightPerson.username LIKE :search)
                    ORDER BY name";
            $resultList = $connection2->prepare($sql);
            $resultList->execute($data);
        } catch (PDOException $e) { die($resultError); }

        if ($resultList->rowCount() > 0) $resultSet['Staff'] = $resultList->fetchAll();
    }

    // STUDENTS
    if ($studentIsAccessible == true) {

        $data = array('search' => '%'.$searchTerm.'%', 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'today' => date('Y-m-d') );

        // Allow parents to search students in any family they belong to
        if ($highestActionStudent == 'View Student Profile_myChildren') {
            $data['pupilsightPersonID'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sql = "SELECT pupilsightPerson.pupilsightPersonID AS id,
                    (CASE WHEN pupilsightPerson.username LIKE :search THEN concat(surname, ', ', preferredName, ' (', pupilsightRollGroup.name, ', ', pupilsightPerson.username, ')')
                        WHEN pupilsightPerson.studentID LIKE :search THEN concat(surname, ', ', preferredName, ' (', pupilsightRollGroup.name, ', ', pupilsightPerson.studentID, ')')
                        WHEN pupilsightPerson.firstName LIKE :search AND firstName<>preferredName THEN concat(surname, ', ', firstName, ' \"', preferredName, '\" (', pupilsightRollGroup.name, ')' )
                        ELSE concat(surname, ', ', preferredName, ' (', pupilsightRollGroup.name, ')') END) AS name,
                    NULL as type 
                    FROM pupilsightPerson, pupilsightStudentEnrolment, pupilsightRollGroup, pupilsightFamilyChild, pupilsightFamilyAdult
                    WHERE pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID
                    AND pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID 
                    AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID
                    AND pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID 
                    AND pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamilyAdult.pupilsightFamilyID";
        }
        // Allow individuals to only search themselves
        else if ($highestActionStudent == 'View Student Profile_my') {
            $data['pupilsightPersonID'] = $_SESSION[$guid]['pupilsightPersonID'];
            $sql = "SELECT pupilsightPerson.pupilsightPersonID AS id,
                    (CASE WHEN pupilsightPerson.username LIKE :search THEN concat(surname, ', ', preferredName, ' (', pupilsightRollGroup.name, ', ', pupilsightPerson.username, ')')
                        WHEN pupilsightPerson.studentID LIKE :search THEN concat(surname, ', ', preferredName, ' (', pupilsightRollGroup.name, ', ', pupilsightPerson.studentID, ')')
                        WHEN pupilsightPerson.firstName LIKE :search AND firstName<>preferredName THEN concat(surname, ', ', firstName, ' \"', preferredName, '\" (', pupilsightRollGroup.name, ')' )
                        ELSE concat(surname, ', ', preferredName, ' (', pupilsightRollGroup.name, ')') END) AS name,
                    NULL as type
                    FROM pupilsightPerson, pupilsightStudentEnrolment, pupilsightRollGroup
                    WHERE pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID
                    AND pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID 
                    AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID";
        }
        // Allow searching of all students
        else {
            $sql = "SELECT pupilsightPerson.pupilsightPersonID AS id,
                    (CASE WHEN pupilsightPerson.username LIKE :search THEN concat(surname, ', ', preferredName, ' (', pupilsightRollGroup.name, ', ', pupilsightPerson.username, ')')
                        WHEN pupilsightPerson.studentID LIKE :search THEN concat(surname, ', ', preferredName, ' (', pupilsightRollGroup.name, ', ', pupilsightPerson.studentID, ')')
                        WHEN pupilsightPerson.firstName LIKE :search AND firstName<>preferredName THEN concat(surname, ', ', firstName, ' \"', preferredName, '\" (', pupilsightRollGroup.name, ')' )
                        ELSE concat(surname, ', ', preferredName, ' (', pupilsightRollGroup.name, ')') END) AS name,
                    NULL as type
                    FROM pupilsightPerson, pupilsightStudentEnrolment, pupilsightRollGroup
                    WHERE pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID
                    AND pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID
                    AND status='Full'";
        }

        $sql.=" AND (dateStart IS NULL OR dateStart<=:today)
                AND (dateEnd IS NULL OR dateEnd>=:today)
                AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID
                AND (pupilsightPerson.surname LIKE :search
                    OR pupilsightPerson.firstName LIKE :search
                    OR pupilsightPerson.preferredName LIKE :search
                    OR pupilsightPerson.username LIKE :search
                    OR pupilsightPerson.studentID LIKE :search
                    OR pupilsightRollGroup.name LIKE :search)
                ORDER BY name";

        try {
            $resultList = $connection2->prepare($sql);
            $resultList->execute($data);
        } catch (PDOException $e) { die($resultError); }

        if ($resultList->rowCount() > 0) $resultSet['Student'] = $resultList->fetchAll();
    }

    $list = '';
    foreach ($resultSet as $type => $results) {
        foreach ($results as $token) {
            if ($token['type'] == 'Core') {
                $list .= '{"id": "'.substr($type, 0, 3).'-'.$token['id'].'", "name": "'.htmlPrep(__($type)).' - '.htmlPrep(__($token['name'])).'"},';
            }
            else if ($token['type'] == 'Additional') {
                $list .= '{"id": "'.substr($type, 0, 3).'-'.$token['id'].'", "name": "'.htmlPrep(__($type)).' - '.htmlPrep(__($token['name'], $token['module'])).'"},';
            }
            else {
                $list .= '{"id": "'.substr($type, 0, 3).'-'.$token['id'].'", "name": "'.htmlPrep(__($type)).' - '.htmlPrep($token['name']).'"},';
            }
        }
    }

    // Output the json
    echo '['.substr($list, 0, -1).']';
}

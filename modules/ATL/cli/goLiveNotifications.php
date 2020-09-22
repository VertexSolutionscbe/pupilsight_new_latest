<?php
/*
Pupilsight, Flexible & Open School System
*/

//USAGE
//Ideally this script should be run shortly after midnight, to alert users to columns that have just gone live

require getcwd().'/../../../pupilsight.php';

getSystemSettings($guid, $connection2);

setCurrentSchoolYear($guid, $connection2);

//Set up for i18n via gettext
if (isset($_SESSION[$guid]['i18n']['code'])) { if ($_SESSION[$guid]['i18n']['code'] != null) {
        putenv('LC_ALL='.$_SESSION[$guid]['i18n']['code']);
        setlocale(LC_ALL, $_SESSION[$guid]['i18n']['code']);
        bindtextdomain('pupilsight', getcwd().'/../i18n');
        textdomain('pupilsight');
    }
}


//Check for CLI, so this cannot be run through browser
if (php_sapi_name() != 'cli') {
    echo __('This script cannot be run from a browser, only via CLI.')."\n\n";
} else {
    //SCAN THROUGH ALL ATLS GOING LIVE TODAY
    try {
        $data = array('completeDate' => date('Y-m-d'));
        $sql = 'SELECT atlColumn.*, pupilsightCourseClass.nameShort AS class, pupilsightCourse.nameShort AS course FROM atlColumn JOIN pupilsightCourseClass ON (atlColumn.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE completeDate=:completeDate';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    while ($row = $result->fetch()) {
        try {
            $dataPerson = array('pupilsightCourseClassID' => $row['pupilsightCourseClassID'], 'today' => date('Y-m-d'));
            $sqlPerson = "SELECT pupilsightCourseClassPerson.*
                FROM pupilsightCourseClassPerson
                    JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                    JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                WHERE (role='Teacher' OR role='Student')
                    AND pupilsightCourseClassPerson.pupilsightCourseClassID=:pupilsightCourseClassID
                    AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<=:today) AND (dateEnd IS NULL  OR dateEnd>=:today)
                    AND pupilsightCourseClass.reportable='Y'
                    AND pupilsightCourseClassPerson.reportable='Y'";
            $resultPerson = $connection2->prepare($sqlPerson);
            $resultPerson->execute($dataPerson);
        } catch (PDOException $e) {
        }

        while ($rowPerson = $resultPerson->fetch()) {
            if ($rowPerson['role'] == 'Teacher') {
                $notificationText = sprintf(__('Your ATL column for class %1$s has gone live today.'), $row['course'].'.'.$row['class']);
                setNotification($connection2, $guid, $rowPerson['pupilsightPersonID'], $notificationText, 'ATL', '/index.php?q=/modules/ATL/atl_write.php&pupilsightCourseClassID='.$row['pupilsightCourseClassID']);
            } else {
                $notificationText = sprintf(__('You have new ATL assessment feedback for class %1$s.'), $row['course'].'.'.$row['class']);
                setNotification($connection2, $guid, $rowPerson['pupilsightPersonID'], $notificationText, 'ATL', '/index.php?q=/modules/ATL/atl_view.php');
            }
        }
    }
}

<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Domain\Staff\StaffAbsenceGateway;

//Checks whether or not a space is free over a given period of time, returning true or false accordingly.
function isSpaceFree($guid, $connection2, $foreignKey, $foreignKeyID, $date, $timeStart, $timeEnd)
{
    $return = true;

    //Check if school is open
    if (isSchoolOpen($guid, $date, $connection2) == false) {
        $return = false;
    } else {
        if ($foreignKey == 'pupilsightSpaceID') {
            //Check timetable including classes moved out
            $ttClear = false;
            try {
                $dataSpace = array('pupilsightSpaceID' => $foreignKeyID, 'date' => $date, 'timeStart1' => $timeStart, 'timeStart2' => $timeStart, 'timeStart3' => $timeStart, 'timeEnd1' => $timeEnd, 'timeStart4' => $timeStart, 'timeEnd2' => $timeEnd);
                $sqlSpace = 'SELECT pupilsightTTDayRowClass.pupilsightSpaceID, pupilsightTTDayDate.date, timeStart, timeEnd, pupilsightTTSpaceChangeID FROM pupilsightTTDayRowClass JOIN pupilsightTTColumnRow ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID) JOIN pupilsightTTDay ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightTTDayDate ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) LEFT JOIN pupilsightTTSpaceChange ON (pupilsightTTSpaceChange.pupilsightTTDayRowClassID=pupilsightTTDayRowClass.pupilsightTTDayRowClassID AND pupilsightTTSpaceChange.date=pupilsightTTDayDate.date) WHERE pupilsightTTDayRowClass.pupilsightSpaceID=:pupilsightSpaceID AND pupilsightTTDayDate.date=:date AND ((timeStart<=:timeStart1 AND timeEnd>:timeStart2) OR (timeStart>=:timeStart3 AND timeEnd<:timeEnd1) OR (timeStart>=:timeStart4 AND timeStart<:timeEnd2))';
                $resultSpace = $connection2->prepare($sqlSpace);
                $resultSpace->execute($dataSpace);
            } catch (PDOException $e) {
                $return = false;
            }
            if ($resultSpace->rowCount() < 1) {
                $ttClear = true;
            } else {
                $ttClashFixed = true;

                while ($rowSpace = $resultSpace->fetch()) {
                    if ($rowSpace['pupilsightTTSpaceChangeID'] == '') {
                        $ttClashFixed = false;
                    }
                }
                if ($ttClashFixed == true) {
                    $ttClear = true;
                }
            }

            if ($ttClear == false) {
                $return = false;
            } else {
                //Check room changes moving in
                try {
                    $dataSpace = array('pupilsightSpaceID' => $foreignKeyID, 'date1' => $date, 'date2' => $date, 'timeStart1' => $timeStart, 'timeStart2' => $timeStart, 'timeStart3' => $timeStart, 'timeEnd1' => $timeEnd, 'timeStart4' => $timeStart, 'timeEnd2' => $timeEnd);
                    $sqlSpace = 'SELECT * FROM pupilsightTTSpaceChange JOIN pupilsightTTDayRowClass ON (pupilsightTTSpaceChange.pupilsightTTDayRowClassID=pupilsightTTDayRowClass.pupilsightTTDayRowClassID) JOIN pupilsightTTColumnRow ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID) JOIN pupilsightTTDay ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightTTDayDate ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) WHERE pupilsightTTSpaceChange.pupilsightSpaceID=:pupilsightSpaceID AND pupilsightTTSpaceChange.date=:date1 AND pupilsightTTDayDate.date=:date2 AND ((timeStart<=:timeStart1 AND timeEnd>:timeStart2) OR (timeStart>=:timeStart3 AND timeEnd<:timeEnd1) OR (timeStart>=:timeStart4 AND timeStart<:timeEnd2))';
                    $resultSpace = $connection2->prepare($sqlSpace);
                    $resultSpace->execute($dataSpace);
                } catch (PDOException $e) {
                    $return = false;
                }

                if ($resultSpace->rowCount() > 0) {
                    $return = false;
                } else {
                    //Check bookings
                    try {
                        $dataSpace = array('foreignKeyID' => $foreignKeyID, 'date' => $date, 'timeStart1' => $timeStart, 'timeStart2' => $timeStart, 'timeStart3' => $timeStart, 'timeEnd1' => $timeEnd, 'timeStart4' => $timeStart, 'timeEnd2' => $timeEnd);
                        $sqlSpace = "SELECT * FROM pupilsightTTSpaceBooking WHERE foreignKey='pupilsightSpaceID' AND foreignKeyID=:foreignKeyID AND date=:date AND ((timeStart<=:timeStart1 AND timeEnd>:timeStart2) OR (timeStart>=:timeStart3 AND timeEnd<:timeEnd1) OR (timeStart>=:timeStart4 AND timeStart<:timeEnd2))";
                        $resultSpace = $connection2->prepare($sqlSpace);
                        $resultSpace->execute($dataSpace);
                    } catch (PDOException $e) {
                        $return = false;
                    }
                    if ($resultSpace->rowCount() > 0) {
                        $return = false;
                    }
                }
            }
        } elseif ($foreignKey == 'pupilsightLibraryItemID') {
            //Check bookings
            try {
                $dataSpace = array('foreignKeyID' => $foreignKeyID, 'date' => $date, 'timeStart1' => $timeStart, 'timeStart2' => $timeStart, 'timeStart3' => $timeStart, 'timeEnd1' => $timeEnd, 'timeStart4' => $timeStart, 'timeEnd2' => $timeEnd);
                $sqlSpace = "SELECT * FROM pupilsightTTSpaceBooking WHERE foreignKey='pupilsightLibraryItemID' AND foreignKeyID=:foreignKeyID AND date=:date AND ((timeStart<=:timeStart1 AND timeEnd>:timeStart2) OR (timeStart>=:timeStart3 AND timeEnd<:timeEnd1) OR (timeStart>=:timeStart4 AND timeStart<:timeEnd2))";
                $resultSpace = $connection2->prepare($sqlSpace);
                $resultSpace->execute($dataSpace);
            } catch (PDOException $e) {
                $return = false;
            }
            if ($resultSpace->rowCount() > 0) {
                $return = false;
            }
        }
    }

    return $return;
}

//Returns space bookings for the specified user for the 7 days on/after $startDayStamp, or for all users for the 7 days on/after $startDayStamp if no user specified
function getSpaceBookingEvents($guid, $connection2, $startDayStamp, $pupilsightPersonID = '')
{
    $return = false;

    try {
        if ($pupilsightPersonID != '') {
            $dataSpaceBooking = array('pupilsightPersonID1' => $pupilsightPersonID, 'pupilsightPersonID2' => $pupilsightPersonID);
            $sqlSpaceBooking = "(SELECT pupilsightTTSpaceBooking.*, name, title, surname, preferredName FROM pupilsightTTSpaceBooking JOIN pupilsightSpace ON (pupilsightTTSpaceBooking.foreignKeyID=pupilsightSpace.pupilsightSpaceID) JOIN pupilsightPerson ON (pupilsightTTSpaceBooking.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE foreignKey='pupilsightSpaceID' AND pupilsightTTSpaceBooking.pupilsightPersonID=:pupilsightPersonID1 AND date>='" . date('Y-m-d', $startDayStamp) . "' AND  date<='" . date('Y-m-d', ($startDayStamp + (7 * 24 * 60 * 60))) . "') UNION (SELECT pupilsightTTSpaceBooking.*, name, title, surname, preferredName FROM pupilsightTTSpaceBooking JOIN pupilsightLibraryItem ON (pupilsightTTSpaceBooking.foreignKeyID=pupilsightLibraryItem.pupilsightLibraryItemID) JOIN pupilsightPerson ON (pupilsightTTSpaceBooking.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE foreignKey='pupilsightLibraryItemID' AND pupilsightTTSpaceBooking.pupilsightPersonID=:pupilsightPersonID2 AND date>='" . date('Y-m-d', $startDayStamp) . "' AND  date<='" . date('Y-m-d', ($startDayStamp + (7 * 24 * 60 * 60))) . "') ORDER BY date, timeStart, name";
        } else {
            $dataSpaceBooking = array();
            $sqlSpaceBooking = "(SELECT pupilsightTTSpaceBooking.*, name, title, surname, preferredName FROM pupilsightTTSpaceBooking JOIN pupilsightSpace ON (pupilsightTTSpaceBooking.foreignKeyID=pupilsightSpace.pupilsightSpaceID) JOIN pupilsightPerson ON (pupilsightTTSpaceBooking.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE foreignKey='pupilsightSpaceID' AND  date>='" . date('Y-m-d', $startDayStamp) . "' AND  date<='" . date('Y-m-d', ($startDayStamp + (7 * 24 * 60 * 60))) . "') UNION (SELECT pupilsightTTSpaceBooking.*, name, title, surname, preferredName FROM pupilsightTTSpaceBooking JOIN pupilsightLibraryItem ON (pupilsightTTSpaceBooking.foreignKeyID=pupilsightLibraryItem.pupilsightLibraryItemID) JOIN pupilsightPerson ON (pupilsightTTSpaceBooking.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE foreignKey='pupilsightLibraryItem' AND  date>='" . date('Y-m-d', $startDayStamp) . "' AND  date<='" . date('Y-m-d', ($startDayStamp + (7 * 24 * 60 * 60))) . "') ORDER BY date, timeStart, name";
        }
        $resultSpaceBooking = $connection2->prepare($sqlSpaceBooking);
        $resultSpaceBooking->execute($dataSpaceBooking);
    } catch (PDOException $e) {
    }
    if ($resultSpaceBooking->rowCount() > 0) {
        $return = array();
        $count = 0;
        while ($rowSpaceBooking = $resultSpaceBooking->fetch()) {
            $return[$count][0] = $rowSpaceBooking['pupilsightTTSpaceBookingID'];
            $return[$count][1] = $rowSpaceBooking['name'];
            $return[$count][2] = $rowSpaceBooking['pupilsightPersonID'];
            $return[$count][3] = $rowSpaceBooking['date'];
            $return[$count][4] = $rowSpaceBooking['timeStart'];
            $return[$count][5] = $rowSpaceBooking['timeEnd'];
            $return[$count][6] = Format::name($rowSpaceBooking['title'], $rowSpaceBooking['preferredName'], $rowSpaceBooking['surname'], 'Staff');
            ++$count;
        }
    }

    return $return;
}

//Returns space bookings for the specified space for the 7 days on/after $startDayStamp
function getSpaceBookingEventsSpace($guid, $connection2, $startDayStamp, $pupilsightSpaceID)
{
    $return = false;

    try {
        $dataSpaceBooking = array('pupilsightSpaceID' => $pupilsightSpaceID);
        $sqlSpaceBooking = "SELECT pupilsightTTSpaceBooking.*, name, title, surname, preferredName FROM pupilsightTTSpaceBooking JOIN pupilsightSpace ON (pupilsightTTSpaceBooking.foreignKeyID=pupilsightSpace.pupilsightSpaceID) JOIN pupilsightPerson ON (pupilsightTTSpaceBooking.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE foreignKey='pupilsightSpaceID' AND pupilsightTTSpaceBooking.foreignKeyID=:pupilsightSpaceID AND date>='" . date('Y-m-d', $startDayStamp) . "' AND  date<='" . date('Y-m-d', ($startDayStamp + (7 * 24 * 60 * 60))) . "' ORDER BY date, timeStart, name";
        $resultSpaceBooking = $connection2->prepare($sqlSpaceBooking);
        $resultSpaceBooking->execute($dataSpaceBooking);
    } catch (PDOException $e) {
    }
    if ($resultSpaceBooking->rowCount() > 0) {
        $return = array();
        $count = 0;
        while ($rowSpaceBooking = $resultSpaceBooking->fetch()) {
            $return[$count][0] = $rowSpaceBooking['pupilsightTTSpaceBookingID'];
            $return[$count][1] = $rowSpaceBooking['name'];
            $return[$count][2] = $rowSpaceBooking['pupilsightPersonID'];
            $return[$count][3] = $rowSpaceBooking['date'];
            $return[$count][4] = $rowSpaceBooking['timeStart'];
            $return[$count][5] = $rowSpaceBooking['timeEnd'];
            $return[$count][6] = Format::name($rowSpaceBooking['title'], $rowSpaceBooking['preferredName'], $rowSpaceBooking['surname'], 'Staff');
            ++$count;
        }
    }

    return $return;
}

//Returns events from a Google Calendar XML field, between the time and date specified
function getCalendarEvents($connection2, $guid, $xml, $startDayStamp, $endDayStamp)
{
    global $container;

    $googleOAuth = getSettingByScope($connection2, 'System', 'googleOAuth');

    if ($googleOAuth == 'Y' and isset($_SESSION[$guid]['googleAPIAccessToken'])) {
        $eventsSchool = array();
        $start = date("Y-m-d\TH:i:s", strtotime(date('Y-m-d', $startDayStamp)));
        $end = date("Y-m-d\TH:i:s", (strtotime(date('Y-m-d', $endDayStamp)) + 86399));

        $service = $container->get('Google_Service_Calendar');
        $getFail = empty($service);

        $calendarListEntry = array();
        try {
            $optParams = array('timeMin' => $start . '+00:00', 'timeMax' => $end . '+00:00', 'singleEvents' => true);
            $calendarListEntry = $service->events->listEvents($xml, $optParams);
        } catch (Exception $e) {
            $getFail = true;
        }

        if ($getFail) {
            $eventsSchool = false;
        } else {
            $count = 0;
            foreach ($calendarListEntry as $entry) {
                $multiDay = false;
                if (substr($entry['start']['dateTime'], 0, 10) != substr($entry['end']['dateTime'], 0, 10)) {
                    $multiDay = true;
                }
                if ($entry['start']['dateTime'] == '') {
                    if ((strtotime($entry['end']['date']) - strtotime($entry['start']['date'])) / (60 * 60 * 24) > 1) {
                        $multiDay = true;
                    }
                }

                if ($multiDay) { //This event spans multiple days
                    if ($entry['start']['date'] != $entry['start']['end']) {
                        $days = (strtotime($entry['end']['date']) - strtotime($entry['start']['date'])) / (60 * 60 * 24);
                    } elseif (substr($entry['start']['dateTime'], 0, 10) != substr($entry['end']['dateTime'], 0, 10)) {
                        $days = (strtotime(substr($entry['end']['dateTime'], 0, 10)) - strtotime(substr($entry['start']['dateTime'], 0, 10))) / (60 * 60 * 24);
                        ++$days; //A hack for events that span multiple days with times set
                    }
                    for ($i = 0; $i < $days; ++$i) {
                        //WHAT
                        $eventsSchool[$count][0] = $entry['summary'];

                        //WHEN - treat events that span multiple days, but have times set, the same as those without time set
                        $eventsSchool[$count][1] = 'All Day';
                        $eventsSchool[$count][2] = strtotime($entry['start']['date']) + ($i * 60 * 60 * 24);
                        $eventsSchool[$count][3] = null;

                        //WHERE
                        $eventsSchool[$count][4] = $entry['location'];

                        //LINK
                        $eventsSchool[$count][5] = $entry['htmlLink'];

                        ++$count;
                    }
                } else {  //This event falls on a single day
                    //WHAT
                    $eventsSchool[$count][0] = $entry['summary'];

                    //WHEN
                    if ($entry['start']['dateTime'] != '') { //Part of day
                        $eventsSchool[$count][1] = 'Specified Time';
                        $eventsSchool[$count][2] = strtotime(substr($entry['start']['dateTime'], 0, 10) . ' ' . substr($entry['start']['dateTime'], 11, 8));
                        $eventsSchool[$count][3] = strtotime(substr($entry['end']['dateTime'], 0, 10) . ' ' . substr($entry['end']['dateTime'], 11, 8));
                    } else { //All day
                        $eventsSchool[$count][1] = 'All Day';
                        $eventsSchool[$count][2] = strtotime($entry['start']['date']);
                        $eventsSchool[$count][3] = null;
                    }
                    //WHERE
                    $eventsSchool[$count][4] = $entry['location'];

                    //LINK
                    $eventsSchool[$count][5] = $entry['htmlLink'];

                    ++$count;
                }
            }
        }
    } else {
        $eventsSchool = false;
    }

    return $eventsSchool;
}

//TIMETABLE FOR INDIVIUDAL
//$narrow can be "full", "narrow", or "trim" (between narrow and full)
function renderTT($guid, $connection2, $pupilsightPersonID, $pupilsightTTID, $title = '', $startDayStamp = '', $q = '', $params = '', $narrow = 'full', $edit = false)
{
    $zCount = 0;
    $output = '';
    $proceed = false;

    $highestAction = getHighestGroupedAction($guid, '/modules/Timetable/tt.php', $connection2);

    if ($highestAction == 'View Timetable by Person_allYears') {
        $proceed = true;
    } else if ($_SESSION[$guid]['pupilsightSchoolYearIDCurrent'] == $_SESSION[$guid]['pupilsightSchoolYearID']) {

        if ($highestAction == 'View Timetable by Person') {
            $proceed = true;
        } else if ($highestAction == 'View Timetable by Person_my') {
            if ($pupilsightPersonID == $_SESSION[$guid]['pupilsightPersonID']) {
                $proceed = true;
            }
        } else if ($highestAction == 'View Timetable by Person_myChildren') {
            try {
                $data = array('pupilsightPersonID1' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonID2' => $pupilsightPersonID);
                $sql = "SELECT pupilsightFamilyChild.pupilsightPersonID FROM pupilsightFamilyChild
                    JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamilyChild.pupilsightFamilyID)
                    WHERE pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID1 AND pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID2 AND pupilsightFamilyAdult.childDataAccess='Y'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }

            if ($result->rowCount() > 0) {
                $proceed = true;
            }
        }
    }

    if ($proceed == false) {
        $output .= "<div class='alert alert-danger'>" . __('You do not have permission to access this timetable at this time.') . '</div>';
    } else {
        $self = false;
        if ($pupilsightPersonID == $_SESSION[$guid]['pupilsightPersonID'] and $edit == false) {
            $self = true;
            //Update display choices
            if ($_SESSION[$guid]['viewCalendarSchool'] != false and $_SESSION[$guid]['viewCalendarPersonal'] != false and $_SESSION[$guid]['viewCalendarSpaceBooking'] != false) {
                try {
                    $dataDisplay = array('viewCalendarSchool' => $_SESSION[$guid]['viewCalendarSchool'], 'viewCalendarPersonal' => $_SESSION[$guid]['viewCalendarPersonal'], 'viewCalendarSpaceBooking' => $_SESSION[$guid]['viewCalendarSpaceBooking'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sqlDisplay = 'UPDATE pupilsightPerson SET viewCalendarSchool=:viewCalendarSchool, viewCalendarPersonal=:viewCalendarPersonal, viewCalendarSpaceBooking=:viewCalendarSpaceBooking WHERE pupilsightPersonID=:pupilsightPersonID';
                    $resultDisplay = $connection2->prepare($sqlDisplay);
                    $resultDisplay->execute($dataDisplay);
                } catch (PDOException $e) {
                    $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                }
            }
        }

        $blank = true;
        if ($startDayStamp == '') {
            $startDayStamp = time();
        }

        //Find out which timetables I am involved in this year
        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID);
            $sql = "SELECT DISTINCT pupilsightTT.pupilsightTTID, pupilsightTT.name, pupilsightTT.nameShortDisplay FROM pupilsightTT JOIN pupilsightTTDay ON (pupilsightTT.pupilsightTTID=pupilsightTTDay.pupilsightTTID) JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' ";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }

        //If I am not involved in any timetables display all within the year
        if ($result->rowCount() == 0) {
            try {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sql = "SELECT pupilsightTT.pupilsightTTID, pupilsightTT.name, pupilsightTT.nameShortDisplay FROM pupilsightTT WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' ";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
        }

        //link to other TTs
        if ($result->rowcount() > 1) {
            $output .= "<table class='table'>";
            $output .= '<tr>';
            $output .= '<td>';
            $output .= "<span class='form-label'>" . __('Timetable Chooser') . '</span>: ';
            while ($row = $result->fetch()) {
                $output .= "<form method='post' action='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=$q&pupilsightTTID=" . $row['pupilsightTTID'] . "$params'>";
                $output .= "<input name='ttDate' value='" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], $startDayStamp) . "' type='hidden'>";
                $output .= "<input name='schoolCalendar' value='" . $_SESSION[$guid]['viewCalendarSchool'] . "' type='hidden'>";
                $output .= "<input name='personalCalendar' value='" . $_SESSION[$guid]['viewCalendarPersonal'] . "' type='hidden'>";
                $output .= "<input name='spaceBookingCalendar' value='" . $_SESSION[$guid]['viewCalendarSpaceBooking'] . "' type='hidden'>";
                $output .= "<input name='fromTT' value='Y' type='hidden'>";
                $output .= "<input class='btn btn-link' style='min-width: 30px; margin-top: 0px; float: left' type='submit' value='" . $row['name'] . "'>";
                $output .= '</form>';
            }
            try {
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
            $output .= '</td>';
            $output .= '</tr>';
            $output .= '</table>';

            if ($pupilsightTTID != '') {
                if (isActionAccessible($guid, $connection2, '/modules/Timetable/tt_master.php', 'View Master Timetable')) {
                    $data = array('pupilsightTTID' => $pupilsightTTID);
                    $sql = "SELECT pupilsightTT.pupilsightTTID, pupilsightTT.name, pupilsightTT.nameShortDisplay FROM pupilsightTT WHERE pupilsightTT.pupilsightTTID=:pupilsightTTID";
                } else {
                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightTTID' => $pupilsightTTID);
                    $sql = "SELECT DISTINCT pupilsightTT.pupilsightTTID, pupilsightTT.name, pupilsightTT.nameShortDisplay FROM pupilsightTT JOIN pupilsightTTDay ON (pupilsightTT.pupilsightTTID=pupilsightTTDay.pupilsightTTID) JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightPersonID=$pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightTT.pupilsightTTID=:pupilsightTTID";
                }
            }
            try {
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
        }

        //Display first TT
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $pupilsightTTID = $row['pupilsightTTID'];
            $nameShortDisplay = $row['nameShortDisplay']; //Store day short name display setting for later
            $thisWeek = time();

            if ($title != false) {
                $output .= '<h2>' . $row['name'] . '</h2>';
            }
            $output .= "<table class='table'>";
            $output .= '<tr>';
            $output .= "<td style='vertical-align: top;width:300px'>";
            $output .= "<form method='post' action='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=$q&pupilsightTTID=" . $row['pupilsightTTID'] . "$params'>";
            $output .= "<input name='ttDate' value='" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($startDayStamp - (7 * 24 * 60 * 60))) . "' type='hidden'>";
            $output .= "<input name='schoolCalendar' value='" . $_SESSION[$guid]['viewCalendarSchool'] . "' type='hidden'>";
            $output .= "<input name='personalCalendar' value='" . $_SESSION[$guid]['viewCalendarPersonal'] . "' type='hidden'>";
            $output .= "<input name='spaceBookingCalendar' value='" . $_SESSION[$guid]['viewCalendarSpaceBooking'] . "' type='hidden'>";
            $output .= "<input name='fromTT' value='Y' type='hidden'>";
            $output .= "<input class='btn btn-link' style='min-width: 30px; margin-top: 0px; float: left' type='submit' value='< " . __('Last Week') . "'>";
            $output .= '</form>';
            $output .= "<form method='post' action='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=$q&pupilsightTTID=" . $row['pupilsightTTID'] . "$params'>";
            $output .= "<input name='ttDate' value='" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($thisWeek)) . "' type='hidden'>";
            $output .= "<input name='schoolCalendar' value='" . $_SESSION[$guid]['viewCalendarSchool'] . "' type='hidden'>";
            $output .= "<input name='personalCalendar' value='" . $_SESSION[$guid]['viewCalendarPersonal'] . "' type='hidden'>";
            $output .= "<input name='spaceBookingCalendar' value='" . $_SESSION[$guid]['viewCalendarSpaceBooking'] . "' type='hidden'>";
            $output .= "<input name='fromTT' value='Y' type='hidden'>";
            $output .= "<input class='btn btn-link' style='min-width: 30px; margin-top: 0px; float: left' type='submit' value='" . __('This Week') . "'>";
            $output .= '</form>';
            $output .= "<form method='post' action='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=$q&pupilsightTTID=" . $row['pupilsightTTID'] . "$params'>";
            $output .= "<input name='ttDate' value='" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($startDayStamp + (7 * 24 * 60 * 60))) . "' type='hidden'>";
            $output .= "<input name='schoolCalendar' value='" . $_SESSION[$guid]['viewCalendarSchool'] . "' type='hidden'>";
            $output .= "<input name='personalCalendar' value='" . $_SESSION[$guid]['viewCalendarPersonal'] . "' type='hidden'>";
            $output .= "<input name='spaceBookingCalendar' value='" . $_SESSION[$guid]['viewCalendarSpaceBooking'] . "' type='hidden'>";
            $output .= "<input name='fromTT' value='Y' type='hidden'>";
            $output .= "<input class='btn btn-link' style='min-width: 30px; margin-top: 0px; float: left' type='submit' value='" . __('Next Week') . " >'>";
            $output .= '</form>';
            $output .= '</td>';
            $output .= "<td style='vertical-align: top; text-align: right'>";
            $output .= "<form method='post' action='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=$q&pupilsightTTID=" . $row['pupilsightTTID'] . "$params'>";
            $output .= '<span class="relative">';
            $output .= "<input name='ttDate' id='ttDate' maxlength=10 value='" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], $startDayStamp) . "' type='text' style='height: 28px; width:100px; margin-right: 0px; float: none'> ";
            $output .= '</span>';
            $output .= '<script type="text/javascript">';
            $output .= "var ttDate=new LiveValidation('ttDate');";
            $output .= 'ttDate.add( Validate.Format, {pattern:';
            if ($_SESSION[$guid]['i18n']['dateFormatRegEx'] == '') {
                $output .= "/^(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$/i";
            } else {
                $output .= $_SESSION[$guid]['i18n']['dateFormatRegEx'];
            }
            $output .= ', failureMessage: "Use ';
            if ($_SESSION[$guid]['i18n']['dateFormat'] == '') {
                $output .= 'dd/mm/yyyy';
            } else {
                $output .= $_SESSION[$guid]['i18n']['dateFormat'];
            }
            $output .= '." } );';
            $output .= 'ttDate.add(Validate.Presence);';
            $output .= '$("#ttDate").datepicker();';
            $output .= '</script>';

            $output .= "<input style='margin-top: 0px; margin-right: -2px' type='submit' value='" . __('Go') . "'>";
            $output .= "<input name='schoolCalendar' value='" . $_SESSION[$guid]['viewCalendarSchool'] . "' type='hidden'>";
            $output .= "<input name='personalCalendar' value='" . $_SESSION[$guid]['viewCalendarPersonal'] . "' type='hidden'>";
            $output .= "<input name='spaceBookingCalendar' value='" . $_SESSION[$guid]['viewCalendarSpaceBooking'] . "' type='hidden'>";
            $output .= "<input name='fromTT' value='Y' type='hidden'>";
            $output .= '</form>';
            $output .= '</td>';
            $output .= '</tr>';
            $output .= '</table>';

            //Check which days are school days
            $daysInWeek = 0;
            $days = array();
            $timeStart = '';
            $timeEnd = '';
            try {
                $dataDays = array();
                $sqlDays = "SELECT * FROM pupilsightDaysOfWeek WHERE schoolDay='Y' ORDER BY sequenceNumber";
                $resultDays = $connection2->prepare($sqlDays);
                $resultDays->execute($dataDays);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
            $days = $resultDays->fetchAll();
            $daysInWeek = $resultDays->rowCount();
            foreach ($days as $day) {
                if ($timeStart == '' or $timeEnd == '') {
                    $timeStart = $day['schoolStart'];
                    $timeEnd = $day['schoolEnd'];
                } else {
                    if ($day['schoolStart'] < $timeStart) {
                        $timeStart = $day['schoolStart'];
                    }
                    if ($day['schoolEnd'] > $timeEnd) {
                        $timeEnd = $day['schoolEnd'];
                    }
                }
            }

            //Sunday week adjust for timetable on home page (so Sundays show next week if the week starts on Sunday or Monday—i.e. it's Sunday now and Sunday is not a school day)
            $homeSunday = true;
            if ($q == '' && ($_SESSION[$guid]['firstDayOfTheWeek'] == 'Monday' || $_SESSION[$guid]['firstDayOfTheWeek'] == 'Sunday')) {
                try {
                    $dataDays = array();
                    $sqlDays = "SELECT nameShort FROM pupilsightDaysOfWeek WHERE nameShort='Sun' AND schoolDay='N'";
                    $resultDays = $connection2->prepare($sqlDays);
                    $resultDays->execute($dataDays);
                } catch (PDOException $e) {
                    echo $e->getMessage();
                }
                if ($resultDays->rowCount() == 1) {
                    $homeSunday = false;
                }
            }

            //If school is closed on Sunday, and it is a Sunday, count forward, otherwise count back
            if (!$homeSunday and date('D', $startDayStamp) == 'Sun') {
                $startDayStamp = $startDayStamp + 86400;
            } else {
                while (date('D', $startDayStamp) != $days[0]['nameShort']) {
                    $startDayStamp = $startDayStamp - 86400;
                }
            }

            //Count forward to the end of the week
            $endDayStamp = $startDayStamp + (86400 * ($daysInWeek - 1));

            $schoolCalendarAlpha = 0.85;
            $ttAlpha = 1.0;

            if ($_SESSION[$guid]['viewCalendarSchool'] != 'N' or $_SESSION[$guid]['viewCalendarPersonal'] != 'N' or $_SESSION[$guid]['viewCalendarSpaceBooking'] != 'N') {
                $ttAlpha = 0.75;
            }

            //Get school calendar array
            $allDay = false;
            $eventsSchool = false;
            if ($self == true and $_SESSION[$guid]['viewCalendarSchool'] == 'Y') {
                if ($_SESSION[$guid]['calendarFeed'] != '') {
                    $eventsSchool = getCalendarEvents($connection2, $guid,  $_SESSION[$guid]['calendarFeed'], $startDayStamp, $endDayStamp);
                }
                //Any all days?
                if ($eventsSchool != false) {
                    foreach ($eventsSchool as $event) {
                        if ($event[1] == 'All Day') {
                            $allDay = true;
                        }
                    }
                }
            }

            //Get personal calendar array
            $eventsPersonal = false;
            if ($self == true and $_SESSION[$guid]['viewCalendarPersonal'] == 'Y') {
                if ($_SESSION[$guid]['calendarFeedPersonal'] != '') {
                    $eventsPersonal = getCalendarEvents($connection2, $guid,  $_SESSION[$guid]['calendarFeedPersonal'], $startDayStamp, $endDayStamp);
                }
                //Any all days?
                if ($eventsPersonal != false) {
                    foreach ($eventsPersonal as $event) {
                        if ($event[1] == 'All Day') {
                            $allDay = true;
                        }
                    }
                }
            }

            $spaceBookingAvailable = isActionAccessible($guid, $connection2, '/modules/Timetable/spaceBooking_manage.php');
            $eventsSpaceBooking = false;
            if ($spaceBookingAvailable) {
                //Get space booking array
                if ($self == true and $_SESSION[$guid]['viewCalendarSpaceBooking'] == 'Y') {
                    $eventsSpaceBooking = getSpaceBookingEvents($guid, $connection2, $startDayStamp, $_SESSION[$guid]['pupilsightPersonID']);
                }
            }

            // STAFF COVERAGE
            // Add coverage as a space booking *for now*
            global $container;
            $staffCoverageGateway = $container->get(StaffCoverageGateway::class);

            $criteria = $staffCoverageGateway->newQueryCriteria()
                ->filterBy('startDate', date('Y-m-d', $startDayStamp))
                ->filterBy('endDate', date('Y-m-d', $endDayStamp))
                ->filterBy('status', 'Accepted')
                ->pageSize(0);
            $coverageList = $staffCoverageGateway->queryCoverageByPersonCovering($criteria, $pupilsightPersonID);

            foreach ($coverageList as $coverage) {
                $fullName = Format::name($coverage['titleAbsence'], $coverage['preferredNameAbsence'], $coverage['surnameAbsence'], 'Staff', false, true);
                if (empty($fullName)) {
                    $fullName = Format::name($coverage['titleStatus'], $coverage['preferredNameStatus'], $coverage['surnameStatus'], 'Staff', false, true);
                }

                $eventsSpaceBooking[] = [
                    'Coverage',
                    __('Coverage'),
                    '',
                    $coverage['date'],
                    $coverage['allDay'] == 'N' ? $coverage['timeStart'] : $timeStart,
                    $coverage['allDay'] == 'N' ? $coverage['timeEnd'] : $timeEnd,
                    $fullName,
                    '',
                ];
            }

            // STAFF ABSENCE
            // Add an absence as a fake all-day personal event, so it doesn't overlap the calendar (which subs need to see!)
            $staffAbsenceGateway = $container->get(StaffAbsenceGateway::class);

            $criteria = $staffAbsenceGateway->newQueryCriteria()
                ->filterBy('dateStart', date('Y-m-d', $startDayStamp))
                ->filterBy('dateEnd', date('Y-m-d', $endDayStamp))
                ->filterBy('status', 'Approved')
                ->pageSize(0);
            $absenceList = $staffAbsenceGateway->queryAbsencesByPerson($criteria, $pupilsightPersonID, false);
            $canViewAbsences = isActionAccessible($guid, $connection2, '/modules/Staff/absences_view_byPerson.php');

            foreach ($absenceList as $absence) {
                $summary = __('Absent');
                if ($absence['coverage'] == 'Accepted') {
                    $summary .= ' - ' . __('Coverage') . ': ' . Format::name($absence['titleCoverage'], $absence['preferredNameCoverage'], $absence['surnameCoverage'], 'Staff', false, true);
                }
                $allDay = true;
                $url = $canViewAbsences
                    ? $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Staff/absences_view_details.php&pupilsightStaffAbsenceID=' . $absence['pupilsightStaffAbsenceID']
                    : '';
                $eventsPersonal[] = [$summary, 'All Day', strtotime($absence['date']), null, '', $url];
            }

            //Count up max number of all day events in a day
            $eventsCombined = false;
            $maxAllDays = 0;
            if ($allDay == true) {
                if ($eventsPersonal != false and $eventsSchool != false) {
                    $eventsCombined = array_merge($eventsSchool, $eventsPersonal);
                } elseif ($eventsSchool != false) {
                    $eventsCombined = $eventsSchool;
                } elseif ($eventsPersonal != false) {
                    $eventsCombined = $eventsPersonal;
                }

                $eventsCombined = msort($eventsCombined, 2, true);

                $currentAllDays = 0;
                $lastDate = '';
                $currentDate = '';
                foreach ($eventsCombined as $event) {
                    if ($event[1] == 'All Day') {
                        $currentDate = date('Y-m-d', $event[2]);
                        if ($lastDate != $currentDate) {
                            $currentAllDays = 0;
                        }
                        ++$currentAllDays;

                        if ($currentAllDays > $maxAllDays) {
                            $maxAllDays = $currentAllDays;
                        }

                        $lastDate = $currentDate;
                    }
                }
            }

            //Max diff time for week based on timetables
            try {
                $dataDiff = array('date1' => date('Y-m-d', ($startDayStamp + (86400 * 0))), 'date2' => date('Y-m-d', ($endDayStamp + (86400 * 1))), 'pupilsightTTID' => $row['pupilsightTTID']);
                $sqlDiff = 'SELECT DISTINCT pupilsightTTColumn.pupilsightTTColumnID FROM pupilsightTTDay JOIN pupilsightTTDayDate ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID) JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) WHERE (date>=:date1 AND date<=:date2) AND pupilsightTTID=:pupilsightTTID';
                $resultDiff = $connection2->prepare($sqlDiff);
                $resultDiff->execute($dataDiff);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
            while ($rowDiff = $resultDiff->fetch()) {
                try {
                    $dataDiffDay = array('pupilsightTTColumnID' => $rowDiff['pupilsightTTColumnID']);
                    $sqlDiffDay = 'SELECT * FROM pupilsightTTColumnRow WHERE pupilsightTTColumnID=:pupilsightTTColumnID ORDER BY timeStart';
                    $resultDiffDay = $connection2->prepare($sqlDiffDay);
                    $resultDiffDay->execute($dataDiffDay);
                } catch (PDOException $e) {
                    $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                }
                while ($rowDiffDay = $resultDiffDay->fetch()) {
                    if ($rowDiffDay['timeStart'] < $timeStart) {
                        $timeStart = $rowDiffDay['timeStart'];
                    }
                    if ($rowDiffDay['timeEnd'] > $timeEnd) {
                        $timeEnd = $rowDiffDay['timeEnd'];
                    }
                }
            }

            //Max diff time for week based on special days timing change
            try {
                $dataDiff = array('date1' => date('Y-m-d', ($startDayStamp + (86400 * 0))), 'date2' => date('Y-m-d', ($startDayStamp + (86400 * 6))));
                $sqlDiff = "SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date>=:date1 AND date<=:date2 AND type='Timing Change' AND NOT schoolStart IS NULL AND NOT schoolEnd IS NULL";
                $resultDiff = $connection2->prepare($sqlDiff);
                $resultDiff->execute($dataDiff);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
            while ($rowDiff = $resultDiff->fetch()) {
                if ($rowDiff['schoolStart'] < $timeStart) {
                    $timeStart = $rowDiff['schoolStart'];
                }
                if ($rowDiff['schoolEnd'] > $timeEnd) {
                    $timeEnd = $rowDiff['schoolEnd'];
                }
            }

            //Max diff based on school calendar events
            if ($self == true and $eventsSchool != false) {
                foreach ($eventsSchool as $event) {
                    if (date('Y-m-d', $event[2]) <= date('Y-m-d', ($startDayStamp + (86400 * 6)))) {
                        if ($event[1] != 'All Day') {
                            if (date('H:i:s', $event[2]) < $timeStart) {
                                $timeStart = date('H:i:s', $event[2]);
                            }
                            if (date('H:i:s', $event[3]) > $timeEnd) {
                                $timeEnd = date('H:i:s', $event[3]);
                            }
                            if (date('Y-m-d', $event[2]) != date('Y-m-d', $event[3])) {
                                $timeEnd = '23:59:59';
                            }
                        }
                    }
                }
            }

            //Max diff based on personal calendar events
            if ($self == true and $eventsPersonal != false) {
                foreach ($eventsPersonal as $event) {
                    if (date('Y-m-d', $event[2]) <= date('Y-m-d', ($startDayStamp + (86400 * 6)))) {
                        if ($event[1] != 'All Day') {
                            if (date('H:i:s', $event[2]) < $timeStart) {
                                $timeStart = date('H:i:s', $event[2]);
                            }
                            if (date('H:i:s', $event[3]) > $timeEnd) {
                                $timeEnd = date('H:i:s', $event[3]);
                            }
                            if (date('Y-m-d', $event[2]) != date('Y-m-d', $event[3])) {
                                $timeEnd = '23:59:59';
                            }
                        }
                    }
                }
            }

            //Max diff based on space booking events
            if ($self == true and $eventsSpaceBooking != false) {
                foreach ($eventsSpaceBooking as $event) {
                    if ($event[3] <= date('Y-m-d', ($startDayStamp + (86400 * 6)))) {
                        if ($event[4] < $timeStart) {
                            $timeStart = $event[4];
                        }
                        if ($event[5] > $timeEnd) {
                            $timeEnd = $event[5];
                        }
                    }
                }
            }

            //Final calc
            $diffTime = strtotime($timeEnd) - strtotime($timeStart);

            if ($narrow == 'trim') {
                $width = (ceil(640 / $daysInWeek) - 20) . 'px';
            } elseif ($narrow == 'narrow') {
                $width = (ceil(515 / $daysInWeek) - 20) . 'px';
            } else {
                $width = (ceil(690 / $daysInWeek) - 20) . 'px';
            }

            $count = 0;

            $output .= '<div class="overflow-x-scroll sm:overflow-x-auto overflow-y-hidden mb-6">';
            /*$output .= "<table class='table mini mb-1' border='1' style='border: 1px solid rgba(110, 117, 130, 0.2);width: ";
            if ($narrow == 'trim') {
                $output .= '700px';
            } elseif ($narrow == 'narrow') {
                $output .= '575px';
            } else {
                $output .= '750px';
            }
            $output .= ";'>";*/
            $output .= "<table class='table mb-1' border='1' style='border: 1px solid rgba(110, 117, 130, 0.2);";
            //Spit out controls for displaying calendars
            /*
            if ($self == true and ($_SESSION[$guid]['calendarFeed'] != '' or $_SESSION[$guid]['calendarFeedPersonal'] != '' or $_SESSION[$guid]['viewCalendarSpaceBooking'] != '')) {
                $output .= "<tr class='head' style='height: 37px;'>";
                $output .= "<th class='ttCalendarBar' colspan=" . ($daysInWeek + 1) . '>';
                $output .= "<form method='post' action='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=$q" . $params . "' style='padding: 5px 5px 0 0'>";
                if ($_SESSION[$guid]['calendarFeed'] != '' and $_SESSION[$guid]['googleAPIAccessToken'] != null) {
                    $checked = '';
                    if ($_SESSION[$guid]['viewCalendarSchool'] == 'Y') {
                        $checked = 'checked';
                    }
                    $output .= "<span class='ttSchoolCalendar' style='opacity: $schoolCalendarAlpha'>" . __('School Calendar');
                    $output .= "<input $checked style='margin-left: 3px' type='checkbox' name='schoolCalendar' onclick='submit();'/>";
                    $output .= '</span>';
                }
                if ($_SESSION[$guid]['calendarFeedPersonal'] != '' and isset($_SESSION[$guid]['googleAPIAccessToken'])) {
                    $checked = '';
                    if ($_SESSION[$guid]['viewCalendarPersonal'] == 'Y') {
                        $checked = 'checked';
                    }
                    $output .= "<span class='ttPersonalCalendar' style='opacity: $schoolCalendarAlpha'>" . __('Personal Calendar');
                    $output .= "<input $checked style='margin-left: 3px' type='checkbox' name='personalCalendar' onclick='submit();'/>";
                    $output .= '</span>';
                }
                if ($spaceBookingAvailable) {
                    if ($_SESSION[$guid]['viewCalendarSpaceBooking'] != '') {
                        $checked = '';
                        if ($_SESSION[$guid]['viewCalendarSpaceBooking'] == 'Y') {
                            $checked = 'checked';
                        }
                        $output .= "<span class='ttSpaceBookingCalendar' style='opacity: $schoolCalendarAlpha'><a href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Timetable/spaceBooking_manage.php'>" . __('Bookings') . '</a> ';
                        $output .= "<input $checked style='margin-left: 3px' type='checkbox' name='spaceBookingCalendar' onclick='submit();'/>";
                        $output .= '</span>';
                    }
                }

                $output .= "<input type='hidden' name='ttDate' value='" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], $startDayStamp) . "'>";
                $output .= "<input name='fromTT' value='Y' type='hidden'>";
                $output .= '</form>';
                $output .= '</th>';
                $output .= '</tr>';
            }
            */

            $output .= "<tr class='head'>";
            $output .= "<th style='vertical-align: top; width: 50px !important; text-align: center'>";
            //Calculate week number
            $week = getWeekNumber($startDayStamp, $connection2, $guid);
            if ($week != false) {
                $output .= sprintf(__('Week %1$s'), $week) . '<br/>';
            }
            $output .= "<span style='font-weight: normal; font-style: italic;'>" . __('Time') . '<span>';
            $output .= '</th>';
            $count = 0;
            foreach ($days as $day) {
                if ($day['schoolDay'] == 'Y') {
                    if ($count == 0) {
                        $firstSequence = $day['sequenceNumber'];
                    }
                    $dateCorrection = ($day['sequenceNumber'] - 1) - ($firstSequence - 1);

                    unset($rowDay);
                    $color = '';
                    try {
                        $dataDay = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $count))), 'pupilsightTTID' => $pupilsightTTID);
                        $sqlDay = 'SELECT nameShort, color, fontColor FROM pupilsightTTDay JOIN pupilsightTTDayDate ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID) WHERE date=:date AND pupilsightTTID=:pupilsightTTID';
                        $resultDay = $connection2->prepare($sqlDay);
                        $resultDay->execute($dataDay);
                    } catch (PDOException $e) {
                    }
                    if ($resultDay->rowCount() == 1) {
                        $rowDay = $resultDay->fetch();
                        if ($rowDay['color'] != '') {
                            $color .= "; background-color: #" . $rowDay['color'] . "; background-image: none";
                        }
                        if ($rowDay['fontColor'] != '') {
                            $color .= "; color: #" . $rowDay['fontColor'];
                        }
                    }

                    $today = ((date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($startDayStamp + (86400 * $dateCorrection))) == date($_SESSION[$guid]['i18n']['dateFormatPHP'])) ? "class='ttToday'" : '');
                    $output .= "<th $today style='vertical-align: top; text-align: center; width: ";

                    if ($narrow == 'trim') {
                        $output .= (550 / $daysInWeek);
                    } elseif ($narrow == 'narrow') {
                        $output .= (375 / $daysInWeek);
                    } else {
                        $output .= (550 / $daysInWeek);
                    }
                    $output .= "px" . $color . "'>";
                    if ($nameShortDisplay != 'Timetable Day Short Name') {
                        $output .= __($day['nameShort']) . '<br/>';
                    } else {
                        if (!empty($rowDay['nameShort']) && $rowDay['nameShort'] != '') {
                            $output .= $rowDay['nameShort'] . '<br/>';
                        } else {
                            $output .= __($day['nameShort']) . '<br/>';
                        }
                    }
                    $output .= "<span style='font-size: 80%; font-style: italic'>" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($startDayStamp + (86400 * $dateCorrection))) . '</span><br/>';
                    try {
                        $dataSpecial = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))));
                        $sqlSpecial = "SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date=:date AND type='Timing Change'";
                        $resultSpecial = $connection2->prepare($sqlSpecial);
                        $resultSpecial->execute($dataSpecial);
                    } catch (PDOException $e) {
                        $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                    }
                    if ($resultSpecial->rowcount() == 1) {
                        $rowSpecial = $resultSpecial->fetch();
                        $output .= "<span style='font-size: 80%; font-weight: bold'><u>" . $rowSpecial['name'] . '</u></span>';
                    }
                    $output .= '</th>';
                }
                $count++;
            }
            $output .= '</tr>';

            //Space for all day events
            if (($eventsSchool == true or $eventsPersonal == true) and $allDay == true and $eventsCombined != null) {
                $output .= "<tr style='height: " . ((31 * $maxAllDays) + 5) . "px'>";
                $output .= "<td style='vertical-align: top;text-align: center; border-top: 1px solid #888; border-bottom: 1px solid #888'>";
                $output .= "<span style='font-size: 80%'><b>" . sprintf(__('All Day%1$s Events'), '<br/>') . '</b></span>';
                $output .= '</td>';
                $output .= "<td colspan=$daysInWeek style='vertical-align: top;text-align: center; border-top: 1px solid #888; border-bottom: 1px solid #888'>";
                $output .= '</td>';
                $output .= '</tr>';
            }

            $output .= "<tr style='height:" . (ceil($diffTime / 60) + 14) . "px'>";
            $output .= "<td class='ttTime' style='height: 300px;text-align: center; vertical-align: top'>";
            $output .= "<div style='position: relative;'>";
            $countTime = 0;
            $time = $timeStart;
            $output .= "<div $title style='z-index: " . $zCount . "; position: absolute; top: -3px;border: none; width: 100%;height: 60px; margin: 0px; padding: 0px; font-size: 92%'>";
            $output .= substr($time, 0, 5) . '<br/>';
            $output .= '</div>';
            $time = date('H:i:s', strtotime($time) + 3600);
            $spinControl = 0;
            while ($time <= $timeEnd and $spinControl < (23 - substr($timeStart, 0, 2))) {
                ++$countTime;
                $output .= "<div $title style='z-index: $zCount; position: absolute; top:" . (($countTime * 60) - 5) . "px ;border: none; width: 100%;height: 60px; margin: 0px; padding: 0px; font-size: 92%'>";
                $output .= substr($time, 0, 5) . '<br/>';
                $output .= '</div>';
                $time = date('H:i:s', strtotime($time) + 3600);
                ++$spinControl;
            }

            $output .= '</div>';
            $output .= '</td>';

            //Run through days of the week
            foreach ($days as $day) {
                if ($day['schoolDay'] == 'Y') {
                    $dateCorrection = ($day['sequenceNumber'] - 1) - ($firstSequence - 1);

                    //Check to see if day is term time
                    $isDayInTerm = false;
                    try {
                        $dataTerm = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                        $sqlTerm = 'SELECT pupilsightSchoolYearTerm.firstDay, pupilsightSchoolYearTerm.lastDay FROM pupilsightSchoolYearTerm, pupilsightSchoolYear WHERE pupilsightSchoolYearTerm.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID AND pupilsightSchoolYear.pupilsightSchoolYearID=:pupilsightSchoolYearID';
                        $resultTerm = $connection2->prepare($sqlTerm);
                        $resultTerm->execute($dataTerm);
                    } catch (PDOException $e) {
                        $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                    }
                    while ($rowTerm = $resultTerm->fetch()) {
                        if (date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))) >= $rowTerm['firstDay'] and date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))) <= $rowTerm['lastDay']) {
                            $isDayInTerm = true;
                        }
                    }

                    if ($isDayInTerm == true) {
                        //Check for school closure day
                        try {
                            $dataClosure = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))));
                            $sqlClosure = 'SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date=:date';
                            $resultClosure = $connection2->prepare($sqlClosure);
                            $resultClosure->execute($dataClosure);
                        } catch (PDOException $e) {
                            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                        }
                        if ($resultClosure->rowCount() == 1) {
                            $rowClosure = $resultClosure->fetch();
                            if ($rowClosure['type'] == 'School Closure') {
                                $day = renderTTDay($guid, $connection2, $row['pupilsightTTID'], false, $startDayStamp, $dateCorrection, $daysInWeek, $pupilsightPersonID, $timeStart, $eventsSchool, $eventsPersonal, $eventsSpaceBooking, $diffTime, $maxAllDays, $narrow, '', '', $edit);
                            } elseif ($rowClosure['type'] == 'Timing Change') {
                                $day = renderTTDay($guid, $connection2, $row['pupilsightTTID'], true, $startDayStamp, $dateCorrection, $daysInWeek, $pupilsightPersonID, $timeStart, $eventsSchool, $eventsPersonal, $eventsSpaceBooking, $diffTime, $maxAllDays, $narrow, $rowClosure['schoolStart'], $rowClosure['schoolEnd'], $edit);
                            }
                        } else {
                            $day = renderTTDay($guid, $connection2, $row['pupilsightTTID'], true, $startDayStamp, $dateCorrection, $daysInWeek, $pupilsightPersonID, $timeStart, $eventsSchool, $eventsPersonal, $eventsSpaceBooking, $diffTime, $maxAllDays, $narrow, '', '', $edit);
                        }
                    } else {
                        $day = renderTTDay($guid, $connection2, $row['pupilsightTTID'], false, $startDayStamp, $dateCorrection, $daysInWeek, $pupilsightPersonID, $timeStart, $eventsSchool, $eventsPersonal, $eventsSpaceBooking, $diffTime, $maxAllDays, $narrow, '', '', $edit);
                    }

                    if ($day == '') {
                        $day .= "<td style='font-size: 12px;padding:0 !important;'></td>";
                    }

                    $output .= $day;
                }
            }
            $output .= '</tr>';
            $output .= '</table>';
            $output .= '</div>';
        }
    }

    return $output;
}

function renderTTDay($guid, $connection2, $pupilsightTTID, $schoolOpen, $startDayStamp, $count, $daysInWeek, $pupilsightPersonID, $gridTimeStart, $eventsSchool, $eventsPersonal, $eventsSpaceBooking, $diffTime, $maxAllDays, $narrow, $specialDayStart = '', $specialDayEnd = '', $edit = false)
{
    $schoolCalendarAlpha = 0.90;
    $ttAlpha = 1.0;

    if ($_SESSION[$guid]['viewCalendarSchool'] != 'N' or $_SESSION[$guid]['viewCalendarPersonal'] != 'N' or $_SESSION[$guid]['viewCalendarSpaceBooking'] != 'N') {
        $ttAlpha = 0.75;
    }

    $date = date('Y-m-d', ($startDayStamp + (86400 * $count)));

    $self = false;
    if ($pupilsightPersonID == $_SESSION[$guid]['pupilsightPersonID'] and $edit == false) {
        $self = true;
        $roleCategory = getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2);
    }
    /*
    if ($narrow == 'trim') {
        $width = (ceil(640 / $daysInWeek) - 20) . 'px';
    } elseif ($narrow == 'narrow') {
        $width = (ceil(515 / $daysInWeek) - 20) . 'px';
    } else {
        $width = (ceil(690 / $daysInWeek) - 20) . 'px';
    }*/
    $width = "100%";
    $output = '';
    $blank = true;

    $zCount = 0;
    $allDay = 0;

    if ($schoolOpen == false) {
        try {
            $dataSpecialDay = array('date' => $date);
            $sqlSpecialDay = "SELECT name, description FROM pupilsightSchoolYearSpecialDay WHERE date=:date";
            $resultSpecialDay = $connection2->prepare($sqlSpecialDay);
            $resultSpecialDay->execute($dataSpecialDay);
        } catch (PDOException $e) {
        }

        $specialDay = $resultSpecialDay->rowCount() > 0 ? $resultSpecialDay->fetch() : array('name' => '', 'description' => '');

        $output .= "<td style='text-align: center; vertical-align: top; font-size: 11px'>";
        $output .= "<div style='position: relative'>";
        $output .= "<div class='ttClosure' style='z-index: $zCount; position: absolute; width: $width ; height: " . ceil($diffTime / 60) . "px; margin: 0px; padding: 0px; opacity: $ttAlpha'>";
        $output .= "<div style='position: relative; top: 50%' title='" . $specialDay['description'] . "'>";
        $output .= "<span style='color: rgba(255,0,0,$ttAlpha);'>" . __('School Closed');
        $output .= '<br/><br/>' . $specialDay['name'] . '</span>';
        $output .= '</div>';
        $output .= '</div>';

        $zCount = 1;

        //Draw periods from school calendar
        if ($eventsSchool != false) {
            $height = 0;
            $top = 0;
            $dayTimeStart = '';
            foreach ($eventsSchool as $event) {
                if (date('Y-m-d', $event[2]) == date('Y-m-d', ($startDayStamp + (86400 * $count)))) {
                    if ($event[1] == 'All Day') {
                        $label = $event[0];
                        $title = '';
                        if (strlen($label) > 20) {
                            $label = substr($label, 0, 20) . '...';
                            $title = "title='" . $event[0] . "'";
                        }
                        $height = '30px';
                        $top = (($maxAllDays * -31) - 8 + ($allDay * 30)) . 'px';
                        $output .= "<div class='ttSchoolCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                        $output .= "<a target='_blank'  href='" . $event[5] . "'>" . $label . '</a>';
                        $output .= '</div>';
                        ++$allDay;
                    } else {
                        $label = $event[0];
                        $title = "title='" . date('H:i', $event[2]) . ' to ' . date('H:i', $event[3]) . "'";
                        $height = ceil(($event[3] - $event[2]) / 60) . 'px';
                        $charCut = 20;
                        if ($height < 20) {
                            $charCut = 12;
                        }
                        if (strlen($label) > $charCut) {
                            $label = substr($label, 0, $charCut) . '...';
                            $title = "title='" . $event[0] . ' (' . date('H:i', $event[2]) . ' to ' . date('H:i', $event[3]) . ")'";
                        }
                        $top = (ceil(($event[2] - strtotime(date('Y-m-d', $startDayStamp + (86400 * $count)) . ' ' . $gridTimeStart)) / 60)) . 'px';
                        $output .= "<div class='ttSchoolCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                        $output .= "<a target='_blank' href='" . $event[5] . "'>" . $label . '</a>';
                        $output .= '</div>';
                    }
                    ++$zCount;
                }
            }
        }

        //Draw periods from personal calendar
        if ($eventsPersonal != false) {
            $height = 0;
            $top = 0;
            $bg = "rgba(103,153,207,$schoolCalendarAlpha)";
            foreach ($eventsPersonal as $event) {
                if (date('Y-m-d', $event[2]) == date('Y-m-d', ($startDayStamp + (86400 * $count)))) {
                    if ($event[1] == 'All Day') {
                        $label = $event[0];
                        $title = '';
                        if (strlen($label) > 20) {
                            $label = substr($label, 0, 20) . '...';
                            $title = "title='" . $event[0] . "'";
                        }
                        $height = '30px';
                        $top = (($maxAllDays * -31) - 8 + ($allDay * 30)) . 'px';
                        $output .= "<div class='ttPersonalCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                        $output .= !empty($event[5])
                            ? "<a target='_blank' href='" . $event[5] . "'>" . $label . '</a>'
                            : $label;
                        $output .= '</div>';
                        ++$allDay;
                    } else {
                        $label = $event[0];
                        $title = "title='" . date('H:i', $event[2]) . ' to ' . date('H:i', $event[3]) . "'";
                        $height = ceil(($event[3] - $event[2]) / 60) . 'px';
                        $charCut = 20;
                        if ($height < 20) {
                            $charCut = 12;
                        }
                        if (strlen($label) > $charCut) {
                            $label = substr($label, 0, $charCut) . '...';
                            $title = "title='" . $event[0] . ' (' . date('H:i', $event[2]) . ' to ' . date('H:i', $event[3]) . ")'";
                        }
                        $top = (ceil(($event[2] - strtotime(date('Y-m-d', $startDayStamp + (86400 * $count)) . ' ' . $gridTimeStart)) / 60)) . 'px';
                        $output .= "<div class='ttPersonalCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                        $output .= !empty($event[5])
                            ? "<a target='_blank' href='" . $event[5] . "'>" . $label . '</a>'
                            : $label;
                        $output .= '</div>';
                    }
                    ++$zCount;
                }
            }
        }
        $output .= '</div>';
        $output .= '</td>';
    } else {
        //Make array of space changes
        $spaceChanges = array();
        try {
            $dataSpaceChange = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $count))));
            $sqlSpaceChange = 'SELECT pupilsightTTSpaceChange.*, pupilsightSpace.name AS space, phoneInternal FROM pupilsightTTSpaceChange LEFT JOIN pupilsightSpace ON (pupilsightTTSpaceChange.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) WHERE date=:date';
            $resultSpaceChange = $connection2->prepare($sqlSpaceChange);
            $resultSpaceChange->execute($dataSpaceChange);
        } catch (PDOException $e) {
        }
        while ($rowSpaceChange = $resultSpaceChange->fetch()) {
            $spaceChanges[$rowSpaceChange['pupilsightTTDayRowClassID']][0] = $rowSpaceChange['space'];
            $spaceChanges[$rowSpaceChange['pupilsightTTDayRowClassID']][1] = $rowSpaceChange['phoneInternal'];
        }

        //Get day start and end!
        $dayTimeStart = '';
        $dayTimeEnd = '';
        try {
            $dataDiff = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $count))), 'pupilsightTTID' => $pupilsightTTID);
            $sqlDiff = 'SELECT timeStart, timeEnd FROM pupilsightTTDay JOIN pupilsightTTDayDate ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID) JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTColumnRow ON (pupilsightTTColumn.pupilsightTTColumnID=pupilsightTTColumnRow.pupilsightTTColumnID) WHERE date=:date AND pupilsightTTID=:pupilsightTTID';
            $resultDiff = $connection2->prepare($sqlDiff);
            $resultDiff->execute($dataDiff);
        } catch (PDOException $e) {
            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }
        while ($rowDiff = $resultDiff->fetch()) {
            if ($dayTimeStart == '') {
                $dayTimeStart = $rowDiff['timeStart'];
            }
            if ($rowDiff['timeStart'] < $dayTimeStart) {
                $dayTimeStart = $rowDiff['timeStart'];
            }
            if ($dayTimeEnd == '') {
                $dayTimeEnd = $rowDiff['timeEnd'];
            }
            if ($rowDiff['timeEnd'] > $dayTimeEnd) {
                $dayTimeEnd = $rowDiff['timeEnd'];
            }
        }
        if ($specialDayStart != '') {
            $dayTimeStart = $specialDayStart;
        }
        if ($specialDayEnd != '') {
            $dayTimeEnd = $specialDayEnd;
        }

        $dayDiffTime = strtotime($dayTimeEnd) - strtotime($dayTimeStart);

        $startPad = strtotime($dayTimeStart) - strtotime($gridTimeStart);

        $today = ((date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($startDayStamp + (86400 * $count))) == date($_SESSION[$guid]['i18n']['dateFormatPHP'])) ? "class='ttToday'" : '');
        $output .= "<td $today style='font-size: 12px;padding:0 !important;'>";

        try {
            $dataDay = array('pupilsightTTID' => $pupilsightTTID, 'date' => date('Y-m-d', ($startDayStamp + (86400 * $count))));
            $sqlDay = 'SELECT pupilsightTTDay.pupilsightTTDayID FROM pupilsightTTDayDate JOIN pupilsightTTDay ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) WHERE pupilsightTTID=:pupilsightTTID AND date=:date';
            $resultDay = $connection2->prepare($sqlDay);
            $resultDay->execute($dataDay);
        } catch (PDOException $e) {
            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }

        if ($resultDay->rowCount() == 1) {
            $rowDay = $resultDay->fetch();
            $zCount = 0;
            $output .= "<div style='position: relative'>";

            //Draw outline of the day
            try {
                $dataPeriods = array('pupilsightTTDayID' => $rowDay['pupilsightTTDayID'], 'date' => date('Y-m-d', ($startDayStamp + (86400 * $count))));
                $sqlPeriods = 'SELECT pupilsightTTColumnRow.pupilsightTTColumnRowID, pupilsightTTColumnRow.name, timeStart, timeEnd, type, date, pupilsightTTDay.pupilsightTTDayID FROM pupilsightTTDay JOIN pupilsightTTDayDate ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID) JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTColumnRow ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) WHERE pupilsightTTDayDate.pupilsightTTDayID=:pupilsightTTDayID AND date=:date ORDER BY timeStart, timeEnd';
                $resultPeriods = $connection2->prepare($sqlPeriods);
                $resultPeriods->execute($dataPeriods);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }

            while ($rowPeriods = $resultPeriods->fetch()) {
                $sqlcs = 'SELECT c.officialName , d.name FROM pupilsightTTDayRowClass AS a LEFT JOIN pupilsightStaff AS b ON a.pupilsightStaffID = b.pupilsightStaffID LEFT JOIN pupilsightPerson AS c ON b.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN pupilsightDepartment AS d ON a.pupilsightDepartmentID = d.pupilsightDepartmentID WHERE a.pupilsightTTColumnRowID = ' . $rowPeriods['pupilsightTTColumnRowID'] . ' AND a.pupilsightTTDayID = ' . $rowPeriods['pupilsightTTDayID'] . ' ';


                $resultcs = $connection2->query($sqlcs);
                $staffcs = $resultcs->fetch();


                if (!empty($staffcs)) {
                    $staffName = $staffcs['officialName'];
                    $subjectName = $staffcs['name'];
                } else {
                    $staffName = '';
                    $subjectName = '';
                }


                $isSlotInTime = false;
                if ($rowPeriods['timeStart'] <= $dayTimeStart and $rowPeriods['timeEnd'] > $dayTimeStart) {
                    $isSlotInTime = true;
                } elseif ($rowPeriods['timeStart'] >= $dayTimeStart and $rowPeriods['timeEnd'] <= $dayTimeEnd) {
                    $isSlotInTime = true;
                } elseif ($rowPeriods['timeStart'] < $dayTimeEnd and $rowPeriods['timeEnd'] >= $dayTimeEnd) {
                    $isSlotInTime = true;
                }

                if ($isSlotInTime == true) {
                    $effectiveStart = $rowPeriods['timeStart'];
                    $effectiveEnd = $rowPeriods['timeEnd'];
                    if ($dayTimeStart > $rowPeriods['timeStart']) {
                        $effectiveStart = $dayTimeStart;
                    }
                    if ($dayTimeEnd < $rowPeriods['timeEnd']) {
                        $effectiveEnd = $dayTimeEnd;
                    }

                    $height = ceil((strtotime($effectiveEnd) - strtotime($effectiveStart)) / 60) . 'px';
                    $top = ceil(((strtotime($effectiveStart) - strtotime($dayTimeStart)) + $startPad) / 60) . 'px';
                    $title = '';
                    if ($rowPeriods['type'] != 'Lesson' and $height > 15 and $height < 30) {
                        $title = "title='" . substr($effectiveStart, 0, 5) . ' - ' . substr($effectiveEnd, 0, 5) . "'";
                    } elseif ($rowPeriods['type'] != 'Lesson' and $height <= 15) {
                        $title = "title='" . $rowPeriods['name'] . ' (' . substr($effectiveStart, 0, 5) . '-' . substr($effectiveEnd, 0, 5) . ")'";
                    }
                    $class = 'ttGeneric';
                    if ((date('H:i:s') > $effectiveStart) and (date('H:i:s') < $effectiveEnd) and $rowPeriods['date'] == date('Y-m-d')) {
                        $class = 'ttCurrent';
                        $lineHeight = '';
                    }
                    $style = '';
                    if ($rowPeriods['type'] == 'Lesson') {
                        $class = 'ttLesson';
                        $lineHeight = '';
                    }


                    if ($class == 'ttGeneric') {
                        $lineHeight = 'line-height:15px;';
                    }
                    $output .= "<div class='$class' $title style='z-index: $zCount; position: absolute; top: $top; width: $width; height: $height; margin: 0px; padding: 0px; opacity: $ttAlpha; $lineHeight'>";
                    if ($height > 15 and $height < 30) {
                        $output .= $rowPeriods['name'] . '<br/>';
                    } elseif ($height >= 30) {
                        $output .= "<div class='ttleft'>" . $subjectName . "</div><div class='ttright'>" . $staffName . '</div><div class="clear"></div><br/>';
                        $output .= $rowPeriods['name'] . '&nbsp;';
                        $output .= '<i>' . substr($effectiveStart, 0, 5) . '-' . substr($effectiveEnd, 0, 5) . '</i>';
                    }
                    /*$output .= '<div style="margin-top:-8px; display:grid"><span style="color:blue; font-size:14px;">' . $subjectName . '</span><span style="margin-top:-8px;color: #206bc4;
                    ">' . $staffName . '</span></div>';*/
                    $output .= '</div>';
                    ++$zCount;
                }
            }

            //Draw periods from TT
            try {
                $dataPeriods = array('pupilsightTTDayID' => $rowDay['pupilsightTTDayID'], 'pupilsightPersonID' => $pupilsightPersonID);
                $sqlPeriods = "SELECT pupilsightTTDayID, pupilsightTTDayRowClassID, pupilsightTTColumnRow.pupilsightTTColumnRowID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightTTColumnRow.name, pupilsightCourse.pupilsightCourseID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, timeStart, timeEnd, phoneInternal, pupilsightSpace.name AS roomName FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightTTDayRowClass ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightTTDayRowClass.pupilsightCourseClassID) JOIN pupilsightTTColumnRow ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID) LEFT JOIN pupilsightSpace ON (pupilsightTTDayRowClass.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) WHERE pupilsightTTDayID=:pupilsightTTDayID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role LIKE '% - Left' ORDER BY timeStart, timeEnd";
                $resultPeriods = $connection2->prepare($sqlPeriods);
                $resultPeriods->execute($dataPeriods);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
            while ($rowPeriods = $resultPeriods->fetch()) {
                $isSlotInTime = false;
                if ($rowPeriods['timeStart'] <= $dayTimeStart and $rowPeriods['timeEnd'] > $dayTimeStart) {
                    $isSlotInTime = true;
                } elseif ($rowPeriods['timeStart'] >= $dayTimeStart and $rowPeriods['timeEnd'] <= $dayTimeEnd) {
                    $isSlotInTime = true;
                } elseif ($rowPeriods['timeStart'] < $dayTimeEnd and $rowPeriods['timeEnd'] >= $dayTimeEnd) {
                    $isSlotInTime = true;
                }

                if ($isSlotInTime == true) {
                    //Check for an exception for the current user
                    try {
                        $dataException = array('pupilsightPersonID' => $pupilsightPersonID);
                        $sqlException = 'SELECT * FROM pupilsightTTDayRowClassException WHERE pupilsightTTDayRowClassID=' . $rowPeriods['pupilsightTTDayRowClassID'] . ' AND pupilsightPersonID=:pupilsightPersonID';
                        $resultException = $connection2->prepare($sqlException);
                        $resultException->execute($dataException);
                    } catch (PDOException $e) {
                        $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                    }
                    if ($resultException->rowCount() < 1) {
                        $effectiveStart = $rowPeriods['timeStart'];
                        $effectiveEnd = $rowPeriods['timeEnd'];
                        if ($dayTimeStart > $rowPeriods['timeStart']) {
                            $effectiveStart = $dayTimeStart;
                        }
                        if ($dayTimeEnd < $rowPeriods['timeEnd']) {
                            $effectiveEnd = $dayTimeEnd;
                        }

                        $blank = false;
                        if ($narrow == 'trim') {
                            $width = (ceil(640 / $daysInWeek) - 20) . 'px';
                        } elseif ($narrow == 'narrow') {
                            $width = (ceil(515 / $daysInWeek) - 20) . 'px';
                        } else {
                            $width = (ceil(690 / $daysInWeek) - 20) . 'px';
                        }
                        $height = ceil((strtotime($effectiveEnd) - strtotime($effectiveStart)) / 60) . 'px';
                        $top = (ceil((strtotime($effectiveStart) - strtotime($dayTimeStart)) / 60 + ($startPad / 60))) . 'px';
                        $title = "title='";
                        if ($height < 45) {
                            $title .= __('Time:') . ' ' . substr($effectiveStart, 0, 5) . ' - ' . substr($effectiveEnd, 0, 5) . ' | ';
                            $title .= __('Timeslot:') . ' ' . $rowPeriods['name'] . ' | ';
                        }
                        if ($rowPeriods['roomName'] != '') {
                            if ($rowPeriods['phoneInternal'] != '') {
                                if (isset($spaceChanges[$rowPeriods['pupilsightTTDayRowClassID']][0]) == false) {
                                    $title .= __('Phone:') . ' ' . $rowPeriods['phoneInternal'] . ' | ';
                                } else {
                                    $title .= __('Phone:') . ' ' . $spaceChanges[$rowPeriods['pupilsightTTDayRowClassID']][1] . ' | ';
                                }
                            }
                        }
                        $title = substr($title, 0, -3);
                        $title .= "'";
                        $class2 = 'ttPeriod';

                        if ((date('H:i:s') > $effectiveStart) and (date('H:i:s') < $effectiveEnd) and $date == date('Y-m-d')) {
                            $class2 = 'ttPeriodCurrent';
                        }

                        //Create div to represent period
                        $fontSize = '100%';
                        if ($height < 60) {
                            $fontSize = '85%';
                        }
                        $output .= "<div class='$class2' $title style='z-index: $zCount; position: absolute; top: $top; width: $width; height: $height; margin: 0px; padding: 0px; opacity: 1; font-size: 12px; font-weight: bold;line-height: 14px;'>";
                        if ($height >= 45) {
                            $output .= $rowPeriods['name'] . '<br/>';
                            $output .= '<i>' . substr($effectiveStart, 0, 5) . ' - ' . substr($effectiveEnd, 0, 5) . '</i><br/>';
                        }

                        if (isActionAccessible($guid, $connection2, '/modules/Departments/department_course_class.php') and $edit == false) {
                            $output .= "<a style='text-decoration: none; font-weight: bold; font-size: 120%' href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Departments/department_course_class.php&pupilsightCourseClassID=' . $rowPeriods['pupilsightCourseClassID'] . "'>" . $rowPeriods['course'] . '.' . $rowPeriods['class'] . '</a><br/>';
                        } elseif (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_manage_class_edit.php') and $edit == true) {
                            $output .= "<a style='text-decoration: none; font-weight: bold; font-size: 120%' href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Timetable Admin/courseEnrolment_manage_class_edit.php&pupilsightCourseClassID=' . $rowPeriods['pupilsightCourseClassID'] . '&pupilsightSchoolYearID=' . $_SESSION[$guid]['pupilsightSchoolYearID'] . '&pupilsightCourseID=' . $rowPeriods['pupilsightCourseID'] . "'>" . $rowPeriods['course'] . '.' . $rowPeriods['class'] . '</a><br/>';
                        } else {
                            $output .= "<span style='font-size: 120%'><b>" . $rowPeriods['course'] . '.' . $rowPeriods['class'] . '</b></span><br/>';
                        }
                        if ($edit == false) {
                            if (isset($spaceChanges[$rowPeriods['pupilsightTTDayRowClassID']]) == false) {
                                $output .= $rowPeriods['roomName'];
                            } else {
                                if ($spaceChanges[$rowPeriods['pupilsightTTDayRowClassID']][0] != '') {
                                    $output .= "<span style='border: 1px solid #c00; padding: 0 2px'>" . $spaceChanges[$rowPeriods['pupilsightTTDayRowClassID']][0] . '</span>';
                                } else {
                                    $output .= "<span style='border: 1px solid #c00; padding: 0 2px'><i>" . __('No Space Allocated') . '</span>';
                                }
                            }
                        } else {
                            $output .= "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Timetable Admin/tt_edit_day_edit_class_edit.php&pupilsightTTDayID=' . $rowPeriods['pupilsightTTDayID'] . "&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=" . $_SESSION[$guid]['pupilsightSchoolYearID'] . '&pupilsightTTColumnRowID=' . $rowPeriods['pupilsightTTColumnRowID'] . '&pupilsightTTDayRowClass=' . $rowPeriods['pupilsightTTDayRowClassID'] . '&pupilsightCourseClassID=' . $rowPeriods['pupilsightCourseClassID'] . "'>" . $rowPeriods['roomName'] . '</a>';
                        }
                        $output .= '</div>';
                        ++$zCount;

                        if ($narrow == 'full' or $narrow == 'trim') {
                            if ($edit == false) {
                                //Add planner link icons for staff looking at own TT.
                                if ($self == true and $roleCategory == 'Staff') {
                                    if ($height >= 30) {
                                        $output .= "<div $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid rgba(136,136,136, $ttAlpha); height: $height; margin: 0px; padding: 0px; background-color: none; pointer-events: none'>";
                                        //Check for lesson plan
                                        $bgImg = 'none';

                                        try {
                                            $dataPlan = array('pupilsightCourseClassID' => $rowPeriods['pupilsightCourseClassID'], 'date' => $date, 'timeStart' => $rowPeriods['timeStart'], 'timeEnd' => $rowPeriods['timeEnd']);
                                            $sqlPlan = 'SELECT name, pupilsightPlannerEntryID FROM pupilsightPlannerEntry WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND date=:date AND timeStart=:timeStart AND timeEnd=:timeEnd GROUP BY name';
                                            $resultPlan = $connection2->prepare($sqlPlan);
                                            $resultPlan->execute($dataPlan);
                                        } catch (PDOException $e) {
                                            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                        }

                                        if ($resultPlan->rowCount() == 1) {
                                            $rowPlan = $resultPlan->fetch();
                                            $output .= "<a style='pointer-events: auto' href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Planner/planner_view_full.php&viewBy=class&pupilsightCourseClassID=' . $rowPeriods['pupilsightCourseClassID'] . '&pupilsightPlannerEntryID=' . $rowPlan['pupilsightPlannerEntryID'] . "'><img style='float: right; margin: " . (substr($height, 0, -2) - 27) . "px 2px 0 0' title='Lesson planned: " . htmlPrep($rowPlan['name']) . "' src='" . $_SESSION[$guid]['absoluteURL'] . '/themes/' . $_SESSION[$guid]['pupilsightThemeName'] . "/img/iconTick.png'/></a>";
                                        } elseif ($resultPlan->rowCount() == 0) {
                                            $output .= "<a style='pointer-events: auto' href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Planner/planner_add.php&viewBy=class&pupilsightCourseClassID=' . $rowPeriods['pupilsightCourseClassID'] . '&date=' . $date . '&timeStart=' . $effectiveStart . '&timeEnd=' . $effectiveEnd . "'><img style='float: right; margin: " . (substr($height, 0, -2) - 27) . "px 2px 0 0' title='Add lesson plan' src='" . $_SESSION[$guid]['absoluteURL'] . '/themes/' . $_SESSION[$guid]['pupilsightThemeName'] . "/img/page_new.png'/></a>";
                                        } else {
                                            $output .= "<a style='pointer-events: auto' href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Planner/planner.php&viewBy=class&pupilsightCourseClassID=' . $rowPeriods['pupilsightCourseClassID'] . '&date=' . $date . '&timeStart=' . $effectiveStart . '&timeEnd=' . $effectiveEnd . "'><div style='float: right; margin: " . (substr($height, 0, -2) - 17) . "px 5px 0 0'>" . __('Error') . '</div></a>';
                                        }
                                        $output .= '</div>';
                                        ++$zCount;
                                    }
                                }
                                //Add planner link icons for any one else's TT
                                else {
                                    $output .= "<div $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid rgba(136,136,136, $ttAlpha); height: $height; margin: 0px; padding: 0px; background-color: none; pointer-events: none'>";
                                    //Check for lesson plan
                                    $bgImg = 'none';

                                    try {
                                        $dataPlan = array('pupilsightCourseClassID' => $rowPeriods['pupilsightCourseClassID'], 'date' => $date, 'timeStart' => $rowPeriods['timeStart'], 'timeEnd' => $rowPeriods['timeEnd']);
                                        $sqlPlan = 'SELECT name, pupilsightPlannerEntryID FROM pupilsightPlannerEntry WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND date=:date AND timeStart=:timeStart AND timeEnd=:timeEnd GROUP BY name';
                                        $resultPlan = $connection2->prepare($sqlPlan);
                                        $resultPlan->execute($dataPlan);
                                    } catch (PDOException $e) {
                                        $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                    }
                                    if ($resultPlan->rowCount() == 1) {
                                        $rowPlan = $resultPlan->fetch();
                                        $output .= "<a style='pointer-events: auto' href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Planner/planner_view_full.php&viewBy=class&pupilsightCourseClassID=' . $rowPeriods['pupilsightCourseClassID'] . '&pupilsightPlannerEntryID=' . $rowPlan['pupilsightPlannerEntryID'] . "&search=$pupilsightPersonID'><img style='float: right; margin: " . (substr($height, 0, -2) - 27) . "px 2px 0 0' title='" . __('View lesson:') . ' ' . htmlPrep($rowPlan['name']) . "' src='" . $_SESSION[$guid]['absoluteURL'] . '/themes/' . $_SESSION[$guid]['pupilsightThemeName'] . "/img/plus.png'/></a>";
                                    } elseif ($resultPlan->rowCount() > 1) {
                                        $output .= "<div style='float: right; margin: " . (substr($height, 0, -2) - 17) . "px 5px 0 0'>" . __('Error') . '</div>';
                                    }
                                    $output .= '</div>';
                                    ++$zCount;
                                }
                            }
                            //Show exception editing
                            elseif ($edit) {
                                $output .= "<div $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid rgba(136,136,136, $ttAlpha); height: $height; margin: 0px; padding: 0px; background-color: none; pointer-events: none'>";
                                //Check for lesson plan
                                $bgImg = 'none';
                                $output .= "<a style='pointer-events: auto' href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Timetable Admin/tt_edit_day_edit_class_exception.php&pupilsightTTDayID=' . $rowPeriods['pupilsightTTDayID'] . "&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=" . $_SESSION[$guid]['pupilsightSchoolYearID'] . '&pupilsightTTColumnRowID=' . $rowPeriods['pupilsightTTColumnRowID'] . '&pupilsightTTDayRowClass=' . $rowPeriods['pupilsightTTDayRowClassID'] . '&pupilsightCourseClassID=' . $rowPeriods['pupilsightCourseClassID'] . "'><img style='float: right; margin: " . (substr($height, 0, -2) - 27) . "px 2px 0 0' title='" . __('Manage Exceptions') . "' src='" . $_SESSION[$guid]['absoluteURL'] . '/themes/' . $_SESSION[$guid]['pupilsightThemeName'] . "/img/attendance.png'/></a>";
                                $output .= '</div>';
                                ++$zCount;
                            }
                        }
                    }
                }
            }

            //Draw periods from school calendar
            if ($eventsSchool != false) {
                $height = 0;
                $top = 0;
                foreach ($eventsSchool as $event) {
                    if (date('Y-m-d', $event[2]) == date('Y-m-d', ($startDayStamp + (86400 * $count)))) {
                        if ($event[1] == 'All Day') {
                            $label = $event[0];
                            $title = '';
                            if (strlen($label) > 20) {
                                $label = substr($label, 0, 20) . '...';
                                $title = "title='" . $event[0] . "'";
                            }
                            $height = '30px';
                            $top = (($maxAllDays * -31) - 8 + ($allDay * 30)) . 'px';
                            $output .= "<div class='ttSchoolCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                            $output .= "<a target='_blank' href='" . $event[5] . "'>" . $label . '</a>';
                            $output .= '</div>';
                            ++$allDay;
                        } else {
                            $label = $event[0];
                            $title = "title='" . date('H:i', $event[2]) . ' to ' . date('H:i', $event[3]) . "'";
                            $height = ceil(($event[3] - $event[2]) / 60) . 'px';
                            $charCut = 20;
                            if ($height < 20) {
                                $charCut = 12;
                            }
                            if (strlen($label) > $charCut) {
                                $label = substr($label, 0, $charCut) . '...';
                                $title = "title='" . $event[0] . ' (' . date('H:i', $event[2]) . ' to ' . date('H:i', $event[3]) . ")'";
                            }
                            $top = (ceil(($event[2] - strtotime(date('Y-m-d', $startDayStamp + (86400 * $count)) . ' ' . $gridTimeStart)) / 60)) . 'px';
                            $output .= "<div class='ttSchoolCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                            $output .= "<a target='_blank' href='" . $event[5] . "'>" . $label . '</a>';
                            $output .= '</div>';
                        }
                        ++$zCount;
                    }
                }
            }

            //Draw periods from personal calendar
            if ($eventsPersonal != false) {
                $height = 0;
                $top = 0;
                $bg = "rgba(103,153,207,$schoolCalendarAlpha)";
                foreach ($eventsPersonal as $event) {
                    if (date('Y-m-d', $event[2]) == date('Y-m-d', ($startDayStamp + (86400 * $count)))) {
                        if ($event[1] == 'All Day') {
                            $label = $event[0];
                            $title = '';
                            if (strlen($label) > 20) {
                                $label = substr($label, 0, 20) . '...';
                                $title = "title='" . $event[0] . "'";
                            }
                            $height = '30px';
                            $top = (($maxAllDays * -31) - 8 + ($allDay * 30)) . 'px';
                            $output .= "<div class='ttPersonalCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                            $output .= !empty($event[5])
                                ? "<a target='_blank' href='" . $event[5] . "'>" . $label . '</a>'
                                : $label;
                            $output .= '</div>';
                            ++$allDay;
                        } else {
                            $label = $event[0];
                            $title = "title='" . date('H:i', $event[2]) . ' to ' . date('H:i', $event[3]) . "'";
                            $height = ceil(($event[3] - $event[2]) / 60) . 'px';
                            $charCut = 20;
                            if ($height < 20) {
                                $charCut = 12;
                            }
                            if (strlen($label) > $charCut) {
                                $label = substr($label, 0, $charCut) . '...';
                                $title = "title='" . $event[0] . ' (' . date('H:i', $event[2]) . ' to ' . date('H:i', $event[3]) . ")'";
                            }
                            $top = (ceil(($event[2] - strtotime(date('Y-m-d', $startDayStamp + (86400 * $count)) . ' ' . $gridTimeStart)) / 60)) . 'px';
                            $output .= "<div class='ttPersonalCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                            $output .= !empty($event[5])
                                ? "<a target='_blank' href='" . $event[5] . "'>" . $label . '</a>'
                                : $label;
                            $output .= '</div>';
                        }
                        ++$zCount;
                    }
                }
            }

            $output .= '</div>';
        }

        //Draw space bookings and staff coverage
        if ($eventsSpaceBooking != false) {
            $dayTimeStart = $gridTimeStart;
            $startPad = 0;
            $output .= "<div style='position: relative'>";

            $height = 0;
            $top = 0;
            foreach ($eventsSpaceBooking as $event) {
                if ($event[3] == date('Y-m-d', ($startDayStamp + (86400 * $count)))) {
                    $height = ceil((strtotime(date('Y-m-d', ($startDayStamp + (86400 * $count))) . ' ' . $event[5]) - strtotime(date('Y-m-d', ($startDayStamp + (86400 * $count))) . ' ' . $event[4])) / 60) . 'px';
                    $top = (ceil((strtotime($event[3] . ' ' . $event[4]) - strtotime(date('Y-m-d', $startDayStamp + (86400 * $count)) . ' ' . $dayTimeStart)) / 60 + ($startPad / 60))) . 'px';
                    if ($height < 45) {
                        $label = $event[1];
                        $title = "title='" . substr($event[4], 0, 5) . '-' . substr($event[5], 0, 5) . ' ' . $event[6] . "'";
                    } else {
                        $label = $event[1] . "<br/><span style='font-weight: normal'>" . substr($event[4], 0, 5) . '-' . substr($event[5], 0, 5) . '<br/>' . $event[6] . '</span>';
                        $title = "title='" . ($event[7] ?? '') . "'";
                    }
                    $output .= "<div class='ttSpaceBookingCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                    $output .= $label;
                    $output .= '</div>';
                    ++$zCount;
                }
            }
            $output .= '</div>';
        }
    }

    $output .= '</td>';

    return $output;
}

//TIMETABLE FOR ROOM
function renderTTSpace($guid, $connection2, $pupilsightSpaceID, $pupilsightTTID, $title = '', $startDayStamp = '', $q = '', $params = '')
{
    $output = '';

    $blank = true;
    if ($startDayStamp == '') {
        $startDayStamp = time();
    }
    $zCount = 0;
    $top = 0;

    //Find out which timetables I am involved in this year
    try {
        $data = array('pupilsightSpaceID' => $pupilsightSpaceID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
        $sql = "SELECT DISTINCT pupilsightTT.pupilsightTTID, pupilsightTT.name, pupilsightTT.nameShortDisplay FROM pupilsightTT JOIN pupilsightTTDay ON (pupilsightTT.pupilsightTTID=pupilsightTTDay.pupilsightTTID) JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSpaceID=:pupilsightSpaceID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' ";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
    }

    //If I am not involved in any timetables display all within the year
    if ($result->rowCount() == 0) {
        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            $sql = "SELECT pupilsightTT.pupilsightTTID, pupilsightTT.name, pupilsightTT.nameShortDisplay FROM pupilsightTT WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' ";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }
    }

    //link to other TTs
    if ($result->rowcount() > 1) {
        $output .= "<table class='table' style='width: 100%'>";
        $output .= '<tr>';
        $output .= '<td>';
        $output .= "<span class='form-label'>" . __('Timetable Chooser') . '</span> ';
        while ($row = $result->fetch()) {
            $output .= "<form method='post' action='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=$q" . $params . '&pupilsightTTID=' . $row['pupilsightTTID'] . "'>";
            $output .= "<input name='ttDate' value='" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], $startDayStamp) . "' type='hidden'>";
            $output .= "<input name='schoolCalendar' value='" . $_SESSION[$guid]['viewCalendarSchool'] . "' type='hidden'>";
            $output .= "<input name='personalCalendar' value='" . $_SESSION[$guid]['viewCalendarPersonal'] . "' type='hidden'>";
            $output .= "<input name='spaceBookingCalendar' value='" . $_SESSION[$guid]['viewCalendarSpaceBooking'] . "' type='hidden'>";
            $output .= "<input name='fromTT' value='Y' type='hidden'>";
            $output .= "<input class='btn btn-link' style='min-width: 30px; margin-top: 0px; float: left' type='submit' value='" . $row['name'] . "'>";
            $output .= '</form>';
        }
        try {
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '</table>';

        if ($pupilsightTTID != '') {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightTTID' => $pupilsightTTID);
            $sql = "SELECT DISTINCT pupilsightTT.pupilsightTTID, pupilsightTT.name, pupilsightTT.nameShortDisplay FROM pupilsightTT JOIN pupilsightTTDay ON (pupilsightTT.pupilsightTTID=pupilsightTTDay.pupilsightTTID) JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSpaceID=$pupilsightSpaceID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightTT.pupilsightTTID=:pupilsightTTID";
        }
        try {
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }
    }

    //Get space booking array
    $eventsSpaceBooking = false;
    if ($_SESSION[$guid]['viewCalendarSpaceBooking'] == 'Y') {
        $eventsSpaceBooking = getSpaceBookingEventsSpace($guid, $connection2, $startDayStamp, $pupilsightSpaceID);
    }

    //Display first TT
    if ($result->rowCount() > 0) {
        $row = $result->fetch();
        $pupilsightTTID = $row['pupilsightTTID'];
        $nameShortDisplay = $row['nameShortDisplay']; //Store day short name display setting for later

        if ($title != false) {
            $output .= '<h2>' . $row['name'] . '</h2>';
        }

        $output .= "<table cellspacing='0' class='noIntBorder' cellspacing='0' style='width: 100%; margin: 10px 0 10px 0'>";
        $output .= '<tr>';
        $output .= "<td style='vertical-align: top'>";
        $output .= "<form method='post' action='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=$q" . $params . '&pupilsightTTID=' . $row['pupilsightTTID'] . "'>";
        $output .= "<input name='ttDate' maxlength=10 value='" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($startDayStamp - (7 * 24 * 60 * 60))) . "' type='hidden'>";
        $output .= "<input name='schoolCalendar' value='" . $_SESSION[$guid]['viewCalendarSchool'] . "' type='hidden'>";
        $output .= "<input name='personalCalendar' value='" . $_SESSION[$guid]['viewCalendarPersonal'] . "' type='hidden'>";
        $output .= "<input name='spaceBookingCalendar' value='" . $_SESSION[$guid]['viewCalendarSpaceBooking'] . "' type='hidden'>";
        $output .= "<input name='fromTT' value='Y' type='hidden'>";
        $output .= "<input class='btn btn-link' style='min-width: 30px; margin-top: 0px; float: left' type='submit' value='" . __('Last Week') . "'>";
        $output .= '</form>';
        $output .= "<form method='post' action='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=$q" . $params . '&pupilsightTTID=' . $row['pupilsightTTID'] . "'>";
        $output .= "<input name='ttDate' value='" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($startDayStamp + (7 * 24 * 60 * 60))) . "' type='hidden'>";
        $output .= "<input name='schoolCalendar' value='" . $_SESSION[$guid]['viewCalendarSchool'] . "' type='hidden'>";
        $output .= "<input name='personalCalendar' value='" . $_SESSION[$guid]['viewCalendarPersonal'] . "' type='hidden'>";
        $output .= "<input name='spaceBookingCalendar' value='" . $_SESSION[$guid]['viewCalendarSpaceBooking'] . "' type='hidden'>";
        $output .= "<input name='fromTT' value='Y' type='hidden'>";
        $output .= "<input class='btn btn-link' style='min-width: 30px; margin-top: 0px; float: left' type='submit' value='" . __('Next Week') . "'>";
        $output .= '</form>';
        $output .= '</td>';
        $output .= "<td style='vertical-align: top; text-align: right'>";
        $output .= "<form method='post' action='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=$q" . $params . '&pupilsightTTID=' . $row['pupilsightTTID'] . "'>";
        $output .= "<input name='ttDate' id='ttDate' maxlength=10 value='" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], $startDayStamp) . "' type='text' style='height: 22px; width:100px; margin-right: 0px; float: none'>";
        $output .= '<script type="text/javascript">';
        $output .= "var ttDate=new LiveValidation('ttDate');";
        $output .= 'ttDate.add( Validate.Format, {pattern: ';
        if ($_SESSION[$guid]['i18n']['dateFormatRegEx'] == '') {
            $output .= "/^(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$/i";
        } else {
            $output .= $_SESSION[$guid]['i18n']['dateFormatRegEx'];
        }
        $output .= ', failureMessage: "Use ';
        if ($_SESSION[$guid]['i18n']['dateFormat'] == '') {
            $output .= 'dd/mm/yyyy';
        } else {
            $output .= $_SESSION[$guid]['i18n']['dateFormat'];
        }
        $output .= '." } );';
        $output .= 'ttDate.add(Validate.Presence);';
        $output .= '</script>';
        $output .= '<script type="text/javascript">';
        $output .= '$(function() {';
        $output .= '$("#ttDate").datepicker();';
        $output .= '});';
        $output .= '</script>';
        $output .= "<input style='margin-top: 0px; margin-right: -2px' type='submit' value='" . __('Go') . "'>";
        $output .= "<input name='schoolCalendar' value='" . $_SESSION[$guid]['viewCalendarSchool'] . "' type='hidden'>";
        $output .= "<input name='personalCalendar' value='" . $_SESSION[$guid]['viewCalendarPersonal'] . "' type='hidden'>";
        $output .= "<input name='spaceBookingCalendar' value='" . $_SESSION[$guid]['viewCalendarSpaceBooking'] . "' type='hidden'>";
        $output .= "<input name='fromTT' value='Y' type='hidden'>";
        $output .= '</form>';
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '</table>';

        //Check which days are school days
        $daysInWeek = 0;
        $days = array();
        $timeStart = '';
        $timeEnd = '';
        try {
            $dataDays = array();
            $sqlDays = "SELECT * FROM pupilsightDaysOfWeek WHERE schoolDay='Y' ORDER BY sequenceNumber";
            $resultDays = $connection2->prepare($sqlDays);
            $resultDays->execute($dataDays);
        } catch (PDOException $e) {
            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }
        $days = $resultDays->fetchAll();
        $daysInWeek = $resultDays->rowCount();
        foreach ($days as $day) {
            if ($timeStart == '' or $timeEnd == '') {
                $timeStart = $day['schoolStart'];
                $timeEnd = $day['schoolEnd'];
            } else {
                if ($day['schoolStart'] < $timeStart) {
                    $timeStart = $day['schoolStart'];
                }
                if ($day['schoolEnd'] > $timeEnd) {
                    $timeEnd = $day['schoolEnd'];
                }
            }
        }

        //Count back to first dayOfWeek before specified calendar date
        while (date('D', $startDayStamp) != $days[0]['nameShort']) {
            $startDayStamp = $startDayStamp - 86400;
        }

        //Count forward to the end of the week
        $endDayStamp = $startDayStamp + (86400 * ($daysInWeek - 1));

        $schoolCalendarAlpha = 0.85;
        $ttAlpha = 1.0;

        if ($_SESSION[$guid]['viewCalendarSpaceBooking'] != 'N') {
            $ttAlpha = 0.75;
        }

        //Max diff time for week based on timetables
        try {
            $dataDiff = array('date1' => date('Y-m-d', ($startDayStamp + (86400 * 0))), 'date2' => date('Y-m-d', ($endDayStamp + (86400 * 1))), 'pupilsightTTID' => $row['pupilsightTTID']);
            $sqlDiff = 'SELECT DISTINCT pupilsightTTColumn.pupilsightTTColumnID FROM pupilsightTTDay JOIN pupilsightTTDayDate ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID) JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) WHERE (date>=:date1 AND date<=:date2) AND pupilsightTTID=:pupilsightTTID';
            $resultDiff = $connection2->prepare($sqlDiff);
            $resultDiff->execute($dataDiff);
        } catch (PDOException $e) {
            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }
        while ($rowDiff = $resultDiff->fetch()) {
            try {
                $dataDiffDay = array('pupilsightTTColumnID' => $rowDiff['pupilsightTTColumnID']);
                $sqlDiffDay = 'SELECT * FROM pupilsightTTColumnRow WHERE pupilsightTTColumnID=:pupilsightTTColumnID ORDER BY timeStart';
                $resultDiffDay = $connection2->prepare($sqlDiffDay);
                $resultDiffDay->execute($dataDiffDay);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
            while ($rowDiffDay = $resultDiffDay->fetch()) {
                if ($rowDiffDay['timeStart'] < $timeStart) {
                    $timeStart = $rowDiffDay['timeStart'];
                }
                if ($rowDiffDay['timeEnd'] > $timeEnd) {
                    $timeEnd = $rowDiffDay['timeEnd'];
                }
            }
        }

        //Max diff time for week based on special days timing change
        try {
            $dataDiff = array('date1' => date('Y-m-d', ($startDayStamp + (86400 * 0))), 'date2' => date('Y-m-d', ($startDayStamp + (86400 * 6))));
            $sqlDiff = "SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date>=:date1 AND date<=:date2 AND type='Timing Change' AND NOT schoolStart IS NULL AND NOT schoolEnd IS NULL";
            $resultDiff = $connection2->prepare($sqlDiff);
            $resultDiff->execute($dataDiff);
        } catch (PDOException $e) {
            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }

        while ($rowDiff = $resultDiff->fetch()) {
            if ($rowDiff['schoolStart'] < $timeStart) {
                $timeStart = $rowDiff['schoolStart'];
            }
            if ($rowDiff['schoolEnd'] > $timeEnd) {
                $timeEnd = $rowDiff['schoolEnd'];
            }
        }

        //Max diff based on space booking events
        if ($eventsSpaceBooking != false) {
            foreach ($eventsSpaceBooking as $event) {
                if ($event[3] <= date('Y-m-d', ($startDayStamp + (86400 * 6)))) {
                    if ($event[4] < $timeStart) {
                        $timeStart = $event[4];
                    }
                    if ($event[5] > $timeEnd) {
                        $timeEnd = $event[5];
                    }
                }
            }
        }

        //Final calc
        $diffTime = strtotime($timeEnd) - strtotime($timeStart);
        $width = (ceil(690 / $daysInWeek) - 20) . 'px';

        $count = 0;

        $output .= "<table class='table mini' cellspacing='0' style='width: 750px; margin: 0px 0px 30px 0px;'>";
        //Spit out controls for displaying calendars
        /*
        if ($_SESSION[$guid]['viewCalendarSpaceBooking'] != '') {
            
            $output .= "<tr class='head' style='height: 37px;'>";
            $output .= "<th class='ttCalendarBar' colspan=" . ($daysInWeek + 1) . '>';
            $output .= "<form method='post' action='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=$q" . $params . "' style='padding: 5px 5px 0 0'>";
            if ($_SESSION[$guid]['viewCalendarSpaceBooking'] != '') {
                $checked = '';
                if ($_SESSION[$guid]['viewCalendarSpaceBooking'] == 'Y') {
                    $checked = 'checked';
                }
                $output .= "<span class='ttSpaceBookingCalendar' style='opacity: $schoolCalendarAlpha'><a style='color: #fff' href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Timetable/spaceBooking_manage.php'>" . __('Bookings') . '</a> ';
                $output .= "<input $checked style='margin-left: 3px' type='checkbox' name='spaceBookingCalendar' onclick='submit();'/>";
                $output .= '</span>';
            }

            $output .= "<input type='hidden' name='ttDate' value='" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], $startDayStamp) . "'>";
            $output .= "<input name='fromTT' value='Y' type='hidden'>";
            $output .= '</form>';
            $output .= '</th>';
            $output .= '</tr>';
        }*/

        $output .= "<tr class='head'>";
        $output .= "<th style='vertical-align: top; width: 70px; text-align: center'>";
        //Calculate week number
        $week = getWeekNumber($startDayStamp, $connection2, $guid);
        if ($week != false) {
            $output .= sprintf(__('Week %1$s'), $week) . '<br/>';
        }
        $output .= "<span style='font-weight: normal; font-style: italic;'>" . __('Time') . '<span>';
        $output .= '</th>';
        $count = 0;
        foreach ($days as $day) {
            if ($day['schoolDay'] == 'Y') {
                if ($count == 0) {
                    $firstSequence = $day['sequenceNumber'];
                }
                $dateCorrection = ($day['sequenceNumber'] - 1) - ($firstSequence - 1);

                $today = ((date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($startDayStamp + (86400 * $dateCorrection))) == date($_SESSION[$guid]['i18n']['dateFormatPHP'])) ? "class='ttToday'" : '');
                $output .= "<th $today style='vertical-align: top; text-align: center; width: ";

                $output .= (550 / $daysInWeek);
                $output .= "px'>";
                if ($nameShortDisplay != 'Timetable Day Short Name') {
                    $output .= __($day['nameShort']) . '<br/>';
                } else {
                    try {
                        $dataDay = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $count))), 'pupilsightTTID' => $pupilsightTTID);
                        $sqlDay = 'SELECT nameShort FROM pupilsightTTDay JOIN pupilsightTTDayDate ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID) WHERE date=:date AND pupilsightTTID=:pupilsightTTID';
                        $resultDay = $connection2->prepare($sqlDay);
                        $resultDay->execute($dataDay);
                    } catch (PDOException $e) {
                    }
                    if ($resultDay->rowCount() == 1) {
                        $rowDay = $resultDay->fetch();
                        $output .= $rowDay['nameShort'] . '<br/>';
                    } else {
                        $output .= __($day['nameShort']) . '<br/>';
                    }
                }
                $output .= "<span style='font-size: 80%; font-style: italic'>" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($startDayStamp + (86400 * $dateCorrection))) . '</span><br/>';
                try {
                    $dataSpecial = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))));
                    $sqlSpecial = "SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date=:date AND type='Timing Change'";
                    $resultSpecial = $connection2->prepare($sqlSpecial);
                    $resultSpecial->execute($dataSpecial);
                } catch (PDOException $e) {
                    $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                }
                if ($resultSpecial->rowcount() == 1) {
                    $rowSpecial = $resultSpecial->fetch();
                    $output .= "<span style='font-size: 80%; font-weight: bold'><u>" . $rowSpecial['name'] . '</u></span>';
                }
                $output .= '</th>';
            }
            $count++;
        }
        $output .= '</tr>';

        $output .= "<tr style='height:" . (ceil($diffTime / 60) + 14) . "px'>";
        $output .= "<td class='ttTime' style='height: 300px; width: 75px; text-align: center; vertical-align: top'>";
        $output .= "<div style='position: relative; width: 71px'>";
        $countTime = 0;
        $time = $timeStart;
        $output .= "<div $title style='position: absolute; top: -3px; width: 71px ; border: none; height: 60px; margin: 0px; padding: 0px; font-size: 92%'>";
        $output .= substr($time, 0, 5) . '<br/>';
        $output .= '</div>';
        $time = date('H:i:s', strtotime($time) + 3600);
        $spinControl = 0;
        while ($time <= $timeEnd and $spinControl < (23 - substr($timeStart, 0, 2))) {
            ++$countTime;
            $output .= "<div $title style='position: absolute; top:" . (($countTime * 60) - 5) . "px ; width: 71px ; border: none; height: 60px; margin: 0px; padding: 0px; font-size: 92%'>";
            $output .= substr($time, 0, 5) . '<br/>';
            $output .= '</div>';
            $time = date('H:i:s', strtotime($time) + 3600);
            ++$spinControl;
        }

        $output .= '</div>';
        $output .= '</td>';

        //Check to see if week is at all in term time...if it is, then display the grid
        $isWeekInTerm = false;
        try {
            $dataTerm = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            $sqlTerm = 'SELECT pupilsightSchoolYearTerm.firstDay, pupilsightSchoolYearTerm.lastDay FROM pupilsightSchoolYearTerm, pupilsightSchoolYear WHERE pupilsightSchoolYearTerm.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID AND pupilsightSchoolYear.pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $resultTerm = $connection2->prepare($sqlTerm);
            $resultTerm->execute($dataTerm);
        } catch (PDOException $e) {
            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }
        $weekStart = date('Y-m-d', ($startDayStamp + (86400 * 0)));
        $weekEnd = date('Y-m-d', ($startDayStamp + (86400 * 6)));
        while ($rowTerm = $resultTerm->fetch()) {
            if ($weekStart <= $rowTerm['firstDay'] and $weekEnd >= $rowTerm['firstDay']) {
                $isWeekInTerm = true;
            } elseif ($weekStart >= $rowTerm['firstDay'] and $weekEnd <= $rowTerm['lastDay']) {
                $isWeekInTerm = true;
            } elseif ($weekStart <= $rowTerm['lastDay'] and $weekEnd >= $rowTerm['lastDay']) {
                $isWeekInTerm = true;
            }
        }
        if ($isWeekInTerm == true) {
            $blank = false;
        }

        //Run through days of the week
        foreach ($days as $day) {
            $dayOut = '';
            if ($day['schoolDay'] == 'Y') {
                $dateCorrection = ($day['sequenceNumber'] - 1) - ($firstSequence - 1);

                //Check to see if day is term time
                $isDayInTerm = false;
                try {
                    $dataTerm = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sqlTerm = 'SELECT pupilsightSchoolYearTerm.firstDay, pupilsightSchoolYearTerm.lastDay FROM pupilsightSchoolYearTerm, pupilsightSchoolYear WHERE pupilsightSchoolYearTerm.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID AND pupilsightSchoolYear.pupilsightSchoolYearID=:pupilsightSchoolYearID';
                    $resultTerm = $connection2->prepare($sqlTerm);
                    $resultTerm->execute($dataTerm);
                } catch (PDOException $e) {
                    $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                }
                while ($rowTerm = $resultTerm->fetch()) {
                    if (date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))) >= $rowTerm['firstDay'] and date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))) <= $rowTerm['lastDay']) {
                        $isDayInTerm = true;
                    }
                }

                if ($isDayInTerm == true) {
                    //Check for school closure day
                    try {
                        $dataClosure = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))));
                        $sqlClosure = 'SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date=:date';
                        $resultClosure = $connection2->prepare($sqlClosure);
                        $resultClosure->execute($dataClosure);
                    } catch (PDOException $e) {
                        $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                    }
                    if ($resultClosure->rowCount() == 1) {
                        $rowClosure = $resultClosure->fetch();
                        if ($rowClosure['type'] == 'School Closure') {
                            $dayOut .= "<td style='text-align: center; vertical-align: top; font-size: 11px'>";
                            $dayOut .= "<div style='position: relative'>";
                            $dayOut .= "<div class='ttClosure' style='z-index: $zCount; position: absolute; width: $width ; height: " . ceil($diffTime / 60) . "px; margin: 0px; padding: 0px; opacity: $ttAlpha'>";
                            $dayOut .= "<div style='position: relative; top: 50%'>";
                            $dayOut .= '<span>' . $rowClosure['name'] . '</span>';
                            $dayOut .= '</div>';
                            $dayOut .= '</div>';
                            $dayOut .= '</div>';
                            $dayOut .= '</td>';
                        } elseif ($rowClosure['type'] == 'Timing Change') {
                            $dayOut = renderTTSpaceDay($guid, $connection2, $row['pupilsightTTID'], $startDayStamp, $dateCorrection, $daysInWeek, $pupilsightSpaceID, $timeStart, $diffTime, $eventsSpaceBooking, $rowClosure['schoolStart'], $rowClosure['schoolEnd']);
                        }
                    } else {
                        $dayOut = renderTTSpaceDay($guid, $connection2, $row['pupilsightTTID'], $startDayStamp, $dateCorrection, $daysInWeek, $pupilsightSpaceID, $timeStart, $diffTime, $eventsSpaceBooking);
                    }
                }

                if ($dayOut == '') {
                    $dayOut .= "<td style='text-align: center; vertical-align: top; font-size: 11px'>";
                    $dayOut .= "<div style='position: relative'>";
                    $dayOut .= "<div class='ttClosure' style='z-index: $zCount; position: absolute; width: $width ; height: " . ceil($diffTime / 60) . "px; margin: 0px; padding: 0px; opacity: $ttAlpha'>";
                    $dayOut .= "<div style='position: relative; top: 50%'>";
                    $dayOut .= "<span style='color: rgba(255,0,0,$ttAlpha);'>" . __('School Closed') . '</span>';
                    $dayOut .= '</div>';
                    $dayOut .= '</div>';
                    $dayOut .= '</div>';
                    $dayOut .= '</td>';
                }

                $output .= $dayOut;
            }
        }

        $output .= '</tr>';
        $output .= '</table>';
    }

    return $output;
}

function renderTTSpaceDay($guid, $connection2, $pupilsightTTID, $startDayStamp, $count, $daysInWeek, $pupilsightSpaceID, $gridTimeStart, $diffTime, $eventsSpaceBooking, $specialDayStart = '', $specialDayEnd = '')
{
    $schoolCalendarAlpha = 0.85;
    $ttAlpha = 1.0;

    $date = date('Y-m-d', ($startDayStamp + (86400 * $count)));

    $output = '';
    $blank = true;

    //Make array of space changes
    $spaceChanges = array();
    try {
        $dataSpaceChange = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $count))));
        $sqlSpaceChange = 'SELECT pupilsightTTSpaceChange.*, pupilsightSpace.name AS space, phoneInternal FROM pupilsightTTSpaceChange LEFT JOIN pupilsightSpace ON (pupilsightTTSpaceChange.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) WHERE date=:date';
        $resultSpaceChange = $connection2->prepare($sqlSpaceChange);
        $resultSpaceChange->execute($dataSpaceChange);
    } catch (PDOException $e) {
    }
    while ($rowSpaceChange = $resultSpaceChange->fetch()) {
        $spaceChanges[$rowSpaceChange['pupilsightTTDayRowClassID']][0] = $rowSpaceChange['space'];
        $spaceChanges[$rowSpaceChange['pupilsightTTDayRowClassID']][1] = $rowSpaceChange['phoneInternal'];
    }

    //Get day start and end!
    $dayTimeStart = '';
    $dayTimeEnd = '';
    try {
        $dataDiff = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $count))), 'pupilsightTTID' => $pupilsightTTID);
        $sqlDiff = 'SELECT timeStart, timeEnd FROM pupilsightTTDay JOIN pupilsightTTDayDate ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID) JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTColumnRow ON (pupilsightTTColumn.pupilsightTTColumnID=pupilsightTTColumnRow.pupilsightTTColumnID) WHERE date=:date AND pupilsightTTID=:pupilsightTTID';
        $resultDiff = $connection2->prepare($sqlDiff);
        $resultDiff->execute($dataDiff);
    } catch (PDOException $e) {
        $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
    }
    while ($rowDiff = $resultDiff->fetch()) {
        if ($dayTimeStart == '') {
            $dayTimeStart = $rowDiff['timeStart'];
        }
        if ($rowDiff['timeStart'] < $dayTimeStart) {
            $dayTimeStart = $rowDiff['timeStart'];
        }
        if ($dayTimeEnd == '') {
            $dayTimeEnd = $rowDiff['timeEnd'];
        }
        if ($rowDiff['timeEnd'] > $dayTimeEnd) {
            $dayTimeEnd = $rowDiff['timeEnd'];
        }
    }
    if ($specialDayStart != '') {
        $dayTimeStart = $specialDayStart;
    }
    if ($specialDayEnd != '') {
        $dayTimeEnd = $specialDayEnd;
    }

    $dayDiffTime = strtotime($dayTimeEnd) - strtotime($dayTimeStart);

    $startPad = strtotime($dayTimeStart) - strtotime($gridTimeStart);

    $today = (date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($startDayStamp + (86400 * $count))) == date($_SESSION[$guid]['i18n']['dateFormatPHP']) ? "class='ttToday'" : '');
    $output .= "<td $today style='text-align: center; vertical-align: top; font-size: 11px'>";

    try {
        $dataDay = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $count))), 'pupilsightTTID' => $pupilsightTTID);
        $sqlDay = 'SELECT pupilsightTTDay.pupilsightTTDayID FROM pupilsightTTDayDate JOIN pupilsightTTDay ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) WHERE pupilsightTTID=:pupilsightTTID AND date=:date';
        $resultDay = $connection2->prepare($sqlDay);
        $resultDay->execute($dataDay);
    } catch (PDOException $e) {
        $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
    }

    if ($resultDay->rowCount() == 1) {
        $rowDay = $resultDay->fetch();
        $zCount = 0;
        $output .= "<div style='position: relative'>";

        //Draw outline of the day
        try {
            $dataPeriods = array('pupilsightTTDayID' => $rowDay['pupilsightTTDayID'], 'date' => date('Y-m-d', ($startDayStamp + (86400 * $count))));
            $sqlPeriods = 'SELECT pupilsightTTColumnRow.name, timeStart, timeEnd, type, date FROM pupilsightTTDay JOIN pupilsightTTDayDate ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID) JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTColumnRow ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) WHERE pupilsightTTDayDate.pupilsightTTDayID=:pupilsightTTDayID AND date=:date ORDER BY timeStart, timeEnd';
            $resultPeriods = $connection2->prepare($sqlPeriods);
            $resultPeriods->execute($dataPeriods);
        } catch (PDOException $e) {
            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }
        while ($rowPeriods = $resultPeriods->fetch()) {
            $isSlotInTime = false;
            if ($rowPeriods['timeStart'] <= $dayTimeStart and $rowPeriods['timeEnd'] > $dayTimeStart) {
                $isSlotInTime = true;
            } elseif ($rowPeriods['timeStart'] >= $dayTimeStart and $rowPeriods['timeEnd'] <= $dayTimeEnd) {
                $isSlotInTime = true;
            } elseif ($rowPeriods['timeStart'] < $dayTimeEnd and $rowPeriods['timeEnd'] >= $dayTimeEnd) {
                $isSlotInTime = true;
            }

            if ($isSlotInTime == true) {
                $effectiveStart = $rowPeriods['timeStart'];
                $effectiveEnd = $rowPeriods['timeEnd'];
                if ($dayTimeStart > $rowPeriods['timeStart']) {
                    $effectiveStart = $dayTimeStart;
                }
                if ($dayTimeEnd < $rowPeriods['timeEnd']) {
                    $effectiveEnd = $dayTimeEnd;
                }

                $width = (ceil(690 / $daysInWeek) - 20) . 'px';
                $height = ceil((strtotime($effectiveEnd) - strtotime($effectiveStart)) / 60) . 'px';
                $top = ceil(((strtotime($effectiveStart) - strtotime($dayTimeStart)) + $startPad) / 60) . 'px';
                $title = '';
                if ($rowPeriods['type'] != 'Lesson' and $height > 15 and $height < 30) {
                    $title = "title='" . substr($effectiveStart, 0, 5) . ' - ' . substr($effectiveEnd, 0, 5) . "'";
                } elseif ($rowPeriods['type'] != 'Lesson' and $height <= 15) {
                    $title = "title='" . $rowPeriods['name'] . ' (' . substr($effectiveStart, 0, 5) . '-' . substr($effectiveEnd, 0, 5) . ")'";
                }
                $class = 'ttGeneric';
                if ((date('H:i:s') > $effectiveStart) and (date('H:i:s') < $effectiveEnd) and $rowPeriods['date'] == date('Y-m-d')) {
                    $class = 'ttCurrent';
                }
                $style = '';
                if ($rowPeriods['type'] == 'Lesson') {
                    $class = 'ttLesson';
                }
                $output .= "<div class='$class' $title style='z-index: $zCount; position: absolute; top: $top; width: $width; height: $height; margin: 0px; padding: 0px; opacity: $ttAlpha;'>";
                if ($height > 15 and $height < 30) {
                    $output .= $rowPeriods['name'] . '<br/>';
                } elseif ($height >= 30) {
                    $output .= $rowPeriods['name'] . '<br/>';
                    $output .= '<i>' . substr($effectiveStart, 0, 5) . '-' . substr($effectiveEnd, 0, 5) . '</i><br/>';

                    if ($_SESSION[$guid]['viewCalendarSpaceBooking'] == 'Y' && isActionAccessible($guid, $connection2, '/modules/Timetable/spaceBooking_manage_add.php') && $date >= date('Y-m-d')) {
                        $overlappingBookings = array_filter(
                            is_array($eventsSpaceBooking) ? $eventsSpaceBooking : [],
                            function ($event) use ($date, $effectiveStart, $effectiveEnd) {
                                return ($event[3] == $date) && (($event[4] >= $effectiveStart && $event[4] < $effectiveEnd) || ($effectiveStart >= $event[4] && $effectiveStart < $event[5]));
                            }
                        );

                        if (empty($overlappingBookings)) {
                            $output .= "<a style='pointer-events: auto; position: absolute; right: 5px; bottom: 5px;' href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Timetable/spaceBooking_manage_add.php&pupilsightSpaceID=' . $pupilsightSpaceID . '&date=' . $date . '&timeStart=' . $effectiveStart . '&timeEnd=' . $effectiveEnd . "&source=tt'><img style='' title='" . __('Add Facility Booking') . "' src='" . $_SESSION[$guid]['absoluteURL'] . '/themes/' . $_SESSION[$guid]['pupilsightThemeName'] . "/img/page_new.png'/></a>";
                        }
                    }
                }
                $output .= '</div>';
                ++$zCount;
            }
        }

        //Draw periods from TT
        try {
            $dataPeriods = array('pupilsightTTDayID' => $rowDay['pupilsightTTDayID'], 'pupilsightSpaceID' => $pupilsightSpaceID, 'pupilsightTTDayID1' => $rowDay['pupilsightTTDayID'], 'pupilsightSpaceID1' => $pupilsightSpaceID, 'date' => date('Y-m-d', ($startDayStamp + (86400 * $count))));
            $sqlPeriods = "(SELECT 'Normal' AS type, pupilsightTTDayRowClassID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightTTColumnRow.name, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, timeStart, timeEnd, phoneInternal, pupilsightSpace.name AS roomName FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightTTDayRowClass ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightTTDayRowClass.pupilsightCourseClassID) JOIN pupilsightTTColumnRow ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID) LEFT JOIN pupilsightSpace ON (pupilsightTTDayRowClass.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) WHERE pupilsightTTDayID=:pupilsightTTDayID AND pupilsightSpace.pupilsightSpaceID=:pupilsightSpaceID)
            UNION
            (SELECT 'Change' AS type, pupilsightTTDayRowClass.pupilsightTTDayRowClassID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightTTColumnRow.name, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, timeStart, timeEnd, phoneInternal, pupilsightSpace.name AS roomName FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightTTDayRowClass ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightTTDayRowClass.pupilsightCourseClassID) JOIN pupilsightTTColumnRow ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID) JOIN pupilsightTTSpaceChange ON (pupilsightTTSpaceChange.pupilsightTTDayRowClassID=pupilsightTTDayRowClass.pupilsightTTDayRowClassID AND pupilsightTTSpaceChange.date=:date) LEFT JOIN pupilsightSpace ON (pupilsightTTSpaceChange.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) WHERE pupilsightTTDayID=:pupilsightTTDayID1 AND pupilsightTTSpaceChange.pupilsightSpaceID=:pupilsightSpaceID1)
            ORDER BY timeStart, timeEnd";
            $resultPeriods = $connection2->prepare($sqlPeriods);
            $resultPeriods->execute($dataPeriods);
        } catch (PDOException $e) {
            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }

        while ($rowPeriods = $resultPeriods->fetch()) {
            $isSlotInTime = false;
            if ($rowPeriods['timeStart'] <= $dayTimeStart and $rowPeriods['timeEnd'] > $dayTimeStart) {
                $isSlotInTime = true;
            } elseif ($rowPeriods['timeStart'] >= $dayTimeStart and $rowPeriods['timeEnd'] <= $dayTimeEnd) {
                $isSlotInTime = true;
            } elseif ($rowPeriods['timeStart'] < $dayTimeEnd and $rowPeriods['timeEnd'] >= $dayTimeEnd) {
                $isSlotInTime = true;
            }

            if ($isSlotInTime == true) {
                if ((isset($spaceChanges[str_pad($rowPeriods['pupilsightTTDayRowClassID'], 12, '0', STR_PAD_LEFT)]) == false and $rowPeriods['type'] == 'Normal') or $rowPeriods['type'] == 'Change') {
                    $effectiveStart = $rowPeriods['timeStart'];
                    $effectiveEnd = $rowPeriods['timeEnd'];
                    if ($dayTimeStart > $rowPeriods['timeStart']) {
                        $effectiveStart = $dayTimeStart;
                    }
                    if ($dayTimeEnd < $rowPeriods['timeEnd']) {
                        $effectiveEnd = $dayTimeEnd;
                    }

                    $blank = false;
                    $width = (ceil(690 / $daysInWeek) - 20) . 'px';
                    $height = ceil((strtotime($effectiveEnd) - strtotime($effectiveStart)) / 60) . 'px';
                    $top = (ceil((strtotime($effectiveStart) - strtotime($dayTimeStart)) / 60 + ($startPad / 60))) . 'px';
                    $title = "title='";
                    if ($height < 45) {
                        $title .= __('Time:') . ' ' . substr($effectiveStart, 0, 5) . ' - ' . substr($effectiveEnd, 0, 5) . ' | ';
                        $title .= __('Timeslot:') . ' ' . $rowPeriods['name'] . ' | ';
                    }
                    if ($rowPeriods['roomName'] != '') {
                        if ($height < 60) {
                            $title .= __('Room:') . ' ' . $rowPeriods['roomName'] . ' | ';
                        }
                        if ($rowPeriods['phoneInternal'] != '') {
                            $title .= __('Phone:') . ' ' . $rowPeriods['phoneInternal'] . ' | ';
                        }
                    }

                    $title = substr($title, 0, -3);
                    $title .= "'";
                    $class2 = 'ttPeriod';

                    if ((date('H:i:s') > $effectiveStart) and (date('H:i:s') < $effectiveEnd) and $date == date('Y-m-d')) {
                        $class2 = 'ttPeriodCurrent';
                    }

                    //Create div to represent period
                    $output .= "<div class='$class2' $title style='z-index: $zCount; position: absolute; top: $top; width: $width; height: $height; margin: 0px; padding: 0px; opacity: $ttAlpha'>";
                    if ($height >= 45) {
                        $output .= $rowPeriods['name'] . '<br/>';
                        $output .= '<i>' . substr($effectiveStart, 0, 5) . ' - ' . substr($effectiveEnd, 0, 5) . '</i><br/>';
                    }

                    if (isActionAccessible($guid, $connection2, '/modules/Departments/department_course_class.php')) {
                        $output .= "<a style='text-decoration: none; font-weight: bold; font-size: 120%' href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Departments/department_course_class.php&pupilsightCourseClassID=' . $rowPeriods['pupilsightCourseClassID'] . "'>" . $rowPeriods['course'] . '.' . $rowPeriods['class'] . '</a><br/>';
                    } else {
                        $output .= "<span style='font-size: 120%'><b>" . $rowPeriods['course'] . '.' . $rowPeriods['class'] . '</b></span><br/>';
                    }
                    if ($height >= 60) {
                        if ($rowPeriods['type'] == 'Normal') {
                            $output .= $rowPeriods['roomName'];
                        } else {
                            $output .= "<span style='border: 1px solid #c00; padding: 0 2px'>" . $rowPeriods['roomName'] . '</span>';
                        }
                    }
                    $output .= '</div>';
                    ++$zCount;
                }
            }
        }

        //Draw space bookings
        if ($eventsSpaceBooking != false) {
            $height = 0;
            $top = 0;
            foreach ($eventsSpaceBooking as $event) {
                if ($event[3] == date('Y-m-d', ($startDayStamp + (86400 * $count)))) {
                    $height = ceil((strtotime(date('Y-m-d', ($startDayStamp + (86400 * $count))) . ' ' . $event[5]) - strtotime(date('Y-m-d', ($startDayStamp + (86400 * $count))) . ' ' . $event[4])) / 60) . 'px';
                    $top = (ceil((strtotime($event[3] . ' ' . $event[4]) - strtotime(date('Y-m-d', $startDayStamp + (86400 * $count)) . ' ' . $dayTimeStart)) / 60 + ($startPad / 60))) . 'px';
                    if ($height < 45) {
                        $label = $event[1];
                        $title = "title='" . substr($event[4], 0, 5) . '-' . substr($event[5], 0, 5) . ' ' . __('by') . ' ' . $event[6] . "'";
                    } else {
                        $label = $event[1] . "<br/><span style='font-weight: normal'>(" . substr($event[4], 0, 5) . '-' . substr($event[5], 0, 5) . ')<br/>' . __('by') . ' ' . $event[6] . '</span>';
                        $title = '';
                    }
                    $output .= "<div class='ttSpaceBookingCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                    $output .= $label;
                    $output .= '</div>';
                    ++$zCount;
                }
            }
        }

        $output .= '</div>';
    }
    $output .= '</td>';

    return $output;
}

function renderTTAttendance($guid, $connection2, $classId, $sectionId, $title = '', $startDayStamp = '', $q = '', $params = '', $narrow = 'full', $edit = false)
{
    $zCount = 0;
    $output = '';
    $proceed = false;

    $highestAction = getHighestGroupedAction($guid, '/modules/Timetable/tt.php', $connection2);

    if ($highestAction == 'View Timetable by Person_allYears') {
        $proceed = true;
    } else if ($_SESSION[$guid]['pupilsightSchoolYearIDCurrent'] == $_SESSION[$guid]['pupilsightSchoolYearID']) {

        if ($highestAction == 'View Timetable by Person') {
            $proceed = true;
        } else if ($highestAction == 'View Timetable by Person_my') {
            if ($pupilsightPersonID == $_SESSION[$guid]['pupilsightPersonID']) {
                $proceed = true;
            }
        } else if ($highestAction == 'View Timetable by Person_myChildren') {
            // try {
            //     $data = array('pupilsightPersonID1' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonID2' => $pupilsightPersonID);
            //     $sql = "SELECT pupilsightFamilyChild.pupilsightPersonID FROM pupilsightFamilyChild
            //         JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamilyChild.pupilsightFamilyID)
            //         WHERE pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID1 AND pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID2 AND pupilsightFamilyAdult.childDataAccess='Y'";
            //     $result = $connection2->prepare($sql);
            //     $result->execute($data);
            // } catch (PDOException $e) {
            //     echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            // }

            if ($result->rowCount() > 0) {
                $proceed = true;
            }
        }
    }

    if ($proceed == false) {
        $output .= "<div class='alert alert-danger'>" . __('You do not have permission to access this timetable at this time.') . '</div>';
    } else {
        $self = false;
        // if ($pupilsightPersonID == $_SESSION[$guid]['pupilsightPersonID'] and $edit == false) {
        //     $self = true;
        //     if ($_SESSION[$guid]['viewCalendarSchool'] != false and $_SESSION[$guid]['viewCalendarPersonal'] != false and $_SESSION[$guid]['viewCalendarSpaceBooking'] != false) {
        //         try {
        //             $dataDisplay = array('viewCalendarSchool' => $_SESSION[$guid]['viewCalendarSchool'], 'viewCalendarPersonal' => $_SESSION[$guid]['viewCalendarPersonal'], 'viewCalendarSpaceBooking' => $_SESSION[$guid]['viewCalendarSpaceBooking'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
        //             $sqlDisplay = 'UPDATE pupilsightPerson SET viewCalendarSchool=:viewCalendarSchool, viewCalendarPersonal=:viewCalendarPersonal, viewCalendarSpaceBooking=:viewCalendarSpaceBooking WHERE pupilsightPersonID=:pupilsightPersonID';
        //             $resultDisplay = $connection2->prepare($sqlDisplay);
        //             $resultDisplay->execute($dataDisplay);
        //         } catch (PDOException $e) {
        //             $output .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        //         }
        //     }
        // }

        $blank = true;
        if ($startDayStamp == '') {
            $startDayStamp = time();
        }

        //Find out which timetables I am involved in this year
        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightYearGroupIDList' => $classId, 'pupilsightRollGroupIDList' => $sectionId);
            $sql = "SELECT DISTINCT pupilsightTT.pupilsightTTID, pupilsightTT.name, pupilsightTT.nameShortDisplay FROM pupilsightTT JOIN pupilsightTTDay ON (pupilsightTT.pupilsightTTID=pupilsightTTDay.pupilsightTTID) JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightYearGroupIDList=:pupilsightYearGroupIDList AND FIND_IN_SET(pupilsightRollGroupIDList, :pupilsightRollGroupIDList) AND active='Y' ";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }

        //If I am not involved in any timetables display all within the year

        if ($result->rowCount() == 0) {
            try {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightYearGroupIDList' => $classId, 'pupilsightRollGroupIDList' => $sectionId);
                $sql = "SELECT pupilsightTT.pupilsightTTID, pupilsightTT.name, pupilsightTT.nameShortDisplay FROM pupilsightTT WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightYearGroupIDList=:pupilsightYearGroupIDList AND FIND_IN_SET(pupilsightRollGroupIDList, :pupilsightRollGroupIDList) AND active='Y' ";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
        }

        //link to other TTs

        if ($result->rowcount() > 1) {
            $output .= "<table class='table' cellspacing='0' style='width: 100%'>";
            $output .= '<tr>';
            $output .= '<td>';
            $output .= "<span class='form-label'>" . __('Timetable Chooser') . '</span>: ';
            while ($row = $result->fetch()) {
                $output .= "<form method='post' action='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=$q&pupilsightTTID=" . $row['pupilsightTTID'] . "$params'>";
                $output .= "<input name='ttDate' value='" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], $startDayStamp) . "' type='hidden'>";
                $output .= "<input name='schoolCalendar' value='" . $_SESSION[$guid]['viewCalendarSchool'] . "' type='hidden'>";
                $output .= "<input name='personalCalendar' value='" . $_SESSION[$guid]['viewCalendarPersonal'] . "' type='hidden'>";
                $output .= "<input name='spaceBookingCalendar' value='" . $_SESSION[$guid]['viewCalendarSpaceBooking'] . "' type='hidden'>";
                $output .= "<input name='fromTT' value='Y' type='hidden'>";
                $output .= "<input class='btn btn-link' style='min-width: 30px; margin-top: 0px; float: left' type='submit' value='" . $row['name'] . "'>";
                $output .= '</form>';
            }
            try {
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
            $output .= '</td>';
            $output .= '</tr>';
            $output .= '</table>';

            if ($pupilsightTTID != '') {

                if (isActionAccessible($guid, $connection2, '/modules/Timetable/tt_master.php', 'View Master Timetable')) {
                    $data = array('pupilsightTTID' => $pupilsightTTID);
                    $sql = "SELECT pupilsightTT.pupilsightTTID, pupilsightTT.name, pupilsightTT.nameShortDisplay FROM pupilsightTT WHERE pupilsightTT.pupilsightTTID=:pupilsightTTID";
                } else {
                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightTTID' => $pupilsightTTID);
                    $sql = "SELECT DISTINCT pupilsightTT.pupilsightTTID, pupilsightTT.name, pupilsightTT.nameShortDisplay FROM pupilsightTT JOIN pupilsightTTDay ON (pupilsightTT.pupilsightTTID=pupilsightTTDay.pupilsightTTID) JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightTT.pupilsightTTID=:pupilsightTTID";
                }
            }
            try {
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
        }

        //Display first TT
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $pupilsightTTID = $row['pupilsightTTID'];
            $nameShortDisplay = $row['nameShortDisplay']; //Store day short name display setting for later
            $thisWeek = time();

            if ($title != false) {
                $output .= '<h2>' . $row['name'] . '</h2>';
            }
            $output .= "<table cellspacing='0' class='noIntBorder' style='width: 100%; margin: 10px 0 10px 0'>";
            $output .= '<tr>';
            $output .= "<td style='vertical-align: top;width:300px'>";
            $output .= "<form method='post' action='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=$q&pupilsightTTID=" . $row['pupilsightTTID'] . "$params'>";
            $output .= "<input name='ttDate' value='" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($startDayStamp - (7 * 24 * 60 * 60))) . "' type='hidden'>";
            $output .= "<input name='schoolCalendar' value='" . $_SESSION[$guid]['viewCalendarSchool'] . "' type='hidden'>";
            $output .= "<input name='personalCalendar' value='" . $_SESSION[$guid]['viewCalendarPersonal'] . "' type='hidden'>";
            $output .= "<input name='spaceBookingCalendar' value='" . $_SESSION[$guid]['viewCalendarSpaceBooking'] . "' type='hidden'>";
            $output .= "<input name='fromTT' value='Y' type='hidden'>";
            $output .= "<input name='pupilsightYearGroupID' value='" . $classId . "' type='hidden'>";
            $output .= "<input name='pupilsightRollGroupID' value='" . $sectionId . "' type='hidden'>";
            $output .= "<input class='btn btn-link' style='min-width: 30px; margin-top: 0px; float: left' type='submit' value='< " . __('Last Week') . "'>";
            $output .= '</form>';
            $output .= "<form method='post' action='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=$q&pupilsightTTID=" . $row['pupilsightTTID'] . "$params'>";
            $output .= "<input name='ttDate' value='" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($thisWeek)) . "' type='hidden'>";
            $output .= "<input name='schoolCalendar' value='" . $_SESSION[$guid]['viewCalendarSchool'] . "' type='hidden'>";
            $output .= "<input name='personalCalendar' value='" . $_SESSION[$guid]['viewCalendarPersonal'] . "' type='hidden'>";
            $output .= "<input name='spaceBookingCalendar' value='" . $_SESSION[$guid]['viewCalendarSpaceBooking'] . "' type='hidden'>";
            $output .= "<input name='fromTT' value='Y' type='hidden'>";
            $output .= "<input name='pupilsightYearGroupID' value='" . $classId . "' type='hidden'>";
            $output .= "<input name='pupilsightRollGroupID' value='" . $sectionId . "' type='hidden'>";
            $output .= "<input class='btn btn-link' style='min-width: 30px; margin-top: 0px; float: left' type='submit' value='" . __('This Week') . "'>";
            $output .= '</form>';
            $output .= "<form method='post' action='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=$q&pupilsightTTID=" . $row['pupilsightTTID'] . "$params'>";
            $output .= "<input name='ttDate' value='" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($startDayStamp + (7 * 24 * 60 * 60))) . "' type='hidden'>";
            $output .= "<input name='schoolCalendar' value='" . $_SESSION[$guid]['viewCalendarSchool'] . "' type='hidden'>";
            $output .= "<input name='personalCalendar' value='" . $_SESSION[$guid]['viewCalendarPersonal'] . "' type='hidden'>";
            $output .= "<input name='spaceBookingCalendar' value='" . $_SESSION[$guid]['viewCalendarSpaceBooking'] . "' type='hidden'>";
            $output .= "<input name='fromTT' value='Y' type='hidden'>";
            $output .= "<input name='pupilsightYearGroupID' value='" . $classId . "' type='hidden'>";
            $output .= "<input name='pupilsightRollGroupID' value='" . $sectionId . "' type='hidden'>";
            $output .= "<input class='btn btn-link' style='min-width: 30px; margin-top: 0px; float: left' type='submit' value='" . __('Next Week') . " >'>";
            $output .= '</form>';
            $output .= '</td>';
            $output .= "<td style='vertical-align: top; text-align: right'>";
            // $output .= "<form method='post' action='".$_SESSION[$guid]['absoluteURL']."/index.php?q=$q&pupilsightTTID=".$row['pupilsightTTID']."$params'>";
            // $output .= "<input name='pupilsightYearGroupID' value='".$classId."' type='hidden'>";
            // $output .= "<input name='pupilsightRollGroupID' value='".$sectionId."' type='hidden'>";
            // $output .= '<span class="relative">';
            // $output .= "<input name='ttDate' id='ttDate' maxlength=10 value='".date($_SESSION[$guid]['i18n']['dateFormatPHP'], $startDayStamp)."' type='text' style='height: 28px; width:100px; margin-right: 0px; float: none'> ";
            // $output .= '</span>';
            // $output .= '<script type="text/javascript">';
            // $output .= "var ttDate=new LiveValidation('ttDate');";
            // $output .= 'ttDate.add( Validate.Format, {pattern:';
            // if ($_SESSION[$guid]['i18n']['dateFormatRegEx'] == '') {
            //     $output .= "/^(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$/i";
            // } else {
            //     $output .= $_SESSION[$guid]['i18n']['dateFormatRegEx'];
            // }
            // $output .= ', failureMessage: "Use ';
            // if ($_SESSION[$guid]['i18n']['dateFormat'] == '') {
            //     $output .= 'dd/mm/yyyy';
            // } else {
            //     $output .= $_SESSION[$guid]['i18n']['dateFormat'];
            // }
            // $output .= '." } );';
            // $output .= 'ttDate.add(Validate.Presence);';
            // $output .= '$("#ttDate").datepicker();';
            // $output .= '</script>';

            // $output .= "<input style='margin-top: 0px; margin-right: -2px' type='submit' value='".__('Go')."'>";
            // $output .= "<input name='schoolCalendar' value='".$_SESSION[$guid]['viewCalendarSchool']."' type='hidden'>";
            // $output .= "<input name='personalCalendar' value='".$_SESSION[$guid]['viewCalendarPersonal']."' type='hidden'>";
            // $output .= "<input name='spaceBookingCalendar' value='".$_SESSION[$guid]['viewCalendarSpaceBooking']."' type='hidden'>";
            // $output .= "<input name='fromTT' value='Y' type='hidden'>";
            // $output .= '</form>';
            $output .= '</td>';
            $output .= '</tr>';
            $output .= '</table>';

            //Check which days are school days
            $daysInWeek = 0;
            $days = array();
            $timeStart = '';
            $timeEnd = '';
            try {
                $dataDays = array();
                $sqlDays = "SELECT * FROM pupilsightDaysOfWeek WHERE schoolDay='Y' ORDER BY sequenceNumber";
                $resultDays = $connection2->prepare($sqlDays);
                $resultDays->execute($dataDays);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
            $days = $resultDays->fetchAll();
            $daysInWeek = $resultDays->rowCount();
            foreach ($days as $day) {
                if ($timeStart == '' or $timeEnd == '') {
                    $timeStart = $day['schoolStart'];
                    $timeEnd = $day['schoolEnd'];
                } else {
                    if ($day['schoolStart'] < $timeStart) {
                        $timeStart = $day['schoolStart'];
                    }
                    if ($day['schoolEnd'] > $timeEnd) {
                        $timeEnd = $day['schoolEnd'];
                    }
                }
            }

            //Sunday week adjust for timetable on home page (so Sundays show next week if the week starts on Sunday or Monday—i.e. it's Sunday now and Sunday is not a school day)
            $homeSunday = true;
            if ($q == '' && ($_SESSION[$guid]['firstDayOfTheWeek'] == 'Monday' || $_SESSION[$guid]['firstDayOfTheWeek'] == 'Sunday')) {
                try {
                    $dataDays = array();
                    $sqlDays = "SELECT nameShort FROM pupilsightDaysOfWeek WHERE nameShort='Sun' AND schoolDay='N'";
                    $resultDays = $connection2->prepare($sqlDays);
                    $resultDays->execute($dataDays);
                } catch (PDOException $e) {
                    echo $e->getMessage();
                }
                if ($resultDays->rowCount() == 1) {
                    $homeSunday = false;
                }
            }

            //If school is closed on Sunday, and it is a Sunday, count forward, otherwise count back
            if (!$homeSunday and date('D', $startDayStamp) == 'Sun') {
                $startDayStamp = $startDayStamp + 86400;
            } else {
                while (date('D', $startDayStamp) != $days[0]['nameShort']) {
                    $startDayStamp = $startDayStamp - 86400;
                }
            }

            //Count forward to the end of the week
            $endDayStamp = $startDayStamp + (86400 * ($daysInWeek - 1));

            $schoolCalendarAlpha = 0.85;
            $ttAlpha = 1.0;

            if ($_SESSION[$guid]['viewCalendarSchool'] != 'N' or $_SESSION[$guid]['viewCalendarPersonal'] != 'N' or $_SESSION[$guid]['viewCalendarSpaceBooking'] != 'N') {
                $ttAlpha = 0.75;
            }

            //Get school calendar array
            $allDay = false;
            $eventsSchool = false;
            if ($self == true and $_SESSION[$guid]['viewCalendarSchool'] == 'Y') {
                if ($_SESSION[$guid]['calendarFeed'] != '') {
                    $eventsSchool = getCalendarEvents($connection2, $guid,  $_SESSION[$guid]['calendarFeed'], $startDayStamp, $endDayStamp);
                }
                //Any all days?
                if ($eventsSchool != false) {
                    foreach ($eventsSchool as $event) {
                        if ($event[1] == 'All Day') {
                            $allDay = true;
                        }
                    }
                }
            }

            //Get personal calendar array
            $eventsPersonal = false;
            if ($self == true and $_SESSION[$guid]['viewCalendarPersonal'] == 'Y') {
                if ($_SESSION[$guid]['calendarFeedPersonal'] != '') {
                    $eventsPersonal = getCalendarEvents($connection2, $guid,  $_SESSION[$guid]['calendarFeedPersonal'], $startDayStamp, $endDayStamp);
                }
                //Any all days?
                if ($eventsPersonal != false) {
                    foreach ($eventsPersonal as $event) {
                        if ($event[1] == 'All Day') {
                            $allDay = true;
                        }
                    }
                }
            }

            $spaceBookingAvailable = isActionAccessible($guid, $connection2, '/modules/Timetable/spaceBooking_manage.php');
            $eventsSpaceBooking = false;
            if ($spaceBookingAvailable) {
                //Get space booking array
                if ($self == true and $_SESSION[$guid]['viewCalendarSpaceBooking'] == 'Y') {
                    $eventsSpaceBooking = getSpaceBookingEvents($guid, $connection2, $startDayStamp, $_SESSION[$guid]['pupilsightPersonID']);
                }
            }

            // STAFF COVERAGE
            // Add coverage as a space booking *for now*
            /* Closed By Bikash
            global $container;
            $staffCoverageGateway = $container->get(StaffCoverageGateway::class);

            $criteria = $staffCoverageGateway->newQueryCriteria()
                ->filterBy('startDate', date('Y-m-d', $startDayStamp))
                ->filterBy('endDate', date('Y-m-d', $endDayStamp))
                ->filterBy('status', 'Accepted')
                ->pageSize(0);
            $coverageList = $staffCoverageGateway->queryCoverageByPersonCovering($criteria, $pupilsightPersonID);

            foreach ($coverageList as $coverage) {
                $fullName = Format::name($coverage['titleAbsence'], $coverage['preferredNameAbsence'], $coverage['surnameAbsence'], 'Staff', false, true);
                if (empty($fullName)) {
                    $fullName = Format::name($coverage['titleStatus'], $coverage['preferredNameStatus'], $coverage['surnameStatus'], 'Staff', false, true);
                }

                $eventsSpaceBooking[] = [
                    'Coverage',
                    __('Coverage'),
                    '',
                    $coverage['date'],
                    $coverage['allDay'] == 'N' ? $coverage['timeStart'] : $timeStart,
                    $coverage['allDay'] == 'N' ? $coverage['timeEnd'] : $timeEnd,
                    $fullName,
                    '',
                ];
            }  

            // STAFF ABSENCE
            // Add an absence as a fake all-day personal event, so it doesn't overlap the calendar (which subs need to see!)
            $staffAbsenceGateway = $container->get(StaffAbsenceGateway::class);

            $criteria = $staffAbsenceGateway->newQueryCriteria()
                ->filterBy('dateStart', date('Y-m-d', $startDayStamp))
                ->filterBy('dateEnd', date('Y-m-d', $endDayStamp))
                ->filterBy('status', 'Approved')
                ->pageSize(0);
            $absenceList = $staffAbsenceGateway->queryAbsencesByPerson($criteria, $pupilsightPersonID, false);
            $canViewAbsences = isActionAccessible($guid, $connection2, '/modules/Staff/absences_view_byPerson.php');

            foreach ($absenceList as $absence) {
                $summary = __('Absent');
                if ($absence['coverage'] == 'Accepted') {
                    $summary .= ' - '.__('Coverage').': '.Format::name($absence['titleCoverage'], $absence['preferredNameCoverage'], $absence['surnameCoverage'], 'Staff', false, true);
                }
                $allDay = true;
                $url = $canViewAbsences
                    ? $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/absences_view_details.php&pupilsightStaffAbsenceID='.$absence['pupilsightStaffAbsenceID']
                    : '';
                $eventsPersonal[] = [$summary, 'All Day', strtotime($absence['date']), null, '', $url];
            }
*/
            //Count up max number of all day events in a day
            $eventsCombined = false;
            $maxAllDays = 0;
            if ($allDay == true) {
                if ($eventsPersonal != false and $eventsSchool != false) {
                    $eventsCombined = array_merge($eventsSchool, $eventsPersonal);
                } elseif ($eventsSchool != false) {
                    $eventsCombined = $eventsSchool;
                } elseif ($eventsPersonal != false) {
                    $eventsCombined = $eventsPersonal;
                }

                $eventsCombined = msort($eventsCombined, 2, true);

                $currentAllDays = 0;
                $lastDate = '';
                $currentDate = '';
                foreach ($eventsCombined as $event) {
                    if ($event[1] == 'All Day') {
                        $currentDate = date('Y-m-d', $event[2]);
                        if ($lastDate != $currentDate) {
                            $currentAllDays = 0;
                        }
                        ++$currentAllDays;

                        if ($currentAllDays > $maxAllDays) {
                            $maxAllDays = $currentAllDays;
                        }

                        $lastDate = $currentDate;
                    }
                }
            }

            //Max diff time for week based on timetables
            try {
                $dataDiff = array('date1' => date('Y-m-d', ($startDayStamp + (86400 * 0))), 'date2' => date('Y-m-d', ($endDayStamp + (86400 * 1))), 'pupilsightTTID' => $row['pupilsightTTID']);
                $sqlDiff = 'SELECT DISTINCT pupilsightTTColumn.pupilsightTTColumnID FROM pupilsightTTDay JOIN pupilsightTTDayDate ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID) JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) WHERE (date>=:date1 AND date<=:date2) AND pupilsightTTID=:pupilsightTTID';
                $resultDiff = $connection2->prepare($sqlDiff);
                $resultDiff->execute($dataDiff);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
            while ($rowDiff = $resultDiff->fetch()) {
                try {
                    $dataDiffDay = array('pupilsightTTColumnID' => $rowDiff['pupilsightTTColumnID']);
                    $sqlDiffDay = 'SELECT * FROM pupilsightTTColumnRow WHERE pupilsightTTColumnID=:pupilsightTTColumnID ORDER BY timeStart';
                    $resultDiffDay = $connection2->prepare($sqlDiffDay);
                    $resultDiffDay->execute($dataDiffDay);
                } catch (PDOException $e) {
                    $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                }
                while ($rowDiffDay = $resultDiffDay->fetch()) {
                    if ($rowDiffDay['timeStart'] < $timeStart) {
                        $timeStart = $rowDiffDay['timeStart'];
                    }
                    if ($rowDiffDay['timeEnd'] > $timeEnd) {
                        $timeEnd = $rowDiffDay['timeEnd'];
                    }
                }
            }

            //Max diff time for week based on special days timing change
            try {
                $dataDiff = array('date1' => date('Y-m-d', ($startDayStamp + (86400 * 0))), 'date2' => date('Y-m-d', ($startDayStamp + (86400 * 6))));
                $sqlDiff = "SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date>=:date1 AND date<=:date2 AND type='Timing Change' AND NOT schoolStart IS NULL AND NOT schoolEnd IS NULL";
                $resultDiff = $connection2->prepare($sqlDiff);
                $resultDiff->execute($dataDiff);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
            while ($rowDiff = $resultDiff->fetch()) {
                if ($rowDiff['schoolStart'] < $timeStart) {
                    $timeStart = $rowDiff['schoolStart'];
                }
                if ($rowDiff['schoolEnd'] > $timeEnd) {
                    $timeEnd = $rowDiff['schoolEnd'];
                }
            }

            //Max diff based on school calendar events
            if ($self == true and $eventsSchool != false) {
                foreach ($eventsSchool as $event) {
                    if (date('Y-m-d', $event[2]) <= date('Y-m-d', ($startDayStamp + (86400 * 6)))) {
                        if ($event[1] != 'All Day') {
                            if (date('H:i:s', $event[2]) < $timeStart) {
                                $timeStart = date('H:i:s', $event[2]);
                            }
                            if (date('H:i:s', $event[3]) > $timeEnd) {
                                $timeEnd = date('H:i:s', $event[3]);
                            }
                            if (date('Y-m-d', $event[2]) != date('Y-m-d', $event[3])) {
                                $timeEnd = '23:59:59';
                            }
                        }
                    }
                }
            }

            //Max diff based on personal calendar events
            if ($self == true and $eventsPersonal != false) {
                foreach ($eventsPersonal as $event) {
                    if (date('Y-m-d', $event[2]) <= date('Y-m-d', ($startDayStamp + (86400 * 6)))) {
                        if ($event[1] != 'All Day') {
                            if (date('H:i:s', $event[2]) < $timeStart) {
                                $timeStart = date('H:i:s', $event[2]);
                            }
                            if (date('H:i:s', $event[3]) > $timeEnd) {
                                $timeEnd = date('H:i:s', $event[3]);
                            }
                            if (date('Y-m-d', $event[2]) != date('Y-m-d', $event[3])) {
                                $timeEnd = '23:59:59';
                            }
                        }
                    }
                }
            }

            //Max diff based on space booking events
            if ($self == true and $eventsSpaceBooking != false) {
                foreach ($eventsSpaceBooking as $event) {
                    if ($event[3] <= date('Y-m-d', ($startDayStamp + (86400 * 6)))) {
                        if ($event[4] < $timeStart) {
                            $timeStart = $event[4];
                        }
                        if ($event[5] > $timeEnd) {
                            $timeEnd = $event[5];
                        }
                    }
                }
            }

            //Final calc
            $diffTime = strtotime($timeEnd) - strtotime($timeStart);

            if ($narrow == 'trim') {
                $width = (ceil(640 / $daysInWeek) - 20) . 'px';
            } elseif ($narrow == 'narrow') {
                $width = (ceil(515 / $daysInWeek) - 20) . 'px';
            } else {
                $width = (ceil(690 / $daysInWeek) - 20) . 'px';
            }

            $count = 0;

            $output .= '<div class="overflow-x-scroll sm:overflow-x-auto overflow-y-hidden mb-6">';
            /*$output .= "<table cellspacing='0' class='mini mb-1' cellspacing='0' style='width: ";
            if ($narrow == 'trim') {
                $output .= '700px';
            } elseif ($narrow == 'narrow') {
                $output .= '575px';
            } else {
                $output .= '750px';
            }
            $output .= ";'>";*/
            $output .= "<table cellspacing='0' class='table mb-1' cellspacing='0'>";
            //Spit out controls for displaying calendars
            /*
            if ($self == true and ($_SESSION[$guid]['calendarFeed'] != '' or $_SESSION[$guid]['calendarFeedPersonal'] != '' or $_SESSION[$guid]['viewCalendarSpaceBooking'] != '')) {

                $output .= "<tr class='head' style='height: 37px;'>";
                $output .= "<th class='ttCalendarBar' colspan=" . ($daysInWeek + 1) . '>';
                $output .= "<form method='post' action='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=$q" . $params . "' style='padding: 5px 5px 0 0'>";
                if ($_SESSION[$guid]['calendarFeed'] != '' and $_SESSION[$guid]['googleAPIAccessToken'] != null) {
                    $checked = '';
                    if ($_SESSION[$guid]['viewCalendarSchool'] == 'Y') {
                        $checked = 'checked';
                    }
                    $output .= "<span class='ttSchoolCalendar' style='opacity: $schoolCalendarAlpha'>" . __('School Calendar');
                    $output .= "<input $checked style='margin-left: 3px' type='checkbox' name='schoolCalendar' onclick='submit();'/>";
                    $output .= '</span>';
                }
                if ($_SESSION[$guid]['calendarFeedPersonal'] != '' and isset($_SESSION[$guid]['googleAPIAccessToken'])) {
                    $checked = '';
                    if ($_SESSION[$guid]['viewCalendarPersonal'] == 'Y') {
                        $checked = 'checked';
                    }
                    $output .= "<span class='ttPersonalCalendar' style='opacity: $schoolCalendarAlpha'>" . __('Personal Calendar');
                    $output .= "<input $checked style='margin-left: 3px' type='checkbox' name='personalCalendar' onclick='submit();'/>";
                    $output .= '</span>';
                }
                if ($spaceBookingAvailable) {
                    if ($_SESSION[$guid]['viewCalendarSpaceBooking'] != '') {
                        $checked = '';
                        if ($_SESSION[$guid]['viewCalendarSpaceBooking'] == 'Y') {
                            $checked = 'checked';
                        }
                        $output .= "<span class='ttSpaceBookingCalendar' style='opacity: $schoolCalendarAlpha'><a style='color: #fff' href='" . $_SESSION[$guid]['absoluteURL'] . "/index.php?q=/modules/Timetable/spaceBooking_manage.php'>" . __('Bookings') . '</a> ';
                        $output .= "<input $checked style='margin-left: 3px' type='checkbox' name='spaceBookingCalendar' onclick='submit();'/>";
                        $output .= '</span>';
                    }
                }

                $output .= "<input type='hidden' name='ttDate' value='" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], $startDayStamp) . "'>";
                $output .= "<input name='fromTT' value='Y' type='hidden'>";
                $output .= '</form>';
                $output .= '</th>';
                $output .= '</tr>';
            }
            */

            $output .= "<tr class='head'>";
            $output .= "<th style='vertical-align: top; width: 50px; text-align: center'>";
            //Calculate week number
            $week = getWeekNumber($startDayStamp, $connection2, $guid);
            if ($week != false) {
                $output .= sprintf(__('Week %1$s'), $week) . '<br/>';
            }
            $output .= "<span style='font-weight: normal; font-style: italic;'>" . __('Time') . '<span>';
            $output .= '</th>';
            $count = 0;
            foreach ($days as $day) {
                if ($day['schoolDay'] == 'Y') {
                    if ($count == 0) {
                        $firstSequence = $day['sequenceNumber'];
                    }
                    $dateCorrection = ($day['sequenceNumber'] - 1) - ($firstSequence - 1);

                    unset($rowDay);
                    $color = '';
                    try {
                        $dataDay = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $count))), 'pupilsightTTID' => $pupilsightTTID);
                        $sqlDay = 'SELECT nameShort, color, fontColor FROM pupilsightTTDay JOIN pupilsightTTDayDate ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID) WHERE date=:date AND pupilsightTTID=:pupilsightTTID';
                        $resultDay = $connection2->prepare($sqlDay);
                        $resultDay->execute($dataDay);
                    } catch (PDOException $e) {
                    }
                    if ($resultDay->rowCount() == 1) {
                        $rowDay = $resultDay->fetch();
                        if ($rowDay['color'] != '') {
                            $color .= "; background-color: #" . $rowDay['color'] . "; background-image: none";
                        }
                        if ($rowDay['fontColor'] != '') {
                            $color .= "; color: #" . $rowDay['fontColor'];
                        }
                    }

                    $today = ((date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($startDayStamp + (86400 * $dateCorrection))) == date($_SESSION[$guid]['i18n']['dateFormatPHP'])) ? "class='ttToday'" : '');
                    $output .= "<th $today style='vertical-align: top; text-align: center; width: ";

                    if ($narrow == 'trim') {
                        $output .= (550 / $daysInWeek);
                    } elseif ($narrow == 'narrow') {
                        $output .= (375 / $daysInWeek);
                    } else {
                        $output .= (550 / $daysInWeek);
                    }
                    $output .= "px" . $color . "'>";
                    if ($nameShortDisplay != 'Timetable Day Short Name') {
                        $output .= __($day['nameShort']) . '<br/>';
                    } else {
                        if (!empty($rowDay['nameShort']) && $rowDay['nameShort'] != '') {
                            $output .= $rowDay['nameShort'] . '<br/>';
                        } else {
                            $output .= __($day['nameShort']) . '<br/>';
                        }
                    }
                    $output .= "<span style='font-size: 80%; font-style: italic'>" . date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($startDayStamp + (86400 * $dateCorrection))) . '</span><br/>';
                    try {
                        $dataSpecial = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))));
                        $sqlSpecial = "SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date=:date AND type='Timing Change'";
                        $resultSpecial = $connection2->prepare($sqlSpecial);
                        $resultSpecial->execute($dataSpecial);
                    } catch (PDOException $e) {
                        $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                    }
                    if ($resultSpecial->rowcount() == 1) {
                        $rowSpecial = $resultSpecial->fetch();
                        $output .= "<span style='font-size: 80%; font-weight: bold'><u>" . $rowSpecial['name'] . '</u></span>';
                    }
                    $output .= '</th>';
                }
                $count++;
            }
            $output .= '</tr>';

            //Space for all day events
            if (($eventsSchool == true or $eventsPersonal == true) and $allDay == true and $eventsCombined != null) {
                $output .= "<tr style='height: " . ((31 * $maxAllDays) + 5) . "px'>";
                $output .= "<td style='vertical-align: top; width: 70px; text-align: center; border-top: 1px solid #888; border-bottom: 1px solid #888'>";
                $output .= "<span style='font-size: 80%'><b>" . sprintf(__('All Day%1$s Events'), '<br/>') . '</b></span>';
                $output .= '</td>';
                $output .= "<td colspan=$daysInWeek style='vertical-align: top; width: 70px; text-align: center; border-top: 1px solid #888; border-bottom: 1px solid #888'>";
                $output .= '</td>';
                $output .= '</tr>';
            }

            $output .= "<tr style='height:" . (ceil($diffTime / 60) + 14) . "px'>";
            $output .= "<td class='ttTime' style='height: 300px; width: 75px; text-align: center; vertical-align: top'>";
            $output .= "<div style='position: relative; width: 71px'>";
            $countTime = 0;
            $time = $timeStart;
            $output .= "<div $title style='z-index: " . $zCount . "; position: absolute; top: -3px; width: 71px ; border: none; height: 60px; margin: 0px; padding: 0px; font-size: 92%'>";
            $output .= substr($time, 0, 5) . '<br/>';
            $output .= '</div>';
            $time = date('H:i:s', strtotime($time) + 3600);
            $spinControl = 0;
            while ($time <= $timeEnd and $spinControl < (23 - substr($timeStart, 0, 2))) {
                ++$countTime;
                $output .= "<div $title style='z-index: $zCount; position: absolute; top:" . (($countTime * 60) - 5) . "px ; width: 71px ; border: none; height: 60px; margin: 0px; padding: 0px; font-size: 92%'>";
                $output .= substr($time, 0, 5) . '<br/>';
                $output .= '</div>';
                $time = date('H:i:s', strtotime($time) + 3600);
                ++$spinControl;
            }

            $output .= '</div>';
            $output .= '</td>';

            //Run through days of the week
            foreach ($days as $day) {
                if ($day['schoolDay'] == 'Y') {
                    $dateCorrection = ($day['sequenceNumber'] - 1) - ($firstSequence - 1);

                    //Check to see if day is term time
                    $isDayInTerm = false;
                    try {
                        $dataTerm = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                        $sqlTerm = 'SELECT pupilsightSchoolYearTerm.firstDay, pupilsightSchoolYearTerm.lastDay FROM pupilsightSchoolYearTerm, pupilsightSchoolYear WHERE pupilsightSchoolYearTerm.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID AND pupilsightSchoolYear.pupilsightSchoolYearID=:pupilsightSchoolYearID';
                        $resultTerm = $connection2->prepare($sqlTerm);
                        $resultTerm->execute($dataTerm);
                    } catch (PDOException $e) {
                        $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                    }
                    while ($rowTerm = $resultTerm->fetch()) {
                        if (date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))) >= $rowTerm['firstDay'] and date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))) <= $rowTerm['lastDay']) {
                            $isDayInTerm = true;
                        }
                    }

                    if ($isDayInTerm == true) {
                        //Check for school closure day
                        try {
                            $dataClosure = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $dateCorrection))));
                            $sqlClosure = 'SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date=:date';
                            $resultClosure = $connection2->prepare($sqlClosure);
                            $resultClosure->execute($dataClosure);
                        } catch (PDOException $e) {
                            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                        }
                        if ($resultClosure->rowCount() == 1) {
                            $rowClosure = $resultClosure->fetch();
                            if ($rowClosure['type'] == 'School Closure') {
                                $day = renderTTDayAttendance($guid, $connection2, $row['pupilsightTTID'], false, $startDayStamp, $dateCorrection, $daysInWeek, $pupilsightPersonID, $timeStart, $eventsSchool, $eventsPersonal, $eventsSpaceBooking, $diffTime, $maxAllDays, $narrow, '', '', $edit);
                            } elseif ($rowClosure['type'] == 'Timing Change') {
                                $day = renderTTDayAttendance($guid, $connection2, $row['pupilsightTTID'], true, $startDayStamp, $dateCorrection, $daysInWeek, $classId, $sectionId, $timeStart, $eventsSchool, $eventsPersonal, $eventsSpaceBooking, $diffTime, $maxAllDays, $narrow, $rowClosure['schoolStart'], $rowClosure['schoolEnd'], $edit);
                            }
                        } else {
                            $day = renderTTDayAttendance($guid, $connection2, $row['pupilsightTTID'], true, $startDayStamp, $dateCorrection, $daysInWeek, $classId, $sectionId, $timeStart, $eventsSchool, $eventsPersonal, $eventsSpaceBooking, $diffTime, $maxAllDays, $narrow, '', '', $edit);
                        }
                    } else {
                        $day = renderTTDayAttendance($guid, $connection2, $row['pupilsightTTID'], false, $startDayStamp, $dateCorrection, $daysInWeek, $classId, $sectionId, $timeStart, $eventsSchool, $eventsPersonal, $eventsSpaceBooking, $diffTime, $maxAllDays, $narrow, '', '', $edit);
                    }

                    if ($day == '') {
                        $day .= "<td style='text-align: center; vertical-align: top; font-size: 12px'></td>";
                    }

                    $output .= $day;
                }
            }
            $output .= '</tr>';
            $output .= '</table>';
            $output .= '</div>';
        }
    }

    return $output;
}

function renderTTDayAttendance($guid, $connection2, $pupilsightTTID, $schoolOpen, $startDayStamp, $count, $daysInWeek, $classId, $sectionId, $gridTimeStart, $eventsSchool, $eventsPersonal, $eventsSpaceBooking, $diffTime, $maxAllDays, $narrow, $specialDayStart = '', $specialDayEnd = '', $edit = false)
{
    $schoolCalendarAlpha = 0.90;
    $ttAlpha = 1.0;

    if ($_SESSION[$guid]['viewCalendarSchool'] != 'N' or $_SESSION[$guid]['viewCalendarPersonal'] != 'N' or $_SESSION[$guid]['viewCalendarSpaceBooking'] != 'N') {
        $ttAlpha = 0.75;
    }

    $date = date('Y-m-d', ($startDayStamp + (86400 * $count)));

    $self = false;
    //if ($pupilsightPersonID == $_SESSION[$guid]['pupilsightPersonID'] and $edit == false) {
    $self = true;
    $roleCategory = getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2);
    //}

    if ($narrow == 'trim') {
        $width = (ceil(640 / $daysInWeek) - 20) . 'px';
    } elseif ($narrow == 'narrow') {
        $width = (ceil(515 / $daysInWeek) - 20) . 'px';
    } else {
        $width = (ceil(690 / $daysInWeek) - 20) . 'px';
    }

    $output = '';
    $blank = true;

    $zCount = 0;
    $allDay = 0;

    if ($schoolOpen == false) {
        try {
            $dataSpecialDay = array('date' => $date);
            $sqlSpecialDay = "SELECT name, description FROM pupilsightSchoolYearSpecialDay WHERE date=:date";
            $resultSpecialDay = $connection2->prepare($sqlSpecialDay);
            $resultSpecialDay->execute($dataSpecialDay);
        } catch (PDOException $e) {
        }

        $specialDay = $resultSpecialDay->rowCount() > 0 ? $resultSpecialDay->fetch() : array('name' => '', 'description' => '');

        $output .= "<td style='text-align: center; vertical-align: top; font-size: 11px'>";
        $output .= "<div style='position: relative'>";
        $output .= "<div class='ttClosure' style='z-index: $zCount; position: absolute; width: $width ; height: " . ceil($diffTime / 60) . "px; margin: 0px; padding: 0px; opacity: $ttAlpha'>";
        $output .= "<div style='position: relative; top: 50%' title='" . $specialDay['description'] . "'>";
        $output .= "<span style='color: rgba(255,0,0,$ttAlpha);'>" . __('School Closed');
        $output .= '<br/><br/>' . $specialDay['name'] . '</span>';
        $output .= '</div>';
        $output .= '</div>';

        $zCount = 1;

        //Draw periods from school calendar
        if ($eventsSchool != false) {
            $height = 0;
            $top = 0;
            $dayTimeStart = '';
            foreach ($eventsSchool as $event) {
                if (date('Y-m-d', $event[2]) == date('Y-m-d', ($startDayStamp + (86400 * $count)))) {
                    if ($event[1] == 'All Day') {
                        $label = $event[0];
                        $title = '';
                        if (strlen($label) > 20) {
                            $label = substr($label, 0, 20) . '...';
                            $title = "title='" . $event[0] . "'";
                        }
                        $height = '30px';
                        $top = (($maxAllDays * -31) - 8 + ($allDay * 30)) . 'px';
                        $output .= "<div class='ttSchoolCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                        $output .= "<a target='_blank' href='" . $event[5] . "'>" . $label . '</a>';
                        $output .= '</div>';
                        ++$allDay;
                    } else {
                        $label = $event[0];
                        $title = "title='" . date('H:i', $event[2]) . ' to ' . date('H:i', $event[3]) . "'";
                        $height = ceil(($event[3] - $event[2]) / 60) . 'px';
                        $charCut = 20;
                        if ($height < 20) {
                            $charCut = 12;
                        }
                        if (strlen($label) > $charCut) {
                            $label = substr($label, 0, $charCut) . '...';
                            $title = "title='" . $event[0] . ' (' . date('H:i', $event[2]) . ' to ' . date('H:i', $event[3]) . ")'";
                        }
                        $top = (ceil(($event[2] - strtotime(date('Y-m-d', $startDayStamp + (86400 * $count)) . ' ' . $gridTimeStart)) / 60)) . 'px';
                        $output .= "<div class='ttSchoolCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                        $output .= "<a target='_blank' href='" . $event[5] . "'>" . $label . '</a>';
                        $output .= '</div>';
                    }
                    ++$zCount;
                }
            }
        }

        //Draw periods from personal calendar
        if ($eventsPersonal != false) {
            $height = 0;
            $top = 0;
            $bg = "rgba(103,153,207,$schoolCalendarAlpha)";
            foreach ($eventsPersonal as $event) {
                if (date('Y-m-d', $event[2]) == date('Y-m-d', ($startDayStamp + (86400 * $count)))) {
                    if ($event[1] == 'All Day') {
                        $label = $event[0];
                        $title = '';
                        if (strlen($label) > 20) {
                            $label = substr($label, 0, 20) . '...';
                            $title = "title='" . $event[0] . "'";
                        }
                        $height = '30px';
                        $top = (($maxAllDays * -31) - 8 + ($allDay * 30)) . 'px';
                        $output .= "<div class='ttPersonalCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                        $output .= !empty($event[5])
                            ? "<a target='_blank' href='" . $event[5] . "'>" . $label . '</a>'
                            : $label;
                        $output .= '</div>';
                        ++$allDay;
                    } else {
                        $label = $event[0];
                        $title = "title='" . date('H:i', $event[2]) . ' to ' . date('H:i', $event[3]) . "'";
                        $height = ceil(($event[3] - $event[2]) / 60) . 'px';
                        $charCut = 20;
                        if ($height < 20) {
                            $charCut = 12;
                        }
                        if (strlen($label) > $charCut) {
                            $label = substr($label, 0, $charCut) . '...';
                            $title = "title='" . $event[0] . ' (' . date('H:i', $event[2]) . ' to ' . date('H:i', $event[3]) . ")'";
                        }
                        $top = (ceil(($event[2] - strtotime(date('Y-m-d', $startDayStamp + (86400 * $count)) . ' ' . $gridTimeStart)) / 60)) . 'px';
                        $output .= "<div class='ttPersonalCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                        $output .= !empty($event[5])
                            ? "<a target='_blank' href='" . $event[5] . "'>" . $label . '</a>'
                            : $label;
                        $output .= '</div>';
                    }
                    ++$zCount;
                }
            }
        }
        $output .= '</div>';
        $output .= '</td>';
    } else {
        //Make array of space changes
        $spaceChanges = array();
        try {
            $dataSpaceChange = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $count))));
            $sqlSpaceChange = 'SELECT pupilsightTTSpaceChange.*, pupilsightSpace.name AS space, phoneInternal FROM pupilsightTTSpaceChange LEFT JOIN pupilsightSpace ON (pupilsightTTSpaceChange.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) WHERE date=:date';
            $resultSpaceChange = $connection2->prepare($sqlSpaceChange);
            $resultSpaceChange->execute($dataSpaceChange);
        } catch (PDOException $e) {
        }
        while ($rowSpaceChange = $resultSpaceChange->fetch()) {
            $spaceChanges[$rowSpaceChange['pupilsightTTDayRowClassID']][0] = $rowSpaceChange['space'];
            $spaceChanges[$rowSpaceChange['pupilsightTTDayRowClassID']][1] = $rowSpaceChange['phoneInternal'];
        }

        //Get day start and end!
        $dayTimeStart = '';
        $dayTimeEnd = '';
        try {
            $dataDiff = array('date' => date('Y-m-d', ($startDayStamp + (86400 * $count))), 'pupilsightTTID' => $pupilsightTTID);
            $sqlDiff = 'SELECT timeStart, timeEnd FROM pupilsightTTDay JOIN pupilsightTTDayDate ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID) JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTColumnRow ON (pupilsightTTColumn.pupilsightTTColumnID=pupilsightTTColumnRow.pupilsightTTColumnID) WHERE date=:date AND pupilsightTTID=:pupilsightTTID';
            $resultDiff = $connection2->prepare($sqlDiff);
            $resultDiff->execute($dataDiff);
        } catch (PDOException $e) {
            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }
        while ($rowDiff = $resultDiff->fetch()) {
            if ($dayTimeStart == '') {
                $dayTimeStart = $rowDiff['timeStart'];
            }
            if ($rowDiff['timeStart'] < $dayTimeStart) {
                $dayTimeStart = $rowDiff['timeStart'];
            }
            if ($dayTimeEnd == '') {
                $dayTimeEnd = $rowDiff['timeEnd'];
            }
            if ($rowDiff['timeEnd'] > $dayTimeEnd) {
                $dayTimeEnd = $rowDiff['timeEnd'];
            }
        }
        if ($specialDayStart != '') {
            $dayTimeStart = $specialDayStart;
        }
        if ($specialDayEnd != '') {
            $dayTimeEnd = $specialDayEnd;
        }

        $dayDiffTime = strtotime($dayTimeEnd) - strtotime($dayTimeStart);

        $startPad = strtotime($dayTimeStart) - strtotime($gridTimeStart);

        $today = ((date($_SESSION[$guid]['i18n']['dateFormatPHP'], ($startDayStamp + (86400 * $count))) == date($_SESSION[$guid]['i18n']['dateFormatPHP'])) ? "class='ttToday'" : '');
        $output .= "<td $today style='text-align: center; vertical-align: top; font-size: 11px'>";

        try {
            $dataDay = array('pupilsightTTID' => $pupilsightTTID, 'date' => date('Y-m-d', ($startDayStamp + (86400 * $count))));
            $sqlDay = 'SELECT pupilsightTTDay.pupilsightTTDayID FROM pupilsightTTDayDate JOIN pupilsightTTDay ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) WHERE pupilsightTTID=:pupilsightTTID AND date=:date';
            $resultDay = $connection2->prepare($sqlDay);
            $resultDay->execute($dataDay);
        } catch (PDOException $e) {
            $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }

        if ($resultDay->rowCount() == 1) {
            $rowDay = $resultDay->fetch();
            $zCount = 0;
            $output .= "<div style='position: relative'>";

            //Draw outline of the day
            try {
                $dataPeriods = array('pupilsightTTDayID' => $rowDay['pupilsightTTDayID'], 'date' => date('Y-m-d', ($startDayStamp + (86400 * $count))));
                $sqlPeriods = 'SELECT pupilsightTTColumnRow.name, timeStart, timeEnd, type, date FROM pupilsightTTDay JOIN pupilsightTTDayDate ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID) JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTColumnRow ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) WHERE pupilsightTTDayDate.pupilsightTTDayID=:pupilsightTTDayID AND date=:date ORDER BY timeStart, timeEnd';
                $resultPeriods = $connection2->prepare($sqlPeriods);
                $resultPeriods->execute($dataPeriods);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }
            while ($rowPeriods = $resultPeriods->fetch()) {
                $pdate = $rowPeriods['date'];
                $isSlotInTime = false;
                if ($rowPeriods['timeStart'] <= $dayTimeStart and $rowPeriods['timeEnd'] > $dayTimeStart) {
                    $isSlotInTime = true;
                } elseif ($rowPeriods['timeStart'] >= $dayTimeStart and $rowPeriods['timeEnd'] <= $dayTimeEnd) {
                    $isSlotInTime = true;
                } elseif ($rowPeriods['timeStart'] < $dayTimeEnd and $rowPeriods['timeEnd'] >= $dayTimeEnd) {
                    $isSlotInTime = true;
                }

                if ($isSlotInTime == true) {
                    $effectiveStart = $rowPeriods['timeStart'];
                    $effectiveEnd = $rowPeriods['timeEnd'];
                    if ($dayTimeStart > $rowPeriods['timeStart']) {
                        $effectiveStart = $dayTimeStart;
                    }
                    if ($dayTimeEnd < $rowPeriods['timeEnd']) {
                        $effectiveEnd = $dayTimeEnd;
                    }

                    $height = ceil((strtotime($effectiveEnd) - strtotime($effectiveStart)) / 60) . 'px';
                    $top = ceil(((strtotime($effectiveStart) - strtotime($dayTimeStart)) + $startPad) / 60) . 'px';
                    $title = '';
                    if ($rowPeriods['type'] != 'Lesson' and $height > 15 and $height < 30) {
                        $title = "title='" . substr($effectiveStart, 0, 5) . ' - ' . substr($effectiveEnd, 0, 5) . "'";
                    } elseif ($rowPeriods['type'] != 'Lesson' and $height <= 15) {
                        $title = "title='" . $rowPeriods['name'] . ' (' . substr($effectiveStart, 0, 5) . '-' . substr($effectiveEnd, 0, 5) . ")'";
                    }
                    $class = 'ttGeneric';
                    if ((date('H:i:s') > $effectiveStart) and (date('H:i:s') < $effectiveEnd) and $rowPeriods['date'] == date('Y-m-d')) {
                        $class = 'ttCurrent';
                        $lineHeight = '';
                    }
                    $style = '';
                    if ($rowPeriods['type'] == 'Lesson') {
                        $class = 'ttLesson';
                        $lineHeight = '';
                    }


                    if ($class == 'ttGeneric') {
                        $lineHeight = 'line-height:15px;';
                    }
                    $output .= "<div class='$class' $title style='z-index: $zCount; position: absolute; top: $top; width: $width; height: $height; margin: 0px; padding: 0px; opacity: $ttAlpha; $lineHeight'>";
                    if ($height > 15 and $height < 30) {
                        $output .= $rowPeriods['name'] . '<br/>';
                    } elseif ($height >= 30) {
                        $output .= $rowPeriods['name'] . '<br/>';
                        $output .= '<i>' . substr($effectiveStart, 0, 5) . '-' . substr($effectiveEnd, 0, 5) . '</i><br/>';
                    }
                    $output .= '</div>';
                    ++$zCount;
                }
            }

            //Draw periods from TT
            try {
                $dataPeriods = array('pupilsightTTDayID' => $rowDay['pupilsightTTDayID']);
                $sqlPeriods = "SELECT pupilsightTTDayID, pupilsightTTDayRowClassID, pupilsightTTColumnRow.pupilsightTTColumnRowID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightTTColumnRow.name, pupilsightCourse.pupilsightCourseID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, timeStart, timeEnd, phoneInternal, pupilsightSpace.name AS roomName FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightTTDayRowClass ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightTTDayRowClass.pupilsightCourseClassID) JOIN pupilsightTTColumnRow ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID) LEFT JOIN pupilsightSpace ON (pupilsightTTDayRowClass.pupilsightSpaceID=pupilsightSpace.pupilsightSpaceID) WHERE pupilsightTTDayID=:pupilsightTTDayID  ORDER BY timeStart, timeEnd";
                $resultPeriods = $connection2->prepare($sqlPeriods);
                $resultPeriods->execute($dataPeriods);
            } catch (PDOException $e) {
                $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }

            while ($rowPeriods = $resultPeriods->fetch()) {
                $chksql = 'SELECT pupilsightAttendanceLogCourseClassID FROM pupilsightAttendanceLogCourseClass WHERE pupilsightCourseClassID = "' . $rowPeriods['pupilsightCourseClassID'] . '" AND date = "' . $pdate . '" ORDER BY pupilsightAttendanceLogCourseClassID DESC LIMIT 0,1 ';
                $resultchk = $connection2->query($chksql);
                $chkattn = $resultchk->fetch();
                if (!empty($chkattn['pupilsightAttendanceLogCourseClassID'])) {
                    $attnchk = 'Taken';
                    $chkicon = '<i class="mdi mdi-check-circle"></i>';
                } else {
                    $attnchk = 'notTaken';
                    $chkicon = '';
                }

                $isSlotInTime = false;
                if ($rowPeriods['timeStart'] <= $dayTimeStart and $rowPeriods['timeEnd'] > $dayTimeStart) {
                    $isSlotInTime = true;
                } elseif ($rowPeriods['timeStart'] >= $dayTimeStart and $rowPeriods['timeEnd'] <= $dayTimeEnd) {
                    $isSlotInTime = true;
                } elseif ($rowPeriods['timeStart'] < $dayTimeEnd and $rowPeriods['timeEnd'] >= $dayTimeEnd) {
                    $isSlotInTime = true;
                }

                if ($isSlotInTime == true) {
                    //Check for an exception for the current user
                    // try {
                    //     $dataException = array('pupilsightPersonID' => $pupilsightPersonID);
                    //     $sqlException = 'SELECT * FROM pupilsightTTDayRowClassException WHERE pupilsightTTDayRowClassID='.$rowPeriods['pupilsightTTDayRowClassID'].' AND pupilsightPersonID=:pupilsightPersonID';
                    //     $resultException = $connection2->prepare($sqlException);
                    //     $resultException->execute($dataException);
                    // } catch (PDOException $e) {
                    //     $output .= "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    // }
                    // if ($resultException->rowCount() < 1) {
                    $effectiveStart = $rowPeriods['timeStart'];
                    $effectiveEnd = $rowPeriods['timeEnd'];
                    if ($dayTimeStart > $rowPeriods['timeStart']) {
                        $effectiveStart = $dayTimeStart;
                    }
                    if ($dayTimeEnd < $rowPeriods['timeEnd']) {
                        $effectiveEnd = $dayTimeEnd;
                    }

                    $blank = false;
                    if ($narrow == 'trim') {
                        $width = (ceil(640 / $daysInWeek) - 20) . 'px';
                    } elseif ($narrow == 'narrow') {
                        $width = (ceil(515 / $daysInWeek) - 20) . 'px';
                    } else {
                        $width = (ceil(690 / $daysInWeek) - 20) . 'px';
                    }
                    $height = ceil((strtotime($effectiveEnd) - strtotime($effectiveStart)) / 60) . 'px';
                    $top = (ceil((strtotime($effectiveStart) - strtotime($dayTimeStart)) / 60 + ($startPad / 60))) . 'px';
                    $title = "title='";
                    if ($height < 45) {
                        $title .= __('Time:') . ' ' . substr($effectiveStart, 0, 5) . ' - ' . substr($effectiveEnd, 0, 5) . ' | ';
                        $title .= __('Timeslot:') . ' ' . $rowPeriods['name'] . ' | ';
                    }
                    // if ($rowPeriods['roomName'] != '') {
                    //     if ($rowPeriods['phoneInternal'] != '') {
                    //         if (isset($spaceChanges[$rowPeriods['pupilsightTTDayRowClassID']][0]) == false) {
                    //             $title .= __('Phone:').' '.$rowPeriods['phoneInternal'].' | ';
                    //         } else {
                    //             $title .= __('Phone:').' '.$spaceChanges[$rowPeriods['pupilsightTTDayRowClassID']][1].' | ';
                    //         }
                    //     }
                    // }
                    $title = substr($title, 0, -3);
                    $title .= "'";
                    $class2 = 'ttPeriod';

                    if ((date('H:i:s') > $effectiveStart) and (date('H:i:s') < $effectiveEnd) and $date == date('Y-m-d')) {
                        $class2 = 'ttPeriodCurrent';
                    }

                    //Create div to represent period
                    $fontSize = '100%';
                    if ($height < 60) {
                        $fontSize = '85%';
                    }
                    $output .= "<div class='$class2' $title style='z-index: $zCount; position: absolute; top: $top; width: $width; height: $height; margin: 0px; padding: 0px; opacity: 1; font-size: 12px; font-weight: bold;line-height: 14px;'>";

                    if ($attnchk == 'notTaken') {
                        $timeperiod = substr($effectiveStart, 0, 5) . ' - ' . substr($effectiveEnd, 0, 5);
                        $output .= "<a href='fullscreen.php?q=/modules/Attendance/select_attendance_bySection.php&classid=" . $classId . "&sectionid=" . $sectionId . "&courseid=" . $rowPeriods['pupilsightCourseID'] . "&courseclsid=" . $rowPeriods['pupilsightCourseClassID'] . "&periodid=" . $rowPeriods['pupilsightTTColumnRowID'] . "&periodname=" . $rowPeriods['name'] . "&coursename=" . $rowPeriods['course'] . "&timeperiod=" . $timeperiod . "&attndate=" . $pdate . "&width=800' class= ' thickbox'>";
                    }

                    if ($height >= 45) {
                        $output .= $rowPeriods['name'] . '<br/>';
                        $output .= '<i>' . substr($effectiveStart, 0, 5) . ' - ' . substr($effectiveEnd, 0, 5) . '</i><br/>';
                    }

                    // if (isActionAccessible($guid, $connection2, '/modules/Departments/department_course_class.php') and $edit == false) {
                    //     $output .= "<a style='text-decoration: none; font-weight: bold; font-size: 120%' href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Departments/department_course_class.php&pupilsightCourseClassID='.$rowPeriods['pupilsightCourseClassID']."'>".$rowPeriods['course'].'.'.$rowPeriods['class'].'</a><br/>';
                    // } elseif (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_manage_class_edit.php') and $edit == true) {
                    //     $output .= "<a style='text-decoration: none; font-weight: bold; font-size: 120%' href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Timetable Admin/courseEnrolment_manage_class_edit.php&pupilsightCourseClassID='.$rowPeriods['pupilsightCourseClassID'].'&pupilsightSchoolYearID='.$_SESSION[$guid]['pupilsightSchoolYearID'].'&pupilsightCourseID='.$rowPeriods['pupilsightCourseID']."'>".$rowPeriods['course'].'.'.$rowPeriods['class'].'</a><br/>';
                    // } else {
                    $output .= "<span style='font-size: 120%'><b>" . $rowPeriods['course'] . '.' . $rowPeriods['class'] . '</b></span>';
                    if ($attnchk == 'notTaken') {
                        $output .= '</a><br/>';
                    } else {
                        $output .= '<br/>';
                    }
                    // }
                    if ($edit == false) {
                        if (isset($spaceChanges[$rowPeriods['pupilsightTTDayRowClassID']]) == false) {
                            $output .= $rowPeriods['roomName'];
                            $output .= $chkicon;
                        } else {
                            if ($spaceChanges[$rowPeriods['pupilsightTTDayRowClassID']][0] != '') {
                                $output .= "<span style='border: 1px solid #c00; padding: 0 2px'>" . $spaceChanges[$rowPeriods['pupilsightTTDayRowClassID']][0] . '</span>';
                            } else {
                                $output .= "<span style='border: 1px solid #c00; padding: 0 2px'><i>" . __('No Space Allocated') . '</span>';
                            }
                        }
                    } else {
                        // $output .= "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Timetable Admin/tt_edit_day_edit_class_edit.php&pupilsightTTDayID='.$rowPeriods['pupilsightTTDayID']."&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=".$_SESSION[$guid]['pupilsightSchoolYearID'].'&pupilsightTTColumnRowID='.$rowPeriods['pupilsightTTColumnRowID'].'&pupilsightTTDayRowClass='.$rowPeriods['pupilsightTTDayRowClassID'].'&pupilsightCourseClassID='.$rowPeriods['pupilsightCourseClassID']."'>".$rowPeriods['roomName'].'</a>';
                    }
                    $output .= '</div>';
                    ++$zCount;

                    if ($narrow == 'full' or $narrow == 'trim') {
                        if ($edit == false) {
                            //Add planner link icons for staff looking at own TT.
                            if ($self == true and $roleCategory == 'Staff') {
                                if ($height >= 30) {
                                    $output .= "<div  style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid rgba(136,136,136, $ttAlpha); height: $height; margin: 0px; padding: 0px; background-color: none; pointer-events: none'>";
                                    //Check for lesson plan
                                    $bgImg = 'none';

                                    try {
                                        $dataPlan = array('pupilsightCourseClassID' => $rowPeriods['pupilsightCourseClassID'], 'date' => $date, 'timeStart' => $rowPeriods['timeStart'], 'timeEnd' => $rowPeriods['timeEnd']);
                                        $sqlPlan = 'SELECT name, pupilsightPlannerEntryID FROM pupilsightPlannerEntry WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND date=:date AND timeStart=:timeStart AND timeEnd=:timeEnd GROUP BY name';
                                        $resultPlan = $connection2->prepare($sqlPlan);
                                        $resultPlan->execute($dataPlan);
                                    } catch (PDOException $e) {
                                        $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                    }

                                    // if ($resultPlan->rowCount() == 1) {
                                    //     $rowPlan = $resultPlan->fetch();
                                    //     $output .= "<a style='pointer-events: auto' href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_view_full.php&viewBy=class&pupilsightCourseClassID='.$rowPeriods['pupilsightCourseClassID'].'&pupilsightPlannerEntryID='.$rowPlan['pupilsightPlannerEntryID']."'><img style='float: right; margin: ".(substr($height, 0, -2) - 27)."px 2px 0 0' title='Lesson planned: ".htmlPrep($rowPlan['name'])."' src='".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/iconTick.png'/></a>";
                                    // } elseif ($resultPlan->rowCount() == 0) {
                                    //     $output .= "<a style='pointer-events: auto' href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_add.php&viewBy=class&pupilsightCourseClassID='.$rowPeriods['pupilsightCourseClassID'].'&date='.$date.'&timeStart='.$effectiveStart.'&timeEnd='.$effectiveEnd."'><img style='float: right; margin: ".(substr($height, 0, -2) - 27)."px 2px 0 0' title='Add lesson plan' src='".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a>";
                                    // } else {
                                    // $output .= "<a style='pointer-events: auto' href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner.php&viewBy=class&pupilsightCourseClassID='.$rowPeriods['pupilsightCourseClassID'].'&date='.$date.'&timeStart='.$effectiveStart.'&timeEnd='.$effectiveEnd."'><div style='float: right; margin: ".(substr($height, 0, -2) - 17)."px 5px 0 0'>".__('Error').'</div></a>';
                                    // }
                                    $output .= '</div>';
                                    ++$zCount;
                                }
                            }
                            //Add planner link icons for any one else's TT
                            else {
                                $output .= "<div  style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid rgba(136,136,136, $ttAlpha); height: $height; margin: 0px; padding: 0px; background-color: none; pointer-events: none'>";
                                //Check for lesson plan
                                $bgImg = 'none';

                                try {
                                    $dataPlan = array('pupilsightCourseClassID' => $rowPeriods['pupilsightCourseClassID'], 'date' => $date, 'timeStart' => $rowPeriods['timeStart'], 'timeEnd' => $rowPeriods['timeEnd']);
                                    $sqlPlan = 'SELECT name, pupilsightPlannerEntryID FROM pupilsightPlannerEntry WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND date=:date AND timeStart=:timeStart AND timeEnd=:timeEnd GROUP BY name';
                                    $resultPlan = $connection2->prepare($sqlPlan);
                                    $resultPlan->execute($dataPlan);
                                } catch (PDOException $e) {
                                    $output .= "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                                }
                                if ($resultPlan->rowCount() == 1) {
                                    $rowPlan = $resultPlan->fetch();
                                    $output .= "<a style='pointer-events: auto' href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Planner/planner_view_full.php&viewBy=class&pupilsightCourseClassID=' . $rowPeriods['pupilsightCourseClassID'] . '&pupilsightPlannerEntryID=' . $rowPlan['pupilsightPlannerEntryID'] . "&search=$pupilsightPersonID'><img style='float: right; margin: " . (substr($height, 0, -2) - 27) . "px 2px 0 0' title='" . __('View lesson:') . ' ' . htmlPrep($rowPlan['name']) . "' src='" . $_SESSION[$guid]['absoluteURL'] . '/themes/' . $_SESSION[$guid]['pupilsightThemeName'] . "/img/plus.png'/></a>";
                                } elseif ($resultPlan->rowCount() > 1) {
                                    $output .= "<div style='float: right; margin: " . (substr($height, 0, -2) - 17) . "px 5px 0 0'>" . __('Error') . '</div>';
                                }
                                $output .= '</div>';
                                ++$zCount;
                            }
                        }
                        //Show exception editing
                        elseif ($edit) {
                            $output .= "<div  style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid rgba(136,136,136, $ttAlpha); height: $height; margin: 0px; padding: 0px; background-color: none; pointer-events: none'>";
                            //Check for lesson plan
                            $bgImg = 'none';
                            $output .= "<a style='pointer-events: auto' href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Timetable Admin/tt_edit_day_edit_class_exception.php&pupilsightTTDayID=' . $rowPeriods['pupilsightTTDayID'] . "&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=" . $_SESSION[$guid]['pupilsightSchoolYearID'] . '&pupilsightTTColumnRowID=' . $rowPeriods['pupilsightTTColumnRowID'] . '&pupilsightTTDayRowClass=' . $rowPeriods['pupilsightTTDayRowClassID'] . '&pupilsightCourseClassID=' . $rowPeriods['pupilsightCourseClassID'] . "'><img style='float: right; margin: " . (substr($height, 0, -2) - 27) . "px 2px 0 0' title='" . __('Manage Exceptions') . "' src='" . $_SESSION[$guid]['absoluteURL'] . '/themes/' . $_SESSION[$guid]['pupilsightThemeName'] . "/img/attendance.png'/></a>";
                            $output .= '</div>';
                            ++$zCount;
                        }
                    }
                    // }
                }
            }

            //Draw periods from school calendar
            if ($eventsSchool != false) {
                $height = 0;
                $top = 0;
                foreach ($eventsSchool as $event) {
                    if (date('Y-m-d', $event[2]) == date('Y-m-d', ($startDayStamp + (86400 * $count)))) {
                        if ($event[1] == 'All Day') {
                            $label = $event[0];
                            $title = '';
                            if (strlen($label) > 20) {
                                $label = substr($label, 0, 20) . '...';
                                $title = "title='" . $event[0] . "'";
                            }
                            $height = '30px';
                            $top = (($maxAllDays * -31) - 8 + ($allDay * 30)) . 'px';
                            $output .= "<div class='ttSchoolCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                            $output .= "<a target='_blank' href='" . $event[5] . "'>" . $label . '</a>';
                            $output .= '</div>';
                            ++$allDay;
                        } else {
                            $label = $event[0];
                            $title = "title='" . date('H:i', $event[2]) . ' to ' . date('H:i', $event[3]) . "'";
                            $height = ceil(($event[3] - $event[2]) / 60) . 'px';
                            $charCut = 20;
                            if ($height < 20) {
                                $charCut = 12;
                            }
                            if (strlen($label) > $charCut) {
                                $label = substr($label, 0, $charCut) . '...';
                                $title = "title='" . $event[0] . ' (' . date('H:i', $event[2]) . ' to ' . date('H:i', $event[3]) . ")'";
                            }
                            $top = (ceil(($event[2] - strtotime(date('Y-m-d', $startDayStamp + (86400 * $count)) . ' ' . $gridTimeStart)) / 60)) . 'px';
                            $output .= "<div class='ttSchoolCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                            $output .= "<a target='_blank' href='" . $event[5] . "'>" . $label . '</a>';
                            $output .= '</div>';
                        }
                        ++$zCount;
                    }
                }
            }

            //Draw periods from personal calendar
            if ($eventsPersonal != false) {
                $height = 0;
                $top = 0;
                $bg = "rgba(103,153,207,$schoolCalendarAlpha)";
                foreach ($eventsPersonal as $event) {
                    if (date('Y-m-d', $event[2]) == date('Y-m-d', ($startDayStamp + (86400 * $count)))) {
                        if ($event[1] == 'All Day') {
                            $label = $event[0];
                            $title = '';
                            if (strlen($label) > 20) {
                                $label = substr($label, 0, 20) . '...';
                                $title = "title='" . $event[0] . "'";
                            }
                            $height = '30px';
                            $top = (($maxAllDays * -31) - 8 + ($allDay * 30)) . 'px';
                            $output .= "<div class='ttPersonalCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                            $output .= !empty($event[5])
                                ? "<a target='_blank' href='" . $event[5] . "'>" . $label . '</a>'
                                : $label;
                            $output .= '</div>';
                            ++$allDay;
                        } else {
                            $label = $event[0];
                            $title = "title='" . date('H:i', $event[2]) . ' to ' . date('H:i', $event[3]) . "'";
                            $height = ceil(($event[3] - $event[2]) / 60) . 'px';
                            $charCut = 20;
                            if ($height < 20) {
                                $charCut = 12;
                            }
                            if (strlen($label) > $charCut) {
                                $label = substr($label, 0, $charCut) . '...';
                                $title = "title='" . $event[0] . ' (' . date('H:i', $event[2]) . ' to ' . date('H:i', $event[3]) . ")'";
                            }
                            $top = (ceil(($event[2] - strtotime(date('Y-m-d', $startDayStamp + (86400 * $count)) . ' ' . $gridTimeStart)) / 60)) . 'px';
                            $output .= "<div class='ttPersonalCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                            $output .= !empty($event[5])
                                ? "<a target='_blank' href='" . $event[5] . "'>" . $label . '</a>'
                                : $label;
                            $output .= '</div>';
                        }
                        ++$zCount;
                    }
                }
            }

            $output .= '</div>';
        }

        //Draw space bookings and staff coverage
        if ($eventsSpaceBooking != false) {
            $dayTimeStart = $gridTimeStart;
            $startPad = 0;
            $output .= "<div style='position: relative'>";

            $height = 0;
            $top = 0;
            foreach ($eventsSpaceBooking as $event) {
                if ($event[3] == date('Y-m-d', ($startDayStamp + (86400 * $count)))) {
                    $height = ceil((strtotime(date('Y-m-d', ($startDayStamp + (86400 * $count))) . ' ' . $event[5]) - strtotime(date('Y-m-d', ($startDayStamp + (86400 * $count))) . ' ' . $event[4])) / 60) . 'px';
                    $top = (ceil((strtotime($event[3] . ' ' . $event[4]) - strtotime(date('Y-m-d', $startDayStamp + (86400 * $count)) . ' ' . $dayTimeStart)) / 60 + ($startPad / 60))) . 'px';
                    if ($height < 45) {
                        $label = $event[1];
                        $title = "title='" . substr($event[4], 0, 5) . '-' . substr($event[5], 0, 5) . ' ' . $event[6] . "'";
                    } else {
                        $label = $event[1] . "<br/><span style='font-weight: normal'>" . substr($event[4], 0, 5) . '-' . substr($event[5], 0, 5) . '<br/>' . $event[6] . '</span>';
                        $title = "title='" . ($event[7] ?? '') . "'";
                    }
                    $output .= "<div class='ttSpaceBookingCalendar' $title style='z-index: $zCount; position: absolute; top: $top; width: $width ; border: 1px solid #555; height: $height; margin: 0px; padding: 0px; opacity: $schoolCalendarAlpha'>";
                    $output .= $label;
                    $output .= '</div>';
                    ++$zCount;
                }
            }
            $output .= '</div>';
        }
    }

    $output .= '</td>';

    return $output;
}

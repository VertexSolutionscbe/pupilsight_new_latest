<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include './pupilsight.php';

//Set up for i18n via gettext
if (isset($_SESSION[$guid]['i18n']['code']) && function_exists('gettext')) {
    if ($_SESSION[$guid]['i18n']['code'] != null) {
        putenv('LC_ALL=' . $_SESSION[$guid]['i18n']['code']);
        setlocale(LC_ALL, $_SESSION[$guid]['i18n']['code']);
        bindtextdomain('pupilsight', './i18n');
        textdomain('pupilsight');
        bind_textdomain_codeset('pupilsight', 'UTF-8');
    }
}

//Setup variables
$output = '';
if (isset($_POST['pupilsightTTID'])) {
    $id = $_POST['pupilsightTTID'];
} else {
    $id = '';
}

if (isActionAccessible($guid, $connection2, '/modules/Timetable/tt.php') == false) {
    //Acess denied
    $output .= "<div class='alert alert-danger'>";
    $output .= __('Your request failed because you do not have access to this action.');
    $output .= '</div>';
} else {

    include './modules/Timetable/moduleFunctions.php';

    $ttDate = '';
    if ($_POST['ttDate'] != '') {
        $ttDate = dateConvertToTimestamp(dateConvert($guid, $_POST['ttDate']));
    }

    if ($_POST['fromTT'] == 'Y') {
        if ($_POST['schoolCalendar'] == 'on' or $_POST['schoolCalendar'] == 'Y') {
            $_SESSION[$guid]['viewCalendarSchool'] = 'Y';
        } else {
            $_SESSION[$guid]['viewCalendarSchool'] = 'N';
        }

        if ($_POST['personalCalendar'] == 'on' or $_POST['personalCalendar'] == 'Y') {
            $_SESSION[$guid]['viewCalendarPersonal'] = 'Y';
        } else {
            $_SESSION[$guid]['viewCalendarPersonal'] = 'N';
        }

        if ($_POST['spaceBookingCalendar'] == 'on' or $_POST['spaceBookingCalendar'] == 'Y') {
            $_SESSION[$guid]['viewCalendarSpaceBooking'] = 'Y';
        } else {
            $_SESSION[$guid]['viewCalendarSpaceBooking'] = 'N';
        }
    }
    $tt = renderTT($guid, $connection2, $_SESSION[$guid]['pupilsightPersonID'], $id, false, $ttDate, '', '', 'trim');
    print_r($tt);
    die();
    if ($tt != false) {
        $output .= $tt;
    } else {
        $output .= "<div class='alert alert-danger'>";
        $output .= __('There is no information for the date specified.');
        $output .= '</div>';
    }
}

echo $output;

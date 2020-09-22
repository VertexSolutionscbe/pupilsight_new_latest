<?php
/*
Pupilsight, Flexible & Open School System
*/

include './pupilsight.php';

//Check to see if academic year id variables are set, if not set them
if (isset($_SESSION[$guid]['pupilsightAcademicYearID']) == false or isset($_SESSION[$guid]['pupilsightSchoolYearName']) == false) {
    setCurrentSchoolYear($guid, $connection2);
}

// Sanitize the whole $_POST array
$validator = new \Pupilsight\Data\Validator();
$_POST = $validator->sanitize($_POST);

$calendarFeedPersonal = isset($_POST['calendarFeedPersonal'])? $_POST['calendarFeedPersonal'] : '';
$personalBackground = isset($_POST['personalBackground'])? $_POST['personalBackground'] : '';
$pupilsightThemeIDPersonal = !empty($_POST['pupilsightThemeIDPersonal'])? $_POST['pupilsightThemeIDPersonal'] : null;
$pupilsighti18nIDPersonal = !empty($_POST['pupilsighti18nIDPersonal'])? $_POST['pupilsighti18nIDPersonal'] : null;
$receiveNotificationEmails = isset($_POST['receiveNotificationEmails'])? $_POST['receiveNotificationEmails'] : 'N';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=preferences.php';

$validated = true;

// Validate the personal background URL
if (!empty($personalBackground) && filter_var($personalBackground, FILTER_VALIDATE_URL) === false) {
    $validated = false;
}

// Validate the personal calendar feed
if (!empty($calendarFeedPersonal) && filter_var($calendarFeedPersonal, FILTER_VALIDATE_EMAIL) === false) {
    $validated = false;
}

if (!$validated) {
    $URL .= '&return=error1';
    header("Location: {$URL}");
    exit();
}

try {
    $data = array('calendarFeedPersonal' => $calendarFeedPersonal, 'personalBackground' => $personalBackground, 'pupilsightThemeIDPersonal' => $pupilsightThemeIDPersonal, 'pupilsighti18nIDPersonal' => $pupilsighti18nIDPersonal, 'receiveNotificationEmails' => $receiveNotificationEmails, 'username' => $_SESSION[$guid]['username']);
    $sql = 'UPDATE pupilsightPerson SET calendarFeedPersonal=:calendarFeedPersonal, personalBackground=:personalBackground, pupilsightThemeIDPersonal=:pupilsightThemeIDPersonal, pupilsighti18nIDPersonal=:pupilsighti18nIDPersonal, receiveNotificationEmails=:receiveNotificationEmails WHERE (username=:username)';
    $result = $connection2->prepare($sql);
    $result->execute($data);
} catch (PDOException $e) {
    $URL .= '&return=error2';
    header("Location: {$URL}");
    exit();
}

$smartWorkflowHelp = isset($_POST['smartWorkflowHelp'])? $_POST['smartWorkflowHelp'] : null;
if (!empty($smartWorkflowHelp)) {
    try {
        $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'smartWorkflowHelp' => $smartWorkflowHelp);
        $sql = "UPDATE pupilsightStaff SET smartWorkflowHelp=:smartWorkflowHelp WHERE pupilsightPersonID=:pupilsightPersonID";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit();
    }
}

//Update personal preferences in session
$_SESSION[$guid]['calendarFeedPersonal'] = $calendarFeedPersonal;
$_SESSION[$guid]['personalBackground'] = $personalBackground;
$_SESSION[$guid]['pupilsightThemeIDPersonal'] = $pupilsightThemeIDPersonal;
$_SESSION[$guid]['pupilsighti18nIDPersonal'] = $pupilsighti18nIDPersonal;
$_SESSION[$guid]['receiveNotificationEmails'] = $receiveNotificationEmails;

//Update language settings in session (to personal preference if set, or system default if not)
if (!is_null($pupilsighti18nIDPersonal)) {
    try {
        $data = array('pupilsighti18nID' => $pupilsighti18nIDPersonal);
        $sql = 'SELECT * FROM pupilsighti18n WHERE pupilsighti18nID=:pupilsighti18nID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }
    if ($result->rowCount() == 1) {
        $row = $result->fetch();
        setLanguageSession($guid, $row);
    }
} else {
    try {
        $data = array();
        $sql = "SELECT * FROM pupilsighti18n WHERE systemDefault='Y'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }
    if ($result->rowCount() == 1) {
        $row = $result->fetch();
        setLanguageSession($guid, $row);
    }
}

$_SESSION[$guid]['pageLoads'] = null;
$URL .= '&return=success0';
header("Location: {$URL}");

<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Comms\NotificationEvent;

include '../../pupilsight.php';

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$mode = $_POST['mode'];
$pupilsightActivityID = $_POST['pupilsightActivityID'];
$pupilsightPersonID = $_POST['pupilsightPersonID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/activities_view_register.php&pupilsightActivityID=$pupilsightActivityID&pupilsightPersonID=$pupilsightPersonID&mode=$mode&search=".$_GET['search'];
$URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/activities_view.php&pupilsightPersonID=$pupilsightPersonID&search=".$_GET['search'];

$pupilsightModuleID = getModuleIDFromName($connection2, 'Activities') ;

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_view_register.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, '/modules/Activities/activities_view_register.php', $connection2);
    if ($highestAction == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Get current role category
        $roleCategory = getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2);

        //Check access controls
        $access = getSettingByScope($connection2, 'Activities', 'access');

        if ($access != 'Register') {
            //Fail0
            $URL .= '&return=error0';
            header("Location: {$URL}");
        } else {
            //Proceed!
            //Check if school year specified
            if ($pupilsightActivityID == '' or $pupilsightPersonID == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                $today = date('Y-m-d');
                //Should we show date as term or date?
                $dateType = getSettingByScope($connection2, 'Activities', 'dateType');

                try {
                    if ($dateType != 'Date') {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightActivityID' => $pupilsightActivityID);
                        $sql = "SELECT DISTINCT pupilsightActivity.*, pupilsightStudentEnrolment.pupilsightYearGroupID, pupilsightPerson.surname, pupilsightPerson.preferredName FROM pupilsightActivity JOIN pupilsightStudentEnrolment ON (pupilsightActivity.pupilsightYearGroupIDList LIKE concat( '%', pupilsightStudentEnrolment.pupilsightYearGroupID, '%' )) JOIN pupilsightPerson ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) WHERE pupilsightActivity.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolment.pupilsightPersonID=:pupilsightPersonID AND pupilsightActivityID=:pupilsightActivityID AND NOT pupilsightSchoolYearTermIDList='' AND active='Y' AND registration='Y'";
                    } else {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightActivityID' => $pupilsightActivityID, 'listingStart' => $today, 'listingEnd' => $today);
                        $sql = "SELECT DISTINCT pupilsightActivity.*, pupilsightStudentEnrolment.pupilsightYearGroupID, pupilsightPerson.surname, pupilsightPerson.preferredName FROM pupilsightActivity JOIN pupilsightStudentEnrolment ON (pupilsightActivity.pupilsightYearGroupIDList LIKE concat( '%', pupilsightStudentEnrolment.pupilsightYearGroupID, '%' )) JOIN pupilsightPerson ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) WHERE pupilsightActivity.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolment.pupilsightPersonID=:pupilsightPersonID AND pupilsightActivityID=:pupilsightActivityID AND listingStart<=:listingStart AND listingEnd>=:listingEnd AND active='Y' AND registration='Y'";
                    }
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
                    $row = $result->fetch();

                    // Grab organizer info for notifications
                    try {
                        $dataStaff = array('pupilsightActivityID' => $pupilsightActivityID);
                        $sqlStaff = "SELECT pupilsightPersonID FROM pupilsightActivityStaff WHERE pupilsightActivityID=:pupilsightActivityID AND role='Organiser'";
                        $resultStaff = $connection2->prepare($sqlStaff);
                        $resultStaff->execute($dataStaff);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $pupilsightActivityStaffIDs = ($resultStaff->rowCount() > 0)? $resultStaff->fetchAll(\PDO::FETCH_COLUMN, 0) : array();

                    //Check for existing registration
                    try {
                        $dataReg = array('pupilsightActivityID' => $pupilsightActivityID, 'pupilsightPersonID' => $pupilsightPersonID);
                        $sqlReg = 'SELECT pupilsightActivityStudentID, status FROM pupilsightActivityStudent WHERE pupilsightActivityID=:pupilsightActivityID AND pupilsightPersonID=:pupilsightPersonID';
                        $resultReg = $connection2->prepare($sqlReg);
                        $resultReg->execute($dataReg);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    if ($mode == 'register') {

                        if ($resultReg->rowCount() > 0) {
                            $URL .= '&return=error3';
                            header("Location: {$URL}");
                        } else {
                            //Validate Inputs
                            $backup = getSettingByScope($connection2, 'Activities', 'backupChoice');
                            $pupilsightActivityIDBackup = null;
                            if ($backup == 'N') {
                                $pupilsightActivityIDBackup = null;
                            } elseif ($backup == 'Y') {
                                $pupilsightActivityIDBackup = $_POST['pupilsightActivityIDBackup'];
                            }

                            if ($backup == 'Y' and $pupilsightActivityIDBackup == '') {
                                $URL .= '&error=error1';
                                header("Location: {$URL}");
                            } else {
                                $status = 'Not accepted';
                                $enrolment = getSettingByScope($connection2, 'Activities', 'enrolmentType');

                                //Lock the activityStudent database table
                                try {
                                    $sql = 'LOCK TABLES pupilsightActivityStudent WRITE, pupilsightPerson WRITE, pupilsightLog WRITE';
                                    $result = $connection2->query($sql);
                                } catch (PDOException $e) {
                                    $URL .= '&return=error2';
                                    header("Location: {$URL}");
                                    exit();
                                }

                                if ($enrolment == 'Selection') {
                                    $status = 'Pending';
                                } else {
                                    //Check number of people registered for this activity (if we ignore status it stops people jumping the queue when someone unregisters)
                                    try {
                                        $dataNumberRegistered = array('pupilsightActivityID' => $pupilsightActivityID);
                                        $sqlNumberRegistered = "SELECT * FROM pupilsightActivityStudent JOIN pupilsightPerson ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightActivityID=:pupilsightActivityID";
                                        $resultNumberRegistered = $connection2->prepare($sqlNumberRegistered);
                                        $resultNumberRegistered->execute($dataNumberRegistered);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }

                                    //If activity is full...
                                    if ($resultNumberRegistered->rowCount() >= $row['maxParticipants']) {
                                        $status = 'Waiting List';
                                    } else {
                                        $status = 'Accepted';
                                    }
                                }

                                //Write to database
                                try {
                                    $data = array('pupilsightActivityID' => $pupilsightActivityID, 'pupilsightPersonID' => $pupilsightPersonID, 'status' => $status, 'timestamp' => date('Y-m-d H:i:s', time()), 'pupilsightActivityIDBackup' => $pupilsightActivityIDBackup);
                                    $sql = 'INSERT INTO pupilsightActivityStudent SET pupilsightActivityID=:pupilsightActivityID, pupilsightPersonID=:pupilsightPersonID, status=:status, timestamp=:timestamp, pupilsightActivityIDBackup=:pupilsightActivityIDBackup';
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    $URL .= '&return=error2';
                                    header("Location: {$URL}");
                                    exit();
                                }

                                //Set log
                                setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], $pupilsightModuleID, $_SESSION[$guid]['pupilsightPersonID'], 'Activities - Student Registered', array('pupilsightPersonIDStudent' => $pupilsightPersonID));

                                //Unlock locked database tables
                                try {
                                    $sql = 'UNLOCK TABLES';
                                    $result = $connection2->query($sql);
                                } catch (PDOException $e) {
                                }

                                // Get the start and end date of the activity, depending on which dateType we're using
                                $activityTimespan = getActivityTimespan($connection2, $pupilsightActivityID, $row['pupilsightSchoolYearTermIDList']);

                                // Is the activity running right now?
                                if (time() >= $activityTimespan['start'] && time() <= $activityTimespan['end']) {
                                    // Raise a new notification event
                                    $event = new NotificationEvent('Activities', 'New Activity Registration');

                                    $studentName = formatName('', $row['preferredName'], $row['surname'], 'Student', false);
                                    $notificationText = sprintf(__('%1$s has registered for the activity %2$s (%3$s)'), $studentName, $row['name'], $status);

                                    $event->setNotificationText($notificationText);
                                    $event->setActionLink('/index.php?q=/modules/Activities/activities_manage_enrolment.php&pupilsightActivityID='.$pupilsightActivityID.'&search=&pupilsightSchoolYearTermID=');

                                    $event->addScope('pupilsightPersonIDStudent', $pupilsightPersonID);
                                    $event->addScope('pupilsightYearGroupID', $row['pupilsightYearGroupID']);

                                    foreach ($pupilsightActivityStaffIDs as $pupilsightPersonIDStaff) {
                                        $event->addRecipient($pupilsightPersonIDStaff);
                                    }

                                    $event->sendNotifications($pdo, $pupilsight->session);
                                }

                                if ($status == 'Waiting List') {
                                    $URLSuccess = $URLSuccess.'&return=success2';
                                    header("Location: {$URLSuccess}");
                                } else {
                                    $URLSuccess = $URLSuccess.'&return=success0';
                                    header("Location: {$URLSuccess}");
                                }
                            }
                        }
                    } elseif ($mode == 'unregister') {

                        if ($resultReg->rowCount() < 1) {
                            $URL .= '&return=error3';
                            header("Location: {$URL}");
                        } else {
                            //Write to database
                            try {
                                $data = array('pupilsightActivityID' => $pupilsightActivityID, 'pupilsightPersonID' => $pupilsightPersonID);
                                $sql = 'DELETE FROM pupilsightActivityStudent WHERE pupilsightActivityID=:pupilsightActivityID AND pupilsightPersonID=:pupilsightPersonID';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $URL .= '&return=error2';
                                header("Location: {$URL}");
                                exit();
                            }

                            //Set log
                            setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], $pupilsightModuleID, $_SESSION[$guid]['pupilsightPersonID'], 'Activities - Student Withdrawn', array('pupilsightPersonIDStudent' => $pupilsightPersonID));

                            $reg = $resultReg->fetch();

                            // Raise a new notification event
                            if ($reg['status'] == 'Accepted') {
                                // Get the start and end date of the activity, depending on which dateType we're using
                                $activityTimespan = getActivityTimespan($connection2, $pupilsightActivityID, $row['pupilsightSchoolYearTermIDList']);

                                // Is the activity running right now?
                                if (time() >= $activityTimespan['start'] && time() <= $activityTimespan['end']) {
                                    $event = new NotificationEvent('Activities', 'Student Withdrawn');

                                    $studentName = formatName('', $row['preferredName'], $row['surname'], 'Student', false);
                                    $notificationText = sprintf(__('%1$s has withdrawn from the activity %2$s'), $studentName, $row['name']);

                                    $event->setNotificationText($notificationText);
                                    $event->setActionLink('/index.php?q=/modules/Activities/activities_manage_enrolment.php&pupilsightActivityID='.$pupilsightActivityID.'&search=&pupilsightSchoolYearTermID=');

                                    $event->addScope('pupilsightPersonIDStudent', $pupilsightPersonID);
                                    $event->addScope('pupilsightYearGroupID', $row['pupilsightYearGroupID']);

                                    foreach ($pupilsightActivityStaffIDs as $pupilsightPersonIDStaff) {
                                        $event->addRecipient($pupilsightPersonIDStaff);
                                    }

                                    $event->sendNotifications($pdo, $pupilsight->session);
                                }
                            }

                            //Bump up any waiting in competitive selection, to fill spaces available
                            $enrolment = getSettingByScope($connection2, 'Activities', 'enrolmentType');
                            if ($enrolment == 'Competitive') {
                                //Check to see who is registering in system
                                $studentRegistration = false;
                                $parentRegistration = false ;
                                try {
                                    $dataAccess = array();
                                    $sqlAccess = "SELECT
                                            pupilsightAction.name, pupilsightRole.category
                                        FROM pupilsightAction
                                            JOIN pupilsightPermission ON (pupilsightPermission.pupilsightActionID=pupilsightAction.pupilsightActionID)
                                            JOIN pupilsightRole ON (pupilsightPermission.pupilsightRoleID=pupilsightRole.pupilsightRoleID)
                                        WHERE
                                            pupilsightAction.name IN ('View Activities_studentRegister', 'View Activities_studentRegisterByParent')
                                            AND pupilsightRole.category IN ('Parent','Student')";
                                    $resultAccess = $connection2->prepare($sqlAccess);
                                    $resultAccess->execute($dataAccess);
                                } catch (PDOException $e) {}
                                while ($rowAccess = $resultAccess->fetch()) {
                                    if ($rowAccess['name'] == 'View Activities_studentRegister' && $rowAccess['category'] == 'Student') {
                                        $studentRegistration = true;
                                    }
                                    else if ($rowAccess['name'] == 'View Activities_studentRegisterByParent' && $rowAccess['category'] == 'Parent') {
                                        $parentRegistration = true;
                                    }
                                }

                                //Lock the activityStudent database table
                                try {
                                    $sql = 'LOCK TABLES pupilsightActivityStudent WRITE, pupilsightActivity READ, pupilsightPerson READ, pupilsightNotificationEvent READ, pupilsightModule READ, pupilsightAction READ, pupilsightPermission READ, pupilsightNotificationListener READ, pupilsightNotification WRITE, pupilsightFamilyChild READ, pupilsightFamily READ, pupilsightFamilyAdult READ, pupilsightLog WRITE';
                                    $result = $connection2->query($sql);
                                } catch (PDOException $e) {
                                    $URL .= '&return=error2';
                                    header("Location: {$URL}");
                                    exit();
                                }

                                //Count spaces
                                try {
                                    $dataNumberRegistered = array('pupilsightActivityID' => $pupilsightActivityID);
                                    $sqlNumberRegistered = "SELECT * FROM pupilsightActivityStudent JOIN pupilsightPerson ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightActivityID=:pupilsightActivityID AND pupilsightActivityStudent.status='Accepted'";
                                    $resultNumberRegistered = $connection2->prepare($sqlNumberRegistered);
                                    $resultNumberRegistered->execute($dataNumberRegistered);
                                } catch (PDOException $e) {
                                }

                                //If activity is not full...
                                $spaces = $row['maxParticipants'] - $resultNumberRegistered->rowCount();
                                if ($spaces > 0) {
                                    //Get top of waiting list
                                    try {
                                        $dataBumps = array('pupilsightActivityID' => $pupilsightActivityID);
                                        $sqlBumps = "SELECT pupilsightActivityStudentID, name, pupilsightPerson.pupilsightPersonID, surname, preferredName
                                            FROM pupilsightActivityStudent
                                            JOIN pupilsightActivity ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID)
                                            JOIN pupilsightPerson ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                                        WHERE pupilsightPerson.status='Full'
                                            AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."')
                                            AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."')
                                            AND pupilsightActivityStudent.pupilsightActivityID=:pupilsightActivityID
                                            AND pupilsightActivityStudent.status='Waiting List'
                                        ORDER BY timestamp ASC LIMIT 0, $spaces";
                                        $resultBumps = $connection2->prepare($sqlBumps);
                                        $resultBumps->execute($dataBumps);
                                    } catch (PDOException $e) { }

                                    //Bump students up
                                    while ($rowBumps = $resultBumps->fetch()) {
                                        try {
                                            $dataBump = array('pupilsightActivityStudentID' => $rowBumps['pupilsightActivityStudentID']);
                                            $sqlBump = "UPDATE pupilsightActivityStudent SET status='Accepted' WHERE pupilsightActivityStudentID=:pupilsightActivityStudentID";
                                            $resultBump = $connection2->prepare($sqlBump);
                                            $resultBump->execute($dataBump);
                                        } catch (PDOException $e) {
                                        }

                                        //Set log
                                        setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], $pupilsightModuleID, $_SESSION[$guid]['pupilsightPersonID'], 'Activities - Student Bump', array('pupilsightPersonIDStudent' => $rowBumps['pupilsightPersonID']));

                                        //Raise notifications
                                        $event = new NotificationEvent('Activities', 'Student Bumped');

                                        $studentName = formatName('', $rowBumps['preferredName'], $rowBumps['surname'], 'Student', false);
                                        $notificationText = sprintf(__('%1$s has been bumped into activity %2$s'), $studentName, $rowBumps['name']);

                                        $event->setNotificationText($notificationText);
                                        $event->setActionLink('/index.php?q=/modules/Activities/activities_view.php&pupilsightPersonID='.$rowBumps['pupilsightPersonID']);

                                        //DO WE WANT TO ADD STUDENT/PARENTS HERE, BASED ON ACCESS?
                                        if ($studentRegistration) { //Notify student
                                            $event->addRecipient($rowBumps['pupilsightPersonID']);
                                        }
                                        if ($parentRegistration) { //Notify contact priority 1 parents in associated families
                                            try {
                                                $dataAdult = array('pupilsightPersonID' => $rowBumps['pupilsightPersonID']);
                                                $sqlAdult = "
                                                    SELECT
                                                        pupilsightFamilyAdult.pupilsightPersonID
                                                    FROM pupilsightFamilyChild
                                                        JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
                                                        JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID)
                                                        JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                                                    WHERE
                                                        pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID
                                                        AND childDataAccess='Y'
                                                        AND contactPriority=1
                                                        AND pupilsightPerson.status='Full'";
                                                $resultAdult = $connection2->prepare($sqlAdult);
                                                $resultAdult->execute($dataAdult);
                                            } catch (PDOException $e) { }
                                            while ($rowAdult = $resultAdult->fetch()) {
                                                $event->addRecipient($rowAdult['pupilsightPersonID']);
                                            }
                                        }

                                        $event->sendNotifications($pdo, $pupilsight->session);
                                    }
                                }
                                //Unlock locked database tables
                                try {
                                    $sql = 'UNLOCK TABLES';
                                    $result = $connection2->query($sql);
                                } catch (PDOException $e) {
                                }
                            }

                            $URLSuccess = $URLSuccess.'&return=success1';
                            header("Location: {$URLSuccess}");
                        }
                    }
                }
            }
        }
    }
}

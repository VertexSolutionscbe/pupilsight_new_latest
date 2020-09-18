<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Comms\NotificationEvent;
use Pupilsight\Services\Format;

include '../../pupilsight.php';

$pupilsightPersonID = $_POST['pupilsightPersonID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/in_edit.php&pupilsightPersonID=$pupilsightPersonID&search=".$_GET['search'].'&source='.$_GET['source'].'&pupilsightINDescriptorID='.$_GET['pupilsightINDescriptorID'].'&pupilsightAlertLevelID='.$_GET['pupilsightAlertLevelID'].'&pupilsightRollGroupID='.$_GET['pupilsightRollGroupID'].'&pupilsightYearGroupID='.$_GET['pupilsightYearGroupID'];

if (isActionAccessible($guid, $connection2, '/modules/Individual Needs/in_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false or ($highestAction != 'Individual Needs Records_viewContribute' and $highestAction != 'Individual Needs Records_viewEdit')) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Check access to specified student
        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID);
            $sql = "SELECT pupilsightPerson.pupilsightPersonID, pupilsightStudentEnrolmentID, surname, preferredName, pupilsightYearGroup.nameShort AS yearGroup, pupilsightRollGroup.nameShort AS rollGroup, dateStart, dateEnd, pupilsightYearGroup.pupilsightYearGroupID FROM pupilsightPerson, pupilsightStudentEnrolment, pupilsightYearGroup, pupilsightRollGroup WHERE (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) AND (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) AND (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.pupilsightPersonID=:pupilsightPersonID AND pupilsightPerson.status='Full' ORDER BY surname, preferredName";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            $partialFail = false;
            $row = $result->fetch();

            if ($highestAction == 'Individual Needs Records_viewEdit') {
                //UPDATE STATUS
                $statuses = array();
                if (isset($_POST['status'])) {
                    $statuses = $_POST['status'];
                }
                try {
                    $data = array('pupilsightPersonID' => $pupilsightPersonID);
                    $sql = 'DELETE FROM pupilsightINPersonDescriptor WHERE pupilsightPersonID=:pupilsightPersonID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
                foreach ($statuses as $status) {
                    try {
                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightINDescriptorID' => substr($status, 0, 3), 'pupilsightAlertLevelID' => substr($status, 4, 3));
                        $sql = 'INSERT INTO pupilsightINPersonDescriptor SET pupilsightPersonID=:pupilsightPersonID, pupilsightINDescriptorID=:pupilsightINDescriptorID, pupilsightAlertLevelID=:pupilsightAlertLevelID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                }

                //UPDATE IEP
                $strategies = $_POST['strategies'];
                $targets = $_POST['targets'];
                $notes = $_POST['notes'];
                try {
                    $data = array('pupilsightPersonID' => $pupilsightPersonID);
                    $sql = 'SELECT * FROM pupilsightIN WHERE pupilsightPersonID=:pupilsightPersonID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
                if ($result->rowCount() > 1) {
                    $partialFail = true;
                } else {
                    try {
                        $data = array('strategies' => $strategies, 'targets' => $targets, 'notes' => $notes, 'pupilsightPersonID' => $pupilsightPersonID);
                        if ($result->rowCount() == 1) {
                            $sql = 'UPDATE pupilsightIN SET strategies=:strategies, targets=:targets, notes=:notes WHERE pupilsightPersonID=:pupilsightPersonID';
                        } else {
                            $sql = 'INSERT INTO pupilsightIN SET pupilsightPersonID=:pupilsightPersonID, strategies=:strategies, targets=:targets, notes=:notes';
                        }
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                }

                //Scan through assistants
                $staff = array();
                if (isset($_POST['staff'])) {
                    $staff = $_POST['staff'];
                }
                $comment = $_POST['comment'];
                if (count($staff) > 0) {
                    foreach ($staff as $t) {
                        //Check to see if person is already registered as an assistant
                        try {
                            $dataGuest = array('pupilsightPersonIDAssistant' => $t, 'pupilsightPersonIDStudent' => $pupilsightPersonID);
                            $sqlGuest = 'SELECT * FROM pupilsightINAssistant WHERE pupilsightPersonIDAssistant=:pupilsightPersonIDAssistant AND pupilsightPersonIDStudent=:pupilsightPersonIDStudent';
                            $resultGuest = $connection2->prepare($sqlGuest);
                            $resultGuest->execute($dataGuest);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                        if ($resultGuest->rowCount() == 0) {
                            try {
                                $data = array('pupilsightPersonIDAssistant' => $t, 'pupilsightPersonIDStudent' => $pupilsightPersonID, 'comment' => $comment);
                                $sql = 'INSERT INTO pupilsightINAssistant SET pupilsightPersonIDAssistant=:pupilsightPersonIDAssistant, pupilsightPersonIDStudent=:pupilsightPersonIDStudent, comment=:comment';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }
                }
            } elseif ($highestAction == 'Individual Needs Records_viewContribute') {
                //UPDATE IEP
                $strategies = $_POST['strategies'];
                try {
                    $data = array('pupilsightPersonID' => $pupilsightPersonID);
                    $sql = 'SELECT * FROM pupilsightIN WHERE pupilsightPersonID=:pupilsightPersonID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
                if ($result->rowCount() > 1) {
                    $partialFail = true;
                } else {
                    try {
                        $data = array('strategies' => $strategies, 'pupilsightPersonID' => $pupilsightPersonID);
                        if ($result->rowCount() == 1) {
                            $sql = 'UPDATE pupilsightIN SET strategies=:strategies WHERE pupilsightPersonID=:pupilsightPersonID';
                        } else {
                            $sql = 'INSERT INTO pupilsightIN SET pupilsightPersonID=:pupilsightPersonID, strategies=:strategies';
                        }
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                }
            }

            if (!$partialFail) {
                // Raise a new notification event
                $event = new NotificationEvent('Individual Needs', 'Updated Individual Needs');

                $staffName = Format::name('', $_SESSION[$guid]['preferredName'], $_SESSION[$guid]['surname'], 'Staff', false, true);
                $studentName = Format::name('', $row['preferredName'], $row['surname'], 'Student', false);
                $actionLink = "/index.php?q=/modules/Individual Needs/in_edit.php&pupilsightPersonID=$pupilsightPersonID&search=";

                $event->setNotificationText(sprintf(__('%1$s has updated the individual needs record for %2$s.'), $staffName, $studentName));
                $event->setActionLink($actionLink);

                $event->addScope('pupilsightPersonIDStudent', $pupilsightPersonID);
                $event->addScope('pupilsightYearGroupID', $row['pupilsightYearGroupID']);

                $event->sendNotifications($pdo, $pupilsight->session);
            }

            //DEAL WITH OUTCOME
            if ($partialFail) {
                $URL .= '&return=warning1';
                header("Location: {$URL}");
            } else {
                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}

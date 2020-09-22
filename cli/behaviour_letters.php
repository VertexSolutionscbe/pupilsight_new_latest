<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Contracts\Comms\Mailer;
use Pupilsight\Comms\NotificationEvent;
use Pupilsight\Comms\NotificationSender;
use Pupilsight\Domain\System\NotificationGateway;

require getcwd().'/../pupilsight.php';

getSystemSettings($guid, $connection2);

setCurrentSchoolYear($guid, $connection2);

//Set up for i18n via gettext
if (isset($_SESSION[$guid]['i18n']['code'])) {
    if ($_SESSION[$guid]['i18n']['code'] != null) {
        putenv('LC_ALL='.$_SESSION[$guid]['i18n']['code']);
        setlocale(LC_ALL, $_SESSION[$guid]['i18n']['code']);
        bindtextdomain('pupilsight', getcwd().'/../i18n');
        textdomain('pupilsight');
    }
}

//Check for CLI, so this cannot be run through browser
if (!isCommandLineInterface()) { echo __('This script cannot be run from a browser, only via CLI.');
} else {
    $emailSendCount = 0;
    $emailFailCount = 0;

    // Initialize the notification sender & gateway objects
    $notificationGateway = new NotificationGateway($pdo);
    $notificationSender = new NotificationSender($notificationGateway, $pupilsight->session);

    //Get settings
    $enableDescriptors = getSettingByScope($connection2, 'Behaviour', 'enableDescriptors');
    $enableLevels = getSettingByScope($connection2, 'Behaviour', 'enableLevels');
    $enableBehaviourLetters = getSettingByScope($connection2, 'Behaviour', 'enableBehaviourLetters');
    if ($enableBehaviourLetters == 'Y') {
        $behaviourLettersLetter1Count = getSettingByScope($connection2, 'Behaviour', 'behaviourLettersLetter1Count');
        $behaviourLettersLetter1Text = getSettingByScope($connection2, 'Behaviour', 'behaviourLettersLetter1Text');
        $behaviourLettersLetter2Count = getSettingByScope($connection2, 'Behaviour', 'behaviourLettersLetter2Count');
        $behaviourLettersLetter2Text = getSettingByScope($connection2, 'Behaviour', 'behaviourLettersLetter2Text');
        $behaviourLettersLetter3Count = getSettingByScope($connection2, 'Behaviour', 'behaviourLettersLetter3Count');
        $behaviourLettersLetter3Text = getSettingByScope($connection2, 'Behaviour', 'behaviourLettersLetter3Text');

        if ($behaviourLettersLetter1Count != '' and $behaviourLettersLetter1Text != '' and $behaviourLettersLetter2Count != '' and $behaviourLettersLetter2Text != '' and $behaviourLettersLetter3Count != '' and $behaviourLettersLetter3Text != '' and is_numeric($behaviourLettersLetter1Count) and is_numeric($behaviourLettersLetter2Count) and is_numeric($behaviourLettersLetter3Count)) {
            //SCAN THROUGH ALL STUDENTS
            try {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sql = "SELECT pupilsightPerson.pupilsightPersonID, preferredName, surname, pupilsightRollGroup.pupilsightRollGroupID, pupilsightRollGroup.name AS rollGroup, 'Student' AS role, pupilsightPersonIDTutor, pupilsightPersonIDTutor2, pupilsightPersonIDTutor3 FROM pupilsightPerson, pupilsightStudentEnrolment, pupilsightRollGroup WHERE pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY surname, preferredName";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
            }

            if ($result->rowCount() > 0) {
                while ($row = $result->fetch()) { //For every student
                    $studentName = formatName('', $row['preferredName'], $row['surname'], 'Student', false);
                    $rollGroup = $row['rollGroup'];

                    //Check count of negative behaviour records in the current year
                    try {
                        $dataBehaviour = array('pupilsightPersonID' => $row['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                        $sqlBehaviour = "SELECT * FROM pupilsightBehaviour WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND type='Negative'";
                        $resultBehaviour = $connection2->prepare($sqlBehaviour);
                        $resultBehaviour->execute($dataBehaviour);
                    } catch (PDOException $e) {
                    }
                    $behaviourCount = $resultBehaviour->rowCount();
                    if ($behaviourCount > 0) { //Only worry about students with more than zero negative records in the current year
                        //Get most recent letter entry
                        try {
                            $dataLetters = array('pupilsightPersonID' => $row['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                            $sqlLetters = 'SELECT * FROM pupilsightBehaviourLetter WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY timestamp DESC LIMIT 0, 1';
                            $resultLetters = $connection2->prepare($sqlLetters);
                            $resultLetters->execute($dataLetters);
                        } catch (PDOException $e) {
                        }

                        $newLetterRequired = false;
                        $newLetterRequiredLevel = null;
                        $newLetterRequiredStatus = null;
                        $issueExistingLetter = false;
                        $issueExistingLetterLevel = null;
                        $issueExistingLetterID = null;

                        //DECIDE WHAT LETTERS TO SEND
                        if ($resultLetters->rowCount() != 1) { //NO LETTER EXISTS
                            $lastLetterLevel = null;
                            $lastLetterStatus = null;

                            if ($behaviourCount >= $behaviourLettersLetter3Count) { //Student is over or equal to level 3
                                $newLetterRequired = true;
                                $newLetterRequiredLevel = 3;
                                $newLetterRequiredStatus = 'Issued';
                            } elseif ($behaviourCount >= $behaviourLettersLetter2Count and $behaviourCount < $behaviourLettersLetter3Count) { //Student is equal to or greater than level 2 but less than level 3
                                $newLetterRequired = true;
                                $newLetterRequiredLevel = 2;
                                $newLetterRequiredStatus = 'Issued';
                            } elseif ($behaviourCount >= $behaviourLettersLetter1Count and $behaviourCount < $behaviourLettersLetter2Count) { //Student is equal to or greater than level 1 but less than level 2
                                $newLetterRequired = true;
                                $newLetterRequiredLevel = 1;
                                $newLetterRequiredStatus = 'Issued';
                            } elseif ($behaviourCount == ($behaviourLettersLetter1Count - 1)) { //Student is one less than level 1
                                $newLetterRequired = true;
                                $newLetterRequiredLevel = 1;
                                $newLetterRequiredStatus = 'Warning';
                            }
                        } else { //YES LETTER EXISTS
                            $rowLetters = $resultLetters->fetch();
                            $lastLetterLevel = $rowLetters['letterLevel'];
                            $lastLetterStatus = $rowLetters['status'];

                            if ($behaviourCount > $rowLetters['recordCountAtCreation']) { //Only consider action if count has increased since creation (stops second day issue of warning when count has not changed)
                                if ($lastLetterStatus == 'Warning') { //Last letter is warning
                                    if ($behaviourCount >= ${'behaviourLettersLetter'.$lastLetterLevel.'Count'} and $behaviourCount < ${'behaviourLettersLetter'.($lastLetterLevel + 1).'Count'}) { //Count escalted to above warning, and less than next full level
                                        $issueExistingLetter = true;
                                        $issueExistingLetterID = $rowLetters['pupilsightBehaviourLetterID'];
                                        $issueExistingLetterLevel = $rowLetters['letterLevel'];
                                    } elseif ($behaviourCount >= ${'behaviourLettersLetter'.($lastLetterLevel + 1).'Count'}) { //Count escalated to equal to or above next level
                                        $newLetterRequired = true;
                                        if ($behaviourCount >= $behaviourLettersLetter3Count) {
                                            $newLetterRequiredLevel = 3;
                                        } elseif ($behaviourCount >= $behaviourLettersLetter2Count and $behaviourCount < $behaviourLettersLetter3Count) {
                                            $newLetterRequiredLevel = 2;
                                        }
                                        $newLetterRequiredStatus = 'Issued';
                                    }
                                } else { //Last letter is issued
                                    if ($behaviourCount == (${'behaviourLettersLetter'.($lastLetterLevel + 1).'Count'} - 1)) { //Count escalated to next warning
                                        $newLetterRequired = true;
                                        if ($behaviourCount == ($behaviourLettersLetter3Count - 1)) {
                                            $newLetterRequiredLevel = 3;
                                        } elseif ($behaviourCount == ($behaviourLettersLetter2Count - 1)) {
                                            $newLetterRequiredLevel = 2;
                                        }
                                        $newLetterRequiredStatus = 'Warning';
                                    } elseif ($behaviourCount > (${'behaviourLettersLetter'.($lastLetterLevel + 1).'Count'} - 1)) { //Count escalated above next warning
                                        $newLetterRequired = true;
                                        if ($behaviourCount >= $behaviourLettersLetter3Count) {
                                            $newLetterRequiredLevel = 3;
                                        } elseif ($behaviourCount >= $behaviourLettersLetter2Count) {
                                            $newLetterRequiredLevel = 2;
                                        }
                                        $newLetterRequiredStatus = 'Issued';
                                    }
                                }
                            }
                        }

                        //SEND LETTERS ACCORDING TO DECISIONS ABOVE
                        $email = false;
                        $letterUpdateFail = false;
                        $pupilsightBehaviourLetterID = null;

                        //PREPARE LETTER BODY
                        $body = '';
                        if ($issueExistingLetter or ($newLetterRequired and $newLetterRequiredStatus == 'Issued')) {
                            if ($issueExistingLetter) {
                                $body = ${'behaviourLettersLetter'.$issueExistingLetterLevel.'Text'};
                            } else {
                                $body = ${'behaviourLettersLetter'.$newLetterRequiredLevel.'Text'};
                            }
                            //Prepare behaviour record for replacement
                            $behaviourRecord = '<ul>';
                            try {
                                $dataBehaviourRecord = array('pupilsightPersonID' => $row['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                                $sqlBehaviourRecord = "SELECT * FROM pupilsightBehaviour WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND type='Negative' ORDER BY timestamp DESC";
                                $resultBehaviourRecord = $connection2->prepare($sqlBehaviourRecord);
                                $resultBehaviourRecord->execute($dataBehaviourRecord);
                            } catch (PDOException $e) {
                            }
                            while ($rowBehaviourRecord = $resultBehaviourRecord->fetch()) {
                                $behaviourRecord .= '<li>';
                                $behaviourRecord .= dateConvertBack($guid, substr($rowBehaviourRecord['timestamp'], 0, 10));
                                if ($enableDescriptors == 'Y' and $rowBehaviourRecord['descriptor'] != '') {
                                    $behaviourRecord .= ' - '.$rowBehaviourRecord['descriptor'];
                                }
                                if ($enableLevels == 'Y' and $rowBehaviourRecord['level'] != '') {
                                    $behaviourRecord .= ' - '.$rowBehaviourRecord['level'];
                                }
                                $behaviourRecord .= '</li>';
                            }
                            $behaviourRecord .= '</ul>';

                            //Peform required text replacements
                            $body = str_replace('[studentName]', $studentName, $body);
                            $body = str_replace('[rollGroup]', $rollGroup, $body);
                            $body = str_replace('[behaviourCount]', $behaviourCount, $body);
                            $body = str_replace('[behaviourRecord]', $behaviourRecord, $body);
                            $body = str_replace('[systemEmailSignature]', '<i>'.sprintf(__('Email sent via %1$s at %2$s.'), $_SESSION[$guid]['systemName'], $_SESSION[$guid]['organisationName']).'</i>', $body);
                        }

                        if ($issueExistingLetter) { //Issue existing letter
                            //Update record
                            $pupilsightBehaviourLetterID = $issueExistingLetterID;
                            try {
                                $dataLetter = array('body' => $body, 'pupilsightBehaviourLetterID' => $pupilsightBehaviourLetterID);
                                $sqlLetter = "UPDATE pupilsightBehaviourLetter SET status='Issued', body=:body WHERE pupilsightBehaviourLetterID=:pupilsightBehaviourLetterID";
                                $resultLetter = $connection2->prepare($sqlLetter);
                                $resultLetter->execute($dataLetter);
                            } catch (PDOException $e) {
                                $letterUpdateFail = true;
                            }

                            if ($letterUpdateFail == false) {
                                //Flag parents to receive email
                                $email = true;

                                //Notify tutor(s)
                                $notificationText = sprintf(__('A student (%1$s) in your form group has received a behaviour letter.'), $studentName);
                                if ($row['pupilsightPersonIDTutor'] != '') {
                                    $notificationSender->addNotification($row['pupilsightPersonIDTutor'], $notificationText, 'Behaviour', '/index.php?q=/modules/Behaviour/behaviour_letters.php&pupilsightPersonID='.$row['pupilsightPersonID']);
                                }
                                if ($row['pupilsightPersonIDTutor2'] != '') {
                                    $notificationSender->addNotification($row['pupilsightPersonIDTutor2'], $notificationText, 'Behaviour', '/index.php?q=/modules/Behaviour/behaviour_letters.php&pupilsightPersonID='.$row['pupilsightPersonID']);
                                }
                                if ($row['pupilsightPersonIDTutor3'] != '') {
                                    $notificationSender->addNotification($row['pupilsightPersonIDTutor3'], $notificationText, 'Behaviour', '/index.php?q=/modules/Behaviour/behaviour_letters.php&pupilsightPersonID='.$row['pupilsightPersonID']);
                                }
                            }
                        } elseif ($newLetterRequired) { //Issue new letter
                            if ($newLetterRequiredStatus == 'Warning') { //It's a warning
                                //Create new record
                                try {
                                    $dataLetter = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $row['pupilsightPersonID'], 'letterLevel' => $newLetterRequiredLevel, 'recordCountAtCreation' => $behaviourCount, 'body' => $body);
                                    $sqlLetter = "INSERT INTO pupilsightBehaviourLetter SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightPersonID=:pupilsightPersonID, letterLevel=:letterLevel, status='Warning', recordCountAtCreation=:recordCountAtCreation, body=:body";
                                    $resultLetter = $connection2->prepare($sqlLetter);
                                    $resultLetter->execute($dataLetter);
                                } catch (PDOException $e) {
                                    $letterUpdateFail = true;
                                }

                                if ($letterUpdateFail == false) {
                                    $pupilsightBehaviourLetterID = $connection2->lastInsertID();

                                    //Notify tutor(s)
                                    $notificationText = sprintf(__('A warning has been issued for a student (%1$s) in your form group, pending a behaviour letter.'), $studentName);
                                    if ($row['pupilsightPersonIDTutor'] != '') {
                                        $notificationSender->addNotification($row['pupilsightPersonIDTutor'], $notificationText, 'Behaviour', '/index.php?q=/modules/Behaviour/behaviour_letters.php&pupilsightPersonID='.$row['pupilsightPersonID']);
                                    }
                                    if ($row['pupilsightPersonIDTutor2'] != '') {
                                        $notificationSender->addNotification($row['pupilsightPersonIDTutor2'], $notificationText, 'Behaviour', '/index.php?q=/modules/Behaviour/behaviour_letters.php&pupilsightPersonID='.$row['pupilsightPersonID']);
                                    }
                                    if ($row['pupilsightPersonIDTutor3'] != '') {
                                        $notificationSender->addNotification($row['pupilsightPersonIDTutor3'], $notificationText, 'Behaviour', '/index.php?q=/modules/Behaviour/behaviour_letters.php&pupilsightPersonID='.$row['pupilsightPersonID']);
                                    }

                                    //Notify teachers
                                    $notificationText = sprintf(__('A warning has been issued for a student (%1$s) in one of your classes, pending a behaviour letter.'), $studentName);
                                    try {
                                        $dataTeachers = array('pupilsightPersonID' => $row['pupilsightPersonID']);
                                        $sqlTeachers = "SELECT DISTINCT teacher.pupilsightPersonID FROM pupilsightPerson AS teacher JOIN pupilsightCourseClassPerson AS teacherClass ON (teacherClass.pupilsightPersonID=teacher.pupilsightPersonID)  JOIN pupilsightCourseClassPerson AS studentClass ON (studentClass.pupilsightCourseClassID=teacherClass.pupilsightCourseClassID) JOIN pupilsightPerson AS student ON (studentClass.pupilsightPersonID=student.pupilsightPersonID) JOIN pupilsightCourseClass ON (studentClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE teacher.status='Full' AND teacherClass.role='Teacher' AND studentClass.role='Student' AND student.pupilsightPersonID=:pupilsightPersonID AND pupilsightCourse.pupilsightSchoolYearID=(SELECT pupilsightSchoolYearID FROM pupilsightSchoolYear WHERE status='Current') ORDER BY teacher.preferredName, teacher.surname, teacher.email ;";
                                        $resultTeachers = $connection2->prepare($sqlTeachers);
                                        $resultTeachers->execute($dataTeachers);
                                    } catch (PDOException $e) {
                                    }
                                    while ($rowTeachers = $resultTeachers->fetch()) {
                                        $notificationSender->addNotification($rowTeachers['pupilsightPersonID'], $notificationText, 'Behaviour', '/index.php?q=/modules/Behaviour/behaviour_letters.php&pupilsightPersonID='.$row['pupilsightPersonID']);
                                    }
                                }
                            } else { //It's being issued
                                //Create new record
                                try {
                                    $dataLetter = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $row['pupilsightPersonID'], 'letterLevel' => $newLetterRequiredLevel, 'recordCountAtCreation' => $behaviourCount, 'body' => $body);
                                    $sqlLetter = "INSERT INTO pupilsightBehaviourLetter SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightPersonID=:pupilsightPersonID, letterLevel=:letterLevel, status='Issued', recordCountAtCreation=:recordCountAtCreation, body=:body";
                                    $resultLetter = $connection2->prepare($sqlLetter);
                                    $resultLetter->execute($dataLetter);
                                } catch (PDOException $e) {
                                    $letterUpdateFail = true;
                                }

                                if ($letterUpdateFail == false) {
                                    //Flag parents to receive email
                                    $email = true;

                                    $pupilsightBehaviourLetterID = $connection2->lastInsertID();

                                    //Notify tutor(s)
                                    $notificationText = sprintf(__('A student (%1$s) in your form group has received a behaviour letter.'), $studentName);
                                    if ($row['pupilsightPersonIDTutor'] != '') {
                                        $notificationSender->addNotification($row['pupilsightPersonIDTutor'], $notificationText, 'Behaviour', '/index.php?q=/modules/Behaviour/behaviour_letters.php&pupilsightPersonID='.$row['pupilsightPersonID']);
                                    }
                                    if ($row['pupilsightPersonIDTutor2'] != '') {
                                        $notificationSender->addNotification($row['pupilsightPersonIDTutor2'], $notificationText, 'Behaviour', '/index.php?q=/modules/Behaviour/behaviour_letters.php&pupilsightPersonID='.$row['pupilsightPersonID']);
                                    }
                                    if ($row['pupilsightPersonIDTutor3'] != '') {
                                        $notificationSender->addNotification($row['pupilsightPersonIDTutor3'], $notificationText, 'Behaviour', '/index.php?q=/modules/Behaviour/behaviour_letters.php&pupilsightPersonID='.$row['pupilsightPersonID']);
                                    }
                                }
                            }
                        }

                        //DEAL WIITH EMAILS
                        if ($email) {
                            $recipientList = '';
                            //Send emails
                            try {
                                $dataMember = array('pupilsightPersonID' => $row['pupilsightPersonID']);
                                $sqlMember = "SELECT DISTINCT email, preferredName, surname FROM pupilsightFamilyChild JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID AND pupilsightPerson.status='Full' AND contactEmail='Y' ORDER BY contactPriority, surname, preferredName";
                                $resultMember = $connection2->prepare($sqlMember);
                                $resultMember->execute($dataMember);
                            } catch (PDOException $e) {
                            }
                            while ($rowMember = $resultMember->fetch()) {
                                ++$emailSendCount;
                                if ($rowMember['email'] == '') {
                                    ++$emailFailCount;
                                } else {
                                    $recipientList .= $rowMember['email'].', ';

                                    //Prep message
                                    $body .= '<br/><br/><i>'.sprintf(__('Email sent via %1$s at %2$s.'), $_SESSION[$guid]['systemName'], $_SESSION[$guid]['organisationName']).'</i>';
                                    $bodyPlain = emailBodyConvert($body);

                                    $mail = $container->get(Mailer::class);

                                    $mail->AddAddress($rowMember['email'], $rowMember['surname'].', '.$rowMember['preferredName']);
                                    if ($_SESSION[$guid]['organisationEmail'] != '') {
                                        $mail->SetFrom($_SESSION[$guid]['organisationEmail'], $_SESSION[$guid]['organisationName']);
                                    } else {
                                        $mail->SetFrom($_SESSION[$guid]['organisationAdministratorEmail'], $_SESSION[$guid]['organisationAdministratorName']);
                                    }
                                    $mail->CharSet = 'UTF-8';
                                    $mail->Encoding = 'base64';
                                    $mail->IsHTML(true);
                                    $mail->Subject = sprintf(__('Behaviour Letter for %1$s via %2$s at %3$s'), $row['surname'].', '.$row['preferredName'].' ('.$row['rollGroup'].')', $_SESSION[$guid]['systemName'], $_SESSION[$guid]['organisationName']);
                                    $mail->Body = $body;
                                    $mail->AltBody = $bodyPlain;

                                    if (!$mail->Send()) {
                                        ++$emailFailCount;
                                    }
                                }
                            }

                            if ($recipientList != '') {
                                $recipientList = substr($recipientList, 0, -2);

                                //Record email recipients in letter record
                                try {
                                    $dataUpdate = array('recipientList' => $recipientList, 'pupilsightBehaviourLetterID' => $pupilsightBehaviourLetterID);
                                    $sqlUpdate = 'UPDATE pupilsightBehaviourLetter set recipientList=:recipientList WHERE pupilsightBehaviourLetterID=:pupilsightBehaviourLetterID';
                                    $resultUpdate = $connection2->prepare($sqlUpdate);
                                    $resultUpdate->execute($dataUpdate);
                                } catch (PDOException $e) {
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // Raise a new notification event
    $event = new NotificationEvent('Behaviour', 'Behaviour Letters');

    //Notify admin
    if ($email == false) {
        $event->setNotificationText(__('The Behaviour Letter CLI script has run: no emails were sent.'));
    } else {
        $event->setNotificationText(sprintf(__('The Behaviour Letter CLI script has run: %1$s emails were sent, of which %2$s failed.'), $emailSendCount, $emailFailCount));
    }

    $event->setActionLink('/index.php?q=/modules/Behaviour/behaviour_letters.php');

    // Add admin, then push the event to the notification sender
    $event->addRecipient($_SESSION[$guid]['organisationAdministrator']);
    $event->pushNotifications($notificationGateway, $notificationSender);

    // Send all notifications
    $sendReport = $notificationSender->sendNotifications();

    // Output the result to terminal
    echo sprintf('Sent %1$s notifications: %2$s inserts, %3$s updates, %4$s emails sent, %5$s emails failed.', $sendReport['count'], $sendReport['inserts'], $sendReport['updates'], $sendReport['emailSent'], $sendReport['emailFailed'])."\n";
}

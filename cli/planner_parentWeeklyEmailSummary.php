<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Contracts\Comms\Mailer;
use Pupilsight\Comms\NotificationEvent;

require getcwd().'/../pupilsight.php';

getSystemSettings($guid, $connection2);

setCurrentSchoolYear($guid, $connection2);

$parentWeeklyEmailSummaryIncludeBehaviour = getSettingByScope($connection2, 'Planner', 'parentWeeklyEmailSummaryIncludeBehaviour');
$parentWeeklyEmailSummaryIncludeMarkbook = getSettingByScope($connection2, 'Planner', 'parentWeeklyEmailSummaryIncludeMarkbook');

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
if (!isCommandLineInterface()) {
	print __("This script cannot be run from a browser, only via CLI.") ;
}
else {
    //Check that one of the days in question is a school day
    $isSchoolOpen = false;
    for ($i = 0; $i < 7; ++$i) {
        if (isSchoolOpen($guid, date('Y-m-d', strtotime("-$i day")), $connection2, true) == true) {
            $isSchoolOpen = true;
        }
    }

    if ($isSchoolOpen == false) { //No school on any day in the last week
        echo __('School is not open, so no emails will be sent.');
    } else { //Yes school, so go ahead.
        if ($_SESSION[$guid]['organisationEmail'] == '') {
            echo __('This script cannot be run, as no school email address has been set.');
        } else {
            //Prep for email sending later
            $mail = $container->get(Mailer::class);
            $mail->SMTPKeepAlive = true;

            //Lock table
            $lock = true;
            try {
                $sqlLock = 'LOCK TABLE pupilsightBehaviour READ, pupilsightCourse READ, pupilsightCourse AS pupilsightCourse2 READ, pupilsightCourseClass READ, pupilsightCourseClass AS pupilsightCourseClass2 READ, pupilsightCourseClassPerson READ, pupilsightCourseClassPerson AS pupilsightCourseClassPerson2 READ, pupilsightFamily READ, pupilsightFamilyAdult READ, pupilsightFamilyChild READ, pupilsightPerson READ, pupilsightPlannerEntry READ, pupilsightPlannerEntry AS pupilsightPlannerEntry2 READ, pupilsightPlannerEntryStudentHomework READ, pupilsightPlannerParentWeeklyEmailSummary WRITE, pupilsightRollGroup READ, pupilsightStudentEnrolment READ, pupilsightMarkbookEntry READ, pupilsightMarkbookColumn READ';
                $resultLock = $connection2->query($sqlLock);
            } catch (PDOException $e) {
                $lock = false;
            }

            if (!$lock) {
                echo __('Your request failed due to a database error.');
            } else {
                //Get list of all current students
                try {
                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sql = "SELECT pupilsightPerson.pupilsightPersonID, preferredName, surname, pupilsightRollGroup.pupilsightRollGroupID, pupilsightRollGroup.name AS name, 'Student' AS role FROM pupilsightPerson, pupilsightStudentEnrolment, pupilsightRollGroup WHERE pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY surname, preferredName";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) { }

                $studentCount = $result->rowCount();
                $sendSucceedCount = 0;
                $sendFailCount = 0;

                if ($studentCount < 1) { //No students to display
                    echo __('There are no records to display.');
                } else { //Students to display so get going
                    while ($row = $result->fetch()) {
                        //Get all homework for the past week, ready for email
                        $homework = '';
                        $homework .= '<h2>'.__('Homework').'</h2>';
                        $homework .= '<p>'.__('The list below includes all homework assigned during the past week.').'</p>';
                        try {
                            $dataHomework = array('pupilsightPersonID1' => $row['pupilsightPersonID'], 'pupilsightSchoolYearID1' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID2' => $row['pupilsightPersonID'], 'pupilsightSchoolYearID2' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                            $sqlHomework = "
    						(SELECT 'teacherRecorded' AS type, pupilsightPlannerEntryID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, viewableStudents, viewableParents, homework, role, homeworkDueDateTime, homeworkDetails, homeworkSubmission, homeworkSubmissionRequired FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID1 AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND homework='Y' AND pupilsightSchoolYearID=:pupilsightSchoolYearID1 AND date>'".date('Y-m-d', strtotime('-1 week'))."' AND date<='".date('Y-m-d')."')
    						UNION
    						(SELECT 'studentRecorded' AS type, pupilsightPlannerEntry2.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightPlannerEntry2.pupilsightCourseClassID, pupilsightCourse2.nameShort AS course, pupilsightCourseClass2.nameShort AS class, pupilsightPlannerEntry2.name, date, timeStart, timeEnd, 'Y' AS viewableStudents, 'Y' AS viewableParents, 'Y' AS homework, role, pupilsightPlannerEntryStudentHomework.homeworkDueDateTime AS homeworkDueDateTime, pupilsightPlannerEntryStudentHomework.homeworkDetails AS homeworkDetails, 'N' AS homeworkSubmission, '' AS homeworkSubmissionRequired FROM pupilsightPlannerEntry AS pupilsightPlannerEntry2 JOIN pupilsightCourseClass AS pupilsightCourseClass2 ON (pupilsightPlannerEntry2.pupilsightCourseClassID=pupilsightCourseClass2.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson AS pupilsightCourseClassPerson2 ON (pupilsightCourseClass2.pupilsightCourseClassID=pupilsightCourseClassPerson2.pupilsightCourseClassID) JOIN pupilsightCourse AS pupilsightCourse2 ON (pupilsightCourse2.pupilsightCourseID=pupilsightCourseClass2.pupilsightCourseID) JOIN pupilsightPlannerEntryStudentHomework ON (pupilsightPlannerEntryStudentHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry2.pupilsightPlannerEntryID AND pupilsightPlannerEntryStudentHomework.pupilsightPersonID=pupilsightCourseClassPerson2.pupilsightPersonID) WHERE pupilsightCourseClassPerson2.pupilsightPersonID=:pupilsightPersonID2 AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND date>'".date('Y-m-d', strtotime('-1 week'))."' AND date<='".date('Y-m-d')."')
    						ORDER BY date, timeStart";
                            $resultHomework = $connection2->prepare($sqlHomework);
                            $resultHomework->execute($dataHomework);
                        } catch (PDOException $e) {
                            $homework .= $e->getMessage();
                        }
                        if ($resultHomework->rowCount() > 0) {
                            $homework .= '<ul>';
                            while ($rowHomework = $resultHomework->fetch()) {
                                $homework .= '<li><b>'.$rowHomework['course'].'.'.$rowHomework['class'].'</b> - '.$rowHomework['name'].' - '.sprintf(__('Due on %1$s at %2$s.'), dateConvertBack($guid, substr($rowHomework['homeworkDueDateTime'], 0, 10)), substr($rowHomework['homeworkDueDateTime'], 11, 5)).'</li>';
                            }
                            $homework .= '</ul><br/>';
                        } else {
                            $homework .= __('There are no records to display.').'<br/><br/>';
                        }

                        $behaviour = '';
                        if ($parentWeeklyEmailSummaryIncludeBehaviour == 'Y') {
                            //Get behaviour records for the past week, ready for email
                            $behaviour .= '<h2>'.__('Behaviour').'</h2>';
                            try {
                                $dataBehaviourPositive = array('pupilsightPersonID' => $row['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                                $sqlBehaviourPositive = "SELECT * FROM pupilsightBehaviour WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND type='Positive' AND date>'".date('Y-m-d', strtotime('-1 week'))."' AND date<='".date('Y-m-d')."'";
                                $resultBehaviourPositive = $connection2->prepare($sqlBehaviourPositive);
                                $resultBehaviourPositive->execute($dataBehaviourPositive);
                            } catch (PDOException $e) {
                            }
                            try {
                                $dataBehaviourNegative = array('pupilsightPersonID' => $row['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                                $sqlBehaviourNegative = "SELECT * FROM pupilsightBehaviour WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND type='Negative' AND date>'".date('Y-m-d', strtotime('-1 week'))."' AND date<='".date('Y-m-d')."'";
                                $resultBehaviourNegative = $connection2->prepare($sqlBehaviourNegative);
                                $resultBehaviourNegative->execute($dataBehaviourNegative);
                            } catch (PDOException $e) {
                            }
                            $behaviour .= '<ul>';
                            $behaviour .= '<li>'.__('Positive behaviour records this week').': '.$resultBehaviourPositive->rowCount().'</li>';
                            $behaviour .= '<li>'.__('Negative behaviour records this week').': '.$resultBehaviourNegative->rowCount().'</li>';
                            $behaviour .= '</ul><br/>';
                        }

                        $markbook = '';
                        if ($parentWeeklyEmailSummaryIncludeMarkbook == 'Y') {
                            try {
                                $dataMarkbook = array('pupilsightPersonID' => $row['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                                $sqlMarkbook = "
                                    SELECT
                                        CONCAT(pupilsightCourse.nameShort, '.', pupilsightCourseClass.nameShort) AS class,
                                        pupilsightMarkbookColumn.name
                                    FROM
                                        pupilsightMarkbookEntry
                                        JOIN pupilsightMarkbookColumn ON (pupilsightMarkbookEntry.pupilsightMarkbookColumnID=pupilsightMarkbookColumn.pupilsightMarkbookColumnID)
                                        JOIN pupilsightCourseClass ON (pupilsightMarkbookColumn.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                                        JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID)
                                        JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID AND pupilsightMarkbookEntry.pupilsightPersonIDStudent=pupilsightCourseClassPerson.pupilsightPersonID)
                                    WHERE
                                        pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID
                                        AND pupilsightMarkbookEntry.pupilsightPersonIDStudent=:pupilsightPersonID
                                        AND complete='Y'
                                        AND completeDate >'".date('Y-m-d', strtotime('-1 week'))."'
                                        AND completeDate <='".date('Y-m-d')."'";
                                $resultMarkbook = $connection2->prepare($sqlMarkbook);
                                $resultMarkbook->execute($dataMarkbook);
                            } catch (PDOException $e) { }

                            if ($resultMarkbook->rowCount() > 0) {
                                $markbook .= '<h2>'.__('Markbook').'</h2>';
                                $markbook .= '<ul>';
                                while ($rowMarkbook = $resultMarkbook->fetch()) {
                                    $markbook .= '<li>'.$rowMarkbook['class'].' - '.$rowMarkbook['name'].'</li>';
                                }
                                $markbook .= '</ul>';
                            }
                        }

                        //Get main form tutor email for reply-to
                        $replyTo = '';
                        $replyToName = '';
                        try {
                            $dataDetail = array('pupilsightRollGroupID' => $row['pupilsightRollGroupID']);
                            $sqlDetail = 'SELECT surname, preferredName, email FROM pupilsightRollGroup LEFT JOIN pupilsightPerson ON (pupilsightRollGroup.pupilsightPersonIDTutor=pupilsightPerson.pupilsightPersonID) WHERE pupilsightRollGroupID=:pupilsightRollGroupID';
                            $resultDetail = $connection2->prepare($sqlDetail);
                            $resultDetail->execute($dataDetail);
                        } catch (PDOException $e) {
                        }
                        if ($resultDetail->rowCount() == 1) {
                            $rowDetail = $resultDetail->fetch();
                            $replyTo = $rowDetail['email'];
                            $replyToName = $rowDetail['surname'].', '.$rowDetail['preferredName'];
                        }

                        //Get CP1 parent(s) email (might be multiples if in multiple families
                        try {
                            $dataFamily = array('pupilsightPersonID' => $row['pupilsightPersonID']);
                            $sqlFamily = 'SELECT * FROM pupilsightFamily JOIN pupilsightFamilyChild ON (pupilsightFamily.pupilsightFamilyID=pupilsightFamilyChild.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID';
                            $resultFamily = $connection2->prepare($sqlFamily);
                            $resultFamily->execute($dataFamily);
                        } catch (PDOException $e) {
                        }

                        while ($rowFamily = $resultFamily->fetch()) { //Run through each CP! family member
                            try {
                                $dataMember = array('pupilsightFamilyID' => $rowFamily['pupilsightFamilyID']);
                                $sqlMember = 'SELECT * FROM pupilsightFamilyAdult JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND contactPriority=1 ORDER BY contactPriority, surname, preferredName';
                                $resultMember = $connection2->prepare($sqlMember);
                                $resultMember->execute($dataMember);
                            } catch (PDOException $e) {
                            }

                            while ($rowMember = $resultMember->fetch()) {
                                //Check for send this week, and only proceed if no prior send
                                $keyReadFail = false;
                                try {
                                    $dataKeyRead = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonIDStudent' => $row['pupilsightPersonID'], 'pupilsightPersonIDParent' => $rowMember['pupilsightPersonID'], 'weekOfYear' => date('W'));
                                    $sqlKeyRead = 'SELECT * FROM pupilsightPlannerParentWeeklyEmailSummary WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonIDStudent=:pupilsightPersonIDStudent AND pupilsightPersonIDParent=:pupilsightPersonIDParent AND weekOfYear=:weekOfYear';
                                    $resultKeyRead = $connection2->prepare($sqlKeyRead);
                                    $resultKeyRead->execute($dataKeyRead);
                                } catch (PDOException $e) {
                                    $keyReadFail = true;
                                }

                                if ($keyReadFail == true) {
                                    ++$sendFailCount;
                                    error_log(sprintf(__('Planner Weekly Summary Email: an error (%1$s) occured sending an email to %2$s.'), '1', $rowMember['preferredName'].' '.$rowMember['surname']));
                                } else {
                                    if ($resultKeyRead->rowCount() != 0) {
                                        ++$sendFailCount;
                                        error_log(sprintf(__('Planner Weekly Summary Email: an error (%1$s) occured sending an email to %2$s.'), '2', $rowMember['preferredName'].' '.$rowMember['surname']));
                                    } else {
                                        //Make and store unique code for confirmation. add it to email text.
                                        $key = '';

                                        //Let's go! Create key, send the invite
                                        $continue = false;
                                        $count = 0;
                                        while ($continue == false and $count < 100) {
                                            $key = randomPassword(40);
                                            try {
                                                $dataUnique = array('key' => $key);
                                                $sqlUnique = 'SELECT * FROM pupilsightPlannerParentWeeklyEmailSummary WHERE pupilsightPlannerParentWeeklyEmailSummary.key=:key';
                                                $resultUnique = $connection2->prepare($sqlUnique);
                                                $resultUnique->execute($dataUnique);
                                            } catch (PDOException $e) {
                                            }

                                            if ($resultUnique->rowCount() == 0) {
                                                $continue = true;
                                            }
                                            ++$count;
                                        }

                                        if ($continue == false) {
                                            ++$sendFailCount;
                                            error_log(sprintf(__('Planner Weekly Summary Email: an error (%1$s) occured sending an email to %2$s.'), '3', $rowMember['preferredName'].' '.$rowMember['surname']));
                                        } else {
                                            //Write key to database
                                            $keyWriteFail = false;
                                            try {
                                                $dataKeyWrite = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonIDStudent' => $row['pupilsightPersonID'], 'pupilsightPersonIDParent' => $rowMember['pupilsightPersonID'], 'key' => $key, 'weekOfYear' => date('W'));
                                                $sqlKeyWrite = "INSERT INTO pupilsightPlannerParentWeeklyEmailSummary SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightPlannerParentWeeklyEmailSummary.key=:key, pupilsightPersonIDStudent=:pupilsightPersonIDStudent, pupilsightPersonIDParent=:pupilsightPersonIDParent, weekOfYear=:weekOfYear, confirmed='N'";
                                                $resultKeyWrite = $connection2->prepare($sqlKeyWrite);
                                                $resultKeyWrite->execute($dataKeyWrite);
                                            } catch (PDOException $e) {
                                                $keyWriteFail = true;
                                            }

                                            if ($keyWriteFail == true) {
                                                ++$sendFailCount;
                                                error_log(sprintf(__('Planner Weekly Summary Email: an error (%1$s) occured sending an email to %2$s.'), '4', $rowMember['preferredName'].' '.$rowMember['surname']));
                                            } else {
                                                //Prep email
                                                $body = sprintf(__('Dear %1$s'), $rowMember['preferredName'].' '.$rowMember['surname']).',<br/><br/>';
                                                if ($parentWeeklyEmailSummaryIncludeBehaviour == 'Y') {
                                                    $body .= sprintf(__('Please find below a summary of homework and behaviour for %1$s.'), $row['preferredName'].' '.$row['surname']).'<br/><br/>';
                                                }
                                                else {
                                                    $body .= sprintf(__('Please find below a summary of homework for %1$s.'), $row['preferredName'].' '.$row['surname']).'<br/><br/>';
                                                }
                                                $body .= $homework;
                                                $body .= $behaviour;
                                                $body .= $markbook;
                                                $body .= sprintf(__('Please %1$sclick here%2$s to confirm that you have received and read this summary email.'), "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_parentWeeklyEmailSummaryConfirm.php&key=$key&pupilsightPersonIDStudent=".$row['pupilsightPersonID'].'&pupilsightPersonIDParent='.$rowMember['pupilsightPersonID'].'&pupilsightSchoolYearID='.$_SESSION[$guid]['pupilsightSchoolYearID']."'>", '</a>');
                                                $body .= "<p class='emphasis'>".sprintf(__('Email sent via %1$s at %2$s.'), $_SESSION[$guid]['systemName'], $_SESSION[$guid]['organisationName']).'</p>';
                                                $bodyPlain = emailBodyConvert($body);

                                                if ($replyTo != '') {
                                                    $mail->AddReplyTo($replyTo, $replyToName);
                                                }
                                                $mail->AddAddress($rowMember['email'], $rowMember['surname'].', '.$rowMember['preferredName']);
                                                $mail->SetFrom($_SESSION[$guid]['organisationEmail'], $_SESSION[$guid]['organisationName']);
                                                $mail->CharSet = 'UTF-8';
                                                $mail->Encoding = 'base64';
                                                $mail->IsHTML(true);
                                                $mail->Subject = sprintf(__('Weekly Planner Summary for %1$s via %2$s at %3$s'), $row['surname'].', '.$row['preferredName'].' ('.$row['name'].')', $_SESSION[$guid]['systemName'], $_SESSION[$guid]['organisationName']);
                                                $mail->Body = $body;
                                                $mail->AltBody = $bodyPlain;

                                                //Send email
                                                if ($mail->Send()) {
                                                    ++$sendSucceedCount;
                                                } else {
                                                    error_log(sprintf(__('Planner Weekly Summary Email: an error (%1$s) occured sending an email to %2$s.'), '5', $rowMember['preferredName'].' '.$rowMember['surname']));
                                                    ++$sendFailCount;
                                                }

                                                //Clear addresses
                                                $mail->ClearAllRecipients( ); // clear all
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            //Close SMTP connection
            $mail->smtpClose();

            //Unlock module table
            try {
                $sql = 'UNLOCK TABLES';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
            }

            $body = __('Week').': '.date('W').'<br/>';
            $body .= __('Student Count').': '.$studentCount.'<br/>';
            $body .= __('Send Succeed Count').': '.$sendSucceedCount.'<br/>';
            $body .= __('Send Fail Count').': '.$sendFailCount.'<br/><br/>';

            // Raise a new notification event
            $event = new NotificationEvent('Planner', 'Parent Weekly Email Summary');

            $event->setNotificationText(__('A Planner CLI script has run.').'<br/>'.$body);
            $event->setActionLink('/index.php?q=/modules/Planner/report_parentWeeklyEmailSummaryConfirmation.php');

            //Notify admin
            $event->addRecipient($_SESSION[$guid]['organisationAdministrator']);

            // Send all notifications
            $sendReport = $event->sendNotifications($pdo, $pupilsight->session);

            // Output the result to terminal
            echo sprintf('Sent %1$s notifications: %2$s inserts, %3$s updates, %4$s emails sent, %5$s emails failed.', $sendReport['count'], $sendReport['inserts'], $sendReport['updates'], $sendReport['emailSent'], $sendReport['emailFailed'])."\n";
        }
    }
}
?>

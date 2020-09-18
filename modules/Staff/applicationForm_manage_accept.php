<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;
use Pupilsight\Contracts\Comms\Mailer;
use Pupilsight\Data\UsernameGenerator;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/applicationForm_manage_accept.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Applications'), 'applicationForm_manage.php')
        ->add(__('Accept Application'));

    //Check if school year specified
    $pupilsightStaffApplicationFormID = $_GET['pupilsightStaffApplicationFormID'];
    $search = $_GET['search'];
    if ($pupilsightStaffApplicationFormID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightStaffApplicationFormID' => $pupilsightStaffApplicationFormID);
            $sql = "SELECT pupilsightStaffApplicationForm.*, pupilsightStaffJobOpening.jobTitle, pupilsightStaffJobOpening.type FROM pupilsightStaffApplicationForm JOIN pupilsightStaffJobOpening ON (pupilsightStaffApplicationForm.pupilsightStaffJobOpeningID=pupilsightStaffJobOpening.pupilsightStaffJobOpeningID) LEFT JOIN pupilsightPerson ON (pupilsightStaffApplicationForm.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightStaffApplicationFormID=:pupilsightStaffApplicationFormID AND pupilsightStaffApplicationForm.status='Pending'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The selected application does not exist or has already been processed.');
            echo '</div>';
        } else {
            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            //Let's go!
            $values = $result->fetch();
            $step = '';
            if (isset($_GET['step'])) {
                $step = $_GET['step'];
            }
            if ($step != 1 and $step != 2) {
                $step = 1;
            }

            //Step 1
            if ($step == 1) {
                echo '<h3>';
                echo __('Step')." $step";
                echo '</h3>';

                echo "<div class='linkTop'>";
                if ($search != '') {
                    echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Staff/applicationForm_manage.php&search=$search'>".__('Back to Search Results').'</a>';
                }
                echo '</div>'; 

                $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/applicationForm_manage_accept.php&step=2&pupilsightStaffApplicationFormID='.$pupilsightStaffApplicationFormID.'&search='.$search);
                
                $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                $form->addHiddenValue('pupilsightStaffApplicationFormID', $pupilsightStaffApplicationFormID);

                $col = $form->addRow()->addColumn()->addClass('stacked');
                
                $applicantName = Format::name('', $values['preferredName'], $values['surname'], 'Staff', false, true);
                $col->addContent(sprintf(__('Are you sure you want to accept the application for %1$s?'), $applicantName))->wrap('<b>', '</b>');

                $informApplicant = (getSettingByScope($connection2, 'Staff', 'staffApplicationFormNotificationDefault') == 'Y');
                $col->addCheckbox('informApplicant')
                    ->description(__('Automatically inform <u>applicant</u> of their Pupilsight login details by email?'))
                    ->inline(true)
                    ->checked($informApplicant)
                    ->setClass('');

                $col->addContent(__('The system will perform the following actions:'))->wrap('<i><u>', '</u></i>');
                $list = $col->addContent();

                if (empty($values['pupilsightPersonID'])) {
                    $list->append('<li>'.__('Create a Pupilsight user account for the applicant.').'</li>')
                         ->append('<li>'.__('Register the user as a member of staff.').'</li>')
                         ->append('<li>'.__('Set the status of the application to "Accepted".').'</li>');
                } else {
                    $list->append('<li>'.__('Register the user as a member of staff, if not already done.').'</li>')
                         ->append('<li>'.__('Set the status of the application to "Accepted".').'</li>');
                }

                $list->wrap('<ol>', '</ol>');

                $col->addContent(__('But you may wish to manually do the following:'))->wrap('<i><u>', '</u></i>');
                $col->addContent()
                    ->append('<li>'.__('Adjust the user\'s roles within the system.').'</li>')
                    ->append('<li>'.__('Create a timetable for the applicant.').'</li>')
                    ->wrap('<ol>', '</ol>');

                $form->addRow()->addSubmit(__('Accept'));

                echo $form->getOutput();
                
            } elseif ($step == 2) {
                echo '<h3>';
                echo __('Step')." $step";
                echo '</h3>';

                echo "<div class='linkTop'>";
                if ($search != '') {
                    echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Staff/applicationForm_manage.php&search=$search'>".__('Back to Search Results').'</a>';
                }
                echo '</div>';

                if ($values['pupilsightPersonID'] == '') { //USER IS NEW TO THE SYSTEM
                    $informApplicant = 'N';
                    if (isset($_POST['informApplicant'])) {
                        if ($_POST['informApplicant'] == 'on') {
                            $informApplicant = 'Y';
                            $informApplicantArray = array();
                        }
                    }

                    //DETERMINE ROLE
                    $pupilsightRoleID = '006'; //Support staff by default
                    if ($values['type'] == 'Teaching') {
                        $pupilsightRoleID = '002';
                    } elseif ($values['type'] != 'Support') { //Work out role based on type, which appears to be drawn from role anyway
                        try {
                            $dataRole = array('name' => $values['type']);
                            $sqlRole = 'SELECT pupilsightRoleID FROM pupilsightRole WHERE name=:name';
                            $resultRole = $connection2->prepare($sqlRole);
                            $resultRole->execute($dataRole);
                        } catch (PDOException $e) {
                        }
                        if ($resultRole->rowCount() == 1) {
                            $rowRole = $resultRole->fetch();
                            $pupilsightRoleID = $rowRole['pupilsightRoleID'];
                        }
                    }

                    //CREATE APPLICANT
                    $failapplicant = true;
                    $lock = true;
                    try {
                        $sql = 'LOCK TABLES pupilsightPerson WRITE, pupilsightStaffApplicationForm WRITE, pupilsightSetting WRITE, pupilsightStaff WRITE, pupilsightStaffJobOpening WRITE, pupilsightUsernameFormat WRITE, pupilsightRole WRITE';
                        $result = $connection2->query($sql);
                    } catch (PDOException $e) {
                        $lock = false;
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    if ($lock == true) {
                        $gotAI = true;
                        try {
                            $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightPerson'";
                            $resultAI = $connection2->query($sqlAI);
                        } catch (PDOException $e) {
                            $gotAI = false;
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        if ($gotAI == true) {
                            $rowAI = $resultAI->fetch();
                            $pupilsightPersonID = str_pad($rowAI['Auto_increment'], 10, '0', STR_PAD_LEFT);

                            // Generate a unique username for the staff member
                            $generator = new UsernameGenerator($pdo);
                            $generator->addToken('preferredName', $values['preferredName']);
                            $generator->addToken('firstName', $values['firstName']);
                            $generator->addToken('surname', $values['surname']);

                            $username = $generator->generateByRole($pupilsightRoleID);

                            $password = randomPassword(8);
                            $salt = getSalt();
                            $passwordStrong = hash('sha256', $salt.$password);

                            $continueLoop = !(!empty($username) && $username != 'usernamefailed' && !empty($password));

                            //Set default email address for applicant
                            $email = $values['email'];
                            $emailAlternate = '';
                            $applicantDefaultEmail = getSettingByScope($connection2, 'Staff', 'staffApplicationFormDefaultEmail');
                            if ($applicantDefaultEmail != '') {
                                $emailAlternate = $email;
                                $email = str_replace('[username]', $username, $applicantDefaultEmail);
                            }

                            //Set default website address for applicant
                            $website = '';
                            $applicantDefaultWebsite = getSettingByScope($connection2, 'Staff', 'staffApplicationFormDefaultWebsite');
                            if ($applicantDefaultWebsite != '') {
                                $website = str_replace('[username]', $username, $applicantDefaultWebsite);
                            }

                            //Email website and email address to admin for creation
                            if ($applicantDefaultEmail != '' or $applicantDefaultWebsite != '') {
                                echo '<h4>';
                                echo __('New Staff Member Email & Website');
                                echo '</h4>';
                                $to = $_SESSION[$guid]['organisationAdministratorEmail'];
                                $subject = sprintf(__('Create applicant Email/Websites for %1$s at %2$s'), $_SESSION[$guid]['systemName'], $_SESSION[$guid]['organisationNameShort']);
                                $body = sprintf(__('Please create the following for new staff member %1$s.'), Format::name('', $values['preferredName'], $values['surname'], 'Student'))."<br/><br/>";
                                if ($applicantDefaultEmail != '') {
                                    $body .= __('Email').': '.$email."<br/>";
                                }
                                if ($applicantDefaultWebsite != '') {
                                    $body .= __('Website').': '.$website."<br/>";
                                }
                                if ($values['dateStart'] != '') {
                                    $body .= __('Start Date').': '.dateConvertBack($guid, $values['dateStart'])."<br/>";
                                }
                                $body .= __('Job Type').': '.dateConvertBack($guid, $values['type'])."<br/>";
                                $body .= __('Job Title').': '.dateConvertBack($guid, $values['jobTitle'])."<br/>";
                                $bodyPlain = emailBodyConvert($body);

                                $mail = $container->get(Mailer::class);
                                $mail->SetFrom($_SESSION[$guid]['organisationAdministratorEmail'], $_SESSION[$guid]['organisationAdministratorName']);
                                $mail->AddAddress($to);
                                $mail->CharSet = 'UTF-8';
                                $mail->Encoding = 'base64';
                                $mail->IsHTML(true);
                                $mail->Subject = $subject;
                                $mail->Body = $body;
                                $mail->AltBody = $bodyPlain;

                                if ($mail->Send()) {
                                    echo "<div class='alert alert-sucess'>";
                                    echo sprintf(__('A request to create a applicant email address and/or website address was successfully sent to %1$s.'), $_SESSION[$guid]['organisationAdministratorName']);
                                    echo '</div>';
                                } else {
                                    echo "<div class='alert alert-danger'>";
                                    echo sprintf(__('A request to create a applicant email address and/or website address failed. Please contact %1$s to request these manually.'), $_SESSION[$guid]['organisationAdministratorName']);
                                    echo '</div>';
                                }
                            }

                            if ($continueLoop == false) {
                                $insertOK = true;
                                try {
                                    $data = array('username' => $username, 'passwordStrong' => $passwordStrong, 'passwordStrongSalt' => $salt, 'surname' => $values['surname'], 'firstName' => $values['firstName'], 'preferredName' => $values['preferredName'], 'officialName' => $values['officialName'], 'nameInCharacters' => $values['nameInCharacters'], 'gender' => $values['gender'], 'dob' => $values['dob'], 'languageFirst' => $values['languageFirst'], 'languageSecond' => $values['languageSecond'], 'languageThird' => $values['languageThird'], 'countryOfBirth' => $values['countryOfBirth'], 'citizenship1' => $values['citizenship1'], 'citizenship1Passport' => $values['citizenship1Passport'], 'nationalIDCardNumber' => $values['nationalIDCardNumber'], 'residencyStatus' => $values['residencyStatus'], 'visaExpiryDate' => $values['visaExpiryDate'], 'email' => $email, 'emailAlternate' => $emailAlternate, 'website' => $website, 'phone1Type' => $values['phone1Type'], 'phone1CountryCode' => $values['phone1CountryCode'], 'phone1' => $values['phone1'], 'dateStart' => $values['dateStart'], 'fields' => $values['fields']);
                                    $sql = "INSERT INTO pupilsightPerson SET username=:username, password='', passwordStrong=:passwordStrong, passwordStrongSalt=:passwordStrongSalt, pupilsightRoleIDPrimary='$pupilsightRoleID', pupilsightRoleIDAll='$pupilsightRoleID', status='Full', surname=:surname, firstName=:firstName, preferredName=:preferredName, officialName=:officialName, nameInCharacters=:nameInCharacters, gender=:gender, dob=:dob, languageFirst=:languageFirst, languageSecond=:languageSecond, languageThird=:languageThird, countryOfBirth=:countryOfBirth, citizenship1=:citizenship1, citizenship1Passport=:citizenship1Passport, nationalIDCardNumber=:nationalIDCardNumber, residencyStatus=:residencyStatus, visaExpiryDate=:visaExpiryDate, email=:email, emailAlternate=:emailAlternate, website=:website, phone1Type=:phone1Type, phone1CountryCode=:phone1CountryCode, phone1=:phone1, dateStart=:dateStart, fields=:fields";
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    $insertOK = false;
                                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                }
                                if ($insertOK == true) {
                                    $failapplicant = false;

                                    //Populate informApplicant array
                                    if ($informApplicant == 'Y') {
                                        $informApplicantArray[0]['email'] = $values['email'];
                                        $informApplicantArray[0]['surname'] = $values['surname'];
                                        $informApplicantArray[0]['preferredName'] = $values['preferredName'];
                                        $informApplicantArray[0]['username'] = $username;
                                        $informApplicantArray[0]['password'] = $password;
                                    }
                                }
                            }
                        }
                    }
                    try {
                        $sql = 'UNLOCK TABLES';
                        $result = $connection2->query($sql);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    if ($failapplicant == true) {
                        echo "<div class='alert alert-danger'>";
                        echo __('Applicant could not be created!');
                        echo '</div>';
                    } else {
                        echo '<h4>';
                        echo __('Applicant Details');
                        echo '</h4>';
                        echo '<ul>';
                        echo "<li><b>pupilsightPersonID</b>: $pupilsightPersonID</li>";
                        echo '<li><b>'.__('Name').'</b>: '.Format::name('', $values['preferredName'], $values['surname'], 'Student').'</li>';
                        echo '<li><b>'.__('Email').'</b>: '.$email.'</li>';
                        echo '<li><b>'.__('Email Alternate').'</b>: '.$emailAlternate.'</li>';
                        echo '<li><b>'.__('Username')."</b>: $username</li>";
                        echo '<li><b>'.__('Password')."</b>: $password</li>";
                        echo '</ul>';

                        //Enrol applicant
                        $enrolmentOK = true;
                        try {
                            $data = array('pupilsightPersonID' => $pupilsightPersonID, 'type' => $values['type'], 'jobTitle' => $values['jobTitle']);
                            $sql = 'INSERT INTO pupilsightStaff SET pupilsightPersonID=:pupilsightPersonID, type=:type, jobTitle=:jobTitle';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $enrolmentOK = false;
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        //Report back
                        if ($enrolmentOK == false) {
                            echo "<div class='alert alert-warning'>";
                            echo __('Applicant could not be added to staff listing, so this will have to be done manually at a later date.');
                            echo '</div>';
                        } else {
                            echo '<h4>';
                            echo 'Applicant Enrolment';
                            echo '</h4>';
                            echo '<ul>';
                            echo '<li>'.__('The applicant has successfully been added to staff listing.').'</li>';
                            echo '</ul>';
                        }

                        //SEND APPLICANT EMAIL
                        if ($informApplicant == 'Y') {
                            echo '<h4>';
                            echo __('New Staff Member Welcome Email');
                            echo '</h4>';
                            $notificationApplicantMessage = getSettingByScope($connection2, 'Staff', 'staffApplicationFormNotificationMessage');
                            foreach ($informApplicantArray as $informApplicantEntry) {
                                if ($informApplicantEntry['email'] != '' and $informApplicantEntry['surname'] != '' and $informApplicantEntry['preferredName'] != '' and $informApplicantEntry['username'] != '' and $informApplicantEntry['password']) {
                                    $to = $informApplicantEntry['email'];
                                    $subject = sprintf(__('Welcome to %1$s at %2$s'), $_SESSION[$guid]['systemName'], $_SESSION[$guid]['organisationNameShort']);
                                    if ($notificationApplicantMessage != '') {
                                        $body = sprintf(__('Dear %1$s,<br/><br/>Welcome to %2$s, %3$s\'s system for managing school information. You can access the system by going to %4$s and logging in with your new username (%5$s) and password (%6$s).<br/><br/>In order to maintain the security of your data, we highly recommend you change your password to something easy to remember but hard to guess. This can be done by using the Preferences page after logging in (top-right of the screen).<br/><br/>'), Format::name('', $informApplicantEntry['preferredName'], $informApplicantEntry['surname'], 'Student'), $_SESSION[$guid]['systemName'], $_SESSION[$guid]['organisationNameShort'], $_SESSION[$guid]['absoluteURL'], $informApplicantEntry['username'], $informApplicantEntry['password']).$notificationApplicantMessage.sprintf(__('Please feel free to reply to this email should you have any questions.<br/><br/>%1$s,<br/><br/>%2$s Administrator'), $_SESSION[$guid]['organisationAdministratorName'], $_SESSION[$guid]['systemName']);
                                    } else {
                                        $body = 'Dear '.Format::name('', $informApplicantEntry['preferredName'], $informApplicantEntry['surname'], 'Student').",<br/><br/>Welcome to ".$_SESSION[$guid]['systemName'].', '.$_SESSION[$guid]['organisationNameShort']."'s system for managing school information. You can access the system by going to ".$_SESSION[$guid]['absoluteURL'].' and logging in with your new username ('.$informApplicantEntry['username'].') and password ('.$informApplicantEntry['password'].").<br/><br/>In order to maintain the security of your data, we highly recommend you change your password to something easy to remember but hard to guess. This can be done by using the Preferences page after logging in (top-right of the screen).<br/><br/>Please feel free to reply to this email should you have any questions.<br/><br/>".$_SESSION[$guid]['organisationAdministratorName'].",<br/><br/>".$_SESSION[$guid]['systemName'].' Administrator';
                                    }
                                    $bodyPlain = emailBodyConvert($body);

                                    $mail = $container->get(Mailer::class);
                                    $mail->SetFrom($_SESSION[$guid]['organisationAdministratorEmail'], $_SESSION[$guid]['organisationAdministratorName']);
                                    $mail->AddAddress($to);
                                    $mail->CharSet = 'UTF-8';
                                    $mail->Encoding = 'base64';
                                    $mail->IsHTML(true);
                                    $mail->Subject = $subject;
                                    $mail->Body = $body;
                                    $mail->AltBody = $bodyPlain;

                                    if ($mail->Send()) {
                                        echo "<div class='alert alert-sucess'>";
                                        echo __('A welcome email was successfully sent to').' '.Format::name('', $informApplicantEntry['preferredName'], $informApplicantEntry['surname'], 'Student').'.';
                                        echo '</div>';
                                    } else {
                                        echo "<div class='alert alert-danger'>";
                                        echo __('A welcome email could not be sent to').' '.Format::name('', $informApplicantEntry['preferredName'], $informApplicantEntry['surname'], 'Student').'.';
                                        echo '</div>';
                                    }
                                }
                            }
                        }
                    }
                } else { //IF NOT IN THE SYSTEM AS STAFF, THEN ADD THEM
                    echo '<h4>';
                    echo 'Staff Listing';
                    echo '</h4>';

                    $alreadyEnroled = false;
                    $enrolmentCheckFail = false;
                    try {
                        $data = array('pupilsightPersonID' => $values['pupilsightPersonID']);
                        $sql = 'SELECT * FROM pupilsightStaff WHERE pupilsightPersonID=:pupilsightPersonID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $enrolmentCheckFail = true;
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    if ($result->rowCount() == 1) {
                        $alreadyEnroled = true;
                    }
                    if ($enrolmentCheckFail) { //Enrolment check did not work, so report error
                        echo "<div class='alert alert-warning'>";
                        echo __('Applicant could not be added to staff listing, so this will have to be done manually at a later date.');
                        echo '</div>';
                    } elseif ($alreadyEnroled) { //User is already enroled, so display message
                        echo "<div class='alert alert-warning'>";
                        echo __('Applicant already exists in staff listing.');
                        echo '</div>';
                    } else { //User is not yet enroled, so try and enrol them.
                        $enrolmentOK = true;

                        try {
                            $data = array('pupilsightPersonID' => $values['pupilsightPersonID'], 'type' => $values['type'], 'jobTitle' => $values['jobTitle']);
                            $sql = 'INSERT INTO pupilsightStaff SET pupilsightPersonID=:pupilsightPersonID, type=:type, jobTitle=:jobTitle';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $enrolmentOK = false;
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        //Report back
                        if ($enrolmentOK == false) {
                            echo "<div class='alert alert-warning'>";
                            echo __('Applicant could not be added to staff listing, so this will have to be done manually at a later date.');
                            echo '</div>';
                        } else {
                            echo '<ul>';
                            echo '<li>'.__('The applicant has successfully been added to staff listing.').'</li>';
                            echo '</ul>';
                        }
                    }
                }

                //SET STATUS TO ACCEPTED
                $failStatus = false;
                try {
                    $data = array('pupilsightStaffApplicationFormID' => $pupilsightStaffApplicationFormID);
                    $sql = "UPDATE pupilsightStaffApplicationForm SET status='Accepted' WHERE pupilsightStaffApplicationFormID=:pupilsightStaffApplicationFormID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $failStatus = true;
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($failStatus == true) {
                    echo "<div class='alert alert-danger'>";
                    echo __('Applicant status could not be updated: applicant is in the system, but acceptance has failed.');
                    echo '</div>';
                } else {
                    echo '<h4>';
                    echo __('Application Status');
                    echo '</h4>';
                    echo '<ul>';
                    echo '<li><b>'.__('Status').'</b>: '.__('Accepted').'</li>';
                    echo '</ul>';

                    echo "<div class='alert alert-sucess' style='margin-bottom: 20px'>";
                    echo sprintf(__('Applicant has been successfully accepted into %1$s.'), $_SESSION[$guid]['organisationName']).' <i><u>'.__('You may wish to now do the following:').'</u></i><br/>';
                    echo '<ol>';
                    echo '<li>'.__('Adjust the user\'s roles within the system.').'</li>';
                    echo '<li>'.__('Create a timetable for the applicant.').'</li>';
                    echo '</ol>';
                    echo '</div>';
                }
            }
        }
    }
}

<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Contracts\Comms\Mailer;

include './pupilsight.php';

//Create password
$password = randomPassword(8);

// Sanitize the $_GET and $_POST arrays
$validator = new \Pupilsight\Data\Validator();
$_GET = $validator->sanitize($_GET);
$_POST = $validator->sanitize($_POST);

//Check email address is not blank
$input = isset($_GET['input'])? $_GET['input'] : (isset($_POST['email'])? $_POST['email'] : '');
$step = $_GET['step'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=passwordReset.php';
$URLSuccess1 = $_SESSION[$guid]['absoluteURL'].'/index.php';

if ($input == '' or ($step != 1 and $step != 2)) {
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
}
//Otherwise proceed
else {
    try {
        $data = array('email' => $input, 'username' => $input);
        $sql = "SELECT pupilsightPersonID, email, username, canLogin, pupilsightRoleIDPrimary FROM pupilsightPerson WHERE (email=:email OR username=:username) AND pupilsightPerson.status='Full' AND NOT email=''";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $URL = $URL.'&return=error2';
        header("Location: {$URL}");
        exit();
    }

    if ($result->rowCount() != 1) {
        $URL = $URL.'&return=error4';
        header("Location: {$URL}");
    } else {
        $row = $result->fetch();

        // Insufficient privileges to login
        if ($row['canLogin'] != 'Y') {
            $URL .= '&return=fail2';
            header("Location: {$URL}");
            exit;
        }

        // Get primary role info
        $data = array('pupilsightRoleIDPrimary' => $row['pupilsightRoleIDPrimary']);
        $sql = "SELECT * FROM pupilsightRole WHERE pupilsightRoleID=:pupilsightRoleIDPrimary";
        $role = $pdo->selectOne($sql, $data);

        // Login not allowed for this role
        if (!empty($role['canLoginRole']) && $role['canLoginRole'] != 'Y') {
            $URL .= '&return=fail9';
            header("Location: {$URL}");
            exit;
        }

        $pupilsightPersonID = $row['pupilsightPersonID'];
        $email = $row['email'];
        $username = $row['username'];

        if ($step == 1) { //This is the request phase
            //Generate key
            $key = randomPassword(40);

            //Try to delete other recors for this user
            try {
                $data = array('pupilsightPersonID' => $pupilsightPersonID);
                $sql = "DELETE FROM pupilsightPersonReset WHERE pupilsightPersonID=:pupilsightPersonID";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) { }

            //Insert key record
            try {
                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'key' => $key);
                $sql = "INSERT INTO pupilsightPersonReset SET pupilsightPersonID=:pupilsightPersonID, `key`=:key";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL = $URL.'&return=error2';
                header("Location: {$URL}");
                exit();
            }
            $pupilsightPersonResetID = str_pad($connection2->lastInsertID(), 12, '0', STR_PAD_LEFT);

            //Send email
            $subject = $_SESSION[$guid]['organisationNameShort'].' '.__('Pupilsight Password Reset');
            $body = sprintf(__('A password reset request has been initiated for account %1$s, which is registered to this email address.%2$sIf you did not initiate this request, please ignore this email.%2$sIf you do wish to reset your password, please use the link below to access the reset form:%2$s%3$s%2$s%4$s'), $username, "\n\n", '', '');

            $mail = $container->get(Mailer::class);
            $mail->AddAddress($email);

            if (isset($_SESSION[$guid]['organisationEmail']) && $_SESSION[$guid]['organisationEmail'] != '') {
                $mail->SetFrom($_SESSION[$guid]['organisationEmail'], $_SESSION[$guid]['organisationName']);
            } else {
                $mail->SetFrom($_SESSION[$guid]['organisationAdministratorEmail'], $_SESSION[$guid]['organisationName']);
            }

            $mail->Subject = $subject;
            $mail->renderBody('mail/email.twig.html', [
                'title'  => __('Password Reset'),
                'body'   => nl2br(trim($body, "\n")),
                'button' => [
                    'url'  => "/index.php?q=/passwordReset.php&input=$input&step=2&pupilsightPersonResetID=$pupilsightPersonResetID&key=$key",
                    'text' => __('Click Here'),
                ],
            ]);

            if ($mail->Send()) {
                $URL = $URL.'&return=success0';
                header("Location: {$URL}");
            } else {
                $URL = $URL.'&return=error3';
                header("Location: {$URL}");
            }
        }
        else { //This is the confirmation/reset phase
            //Get URL parameters
        	$input = $_GET['input'];
        	$key = $_GET['key'];
        	$pupilsightPersonResetID = $_GET['pupilsightPersonResetID'];

        	//Verify authenticity of this request and check it is fresh (within 48 hours)
        	try {
                $data = array('key' => $key, 'pupilsightPersonResetID' => $pupilsightPersonResetID);
                $sql = "SELECT * FROM pupilsightPersonReset WHERE `key`=:key AND pupilsightPersonResetID=:pupilsightPersonResetID AND (timestamp > DATE_SUB(now(), INTERVAL 2 DAY))";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL = $URL.'&return=error2';
                header("Location: {$URL}");
                exit();
            }

        	if ($result->rowCount() != 1) {
                $URL = $URL.'&return=error2';
                header("Location: {$URL}");
        	} else {
                $row = $result->fetch();
                $pupilsightPersonID = $row['pupilsightPersonID'];
                $passwordNew = $_POST['passwordNew'];
                $passwordConfirm = $_POST['passwordConfirm'];

                //Check passwords are not blank
                if ($passwordNew == '' or $passwordConfirm == '') {
                    $URL .= '&return=error1';
                    header("Location: {$URL}");
                } else {
                    //Check that new password is not same as old password
                    if ($password == $passwordNew) {
                        $URL .= '&return=error7';
                        header("Location: {$URL}");
                    } else {
                        //Check strength of password
                        $passwordMatch = doesPasswordMatchPolicy($connection2, $passwordNew);

                        if ($passwordMatch == false) {
                            $URL .= '&return=error6';
                            header("Location: {$URL}");
                        } else {
                            //Check new passwords match
                            if ($passwordNew != $passwordConfirm) {
                                $URL .= '&return=error5';
                                header("Location: {$URL}");
                            } else {
                                //Update password
                                $salt = getSalt();
                                $passwordStrong = hash('sha256', $salt.$passwordNew);
                                try {
                                    $data = array('passwordStrong' => $passwordStrong, 'salt' => $salt, 'pupilsightPersonID' => $pupilsightPersonID);
                                    $sql = "UPDATE pupilsightPerson SET password='', passwordStrong=:passwordStrong, passwordStrongSalt=:salt, passwordForceReset='N', failCount=0 WHERE pupilsightPersonID=:pupilsightPersonID";
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    $URL .= '&return=error2';
                                    header("Location: {$URL}");
                                    exit();
                                }

                                //Remove requests for this person
                                try {
                                    $data = array('pupilsightPersonID' => $pupilsightPersonID);
                                    $sql = "DELETE FROM pupilsightPersonReset WHERE pupilsightPersonID=:pupilsightPersonID";
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) { }

                                //Return
                                $URL = $URLSuccess1.'?return=success1';
                                header("Location: {$URL}");
                            }
                        }
                    }
                }
            }
        }
    }
}

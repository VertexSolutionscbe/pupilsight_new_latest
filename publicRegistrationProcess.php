<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Comms\NotificationEvent;
use Pupilsight\Services\Format;

include './pupilsight.php';

//Module includes from User Admin (for custom fields)
include './modules/User Admin/moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/publicRegistration.php';

$proceed = false;

if (isset($_SESSION[$guid]['username']) == false) {
    $enablePublicRegistration = getSettingByScope($connection2, 'User Admin', 'enablePublicRegistration');
    if ($enablePublicRegistration == 'Y') {
        $proceed = true;
    }
}

if ($proceed == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Lock activities table
    try {
        $data = array();
        $sql = 'LOCK TABLES pupilsightPerson WRITE, pupilsightSetting READ, pupilsightNotification WRITE, pupilsightModule WRITE, pupilsightPersonField WRITE';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit();
    }

    // Sanitize the whole $_POST array
    $validator = new \Pupilsight\Data\Validator();
    $_POST = $validator->sanitize($_POST);

    //Proceed!
    $surname = trim($_POST['surname']);
    $firstName = trim($_POST['firstName']);
    $preferredName = trim($firstName);
    $officialName = $firstName.' '.$surname;
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    if ($dob == '') {
        $dob = null;
    } else {
        $dob = dateConvert($guid, $dob);
    }
    $email = trim($_POST['email']);
    $username = trim($_POST['usernameCheck']);
    $password = $_POST['passwordNew'];
    $salt = getSalt();
    $passwordStrong = hash('sha256', $salt.$password);
    $status = getSettingByScope($connection2, 'User Admin', 'publicRegistrationDefaultStatus');
    $pupilsightRoleIDPrimary = getSettingByScope($connection2, 'User Admin', 'publicRegistrationDefaultRole');
    $pupilsightRoleIDAll = $pupilsightRoleIDPrimary;

    if ($surname == '' or $firstName == '' or $preferredName == '' or $officialName == '' or $gender == '' or $dob == '' or $email == '' or $username == '' or $password == '' or $pupilsightRoleIDPrimary == '' or $pupilsightRoleIDPrimary == '' or ($status != 'Pending Approval' and $status != 'Full')) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        $customRequireFail = false;
        $resultFields = getCustomFields($connection2, $guid, null, null, null, null, null, null, true);
        $fields = array();
        if ($resultFields->rowCount() > 0) {
            while ($rowFields = $resultFields->fetch()) {
                if (isset($_POST['custom'.$rowFields['pupilsightPersonFieldID']])) {
                    if ($rowFields['type'] == 'date') {
                        $fields[$rowFields['pupilsightPersonFieldID']] = dateConvert($guid, $_POST['custom'.$rowFields['pupilsightPersonFieldID']]);
                    } else {
                        $fields[$rowFields['pupilsightPersonFieldID']] = $_POST['custom'.$rowFields['pupilsightPersonFieldID']];
                    }
                }
                if ($rowFields['required'] == 'Y') {
                    if (isset($_POST['custom'.$rowFields['pupilsightPersonFieldID']]) == false) {
                        $customRequireFail = true;
                    } elseif ($_POST['custom'.$rowFields['pupilsightPersonFieldID']] == '') {
                        $customRequireFail = true;
                    }
                }
            }
        }

        if ($customRequireFail) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
            exit();
        } else {
            $fields = serialize($fields);
        }

        //Check strength of password
        $passwordMatch = doesPasswordMatchPolicy($connection2, $password);

        if ($passwordMatch == false) {
            $URL .= '&return=error7';
            header("Location: {$URL}");
        } else {
            //Check uniqueness of username
            try {
                $data = array('username' => $username, 'email' => $email);
                $sql = 'SELECT * FROM pupilsightPerson WHERE username=:username OR email=:email';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            if ($result->rowCount() > 0) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check publicRegistrationMinimumAge
                $publicRegistrationMinimumAge = getSettingByScope($connection2, 'User Admin', 'publicRegistrationMinimumAge');

                $ageFail = false;
                if ($publicRegistrationMinimumAge == '') {
                    $ageFail = true;
                } elseif ($publicRegistrationMinimumAge > 0 and $publicRegistrationMinimumAge > (new DateTime('@'.Format::timestamp($dob)))->diff(new DateTime())->y) {
                    $ageFail = true;
                }

                if ($ageFail == true) {
                    $URL .= '&return=fail5';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('surname' => $surname, 'firstName' => $firstName, 'preferredName' => $preferredName, 'officialName' => $officialName, 'gender' => $gender, 'dob' => $dob, 'email' => $email, 'username' => $username, 'passwordStrong' => $passwordStrong, 'passwordStrongSalt' => $salt, 'status' => $status, 'pupilsightRoleIDPrimary' => $pupilsightRoleIDPrimary, 'pupilsightRoleIDAll' => $pupilsightRoleIDAll, 'fields' => $fields);
                        $sql = "INSERT INTO pupilsightPerson SET surname=:surname, firstName=:firstName, preferredName=:preferredName, officialName=:officialName, gender=:gender, dob=:dob, email=:email, username=:username, password='', passwordStrong=:passwordStrong, passwordStrongSalt=:passwordStrongSalt, status=:status, pupilsightRoleIDPrimary=:pupilsightRoleIDPrimary, pupilsightRoleIDAll=:pupilsightRoleIDAll, fields=:fields";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo $e->getMessage();
                        exit();
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $pupilsightPersonID = $connection2->lastInsertId();

                    try {
                        $sqlLock = 'UNLOCK TABLES';
                        $result = $connection2->query($sqlLock);
                    } catch (PDOException $e) {
                    }

                    if ($status == 'Pending Approval') {
                        // Raise a new notification event
                        $event = new NotificationEvent('User Admin', 'New Public Registration');

                        $event->addRecipient($_SESSION[$guid]['organisationAdmissions']);
                        $event->setNotificationText(sprintf(__('An new public registration, for %1$s, is pending approval.'), formatName('', $preferredName, $surname, 'Student')));
                        $event->setActionLink("/index.php?q=/modules/User Admin/user_manage_edit.php&pupilsightPersonID=$pupilsightPersonID&search=");

                        $event->sendNotifications($pdo, $pupilsight->session);

                        $URL .= '&return=success1';
                        header("Location: {$URL}");
                    } else {
                        $URL .= '&return=success0';
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}

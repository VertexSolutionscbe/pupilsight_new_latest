<?php

/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Comms\NotificationEvent;

// Pupilsight system-wide include
require_once './pupilsight.php';

setCurrentSchoolYear($guid, $connection2);

//The current/actual school year info, just in case we are working in a different year
$_SESSION[$guid]['pupilsightSchoolYearIDCurrent'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
$_SESSION[$guid]['pupilsightSchoolYearNameCurrent'] = $_SESSION[$guid]['pupilsightSchoolYearName'];
$_SESSION[$guid]['pupilsightSchoolYearSequenceNumberCurrent'] = $_SESSION[$guid]['pupilsightSchoolYearSequenceNumber'];

$_SESSION[$guid]['pageLoads'] = null;

$URL = './index.php';
$NEWURL='home.php?invalid=true';
             

// Sanitize the whole $_POST array
$validator = new \Pupilsight\Data\Validator();
$_POST = $validator->sanitize($_POST);

//Get and store POST variables from calling page
$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if(isset($_POST["rememberme"])) {
    
	setcookie ("username",$_POST["username"],time()+ (86400 * 30));
	setcookie ("password",$_POST["password"],time()+ (86400 * 30));
	echo "Cookies Set Successfuly";
}
else
{
    if(isset($_COOKIE["username"])) {
        setcookie ("username","");
	    setcookie ("password","");
	
    }
    

}



if (empty($username) or empty($password)) {
    $URL .= '?loginReturn=fail0b';
    header("Location: {$URL}");
    exit;
}
//VALIDATE LOGIN INFORMATION
else {
    try {
        $_SESSION["loginuser"] = $username;
        $_SESSION["loginpass"] = $password;
        $data = array('username' => $username);
        $sql = "SELECT pupilsightPerson.*, futureYearsLogin, pastYearsLogin FROM pupilsightPerson LEFT JOIN pupilsightRole ON (pupilsightPerson.pupilsightRoleIDPrimary=pupilsightRole.pupilsightRoleID) WHERE ((username=:username OR (LOCATE('@', :username)>0 AND email=:username) ) AND (status='Full'))";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    //Test to see if username exists and is unique
    if ($result->rowCount() != 1) {
        setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], null, null, 'Login - Failed', array('username' => $username, 'reason' => 'Username does not exist'), $_SERVER['REMOTE_ADDR']);
        $URL .= '?loginReturn=fail1';

        // echo "<script type='text/javascript'>alert('Wrong Username or Password');
        // window.location.href='./index.php';

        // </script>";
        header("location:{$NEWURL}");


        exit;
    } else {
        $row = $result->fetch();

        $_SESSION["lmsuser"] = strtolower($row["username"]);
        $_SESSION["lmspass"] = strtolower($row["username"]);

        // Insufficient privileges to login
        if ($row['canLogin'] != 'Y') {
            $URL .= '?loginReturn=fail2';
            header("Location: {$URL}");
            exit;
        }

        // Get primary role info
        $data = array('pupilsightRoleIDPrimary' => $row['pupilsightRoleIDPrimary']);
        $sql = "SELECT * FROM pupilsightRole WHERE pupilsightRoleID=:pupilsightRoleIDPrimary";
        $role = $pdo->selectOne($sql, $data);

        // Login not allowed for this role
        if (!empty($role['canLoginRole']) && $role['canLoginRole'] != 'Y') {
            $URL .= '?loginReturn=fail9';
            header("Location: {$URL}");
            exit;
        }

        // Set the username explicity, to handle logging in with email
        $username = $row['username'];

        //Check fail count, reject & alert if 3rd time

        if ($row['failCount'] > 2) {
            try {
                $dataSecure = array('lastFailIPAddress' => $_SERVER['REMOTE_ADDR'], 'lastFailTimestamp' => date('Y-m-d H:i:s'), 'failCount' => ($row['failCount'] + 1), 'username' => $username);
                $sqlSecure = 'UPDATE pupilsightPerson SET lastFailIPAddress=:lastFailIPAddress, lastFailTimestamp=:lastFailTimestamp, failCount=:failCount WHERE (username=:username)';
                $resultSecure = $connection2->prepare($sqlSecure);
                $resultSecure->execute($dataSecure);
            } catch (PDOException $e) {
            }

            if ($row['failCount'] == 3) {
                // Raise a new notification event
                $event = new NotificationEvent('User Admin', 'Login - Failed');

                $event->addRecipient($_SESSION[$guid]['organisationAdministrator']);
                $event->setNotificationText(sprintf(__('Someone failed to login to account "%1$s" 3 times in a row.'), $username));
                $event->setActionLink('/index.php?q=/modules/User Admin/user_manage.php&search=' . $username);

                $event->sendNotifications($pdo, $pupilsight->session);
            }

            setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], null, $row['pupilsightPersonID'], 'Login - Failed', array('username' => $username, 'reason' => 'Too many failed logins'), $_SERVER['REMOTE_ADDR']);
            $URL .= '?loginReturn=fail6';
            // header("Location: {$URL}");
            // exit;
            echo "<script type='text/javascript'>alert('Your Account is Locked, Please Contact to Admin');
            window.location.href='./index.php';

            </script>";
            exit;
            //header("Location: {$URL}");
            //exit;
        } else {
            $passwordTest = false;
            //If strong password exists
            $salt = $row['passwordStrongSalt'];
            $passwordStrong = $row['passwordStrong'];
            if ($passwordStrong != '' and $salt != '') {
                if (hash('sha256', $row['passwordStrongSalt'] . $password) == $row['passwordStrong']) {
                    $passwordTest = true;
                }
            }
            //If only weak password exists
            elseif ($row['password'] != '') {
                if ($row['password'] == md5($password)) {
                    $passwordTest = true;

                    //Migrate to strong password
                    $salt = getSalt();
                    $passwordStrong = hash('sha256', $salt . $password);

                    try {
                        $dataSecure = array('passwordStrong' => $passwordStrong, 'passwordStrongSalt' => $salt, 'username' => $username);
                        $sqlSecure = "UPDATE pupilsightPerson SET password='', passwordStrong=:passwordStrong, passwordStrongSalt=:passwordStrongSalt WHERE (username=:username)";
                        $resultSecure = $connection2->prepare($sqlSecure);
                        $resultSecure->execute($dataSecure);
                    } catch (PDOException $e) {
                        $passwordTest = false;
                    }
                }
            }

            //Test to see if password matches username
            if ($passwordTest != true) {
                //FAIL PASSWORD
                try {
                    $dataSecure = array('lastFailIPAddress' => $_SERVER['REMOTE_ADDR'], 'lastFailTimestamp' => date('Y-m-d H:i:s'), 'failCount' => ($row['failCount'] + 1), 'username' => $username);
                    $sqlSecure = 'UPDATE pupilsightPerson SET lastFailIPAddress=:lastFailIPAddress, lastFailTimestamp=:lastFailTimestamp, failCount=:failCount WHERE (username=:username)';
                    $resultSecure = $connection2->prepare($sqlSecure);
                    $resultSecure->execute($dataSecure);
                } catch (PDOException $e) {
                    $passwordTest = false;
                }

                setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], null, $row['pupilsightPersonID'], 'Login - Failed', array('username' => $username, 'reason' => 'Incorrect password'), $_SERVER['REMOTE_ADDR']);
                $URL .= '?loginReturn=fail1';

                // echo "<script type='text/javascript'>alert('Wrong Username or Password');
                // window.location.href='./index.php';

                // </script>";
            
                header("Location: {$NEWURL}");
                exit;
            } else {
                if ($row['pupilsightRoleIDPrimary'] == '' or count(getRoleList($row['pupilsightRoleIDAll'], $connection2)) == 0) {
                    //FAILED TO SET ROLES
                    setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], null, $row['pupilsightPersonID'], 'Login - Failed', array('username' => $username, 'reason' => 'Failed to set role(s)'), $_SERVER['REMOTE_ADDR']);
                    $URL .= '?loginReturn=fail2';
                    header("Location: {$URL}");
                    exit;
                } else {
                    //Allow for non-current school years to be specified
                    $datas = array('status' => 'Current');
                    $sqls = "SELECT * FROM pupilsightSchoolYear WHERE status=:status";
                    $cureentYear = $pdo->selectOne($sqls, $datas);
                    $currentYearId = $cureentYear['pupilsightSchoolYearID'];

                    if ($currentYearId != $_SESSION[$guid]['pupilsightSchoolYearID']) {
                        if ($row['futureYearsLogin'] != 'Y' and $row['pastYearsLogin'] != 'Y') { //NOT ALLOWED DUE TO CONTROLS ON ROLE, KICK OUT!
                            setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], null, $row['pupilsightPersonID'], 'Login - Failed', array('username' => $username, 'reason' => 'Not permitted to access non-current school year'), $_SERVER['REMOTE_ADDR']);
                            $URL .= '?loginReturn=fail9';
                            header("Location: {$URL}");
                            exit();
                        } else {
                            //Get details on requested school year
                            try {
                                $dataYear = array('pupilsightSchoolYearID' => $currentYearId);
                                $sqlYear = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
                                $resultYear = $connection2->prepare($sqlYear);
                                $resultYear->execute($dataYear);
                            } catch (PDOException $e) {
                            }

                            //Check number of rows returned.
                            //If it is not 1, show error
                            if (!($resultYear->rowCount() == 1)) {
                                die(__('Configuration Error: there is a problem accessing the current Academic Year from the database.'));
                            }
                            //Else get year details
                            else {
                                $rowYear = $resultYear->fetch();
                                if ($row['futureYearsLogin'] != 'Y' and $_SESSION[$guid]['pupilsightSchoolYearSequenceNumber'] < $rowYear['sequenceNumber']) { //POSSIBLY NOT ALLOWED DUE TO CONTROLS ON ROLE, CHECK YEAR
                                    setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], null, $row['pupilsightPersonID'], 'Login - Failed', array('username' => $username, 'reason' => 'Not permitted to access non-current school year'), $_SERVER['REMOTE_ADDR']);
                                    $URL .= '?loginReturn=fail9';
                                    header("Location: {$URL}");
                                    exit();
                                } elseif ($row['pastYearsLogin'] != 'Y' and $_SESSION[$guid]['pupilsightSchoolYearSequenceNumber'] > $rowYear['sequenceNumber']) { //POSSIBLY NOT ALLOWED DUE TO CONTROLS ON ROLE, CHECK YEAR
                                    setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], null, $row['pupilsightPersonID'], 'Login - Failed', array('username' => $username, 'reason' => 'Not permitted to access non-current school year'), $_SERVER['REMOTE_ADDR']);
                                    $URL .= '?loginReturn=fail9';
                                    header("Location: {$URL}");
                                    exit();
                                } else { //ALLOWED
                                    $_SESSION[$guid]['pupilsightSchoolYearID'] = $rowYear['pupilsightSchoolYearID'];
                                    $_SESSION[$guid]['pupilsightSchoolYearName'] = $rowYear['name'];
                                    $_SESSION[$guid]['pupilsightSchoolYearSequenceNumber'] = $rowYear['sequenceNumber'];
                                }
                            }
                        }
                    }

                    //USER EXISTS, SET SESSION VARIABLES
                    $pupilsight->session->createUserSession($username, $row);

                    // Set these from local values
                    $pupilsight->session->set('passwordStrong', $passwordStrong);
                    $pupilsight->session->set('passwordStrongSalt', $salt);
                    $pupilsight->session->set('googleAPIAccessToken', null);

                    //Allow for non-system default language to be specified from login form
                    if (@$_POST['pupilsighti18nID'] != $_SESSION[$guid]['i18n']['pupilsighti18nID']) {
                        try {
                            $dataLanguage = array('pupilsighti18nID' => $_POST['pupilsighti18nID']);
                            $sqlLanguage = 'SELECT * FROM pupilsighti18n WHERE pupilsighti18nID=:pupilsighti18nID';
                            $resultLanguage = $connection2->prepare($sqlLanguage);
                            $resultLanguage->execute($dataLanguage);
                        } catch (PDOException $e) {
                        }
                        if ($resultLanguage->rowCount() == 1) {
                            $rowLanguage = $resultLanguage->fetch();
                            setLanguageSession($guid, $rowLanguage, false);
                        }
                    } else {
                        //If no language specified, get user preference if it exists
                        if (!is_null($_SESSION[$guid]['pupilsighti18nIDPersonal'])) {
                            try {
                                $dataLanguage = array('pupilsighti18nID' => $_SESSION[$guid]['pupilsighti18nIDPersonal']);
                                $sqlLanguage = "SELECT * FROM pupilsighti18n WHERE active='Y' AND pupilsighti18nID=:pupilsighti18nID";
                                $resultLanguage = $connection2->prepare($sqlLanguage);
                                $resultLanguage->execute($dataLanguage);
                            } catch (PDOException $e) {
                            }
                            if ($resultLanguage->rowCount() == 1) {
                                $rowLanguage = $resultLanguage->fetch();
                                setLanguageSession($guid, $rowLanguage, false);
                            }
                        }
                    }

                    //Make best effort to set IP address and other details, but no need to error check etc.
                    try {
                        $data = array('lastIPAddress' => $_SERVER['REMOTE_ADDR'], 'lastTimestamp' => date('Y-m-d H:i:s'), 'failCount' => 0, 'username' => $username);
                        $sql = 'UPDATE pupilsightPerson SET lastIPAddress=:lastIPAddress, lastTimestamp=:lastTimestamp, failCount=:failCount WHERE username=:username';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                    }
                    $role = $_SESSION[$guid]['pupilsightRoleIDPrimary'];
                    if ($role == '033') {
                        $URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Campaign/check_status.php';
                    } else {
                        if (isset($_GET['q'])) {
                            if ($_GET['q'] == '/publicRegistration.php') {
                                $URL = './index.php';
                            } else {
                                $URL = './index.php?q=' . $_GET['q'];
                            }
                        } else {
                            $URL = './index.php';
                        }
                    }
                    setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], null, $row['pupilsightPersonID'], 'Login - Success', array('username' => $username), $_SERVER['REMOTE_ADDR']);
                    $_SESSION["loginstatus"] = '1';
                    header("Location: {$URL}");
                    exit;
                }
            }
        }
    }
}

<?php
/*
Pupilsight, Flexible & Open School System
*/

include './pupilsight.php';

//Check to see if academic year id variables are set, if not set them
if (isset($_SESSION[$guid]['pupilsightAcademicYearID']) == false or isset($_SESSION[$guid]['pupilsightSchoolYearName']) == false) {
    setCurrentSchoolYear($guid, $connection2);
}

//Check password address is not blank
$password = $_POST['password'];
$passwordNew = $_POST['passwordNew'];
$passwordConfirm = $_POST['passwordConfirm'];
$forceReset = $_SESSION[$guid]['passwordForceReset'];

if ($forceReset != 'Y') {
    $forceReset = 'N';
}

$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=preferences.php&forceReset=$forceReset";

//Check passwords are not blank
if ($password == '' or $passwordNew == '' or $passwordConfirm == '') {
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
                $URL .= '&return=error4';
                header("Location: {$URL}");
            } else {
                //Check current password
                if (hash('sha256', $_SESSION[$guid]['passwordStrongSalt'].$password) != $_SESSION[$guid]['passwordStrong']) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //If answer insert fails...
                    $salt = getSalt();
                    $passwordStrong = hash('sha256', $salt.$passwordNew);
                    try {
                        $data = array('passwordStrong' => $passwordStrong, 'salt' => $salt, 'username' => $_SESSION[$guid]['username']);
                        $sql = "UPDATE pupilsightPerson SET password='', passwordStrong=:passwordStrong, passwordStrongSalt=:salt WHERE (username=:username)";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Check for forceReset and take action
                    if ($forceReset == 'Y') {
                        //Update passwordForceReset field
                        try {
                            $data = array('username' => $_SESSION[$guid]['username']);
                            $sql = "UPDATE pupilsightPerson SET passwordForceReset='N' WHERE username=:username";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= '&return=errora';
                            header("Location: {$URL}");
                            exit();
                        }
                        $_SESSION[$guid]['passwordForceReset'] = 'N';
                        $_SESSION[$guid]['passwordStrongSalt'] = $salt;
                        $_SESSION[$guid]['passwordStrong'] = $passwordStrong;
                        $_SESSION[$guid]['pageLoads'] = null;
                        $URL .= '&return=successa';
                        header("Location: {$URL}");
                        exit() ;
                    }

                    $_SESSION[$guid]['passwordStrongSalt'] = $salt;
                    $_SESSION[$guid]['passwordStrong'] = $passwordStrong;
                    $_SESSION[$guid]['pageLoads'] = null;
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}

<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightPersonID = $_GET['pupilsightPersonID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/user_manage_password.php&pupilsightPersonID=$pupilsightPersonID&search=".$_GET['search'];

if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage_password.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if person specified
    if ($pupilsightPersonID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightPersonID' => $pupilsightPersonID);
            $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            $passwordNew = $_POST['passwordNew'];
            $passwordConfirm = $_POST['passwordConfirm'];
            $passwordForceReset = $_POST['passwordForceReset'];

            //Validate Inputs
            if ($passwordNew == '' or $passwordConfirm == '') {
                $URL .= '&return=error3';
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
                        $salt = getSalt();
                        $passwordStrong = hash('sha256', $salt.$passwordNew);

                        //Write to database
                        try {
                            $data = array('passwordStrong' => $passwordStrong, 'passwordStrongSalt' => $salt, 'passwordForceReset' => $passwordForceReset, 'pupilsightPersonID' => $pupilsightPersonID);
                            $sql = "UPDATE pupilsightPerson SET password='', passwordStrong=:passwordStrong, passwordStrongSalt=:passwordStrongSalt, passwordForceReset=:passwordForceReset, failCount=0 WHERE pupilsightPersonID=:pupilsightPersonID";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        $URL .= '&return=success0';
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}

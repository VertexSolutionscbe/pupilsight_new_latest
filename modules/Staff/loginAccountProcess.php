<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Contracts\Comms\SMS;

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Staff/loginAccount.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/loginAccount.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $sms = $container->get(SMS::class);
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    //nsRSrnc2
    //Proceed!
    //Validate Inputs
    $password = $_POST['password'];
    $types = $_POST['content'];
    $student_id = $_POST['personId'];

    if (!empty($types)) {
        $notTypes = explode(',', $types);
    }


    if ($password == ''  or $student_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        foreach ($student_id as $pupilsightPersonID) {
            $sqls = 'SELECT username, email, phone1, pupilsightRoleIDPrimary FROM pupilsightPerson WHERE pupilsightPersonID = ' . $pupilsightPersonID . ' ';
            $results = $connection2->query($sqls);
            $userData = $results->fetch();

            $salt = getSalt();
            $passwordStrong = hash('sha256', $salt . $password);

            $datafort12 = array('passwordStrong' => $passwordStrong, 'passwordStrongSalt' => $salt, 'canLogin' => 'Y', 'pupilsightPersonID' => $pupilsightPersonID);
            $sqlfort12 = 'UPDATE pupilsightPerson SET passwordStrong=:passwordStrong, passwordStrongSalt=:passwordStrongSalt, canLogin=:canLogin WHERE pupilsightPersonID=:pupilsightPersonID';
            $resultfort12 = $connection2->prepare($sqlfort12);
            $resultfort12->execute($datafort12);

            if (!empty($types)) {

                foreach ($notTypes as $nt) {

                    if (!empty($userData)) {

                        if (!empty($userData['email']) && $nt == 'Email') {
                            $sqls = 'SELECT content FROM pupilsightContent WHERE type = "' . $nt . '" AND user_type = "Staff" ';
                            $results = $connection2->query($sqls);
                            $contentData = $results->fetch();

                            if (!empty($contentData)) {
                                $content = $contentData['content'];

                                $to = $userData['email'];
                                $subject = 'Login credentials';
                                $body = str_replace('$username', $userData['username'], $content);
                                $body = str_replace('$password', $password, $body);
                                $body = nl2br($body);
                                $crtd =  date('Y-m-d H:i:s');
                                $cuid = $_SESSION[$guid]['pupilsightPersonID'];

                                $url = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Staff/mailsend.php';
                                $url .= "&to=" . $to;
                                $url .= "&sub=" . rawurlencode($subject);
                                $url .= "&body=" . rawurlencode($body);

                                $res = file_get_contents($url);
                                $body = str_replace("'","",$body);
                                $sq = "INSERT INTO user_email_sms_sent_details SET type='2', pupilsightPersonID = " . $pupilsightPersonID . ", email='" . $to . "', subject='" . $subject . "', description='" .  stripslashes($body) . "', uid=" . $cuid . " ";
                                $connection2->query($sq);
                            }
                        }

                        if (!empty($userData['phone1'])  && $nt == 'Sms') {
                            $cuid = $_SESSION[$guid]['pupilsightPersonID'];

                            $sqls = 'SELECT content FROM pupilsightContent WHERE type = "' . $nt . '" AND user_type = "Staff" ';
                            $results = $connection2->query($sqls);
                            $contentData = $results->fetch();

                            if (!empty($contentData)) {
                                $content = $contentData['content'];

                                $msg = str_replace('$username', $userData['username'], $content);
                                $msg = str_replace('$password', $password, $msg);

                                $number = $userData['phone1'];
                                $smspupilsightPersonID = $userData['pupilsightPersonID'];

                                /*$urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
                                $urls .="&send_to=".$number;
                                $urls .="&msg=".rawurlencode($msg);
                                $urls .="&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
                                //echo $urls;
                                $resms = file_get_contents($urls);
                                */

                                $msgto=$smspupilsightPersonID;
                                $msgby=$_SESSION[$guid]["pupilsightPersonID"];
                                $res = $sms->sendSMSPro($number, $msg, $msgto, $msgby);
                                $msg = str_replace("'","",$msg);
                                if ($res) {
                                    $sq = "INSERT INTO user_email_sms_sent_details SET type='1', sent_to = '1', pupilsightPersonID = " . $pupilsightPersonID . ", phone=" . $number . ", description='" . stripslashes($msg) . "', uid=" . $cuid . " ";
                                    $connection2->query($sq);
                                }
                            }
                        }
                    }
                }
            }
        }
        $URL .= "&return=success0";
        header("Location: {$URL}");
    }
}

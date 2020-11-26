<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/loginAccount.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/loginAccount.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    //nsRSrnc2
    //Proceed!
    //Validate Inputs
    $password = $_POST['password'];
    $content = $_POST['content'];
    $student_id = $_POST['personId'];
    
    
    if ($password == ''  or $student_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        foreach($student_id as $pupilsightPersonID){
            $sqls = 'SELECT username, email FROM pupilsightPerson WHERE pupilsightPersonID = '.$pupilsightPersonID.' ';
            $results = $connection2->query($sqls);
            $userData = $results->fetch();

            $salt = getSalt();
            $passwordStrong = hash('sha256', $salt.$password);

            $datafort12 = array('passwordStrong' => $passwordStrong, 'passwordStrongSalt' => $salt, 'canLogin' => 'Y', 'pupilsightPersonID' => $pupilsightPersonID);
            $sqlfort12 = 'UPDATE pupilsightPerson SET passwordStrong=:passwordStrong, passwordStrongSalt=:passwordStrongSalt, canLogin=:canLogin WHERE pupilsightPersonID=:pupilsightPersonID';
            $resultfort12 = $connection2->prepare($sqlfort12);
            $resultfort12->execute($datafort12);

            if(!empty($userData['email']) && !empty($userData['username'])){

                $to = $userData['email'];
                $subject = 'Login credentials';
                $body = str_replace('$username',$userData['username'] , $content);
                $body = str_replace('$password',$password , $body);
                $body = nl2br($body);
                $crtd =  date('Y-m-d H:i:s');
                $cuid = $_SESSION[$guid]['pupilsightPersonID'];

                $url = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/mailsend.php';
                $url .="&to=".$to;
                $url .="&sub=".rawurlencode($subject);
                $url .="&body=".rawurlencode($body);

                $res = file_get_contents($url);
                $sq = "INSERT INTO user_email_sms_sent_details SET type='2', pupilsightPersonID = " . $pupilsightPersonID . ", email='" . $to . "', subject='" . $subject . "', description='" . $body . "', uid=" . $cuid . " ";
                $connection2->query($sq);
            }


        }
            
        $URL .= "&return=success0";
        header("Location: {$URL}");
       
    }
}

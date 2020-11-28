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
    $types = $_POST['content'];
    $student_id = $_POST['personId'];

    if(!empty($types)){
        $notTypes = explode(',', $types);
    }
    
    
    if ($password == ''  or $student_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        foreach($student_id as $pupilsightPersonID){
            $sqls = 'SELECT username, email, phone1, pupilsightRoleIDPrimary FROM pupilsightPerson WHERE pupilsightPersonID = '.$pupilsightPersonID.' ';
            $results = $connection2->query($sqls);
            $userData = $results->fetch();

            $salt = getSalt();
            $passwordStrong = hash('sha256', $salt.$password);

            $datafort12 = array('passwordStrong' => $passwordStrong, 'passwordStrongSalt' => $salt, 'canLogin' => 'Y', 'pupilsightPersonID' => $pupilsightPersonID);
            $sqlfort12 = 'UPDATE pupilsightPerson SET passwordStrong=:passwordStrong, passwordStrongSalt=:passwordStrongSalt, canLogin=:canLogin WHERE pupilsightPersonID=:pupilsightPersonID';
            $resultfort12 = $connection2->prepare($sqlfort12);
            $resultfort12->execute($datafort12);

            if(!empty($types)){

                foreach($notTypes as $nt){

                    if($userData['pupilsightRoleIDPrimary'] == '004'){

                        if(!empty($userData['email']) && $nt == 'Email'){
                            $sqls = 'SELECT content FROM pupilsightContent WHERE type = "'.$nt.'" ';
                            $results = $connection2->query($sqls);
                            $contentData = $results->fetch();

                            if(!empty($contentData)){
                                $content = $contentData['content'];

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
                                $sq = "INSERT INTO user_email_sms_sent_details SET type='2', pupilsightPersonID = " . $pupilsightPersonID . ", email='" . $to . "', subject='" . $subject . "', description='" .  stripslashes($body) . "', uid=" . $cuid . " ";
                                $connection2->query($sq);
                            }
                        }

                        if(!empty($userData['phone1'])  && $nt == 'Sms'){
                            $cuid = $_SESSION[$guid]['pupilsightPersonID'];

                            $sqls = 'SELECT content FROM pupilsightContent WHERE type = "'.$nt.'" ';
                            $results = $connection2->query($sqls);
                            $contentData = $results->fetch();
                            
                            if(!empty($contentData)){
                                $content = $contentData['content'];

                                $msg = str_replace('$username',$userData['username'] , $content);
                                $msg = str_replace('$password',$password , $msg);

                                $number = $userData['phone1'];
                                $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
                                $urls .="&send_to=".$number;
                                $urls .="&msg=".rawurlencode($msg);
                                $urls .="&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
                                //echo $urls;
                                $resms = file_get_contents($urls);

                                $sq = "INSERT INTO user_email_sms_sent_details SET type='1', sent_to = '1', pupilsightPersonID = " . $pupilsightPersonID . ", phone=" . $number . ", description='" . stripslashes($msg) . "', uid=" . $cuid . " ";
                                $connection2->query($sq);
                            }
                        }
                    } else {
                        $sqla = "SELECT  parent1.email as fatherEmail, parent1.phone1 as fatherPhone, parent2.email as motherEmail, parent2.phone1 as motherPhone FROM pupilsightPerson AS a 
                        LEFT JOIN pupilsightFamilyChild AS child ON child.pupilsightPersonID=a.pupilsightPersonID 
                        LEFT JOIN pupilsightFamilyAdult AS adult1 ON adult1.pupilsightFamilyID=child.pupilsightFamilyID AND adult1.contactPriority=1 
                        LEFT JOIN pupilsightPerson as parent1 ON parent1.pupilsightPersonID=adult1.pupilsightPersonID AND parent1.status='Full' 
                        LEFT JOIN pupilsightFamilyAdult as adult2 ON adult2.pupilsightFamilyID=child.pupilsightFamilyID AND adult2.contactPriority=2 
                        LEFT JOIN pupilsightPerson as parent2 ON parent2.pupilsightPersonID=adult2.pupilsightPersonID AND parent2.status='Full' 
                        WHERE a.pupilsightPersonID = " . $pupilsightPersonID . " ";
                        $result = $connection2->query($sqla);
                        $studentData = $result->fetch();
                        // echo '<pre>';
                        // print_r($studentData);
                        // echo '</pre>';
                        // die();
                        $sqls = 'SELECT content FROM pupilsightContent WHERE type = "'.$nt.'" ';
                        $results = $connection2->query($sqls);
                        $contentData = $results->fetch();
                        if(!empty($contentData)){
                            $content = $contentData['content'];
                            $cuid = $_SESSION[$guid]['pupilsightPersonID'];
                            
                            if(!empty($studentData['fatherEmail']) && $nt == 'Email'){
                                
                                $to = $studentData['fatherEmail'];
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
                                // echo $url;
                                // die();
                                $res = file_get_contents($url);
                                $sq = "INSERT INTO user_email_sms_sent_details SET type='2', pupilsightPersonID = " . $pupilsightPersonID . ", email='" . $to . "', subject='" . $subject . "', description='" .  stripslashes($body) . "', uid=" . $cuid . " ";
                                $connection2->query($sq);
                            }
                            
                            if(!empty($studentData['motherEmail']) && $nt == 'Email'){
                                $to = $studentData['motherEmail'];
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
                                $sq = "INSERT INTO user_email_sms_sent_details SET type='2', pupilsightPersonID = " . $pupilsightPersonID . ", email='" . $to . "', subject='" . $subject . "', description='" .  stripslashes($body) . "', uid=" . $cuid . " ";
                                $connection2->query($sq);
                            }

                            if(!empty($studentData['fatherPhone']) && $nt == 'Sms'){
                                $msg = str_replace('$username',$userData['username'] , $content);
                                $msg = str_replace('$password',$password , $msg);

                                $number = $studentData['fatherPhone'];
                                $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
                                $urls .="&send_to=".$number;
                                $urls .="&msg=".rawurlencode($msg);
                                $urls .="&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
                                //echo $urls;
                                $resms = file_get_contents($urls);

                                $sq = "INSERT INTO user_email_sms_sent_details SET type='1', sent_to = '1', pupilsightPersonID = " . $pupilsightPersonID . ", phone=" . $number . ", description='" . stripslashes($msg) . "', uid=" . $cuid . " ";
                                $connection2->query($sq);
                            }

                            if(!empty($studentData['motherPhone']) && $nt == 'Sms'){
                                $msg = str_replace('$username',$userData['username'] , $content);
                                $msg = str_replace('$password',$password , $msg);

                                $number = $studentData['motherPhone'];
                                $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
                                $urls .="&send_to=".$number;
                                $urls .="&msg=".rawurlencode($msg);
                                $urls .="&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
                                //echo $urls;
                                $resms = file_get_contents($urls);

                                $sq = "INSERT INTO user_email_sms_sent_details SET type='1', sent_to = '1', pupilsightPersonID = " . $pupilsightPersonID . ", phone=" . $number . ", description='" . stripslashes($msg) . "', uid=" . $cuid . " ";
                                $connection2->query($sq);
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

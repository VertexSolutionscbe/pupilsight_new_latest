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
    $student_id = $_POST['personId'];
    
    
    if ($password == ''  or $student_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        foreach($student_id as $pupilsightPersonID){
            $salt = getSalt();
            $passwordStrong = hash('sha256', $salt.$password);

            $datafort12 = array('passwordStrong' => $passwordStrong, 'passwordStrongSalt' => $salt, 'canLogin' => 'Y', 'pupilsightPersonID' => $pupilsightPersonID);
            $sqlfort12 = 'UPDATE pupilsightPerson SET passwordStrong=:passwordStrong, passwordStrongSalt=:passwordStrongSalt, canLogin=:canLogin WHERE pupilsightPersonID=:pupilsightPersonID';
            $resultfort12 = $connection2->prepare($sqlfort12);
            $resultfort12->execute($datafort12);


        }
            
        $URL .= "&return=success0";
        header("Location: {$URL}");
       
    }
}

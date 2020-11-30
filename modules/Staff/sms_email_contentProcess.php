<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/loginAccount.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/loginAccount.php') == false) {
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
    $post_content = $_POST['content'];
    $post_type = $_POST['type'];
    
    
    if ($post_content == ''  or $post_type == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        

        $datafort12 = array('content' => $post_content, 'type' => $post_type, 'user_type' => 'Staff');
        $sqlfort12 = 'UPDATE pupilsightContent SET content=:content WHERE type=:type AND user_type=:user_type';
        $resultfort12 = $connection2->prepare($sqlfort12);
        $resultfort12->execute($datafort12);

            
        $URL .= "&return=success0";
        header("Location: {$URL}");
       
    }
}

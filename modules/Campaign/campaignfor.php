<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/index.php';
$session = $container->get('session');

if (isActionAccessible($guid, $connection2, '/modules/Campaign/campaignfor.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs

    
    $page_for = $_POST['type'];
    $campaignid = $session->get('campaignid');
   
    if ($campaignid == '' or $page_for == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        
            //Check for other currents
            
                //Write to database
                try {
                    $campaign_id = $campaignid;
                    // $data1 = array('page_for' => $page_for, 'id' => $campaign_id);
                    // $sql1 = "UPDATE campaign SET page_for=:page_for WHERE id=:id";
                    // $result1 = $connection2->prepare($sql1);
                    // $result1->execute($data1);

                    $data = array('id' => $campaignid);
                    $sql = 'SELECT * FROM campaign WHERE id=:id';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                    $values = $result->fetch();
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                }

    
                // $URL .= "&return=success0&editID=$AI";
                //$session->forget(['campaignid']);
                $name = $values['name'];
                $ayear = $values['academic_year'];
                $id = $campaignid;
                $session->forget(['campaignid']);
                $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/wf_add.php&id='.$id.'&name='.$name.'&academic_year='.$ayear.'&search=';
                //header("Location: {$URL}");
                echo $URL;
        
    }
}

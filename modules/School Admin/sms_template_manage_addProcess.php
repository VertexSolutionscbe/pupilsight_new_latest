<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/sms_template_manage_add.php';
$session = $container->get('session');
$session->forget(['campaignid']);

if (isActionAccessible($guid, $connection2, '/modules/School Admin/sms_template_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    
    $name = $_POST['name'];
    if(!empty($_POST['status'])){
        $status = implode('',$_POST['status']);
    } else {
        $status = 0;
    }
    
    $entities = implode(', ',$_POST['entities']);
    $description = $_POST['description'];
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
    
    if ($name == '' or $entities == '' or $description == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'type' => 'Sms');
            $sql = 'SELECT * FROM pupilsightTemplate WHERE name=:name AND type=:type';
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
            //Check for other currents
            
                //Write to database
                try {
                    $data = array('name' => $name, 'type' => 'Sms', 'status' => $status, 'description' => $description, 'entities' => $entities,'created_by' => $cuid);
                    $sql = "INSERT INTO pupilsightTemplate SET name=:name, type=:type, status=:status, description=:description, entities=:entities, created_by=:created_by";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error9';
                    header("Location: {$URL}");
                }

                //Last insert ID
                $URL .= '&return=success0';
                header("Location: {$URL}");
           
        }
    }
}




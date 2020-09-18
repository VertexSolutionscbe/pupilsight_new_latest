<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightTemplateID = $_GET['pupilsightTemplateID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/sms_template_manage_edit.php&pupilsightTemplateID='.$pupilsightTemplateID;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/sms_template_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightTemplateID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightTemplateID' => $pupilsightTemplateID);
            $sql = 'SELECT * FROM pupilsightTemplate WHERE pupilsightTemplateID=:pupilsightTemplateID';
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
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'type' => 'Sms', 'pupilsightTemplateID' => $pupilsightTemplateID);
                    $sql = 'SELECT * FROM pupilsightTemplate WHERE (name=:name AND type=:type) AND NOT pupilsightTemplateID=:pupilsightTemplateID';
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
                    

                        //Write to database
                        try {
                            $data = array('name' => $name, 'type' => 'Sms', 'status' => $status, 'description' => $description, 'entities' => $entities,'created_by' => $cuid,'pupilsightTemplateID' => $pupilsightTemplateID);
                            
                            $sql = "UPDATE pupilsightTemplate SET name=:name, type=:type, status=:status, description=:description, entities=:entities, created_by=:created_by WHERE pupilsightTemplateID=:pupilsightTemplateID";
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

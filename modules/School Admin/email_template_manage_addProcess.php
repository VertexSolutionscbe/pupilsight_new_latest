<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/email_template_manage_add.php';
$session = $container->get('session');
$session->forget(['campaignid']);

if (isActionAccessible($guid, $connection2, '/modules/School Admin/email_template_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs

    $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);
    $fileUploader->getFileExtensions();
    
    $name = $_POST['name'];
    if(!empty($_POST['status'])){
        $status = implode('',$_POST['status']);
    } else {
        $status = 0;
    }
    $entities = implode(', ',$_POST['entities']);
    $subject = $_POST['subject'];
    $description = stripslashes($_POST['description']);
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
    
    if ($name == '' or $entities == '' or $subject == '' or $description == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'type' => 'Email');
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

            //Move attached file, if there is one
                if (!empty($_FILES['file']['tmp_name'])) {
                    $file = (isset($_FILES['file']))? $_FILES['file'] : null;

                    // Upload the file, return the /uploads relative path
                    $attachment = $fileUploader->uploadFromPost($file, $name);

                    if (empty($attachment)) {
                        $partialFail = true;
                    }
                } else {
                    $attachment = '';
                }
            //echo $attachment;
            //die();
                //Write to database
                try {
                    $data = array('name' => $name, 'type' => 'Email', 'status' => $status, 'description' => $description, 'entities' => $entities, 'subject' => $subject, 'attach_file' => $attachment,'created_by' => $cuid);
                    $sql = "INSERT INTO pupilsightTemplate SET name=:name, type=:type, status=:status, description=:description, entities=:entities,subject=:subject, attach_file=:attach_file, created_by=:created_by";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                }

                //Last insert ID
                $URL .= '&return=success0';
                header("Location: {$URL}");
           
        }
    }
}




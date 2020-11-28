<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
ini_set('max_execution_time', 7200);

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address']).'/tc_template_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/tc_template_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // print_r($_FILES);
    // die();
    //Proceed!
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $pupilsightProgramID = $_POST['pupilsightProgramID'];
    $classIds = $_POST['classIds'];
    if(!empty($_POST['classIds'])){
        $pupilsightYearGroupID = implode(',',$_POST['classIds']);
    } else {
        $pupilsightYearGroupID = '';
    }
    $name = $_POST['name'];
    $type = $_POST['type'];
    
   
    if ($name == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'type' => $type);
            $sql = 'SELECT * FROM pupilsightDocTemplate WHERE name=:name AND type=:type';
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

                    $attachment = '';
                    //Move attached image  file, if there is one
                    if(isset($_FILES["file"]) && $_FILES["file"]["error"] == 0){
                        $allowed = array("docx" => "docx");
                        $filename = $_FILES["file"]["name"];
                        $filetype = $_FILES["file"]["type"];
                        $filesize = $_FILES["file"]["size"];
                       
                        // Verify file extension
                        $ext = pathinfo($filename, PATHINFO_EXTENSION);
                        if(!array_key_exists($ext, $allowed)) die("Error: Please select a valid file format.");
    
    
                        $filename = time() . '_' .  $_FILES["file"]["name"];
                        $fileTarget = $_SERVER['DOCUMENT_ROOT']."/pupilsight/public/doc_template/" . $filename;	
                        if(move_uploaded_file($_FILES["file"]["tmp_name"], $fileTarget)){
                            echo "Template updated successfully";
                        } else {
                                echo "No";
                        }
                    } else{
                        // echo "Error: " . $_FILES["file"]["error"];
                    }
    
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'classIds' => $pupilsightYearGroupID, 'name' => $name, 'type' => $type, 'path' => $fileTarget, 'filename' => $filename);
                    $sql = "INSERT INTO pupilsightDocTemplate SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, classIds=:classIds, name=:name, type=:type, path=:path, filename=:filename";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                }
   
                // $URL .= "&return=success0&editID=$AI";
              
                $URL .= '&return=success0';
                header("Location: {$URL}");
           
        }
    }
}
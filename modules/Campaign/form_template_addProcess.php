<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
ini_set('max_execution_time', 7200);
$id = $_POST['id'];

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . getModuleName($_GET['address']) . '/form_template_manage.php&id=' . $id . '';

if (isActionAccessible($guid, $connection2, '/modules/Campaign/index.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // print_r($_FILES);
    // die();
    //Proceed!
    //$id = $_POST['id'];
    $name = $_POST['name'];


    if ($name == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness

        //Check for other currents

        //Write to database
        try {

            $attachment = '';
            //Move attached image  file, if there is one
            if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
                $allowed = array("docx" => "docx");
                $filename = $_FILES["file"]["name"];
                $filetype = $_FILES["file"]["type"];
                $filesize = $_FILES["file"]["size"];

                // Verify file extension
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (!array_key_exists($ext, $allowed)) die("Error: Please select a valid file format.");


                $filename = time() . '_' .  $_FILES["file"]["name"];
                $fileTarget = $_SERVER['DOCUMENT_ROOT'] . "/public/application_template/" . $filename;
                if (move_uploaded_file($_FILES["file"]["tmp_name"], $fileTarget)) {
                    echo "Template updated successfully";
                } else {
                    echo "No";
                }
            } else {
                // echo "Error: " . $_FILES["file"]["error"];
            }

            $data = array('template_name' => $name, 'template_path' => $fileTarget, 'template_filename' => $filename, 'id' => $id);
            $sql = "UPDATE campaign SET template_name=:template_name, template_path=:template_path, template_filename=:template_filename WHERE id=:id";
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

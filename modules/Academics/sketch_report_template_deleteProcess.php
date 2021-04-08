<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];

$sql = 'SELECT sketch_id FROM examinationReportSketchTemplateMaster WHERE id = "' . $id . '" ';
$result = $connection2->query($sql);
$sketchData = $result->fetch();

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Academics/sketch_report_template_manage.php&id='.$sketchData['sketch_id'].' ';

if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_attribute.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM examinationReportSketchTemplateMaster WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);

           
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

       
       
        if ($result->rowCount() != 1 ) {
           $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('id' => $id);
                $sql = 'DELETE FROM examinationReportSketchTemplateMaster WHERE id=:id';
                $result = $connection2->prepare($sql);
                $result->execute($data);

               
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URL = $URL.'&return=success0';
            header("Location: {$URL}");
        }
    }
}

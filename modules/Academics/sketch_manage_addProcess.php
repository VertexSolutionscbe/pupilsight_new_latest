<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/sketch_manage.php';


if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
//  echo '<pre>';
//  print_r($_POST);
//  echo '</pre>';die();
    
    $name = $_POST['sketch_name'];    
    $code = $_POST['sketch_code'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $class_ids = implode(',', $_POST['class_ids']);
    $pupilsightProgramID = $_POST['pupilsightProgramID'];
    
    if ($name == '' or $code == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('sketch_name' => $name, 'sketch_code' => $code);
            $sql = 'SELECT * FROM examinationReportTemplateSketch WHERE sketch_code=:sketch_code OR sketch_name=:sketch_name';
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
                    $data = array('sketch_name' => $name, 'sketch_code' => $code, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'class_ids' => $class_ids);
                    $sql = "INSERT INTO examinationReportTemplateSketch SET sketch_name=:sketch_name, sketch_code=:sketch_code, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID,class_ids=:class_ids";
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




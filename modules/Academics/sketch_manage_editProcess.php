<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';


$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/sketch_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $id = $_POST['id'];
//print_r($id);die();
    //Proceed!
    //Check if school year specified
    if ($id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM examinationReportTemplateSketch WHERE id=:id';
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
               // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();

    $name = $_POST['sketch_name'];
    $code = $_POST['sketch_code'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $class_ids = implode(',', $_POST['class_ids']);
    $pupilsightProgramID = $_POST['pupilsightProgramID'];
   
   // $udt = date('Y-m-d H:i:s');
            

    if ($name == ''  or $code == '' ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('sketch_name' => $name,'sketch_code'=>$code, 'id' => $id);
                    $sql = 'SELECT * FROM examinationReportTemplateSketch WHERE (sketch_name=:sketch_name OR sketch_code=:sketch_code) AND NOT id=:id';
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
                        $data = array('sketch_name' => $name, 'sketch_code' => $code, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'class_ids' => $class_ids, 'id' => $id);
                        $sql = 'UPDATE examinationReportTemplateSketch SET sketch_name=:sketch_name, sketch_code=:sketch_code, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID,class_ids=:class_ids WHERE id=:id';
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

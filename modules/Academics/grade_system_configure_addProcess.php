<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';

$gradeSystemId = $_POST['gradeSystemId'];
$grade_name = $_POST['grade_name'];
$grade_point = $_POST['grade_point'];
$lower_limit = $_POST['lower_limit'];
$upper_limit = $_POST['upper_limit'];
$rank = $_POST['rank'];
if(isset($_POST['subject_status'])){
$subject_status = $_POST['subject_status'];
} else {
   $subject_status = ''; 
}

$class_obtained = $_POST['class_obtained'];
$description = $_POST['description'];


$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/grade_system_configure.php&id='.$gradeSystemId.'';

if (isActionAccessible($guid, $connection2, '/modules/Academics/grade_system_configure_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    if ($grade_name == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('gradeSystemId'=>$gradeSystemId,'grade_name' => $grade_name);
            $sql = 'SELECT * FROM examinationGradeSystemConfiguration WHERE grade_name=:grade_name AND gradeSystemId=:gradeSystemId';
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
                $data = array('gradeSystemId' => $gradeSystemId, 'grade_name' => $grade_name,'grade_point' => $grade_point, 'lower_limit' => $lower_limit,'upper_limit' => $upper_limit, 'rank' => $rank,'subject_status' => $subject_status, 'class_obtained' => $class_obtained, 'description' => $description);
                $sql = 'INSERT INTO examinationGradeSystemConfiguration SET gradeSystemId=:gradeSystemId, grade_name=:grade_name, grade_point=:grade_point, lower_limit=:lower_limit,upper_limit=:upper_limit, rank=:rank, subject_status=:subject_status, class_obtained=:class_obtained, description=:description';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 5, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}

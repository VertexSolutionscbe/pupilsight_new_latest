<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightPersonID = $_POST['staff_id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/feedback_manage.php&stid='.$pupilsightPersonID.'';


if (isActionAccessible($guid, $connection2, '/modules/Staff/feedback_category_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    
//  echo '<pre>';
//  print_r($_POST);
//  echo '</pre>';
    
    $name = $_POST['name'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
        
    $category_id = $_POST['category_id'];
    $pupilsightProgramID = $_POST['pupilsightProgramID'];
    $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
    $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];
    $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
    $feedback_date = date('Y-m-d', strtotime($_POST['feedback_date']));
    $description = $_POST['description'];
   
    if ($name == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('name' => $name, 'category_id' => $category_id, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'feedback_date' => $feedback_date, 'description' => $description);
            $sql = "INSERT INTO pupilsightFeedback SET name=:name, category_id=:category_id, pupilsightPersonID=:pupilsightPersonID, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID, pupilsightDepartmentID=:pupilsightDepartmentID, feedback_date=:feedback_date, description=:description";
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




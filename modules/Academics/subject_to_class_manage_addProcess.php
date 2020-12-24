<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/subject_to_class_manage.php&acaId='.$_POST['pupilsightSchoolYearID'].'&proId='.$_POST['pupilsightProgramID'].'&classId='.$_POST['pupilsightYearGroupID'].'';

if (isActionAccessible($guid, $connection2, '/modules/Academics/subject_to_class_manage.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {

    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    //Proceed!
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $pupilsightProgramID = $_POST['pupilsightProgramID'];
    $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
    $subId = $_POST['pupilsightDepartmentID'];
    $display_name = $_POST['display_name'];
    $sub_type = $_POST['subject_type'];
    $dimode = $_POST['di_mode'];
    $sub_code = $_POST['subject_code'];
    
    //Validate Inputs
    if ($pupilsightYearGroupID == '' or $subId == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            // $data1 = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID);
            // $sql1 = 'DELETE FROM subjectToClassCurriculum WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID';
            // $result1 = $connection2->prepare($sql1);
            // $result1->execute($data1);

            foreach($subId as $sid){
                $pupilsightDepartmentID = $sid;
                $subject_code = $sub_code[$sid];
                $subject_display_name = $display_name[$sid];
                $subject_type = $sub_type[$sid];
                $di_mode = $dimode[$sid];

                $sqlchk = 'SELECT * FROM subjectToClassCurriculum WHERE pupilsightSchoolYearID= '.$pupilsightSchoolYearID.' AND pupilsightProgramID= '.$pupilsightProgramID.' AND pupilsightYearGroupID= '.$pupilsightYearGroupID.' AND pupilsightDepartmentID = '.$pupilsightDepartmentID.' ';
                $resultchk = $connection2->query($sqlchk);
                $curData = $resultchk->fetch();

                if(!empty($curData)){
                    $curId = $curData['id'];
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID,'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'subject_code' => $subject_code, 'subject_display_name' => $subject_display_name, 'subject_type' => $subject_type, 'di_mode' => $di_mode , 'id' => $curId);
                    $sql = 'UPDATE subjectToClassCurriculum SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightDepartmentID=:pupilsightDepartmentID, subject_code=:subject_code, subject_display_name=:subject_display_name, subject_type=:subject_type, di_mode=:di_mode WHERE id=:id';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } else {
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID,'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'subject_code' => $subject_code, 'subject_display_name' => $subject_display_name, 'subject_type' => $subject_type, 'di_mode' => $di_mode);
                    $sql = 'INSERT INTO subjectToClassCurriculum SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightDepartmentID=:pupilsightDepartmentID, subject_code=:subject_code, subject_display_name=:subject_display_name, subject_type=:subject_type, di_mode=:di_mode';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                }


                
            }
    
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Last insert ID
        $AI = str_pad($connection2->lastInsertID(), 10, '0', STR_PAD_LEFT);

        $URL .= "&return=success0&editID=$AI";
        header("Location: {$URL}");
    }
}

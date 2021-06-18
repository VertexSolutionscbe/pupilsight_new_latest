<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
ini_set('max_execution_time', 7200);
$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Academics/entry_marks_byStudent.php';
if (isActionAccessible($guid, $connection2, '/modules/Academics/entry_marks_byStudentProcess.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {

    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    //Proceed!
    $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
    $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];

    $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
    $mark_obtained = $_POST['mark_obtained']; //array for testwise
    $remark_own =  $_POST['remark_own'];
    $pupilsightPersonID =  $_POST['studentID'];


    //Validate Inputs
    if ($pupilsightPersonID == '' || $mark_obtained == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            foreach ($mark_obtained as $key => $Department) {
                $test_id = $key;
                if (!empty($Department)) {
                    foreach ($Department as $k => $skills) {
                        $departmentID = $k;
                        if (!empty($skills)) {
                            foreach ($skills as $ks => $marksdata) {
                                $skill_id = $ks;
                                $marks_abex = NULL;
                                $gradeId = isset($_POST['grade_val'][$key][$k][$ks]) ? $_POST['grade_val'][$key][$k][$ks] : '';

                                if (empty($marksdata)) {
                                    $marks_abex = $_POST['mark_abex'][$key][$k][$ks];
                                    if ($marks_abex == "-") {
                                        $marks_abex = NULL;
                                    } elseif ($marks_abex == "AB" || $marks_abex == "EX") {
                                        $gradeId = '';
                                        $marksdata = '';
                                    }
                                }

                                if (!empty($_POST['remark_own'][$key][$k][$ks])) {
                                    $remark_own = $_POST['remark_own'][$key][$k][$ks];
                                } else {
                                    $remark_own = '';
                                }

                                if (!empty($skill_id)) {
                                    $datadel = array('test_id' => $test_id, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightDepartmentID' => $departmentID, 'skill_id' => $skill_id);
                                    //print_r($datadel);

                                    $sqldel = 'DELETE FROM examinationMarksEntrybySubject WHERE test_id=:test_id AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightPersonID=:pupilsightPersonID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID AND skill_id=:skill_id  ';
                                    $resultdel = $connection2->prepare($sqldel);
                                    $resultdel->execute($datadel);
                                } else {
                                    $datadel = array('test_id' => $test_id, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightDepartmentID' => $departmentID);
                                    //print_r($datadel);

                                    $sqldel = 'DELETE FROM examinationMarksEntrybySubject WHERE test_id=:test_id AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightPersonID=:pupilsightPersonID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID  ';
                                    $resultdel = $connection2->prepare($sqldel);
                                    $resultdel->execute($datadel);
                                }

                                if (!empty($marksdata) || $marksdata == '0' || !empty($marks_abex) || !empty($gradeId) || !empty($remark_own)) {

                                    // if(!empty($marks_abex) || !empty($gradeId)){
                                    //     $marksdata = '';
                                    // }

                                    $remark_type = 'own';
                                    $data = array('pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'test_id' => $test_id, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightDepartmentID' => $departmentID, 'pupilsightPersonID' => $pupilsightPersonID, 'skill_id' => $skill_id, 'marks_obtained' => $marksdata, 'marks_abex' => $marks_abex, 'gradeId' => $gradeId, 'remark_type' => $remark_type, 'remarks' => $remark_own);

                                    $sql = 'INSERT INTO examinationMarksEntrybySubject SET test_id=:test_id, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID, pupilsightDepartmentID=:pupilsightDepartmentID, pupilsightPersonID=:pupilsightPersonID, skill_id=:skill_id, marks_obtained=:marks_obtained,marks_abex=:marks_abex,pupilsightPersonIDTaker=:pupilsightPersonIDTaker, gradeId=:gradeId, remark_type=:remark_type, remarks=:remarks';
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);

                                    $data1 = array('pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'test_id' => $test_id, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightDepartmentID' => $departmentID, 'pupilsightPersonID' => $pupilsightPersonID, 'skill_id' => $skill_id, 'marks_obtained' => $marksdata, 'marks_abex' => $marks_abex, 'gradeId' => $gradeId, 'remark_type' => $remark_type, 'remark' => $remark_own);


                                    $sql1 = 'INSERT INTO history_of_students_marks SET test_id=:test_id, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID, pupilsightDepartmentID=:pupilsightDepartmentID, pupilsightPersonID=:pupilsightPersonID, skill_id=:skill_id, marks_obtained=:marks_obtained,marks_abex=:marks_abex,pupilsightPersonIDTaker=:pupilsightPersonIDTaker, gradeId=:gradeId, remark_type=:remark_type, remark=:remark';
                                    $result1 = $connection2->prepare($sql1);
                                    $result1->execute($data1);
                                }
                            }
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            // print_r($e);
            // die();
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }
        //die();

        $URL .= "&return=success0";
        header("Location: {$URL}");
    }
}

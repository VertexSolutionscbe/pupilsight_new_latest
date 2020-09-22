<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
ini_set('max_execution_time', 7200);
$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Academics/manage_marks_entry_by_subject.php';
if (isActionAccessible($guid, $connection2, '/modules/Academics/manage_marks_entry_by_subject.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    /*
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';
    die();*/
    //Proceed!
    $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
    $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
    $test_id = $_POST['test_id'];
    $skill_id = $_POST['skill_id'];
    $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
    $stud_id = $_POST['student_id'];
    $mark_obtained = $_POST['mark_obtained']; //array for testwise
    $remark_frmlst = $_POST['remark_frmlst'];
    $remark_own =  $_POST['remark_own'];

    //Validate Inputs
    if ($pupilsightDepartmentID == '' or $mark_obtained == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            foreach ($test_id as $tid) {
                foreach ($stud_id as $sd => $sid) {
                    $student_id = $sid;
                    $rmrk_frm_list = isset($_POST['remark_frmlst'][$tid][$sid]) ? $_POST['remark_frmlst'][$tid][$sid] : "";
                    $rmrk_own = isset($_POST['remark_own'][$tid][$sid]) ? $_POST['remark_own'][$tid][$sid] : "";
                    $remark_val = "";


                    if ($_POST['remark_frmlst'][$tid][$sid] != "") {
                        $remark_val = $_POST['remark_frmlst'][$tid][$sid];
                    } else {
                        $remark_val = $_POST['remark_own'][$tid][$sid];
                    }
                    $locksts =   $_POST['lock_status'][$tid][$sid];

                    // echo    $grade_obtn = $_POST['remark_type'][$tid][$sid];  

                    if (isset($_POST['mark_obtained'][$tid][$sid])) {
                        $mark_obtn  = $_POST['mark_obtained'][$tid][$sid];
                    } else {
                        $mark_obtn = '';
                    }

                    

                    if (isset($_POST['grade_val'][$tid][$sid])) {
                        $grade_obtn = $_POST['grade_val'][$tid][$sid];
                    } else {
                        $grade_obtn = '';
                    }

                    if (isset($_POST['marks_abex'][$tid][$sid])) {
                        $marks_abex  = $_POST['marks_abex'][$tid][$sid];
                        if ($marks_abex == "-") {
                            $marks_abex = NULL;
                        }else{
                            $grade_obtn = '';
                            $mark_obtn = '';
                        }
                    } else {
                        $marks_abex = '';
                    }
                    // echo $mark_obtn;
                    // die();

                    $entry_type = 2;

                    if ($locksts != 1 && $mark_obtn != 0) {
                        // $data1 = array('test_id' => $tid, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'pupilsightPersonID' => $student_id, 'skill_id' => $skill_id, 'entrytype' => $entry_type);
                        // $sql1 = 'DELETE FROM examinationMarksEntrybySubject WHERE test_id=:test_id  AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightPersonID=:pupilsightPersonID AND  skill_id=:skill_id AND entrytype=:entrytype';
                        // $result1 = $connection2->prepare($sql1);
                        // $result1->execute($data1);

                        $data1 = array('test_id' => $tid, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'pupilsightPersonID' => $student_id);
                        $sql1 = 'DELETE FROM examinationMarksEntrybySubject WHERE test_id=:test_id  AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightPersonID=:pupilsightPersonID';
                        $result1 = $connection2->prepare($sql1);
                        $result1->execute($data1);


                        // `examinationMarksEntrybySubject` ,`test_id`,`pupilsightYearGroupID`,`pupilsightRollGroupID`,`pupilsightDepartmentID`,`pupilsightPersonID`,`skill_id`,`marks_obtained`,`gradeId`,`remarks`,                
                        $data = array('pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'test_id' => $tid, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'pupilsightPersonID' => $student_id, 'skill_id' => $skill_id, 'marks_obtained' => $mark_obtn, 'marks_abex' => $marks_abex, 'gradeId' => $grade_obtn, 'remarks' => $remark_val, 'status' => $locksts, 'entrytype' => $entry_type);
                          echo "<pre>";
                        print_r($data);
                        $sql = 'INSERT INTO examinationMarksEntrybySubject 
                        SET pupilsightPersonIDTaker=:pupilsightPersonIDTaker, test_id=:test_id, pupilsightYearGroupID=:pupilsightYearGroupID, 
                        pupilsightRollGroupID=:pupilsightRollGroupID, pupilsightDepartmentID=:pupilsightDepartmentID, pupilsightPersonID=:pupilsightPersonID, skill_id=:skill_id, 
                        marks_obtained=:marks_obtained, marks_abex=:marks_abex, gradeId=:gradeId, 
                        remarks=:remarks,status=:status,entrytype=:entrytype';

                        $result = $connection2->prepare($sql);
                        $result->execute($data);

                        //data store histroy
                        $data_h = array('pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'test_id' => $tid, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightPersonID' => $student_id, 'marks_obtained' => $mark_obtn, 'marks_abex' => $marks_abex, 'remark' => $remark_val, 'gradeId' => $grade_obtn, 'skill_id' => $skill_id);

                        $sql_h = 'INSERT INTO history_of_students_marks SET test_id=:test_id, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID, pupilsightDepartmentID=:pupilsightDepartmentID, pupilsightPersonID=:pupilsightPersonID, skill_id=:skill_id, marks_obtained=:marks_obtained, marks_abex=:marks_abex, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, gradeId=:gradeId, remark=:remark';
                        $result = $connection2->prepare($sql_h);
                        $result->execute($data_h);
                        //ends here 

                        
                    }
                }
            }
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }
        //die();
        //Last insert ID
        // $AI = str_pad($connection2->lastInsertID(), 10, '0', STR_PAD_LEFT);

        // $URL .= "&return=success0&editID=$AI";
        // header("Location: {$URL}");
    }
}
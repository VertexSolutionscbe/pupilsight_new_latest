<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

include $_SERVER["DOCUMENT_ROOT"] . "/db.php";

require __DIR__ . "/moduleFunctions.php";

ini_set('max_execution_time', 7200);
ini_set('memory_limit', '1024M');
set_time_limit(1200);

$URL =
    $_SESSION[$guid]["absoluteURL"] .
    "/index.php?q=/modules/Academics/import_marks.php";

if (isActionAccessible($guid, $connection2, '/modules/Academics/test_marks_upload.php') == false) {
    // Access denied
    $page->addError(__("You do not have access to this action."));
} else {

    if (isset($_GET["return"])) {
        returnProcess($guid, $_GET["return"], null, null);
    }

    $page->breadcrumbs
        ->add(__('Upload Marks'), 'test_marks_upload.php')
        ->add(__("Import Marks"));
    $form = Form::create(
        "importStep1",
        $_SESSION[$guid]["absoluteURL"] .
            "/index.php?q=/modules/" .
            $_SESSION[$guid]["module"] .
            "/import_marks.php"
    );

    $form->addHiddenValue("address", $_SESSION[$guid]["address"]);

    $row = $form->addRow();
    $row->addLabel("file", __("File"))->description(
        __("See Notes below for specification.")
    );
    $row->addFileUpload("file")
        ->required()
        ->accepts(".csv");

    $row = $form->addRow();
    $row->addFooter();
    $row->addSubmit();

    echo $form->getOutput();

    if ($_POST) {
        $pupilsightPersonIDTaker = $_SESSION[$guid]['pupilsightPersonID'];
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $handle = fopen($_FILES['file']['tmp_name'], "r");
        $headers = fgetcsv($handle, 10000, ",");
        $hders = array();
        //echo '<pre>';
        //print_r($headers);
        //create header
        $len = count($headers);
        $i = 1;
        $pos  = 0;
        $mhead = [];
        while ($i < $len) {
            if (!empty($headers[$i])) {
                $ds = explode("/", $headers[$i]);
                $mhead[$pos]["testname"] = trim($ds[0]);
                $sub = explode("#", $ds[1]);
                $mhead[$pos]["subject"] = trim($sub[0]);
                if (isset($sub[1])) {
                    $mhead[$pos]["skill"] = trim($sub[1]);
                } else {
                    $mhead[$pos]["skill"] = "";
                }
                $mhead[$pos]["maxmarks"] = "";
                $mhead[$pos]["getmarks"] = "";
                $mhead[$pos]["remarks"] = "";
                $pos++;
            }
            $i++;
        }
        //print_r($mhead);
        //echo '</pre>';
        // die();

        function setMarks($dt, $mhead)
        {
            $len = count($mhead);
            $i = 0;
            $pos = 2;
            while ($i < $len) {
                $mhead[$i]["getmarks"] = $dt[$pos];
                $pos++;
                $mhead[$i]["remarks"] = $dt[$pos];
                $pos++;
                $i++;
            }
            return $mhead;
        }

        //update total marks
        function updateTotalMarks($dt, $mhead)
        {
            $len = count($mhead);
            $i = 0;
            $pos = 2;
            while ($i < $len) {
                $maxmarks = explode(" ", $dt[$pos]);
                if (isset($maxmarks[1])) {
                    $mhead[$i]["maxmarks"] = $maxmarks[1];
                } else {
                    $mhead[$i]["maxmarks"] = "";
                }
                $pos += 2;
                $i++;
            }
            return $mhead;
        }


        $hders = $headers;

        $header2 = array();
        $all_rows = array();
        $k = 0;
        while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
            if ($k == 0) {
                //2nd header
                $header2 = $data;
                $mhead = updateTotalMarks($data, $mhead);
            }

            if ($k != 0) {
                //$all_rows[] = array_combine($header2, $data);
                $fd = array();
                $fd["student_id"] = trim($data[0]);
                $fd["student_name"] = trim($data[1]);
                $fd["marks"] = setMarks($data, $mhead);
                $all_rows[] = $fd;
            }

            $k++;
        }

        if (!empty($all_rows)) {

            // echo '<pre>';
            // print_r($all_rows);
            // echo '</pre>';
            // die();
            try {
                foreach ($all_rows as  $alrow) {
                    $pupilsightPersonID = $alrow['student_id'];
                    $studentData = getStudentData($pupilsightPersonID, $pupilsightSchoolYearID, $connection2);

                    if (!empty($studentData)) {
                        $pupilsightProgramID = $studentData['pupilsightProgramID'];
                        $pupilsightYearGroupID = $studentData['pupilsightYearGroupID'];
                        $pupilsightRollGroupID = $studentData['pupilsightRollGroupID'];
                        // Marks Entry
                        if (!empty($alrow['marks'])) {
                            try {
                                $sql = "INSERT INTO examinationMarksEntrybySubject (test_id,pupilsightYearGroupID,pupilsightRollGroupID,pupilsightDepartmentID,pupilsightPersonID,skill_id,marks_obtained,marks_abex,gradeId,remark_type,remarks,pupilsightPersonIDTaker) VALUES ";

                                foreach ($alrow['marks'] as $k => $value) {
                                    $testData = getTestId($value['testname'], $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID, $connection2);
                                    $pupilsightDepartmentID = getsubjectID($value['subject'], $pupilsightSchoolYearID, $connection2);
                                    $skill_id = getSkillId($value['skill'], $connection2);
                                    $testname = $value['testname'];
                                    $subject = $value['subject'];
                                    $skill = $value['skill'];
                                    $maxmarks = $value['maxmarks'];

                                    if (!empty($value['getmarks'])) {
                                        $marks_obtained = $value['getmarks'];
                                    } else {
                                        $marks_obtained = 0;
                                    }
                                    $delmarks_obtained = $marks_obtained;

                                    if (!empty($value['remarks'])) {
                                        $remarks = $value['remarks'];
                                        $remark_type = 'own';
                                    } else {
                                        $remarks = '';
                                        $remark_type = '';
                                    }

                                    $gradeId = '';
                                    $test_id = '';
                                    if (!empty($testData)) {
                                        $test_id = $testData['testId'];
                                        $gradeSystemData = getGradeSystemIdBySujectandTest($test_id, $pupilsightDepartmentID, $skill_id, $connection2);
                                        $gradeSystemId = $gradeSystemData['gradeSystemId'];
                                        $max_marks = $gradeSystemData['max_marks'];
                                        if (!empty($marks_obtained)) {
                                            if (is_numeric($marks_obtained)) {
                                                $gradeId = getGradeId($gradeSystemId, $marks_obtained, $connection2);
                                                $marks_abex = '';
                                                if(!empty($max_marks)){
                                                    if((float)$marks_obtained > (float)$max_marks){
                                                        $marks_obtained = '';
                                                    }
                                                }
                                            } else {
                                                if(trim($marks_obtained) == 'AB'){
                                                    $marks_abex = 'AB';
                                                } else if(trim($marks_obtained) == 'EX'){
                                                    $marks_abex = 'EX';
                                                } else {
                                                    $gradeId = getGradeIdByName($gradeSystemId, $marks_obtained, $connection2);
                                                    $marks_abex = '';
                                                }
                                                
                                                $marks_obtained = '';
                                            }
                                        } else {
                                            $gradeId = '';
                                        }
                                    }

                                    if ((!empty($delmarks_obtained) || !empty($remarks)) && !empty($test_id)) {
                                        if (!empty($skill_id)) {
                                            $data1 = array('test_id' => $test_id, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'pupilsightPersonID' => $pupilsightPersonID, 'skill_id' => $skill_id);
                                            $sql1 = 'DELETE FROM examinationMarksEntrybySubject WHERE test_id=:test_id  AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightPersonID=:pupilsightPersonID AND skill_id=:skill_id';
                                            $result1 = $connection2->prepare($sql1);
                                            $result1->execute($data1);
                                        } else {
                                            $data1 = array('test_id' => $test_id, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'pupilsightPersonID' => $pupilsightPersonID);
                                            $sql1 = 'DELETE FROM examinationMarksEntrybySubject WHERE test_id=:test_id  AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightPersonID=:pupilsightPersonID';
                                            $result1 = $connection2->prepare($sql1);
                                            $result1->execute($data1);
                                        }
                                    }


                                    if (!empty($test_id)) {
                                        if ($skill_id == "") {
                                            $skill_id = "0";
                                        }
                                        if ($marks_obtained == 0 || $marks_obtained == "" || empty($marks_obtained)) {
                                            $marks_obtained = "NULL";
                                        }
                                        if ($gradeId == 0 || $gradeId == "" || empty($gradeId)) {
                                            $gradeId = "NULL";
                                        }
                                        $sql .= '("' . $test_id . '",
                                        "' . $pupilsightYearGroupID . '",
                                        "' . $pupilsightRollGroupID . '",
                                        "' . $pupilsightDepartmentID . '",
                                        "' . $pupilsightPersonID . '",
                                        ' . $skill_id . ',
                                        ' . $marks_obtained . ',';
                                        if (empty($marks_abex)) {
                                            $sql .= 'NULL';
                                        } else {
                                            $sql .= "'" . $marks_abex . "'";
                                        }
                                        $sql .= ',' . $gradeId . ',
                                        "' . $remark_type . '",
                                        "' . $remarks . '",
                                        "' . $pupilsightPersonIDTaker . '"),';
                                    }
                                }
                                $sql = rtrim($sql, ", ");
                                //echo $sql;
                                //die();
                                $conn->query($sql);
                            } catch (Exception $ex) {
                                print_r($ex);
                            }

                            try {
                                $sql1 = "INSERT INTO history_of_students_marks (test_id,pupilsightYearGroupID,pupilsightRollGroupID,pupilsightDepartmentID,pupilsightPersonID,skill_id,marks_obtained,marks_abex,gradeId,remark_type,remark,pupilsightPersonIDTaker) VALUES ";

                                foreach ($alrow['marks'] as $k => $value) {
                                    $testData = getTestId($value['testname'], $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID, $connection2);
                                    $pupilsightDepartmentID = getsubjectID($value['subject'], $pupilsightSchoolYearID, $connection2);
                                    $skill_id = getSkillId($value['skill'], $connection2);
                                    $testname = $value['testname'];
                                    $subject = $value['subject'];
                                    $skill = $value['skill'];
                                    $maxmarks = $value['maxmarks'];

                                    if (!empty($value['getmarks'])) {
                                        $marks_obtained = $value['getmarks'];
                                    } else {
                                        $marks_obtained = 0;
                                    }

                                    if (!empty($value['remarks'])) {
                                        $remarks = $value['remarks'];
                                        $remark_type = 'own';
                                    } else {
                                        $remarks = '';
                                        $remark_type = '';
                                    }

                                    $gradeId = '';
                                    $test_id = 0;
                                    if (!empty($testData)) {
                                        $test_id = $testData['testId'];
                                        $gradeSystemData = getGradeSystemIdBySujectandTest($test_id, $pupilsightDepartmentID, $skill_id, $connection2);
                                        $gradeSystemId = $gradeSystemData['gradeSystemId'];
                                        $max_marks = $gradeSystemData['max_marks'];
                                        if (!empty($marks_obtained)) {
                                            if (is_numeric($marks_obtained)) {
                                                $gradeId = getGradeId($gradeSystemId, $marks_obtained, $connection2);
                                                $marks_abex = '';
                                                if(!empty($max_marks)){
                                                    if((float)$marks_obtained > (float)$max_marks){
                                                        $marks_obtained = '';
                                                    }
                                                }
                                            } else {
                                                if(trim($marks_obtained) == 'AB'){
                                                    $marks_abex = 'AB';
                                                } else if(trim($marks_obtained) == 'EX'){
                                                    $marks_abex = 'EX';
                                                } else {
                                                    $gradeId = getGradeIdByName($gradeSystemId, $marks_obtained, $connection2);
                                                    $marks_abex = '';
                                                }
                                                
                                                $marks_obtained = '';
                                            }
                                        } else {
                                            $gradeId = '';
                                        }
                                    }

                                    if (!empty($test_id)) {
                                        if ($skill_id == "") {
                                            $skill_id = "0";
                                        }

                                        if ($marks_obtained == 0 || $marks_obtained == "" || empty($marks_obtained)) {
                                            $marks_obtained = "NULL";
                                        }
                                        if ($gradeId == 0 || $gradeId == "" || empty($gradeId)) {
                                            $gradeId = "NULL";
                                        }
                                        
                                        $sql1 .= '("' . $test_id . '",
                                        "' . $pupilsightYearGroupID . '",
                                        "' . $pupilsightRollGroupID . '",
                                        "' . $pupilsightDepartmentID . '",
                                        "' . $pupilsightPersonID . '",
                                        ' . $skill_id . ',
                                        ' . $marks_obtained . ',';
                                        if (empty($marks_abex)) {
                                            $sql1 .= 'NULL';
                                        } else {
                                            $sql1 .= "'" . $marks_abex . "'";
                                        }
                                        $sql1 .= ',' . $gradeId . ',
                                        "' . $remark_type . '",
                                        "' . $remarks . '",
                                        "' . $pupilsightPersonIDTaker . '"),';
                                    }
                                }
                                $sql1 = rtrim($sql1, ", ");
                                //echo $sql1;
                                //  die();
                                $conn->query($sql1);
                            } catch (Exception $ex) {
                                print_r($ex);
                                // die();
                            }
                        }
                    }
                    // die();
                }
            } catch (Exception $ex) {
                print_r($ex);
            }
        }
        //die();
        fclose($handle);
        $URL .= '&return=success1';
        header("Location: {$URL}");
    }
}

function getStudentData($pupilsightPersonID, $pupilsightSchoolYearID, $connection2)
{
    $sql = 'SELECT pupilsightProgramID, pupilsightYearGroupID, pupilsightRollGroupID FROM pupilsightStudentEnrolment WHERE pupilsightPersonID = "' . $pupilsightPersonID . '" AND pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ';
    $result = $connection2->query($sql);
    $data = $result->fetch();
    $studata = array();
    if (!empty($data)) {
        $studata['pupilsightProgramID'] = $data['pupilsightProgramID'];
        $studata['pupilsightYearGroupID'] = $data['pupilsightYearGroupID'];
        $studata['pupilsightRollGroupID'] = $data['pupilsightRollGroupID'];
        return $studata;
    }
}

function getTestId($value, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID, $connection2)
{
    $sql = 'SELECT a.id, a.gradeSystemId FROM examinationTest AS a LEFT JOIN examinationTestAssignClass AS b ON a.id = b.test_id WHERE a.name = "' . $value . '" AND a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND b.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND b.pupilsightProgramID = "' . $pupilsightProgramID . '" AND b.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" AND b.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '"  ';
    $result = $connection2->query($sql);
    $data = $result->fetch();
    $testData = array();
    if (!empty($data)) {
        $testData['testId'] = $data['id'];
        $testData['gradeSystemId'] = $data['gradeSystemId'];
        return $testData;
    }
}

function getsubjectID($value, $pupilsightSchoolYearID, $connection2)
{
    $sql = 'SELECT pupilsightDepartmentID FROM subjectToClassCurriculum WHERE subject_display_name = "' . $value . '" AND pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ';
    $result = $connection2->query($sql);
    $data = $result->fetch();
    if (!empty($data)) {
        $pupilsightDepartmentID = $data['pupilsightDepartmentID'];
        return $pupilsightDepartmentID;
    }
}

function getSkillId($value, $connection2)
{
    $sql = 'SELECT id FROM ac_manage_skill WHERE name = "' . $value . '" ';
    $result = $connection2->query($sql);
    $data = $result->fetch();
    if (!empty($data)) {
        $skillId = $data['id'];
        return $skillId;
    }
}

function getGradeId($gradeSystemId, $marks_obtained, $connection2)
{
    $sql = 'SELECT grade_name,id, subject_status FROM examinationGradeSystemConfiguration  WHERE gradeSystemId="' . $gradeSystemId . '" AND  (' . $marks_obtained . ' BETWEEN `lower_limit` AND `upper_limit`)';
    $result = $connection2->query($sql);
    $grade = $result->fetch();
    if (!empty($grade)) {
        return $grade['id'];
    }
}

function getGradeIdByName($gradeSystemId, $grade_name, $connection2)
{
    $sql = 'SELECT grade_name,id, subject_status FROM examinationGradeSystemConfiguration  WHERE gradeSystemId="' . $gradeSystemId . '" AND grade_name="' . $grade_name . '" ';
    $result = $connection2->query($sql);
    $grade = $result->fetch();
    if (!empty($grade)) {
        return $grade['id'];
    }
}

function getGradeSystemIdBySujectandTest($test_id, $pupilsightDepartmentID, $skill_id, $connection2)
{
    $sql = 'SELECT gradeSystemId, max_marks FROM examinationSubjectToTest WHERE test_id ="' . $test_id . '" AND pupilsightDepartmentID ="' . $pupilsightDepartmentID . '" AND is_tested = "1" ';
    if (!empty($skill_id)) {
        $sql .= ' AND skill_id = "' . $skill_id . '" ';
    }

    $result = $connection2->query($sql);
    $grade = $result->fetch();
    $data = array();
    if (!empty($grade)) {
        $data['gradeSystemId'] = $grade['gradeSystemId'];
        $data['max_marks'] = $grade['max_marks'];
    }
    return $data;
}


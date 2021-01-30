<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
include '../../db.php';

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;


if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_attribute.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    $sketch_id = $_POST['id'];
    $pupilsightSchoolYearID = $_POST['yid'];
    $pupilsightProgramID = $_POST['pid'];
    $classIds = $_POST['cid'];
    $secIds = $_POST['secid'];
    $mappingIds = explode(',',$_POST['mid']);
    $studentIds = $_POST['stid'];

    //$sketch_id = $_GET['id'];
    $sql = 'SELECT * FROM examinationReportTemplateSketch WHERE id = ' . $sketch_id . ' ';
    $result = $connection2->query($sql);
    $sketchData = $result->fetch();

    $sqlsketch = 'SELECT a.id as erta_id, a.attribute_name, a.attribute_category, a.attribute_type, a.test_master_id, a.attr_ids, a.final_formula, a.grade_id, a.supported_attribute, b.* FROM examinationReportTemplateAttributes AS a LEFT JOIN examinationReportTemplateConfiguration AS b ON a.ertc_id = b.id WHERE a.sketch_id = ' . $sketch_id . ' ';
    $resultsk = $connection2->query($sqlsketch);
    $sketchDataAttr = $resultsk->fetchAll();

    
    //print_r($studentData);

    $sqlpr = 'SELECT a.signature_path, b.officialName, b.image_240 FROM pupilsightStaff AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.is_principle = 1 ';
    $resultpr = $connection2->query($sqlpr);
    $prData = $resultpr->fetch();

    if(!empty($mappingIds)){
        $cuid = $_SESSION[$guid]['pupilsightPersonID'];
        foreach($mappingIds as $pupilsightMappingID){

            $sqlmap = 'SELECT * FROM pupilsightProgramClassSectionMapping WHERE pupilsightMappingID = '.$pupilsightMappingID.' ';
            $resultmap = $connection2->query($sqlmap);
            $mappingData = $resultmap->fetch();

            if(!empty($studentIds)){
                $sqls = 'SELECT fr.relationship, ft.officialName as parent_name, ft.email as parent_email, ft.phone1 as parent_phone, b.officialName, b.pupilsightPersonID, b.gender, b.dob, b.address1, b.admission_no, b.roll_no, b.cbse_reg_no, d.name as classname, e.name as sectionname, e.pupilsightPersonIDTutor, c.pupilsightSchoolYearID, c.pupilsightProgramID, c.pupilsightYearGroupID, c.pupilsightRollGroupID FROM pupilsightPerson AS b LEFT JOIN pupilsightStudentEnrolment AS c ON b.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS d ON c.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS e ON c.pupilsightRollGroupID = e.pupilsightRollGroupID LEFT JOIN pupilsightFamilyRelationship AS fr ON b.pupilsightPersonID = fr.pupilsightPersonID2 LEFT JOIN pupilsightPerson AS ft ON fr.pupilsightPersonID1 = ft.pupilsightPersonID  WHERE b.pupilsightPersonID IN ('.$studentIds.') AND c.pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND c.pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND c.pupilsightYearGroupID = '.$mappingData['pupilsightYearGroupID'].' AND c.pupilsightRollGroupID = '.$mappingData['pupilsightRollGroupID'].' GROUP BY b.pupilsightPersonID';
            } else {
                $sqls = 'SELECT fr.relationship, ft.officialName as parent_name, ft.email as parent_email, ft.phone1 as parent_phone, b.officialName, b.pupilsightPersonID, b.gender, b.dob, b.address1, b.admission_no, b.roll_no, b.cbse_reg_no, d.name as classname, e.name as sectionname, e.pupilsightPersonIDTutor, c.pupilsightSchoolYearID, c.pupilsightProgramID, c.pupilsightYearGroupID, c.pupilsightRollGroupID FROM pupilsightPerson AS b LEFT JOIN pupilsightStudentEnrolment AS c ON b.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS d ON c.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS e ON c.pupilsightRollGroupID = e.pupilsightRollGroupID LEFT JOIN pupilsightFamilyRelationship AS fr ON b.pupilsightPersonID = fr.pupilsightPersonID2 LEFT JOIN pupilsightPerson AS ft ON fr.pupilsightPersonID1 = ft.pupilsightPersonID  WHERE c.pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND c.pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND c.pupilsightYearGroupID = '.$mappingData['pupilsightYearGroupID'].' AND c.pupilsightRollGroupID = '.$mappingData['pupilsightRollGroupID'].' GROUP BY b.pupilsightPersonID';
            }
           


            $results = $connection2->query($sqls);
            $studentData = $results->fetchAll();

            if (!empty($studentData)) {

                $sqlchk = 'SELECT * FROM examinationReportTemplateSketchGenerate WHERE pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND pupilsightYearGroupID = '.$mappingData['pupilsightYearGroupID'].' AND pupilsightRollGroupID = '.$mappingData['pupilsightRollGroupID'].' AND sketch_id = '.$sketch_id.' ';
                $resultchk = $connection2->query($sqlchk);
                $sktGenData = $resultchk->fetch();

                if(!empty($sktGenData)){
                    $datadel = array('id' => $sktGenData['id']);
                    $sqldel = 'DELETE FROM  examinationReportTemplateSketchGenerate WHERE id=:id';
                    $resultdel = $connection2->prepare($sqldel);
                    $resultdel->execute($datadel);

                    $datadel1 = array('sketch_id' => $sketch_id, 'sketch_generate_id' => $sktGenData['id']);
                    $sqldel1 = 'DELETE FROM  examinationReportTemplateSketchData WHERE sketch_id=:sketch_id AND sketch_generate_id=:sketch_generate_id ';
                    $resultdel1 = $connection2->prepare($sqldel1);
                    $resultdel1->execute($datadel1);
                } 

                $dataf = array('sketch_id' => $sketch_id, 'pupilsightSchoolYearID' => $mappingData['pupilsightSchoolYearID'], 'pupilsightProgramID' => $mappingData['pupilsightProgramID'], 'pupilsightYearGroupID' => $mappingData['pupilsightYearGroupID'], 'pupilsightRollGroupID' => $mappingData['pupilsightRollGroupID'], 'created_by' => $cuid);
                $sqlf = 'INSERT INTO examinationReportTemplateSketchGenerate SET sketch_id=:sketch_id, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID, created_by=:created_by';
                $resultf = $connection2->prepare($sqlf);
                $resultf->execute($dataf);

                $sketch_generate_id = $connection2->lastInsertID();

               
                //die();
                // $data = array('erta_id' => $erta_id);
                // $sqldel = 'DELETE FROM examinationReportTemplateFormulaAttributeMapping WHERE erta_id=:erta_id';
                // $resultdel = $connection2->prepare($sqldel);
                // $resultdel->execute($data);
        
                foreach ($studentData as $td) {
        
                    $relationship = $td['relationship'];
                    if ($relationship == 'Father') {
                        $father_name = $td['parent_name'];
                        $father_email = $td['parent_email'];
                        $father_phone = $td['parent_phone'];
        
                        $sqlm = 'SELECT ft.officialName as parent_name, ft.email as parent_email, ft.phone1 as parent_phone FROM pupilsightFamilyRelationship AS fr LEFT JOIN pupilsightPerson AS ft ON fr.pupilsightPersonID1 = ft.pupilsightPersonID  WHERE fr.pupilsightPersonID2 = ' . $td['pupilsightPersonID'] . ' AND fr.relationship = "Mother" ';
                        $resultm = $connection2->query($sqlm);
                        $pardata = $resultm->fetch();
                        if (!empty($pardata)) {
                            $mother_name = $pardata['parent_name'];
                            $mother_email = $pardata['parent_email'];
                            $mother_phone = $pardata['parent_phone'];
                        } else {
                            $mother_name = '';
                            $mother_email = '';
                            $mother_phone = '';
                        }
                    }
        
                    if ($relationship == 'Mother') {
                        $mother_name = $td['parent_name'];
                        $mother_email = $td['parent_email'];
                        $mother_phone = $td['parent_phone'];
        
                        $sqlm = 'SELECT ft.officialName as parent_name, ft.email as parent_email, ft.phone1 as parent_phone FROM pupilsightFamilyRelationship AS fr LEFT JOIN pupilsightPerson AS ft ON fr.pupilsightPersonID1 = ft.pupilsightPersonID  WHERE fr.pupilsightPersonID2 = ' . $td['pupilsightPersonID'] . ' AND fr.relationship = "Father" ';
                        $resultm = $connection2->query($sqlm);
                        $pardata = $resultm->fetch();
                        if (!empty($pardata)) {
                            $father_name = $pardata['parent_name'];
                            $father_email = $pardata['parent_email'];
                            $father_phone = $pardata['parent_phone'];
                        } else {
                            $father_name = '';
                            $father_email = '';
                            $father_phone = '';
                        }
                    }
        
                    $reportcolumn = array(
                        'student_id' => $td['pupilsightPersonID'],
                        'student_name' => $td['officialName'],
                        'student_gender' => $td['gender'],
                        'student_dob' => date('d/m/Y', strtotime($td['dob'])),
                        'student_address' => $td['address1'],
                        'admission_no' => $td['admission_no'],
                        'roll_no' => $td['roll_no'],
                        'cbse_reg_no' => $td['cbse_reg_no'],
                        'father_name' => $father_name,
                        'father_email' => $father_email,
                        'father_phone' => $father_phone,
                        'mother_name' => $mother_name,
                        'mother_email' => $mother_email,
                        'mother_phone' => $mother_phone,
                        'class' => $td['classname'],
                        'section' => $td['sectionname']
                    );
        
                    $dataarr = array();
                    $gmsarr = array();
                    $maxMarksArr = array();
                    $totalMarksArr = array();
                    $totalMaxMarksArr = array();
        
                    $getSketchData = array();
                    try {
                        foreach ($sketchDataAttr as $sd) {
                            if ($sd['attribute_type'] == 'Class Teacher') {
                                $teacherId = $td['pupilsightPersonIDTutor'];
                                if (!empty($teacherId)) {
                                    $sqlpr = 'SELECT b.signature_path, a.officialName, a.image_240 FROM pupilsightPerson AS a LEFT JOIN pupilsightStaff AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightPersonID = ' . $teacherId . ' ';
                                    $resultpr = $connection2->query($sqlpr);
                                    $ctData = $resultpr->fetch();
                                    if ($sd['report_column_word'] == 'class_teacher_name') {
                                        $dataarr[$sd['attribute_name']] = $ctData['officialName'];
                                    }
                                    if ($sd['report_column_word'] == 'class_teacher_signature') {
                                        $dataarr[$sd['attribute_name'] . '#signature'] = $ctData['signature_path'];
                                    }
                                    if ($sd['report_column_word'] == 'class_teacher_photo') {
                                        $dataarr[$sd['attribute_name'] . '#photo'] = $ctData['image_240'];
                                    }
                                }
                            }
        
                            if ($sd['attribute_type'] == 'Principal') {
        
                                if (!empty($prData)) {
                                    if ($sd['report_column_word'] == 'principle_name') {
                                        $dataarr[$sd['attribute_name']] = $prData['officialName'];
                                    }
                                    if ($sd['report_column_word'] == 'principle_signature') {
                                        $dataarr[$sd['attribute_name'] . '#signature'] = $prData['signature_path'];
                                    }
                                    if ($sd['report_column_word'] == 'principle_photo') {
                                        $dataarr[$sd['attribute_name'] . '#photo'] = $prData['image_240'];
                                    }
                                }
                            }
        
                            if (array_key_exists($sd['report_column_word'], $reportcolumn)) {
                                $dataarr[$sd['attribute_name']] = $reportcolumn[$sd['report_column_word']];
                            }
        
                            if ($sd['attribute_type'] == 'Marks' && $sd['attribute_category'] == 'Test') {
        
                                $sqlp = 'SELECT a.*, b.name FROM examinationReportTemplatePluginAttributeMapping AS a LEFT JOIN examinationReportTemplatePlugin AS b ON a.plugin_id = b.id WHERE a.erta_id = ' . $sd['erta_id'] . ' ';
                                $resultp = $connection2->query($sqlp);
                                $plugindata = $resultp->fetch();
        
                                if ($plugindata['name'] == 'Round') {
                                    $roundvalue = $plugindata['plugin_val'];
                                } else {
                                    $roundvalue = '';
                                }
        
                                $sqlf = 'SELECT a.*, b.name FROM examinationReportTemplateFormulaAttributeMapping AS a LEFT JOIN examinationReportTemplateFormula AS b ON a.formula_id = b.id WHERE a.erta_id = ' . $sd['erta_id'] . ' ';
                                $resultf = $connection2->query($sqlf);
                                $formuladata = $resultf->fetch();
                                // print_r($formuladata);
                                if ($formuladata['name'] == 'Scale') {
                                    $scalevalue = $formuladata['formula_val'];
                                } else {
                                    $scalevalue = '';
                                }
        
                                if (!empty($sd['test_master_id'])) {
                                    $testMasterId = $sd['test_master_id'];
                                    $sqlt = 'SELECT * FROM examinationTest WHERE test_master_id = ' . $testMasterId . ' ';
                                    $resultt = $connection2->query($sqlt);
                                    $testdata = $resultt->fetchAll();
                                    //$testId = explode(',',$testdata['testId']);
        
        
                                    foreach ($testdata as $testId) {
                                        $getMarksNew = '';
                                        $test_id = $testId['id'];
                                        $test_name = $testId['name'];
        
                                        //$subsql = 'SELECT a.pupilsightDepartmentID, a.max_marks, a.skill_configure, a.gradeSystemId, b.name AS subject FROM examinationSubjectToTest AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN examinationTestAssignClass AS c ON a.test_id = c.test_id LEFT JOIN subjectToClassCurriculum AS d ON a.pupilsightDepartmentID = d.pupilsightDepartmentID WHERE a.test_id = ' . $test_id . '  AND a.skill_configure != "" AND c.pupilsightProgramID = ' . $td['pupilsightProgramID'] . ' AND c.pupilsightYearGroupID = ' . $td['pupilsightYearGroupID'] . ' AND c.pupilsightRollGroupID = ' . $td['pupilsightRollGroupID'] . ' AND c.pupilsightSchoolYearID = ' . $td['pupilsightSchoolYearID'] . ' AND d.pupilsightProgramID = ' . $td['pupilsightProgramID'] . ' AND d.pupilsightYearGroupID = ' . $td['pupilsightYearGroupID'] . ' AND d.pupilsightSchoolYearID = ' . $td['pupilsightSchoolYearID'] . ' GROUP BY a.pupilsightDepartmentID ORDER BY d.pos ASC ';
                                        $subsql = "CALL sketchMarksReturn(" . $test_id . "," . $td['pupilsightProgramID'] . "," . $td['pupilsightYearGroupID'] . "," . $td['pupilsightRollGroupID'] . "," . $td['pupilsightSchoolYearID'] . ");";
                                        
                                        $connPro1 = new mysqli($databaseServer, $databaseUsername, $databasePassword, $databaseName);
                                        $testdatasub = mysqli_query($connPro1, $subsql);
                                        
                                        
                                        if($testdatasub){
                                            $cnt = 1;
                                            while ($tsub = mysqli_fetch_array($testdatasub)) {
                                                $gsid = $tsub['gradeSystemId'];
                                                if ($tsub['skill_configure'] == 'Average') {
                                                    $sqlmarks = 'SELECT AVG(marks_obtained) AS getmarks FROM examinationMarksEntrybySubject WHERE pupilsightDepartmentID = ' . $tsub['pupilsightDepartmentID'] . ' AND pupilsightPersonID = ' . $td['pupilsightPersonID'] . ' AND test_id = ' . $test_id . ' ';
                                                } else if ($tsub['skill_configure'] == 'Sum') {
                                                    $sqlmarks = 'SELECT SUM(marks_obtained) AS getmarks FROM examinationMarksEntrybySubject WHERE pupilsightDepartmentID = ' . $tsub['pupilsightDepartmentID'] . ' AND pupilsightPersonID = ' . $td['pupilsightPersonID'] . ' AND test_id = ' . $test_id . ' ';
                                                } else {
                                                    $sqlmarks = 'SELECT marks_obtained AS getmarks FROM examinationMarksEntrybySubject WHERE pupilsightDepartmentID = ' . $tsub['pupilsightDepartmentID'] . ' AND pupilsightPersonID = ' . $td['pupilsightPersonID'] . ' AND test_id = ' . $test_id . ' ';
                                                }
                                                $resultmarks = $connection2->query($sqlmarks);
                                                $marksdatasub = $resultmarks->fetch();
                                                //print_r($marksdatasub);
        
                                                if (!empty($marksdatasub) && $marksdatasub['getmarks'] != '0.00') {
                                                    if (!empty($scalevalue)) {
                                                        $max_marks = $scalevalue;
                                                        if (!empty($tsub["max_marks"]) && $tsub["max_marks"] != '0.00') {
                                                            $gm = ($marksdatasub['getmarks'] / $tsub["max_marks"]) * $max_marks;
                                                        } else {
                                                            $gm = $marksdatasub['getmarks'] * $max_marks;
                                                        }
                                                        if (!empty($roundvalue)) {
                                                            $getmarks = round($gm, $roundvalue);
                                                        } else {
                                                            $getmarks = $gm;
                                                        }
                                                    } else {
                                                        $max_marks = $tsub["max_marks"];
                                                        if (!empty($roundvalue)) {
                                                            $getmarks = round($marksdatasub['getmarks'], $roundvalue);
                                                        } else {
                                                            $getmarks = $marksdatasub['getmarks'];
                                                        }
                                                    }
                                                } else {
                                                    $getmarks = '';
                                                }
        
                                                $getMarksNew = str_replace(".00","", $getmarks);
                                                $gmsarr[$tsub['pupilsightDepartmentID']][] = $getMarksNew;
        
                                                $getSketchData[$sd['attribute_type']][$sd['erta_id']][$tsub['pupilsightDepartmentID']] = $getMarksNew;
                                                // echo $sd['attribute_name'].'_'.$cnt.'_'.$tsub['subject'].'--'.$getmarks.'</br>';
        
                                                // $dataarr[$sd['attribute_name'] . '_' . $cnt . '_' . $tsub['subject']] = $getmarks;
        
                                                $dataarr[$sd['attribute_name'] . '_' . $cnt . '_' . $tsub['subject']] = $getMarksNew;
                                                $cnt++;
                                            }
                                        }
                                        $connPro1->close();
                                    }
                                }
                            }
        
                            if ($sd['attribute_type'] == 'Max Marks' && $sd['attribute_category'] == 'Test') {
        
                                $sqlp = 'SELECT a.*, b.name FROM examinationReportTemplatePluginAttributeMapping AS a LEFT JOIN examinationReportTemplatePlugin AS b ON a.plugin_id = b.id WHERE a.erta_id = ' . $sd['erta_id'] . ' ';
                                $resultp = $connection2->query($sqlp);
                                $plugindata = $resultp->fetch();
        
                                if ($plugindata['name'] == 'Round') {
                                    $roundvalue = $plugindata['plugin_val'];
                                } else {
                                    $roundvalue = '';
                                }
        
                                $sqlf = 'SELECT a.*, b.name FROM examinationReportTemplateFormulaAttributeMapping AS a LEFT JOIN examinationReportTemplateFormula AS b ON a.formula_id = b.id WHERE a.erta_id = ' . $sd['erta_id'] . ' ';
                                $resultf = $connection2->query($sqlf);
                                $formuladata = $resultf->fetch();
                                // print_r($formuladata);
                                if ($formuladata['name'] == 'Scale') {
                                    $scalevalue = $formuladata['formula_val'];
                                } else {
                                    $scalevalue = '';
                                }
        
                                if (!empty($sd['test_master_id'])) {
                                    $testMasterId = $sd['test_master_id'];
                                    $sqlt = 'SELECT GROUP_CONCAT(id) AS testId FROM examinationTest WHERE test_master_id = ' . $testMasterId . ' ';
                                    $resultt = $connection2->query($sqlt);
                                    $testdata = $resultt->fetch();
                                    $testId = explode(',', $testdata['testId']);
        
                                    foreach ($testId as $test_id) {
                                        $newMaxMarks = '';
        
                                        $subsql = "CALL sketchMarksReturn(" . $test_id . "," . $td['pupilsightProgramID'] . "," . $td['pupilsightYearGroupID'] . "," . $td['pupilsightRollGroupID'] . "," . $td['pupilsightSchoolYearID'] . ");";
                                        $connPro2 = new mysqli($databaseServer, $databaseUsername, $databasePassword, $databaseName);
                                        $testdatasub = mysqli_query($connPro2, $subsql);
        
                                        if($testdatasub){
                                            $cnt = 1;
                                            while ($tsub = mysqli_fetch_array($testdatasub)) {
        
                                                if (!empty($scalevalue)) {
                                                    $max_marks = $scalevalue;
                                                } else {
                                                    $max_marks = $tsub["max_marks"];
                                                }
        
                                                $max_marks = str_replace(".00","", $max_marks); 
                                                $newMaxMarks = $max_marks;
        
                                                $maxMarksArr[$tsub['pupilsightDepartmentID']][] = $newMaxMarks;
        
                                                $getSketchData[$sd['attribute_type']][$sd['erta_id']][$tsub['pupilsightDepartmentID']] = $newMaxMarks;
        
                                                $dataarr[$sd['attribute_name'] . '_' . $cnt . '_' . $tsub['subject']] = $max_marks;
                                                $cnt++;
                                            }
                                        }
                                        $connPro2->close();
                                    }
                                }
                            }
        
                            if ($sd['attribute_type'] == 'Grade' && $sd['attribute_category'] == 'Test') {
        
                                $sqlp = 'SELECT a.*, b.name FROM examinationReportTemplatePluginAttributeMapping AS a LEFT JOIN examinationReportTemplatePlugin AS b ON a.plugin_id = b.id WHERE a.erta_id = ' . $sd['erta_id'] . ' ';
                                $resultp = $connection2->query($sqlp);
                                $plugindata = $resultp->fetch();
        
                                if ($plugindata['name'] == 'Round') {
                                    $roundvalue = $plugindata['plugin_val'];
                                } else {
                                    $roundvalue = '';
                                }
        
                                $sqlf = 'SELECT a.*, b.name FROM examinationReportTemplateFormulaAttributeMapping AS a LEFT JOIN examinationReportTemplateFormula AS b ON a.formula_id = b.id WHERE a.erta_id = ' . $sd['erta_id'] . ' ';
                                $resultf = $connection2->query($sqlf);
                                $formuladata = $resultf->fetch();
                                // print_r($formuladata);
                                if ($formuladata['name'] == 'Scale') {
                                    $scalevalue = $formuladata['formula_val'];
                                } else {
                                    $scalevalue = '';
                                }
        
        
                                if (!empty($sd['test_master_id'])) {
                                    $testMasterId = $sd['test_master_id'];
                                    $sqlt = 'SELECT GROUP_CONCAT(id) AS testId FROM examinationTest WHERE test_master_id = ' . $testMasterId . ' ';
                                    $resultt = $connection2->query($sqlt);
                                    $testdata = $resultt->fetch();
                                    $testId = explode(',', $testdata['testId']);
        
                                    foreach ($testId as $test_id) {
                                        
                                        $subsql = "CALL sketchMarksReturn(" . $test_id . "," . $td['pupilsightProgramID'] . "," . $td['pupilsightYearGroupID'] . "," . $td['pupilsightRollGroupID'] . "," . $td['pupilsightSchoolYearID'] . ");";
                                        $connPro3 = new mysqli($databaseServer, $databaseUsername, $databasePassword, $databaseName);
                                        $testdatasub = mysqli_query($connPro3, $subsql);
        
                                        if($testdatasub){
                                            $cnt = 1;
                                            while ($tsub = mysqli_fetch_array($testdatasub)) {
                                                $gsid = $tsub['gradeSystemId'];
        
                                                $sqlmarks = 'SELECT gradeId FROM examinationMarksEntrybySubject WHERE pupilsightDepartmentID = ' . $tsub['pupilsightDepartmentID'] . ' AND pupilsightPersonID = ' . $td['pupilsightPersonID'] . ' AND test_id = ' . $test_id . ' ';
                                                $resultmarks = $connection2->query($sqlmarks);
                                                $marksdatasub = $resultmarks->fetch();
        
                                                if (!empty($marksdatasub)) {
                                                    $sqlg = 'SELECT grade_name FROM examinationGradeSystemConfiguration  WHERE id="' . $marksdatasub['gradeId'] . '" ';
                                                    $resultg = $connection2->query($sqlg);
                                                    $grade = $resultg->fetch();
                                                    $gradename = $grade['grade_name'];
                                                } else {
                                                    $gradename = '';
                                                }
        
        
                                                $getSketchData[$sd['attribute_type']][$sd['erta_id']][$tsub['pupilsightDepartmentID']] = $gradename;
        
                                                $dataarr[$sd['attribute_name'] . '_' . $cnt . '_' . $tsub['subject']] = $gradename;
                                                $cnt++;
                                            }
                                        }
                                        $connPro3->close();
        
                                    }
                                }
                            }
        
                            if ($sd['attribute_type'] == 'Marks' && $sd['attribute_category'] == 'Computed') {
        
                                $finalFormuala = $sd['final_formula'];
        
                                $sqlp = 'SELECT a.*, b.name FROM examinationReportTemplatePluginAttributeMapping AS a LEFT JOIN examinationReportTemplatePlugin AS b ON a.plugin_id = b.id WHERE a.erta_id = ' . $sd['erta_id'] . ' ';
                                $resultp = $connection2->query($sqlp);
                                $plugindata = $resultp->fetch();
        
                                if ($plugindata['name'] == 'Round') {
                                    $roundvalue = $plugindata['plugin_val'];
                                } else {
                                    $roundvalue = '';
                                }
        
                                $sqlf = 'SELECT a.*, b.name FROM examinationReportTemplateFormulaAttributeMapping AS a LEFT JOIN examinationReportTemplateFormula AS b ON a.formula_id = b.id WHERE a.erta_id = ' . $sd['erta_id'] . ' ';
                                $resultf = $connection2->query($sqlf);
                                $formuladata = $resultf->fetch();
                                // print_r($formuladata);
                                if ($formuladata['name'] == 'Scale') {
                                    $scalevalue = $formuladata['formula_val'];
                                } else {
                                    $scalevalue = '';
                                }
        
                                $attrIds = $sd['attr_ids'];
        
                                $i = 1;
                                foreach ($gmsarr as $k => $gs) {
                                    $sqlsub = 'SELECT name AS subject FROM  pupilsightDepartment WHERE pupilsightDepartmentID = ' . $k . '  ';
                                    $resultsubname = $connection2->query($sqlsub);
                                    $subnamdata = $resultsubname->fetch();
        
                                    if ($finalFormuala == 'Sum') {
                                        $getComMarks = array_sum($gs);
                                    }
        
                                    if ($finalFormuala == 'Average') {
                                        $getComMarks = array_sum($gs) / count($gs);
                                    }
        
                                    $getComMarks = str_replace(".00","",$getComMarks);
        
                                    $totalMarksArr[$k] = $getComMarks;
        
                                    $getSketchData[$sd['attribute_type']][$sd['erta_id']][$k] = $getComMarks;
        
                                    $dataarr[$sd['attribute_name'] . '_' . $i . '_' . $subnamdata['subject']] = $getComMarks;
                                    $i++;
                                }
                            }
        
                            if ($sd['attribute_type'] == 'Max Marks' && $sd['attribute_category'] == 'Computed') {
        
                                $finalFormuala = $sd['final_formula'];
        
                                $sqlp = 'SELECT a.*, b.name FROM examinationReportTemplatePluginAttributeMapping AS a LEFT JOIN examinationReportTemplatePlugin AS b ON a.plugin_id = b.id WHERE a.erta_id = ' . $sd['erta_id'] . ' ';
                                $resultp = $connection2->query($sqlp);
                                $plugindata = $resultp->fetch();
        
                                if ($plugindata['name'] == 'Round') {
                                    $roundvalue = $plugindata['plugin_val'];
                                } else {
                                    $roundvalue = '';
                                }
        
                                $sqlf = 'SELECT a.*, b.name FROM examinationReportTemplateFormulaAttributeMapping AS a LEFT JOIN examinationReportTemplateFormula AS b ON a.formula_id = b.id WHERE a.erta_id = ' . $sd['erta_id'] . ' ';
                                $resultf = $connection2->query($sqlf);
                                $formuladata = $resultf->fetch();
                                // print_r($formuladata);
                                if ($formuladata['name'] == 'Scale') {
                                    $scalevalue = $formuladata['formula_val'];
                                } else {
                                    $scalevalue = '';
                                }
        
                                $attrIds = $sd['attr_ids'];
        
                                $i = 1;
                                foreach ($maxMarksArr as $k => $gs) {
                                    $sqlsub = 'SELECT name AS subject FROM  pupilsightDepartment WHERE pupilsightDepartmentID = ' . $k . '  ';
                                    $resultsubname = $connection2->query($sqlsub);
                                    $subnamdata = $resultsubname->fetch();
        
                                    if ($finalFormuala == 'Sum') {
                                        $getComMaxMarks = array_sum($gs);
                                    }
        
                                    if ($finalFormuala == 'Average') {
                                        $getComMaxMarks = array_sum($gs) / count($gs);
                                    }
        
                                    $totalMaxMarksArr[$k] = $getComMaxMarks;
        
                                    $getSketchData[$sd['attribute_type']][$sd['erta_id']][$k] = $getComMaxMarks;
        
                                    $dataarr[$sd['attribute_name'] . '_' . $i . '_' . $subnamdata['subject']] = $getComMaxMarks;
                                    $i++;
                                }
                            }
        
                            if ($sd['attribute_type'] == 'Grade' && $sd['attribute_category'] == 'Computed') {
        
                                $finalFormuala = $sd['final_formula'];
                                $supported_attribute = $sd['supported_attribute'];
                                $grade_id = $sd['grade_id'];
        
                                $gradeMaxMarks = $getSketchData['Max Marks'][$supported_attribute];
                                $gradeMarks = $getSketchData['Marks'][$sd['attr_ids']];
        
                                $i = 1;
                                foreach ($gradeMarks as $d => $grm) {
                                    //echo $grm.'</br>';
                                    $sqlsub = 'SELECT name AS subject FROM  pupilsightDepartment WHERE pupilsightDepartmentID = ' . $d . '  ';
                                    $resultsubname = $connection2->query($sqlsub);
                                    $subnamdata = $resultsubname->fetch();
        
                                    $gmn = ($grm / 100) * $gradeMaxMarks[$d];
                                    if (!empty($getmarks)) {
                                        $sqlg = 'SELECT grade_name FROM examinationGradeSystemConfiguration  WHERE gradeSystemId="' . $grade_id . '" AND  (' . $gmn . ' BETWEEN `lower_limit` AND `upper_limit`)';
                                        $resultg = $connection2->query($sqlg);
                                        $grade = $resultg->fetch();
                                        $gradename = $grade['grade_name'];
                                    } else {
                                        $gradename = '';
                                    }
                                    $dataarr[$sd['attribute_name'] . '_' . $i . '_' . $subnamdata['subject']] = $gradename;
                                    $i++;
                                }
                            }
                        }
                    } catch (Exception $ex) {
                        print_r($ex);
                    }
        
                    // echo '<pre>';
                    // print_r($getSketchData);
                    // echo '</pre>';
        
                    // echo '<pre>';
                    // print_r($gradeMarks);
                    // echo '</pre>';
        
                    // echo '<pre>';
                    // print_r($totalMaxMarksArr);
                    // echo '</pre>';
        
                    //  echo '<pre>';
                    //  print_r($dataarr);
                    //  echo '</pre>';
        
                    //  die();
        
                    if (!empty($dataarr)) {
                        $count = 0;
                        $appendFlag = FALSE;
                        $sqlInsert = "INSERT INTO examinationReportTemplateSketchData (sketch_id,sketch_generate_id, pupilsightPersonID, attribute_name, attribute_value, attribute_type)  VALUES ";
        
                        foreach ($dataarr as $key => $value) {
                            $count++;
                            if ($count > 200) {
                                $appendFlag = FALSE;
                                $count = 0;
                                $sqlInsert .= ";INSERT INTO examinationReportTemplateSketchData (sketch_id, sketch_generate_id, pupilsightPersonID, attribute_name, attribute_value, attribute_type)  VALUES ";
                            }
        
                            if ($appendFlag) {
                                $sqlInsert .= ",";
                            }
        
                            $keychk = substr($key, strpos($key, "#") + 1);
                            if ($keychk == 'signature') {
                                $attrType = 'signature';
                            } else if ($keychk == 'photo') {
                                $attrType = 'photo';
                            } else {
                                $attrType = 'text';
                            }
                            $sqlInsert .= '(' . $sketch_id . ',' .$sketch_generate_id. ',' . $td['pupilsightPersonID'] . ',"' . $key . '","' . $value . '","' . $attrType . '")';
                            $appendFlag = TRUE;
                        }
                        $sqlInsert .= ";";
                        $sqlInsert = rtrim($sqlInsert, ", ");
                        //echo $sqlInsert;
                        //die();
                        //$connection2->query($sqlsub);
                        try {
                            $connInsert = new mysqli($databaseServer, $databaseUsername, $databasePassword, $databaseName);
                            $connInsert->query($sqlInsert);
                            $connInsert->close();
                        } catch (Exception $ex) {
                            print_r($ex);
                        }
        
                        //echo $sqlInsert;
                    }
                }
               
                //header("Location: {$URL}");  
            }
            echo 'done';
            die();
        }
    }


    
}

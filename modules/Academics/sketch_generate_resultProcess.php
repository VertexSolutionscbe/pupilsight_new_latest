<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
include '../../db.php';

//$conn->close();


if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_attribute.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $sketch_id = $_POST['id'];
    $pupilsightSchoolYearID = $_POST['yid'];
    $pupilsightProgramID = $_POST['pid'];
    $classIds = $_POST['cid'];
    $secIds = $_POST['secid'];
    $mappingIds = explode(',', $_POST['mid']);
    $studentIds = $_POST['stid'];

    //$sketch_id = $_GET['id'];
    $sql = 'SELECT * FROM examinationReportTemplateSketch WHERE id = ' . $sketch_id . ' ';
    $result = $connection2->query($sql);
    $sketchData = $result->fetch();

    $sqlsketch = 'SELECT a.id as erta_id, a.attribute_name, a.attribute_category, a.attribute_type, a.test_master_id, a.attr_ids, a.final_formula, a.grade_id, a.supported_attribute, a.subject_type, a.subject_val_id, b.* FROM examinationReportTemplateAttributes AS a LEFT JOIN examinationReportTemplateConfiguration AS b ON a.ertc_id = b.id WHERE a.sketch_id = ' . $sketch_id . ' ';
    $resultsk = $connection2->query($sqlsketch);
    $sketchDataAttr = $resultsk->fetchAll();

    // echo '<pre>';
    // print_r($sketchDataAttr);
    // echo '</pre>';
    // die();

    $sqlpr = 'SELECT a.signature_path, b.officialName, b.image_240 FROM pupilsightStaff AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.is_principle = 1 ';
    $resultpr = $connection2->query($sqlpr);
    $prData = $resultpr->fetch();

    if (!empty($mappingIds)) {
        $cuid = $_SESSION[$guid]['pupilsightPersonID'];
        foreach ($mappingIds as $pupilsightMappingID) {

            $sqlmap = 'SELECT * FROM pupilsightProgramClassSectionMapping WHERE pupilsightMappingID = ' . $pupilsightMappingID . ' ';
            $resultmap = $connection2->query($sqlmap);
            $mappingData = $resultmap->fetch();

            $sqlchk = 'SELECT * FROM examinationReportTemplateSketchGenerate WHERE pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND pupilsightYearGroupID = ' . $mappingData['pupilsightYearGroupID'] . ' AND pupilsightRollGroupID = ' . $mappingData['pupilsightRollGroupID'] . ' AND sketch_id = ' . $sketch_id . ' ';
            $resultchk = $connection2->query($sqlchk);
            $sktGenData = $resultchk->fetch();

            if (!empty($sktGenData)) {
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

            $dataarr = array();
            $gmsarr = array();
            $maxMarksArr = array();
            $totalMarksArr = array();
            $totalMaxMarksArr = array();

            $getSketchData = array();
            $subTeacherBySubject = array();
            try {
                foreach ($sketchDataAttr as $sd) {
                    /*
                    if ($sd['attribute_type'] == 'Student' || $sd['attribute_type'] == 'Class Teacher' || $sd['attribute_type'] == 'Principal') {
                        $studentDetails = getStudentDetails($connection2, $studentIds, $mappingData,  $sd['attribute_type']);
                        foreach($studentDetails as $k => $std){
                            if ($sd['attribute_type'] == 'Student' && array_key_exists($sd['report_column_word'], $std)) {
                                $dataarr[$k][$sd['attribute_name']] = $std[$sd['report_column_word']];
                            }

                            if($sd['attribute_type'] == 'Class Teacher'){
                                if ($sd['report_column_word'] == 'class_teacher_name' && !empty($std['clt_name']) ) {
                                    $dataarr[$k][$sd['attribute_name']] = $std['clt_name'];
                                }
                                if ($sd['report_column_word'] == 'class_teacher_signature' && !empty($std['clt_signature'])) {
                                    $dataarr[$k][$sd['attribute_name'] . '#signature'] = $std['clt_signature'];
                                }
                                if ($sd['report_column_word'] == 'class_teacher_photo' && !empty($std['clt_photo'])) {
                                    $dataarr[$k][$sd['attribute_name'] . '#photo'] = $std['clt_photo'];
                                }
                            }

                            if ($sd['attribute_type'] == 'Principal') {

                                if (!empty($prData)) {
                                    if ($sd['report_column_word'] == 'principle_name') {
                                        $dataarr[$k][$sd['attribute_name']] = $prData['officialName'];
                                    }
                                    if ($sd['report_column_word'] == 'principle_signature') {
                                        $dataarr[$k][$sd['attribute_name'] . '#signature'] = $prData['signature_path'];
                                    }
                                    if ($sd['report_column_word'] == 'principle_photo') {
                                        $dataarr[$k][$sd['attribute_name'] . '#photo'] = $prData['image_240'];
                                    }
                                }
                            }
                        }
                    }

                    if ($sd['attribute_type'] == 'Subject' && $sd['attribute_category'] == 'Test') {
                        $subjectNames = getSubjectNames($connection2, $sd['test_master_id'], $mappingData);
                       
                        if(!empty($subjectNames)){
                            $cnt = 1;
                            foreach($subjectNames as $sub){
                                $subjectArray[$sd['attribute_name'] . '_' . $cnt ] = $sub;
                                $cnt++;
                            }
                        }
                    }

                    if ($sd['attribute_type'] == 'Subject Teacher' && $sd['attribute_category'] == 'Entity') {
                        $subjectTeachers = getSubjectTeachers($connection2, $sd['subject_val_id'], $sd['subject_type'], $mappingData);
                                
                        $cnt = 1;
                        if(!empty($subjectTeachers)){
                            foreach($subjectTeachers as $sdata){
                                // $dataarr[$sd['attribute_name'] . '_' . $cnt . '_' . $sdata['subject_display_name']] = $sdata['sub_teacher'];
                                if ($sd['report_column_word'] == 'subject_teacher_name') {
                                    $subTeacherArray[$sd['attribute_name'] . '_' . $cnt . '_' . $sdata['subject_display_name']] = $sdata['sub_teacher'];
                                }
                                if ($sd['report_column_word'] == 'subject_teacher_signature') {
                                    $subTeacherArray[$sd['attribute_name'] . '_' . $cnt . '_' . $sdata['subject_display_name']. '#signature'] = $sdata['signature_path'];
                                }
                                if ($sd['report_column_word'] == 'subject_teacher_photo') {
                                    $subTeacherArray[$sd['attribute_name'] . '_' . $cnt . '_' . $sdata['subject_display_name']. '#photo'] = $sdata['image_240'];
                                }
                                $cnt++;
                            }
                        }
                    }
                    */

                    if ($sd['attribute_type'] == 'Marks' && $sd['attribute_category'] == 'Test') {
                        $erta_id =  $sd['erta_id'];
                        $sqlp = 'SELECT a.*, b.name FROM examinationReportTemplatePluginAttributeMapping AS a LEFT JOIN examinationReportTemplatePlugin AS b ON a.plugin_id = b.id WHERE a.erta_id = ' . $erta_id . ' ';
                        $resultp = $connection2->query($sqlp);
                        $plugindata = $resultp->fetch();

                        if ($plugindata['name'] == 'Round') {
                            $roundvalue = $plugindata['plugin_val'];
                        } else {
                            $roundvalue = '';
                        }

                        $sqlf = 'SELECT a.*, b.name FROM examinationReportTemplateFormulaAttributeMapping AS a LEFT JOIN examinationReportTemplateFormula AS b ON a.formula_id = b.id WHERE a.erta_id = ' . $erta_id . ' ';
                        $resultf = $connection2->query($sqlf);
                        $formuladata = $resultf->fetch();
                        // print_r($formuladata);
                        if ($formuladata['name'] == 'Scale') {
                            $scalevalue = $formuladata['formula_val'];
                        } else {
                            $scalevalue = '';
                        }

                        $getStudentMarks = getStudentMarks($connection2, $conn, $sd['test_master_id'], $sd['erta_id'], $studentIds, $mappingData, $sd['final_formula']);
                        // echo '<pre>';
                        // print_r($getStudentMarks);
                        // echo '</pre>';

                        // foreach($getStudentMarks as ){

                        // }
                    }
                    /*
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
                                        $testMaxMarks = $tsub["max_marks"];

                                        if($tsub['skill_configure'] == 'Average'){
                                            $sqlmarks = 'SELECT AVG(marks_obtained) AS getmarks FROM examinationMarksEntrybySubject WHERE pupilsightDepartmentID = '.$tsub['pupilsightDepartmentID'].' AND pupilsightPersonID = '.$td['pupilsightPersonID'].' AND test_id = '.$test_id.' ';
                                            $resultmarks = $connection2->query($sqlmarks);
                                            $marksdatasub = $resultmarks->fetch();

                                            if(!empty($scalevalue)){
                                                $max_marks = $scalevalue;
                                                $gm = ($marksdatasub['getmarks'] / $tsub["max_marks"]) * $max_marks;
                                                if(!empty($roundvalue)){
                                                    $getmarks = round($gm, $roundvalue);
                                                } else {
                                                    $getmarks = $gm;
                                                }
                                            } else {
                                                $max_marks = $tsub["max_marks"];
                                                if(!empty($roundvalue)){
                                                    $getmarks = round($marksdatasub['getmarks'], $roundvalue);
                                                } else {
                                                    $getmarks = $marksdatasub['getmarks'];
                                                }
                                            }

                                            if(!empty($getmarks)){
                                                $mrks = ($getmarks / $testMaxMarks) * 100;
                                                $sqlg = 'SELECT grade_name FROM examinationGradeSystemConfiguration  WHERE gradeSystemId="'.$gsid.'" AND  ('.$mrks.' BETWEEN `lower_limit` AND `upper_limit`)';
                                                $resultg = $connection2->query($sqlg);
                                                $grade = $resultg->fetch();
                                                $gradename = $grade['grade_name'];
                                            } else {
                                                $gradename = '';
                                            }
                                        } else if($tsub['skill_configure'] == 'Sum'){
                                            $sqlmarks = 'SELECT SUM(marks_obtained) AS getmarks FROM examinationMarksEntrybySubject WHERE pupilsightDepartmentID = '.$tsub['pupilsightDepartmentID'].' AND pupilsightPersonID = '.$td['pupilsightPersonID'].' AND test_id = '.$test_id.' ';
                                            $resultmarks = $connection2->query($sqlmarks);
                                            $marksdatasub = $resultmarks->fetch();

                                            if(!empty($scalevalue)){
                                                $max_marks = $scalevalue;
                                                $gm = ($marksdatasub['getmarks'] / $tsub["max_marks"]) * $max_marks;
                                                if(!empty($roundvalue)){
                                                    $getmarks = round($gm, $roundvalue);
                                                } else {
                                                    $getmarks = $gm;
                                                }
                                            } else {
                                                $max_marks = $tsub["max_marks"];
                                                if(!empty($roundvalue)){
                                                    $getmarks = round($marksdatasub['getmarks'], $roundvalue);
                                                } else {
                                                    $getmarks = $marksdatasub['getmarks'];
                                                }
                                            }

                                            if(!empty($getmarks)){
                                                $mrks = ($getmarks / $testMaxMarks) * 100;
                                                $sqlg = 'SELECT grade_name FROM examinationGradeSystemConfiguration  WHERE gradeSystemId="'.$gsid.'" AND  ('.$mrks.' BETWEEN `lower_limit` AND `upper_limit`)';
                                                $resultg = $connection2->query($sqlg);
                                                $grade = $resultg->fetch();
                                                $gradename = $grade['grade_name'];
                                            } else {
                                                $gradename = '';
                                            }
                                        } else {
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

                    if ($sd['attribute_type'] == 'Remarks' && $sd['attribute_category'] == 'Test') {

                        if (!empty($sd['test_master_id'])) {
                            $testMasterId = $sd['test_master_id'];
                            $sqlt = 'SELECT GROUP_CONCAT(id) AS testId FROM examinationTest WHERE test_master_id = ' . $testMasterId . ' ';
                            $resultt = $connection2->query($sqlt);
                            $testdata = $resultt->fetch();
                            $testId = explode(',', $testdata['testId']);

                            foreach ($testId as $test_id) {
                                
                                $sqlmarks = 'SELECT a.pupilsightDepartmentID, b.subject_display_name, c.remarks FROM examinationSubjectToTest AS a LEFT JOIN subjectToClassCurriculum AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN examinationMarksEntrybySubject AS c ON a.pupilsightDepartmentID = c.pupilsightDepartmentID WHERE  a.test_id = '.$test_id.'  AND b.pupilsightSchoolYearID = ' . $td['pupilsightSchoolYearID'] . ' AND b.pupilsightProgramID = ' . $td['pupilsightProgramID'] . ' AND b.pupilsightYearGroupID = ' . $td['pupilsightYearGroupID'] . ' AND  c.test_id = '.$test_id.' AND c.pupilsightPersonID = ' . $td['pupilsightPersonID'] . ' AND c.pupilsightYearGroupID = ' . $td['pupilsightYearGroupID'] . ' GROUP BY a.pupilsightDepartmentID ORDER BY b.pos ASC ';
                                $resultmarks = $connection2->query($sqlmarks);
                                $testdatasub = $resultmarks->fetchAll();

                                if($testdatasub){
                                    $cnt = 1;
                                    foreach($testdatasub as $testSubject){
                                        $dataarr[$sd['attribute_name'] . '_' . $cnt . '_' . $testSubject['subject_display_name']] = $testSubject['remarks'];
                                        $cnt++;
                                    }
                                }
                                
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
                        if(!empty($attrIds)){
                            $attributeIds = explode(',', $attrIds);
                        } else {
                            $attributeIds = array();
                        }

                        $sumArray = array();
                    
                        foreach($gmsarr as $aid => $mrksData){
                            if(!empty($attributeIds)){
                                if(in_array($aid, $attributeIds)){
                                    try{
                                        if(!empty($mrksData)){
                                            foreach ($mrksData as $k => $gs) {
                                                if ($finalFormuala == 'Sum') {
                                                    if(!empty($gs)){
                                                        $ngs = $gs;
                                                    } else {
                                                        $ngs = 0;
                                                    }
                                                    $sumArray[$k] = (isset($sumArray[$k]) ? $sumArray[$k] + $ngs : $ngs);
                                                }
                                            }
                                        } else {
                                            $sumArray[$k] = '';
                                        }
                                    } catch (Exception $ex) {
                                        //print_r($ex);
                                    }
                        
                                }
                            }
                        }

                        if(!empty($sumArray)){
                            $i = 1;
                            foreach($sumArray as $k => $mdata){
                                $sqlsub = 'SELECT name AS subject FROM  pupilsightDepartment WHERE pupilsightDepartmentID = ' . $k . '  ';
                                $resultsubname = $connection2->query($sqlsub);
                                $subnamdata = $resultsubname->fetch();

                                if ($finalFormuala == 'Sum') {
                                    $getComMarks = $mdata;
                                }
    
                                if ($finalFormuala == 'Average') {
                                    $avgKount = count($attributeIds);
                                    $getComMarks = $totSumMark / count($avgKount);
                                }

                                $getComMarks = str_replace(".00","",$getComMarks);
            
                                $totalMarksArr[$k] = $getComMarks;
    
                                $getSketchData[$sd['attribute_type']][$sd['erta_id']][$k] = $getComMarks;
    
                                $dataarr[$sd['attribute_name'] . '_' . $i . '_' . $subnamdata['subject']] = $getComMarks;
                                $i++;
                            }
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
                        if(!empty($sd['supported_attribute'])){
                            $supported_attribute = $sd['supported_attribute'];
                            $grade_id = $sd['grade_id'];
    
                            try{
                                if(!empty($getSketchData)){
                                    if(isset($getSketchData['Max Marks'][$supported_attribute])){
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
                            }   catch (Exception $ex) {
                                //print_r($ex);
                            }
                        } 
                    }

                    */
                }
            } catch (Exception $ex) {
                //print_r($ex);
            }
            echo '<pre>';
            print_r($dataarr);
            echo '</pre>';

            /*
            if (!empty($dataarr)) {
                $count = 0;
                $appendFlag = FALSE;
                $sqlInsert = "INSERT INTO examinationReportTemplateSketchData (sketch_id,sketch_generate_id, pupilsightPersonID, attribute_name, attribute_value, attribute_type)  VALUES ";

                foreach ($dataarr as $k => $value) {
                    if (!empty($subjectArray)) {
                        $newVal = array_merge($value, $subjectArray);
                    } else {
                        $newVal = $value;
                    }

                    if (!empty($subTeacherArray)) {
                        $newVal = array_merge($newVal, $subTeacherArray);
                    } else {
                        $newVal = $newVal;
                    }

                    $count++;
                    foreach ($newVal as $key => $val) {

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
                        $sqlInsert .= '(' . $sketch_id . ',' . $sketch_generate_id . ',' . $k . ',"' . $key . '","' . $val . '","' . $attrType . '")';
                        $appendFlag = TRUE;
                    }
                }
                $sqlInsert .= ";";
                $sqlInsert = rtrim($sqlInsert, ", ");
                // echo $sqlInsert;
                // die();
                //$connection2->query($sqlsub);
                try {
                    $connInsert = new mysqli($databaseServer, $databaseUsername, $databasePassword, $databaseName);
                    $connInsert->query($sqlInsert);
                    $connInsert->close();
                } catch (Exception $ex) {
                    print_r($ex);
                    //die();
                }

                //echo $sqlInsert;
            }
            */
            //header("Location: {$URL}");  

        }
        //echo 'done';
        die();
    }
}

function getStudentDetails($connection2, $studentIds, $mappingData, $attributeType)
{
    if (!empty($studentIds)) {
        $sqls = 'SELECT fr.relationship, ft.officialName as parent_name, ft.email as parent_email, ft.phone1 as parent_phone, b.officialName, b.pupilsightPersonID, b.gender, b.dob, b.address1, b.admission_no, b.roll_no, b.cbse_reg_no, d.name as classname, e.name as sectionname, e.pupilsightPersonIDTutor, c.pupilsightSchoolYearID, c.pupilsightProgramID, c.pupilsightYearGroupID, c.pupilsightRollGroupID FROM pupilsightPerson AS b LEFT JOIN pupilsightStudentEnrolment AS c ON b.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS d ON c.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS e ON c.pupilsightRollGroupID = e.pupilsightRollGroupID LEFT JOIN pupilsightFamilyRelationship AS fr ON b.pupilsightPersonID = fr.pupilsightPersonID2 LEFT JOIN pupilsightPerson AS ft ON fr.pupilsightPersonID1 = ft.pupilsightPersonID WHERE b.pupilsightPersonID IN (' . $studentIds . ') AND c.pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND c.pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND c.pupilsightYearGroupID = ' . $mappingData['pupilsightYearGroupID'] . ' AND c.pupilsightRollGroupID = ' . $mappingData['pupilsightRollGroupID'] . ' GROUP BY b.pupilsightPersonID';
    } else {
        $sqls = 'SELECT fr.relationship, ft.officialName as parent_name, ft.email as parent_email, ft.phone1 as parent_phone, b.officialName, b.pupilsightPersonID, b.gender, b.dob, b.address1, b.admission_no, b.roll_no, b.cbse_reg_no, d.name as classname, e.name as sectionname, e.pupilsightPersonIDTutor, c.pupilsightSchoolYearID, c.pupilsightProgramID, c.pupilsightYearGroupID, c.pupilsightRollGroupID FROM pupilsightPerson AS b LEFT JOIN pupilsightStudentEnrolment AS c ON b.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS d ON c.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS e ON c.pupilsightRollGroupID = e.pupilsightRollGroupID LEFT JOIN pupilsightFamilyRelationship AS fr ON b.pupilsightPersonID = fr.pupilsightPersonID2 LEFT JOIN pupilsightPerson AS ft ON fr.pupilsightPersonID1 = ft.pupilsightPersonID WHERE c.pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND c.pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND c.pupilsightYearGroupID = ' . $mappingData['pupilsightYearGroupID'] . ' AND c.pupilsightRollGroupID = ' . $mappingData['pupilsightRollGroupID'] . '  GROUP BY b.pupilsightPersonID';
    }



    $results = $connection2->query($sqls);
    $studentData = $results->fetchAll();

    $ctName = '';
    $ctSign = '';
    $ctPhoto = '';
    if ($attributeType == 'Class Teacher') {
        $sqlpr = 'SELECT ct.pupilsightPersonID, b.signature_path, a.officialName, a.image_240 FROM assign_class_teacher_section AS ct LEFT JOIN  pupilsightPerson AS a ON ct.pupilsightPersonID = a.pupilsightPersonID LEFT JOIN pupilsightStaff AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE ct.pupilsightMappingID = ' . $mappingData['pupilsightMappingID'] . ' ';
        $resultpr = $connection2->query($sqlpr);
        $ctData = $resultpr->fetch();
        $ctName = $ctData['officialName'];
        $ctSign = $ctData['signature_path'];
        $ctPhoto = $ctData['image_240'];
    }

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



        $reportcolumn[$td['pupilsightPersonID']] = array(
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
            'section' => $td['sectionname'],
            'clt_name' => $ctName,
            'clt_signature' => $ctSign,
            'clt_photo' => $ctPhoto
        );
    }
    return $reportcolumn;
}

function getSubjectNames($connection2, $testMasterId, $mappingData)
{
    $sqlt = 'SELECT test_id FROM examinationTestAssignClass WHERE test_master_id IN (' . $testMasterId . ') AND pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND pupilsightYearGroupID = ' . $mappingData['pupilsightYearGroupID'] . ' AND pupilsightRollGroupID = ' . $mappingData['pupilsightRollGroupID'] . ' ';
    $resultt = $connection2->query($sqlt);
    $testdata = $resultt->fetchAll();

    $subjectNames = array();
    foreach ($testdata as $test_id) {
        $testId = $test_id['test_id'];
        $sqlmarks = 'SELECT a.pupilsightDepartmentID, b.subject_display_name FROM examinationSubjectToTest AS a LEFT JOIN subjectToClassCurriculum AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID WHERE  a.test_id = ' . $testId . '  AND b.pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND b.pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND b.pupilsightYearGroupID = ' . $mappingData['pupilsightYearGroupID'] . ' GROUP BY a.pupilsightDepartmentID ORDER BY b.pos ASC ';
        $resultmarks = $connection2->query($sqlmarks);
        $testdatasub = $resultmarks->fetchAll();

        if (!empty($testdatasub)) {

            foreach ($testdatasub as $testSubject) {
                $subjectNames[] = $testSubject['subject_display_name'];
            }
        }
    }
    return $subjectNames;
}

function getSubjectTeachers($connection2, $subject_val_id, $subject_type, $mappingData)
{
    $subData = array();
    if (!empty($subject_type)) {
        if ($subject_type == 'Select Subject') {
            $subIds = $subject_val_id;
            if (!empty($subIds)) {
                $sql = 'SELECT c.officialName as sub_teacher, d.name, sd.subject_display_name,  s.signature_path, c.image_240 FROM assignstaff_tosubject AS a LEFT JOIN pupilsightStaff AS b ON a.pupilsightStaffID = b.pupilsightStaffID LEFT JOIN pupilsightPerson AS c ON b.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN pupilsightDepartment AS d ON a.pupilsightDepartmentID = d.pupilsightDepartmentID LEFT JOIN subjectToClassCurriculum AS sd ON a.pupilsightDepartmentID = sd.pupilsightDepartmentID LEFT JOIN pupilsightStaff AS s ON c.pupilsightPersonID = s.pupilsightPersonID WHERE d.pupilsightDepartmentID IN (' . $subIds . ') AND sd.pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND sd.pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND sd.pupilsightYearGroupID = ' . $mappingData['pupilsightYearGroupID'] . ' GROUP BY d.pupilsightDepartmentID ';
                $resultsub = $connection2->query($sql);
                $subData = $resultsub->fetchAll();
            }
        } else if ($subject_type == 'All Subject') {

            $sql = 'SELECT c.officialName as sub_teacher, d.subject_display_name, s.signature_path, c.image_240 FROM assignstaff_tosubject AS a LEFT JOIN pupilsightStaff AS b ON a.pupilsightStaffID = b.pupilsightStaffID LEFT JOIN pupilsightPerson AS c ON b.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN subjectToClassCurriculum AS d ON a.pupilsightDepartmentID = d.pupilsightDepartmentID LEFT JOIN pupilsightStaff AS s ON c.pupilsightPersonID = s.pupilsightPersonID WHERE d.pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND d.pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND d.pupilsightYearGroupID = ' . $mappingData['pupilsightYearGroupID'] . ' GROUP BY d.pupilsightDepartmentID ';
            $resultsub = $connection2->query($sql);
            $subData = $resultsub->fetchAll();
        } else {

            $sql = 'SELECT c.officialName as sub_teacher, d.subject_display_name, s.signature_path, c.image_240 FROM assignstaff_tosubject AS a LEFT JOIN pupilsightStaff AS b ON a.pupilsightStaffID = b.pupilsightStaffID LEFT JOIN pupilsightPerson AS c ON b.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN subjectToClassCurriculum AS d ON a.pupilsightDepartmentID = d.pupilsightDepartmentID LEFT JOIN pupilsightDepartment AS e ON d.pupilsightDepartmentID = e.pupilsightDepartmentID LEFT JOIN pupilsightStaff AS s ON c.pupilsightPersonID = s.pupilsightPersonID WHERE d.pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND d.pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND d.pupilsightYearGroupID = ' . $mappingData['pupilsightYearGroupID'] . ' AND e.type = "' . $subject_type . '" GROUP BY d.pupilsightDepartmentID  ';
            $resultsub = $connection2->query($sql);
            $subData = $resultsub->fetchAll();
        }
    }
    return $subData;
}

function getStudentMarks($connection2, $conn, $test_master_id, $erta_id, $studentIds, $mappingData, $final_formula)
{
    $tmID_array = explode(",", $test_master_id);
    $kountTmId = count($tmID_array);
    $testResultData = array();
    if ($kountTmId > 1) {
        foreach($tmID_array as $testMasId){
            $sqlt = 'SELECT test_id FROM examinationTestAssignClass WHERE test_master_id = ' . $testMasId . ' AND pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND pupilsightYearGroupID = ' . $mappingData['pupilsightYearGroupID'] . ' AND pupilsightRollGroupID = ' . $mappingData['pupilsightRollGroupID'] . ' ';
            $resultt = $connection2->query($sqlt);
            $testdata = $resultt->fetch();
            if (!empty($testdata)) {
                $testid = $testdata['test_id'];
                $testIDs[] = $testdata['test_id'];
                $stuResultData[] = getTestData($connection2, $testid, $studentIds, $kountTmId, $mappingData);
            }
        }
        $testResultData = runMultiple($stuResultData, $testIDs, $final_formula);
    } else {
        $sqlt = 'SELECT test_id FROM examinationTestAssignClass WHERE test_master_id = ' . $test_master_id . ' AND pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND pupilsightYearGroupID = ' . $mappingData['pupilsightYearGroupID'] . ' AND pupilsightRollGroupID = ' . $mappingData['pupilsightRollGroupID'] . ' ';
        $resultt = $connection2->query($sqlt);
        $testdata = $resultt->fetch();
        if (!empty($testdata)) {
            $testid = $testdata['test_id'];
            $testResultData = getTestData($connection2, $testid, $studentIds, $kountTmId, $mappingData);
        }
    }
    // echo '<pre>';
    // print_r($testResultData);
    // echo '</pre>';
    // die();
    return $testResultData;
}

function getTestData($connection2, $testid, $pupilsightPersonID = NULL, $kountTmId = NULL, $mappingData)
{

    $dt = array();
    try {
        $sql = "select id, pupilsightPersonID,
        group_concat(pupilsightDepartmentID) as subject, 
        group_concat(marks_obtained) as marks, 
        group_concat(marks_abex) as marks_abex,
        group_concat(skill_id) as skill,
        group_concat(gradeId) as grade_id
        from  `examinationMarksEntrybySubject` WHERE test_id = " . $testid . " ";
        if ($pupilsightPersonID) {
            $sql .= " and pupilsightPersonID IN (" . $pupilsightPersonID . ") ";
        }
        $sql .= " group by pupilsightPersonID order by pupilsightPersonID;";

        $resultt = $connection2->query($sql);
        $resultData = $resultt->fetchAll();

        if (!empty($resultData)) {
            $subjectNames = getSubjectList($connection2, $testid, $mappingData);
            $skillConfig = getSkillConfig($connection2, $testid);
            //print_r($skillConfig);
            $resultSkillData = array();
            if ($skillConfig) {
                $resultSkillData = loadSkillConfig($skillConfig, $resultData);
            }

            foreach ($resultData as $rd) {
                //$id = $col[0];
                $pupilsightPersonID = $rd['pupilsightPersonID'];
                $subject = $rd['subject'];
                $marks = $rd['marks'];
                $marks_abex = $rd['marks_abex'];
                $skill = $rd['skill'];
                $grade_id = $rd['grade_id'];

                $sub = explode(",", $subject);
                $mks = explode(",", $marks);
                $mksab = explode(",", $marks_abex);
                $sl = explode(",", $skill);
                $grd = explode(",", $grade_id);
                $len = count($sub);
                $i = 0;
                while ($i < $len) {
                    //$tmp[$sub[$i]]=;
                    $tmp = array();
                    $tmp["subject"] = $sub[$i];
                    if(isset($subjectNames[$sub[$i]])){
                        $tmp["subjectName"] = $subjectNames[$sub[$i]];
                    }
                    $tmp["marks"] = $mks[$i];
                    if (!empty($mksab[$i])) {
                        $tmp["marks_abex"] = $mksab[$i];
                    } else {
                        $tmp["marks_abex"] = '';
                    }
                    $tmp["skill"] = $sl[$i];
                    $tmp["grade_id"] = $grd[$i];

                    if ($resultSkillData) {
                        if (isset($resultSkillData[$pupilsightPersonID][$sub[$i]]["marks"])) {
                            $tmp["marks"] = $resultSkillData[$pupilsightPersonID][$sub[$i]]["marks"];
                        }
                    }

                    if ($kountTmId > 1) {
                        $dt[$testid][$pupilsightPersonID][$sub[$i]] = $tmp;
                    } else {
                        $dt[$pupilsightPersonID][$sub[$i]] = $tmp;
                    }
                    $i++;
                }
            }
        }
    } catch (Exception $ex) {
        print_r($ex);
    }
    return $dt;
}

function runMultiple($stuResultData, $testIDs, $final_formula)
{
    if ($stuResultData) {


        $len = count($testIDs);
        $i = 0;
        $lastMaxVal = 0;
        $maxPos = 0;
        
        $stuDataArray = array();
        foreach($stuResultData as $stData){
            foreach($stData as $k => $st){
                $stuDataArray[$k] = $st;
            }
        }
        
        $stuResultData = $stuDataArray;
        while ($i < $len) {
            //print_r($testIDs[$i]);
            $testData = $stuResultData[$testIDs[$i]];
            //print_r($stuResultData);
            foreach ($testData as $stu => $stid) {
                $tmpCount = count($stid);
                if ($lastMaxVal < $tmpCount) {
                    $lastMaxVal = $tmpCount;
                    $maxPos = $i;
                }
            }
            $i++;
        }
        // echo "\n<br>" . $maxPos;
        // die();
        $finalData = array();
        // print_r($stuResultData[$testIDs[$maxPos]]['0000000058']);
        // die();
        foreach ($stuResultData[$testIDs[$maxPos]] as $studentid => $testData) {

            //echo "\n<br>max subject test data";
            //print_r($testData);
            
            foreach ($testData as $subid => $marks) {
                $i = 0;
                $cnt = 0;
                $testmarks = array();
                while ($i < $len) {
                    if ($i != $maxPos) {
                        //print_r($stuResultData[$testIDs[$i]][$studentid]);
                        if(isset($stuResultData[$testIDs[$i]][$studentid][$subid]["marks"])){
                            //print_r($stuResultData[$testIDs[$i]][$studentid][$subid]["marks"]);
                            // echo 'mrks'.$stuResultData[$testIDs[$i]][$studentid][$sub]["marks"];
                            $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$studentid][$subid]["marks"];
                            $cnt++;
                            // $mks = $stuResultData[$testIDs[$i]][$studentid][$subid]["marks"];
                            // $lastmks = $stuResultData[$testIDs[$maxPos]][$studentid][$subid]["marks"];
                            
                            // $fm = $mks + $lastmks;
                            // $stuResultData[$testIDs[$maxPos]][$studentid][$subid]["marks"] = $fm;
                        } 
                    } else {
                        $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$studentid][$subid]["marks"];
                        $cnt++;
                    }
                    $i++;
                }
                //print_r($testmarks);
                // if($final_formula == 'Sum'){
                //     $sumMarks = sum($testmarks);
                // }
                // if($final_formula == 'Average'){
                //     $sumMarks = avg($testmarks);
                // }
                // if($final_formula == 'Best_of_All'){
                    $sumMarks = Best_of_All($testmarks);
                //}
                
                
                $stuResultData[$testIDs[$maxPos]][$studentid][$subid]["marks"] = $sumMarks;
            }
            
        }
        echo '<pre>';
        print_r($stuResultData[$testIDs[$maxPos]]);
        die();
        
    }
    //return $dt;
}

function sum($marks)
{
    $len = count($marks);
    $i = 0;
    $fm = 0;
    while ($i < $len) {
        $fm += $marks[$i];
        $i++;
    }
    return $fm;
}

function avg($marks)
{
    $sumMarks = sum($marks);
    $len = count($marks);
    $avgMarks = $sumMarks / $len;
    return $avgMarks;
}

function Best_of_All($marks)
{
    $data = max($marks);
    // $len = count($marks);
    // $i = 0;
    // $fm = 0;
    // while ($i < $len) {
    //     $fm += $marks[$i];
    //     $i++;
    // }
    return $data;
}

function getClassTeacherDetails($connection2, $pupilsightMappingID)
{
    $sqlpr = 'SELECT ct.pupilsightPersonID, b.signature_path, a.officialName, a.image_240 FROM assign_class_teacher_section AS ct LEFT JOIN  pupilsightPerson AS a ON ct.pupilsightPersonID = a.pupilsightPersonID LEFT JOIN pupilsightStaff AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE ct.pupilsightMappingID = ' . $pupilsightMappingID . ' ';
    $resultpr = $connection2->query($sqlpr);
    $ctData = $resultpr->fetch();
    $classTeacher[$ctData['pupilsightPersonID']] = array(
        'name' => $ctData['officialName'],
        'signature' => $ctData['signature_path'],
        'photo' => $ctData['image_240']
    );
    return $classTeacher;
}

function getSkillConfig__($connection2, $testid)
{
    try {
        $sq = "select DISTINCT pupilsightDepartmentID,skill_id,skill_configure from examinationSubjectToTest where `test_id`='" . $testid . "' and skill_configure <> '' and skill_configure <> 'None' ";
        $result = $connection2->query($sq);
        $data = $result->fetchAll();
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        die();
        return $data;
    } catch (Exception $ex) {
        print_r($ex);
    }
}

function getSkillConfig($connection2, $testid)
{
    try {
        $sq = "select DISTINCT pupilsightDepartmentID,skill_id,skill_configure from examinationSubjectToTest where `test_id`='" . $testid . "' and skill_configure <> '' and skill_configure <> 'None' ";
        $result = $connection2->query($sq);
        $data = $result->fetchAll();
        $len = count($data);
        $i = 0;
        $res = array();
        while ($i < $len) {
            $res[$data[$i]["pupilsightDepartmentID"]] = $data[$i]["skill_configure"];
            $i++;
        }
        // print_r($res);
        // die();
        return $res;
    } catch (Exception $ex) {
        print_r($ex);
    }
}

function loadSkillConfig($skillConfig, $result)
{
    $dt = array();
     //print_r($skillConfig);
    // print_r($result);
    foreach ($result as $rd) {
        //$id = $col[0];
        $pupilsightPersonID = $rd['pupilsightPersonID'];
        $subject = $rd['subject'];
        $marks = $rd['marks'];
        //$skill = $rd['skill'];

        $sub = explode(",", $subject);
        $mks = explode(",", $marks);
        //$sl = explode(",", $skill);
        $len = count($sub);
        $i = 0;
        $cnt1 = 1;
        $subject = '';
        while ($i < $len) {
            if (isset($skillConfig[$sub[$i]])) {
                if($subject !=  $sub[$i]){
                    $subject = $sub[$i];
                    $cnt1 = 1;
                }
                $skillConfigure = $skillConfig[$sub[$i]];
                if (isset($dt[$pupilsightPersonID][$sub[$i]]["marks"])) {
                    $oldMarks = $marks;
                    $marks = $oldMarks + $mks[$i];
                } else {
                   $marks = $mks[$i];
                }
                
                if($skillConfigure == 'Sum'){
                    $dt[$pupilsightPersonID][$sub[$i]]["marks"] = $marks;
                } else if($skillConfigure == 'Average'){
                    $avgMarks = $marks / $cnt1;
                    $dt[$pupilsightPersonID][$sub[$i]]["marks"] = $avgMarks;
                } 
                $cnt1++;
            }
            $i++;
        }
    }
    // echo '<pre>';
    // print_r($dt);
    // echo '</pre>';
    // die();
    return $dt;
}

function getSubjectList($connection2, $testId, $mappingData){
    $sq = "select  a.pupilsightDepartmentID, b.subject_display_name from examinationSubjectToTest AS a LEFT JOIN subjectToClassCurriculum AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID where a.test_id='" . $testId . "' AND b.pupilsightSchoolYearID = " . $mappingData['pupilsightSchoolYearID'] . " AND b.pupilsightProgramID = " . $mappingData['pupilsightProgramID'] . " AND b.pupilsightYearGroupID = " . $mappingData['pupilsightYearGroupID'] . "  ";
    $result = $connection2->query($sq);
    $data = $result->fetchAll();
    $sub = array();
    if(!empty($data)){
        foreach($data as $d){
            $sub[$d['pupilsightDepartmentID']] = $d['subject_display_name'];
        }
    }
    // print_r($sub);
    // die();
    return $sub;
}
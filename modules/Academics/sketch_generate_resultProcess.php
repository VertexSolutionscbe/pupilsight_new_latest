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

    $sqlsketch = 'SELECT a.id as erta_id, a.attribute_name, a.attribute_category, a.attribute_type, a.test_master_id, a.attr_ids, a.final_formula, a.final_formula_best_cal, a.grade_id, a.supported_attribute, a.subject_type, a.subject_val_id, b.* FROM examinationReportTemplateAttributes AS a LEFT JOIN examinationReportTemplateConfiguration AS b ON a.ertc_id = b.id WHERE a.sketch_id = ' . $sketch_id . ' ';
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
            $maxMarksArray = array();
            $comMarksArray = array();
            $comMaxMarksArray = array();
            try {
                foreach ($sketchDataAttr as $sd) {
                    
                    
                    if ($sd['attribute_type'] == 'Student' || $sd['attribute_type'] == 'Parent' || $sd['attribute_type'] == 'Class Teacher' || $sd['attribute_type'] == 'Principal') {
                        $studentDetails = getStudentDetails($connection2, $studentIds, $mappingData,  $sd['attribute_type']);
                        foreach($studentDetails as $k => $std){
                            if (($sd['attribute_type'] == 'Student' || $sd['attribute_type'] == 'Parent') && array_key_exists($sd['report_column_word'], $std)) {
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
                   

                    if ($sd['attribute_type'] == 'Marks' && $sd['attribute_category'] == 'Test') {
                        $erta_id =  $sd['erta_id'];

                        if(!empty($sd['test_master_id'])){
                            $getStudentMarks = getStudentMarks($connection2, $conn, $sd['test_master_id'], $sd['erta_id'], $studentIds, $mappingData, $sd['final_formula'], $sd['final_formula_best_cal']);
                            
                            $cnt = 1;
                            foreach($getStudentMarks as $stuId => $getStuData){
                                foreach($getStuData as $getStuMarks){
                                    $getmarks = $getStuMarks['marks'];
                                    $subjectId = $getStuMarks['subject'];
                                    $subjectName = $getStuMarks['subjectName'];

                                    // $getMarksNew = str_replace(".00","", $getmarks);
                                    $getMarksNew = $getmarks;
                                    $gmsarr[$stuId][$sd['erta_id']][$subjectId] = $getMarksNew;
                                    
                                    $getSketchData[$sd['attribute_type']][$stuId][$sd['erta_id']][$subjectId] = $getMarksNew;
                                    //echo $sd['attribute_name'].'_'.$cnt.'_'.$tsub['subject'].'--'.$getmarks.'</br>';

                                    // $dataarr[$sd['attribute_name'] . '_' . $cnt . '_' . $tsub['subject']] = $getmarks;

                                    $dataarr[$stuId][$sd['attribute_name'] . '_' . $cnt . '_' . $subjectName] = $getMarksNew;
                                    $cnt++;
                                }
                            }
                        }


                        // echo '<pre>';
                        // print_r($getSketchData);
                        // echo '</pre>';
                    }
                   
                   
                    if ($sd['attribute_type'] == 'Max Marks' && $sd['attribute_category'] == 'Test') {

                        $erta_id =  $sd['erta_id'];
                        
                        $getStudentSubMaxMarks = getStudentSubMaxMarks($connection2, $conn, $sd['test_master_id'], $sd['erta_id'], $studentIds, $mappingData, $sd['final_formula'], $sd['final_formula_best_cal']);

                        
                        $cnt = 1;
                        foreach($getStudentSubMaxMarks as $maxData){
                            $newMaxMarks = $maxData['max_marks'];

                            $maxMarksArr[$sd['erta_id']][$maxData['subject']] = $newMaxMarks;

                            $getSketchData[$sd['attribute_type']][$sd['erta_id']][$maxData['subject']] = $newMaxMarks;

                            
                            $maxMarksArray[$sd['attribute_name'] . '_' . $cnt . '_' . $maxData['subjectName']] = $maxData['max_marks'];
                            $cnt++;
                        }

                    }

                    //print_r($maxMarksArr);
                    
                      
                    if ($sd['attribute_type'] == 'Grade' && $sd['attribute_category'] == 'Test') {

                        $erta_id =  $sd['erta_id'];
                        
                        $getStudentMarks = getStudentMarks($connection2, $conn, $sd['test_master_id'], $sd['erta_id'], $studentIds, $mappingData, $sd['final_formula'], $sd['final_formula_best_cal']);
                        

                        $gradeConfiguration = getGradeConfiguration($connection2);
                        $finalGradeConfig = loadGradeConfiguration($gradeConfiguration);
                        // echo '<pre>';
                        // print_r($finalGradeConfig);
                        // echo '</pre>';
                        // die();

                        $cnt = 1;
                        foreach($getStudentMarks as $stuId => $getStuData){
                            foreach($getStuData as $getStuMarks){
                                $getmarks = $getStuMarks['marks'];
                                $gradeSystemId = $getStuMarks['gradeSystemId'];
                                $grade_id = $getStuMarks['grade_id'];
                                $skill_configure = $getStuMarks['skill_configure'];
                                $subjectId = $getStuMarks['subject'];
                                $subjectName = $getStuMarks['subjectName'];
                                $gradeName = '';

                                if($skill_configure == 'Sum'){
                                    $gradeName =  getGradeByMarks($gradeSystemId, $getmarks, $gradeConfiguration);
                                } else if($skill_configure == 'Average'){
                                    $gradeName =  getGradeByMarks($gradeSystemId, $getmarks, $gradeConfiguration);
                                } else {
                                    //echo (int)$grade_id.'</br>';
                                    $gId = (int)$grade_id;
                                    if(!empty($gId)){
                                        $gradeName = $finalGradeConfig[(int)$grade_id];
                                    }
                                }

                                $getSketchData[$sd['attribute_type']][$sd['erta_id']][$subjectId] = $gradeName;

                                $dataarr[$stuId][$sd['attribute_name'] . '_' . $cnt . '_' . $subjectName] = $gradeName;
                                $cnt++;
                            }
                        }

                        
                    }

                  
                    if ($sd['attribute_type'] == 'Remarks' && $sd['attribute_category'] == 'Test') {

                        $remarks = getRemarks($connection2, $sd['test_master_id'], $mappingData);
                        //die();
                        if(!empty($sd['test_master_id'])){
                            $getStudentMarks = getStudentMarks($connection2, $conn, $sd['test_master_id'], $sd['erta_id'], $studentIds, $mappingData, $sd['final_formula'], $sd['final_formula_best_cal']);

                            //print_r($getStudentMarks);
                            
                            $cnt = 1;
                            foreach($getStudentMarks as $stuId => $getStuData){
                                foreach($getStuData as $getStuMarks){
                                    if(isset($remarks[$getStuMarks['test_id']][$stuId][$getStuMarks['subject']])) {
                                        $getRemarks = $remarks[$getStuMarks['test_id']][$stuId][$getStuMarks['subject']];

                                        $dataarr[$stuId][$sd['attribute_name'] . '_' . $cnt . '_' . $getStuMarks['subjectName']] = $getRemarks;
                                        $cnt++;
                                    }
                                }
                            }
                        }

                      
                    }
  
                     
                    //print_r($sd);
                    
                    if ($sd['attribute_type'] == 'Marks' && $sd['attribute_category'] == 'Computed') {

                        // echo '<pre>';
                        // print_r($gmsarr);
                        // echo '</pre>';
                        // die();
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

                        $getAllSubjectName = getAllSubjectName($connection2, $mappingData);

                        foreach($gmsarr as $stuId => $gms){
                            $getComputedMarks = calculateComputed($gms, $attributeIds);
                            $computedData = array();
                            foreach($getComputedMarks as $k => $gmarks){
                                $subName = $getAllSubjectName[$k];
                                $computedData[$k]['subjectName'] =  $subName;
                                $computedData[$k]['marks'] = getFinalComputedMarks($finalFormuala,  $getComputedMarks[$k], $sd['final_formula_best_cal']);
                                
                            }

                            $i = 1;
                            foreach($computedData as $cd){
                                $totalMarksArr[$k] = $cd['marks'];
        
                                $getSketchData[$sd['attribute_type']][$sd['erta_id']][$k] = $cd['marks'];

                                $dataarr[$stuId][$sd['attribute_name'] . '_' . $i . '_' . $cd['subjectName']] = $cd['marks'];
                                $i++;
                            }
                        }

                        
                    }

                    
                    if ($sd['attribute_type'] == 'Max Marks' && $sd['attribute_category'] == 'Computed') {

                        // echo '<pre>';
                        // print_r($maxMarksArr);
                        // echo '</pre>';
                        // die();
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

                        $attrIds = explode(',', $sd['attr_ids']);

                        $getAllSubjectName = getAllSubjectName($connection2, $mappingData);

                        
                            $getComputedMarks = calculateComputed($maxMarksArr, $attrIds);
                       
                            $computedData = array();
                            foreach($getComputedMarks as $k => $gmarks){
                                $subName = $getAllSubjectName[$k];
                                $computedData[$k]['subjectName'] =  $subName;
                                $computedData[$k]['max_marks'] = getFinalComputedMarks($finalFormuala,  $getComputedMarks[$k], $sd['final_formula_best_cal']);
                                
                            }
                            //print_r($computedData);

                            $i = 1;
                            foreach($computedData as $cd){
                                $totalMaxMarksArr[$k] = $cd['max_marks'];
        
                                $getSketchData[$sd['attribute_type']][$sd['erta_id']][$k] = $cd['max_marks'];

                                $comMaxMarksArray[$sd['attribute_name'] . '_' . $i . '_' . $cd['subjectName']] = $cd['max_marks'];
                                $i++;

                            }
                        
                    }

                    if ($sd['attribute_type'] == 'Grade' && $sd['attribute_category'] == 'Computed') {

                        $finalFormuala = $sd['final_formula'];
                        if(!empty($sd['supported_attribute'])){
                            $supported_attribute = $sd['supported_attribute'];
                            $grade_id = $sd['grade_id'];

                            $getAllSubjectName = getAllSubjectName($connection2, $mappingData);
                            $gradeConfiguration = getGradeConfiguration($connection2);
    
                            try{
                                if(!empty($getSketchData)){
                                    // echo '<pre>';
                                    // print_r($getSketchData['Marks']);
                                    // echo '</pre>';
                                    // die();
                                    if(isset($getSketchData['Max Marks'][$supported_attribute])){
                                        $gradeMaxMarks = $getSketchData['Max Marks'][$supported_attribute];
                                       // $gradeMarks = $getSketchData['Marks'][$sd['attr_ids']];
                
                                        $i = 1;
                                        if(isset($getSketchData['Marks'])){
                                            foreach($getSketchData['Marks'] as $stuid => $gsd){
                                                // print_r($gsd);
                                                // die();
                                                if(is_array($gsd)){
                                                    foreach ($gsd as $j => $grmnew) {
                                                        // echo $j;
                                                        // print_r($grmnew);
                                                        // die();
                                                        // echo $d.'</br>';
                                                        if(is_array($grmnew)){
                                                            try{
                                                                foreach($grmnew as $d => $subDa){
                                                                    $subName = $getAllSubjectName[$d];
                                                                    if(!empty($gradeMaxMarks[$d])){
                                                                        $gmn = ($subDa / 100) * $gradeMaxMarks[$d];
                                                                    } else {
                                                                        $gmn = $subDa;
                                                                    }
                                                                    
                                                                    if (!empty($gmn)) {
                                                                        $gradeName =  getGradeByMarks($grade_id, $gmn, $gradeConfiguration);
                                                                    
                                                                        $gradename = $gradeName;
                                                                    } else {
                                                                        $gradename = '';
                                                                    }
                                                                    $dataarr[$stuid][$sd['attribute_name'] . '_' . $i . '_' . $subName] = $gradename;
                                                                    $i++;
                                                                }
                                                            } catch(Exception $ex){
                                                                print_r($ex);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }   catch (Exception $ex) {
                                //print_r($ex);
                            }
                        } 
                    }

                    
                }
            } catch (Exception $ex) {
                //print_r($ex);
            }
            // echo '<pre>';
            // print_r($dataarr);
            // echo '</pre>';

            
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

                    if(!empty($maxMarksArray)){
                        $newVal = array_merge($newVal, $maxMarksArray);
                    } else {
                        $newVal = $newVal;
                    }

                    
                     if(!empty($comMaxMarksArray)){
                        $newVal = array_merge($newVal, $comMaxMarksArray);
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
            
            //header("Location: {$URL}");  

        }
        echo 'done';
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
                $sql = 'SELECT c.officialName as sub_teacher, d.name, sd.subject_display_name,  s.signature_path, c.image_240 FROM assignstaff_tosubject AS a LEFT JOIN pupilsightStaff AS b ON a.pupilsightStaffID = b.pupilsightStaffID LEFT JOIN pupilsightPerson AS c ON b.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN pupilsightDepartment AS d ON a.pupilsightDepartmentID = d.pupilsightDepartmentID LEFT JOIN subjectToClassCurriculum AS sd ON a.pupilsightDepartmentID = sd.pupilsightDepartmentID LEFT JOIN pupilsightStaff AS s ON c.pupilsightPersonID = s.pupilsightPersonID WHERE d.pupilsightDepartmentID IN (' . $subIds . ') AND sd.pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND sd.pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND sd.pupilsightYearGroupID = ' . $mappingData['pupilsightYearGroupID'] . ' GROUP BY d.pupilsightDepartmentID ORDER BY sd.pos ASC ';
                $resultsub = $connection2->query($sql);
                $subData = $resultsub->fetchAll();
            }
        } else if ($subject_type == 'All Subject') {

            $sql = 'SELECT c.officialName as sub_teacher, d.subject_display_name, s.signature_path, c.image_240 FROM assignstaff_tosubject AS a LEFT JOIN pupilsightStaff AS b ON a.pupilsightStaffID = b.pupilsightStaffID LEFT JOIN pupilsightPerson AS c ON b.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN subjectToClassCurriculum AS d ON a.pupilsightDepartmentID = d.pupilsightDepartmentID LEFT JOIN pupilsightStaff AS s ON c.pupilsightPersonID = s.pupilsightPersonID WHERE d.pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND d.pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND d.pupilsightYearGroupID = ' . $mappingData['pupilsightYearGroupID'] . ' GROUP BY d.pupilsightDepartmentID ORDER BY d.pos ASC ';
            $resultsub = $connection2->query($sql);
            $subData = $resultsub->fetchAll();
        } else {

            $sql = 'SELECT c.officialName as sub_teacher, d.subject_display_name, s.signature_path, c.image_240 FROM assignstaff_tosubject AS a LEFT JOIN pupilsightStaff AS b ON a.pupilsightStaffID = b.pupilsightStaffID LEFT JOIN pupilsightPerson AS c ON b.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN subjectToClassCurriculum AS d ON a.pupilsightDepartmentID = d.pupilsightDepartmentID LEFT JOIN pupilsightDepartment AS e ON d.pupilsightDepartmentID = e.pupilsightDepartmentID LEFT JOIN pupilsightStaff AS s ON c.pupilsightPersonID = s.pupilsightPersonID WHERE d.pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND d.pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND d.pupilsightYearGroupID = ' . $mappingData['pupilsightYearGroupID'] . ' AND e.type = "' . $subject_type . '" GROUP BY d.pupilsightDepartmentID ORDER BY d.pos ASC ';
            $resultsub = $connection2->query($sql);
            $subData = $resultsub->fetchAll();
        }
    }
    return $subData;
}

function getStudentMarks($connection2, $conn, $test_master_id, $erta_id, $studentIds, $mappingData, $final_formula, $final_formula_best_cal)
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
                $stuResultData[] = getTestData($connection2, $testid, $studentIds, $kountTmId, $mappingData, $erta_id);
            }
        }
        $testResultData = runMultiple($stuResultData, $testIDs, $final_formula, $final_formula_best_cal);
    } else {
        $sqlt = 'SELECT test_id FROM examinationTestAssignClass WHERE test_master_id = ' . $test_master_id . ' AND pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND pupilsightYearGroupID = ' . $mappingData['pupilsightYearGroupID'] . ' AND pupilsightRollGroupID = ' . $mappingData['pupilsightRollGroupID'] . ' ';
        $resultt = $connection2->query($sqlt);
        $testdata = $resultt->fetch();
        if (!empty($testdata)) {
            $testid = $testdata['test_id'];
            $testResultData = getTestData($connection2, $testid, $studentIds, $kountTmId, $mappingData, $erta_id);
        }
    }
    // echo '<pre>';
    // print_r($testResultData);
    // echo '</pre>';
    // die();
    return $testResultData;
}

function getStudentSubMaxMarks($connection2, $conn, $test_master_id, $erta_id, $studentIds, $mappingData, $final_formula, $final_formula_best_cal)
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
                $stuResultData[] = getSubjectMaxData($connection2, $testid, $studentIds, $kountTmId, $mappingData, $erta_id);
            }
        }
        $testResultData = runMultipleSubMax($stuResultData, $testIDs, $final_formula, $final_formula_best_cal);
    } else {
        $sqlt = 'SELECT test_id FROM examinationTestAssignClass WHERE test_master_id = ' . $test_master_id . ' AND pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND pupilsightYearGroupID = ' . $mappingData['pupilsightYearGroupID'] . ' AND pupilsightRollGroupID = ' . $mappingData['pupilsightRollGroupID'] . ' ';
        $resultt = $connection2->query($sqlt);
        $testdata = $resultt->fetch();
        if (!empty($testdata)) {
            $testid = $testdata['test_id'];
            $testResultData = getSubjectMaxData($connection2, $testid, $studentIds, $kountTmId, $mappingData, $erta_id);
        }
    }
    // echo '<pre>';
    // print_r($testResultData);
    // echo '</pre>';
    //die();
    return $testResultData;
}

function getSubjectMaxData($connection2, $testid, $pupilsightPersonID = NULL, $kountTmId = NULL, $mappingData, $erta_id)
{
    $sql = 'SELECT test_master_id FROM examinationTest WHERE id= '.$testid.' ';
    $result = $connection2->query($sql);
    $tMasData = $result->fetch();
    $testMasterId = $tMasData['test_master_id'];
    try{
        $sqlp = 'SELECT a.*, b.name FROM examinationReportTemplatePluginAttributeMapping AS a LEFT JOIN examinationReportTemplatePlugin AS b ON a.plugin_id = b.id WHERE a.erta_id = ' . $erta_id . ' AND a.test_master_id = '.$testMasterId.' ';
        $resultp = $connection2->query($sqlp);
        $plugindata = $resultp->fetch();

        if ($plugindata['name'] == 'Round') {
            $roundvalue = $plugindata['plugin_val'];
        } else {
            $roundvalue = '';
        }
    } catch (Exception $ex) {
        //print_r($ex);
    }

    try{
        $sqlf = 'SELECT a.*, b.name FROM examinationReportTemplateFormulaAttributeMapping AS a LEFT JOIN examinationReportTemplateFormula AS b ON a.formula_id = b.id WHERE a.erta_id = ' . $erta_id . ' AND a.test_master_id = '.$testMasterId.' ';
        $resultf = $connection2->query($sqlf);
        $formuladata = $resultf->fetch();
        // print_r($formuladata);
        if ($formuladata['name'] == 'Scale') {
            $scalevalue = $formuladata['formula_val'];
        } else {
            $scalevalue = '';
        }
    } catch (Exception $ex) {
        //print_r($ex);
    }

    $dt = array();
    try {
        $sql = "select  a.pupilsightDepartmentID, a.max_marks, b.subject_display_name from examinationSubjectToTest AS a LEFT JOIN subjectToClassCurriculum AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID where a.test_id='" . $testid . "' AND b.pupilsightSchoolYearID = " . $mappingData['pupilsightSchoolYearID'] . " AND b.pupilsightProgramID = " . $mappingData['pupilsightProgramID'] . " AND b.pupilsightYearGroupID = " . $mappingData['pupilsightYearGroupID'] . "  ";
        $resultt = $connection2->query($sql);
        $resultData = $resultt->fetchAll();
        //print_r($resultData);

        if (!empty($resultData)) {
            
            foreach ($resultData as $rd) {
                //$id = $col[0];
                //$pupilsightPersonID = $rd['pupilsightPersonID'];
                $subject = $rd['pupilsightDepartmentID'];
                $max_marks = $rd['max_marks'];
                $subjectName = $rd['subject_display_name'];
                
                $tmp = array();
                $tmp["subject"] = $subject;
                $tmp["subjectName"] = $subjectName;
                if (!empty($scalevalue)) {
                    $max_marks = $scalevalue;
                } else {
                    $max_marks = $max_marks;
                }

                $tmp["max_marks"] = $max_marks;

                    // echo '<pre>';
                    // print_r($tmp["marks"]);
                    // echo '</pre>';

                if ($kountTmId > 1) {
                    $dt[$testid][$subject] = $tmp;
                } else {
                    $dt[$subject] = $tmp;
                }
                
            }
        }
    } catch (Exception $ex) {
        print_r($ex);
    }
    return $dt;
}

function runMultipleSubMax($stuResultData, $testIDs, $final_formula, $final_formula_best_cal)
{
    // echo '<pre>';
    // print_r($stuResultData);
    // echo '</pre>';

    if ($stuResultData) {

        $len = count($testIDs);
        $i = 0;
        $lastMaxVal = 0;
        $maxPos = 0;
        
        $stuDataArray = array();
        foreach($stuResultData as $k => $stData){
            foreach($stData as $k => $st){
                $tmpCount = count($st);
                if ($lastMaxVal < $tmpCount) {
                    $lastMaxVal = $tmpCount;
                    $maxPos = $i;
                } 
                $stuDataArray[$k] = $st;
                $i++;
            }
        }
        // echo $maxPos;
        // die();
    //      echo '<pre>';
    // print_r($stuDataArray);
    // echo '</pre>';
        
        $stuResultData = $stuDataArray;
        // while ($i < $len) {
        //     $testData = $stuResultData[$testIDs[$i]];
        //     foreach ($testData as $stu => $stid) {
        //         $tmpCount = count($stid);
        //         if ($lastMaxVal < $tmpCount) {
        //             $lastMaxVal = $tmpCount;
        //             $maxPos = $i;
        //         }
        //     }
        //     $i++;
        // }
        // echo "\n<br>" . $maxPos;
        // die();
        $finalData = array();
        // print_r($stuResultData[$testIDs[$maxPos]]['0000000058']);
        // die();
        foreach ($stuResultData[$testIDs[$maxPos]] as $subid => $marks) {

            // echo "\n<br>max subject test data".$subid;
            // print_r($marks);
            
            //foreach ($testData as $subid => $marks) {
                $i = 0;
                $cnt = 0;
                $testmarks = array();
                while ($i < $len) {
                    if ($i != $maxPos) {
                        //print_r($stuResultData[$testIDs[$i]][$studentid]);
                        if(isset($stuResultData[$testIDs[$i]][$subid]["max_marks"])){
                            //print_r($stuResultData[$testIDs[$i]][$studentid][$subid]["marks"]);
                            // echo 'mrks'.$stuResultData[$testIDs[$i]][$studentid][$sub]["marks"];
                            
                            if($final_formula == 'Average_Excluding_Ab_Ex'){
                                if($stuResultData[$testIDs[$i]][$subid]["max_marks"] != '0.00'){
                                    $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$subid]["max_marks"];
                                    $cnt++;
                                }
                            } else if($final_formula == 'Average_Excluding_Ab'){
                                
                                if($stuResultData[$testIDs[$i]][$subid]["marks_abex"] != 'AB'){
                                    $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$subid]["max_marks"];
                                    $cnt++;
                                }
                            } else if($final_formula == 'Average_Excluding_Ex'){
                                if($stuResultData[$testIDs[$i]][$subid]["max_marks"] != '0.00'){
                                    $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$subid]["max_marks"];
                                    $cnt++;
                                }
                            } else {
                                $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$subid]["max_marks"];
                                $cnt++;
                            }
                            
                        } 
                    } else {
                        if($final_formula == 'Average_Excluding_Ab_Ex'){
                            if($stuResultData[$testIDs[$i]][$subid]["max_marks"] != '0.00'){
                                $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$subid]["max_marks"];
                                $cnt++;
                            }
                        } else if($final_formula == 'Average_Excluding_Ab'){
                            
                            if($stuResultData[$testIDs[$i]][$subid]["marks_abex"] != 'AB'){
                                $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$subid]["max_marks"];
                                $cnt++;
                            }
                        } else if($final_formula == 'Average_Excluding_Ex'){
                            if($stuResultData[$testIDs[$i]][$subid]["max_marks"] != '0.00'){
                                $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$subid]["max_marks"];
                                $cnt++;
                            }
                        } else {
                            $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$subid]["max_marks"];
                            $cnt++;
                        }
                        
                    }
                    // echo $i.'--'.$testIDs[$i].'--'.$stuResultData[$testIDs[$i]][$subid]["max_marks"].'</br>';
                    // $minmax[] = $stuResultData[$testIDs[$i]][$subid]["max_marks"];   
                    // $testmarks[$i] = $stuResultData[$testIDs[$i]][$subid][$testIDs[$i]."max_marks"];
                    $i++;
                }
                //print_r($testmarks);
               // die();
                if($final_formula == 'Sum'){
                    $getStudentMarksData = sum($testmarks);
                }
                if($final_formula == 'Sum_Excluding_Ab'){
                    $getStudentMarksData = sum($testmarks);
                }

                if($final_formula == 'Sum_Excluding_Ex'){
                    $getStudentMarksData = sum($testmarks);
                }

                if($final_formula == 'Sum_Excluding_Ab_Ex'){
                    $getStudentMarksData = sum($testmarks);
                }

                if($final_formula == 'Average'){
                   $getStudentMarksData = avg($testmarks);
                }

                if($final_formula == 'Average_Excluding_Ab'){
                    $getStudentMarksData = avg($testmarks);
                }

                if($final_formula == 'Average_Excluding_Ex'){
                    $getStudentMarksData = avg($testmarks);
                }

                if($final_formula == 'Average_Excluding_Ab_Ex'){
                    $getStudentMarksData = avg($testmarks);
                }

                if($final_formula == 'Best_of_All'){
                    $getStudentMarksData = Best_of_All($testmarks);
                }

                if($final_formula == 'Best_of_All_Excluding_Ex'){
                    $getStudentMarksData = Best_of_All($testmarks);
                }

                if($final_formula == 'Best_of_All_Excluding_Ab'){
                    $getStudentMarksData = Best_of_All($testmarks);
                }

                if($final_formula == 'Best_of_All_Excluding_Ab_Ex'){
                    $getStudentMarksData = Best_of_All($testmarks);
                }

                if($final_formula == 'Second_Best_of_All'){
                   $getStudentMarksData = Third_Best_of_All($testmarks);
                }

                if($final_formula == 'Second_Best_of_All_Excluding_Ex'){
                    $getStudentMarksData = Second_Best_of_All($testmarks);
                }

                if($final_formula == 'Second_Best_of_All_Excluding_Ab'){
                    $getStudentMarksData = Second_Best_of_All($testmarks);
                }

                if($final_formula == 'Second_Best_of_All_Excluding_Ab_Ex'){
                    $getStudentMarksData = Second_Best_of_All($testmarks);
                }

                if($final_formula == 'Third_Best_of_All'){
                    $getStudentMarksData = Third_Best_of_All($testmarks);
                }

                if($final_formula == 'Third_Best_of_All_Excluding_Ex'){
                    $getStudentMarksData = Third_Best_of_All($testmarks);
                }

                if($final_formula == 'Third_Best_of_All_Excluding_Ab'){
                    $getStudentMarksData = Third_Best_of_All($testmarks);
                }

                if($final_formula == 'Third_Best_of_All_Excluding_Ab_Ex'){
                    $getStudentMarksData = Third_Best_of_All($testmarks);
                }

                if($final_formula == 'Best_of_Sum'){
                    $getStudentMarksData = Best_of_Sum($testmarks, $final_formula_best_cal);
                }

                if($final_formula == 'Best_of_Sum_Excluding_Ab_Ex'){
                    $getStudentMarksData = Best_of_Sum($testmarks, $final_formula_best_cal);
                }

                if($final_formula == 'Best_of_Average'){
                    $getStudentMarksData = Best_of_Average($testmarks, $final_formula_best_cal);
                }

                if($final_formula == 'Best_of_Average_Excluding_Ab_Ex'){
                    $getStudentMarksData = Best_of_Average($testmarks, $final_formula_best_cal);
                }

                $stuResultData[$testIDs[$maxPos]][$subid]["max_marks"] = $getStudentMarksData;
            //}
            
        }
        // echo '<pre>';
        // print_r($stuResultData[$testIDs[$maxPos]]);
        // die();
        $dt = $stuResultData[$testIDs[$maxPos]];
        
    }
    return $dt;
}

function getTestData($connection2, $testid, $pupilsightPersonID = NULL, $kountTmId = NULL, $mappingData, $erta_id)
{
    $sql = 'SELECT test_master_id FROM examinationTest WHERE id= '.$testid.' ';
    $result = $connection2->query($sql);
    $tMasData = $result->fetch();
    $testMasterId = $tMasData['test_master_id'];
    try{
        $sqlp = 'SELECT a.*, b.name FROM examinationReportTemplatePluginAttributeMapping AS a LEFT JOIN examinationReportTemplatePlugin AS b ON a.plugin_id = b.id WHERE a.erta_id = ' . $erta_id . ' AND a.test_master_id = '.$testMasterId.' ';
        $resultp = $connection2->query($sqlp);
        $plugindata = $resultp->fetch();

        if ($plugindata['name'] == 'Round') {
            $roundvalue = $plugindata['plugin_val'];
        } else {
            $roundvalue = '';
        }
    } catch (Exception $ex) {
        //print_r($ex);
    }

    try{
        $sqlf = 'SELECT a.*, b.name FROM examinationReportTemplateFormulaAttributeMapping AS a LEFT JOIN examinationReportTemplateFormula AS b ON a.formula_id = b.id WHERE a.erta_id = ' . $erta_id . ' AND a.test_master_id = '.$testMasterId.' ';
        $resultf = $connection2->query($sqlf);
        $formuladata = $resultf->fetch();
        // print_r($formuladata);
        if ($formuladata['name'] == 'Scale') {
            $scalevalue = $formuladata['formula_val'];
        } else {
            $scalevalue = '';
        }
    } catch (Exception $ex) {
        //print_r($ex);
    }

    $dt = array();
    try {
        $sql = "select id, pupilsightPersonID,
        group_concat(pupilsightDepartmentID) as subject, 
        group_concat(marks_obtained) as marks, 
        group_concat(IFNULL(marks_abex,'0')) as marks_abex,
        group_concat(skill_id) as skill,
        group_concat(gradeId) as grade_id,
        group_concat(test_id) as test_id
        from  `examinationMarksEntrybySubject` WHERE test_id = " . $testid . " ";
        if ($pupilsightPersonID) {
            $sql .= " and pupilsightPersonID IN (" . $pupilsightPersonID . ") ";
        }
        $sql .= " group by pupilsightPersonID order by pupilsightPersonID;";

        $resultt = $connection2->query($sql);
        $resultData = $resultt->fetchAll();
        //print_r($resultData);

        if (!empty($resultData)) {
            $subjectNames = getSubjectList($connection2, $testid, $mappingData);
            $subjectMaxMarks = getSubjectMaxMarks($connection2, $testid, $mappingData);
            $subjectMinMarks = getSubjectMinMarks($connection2, $testid, $mappingData);
            $subjectGrade = getSubjectGrade($connection2, $testid, $mappingData);
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
                $t_id = $rd['test_id'];

                $sub = explode(",", $subject);
                $mks = explode(",", $marks);
                $mksab = explode(",", $marks_abex);
                $sl = explode(",", $skill);
                $grd = explode(",", $grade_id);
                $tId = explode(",", $t_id);
                $len = count($sub);
                $i = 0;
                while ($i < $len) {
                    //$tmp[$sub[$i]]=;
                    $tmp = array();
                    $tmp["subject"] = $sub[$i];
                    if(isset($subjectNames[$sub[$i]])){
                        $tmp["subjectName"] = $subjectNames[$sub[$i]];
                    }

                    if(isset($subjectMaxMarks[$sub[$i]])){
                        $tmp["max_marks"] = $subjectMaxMarks[$sub[$i]];
                    }

                    if(isset($subjectMinMarks[$sub[$i]])){
                        $tmp["min_marks"] = $subjectMinMarks[$sub[$i]];
                    }

                    if(isset($subjectGrade[$sub[$i]])){
                        $tmp["gradeSystemId"] = $subjectGrade[$sub[$i]];
                    }

                    
                    if (!empty($mksab[$i])) {
                        $tmp["marks_abex"] = $mksab[$i];
                    } else {
                        $tmp["marks_abex"] = '';
                    }
                    $tmp["skill"] = $sl[$i];
                    $tmp["grade_id"] = $grd[$i];
                    $tmp["test_id"] = $tId[$i];

                    $getMaks = $mks[$i];
                    //echo $tmp["max_marks"].'</br>';
                    if (!empty($scalevalue)) {
                        $max_marks = $scalevalue;
                        //echo $sub[$i].'---'.$getMaks.'--'.$tmp["max_marks"].'--'.$max_marks.'</br>';
                        if (!empty($tmp["max_marks"]) && $tmp["max_marks"] != '0.00') {
                            $gm = ($getMaks / $tmp["max_marks"]) * $max_marks;
                            
                        } else {
                            $gm = $getMaks * $max_marks;
                        }
                        if (!empty($roundvalue)) {
                            $getmarks = round($gm, $roundvalue);
                        } else {
                            $getmarks = $gm;
                        }
                    } else {
                        $max_marks = $tmp["max_marks"];
                        if (!empty($roundvalue)) {
                            $getmarks = round($getMaks, $roundvalue);
                        } else {
                            $getmarks = $getMaks;
                        }
                    }
                    $tmp["marks"] = $getmarks;

                    // if ($resultSkillData) {
                    //     if (isset($resultSkillData[$pupilsightPersonID][$sub[$i]]["marks"])) {
                    //         $tmp["marks"] = $resultSkillData[$pupilsightPersonID][$sub[$i]]["marks"];
                    //     }
                    // }
                    

                    if ($resultSkillData) {
                        if (isset($resultSkillData[$pupilsightPersonID][$sub[$i]]["marks"])) {
                            $getMaks = $resultSkillData[$pupilsightPersonID][$sub[$i]]["marks"];
                            //echo $tmp["max_marks"].'</br>';
                            if (!empty($scalevalue)) {
                                $max_marks = $scalevalue;
                                //echo $sub[$i].'---'.$getMaks.'--'.$tmp["max_marks"].'--'.$max_marks.'</br>';
                                if (!empty($tmp["max_marks"]) && $tmp["max_marks"] != '0.00') {
                                    $gm = ($getMaks / $tmp["max_marks"]) * $max_marks;
                                    
                                } else {
                                    $gm = $getMaks * $max_marks;
                                }
                                if (!empty($roundvalue)) {
                                    $getmarks = round($gm, $roundvalue);
                                } else {
                                    $getmarks = $gm;
                                }
                            } else {
                                $max_marks = $tmp["max_marks"];
                                if (!empty($roundvalue)) {
                                    $getmarks = round($getMaks, $roundvalue);
                                } else {
                                    $getmarks = $getMaks;
                                }
                            }
                            $tmp["marks"] = $getmarks;
                        }
                        if (isset($resultSkillData[$pupilsightPersonID][$sub[$i]]["skill_configure"])) {
                            $tmp["skill_configure"] = $resultSkillData[$pupilsightPersonID][$sub[$i]]["skill_configure"];
                        } else {
                            $tmp["skill_configure"] = 'None';
                        }
                    } 

                    // echo '<pre>';
                    // print_r($tmp["marks"]);
                    // echo '</pre>';

                    if ($kountTmId > 1) {
                        $dt[$testid][$pupilsightPersonID][$sub[$i]] = $tmp;
                    } else {
                        $dt[$pupilsightPersonID][$sub[$i]] = $tmp;
                    }
                    $i++;
                }
            }
        }
        // $subOrder = array();
        // foreach($subjectNames as $key => $subName){
        //     $subOrder[$key] = $key;
        // }
        // $newdt = array();
        // $i = 0;
        // foreach($dt as $stuID => $ds){
        //     foreach($ds as $k => $d){
        //         // if(array_key_exists($k,$subOrder)) {
        //         //     echo $k.'</br>';
        //         //     $newdt[$stuID][$k] = $d;
        //         //     unset($d[$k]);
        //         // }
        //         $newdt[$stuID][$i] = $subOrder[$d[$k]-1];
        //         $i++;
        //     }
        // }
    } catch (Exception $ex) {
        print_r($ex);
    }

    
    // echo '<pre>';
    // print_r($newdt);
    // echo '</pre>';
    // die();
    return $dt;
}

function runMultiple($stuResultData, $testIDs, $final_formula, $final_formula_best_cal)
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
                            
                            if($final_formula == 'Average_Excluding_Ab_Ex'){
                                if($stuResultData[$testIDs[$i]][$studentid][$subid]["marks"] != '0.00'){
                                    $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$studentid][$subid]["marks"];
                                    $cnt++;
                                }
                            } else if($final_formula == 'Average_Excluding_Ab'){
                                
                                if($stuResultData[$testIDs[$i]][$studentid][$subid]["marks_abex"] != 'AB'){
                                    //echo $testIDs[$i].'--'.$subid.'--'.$stuResultData[$testIDs[$i]][$studentid][$subid]["marks_abex"].'--'.$stuResultData[$testIDs[$i]][$studentid][$subid]["marks"].'<br>';
                                    $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$studentid][$subid]["marks"];
                                    $cnt++;
                                }
                            } else if($final_formula == 'Average_Excluding_Ex'){
                                if($stuResultData[$testIDs[$i]][$studentid][$subid]["marks"] != '0.00'){
                                    $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$studentid][$subid]["marks"];
                                    $cnt++;
                                }
                            } else {
                                $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$studentid][$subid]["marks"];
                                $cnt++;
                            }
                            
                            // $mks = $stuResultData[$testIDs[$i]][$studentid][$subid]["marks"];
                            // $lastmks = $stuResultData[$testIDs[$maxPos]][$studentid][$subid]["marks"];
                            
                            // $fm = $mks + $lastmks;
                            // $stuResultData[$testIDs[$maxPos]][$studentid][$subid]["marks"] = $fm;
                        } 
                    } else {
                        // $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$studentid][$subid]["marks"];
                        // $cnt++;
                        //echo $testIDs[$i].'--'.$subid.'--'.$stuResultData[$testIDs[$i]][$studentid][$subid]["marks_abex"].'--'.$stuResultData[$testIDs[$i]][$studentid][$subid]["marks"].'<br>';
                        if($final_formula == 'Average_Excluding_Ab_Ex'){
                            if($stuResultData[$testIDs[$i]][$studentid][$subid]["marks"] != '0.00'){
                                $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$studentid][$subid]["marks"];
                                $cnt++;
                            }
                        } else if($final_formula == 'Average_Excluding_Ab'){
                            
                            if($stuResultData[$testIDs[$i]][$studentid][$subid]["marks_abex"] != 'AB'){
                                $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$studentid][$subid]["marks"];
                                $cnt++;
                            }
                        } else if($final_formula == 'Average_Excluding_Ex'){
                            if($stuResultData[$testIDs[$i]][$studentid][$subid]["marks"] != '0.00'){
                                $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$studentid][$subid]["marks"];
                                $cnt++;
                            }
                        } else {
                            $testmarks[$cnt] = $stuResultData[$testIDs[$i]][$studentid][$subid]["marks"];
                            $cnt++;
                        }
                        
                    }
                    $i++;
                }
                //print_r($testmarks);
                $getStudentMarksData = '';
                if($final_formula == 'Sum'){
                    $getStudentMarksData = sum($testmarks);
                } else if($final_formula == 'Sum_Excluding_Ab'){
                    $getStudentMarksData = sum($testmarks);
                } else if($final_formula == 'Sum_Excluding_Ex'){
                    $getStudentMarksData = sum($testmarks);
                } else if($final_formula == 'Sum_Excluding_Ab_Ex'){
                    $getStudentMarksData = sum($testmarks);
                } else if($final_formula == 'Average'){
                   $getStudentMarksData = avg($testmarks);
                } else if($final_formula == 'Average_Excluding_Ab'){
                    $getStudentMarksData = avg($testmarks);
                } else if($final_formula == 'Average_Excluding_Ex'){
                    $getStudentMarksData = avg($testmarks);
                } else if($final_formula == 'Average_Excluding_Ab_Ex'){
                    $getStudentMarksData = avg($testmarks);
                } else if($final_formula == 'Best_of_All'){
                    $getStudentMarksData = Best_of_All($testmarks);
                } else if($final_formula == 'Best_of_All_Excluding_Ex'){
                    $getStudentMarksData = Best_of_All($testmarks);
                } else if($final_formula == 'Best_of_All_Excluding_Ab'){
                    $getStudentMarksData = Best_of_All($testmarks);
                } else if($final_formula == 'Best_of_All_Excluding_Ab_Ex'){
                    $getStudentMarksData = Best_of_All($testmarks);
                } else if($final_formula == 'Second_Best_of_All'){
                   $getStudentMarksData = Third_Best_of_All($testmarks);
                } else if($final_formula == 'Second_Best_of_All_Excluding_Ex'){
                    $getStudentMarksData = Second_Best_of_All($testmarks);
                } else if($final_formula == 'Second_Best_of_All_Excluding_Ab'){
                    $getStudentMarksData = Second_Best_of_All($testmarks);
                } else if($final_formula == 'Second_Best_of_All_Excluding_Ab_Ex'){
                    $getStudentMarksData = Second_Best_of_All($testmarks);
                } else if($final_formula == 'Third_Best_of_All'){
                    $getStudentMarksData = Third_Best_of_All($testmarks);
                } else if($final_formula == 'Third_Best_of_All_Excluding_Ex'){
                    $getStudentMarksData = Third_Best_of_All($testmarks);
                } else if($final_formula == 'Third_Best_of_All_Excluding_Ab'){
                    $getStudentMarksData = Third_Best_of_All($testmarks);
                } else if($final_formula == 'Third_Best_of_All_Excluding_Ab_Ex'){
                    $getStudentMarksData = Third_Best_of_All($testmarks);
                } else if($final_formula == 'Best_of_Sum'){
                    $getStudentMarksData = Best_of_Sum($testmarks, $final_formula_best_cal);
                } else if($final_formula == 'Best_of_Sum_Excluding_Ab_Ex'){
                    $getStudentMarksData = Best_of_Sum($testmarks, $final_formula_best_cal);
                } else if($final_formula == 'Best_of_Average'){
                    $getStudentMarksData = Best_of_Average($testmarks, $final_formula_best_cal);
                } else if($final_formula == 'Best_of_Average_Excluding_Ab_Ex'){
                    $getStudentMarksData = Best_of_Average($testmarks, $final_formula_best_cal);
                }

                $stuResultData[$testIDs[$maxPos]][$studentid][$subid]["marks"] = $getStudentMarksData;
            }
            
        }
        // echo '<pre>';
        // print_r($stuResultData[$testIDs[$maxPos]]);
        // die();
        $dt = $stuResultData[$testIDs[$maxPos]];
        
    }
    return $dt;
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

function Best_of_Sum($marks, $final_formula_best_cal)
{
    rsort($marks);
    $mar = array_slice($marks, 0, $final_formula_best_cal);
    $len = count($mar);
    $i = 0;
    $fm = 0;
    while ($i < $len) {
        $fm += $marks[$i];
        $i++;
    }
    
    return $fm;
}

function Best_of_Average($marks, $final_formula_best_cal)
{
    $sumMarks = sum($marks, $final_formula_best_cal);
    $avgMarks = $sumMarks / $final_formula_best_cal;
    return $avgMarks;
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
    return $data;
}

function Second_Best_of_All($marks)
{
    sort($marks);
    $data = $marks[sizeof($marks)-2];
    return $data;
}

function Third_Best_of_All($marks)
{
    sort($marks);
    $data = $marks[sizeof($marks)-3];
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
                $dt[$pupilsightPersonID][$sub[$i]]["skill_configure"] = $skillConfigure;
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
    $sq = "select  a.pupilsightDepartmentID, b.subject_display_name from examinationSubjectToTest AS a LEFT JOIN subjectToClassCurriculum AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID where a.test_id='" . $testId . "' AND b.pupilsightSchoolYearID = " . $mappingData['pupilsightSchoolYearID'] . " AND b.pupilsightProgramID = " . $mappingData['pupilsightProgramID'] . " AND b.pupilsightYearGroupID = " . $mappingData['pupilsightYearGroupID'] . " ORDER BY b.pos ASC  ";
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

function getSubjectMaxMarks($connection2, $testId, $mappingData){
    $sq = "select  pupilsightDepartmentID, max_marks from examinationSubjectToTest where test_id='" . $testId . "' GROUP BY pupilsightDepartmentID  ";
    $result = $connection2->query($sq);
    $data = $result->fetchAll();
    $sub = array();
    if(!empty($data)){
        foreach($data as $d){
            $sub[$d['pupilsightDepartmentID']] = $d['max_marks'];
        }
    }
    // print_r($sub);
    // die();
    return $sub;
}

function getSubjectMinMarks($connection2, $testId, $mappingData){
    $sq = "select  pupilsightDepartmentID, min_marks from examinationSubjectToTest where test_id='" . $testId . "' GROUP BY pupilsightDepartmentID  ";
    $result = $connection2->query($sq);
    $data = $result->fetchAll();
    $sub = array();
    if(!empty($data)){
        foreach($data as $d){
            $sub[$d['pupilsightDepartmentID']] = $d['min_marks'];
        }
    }
    // print_r($sub);
    // die();
    return $sub;
}

function getSubjectGrade($connection2, $testId, $mappingData){
    $sq = "select pupilsightDepartmentID, gradeSystemId from examinationSubjectToTest where test_id='" . $testId . "' GROUP BY pupilsightDepartmentID  ";
    $result = $connection2->query($sq);
    $data = $result->fetchAll();
    $sub = array();
    if(!empty($data)){
        foreach($data as $d){
            $sub[$d['pupilsightDepartmentID']] = $d['gradeSystemId'];
        }
    }
    // print_r($sub);
    // die();
    return $sub;
}

function getGradeConfiguration($connection2){
    $sq = "select * from examinationGradeSystemConfiguration";
    $result = $connection2->query($sq);
    $data = $result->fetchAll();
    return $data;
}

function loadGradeConfiguration($gc){
    $dt = array();
    $i = 0;
    $len = count($gc);
    while($i < $len){
        $dt[$gc[$i]['id']] =  $gc[$i]['grade_name'];
        $i++;
    }
    return $dt;

}

function getGradeByMarks($gradeSystemId, $getmarks, $gc){
    $dt = array();
    $i = 0;
    $len = count($gc);
    $gradeName = '';
    while($i < $len){
        if($gc[$i]['gradeSystemId']){
            if($getmarks >= $gc[$i]['lower_limit'] && $getmarks <= $gc[$i]['upper_limit']){
                $gradeName = $gc[$i]['grade_name'];
                break;
            }
        }
        $i++;
    }
    return $gradeName;
}

function calculateComputed($gmsarr, $attrIds){
    $dt = array();
    $i = 0;
    $len = count($attrIds);
    $finalArray =  array();
    while($i < $len){
        foreach($gmsarr as $k => $gc){
            //echo $k.'--'.$attrIds[$i].'</br>';
            if($k == $attrIds[$i]){
                $dt[$k] = $gc;
            }
        }
        $i++;
    }
    $len = count($dt);
    $i = 1;
    // print_r($gmsarr);
    // print_r($attrIds);
    //die();
    if(isset($dt[$attrIds[0]])){
        foreach($dt[$attrIds[0]] as $k => $marks){
            $i = 1;
            $tmp = array();
            $tmp[0] = $marks;
            while($i < $len){
                $tmp[$i] = $dt[$attrIds[$i]][$k];
                $i++;
            }
            $finalArray[$k] = $tmp;
        }
    }
    return $finalArray;
}

function getAllSubjectName($connection2, $mappingData){
    $sq = "select pupilsightDepartmentID, subject_display_name from subjectToClassCurriculum  where pupilsightSchoolYearID = " . $mappingData['pupilsightSchoolYearID'] . " AND pupilsightProgramID = " . $mappingData['pupilsightProgramID'] . " AND pupilsightYearGroupID = " . $mappingData['pupilsightYearGroupID'] . "  ";
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

function getFinalComputedMarks($finalFormuala,  $getComputedMarks, $final_formula_best_cal){
    $computedData = '';
    if ($finalFormuala == 'Sum') {
        $computedData =  sum($getComputedMarks);
    } else if ($finalFormuala == 'Average') {
        $computedData =  avg($getComputedMarks);
    } else if ($finalFormuala == 'Best_of_All') {
        $computedData =  Best_of_All($getComputedMarks);
    } else if ($finalFormuala == 'Second_Best_of_All') {
        $computedData =  Second_Best_of_All($getComputedMarks);
    } else if ($finalFormuala == 'Third_Best_of_All') {
        $computedData =  Third_Best_of_All($getComputedMarks);
    } else if ($finalFormuala == 'Best_of_Sum') {
        $computedData =  Best_of_Sum($getComputedMarks, $final_formula_best_cal);
    } else if ($finalFormuala == 'Best_of_Average') {
        $computedData =  Best_of_Average($getComputedMarks, $final_formula_best_cal);
    }
    return $computedData;
}

function getRemarks($connection2, $test_master_id, $mappingData){
    $sqlt = 'SELECT test_id FROM examinationTestAssignClass WHERE test_master_id = ' . $test_master_id . ' AND pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND pupilsightYearGroupID = ' . $mappingData['pupilsightYearGroupID'] . ' AND pupilsightRollGroupID = ' . $mappingData['pupilsightRollGroupID'] . ' ';
    $resultt = $connection2->query($sqlt);
    $testdata = $resultt->fetch();
    if (!empty($testdata)) {
        $testid = $testdata['test_id'];
        $sqlmarks = 'SELECT a.test_id, a.pupilsightDepartmentID, b.subject_display_name, a.remarks, a.pupilsightPersonID FROM examinationMarksEntrybySubject AS a LEFT JOIN subjectToClassCurriculum AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID  WHERE  a.test_id = '.$testid.'  AND b.pupilsightSchoolYearID = ' . $mappingData['pupilsightSchoolYearID'] . ' AND b.pupilsightProgramID = ' . $mappingData['pupilsightProgramID'] . ' AND b.pupilsightYearGroupID = ' . $mappingData['pupilsightYearGroupID'] . ' AND a.skill_id = 0 ORDER BY b.pos ASC  ';
        $resultmarks = $connection2->query($sqlmarks);
        $rem = $resultmarks->fetchAll();
        
        $i = 0;
        $len = count($rem);
        $dt = array();
        while($i < $len){
            $dt[$rem[$i]['test_id']][$rem[$i]['pupilsightPersonID']][$rem[$i]['pupilsightDepartmentID']] = $rem[$i]['remarks'];
            $i++;
        }
        //print_r($dt);
        return $dt; 
    }
}
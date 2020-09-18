<?php

include $_SERVER['DOCUMENT_ROOT'].'/pupilsight.php';

require_once $_SERVER["DOCUMENT_ROOT"].'/vendor/phpoffice/phpword/bootstrap.php';


$test_id = $_GET['tid'];

$chksql = 'SELECT * FROM examinationTest WHERE id = '.$test_id.' ';
$resultchk = $connection2->query($chksql);
$chkTestData = $resultchk->fetch();
$sketch_id = $chkTestData['sketch_id'];

if(!empty($sketch_id)){
    $testname = $chkTestData['name'];

    $sqlfile = 'SELECT path, filename FROM examinationReportTemplateMaster WHERE id = '.$chkTestData['report_template_id'].' ';
    $resultfile = $connection2->query($sqlfile);
    $fileData = $resultfile->fetch();
    $filename = $fileData['filename'];

    $sqlsketch = 'SELECT a.id as erta_id, b.* FROM examinationReportTemplateAttributes AS a LEFT JOIN examinationReportTemplateConfiguration AS b ON a.ertc_id = b.id WHERE a.sketch_id = '.$sketch_id.' ';
    $resultsk = $connection2->query($sqlsketch);
    $sketchData = $resultsk->fetchAll();


    if(!empty($filename)){
        
        $file = $_SERVER["DOCUMENT_ROOT"]."/public/report_template/".$filename;
       
        //$sql = 'SELECT fr.relationship, ft.officialName as parent_name, ft.email as parent_email, ft.phone1 as parent_phone, b.officialName, b.pupilsightPersonID, b.gender, b.dob, b.address1, d.name as classname, e.name as sectionname FROM `examinationMarksEntrybySubject` AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS c ON a.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS d ON c.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS e ON c.pupilsightRollGroupID = e.pupilsightRollGroupID LEFT JOIN pupilsightFamilyRelationship AS fr ON b.pupilsightPersonID = fr.pupilsightPersonID2 LEFT JOIN pupilsightPerson AS ft ON fr.pupilsightPersonID1 = ft.pupilsightPersonID  WHERE a.test_id = '.$test_id.' GROUP BY a.pupilsightPersonID ';

        $sql = "call studentDetails(".$test_id.")";

        // $stmt2 = db::db()->prepare($sql);
        // $stmt2->execute();
        // $rs2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        // $result->closeCursor();

        $result = $connection2->query($sql);
        $testdata = $result->fetchAll();

        $result->closeCursor();
        // echo '<pre>';
        // print_r($testdata);
        // echo '</pre>';
        

        foreach($testdata as $td){
            $relationship = $td['relationship'];
            if($relationship == 'Father'){
                $father_name = $td['parent_name'];
                $father_email = $td['parent_email'];
                $father_phone = $td['parent_phone'];

                $sqlm = 'SELECT ft.officialName as parent_name, ft.email as parent_email, ft.phone1 as parent_phone FROM pupilsightFamilyRelationship AS fr LEFT JOIN pupilsightPerson AS ft ON fr.pupilsightPersonID1 = ft.pupilsightPersonID  WHERE fr.pupilsightPersonID2 = '.$td['pupilsightPersonID'].' AND fr.relationship = "Mother" ';
                $resultm = $connection2->query($sqlm);
                $pardata = $resultm->fetch();
                if(!empty($pardata)){
                    $mother_name = $pardata['parent_name'];
                    $mother_email = $pardata['parent_email'];
                    $mother_phone = $pardata['parent_phone'];
                } else {
                    $mother_name = '';
                    $mother_email = '';
                    $mother_phone = '';
                }
            } 

            if ($relationship == 'Mother'){
                $mother_name = $td['parent_name'];
                $mother_email = $td['parent_email'];
                $mother_phone = $td['parent_phone'];

                $sqlm = 'SELECT ft.officialName as parent_name, ft.email as parent_email, ft.phone1 as parent_phone FROM pupilsightFamilyRelationship AS fr LEFT JOIN pupilsightPerson AS ft ON fr.pupilsightPersonID1 = ft.pupilsightPersonID  WHERE fr.pupilsightPersonID2 = '.$td['pupilsightPersonID'].' AND fr.relationship = "Father" ';
                $resultm = $connection2->query($sqlm);
                $pardata = $resultm->fetch();
                if(!empty($pardata)){
                    $father_name = $pardata['parent_name'];
                    $father_email = $pardata['parent_email'];
                    $father_phone = $pardata['parent_phone'];
                } else {
                    $father_name = '';
                    $father_email = '';
                    $father_phone = '';
                }

            } 


            $testclassSectionName = $testname.'_'.$td['classname'].'_'.$td['sectionname'];
            unset($sub_marks);
            unset($sub_grade);
            $savedocsx = '';
            $filename = '';
        
            $phpword = new \PhpOffice\PhpWord\TemplateProcessor($file);

            $reportcolumn = array(
                'student_id' => $td['pupilsightPersonID'],
                'student_name' => $td['officialName'],
                'student_gender' => $td['gender'],
                'student_dob' => $td['dob'],
                'student_address' => $td['address1'],
                'father_name' => $father_name,
                'father_email' => $father_email,
                'father_phone' => $father_phone,
                'mother_name' => $mother_name,
                'mother_email' => $mother_email,
                'mother_phone' => $mother_phone,
                'class' => $td['classname'],
                'section' => $td['sectionname']
            );
            foreach($sketchData as $sd){

                if (array_key_exists($sd['report_column_word'],$reportcolumn)){
                    $phpword->setValue($sd['report_column_word'], $reportcolumn[$sd['report_column_word']]);
                } 

                $phpword->setValue('date', date("d-M-Y"));

                if($sd['report_column_word'] == 'marks_obtained'){

                    $sqlp = 'SELECT a.*, b.name FROM examinationReportTemplatePluginAttributeMapping AS a LEFT JOIN examinationReportTemplatePlugin AS b ON a.plugin_id = b.id WHERE a.erta_id = '.$sd['erta_id'].' ';
                    $resultp = $connection2->query($sqlp);
                    $plugindata = $resultp->fetch();

                    if($plugindata['name'] == 'Round'){
                        $roundvalue = $plugindata['plugin_val'];
                    } else {
                        $roundvalue = '';
                    }

                    $sqlf = 'SELECT a.*, b.name FROM examinationReportTemplateFormulaAttributeMapping AS a LEFT JOIN examinationReportTemplateFormula AS b ON a.formula_id = b.id WHERE a.erta_id = '.$sd['erta_id'].' ';
                    $resultf = $connection2->query($sqlf);
                    $formuladata = $resultf->fetch();
                    // print_r($formuladata);
                    if($formuladata['name'] == 'Scale'){
                        $scalevalue = $formuladata['formula_val'];
                    } else {
                        $scalevalue = '';
                    }

                    $subsql = 'SELECT a.pupilsightDepartmentID, a.max_marks, a.skill_configure, a.gradeSystemId, b.name AS subject FROM examinationSubjectToTest AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID WHERE a.test_id = '.$test_id.' AND a.is_tested = 1 GROUP BY a.pupilsightDepartmentID ';
                    $resultsub = $connection2->query($subsql);
                    $testdatasub = $resultsub->fetchAll();

                    $cnt = 1;
                    foreach($testdatasub as $k => $tsub){
                        $gsid = $tsub['gradeSystemId'];
                        if($tsub['skill_configure'] == 'Average'){
                            $sqlmarks = 'SELECT AVG(marks_obtained) AS getmarks FROM examinationMarksEntrybySubject WHERE pupilsightDepartmentID = '.$tsub['pupilsightDepartmentID'].' AND pupilsightPersonID = '.$td['pupilsightPersonID'].' AND test_id = '.$test_id.' ';
                        } else if($tsub['skill_configure'] == 'Sum'){
                            $sqlmarks = 'SELECT SUM(marks_obtained) AS getmarks FROM examinationMarksEntrybySubject WHERE pupilsightDepartmentID = '.$tsub['pupilsightDepartmentID'].' AND pupilsightPersonID = '.$td['pupilsightPersonID'].' AND test_id = '.$test_id.' ';
                        } else {
                            $sqlmarks = 'SELECT marks_obtained AS getmarks FROM examinationMarksEntrybySubject WHERE pupilsightDepartmentID = '.$tsub['pupilsightDepartmentID'].' AND pupilsightPersonID = '.$td['pupilsightPersonID'].' AND test_id = '.$test_id.' ';
                        }
                        $resultmarks = $connection2->query($sqlmarks);
                        $marksdatasub = $resultmarks->fetch();
                        //echo $scalevalue;
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
                            $sqlg = 'SELECT grade_name FROM examinationGradeSystemConfiguration  WHERE gradeSystemId="'.$tsub['gradeSystemId'].'" AND  ('.$getmarks.' BETWEEN `lower_limit` AND `upper_limit`)';
                            $resultg = $connection2->query($sqlg);
                            $grade = $resultg->fetch();
                            $gradename = $grade['grade_name'];
                        } else {
                            $gradename = '';
                        }
                        

                        $testdatasub[$k]['marks_obtained'] = $getmarks;
                        $testdatasub[$k]['grade'] = $gradename;

                        $sub_marks[] = array(
                            "serial.all"=>$cnt,
                            "particulars.all"=>$tsub['subject'],
                            "maxmarks.all"=>$max_marks,
                            "marksobtained.all"=>$getmarks,
                            "gradeobtained.all"=>$gradename
                        );
                        $cnt++;
                    }
                    // echo '<pre>';
                    // print_r($sub_marks);
                    // echo '</pre>';
                    // die();
                    $gradesql = 'SELECT * FROM examinationGradeSystemConfiguration WHERE gradeSystemId = '.$gsid.' ORDER BY id DESC ';
                    $resultgr = $connection2->query($gradesql);
                    $gradeConfig = $resultgr->fetchAll();
                    $cntn = 1;
                    foreach($gradeConfig as $gc){
                        $gconfig = $gc['lower_limit'].' to '.$gc['upper_limit'].' - '.$gc['grade_name'];
                        $sub_grade[] = array(
                            "grade_configuration.all"=>$gconfig
                        );
                        $cntn++;
                    }

                    
                    if(!empty($sub_marks)){
                        $phpword->cloneRowAndSetValues('serial.all', $sub_marks);
                    }

                    if(!empty($sub_grade)){
                        $phpword->cloneRowAndSetValues('grade_configuration.all', $sub_grade);
                    }
                }
            
            }
            
            //die();

            $savedocsx = $_SERVER["DOCUMENT_ROOT"]."/public/report_template/report_card/".$td['officialName'].'-'.$td['pupilsightPersonID'].".docx";

            $filename = $td['officialName'].'-'.$td['pupilsightPersonID'].".docx";
            //echo $savedocsx;
            
            $phpword->saveAs($savedocsx);
            
        }
        //die();

        $callback = $_SESSION[$guid]['absoluteURL']."/zip.php?zipname=".$testclassSectionName."";

        header('Location: '.$callback);
        exit;

    } 
} else {
    $sqlac = 'SELECT b.name as academicYear, a.name as testname, a.report_template_id, a.sketch_id, c.path, c.filename FROM examinationTest AS a LEFT JOIN pupilsightSchoolYear AS b ON a.pupilsightSchoolYearID = b.pupilsightSchoolYearID LEFT JOIN examinationReportTemplateMaster AS c ON a.report_template_id = c.id WHERE a.id = '.$test_id.' ';
    $resultac = $connection2->query($sqlac);
    $testdataAc = $resultac->fetch();
    $academicYear = $testdataAc['academicYear'];
    $testname = $testdataAc['testname'];



    if(!empty($testdataAc['filename'])){
        // echo $file = $_SERVER["DOCUMENT_ROOT"]."/pupilsight/public/report_template/Default_Report_Template.docx";
        $file = $_SERVER["DOCUMENT_ROOT"]."/public/report_template/".$testdataAc['filename'];
        //$file = "/var/www/pupilsight/public/public/report_template/1596554854_Default_Report_Template.docx";


        $sql = 'SELECT b.officialName, b.pupilsightPersonID, d.name as classname, e.name as sectionname FROM `examinationMarksEntrybySubject` AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS c ON a.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS d ON c.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS e ON c.pupilsightRollGroupID = e.pupilsightRollGroupID WHERE a.test_id = '.$test_id.' GROUP BY a.pupilsightPersonID ';

        $result = $connection2->query($sql);
        $testdata = $result->fetchAll();
    

        foreach($testdata as $td){
            $testclassSectionName = $testname.'_'.$td['classname'].'_'.$td['sectionname'];
            unset($sub_marks);
            unset($sub_grade);
            $savedocsx = '';
            $filename = '';
        
            $phpword = new \PhpOffice\PhpWord\TemplateProcessor($file);
        
            $classSection = $td['classname'].' - '.$td['sectionname'];
            $dts = array(
                "academic_year" => $academicYear,
                "date" => date("d-M-Y"),
                "student_name" => $td['officialName'],
                "student_id" => $td['pupilsightPersonID'],
                "class_section_name" => $classSection,
            );
            
            // $dts["total"]=$dts["transcation_amount"];
            foreach ($dts as $key => $value) {
                $phpword->setValue($key, $value);
            }

            $subsql = 'SELECT a.pupilsightDepartmentID, a.max_marks, a.skill_configure, a.gradeSystemId, b.name AS subject FROM examinationSubjectToTest AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID WHERE test_id = '.$test_id.' AND skill_configure != "" GROUP BY a.pupilsightDepartmentID ';
            $resultsub = $connection2->query($subsql);
            $testdatasub = $resultsub->fetchAll();

            $cnt = 1;
            foreach($testdatasub as $k => $tsub){
                $gsid = $tsub['gradeSystemId'];
                if($tsub['skill_configure'] == 'Average'){
                    $sqlmarks = 'SELECT AVG(marks_obtained) AS getmarks FROM examinationMarksEntrybySubject WHERE pupilsightDepartmentID = '.$tsub['pupilsightDepartmentID'].' AND pupilsightPersonID = '.$td['pupilsightPersonID'].' AND test_id = '.$test_id.' ';
                } else if($tsub['skill_configure'] == 'Sum'){
                    $sqlmarks = 'SELECT SUM(marks_obtained) AS getmarks FROM examinationMarksEntrybySubject WHERE pupilsightDepartmentID = '.$tsub['pupilsightDepartmentID'].' AND pupilsightPersonID = '.$td['pupilsightPersonID'].' AND test_id = '.$test_id.' ';
                } else {
                    $sqlmarks = 'SELECT marks_obtained AS getmarks FROM examinationMarksEntrybySubject WHERE pupilsightDepartmentID = '.$tsub['pupilsightDepartmentID'].' AND pupilsightPersonID = '.$td['pupilsightPersonID'].' AND test_id = '.$test_id.' ';
                }
                $resultmarks = $connection2->query($sqlmarks);
                $marksdatasub = $resultmarks->fetch();
                $getmarks = round($marksdatasub['getmarks']);

                if(!empty($getmarks)){
                    $sqlg = 'SELECT grade_name FROM examinationGradeSystemConfiguration  WHERE gradeSystemId="'.$tsub['gradeSystemId'].'" AND  ('.$getmarks.' BETWEEN `lower_limit` AND `upper_limit`)';
                    $resultg = $connection2->query($sqlg);
                    $grade = $resultg->fetch();
                    $gradename = $grade['grade_name'];
                } else {
                    $gradename = '';
                }
                

                $testdatasub[$k]['marks_obtained'] = $getmarks;
                $testdatasub[$k]['grade'] = $gradename;

                $sub_marks[] = array(
                    "serial.all"=>$cnt,
                    "particulars.all"=>$tsub['subject'],
                    "maxmarks.all"=>$tsub["max_marks"],
                    "marksobtained.all"=>$getmarks,
                    "gradeobtained.all"=>$gradename
                );
                $cnt++;
            }

            $gradesql = 'SELECT * FROM examinationGradeSystemConfiguration WHERE gradeSystemId = '.$gsid.' ORDER BY id DESC ';
            $resultgr = $connection2->query($gradesql);
            $gradeConfig = $resultgr->fetchAll();
            $cntn = 1;
            foreach($gradeConfig as $gc){
                $gconfig = $gc['lower_limit'].' to '.$gc['upper_limit'].' - '.$gc['grade_name'];
                $sub_grade[] = array(
                    "grade_configuration.all"=>$gconfig
                );
                $cntn++;
            }

            
            if(!empty($sub_marks)){
                $phpword->cloneRowAndSetValues('serial.all', $sub_marks);
            }

            if(!empty($sub_grade)){
                $phpword->cloneRowAndSetValues('grade_configuration.all', $sub_grade);
            }

            $savedocsx = $_SERVER["DOCUMENT_ROOT"]."/public/report_template/report_card/".$td['officialName'].'-'.$td['pupilsightPersonID'].".docx";

            $filename = $td['officialName'].'-'.$td['pupilsightPersonID'].".docx";
            //echo $savedocsx;
            
            $phpword->saveAs($savedocsx);

        }

        $callback = $_SESSION[$guid]['absoluteURL']."/zip.php?zipname=".$testclassSectionName."";

        header('Location: '.$callback);
        exit;

    } 
}




?>
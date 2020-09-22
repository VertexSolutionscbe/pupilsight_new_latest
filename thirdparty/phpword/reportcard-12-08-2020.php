<?php

include $_SERVER['DOCUMENT_ROOT'].'/pupilsight.php';

require_once $_SERVER["DOCUMENT_ROOT"].'/vendor/phpoffice/phpword/bootstrap.php';


$test_id = $_GET['tid'];

$sqlac = 'SELECT b.name as academicYear, a.name as testname, a.report_template_id, c.path, c.filename FROM examinationTest AS a LEFT JOIN pupilsightSchoolYear AS b ON a.pupilsightSchoolYearID = b.pupilsightSchoolYearID LEFT JOIN examinationReportTemplateMaster AS c ON a.report_template_id = c.id WHERE a.id = '.$test_id.' ';
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

} else {
    exit;
}


?>
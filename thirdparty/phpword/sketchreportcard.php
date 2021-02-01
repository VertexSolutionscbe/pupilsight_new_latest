<?php

include $_SERVER['DOCUMENT_ROOT'].'/pupilsight.php';

require_once $_SERVER["DOCUMENT_ROOT"].'/vendor/phpoffice/phpword/bootstrap.php';


$id = $_GET['id'];
$skgid = $_GET['skgid'];
$stuid = $_GET['stuid'];


if(!empty($stuid)){
    $sql = 'SELECT GROUP_CONCAT(DISTINCT a.pupilsightPersonId) AS stuid, b.sketch_name, b.template_path, b.template_filename  FROM examinationReportTemplateSketchData AS a LEFT JOIN examinationReportTemplateSketch AS b ON a.sketch_id = b.id WHERE sketch_id = '.$id.' AND sketch_generate_id IN ('.$skgid.') AND pupilsightPersonID IN ('.$stuid.') ';
} else {
    $sql = 'SELECT GROUP_CONCAT(DISTINCT a.pupilsightPersonId) AS stuid, b.sketch_name, b.template_path, b.template_filename  FROM examinationReportTemplateSketchData AS a LEFT JOIN examinationReportTemplateSketch AS b ON a.sketch_id = b.id WHERE sketch_id = '.$id.' AND sketch_generate_id IN ('.$skgid.') ';
}



$result = $connection2->query($sql);
$sketchGroupData = $result->fetch();

$sketchName = $sketchGroupData['sketch_name'];
$studentId = explode(',', $sketchGroupData['stuid']);
//$filename = $sketchGroupData['template_filename'];

// echo '<pre>';
// print_r($sketchGroupData);
// echo '</pre>';
// die();
if(!empty($sketchGroupData)){
    //echo $file = $_SERVER["DOCUMENT_ROOT"]."/public/sketch_template/".$filename;
    //$file = $_SERVER["DOCUMENT_ROOT"]."/pupilsight/public/report_template/1597834900_sketch_Template.docx";

    foreach($studentId as $sgd){
        $pupilsightPersonID = $sgd;

        $sqln = 'SELECT a.officialName, b.* FROM pupilsightPerson AS a LEFT JOIN pupilsightStudentEnrolment AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightPersonID = '.$pupilsightPersonID.' ';
        $resultn = $connection2->query($sqln);
        $stuname = $resultn->fetch();

        $pupilsightSchoolYearID = $stuname['pupilsightSchoolYearID'];
        $pupilsightProgramID = $stuname['pupilsightProgramID'];
        $pupilsightYearGroupID = $stuname['pupilsightYearGroupID'];

        $sqlf = 'SELECT template_filename FROM examinationReportSketchTemplateMaster WHERE sketch_id = '.$id.' AND pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND pupilsightProgramID = '.$pupilsightProgramID.' AND pupilsightYearGroupID = '.$pupilsightYearGroupID.' ';
        $resultf = $connection2->query($sqlf);
        $fileData = $resultf->fetch();

        if(!empty($fileData['template_filename'])){
            $file = $_SERVER["DOCUMENT_ROOT"]."/public/sketch_template/".$fileData['template_filename'];

            $studentName = $stuname['officialName'];

            $phpword = new \PhpOffice\PhpWord\TemplateProcessor($file);

            $sqldata = 'SELECT * FROM examinationReportTemplateSketchData WHERE sketch_id = '.$id.' AND pupilsightPersonId = '.$pupilsightPersonID.' ';
            $resultdata = $connection2->query($sqldata);
            $sketchStudentData = $resultdata->fetchAll();

            foreach($sketchStudentData as $sd){
                $attrValue = $sd['attribute_value'];
                if($sd['attribute_type'] == 'signature' && !empty($attrValue)){
                    $attrname = str_replace("#signature", "",$sd['attribute_name']);
                    $phpword->setImageValue($attrname, $attrValue);
                } else if($sd['attribute_type'] == 'photo' && !empty($attrValue)){
                    $attrname = str_replace("#photo", "",$sd['attribute_name']);
                    $phpword->setImageValue($attrname, $attrValue);
                } else {
                    $phpword->setValue($sd['attribute_name'], $attrValue);
                }
            }
                

            $savedocsx = $_SERVER["DOCUMENT_ROOT"]."/public/sketch_template/report_card/".$studentName.'-'.$pupilsightPersonID.".docx";

            $phpword->saveAs($savedocsx);
        } 
    }

    $callback = $_SESSION[$guid]['absoluteURL']."/zipsketch.php?zipname=".$sketchName."";

    header('Location: '.$callback);
    exit;
    
}

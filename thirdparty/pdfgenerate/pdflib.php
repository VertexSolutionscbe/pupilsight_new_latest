<?php

include $_SERVER['DOCUMENT_ROOT'].'/pupilsight.php';
require_once($_SERVER['DOCUMENT_ROOT'] . '/pdflib.php');

$pdflib = new PDFLib();

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
$formData = array();
if(!empty($sketchGroupData)){
    //echo $file = $_SERVER["DOCUMENT_ROOT"]."/public/sketch_template/".$filename;
    //$file = $_SERVER["DOCUMENT_ROOT"]."/pupilsight/public/report_template/1597834900_sketch_Template.docx";

    foreach($studentId as $sgd){
        unset($formData);
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

            $sqldata = 'SELECT * FROM examinationReportTemplateSketchData WHERE sketch_id = '.$id.' AND pupilsightPersonId = '.$pupilsightPersonID.' ';
            $resultdata = $connection2->query($sqldata);
            $sketchStudentData = $resultdata->fetchAll();

            foreach($sketchStudentData as $sd){
                $attrValue = $sd['attribute_value'];
                $attrName = $sd['attribute_name'];
                $formData[$attrName] = $sd['attribute_value'];
            }

            $imgData[0] = [
                'pageno' => 2,
                'src' => $_SERVER['DOCUMENT_ROOT'] . "/debug/test1.jpg",
                'x' => 100,
                'y' => 200,
                'width' => 20,
                'height' => 20
            ];
            
            $imgData[1] = [
                'pageno' => 1,
                'src' => $_SERVER['DOCUMENT_ROOT'] . "/debug/test2.jpg",
                'x' => 100,
                'y' => 200,
                'width' => 20,
                'height' => 20
            ];

            $templateFileName = $file;
            $outFileName = $_SERVER['DOCUMENT_ROOT'] . '/thirdparty/pdfgenerate/'.$studentName.'.pdf';
            $pdflib->generate($templateFileName, $outFileName, $formData, $imgData, TRUE);
             
        } 
    }

    // echo '<pre>';
    // print_r($formData);
    // echo '</pre>';
    //die();

    // $callback = $_SESSION[$guid]['absoluteURL']."/zipsketch.php?zipname=".$sketchName."";

    // header('Location: '.$callback);
    exit;
    
}


// require_once($_SERVER['DOCUMENT_ROOT'] . '/pupilsight/pdflib.php');
// $formData = [
//     'student_name' => "bikash",
//     'student_dob' => ' test dob',
//     'student_class' => 'test class',
//     'student_id' => ' test id',
//     'student_mother_name' => 'test mother name',
//     'student_father_name' => ' test father',
//     'student_address' => 'test address'
// ];

// $imgData[0] = [
//     'pageno' => 2,
//     'src' => $_SERVER['DOCUMENT_ROOT'] . "/pupilsight/debug/test1.jpg",
//     'x' => 100,
//     'y' => 200,
//     'width' => 20,
//     'height' => 20
// ];

// $imgData[1] = [
//     'pageno' => 1,
//     'src' => $_SERVER['DOCUMENT_ROOT'] . "/pupilsight/debug/test2.jpg",
//     'x' => 100,
//     'y' => 200,
//     'width' => 20,
//     'height' => 20
// ];

// $templateFileName = $_SERVER['DOCUMENT_ROOT'] . '/pupilsight/debug/' . 'Test_Template1_New.pdf';
// $outFileName = $_SERVER['DOCUMENT_ROOT'] . '/pupilsight/debug/' . 'Test_Template_out1.pdf';
// generate($templateFileName, $outFileName, $formData, $imgData, TRUE, TRUE);

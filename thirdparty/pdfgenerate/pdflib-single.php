<?php

include $_SERVER['DOCUMENT_ROOT'] . '/pupilsight.php';
require_once($_SERVER['DOCUMENT_ROOT'] . '/pdflib.php');

$pdfLib = new PDFLib();
$pdfLib->reset();

$id = $_GET['id'];
$skgid = $_GET['skgid'];
$stuid = $_GET['stuid'];


if (!empty($stuid)) {
    $sql = 'SELECT GROUP_CONCAT(DISTINCT a.pupilsightPersonId) AS stuid, b.sketch_name, b.template_path, b.template_filename  FROM examinationReportTemplateSketchData AS a LEFT JOIN examinationReportTemplateSketch AS b ON a.sketch_id = b.id WHERE sketch_id = ' . $id . ' AND sketch_generate_id IN (' . $skgid . ') AND pupilsightPersonID IN (' . $stuid . ') ';
} else {
    $sql = 'SELECT GROUP_CONCAT(DISTINCT a.pupilsightPersonId) AS stuid, b.sketch_name, b.template_path, b.template_filename  FROM examinationReportTemplateSketchData AS a LEFT JOIN examinationReportTemplateSketch AS b ON a.sketch_id = b.id WHERE sketch_id = ' . $id . ' AND sketch_generate_id IN (' . $skgid . ') ';
}


$result = $connection2->query($sql);
$sketchGroupData = $result->fetch();

$sketchName = $sketchGroupData['sketch_name'];
$studentId = explode(',', $sketchGroupData['stuid']);
$formData = array();
if (!empty($sketchGroupData)) {
    //echo $file = $_SERVER["DOCUMENT_ROOT"]."/public/sketch_template/".$filename;
    //$file = $_SERVER["DOCUMENT_ROOT"]."/pupilsight/public/report_template/1597834900_sketch_Template.docx";

    foreach ($studentId as $sgd) {
        unset($formData);
        $pupilsightPersonID = $sgd;

        $sqln = 'SELECT a.officialName, b.* FROM pupilsightPerson AS a LEFT JOIN pupilsightStudentEnrolment AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightPersonID = ' . $pupilsightPersonID . ' ';
        $resultn = $connection2->query($sqln);
        $stuname = $resultn->fetch();

        $pupilsightSchoolYearID = $stuname['pupilsightSchoolYearID'];
        $pupilsightProgramID = $stuname['pupilsightProgramID'];
        $pupilsightYearGroupID = $stuname['pupilsightYearGroupID'];

        $sqlf = 'SELECT template_filename FROM examinationReportSketchTemplateMaster WHERE sketch_id = ' . $id . ' AND pupilsightSchoolYearID = ' . $pupilsightSchoolYearID . ' AND pupilsightProgramID = ' . $pupilsightProgramID . ' AND pupilsightYearGroupID = ' . $pupilsightYearGroupID . ' ';
        $resultf = $connection2->query($sqlf);
        $fileData = $resultf->fetch();

        if (!empty($fileData['template_filename'])) {
            $file = $_SERVER["DOCUMENT_ROOT"] . "/public/sketch_template/" . $fileData['template_filename'];

            $studentName = $stuname['officialName'];

            $sqldata = 'SELECT * FROM examinationReportTemplateSketchData WHERE sketch_id = ' . $id . ' AND pupilsightPersonId = ' . $pupilsightPersonID . ' ';
            $resultdata = $connection2->query($sqldata);
            $sketchStudentData = $resultdata->fetchAll();

            $imgData = array();
            $k = 0;
            foreach ($sketchStudentData as $sd) {
                $attrValue = $sd['attribute_value'];
                $attrName = $sd['attribute_name'];
                $formData[$attrName] = $sd['attribute_value'];
                if ($sd['attribute_type'] == 'photo') {
                    $attrName = str_replace('#photo', '', $attrName);
                    $sql = 'SELECT b.* FROM examinationReportTemplateAttributes AS a LEFT JOIN examinationReportSketchConfigureImage AS b ON a.id = b.attr_id WHERE a.attribute_name = "' . $attrName . '" AND a.sketch_id = ' . $id . ' AND b.sketch_id = ' . $id . ' ';
                    $result = $connection2->query($sql);
                    $imgStuData = $result->fetch();
                    if (!empty($imgStuData)) {
                        $imgData[$k]['pageno'] = $imgStuData['page_no'];
                        $imgData[$k]['src'] = $sd['attribute_value'];
                        $imgData[$k]['x'] = $imgStuData['x'];
                        $imgData[$k]['y'] = $imgStuData['y'];
                        $imgData[$k]['width'] = $imgStuData['width'];
                        $imgData[$k]['height'] = $imgStuData['height'];
                    }
                    $k++;
                }

                if ($sd['attribute_type'] == 'signature') {
                    $attrName = str_replace('#signature', '', $attrName);
                    $sql = 'SELECT b.* FROM examinationReportTemplateAttributes AS a LEFT JOIN examinationReportSketchConfigureImage AS b ON a.id = b.attr_id WHERE a.attribute_name = "' . $attrName . '" AND a.sketch_id = ' . $id . ' AND b.sketch_id = ' . $id . ' ';
                    $result = $connection2->query($sql);
                    $imgStuData = $result->fetch();
                    if (!empty($imgStuData)) {
                        $imgData[$k]['pageno'] = $imgStuData['page_no'];
                        $imgData[$k]['src'] = $sd['attribute_value'];
                        $imgData[$k]['x'] = $imgStuData['x'];
                        $imgData[$k]['y'] = $imgStuData['y'];
                        $imgData[$k]['width'] = $imgStuData['width'];
                        $imgData[$k]['height'] = $imgStuData['height'];
                    }
                    $k++;
                }
            }

            // echo '<pre>';
            // print_r($imgData);
            // echo '</pre>';

            $templateFileName = $file;
            $stname = preg_replace("/[^a-zA-Z]+/", "_", $studentName);
            $outFileName = $_SERVER['DOCUMENT_ROOT'] . '/thirdparty/pdfgenerate/files/' . $stname . '.pdf';
            $pdfLib->generate($templateFileName, $outFileName, $formData, $imgData);

        }
    }
    $pdfLib->download();
    //$pdfLib->createZipAndDownload("download" . rand(10, 90) . ".zip");
    // $pdfLib->createZipAndDownload("download.zip");
    // $pdfLib->deleteSource();
    exit;
}

<?php
include_once '../vendor/autoload.php';
include_once 'w2f/adminLib.php';
//include $_SERVER["DOCUMENT_ROOT"] . '/pdf_convert.php';
$adminlib = new adminlib();
session_start();

$cid = $_GET['cid'];
$submissionId = $_SESSION['submissionId'];
$applicantId = explode(',', $submissionId);

$sqlpt = "SELECT template_path, template_filename FROM campaign WHERE id = ".$cid." ";
$valuept = database::doSelectOne($sqlpt);

$file = $valuept['template_path'];

$sqlf = 'Select b.form_fields FROM campaign AS a LEFT JOIN wp_fluentform_forms AS b ON a.form_id = b.id WHERE a.id = '.$cid.' ';
$fluent = database::doSelectOne($sqlf);
 
$field = json_decode($fluent['form_fields']);
$fields = array();

$arrHeader = array();
foreach($field as $fe){
    foreach($fe as $f){
        if(!empty($f->attributes)){
            $arrHeader[] = $f->attributes->name;
        }
    }
}

if(!empty($file)){
    $arr = array();
    $files = array();
    foreach($applicantId as $aid){
        $phpword = new \PhpOffice\PhpWord\TemplateProcessor($file);

        $sqla = "select application_id FROM wp_fluentform_submissions  where id = ".$aid." ";
        $applicationData = database::doSelectOne($sqla);
        

        $sql = "select field_name, field_value FROM wp_fluentform_entry_details  where submission_id = ".$aid." ";
        $rowdata = database::doSelect($sql);
        
        foreach($rowdata as $key => $value) {
            try {
                $arr[$value['field_name']] = $value['field_value'];
            } catch (Exception $ex) {
            }
        }

        foreach($arrHeader as $k=>$ah){
            if(array_key_exists($ah,$arr)){
                if($ah == 'file-upload' || $ah == 'image-upload'){
                    $attrValue = $arr[$ah];
                    try {
                        $phpword->setImageValue($ah, $attrValue);
                    } catch (Exception $ex) {
                    }
                } else {
                    try {
                        $phpword->setValue($ah, $arr[$ah]);
                    } catch (Exception $ex) {
                    }
                }
                
            } else {
                try {
                    $phpword->setValue($ah, '');
                } catch (Exception $ex) {
                }
            }
            
        }

        // echo '<pre>';
        // print_r($newarr);
        // echo '</pre>';
        // die();
        if(!empty($applicationData['application_id'])){
            $fname = $applicationData['application_id'];
        } else {
            $fname = $aid;
        }
        $date = date('y-m-d');
        $phpword->setValue('application_no', $fname);
        $phpword->setValue('application_date', $date);

        // $savedocsx = $_SERVER["DOCUMENT_ROOT"]."/public/applicationpdf/parent/".$fname.".docx";
        // $filename = $fname.".docx";
        // $phpword->saveAs($savedocsx);

        $fileName = $fname . ".docx";
        $inFilePath = $_SERVER["DOCUMENT_ROOT"] . "/pupilsight/public/applicationpdf/parent/";
        $savedocsx = $inFilePath . $fileName;
        //$savedocsx = $_SERVER["DOCUMENT_ROOT"]."/public/receipts/".$dts["transactionId"].".docx";
        //echo $savedocsx;
        $phpword->saveAs($savedocsx);

        //convert($fileName, $inFilePath, $inFilePath, FALSE, TRUE);

        $pdfFilename = $fname . ".docx";

        header("Content-Disposition: attachment; filename=".$pdfFilename." ");
        readfile($savedocsx); 
        //unlink($savedocsx);
    }

}

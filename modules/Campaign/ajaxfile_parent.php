<?php
include '../../pupilsight.php';
include $_SERVER["DOCUMENT_ROOT"] . '/pdf_convert.php';


$cid = $_GET['cid'];
$submissionId = $_SESSION['submissionId'];
$applicantId = explode(',', $submissionId);

$sqlpt = "SELECT template_path, template_filename FROM campaign WHERE id = " . $cid . " ";
$resultpt = $connection2->query($sqlpt);
$valuept = $resultpt->fetch();
$file = $valuept['template_path'];

$sqlf = 'Select b.form_fields FROM campaign AS a LEFT JOIN wp_fluentform_forms AS b ON a.form_id = b.id WHERE a.id = ' . $cid . ' ';
$resultvalf = $connection2->query($sqlf);
$fluent = $resultvalf->fetch();
$field = json_decode($fluent['form_fields']);
$fields = array();

$arrHeader = array();
foreach ($field as $fe) {
    foreach ($fe as $f) {
        if (!empty($f->attributes)) {
            $arrHeader[] = $f->attributes->name;
        }
    }
}

if (!empty($file)) {
    $arr = array();
    $files = array();
    foreach ($applicantId as $aid) {
        $phpword = new \PhpOffice\PhpWord\TemplateProcessor($file);

        $sqla = "select application_id FROM wp_fluentform_submissions  where id = " . $aid . " ";
        $resulta = $connection2->query($sqla);
        $applicationData = $resulta->fetch();

        $sql = "select field_name, field_value FROM wp_fluentform_entry_details  where submission_id = " . $aid . " ";
        $result = $connection2->query($sql);
        $rowdata = $result->fetchAll();
        foreach ($rowdata as $key => $value) {
            $arr[$value['field_name']] = $value['field_value'];
        }

        foreach ($arrHeader as $k => $ah) {
            if (array_key_exists($ah, $arr)) {
                if ($ah == 'file-upload' || $ah == 'image-upload') {
                    $attrValue = $arr[$ah];
                    $phpword->setImageValue($ah, $attrValue);
                } else {
                    $phpword->setValue($ah, $arr[$ah]);
                }
            } else {
                $phpword->setValue($ah, '');
            }
        }
        if (!empty($applicationData['application_id'])) {
            $fname = $applicationData['application_id'];
        } else {
            $fname = $aid;
        }

        $savedocsx = $_SERVER["DOCUMENT_ROOT"] . "/public/applicationpdf/parent/" . $fname . ".docx";
        $filename = $fname . ".docx";
        $phpword->saveAs($savedocsx);

        header("Content-Disposition: attachment; filename=" . $filename . " ");
        readfile($savedocsx);
        // unlink($savedocsx);
    }
}

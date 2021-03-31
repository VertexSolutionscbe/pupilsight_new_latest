<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

use mikehaertl\pdftk\Pdf;
use setasign\Fpdi\Fpdi;

/*
    Form data should be array
    $formData example
    $data = [
    'student_name' => "test user",
    'student_dob' => ' test dob',
    'student_class' => 'test class',
    'student_id' => ' test id',
    'student_mother_name' => 'test mother name',
    'student_father_name' => ' test father',
    'student_address' => 'test address'
];

$imgData[0] = [
    'pageno' => "test user",
    'src' => ' test dob',
    'x' => 'test class',
    'y' => ' test id',
    'width' => 'test mother name',
    'height' => ' test father'
];

$templateFileName //$_SERVER['DOCUMENT_ROOT'] . '/debug/' . 'Test_Template.pdf'
$outFileName // $_SERVER['DOCUMENT_ROOT'] . '/debug/' . $filename
*/

function generate($templateFileName, $outFileName, $formData, $imgData = NULL)
{
    try {
        //form data added
        $pdf = new Pdf($templateFileName);
        $result = $pdf->fillForm($formData)
            ->flatten()
            ->saveAs($outFileName);
        chmod($outFileName, 0777);
        if ($result === false) {
            $error = $pdf->getError();
            print_r($error);
        }
    } catch (Exception $ex) {
        print_r($ex);
    }

    //addimg image in pdf
    if ($imgData) {
        try {
            $pdf = new Fpdi();
            $pdf->AddPage();
            $pdf->setSourceFile($outFileName);

            $len = count($imgData);
            $i = 0;
            while ($i < $len) {
                $img = $imgData[$i];
                $template = $pdf->importPage($img["pageno"]);
                $pdf->useTemplate($template);
                $pdf->Image($img["src"], $img["x"], $img["y"], $img["width"], $img["height"]);
                $i++;
            }
            $pdf->Output($outFileName, "F");
        } catch (Exception $ex) {
            print_r($ex);
        }
    }
}

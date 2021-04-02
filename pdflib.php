<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/pupilsight/vendor/autoload.php');

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
    'pageno' => 1,
    'src' => $_SERVER['DOCUMENT_ROOT'] . "/debug/rakesh_photo.jpg",
    'x' => 10,
    'y' => 10,
    'width' => 20,
    'height' => 20
];

$templateFileName //$_SERVER['DOCUMENT_ROOT'] . '/debug/' . 'Test_Template.pdf'
$outFileName // $_SERVER['DOCUMENT_ROOT'] . '/debug/' . $filename
*/

function generate($templateFileName, $outFileName, $formData, $imgData = NULL, $download = FALSE, $deleteSource = FALSE)
{
    try {
        $pdf = new Pdf($templateFileName);
        $result = $pdf->fillForm($formData)
            ->flatten()
            ->saveAs($outFileName);
        if ($result === false) {
            $error = $pdf->getError();
            print_r($error);
        }
        chmod($outFileName, 0777);
    } catch (Exception $ex) {
        print_r($ex);
    }

    //addimg image in pdf
    if ($imgData) {
        try {
            $fpdi = new Fpdi();
            $pageCount = $fpdi->setSourceFile($outFileName);
            for ($j = 1; $j <= $pageCount; $j++) {
                $fpdi->AddPage();
                $template = $fpdi->importPage($j);
                $fpdi->useTemplate($template);
                $len = count($imgData);
                $i = 0;
                while ($i < $len) {
                    $img = $imgData[$i];
                    if ($img["pageno"] == $j) {
                        $fpdi->Image($img["src"], $img["x"], $img["y"], $img["width"], $img["height"]);
                    }
                    $i++;
                }
            }

            $fpdi->Output($outFileName, "F");
        } catch (Exception $ex) {
            print_r($ex);
        }
    }

    if ($download) {
        $fileName = basename($outFileName);
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($outFileName));
        readfile($outFileName);
        exit;
    }

    if ($deleteSource) {
        if (is_file($outFileName)) {
            unlink($outFileName);
        }
    }
}

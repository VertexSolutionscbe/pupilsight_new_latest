<?php


require_once($_SERVER['DOCUMENT_ROOT'] . '/pdflib.php');
$path = $_SERVER['DOCUMENT_ROOT'] . '/debug/';
for ($i = 0; $i < 2; $i++) {
    $formData[] = [
        'student_name' => "test user" . $i,
        'student_dob' => 'test dob',
        'student_class' => 'test class',
        'student_id' => 'test id',
        'student_mother_name' => 'test mother name' . $i,
        'student_father_name' => 'test father',
        'student_address' => 'test address' . $i
    ];

    $img[0] = [
        'pageno' => 2,
        'src' => $_SERVER['DOCUMENT_ROOT'] . "/debug/test1.jpg",
        'x' => 100,
        'y' => 200,
        'width' => 20,
        'height' => 20
    ];

    $img[1] = [
        'pageno' => 1,
        'src' => $_SERVER['DOCUMENT_ROOT'] . "/debug/test2.jpg",
        'x' => 100,
        'y' => 200,
        'width' => 20,
        'height' => 20
    ];
    $imgData[] = $img;
    $templateFileName[] = $path . 'Test_Template1_New.pdf';
    $outFileName[] = $path . 'Test_Template_out' . $i . '.pdf';
    $download[] = FALSE;
}

//$outFileName = $_SERVER['DOCUMENT_ROOT'] . '/debug/' . 'Test_Template_out' . $i . '.pdf';
$pdf1 = new PDFLib();
//$pdf1->bulkinit($templateFileName, $outFileName, $formData, $imgData, $download, $deleteSource);
for ($i = 0; $i < 2; $i++) {
    $pdf1->generate($templateFileName[$i], $outFileName[$i], $formData[$i], $imgData[$i], FALSE);
}
print_r($pdf1->files);
//$pdf1->createZipAndDownload("download.zip");
$pdf1->download();
//$pdf1->deleteSource();

/*
$pdf1->generate($templateFileName[1], $outFileName[1], $formData[1], $imgData[1], FALSE, TRUE);
$pdf1->generate($templateFileName[2], $outFileName[2], $formData[2], $imgData[2], FALSE, TRUE);
$pdf1->generate($templateFileName[2], $outFileName[2], $formData[2], $imgData[2], FALSE, TRUE);
*/

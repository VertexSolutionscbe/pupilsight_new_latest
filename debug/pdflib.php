<?php


require_once($_SERVER['DOCUMENT_ROOT'] . '/pdflib.php');
$formData = [
    'student_name' => "test user",
    'student_dob' => ' test dob',
    'student_class' => 'test class',
    'student_id' => 'test id',
    'student_mother_name' => 'test mother name',
    'student_father_name' => 'test father',
    'student_address' => 'test address'
];

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

$templateFileName = $_SERVER['DOCUMENT_ROOT'] . '/debug/' . 'Test_Template1_New.pdf';
$outFileName = $_SERVER['DOCUMENT_ROOT'] . '/debug/' . 'Test_Template_out1.pdf';

$outFileName = $_SERVER['DOCUMENT_ROOT'] . '/debug/' . 'Test_Template_out' . $i . '.pdf';
generate($templateFileName, $outFileName, $formData, $imgData, TRUE, TRUE);


$outFileName = $_SERVER['DOCUMENT_ROOT'] . '/debug/' . 'Test_Template_out2.pdf';
generate($templateFileName, $outFileName, $formData, $imgData, TRUE, TRUE);


$outFileName = $_SERVER['DOCUMENT_ROOT'] . '/debug/' . 'Test_Template_out3.pdf';
generate($templateFileName, $outFileName, $formData, $imgData, TRUE, TRUE);

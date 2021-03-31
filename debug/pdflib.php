<?php


require_once($_SERVER['DOCUMENT_ROOT'] . '/pdflib.php');
$formData = [
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
    'src' => $_SERVER['DOCUMENT_ROOT'] . "/debug/test1.jpg",
    'x' => 100,
    'y' => 200,
    'width' => 20,
    'height' => 20
];

$imgData[1] = [
    'pageno' => 1,
    'src' => $_SERVER['DOCUMENT_ROOT'] . "/debug/test2.jpg",
    'x' => 130,
    'y' => 200,
    'width' => 20,
    'height' => 20
];

$templateFileName = $_SERVER['DOCUMENT_ROOT'] . '/debug/' . 'Test_Template.pdf';
$outFileName = $_SERVER['DOCUMENT_ROOT'] . '/debug/' . 'Test_Template_out.pdf';
generate($templateFileName, $outFileName, $formData, $imgData);

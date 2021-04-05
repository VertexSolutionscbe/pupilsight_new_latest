<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

use mikehaertl\pdftk\Pdf;

define('ACCESSCHECK', TRUE);

$filename = 'pdf_' . rand(2000, 1200000) . '.pdf';
$data = [
    'name_field' => "rakesh" . ' ' . "kumar",
    'email_field' => "rakesh@test.com",
    'phone_field' => "8867776787",
    'enquiry_field' => "test enquiry"
];

$data = [
    'student_name' => "test user",
    'student_dob' => ' test dob',
    'student_class' => 'test class',
    'student_id' => ' test id',
    'student_mother_name' => 'test mother name',
    'student_father_name' => ' test father',
    'student_address' => 'test address'
];

//$pdf = new Pdf($_SERVER['DOCUMENT_ROOT'] . '/debug/' . 'test.pdf');
$pdf = new Pdf($_SERVER['DOCUMENT_ROOT'] . '/debug/' . 'Test_Template.pdf');
$result = $pdf->fillForm($data)
    ->flatten()
    ->saveAs($_SERVER['DOCUMENT_ROOT'] . '/debug/' . $filename);

if ($result === false) {
    $error = $pdf->getError();
}
echo "done";




/*
1. student_name
2. student_dob
3. student_class
4. student_id
5. student_mother_name
6. student_father_name
7. student_address
*/

<?php
/*
Pupilsight, Flexible & Open School System

*/
$data = array();
$data["rececipt_no"]="123456";

$data["date"]="10-Mar-2020";
$data["student_name"]="Shivansh Kumar";
$data["student_id"]="shivclass1a23";
$data["class_section"]="1A";
$data["instrument_date"]="12-Mar-2020";
$data["pay_mode"]="NEFT";
$data["instrument_no"]="123";


$data["transcation_amount"]="5000";
$data["fine_amount"]="1000";
$data["other_amount"]="1000";
$data["total"]="7000";
$data["total_in_words"]="Seven thousand rs only.";

/*
$serial = array(1,2);
$particulars = array("Tution Fee","Transport Fee");
$amount = array(4000,1000);
$data["serial"]=$serial;
$data["particulars"]=$particulars;
$data["amount"]=$amount;
*/
echo json_encode($data);

?>
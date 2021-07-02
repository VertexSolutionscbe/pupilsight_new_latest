<?php
include 'pupilsight.php';
$session = $container->get('session');
//before start check table is created or not
die();
$loc = $_SERVER['DOCUMENT_ROOT'] . "/public/archive/fee_receipt"; //file location
$htmlFiles = glob("$loc/*.{html,htm}", GLOB_BRACE); //only html files
//print_r($htmlFiles); //for debug files check here
//die();
$len = count($htmlFiles);
$i = 0;
$result = array();
$sq = "";
while ($i < $len) {
    if ($sq) {
        $sq .= ",";
    }
    $res = getFileStudentDetails($htmlFiles[$i]);
    if ($res) {
        $sq .= "('" . $res["student_id"] . "','" . $res["student_name"] . "','" . $res["st_class"] . "','" . $res["st_date"] . "','" . $res["receipt_no"] . "','" . $res["file_html"] . "')";
    }
    $i++;
}
//print_r($result);
$sql = 'insert into archive_fee_receipt_html (student_id, student_name, st_class, st_date, receipt_no, file_html) VALUES ' . $sq;
//get 
echo $sql;
try {
    $connection2->query($sql);
} catch (Exception $ex) {
    echo $ex->getMessage();
}

function getFileStudentDetails($fileName)
{

    $lines = file($fileName, FILE_IGNORE_NEW_LINES);
    //print_r($lines);
    $tag = array("Receipt No", "Date", "Student's Name", "Class/Section", "Student ID");
    //$tag = array("Receipt No","Date","Student Name","Class","Student Id");
    $tagkey = array("receipt_no", "st_date", "student_name", "st_class", "student_id");
    $i = 0;
    $ilen = count($lines);
    $result = array();
    $result["file_html"] = basename($fileName);
    while ($i < $ilen) {

        $len = count($tag);
        $j = 0;
        while ($j < $len) {
            $pos = strpos($lines[$i], $tag[$j]);
            if ($pos !== false) {
                $dt = strip_tags($lines[$i]); //data 
                $dtv = str_replace($tag[$j], "", $dt); //data value

                $dtvf = preg_replace('/[:\/]/', '', $dtv); //final value

                $result[$tagkey[$j]] = trim($dtvf);
                array_splice($tag, $j, 1);
                array_splice($tagkey, $j, 1);
                break;
            }
            $j++;
        }
        $i++;
    }
    return $result;
}

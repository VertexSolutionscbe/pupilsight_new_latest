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
//$len = 1;
$i = 0;

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


$sql = 'insert into archive_fee_receipt_html (student_id, student_name, st_class, st_date, receipt_no, file_html) VALUES ' . $sq;
//get 
echo $sql;
try {
    $connection2->query($sql);
} catch (Exception $ex) {
    echo $ex->getMessage();
}

function _getFileStudentDetails($fileName)
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
    //print_r($lines);
    while ($i < $ilen) {
        //print_r($lines[$i]);
        $len = count($tag);
        $j = 0;
        while ($j < $len) {
            $pos = strpos($lines[$i], $tag[$j]);
            if ($pos > -1) {
                //echo "test raeday";
                //print_r($lines[$i]);
                $i++;
                //print_r($lines[$i]);
                $dt = strip_tags($lines[$i]); //data 
                //$dtv = str_replace($tag[$j], "", $lines[$i]); //data value

                $dtvf = preg_replace('/[:\/]/', '', $dt); //final value
                //
                $result[$tagkey[$j]] = trim($dtvf);
                //echo "array:->";
                //print_r($result);
                array_splice($tag, $j, 1);
                array_splice($tagkey, $j, 1);
                break;
            }
            $j++;
        }
        $i++;
    }
    //echo "ater process";
    //print_r($result);
    return $result;
}


function getFileStudentDetails($fileName)
{

    $lines = file($fileName, FILE_IGNORE_NEW_LINES);
    //print_r($lines);
    $tag = array("Receipt No", "Date", "Student Name", "Class", "Student Id");
    $tagkey = array("receipt_no", "st_date", "student_name", "st_class", "student_id");
    $i = 0;
    $ilen = count($lines);
    $result = array();
    $result["file_html"] = basename($fileName);
    //print_r($lines);
    while ($i < $ilen) {
        //print_r($lines[$i]);
        $len = count($tag);
        $j = 0;
        while ($j < $len) {
            $pos = strpos($lines[$i], $tag[$j]);
            if ($pos > -1) {
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

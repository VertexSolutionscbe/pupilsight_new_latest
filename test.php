<?php
/*
$loc = $_SERVER['DOCUMENT_ROOT']."/public/archive/fee_receipt"; //file location
$htmlFiles = glob("$loc/*.{html,htm}", GLOB_BRACE); //only html files

$len = count($htmlFiles);
$i = 0;
$result = array();
$sq = "";
while($i<$len){
    if($sq){
        $sq .=",";
    }
    $res = getFileStudentDetails($htmlFiles[$i]);
    if($res){
        $sq .="('".$res["student_id"]."','".$res["student_name"]."','".$res["st_class"]."','".$res["st_date"]."','".$res["receipt_no"]."','".$res["file_html"]."')";
    }
    $i++;
}
//print_r($result);
$sql = 'insert into archive_fee_receipt_html (student_id, student_name, st_class, st_date, receipt_no, file_html) VALUES '.$sq;
//get 

echo $sql;
function getFileStudentDetails($fileName){
    
    $lines = file($fileName, FILE_IGNORE_NEW_LINES);
    //print_r($lines);
    $tag = array("Receipt No","Date","Student Name","Class","Student Id");
    $tagkey = array("receipt_no","st_date","student_name","st_class","student_id");
    $i = 0;
    $ilen = count($lines);
    $result = array();
    $result["file_html"] = basename($fileName);
    while($i<$ilen){

        $len = count($tag);
        $j = 0;
        while($j<$len){
            $pos = strpos($lines[$i], $tag[$j]);
            if($pos !== false) {
                $dt = strip_tags($lines[$i]);//data 
                $dtv = str_replace($tag[$j],"",$dt); //data value

                $dtvf = preg_replace('/[:\/]/', '', $dtv); //final value

                $result[$tagkey[$j]] = trim($dtvf);
                array_splice($tag, $j,1);
                array_splice($tagkey, $j,1);
                break;
            }
            $j++;
        }
        $i++;
    }
    return $result;
}
//print_r($result);
die();
*/

include 'pupilsight.php';

use Pupilsight\Contracts\Comms\Mailer;

// ini_set( 'display_errors', 1 );
// error_reporting( E_ALL );

// $input = json_decode($data, true);
$to = "rakesh@thoughtnet.in";
$subject = "Mail Testing";
$body = "Mail Testing";

/*
$to = $_GET['to'];
$subject = $_GET['subject'];
$body = $_GET['body'];
*/
$mail = $container->get(Mailer::class);
$mail->SetFrom($_SESSION[$guid]['organisationAdministratorEmail'], $_SESSION[$guid]['organisationAdministratorName']);

$mail->AddAddress($to);
$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';
$mail->isHTML(true);
$mail->Subject = $subject;
$mail->Body = $body;

// $mail->AddAttachment($_FILES['emailAttachment']['tmp_name'],
// $_FILES['emailAttachment']['name']);

$mail->Send();

die();
// $date = date('Y-m-d H:i:s');
// echo $date;
// $commoadPath = "lowriter --convert-to pdf " . $_SERVER['DOCUMENT_ROOT'] . "/thirdparty/phpword/templates/refund_receipt.docx";
// echo "\n<br>\n" . $commoadPath;

// echo "\n";
// $command = escapeshellcmd($commoadPath);
// $highlight = shell_exec($command);
// print_r($highlight);

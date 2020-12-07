<?php
/**
 * Created by PhpStorm.
 * User: Preetam
 * Date: 04-Dec-20
 * Time: 7:41 PM
 */
//print_r($_POST);
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."Timetable Admin/ttDates.php";
//die();
if(sizeof($_POST['ttName'])>0){

foreach ($_POST['ttName'] as $key => $value){
    $pupilsightid=explode('~',$value);
//    print_r($pupilsightid);
    $pupilsightTTDayID=($pupilsightid[0]);
    $date= date('Y-m-d',$pupilsightid[2]).'<br/>';
    try {
        $data = array('date' => $date, 'pupilsightTTDayID' => $pupilsightTTDayID);
        $sql = 'DELETE FROM pupilsightTTDayDate WHERE pupilsightTTDayID=:pupilsightTTDayID AND date=:date';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit();
    }
}
}else{
    $URL .= '&return=error2';
    header("Location: {$URL}");
    exit();
}
$URL .= '&return=success0';
header("Location: {$URL}");
?>
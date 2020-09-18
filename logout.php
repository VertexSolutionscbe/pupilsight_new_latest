<?php
/*
Pupilsight, Flexible & Open School System
*/

// Pupilsight system-wide include

require_once './pupilsight.php';
$session = $container->get('session');
$URL = './index.php';
if (isset($_GET['timeout'])) {
    if ($_GET['timeout'] == 'true') {
        $URL = './index.php?timeout=true';
    }
}

$cuid = $_SESSION[$guid]['pupilsightPersonID'];
$cdate = date('Y-m-d');
$ctime = date('H:i:s');

$sqlchk = 'SELECT a.* FROM fn_fees_counter_map AS a LEFT JOIN fn_fees_counter AS b ON a.fn_fees_counter_id = b.id WHERE a.pupilsightPersonID = "'.$cuid.'" AND a.active_date = "'.$cdate.'" AND a.end_time IS NULL AND b.status = "1" ';
$resultchk = $connection2->query($sqlchk);
$chkcounter = $resultchk->fetchAll();

foreach($chkcounter as $chk){
    $id = $chk['id'];
    $counterid = $chk['fn_fees_counter_id'];

    $data = array('end_time' => $ctime, 'pupilsightPersonID' => $cuid, 'id' => $id);
    $sql = 'UPDATE fn_fees_counter_map SET end_time=:end_time WHERE id=:id AND pupilsightPersonID=:pupilsightPersonID';
    $result = $connection2->prepare($sql);
    $result->execute($data);

    $data1 = array('status' => '2', 'id' => $counterid);
    $sql1 = 'UPDATE fn_fees_counter SET status=:status WHERE id=:id';
    $result1 = $connection2->prepare($sql1);
    $result1->execute($data1);
    $session->forget(['counterid']);
}


unset($_SESSION[$guid]['googleAPIAccessToken']);
unset($_SESSION[$guid]['gplusuer']);

session_destroy();

$_SESSION[$guid] = null;
//header("Location: {$URL}");

// $url = 'http://moodle.pupiltalk.com/login/logout.php';
//$url = 'http://localhost/pupilsight/cms/index.php';
$url = "index.php";
header("Location: {$url}");
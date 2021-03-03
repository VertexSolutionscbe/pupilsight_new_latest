<?php


error_reporting(E_ERROR | E_PARSE);

include '../pupilsight.php';



$sql = "SELECT pupilsightRole.category, pupilsightPersonID, preferredName, surname, username FROM pupilsightPerson JOIN pupilsightRole ON (pupilsightRole.pupilsightRoleID=pupilsightPerson.pupilsightRoleIDPrimary) WHERE status='Full' ORDER BY surname, preferredName";
$result = $connection2->query($sql);
$rowdata = $result->fetchAll();


$i=0;
foreach($rowdata as $val)
{
    $data[$i]['id']=$val['pupilsightPersonID'];
    $data[$i]['name']=$val['preferredName'];
    $i++;
}
echo json_encode($data);

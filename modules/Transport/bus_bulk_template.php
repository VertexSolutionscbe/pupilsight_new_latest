<?php


use Pupilsight\Data\ImportType;
use Pupilsight\Domain\System\CustomField;

$filename = "bus_details.csv";
$fp = fopen('php://output', 'w');


$sql = 'SELECT * FROM trans_bus_details';
$result = $connection2->query($sql);
$buses = $result->fetch();


// echo 'Buses:<pre>';print_r(array_keys($buses));exit;

$keys= array_keys($buses);
$bus_arr=array();
for($i=1;$i<=12;$i++)
{
    if($keys[$i]=='register_date' || $keys[$i]=='insurance_exp' || $keys[$i]=='fc_expiry' )
    {
        $bus_arr[$i]=$keys[$i].('(YYYY-MM-DD)');
    }
    else
    {
        $bus_arr[$i]=$keys[$i];
    }    

} 

//echo '<pre>';print_r($bus_arr);exit;

header('Content-type: application/csv');
header('Content-Disposition: attachment; filename='.$filename);
fputcsv($fp, $bus_arr);
fclose($fp);
die();

?>


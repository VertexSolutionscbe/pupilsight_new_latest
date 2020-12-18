<?php
/*
Pupilsight, Flexible & Open School System
*/
include 'pupilsight.php';
$session = $container->get('session');
try {
    $sq = "SELECT field_name FROM custom_field where field_type='varchar'";
    $result = $connection2->query($sq);
    $res = $result->fetchAll();
    if ($res) {
        $len = count($res);
        $i = 0;
        $sq = "ALTER TABLE `pupilsightPerson` ";

        while ($i < $len) {
            if ($i > 0) {
                $sq .= ",";
            }
            $sq .= "CHANGE COLUMN " . $res[$i]["field_name"] . " " . $res[$i]["field_name"] . " TEXT NULL DEFAULT NULL ";
            $i++;
        }
        $sq .= ";";
        $connection2->query($sq);
        $sq = "update custom_field set field_type='tinytext' where field_type='varchar' ";
        $connection2->query($sq);
        echo "Tousif Done go for next";
    }
} catch (Exception $ex) {
    print_r($ex);
}

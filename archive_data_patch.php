<?php
/*
Pupilsight, Flexible & Open School System
*/
include 'pupilsight.php';
$session = $container->get('session');
try {
    $sq = "SELECT StudentID FROM archive_fee_transactions_backup";
    $result = $connection2->query($sq);
    $res = $result->fetchAll();

    if ($res) {
        $len = count($res);
        $i = 0;
        while ($i < $len) {
            $sq = "select old_pupilpod_id from pupilsightPerson where old_pupilpod_id='" . $res[$i]["StudentID"] . "' ";
            $result1 = $connection2->query($sq);
            $res1 = $result1->fetch();
            if (empty($res1)) {
                $sq = "delete from archive_fee_transactions_backup where StudentID='" . $res[$i]["StudentID"] . "'; ";
                $connection2->query($sq);
            }
            $i++;
        }
    }
} catch (Exception $ex) {
    print_r($ex);
}

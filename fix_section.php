<?php

include 'pupilsight.php';

$sq = "select m.pupilsightMappingID, m.pupilsightSchoolYearID, m.pupilsightProgramID, 
m.pupilsightYearGroupID, m.pupilsightRollGroupID  
from pupilsightProgramClassSectionMapping as m";
$query = $connection2->query($sq);
$res = $query->fetchAll();
$len = count($res);
$i = 0;
$clasMapSection = array();
while ($i < $len) {
    $clasMapSection[$res[$i]["pupilsightRollGroupID"]] = $res[$i];
    $i++;
}

$sq = "select * from pupilsightMessengerTarget where type='Roll Group' and id is not null";
$query = $connection2->query($sq);
$res1 = $query->fetchAll();

$len = count($res1);
$i = 0;

while ($i < $len) {
    $id = $res1[$i]["id"];
    echo $id;
    $rs = $clasMapSection[$id];
    print_r($rs);

    if (!empty($rs)) {
        //print_r($rs);
        $squ = "update pupilsightMessengerTarget set 
    pupilsightMappingID='" . $rs["pupilsightMappingID"] . "',
    pupilsightSchoolYearID = '" . $rs["pupilsightSchoolYearID"] . "',
    pupilsightProgramID = '" . $rs["pupilsightProgramID"] . "',
    pupilsightYearGroupID = '" . $rs["pupilsightYearGroupID"] . "',
    pupilsightRollGroupID = '" . $rs["pupilsightRollGroupID"] . "' 
    where pupilsightMessengerTargetID='" . $res1[$i]["pupilsightMessengerTargetID"] . "' ";
        echo "\n<br>" . $squ;
        $connection2->query($squ);
    } else {
        echo "\n<br\>" . $res1[$i]["id"] . " | " . $res1[$i]["pupilsightMessengerTargetID"];
    }
    $i++;
}

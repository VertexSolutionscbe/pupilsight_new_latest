<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
/*
echo '<pre>';
print_r($_POST);
print_r($st_id);
echo '</pre>';
*/
$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . getModuleName($_POST['address']) . '/assign_staff_toSubject.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/assigned_staff_toStudent_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $pupilsightStaffID = $_POST["selected_sstaff"];
    $pupilsightdepartmentID = $_POST["selected_sub"];
    $cnt = count($pupilsightdepartmentID);

    if ($pupilsightStaffID == ''  or $cnt == 0) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $i = 0;
            $len = count($pupilsightStaffID);
            $slen = count($pupilsightdepartmentID);

            while ($i < $len) {
                $j = 0;
                while ($j < $slen) {
                    $sq = "select id from assignstaff_tosubject where pupilsightStaffID='" . $pupilsightStaffID[$i] . "' and pupilsightdepartmentID='" . $pupilsightdepartmentID[$j] . "' ";
                    //echo $sq;
                    $re_mode_check = $connection2->query($sq);
                    //print_r($re_mode_check);
                    $re_mode_data_check = $re_mode_check->fetch();
                    if (empty($re_mode_data_check)) {
                        $data = array('pupilsightdepartmentID' => $pupilsightdepartmentID[$j], 'pupilsightStaffID' => $pupilsightStaffID[$i]);
                        $sql = 'INSERT INTO assignstaff_tosubject SET pupilsightdepartmentID=:pupilsightdepartmentID, pupilsightStaffID=:pupilsightStaffID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    }
                    //print_r($re_mode_data_check);
                    $j++;
                }
                $i++;
            }
        } catch (PDOException $e) {
            $URL .= '&return=error9';
            header("Location: {$URL}");
            exit();
        }
        $URL .= "&return=success0";
        header("Location: {$URL}");
    }
}

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
$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . getModuleName($_POST['address']) . '/select_staff_sub.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/assigned_staff_toStudent_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $pupilsightStaffID = $_POST["selected_sstaff"];
    $subjectToClassCurriculumID = $_POST["selected_sub"];
    $cnt = count($subjectToClassCurriculumID);

    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $pupilsightProgramID = $_POST['pupilsightProgramID'];
    $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
    $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];

    if ($pupilsightStaffID == ''  or $cnt == 0) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $i = 0;
            $len = count($pupilsightStaffID);
            $slen = count($subjectToClassCurriculumID);

            while ($i < $len) {
                $j = 0;
                while ($j < $slen) {
                    $sq = "SELECT id from assignstaff_tosubject where pupilsightStaffID='" . $pupilsightStaffID[$i] . "' AND subjectToClassCurriculumID='" . $subjectToClassCurriculumID[$j] . "' AND pupilsightSchoolYearID='" . $pupilsightSchoolYearID . "' AND pupilsightProgramID='" . $pupilsightProgramID . "' AND pupilsightYearGroupID='" . $pupilsightYearGroupID . "' AND pupilsightRollGroupID='" . $pupilsightRollGroupID . "' ";
                    //echo $sq;
                    $re_mode_check = $connection2->query($sq);
                    //print_r($re_mode_check);
                    $re_mode_data_check = $re_mode_check->fetch();
                    if (empty($re_mode_data_check)) {
                        $sqdep = "select pupilsightdepartmentID from subjectToClassCurriculum where id='" . $subjectToClassCurriculumID[$j] . "'  ";
                        $resDep = $connection2->query($sqdep);
                        $DepData = $resDep->fetch();
                        $pupilsightdepartmentID = $DepData['pupilsightdepartmentID'];

                        $data = array('pupilsightdepartmentID' => $pupilsightdepartmentID, 'pupilsightStaffID' => $pupilsightStaffID[$i], 'subjectToClassCurriculumID' => $subjectToClassCurriculumID[$j], 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
                        $sql = 'INSERT INTO assignstaff_tosubject SET pupilsightdepartmentID=:pupilsightdepartmentID, pupilsightStaffID=:pupilsightStaffID, subjectToClassCurriculumID=:subjectToClassCurriculumID, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID';
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

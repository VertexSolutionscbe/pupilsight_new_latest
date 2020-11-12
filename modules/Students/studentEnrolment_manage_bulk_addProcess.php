<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$stuId = $_POST['stu_id'];
$search = $_GET['search'];

if ($pupilsightSchoolYearID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/studentEnrolment_manage.php";
    $RURL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/student_view.php";

    if (isActionAccessible($guid, $connection2, '/modules/Students/student_view.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
        exit;
    } else {
        //Proceed!
        //Check if person specified
        if ($stuId == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
            exit;
        } else {
            $studentid = explode(',', $stuId);
            $pupilsightProgramID = $_POST['pupilsightProgramID'];
            $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
            $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];

            foreach($studentid as $pupilsightPersonID){
                $datadel = array('pupilsightPersonID' => $pupilsightPersonID);
                $sqldel = 'DELETE FROM pupilsightStudentEnrolment WHERE pupilsightPersonID=:pupilsightPersonID';
                $resultdel = $connection2->prepare($sqldel);
                $resultdel->execute($datadel);


                $data = array('pupilsightProgramID'=>$pupilsightProgramID,'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
                $sql = 'INSERT INTO pupilsightStudentEnrolment SET pupilsightPersonID=:pupilsightPersonID, pupilsightProgramID=:pupilsightProgramID, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            }
                //Last insert ID
                $AI = str_pad($connection2->lastInsertID(), 8, '0', STR_PAD_LEFT);

                // if(isset($_SERVER['HTTP_REFERER'])) {
                //     $previous = $_SERVER['HTTP_REFERER'].'&return=success0';
                // }
                //     header("location:{$previous}");
               $RURL .= "&return=success0";
               header("Location: {$RURL}");
                exit;
        }
    }
}

<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightTTDayID = $_GET['pupilsightTTDayID'];
$pupilsightTTID = $_GET['pupilsightTTID'];
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightTTColumnRowID = $_GET['pupilsightTTColumnRowID'];
// $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightTTDayRowClassID = $_GET['pupilsightTTDayRowClassID'];
$pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
$pupilsightStaffID = $_POST['pupilsightStaffID'];
$pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
$pupilsightProgramID = $_POST['pupilsightProgramID'];

$st =implode(',' ,$pupilsightStaffID);
if ($pupilsightTTDayID == '' or $pupilsightTTID == '' or $pupilsightSchoolYearID == '' or $pupilsightTTColumnRowID == '' ) { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/tt_edit_day_edit_class.php&pupilsightTTDayID=$pupilsightTTDayID&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightTTColumnRowID=$pupilsightTTColumnRowID&pupilsightTTDayRowClassID=$pupilsightTTDayRowClassID&pupilsightProgramID=$pupilsightProgramID&pupilsightYearGroupID=$pupilsightYearGroupID";

    if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit_day_edit_class_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        $pupilsightSpaceID = $_POST['pupilsightSpaceID'];

        //Check if school year specified
        if ($pupilsightTTDayID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $datau = array('pupilsightTTDayRowClassID'=>$pupilsightTTDayRowClassID);
                $sqlu = 'SELECT * FROM pupilsightTTDayRowClass WHERE  pupilsightTTDayRowClassID=:pupilsightTTDayRowClassID' ;
                //print_r( $datau);die();
                $resultu = $connection2->prepare($sqlu);
                $resultu->execute($datau);
                //print_r($resultu->rowCount()); die();
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }   if ($resultu->rowCount() == 0) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {

                try {
                    $data = array('pupilsightTTColumnRowID' => $pupilsightTTColumnRowID, 'pupilsightTTDayID' => $pupilsightTTDayID, 'pupilsightTTColumnRowID' => $pupilsightTTColumnRowID,'pupilsightTTDayRowClassID'=>$pupilsightTTDayRowClassID);
                    $sql = 'SELECT  pupilsightTTDayRowClassID FROM pupilsightTTDayRowClass WHERE pupilsightTTColumnRowID=:pupilsightTTColumnRowID AND pupilsightTTDayID=:pupilsightTTDayID AND pupilsightTTColumnRowID=:pupilsightTTColumnRowID AND pupilsightTTDayRowClassID=:pupilsightTTDayRowClassID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                    //print_r($result->rowCount()); die();
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() < 1) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {


                        $data = array('pupilsightSpaceID' => $pupilsightSpaceID, 'pupilsightTTColumnRowID' => $pupilsightTTColumnRowID, 'pupilsightTTDayID' => $pupilsightTTDayID,'pupilsightStaffID'=>$st,'pupilsightDepartmentID'=>$pupilsightDepartmentID,'pupilsightTTDayRowClassID'=>$pupilsightTTDayRowClassID);
                        $sql = 'UPDATE pupilsightTTDayRowClass SET pupilsightSpaceID=:pupilsightSpaceID,pupilsightStaffID=:pupilsightStaffID,pupilsightDepartmentID=:pupilsightDepartmentID WHERE pupilsightTTColumnRowID=:pupilsightTTColumnRowID AND pupilsightTTDayID=:pupilsightTTDayID AND pupilsightTTDayRowClassID=:pupilsightTTDayRowClassID';
                        //print_r($data);die();
                        $result = $connection2->prepare($sql);
                        $result->execute($data);



                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }}
    }
}

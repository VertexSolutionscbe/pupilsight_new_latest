<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightTTDayID = $_GET['pupilsightTTDayID'];
$pupilsightTTID = $_GET['pupilsightTTID'];
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightTTColumnRowID = $_GET['pupilsightTTColumnRowID'];
$pupilsightCourseClassID = '12';
$pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
$pupilsightStaffID = $_POST['pupilsightStaffID'];
$pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
$pupilsightProgramID = $_POST['pupilsightProgramID'];

$st =implode(',' ,$pupilsightStaffID);
$pupilsightSpaceID = null;
if ($_POST['pupilsightSpaceID'] != '') {
    $pupilsightSpaceID = $_POST['pupilsightSpaceID'];
}

if ($pupilsightTTDayID == '' or $pupilsightTTID == '' or $pupilsightSchoolYearID == '' or $pupilsightTTColumnRowID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/tt_edit_day_edit_class.php&pupilsightTTDayID=$pupilsightTTDayID&pupilsightTTID=$pupilsightTTID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightTTColumnRowID=$pupilsightTTColumnRowID&pupilsightProgramID=$pupilsightProgramID&pupilsightYearGroupID=$pupilsightYearGroupID&pupilsightCourseClassID=$pupilsightCourseClassID";

    if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit_day_edit_class_add.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightTTDayID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
                //Check unique inputs for uniquness
        try {
            $datau = array('pupilsightDepartmentID' => $pupilsightDepartmentID, 'pupilsightTTColumnRowID'=>$pupilsightTTColumnRowID,'pupilsightStaffID'=>$st,'pupilsightTTDayID'=>$pupilsightTTDayID);
            $sqlu = 'SELECT * FROM pupilsightTTDayRowClass WHERE pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightTTColumnRowID=:pupilsightTTColumnRowID AND pupilsightStaffID=:pupilsightStaffID AND pupilsightTTDayID=:pupilsightTTDayID' ;
           //print_r( $datau);die();
            $resultu = $connection2->prepare($sqlu);
            $resultu->execute($datau);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }   if ($resultu->rowCount() > 0) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
   
            try {
                $data = array('pupilsightTTDayID' => $pupilsightTTDayID, 'pupilsightTTID' => $pupilsightTTID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightTTColumnRowID' => $pupilsightTTColumnRowID);
                $sql = 'SELECT pupilsightTT.name AS ttName, pupilsightTTDay.name AS dayName, pupilsightTTColumnRow.name AS rowName FROM pupilsightTT JOIN pupilsightTTDay ON (pupilsightTT.pupilsightTTID=pupilsightTTDay.pupilsightTTID) JOIN pupilsightTTColumn ON (pupilsightTTDay.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTColumnRow ON (pupilsightTTColumn.pupilsightTTColumnID=pupilsightTTColumnRow.pupilsightTTColumnID) WHERE pupilsightTTDay.pupilsightTTDayID=:pupilsightTTDayID AND pupilsightTT.pupilsightTTID=:pupilsightTTID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightTTColumnRowID=:pupilsightTTColumnRowID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                   
                   // print_r($st);die();                   
                        $data = array('pupilsightTTColumnRowID' => $pupilsightTTColumnRowID, 'pupilsightTTDayID' => $pupilsightTTDayID, 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightSpaceID' => $pupilsightSpaceID,'pupilsightDepartmentID'=>$pupilsightDepartmentID,'pupilsightStaffID'=>$st);
                        $sql = 'INSERT INTO pupilsightTTDayRowClass SET pupilsightTTColumnRowID=:pupilsightTTColumnRowID, pupilsightTTDayID=:pupilsightTTDayID, pupilsightCourseClassID=:pupilsightCourseClassID, pupilsightSpaceID=:pupilsightSpaceID,pupilsightDepartmentID=:pupilsightDepartmentID,pupilsightStaffID=:pupilsightStaffID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Last insert ID
                $AI = str_pad($connection2->lastInsertID(), 8, '0', STR_PAD_LEFT);

                $URL .= "&return=success0&editID=$AI&pupilsightCourseClassID=$pupilsightCourseClassID";
                header("Location: {$URL}");
            }
        }}
    }
}

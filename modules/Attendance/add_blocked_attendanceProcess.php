<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Module\Attendance\AttendanceView;

//Pupilsight system-wide includes
include __DIR__ . '/../../pupilsight.php';

//Module includes
include __DIR__ . '/moduleFunctions.php';

$name=$_POST['name'] ;
$pupilsightYearGroupID=$_POST['pupilsightYearGroupID'];

$pupilsightProgramID=$_POST['pupilsightProgramID'];
if(isset($_POST['pupilsightRollGroupID'] ))
{
    $pupilsightRollGroupIDs=$_POST['pupilsightRollGroupID'] ;
}
else
{
    $pupilsightRollGroupIDs=array('0'=>"");
}

$type=$_POST['type'] ;
$start_date=$_POST['start_date'];
$end_date=$_POST['end_date'] ;
$remark=$_POST['remark'] ;


$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/blocked_attendance.php";
$URL_ADD = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/add_blocked_attendance.php";

if (isActionAccessible($guid, $connection2, '/modules/Attendance/add_blocked_attendance.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if person specified
    if ( $name == ''  or $type == '' or $start_date == '' or $end_date=='') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
       /* $pupilsightPersonID = explode(',', $pupilsightPersonID);

        $personCheck = true ;
        foreach ($pupilsightPersonID as $pupilsightPersonIDCurrent) {
            try {
                $data = array('pupilsightPersonID' => $pupilsightPersonIDCurrent);
                $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $personCheck = false;
            }
            if ($result->rowCount() != 1) {
                $personCheck = false;
            }
        }*/

      /*  if (!$personCheck) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else { */
            //Write to database
            require_once __DIR__ . '/src/AttendanceView.php';
            $attendance = new AttendanceView($pupilsight, $pdo);

            $fail = false;
    

            $dateStart = '';
            if ($_POST['start_date'] != '') {
                 $dateStart = dateConvert($guid, $_POST['start_date']);
            }
            $dateEnd = $dateStart;
            if ($_POST['end_date'] != '') {
                 $dateEnd = dateConvert($guid, $_POST['end_date']);
            }
            $today = date('Y-m-d');

            //Check to see if date is in the future and is a school day.
            if ($dateStart == '' or ($dateEnd != '' and $dateEnd < $dateStart)  ) {
                $URL_ADD .= '&return=error8';
                header("Location: {$URL_ADD}");
            } else {
               
                foreach($pupilsightRollGroupIDs as $pupilsightRollGroupID)
                {

                try {
                    $sqlc = 'SELECT pupilsightYearGroupID FROM pupilsightProgramClassSectionMapping  WHERE pupilsightProgramID = "'.$pupilsightProgramID.'" AND pupilsightRollGroupID = "'.$pupilsightRollGroupID.'" ';
                    $resultc = $connection2->query($sqlc);
                    $clsdata = $resultc->fetch();

                    $clID = $clsdata['pupilsightYearGroupID'];
                  
                    $sectionID = $pupilsightRollGroupID;
                   
                    $data = array('pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'],'pupilsightRollGroupID' => $sectionID, 'pupilsightYearGroupID' => $clID ,'start_date' => $dateStart, 'end_date' => $dateEnd);
                    $sql = 'SELECT * FROM pupilsightAttendanceBlocked WHERE pupilsightPersonIDTaker=:pupilsightPersonIDTaker AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightYearGroupID=:pupilsightYearGroupID AND  start_date<=:start_date AND end_date>=:end_date ';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() > 0 ) {
                  
                    $URL .= '&return=error7';
                    
                    header("Location: {$URL}");
                    
                } else {
          

//SELECT * FROM `pupilsightAttendanceBlocked` WHERE 1,`pupilsightAttendanceBlockID`,`pupilsightRollGroupID`,`pupilsightYearGroupID`,`name`,`type`,`start_date`,`end_date`,`remark`,`pupilsightPersonIDTaker`,`timestampTaken`

                        try {
                            $dataUpdate = array('pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'],'pupilsightRollGroupID' => $sectionID, 'pupilsightYearGroupID' => $clID, 'name' => $name, 'type' => $type, 'start_date' => $dateStart, 'end_date' => $dateEnd, 'remark'=> $remark, 'timestampTaken' => date('Y-m-d H:i:s'));
                            $sqlUpdate = 'INSERT INTO pupilsightAttendanceBlocked SET  pupilsightPersonIDTaker=:pupilsightPersonIDTaker, pupilsightRollGroupID=:pupilsightRollGroupID, pupilsightYearGroupID=:pupilsightYearGroupID, name=:name, type=:type, start_date=:start_date, end_date=:end_date, remark=:remark, timestampTaken=:timestampTaken';
                            $resultUpdate = $connection2->prepare($sqlUpdate);
                            $resultUpdate->execute($dataUpdate);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }


                      

                    }

                }
                $URL .= '&return=success0';
                // echo $URL;
                header("Location: {$URL}");
            }

       
       // }
    }
}

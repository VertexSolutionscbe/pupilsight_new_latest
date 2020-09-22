<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Module\Attendance\AttendanceView;

//Pupilsight system-wide includes
require __DIR__ . '/../../pupilsight.php';

//Module includes
require_once __DIR__ . '/moduleFunctions.php';
$pupilsightPersonID =$_GET['pupilsightPersonID'];
$currentDate = $_POST['currentDate'];
$periodIDs = $_POST['pd_id'];
$types = $_POST['type'];
$reasons = $_POST['reasons'];
$comments = $_POST['comment'];
$count=count($periodIDs);
$today = date('Y-m-d');
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/attendance_take_byPerson_periodWise.php&pupilsightPersonID=$pupilsightPersonID&currentDate=".dateConvertBack($guid, $currentDate);

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_byPerson_periodWise.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
   
    // use any string / character by which, need to split string into Array
    // var_dump($resultArr); 
  // print_r($periodID );  die();


 
    if ($pupilsightPersonID == '' and $currentDate == '' ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightPersonID' => $pupilsightPersonID);
            $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            //Check that date is not in the future
            if ($currentDate > $today) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check that date is a school day
                // check special day
                $SpecialDays = array('date' => $currentDate);
                $sqlSpecialDays = "SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date=:date";
                $resultSpecialDays = $connection2->prepare($sqlSpecialDays);
                $resultSpecialDays->execute($SpecialDays);
                $specialDaysCounts = $resultSpecialDays->fetch();
                //check special day ends 
                if (isSchoolOpen($guid, $currentDate, $connection2) == false AND empty($specialDaysCounts)) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    require_once __DIR__ . '/src/AttendanceView.php';
                    $attendance = new AttendanceView($pupilsight, $pdo);

                    $fail = false;
                    $type = $_POST['type'];
                    $reason = $_POST['reason'];
                    $comment = $_POST['comment'];

                    //Check for last record on same day
                    try {
                       
                                //foreach($periodID as $pd_id){
                                for($i=0;$i<$count;$i++){

                                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'date' => $currentDate.'%','periodID'=>$periodIDs[$i]);
                                        $sql = 'SELECT * FROM pupilsightAttendanceLogPerson WHERE pupilsightPersonID=:pupilsightPersonID AND date LIKE :date  AND periodID=:periodID ORDER BY pupilsightAttendanceLogPersonID DESC';
                                        $result = $connection2->prepare($sql);
                                        $result->execute($data);
                                   }
                          
                           
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }
                  

                    //Check context and type, updating only if not a match
                    $existing = false ;
                    $pupilsightAttendanceLogPersonID = '';
                    if ($result->rowCount()>0) {
                        $row=$result->fetch() ;
                        if ($row['context'] == 'Person' && $row['type'] == $type) {
                            $existing = true ;
                            $pupilsightAttendanceLogPersonID = $row['pupilsightAttendanceLogPersonID'];
                        }
                    }
                   
                    if (!$existing) {
                        //If no records then create one
                        try {
                      
                             for($i=0;$i<$count;$i++){
                                $attendanceCode = $attendance->getAttendanceCodeByType($types[$i]);
                                $direction = $attendanceCode['direction'];
                                $dataUpdate = array('pupilsightPersonID' => $pupilsightPersonID, 'direction' => $direction, 'type' => $types[$i], 'reason' => $reasons[$i], 'comment' => $comments[$i], 'pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'periodID'=>$periodIDs[$i],'date' => $currentDate, 'timestampTaken' => date('Y-m-d H:i:s'));
                                $sqlUpdate = 'INSERT INTO pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=:type), pupilsightPersonID=:pupilsightPersonID,periodID=:periodID, direction=:direction, type=:type, context=\'Person\', reason=:reason, comment=:comment, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, date=:date, timestampTaken=:timestampTaken';
                                $resultUpdate = $connection2->prepare($sqlUpdate);
                                $resultUpdate->execute($dataUpdate);
                              
                            }
                         
                       
                        } catch (PDOException $e) {
                            $fail = true;
                        }

                       
                    } else {
                        //If direction same then update
                        if ($row['pupilsightCourseClassID'] == 0) {
                            try {
                                
                                for($i=0;$i<$count;$i++){
                                $attendanceCode = $attendance->getAttendanceCodeByType($types[$i]);
                                $direction = $attendanceCode['direction'];
                                $dataUpdate = array('pupilsightPersonID' => $pupilsightPersonID, 'direction' => $direction, 'type' => $types[$i],'periodID'=>$periodIDs[$i], 'reason' => $reasons[$i], 'comment' => $comments[$i], 'pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'date' => $currentDate, 'timestampTaken' => date('Y-m-d H:i:s'), 'pupilsightAttendanceLogPersonID' => $row['pupilsightAttendanceLogPersonID']);
                                $sqlUpdate = 'UPDATE pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=:type), pupilsightPersonID=:pupilsightPersonID, direction=:direction, type=:type, context=\'Person\', reason=:reason, periodID=:periodID,comment=:comment, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, date=:date, timestampTaken=:timestampTaken WHERE pupilsightAttendanceLogPersonID=:pupilsightAttendanceLogPersonID';
                                $resultUpdate = $connection2->prepare($sqlUpdate);
                                $resultUpdate->execute($dataUpdate);
                                }
                               
                              
                            } catch (PDOException $e) {
                                $fail = true;
                            }
                        }
                        //Else create a new record
                        else {
                            try {
                                foreach($periodID as $pd_id){
                                    $attendanceCode = $attendance->getAttendanceCodeByType($types[$i]);
                                    $direction = $attendanceCode['direction'];
                                $dataUpdate = array('pupilsightPersonID' => $pupilsightPersonID, 'direction' => $direction, 'type' => $type,'periodID'=>$pd_id, 'reason' => $reason, 'comment' => $comment, 'pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'], 'date' => $currentDate, 'timestampTaken' => date('Y-m-d H:i:s'));
                                $sqlUpdate = 'INSERT INTO pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=:type), pupilsightPersonID=:pupilsightPersonID, direction=:direction, type=:type, context=\'Person\', reason=:reason, periodID=:periodID,comment=:comment, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, date=:date, timestampTaken=:timestampTaken';
                                $resultUpdate = $connection2->prepare($sqlUpdate);
                                $resultUpdate->execute($dataUpdate);
                                }
                            } catch (PDOException $e) {
                                $fail = true;
                            }
                        }
                    }
                }
            }

            if ($fail == true) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}

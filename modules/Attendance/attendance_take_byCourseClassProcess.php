<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Module\Attendance\AttendanceView;

//Pupilsight system-wide includes
require __DIR__ . '/../../pupilsight.php';

//Module includes
require_once __DIR__ . '/moduleFunctions.php' ;

$pupilsightCourseClassID=$_POST["pupilsightCourseClassID"];
$currentDate=$_POST["currentDate"] ;
$today=date("Y-m-d");

$moduleName = getModuleName($_POST["address"]);

if ($moduleName == "Planner") {
	$pupilsightPlannerEntryID = $_POST['pupilsightPlannerEntryID'];
	$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $moduleName . "/planner_view_full.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID&viewBy=date&pupilsightCourseClassID=$pupilsightCourseClassID&date=" . $currentDate ;
} else {
	$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $moduleName . "/attendance_take_byCourseClass.php&pupilsightCourseClassID=$pupilsightCourseClassID&currentDate=" . dateConvertBack($guid, $currentDate) ;
}

if (isActionAccessible($guid, $connection2, "/modules/Attendance/attendance_take_byCourseClass.php")==FALSE) {
	//Fail 0
	$URL.="&return=error0" ;
	header("Location: {$URL}");
	die();
}
else {
	//Proceed!
	//Check if school year specified
	if ($pupilsightCourseClassID=="" AND $currentDate=="") {
		//Fail1
		$URL.="&return=error1" ;
		header("Location: {$URL}");
		die();
	}
	else {
		try {
			$data=array("pupilsightCourseClassID"=>$pupilsightCourseClassID);
			$sql="SELECT * FROM pupilsightCourseClass WHERE pupilsightCourseClassID=:pupilsightCourseClassID" ;
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) {
			//Fail2
			$URL.="&return=error2" ;
			header("Location: {$URL}");
			die();
		}

		if ($result->rowCount()!=1) {
			//Fail 2
			$URL.="&return=error1" ;
			header("Location: {$URL}");
			die();
		}
		else {
			//Check that date is not in the future
			if ($currentDate>$today) {
				//Fail 4
				$URL.="&return=error3" ;
				header("Location: {$URL}");
				die();
			}
			else {
				//Check that date is a school day
				if (isSchoolOpen($guid, $currentDate, $connection2)==FALSE) {
					//Fail 5
					$URL.="&return=error3" ;
					header("Location: {$URL}");
					die();
				}
				else {
					//Write to database
					require_once __DIR__ . '/src/AttendanceView.php';
					$attendance = new AttendanceView($pupilsight, $pdo);

					try {
						$data=array("pupilsightCourseClassID"=>$pupilsightCourseClassID, "date"=>$currentDate);
						$sql="SELECT pupilsightAttendanceLogCourseClassID FROM pupilsightAttendanceLogCourseClass WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND date LIKE :date ORDER BY pupilsightAttendanceLogCourseClassID DESC" ;
						$resultLog=$connection2->prepare($sql);
						$resultLog->execute($data);
					}
					catch(PDOException $e) {
						//Fail 2
						$URL.="&return=error2" ;
						header("Location: {$URL}");
						die();
					}

					if ($resultLog->rowCount()<1) {
						$data=array("pupilsightPersonIDTaker"=>$_SESSION[$guid]["pupilsightPersonID"], "pupilsightCourseClassID"=>$pupilsightCourseClassID, "date"=>$currentDate, "timestampTaken"=>date("Y-m-d H:i:s"));
						$sql="INSERT INTO pupilsightAttendanceLogCourseClass SET pupilsightPersonIDTaker=:pupilsightPersonIDTaker, pupilsightCourseClassID=:pupilsightCourseClassID, date=:date, timestampTaken=:timestampTaken" ;

					} else {
						$resultUpdate=$resultLog->fetch() ;
						$data=array("pupilsightAttendanceLogCourseClassID" => $resultUpdate['pupilsightAttendanceLogCourseClassID'], "pupilsightPersonIDTaker"=>$_SESSION[$guid]["pupilsightPersonID"], "pupilsightCourseClassID"=>$pupilsightCourseClassID, "date"=>$currentDate, "timestampTaken"=>date("Y-m-d H:i:s"));
						$sql="UPDATE pupilsightAttendanceLogCourseClass SET pupilsightPersonIDTaker=:pupilsightPersonIDTaker, pupilsightCourseClassID=:pupilsightCourseClassID, date=:date, timestampTaken=:timestampTaken WHERE pupilsightAttendanceLogCourseClassID=:pupilsightAttendanceLogCourseClassID" ;
					}

					try {
						$result=$connection2->prepare($sql);
						$result->execute($data);
					}
					catch(PDOException $e) {
						//Fail 2
						$URL.="&return=error2" ;
						header("Location: {$URL}");
						die();
					}

					$count=$_POST["count"] ;
					$partialFail=FALSE ;

					for ($i=0; $i<$count; $i++) {
						$pupilsightPersonID=$_POST[$i . "-pupilsightPersonID"] ;

						$type=$_POST[$i . "-type"] ;
						$reason=$_POST[$i . "-reason"] ;
						$comment=$_POST[$i . "-comment"] ;

						$attendanceCode = $attendance->getAttendanceCodeByType($type);
						$direction = $attendanceCode['direction'];

						//Check for last record on same day
						try {
							$data=array("pupilsightPersonID"=>$pupilsightPersonID, "date"=>$currentDate . "%");
							$sql="SELECT * FROM pupilsightAttendanceLogPerson WHERE pupilsightPersonID=:pupilsightPersonID AND date LIKE :date ORDER BY pupilsightAttendanceLogPersonID DESC" ;
							$result=$connection2->prepare($sql);
							$result->execute($data);
						}
						catch(PDOException $e) {
							//Fail 2
							$URL.="&return=error2" ;
							header("Location: {$URL}");
							die();
						}

                        //Check context, pupilsightCourseClassID and type, updating only if not a match
                        $existing = false ;
                        $pupilsightAttendanceLogPersonID = '';
                        if ($result->rowCount()>0) {
                            $row=$result->fetch() ;
                            if ($row['context'] == 'Class' && $row['pupilsightCourseClassID'] == $pupilsightCourseClassID && $row['type'] == $type) {
                                $existing = true ;
                                $pupilsightAttendanceLogPersonID = $row['pupilsightAttendanceLogPersonID'];
                            }
                        }

						if (!$existing) {
							//If no records then create one
							try {
								$dataUpdate=array("pupilsightPersonID"=>$pupilsightPersonID, "direction"=>$direction, "type"=>$type, "reason"=>$reason, "comment"=>$comment, "pupilsightPersonIDTaker"=>$_SESSION[$guid]["pupilsightPersonID"], "pupilsightCourseClassID"=>$pupilsightCourseClassID, "date"=>$currentDate, "timestampTaken"=>date("Y-m-d H:i:s"));
								$sqlUpdate="INSERT INTO pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=:type), pupilsightPersonID=:pupilsightPersonID, direction=:direction, type=:type, context='Class', reason=:reason, comment=:comment, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, pupilsightCourseClassID=:pupilsightCourseClassID, date=:date, timestampTaken=:timestampTaken" ;
								$resultUpdate=$connection2->prepare($sqlUpdate);
								$resultUpdate->execute($dataUpdate);
							}
							catch(PDOException $e) {
								$partialFail=TRUE ;
							}
						}
						else {
							try {
								$dataUpdate=array("pupilsightAttendanceLogPersonID"=>$pupilsightAttendanceLogPersonID, "pupilsightPersonID"=>$pupilsightPersonID, "direction"=>$direction, "type"=>$type, "reason"=>$reason, "comment"=>$comment, "pupilsightPersonIDTaker"=>$_SESSION[$guid]["pupilsightPersonID"], "pupilsightCourseClassID"=>$pupilsightCourseClassID, "date"=>$currentDate, "timestampTaken"=>date("Y-m-d H:i:s"));
								$sqlUpdate="UPDATE pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=:type), pupilsightPersonID=:pupilsightPersonID, direction=:direction, type=:type, context='Class', reason=:reason, comment=:comment, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, pupilsightCourseClassID=:pupilsightCourseClassID, date=:date, timestampTaken=:timestampTaken WHERE pupilsightAttendanceLogPersonID=:pupilsightAttendanceLogPersonID" ;
								$resultUpdate=$connection2->prepare($sqlUpdate);
								$resultUpdate->execute($dataUpdate);
							}
							catch(PDOException $e) {
								$partialFail=TRUE ;
							}
						}
					}

					if ($partialFail==TRUE) {
						//Fail 3
						$URL.="&return=warning1" ;
						header("Location: {$URL}");
						die();
					}
					else {
						//Success 0
						$URL.="&return=success0&time=" . date("H-i-s") ;
						header("Location: {$URL}");
					}
				}
			}
		}
	}
}

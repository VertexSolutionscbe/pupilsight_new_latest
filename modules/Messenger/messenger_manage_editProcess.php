<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightMessengerID=$_POST["pupilsightMessengerID"] ;
$search=NULL ;
if (isset($_GET["search"])) {
	$search=$_GET["search"] ;
}
$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_POST["address"]) . "/messenger_manage_edit.php&sidebar=true&search=$search&pupilsightMessengerID=" . $pupilsightMessengerID ;
$time=time() ;

if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_manage_edit.php")==FALSE) {
	$URL.="&updateReturn=fail0" ;
	header("Location: {$URL}");
}
else {
	if (empty($_POST)) {
		//fail 5
		$URL.="&updateReturn=fail5" ;
		header("Location: {$URL}");
	}
	else {
		$highestAction=getHighestGroupedAction($guid, $_POST["address"], $connection2) ;
		if ($highestAction==FALSE) {
			$URL.="&updateReturn=fail0$params" ;
			header("Location: {$URL}");
		}
		else {
			//Proceed!
			//Validate Inputs
			$messageWall=$_POST["messageWall"] ;
			$date1=NULL ;
			if (isset($_POST["date1"])) {
				if ($_POST["date1"]!="") {
					$date1=dateConvert($guid, $_POST["date1"]) ;
				}
			}
			$date2=NULL ;
			if (isset($_POST["date2"])) {
				if ($_POST["date2"]!="") {
					$date2=dateConvert($guid, $_POST["date2"]) ;
				}
			}
			$date3=NULL ;
			if (isset($_POST["date3"])) {
				if ($_POST["date3"]!="") {
					$date3=dateConvert($guid, $_POST["date3"]) ;
				}
			}
			$subject=$_POST["subject"] ;
			$body=$_POST["body"] ;

			if ($subject=="" OR $body=="") {
				//fail 3
				$URL.="&updateReturn=fail3" ;
				header("Location: {$URL}");
			}
			else {
				//Write to database
				try {
					$dataUpdate=array("messageWall"=>$messageWall, "messageWall_date1"=>$date1, "messageWall_date2"=>$date2, "messageWall_date3"=>$date3, "subject"=>$subject, "body"=>$body, "timestamp"=>date("Y-m-d H:i:s"), "pupilsightMessengerID"=>$pupilsightMessengerID);
					$sqlUpdate="UPDATE pupilsightMessenger SET messageWall=:messageWall, messageWall_date1=:messageWall_date1, messageWall_date2=:messageWall_date2, messageWall_date3=:messageWall_date3, subject=:subject, body=:body, timestamp=:timestamp WHERE pupilsightMessengerID=:pupilsightMessengerID" ;
					$resultUpdate=$connection2->prepare($sqlUpdate);
					$resultUpdate->execute($dataUpdate);
				}
				catch(PDOException $e) {
					//fail 2
					$URL.="&updateReturn=fail2" ;
					header("Location: {$URL}");
					exit() ;
				}

				//TARGETS
				$partialfail=FALSE ;

				try {
					$dataRemove=array("pupilsightMessengerID"=>$pupilsightMessengerID);
					$sqlRemove="DELETE FROM pupilsightMessengerTarget WHERE pupilsightMessengerID=:pupilsightMessengerID" ;
					$resultRemove=$connection2->prepare($sqlRemove);
					$resultRemove->execute($dataRemove);
				}
				catch(PDOException $e) {
					$partialfail=TRUE;
				}

				//Roles
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_role")) {
					$_POST["role"] ;
					if ($_POST["role"]=="Y") {
						$choices=$_POST["roles"] ;
						if ($choices!="") {
							foreach ($choices as $t) {
								try {
									$data=array("pupilsightMessengerID"=>$pupilsightMessengerID, "t"=>$t);
									$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Role', id=:t" ;
									$result=$connection2->prepare($sql);
									$result->execute($data);
								}
								catch(PDOException $e) {
									$partialfail=TRUE;
								}
							}
						}
					}
				}


				//Role Categories
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_role") || isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_postQuickWall.php")) {
					if ($_POST["roleCategory"]=="Y") {
						$choices=$_POST["roleCategories"] ;
						if ($choices!="") {
							foreach ($choices as $t) {
								try {
									$data=array("pupilsightMessengerID"=>$pupilsightMessengerID, "t"=>$t);
									$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Role Category', id=:t" ;
									$result=$connection2->prepare($sql);
									$result->execute($data);
								}
								catch(PDOException $e) {
									$partialfail=TRUE;
								}
							}
						}
					}
				}

				//Year Groups
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_yearGroups_any")) {
					if ($_POST["yearGroup"]=="Y") {
						$staff=$_POST["yearGroupsStaff"] ;
						$students=$_POST["yearGroupsStudents"] ;
						$parents="N" ;
						if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_yearGroups_parents")) {
							$parents=$_POST["yearGroupsParents"] ;
						}
						$choices=$_POST["yearGroups"] ;
						if ($choices!="") {
							foreach ($choices as $t) {
								try {
									$data=array("pupilsightMessengerID"=>$pupilsightMessengerID, "t"=>$t, "staff"=>$staff, "students"=>$students, "parents"=>$parents);
									$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Year Group', id=:t, staff=:staff, students=:students, parents=:parents" ;
									$result=$connection2->prepare($sql);
									$result->execute($data);
								}
								catch(PDOException $e) {
									$partialfail=TRUE;
								}
							}
						}
					}
				}

				//Roll Groups
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_rollGroups_my") OR isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_rollGroups_any")) {
					if ($_POST["rollGroup"]=="Y") {
						$staff=$_POST["rollGroupsStaff"] ;
						$students=$_POST["rollGroupsStudents"] ;
						$parents="N" ;
						if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_rollGroups_parents")) {
							$parents=$_POST["rollGroupsParents"] ;
						}
						$choices=$_POST["rollGroups"] ;
						if ($choices!="") {
							foreach ($choices as $t) {
								try {
									$data=array("pupilsightMessengerID"=>$pupilsightMessengerID, "t"=>$t, "staff"=>$staff, "students"=>$students, "parents"=>$parents);
									$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Roll Group', id=:t, staff=:staff, students=:students, parents=:parents" ;
									$result=$connection2->prepare($sql);
									$result->execute($data);
								}
								catch(PDOException $e) {
									$partialfail=TRUE;
								}
							}
						}
					}
				}

				//Course Groups
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_courses_my") OR isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_courses_any")) {
					if ($_POST["course"]=="Y") {
						$staff=$_POST["coursesStaff"] ;
						$students=$_POST["coursesStudents"] ;
						$parents="N" ;
						if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_courses_parents")) {
							$parents=$_POST["coursesParents"] ;
						}
						$choices=$_POST["courses"] ;
						if ($choices!="") {
							foreach ($choices as $t) {
								try {
									$data=array("pupilsightMessengerID"=>$pupilsightMessengerID, "id"=>$t, "staff"=>$staff, "students"=>$students, "parents"=>$parents);
									$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Course', id=:id, staff=:staff, students=:students, parents=:parents" ;
									$result=$connection2->prepare($sql);
									$result->execute($data);
								}
								catch(PDOException $e) {
									$partialfail=TRUE;
								}
							}
						}
					}
				}

				//Class Groups
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_classes_my") OR isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_classes_any")) {
					if ($_POST["class"]=="Y") {
						$staff=$_POST["classesStaff"] ;
						$students=$_POST["classesStudents"] ;
						$parents="N" ;
						if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_classes_parents")) {
							$parents=$_POST["classesParents"] ;
						}
						$choices=$_POST["classes"] ;
						if ($choices!="") {
							foreach ($choices as $t) {
								try {
									$data=array("pupilsightMessengerID"=>$pupilsightMessengerID, "id"=>$t, "staff"=>$staff, "students"=>$students, "parents"=>$parents);
									$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Class', id=:id, staff=:staff, students=:students, parents=:parents" ;
									$result=$connection2->prepare($sql);
									$result->execute($data);
								}
								catch(PDOException $e) {
									$partialfail=TRUE;
								}
							}
						}
					}
				}

				//Activity Groups
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_activities_my") OR isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_activities_any")) {
					if ($_POST["activity"]=="Y") {
						$staff=$_POST["activitiesStaff"] ;
						$students=$_POST["activitiesStudents"] ;
						$parents="N" ;
						if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_activities_parents")) {
							$parents=$_POST["activitiesParents"] ;
						}
						$choices=$_POST["activities"] ;
						if ($choices!="") {
							foreach ($choices as $t) {
								try {
									$data=array("pupilsightMessengerID"=>$pupilsightMessengerID, "id"=>$t, "staff"=>$staff, "students"=>$students, "parents"=>$parents);
									$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Activity', id=:id, staff=:staff, students=:students, parents=:parents" ;
									$result=$connection2->prepare($sql);
									$result->execute($data);
								}
								catch(PDOException $e) {
									$partialfail=TRUE;
								}
							}
						}
					}
				}

				//Applicants
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_applicants")) {
					if ($_POST["applicants"]=="Y") {
						$choices=$_POST["applicantList"] ;
						if ($choices!="") {
							foreach ($choices as $t) {
								try {
									$data=array("pupilsightMessengerID"=>$pupilsightMessengerID, "id"=>$t);
									$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Applicants', id=:id" ;
									$result=$connection2->prepare($sql);
									$result->execute($data);
								}
								catch(PDOException $e) {
									$partialfail=TRUE;
								}
							}
						}
					}
				}

				//Houses
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_houses_all") OR isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_houses_my")) {
					if ($_POST["houses"]=="Y") {
						$choices=$_POST["houseList"] ;
						if ($choices!="") {
							foreach ($choices as $t) {
								try {
									$data=array("pupilsightMessengerID"=>$pupilsightMessengerID, "id"=>$t);
									$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Houses', id=:id" ;
									$result=$connection2->prepare($sql);
									$result->execute($data);
								}
								catch(PDOException $e) {
									$partialfail=TRUE;
								}
							}
						}
					}
				}

                //Transport
                if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_transport_any")) {
                  if ($_POST["transport"]=="Y") {
          					$staff=$_POST["transportStaff"] ;
          					$students=$_POST["transportStudents"] ;
          					$parents="N" ;
                    if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_transport_parents")) {
                      $parents=$_POST["transportParents"] ;
                    }
                    $choices=$_POST["transports"] ;
                    if ($choices!="") {
                      foreach ($choices as $t) {
                        try {
                          $data=array("pupilsightMessengerID"=>$pupilsightMessengerID, "id"=>$t, "students"=>$students, "parents"=>$parents, "staff"=>$staff);
                          $sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Transport', id=:id, students=:students, staff=:staff, parents=:parents" ;
                          $result=$connection2->prepare($sql);
                          $result->execute($data);
                        }
                        catch(PDOException $e) {
                          $partialfail=TRUE;
                        }
                      }
                    }
                  }
                }

                //Attendance
                if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_attendance")) {
                  if ($_POST["attendance"]=="Y") {
                    $choices=$_POST["attendanceStatus"];
                    $students=$_POST["attendanceStudents"];
                    $parents=$_POST["attendanceParents"];
                    if ($choices!="") {
                      foreach ($choices as $t) {
                        try {
                          $data=array("pupilsightMessengerID"=>$pupilsightMessengerID, "id"=>$t, "students"=>$students, "parents"=>$parents);
                          $sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Attendance', id=:id, students=:students, parents=:parents" ;
                          $result=$connection2->prepare($sql);
                          $result->execute($data);
                        }
                        catch(PDOException $e) {
                          $partialfail=TRUE;
                        }
                      }
                    }
                  }
				}
				
				//Groups
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_groups_any") || isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_groups_my")) {
					if ($_POST["group"] == "Y") {
						$staff = $_POST["groupsStaff"] ;
						$students = $_POST["groupsStudents"] ;
						$parents = "N" ;
						if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_groups_parents")) {
							$parents=$_POST["groupsParents"] ;
						}
						$choices=$_POST["groups"] ;
						if ($choices!="") {
							foreach ($choices as $t) {
								try {
									$data=array("pupilsightMessengerID"=>$pupilsightMessengerID, "t"=>$t, "staff"=>$staff, "students"=>$students, "parents"=>$parents);
									$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Group', id=:t, staff=:staff, students=:students, parents=:parents" ;
									$result=$connection2->prepare($sql);
									$result->execute($data);
								}
								catch(PDOException $e) {
									$partialfail=TRUE;
								}
							}
						}
					}
				}

				//Individuals
				if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_individuals")) {
					if ($_POST["individuals"]=="Y") {
						$choices=$_POST["individualList"] ;
						if ($choices!="") {
							foreach ($choices as $t) {
								try {
									$data=array("pupilsightMessengerID"=>$pupilsightMessengerID, "id"=>$t);
									$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Individuals', id=:id" ;
									$result=$connection2->prepare($sql);
									$result->execute($data);
								}
								catch(PDOException $e) {
									$partialfail=TRUE;
								}
							}
						}
					}
				}


				if ($partialfail==TRUE) {
					//fail 4
					$URL.="&updateReturn=fail4" ;
					header("Location: {$URL}");
				}
				else {
					//Success 0
					$URL.="&updateReturn=success0" ;
					header("Location: {$URL}") ;
				}
			}
		}
	}
}
?>

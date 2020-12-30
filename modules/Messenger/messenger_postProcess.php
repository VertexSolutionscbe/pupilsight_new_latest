<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Contracts\Comms\Mailer;
use Pupilsight\Contracts\Comms\SMS;
//print_r($_POST);die();
include '../../pupilsight.php';

//Increase max execution time, as this stuff gets big
ini_set('max_execution_time', 7200);
ini_set('memory_limit','1024M');
set_time_limit(1200);

//Module includes
include "./moduleFunctions.php" ;

$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_POST["address"]) . "/messenger_post.php" ;
$time=time() ;

if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php")==FALSE) {
	//Fail 0
	$URL.="&addReturn=fail0" ;
	header("Location: {$URL}");
}
else {
	if (empty($_POST)) {
		//Fail 5
		$URL.="&addReturn=fail5" ;
		header("Location: {$URL}");
	}
	else {
		//Proceed!
		//Setup return variables
		$emailCount=NULL ;
		$smsCount=NULL ;
		$smsBatchCount=NULL ;

		//Validate Inputs
		$email=$_POST["email"] ;
		if ($email!="Y") {
			$email="N" ;
		}
		if ($email=="Y") {
			$from=$_POST["from"];
		}
		$emailReplyTo="" ;
		if (isset($_POST["emailReplyTo"])) {
			$emailReplyTo=$_POST["emailReplyTo"] ;
		}
		$messageWall="" ;
		if (isset($_POST["messageWall"])) {
			$messageWall=$_POST["messageWall"] ;
		}
		if ($messageWall!="Y") {
			$messageWall="N" ;
		}
		$date1=NULL ;
		//if (isset($_POST["date1"])) {
			if ($_POST["date1"]!="") {
				$date1=dateConvert($guid, $_POST["date1"]) ;
			}else{
                $dateFormat = $_SESSION[$guid]['i18n']['dateFormatPHP'];
                $date1=date('Y-m-d');
			}
		//}
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
		$sms=NULL ;
		if (isset($_POST["sms"])) {
			$sms=$_POST["sms"] ;
		}
		if ($sms!="Y") {
			$sms="N" ;
		}
		$subject=$_POST["subject"] ;
		$category=$_POST["category"] ;
		$body=stripslashes($_POST["body"]) ;
		$emailReceipt = $_POST["emailReceipt"] ;
		$emailReceiptText = null;
		if (isset($_POST["emailReceiptText"]))
			$emailReceiptText = $_POST["emailReceiptText"] ;

		if ($subject == "" OR $body == "" OR ($email == "Y" AND $from == "") OR $emailReceipt == '' OR ($emailReceipt == "Y" AND $emailReceiptText == "")) {
			//Fail 3
			$URL.="&addReturn=fail3" ;
			header("Location: {$URL}");
		}
		else {
			//Lock table
			try {
				$sql="LOCK TABLES pupilsightMessenger WRITE" ;
				$result=$connection2->query($sql);
			}
			catch(PDOException $e) {
				//Fail 2
				$URL.="&addReturn=fail2" ;
				header("Location: {$URL}");
				exit() ;
			}

			//Get next autoincrement
			try {
				$sqlAI="SHOW TABLE STATUS LIKE 'pupilsightMessenger'";
				$resultAI=$connection2->query($sqlAI);
			}
			catch(PDOException $e) {
				//Fail 2
				$URL.="&addReturn=fail2" ;
				header("Location: {$URL}");
				exit() ;
			}

			$rowAI=$resultAI->fetch();
			$AI=str_pad($rowAI['Auto_increment'], 12, "0", STR_PAD_LEFT) ;

			//Write to database
			try {
				$data=array("email"=>$email, "messageWall"=>$messageWall, "messageWall_date1"=>$date1, "messageWall_date2"=>$date2, "messageWall_date3"=>$date3, "sms"=>$sms, "subject"=>$subject, "body"=>$body, "emailReceipt" => $emailReceipt, "emailReceiptText" => $emailReceiptText, "pupilsightPersonID"=>$_SESSION[$guid]["pupilsightPersonID"],"category"=>$category, "timestamp"=>date("Y-m-d H:i:s"));
				$sql="INSERT INTO pupilsightMessenger SET email=:email, messageWall=:messageWall, messageWall_date1=:messageWall_date1, messageWall_date2=:messageWall_date2, messageWall_date3=:messageWall_date3, sms=:sms, subject=:subject, body=:body, emailReceipt=:emailReceipt, emailReceiptText=:emailReceiptText, pupilsightPersonID=:pupilsightPersonID,messengercategory=:category, timestamp=:timestamp" ;
				$result=$connection2->prepare($sql);
				$result->execute($data);
			}
			catch(PDOException $e) {
				//Fail 2
				$URL.="&addReturn=fail2" ;
				header("Location: {$URL}");
				exit() ;
			}

			try {
				$sql="UNLOCK TABLES" ;
				$result=$connection2->query($sql);
			}
			catch(PDOException $e) {
				//Fail 2
				$URL.="&addReturn=fail2" ;
				header("Location: {$URL}");
				exit() ;
			}

			//TARGETS
			$partialFail=FALSE ;
			$report = array();
			//Get country code
			$countryCode="" ;
			$country=getSettingByScope($connection2, "System", "country") ;
			$countryCodeTemp = '';
			try {
				$dataCountry=array("printable_name"=>$country);
				$sqlCountry="SELECT iddCountryCode FROM pupilsightCountry WHERE printable_name=:printable_name" ;
				$resultCountry=$connection2->prepare($sqlCountry);
				$resultCountry->execute($dataCountry);
			}
			catch(PDOException $e) { }
			if ($resultCountry->rowCount()==1) {
				$rowCountry=$resultCountry->fetch() ;
				$countryCode=$rowCountry["iddCountryCode"] ;
			}

			//Roles
			if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_role")) {
				if ($_POST["role"]=="Y") {
					$choices=$_POST["roles"] ;
					if ($choices!="") {
						foreach ($choices as $t) {
							try {
								$data=array("AI"=>$AI, "t"=>$t);
								$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:AI, type='Role', id=:t" ;
								$result=$connection2->prepare($sql);
								$result->execute($data);
							}
							catch(PDOException $e) {
								$partialFail=TRUE;
							}

							//Get email addresses
							$category=getRoleCategory($t, $connection2) ;
                            $pupilsightRoleID = str_pad(intval($t), 3, '0', STR_PAD_LEFT);
							if ($email=="Y") {
								if ($category=="Parent") {
									try {
										$dataEmail=array('pupilsightRoleID'=>$pupilsightRoleID);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT email='' AND FIND_IN_SET(:pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll) AND status='Full' AND contactEmail='Y'" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);

									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Role', $t, 'Email', $rowEmail["email"]);
									}
								}
								else {
									try {
										$dataEmail=array('pupilsightRoleID'=>$pupilsightRoleID);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT email='' AND FIND_IN_SET(:pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll) AND status='Full'" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Role', $t, 'Email', $rowEmail["email"]);
									}
								}
							}
							if ($sms=="Y" AND $countryCode!="") {
								if ($category=="Parent") {
									try {
										$dataEmail=array('pupilsightRoleID'=>$pupilsightRoleID);
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone1='' AND phone1Type='Mobile' AND FIND_IN_SET(:pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll) AND status='Full' AND contactSMS='Y')" ;
										$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone2='' AND phone2Type='Mobile' AND FIND_IN_SET(:pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll) AND status='Full' AND contactSMS='Y')" ;
										$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone3='' AND phone3Type='Mobile' AND FIND_IN_SET(:pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll) AND status='Full' AND contactSMS='Y')" ;
										$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone4='' AND phone4Type='Mobile' AND FIND_IN_SET(:pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll) AND status='Full' AND contactSMS='Y')" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Role', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
								else {
									try {
										$dataEmail=array('pupilsightRoleID'=>$pupilsightRoleID);
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone1='' AND phone1Type='Mobile' AND FIND_IN_SET(:pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll) AND status='Full')" ;
										$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone2='' AND phone2Type='Mobile' AND FIND_IN_SET(:pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll) AND status='Full')" ;
										$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone3='' AND phone3Type='Mobile' AND FIND_IN_SET(:pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll) AND status='Full')" ;
										$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone4='' AND phone4Type='Mobile' AND FIND_IN_SET(:pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll) AND status='Full')" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Role', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
							}
						}
					}
				}
			}

			//Role Categories
			if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_role")) {
				if ($_POST["roleCategory"]=="Y") {
					$choices=$_POST["roleCategories"] ;
					if ($choices!="") {
						foreach ($choices as $t) {
							try {
								$data=array("AI"=>$AI, "t"=>$t);
								$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:AI, type='Role Category', id=:t" ;
								$result=$connection2->prepare($sql);
								$result->execute($data);
							}
							catch(PDOException $e) {
								$partialFail=TRUE;
							}
							//Get email addresses
							if ($email=="Y") {
								if ($t=="Parent") {
									try {
										$dataEmail=array("category"=>$t);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)) WHERE NOT email='' AND category=:category AND status='Full' AND contactEmail='Y'" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Role Category', $t, 'Email', $rowEmail["email"]);
									}
								}
								else {
									try {
										$dataEmail=array("category"=>$t);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightRole ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)) WHERE NOT email='' AND category=:category AND status='Full'" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Role Category', $t, 'Email', $rowEmail["email"]);
									}
								}
							}
							if ($sms=="Y" AND $countryCode!="") {
								if ($t=="Parent") {
									try {
										$dataEmail=array("category"=>$t);
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)) WHERE NOT phone1='' AND phone1Type='Mobile' AND category=:category AND status='Full' AND contactSMS='Y')" ;
										$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)) WHERE NOT phone2='' AND phone2Type='Mobile' AND category=:category AND status='Full' AND contactSMS='Y')" ;
										$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)) WHERE NOT phone3='' AND phone3Type='Mobile' AND category=:category AND status='Full' AND contactSMS='Y')" ;
										$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRole ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)) WHERE NOT phone4='' AND phone4Type='Mobile' AND category=:category AND status='Full' AND contactSMS='Y')" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { print $e->getMessage() ;}
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Role Category', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
								else {
									try {
										$dataEmail=array("category"=>$t);
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightRole ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)) WHERE NOT phone1='' AND phone1Type='Mobile' AND category=:category AND status='Full')" ;
										$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightRole ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)) WHERE NOT phone2='' AND phone2Type='Mobile' AND category=:category AND status='Full')" ;
										$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightRole ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)) WHERE NOT phone3='' AND phone3Type='Mobile' AND category=:category AND status='Full')" ;
										$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightRole ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll)) WHERE NOT phone4='' AND phone4Type='Mobile' AND category=:category AND status='Full')" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Role Category', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
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
								$data=array("AI"=>$AI, "t"=>$t, "staff"=>$staff, "students"=>$students, "parents"=>$parents);
								$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:AI, type='Year Group', id=:t, staff=:staff, students=:students, parents=:parents" ;
								$result=$connection2->prepare($sql);
								$result->execute($data);
							}
							catch(PDOException $e) {
								$partialFail=TRUE;
							}

							//Get email addresses
							if ($email=="Y") {
								if ($staff=="Y") {
									try {
										$dataEmail=array('pupilsightSchoolYearID'=>$_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightYearGroupID'=>$t);
										$sqlEmail="(SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID
                                            FROM pupilsightPerson
                                            JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                                            JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightStaff.pupilsightPersonID)
                                            JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID)
                                            JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                                            WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID
                                            AND FIND_IN_SET(:pupilsightYearGroupID, pupilsightCourse.pupilsightYearGroupIDList)
                                            AND NOT pupilsightPerson.email=''
                                            AND pupilsightPerson.status='Full')
                                        UNION ALL (
                                            SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID
                                            FROM pupilsightPerson
                                            JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightPersonIDTutor=pupilsightPerson.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor2=pupilsightPerson.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor3=pupilsightPerson.pupilsightPersonID)
                                            JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                                            WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
                                            AND NOT email='' AND status='Full'
                                            AND pupilsightStudentEnrolment.pupilsightYearGroupID=:pupilsightYearGroupID
                                            GROUP BY pupilsightPerson.pupilsightPersonID
                                        )" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Year Group', $t, 'Email', $rowEmail["email"]);
									}
								}
								if ($students=="Y") {
									try {
										$dataEmail=array("pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"], "pupilsightYearGroupID"=>$t);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT email='' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightYearGroupID=:pupilsightYearGroupID" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Year Group', $t, 'Email', $rowEmail["email"]);
									}
								}
								if ($parents=="Y") {
									try {
										$dataStudents=array("pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"], "pupilsightYearGroupID"=>$t);
										$sqlStudents="SELECT pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightYearGroupID=:pupilsightYearGroupID" ;
										$resultStudents=$connection2->prepare($sqlStudents);
										$resultStudents->execute($dataStudents);
									}
									catch(PDOException $e) { }
									while ($rowStudents=$resultStudents->fetch()) {
										try {
											$dataFamily=array("pupilsightPersonID"=>$rowStudents["pupilsightPersonID"]);
											$sqlFamily="SELECT DISTINCT pupilsightFamily.pupilsightFamilyID FROM pupilsightFamily JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID" ;
											$resultFamily=$connection2->prepare($sqlFamily);
											$resultFamily->execute($dataFamily);
										}
										catch(PDOException $e) { }
										while ($rowFamily=$resultFamily->fetch()) {
											try {
												$dataEmail=array("pupilsightFamilyID"=>$rowFamily["pupilsightFamilyID"] );
												$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT email='' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactEmail='Y'" ;
												$resultEmail=$connection2->prepare($sqlEmail);
												$resultEmail->execute($dataEmail);
											}
											catch(PDOException $e) { }
											while ($rowEmail=$resultEmail->fetch()) {
												$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Year Group', $t, 'Email', $rowEmail["email"]);
											}
										}
									}
								}
							}
							if ($sms=="Y" AND $countryCode!="") {
								if ($staff=="Y") {
									try {
										$dataEmail=array();
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone1='' AND phone1Type='Mobile' AND status='Full')" ;
										$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone2='' AND phone2Type='Mobile' AND status='Full')" ;
										$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone3='' AND phone3Type='Mobile' AND status='Full')" ;
										$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone4='' AND phone4Type='Mobile' AND status='Full')" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Year Group', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
								if ($students=="Y") {
									try {
										$dataEmail=array("pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"], "pupilsightYearGroupID"=>$t);
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightYearGroupID=:pupilsightYearGroupID)" ;
										$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightYearGroupID=:pupilsightYearGroupID)" ;
										$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightYearGroupID=:pupilsightYearGroupID)" ;
										$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightYearGroupID=:pupilsightYearGroupID)" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Year Group', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
								if ($parents=="Y") {
									try {
										$dataStudents=array("pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"], "pupilsightYearGroupID"=>$t);
										$sqlStudents="SELECT pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightYearGroupID=:pupilsightYearGroupID" ;
										$resultStudents=$connection2->prepare($sqlStudents);
										$resultStudents->execute($dataStudents);
									}
									catch(PDOException $e) { }
									while ($rowStudents=$resultStudents->fetch()) {
										try {
											$dataFamily=array("pupilsightPersonID"=>$rowStudents["pupilsightPersonID"]);
											$sqlFamily="SELECT DISTINCT pupilsightFamily.pupilsightFamilyID FROM pupilsightFamily JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID" ;
											$resultFamily=$connection2->prepare($sqlFamily);
											$resultFamily->execute($dataFamily);
										}
										catch(PDOException $e) { }
										while ($rowFamily=$resultFamily->fetch()) {
											try {
												$dataEmail=array("pupilsightFamilyID"=>$rowFamily["pupilsightFamilyID"] );
												$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$resultEmail=$connection2->prepare($sqlEmail);
												$resultEmail->execute($dataEmail);
											}
											catch(PDOException $e) { }
											while ($rowEmail=$resultEmail->fetch()) {
												$countryCodeTemp = $countryCode;
												if ($rowEmail["countryCode"]=="") {
                                                    $countryCodeTemp = $rowEmail["countryCode"];
                                                }
												$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Year Group', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
											}
										}
									}
								}
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
								$data=array("AI"=>$AI, "t"=>$t, "staff"=>$staff, "students"=>$students, "parents"=>$parents);
								$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:AI, type='Roll Group', id=:t, staff=:staff, students=:students, parents=:parents" ;
								$result=$connection2->prepare($sql);
								$result->execute($data);
							}
							catch(PDOException $e) {
								$partialFail=TRUE;
							}

							//Get email addresses
							if ($email=="Y") {
								if ($staff=="Y") {
									try {
										$dataEmail=array("t"=>$t);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightPersonIDTutor=pupilsightPerson.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor2=pupilsightPerson.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor3=pupilsightPerson.pupilsightPersonID) WHERE NOT email='' AND status='Full' AND pupilsightRollGroupID=:t" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Roll Group', $t, 'Email', $rowEmail["email"]);
									}
								}
								if ($students=="Y") {
									try {
										$dataEmail=array("pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"], "pupilsightRollGroupID"=>$t);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT email='' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightRollGroupID=:pupilsightRollGroupID" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Roll Group', $t, 'Email', $rowEmail["email"]);
									}
								}
								if ($parents=="Y") {
									try {
										$dataStudents=array("pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"], "pupilsightRollGroupID"=>$t);
										$sqlStudents="SELECT DISTINCT pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightRollGroupID=:pupilsightRollGroupID" ;
										$resultStudents=$connection2->prepare($sqlStudents);
										$resultStudents->execute($dataStudents);
									}
									catch(PDOException $e) { }
									while ($rowStudents=$resultStudents->fetch()) {
										try {
											$dataFamily=array("pupilsightPersonID"=>$rowStudents["pupilsightPersonID"]);
											$sqlFamily="SELECT DISTINCT pupilsightFamily.pupilsightFamilyID FROM pupilsightFamily JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID" ;
											$resultFamily=$connection2->prepare($sqlFamily);
											$resultFamily->execute($dataFamily);
										}
										catch(PDOException $e) { }
										while ($rowFamily=$resultFamily->fetch()) {
											try {
												$dataEmail=array("pupilsightFamilyID"=>$rowFamily["pupilsightFamilyID"] );
												$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT email='' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactEmail='Y'" ;
												$resultEmail=$connection2->prepare($sqlEmail);
												$resultEmail->execute($dataEmail);
											}
											catch(PDOException $e) { }
											while ($rowEmail=$resultEmail->fetch()) {
												$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Roll Group', $t, 'Email', $rowEmail["email"]);
											}
										}
									}
								}
							}
							if ($sms=="Y" AND $countryCode!="") {
								if ($staff=="Y") {
									try {
										$dataEmail=array("pupilsightRollGroupID"=>$t);
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightPersonIDTutor=pupilsightPerson.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor2=pupilsightPerson.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor3=pupilsightPerson.pupilsightPersonID) WHERE NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND pupilsightRollGroupID=:pupilsightRollGroupID)" ;
										$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightPersonIDTutor=pupilsightPerson.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor2=pupilsightPerson.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor3=pupilsightPerson.pupilsightPersonID) WHERE NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND pupilsightRollGroupID=:pupilsightRollGroupID)" ;
										$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightPersonIDTutor=pupilsightPerson.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor2=pupilsightPerson.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor3=pupilsightPerson.pupilsightPersonID) WHERE NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND pupilsightRollGroupID=:pupilsightRollGroupID)" ;
										$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightPersonIDTutor=pupilsightPerson.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor2=pupilsightPerson.pupilsightPersonID OR pupilsightRollGroup.pupilsightPersonIDTutor3=pupilsightPerson.pupilsightPersonID) WHERE NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND pupilsightRollGroupID=:pupilsightRollGroupID)" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Roll Group', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
								if ($students=="Y") {
									try {
										$dataEmail=array("pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"], "pupilsightRollGroupID"=>$t);
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightRollGroupID=:pupilsightRollGroupID)" ;
										$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightRollGroupID=:pupilsightRollGroupID)" ;
										$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightRollGroupID=:pupilsightRollGroupID)" ;
										$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightRollGroupID=:pupilsightRollGroupID)" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Roll Group', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
								if ($parents=="Y") {
									try {
										$dataStudents=array("pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"], "pupilsightRollGroupID"=>$t);
										$sqlStudents="SELECT DISTINCT pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightRollGroupID=:pupilsightRollGroupID" ;
										$resultStudents=$connection2->prepare($sqlStudents);
										$resultStudents->execute($dataStudents);
									}
									catch(PDOException $e) { }
									while ($rowStudents=$resultStudents->fetch()) {
										try {
											$dataFamily=array("pupilsightPersonID"=>$rowStudents["pupilsightPersonID"]);
											$sqlFamily="SELECT DISTINCT pupilsightFamily.pupilsightFamilyID FROM pupilsightFamily JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID" ;
											$resultFamily=$connection2->prepare($sqlFamily);
											$resultFamily->execute($dataFamily);
										}
										catch(PDOException $e) { }
										while ($rowFamily=$resultFamily->fetch()) {
											try {
												$dataEmail=array("pupilsightFamilyID"=>$rowFamily["pupilsightFamilyID"] );
												$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$resultEmail=$connection2->prepare($sqlEmail);
												$resultEmail->execute($dataEmail);
											}
											catch(PDOException $e) { }
											while ($rowEmail=$resultEmail->fetch()) {
												$countryCodeTemp = $countryCode;
												if ($rowEmail["countryCode"]=="") {
                                                    $countryCodeTemp = $rowEmail["countryCode"];
                                                }
												$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Roll Group', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
											}
										}
									}
								}
							}
						}
					}
				}
			}

			//Course Groups
			/*if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_courses_my") OR isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_courses_any")) {
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
								$data=array("pupilsightMessengerID"=>$AI, "id"=>$t, "staff"=>$staff, "students"=>$students, "parents"=>$parents);
								$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Course', id=:id, staff=:staff, students=:students, parents=:parents" ;
								$result=$connection2->prepare($sql);
								$result->execute($data);
							}
							catch(PDOException $e) {
								$partialFail=TRUE;
							}

							//Get email addresses
							if ($email=="Y") {
								if ($staff=="Y") {
									try {
										$dataEmail=array("pupilsightCourseID"=>$t);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE (role='Teacher' OR role='Assistant' OR role='Technician') AND NOT email='' AND status='Full' AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Course', $t, 'Email', $rowEmail["email"]);
									}
								}
								if ($students=="Y") {
									try {
										$dataEmail=array("pupilsightCourseID"=>$t);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Student' AND NOT email='' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Course', $t, 'Email', $rowEmail["email"]);
									}
								}
								if ($parents=="Y") {
									try {
										$dataStudents=array("pupilsightCourseID"=>$t);
										$sqlStudents="SELECT DISTINCT pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID" ;
										$resultStudents=$connection2->prepare($sqlStudents);
										$resultStudents->execute($dataStudents);
									}
									catch(PDOException $e) { }
									while ($rowStudents=$resultStudents->fetch()) {
										try {
											$dataFamily=array("pupilsightPersonID"=>$rowStudents["pupilsightPersonID"]);
											$sqlFamily="SELECT DISTINCT pupilsightFamily.pupilsightFamilyID FROM pupilsightFamily JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID" ;
											$resultFamily=$connection2->prepare($sqlFamily);
											$resultFamily->execute($dataFamily);
										}
										catch(PDOException $e) { }
										while ($rowFamily=$resultFamily->fetch()) {
											try {
												$dataEmail=array("pupilsightFamilyID"=>$rowFamily["pupilsightFamilyID"] );
												$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT email='' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactEmail='Y'" ;
												$resultEmail=$connection2->prepare($sqlEmail);
												$resultEmail->execute($dataEmail);
											}
											catch(PDOException $e) { }
											while ($rowEmail=$resultEmail->fetch()) {
												$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Course', $t, 'Email', $rowEmail["email"]);
											}
										}
									}
									try {
										$dataEmail=array("pupilsightCourseID"=>$t);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Parent' AND NOT email='' AND status='Full' AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Course', $t, 'Email', $rowEmail["email"]);
									}
								}
							}
							if ($sms=="Y" AND $countryCode!="") {
								if ($staff=="Y") {
									try {
										$dataEmail=array("pupilsightCourseID"=>$t);
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE (role='Teacher' OR role='Assistant' OR role='Technician') AND NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID)" ;
										$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE (role='Teacher' OR role='Assistant' OR role='Technician') AND NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID)" ;
										$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE (role='Teacher' OR role='Assistant' OR role='Technician') AND NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID)" ;
										$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE (role='Teacher' OR role='Assistant' OR role='Technician') AND NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID)" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Course', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
								if ($students=="Y") {
									try {
										$dataEmail=array("pupilsightCourseID"=>$t);
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Student' AND NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID)" ;
										$sqlEmail.=" UNION (SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Student' AND NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID)" ;
										$sqlEmail.=" UNION (SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Student' AND NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID)" ;
										$sqlEmail.=" UNION (SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Student' AND NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID)" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Course', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
								if ($parents=="Y") {
									try {
										$dataStudents=array("pupilsightCourseID"=>$t);
										$sqlStudents="SELECT DISTINCT pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID" ;
										$resultStudents=$connection2->prepare($sqlStudents);
										$resultStudents->execute($dataStudents);
									}
									catch(PDOException $e) { }
									while ($rowStudents=$resultStudents->fetch()) {
										try {
											$dataFamily=array("pupilsightPersonID"=>$rowStudents["pupilsightPersonID"]);
											$sqlFamily="SELECT DISTINCT pupilsightFamily.pupilsightFamilyID FROM pupilsightFamily JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID" ;
											$resultFamily=$connection2->prepare($sqlFamily);
											$resultFamily->execute($dataFamily);
										}
										catch(PDOException $e) { }
										while ($rowFamily=$resultFamily->fetch()) {
											try {
												$dataEmail=array("pupilsightFamilyID"=>$rowFamily["pupilsightFamilyID"] );
												$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$resultEmail=$connection2->prepare($sqlEmail);
												$resultEmail->execute($dataEmail);
											}
											catch(PDOException $e) { }
											while ($rowEmail=$resultEmail->fetch()) {
												$countryCodeTemp = $countryCode;
												if ($rowEmail["countryCode"]=="") {
                                                    $countryCodeTemp = $rowEmail["countryCode"];
                                                }
												$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Course', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
											}
										}
									}
									try {
										$dataEmail=array("pupilsightCourseID"=>$t);
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Parent' AND NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID)" ;
										$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Parent' AND NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID)" ;
										$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Parent' AND NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID)" ;
										$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Parent' AND NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID)" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Course', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
							}
						}
					}
				}
			}*/

			//Class Groups
			/*if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_classes_my") OR isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_classes_any")) {
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
								$data=array("pupilsightMessengerID"=>$AI, "id"=>$t, "staff"=>$staff, "students"=>$students, "parents"=>$parents);
								$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Class', id=:id, staff=:staff, students=:students, parents=:parents" ;
								$result=$connection2->prepare($sql);
								$result->execute($data);
							}
							catch(PDOException $e) {
								$partialFail=TRUE;
							}

							//Get email addresses
							if ($email=="Y") {
								if ($staff=="Y") {
									try {
										$dataEmail=array("pupilsightCourseClassID"=>$t);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE (role='Teacher' OR role='Assistant' OR role='Technician') AND NOT email='' AND status='Full' AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Class', $t, 'Email', $rowEmail["email"]);
									}
								}
								if ($students=="Y") {
									try {
										$dataEmail=array("pupilsightCourseClassID"=>$t);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Student' AND NOT email='' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourseClass.pupilsightCourseClassID=$t" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Class', $t, 'Email', $rowEmail["email"]);
									}
								}
								if ($parents=="Y") {
									try {
										$dataStudents=array("pupilsightCourseClassID"=>$t);
										$sqlStudents="SELECT DISTINCT pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID" ;
										$resultStudents=$connection2->prepare($sqlStudents);
										$resultStudents->execute($dataStudents);
									}
									catch(PDOException $e) { }
									while ($rowStudents=$resultStudents->fetch()) {
										try {
											$dataFamily=array("pupilsightPersonID"=>$rowStudents["pupilsightPersonID"]);
											$sqlFamily="SELECT DISTINCT pupilsightFamily.pupilsightFamilyID FROM pupilsightFamily JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID" ;
											$resultFamily=$connection2->prepare($sqlFamily);
											$resultFamily->execute($dataFamily);
										}
										catch(PDOException $e) { }
										while ($rowFamily=$resultFamily->fetch()) {
											try {
												$dataEmail=array("pupilsightFamilyID"=>$rowFamily["pupilsightFamilyID"] );
												$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT email='' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactEmail='Y'" ;
												$resultEmail=$connection2->prepare($sqlEmail);
												$resultEmail->execute($dataEmail);
											}
											catch(PDOException $e) { }
											while ($rowEmail=$resultEmail->fetch()) {
												$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Class', $t, 'Email', $rowEmail["email"]);
											}
										}
									}
									try {
										$dataEmail=array("pupilsightCourseClassID"=>$t);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE role='Parent' AND NOT email='' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Class', $t, 'Email', $rowEmail["email"]);
									}
								}
							}
							if ($sms=="Y" AND $countryCode!="") {
								if ($staff=="Y") {
									try {
										$dataEmail=array("pupilsightCourseClassID"=>$t);
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE (role='Teacher' OR role='Assistant' OR role='Technician') AND NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID)" ;
										$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE (role='Teacher' OR role='Assistant' OR role='Technician') AND NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID)" ;
										$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE (role='Teacher' OR role='Assistant' OR role='Technician') AND NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID)" ;
										$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE (role='Teacher' OR role='Assistant' OR role='Technician') AND NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID)" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Class', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
								if ($students=="Y") {
									try {
										$dataEmail=array("pupilsightCourseClassID"=>$t);
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Student' AND NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourseClass.pupilsightCourseClassID=$t)" ;
										$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Student' AND NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourseClass.pupilsightCourseClassID=$t)" ;
										$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Student' AND NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourseClass.pupilsightCourseClassID=$t)" ;
										$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Student' AND NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourseClass.pupilsightCourseClassID=$t)" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Class', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
								if ($parents=="Y") {
									try {
										$dataStudents=array("pupilsightCourseClassID"=>$t);
										$sqlStudents="SELECT DISTINCT pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID" ;
										$resultStudents=$connection2->prepare($sqlStudents);
										$resultStudents->execute($dataStudents);
									}
									catch(PDOException $e) { }
									while ($rowStudents=$resultStudents->fetch()) {
										try {
											$dataFamily=array("pupilsightPersonID"=>$rowStudents["pupilsightPersonID"]);
											$sqlFamily="SELECT DISTINCT pupilsightFamily.pupilsightFamilyID FROM pupilsightFamily JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID" ;
											$resultFamily=$connection2->prepare($sqlFamily);
											$resultFamily->execute($dataFamily);
										}
										catch(PDOException $e) { }
										while ($rowFamily=$resultFamily->fetch()) {
											try {
												$dataEmail=array("pupilsightFamilyID"=>$rowFamily["pupilsightFamilyID"] );
												$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$resultEmail=$connection2->prepare($sqlEmail);
												$resultEmail->execute($dataEmail);
											}
											catch(PDOException $e) { }
											while ($rowEmail=$resultEmail->fetch()) {
												$countryCodeTemp = $countryCode;
												if ($rowEmail["countryCode"]=="") {
                                                    $countryCodeTemp = $rowEmail["countryCode"];
                                                }
												$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Class', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
											}
										}
									}
									try {
										$dataEmail=array("pupilsightCourseClassID"=>$t);
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE role='Parent' AND NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID)" ;
										$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE role='Parent' AND NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID)" ;
										$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE role='Parent' AND NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID)" ;
										$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE role='Parent' AND NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID)" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Class', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
							}
						}
					}
				}
			}*/

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
								$data=array("pupilsightMessengerID"=>$AI, "id"=>$t, "staff"=>$staff, "students"=>$students, "parents"=>$parents);
								$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Activity', id=:id, staff=:staff, students=:students, parents=:parents" ;
								$result=$connection2->prepare($sql);
								$result->execute($data);
							}
							catch(PDOException $e) {
								$partialFail=TRUE;
							}

							//Get email addresses
							if ($email=="Y") {
								if ($staff=="Y") {
									try {
										$dataEmail=array("pupilsightActivityID"=>$t);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightActivityStaff ON (pupilsightActivityStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightActivityStaff.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE NOT email='' AND status='Full' AND pupilsightActivity.pupilsightActivityID=:pupilsightActivityID" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Activity', $t, 'Email', $rowEmail["email"]);
									}
								}
								if ($students=="Y") {
									try {
										$dataEmail=array("pupilsightActivityID"=>$t);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE NOT email='' AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightActivityStudent.status='Accepted' AND pupilsightActivity.pupilsightActivityID=:pupilsightActivityID" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Activity', $t, 'Email', $rowEmail["email"]);
									}
								}
								if ($parents=="Y") {
									try {
										$dataStudents=array("pupilsightActivityID"=>$t);
										$sqlStudents="SELECT DISTINCT pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightActivityStudent.status='Accepted' AND pupilsightActivity.pupilsightActivityID=:pupilsightActivityID" ;
										$resultStudents=$connection2->prepare($sqlStudents);
										$resultStudents->execute($dataStudents);
									}
									catch(PDOException $e) { }
									while ($rowStudents=$resultStudents->fetch()) {
										try {
											$dataFamily=array("pupilsightPersonID"=>$rowStudents["pupilsightPersonID"]);
											$sqlFamily="SELECT DISTINCT pupilsightFamily.pupilsightFamilyID FROM pupilsightFamily JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID" ;
											$resultFamily=$connection2->prepare($sqlFamily);
											$resultFamily->execute($dataFamily);
										}
										catch(PDOException $e) { }
										while ($rowFamily=$resultFamily->fetch()) {
											try {
												$dataEmail=array("pupilsightFamilyID"=>$rowFamily["pupilsightFamilyID"] );
												$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT email='' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactEmail='Y'" ;
												$resultEmail=$connection2->prepare($sqlEmail);
												$resultEmail->execute($dataEmail);
											}
											catch(PDOException $e) { }
											while ($rowEmail=$resultEmail->fetch()) {
												$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Activity', $t, 'Email', $rowEmail["email"]);
											}
										}
									}
								}
							}
							if ($sms=="Y" AND $countryCode!="") {
								if ($staff=="Y") {
									try {
										$dataEmail=array("pupilsightActivityID"=>$t);
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightActivityStaff ON (pupilsightActivityStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightActivityStaff.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND pupilsightActivity.pupilsightActivityID=:pupilsightActivityID)" ;
										$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightActivityStaff ON (pupilsightActivityStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightActivityStaff.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND pupilsightActivity.pupilsightActivityID=:pupilsightActivityID)" ;
										$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightActivityStaff ON (pupilsightActivityStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightActivityStaff.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND pupilsightActivity.pupilsightActivityID=:pupilsightActivityID)" ;
										$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightActivityStaff ON (pupilsightActivityStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightActivityStaff.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND pupilsightActivity.pupilsightActivityID=:pupilsightActivityID)" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Activity', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
								if ($students=="Y") {
									try {
										$dataEmail=array("pupilsightActivityID"=>$t);
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE NOT phone1='' AND phone1Type='Mobile' AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightActivityStudent.status='Accepted' AND pupilsightActivity.pupilsightActivityID=:pupilsightActivityID)" ;
										$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE NOT phone2='' AND phone2Type='Mobile' AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightActivityStudent.status='Accepted' AND pupilsightActivity.pupilsightActivityID=:pupilsightActivityID)" ;
										$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE NOT phone3='' AND phone3Type='Mobile' AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightActivityStudent.status='Accepted' AND pupilsightActivity.pupilsightActivityID=:pupilsightActivityID)" ;
										$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE NOT phone4='' AND phone4Type='Mobile' AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightActivityStudent.status='Accepted' AND pupilsightActivity.pupilsightActivityID=:pupilsightActivityID)" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Activity', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
								if ($parents=="Y") {
									try {
										$dataStudents=array("pupilsightActivityID"=>$t);
										$sqlStudents="SELECT DISTINCT pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightActivity ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightActivityStudent.status='Accepted' AND pupilsightActivity.pupilsightActivityID=:pupilsightActivityID" ;
										$resultStudents=$connection2->prepare($sqlStudents);
										$resultStudents->execute($dataStudents);
									}
									catch(PDOException $e) { }
									while ($rowStudents=$resultStudents->fetch()) {
										try {
											$dataFamily=array("pupilsightPersonID"=>$rowStudents["pupilsightPersonID"]);
											$sqlFamily="SELECT DISTINCT pupilsightFamily.pupilsightFamilyID FROM pupilsightFamily JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID" ;
											$resultFamily=$connection2->prepare($sqlFamily);
											$resultFamily->execute($dataFamily);
										}
										catch(PDOException $e) { }
										while ($rowFamily=$resultFamily->fetch()) {
											try {
												$dataEmail=array("pupilsightFamilyID"=>$rowFamily["pupilsightFamilyID"] );
												$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$resultEmail=$connection2->prepare($sqlEmail);
												$resultEmail->execute($dataEmail);
											}
											catch(PDOException $e) { print $e->getMessage() ;}
											while ($rowEmail=$resultEmail->fetch()) {
												$countryCodeTemp = $countryCode;
												if ($rowEmail["countryCode"]=="") {
                                                    $countryCodeTemp = $rowEmail["countryCode"];
                                                }
												$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Activity', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
											}
										}
									}
								}
							}
						}
					}
				}
			}

			//Applicants
			if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_applicants")) {
				if ($_POST["applicants"] == "Y") {
					$applicantsWhere = "AND NOT status IN ('Waiting List', 'Rejected', 'Withdrawn', 'Pending')";

					$choices=$_POST["applicantList"] ;
					if ($choices!="") {
						foreach ($choices as $t) {
							try {
								$data=array("pupilsightMessengerID"=>$AI, "id"=>$t);
								$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Applicants', id=:id" ;
								$result=$connection2->prepare($sql);
								$result->execute($data);
							}
							catch(PDOException $e) {
								$partialFail=TRUE;
							}

							if ($email=="Y") {
								//Get applicant emails
								try {
									$dataEmail=array("pupilsightSchoolYearIDEntry"=>$t);
									$sqlEmail="SELECT DISTINCT email FROM pupilsightApplicationForm WHERE NOT email='' AND pupilsightSchoolYearIDEntry=:pupilsightSchoolYearIDEntry $applicantsWhere" ;
									$resultEmail=$connection2->prepare($sqlEmail);
									$resultEmail->execute($dataEmail);
								}
								catch(PDOException $e) { }
								while ($rowEmail=$resultEmail->fetch()) {
									$report = reportAdd($report, $emailReceipt, NULL, 'Applicants', $t, 'Email', $rowEmail["email"]);
								}

								//Get parent 1 emails
								try {
									$dataEmail=array("pupilsightSchoolYearIDEntry"=>$t);
									$sqlEmail="SELECT DISTINCT parent1email FROM pupilsightApplicationForm WHERE NOT parent1email='' AND pupilsightSchoolYearIDEntry=:pupilsightSchoolYearIDEntry $applicantsWhere" ;
									$resultEmail=$connection2->prepare($sqlEmail);
									$resultEmail->execute($dataEmail);
								}
								catch(PDOException $e) { }
								while ($rowEmail=$resultEmail->fetch()) {
									$report = reportAdd($report, $emailReceipt, NULL, 'Applicants', $t, 'Email', $rowEmail["parent1email"]);
								}

								//Get parent 2 emails
								try {
									$dataEmail=array("pupilsightSchoolYearIDEntry"=>$t);
									$sqlEmail="SELECT DISTINCT parent2email FROM pupilsightApplicationForm WHERE NOT parent2email='' AND pupilsightSchoolYearIDEntry=:pupilsightSchoolYearIDEntry $applicantsWhere" ;
									$resultEmail=$connection2->prepare($sqlEmail);
									$resultEmail->execute($dataEmail);
								}
								catch(PDOException $e) { }
								while ($rowEmail=$resultEmail->fetch()) {
									$report = reportAdd($report, $emailReceipt, NULL, 'Applicants', $t, 'Email', $rowEmail["parent2email"]);
								}

								//Get parent ID emails (when no family in system, but user is in system)
								try {
									$dataEmail=array("pupilsightSchoolYearIDEntry"=>$t);
									$sqlEmail="SELECT pupilsightPerson.email, pupilsightPerson.pupilsightPersonID FROM pupilsightApplicationForm JOIN pupilsightPerson ON (pupilsightApplicationForm.parent1pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT parent1pupilsightPersonID='' AND pupilsightSchoolYearIDEntry=:pupilsightSchoolYearIDEntry $applicantsWhere" ;
									$resultEmail=$connection2->prepare($sqlEmail);
									$resultEmail->execute($dataEmail);
								}
								catch(PDOException $e) { }
								while ($rowEmail=$resultEmail->fetch()) {
									$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Applicant', $t, 'Email', $rowEmail["email"]);
								}

								//Get family emails
								try {
									$dataEmail=array("pupilsightSchoolYearIDEntry"=>$t);
									$sqlEmail="SELECT * FROM pupilsightApplicationForm WHERE NOT pupilsightFamilyID='' AND pupilsightSchoolYearIDEntry=:pupilsightSchoolYearIDEntry $applicantsWhere" ;
									$resultEmail=$connection2->prepare($sqlEmail);
									$resultEmail->execute($dataEmail);
								}
								catch(PDOException $e) { }
								while ($rowEmail=$resultEmail->fetch()) {
									try {
										$dataEmail2=array("pupilsightFamilyID"=>$rowEmail["pupilsightFamilyID"]);
										$sqlEmail2="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT email='' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactEmail='Y'" ;
										$resultEmail2=$connection2->prepare($sqlEmail2);
										$resultEmail2->execute($dataEmail2);
									}
									catch(PDOException $e) { }
									while ($rowEmail2=$resultEmail2->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail2['pupilsightPersonID'], 'Applicant', $t, 'Email', $rowEmail2["email"]);
									}
								}
							}
							if ($sms=="Y" AND $countryCode!="") {
								//Get applicant phone numbers
								try {
									$dataEmail=array("pupilsightSchoolYearIDEntry"=>$t);
									$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode FROM pupilsightApplicationForm WHERE NOT phone1='' AND phone1Type='Mobile' AND pupilsightSchoolYearIDEntry=:pupilsightSchoolYearIDEntry $applicantsWhere)" ;
									$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode FROM pupilsightApplicationForm WHERE NOT phone2='' AND phone2Type='Mobile' AND pupilsightSchoolYearIDEntry=:pupilsightSchoolYearIDEntry $applicantsWhere)" ;
									$resultEmail=$connection2->prepare($sqlEmail);
									$resultEmail->execute($dataEmail);
								}
								catch(PDOException $e) { }
								while ($rowEmail=$resultEmail->fetch()) {
									$countryCodeTemp = $countryCode;
									if ($rowEmail["countryCode"]=="") {
                                        $countryCodeTemp = $rowEmail["countryCode"];
                                    }
									$report = reportAdd($report, $emailReceipt, NULL, 'Applicants', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
								}

								//Get parent 1 numbers
								try {
									$dataEmail=array("pupilsightSchoolYearIDEntry"=>$t);
									$sqlEmail="(SELECT CONCAT(parent1phone1CountryCode,parent1phone1) AS phone, parent1phone1CountryCode AS countryCode FROM pupilsightApplicationForm WHERE NOT parent1phone1='' AND parent1phone1Type='Mobile' AND parent1phone1CountryCode='$countryCode' AND pupilsightSchoolYearIDEntry=:pupilsightSchoolYearIDEntry $applicantsWhere)" ;
									$sqlEmail.=" UNION (SELECT CONCAT(parent1phone2CountryCode,parent1phone2) AS phone, parent1phone2CountryCode AS countryCode FROM pupilsightApplicationForm WHERE NOT parent1phone2='' AND parent1phone2Type='Mobile' AND parent1phone2CountryCode='$countryCode' AND pupilsightSchoolYearIDEntry=:pupilsightSchoolYearIDEntry $applicantsWhere)" ;
									$resultEmail=$connection2->prepare($sqlEmail);
									$resultEmail->execute($dataEmail);
								}
								catch(PDOException $e) { }
								while ($rowEmail=$resultEmail->fetch()) {
									$countryCodeTemp = $countryCode;
									if ($rowEmail["countryCode"]=="") {
                                        $countryCodeTemp = $rowEmail["countryCode"];
                                    }
									$report = reportAdd($report, $emailReceipt, NULL, 'Applicants', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
								}

								//Get parent 2 numbers
								try {
									$dataEmail=array("pupilsightSchoolYearIDEntry"=>$t);
									$sqlEmail="(SELECT CONCAT(parent2phone1CountryCode,parent2phone1) AS phone, parent2phone1CountryCode AS countryCode FROM pupilsightApplicationForm WHERE NOT parent2phone1='' AND parent2phone1Type='Mobile' AND parent2phone1CountryCode='$countryCode' AND pupilsightSchoolYearIDEntry=:pupilsightSchoolYearIDEntry $applicantsWhere)" ;
									$sqlEmail.=" UNION (SELECT CONCAT(parent2phone2CountryCode,parent2phone2) AS phone, parent2phone2CountryCode AS countryCode FROM pupilsightApplicationForm WHERE NOT parent2phone2='' AND parent2phone2Type='Mobile' AND parent2phone2CountryCode='$countryCode' AND pupilsightSchoolYearIDEntry=:pupilsightSchoolYearIDEntry $applicantsWhere)" ;
									$resultEmail=$connection2->prepare($sqlEmail);
									$resultEmail->execute($dataEmail);
								}
								catch(PDOException $e) { }
								while ($rowEmail=$resultEmail->fetch()) {
									$countryCodeTemp = $countryCode;
									if ($rowEmail["countryCode"]=="") {
                                        $countryCodeTemp = $rowEmail["countryCode"];
                                    }
									$report = reportAdd($report, $emailReceipt, NULL, 'Applicants', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
								}

								//Get parent ID numbers (when no family in system, but user is in system)
								try {
									$dataEmail=array("pupilsightSchoolYearIDEntry"=>$t);
									$sqlEmail="(SELECT CONCAT(pupilsightPerson.phone1CountryCode,pupilsightPerson.phone1) AS phone, pupilsightPerson.pupilsightPersonID FROM pupilsightApplicationForm JOIN pupilsightPerson ON (pupilsightApplicationForm.parent1pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT pupilsightPerson.phone1='' AND pupilsightPerson.phone1Type='Mobile' AND pupilsightPerson.phone1CountryCode='$countryCode' AND NOT parent1pupilsightPersonID='' AND pupilsightSchoolYearIDEntry=:pupilsightSchoolYearIDEntry $applicantsWhere)" ;
									$sqlEmail.=" UNION (SELECT CONCAT(pupilsightPerson.phone2CountryCode,pupilsightPerson.phone2) AS phone, pupilsightPerson.pupilsightPersonID FROM pupilsightApplicationForm JOIN pupilsightPerson ON (pupilsightApplicationForm.parent1pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT pupilsightPerson.phone2='' AND pupilsightPerson.phone2Type='Mobile' AND pupilsightPerson.phone2CountryCode='$countryCode' AND NOT parent1pupilsightPersonID='' AND pupilsightSchoolYearIDEntry=:pupilsightSchoolYearIDEntry $applicantsWhere)" ;
									$sqlEmail.=" UNION (SELECT CONCAT(pupilsightPerson.phone3CountryCode,pupilsightPerson.phone3) AS phone, pupilsightPerson.pupilsightPersonID FROM pupilsightApplicationForm JOIN pupilsightPerson ON (pupilsightApplicationForm.parent1pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT pupilsightPerson.phone3='' AND pupilsightPerson.phone3Type='Mobile' AND pupilsightPerson.phone3CountryCode='$countryCode' AND NOT parent1pupilsightPersonID='' AND pupilsightSchoolYearIDEntry=:pupilsightSchoolYearIDEntry $applicantsWhere)" ;
									$sqlEmail.=" UNION (SELECT CONCAT(pupilsightPerson.phone4CountryCode,pupilsightPerson.phone4) AS phone, pupilsightPerson.pupilsightPersonID FROM pupilsightApplicationForm JOIN pupilsightPerson ON (pupilsightApplicationForm.parent1pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT pupilsightPerson.phone4='' AND pupilsightPerson.phone4Type='Mobile' AND pupilsightPerson.phone4CountryCode='$countryCode' AND NOT parent1pupilsightPersonID='' AND pupilsightSchoolYearIDEntry=:pupilsightSchoolYearIDEntry $applicantsWhere)" ;
									$resultEmail=$connection2->prepare($sqlEmail);
									$resultEmail->execute($dataEmail);
								}
								catch(PDOException $e) { }
								while ($rowEmail=$resultEmail->fetch()) {
									$countryCodeTemp = $countryCode;
									if ($rowEmail["countryCode"]=="") {
                                        $countryCodeTemp = $rowEmail["countryCode"];
                                    }
									$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Applicants', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
								}

								//Get family numbers
								try {
									$dataEmail=array("pupilsightSchoolYearIDEntry"=>$t);
									$sqlEmail="SELECT * FROM pupilsightApplicationForm WHERE NOT pupilsightFamilyID='' AND pupilsightSchoolYearIDEntry=:pupilsightSchoolYearIDEntry $applicantsWhere" ;
									$resultEmail=$connection2->prepare($sqlEmail);
									$resultEmail->execute($dataEmail);
								}
								catch(PDOException $e) { }
								while ($rowEmail=$resultEmail->fetch()) {
									try {
										$dataEmail2=array("pupilsightFamilyID"=>$rowEmail["pupilsightFamilyID"]);
										$sqlEmail2="(SELECT CONCAT(pupilsightPerson.phone1CountryCode,pupilsightPerson.phone1) AS phone, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT pupilsightPerson.phone1='' AND pupilsightPerson.phone1Type='Mobile' AND pupilsightPerson.phone1CountryCode='$countryCode' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID)" ;
										$sqlEmail2.=" UNION (SELECT CONCAT(pupilsightPerson.phone2CountryCode,pupilsightPerson.phone2) AS phone, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT pupilsightPerson.phone2='' AND pupilsightPerson.phone2Type='Mobile' AND pupilsightPerson.phone2CountryCode='$countryCode' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID)" ;
										$sqlEmail2.=" UNION (SELECT CONCAT(pupilsightPerson.phone3CountryCode,pupilsightPerson.phone3) AS phone, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT pupilsightPerson.phone3='' AND pupilsightPerson.phone3Type='Mobile' AND pupilsightPerson.phone3CountryCode='$countryCode' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID)" ;
										$sqlEmail2.=" UNION (SELECT CONCAT(pupilsightPerson.phone4CountryCode,pupilsightPerson.phone4) AS phone, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT pupilsightPerson.phone4='' AND pupilsightPerson.phone4Type='Mobile' AND pupilsightPerson.phone4CountryCode='$countryCode' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID)" ;
										$resultEmail2=$connection2->prepare($sqlEmail2);
										$resultEmail2->execute($dataEmail2);
									}
									catch(PDOException $e) { }
									while ($rowEmail2=$resultEmail2->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail2["countryCode"]=="")
											$countryCodeTemp = $rowEmail2["countryCode"];
										$report = reportAdd($report, $emailReceipt, $rowEmail2['pupilsightPersonID'], 'Applicants', $t, 'SMS', $countryCodeTemp.$rowEmail2["phone"]);
									}
								}
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
								$data=array("pupilsightMessengerID"=>$AI, "id"=>$t);
								$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Houses', id=:id" ;
								$result=$connection2->prepare($sql);
								$result->execute($data);
							}
							catch(PDOException $e) {
								$partialFail=TRUE;
							}

							if ($email=="Y") {
								try {
									$dataEmail=array("pupilsightHouseID"=>$t);
									$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT email='' AND pupilsightHouseID=:pupilsightHouseID AND status='Full'" ;
									$resultEmail=$connection2->prepare($sqlEmail);
									$resultEmail->execute($dataEmail);
								}
								catch(PDOException $e) { }
								while ($rowEmail=$resultEmail->fetch()) {
									$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Houses', $t, 'Email', $rowEmail["email"]);
								}
							}
							if ($sms=="Y" AND $countryCode!="") {
								try {
									$dataEmail=array("pupilsightHouseID"=>$t);
									$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone1='' AND phone1Type='Mobile' AND pupilsightHouseID=:pupilsightHouseID AND status='Full')" ;
									$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone2='' AND phone2Type='Mobile' AND pupilsightHouseID=:pupilsightHouseID AND status='Full')" ;
									$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone3='' AND phone3Type='Mobile' AND pupilsightHouseID=:pupilsightHouseID AND status='Full')" ;
									$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone4='' AND phone4Type='Mobile' AND pupilsightHouseID=:pupilsightHouseID AND status='Full')" ;
									$resultEmail=$connection2->prepare($sqlEmail);
									$resultEmail->execute($dataEmail);
								}
								catch(PDOException $e) { }
								while ($rowEmail=$resultEmail->fetch()) {
									$countryCodeTemp = $countryCode;
									if ($rowEmail["countryCode"]=="") {
                                        $countryCodeTemp = $rowEmail["countryCode"];
                                    }
									$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Houses', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
								}
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
								$data=array("AI"=>$AI, "t"=>$t, "staff"=>$staff, "students"=>$students, "parents"=>$parents);
								$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:AI, type='Transport', id=:t, staff=:staff, students=:students, parents=:parents" ;
								$result=$connection2->prepare($sql);
								$result->execute($data);
							}
							catch(PDOException $e) {
								$partialFail=TRUE;
							}

							//Get email addresses
							if ($email=="Y") {
								if ($staff=="Y") {
									try {
										$dataEmail=array("transport"=>$t);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT email='' AND status='Full' AND transport=:transport" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Transport', $t, 'Email', $rowEmail["email"]);
									}
								}
								if ($students=="Y") {
									try {
										$dataEmail=array("pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"], "transport"=>$t);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT email='' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND transport=:transport" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Transport', $t, 'Email', $rowEmail["email"]);
									}
								}
								if ($parents=="Y") {
									try {
										$dataStudents=array("pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"], "transport"=>$t);
										$sqlStudents="SELECT pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND transport=:transport" ;
										$resultStudents=$connection2->prepare($sqlStudents);
										$resultStudents->execute($dataStudents);
									}
									catch(PDOException $e) { }
									while ($rowStudents=$resultStudents->fetch()) {
										try {
											$dataFamily=array("pupilsightPersonID"=>$rowStudents["pupilsightPersonID"]);
											$sqlFamily="SELECT DISTINCT pupilsightFamily.pupilsightFamilyID FROM pupilsightFamily JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID" ;
											$resultFamily=$connection2->prepare($sqlFamily);
											$resultFamily->execute($dataFamily);
										}
										catch(PDOException $e) { }
										while ($rowFamily=$resultFamily->fetch()) {
											try {
												$dataEmail=array("pupilsightFamilyID"=>$rowFamily["pupilsightFamilyID"] );
												$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT email='' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactEmail='Y'" ;
												$resultEmail=$connection2->prepare($sqlEmail);
												$resultEmail->execute($dataEmail);
											}
											catch(PDOException $e) { }
											while ($rowEmail=$resultEmail->fetch()) {
												$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Transport', $t, 'Email', $rowEmail["email"]);
											}
										}
									}
								}
							}
							if ($sms=="Y" AND $countryCode!="") {
								if ($staff=="Y") {
									try {
										$dataEmail=array("transport"=>$t);
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND transport=:transport)" ;
										$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND transport=:transport)" ;
										$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND transport=:transport)" ;
										$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND transport=:transport)" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Transport', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
								if ($students=="Y") {
									try {
										$dataEmail=array("pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"], "transport"=>$t);
										$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND transport=:transport)" ;
										$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND transport=:transport)" ;
										$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND transport=:transport)" ;
										$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND transport=:transport)" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Transport', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
								if ($parents=="Y") {
									try {
										$dataStudents=array("pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"], "transport"=>$t);
										$sqlStudents="SELECT pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND transport=:transport" ;
										$resultStudents=$connection2->prepare($sqlStudents);
										$resultStudents->execute($dataStudents);
									}
									catch(PDOException $e) { }
									while ($rowStudents=$resultStudents->fetch()) {
										try {
											$dataFamily=array("pupilsightPersonID"=>$rowStudents["pupilsightPersonID"]);
											$sqlFamily="SELECT DISTINCT pupilsightFamily.pupilsightFamilyID FROM pupilsightFamily JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID" ;
											$resultFamily=$connection2->prepare($sqlFamily);
											$resultFamily->execute($dataFamily);
										}
										catch(PDOException $e) { }
										while ($rowFamily=$resultFamily->fetch()) {
											try {
												$dataEmail=array("pupilsightFamilyID"=>$rowFamily["pupilsightFamilyID"] );
												$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y')" ;
												$resultEmail=$connection2->prepare($sqlEmail);
												$resultEmail->execute($dataEmail);
											}
											catch(PDOException $e) { }
											while ($rowEmail=$resultEmail->fetch()) {
												$countryCodeTemp = $countryCode;
												if ($rowEmail["countryCode"]=="") {
                                                    $countryCodeTemp = $rowEmail["countryCode"];
                                                }
												$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Transport', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
											}
										}
									}
								}
							}
						}
					}
				}
			}

            //Target Absent students / Attendance Status
            if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_attendance")) {
                if ($_POST["attendance"]=="Y") {
                    $choices=$_POST["attendanceStatus"];
                    $students=$_POST["attendanceStudents"];
                    $parents=$_POST["attendanceParents"];
                    $selectedDate=dateConvert($guid, $_POST["attendanceDate"]);
                    if ($choices!="") {
                        foreach ($choices as $t) {
      						try {
      							$data=array("AI"=>$AI, "t"=>$t." ".$selectedDate, "students"=>$students, "parents"=>$parents);
      							$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:AI, type='Attendance', id=:t, students=:students, parents=:parents" ;
      							$result=$connection2->prepare($sql);
      							$result->execute($data);
      						}
      						catch(PDOException $e) {
      							$partialFail=TRUE;
      						}
                        }
                        //Get all logs by student, with latest log entry first.
                        try {
                          $data=array("selectedDate"=>$selectedDate, "pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"], "nowDate"=>date("Y-m-d"));
                          $sql="SELECT galp.pupilsightPersonID, galp.pupilsightAttendanceLogPersonID, galp.type, galp.date FROM pupilsightAttendanceLogPerson AS galp JOIN pupilsightStudentEnrolment AS gse ON (galp.pupilsightPersonID=gse.pupilsightPersonID) JOIN pupilsightPerson AS gp ON (gse.pupilsightPersonID=gp.pupilsightPersonID) WHERE gp.status='Full' AND (gp.dateStart IS NULL OR gp.dateStart<=:nowDate) AND (gp.dateEnd IS NULL OR gp.dateEnd>=:nowDate) AND gse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND galp.date=:selectedDate ORDER BY galp.pupilsightPersonID, pupilsightAttendanceLogPersonID DESC" ;
                          $result=$connection2->prepare($sql);
                          $result->execute($data);
                        }
                        catch(PDOException $e) { }

                        if ($result->rowCount()<1) { //Check we have some attendance logs for this date
                        //No attendance data
                        }
                        else { //Log the personIDs of the students whose latest attendance log is in list of choices submitted by user
                          $selectedStudents=array();
                          $currentStudent="";
                          $lastStudent="";
                          while ($row=$result->fetch()) {
                            $currentStudent=$row["pupilsightPersonID"] ;
                            if (in_array($row["type"], $choices) AND $currentStudent!=$lastStudent) {
                              $selectedStudents[]=$currentStudent ;
                            }
                            $lastStudent=$currentStudent ;
                          }

                          if (count($selectedStudents)<1) {
                          //If we have no students
                          }
                          else {
                            if ($parents=="Y" AND ($email=="Y" OR ($sms=="Y" AND $countryCode!=""))) {
                              try { //Get the familyIDs for each student logged
                                $dataFamily=array();
                                $sqlFamily="SELECT DISTINCT pupilsightFamilyID FROM pupilsightFamilyChild WHERE pupilsightPersonID IN (".implode(",",$selectedStudents).")" ;
                                $resultFamily=$connection2->prepare($sqlFamily);
                                $resultFamily->execute($dataFamily);

                                $resultFamilies = $resultFamily->fetchAll();
                              }
                              catch(PDOException $e) { }
                            }

                            //Get emails
                            if ($email=="Y") {
                              if ($parents=="Y") {
                                foreach ($resultFamilies as $rowFamily) { //Get the emails for each familyID
                                  try {
                                    $dataEmail=array("pupilsightFamilyID"=>$rowFamily["pupilsightFamilyID"] );
                                    $sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT email='' AND status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactEmail='Y'" ;
                                    $resultEmail=$connection2->prepare($sqlEmail);
                                    $resultEmail->execute($dataEmail);
                                  }
                                  catch(PDOException $e) { }
                                  while ($rowEmail=$resultEmail->fetch()) { //Add emails to list of receivers
                                    $report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Attendance', $t, 'Email', $rowEmail["email"]);
                                  }
                                }
                              }
                              if ($students=="Y") {
                                try { //Get the email for each student
                                  $dataEmail=array("pupilsightSchoolYearID"=>$_SESSION[$guid]["pupilsightSchoolYearID"], "pupilsightPersonIDs"=>join(",",$selectedStudents));
                                  $sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT email='' AND status='Full' AND (dateStart IS NULL OR dateStart<='" . date("Y-m-d") . "') AND (dateEnd IS NULL  OR dateEnd>='" . date("Y-m-d") . "') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPerson.pupilsightPersonID IN (:pupilsightPersonIDs)" ;
                                  $resultEmail=$connection2->prepare($sqlEmail);
                                  $resultEmail->execute($dataEmail);
                                }
                                catch(PDOException $e) { }
                                while ($rowEmail=$resultEmail->fetch()) { //Add emails to list of receivers
                                  $report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Attendance', $t, 'Email', $rowEmail["email"]);
                                }
                              }
                            } //end get emails
                            //Get SMS
                            if ($sms=="Y" AND $countryCode!="") {
                              if ($parents=="Y") {
                                foreach ($resultFamilies as $rowFamily) { //Get the people for each familyID
                                  try {
                                    $dataPerson=array("pupilsightFamilyID"=>$rowFamily["pupilsightFamilyID"] );
                                    $sqlPerson="SELECT DISTINCT pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full' AND pupilsightFamilyAdult.pupilsightFamilyID=:pupilsightFamilyID AND contactSMS='Y'" ;
                                    $resultPerson=$connection2->prepare($sqlPerson);
                                    $resultPerson->execute($dataPerson);
                                  }
                                  catch(PDOException $e) { }
                                  while ($rowPerson=$resultPerson->fetch()) { //Add phone numbers to SMS receivers
                                    try {
                                      $dataSMS=array("pupilsightPersonID"=>$rowPerson["pupilsightPersonID"] );
                                      $sqlSMS="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone1='' AND phone1Type='Mobile' AND pupilsightPersonID=:pupilsightPersonID)" ;
                                      $sqlSMS.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone2='' AND phone2Type='Mobile' AND pupilsightPersonID=:pupilsightPersonID)" ;
                                      $sqlSMS.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone3='' AND phone3Type='Mobile' AND pupilsightPersonID=:pupilsightPersonID)" ;
                                      $sqlSMS.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone4='' AND phone4Type='Mobile' AND pupilsightPersonID=:pupilsightPersonID)" ;
                                      $resultSMS=$connection2->prepare($sqlSMS);
                                      $resultSMS->execute($dataSMS);
                                    }
                                    catch(PDOException $e) { }
                                    while ($rowSMS=$resultSMS->fetch()) {
										$countryCodeTemp = $countryCode;
	  									  if ($rowEmail["countryCode"]=="") {
                                              $countryCodeTemp = $rowEmail["countryCode"];
                                          }
	  									  $report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Role', $t, 'Attendance', $countryCodeTemp.$rowEmail["phone"]);
                                    }
                                  }
                                }
                              }
                              if ($students=="Y") {
                                try { //Get the phone numbers for each student
                                  foreach ($selectedStudents as $t) {
                                    $dataSMS=array("pupilsightPersonID"=>$t);
                                    $sqlSMS="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone1='' AND phone1Type='Mobile' AND pupilsightPersonID=:pupilsightPersonID AND status='Full')" ;
                                    $sqlSMS.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone2='' AND phone2Type='Mobile' AND pupilsightPersonID=:pupilsightPersonID AND status='Full')" ;
                                    $sqlSMS.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone3='' AND phone3Type='Mobile' AND pupilsightPersonID=:pupilsightPersonID AND status='Full')" ;
                                    $sqlSMS.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT  phone4='' AND phone4Type='Mobile' AND pupilsightPersonID=:pupilsightPersonID AND status='Full')" ;
                                    $resultSMS=$connection2->prepare($sqlSMS);
                                    $resultSMS->execute($dataSMS);
                                  }
                                }
                                catch(PDOException $e) { }
                                while ($rowSMS=$resultSMS->fetch()) {
									$countryCodeTemp = $countryCode;
	   								 if ($rowEmail["countryCode"]=="") {
                                         $countryCodeTemp = $rowEmail["countryCode"];
                                     }
	   								 $report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Attendance', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
                                }
                              }
                            } //END SMS
                          }
                        }
                      }
                    }
				  }//END Target Absent students / Attendance Status


			//Groups
			if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_groups_my") OR isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_groups_any")) {
				if ($_POST["group"]=="Y") {
					$staff=$_POST["groupsStaff"] ;
					$students=$_POST["groupsStudents"] ;
					$parents="N" ;
					if (isActionAccessible($guid, $connection2, "/modules/Messenger/messenger_post.php", "New Message_groups_parents")) {
						$parents=$_POST["groupsParents"] ;
					}
					$choices=$_POST["groups"] ;
					if ($choices!="") {
						foreach ($choices as $t) {
							try {
								$data=array("pupilsightMessengerID"=>$AI, "id"=>$t, "staff"=>$staff, "students"=>$students, "parents"=>$parents);
								$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Group', id=:id, staff=:staff, students=:students, parents=:parents" ;
								$result=$connection2->prepare($sql);
								$result->execute($data);
							}
							catch(PDOException $e) {
								$partialFail=TRUE;
							}

							//Get email addresses
							if ($email=="Y") {
								if ($staff=="Y") {
									try {
										$dataEmail=array("pupilsightGroupID"=>$t);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroupPerson ON (pupilsightGroupPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) WHERE NOT email='' AND status='Full' AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) {}
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Group', $t, 'Email', $rowEmail["email"]);
									}
								}
								if ($students=="Y") {
									try {
										$dataEmail=array("pupilsightGroupID"=>$t, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
										$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroupPerson ON (pupilsightGroupPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) WHERE NOT email='' AND status='Full' AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Group', $t, 'Email', $rowEmail["email"]);
									}
								}
								if ($parents=="Y") {
									try {
										$dataEmail=array("pupilsightGroupID"=>$t, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], "pupilsightGroupID2"=>$t);
										$sqlEmail="(SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightGroupPerson ON (pupilsightGroupPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightFamily ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE NOT email='' AND contactEmail='Y' AND pupilsightPerson.status='Full' AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID)
											UNION
											(SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightGroupPerson JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightGroupPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightFamilyChild.pupilsightPersonID) JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT email='' AND contactEmail='Y' AND pupilsightPerson.status='Full' AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID2)" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) {}
									while ($rowEmail=$resultEmail->fetch()) {
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Group', $t, 'Email', $rowEmail["email"]);
									}
								}
							}
							if ($sms=="Y" AND $countryCode!="") {
								if ($staff=="Y") {
									try {
										$dataEmail=array("pupilsightGroupID"=>$t);
										$sqlEmail="(SELECT DISTINCT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroupPerson ON (pupilsightGroupPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) WHERE NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID)" ;
										$sqlEmail.=" UNION (SELECT DISTINCT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroupPerson ON (pupilsightGroupPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) WHERE NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID)" ;
										$sqlEmail.=" UNION (SELECT DISTINCT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroupPerson ON (pupilsightGroupPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) WHERE NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID)" ;
										$sqlEmail.=" UNION (SELECT DISTINCT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStaff ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroupPerson ON (pupilsightGroupPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) WHERE NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID)" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { echo $e->getMessage(); }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Group', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
								if ($students=="Y") {
									try {
										$dataEmail=array("pupilsightGroupID"=>$t, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
										$sqlEmail="(SELECT DISTINCT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroupPerson ON (pupilsightGroupPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) WHERE NOT phone1='' AND phone1Type='Mobile' AND status='Full' AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID)" ;
										$sqlEmail.=" UNION (SELECT DISTINCT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroupPerson ON (pupilsightGroupPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) WHERE NOT phone2='' AND phone2Type='Mobile' AND status='Full' AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID)" ;
										$sqlEmail.=" UNION (SELECT DISTINCT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroupPerson ON (pupilsightGroupPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) WHERE NOT phone3='' AND phone3Type='Mobile' AND status='Full' AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID)" ;
										$sqlEmail.=" UNION (SELECT DISTINCT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroupPerson ON (pupilsightGroupPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) WHERE NOT phone4='' AND phone4Type='Mobile' AND status='Full' AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID)" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) { }
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Group', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
								if ($parents=="Y") {
									try {
										$dataEmail=array("pupilsightGroupID"=>$t, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
										$sqlEmail="(SELECT DISTINCT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightGroupPerson ON (pupilsightGroupPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightFamily ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE NOT phone1='' AND phone1Type='Mobile' AND contactSMS='Y' AND pupilsightPerson.status='Full' AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID)";
										$sqlEmail.=" UNION (SELECT DISTINCT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightGroupPerson JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightGroupPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightFamilyChild.pupilsightPersonID) JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone1='' AND phone1Type='Mobile' AND contactSMS='Y' AND pupilsightPerson.status='Full' AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID)" ;
										$sqlEmail.=" UNION (SELECT DISTINCT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightGroupPerson ON (pupilsightGroupPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightFamily ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE NOT phone2='' AND phone2Type='Mobile' AND contactSMS='Y' AND pupilsightPerson.status='Full' AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID)";
										$sqlEmail.=" UNION (SELECT DISTINCT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightGroupPerson JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightGroupPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightFamilyChild.pupilsightPersonID) JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone2='' AND phone2Type='Mobile' AND contactSMS='Y' AND pupilsightPerson.status='Full' AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID)" ;
										$sqlEmail.=" UNION (SELECT DISTINCT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightGroupPerson ON (pupilsightGroupPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightFamily ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE NOT phone3='' AND phone3Type='Mobile' AND contactSMS='Y' AND pupilsightPerson.status='Full' AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID)";
										$sqlEmail.=" UNION (SELECT DISTINCT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightGroupPerson JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightGroupPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightFamilyChild.pupilsightPersonID) JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone3='' AND phone3Type='Mobile' AND contactSMS='Y' AND pupilsightPerson.status='Full' AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID)" ;
										$sqlEmail.=" UNION (SELECT DISTINCT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson JOIN pupilsightGroupPerson ON (pupilsightGroupPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightFamily ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE NOT phone4='' AND phone4Type='Mobile' AND contactSMS='Y' AND pupilsightPerson.status='Full' AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID)";
										$sqlEmail.=" UNION (SELECT DISTINCT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightGroupPerson JOIN pupilsightGroup ON (pupilsightGroupPerson.pupilsightGroupID=pupilsightGroup.pupilsightGroupID) JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightGroupPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightFamilyChild.pupilsightPersonID) JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE NOT phone4='' AND phone4Type='Mobile' AND contactSMS='Y' AND pupilsightPerson.status='Full' AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightGroup.pupilsightGroupID=:pupilsightGroupID)" ;
										$resultEmail=$connection2->prepare($sqlEmail);
										$resultEmail->execute($dataEmail);
									}
									catch(PDOException $e) {}
									while ($rowEmail=$resultEmail->fetch()) {
										$countryCodeTemp = $countryCode;
										if ($rowEmail["countryCode"]=="") {
                                            $countryCodeTemp = $rowEmail["countryCode"];
                                        }
										$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Group', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
									}
								}
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
								$data=array("pupilsightMessengerID"=>$AI, "id"=>$t);
								$sql="INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:pupilsightMessengerID, type='Individuals', id=:id" ;
								$result=$connection2->prepare($sql);
								$result->execute($data);
							}
							catch(PDOException $e) {
								$partialFail=TRUE;
							}

							if ($email=="Y") {
								try {
									$dataEmail=array("pupilsightPersonID"=>$t);
									$sqlEmail="SELECT DISTINCT email, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT email='' AND pupilsightPersonID=:pupilsightPersonID AND status='Full'" ;
									$resultEmail=$connection2->prepare($sqlEmail);
									$resultEmail->execute($dataEmail);
								}
								catch(PDOException $e) { }
								while ($rowEmail=$resultEmail->fetch()) {
									$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Individuals', $t, 'Email', $rowEmail["email"]);
								}
							}
							if ($sms=="Y" AND $countryCode!="") {
								try {
									$dataEmail=array("pupilsightPersonID"=>$t);
									$sqlEmail="(SELECT phone1 AS phone, phone1CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone1='' AND phone1Type='Mobile' AND pupilsightPersonID=:pupilsightPersonID AND status='Full')" ;
									$sqlEmail.=" UNION (SELECT phone2 AS phone, phone2CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone2='' AND phone2Type='Mobile' AND pupilsightPersonID=:pupilsightPersonID AND status='Full')" ;
									$sqlEmail.=" UNION (SELECT phone3 AS phone, phone3CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone3='' AND phone3Type='Mobile' AND pupilsightPersonID=:pupilsightPersonID AND status='Full')" ;
									$sqlEmail.=" UNION (SELECT phone4 AS phone, phone4CountryCode AS countryCode, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE NOT phone4='' AND phone4Type='Mobile' AND pupilsightPersonID=:pupilsightPersonID AND status='Full')" ;
									$resultEmail=$connection2->prepare($sqlEmail);
									$resultEmail->execute($dataEmail);
								}
								catch(PDOException $e) { }
								while ($rowEmail=$resultEmail->fetch()) {
									$countryCodeTemp = $countryCode;
									if ($rowEmail["countryCode"]=="") {
                                        $countryCodeTemp = $rowEmail["countryCode"];
                                    }
									$report = reportAdd($report, $emailReceipt, $rowEmail['pupilsightPersonID'], 'Individuals', $t, 'SMS', $countryCodeTemp.$rowEmail["phone"]);
								}
							}
						}
					}
				}
			}

			if ($email=="Y") {
				//Set up email
				$emailCount=0 ;
				$mail= $container->get(Mailer::class);
				$mail->SMTPKeepAlive = true;
				if ($emailReplyTo!="")
					$mail->AddReplyTo($emailReplyTo, '');
				if ($from!=$_SESSION[$guid]["email"])	//If sender is using school-wide address, send from school
					$mail->SetFrom($from, $_SESSION[$guid]["organisationName"]);
				else //Else, send from individual
					$mail->SetFrom($from, $_SESSION[$guid]["preferredName"] . " " . $_SESSION[$guid]["surname"]);
				$mail->CharSet="UTF-8";
				$mail->Encoding="base64" ;
				$mail->IsHTML(true);
				$mail->Subject=$subject ;
				$mail->renderBody('mail/email.twig.html', [
					'title'  => $subject,
					'body'   => $body
				]);

				//Send to sender, if not in recipient list
				$includeSender = true ;
				foreach ($report as $reportEntry) {
					if ($reportEntry[3] == 'Email') {
						if ($reportEntry[4] == $from) {
							$includeSender = false ;
						}
					}
				}
				if ($includeSender) {
					$emailCount ++ ;
					$mail->AddAddress($from);
					if(!$mail->Send()) {
						$partialFail = TRUE ;
					}
				}

				//If sender is using school-wide address, and it is not in recipient list, send to school-wide address
				if ($from!=$_SESSION[$guid]["email"]) { //If sender is using school-wide address, add them to recipient list.
					$includeSender = true ;
					foreach ($report as $reportEntry) {
						if ($reportEntry[3] == 'Email') {
							if ($reportEntry[4] == $_SESSION[$guid]["email"]) {
								$includeSender = false ;
							}
						}
					}
					if ($includeSender) {
						$emailCount ++;
						$mail->ClearAddresses();
						$mail->AddAddress($_SESSION[$guid]["email"]);
						if(!$mail->Send()) {
							$partialFail = TRUE ;
						}
					}
				}

				//Send to each recipient
				foreach ($report as $reportEntry) {
					if ($reportEntry[3] == 'Email') {
						$emailCount ++;
						$mail->ClearAddresses();
						$mail->AddAddress($reportEntry[4]);
						//Deal with email receipt and body finalisation
						if ($emailReceipt == 'Y') {
							$bodyReadReceipt = "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Messenger/messenger_emailReceiptConfirm.php&pupilsightMessengerID=$AI&pupilsightPersonID=".$reportEntry[0]."&key=".$reportEntry[5]."'>".$emailReceiptText."</a>";
							if (is_numeric(strpos($body, '[confirmLink]'))) {
								$bodyOut = str_replace('[confirmLink]', $bodyReadReceipt, $body);
							}
							else {
								$bodyOut = $body.$bodyReadReceipt;
							}
						}
						else {
							$bodyOut = $body;
						}
						$mail->renderBody('mail/email.twig.html', [
							'title'  => $subject,
							'body'   => $bodyOut
						]);
						if(!$mail->Send()) {
							$partialFail = TRUE ;
							setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], getModuleID($connection2, $_POST["address"]), $_SESSION[$guid]['pupilsightPersonID'], 'Email Send Status', array('Status' => 'Not OK', 'Result' => $mail->ErrorInfo, 'Recipients' => $reportEntry[4]));
						}
					}
                }

                // Optionally send bcc copies of this message, excluding recipients already sent to.
                $recipientList = array_column($report, 4);
                $messageBccList = explode(',', getSettingByScope($connection2, 'Messenger', 'messageBcc'));
                $messageBccList = array_filter($messageBccList, function($recipient) use ($recipientList, $from) {
                    return $recipient != $from && !in_array($recipient, $recipientList);
                });

                if (!empty($messageBccList) && !empty($report)) {
                    $mail->ClearAddresses();
                    foreach ($messageBccList as $recipient) {
                        $mail->AddBCC($recipient, '');
                    }

                    $sender = formatName('', $_SESSION[$guid]['preferredName'], $_SESSION[$guid]['surname'], 'Staff');
                    $date = dateConvertBack($guid, date('Y-m-d')).' '.date('H:i:s');

                    $mail->renderBody('mail/email.twig.html', [
						'title'  => $subject,
						'body'   => __('Message Bcc').': '.sprintf(__('The following message was sent by %1$s on %2$s and delivered to %3$s recipients.'), $sender, $date, $emailCount).'<br/><br/>'.$body
					]);
					$mail->Send();
                }

                $mail->smtpClose();
			}

			if ($sms=="Y") {
				if ($countryCode=="") {
					$partialFail = true;
				} else {
                    $recipients = array_filter(array_reduce($report, function ($phoneNumbers, $reportEntry) {
                        if ($reportEntry[3] == 'SMS')  $phoneNumbers[] = '+'.$reportEntry[4];

                            return $phoneNumbers;

                    }, []));

                    $sms = $container->get(SMS::class);

                    $result = $sms
                        ->content($body)
                        ->send($recipients);

                    $smsCount = count($recipients);
                    $smsBatchCount = count($result);

                    $smsStatus = $result ? 'OK' : 'Not OK';
                    $partialFail &= !empty($result);

					//Set log
					setLog($connection2, $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'], getModuleID($connection2, $_POST["address"]), $_SESSION[$guid]['pupilsightPersonID'], 'SMS Send Status', array('Status' => $smsStatus, 'Result' => count($result), 'Recipients' => $recipients));
				}
			}

			//Write report entries
			foreach ($report as $reportEntry) {
				try {
					$confirmed = null ;
					if ($reportEntry[5] != '')
						$confirmed = 'N';
					$data=array("pupilsightMessengerID"=>$AI, "pupilsightPersonID"=>$reportEntry[0], "targetType"=>$reportEntry[1], "targetID"=>$reportEntry[2], "contactType"=>$reportEntry[3], "contactDetail"=>$reportEntry[4], "key"=>$reportEntry[5], "confirmed" => $confirmed);
					$sql="INSERT INTO pupilsightMessengerReceipt SET pupilsightMessengerID=:pupilsightMessengerID, pupilsightPersonID=:pupilsightPersonID, targetType=:targetType, targetID=:targetID, contactType=:contactType, contactDetail=:contactDetail, `key`=:key, confirmed=:confirmed" ;
					$result=$connection2->prepare($sql);
					$result->execute($data);
				}
				catch(PDOException $e) {
					$partialFail = true;
				}
			}

			if ($partialFail == TRUE) {
				//Fail 4
				$URL.="&addReturn=fail4" ;
				header("Location: {$URL}");
			}
			else {
				$_SESSION[$guid]['pageLoads'] = null;
				$URL.="&addReturn=success0&emailCount=" . $emailCount . "&smsCount=" . $smsCount . "&smsBatchCount=" . $smsBatchCount ;
				header("Location: {$URL}") ;
			}
		}
	}
}
?>

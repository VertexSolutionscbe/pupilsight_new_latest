<?php
/*
Pupilsight, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

include "../../functions.php" ;
include "../../config.php" ;

include "./moduleFunctions.php" ;

//New PDO DB connection
$pdo = new Pupilsight\sqlConnection();
$connection2 = $pdo->getConnection();

@session_start() ;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Help Desk/" ;

if (isActionAccessible($guid, $connection2, "/modules/Help Desk/issues_create.php") == FALSE) {
	$URL.= "issues_view.php&return=error0";
	header("Location: {$URL}");
} else {

	$personID = $_SESSION[$guid]["pupilsightPersonID"];
	//Proceed!
	if (isset($_POST["name"])) {
	  	$name = $_POST["name"] ;
	}

	$category = "";
	if (isset($_POST["category"])) {
	  	$category = $_POST["category"] ;
	}

	$description = "";
	if (isset($_POST["description"])) {
	  	$description = $_POST["description"] ;
	}

	$priority = "";
	if (isset($_POST["priority"])) {
	  	$priority = $_POST["priority"] ;
	}
	
	$createdByID = $_SESSION[$guid]["pupilsightPersonID"];
	$personID = $_SESSION[$guid]["pupilsightPersonID"];
	if (isset($_POST["createFor"]) && getPermissionValue($connection2, $_SESSION[$guid]["pupilsightPersonID"], "createIssueForOther")) {
	  	if($_POST["createFor"] != 0) {
		  	$personID = $_POST["createFor"];
		  	$createdByID = $_SESSION[$guid]["pupilsightPersonID"];
	  	}
	}
	
	if (isset($_POST["privacySetting"])) {
		$privacySetting = $_POST["privacySetting"];
	} else {
		try {
			$data = array(); 
			$sql = "SELECT * FROM pupilsightSetting WHERE scope='Help Desk' AND name='resolvedIssuePrivacy'" ;
			$result = $connection2->prepare($sql);
			$result->execute($data);
		} catch (PDOException $e) {
		}

		$row = $result->fetch() ;
		$privacySetting = $row['value'];
	}

	if ($name == "" || $description == "") {
		//Fail 3
		$URL = $URL  . "issues_create.php&return=error1" ;
		header("Location: {$URL}");
		exit();
	} else {
		$date = date("Y-m-d") ;
		//Write to database
		try {
			$pupilsightModuleID = getModuleIDFromName($connection2, "Help Desk");
			if($pupilsightModuleID == null) {
				throw new PDOException("Invalid pupilsightModuleID.");
			}

			$data = array("technicianID" => null, "pupilsightPersonID" => $personID, "name" => $name, "description" => $description, "status" => "Unassigned", "category" => $category, "priority" => $priority, "pupilsightSchoolYearID" => $_SESSION[$guid]["pupilsightSchoolYearID"], "createdByID" => $createdByID, "privacySetting" => $privacySetting, "datee" => $date);
			$sql = "INSERT INTO helpDeskIssue SET technicianID=:technicianID, pupilsightPersonID=:pupilsightPersonID, issueName=:name, description=:description, status=:status, category=:category, priority=:priority, pupilsightSchoolYearID=:pupilsightSchoolYearID, createdByID=:createdByID, privacySetting=:privacySetting, `date`=:datee" ;
      		$result = $connection2->prepare($sql);
			$result->execute($data);
		} catch (PDOException $e) {
			$URL=$URL . "issues_create.php&return=error2";
			header("Location: {$URL}");
			exit();
		}

		$issueID = $connection2->lastInsertId();
		if (isset($_POST["createFor"])) {
			if ($_POST["createFor"] != 0) {
				setNotification($connection2, $guid, $personID, "A new issue has been created on your behalf (". $name . ").", "Help Desk", "/index.php?q=/modules/Help Desk/issues_discussView.php&issueID=" . $issueID); 
			}
		}
		notifyTechnican($connection2, $guid, $issueID, $name, $personID);
		
		$array = array("issueID" =>$issueID);
		$title = "Issue Created";
		if (isset($_POST["createFor"])) { 
			if ($_POST["createFor"] != 0) {
				$array['technicianID'] = getTechnicianID($connection2, $createdByID);
				$title = "Issue Created (for Another Person)";
			}
		}
		
		setLog($connection2, $_SESSION[$guid]["pupilsightSchoolYearID"], $pupilsightModuleID, $_SESSION[$guid]["pupilsightPersonID"], $title, $array, null);
	
		//Success 0 aka Created
		$URL = $URL . "issues_create.php&issueID=" . $issueID . "&return=success0" ;
		header("Location: {$URL}");
	}
}
?>

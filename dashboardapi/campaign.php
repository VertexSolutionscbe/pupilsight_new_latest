<?php
include '../pupilsight.php';
define("IMAGE_PATH","");
define("BASE_URL","");
if(isset($_SESSION[$guid]['pupilsightSchoolYearID'])){
if(isset($_REQUEST['type'])){
$type = trim($_REQUEST['type']);
  $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
if(!empty($type)){
 switch ($type) {
 	case "campaign_list":
			/* 1 - draft | 2 - published | 3 - stoped */
			$status='';
			if(isset($_REQUEST['status'])){
		      $status=trim($_REQUEST['status']);
			}
			$sql = 'Select * FROM campaign WHERE id != ""';
			if(isset($_REQUEST['pupilsightSchoolYearID'])){
		        $sql.=" AND academic_id='".$_REQUEST['pupilsightSchoolYearID']."'";
			} else {
				$sql.=" AND academic_id ='".$pupilsightSchoolYearID."'";
			}
			if(!empty($status)){
		       $sql.=" AND status='".$status."'";
			} else {
				$sql.=" AND status ='2'";
			}
			$result = $connection2->query($sql);
			$campaign = $result->fetchAll();
			echo json_encode($campaign);
 	  break;
    case "campaign_count":
		$sql = "SELECT 
		COUNT(id) as total, 
		SUM(status='1') draft, SUM(status='2') published,SUM(status='3') stoped
		FROM campaign WHERE id !=''";
		if(isset($_REQUEST['pupilsightSchoolYearID'])){
		$sql.=" AND academic_id='".$_REQUEST['pupilsightSchoolYearID']."'";
		} else {
		$sql.=" AND academic_id ='".$pupilsightSchoolYearID."'";
		}
		$result = $connection2->query($sql);
		$campaign = $result->fetch();
		echo json_encode($campaign);
    break;
    case "campaign_seat_availabity":
		$status='';
		if(isset($_REQUEST['status'])){
		$status=trim($_REQUEST['status']);
		}
		$sql = 'SELECT SUM(seats) as seats_total FROM campaign WHERE id!=""';
		if(isset($_REQUEST['pupilsightSchoolYearID'])){
		$sql.=" AND academic_id='".$_REQUEST['pupilsightSchoolYearID']."'";
		} else {
		$sql.=" AND academic_id ='".$pupilsightSchoolYearID."'";
		}
		if(!empty($status)){
		$sql.=" AND status='".$status."'";
		} else {
		$sql.=" AND status ='2'";
		}
		$result = $connection2->query($sql);
		$campaign = $result->fetch();
		echo json_encode($campaign);
    break;
 	default:
 		echo "Invalid request type";
 		break;
 }
} else { echo "Request type parameter is not empty"; }
} else { echo " Request type parameter is missing"; }
} else { echo "User not login ";}
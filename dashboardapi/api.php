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
    case "workflow_states":
		$sql = 'SELECT name,COUNT(id) as total FROM `workflow_state` GROUP BY name';
		$result = $connection2->query($sql);
		$res = $result->fetchAll();
		echo json_encode($res);
    break;
    case "workflow_state1":
		$sql = 'SELECT COUNT(*) as submitted_total  FROM workflow_state WHERE `name` LIKE "Submitted"';
		$result = $connection2->query($sql);
		$res = $result->fetch();
		echo json_encode($res);
    break;
	 case "workflow_state2":
		$sql = 'SELECT COUNT(*) as shortlisted_total  FROM `workflow_state` WHERE `name` LIKE "Shortlisted"';
		$result = $connection2->query($sql);
		$res = $result->fetch();
		echo json_encode($res);
	break;
	case "workflow_state3":
		$sql = 'SELECT COUNT(*) as verified_total  FROM `workflow_state` WHERE `name` LIKE "verified"';
		$result = $connection2->query($sql);
		$res = $result->fetch();
		echo json_encode($res);
	break;
	case "workflow_state4":
		$sql = 'SELECT COUNT(*) as interview_total  FROM `workflow_state` WHERE `name` LIKE "Interview"';
		$result = $connection2->query($sql);
		$res = $result->fetch();
		echo json_encode($res);
	break;
	case "workflow_state5":
		$sql = 'SELECT COUNT(*) as generated_total  FROM `workflow_state` WHERE `name` LIKE "Generated"';
		$result = $connection2->query($sql);
		$res = $result->fetch();
		echo json_encode($res);
	break;
	case "workflow_state6":
		$sql = 'SELECT COUNT(*) as fee_paid_total  FROM `workflow_state` WHERE `name` LIKE "Fee Paid"';
		$result = $connection2->query($sql);
		$res = $result->fetch();
		echo json_encode($res);
	break;
	case "workflow_state7":
		$sql = 'SELECT COUNT(*) as accepted_total  FROM `workflow_state` WHERE `name` LIKE "Accepted"';
		$result = $connection2->query($sql);
		$res = $result->fetch();
		echo json_encode($res);
	break;
	case "fee_collected":
		$sql = 'SELECT COUNT(*) as total  FROM `fn_fees_collection`';
		$result = $connection2->query($sql);
		$res = $result->fetch();
		echo json_encode($res);
	break;
	case "fee_due":
		$sql = 'SELECT COUNT(i.id) as total
		FROM fn_fee_invoice as  i
		WHERE NOT EXISTS (SELECT c.fn_fees_invoice_id FROM fn_fees_collection as  c WHERE i.id = c.fn_fees_invoice_id';
		if(isset($_REQUEST['pupilsightSchoolYearID'])){
		$sql.=" AND c.pupilsightSchoolYearID='".$_REQUEST['pupilsightSchoolYearID']."'";
		} else {
		$sql.=" AND c.pupilsightSchoolYearID ='".$pupilsightSchoolYearID."'";
		}
		$sql.= " )";
		$result = $connection2->query($sql);
		$res = $result->fetch();
		echo json_encode($res);
	break;
	case "total_fee_collected":
		$sql = 'SELECT COUNT(i.id) as total
		FROM fn_fee_invoice as  i
		WHERE  EXISTS (SELECT c.fn_fees_invoice_id FROM fn_fees_collection as  c WHERE i.id = c.fn_fees_invoice_id';
		if(isset($_REQUEST['pupilsightSchoolYearID'])){
		$sql.=" AND c.pupilsightSchoolYearID='".$_REQUEST['pupilsightSchoolYearID']."'";
		} else {
		$sql.=" AND c.pupilsightSchoolYearID ='".$pupilsightSchoolYearID."'";
		}
		$sql.= ")";
		$result = $connection2->query($sql);
		$res = $result->fetch();
		echo json_encode($res);
	break;
	case "item_wise_destribution_fee":
		$sql = 'SELECT count(c.id) as total FROM  fn_fees_collection as c 
		LEFT JOIN pupilsightStudentEnrolment as enrol ON c.pupilsightPersonID=enrol.pupilsightPersonID
		LEFT JOIN pupilsightYearGroup as cls ON enrol.pupilsightYearGroupID=cls.pupilsightYearGroupID';
		$sql.=' WHERE c.id!="" ';
		if(isset($_REQUEST['pupilsightSchoolYearID'])){
		$sql.=" AND c.pupilsightSchoolYearID='".$_REQUEST['pupilsightSchoolYearID']."'";
		} else {
		$sql.=" AND c.pupilsightSchoolYearID ='".$pupilsightSchoolYearID."'";
		}
		$result = $connection2->query($sql);
		$res = $result->fetch();
		echo json_encode($res);
	break;
	case "paymode_wise_destribution":
		$sql = 'SELECT cls.name as class, COUNT(c.id) as total  FROM  fn_fees_collection as c 
		LEFT JOIN pupilsightstudentenrolment as enrol ON c.pupilsightPersonID=enrol.pupilsightPersonID
		LEFT JOIN pupilsightyeargroup as cls ON enrol.pupilsightYearGroupID=cls.pupilsightYearGroupID
		LEFT JOIN fn_masters as m ON c.payment_mode_id=m.id
		WHERE m.type="payment_mode" ';
		if(isset($_REQUEST['pupilsightSchoolYearID'])){
		$sql.=" AND c.pupilsightSchoolYearID='".$_REQUEST['pupilsightSchoolYearID']."'";
		} else {
		$sql.=" AND c.pupilsightSchoolYearID ='".$pupilsightSchoolYearID."'";
		}
		$sql.=" GROUP BY cls.name";
		$result = $connection2->query($sql);
		$res = $result->fetchAll();
		echo json_encode($res);
	break;
 	default:
 		echo "Invalid request type";
 		break;
 }
} else { echo "Request type parameter is not empty"; }
} else { echo " Request type parameter is missing"; }
} else { echo "User not login ";}
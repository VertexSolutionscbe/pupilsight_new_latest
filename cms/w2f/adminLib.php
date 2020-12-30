<?php

include_once 'database.php';
$connection = new database();
$connection->init();

class adminlib
{

	function activecampaign_cnt()
	{
		$sql = "SELECT count(id) as active_camp_cnt FROM campaign WHERE status = 1";
		$result = database::doSelectOne($sql);
		return $result;
	}
	function overallcampaign_cnt()
	{
		$sql = "SELECT count(id) as overall_camp_cnt FROM campaign ";
		$result = database::doSelectOne($sql);
		return $result;
	}
	function getPupilSightData()
	{
		$sql = "SELECT * FROM pupilsight_cms WHERE id = 1";
		$result = database::doSelectOne($sql);
		return $result;
	}
	function getApp_statusData()
	{
		$sql = "SELECT * FROM app_status WHERE status = '1' ";
		$result = database::doSelect($sql);
		return $result;
	}

	//public campaign
	function getcampaign()
	{
		$sql = "SELECT a.* FROM campaign AS a JOIN workflow_map AS b ON a.id = b.campaign_id WHERE  a.status = '2' AND CURDATE() between start_date and end_date order by id DESC";
		$result = database::doSelect($sql);
		return $result;
	}

	function getcampaign_byid($id)
	{
		$sql = "SELECT a.*,b.name as progname FROM campaign AS a LEFT JOIN pupilsightProgram AS b ON a.pupilsightProgramID = b.pupilsightProgramID WHERE  a.id = '" . $id . "'";
		$result = database::doSelectOne($sql);
		return $result;
	}

	function createPupilSightData($input)
	{
		$sql = "UPDATE pupilsight_cms SET ";
		foreach ($input as $key => $value) {
			$val = htmlspecialchars($value);
			$sql .= $key . '= "' . $val . '", ';
		}
		$sql = rtrim($sql, ", ");
		$sql .= " WHERE id = 1";

		$result = database::doUpdate($sql);
		//return $result;
	}

	function getPupilSightSectionData()
	{
		$sql = "SELECT * FROM pupilsight_cms_sections WHERE status = '1' order by id DESC";
		$result = database::doSelect($sql);
		return $result;
	}

	function getPupilSightSectionDataByCategory($id)
	{
		$sql = "SELECT * FROM pupilsight_cms_sections WHERE type = '" . $id . "' AND status = '1' order by id DESC";
		$result = database::doSelect($sql);
		return $result;
	}

	function getPupilSightSectionFrontendData()
	{
		$sql = "SELECT type FROM pupilsight_cms_sections WHERE status = '1' GROUP BY type";
		$result = database::doSelect($sql);
		$arr = [];
		foreach ($result as $k => $r) {
			$sql1 = "SELECT * FROM pupilsight_cms_sections WHERE type = '" . $r['type'] . "' AND status = '1' ";
			$result1 = database::doSelect($sql1);
			$arr[$r['type']] = $result1;
		}

		// echo '<pre>';
		// print_r($arr);
		// echo '</pre>';
		return $arr;
	}

	function createSectionData($input)
	{
		$sql = "INSERT INTO pupilsight_cms_sections SET ";
		foreach ($input as $key => $value) {
			$val = htmlspecialchars($value);
			$sql .= $key . '= "' . $val . '", ';
		}
		$sql = rtrim($sql, ", ");

		$result = database::doInsert($sql);
		//return $result;
	}

	function deletePupilSightSectionData($id)
	{
		$sql = "DELETE FROM pupilsight_cms_sections  WHERE id ='" . $id . "' ";
		$result = database::doDelete($sql);
		return $result;
	}

	function getPupilSightEctionDataById($id)
	{
		$sql = "SELECT * FROM pupilsight_cms_sections WHERE status = '1' AND id = '" . $id . "' ";
		$result = database::doSelectOne($sql);
		return $result;
	}

	function updateSectionData($input, $id)
	{
		$sql = "UPDATE pupilsight_cms_sections SET ";
		foreach ($input as $key => $value) {
			$val = htmlspecialchars($value);
			$sql .= $key . '= "' . $val . '", ';
		}
		$sql = rtrim($sql, ", ");
		$sql .= " WHERE id ='$id'";

		$result = database::doUpdate($sql);
	}

	function getadmin($user)
	{
		$sql = "SELECT *FROM admin where userid='" . $user . "'";
		$result = database::doSelectone($sql);
		return $result;
	}

	function sendMessageData($input)
	{
		$sql = "INSERT INTO pupilsight_cms_message SET ";
		foreach ($input as $key => $value) {
			$val = htmlspecialchars($value);
			$sql .= $key . '= "' . $val . '", ';
		}
		$sql = rtrim($sql, ", ");

		$result = database::doInsert($sql);
		return 'done';
	}

	function getUserMessageData()
	{
		$sql = "SELECT * FROM pupilsight_cms_message WHERE status = '1' ";
		$result = database::doSelect($sql);
		return $result;
	}

	function deletePupilSightMessageData($id)
	{
		$sql = "DELETE FROM pupilsight_cms_message  WHERE id ='" . $id . "' ";
		$result = database::doDelete($sql);
		return $result;
	}

	function deletePupilSightSectionImageData($id, $col)
	{
		$path = $col . '_path';
		$sql = "UPDATE pupilsight_cms_sections SET " . $col . " = '' , " . $path . " = '' WHERE id ='" . $id . "' ";
		$result = database::doUpdate($sql);
		return $result;
	}

	function changeSectionStatus($name, $val)
	{
		$sql = "UPDATE pupilsight_cms SET " . $name . " = '" . $val . "' WHERE id ='1' ";
		$result = database::doUpdate($sql);
		return $result;
	}

	/*
	public function getApp_status(QueryCriteria $criteria)
	{
		$query = $this
			->newQuery()
			->from('app_status')
			->cols([
				'id', 'name', 'application_num', 'status'
			]);

		return $this->runQuery($query, $criteria);
	}*/

	public function getApplist($val)
	{
		// 
		// $sql = "SELECT cs.id, cs.campaign_id, cm.form_id,cs.submission_id,cs.state,cs.state_id,cs.status,cm.name, ws.created_at FROM wp_fluentform_entry_details as we LEFT JOIN campaign AS cm ON we.form_id=cm.form_id LEFT JOIN campaign_form_status AS cs ON cm.id=cs.campaign_id LEFT JOIN wp_fluentform_submissions AS ws ON we.submission_id=ws.id WHERE we.field_value = '".$val."' GROUP BY we.form_id";		
		$sql = "SELECT  cm.id, cm.form_id,cm.name, cm.is_fee_generate, we.submission_id, ws.created_at, ws.application_id FROM wp_fluentform_entry_details as we LEFT JOIN campaign AS cm ON we.form_id=cm.form_id LEFT JOIN wp_fluentform_submissions AS ws ON we.submission_id=ws.id WHERE we.field_value LIKE '%" . $val . "%' AND (we.field_name = 'email' OR we.field_name = 'father_email' OR we.field_name = 'mother_email' OR we.field_name = 'father_mobile' OR we.field_name = 'mother_mobile') GROUP BY we.submission_id";
		$result = database::doSelect($sql);

		foreach ($result as $k => $rs) {
			$subId = $rs['submission_id'];
			$sql1 = "SELECT GROUP_CONCAT(field_value SEPARATOR ' ') as names FROM wp_fluentform_entry_details WHERE submission_id = " . $subId . "  AND field_name = 'student_name' ";
			$result1 = database::doSelectOne($sql1);
			$result[$k]['username'] = $result1['names'];

			$sql2 = "SELECT field_value FROM wp_fluentform_entry_details WHERE submission_id = " . $subId . "  AND field_name = 'father_email' ";
			$result2 = database::doSelectOne($sql2);
			if(!empty($result2['field_value'])){
				$result[$k]['email'] = $result2['field_value'];
			} else {
				$result[$k]['email'] = '';
			}
			$sql3 = "SELECT field_value FROM wp_fluentform_entry_details WHERE submission_id = " . $subId . "  AND field_name = 'father_mobile' ";
			$result3 = database::doSelectOne($sql3);
			if(!empty($result3['field_value'])){
				$result[$k]['phone'] = $result3['field_value'];
			} else {
				$result[$k]['phone'] = '';
			}

			$sql2 = "SELECT transaction_id FROM fn_fees_applicant_collection WHERE submission_id = " . $subId . "  ";
			$result2 = database::doSelectOne($sql2);
			if (!empty($result2['transaction_id'])) {
				$result[$k]['transaction_id'] = $result2['transaction_id'] . '.pdf';
			} else {
				$result[$k]['transaction_id'] = '';
			}

			if (!empty($rs['application_id'])) {
				$result[$k]['application_no'] = $rs['application_id'] . '.pdf';
			} else {
				$result[$k]['application_no'] = $subId . '.pdf';;
			}
		}
		// echo '<pre>';
		// print_r($result1);
		// echo '</pre>';
		// die();
		return $result;
	}

	public function getstatedata($cid, $fid, $sid)
	{
		if (!empty($cid) && !empty($fid) && !empty($sid)) {
			$sql = "SELECT state FROM campaign_form_status WHERE campaign_id = " . $cid . " AND form_id = " . $fid . " AND submission_id = " . $sid . " ORDER BY id DESC LIMIT 1";
			$result = database::doSelectOne($sql);

			$sql2 = "SELECT transaction_id FROM fn_fees_applicant_collection WHERE submission_id = " . $sid . "  ";
			$result2 = database::doSelectOne($sql2);

			if (!empty($result2['transaction_id'])) {
				$state = 'Submitted';
			} else {
				$state = 'Created';
			}

			// if (!empty($result)) {
			// 	$state = $result['state'];
			// } else {
			// 	// $sql = "Select workflow_id  FROM workflow_map WHERE campaign_id = ".$cid." ";
			// 	// $result = database::doSelectOne($sql);

			// 	// $sql1 = "Select name FROM workflow_state WHERE workflowid = ".$result['workflow_id']." AND order_wise = '1' ";
			// 	// $result1 = database::doSelectOne($sql1);
			// 	// $state = $result1['name'];
			// 	$state = 'Submitted';
			// }
			return $state;
		} else {
			$state = 'Submitted';
			return $state;
		}
	}

	public function isCampaignActive($cid)
	{
		$sql = "SELECT id FROM campaign WHERE id = " . $cid . " and status<3 and DATE(end_date) >= DATE(CURRENT_TIMESTAMP)  ";
		//$sql = "SELECT id FROM campaign WHERE id = " . $cid . " and status<3  ";
		$result = database::doSelectOne($sql);
		if (empty($result)) {
			return FALSE;
		}
		return TRUE;
		//print_r($result);
	}

	public function chkCampaignStatus($cid)
	{
		$sql = "SELECT limit_apply_form, form_id FROM campaign WHERE id = " . $cid . " ";
		$result = database::doSelectOne($sql);
		$chklimit = $result['limit_apply_form'];
		$formId = $result['form_id'];
		if(!empty($formId)){

			$sql2 = "SELECT count(DISTINCT submission_id) as ids FROM `wp_fluentform_entry_details` WHERE form_id = " . $formId . " ";
			$result2 = database::doSelectOne($sql2);
			$kount = $result2['ids'];

			if ($chklimit != '0') {
				if (!empty($kount)) {
					if ($chklimit > $kount) {
						$return = '1';
					} else {
						$return = '2';
					}
				} else {
					$return = '1';
				}
			} else {
				$return = '1';
			}
			if ($return == '2') {
				$sql3 = "UPDATE campaign SET status = '3' WHERE id = " . $cid . " ";
				$result3 = database::doUpdate($sql3);
			}
		} else {
			$return = '2';
		}

		return $return;
	}

	function createCampaignRegistration($input, $campId)
	{

		$sql = "INSERT INTO pupilsightPerson SET ";
		foreach ($input as $key => $value) {
			$val = htmlspecialchars($value);
			$sql .= $key . '= "' . $val . '", ';
		}
		$sql = rtrim($sql, ", ");

		$result = database::doInsert($sql);
		$lastId = $result;

		$cinput['name'] = $input['firstName'];
		$cinput['email'] = $input['email'];
		$cinput['pupilsightPersonID'] = $lastId;
		$cinput['mobile'] = $input['phone1'];
		$cinput['campaign_id'] = $campId;
		$csql = "INSERT INTO campaign_parent_registration SET ";
		foreach ($cinput as $ckey => $cvalue) {
			$cval = htmlspecialchars($cvalue);
			$csql .= $ckey . '= "' . $cval . '", ';
		}
		$csql = rtrim($csql, ", ");

		$cresult = database::doInsert($csql);
	}

	function updateApplicantData2($submissionId, $pupilsightProgramID, $pupilsightYearGroupID, $application_id)
	{
		$sql1 = "UPDATE wp_fluentform_submissions SET pupilsightProgramID=" . $pupilsightProgramID . ", pupilsightYearGroupID=" . $pupilsightYearGroupID . ", application_id = '" . $application_id . "'  WHERE id= " . $submissionId . " ";
		$result1 = database::doUpdate($sql1);
	}

	function updateApplicantData($submissionId, $pupilsightProgramID, $pupilsightYearGroupID)
	{
		$sql1 = "UPDATE wp_fluentform_submissions SET pupilsightProgramID=" . $pupilsightProgramID . ", pupilsightYearGroupID=" . $pupilsightYearGroupID . "  WHERE id= " . $submissionId . " ";
		$result1 = database::doUpdate($sql1);
	}

	public function getstatedata_status($cid, $fid, $sid)
	{
		if (!empty($cid) && !empty($fid) && !empty($sid)) {
			$sql = "SELECT state FROM campaign_form_status WHERE campaign_id = " . $cid . " AND form_id = " . $fid . " AND submission_id = " . $sid . " ORDER BY id DESC LIMIT 1";
			$result = database::doSelectOne($sql);

			if (!empty($result)) {
				$state = $result['state'];
			} else {
				$sql = "Select workflow_id  FROM workflow_map WHERE campaign_id = " . $cid . " ";
				$result = database::doSelect($sql);

				$sql1 = "Select name FROM workflow_state WHERE workflowid = " . $result['workflow_id'] . " AND order_wise = '1' ";
				$result1 = database::doSelectOne($sql1);
				$state = $result1['name'];
			}
			return $state;
		} else {
			$state = 'No Status';
			return $state;
		}
	}

	function getCampaignClass($classes)
	{
		$sql = "SELECT pupilsightYearGroupID, name FROM pupilsightYearGroup WHERE pupilsightYearGroupID IN (" . $classes . ") ";
		$result = database::doSelect($sql);
		return $result;
	}

	public function getStatus($sid)
	{
		// 
		// $sql = "SELECT cs.id, cs.campaign_id, cm.form_id,cs.submission_id,cs.state,cs.state_id,cs.status,cm.name, ws.created_at FROM wp_fluentform_entry_details as we LEFT JOIN campaign AS cm ON we.form_id=cm.form_id LEFT JOIN campaign_form_status AS cs ON cm.id=cs.campaign_id LEFT JOIN wp_fluentform_submissions AS ws ON we.submission_id=ws.id WHERE we.field_value = '".$val."' GROUP BY we.form_id";		
		$sql = "SELECT  cm.id, cm.form_id,cm.name, we.submission_id, ws.created_at, ws.application_id FROM wp_fluentform_entry_details as we LEFT JOIN campaign AS cm ON we.form_id=cm.form_id LEFT JOIN wp_fluentform_submissions AS ws ON we.submission_id=ws.id WHERE we.submission_id = " . $sid . " GROUP BY we.submission_id";
		$result = database::doSelect($sql);

		foreach ($result as $k => $rs) {
			$subId = $rs['submission_id'];
			$sql1 = "SELECT GROUP_CONCAT(field_value SEPARATOR ' ') as names FROM wp_fluentform_entry_details WHERE submission_id = " . $subId . "  AND field_name = 'student_name' ";
			$result1 = database::doSelectOne($sql1);
			$result[$k]['username'] = $result1['names'];

			$sql2 = "SELECT transaction_id FROM fn_fees_applicant_collection WHERE submission_id = " . $subId . "  ";
			$result2 = database::doSelectOne($sql2);
			if (!empty($result2['transaction_id'])) {
				$result[$k]['transaction_id'] = $result2['transaction_id'] . '.pdf';
			} else {
				$result[$k]['transaction_id'] = '';
			}

			if (!empty($rs['application_id'])) {
				$result[$k]['application_no'] = $rs['application_id'] . '.pdf';
			} else {
				$result[$k]['application_no'] = $subId . '.pdf';;
			}
		}
		// echo '<pre>';
		// print_r($result1);
		// echo '</pre>';
		// die();
		return $result;
	}
}

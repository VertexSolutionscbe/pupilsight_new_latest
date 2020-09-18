<?php
include_once 'database.php';
$connection = new database();
$connection->init();

class adminlib
{

	function getPupilSightData()
	{
		$sql = "SELECT * FROM pupilsight_cms WHERE id = 1";
		$result = database::doSelectOne($sql);
		return $result;
	}


	function createPupilSightData($input)
	{
		$sql = "UPDATE pupilsight_cms SET ";
		foreach ($input as $key => $value) {
			$sql .= $key . '= "' . $value . '", ';
		}
		$sql = rtrim($sql, ", ");
		$sql .= " WHERE id = 1";

		$result = database::doUpdate($sql);
		//return $result;
	}

	function getPupilSightSectionData()
	{
		$sql = "SELECT * FROM pupilsight_cms_sections WHERE status = '1' ";
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
			$sql .= $key . '= "' . $value . '", ';
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
			$sql .= $key . '= "' . $value . '", ';
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
			$sql .= $key . '= "' . $value . '", ';
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
}
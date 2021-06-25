<?php

$mem = array();
	function validate_new_key($id)
	{
		$flag = FALSE;
		while ($flag == FALSE) {
			$key = array_search($id, $mem); // $key = 2;
			if (empty($key)) {
				$flag = TRUE;
			} else {
				$id = createComplexKey();
			}
		}
		return $id;
	}

	function resetSuperKey()
	{
		unset($mem);
	}

	function createSuperKey()
	{
		$id = createComplexKey();
		if (!empty($mem)) {
			$id = validate_new_key($id);
			array_push($mem, $id);
		} else {
			$mem = array($id);
		}
		return $id;
	}

	$oldID = -1;

	function createKey()
	{
		$id = -1;
		try {
			//$four_digit_random_number = mt_rand(1000, 9999);
			$two_digit_random_number = mt_rand(10, 99);
			$today = time();
			$id = $today . $two_digit_random_number;
			if ($id == $oldID) {
				createKey();
			} else {
				$oldID = $id;
			}
		} catch (Exception $ex) {
			echo 'common.createKey(): ' . $ex->getMessage();
		}
		return $id;
	}

	function createComplexKey()
	{
		$id = -1;
		try {
			$random_number = mt_rand(1000, 9999);
			$today = time();
			$id = $today . $random_number;
			if ($id == $oldID) {
				createComplexKey();
			} else {
				$oldID = $id;
			}
		} catch (Exception $ex) {
			echo 'common.createKey(): ' . $ex->getMessage();
		}
		return $id;
	}

?>
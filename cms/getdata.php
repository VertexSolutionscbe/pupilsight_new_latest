<?php

include_once 'w2f/adminLib.php';
$adminlib = new adminlib();
$data = $_POST['val'];
$camdata = $adminlib->getApplist($data);
// echo '<pre>'; 
// print_r($camdata); 
// die();
if (!empty($camdata)) {
	$cnt = 1;
	foreach ($camdata as $row) {
		if (!empty($row['id']) || $row['form_id'] || $row['submission_id']) {
			$statedata = $adminlib->getstatedata($row['id'], $row['form_id'], $row['submission_id']);
			echo "<tr>";
			echo '<td>';
			echo $cnt;
			echo '</td>';
			echo '<td>';
			echo $row["username"];
			echo '</td>';
			echo '<td>';
			echo $row["name"];
			echo '</td>';
			echo '<td>';
			echo $row['created_at'];
			echo '</td>';
			echo '<td>';
			echo $statedata;
			echo '</td>';
			echo '<td>';
			if (!empty($row['application_no'])) {
				$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
				$link = $base_url . '/public/applicationpdf/parent/' . $row['application_no'];
				echo '<a href="' . $link . '" download><img title="Download" src="' . $base_url . '/cms/assets/css/img/download-box.png"></img></a>';
			}
			echo '</td>';
			echo '<td>';
			if (!empty($row['transaction_id'])) {
				$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
				$link = $base_url . '/public/receipts/' . $row['transaction_id'];
				echo '<a href="' . $link . '" download><img title="Download" src="' . $base_url . '/cms/assets/css/img/download-box.png"></img></a>';
			}
			echo '</td>';
			echo '</tr>';
		} else {
			echo "<tr>";
			echo '<td colspan="4">No Record Found</td>';
			echo '</tr>';
		}
		$cnt++;
	}
}

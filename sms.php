<?php
/*
$url1 = "https://japi.instaalerts.zone/httpapi/QueryStringReceiver?ver=1.0&key=WVDLxrEydZYYMKZ8w6aJLQ==&encrpt=0&send=" . $senderid;
$url1 .= "&text=" . urlencode($msg);
$url1 .= "&dest=" . $numbers;
*/

//$host = 'https://api.karix.io/message/';
$host = 'https://api.karix.io/message/';
$key = 'WVDLxrEydZYYMKZ8w6aJLQ==';
// $authoriseKey = base64_encode('259387e0-4c4a-4410-a934-056598ae6f20:03060bce-4903-4347-90f8-6a87595ebbd2');
$authoriseKey = base64_encode('WVDLxrEydZYYMKZ8w6aJLQ==');
$headers = array(
	"Authorization" => $authoriseKey,
	"Content-Type" => "application/json",
);
$fields = array(
	"ver" => '1.0',
	'key' => $key,
	'encrpt' => '0',
	'messages' => array(
		'dest' => array('9883928942', '8867776787'),
		'text' => 'Dear Parent, This is a test SMS kindly ignore. Thoughtnet',
	),
);
$jsonField = json_encode($fields);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $host);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonField);
$json_result = curl_exec($ch);
print_r($json_result);
exit();

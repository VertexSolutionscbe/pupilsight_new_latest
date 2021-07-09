<?php
include 'config.php';
$conn = new mysqli($databaseServer, $databaseUsername, $databasePassword, $databaseName);
// Check connection
if ($conn->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}


function curl_post($url, $post = NULL)
{
    try {
        //$url = 'https://127.0.0.1/ajax/received.php';
        $curl = curl_init();
        //$post['test'] = 'examples daata'; // our data todo in received
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, TRUE);

        if (!empty($post)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }

        curl_setopt($curl, CURLOPT_USERAGENT, 'api');

        curl_setopt($curl, CURLOPT_TIMEOUT, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl,  CURLOPT_RETURNTRANSFER, false);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($curl, CURLOPT_DNS_CACHE_TIMEOUT, 10);

        curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);

        curl_exec($curl);

        curl_close($curl);
    } catch (Exception $ex) {
        echo $ex;
    }
}

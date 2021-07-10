<?php
include 'config.php';
$conn = new mysqli($databaseServer, $databaseUsername, $databasePassword, $databaseName);
// Check connection
if ($conn->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

$param = array_merge($_GET, $_POST);
if ($param) {
    //from post or get
    if (isset($param["type"])) {
        $type = $param["type"];
        if ($type == "config") {
            $res = smsGatewayConfig($conn);
            if ($res) {
                echo json_encode($res);
            }
        } else if ($type == "smsGatewayPro") {
            $res = smsGatewayPro($conn, $param["numbers"], $param['msg'], $param['msgto'], $param['msgby'], $param['multipleSend']);
            if ($res) {
                echo json_encode($res);
            }
        } else if ($type == "smsGateway") {
            $res = smsGateway($conn, $param["activeGateway"], $param["senderid"], $param["smsCount"], $param["numbers"], $param['msg'], $param['msgto'], $param['msgby'], $param['multipleSend']);
            if ($res) {
                echo json_encode($res);
            }
        }
    }
} else {
    //from command
    if (isset($argv)) {
        $param = $argv;
        if ($param[1] == "config") {
            $res = smsGatewayConfig($conn);
            if ($res) {
                print_r($res);
            }
        } else if ($param[1] == "smsGatewayPro") {
            //$param["numbers"], $param['msg'], $param['msgto'], $param['msgby'], $param['multipleSend']
            $res = smsGatewayPro($conn, $param[2], $param[3], $param[4], $param[5], $param[6]);
            if ($res) {
                print_r($res);
            }
        } else if ($param[1] == "smsGateway") {
            //$param["activeGateway"], $param["senderid"], $param["smsCount"], $param["numbers"], $param['msg'], $param['msgto'], $param['msgby'], $param['multipleSend']
            $res = smsGateway($conn, $param[2], $param[3], $param[4], $param[5], $param[6], $param[7], $param[8], $param[9]);
            if ($res) {
                print_r($res);
            }
        }
    } else {
        echo "Empty Parameters";
    }
}



function smsGatewayConfig($conn)
{
    $res = array();
    try {
        $sq = "select name, value from pupilsightSetting where scope='Messenger' ";
        $result = $conn->query($sq);
        if ($result->num_rows > 0) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $data = keyArray($rows, 'name');

            $res['activeGateway'] = $data["smsGateway"]["value"];
            $res['senderid'] = $data["smsSenderID"]["value"];
            $smsCountGateway = "smsCount" . $data["smsGateway"]["value"];
            $res['smsCount'] = $data[$smsCountGateway]["value"];
            $result->free_result();
            //smsGateway, smsPassword, smsSenderID, smsUsername, smsCountKarix,
            //print_r($rows);
        }
    } catch (Exception $ex) {
        echo "smsGatewayPro: " . $ex->getMessage();
    }
    return $res;
}

function smsGatewayPro($conn, $numbers, $msg, $msgto, $msgby, $multipleSend = null)
{
    $result = array();
    try {
        $config = smsGatewayConfig($conn);
        if ($config) {
            $activeGateway = $config['activeGateway'];
            $senderid = $config['senderid'];
            $smsCount = $config['smsCount'];
            smsGateway($conn, $activeGateway, $senderid, $smsCount, $numbers, $msg, $msgto, $msgby, $multipleSend);
            //smsGateway, smsPassword, smsSenderID, smsUsername, smsCountKarix,
            //print_r($rows);
            $result = array("status" => 1);
        }
    } catch (Exception $ex) {
        echo "smsGatewayPro: " . $ex->getMessage();
        $result = array("status" => 2, "msg" => $ex->getMessage());
    }
    return $result;
}

function keyArray($data, $col)
{
    $len = count($data);
    $i = 0;
    $res = array();
    while ($i < $len) {
        $res[$data[$i][$col]] = $data[$i];
        $i++;
    }
    return $res;
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

function smsGateway($conn, $activeGateway, $senderid, $smsCount, $numbers, $msg, $msgto, $msgby, $multipleSend = null)
{
    try {
        switch ($activeGateway) {
            case 'Karix':
                $flag = _karix($conn, $senderid, $smsCount, $numbers, $msg, $msgto, $msgby, $multipleSend);
                break;
            case 'Gupshup':
                $flag = _gupshup($conn, $senderid, $smsCount, $numbers, $msg, $msgto, $msgby, $multipleSend);
                break;
            default:
                echo "sms not configured";
        }
        $result = array("status" => 1);
    } catch (Exception $ex) {
        $result = array("status" => 2, "msg" => $ex->getMessage());
    }
    return $result;
}

function _karix($conn, $senderid, $smsCount, $numbers, $msg, $msgto, $msgby, $multipleSend = null)
{
    $flag = TRUE;
    try {
        $url1 = "https://japi.instaalerts.zone/httpapi/QueryStringReceiver?ver=1.0&key=WVDLxrEydZYYMKZ8w6aJLQ==&encrpt=0&send=" . $senderid;
        $url1 .= "&text=" . urlencode($msg);
        $url1 .= "&dest=" . $numbers;
        // echo $url1;
        // die();
        if ($multipleSend) {
            echo "testchecking..";
            curl_post($url1);
            $res3 = 200;
            $res5[1] = 1;
        } else {
            $res = file_get_contents($url1);
            $res1 = explode('&', $res);
            $res2 = explode('=', $res1[1]);
            $res3 = $res2[1];
            //print_r($res);//die();
            $res4 = explode('&', $res1[0]);
            $res5 = explode('=', $res4[0]);
        }
        if ($res3 == 200) {
            updateSmsCount($conn, $smsCount, "Karix");
            if ($msgby != '') {
                updateMessengerTable($conn, $msg, $msgto, $msgby);
            }
            //die();
            $p = explode(',', $numbers);
            if ($msgby != '') {
                $nowtime = date("Y-m-d H:i:s");

                $sq = "INSERT INTO pupilsightMessengerReceipt  (pupilsightMessengerID, pupilsightPersonID, targetType, targetID, contactType, contactDetail, key, confirmed, requestid, confirmedTimestamp) ";
                $sq .= "values";
                $flag = false;
                foreach ($p as $numb) {
                    if ($flag) {
                        $sq .= ",";
                    } else {
                        $flag = true;
                    }
                    $sq .= "('$msgby', '$msgby', 'Individuals', '$msgto', 'SMS', $numb, 'NA', 'N', $res5[1], '$nowtime')";
                }
                $conn->query($sq);
            }
        }
    } catch (Exception $ex) {
        print_r($ex);
        $flag = FALSE;
    }
    $dkey = $res5[1];
    return $dkey;
}


function _gupshup($conn, $senderid, $smsCount, $numbers, $msg, $msgto, $msgby, $multipleSend = null)
{
    $flag = TRUE;
    try {
        $url = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
        $url .= "&send_to=" . $numbers;
        //$url .="&msg=".rawurlencode($msg);
        $url .= "&msg=" . urlencode($msg);
        $url .= "&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
        $res = file_get_contents($url);
        $res1 = explode('|', $res);
        $res2 = trim($res1[0]);
        $res3 = 'success';
        if (strcmp($res2, $res3) === 0) {
            updateSmsCount($conn, $smsCount, "Gupshup");
            if ($msgby != '') {
                updateMessengerTable($conn, $msg, $msgto, $msgby);
            }
        }
        $p = explode(',', $numbers);
        if ($msgby != '') {
            $nowtime = date("Y-m-d H:i:s");

            $sq = "INSERT INTO pupilsightMessengerReceipt  (pupilsightMessengerID, pupilsightPersonID, targetType, targetID, contactType, contactDetail, key, confirmed, requestid, confirmedTimestamp) ";
            $sq .= "values";
            $flag = false;
            foreach ($p as $numb) {
                if ($flag) {
                    $sq .= ",";
                } else {
                    $flag = true;
                }
                $sq .= "('$msgby', '$msgby', 'Individuals', '$msgto', 'SMS', $numb, 'NA', 'N', $res5[1], '$nowtime')";
            }
            $conn->query($sq);
        }
    } catch (Exception $ex) {
        print_r($ex);
        $flag = FALSE;
    }
    return $flag;
}

function updateSmsCount($conn, $count, $description)
{
    try {
        $sq = "UPDATE pupilsightSetting SET value=" . $count . " WHERE scope='Messenger' AND description='" . $description . "' ";
        $conn->query($sq);
    } catch (Exception $ex) {
        print_r($ex);
    }
}

function updateMessengerTable($conn, $msg, $msgto, $msgby)
{
    /*
    $sqlAI = "SHOW TABLE STATUS LIKE pupilsightMessenger ";
    $db = new DBQuery();
    $rs = $db->selectRaw($sqlAI, TRUE);
    //print_r($rs);
    //$AI = $rs[0]['Auto_increment'];
    $AI = str_pad($rs[0]['Auto_increment'], 12, "0", STR_PAD_LEFT);
    */

    $sms = "Y";
    $date1 = date('Y-m-d');
    /*$date2 = date('Y-m-d');
        $date3 = date('Y-m-d');*/
    $todaydatetime = date("Y-m-d H:i:s");

    $sq = 'INSERT INTO pupilsightMessenger SET  messageWall_date1="' . $date1 . '", sms="' . $sms . '", subject="NA", body="' . addslashes(htmlspecialchars($msg)) . '",  pupilsightPersonID="' . $msgby . '", messengercategory="Other" ';
    $conn->query($sq);
    $AI = $conn->insert_id;
    //echo "<br>" . $AI . "<br>";
    $sq = "INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=$AI, type='Individuals', id='$msgto'";
    $conn->query($sq);
}

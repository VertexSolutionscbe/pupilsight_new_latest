<?php
/*
Pupilsight, Flexible & Open School System
*/
include "pupilsight.php";
$session = $container->get("session");

//Proceed
$type = "";
if (isset($_POST["type"])) {
    $type = $_POST["type"];
    /*if (!isset($_SESSION[$guid]["pupilsightPersonID"])) {
        $result = ["status" => 2, "msg" => "Session Expired"];
        echo json_encode($result);
        die();
    }*/
}

function createId()
{
    $rand = mt_rand(10, 99);
    return time() . $rand;
}

function advDateOut($ts)
{
    if (date("Ymd") == date("Ymd", strtotime($ts))) {
        return date("h:i A", strtotime($ts));
    } else {
        return date("j M, Y h:i A", strtotime($ts));
    }
}

function get2Char($string)
{
    $str = explode(" ", $string);
    $firstCharacter = $str[0];
    $firstCharacter = substr($str[0], 0, 1);
    $firstTwoCharacters = substr($str[0], 0, 2);

    if (count($str) > 1) {
        $firstTwoCharacters = $firstCharacter . substr($str[1], 0, 1);
    }
    return $firstTwoCharacters;
}

/* open Description Indicator */
if ($type == "postMessage") {
    try {
        $result = [];
        $msg = $_POST["msg"];
        if ($msg) {
            try {
                $id = createId();
                //chat_parent_id = N
                $cuid = $_SESSION[$guid]["pupilsightPersonID"];
                $chat_parent_id = null;
                if (isset($_POST["chat_parent_id"])) {
                    $chat_parent_id = $_POST["chat_parent_id"];
                }

                $pupilsightSchoolYearID =
                    $_SESSION[$guid]["pupilsightSchoolYearID"];

                $msg_type = 2;
                if ($_POST["msg_type"]) {
                    $msg_type = $_POST["msg_type"];
                }
                //attachment
                $cdt = date("Y-m-d H:i:s");

                //$sq ="insert into chat_message(id, chat_parent_id, cuid, pupilsightSchoolYearID, msg_type, attachment, msg, cdt, timestamp)";
                $sq =
                    "insert into chat_message(id, chat_parent_id, cuid, pupilsightSchoolYearID, msg_type, msg, cdt, udt, timestamp)";
                $sq .=
                    "values('" .
                    $id .
                    "','" .
                    $chat_parent_id .
                    "','" .
                    $cuid .
                    "','" .
                    $pupilsightSchoolYearID .
                    "','" .
                    $msg_type .
                    "','" .
                    nl2br(addslashes(htmlspecialchars($msg))) .
                    "','" .
                    $cdt .
                    "','" .
                    $cdt .
                    "','" .
                    time() .
                    "')";
                //echo $sq;
                $connection2->query($sq);

                $result["status"] = 1;
                $result["msg"] = "Message Posted Successfully.";
            } catch (Exception $ex) {
                $result["status"] = 2;
                $result["msg"] = "Exception: " . $ex->getMessage();
            }
        } else {
            $result["status"] = 2;
            $result["msg"] = "Invalid Message Parameter.";
        }

        //echo $squ;
    } catch (Exception $ex) {
        $res["status"] = 2;
        $res["message"] = $ex->getMessage();
    }
    if ($result) {
        echo json_encode($result);
    }
} elseif ($type == "getMessage") {
    $result = [];
    try {
        $lts = "";
        if (isset($_POST["lts"])) {
            $lts = $_POST["lts"];
        }

        $sq = "select cm.*, p.officialName from chat_message as cm ";
        $sq .=
            "left join pupilsightPerson as p on cm.cuid=p.pupilsightPersonID ";
        if ($lts) {
            $sq .= " where cm.timestamp > " . $lts . " ";
        }
        $sq .= " order by cm.cdt desc limit 0, 10000 ";
        //echo $sq;
        $query = $connection2->query($sq);
        $res = $query->fetchAll();
        //print_r($res);
        if ($res) {
            //$len = count($res);
            $i = count($res) - 1;
            while ($i > -1) {
                $res[$i]["officialName"] = ucwords(
                    strtolower($res[$i]["officialName"])
                );
                $res[$i]["ts"] = advDateOut($res[$i]["cdt"]);
                $res[$i]["shortName"] = get2Char($res[$i]["officialName"]);

                $parentid = $res[$i]["chat_parent_id"];
                if ($parentid) {
                    if (isset($result[$parentid])) {
                        $result[$parentid]["response"][] = $res[$i];
                    } else {
                        //no parent data here
                        $sqj =
                            "select cm.*, p.officialName from chat_message as cm ";
                        $sqj .=
                            "left join pupilsightPerson as p on cm.cuid=p.pupilsightPersonID ";
                        $sqj .= " where cm.id='" . $parentid . "'";
                        $query1 = $connection2->query($sqj);
                        $result[$parentid] = $query1->fetch();
                        $result[$parentid]["response"][] = $res[$i];
                    }
                } else {
                    $id = $res[$i]["id"];
                    $result[$id] = $res[$i];
                }
                $i--;
            }
        }
    } catch (Exception $ex) {
        $result["status"] = 2;
        $result["msg"] = "Exception: " . $ex->getMessage();
    }
    if ($result) {
        echo json_encode($result);
    }
}

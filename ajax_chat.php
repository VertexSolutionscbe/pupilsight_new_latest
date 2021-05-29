<?php
/*
Pupilsight, Flexible & Open School System
*/
include 'pupilsight.php';
$session = $container->get('session');

//Proceed
$type = '';
$roleid = '';
$cuid = '';
if (isset($_POST['type'])) {
    $type = $_POST['type'];
    if (!isset($_SESSION[$guid]['pupilsightPersonID'])) {
        $result = ['status' => 2, 'msg' => 'Session Expired'];
        echo json_encode($result);
        die();
    } else {
        $roleid = $_SESSION[$guid]['pupilsightRoleIDPrimary'];
        $cuid = $_SESSION[$guid]['pupilsightPersonID'];
    }
}

$mem = [];

function validate_new_key($id)
{
    $flag = false;
    while ($flag == false) {
        $key = array_search($id, $mem); // $key = 2;
        if (empty($key)) {
            $flag = true;
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
        $mem = [$id];
    }
    return $id;
}

function createComplexKey()
{
    $oldID = -1;
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

function createId()
{
    $rand = mt_rand(10, 99);
    return time() . $rand;
}

function advDateOut($ts)
{
    if (date('Ymd') == date('Ymd', strtotime($ts))) {
        return date('h:i A', strtotime($ts));
    } else {
        return date('j M, Y h:i A', strtotime($ts));
    }
}

function get2Char($string)
{
    $str = explode(' ', $string);
    $firstCharacter = $str[0];
    $firstCharacter = substr($str[0], 0, 1);
    $firstTwoCharacters = substr($str[0], 0, 2);

    if (count($str) > 1) {
        $firstTwoCharacters = $firstCharacter . substr($str[1], 0, 1);
    }
    return $firstTwoCharacters;
}

function chatAttachment()
{
    $attachment = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        try {
            $filename = $_FILES['attachment']['name'];
            $filetype = $_FILES['attachment']['type'];
            $filesize = $_FILES['attachment']['size'];

            // Verify file extension
            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            //$filename = time() . "_" . $_FILES["attachment"]["name"];
            $fn =
                time() .
                '_' .
                preg_replace('/[^a-z0-9\_\-\.]/i', '', basename($filename));
            $fileTarget = $_SERVER['DOCUMENT_ROOT'] . '/public/chat/' . $fn;
            $attachment = '/public/chat/' . $fn;

            move_uploaded_file($_FILES['attachment']['tmp_name'], $fileTarget);
        } catch (Exception $ex) {
            $result['status'] = 2;
            $result['msg'] = 'Exception: ' . $ex->getMessage();
        }
    }
    return $attachment;
}

function getIndividualUserList($con, $res){
    try{
        if($res["delivery_type"]=="individual" && empty($res["chat_parent_id"])){
            $chat_msg_id = $res["id"];
            $sq = "select cs.uid, p.officialName from chat_share as cs left join pupilsightPerson as p on cs.uid=p.pupilsightPersonID where cs.chat_msg_id='".$chat_msg_id."' and p.pupilsightPersonID is not null ";
            $query = $con->query($sq);
            return $query->fetchAll();
        }
    }catch(Exception $ex){
        echo $ex->getMessage();
    }
    return "";
}

function getStudentQuery($connection2, $cuid, $pupilsightSchoolYearID, $lts){
    $sq1="select pupilsightSchoolYearID, pupilsightProgramID, pupilsightYearGroupID, pupilsightRollGroupID from pupilsightStudentEnrolment ";
    $sq1 .="where pupilsightPersonID='".$cuid."' ";
    $sq1 .="and pupilsightSchoolYearID='".$pupilsightSchoolYearID."' ";
    $query2 = $connection2->query($sq1);
    $res2 = $query2->fetch();
    $classid = $res2["pupilsightSchoolYearID"]."-".$res2["pupilsightProgramID"]."-".$res2["pupilsightYearGroupID"]."-".$res2["pupilsightRollGroupID"];


    $sq = 'select cm.id, cm.chat_parent_id, cm.cuid,cm.pupilsightSchoolYearID,cm.msg_type, ';
    $sq .='cm.attachment,cm.delivery_type,cm.group_id,cm.group_name, cm.cdt,cm.udt,cm.timestamp,p.officialName,cm.msg from chat_message as cm ';
    $sq .='left join pupilsightPerson as p on cm.cuid=p.pupilsightPersonID ';
    $sq .= ' left join chat_share as cs on cm.id=cs.chat_msg_id ';
    $sq .= ' where  ';
    $sq .="  (cm.delivery_type in('all_students','all','".$classid."') 
            or cs.uid='" .$cuid ."' or cm.cuid='".$cuid."') ";

    if ($lts) {
        $sq .= ' and cm.timestamp > ' . $lts . ' ';
    }
    $sq .= ' order by cm.cdt desc limit 0, 10000 ';
    $query = $connection2->query($sq);
    return $query->fetchAll();

}

function getParentQuery($connection2, $cuid, $pupilsightSchoolYearID, $lts){
    $sq1="select pupilsightSchoolYearID, pupilsightProgramID, pupilsightYearGroupID, pupilsightRollGroupID from pupilsightStudentEnrolment ";
    $sq1 .="where pupilsightPersonID='".$_SESSION['student_id']."' ";
    $sq1 .="and pupilsightSchoolYearID='".$pupilsightSchoolYearID."' ";
    $query2 = $connection2->query($sq1);
    $res2 = $query2->fetch();
    $classid = $res2["pupilsightSchoolYearID"]."-".$res2["pupilsightProgramID"]."-".$res2["pupilsightYearGroupID"]."-".$res2["pupilsightRollGroupID"];


    $sq = 'select cm.id, cm.chat_parent_id, cm.cuid,cm.pupilsightSchoolYearID,cm.msg_type, ';
    $sq .='cm.attachment,cm.delivery_type,cm.group_id,cm.group_name, cm.cdt,cm.udt,cm.timestamp,p.officialName,cm.msg from chat_message as cm ';
    $sq .='left join pupilsightPerson as p on cm.cuid=p.pupilsightPersonID ';
    $sq .= ' left join chat_share as cs on cm.id=cs.chat_msg_id ';
    $sq .= ' where  ';
    $sq .=" (cm.delivery_type in('all_students','all','".$classid."')
            or cs.uid in('" .$_SESSION['student_id'] ."','" .$cuid ."')
            or cm.cuid in('" .$_SESSION['student_id'] ."','" .$cuid ."')) ";
    

    if ($lts) {
        $sq .= ' and cm.timestamp > ' . $lts . ' ';
    }
    $sq .= ' order by cm.cdt desc limit 0, 10000 ';
    $query = $connection2->query($sq);
    return $query->fetchAll();
}

function getAdminQuery($connection2, $lts){
    $sq = 'select cm.id, cm.chat_parent_id, cm.cuid,cm.pupilsightSchoolYearID,cm.msg_type, ';
    $sq .='cm.attachment,cm.delivery_type,cm.group_id,cm.group_name, cm.cdt,cm.udt,cm.timestamp,p.officialName,cm.msg from chat_message as cm ';
    $sq .='left join pupilsightPerson as p on cm.cuid=p.pupilsightPersonID ';
    if ($lts) {
        $sq .= ' where cm.timestamp > ' . $lts . ' ';
    }
    $sq .= ' order by cm.cdt desc limit 0, 10000 ';
    //echo $sq;
    $query = $connection2->query($sq);
    return $query->fetchAll();
}

function getTeacherQuery($connection2, $cuid, $lts){
    $sq = 'select cm.id, cm.chat_parent_id, cm.cuid,cm.pupilsightSchoolYearID,cm.msg_type, ';
    $sq .='cm.attachment,cm.delivery_type,cm.group_id,cm.group_name, cm.cdt,cm.udt,cm.timestamp,p.officialName,cm.msg from chat_message as cm ';
    $sq .='left join pupilsightPerson as p on cm.cuid=p.pupilsightPersonID ';
    $sq .= ' left join chat_share as cs on cm.id=cs.chat_msg_id ';
    
    if(isset($_SESSION["teacher_class_id"])){
        $sq .= " where (cm.delivery_type in(".$_SESSION["teacher_class_id"].",'all_staff') or cs.uid='" . $cuid . "' or cm.cuid='".$cuid."')  ";
    }else{
        $sq .= " where (cs.uid='" . $cuid . "' or cm.cuid='".$cuid."')  ";
    }

    if ($lts) {
        $sq .= ' and cm.timestamp > ' . $lts . ' ';
    }

    $sq .= ' order by cm.cdt desc limit 0, 10000 ';
    $query = $connection2->query($sq);
    return $query->fetchAll();
}

function getOtherRoleQuery($connection2, $cuid, $lts){
    
    $sq = 'select cm.id, cm.chat_parent_id, cm.cuid,cm.pupilsightSchoolYearID,cm.msg_type, ';
    $sq .='cm.attachment,cm.delivery_type,cm.group_id,cm.group_name, cm.cdt,cm.udt,cm.timestamp,p.officialName,cm.msg from chat_message as cm ';
    $sq .='left join pupilsightPerson as p on cm.cuid=p.pupilsightPersonID ';
    $sq .= 'left join chat_share as cs on cm.id=cs.chat_msg_id ';
    $sq .= "where and (cm.delivery_type in('all','all_staff') or cs.uid='" . $cuid . "' or cm.cuid='".$cuid."')  ";
    
    if ($lts) {
        $sq .= ' and cm.timestamp > ' . $lts . ' ';
    }
    //for other role we need to
    $sq .= ' order by cm.cdt desc limit 0, 10000 ';
    $query = $connection2->query($sq);
    return $query->fetchAll();
}

function getRoleQuery($roleid, $connection2, $cuid, $pupilsightSchoolYearID, $lts){
    if($roleid=="001"){
        return getAdminQuery($connection2, $lts);
    }else if($roleid=="002"){
        return getTeacherQuery($connection2, $cuid, $lts);
    }else if($roleid=="003"){
        return getStudentQuery($connection2, $cuid, $pupilsightSchoolYearID, $lts);
    }else if($roleid=="004"){
        return getParentQuery($connection2, $cuid, $pupilsightSchoolYearID, $lts);
    }else {
        return getOtherRoleQuery($connection2, $cuid, $lts);
    }
}

/* open Description Indicator */
if ($type == 'postMessage') {
    try {
        $result = [];

        $msg = null;
        $people = null;
        $delivery_type = null;
        $group_id = null;
        $group_name = null;
        $flag = true;

        if (isset($_POST['msg'])) {
            $msg = $_POST['msg'];
        } else {
            $flag = false;
        }

        if (isset($_POST['people'])) {
            $people = $_POST['people'];
        }

        if (isset($_POST['delivery_type'])) {
            $delivery_type = $_POST['delivery_type'];
        } else {
            $flag = false;
        }

        if ($delivery_type == 'individual') {
            if (empty($people)) {
                $flag = false;
            } else {
                if (is_array($people) == false) {
                    $people = explode(',', $people);
                }
            }
        }

        if (isset($_POST['group_id'])) {
            $group_id = $_POST['group_id'];
        }
        if (isset($_POST['group_id'])) {
            $group_name = $_POST['group_name'];
        }

        if ($flag) {
            try {
                $attachment = chatAttachment();

                $id = createId();
                //chat_parent_id = N

                $pupilsightSchoolYearID =
                    $_SESSION[$guid]['pupilsightSchoolYearID'];

                $msg_type = 2;
                if ($_POST['msg_type']) {
                    $msg_type = $_POST['msg_type'];
                }
                //attachment
                $cdt = date('Y-m-d H:i:s');
                $timestamp = time();
                //$sq ="insert into chat_message(id, chat_parent_id, cuid, pupilsightSchoolYearID, msg_type, attachment, msg, cdt, timestamp)";
                $sq =
                    'insert into chat_message(id, cuid, pupilsightSchoolYearID, msg_type, attachment, delivery_type, group_id, group_name, msg, cdt, udt, timestamp)';
                $sq .=
                    "values('" .
                    $id .
                    "','" .
                    $cuid .
                    "','" .
                    $pupilsightSchoolYearID .
                    "','" .
                    $msg_type .
                    "','" .
                    $attachment .
                    "','" .
                    $delivery_type .
                    "','" .
                    $group_id .
                    "','" .
                    $group_name .
                    "','" .
                    nl2br(addslashes(htmlspecialchars($msg))) .
                    "','" .
                    $cdt .
                    "','" .
                    $cdt .
                    "','" .
                    $timestamp .
                    "')";
                //echo $sq;
                $connection2->query($sq);
                //INSERT INTO tbl_name (a,b,c) VALUES(1,2,3),(4,5,6),(7,8,9);
                if ($delivery_type == 'individual') {
                    $sqi =
                        'insert into chat_share (id, chat_msg_id, uid, isread, cdt, udt, timestamp) values ';
                    $len = count($people);
                    $i = 0;
                    $sqi .= '';
                    while ($i < $len) {
                        $cid = createSuperKey();

                        if ($i > 0) {
                            $sqi .= ',';
                        }
                        $sqi .=
                            "('" .
                            $cid .
                            "','" .
                            $id .
                            "','" .
                            $people[$i] .
                            "','1','" .
                            $cdt .
                            "','" .
                            $cdt .
                            "','" .
                            $timestamp .
                            "')";
                        $i++;
                    }
                    $connection2->query($sqi);
                    resetSuperKey();
                }
                $result['status'] = 1;
                $result['msg'] = 'Message Posted Successfully.';
            } catch (Exception $ex) {
                $result['status'] = 2;
                $result['msg'] = 'Exception: ' . $ex->getMessage();
            }
        } else {
            $result['status'] = 2;
            $result['msg'] = 'Invalid Message Parameter.';
        }

        //echo $squ;
    } catch (Exception $ex) {
        $res['status'] = 2;
        $res['message'] = $ex->getMessage();
    }
    if ($result) {
        echo json_encode($result);
    }
} elseif ($type == 'replyMessage') {
    try {
        $result = [];

        $msg = null;

        $flag = true;

        $attachment = chatAttachment();

        if (isset($_POST['msg'])) {
            $msg = $_POST['msg'];
        } else {
            $flag = false;
        }
        if (isset($_POST['delivery_type'])) {
            $delivery_type = $_POST['delivery_type'];
        } else {
            $flag = false;
        }

        $chat_parent_id = null;
        if (isset($_POST['chat_parent_id'])) {
            $chat_parent_id = $_POST['chat_parent_id'];
        } else {
            $flag = false;
        }

        if ($flag) {
            try {
                $id = createId();
                //chat_parent_id = N

                $pupilsightSchoolYearID =
                    $_SESSION[$guid]['pupilsightSchoolYearID'];

                //attachment
                $cdt = date('Y-m-d H:i:s');
                $timestamp = time();
                //$sq ="insert into chat_message(id, chat_parent_id, cuid, pupilsightSchoolYearID, msg_type, attachment, msg, cdt, timestamp)";
                $sq =
                    'insert into chat_message(id, chat_parent_id, cuid, pupilsightSchoolYearID, msg_type, attachment, delivery_type, msg, cdt, udt, timestamp)';
                $sq .=
                    "values('" .
                    $id .
                    "','" .
                    $chat_parent_id .
                    "','" .
                    $cuid .
                    "','" .
                    $pupilsightSchoolYearID .
                    "','2','" .
                    $attachment .
                    "','" .
                    $delivery_type .
                    "','" .
                    nl2br(addslashes(htmlspecialchars($msg))) .
                    "','" .
                    $cdt .
                    "','" .
                    $cdt .
                    "','" .
                    $timestamp .
                    "')";
                //echo $sq;
                $connection2->query($sq);

                if ($delivery_type == 'individual') {
                    $sqi = 'insert into chat_share ';
                    $sqi .=
                        '(id, chat_msg_id, uid, isread, cdt, udt, timestamp) values ';

                    $sqshare =
                        "select uid from chat_share where chat_msg_id='" .
                        $chat_parent_id .
                        "' ";
                    $query = $connection2->query($sqshare);
                    $res = $query->fetchAll();
                    $flag = false;
                    if ($res) {
                        $len = count($res);
                        $i = 0;
                        $sqi .= '';
                        while ($i < $len) {
                            $cid = createSuperKey();
                            if ($i > 0) {
                                $sqi .= ',';
                            }
                            $sqi .=
                                "('" .
                                $cid .
                                "','" .
                                $id .
                                "','" .
                                $res[$i]['uid'] .
                                "','1','" .
                                $cdt .
                                "','" .
                                $cdt .
                                "','" .
                                $timestamp .
                                "')";
                            $i++;
                            $flag = true;
                        }
                    }

                    $sqshare1 =
                        "select cuid as uid from chat_message where id='" .
                        $chat_parent_id .
                        "' ";
                    $query1 = $connection2->query($sqshare1);
                    $res1 = $query1->fetchAll();
                    if ($res1) {
                        $len = count($res1);
                        $i = 0;
                        while ($i < $len) {
                            $cid = createSuperKey();
                            if ($flag) {
                                $sqi .= ',';
                            } else {
                                $flag = true;
                            }
                            $sqi .=
                                "('" .
                                $cid .
                                "','" .
                                $id .
                                "','" .
                                $res1[$i]['uid'] .
                                "','1','" .
                                $cdt .
                                "','" .
                                $cdt .
                                "','" .
                                $timestamp .
                                "')";
                            $i++;
                        }
                    }

                    if ($sqi) {
                        $connection2->query($sqi);
                        resetSuperKey();
                    }
                }

                $result['status'] = 1;
                $result['msg'] = 'Message Posted Successfully.';
            } catch (Exception $ex) {
                $result['status'] = 2;
                $result['msg'] = 'Exception: ' . $ex->getMessage();
            }
        } else {
            $result['status'] = 2;
            $result['msg'] = 'Invalid Message Parameter.';
        }

        //echo $squ;
    } catch (Exception $ex) {
        $res['status'] = 2;
        $res['message'] = $ex->getMessage();
    }
    if ($result) {
        echo json_encode($result);
    }
} elseif ($type == 'getMessage') {
    $result = [];
    try {
        $lts = '';
        if (isset($_POST['lts'])) {
            $lts = $_POST['lts'];
        }
        $isWhereAdded = false;
        $classid="";
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

        //get role wise data
        $res = getRoleQuery($roleid, $connection2, $cuid, $pupilsightSchoolYearID, $lts);
        
        //print_r($res);
        //die();
        if ($res) {
            //$len = count($res);
            $i = count($res) - 1;
            $cnt = 0;
            
            while ($i > -1) {
                $officialName = $res[$i]['officialName'];
                $res[$i]['ts'] = advDateOut($res[$i]['cdt']);
                $res[$i]['shortName'] = get2Char($officialName);
                
                if ($res[$i]['attachment']) {
                    $res[$i]['attach_file'] = basename($res[$i]['attachment']);
                }
                
                $parentid = $res[$i]['chat_parent_id'];
                //echo "<br/>shortname : ".$res[$i]['shortName']." ts ".$ts." | ".$officialName." | ".$cdt;
                
                $res[$i]["userlist"] = getIndividualUserList($connection2, $res[$i]);    
                if ($parentid) {
                    if (isset($result[$parentid])) {
                        $result[$parentid]['response'][] = $res[$i];
                    } else {
                        //reply data found but post data not found so search the post actual data
                        $sqj ='select cm.*, p.officialName from chat_message as cm ';
                        $sqj .='left join pupilsightPerson as p on cm.cuid=p.pupilsightPersonID ';
                        $sqj .= " where cm.id='" . $parentid . "'";
                        $query1 = $connection2->query($sqj);
                        $resWait = $query1->fetch();
                        
                        $resWait['ts'] = advDateOut($resWait['cdt']);
                        $resWait['shortName'] = get2Char($resWait['officialName']);
                        $result[$parentid] = $resWait;
                        
                        $result[$parentid]['response'][] = $res[$i];
                    }
                } else {
                    $id = $res[$i]['id'];
                    $result[$id] = $res[$i];
                }
                $i--;
            }
        }
    } catch (Exception $ex) {
        $result['status'] = 2;
        $result['msg'] = 'Exception: ' . $ex->getMessage();
    }
    
    if ($result) {
        echo json_encode($result);
    }
} elseif ($type = 'people') {
    $result = [];
    try {
        $roleid = '003';
        if ($_POST['userType']) {
            $userType = $_POST['userType'];
        }
        $sq = 'select pupilsightPersonID, officialName from pupilsightPerson ';
        if ($userType == 'staff') {
            $sq .= 'where pupilsightRoleIDPrimary not in(003,004) ';
        } elseif ($userType == '003') {
            $sq .= "where pupilsightRoleIDPrimary='003' ";
        } elseif ($userType == '004') {
            $sq .= "where pupilsightRoleIDPrimary='004' ";
        }
        $sq .= 'order by officialName asc';
        //echo $sq;
        $query = $connection2->query($sq);
        $result = $query->fetchAll();
    } catch (Exception $ex) {
        $result['status'] = 2;
        $result['msg'] = 'Exception: ' . $ex->getMessage();
    }
    if ($result) {
        echo json_encode($result);
    }
}
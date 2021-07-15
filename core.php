<?php
/*
Pupilsight, Flexible & Open School System
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$ts = date('Y-m-d H:i:s');


$mem = [];

function getDomain()
{
    if (isset($_SERVER['HTTPS'])) {
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    } else {
        $protocol = 'http';
    }
    //return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}


function isPost($postid)
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST[$postid])) {
        if (empty($_POST[$postid])) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    return FALSE;
}

function getPost($postid)
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST[$postid])) {
        if (empty($_POST[$postid])) {
            return NULL;
        } else {
            return $_POST[$postid];
        }
    }
    return NULL;
}

function sqlNullSafe($postid)
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST[$postid])) {
        if (empty($_POST[$postid])) {
            return "NULL";
        } else {
            $dt = $_POST[$postid];
            return "'$dt'";
        }
    }
    return "NULL";
}

function getIntPost($postid)
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST[$postid])) {
        if (empty($_POST[$postid])) {
            return "";
        } else {
            return (int)($_POST[$postid]);
        }
    }
    return "";
}

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

//fileAttachment("attachment",'/public/chat/');
function fileAttachment($attach_file, $file_location)
{
    $attachment = NULL;
    if (isset($_FILES[$attach_file]) && $_FILES[$attach_file]['error'] == 0) {
        try {
            $filename = $_FILES[$attach_file]['name'];
            $filetype = $_FILES[$attach_file]['type'];
            $filesize = $_FILES[$attach_file]['size'];

            // Verify file extension
            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            //$filename = time() . "_" . $_FILES["attachment"]["name"];
            $fn = time() . '_' . preg_replace('/[^a-z0-9\_\-\.]/i', '', basename($filename));
            //$fileTarget = $_SERVER['DOCUMENT_ROOT'] . '/public/chat/' . $fn;
            //$attachment = '/public/chat/' . $fn;
            $fileTarget = $_SERVER['DOCUMENT_ROOT'] . $file_location . $fn;
            $attachment = $file_location . $fn;
            move_uploaded_file($_FILES[$attach_file]['tmp_name'], $fileTarget);
        } catch (Exception $ex) {
            $result['status'] = 2;
            $result['msg'] = 'Exception: ' . $ex->getMessage();
        }
    }
    return $attachment;
}

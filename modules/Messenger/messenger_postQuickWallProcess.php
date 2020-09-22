<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address']).'/messenger_postQuickWall.php';
$time = time();

if (isActionAccessible($guid, $connection2, '/modules/Messenger/messenger_postQuickWall.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    if (empty($_POST)) {
        $URL .= '&return=warning1';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Setup return variables
        $messageWall = $_POST['messageWall'];
        if ($messageWall != 'Y') {
            $messageWall = 'N';
        }
        $date1 = null;
        if (isset($_POST['date1'])) {
            if ($_POST['date1'] != '') {
                $date1 = dateConvert($guid, $_POST['date1']);
            }
        }
        $date2 = null;
        if (isset($_POST['date2'])) {
            if ($_POST['date2'] != '') {
                $date2 = dateConvert($guid, $_POST['date2']);
            }
        }
        $date3 = null;
        if (isset($_POST['date3'])) {
            if ($_POST['date3'] != '') {
                $date3 = dateConvert($guid, $_POST['date3']);
            }
        }
        $subject = $_POST['subject'];
        $body = stripslashes($_POST['body']);

        if ($subject == '' or $body == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //Lock table
            try {
                $sql = 'LOCK TABLES pupilsightMessenger WRITE';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Get next autoincrement
            try {
                $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightMessenger'";
                $resultAI = $connection2->query($sqlAI);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $rowAI = $resultAI->fetch();
            $AI = str_pad($rowAI['Auto_increment'], 12, '0', STR_PAD_LEFT);

            //Write to database
            try {
                $data = array('email' => '', 'messageWall' => $messageWall, 'messageWall_date1' => $date1, 'messageWall_date2' => $date2, 'messageWall_date3' => $date3, 'sms' => '', 'subject' => $subject, 'body' => $body, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'timestamp' => date('Y-m-d H:i:s'));
                $sql = 'INSERT INTO pupilsightMessenger SET email=:email, messageWall=:messageWall, messageWall_date1=:messageWall_date1, messageWall_date2=:messageWall_date2, messageWall_date3=:messageWall_date3, sms=:sms, subject=:subject, body=:body, pupilsightPersonID=:pupilsightPersonID, timestamp=:timestamp';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo $e->getMessage();
                exit();
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 12, '0', STR_PAD_LEFT);

            try {
                $sql = 'UNLOCK TABLES';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $partialFail = false;
            $choices = $_POST['roleCategories'];
            if ($choices != '') {
                foreach ($choices as $t) {
                    try {
                        $data = array('AI' => $AI, 't' => $t);
                        $sql = "INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:AI, type='Role Category', id=:t";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                }
            }

            if ($partialFail == true) {
                $URL .= '&return=warning1';
                header("Location: {$URL}");
            } else {
                //Success 0
                $_SESSION[$guid]['pageLoads'] = null;
				$URL .= "&return=success0&editID=$AI";
                header("Location: {$URL}");
            }
        }
    }
}

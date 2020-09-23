<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include '../../pupilsight.php';

$output = '';

if (isActionAccessible($guid, $connection2, '/modules/Messenger/messenger_post.php')) {
    if (isset($_SESSION[$guid]['username'])) {
        if (isset($_GET['pupilsightMessengerCannedResponseID'])) {
            $pupilsightMessengerCannedResponseID = $_GET['pupilsightMessengerCannedResponseID'];

            try {
                $data = array('pupilsightMessengerCannedResponseID' => $pupilsightMessengerCannedResponseID);
                $sql = 'SELECT body FROM pupilsightMessengerCannedResponse WHERE pupilsightMessengerCannedResponseID=:pupilsightMessengerCannedResponseID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
            }
            if ($result->rowCount() == 1) {
                $row = $result->fetch();
                $output .= $row['body'];
            }
        }
    }
}

echo $output;

<?php
/*
Pupilsight, Flexible & Open School System
*/

//Get variables
$key = '';
if (isset($_GET['key'])) {
    $key = $_GET['key'];
}
$pupilsightPersonID = '';
if (isset($_GET['pupilsightPersonID'])) {
    $pupilsightPersonID = $_GET['pupilsightPersonID'];
}
$pupilsightMessengerID = '';
if (isset($_GET['pupilsightMessengerID'])) {
    $pupilsightMessengerID = $_GET['pupilsightMessengerID'];
}

//Check variables
if ($key == '' or $pupilsightPersonID == '' or $pupilsightMessengerID == '') {
    echo "<div class='alert alert-danger'>";
    echo __('You have not specified one or more required parameters.');
    echo '</div>';
} else {
    //Check for record
    $keyReadFail = false;
    try {
        $dataKeyRead = array('key' => $key, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightMessengerID' => $pupilsightMessengerID, 'key' => $key);
        $sqlKeyRead = 'SELECT * FROM pupilsightMessengerReceipt WHERE `key`=:key AND pupilsightPersonID=:pupilsightPersonID AND pupilsightMessengerID=:pupilsightMessengerID';
        $resultKeyRead = $connection2->prepare($sqlKeyRead);
        $resultKeyRead->execute($dataKeyRead);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>";
        echo __('Your request failed due to a database error.');
        echo '</div>';
    }

    if ($resultKeyRead->rowCount() != 1) { //If not exists, report error
        echo "<div class='alert alert-danger'>";
        echo __('The selected record does not exist, or you do not have access to it.');
        echo '</div>';
    } else {    //If exists check confirmed
        $rowKeyRead = $resultKeyRead->fetch();

        if ($rowKeyRead['confirmed'] == 'Y') { //If already confirmed, report success
            echo "<div class='alert alert-sucess'>";
            echo __('Thank you for confirming receipt and reading of this email.');
            echo '</div>';
        } else { //If not confirmed, confirm
            $keyWriteFail = false;
            try {
                $dataKeyWrite = array('key' => $key, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightMessengerID' => $pupilsightMessengerID);
                $sqlKeyWrite = 'UPDATE pupilsightMessengerReceipt SET confirmed=\'Y\', confirmedTimestamp=now() WHERE `key`=:key AND pupilsightPersonID=:pupilsightPersonID AND pupilsightMessengerID=:pupilsightMessengerID';
                $resultKeyWrite = $connection2->prepare($sqlKeyWrite);
                $resultKeyWrite->execute($dataKeyWrite);
            } catch (PDOException $e) {
                print $e->getMessage();
                $keyWriteFail = true;
            }

            if ($keyWriteFail == true) { //Report error
                echo "<div class='alert alert-danger'>";
                echo __('Your request failed due to a database error.');
                echo '</div>';
            } else { //Report success
                echo "<div class='alert alert-sucess'>";
                echo __('Thank you for confirming receipt and reading of this email.');
                echo '</div>';
            }
        }
    }
}

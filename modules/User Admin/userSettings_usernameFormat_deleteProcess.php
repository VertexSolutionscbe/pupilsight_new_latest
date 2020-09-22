<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightUsernameFormatID = isset($_POST['pupilsightUsernameFormatID'])? $_POST['pupilsightUsernameFormatID'] : '';
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/userSettings.php&pupilsightUsernameFormatID='.$pupilsightUsernameFormatID;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/userSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    try {
        $data = array('pupilsightUsernameFormatID' => $pupilsightUsernameFormatID);
        $sql = "DELETE FROM pupilsightUsernameFormat WHERE pupilsightUsernameFormatID=:pupilsightUsernameFormatID";
        $result = $pdo->executeQuery($data, $sql);
    } catch (PDOException $e) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    //Success 0
    $URL .= '&return=success0';
    header("Location: {$URL}");
    exit;
}

<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightUsernameFormatID = isset($_POST['pupilsightUsernameFormatID'])? $_POST['pupilsightUsernameFormatID'] : '';
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/userSettings_usernameFormat_edit.php&pupilsightUsernameFormatID='.$pupilsightUsernameFormatID;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/userSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $format = isset($_POST['format'])? $_POST['format'] : '';
    $pupilsightRoleIDList = isset($_POST['pupilsightRoleIDList'])? $_POST['pupilsightRoleIDList'] : '';
    $isDefault = isset($_POST['isDefault'])? $_POST['isDefault'] : '';
    $isNumeric = isset($_POST['isNumeric'])? $_POST['isNumeric'] : '';
    $numericValue = isset($_POST['numericValue'])? $_POST['numericValue'] : 1;
    $numericSize = isset($_POST['numericSize'])? $_POST['numericSize'] : 4;
    $numericIncrement = isset($_POST['numericIncrement'])? $_POST['numericIncrement'] : 1;

    if (empty($format) || empty($pupilsightRoleIDList)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    } else {
        $pupilsightRoleIDList = implode(',', $pupilsightRoleIDList);

        try {
            $data = array('pupilsightUsernameFormatID' => $pupilsightUsernameFormatID, 'format' => $format, 'pupilsightRoleIDList' => $pupilsightRoleIDList, 'isDefault' => $isDefault, 'isNumeric' => $isNumeric, 'numericValue' => $numericValue, 'numericSize' => $numericSize, 'numericIncrement' => $numericIncrement);
            $sql = "UPDATE pupilsightUsernameFormat SET format=:format, pupilsightRoleIDList=:pupilsightRoleIDList, isDefault=:isDefault, isNumeric=:isNumeric, numericValue=:numericValue, numericSize=:numericSize, numericIncrement=:numericIncrement WHERE pupilsightUsernameFormatID=:pupilsightUsernameFormatID";
            $result = $pdo->executeQuery($data, $sql);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit;
        }

        // Update default
        if ($isDefault == 'Y') {
            $data = array('pupilsightUsernameFormatID' => $pupilsightUsernameFormatID);
            $sql = "UPDATE pupilsightUsernameFormat SET isDefault='N' WHERE pupilsightUsernameFormatID <> :pupilsightUsernameFormatID";
            $result = $pdo->executeQuery($data, $sql);
        }

        //Success 0
        $URL .= '&return=success0';
        header("Location: {$URL}");
        exit;
    }
}

<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Data\UsernameGenerator;

//Pupilsight system-wide include
include '../../pupilsight.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage_add.php') == false) {
    die( __('Your request failed because you do not have access to this action.') );
} else {
    $pupilsightRoleID = isset($_POST['pupilsightRoleID'])? $_POST['pupilsightRoleID'] : '003';
    $preferredName = isset($_POST['preferredName'])? $_POST['preferredName'] : '';
    $firstName = isset($_POST['firstName'])? $_POST['firstName'] : '';
    $surname = isset($_POST['surname'])? $_POST['surname'] : '';

    if (empty($pupilsightRoleID) || $pupilsightRoleID == 'Please select...' || empty($preferredName) || empty($firstName) || empty($surname)) {
        echo '0';
    } else {
        $generator = new UsernameGenerator($pdo);
        $generator->addToken('preferredName', $preferredName);
        $generator->addToken('firstName', $firstName);
        $generator->addToken('surname', $surname);

        echo $generator->generateByRole($pupilsightRoleID);
    }
}

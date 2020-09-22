<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Data\UsernameGenerator;

include './pupilsight.php';

$username = (isset($_POST['username']))? $_POST['username'] : '';
$currentUsername = (isset($_POST['currentUsername']))? $_POST['currentUsername'] : '';

if (!empty($currentUsername) && $currentUsername == $username) {
    echo '0';
} else if (!empty($username)) {
    $generator = new UsernameGenerator($pdo);
    echo $generator->isUsernameUnique($username)? '0' : '1';
}

$email = (isset($_POST['email']))? $_POST['email']: '';

if (!empty($email)) {
    $data = array('email' => $email);
    $sql = "SELECT COUNT(*) FROM pupilsightPerson WHERE email=:email";
    $result = $pdo->executeQuery($data, $sql);

    echo ($result && $result->rowCount() == 1)? $result->fetchColumn(0) : -1;
}

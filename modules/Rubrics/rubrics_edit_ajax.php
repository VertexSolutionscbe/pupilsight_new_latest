<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

//Open Database connection
if (!($connection = mysql_connect($databaseServer, $databaseUsername, $databasePassword))) {
    showError();
}

//Select database
if (!(mysql_select_db($databaseName, $connection))) {
    showError();
}

mysql_set_charset('utf8');

$pupilsightRubricID = $_GET['pupilsightRubricID'];
echo rubricEdit($guid, $connection, $pupilsightRubricID);

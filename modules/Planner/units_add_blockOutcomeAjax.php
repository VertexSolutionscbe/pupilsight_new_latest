<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

$page = $container->get('page');

$id = $_GET['id'];
$type = $_GET['type'];
$pupilsightOutcomeID = $_GET['pupilsightOutcomeID'];
$title = $_GET['title'];
$category = $_GET['category'];
$contents = $_GET['contents'];
$allowOutcomeEditing = $_GET['allowOutcomeEditing'];

makeBlockOutcome($guid,  $id, $type, $pupilsightOutcomeID, $title, $category, $contents, '', false, $allowOutcomeEditing);

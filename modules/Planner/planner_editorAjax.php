<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
include './moduleFunctions.php';

$page = $container->get('page');

$id = $_POST['id'];
$value = $_POST['value'];
$showMedia = $_POST['showMedia'] ?? false;
$rows = $_POST['rows'] ?? 10;

echo getEditor($guid, false, $id, $value, $rows, $showMedia, false, false, $showMedia, '', false);

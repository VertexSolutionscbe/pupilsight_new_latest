<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

$id = $_GET['id'];
$mode = $_GET['mode'];
$feeType = $_GET['feeType'];
$pupilsightFinanceFeeID = $_GET['pupilsightFinanceFeeID'];
$name = $_GET['name'];
$description = $_GET['description'];
$pupilsightFinanceFeeCategoryID = $_GET['pupilsightFinanceFeeCategoryID'];
$fee = $_GET['fee'];
$category = null;
if (isset($_GET['category'])) {
    $category = $_GET['category'];
}

makeFeeBlock($guid, $connection2, $id, $mode, $feeType, $pupilsightFinanceFeeID, $name, $description, $pupilsightFinanceFeeCategoryID, $fee, $category);

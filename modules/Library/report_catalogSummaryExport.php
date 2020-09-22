<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address']).'/report_catalogSummary.php';

if (isActionAccessible($guid, $connection2, '/modules/Library/report_catalogSummary.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $naownershipTypeme = trim($_GET['ownershipType']);
    $pupilsightLibraryTypeID = trim($_GET['pupilsightLibraryTypeID']);
    $pupilsightSpaceID = trim($_GET['pupilsightSpaceID']);
    $status = trim($_GET['status']);

    $ownershipType = null;
    if (isset($_GET['ownershipType'])) {
        $ownershipType = trim($_GET['ownershipType']);
    }
    $pupilsightLibraryTypeID = null;
    if (isset($_GET['pupilsightLibraryTypeID'])) {
        $pupilsightLibraryTypeID = trim($_GET['pupilsightLibraryTypeID']);
    }
    $pupilsightSpaceID = null;
    if (isset($_GET['pupilsightSpaceID'])) {
        $pupilsightSpaceID = trim($_GET['pupilsightSpaceID']);
    }
    $status = null;
    if (isset($_GET['status'])) {
        $status = trim($_GET['status']);
    }

    try {
        $data = array();
        $sqlWhere = 'WHERE ';
        if ($ownershipType != '') {
            $data['ownershipType'] = $ownershipType;
            $sqlWhere .= 'ownershipType=:ownershipType AND ';
        }
        if ($pupilsightLibraryTypeID != '') {
            $data['pupilsightLibraryTypeID'] = $pupilsightLibraryTypeID;
            $sqlWhere .= 'pupilsightLibraryTypeID=:pupilsightLibraryTypeID AND ';
        }
        if ($pupilsightSpaceID != '') {
            $data['pupilsightSpaceID'] = $pupilsightSpaceID;
            $sqlWhere .= 'pupilsightSpaceID=:pupilsightSpaceID AND ';
        }
        if ($status != '') {
            $data['status'] = $status;
            $sqlWhere .= 'status=:status AND ';
        }
        if ($sqlWhere == 'WHERE ') {
            $sqlWhere = '';
        } else {
            $sqlWhere = substr($sqlWhere, 0, -5);
        }
        $sql = "SELECT * FROM pupilsightLibraryItem $sqlWhere ORDER BY id";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    if ($result->rowCount() < 1) {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Proceed!
		include './report_catalogSummaryExportContents.php';
    }
}

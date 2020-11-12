<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\System\CustomField;

include '../../pupilsight.php';

$allStaff = '';
if (isset($_GET['allStaff'])) {
    $allStaff = $_GET['allStaff'];
}
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}
$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . getModuleName($_POST['address']) . "/staff_manage_add.php&search=$search&allStaff=$allStaff";

if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $pupilsightPersonID = $_POST['pupilsightPersonID'];
    $initials = $_POST['initials'];
    if ($initials == '') {
        $initials = null;
    }
    $type = $_POST['type'];
    $jobTitle = $_POST['jobTitle'];
    $firstAidQualified = $_POST['firstAidQualified'];
    $firstAidExpiry = null;
    if ($firstAidQualified == 'Y' and $_POST['firstAidExpiry'] != '') {
        $firstAidExpiry = dateConvert($guid, $_POST['firstAidExpiry']);
    }
    $countryOfOrigin = $_POST['countryOfOrigin'];
    $qualifications = $_POST['qualifications'];
    $biographicalGrouping = $_POST['biographicalGrouping'];
    $biographicalGroupingPriority = $_POST['biographicalGroupingPriority'];
    $biography = $_POST['biography'];
    $is_principle = $_POST['is_principle'];
    if (!empty($is_principle)) {
        $is_principle = $is_principle;

        $allotherprinciple = '0';
        $datau = array('is_principle' => $allotherprinciple);
        $sqlu = 'UPDATE pupilsightStaff SET is_principle=:is_principle';
        $resultu = $connection2->prepare($sqlu);
        $resultu->execute($datau);
    } else {
        $is_principle = '';
    }

    //Validate Inputs
    if ($pupilsightPersonID == '' or $type == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            if ($initials == '') {
                $data = array('pupilsightPersonID' => $pupilsightPersonID);
                $sql = 'SELECT * FROM pupilsightStaff WHERE pupilsightPersonID=:pupilsightPersonID';
            } else {
                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'initials' => $initials);
                $sql = 'SELECT * FROM pupilsightStaff WHERE pupilsightPersonID=:pupilsightPersonID OR initials=:initials';
            }
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() > 0) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {

                if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
                    $filename = $_FILES["file"]["name"];
                    $filetype = $_FILES["file"]["type"];
                    $filesize = $_FILES["file"]["size"];

                    // Verify file extension
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);


                    $filename = time() . '_' . $_FILES["file"]["name"];
                    $fileTarget = $_SERVER['DOCUMENT_ROOT'] . "/public/staff_signature/" . $filename;
                    if (move_uploaded_file($_FILES["file"]["tmp_name"], $fileTarget)) {
                        echo "Signature updated successfully";
                    } else {
                        echo "No";
                    }
                } else {
                    // echo "Error: " . $_FILES["file"]["error"];
                    $fileTarget = '';
                }

                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'initials' => $initials, 'type' => $type, 'jobTitle' => $jobTitle, 'firstAidQualified' => $firstAidQualified, 'firstAidExpiry' => $firstAidExpiry, 'countryOfOrigin' => $countryOfOrigin, 'qualifications' => $qualifications, 'biographicalGrouping' => $biographicalGrouping, 'biographicalGroupingPriority' => $biographicalGroupingPriority, 'biography' => $biography, 'is_principle' => $is_principle, 'signature_path' => $fileTarget);
                $sql = 'INSERT INTO pupilsightStaff SET pupilsightPersonID=:pupilsightPersonID, initials=:initials, type=:type, jobTitle=:jobTitle, firstAidQualified=:firstAidQualified, firstAidExpiry=:firstAidExpiry, countryOfOrigin=:countryOfOrigin, qualifications=:qualifications, biographicalGrouping=:biographicalGrouping, biographicalGroupingPriority=:biographicalGroupingPriority, biography=:biography, is_principle=:is_principle, signature_path=:signature_path';
                $result = $connection2->prepare($sql);
                $result->execute($data);

                $staffId = $connection2->lastInsertID();
                //custom Field Added
                $customField  = $container->get(CustomField::class);
                $customField->postCustomField($_POST["custom"], 'pupilsightPersonID', $staffId);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($staffId, 10, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}

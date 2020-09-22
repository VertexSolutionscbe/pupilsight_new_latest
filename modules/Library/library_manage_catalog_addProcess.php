<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/library_manage_catalog_add.php&name='.$_GET['name'].'&pupilsightLibraryTypeID='.$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status'].'&pupilsightPersonIDOwnership='.$_GET['pupilsightPersonIDOwnership'].'&typeSpecificFields='.$_GET['typeSpecificFields'];

if (isActionAccessible($guid, $connection2, '/modules/Library/library_manage_catalog_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Get general fields
    $pupilsightLibraryTypeID = $_POST['pupilsightLibraryTypeID'];
    $id = $_POST['idCheck'];
    $name = $_POST['name'];
    $producer = $_POST['producer'];
    $vendor = $_POST['vendor'];
    $purchaseDate = null;
    if ($_POST['purchaseDate'] != '') {
        $purchaseDate = dateConvert($guid, $_POST['purchaseDate']);
    }
    $invoiceNumber = $_POST['invoiceNumber'];
    $imageType = $_POST['imageType'];
    if ($imageType == 'Link') {
        $imageLocation = $_POST['imageLink'];
    } else {
        $imageLocation = '';
    }
    $replacement = $_POST['replacement'];
    $pupilsightSchoolYearIDReplacement = null;
    $replacementCost = null;
    if ($replacement == 'Y') {
        if ($_POST['pupilsightSchoolYearIDReplacement'] != '') {
            $pupilsightSchoolYearIDReplacement = $_POST['pupilsightSchoolYearIDReplacement'];
        }
        if ($_POST['replacementCost'] != '') {
            $replacementCost = $_POST['replacementCost'];
        }
    } else {
        $replacement == 'N';
    }
    $comment = $_POST['comment'];
    $pupilsightSpaceID = null;
    if ($_POST['pupilsightSpaceID'] != '') {
        $pupilsightSpaceID = $_POST['pupilsightSpaceID'];
    }
    $locationDetail = $_POST['locationDetail'];
    $ownershipType = $_POST['ownershipType'];
    $pupilsightPersonIDOwnership = null;
    if ($ownershipType == 'School' and $_POST['pupilsightPersonIDOwnershipSchool'] != '') {
        $pupilsightPersonIDOwnership = $_POST['pupilsightPersonIDOwnershipSchool'];
    } elseif ($ownershipType == 'Individual' and $_POST['pupilsightPersonIDOwnershipIndividual'] != '') {
        $pupilsightPersonIDOwnership = $_POST['pupilsightPersonIDOwnershipIndividual'];
    }
    $pupilsightDepartmentID = null;
    if ($_POST['pupilsightDepartmentID'] != '') {
        $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
    }
    $bookable = $_POST['bookable'];
    $borrowable = $_POST['borrowable'];
    $status = $_POST['status'];
    $physicalCondition = $_POST['physicalCondition'];

    //Get type-specific fields
    try {
        $data = array('pupilsightLibraryTypeID' => $pupilsightLibraryTypeID);
        $sql = "SELECT * FROM pupilsightLibraryType WHERE pupilsightLibraryTypeID=:pupilsightLibraryTypeID AND active='Y' ORDER BY name";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    if ($result->rowCount() == 1) {
        $row = $result->fetch();
        $fieldsIn = unserialize($row['fields']);
        $fieldsOut = array();
        foreach ($fieldsIn as $field) {
            $fieldName = preg_replace('/ |\(|\)/', '', $field['name']);
            if ($field['type'] == 'Date') {
                $fieldsOut[$field['name']] = dateConvert($guid, $_POST['field'.$fieldName]);
            } else {
                $fieldsOut[$field['name']] = $_POST['field'.$fieldName];
            }
        }
    }

    if ($pupilsightLibraryTypeID == '' or $name == '' or $id == '' or $producer == '' or $bookable == '' or $borrowable == ''  or $status == '' or $replacement == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $dataUnique = array('id' => $id);
            $sqlUnique = 'SELECT * FROM pupilsightLibraryItem WHERE id=:id';
            $resultUnique = $connection2->prepare($sqlUnique);
            $resultUnique->execute($dataUnique);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($resultUnique->rowCount() > 0) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            $partialFail = false;

            //Move attached image  file, if there is one
            if (!empty($_FILES['imageFile']['tmp_name']) && $imageType == 'File') {
                $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);
                $fileUploader->getFileExtensions('Graphics/Design');

                $file = (isset($_FILES['imageFile']))? $_FILES['imageFile'] : null;

                // Upload the file, return the /uploads relative path
                $imageLocation = $fileUploader->uploadFromPost($file, $id);

                if (empty($imageLocation)) {
                    $partialFail = true;
                }
            }

            //Write to database
            try {
                $data = array('pupilsightLibraryTypeID' => $pupilsightLibraryTypeID, 'id' => $id, 'name' => $name, 'producer' => $producer, 'fields' => serialize($fieldsOut), 'vendor' => $vendor, 'purchaseDate' => $purchaseDate, 'invoiceNumber' => $invoiceNumber, 'imageType' => $imageType, 'imageLocation' => $imageLocation, 'replacement' => $replacement, 'pupilsightSchoolYearIDReplacement' => $pupilsightSchoolYearIDReplacement, 'replacementCost' => $replacementCost, 'comment' => $comment, 'pupilsightSpaceID' => $pupilsightSpaceID, 'locationDetail' => $locationDetail, 'ownershipType' => $ownershipType, 'pupilsightPersonIDOwnership' => $pupilsightPersonIDOwnership, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'bookable' => $bookable, 'borrowable' => $borrowable, 'status' => $status, 'physicalCondition' => $physicalCondition, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID'], 'timestampCreator' => date('Y-m-d H:i:s', time()));
                $sql = 'INSERT INTO pupilsightLibraryItem SET pupilsightLibraryTypeID=:pupilsightLibraryTypeID, id=:id, name=:name, producer=:producer, fields=:fields, vendor=:vendor, purchaseDate=:purchaseDate, invoiceNumber=:invoiceNumber, imageType=:imageType, imageLocation=:imageLocation, replacement=:replacement, pupilsightSchoolYearIDReplacement=:pupilsightSchoolYearIDReplacement, replacementCost=:replacementCost, comment=:comment, pupilsightSpaceID=:pupilsightSpaceID, locationDetail=:locationDetail, ownershipType=:ownershipType, pupilsightPersonIDOwnership=:pupilsightPersonIDOwnership, pupilsightDepartmentID=:pupilsightDepartmentID, bookable=:bookable, borrowable=:borrowable, status=:status, physicalCondition=:physicalCondition, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, timestampCreator=:timestampCreator';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 10, '0', STR_PAD_LEFT);

            if ($partialFail == true) {
                $URL .= '&return=warning1';
                header("Location: {$URL}");
            } else {
                $URL .= "&return=success0&editID=$AI";
                header("Location: {$URL}");
            }
        }
    }
}

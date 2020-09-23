<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

$pupilsightLibraryItemID = $_POST['pupilsightLibraryItemID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/library_manage_catalog_duplicate.php&pupilsightLibraryItemID=$pupilsightLibraryItemID&name=".$_GET['name'].'&pupilsightLibraryTypeID='.$_GET['pupilsightLibraryTypeID'].'&pupilsightSpaceID='.$_GET['pupilsightSpaceID'].'&status='.$_GET['status'].'&pupilsightPersonIDOwnership='.$_GET['pupilsightPersonIDOwnership'].'&typeSpecificFields='.$_GET['typeSpecificFields'];

if (isActionAccessible($guid, $connection2, '/modules/Library/library_manage_catalog_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightLibraryItemID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightLibraryItemID' => $pupilsightLibraryItemID);
            $sql = 'SELECT * FROM pupilsightLibraryItem WHERE pupilsightLibraryItemID=:pupilsightLibraryItemID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            $row = $result->fetch();

            $status = 'Available';
            $imageType = $row['imageType'];
            $imageLocation = $row['imageLocation'];
            $pupilsightLibraryTypeID = $row['pupilsightLibraryTypeID'];
            $name = $row['name'];
            $producer = $row['producer'];
            $vendor = $row['vendor'];
            $purchaseDate = $row['purchaseDate'];
            $invoiceNumber = $row['invoiceNumber'];
            $replacement = $row['replacement'];
            $pupilsightSchoolYearIDReplacement = $row['pupilsightSchoolYearIDReplacement'];
            $replacementCost = $row['replacementCost'];
            $comment = $row['comment'];
            $pupilsightSpaceID = $row['pupilsightSpaceID'];
            $locationDetail = $row['locationDetail'];
            $ownershipType = $row['ownershipType'];
            $pupilsightPersonIDOwnership = $row['pupilsightPersonIDOwnership'];
            $pupilsightDepartmentID = $row['pupilsightDepartmentID'];
            $borrowable = $row['borrowable'];
            $bookable = $row['bookable'];
            $fields = $row['fields'];
            $count = $_POST['count'];

            if ($pupilsightLibraryTypeID == '' or $name == '' or $producer == '' or $borrowable == '' or $count == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            }
            else {
                $partialFail = false;

                for ($i = 1; $i <= $count; ++$i) {
                    $id = $_POST['id'.$i];

                    if ($id == '') {
                        $partialFail = true;
                    }
                    else {
                        //Check unique inputs for uniquness
                        try {
                            $dataUnique = array('id' => $id);
                            $sqlUnique = 'SELECT * FROM pupilsightLibraryItem WHERE id=:id';
                            $resultUnique = $connection2->prepare($sqlUnique);
                            $resultUnique->execute($dataUnique);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }

                        if ($resultUnique->rowCount() > 0) {
                            $partialFail = true;
                        } else {
                            //Write to database
                            try {
                                $data = array('pupilsightLibraryTypeID' => $pupilsightLibraryTypeID, 'id' => $id, 'name' => $name, 'producer' => $producer, 'fields' => $fields, 'vendor' => $vendor, 'purchaseDate' => $purchaseDate, 'invoiceNumber' => $invoiceNumber, 'imageType' => $imageType, 'imageLocation' => $imageLocation, 'replacement' => $replacement, 'pupilsightSchoolYearIDReplacement' => $pupilsightSchoolYearIDReplacement, 'replacementCost' => $replacementCost, 'comment' => $comment, 'pupilsightSpaceID' => $pupilsightSpaceID, 'locationDetail' => $locationDetail, 'ownershipType' => $ownershipType, 'pupilsightPersonIDOwnership' => $pupilsightPersonIDOwnership, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'borrowable' => $borrowable, 'bookable' => $bookable, 'status' => $status, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID'], 'timestampCreator' => date('Y-m-d H:i:s', time()));
                                $sql = 'INSERT INTO pupilsightLibraryItem SET pupilsightLibraryTypeID=:pupilsightLibraryTypeID, id=:id, name=:name, producer=:producer, fields=:fields, vendor=:vendor, purchaseDate=:purchaseDate, invoiceNumber=:invoiceNumber, imageType=:imageType, imageLocation=:imageLocation, replacement=:replacement, pupilsightSchoolYearIDReplacement=:pupilsightSchoolYearIDReplacement, replacementCost=:replacementCost, comment=:comment, pupilsightSpaceID=:pupilsightSpaceID, locationDetail=:locationDetail, ownershipType=:ownershipType, pupilsightPersonIDOwnership=:pupilsightPersonIDOwnership, pupilsightDepartmentID=:pupilsightDepartmentID, borrowable=:borrowable, bookable=:bookable, status=:status, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, timestampCreator=:timestampCreator';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                                $failCode = $e->getMessage();
                            }
                        }
                    }
                }
            }

            if ($partialFail == true) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}

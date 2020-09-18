<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Domain\System\CustomField;

include '../../pupilsight.php';

$pupilsightStaffID = $_GET['pupilsightStaffID'];
$allStaff = '';
if (isset($_GET['allStaff'])) {
    $allStaff = $_GET['allStaff'];
}
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}
// $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/staff_manage_edit.php&pupilsightStaffID=$pupilsightStaffID&search=$search&allStaff=$allStaff";

if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightStaffID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightStaffID' => $pupilsightStaffID);
            $sql = 'SELECT * FROM pupilsightStaff WHERE pupilsightStaffID=:pupilsightStaffID';
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
            //custom Field Added
            $customField  = $container->get(CustomField::class);
            $customField->postCustomField($_POST["custom"], 'pupilsightPersonID', $pupilsightPersonID);
        
            //Validate Inputs
            $initials = $_POST['initials'];
            if ($initials == '') {
                $initials = null;
            }
            $type = $_POST['type'];
            $jobTitle = $_POST['jobTitle'];
            $dateStart = $_POST['dateStart'];
            if ($dateStart == '') {
                $dateStart = null;
            } else {
                $dateStart = dateConvert($guid, $dateStart);
            }
            $dateEnd = $_POST['dateEnd'];
            if ($dateEnd == '') {
                $dateEnd = null;
            } else {
                $dateEnd = dateConvert($guid, $dateEnd);
            }
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
            $signature_path = $_POST['signature_path'];

            if(!empty($is_principle)){
                $is_principle = $is_principle;

                $allotherprinciple = '0';
                $datau = array('is_principle' => $allotherprinciple);
                $sqlu = 'UPDATE pupilsightStaff SET is_principle=:is_principle';
                $resultu = $connection2->prepare($sqlu);
                $resultu->execute($datau);
            } else {
                $is_principle = '';
            }

            if ($type == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('pupilsightStaffID' => $pupilsightStaffID, 'initials' => $initials);
                    $sql = "SELECT * FROM pupilsightStaff WHERE initials=:initials AND NOT pupilsightStaffID=:pupilsightStaffID AND NOT initials=''";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                }

                if ($result->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {

                        if(isset($_FILES["file"]) && $_FILES["file"]["error"] == 0){
                            $filename = $_FILES["file"]["name"];
                            $filetype = $_FILES["file"]["type"];
                            $filesize = $_FILES["file"]["size"];
                           
                            // Verify file extension
                            $ext = pathinfo($filename, PATHINFO_EXTENSION);
                            
        
                            $filename = time() . '_' .$_FILES["file"]["name"];
                            $fileTarget = $_SERVER['DOCUMENT_ROOT']."/public/staff_signature/" . $filename;	
                            if(move_uploaded_file($_FILES["file"]["tmp_name"], $fileTarget)){
                                echo "Signature updated successfully";
                            } else {
                                    echo "No";
                            }
                        } else{
                            // echo "Error: " . $_FILES["file"]["error"];
                            $fileTarget = $signature_path;
                        }


                        $data = array('initials' => $initials, 'type' => $type, 'jobTitle' => $jobTitle, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'firstAidQualified' => $firstAidQualified, 'firstAidExpiry' => $firstAidExpiry, 'countryOfOrigin' => $countryOfOrigin, 'qualifications' => $qualifications, 'biographicalGrouping' => $biographicalGrouping, 'biographicalGroupingPriority' => $biographicalGroupingPriority, 'biography' => $biography, 'is_principle' => $is_principle, 'signature_path' => $fileTarget,  'pupilsightStaffID' => $pupilsightStaffID);
                        $sql = 'UPDATE pupilsightStaff JOIN pupilsightPerson ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) SET initials=:initials, type=:type, pupilsightStaff.jobTitle=:jobTitle, dateStart=:dateStart, dateEnd=:dateEnd, firstAidQualified=:firstAidQualified, firstAidExpiry=:firstAidExpiry, countryOfOrigin=:countryOfOrigin, qualifications=:qualifications, biographicalGrouping=:biographicalGrouping, biographicalGroupingPriority=:biographicalGroupingPriority, biography=:biography, is_principle=:is_principle, signature_path=:signature_path WHERE pupilsightStaffID=:pupilsightStaffID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $pupilsightPersonID = $_POST['pupilsightPersonID'];

                    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/staff_manage_edit.php&pupilsightPersonID=$pupilsightPersonID&search=$search&allStaff=$allStaff";

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}

<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightStaffID = $_GET['pupilsightStaffID'];
$pupilsightStaffContractID = $_GET['pupilsightStaffContractID'];
$search = $_GET['search'];
if ($pupilsightStaffID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/staff_manage_edit_contract_edit.php&pupilsightStaffContractID=$pupilsightStaffContractID&pupilsightStaffID=$pupilsightStaffID&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_manage_edit_contract_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if person specified
        if ($pupilsightStaffContractID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightStaffID' => $pupilsightStaffID, 'pupilsightStaffContractID' => $pupilsightStaffContractID);
                $sql = 'SELECT pupilsightStaffContract.*, surname, preferredName, username FROM pupilsightStaffContract JOIN pupilsightStaff ON (pupilsightStaffContract.pupilsightStaffID=pupilsightStaff.pupilsightStaffID) JOIN pupilsightPerson ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightStaffContract.pupilsightStaffID=:pupilsightStaffID AND pupilsightStaffContractID=:pupilsightStaffContractID';
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
                $username = $row['username'];

                $title = $_POST['title'];
                $status = $_POST['status'];
                $dateStart = null;
                if (isset($_POST['dateStart'])) {
                    $dateStart = dateConvert($guid, $_POST['dateStart']);
                }
                $dateEnd = null;
                if (isset($_POST['dateEnd'])) {
                    if ($_POST['dateEnd'] != '') {
                        $dateEnd = dateConvert($guid, $_POST['dateEnd']);
                    }
                }
                $salaryScale = null;
                if (isset($_POST['salaryScale'])) {
                    $salaryScale = $_POST['salaryScale'];
                }
                $salaryAmount = null;
                if (isset($_POST['salaryAmount']) && $_POST['salaryAmount'] != '') {
                    $salaryAmount = $_POST['salaryAmount'];
                }
                $salaryPeriod = null;
                if (isset($_POST['salaryPeriod'])) {
                    $salaryPeriod = $_POST['salaryPeriod'];
                }
                $responsibility = null;
                if (isset($_POST['responsibility'])) {
                    $responsibility = $_POST['responsibility'];
                }
                $responsibilityAmount = null;
                if (isset($_POST['responsibilityAmount']) && $_POST['responsibilityAmount'] != '') {
                    $responsibilityAmount = $_POST['responsibilityAmount'];
                }
                $responsibilityPeriod = null;
                if (isset($_POST['responsibilityPeriod'])) {
                    $responsibilityPeriod = $_POST['responsibilityPeriod'];
                }
                $housingAmount = null;
                if (isset($_POST['housingAmount']) && $_POST['housingAmount'] != '') {
                    $housingAmount = $_POST['housingAmount'];
                }
                $housingPeriod = null;
                if (isset($_POST['housingPeriod'])) {
                    $housingPeriod = $_POST['housingPeriod'];
                }
                $travelAmount = null;
                if (isset($_POST['travelAmount']) && $_POST['travelAmount'] != '') {
                    $travelAmount = $_POST['travelAmount'];
                }
                $travelPeriod = null;
                if (isset($_POST['travelPeriod'])) {
                    $travelPeriod = $_POST['travelPeriod'];
                }
                $retirementAmount = null;
                if (isset($_POST['retirementAmount']) && $_POST['retirementAmount'] != '') {
                    $retirementAmount = $_POST['retirementAmount'];
                }
                $retirementPeriod = null;
                if (isset($_POST['retirementPeriod'])) {
                    $retirementPeriod = $_POST['retirementPeriod'];
                }
                $bonusAmount = null;
                if (isset($_POST['bonusAmount']) && $_POST['bonusAmount'] != '') {
                    $bonusAmount = $_POST['bonusAmount'];
                }
                $bonusPeriod = null;
                if (isset($_POST['bonusPeriod'])) {
                    $bonusPeriod = $_POST['bonusPeriod'];
                }
                $education = null;
                if (isset($_POST['education'])) {
                    $education = $_POST['education'];
                }
                $notes = null;
                if (isset($_POST['notes'])) {
                    $notes = $_POST['notes'];
                }
                $contractUpload = isset($_POST['contractUpload'])? $_POST['contractUpload'] : $row['contractUpload'];

                $partialFail = false;
                if (!empty($_FILES['file1']['tmp_name'])) {
                    $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);
                    $fileUploader->getFileExtensions('Document');

                    $file = (isset($_FILES['file1']))? $_FILES['file1'] : null;

                    // Upload the file, return the /uploads relative path
                    $contractUpload = $fileUploader->uploadFromPost($file, $username);

                    if (empty($contractUpload)) {
                        $contractUpload = '';
                        $partialFail = true;
                    }
                }

                if ($title == '' or $status == '') {
                    $URL .= '&return=error1&step=1';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightStaffID' => $pupilsightStaffID, 'title' => $title, 'status' => $status, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'salaryScale' => $salaryScale, 'salaryAmount' => $salaryAmount, 'salaryPeriod' => $salaryPeriod, 'responsibility' => $responsibility, 'responsibilityAmount' => $responsibilityAmount, 'responsibilityPeriod' => $responsibilityPeriod, 'housingAmount' => $housingAmount, 'housingPeriod' => $housingPeriod, 'travelAmount' => $travelAmount, 'travelPeriod' => $travelPeriod, 'retirementAmount' => $retirementAmount, 'retirementPeriod' => $retirementPeriod, 'bonusAmount' => $bonusAmount, 'bonusPeriod' => $bonusPeriod, 'education' => $education, 'notes' => $notes, 'contractUpload' => $contractUpload, 'pupilsightStaffContractID' => $pupilsightStaffContractID);
                        $sql = 'UPDATE pupilsightStaffContract SET pupilsightStaffID=:pupilsightStaffID, title=:title, status=:status, dateStart=:dateStart, dateEnd=:dateEnd, salaryScale=:salaryScale, salaryAmount=:salaryAmount, salaryPeriod=:salaryPeriod, responsibility=:responsibility, responsibilityAmount=:responsibilityAmount, responsibilityPeriod=:responsibilityPeriod, housingAmount=:housingAmount, housingPeriod=:housingPeriod, travelAmount=:travelAmount, travelPeriod=:travelPeriod, retirementAmount=:retirementAmount, retirementPeriod=:retirementPeriod, bonusAmount=:bonusAmount, bonusPeriod=:bonusPeriod, education=:education, notes=:notes, contractUpload=:contractUpload WHERE pupilsightStaffContractID=:pupilsightStaffContractID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    if ($partialFail == true) {
                        $URL .= '&return=warning1';
                        header("Location: {$URL}");
                    } else {
                        $URL .= "&return=success0";
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}

<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightDepartmentID = $_GET['pupilsightDepartmentID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/department_manage_edit.php&pupilsightDepartmentID=$pupilsightDepartmentID";

if (isActionAccessible($guid, $connection2, '/modules/Academics/department_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit();
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightDepartmentID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit();
    } else {
        try {
            $data = array('pupilsightDepartmentID' => $pupilsightDepartmentID);
            $sql = 'SELECT * FROM pupilsightDepartment WHERE pupilsightDepartmentID=:pupilsightDepartmentID';
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
            exit();
        } else {
            $row = $result->fetch();
            //Validate Inputs
            $type = $_POST['type'];
            $name = $_POST['name'];
            $nameShort = $_POST['nameShort'];
            // $subjectListing = $_POST['subjectListing'];
            // $blurb = $_POST['blurb'];
            $subjectListing = '';
            $blurb = '';

            if ($name == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
                exit();
            } else {
                $partialFail = false;
                
                //Move attached file, if there is one
                if (!empty($_FILES['file']['tmp_name'])) {
                    $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);
            
                    $file = (isset($_FILES['file']))? $_FILES['file'] : null;

                    // Upload the file, return the /uploads relative path
                    $attachment = $fileUploader->uploadFromPost($file, $name);

                    if (empty($attachment)) {
                        $partialFail = true;
                    }
                } else {
                    $attachment = $_POST['logo'];
                }

                //Scan through staff
                $staff = array();
                if (isset($_POST['staff'])) {
                    $staff = $_POST['staff'];
                }
                $role = $_POST['role'];
                if ($role == '') {
                    $role = 'Other';
                }
                if (count($staff) > 0) {
                    foreach ($staff as $t) {
                        //Check to see if person is already registered in this activity
                        try {
                            $dataGuest = array('pupilsightPersonID' => $t, 'pupilsightDepartmentID' => $pupilsightDepartmentID);
                            $sqlGuest = 'SELECT * FROM pupilsightDepartmentStaff WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightDepartmentID=:pupilsightDepartmentID';
                            $resultGuest = $connection2->prepare($sqlGuest);
                            $resultGuest->execute($dataGuest);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                        if ($resultGuest->rowCount() == 0) {
                            try {
                                $data = array('pupilsightPersonID' => $t, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'role' => $role);
                                $sql = 'INSERT INTO pupilsightDepartmentStaff SET pupilsightPersonID=:pupilsightPersonID, pupilsightDepartmentID=:pupilsightDepartmentID, role=:role';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }
                }

                //Write to database
                try {
                    $data = array('type' => $type, 'name' => $name, 'nameShort' => $nameShort, 'subjectListing' => $subjectListing, 'blurb' => $blurb, 'logo' => $attachment, 'pupilsightDepartmentID' => $pupilsightDepartmentID);
                    $sql = 'UPDATE pupilsightDepartment SET type=:type, name=:name, nameShort=:nameShort, subjectListing=:subjectListing, blurb=:blurb, logo=:logo WHERE pupilsightDepartmentID=:pupilsightDepartmentID';
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
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}

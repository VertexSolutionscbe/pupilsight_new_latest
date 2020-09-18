<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightDepartmentID = $_GET['pupilsightDepartmentID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/department_edit.php&pupilsightDepartmentID=$pupilsightDepartmentID";

if (isActionAccessible($guid, $connection2, '/modules/Departments/department_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    if (empty($_POST)) {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Validate Inputs
        $blurb = $_POST['blurb'];

        if ($pupilsightDepartmentID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //Check access to specified course
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
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                //Get role within learning area
                $role = getRole($_SESSION[$guid]['pupilsightPersonID'], $pupilsightDepartmentID, $connection2);

                if ($role != 'Coordinator' and $role != 'Assistant Coordinator' and $role != 'Teacher (Curriculum)' and $role != 'Director' and $role != 'Manager') {
                    $URL .= '&return=error0';
                    header("Location: {$URL}");
                } else {
                    //Scan through resources
                    $partialFail = false;
                    for ($i = 1; $i < 4; ++$i) {
                        $resourceName =isset( $_POST["name$i"])? $_POST["name$i"] : '';
                        $resourceType = isset($_POST["type$i"])? $_POST["type$i"] : '';
                        $resourceURL = isset($_POST["url$i"])? $_POST["url$i"] : '';

                        if ($resourceName != '' and $resourceType != '' and ($resourceType == 'File' or $resourceType == 'Link')) {
                            if (($resourceType == 'Link' and $resourceURL != '') or ($resourceType == 'File' and !empty($_FILES['file'.$i]['tmp_name']))) {
                                if ($resourceType == 'Link') {
                                    try {
                                        $data = array('pupilsightDepartmentID' => $pupilsightDepartmentID, 'resourceType' => $resourceType, 'resourceName' => $resourceName, 'resourceURL' => $resourceURL);
                                        $sql = 'INSERT INTO pupilsightDepartmentResource SET pupilsightDepartmentID=:pupilsightDepartmentID, type=:resourceType, name=:resourceName, url=:resourceURL';
                                        $result = $connection2->prepare($sql);
                                        $result->execute($data);
                                    } catch (PDOException $e) {
                                        $partialFail = true;
                                    }
                                } elseif ($resourceType == 'File') {
                                    $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);

                                    // Handle the attached file, if there is one
                                    if (!empty($_FILES['file'.$i]['tmp_name'])) {
                                        $file = (isset($_FILES['file'.$i]))? $_FILES['file'.$i] : null;

                                        // Upload the file, return the /uploads relative path
                                        $attachment = $fileUploader->uploadFromPost($file, $resourceName);

                                        if (empty($attachment)) {
                                            $URL .= '&return=warning1';
                                            header("Location: {$URL}");
                                            exit();
                                        } else {
                                            try {
                                                $data = array('pupilsightDepartmentID' => $pupilsightDepartmentID, 'resourceType' => $resourceType, 'resourceName' => $resourceName, 'attachment' => $attachment);
                                                $sql = 'INSERT INTO pupilsightDepartmentResource SET pupilsightDepartmentID=:pupilsightDepartmentID, type=:resourceType, name=:resourceName, url=:attachment';
                                                $result = $connection2->prepare($sql);
                                                $result->execute($data);
                                            } catch (PDOException $e) {
                                                $partialFail = true;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    //Write to database
                    try {
                        $data = array('blurb' => $blurb, 'pupilsightDepartmentID' => $pupilsightDepartmentID);
                        $sql = 'UPDATE pupilsightDepartment SET blurb=:blurb WHERE pupilsightDepartmentID=:pupilsightDepartmentID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    if ($partialFail == true) {
                        $URL .= '&return=error3';
                        header("Location: {$URL}");
                    } else {
                        $URL .= '&return=success0';
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}

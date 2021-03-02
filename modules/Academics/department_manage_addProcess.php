<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address']).'/department_manage_add.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/department_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit();
} else {
    //Proceed!
    $type = $_POST['type'];
    $name = $_POST['name'];
    $nameShort = $_POST['nameShort'];
    // $subjectListing = $_POST['subjectListing'];
    // $blurb = $_POST['blurb'];
    $subjectListing = '';
    $blurb = '';
    
    $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);
    $fileUploader->getFileExtensions();

    //Lock table
    try {
        $sql = 'LOCK TABLES pupilsightDepartment WRITE, pupilsightDepartmentStaff WRITE';
        $result = $connection2->query($sql);
    } catch (PDOException $e) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit();
    }

    //Get next autoincrement
    try {
        $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightDepartment'";
        $resultAI = $connection2->query($sqlAI);
    } catch (PDOException $e) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit();
    }

    $rowAI = $resultAI->fetch();
    $AI = str_pad($rowAI['Auto_increment'], 4, '0', STR_PAD_LEFT);

    if ($type == '' or $name == '' ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit();
    } else {
        $partialFail = false;
        
        //Move attached file, if there is one
        if (!empty($_FILES['file']['tmp_name'])) {
            $file = (isset($_FILES['file']))? $_FILES['file'] : null;

            // Upload the file, return the /uploads relative path
            $attachment = $fileUploader->uploadFromPost($file, $name);

            if (empty($attachment)) {
                $partialFail = true;
            }
        } else {
            $attachment = '';
        }

        //Scan through staff
        $staff = array();
        if (isset($_POST['staff'])) {
            $staff = $_POST['staff'];
        }
        if ($type == 'Learning Area') {
            $role = $_POST['roleLA'];
        } elseif ($type == 'Administration') {
            $role = $_POST['roleAdmin'];
        }
        if ($role == '') {
            $role = 'Other';
        }
        if (count($staff) > 0) {
            foreach ($staff as $t) {
                //Check to see if person is already registered in this activity
                try {
                    $dataGuest = array('pupilsightPersonID' => $t, 'pupilsightDepartmentID' => $AI);
                    $sqlGuest = 'SELECT * FROM pupilsightDepartmentStaff WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightDepartmentID=:pupilsightDepartmentID';
                    $resultGuest = $connection2->prepare($sqlGuest);
                    $resultGuest->execute($dataGuest);
                } catch (PDOException $e) {
                    echo "<div class='error'>".$e->getMessage().'</div>';
                }

                if ($resultGuest->rowCount() == 0) {
                    try {
                        $data = array('pupilsightPersonID' => $t, 'pupilsightDepartmentID' => $AI, 'role' => $role);
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
            $data = array('pupilsightDepartmentID' => $AI, 'type' => $type, 'name' => $name, 'nameShort' => $nameShort, 'subjectListing' => $subjectListing, 'blurb' => $blurb, 'logo' => $attachment);
            $sql = 'INSERT INTO pupilsightDepartment SET pupilsightDepartmentID=:pupilsightDepartmentID, type=:type, name=:name, nameShort=:nameShort, subjectListing=:subjectListing, blurb=:blurb, logo=:logo';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        try {
            $sql = 'UNLOCK TABLES';
            $result = $connection2->query($sql);
        } catch (PDOException $e) {
        }

        if ($partialFail == true) {
            $URL .= '&return=warning1';
            header("Location: {$URL}");
        } else {
            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}

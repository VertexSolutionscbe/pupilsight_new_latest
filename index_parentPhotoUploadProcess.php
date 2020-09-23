<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include './pupilsight.php';

$pupilsightPersonID = $_GET['pupilsightPersonID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php';

//Proceed!
//Check if planner specified
if ($pupilsightPersonID == '' or $pupilsightPersonID != $_SESSION[$guid]['pupilsightPersonID'] or $_FILES['file1']['tmp_name'] == '') {
    $URL .= '?return=error1';
    header("Location: {$URL}");
    exit();
} else {
    try {
        $data = array('pupilsightPersonID' => $pupilsightPersonID);
        $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $URL .= '?return=error2';
        header("Location: {$URL}");
        exit();
    }

    if ($result->rowCount() != 1) {
        $URL .= '?return=error2';
        header("Location: {$URL}");
        exit();
    } else {
        $attachment1 = null;
        if (!empty($_FILES['file1']['tmp_name'])) {
            $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);
            $fileUploader->setFileSuffixType(Pupilsight\FileUploader::FILE_SUFFIX_INCREMENTAL);

            $file = (isset($_FILES['file1']))? $_FILES['file1'] : null;

            // Upload the file, return the /uploads relative path
            $attachment1 = $fileUploader->uploadFromPost($file, $_SESSION[$guid]['username'].'_240');

            if (empty($attachment1)) {
                $URL .= '?return=warning1';
                header("Location: {$URL}");
                exit();
            }
        }
        
        $path = $_SESSION[$guid]['absolutePath'];

        //Check for reasonable image
        $size = getimagesize($path.'/'.$attachment1);
        $width = $size[0];
        $height = $size[1];
        if ($width < 240 or $height < 320) {
            $URL .= '?return=error6';
            header("Location: {$URL}");
            exit();
        } elseif ($width > 480 or $height > 640) {
            $URL .= '?return=error6';
            header("Location: {$URL}");
            exit();
        } elseif (($width / $height) < 0.60 or ($width / $height) > 0.8) {
            $URL .= '?return=error6';
            header("Location: {$URL}");
            exit();
        } else {
            //UPDATE
            try {
                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'attachment1' => $attachment1);
                $sql = 'UPDATE pupilsightPerson SET image_240=:attachment1 WHERE pupilsightPersonID=:pupilsightPersonID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '?return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Update session variables
            $_SESSION[$guid]['image_240'] = $attachment1;

            //Clear cusotm sidebar
            unset($_SESSION[$guid]['index_customSidebar.php']);

            $URL .= '?return=success0';
            header("Location: {$URL}");
        }
    }
}

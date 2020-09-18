<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightStudentNoteCategoryID = $_GET['pupilsightStudentNoteCategoryID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/studentsSettings_noteCategory_edit.php&pupilsightStudentNoteCategoryID=$pupilsightStudentNoteCategoryID";

if (isActionAccessible($guid, $connection2, '/modules/User Admin/studentsSettings_noteCategory_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightStudentNoteCategoryID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightStudentNoteCategoryID' => $pupilsightStudentNoteCategoryID);
            $sql = 'SELECT * FROM pupilsightStudentNoteCategory WHERE pupilsightStudentNoteCategoryID=:pupilsightStudentNoteCategoryID';
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
            $name = $_POST['name'];
            $active = $_POST['active'];
            $template = $_POST['template'];

            //Validate Inputs
            if ($name == '' or $active == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'pupilsightStudentNoteCategoryID' => $pupilsightStudentNoteCategoryID);
                    $sql = 'SELECT * FROM pupilsightStudentNoteCategory WHERE name=:name AND NOT pupilsightStudentNoteCategoryID=:pupilsightStudentNoteCategoryID';
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
                        $data = array('name' => $name, 'active' => $active, 'template' => $template, 'pupilsightStudentNoteCategoryID' => $pupilsightStudentNoteCategoryID);
                        $sql = 'UPDATE pupilsightStudentNoteCategory SET name=:name, active=:active, template=:template WHERE pupilsightStudentNoteCategoryID=:pupilsightStudentNoteCategoryID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}

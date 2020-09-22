<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightMappingID = $_GET['pupilsightMappingID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/mapping_manage_edit.php&pupilsightMappingID='.$pupilsightMappingID;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/mapping_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightMappingID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightMappingID' => $pupilsightMappingID);
            $sql = 'SELECT * FROM pupilsightProgramClassSectionMapping WHERE pupilsightMappingID=:pupilsightMappingID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error6';
            header("Location: {$URL}");
        } else {
            //Validate Inputs
            $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
            $program = $_POST['pupilsightProgramID'];
            $class = $_POST['pupilsightYearGroupID'];
            $section = $_POST['pupilsightRollGroupID'];
            

            if ($program == '' or $class == '' or $section == '' ) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $class, 'pupilsightRollGroupID' => $section, 'pupilsightMappingID' => $pupilsightMappingID);
                    $sql = 'SELECT * FROM pupilsightProgramClassSectionMapping WHERE (pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID) AND NOT pupilsightMappingID=:pupilsightMappingID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error7';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $class, 'pupilsightRollGroupID' => $section,  'pupilsightMappingID' => $pupilsightMappingID);
                        $sql = 'UPDATE pupilsightProgramClassSectionMapping SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID WHERE pupilsightMappingID=:pupilsightMappingID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error8';
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

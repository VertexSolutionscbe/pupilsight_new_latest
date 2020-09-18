<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightSchoolYearIDNext = $_GET['pupilsightSchoolYearIDNext'];
$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/School Admin/rollGroup_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearIDNext";

if (isActionAccessible($guid, $connection2, '/modules/School Admin/rollGroup_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school years specified (current and next)
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearIDNext == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //GET CURRENT ROLL GROUPS
        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT * FROM pupilsightRollGroup WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() < 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            $partialFail = false;
            while ($row = $result->fetch()) {
                //Write to database
                try {
                    $dataInsert = array('pupilsightSchoolYearID' => $pupilsightSchoolYearIDNext, 'name' => $row['name'], 'nameShort' => $row['nameShort'], 'pupilsightPersonIDTutor' => $row['pupilsightPersonIDTutor'], 'pupilsightPersonIDTutor2' => $row['pupilsightPersonIDTutor2'], 'pupilsightPersonIDTutor3' => $row['pupilsightPersonIDTutor3'], 'pupilsightSpaceID' => $row['pupilsightSpaceID'], 'website' => $row['website']);
                    $sqlInsert = 'INSERT INTO pupilsightRollGroup SET pupilsightSchoolYearID=:pupilsightSchoolYearID, name=:name, nameShort=:nameShort, pupilsightPersonIDTutor=:pupilsightPersonIDTutor, pupilsightPersonIDTutor2=:pupilsightPersonIDTutor2, pupilsightPersonIDTutor3=:pupilsightPersonIDTutor3, pupilsightSpaceID=:pupilsightSpaceID, pupilsightRollGroupIDNext=NULL, website=:website';
                    $resultInsert = $connection2->prepare($sqlInsert);
                    $resultInsert->execute($dataInsert);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
            }

            if ($partialFail == true) {
                $URL .= '&return=error5';
                header("Location: {$URL}");
            } else {
                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}

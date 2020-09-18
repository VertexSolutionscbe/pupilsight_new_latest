<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/rollGroup_manage_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID";

if (isActionAccessible($guid, $connection2, '/modules/School Admin/rollGroup_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $name = $_POST['name'];
    $nameShort = $_POST['nameShort'];
    $pupilsightPersonIDTutor = null;
    if ($_POST['pupilsightPersonIDTutor'] != '') {
        $pupilsightPersonIDTutor = $_POST['pupilsightPersonIDTutor'];
    }
    $pupilsightPersonIDTutor2 = null;
    if ($_POST['pupilsightPersonIDTutor2'] != '') {
        $pupilsightPersonIDTutor2 = $_POST['pupilsightPersonIDTutor2'];
    }
    $pupilsightPersonIDTutor3 = null;
    if ($_POST['pupilsightPersonIDTutor3'] != '') {
        $pupilsightPersonIDTutor3 = $_POST['pupilsightPersonIDTutor3'];
    }
    $pupilsightPersonIDEA = null;
    if ($_POST['pupilsightPersonIDEA'] != '') {
        $pupilsightPersonIDEA = $_POST['pupilsightPersonIDEA'];
    }
    $pupilsightPersonIDEA2 = null;
    if ($_POST['pupilsightPersonIDEA2'] != '') {
        $pupilsightPersonIDEA2 = $_POST['pupilsightPersonIDEA2'];
    }
    $pupilsightPersonIDEA3 = null;
    if ($_POST['pupilsightPersonIDEA3'] != '') {
        $pupilsightPersonIDEA3 = $_POST['pupilsightPersonIDEA3'];
    }
    $pupilsightSpaceID = null;
    if ($_POST['pupilsightSpaceID'] != '') {
        $pupilsightSpaceID = $_POST['pupilsightSpaceID'];
    }
    $pupilsightRollGroupIDNext = null;
    if (isset($_POST['pupilsightRollGroupIDNext'])) {
        $pupilsightRollGroupIDNext = $_POST['pupilsightRollGroupIDNext'];
    }
    $website = null;
    if (isset($_POST['website'])) {
        $website = $_POST['website'];
    }

    $attendance = (isset($_POST['attendance']))? $_POST['attendance'] : NULL;

    if ($pupilsightSchoolYearID == '' or $name == '' or $nameShort == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness in current school year
        try {
            $data = array('name' => $name, 'nameShort' => $nameShort, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT * FROM pupilsightRollGroup WHERE (name=:name OR nameShort=:nameShort) AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
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
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'name' => $name, 'nameShort' => $nameShort, 'pupilsightPersonIDTutor' => $pupilsightPersonIDTutor, 'pupilsightPersonIDTutor2' => $pupilsightPersonIDTutor2, 'pupilsightPersonIDTutor3' => $pupilsightPersonIDTutor3, 'pupilsightPersonIDEA' => $pupilsightPersonIDEA, 'pupilsightPersonIDEA2' => $pupilsightPersonIDEA2, 'pupilsightPersonIDEA3' => $pupilsightPersonIDEA3, 'pupilsightSpaceID' => $pupilsightSpaceID, 'pupilsightRollGroupIDNext' => $pupilsightRollGroupIDNext, 'attendance' => $attendance, 'website' => $website);
                $sql = 'INSERT INTO pupilsightRollGroup SET pupilsightSchoolYearID=:pupilsightSchoolYearID, name=:name, nameShort=:nameShort, pupilsightPersonIDTutor=:pupilsightPersonIDTutor, pupilsightPersonIDTutor2=:pupilsightPersonIDTutor2, pupilsightPersonIDTutor3=:pupilsightPersonIDTutor3, pupilsightPersonIDEA=:pupilsightPersonIDEA, pupilsightPersonIDEA2=:pupilsightPersonIDEA2, pupilsightPersonIDEA3=:pupilsightPersonIDEA3, pupilsightSpaceID=:pupilsightSpaceID, pupilsightRollGroupIDNext=:pupilsightRollGroupIDNext, attendance=:attendance, website=:website';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 5, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}

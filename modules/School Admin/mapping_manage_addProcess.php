<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/mapping_manage_add.php';

$URLSUC = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/mapping_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/mapping_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    //Proceed!
    //Validate Inputs
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $program = $_POST['pupilsightProgramID'];
    $class = $_POST['pupilsightYearGroupID'];
    $section = $_POST['pupilsightRollGroupID'];
    
    if ($program == '' or $class == '' or $section == '' ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        // try {
        //     $data = array('pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $class, 'pupilsightRollGroupID' => $section);
        //     $sql = 'SELECT * FROM pupilsightProgramClassSectionMapping WHERE pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID';
        //     $result = $connection2->prepare($sql);
        //     $result->execute($data);
        // } catch (PDOException $e) {
        //     $URL .= '&return=error2';
        //     header("Location: {$URL}");
        //     exit();
        // }

        // if ($result->rowCount() > 0) {
        //     $URL .= '&return=error3';
        //     header("Location: {$URL}");
        // } else {
            //Write to database
            try {
                foreach($section as $sec){
                    $pupilsightRollGroupID = $sec;
                    $datachk = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $class, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
                    $sqlchk = 'SELECT * FROM pupilsightProgramClassSectionMapping WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID';
                    $resultchk = $connection2->prepare($sqlchk);
                    $resultchk->execute($datachk);
                    if ($resultchk->rowCount() < 1) {
                        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $class, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
                        $sql = 'INSERT INTO pupilsightProgramClassSectionMapping SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    }

                }

                
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            $URLSUC .= "&return=success0&editID=$AI";
            header("Location: {$URLSUC}");
        // }
    }
}

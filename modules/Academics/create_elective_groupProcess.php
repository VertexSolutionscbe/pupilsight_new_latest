<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
$session = $container->get('session');
$sectionids = $session->get('section_ids');
$subjectids = $session->get('subject_ids');
$pupilsightSchoolYearID  = $_POST['pupilsightSchoolYearID'];
$pupilsightProgramID  = $_POST['pupilsightProgramID'];
$pupilsightYearGroupID  = $_POST['pupilsightYearGroupID'];

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . getModuleName($_POST['address']) . '/manage_elective_group.php&sid='.$pupilsightSchoolYearID.'&pid='.$pupilsightProgramID.'&cid='.$pupilsightYearGroupID.'';


if (isActionAccessible($guid, $connection2, '/modules/Academics/create_elective_group.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    
    
    //Proceed!
    //Validate Inputs
    
    $ename                  = $_POST['elective_name'];
    $order_no               = $_POST['order_no'];
    
    $max_selection          = $_POST['max_selection'];
    $min_selection          = $_POST['min_selection'];
    $sectionid  = explode(',', $sectionids);
    $subjectid = explode(',', $subjectids);
    if ($ename == '' or $order_no == '' or $max_selection == '' or $min_selection == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data   = array(
                'name' => $ename,
                'pupilsightSchoolYearID' => $pupilsightSchoolYearID,
                'pupilsightProgramID' => $pupilsightProgramID,
                'pupilsightYearGroupID' => $pupilsightYearGroupID,
            );
            $sql = 'SELECT * FROM ac_elective_group WHERE  name=:name AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
           
        }
        catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }
        
        if ($result->rowCount() > 0) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            //Check for other currents
            
            //Write to database
            try {
                $data3   = array(
                    'name' => $ename,
                    'order_no' => $order_no,
                    'max_selection' => $max_selection,
                    'min_selection' => $min_selection,
                    'pupilsightSchoolYearID' => $pupilsightSchoolYearID,
                    'pupilsightProgramID' => $pupilsightProgramID,
                    'pupilsightYearGroupID' => $pupilsightYearGroupID
                );
                $sql3 = 'INSERT INTO ac_elective_group SET name=:name, order_no=:order_no,max_selection=:max_selection,min_selection=:min_selection,pupilsightSchoolYearID=:pupilsightSchoolYearID,pupilsightProgramID=:pupilsightProgramID,pupilsightYearGroupID=:pupilsightYearGroupID';
                $result3 = $connection2->prepare($sql3);
                $result3->execute($data3);
                $electiveId = $connection2->lastInsertID();

                if(!empty($sectionids)){
                    foreach($sectionid as $pupilsightRollGroupID){
                        $datasec   = array(
                            'ac_elective_group_id' => $electiveId,
                            'pupilsightRollGroupID' => $pupilsightRollGroupID
                        );
                        $sqlsec = 'INSERT INTO ac_elective_group_section SET ac_elective_group_id=:ac_elective_group_id, pupilsightRollGroupID=:pupilsightRollGroupID';
                        $resultsec = $connection2->prepare($sqlsec);
                        $resultsec->execute($datasec);
                    }
                }

                if(!empty($subjectids)){
                    foreach($subjectid as $pupilsightDepartmentID){
                        $datasub   = array(
                            'ac_elective_group_id' => $electiveId,
                            'pupilsightDepartmentID' => $pupilsightDepartmentID
                        );
                        $sqlsub = 'INSERT INTO ac_elective_group_subjects SET 
                        ac_elective_group_id=:ac_elective_group_id, pupilsightDepartmentID=:pupilsightDepartmentID';
                        $resultsub = $connection2->prepare($sqlsub);
                        $resultsub->execute($datasub);
                    }
                }

                unset($_SESSION['section_ids']);
                unset($_SESSION['subject_ids']);
                $session->forget(['section_ids']);
                $session->forget(['subject_ids']);
            }
            catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            }
            
            
            // $URL .= "&return=success0&editID=$AI";
            
            $URL .= "&return=success0";
            header("Location: {$URL}");
            
        }
    }
}


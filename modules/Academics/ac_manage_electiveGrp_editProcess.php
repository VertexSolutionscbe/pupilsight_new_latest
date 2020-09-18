<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$session = $container->get('session');
echo $sectionids = $session->get('section_ids');
$subjectids = $session->get('subject_ids');
$pupilsightSchoolYearID  = $_POST['pupilsightSchoolYearID'];
$pupilsightProgramID  = $_POST['pupilsightProgramID'];
$pupilsightYearGroupID  = $_POST['pupilsightYearGroupID'];

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . getModuleName($_POST['address']) . '/manage_elective_group.php&sid='.$pupilsightSchoolYearID.'&pid='.$pupilsightProgramID.'&cid='.$pupilsightYearGroupID.'';

if (isActionAccessible($guid, $connection2, '/modules/Academics/ac_manage_electiveGrp_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $id = $_POST['id'];
    //print_r($id);die();
    //Proceed!
    //Check if school year specified
    if ($id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data   = array(
                'id' => $id
            );
            $sql    = 'SELECT * FROM ac_elective_group WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        }
        catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }
       // print_r($result->rowCount());die();
        
        if ($result->rowCount() != 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            //Validate Inputs
            // echo '<pre>';
            // print_r($_POST);
            // echo '</pre>';
            // die();
            
            $ename         = $_POST['elective_name'];
            $order_no      = $_POST['order_no'];
            $max_selection = $_POST['max_selection'];
            $min_selection = $_POST['min_selection'];
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
                        'id' => $id,
                    );
                    $sql = 'SELECT * FROM ac_elective_group WHERE  (name=:name AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID) AND NOT id=:id';
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
                    //Write to database
                    try {
                        
                        $data = array(
                            'name' => $ename,
                            'order_no' => $order_no,
                            'max_selection' => $max_selection,
                            'min_selection' => $min_selection,
                            'pupilsightSchoolYearID' => $pupilsightSchoolYearID,
                            'pupilsightProgramID' => $pupilsightProgramID,
                            'pupilsightYearGroupID' => $pupilsightYearGroupID,
                            'id' => $id
                        );
                        
                        $sql    = 'UPDATE ac_elective_group SET name=:name, order_no=:order_no,max_selection=:max_selection, min_selection=:min_selection,pupilsightSchoolYearID=:pupilsightSchoolYearID,pupilsightProgramID=:pupilsightProgramID,pupilsightYearGroupID=:pupilsightYearGroupID WHERE id=:id';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);                        
                        $electiveId = $id;    
                        
                        if(!empty($sectionids)){
                            $deldatasec = array('ac_elective_group_id' => $electiveId);
                            $delsqlsec = 'DELETE FROM ac_elective_group_section WHERE ac_elective_group_id=:ac_elective_group_id';
                            $delresultsec = $connection2->prepare($delsqlsec);
                            $delresultsec->execute($deldatasec);

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
                            $deldatasub = array('ac_elective_group_id' => $electiveId);
                            $delsqlsub = 'DELETE FROM ac_elective_group_subjects WHERE ac_elective_group_id=:ac_elective_group_id';
                            $delresultsub = $connection2->prepare($delsqlsub);
                            $delresultsub->execute($deldatasub);

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

                        $session->forget(['section_ids']);
                        $session->forget(['subject_ids']);
                        
                    }
                    catch (PDOException $e) {
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

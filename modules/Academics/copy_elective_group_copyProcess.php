<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$getsql1 = 'SELECT * FROM ac_elective_group WHERE id IN ('.$_POST['electiveGroupId'].') GROUP BY pupilsightYearGroupID';
$getresult1 = $connection2->query($getsql1);
$getdata1 = $getresult1->fetch();

                
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/manage_elective_group.php&sid='.$getdata1['pupilsightSchoolYearID'].'&pid='.$getdata1['pupilsightProgramID'].'&cid='.$getdata1['pupilsightYearGroupID'].'';

if (isActionAccessible($guid, $connection2, '/modules/Academics/subject_to_class_manage_copy.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    //Proceed!
    $electiveGroupId = $_POST['electiveGroupId'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $pupilsightProgramID = $_POST['pupilsightProgramID'];
    $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
    
    
    //Validate Inputs
    if ($pupilsightYearGroupID == '' or $electiveGroupId == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $eGId = explode(',', $electiveGroupId);
            foreach($eGId as $eid){
                $getsql = 'SELECT * FROM ac_elective_group WHERE id = '.$eid.' ';
                $getresult = $connection2->query($getsql);
                $getdata = $getresult->fetch();

                $name = $getdata['name'];
                $order_no = $getdata['order_no'];
                $max_selection = $getdata['max_selection'];
                $min_selection = $getdata['min_selection'];
                

                $getsqlsecs = 'SELECT * FROM ac_elective_group_section WHERE ac_elective_group_id = "'.$eid.'" ';
                $getresultsecs = $connection2->query($getsqlsecs);
                $getsecsdata = $getresultsecs->fetchAll();

                $getsqlsubs = 'SELECT * FROM ac_elective_group_subjects WHERE ac_elective_group_id = "'.$eid.'" ';
                $getresultsubs = $connection2->query($getsqlsubs);
                $getsubsdata = $getresultsubs->fetchAll();

                $data1 = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID);
                $sql1 = 'SELECT * FROM ac_elective_group WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID';
                $resultchk = $connection2->prepare($sql1);
                $resultchk->execute($data1);

                if ($resultchk->rowCount() == 0) {
                    $data3   = array(
                        'name' => $name,
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
    
                    if(!empty($getsecsdata)){
                        foreach($getsecsdata as $sdata){
                            $datasec   = array(
                                'ac_elective_group_id' => $electiveId,
                                'pupilsightRollGroupID' => $sdata['pupilsightRollGroupID']
                            );
                            $sqlsec = 'INSERT INTO ac_elective_group_section SET ac_elective_group_id=:ac_elective_group_id, pupilsightRollGroupID=:pupilsightRollGroupID';
                            $resultsec = $connection2->prepare($sqlsec);
                            $resultsec->execute($datasec);
                        }
                    }
    
                    if(!empty($getsubsdata)){
                        foreach($getsubsdata as $subdata){
                            $datasub   = array(
                                'ac_elective_group_id' => $electiveId,
                                'pupilsightDepartmentID' => $subdata['pupilsightDepartmentID']
                            );
                            $sqlsub = 'INSERT INTO ac_elective_group_subjects SET 
                            ac_elective_group_id=:ac_elective_group_id, pupilsightDepartmentID=:pupilsightDepartmentID';
                            $resultsub = $connection2->prepare($sqlsub);
                            $resultsub->execute($datasub);
                        }
                    }
                }
                
            }
    
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Last insert ID
        $AI = str_pad($connection2->lastInsertID(), 10, '0', STR_PAD_LEFT);

        $URL .= "&return=success0&editID=$AI";
        header("Location: {$URL}");
    }
}

<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=%2Fmodules%2FCampaign%2FtransitionsList.php';

if (isActionAccessible($guid, $connection2, '/modules/Campaign/transitionImportProcess.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $subid = explode(',',$_POST['subid']);
    
   
    $sql = "SELECT a.*, b.form_id, b.academic_id FROM campaign_transitions_form_map AS a LEFT JOIN campaign AS b ON a.campaign_id = b.id GROUP BY table_name";
    $result = $connection2->query($sql);
    $tablelist = $result->fetchAll();
    // echo '<pre>';
    // print_r($tablelist);
    // echo '</pre>';
    // die();
    if(empty($tablelist)){
        header("Location: {$URL}");
    }
    $tabledata = array();
    foreach($tablelist as $k=>$tbl){
        $sqlc = "SELECT column_name, fluent_form_column_name FROM campaign_transitions_form_map WHERE table_name = '".$tbl['table_name']."' ";
        $resultc = $connection2->query($sqlc);
        $tablecol = $resultc->fetchAll();

        

        // $sqls = "SELECT submission_id FROM wp_fluentform_entry_details WHERE form_id = '".$tbl['form_id']."' GROUP BY submission_id ";
        // $results = $connection2->query($sqls);
        // $tablesubdata = $results->fetchAll();
        
        $tabledata[$k]['tablename'] = $tbl['table_name'];
        $tabledata[$k]['campaign_id'] = $tbl['campaign_id'];
        $tabledata[$k]['pupilsightSchoolYearID'] = $tbl['academic_id'];
        $tabledata[$k]['form_id'] = $tbl['form_id'];
        $tabledata[$k]['tablecol'] = $tablecol;
        $tabledata[$k]['tablesubdata'] = $subid;
    }    
    
     
    $tdata = array();
    $cname = array();
    if(!empty($tabledata)){
        foreach($tabledata as $k=>$td){
            $fid = $td['form_id'];
            $pupilsightSchoolYearID = $td['pupilsightSchoolYearID']; 
            foreach($td['tablesubdata'] as $ts=>$tsub){
                foreach($td['tablecol'] as $t=>$tcol){
                    
                        $sqlf = "SELECT field_value FROM wp_fluentform_entry_details WHERE submission_id = ".$tsub." AND field_name = '".$tcol['fluent_form_column_name']."' AND status = '0' ";
                        $resultf = $connection2->query($sqlf);
                        $coldata = $resultf->fetch();

                        $sqlcp = "SELECT pupilsightProgramID, pupilsightYearGroupID FROM wp_fluentform_submissions WHERE id = ".$tsub."  ";
                        $resultcp = $connection2->query($sqlcp);
                        $clspro = $resultcp->fetch();
                        if(!empty($clspro)){
                            $prog = $clspro['pupilsightProgramID'];
                            $cls = $clspro['pupilsightYearGroupID'];
                        } else {
                            $prog = '';
                            $cls = '';
                        }

                        //echo $coldata->rowCount();
                        if(!empty($coldata)){
                            $colname = $tcol['column_name'];
                            $val = $coldata['field_value'];
                            $fid = $td['form_id'];
                            $tablename = $td['tablename']; 
                            $tdata[$ts][$colname] = $val;
                            $tdata[$ts]['pupilsightRoleIDPrimary'] = '003';
                            $tdata[$ts]['pupilsightRoleIDAll'] = '003';
                            $tdata[$ts]['sid'] = $tsub;
                            $cname[$t] = $tcol['column_name'].'=:'.$tcol['column_name'];
                            $chk = '1';
                        } else {
                            $chk = '2';
                        }
                        
               }
            }
            if($chk == '1' && !empty($tdata)){
                foreach($tdata as $td){
                    $sid = $td['sid'];
                    unset($td['sid']);
                    // echo '<pre>';
                    // print_r($td);
                    // echo '</pre>';
                    $setdata  = implode(',',$cname);
                    $setdata  = $setdata.',pupilsightRoleIDPrimary=:pupilsightRoleIDPrimary,pupilsightRoleIDAll=:pupilsightRoleIDAll';
                    $sqlins = "INSERT INTO ".$tablename." SET ".$setdata." ";
                    $resultins = $connection2->prepare($sqlins);
                    $resultins->execute($td);
                    $stuId = $connection2->lastInsertID();

                    if($tablename == 'pupilsightPerson' && !empty($prog) && !empty($cls)){
                        
                        $data = array('pupilsightPersonID' => $stuId,'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $prog, 'pupilsightYearGroupID' => $cls);
                                
                        $sqlenroll = "INSERT INTO pupilsightStudentEnrolment SET pupilsightPersonID=:pupilsightPersonID,pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID,pupilsightYearGroupID=:pupilsightYearGroupID";
                        $resultenroll = $connection2->prepare($sqlenroll);
                        $resultenroll->execute($data);
                        
                    }



                    $wdata = array('status'=> '1', 'submission_id' => $sid);
                    $sqlupd = "UPDATE wp_fluentform_entry_details SET status=:status WHERE submission_id=:submission_id";
                    $resultupd = $connection2->prepare($sqlupd);
                    $resultupd->execute($wdata);
                }
                $tdata=[];
            }    
            
        }
        echo 'success';
    }
  
               
    //$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/transitionsList.php';
    
                
    
                
       
    
}

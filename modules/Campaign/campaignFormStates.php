<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

use Pupilsight\Contracts\Comms\SMS;
use Pupilsight\Contracts\Comms\Mailer;

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/campaignFormList.php';

if (isActionAccessible($guid, $connection2, '/modules/Campaign/campaignFormStates.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $sms = $container->get(SMS::class);
  
    $stateid = $_POST['sid'];
    $statename = $_POST['sname'];
    $campaignId = $_POST['cid'];
    $formId = $_POST['fid'];
    $subId = $_POST['subid'];
    $crtd =  date('Y-m-d H:i:s');
    $cdt = date('Y-m-d H:i:s');
    
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
	$email_arr =array();
	
    if ($stateid == '' or $statename == ''  or $campaignId == '' or $formId == '' or $subId == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    }  else {
        $sqlnotitype="SELECT a.notification, a.pupilsightTemplateIDs, b.fn_fee_admission_setting_ids FROM workflow_state AS a LEFT JOIN workflow_transition AS b ON a.id = b.to_state WHERE b.id = ".$stateid." ";
        $resultchk = $connection2->query($sqlnotitype);
        $chknotitype = $resultchk->fetch();

        $submissionId = explode(',', $subId);
        $admsettingsId = $chknotitype['fn_fee_admission_setting_ids'];
        //print_r($submissionId);die();
        foreach($submissionId as $sub){
            $sqle = "SELECT a.response, a.pupilsightProgramID, b.pupilsightYearGroupID FROM wp_fluentform_submissions AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.id = ".$sub." ";
            $resulte = $connection2->query($sqle);
            $rowdata = $resulte->fetch();
            $sd = json_decode($rowdata['response'], TRUE);
            $email = '';
            $number = '';
            if(!empty($sd)){
                $names = implode(' ', $sd['student_name']);
                $email = $sd['father_email'];
                $number = $sd['father_mobile'];
                $father_name = $sd['father_name'];
                $mother_email = $sd['mother_email'];
                $mother_name = $sd['mother_name'];
                $application_no = $sd['application_id'];
                $pupilsightProgramID = $rowdata['pupilsightProgramID'];
                $pupilsightYearGroupID = $rowdata['pupilsightYearGroupID'];
                $smspupilsightPersonID = $rowdata['pupilsightPersonID'];

                
            }
            
            $sqlchk = "SELECT COUNT(id) AS kount FROM campaign_form_status WHERE submission_id = ".$sub." AND campaign_id = ".$campaignId." AND form_id = ".$formId." AND state_id = ".$stateid." ";
            $resultchk = $connection2->query($sqlchk);
            $datachk = $resultchk->fetch();
            // echo $datachk['kount'];
            // die();
            if($datachk['kount'] == 0){

                $data = array('campaign_id' => $campaignId,'form_id' => $formId, 'submission_id' => $sub, 'state' => $statename,  'state_id' => $stateid, 'status' => '1', 'pupilsightPersonID' => $cuid, 'cdt' => $crtd);
                
                $sql = "INSERT INTO campaign_form_status SET campaign_id=:campaign_id,form_id=:form_id, submission_id=:submission_id,state=:state,state_id=:state_id, status=:status, pupilsightPersonID=:pupilsightPersonID, cdt=:cdt";
                $result = $connection2->prepare($sql);
                $result->execute($data);

                if(!empty($admsettingsId) && !empty($pupilsightProgramID) && !empty($pupilsightYearGroupID)){
                    $sqlas = "SELECT fn_fee_structure_id, classes FROM fn_fee_admission_settings WHERE pupilsightProgramID = ".$pupilsightProgramID." AND FIND_IN_SET('".$pupilsightYearGroupID."',classes) AND id IN (".$admsettingsId.") ";
                    $resultas = $connection2->query($sqlas);
                    $settdata = $resultas->fetchAll();
                    // echo '<pre>';
                    // print_r($settdata);
                    // echo '</pre>';

                    foreach($settdata as $asd){
                            $fn_fee_structure_id = $asd['fn_fee_structure_id'];
                            $id = $fn_fee_structure_id;
                                $datas = array('id' => $id);
                                $sqls = 'SELECT a.*, b.formatval FROM fn_fee_structure AS a LEFT JOIN fn_fee_series AS b ON a.inv_fee_series_id = b.id WHERE a.id=:id';
                                $results = $connection2->prepare($sqls);
                                $results->execute($datas);
                                $values = $results->fetch();
                                
                           
                                $datac = array('fn_fee_structure_id' => $id);
                                $sqlc = 'SELECT * FROM fn_fee_structure_item WHERE fn_fee_structure_id=:fn_fee_structure_id';
                                $resultc = $connection2->prepare($sqlc);
                                $resultc->execute($datac);
                                $childvalues = $resultc->fetchAll();
                
                            
                
                                $data = array('title' => $values['invoice_title'], 'fn_fee_structure_id' => $id , 'pupilsightSchoolYearID' => $values['pupilsightSchoolYearID'], 'pupilsightSchoolFinanceYearID' => $values['pupilsightSchoolFinanceYearID'], 'inv_fn_fee_series_id' => $values['inv_fee_series_id'], 'rec_fn_fee_series_id' => $values['recp_fee_series_id'], 'fn_fees_head_id' => $values['fn_fees_head_id'], 'fn_fees_fine_rule_id' => $values['fn_fees_fine_rule_id'], 'fn_fees_discount_id' => $values['fn_fees_discount_id'], 'due_date' => $values['due_date'],'amount_editable' => $values['amount_editable'],'display_fee_item' => $values['display_fee_item'], 'cdt' => $cdt);
                            
                                $sql = 'INSERT INTO fn_fee_invoice SET title=:title, fn_fee_structure_id=:fn_fee_structure_id, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightSchoolFinanceYearID=:pupilsightSchoolFinanceYearID, inv_fn_fee_series_id=:inv_fn_fee_series_id, rec_fn_fee_series_id=:rec_fn_fee_series_id, fn_fees_head_id=:fn_fees_head_id, fn_fees_fine_rule_id=:fn_fees_fine_rule_id, fn_fees_discount_id=:fn_fees_discount_id, due_date=:due_date, amount_editable=:amount_editable, display_fee_item=:display_fee_item, cdt=:cdt';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                                
                                $invId = $connection2->lastInsertID();
                
                                if(!empty($childvalues)){
                                    foreach($childvalues as $cv){
                                        $feeitem = $cv['fn_fee_item_id'];
                                        $desc = '';
                                        $amt = $cv['amount'];
                                        $taxdata = $cv['tax_percent'];
                                        $disc = '';
                                        $tamt = $cv['total_amount'];
                
                                        if(!empty($feeitem) && !empty($amt)){
                                            $data1 = array('fn_fee_invoice_id' => $invId, 'fn_fee_item_id' => $feeitem, 'description' => $desc, 'amount' => $amt, 'tax' => $taxdata, 'discount' => $disc, 'total_amount' => $tamt);
                                            $sql1 = "INSERT INTO fn_fee_invoice_item SET fn_fee_invoice_id=:fn_fee_invoice_id, fn_fee_item_id=:fn_fee_item_id, description=:description, amount=:amount,  tax=:tax, discount=:discount, total_amount=:total_amount";
                                            $result1 = $connection2->prepare($sql1);
                                            $result1->execute($data1);
                                        }
                                    }
                                }
                
                             
                                $dataav = array('fn_fee_invoice_id'=>$invId,'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID);
                                $sqlav = 'SELECT * FROM fn_fee_invoice_class_assign WHERE fn_fee_invoice_id=:fn_fee_invoice_id AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID';
                                $resultav = $connection2->prepare($sqlav);
                                $resultav->execute($dataav);
                                if ($resultav->rowCount() == 0) {
                                    $sql1av = 'INSERT INTO fn_fee_invoice_class_assign SET fn_fee_invoice_id=:fn_fee_invoice_id,pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID';
                                    $result1av = $connection2->prepare($sql1av);
                                    $result1av->execute($dataav);
                                }
        
                            
                                        $datastu = array('fn_fee_invoice_id'=>$invId,'submission_id' => $sub);
                                        $sqlstu = 'SELECT * FROM fn_fee_invoice_applicant_assign WHERE fn_fee_invoice_id=:fn_fee_invoice_id AND submission_id=:submission_id';
                                        $resultstu = $connection2->prepare($sqlstu);
                                        $resultstu->execute($datastu);
                                        
                                        if ($resultstu->rowCount() == 0) {
                                            $invSeriesId = $values['inv_fee_series_id'];
                                            // $invformat = explode('/',$values['format']);
                                            $invformat = explode('$',$values['formatval']);
                                            $iformat = '';
                                            $orderwise = 0;
                                            foreach($invformat as $inv){
                                                if($inv == '{AB}'){
                                                    $datafort = array('fn_fee_series_id'=>$invSeriesId,'order_wise' => $orderwise, 'type' => 'numberwise');
                                                    $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=:fn_fee_series_id AND order_wise=:order_wise AND type=:type';
                                                    $resultfort = $connection2->prepare($sqlfort);
                                                    $resultfort->execute($datafort);
                                                    $formatvalues = $resultfort->fetch();
                                                    // $iformat .= $formatvalues['last_no'].'/';
                                                    $iformat .= $formatvalues['last_no'];
                                                    
                                                    $str_length = $formatvalues['no_of_digit'];
        
                                                    $lastnoadd = $formatvalues['last_no'] + 1;
        
                                                    $lastno = substr("0000000{$lastnoadd}", -$str_length); 
        
                                                    $datafort1 = array('fn_fee_series_id'=>$invSeriesId,'order_wise' => $orderwise, 'type' => 'numberwise' , 'last_no' => $lastno);
                                                    $sqlfort1 = 'UPDATE fn_fee_series_number_format SET last_no=:last_no WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type AND order_wise=:order_wise';
                                                    $resultfort1 = $connection2->prepare($sqlfort1);
                                                    $resultfort1->execute($datafort1);
        
                                                } else {
                                                    // $iformat .= $inv.'/';
                                                    $iformat .= $inv;
                                                }
                                                $orderwise++;
                                            }
                                            
                                            // $invoiceno =  rtrim($iformat, "/");
                                            $invoiceno =  $iformat;
                                            $dataistu = array('fn_fee_invoice_id'=>$invId, 'invoice_no' => $invoiceno,'submission_id' => $sub, 'state_id' => $stateid);
                                            $sqlstu1 = 'INSERT INTO fn_fee_invoice_applicant_assign SET fn_fee_invoice_id=:fn_fee_invoice_id,invoice_no=:invoice_no, submission_id=:submission_id,state_id=:state_id';
                                            $resultstu1 = $connection2->prepare($sqlstu1);
                                            $resultstu1->execute($dataistu);
                                            
                                            
                                        }
                                
                    }
                }
                //die();

                if(!empty($chknotitype['pupilsightTemplateIDs'])){
                    $sqltemplate="SELECT * FROM pupilsightTemplate WHERE pupilsightTemplateID IN (".$chknotitype['pupilsightTemplateIDs'].") ";
                    $resulttem = $connection2->query($sqltemplate);
                    if($chknotitype['notification'] == '3'){
                        echo '1';
                        $templateData = $resulttem->fetchAll();
                        
                        foreach($templateData as $td){
                            $subject = $td['subject'];
                            $description = $td['description'];
                            $body = str_replace('@student_name',$names , $description);
                            $body = str_replace('@student_email',$email , $body);
                            $body = str_replace('@father_email',$email , $body);
                            $body = str_replace('@father_name',$father_name , $body);
                            $body = str_replace('@mother_email',$mother_email , $body);
                            $body = str_replace('@mother_name',$mother_name , $body);
                            $body = str_replace('@application_no',$application_no , $body);
                            if($td['type'] == 'Email'){
                                if(!empty($body)){ 
                                    $url = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/mailsend.php';
                                    $url .="&to=".$email;
                                    $url .="&subject=".rawurlencode($subject);
                                    $url .="&body=".rawurlencode($body);
                                    $res = file_get_contents($url);

                                    $ibody = stripslashes($body);
                                    $sq = "INSERT INTO campaign_email_sms_sent_details SET campaign_id = ".$campaignId.", submission_id = ".$sub.", state_id = ".$stateid." ,state_name='".$statename."', email='".$email."', subject='".$subject."', description='".$body."', pupilsightPersonID=".$cuid." ";
                                    $connection2->query($sq);
                                }    
                            } else if($td['type'] == 'Sms'){
                                if(!empty($body) && !empty($number)){
                                    // $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
                                    // $urls .="&send_to=".$number;
                                    // $urls .="&msg=".rawurlencode($body);
                                    // $urls .="&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
                                    // $resms = file_get_contents($urls);
                                    $msgto=$smspupilsightPersonID;
                                    $msgby=$_SESSION[$guid]["pupilsightPersonID"];
                                    $res = $sms->sendSMSPro($number, $msg, $msgto, $msgby);
                                    if ($res) {
                                        $sq = "INSERT INTO campaign_email_sms_sent_details SET campaign_id = ".$campaignId.", submission_id = ".$sub.", state_id = ".$stateid." ,state_name='".$statename."', phone=".$number.", description='".stripslashes($body)."', pupilsightPersonID=".$cuid." ";
                                        $connection2->query($sq);
                                    }
                                }
                            }    
                        }
                    } else {
                        echo '2';
                        $templateData = $resulttem->fetch();
                        $subject = $templateData['subject'];
                        $description = $templateData['description'];
                        $body = str_replace('@student_name',$names , $description);
                        $body = str_replace('@student_email',$email , $body);
                        if($chknotitype['notification'] == '1'){
                            if(!empty($body)){ 
                                $url = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/mailsend.php';
                                $url .="&to=".$email;
                                $url .="&subject=".rawurlencode($subject);
                                $url .="&body=".rawurlencode($body);
                                $res = file_get_contents($url);
                                echo $url;
                                
                                echo $sq = "INSERT INTO campaign_email_sms_sent_details SET campaign_id = ".$campaignId.", submission_id = ".$sub.", state_id = ".$stateid." ,state_name='".$statename."', email='".$email."', subject='".$subject."', description='".stripslashes($body)."', pupilsightPersonID=".$cuid." ";
                                $connection2->query($sq);
                            }    
                        } else if($chknotitype['notification'] == '2'){
                            if(!empty($body) && !empty($number)){
                                // $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
                                // $urls .="&send_to=".$number;
                                // $urls .="&msg=".rawurlencode($body);
                                // $urls .="&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
                                // $resms = file_get_contents($urls);

                                $msgto=$smspupilsightPersonID;
                                $msgby=$_SESSION[$guid]["pupilsightPersonID"];
                                $res = $sms->sendSMSPro($number, $msg, $msgto, $msgby);
                                if ($res) {
                                    $sq = "INSERT INTO campaign_email_sms_sent_details SET campaign_id = ".$campaignId.", submission_id = ".$sub.", state_id = ".$stateid." ,state_name='".$statename."', phone=".$number.", description='".stripslashes($body)."', pupilsightPersonID=".$cuid." ";
                                    $connection2->query($sq);
                                }
                            }
                        }    
                    }
                }
            }    
            
           die();

            
            
        }
        
        echo $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/campaignFormList.php&id='.$campaignId.'&search=';
               // header("Location: {$URL}");
                
       
    }
}

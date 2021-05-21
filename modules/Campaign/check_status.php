<?php
echo "<style>

.custom_row{
 border:1px solid black;
 margin:30px;

}
.custom_col{
    
    margin:10px;

}


</style>";
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

use Pupilsight\Services\Format;

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Admission\AdmissionGateway;


// Module includes
require_once __DIR__ . '/moduleFunctions.php';


if (isActionAccessible($guid, $connection2, '/modules/Campaign/check_status.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {

    // function getDomain()
    // {
    //     if (isset($_SERVER['HTTPS'])) {
    //         $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    //     } else {
    //         $protocol = 'http';
    //     }
    //     //return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    //     return $protocol . "://" . $_SERVER['HTTP_HOST'];
    // }
    // $baseurlforgigis = getDomain();
    
    $callbacklink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
        "https" : "http") . "://" . $_SERVER['HTTP_HOST'] .
        $_SERVER['REQUEST_URI'];

    //$baseurl = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];     

    $baseurl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
    $baseurl .= "://" . $_SERVER['HTTP_HOST'];
    $baseurl .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
    //echo $baseurl;

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    $roleId = $_SESSION[$guid]['pupilsightRoleIDPrimary'];

    $email = $_SESSION[$guid]['email'];
    //$phone = $_SESSION[$guid]['phone1'];
    
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    $admissionGateway = $container->get(AdmissionGateway::class);
    $criteria = $admissionGateway->newQueryCriteria()
        ->searchBy($admissionGateway->getSearchableColumns(), $search)
        ->sortBy(['id'])
        ->fromPOST();


    $form = Form::create('App_status', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/application_status.php')->addClass('newform');
    $form->setFactory(DatabaseFormFactory::create($pdo));


    // QUERY
    echo '<h2>';
    echo __('Application Status');
    echo '</h2>';
    //  print_r($criteria);

    $submissionId = '';
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
    $sql1 = 'Select GROUP_CONCAT(id) AS submission_id FROM wp_fluentform_submissions WHERE pupilsightPersonID = "' . $cuid . '" ';
    
    $resultval1 = $connection2->query($sql1);
    $submissionIdData = $resultval1->fetch();
    
    $submissionId = $submissionIdData['submission_id'];
    if(!empty($submissionId)){
        $submissionId = $submissionId;
    }   else {
        $submissionId = '0';
    }

    $dataSet = $admissionGateway->getApp_status($criteria, $submissionId, $cuid);
    $table = DataTable::createPaginated('userManage', $criteria);

    if($roleId == '004'){
        echo "<div style='height:50px;'><div class='float-right mb-2 contentPanel'><a href='index.php?q=%2Fmodules%2FCampaign%2Fparent_active_campaign.php' class='btn btn-primary'>Apply Here</a></div><div class='float-none'></div></div>";
    } else {
        echo "<div style='height:50px;'><div class='float-right mb-2 contentPanel'><a href='index.php?q=%2Fmodules%2FCampaign%2Factive_campaign.php' class='btn btn-primary'>Apply Here</a></div><div class='float-none'></div></div>";
    }
   

    // $table->addColumn('name', __('Campaign Name'))
    //     ->width('10%')
    //     ->translatable();

    // $table->addColumn('created_at', __('Submission Date'))
    //     ->width('10%')
    //     ->translatable();

    // $table->addColumn('state', __('Status'))
    //     ->width('10%')
    //     ->translatable();

    // echo $table->render($dataSet);

    echo "<div class ='row fee_hdr FeeInvoiceListManage contentPanel' id=''><div class='col-md-12'><h2>Applied Campaign</h2></div></div>";

    echo "<table class='table contentPanel' id='FeeInvoiceListManage'>";
    echo "<thead>";
    echo "<tr class='head'>";
    echo '<th>';
    echo __('Campaign Name');
    echo '</th>';
    echo '<th>';
    echo __('Submission Date');
    echo '</th>';
    echo '<th>';
    echo __('Status');
    echo '</th>';
    echo '<th>';
    echo __('Amount');
    echo '</th>';
    echo "<th>";
    echo __('Action');
    echo '</th>';
    echo "<th>";
    echo __('Form Download');
    echo '</th>';
    echo "<th>";
    echo __('Fee Receipt Download');
    echo '</th>';
    if(strpos($baseurl,"gigis")>-1){
        //if(strpos($baseurl,"localhost")>-1){
        echo "<th>";
        echo __('Term Fee Amount');
        echo '</th>';
        echo "<th>";
        echo __('Term Fee Status');
        echo '</th>';
        echo "<th>";
        echo __('Upload Fee Attachment');
        echo '</th>';
        echo "<th>";
        echo __('Student Contract Form');
        echo '</th>';
    }
    echo "</thead>";
    echo "<tbody id='getInvoiceFeeItem'>";
    
        // echo'<pre>';
        // print_r($dataSet);
        // die();
        $totalamountnew = '';
        foreach ($dataSet as $campstatus) {
            if(!empty($campstatus['subid'])){
                $workflowstate = $campstatus['workflowstate'];
                $sqla = "select application_id FROM wp_fluentform_submissions  where id = ".$campstatus['subid']." ";
                $resulta = $connection2->query($sqla);
                $applicationData = $resulta->fetch();
                $applicationId = $applicationData['application_id'];

                $invoices = 'SELECT fn_fee_invoice.*,fn_fee_invoice.id as invoiceid, g.fine_type, g.rule_type, fn_fee_invoice_applicant_assign.invoice_no as stu_invoice_no FROM fn_fee_invoice LEFT JOIN fn_fees_fine_rule AS g ON fn_fee_invoice.fn_fees_fine_rule_id = g.id LEFT JOIN fn_fee_invoice_applicant_assign ON fn_fee_invoice.id = fn_fee_invoice_applicant_assign.fn_fee_invoice_id  WHERE fn_fee_invoice.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" AND fn_fee_invoice_applicant_assign.submission_id = "'.$campstatus['subid'].'" ';
                $resultinv = $connection2->query($invoices);
                $invdata = $resultinv->fetch();
                if(!empty($invdata)){
                    $invoiceId = $invdata['invoiceid'];

                    $sqlamt = 'SELECT SUM(fn_fee_invoice_item.total_amount) as totalamount, group_concat(fn_fee_invoice_item.id) as fn_fee_invoice_item_id FROM fn_fee_invoice_item WHERE fn_fee_invoice_id = '.$invoiceId.' '; 
                    $resultamt = $connection2->query($sqlamt);
                    $dataamt = $resultamt->fetch();

                    $totalamount = $dataamt['totalamount'];
                    $finalamount = $totalamount;
                    $fn_fee_invoice_item_id = $dataamt['fn_fee_invoice_item_id'];

                    $date = date('Y-m-d');
                    $curdate = strtotime($date);
                    $duedate = strtotime($invdata['due_date']);
                    $fineId = $invdata['fn_fees_fine_rule_id'];

                    if(!empty($fineId) && $curdate > $duedate){
                        $finetype = $invdata['fine_type'];
                        $ruletype = $invdata['rule_type'];
                        if($finetype == '1' && $ruletype == '1'){
                            $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                            $resultf = $connection2->query($sqlf);
                            $finedata = $resultf->fetch();
                            $amtper = $finedata['amount_in_percent'];
                            $type = 'percent';
                        } elseif($finetype == '1' && $ruletype == '2'){
                            $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                            $resultf = $connection2->query($sqlf);
                            $finedata = $resultf->fetch();
                            $amtper = $finedata['amount_in_number'];
                            $type = 'num';
                        } elseif($finetype == '1' && $ruletype == '3'){
                            $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" AND from_date <= "'.$date.'" AND to_date >= "'.$date.'" ';
                            $resultf = $connection2->query($sqlf);
                            $finedata = $resultf->fetch();
                            if(!empty($finedata)){
                                if($finedata['amount_type'] == 'Fixed'){
                                    $amtper = $finedata['amount_in_number'];
                                    $type = 'num';
                                } else {
                                    $amtper = $finedata['amount_in_percent'];
                                    $type = 'percent';
                                }
                                
                            } else {
                                $amtper = '';
                                $type = '';
                            }
                        } elseif($finetype == '2' && $ruletype == '1'){
                            $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                            $resultf = $connection2->query($sqlf);
                            $finedata = $resultf->fetch();
                            $amtper = $finedata['amount_in_percent'];
                            $type = 'percent';
                        } elseif($finetype == '2' && $ruletype == '2'){
                            $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                            $resultf = $connection2->query($sqlf);
                            $finedata = $resultf->fetch();
                            $amtper = $finedata['amount_in_number'];
                            $type = 'num';
                        } elseif($finetype == '3' && $ruletype == '1'){
                            $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                            $resultf = $connection2->query($sqlf);
                            $finedata = $resultf->fetch();
                            $amtper = $finedata['amount_in_percent'];
                            $type = 'percent';
                        } elseif($finetype == '3' && $ruletype == '2'){
                            $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                            $resultf = $connection2->query($sqlf);
                            $finedata = $resultf->fetch();
                            $amtper = $finedata['amount_in_number'];
                            $type = 'num';
                        } elseif($finetype == '3' && $ruletype == '4'){
                            $date1 = strtotime($invdata['due_date']);  
                            $date2 = strtotime($date); 
                            $diff = abs($date2 - $date1);
                            $years = floor($diff / (365*60*60*24));  
                            $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));   
                            $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

                            $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" AND from_day <= "'.$days.'" AND to_day >= "'.$days.'" ';
                            $resultf = $connection2->query($sqlf);
                            $finedata = $resultf->fetch();
                            if($finedata['amount_type'] == 'Fixed'){
                                $amtper = $finedata['amount_in_number'];
                                $type = 'num';
                            } else {
                                $amtper = $finedata['amount_in_percent'];
                                $type = 'percent';
                            }
                        
                        } else {
                            $amtper = '';
                            $type = '';
                        }
                    } else {
                        $amtper = '';
                        $type = '';
                    }
                    //$invdata[$k]['amtper'] = $amtper;
                    //$invdata[$k]['type'] = $type;

                
                    $invid =  $invoiceId;
                    $invno =  $invdata['stu_invoice_no'];
                    $sqla = 'SELECT GROUP_CONCAT(a.fn_fee_invoice_item_id) AS invitemid, filename FROM fn_fees_applicant_collection AS a LEFT JOIN fn_fees_collection AS b ON  a.transaction_id = b.transaction_id WHERE a.invoice_no = "'.$invno.'" AND b.transaction_status = "1" ';
                    $resulta = $connection2->query($sqla);
                    $inv = $resulta->fetch();
                    // die();
                    if(!empty($inv['invitemid'])){
                        if(!empty($invdata['transport_schedule_id'])){
                            $invdata[$k]['paidamount'] = $totalamount;
                            $pendingamount = 0;
                            $pendingamount = $pendingamount;
                            $chkpayment = 'Paid';
                        } else {    
                            $itemids = $inv['invitemid'];
                            $sqlp = 'SELECT SUM(total_amount) as paidtotalamount FROM fn_fee_invoice_item WHERE id IN ('.$itemids.') ';
                            $resultp = $connection2->query($sqlp);
                            $amt = $resultp->fetch();
                            $totalpaidamt = $amt['paidtotalamount'];
                            if(!empty($totalpaidamt)){
                                $paidamount = $totalpaidamt;
                                $pendingamount = $totalamount- $totalpaidamt;
                                //$pendingamount = $pendingamount;
                                if($pendingamount == ''){
                                    $chkpayment = 'Paid';
                                } else {
                                    $chkpayment = 'Half Paid';
                                }
                                
                            } 
                        }
                    } else {
                        $paidamount = '0';
                        $pendingamount = $totalamount;
                        $pendingamount = $pendingamount;
                        $chkpayment = 'UnPaid';
                    }
                }
        
            }

            $sq = 'SELECT name as classname FROM pupilsightYearGroup WHERE pupilsightYearGroupID = "'.$campstatus['pupilsightYearGroupID'].'" ';
            $resultsq = $connection2->query($sq);
            $classname = $resultsq->fetch();

            //$sq2 = 'SELECT name as classname FROM pupilsightYearGroup WHERE pupilsightYearGroupID = "'.$campstatus['pupilsightYearGroupID'].'" ';
            //$resultsq2 = $connection2->query($sq);
            //$classname2 = $resultsq2->fetch();



            if(!empty($invdata)){

                $totalamountnew = $finalamount;
                $fineamount = 0;
                if (!empty($amtper)) {
                    if($type == 'percent'){
                        $fineamount = ($finalamount * $amtper) / 100;
                    } else {
                        $fineamount = $amtper;
                    }
                    $totalamountnew = $finalamount + $fineamount;
                }
                
                
                if ($chkpayment == 'Paid') {
                    $cls = 'Paid';
                    echo '<tr><td>' . $campstatus['name'] ." - ".$classname["classname"] . '</td><td>' . $campstatus['created_at'] . '</td><td>' . $workflowstate . '</td><td>' . $totalamountnew . '</td><td>&nbsp;&nbsp;Paid</td><td><a href="thirdparty/pdfgenerate/admission_pdflib.php?cid=' . $campstatus['campaign_id'] . '&submissionId='.$campstatus['subid'].'" title="Download Pdf Form " data-aid="'.$applicationId.'" data-id="'.$campstatus['subid'].'"><i title="Applied Form Download" class="mdi mdi-file-pdf mdi-24px download_icon"></i></a></td>';
                    $lin = $baseurl . "public/receipts/" . $invdata["filename"] . ".pdf";
                    echo "<td><a href='" . $lin . "' download><i title='Fee Receipt Download' class='mdi mdi-file-pdf mdi-24px download_icon'></i></a></td>";
                    if(strpos($baseurl,"gigis")>-1){
                        //if(strpos($baseurl,"localhost")>-1){
                        $sql = 'SELECT a.campaign_id, b.to_state, c.amount, c.fn_fee_structure_id, e.invoice_no FROM campaign_form_status AS a LEFT JOIN workflow_transition AS b ON a.state_id = b.id LEFT JOIN fn_fee_admission_settings AS c ON b.fn_fee_admission_setting_ids = c.id LEFT JOIN fn_fee_invoice AS d ON c.fn_fee_structure_id = d.fn_fee_structure_id LEFT JOIN fn_fee_invoice_applicant_assign AS e ON d.id = e.fn_fee_invoice_id WHERE a.submission_id = "'.$campstatus['subid'].'" AND a.state = "Generate Term Fee"  ';
                        $result = $connection2->query($sql);
                        $chkStData = $result->fetch();
                        $to_state = $chkStData['to_state'];
                        $campaign_id = $chkStData['campaign_id'];
                        $invoice_no = $chkStData['invoice_no'];

                        if(!empty($chkStData['amount'])){
                            $termamount = number_format((float)$chkStData['amount'], 2, '.', '');
                            echo '<td>'.$termamount.'</td>';

                            $sql = 'SELECT * FROM fn_fees_applicant_collection WHERE invoice_no= "'.$invoice_no.'" AND submission_id= "'.$campstatus['subid'].'" ';
                            $result = $connection2->query($sql);
                            $payData = $result->fetch();
                            if(!empty($payData)){
                                echo '<td>Paid</td>';
                            } else {
                                echo '<td></td>';
                            }
                            
                        } else {
                            echo '<td></td>';
                            echo '<td></td>';
                        }
                        
                        $sql = 'SELECT * FROM campaign_payment_attachment WHERE campaign_id= "'.$campstatus['campaign_id'].'" AND submission_id= "'.$campstatus['subid'].'" ';
                        $result = $connection2->query($sql);
                        $attachData = $result->fetch();
                        // if (!empty($attachData)) {
                        //     echo '<td><a href=" '. $attachData['pay_attachment'] .'"  title="Download Pay Receipt " download><i title="Uploaded Pay receipt" class="mdi mdi-file-pdf mdi-24px download_icon"></i></a></td>';
                        // } else {
                            echo '<td><a href="index.php?q=/modules/Campaign/pay_receipt_template_add.php&cid=' . $campstatus['campaign_id'] . '&sid='.$campstatus['subid'].'"" class=" btn btn-primary">Upload</a></td>';
                        // }

                        //if($campstatus['is_contract_generated'] != '1'){
                            $sql = 'SELECT field_value FROM wp_fluentform_entry_details WHERE field_name = "priority_contact" AND submission_id= "'.$campstatus['subid'].'" ';
                            $result = $connection2->query($sql);
                            $sdData = $result->fetch();
                            if($sdData == 'Mother'){
                                $sql1 = 'SELECT field_value FROM wp_fluentform_entry_details WHERE field_name = "mother_email" AND submission_id= "'.$campstatus['subid'].'" ';
                            } else {
                                $sql1 = 'SELECT field_value FROM wp_fluentform_entry_details WHERE field_name = "father_email" AND submission_id= "'.$campstatus['subid'].'" ';
                            }
                            $result1 = $connection2->query($sql1);
                            $sdEmailData = $result1->fetch();
                            $sdEmail = $sdEmailData['field_value'];
                            echo '<td><button href="contractForm.php?sid='.$campstatus['subid'].'" class=" btn btn-primary openOtp" data-sid="'.$campstatus['subid'].'" data-semail = "'.$sdEmail.'"  >Contract Form</button></td>';
                        // } else {
                        //     echo '<td>Contract Form Generated</td>';
                        // }
                    }
                    echo "</tr>";
                } else {

                    if(!empty($workflowstate)){
                        $statusCamp = $workflowstate;
                    } else { 
                        if ($campstatus['is_fee_generate'] == '2') {
                            $sql2 = "SELECT transaction_id FROM fn_fees_applicant_collection WHERE submission_id = " . $campstatus['subid'] . "  ";
                            $resulttr = $connection2->query($sql2);
                            $stateChk = $resulttr->fetch();
                            if (!empty($stateChk['transaction_id'])) {
                                $statusCamp = 'Submitted';
                            } else {
                                $statusCamp = 'Created';
                            }
                        } else {
                            $statusCamp = 'Submitted';
                        }
                    }
                    
                    echo '<tr><td>' . $campstatus['name'] ." - ".$classname["classname"] . '</td><td>' . $campstatus['created_at'] . '</td><td>' . $statusCamp . '</td><td>' . $totalamountnew . '</td>';

                    $sqlstu = 'SELECT field_value FROM wp_fluentform_entry_details WHERE submission_id = "'.$campstatus['subid'].'" AND (sub_field_name = "first_name" OR field_name = "student_name") ';
                    $resultstu = $connection2->query($sqlstu);
                    $studetails = $resultstu->fetch();

                    $fn_fees_head_id = $invdata['fn_fees_head_id'];
                    $sql = 'SELECT b.* FROM fn_fees_head AS a LEFT JOIN fn_fee_payment_gateway AS b ON a.payment_gateway_id = b.id WHERE a.id = '.$fn_fees_head_id.' ';
                   // die();
                    $result = $connection2->query($sql);
                    $gatewayData = $result->fetch();
                    $terms = $gatewayData['terms_and_conditions'];
                    $gatewayID = $gatewayData['id'];

                    
                    
                if($gatewayData['name'] == 'RAZORPAY'){
                    ?>
                    <td>
                        <form action="thirdparty/applicantpayment/razorpay/pay.php" method="post">
                            <input type="hidden" name="payment_gateway_id" value="<?= $gatewayID ?>">
                            <input type="hidden" name="pupilsightSchoolYearID" value="<?= $invdata['pupilsightSchoolYearID'] ?>">
                            <input type="hidden" name="classid" value="<?=$campstatus['pupilsightYearGroupID'] ?>">
                            <input type="hidden" name="sectionid" value="">
                            <input type="hidden" name="fn_fees_invoice_id" value="<?= $invoiceId ?>">
                            <input type="hidden" name="fn_fees_head_id" value="<?= $invdata['fn_fees_head_id'] ?>">
                            <input type="hidden" name="rec_fn_fee_series_id" value="<?= $invdata['rec_fn_fee_series_id'] ?>">
                            <input type="hidden" name="fn_fee_invoice_item_id" value="<?= $fn_fee_invoice_item_id ?>">

                            <input type="hidden" name="total_amount_without_fine_discount" value="<?= $finalamount ?>">
                            <input type="hidden" name="amount" value="<?= $totalamountnew ?>">
                            <input type="hidden" name="fine" value="<?= $fineamount ?>">
                            <input type="hidden" name="discount" value="0">
                            
                            <input type="hidden" name="receipt_number" value="<?= $invdata['rec_fn_fee_series_id'] ?>">
                            <input type="hidden" name="name" value="<?= $studetails['field_value'] ?>">
                            <input type="hidden" name="email" value="<?= $campstatus['email'] ?>">
                            <input type="hidden" name="phone" value="<?= $campstatus['phone1'] ?>">
                            <input type="hidden" name="payid" value="<?= $invdata['stu_invoice_no'] ?>">
                            <input type="hidden" name="stuid" value="<?= $campstatus['subid'] ?>">
                            <input type="hidden" name="callbackurl" value="<?= $callbacklink ?>">
                            <button type="submit" class="btn btn-primary">Pay</button>
                        </form> 
                    </td>
                <?php } elseif($gatewayData['name'] == 'PAYU'){ ?>
                    <td>
                        <form action="thirdparty/payment/payu/checkout_parent.php" method="post">
                            <input type="hidden" name="payment_gateway_id" value="<?= $gatewayID ?>">
                            <input type="hidden" name="pupilsightSchoolYearID" value="<?= $invdata['pupilsightSchoolYearID'] ?>">
                            <input type="hidden" name="classid" value="<?=$campstatus['pupilsightYearGroupID'] ?>">
                            <input type="hidden" name="sectionid" value="">
                            <input type="hidden" name="fn_fees_invoice_id" value="<?= $invoiceId ?>">
                            <input type="hidden" name="fn_fees_head_id" value="<?= $invdata['fn_fees_head_id'] ?>">
                            <input type="hidden" name="rec_fn_fee_series_id" value="<?= $invdata['rec_fn_fee_series_id'] ?>">
                            <input type="hidden" name="fn_fee_invoice_item_id" value="<?= $fn_fee_invoice_item_id ?>">

                            <input type="hidden" name="total_amount_without_fine_discount" value="<?= $finalamount ?>">
                            <input type="hidden" name="amount" value="<?= $totalamountnew ?>">
                            <input type="hidden" name="fine" value="<?= $fineamount ?>">
                            <input type="hidden" name="discount" value="0">
                            
                            <input type="hidden" name="receipt_number" value="<?= $invdata['rec_fn_fee_series_id'] ?>">
                            <input type="hidden" name="name" value="<?= $studetails['field_value'] ?>">
                            <input type="hidden" name="email" value="<?= $campstatus['email'] ?>">
                            <input type="hidden" name="phone" value="<?= $campstatus['phone1'] ?>">
                            <input type="hidden" name="payid" value="<?= $invdata['stu_invoice_no'] ?>">
                            <input type="hidden" name="stuid" value="<?= $campstatus['subid'] ?>">
                            <input type="hidden" name="callbackurl" value="<?= $callbacklink ?>">
                            <button type="submit" class="btn btn-primary">Pay</button>
                        </form> 
                    </td>

                <?php } elseif($gatewayData['name'] == 'AIRPAY'){ 
                        $random_number = mt_rand(1000, 9999);
                        $today = time();
                        $orderId = $today . $random_number;

                        $totalamountnewAirpay = number_format($totalamountnew, 2, '.', '');
                    ?>
                    <td>
                        <form action="thirdparty/payment/airpay/sendtoairpay.php" method="post">
                            <input type="hidden" name="payment_gateway_id" value="<?= $gatewayID ?>">
                            <input type="hidden" value="<?= $orderId; ?>" id="OrderId" name="orderid">
                            <input type="hidden" name="pupilsightSchoolYearID" value="<?= $invdata['pupilsightSchoolYearID'] ?>">
                            <input type="hidden" name="classid" value="<?=$campstatus['pupilsightYearGroupID'] ?>">
                            <input type="hidden" name="sectionid" value="">
                            <input type="hidden" name="fn_fees_invoice_id" value="<?= $invoiceId ?>">
                            <input type="hidden" name="fn_fees_head_id" value="<?= $invdata['fn_fees_head_id'] ?>">
                            <input type="hidden" name="rec_fn_fee_series_id" value="<?= $invdata['rec_fn_fee_series_id'] ?>">
                            <input type="hidden" name="fn_fee_invoice_item_id" value="<?= $fn_fee_invoice_item_id ?>">

                            <input type="hidden" name="total_amount_without_fine_discount" value="<?= $finalamount ?>">
                            <input type="hidden" name="amount" value="<?= $totalamountnewAirpay ?>">
                            <input type="hidden" name="fine" value="<?= $fineamount ?>">
                            <input type="hidden" name="discount" value="0">
                            
                            <input type="hidden" name="receipt_number" value="<?= $invdata['rec_fn_fee_series_id'] ?>">
                            <input type="hidden" name="name" value="<?= $studetails['field_value'] ?>">
                            <input type="hidden" name="buyerFirstName" value="<?= $studetails['field_value'] ?>">
                            <input type="hidden" name="buyerLastName" value="<?= $studetails['field_value'] ?>">
                            <input type="hidden" name="buyerEmail" value="<?= $campstatus['email'] ?>">
                            <input type="hidden" name="buyerPhone" value="<?= $campstatus['phone1'] ?>">
                            <input type="hidden" name="payid" value="<?= $invdata['stu_invoice_no'] ?>">
                            <input type="hidden" name="stuid" value="<?= $campstatus['subid'] ?>">
                            <input type="hidden" name="callbackurl" value="<?= $callbacklink ?>">

                            <input type="hidden" class="buyerAddress" name="buyerAddress" value="">
                            <input type="hidden" class="buyerCity" name="buyerCity" value="">
                            <input type="hidden" class="buyerState" name="buyerState" value="">
                            <input type="hidden" class="buyerPinCode" name="buyerPinCode" value="">
                            <input type="hidden" class="buyerCountry" name="buyerCountry" value="">
                            <input type="hidden" class="ptype" name="ptype" value="parent_admission">
                            <button type="submit" class="btn btn-primary">Pay</button>
                        </form> 
                    </td>
                <?php } ?>
                <?php
                    // echo '<td><a href="javascript:void(0)" class="download_form" title="Download Pdf Form " data-aid="'.$applicationId.'"  data-id="'.$campstatus['subid'].'"><i title="Applied Form Download" class="mdi mdi-file-pdf mdi-24px download_icon"></i></a></td>';
                    echo '<td><a href="thirdparty/pdfgenerate/admission_pdflib.php?cid=' . $campstatus['campaign_id'] . '&submissionId='.$campstatus['subid'].'"  title="Download Pdf Form " data-aid="'.$applicationId.'"  data-id="'.$campstatus['subid'].'"><i title="Applied Form Download" class="mdi mdi-file-pdf mdi-24px download_icon"></i></a></td>';
                    echo "<td>NA</td>";
                    if(strpos($baseurl,"gigis")>-1){
                    //if(strpos($baseurl,"localhost")>-1){
                        $sql = 'SELECT a.campaign_id, b.to_state, c.amount, c.fn_fee_structure_id, e.invoice_no FROM campaign_form_status AS a LEFT JOIN workflow_transition AS b ON a.state_id = b.id LEFT JOIN fn_fee_admission_settings AS c ON b.fn_fee_admission_setting_ids = c.id LEFT JOIN fn_fee_invoice AS d ON c.fn_fee_structure_id = d.fn_fee_structure_id LEFT JOIN fn_fee_invoice_applicant_assign AS e ON d.id = e.fn_fee_invoice_id WHERE a.submission_id = "'.$campstatus['subid'].'" AND a.state = "Generate Term Fee"  ';
                        $result = $connection2->query($sql);
                        $chkStData = $result->fetch();
                        $to_state = $chkStData['to_state'];
                        $campaign_id = $chkStData['campaign_id'];
                        $invoice_no = $chkStData['invoice_no'];

                        if(!empty($chkStData['amount'])){
                            $termamount = number_format((float)$chkStData['amount'], 2, '.', '');
                            echo '<td>'.$termamount.'</td>';

                            $sql = 'SELECT * FROM fn_fees_applicant_collection WHERE invoice_no= "'.$invoice_no.'" AND submission_id= "'.$campstatus['subid'].'" ';
                            $result = $connection2->query($sql);
                            $payData = $result->fetch();
                            if(!empty($payData)){
                                echo '<td>Paid</td>';
                            } else {
                                echo '<td></td>';
                            }
                            
                        } else {
                            echo '<td></td>';
                            echo '<td></td>';
                        }
                        $sql = 'SELECT * FROM campaign_payment_attachment WHERE campaign_id= "'.$campstatus['campaign_id'].'" AND submission_id= "'.$campstatus['subid'].'" ';
                        $result = $connection2->query($sql);
                        $attachData = $result->fetch();
                        // if (!empty($attachData)) {
                        //     echo '<td><a href=" '. $attachData['pay_attachment'] .'"  title="Download Pay Receipt " download><i title="Uploaded Pay receipt" class="mdi mdi-file-pdf mdi-24px download_icon"></i></a></td>';
                        // } else {
                            echo '<td><a href="index.php?q=/modules/Campaign/pay_receipt_template_add.php&cid=' . $campstatus['campaign_id'] . '&sid='.$campstatus['subid'].'"" class=" btn btn-primary">Upload</a></td>';
                        // }
                        echo '<td></td>';
                    }
                    echo "</tr>";
                }
                //echo '<tr><td>'.$ind['officialName'].'</td><td>'.$ind['stu_invoice_no'].'</td><td>'.$ind['title'].'</td><td>'.$ind['totalamount'].'</td><td>'.$ind['pendingamount'].'</td><td>'.$cls.'</td></tr>';
            } else {
                
                if(!empty($workflowstate)){
                    $cmstatus = $workflowstate;
                } else {
                    $cmstatus="Submitted";
                    if($campstatus['state']){
                        $cmstatus=$campstatus['state'];
                    }
                }
                echo '<tr>
                <td>' . $campstatus['name'] ." - ".$classname["classname"]. '</td>
                <td>' . $campstatus['created_at'] . '</td>
                <td>' . $cmstatus . '</td>
                <td>' . $totalamountnew . '</td>
                <td>NA</td>
                <td><a href="thirdparty/pdfgenerate/admission_pdflib.php?cid=' . $campstatus['campaign_id'] . '&submissionId='.$campstatus['subid'].'" title="Download Pdf Form " data-aid="'.$applicationId.'"  data-id="'.$campstatus['subid'].'"><i title="Applied Form Download" class="mdi mdi-file-pdf mdi-24px download_icon"></i></a></td><td>NA</td>';
                if(strpos($baseurl,"gigis")>-1){
                    //if(strpos($baseurl,"localhost")>-1){
                    $sql = 'SELECT a.campaign_id, b.to_state, c.amount, c.fn_fee_structure_id, e.invoice_no FROM campaign_form_status AS a LEFT JOIN workflow_transition AS b ON a.state_id = b.id LEFT JOIN fn_fee_admission_settings AS c ON b.fn_fee_admission_setting_ids = c.id LEFT JOIN fn_fee_invoice AS d ON c.fn_fee_structure_id = d.fn_fee_structure_id LEFT JOIN fn_fee_invoice_applicant_assign AS e ON d.id = e.fn_fee_invoice_id WHERE a.submission_id = "'.$campstatus['subid'].'" AND a.state = "Generate Term Fee"  ';
                    $result = $connection2->query($sql);
                    $chkStData = $result->fetch();
                    $to_state = $chkStData['to_state'];
                    $campaign_id = $chkStData['campaign_id'];
                    $invoice_no = $chkStData['invoice_no'];

                    if(!empty($chkStData['amount'])){
                        $termamount = number_format((float)$chkStData['amount'], 2, '.', '');
                        echo '<td>'.$termamount.'</td>';

                        $sql = 'SELECT * FROM fn_fees_applicant_collection WHERE invoice_no= "'.$invoice_no.'" AND submission_id= "'.$campstatus['subid'].'" ';
                        $result = $connection2->query($sql);
                        $payData = $result->fetch();
                        if(!empty($payData)){
                            echo '<td>Paid</td>';
                        } else {
                            echo '<td></td>';
                        }
                        
                    } else {
                        echo '<td></td>';
                        echo '<td></td>';
                    }
                    $sql = 'SELECT * FROM campaign_payment_attachment WHERE campaign_id= "'.$campstatus['campaign_id'].'" AND submission_id= "'.$campstatus['subid'].'" ';
                    $result = $connection2->query($sql);
                    $attachData = $result->fetch();
                    // if (!empty($attachData)) {
                    //     echo '<td><a href=" '. $attachData['pay_attachment'] .'"  title="Download Pay Receipt " download><i title="Uploaded Pay receipt" class="mdi mdi-file-pdf mdi-24px download_icon"></i></a></td>';
                    // } else {
                        echo '<td><a href="index.php?q=/modules/Campaign/pay_receipt_template_add.php&cid=' . $campstatus['campaign_id'] . '&sid='.$campstatus['subid'].'"" class=" btn btn-primary">Upload</a></td>';
                    // }
                    echo '<td></td>';
                }
                echo "</tr>";
            }
            
        }
    
    echo "</tbody>";
    echo '</table>';
    
}
?>

<div id="otpPanel" class="container-tight py-6">

            <!-- <form action="../login.php?" class="card card-md needs-validation" novalidate="" method="post" autocomplete="off"> -->
        <div class="card">
            <div class="card-body">
                
                <div class="text-center my-3">
                    <img src="<?= $logo ?>" height="50" alt="">
                </div>
                <h2 class="mb-3 text-center">Verify OTP</h2>
                <div class="empty-warning" id='otpVerify'></div>

                <div class="mb-3">
                    <label class="form-label">Enter OTP</label>
                    <input type="text" id="otp" value="" name="otp" class="form-control" required="">
                    <div class="invalid-feedback">Invalid OTP</div>
                </div>

                <div class="mt-2">
                    <button class="btn btn-primary validateOtp" type="button" data-sid="">Validate</button>
                </div>

                <!-- <div class="mt-2 text-right">
                    <button class="btn btn-link" type="button" onclick="openOtp();">Resent Otp</button>   
                </div> -->

            </div>
            </div>

</div>

<script>
    document.body.style.display = "block";
    $(document).ready(function() {
        $("#otpPanel").hide();
    });

    var otpLimit = 0;
    $(document).on('click', '.openOtp', function (e) {
        var sid = $(this).attr('data-sid');
        var semail = $(this).attr('data-semail');
        //function openOtp(sid,email){
        //alert(sid);
        if(otpLimit>10){
            alert("Your account is locked due to multiple otp failed.");
            return;
        }
        otpLimit++;
        $(".validateOtp").attr('data-sid',sid);
        $(".contentPanel").hide(400);
        $("#otpPanel").show(400);
        alert("A Otp email is sent on ur mail id ["+semail+"].Please verify the same.");
        try{
            $.ajax({
                url: 'contact_form_mail_send.php',
                type: 'post',
                data: { to: semail },
                async: true,
                success: function (response) {
                    
                }
            });
        }catch(ex){
            console.log(ex);
        }
    });

    $(document).on('click', '.validateOtp', function (e) {
    //function validateOtp(){
        var sid = $(this).attr('data-sid');
        //var stu_name = '<?=$dt["student_name"];?>';
        var val = $("#otp").val();
        if(val==""){
            alert("Invalid OTP");
            return;
        }
        //ajax session
        try{
            $("#preloader").show();
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { otp: val, val: val, type:"checkOtpForContractForm"},
                async: true,
                success: function (response) {
                    if(response=="success"){
                        window.location.href = 'contractForm.php?sid='+sid;
                        // $.ajax({
                        //     url: 'student_contract_mail_send.php',
                        //     type: 'post',
                        //     data: { sid: sid, stu_name:stu_name },
                        //     async: true,
                        //     success: function (response) {
                        //         $("#preloader").hide();
                        //         alert("Your contract form submitted successfully");
                        //         window.location.href = 'index.php?q=/modules/Campaign/check_status.php';
                        //     }
                        // });
                        
                    }else{
                        $("#preloader").hide();
                        alert("Invalid OTP");
                    }
                }
            });
        }catch(ex){
            console.log(ex);
        }
    });
</script>

<script type="text/javascript">
    $(document).on('click','.download_form',function(){
        var id = $(this).attr('data-id');
        var aid = $(this).attr('data-aid');
        var fname = '';
        if(aid != ''){
            fname = aid;
        } else {
            fname = id;
        }
        var link = document.createElement('a');
        link.href = "public/applicationpdf/parent/"+fname+".pdf";
        link.download = fname+".pdf";
        link.click();
    });
</script>
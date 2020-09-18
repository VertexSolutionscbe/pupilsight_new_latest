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
    
    $callbacklink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
        "https" : "http") . "://" . $_SERVER['HTTP_HOST'] .
        $_SERVER['REQUEST_URI'];

    //$baseurl = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];     

    $baseurl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
    $baseurl .= "://" . $_SERVER['HTTP_HOST'];
    $baseurl .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

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
    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=%2Fmodules%2FCampaign%2Factive_campaign.php' class='btn btn-primary'>Apply Here</a></div><div class='float-none'></div></div>";

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

    echo "<div class ='row fee_hdr FeeInvoiceListManage'><div class='col-md-12'><h2>Applied Campaign</h2></div></div>";

    echo "<table class='table' id='FeeInvoiceListManage'>";
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
    echo __('Download');
    echo '</th>';
    echo "</thead>";
    echo "<tbody id='getInvoiceFeeItem'>";
    
        //echo'<pre>';
        //print_r($dataSet);
        //die();

        foreach ($dataSet as $campstatus) {
            if(!empty($campstatus['subid'])){
               

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
                    $sqla = 'SELECT GROUP_CONCAT(a.fn_fee_invoice_item_id) AS invitemid FROM fn_fees_applicant_collection AS a LEFT JOIN fn_fees_collection AS b ON  a.transaction_id = b.transaction_id WHERE a.invoice_no = "'.$invno.'" AND b.transaction_status = "1" ';
                    $resulta = $connection2->query($sqla);
                    $inv = $resulta->fetch();
                    
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
                    echo '<tr><td>' . $campstatus['name'] . '</td><td>' . $campstatus['created_at'] . '</td><td>' . $campstatus['state'] . '</td><td>' . $totalamountnew . '</td><td>&nbsp;&nbsp;Paid</td>';
                    $lin = $baseurl . "public/receipts/" . $ind["transaction_id"] . ".docx";
                    echo "<td><a href='" . $lin . "' download>Download</a></td></tr>";
                } else {
                    
                    echo '<tr><td>' . $campstatus['name'] . '</td><td>' . $campstatus['created_at'] . '</td><td>' . $campstatus['state'] . '</td><td>' . $totalamountnew . '</td>';

                    $sqlstu = 'SELECT field_value FROM wp_fluentform_entry_details WHERE submission_id = "'.$campstatus['subid'].'" AND sub_field_name = "first_name" ';
                    $resultstu = $connection2->query($sqlstu);
                    $studetails = $resultstu->fetch();


                    
    ?>
                    <td>
                        <form action="thirdparty/applicantpayment/razorpay/pay.php" method="post">
                            <input type="hidden" name="pupilsightSchoolYearID" value="<?= $ind['pupilsightSchoolYearID'] ?>">
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
                            
                            <input type="hidden" name="receipt_number" value="<?= $invdata['stu_invoice_no'] ?>">
                            <input type="hidden" name="name" value="<?= $studetails['field_value'] ?>">
                            <input type="hidden" name="email" value="<?= $campstatus['email'] ?>">
                            <input type="hidden" name="phone" value="<?= $campstatus['phone1'] ?>">
                            <input type="hidden" name="payid" value="<?= $invdata['stu_invoice_no'] ?>">
                            <input type="hidden" name="stuid" value="<?= $campstatus['subid'] ?>">
                            <input type="hidden" name="callbackurl" value="<?= $callbacklink ?>">
                            <button type="submit">Pay</button>
                        </form> 
                    </td>
            <?php
                    echo "<td>NA</td></tr>";
                }
                //echo '<tr><td>'.$ind['officialName'].'</td><td>'.$ind['stu_invoice_no'].'</td><td>'.$ind['title'].'</td><td>'.$ind['totalamount'].'</td><td>'.$ind['pendingamount'].'</td><td>'.$cls.'</td></tr>';
            } else {
                $cmstatus="Processing";
                if($campstatus['state']){
                    $cmstatus=$campstatus['state'];
                }
                echo '<tr>
                <td>' . $campstatus['name'] ." - ".$classname["classname"]. '</td>
                <td>' . $campstatus['created_at'] . '</td>
                <td>' . $cmstatus . '</td>
                <td>' . $totalamountnew . '</td>
                <td></td>
                <td><a href="javascript:void(0)" class="download_form" title="Download Pdf Form " data-id="'.$campstatus['subid'].'">Download</a></td>';
            }
        }
    
    echo "</tbody>";
    echo '</table>';
    
}
?>
<script type="text/javascript">
    $(document).on('click','.download_form',function(){
      var id = $(this).attr('data-id');
    var link = document.createElement('a');
    link.href = "public/applicationpdf/"+id+"-application.pdf";
    link.download = id+".pdf";
    link.click();
    });
</script>
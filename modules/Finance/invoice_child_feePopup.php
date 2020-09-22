<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_child_view.php') == false) {
    //Access denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Fee Items'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }


     $session = $container->get('session');
    $id = $session->get('invoiceID_parent');
 

    $callbacklink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
        "https" : "http") . "://" . $_SERVER['HTTP_HOST'] .
        $_SERVER['REQUEST_URI'];

    //$baseurl = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];     

    $baseurl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
    $baseurl .= "://" . $_SERVER['HTTP_HOST'];
    $baseurl .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);

    $FeesGateway = $container->get(FeesGateway::class);
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
    $childs = 'SELECT b.pupilsightPersonID, b.officialName FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID2 = b.pupilsightPersonID WHERE a.pupilsightPersonID1 = ' . $cuid . ' GROUP BY a.pupilsightPersonID1 LIMIT 0,1';
    $resulta = $connection2->query($childs);
    $students = $resulta->fetch();

    $stuId = $students['pupilsightPersonID'];

    // QUERY
    $criteria = $FeesGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();
    $invoices = 'SELECT fn_fee_invoice.*,fn_fee_invoice.id as invoiceid, fn_fee_invoice_student_assign.invoice_no as stu_invoice_no, g.fine_type, g.name AS fine_name, g.rule_type, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype, pupilsightPerson.officialName , pupilsightPerson.email, pupilsightPerson.phone1, pupilsightStudentEnrolment.pupilsightYearGroupID as classid, pupilsightStudentEnrolment.pupilsightRollGroupID as sectionid FROM fn_fee_invoice LEFT JOIN pupilsightStudentEnrolment ON fn_fee_invoice.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID LEFT JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID RIGHT JOIN fn_fee_invoice_student_assign ON pupilsightPerson.pupilsightPersonID=fn_fee_invoice_student_assign.pupilsightPersonID AND fn_fee_invoice.id = fn_fee_invoice_student_assign.fn_fee_invoice_id LEFT JOIN fn_fees_fine_rule AS g ON fn_fee_invoice.fn_fees_fine_rule_id = g.id LEFT JOIN trans_route_assign AS asg ON pupilsightPerson.pupilsightPersonID = asg.pupilsightPersonID WHERE pupilsightPerson.pupilsightPersonID = "' . $stuId . '" GROUP BY fn_fee_invoice.id';

    $resultinv = $connection2->query($invoices);
    $invdata = $resultinv->fetchAll();

    // echo '<pre>';
    // print_r($invdata);
    // echo '</pre>';
    // die();

   
    $sid = $stuId;

    $sqli = 'SELECT e.pupilsightPersonID,a.*,a.id as itemid, b.*, b.id as ifid, b.name as feeitemname, c.id AS invoiceid, c.transport_schedule_id, d.format, e.invoice_no as stu_invoice_no, f.item_type, f.name, f.min_invoice, f.max_invoice, f.amount_in_percent, f.amount_in_number, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype  FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_items AS b ON a.fn_fee_item_id = b.id LEFT JOIN fn_fee_invoice AS c ON a.fn_fee_invoice_id = c.id LEFT JOIN fn_fee_series AS d ON c.inv_fn_fee_series_id = d.id LEFT JOIN fn_fee_invoice_student_assign AS e ON c.id = e.fn_fee_invoice_id  LEFT JOIN fn_fee_discount_item as f ON c.fn_fees_discount_id = f.fn_fees_discount_id AND a.fn_fee_item_id = f.fn_fee_item_id LEFT JOIN trans_route_assign AS asg ON e.pupilsightPersonID = asg.pupilsightPersonID WHERE a.fn_fee_invoice_id IN (' . $id . ') AND e.pupilsightPersonID = ' . $sid . ' GROUP BY a.id';
    $resulti = $connection2->query($sqli);
    $feeItem = $resulti->fetchAll();

    $data = '';
 


    echo "<div class ='row fee_hdr FeeInvoiceListManage'><div class='col-md-12'><h2>Bill Details</h2></div></div>";

 
    echo "<table class='table' cellspacing='0' style='width: 100%; id='FeeItemManage' class='oCls_0 oClose'>";
    echo "<thead>";
    echo "<tr class='head'>";
   
    echo '<th>';
    echo __('Fee Item');
    echo '</th>'; 
    //echo '<th>';
    //echo __('Invoice No');
    //echo '</th>';
    echo "<th>";
    echo __('Amount');
    echo '</th>';
    echo '<th>';
    echo __('Tax');
    echo '</th>';
    echo '<th>';
    echo __('Amount with Tax');
    echo '</th>';
    echo '<th>';
    echo __('Discount');
    echo '</th>';
    echo "<th>";
    echo __('Amount Discounted');
    echo '</th>';
    echo '<th>';
    echo __('Final Amount');
    echo '</th>';
    echo '<th>';
    echo __('Amount Paid');
    echo '</th>';
    echo '<th>';
    echo __('Amount Pending');
    echo '</th>';
   
    echo '</tr>';
    echo "</thead>";
  //  echo "<tbody id='getInvoiceFeeItem'>";
  $data = '';
//   echo '<pre>';
//   print_r($feeItem);
//   echo '</pre>';
  foreach ($feeItem as $fI) {
    if (!empty($fI['transport_schedule_id'])) {
        $routes = explode(',', $fI['routes']);
        foreach ($routes as $rt) {
            $sqlsc = 'SELECT * FROM trans_route_price WHERE schedule_id = ' . $fI['transport_schedule_id'] . ' AND route_id = ' . $rt . ' ';
            $resultsc = $connection2->query($sqlsc);
            $datasc = $resultsc->fetch();
            if ($fI['routetype'] == 'oneway') {
                $price = $datasc['oneway_price'];
                $tax = $datasc['tax'];
                $amtperc = ($tax / 100) * $price;
                $tranamount = $price + $amtperc;
            } else {
                $price = $datasc['twoway_price'];
                $tax = $datasc['tax'];
                $amtperc = ($tax / 100) * $price;
                $tranamount = $price + $amtperc;
            }
        }
        $totalamount = $tranamount;
    } else {
        $totalamount = $fI['total_amount'];
    }

// print_r($totalamount);die();
    $sqlchk = 'SELECT COUNT(a.id) as kount,amount_paying FROM fn_fees_student_collection AS a LEFT JOIN fn_fees_collection AS b ON a.transaction_id = b.transaction_id WHERE a.fn_fee_invoice_item_id = ' . $fI['itemid'] . ' AND a.pupilsightPersonID = ' . $sid . ' AND b.transaction_status = "1" ';

    $resultchk = $connection2->query($sqlchk);
    $itemchk = $resultchk->fetch();

    if ($itemchk['kount'] == '1') {
        $cls = '';
        $checked = 'checked disabled';
    } else {
        $cls = 'selFeeItem';
        $checked = '';
    }

    $inid = '000'.$id;
    $invno = str_replace("0001",$inid,$fI['format']);
    if ($fI['item_type'] == 'Fixed') {
        $discount = $fI['amount_in_number'];
        $discountamt = $fI['amount_in_number'];
    } else {
        $discount = $fI['amount_in_percent'] . '%';
        $discountamt = ($fI['amount_in_percent'] / 100) * $totalamount;
    }
    $amtdiscount = $totalamount - $discountamt;
    $pendding = $totalamount - $itemchk['amount_paying'];
    if($pendding<0){
    $pendding=abs($pendding)."(Fine paid)";
    }
    $data .= '<tr class="odd invrow' . $id . '" role="row">
     
         
        <td class="p-2 sm:p-3">
           ' . $fI['feeitemname'] . '     
        </td>
         
        
        <td class="p-2 sm:p-3 hidden-1 md:table-cell">
        ' . $fI['amount'] . '  
        </td>
         
        <td class="p-2 sm:p-3 hidden-1 md:table-cell">
        ' . $fI['tax'] . '% 
        </td>
         
        <td class="p-2 sm:p-3 hidden-1 md:table-cell">
        ' . $totalamount . '   
        </td>
         
        <td class="p-2 sm:p-3 hidden-1 md:table-cell">
        ' . $discount . '     
        </td>
         
        <td class="p-2 sm:p-3 hidden-1 md:table-cell">
           ' . $discountamt . '
        </td>
        <td class="p-2 sm:p-3 hidden-1 md:table-cell">
        ' . $amtdiscount . '   
        </td>
        <td class="p-2 sm:p-3 hidden-1 md:table-cell">
            '.$itemchk['amount_paying'].'
        </td>
         
        <td class="p-2 sm:p-3 hidden-1 md:table-cell">
           '.$pendding.'
        </td>
       
         
    </tr>';
}
echo $data;
  
}
    echo '</table>';    
    ?>
    
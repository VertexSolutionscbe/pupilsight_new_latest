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
    $page->breadcrumbs->add(__('Manage Invoice'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $callbacklink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
        "https" : "http") . "://" . $_SERVER['HTTP_HOST'] .
        $_SERVER['REQUEST_URI'];

    //$baseurl = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];     

    $baseurl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
    $baseurl .= "://" . $_SERVER['HTTP_HOST'];
    $baseurl .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);

    $FeesGateway = $container->get(FeesGateway::class);

    $cuid = $_SESSION[$guid]['pupilsightPersonID'];

    $sqlf = 'SELECT pupilsightFamilyID FROM pupilsightFamilyAdult WHERE pupilsightPersonID= ' . $cuid . ' ';
    $resultf = $connection2->query($sqlf);
    $fdata = $resultf->fetch();
    $pupilsightFamilyID = $fdata['pupilsightFamilyID'];

    // $childs = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID2 = b.pupilsightPersonID WHERE a.pupilsightPersonID1 = ' . $cuid . ' GROUP BY a.pupilsightPersonID1 LIMIT 0,1';

    if(!empty($_GET['cid'])){
        $chkchilds = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID = '.$_GET['cid'].' ';
        $resultachk = $connection2->query($chkchilds);
        $chkstuData = $resultachk->fetch();

        if(!empty($chkstuData)){
            $childs = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID != "" ';
            $resulta = $connection2->query($childs);
            $stuData = $resulta->fetchAll();

            $students = $chkstuData;
            $stuId = $_GET['cid'];
        } else {
            echo '<h1>No Child</h1>';
        }
    } else {
        $childs = 'SELECT b.pupilsightPersonID, b.officialName, b.email, b.phone1 FROM pupilsightFamilyChild AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightFamilyID = ' . $pupilsightFamilyID . ' AND a.pupilsightPersonID != "" ';
        $resulta = $connection2->query($childs);
        $stuData = $resulta->fetchAll();
        $students = $stuData[0];
        $stuId = $students['pupilsightPersonID'];
    }
    
    //$students = $resulta->fetchAll();
    // echo '<pre>';
    // print_r($students);
    // echo '</pre>';
    // die();
    $parents = 'SELECT email, phone1 FROM pupilsightPerson WHERE pupilsightPersonID = ' . $cuid . ' ';
    $resultp = $connection2->query($parents);
    $parData = $resultp->fetch();


    

    // QUERY
    $criteria = $FeesGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    if($_GET['success'] == '1'){
        echo '<h3 style="color:light-green;color: green;border: 1px solid grey;text-align: center;padding: 5px 5px;">Payment Succesfully Done!</h3>';
    }

    $tab = '';
    if(!empty($stuData) && count($stuData) > 1){
        $tab = '<div style="display:inline-flex;width:25%"><span style="width:25%">Child : </span><select id="childSel" class="form-control" style="width:100%">';
        foreach($stuData as $stu){
            $selected = '';
            if(!empty($_GET['cid'])){
                if($_GET['cid'] == $stu['pupilsightPersonID']){
                    $selected = 'selected';
                }
            }
            $tab .=  '<option value='.$stu['pupilsightPersonID'].'  '.$selected.'>'.$stu['officialName'].'</option>';
        }
        $tab .=  '</select></div>';
    }
    echo $tab;
    // die();

    if(!empty($stuId)){
        $feeheadsql = 'SELECT fn_fee_invoice.fn_fees_head_id, fn_fee_invoice_student_assign.invoice_no  FROM fn_fee_invoice_student_assign LEFT JOIN fn_fee_invoice ON fn_fee_invoice_student_assign.fn_fee_invoice_id = fn_fee_invoice.id WHERE fn_fee_invoice_student_assign.pupilsightPersonID = ' . $stuId . ' AND fn_fee_invoice_student_assign.invoice_status != "Fully Paid" AND fn_fee_invoice_student_assign.status = "1" GROUP BY fn_fee_invoice.fn_fees_head_id';
        $resultfh = $connection2->query($feeheadsql);
        $feeHeadData = $resultfh->fetchAll();
    }

    // echo '<pre>';
    // print_r($feeHeadData);
    // echo '</pre>';
    if(!empty($feeHeadData)){
        foreach($feeHeadData as $fd){

            $sql = 'SELECT b.* FROM fn_fees_head AS a LEFT JOIN fn_fee_payment_gateway AS b ON a.payment_gateway_id = b.id WHERE a.id = '.$fd['fn_fees_head_id'].' ';
            $result = $connection2->query($sql);
            $gatewayDataAll = $result->fetch();
            $termsAll = $gatewayDataAll['terms_and_conditions'];
            $gatewayIDAll = $gatewayDataAll['id'];

            $invoices = 'SELECT fn_fee_invoice.*,fn_fee_invoice.id as invoiceid, fn_fee_invoice_student_assign.invoice_no as stu_invoice_no, g.fine_type, g.name AS fine_name, g.rule_type, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype, pupilsightPerson.officialName , pupilsightPerson.email, pupilsightPerson.phone1, pupilsightStudentEnrolment.pupilsightYearGroupID as classid, pupilsightStudentEnrolment.pupilsightRollGroupID as sectionid, pupilsightStudentEnrolment.pupilsightProgramID FROM fn_fee_invoice LEFT JOIN pupilsightStudentEnrolment ON fn_fee_invoice.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID LEFT JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID RIGHT JOIN fn_fee_invoice_student_assign ON pupilsightPerson.pupilsightPersonID=fn_fee_invoice_student_assign.pupilsightPersonID AND fn_fee_invoice.id = fn_fee_invoice_student_assign.fn_fee_invoice_id LEFT JOIN fn_fees_fine_rule AS g ON fn_fee_invoice.fn_fees_fine_rule_id = g.id LEFT JOIN trans_route_assign AS asg ON pupilsightPerson.pupilsightPersonID = asg.pupilsightPersonID WHERE fn_fee_invoice_student_assign.invoice_status != "Fully Paid" AND fn_fee_invoice_student_assign.status = "1" AND pupilsightPerson.pupilsightPersonID = "' . $stuId . '" AND fn_fee_invoice.fn_fees_head_id = '.$fd['fn_fees_head_id'].' GROUP BY fn_fee_invoice.id ORDER BY fn_fee_invoice.due_date ASC';

            $resultinv = $connection2->query($invoices);
            $invdata = $resultinv->fetchAll();

            // echo '<pre>';
            // print_r($invdata);
            // echo '</pre>';

            foreach ($invdata as $k => $d) {

                $sqlamt = 'SELECT SUM(fn_fee_invoice_item.total_amount) as totalamount, group_concat(fn_fee_invoice_item.id) as fn_fee_invoice_item_id FROM fn_fee_invoice_item WHERE fn_fee_invoice_id = ' . $d['invoiceid'] . ' ';
                $resultamt = $connection2->query($sqlamt);
                $dataamt = $resultamt->fetch();

                $sql_dis = "SELECT discount FROM fn_invoice_level_discount WHERE pupilsightPersonID = " . $stuId . "  AND invoice_id=".$d['invoiceid']." ";
                $result_dis = $connection2->query($sql_dis);
                $special_dis = $result_dis->fetch();

                $sp_item_sql = "SELECT SUM(discount.discount) as sp_discount
                FROM fn_fee_invoice_item as fee_item
                LEFT JOIN fn_fee_item_level_discount as discount
                ON fee_item.id = discount.item_id WHERE fee_item.fn_fee_invoice_id= ".$d['invoiceid']." AND pupilsightPersonID = ".$stuId."  ";
                $result_sp_item = $connection2->query($sp_item_sql);
                $sp_item_dis = $result_sp_item->fetch();


                //unset($invdata[$k]['finalamount']);
                if (!empty($d['transport_schedule_id'])) {
                    $routes = explode(',', $d['routes']);
                    foreach ($routes as $rt) {
                        $sqlsc = 'SELECT * FROM trans_route_price WHERE schedule_id = ' . $d['transport_schedule_id'] . ' AND route_id = ' . $rt . ' ';
                        $resultsc = $connection2->query($sqlsc);
                        $datasc = $resultsc->fetch();
                        if ($d['routetype'] == 'oneway') {
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
                    $totalamount = $dataamt['totalamount'];
                }

                if (!empty($special_dis['discount']) || !empty($sp_item_dis['sp_discount'])) {
                    $invdata[$k]['finalamount'] = $totalamount - $special_dis['discount'] - $sp_item_dis['sp_discount'];
                    $totalamount = $totalamount - $special_dis['discount'] - $sp_item_dis['sp_discount'];
                } else {
                    $invdata[$k]['finalamount'] = $totalamount;
                }

                //$invdata[$k]['finalamount'] = $totalamount;
                $invdata[$k]['fn_fee_invoice_item_id'] = $dataamt['fn_fee_invoice_item_id'];



                $date = date('Y-m-d');
                $curdate = strtotime($date);
                $duedate = strtotime($d['due_date']);
                $fineId = $d['fn_fees_fine_rule_id'];

                if (!empty($fineId) && $curdate > $duedate) {
                    $finetype = $d['fine_type'];
                    $ruletype = $d['rule_type'];
                    if ($finetype == '1' && $ruletype == '1') {
                        $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" ';
                        $resultf = $connection2->query($sqlf);
                        $finedata = $resultf->fetch();
                        $amtper = $finedata['amount_in_percent'];
                        $type = 'percent';
                    } elseif ($finetype == '1' && $ruletype == '2') {
                        $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" ';
                        $resultf = $connection2->query($sqlf);
                        $finedata = $resultf->fetch();
                        $amtper = $finedata['amount_in_number'];
                        $type = 'num';
                    } elseif ($finetype == '1' && $ruletype == '3') {
                        $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" AND from_date <= "' . $date . '" AND to_date >= "' . $date . '" ';
                        $resultf = $connection2->query($sqlf);
                        $finedata = $resultf->fetch();
                        if (!empty($finedata)) {
                            $amtper = $finedata['amount_in_number'];
                            $type = 'num';
                        } else {
                            $amtper = '';
                            $type = '';
                        }
                    } elseif ($finetype == '2' && $ruletype == '1') {
                        $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" ';
                        $resultf = $connection2->query($sqlf);
                        $finedata = $resultf->fetch();
                        $amtper = $finedata['amount_in_percent'];
                        $type = 'percent';
                    } elseif ($finetype == '2' && $ruletype == '2') {
                        $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" ';
                        $resultf = $connection2->query($sqlf);
                        $finedata = $resultf->fetch();
                        $amtper = $finedata['amount_in_number'];
                        $type = 'num';
                    } elseif ($finetype == '3' && $ruletype == '1') {
                        $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" ';
                        $resultf = $connection2->query($sqlf);
                        $finedata = $resultf->fetch();
                        $amtper = $finedata['amount_in_percent'];
                        $type = 'percent';
                    } elseif ($finetype == '3' && $ruletype == '2') {
                        $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" ';
                        $resultf = $connection2->query($sqlf);
                        $finedata = $resultf->fetch();
                        $amtper = $finedata['amount_in_number'];
                        $type = 'num';
                    } elseif ($finetype == '3' && $ruletype == '4') {
                        $date1 = strtotime($d['due_date']);
                        $date2 = strtotime($date);
                        $diff = abs($date2 - $date1);
                        $years = floor($diff / (365 * 60 * 60 * 24));
                        $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

                        $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" AND from_day <= "' . $days . '" AND to_day >= "' . $days . '" ';
                        $resultf = $connection2->query($sqlf);
                        $finedata = $resultf->fetch();
                        if ($finedata['amount_type'] == 'Fixed') {
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
                $invdata[$k]['amtper'] = $amtper;
                $invdata[$k]['type'] = $type;


                $invid =  $d['invoiceid'];
                $invno =  $d['stu_invoice_no'];
                $sqla = 'SELECT GROUP_CONCAT(a.fn_fee_invoice_item_id) AS invitemid, a.transaction_id FROM fn_fees_student_collection AS a LEFT JOIN fn_fees_collection AS b ON  a.transaction_id = b.transaction_id WHERE a.invoice_no = "' . $invno . '" AND b.transaction_status = "1" ';
                $resulta = $connection2->query($sqla);
                $inv = $resulta->fetch();
                if (!empty($inv['invitemid'])) {
                    if (!empty($d['transport_schedule_id'])) {
                        $invdata[$k]['paidamount'] = $totalamount;
                        $pendingamount = 0;
                        $invdata[$k]['pendingamount'] = $pendingamount;
                        $invdata[$k]['chkpayment'] = 'Paid';
                    } else {
                        $itemids = $inv['invitemid'];
                        $sqlp = 'SELECT SUM(total_amount) as paidtotalamount FROM fn_fee_invoice_item WHERE id IN (' . $itemids . ') ';
                        $resultp = $connection2->query($sqlp);
                        $amt = $resultp->fetch();
                        $totalpaidamt = $amt['paidtotalamount'];
                        if (!empty($totalpaidamt)) {
                            $invdata[$k]['paidamount'] = $totalpaidamt;
                            $pendingamount = $totalamount - $totalpaidamt;
                            $invdata[$k]['pendingamount'] = $pendingamount;
                            if ($pendingamount == '') {
                                $invdata[$k]['chkpayment'] = 'Paid';
                            }
                        }
                    }
                    $invdata[$k]['transaction_id'] = $inv["transaction_id"];
                } else {
                    $invdata[$k]['paidamount'] = '0';
                    $pendingamount = $totalamount;
                    $invdata[$k]['pendingamount'] = $pendingamount;
                    $invdata[$k]['chkpayment'] = 'UnPaid';
                    $invdata[$k]['transaction_id'] = "NA";
                }
            }

            $sqlo = "SELECT * FROM pupilsight_cms  WHERE title != '' ";
            $resulto = $connection2->query($sqlo);
            $orgData = $resulto->fetch();

            // echo '<pre>';
            // print_r($invdata);
            // echo '</pre>';
            // die();

            

            
            echo "<div class ='row fee_hdr FeeInvoiceListManage'><div class='col-md-12'><h2>Invoices <a  id='payMultiple' data-id=".$fd['fn_fees_head_id']."  style='float:right; color:white;font-size: 14px; margin: -6px 0 0 0px;
        ' class='btn btn-primary '>Multiple Pay</a></h2></div></div>";

            echo "<table class='table' cellspacing='0' style='width: 100%' id='FeeInvoiceListManage'>";
            echo "<thead>";
            echo "<tr class='head'>";
            echo '<th>';
            echo __('<input type="checkbox" class="chkAllInv chkAllInvSel-'.$fd['fn_fees_head_id'].'" data-id='.$fd['fn_fees_head_id'].'>');
            echo '</th>';
            echo '<th>';
            echo __('Child Name');
            echo '</th>';
            echo '<th>';
            echo __('Invoice No');
            echo '</th>';
            echo '<th>';
            echo __('Title');
            echo '</th>';
            echo '<th>';
            echo __('Amount');
            echo '</th>';
            echo '<th>';
            echo __('Due Date');
            echo '</th>';
            echo '<th>';
            echo __('Fine');
            echo '</th>';
            echo '<th>';
            echo __('View Bill Details');
            echo '</th>';
            echo "<th>";
            echo __('Action');
            echo '</th>';
            // echo "<th>";
            // echo __('Download');
            // echo '</th>';
            echo "</thead>";
            //  echo "<tbody id='getInvoiceFeeItem'>";
            if (!empty($invdata)) {
                //print_r($invdata);
                //die();

                foreach ($invdata as $ind) {
                    $fn_fees_head_id = $ind['fn_fees_head_id'];
                    $sql = 'SELECT b.* FROM fn_fees_head AS a LEFT JOIN fn_fee_payment_gateway AS b ON a.payment_gateway_id = b.id WHERE a.id = '.$fn_fees_head_id.' ';
                    $result = $connection2->query($sql);
                    $gatewayData = $result->fetch();
                    $terms = $gatewayData['terms_and_conditions'];
                    $gatewayID = $gatewayData['id'];


                    $pupilsightSchoolYearID = $ind['pupilsightSchoolYearID'];
                    $totalamountnew = $ind['finalamount'];
                    $fineamount = 0;
                    if (!empty($ind['amtper'])) {
                        if ($ind['type'] == 'percent') {
                            $fineamount = ($ind['finalamount'] * $ind['amtper']) / 100;
                        } else {
                            $fineamount = $ind['amtper'];
                        }
                        $totalamountnew = $ind['finalamount'] + $fineamount;
                    }

                    if ($ind['chkpayment'] == 'Paid') {
                        $cls = 'Paid';
                        // echo '<tr><td><input type="checkbox" class="" checked></td><td>' . $ind['officialName'] . '</td><td>' . $ind['stu_invoice_no'] . '</td><td>' . $ind['title'] . '</td><td>' . $totalamountnew . '</td><td>' . date('d-m-Y', strtotime($ind['due_date'])) . '</td><td>' . $ind['amtper'] . '</td><td><a  href="fullscreen.php?q=/modules/Finance/invoice_child_feePopup.php&width=1000"  class="thickbox" id="chk_feeID" style="display:none"><button class="">View Bill Details</button></a><a class="chkinvoice_parent" name="'.$stuId.'"id = "'.$ind['id'].'"><button class="btn btn-primary customBtn">View Bill Details</button></a></td><td>&nbsp;&nbsp;Paid</td>';
                        // $lin = $baseurl . "public/receipts/" . $ind["transaction_id"] . ".docx";
                        // echo "<td><a href='" . $lin . "' download>Download</a></td></tr>";
                    } else {
                        //$cuid = $_SESSION[$guid]['pupilsightPersonID'];
                        //$cls = '<a href="index.php?q=/modules/Finance/invoice_child_pay.php&id='.$ind['invoiceid'].'">Pay</a>';
                        //$cls = '<a href="#" onclick="payNow();">Pay</a>';
                        $date = date('Y-m-d');
                        $curdate = strtotime($date);
                        $duedate = strtotime($ind['due_date']);

                        if ($ind['due_date'] == '1970-01-01') {
                            $ddate = '';
                        } else {
                            $dt = date('d/m/Y', strtotime($ind['due_date']));
                            $ddate = $dt;
                        }

                        $style = $curdate >= $duedate ? '#FAFD94' : '#fff';
                        // echo '<tr style="background:'.$style.'"><td><input type="checkbox" class="multiplePayFees" value="'.$ind['id'].'"></td><td>' . $ind['officialName'] . '</td><td>' . $ind['stu_invoice_no'] . '</td><td>' . $ind['title'] . '</td><td>' . $totalamountnew . '</td><td>' . $ddate . '</td><td>' . $ind['amtper'] . '</td><td><a  href="fullscreen.php?q=/modules/Finance/invoice_child_feePopup.php&width=1000"  class="thickbox" id="chk_feeID" style="display:none"><button class="">View Bill Details</button></a><a class="chkinvoice_parent" name="'.$stuId.'"id = "'.$ind['id'].'"><button class="btn btn-primary customBtn">View Bill Details</button></a></td>';
                        echo '<tr><td><input type="checkbox" class="multiplePayFees-'.$fd['fn_fees_head_id'].' chkChildInv chkChildInvSel-'.$fd['fn_fees_head_id'].'" data-fid="'.$fd['fn_fees_head_id'].'" data-amt="'.$totalamountnew.'" value="' . $ind['id'] . '"></td><td>' . $ind['officialName'] . '</td><td>' . $ind['stu_invoice_no'] . '</td><td>' . $ind['title'] . '</td><td>' . $totalamountnew . '</td><td>' . $ddate . '</td><td>' . $ind['amtper'] . '</td><td><a  href="fullscreen.php?q=/modules/Finance/invoice_child_feePopup.php&width=1000"  class="thickbox" id="chk_feeID" style="display:none"><button class="">View Bill Details</button></a><a class="chkinvoice_parent" name="' . $stuId . '"id = "' . $ind['id'] . '"><button class="btn btn-primary customBtn">View Bill Details</button></a></td>';

                        

                    ?>

                    <?php if($gatewayData['name'] == 'RAZORPAY'){ ?>
                        <td>
                            <form action="thirdparty/payment/razorpay/pay.php" method="post" id="payform-<?= $ind['invoiceid'] ?>">
                                <input type="hidden" name="payment_gateway_id" value="<?= $gatewayID ?>">
                                <input type="hidden" name="pupilsightSchoolYearID" value="<?= $ind['pupilsightSchoolYearID'] ?>">
                                <input type="hidden" name="pupilsightProgramID" value="<?= $ind['pupilsightProgramID'] ?>">
                                <input type="hidden" name="classid" value="<?= $ind['classid'] ?>">
                                <input type="hidden" name="sectionid" value="<?= $ind['sectionid'] ?>">
                                <input type="hidden" name="fn_fees_invoice_id" value="<?= $ind['invoiceid'] ?>">
                                <input type="hidden" name="fn_fees_head_id" value="<?= $ind['fn_fees_head_id'] ?>">
                                <input type="hidden" name="rec_fn_fee_series_id" value="<?= $ind['rec_fn_fee_series_id'] ?>">
                                <input type="hidden" name="fn_fee_invoice_item_id" value="<?= $ind['fn_fee_invoice_item_id'] ?>">

                                <input type="hidden" name="total_amount_without_fine_discount" value="<?= $ind['finalamount'] ?>">
                                <input type="hidden" class="fee_amt" name="amount" value="<?= $totalamountnew ?>">
                                <input type="hidden" name="fine" value="<?= $fineamount ?>">
                                <input type="hidden" name="discount" value="0">
                                <!-- Discount calculation pending--->
                                <input type="hidden" name="receipt_number" value="<?= $ind['rec_fn_fee_series_id'] ?>">
                                <input type="hidden" name="name" value="<?= $ind['officialName'] ?>">
                                <input type="hidden" name="email" value="<?= $ind['email'] ?>">
                                <input type="hidden" name="phone" value="<?= $ind['phone1'] ?>">
                                <input type="hidden" name="payid" value="<?= $ind['stu_invoice_no'] ?>">
                                <input type="hidden" name="stuid" value="<?= $stuId ?>">
                                <input type="hidden" name="callbackurl" value="<?= $callbacklink ?>">
                                <input type="hidden" value="<?php echo $orgData['title']; ?>" id="organisationName" name="organisationName">
                                <input type="hidden" value="<?php echo $orgData['logo_image']; ?>" id="organisationLogo" name="organisationLogo">

                                <?php if(!empty($terms)){   ?>
                                    <a class="terms_condition"><button data-id="<?= $ind['invoiceid'] ?>" class="btn btn-primary customBtn clickPay" type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal-<?php echo $gatewayID;?>">Pay</button></a>
                                    <button type="submit" id='click_submit-<?= $ind['invoiceid'] ?>' style="display:none" class="btn btn-primary ">Pay</button>
                                <?php } else { ?>
                                    <button type="submit" id='click_submit-<?= $ind['invoiceid'] ?>'  class="btn btn-primary ">Pay</button>
                                <?php } ?>
                                
                                
                            </form>
                        </td>
                    <?php } if($gatewayData['name'] == 'AIRPAY'){ 
                            $random_number = mt_rand(1000, 9999);
                            $today = time();
                            $orderId = $today . $random_number;

                            $totalamountnewAirpay = number_format($totalamountnew, 2, '.', '');
                    ?>
                        <td>
                            <form action="thirdparty/payment/airpay/sendtoairpay.php" method="post" id="payform-<?= $ind['invoiceid'] ?>">
                                <input type="hidden" name="payment_gateway_id" value="<?= $gatewayID ?>">
                                <input type="hidden" value="<?= $orderId; ?>" id="OrderId" name="orderid">
                                <input type="hidden" name="pupilsightSchoolYearID" value="<?= $ind['pupilsightSchoolYearID'] ?>">
                                <input type="hidden" name="pupilsightProgramID" value="<?= $ind['pupilsightProgramID'] ?>">
                                <input type="hidden" name="classid" value="<?= $ind['classid'] ?>">
                                <input type="hidden" name="sectionid" value="<?= $ind['sectionid'] ?>">
                                <input type="hidden" name="fn_fees_invoice_id" value="<?= $ind['invoiceid'] ?>">
                                <input type="hidden" name="fn_fees_head_id" value="<?= $ind['fn_fees_head_id'] ?>">
                                <input type="hidden" name="rec_fn_fee_series_id" value="<?= $ind['rec_fn_fee_series_id'] ?>">
                                <input type="hidden" name="fn_fee_invoice_item_id" value="<?= $ind['fn_fee_invoice_item_id'] ?>">

                                <input type="hidden" name="total_amount_without_fine_discount" value="<?= $ind['finalamount'] ?>">
                                <input type="hidden" class="fee_amt" name="amount" value="<?= $totalamountnewAirpay ?>">
                                <input type="hidden" name="fine" value="<?= $fineamount ?>">
                                <input type="hidden" name="discount" value="0">
                                <!-- Discount calculation pending--->
                                <input type="hidden" name="receipt_number" value="<?= $ind['rec_fn_fee_series_id'] ?>">
                                <input type="hidden" name="name" value="<?= $ind['officialName'] ?>">
                                <input type="hidden" name="email" value="<?= $parData['email'] ?>">
                                <input type="hidden" name="phone" value="<?= $parData['phone1'] ?>">
                                <input type="hidden" name="payid" value="<?= $ind['stu_invoice_no'] ?>">
                                <input type="hidden" name="stuid" value="<?= $stuId ?>">
                                <input type="hidden" name="callbackurl" value="<?= $callbacklink ?>">
                                <input type="hidden" name="buyerFirstName" value="<?= $ind['officialName'] ?>">
                                <input type="hidden" name="buyerLastName" value="<?= $ind['officialName'] ?>">
                                <input type="hidden" name="buyerEmail" value="<?= $parData['email'] ?>">
                                <input type="hidden" name="buyerPhone" value="<?= $parData['phone1'] ?>">
                                
                                <input type="hidden" class="buyerAddress" name="buyerAddress" value="">
                                <input type="hidden" class="buyerCity" name="buyerCity" value="">
                                <input type="hidden" class="buyerState" name="buyerState" value="">
                                <input type="hidden" class="buyerPinCode" name="buyerPinCode" value="">
                                <input type="hidden" class="buyerCountry" name="buyerCountry" value="">
                                <input type="hidden" class="ptype" name="ptype" value="fee_collection">
                                <input type="hidden" value="<?php echo $orgData['title']; ?>" id="organisationName" name="organisationName">
                                <input type="hidden" value="<?php echo $orgData['logo_image']; ?>" id="organisationLogo" name="organisationLogo">

                                <?php if(!empty($terms)){   ?>
                                    <a class="terms_condition"><button data-id="<?= $ind['invoiceid'] ?>" class="btn btn-primary customBtn clickPay" type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal-<?php echo $gatewayID;?>">Pay</button></a>
                                    <button type="submit" id='click_submit-<?= $ind['invoiceid'] ?>' style="display:none" class="btn btn-primary ">Pay</button>
                                <?php } else { ?>
                                    <button type="submit" id='click_submit-<?= $ind['invoiceid'] ?>'  class="btn btn-primary ">Pay</button>
                                <?php } ?>
                                
                                
                            </form>
                        </td>
                    <?php } ?>

                    <?php
                        // echo "<td>NA</td></tr>";
                        echo "</tr>";
                    }
                    //echo '<tr><td>'.$ind['officialName'].'</td><td>'.$ind['stu_invoice_no'].'</td><td>'.$ind['title'].'</td><td>'.$ind['totalamount'].'</td><td>'.$ind['pendingamount'].'</td><td>'.$cls.'</td></tr>';
                }
            }
            echo "</tbody>";
            echo '</table>';

            echo "</tbody>";
            echo '</table></br>';

            if($gatewayData['name'] == 'RAZORPAY'){ ?>
                <form action='thirdparty/multiplepayment/razorpay/multiplepay.php' method='post' id="razorPayment" class="multipayment-<?= $fd['fn_fees_head_id']?>">
                <input type="hidden" name="payment_gateway_id" value="<?= $gatewayIDAll ?>">
            <?php } else if($gatewayData['name'] == 'AIRPAY'){ 
                $random_number = mt_rand(1000, 9999);
                $today = time();
                $orderId = $today . $random_number;    
            ?> 
                <form action='thirdparty/payment/airpay/sendtoairpay.php' method='post' id="airpayPayment" class="multipayment-<?= $fd['fn_fees_head_id']?>">
                    <input type="hidden" name="payment_gateway_id" value="<?= $gatewayIDAll ?>">
                    <input type="hidden" value="<?= $orderId; ?>" id="OrderId" name="orderid">
                    <input type="hidden" name="buyerFirstName" value="<?= $students['officialName'] ?>">
                    <input type="hidden" name="buyerLastName" value="<?= $students['officialName'] ?>">
                    <input type="hidden" name="buyerEmail" value="<?= $parData['email'] ?>">
                    <input type="hidden" name="buyerPhone" value="<?= $parData['phone1'] ?>">
                    
                    <input type="hidden" class="buyerAddress" name="buyerAddress" value="">
                    <input type="hidden" class="buyerCity" name="buyerCity" value="">
                    <input type="hidden" class="buyerState" name="buyerState" value="">
                    <input type="hidden" class="buyerPinCode" name="buyerPinCode" value="">
                    <input type="hidden" class="buyerCountry" name="buyerCountry" value="">
                    <input type="hidden" class="amount" id="multiAmt" name="amount" value="10.00">
                    <input type="hidden" class="ptype" name="ptype" value="multiple_fee_collection">
            <?php } ?>
                    <input type='hidden' id='multiplepayData-<?= $fd['fn_fees_head_id']?>' name='formdata' value=''>
                    <button type='submit' id='clickMultiplePay-<?= $fd['fn_fees_head_id']?>' style='display:none'>Submit</button>
                </form>
            <?php
        }
    }




    //payment history
    echo "<div class ='row fee_hdr FeeInvoiceListManage'><div class='col-md-12'><h2> Payment History</h2></div></div>";

    echo "<table class='table' cellspacing='0' style='width: 100%;' id='FeeInvoiceListManage'>";
    echo "<thead>";
    echo "<tr class='head'>";
    echo '<th>';
    echo __('S.No');
    echo '</th>';
    echo '<th>';
    echo __('Invoice No');
    echo '</th>';
    echo '<th>';
    echo __('Invoice Title');
    echo '</th>';
    echo '<th>';
    echo __('Child Name');
    echo '</th>';
    echo '<th>';
    echo __('Transaction Id');
    echo '</th>';
    echo '<th>';
    echo __('Receipt No');
    echo '</th>';
    echo '<th>';
    echo __('Transaction Amount');
    echo '</th>';
    echo '<th>';
    echo __('Payment date');
    echo '</th>';
    echo '<th>';
    echo __('Payment mode');
    echo '</th>';
    echo '<th>';
    echo __('Bank Name');
    echo '</th>';
    echo '<th>';
    echo __('Instrument No');
    echo '</th>';
    echo '<th>';
    echo __('status');
    echo '</th>';
    echo '<th>';
    echo __('View Reciept');
    echo '</th>';
    echo "</thead>";
    echo "<tbody id='getPaymentHistory'>";
    //print_r($pupilsightSchoolYearID); 
    $paymenthistory = 'SELECT  f.*,m.name as payMode,b.name as bankname,f.dd_cheque_no,f.reference_no,f.pay_gateway_id, c.officialName as StuName FROM fn_fees_collection as f 
    LEFT JOIN fn_masters as m
    ON f.payment_mode_id = m.id
    LEFT JOIN fn_masters as b ON f.bank_id = b.id
    LEFT JOIN pupilsightPerson as c ON f.pupilsightPersonID = c.pupilsightPersonID
    WHERE  f.pupilsightPersonID = "' . $stuId . '" AND f.transaction_status = "1" ORDER BY f.id DESC';
    $resultPhis = $connection2->query($paymenthistory);
    $payhistory = $resultPhis->fetchAll();

    //     print_r($paymenthistory


    // );
    if (!empty($payhistory)) {
        $i = 1;
        foreach ($payhistory as $ph) {
            

            $sqlfi = "SELECT a.invoice_no, b.title FROM fn_fees_student_collection AS a LEFT JOIN fn_fee_invoice as b ON a.fn_fees_invoice_id = b.id where a.transaction_id = " . $ph['transaction_id'] . " ";
            $resultfi = $connection2->query($sqlfi);
            $fiData = $resultfi->fetch();


            $m_txt = '';
            $mode = strtoupper($ph['payMode']);
            if ($mode == "MULTIPLE") {

                $sql = "SELECT f.name FROM fn_multi_payment_mode AS m LEFT JOIN fn_masters as f ON m.payment_mode_id = f.id
                where m.transaction_id = '" . $ph['transaction_id'] . "'";
                $re_m = $connection2->query($sql);
                $pm = $re_m->fetchAll();
                if (!empty($pm)) {
                    $i = 1;
                    foreach ($pm as $m) {
                        $m_txt .= $m['name'] . ",";
                    }
                }
            } else {
                $m_txt = $ph['payMode'];
            }
            echo '<tr>
                  <td>' . $i++ . '</td>
                  <td>' . $fiData['invoice_no'] . '</td>
                  <td>' . $fiData['title'] . '</td>
                  <td>' . $ph['StuName'] . '</td>
                  <td>' . $ph['transaction_id'] . '</td>
                  <td>' . $ph['receipt_number'] . '</td>
                  <td>' . $ph['transcation_amount'] . '</td>
                  <td>' . date("d/m/Y", strtotime($ph['payment_date'])) . '</td>';
            // if(!empty($ph['pay_gateway_id'])){
            // echo'<td>Online Paid('.$ph['pay_gateway_id'].')</td><td></td>';
            // }  else {
            // echo'<td>'.$ph['payMode'].'</td><td>'.$ph['bankname'].'</td>';
            // }
            if (!empty($ph['pay_gateway_id'])) {
                echo '<td>Online Paid</td><td></td>';
            } else {
                echo '<td>' . $ph['payMode'] . '</td><td>' . $ph['bankname'] . '</td>';
            }
            echo '<td>';
            if (!empty($ph['dd_cheque_no'])) {
                echo $ph['dd_cheque_no'];
            } else if (!empty($ph['reference_no'])) {
                echo $ph['reference_no'];
            }
            echo '</td>';
            echo '<td>' . $ph['payment_status'] . '</td>';

            if (!empty($ph['filename'])) {
                $receipt = 'public/receipts/' . $ph['filename'] . '.pdf';
            } else if (!empty($ph['transaction_id'])) {
                $receipt = 'public/receipts/' . $ph['transaction_id'] . '.pdf';
            } else {
                $receipt = '';
            }

            echo '<td align=\'center\'><a href="'.$receipt.'"  download><i class="mdi mdi-download mdi-18px"></i></a></td></tr>';
        }
    }
    echo "</tbody>";
    echo '</table>';

    ?>


    <style>
        .customBtn {
            font-size: 12px;
        }

        .proceed_decline {
            color: white;
            height: 40px;
            width: 82px;
            font-weight: 600;
            font-size: 12px;
            border-radius: 6px;
        }

        .decline {
            color: red;
            border: 2px solid;
        }

        .modal-header_pay {
            text-align: center;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            border-top-left-radius: .3rem;
            border-top-right-radius: .3rem;
        }

        .modal-title {
            font-size: 20px;
        }
    </style>
    <script>
        function payNow() {
            console.log("paynow");
        }



        $(document).on('click', '.clickPay', function() {
            var id = $(this).attr('data-id');
            $(".clickPayButton").attr('data-id', id);
        });

        $(document).on('click', '.clickPayButton', function() {
            var id = $(this).attr('data-id');
            $("#click_submit-" + id).click();
        });

        $(document).on('click', '#payMultiple', function() {
            var val = '';
            var multipleData = [];
            var cheked = [];
            var amt = 0;
            var tamt = 0;
            var fid = $(this).attr('data-id');
            $.each($(".multiplePayFees-"+fid+":checked"), function() {
                val = $(this).val();
                var formData = $('#payform-' + val).serializeArray();
                multipleData.push(formData);
                cheked.push($(this).val());
                amt = $(this).attr('data-amt');
                tamt += parseInt(amt);
            }); 
            $("#multiAmt").val(Number(tamt).toFixed(2));
            
            var chkid = cheked.join(", ");
            if (chkid) {
                if (multipleData) {
                    $.ajax({
                        url: 'modules/Finance/invoice_multiple_pay_data.php',
                        type: 'post',
                        data: {
                            multipleData: multipleData
                        },
                        async: true,
                        success: function(response) {
                            $("#multiplepayData-"+fid).val(response);
                            setTimeout(function() {
                                $("#clickMultiplePay-"+fid).click();
                            }, 100);
                        }
                    });
                }
            } else {
                alert('You Have to Select Invoice!');
            }

        });

        $(document).on('change', '.chkAllInv', function () {
            var id = $(this).attr('data-id');
            if ($(this).is(':checked')) {
                $(".chkChildInvSel-"+id).prop("checked", true);
            } else {
                $(".chkChildInvSel-"+id).prop("checked", false);
            }
        });

        $(document).on('change', '.chkChildInv', function () {
            var id = $(this).attr('data-fid');
            if ($(this).is(':checked')) {
                //$(".chkChild"+id).prop("checked", true);
            } else {
                $(".chkAllInvSel-"+id).prop("checked", false);
            }
        });

        
        $(document).on('change', '#childSel', function () {
            var id = $(this).val();
            var hrf = 'index.php?q=/modules/Finance/invoice_child_view.php&cid='+id;
            window.location.href = hrf;
        });
    </script>
<?php

}


?>
<!DOCTYPE html>
<html lang="en">

    <body>

        <div class="container">
            <!-- Modal -->
            <?php 
                $sql = 'SELECT * FROM fn_fee_payment_gateway';
                $result = $connection2->query($sql);
                $gatewayDataAll = $result->fetchAll();
            
                if(!empty($gatewayDataAll)){
                    foreach($gatewayDataAll as $gd){    
            ?>
                <div class="modal fade" id="myModal-<?php echo $gd['id'];?>" role="dialog">
                    <div class="modal-dialog">

                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header_pay">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <span class="modal-title">Terms and Conditions </span>
                            </div>
                            <div class="modal-body" id="termsShow">
                                <?php echo $gd['terms_and_conditions'];?>
                                
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary proceed_decline clickPayButton" data-id=""  data-dismiss="modal">PROCEED</button>
                                <button type="button" class="btn btn-default proceed_decline decline" data-dismiss="modal">DECLINE</button>
                            </div>
                        </div>

                    </div>
                </div>
            <?php } } ?>

        </div>

    </body>

</html>
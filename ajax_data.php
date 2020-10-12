<?php
/*
Pupilsight, Flexible & Open School System
*/
include 'pupilsight.php';
$session = $container->get('session');


//Proceed
$type = $_POST['type'];
$val = $_POST['val'];

/* open Description Indicator */

if ($type == 'remarkDetailsUpdate') {
    $remarkid = $_POST['remarkid'];
    if($remarkid && $val){
        $sq = "update acRemarks SET description='".$val."' where id=".$remarkid."";
        //echo $sq;
        $connection2->query($sq);
        echo "success";
    }else{
        echo "error";
    }
}

if ($type == 'getDescriptiveSubject') {
     $pupilsightProgramID=$_POST['pupilsightProgramID'];
    $sq = "select pupilsightDepartmentID, subject_display_name, di_mode from subjectToClassCurriculum where pupilsightProgramID ='".$pupilsightProgramID."' AND pupilsightYearGroupID ='".$val."' AND  di_mode NOT IN ('FREE_FORM','NO_DI') order by subject_display_name asc ";
    $result = $connection2->query($sq);
    $rowdata = $result->fetchAll();
    $returndata = '<option value="">Select Subject</option>';
    foreach ($rowdata as $row) {
        $returndata .= '<option value=' . $row['pupilsightDepartmentID'] . '  data-dimode=' . $row['di_mode'] . '>' . $row['subject_display_name'] . '</option>';
    }
    echo $returndata;
}

if ($type == 'getDescriptiveSkill') {
    $pupilsightYearGroupID = $_POST["pupilsightYearGroupID"];
    $pupilsightDepartmentID = $_POST["pupilsightDepartmentID"];
    if($pupilsightYearGroupID && $pupilsightDepartmentID){
        $sq = "select skill_id, skill_display_name from subjectSkillMapping where pupilsightYearGroupID ='".$pupilsightYearGroupID."' and pupilsightDepartmentID ='".$pupilsightDepartmentID."' order by skill_display_name asc";
        $result = $connection2->query($sq);
        $rowdata = $result->fetchAll();
        $returndata = '<option value="">Select Skill</option>';
        foreach ($rowdata as $row) {
            $returndata .= '<option value=' . $row['skill_id'] . '>' . $row['skill_display_name'] . '</option>';
        }
        echo $returndata;
    }
}

/* close Description Indicator */


if ($type == 'getterm') {
    $sql = 'SELECT * FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearID = ' . $val . ' ';
    $result = $connection2->query($sql);
    $rowdata = $result->fetchAll();
    $returndata = '<option value="">Select Term</option>';
    foreach ($rowdata as $row) {
        $returndata .= '<option value=' . $row['pupilsightSchoolYearTermID'] . '>' . $row['name'] . '</option>';
    }
    echo $returndata;
}

if ($type == 'getstopname') {
    $sql = 'SELECT * FROM trans_route_stops WHERE route_id = ' . $val . ' ';
    $result = $connection2->query($sql);
    $rowdata = $result->fetchAll();
    $returndata = '<option value="">Select Stop</option>';
    foreach ($rowdata as $row) {
        $returndata .= '<option value=' . $row['id'] . '>' . $row['stop_name'] . '</option>';
    }

    echo $returndata;
}

if ($type == 'gettermdaterange') {
    $sql = 'SELECT firstDay, lastDay FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearTermID = ' . $val . ' ';
    $result = $connection2->query($sql);
    $rowdata = $result->fetch();
    $return['firstDay'] = $rowdata['firstDay'];
    $return['lastDay'] = $rowdata['lastDay'];
    echo json_encode($return);
}

if ($type == 'insertcampaigndetails') {
    $campid = $val;
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
    $data1 = array('campaign_id' => $campid, 'pupilsightPersonID' => $cuid);
    $sql1 = "INSERT INTO campaign_parent_registration SET campaign_id=:campaign_id, pupilsightPersonID=:pupilsightPersonID";
    $result = $connection2->prepare($sql1);
    $result->execute($data1);
}

if ($type == 'delFineRuleType') {
    $id = $val;
    $data1 = array('id' => $id);
    $sql1 = 'DELETE FROM fn_fees_rule_type WHERE id=:id';
    $result1 = $connection2->prepare($sql1);
    $result1->execute($data1);
}

if ($type == 'getAjaxDiscountCategory') {
    $aid = $val;
    $sqli = 'SELECT id, name FROM fn_fee_items ';
    $resulti = $connection2->query($sqli);
    $feeItem = $resulti->fetchAll();

    $sqlp = 'SELECT id,name FROM fee_category WHERE status="1"';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();
    $fee_category=array();  
    $fee_category2=array();  
    $fee_category1=array(''=>'Select fee category');
    foreach ($rowdataprog as $dt) {
    $fee_category2[$dt['id']] = $dt['name'];
    }
    $fee_category= $fee_category1 + $fee_category2;

    $feeItemData = array();
    foreach ($feeItem as $dt) {
        $feeItemData[$dt['id']] = $dt['name'];
    }

    $data = '<tr id="seatdiv" class="seatdiv fixedfine flex flex-col sm:flex-row justify-between content-center p-0 deltr' . $aid . '"><td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group">
            <div class="dte mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative">
            <select id="cat_name['.$aid.']" name="cat_name['.$aid.']" class="w-full txtfield">';
            foreach ($fee_category as $k => $st) {
            $data .= '<option value="' . $k . '">' . $st . '</option>';
            }
            $data .= '</select>
            </div></div>
        </div></td>
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group">
            <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative"><select id="fn_fee_item_id" name="fn_fee_item_id[' . $aid . ']" class="w-full  txtfield">';
    foreach ($feeItemData as $k => $st) {
        $data .= '<option value="' . $k . '">' . $st . '</option>';
    }
    $data .= ' </select></div></div>
        </div></td>
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group">
            <div class="dte mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><select id="item_type" name="item_type[' . $aid . ']" class="w-full txtfield"><option value="Fixed">Fixed</option><option value="Percentage">Percentage</option></select></div></div>
        </div>  
        </td>
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group" style="display:inline-flex;">
            <div class="dte mb-1"></div><div class=" txtfield kountseat szewdt numfield mb-1"><div class="flex-1 relative"><input type="text" id="category_amount" name="category_amount[' . $aid . ']" class="ralignnumfield w-full txtfield kountseat szewdt numfield amtPercent"></div></div><div class="dte mb-1"  style="font-size: 25px; padding:  0px 0 0px 4px; width: 30px"><i style="cursor:pointer" class="far fa-times-circle delSeattr" data-id="' . $aid . '"></i></div></div></td></tr>';


    echo $data;
}

if ($type == 'getAjaxInvoiceCategory') {
    $aid = $val;
    $sqli = 'SELECT id, name FROM fn_fee_items ';
    $resulti = $connection2->query($sqli);
    $feeItem = $resulti->fetchAll();

    $feeItemData = array();
    foreach ($feeItem as $dt) {
        $feeItemData[$dt['id']] = $dt['name'];
    }

    $data = '<tr id="seatdiv2" class="seatdiv2 dayslabfine flex flex-col sm:flex-row justify-between content-center p-0 deltr' . $aid . '"><td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm2">
        <div class="input-group stylish-input-group">
            <div class="dte mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="inv_name" name="inv_name[' . $aid . ']" class="inv_name w-full txtfield"></div></div>
        </div></td>
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm2">
            <div class="input-group stylish-input-group">
                <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative"><input type="text" id="min_invoice" name="min_invoice[' . $aid . ']" class="min_inv w-full  txtfield numfield"></div></div>
            </div></td>
            <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm2">
            <div class="input-group stylish-input-group">
                <div class="dte mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="max_invoice" name="max_invoice[' . $aid . ']" class="max_inv w-full  txtfield numfield"></div></div>
            </div></td>
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm2">
        <div class="input-group stylish-input-group">
            <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative"><select id="inv_fn_fee_item_id" name="inv_fn_fee_item_id[' . $aid . ']" class="w-full  txtfield">';
    foreach ($feeItemData as $k => $st) {
        $data .= '<option value="' . $k . '">' . $st . '</option>';
    }
    $data .= ' </select></div></div>
        </div></td>    
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm2">
        <div class="input-group stylish-input-group">
            <div class="dte mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><select id="inv_item_type" name="inv_item_type[' . $aid . ']" class="w-full txtfield"><option value="Fixed">Fixed</option><option value="Percentage">Percentage</option></select></div></div>
        </div>  
        </td>
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm2">
        <div class="input-group stylish-input-group" style="display:inline-flex;">
            <div class="dte mb-1"></div><div class=" txtfield kountseat szewdt2 numfield mb-1"><div class="flex-1 relative"><input type="text" id="inv_amount" name="inv_amount[' . $aid . ']" class="w-full txtfield kountseat szewdt2 numfield inv_amtPercent"></div></div><div class="dte mb-1"  style="font-size: 20px; padding:  0px 0 0px 4px; width: 20px"><i style="cursor:pointer" class="far fa-times-circle delSeattr" data-id="' . $aid . '"></i></div></div></td></tr>';


    echo $data;
}

if ($type == 'delDiscountRuleType') {
    $id = $val;
    $data1 = array('id' => $id);
    $sql1 = 'DELETE FROM fn_fee_discount_item WHERE id=:id';
    $result1 = $connection2->prepare($sql1);
    $result1->execute($data1);
}

if ($type == 'getAjaxFeeStructureItem') {
    $aid = $val;
    $disid = $_POST['disid'];
    if ($disid != 'nodata') {
        $sqli = 'SELECT id, name FROM fn_fee_items WHERE id NOT IN (' . $disid . ')';
    } else {
        $sqli = 'SELECT id, name FROM fn_fee_items ';
    }
    $resulti = $connection2->query($sqli);
    $feeItem = $resulti->fetchAll();

    $feeItemData = array();
    $feeItemData1 = array('' => 'Select Fee Item');
    $feeItemData2 = array();
    foreach ($feeItem as $dt) {
        $feeItemData2[$dt['id']] = $dt['name'];
    }
    $feeItemData = $feeItemData1 + $feeItemData2;

    $data = '<tr id="seatdiv" class="seatdiv fixedfine flex flex-col sm:flex-row justify-between content-center p-0 deltr' . $aid . '"><td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group">
            <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative"><select id="feeStructureItemDisableId" name="fn_fee_item_id[' . $aid . ']" class="w-full  txtfield allFeeItemId">';
    foreach ($feeItemData as $k => $st) {
        $data .= '<option value="' . $k . '">' . $st . '</option>';
    }
    $data .= ' </select></div></div>
        </div></td>
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
            <div class="input-group stylish-input-group">
                <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative"><input type="text" id="amount" name="amount[' . $aid . ']" class="w-full  txtfield numfield kountAmt"></div></div>
            </div></td>
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group">
            <div class="dte mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><select id="tax" name="tax[' . $aid . ']" class="w-full txtfield taxOptionSelect" data-id="' . $aid . '"><option value="N">No</option><option value="Y">Yes</option></select></div></div>
        </div>  
        </td>
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group" style="display:inline-flex;">
            <div class="dte mb-1"></div><div class=" txtfield kountseat szewdt numfield mb-1"><div class="flex-1 relative"><input type="text" id="taxPercent' . $aid . '" name="tax_percent[' . $aid . ']" class="w-full txtfield kountseat szewdt numfield" readonly></div></div><div class="dte mb-1"  style="font-size: 25px; padding:  0px 0 0px 4px; width: 30px"><i style="cursor:pointer" class="far fa-times-circle delSeattr" data-id="' . $aid . '"></i></div></div></td></tr>';


    echo $data;
}

if ($type == 'delFeeStructureItem') {
    $id = $val;
    $data1 = array('id' => $id);
    $sql1 = 'DELETE FROM fn_fee_structure_item WHERE id=:id';
    $result1 = $connection2->prepare($sql1);
    $result1->execute($data1);
}

if ($type == 'addstudentidInSession') {
    $val=$_POST['val'];
    $session->forget(['student_ids']);
    $session->set('student_ids', $val);
}
 if($type=='copyfeeItemSession'){ 
     $session->forget(['fee_items']);
    $session->set('fee_items', $val);}

if ($type == 'addtransactionidInSession') {
    $session->forget(['transaction_ids']);
    $session->set('transaction_ids', $val);
}

if ($type == 'setSessionEditInvoice') {
    $stu = $_POST['stu'];
    $session->forget(['inovice_ids']);
    $session->forget(['can_stu_id']);
    $session->set('inovice_ids', $val);
    $session->set('can_stu_id', $stu);
}

if ($type == 'getAjaxInvoiceItem') {
    $aid = $val;
    $disid = $_POST['disid'];
    if ($disid != 'nodata') {
        $sqli = 'SELECT id, name FROM fn_fee_items WHERE id NOT IN (' . $disid . ')';
    } else {
        $sqli = 'SELECT id, name FROM fn_fee_items ';
    }
    $resulti = $connection2->query($sqli);
    $feeItem = $resulti->fetchAll();

    $feeItemData = array();
    $feeItemData1 = array('' => 'Select Fee Item');
    $feeItemData2 = array();
    foreach ($feeItem as $dt) {
        $feeItemData2[$dt['id']] = $dt['name'];
    }
    $feeItemData = $feeItemData1 + $feeItemData2;

    $data = '<tr style="margin-bottom:0px !important;" id="seatdiv" class="seatdiv fixedfine flex flex-col sm:flex-row justify-between content-center p-0 deltr' . $aid . '"><td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group">
            <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative"><select id="feeStructureItemDisableId" name="fn_fee_item_id[' . $aid . ']" class="w-full  txtfield allFeeItemId">';
    foreach ($feeItemData as $k => $st) {
        $data .= '<option value="' . $k . '">' . $st . '</option>';
    }
    $data .= ' </select></div></div>
        </div></td>
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
            <div class="input-group stylish-input-group">
                <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative"><input type="text" id="description" name="description[' . $aid . ']" class="w-full  txtfield"></div></div>
            </div></td>
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
            <div class="input-group stylish-input-group">
                <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative"><input type="text" id="amount" name="amount[' . $aid . ']" class="w-full  txtfield numfield"></div></div>
            </div></td>
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group">
            <div class="dte mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="tax" name="tax[' . $aid . ']" class="w-full txtfield numfield"></div></div>
        </div>  
        </td>
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
            <div class="input-group stylish-input-group" style="display:inline-flex;">
                <div class="dte mb-1"></div><div class="  txtfield kountseat szewdt2 numfield mb-1"><div class="flex-1 relative"><input type="text" id="discount" name="discount[' . $aid . ']" class="w-full  txtfield szewdt2 numfield"></div></div>
                <div class="dte mb-1"  style="font-size: 25px; padding:  0px 0 0px 4px; width: 30px"><i style="cursor:pointer" class="far fa-times-circle delSeattr" data-id="' . $aid . '"></i></div>
            </div></td>

        </tr>';


    echo $data;
}

if ($type == 'delInvoiceItem') {
    $id = $val;
    $data1 = array('id' => $id);
    $sql1 = 'DELETE FROM fn_fee_structure_item WHERE id=:id';
    $result1 = $connection2->prepare($sql1);
    $result1->execute($data1);
}

if ($type == 'filterstudentbyclass') {
    $id = $val;
    $pid = $_POST['pid'];
    $sqls = 'SELECT a.officialName AS student_name, a.pupilsightPersonID, GROUP_CONCAT(fn_fee_structure_id) AS fsid FROM pupilsightPerson AS a LEFT JOIN pupilsightStudentEnrolment AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN fn_fees_student_assign AS c ON a.pupilsightPersonID = c.pupilsightPersonID WHERE b.pupilsightProgramID = ' . $pid . ' AND a.pupilsightRoleIDAll = "003" AND b.pupilsightYearGroupID = ' . $id . ' GROUP BY a.pupilsightPersonID';
    $results = $connection2->query($sqls);
    $students = $results->fetchAll();
    //     echo '<pre>';
    // echo $students;
    

    $data = ' <td class="flex flex-col flex-grow justify-center -mb-1 sm:mb-0  px-2 border-b-0 sm:border-b border-t-0 ">
        <div class="input-group stylish-input-group">
            <label for="pupilsightYearGroupID" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs"> </label>
        </div></td>

        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
        <div class="input-group stylish-input-group">
            <div class=" mb-1"><label for="name" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">Student </label></div>';

    foreach ($students as $k => $st) {
        if (empty($st['fsid'])) {
            $data .= '<div class="right mb-1 mbtm" ><div class="inline flex-1 relative"><label class="leading-normal" for="pupilsightPersonID[student][' . $st['pupilsightPersonID'] . ']"> <span style="color:red;">(Not Assign)</span>' . $st['student_name'] . '</label> <input type="checkbox" name="pupilsightPersonID[student][' . $st['pupilsightPersonID'] . ']" id="pupilsightPersonID[class][' . $st['pupilsightPersonID'] . ']" value="on" class="right" disabled="1"><br></div>
                </div>';
        } else {
            $data .= '<div class="right mb-1 mbtm"><div class="inline flex-1 relative"><label class="leading-normal" for="pupilsightPersonID[student][' . $st['pupilsightPersonID'] . ']"> ' . $st['student_name'] . '</label> <input type="checkbox" name="pupilsightPersonID[student][' . $st['pupilsightPersonID'] . ']" id="pupilsightPersonID[class][' . $st['pupilsightPersonID'] . ']" value="on" class="right"><br></div>
                </div>';
        }
    }
    $data .= '</div>    
        </td>
                            
                                                                        
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
        <div class="input-group stylish-input-group">
            <div class=" mb-1"><label for="invoice_title" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs"> </label></div>';
    foreach ($students as $k => $cl) {
        if (!empty($cl['fsid'])) {
            $sqlchk = 'SELECT GROUP_CONCAT(DISTINCT b.fn_fee_structure_id) as fid FROM fn_fee_invoice_student_assign AS a LEFT JOIN fn_fee_invoice AS b ON a.fn_fee_invoice_id = b.id WHERE a.pupilsightPersonID = ' . $cl['pupilsightPersonID'] . ' ';
            $resultchk = $connection2->query($sqlchk);
            $chkstrid = $resultchk->fetch();
            $fid = $chkstrid['fid'];
            if (!empty($fid)) {
                $sqlf = 'SELECT GROUP_CONCAT(DISTINCT id) as fsid FROM fn_fee_structure WHERE id IN (' . $cl['fsid'] . ') AND id NOT IN (' . $fid . ') ';
            } else {
                $sqlf = 'SELECT GROUP_CONCAT(DISTINCT id) as fsid FROM fn_fee_structure WHERE id IN (' . $cl['fsid'] . ') ';
            }
            $resultf = $connection2->query($sqlf);
            $rowdatafees = $resultf->fetch();
            if (!empty($rowdatafees)) {
                $feesStructure = $rowdatafees['fsid'];

                echo '<input type="hidden" id="pupilsightPersonID[structure][' . $cl['pupilsightPersonID'] . ']" name="pupilsightPersonID[structure][' . $cl['pupilsightPersonID'] . ']" class="w-full mbtm1" value="'.$feesStructure.'">';
                /*
                $feesStructure = array();
                $feesStructure1 = array('' => 'Select Fee Structure');
                $feesStructure2 = array();
                foreach ($rowdatafees as $dt) {
                    $feesStructure2[$dt['id']] = $dt['name'];
                }
                $feesStructure = $feesStructure1 + $feesStructure2;
                $data .= '<div class="  mb-1 "><div class="flex-1 relative"><select id="pupilsightPersonID[structure][' . $cl['pupilsightPersonID'] . ']" name="pupilsightPersonID[structure][' . $cl['pupilsightPersonID'] . ']" class="w-full mbtm1">';
                foreach ($feesStructure as $k => $st) {
                    $data .= '<option value="' . $k . '">' . $st . '</option>';
                }
                $data .= ' </select></div></div>';
                */
            } else {
                $feesStructure = array();
                $data .= '<div class="hidelevel mb-1"><label for="pupilsightPersonID[structure][' . $cl['pupilsightPersonID'] . ']" class="hidelevel inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs"> </label></div>';
            }
        } else {
            $feesStructure = array();
            $data .= '<div class="hidelevel mb-1 mbtm1"><label for="pupilsightPersonID[structure][' . $cl['pupilsightPersonID'] . ']" class="hidelevel inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs"> </label></div>';
        }
    }
    $data .= '    
         </div> 
        </td>';
    echo $data;
}

if ($type == 'invoiceFeeItem') {
    $id = $_POST['val'];
    $sid = $_POST['sid'];
    $std_query="SELECT fee_category_id FROM `pupilsightPerson` WHERE `pupilsightPersonID` = '".$sid."'";
    $std_exe = $connection2->query($std_query);
    $std_data=$std_exe->fetch();
    $fee_category_id=$std_data['fee_category_id'];
    $sqli = 'SELECT e.pupilsightPersonID,a.*,a.id as itemid, b.*, b.id as ifid, b.name as feeitemname, c.id AS invoiceid, c.transport_schedule_id, d.format, e.invoice_no as stu_invoice_no, f.item_type, f.name, f.min_invoice, f.max_invoice, f.amount_in_percent, f.amount_in_number, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype  FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_items AS b ON a.fn_fee_item_id = b.id LEFT JOIN fn_fee_invoice AS c ON a.fn_fee_invoice_id = c.id LEFT JOIN fn_fee_series AS d ON c.inv_fn_fee_series_id = d.id LEFT JOIN fn_fee_invoice_student_assign AS e ON c.id = e.fn_fee_invoice_id  LEFT JOIN fn_fee_discount_item as f ON a.fn_fee_item_id = f.fn_fee_item_id LEFT JOIN trans_route_assign AS asg ON e.pupilsightPersonID = asg.pupilsightPersonID WHERE a.fn_fee_invoice_id IN (' . $id . ') AND e.pupilsightPersonID = ' . $sid . ' GROUP BY a.id';
    $resulti = $connection2->query($sqli);
    $feeItem = $resulti->fetchAll();

    $data = '';
  
    foreach ($feeItem as $fI) {
         $discountamt=0;
         $discount=0;
         $amtpaid = 0;
         $amtpending = 0;
         $paystatus = '';
        $sql_dis = "SELECT discount FROM fn_fee_item_level_discount WHERE pupilsightPersonID = ".$sid."  AND item_id='".$fI['itemid']."' ";
        $result_dis = $connection2->query($sql_dis);
        $special_dis = $result_dis->fetch();
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


        $sqlchk = 'SELECT COUNT(a.id) as kount, a.total_amount, a.total_amount_collection, a.status FROM fn_fees_student_collection AS a LEFT JOIN fn_fees_collection AS b ON a.transaction_id = b.transaction_id WHERE a.fn_fee_invoice_item_id = ' . $fI['itemid'] . ' AND a.pupilsightPersonID = ' . $sid . ' AND b.transaction_status = "1" ';

        $resultchk = $connection2->query($sqlchk);
        $itemchk = $resultchk->fetch();
       

        $amtpaid = $itemchk['total_amount_collection'];
        $amtpending = $itemchk['total_amount'] - $itemchk['total_amount_collection'];
        if($amtpending){
            $payamount = $amtpending;
        } else {
            $payamount = $totalamount;
        }
        if($itemchk['status'] == '1'){
            $paystatus = 'Paid';
            $cls = '';
            $checked = 'checked disabled';
        } else if($itemchk['status'] == '2') {
            $paystatus = 'Partial Paid';
            $cls = 'selFeeItem';
            $checked = '';
        } else {
            $cls = 'selFeeItem';
            $checked = '';
        }

        // $inid = '000'.$id;
        // $invno = str_replace("0001",$inid,$fI['format']);
        //echo $fI['name'];
        if($fee_category_id==$fI['name']){
            if ($fI['item_type'] == 'Fixed') {
                $discount = $fI['amount_in_number'];
                $discountamt = $fI['amount_in_number'];
            } else {
                $discount = $fI['amount_in_percent'] . '%';
                $discountamt = ($fI['amount_in_percent'] / 100) * $totalamount;
            }
        } 
        if(!empty($special_dis['discount'])){
          $discountamt+=$special_dis['discount'];
        }
        $amtdiscount = $totalamount - $discountamt;

        $data .= '<tr class="odd invrow' . $id . '" role="row">
                  
            <td>
                  <div class="inline flex-1 relative"><label class="leading-normal" for="feeItemid"></label> <input type="checkbox" class="' . $cls . '" data-invid="' . $fI['invoiceid'] . '" data-totamt="' . $payamount . '" data-amt="' . $amtdiscount . '" data-dis="' . $discountamt . '"  name="feeItemid[]" id="feeItemid" value="' . $fI['itemid'] . '" ' . $checked . '><br></div>  
            </td>
             
            <td class="p-2 sm:p-3">
               ' . $fI['feeitemname'] . '     
            </td>
             
            <td class="p-2 sm:p-3 hidden-1 sm:table-cell">
            ' . $fI['description'] . '
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
                '.$amtpaid.'
            </td>
             
            <td class="p-2 sm:p-3 hidden-1 md:table-cell">
               '.$amtpending.'
            </td>
            <td class="p-2 sm:p-3 hidden-1 md:table-cell">
               '.$paystatus.'
            </td>
             
        </tr>';
    }
    echo $data;
}

if ($type == 'searchStudentInvoice') {
    $aid = $_POST['val'];
    $pupilsightProgramID = $_POST['pupilsightProgramID'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
  
  
    if(isset($_POST['pupilsightRollGroupID'])){
        $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];
        } else {
            $pupilsightRollGroupID='';
        }
        if(isset($_POST['search'])){
        $search = $_POST['search'];
        } else {
            $search='';
        }

    $sqli = 'SELECT a.pupilsightPersonID, a.admission_no, p.name, a.officialName,d.name as class, e.name as section FROM pupilsightPerson AS a LEFT JOIN pupilsightStudentEnrolment AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightProgram AS p ON b.pupilsightProgramID = p.pupilsightProgramID LEFT JOIN pupilsightYearGroup AS d ON b.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS e ON b.pupilsightRollGroupID = e.pupilsightRollGroupID WHERE a.pupilsightRoleIDPrimary = "003" AND b.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND b.pupilsightProgramID = "' . $pupilsightProgramID . '" AND b.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" ';
    if($pupilsightRollGroupID!=""){
     $sqli.=' AND b.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '" ';
    }
    if($search!=""){
      $sqli.='  AND a.officialName LIKE "%' . $search . '%" ';
    }
    $resulti = $connection2->query($sqli);
    $students = $resulti->fetchAll();
    $data = '';
    foreach ($students as $k => $dt) {
        $sqls = 'SELECT p.officialName FROM pupilsightFamilyRelationship as r LEFT JOIN pupilsightPerson as p
        ON r.pupilsightPersonID1 = p.pupilsightPersonID WHERE r.pupilsightPersonID2="'.$dt['pupilsightPersonID'].'" AND r.relationship="Father"';
        $results = $connection2->query($sqls);
        $parents = $results->fetch();
         
        $sqls1 = 'SELECT p.officialName FROM pupilsightFamilyRelationship as r LEFT JOIN pupilsightPerson as p
        ON r.pupilsightPersonID1 = p.pupilsightPersonID WHERE r.pupilsightPersonID2="'.$dt['pupilsightPersonID'].'" AND r.relationship="Mother"';
        $results1 = $connection2->query($sqls1);
        $parents1 = $results1->fetch();

        $students[$k]['parent_name'] = $parents['officialName'];

        $data .= '<tr><td >
               <input type="radio" id="selStudent" name="student_id" value="' . $dt['pupilsightPersonID'] . '" class=""></td>
                <td >' . $dt['admission_no'] . '</td>   
                <td >' . $dt['officialName'] . '</td>
                <td >' . $parents['officialName'] . '</td>
                <td >' . $parents1['officialName'] . '</td>
                <td >' . $dt['name'] . '</td>
                <td >' . $dt['class'] . '</td>
                <td >' . $dt['section'] . '</td>
                </tr>';
    }
    echo $data;
}

if ($type == 'searchStudent') {
    $aid = $_POST['val'];
    $search = $_POST['search'];

    $sqli = 'SELECT a.pupilsightPersonID, a.admission_no, p.name,a.officialName,  d.name as class, e.name as section FROM pupilsightPerson AS a LEFT JOIN pupilsightStudentEnrolment AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightProgram AS p ON b.pupilsightProgramID = p.pupilsightProgramID  LEFT JOIN pupilsightYearGroup AS d ON b.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS e ON b.pupilsightRollGroupID = e.pupilsightRollGroupID WHERE a.pupilsightRoleIDPrimary = "003" AND a.officialName LIKE "%' . $search . '%" OR b.pupilsightSchoolYearID = "' . $search . '" OR b.pupilsightProgramID = "' . $search . '" OR b.pupilsightYearGroupID = "' . $search . '" OR  a.pupilsightPersonID = "' . $search . '" OR  a.admission_no = "' . $search . '"';
    $resulti = $connection2->query($sqli);
    $students = $resulti->fetchAll();
    $data = '';
    foreach ($students as $k => $dt) {
        $sqls = 'SELECT p.officialName FROM pupilsightFamilyRelationship as r LEFT JOIN pupilsightPerson as p
        ON r.pupilsightPersonID1 = p.pupilsightPersonID WHERE r.pupilsightPersonID2="'.$dt['pupilsightPersonID'].'" AND r.relationship="Father"';
        $results = $connection2->query($sqls);
        $parents = $results->fetch();
         
        $sqls1 = 'SELECT p.officialName FROM pupilsightFamilyRelationship as r LEFT JOIN pupilsightPerson as p
        ON r.pupilsightPersonID1 = p.pupilsightPersonID WHERE r.pupilsightPersonID2="'.$dt['pupilsightPersonID'].'" AND r.relationship="Mother"';
        $results1 = $connection2->query($sqls1);
        $parents1 = $results1->fetch();

        $students[$k]['parent_name'] = $parents['officialName'];

        $data .= '<tr><td >
               <input type="radio" id="selStudent" name="student_id" value="' . $dt['pupilsightPersonID'] . '" class=""></td>
                <td >' . $dt['admission_no'] . '</td>   
                <td >' . $dt['officialName'] . '</td>
                <td >' . $parents['officialName'] . '</td>
                <td >' . $parents1['officialName'] . '</td>
                <td >' . $dt['name'] . '</td>
                <td >' . $dt['class'] . '</td>
                <td >' . $dt['section'] . '</td>
                </tr>';
    }
    echo $data;
}

// if($type == 'addstaffidInSession'){
//     $session->forget(['staff_ids']);
//     $session->set('staff_ids', $val);
// }
if ($type == 'change_routeId') {
    $sql = 'SELECT id FROM  trans_route_assign WHERE pupilsightPersonID = ' . $val . ' ';
    $result = $connection2->query($sql);
    $rowdata = $result->fetch();

    $session->forget(['changeRoute_id']);
    $session->set('changeRoute_id', $rowdata);
}


if ($type == 'logoutCounter') {
    $counterid = $val;
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
    $cdate = date('Y-m-d');
    $ctime = date('H:i:s');

    $sqlchk = 'SELECT a.* FROM fn_fees_counter_map AS a LEFT JOIN fn_fees_counter AS b ON a.fn_fees_counter_id = b.id WHERE a.pupilsightPersonID = "' . $cuid . '" AND a.active_date = "' . $cdate . '" AND a.end_time IS NULL AND b.status = "1" ';
    $resultchk = $connection2->query($sqlchk);
    $chkcounter = $resultchk->fetchAll();

    foreach ($chkcounter as $chk) {
        $id = $chk['id'];
        $counterid = $chk['fn_fees_counter_id'];

        $data = array('end_time' => $ctime, 'pupilsightPersonID' => $cuid, 'id' => $id);
        $sql = 'UPDATE fn_fees_counter_map SET end_time=:end_time WHERE id=:id AND pupilsightPersonID=:pupilsightPersonID';
        $result = $connection2->prepare($sql);
        $result->execute($data);

        $data1 = array('status' => '2', 'id' => $counterid);
        $sql1 = 'UPDATE fn_fees_counter SET status=:status WHERE id=:id';
        $result1 = $connection2->prepare($sql1);
        $result1->execute($data1);
        $session->forget(['counterid']);
    }
}

if ($type == 'getClass') {
    $pid = $val;
    $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramID = "' . $pid . '" GROUP BY a.pupilsightYearGroupID';
    $result = $connection2->query($sql);
    $classes = $result->fetchAll();
    // echo '<pre>';
    // print_r($classes);
    // echo '</pre>';
    $data = '<option value="">Select Class</option>';
    if (!empty($classes)) {
        foreach ($classes as $k => $cl) {
            $data .= '<option value="' . $cl['pupilsightYearGroupID'] . '">' . $cl['name'] . '</option>';
        }
    }
    echo $data;
}
if ($type == 'getClass_new') {
    $pid = $val;
    $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramID = "' . $pid . '" GROUP BY a.pupilsightYearGroupID';
    $result = $connection2->query($sql);
    $classes = $result->fetchAll();
    // echo '<pre>';
    // print_r($classes);
    // echo '</pre>';
    $data = '';
    if (!empty($classes)) {
        foreach ($classes as $k => $cl) {
            $data .= '<option value="' . $cl['pupilsightYearGroupID'] . '">' . $cl['name'] . '</option>';
        }
    }
    echo $data;
}

if ($type == 'getSection') {
    $cid = $val;
    $pid = $_POST['pid'];
   $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE a.pupilsightProgramID = "' . $pid . '" AND a.pupilsightYearGroupID = "' . $cid . '" GROUP BY a.pupilsightRollGroupID';
    $result = $connection2->query($sql);
    $sections = $result->fetchAll();
    $data = '<option value="">Select Section</option>';
    if (!empty($sections)) {
        foreach ($sections as $k => $cl) {
            $data .= '<option value="' . $cl['pupilsightRollGroupID'] . '">' . $cl['name'] . '</option>';
        }
    }
    echo $data;
}

if ($type == 'changestaffstatus') {
    $session->forget(['staff_id']);
    $session->set('staff_id', $val);
}


if($type == 'transStatusChange'){
    $transid = $val;
    $status = $_POST['status'];
    $tid = explode(',', $transid);
    foreach($tid as $t){
        $data1 = array('payment_status' => $status, 'id' => $t);
        $sql1 = 'UPDATE fn_fees_collection SET payment_status=:payment_status WHERE id=:id';
        $result1 = $connection2->prepare($sql1);
        $result1->execute($data1);
    }
}

if($type == 'deactiveCounter'){
    $counterid = $val;
    $data1 = array('status' => '2', 'id' => $counterid);
    $sql1 = 'UPDATE fn_fees_counter SET status=:status WHERE id=:id';
    $result1 = $connection2->prepare($sql1);
    $result1->execute($data1);
    $session->forget(['counterid']);

}

if($type == 'deleteStudentRoutes'){
    $ids = explode(',',$val);
    foreach($ids as $st){
        $data2 = array('pupilsightPersonID' => $st);
        $sql2 = 'DELETE FROM trans_route_assign WHERE pupilsightPersonID=:pupilsightPersonID';
        $result2 = $connection2->prepare($sql2);
        $result2->execute($data2);
    }
}

if ($type == 'changeStudentRoutes') {
    $session->forget(['student_ids']);
    $session->set('student_ids', $val);

    // $ids = explode(',',$val);
    // foreach($ids as $st){
    //     $data2 = array('pupilsightPersonID' => $st);
    //     $sql2 = 'DELETE FROM trans_route_assign WHERE pupilsightPersonID=:pupilsightPersonID';
    //     $result2 = $connection2->prepare($sql2);
    //     $result2->execute($data2);
    // }
}

if ($type == 'filterstudentbyclassTransport') {
    $id = $val;
    $sqls = 'SELECT a.officialName AS student_name, a.pupilsightPersonID, GROUP_CONCAT(c.schedule_id) AS fsid FROM pupilsightPerson AS a LEFT JOIN pupilsightStudentEnrolment AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN trans_schedule_assign_student AS c ON a.pupilsightPersonID = c.pupilsightPersonID WHERE a.pupilsightRoleIDAll = "003" AND b.pupilsightYearGroupID = ' . $id . ' GROUP BY a.pupilsightPersonID';
    $results = $connection2->query($sqls);
    $students = $results->fetchAll();
    //     echo '<pre>';
    // print_r($students);
    // echo '</pre>';

    $data = ' <td class="flex flex-col flex-grow justify-center -mb-1 sm:mb-0  px-2 border-b-0 sm:border-b border-t-0 ">
        <div class="input-group stylish-input-group">
            <label for="pupilsightYearGroupID" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs"> </label>
        </div></td>

        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
        <div class="input-group stylish-input-group">
            <div class=" mb-1"><label for="name" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">Student </label></div>';

    foreach ($students as $k => $st) {
        if (empty($st['fsid'])) {
            $data .= '<div class="right mb-1 mbtm" ><div class="inline flex-1 relative"><label class="leading-normal" for="pupilsightPersonID[student][' . $st['pupilsightPersonID'] . ']"> <span style="color:red;">(Not Assign)</span>' . $st['student_name'] . '</label> <input type="checkbox" name="pupilsightPersonID[student][' . $st['pupilsightPersonID'] . ']" id="pupilsightPersonID[class][' . $st['pupilsightPersonID'] . ']" value="on" class="right" disabled="1"><br></div>
                </div>';
        } else {
            $data .= '<div class="right mb-1 mbtm"><div class="inline flex-1 relative"><label class="leading-normal" for="pupilsightPersonID[student][' . $st['pupilsightPersonID'] . ']"> ' . $st['student_name'] . '</label> <input type="checkbox" name="pupilsightPersonID[student][' . $st['pupilsightPersonID'] . ']" id="pupilsightPersonID[class][' . $st['pupilsightPersonID'] . ']" value="on" class="right"><br></div>
                </div>';
        }
    }
    $data .= '</div>    
        </td>
                            
                                                                        
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes">
        <div class="input-group stylish-input-group">
            <div class=" mb-1"><label for="invoice_title" class="inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">Transport Schedule </label></div>';
    foreach ($students as $k => $cl) {
        if (!empty($cl['fsid'])) {
            $sqlchk = 'SELECT GROUP_CONCAT(DISTINCT b.transport_schedule_id) as fid FROM fn_fee_invoice_student_assign AS a LEFT JOIN fn_fee_invoice AS b ON a.fn_fee_invoice_id = b.id WHERE a.pupilsightPersonID = ' . $cl['pupilsightPersonID'] . ' ';
            $resultchk = $connection2->query($sqlchk);
            $chkstrid = $resultchk->fetch();
            $fid = $chkstrid['fid'];
            if (!empty($fid)) {
                $sqlf = 'SELECT id, schedule_name FROM trans_schedule WHERE id IN (' . $cl['fsid'] . ') AND id NOT IN (' . $fid . ') ';
            } else {
                $sqlf = 'SELECT id, schedule_name FROM trans_schedule WHERE id IN (' . $cl['fsid'] . ') ';
            }
            $resultf = $connection2->query($sqlf);
            $rowdatafees = $resultf->fetchAll();
            if (!empty($rowdatafees)) {
                $feesStructure = array();
                $feesStructure1 = array('' => 'Select Schedule');
                $feesStructure2 = array();
                foreach ($rowdatafees as $dt) {
                    $feesStructure2[$dt['id']] = $dt['schedule_name'];
                }
                $feesStructure = $feesStructure1 + $feesStructure2;
                $data .= '<div class="  mb-1 "><div class="flex-1 relative"><select id="pupilsightPersonID[structure][' . $cl['pupilsightPersonID'] . ']" name="pupilsightPersonID[structure][' . $cl['pupilsightPersonID'] . ']" class="w-full mbtm1">';
                foreach ($feesStructure as $k => $st) {
                    $data .= '<option value="' . $k . '">' . $st . '</option>';
                }
                $data .= ' </select></div></div>';
            } else {
                $feesStructure = array();
                $data .= '<div class="hidelevel mb-1"><label for="pupilsightPersonID[structure][' . $cl['pupilsightPersonID'] . ']" class="hidelevel inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs"> </label></div>';
            }
        } else {
            $feesStructure = array();
            $data .= '<div class="hidelevel mb-1 mbtm1"><label for="pupilsightPersonID[structure][' . $cl['pupilsightPersonID'] . ']" class="hidelevel inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs"> </label></div>';
        }
    }
    $data .= '    
         </div> 
        </td>';
    echo $data;
}

if ($type == 'getAjaxTransportRouteFee') {
    $aid = $val;
    $sqlrt = 'SELECT id, route_name FROM trans_routes';

    $resultrt = $connection2->query($sqlrt);
    $routesData = $resultrt->fetchAll();
    $routes = array();
    $routes1 = array('' => 'Select Route');
    $routes2 = array();

    foreach ($routesData as $rt) {
        $routes2[$rt['id']] = $rt['route_name'];
    }
    $routes = $routes1 + $routes2;
    //$routes = $routes2;

    $data = '<tr id="routePrice" class="routePrice flex flex-col sm:flex-row justify-between content-center p-0 deltr' . $aid . '">

                                
                                                                            
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group">
            <div class=" mb-1"><div class="flex-1 relative"><select id="route_id" name="route_id[' . $aid . ']" class="w-full routeid">';
    foreach ($routes as $k => $st) {
        $data .= '<option value="' . $k . '">' . $st . '</option>';
    }
    $data .= ' </select></div></div>
        </div></td>
                            
                                                                        
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group">
            <div class=" txtfield kountseat mb-1"><div class="flex-1 relative"><input type="text" id="oneway_price[1]" name="oneway_price[' . $aid . ']" class="numfield w-full txtfield kountseat onewayprice"></div></div>
        </div></td>
                            
                                                                        
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group">
            <div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="twoway_price[1]" name="twoway_price[' . $aid . ']" class="numfield w-full txtfield twowayprice"></div></div>
        </div></td>
                            
                                                                        
        <td class="w-full max-w-full sm:max-w-xs flex justify-end px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group">
            <div class=" txtfield kountseat szewdt mb-1"><div class="flex-1 relative"><input type="text" id="tax[1]" name="tax[' . $aid . ']" class="numfield w-full txtfield kountseat szewdt stoptax"></div></div>
        </div><div class="dte mb-1"  style="font-size: 25px; padding:5px 10px"><i style="cursor:pointer" class="far fa-times-circle delSeattr" data-id="' . $aid . '"></i></div></div></td>
    
    </tr>';


    echo $data;
}


if ($type == 'getAjaxTransportStopFee') {
    $aid = $val;
    echo   $sqlrt = 'SELECT id, stop_name FROM trans_route_stops WHERE route_id = ' . $aid . ' ';

    $resultrt = $connection2->query($sqlrt);
    $stopData = $resultrt->fetchAll();


    $data = '';
    $s = '1';
    foreach ($stopData as $rt) {

        $data .= '<tr id="stopPrice" class="stopPrice flex flex-col sm:flex-row justify-between content-center p-0 deltr' . $aid . '">
            <input type="hidden" name="stop_id[' . $s . ']" value="' . $rt['id'] . '">
                                

        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group">';
        if ($s == '1') {
            $data .= '<div class="dte mb-1"><label for="oneway_price" class="dte inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">Stop Name </label></div>';
        }
        $data .= '<div class=" mb-1"><div class="flex-1 relative">' . $rt['stop_name'] . '</div></div>
        </div></td>
                            
                                                                        
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group">';
        if ($s == '1') {
            $data .= '
        <div class="dte mb-1"><label for="oneway_price" class="dte inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">One Way Price </label></div>';
        }
        $data .= '   <div class=" txtfield kountseat mb-1"><div class="flex-1 relative"><input type="text" id="oneway_price[1]" name="stop_oneway_price[' . $s . ']" class="numfield w-full txtfield kountseat onewaypricestop"></div></div>
        </div></td>
                            
                                                                        
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group">';
        if ($s == '1') {
            $data .= '
        <div class="dte mb-1"><label for="oneway_price" class="dte inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">Two Way Price </label></div>';
        }
        $data .= '  <div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="twoway_price[1]" name="stop_twoway_price[' . $s . ']" class="numfield w-full txtfield twowaypricestop"></div></div>
        </div></td>
                            
                                                                        
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group">';
        if ($s == '1') {
            $data .= '
        <div class="dte mb-1"><label for="oneway_price" class="dte inline-block sm:my-1 sm:max-w-xs font-bold text-sm sm:text-xs">Tax (%)</label></div>';
        }
        $data .= '    <div class=" txtfield kountseat  mb-1"><div class="flex-1 relative"><input type="text" id="tax[1]" name="stop_tax[' . $s . ']" class="numfield w-full txtfield kountseat stoptax"></div></div>
        </div></div></td>
    
        </tr>';
        $s++;
    }
    echo $data;
}




if ($type == 'assign_section') {
    $session->forget(['student_ids']);
    $session->set('student_ids', $val);
    $pupilsightRollGroupID = $_POST['section'];
    $ids = explode(',', $val);
    foreach ($ids as $st) {

        $data1 = array('pupilsightPersonID' => $st, 'pupilsightRollGroupID' => $pupilsightRollGroupID);

        $sql1 = 'UPDATE pupilsightStudentEnrolment SET pupilsightRollGroupID=:pupilsightRollGroupID WHERE pupilsightPersonID=:pupilsightPersonID';
        $result1 = $connection2->prepare($sql1);
        $result1->execute($data1);
    }
}

if ($type == 'deletsStaffAssigned') {
    $ids = explode(',', $val);
    foreach ($ids as $st) {
        $data2 = array('pupilsightPersonID' => $st);
        $sql2 = 'DELETE FROM assignstudent_tostaff WHERE pupilsightPersonID=:pupilsightPersonID';
        $result2 = $connection2->prepare($sql2);
        $result2->execute($data2);
    }
}

if ($type == 'selectStaffToSubject') {
    $session->forget(['staff_sub_id']);
    $session->set('staff_sub_id', $val);
    // $session = $container->get('session');
    // $student_id = $session->get('staff_sub_id');
    // print_r($student_id);die();
}


if ($type == 'selectSubject') {
    $session->forget(['sub_id']);
    $session->set('sub_id', $val);
    $session = $container->get('session');
    $studeaant_id = $session->get('sub_id');
    print_r($studeaant_id);
    die();
}


if ($type == 'remove_section') {
    $session->forget(['student_ids']);
    $session->set('student_ids', $val);
    // echo $pupilsightRollGroupID = $_POST['section'];
    $ids = explode(',', $val);
    foreach ($ids as $st) {

        $data1 = array('pupilsightPersonID' => $st, 'pupilsightRollGroupID' => '');

        $sql1 = 'UPDATE pupilsightStudentEnrolment SET pupilsightRollGroupID=:pupilsightRollGroupID WHERE pupilsightPersonID=:pupilsightPersonID';
        $result1 = $connection2->prepare($sql1);
        $result1->execute($data1);
    }
}


if ($type == 'assigncoresubtoclass') {
    //   $session->forget(['student_ids']);
    // $session->set('student_ids', $val);
    $subjects = $_POST['subjects'];
    $pgrm_val = $_POST['pgrm_val'];
    $class_val = $_POST['class_val'];
    $clses = explode(',',$class_val); 
    $subs = explode(',', $subjects);
    if(!empty($clses) && !empty($subs) && !empty($pgrm_val)){
    foreach ($clses as $cl) {
    foreach ($subs as $sb) {
        $sqlprev = 'SELECT * FROM assign_core_subjects_toclass WHERE pupilsightProgramID = ' . $pgrm_val . ' AND pupilsightYearGroupID = ' . $cl . '  AND pupilsightDepartmentID=' . $sb . ' ';
        $resultprev = $connection2->query($sqlprev);
        $prevData = $resultprev->fetchAll();
        if (count($prevData) < 1) {
        $cuid = $_SESSION[$guid]['pupilsightPersonID'];
        $data1 = array('pupilsightProgramID' => $pgrm_val, 'pupilsightYearGroupID' => $cl, 'pupilsightDepartmentID' => $sb);
        $sql1 = "INSERT INTO assign_core_subjects_toclass SET pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID,pupilsightDepartmentID=:pupilsightDepartmentID";
        $result = $connection2->prepare($sql1);
        $result->execute($data1);
        }
    }
    }
    echo "success";
   } else {
       echo "Some required parametters is missing";
   }
}





if ($type == 'assign_elective_subtoclass') {

    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
    //stud_id,pupilsightProgramID,pupilsightYearGroupID,subjects
    $subjects = $_POST['subjects'];
    $pgrm_val = $_POST['pupilsightProgramID'];
    $class_val = $_POST['pupilsightYearGroupID'];
    $studid =  $_POST['stud_id'];

    //  $ids = explode(',',$val);
    $subs = explode(',', $subjects);
    foreach ($subs as $sb) {

        /// `assign_elective_subjects_tostudents`,`pupilsightProgramID`,`pupilsightYearGroupID`,`pupilsightDepartmentID`,`pupilsightPersonID`
        //to remove duplicate entry for elective language
        $sqlprev = 'SELECT * FROM assign_elective_subjects_tostudents WHERE pupilsightPersonID = ' . $studid . '  AND pupilsightDepartmentID=' . $sb . ' ';

        $resultprev = $connection2->query($sqlprev);
        $prevData = $resultprev->fetchAll();

        if (count($prevData) < 1) {
            //if the subject is not assigned previosly then only will assign
            // echo "no";

            $data1 = array('pupilsightPersonID' => $studid, 'pupilsightProgramID' => $pgrm_val, 'pupilsightYearGroupID' => $class_val, 'pupilsightDepartmentID' => $sb);
            $sql1 = "INSERT INTO assign_elective_subjects_tostudents SET pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID,pupilsightDepartmentID=:pupilsightDepartmentID,pupilsightPersonID=:pupilsightPersonID";
            $result = $connection2->prepare($sql1);
            $result->execute($data1);
        }
    }
}

if ($type == 'chkFeeStructure') {
    $name = $_POST['val'];
    $pupilsightSchoolYearID = $_POST['acyear'];
    $data = array('name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
    $sql = 'SELECT * FROM fn_fee_structure WHERE name=:name AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
    $result = $connection2->prepare($sql);
    $result->execute($data);
    if ($result->rowCount() > 0) {
        echo 'exist';
    } else {
        echo 'new';
    }
}


//bulk_reg_students

if ($type == 'bulk_reg_students') {
    // $session->forget(['student_ids']);
    // $session->set('student_ids', $val);
    $pupilsightProgramID = $_POST['prgm'];
    $pupilsightYearGroupID = $_POST['class_val'];
    $stuid = $_POST['stuid'];

    $ids = explode(',', $stuid);

    // print_r($ids);
    foreach ($ids as $st) {

        $active_status =  1;

        $data1 = array('pupilsightPersonID' => $st, 'active' => $active_status);

        $sql1 = 'UPDATE pupilsightStudentEnrolment SET active=:active WHERE pupilsightPersonID=:pupilsightPersonID';
        $result1 = $connection2->prepare($sql1);
        $result1->execute($data1);

        $sql2 = 'UPDATE pupilsightPerson SET active=:active WHERE pupilsightPersonID=:pupilsightPersonID';
        $result2 = $connection2->prepare($sql2);
        $result2->execute($data1);

        //to delete the status from pupilsight_deregister_students
        $data = array('pupilsightPersonID' => $st);
        $sql = 'SELECT * FROM pupilsightStudentEnrolment WHERE pupilsightPersonID=:pupilsightPersonID ';
        $result = $connection2->prepare($sql);
        $result->execute($data);
        $prevdata =  $result->fetch();

        $data_arr = array('pupilsightStudentEnrolmentID' => $prevdata['pupilsightStudentEnrolmentID']);
        $sql_data = 'SELECT * FROM pupilsight_deregister_students WHERE pupilsightStudentEnrolmentID=:pupilsightStudentEnrolmentID ';
        $result_data = $connection2->prepare($sql_data);
        $result_data->execute($data_arr);
        if ($result_data->rowCount() > 0) {


            $data = array('pupilsightStudentEnrolmentID' => $prevdata['pupilsightStudentEnrolmentID']);
            $sql = 'DELETE FROM pupilsight_deregister_students WHERE pupilsightStudentEnrolmentID=:pupilsightStudentEnrolmentID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        }
    }
}

if ($type == 'selectstaff') {

    $sqld = 'SELECT name,pupilsightDepartmentID AS sub_id FROM pupilsightDepartment';
    $resultd = $connection2->query($sqld);
    $getsuject = $resultd->fetchAll();
    print_r($getsuject);

    // if(!empty($selectstaff)){
    //     foreach($sections as $k=>$cl){ 
    //         $data .= '<option value="'.$cl['pupilsightRollGroupID'].'">'.$cl['name'].'</option>';
    //     }
    // }
    // echo $getsuject;
}


if ($type == 'display_multiple_attend_session') {
    $sess_cnt = $_POST['val'];
    $data = '';
    $s = '1';
    $data='<table align="center" class="table" border="1"><tr><td class=" ">Session No</td><td class=" ">Session Name</td></tr>';
   
    for($i=0;$i<$sess_cnt;$i++){
        $data .= '<tr id="" class="  deltr' . $i . '">
            <td class="text_centr ">'. $s.'</td>     
            <td class="">
                <input type="hidden" name="session_no[' . $s . ']" value="'.$s.'">
                <input type="text" class="sessionName" name="session_name[' . $s . ']" value="">
            </td>
        </tr>';
        $s++;
    }
    $data .= '</table>';
    echo $data;
}

if ($type == 'getSectionTimeTableWise') {
    $cid = $val;
    $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE a.pupilsightYearGroupID = "' . $cid . '" GROUP BY a.pupilsightRollGroupID';
    $result = $connection2->query($sql);
    $sections = $result->fetchAll();
    $data = '<option value="">Select Section</option>';
    if (!empty($sections)) {
        foreach ($sections as $k => $cl) {
            $sqlchk = 'SELECT pupilsightTTID FROM pupilsightTT WHERE find_in_set("'.$cl['pupilsightRollGroupID'].'",pupilsightRollGroupIDList) <> 0 AND pupilsightYearGroupIDList = "'.$cid.'"';
            $resultchk = $connection2->query($sqlchk);
            $chksec = $resultchk->fetch();
            if(empty($chksec['pupilsightTTID'])){
                $data .= '<option value="' . $cl['pupilsightRollGroupID'] . '">' . $cl['name'] . '</option>';
            }
        }
    }
}        


if ($type == 'getStudent') {
    $cid = $val;
    $pupilsightSchoolYearID = $_POST['yid'];
    $pupilsightProgramID = $_POST['pid'];
    $pupilsightYearGroupID = $_POST['cid'];
    $sql = 'SELECT a.*, b.officialName FROM  pupilsightStudentEnrolment AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" AND a.pupilsightRollGroupID = "' . $cid . '" AND pupilsightRoleIDPrimary=003 GROUP BY b.pupilsightPersonID';
    $result = $connection2->query($sql);
    $sections = $result->fetchAll();
    $data = '<option value="">Select Student</option>';
    if (!empty($sections)) {
        foreach ($sections as $k => $cl) {
            $data .= '<option value="' . $cl['pupilsightPersonID'] . '">' . $cl['officialName'] . '</option>';
        }
    }
    echo $data;
}
if ($type == 'getMultiStudent') {
    $cid = $val;
    $pupilsightSchoolYearID = $_POST['yid'];
    $pupilsightProgramID = $_POST['pid'];
    $pupilsightYearGroupID = $_POST['cid'];
    $pupilsightRollGroupID=$_POST['sid'];
    // $clid = explode('-',$cd);
    //     $pupilsightProgramID = $clid[1];
    //     $pupilsightYearGroupID = $clid[0];   
  
    // if(!empty($pupilsightRollGroupID)){
    //     $sql.='AND a.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '"';
    // }

    //$sql. = 'GROUP BY b.pupilsightPersonID';

    //WHERE a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" AND a.pupilsightRollGroupID = "' . $cid . '" AND pupilsightRoleIDPrimary=003 GROUP BY b.pupilsightPersonID';
   
    if (is_array($pupilsightYearGroupID) && (empty($pupilsightRollGroupID)) ){
        $data = '<option value="">Select Student</option>';
        foreach ($pupilsightYearGroupID as $cid){        
                $sql = 'SELECT a.*, b.officialName FROM  pupilsightStudentEnrolment AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightYearGroupID = "' . $cid . '" AND pupilsightRoleIDPrimary=003 GROUP BY b.pupilsightPersonID';
            
            $result = $connection2->query($sql);
    
   
            $sections = $result->fetchAll();
           
            if (!empty($sections)) {
                foreach ($sections as $k => $cl) {
                    $data .= '<option value="' . $cl['pupilsightPersonID'] . '">' . $cl['officialName'] . '</option>';
                }
            }
        }
            echo $data;
    }else if(!empty($pupilsightRollGroupID)){ 
       
$pupilsightRollGroupIDs = explode(',',$pupilsightRollGroupID);
//print_r(count($pupilsightRollGroupIDs));die();
$data = '<option value="">Select Student</option>';
        foreach ($pupilsightRollGroupIDs as $seid){
           
            $csid = explode('-', $seid);
            $sid = $csid[0];
            $cid = $csid[1];      
         
            $sql = 'SELECT a.*, b.officialName FROM  pupilsightStudentEnrolment AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightYearGroupID = "' . $cid . '" AND a.pupilsightRollGroupID = "' . $sid . '" AND pupilsightRoleIDPrimary=003 GROUP BY b.pupilsightPersonID';

            $result = $connection2->query($sql);
    
   
            $sections = $result->fetchAll();
           
            //print_r();
            if (!empty($sections)) {
                foreach ($sections as $k => $cl) {
                    $data .= '<option value="' . $cl['pupilsightPersonID'] . '">' . $cl['officialName'] . '</option>';
                }
            }
            
        }
        
        echo $data;
    }else{
        $sql = 'SELECT a.*, b.officialName FROM  pupilsightStudentEnrolment AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID ';
        if(!empty($pupilsightSchoolYearID)){
                    $sql.='WHERE a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '"';
                }
        if(!empty($pupilsightProgramID)){
            $sql.=' AND a.pupilsightProgramID = "' . $pupilsightProgramID . '"';
        }
        if(!empty($pupilsightYearGroupID)){
            $sql.=' AND a.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '"';
        }
        $result = $connection2->query($sql);
    
   
        $sections = $result->fetchAll();
        $data = '<option value="">Select Student</option>';
        if (!empty($sections)) {
            foreach ($sections as $k => $cl) {
                $data .= '<option value="' . $cl['pupilsightPersonID'] . '">' . $cl['officialName'] . '</option>';
            }
        }
        echo $data;

    }

    
 
}
if($type == 'attendancePeriod'){
    $session->forget(['period_ids']);
    $session->set('period_ids', $val);   
}

if($type == 'addSubjectSkills'){
    $skid = $val;
    $subid = explode(',', $_POST['subid']); 
    $skillname = explode(',', $_POST['skillname']);  
    $pupilsightSchoolYearID = $_POST['academicId'];
    $pupilsightProgramID = $_POST['programId'];
    $classId = $_POST['classId'];
    foreach($subid as $sub){
        $data1 = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $classId, 'pupilsightDepartmentID' => $sub);
        $sql1 = 'DELETE FROM subjectSkillMapping WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID';
        $result1 = $connection2->prepare($sql1);
        $result1->execute($data1);
        foreach($skillname as $skname){
            $skillidname = explode('-', $skname);
            $sid = $skillidname[0];
            $name = $skillidname[1];
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $classId, 'pupilsightDepartmentID' => $sub, 'skill_id' => $sid, 'skill_display_name' => $name);
            $sql = "INSERT INTO subjectSkillMapping SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightDepartmentID=:pupilsightDepartmentID, skill_id=:skill_id, skill_display_name=:skill_display_name";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        }
    }
}

if($type == 'subjectToClassId'){
    $session->forget(['subjectToClassId']);
    $session->set('subjectToClassId', $val);   
}

if($type == 'getSubjectSkills'){
    $subid = $val; 
    $pupilsightSchoolYearID = $_POST['academicId'];
    $pupilsightProgramID = $_POST['programId'];
    $classId = $_POST['classId'];
    $chk = $_POST['chk'];
    $sql = 'SELECT * FROM ac_manage_skill';
    $result = $connection2->query($sql);
    $subskills = $result->fetchAll();
    $data = '';
    foreach($subskills as $k=>$subsk){
        $sqls = 'SELECT * FROM subjectSkillMapping WHERE skill_id = '.$subsk['id'].' AND pupilsightSchoolYearID= '.$pupilsightSchoolYearID.' AND pupilsightProgramID= '.$pupilsightProgramID.' AND pupilsightYearGroupID= '.$classId.' AND pupilsightDepartmentID= '.$subid.' ';
        $results = $connection2->query($sqls);
        $skills = $results->fetch();
        if(!empty($skills['skill_display_name']) && $chk== 'checked'){
            $sname = $skills['skill_display_name'];
            $checked = 'checked';
        } else {
            $sname = $subsk['name'];
            $checked = '';
        }
        
        
        $data .= "<tr><td><input type='checkbox' class='skillId' name='skill_id[]'  data-id='".$subsk["id"]."' ".$checked."></td><td>".$subsk['name']."</td><td><input type='textbox'  name='skill_display_name' id='sname".$subsk["id"]."' value='".$sname."' style='border:1px solid gray'></td>/tr>";
    }
    echo $data;

}

if ($type == 'copytesttonextyear') {
    $testid = $_POST['val'];
    $next_acyr = $_POST['next_acyr'];
  //  $testids = explode(',',$testid);
     
        $sql_tst = 'SELECT * FROM examinationTest WHERE id IN (' . $testid . ')';
        $result_test = $connection2->query($sql_tst);
      //  $tests = $result_test->fetchAll();

        while ($row = $result_test->fetch()) {
            //Write to database
          
                $dataInsert = array('pupilsightSchoolYearID' => $next_acyr, 'name' => $row['name'], 'code' => $row['code']);
                $sqlInsert = 'INSERT INTO examinationTest SET pupilsightSchoolYearID=:pupilsightSchoolYearID, name=:name, code=:code';
                $resultInsert = $connection2->prepare($sqlInsert);
                $resultInsert->execute($dataInsert);
           
        }
  
}

if($type == 'selectSection'){
    $session->forget(['section_ids']);
    $session->set('section_ids', $val);   
}

if($type == 'selectSub'){
    $session->forget(['subject_ids']);
    $session->set('subject_ids', $val);   
}

if ($type == 'changeGradeSystemCondition') {
    $val = $_POST['val'];
    $sid = $_POST['sid'];
    
    $data1 = array('id' => $sid, 'pass_fail_condition' => $val);
    $sql1 = 'UPDATE examinationGradeSystem SET pass_fail_condition=:pass_fail_condition WHERE id=:id';
    $result1 = $connection2->prepare($sql1);
    $result1->execute($data1);
}

if ($type == 'addtestIdinSession') {
    $session->forget(['id']);
    $session->set('testid', $val);
}

if ($type == 'getSection_checkbox') {
    $cid = $val;
    $pid = $_POST['pid'];
    $data = '<ul class=""  style="background: #fff;
    width: 151px;
    position: absolute;
    z-index: 1;
    border: 1px solid gray;">';
    foreach($cid as $sid){
        $sql = 'SELECT a.*, b.name ,c.name AS cname,c.pupilsightYearGroupID as cid FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID   LEFT JOIN pupilsightYearGroup as c ON a.pupilsightYearGroupID = c.pupilsightYearGroupID  WHERE a.pupilsightProgramID = "' . $pid . '" AND a.pupilsightYearGroupID = "' . $sid . '" GROUP BY a.pupilsightRollGroupID';

       
        $result = $connection2->query($sql);
        $sections = $result->fetchAll();
       
        
        if (!empty($sections)) {            
            foreach ($sections as $k => $cl) {
                $data .= '<li class="check_mrgin getClasses" ><input  id="classSectionDtl" type ="checkbox" name="pupilsightRollGroupID[]"  data-section="'. $cl['pupilsightRollGroupID']."-". $cl['cid'] .'" data-val="'.$cl['cname'].$cl['name'].'" value="' . $cl['pupilsightRollGroupID'].",". $cl['cid'] .'">' .$cl['cname']." ". $cl['name']." </li>" ;
            }

            
        }
    
        
    }
    $data .="</ul>";
    echo $data;
}

if ($type == 'getSection_checkbox_td'){
    $cid = $val;
    $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE a.pupilsightYearGroupID = "' . $cid . '" GROUP BY a.pupilsightRollGroupID';
    $result = $connection2->query($sql);
    $sections = $result->fetchAll();
    $data = ' ';
    if (!empty($sections)) {
        foreach ($sections as $k => $cl) {
            $data .= '<tr><td>
            <input class="check_mrgin" type ="checkbox" name="pupilsightRollGroupID[]" value="' . $cl['pupilsightRollGroupID'] . '">'. " </td>
            <td>". $cl['name']."</td></tr>" ;
        }
    }
    echo $data;
}


if($type == 'electiveGroupId'){
    $session->forget(['electiveGroupId']);
    $session->set('electiveGroupId', $val);   
}

if ($type == 'stdMarksEntry') {

    $studentmarks_id='';
    $session->forget(['studentmarks_id']);
    $session->set('studentmarks_id', $val); 
    $test_id = $_POST['test_id'];
    $session->forget(['test_id']);
    $session->set('test_id', $test_id); 
    echo 'index.php?q=/modules/Academics/entry_marks_byStudent.php' ;
}

if ($type == 'storetestId') {     
    $session->forget(['test_id']);
    $session->set('test_id', $val);
  
  echo 'index.php?q=/modules/Academics/entry_marks_byStudent.php' ; 
}

if($type=='pre_stdMarksEntry'){
        $studentmarks_id = '';

        $sql = 'SELECT * FROM pupilsightStudentEnrolment WHERE pupilsightPersonID= "'.$val.'" ';
        $res = $connection2->query($sql);
        $stu_detail = $res->fetch();

    $sqlp = 'SELECT pupilsightPerson.pupilsightPersonID  FROM pupilsightPerson 
    JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
    JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)  
    JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) 
     
    WHERE  pupilsightStudentEnrolment.pupilsightProgramID="'.$stu_detail['pupilsightProgramID'].'"  AND pupilsightStudentEnrolment.pupilsightYearGroupID="'.$stu_detail['pupilsightYearGroupID'].'" AND pupilsightStudentEnrolment.pupilsightRollGroupID="'.$stu_detail['pupilsightRollGroupID'].'" AND  pupilsightPerson.pupilsightPersonID < "'.$val.'"  ORDER BY pupilsightPerson.pupilsightPersonID DESC
        ';
    $resultp = $connection2->query($sqlp);
    $previous = $resultp->fetch();

   // print_r($rowdataprog);die();
   
    $session->forget(['studentmarks_id']);
    $session->set('studentmarks_id', implode(' ',$previous));
}

if($type=='next_stdMarksEntry'){

    $sql = 'SELECT * FROM pupilsightStudentEnrolment WHERE pupilsightPersonID= "'.$val.'" ';
    $res = $connection2->query($sql);
    $stu_detail = $res->fetch();
    $studentmarks_id = '';
    $sqlp = 'SELECT pupilsightPerson.pupilsightPersonID  FROM pupilsightPerson 
    JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
    JOIN pupilsightYearGroup ON (pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID)  
    JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) 
    
    WHERE  pupilsightStudentEnrolment.pupilsightProgramID="'.$stu_detail['pupilsightProgramID'].'"  AND pupilsightStudentEnrolment.pupilsightYearGroupID="'.$stu_detail['pupilsightYearGroupID'].'" AND pupilsightStudentEnrolment.pupilsightRollGroupID="'.$stu_detail['pupilsightRollGroupID'].'" AND pupilsightPerson.pupilsightPersonID > "'.$val.'"  ORDER BY pupilsightPerson.pupilsightPersonID ASC
        ';
    $resultp = $connection2->query($sqlp);
    $previous = $resultp->fetch();

    // print_r($rowdataprog);die();

    $session->forget(['studentmarks_id']);
    $session->set('studentmarks_id', implode(' ',$previous));
}

if ($type == 'deleteAssignCls'){
    $cid = explode(',', $val);
    foreach($cid as $cd){
        $clid = explode('-',$cd);
        $test_id = $clid[0];
        $pupilsightProgramID = $clid[1];
        $pupilsightYearGroupID = $clid[2];

        $data2 = array('test_master_id' => $test_id, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID'=> $pupilsightYearGroupID);
        $sql2 = 'DELETE FROM examinationTestAssignClass WHERE test_master_id=:test_master_id AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID';
        $result2 = $connection2->prepare($sql2);
        $result2->execute($data2);
    }
}

if ($type == 'addElectiveSubjectToStudent'){
    $pupilsightPersonID = $val;
    $pupilsightDepartmentID = $_POST['sid'];
    $pupilsightProgramID = $_POST['progId'];
    $pupilsightYearGroupID = $_POST['classId'];
    $electiveId = $_POST['eid'];
    $chktype = $_POST['chktype'];

    if($chktype == 'add'){
        $sqlprev = 'SELECT id FROM assign_elective_subjects_tostudents WHERE pupilsightPersonID = '.$pupilsightPersonID.'  AND pupilsightDepartmentID= '.$pupilsightDepartmentID.' AND  pupilsightProgramID = '.$pupilsightProgramID.' AND pupilsightYearGroupID = '.$pupilsightYearGroupID.'';
        $resultprev = $connection2->query($sqlprev);
        $prevData = $resultprev->fetch();

        if ($prevData < 1) {
            $data1 = array('ac_elective_group_id'=>$electiveId, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightDepartmentID' => $pupilsightDepartmentID);
            $sql1 = "INSERT INTO assign_elective_subjects_tostudents SET ac_elective_group_id=:ac_elective_group_id, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID,pupilsightDepartmentID=:pupilsightDepartmentID,pupilsightPersonID=:pupilsightPersonID";
            $result = $connection2->prepare($sql1);
            $result->execute($data1);
        } else {
            $data1 = array('ac_elective_group_id' => $electiveId, 'id' => $prevData['id']);
            $sql1 = "UPDATE assign_elective_subjects_tostudents SET ac_elective_group_id=:ac_elective_group_id WHERE id=:id";
            $result = $connection2->prepare($sql1);
            $result->execute($data1);
        }
    } else {
        $data2 = array('ac_elective_group_id'=>$electiveId, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightDepartmentID' => $pupilsightDepartmentID);

        $sql2 = 'DELETE FROM assign_elective_subjects_tostudents WHERE  ac_elective_group_id=:ac_elective_group_id AND pupilsightPersonID=:pupilsightPersonID AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID';
        $result2 = $connection2->prepare($sql2);
        $result2->execute($data2);
    }
}    
    
if ($type == 'getgrade_based_onmark') {
    $marks_enter = $val;
    $sql = 'SELECT grade_name,id FROM examinationGradeSystemConfiguration  WHERE '.$marks_enter.' BETWEEN `lower_limit` AND `upper_limit`';
    $result = $connection2->query($sql);
    $grade = $result->fetch();
   echo $grade['id'];
   
}
if ($type == 'getgrade_based_onmark_new') {
    $marks_enter = $val;
    $gradeSystemId=$_POST['gsid'];
   $sql = 'SELECT grade_name,id FROM examinationGradeSystemConfiguration  WHERE gradeSystemId="'.$gradeSystemId.'" AND  ('.$marks_enter.' BETWEEN `lower_limit` AND `upper_limit`)';
    $result = $connection2->query($sql);
    $grade = $result->fetch();
   echo $grade['id'];
   
}
if ($type == 'lock_unlock_mark_entry') {

   
    $entryids = explode(',', $_POST['val']); 
    if($_POST['action_type']=='lock_mark_entry')
    {       $lock_status =  1;
            foreach($entryids as $k=>$enid){
                $data1 = array('id' => $enid, 'status' => $lock_status);
                $sql1 = 'UPDATE examinationMarksEntrybySubject SET  status=:status WHERE id=:id';
                $result1 = $connection2->prepare($sql1);
                $result1->execute($data1);
            }

        }
      else  if($_POST['action_type']=='unlock_mark_entry')
        {      $lock_status =  0;
                foreach($entryids as $k=>$enid){
                    $data1 = array('id' => $enid, 'status' => $lock_status);
                    $sql1 = 'UPDATE examinationMarksEntrybySubject SET  status=:status WHERE id=:id';
                    $result1 = $connection2->prepare($sql1);
                    $result1->execute($data1);
                }
    
            } 
}

if($type=='getSubjectTimeslote')
{
    $dep_id = $_POST['val'];
    $class_id =$_POST['class_id'];
  //  $sql = 'SELECT a.*,c.pupilsightCourseID FROM  pupilsightTTColumnRow AS a LEFT JOIN pupilsightTTDayRowClass AS b ON a.pupilsightTTColumnRowID = b.pupilsightTTColumnRowID LEFT JOIN pupilsightCourseClass AS c ON b.pupilsightCourseClassID = c.pupilsightCourseClassID LEFT JOIN pupilsightCourse AS d ON c.pupilsightCourseID = d.pupilsightCourseID WHERE d.pupilsightYearGroupIDList = "' . $class_id . '"  AND  d.pupilsightDepartmentID = "' . $dep_id . '" GROUP By c.pupilsightCourseID  ';
   //echo  $sql = 'SELECT a.pupilsightCourseID,a.pupilsightYearGroupIDList,a.pupilsightDepartmentID, b.pupilsightCourseClassID,c.pupilsightTTColumnRowID,d.pupilsightTTColumnRowID,d.nameShort,d.name FROM  pupilsightCourse AS a LEFT JOIN pupilsightCourseClass AS b ON a.pupilsightCourseID = b.pupilsightCourseID LEFT JOIN pupilsightTTDayRowClass AS c ON b.pupilsightCourseClassID = c.pupilsightCourseClassID LEFT JOIN pupilsightTTColumnRow AS d ON c.pupilsightTTColumnRowID = d.pupilsightTTColumnRowID WHERE a.pupilsightYearGroupIDList = "' . $class_id . '"  AND  a.pupilsightDepartmentID = "' . $dep_id . '"  ';
   $pupilsightTTDayID='00000002';
  // $data = array('pupilsightTTDayID' => $pupilsightTTDayID);
        $sql = "SELECT pupilsightTTColumnRow.*, COUNT(DISTINCT pupilsightTTDayRowClassID) AS classCount
                FROM pupilsightTTDay
                JOIN pupilsightTTColumnRow ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTDay.pupilsightTTColumnID)
                LEFT JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID AND pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID)
                LEFT JOIN pupilsightCourseClass  ON pupilsightCourseClass.pupilsightCourseClassID = pupilsightTTDayRowClass.pupilsightCourseClassID 
                 LEFT JOIN pupilsightCourse  ON pupilsightCourseClass.pupilsightCourseID = pupilsightCourse.pupilsightCourseID 
                WHERE pupilsightTTDay.pupilsightTTDayID=".$pupilsightTTDayID." AND pupilsightCourse.pupilsightYearGroupIDList = ".$class_id."  AND  pupilsightCourse.pupilsightDepartmentID = ".$dep_id." 
                GROUP BY pupilsightTTColumnRow.pupilsightTTColumnRowID
                ORDER BY pupilsightTTColumnRow.timeStart, pupilsightTTColumnRow.name";
   $result = $connection2->query($sql);
    $timeslot = $result->fetchAll();

    //echo "<pre>";print_r($timeslot);
    
    $data = '<option value="">Select Timeslot</option>';
    if (!empty($timeslot)) {
        foreach ($timeslot as $k => $cl) {
           $data .= '<option value="' . $cl['pupilsightTTColumnRowID'] . '">' . $cl['nameShort'] .'('.$cl['timeStart'].'-'.$cl['timeEnd'].')' .'</option>';
        }
    }
    echo $data;
}
if($type=='getSubjectTimesloteNew')
{
    $dep_id = $_POST['val'];
    $class_id =$_POST['class_id'];
  //  $sql = 'SELECT a.*,c.pupilsightCourseID FROM  pupilsightTTColumnRow AS a LEFT JOIN pupilsightTTDayRowClass AS b ON a.pupilsightTTColumnRowID = b.pupilsightTTColumnRowID LEFT JOIN pupilsightCourseClass AS c ON b.pupilsightCourseClassID = c.pupilsightCourseClassID LEFT JOIN pupilsightCourse AS d ON c.pupilsightCourseID = d.pupilsightCourseID WHERE d.pupilsightYearGroupIDList = "' . $class_id . '"  AND  d.pupilsightDepartmentID = "' . $dep_id . '" GROUP By c.pupilsightCourseID  ';
   //echo  $sql = 'SELECT a.pupilsightCourseID,a.pupilsightYearGroupIDList,a.pupilsightDepartmentID, b.pupilsightCourseClassID,c.pupilsightTTColumnRowID,d.pupilsightTTColumnRowID,d.nameShort,d.name FROM  pupilsightCourse AS a LEFT JOIN pupilsightCourseClass AS b ON a.pupilsightCourseID = b.pupilsightCourseID LEFT JOIN pupilsightTTDayRowClass AS c ON b.pupilsightCourseClassID = c.pupilsightCourseClassID LEFT JOIN pupilsightTTColumnRow AS d ON c.pupilsightTTColumnRowID = d.pupilsightTTColumnRowID WHERE a.pupilsightYearGroupIDList = "' . $class_id . '"  AND  a.pupilsightDepartmentID = "' . $dep_id . '"  ';
   $pupilsightTTDayID='00000002';
  // $data = array('pupilsightTTDayID' => $pupilsightTTDayID);
        $sql = "SELECT pupilsightTTColumnRow.*, COUNT(DISTINCT pupilsightTTDayRowClassID) AS classCount
                FROM pupilsightTTDay
                JOIN pupilsightTTColumnRow ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTDay.pupilsightTTColumnID)
                LEFT JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID AND pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID)
                LEFT JOIN pupilsightCourseClass  ON pupilsightCourseClass.pupilsightCourseClassID = pupilsightTTDayRowClass.pupilsightCourseClassID 
                 LEFT JOIN pupilsightCourse  ON pupilsightCourseClass.pupilsightCourseID = pupilsightCourse.pupilsightCourseID 
                WHERE pupilsightTTDay.pupilsightTTDayID=".$pupilsightTTDayID." AND pupilsightCourse.pupilsightYearGroupIDList = ".$class_id."  AND  pupilsightTTDayRowClass.pupilsightDepartmentID = ".$dep_id." 
                GROUP BY pupilsightTTColumnRow.pupilsightTTColumnRowID
                ORDER BY pupilsightTTColumnRow.timeStart, pupilsightTTColumnRow.name";
   $result = $connection2->query($sql);
    $timeslot = $result->fetchAll();

    //echo "<pre>";print_r($timeslot);
    
    $data = '<option value="">Select Timeslot</option>';
    if (!empty($timeslot)) {
        foreach ($timeslot as $k => $cl) {
           $data .= '<option value="' . $cl['pupilsightTTColumnRowID'] . '">' . $cl['nameShort'] .'('.$cl['timeStart'].'-'.$cl['timeEnd'].')' .'</option>';
        }
    }
    echo $data;
}

if($type == 'getTestBySection'){
    $pupilsightYearGroupID = $_POST['cid'];
    $pupilsightRollGroupID = $val;
    $pupilsightProgramID = $_POST['pid'];
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

    $sql = 'SELECT b.id, b.name FROM examinationTestAssignClass AS a LEFT JOIN examinationTest AS b ON a.test_id = b.id  WHERE a.pupilsightSchoolYearID= '.$pupilsightSchoolYearID.' AND a.pupilsightProgramID = '.$pupilsightProgramID.' AND a.pupilsightYearGroupID = '.$pupilsightYearGroupID.' AND a.pupilsightRollGroupID = '.$pupilsightRollGroupID.' ';
    $result = $connection2->query($sql);
    $tests = $result->fetchAll();
    $returndata = '<option value="">Select Test</option>';
    foreach ($tests as $row) {
        $returndata .= '<option value=' . $row['id'] . ' >' . $row['name'] . '</option>';
    }
    echo $returndata;
}

if($type == 'updateBulkTest'){
   $tid = $val;
   $coulmnname = $_POST['testcol'];
   $coulmndata = $_POST['testdata'];
   $sq = "update examinationTest SET ".$coulmnname." ='".$coulmndata."' where id IN (".$tid.") ";
   $connection2->query($sq);
}

if($type == 'updateBulkTestettings'){
    $tid = $val;
    $coulmnname = $_POST['testcol'];
    $coulmndata = $_POST['testdata'];
    $sq = "update examinationTest SET ".$coulmnname." ='".$coulmndata."' where id IN (".$tid.") ";
    $connection2->query($sq);
}

if ($type == 'getSubjectbasedonclass') {
    $roleid= $_POST['roleid'];
    $pupilsightPersonID= $_POST['pupilsightPersonID'];
    if($roleid=='002')//for teacher login
    {
        $sq = "select DISTINCT subjectToClassCurriculum.pupilsightDepartmentID, subjectToClassCurriculum.subject_display_name from subjectToClassCurriculum  LEFT JOIN assignstaff_tosubject ON subjectToClassCurriculum.pupilsightDepartmentID = assignstaff_tosubject.pupilsightDepartmentID  LEFT JOIN pupilsightStaff ON assignstaff_tosubject.pupilsightStaffID = pupilsightStaff.pupilsightStaffID  where pupilsightYearGroupID ='".$val."' AND pupilsightStaff.pupilsightPersonID='".$pupilsightPersonID."' order by subject_display_name asc";
    }
    else
    {
        $sq = "select pupilsightDepartmentID, subject_display_name, di_mode from subjectToClassCurriculum where pupilsightYearGroupID ='".$val."' order by subject_display_name asc";
    }
   
    $result = $connection2->query($sq);
    $rowdata = $result->fetchAll();
    $returndata = '<option value="">Select Subject</option>';
    foreach ($rowdata as $row) {
        $returndata .= '<option value=' . $row['pupilsightDepartmentID'] . '  data-dimode=' . $row['di_mode'] . '>' . $row['subject_display_name'] . '</option>';
    }
    echo $returndata;
}

if ($type == 'getNewSectionByClassProg') {
    $cid = $val;
    $pid = $_POST['pid'];
    $sqlsec = 'SELECT GROUP_CONCAT(pupilsightRollGroupID) AS secId FROM pupilsightProgramClassSectionMapping  WHERE pupilsightProgramID = "' . $pid . '" AND pupilsightYearGroupID = "' . $cid . '" ';
    $resultsec = $connection2->query($sqlsec);
    $secdata = $resultsec->fetch();
    $sqlId = $secdata['secId'];
    if(!empty($sqlId)){
        $sqlId = $sqlId;
    } else {
        $sqlId = '0';
    }

    $sql = 'SELECT pupilsightRollGroupID, name FROM pupilsightRollGroup  WHERE pupilsightRollGroupID Not In ('.$sqlId.')  GROUP BY pupilsightRollGroupID';
    $result = $connection2->query($sql);
    $sections = $result->fetchAll();
    $data = '<option value="">Select Section</option>';
    if (!empty($sections)) {
        foreach ($sections as $k => $cl) {
            $data .= '<option value="' . $cl['pupilsightRollGroupID'] . '">' . $cl['name'] . '</option>';
        }
    }
    echo $data;
}


if ($type == 'getClasses_assignedtoStaff') {
    $id=$val;
    $pid = $_POST['pid'];
    $sql = 'SELECT a.*, c.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN assignstaff_toclasssection b ON(a.pupilsightMappingID =b.pupilsightMappingID)  LEFT JOIN pupilsightYearGroup AS c ON a.pupilsightYearGroupID =c.pupilsightYearGroupID  WHERE a.pupilsightProgramID = "' . $pid . '" AND b.pupilsightPersonID="'.$id.'" GROUP BY a.pupilsightYearGroupID';
    $result = $connection2->query($sql);
    $classes = $result->fetchAll();
    // echo '<pre>';
    // print_r($classes);
    // echo '</pre>';
    $data = '<option value="">Select Class</option>';
    if (!empty($classes)) {
        foreach ($classes as $k => $cl) {
            $data .= '<option value="' . $cl['pupilsightYearGroupID'] . '">' . $cl['name'] . '</option>';
        }
    }
    echo $data;
}


if ($type == 'getsections_assignedtoStaff') {
$id=$val;
$cid = $_POST['cid'];
$sql = 'SELECT a.*, c.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN assignstaff_toclasssection b ON(a.pupilsightMappingID =b.pupilsightMappingID) LEFT JOIN pupilsightRollGroup AS c ON a.pupilsightRollGroupID = c.pupilsightRollGroupID WHERE a.pupilsightYearGroupID = "' . $cid . '" AND b.pupilsightPersonID="'.$id.'" GROUP BY a.pupilsightRollGroupID';
$result = $connection2->query($sql);
$sections = $result->fetchAll();
$data = '<option value="">Select Section</option>';
if (!empty($sections)) {
    foreach ($sections as $k => $cl) {
        $data .= '<option value="' . $cl['pupilsightRollGroupID'] . '">' . $cl['name'] . '</option>';
    }
}
echo $data;
}

if ($type == 'addstudentid_toassign_section') {
    $session->forget(['student_ids']);
    $session->set('student_ids', $val);
    $sqlp = 'SELECT  pupilsightStudentEnrolment.pupilsightProgramID,pupilsightStudentEnrolment.pupilsightYearGroupID  FROM pupilsightPerson 
    JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) 
    WHERE pupilsightPerson.pupilsightPersonID IN (' . $val . ') 
        ';
    $resultp = $connection2->query($sqlp);
    $rowdata = $resultp->fetchAll();
    $result_arr=array();
    $result_arr1=array();
    foreach( $rowdata as $rdata)
    {   $result_arr+=$rdata;
        $result_arr1=array_diff($result_arr,$rdata);
    }
  echo   count($result_arr1);

}


if ($type == 'getsession') {
 $pid= $val;   
    $sqlp = 'SELECT a.session_no, a.session_name FROM attn_session_settings AS a LEFT JOIN attn_settings AS b ON(a.attn_settings_id =b.id) WHERE b.pupilsightProgramID='.$pid.' ';
    $resultp = $connection2->query($sqlp);
    $rowdatasession = $resultp->fetchAll();
    $data = '<option value="">Select Session</option>';
if (!empty($rowdatasession)) {
    foreach ($rowdatasession as $k => $cl) {
        $data .= '<option value="' . $cl['session_no'] . '">' . $cl['session_name'] . '</option>';
    }
}
echo $data;
}

if ($type == 'getCampaignStatusButton') {
    $id= $val;   
    $cid = $_POST['cid'];
    $fid = $_POST['fid'];
    $userId = $_SESSION[$guid]['pupilsightPersonID'];
    $sqlstf = 'Select pupilsightStaffID FROM pupilsightStaff WHERE pupilsightPersonID = '.$userId.'';
    $resultstf = $connection2->query($sqlstf);
    $staff = $resultstf->fetch();
    $staffId = $staff['pupilsightStaffID'];
    if($id == 'all'){
        $sql = 'Select a.*, b.notification FROM workflow_transition as a LEFT JOIN workflow_state as b ON a.to_state = b.id WHERE a.campaign_id = '.$cid.' AND FIND_IN_SET("'.$staffId.'",user_permission) ';
        $resultval = $connection2->query($sql);
        $stats = $resultval->fetchAll();

        $data ='';
        if(!empty($stats)){
            foreach($stats as $s){
                $data .= '<button class="btn btn-primary statesButton"  data-formid = '.$fid.' data-name="'.$s['transition_display_name'].'" data-sid='.$s['id'].' data-cid='.$cid.' data-noti='.$s['notification'].' data-remark="'.$s['enable_remark'].'" style="margin:5px" >'.ucwords($s['transition_display_name']).'</button>';
            }
        }

        echo $data;
    } else {
        
        // $sql = 'SELECT GROUP_CONCAT(state_id) AS sid FROM campaign_form_status WHERE submission_id ='.$id.' ';
        // $result = $connection2->query($sql);
        // $stateid = $result->fetch();

        // if(!empty($stateid['sid'])){
        //     $sid = $stateid['sid'];
        // } else {
        //     $sid = 0;
        // }


        $sql = 'SELECT a.id, to_state FROM campaign_form_status AS a LEFT JOIN workflow_transition AS b ON a.state_id = b.id WHERE a.submission_id ='.$id.' ORDER BY a.id DESC LIMIT 0,1 ';
        $result = $connection2->query($sql);
        $stateid = $result->fetch();
        
        if(!empty($stateid['to_state'])){
            $fromState = $stateid['to_state'];
            
            $sql = 'Select a.*, b.notification FROM workflow_transition as a LEFT JOIN workflow_state as b ON a.to_state = b.id WHERE a.campaign_id = '.$cid.' AND a.from_state = '.$fromState.'  AND FIND_IN_SET("'.$staffId.'",user_permission) ';
            $resultval = $connection2->query($sql);
            $stats = $resultval->fetchAll();
        } else {

            $sq = 'SELECT a.id FROM workflow_state AS a LEFT JOIN workflow_map AS b ON a.workflowid = b.workflow_id WHERE a.name = "Submitted" AND b.campaign_id = '.$cid.' ';
            $resultsq = $connection2->query($sq);
            $sqstate = $resultsq->fetch();
            $fromStateSub = $sqstate['id'];
            
            if(!empty($fromStateSub)){
                $sql = 'Select a.*, b.notification FROM workflow_transition as a LEFT JOIN workflow_state as b ON a.to_state = b.id WHERE a.campaign_id = '.$cid.' AND a.from_state = '.$fromStateSub.' AND FIND_IN_SET("'.$staffId.'",user_permission) ';
            } else {
                $sql = 'Select a.*, b.notification FROM workflow_transition as a LEFT JOIN workflow_state as b ON a.to_state = b.id WHERE a.campaign_id = '.$cid.' AND FIND_IN_SET("'.$staffId.'",user_permission) ';
            }
            
            $resultval = $connection2->query($sql);
            $stats = $resultval->fetchAll();
        }

        // $sql = 'Select a.*, b.notification FROM workflow_transition as a LEFT JOIN workflow_state as b ON a.to_state = b.id WHERE a.campaign_id = '.$cid.' AND a.id NOT IN ('.$sid.')  AND FIND_IN_SET("'.$staffId.'",user_permission) ';


        

        $data ='';
        if(!empty($stats)){
            foreach($stats as $s){
                $data .= '<button class="btn btn-primary statesButton"  data-formid = '.$fid.' data-name="'.$s['transition_display_name'].'" data-sid='.$s['id'].' data-cid='.$cid.' data-noti='.$s['notification'].' data-remark="'.$s['enable_remark'].'" style="margin:5px" >'.ucwords($s['transition_display_name']).'</button>';
            }
        }

        echo $data;
    }
}

if($type == 'chkInvoice'){
    $sql = 'SELECT invoice_no FROM fn_fee_invoice_applicant_assign WHERE submission_id = '.$val.' ';
    $result = $connection2->query($sql);
    $rowdata = $result->fetch();
    if(!empty($rowdata['invoice_no'])){
        echo 'yes';
    } else {
        echo 'no';
    }
}

if ($type == 'applicantInvoiceFeeItem') {
    $id = $_POST['val'];
    $sid = $_POST['sid'];

    $sqli = 'SELECT e.submission_id,a.*,a.id as itemid, b.*, b.id as ifid, b.name as feeitemname, c.id AS invoiceid, c.transport_schedule_id, d.format, e.invoice_no as stu_invoice_no, f.item_type, f.name, f.min_invoice, f.max_invoice, f.amount_in_percent, f.amount_in_number  FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_items AS b ON a.fn_fee_item_id = b.id LEFT JOIN fn_fee_invoice AS c ON a.fn_fee_invoice_id = c.id LEFT JOIN fn_fee_series AS d ON c.inv_fn_fee_series_id = d.id LEFT JOIN fn_fee_invoice_applicant_assign AS e ON c.id = e.fn_fee_invoice_id  LEFT JOIN fn_fee_discount_item as f ON c.fn_fees_discount_id = f.fn_fees_discount_id AND a.fn_fee_item_id = f.fn_fee_item_id WHERE a.fn_fee_invoice_id IN (' . $id . ') AND e.submission_id = ' . $sid . ' GROUP BY a.id';
    $resulti = $connection2->query($sqli);
    $feeItem = $resulti->fetchAll();

    $data = '';
    // echo '<pre>';
    // print_r($feeItem);
    // echo '</pre>';
    foreach ($feeItem as $fI) {
        $totalamount = $fI['total_amount'];
        
        $sqlchk = 'SELECT COUNT(a.id) as kount FROM fn_fees_applicant_collection AS a LEFT JOIN fn_fees_collection AS b ON a.transaction_id = b.transaction_id WHERE a.fn_fee_invoice_item_id = ' . $fI['itemid'] . ' AND a.submission_id = ' . $sid . ' AND b.transaction_status = "1" ';

        $resultchk = $connection2->query($sqlchk);
        $itemchk = $resultchk->fetch();

        if ($itemchk['kount'] == '1') {
            $cls = '';
            $checked = 'checked disabled';
            $status = 'Paid';
        } else {
            $cls = 'selFeeItem';
            $checked = '';
            $status = '';
        }

        // $inid = '000'.$id;
        // $invno = str_replace("0001",$inid,$fI['format']);
        if ($fI['item_type'] == 'Fixed') {
            $discount = $fI['amount_in_number'];
            $discountamt = $fI['amount_in_number'];
        } else {
            $discount = $fI['amount_in_percent'] . '%';
            $discountamt = ($fI['amount_in_percent'] / 100) * $totalamount;
        }
        $amtdiscount = $totalamount - $discountamt;

        if ($itemchk['kount'] == '1') {
            $cls = '';
            $checked = 'checked disabled';
            $status = 'Paid';
            $paid = $amtdiscount;
            $pending = '0';
        } else {
            $cls = 'selFeeItem';
            $checked = '';
            $status = '';
            $paid = '0';
            $pending = $amtdiscount;
        }

        $data .= '<tr class="odd invrow' . $id . '" role="row">
                  
            <td>
                  <div class="inline flex-1 relative"><label class="leading-normal" for="feeItemid"></label> <input type="checkbox" class="' . $cls . '" data-invid="' . $fI['invoiceid'] . '" data-totamt="' . $totalamount . '" data-amt="' . $amtdiscount . '" data-dis="' . $discountamt . '"  name="feeItemid[]" id="feeItemid" value="' . $fI['itemid'] . '" ' . $checked . '><br></div>  
            </td>
             
            <td class="p-2 sm:p-3">
               ' . $fI['feeitemname'] . '     
            </td>
             
            <td class="p-2 sm:p-3 hidden-1 sm:table-cell">
            ' . $fI['stu_invoice_no'] . '
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
            '.$paid.'
            </td>
             
            <td class="p-2 sm:p-3 hidden-1 md:table-cell">
            '.$pending.'
            </td>
            <td class="p-2 sm:p-3 hidden-1 md:table-cell">
               '.$status.'
            </td>
             
        </tr>';
    }
    echo $data;
}

if($type == 'updateApplicantData'){
    $campaignid = $_POST['val'];
    $submissionId = $_SESSION['submissionId'];
    $pupilsightProgramID = $_POST['pid'];
    $pupilsightYearGroupID = $_POST['clid'];
    $pupilsightPersonID = $_POST['pupilsightPersonID'];
    $application_id = '';

    if(!empty($campaignid)){
        $sqlrec = 'SELECT b.id, b.formatval FROM campaign AS a LEFT JOIN fn_fee_series AS b ON a.application_series_id = b.id WHERE a.id = "'.$campaignid.'" ';
        $resultrec = $connection2->query($sqlrec);
        $recptser = $resultrec->fetch();

        $seriesId = $recptser['id'];

        if(!empty($seriesId)){
            $invformat = explode('$',$recptser['formatval']);
            $iformat = '';
            $orderwise = 0;
            foreach($invformat as $inv){
                if($inv == '{AB}'){
                    // $datafort = array('fn_fee_series_id'=>$seriesId,'order_wise' => $orderwise, 'type' => 'numberwise');
                    // $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=:fn_fee_series_id AND order_wise=:order_wise AND type=:type';
                    $datafort = array('fn_fee_series_id'=>$seriesId, 'type' => 'numberwise');
                    $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type';
                    $resultfort = $connection2->prepare($sqlfort);
                    $resultfort->execute($datafort);
                    $formatvalues = $resultfort->fetch();
                    
                    $iformat .= $formatvalues['last_no'];
                    
                    $str_length = $formatvalues['no_of_digit'];

                    $lastnoadd = $formatvalues['last_no'] + 1;

                    $lastno = substr("0000000{$lastnoadd}", -$str_length); 

                    $datafort1 = array('fn_fee_series_id'=>$seriesId, 'type' => 'numberwise' , 'last_no' => $lastno);
                    $sqlfort1 = 'UPDATE fn_fee_series_number_format SET last_no=:last_no WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type ';
                    $resultfort1 = $connection2->prepare($sqlfort1);
                    $resultfort1->execute($datafort1);

                } else {
                    $iformat .= $inv;
                }
                $orderwise++;
            }
            $application_id = $iformat;
        } else {
            $application_id = '';
        }
    }
    
    $data = array('pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightPersonID' => $pupilsightPersonID, 'application_id' => $application_id, 'id' => $submissionId);
    $sql = 'UPDATE wp_fluentform_submissions SET pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightPersonID=:pupilsightPersonID, application_id=:application_id WHERE id=:id';
    $result = $connection2->prepare($sql);
    $result->execute($data);
}


if ($type == 'getfeeitems') {
    $pupilsightSchoolYearID = $val;

    $sqli = 'SELECT id, name FROM fn_fee_items  WHERE pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '"';
    $resulti = $connection2->query($sqli);
    $feeItem = $resulti->fetchAll();
    $returndata = '<option value="">Select Fee Item</option>';
    foreach ($feeItem as $row) {
        $returndata .= '<option value=' . $row['id'] . '>' . $row['name'] . '</option>';
    }

    echo $returndata;
}

if ($type == 'getAllStaff') {
    $name = $val;
    $id = $_POST['id'];
    $sql = 'SELECT pupilsightPersonID, officialName FROM pupilsightPerson  WHERE officialName LIKE "%'.$name.'%"';
    $result = $connection2->query($sql);
    $staffData = $result->fetchAll();
    $returndata = '<ul>';
    foreach ($staffData as $row) {
        $returndata .= '<li data-mid='.$id.' data-id='.$row['pupilsightPersonID'].' data-name='.$row['officialName'].' class="clickGetStaff">' . $row['officialName'] . '</li>';
    }
    $returndata .= '</ul>';

    echo $returndata;
}

if($type=='feeItemSession'){ 
    $session->forget(['fee_structure']);
   $session->set('fee_structure', $val);

}
if ($type == 'invoiceFeeItem_parent') {
    $session->forget(['invoiceID_parent']);
    $session->set('invoiceID_parent', $val);
    
}

if($type == 'saveApplicantForm'){
    $submissionId = $_SESSION['submissionId'];
    $data = base64_decode($_POST['pdf']);
    // print_r($data);
    file_put_contents( "public/applicationpdf/".$submissionId."-application.pdf", $data );
}

if($type=="getPaymentHistory"){
    $stuId=$_POST['val'];
    $pupilsightSchoolYearID=$_POST['py'];
   $paymenthistory = 'SELECT  f.*,m.name as payMode
        FROM fn_fees_collection as f 
        LEFT JOIN fn_masters as m
        ON f.payment_mode_id = m.id
        WHERE f.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" AND f.pupilsightPersonID = "'.$stuId.'"  ORDER BY f.id DESC';
        $resultPhis = $connection2->query($paymenthistory);
        $payhistory = $resultPhis->fetchAll();
        // echo "<pre>";
        // print_r($payhistory);die();
        if(!empty($payhistory)){
             foreach($payhistory as $ph){
                $sqli="SELECT invoice_no FROM fn_fees_student_collection where transaction_id = '".$ph['transaction_id']."' LIMIT 0,1";
                $resulti = $connection2->query($sqli);
                $invdata = $resulti->fetch();
                $invnno = $invdata['invoice_no'];

                $m_txt='';
                $mode=strtoupper($ph['payMode']);
                if($mode=="MULTIPLE"){
                
                    $sql="SELECT f.name FROM fn_multi_payment_mode AS m LEFT JOIN fn_masters as f ON m.payment_mode_id = f.id
                    where m.transaction_id = '".$ph['transaction_id']."'";
                    $re_m = $connection2->query($sql);
                    $pm= $re_m->fetchAll();
                    if(!empty($pm)){
                        $i=1;
                        foreach($pm as $m ){
                            $m_txt.=$m['name'].",";
                        }
                    } 
                }else {
                    $m_txt=$ph['payMode'];
                }

                if($ph['transaction_status'] == '2'){
                    $sqlc="SELECT a.*, b.officialName FROM fn_fees_cancel_collection AS a LEFT JOIN pupilsightPerson AS b ON a.canceled_by = b.pupilsightPersonID where a.fn_fees_collection_id = '".$ph['id']."' ";
                    $resultc = $connection2->query($sqlc);
                    $candata = $resultc->fetch();
                    $canceledBy = $candata['officialName'];
                    $canceledDate = $candata['cdt'];
                    $canceledRemark = $candata['remarks'];
                    $cantitle = 'Canceled By : '.$canceledBy.' , Date : '.$canceledDate; 

                    $paystatus = '<td title="'.$cantitle.'">Cancelled</td>';
                } else if($ph['transaction_status'] == '3'){
                    $paystatus = '<td>Refunded</td>';
                } else {
                    $paystatus = '<td>'.$ph['payment_status'].'</td>';
                }
                echo '<tr><td><input type="checkbox" name="paymentHistory[]" id="paymentHistory" value="'.$ph['id'].'" class="selPayHistory payhistory'.$ph['transaction_id'].'"></td>
                <td><a title="View receipt" href="public/receipts/'.$ph['transaction_id'].'.docx"  download><i class="fas fa-receipt"></i></a></td>
                <td>                
                <a href="index.php?q=/modules/Finance/fee_payment_history.php&tid='.$ph['transaction_id'].'" target="_blank">'.$ph['transaction_id'].'</a></td><td>'.$ph['receipt_number'].'</td><td>'.$invnno.'</td><td>'.$ph['total_amount_without_fine_discount'].'</td><td>'.$ph['fine'].'</td><td>'.$ph['discount'].'</td><td>'.$ph['amount_paying'].'</td><td>'.date("d/m/Y", strtotime($ph['payment_date'])).'</td><td>'.$ph['payMode'].'</td>'.$paystatus.'</tr>';
            }
        } else {
            echo "<tr><td colspan='7'>No payment history found</td></tr>";
        }
}

if ($type == 'getAjaxMultiPlayment') {
    $aid = $val;
    $disid = $aid;
    $sqldr = 'SELECT * FROM fn_masters ';
    $resultdr = $connection2->query($sqldr);
    $master = $resultdr->fetchAll();
    $paymentmode = array();
    $paymentmode1 = array(''=>'Select Payment Mode');
    $paymentmode2 = array();
    $bank = array();
    $bank1 = array(''=>'Select Bank');
    $bank2 = array();
    foreach ($master as $dr) {
        if($dr['type'] == 'payment_mode'){
            if($dr['name'] != 'Multiple'){
                $paymentmode2[$dr['id']] = $dr['name'];
             }
           
        } else {
            $bank2[$dr['id']] = $dr['name'];
        }
        
    }
    $bank = $bank1 + $bank2;
    $paymentmode = $paymentmode1 + $paymentmode2;
    $credit_card = array(''=>'Select',
    'credit_card'=>'Credit Card',
    'debit_card'=>'Debit Card');

    $data = '<tr id="seatdiv" class="seatdiv fixedfine flex flex-col sm:flex-row justify-between content-center p-0 deltr' . $aid . '"><td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group">
            <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative">
            <select id="py_mode'.$disid.'" name="payment_mode_id[]" class="w-full  txtfield allFeeItemId payment_slt_mode" data-id="'.$disid.'">';
    foreach ($paymentmode as $k => $st) {
        $data .= '<option value="' . $k . '">' . $st . '</option>';
    }
    $data .= ' </select></div></div>
        </div></td>
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group">
            <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative"><select id="feeStructureItemDisableId" name="bank_id[]" class="w-full  txtfield allFeeItemId bank_'.$disid.'">';
    foreach ($bank as $k => $st) {
        $data .= '<option value="' . $k . '">' . $st . '</option>';
    }
    $data .= ' </select>
    <input type="text" readonly placeholder="No option" class="d_bank_'.$disid.' form-control" style="display:none">
    </div></div>
        </div></td>

        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
            <div class="input-group stylish-input-group">
                <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative"><input type="text" id="amount" name="amount[]" class="w-full  txtfield numfield kountAmt amt_'.$disid.'"></div></div>
            </div></td>
            
            <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
            <div class="input-group stylish-input-group">
                <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative"><input type="text" id="reference_no" name="reference_no[]" class="w-full  txtfield  ref_'.$disid.'"></div></div>
            </div></td>
      
      
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
        <div class="input-group stylish-input-group" style="display:inline-flex;">
            <div class="dte mb-1"></div><div class=" txtfield   mb-1"><div class="flex-1 relative"><input type="date" id="instrument_date" name="instrument_date[]" class="w-full  txtfield  due_'.$disid.'"style="background-color: #f0f1f3;height: 35px;width:140px;font-size: 14px; font-size: 14px;color: #111111;border-radius: 4px !important;"></div></div><div class="dte mb-1"  style="font-size: 20px; padding:  7px 0 0px 4px; width: 15px"><i style="cursor:pointer" class="far fa-times-circle delSeattr" data-id="' . $disid . '"></i></div></div></td></tr>';
    echo $data;
}

if ($type == 'AmountSession') {
    $session->forget(['amount']);
    $session->set('amount', $val);
    
}

if ($type == 'SubdescriptiveIndicator') { 
        $r_ID = explode(',',$_POST['val']);
        $sub_id = $_POST['sub'];
        $pupilsightYearGroupID = $_POST['cls'];
        $di_mode = $_POST['dimode'];
        $r_des= $_POST['rdes'];
        $grade =  $_POST['grade'];
        $prg =  $_POST['program'];
        $grade_text=$_POST['grade_text'];
    foreach($r_des as $rdata){      
        $remarks = explode('_', $rdata);
      
        $rid = $remarks[0];
        $name = $remarks[1]; 
        $data1 = array('remark_id' => $rid ,'pupilsightDepartmentID'=>$sub_id,'pupilsightProgramID'=>$prg,'pupilsightYearGroupID'=>$pupilsightYearGroupID,);        
        $sql1 = 'DELETE FROM subject_skill_descriptive_indicator_config WHERE remark_id=:remark_id AND pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightProgramID =:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID';
        $result1 = $connection2->prepare($sql1);
        $result1->execute($data1); 
         
        $data = array('remark_id' => $rid,'remark_description'=>$name,'pupilsightDepartmentID'=>$sub_id,'pupilsightYearGroupID'=>$pupilsightYearGroupID,'di_mode'=>$di_mode,'grade_id'=>$grade,'grade'=>$grade_text,'pupilsightProgramID'=>$prg);
        
        $sql = "INSERT INTO subject_skill_descriptive_indicator_config SET remark_id    =:remark_id ,remark_description=:remark_description,pupilsightYearGroupID=:pupilsightYearGroupID,pupilsightDepartmentID=:pupilsightDepartmentID,di_mode=:di_mode,grade_id=:grade_id,grade=:grade,pupilsightProgramID=:pupilsightProgramID";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    }
}
if ($type == 'remove_config') {
    $id = $val; 
    $ids = explode(',',$val);
    foreach($ids as $st){
        $data2 = array('id' => $st);
        $sql2 = 'DELETE FROM subject_skill_descriptive_indicator_config WHERE id=:id';
        $result2 = $connection2->prepare($sql2);
        $result2->execute($data2);
    }
}
if($type=='copyDescriptive'){

$session->forget(['copyDesc']);
$session->set('copyDesc',$val);
$session->forget(['copycls']);
$session->set('copycls',$_POST['cls']);
$session->forget(['copyprg']);
$session->set('copyprg',$_POST['prg']);

}

if($type == 'saveDescriptive'){
    $dID =$_POST['descid'];
    $RemarksD = $_POST['val'];
    
    $rid = explode(',', $dID);
    $count=count($rid);
    for($i=0;$i<$count;$i++){      
            $data1 = array('remark_description' => $RemarksD[$i], 'id' => $rid[$i]);
            $sql1 = 'UPDATE  subject_skill_descriptive_indicator_config SET remark_description=:remark_description WHERE id=:id';
            $result1 = $connection2->prepare($sql1);
            $result1->execute($data1);
    }
}

if ($type == 'getSectionByClassProgForMapping') {
    $cid = $val;
    $pid = $_POST['pid'];
    $aid = $_POST['aid'];
    $sqlsec = 'SELECT GROUP_CONCAT(pupilsightRollGroupID) AS secId FROM pupilsightProgramClassSectionMapping  WHERE pupilsightSchoolYearID = "' . $aid . '" AND pupilsightProgramID = "' . $pid . '" AND pupilsightYearGroupID = "' . $cid . '" ';
    $resultsec = $connection2->query($sqlsec);
    $secdata = $resultsec->fetch();
    $sqlId = $secdata['secId'];
    if(!empty($sqlId)){
        $sqlId = $sqlId;
    } else {
        $sqlId = '0';
    }

    $sql = 'SELECT pupilsightRollGroupID, name FROM pupilsightRollGroup  WHERE pupilsightRollGroupID Not In ('.$sqlId.')  GROUP BY pupilsightRollGroupID';
    $result = $connection2->query($sql);
    $sections = $result->fetchAll();
    $data = '<option value="">Select Section</option>';
    if (!empty($sections)) {
        foreach ($sections as $k => $cl) {
            $data .= '<option value="' . $cl['pupilsightRollGroupID'] . '">' . $cl['name'] . '</option>';
        }
    }
    echo $data;
}


if($type == 'getSkillBySubject'){
    $did = $val;
    $pid = $_POST['pid'];
    $cid = $_POST['cid'];
    $sid = $_POST['sid'];
    $aid = $_SESSION[$guid]['pupilsightSchoolYearID'];
    
    $sql = 'SELECT * FROM subjectSkillMapping WHERE pupilsightSchoolYearID = "'.$aid.'" AND pupilsightProgramID = "'.$pid.'" AND pupilsightYearGroupID = "'.$cid.'" AND pupilsightDepartmentID = "'.$did.'"  ';
    $result = $connection2->query($sql);
    $skills = $result->fetchAll();
    
    $data = '<option value="">Select Skill</option>';
    if (!empty($skills)) {
        foreach ($skills as $k => $cl) {
            $data .= '<option value="' . $cl['skill_id'] . '">' . $cl['skill_display_name'] . '</option>';
        }
    }
    echo $data;
}

if ($type == 'getGradeConfigData') {
    $marks_enter = $val;
    $gradeSystemId=$_POST['gsid'];
    $sql = 'SELECT grade_name,id, subject_status FROM examinationGradeSystemConfiguration  WHERE gradeSystemId="'.$gradeSystemId.'" AND  ('.$marks_enter.' BETWEEN `lower_limit` AND `upper_limit`)';
    $result = $connection2->query($sql);
    $grade = $result->fetch();
    $return['id'] = $grade['id'];
    $return['status'] = $grade['subject_status'];
    echo json_encode($return);
    
}
if ($type == 'getGradeConfigData1') {
    $marks_enter = $val;
    $gradeSystemId=$_POST['gsid'];
    $sql = 'SELECT grade_name,id, subject_status FROM examinationGradeSystemConfiguration  WHERE gradeSystemId="'.$gradeSystemId.'" AND  ('.$marks_enter.' BETWEEN `lower_limit` AND `upper_limit`)';
    $result = $connection2->query($sql);
    $grade = $result->fetch();
  
   $sql1 = 'SELECT skill_configure  FROM `examinationSubjectToTest` WHERE `test_id` ="'.$_POST['tid'].'"  AND `pupilsightDepartmentID` = "'.$_POST['d'].'" AND skill_configure !="" GROUP BY pupilsightDepartmentID';
    $result1 = $connection2->query($sql1);
    $mode = $result1->fetch();
    $return['id'] = $grade['id'];
    $return['status'] = $grade['subject_status'];
    $return['skill_configure'] = $mode['skill_configure'];
    echo json_encode($return);
    
}

if ($type == 'getGradeConfigDataSubject') {
    $marks_enter = $val;
    $gradeSystemId=$_POST['gsid'];
    $sql = 'SELECT grade_name,id, subject_status FROM examinationGradeSystemConfiguration  WHERE gradeSystemId="'.$gradeSystemId.'" AND  ('.$marks_enter.' BETWEEN `lower_limit` AND `upper_limit`)';
    $result = $connection2->query($sql);
    $grade = $result->fetch();
  
    $return['id'] = $grade['id'];
    $return['status'] = $grade['subject_status'];
    echo json_encode($return);
    
}

// if ($type == 'getSubjectbasedonclassNew') {
//     $roleid= $_POST['roleid'];
//     $pupilsightPersonID= $_POST['pupilsightPersonID'];
//     $pupilsightProgramID= $_POST['pupilsightProgramID'];
//     if($roleid=='002')//for teacher login
//     {
//         $sq = "select DISTINCT subjectToClassCurriculum.pupilsightDepartmentID, subjectToClassCurriculum.subject_display_name from subjectToClassCurriculum  LEFT JOIN assignstaff_tosubject ON subjectToClassCurriculum.pupilsightDepartmentID = assignstaff_tosubject.pupilsightDepartmentID  LEFT JOIN pupilsightStaff ON assignstaff_tosubject.pupilsightStaffID = pupilsightStaff.pupilsightStaffID  where pupilsightProgramID = '".$pupilsightProgramID."' AND pupilsightYearGroupID ='".$val."' AND pupilsightStaff.pupilsightPersonID='".$pupilsightPersonID."' order by subject_display_name asc";
//     }
//     else
//     {
//         $sq = "select pupilsightDepartmentID, subject_display_name, di_mode from subjectToClassCurriculum where pupilsightYearGroupID ='".$val."' order by subject_display_name asc";
//     }
   
//     $result = $connection2->query($sq);
//     $rowdata = $result->fetchAll();
//     $returndata = '<option value="">Select Subject</option>';
//     foreach ($rowdata as $row) {
//         $returndata .= '<option value=' . $row['pupilsightDepartmentID'] . '  data-dimode=' . $row['di_mode'] . '>' . $row['subject_display_name'] . '</option>';
//     }
//     echo $returndata;
// }

if($type == 'Classwisesubject'){
    $sub = $val;   
    $sql = 'SELECT a.pupilsightStaffID,a.pupilsightDepartmentID ,c.officialName FROM assignstaff_tosubject AS a LEFT JOIN pupilsightStaff AS b ON a.pupilsightStaffID = b.pupilsightStaffID LEFT JOIN pupilsightPerson AS c ON b.pupilsightPersonID = c.pupilsightPersonID  WHERE a.pupilsightDepartmentID = "'.$sub.'"';
  
    $result = $connection2->query($sql);
    $staff = $result->fetchAll();

    $data = '<option value="">Select Staffs</option>';
    if(!empty($staff)){
        foreach ($staff as $k => $st) {
        $data .= '<option value="' . $st['pupilsightStaffID'] . '">' . $st['officialName'] . '</option>';
        }
    }
    echo $data;


}


if ($type == 'getSubjectbasedonclassNew') {
    $pupilsightSchoolYearID= $_SESSION[$guid]['pupilsightSchoolYearID'];
    $pupilsightPersonID= $_SESSION[$guid]['pupilsightPersonID'];
    $sqlck = 'SELECT pupilsightRoleIDPrimary FROM pupilsightPerson WHERE pupilsightPersonID = '.$pupilsightPersonID.' ';
    $resultck = $connection2->query($sqlck);
    $ckdata = $resultck->fetch();
    $roleid= $ckdata['pupilsightRoleIDPrimary'];
    
    $pupilsightProgramID= $_POST['pupilsightProgramID'];
    if($roleid=='002')//for teacher login
    {
        $sq = "select DISTINCT subjectToClassCurriculum.pupilsightDepartmentID, subjectToClassCurriculum.subject_display_name from subjectToClassCurriculum  LEFT JOIN assignstaff_tosubject ON subjectToClassCurriculum.pupilsightDepartmentID = assignstaff_tosubject.pupilsightDepartmentID  LEFT JOIN pupilsightStaff ON assignstaff_tosubject.pupilsightStaffID = pupilsightStaff.pupilsightStaffID  where subjectToClassCurriculum.pupilsightSchoolYearID = '".$pupilsightSchoolYearID."' AND subjectToClassCurriculum.pupilsightProgramID = '".$pupilsightProgramID."' AND subjectToClassCurriculum.pupilsightYearGroupID ='".$val."' AND pupilsightStaff.pupilsightPersonID='".$pupilsightPersonID."' order by subjectToClassCurriculum.subject_display_name asc";
    }
    else
    {
        $sq = "select pupilsightDepartmentID, subject_display_name, di_mode from subjectToClassCurriculum where pupilsightSchoolYearID = '".$pupilsightSchoolYearID."' AND pupilsightProgramID = '".$pupilsightProgramID."' AND pupilsightYearGroupID ='".$val."' order by subject_display_name asc";
    }
   
    $result = $connection2->query($sq);
    $rowdata = $result->fetchAll();
    $returndata = '<option value="">Select Subject</option>';
    foreach ($rowdata as $row) {
        $returndata .= '<option value=' . $row['pupilsightDepartmentID'] . '  data-dimode=' . $row['di_mode'] . '>' . $row['subject_display_name'] . '</option>';
    }
    echo $returndata;
}

if($type=='subPeriodWise'){
    $pro = $val;
    $cls = $_POST['cls'];
    $sec = $_POST['sec'];
    $sqlt = 'SELECT d.name,d.pupilsightDepartmentID FROM pupilsightTT AS a LEFT JOIN pupilsightTTDay AS b ON a.pupilsightTTID = b.pupilsightTTID LEFT JOIN pupilsightTTDayRowClass AS c ON b.pupilsightTTDayID = c.pupilsightTTDayID LEFT JOIN pupilsightDepartment as d ON c.pupilsightDepartmentID = d.pupilsightDepartmentID WHERE a.pupilsightProgramID = "'.$pro.'" AND a.pupilsightYearGroupIDList = "'.$cls.'"';
    if(!empty( $sec)){
        $sqlt.= 'AND a.pupilsightRollGroupIDList = "'.$sec.'"';
    }
    
    $resultt = $connection2->query($sqlt);  
    $subjects = $resultt->fetchAll();
   // print_r($subjects);die();
    $data = '<option value="">Select Subjects</option>';
    if(!empty($subjects)){
        foreach ($subjects as $k => $st) {
        $data .= '<option value="' . $st['pupilsightDepartmentID'] . '">' . $st['name'] . '</option>';
        }
    }
    echo $data;
}
if($type== "attendanceConfigCls"){

        $pid = $val;
        $att_type="";
        if(isset($_POST['att_type'])){
            $att_type=$_POST['att_type'];
        }
        $sql = 'SELECT a.*,GROUP_CONCAT(b.pupilsightYearGroupID SEPARATOR ",") as clid,GROUP_CONCAT(b.name SEPARATOR ", ") as name  FROM attn_settings AS a LEFT JOIN pupilsightYearGroup as b ON (FIND_IN_SET(b.pupilsightYearGroupID, a.pupilsightYearGroupID)) WHERE a.pupilsightProgramID = "' . $pid . '"  '; 
        if($att_type!=""){
         $sql.=' AND a.attn_type="'.$att_type.'"';
        }
        $sql.=' GROUP BY a.pupilsightYearGroupID   ORDER BY b.pupilsightYearGroupID';   
        $result = $connection2->query($sql);
        $classes = $result->fetchAll();
       
        $data = '<option value="">Select Class</option>';
        if (!empty($classes)) {
            foreach ($classes as  $cl) {
               
                $class = explode(',' , $cl['name']);
                $cid = explode(',' , $cl['clid']);            
                $count=count($class);
                for($i=0;$i<$count;$i++){                     
                $data .= '<option value="'. $cid[$i] .'">' . $class[$i] . '</option>';
            }
                             
            }
        }
        echo $data;
    
   
}


if ($type == 'getsessionbyclass') {
    $pid= $val; 
    $cid = $_POST['cid'];  
       $sqlp = 'SELECT a.session_no, a.session_name FROM attn_session_settings AS a LEFT JOIN attn_settings AS b ON(a.attn_settings_id =b.id) WHERE b.pupilsightProgramID='.$pid.' AND FIND_IN_SET("'.$cid.'",b.pupilsightYearGroupID) ';
       // b.pupilsightYearGroupID = '.$cid. '';
       $resultp = $connection2->query($sqlp);
      
       $rowdatasession = $resultp->fetchAll();
       $data = '<option value="">Select Session</option>';
   if (!empty($rowdatasession)) {
       foreach ($rowdatasession as $k => $cl) {
           $data .= '<option value="' . $cl['session_no'] . '">' . $cl['session_name'] . '</option>';
       }
   }   
   echo $data;
   }

   if($type=='subclassWise'){
    $pro = $val;
    $cls = $_POST['cid'];
    //$sec = $_POST['sec'];
    $sqlt = 'SELECT a.pupilsightDepartmentID, b.name FROM assign_core_subjects_toclass AS a LEFT JOIN pupilsightDepartment as b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID WHERE a.pupilsightProgramID = "'.$pro.'" AND  a.pupilsightYearGroupID = "'.$cls.'"';
    $resultt = $connection2->query($sqlt);  
    $subjects = $resultt->fetchAll();
    //print_r($subjects);die();
    $data = '<option value="">Select Subjects</option>';
    if(!empty($subjects)){
        foreach ($subjects as $k => $st) {
        $data .= '<option value="' . $st['pupilsightDepartmentID'] . '">' . $st['name'] . '</option>';
        }
    }
    echo $data;
}


if($type=='getPrograms'){
    $yr = $val;
    $sqlp ="SELECT pupilsightProgramID,name FROM pupilsightProgram";
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();    
    $data = '' ;   
    foreach ($rowdataprog as $dt) {
       
      $data.= '<tr><td><input type="checkbox" data_check="Student_Program_'. $dt['pupilsightProgramID'] .'" data_pro="Student_Program_'. $dt['name'] .'_'. $dt['pupilsightProgramID'] .'" class="selectItems Student_Program_'. $dt['pupilsightProgramID'].'" name="selectItems[]" data_id="'. $dt['name']  .'-'.$dt['pupilsightProgramID'] .'"></td><td>'. $dt['name'] .'</td></tr>';
    }
    echo $data;
}

if($type=='getClass_interaction'){
    $pid = $val;
    $sql = 'SELECT a.*, b.name,c.name as program FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID  LEFT JOIN pupilsightProgram as c ON a.pupilsightProgramID = c.pupilsightProgramID WHERE a.pupilsightProgramID = "' . $pid . '" GROUP BY a.pupilsightYearGroupID';
    $result = $connection2->query($sql);
    $classes = $result->fetchAll();   
    $data = '' ;   
    foreach ($classes as $dt) {
      $data.= '<tr><td><input type="checkbox" data_check="Student_Class_'.$pid.$dt['pupilsightYearGroupID'].'" data_pro="Student_Class_'.$dt['name'].'(' .$dt['program'].')_'.$pid.$dt['pupilsightYearGroupID'].'" class="selectItems Student_Class_'.$pid.$dt['pupilsightYearGroupID'].'" name="selectItems[]" data_id="'.$dt['name'].'-'.$pid.$dt['pupilsightYearGroupID'] .'"></td><td>'. $dt['name'].'</td></tr>';
    }
    echo $data;
}


if($type=='getSection_interaction'){
    $pid = $val;
    $cid = $_POST['cls'];
    $sql = 'SELECT a.*, b.name,c.name as class,d.name as program FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID LEFT JOIN pupilsightYearGroup AS c ON a.pupilsightYearGroupID = c.pupilsightYearGroupID  LEFT JOIN pupilsightProgram as d ON a.pupilsightProgramID = d.pupilsightProgramID WHERE a.pupilsightProgramID = "' . $pid . '" AND a.pupilsightYearGroupID = "' . $cid . '" GROUP BY a.pupilsightRollGroupID';
    $result = $connection2->query($sql);
    $sections = $result->fetchAll();   
    $data = '' ;   
    foreach ($sections as $dt) {
      $data.= '<tr><td><input type="checkbox" data_check="Student_Section_'.$dt['pupilsightProgramID'].$dt['pupilsightRollGroupID'] .'" data_pro="Student_Section_'.$dt['name'].'(' .$dt['class'].'-'.$dt['program'].')_'. $dt['pupilsightProgramID'].$dt['pupilsightRollGroupID'] .'" class="selectItems Student_Section_'.$dt['pupilsightProgramID']. $dt['pupilsightRollGroupID'].'" name="selectItems[]" data_id="'. $dt['name']  .'-'.$dt['pupilsightProgramID'].$dt['pupilsightProgramID'] .'"></td><td>'. $dt['name'] .'</td></tr>';
    }
    echo $data;
}

if($type=='getStaffDeparment'){
    $dip = $val;  
    $sql = 'SELECT pupilsightRoleID as id,name as value, name FROM pupilsightRole WHERE category="'.$dip.'" ORDER BY name';
    $result = $connection2->query($sql);
    $deparment = $result->fetchAll();   
    $data = '' ;   
    
    foreach ($deparment as $dt) {
        
      $data.= '<tr><td><input type="checkbox" data_check="Staff_Department_'.$dt['value'].'" data_pro="Staff_Deprtment_'.$dt['name'].'_'. $dt['id'] .'" class="selectItems Staff_Deprtment_'.$dt['name'].'" name="selectItems[]" data_id="'.$dt['name'].'-'.$dt['id'] .'"></td><td>'. $dt['name'] .'</td></tr>';
    }
    echo $data;
}

if($type == 'getindStaff'){
    $sname = $val;  
    $sql = 'SELECT a.pupilsightStaffID ,b.officialName FROM pupilsightStaff AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID  WHERE b.officialName LIKE "'.$sname.'%"';
    echo $sql;
    $result = $connection2->query($sql);
    $stafflist = $result->fetchAll();   
    $data = '' ;   
    foreach ($stafflist as $dt) {
      $data.= '<tr><td><input type="checkbox"  data_check="Staff_Individual_'.$dt['pupilsightStaffID'].'" data_pro="Staff_Individual_'.$dt['officialName'].'_'. $dt['pupilsightStaffID'] .'" class="selectItems Staff_Individual_'.$dt['pupilsightStaffID'].'" name="selectItems[]" data_id="'.$dt['officialName'].'-'.$dt['pupilsightStaffID'] .'"></td><td>'. $dt['officialName'] .'</td></tr>';
    }
    echo $data;
}

if($type == 'recipienst'){
    $session->forget(['recipients_dtl']);
    $session->set('recipients_dtl', $val); 
}

if($type=='getIndi_interaction'){
    $pupilsightSchoolYearID = $val;
    $pupilsightProgramID = $_POST['pid'];
    $pupilsightYearGroupID = $_POST['cls'];
    $sec = $_POST['sec'];
    $search = $_POST['name'];
    $sql = 'SELECT a.*, b.officialName as name ,c.name as program,d.name as class,e.name as section FROM  pupilsightStudentEnrolment AS a  LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID  LEFT JOIN pupilsightProgram as c ON a.pupilsightProgramID = c.pupilsightProgramID LEFT JOIN pupilsightYearGroup as d ON a.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup as e ON a.pupilsightRollGroupID = e.pupilsightRollGroupID WHERE a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" ';
    if(!empty($pupilsightProgramID)){
        $sql.=  'AND a.pupilsightProgramID = "' . $pupilsightProgramID . '"';
    }
    if(!empty($pupilsightYearGroupID)){
        $sql.=  'AND a.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '"';
    }
    if(!empty($pupilsightRollGroupID)){
        $sql.= 'AND a.pupilsightRollGroupID = "' . $sec . '"';
    }
    if(!empty($search)){
        $sql.='AND b.officialName LIKE "' . $search . '%"';
    }
    $sql.='AND pupilsightRoleIDPrimary=003 GROUP BY b.pupilsightPersonID';

    $result = $connection2->query($sql);
    $selectInd = $result->fetchAll();   
    $data = '' ;   
    foreach ($selectInd as $dt) {
      $data.= '<tr><td><input  type="checkbox" data_check="Student_Individual_'.$dt['pupilsightPersonID'].'" data_pro="Student_Individual_'. $dt['name'].'_'.$dt['pupilsightPersonID'].'" class="selectItems Student_Individual_'.$dt['pupilsightPersonID']. '" name="selectItems[]" data_id="'. $dt['name']  .'-'.$dt['pupilsightPersonID'].'"></td><td>'. $dt['name'] .'('.$dt['class'].'-'.$dt['program'].')</td></tr>';
    }
    echo $data;
}

if($type=="getSelect_interaction"){
    $ids = explode(',',$val);
    $data = '' ; 
    foreach($ids as $st){
         $clid = explode('_',$st);
        $stakeholder = $clid[0];
        $target = $clid[1]; 
        $name = $clid[2];  
        $id = $clid[3]; 
  
        $data.= '<tr data_dtl='.$stakeholder.'_'.$target.'_'.$id.'_'.$name.' data_id='.$stakeholder.'_'.$target.'_'.$id.'><td>' . $stakeholder . ' </td><td>'.$target.'</td><td>'.$name.'</td><td><i class="fa fa-times deletRow" aria-hidden="true"></i>
        </td></tr>';
    }
    echo $data; 
}    

if($type=='getSketchLabel'){
    $entity = $val;
    if($entity == 'Entity'){
        $label = "'Student','Parent','Principal','Staff','Subject Teacher','Class Teacher'";
    } else if($entity == 'Test'){
        $label = "'Marks','Max Marks','Min Marks','Grade','Grade Point','Remarks','Subject','Subject Code'";
    } else {
        $label = "'Marks','Grade','Grade Point','Fail Count','Change of Color','Percentage'";
    }
    echo $sql = "SELECT table_label FROM examinationReportTemplateConfiguration WHERE table_label IN (".$label.") GROUP BY table_label";
    $result = $connection2->query($sql);
    $labeldata = $result->fetchAll();
    $data = '<option value="">Select Type</option>';   
    foreach ($labeldata as $ldt) {
        $data .= '<option value="' . $ldt['table_label'] . '">' . $ldt['table_label'] . '</option>';
    }
    echo $data;
}


if ($type == 'getsessionConfigured') {
    $pid= $val;  
    $pupilsightYearGroupID=$_POST['pupilsightYearGroupID'];
    $sqlp = 'SELECT a.session_no, a.session_name FROM attn_session_settings AS a LEFT JOIN attn_settings AS b ON(a.attn_settings_id =b.id) WHERE b.pupilsightProgramID='.$pid.' AND  FIND_IN_SET("'.$pupilsightYearGroupID.'",b.pupilsightYearGroupID) > 0';
    $resultp = $connection2->query($sqlp);
    $rowdatasession = $resultp->fetchAll();
    $data = '<option value="">Select Session</option>';
    if (!empty($rowdatasession)) {
        foreach ($rowdatasession as $k => $cl) {
            $data .= '<option value="' . $cl['session_no'] . '">' . $cl['session_name'] . '</option>';
        }
    }
    echo $data;
}

if($type == 'getTestBySubject'){
    $pupilsightDepartmentID = $val;
    $pupilsightYearGroupID = $_POST['cid'];
    $pupilsightRollGroupID = $_POST['sid'];
    $pupilsightProgramID = $_POST['pid'];
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

    $sql = 'SELECT b.id, b.name FROM examinationTestAssignClass AS a LEFT JOIN examinationTest AS b ON a.test_id = b.id LEFT JOIN examinationSubjectToTest AS c ON a.test_id = c.test_id  WHERE a.pupilsightSchoolYearID= '.$pupilsightSchoolYearID.' AND a.pupilsightProgramID = '.$pupilsightProgramID.' AND a.pupilsightYearGroupID = '.$pupilsightYearGroupID.' AND a.pupilsightRollGroupID = '.$pupilsightRollGroupID.' AND c.pupilsightDepartmentID = '.$pupilsightDepartmentID.' GROUP BY a.test_id ';
    $result = $connection2->query($sql);
    $tests = $result->fetchAll();
    $returndata = '<option value="">Select Test</option>';
    foreach ($tests as $row) {
        $returndata .= '<option value=' . $row['id'] . ' >' . $row['name'] . '</option>';
    }
    echo $returndata;
}

if($type == 'deleteSketchAttribute'){
    $ids = explode(',',$val);
    foreach($ids as $st){
        $data2 = array('id' => $st);
        $sql2 = 'DELETE FROM examinationReportTemplateAttributes WHERE id=:id';
        $result2 = $connection2->prepare($sql2);
        $result2->execute($data2);
    }
}

if($type == 'getStudentSketchData'){
    $sketch_id = $val;
    $pupilsightPersonID = $_POST['pid'];
   
    $sql = 'SELECT * FROM examinationReportTemplateSketchData WHERE sketch_id = '.$sketch_id.' AND pupilsightPersonID = '.$pupilsightPersonID.'';
  
    $result = $connection2->query($sql);
    $studentData = $result->fetchAll();

    $data = '';
    if(!empty($studentData)){
        foreach ($studentData as $k => $st) {
        $data .= '<tr>
                        <td style="width:15%">'.$st['attribute_name'].'</td>
                        <td style="width:15%">${'.$st['attribute_name'].'}</td>
                        <td style="width:15%">'.$st['attribute_value'].'</td>
                    </tr>';
        }
    }
    echo $data;
}

if($type == 'transStatusChangeNew'){
    $session->forget(['collection_id']);
    $session->set('collection_id', $val);
}

if ($type == 'getMultiSectionData') {
    $cid = $val;
    $pid = $_POST['pid'];
    $data = '<option value="">Select Section</option>';
    foreach($cid as $sid){
        $sql = 'SELECT a.*, b.name ,c.name AS cname,c.pupilsightYearGroupID as cid FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID   LEFT JOIN pupilsightYearGroup as c ON a.pupilsightYearGroupID = c.pupilsightYearGroupID  WHERE a.pupilsightProgramID = "' . $pid . '" AND a.pupilsightYearGroupID = "' . $sid . '" GROUP BY a.pupilsightRollGroupID';

       
        $result = $connection2->query($sql);
        $sections = $result->fetchAll();
       
        
        if (!empty($sections)) {
            foreach ($sections as $k => $cl) {
                $data .= '<option value="' . $cl['pupilsightRollGroupID'] . '">' . $cl['name'] . '</option>';
            }
        }
    }
   
    echo $data;
}

if ($type == 'getMultiSection') {
    $cid = implode(',',$val);
    $pid = $_POST['pid'];
    $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE a.pupilsightProgramID = "' . $pid . '" AND a.pupilsightYearGroupID IN (' . $cid . ') GROUP BY a.pupilsightRollGroupID';
    $result = $connection2->query($sql);
    $sections = $result->fetchAll();
    $data = '<option value="">Select Section</option>';
    if (!empty($sections)) {
        foreach ($sections as $k => $cl) {
            $data .= '<option value="' . $cl['pupilsightRollGroupID'] . '">' . $cl['name'] . '</option>';
        }
    }
    echo $data;
}

if ($type == 'getAllSchoolStaff') {
    $sql = 'SELECT a.type, b.pupilsightPersonID, b.officialName FROM pupilsightStaff AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE b.officialName != "" ';
    $result = $connection2->query($sql);
    $staffs = $result->fetchAll();
    $data = '<option value="">Select Staff</option>';
    if (!empty($staffs)) {
        foreach ($staffs as $k => $cl) {
            $data .= '<option value="' . $cl['pupilsightPersonID'] . '">' . $cl['officialName'].' ('.$cl['type']. ') </option>';
        }
    }
    echo $data;
}

if ($type == 'getFeeStructure') {
    $cid = implode(',',$val);
    $pid = $_POST['pid'];
    $aid = $_POST['aid'];
    $sql = 'SELECT a.* FROM fn_fee_structure AS a LEFT JOIN fn_fees_class_assign AS b ON a.id = b.fn_fee_structure_id WHERE a.pupilsightSchoolYearID = "'.$aid.'" AND b.pupilsightProgramID = "' . $pid . '" AND B.pupilsightYearGroupID IN ('.$cid.') GROUP BY a.id';
    $result = $connection2->query($sql);
    $structure = $result->fetchAll();
    $data = '<option value="">Select Fee Structure</option>';
    if (!empty($structure)) {
        foreach ($structure as $k => $cl) {
            $data .= '<option value="' . $cl['id'] . '">' . $cl['name'] . '</option>';
        }
    }
    echo $data;
}

if ($type == 'delWorkFlowState') {
    $id = $val; 
    $data2 = array('id' => $id);
    $sql2 = 'DELETE FROM workflow_state WHERE id=:id';
    $result2 = $connection2->prepare($sql2);
    $result2->execute($data2);
}

if($type == 'convertApplicantData'){
    $campaignid = $_POST['val'];
    $submissionId = $_SESSION['submissionId'];
    $pupilsightProgramID = $_POST['pid'];
    $pupilsightYearGroupID = $_POST['clid'];
    $pupilsightPersonID = $_POST['pupilsightPersonID'];
    $application_id = $_POST['aid'];
    $oldsid = $_POST['oldsid'];

    $chksub = 'SELECT is_converted FROM wp_fluentform_submissions WHERE id = '.$oldsid.' ';
    $resultsub = $connection2->query($chksub);
    $chkConvertion = $resultsub->fetch();

    if($chkConvertion['is_converted'] == '0'){
    
        $data = array('pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightPersonID' => $pupilsightPersonID, 'application_id' => $application_id, 'id' => $submissionId);
        $sql = 'UPDATE wp_fluentform_submissions SET pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightPersonID=:pupilsightPersonID, application_id=:application_id WHERE id=:id';
        $result = $connection2->prepare($sql);
        $result->execute($data);

        $data = array('is_converted' => '1', 'id' => $oldsid);
        $sql = 'UPDATE wp_fluentform_submissions SET is_converted=:is_converted WHERE id=:id';
        $result = $connection2->prepare($sql);
        $result->execute($data);

        $invsql = 'SELECT * FROM fn_fee_invoice_applicant_assign WHERE submission_id = '.$oldsid.' ';
        $resultinv = $connection2->query($invsql);
        $invoiceData = $resultinv->fetchAll();
        if(!empty($invoiceData)){
            foreach($invoiceData as $inv){
                $datainv = array('submission_id' => $submissionId, 'fn_fee_invoice_id' => $inv['fn_fee_invoice_id'], 'invoice_no' => $inv['invoice_no']);
                $sqlinv = 'INSERT INTO fn_fee_invoice_applicant_assign SET submission_id=:submission_id, fn_fee_invoice_id=:fn_fee_invoice_id, invoice_no=:invoice_no';
                $resulti = $connection2->prepare($sqlinv);
                $resulti->execute($datainv);
            }
        }

        $chksql = 'SELECT * FROM fn_fee_collection WHERE submission_id = '.$oldsid.' ';
        $resultcol = $connection2->query($chksql);
        $collectionData = $resultcol->fetchAll();
        if(!empty($collectionData)){
            foreach($collectionData as $col){
                $datacol = array('transaction_id'=> $col['transaction_id'], 'fn_fees_invoice_id' => $col['fn_fees_invoice_id'], 'submission_id' => $submissionId, 'pupilsightSchoolYearID' => $col['pupilsightSchoolYearID'], 'fn_fees_counter_id' =>$col['fn_fees_counter_id'], 'receipt_number' => $col['receipt_number'], 'is_custom' => $col['is_custom'], 'payment_mode_id' => $col['payment_mode_id'], 'bank_id' => $col['bank_id'], 'dd_cheque_no' => $col['dd_cheque_no'], 'dd_cheque_date' => $col['dd_cheque_date'], 'dd_cheque_amount' => $col['dd_cheque_amount'], 'payment_status' => $col['payment_status'], 'payment_date' => $col['payment_date'], 'fn_fees_head_id' => $col['fn_fees_head_id'], 'fn_fees_receipt_series_id' => $col['fn_fees_receipt_series_id'], 'transcation_amount' => $col['transcation_amount'], 'total_amount_without_fine_discount' => $col['total_amount_without_fine_discount'], 'amount_paying' => $col['amount_paying'], 'fine' => $col['fine'], 'discount' =>$col['discount'], 'remarks' => $col['remarks'], 'status' => $col['status'], 'cdt' => $col['cdt']);
                $sqlcol = 'INSERT INTO fn_fees_collection SET transaction_id=:transaction_id, fn_fees_invoice_id=:fn_fees_invoice_id, submission_id=:submission_id, pupilsightSchoolYearID =:pupilsightSchoolYearID, fn_fees_counter_id=:fn_fees_counter_id, receipt_number=:receipt_number, is_custom=:is_custom, payment_mode_id=:payment_mode_id, bank_id=:bank_id, dd_cheque_no=:dd_cheque_no, dd_cheque_date=:dd_cheque_date, dd_cheque_amount=:dd_cheque_amount, payment_status=:payment_status, payment_date=:payment_date, fn_fees_head_id=:fn_fees_head_id, fn_fees_receipt_series_id=:fn_fees_receipt_series_id, transcation_amount=:transcation_amount, total_amount_without_fine_discount=:total_amount_without_fine_discount, amount_paying=:amount_paying, fine=:fine, discount=:discount, remarks=:remarks, status=:status,cdt=:cdt';
                $resultc = $connection2->prepare($sqlcol);
                $resultc->execute($datacol);
            }
        }

        $acsql = 'SELECT * FROM fn_fees_applicant_collection WHERE submission_id = '.$oldsid.' ';
        $resultac = $connection2->query($acsql);
        $appCollectionData = $resultac->fetchAll();
        if(!empty($appCollectionData)){
            foreach($appCollectionData as $acol){
                $datacola = array('submission_id' => $submissionId,  'transaction_id' => $inv['transaction_id'], 'fn_fee_invoice_id' => $inv['fn_fee_invoice_id'], 'fn_fee_invoice_item_id' => $inv['fn_fee_invoice_item_id'], 'invoice_no' => $inv['invoice_no']);
                $sqlcola = 'INSERT INTO fn_fees_applicant_collection SET submission_id=:submission_id, transaction_id=:transaction_id, fn_fee_invoice_id=:fn_fee_invoice_id, fn_fee_invoice_item_id=:fn_fee_invoice_item_id, invoice_no=:invoice_no';
                $resultca = $connection2->prepare($sqlcola);
                $resultca->execute($datacola);
            }
        }
    }
}


if ($type == 'checkApplicantConversion') {
    $sid = $val;
    $chksub = 'SELECT is_converted FROM wp_fluentform_submissions WHERE id = '.$sid.' ';
    $resultsub = $connection2->query($chksub);
    $chkConvertion = $resultsub->fetch();
    if($chkConvertion['is_converted'] == '0'){
        $response = '1';
    } else {
        $response = '2';
    }
    echo $response;
}
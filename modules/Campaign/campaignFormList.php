<?php


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
//echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>';

use Pupilsight\Services\Format;

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTableResult;
use Pupilsight\Domain\Admission\AdmissionGateway;
use Pupilsight\Domain\DataSet;


include $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// Module includes
require_once __DIR__ . '/moduleFunctions.php';


if (isActionAccessible($guid, $connection2, '/modules/Campaign/campaignFormList.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {

    function getDomain()
    {
        if (isset($_SERVER['HTTPS'])) {
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        } else {
            $protocol = 'http';
        }
        //return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return $protocol . "://" . $_SERVER['HTTP_HOST'];
    }
    $baseurl = getDomain();

    $page->breadcrumbs->add(__('Campaign Submitted Form List'));


    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }


    $id = "";
    $formId = '';
    if (isset($_REQUEST['id']) ? $id = $_REQUEST['id'] : $id = "");

    $sql1 = 'Select form_id, name, classes, pupilsightProgramID, is_fee_generate FROM campaign WHERE id = ' . $id . ' ';
    $resultval1 = $connection2->query($sql1);
    $formid = $resultval1->fetch();
    //  echo $formid['form_id'];
    //  die();
    $formId = $formid['form_id'];
    $isFeeGenerate = $formid['is_fee_generate'];

    $search = isset($_GET['search']) ? $_GET['search'] : '';

    $sqlp = 'SELECT pupilsightProgramID, name FROM  pupilsightProgram  WHERE pupilsightProgramID IN (' . $formid['pupilsightProgramID'] . ') ';
    $resultp = $connection2->query($sqlp);
    $progData = $resultp->fetchAll();


    $programs = '<select class="" id="applicationProg" ><option value="">Select Program</option>';
    foreach ($progData as $pg) {
        $programs .= '<option value="' . $pg['pupilsightProgramID'] . '" >' . $pg['name'] . '</option>';
    }
    $programs .= '</select>';

    $sql = 'SELECT pupilsightYearGroupID, name FROM  pupilsightYearGroup  WHERE pupilsightYearGroupID IN (' . $formid['classes'] . ') ';
    $result = $connection2->query($sql);
    $classes = $result->fetchAll();


    $class = '<select class="" id="applicationClass" ><option value="">Select Class</option>';
    foreach ($classes as $st) {
        $class .= '<option value="' . $st['pupilsightYearGroupID'] . '" >' . $st['name'] . '</option>';
    }
    $class .= '</select>';

    $admissionGateway = $container->get(AdmissionGateway::class);
    $criteria = $admissionGateway->newQueryCriteria()
        ->searchBy($admissionGateway->getSearchableColumns(), $search)
        ->sortBy(['id'])
        ->fromPOST();
    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'] . '/index.php', 'get');
    $form->setClass('noIntBorder fullWidth');
    $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/index.php');

    echo $form->getOutput();

    // QUERY
    echo '<h2>';
    echo __('Online Campaign Submitted Form List  ');
    echo '(' . $formid['name'] . ')';
    echo '</h2>';

    echo '<input type="hidden" id="cmpId" value="' . $id . '">';

    echo '<div style="margin-bottom:5px;margin-top: -5px;"><label class="switch"><input type="checkbox" id="togBtn" class="changeForm" checked><div class="slider round"><span class="on" style="margin: 0 0 0 -12px;">Online</span><span class="off" style="margin: 0 0 0 12px;">Offline</span></div></label>';

    echo "<a style='display:none;' href='' class='thickbox' id='clickStateRemark'>Remark</a>";

    // echo $butt = '<i id="btnExport" title="Export PDF" class="far fa-file-pdf download_icon"></i> ';
    //echo $butt = '<i id="expore_csv" title="Export CSV" class="fas fa-file-csv download_icon"></i> ';
    echo "<div style='height:25px; margin-top:5px;width: 50%;float: right;'><div class='float-right mb-2'>
    <a style='display:none; ' href='" . $_SESSION[$guid]['absoluteURL'] . "/fullscreen.php?q=/modules/Campaign/fee_make_payment.php&cid=" . $id . "' class='thickbox btn btn-primary' id='clickAdmissionFeePayment'>Fee Payment</a>
    <a style='display:none; margin-bottom:10px;'  class='btn btn-primary' id='admissionFeePayment'>Fee Payment</a>
    &nbsp;&nbsp;<a id='offlineClick' style='display:none; margin-bottom:10px;' href='?q=/modules/Campaign/offline_campaignFormList.php&id=" . $id . "'   class=' btn btn-primary' >Offline Submitted List</a>
    &nbsp;&nbsp;<a style='display:none; margin-bottom:10px;' href='?q=/modules/Campaign/formopen.php&id=" . $id . "'   class='btn btn-white' id='' >Apply</a>  &nbsp;&nbsp;<a style=' margin-bottom:10px;' href=''  data-toggle='modal' data-target='#large-modal-campaign_list' data-noti='2'  class='sendButton_campaign_list btn btn-white' data-cid=" . $id . " data-fid=" . $formId . " id='sendSMS'>Send SMS</a>";
    echo "&nbsp;&nbsp;<a style=' margin-bottom:10px;' href='' data-toggle='modal' data-noti='1' data-target='#large-modal-campaign_list' class='sendButton_campaign_list btn btn-white' data-cid=" . $id . "  data-fid=" . $formId . " id='sendEmail'>Send Email</a>";
    //echo $butt = '<i id="expore_xl_campaign" title="Export Excel" class="mdi mdi-file-excel mdi-24px download_icon"></i><i id="pdf_export" title="Export PDF" class="mdi mdi-file-pdf mdi-24px download_icon"></i><a id="downloadLink" data-hrf="index.php?q=/modules/Campaign/ajaxfile.php&cid='.$id.'&id=" href="index.php?q=/modules/Campaign/ajaxfile.php" class="" style="display:none;">Download Receipts</a><i id="showHistory" title="Show History" class="mdi mdi-eye-outline mdi-24px download_icon"></i><i  id="viewForm" title="View Form" class="mdi mdi-clipboard-list-outline  mdi-24px download_icon"></i></div></div> <br>';
    echo $butt = '<i id="expore_xl_campaign" title="Export Excel" class="mdi mdi-file-excel mdi-24px download_icon"></i><i id="pdf_export" title="Export PDF" class="mdi mdi-file-pdf mdi-24px download_icon"></i><a id="downloadLink" data-hrf="thirdparty/pdfgenerate/admission_pdflib.php?cid=' . $id . '&submissionId=" href="index.php?q=/modules/Campaign/ajaxfile.php" class="" style="display:none;">Download Receipts</a><i id="showHistory" title="Show History" class="mdi mdi-eye-outline mdi-24px download_icon"></i><i  id="viewForm" title="View Form" class="mdi mdi-clipboard-list-outline  mdi-24px download_icon"></i></div></div></div> ';

    //  print_r($criteria);
    //  die();
    // echo $butt = '<i id="btnExport" title="Export PDF" class="far fa-file-pdf download_icon"></i> ';
    // echo $butt = '<i id="expore_csv" title="Export CSV" class="fas fa-file-csv download_icon"></i> ';
    // echo $butt = '<i id="expore_xl" title="Export Excel" class="far fa-file-excel download_icon"></i> <br>';

    $dataSet = $admissionGateway->getCampaignFormList($criteria, $formId);
    /*
    $subsql = "CALL campaignFormList(" . $formId . ");";

    $connPro1 = new mysqli($databaseServer, $databaseUsername, $databasePassword, $databaseName);
    $testdatasub = mysqli_query($connPro1, $subsql);
    while ($row = $testdatasub->fetch_assoc()) {
        $row["serial_number"] = $cnt;
        $dt[] = $row;
    }
    $dataSet = new DataSet($dt);
    */
    //$table = DataTable::createPaginated('userManage', $criteria)->setID('expore_tbls');
    $table = DataTableResult::createPaginated('userManage', $criteria)->setID('expore_tbl');

    $sqlw = "Select workflow_id  FROM workflow_map WHERE campaign_id = ' . $id . ' ";
    $resultvalw = $connection2->query($sqlw);
    $woid = $resultvalw->fetch();
    $wid = $woid['workflow_id'];

    $sqlct = "Select count(id) AS kount  FROM wp_fluentform_submissions WHERE form_id = ' . $formId . ' ";
    $resultct = $connection2->query($sqlct);
    $kountSubmission = $resultct->fetch();

    // $sql = 'Select b.name as states, b.id as sid, b.notification FROM workflow_map as a LEFT JOIN workflow_state as b ON a.workflow_id = b.workflowid WHERE a.campaign_id = '.$id.' ';
    $sql = 'Select a.*, b.notification FROM workflow_transition as a LEFT JOIN workflow_state as b ON a.to_state = b.id WHERE a.campaign_id = ' . $id . ' ';
    $resultval = $connection2->query($sql);
    $stats = $resultval->fetchAll();

    $statefields = '<select class="" id="applicationStatus" ><option value="">Select Status</option><option value="Submitted">Submitted</option>';
    foreach ($stats as $st) {
        $statefields .= '<option value="' . $st['id'] . '" >' . $st['transition_display_name'] . '</option>';
    }
    $statefields .= '</select>';
    // echo '<pre>';
    //  print_r($dataSet);
    // echo '</pre>';
    // die();
    //  foreach($stats as $s){
    //     echo $butt = '<button class="btn btn-primary statesButton" data-toggle="modal" data-target="#large-modal" data-formid = '.$formId.' data-name="'.$s['transition_display_name'].'" data-sid='.$s['id'].' data-cid='.$id.' data-noti='.$s['notification'].' style="margin:5px" >'.ucwords($s['transition_display_name']).'</button>';

    //  }
    //  foreach($stats as $s){
    //     echo $butt = '<button class="btn btn-primary statesButton"  data-formid = '.$formId.' data-name="'.$s['transition_display_name'].'" data-sid='.$s['id'].' data-cid='.$id.' data-noti='.$s['notification'].' style="margin:5px" >'.ucwords($s['transition_display_name']).'</button>';
    //  }
    echo '<input type="hidden" id="campId" value="' . $id . '"><input type="hidden" id="formId" value="' . $formId . '">';
    echo '<span id="statusButton"></span>';

    if (!empty($formId)) {
        $sqlf = 'Select form_fields FROM wp_fluentform_forms WHERE id = ' . $formId . ' ';
        $resultvalf = $connection2->query($sqlf);
        $fluent = $resultvalf->fetch();
        $field = json_decode($fluent['form_fields']);
        $fields = array();

        $showfields = '<div style="display:inline-flex;"><select class="filterCampaign" id="showfield1" style="width:50%"><option>Select Field for Filter</option>';
        foreach ($field as $fe) {
            foreach ($fe as $f) {
                // if(!empty($f->fields)){

                //     foreach($f->fields as $fs){
                //         if(!empty($fs->attributes)){
                //             $lbl = $fs->attributes->placeholder;
                //             if(empty($lbl)){
                //                 $lbl = $fs->attributes->name;
                //             }
                //             $lbl = str_replace("_"," ",$lbl);
                //             if($lbl){
                //                 $fields[] = $fs->attributes->name;
                //                 $showfields .= '<option value="'.$fs->attributes->name.'" >'.ucwords($lbl).'</option>';
                //             }
                //         }
                //     }

                // } else {
                if (!empty($f->attributes->name)) {
                    if (strpos($f->attributes->name, 'terms-n-condition') !== false) {
                    } else {
                        $fields[] = $f->attributes->name;
                        /*
                        $lbl = $f->attributes->placeholder;
                        if (empty($lbl)) {
                            $lbl = $f->attributes->name;
                        }*/
                        $lbl = $f->attributes->name;
                        $lbl = str_replace("_", " ", $lbl);
                        $showfields .= '<option value="' . $f->attributes->name . '" >' . ucwords($lbl) . '</option>';
                    }
                }
                // }

            }
        }
        $showfields .= '</select>';
        echo $showfields;

        echo $showfields2 = '<select id="showfield2" class="filterCampaign" style="display:none;height: 36px;"><option>Select Search Criteria</option><option value="search">Search</option><option value="range">Range</option></select><input type="text" class="filterCampaign searchOpen searchby" name="searchby" id="" placeholder="Enter Your Search Data" style="display:none;height: 36px;"><input type="text" id="range1" name="rangestart" class="rangeOpen filterCampaign searchby" placeholder="Enter Your Start Range" style="display:none;height: 36px;"><input type="text" name="rangeend" class="rangeOpen filterCampaign searchby" id="range2" placeholder="Enter Your End Range" style="display:none;height: 36px;"><input type="hidden" id="campaignId" value=' . $id . '><input type="hidden" id="formId" value=' . $formId . '>
        <input type="text" id="applicationName" style="height: 36px;" name="applicationName" class=""  placeholder="Application Name" >&nbsp;
        <input type="text" id="applicationId" style="height: 36px;" name="application_id" class=""  placeholder="Application No" >&nbsp;
        ' . $statefields . ' &nbsp;
        ' . $programs . ' &nbsp;
        ' . $class . ' &nbsp;
        <button id="filterCampaign" style="height: 36px;" class="btn btn-primary">Search</button>
         
        </div>
        <br/>
        </span>';
        //<span id="totalCount">&nbsp;Total Count : <span id="kountApplicant">' . $kountSubmission['kount'] . '</span>
    }

    // $sqlw = 'Select * FROM wp_fluentform_entry_details WHERE form_id = '.$formId.' ';
    // $resultvalw = $connection2->query($sqlw);
    // $formdets = $resultvalw->fetchAll();

    echo "<input type='hidden' name='form_id' data-cid='" . $id . "' id='form_id' value = '" . $formId . "'> ";


    //    array_reverse($dataSet->data);



    $arrHeader = array();
    if (isset($field)) {
        foreach ($field as $fe) {
            foreach ($fe as $f) {
                if (isset($f->attributes->name)) {
                    if (!empty($f->attributes) && $f->attributes->name == 'student_name') {
                        $arrHeader[] = $f->attributes->name;
                    }
                }
                if (!empty($f->settings) && !empty($f->settings->container_class)) {
                    if ($f->settings->container_class == 'show-in-grid') {
                        $arrHeader[] = $f->attributes->name;
                    }
                }
                if (!empty($f->columns)) {
                    foreach ($f->columns as $cf) {
                        foreach ($cf as $cff) {
                            foreach ($cff as $ctf) {
                                if (!empty($ctf->attributes) && $ctf->attributes->name == 'student_name') {
                                    $arrHeader[] = $ctf->attributes->name;
                                }
                                if (!empty($ctf->settings) && !empty($ctf->settings->container_class)) {
                                    if ($ctf->settings->container_class == 'show-in-grid') {
                                        $arrHeader[] = $ctf->attributes->name;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }


        // echo '<pre>';
        // print_r($arrHeader);
        // echo '</pre>';


        if (!empty($formId)) {
            $table->addCheckboxColumn('submission_id', __(''))
                ->addClass('chkbox')
                ->context('Select')
                ->notSortable()
                ->width('10%');

            $table->addColumn('application_id', __('Application No'))
                ->width('10%');

            $table->addColumn('submission_no', __('Submission No'))
                ->width('10%');



            $len = count($dataSet->data);
            $i = 0;
            $flag = TRUE;
            while ($i < $len) {
                $sid = $dataSet->data[$i]["submission_id"];
                $sqlchk = 'Select ws.created_at, ws.pupilsightProgramID, ws.pupilsightYearGroupID FROM wp_fluentform_submissions AS ws WHERE ws.id = ' . $sid . ' AND  ws.form_id = ' . $formId . '';
                $resultchk = $connection2->query($sqlchk);
                $submited_formchk = $resultchk->fetch();

                if (!empty($submited_formchk['pupilsightProgramID']) && !empty($submited_formchk['pupilsightYearGroupID'])) {
                    $sqlprog = 'Select name FROM pupilsightProgram WHERE pupilsightProgramID = ' . $submited_formchk['pupilsightProgramID'] . '  ';
                    $resultprog = $connection2->query($sqlprog);
                    $prog = $resultprog->fetch();
                    $progname = $prog['name'];

                    $sqlcls = 'Select name FROM pupilsightYearGroup WHERE pupilsightYearGroupID = ' . $submited_formchk['pupilsightYearGroupID'] . ' ';
                    $resultcls = $connection2->query($sqlcls);
                    $cls = $resultcls->fetch();
                    $clsname = $cls['name'];

                    $sqlname = 'Select GROUP_CONCAT(field_value) AS name FROM wp_fluentform_entry_details WHERE field_name = "student_name" AND submission_id = ' . $sid . ' ';
                    $resultname = $connection2->query($sqlname);
                    $aname = $resultname->fetch();
                    $usrname = str_replace(',', ' ', $aname['name']);

                    $pdfvalue = $progname . '-' . $clsname . '-' . $usrname;
                } else {
                    $pdfvalue = $dataSet->data[$i]["submission_id"];
                }
                //$value = 
                echo '<input type="hidden" id="' . $sid . '-subId" value="' . $pdfvalue . '" >';

                $field = explode(",", $dataSet->data[$i]["field_name"]);
                $fieldval = explode("|$$|", $dataSet->data[$i]["field_value"]);

                $jlen = count($field);
                $j = 0;
                if ($dataSet->data[$i]["status"] == '1') {
                    $dataSet->data[$i]["workflowstate"] = 'Admitted';
                } else if ($dataSet->data[$i]["workflowstate"] == '') {
                    // $sqls = 'Select name FROM workflow_state WHERE workflowid = '.$wid.' AND order_wise = "1" ';
                    // $resultvals = $connection2->query($sqls);
                    // $states = $resultvals->fetch();
                    // $statename = $states['name'];
                    if ($isFeeGenerate == '2') {
                        $sql2 = "SELECT transaction_id FROM fn_fees_applicant_collection WHERE submission_id = " . $sid . "  ";
                        $resulttr = $connection2->query($sql2);
                        $stateChk = $resulttr->fetch();
                        if (!empty($stateChk['transaction_id'])) {
                            $dataSet->data[$i]["workflowstate"] = 'Submitted';
                        } else {
                            $dataSet->data[$i]["workflowstate"] = 'Created';
                        }
                    } else {
                        $dataSet->data[$i]["workflowstate"] = 'Submitted';
                    }
                }
                if (isset($dataSet->data[$i]["created_at"])) {
                    echo $dataSet->data[$i]["created_at"];
                }

                if (isset($dataSet->data[$i]["created_at"])) {
                    if (empty($dataSet->data[$i]["created_at"])) {
                        $sqls1 = 'Select ws.created_at FROM wp_fluentform_submissions AS ws WHERE ws.id = ' . $dataSet->data[$i]["submission_id"] . ' AND  ws.form_id = ' . $formId . '';
                        $resultvals1 = $connection2->query($sqls1);
                        $submited_form = $resultvals1->fetch();
                        //$created_at = date('d-m-Y H:i:s', datetotime($submited_form['created_at']));
                        $dt = new DateTime($submited_form['created_at']);
                        $created_at = $dt->format('d-m-Y H:i:s');
                        $dataSet->data[$i]["created_at"] = $created_at;
                    }
                }


                // echo  $dataSet->data[$i]["submission_id"];



                //$dt = array();

                $table->addColumn('workflowstate', __('Status'))
                    ->width('10%')
                    ->translatable();

                if(strpos($baseurl,"gigis")>-1){
                    //if(strpos($baseurl,"localhost")>-1){
                    $sql = 'SELECT * FROM campaign_payment_attachment WHERE campaign_id= "'.$id.'" AND submission_id= "'.$dataSet->data[$i]["submission_id"].'" ';
                    $result = $connection2->query($sql);
                    $attachData = $result->fetch();
                    if (!empty($attachData)) {
                        // $dataSet->data[$i]["attachFile"] = '<a href=" '. $attachData['pay_attachment'] .'"  title="Download Pay Receipt " download><i title="Uploaded Pay receipt" class="mdi mdi-file-pdf mdi-24px download_icon"></i></a>';
                        $dataSet->data[$i]["attachFile"] = '<a href="fullscreen.php?q=/modules/Campaign/pay_receipt_template.php&cid='.$id.'&sid='.$dataSet->data[$i]["submission_id"].'"  title="Pay Receipts " class="thickbox btn btn-secondary">View</a>';
                    } else {
                        $dataSet->data[$i]["attachFile"] = '';
                    }
                }

                $table->addColumn('attachFile', __('Fee Details'))
                ->width('10%')
                ->translatable();


                while ($j < $jlen) {
                    $dataSet->data[$i][$field[$j]] = $fieldval[$j];
                    if ($flag) {
                        foreach ($arrHeader as $ar) {
                            $headcol = ucwords(str_replace("_", " ", $ar));
                            if ($ar == 'file-upload') {
                                $table->addColumn('' . $ar . '', __('' . $headcol . ''))
                                    ->format(function ($dataSet) {
                                        if ($dataSet['file-upload'] != '') {
                                            return '<a href="' . $dataSet['file-upload'] . '" download><i class="mdi mdi-download  mdi-24px download_icon"></i></a>';
                                        }
                                    });
                            } elseif ($ar == 'image-upload') {
                                $table->addColumn('' . $ar . '', __('' . $headcol . ''))
                                    ->format(function ($dataSet) {
                                        if ($dataSet['image-upload'] != '') {
                                            return '<a href="' . $dataSet['image-upload'] . '" download><i class="mdi mdi-download  mdi-24px download_icon"></i></a>';
                                        }
                                    });
                            } else {
                                $table->addColumn('' . $ar . '', __('' . $headcol . ''))
                                    ->width('10%')
                                    ->notSortable()
                                    ->translatable();
                            }
                        }
                    }
                    $j++;
                }
                $flag = FALSE;
                unset($dataSet->data[$i]["field_name"], $dataSet->data[$i]["field_value"]);
                $i++;
            }


            $table->addColumn('created_at', __('Submitted Date and time'))
                ->width('10%')
                ->translatable();

            
            

            $table->addActionColumn()

                ->addParam('submission_id')
                ->format(function ($dataSet, $actions) use ($guid) {
                    $actions->addAction('View', __('Show History'))
                        ->setTitle('form')
                        ->setIcon('eye')
                        ->setId('showhistory-' . $dataSet["submission_id"])
                        ->setURL('modules/Campaign/history.php')
                        ->modalWindow(1100, 550);

                    $actions->addAction('form', __('Form'))
                        ->setId('showform-' . $dataSet["submission_id"])
                        ->setURL('/modules/Campaign/form_open.php')
                        ->modalWindow(1100, 550);
                });
        }


        // echo '<pre>';
        // print_r($dataSet);
        // echo '</pre>';
        echo $table->render($dataSet);
    }
}
?>

<style>
    .download_icon {
        cursor: pointer;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 90px;
        height: 34px;
    }

    .switch input {
        display: none;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ca2222;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked+.slider {
        background-color: #2ab934;
    }

    input:focus+.slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked+.slider:before {
        -webkit-transform: translateX(55px);
        -ms-transform: translateX(55px);
        transform: translateX(55px);
    }

    /*------ ADDED CSS ---------*/
    .on {
        display: none;
    }

    .on,
    .off {
        color: white;
        position: absolute;
        transform: translate(-50%, -50%);
        top: 50%;
        left: 50%;
        font-size: 15px;
        font-family: Verdana, sans-serif;
    }

    input:checked+.slider .on {
        display: block;
    }

    input:checked+.slider .off {
        display: none;
    }

    /*--------- END --------*/

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    /* .table-responsive {
        height: 500px;
    } */

    #expore_tbl{
        margin-top:-15px;
    }

    .card-body{
        margin-top:-15px;
    }

    .table-responsive {
        height: 375px !important;
    }
</style>

<script>

    $(document).ready(function() {
        window.setTimeout(function () {
            $('#expore_tbl').find("input[name='submission_id[]']").each(function() {
                $(this).addClass('include_cell');
                $(this).closest('tr').addClass('rm_cell');
                var val = $(this).val();
                var type = 'chkInvoice';
                var thiscls = $(this);
                //$(this).addClass('invoicemade');
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: {
                        val: val,
                        type: type
                    },
                    async: true,
                    success: function(response) {
                        if (response == 'yes') {
                            thiscls.addClass('invoicemade');
                        }
                    }
                });
            });
        }, 1000);
        


        $(document).on('change', '.include_cell', function() {
            if ($(this).is(":checked")) {
                $(this).closest('tr').removeClass('rm_cell');
            } else {
                $(this).closest('tr').addClass('rm_cell');
            }
        });

        $(document).on('change', '.invoicemade', function() {
            if ($(this).is(":checked")) {
                $("#admissionFeePayment").show();
            } else {
                $("#admissionFeePayment").hide();
            }
        });

        $(document).on('click', '#admissionFeePayment', function(e) {
            e.preventDefault();
            var checked = $("input[name='submission_id[]']:checked").length;
            if (checked > 1) {
                alert('You Have to Select only One Applicant.');
            } else {
                var type = 'getApplicationInvoice';
                var hrf = $("#clickAdmissionFeePayment").attr('href');
                var favorite = [];
                $.each($("input[name='submission_id[]']:checked"), function() {
                    favorite.push($(this).val());
                });
                var id = favorite.join(",");
                var newhrf = hrf + '&sid=' + id + '&width=1200&height=500';
                $("#clickAdmissionFeePayment").attr('href', newhrf);
                $("#clickAdmissionFeePayment")[0].click();
            }
        });

    });

    //jQuery.noConflict()(function($){
    $(document).on('click', "#checkall", function() {
        var id = 'all';
        var cid = $("#campId").val();
        var fid = $("#formId").val();
        var type = 'getCampaignStatusButton';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: {
                val: id,
                type: type,
                cid: cid,
                fid: fid
            },
            async: true,
            success: function(response) {
                $("#statusButton").html();
                $("#statusButton").html(response);
            }
        });
    });
    /*
    $(document).ready(function() {
        $('#expore_tbl').DataTable( {
            dom: 'Bfrtip',
            buttons: [
                'copy',
                'csv',
                'excel',
                'pdf',
                {
                    extend: 'print',
                    text: 'Print all (not just selected)',
                    exportOptions: {
                        modifier: {
                            selected: null
                        }
                    }
                }
            ],
            select: true
        } );
    } );
    */
    //});    

    $(document).on('click', '#pdf_export', function() {
        //var type = 'getApplicationInvoice';
        //var hrf = $("#clickAdmissionFeePayment").attr('href');
        var checked = $("input[name='submission_id[]']:checked").length;
        if (checked >= 1) {
            // var favorite = [];
            // $.each($("input[name='submission_id[]']:checked"), function() {
            //     var id = $(this).val();
            //     var nme = $("#"+id+"-subId").val();
            //     var link = document.createElement('a');
            //     link.href = "/public/applicationpdf/"+id+"-application.pdf";
            //     link.download = nme+".pdf";
            //     link.click();
            // });
            var favorite = [];
            $.each($("input[name='submission_id[]']:checked"), function() {
                favorite.push($(this).val());
            });
            var collid = favorite.join(",");
            if (favorite.length == 1) {
                var url = $("#downloadLink").attr('data-hrf');
                var newurl = url + collid;
                if (collid != '') {
                    $("#downloadLink").attr('href', newurl);
                    window.setTimeout(function() {
                        $("#downloadLink")[0].click();
                    }, 100);
                }
            } else {
                alert('You Have to Select One Applicant at a time.');
            }

        } else {
            alert('You Have to Select Applicant');
        }

    });

    $(document).on('click', '#showHistory', function() {
        var favorite = [];
        $.each($("input[name='submission_id[]']:checked"), function() {
            favorite.push($(this).val());
        });
        var stuid = favorite.join(",");
        if (stuid) {
            if (favorite.length == 1) {
                var id = stuid;
                $("#showhistory-" + id)[0].click();
            } else {
                alert('You Have to Select One Applicant at a time.');
            }
        } else {
            alert('You Have to Select Applicant.');
        }
    });

    $(document).on('click', '#viewForm', function() {
        var favorite = [];
        $.each($("input[name='submission_id[]']:checked"), function() {
            favorite.push($(this).val());
        });
        var stuid = favorite.join(",");
        if (stuid) {
            if (favorite.length == 1) {
                var id = stuid;
                $("#showform-" + id)[0].click();
            } else {
                alert('You Have to Select One Applicant at a time.');
            }
        } else {
            alert('You Have to Select Applicant.');
        }
    });

    $(document).on('keydown', '#applicationName', function(e) {
        var key = e.which;
        if (key == 13) {
            $("#filterCampaign").click();
        }
    });

    $(document).on('keydown', '#applicationId', function(e) {
        var key = e.which;
        if (key == 13) {
            $("#filterCampaign").click();
        }
    });

    $(document).on('change', '#applicationStatus', function(e) {
        $("#filterCampaign").click();
    });

    $(document).on('change', '#applicationClass', function(e) {
        $("#filterCampaign").click();
    });

    $(document).on('change', '#applicationProg', function(e) {
        $("#filterCampaign").click();
    });

    $(document).on('click', "#saveApplicant", function() {
        var favorite = [];
        $.each($("input[name='submission_id[]']:checked"), function() {
            favorite.push($(this).val());
        });
        var submit_id = favorite.join(", ");
        var url = $(this).attr('data-href');
        if (submit_id) {
            var cid = $("#cmpId").val();
            var fid = $(this).attr('data-formid');
            var sid = $(this).attr('data-sid');
            var sname = $(this).attr('data-name');
            var noti = $(this).attr('data-noti');
            var remrk = $(this).attr('data-remark');
            $.ajax({
                url: url,
                type: 'post',
                data: {
                    subid: submit_id,
                    campaign_id: cid,
                    fid: fid,
                    sid: sid,
                    sname: sname,
                    noti: noti,
                    remrk: remrk
                },
                async: true,
                success: function(response) {
                    if (response == 'fail') {
                        alert('Seats are Full!');
                    } else {
                        alert('Your Applicant Admitted Successfully! Click Ok to Continue');
                        location.reload();
                    }

                }
            });
        } else {
            alert('You have to Select Applicant');
        }
    });

    $(document).on('change', '#applicationProg', function() {
        var val = $(this).val();
        var cid = $("#campId").val();
        if (val != '') {
            var type = 'getCampClass';
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: {
                    val: val,
                    type: type,
                    cid: cid
                },
                async: true,
                success: function(response) {
                    $("#applicationClass").html('');
                    $("#applicationClass").html(response);
                }
            });
        }
    });
</script>
<?php

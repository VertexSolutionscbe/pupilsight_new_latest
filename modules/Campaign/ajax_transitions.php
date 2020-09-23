<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/ajax_transitions.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $aid = $_POST['ncid'];
    $tablename = $_POST['tname'];
    $cname = $_POST['cname'];

    $sqlq = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE  table_schema='" . $_SESSION['databaseName'] . "' ";
    $resultval = $connection2->query($sqlq);
    $rowdata = $resultval->fetchAll();

    $tables = array();
    $tables2 = array();
    $tables1 = array('' => 'Select Table');
    foreach ($rowdata as $dt) {
        $tables2[$dt['TABLE_NAME']] = $dt['TABLE_NAME'];
    }
    $tables = $tables1 + $tables2;


    $sql = 'SELECT id, form_id, name FROM campaign ';
    $result = $connection2->query($sql);
    $rowdatacamp = $result->fetchAll();

    $campaign = array();
    $campaign2 = array();
    $campaign1 = array('' => 'Select Campaign');
    foreach ($rowdatacamp as $dt) {
        $campaign2[$dt['id']] = $dt['name'];
    }
    $campaign = $campaign1 + $campaign2;

    $data = '<div id="requireddiv' . $aid . '" class="requiredcss"></div>
    <div id="seatdiv" class="row deltr' . $aid . '">
    <div class="col-sm newdes">
    <div  class="input-group stylish-input-group">
    <div class=" mb-1"></div>
    <div class=" txtfield mb-1">
    <div class="flex-1 relative">
    <select data-rid="' . $aid . '" name="table_name[' . $aid . ']" class="tableName w-full txtfield">';
    foreach ($tables as $st) {
        if ($st == $tablename) {
            $sel = 'selected';
        } else {
            $sel = '';
        }
        $data .= '<option value="' . $st . '" ' . $sel . '>' . $st . '</option>';
    }
    $data .= ' </select>
    </div>
    </div>
    </div>
    </div>

    <div class="col-sm newdes" >
    <div class="input-group stylish-input-group" >
    <div class=" mb-1"></div><div class=" txtfield mb-1">
    <div class="flex-1 relative">
    <select id="columnName' . $aid . '" name="column[' . $aid . ']" class="w-full txtfield">';
    $data .= ' </select>
    </div>
    </div>
    </div>
    </div>
    <div class="col-sm newdes" >
    <div class="input-group stylish-input-group" >
    <div class=" mb-1"></div>
    <div class=" txtfield mb-1">
    <div class="flex-1 relative">
    <select data-rid="' . $aid . '" name="campaign[' . $aid . ']" class="campaignName w-full txtfield">';
    foreach ($campaign as $k => $au) {
        if ($k == $cname) {
            $csel = 'selected';
        } else {
            $csel = '';
        }
        $data .= '<option value="' . $k . '" ' . $csel . '>' . $au . '</option>';
    }
    $data .= ' </select>
    </div>
    </div>
    </div>	
    </div>

    <div class="col-sm newdes" >
    <div class="input-group mt-1" style="display:inline-flex;">
    <select id="fluentForm' . $aid . '" name="fluent_form[' . $aid . ']" class="w-full txtfield" style="width:250px;">';
    $data .= ' </select>
    <i style="cursor:pointer;padding:6px;" class="dte mdi mdi-close mdi-24px delSeattr" data-id="' . $aid . '"></i>
    </div>
    </div>
</div>';
    echo $data;
    // update by bikash//
}

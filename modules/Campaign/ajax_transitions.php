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
    
    $sqlq = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE  table_schema='".$_SESSION['databaseName']."' ";
    $resultval = $connection2->query($sqlq);
    $rowdata = $resultval->fetchAll();
    
    $tables=array();  
    $tables2=array();  
    $tables1=array(''=>'Select Table');
    foreach ($rowdata as $dt) {
        $tables2[$dt['TABLE_NAME']] = $dt['TABLE_NAME'];
    }
    $tables= $tables1 + $tables2;   
    

    $sql = 'SELECT id, form_id, name FROM campaign ';
    $result = $connection2->query($sql);
    $rowdatacamp = $result->fetchAll();
   
    $campaign=array();  
    $campaign2=array();  
    $campaign1=array(''=>'Select Campaign');
    foreach ($rowdatacamp as $dt) {
        $campaign2[$dt['id']] = $dt['name'];
    }
    $campaign= $campaign1 + $campaign2;   

    $data = '<tr id="requireddiv'.$aid.'" class=" requiredcss flex flex-col sm:flex-row justify-between content-center p-0 "></tr><tr id="seatdiv" class=" deltr' . $aid . '  flex flex-col sm:flex-row justify-between content-center p-0"><td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes" ><div  class="input-group stylish-input-group">
        <div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative">
        <select data-rid="'.$aid.'" name="table_name['.$aid.']" class="tableName w-full txtfield">';
        foreach($tables as $st){ 
            if($st == $tablename){
                $sel = 'selected';
            } else {
                $sel = '';
            }
            $data .= '<option value="'.$st.'" '.$sel.'>'.$st.'</option>';
        }
        $data .= ' </select></div></div>
    </div></td>
      <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes" >
    <div class="input-group stylish-input-group" >
        <div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative">
        <select id="columnName'.$aid.'" name="column['.$aid.']" class="w-full txtfield">';
        $data .= ' </select></div></div>
    </div></td>
     <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes" >
    <div class="input-group stylish-input-group" >
        <div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><select data-rid="'.$aid.'" name="campaign['.$aid.']" class="campaignName w-full txtfield">';
        foreach($campaign as $k=>$au){ 
            if($k == $cname){
                $csel = 'selected';
            } else {
                $csel = '';
            }
            $data .= '<option value="'.$k.'" '.$csel.'>'.$au.'</option>';
        }
        $data .= ' </select></div></div>
    </div>	
</td>
        <td class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes" >
    <div class="input-group stylish-input-group" ><div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><select id="fluentForm'.$aid.'" name="fluent_form['.$aid.']" class="w-full txtfield">';
        $data .= ' </select></div></div>
    </div>	</td>
    <td style="width:14%" class="w-full  px-2 border-b-0 sm:border-b border-t-0 newdes nobrdbtm1">
    <div class="input-group stylish-input-group"  style="margin-left: 30px;display:inline-flex;">
        <div class="dte mb-1"></div><div class="dte mb-1"  style="font-size: 25px; padding:  0px 0 0px 4px; width: 30px"><i style="cursor:pointer" class="far fa-times-circle delSeattr" data-id="' . $aid . '"></i></div></div></td>
    
</tr>';
    
  
    echo $data;
  // update by bikash//
}


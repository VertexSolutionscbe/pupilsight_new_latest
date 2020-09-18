<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/campaignFluentFields.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {  
    $cid = $_POST['campformid'];
    $sqlc = 'Select form_id FROM campaign WHERE id = '.$cid.'  ';
    $resultc = $connection2->query($sqlc);
    $rowdatac = $resultc->fetch();

    $campformid = $rowdatac['form_id'];
   
    $sql = 'Select form_fields FROM wp_fluentform_forms WHERE id = '.$campformid.'  ';
    $result = $connection2->query($sql);
    $rowdata = $result->fetch();
    $field = json_decode($rowdata['form_fields']);
    $fields = array();
    // echo '<pre>';
    // print_r($field);
    // echo '</pre>';
    $data = '<option value="" >Select Field</option>';
    foreach($field as $fe){
        foreach($fe as $f){
            if(!empty($f->attributes)){
                $data .= '<option value="'.$f->attributes->name.'" >'.ucwords($f->attributes->name).'</option>';
            }
        }
    }
    // foreach ($rowdata as $dt) {
    //     $data .= '<option value="'.$dt['id'].'" >'.$dt['field_name'].' '.$dt['sub_field_name'].'</option>';
    // }

    // $sqlq = 'SELECT * FROM wp_fluentform_entry_details WHERE form_id = '.$campformid.' ';
    // $resultval = $conn->query($sqlq);
    // $data = '<option value="" >Select Field</option>';
    // while($row = $resultval->fetch_assoc()) {
    //     $data .= '<option value="'.$row['id'].'" >'.$row['field_name'].' '.$row['sub_field_name'].'</option>';
    // }
   
     
    echo $data;
}
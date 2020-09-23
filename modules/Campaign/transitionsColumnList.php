<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';


if (isActionAccessible($guid, $connection2, '/modules/Campaign/transitionsColumnList.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {  
    //Proceed!
    $tabname = $_POST['tabname'];
   
    $sqlq = 'SHOW COLUMNS FROM '.$tabname.' ';
    $resultval = $connection2->query($sqlq);
    $rowdata = $resultval->fetchAll();
    $data = array();
    $data['col'] = '<option value="" >Select Column</option>';
      foreach ($rowdata as $dt) {
        if($dt['Null'] == 'NO'){
          $notnull = '*';
          $fieldname[] = $dt['Field'];
        } else {
          $notnull = '';
        }
        $data['col'] .= '<option value="'.$dt['Field'].'" >'.$dt['Field'].' '.$notnull.'</option>';
      }
    // echo '<pre>';
    // print_r($fieldname);
    // echo '</pre>';
    $data['required'] = 'Required Field : '.implode(' , ',$fieldname);
      echo json_encode($data);
  //  return $data;
  // update by bikash//
}
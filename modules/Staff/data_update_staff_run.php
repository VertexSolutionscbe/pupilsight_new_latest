<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\DataSet;
use Pupilsight\Services\Format;


include $_SERVER["DOCUMENT_ROOT"].'/db.php';



require __DIR__ . '/moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/data_update_staff_run.php';

if (isActionAccessible($guid, $connection2, "/modules/Staff/data_update_staff_run.php")==false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, $returns);
    }
        $page->breadcrumbs->add(__('Staff Data Update'));
        $form = Form::create('importStep1', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/data_update_staff_run.php');

        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

        
        $row = $form->addRow();
        $row->addLabel('file', __('File'))->description(__('See Notes below for specification.'));
        $row->addFileUpload('file')->required()->accepts('.csv');

        $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

        echo $form->getOutput();


        if($_POST){
            $handle = fopen($_FILES['file']['tmp_name'], "r");
            $headers = fgetcsv($handle, 10000, ",");
            $hders = array();
            // echo '<pre>';
            // print_r($headers);
            // echo '</pre>';
            $chkHeaderKey = array();
            foreach($headers as $key => $hd){
                if($hd == 'Staff Id'){
                    $headers[$key] = 'pupilsightPersonID';
                }
                else if($hd == 'Staff Name'){
                    $headers[$key] = 'at_stuName';
                }
               else if($hd == 'Official Name'){
                    $headers[$key] = '##_officialName';
                }
                else if($hd == 'Gender'){
                    $headers[$key] = '##_gender';
                }
                else if($hd == 'Date of Birth'){
                    $headers[$key] = '##_dob';
                }
                else if($hd == 'Username'){
                    $headers[$key] = '##_username';
                }
                else if($hd == 'Can Login'){
                    $headers[$key] = '##_canLogin';
                }
                else if($hd == 'Email'){
                    $headers[$key] = '##_email';
                }
                else if($hd == 'Mobile'){
                    $headers[$key] = '##_phone1';
                }
                else if($hd == 'Address'){
                    $headers[$key] = '##_address1';
                }
                else if($hd == 'District'){
                    $headers[$key] = '##_address1District';
                }
                else if($hd == 'Country'){
                    $headers[$key] = '##_address1Country';
                }
                else if($hd == 'First Language'){
                    $headers[$key] = '##_languageFirst';
                }
                else if($hd == 'Second Language'){
                    $headers[$key] = '##_languageSecond';
                }
                else if($hd == 'Third Language'){
                    $headers[$key] = '##_languageThird';
                }
                else if($hd == 'Country of Birth'){
                    $headers[$key] = '##_countryOfBirth';
                }
                else if($hd == 'Ethnicity'){
                    $headers[$key] = '##_ethnicity';
                }
                else if($hd == 'Religion'){
                    $headers[$key] = '##_religion';
                }
                else if($hd == 'National ID Card Number'){
                    $headers[$key] = '##_nationalIDCardNumber';
                }
                else {

                    $sqlchk = 'SELECT field_name, modules FROM custom_field WHERE field_title = "'.$hd.'"';
                    $resultchk = $connection2->query($sqlchk);
                    $cd = $resultchk->fetch();
                    $modules = explode(',',$cd['modules']);
                    
                    if(!in_array('##_'.$cd['field_name'], $chkHeaderKey)){
                        if(in_array('staff', $modules)){
                            $headers[$key] = '##_'.$cd['field_name'];
                            $chkHeaderKey[] = '##_'.$cd['field_name'];
                        }
                    }
                    
                }
            }
           
            $hders = $headers;
            
            $all_rows = array();
            while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) 
            {
                $all_rows[] = array_combine($hders, $data);
            }
            
            if(!empty($all_rows)){

                // echo '<pre>';
                //     print_r($all_rows);
                //     echo '</pre>';
                //    die();
                foreach($all_rows as  $alrow){
                    
                    // Student Update
                    $sql = "UPDATE pupilsightPerson SET ";
                        foreach($alrow as $key => $ar){
                            if (strpos($key, '##_') !== false && !empty($ar)) {
                                $clname = ltrim($key, '##_'); 
                                $sql .= $clname.'= "'.$ar.'",';
                            } 
                        } 
                        $sql = rtrim($sql, ", ");
                    $sql .= " WHERE pupilsightPersonID = '".$alrow['pupilsightPersonID']."'";
                   //echo $sql;
                    $conn->query($sql);
                    
                }
               // die();
            }

            
            fclose($handle);

            $URL .= '&return=success1';
            header("Location: {$URL}");
        }

}

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

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/data_update_student_run.php';

if (isActionAccessible($guid, $connection2, "/modules/Students/data_update_student_run.php")==false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
        $page->breadcrumbs->add(__('Student Data Update'));
        $form = Form::create('importStep1', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/data_update_student_run.php');

        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

        
        $row = $form->addRow();
        $row->addLabel('file', __('File'))->description(__('See Notes below for specification.'));
        $row->addFileUpload('file')->required()->accepts('.csv');

        $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

        echo $form->getOutput();


        if($_POST){
            if(!empty($_FILES['file']['name'])){
                $handle = fopen($_FILES['file']['tmp_name'], "r");
                $headers = fgetcsv($handle, 10000, ",");
                $hders = array();
                // echo '<pre>';
                // print_r($headers);
                // echo '</pre>';
                $chkHeaderKey = array();
                foreach($headers as $key => $hd){
                    if($hd == 'Student Id'){
                        $headers[$key] = 'pupilsightPersonID';
                    }
                    else if($hd == 'Student Name'){
                        $headers[$key] = 'at_stuName';
                    }
                    else if($hd == 'Academic Year'){
                        $headers[$key] = 'at_pupilsightSchoolYearID';
                    }
                    else if($hd == 'Program'){
                        $headers[$key] = 'at_pupilsightProgramID';
                    }
                    else if($hd == 'Class'){
                        $headers[$key] = 'at_pupilsightYearGroupID';
                    }
                    else if($hd == 'Section'){
                        $headers[$key] = 'at_pupilsightRollGroupID';
                    }
                    else if($hd == 'Official Name'){
                        $headers[$key] = 'st_officialName';
                    }
                    else if($hd == 'Gender'){
                        $headers[$key] = 'st_gender';
                    }
                    else if($hd == 'Date of Birth'){
                        $headers[$key] = 'st_dob';
                    }
                    else if($hd == 'Username'){
                        $headers[$key] = 'st_username';
                    }
                    else if($hd == 'Can Login'){
                        $headers[$key] = 'st_canLogin';
                    }
                    else if($hd == 'Email'){
                        $headers[$key] = 'st_email';
                    }
                    else if($hd == 'Address'){
                        $headers[$key] = 'st_address1';
                    }
                    else if($hd == 'District'){
                        $headers[$key] = 'st_address1District';
                    }
                    else if($hd == 'Country'){
                        $headers[$key] = 'st_address1Country';
                    }
                    else if($hd == 'First Language'){
                        $headers[$key] = 'st_languageFirst';
                    }
                    else if($hd == 'Second Language'){
                        $headers[$key] = 'st_languageSecond';
                    }
                    else if($hd == 'Third Language'){
                        $headers[$key] = 'st_languageThird';
                    }
                    else if($hd == 'Country of Birth'){
                        $headers[$key] = 'st_countryOfBirth';
                    }
                    else if($hd == 'Ethnicity'){
                        $headers[$key] = 'st_ethnicity';
                    }
                    else if($hd == 'Religion'){
                        $headers[$key] = 'st_religion';
                    }
                    else if($hd == 'National ID Card Number'){
                        $headers[$key] = 'st_nationalIDCardNumber';
                    }
                    else {

                        $sqlchk = 'SELECT field_name, modules FROM custom_field WHERE field_title = "'.$hd.'"';
                        $resultchk = $connection2->query($sqlchk);
                        $cd = $resultchk->fetch();
                        $modules = explode(',',$cd['modules']);
                        
                        if(!in_array('st_'.$cd['field_name'], $chkHeaderKey)){
                            if(in_array('student', $modules)){
                                $headers[$key] = 'st_'.$cd['field_name'];
                                $chkHeaderKey[] = 'st_'.$cd['field_name'];
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
                                if (strpos($key, 'st_') !== false && !empty($ar)) {
                                    $clname = ltrim($key, 'st_'); 
                                    $sql .= $clname.'= "'.$ar.'",';
                                } 
                            } 
                            $sql = rtrim($sql, ", ");
                        $sql .= " WHERE pupilsightPersonID = '".$alrow['pupilsightPersonID']."'";
                       echo $sql;
                        $conn->query($sql);
                        
                    }
                // die();
                }

                
                fclose($handle);

                $URL .= '&return=success0';
                header("Location: {$URL}");
            } else {
                $URL .= '&return=error0';
                header("Location: {$URL}");
            }
        }

}

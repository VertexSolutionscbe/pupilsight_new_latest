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

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/import_student_run.php';

if (isActionAccessible($guid, $connection2, "/modules/Students/import_student_run.php")==false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Edited by : Mandeep, Reason : added recomended way for displaying notification
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }
        $page->breadcrumbs->add(__('Student Import'));
        $form = Form::create('importStep1', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/import_student_run.php');

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
                if($hd == 'Academic Year'){
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
                else if($hd == 'Father Official Name'){
                    $headers[$key] = 'ft_officialName';
                }
                else if($hd == 'Father Date of Birth'){
                    $headers[$key] = 'ft_dob';
                }
                else if($hd == 'Father Username'){
                    $headers[$key] = 'ft_username';
                }
                else if($hd == 'Father Can Login'){
                    $headers[$key] = 'ft_canLogin';
                }
                else if($hd == 'Father Email'){
                    $headers[$key] = 'ft_email';
                }
                else if($hd == 'Father Mobile (Country Code)'){
                    $headers[$key] = 'ft_phone1CountryCode';
                }
                else if($hd == 'Father Mobile No'){
                    $headers[$key] = 'ft_phone1';
                }
                else if($hd == 'Father LandLine (Country Code)'){
                    $headers[$key] = 'ft_phone2CountryCode';
                }
                else if($hd == 'Father Landline No'){
                    $headers[$key] = 'ft_phone2';
                }
                else if($hd == 'Mother Official Name'){
                    $headers[$key] = 'mt_officialName';
                }
                else if($hd == 'Mother Date of Birth'){
                    $headers[$key] = 'mt_dob';
                }
                else if($hd == 'Mother Username'){
                    $headers[$key] = 'mt_username';
                }
                else if($hd == 'Mother Can Login'){
                    $headers[$key] = 'mt_canLogin';
                }
                else if($hd == 'Mother Email'){
                    $headers[$key] = 'mt_email';
                }
                else if($hd == 'Mother Mobile (Country Code)'){
                    $headers[$key] = 'mt_phone1CountryCode';
                }
                else if($hd == 'Mother Mobile No'){
                    $headers[$key] = 'mt_phone1';
                }
                else if($hd == 'Mother LandLine (Country Code)'){
                    $headers[$key] = 'mt_phone2CountryCode';
                }
                else if($hd == 'Mother Landline No'){
                    $headers[$key] = 'mt_phone2';
                } else {

                    $sqlchk = 'SELECT field_name, modules FROM custom_field WHERE field_title = "'.$hd.'"';
                    $resultchk = $connection2->query($sqlchk);
                    $cd = $resultchk->fetch();
                    $modules = explode(',',$cd['modules']);
                    
                    //if(!in_array('st_'.$cd['field_name'], $chkHeaderKey)){
                        if(in_array('student', $modules)){
                            $headers[$key] = 'st_'.$cd['field_name'];
                            $chkHeaderKey[] = 'st_'.$cd['field_name'];
                        }
                    //}
                    //else if(!in_array('ft_'.$cd['field_name'], $chkHeaderKey)){
                        if(in_array('father', $modules)){
                            $headers[$key] = 'ft_'.$cd['field_name'];
                            $chkHeaderKey[] = 'ft_'.$cd['field_name'];
                        }
                    //}
                    //else if(!in_array('mt_'.$cd['field_name'], $chkHeaderKey)){
                        if(in_array('mother', $modules)){
                            $headers[$key] = 'mt_'.$cd['field_name'];
                            $chkHeaderKey[] = 'mt_'.$cd['field_name'];
                        }
                    //}
                }
            }
           
            $hders = $headers;
            
            $all_rows = array();
            while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) 
            {
                $all_rows[] = array_combine($hders, $data);
            }


            
            if(!empty($all_rows)){

                function getSaltNew(){
                    $c = explode(' ', '. / a A b B c C d D e E f F g G h H i I j J k K l L m M n N o O p P q Q r R s S t T u U v V w W x X y Y z Z 0 1 2 3 4 5 6 7 8 9');
                    $ks = array_rand($c, 22);
                    $s = '';
                    foreach ($ks as $k) {
                        $s .= $c[$k];
                    }
                    return $s;
                }

                $salt = getSaltNew();
                $pass = 'Admin@123456';
                $password = hash('sha256', $salt.$pass);
                // echo '<pre>';
                //     print_r($all_rows);
                //     echo '</pre>';
                //    die();
                
                foreach($all_rows as  $alrow){
                    
                    $pupilsightSchoolYearID = '0';
                    $pupilsightProgramID = '0';
                    $pupilsightYearGroupID = '0';
                    $pupilsightRollGroupID = '0';
                    $homeAddress = 'Null';
                    $homeDistrict = 'Null';
                    $homeCountry = 'Null';
                    $stu_id = '';
                    $fat_id = '0';
                    $mot_id = '0';
                    if(!empty($alrow['st_address1']) ){
                        $addr = str_replace('"', "", $alrow['st_address1']);
                        $homeAddress = $addr;
                    }
                    if(!empty($alrow['st_address1District']) ){
                        $homeDistrict = $alrow['st_address1District'];
                    }
                    if(!empty($alrow['st_address1Country']) ){
                        $homeCountry = $alrow['st_address1Country'];
                    }    

                    if(!empty($alrow['at_pupilsightSchoolYearID']) ){
                        $sqlaca = 'SELECT pupilsightSchoolYearID FROM pupilsightSchoolYear WHERE name = "'.$alrow['at_pupilsightSchoolYearID'].'"';
                        $resultaca = $connection2->query($sqlaca);
                        $acaData = $resultaca->fetch();
                        $pupilsightSchoolYearID =  $acaData['pupilsightSchoolYearID'];
                    } 
                    if(!empty($alrow['at_pupilsightProgramID']) ){
                        $sqlaca = 'SELECT pupilsightProgramID FROM pupilsightProgram WHERE name = "'.$alrow['at_pupilsightProgramID'].'"';
                        $resultaca = $connection2->query($sqlaca);
                        $acaData = $resultaca->fetch();
                        $pupilsightProgramID =  $acaData['pupilsightProgramID'];
                    } 

                    if(!empty($alrow['at_pupilsightYearGroupID']) ){
                        $sqlaca = 'SELECT pupilsightYearGroupID FROM pupilsightYearGroup WHERE name = "'.$alrow['at_pupilsightYearGroupID'].'"';
                        $resultaca = $connection2->query($sqlaca);
                        $acaData = $resultaca->fetch();
                        $pupilsightYearGroupID =  $acaData['pupilsightYearGroupID'];
                    } 

                    if(!empty($alrow['at_pupilsightRollGroupID']) ){
                        $sqlaca = 'SELECT pupilsightRollGroupID FROM pupilsightRollGroup WHERE name = "'.$alrow['at_pupilsightRollGroupID'].'"';
                        $resultaca = $connection2->query($sqlaca);
                        $acaData = $resultaca->fetch();
                        $pupilsightRollGroupID =  $acaData['pupilsightRollGroupID'];
                    } 
                    
                    // Student Entry
                    $sql = "INSERT INTO pupilsightPerson (";
                        foreach($alrow as $key => $ar){
                            if (strpos($key, 'st_') !== false && !empty($ar)) {
                                //$clname = ltrim($key, 'st_'); 
                                $clname = substr($key,3,strlen($key));
                                $sql .= $clname.',';
                            } 
                        } 
                        $sql .= 'preferredName,pupilsightRoleIDPrimary,pupilsightRoleIDAll';
                        //$sql = rtrim($sql, ", ");
                    $sql .= ") VALUES (";
                        foreach($alrow as $k => $value){
                            if (strpos($k, 'st_') !== false && !empty($value)) {
                                $val = str_replace('"', "", $value);
                                $sql .= '"'.$val.'",';
                            }
                        }
                        $sql .= '"'.$alrow['st_officialName'].'","003","003"';  
                        //$sql = rtrim($sql, ", ");
                    $sql .= ")";
                    $sql = rtrim($sql, ", ");
                    //echo "\n<br/>student ".$sql;
                    //mysqli_query( $conn, $sql );
                    $conn->query($sql);
                    $stu_id = $conn->insert_id;

                    // Father Entry
                    if(!empty($alrow['ft_officialName']) ){
                        $sqlf = "INSERT INTO pupilsightPerson (";
                            foreach($alrow as $key => $ar){
                                if (strpos($key, 'ft_') !== false  && !empty($ar)) {
                                    //$clname = ltrim($key, 'ft_'); 
                                    $clname = substr($key,3,strlen($key));
                                    $sqlf .= $clname.',';
                                } 
                            } 
                            $sqlf .= 'preferredName,gender,passwordStrong,passwordStrongSalt,pupilsightRoleIDPrimary,pupilsightRoleIDAll';
                            //$sqlf = rtrim($sqlf, ", ");
                        $sqlf .= ") VALUES (";
                            foreach($alrow as $k => $value){
                                if (strpos($k, 'ft_') !== false  && !empty($value)) {
                                    $val = str_replace('"', "", $value);
                                    $sqlf .= '"'.$val.'",';
                                }
                            }
                            $sqlf .= '"'.$alrow['ft_officialName'].'","M","'.$password.'","'.$salt.'","004","004"';    
                            //$sqlf = rtrim($sqlf, ", ");
                        $sqlf .= ")";
                        $sqlf = rtrim($sqlf, ", ");

                        //echo "\n<br/>father ".$sqlf;

                        $conn->query($sqlf);
                        $fat_id = $conn->insert_id;
                    }

                    // Mother Entry
                    if(!empty($alrow['mt_officialName']) ){
                        $sqlm = "INSERT INTO pupilsightPerson (";
                            foreach($alrow as $key => $ar){
                                if (strpos($key, 'mt_') !== false  && !empty($ar)) {
                                    //$clname = ltrim($key, 'mt_'); 
                                    $clname = substr($key,3,strlen($key));
                                    $sqlm .= $clname.',';
                                } 
                            } 
                            $sqlm .= 'preferredName,gender,passwordStrong,passwordStrongSalt,pupilsightRoleIDPrimary,pupilsightRoleIDAll';
                            //$sqlm = rtrim($sqlm, ", ");
                        $sqlm .= ") VALUES (";
                            foreach($alrow as $k => $value){
                                if (strpos($k, 'mt_') !== false  && !empty($value)) {
                                    $val = str_replace('"', "", $value);
                                    $sqlm .= '"'.$val.'",';
                                }
                            }  
                            $sqlm .= '"'.$alrow['mt_officialName'].'","F","'.$password.'","'.$salt.'","004","004"';   
                            //$sqlm = rtrim($sqlm, ", ");
                        $sqlm .= ")";
                        $sqlm = rtrim($sqlm, ", ");
                        //echo "\n<br/>mother ".$sqlm;
                        $conn->query($sqlm);
                        $mot_id = $conn->insert_id;
                    }

                    if(!empty($stu_id) && !empty($pupilsightSchoolYearID)){
                        $sqle = "INSERT INTO pupilsightStudentEnrolment (pupilsightPersonID,pupilsightSchoolYearID,pupilsightProgramID,pupilsightYearGroupID,pupilsightRollGroupID) VALUES (".$stu_id.",".$pupilsightSchoolYearID.",".$pupilsightProgramID.",".$pupilsightYearGroupID.",".$pupilsightRollGroupID.")";
                        $enrol = $conn->query($sqle);

                        //echo "\n<br/>pupilsightStudentEnrolment: ".$sqle;
                    }

                    
                    if(!empty($fat_id) || !empty($mot_id)){
                        if(!empty($alrow['ft_officialName']) && !empty($alrow['mt_officialName'])){
                            $name = $alrow['ft_officialName'].' & '.$alrow['mt_officialName'].' Family';
                        } elseif(!empty($alrow['ft_officialName']) && empty($alrow['mt_officialName'])){
                            $name = $alrow['ft_officialName'].' Family';
                        } elseif(empty($alrow['ft_officialName']) && !empty($alrow['mt_officialName'])){
                            $name = $alrow['mt_officialName'].' Family';
                        } else {
                            $name = 'Family';
                        }

                      $sqlfamily = 'INSERT INTO pupilsightFamily (name,homeAddress,homeAddressDistrict,homeAddressCountry) VALUES ("'.$name.'","'.$homeAddress.'","'.$homeDistrict.'","'.$homeCountry.'")';

                      //echo "\n<br/>family: ".$sqlfamily;

                        $conn->query($sqlfamily);
                        $family_id = $conn->insert_id;
                        if(!empty($family_id)){
                            if(!empty($fat_id)){
                                $sqlf1 = "INSERT INTO pupilsightFamilyAdult (pupilsightFamilyID,pupilsightPersonID,childDataAccess,contactPriority,contactCall,contactSMS,contactEmail,contactMail) VALUES (".$family_id.",".$fat_id.",'Y','1','N','N','N','N')";
                                $conn->query($sqlf1);
                                //echo "\n<br/>pupilsightFamilyAdult: ".$sqlf1;

                                $sqlf4 = "INSERT INTO pupilsightFamilyRelationship (pupilsightFamilyID,pupilsightPersonID1,pupilsightPersonID2,relationship) VALUES (".$family_id.",".$fat_id.",".$stu_id.",'Father')";
                                $conn->query($sqlf4);

                                //echo "\n<br/>pupilsightFamilyRelationship: ".$sqlf4;
                            }

                            if(!empty($mot_id)){
                                $sqlf2 = "INSERT INTO pupilsightFamilyAdult (pupilsightFamilyID,pupilsightPersonID,childDataAccess,contactPriority,contactCall,contactSMS,contactEmail,contactMail) VALUES (".$family_id.",".$mot_id.",'Y','2','N','N','N','N')";
                                $conn->query($sqlf2);

                                //echo "\n<br/>pupilsightFamilyAdult: ".$sqlf2;

                                $sqlf5 = "INSERT INTO pupilsightFamilyRelationship (pupilsightFamilyID,pupilsightPersonID1,pupilsightPersonID2,relationship) VALUES (".$family_id.",".$mot_id.",".$stu_id.",'Mother')";

                                //echo "\n<br/>pupilsightFamilyRelationship: ".$sqlf5;
                                $conn->query($sqlf5);
                            }

                            $sqlf3 = "INSERT INTO pupilsightFamilyChild (pupilsightFamilyID,pupilsightPersonID) VALUES (".$family_id.",".$stu_id.")";
                            $conn->query($sqlf3);

                            //echo "\n<br/>pupilsightFamilyChild: ".$sqlf3;

                        }
                    }
                      
                    
                    
                }

            }

            
            fclose($handle);
            //die();
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }

}

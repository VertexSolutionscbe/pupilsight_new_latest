<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\DataSet;
use Pupilsight\Services\Format;


include $_SERVER["DOCUMENT_ROOT"] . '/db.php';



require __DIR__ . '/moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Students/data_update_student_run.php';

if (isActionAccessible($guid, $connection2, "/modules/Students/data_update_student_run.php") == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    $page->breadcrumbs->add(__('Student Data Update'));
    $form = Form::create('importStep1', $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . '/data_update_student_run.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);


    $row = $form->addRow();
    $row->addLabel('file', __('File'))->description(__('See Notes below for specification.'));
    $row->addFileUpload('file')->required()->accepts('.csv');

    $row = $form->addRow();
    $row->addFooter();
    $row->addSubmit();

    echo $form->getOutput();


    if ($_POST) {
        if (!empty($_FILES['file']['name'])) {
            $handle = fopen($_FILES['file']['tmp_name'], "r");
            $headers = fgetcsv($handle, 10000, ",");
            $hders = array();
            // echo '<pre>';
            // print_r($headers);
            // echo '</pre>';
            $chkHeaderKey = array();
            foreach ($headers as $key => $hd) {
                if ($hd == 'Student Id') {
                    $headers[$key] = 'pupilsightPersonID';
                } else if ($hd == 'Student Name') {
                    $headers[$key] = 'at_stuName';
                } else if ($hd == 'Academic Year') {
                    $headers[$key] = 'at_pupilsightSchoolYearID';
                } else if ($hd == 'Program') {
                    $headers[$key] = 'at_pupilsightProgramID';
                } else if ($hd == 'Class') {
                    $headers[$key] = 'at_pupilsightYearGroupID';
                } else if ($hd == 'Section') {
                    $headers[$key] = 'at_pupilsightRollGroupID';
                } else if ($hd == 'Official Name') {
                    $headers[$key] = '##_officialName';
                } else if ($hd == 'Gender') {
                    $headers[$key] = '##_gender';
                } else if ($hd == 'Date of Birth') {
                    $headers[$key] = '##_dob';
                } else if ($hd == 'Username') {
                    $headers[$key] = '##_username';
                } else if ($hd == 'Can Login') {
                    $headers[$key] = '##_canLogin';
                } else if ($hd == 'Email') {
                    $headers[$key] = '##_email';
                } else if ($hd == 'Address') {
                    $headers[$key] = '##_address1';
                } else if ($hd == 'District') {
                    $headers[$key] = '##_address1District';
                } else if ($hd == 'Country') {
                    $headers[$key] = '##_address1Country';
                } else if ($hd == 'First Language') {
                    $headers[$key] = '##_languageFirst';
                } else if ($hd == 'Second Language') {
                    $headers[$key] = '##_languageSecond';
                } else if ($hd == 'Third Language') {
                    $headers[$key] = '##_languageThird';
                } else if ($hd == 'Country of Birth') {
                    $headers[$key] = '##_countryOfBirth';
                } else if ($hd == 'Ethnicity') {
                    $headers[$key] = '##_ethnicity';
                } else if ($hd == 'Religion') {
                    $headers[$key] = '##_religion';
                } else if ($hd == 'National ID Card Number') {
                    $headers[$key] = '##_nationalIDCardNumber';
                } else if ($hd == 'Father Official Name') {
                    $headers[$key] = '&&_officialName';
                } else if ($hd == 'Father Date of Birth') {
                    $headers[$key] = '&&_dob';
                } else if ($hd == 'Father Username') {
                    $headers[$key] = '&&_username';
                } else if ($hd == 'Father Can Login') {
                    $headers[$key] = '&&_canLogin';
                } else if ($hd == 'Father Email') {
                    $headers[$key] = '&&_email';
                } else if ($hd == 'Father Mobile (Country Code)') {
                    $headers[$key] = '&&_phone1CountryCode';
                } else if ($hd == 'Father Mobile No') {
                    $headers[$key] = '&&_phone1';
                } else if ($hd == 'Father LandLine (Country Code)') {
                    $headers[$key] = '&&_phone2CountryCode';
                } else if ($hd == 'Father Landline No') {
                    $headers[$key] = '&&_phone2';
                } else if ($hd == 'Mother Official Name') {
                    $headers[$key] = '$$_officialName';
                } else if ($hd == 'Mother Date of Birth') {
                    $headers[$key] = '$$_dob';
                } else if ($hd == 'Mother Username') {
                    $headers[$key] = '$$_username';
                } else if ($hd == 'Mother Can Login') {
                    $headers[$key] = '$$_canLogin';
                } else if ($hd == 'Mother Email') {
                    $headers[$key] = '$$_email';
                } else if ($hd == 'Mother Mobile (Country Code)') {
                    $headers[$key] = '$$_phone1CountryCode';
                } else if ($hd == 'Mother Mobile No') {
                    $headers[$key] = '$$_phone1';
                } else if ($hd == 'Mother LandLine (Country Code)') {
                    $headers[$key] = '$$_phone2CountryCode';
                } else if ($hd == 'Mother Landline No') {
                    $headers[$key] = '$$_phone2';
                } else {

                    //$sqlchk = 'SELECT field_name, modules FROM custom_field WHERE field_title = "' . $hd . '"';

                    $sqlchk = "SELECT field_name, modules FROM custom_field WHERE field_title = '" . $hd . "' and not  find_in_set('staff',modules)";
                    $resultchk = $connection2->query($sqlchk);
                    $cd = $resultchk->fetch();
                    $modules = explode(',', $cd['modules']);

                    // if (!in_array('##_' . $cd['field_name'], $chkHeaderKey)) {
                    //     if (in_array('student', $modules)) {
                    //         $headers[$key] = '##_' . $cd['field_name'];
                    //         $chkHeaderKey[] = '##_' . $cd['field_name'];
                    //     }
                    // }

                    //if(!in_array('##_'.$cd['field_name'], $chkHeaderKey)){
                    if (in_array('student', $modules)) {
                        $headers[$key] = '##_' . $cd['field_name'];
                        $chkHeaderKey[] = '##_' . $cd['field_name'];
                    }
                    //}
                    //else if(!in_array('&&_'.$cd['field_name'], $chkHeaderKey)){
                    if (in_array('father', $modules)) {
                        $headers[$key] = '&&_' . $cd['field_name'];
                        $chkHeaderKey[] = '&&_' . $cd['field_name'];
                    }
                    //}
                    //else if(!in_array('$$_'.$cd['field_name'], $chkHeaderKey)){
                    if (in_array('mother', $modules)) {
                        $headers[$key] = '$$_' . $cd['field_name'];
                        $chkHeaderKey[] = '$$_' . $cd['field_name'];
                    }
                    //}
                }
            }

            $hders = $headers;

            $all_rows = array();
            while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                $all_rows[] = array_combine($hders, $data);
            }

            if (!empty($all_rows)) {

                // echo '<pre>';
                //     print_r($all_rows);
                //     echo '</pre>';
                //    die();
                foreach ($all_rows as  $alrow) {
                    $fatherID = '';
                    $sqlfat = "SELECT pupilsightPersonID1 FROM pupilsightFamilyRelationship WHERE pupilsightPersonID2 = " . $alrow['pupilsightPersonID'] . " AND relationship = 'Father' ";
                    $resultfat = $connection2->query($sqlfat);
                    $fatData = $resultfat->fetch();
                    if(!empty($fatData)){
                        $fatherID = $fatData['pupilsightPersonID1'];
                    }

                    $motherID = '';
                    $sqlmot = "SELECT pupilsightPersonID1 FROM pupilsightFamilyRelationship WHERE pupilsightPersonID2 = " . $alrow['pupilsightPersonID'] . " AND relationship = 'Mother' ";
                    $resultmot = $connection2->query($sqlmot);
                    $motData = $resultmot->fetch();
                    if(!empty($motData)){
                        $motherID = $motData['pupilsightPersonID1'];
                    }


                    // Student Update
                    $sql = "UPDATE pupilsightPerson SET ";
                    foreach ($alrow as $key => $ar) {
                        if (strpos($key, '##_') !== false && !empty($ar)) {
                            $clname = ltrim($key, '##_');
                            $sql .= $clname . '= "' . $ar . '",';
                        }
                    }
                    $sql = rtrim($sql, ", ");
                    $sql .= " WHERE pupilsightPersonID = '" . $alrow['pupilsightPersonID'] . "'";
                    //echo $sql;
                    $conn->query($sql);

                    if(!empty($fatherID)){
                        //Father Entry
                        $sql = "UPDATE pupilsightPerson SET ";
                        foreach ($alrow as $key => $ar) {
                            if (strpos($key, '&&_') !== false && !empty($ar)) {
                                $clname = ltrim($key, '&&_');
                                $sql .= $clname . '= "' . $ar . '",';
                            }
                        }
                        $sql = rtrim($sql, ", ");
                        $sql .= " WHERE pupilsightPersonID = '" . $fatherID . "'";
                        //echo $sql;
                        $conn->query($sql);
                    }

                    if(!empty($motherID)){
                        //Mother Entry
                        $sql = "UPDATE pupilsightPerson SET ";
                        foreach ($alrow as $key => $ar) {
                            if (strpos($key, '$$_') !== false && !empty($ar)) {
                                $clname = ltrim($key, '$$_');
                                $sql .= $clname . '= "' . $ar . '",';
                            }
                        }
                        $sql = rtrim($sql, ", ");
                        $sql .= " WHERE pupilsightPersonID = '" . $motherID . "'";
                        //echo $sql;
                        $conn->query($sql);
                    }
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

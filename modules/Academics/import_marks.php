<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

include $_SERVER["DOCUMENT_ROOT"] . "/db.php";

require __DIR__ . "/moduleFunctions.php";

$URL =
    $_SESSION[$guid]["absoluteURL"] .
    "/index.php?q=/modules/Academics/import_marks.php";

if (isActionAccessible($guid, $connection2, '/modules/Academics/test_marks_upload.php') == false) {
    // Access denied
    $page->addError(__("You do not have access to this action."));
} else {

    if (isset($_GET["return"])) {
        returnProcess($guid, $_GET["return"], null, null);
    }

    $page->breadcrumbs
    ->add(__('Upload Marks'), 'test_marks_upload.php')
    ->add(__("Import Marks"));
    $form = Form::create(
        "importStep1",
        $_SESSION[$guid]["absoluteURL"] .
            "/index.php?q=/modules/" .
            $_SESSION[$guid]["module"] .
            "/import_marks.php"
    );

    $form->addHiddenValue("address", $_SESSION[$guid]["address"]);

    $row = $form->addRow();
    $row->addLabel("file", __("File"))->description(
        __("See Notes below for specification.")
    );
    $row->addFileUpload("file")
        ->required()
        ->accepts(".csv");

    $row = $form->addRow();
    $row->addFooter();
    $row->addSubmit();

    echo $form->getOutput();
    
    if ($_POST) {
        $handle = fopen($_FILES['file']['tmp_name'], "r");
        $headers = fgetcsv($handle, 10000, ",");
        $hders = array();
        echo '<pre>';
        print_r($headers);
        echo '</pre>';
        //die();
        $chkHeaderKey = array();
        foreach ($headers as $key => $hd) {

            if ($hd == 'Official Name') {
                $headers[$key] = '##_officialName';
            } else if ($hd == 'Type') {
                $headers[$key] = 'at_type';
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
            } else if ($hd == 'Mobile') {
                $headers[$key] = '##_phone1';
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
            } else {

                //$sqlchk = 'SELECT field_name, modules FROM custom_field WHERE field_title = "' . $hd . '"';
                $sqlchk = "SELECT field_name, modules FROM custom_field WHERE field_title = '" . $hd . "' and  find_in_set('staff',modules)";

                $resultchk = $connection2->query($sqlchk);
                $cd = $resultchk->fetch();
                $modules = explode(',', $cd['modules']);

                if (in_array('staff', $modules)) {
                    $headers[$key] = '##_' . $cd['field_name'];
                    $chkHeaderKey[] = '##_' . $cd['field_name'];
                }

                $page->breadcrumbs->add(__('Staff Import'));
                $form = Form::create('importStep1', $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . '/import_staff_run.php');
            }
        }

        $hders = $headers;

        $header2 = array();
        $all_rows = array();
        $k = 0;
        while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
            if($k == 0){
                $header2 = $data;
            } 
            
            if($k != 0) {
                $all_rows[] = array_combine($header2, $data);
            }
            $k++;
        }

        // $all_rows = array();
        // $i=0;
        // while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
        //     if($i != 0){
        //         $all_rows[] = array_combine($header2, $data);
        //     }
        //     $i++;
        // }

        // echo '<pre>';
        // print_r($header2);
        // print_r($all_rows);
        // echo '</pre>';
        // die();

        if (!empty($all_rows)) {

            echo '<pre>';
            print_r($all_rows);
            echo '</pre>';
            // die();
            try {
                foreach ($all_rows as  $alrow) {
                    // Student Entry
                    $sql = "INSERT INTO examinationMarksEntrybySubject (test_id,pupilsightYearGroupID,pupilsightRollGroupID,pupilsightDepartmentID,pupilsightPersonID,skill_id,marks_obtained,gradeId,remark_type,remarks,pupilsightPersonIDTaker) VALUES (";
                    
                    foreach ($alrow as $k => $value) {
                        if ($k == "##_dob" && !empty($value)) {
                            $value = date('Y-m-d', strtotime($value));
                        }
                        if (strpos($k, '##_') !== false && !empty($value)) {
                            $val = str_replace('"', "", $value);
                            $sql .= '"' . $val . '",';
                        }
                    }
                    $sql .= '"' . $alrow['##_officialName'] . '","002","002"';
                    //$sql = rtrim($sql, ", ");
                    $sql .= ")";
                    $sql = rtrim($sql, ", ");
                    // echo $sql;
                    $conn->query($sql);
                    $stu_id = $conn->insert_id;


                    if (!empty($stu_id)) {
                        $sqle = 'INSERT INTO pupilsightStaff (pupilsightPersonID,type) VALUES ("' . $stu_id . '","' . $alrow['at_type'] . '")';
                        $enrol = $conn->query($sqle);
                    }
                }
            } catch (Exception $ex) {
                print_r($ex);
            }
        }

        fclose($handle);

        $URL .= '&return=success1';
        header("Location: {$URL}");
    }
}
?>


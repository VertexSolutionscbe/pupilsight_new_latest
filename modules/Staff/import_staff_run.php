<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\DataSet;
use Pupilsight\Services\Format;


include('C:/xampp/htdocs/pupilsight/db.php');



require __DIR__ . '/moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Staff/import_staff_run.php';

if (isActionAccessible($guid, $connection2, "/modules/Staff/import_staff_run.php") == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Edited by : Mandeep, Reason : Notification after successful update added
    if ($_GET['return'] == "success0") {
        echo "<div class='alert alert-success'>";
        echo __("Data import was successful");
        echo '</div>';
    }
    $page->breadcrumbs->add(__('Staff Import'));
    $form = Form::create('importStep1', $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . '/import_staff_run.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);


    $row = $form->addRow();
    $row->addLabel('file', __('File'))->description(__('See Notes below for specification.'));
    $row->addFileUpload('file')->required()->accepts('.csv');

    $row = $form->addRow();
    $row->addFooter();
    $row->addSubmit();

    echo $form->getOutput();


    if ($_POST) {
        $handle = fopen($_FILES['file']['tmp_name'], "r");
        $headers = fgetcsv($handle, 10000, ",");
        $hders = array();
        // echo '<pre>';
        // print_r($headers);
        // echo '</pre>';
        $chkHeaderKey = array();
        foreach ($headers as $key => $hd) {

            if ($hd == 'Official Name') {
                $headers[$key] = 'st_officialName';
            } else if ($hd == 'Type') {
                $headers[$key] = 'at_type';
            } else if ($hd == 'Gender') {
                $headers[$key] = 'st_gender';
            } else if ($hd == 'Date of Birth') {
                $headers[$key] = 'st_dob';
            } else if ($hd == 'Username') {
                $headers[$key] = 'st_username';
            } else if ($hd == 'Can Login') {
                $headers[$key] = 'st_canLogin';
            } else if ($hd == 'Email') {
                $headers[$key] = 'st_email';
            } else if ($hd == 'Address') {
                $headers[$key] = 'st_address1';
            } else if ($hd == 'District') {
                $headers[$key] = 'st_address1District';
            } else if ($hd == 'Country') {
                $headers[$key] = 'st_address1Country';
            } else if ($hd == 'First Language') {
                $headers[$key] = 'st_languageFirst';
            } else if ($hd == 'Second Language') {
                $headers[$key] = 'st_languageSecond';
            } else if ($hd == 'Third Language') {
                $headers[$key] = 'st_languageThird';
            } else if ($hd == 'Country of Birth') {
                $headers[$key] = 'st_countryOfBirth';
            } else if ($hd == 'Ethnicity') {
                $headers[$key] = 'st_ethnicity';
            } else if ($hd == 'Religion') {
                $headers[$key] = 'st_religion';
            } else if ($hd == 'National ID Card Number') {
                $headers[$key] = 'st_nationalIDCardNumber';
            } else {

                $sqlchk = 'SELECT field_name, modules FROM custom_field WHERE field_title = "' . $hd . '"';
                $resultchk = $connection2->query($sqlchk);
                $cd = $resultchk->fetch();
                $modules = explode(',', $cd['modules']);

                //if(!in_array('st_'.$cd['field_name'], $chkHeaderKey)){
                if (in_array('staff', $modules)) {
                    $headers[$key] = 'st_' . $cd['field_name'];
                    $chkHeaderKey[] = 'st_' . $cd['field_name'];
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

            function getSaltNew()
            {
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
            $password = hash('sha256', $salt . $pass);
            // echo '<pre>';
            //     print_r($all_rows);
            //     echo '</pre>';
            //    die();
            foreach ($all_rows as  $alrow) {

                // Student Entry
                $sql = "INSERT INTO pupilsightPerson (";
                foreach ($alrow as $key => $ar) {
                    if (strpos($key, 'st_') !== false && !empty($ar)) {
                        //$clname = ltrim($key, 'st_'); 
                        $clname = substr($key, 3, strlen($key));
                        $sql .= $clname . ',';
                    }
                }
                $sql .= 'preferredName,pupilsightRoleIDPrimary,pupilsightRoleIDAll';
                //$sql = rtrim($sql, ", ");
                $sql .= ") VALUES (";
                foreach ($alrow as $k => $value) {
                    if (strpos($k, 'st_') !== false && !empty($value)) {
                        $val = str_replace('"', "", $value);
                        $sql .= '"' . $val . '",';
                    }
                }
                $sql .= '"' . $alrow['st_officialName'] . '","002","002"';
                //$sql = rtrim($sql, ", ");
                $sql .= ")";
                $sql = rtrim($sql, ", ");
                $conn->query($sql);
                $stu_id = $conn->insert_id;


                if (!empty($stu_id)) {
                    $sqle = 'INSERT INTO pupilsightStaff (pupilsightPersonID,type) VALUES ("' . $stu_id . '","' . $alrow['at_type'] . '")';
                    $enrol = $conn->query($sqle);
                }
            }
        }


        fclose($handle);

        $URL .= '&return=success0';
        header("Location: {$URL}");
    }
}

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

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Finance/fee_master_manage.php';

if (isActionAccessible($guid, $connection2, "/modules/Finance/fee_master_manage.php") == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Edited by : Mandeep, Reason : added recomended way for displaying notification
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $page->breadcrumbs
    ->add(__('Manage Bank And Payment Mode'), 'fee_master_manage.php')
    ->add(__('Manage Bank And Payment Mode Import'));


    $form = Form::create('importStep1', $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . '/import_fee_master_run.php');

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

            if ($hd == 'Name') {
                $headers[$key] = 'name';
            } else if ($hd == 'Type') {
                $headers[$key] = 'type';
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

                // Student Entry
                $sql = "INSERT INTO fn_masters (";
                foreach ($alrow as $key => $ar) {
                    $clname = $key;
                    $sql .= $clname . ',';
                }
                $sql = rtrim($sql, ", ");
                $sql .= ") VALUES (";
                foreach ($alrow as $k => $value) {
                    if($k == 'type'){
                        if($value == 'Bank Name'){
                            $val = 'bank';
                        } else if($value == 'Payment Mode') {
                            $val = 'payment_mode';
                        } else {
                            $val = '';
                        }
                    } else {
                        $val = $value;
                    }
                        $vals = str_replace('"', "", $val);
                        $sql .= '"' . $vals . '",';
                    
                }
                $sql = rtrim($sql, ", ");
                $sql .= ")";
                $sql = rtrim($sql, ", ");
                //echo $sql;
                 $conn->query($sql);
                // $stu_id = $conn->insert_id;

            }
        }


        fclose($handle);

        $URL .= '&return=success1';
        header("Location: {$URL}");
    }
}

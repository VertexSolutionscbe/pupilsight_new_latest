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

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Academics/department_manage.php';

if (isActionAccessible($guid, $connection2, "/modules/Academics/department_manage.php") == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    /*if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }*/

    $page->breadcrumbs->add(__('Manage Subjects'), 'department_manage.php')
    ->add('Subject Import', '');
    $form = Form::create('importStep1', $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . '/import_subject.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);


    $row = $form->addRow();
    $row->addLabel('file', __('File'))->description(__('See Notes below for specification.'));
    $row->addFileUpload('file')->required()->accepts('.csv');

    $row = $form->addRow();
    $row->addFooter();
    $row->addSubmit();

    echo $form->getOutput();
    //print_r($_POST);
    //print_r($_FILES['file']);


    if ($_POST) {

        $handle = fopen($_FILES['file']['tmp_name'], "r");
        $headers = fgetcsv($handle, 10000, ",");
        $hders = array();
        //echo '<pre>';
        //print_r($headers);
        //echo '</pre>';
        //die();
        $chkHeaderKey = array();
        foreach ($headers as $key => $hd) {

            if ($hd == 'Subject Type') {
                $headers[$key] = '##_type';
            } else if ($hd == 'Subject Name') {
                $headers[$key] = '##_name';
            } else if ($hd == 'Subject Code') {
                $headers[$key] = '##_nameShort';
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

           
            // echo '<pre>';
            // print_r($all_rows);
            // echo '</pre>';
            // die();
            try {
                $cnt = 0;
                $dcnt = 0;
                $username_stock = array();
                $dusername_stock = array();
                $dtable_str = "";
                foreach ($all_rows as  $alrow) {
                    try {
                        // Staff Entry
                        $sql = "INSERT INTO pupilsightDepartment (";
                        foreach ($alrow as $key => $ar) {
                            if (strpos($key, '##_') !== false && !empty($ar)) {
                                //$clname = ltrim($key, '##_'); 
                                $clname = substr($key, 3, strlen($key));
                                $sql .= $clname . ',';
                            }
                        }
                        //$sql .= 'preferredName,pupilsightRoleIDPrimary,pupilsightRoleIDAll';
                        $sql = rtrim($sql, ", ");
                        $sql .= ") VALUES (";
                        foreach ($alrow as $k => $value) {
                            if (strpos($k, '##_') !== false && !empty($value)) {
                                $val = str_replace('"', "", $value);
                                $sql .= '"' . $val . '",';
                            }
                        }
                        //$sql .= '"' . $alrow['##_officialName'] . '","002","002"';
                        $sql = rtrim($sql, ", ");
                        $sql .= ")";
                        $sql = rtrim($sql, ", ");
                        echo "\n" . $sql . ";";
                        //$conn->autocommit(FALSE);

                        $conn->query($sql);

                    } catch (PDOException $ex) {
                        $conn->rollback();
                        $exception_result[$exception_count] = $e->getMessage();
                        $exception_count++;
                    }
                }
            } catch (Exception $ex) {
                $exception_result[$exception_count] = $e->getMessage();
                $exception_count++;
            }
        }

        fclose($handle);
        //die();
        $URL .= '&return=success1';
        header("Location: {$URL}");
    }

    //die();
}

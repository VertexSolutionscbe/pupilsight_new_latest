<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;


include $_SERVER["DOCUMENT_ROOT"] . '/db.php';



require __DIR__ . '/moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Staff/import_staff_run.php';

if (isActionAccessible($guid, $connection2, "/modules/Staff/import_staff_run.php") == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
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
?>
    <style>
        .hide {
            display: none;
            visibility: hidden;
        }
    </style>

<?php


    if ($_POST) {
        if (isset($_POST["validFormData"])) {
            //print_r($_POST);

            if (isset($_POST["data"])) {
                $data = $_POST["data"];
            }

            try {
                foreach ($data as  $alrow) {

                    // staff Entry
                    $sql = "INSERT INTO pupilsightPerson (";
                    foreach ($alrow as $key => $ar) {
                        if (strpos($key, '##_') !== false && !empty($ar)) {
                            //$clname = ltrim($key, '##_'); 
                            $clname = substr($key, 3, strlen($key));
                            $sql .= $clname . ',';
                        }
                    }
                    $sql .= 'preferredName,pupilsightRoleIDPrimary,pupilsightRoleIDAll';
                    //$sql = rtrim($sql, ", ");
                    $sql .= ") VALUES (";
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
                    //echo "\n<br>" . $sql;
                    $conn->query($sql);
                    $stu_id = $conn->insert_id;

                    if (!empty($stu_id)) {
                        $sqle = 'INSERT INTO pupilsightStaff (pupilsightPersonID,type) VALUES ("' . $stu_id . '","' . $alrow['at_type'] . '")';
                        $enrol = $conn->query($sqle);
                    }
                }

                $URL .= '&return=success1';
                header("Location: {$URL}");
            } catch (Exception $ex) {
                print_r($ex);
            }
        } else {
            $handle = fopen($_FILES['file']['tmp_name'], "r");
            $headers = fgetcsv($handle, 10000, ",");
            $hders = array();
            // echo '<pre>';
            // print_r($headers);
            // echo '</pre>';
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
                //echo '<pre>';
                //print_r($all_rows);
                //echo '</pre>';

                $tbl = '<hr/><form id="formValidSubmit" class="mt-3" action="' . $URL . '" method="post" enctype="multipart/form-data">';
                $tbl .= "<input type='hidden' name='validFormData' value='1'>";
                $tbl .= "<div class='table-responsive dataTables_wrapper'>";
                $tbl .= "\n<table id='validate_tbl' class='table'>";
                //header
                $tbl .= "\n<thead>";
                foreach ($all_rows as  $alrow) {
                    $tbl .= "\n<tr>";
                    foreach ($alrow as $key => $ar) {
                        if (strpos($key, '##_') !== false) {
                            $clname = substr($key, 3, strlen($key));

                            if ($clname == "phone1" || $clname == "email" || $clname == "username" || $clname == "dob") {
                                $clname .= " - validate";
                            }
                            $tbl .= "\n<th " . $colWidth . ">" . $clname . "</th>";
                        }
                    }
                    $tbl .= "\n</tr>";
                    break;
                }
                $tbl .= "\n</thead>";

                //data
                $tbl .= "\n<tbody>";
                $cnt = 1;
                $row = 0;
                foreach ($all_rows as  $alrow) {
                    $tbl .= "\n<tr>";
                    foreach ($alrow as $k => $value) {
                        if ($k == "##_dob" && !empty($value)) {
                            $value = date('Y-m-d', strtotime($value));
                        }
                        if (strpos($k, '##_') !== false && !empty($value)) {
                            $value = str_replace('"', "", $value);
                        }

                        $tfwidth = "";
                        $tfValidate = "";
                        if ($k == "##_phone1" || $k == "##_email" || $k == "##_username" || $k == "##_dob") {
                            $tfwidth = " style='width:180px;'";
                            $tfValidate = " validActive ";
                        }
                        $tbl .= "\n<td><span class='hide'>" . $value . "</span><input type='text' id='" . $k . "_" . $cnt . "' data-type='" . $k . "' class='w-full " . $tfValidate . "' " . $tfwidth . " name='data[" . $row . "][" . $k . "]' value='" . $value . "'></td>";
                        $cnt++;
                    }
                    $tbl .= "\n</tr>";
                    $row++;
                    $cnt++;
                }
                $tbl .= "\n</tbody></table></div>";
                $tbl .= "\n<button type='button' class='btn btn-primary mt-3' onclick='validateImport();'>Validate & Submit</button>";
                $tbl .= "\n</form>";
                echo $tbl;
            }

            fclose($handle);
        }
    }
}
?>
<script>
    $(document).ready(function() {
        $('#validate_tbl').DataTable({
            "pageLength": 25,
            "lengthMenu": [
                [10, 25, 50, 250, -1],
                [10, 25, 50, 250, "All"]
            ],
            "sDom": '<"top"lfpi>rt<"bottom"ifp><"clear">'
        });
        $(".dataTables_length").find("select").css("width", "90px");
        $(".dataTables_length").find("select").css("display", "inline-block");
    });
</script>
<script>
    var un = new Array(); //user
    var em = new Array(); //email
    var ph = new Array(); // phone
    var formValid = true;

    function validateImport() {
        un = new Array();
        em = new Array();
        ph = new Array();
        formValid = true;
        $(".validActive").each(function() {
            //console.log($(this).val());
            if ($(this).attr("data-type") == "##_username") {
                if ($(this).val()) {
                    un.push($(this).val());
                }
            }

            if ($(this).attr("data-type") == "##_email") {
                if ($(this).val()) {
                    em.push($(this).val());
                }
            }

            if ($(this).attr("data-type") == "##_phone1") {
                if ($(this).val()) {
                    ph.push($(this).val());
                }
            }
        });

        $(".validActive").each(function() {
            var val = $(this).val();
            var dataType = $(this).attr("data-type");
            var flag = isValid(dataType, val);
            if (flag) {
                $(this).css("border", "1px solid rgba(110, 117, 130, 0.2)");
            } else {
                $(this).css("border", "1px solid red");
                formValid = false;
            }
        });
        if (formValid) {
            ///start submit
            $("form#formValidSubmit").submit();
            console.log("ready for submit");
        } else {
            alert("Data is not valid. Please correct the data then proceed.");
        }
    }

    function isValid(dataType, val) {
        //console.log(dataType, val);
        if (val == "") {
            if (dataType != "##_username") {
                return true;
            }
        }

        if (dataType == "##_phone1") {
            var regx = /^[6-9]\d{9}$/;
            if (regx.test(val)) {
                return isDuplicate(ph, val);
            } else {
                return false;
            }
        } else if (dataType == "##_email") {
            if (/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(val)) {
                return isDuplicate(em, val);
            } else {
                return false;
            }
        } else if (dataType == "##_username") {
            return isDuplicate(un, val);
        } else if (dataType == "##_dob") {
            if (moment(val, 'YYYY-MM-DD', true).isValid()) {
                return true;
            }
        }
    }

    function isDuplicate(obj, val) {
        var len = obj.length;
        var i = 0;
        var match = 0;
        while (i < len) {
            if (obj[i] == val) {
                match++;
            }
            i++;
        }
        if (match == 1) {
            return true;
        }
        return false;
    }
</script>
<?php

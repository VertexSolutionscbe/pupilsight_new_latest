<?php
/*
Pupilsight, Flexible & Open School System

*/

use FluentValidator\Arr;
use Pupilsight\Forms\Form;
use Pupilsight\Data\ImportType;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Helper\HelperGateway;
use Pupilsight\Domain\Staff\StaffGateway;

// Increase max execution time, as this stuff gets big
ini_set('max_execution_time', 7200);
ini_set('memory_limit', '1024M');
set_time_limit(1200);

$_POST['address'] = '/modules/Staff/import_staff_manage.php';

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_POST['address'];

if (isActionAccessible($guid, $connection2, "/modules/Staff/export_staff_run.php") == false) {
    // Access denied
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $HelperGateway = $container->get(HelperGateway::class);
    $StaffGateway = $container->get(StaffGateway::class);
    $criteria = $StaffGateway->newQueryCriteria()
        ->pageSize('1000');
    $page->breadcrumbs->add(__('Export Staff Data Update File'));
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    if (!empty($_POST['staff_column']) && !empty($_POST['staff_id'])) {
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';
        // $st = array();

        // die();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="StaffDataUpdate.csv"');
        $columndata = implode(',', $_POST['staff_column']);
        $data = array($columndata);
        // start changes by nishil on 2nd july 2021
        $columnnamedata = implode(',', $_POST['staff_column_name']);
        $namedata = explode(',',$columnnamedata);
        // end changes by nishil on 2nd july 2021
        $fp = fopen('php://output', 'wb');
        foreach ($data as $line) {
            $val = explode(",", $line);
            fputcsv($fp, $val);
        }
        // start changes by nishil on 2nd july 2021
        foreach ($_POST['staff_id'] as $linenew) {
            $data_row = json_decode($linenew,true);
            $selectedDataArr = array();
            // foreach($data_row as $dkey => $dvalue){
            //     if(in_array($dkey,$namedata)){
            //         if($dkey == 'gender'){
            //             if($dvalue == 'F'){
            //                 $dvalue = 'Female';
            //             } else {
            //                 $dvalue = 'Male';
            //             }
            //         }
            //         $selectedDataArr[] = $dvalue;
            //     }
            // }
            foreach($namedata as $namekey => $nameValue){
                if($nameValue == 'gender'){
                    if($data_row[$nameValue] == 'F'){
                        $data_row[$nameValue] = 'Female';
                    } else {
                        $data_row[$nameValue] = 'Male';
                    }
                }
                $selectedDataArr[] = $data_row[$nameValue];
            }
            // $valnew = explode(",", $linenew);
            fputcsv($fp, $selectedDataArr);
        }
        // end changes by nishil on 2nd july 2021
        fclose($fp);
        die();
    }

    $sql = 'SELECT  field_name, field_title, modules FROM custom_field WHERE table_name = "pupilsightPerson" ';
    $result = $connection2->query($sql);
    $customFields = $result->fetchAll();
    // echo '<pre>';
    // print_r($customFields);
    // echo '</pre>';

    if (!empty($customFields)) {
        $stuCusFld = array();
        $fatCusFld = array();
        $motCusFld = array();
        foreach ($customFields as $cf) {
            $modules = explode(',', $cf['modules']);
            if (in_array('staff', $modules)) {
                $stuCusFld[] = $cf;
            }
        }
    }
    $staff = $StaffGateway->getStaffExportData($criteria);
    // echo '<pre>';
    // print_r($staff);
    // echo '</pre>';

?>
    <form method="post" action="" id="generatefile">
        <button type="button" class="thickbox btn btn-primary" id="generate_template_file">Generate Template File</button>
        <h1>Staff's</h1>
        <div class="scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="chkAllFatherField"> Select All</th>
                        <th>Staff Name</th>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staff as $stu) { ?>
                        <tr>
                            <td>
                                <!-- <input type="checkbox" class="fatherField" name="staff_id[]" value="<?php echo $stu['pupilsightPersonID'] . ',' . $stu['preferredName'] .  ',' . $stu['officialName'] . ',' .  $stu['gender'] . ',' . $stu['dob']; ?>"> -->
                                <input type="checkbox" class="fatherField" name="staff_id[]" value='<?php echo json_encode($stu); ?>'>

                            </td>
                            <td><?php echo $stu['preferredName']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <h1>Staff Details Field</h1>
        <table class="table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="chkAllStuField"> Select All</th>
                    <th>Column Name</th>

                </tr>
            </thead>
            <tbody>
                <input type="checkbox" class="" name="staff_column[]" value="Staff Id" checked style="display:none;">
                <input type="checkbox" class="" name="staff_column_name[]" value="pupilsightPersonID" checked style="display:none;">
                <input type="checkbox" class="" name="staff_column[]" value="Staff Name" checked style="display:none;">
                <input type="checkbox" class="" name="staff_column_name[]" value="preferredName" checked style="display:none;">

                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="staff_column[]" value="Official Name">
                        <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="officialName" style="display:none;">
                    </td>
                    <td>Official Name</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="staff_column[]" value="Gender">
                        <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="gender" style="display:none;">
                    </td>
                    <td>Gender</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="staff_column[]" value="Date of Birth">
                        <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="dob" style="display:none;">
                    </td>
                    <td>Date of Birth</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="staff_column[]" value="Username">
                        <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="username" style="display:none;">
                    </td>
                    <td>Username</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="staff_column[]" value="Can Login">
                        <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="canLogin" style="display:none;">
                    </td>
                    <td>Can Login</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="staff_column[]" value="Email">
                        <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="email" style="display:none;">
                    </td>
                    <td>Email</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="staff_column[]" value="Mobile">
                        <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="phone1" style="display:none;">
                    </td>
                    <td>Mobile</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="staff_column[]" value="Address">
                        <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="address1" style="display:none;">
                    </td>
                    <td>Address</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="staff_column[]" value="District">
                        <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="address1District" style="display:none;">
                    </td>
                    <td>District</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="staff_column[]" value="Country">
                        <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="address1Country" style="display:none;">
                    </td>
                    <td>Country</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="staff_column[]" value="First Language">
                        <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="languageFirst" style="display:none;">
                    </td>
                    <td>First Language</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="staff_column[]" value="Second Language">
                        <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="languageSecond" style="display:none;">
                    </td>
                    <td>Second Language</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="staff_column[]" value="Third Language">
                        <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="languageThird" style="display:none;">
                    </td>
                    <td>Third Language</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="staff_column[]" value="Country of Birth">
                        <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="countryOfBirth" style="display:none;">
                    </td>
                    <td>Country of Birth</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="staff_column[]" value="Ethnicity">
                        <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="ethnicity" style="display:none;">
                    </td>
                    <td>Ethnicity</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="staff_column[]" value="Religion">
                        <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="religion" style="display:none;">
                    </td>
                    <td>Religion</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="staff_column[]" value="National ID Card Number">
                        <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="nationalIDCardNumber" style="display:none;">
                    </td>
                    <td>National ID Card Number</td>
                </tr>
                <?php
                if (!empty($stuCusFld)) {
                    foreach ($stuCusFld as $sc) {
                ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="stuField" name="staff_column[]" value="<?php echo $sc['field_title']; ?>">
                                <input type="checkbox" class="stuFieldName" name="staff_column_name[]" value="<?php echo $sc['field_name']; ?>" style="display:none;">
                            </td>
                            <td><?php echo $sc['field_title']; ?> (Custom Field)</td>
                        </tr>
                <?php }
                } ?>

            </tbody>
        </table>
        <?php /*?>
    <h1>Father Details Field</h1>
    <table class="table">
        <thead>
            <tr>
                <th><input type="checkbox" id="chkAllFatherField" > Select All</th>
                <th>Column Name</th>
                
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <input type="checkbox" class="fatherField" name="staff_column[]" value="Father Official Name">
                </td>
                <td>Official Name</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="fatherField" name="staff_column[]" value="Father Date of Birth">
                </td>
                <td>Date of Birth</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="fatherField" name="staff_column[]" value="Father Username">
                </td>
                <td>Username</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="fatherField" name="staff_column[]" value="Father Can Login">
                </td>
                <td>Can Login</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="fatherField" name="staff_column[]" value="Father Email">
                </td>
                <td>Email</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="fatherField" name="staff_column[]" value="Father Mobile (Country Code)">
                </td>
                <td>Mobile (Country Code)</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="fatherField" name="staff_column[]" value="Father Mobile No">
                </td>
                <td>Mobile No</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="fatherField" name="staff_column[]" value="Father LandLine (Country Code)">
                </td>
                <td>LandLine (Country Code)</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="fatherField" name="staff_column[]" value="Father Landline No">
                </td>
                <td>Landline No</td>
            </tr>
            <?php
            if(!empty($fatCusFld)){
                foreach($fatCusFld as $fc){
            ?>
            <tr>
                <td>
                    <input type="checkbox" class="fatherField" name="staff_column[]" value="<?php echo $fc['field_title'];?>">
                </td>
                <td><?php echo $fc['field_title'];?>  (Custom Field)</td>
            </tr>
            <?php } }?>
        </tbody>
    </table>

    <h1>Mother Details Field</h1>
    <table class="table">
        <thead>
            <tr>
                <th><input type="checkbox" id="chkAllMotherField" > Select All</th>
                <th>Column Name</th>
                
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <input type="checkbox" class="motherField" name="staff_column[]" value="Mother Official Name">
                </td>
                <td>Official Name</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="motherField" name="staff_column[]" value="Mother Date of Birth">
                </td>
                <td>Date of Birth</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="motherField" name="staff_column[]" value="Mother Username">
                </td>
                <td>Username</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="motherField" name="staff_column[]" value="Mother Can Login">
                </td>
                <td>Can Login</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="motherField" name="staff_column[]" value="Mother Email">
                </td>
                <td>Email</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="motherField" name="staff_column[]" value="Mother Mobile (Country Code)">
                </td>
                <td>Mobile (Country Code)</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="motherField" name="staff_column[]" value="Mother Mobile No">
                </td>
                <td>Mobile No</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="motherField" name="staff_column[]" value="Mother LandLine (Country Code)">
                </td>
                <td>LandLine (Country Code)</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="motherField" name="staff_column[]" value="Mother Landline No">
                </td>
                <td>Landline No</td>
            </tr>
            <?php
            if(!empty($motCusFld)){
                foreach($motCusFld as $mc){
            ?>
            <tr>
                <td>
                    <input type="checkbox" class="motherField" name="staff_column[]" value="<?php echo $mc['field_title'];?>">
                </td>
                <td><?php echo $mc['field_title'];?>  (Custom Field)</td>
            </tr>
            <?php } }?>
        </tbody>
    </table>
<?php */ ?>
    </form>
    <script>
        $(document).on('change', '#chkAllStuField', function() {
            if ($(this).is(':checked')) {
                $(".stuField").prop("checked", true);
                $(".stuFieldName").prop("checked", true);
            } else {
                $(".stuField").prop("checked", false);
                $(".stuFieldName").prop("checked", false);
            }
        });

        $(document).on('change', '.stuField', function() {
            if ($(this).is(':checked')) {
                $(this).next('.stuFieldName').prop("checked", true);
                //$(".chkChild"+id).prop("checked", true);
            } else {
                $("#chkAllStuField").prop("checked", false);
                $(this).next('.stuFieldName').prop("checked", false);
            }
        });

        $(document).on('change', '#chkAllFatherField', function() {
            if ($(this).is(':checked')) {
                $(".fatherField").prop("checked", true);
            } else {
                $(".fatherField").prop("checked", false);
            }
        });

        $(document).on('change', '.fatherField', function() {
            if ($(this).is(':checked')) {
                //$(".chkChild"+id).prop("checked", true);
            } else {
                $("#chkAllFatherField").prop("checked", false);
            }
        });

        $(document).on('change', '#chkAllMotherField', function() {
            if ($(this).is(':checked')) {
                $(".motherField").prop("checked", true);
            } else {
                $(".motherField").prop("checked", false);
            }
        });

        $(document).on('change', '.motherField', function() {
            if ($(this).is(':checked')) {
                //$(".chkChild"+id).prop("checked", true);
            } else {
                $("#chkAllMotherField").prop("checked", false);
            }
        });
        $('#generate_template_file').click(function(e){
            if($('input[name="staff_id[]"]:checked').length <= 0 && $('input[name="staff_column[]"]:visible:checked').length <= 0){
                alert("Please Select Staff's and staff field");
            } else if($('input[name="staff_id[]"]:checked').length <= 0){
                alert("Please Select Staff's");
            } else if($('input[name="staff_column[]"]:visible:checked').length <= 0){
                alert("Please Select staff field");
            } else {
                $('#generatefile').submit();
            }
        });
    </script>
<?php










    //     $filename = 'StudentImportFile.csv';
    //     $dir = "C:/xampp/htdocs/pupilsight/public/staffImportFile/"; // trailing slash is important
    //    echo $file = $dir . $filename;

    //     if (file_exists($file))
    //     {
    //         echo '1';
    //     header('Content-Description: File Transfer');
    //     header('Content-Type: application/octet-stream');
    //     header('Content-Disposition: attachment; filename='.basename($file));
    //     header('Expires: 0');
    //     header('Cache-Control: must-revalidate');
    //     header('Pragma: public');
    //     header('Content-Length: ' . filesize($file));
    //     ob_clean();
    //     flush();
    //     readfile($file);
    //     exit;
    //     }
}

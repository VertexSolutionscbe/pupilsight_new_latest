<?php
/*
Pupilsight, Flexible & Open School System

*/

use Pupilsight\Data\ImportType;
use Pupilsight\Domain\System\CustomField;

// Increase max execution time, as this stuff gets big
ini_set('max_execution_time', 7200);
ini_set('memory_limit','1024M');
set_time_limit(1200);

$_POST['address'] = '/modules/Staff/import_staff_manage.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_POST['address'];

if (isActionAccessible($guid, $connection2, "/modules/Staff/export_staff_run.php")==false) {
    // Access denied
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $customField  = $container->get(CustomField::class);
    $page->breadcrumbs->add(__('Export Staff Import File'));
    if(!empty($_POST['staff_column'])){
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';
        // die();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="StaffImport.csv"');
        $columndata = implode(',',$_POST['staff_column']);
        $data = array($columndata);
        
        $fp = fopen('php://output', 'wb');
        foreach ( $data as $line ) {
            $val = explode(",", $line);
            fputcsv($fp, $val);
        }
        fclose($fp);
        die();
    }

    $sql = 'SELECT  field_name, field_title, modules, active, field_type FROM custom_field WHERE table_name = "pupilsightPerson" ';
    $result = $connection2->query($sql);
    $customFields = $result->fetchAll();
    // echo '<pre>';
    // print_r($customFields);
    // echo '</pre>';

    if(!empty($customFields)){
        $stuCusFld = array();
        $fatCusFld = array();
        $motCusFld = array();
        foreach($customFields as $cf){
            $modules = explode(',',$cf['modules']);
            if(in_array('staff', $modules)){
                $stuCusFld[] = $cf;
            }
        }
    }
    
?>
<form method="post" action="">
    <button type="submit" class="thickbox btn btn-primary">Generate Template File</button>
    <h1>Staff Details Field</h1> 
    <table class="table">
        <thead>
            <tr>
                <th><input type="checkbox" id="chkAllStuField" > Select All</th>
                <th>Column Name</th>
                
            </tr>
        </thead>
        <tbody>
            
            <tr>
                <td>
                    <input type="checkbox" class="" checked  disabled>
                    <input type="checkbox" class="" name="staff_column[]" value="Official Name" checked style="display:none;">
                </td>
                <td>Official Name</td>
            </tr>
            <!-- <tr>
                <td>
                    <input type="checkbox" class="stuField" name="staff_column[]" value="Type">
                </td>
                <td>Type</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="staff_column[]" value="Gender">
                </td>
                <td>Gender (M,F) (e.g. M)</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="staff_column[]" value="Date of Birth">
                </td>
                <td>Date of Birth (YYYY-MM-DD) (e.g. 1989-05-03)</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="staff_column[]" value="Username">
                </td>
                <td>Username</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="staff_column[]" value="Can Login">
                </td>
                <td>Can Login (Y,N) (e.g. Y)</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="staff_column[]" value="Email">
                </td>
                <td>Email</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="staff_column[]" value="Address">
                </td>
                <td>Address</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="staff_column[]" value="District">
                </td>
                <td>District</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="staff_column[]" value="Country">
                </td>
                <td>Country</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="staff_column[]" value="First Language">
                </td>
                <td>First Language</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="staff_column[]" value="Second Language">
                </td>
                <td>Second Language</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="staff_column[]" value="Third Language">
                </td>
                <td>Third Language</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="staff_column[]" value="Country of Birth">
                </td>
                <td>Country of Birth</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="staff_column[]" value="Ethnicity">
                </td>
                <td>Ethnicity</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="staff_column[]" value="Religion">
                </td>
                <td>Religion</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="staff_column[]" value="National ID Card Number">
                </td>
                <td>National ID Card Number</td>
            </tr> -->
        <?php
            $fieldName = array("gender","dob","username","canLogin","email", "phone1", "address", "address1District", "address1Country", "languageFirst", "languageSecond", "languageThird", "countryOfBirth", "ethnicity", "religion", "nationalIDCardNumber");
                $field = array("Gender","Date of Birth","Username","Can Login","Email", "Mobile", "Address", "District", "Country", "First Language", "Second Language", "Third Language", "Country of Birth", "Ethnicity", "Religion", "National ID Card Number");
                $len = count($field);
                $i = 0;
                while ($i < $len) {
                    if ($customField->isActiveField($customFields, $fieldName[$i])) {
                        if ($customField->isNotTabField($customFields, $fieldName[$i])) {
                ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="stuField" name="staff_column[]" value="<?= $field[$i] ?>">
                            </td>
                            <td><?= $field[$i] ?></td>
                        </tr>
                <?php
                    } }
                    $i++;
                }
                ?>

            <?php
            if(!empty($stuCusFld)){
                foreach($stuCusFld as $sc){
                    if($sc['field_type'] != 'tab' && $sc['active'] == 'Y'){
            ?>
            <tr> 
                <td>
                    <input type="checkbox" class="stuField" name="staff_column[]" value="<?php echo $sc['field_title'];?>">
                </td>
                <td><?php echo $sc['field_title'];?>  (Custom Field)</td>
            </tr>
            <?php } } } ?>

        </tbody>
    </table>

   </form> 
   <script>
        $(document).on('change', '#chkAllStuField', function() {
            if ($(this).is(':checked')) {
                $(".stuField").prop("checked", true);
            } else {
                $(".stuField").prop("checked", false);
            }
        });

        $(document).on('change', '.stuField', function() {
            if ($(this).is(':checked')) {
                //$(".chkChild"+id).prop("checked", true);
            } else {
                $("#chkAllStuField").prop("checked", false);
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

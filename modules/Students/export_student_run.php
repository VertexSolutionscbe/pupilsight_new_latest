<?php
/*
Pupilsight, Flexible & Open School System

*/

use Pupilsight\Data\ImportType;
use Pupilsight\Domain\System\CustomField;

// Increase max execution time, as this stuff gets big
ini_set('max_execution_time', 7200);
ini_set('memory_limit', '1024M');
set_time_limit(1200);

$_POST['address'] = '/modules/Students/import_student_manage.php';

$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_POST['address'];

if (isActionAccessible($guid, $connection2, "/modules/Students/export_student_run.php") == false) {
    // Access denied
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $customField  = $container->get(CustomField::class);
    $page->breadcrumbs->add(__('Export Student Import File'));
    if (!empty($_POST['student_column'])) {
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';
        // die();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="StudentImport.csv"');
        $columndata = implode(',', $_POST['student_column']);
        $data = array($columndata);

        $fp = fopen('php://output', 'wb');
        foreach ($data as $line) {
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

    if (!empty($customFields)) {
        $stuCusFld = array();
        $fatCusFld = array();
        $motCusFld = array();
        foreach ($customFields as $cf) {
            $modules = explode(',', $cf['modules']);
            if (in_array('student', $modules)) {
                $stuCusFld[] = $cf;
            }
            if (in_array('father', $modules)) {
                $fatCusFld[] = $cf;
            }
            if (in_array('mother', $modules)) {
                $motCusFld[] = $cf;
            }
        }
    }

?>
    <form method="post" action="">
        <button type="submit" class="thickbox btn btn-primary">Generate Template File</button>
        <h1>Student Details Field</h1>
        <table class="table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="chkAllStuField"> Select All</th>
                    <th>Column Name</th>

                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <input type="checkbox" class="" checked disabled>
                        <input type="checkbox" class="" name="student_column[]" value="Academic Year" checked style="display:none;">
                    </td>
                    <td>Academic Year</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="student_column[]" value="Program">
                    </td>
                    <td>Program</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="student_column[]" value="Class">
                    </td>
                    <td>Class</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="student_column[]" value="Section">
                    </td>
                    <td>Section</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="" checked disabled>
                        <input type="checkbox" class="" name="student_column[]" value="Official Name" checked style="display:none;">
                    </td>
                    <td>Official Name</td>
                </tr>
                <!-- <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="student_column[]" value="Gender">
                    </td>
                    <td>Gender (M,F) (e.g. M)</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="student_column[]" value="Date of Birth">
                    </td>
                    <td>Date of Birth (YYYY-MM-DD) (e.g. 1989-05-03)</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="student_column[]" value="Username">
                    </td>
                    <td>Username</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="student_column[]" value="Can Login">
                    </td>
                    <td>Can Login (Y,N) (e.g. Y)</td>
                </tr> -->
                <?php

                /*
INSERT INTO `pupilsightPerson` (`pupilsightPersonID`, `title`, `surname`, `firstName`, `preferredName`, 
`officialName`, `nameInCharacters`, `gender`, `username`, `password`, `passwordStrong`, 
`passwordStrongSalt`, `passwordForceReset`, `status`, `canLogin`, `pupilsightRoleIDPrimary`, 
`pupilsightRoleIDAll`, `dob`, `email`, `emailAlternate`, `image_240`, `lastIPAddress`, `lastTimestamp`, 
`lastFailIPAddress`, `lastFailTimestamp`, `failCount`, `address2District`, `address2Country`, `phone1Type`, `phone1CountryCode`, `phone1`,
 `phone3Type`, `phone3CountryCode`, `phone3`, `phone2Type`, `phone2CountryCode`, `phone2`, `phone4Type`, `phone4CountryCode`, `phone4`, 
`website`, `languageFirst`, `languageSecond`, `languageThird`, `countryOfBirth`, `birthCertificateScan`, 
`ethnicity`, `citizenship1`, `citizenship1Passport`, `citizenship1PassportScan`, 
`citizenship2`, `citizenship2Passport`, `religion`, `nationalIDCardNumber`, `nationalIDCardScan`, 
`residencyStatus`, `visaExpiryDate`, `profession`, `employer`, `jobTitle`, `emergency1Name`, 
`emergency1Number1`, `emergency1Number2`, `emergency1Relationship`, 
`emergency2Name`, `emergency2Number1`, `emergency2Number2`, `emergency2Relationship`,
 `pupilsightHouseID`, `studentID`, `dateStart`, `dateEnd`, `pupilsightSchoolYearIDClassOf`,
  `lastSchool`, `nextSchool`, `departureReason`, `transport`, `transportNotes`, `calendarFeedPersonal`,
   `viewCalendarSchool`, `viewCalendarPersonal`, `viewCalendarSpaceBooking`, `pupilsightApplicationFormID`,
    `lockerNumber`, `vehicleRegistration`, `personalBackground`, `messengerLastBubble`, `privacy`, `dayType`, 
    `pupilsightThemeIDPersonal`, `pupilsighti18nIDPersonal`, `studentAgreements`, `googleAPIRefreshToken`,
     `receiveNotificationEmails`, `fields`, `signature`, `active`, `admission_no`, `roll_no`, `mother_tounge`, 
     `email_test`, `date_check`, `file_upload_test`, `test_upload2`, `image_upload`)
     */

                $fieldName = array("gender","dob","username","canLogin","email", "address", "address1District", "address1Country", "languageFirst", "languageSecond", "languageThird", "countryOfBirth", "ethnicity", "religion", "nationalIDCardNumber");
                $field = array("Gender","Date of Birth","Username","Can Login","Email", "Address", "District", "Country", "First Language", "Second Language", "Third Language", "Country of Birth", "Ethnicity", "Religion", "National ID Card Number", "Fee Category");
                $len = count($field);
                $i = 0;
                while ($i < $len) {
                    if ($customField->isActiveField($customFields, $fieldName[$i])) {
                        if ($customField->isNotTabField($customFields, $fieldName[$i])) {
                ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="stuField" name="student_column[]" value="<?= $field[$i] ?>">
                            </td>
                            <td><?= $field[$i] ?></td>
                        </tr>
                <?php
                    } }
                    $i++;
                }
                ?>
                <!--
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="student_column[]" value="Address">
                    </td>
                    <td>Address</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="student_column[]" value="District">
                    </td>
                    <td>District</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="student_column[]" value="Country">
                    </td>
                    <td>Country</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="student_column[]" value="First Language">
                    </td>
                    <td>First Language</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="student_column[]" value="Second Language">
                    </td>
                    <td>Second Language</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="student_column[]" value="Third Language">
                    </td>
                    <td>Third Language</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="student_column[]" value="Country of Birth">
                    </td>
                    <td>Country of Birth</td>
                </tr>
                
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="student_column[]" value="Ethnicity">
                    </td>
                    <td>Ethnicity</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="student_column[]" value="Religion">
                    </td>
                    <td>Religion</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="stuField" name="student_column[]" value="National ID Card Number">
                    </td>
                    <td>National ID Card Number</td>
                </tr>
                -->
                <?php
                if (!empty($stuCusFld)) {
                    foreach ($stuCusFld as $sc) {
                        if($sc['field_type'] != 'tab' && $sc['active'] == 'Y'){
                ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="stuField" name="student_column[]" value="<?php echo $sc['field_title']; ?>">
                            </td>
                            <td><?php echo $sc['field_title']; ?> (Custom Field)</td>
                        </tr>
                <?php } }
                } ?>

            </tbody>
        </table>

        <h1>Father Details Field</h1>
        <table class="table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="chkAllFatherField"> Select All</th>
                    <th>Column Name</th>

                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <input type="checkbox" class="fatherField" name="student_column[]" value="Father Official Name">
                    </td>
                    <td>Official Name</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="fatherField" name="student_column[]" value="Father Date of Birth">
                    </td>
                    <td>Date of Birth (YYYY-MM-DD) (e.g. 1989-05-03)</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="fatherField" name="student_column[]" value="Father Username">
                    </td>
                    <td>Username</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="fatherField" name="student_column[]" value="Father Can Login">
                    </td>
                    <td>Can Login (Y,N) (e.g. Y)</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="fatherField" name="student_column[]" value="Father Email">
                    </td>
                    <td>Email</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="fatherField" name="student_column[]" value="Father Mobile (Country Code)">
                    </td>
                    <td>Mobile (Country Code)</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="fatherField" name="student_column[]" value="Father Mobile No">
                    </td>
                    <td>Mobile No</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="fatherField" name="student_column[]" value="Father LandLine (Country Code)">
                    </td>
                    <td>LandLine (Country Code)</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="fatherField" name="student_column[]" value="Father Landline No">
                    </td>
                    <td>Landline No</td>
                </tr>
                <?php
                if (!empty($fatCusFld)) {
                    foreach ($fatCusFld as $fc) {
                        if($fc['field_type'] != 'tab' && $fc['active'] == 'Y'){
                ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="fatherField" name="student_column[]" value="<?php echo $fc['field_title']; ?>">
                            </td>
                            <td><?php echo $fc['field_title']; ?> (Custom Field)</td>
                        </tr>
                <?php } }
                } ?>
            </tbody>
        </table>

        <h1>Mother Details Field</h1>
        <table class="table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="chkAllMotherField"> Select All</th>
                    <th>Column Name</th>

                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <input type="checkbox" class="motherField" name="student_column[]" value="Mother Official Name">
                    </td>
                    <td>Official Name</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="motherField" name="student_column[]" value="Mother Date of Birth">
                    </td>
                    <td>Date of Birth (YYYY-MM-DD) (e.g. 1989-05-03)</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="motherField" name="student_column[]" value="Mother Username">
                    </td>
                    <td>Username</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="motherField" name="student_column[]" value="Mother Can Login">
                    </td>
                    <td>Can Login (Y,N) (e.g. Y)</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="motherField" name="student_column[]" value="Mother Email">
                    </td>
                    <td>Email</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="motherField" name="student_column[]" value="Mother Mobile (Country Code)">
                    </td>
                    <td>Mobile (Country Code)</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="motherField" name="student_column[]" value="Mother Mobile No">
                    </td>
                    <td>Mobile No</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="motherField" name="student_column[]" value="Mother LandLine (Country Code)">
                    </td>
                    <td>LandLine (Country Code)</td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" class="motherField" name="student_column[]" value="Mother Landline No">
                    </td>
                    <td>Landline No</td>
                </tr>
                <?php
                if (!empty($motCusFld)) {
                    foreach ($motCusFld as $mc) {
                        if($mc['field_type'] != 'tab' && $mc['active'] == 'Y'){
                ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="motherField" name="student_column[]" value="<?php echo $mc['field_title']; ?>">
                            </td>
                            <td><?php echo $mc['field_title']; ?> (Custom Field)</td>
                        </tr>
                <?php } }
                } ?>
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
    //     $dir = "C:/xampp/htdocs/pupilsight/public/studentImportFile/"; // trailing slash is important
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

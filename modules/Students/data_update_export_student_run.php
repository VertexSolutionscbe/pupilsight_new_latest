<?php
/*
Pupilsight, Flexible & Open School System

*/
use Pupilsight\Forms\Form;
use Pupilsight\Data\ImportType;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Helper\HelperGateway;
use Pupilsight\Domain\Students\StudentGateway;

// Increase max execution time, as this stuff gets big
ini_set('max_execution_time', 7200);
ini_set('memory_limit','1024M');
set_time_limit(1200);

$_POST['address'] = '/modules/Students/import_student_manage.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_POST['address'];

if (isActionAccessible($guid, $connection2, "/modules/Students/export_student_run.php")==false) {
    // Access denied
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $HelperGateway = $container->get(HelperGateway::class);
    $StudentGateway = $container->get(StudentGateway::class);
    $criteria = $StudentGateway->newQueryCriteria()
                    ->pageSize('1000');
    $page->breadcrumbs->add(__('Export Student Import File'));
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    if(!empty($_POST['student_column']) && !empty($_POST['student_id'])){
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';
        // $st = array();
        
        // die();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="StudentImport.csv"');
        $columndata = implode(',',$_POST['student_column']);
        $data = array($columndata);
        
        $fp = fopen('php://output', 'wb');
        foreach ( $data as $line ) {
            $val = explode(",", $line);
            fputcsv($fp, $val);
        }
         foreach ( $_POST[student_id] as $linenew ) {
            $valnew = explode(",", $linenew);
            fputcsv($fp, $valnew);
        }
        fclose($fp);
        die();
    }

    $sql = 'SELECT  field_name, field_title, modules FROM custom_field WHERE table_name = "pupilsightPerson" ';
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
            if(in_array('student', $modules)){
                $stuCusFld[] = $cf;
            }
            if(in_array('father', $modules)){
                $fatCusFld[] = $cf;
            }
            if(in_array('mother', $modules)){
                $motCusFld[] = $cf;
            }
        }
    }
    $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();

    $program = array();
    $program2 = array();
    $program1 = array('' => 'Select Program');
    foreach ($rowdataprog as $dt) {
        $program2[$dt['pupilsightProgramID']] = $dt['name'];
    }
    $program = $program1 + $program2;

    if($_POST){    
        $pupilsightProgramID = $_POST['pupilsightProgramID'];
        $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];  
        
        if(!empty($pupilsightProgramID)){
        $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID, $pupilsightSchoolYearID);
        $sections =  $HelperGateway->getMultipleSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID);
        }
        
    } else {      
        $classes = array('' => 'Select Class');
        $sections = array('' => 'Select Section');
        $pupilsightProgramID = '';
        $pupilsightYearGroupID =  '';
        $pupilsightRollGroupID = '';
        
    }

    echo '<input type="hidden" id="pupilsightSchoolYearID" value="'.$_SESSION[$guid]['pupilsightSchoolYearID'].'">';
    $searchform = Form::create('searchForm', '');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();
   
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->setId('getMultiClassByProg')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder('Select Program');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'))->addClass('dte');
     $col->addSelect('pupilsightYearGroupID')->setId('showMultiClassByProg')->fromArray($classes)->selected($pupilsightYearGroupID)->required()->placeholder('Select Class')->selectMultiple();

     
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'))->addClass('dte');
    $col->addSelect('pupilsightRollGroupID')->setID('showMultiSecByProgCls')->fromArray($sections)->selected($pupilsightRollGroupID)->placeholder('Select Section')->selectMultiple();

    
    $col = $row->addColumn()->setClass('newdes');
    
    $col->addLabel('', __(''));
    $col->addContent('<button id="submitInvoice"  class=" btn btn-primary">Search</button>');
    echo $searchform->getOutput();
    $students = $StudentGateway->getStudentData($criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID);
    // echo '<pre>';
    // print_r($student);
    // echo '</pre>';

?>
<form method="post" action="" id="studentexportform">
    <button type="button" class="thickbox btn btn-primary" id="generatestudenttemplate">Generate Template File</button>
    <h1>Student's</h1> 
    <table class="table">
        <thead>
            <tr>
                <th><input type="checkbox" id="chkAllFatherField" > Select All</th>
                <th>Student Name</th>
                <th>Class</th>
                <th>Section</th>
                
            </tr>
        </thead>
        <tbody>
            <?php foreach($students as $stu){?>
            <tr>
                <td>
                    <input type="checkbox" class="fatherField" name="student_id[]" value="<?php echo $stu['stuid'].','.$stu['student_name'].','.$pupilsightSchoolYearName.','.$stu['progname'].','.$stu['classname'].','.$stu['secname'];?>">
                    <input type="checkbox" class="" name="student_name[]" value="<?php echo $stu['student_name'];?>" checked style="display:none;">
                </td>
                <td><?php echo $stu['student_name'];?></td>
                <td><?php echo $stu['classname'];?></td>
                <td><?php echo $stu['secname'];?></td>
            </tr>
            <?php }?>
        </tbody>
    </table>
    <h1>Student Details Field</h1> 
    <table class="table">
        <thead>
            <tr>
                <th><input type="checkbox" id="chkAllStuField" > Select All</th>
                <th>Column Name</th>
                
            </tr>
        </thead>
        <tbody>
        <input type="checkbox" class="" name="student_column[]" value="Student Id" checked style="display:none;">
        <input type="checkbox" class="" name="student_column[]" value="Student Name" checked style="display:none;">
        <input type="checkbox" class="" name="student_column[]" value="Academic Year" checked style="display:none;">
        <input type="checkbox" class="stuField" name="student_column[]" value="Program" checked style="display:none;">
        <input type="checkbox" class="stuField" name="student_column[]" value="Class" checked style="display:none;">
        <input type="checkbox" class="stuField" name="student_column[]" value="Section"checked style="display:none;" >
            <tr>
                <td>
                    <input type="checkbox" class="" name="student_column[]" value="Official Name" >
                </td>
                <td>Official Name</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="student_column[]" value="Gender">
                </td>
                <td>Gender</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="student_column[]" value="Date of Birth">
                </td>
                <td>Date of Birth</td>
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
                <td>Can Login</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="student_column[]" value="Email">
                </td>
                <td>Email</td>
            </tr>
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
            <?php
            if(!empty($stuCusFld)){
                foreach($stuCusFld as $sc){
            ?>
            <tr>
                <td>
                    <input type="checkbox" class="stuField" name="student_column[]" value="<?php echo $sc['field_title'];?>">
                </td>
                <td><?php echo $sc['field_title'];?>  (Custom Field)</td>
            </tr>
            <?php } }?>

        </tbody>
    </table>


    <h1>Father Details Field</h1>
    <table class="table">
        <thead>
            <tr>
                <th><input type="checkbox" id="chkAllExpFatherField" > Select All</th>
                <th>Column Name</th>
                
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <input type="checkbox" class="fatherExpField" name="student_column[]" value="Father Official Name">
                </td>
                <td>Official Name</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="fatherExpField" name="student_column[]" value="Father Date of Birth">
                </td>
                <td>Date of Birth</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="fatherExpField" name="student_column[]" value="Father Username">
                </td>
                <td>Username</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="fatherExpField" name="student_column[]" value="Father Can Login">
                </td>
                <td>Can Login</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="fatherExpField" name="student_column[]" value="Father Email">
                </td>
                <td>Email</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="fatherExpField" name="student_column[]" value="Father Mobile (Country Code)">
                </td>
                <td>Mobile (Country Code)</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="fatherExpField" name="student_column[]" value="Father Mobile No">
                </td>
                <td>Mobile No</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="fatherExpField" name="student_column[]" value="Father LandLine (Country Code)">
                </td>
                <td>LandLine (Country Code)</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="fatherExpField" name="student_column[]" value="Father Landline No">
                </td>
                <td>Landline No</td>
            </tr>
            <?php
            if(!empty($fatCusFld)){
                foreach($fatCusFld as $fc){
            ?>
            <tr>
                <td>
                    <input type="checkbox" class="fatherExpField" name="student_column[]" value="<?php echo $fc['field_title'];?>">
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
                    <input type="checkbox" class="motherField" name="student_column[]" value="Mother Official Name">
                </td>
                <td>Official Name</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" class="motherField" name="student_column[]" value="Mother Date of Birth">
                </td>
                <td>Date of Birth</td>
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
                <td>Can Login</td>
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
            if(!empty($motCusFld)){
                foreach($motCusFld as $mc){
            ?>
            <tr>
                <td>
                    <input type="checkbox" class="motherField" name="student_column[]" value="<?php echo $mc['field_title'];?>">
                </td>
                <td><?php echo $mc['field_title'];?>  (Custom Field)</td>
            </tr>
            <?php } }?>
        </tbody>
    </table>
   
   </form> 

   <style>
        select[multiple] {
            min-height: 35px !important;
        }
   </style>
   <script>
        $('#showMultiClassByProg').selectize({
            plugins: ['remove_button'],
        });

        $('#showMultiSecByProgCls').selectize({
            plugins: ['remove_button'],
        });
        
        
        $(document).on('change', '#getMultiClassByProg', function () {
            var id = $(this).val();
            var type = 'getClass';
            $('#showMultiClassByProg').selectize()[0].selectize.destroy();
            $("#getFeeStructureByProgClass").html('');
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: id, type: type },
                async: true,
                success: function (response) {
                    $("#showMultiClassByProg").html('');
                    $("#showMultiClassByProg").html(response);
                    $("#showMultiClassByProg").parent().children('.LV_validation_message').remove();
                    $('#showMultiClassByProg').selectize({
                        plugins: ['remove_button'],
                    });
                    
                }
            });
        });

        
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

        

        $(document).on('change', '#chkAllExpFatherField', function() {
            if ($(this).is(':checked')) {
                $(".fatherExpField").prop("checked", true);
            } else {
                $(".fatherExpField").prop("checked", false);
            }
        });

        $(document).on('change', '.fatherExpField', function() {
            if ($(this).is(':checked')) {
                //$(".chkChild"+id).prop("checked", true);
            } else {
                $("#chkAllExpFatherField").prop("checked", false);
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

        $('#generatestudenttemplate').click(function(e){
            if($('#getMultiClassByProg option:selected').val() == '' || $('#showMultiClassByProg option:selected').val() == ''){
                alert('Please Select Program and Class and click on search for studnent data');
            } else {
                if($('input[name="student_id[]"]:checked').length > 0){
                    $('#studentexportform').submit();
                } else {
                    alert("Please Select student's");
                }
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

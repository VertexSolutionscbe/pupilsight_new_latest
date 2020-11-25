<?php
 
 
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/
//echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>';

use Pupilsight\Services\Format;

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Admission\AdmissionGateway;
use Pupilsight\Domain\Helper\HelperGateway;

// Module includes
require_once __DIR__ . '/moduleFunctions.php';


if (isActionAccessible($guid, $connection2, '/modules/Students/loginAccount.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    
    $page->breadcrumbs->add(__('Student Update'));

 
     if (isset($_GET['return'])) {
         returnProcess($guid, $_GET['return'], null, null);
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

    $sqls = 'SELECT id, series_name FROM fn_fee_series WHERE type = "Admission" ';
    $results = $connection2->query($sqls);
    $seriesData = $results->fetchAll();

    $series = array();
    $series2 = array();
    $series1 = array('' => 'Select Series');
    foreach ($seriesData as $dt) {
        $series2[$dt['id']] = $dt['series_name'];
    }
    $series = $series1 + $series2;

    $classes = '';
    $endDate = '';
    $stDate = '';
    $enDate = '';

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

    $HelperGateway = $container->get(HelperGateway::class);
    if($_POST){
        //$series_id = $_POST['series_id'];
        $pupilsightProgramID = $_POST['pupilsightProgramID'];
        $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
        //$type = $_POST['type'];

        $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID);
        
        if(!empty($pupilsightProgramID) && !empty($pupilsightYearGroupID)){ 
        $sqle = "SELECT a.officialName, a.pupilsightPersonID, a.admission_no, a.username as stuUsername, a.passwordStrong as stuPassword, d.name AS class,c.name as program ,f.name as academic, d.pupilsightYearGroupID,c.pupilsightProgramID ,f.pupilsightSchoolYearID,f.pupilsightSchoolYearID, parent1.pupilsightPersonID as fatherId, parent1.officialName as fatherName, parent1.username as fatherUsername, parent1.passwordStrong as fatherPassword, parent2.pupilsightPersonID as motherId, parent2.officialName as motherName, parent2.username as motherUsername, parent2.passwordStrong as motherPassword FROM pupilsightPerson AS a 
        LEFT JOIN pupilsightStudentEnrolment AS b ON a.pupilsightPersonID=b.pupilsightPersonID 
        LEFT JOIN pupilsightProgram AS c ON b.pupilsightProgramID=c.pupilsightProgramID 
        LEFT JOIN pupilsightYearGroup AS d ON b.pupilsightYearGroupID=d.pupilsightYearGroupID 
        LEFT JOIN pupilsightRollGroup AS e ON b.pupilsightRollGroupID=e.pupilsightRollGroupID 
        LEFT JOIN pupilsightSchoolYear AS f ON b.pupilsightSchoolYearID=f.pupilsightSchoolYearID

        LEFT JOIN pupilsightFamilyChild AS child ON child.pupilsightPersonID=a.pupilsightPersonID 
        LEFT JOIN pupilsightFamilyAdult AS adult1 ON adult1.pupilsightFamilyID=child.pupilsightFamilyID AND adult1.contactPriority=1 
        LEFT JOIN pupilsightPerson as parent1 ON parent1.pupilsightPersonID=adult1.pupilsightPersonID AND parent1.status='Full' 
        LEFT JOIN pupilsightFamilyAdult as adult2 ON adult2.pupilsightFamilyID=child.pupilsightFamilyID AND adult2.contactPriority=2 
        LEFT JOIN pupilsightPerson as parent2 ON parent2.pupilsightPersonID=adult2.pupilsightPersonID AND parent2.status='Full' 
        
        WHERE  b.pupilsightProgramID = " . $pupilsightProgramID . " AND b.pupilsightSchoolYearID = " . $pupilsightSchoolYearID . " AND b.pupilsightYearGroupID = " . $pupilsightYearGroupID . " ORDER BY d.pupilsightYearGroupID ASC ";
         //echo $sqle;
        // die();
        $resulte = $connection2->query($sqle);
        $studentData = $resulte->fetchAll();
        }
       
    } 

    // echo '<pre>';
    // print_r($studentData);
    // echo '</pre>';
    //die();
    
     echo '<h2>';
     echo __('Student Message History');
     echo '</h2>';
     
     $types = array('' => 'Select Type', 'ASC' => 'Ascending', 'DESC' => 'Descending');
     $form = Form::create('filter', '');

            $form->setClass('noIntBorder fullWidth');
            $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/student_view.php');
            $row = $form->addRow();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightProgramID', __('Program'));
            $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->placeholder('Select Program')->required();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightYearGroupID', __('Class'));
            $col->addSelect('pupilsightYearGroupID')->fromArray($classes)->selected($pupilsightYearGroupID)->placeholder('Select Class')->required();


            // $col = $row->addColumn()->setClass('newdes');
            // $col->addLabel('series_id', __('Series'));
            // $col->addSelect('series_id')->fromArray($series)->selected($series_id)->placeholder('Select Series')->required();


            // $col = $row->addColumn()->setClass('newdes');
            // $col->addLabel('type', __('Type'))->addClass('dte');
            // $col->addSelect('type')->fromArray($types)->selected($type)->required();


            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('', __(''));
            $col->addSearchSubmit($pupilsight->session, __('Clear Search'));



            echo $form->getOutput();
    if($_POST){
    ?>
    <form method="post" id="createAccountForm" action="index.php?q=/modules/Students/loginAccountProcess.php">
    <a class="btn btn-primary" style="float:right;" id="createAccount">Create Account</a>
    <a  style="display:none;" class="thickbox" id="showPasswordPage" href="fullscreen.php?q=/modules/Students/loginPassword.php">Create Account</a>
    <input type='hidden' name="password" id="addPassword" value="">
    <div style="overflow-x:auto; width:100%;" >
    <table class="table" id="historyTable">
        <thead>
            <tr>
                <th colspan="4" style="text-align:center;">Student</th>
                <th colspan="4" style="text-align:center;">Father</th>
                <th colspan="4" style="text-align:center;">Mother</th>
                <!-- <th colspan="4" style="text-align:center;">Guardian</th> -->
            </tr>
            <tr>
                <th><input type="checkbox" class="chkAll"></th>
                <th>Name</th>
                <th>Login Id</th>
                <th>Status</th>

                <th><input type="checkbox" class="chkAllFather"></th>
                <th>Name</th>
                <th>Login Id</th>
                <th>Status</th>

                <th><input type="checkbox" class="chkAllMother"></th>
                <th>Name</th>
                <th>Login Id</th>
                <th>Status</th>

                <!-- <th><input type="checkbox"></th>
                <th>Name</th>
                <th>Login Id</th>
                <th>Status</th> -->
            </tr>
        </thead>
        <tbody>
            <?php 
            if(!empty($studentData)) { 
                $i = 1;
                foreach($studentData as $estd){ 
                    if(!empty($estd['stuPassword'])){
                        $chkclsStu = 'greenicon';
                    } else {
                        $chkclsStu = 'greyicon';
                    }

                    if(!empty($estd['fatherPassword'])){
                        $chkclsFat = 'greenicon';
                    } else {
                        $chkclsFat = 'greyicon';
                    }

                    if(!empty($estd['motherPassword'])){
                        $chkclsMot = 'greenicon';
                    } else {
                        $chkclsMot = 'greyicon';
                    }

                ?>
                
                    <tr>
                        <td><input type="checkbox" name="personId[]" class="chkclick chkChild" value="<?php echo $estd['pupilsightPersonID']; ?>"></td>
                        <td><?php echo $estd['officialName']; ?></td>
                        <td><?php echo $estd['stuUsername']; ?></td>
                        <td><i class="mdi mdi-checkbox-marked-circle mdi-24px <?php echo $chkclsStu;?> "></i></td>

                        <td><input type="checkbox" name="personId[]" class="chkclick chkFather" value="<?php echo $estd['fatherId']; ?>"></td>
                        <td><?php echo $estd['fatherName']; ?></td>
                        <td><?php echo $estd['fatherUsername']; ?></td>
                        <td><i class="mdi mdi-checkbox-marked-circle mdi-24px <?php echo $chkclsFat;?> "></i></td>

                        <td><input type="checkbox" name="personId[]" class="chkclick chkMother" value="<?php echo $estd['motherId']; ?>"></td>
                        <td><?php echo $estd['motherName']; ?></td>
                        <td><?php echo $estd['motherUsername']; ?></td>
                        <td><i class="mdi mdi-checkbox-marked-circle mdi-24px <?php echo $chkclsMot;?> "></i></td>
                        
                    </tr>
            <?php  $i++; } } else { ?> 
                <tr>
                    <td colspan="7">No Message History</td>
                </tr>
            <?php }  ?>
        </tbody>
        </div>

    </table>
    </form>
<?php   
} }
?>

<script>
    $(function(){
        $("#historyTable").dataTable();
    })
    $("#start_date").datepicker({
        //minDate: 0,
        onClose: function (selectedDate) {
            $("#end_date").datepicker("option", "minDate", selectedDate);
        }
    });

    $(document).on('change', '.chkAllFather', function () {
        if ($(this).is(':checked')) {
            $(".chkFather").prop("checked", true);
        } else {
            $(".chkFather").prop("checked", false);
        }
    });

    $(document).on('change', '.chkFather', function () {
        if ($(this).is(':checked')) {
            //$(".chkChild"+id).prop("checked", true);
        } else {
            $(".chkAllFather").prop("checked", false);
        }
    });

    $(document).on('change', '.chkAllMother', function () {
        if ($(this).is(':checked')) {
            $(".chkMother").prop("checked", true);
        } else {
            $(".chkMother").prop("checked", false);
        }
    });

    $(document).on('change', '.chkMother', function () {
        if ($(this).is(':checked')) {
            //$(".chkChild"+id).prop("checked", true);
        } else {
            $(".chkAllMother").prop("checked", false);
        }
    });

    $(document).on('click', '#createAccount', function () {
         
        var stuids = [];
        $.each($(".chkclick:checked"), function () {
            stuids.push($(this).val());
        });
        var stuid = stuids.join(",");
        if (stuid) {
            $("#showPasswordPage").click();
        } else {
            alert('you Have to Select User to Create Account');
        }
    });

    $(document).on('click', '#donePassword', function () {
        var pass = $("#pass").val();
        $("#addPassword").val(pass);
        $("#createAccountForm").submit();
    });

    $(document).on('click', '#closePassword', function () {
        $("#TB_overlay").remove();
        $("#TB_window").remove();
    });
</script>
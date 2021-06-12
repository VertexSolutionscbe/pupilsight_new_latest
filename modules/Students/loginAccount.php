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
    
    $page->breadcrumbs->add(__('Login Accounts'));

 
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
    $pupilsightProgramID = '';
    $pupilsightYearGroupID = '';
    $enDate = '';

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

    $HelperGateway = $container->get(HelperGateway::class);
    if($_POST){
        //$series_id = $_POST['series_id'];
        $pupilsightProgramID = $_POST['pupilsightProgramID'];
        $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
        //$type = $_POST['type'];

        $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID, $pupilsightSchoolYearID);
        
        if(!empty($pupilsightProgramID) && !empty($pupilsightYearGroupID)){ 
            $classIds = implode(',', $pupilsightYearGroupID);
            $sqle = "SELECT a.officialName, a.pupilsightPersonID, a.admission_no, a.username as stuUsername, a.passwordStrong as stuPassword, a.canLogin, d.name AS class,c.name as program ,f.name as academic, d.pupilsightYearGroupID,c.pupilsightProgramID ,f.pupilsightSchoolYearID,f.pupilsightSchoolYearID, parent1.pupilsightPersonID as fatherId, parent1.officialName as fatherName, parent1.username as fatherUsername, parent1.passwordStrong as fatherPassword, parent1.canLogin as fatherCanLogin, parent2.pupilsightPersonID as motherId, parent2.officialName as motherName, parent2.username as motherUsername, parent2.passwordStrong as motherPassword , parent2.canLogin as motherCanLogin FROM pupilsightPerson AS a 
            LEFT JOIN pupilsightStudentEnrolment AS b ON a.pupilsightPersonID=b.pupilsightPersonID 
            LEFT JOIN pupilsightProgram AS c ON b.pupilsightProgramID=c.pupilsightProgramID 
            LEFT JOIN pupilsightYearGroup AS d ON b.pupilsightYearGroupID=d.pupilsightYearGroupID 
            LEFT JOIN pupilsightRollGroup AS e ON b.pupilsightRollGroupID=e.pupilsightRollGroupID 
            LEFT JOIN pupilsightSchoolYear AS f ON b.pupilsightSchoolYearID=f.pupilsightSchoolYearID

            LEFT JOIN pupilsightFamilyChild AS child ON child.pupilsightPersonID=a.pupilsightPersonID 
            LEFT JOIN pupilsightFamilyRelationship AS adult1 ON adult1.pupilsightFamilyID=child.pupilsightFamilyID AND adult1.relationship= 'Father'
            LEFT JOIN pupilsightPerson as parent1 ON parent1.pupilsightPersonID=adult1.pupilsightPersonID1 AND parent1.status='Full' 
            LEFT JOIN pupilsightFamilyRelationship as adult2 ON adult2.pupilsightFamilyID=child.pupilsightFamilyID AND adult2.relationship= 'Mother'
            LEFT JOIN pupilsightPerson as parent2 ON parent2.pupilsightPersonID=adult2.pupilsightPersonID1 AND parent2.status='Full' 

            
            -- LEFT JOIN pupilsightFamilyAdult AS adult1 ON adult1.pupilsightFamilyID=child.pupilsightFamilyID AND adult1.contactPriority=1 
            -- LEFT JOIN pupilsightPerson as parent1 ON parent1.pupilsightPersonID=adult1.pupilsightPersonID AND parent1.status='Full' 
            -- LEFT JOIN pupilsightFamilyAdult as adult2 ON adult2.pupilsightFamilyID=child.pupilsightFamilyID AND adult2.contactPriority=2 
            -- LEFT JOIN pupilsightPerson as parent2 ON parent2.pupilsightPersonID=adult2.pupilsightPersonID AND parent2.status='Full' 
            
            WHERE  a.is_delete = '0' AND b.pupilsightProgramID = " . $pupilsightProgramID . " AND b.pupilsightSchoolYearID = " . $pupilsightSchoolYearID . " AND b.pupilsightYearGroupID IN (" . $classIds . ") GROUP BY a.pupilsightPersonID ORDER BY a.pupilsightPersonID DESC ";
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
     echo __('Login Accounts');
     echo '</h2>';
     
     $types = array('' => 'Select Type', 'ASC' => 'Ascending', 'DESC' => 'Descending');
     $form = Form::create('filter', '');

            $form->setClass('noIntBorder fullWidth');
            $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/student_view.php');
            $row = $form->addRow();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightProgramID', __('Program'));
            $col->addSelect('pupilsightProgramID')->setID('getMultiClassByProgCamp')->fromArray($program)->selected($pupilsightProgramID)->placeholder('Select Program')->required();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightYearGroupID', __('Class'))->addClass('dte');
            $col->addSelect('pupilsightYearGroupID')->setID('showMultiClassByProg')->fromArray($classes)->selected($pupilsightYearGroupID)->placeholder('Select Class')->required()->selectMultiple();


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
    ?>
    <a class="btn btn-primary thickbox" style="float:right;margin-left: 10px;" href="fullscreen.php?q=/modules/Students/sms_email_content.php&type=Sms">SMS Content</a>
    <a class="btn btn-primary thickbox" style="float:right;margin-left: 10px;" href="fullscreen.php?q=/modules/Students/sms_email_content.php&type=Email">Email Content</a>

   <?php if($_POST){
    ?>
    <form method="post" id="createAccountForm" action="index.php?q=/modules/Students/loginAccountProcess.php">
    
    <a class="btn btn-primary" style="float:right;" id="createAccount">Create Account</a>
    <a  style="display:none;" class="thickbox" id="showPasswordPage" href="fullscreen.php?q=/modules/Students/loginPassword.php">Create Account</a>
    <a class="btn btn-primary" style="float:right;margin-right:10px;" id="deleteAccount">Delete Account</a>

    <a class="btn btn-primary" style="float:right;margin-right:10px;" id="enableAccount">Enable Login</a>

    <a class="btn btn-primary" style="float:right;margin-right:10px;" id="disableAccount">Disable Login</a>

    <input type='hidden' name="password" id="addPassword" value="">
    <textarea id="addContent" name="content" style="display:none;"></textarea>
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
                <th class="no-sort"><input type="checkbox" class="chkAll"></th>
                <th>Name</th>
                <th>Login Id</th>
                <th>Status</th>

                <th class="no-sort"><input type="checkbox" class="chkAllFather"></th>
                <th>Name</th>
                <th>Login Id</th>
                <th>Status</th>

                <th class="no-sort"><input type="checkbox" class="chkAllMother"></th>
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
                    if(!empty($estd['stuPassword'])  && $estd['canLogin'] == 'Y'){
                        $chkclsStu = 'greenicon';
                    } else if (!empty($estd['stuPassword']) && $estd['canLogin'] == 'N') {
                        $chkclsStu = 'orangeicon';
                    } else {
                        $chkclsStu = 'greyicon';
                    }

                   
                    if(!empty($estd['fatherPassword'])  && $estd['fatherCanLogin'] == 'Y'){
                        $chkclsFat = 'greenicon';
                    } else if (!empty($estd['fatherPassword']) && $estd['fatherCanLogin'] == 'N') {
                        $chkclsFat = 'orangeicon';
                    } else {
                        $chkclsFat = 'greyicon';
                    }

                    if(!empty($estd['motherPassword'])  && $estd['motherCanLogin'] == 'Y'){
                        $chkclsMot = 'greenicon';
                    } else if (!empty($estd['motherPassword']) && $estd['motherCanLogin'] == 'N') {
                        $chkclsMot = 'orangeicon';
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

<style>
.orangeicon {
    color: orange;
    font-size: 25px;
}
</style>

<script>
    $(function(){
        $("#historyTable").dataTable({
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "columnDefs": [ {
                "targets"  : 'no-sort',
                "orderable": false,
            }],
        });
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
        var content = $("#mailcontent").val();
        var favorite = [];
        $.each($("input[name='type[]']:checked"), function(){
            favorite.push($(this).val());
        });
        var types = favorite.join(",");
        $("#addPassword").val(pass);
        $("#addContent").val(types);
        $("#preloader").show();
        $("#createAccountForm").submit();
    });

    $(document).on('click', '#closePassword', function () {
        $("#TB_overlay").remove();
        $("#TB_window").remove();
    });

    $(document).ready(function () {
      	$('#showMultiClassByProg').selectize({
      		plugins: ['remove_button'],
      	});
    });

    $(document).on('change', '#getMultiClassByProgCamp', function () {
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

    $(document).on('click', '#deleteAccount', function () {
         
         var stuids = [];
         $.each($(".chkclick:checked"), function () {
             stuids.push($(this).val());
         });
         var stuid = stuids.join(",");
         if (stuid) {
            if (confirm("Do you want to Delete Account?")) {
                var val = stuid;
                var type = 'deleteUserLoginAccount';
                if (val != '') {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: { val: val, type: type },
                        async: true,
                        success: function (response) {
                            location.reload();
                        }
                    });
                }
            }
         } else {
             alert('you Have to Select User to Delete Account');
         }
     });

    $(document).on('click', '#enableAccount', function() {
        var stuids = [];
        $.each($(".chkclick:checked"), function() {
            stuids.push($(this).val());
        });
        var stuid = stuids.join(",");
        if (stuid) {
            if (confirm("Do you want to Enable Account?")) {
                var val = stuid;
                var type = 'enableUserLoginAccount';
                if (val != '') {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: {
                            val: val,
                            type: type
                        },
                        async: true,
                        success: function(response) {
                            location.reload();
                        }
                    });
                }
            }
        } else {
            alert('you Have to Select User to Enable Account');
        }
    });

    $(document).on('click', '#disableAccount', function() {
        var stuids = [];
        $.each($(".chkclick:checked"), function() {
            stuids.push($(this).val());
        });
        var stuid = stuids.join(",");
        if (stuid) {
            if (confirm("Do you want to Disable Account?")) {
                var val = stuid;
                var type = 'disableUserLoginAccount';
                if (val != '') {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: {
                            val: val,
                            type: type
                        },
                        async: true,
                        success: function(response) {
                            location.reload();
                        }
                    });
                }
            }
        } else {
            alert('you Have to Select User to Disable Account');
        }
    });
</script>
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
use Pupilsight\Domain\Staff\StaffGateway;

// Module includes
require_once __DIR__ . '/moduleFunctions.php';


if (isActionAccessible($guid, $connection2, '/modules/Staff/loginAccount.php') == false) {
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

    $sqld = 'SELECT pupilsightDepartmentID, name FROM pupilsightDepartment ';
    $resultd = $connection2->query($sqld);
    $rowdatadept = $resultd->fetchAll();
    $subjects = array('' => __('Select Subject'));
    $subject2 = array();
    // $subject1=array(''=>'Select Subjects');
    foreach ($rowdatadept as $dt) {
        $subject2[$dt['pupilsightDepartmentID']] = $dt['name'];
    }
    $subjects +=  $subject2;



    if ($_POST) {
        $pupilsightProgramID = $_POST['pupilsightProgramID'];
        $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
        $search = $_POST['search'];
    } else {
        $pupilsightProgramID = '';
        $pupilsightDepartmentID = '';
        $search = '';
    }


    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];


    $staffGateway = $container->get(StaffGateway::class);

    // QUERY
    $criteria = $staffGateway->newQueryCriteria()
        ->searchBy($staffGateway->getSearchableColumns(), $search)
        ->filterBy('all', $allStaff)
        ->pageSize(5000)
        ->sortBy(['surname', 'preferredName'])
        ->fromPOST();

    $staff = $staffGateway->queryAllStaff($criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightDepartmentID);

    // echo '<pre>';
    // print_r($studentData);
    // echo '</pre>';
    //die();

    echo '<h2>';
    echo __('Login Accounts');
    echo '</h2>';

    $form = Form::create('filter', '');

    $form->setClass('noIntBorder fullWidth');
    $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/student_view.php');
    $row = $form->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->setID('getMultiClassByProgCamp')->fromArray($program)->selected($pupilsightProgramID)->placeholder('Select Program');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightDepartmentID', __('Subjects'));
    $col->addSelect('pupilsightDepartmentID')->fromArray($subjects)->selected($pupilsightDepartmentID)->placeholder();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('search', __('Search By Name, Email, Type, Phone'));
    $col->addTextField('search')->setValue($search)->maxLength(20);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __(''));
    $col->addSearchSubmit($pupilsight->session, __('Clear Search'));



    echo $form->getOutput();
?>
    <a class="btn btn-primary thickbox" style="float:right;margin-left: 10px;" href="fullscreen.php?q=/modules/Staff/sms_email_content.php&type=Sms">SMS Content</a>
    <a class="btn btn-primary thickbox" style="float:right;margin-left: 10px;" href="fullscreen.php?q=/modules/Staff/sms_email_content.php&type=Email">Email Content</a>


    <form method="post" id="createAccountForm" action="index.php?q=/modules/Staff/loginAccountProcess.php">

        <a class="btn btn-primary" style="float:right;" id="createAccount">Create Account</a>
        <a style="display:none;" class="thickbox" id="showPasswordPage" href="fullscreen.php?q=/modules/Staff/loginPassword.php">Create Account</a>
        <a class="btn btn-primary" style="float:right;margin-right:10px;" id="deleteAccount">Delete Account</a>

        <a class="btn btn-primary" style="float:right;margin-right:10px;" id="enableAccount">Enable Login</a>

        <a class="btn btn-primary" style="float:right;margin-right:10px;" id="disableAccount">Disable Login</a>
        <input type='hidden' name="password" id="addPassword" value="">
        <textarea id="addContent" name="content" style="display:none;"></textarea>
        <div style="overflow-x:auto; width:100%;">
            <table class="table" id="historyTable">
                <thead>
                    <tr>
                        <th><input type="checkbox" class="chkAll"></th>
                        <th>Name</th>
                        <th>Login Id</th>
                        <th>Status</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($staff)) {
                        $i = 1;
                        foreach ($staff as $estd) {
                            if (!empty($estd['stfPassword']) && $estd['canLogin'] == 'Y') {
                                $chkclsStu = 'greenicon';
                            } else if (!empty($estd['stfPassword']) && $estd['canLogin'] == 'N') {
                                $chkclsStu = 'orangeicon';
                            } else {
                                $chkclsStu = 'greyicon';
                            }

                    ?>

                            <tr>
                                <td><input type="checkbox" name="personId[]" class="chkclick chkChild" value="<?php echo $estd['pupilsightPersonID']; ?>"></td>
                                <td><?php echo $estd['officialName']; ?></td>
                                <td><?php echo $estd['stfUsername']; ?></td>
                                <td><i class="mdi mdi-checkbox-marked-circle mdi-24px <?php echo $chkclsStu; ?> "></i></td>

                            </tr>
                        <?php $i++;
                        }
                    } else { ?>
                        <tr>
                            <td colspan="7">No Message History</td>
                        </tr>
                    <?php }  ?>
                </tbody>
        </div>

        </table>
    </form>
<?php
}
?>
<style>
.orangeicon {
    color: orange;
    font-size: 25px;
}
</style>
<script>
    $(function() {
        $('#historyTable').DataTable({
            "pageLength": 25,
            "lengthMenu": [
                [10, 25, 50, 250, -1],
                [10, 25, 50, 250, "All"]
            ],
            "sDom": '<"top"lfpi>rt<"bottom"ifp><"clear">'
        });
        //$("#historyTable").dataTable();
    })
    $("#start_date").datepicker({
        //minDate: 0,
        onClose: function(selectedDate) {
            $("#end_date").datepicker("option", "minDate", selectedDate);
        }
    });

    $(document).on('change', '.chkAllFather', function() {
        if ($(this).is(':checked')) {
            $(".chkFather").prop("checked", true);
        } else {
            $(".chkFather").prop("checked", false);
        }
    });

    $(document).on('change', '.chkFather', function() {
        if ($(this).is(':checked')) {
            //$(".chkChild"+id).prop("checked", true);
        } else {
            $(".chkAllFather").prop("checked", false);
        }
    });

    $(document).on('change', '.chkAllMother', function() {
        if ($(this).is(':checked')) {
            $(".chkMother").prop("checked", true);
        } else {
            $(".chkMother").prop("checked", false);
        }
    });

    $(document).on('change', '.chkMother', function() {
        if ($(this).is(':checked')) {
            //$(".chkChild"+id).prop("checked", true);
        } else {
            $(".chkAllMother").prop("checked", false);
        }
    });

    $(document).on('click', '#createAccount', function() {

        var stuids = [];
        $.each($(".chkclick:checked"), function() {
            stuids.push($(this).val());
        });
        var stuid = stuids.join(",");
        if (stuid) {
            $("#showPasswordPage").click();
        } else {
            alert('you Have to Select User to Create Account');
        }
    });

    $(document).on('click', '#donePassword', function() {
        var pass = $("#pass").val();
        var content = $("#mailcontent").val();
        var favorite = [];
        $.each($("input[name='type[]']:checked"), function() {
            favorite.push($(this).val());
        });
        var types = favorite.join(",");
        $("#addPassword").val(pass);
        $("#addContent").val(types);
        $("#preloader").show();
        $("#createAccountForm").submit();
    });

    $(document).on('click', '#closePassword', function() {
        $("#TB_overlay").remove();
        $("#TB_window").remove();
    });

    $(document).ready(function() {
        $('#showMultiClassByProg').selectize({
            plugins: ['remove_button'],
        });
    });

    $(document).on('change', '#getMultiClassByProgCamp', function() {
        var id = $(this).val();
        var type = 'getClass';
        $('#showMultiClassByProg').selectize()[0].selectize.destroy();
        $("#getFeeStructureByProgClass").html('');
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: {
                val: id,
                type: type
            },
            async: true,
            success: function(response) {
                $("#showMultiClassByProg").html('');
                $("#showMultiClassByProg").html(response);
                $("#showMultiClassByProg").parent().children('.LV_validation_message').remove();
                $('#showMultiClassByProg').selectize({
                    plugins: ['remove_button'],
                });

            }
        });
    });

    $(document).on('click', '#deleteAccount', function() {

        var stuids = [];
        $.each($(".chkclick:checked"), function() {
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
<?php
/*
Pupilsight, Flexible & Open School System
*/
echo "<style>
.marginp {
margin-bottom: 2px;
}
</style>";


use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Messenger\GroupGateway;

$page->breadcrumbs->add(__('Manage Groups'));

if (isActionAccessible($guid, $connection2, '/modules/Messenger/groups_manage.php') == false) {
    //Acess denied
    echo '<div class="alert alert-danger">';
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $groupGateway = $container->get(GroupGateway::class);

    $criteria = $groupGateway->newQueryCriteria()
        ->sortBy(['schoolYear', 'name'])
        ->fromPOST();

    $highestAction = getHighestGroupedAction($guid, '/modules/Messenger/groups_manage.php', $connection2);
    if ($highestAction == 'Manage Groups_all') {
        $groups = $groupGateway->queryGroups($criteria, $_SESSION[$guid]['pupilsightSchoolYearID']);
    } else {
        $groups = $groupGateway->queryGroups($criteria, $_SESSION[$guid]['pupilsightSchoolYearID'], $_SESSION[$guid]['pupilsightPersonID']);
    }

    echo "<div style='height:50px; margin-top:10px; '><a href='index.php?q=/modules/Messenger/groups_manage_add.php' class='btn btn-primary mb-2'>Add</a><div class='float-right mb-2'><button class='btn btn-primary mr-2' type='button' onclick='showemailDiv()'>Send Email</button><button class='btn btn-primary' type='button' onclick='showsmsDiv()'>Send SMS</button></div></div>";
    echo "<div id='smsDiv'  style='display:none;' class='answer_list' >
    <div class='modal-dialog modal-lg' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title smsFieldTitle'>SMS</h5>
                <button type='button' class='close' data-dismiss='modal' onclick='hidesmsDiv()' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>
            <form id='sendSms_groupform' method='post' enctype='multipart/form-data'>
                <div class='modal-body smsField'>
                    <h3 class='font-semibold'>SMS Message</h3>
                    <input type='hidden' id='msgtype' value='sms'>
                    <textarea name='sms_quote' id='smsQuote_stud' class='smsQuote_stud'></textarea>
                    <!--<div style='margin-top: 15px;' id='showMobileField'>
                        <input type='checkbox' class='chkType' data-type='fatherMobile' name='father_mobile' value='1'>
                        Father
                        Mobile
                        <input type='checkbox' class='chkType' data-type='motherMobile' name='mother_mobile' value='1'>
                        Mother
                        Mobile
                        <input type='checkbox' class='chkType' data-type='guardianMobile' name='guardian_mobile'
                            value='1'>
                        Guardian Mobile
                    </div>-->
                    <span></span>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-dismiss='modal' id='closeSM' onclick='hidesmsDiv()'>Close</button>
                    <button type='button' class='btn btn-primary' id='sendSms_group'>Send</button>
                </div>
            </form>
        </div>
    </div>
   </div>";
    echo "<div id='emailDiv'  style='display:none;' class='answer_list' >
    <div class='modal-dialog modal-lg' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
            <h5 class='modal-title emailFieldTitle' style='display:none;'>Email</h5>
                <button type='button' class='close' data-dismiss='modal' onclick='hideemailDiv()' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>
            <form id='sendEmail_groupform' method='post' enctype='multipart/form-data'>
                <div class='modal-body emailField''>
                    <h3 class='font-semibold'>Subject</h3>
                    <input type='hidden' id='msgtype1' value='email'>
                    <textarea name='subject' id='emailSubjectQuote_stud' rows='1'></textarea></br>
                    <h3 class='font-semibold'>Email Message</h3>
                    <textarea name='body' id='emailQuote_stud' rows='5'></textarea>
                    <!--<h3 class='font-semibold'>Attachments (Max Size 2MB)</h3>
                    <input type='hidden' name='MAX_FILE_SIZE' value='15728640' />
                    <input type='file' name='email_attach' id='emailattach_camp'>-->
                    <!--<div style='margin-top: 15px;' id='showEmailField'>
                        <input type='checkbox' class='chkType' data-type='fatherEmail' name='father_email' value='1'>
                        Father
                        Email
                        <input type='checkbox' class='chkType' data-type='motherEmail' name='mother_email' value='1'>
                        Mother
                        Email
                        <input type='checkbox' class='chkType' data-type='guardianEmail' name='guardian_email'
                            value='1'>
                        Guardian Email
                    </div>-->
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-dismiss='modal' id='closeSM' onclick='hideemailDiv()'>Close</button>
                    <button type='button' class='btn btn-primary' id='sendEmail_group'>Send</button>
                </div>
            </form>
        </div>
    </div>
   </div>";

    // DATA TABLE
    $table = DataTable::createPaginated('groupsManage', $criteria);

    /*$table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/Messenger/groups_manage_add.php')
        ->displayLabel()->addClass('marginp');*/

    // COLUMNS
    $table->addCheckboxColumn('group_id', __(''))
        ->setClass('chkbox')
        ->notSortable()
        ->format(function ($groups) {


                if (!empty($groups['yearGroup'])) {
                    return "<input id='group_id' name='group_id[]' type='checkbox' value='" . $groups['pupilsightGroupID'] . "' class='enrollstuid' data-del='1' data-name='" . $groups['pupilsightGroupID'] . "'>";
                } else {
                    return "<input id='group_id' name='group_id[]' type='checkbox' value='" . $groups['pupilsightGroupID'] . "' class='stuid' data-del='1' data-name='" . $groups['pupilsightGroupID'] . "'>";
                }


        });
    $table->addColumn('name', __('Name'))->sortable();

    $table->addColumn('owner', __('Group Owner'))
        ->sortable(['surname', 'preferredName'])
        ->format(Format::using('name', ['', 'preferredName', 'surname', 'Staff', true, true]));

    $table->addColumn('count', __('Group Members'))->sortable();

    $table->addActionColumn()
        ->addParam('pupilsightGroupID')
        ->format(function ($person, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Messenger/groups_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Messenger/groups_manage_delete.php');
        });

    echo $table->render($groups);

}
?>

<script>
    function showsmsDiv() {
            var favorite = [];
            $.each($("input[name='group_id[]']:checked"), function () {
                favorite.push($(this).val());
            });
            var stuid = favorite.join(",");

            if (stuid) {
                //alert(stuid);
                document.getElementById('smsDiv').style.display = "block";
                document.getElementById('emailDiv').style.display = "none";

            } else {
                alert('You Have to Select Group.');
            }
    }

    function showemailDiv() {
        var favorite = [];
        $.each($("input[name='group_id[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var stuid = favorite.join(",");

        if (stuid) {
            //alert(stuid);
            document.getElementById('emailDiv').style.display = "block";
            document.getElementById('smsDiv').style.display = "none";

        } else {
            alert('You Have to Select Group.');
        }
    }

    function hidesmsDiv() {
        document.getElementById('smsDiv').style.display = "none";
    }
    function hideemailDiv() {
        document.getElementById('emailDiv').style.display = "none";
    }

    $(document).on('click', '#sendSms_group', function () {
        var msg = document.getElementById("smsQuote_stud").value;
        var msgtype = document.getElementById("msgtype").value;
        var favorite = [];
        $.each($("input[name='group_id[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var grpid = favorite.join(",");
        if (msg) {
            //alert(grpid);
            //alert (msg);
            //alert (msgtype);
            var grpval = grpid;
            var msgval = msg;
            //var type = 'groupmanagesms';

            if (msgval != '') {
                $.ajax({
                    url: 'modules/Messenger/send_group_message.php',
                    type: 'post',
                    data: { grpval: grpval, msgval: msgval, msgtype:msgtype },
                    async: true,
                    success: function (response) {
                        alert('Message sent successfully.');
                        location.reload();
                    }
                });
            }
        } else {
            alert('You Have to Enter Message.');
        }
    });

    $(document).on('click', '#sendEmail_group', function (e) {
        //var url = $(this).attr('data-href');
        e.preventDefault();
        //$("#preloader").show();
        window.setTimeout(function () {
            var formData = new FormData(document.getElementById("sendEmail_groupform"));
            //alert(formData);
            var body = document.getElementById("emailQuote_stud").value;
            var msgtype = document.getElementById("msgtype1").value;
            //var emailattach_camp = document.getElementById("emailattach_camp").value;
            var favorite = [];
            $.each($("input[name='group_id[]']:checked"), function () {
                favorite.push($(this).val());
            });
            var grpid = favorite.join(",");
            if (body) {
                
                formData.append('msgtype', msgtype);
                formData.append('grpval', grpid);

                if (body != '') {
                    $.ajax({
                        url: 'modules/Messenger/send_group_message.php',
                        type: 'post',
                        data: formData ,
                        contentType: false,
                        cache: false,
                        processData: false,
                        async: false,
                        success: function (response) {
                            alert('Message sent successfully.');
                            location.reload();
                        }
                    });
                }
            } else {
                alert('You Have to Enter Message.');
            }
        }, 100);
    });
</script>
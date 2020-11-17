<?php
/*
Pupilsight, Flexible & Open School System
 */

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Students\StudentGateway;
use Pupilsight\Domain\Helper\HelperGateway;

if (isActionAccessible($guid, $connection2, '/modules/Students/student_view.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {

    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        $page->breadcrumbs->add(__('Student Profiles'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        $studentGateway = $container->get(StudentGateway::class);

        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];

        $canViewFullProfile = ($highestAction == 'Student Profile_full' or $highestAction == 'View Student Profile_fullNoNotes');
        $canViewBriefProfile = isActionAccessible($guid, $connection2, '/modules/Students/student_view_details.php', 'View Student Profile_brief');


        if ($highestAction == 'View Student Profile_myChildren' or $highestAction == 'View Student Profile_my') {

            if ($highestAction == 'View Student Profile_myChildren') {
                echo '<h2>';
                echo __('My Children');
                echo '</h2>';

                $result = $studentGateway->selectActiveStudentsByFamilyAdult($pupilsightSchoolYearID, $pupilsightPersonID);
            } else if ($highestAction == 'View Student Profile_my') {
                echo '<h2>';
                echo __('View Student Profile');
                echo '</h2>';

                $result = $studentGateway->selectActiveStudentByPerson($pupilsightSchoolYearID, $pupilsightPersonID);
            }

            if ($result->isEmpty()) {
                echo "<div class='alert alert-danger'>";
                echo __('You do not have access to this action.');
                echo '</div>';
            } else {
                $table = DataTable::create('students');

                $table->addColumn('student', __('Student'))
                    ->sortable(['surname', 'preferredName'])
                    ->format(Format::using('name', ['', 'preferredName', 'surname', 'Student', true]));
                $table->addColumn('yearGroup', __('Year Group'));
                $table->addColumn('rollGroup', __('Roll Group'));

                $table->addActionColumn()
                    ->addParam('pupilsightPersonID')
                    ->format(function ($row, $actions) {
                        $actions->addAction('view', __('View Details'))
                            ->setURL('/modules/Students/student_view_details.php');
                    });

                echo $table->render($result->toDataSet());
            }
        }

        if ($canViewBriefProfile || $canViewFullProfile) {
            //Proceed!
            $classes = array('' => 'Select Class');
            $sections = array('' => 'Select Section');

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
            // echo '<pre>';
            // print_r($_POST);
            // echo '</pre>';
            // die();

            //$search = isset($_GET['search'])? $_GET['search'] : '';
            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'surname,preferredName';
            $allStudents = isset($_GET['allStudents']) ? $_GET['allStudents'] : '';

            $studentGateway = $container->get(StudentGateway::class);

            $searchColumns = $canViewFullProfile
                ? array_merge($studentGateway->getSearchableColumns(), ['parent1.email', 'parent1.emailAlternate', 'parent2.email', 'parent2.emailAlternate'])
                : $studentGateway->getSearchableColumns();


            $HelperGateway = $container->get(HelperGateway::class);

            if ($_POST) {
                $input = $_POST;
                $pupilsightProgramID = $_POST['pupilsightProgramID'];
                $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
                $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
                $search = $_POST['search'];

                $roleId = $_SESSION[$guid]['pupilsightRoleIDPrimary'];
                $uid = $_SESSION[$guid]['pupilsightPersonID'];

                if ($roleId == '2') {
                    $classes =  $HelperGateway->getClassByProgramForTeacher($connection2, $pupilsightProgramID, $uid);
                    $sections =  $HelperGateway->getSectionByProgramForTeacher($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $uid);
                } else {
                    $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID);
                    $sections =  $HelperGateway->getSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID);
                }

                if (empty($pupilsightProgramID)) {
                    unset($_SESSION['student_search']);
                }
            } else {
                $classes = array('' => 'Select Class');
                $sections = array('' => 'Select Section');
                $pupilsightProgramID = '';
                $pupilsightYearGroupID =  '';
                $pupilsightRollGroupID = '';
                $search = '';
                $input = '';
                unset($_SESSION['student_search']);
            }
            if (!empty($pupilsightProgramID)) {
                $_SESSION['student_search'] = $input;
            }
            // echo '<pre>';
            // print_r($_SESSION['student_search']);
            // echo '</pre>';

            $criteria = $studentGateway->newQueryCriteria()
                ->searchBy($searchColumns, $search)
                ->sortBy(array_filter(explode(',', $sort)))
                ->filterBy('all', $canViewFullProfile ? $allStudents : '')
                ->pageSize(5000)
                ->fromPOST();


            echo '<h2>';
            echo __('Filter');
            echo '</h2>';

            $sortOptions = array(
                'surname,preferredName' => __('Surname'),
                'preferredName' => __('Given Name'),
                'rollGroup' => __('Roll Group'),
                'yearGroup' => __('Year Group'),
            );

            $form = Form::create('filter', '');

            $form->setClass('noIntBorder fullWidth');
            $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/student_view.php');
            $row = $form->addRow();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightProgramID', __('Program'));
            $col->addSelect('pupilsightProgramID')->setId('pupilsightProgramIDbyPP')->fromArray($program)->selected($pupilsightProgramID)->placeholder('Select Program');


            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightYearGroupID', __('Class'));
            $col->addSelect('pupilsightYearGroupID')->setId('pupilsightYearGroupIDbyPP')->fromArray($classes)->selected($pupilsightYearGroupID)->placeholder('Select Class');


            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightRollGroupID', __('Section'));
            $col->addSelect('pupilsightRollGroupID')->setId('pupilsightRollGroupIDbyPP')->fromArray($sections)->selected($pupilsightRollGroupID)->placeholder('Select Section');

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('search', __('Search For'))
                ->description($searchDescription);
            $col->addTextField('search')->setValue($search);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('', __(''));
            $col->addSearchSubmit($pupilsight->session, __('Clear Search'));



            echo $form->getOutput();

            echo "<div style='height:50px; margin-top:10px; '><div class='float-right mb-2'><a style=' ' href=''  data-toggle='modal' data-target='#large-modal-new_stud' data-noti='2'  class='sendButton_stud btn btn-primary' id='sendSMS'>Send SMS</a>";
            echo "&nbsp;&nbsp;<a style='' href='' data-toggle='modal' data-noti='1' data-target='#large-modal-new_stud' class='sendButton_stud btn btn-primary' id='sendEmail'>Send Email</a>";
            echo "&nbsp;&nbsp;<a style='' href='index.php?q=/modules/Students/message_history.php' class='btn btn-primary' id='sendEmail'>History</a>";
            //  echo "&nbsp;&nbsp;<a style=' margin-bottom:10px;' href='' class='btn btn-primary' id='printIDCard'>Print ID Card</a>";
            //  echo "&nbsp;&nbsp;<a style=' margin-bottom:10px;' href='' class='btn btn-primary' id='visitorPass'>Visitor Pass</a>";
            // echo "&nbsp;&nbsp;<a style=' margin-bottom:10px;' href='' class='btn btn-primary' id='visitorHistory'>Visitor History</a>";


            echo "<a style='display:none' id='clickStudentsection' href='fullscreen.php?q=/modules/Students/assign_student_section.php&pupilsightYearGroupID=$pupilsightYearGroupID&pupilsightProgramID=$pupilsightProgramID&width=1000'  class='thickbox '>Assign Students to Section</a>";
            echo "&nbsp;&nbsp;<a style=' ' data-type='student' class='btn btn-primary' href='#'  id='assignStuSec'>Assign Students to Section</a>";

            // echo "<a style='display:none' id='click_bulkStudentregister' href='fullscreen.php?q=/modules/Students/register_student_bulk.php&width=1000'  class='thickbox '>Register Students</a>";
            // echo "&nbsp;&nbsp;<a style='display:none; margin-bottom:10px;' data-type='student' class='btn btn-primary' href='#'  id='bulk_student_reg'>Register Students</a>";

            // echo "<a style='display:none' id='clickStudentsubject' href='fullscreen.php?q=/modules/Students/assign_student_subjects.php&width=1000'  class='thickbox '> Core Subjects Assigned to Students</a>";
            // echo "&nbsp;&nbsp;<a style='display:none; margin-bottom:10px;' data-type='student' class='btn btn-primary' href='#'  id='assignStusub'> Core Subjects Assigned to Students</a>";

            // echo "<a style='display:none' id='clickStudent_elect_subject' href='fullscreen.php?q=/modules/Students/assign_student_elective_subjects.php&width=1000'  class='thickbox '>Assign Elective Subjects to Students</a>";
            // echo "&nbsp;&nbsp;<a style='display:none; margin-bottom:10px;' data-type='student' class='btn btn-primary' href='#'  id='assignStu_elesub'>Assign Elective Subjects to Students</a>";

            echo "&nbsp;&nbsp;<a style='' id='addBulkStudentEnrolment' data-type='student' class='btn btn-primary'>Student Enrollment</a>&nbsp;&nbsp;<a style='' id='removeStudentEnrolment' data-type='student' class='btn btn-primary'>Remove Enrollment</a>&nbsp;&nbsp;<a   class='btn btn-primary' href='index.php?q=/modules/Students/student_add.php&search=" . $criteria->getSearchText(true) . "'>Add</a>&nbsp;&nbsp;<a style='' id='deleteBulkStudent' class='btn btn-primary'>Bulk Delete</a>";
            // echo "&nbsp;&nbsp;<a style=' margin-bottom:10px;' href='' class='btn btn-primary' id='changeStuStatus'>Change Status</a>";
            // echo "&nbsp;&nbsp;<a style=' margin-bottom:10px;' href='' class='btn btn-primary' id='export'>Export</a>";
            echo "&nbsp;&nbsp;<i id='expore_student_xl' title='Export Excel' class='mdi mdi-file-excel mdi-24px download_icon'></i> ";

            echo "</div><div class='float-none'></div></div>";


            if ($_POST) {
                echo '<div class="float-left"><h2>Choose A Student</h2></div>';
            }

            $students = $studentGateway->queryStudentsBySchoolYear($criteria, $pupilsightSchoolYearID, $canViewFullProfile, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID, $search);

            // DATA TABLE
            $table = DataTable::createPaginated('students', $criteria);
            echo "<a style='display:none;' id='submitBulkStudentEnrolment' href='fullscreen.php?q=/modules/Students/studentEnrolment_manage_bulk_add.php&pupilsightSchoolYearID=" . $pupilsightSchoolYearID . "&width=800'  class='thickbox '>Bulk Student Enrollment</a>";
            echo "<div style='height:50px;'><div class='float-right mb-2'></div><div class='float-none'></div></div>&nbsp;&nbsp;";

            /*   $table->addHeaderAction('add', __('Add'))
            ->setURL('/modules/User Admin/student_add.php')
            ->addParam('search', $search)
            ->displayLabel();
      */
            $table->modifyRows($studentGateway->getSharedUserRowHighlighter());

            if ($canViewFullProfile) {
                $table->addMetaData('filterOptions', [
                    'all:on'        => __('All Students')
                ]);

                if ($criteria->hasFilter('all')) {
                    $table->addMetaData('filterOptions', [
                        'status:full'     => __('Status') . ': ' . __('Full'),
                        'status:expected' => __('Status') . ': ' . __('Expected'),
                        'date:starting'   => __('Before Start Date'),
                        'date:ended'      => __('After End Date'),
                    ]);
                }
            }
            // echo $butt = '<i id="expore_xl" title="Export Excel" class="far fa-file-excel download_icon"></i> <br>';
            // COLUMNS
            /*   $table->addCheckboxColumn('student_id',__(''))
            ->setClass('chkbox stuid')
            ->notSortable();*/
            $table->addCheckboxColumn('student_id', __(''))
                ->setClass('chkbox')
                ->notSortable()
                ->format(function ($person) {


                    if (!empty($person['yearGroup'])) {
                        return "<input id='student_id' name='student_id[]' type='checkbox' value='" . $person['pupilsightPersonID'] . "' class='enrollstuid'>";
                    } else {
                        return "<input id='student_id' name='student_id[]' type='checkbox' value='" . $person['pupilsightPersonID'] . "' class='stuid'>";
                    }
                });

            $table->addColumn('student', __('Student'))

                ->sortable(['surname', 'preferredName'])
                ->format(function ($person) {
                    // if(!empty($person['surname'])){
                    //     return Format::name('', $person['surname'], $person['officialName'], 'Student', true, true) . '<br/><small><i>'.Format::userStatusInfo($person).'</i></small>';

                    // } else {
                    if ($person['active_status'] == 'Active') {
                        $status_icon = '<i class="fa fa-circle greenicon_sts" aria-hidden="true"></i>&nbsp;&nbsp;';
                    } else {
                        $status_icon = '<i class="fa fa-circle greyicon_sts " aria-hidden="true"></i>&nbsp;&nbsp;';
                    }
                    return  $status_icon . $person['officialName'];
                    // }

                });
            $table->addColumn('pupilsightPersonID', __('Student Id'));
            // $table->addColumn('pupilsightStudentEnrolmentID', __('Enrolment Id')); 
            $table->addColumn('academic_year', __('Academic'));
            $table->addColumn('program', __('Program'));
            $table->addColumn('yearGroup', __('Class'))
                ->format(function ($person) {

                    return $person['yearGroup'];
                });
            $table->addColumn('rollGroup', __('Section'));
            //  $table->addColumn('dob', __('Date of Birth'));
            //  $table->addColumn('active_status', __('Status'));


            $table->addColumn('student_action', __('Action'))
                ->sortable(['surname', 'preferredName'])
                ->format(function ($person) {
                    return '<div class="navbar">
                    <a class="nav-link dropdown-toggle" href="#navbar-base" data-toggle="dropdown"
                                    role="button" aria-expanded="false"><span class="nav-link-title">Select Action</span></a>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=' . $person['pupilsightPersonID'] . '">Student 360</a></li>
                      <li><a class="dropdown-item" href="index.php?q=/modules/Students/student_edit.php&pupilsightPersonID=' . $person['pupilsightPersonID'] . '">Edit</a></li>
                      <li><a class="dropdown-item thickbox" style="text-decoration: underline !important;" href="fullscreen.php?q=/modules/Students/student_manage_delete.php&pupilsightPersonID=' . $person['pupilsightPersonID'] . '&search=width=650height=135">Delete</a></li>
                      <li><a class="dropdown-item" href="index.php?q=/modules/User Admin/user_manage_password.php&pupilsightPersonID=' . $person['pupilsightPersonID'] . '">Change Password</a></li>
                      <li><a class="dropdown-item" href="#" id="transfer_student" data-id=' . $person['pupilsightPersonID'] . ' data-type="student">Transfer</a></li>
                      <li><a class="dropdown-item" href="#" id="register_deregister" data-id=' . $person['pupilsightPersonID'] . ' data-type="student">Register / De-register</a></li>
                      <li><a class="dropdown-item thickbox" href="fullscreen.php?q=/modules/Students/history.php&pupilsightPersonID=' . $person['pupilsightPersonID'] . '&width=800">History</a></li>
                    </ul>
                  </div>';
                });

            echo "<a style='display:none' id='clickStudent_transfer' href='fullscreen.php?q=/modules/Students/transfer_student_view.php&width=600'  class='thickbox '>Transfer Students</a>";
            echo "<a style='display:none' id='clickStudent_reg_dereg' href='fullscreen.php?q=/modules/Students/reg_dereg_student_view.php&width=800'  class='thickbox '>Register &  De-register</a>";
            // $table->addActionColumn()
            //     ->addParam('pupilsightPersonID')
            //     ->addParam('search', $criteria->getSearchText(true))
            //     ->addParam('sort', $sort)
            //     ->addParam('allStudents', $canViewFullProfile ? $allStudents : '')
            //     ->format(function ($row, $actions) {
            //         $actions->addAction('view', __('View Details'))
            //             ->setURL('/modules/Students/student_view_details.php');
            //     });

            if ($_POST) {
                //echo "test dhfhd";
                echo $table->render($students);
            } else {
                //echo "test 234";
                echo '<h2>Please Filter the Data</h2>';
            }
        }
    }
}

?>


<script>
    //limit

    $(document).on('change', '.filters', function() {
        $("#addBulkStudentEnrolment").show();
    });
    $(document).on('click', '.clear', function() {
        $("#addBulkStudentEnrolment").hide();
    });

    $(document).on('change', '#limit', function() {
        var flter = $("#limit  option:selected").text();
        if (flter == 'All') {
            $("#addBulkStudentEnrolment").show();
        } else {
            $("#addBulkStudentEnrolment").hide();
        }
    });
    $(document).on('click', '.padipag2', function() {
        $prgm = $("#pupilsightProgramIDbyPP  option:selected").val();
        if ($prgm != '') {
            // location.reload();
        }

    });
    $(document).on('keyup', '.smsQuote_stud', function() {
        var txt = $(this).val();
        var count = txt.length;
        var dis = txt.replace(/\"/g, "");
        var sms_count = count / 161;
        var sms_count = parseInt(sms_count) + 1;
        $(this).nextAll('span:first').html('Characters : ' + count + " (<i class='fa fa-eye' aria-hidden='true'></i>) : " + sms_count + " SMS Count(s)");
        $(this).nextAll('span:first').attr("title", dis);
    });

    $(document).on('click', '#deleteBulkStudent', function() {
        var favorite = [];
        $.each($("input[name='student_id[]']:checked"), function() {
            favorite.push($(this).val());
        });
        var stuId = favorite.join(",");
        //alert(subid);
        if (stuId) {
            var val = stuId;
            var type = 'deleteBulkStudent';
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
                        alert('Student Deleted Successfully!');
                        location.reload();
                    }
                });
            }
        } else {
            alert('You Have to Select Student.');
        }
    });

    $(document).on('click', '#removeStudentEnrolment', function() {
        var favorite = [];
        $.each($(".enrollstuid:checked"), function() {
            favorite.push($(this).val());
        });

        var stuid = favorite.join(",");

        var checked = $(".stuid:checked").length;
        if (checked >= 1) {
            alert('You Have to Select Enrolled Student.');
            return false;
        } else {
            if (stuid) {
                var val = stuid;
                var type = 'removeStudentEnrollment';
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
                            alert('Student Enrollment Removed Successfuliy!.');
                            location.reload();
                        }
                    });
                }
            } else {
                alert('You Have to Select Enrolled Student.');
            }
        }
    });
</script>
<style>
    #expore_tbl {
        min-height: 152px !important;
    }
</style>
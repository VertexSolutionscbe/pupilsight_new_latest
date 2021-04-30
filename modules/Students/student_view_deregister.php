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
    
        $page->breadcrumbs->add(__('Student Profiles'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        $studentGateway = $container->get(StudentGateway::class);

        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];

        
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

            $sqlf = 'SELECT field_name FROM student_field_show WHERE pupilsightPersonID = '.$pupilsightPersonID.' ';
            $resultf = $connection2->query($sqlf);
            $showfield = $resultf->fetchAll();

            $sqlchk = 'SELECT GROUP_CONCAT(pupilsightModuleButtonID) as buttonIDS FROM pupilsightModuleButtonPermission WHERE pupilsightModuleID = 5 AND pupilsightPersonID = '.$pupilsightPersonID.' ';
            $resultchk = $connection2->query($sqlchk);
            $buttPermisionData = $resultchk->fetch();
            $permissionChk = explode(',',$buttPermisionData['buttonIDS']);

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
                    $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID, $pupilsightSchoolYearID);
                    $sections =  $HelperGateway->getSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $pupilsightSchoolYearID);
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

       
            echo "<div style='height:50px; margin-top:10px; '><div class='float-right mb-2'>";
           
            echo "<a style='' id='registerBulkStudent' class='btn btn-primary'>Bulk Register</a>";
            
            echo "</div><div class='float-none'></div></div>";
      

            if ($_POST) {
                echo '<div class="float-left"><h2>Choose A Student</h2></div>';
            }

            $students = $studentGateway->getAllDeRegisterStudents($criteria, $pupilsightSchoolYearID, $canViewFullProfile, $pupilsightProgramID, $pupilsightYearGroupID, $pupilsightRollGroupID, $search);

            // DATA TABLE
            $table = DataTable::createPaginated('students', $criteria);
            
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


            if(!empty($showfield)){
                foreach($showfield as $sf){
                    if($sf['field_name'] == 'student'){
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
                    }
                    if($sf['field_name'] == 'pupilsightPersonID'){
                    $table->addColumn('pupilsightPersonID', __('Student Id'));
                    }
                    if($sf['field_name'] == 'admission_no'){
                    $table->addColumn('admission_no', __('Admission No'));
                }
                if($sf['field_name'] == 'academic_year'){
                    $table->addColumn('academic_year', __('Academic'));
                }
                if($sf['field_name'] == 'program'){
                    $table->addColumn('program', __('Program'));
                }
                if($sf['field_name'] == 'yearGroup'){
                    $table->addColumn('yearGroup', __('Class'))
                        ->format(function ($person) {

                            return $person['yearGroup'];
                        });
                    }
                    if($sf['field_name'] == 'rollGroup'){
                    $table->addColumn('rollGroup', __('Section'));
                }
                if($sf['field_name'] == 'dob'){
                    $table->addColumn('dob', __('Date of Birth'));
                }
                if($sf['field_name'] == 'gender'){
                    $table->addColumn('gender', __('Gender'));
                }
                if($sf['field_name'] == 'username'){
                    $table->addColumn('username', __('Username'));
                }
                if($sf['field_name'] == 'phone1'){
                    $table->addColumn('phone1', __('Phone'));
                }
                if($sf['field_name'] == 'email'){
                    $table->addColumn('email', __('Email'));
                }
                if($sf['field_name'] == 'address1'){
                    $table->addColumn('address1', __('Address'));
                }
                if($sf['field_name'] == 'address1District'){
                    $table->addColumn('address1District', __('District'));
                }
                if($sf['field_name'] == 'address1Country'){
                    $table->addColumn('address1Country', __('Country'));
                }
                if($sf['field_name'] == 'languageFirst'){
                    
                    $table->addColumn('languageFirst', __('First Language'));
                }
                if($sf['field_name'] == 'languageSecond'){
                    $table->addColumn('languageSecond', __('Second Language'));
                }
                if($sf['field_name'] == 'languageThird'){
                    $table->addColumn('languageThird', __('Third Language'));
                }
                if($sf['field_name'] == 'religion'){
                    $table->addColumn('religion', __('Religion'));
                }
               
                }
            } else {
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
                $table->addColumn('admission_no', __('Admission No'));
                // $table->addColumn('pupilsightStudentEnrolmentID', __('Enrolment Id')); 
                $table->addColumn('academic_year', __('Academic'));
                $table->addColumn('program', __('Program'));
                $table->addColumn('yearGroup', __('Class'))
                    ->format(function ($person) {

                        return $person['yearGroup'];
                    });
                $table->addColumn('rollGroup', __('Section'));
                $table->addColumn('dob', __('Date of Birth'));
                $table->addColumn('gender', __('Gender'));
                $table->addColumn('username', __('Username'));
                $table->addColumn('phone1', __('Phone'));
                $table->addColumn('email', __('Email'));
                $table->addColumn('address1', __('Address'));
                $table->addColumn('address1District', __('District'));
                $table->addColumn('address1Country', __('Country'));
                
                $table->addColumn('languageFirst', __('First Language'));
                $table->addColumn('languageSecond', __('Second Language'));
                $table->addColumn('languageThird', __('Third Language'));
                $table->addColumn('religion', __('Religion'));
            }


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

    $(document).on('click', '#registerBulkStudent', function() {
        var favorite = [];
        $.each($("input[name='student_id[]']:checked"), function() {
            favorite.push($(this).val());
        });
        var stuId = favorite.join(",");
        //alert(subid);
        if (stuId) {
            var val = stuId;
            var type = 'registerBulkStudent';
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
                        alert('Student Register Successfully!');
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

    $(document).on('click', '#alertDelStu', function() {
        alert('You Have to De-Register Student First!');
    });
</script>
<style>
    #expore_tbl {
        min-height: 152px !important;
    }
</style>
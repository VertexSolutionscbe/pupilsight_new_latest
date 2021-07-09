<?php
/*
Pupilsight, Flexible & Open School System
 */

use Pupilsight\Domain\Curriculum\CurriculamGateway;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Academics/manage_test_results.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    //Proceed!
    $page->breadcrumbs->add(__('Test Results'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];
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


    if ($_POST) {

        $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];
        $pupilsightProgramID = $_POST['pupilsightProgramID'];

        //$tesid = $_POST['test_id'];

        $test_id = $_POST['test_id'];
        $testIds = implode(',', $tesid);
        $kount = 1;
        // $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
        $searchbyPost = '';

        //  $search =  $_POST['search'];
        $stuId = $_POST['studentId'];
    } else {
        $test_id = '';
        $pupilsightYearGroupID = '';
        $pupilsightRollGroupID = '';
        $pupilsightProgramID = "";
        //    $pupilsightDepartmentID='';
        $searchbyPost = '';
        $search = '';
        $stuId = '0';
    }
    $sql_rmk = 'SELECT id, description FROM acRemarks ';
    $result_rmk = $connection2->query($sql_rmk);
    $rowdata_rmk = $result_rmk->fetchAll();
    $remarks = array();
    $remark2 = array();
    // $subject1=array(''=>'Select Subjects');
    foreach ($rowdata_rmk as $dr) {
        $subject2[$dr['id']] = $dr['description'];
    }
    $remarks =  $remark2;


    $cls_sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramID = "' . $pupilsightProgramID . '" GROUP BY a.pupilsightYearGroupID';
    $cls_res = $connection2->query($cls_sql);
    $cls_res1 = $cls_res->fetchAll();
    $classes = array();
    $classes1 = array();
    // $subject1=array(''=>'Select Subjects');
    foreach ($cls_res1 as $dr) {
        $classes1[$dr['pupilsightYearGroupID']] = $dr['name'];
    }
    $classes =  $classes1;
    //section 
    $s_sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" GROUP BY a.pupilsightRollGroupID';
    $s_res = $connection2->query($s_sql);
    $s_res1 = $s_res->fetchAll();
    $section = array('' => __('Select'));
    $section1 = array();
    foreach ($s_res1 as $s) {
        $section1[$s['pupilsightRollGroupID']] = $s['name'];
    }
    $section +=  $section1;
    //select subjects from department
    $sqld = 'SELECT pupilsightDepartmentID, name FROM pupilsightDepartment ';
    $resultd = $connection2->query($sqld);
    $rowdatadept = $resultd->fetchAll();
    $subjects = array();
    $subject2 = array();
    // $subject1=array(''=>'Select Subjects');
    foreach ($rowdatadept as $dt) {
        $subject2[$dt['pupilsightDepartmentID']] = $dt['name'];
    }
    $subjects =  $subject2;
    //load test
    $sql_tst = 'SELECT b.id, b.name FROM examinationTestAssignClass AS a LEFT JOIN examinationTest AS b ON a.test_id = b.id  WHERE a.pupilsightSchoolYearID= "' . $pupilsightSchoolYearID . '" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '"  AND a.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '"';
    $result_test = $connection2->query($sql_tst);
    $tests = $result_test->fetchAll();
    $testarr = array('' => __('Select'));
    $test2 = array();

    foreach ($tests as $ts) {
        $test2[$ts['id']] = $ts['name'];
    }
    $testarr +=  $test2;
    $test_types = array(__('Select') => array('Term1' => __('Term 1'), 'Term2' => __('Term 2')));
    /*  $tests =  array(__('Select') => array('Test1' => __('Test1'),
    'Test2' => __('Test2'),
    'Test3' => __('Test3')));*/

    echo "<div class='btn-list'>";
    echo "<a  id='lock_me_btn' data-type='lock' class='lock_me_btn btn btn-white butt'>Lock Me</a>";
    echo "<a  id='unlock_me_btn' data-type='unlock' class='lock_me_btn btn btn-white butt'>Unlock Me</a>";
    echo "<a  id='publish_result' data-type='test' class='btn btn-white butt'>Publish</a>";
    echo "<a  id='unpublish_result' data-type='test' class='btn btn-white butt'>Unpublish</a>";
    echo "<a  id='withheld_result' data-type='test' class='btn btn-white butt'>Withhold</a>";
    echo "<a style='display:none' id='modifyMarks' class='btn btn-white butt'>test Mark Entry</a>";
    echo "<a id='modifyMarksEntry'  data-type='student' class='btn btn-white butt'>Mark Entry</a>";

    echo "<a  id='sendSMS'  href=''  data-toggle='modal' data-target='#large-modal-new_stud' data-noti='2'  class='sendButton_stud btn btn-white butt' >Send SMS</a>";
    echo "<a  id='sendEmail'  href='' data-toggle='modal' data-noti='1' data-target='#large-modal-new_stud' class='sendButton_stud btn btn-white butt' >Send Email</a>";

    echo "<a  id='result_show_pdf' data-type='test' class='btn btn-white butt'>Show Pdf</a>";
    echo "<a  id='result_hide_pdf' data-type='test' class='btn btn-white butt'>Hide Pdf</a>";
    echo "<a  id='result_show_html' data-type='test' class='btn btn-white butt'>Show HTML</a>";
    echo "<a  id='result_hide_html'  data-type='test' class='btn btn-white butt'>Hide HTML</a>";
    echo "<a  id='result_lock_tr' data-type='test' class='btn btn-white butt'>Lock T.R</a>";
    echo "<a  id='result_unlock_tr' data-type='test' class='btn btn-white butt'>Unlock T.R</a>";
    echo "<button  id='result_send_mark_by_sms' data-type='test' class='btn btn-outline-primary butt'>Send Mark via SMS</button>";
    echo "<button  id='result_send_mark_by_email' data-type='test' class='btn btn-outline-primary butt'>Send Mark via Email</button>";

    echo "<a  id='mass_student_tests_xl' title='' data-type='test' class='btn btn-white butt'>Mass Download</a>";
    echo "<a  id='result_publish_history' data-type='test' class='btn btn-white butt'>Publish History</a>";
    echo "</div>";
    echo  "<div class='hr-text'>Search Filter</div>";


    $searchform = Form::create('searchForm', '');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->setClass('noIntBorder fullWidth');
    $searchform->addHiddenValue('studentId', '0');

    $row = $searchform->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->setId('pupilsightProgramIDbyPP')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder('Select Program');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
    $col->addSelect('pupilsightYearGroupID')->fromArray($classes)->setId('pupilsightYearGroupIDbyPP')->selected($pupilsightYearGroupID)->required()->placeholder('Select Class');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelect('pupilsightRollGroupID')->fromArray($section)->setId('pupilsightRollGroupIDbyPP')->selected($pupilsightRollGroupID)->required()->placeholder('Select Section');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('test_id', __('Select Test'));
    $col->addSelect('test_id')->fromArray($testarr)->required()->setId('testId')->selected($test_id)->placeholder('Select Test');

    $col = $row->addColumn()->setClass('newdes');
    $col->addContent('<div style="width:90px;margin-top: 30px;"><button type="submit"  class=" btn btn-primary">Go</button>&nbsp;&nbsp;
   </div>');
    echo $searchform->getOutput();
    echo  "<div style='height:20px'></div>";
    $CurriculamGateway = $container->get(CurriculamGateway::class);
    $criteria = $CurriculamGateway->newQueryCriteria()
        //->sortBy(['id'])
        ->pageSize(1000)
        ->fromPOST();
    if (isset($_POST['pupilsightYearGroupID']) && $_POST['pupilsightRollGroupID']) {
        $students_test_results = $CurriculamGateway->getTestResults($criteria, $pupilsightSchoolYearID, $pupilsightYearGroupID, $pupilsightRollGroupID, $test_id);
    } else {
        $students_test_results = array();
    }
    /*  echo "<pre>";
  print_r($students_test_results);*/
    if (count($students_test_results) < 1) {
        echo "<div class='error'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {
?>

        <div id="divTableDataHolder">
            <a href="javascript:void(0)" class="btn btn-primary export_excel_sheet"> Export excel</a>

            <?php if ($kount > 1) { ?>
                <a href="thirdparty/phpword/reportcard_multitest.php?tid=<?php echo $testIds; ?>" class="btn btn-primary "> Download PDF</a>
            <?php } else { ?>
                <a href="thirdparty/phpword/reportcard.php?tid=<?php echo $testIds; ?>" class="btn btn-primary "> Download PDF</a>
            <?php } ?>



            <form name="test_result_form" action="" id="test_result_form">
                <div id="marks_studentExcel" style="display: none"></div>
                <table id="expore_tbl" class='table text-nowrap' cellspacing='0' style='width: 100%;margin-top: 20px;'>
                    <thead>
                        <tr class='head'>
                            <th style="width:80px" rowspan="2">
                                <input type="checkbox" name="checkall" id="checkall" value="on" class="floatNone checkall">
                            </th>
                            <th>Sl No <br /></th>
                            <th>Name <br /> </th>
                            <th class="bdr_right"> Id </th>
                            <th>Marks <br />Entered</th>
                            <th>Locked</th>
                            <th class="">Published</th>
                            <th>Withheld</th>
                            <th>Test Report lock</th>
                            <th>PDF Report</th>
                            <th>HTML</th>
                            <th> Report</th>
                        </tr>
                    </thead>
                    <tbody>
                <?php
                $count = 0;
                $rowNum = 'odd';
                foreach ($students_test_results as $row) {
                    if ($count % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                        $rowNum = 'odd';
                    }
                    ++$count;
                    echo "<tr class=$rowNum>";
                    echo '<td class="check_boxes">';
                    echo  '<input type="checkbox"  data_tid="' . $row['id'] . '" data_status="' . $row['status'] . '" data_testid="' . $row['test_id'] . '" name="student_id[]" id="student_id[' . $row['pupilsightPersonID'] . ']" value="' . $row['pupilsightPersonID'] . '" >';
                    echo '</td>';
                    echo '<td >';
                    echo '<center>';
                    echo $count;
                    echo '</center>';
                    echo '</td>';
                    echo '<td>';
                    echo $row['student_name'];
                    echo '</td>';
                    echo '<td>';
                    echo $row['stuid'];
                    echo '</td>';
                    echo '<td class="td_texfield">';
                    echo '<input type="hidden" name="test_id[' . $row['test_id'] . ']">';
                    //if marks is enabled
                    if (!empty($row['marks_obtained']) || !empty($row['gradeId'])) {
                        //$row['marks_obtained']
                        echo '<i class="mdi mdi-checkbox-marked-circle mdi-24px  greenicon "></i>';
                    } else {
                        echo '<i class="mdi mdi-close-circle mdi-24px x_icon "></i>';
                    }

                    echo '</td>';
                    echo '<td>';
                    if ($row['status'] == 1) {
                        echo '<i class="mdi mdi-lock mdi-24px   "></i>';
                    } else {
                        echo '<i class="mdi mdi-close-circle mdi-24px x_icon "></i>';
                    }

                    echo '</td>';
                    echo '<td> ';
                    echo '<i class="mdi mdi-checkbox-marked-circle mdi-24px  greenicon "></i>';
                    echo '</td>';
                    echo '<td class="td_texfield">';
                    echo '<i class="mdi mdi-close-circle mdi-24px x_icon"></i>';
                    echo '</td>';
                    echo '<td> ';
                    echo '<i class="mdi mdi-close-circle mdi-24px x_icon"></i>';
                    echo '</td>';
                    echo '<td> ';
                    echo '<i class="mdi mdi-checkbox-marked-circle mdi-24px  greenicon "></i>';
                    echo '</td>';
                    echo '<td> ';
                    echo '<i class="mdi mdi-close-circle mdi-24px x_icon""></i>';
                    echo '</td>';
                    echo '<td> ';
                    echo '<a href="thirdparty/phpword/reportcardsingle.php?tid=' . $test_id . '&stid=' . $row['stuid'] . '"><i class="mdi mdi-file-pdf mdi-24px  small_icon"></i></a>';
                    echo '</td>';
                    echo '</tr>';
                }
                echo "</tbody>";
                echo '</table> </form></div>';
            }
        }

        echo '<a href="" id="sendMarks" style="display:none;">sendMarks</a>';
        echo '<input type="hidden" id="uid" value="' . $pupilsightPersonID . '">';
                ?>

                <style>
                    .text-xxs {
                        display: none;
                    }

                    .text_cntr {
                        text-align: center;
                    }

                    .bdr_right {
                        border-right: 2px solid #dee2e6;
                    }

                    .textfield_wdth {
                        width: 75px;
                    }

                    .td_texfield {
                        width: 9%;
                    }

                    .rmk_width {
                        width: 250px;
                    }

                    .x_icon,
                    .small_icon,
                    .greenicon {
                        font-size: 18px !important;
                    }

                    /* .butt {
        width: 150px;
        text-align: center;
    } */
                </style>
                <script type="text/javascript">
                    $(document).on('click', '.export_excel_sheet', function() {
                        var pupilsightProgramID = $("#pupilsightProgramIDbyPP").val();
                        var pupilsightYearGroupID = $("#pupilsightYearGroupIDbyPP").val();
                        var pupilsightRollGroupID = $("#pupilsightRollGroupIDbyPP").val();
                        var testId = $("#testId").val();
                        var favorite = [];
                        $.each($("input[name='student_id[]']:checked"), function() {
                            favorite.push($(this).val());
                        });
                        var length = favorite.length;
                        var type = "download_excel_results";
                        if (length != 0) {
                            $.ajax({
                                url: 'ajaxSwitchExcel.php',
                                type: 'post',
                                data: {
                                    pupilsightProgramID: pupilsightProgramID,
                                    pupilsightYearGroupID: pupilsightYearGroupID,
                                    pupilsightRollGroupID: pupilsightRollGroupID,
                                    testId: testId,
                                    stuid: favorite,
                                    type: type
                                },
                                async: true,
                                success: function(response) {

                                    $("#marks_studentExcel").html(response);
                                    $("#excelexport").table2excel({
                                        name: " Student Marks",
                                        filename: "Student_marks.xls",
                                        fileext: ".xls",
                                        exclude: ".checkall",
                                        exclude_inputs: true,
                                        exclude_links: true
                                    });
                                }
                            });
                        } else {
                            alert('Please select atleast one student.');
                        }
                    });

                    $(document).on('change', '#pupilsightRollGroupIDbyPP', function() {
                        var id = $(this).val();
                        var cid = $('#pupilsightYearGroupIDbyPP').val();
                        var pid = $('#pupilsightProgramIDbyPP').val();
                        var type = 'getTestBySection';
                        $.ajax({
                            url: 'ajax_data.php',
                            type: 'post',
                            data: {
                                val: id,
                                type: type,
                                cid: cid,
                                pid: pid
                            },
                            async: true,
                            success: function(response) {
                                $("#testId").html();
                                $("#testId").html(response);
                            }
                        });
                    });


                    $(document).on('click', '#result_send_mark_by_sms', function() {
                        var favorite = [];
                        $.each($("input[name='student_id[]']:checked"), function() {
                            favorite.push($(this).val());
                        });
                        var stuId = favorite.join(",");
                        var tid = $("#testId").val();
                        var uid = $("#uid").val();
                        //alert(subid);
                        if (stuId) {
                            if (tid) {
                                var hrf = 'send_marks_via_sms_email.php?type=sms&uid=' + uid + '&testId=';
                                var newhrf = hrf + tid + '&stuId=' + stuId;
                                $("#sendMarks").attr('href', newhrf);
                                window.setTimeout(function() {
                                    $("#sendMarks")[0].click();
                                }, 10);
                            } else {
                                alert('You Have to Select Test.');
                            }
                        } else {
                            alert('You Have to Select Student.');
                        }
                    });

                    $(document).on('click', '#result_send_mark_by_email', function() {
                        var favorite = [];
                        $.each($("input[name='student_id[]']:checked"), function() {
                            favorite.push($(this).val());
                        });
                        var stuId = favorite.join(",");
                        var tid = $("#testId").val();
                        var uid = $("#uid").val();
                        //alert(subid);
                        if (stuId) {
                            if (tid) {
                                var hrf = 'send_marks_via_sms_email.php?type=email&uid=' + uid + '&testId=';
                                var newhrf = hrf + tid + '&stuId=' + stuId;
                                $("#sendMarks").attr('href', newhrf);
                                window.setTimeout(function() {
                                    $("#sendMarks")[0].click();
                                }, 10);
                            } else {
                                alert('You Have to Select Test.');
                            }
                        } else {
                            alert('You Have to Select Student.');
                        }
                    });
                </script>
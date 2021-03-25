<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\CurriculumGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Academics/subject_to_class_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //  echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    //Proceed!
    $page->breadcrumbs->add(__('Subject To Class'));
    if (isset($_GET['m_err'])) {
        $err_sql = 'SELECT name FROM pupilsightYearGroup WHERE pupilsightYearGroupID IN(' . $_GET['m_err'] . ')';
        $err_res = $connection2->query($err_sql);
        $err_data = $err_res->fetchAll();
        $e_txt = "";
        foreach ($err_data as $err) {
            $e_txt .= $err['name'] . ",";
        }
        $e_txt = substr($e_txt, 0, -1);
        echo '<div class="error">You have to assign subjects to class to do this activity( Not completed this ' . $e_txt . ').</div>';
    } else  if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();

    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
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

    if ($_POST) {
        if (!empty($_POST['page'])) {
            $pupilsightProgramID =  '';
            $pupilsightYearGroupID =  '';
            $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        } else {
            $pupilsightProgramID =  $_POST['pupilsightProgramID_MC'];
            $pupilsightYearGroupID = $_POST['pupilsightClassID'];
            $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
        }
    } else {
        if (!empty($_GET['classId'])) {
            $clId = $_GET['classId'];
            $pupilsightYearGroupID =  $clId;
            $pupilsightProgramID =  $_GET['proId'];
            $pupilsightSchoolYearID = $_GET['acaId'];
        } else {
            $pupilsightYearGroupID =  '';
            $pupilsightProgramID =  '';
            $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        }
    }
    $cls_sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND a.pupilsightProgramID = "' . $pupilsightProgramID . '" GROUP BY a.pupilsightYearGroupID';
    $cls_res = $connection2->query($cls_sql);
    $cls_res1 = $cls_res->fetchAll();
    $searchform = Form::create('searchForm', '');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $row = $searchform->addRow();
    // $col = $row->addColumn()->setClass('newdes');
    //     $col->addLabel('pupilsightProgramID', __('Program'));
    //     $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
    $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->required()->selected($pupilsightSchoolYearID)->placeholder('Select Academic Year')->setClass('slt_chng_sub');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID_MC', __('Program'));
    $col->addSelect('pupilsightProgramID_MC')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder('Select Program')->setClass('slt_chng_sub');

    $col = $row->addColumn()->setClass('newdes');
    // $col->addLabel('', __(''));
    $option = '<option value="">Select class</option>';
    foreach ($cls_res1 as $val) {
        $slt = '';
        if ($val['pupilsightYearGroupID'] == $pupilsightYearGroupID) {
            $slt = "selected";
        }
        $option .= "<option value='" . $val['pupilsightYearGroupID'] . "' " . $slt . ">" . $val['name'] . "</option>";
    }
    $col->addLabel('pupilsightClassID', __('Class *'));
    $col->addContent('<select class="slt_chng_sub " style="300px"  id="pupilsightClassID" name="pupilsightClassID"  required>' . $option . '</select>');
    /*$col->addSelectYearGroup('pupilsightClassID')->selected($pupilsightYearGroupID)->required()->placeholder('Select Class')->setClass('slt_chng_sub');*/
    $links = '';
    if (isset($_POST['pupilsightProgramID_MC'])) {
        $links = '<a id="copySubjectToClass" class="btn btn-primary">Copy</a>&nbsp;&nbsp;<a id="saveSubjectToClass"  class=" btn btn-primary">Save</a></div>';
    }
    $col = $row->addColumn()->setClass('newdes');
    $col->addContent('<div style="width:250px; margin-top: 4px;"><br/><button type="submit"  class=" btn btn-primary">Go</button>&nbsp;&nbsp;<a style="display:none;" id="clickSubjectToClass" href="fullscreen.php?q=/modules/Academics/subject_to_class_manage_copy.php"  class="thickbox btn btn-primary">Copy</a>
        ' . $links . '
    ');
    echo $searchform->getOutput();


    $CurriculumGateway = $container->get(CurriculumGateway::class);
    $criteria = $CurriculumGateway->newQueryCriteria()
        //->sortBy(['id'])
        ->pageSize(1000)
        ->fromPOST();
?>
    <div class='mt-2 mb-2'>
        <button type="button" id='btnSubjSort' class="btn btn-primary ml-2" onclick="subjectSorting();">Subject Sorting</button>
    </div>
<?php
    $subjects = $CurriculumGateway->getSubjectDate($criteria, $pupilsightSchoolYearID, $pupilsightProgramID, $pupilsightYearGroupID);

    echo '<form method="POST" id="subject_to_class_form" action="' . $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/subject_to_class_manage_addProcess.php">
            <input type="hidden" name="address" value="' . $_SESSION[$guid]['address'] . '">
            <input type="hidden" name="pupilsightSchoolYearID" value="' . $pupilsightSchoolYearID . '">
            <input type="hidden" name="pupilsightProgramID" value="' . $pupilsightProgramID . '">
            <input type="hidden" name="pupilsightYearGroupID" value="' . $pupilsightYearGroupID . '">
            <input type="hidden" id="deptId" value="">
    ';
    $table = DataTable::createPaginated('FeeStructureManage', $criteria);

    $table->addCheckboxColumn('pupilsightDepartmentID', __(''))
        ->setClass('chkbox')
        ->notSortable()
        ->format(function ($subjects) {
            $checked = '';
            if ($subjects['checked'] == '1') {
                $checked = 'checked';
            }
            return "<input type='checkbox' class='subId' name='pupilsightDepartmentID[]'  data-id='" . $subjects["pupilsightDepartmentID"] . "' data-sid='" . $subjects["id"] . "' value='" . $subjects["pupilsightDepartmentID"] . "' " . $checked . "><input type='hidden' name='subject_code[" . $subjects["pupilsightDepartmentID"] . "]' value='" . $subjects["nameShort"] . "' >";
        });
    $table->addColumn('name', __('Subject'))
        ->format(function ($subjects) {
            if (!empty($subjects['subject_display_name'])) {
                return "<a  class='showSkillBySubId' data-id='" . $subjects["pupilsightDepartmentID"] . "'   style='cursor:pointer'>" . $subjects['subject_display_name'] . "</a>";
            } else {
                return "<a  class='showSkillBySubId' data-id='" . $subjects["pupilsightDepartmentID"] . "'    style='cursor:pointer'>" . $subjects['name'] . "</a>";
            }
        });
    

    $table->addColumn('', __('Display Name'))
        ->format(function ($subjects) {
            if (!empty($subjects['subject_display_name'])) {
                return "<input type='textbox' name='display_name[" . $subjects["pupilsightDepartmentID"] . "]' value='" . $subjects['subject_display_name'] . "' class='form-control'>";
            } else {
                return "<input type='textbox' name='display_name[" . $subjects["pupilsightDepartmentID"] . "]' value='" . $subjects['name'] . "' class='form-control' >";
            }
        });
    $table->addColumn('nameShort', __('Code'));
    $table->addColumn('subject_type', __('Subject Type'))
        ->format(function ($subjects) {
            if (!empty($subjects['subject_type'])) {
                if ($subjects['subject_type'] == 'Core') {
                    $cselect = 'selected';
                    $eselect = '';
                } else {
                    $eselect = 'selected';
                    $cselect = '';
                }
                return "<select name='subject_type[" . $subjects["pupilsightDepartmentID"] . "]' style='border:1px solid gray; padding: 0px 10px;float: left;'><option value='Core' " . $cselect . ">Core</option><option value='Elective' " . $eselect . ">Elective</option></select>";
            } else {
                return "<select name='subject_type[" . $subjects["pupilsightDepartmentID"] . "]' style='border:1px solid gray; padding: 0px 10px;float: left;'><option value='Core'>Core</option><option value='Elective'>Elective</option></select>";
            }
        });
    $table->addColumn('di_mode', __('D.I Mode'))
        ->format(function ($subjects) {
            if (!empty($subjects['di_mode'])) {
                $select1 = '';
                $select2 = '';
                $select3 = '';
                $select4 = '';
                $select5 = '';
                $select6 = '';
                if ($subjects['di_mode'] == 'FREE_FORM') {
                    $select1 = 'selected';
                } elseif ($subjects['di_mode'] == 'SUBJECT_WISE') {
                    $select2 = 'selected';
                } elseif ($subjects['di_mode'] == 'SUBJECT_WISE_NO_EDIT') {
                    $select3 = 'selected';
                } elseif ($subjects['di_mode'] == 'SUBJECT_GRADE_WISE') {
                    $select4 = 'selected';
                } elseif ($subjects['di_mode'] == 'SUBJECT_GRADE_WISE_AUTO') {
                    $select5 = 'selected';
                } else {
                    $select6 = 'selected';
                }


                return "<select name='di_mode[" . $subjects["pupilsightDepartmentID"] . "]' style='border:1px solid gray;padding: 0px 10px;float: left;'><option value='FREE_FORM' " . $select1 . ">FREE FORM</option><option value='SUBJECT_WISE' " . $select2 . ">SUBJECT WISE</option><option value='SUBJECT_WISE_NO_EDIT' " . $select3 . ">SUBJECT WISE NO EDIT</option><option value='SUBJECT_GRADE_WISE' " . $select4 . ">SUBJECT GRADE WISE</option><option value='SUBJECT_GRADE_WISE_AUTO' " . $select5 . ">SUBJECT GRADE WISE AUTO</option><option value='NO_DI' " . $select6 . ">NO DI</option></select>";
            } else {
                return "<select name='di_mode[" . $subjects["pupilsightDepartmentID"] . "]' style='border:1px solid gray;padding: 0px 10px;float: left;'><option value='FREE_FORM'>FREE FORM</option><option value='SUBJECT_WISE'>SUBJECT WISE</option><option value='SUBJECT_WISE_NO_EDIT'>SUBJECT WISE NO EDIT</option><option value='SUBJECT_GRADE_WISE'>SUBJECT GRADE WISE</option><option value='SUBJECT_GRADE_WISE_AUTO'>SUBJECT GRADE WISE AUTO</option><option value='NO_DI'>NO DI</option></select>";
            }
        });

    echo $table->render($subjects);
    echo '</form>';

    echo "</br><table cellspacing='0' id='stuListTable' style='width: 100%;' class='table'>";
    echo "<thead>";
    echo "<tr class='head'>";
    echo '<th style="width: 10%;">';
    echo __("<input type='checkbox' class='allskillId' >");
    echo '</th>';
    echo '<th style="width: 20%;">';
    echo __('Skill');
    echo '</th>';
    echo '<th style="width: 20%;">';
    echo __('Skill Display Name');
    echo '</th>';
    echo "</tr></thead>";
    echo "<tbody id='skillList'>";
    echo "</tbody>";
    echo "<tr>";
    echo '<td style="width: 10%;">';
    echo __(" ");
    echo '</td>';
    echo '<td style="width: 20%;">';
    echo __(' ');
    echo '</td>';
    echo '<td style="width: 20%;">';
    echo __(' ');
    echo '</td>';
    echo "</tr>";
    echo '</table>';

    // $skills = $CurriculumGateway->getSkill($criteria);
    // $table = DataTable::createPaginated('skillManage', $criteria);

    // $table->addCheckboxColumn('id',__(''))
    //         ->setClass('chkbox')
    //         ->notSortable()
    //         ->format(function ($skills) {
    //             return "<input type='checkbox' class='skillId' name='skill_id[]'  data-id='".$skills["id"]."'>";
    //         });
    // $table->addColumn('name', __('Skill'));
    // $table->addColumn('', __('Skill Display Name'))
    // ->format(function ($skills) {
    // return "<input type='textbox'  name='skill_display_name' id='sname".$skills["id"]."' value='".$skills['name']."' style='border:1px solid gray'>";
    // });

    // if(!empty($subjects->data)){
    //    echo $table->render($skills);
    // }


}
?>

<style>
    .sortDiv {
        margin-bottom: 4px;
        padding: 10px 20px;
        font-size: 16px;
        background-color: #f3f3f3;
        cursor: move;
    }

    .multiselect-container {
        width: 158px !important;
    }

    .multiselect {
        height: 35px;
        width: 190px;
        margin-top: 2px;
    }

    .slt_chng_sub {
        width: 100%;
    }
</style>
<div id='subjectSortPanel'>
    <div class="w-100 mb-2 mt-2">
        <div class='float-left h2'>Subject Sorting</div>
        <div class='float-right'>
            <i class="fas fa-times-circle" style="font-size:24px;cursor:pointer;" onclick="closeSubjectSortPanel();"></i>
        </div>
        <div class='clearfix'></div>
    </div>
    <form id='sortForm' method='post'>
        <div class="row mb-2" id='subjSortId' style='margin:0;'>
            <?php
            $sub = $subjects->data;
            $clen = count($sub);
            $ci = 0;
            while ($ci < $clen) {
                $tabid = $sub[$ci]["pupilsightDepartmentID"];
                $tabTitle = $sub[$ci]["name"];
                echo "\n<div class='sortDiv w-100'><input type='hidden' name='subjects[]' value='" . $tabid . "' >" . ucwords($tabTitle) . "</div>";
                $ci++;
            }
            ?>
        </div>

        <input type="hidden" name="pupilsightSchoolYearID" value="<?= $pupilsightSchoolYearID; ?>">
        <input type="hidden" name="pupilsightProgramID" value="<?= $pupilsightProgramID; ?>">
        <input type="hidden" name="pupilsightYearGroupID" value="<?= $pupilsightYearGroupID; ?>">
        <input type="hidden" name="type" value="subjectSortTab">
        <input type="hidden" id='sortSubjectTable' name="val" value="">
    </form>
    <div class='w-100 mt-2'>
        <button type="button" class="btn btn-primary ml-2" onclick="saveSorting();">Save</button>
    </div>
</div>


<script>
    //sorting
    function subjectSorting() {
        $("#expore_tbl_wrapper, #stuListTable, #btnSubjSort").hide(200);
        $("#subjectSortPanel").show(200);
    }

    function closeSubjectSortPanel() {
        $("#subjectSortPanel").hide(200);
        $("#expore_tbl_wrapper, #stuListTable, #btnSubjSort").show(200);
    }

    $(function() {
        new Sortable(subjSortId, {
            animation: 150,
            ghostClass: 'blue-background-class'
        });
    });

    function saveSorting() {
        try {
            $("#sortSubjectTable").val("sortSubject");
            var frmData = $('#sortForm').serialize();
            var link = "ajax_custom_data.php";
            $.ajax({
                type: "POST",
                url: link,
                data: frmData,
            }).done(function(msg) {
                console.log(msg);
                if (msg) {
                    var obj = jQuery.parseJSON(msg);
                    if (obj.status == 1) {
                        alert("Your request has been successfully executed");
                        location.reload();
                    } else {
                        if (obj.message) {
                            alert(obj.message);
                        }
                    }
                }
            });
        } catch (ex) {
            console.log(ex);
        }
    }
</script>
<script type="text/javascript">


    $(function() {
        //$("#expore_tbl_wrapper").hide();
        $("#subjectSortPanel").hide();//sorting panel hide
    });

    $(document).on('change', '.slt_chng_sub', function() {
        $("#copySubjectToClass").hide();
        $("#saveSubjectToClass").hide();
    });
</script>
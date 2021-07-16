<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Forms\DatabaseFormFactory;

use Pupilsight\Domain\Departments\DepartmentGateway;
//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/department_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('Manage Subjects'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }


    echo '<h3>';
    echo __('Subjects');
    echo '</h3>';

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    if ($pupilsightSchoolYearID != $_SESSION[$guid]['pupilsightSchoolYearID']) {
        try {
            $data = array('pupilsightSchoolYearID' => $_GET['pupilsightSchoolYearID']);
            $sql = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>" . $e->getMessage() . '</div>';
        }
        if ($result->rowcount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            $row = $result->fetch();
            $pupilsightSchoolYearID = $row['pupilsightSchoolYearID'];
            $pupilsightSchoolYearName = $row['name'];
        }
    }

    $sqlq = 'SELECT * FROM pupilsightDepartmentType ORDER BY id ASC';
    $resultval = $connection2->query($sqlq);
    $rowdata = $resultval->fetchAll();

    if (!empty($rowdata)) {
        $types = array();
        $types2 = array();
        $types1 = array('' => 'Select Type');
        foreach ($rowdata as $rd) {
            $types2[$rd['name']] = $rd['name'];
        }
        $types = $types1 + $types2;
    } else {
        $types = array(
            'Scholastic' => __('Scholastic'),
            'Co-Scholastic' => __('Co-Scholastic'),
            'Part A' => __('Part A'),
            'Part B' => __('Part B'),
            'Learning Area' => __('Learning Area'),
            'Administration' => __('Administration'),
        );
    }

    if ($_POST) {
        if (!empty($_POST['search'])) {
            $serchName = $_POST['search'];
        } else {
            $serchName = '';
        }
        if (!empty($_POST['type'])) {
            $serchType = $_POST['type'];
        } else {
            $serchType = '';
        }
    } else {
        $serchName = '';
        $serchType = '';
        unset($_SESSION['serchType']);
    }

    if (!empty($serchType)) {
        $serchType = $_POST['type'];
        $_SESSION['serchType'] = $serchType;
    }

    $searchform = Form::create('searchForm', '');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('type', __('Type'));
    $col->addSelect('type')->fromArray($types)->selected($serchType);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('search', __('Subject Name'));
    $col->addTextField('search')->placeholder('Search by Subject Name')->addClass('txtfield')->setValue($serchName);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __(''));

    $col->addSearchSubmit($pupilsight->session, __('Clear Search'));
    //$col->addContent('<button id="submitInvoice"  class=" btn btn-primary">Search</button>');
    echo $searchform->getOutput();



    $departmentGateway = $container->get(DepartmentGateway::class);

    // QUERY
    //echo $serchName;
    $criteria = $departmentGateway->newQueryCriteria()
        ->pageSize(5000)
        ->sortBy('name')
        //->searchBy($search)
        ->searchBy('name', $serchName)
        ->fromPOST();

    if (isset($_SESSION['serchType'])) {
        $serchType = $_SESSION['serchType'];
    } else {
        $serchType = '';
    }
    $departments = $departmentGateway->queryDepartments($criteria, $serchType);

    // DATA TABLE
    $table = DataTable::createPaginated('departmentManage', $criteria);

    // $table->addHeaderAction('Assign Subjects to Class', __('Assign Subjects to class'))
    //     ->setClass('btn btn-white')
    //     ->setURL('/modules/Academics/assign_subjects_class_add.php')
    //     ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
    //     ->addParam('search', $criteria->getSearchText(true))
    //     ->displayLabel();

    // $table->addHeaderAction('addnew', __('Add Subject'))
    //     ->setClass('btn btn-white')
    //     ->setURL('/modules/Academics/department_manage_add.php')
    //     ->displayLabel();

    echo "<div style='height:50px;'><div class='float-right mb-2'>
    <a href='index.php?q=/modules/Academics/assign_subjects_class_add.php&pupilsightSchoolYearID=".$pupilsightSchoolYearID."' class=' btn btn-white mr-1'>Assign Subjects to class</a>
    <a href='index.php?q=/modules/Academics/department_manage_add.php' class=' btn btn-white mr-1'>Add Subject</a>
    <a id='deleteBulkSubject' class='btn btn-white mr-1'>Bulk Delete</a>
    <a class='btn btn-white mr-1' title='Default Import Template'  href='public/report_template/subject_import_default_template.csv' >Default Template</a>
    <a class='btn btn-white mr-1' href='index.php?q=/modules/Academics/import_subject.php' title='Import Csv'  >Import</a>
    <a class='btn btn-white mr-1' id='expore_skill_xl' title='Export Excel'  >Export</a>
    <div class='float-none'></div></div></div>";  

    $table->addCheckboxColumn('pupilsightDepartmentID', __(''))
                ->setClass('chkbox')
                ->notSortable();
    $table->addColumn('name', __('Name'));
    $table->addColumn('type', __('Type'))->translatable();
    $table->addColumn('nameShort', __('Subject Code'));
    // $table->addColumn('staff', __('Staff'))
    //     ->sortable(false)
    //     ->format(function($row) use ($departmentGateway) {
    //         $staff = $departmentGateway->selectStaffByDepartment($row['pupilsightDepartmentID'])->fetchAll();
    //         return (!empty($staff)) 
    //             ? Format::nameList($staff, 'Staff', true, true)
    //             : '<i>'.__('None').'</i>';
    //     });

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightDepartmentID')
        ->format(function ($department, $actions) {
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/Academics/department_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/Academics/department_manage_delete.php');
        });

    echo $table->render($departments);
}

?>

<script>
    $(document).ready(function() {
        window.setTimeout(function () {
            $('#expore_tbl').find("input[name='pupilsightDepartmentID[]']").each(function() {
                $(this).addClass('include_cell');
                $(this).closest('tr').addClass('rm_cell');
            });
        }, 1000);
        


        $(document).on('change', '.include_cell', function() {
            if ($(this).is(":checked")) {
                $(this).closest('tr').removeClass('rm_cell');
            } else {
                $(this).closest('tr').addClass('rm_cell');
            }
        });

    });

    $(document).on('click', '#deleteBulkSubject', function() {
        var favorite = [];
        var chk = [];
        var chkname = [];
        $.each($("input[name='pupilsightDepartmentID[]']:checked"), function() {
            favorite.push($(this).val());
        });
        var sklId = favorite.join(",");
        if (sklId) {
            var val = sklId;
            var type = 'deleteBulkSubject';
            if (val != '') {
                if (confirm("Are you sure want to Delete Subjects?")) {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: {
                            val: val,
                            type: type
                        },
                        async: true,
                        success: function(response) {
                            toast('success','Subject Deleted Successfully!');
                            location.reload();
                        }
                    });
                }
            }
        } else {
            toast('error','You Have to Select Subject.');
        }
    });

    $(document).on('click', '#expore_skill_xl', function () {
        var submit_ids = [];
        $.each($("input[name='pupilsightDepartmentID[]']:checked"), function () {
            submit_ids.push($(this).val());
        });
        var submt_id = submit_ids.join(",");

        if (submt_id == '') {
            toast('error','You Have to Select Subject.');
        } else {
            $("#expore_tbl tr").each(function () {
                $(this).find("th:last").remove();
                $(this).find("td:last").remove();
                $(this).find("th:first").remove();
                $(this).find("td:first").remove();
            });
            $("#expore_tbl").table2excel({
                name: "Worksheet Name",
                filename: "Subject.xls",
                fileext: ".xls",
                exclude: ".checkall",
                exclude: ".rm_cell",
                exclude_inputs: true,
                columns: [0, 1, 2, 3, 4, 5]

            });
            location.reload();
        }
    });
</script>
<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\SchoolYearGateway;

if (isActionAccessible($guid, $connection2, '/modules/Academics/ac_manage_skill.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Skills'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $schoolYearGateway = $container->get(SchoolYearGateway::class);

    // QUERY
    $criteria = $schoolYearGateway->newQueryCriteria()
        ->pageSize(1000)
        ->sortBy(['id'])
        ->fromPOST();

    $skills = $schoolYearGateway->getSkill($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('schoolYearManage', $criteria);

    echo "<div style='height:50px;'><div class='float-right mb-2'>
    <a href='fullscreen.php?q=/modules/Academics/ac_manage_skill_add.php' class='thickbox btn btn-white mr-1'>Add</a>
    <a id='deleteBulkSKill' class='btn btn-white mr-1'>Bulk Delete</a>
    <a class='btn btn-white mr-1' title='Default Import Template'  href='public/report_template/skill_import_default_template.csv' >Default Template</a>
    <a class='btn btn-white mr-1' href='index.php?q=/modules/Academics/import_skill.php' title='Import Csv'  >Import</a>
    <a class='btn btn-white mr-1' id='expore_skill_xl' title='Export Excel'  >Export</a>
    <div class='float-none'></div></div></div>";  
    
    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Academics/schoolYear_manage_add.php')
    //     ->displayLabel();
    $table->addCheckboxColumn('id', __(''))
                ->setClass('chkbox')
                ->notSortable();
    $table->addColumn('serial_number', __('SI No'));
    $table->addColumn('name', __('Name'));
    $table->addColumn('code', __('Code'));
   
    $table->addColumn('description', __('Description'))->translatable();
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($skills, $actions) {
            $actions->addAction('editnew', __('Edit'))
                    ->setURL('/modules/Academics/ac_manage_skill_edit.php');
                    
                    

            // if ($schoolYear['status'] != 'Current') {
                $actions->addAction('delete', __('Delete'))
                       ->setURL('/modules/Academics/ac_manage_skill_delete.php');
            // }
        });

    echo $table->render($skills);
}

?>

<script>
    $(document).ready(function() {
        window.setTimeout(function () {
            $('#expore_tbl').find("input[name='id[]']").each(function() {
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

    $(document).on('click', '#deleteBulkSKill', function() {
        var favorite = [];
        var chk = [];
        var chkname = [];
        $.each($("input[name='id[]']:checked"), function() {
            favorite.push($(this).val());
        });
        var sklId = favorite.join(",");
        if (sklId) {
            var val = sklId;
            var type = 'deleteBulkSKill';
            if (val != '') {
                if (confirm("Are you sure want to Delete Skills?")) {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: {
                            val: val,
                            type: type
                        },
                        async: true,
                        success: function(response) {
                            toast('success','Skill Deleted Successfully!');
                            location.reload();
                        }
                    });
                }
            }
        } else {
            toast('error','You Have to Select Skill.');
        }
    });

    $(document).on('click', '#expore_skill_xl', function () {
        var submit_ids = [];
        $.each($("input[name='id[]']:checked"), function () {
            submit_ids.push($(this).val());
        });
        var submt_id = submit_ids.join(",");

        if (submt_id == '') {
            toast('error','You Have to Select Skill.');
        } else {
            $("#expore_tbl tr").each(function () {
                $(this).find("th:last").remove();
                $(this).find("td:last").remove();
                $(this).find("th:first").remove();
                $(this).find("td:first").remove();
            });
            $("#expore_tbl").table2excel({
                name: "Worksheet Name",
                filename: "Skill.xls",
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
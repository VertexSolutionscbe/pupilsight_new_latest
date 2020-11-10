<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\RollGroups\RollGroupGateway;
use Pupilsight\Domain\School\SchoolYearGateway;
use Pupilsight\Forms\Prefab\BulkActionForm;
use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/rollGroup_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $pupilsightRollGroupID = $_GET['pupilsightRollGroupID'] ?? '';

    $page->breadcrumbs->add(__('Manage Section'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }


    if (isset($_GET["nameAlreadyExists"]) && $_GET["nameAlreadyExists"]) {
        echo "<script>alert('Duplicate section names were not Copied.')</script>";
    }

    $pupilsightSchoolYearID = isset($_REQUEST['pupilsightSchoolYearID']) ? $_REQUEST['pupilsightSchoolYearID'] : $_SESSION[$guid]['pupilsightSchoolYearID'];

    
    if (!empty($pupilsightSchoolYearID)) {
        $schoolYearGateway = $container->get(SchoolYearGateway::class);
        $targetSchoolYear = $schoolYearGateway->getSchoolYearByID($pupilsightSchoolYearID);

        echo '<h2>';
        echo $targetSchoolYear['name'];
        echo '</h2>';

        echo "<div class='linkTop'>";
        if ($prevSchoolYear = $schoolYearGateway->getPreviousSchoolYearByID($pupilsightSchoolYearID)) {
            echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . '&pupilsightSchoolYearID=' . $prevSchoolYear['pupilsightSchoolYearID'] . "'>" . __('Previous Year') . '</a> ';
        } else {
            echo __('Previous Year') . ' ';
        }
        echo ' | ';
        if ($nextSchoolYear = $schoolYearGateway->getNextSchoolYearByID($pupilsightSchoolYearID)) {
            echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=' . $_GET['q'] . '&pupilsightSchoolYearID=' . $nextSchoolYear['pupilsightSchoolYearID'] . "'>" . __('Next Year') . '</a> ';
        } else {
            echo __('Next Year') . ' ';
        }
        echo '<a style="margin-left:5px;float:right;cursor:pointer;" class="btn btn-primary" id="deleteBulkSection">Delete</a>
        <a class="btn btn-primary" id="btnRight" href="index.php?q=%2Fmodules%2FSchool+Admin%2FrollGroup_manage_add.php&amp;pupilsightSchoolYearID=028">Add </a>
        ';
        echo '</div>';
    }

    $rollGroupGateway = $container->get(RollGroupGateway::class);

    // QUERY
    $criteria = $rollGroupGateway->newQueryCriteria()
        ->pageSize(1000)
        ->sortBy(['sequenceNumber', 'pupilsightRollGroup.name'])
        ->fromPOST();

    $rollGroups = $rollGroupGateway->queryRollGroups($criteria, $pupilsightSchoolYearID);

    $formatTutorsList = function ($row) use ($rollGroupGateway) {
        $tutors = $rollGroupGateway->selectTutorsByRollGroup($row['pupilsightRollGroupID'])->fetchAll();
        if (count($tutors) > 1) $tutors[0]['surname'] .= ' (' . __('Main Tutor') . ')';

        return Format::nameList($tutors, 'Staff', false, true);
    };

    /*
    //BEGIN: bulk action
    $form = BulkActionForm::create("bulkAction", $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/rollGroup_manageProcessBulk.php?action=bulkDelete');
    $form->addHiddenValue('search', $search);

    $bulkActions = array(
        "bulkDelete" => __("bulkDelete"),
    );

    $col = $form->createBulkActionColumn($bulkActions);
    $col->addSubmit(__('Go'));
    //END: bulk action
    */
    
    // DATA TABLE
    $table = DataTable::createPaginated('rollGroupManage', $criteria);

    if (!empty($nextSchoolYear)) {
        $table->addHeaderAction('copy', __('Copy All To Next Year'))
            ->setURL('/modules/School Admin/rollGroup_manage_copyProcess.php')
            ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->addParam('pupilsightSchoolYearIDNext', $nextSchoolYear['pupilsightSchoolYearID'])
            ->setIcon('copy')
            ->onClick('return confirm("' . __('Are you sure you want to continue?') . ' ' . __('This operation cannot be undone.') . '");')
            ->displayLabel()
            ->directLink();
    }

    // $table->addHeaderAction('add', __('Add'))
    //     ->setID('btnRight')
    //     ->setURL('/modules/School Admin/rollGroup_manage_add.php')
    //     ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
    //     ->displayLabel();

    


    $table->addCheckboxColumn("pupilsightRollGroupID", __(""))
        ->setClass("chkbox")
        ->notSortable();

    $table->addColumn('name', __('Name'));

    $table->addColumn('nameShort', __('Short Name'));
        // ->description(__('Short Name'))
        // ->format(function ($rollGroup) {
        //     return '<strong>' . $rollGroup['nameShort'] . '</strong><br/><small><i>' . $rollGroup['nameShort'] . '</i></small>';
        // });

    // $table->addColumn('tutors', __('Form Tutors'))->sortable(false)->format($formatTutorsList);
    // $table->addColumn('space', __('Location'));
    // $table->addColumn('website', __('Website'))
    //     ->format(Format::using('link', ['website']));

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightRollGroupID')
        ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)

        ->format(function ($rollGroup, $actions) {
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/School Admin/rollGroup_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/School Admin/rollGroup_manage_delete.php');
        });

    echo $table->render($rollGroups);
}

?>

<script>
    $(document).on('click', '#deleteBulkSection', function () {
        var favorite = [];
        $.each($("input[name='pupilsightRollGroupID[]']:checked"), function () {
            favorite.push($(this).val());
        });
        var feeId = favorite.join(",");
        //alert(subid);
        if (feeId) {
            var val = feeId;
            var type = 'deleteBulkSection';
            if (val != '') {
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: val, type: type },
                    async: true,
                    success: function (response) {
                        alert('Sections Deleted Successfully!');
                        location.reload();
                    }
                });
            }
        } else {
            alert('You Have to Select Section.');
        }
    });
</script>
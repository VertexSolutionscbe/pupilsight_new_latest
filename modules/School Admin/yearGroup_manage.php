<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\YearGroupGateway;
use Pupilsight\Domain\School\SchoolYearGateway;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/yearGroup_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Class'));

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
        echo '</div>';
    }

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $yearGroupGateway = $container->get(YearGroupGateway::class);

    // QUERY
    $criteria = $yearGroupGateway->newQueryCriteria()
        ->pageSize(1000)
        ->sortBy(['sequenceNumber'])
        ->fromPOST();

    $yearGroups = $yearGroupGateway->queryYearGroups($criteria, $pupilsightSchoolYearID);

    // DATA TABLE
    $table = DataTable::createPaginated('yearGroupManage', $criteria);

    if (!empty($nextSchoolYear)) {
        $table->addHeaderAction('copy', __('Copy All To Next Year'))
            ->setURL('/modules/School Admin/yearGroup_manage_copyProcess.php')
            ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->addParam('pupilsightSchoolYearIDNext', $nextSchoolYear['pupilsightSchoolYearID'])
            ->setIcon('copy')
            ->onClick('return confirm("' . __('Are you sure you want to continue?') . ' ' . __('This operation cannot be undone.') . '");')
            ->displayLabel()
            ->directLink();
    }

    $table->addHeaderAction('add', __('Add'))
        ->setID('btnRight')
        ->setURL('/modules/School Admin/yearGroup_manage_add.php')
        ->displayLabel();

    $table->addColumn('sequenceNumber', __('sequenceNumber'));
    $table->addColumn('name', __('Name'));
    $table->addColumn('nameShort', __('Short Name'));
    // $table->addColumn('pupilsightPersonIDHOY', __('Head of Year'))
    //     ->format(function($values) {
    //         if (!empty($values['preferredName']) && !empty($values['surname'])) {
    //             return Format::name('', $values['preferredName'], $values['surname'], 'Staff', false, true);
    //         }
    //     });
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightYearGroupID')
        ->format(function ($facilities, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/School Admin/yearGroup_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/School Admin/yearGroup_manage_delete.php');
        });

    echo $table->render($yearGroups);

   
}

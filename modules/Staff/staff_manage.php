<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Forms\Prefab\BulkActionForm;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffGateway;

if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Staff'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $search = (isset($_GET['search']) ? $_GET['search'] : '');
    $allStaff = (isset($_GET['allStaff']) ? $_GET['allStaff'] : '');

    $staffGateway = $container->get(StaffGateway::class);

    // CRITERIA
    $criteria = $staffGateway->newQueryCriteria()
        ->searchBy($staffGateway->getSearchableColumns(), $search)
        ->filterBy('all', $allStaff)
        ->sortBy(['surname', 'preferredName'])
        ->fromPOST();

    echo '<h2>';
    echo __('Search & Filter');
    echo '</h2>';

    $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL']."/index.php", 'get');

    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/staff_manage.php");

    $row = $form->addRow();
        $row->addLabel('search', __('Search For'))->description(__('Preferred, surname, username.'));
        $row->addTextField('search')->setValue($criteria->getSearchText())->maxLength(20);

    $row = $form->addRow();
        $row->addLabel('allStaff', __('All Staff'))->description(__('Include Expected and Left.'));
        $row->addCheckbox('allStaff')->checked($allStaff);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    echo '<h2>';
    echo __('View');
    echo '</h2>';

    $staff = $staffGateway->queryAllStaff($criteria);

    // FORM
    $form = BulkActionForm::create('bulkAction', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/staff_manageProcessBulk.php?search='.$search.'&allStaff='.$allStaff);
    $form->addHiddenValue('search', $search);

    $bulkActions = array(
        'Left' => __('Mark as left'),
    );

    $col = $form->createBulkActionColumn($bulkActions);
        $col->addDate('dateEnd')
            ->required()
            ->placeholder(__('Date End'))
            ->setClass('shortWidth dateEnd');
        $col->addSubmit(__('Go'));

    $form->toggleVisibilityByClass('dateEnd')->onSelect('action')->when('Left');


    // DATA TABLE
    $table = $form->addRow()->addDataTable('staffManage', $criteria)->withData($staff);

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/Staff/staff_manage_add.php')
        ->addParam('search', $search)
        ->displayLabel();

    $table->modifyRows(function($person, $row) {
        if (!empty($person['status']) && $person['status'] != 'Full') $row->addClass('error');
        return $row;
    });

    $table->addMetaData('filterOptions', [
        'all:on'          => __('All Staff'),
        'type:teaching'   => __('Staff Type').': '.__('Teaching'),
        'type:support'    => __('Staff Type').': '.__('Support'),
        'type:other'      => __('Staff Type').': '.__('Other'),
        'status:full'     => __('Status').': '.__('Full'),
        'status:left'     => __('Status').': '.__('Left'),
        'status:expected' => __('Status').': '.__('Expected'),
    ]);

    $table->addMetaData('bulkActions', $col);

    // COLUMNS
    $table->addColumn('fullName', __('Name'))
        ->description(__('Initials'))
        ->width('35%')
        ->sortable(['surname', 'preferredName'])
        ->format(function($person) {
            return Format::name($person['title'], $person['preferredName'], $person['surname'], 'Staff', true, true)
                .'<br/><span style="font-size: 85%; font-style: italic">'.$person['initials']."</span>";
        });

    $table->addColumn('type', __('Staff Type'))->width('20%')->translatable();
    $table->addColumn('status', __('Status'))->width('10%')->translatable();
    $table->addColumn('jobTitle', __('Job Title'))->width('20%');

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightStaffID')
        ->addParam('search', $criteria->getSearchText(true))
        ->format(function ($person, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Staff/staff_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Staff/staff_manage_delete.php');
        });

    $table->addCheckboxColumn('pupilsightStaffID');

    echo $form->getOutput();
}

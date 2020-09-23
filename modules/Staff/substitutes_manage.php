<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\SubstituteGateway;

if (isActionAccessible($guid, $connection2, '/modules/Staff/substitutes_manage.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $page->breadcrumbs->add(__('Manage Substitutes'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $search = $_GET['search'] ?? '';

    $subGateway = $container->get(SubstituteGateway::class);

    // CRITERIA
    $criteria = $subGateway->newQueryCriteria()
        ->searchBy($subGateway->getSearchableColumns(), $search)
        ->sortBy('active')
        ->sortBy(['surname', 'preferredName'])
        ->fromPOST();

    // SEARCH FORM
    $form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setTitle(__('Search'));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/substitutes_manage.php');

    $row = $form->addRow();
        $row->addLabel('search', __('Search For'))->description(__('Preferred, surname, username.'));
        $row->addTextField('search')->setValue($criteria->getSearchText())->maxLength(20);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    $subs = $subGateway->queryAllSubstitutes($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('subsManage', $criteria);
    $table->setTitle(__('View'));

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/Staff/substitutes_manage_add.php')
        ->addParam('search', $search)
        ->displayLabel();

    $table->modifyRows(function ($person, $row) {
        if (!empty($person['status']) && $person['status'] != 'Full') $row->addClass('error');
        if ($person['active'] != 'Y') $row->addClass('error');
        return $row;
    });

    $table->addMetaData('filterOptions', [
        'active:Y'        => __('Active').': '.__('Yes'),
        'active:N'        => __('Active').': '.__('No'),
        'status:full'     => __('Status').': '.__('Full'),
        'status:left'     => __('Status').': '.__('Left'),
        'status:expected' => __('Status').': '.__('Expected'),
    ]);

    // COLUMNS
    $table->addColumn('image_240', __('Photo'))
        ->width('10%')
        ->notSortable()
        ->format(Format::using('userPhoto', 'image_240'));

    $table->addColumn('fullName', __('Name'))
        ->width('35%')
        ->sortable(['surname', 'preferredName'])
        ->format(function ($person) {
            $name = Format::name($person['title'], $person['preferredName'], $person['surname'], 'Staff', true, true);
            $url = !empty($person['pupilsightStaffID'])
                ? './index.php?q=/modules/Staff/staff_view_details.php&pupilsightPersonID='.$person['pupilsightPersonID']
                : '';

            return Format::link($url, $name).'<br/>'.Format::small($person['type']);
        });

    $table->addColumn('details', __('Details'));

    $table->addColumn('active', __('Active'))
        ->width('10%')
        ->format(Format::using('yesNo', 'active'));

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightSubstituteID')
        ->addParam('pupilsightPersonID')
        ->addParam('search', $criteria->getSearchText(true))
        ->format(function ($person, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Staff/substitutes_manage_edit.php');

            $actions->addAction('availability', __('Edit Availability'))
                    ->setIcon('planner')
                    ->setURL('/modules/Staff/coverage_availability.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Staff/substitutes_manage_delete.php');
        });

    echo $table->render($subs);
}

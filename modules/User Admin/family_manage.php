<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\User\FamilyGateway;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/family_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Set returnTo point for upcoming pages
    //Proceed!
    $page->breadcrumbs->add(__('Manage Families'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $search = isset($_GET['search'])? $_GET['search'] : '';

    $familyGateway = $container->get(FamilyGateway::class);

    // QUERY
    $criteria = $familyGateway->newQueryCriteria()
        ->searchBy($familyGateway->getSearchableColumns(), $search)
        ->sortBy(['name'])
        ->fromPOST();

    echo '<h2>';
    echo __('Search');
    echo '</h2>';

    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/family_manage.php');

    $row = $form->addRow();
        $row->addLabel('search', __('Search For'))->description(_('Family Name'));
        $row->addTextField('search')->setValue($criteria->getSearchText());

    $row = $form->addRow();
        $row->addSearchSubmit($pupilsight->session, __('Clear Search'));

    echo $form->getOutput();

    echo '<h2>';
    echo __('View');
    echo '</h2>';

    // QUERY
    $families = $familyGateway->queryFamilies($criteria);

    $familyIDs = $families->getColumn('pupilsightFamilyID');
    $adults = $familyGateway->selectAdultsByFamily($familyIDs)->fetchGrouped();
    $families->joinColumn('pupilsightFamilyID', 'adults', $adults);

    $children = $familyGateway->selectChildrenByFamily($familyIDs)->fetchGrouped();
    $families->joinColumn('pupilsightFamilyID', 'children', $children);

    // DATA TABLE
    $table = DataTable::createPaginated('familyManage', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/User Admin/family_manage_add.php')
        ->addParam('search', $search)
        ->displayLabel();

    $table->addColumn('name', __('Name'));
    $table->addColumn('status', __('Marital Status'))->translatable();
    $table->addColumn('adults', __('Adults'))
        ->notSortable()
        ->format(function($row) {
            array_walk($row['adults'], function(&$person) {
                if ($person['status'] == 'Left' || $person['status'] == 'Expected') {
                    $person['surname'] .= ' <i>('.__($person['status']).')</i>';
                }
            });
            return Format::nameList($row['adults'], 'Parent');
        });
    $table->addColumn('children', __('Children'))
        ->notSortable()
        ->format(function($row) {
            array_walk($row['children'], function(&$person) {
                if ($person['status'] == 'Left' || $person['status'] == 'Expected') {
                    $person['surname'] .= ' <i>('.__($person['status']).')</i>';
                }
            });
            return Format::nameList($row['children'], 'Student');
        });

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightFamilyID')
        ->addParam('search', $criteria->getSearchText(true))
        ->format(function ($family, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/User Admin/family_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/User Admin/family_manage_delete.php');
        });

    echo $table->render($families);
}

<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\User\UserFieldGateway;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/userFields.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Custom Fields'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $userFieldGateway = $container->get(UserFieldGateway::class);
    
    // QUERY
    $criteria = $userFieldGateway->newQueryCriteria()
        ->sortBy('name')
        ->fromPOST();

    $userFields = $userFieldGateway->queryUserFields($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('userFieldManage', $criteria);

    $table->modifyRows(function ($userField, $row) {
        if ($userField['active'] == 'N') $row->addClass('error');
        return $row;
    });

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/User Admin/userFields_add.php')
        ->displayLabel();

    $table->addMetaData('filterOptions', [
        'active:Y'     => __('Active').': '.__('Yes'),
        'active:N'     => __('Active').': '.__('No'),
        'role:student' => __('Role').': '.__('Student'),
        'role:parent'  => __('Role').': '.__('Parent'),
        'role:staff'   => __('Role').': '.__('Staff'),
        'role:other'   => __('Role').': '.__('Other'),
    ]);

    $customFieldTypes = array(
        'varchar' => __('Short Text'),
        'text'    => __('Long Text'),
        'date'    => __('Date'),
        'url'     => __('Link'),
        'select'  => __('Dropdown')
    );

    $table->addColumn('name', __('Name'));
    $table->addColumn('type', __('Type'))->format(function ($row) use ($customFieldTypes) {
        return isset($customFieldTypes[$row['type']])? $customFieldTypes[$row['type']] : '';
    });
    $table->addColumn('active', __('Active'))->format(Format::using('yesNo', 'active'));
    $table->addColumn('roles', __('Role Categories'))
        ->notSortable()
        ->format(function ($row) {
            $output = '';
            if ($row['activePersonStudent']) $output .= __('Student').'<br/>';
            if ($row['activePersonParent']) $output .= __('Parent').'<br/>';
            if ($row['activePersonStaff']) $output .= __('Staff').'<br/>';
            if ($row['activePersonOther']) $output .= __('Other').'<br/>';
            return $output;
        });

    $table->addActionColumn()
        ->addParam('pupilsightPersonFieldID')
        ->format(function ($row, $actions) {
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/User Admin/userFields_edit.php');
            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/User Admin/userFields_delete.php');
            
        });
        
    echo $table->render($userFields);
}

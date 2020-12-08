<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Finance\FeesGateway;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_item_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {


    if (!empty($_POST['search'])) {
        $search =  $_POST['search'];
    } else {
        $search = '';
    }

    //Proceed!
    $page->breadcrumbs->add(__('Manage Fee Item'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $FeesGateway = $container->get(FeesGateway::class);

    // QUERY
    $criteria = $FeesGateway->newQueryCriteria()
        ->pageSize(5000)
        ->sortBy(['id'])
        ->fromPOST();

    $yearGroups = $FeesGateway->getFeesItem($criteria, $search, $pupilsightSchoolYearID);

    // DATA TABLE
    $table = DataTable::createPaginated('FeeItemManage', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Finance/program_manage_add.php')
    //     ->displayLabel();

    echo "<div style='height:50px;'><div class='float-right mb-2'>
    <a href='index.php?q=/modules/Finance/fee_item_manage_add.php' class=' btn btn-primary'>Add</a>&nbsp;&nbsp;<a id='cop_feeitem' class='btn btn-primary'>copy</a>
    <a href='fullscreen.php?q=/modules/Finance/fee_item_manage_copy.php' id='feeitem_copy' class='thickbox btn btn-primary'style='display:none' >copybutton</a></div><div class='float-none'></div></div>";

    $searchform = Form::create('searchForm', '');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');

    $row = $searchform->addRow()->setId('normalSearchRow');
    $col = $row->addColumn()->setClass('newdes');
    $col->addTextField('search')->placeholder('Search by Name, Code')->addClass('txtfield')->setValue($search);

    $col = $row->addColumn()->setClass('newdes');
    $col->addContent('<button class="transactionButton btn btn-primary">Search</button>');
    echo $searchform->getOutput();

    //$table->addColumn('sequenceNumber', __('sequenceNumber'));
    $table->addCheckBoxColumn('id', __(''));
    $table->addColumn('serial_number', __('SI No'));
    $table->addColumn('name', __('Fee Item Name'));
    $table->addColumn('code', __('Fee Item Code'));
    $table->addColumn('acedemic_year', __('Acedemic Year'));
    $table->addColumn('fee_item_type', __('Item Type'));


    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($facilities, $actions) use ($guid) {

            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/Finance/fee_item_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/Finance/fee_item_manage_delete.php');
        });

    echo $table->render($yearGroups);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}

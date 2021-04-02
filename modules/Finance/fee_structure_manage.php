<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_structure_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Fee Structure'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }


    $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resultval = $connection2->query($sqlq);
    $rowdata = $resultval->fetchAll();
    $academic = array();
    $ayear = '';
    if (!empty($rowdata)) {
        $ayear = $rowdata[0]['name'];
        foreach ($rowdata as $dt) {
            $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
        }
    }

    if ($_POST) {
        $input = $_POST;
        $name =  $_POST['name'];
        $fn_fees_head_id = $_POST['fn_fees_head_id'];
    } else {
        $input = '';
        $name = '';
        $fn_fees_head_id = '';
        unset($_SESSION['fn_fees_head_id_search']);
    }

    if (!empty($fn_fees_head_id)) {
        $_SESSION['fn_fees_head_id_search'] = $fn_fees_head_id;
    }

    $sqlah = 'SELECT id, name FROM fn_fees_head WHERE pupilsightSchoolYearID = ' . $pupilsightSchoolYearID . ' ';
    $resultah = $connection2->query($sqlah);
    $rowdataAcctHead = $resultah->fetchAll();

    $accthead = array();
    $accthead2 = array();
    $accthead1 = array('' => 'Select Account Head');
    foreach ($rowdataAcctHead as $dt) {
        $accthead2[$dt['id']] = $dt['name'];
    }
    $accthead = $accthead1 + $accthead2;


    $searchform = Form::create('searchForm', '');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('name', __('Structure Name'));
    $col->addTextField('name')->setID('strname')->setValue($name)->addClass('txtfield');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('fn_fees_head_id', __('Account Head'));
    $col->addSelect('fn_fees_head_id')->fromArray($accthead)->selected($fn_fees_head_id)->addClass('txtfield');



    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __(''));
    $col->addContent('<button class=" btn btn-primary">Search</button>');

    echo $searchform->getOutput();


    $FeesGateway = $container->get(FeesGateway::class);
    $criteria = $FeesGateway->newQueryCriteria()
        ->pageSize(5000)
        ->sortBy(['id'])
        ->fromPOST();

    //print_r($criteria);

    $yearGroups = $FeesGateway->getFeeStructure($criteria, $pupilsightSchoolYearID, $input);
    $table = DataTable::createPaginated('FeeStructureManage', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Finance/program_manage_add.php')
    //     ->displayLabel();

    echo "<div style='height:50px;'><div class='float-right mb-2'><a id='showfee_assign_class' style='display:none' href='fullscreen.php?q=/modules/Finance/fee_structure_assignClass_manage_add.php' class='thickbox btn btn-primary' class='thickbox btn btn-primary'>Assign To Class hidden</a><a id='fee_assign_class' class='btn btn-primary'>Assign To Class</a>&nbsp;&nbsp;<a href='index.php?q=/modules/Finance/fee_structure_assign_student_manage.php' class='btn btn-primary'>Assign Students</a>";
    echo "&nbsp;&nbsp;<a href='index.php?q=/modules/Finance/fee_structure_manage_add.php' class='btn btn-primary'>Add Fee Structure</a></div><div class='float-none'></div></div>";


    $table->addCheckboxColumn('id', __(''))
        ->setClass('chkbox')
        ->context('Select');
    $table->addColumn('serial_number', __('SI No'));
    $table->addColumn('name', __('Name'));
    $table->addColumn('acedemic_year', __('Academic Year'));
    $table->addColumn('invoice_title', __('Title of Invoice'));
    $table->addColumn('totalamount', __('Fee Amount'));
    $table->addColumn('account_head', __('Account Head'));
    $table->addColumn('kountitem', __('Total Fee Item'));



    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($facilities, $actions) use ($guid) {
            $actions->addAction('copynew', __('Copy'))
                ->setURL('/modules/Finance/fee_structure_manage_copy.php');

            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/Finance/fee_structure_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/Finance/fee_structure_manage_delete.php');

            $actions->addAction('assign', __('Assign to Class'))
                ->setURL('/modules/Finance/fee_structure_assign_manage.php');
        });

    echo $table->render($yearGroups);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}

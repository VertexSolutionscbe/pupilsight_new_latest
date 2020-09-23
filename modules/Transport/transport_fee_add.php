<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_fee_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Transport Fee'), 'transport_fee.php')
        ->add(__('Add Transport fee'));


    // if (isset($_GET['return'])) {
    //     returnProcess($guid, $_GET['return'], $editLink, null);
    // }
    echo '<h2>';
    echo __('Add Transport Fee');
    echo '</h2>';
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();
    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }

    $fee_head = array();
    $sqlr = 'SELECT * FROM fn_fees_head';

    $resultr = $connection2->query($sqlr);
    $feehead = $resultr->fetchAll();
    $fee_head = array();
    $fee_head1 = array('' => 'Select Fee Head');
    $fee_head2 = array();

    foreach ($feehead as $dt) {
        $fee_head2[$dt['id']] = $dt['name'];
    }
    $fee_head = $fee_head1 + $fee_head2;

    $sqls = 'SELECT * FROM fn_fee_series';
    $results = $connection2->query($sqls);
    $feeseries  = $results->fetchAll();

    if (!empty($feeseries)) {
        $fee_series = array();
        $fee_series1 = array('' => 'Select Fee Series');
        foreach ($feeseries as $dt) {
            $fee_series2[$dt['id']] = $dt['series_name'];
        }
        $fee_series = $fee_series1 + $fee_series2;
    }
    $Receipt_series =  array('' => 'Select  Receipt Series');

    $sqlf = 'SELECT * FROM fn_fee_items';
    $resultf = $connection2->query($sqlf);

    $feeItem = $resultf->fetchAll();

    $Fee_item = array();
    $Fee_item1 = array('' => 'Select Fee Item');
    $Fee_item2 = array();
    
    foreach ($feeItem as $dt) {
        $Fee_item2[$dt['id']] = $dt['name'];
    }
    $Fee_item = $Fee_item1 + $Fee_item2;


    $sqlrt = 'SELECT id, route_name FROM trans_routes';

    $resultrt = $connection2->query($sqlrt);
    $routesData = $resultrt->fetchAll();
    $routes = array();
    $routes1 = array('' => 'Select Route');
    $routes2 = array();

    foreach ($routesData as $rt) {
        $routes2[$rt['id']] = $rt['route_name'];
    }
    $routes = $routes1 + $routes2;


    $type = array();
    $type =  array(
        '' => 'Select  Type',
        '1' => 'Monthly',
        '2' => 'Bimonthly',
        '3' => 'Quarterly',
        '6' => 'Half Yearly',
        '12' => 'Yearly'
    );

    $month = array(
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July ',
        'August',
        'September',
        'October',
        'November',
        'December',
    );

    $year = array_combine(range(date('Y'), date('Y') + 10), range(date('Y'), date('Y') + 10));


    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/transport_fee_addProcess.php')->addClass('newform');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);


    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('fee_item_id', __('Fee Item'));
    $col->addSelect('fee_item_id')->fromArray($Fee_item)->required();


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
    $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->required()->selected($pupilsightSchoolYearID);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('schedule_name', __('schedule Name'));
    $col->addTextField('schedule_name')->addClass('txtfield')->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('type', __('Type'));
    $col->addSelect('type')->setId('feetype')->fromArray($type)->required();

    $row = $form->addRow()->setId('hiderow');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('start_year', __('Start Year'));
    $col->addSelect('start_year')->fromArray($year)->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('start_month', __('Start Month'));
    $col->addSelect('start_month')->fromArray($month)->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('end_year', __('End Year'));
    $col->addSelect('end_year')->fromArray($year)->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('end_month', __('End Month'));
    $col->addSelect('end_month')->fromArray($month)->required();


    $row = $form->addRow()->setID('due_day');
    $row = $form->addRow()->setId('hiderow');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('route_id', __('Route'));
    $col->addSelect('route_id')->fromArray($routes)->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('fee_head_id', __('Account Head'));
    $col->addSelect('fee_head_id')->fromArray($fee_head)->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __(''));
    //$col->addSelect('')->fromArray($fee_series)->required(); 

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __(''));
    $col->addSubmit();
    //$col->addSelect('receipt_series_id')->fromArray($Receipt_series);



    // $row = $form->addRow();
    // $row->addFooter();
    // $row->addSubmit();


    echo $form->getOutput();
}

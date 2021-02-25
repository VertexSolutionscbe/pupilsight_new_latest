<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$id = $_GET['id'];

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_fee_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    //Check if school year specified
    $page->breadcrumbs
    ->add(__('Transport Fee'), 'transport_fee.php')
    ->add(__('Edit Transport fee'));
    echo '<h2>';
    echo __('Edit Transport Fee');
    echo '</h2>';

    if ($id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM trans_schedule WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
           
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            $values = $result->fetch();

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
            $fee_head1 = array(''=>'Select Fee Head');
            $fee_head2 = array();
            foreach ($feehead as $dt) {
                $fee_head2[$dt['id']] = $dt['name'];
            }
            $fee_head = $fee_head1+$fee_head2;

            $sqls = 'SELECT * FROM fn_fee_series';
            $results = $connection2->query($sqls);
            $feeseries  = $results->fetchAll();
            // /print_r($feeseries);die();
            $fee_series = array();
            $fee_series1 = array(''=>'Select Fee Series');
           foreach($feeseries as $dt){
                $fee_series2[$dt['id']] = $dt['series_name'];
            }
            $fee_series = $fee_series1 +$fee_series2 ;

            $receipt_series_id = array();
            $receipt_series_id =  array(''=>'Select  Receipt Series');

            $sqlf = 'SELECT * FROM fn_fee_items';
            $resultf = $connection2->query($sqlf);
            $feeItem = $resultf->fetchAll();
            $Fee_item = array();
            $Fee_item1 = array(''=>'Select Fee Item'); 
            $Fee_item2 = array();
            // print_r($feeItem) ;die(); 
            foreach($feeItem as $dt){
                $Fee_item2[$dt['id']] = $dt['name'];
            }
            $Fee_item = $Fee_item1 + $Fee_item2 ;

            $sqlrt = 'SELECT id, route_name FROM trans_routes';

            $resultrt = $connection2->query($sqlrt);
            $routesData = $resultrt->fetchAll();
            $routes = array();
            $routes1 = array(''=>'Select Route');
            $routes2 = array();

            foreach ($routesData as $rt) {
                $routes2[$rt['id']] = $rt['route_name'];
            }
            $routes = $routes1+$routes2;

            $type = array();
            $type =  array(''=>'Select  Type',                
                '1'=>'Monthly',
                '2'=>'Bimonthly',
                '3'=>'Quarterly',
                '6'=>'Half Yearly',
                '12' =>'Yearly'
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
            $year = array_combine(range(date('Y'), date('Y')+10), range(date('Y'), date('Y')+10));
        }
    }
            $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/transport_fee_editProcess.php')->addClass('newform');
            $form->setFactory(DatabaseFormFactory::create($pdo));
        
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('id', $id);
        
           
            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('fee_item_id', __('Fee Item'));
                    $col->addSelect('fee_item_id')->fromArray($Fee_item)->required()->selected($values['fee_item_id']);
              
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
                    $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->required()->selected($pupilsightSchoolYearID);    
        
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('schedule_name', __('schedule Name'));
                    $col->addTextField('schedule_name')->addClass('txtfield')->setValue($values['schedule_name'])->required();
        
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('type', __('Type'));
                    $col->addSelect('type')->setId('feetype')->fromArray($type)->required()->selected($values['type']);    
           
                $row = $form->addRow()->setId('hiderow');
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('start_year', __('Start Year'));
                    $col->addSelect('start_year')->fromArray($year)->required()->selected($values['start_year']);    
        
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('start_month', __('Start Month'));
                    $col->addSelect('start_month')->fromArray($month)->required()->selected($values['start_month']);
        
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('end_year', __('End Year'));
                    $col->addSelect('end_year')->fromArray($year)->required()->selected($values['end_year']);
        
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('end_month', __('End Month'));
                    $col->addSelect('end_month')->fromArray($month)->required()->selected($values['end_month']); 
                    
        
                $row = $form->addRow()->setID('due_day');
                $row = $form->addRow()->setId('hiderow');

                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('route_id', __('Route'));
                    $col->addSelect('route_id')->fromArray($routes)->selected($values['route_id'])->required(); 

                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('fee_head_id', __('Account Head'));
                    $col->addSelect('fee_head_id')->fromArray($fee_head)->required()->selected($values['fee_head_id']);
        
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('', __(''));
                    //$col->addSelect('')->fromArray($fee_series)->required(); 
        
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('', __(''));
                    $col->addSubmit();

            echo $form->getOutput();

}

?>
<script>
$( document ).ready(function() {

    var ftype=$("#feetype").val();
    //alert( ftype );

    if (ftype != '') {
        var val = ftype;
        $('#due_day').empty();
        $('#hiderow').show();
        if (val == '1') {
            $("#due_day").append("<td style='color: #666;font-weight: bold !important;'>Due <input name='due_date' readonly style='width:55px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' type='number' value='5' min='1' max='15'>Day Of First Month</td><td style='color: #666;font-weight: bold !important;'>Number Of Invoice <input style='width:55px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' name='total_invoice_generate'  type='number' value='12' readonly></td><br>");
        } else if (val == '2') {
            $("#due_day").append("<td style='color: #666;font-weight: bold !important;'>Due <input name='due_date' readonly style='width:55px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' type='number' value='5' min='1' max='15'>Day Of First Month</td><td style='color: #666;font-weight: bold !important;'>Number Of Invoice <input style='width:55px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' name='total_invoice_generate'  type='number' value='6' readonly></td><br>");
        } else if (val == '3') {
            $("#due_day").append("<td style='color: #666;font-weight: bold !important;'>Due <input  name='due_date' readonly style='width:55px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' type='number' value='5' min='1' max='15'>Day Of First Month</td><td style='color: #666;font-weight: bold !important;'>Number Of Invoice <input style='width:55px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' name='total_invoice_generate' type='number' value='4' readonly></td><br>");
        } else if (val == '6') {
            $("#due_day").append("<td style='color: #666;font-weight: bold !important;'>Due <input  name='due_date' readonly style='width:55px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' type='number' value='5' min='1' max='15'>Day Of First Month</td><td style='color: #666;font-weight: bold !important;'>Number Of Invoice <input style='width:55px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' name='total_invoice_generate' type='number' value='2' readonly></td><br>");
        } else if (val == '12') {
            $("#due_day").append("<td style='color: #666;font-weight: bold !important;'></td><td style='color: #666;font-weight: bold !important;'>Number Of Invoice <input style='width:55px; font-weight: bold; border: 1px solid;color:#666; margin: 10px;' name='total_invoice_generate' type='number' value='1' readonly></td><br>");
        }
    }
});
</script>
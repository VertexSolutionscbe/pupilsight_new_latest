<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_series_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Series'), 'fee_series_manage.php')
        ->add(__('Add Fee Series'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_series_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Add Fee Series');
    echo '</h2>';

    
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();

    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }
    
    $form = Form::create('feeseries', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_series_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
        $row->addLabel('pupilsightSchoolYearID', __('Academic Year'));
        $row->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($pupilsightSchoolYearID)->required();

    $row = $form->addRow();
        $row->addLabel('series_name', __('Series Name'))->description(__('Must be unique.'));
        $row->addTextField('series_name')->required();

    $row = $form->addRow();
        $row->addLabel('description', __('Description'));
        $row->addTextField('description');

    
    $row = $form->addRow();
        //$row->addLabel('description', __('Add Format Data'));
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('format', __('Format *'))->description(__('Must be unique.'));
        $col->addContent('');

        $col = $row->addColumn()->setClass('newdes');
        //$col->addTextField('format')->addClass('txtfield')->required()->readonly();
        $col->addContent('<div style="display:inline-flex;"><input type="text" class="form-control" name="format" id="format" value="" readonly><input type="hidden" name="formatval" id="formatval" value=""><i id="delFormatData" style="cursor:pointer; font-size: 25px;margin: 5px 5px;" class="mdi mdi-close-circle mdi-24px"></i></div>');
    
    $row = $form->addRow();
        //$row->addLabel('description', __('Add Format Data'));
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('', __('Add Format Data'));
        $col->addContent('');

        $col = $row->addColumn()->setClass('newdes');
        $col->addContent('<a id="creatNum" class="transactionButton btn btn-primary">Add Number</a><a id="creatChar" style="margin:0px 4px;" class="transactionButton btn btn-primary">Add Character</a>');
       
        
    $row = $form->addRow()->addClass('creatNum hidediv');
        $row->addLabel('start_number', __('Start No'));
        $row->addTextField('start_number')->addClass('numfield');

    // $row = $form->addRow()->addClass('creatNum hidediv');
    //     $row->addLabel('no_of_digit', __('No of Digit'));
    //     $row->addTextField('no_of_digit');
    
    $row = $form->addRow()->addClass('creatNum hidediv');
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('', __('No of Digit'));
        $col->addContent('');
       
        $col = $row->addColumn()->setClass('newdes');   
        //$col->addTextField('no_of_digit')->addClass('numfield');
        $col->addContent('<div style="display:inline-flex;"><input type="text" id="no_of_digit" name="no_of_digit" class="w-full numfield"><i  id="addNum" data-id="1" style="cursor:pointer; font-size: 25px;margin: 5px 5px;" class="mdi mdi-check-circle mdi-24px"></i></div>');    

    $row = $form->addRow()->addClass('creatChar hidediv');
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('', __('Enter Character'));
        $col->addContent('');
       
        $col = $row->addColumn()->setClass('newdes');   
        $col->addContent('<div style="display:inline-flex;"><input type="text" id="start_char" name="start_char" class="w-full"><i id="addChar" style="cursor:pointer;  font-size: 25px;margin: 5px 5px;" class="mdi mdi-check-circle mdi-24px"></i></div>');
        
    $row = $form->addRow();
        $row->addFooter();
        $row->addContent('<button type="submit" id="chkSubmit" style="margin:0px 4px;" class="transactionButton btn btn-primary">Submit</button>');

    echo $form->getOutput();

}
?>
<script>
$('#start_char').on('keypress', function (event) {
    var regex = new RegExp("^[0-9]+$");
    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
    if (regex.test(key)) {
       event.preventDefault();
       return false;
    }
});
</script>

<?php
/*
Pupilsight, Flexible & Open School System
*/


use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    
    $page->breadcrumbs
        ->add(__('Manage Campaign'), 'index.php')
        ->add(__('Add Campaign'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();

    $program=array();  
    $program2=array();  
    $program1=array(''=>'Select Program');
    foreach ($rowdataprog as $key => $dt) {
        $program2[$dt['pupilsightProgramID']] = $dt['name'];
    }
    $program= $program1 + $program2; 

    $form = Form::create('Campaign', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/addProcess.php')->addClass('newform');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
   

    $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resultval = $connection2->query($sqlq);
         $rowdata = $resultval->fetchAll();
         $academic=array();
         $ayear = '';
        if(!empty($rowdata)){
            $ayear = $rowdata[0]['name'];
            foreach ($rowdata as $dt) {
                $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
            }
        }
    $form->addHiddenValue('ayear', $ayear);    

    $statuses = array(
        '1'     => __('Draft'),
        '2'  => __('Publish'),
        '3' => __('Stop'),
    );
    $reg_status = array(
        '1'     => __('No'),  //public// page_for-1(db)
        '2'  => __('Yes'),   // private// page_for-2(db)
       
    );
   
    echo '<h2>';
    echo __('Add Campaign');
    echo '</h2>';
    $row = $form->addRow();
    
        $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('name', __('Name'));
                $col->addTextField('name')->addClass('txtfield')->required();
            
        $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('academic_year', __('Academic Year'));
                $col->addSelect('academic_id')->addClass('txtfield')->fromArray($academic)->selected($pupilsightSchoolYearID)->required();

        $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('pupilsightProgramID', __('Program'));
                $col->addSelect('pupilsightProgramID')->setId('getMultiClassByProg')->addClass('txtfield')->fromArray($program)->required();        
                
        $col = $row->addColumn()->setClass('newdes showClass');
                $col->addLabel('classes', __('Class'))->addClass('dte');
                $col->addSelect('classes')->setId('showMultiClassByProg')->addClass('txtfield')->placeholder('Select Class')->selectMultiple();    
        // $col = $row->addColumn()->setClass('newdes');
        //         $col->addLabel('seats', __('Seats'))->addClass('dte');
        //         $col->addTextField('seats')->addClass('txtfield'); 
                $form->addHiddenValue('seats', '');        

               
        
        
    $row = $form->addRow();
       
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('limit_apply_form', __('Limit Max Users'))->addClass('dte');
            $col->addNumber('limit_apply_form')->setId('numAllow')->addClass('txtfield');  

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('status', __('Status'));
            $col->addSelect('status')->addClass('txtfield')->fromArray($statuses)->required();
            
        
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('start_date', __('Start Date'))->addClass('dte');
            $col->addDate('start_date')->addClass('txtfield')->readonly()->required();   

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('end_date', __('End Date'))->addClass('dte');
            $col->addDate('end_date')->addClass('txtfield')->readonly()->required();

    $row = $form->addRow();
        
    $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('reg_req', __('Registration Required'));
            $col->addSelect('reg_req')->addClass('txtfield')->fromArray($reg_status)->required();
            
    $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('', __(''));

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('', __(''));

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('', __(''));
                  

   
    // $form->toggleVisibilityByClass('statusChange')->onSelect('status')->when('Current');
    // $direction = __('Past');

    // Display an alert to warn users that changing this will have an impact on their system.
    // $row = $form->addRow()->setClass('statusChange');
    // $row->addAlert(sprintf(__('Setting the status of this school year to Current will change the current school year %1$s to %2$s. Adjustments to the Academic Year can affect the visibility of vital data in your system. It\'s recommended to use the Rollover tool in User Admin to advance school years rather than changing them here. PROCEED WITH CAUTION!'), $_SESSION[$guid]['pupilsightSchoolYearNameCurrent'], $direction) );

   

    $row = $form->addRow();
              
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('description', __('Description'));
            $col->addTextArea('description')->addClass('txtfield')->setRows(4); 

    $row = $form->addRow();
            $col = $row->addColumn()->setClass('newdes');
            $col->addButton(__('Add More Seat Matrix'))->addData('cid', '1')->setID('addSeats')->addClass('bttnsubmt');

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('Total Seats : ', __('Total Seats : '))->addClass('showSeats');
           // $row->addButton('Add Seats')->addData('class', 'addSeats')->addClass('submt');
                  
    $row = $form->addRow()->setID('seatdiv');
           $col = $row->addColumn()->setClass('newdes');
               $col->addLabel('name', __('Name'));
               $col->addTextField('seatname[1]')->addClass('txtfield');
   
           $col = $row->addColumn()->setClass('newdes');
               $col->addLabel('seat', __('Campaign Seat'))->addClass('dte');
               $col->addNumber('seatallocation[1]')->addClass('txtfield kountseat szewdt'); 
              // $col->addLabel('', __(''))->addClass('dte');
               
           
           
   
    
        $row = $form->addRow()->setID('lastseatdiv');
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
  
}
?>

<style>
    
    .multiselect {
        width: 212px;
        height: 35px;
    }
    .multiselect-container{
        height: 300px;
        overflow: auto;
    }

    
</style>
<script>
    
    $(document).ready(function () {
      	$('#showMultiClassByProg').selectize({
      		maxItems: 15,
      		plugins: ['remove_button'],
      	});
    });
</script>

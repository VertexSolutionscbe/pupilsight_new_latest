<?php

echo "<style>
.sel_width {
    width: 33% !important;
}
</style>";
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\School\SchoolYearGateway;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/wf_add.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    
    $page->breadcrumbs
        ->add(__('Manage Work Flow'), 'index.php')
        ->add(__('Add Work Flow'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/wf_state_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $form = Form::create('WorkFlow State', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/add_state_Process.php')->addClass('newform');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
	$id="";
	$ac_y="";
	$name="";
	if(isset($_REQUEST['id'])?$id=$_REQUEST['id']:$id="" );
	if(isset($_REQUEST['academic_year'])?$ac_y=$_REQUEST['academic_year']:$ac_y="" );
	if(isset($_REQUEST['name'])?$name=$_REQUEST['name']:$name="" );
	$form->addHiddenValue('cid', $id);
	

    $statuses = array(
        '1'     => __('Draft'),
        '2'  => __('Publish'),
        '3' => __('Stop'),
    );
	$schoolYearGateway = $container->get(SchoolYearGateway::class);
    $criteria = $schoolYearGateway->newQueryCriteria()
        ->sortBy(['sequenceNumber'])
        ->fromPOST();

    $schoolYears = $schoolYearGateway->querySchoolYears($criteria);
	/*echo "<pre>";
		print_r($schoolYears->getColumn('name'));*/
		$ay=$schoolYears->getColumn('name');
/*echo "<pre>";
print_r($_REQUEST);exit;*/
//`workflowid`,`name`,`code`,`display_name`,`notification`,`cuid`,

	    $notification = array(
		'0'     => __('Select'),
        '1'     => __('Email'),
        '2'  => __('SMS'),
        '3' => __('Email& SMS'),
    );
    $row = $form->addRow();	   
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('name', __('State Name'));
            $col->addTextField('name')->addClass('txtfield')->required()->setValue($ac_y);
	  $col = $row->addColumn()->setClass('newdes');
				$col->addLabel('code', __('State Code'));
				$col->addTextField('code')->addClass('txtfield')->required();		
         
		$col = $row->addColumn()->setClass('newdes');
                $col->addLabel('display_name', __('Display Name'));
                $col->addTextField('display_name')->addClass('txtfield')->required()->setValue($name); 
		
				
	$row = $form->addRow();	  

		$col = $row->addColumn()->setClass('newdes sel_width');
            $col->addLabel('notification', __('Notification'));
            $col->addSelect('notification')->addClass('txtfield')->fromArray($notification)->required();	
		$row = $form->addRow();	 
              
        	

   
    // $form->toggleVisibilityByClass('statusChange')->onSelect('status')->when('Current');
    // $direction = __('Past');

    // Display an alert to warn users that changing this will have an impact on their system.
    // $row = $form->addRow()->setClass('statusChange');
    // $row->addAlert(sprintf(__('Setting the status of this school year to Current will change the current school year %1$s to %2$s. Adjustments to the Academic Year can affect the visibility of vital data in your system. It\'s recommended to use the Rollover tool in User Admin to advance school years rather than changing them here. PROCEED WITH CAUTION!'), $_SESSION[$guid]['pupilsightSchoolYearNameCurrent'], $direction) );

        $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit()->addClass('submt');

    echo $form->getOutput();
  
}

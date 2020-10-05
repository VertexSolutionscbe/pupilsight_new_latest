<?php
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
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/wf_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $form = Form::create('WorkFlow', $_SESSION[$guid]['absoluteURL'].'/modules/Campaign/add_wf_Process.php')->addClass('newform');
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

    $notification = array(
        ''     => __('Select Notification'),
        '1'     => __('Email'),
        '2'  => __('SMS'),
        '3' => __('Both'),
		
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

echo '<h2>';
echo __('Add WorkFlow');
echo '</h2>';

    $row = $form->addRow();	   
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('academic_year', __('Academic Year'));
            $col->addTextField('academic_year')->addClass('txtfield')->required()->setValue($ac_y);
			
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('campaign Name', __('Campaign Name'));
            $col->addTextField('name')->addClass('txtfield')->required()->setValue($name);

			$col = $row->addColumn()->setClass('newdes');
            $col->addLabel('name', __('Work Flow Name'));
            $col->addTextField('name')->addClass('txtfield')->required();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('code', __('Work Flow Code'));
            $col->addTextField('code')->addClass('txtfield')->required();
				
	$row = $form->addRow();	   
 			
		    $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('description', __('Description'));
            $col->addTextArea('description')->addClass('txtfield')->setRows(4);
    
    $row = $form->addRow();
            $col = $row->addColumn()->setClass('newdes');
            //$col->addButton(__('Add More State'))->addData('cid', '1')->setID('addState')->addClass('bttnsubmt'); 
            $col->addContent('<a class="btn btn-primary" id="addState" data-cid="1">Add More State</a>');

            $col = $row->addColumn()->setClass('newdes');
            //$col->addLabel('Total Seats : ', __('Total Seats : '))->addClass('showSeats');
           // $row->addButton('Add Seats')->addData('class', 'addSeats')->addClass('submt');
                  
    $row = $form->addRow()->setID('seatdiv');
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('order', __('Order No *'));
            $col->addNumber('serialorder[1]')->addClass('txtfield')->required();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('name', __('State Name *'));
            $col->addTextField('statename[1]')->addClass('txtfield')->required();
            
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('code', __('State Code *'));
            $col->addTextField('statecode[1]')->addClass('txtfield')->required();

           
            
            $col = $row->addColumn()->setClass('max-w-full sm:max-w-xs flex justify-end newdes sel_width');
            $col->addLabel('notification', __('Notification'))->addClass('ncls');
            $col->addSelect('notification[1]')->addClass('txtfield kountseat szewdt showTemplate')->fromArray($notification)->addData('sid', '1');

            $col->addContent('<a href="'.$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/Campaign/email_sms_template.php&wsid=1&type=" data-hrf="'.$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/Campaign/email_sms_template.php&wsid=1&type=" class="thickbox" id="clickTemplate1" style="display:none;">click</a><input type="hidden" name="pupilsightTemplateIDs[1]" id="pupilsightTemplateID-1" value=""><div id="showTemplateName1" ></div>');

            $col->addLabel('', __(''))->addClass('dte'); 
            
           

   
    // $form->toggleVisibilityByClass('statusChange')->onSelect('status')->when('Current');
    // $direction = __('Past');

    // Display an alert to warn users that changing this will have an impact on their system.
    // $row = $form->addRow()->setClass('statusChange');
    // $row->addAlert(sprintf(__('Setting the status of this school year to Current will change the current school year %1$s to %2$s. Adjustments to the Academic Year can affect the visibility of vital data in your system. It\'s recommended to use the Rollover tool in User Admin to advance school years rather than changing them here. PROCEED WITH CAUTION!'), $_SESSION[$guid]['pupilsightSchoolYearNameCurrent'], $direction) );
        $row = $form->addRow()->setID('lastseatdiv');
        $row->addSubmit()->addClass('submt');
        $row->addFooter();

    echo $form->getOutput();
  
}

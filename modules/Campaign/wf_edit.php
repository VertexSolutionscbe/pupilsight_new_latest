<?php
 
echo "<style>
.text-xxs  {
    display: none;
}

</style>";
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/wf_edit.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Work Flow'), 'wf_manage.php')
        ->add(__('Edit Work Flow'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $id = $_GET['id'];
    if ($id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try { 
            $data = array('id' => $id);
            $sql = 'SELECT wf.*,wm.workflow_id,wm.campaign_id,camp.id AS camp_id,camp.name AS camp_name FROM workflow AS wf LEFT JOIN workflow_map AS wm ON (wf.id=wm.workflow_id) LEFT JOIN campaign AS camp ON (camp.id=wm.campaign_id)  WHERE wf.id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
            
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
     $values = $result->fetch();  
/*
echo "<pre>";   
print_r($values);  */
     $id=$values['id'];
     $cid=$values['campaign_id'];
	$ac_y=$values['academic_year'];
	$camp_name=$values['camp_name'];
	$wf_name =$values['name'];
	$wf_code =$values['code'];
    $wf_desc =$values['description'];
   
    $sqlq = 'SELECT * FROM workflow_state where workflowid = '.$id.' ';
    $resultval = $connection2->query($sqlq);
    $rowdata = $resultval->fetchAll();
    $rowlast = end($rowdata);
    $lastid = $rowlast['id'];
    // echo '<pre>';
    // print_r($rowlast);        
    // echo '</pre>';
    // die(0);
    $statekount = count($rowdata);
    if(!empty($statekount)){
        $statekount = $statekount;
    } else {
        $statekount = '1';
    }

    $sqlchk = 'SELECT count(id) AS kount FROM workflow_transition  WHERE campaign_id = '.$cid.' ';
    $resultchk = $connection2->query($sqlchk);
    $chktransdata = $resultchk->fetch();
    $kountTransData = $chktransdata['kount'];

    $notification = array(
        ''     => __('Select Notification'),
        '1'     => __('Email'),
        '2'  => __('SMS'),
        '3' => __('Both'),
    );
	
	
            $form = Form::create('WorkFlow', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/wf_editProcess.php?id='.$id)->addClass('newform');
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
           $form->addHiddenValue('cid', $id);  
           echo '<div style="display: inline-flex;width: 100%;"><h2 style="width:74%;">';
           echo __('Edit WorkFlow');
           echo '</h2>';
           
            if($kountTransData < 1){
                echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=modules/Campaign/add_wf_transitions.php&wid=".$id."&cid=".$cid."' class='btn btn-primary'>Edit Work Flow Transition</a></div></div></div>";   
            } else {
                echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=modules/Campaign/edit_wf_transition.php&id=".$cid."&wid=".$id."' class='btn btn-primary'>Edit Work Flow Transition</a></div></div></div>"; 
            }
            

        $row = $form->addRow();	   
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('academic_year', __('Academic Year'));
            $col->addTextField('academic_year')->addClass('txtfield')->setValue($ac_y);
			
        $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('campaign Name', __('Campaign Name'));
                $col->addTextField('name')->addClass('txtfield')->readonly()->setValue($camp_name);
		
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('name', __('Work Flow Name'));
            $col->addTextField('name')->addClass('txtfield')->readonly()->setValue($wf_name);	
        
            $col = $row->addColumn()->setClass('newdes');     
            $col->addLabel('wf_code', __('Work Flow Code'));
            $col->addTextField('code')->addClass('txtfield')->readonly()->setValue($wf_code);
            	        
				
	$row = $form->addRow();	   
 			
		    
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('description', __('Description'));
            $col->addTextArea('description')->addClass('txtfield')->setRows(4)->setValue($wf_desc);

        $row = $form->addRow();
            $col = $row->addColumn()->setClass('newdes');
            //$col->addButton(__('Add More State'))->addData('cid', $lastid)->setID('addState')->addClass('bttnsubmt');
            $col->addContent('<a class="btn btn-primary" id="addState" data-cid='.$lastid.'>Add More State</a>');

            $col = $row->addColumn()->setClass('newdes');
            //$col->addLabel('Total Seats : ', __('Total Seats : '))->addClass('showSeats');
           // $row->addButton('Add Seats')->addData('class', 'addSeats')->addClass('submt');
                  
        
        if(!empty($rowdata)){ 
            $i = '1';
            foreach($rowdata as $k=>$st){   
                $row = $form->addRow()->setID('seatdiv');
                $col = $row->addColumn()->setClass('newdes');
                if($i == '1'){
                    $col->addLabel('order', __('Order No *'));
                }    
                $col->addNumber('serialorder['.$st['id'].']')->addClass('txtfield')->required()->setValue($st['order_wise']);

                $col = $row->addColumn()->setClass('newdes');
                if($i == '1'){
                    $col->addLabel('name', __('State Name *'));
                }
                $col->addTextField('statename['.$st['id'].']')->addClass('txtfield')->required()->setValue($st['name']);
                
                $col = $row->addColumn()->setClass('newdes');
                if($i == '1'){
                    $col->addLabel('code', __('State Code *'));
                }
                $col->addTextField('statecode['.$st['id'].']')->addClass('txtfield')->required()->setValue($st['code']);

                
                
                $col = $row->addColumn()->setClass('max-w-full sm:max-w-xs flex justify-end newdes sel_width');
                if($i == '1'){
                    $col->addLabel('notification', __('Notification'))->addClass('ncls');
                }
                $col->addSelect('notification['.$st['id'].']')->addClass('txtfield kountseat szewdt showTemplate')->fromArray($notification)->selected($st['notification'])->addData('sid', $st['id']);
                $col->addContent('<a href="'.$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/Campaign/email_sms_template.php&wsid='.$st['id'].'&type=" data-hrf="'.$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/Campaign/email_sms_template.php&wsid='.$st['id'].'&type=" class="thickbox" id="clickTemplate'.$st['id'].'" style="display:none;">click</a><input type="hidden" name="pupilsightTemplateIDs['.$st['id'].']" id="pupilsightTemplateID-'.$st['id'].'" value="">');

                $col->addLabel('', __(''))->addClass('dte'); 

                $i++;
            }
        }

            $row = $form->addRow()->setID('lastseatdiv');
                $row->addSubmit()->addClass('submt');
                $row->addFooter();
                

            echo $form->getOutput();
        }
    }
}

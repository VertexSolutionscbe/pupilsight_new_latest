<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/wf_state_edit.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Work Flow State'), 'wf_state_list.php')
        ->add(__('Edit Work Flow State'));

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
            $sql = 'SELECT * FROM workflow_state WHERE id=:id';
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
	  $notification = array(
        '1'     => __('Email'),
        '2'  => __('SMS'),
        '3' => __('Email& SMS'),
    );
/*
echo "<pre>";   
print_r($values);  
    
	*/
	
            $form = Form::create('WorkFlow State', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/wf_state_editProcess.php?id='.$id)->addClass('newform');
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
           $form->addHiddenValue('cid', $id);  
		$row = $form->addRow();	   
			 $row = $form->addRow();	   
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('name', __('State Name'));
            $col->addTextField('name')->addClass('txtfield')->required()->setValue($values['name']);
	  $col = $row->addColumn()->setClass('newdes');
				$col->addLabel('code', __('State Code'));
				$col->addTextField('code')->addClass('txtfield')->required()->setValue($values['code']);		
         
		$col = $row->addColumn()->setClass('newdes');
                $col->addLabel('display_name', __('Display Name'));
                $col->addTextField('display_name')->addClass('txtfield')->required()->setValue($values['display_name']); 
		
				
	$row = $form->addRow();	  
		
		$col = $row->addColumn()->setClass('newdes sel_width');
            $col->addLabel('notification', __('Notification'));
			$col->addSelect('notification')
                                     ->fromArray($notification)
                                     ->selected($values['notification']);	
			 
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit()->addClass('submt');

            echo $form->getOutput();
        }
    }
}

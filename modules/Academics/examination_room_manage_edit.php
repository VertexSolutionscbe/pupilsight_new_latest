<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Academics/examination_room_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $id = $_GET['id'];
    try {
        $data = array('id' => $id);
        $sql = 'SELECT * FROM examinationRoomMaster WHERE id=:id';
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
    //Proceed!
   // print_r($values);die();

    $page->breadcrumbs->add(__('Manage Test Room'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo '<h3>';
    echo __('Edit Test Room');
    echo '</h3>';

    $form = Form::create('specialDayAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/examination_room_manage_editProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']); 
    $form->addHiddenValue('id', $id); 
 
    $row = $form->addRow();        
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('name', __('Room Name'));
        $col->addTextField('name')->addClass('txtfield')->required()->setValue($values['name']);

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('code', __('Room Code'));
        $col->addTextField('code')->addClass('txtfield')->required()->setValue($values['code']);
        
    $row = $form->addRow();
        
        $col = $row->addColumn()->setClass('newdes');
        
        // $row->addButton('Add Seats')->addData('class', 'addSeats')->addClass('submt');
        $col->addLabel('', __(''))->addClass('dte');
        $row = $form->addRow()->setID('lastseatdiv');
        $row->addFooter();
        $row->addSubmit();

        echo $form->getOutput();
  
}
}
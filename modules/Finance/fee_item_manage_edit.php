<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_item_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Item'), 'fee_item_manage.php')
        ->add(__('Edit Fee Item'));

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
            $sql = 'SELECT * FROM fn_fee_items WHERE id=:id';
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

            echo '<h2>';
            echo __('Edit Fee Item');
            echo '</h2>';

            $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
            $resulta = $connection2->query($sqla);
            $academic = $resulta->fetchAll();
        
            $academicData = array();
            foreach ($academic as $dt) {
                $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
            }
            
        
            $sqlp = 'SELECT id, name FROM fn_fee_item_type ';
            $resultp = $connection2->query($sqlp);
            $feeType = $resultp->fetchAll();
        
            $feeItemType = array();
            foreach ($feeType as $dt) {
                $feeItemType[$dt['id']] = $dt['name'];
            }

            $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_item_manage_editProcess.php?id='.$id);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addLabel('name', __('Fee Item Name'))->description(__('Must be unique.'));
                $row->addTextField('name')->required()->setValue($values['name']);

            $row = $form->addRow();
                $row->addLabel('code', __('Fee Item Code'))->description(__('Must be unique.'));
                $row->addTextField('code')->required()->setValue($values['code']);
        
            $row = $form->addRow();
                $row->addLabel('name', __('Academic Year'));
                $row->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($values['pupilsightSchoolYearID'])->required();
        
            $row = $form->addRow();
                $row->addLabel('code', __('Item Type'));
                $row->addSelect('fn_fee_item_type_id')->fromArray($feeItemType)->selected($values['fn_fee_item_type_id'])->required();
            
           
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}

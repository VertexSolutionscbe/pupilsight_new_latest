<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$studentids = $session->get('student_ids');

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_quick_cash_payment_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Quick Cash Payment'), 'fee_quick_cash_payment.php')
        ->add(__('Quick Cash Payment'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_structure_assign_student_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Quick Cash Payment');
    echo '</h2>';
    $form = Form::create('program_quick', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_quick_cash_payment_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

     $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('stu_id', $studentids);

    $row = $form->addRow();
        $row->addLabel('amount', __('Amount'));
        $row->addTextField('amount')->addClass('txtfield numfield')->required();

    $row = $form->addRow();
        $row->addLabel('fine', __('Fine'));
        $row->addTextField('fine')->addClass('txtfield numfield');

    $row = $form->addRow();
        $row->addLabel('grand_total', __('Grand Total'));
        $row->addTextField('grand_total')->addClass('txtfield numfield')->required();   
     
    
        
    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit(__('Make Payment'));

    echo $form->getOutput();

}

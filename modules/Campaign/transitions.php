<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/transitions.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs
        ->add(__('Transition'), 'transitions.php')
        ->add(__('Add Transition'));

    // $editLink = '';
    // if (isset($_GET['editID'])) {
    //     $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/edit_wf_transition.php&id='.$_GET['editID'];
    // }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }


    $form = Form::create('WorkflowTransition', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/transitionProcess.php')->addClass('newform');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $sqlq = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE  table_schema='" . $_SESSION['databaseName'] . "' ";
    $resultval = $connection2->query($sqlq);
    $rowdata = $resultval->fetchAll();

    $tables = array();
    $tables2 = array();
    $tables1 = array('' => 'Select Table');
    foreach ($rowdata as $dt) {
        $tables2[$dt['TABLE_NAME']] = $dt['TABLE_NAME'];
    }
    $tables = $tables1 + $tables2;


    $sql = 'SELECT id, form_id, name FROM campaign ';
    $result = $connection2->query($sql);
    $rowdatacamp = $result->fetchAll();

    $campaign = array();
    $campaign2 = array();
    $campaign1 = array('' => 'Select Campaign');
    foreach ($rowdatacamp as $dt) {
        $campaign2[$dt['id']] = $dt['name'];
    }
    $campaign = $campaign1 + $campaign2;

    echo '<h2>';
    echo __('Add Transitions');
    echo '</h2>';

    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addButton(__('Add Transition'))->addData('cid', '1')->addData('tname', 'none')->addData('cname', 'none')->setID('addMoreTransition')->addClass('bttnsubmt');

    $row = $form->addRow()->setClass('requiredcss')->setID('requireddiv1');
    $row = $form->addRow()->setID('seatdiv');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('table_name', __('Tables'));
    $col->addSelect('table_name[1]')->addData('rid', '1')->addClass('tableName txtfield')->fromArray($tables)->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('column', __('Column'));
    $col->addSelect('column[1]')->setID('columnName1')->addClass('txtfield')->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('auto_gen_inv', __('Application'));
    $col->addSelect('campaign[1]')->addData('rid', '1')->addClass('campaignName txtfield')->fromArray($campaign)->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('tansition_action', __('Form'));
    $col->addSelect('fluent_form[1]')->setID('fluentForm1')->addClass('txtfield')->required();


    $row = $form->addRow()->setID('lastseatdiv');
    $row->addFooter();
    $row->addSubmit()->addClass('sumit_css submt');

    echo $form->getOutput();
}
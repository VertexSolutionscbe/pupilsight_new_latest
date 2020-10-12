<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Admission\AdmissionGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/fee_setting.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('State Remark'));

    echo '<h2>';
    echo __('Add Remarks');
    echo '</h2>';

    $cid = $_GET['cid'];
    $sid = $_GET['sid'];
    $sname = $_GET['sname'];
    $fid = $_GET['fid'];
    $subid = $_GET['subid'];
    

    $form = Form::create('searchForm', '');
    $form = Form::create('Remark', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/campaignFormStatesRemark.php')->addClass('newform');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('cid', $cid);
    $form->addHiddenValue('sid', $sid);
    $form->addHiddenValue('sname', $sname);
    $form->addHiddenValue('fid', $fid);
    $form->addHiddenValue('subid', $subid);
    
    $row = $form->addRow();
   
    $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('remarks', __('Remark'));
            $col->addTextArea('remarks')->addClass('txtfield')->setRows(4);
            
    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __(''))->setClass('right');
    $col->addContent('<button type="submit" class="btn btn-primary" style="float:right;">Save</a>')->setClass('right');
    
    echo $form->getOutput();

}
?>
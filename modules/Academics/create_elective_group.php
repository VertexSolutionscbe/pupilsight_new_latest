<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;

use Pupilsight\Forms\DatabaseFormFactory;
// $session = $container->get('session');
// $rollGroup = $session->get('section_ids');

if (isActionAccessible($guid, $connection2, '/modules/Academics/create_elective_group.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $pupilsightSchoolYearID = $_GET['sid'];
    $pupilsightProgramID = $_GET['pid'];
    $pupilsightYearGroupID = $_GET['cid'];
    //Proceed!
    $page->breadcrumbs->add(__('Manage Elective Group'), 'manage_elective_group.php')->add(__('Add Elective Group'));
    
    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Academics/create_elective_group.php&id=' . $_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
   
    echo '<h2>';
    echo __('Create Elective Group');
    echo '</h2>';
    
    
    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/create_elective_groupProcess.php')->addClass('newform');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
    $form->addHiddenValue('pupilsightProgramID', $pupilsightProgramID);
    $form->addHiddenValue('pupilsightYearGroupID', $pupilsightYearGroupID);
    
    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('elective_name', __('Elective Group Name'));
    $col->addTextField('elective_name')->addClass('txtfield')->required();
    
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('order_no', __('Order Number'));
    $col->addTextField('order_no')->addClass('txtfield')->required();
    
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('max_selection', __('Max Selection'));
    $col->addNumber('max_selection')->addClass('txtfield')->required();
    
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('min_selection', __('Min Selection'));
    $col->addNumber('min_selection')->addClass('txtfield')->required();
    
    
    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addContent('<input type= "checkbox" class="enableLinkbychkBox" >  Section Specified &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a id="enableLink" style="display:none;" href="fullscreen.php?q=/modules/Academics/section_specify.php&pid='.$pupilsightProgramID.'&cid='.$pupilsightYearGroupID.'" class="thickbox  btn btn-primary">Select Section</a><a id="disableLink" class="btn btn-primary">Select Section</a>');
    
    $col = $row->addColumn()->setClass('newdes');
    $col->addContent('<a id="" href="fullscreen.php?q=/modules/Academics/subject_specify.php&pid='.$pupilsightProgramID.'&cid='.$pupilsightYearGroupID.'" class="thickbox  btn btn-primary">Assign Subject</a>');
    
    
    // $row = $form->addRow();
    // $col = $row->addColumn()->setClass('newdes');
    // $col->addContent('<input type= "checkbox"> Applicable to Specialization <button class="btn btn-primary">Select Specification</button> ');
    
    
    $row = $form->addRow();
    $row->addFooter();
    $row->addSubmit();  
    
    echo $form->getOutput();
    
}
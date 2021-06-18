<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
// print_r($id);die();

if (isActionAccessible($guid, $connection2, '/modules/Staff/remove_assigned_staffSub.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Staff'), 'staff_view.php')
        ->add(__('Change Staff Status'));

  
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Add Section');
    echo '</h2>';

    $pupilsightSchoolYearID = $_GET['aid'];
    $pupilsightProgramID =  $_GET['pid'];
    $pupilsightYearGroupID =  $_GET['cid'];
    $ids =  $_GET['ids'];

    $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" AND a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" GROUP BY a.pupilsightRollGroupID';
    
    //echo $sql;
    $result = $connection2->query($sql);
    $sectionsdata = $result->fetchAll();

    $sections = array();
    $sections2 = array();
    $sections1 = array('' => 'Select Section');
    foreach ($sectionsdata as $ct) {
        $sections2[$ct['pupilsightRollGroupID']] = $ct['name'];
    }
    $sections = $sections1 + $sections2;

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/add_sectionProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
    $form->addHiddenValue('pupilsightProgramID', $pupilsightProgramID);
    $form->addHiddenValue('pupilsightYearGroupID', $pupilsightYearGroupID);
    $form->addHiddenValue('ids', $ids);
   
    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelect('pupilsightRollGroupID')->fromArray($sections)->placeholder('Select Section')->required();
    
    $row = $form->addRow();

    $row->addSubmit();
 

    echo $form->getOutput();


}

<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/grade_system_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs
        ->add(__('Manage Grade System'), 'grade_system_manage.php')
        ->add(__('Add Grade'));

    
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

        $sqlq = 'SELECT * FROM pupilsightSchoolYear ORDER BY sequenceNumber';
        $resultval = $connection2->query($sqlq);
        $rowdata = $resultval->fetchAll();
        $academic = array();
        $ayear = '';
        if (!empty($rowdata)) {
            $ayear = $rowdata[0]['name'];
            foreach ($rowdata as $dt) {
                $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
            }
        }
        $form = Form::create('gradesytemadd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/grade_system_addProcess.php?address='.$_SESSION[$guid]['address']);
        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $row = $form->addRow();
        $row->addLabel('name', __('Grade System Name'));
        $row->addTextField('name')->maxLength(40)->required();
        $row = $form->addRow();
        $row->addLabel('code', __('Grade System Code'));
        $row->addTextField('code')->maxLength(30)->required();
        $row = $form->addRow();
        $row->addFooter();      
        $row->addSubmit();
        echo $form->getOutput();
}

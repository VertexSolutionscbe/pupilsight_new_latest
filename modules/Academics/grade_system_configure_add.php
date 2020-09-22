<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/grade_system_configure_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $sid = $_GET['sid'];
    $page->breadcrumbs
        ->add(__('Manage Grade System'), 'grade_system_manage.php')
        ->add(__('Add Grade'));
        echo '<h3>';
        echo __('Add Subject Grades');
        echo '</h3>';
    
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

        $grade_class = array(
            'Distinction' => __('Distinction '),
            'First Class' => __('First Class'),
            'Poor' => __('Poor'),
            'Second Class' => __('Second Class')
        );

        $subStatus = array('Pass' => 'Pass', 'Fail' => 'Fail');


        $form = Form::create('gradesytemadd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/grade_system_configure_addProcess.php?address='.$_SESSION[$guid]['address']);
        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('gradeSystemId', $sid);
        $row = $form->addRow();
        $row->addLabel('grade_name', __('Subject grade Name'));
        $row->addTextField('grade_name')->required();
        $row = $form->addRow();
        $row->addLabel('grade_point', __('Grade Point'));
        $row->addTextField('grade_point')->required()->addClass('txtfield   numfield');
        $row = $form->addRow();
        $row->addLabel('lower_limit', __('Lower Limit'));
        $row->addTextField('lower_limit')->required()->addClass('txtfield   numfield');
        $row = $form->addRow();
        $row->addLabel('upper_limit', __('Upper Limit'));
        $row->addTextField('upper_limit')->required()->addClass('txtfield   numfield');
        $row = $form->addRow()->setClass('error_cls');
        $row->addContent('<span  style="color:red">Please Check, Lower Limit is Less than or Equal to Upper limit</span>');
        $row = $form->addRow();
        $row->addLabel('rank', __('Rank'));
        $row->addTextField('rank')->addClass('txtfield   numfield'); 
        $row = $form->addRow();
        $row->addLabel('subject_status', __('Subject Status'));
        $row->addRadio('subject_status')->fromArray($subStatus)->inline();
        $row = $form->addRow();
        $row->addLabel('class_obtained', __('Class Obtained'));
        $row->addSelect('class_obtained')->fromArray($grade_class)->placeholder();
        $row = $form->addRow();
        $row->addLabel('description', __('Description'));
        $row->addTextArea('description')->setRows(4);
        $row = $form->addRow();
        $row->addFooter();
        $row->addContent('<a href="#" id="checkformVal" class="btn btn-primary">Add</a> <input type="submit" id="formStnBtn" style="display:none">');      
        //$row->addSubmit(__('Add'))->setClass('submit_btn_syst');
        echo $form->getOutput();
}
?>
<style>
    .error_cls {
        display: none; 
    }
    </style>
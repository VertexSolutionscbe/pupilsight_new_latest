<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/grade_system_configure_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $id = $_GET['id'];
    $gradeSystemId = $_GET['gradeSystemId'];

    $page->breadcrumbs
        ->add(__('Manage Grade System'), 'grade_system_manage.php')
        ->add(__('Edit Grade'));
        echo '<h3>';
        echo __('Edit Subject Grades');
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

        $data = array('id' => $id);
        $sql = 'SELECT * FROM examinationGradeSystemConfiguration WHERE id=:id';
        $result = $connection2->prepare($sql);
        $result->execute($data);
        $values = $result->fetch();

        if(!empty($values)){
            $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/grade_system_configure_editProcess.php?id='.$id);

            $form->setFactory(DatabaseFormFactory::create($pdo));
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('gradeSystemId', $gradeSystemId);
            $row = $form->addRow();
            $row->addLabel('grade_name', __('Subject grade Name'));
            $row->addTextField('grade_name')->required()->setValue($values['grade_name']);
            $row = $form->addRow();
            $row->addLabel('grade_point', __('Grade Point'));
            $row->addTextField('grade_point')->required()->addClass('txtfield   numfield')->setValue($values['grade_point']);
            $row = $form->addRow();
            $row->addLabel('lower_limit', __('Lower Limit'));
            $row->addTextField('lower_limit')->required()->addClass('txtfield   numfield')->setValue($values['lower_limit']);
            $row = $form->addRow();
            $row->addLabel('upper_limit', __('Upper Limit'));
            $row->addTextField('upper_limit')->required()->addClass('txtfield   numfield')->setValue($values['upper_limit']);
            $row = $form->addRow()->setClass('error_cls');
        $row->addContent('<span  style="color:red">Please Check, Lower Limit is Less than or Equal to Upper limit</span>');
            $row = $form->addRow();
            $row->addLabel('rank', __('Rank'));
            $row->addTextField('rank')->addClass('txtfield   numfield')->setValue($values['rank']);
            $row = $form->addRow();
            $row->addLabel('subject_status', __('Subject Status'));
            $row->addRadio('subject_status')->fromArray($subStatus)->inline()->checked($values['subject_status']);
            $row = $form->addRow();
            $row->addLabel('class_obtained', __('Class Obtained'));
            $row->addSelect('class_obtained')->fromArray($grade_class)->placeholder()->selected($values['class_obtained']);
            $row = $form->addRow();
            $row->addLabel('description', __('Description'));
            $row->addTextArea('description')->setRows(4)->setValue($values['description']);
            $row = $form->addRow();
            $row->addFooter();  
             $row->addContent('<a href="#" id="checkformVal" class="btn btn-primary">Update</a> <input type="submit" id="formStnBtn" style="display:none">');    
            //$row->addSubmit(__('Update'));
            echo $form->getOutput();
        } else {
            echo "<div class='error'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        }
       
}
?>
<style>
    .error_cls {
        display: none; 
    }
    </style>
<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/gradeScales_manage_edit_grade_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightScaleGradeID = $_GET['pupilsightScaleGradeID'] ?? '';
    $pupilsightScaleID = $_GET['pupilsightScaleID'] ?? '';
    if ($pupilsightScaleGradeID == '' or $pupilsightScaleID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightScaleID' => $pupilsightScaleID, 'pupilsightScaleGradeID' => $pupilsightScaleGradeID);
            $sql = 'SELECT pupilsightScaleGrade.*, pupilsightScale.name AS name FROM pupilsightScale JOIN pupilsightScaleGrade ON (pupilsightScale.pupilsightScaleID=pupilsightScaleGrade.pupilsightScaleID) WHERE pupilsightScaleGrade.pupilsightScaleID=:pupilsightScaleID AND pupilsightScaleGrade.pupilsightScaleGradeID=:pupilsightScaleGradeID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();

            $page->breadcrumbs
                ->add(__('Manage Grade Scales'), 'gradeScales_manage.php')
                ->add(__('Edit Grade Scale'), 'gradeScales_manage_edit.php', ['pupilsightScaleID' => $pupilsightScaleID])
                ->add(__('Edit Grade'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            $form = Form::create('gradeScaleGradeEdit', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/gradeScales_manage_edit_grade_editProcess.php?pupilsightScaleGradeID='.$pupilsightScaleGradeID.'&pupilsightScaleID='.$pupilsightScaleID);

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightScaleID', $pupilsightScaleID);

            $row = $form->addRow();
                $row->addLabel('name', __('Grade Scale'));
                $row->addTextField('name')->readonly()->setValue($values['name']);

            $row = $form->addRow();
                $row->addLabel('value', __('Value'))->description(__('Must be unique for this grade scale.'));
                $row->addTextField('value')->required()->maxLength(10);

            $row = $form->addRow();
                $row->addLabel('descriptor', __('Descriptor'));
                $row->addTextField('descriptor')->required()->maxLength(50);

            $row = $form->addRow();
                $row->addLabel('sequenceNumber', __('Sequence Number'))->description(__('Must be unique for this grade scale.'));
                $row->addNumber('sequenceNumber')->required()->maxLength(5);

            $row = $form->addRow();
                $row->addLabel('isDefault', __('Is Default?'))->description(__('Preselects this option when using this grade scale in appropriate contexts.'));
                $row->addYesNo('isDefault')->required();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}

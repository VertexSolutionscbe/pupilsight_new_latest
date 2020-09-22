<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/externalAssessments_manage_edit_field_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightExternalAssessmentID = $_GET['pupilsightExternalAssessmentID'] ?? '';

    if ($pupilsightExternalAssessmentID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightExternalAssessmentID' => $pupilsightExternalAssessmentID);
            $sql = 'SELECT name as assessmentName FROM pupilsightExternalAssessment WHERE pupilsightExternalAssessmentID=:pupilsightExternalAssessmentID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record does not exist.');
            echo '</div>';
        } else {
            $values = $result->fetch();

            $page->breadcrumbs
                ->add(__('Manage External Assessments'), 'externalAssessments_manage.php')
                ->add(__('Edit External Assessment'), 'externalAssessments_manage_edit.php', ['pupilsightExternalAssessmentID' => $pupilsightExternalAssessmentID])
                ->add(__('Add Field'));

            $editLink = '';
            if (isset($_GET['editID'])) {
                $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/School Admin/externalAssessments_manage_edit_field_edit.php&pupilsightExternalAssessmentFieldID='.$_GET['editID'].'&pupilsightExternalAssessmentID='.$_GET['pupilsightExternalAssessmentID'];
            }
            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], $editLink, null);
            }

            $form = Form::create('externalAssessmentField', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/externalAssessments_manage_edit_field_addProcess.php');
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightExternalAssessmentID', $pupilsightExternalAssessmentID);

            $row = $form->addRow();
                $row->addLabel('assessmentName', __('External Assessment'));
                $row->addTextField('assessmentName')->readonly()->setValue($values['assessmentName']);

            $row = $form->addRow();
                $row->addLabel('name', __('Name'));
                $row->addTextField('name')->required()->maxLength(50);

            $row = $form->addRow();
                $row->addLabel('category', __('Category'));
                $row->addTextField('category')->required()->maxLength(50);

            $row = $form->addRow();
                $row->addLabel('order', __('Order'))->description(__('Order in which fields appear within category<br/>Should be unique for this category.'));
                $row->addNumber('order')->required()->maxLength(4);

            $sql = "SELECT pupilsightScaleID as value, name FROM pupilsightScale WHERE (active='Y') ORDER BY name";
            $row = $form->addRow();
                $row->addLabel('pupilsightScaleID', __('Grade Scale'))->description(__('Grade scale used to control values that can be assigned.'));
                $row->addSelect('pupilsightScaleID')->fromQuery($pdo, $sql)->required()->placeholder();

            $row = $form->addRow();
                $row->addLabel('yearGroups', __('Year Groups'))->description(__('Year groups to which this field is relevant.'));
                $row->addCheckboxYearGroup('pupilsightYearGroupIDList')->addCheckAllNone();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}

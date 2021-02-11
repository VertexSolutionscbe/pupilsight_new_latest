<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\GradeScaleGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/gradeScales_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Grade Scales'), 'gradeScales_manage.php')
        ->add(__('Edit Grade Scale'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightScaleID = (isset($_GET['pupilsightScaleID'])) ? $_GET['pupilsightScaleID'] : null;
    if (empty($pupilsightScaleID)) {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightScaleID' => $pupilsightScaleID);
            $sql = 'SELECT * FROM pupilsightScale WHERE pupilsightScaleID=:pupilsightScaleID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();

            $form = Form::create('gradeScaleEdit', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/gradeScales_manage_editProcess.php?pupilsightScaleID=' . $pupilsightScaleID);

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightScaleID', $pupilsightScaleID);

            $row = $form->addRow();
            $row->addLabel('name', __('Name'))->description(__('Must be unique.'));
            $row->addTextField('name')->required()->maxLength(40);

            $row = $form->addRow();
            $row->addLabel('nameShort', __('Short Name'))->description(__('Must be unique.'));
            $row->addTextField('nameShort')->required()->maxLength(5);

            $row = $form->addRow();
            $row->addLabel('usage', __('Usage'))->description(__('Brief description of how scale is used.'));
            $row->addTextField('usage')->required()->maxLength(50);

            $row = $form->addRow();
            $row->addLabel('active', __('Active'));
            $row->addYesNo('active')->required();

            $row = $form->addRow();
            $row->addLabel('numeric', __('Numeric'))->description(__('Does this scale use only numeric grades? Note, grade "Incomplete" is exempt.'));
            $row->addYesNo('numeric')->required();

            $data = array('pupilsightScaleID' => $pupilsightScaleID);
            $sql = "SELECT sequenceNumber as value, pupilsightScaleGrade.value as name FROM pupilsightScaleGrade WHERE pupilsightScaleID=:pupilsightScaleID ORDER BY sequenceNumber";

            $row = $form->addRow();
            $row->addLabel('lowestAcceptable', __('Lowest Acceptable'))->description(__('This is the lowest grade a student can get without being unsatisfactory.'));
            $row->addSelect('lowestAcceptable')->fromQuery($pdo, $sql, $data)->placeholder();

            $row = $form->addRow();
            $row->addFooter();
            $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();

            echo '<h2>';
            echo __('Edit Grades');
            echo '</h2>';

            $gradeScaleGateway = $container->get(GradeScaleGateway::class);

            // QUERY
            $criteria = $gradeScaleGateway->newQueryCriteria()
                ->sortBy('sequenceNumber')
                ->fromPOST();

            $grades = $gradeScaleGateway->queryGradeScaleGrades($criteria, $pupilsightScaleID);

            // DATA TABLE
            $table = DataTable::createPaginated('gradeScaleManage', $criteria);

            $table->addHeaderAction('add', __('Add'))
                ->setURL('/modules/School Admin/gradeScales_manage_edit_grade_add.php')
                ->addParam('pupilsightScaleID', $pupilsightScaleID)
                ->displayLabel();

            $table->addColumn('value', __('Value'));
            $table->addColumn('descriptor', __('Descriptor'));
            $table->addColumn('sequenceNumber', __('Sequence Number'));
            $table->addColumn('isDefault', __('Is Default?'))->format(Format::using('yesNo', ['isDefault']));

            // ACTIONS
            $table->addActionColumn()
                ->addParam('pupilsightScaleID')
                ->addParam('pupilsightScaleGradeID')
                ->format(function ($grade, $actions) {
                    $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/School Admin/gradeScales_manage_edit_grade_edit.php');

                    $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/School Admin/gradeScales_manage_edit_grade_delete.php');
                });

            echo $table->render($grades);
        }
    }
}

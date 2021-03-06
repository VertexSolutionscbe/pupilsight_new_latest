<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\ExternalAssessmentGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/externalAssessments_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage External Assessments'), 'externalAssessments_manage.php')
        ->add(__('Edit External Assessment'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightExternalAssessmentID = $_GET['pupilsightExternalAssessmentID'];
    if ($pupilsightExternalAssessmentID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightExternalAssessmentID' => $pupilsightExternalAssessmentID);
            $sql = 'SELECT * FROM pupilsightExternalAssessment WHERE pupilsightExternalAssessmentID=:pupilsightExternalAssessmentID';
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

            $form = Form::create('externalAssessmentEdit', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/externalAssessments_manage_editProcess.php?pupilsightExternalAssessmentID='.$pupilsightExternalAssessmentID);

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('pupilsightExternalAssessmentID', $pupilsightExternalAssessmentID);

            $row = $form->addRow();
                $row->addLabel('name', __('Name'))->description(__('Must be unique.'));
                $row->addTextField('name')->required()->maxLength(50);

            $row = $form->addRow();
                $row->addLabel('nameShort', __('Short Name'))->description(__('Must be unique.'));
                $row->addTextField('nameShort')->required()->maxLength(10);

            $row = $form->addRow();
                $row->addLabel('description', __('Description'))->description(__('Brief description of assessment and how it is used.'));
                $row->addTextField('description')->required()->maxLength(255);

            $row = $form->addRow();
                $row->addLabel('active', __('Active'));
                $row->addYesNo('active')->required();

            $row = $form->addRow();
                $row->addLabel('allowFileUpload', __('Allow File Upload'))->description(__('Should the student record include the option of a file upload?'));
                $row->addYesNo('allowFileUpload')->required()->selected('N');

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();

            echo '<h2>';
            echo __('Edit Fields');
            echo '</h2>';

            $externalAssessmentGateway = $container->get(ExternalAssessmentGateway::class);

            // QUERY
            $criteria = $externalAssessmentGateway->newQueryCriteria()
                ->sortBy(['category', 'order'])
                ->fromPOST();

            $externalAssessments = $externalAssessmentGateway->queryExternalAssessmentFields($criteria, $pupilsightExternalAssessmentID);

            // DATA TABLE
            $table = DataTable::createPaginated('externalAssessmentManage', $criteria);

            $table->addHeaderAction('add', __('Add'))
                ->setURL('/modules/School Admin/externalAssessments_manage_edit_field_add.php')
                ->addParam('pupilsightExternalAssessmentID', $pupilsightExternalAssessmentID)
                ->displayLabel();

            $table->addColumn('name', __('Name'));
            $table->addColumn('category', __('Category'));
            $table->addColumn('order', __('Order'));
                
            // ACTIONS
            $table->addActionColumn()
                ->addParam('pupilsightExternalAssessmentID', $pupilsightExternalAssessmentID)
                ->addParam('pupilsightExternalAssessmentFieldID')
                ->format(function ($externalAssessment, $actions) {
                    $actions->addAction('edit', __('Edit'))
                            ->setURL('/modules/School Admin/externalAssessments_manage_edit_field_edit.php');

                    $actions->addAction('delete', __('Delete'))
                            ->setURL('/modules/School Admin/externalAssessments_manage_edit_field_delete.php');
                });

            echo $table->render($externalAssessments);
        }
    }
}

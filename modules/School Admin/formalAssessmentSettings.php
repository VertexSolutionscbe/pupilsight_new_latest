<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/formalAssessmentSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Formal Assessment Settings'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $form = Form::create('formalAssessmentSettings', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/formalAssessmentSettingsProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $form->addRow()->addHeading(__('Internal Assessment Settings'));

    $setting = getSettingByScope($connection2, 'Formal Assessment', 'internalAssessmentTypes', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description($setting['description']);
        $row->addTextArea($setting['name'])->setValue($setting['value'])->required();

    $form->addRow()->addHeading(__('Primary External Assessement'))->append(__('These settings allow a particular type of external assessment to be associated with each year group. The selected assessment will be used as the primary assessment to be used as a baseline for comparison (for example, within the Markbook). You are required to select a particular field category can be chosen from which to draw data (if no category is chosen, the data will not be saved).'));

    $row = $form->addRow()->setClass('break');
        $row->addContent(__('Year Group'));
        $row->addContent(__('External Assessment'));
        $row->addContent(__('Field Set'));

    // External Assessments, $key => $valye pairs
    $sql = "SELECT pupilsightExternalAssessmentID as `value`, name FROM pupilsightExternalAssessment WHERE active='Y' ORDER BY name";
    $results = $pdo->executeQuery(array(), $sql);
    $externalAssessments = $results->fetchAll(\PDO::FETCH_KEY_PAIR);

    // External Assessment Field Sets
    $sql = "SELECT pupilsightExternalAssessmentField.pupilsightExternalAssessmentID, category FROM pupilsightExternalAssessment JOIN pupilsightExternalAssessmentField ON (pupilsightExternalAssessmentField.pupilsightExternalAssessmentID=pupilsightExternalAssessment.pupilsightExternalAssessmentID) WHERE active='Y' ORDER BY pupilsightExternalAssessmentID, category";
    $results = $pdo->executeQuery(array(), $sql);

    $externalAssessmentsFieldSetNames = array();
    $externalAssessmentsFieldSetIDs = array();

    // Build two arrays, one of $key => $value for the dropdown, one of $key => $class for the chainedTo method
    if ($results && $results->rowCount() > 0) {
        while ($assessment = $results->fetch()) {
            $key = $assessment['pupilsightExternalAssessmentID'].'-'.$assessment['category'];
            $externalAssessmentsFieldSetNames[$key] = substr($assessment['category'], strpos($assessment['category'], '_') + 1);
            $externalAssessmentsFieldSetIDs[$key] = $assessment['pupilsightExternalAssessmentID'];
        }
    }

    // Get and unserialize the current settings value
    $primaryExternalAssessmentByYearGroup = unserialize(getSettingByScope($connection2, 'School Admin', 'primaryExternalAssessmentByYearGroup'));

    // Split the ID portion off of the ID-category pair, for the first dropdown
    $primaryExternalAssessmentIDsByYearGroup = array_map(function($v) { return (stripos($v, '-') !== false? substr($v, 0, strpos($v, '-')) : $v); }, $primaryExternalAssessmentByYearGroup);

    $sql = 'SELECT pupilsightYearGroupID, name FROM pupilsightYearGroup ORDER BY sequenceNumber';
    $result = $pdo->executeQuery(array(), $sql);

    // Add one row per year group
    while ($yearGroup = $result->fetch()) {
        $id = $yearGroup['pupilsightYearGroupID'];

        $selectedID = (isset($primaryExternalAssessmentIDsByYearGroup[$id]))? $primaryExternalAssessmentIDsByYearGroup[$id] : '';
        $selectedField = (isset($primaryExternalAssessmentByYearGroup[$id]))? $primaryExternalAssessmentByYearGroup[$id] : '';

        $row = $form->addRow();
        $row->addContent($yearGroup['name']);

        $row->addSelect('pupilsightExternalAssessmentID['.$id.']')
            ->setID('pupilsightExternalAssessmentID'.$id)
            ->setClass('mediumWidth')
            ->placeholder()
            ->fromArray($externalAssessments)
            ->selected($selectedID);

        $row->addSelect('category['.$id.']')
            ->setID('category'.$id)
            ->setClass('mediumWidth')
            ->placeholder()
            ->fromArray($externalAssessmentsFieldSetNames)
            ->selected($selectedField)
            ->chainedTo('pupilsightExternalAssessmentID'.$id, $externalAssessmentsFieldSetIDs);
    }

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}

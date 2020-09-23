<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/trackingSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Tracking Settings'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $form = Form::create('trackingSettings', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/trackingSettingsProcess.php');

    $form->removeClass('standardForm');
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    // Get the yearGroups in a $key => $value array
    $sql = "SELECT pupilsightYearGroupID as `value`, name FROM pupilsightYearGroup ORDER BY sequenceNumber";
    $result = $pdo->executeQuery(array(), $sql);
    $yearGroups = $result->fetchAll(\PDO::FETCH_KEY_PAIR);

    if (empty($yearGroups)) {
        $form->addRow()->addAlert(__('There are no records to display.'), 'error');
    } else {
        // EXTERNAL ASSESSMENT DATA POINTS
        $row = $form->addRow();
            $row->addHeading(__('Data Points').' - '.__('External Assessment'))
                ->append(__('Use the options below to select the external assessments that you wish to include in your Data Points export.'))
                ->append(' ')
                ->append(__('If duplicates of any assessment exist, only the most recent entry will be shown.'));

        // Get the existing External Assesment IDs and categories
        $sql = "SELECT DISTINCT pupilsightExternalAssessment.pupilsightExternalAssessmentID, pupilsightExternalAssessment.nameShort, pupilsightExternalAssessmentField.category FROM pupilsightExternalAssessment JOIN pupilsightExternalAssessmentField ON (pupilsightExternalAssessmentField.pupilsightExternalAssessmentID=pupilsightExternalAssessment.pupilsightExternalAssessmentID) WHERE active='Y' ORDER BY nameShort, category";
        $result = $pdo->executeQuery(array(), $sql);

        if ($result->rowCount() < 1) {
            $form->addRow()->addAlert(__('There are no records to display.'), 'error');
        } else {
            // Get the external data points from Settings, if any exist
            $externalAssessmentDataPoints = unserialize(getSettingByScope($connection2, 'Tracking', 'externalAssessmentDataPoints'));
            $externalAssessmentDataPoints = is_array($externalAssessmentDataPoints) ? $externalAssessmentDataPoints : array() ;

            // Create a lookup table for data points as pupilsightExternalAssessmentID-category pair
            $externalDP = array();
            foreach ($externalAssessmentDataPoints as $dp) {
                $key = $dp['pupilsightExternalAssessmentID'].'-'.$dp['category'];
                $externalDP[$key] = (isset($dp['pupilsightYearGroupIDList']))? $dp['pupilsightYearGroupIDList'] : '';
            }

            $count = 0;
            while ($assessment = $result->fetch()) {
                $name = 'externalDP['.$count.'][pupilsightYearGroupIDList][]';
                $categoryLabel = substr($assessment['category'], (strpos($assessment['category'], '_') + 1));
                $key = $assessment['pupilsightExternalAssessmentID'].'-'.$assessment['category'];

                $checked = array();
                if (isset($externalDP[$key])) {
                    // Explode the saved CSV data into an array
                    $checked = explode(',', $externalDP[$key]) ;
                }

                // Add the checkbox group for this pupilsightExternalAssessmentID-category pair
                $row = $form->addRow();
                    $row->addLabel($name, __($assessment['nameShort']).' - '.__($categoryLabel));
                    $row->addCheckbox($name)->fromArray($yearGroups)->checked($checked);

                $form->addHiddenValue('externalDP['.$count.'][pupilsightExternalAssessmentID]', $assessment['pupilsightExternalAssessmentID']);
                $form->addHiddenValue('externalDP['.$count.'][category]', $assessment['category']);

                $count++;
            }
        }

        // INTERNAL ASSESSMENT DATA POINTS
        $row = $form->addRow();
            $row->addHeading(__('Data Points').' - '.__('Internal Assessment'))
                ->append(__('Use the options below to select the internal assessments that you wish to include in your Data Points export.'))
                ->append(' ')
                ->append(__('If duplicates of any assessment exist, only the most recent entry will be shown.'));

        $internalAssessmentTypes = explode(',', getSettingByScope($connection2, 'Formal Assessment', 'internalAssessmentTypes'));

        if (empty($internalAssessmentTypes)) {
            $form->addRow()->addAlert(__('There are no records to display.'), 'error');
        } else {
            // Get the internal data points from Settings, if any exist
            $internalAssessmentDataPoints = unserialize(getSettingByScope($connection2, 'Tracking', 'internalAssessmentDataPoints'));
            $internalAssessmentDataPoints = is_array($internalAssessmentDataPoints) ? $internalAssessmentDataPoints : array() ;

            // Create a lookup table for data points (CSV index order can change)
            $internalDP = array();
            foreach ($internalAssessmentDataPoints as $dp) {
                $internalDP[$dp['type']] = (isset($dp['pupilsightYearGroupIDList']))? $dp['pupilsightYearGroupIDList'] : '';
            }

            $count = 0;
            foreach ($internalAssessmentTypes as $internalAssessmentType) {
                $name = 'internalDP['.$count.'][pupilsightYearGroupIDList][]';
                $checked = array();
                if (isset($internalDP[$internalAssessmentType])) {
                    // Explode the saved CSV data into an array
                    $checked = explode(',', $internalDP[$internalAssessmentType]);
                }

                // Add the checkbox group for this type
                $row = $form->addRow();
                    $row->addLabel($name, __($internalAssessmentType));
                    $row->addCheckbox($name)->fromArray($yearGroups)->checked($checked);

                $form->addHiddenValue('internalDP['.$count.'][type]', $internalAssessmentType);

                $count++;
            }
        }
    }

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit()->addClass('submit_align right_align');

    echo $form->getOutput();
}

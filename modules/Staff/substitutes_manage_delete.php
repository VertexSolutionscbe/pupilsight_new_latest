<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;
use Pupilsight\Domain\Staff\SubstituteGateway;

if (isActionAccessible($guid, $connection2, '/modules/Staff/substitutes_manage_delete.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $pupilsightSubstituteID = $_GET['pupilsightSubstituteID'] ?? '';
    
    if (empty($pupilsightSubstituteID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $values = $container->get(SubstituteGateway::class)->getByID($pupilsightSubstituteID);

    if (empty($values)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/Staff/substitutes_manage_deleteProcess.php?pupilsightSubstituteID='.$pupilsightSubstituteID, true);
    echo $form->getOutput();
}

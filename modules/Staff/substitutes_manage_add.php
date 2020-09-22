<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\System\SettingGateway;

if (isActionAccessible($guid, $connection2, '/modules/Staff/substitutes_manage_add.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $search = $_GET['search'] ?? '';

    $page->breadcrumbs
        ->add(__('Manage Substitutes'), 'substitutes_manage.php', ['search' => $search])
        ->add(__('Add Substitute'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/substitutes_manage_edit.php&pupilsightSubstituteID='.$_GET['editID'].'&search='.$_GET['search'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    if ($search != '') {
        echo "<div class='linkTop'>";
        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Staff/substitutes_manage.php&search=$search'>".__('Back to Search Results').'</a>';
        echo '</div>';
    }

    $form = Form::create('subsManage', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/substitutes_manage_addProcess.php?search='.$search);

    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $form->addRow()->addHeading(__('Basic Information'));

    $row = $form->addRow();
        $row->addLabel('pupilsightPersonID', __('Person'))->description(__('Must be unique.'));
        $row->addSelectUsers('pupilsightPersonID')->placeholder()->required();

    $row = $form->addRow();
        $row->addLabel('active', __('Active'));
        $row->addYesNo('active')->required();

    $types = $container->get(SettingGateway::class)->getSettingByScope('Staff', 'substituteTypes');
    $types = array_filter(array_map('trim', explode(',', $types)));

    $row = $form->addRow();
        $row->addLabel('type', __('Type'));
        $row->addSelect('type')->fromArray($types);

    $row = $form->addRow();
        $row->addLabel('priority', __('Priority'))->description(__('Higher priority substitutes appear first when booking coverage.'));
        $row->addSelect('priority')->fromArray(range(-9, 9))->required()->selected(0);

    $row = $form->addRow();
        $row->addLabel('details', __('Details'))->description(__('Additional information such as year group preference, language preference, etc.'));
        $row->addTextArea('details')->setRows(2)->maxlength(255);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
